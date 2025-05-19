<?php
session_start();

include '../assets/layouts/verify.php';

$success_message = '';
$error_message = '';

// Processar o formulário quando enviado
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $unidade = trim($_POST['unidade']);
    $duracao = trim($_POST['duracao']);
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Validações
    if(empty($nome) || empty($unidade) || empty($duracao)) {
        $error_message = "Todos os campos são obrigatórios";
    } else {
        // Criar novo curso
        $stmt = $conn->prepare("INSERT INTO CURSO (nome, unidade, duracao, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $nome, $unidade, $duracao, $status);
        
        if($stmt->execute()) {
            $success_message = "Curso criado com sucesso!";
            // Limpar os campos após sucesso
            $nome = $unidade = $duracao = '';
        } else {
            $error_message = "Erro ao criar curso: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Curso - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <!-- Sidebar -->
        <?php include '../assets/layouts/sidebar.php'; ?>

        <!-- Conteúdo Principal -->
        <div class="main-content">
            <div class="header">
                <h1>Novo Curso</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle fa-2x"></i>
                    <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                </div>
            </div>

            <div class="form-container">
                <?php if($success_message): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if($error_message): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="nome">Nome do Curso</label>
                        <input type="text" id="nome" name="nome" value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="unidade">Unidade</label>
                        <input type="text" id="unidade" name="unidade" value="<?php echo isset($unidade) ? htmlspecialchars($unidade) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="duracao">Duração</label>
                        <input type="text" id="duracao" name="duracao" value="<?php echo isset($duracao) ? htmlspecialchars($duracao) : ''; ?>" required>
                        <small class="form-text text-muted">Exemplo: 6 meses, 1 ano, etc.</small>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="status" <?php echo (!isset($status) || $status) ? 'checked' : ''; ?>>
                            <span>Curso Ativo</span>
                        </label>
                    </div>

                    <div class="btn-container">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Criar Curso
                        </button>
                        <a href="./" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 