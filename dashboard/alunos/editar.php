<?php
session_start();

include '../assets/layouts/verify.php';

// Verificar se foi passado um ID
if(!isset($_GET['id'])) {
    header("Location: index");
    exit();
}

$id = $_GET['id'];
$success_message = '';
$error_message = '';

// Buscar dados do aluno
$stmt = $conn->prepare("SELECT a.*, u.nome, u.usuario, u.email, u.status 
                       FROM ALUNO a 
                       INNER JOIN usuarios u ON a.id_usuario = u.id 
                       WHERE a.id_usuario = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$aluno = $result->fetch_assoc();

if(!$aluno) {
    header("Location: index");
    exit();
}

// Processar o formulário quando enviado
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $data_nascimento = $_POST['data_nascimento'];
    $status = isset($_POST['status']) ? 1 : 0;
    $nova_senha = trim($_POST['nova_senha']);
    
    // Validações
    if(empty($nome) || empty($email) || empty($data_nascimento)) {
        $error_message = "Nome, email e data de nascimento são obrigatórios";
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
            
            // Atualizar aluno
            $stmt = $conn->prepare("UPDATE ALUNO SET data_nascimento = ?, status = ? WHERE id_usuario = ?");
            $stmt->bind_param("sii", $data_nascimento, $status, $id);
            $stmt->execute();
            
            // Confirmar transação
            $conn->commit();
            
            $success_message = "Aluno atualizado com sucesso!";
            
            // Atualizar dados na variável
            $aluno['nome'] = $nome;
            $aluno['email'] = $email;
            $aluno['data_nascimento'] = $data_nascimento;
            $aluno['status'] = $status;
            
        } catch (Exception $e) {
            // Reverter em caso de erro
            $conn->rollback();
            $error_message = "Erro ao atualizar aluno: " . $e->getMessage();
        }
    }
}

// Buscar matrículas do aluno
$stmt = $conn->prepare("SELECT m.*, t.nome as nome_turma, c.nome as nome_curso 
                       FROM MATRICULA m 
                       INNER JOIN TURMA t ON m.id_turma = t.id 
                       INNER JOIN CURSO c ON t.id_curso = c.id 
                       WHERE m.id_aluno = ? 
                       ORDER BY m.data_hora DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$matriculas = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Aluno - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Editar Aluno</h1>
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
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($aluno['nome']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="usuario">Nome de Usuário</label>
                        <input type="text" id="usuario" value="<?php echo htmlspecialchars($aluno['usuario']); ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($aluno['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="data_nascimento">Data de Nascimento</label>
                        <input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo date('Y-m-d', strtotime($aluno['data_nascimento'])); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="nova_senha">Nova Senha (deixe em branco para manter a atual)</label>
                        <input type="password" id="nova_senha" name="nova_senha">
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="status" <?php echo $aluno['status'] ? 'checked' : ''; ?>>
                            <span>Aluno Ativo</span>
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
                    <h2><i class="fas fa-info-circle"></i> Informações do Aluno</h2>
                    <div class="info-content">
                        <p><strong>RA:</strong> <?php echo htmlspecialchars($aluno['RA']); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge <?php echo $aluno['status'] ? 'badge-admin' : 'badge-user'; ?>">
                                <?php echo $aluno['status'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Histórico de Matrículas -->
                <?php if($matriculas->num_rows > 0): ?>
                <div class="info-card">
                    <h2><i class="fas fa-history"></i> Histórico de Matrículas</h2>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Turma</th>
                                    <th>Curso</th>
                                    <th>Data Matrícula</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($matricula = $matriculas->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($matricula['nome_turma']); ?></td>
                                    <td><?php echo htmlspecialchars($matricula['nome_curso']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($matricula['data_matricula'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo $matricula['status'] ? 'badge-admin' : 'badge-user'; ?>">
                                            <?php echo $matricula['status'] ? 'Ativa' : 'Cancelada'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html> 