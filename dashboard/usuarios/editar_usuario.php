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
$stmt = $conn->prepare("SELECT nivel_acesso FROM usuarios WHERE id = ? AND nivel_acesso = 'admin'");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows !== 1) {
    header("Location: ../../index");
    exit();
}

// Verificar se foi passado um ID
if(!isset($_GET['id'])) {
    header("Location: index");
    exit();
}

$id = $_GET['id'];
$success_message = '';
$error_message = '';

// Buscar dados do usuário
$stmt = $conn->prepare("SELECT id, nome, usuario, email, nivel_acesso FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if(!$usuario) {
    header("Location: index");
    exit();
}

// Processar o formulário quando enviado
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $nivel_acesso = $_POST['nivel_acesso'];
    $nova_senha = trim($_POST['nova_senha']);
    
    // Validações básicas
    if(empty($nome) || empty($email)) {
        $error_message = "Nome e email são obrigatórios";
    } else {
        // Iniciar a query de atualização
        $query = "UPDATE usuarios SET nome = ?, email = ?, nivel_acesso = ?";
        $params = array($nome, $email, $nivel_acesso);
        $types = "sss";
        
        // Se uma nova senha foi fornecida, incluí-la na atualização
        if(!empty($nova_senha)) {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $query .= ", senha = ?";
            $params[] = $senha_hash;
            $types .= "s";
        }
        
        $query .= " WHERE id = ?";
        $params[] = $id;
        $types .= "i";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        if($stmt->execute()) {
            $success_message = "Usuário atualizado com sucesso!";
            // Atualizar os dados exibidos
            $stmt = $conn->prepare("SELECT id, nome, usuario, email, nivel_acesso FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $usuario = $result->fetch_assoc();
        } else {
            $error_message = "Erro ao atualizar usuário: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário - Dashboard Administrativo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn-container {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Editar Usuário</h1>
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
                        <label for="nome">Nome</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="usuario">Usuário</label>
                        <input type="text" id="usuario" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="nivel_acesso">Nível de Acesso</label>
                        <select id="nivel_acesso" name="nivel_acesso">
                            <option value="usuario" <?php echo $usuario['nivel_acesso'] == 'usuario' ? 'selected' : ''; ?>>Usuário</option>
                            <option value="admin" <?php echo $usuario['nivel_acesso'] == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nova_senha">Nova Senha (deixe em branco para manter a atual)</label>
                        <input type="password" id="nova_senha" name="nova_senha">
                    </div>

                    <div class="btn-container">
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        <a href="./" class="btn btn-danger">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 