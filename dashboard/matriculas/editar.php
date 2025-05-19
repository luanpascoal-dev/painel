<?php
session_start();
include '../assets/layouts/verify.php';

if (!isset($_GET['RM'])) {
    $_SESSION['error'] = "Informações inválidas";
    header("Location: ./");
    exit;
}

$RM = $_GET['RM'];

// Buscar dados da matrícula
$stmt = $conn->prepare("SELECT m.*, u.nome as nome_aluno, t.nome as nome_turma 
                       FROM MATRICULA m
                       INNER JOIN ALUNO a ON m.id_aluno = a.id_usuario
                       INNER JOIN usuarios u ON a.id_usuario = u.id
                       INNER JOIN TURMA t ON m.id_turma = t.id
                       WHERE m.RM = ?");
$stmt->bind_param("i", $RM);
$stmt->execute();
$matricula = $stmt->get_result()->fetch_assoc();

if (!$matricula) {
    $_SESSION['error'] = "Matrícula não encontrada";
    header("Location: ./");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novo_status = $_POST['status'];
    
    try {
        $stmt = $conn->prepare("UPDATE MATRICULA SET status = ? WHERE RM = ?");
        $stmt->bind_param("ii", $novo_status, $RM);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Matrícula atualizada com sucesso!";
            header("Location: ./");
            exit;
        } else {
            throw new Exception("Erro ao atualizar matrícula");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Erro ao atualizar matrícula: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Matrícula - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Editar Matrícula</h1>
            </div>

            <div class="content-box">
                <form method="POST" class="form">
                    <div class="form-group">
                        <label>Aluno:</label>
                        <input type="text" value="<?php echo htmlspecialchars($matricula['nome_aluno']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Turma:</label>
                        <input type="text" value="<?php echo htmlspecialchars($matricula['nome_turma']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>RM:</label>
                        <input type="text" value="<?php echo htmlspecialchars($matricula['RM']); ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="status">Status:</label>
                        <select name="status" id="status" required>
                            <option value="1" <?php echo $matricula['status'] ? 'selected' : ''; ?>>Ativa</option>
                            <option value="0" <?php echo !$matricula['status'] ? 'selected' : ''; ?>>Inativa</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 