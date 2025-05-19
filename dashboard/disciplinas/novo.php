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
    $codigo = trim($_POST['codigo']);
    $carga_horaria = trim($_POST['carga_horaria']);
    $metodo_avaliacao = trim($_POST['metodo_avaliacao']);
    $descricao_avaliacao = trim($_POST['descricao_avaliacao']);
    $descricao = trim($_POST['descricao']);
    $id_curso = $_POST['id_curso'];
    
    // Validações
    if(empty($nome) || empty($codigo) || empty($carga_horaria) || empty($id_curso)) {
        $error_message = "Os campos Nome, Código, Carga Horária e Curso são obrigatórios";
    } else {
        // Criar nova disciplina
        $stmt = $conn->prepare("INSERT INTO DISCIPLINA (nome, codigo, carga_horaria, metodo_avaliacao, descricao_avaliacao, descricao, id_curso) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisssi", $nome, $codigo, $carga_horaria, $metodo_avaliacao, $descricao_avaliacao, $descricao, $id_curso);
        
        if($stmt->execute()) {
            $success_message = "Disciplina criada com sucesso!";
            // Limpar os campos após sucesso
            $nome = $codigo = $carga_horaria = $metodo_avaliacao = $descricao_avaliacao = $descricao = '';
            $id_curso = null;
        } else {
            $error_message = "Erro ao criar disciplina: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Disciplina - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Nova Disciplina</h1>
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
                        <label for="nome">Nome da Disciplina</label>
                        <input type="text" id="nome" name="nome" value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="codigo">Código</label>
                        <input type="text" id="codigo" name="codigo" value="<?php echo isset($codigo) ? htmlspecialchars($codigo) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="carga_horaria">Carga Horária (horas)</label>
                        <input type="number" id="carga_horaria" name="carga_horaria" value="<?php echo isset($carga_horaria) ? htmlspecialchars($carga_horaria) : ''; ?>" required>
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
                        <label for="metodo_avaliacao">Método de Avaliação</label>
                        <input type="text" id="metodo_avaliacao" name="metodo_avaliacao" value="<?php echo isset($metodo_avaliacao) ? htmlspecialchars($metodo_avaliacao) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="descricao_avaliacao">Descrição da Avaliação</label>
                        <textarea id="descricao_avaliacao" name="descricao_avaliacao" rows="3"><?php echo isset($descricao_avaliacao) ? htmlspecialchars($descricao_avaliacao) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Descrição da Disciplina</label>
                        <textarea id="descricao" name="descricao" rows="4"><?php echo isset($descricao) ? htmlspecialchars($descricao) : ''; ?></textarea>
                    </div>

                    <div class="btn-container">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Criar Disciplina
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