<?php
session_start();
include '../assets/layouts/verify.php';

// Verificar se é professor
$id_usuario = $_SESSION['id'];
$query_professor = "SELECT p.* FROM PROFESSOR p WHERE p.id_usuario = ?";
$stmt = $conn->prepare($query_professor);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$professor = $stmt->get_result()->fetch_assoc();

if (!$professor) {
    $_SESSION['error'] = "Acesso restrito a professores.";
    header("Location: ../../");
    exit;
}

// Verificar se foi passado um ID
$id_avaliacao = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_avaliacao) {
    $_SESSION['error'] = "Avaliação não especificada.";
    header("Location: index.php");
    exit;
}

// Buscar dados da avaliação e verificar se pertence ao professor
$query_avaliacao = "SELECT av.*, d.nome as disciplina_nome
                    FROM AVALIACAO av
                    INNER JOIN DISCIPLINA d ON av.id_disciplina = d.id
                    INNER JOIN LECIONA l ON d.id = l.id_disciplina
                    WHERE av.id = ? AND l.id_professor = ?";
$stmt = $conn->prepare($query_avaliacao);
$stmt->bind_param("ii", $id_avaliacao, $professor['id_usuario']);
$stmt->execute();
$avaliacao = $stmt->get_result()->fetch_assoc();

if (!$avaliacao) {
    $_SESSION['error'] = "Avaliação não encontrada ou sem permissão.";
    header("Location: index.php");
    exit;
}

// Processar o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $tipo = $_POST['tipo'];
    $peso = $_POST['peso'];

    // Validações
    $errors = [];
    
    if (empty($nome)) {
        $errors[] = "O nome da avaliação é obrigatório.";
    }
    
    if (empty($tipo)) {
        $errors[] = "O tipo de avaliação é obrigatório.";
    }
    
    if (empty($peso) || !is_numeric($peso) || $peso <= 0) {
        $errors[] = "O peso deve ser um número maior que zero.";
    }

    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("UPDATE AVALIACAO SET nome = ?, tipo = ?, peso = ? WHERE id = ?");
            $stmt->bind_param("ssdi", $nome, $tipo, $peso, $id_avaliacao);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Avaliação atualizada com sucesso!";
                header("Location: index.php");
                exit;
            } else {
                throw new Exception("Erro ao atualizar avaliação");
            }
        } catch (Exception $e) {
            $errors[] = "Erro ao atualizar avaliação: " . $e->getMessage();
        }
    }
}

// Buscar estatísticas da avaliação
$query_stats = "SELECT 
                COUNT(n.id_aluno) as total_notas,
                COUNT(DISTINCT m.id_aluno) as total_alunos,
                AVG(n.nota) as media_notas
                FROM AVALIACAO av
                LEFT JOIN NOTA n ON av.id = n.id_avaliacao
                LEFT JOIN MATRICULA m ON m.id_turma IN (
                    SELECT id_turma FROM AULA WHERE id_disciplina = av.id_disciplina
                )
                WHERE av.id = ?";
$stmt = $conn->prepare($query_stats);
$stmt->bind_param("i", $id_avaliacao);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Avaliação - Área do Professor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Editar Avaliação</h1>
            </div>

            <?php include '../assets/layouts/alerts.php'; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="content-box">
                <div class="info-header">
                    <h2>Disciplina: <?php echo htmlspecialchars($avaliacao['disciplina_nome']); ?></h2>
                </div>

                <!-- Cards de estatísticas -->
                <div class="stats-mini">
                    <div class="stat-card">
                        <i class="fas fa-users"></i>
                        <h3>Total de Alunos</h3>
                        <p><?php echo $stats['total_alunos']; ?></p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-check-circle"></i>
                        <h3>Notas Lançadas</h3>
                        <p><?php echo $stats['total_notas']; ?></p>
                    </div>
                    <div class="stat-card">
                        <i class="fas fa-chart-line"></i>
                        <h3>Média da Turma</h3>
                        <p><?php echo number_format($stats['media_notas'], 1); ?></p>
                    </div>
                </div>

                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="nome">Nome da Avaliação:</label>
                        <input type="text" 
                               id="nome" 
                               name="nome" 
                               required 
                               value="<?php echo htmlspecialchars($avaliacao['nome']); ?>"
                               placeholder="Ex: Prova 1, Trabalho Final, etc">
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="tipo">Tipo:</label>
                            <select name="tipo" id="tipo" required>
                                <option value="">Selecione o tipo</option>
                                <option value="Prova" <?php echo $avaliacao['tipo'] == 'Prova' ? 'selected' : ''; ?>>Prova</option>
                                <option value="Trabalho" <?php echo $avaliacao['tipo'] == 'Trabalho' ? 'selected' : ''; ?>>Trabalho</option>
                                <option value="Projeto" <?php echo $avaliacao['tipo'] == 'Projeto' ? 'selected' : ''; ?>>Projeto</option>
                                <option value="Apresentação" <?php echo $avaliacao['tipo'] == 'Apresentação' ? 'selected' : ''; ?>>Apresentação</option>
                                <option value="Atividade" <?php echo $avaliacao['tipo'] == 'Atividade' ? 'selected' : ''; ?>>Atividade</option>
                            </select>
                        </div>

                        <div class="form-group flex-1">
                            <label for="peso">Peso:</label>
                            <input type="number" 
                                   id="peso" 
                                   name="peso" 
                                   step="0.1" 
                                   min="0.1" 
                                   required
                                   value="<?php echo htmlspecialchars($avaliacao['peso']); ?>"
                                   placeholder="Ex: 1.0, 2.5, etc">
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
    .content-box {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 20px;
        margin: 20px;
    }

    .info-header {
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }

    .info-header h2 {
        color: #333;
        margin: 0;
        font-size: 1.2em;
    }

    .stats-mini {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .stats-mini .stat-card {
        padding: 15px;
        text-align: center;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .stats-mini .stat-card i {
        font-size: 1.5em;
        color: #2196F3;
        margin-bottom: 5px;
    }

    .stats-mini .stat-card h3 {
        font-size: 0.9em;
        color: #666;
        margin: 5px 0;
    }

    .stats-mini .stat-card p {
        font-size: 1.2em;
        color: #333;
        margin: 0;
        font-weight: 500;
    }

    .form {
        max-width: 800px;
        margin: 0 auto;
    }

    .form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
    }

    .flex-1 {
        flex: 1;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: #333;
        font-weight: 500;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }

    .form-group input:focus,
    .form-group select:focus {
        border-color: #2196F3;
        outline: none;
        box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.1);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    </style>
</body>
</html>
