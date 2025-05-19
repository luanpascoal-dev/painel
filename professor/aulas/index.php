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

// Se disciplina e turma foram selecionadas, buscar aulas
$aulas = [];
if ($id_disciplina && $id_turma) {
    $query_aulas = "SELECT a.*, d.nome as disciplina, t.nome as turma 
                    FROM AULA a
                    INNER JOIN DISCIPLINA d ON a.id_disciplina = d.id
                    INNER JOIN TURMA t ON a.id_turma = t.id
                    WHERE a.id_disciplina = ? AND a.id_turma = ?
                    ORDER BY a.data_hora_inicio DESC";
    $stmt = $conn->prepare($query_aulas);
    $stmt->bind_param("ii", $id_disciplina, $id_turma);
    $stmt->execute();
    $aulas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Aulas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1>Gerenciar Aulas</h1>
                <div class="btn-group">
                    <a href="novo" class="btn-new">
                        <i class="fas fa-plus"></i> Nova Aula
                    </a>
                </div>
            </div>

            <div class="content">
                <div class="card">
                    <div class="card-body">
                        <div class="filter-section">
                            <form id="filterForm" method="GET">
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
                                        <select name="turma" id="turma" onchange="this.form.submit()">
                                            <option value="">Selecione uma Turma</option>
                                            <?php foreach ($turmas as $turma): ?>
                                                <option value="<?= $turma['id'] ?>" <?= $id_turma == $turma['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($turma['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>

                        <?php if ($id_disciplina && $id_turma): ?>
                            <?php if (!empty($aulas)): ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Data/Hora Início</th>
                                                <th>Data/Hora Final</th>
                                                <th>Descrição</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($aulas as $aula): ?>
                                                <tr>
                                                    <td><?= date('d/m/Y H:i', strtotime($aula['data_hora_inicio'])) ?></td>
                                                    <td><?= date('d/m/Y H:i', strtotime($aula['data_hora_final'])) ?></td>
                                                    <td><?= htmlspecialchars($aula['descricao']) ?></td>
                                                    <td class="actions">
                                                        <a href="visualizar?id=<?= $aula['id'] ?>" class="btn-view" title="Visualizar">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="editar?id=<?= $aula['id'] ?>" class="btn-edit" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button onclick="excluirAula(<?= $aula['id'] ?>)" class="btn-delete" title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-book"></i>
                                    <p>Nenhuma aula cadastrada para esta turma.</p>
                                    <a href="novo" class="btn-new">Cadastrar Aula</a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function excluirAula(id) {
        if (confirm('Tem certeza que deseja excluir esta aula?')) {
            window.location.href = `excluir?id=${id}`;
        }
    }
    </script>
</body>
</html> 