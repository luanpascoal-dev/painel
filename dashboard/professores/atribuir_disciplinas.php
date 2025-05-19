<?php
session_start();
include '../assets/layouts/verify.php';

// Verificar se foi passado um ID de professor
$id_professor = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_professor) {
    $_SESSION['error'] = "Professor não especificado.";
    header("Location: ./");
    exit;
}

// Buscar dados do professor
$query_professor = "SELECT p.*, u.nome 
                   FROM PROFESSOR p 
                   INNER JOIN usuarios u ON p.id_usuario = u.id 
                   WHERE p.id_usuario = ?";
$stmt = $conn->prepare($query_professor);
$stmt->bind_param("i", $id_professor);
$stmt->execute();
$professor = $stmt->get_result()->fetch_assoc();

if (!$professor) {
    $_SESSION['error'] = "Professor não encontrado.";
    header("Location: ./");
    exit;
}

// Buscar todas as disciplinas
$query_todas_disciplinas = "SELECT * FROM DISCIPLINA ORDER BY nome";
$todas_disciplinas = $conn->query($query_todas_disciplinas);

// Buscar disciplinas já atribuídas ao professor
$query_disciplinas_atribuidas = "SELECT d.*, l.id_turma 
                                FROM DISCIPLINA d
                                INNER JOIN LECIONA l ON d.id = l.id_disciplina
                                WHERE l.id_professor = ?";
$stmt = $conn->prepare($query_disciplinas_atribuidas);
$stmt->bind_param("i", $id_professor);
$stmt->execute();
$disciplinas_atribuidas = $stmt->get_result();

$disciplinas_atribuidas_ids = [];
while ($disc = $disciplinas_atribuidas->fetch_assoc()) {
    $disciplinas_atribuidas_ids[] = $disc['id'];
}

// Buscar todas as turmas
$query_turmas = "SELECT t.*, c.nome as nome_curso 
                 FROM TURMA t 
                 INNER JOIN CURSO c ON t.id_curso = c.id 
                 ORDER BY c.nome, t.nome";
$turmas = $conn->query($query_turmas);

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novas_disciplinas = isset($_POST['disciplinas']) ? $_POST['disciplinas'] : [];
    $id_turma = isset($_POST['turma']) ? $_POST['turma'] : null;
    
    if (empty($id_turma)) {
        $_SESSION['error'] = "Selecione uma turma.";
    } else {
        try {
            // Iniciar transação
            $conn->begin_transaction();

            // Remover todas as atribuições atuais
            $stmt = $conn->prepare("DELETE FROM LECIONA WHERE id_professor = ?");
            $stmt->bind_param("i", $id_professor);
            $stmt->execute();

            // Inserir novas atribuições
            if (!empty($novas_disciplinas)) {
                $stmt = $conn->prepare("INSERT INTO LECIONA (id_professor, id_disciplina, id_turma) VALUES (?, ?, ?)");
                foreach ($novas_disciplinas as $disciplina_id) {
                    $stmt->bind_param("iii", $id_professor, $disciplina_id, $id_turma);
                    $stmt->execute();
                }
            }

            $conn->commit();
            $_SESSION['success'] = "Disciplinas atribuídas com sucesso!";
            header("Location: ./");
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Erro ao atribuir disciplinas: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atribuir Disciplinas - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Atribuir Disciplinas</h1>
            </div>

            <?php include '../assets/layouts/alerts.php'; ?>

            <div class="content-box">
                <div class="professor-info">
                    <h2>Professor: <?php echo htmlspecialchars($professor['nome']); ?></h2>
                </div>

                <form method="POST" class="form-grid">
                    <div class="form-group">
                        <label for="turma">Turma:</label>
                        <select name="turma" id="turma" required>
                            <option value="">Selecione uma Turma</option>
                            <?php while ($turma = $turmas->fetch_assoc()): ?>
                                <option value="<?php echo $turma['id']; ?>">
                                    <?php echo htmlspecialchars($turma['nome_curso'] . ' - ' . $turma['nome']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Disciplinas:</label>
                        <div class="checkbox-group">
                            <?php while ($disciplina = $todas_disciplinas->fetch_assoc()): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" 
                                           name="disciplinas[]" 
                                           id="disciplina_<?php echo $disciplina['id']; ?>" 
                                           value="<?php echo $disciplina['id']; ?>"
                                           <?php echo in_array($disciplina['id'], $disciplinas_atribuidas_ids) ? 'checked' : ''; ?>>
                                    <label for="disciplina_<?php echo $disciplina['id']; ?>">
                                        <?php echo htmlspecialchars($disciplina['nome']); ?>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        <a href="./" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
    .content-box {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 20px;
        margin: 20px;
    }

    .professor-info {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .professor-info h2 {
        color: #333;
        font-size: 1.5em;
        margin: 0;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .form-group {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 6px;
        transition: background-color 0.3s;
    }

    .form-group:hover {
        background: #e9ecef;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #333;
    }

    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        cursor: pointer;
        user-select: none;
    }

    .checkbox-item input {
        margin-right: 10px;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    /* Estilo personalizado para checkbox */
    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
    }
    </style>
</body>
</html> 