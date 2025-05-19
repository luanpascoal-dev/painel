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

// Buscar dados do professor
$stmt = $conn->prepare("SELECT p.*, u.nome, u.usuario, u.email, u.status 
                       FROM PROFESSOR p 
                       INNER JOIN usuarios u ON p.id_usuario = u.id 
                       WHERE p.id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$professor = $result->fetch_assoc();

if(!$professor) {
    header("Location: index");
    exit();
}

// Processar o formulário quando enviado
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
    $status = isset($_POST['status']) ? 1 : 0;
    $nova_senha = trim($_POST['nova_senha']);
    
    // Validações
    if(empty($nome) || empty($email) || empty($cpf)) {
        $error_message = "Nome, email e CPF são obrigatórios";
    } else {
        // Iniciar transação
        $conn->begin_transaction();
        
        try {
            // Atualizar usuário
            if(!empty($nova_senha)) {
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, senha = ?, status = ? WHERE id = ?");
                $stmt->bind_param("sssis", $nome, $email, $senha_hash, $status, $id);
            } else {
                $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, status = ? WHERE id = ?");
                $stmt->bind_param("ssis", $nome, $email, $status, $id);
            }
            $stmt->execute();
            
            // Atualizar professor
            $stmt = $conn->prepare("UPDATE PROFESSOR SET CPF = ? WHERE id_usuario = ?");
            $stmt->bind_param("si", $cpf, $id);
            $stmt->execute();
            
            // Confirmar transação
            $conn->commit();
            
            $success_message = "Professor atualizado com sucesso!";
            
            // Atualizar dados na variável
            $professor['nome'] = $nome;
            $professor['email'] = $email;
            $professor['CPF'] = $cpf;
            $professor['status'] = $status;
            
        } catch (Exception $e) {
            // Reverter em caso de erro
            $conn->rollback();
            $error_message = "Erro ao atualizar professor: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Professor - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Editar Professor</h1>
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
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($professor['nome']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="usuario">Nome de Usuário</label>
                        <input type="text" id="usuario" value="<?php echo htmlspecialchars($professor['usuario']); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($professor['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="cpf">CPF</label>
                        <input type="text" id="cpf" name="cpf" value="<?php echo htmlspecialchars($professor['CPF']); ?>" maxlength="14" required>
                    </div>

                    <div class="form-group">
                        <label for="nova_senha">Nova Senha (deixe em branco para manter a atual)</label>
                        <input type="password" id="nova_senha" name="nova_senha">
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="status" <?php echo $professor['status'] ? 'checked' : ''; ?>>
                            <span>Professor Ativo</span>
                        </label>
                    </div>

                    <div class="btn-container">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        <a href="./" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>

                <!-- Informações Adicionais -->
                <div class="info-card">
                    <h2><i class="fas fa-info-circle"></i> Informações do Professor</h2>
                    <div class="info-content">
                        <p><strong>ID:</strong> <?php echo htmlspecialchars($professor['id_usuario']); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge <?php echo $professor['status'] ? 'badge-admin' : 'badge-user'; ?>">
                                <?php echo $professor['status'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </p>
                    </div>
                </div>
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