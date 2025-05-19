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

// Buscar dados da turma
$stmt = $conn->prepare("SELECT * FROM TURMA WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$turma = $result->fetch_assoc();

if(!$turma) {
    header("Location: index");
    exit();
}

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
        // Atualizar turma
        $stmt = $conn->prepare("UPDATE TURMA SET nome = ?, data_inicio = ?, data_final = ?, carga_horaria = ?, id_curso = ? WHERE id = ?");
        $stmt->bind_param("sssiii", $nome, $data_inicio, $data_final, $carga_horaria, $id_curso, $id);
        
        if($stmt->execute()) {
            $success_message = "Turma atualizada com sucesso!";
            // Atualizar dados da turma na variável
            $turma = array_merge($turma, [
                'nome' => $nome,
                'data_inicio' => $data_inicio,
                'data_final' => $data_final,
                'carga_horaria' => $carga_horaria,
                'id_curso' => $id_curso
            ]);
        } else {
            $error_message = "Erro ao atualizar turma: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Turma - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Editar Turma</h1>
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
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($turma['nome']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="id_curso">Curso</label>
                        <select id="id_curso" name="id_curso" required>
                            <option value="">Selecione um curso</option>
                            <?php while($curso = $cursos->fetch_assoc()): ?>
                                <option value="<?php echo $curso['id']; ?>" <?php echo $turma['id_curso'] == $curso['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($curso['nome']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="data_inicio">Data de Início</label>
                        <input type="date" id="data_inicio" name="data_inicio" value="<?php echo date('Y-m-d', strtotime($turma['data_inicio'])); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="data_final">Data de Término</label>
                        <input type="date" id="data_final" name="data_final" value="<?php echo date('Y-m-d', strtotime($turma['data_final'])); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="carga_horaria">Carga Horária (horas)</label>
                        <input type="number" id="carga_horaria" name="carga_horaria" value="<?php echo htmlspecialchars($turma['carga_horaria']); ?>" required>
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
                    <h2><i class="fas fa-info-circle"></i> Informações da Turma</h2>
                    <div class="info-content">
                        <p><strong>ID da Turma:</strong> <?php echo htmlspecialchars($turma['id']); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge <?php echo strtotime($turma['data_final']) > time() ? 'badge-admin' : 'badge-user'; ?>">
                                <?php echo strtotime($turma['data_final']) > time() ? 'Em Andamento' : 'Encerrada'; ?>
                            </span>
                        </p>
                        <p><strong>Duração:</strong> <?php 
                            $inicio = new DateTime($turma['data_inicio']);
                            $fim = new DateTime($turma['data_final']);
                            $duracao = $inicio->diff($fim);
                            echo $duracao->format('%m meses e %d dias');
                        ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 