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

// Buscar dados do curso
$stmt = $conn->prepare("SELECT id, nome, unidade, duracao, status FROM CURSO WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$curso = $result->fetch_assoc();

if(!$curso) {
    header("Location: index");
    exit();
}

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
        // Atualizar curso
        $stmt = $conn->prepare("UPDATE CURSO SET nome = ?, unidade = ?, duracao = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssii", $nome, $unidade, $duracao, $status, $id);
        
        if($stmt->execute()) {
            $success_message = "Curso atualizado com sucesso!";
            // Atualizar dados do curso na variável
            $curso['nome'] = $nome;
            $curso['unidade'] = $unidade;
            $curso['duracao'] = $duracao;
            $curso['status'] = $status;
        } else {
            $error_message = "Erro ao atualizar curso: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Curso - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Editar Curso</h1>
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
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($curso['nome']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="unidade">Unidade</label>
                        <input type="text" id="unidade" name="unidade" value="<?php echo htmlspecialchars($curso['unidade']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="duracao">Duração</label>
                        <input type="text" id="duracao" name="duracao" value="<?php echo htmlspecialchars($curso['duracao']); ?>" required>
                        <small class="form-text text-muted">Exemplo: 6 meses, 1 ano, etc.</small>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="status" <?php echo $curso['status'] ? 'checked' : ''; ?>>
                            <span>Curso Ativo</span>
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
                    <h2><i class="fas fa-info-circle"></i> Informações do Curso</h2>
                    <div class="info-content">
                        <p><strong>ID do Curso:</strong> <?php echo htmlspecialchars($curso['id']); ?></p>
                        <p><strong>Status Atual:</strong> 
                            <span class="badge <?php echo $curso['status'] ? 'badge-admin' : 'badge-user'; ?>">
                                <?php echo $curso['status'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </p>
                        <p><strong>Última Atualização:</strong> <?php echo date('d/m/Y H:i'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 