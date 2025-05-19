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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_disciplina = $_POST['disciplina'];
    $id_turma = $_POST['turma'];
    $data_hora_inicio = $_POST['data_hora_inicio'];
    $data_hora_final = $_POST['data_hora_final'];
    $descricao = $_POST['descricao'];

    // Verificar se o professor leciona esta disciplina nesta turma
    $query_check = "SELECT 1 FROM LECIONA 
                   WHERE id_professor = ? AND id_disciplina = ? AND id_turma = ?";
    $stmt = $conn->prepare($query_check);
    $stmt->bind_param("iii", $professor['id_usuario'], $id_disciplina, $id_turma);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows === 0) {
        $_SESSION['error'] = "Você não leciona esta disciplina nesta turma.";
    } else {
        try {
            $query = "INSERT INTO AULA (id_professor, id_turma, id_disciplina, descricao, data_hora_inicio, data_hora_final) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iiisss", $professor['id_usuario'], $id_turma, $id_disciplina, $descricao, $data_hora_inicio, $data_hora_final);
            $stmt->execute();

            $_SESSION['success'] = "Aula cadastrada com sucesso!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Erro ao cadastrar aula: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Aula - Área do Professor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Nova Aula</h1>
            </div>

            <?php include '../assets/layouts/alerts.php'; ?>

            <div class="content-box">
                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="disciplina">Disciplina:</label>
                        <select name="disciplina" id="disciplina" required>
                            <option value="">Selecione uma Disciplina</option>
                            <?php foreach ($disciplinas as $disciplina): ?>
                                <option value="<?php echo $disciplina['id']; ?>" 
                                        data-turma="<?php echo $disciplina['id_turma']; ?>">
                                    <?php echo htmlspecialchars($disciplina['nome'] . ' - ' . $disciplina['nome_curso'] . ' (' . $disciplina['nome_turma'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="turma">Turma:</label>
                        <select name="turma" id="turma" required>
                            <option value="">Selecione uma Turma</option>
                            <?php foreach ($disciplinas as $disciplina): ?>
                                <option value="<?php echo $disciplina['id_turma']; ?>"
                                        data-disciplina="<?php echo $disciplina['id']; ?>">
                                    <?php echo htmlspecialchars($disciplina['nome_turma']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="data_hora_inicio">Data/Hora Início:</label>
                        <input type="datetime-local" name="data_hora_inicio" id="data_hora_inicio" required>
                    </div>

                    <div class="form-group">
                        <label for="data_hora_final">Data/Hora Final:</label>
                        <input type="datetime-local" name="data_hora_final" id="data_hora_final" required>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Descrição:</label>
                        <textarea name="descricao" id="descricao" rows="4"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Criar Aula</button>
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const disciplinaSelect = document.getElementById('disciplina');
        const turmaSelect = document.getElementById('turma');

        function updateTurmaOptions() {
            const selectedDisciplina = disciplinaSelect.value;
            const turmaOptions = turmaSelect.options;

            for (let i = 0; i < turmaOptions.length; i++) {
                const option = turmaOptions[i];
                if (option.value === '') continue; // Skip the default option

                const disciplinaId = option.getAttribute('data-disciplina');
                option.style.display = disciplinaId === selectedDisciplina ? '' : 'none';
            }

            // Reset turma selection if it's not valid for the selected disciplina
            const selectedTurma = turmaSelect.value;
            const selectedTurmaOption = turmaSelect.querySelector(`option[value="${selectedTurma}"]`);
            if (selectedTurma && selectedTurmaOption.getAttribute('data-disciplina') !== selectedDisciplina) {
                turmaSelect.value = '';
            }
        }

        disciplinaSelect.addEventListener('change', updateTurmaOptions);
        updateTurmaOptions(); // Initial update
    });
    </script>
</body>
</html> 