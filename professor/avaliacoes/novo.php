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

// Se foi enviado o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $id_disciplina = $_POST['disciplina'];
    $id_turma = $_POST['turma'];
    $tipo = $_POST['tipo'];
    $peso = $_POST['peso'];

    // Validações
    $errors = [];
    
    if (empty($nome)) {
        $errors[] = "O nome da avaliação é obrigatório.";
    }
    
    if (empty($id_disciplina)) {
        $errors[] = "Selecione uma disciplina.";
    }
    
    if (empty($id_turma)) {
        $errors[] = "Selecione uma turma.";
    }
    
    if (empty($tipo)) {
        $errors[] = "O tipo de avaliação é obrigatório.";
    }
    
    if (empty($peso) || !is_numeric($peso) || $peso <= 0) {
        $errors[] = "O peso deve ser um número maior que zero.";
    }

    // Verificar se o professor leciona esta disciplina nesta turma
    if (!empty($id_disciplina) && !empty($id_turma)) {
        $query_check = "SELECT 1 FROM LECIONA 
                       WHERE id_professor = ? AND id_disciplina = ? AND id_turma = ?";
        $stmt = $conn->prepare($query_check);
        $stmt->bind_param("iii", $professor['id_usuario'], $id_disciplina, $id_turma);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            $errors[] = "Você não leciona esta disciplina nesta turma.";
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO AVALIACAO (id_disciplina, nome, peso, tipo) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isds", $id_disciplina, $nome, $peso, $tipo);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Avaliação criada com sucesso!";
                header("Location: index.php");
                exit;
            } else {
                throw new Exception("Erro ao criar avaliação");
            }
        } catch (Exception $e) {
            $errors[] = "Erro ao criar avaliação: " . $e->getMessage();
        }
    }
}

// Pré-selecionar disciplina e turma se vierem da URL
$disciplina_selecionada = isset($_GET['disciplina']) ? $_GET['disciplina'] : '';
$turma_selecionada = isset($_GET['turma']) ? $_GET['turma'] : '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Avaliação - Área do Professor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Nova Avaliação</h1>
            </div>

            <?php include '../assets/layouts/alerts.php'; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="content-box">
                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="disciplina">Disciplina:</label>
                        <select name="disciplina" id="disciplina" required>
                            <option value="">Selecione uma Disciplina</option>
                            <?php while ($disciplina = $disciplinas->fetch_assoc()): ?>
                                <option value="<?php echo $disciplina['id']; ?>" 
                                        data-turma="<?php echo $disciplina['id_turma']; ?>"
                                        <?php echo $disciplina_selecionada == $disciplina['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($disciplina['nome'] . ' - ' . $disciplina['nome_curso'] . ' (' . $disciplina['nome_turma'] . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="turma">Turma:</label>
                        <select name="turma" id="turma" required>
                            <option value="">Selecione uma Turma</option>
                            <?php 
                            $disciplinas->data_seek(0);
                            while ($disciplina = $disciplinas->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $disciplina['id_turma']; ?>"
                                        data-disciplina="<?php echo $disciplina['id']; ?>"
                                        <?php echo $turma_selecionada == $disciplina['id_turma'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($disciplina['nome_turma']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nome">Nome da Avaliação:</label>
                        <input type="text" name="nome" id="nome" required>
                    </div>

                    <div class="form-group">
                        <label for="tipo">Tipo de Avaliação:</label>
                        <select name="tipo" id="tipo" required>
                            <option value="">Selecione o Tipo</option>
                            <option value="Prova">Prova</option>
                            <option value="Trabalho">Trabalho</option>
                            <option value="Projeto">Projeto</option>
                            <option value="Seminário">Seminário</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="peso">Peso:</label>
                        <input type="number" name="peso" id="peso" step="0.01" min="0.01" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Criar Avaliação</button>
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