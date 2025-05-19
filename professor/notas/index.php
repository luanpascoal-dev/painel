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
                     WHERE l.id_professor = ?";
$stmt = $conn->prepare($query_disciplinas);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Filtros
$id_disciplina = isset($_GET['disciplina']) ? $_GET['disciplina'] : '';
$id_turma = isset($_GET['turma']) ? $_GET['turma'] : '';

// Se uma disciplina foi selecionada, buscar turmas
$turmas = [];
if ($id_disciplina) {
    $query_turmas = "SELECT DISTINCT t.* 
                     FROM TURMA t 
                     INNER JOIN MATRICULA m ON t.id = m.id_turma 
                     INNER JOIN DISCIPLINA d ON t.id_curso = d.id_curso
                     WHERE d.id = ?";
    $stmt = $conn->prepare($query_turmas);
    $stmt->bind_param("i", $id_disciplina);
    $stmt->execute();
    $turmas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Se disciplina e turma foram selecionadas, buscar avaliações e notas
$avaliacoes = [];
$notas = [];
if ($id_disciplina && $id_turma) {
    // Buscar avaliações
    $query_avaliacoes = "SELECT a.* 
                        FROM AVALIACAO a 
                        WHERE a.id_disciplina = ?
                        ORDER BY a.nome ";
    $stmt = $conn->prepare($query_avaliacoes);
    $stmt->bind_param("i", $id_disciplina);
    $stmt->execute();
    $avaliacoes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Buscar alunos e suas notas
    $query_notas = "SELECT a.id_usuario as id, u.nome, av.id as id_avaliacao, n.nota, m.RM
                    FROM ALUNO a 
                    INNER JOIN USUARIOS u ON a.id_usuario = u.id
                    INNER JOIN MATRICULA m ON a.id_usuario = m.id_aluno 
                    LEFT JOIN NOTA n ON a.id_usuario = n.id_aluno 
                    LEFT JOIN AVALIACAO av ON n.id_avaliacao = av.id 
                    WHERE m.id_turma = ? 
                    ORDER BY u.nome";
    $stmt = $conn->prepare($query_notas);
    $stmt->bind_param("i", $id_turma);
    $stmt->execute();
    $notas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Notas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .nota-input {
            width: 70px;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 6px;
        }
        .media {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .nota-input.is-valid {
            border-color: #28a745;
            background-color: #e8f5e9;
        }
        .nota-input.is-invalid {
            border-color: #dc3545;
            background-color: #fde9e9;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1>Gerenciar Notas</h1>
            </div>

            <div class="content">
                <div class="card">
                    <div class="card-body">
                    <div class="filter-section">
                        <form id="filterForm" method="GET" class="row g-3">
                        <div class="filter-groups">

                            <div class="filter-group">
                                <label for="disciplina">Disciplina:</label>
                                <select name="disciplina" id="disciplina" onchange="this.form.submit()">
                                    <option value="">Selecione uma Disciplina</option>
                                    <?php foreach ($disciplinas as $disciplina): ?>
                                        <option value="<?= $disciplina['id'] ?>" <?= $id_disciplina == $disciplina['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($disciplina['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php if ($id_disciplina): ?>
                            <div class="filter-group">
                                <label for="turma">Turma:</label>
                                <select name="turma" id="turma" class="form-select" onchange="this.form.submit()">
                                    <option value="">Selecione uma Turma</option>
                                    <?php foreach ($turmas as $turma): ?>
                                        <option value="<?= $turma['id'] ?>" <?= $id_turma == $turma['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($turma['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>

                            
                        </form>
                    </div>
                        


                        <?php if ($id_disciplina && $id_turma && !empty($avaliacoes)): ?>
                            <div class="table-responsive mt-4">
                                <div id="notasForm">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>RM</th>
                                                <th>Aluno</th>
                                                <?php foreach ($avaliacoes as $avaliacao): ?>
                                                    <th>
                                                        <?= htmlspecialchars($avaliacao['nome']) ?>
                                                        <br>
                                                        <small class="text-muted">(<?= number_format($avaliacao['peso'], 1) ?>)</small>
                                                    </th>
                                                <?php endforeach; ?>
                                                <th>Média</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $alunos = [];
                                            foreach ($notas as $nota) {
                                                $alunos[$nota['id']]['nome'] = $nota['nome'];
                                                $alunos[$nota['id']]['RM'] = $nota['RM'];
                                                if (isset($nota['id_avaliacao'])) {
                                                    $alunos[$nota['id']]['notas'][$nota['id_avaliacao']] = $nota['nota'];
                                                }
                                            }

                                            foreach ($alunos as $id_aluno => $aluno):
                                                $soma = 0;
                                                $count = 0;
                                            ?>
                                            <tr>
                                                <td class="align-middle"><?= htmlspecialchars($aluno['RM']) ?></td>
                                                <td class="align-middle"><?= htmlspecialchars($aluno['nome']) ?></td>
                                                <?php foreach ($avaliacoes as $avaliacao):
                                                    $nota = isset($aluno['notas'][$avaliacao['id']]) ? $aluno['notas'][$avaliacao['id']] : '';
                                                    if ($nota !== '') {
                                                        $soma += $nota * $avaliacao['peso'];
                                                        $count += $avaliacao['peso'];
                                                    }
                                                ?>
                                                    <td>
                                                        <input type="number" 
                                                               class="nota-input" 
                                                               step="0.1" 
                                                               min="0" 
                                                               max="10"
                                                               value="<?= $nota ?>"
                                                               data-aluno="<?= $id_aluno ?>"
                                                               data-avaliacao="<?= $avaliacao['id'] ?>"
                                                               onchange="salvarNota(this)">
                                                    </td>
                                                <?php endforeach; ?>
                                                <td class="media align-middle">
                                                    <?= $count > 0 ? number_format($soma / $count, 1) : '-' ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php elseif ($id_disciplina && $id_turma): ?>
                            <div class="alert alert-info mt-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Nenhuma avaliação cadastrada para esta disciplina.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    async function salvarNota(input) {
        const id_aluno = input.dataset.aluno;
        const id_avaliacao = input.dataset.avaliacao;
        const nota = input.value;

        try {
            const response = await fetch('salvar_nota.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_aluno,
                    id_avaliacao,
                    nota
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Atualizar média
                const row = input.closest('tr');
                const notas = Array.from(row.querySelectorAll('.nota-input'))
                    .map(input => parseFloat(input.value) || 0);
                
                const media = notas.length > 0 
                    ? notas.reduce((a, b) => a + b) / notas.length 
                    : 0;
                
                row.querySelector('.media').textContent = media.toFixed(1);
                
                // Feedback visual
                input.classList.add('is-valid');
                setTimeout(() => input.classList.remove('is-valid'), 2000);
            } else {
                input.classList.add('is-invalid');
                setTimeout(() => input.classList.remove('is-invalid'), 2000);
                alert('Erro ao salvar nota: ' + data.error);
            }
        } catch (error) {
            console.error('Erro:', error);
            input.classList.add('is-invalid');
            setTimeout(() => input.classList.remove('is-invalid'), 2000);
            alert('Erro ao salvar nota');
        }
    }
    </script>
</body>
</html> 