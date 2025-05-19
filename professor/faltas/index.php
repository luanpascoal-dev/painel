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
$query_disciplinas = "SELECT d.* 
                     FROM DISCIPLINA d 
                     INNER JOIN LECIONA l ON d.id = l.id_disciplina 
                     WHERE l.id_professor = ?";
$stmt = $conn->prepare($query_disciplinas);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$disciplinas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Filtros
$id_disciplina = isset($_GET['disciplina']) ? $_GET['disciplina'] : '';
$id_turma = isset($_GET['turma']) ? $_GET['turma'] : '';
$data = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

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

// Se disciplina e turma foram selecionadas, buscar aulas e alunos
$aulas = [];
$alunos = [];
if ($id_disciplina && $id_turma) {
    // Buscar aulas do dia
    $query_aulas = "SELECT a.* 
                    FROM AULA a
                    WHERE a.id_disciplina = ? 
                    AND a.id_turma = ?
                    AND DATE(a.data_hora_inicio) = ?
                    ORDER BY a.data_hora_inicio";
    $stmt = $conn->prepare($query_aulas);
    $stmt->bind_param("iis", $id_disciplina, $id_turma, $data);
    $stmt->execute();
    $aulas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Buscar alunos da turma
    $query_alunos = "SELECT DISTINCT a.id_usuario as id, u.nome, m.RM
                     FROM ALUNO a
                     INNER JOIN USUARIOS u ON a.id_usuario = u.id
                     INNER JOIN MATRICULA m ON a.id_usuario = m.id_aluno
                     WHERE m.id_turma = ?
                     ORDER BY u.nome";
    $stmt = $conn->prepare($query_alunos);
    $stmt->bind_param("i", $id_turma);
    $stmt->execute();
    $alunos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Buscar faltas existentes
    if (!empty($aulas)) {
        $aula_ids = array_column($aulas, 'id');
        $aula_ids_str = implode(',', $aula_ids);
        
        $query_faltas = "SELECT f.id_aluno, f.id_aula 
                        FROM FALTA f 
                        WHERE f.id_aula IN ($aula_ids_str)";
        $faltas = $conn->query($query_faltas)->fetch_all(MYSQLI_ASSOC);
        
        // Criar array associativo de faltas
        $faltas_map = [];
        foreach ($faltas as $falta) {
            $faltas_map[$falta['id_aluno']][$falta['id_aula']] = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Faltas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <h1>Controle de Faltas</h1>
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

                                    <?php if ($id_disciplina && $id_turma): ?>
                                    <div class="filter-group">
                                        <label for="data">Data:</label>
                                        <input type="date" name="data" id="data" value="<?= $data ?>" onchange="this.form.submit()">
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>

                        <?php if ($id_disciplina && $id_turma): ?>
                            <?php if (!empty($aulas) && !empty($alunos)): ?>
                                <form method="POST" action="salvar_faltas" id="formFaltas">
                                    <input type="hidden" name="data" value="<?= $data ?>">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>RM</th>
                                                    <th>Aluno</th>
                                                    <?php foreach ($aulas as $aula): ?>
                                                        <th>
                                                            <?= date('H:i', strtotime($aula['data_hora_inicio'])) ?>
                                                            -
                                                            <?= date('H:i', strtotime($aula['data_hora_final'])) ?>
                                                        </th>
                                                    <?php endforeach; ?>
                                                    <th width="100">Ações</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($alunos as $aluno): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($aluno['RM']) ?></td>
                                                        <td><?= htmlspecialchars($aluno['nome']) ?></td>
                                                        <?php foreach ($aulas as $aula): ?>
                                                            <td>
                                                                <div class="form-check">
                                                                    <input type="checkbox" 
                                                                           class="form-check-input presenca" 
                                                                           name="presenca[<?= $aluno['id'] ?>][<?= $aula['id'] ?>]"
                                                                           data-aluno="<?= $aluno['id'] ?>"
                                                                           <?= !isset($faltas_map[$aluno['id']][$aula['id']]) ? 'checked' : '' ?>>
                                                                </div>
                                                            </td>
                                                        <?php endforeach; ?>
                                                        <td class="actions">
                                                            <button type="button" 
                                                                    class="btn-delete" 
                                                                    onclick="faltarTudo(<?= $aluno['id'] ?>)"
                                                                    title="Marcar todas as faltas">
                                                                <i class="fas fa-user-times"></i>
                                                            </button>
                                                            <button type="button" 
                                                                    class="btn-edit" 
                                                                    onclick="presencaTudo(<?= $aluno['id'] ?>)"
                                                                    title="Marcar todas as presenças">
                                                                <i class="fas fa-user-check"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="form-actions mt-3">
                                        <button type="button" class="btn-primary" onclick="confirmarFaltas()">
                                            <i class="fas fa-save"></i> Salvar Faltas
                                        </button>
                                    </div>
                                </form>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-calendar-times"></i>
                                    <p>Nenhuma aula encontrada para esta data.</p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmarFaltas() {
        // Coletar alunos faltantes
        const form = document.getElementById('formFaltas');
        const checkboxes = form.querySelectorAll('.presenca:not(:checked)');
        const faltantes = [];

        checkboxes.forEach(checkbox => {
            const [aluno_id, aula_id] = checkbox.name.match(/\[(\d+)\]/g).map(m => m.slice(1, -1));
            const row = checkbox.closest('tr');
            const nome = row.cells[1].textContent;
            const horario = document.querySelector(`th:nth-child(${checkbox.closest('td').cellIndex + 1})`).textContent;
            
            faltantes.push(`${nome} - ${horario}`);
        });

        if (faltantes.length > 0) {
            const mensagem = 'Os seguintes alunos serão marcados como faltantes:\n\n' + 
                           faltantes.join('\n') + 
                           '\n\nConfirma o registro das faltas?';

            if (confirm(mensagem)) {
                form.submit();
            }
        } else {
            if (confirm('Nenhuma falta será registrada. Todos os alunos estavam presentes?')) {
                form.submit();
            }
        }
    }

    function faltarTudo(alunoId) {
        const checkboxes = document.querySelectorAll(`input[data-aluno="${alunoId}"]`);
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
    }

    function presencaTudo(alunoId) {
        const checkboxes = document.querySelectorAll(`input[data-aluno="${alunoId}"]`);
        checkboxes.forEach(checkbox => {
            checkbox.checked = true;
        });
    }
    </script>
</body>
</html> 