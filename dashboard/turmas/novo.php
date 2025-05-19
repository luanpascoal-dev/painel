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

$success_message = '';
$error_message = '';

// Buscar cursos para o select
$cursos = $conn->query("SELECT id, nome FROM CURSO WHERE status = 1 ORDER BY nome");

// Processar o formulário quando enviado
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = trim($_POST['nome']);
    $data_inicio = $_POST['data_inicio'];
    $data_final = $_POST['data_final'];
    $carga_horaria = trim($_POST['carga_horaria']);
    $id_curso = $_POST['id_curso'];
    
    // Validações
    if(empty($nome) || empty($data_inicio) || empty($data_final) || empty($carga_horaria) || empty($id_curso)) {
        $error_message = "Todos os campos são obrigatórios";
    } else {
        // Criar nova turma
        $stmt = $conn->prepare("INSERT INTO TURMA (nome, data_inicio, data_final, carga_horaria, id_curso) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $nome, $data_inicio, $data_final, $carga_horaria, $id_curso);
        
        if($stmt->execute()) {
            $success_message = "Turma criada com sucesso!";
            // Limpar os campos após sucesso
            $nome = $data_inicio = $data_final = $carga_horaria = '';
            $id_curso = null;
        } else {
            $error_message = "Erro ao criar turma: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Turma - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Nova Turma</h1>
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
                        <label for="nome">Nome da Turma</label>
                        <input type="text" id="nome" name="nome" value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="id_curso">Curso</label>
                        <select id="id_curso" name="id_curso" required>
                            <option value="">Selecione um curso</option>
                            <?php while($curso = $cursos->fetch_assoc()): ?>
                                <option value="<?php echo $curso['id']; ?>" <?php echo (isset($id_curso) && $id_curso == $curso['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($curso['nome']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="data_inicio">Data de Início</label>
                        <input type="date" id="data_inicio" name="data_inicio" value="<?php echo isset($data_inicio) ? htmlspecialchars($data_inicio) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="data_final">Data de Término</label>
                        <input type="date" id="data_final" name="data_final" value="<?php echo isset($data_final) ? htmlspecialchars($data_final) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="carga_horaria">Carga Horária (horas)</label>
                        <input type="number" id="carga_horaria" name="carga_horaria" value="<?php echo isset($carga_horaria) ? htmlspecialchars($carga_horaria) : ''; ?>" required>
                    </div>

                    <div class="btn-container">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Criar Turma
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