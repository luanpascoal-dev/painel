<?php
session_start();

// Verificar se está logado
if(!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
    header("Location: ../login");
    exit();
}

// Conexão com o banco de dados
include '../database.php';

// Verificar se o usuário é admin
$stmt = $conn->prepare("SELECT nivel_acesso FROM usuarios WHERE id = ? AND nivel_acesso = 'admin'");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows !== 1) {
    header("Location: ../index");
    exit();
}

$success_message = '';
$error_message = '';

// Processar o formulário quando enviado
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $usuario = trim($_POST['usuario']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $nivel_acesso = $_POST['nivel_acesso'];
    
    // Validações
    if(empty($nome) || empty($usuario) || empty($email) || empty($senha)) {
        $error_message = "Todos os campos são obrigatórios";
    } else {
        // Verificar se usuário já existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        if($stmt->get_result()->num_rows > 0) {
            $error_message = "Este nome de usuário já está em uso";
        } else {
            // Criar novo usuário
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, usuario, email, senha, nivel_acesso) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nome, $usuario, $email, $senha_hash, $nivel_acesso);
            
            if($stmt->execute()) {
                $success_message = "Usuário criado com sucesso!";
                // Limpar os campos após sucesso
                $nome = $usuario = $email = '';
            } else {
                $error_message = "Erro ao criar usuário: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Usuário - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard">

        <!-- Sidebar -->
        <?php include 'assets/layouts/sidebar.php'; ?>

    <!-- Conteúdo Principal -->
    <div class="main-content">
        <div class="header">
            <h1>Novo Usuário</h1>
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

            <form method="POST">
                <div class="form-group">
                    <label for="nome">Nome Completo</label>
                    <input type="text" id="nome" name="nome" value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="usuario">Nome de Usuário</label>
                    <input type="text" id="usuario" name="usuario" value="<?php echo isset($usuario) ? htmlspecialchars($usuario) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>

                <div class="form-group">
                    <label for="nivel_acesso">Nível de Acesso</label>
                    <select id="nivel_acesso" name="nivel_acesso">
                        <option value="usuario">Usuário</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn btn-primary">Criar Usuário</button>
                    <a href="usuarios" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    </div>

</body>
</html> 