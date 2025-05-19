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

// Buscar dados da disciplina
$stmt = $conn->prepare("SELECT * FROM DISCIPLINA WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$disciplina = $result->fetch_assoc();

if(!$disciplina) {
    header("Location: index");
    exit();
}

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
        // Atualizar disciplina
        $stmt = $conn->prepare("UPDATE DISCIPLINA SET nome = ?, codigo = ?, carga_horaria = ?, metodo_avaliacao = ?, descricao_avaliacao = ?, descricao = ?, id_curso = ? WHERE id = ?");
        $stmt->bind_param("ssisssii", $nome, $codigo, $carga_horaria, $metodo_avaliacao, $descricao_avaliacao, $descricao, $id_curso, $id);
        
        if($stmt->execute()) {
            $success_message = "Disciplina atualizada com sucesso!";
            // Atualizar dados da disciplina na variável
            $disciplina = array_merge($disciplina, [
                'nome' => $nome,
                'codigo' => $codigo,
                'carga_horaria' => $carga_horaria,
                'metodo_avaliacao' => $metodo_avaliacao,
                'descricao_avaliacao' => $descricao_avaliacao,
                'descricao' => $descricao,
                'id_curso' => $id_curso
            ]);
        } else {
            $error_message = "Erro ao atualizar disciplina: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Disciplina - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Editar Disciplina</h1>
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
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($disciplina['nome']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="codigo">Código</label>
                        <input type="text" id="codigo" name="codigo" value="<?php echo htmlspecialchars($disciplina['codigo']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="carga_horaria">Carga Horária (horas)</label>
                        <input type="number" id="carga_horaria" name="carga_horaria" value="<?php echo htmlspecialchars($disciplina['carga_horaria']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="id_curso">Curso</label>
                        <select id="id_curso" name="id_curso" required>
                            <option value="">Selecione um curso</option>
                            <?php while($curso = $cursos->fetch_assoc()): ?>
                                <option value="<?php echo $curso['id']; ?>" <?php echo $disciplina['id_curso'] == $curso['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($curso['nome']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="metodo_avaliacao">Método de Avaliação</label>
                        <input type="text" id="metodo_avaliacao" name="metodo_avaliacao" value="<?php echo htmlspecialchars($disciplina['metodo_avaliacao']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="descricao_avaliacao">Descrição da Avaliação</label>
                        <textarea id="descricao_avaliacao" name="descricao_avaliacao" rows="3"><?php echo htmlspecialchars($disciplina['descricao_avaliacao']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Descrição da Disciplina</label>
                        <textarea id="descricao" name="descricao" rows="4"><?php echo htmlspecialchars($disciplina['descricao']); ?></textarea>
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
                    <h2><i class="fas fa-info-circle"></i> Informações da Disciplina</h2>
                    <div class="info-content">
                        <p><strong>ID da Disciplina:</strong> <?php echo htmlspecialchars($disciplina['id']); ?></p>
                        <p><strong>Código:</strong> <?php echo htmlspecialchars($disciplina['codigo']); ?></p>
                        <p><strong>Carga Horária:</strong> <?php echo htmlspecialchars($disciplina['carga_horaria']); ?>h</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 