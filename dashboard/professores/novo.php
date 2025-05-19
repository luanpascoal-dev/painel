<?php
session_start();

include '../assets/layouts/verify.php';

$success_message = '';
$error_message = '';

// Processar o formulário quando enviado
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $usuario = trim($_POST['usuario']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']); // Remove caracteres não numéricos
    
    // Validações
    if(empty($nome) || empty($usuario) || empty($email) || empty($senha) || empty($cpf)) {
        $error_message = "Todos os campos são obrigatórios";
    } else {
        // Verificar se usuário já existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ? OR email = ?");
        $stmt->bind_param("ss", $usuario, $email);
        $stmt->execute();
        if($stmt->get_result()->num_rows > 0) {
            $error_message = "Este usuário ou email já está em uso";
        } else {
            // Iniciar transação
            $conn->begin_transaction();
            
            try {
                // Criar usuário
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO usuarios (nome, usuario, email, senha, nivel_acesso, status) VALUES (?, ?, ?, ?, 'professor', 1)");
                $stmt->bind_param("ssss", $nome, $usuario, $email, $senha_hash);
                $stmt->execute();
                
                $id_usuario = $conn->insert_id;
                
                // Criar professor
                $stmt = $conn->prepare("INSERT INTO PROFESSOR (id_usuario, CPF) VALUES (?, ?)");
                $stmt->bind_param("is", $id_usuario, $cpf);
                $stmt->execute();
                
                // Confirmar transação
                $conn->commit();
                
                $success_message = "Professor cadastrado com sucesso!";
                // Limpar os campos após sucesso
                $nome = $usuario = $email = $cpf = '';
                
            } catch (Exception $e) {
                // Reverter em caso de erro
                $conn->rollback();
                $error_message = "Erro ao cadastrar professor: " . $e->getMessage();
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
    <title>Novo Professor - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Novo Professor</h1>
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
                        <label for="cpf">CPF</label>
                        <input type="text" id="cpf" name="cpf" value="<?php echo isset($cpf) ? htmlspecialchars($cpf) : ''; ?>" maxlength="14" required>
                    </div>

                    <div class="form-group">
                        <label for="senha">Senha</label>
                        <input type="password" id="senha" name="senha" required>
                    </div>

                    <div class="btn-container">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Criar Professor
                        </button>
                        <a href="./" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Máscara para CPF
        document.getElementById('cpf').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
                e.target.value = value;
            }
        });
    </script>
</body>
</html> 