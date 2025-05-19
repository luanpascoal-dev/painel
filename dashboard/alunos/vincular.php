<?php
session_start();

include '../assets/layouts/verify.php';

$success_message = '';
$error_message = '';

// Buscar usuários que não são alunos
$query_usuarios = "SELECT u.* FROM usuarios u 
                  LEFT JOIN ALUNO a ON u.id = a.id_usuario 
                  WHERE a.id_usuario IS NULL 
                  ORDER BY u.nome";
$usuarios = $conn->query($query_usuarios);

// Gerar RA único
function gerarRA($conn) {
    do {
        $ra = mt_rand(10000, 99999);
        $stmt = $conn->prepare("SELECT id_usuario FROM ALUNO WHERE RA = ?");
        $stmt->bind_param("i", $ra);
        $stmt->execute();
    } while ($stmt->get_result()->num_rows > 0);
    return $ra;
}

// Processar o formulário quando enviado
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_POST['id_usuario'];
    $data_nascimento = $_POST['data_nascimento'];
    
    // Validações
    if(empty($id_usuario) || empty($data_nascimento)) {
        $error_message = "Todos os campos são obrigatórios";
    } else {
        // Iniciar transação
        $conn->begin_transaction();
        
        try {
            // Verificar se o usuário existe
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            if($stmt->get_result()->num_rows === 0) {
                throw new Exception("Usuário não encontrado");
            }

            $ra = gerarRA($conn);
            
            // Criar aluno
            $stmt = $conn->prepare("INSERT INTO ALUNO (id_usuario, RA, data_nascimento, status) VALUES (?, ?, ?, 1)");
            $stmt->bind_param("iis", $id_usuario, $ra, $data_nascimento);
            $stmt->execute();
            
            // Confirmar transação
            $conn->commit();
            
            $success_message = "Usuário vinculado como aluno com sucesso! RA: " . $ra;
            
        } catch (Exception $e) {
            // Reverter em caso de erro
            $conn->rollback();
            $error_message = "Erro ao vincular aluno: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vincular Aluno - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Vincular Usuário como Aluno</h1>
                <div class="btn-group">
                    <a href="novo" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Novo Aluno
                    </a>
                    <a href="./" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
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

                <?php if($usuarios->num_rows > 0): ?>
                    <form method="POST" class="form">
                        <div class="form-group">
                            <label for="id_usuario">Selecione o Usuário</label>
                            <select id="id_usuario" name="id_usuario" required class="select-with-info">
                                <option value="">Selecione um usuário</option>
                                <?php while($usuario = $usuarios->fetch_assoc()): ?>
                                    <option value="<?php echo $usuario['id']; ?>" 
                                            data-email="<?php echo htmlspecialchars($usuario['email']); ?>"
                                            data-status="<?php echo $usuario['status']; ?>">
                                        <?php echo htmlspecialchars($usuario['nome']); ?> (<?php echo htmlspecialchars($usuario['usuario']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Informações do usuário selecionado -->
                        <div id="userInfo" class="info-card" style="display: none;">
                            <h3>Informações do Usuário</h3>
                            <div class="info-content">
                                <p><strong>Email:</strong> <span id="userEmail"></span></p>
                                <p><strong>Status:</strong> <span id="userStatus"></span></p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="data_nascimento">Data de Nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" required>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-link"></i> Vincular como Aluno
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">
                        Não há usuários disponíveis para vincular como aluno.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Mostrar informações do usuário selecionado
        document.getElementById('id_usuario').addEventListener('change', function() {
            const userInfo = document.getElementById('userInfo');
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value) {
                document.getElementById('userEmail').textContent = selectedOption.dataset.email;
                document.getElementById('userStatus').textContent = selectedOption.dataset.status == '1' ? 'Ativo' : 'Inativo';
                userInfo.style.display = 'block';
            } else {
                userInfo.style.display = 'none';
            }
        });
    </script>
</body>
</html> 