<?php
session_start();

// Verificar se está logado
if(!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
    header("Location: ../../login");
    exit();
}

// Conexão com o banco de dados
include '../../database.php';

// Verificar se o usuário é admin
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ? AND nivel_acesso = 'admin'");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if(!$usuario) {
    header("Location: ../../index");
    exit();
}

$success_message = '';
$error_message = '';

// Processar alteração de senha
if(isset($_POST['alterar_senha'])) {
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if(password_verify($senha_atual, $usuario['senha'])) {
        if($nova_senha === $confirmar_senha) {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmt->bind_param("si", $senha_hash, $_SESSION['id']);
            
            if($stmt->execute()) {
                $success_message = "Senha alterada com sucesso!";
            } else {
                $error_message = "Erro ao alterar a senha.";
            }
        } else {
            $error_message = "As novas senhas não coincidem.";
        }
    } else {
        $error_message = "Senha atual incorreta.";
    }
}

// Processar atualização de perfil
if(isset($_POST['atualizar_perfil'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);

    if(!empty($nome) && !empty($email)) {
        $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nome, $email, $_SESSION['id']);
        
        if($stmt->execute()) {
            $success_message = "Perfil atualizado com sucesso!";
            $usuario['nome'] = $nome;
            $usuario['email'] = $email;
        } else {
            $error_message = "Erro ao atualizar perfil.";
        }
    } else {
        $error_message = "Nome e email são obrigatórios.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Dashboard</title>
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
            <h1>Configurações</h1>
            <div class="user-info">
                <i class="fas fa-user-circle fa-2x"></i>
                <span><?php echo htmlspecialchars($usuario['usuario']); ?></span>
            </div>
        </div>

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

        <div class="settings-container">
            <!-- Seção de Perfil -->
            <div class="settings-card">
                <h2><i class="fas fa-user"></i> Perfil</h2>
                <form method="POST" class="settings-form">
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>

                    <button type="submit" name="atualizar_perfil" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </form>
            </div>

            <!-- Seção de Senha -->
            <div class="settings-card">
                <h2><i class="fas fa-lock"></i> Alterar Senha</h2>
                <form method="POST" class="settings-form">
                    <div class="form-group">
                        <label for="senha_atual">Senha Atual</label>
                        <input type="password" id="senha_atual" name="senha_atual" required>
                    </div>

                    <div class="form-group">
                        <label for="nova_senha">Nova Senha</label>
                        <input type="password" id="nova_senha" name="nova_senha" required>
                    </div>

                    <div class="form-group">
                        <label for="confirmar_senha">Confirmar Nova Senha</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required>
                    </div>

                    <button type="submit" name="alterar_senha" class="btn btn-primary">
                        <i class="fas fa-key"></i> Alterar Senha
                    </button>
                </form>
            </div>

            <!-- Seção de Preferências -->
            <div class="settings-card">
                <h2><i class="fas fa-sliders-h"></i> Preferências</h2>
                <div class="settings-info">
                    <p><strong>Usuário:</strong> <?php echo htmlspecialchars($usuario['usuario']); ?></p>
                    <p><strong>Nível de Acesso:</strong> <?php echo ucfirst(htmlspecialchars($usuario['nivel_acesso'])); ?></p>
                    <p><strong>Último Acesso:</strong> <?php echo date('d/m/Y H:i'); ?></p>
                </div>
            </div>
        </div>
    </div>

    </div>
</body>
</html> 