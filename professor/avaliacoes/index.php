<?php
session_start();
include '../assets/layouts/verify.php';

// Verificar se é professor
$id_usuario = $_SESSION['id'];
$query_professor = "SELECT p.* FROM PROFESSOR p WHERE p.id_usuario = ?";
$stmt = $conn->prepare($query_professor);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$professor = $stmt->get_result()->fetch_assoc();

if (!$professor) {
    $_SESSION['error'] = "Acesso restrito a professores.";
    header("Location: ../../");
    exit;
}

// Buscar disciplinas do professor
$query_disciplinas = "SELECT d.*, l.id_professor, l.id_turma, c.nome as nome_curso, t.nome as nome_turma
                     FROM DISCIPLINA d
                     INNER JOIN LECIONA l ON d.id = l.id_disciplina
                     INNER JOIN CURSO c ON d.id_curso = c.id
                     INNER JOIN TURMA t ON l.id_turma = t.id
                     WHERE l.id_professor = ?
                     ORDER BY d.nome";
$stmt = $conn->prepare($query_disciplinas);
$stmt->bind_param("i", $professor['id_usuario']);
$stmt->execute();
$disciplinas = $stmt->get_result();

// Estatísticas gerais
$query_stats = "SELECT 
    COUNT(DISTINCT av.id) as total_avaliacoes,
    COUNT(DISTINCT CASE WHEN n.nota IS NULL THEN av.id END) as avaliacoes_pendentes
    FROM AVALIACAO av
    INNER JOIN LECIONA l ON av.id_disciplina = l.id_disciplina
    LEFT JOIN NOTA n ON av.id = n.id_avaliacao
    WHERE l.id_professor = ?";
$stmt = $conn->prepare($query_stats);
$stmt->bind_param("i", $professor['id_usuario']);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliações - Área do Professor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Gerenciar Avaliações</h1>
                <div class="btn-group">
                    <a href="novo" class="btn-new">
                        <i class="fas fa-plus"></i> Nova Avaliação
                    </a>
                </div>
            </div>

            <?php include '../assets/layouts/alerts.php'; ?>

            <!-- Cards de estatísticas -->
            <div class="stats">
                <div class="stat-card">
                    <i class="fas fa-tasks"></i>
                    <h3>Total de Avaliações</h3>
                    <p><?php echo $stats['total_avaliacoes']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>Notas Pendentes</h3>
                    <p><?php echo $stats['avaliacoes_pendentes']; ?></p>
                </div>
            </div>

            <!-- Lista de Disciplinas com suas Avaliações -->
            <div class="disciplinas-container">
                <?php if ($disciplinas->num_rows > 0): ?>
                    <?php while ($disciplina = $disciplinas->fetch_assoc()): ?>
                        <div class="disciplina-card">
                            <div class="disciplina-header">
                                <div>
                                    <h3><?php echo htmlspecialchars($disciplina['nome']); ?></h3>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($disciplina['nome_curso']); ?></span>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($disciplina['nome_turma']); ?></span>
                                </div>
                                <a href="novo?disciplina=<?php echo $disciplina['id']; ?>&turma=<?php echo $disciplina['id_turma']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> Adicionar Avaliação
                                </a>
                            </div>
                            
                            <?php
                            // Buscar avaliações da disciplina
                            $query_avaliacoes = "SELECT av.*, 
                                               COUNT(n.id_aluno) as total_notas,
                                               COUNT(DISTINCT m.id_aluno) as total_alunos
                                               FROM AVALIACAO av
                                               LEFT JOIN NOTA n ON av.id = n.id_avaliacao
                                               LEFT JOIN MATRICULA m ON m.id_turma IN (
                                                   SELECT id_turma FROM AULA WHERE id_disciplina = av.id_disciplina
                                               )
                                               WHERE av.id_disciplina = ?
                                               GROUP BY av.id
                                               ORDER BY av.nome DESC";
                            $stmt = $conn->prepare($query_avaliacoes);
                            $stmt->bind_param("i", $disciplina['id']);
                            $stmt->execute();
                            $avaliacoes = $stmt->get_result();
                            ?>

                            <?php if ($avaliacoes->num_rows > 0): ?>
                                <div class="avaliacoes-list">
                                    <?php while ($avaliacao = $avaliacoes->fetch_assoc()): ?>
                                        <div class="avaliacao-item">
                                            <div class="avaliacao-info">
                                                <h4><?php echo htmlspecialchars($avaliacao['nome']); ?></h4>
                                                <p>
                                                    <span class="badge"><?php echo htmlspecialchars($avaliacao['tipo']); ?></span>
                                                    <span class="badge">Peso: <?php echo $avaliacao['peso']; ?></span>
                                                </p>
                                                <p class="progress-text">
                                                    Notas lançadas: <?php echo $avaliacao['total_notas']; ?>/<?php echo $avaliacao['total_alunos']; ?>
                                                </p>
                                            </div>
                                            <div class="avaliacao-actions">
                                                <a href="../notas?disciplina=<?php echo $disciplina['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-pen"></i> Lançar Notas
                                                </a>
                                                <a href="editar?id=<?php echo $avaliacao['id']; ?>" class="btn btn-sm bg-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="confirmarExclusao(<?php echo $avaliacao['id']; ?>)" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-results">
                                    <p>Nenhuma avaliação cadastrada para esta disciplina.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-book"></i>
                        <p>Você não possui disciplinas atribuídas.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
    .disciplinas-container {
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .disciplina-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .disciplina-header {
        padding: 15px 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .avaliacoes-list {
        padding: 15px;
    }

    .avaliacao-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #eee;
    }

    .avaliacao-item:last-child {
        border-bottom: none;
    }

    .avaliacao-info h4 {
        margin: 0 0 5px 0;
        color: #333;
    }

    .badge {
        background: #e9ecef;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.8em;
        color: #666;
        margin-right: 5px;
    }

    .progress-text {
        color: #666;
        font-size: 0.9em;
        margin: 5px 0 0 0;
    }

    .avaliacao-actions {
        display: flex;
        gap: 5px;
    }

    .no-results {
        text-align: center;
        padding: 40px;
        color: #666;
    }

    .no-results i {
        font-size: 2em;
        margin-bottom: 10px;
        color: #999;
    }
    </style>

    <script>
    function confirmarExclusao(id) {
        if (confirm('Tem certeza que deseja excluir esta avaliação?')) {
            window.location.href = `excluir.php?id=${id}`;
        }
    }
    </script>
</body>
</html> 