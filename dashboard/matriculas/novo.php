<?php
session_start();
include '../assets/layouts/verify.php';

// Buscar alunos sem matrícula ativa
$query_alunos = "SELECT a.id_usuario, u.nome 
                 FROM ALUNO a 
                 INNER JOIN usuarios u ON a.id_usuario = u.id 
                 WHERE a.id_usuario 
                 ORDER BY u.nome";
$alunos = $conn->query($query_alunos);

// Buscar turmas ativas
$query_turmas = "SELECT id, nome FROM TURMA ORDER BY nome";
$turmas = $conn->query($query_turmas);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_aluno = $_POST['aluno'];
    $id_turma = $_POST['turma'];

    $query_existente = "SELECT * 
                 FROM MATRICULA 
                 WHERE id_aluno = $id_aluno AND id_turma = $id_turma";
    $existente = $conn->query($query_existente);

    if ($existente->num_rows > 0) {
        $_SESSION['error'] = "Aluno já matriculado nesta turma!";
        header("Location: ./novo");
        exit;
    }
    
    // Gerar RM único (ano atual + número sequencial)
    $ano = date('Y');
    $query_ultimo_rm = "SELECT MAX(RM) as ultimo_rm FROM MATRICULA WHERE RM LIKE '$ano%'";
    $resultado = $conn->query($query_ultimo_rm);
    $ultimo_rm = $resultado->fetch_assoc()['ultimo_rm'];
    
    if ($ultimo_rm) {
        $sequencial = intval(substr($ultimo_rm, -4)) + 1;
    } else {
        $sequencial = 1;
    }
    
    $novo_rm = intval($ano . str_pad($sequencial, 4, '0', STR_PAD_LEFT));

    try {
        $stmt = $conn->prepare("INSERT INTO MATRICULA (id_aluno, id_turma, RM, data_hora, status) VALUES (?, ?, ?, NOW(), 1)");
        $stmt->bind_param("iii", $id_aluno, $id_turma, $novo_rm);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Matrícula realizada com sucesso! RM: " . $novo_rm;
        } else {
            throw new Exception("Erro ao realizar matrícula");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Erro ao realizar matrícula: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Matrícula - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Nova Matrícula</h1>
            </div>

            <?php include '../assets/layouts/alerts.php'; ?>

            <div class="content-box">
                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="aluno">Aluno:</label>
                        <select name="aluno" id="aluno" required>
                            <option value="">Selecione um aluno</option>
                            <?php while ($aluno = $alunos->fetch_assoc()): ?>
                                <option value="<?php echo $aluno['id_usuario']; ?>">
                                    <?php echo htmlspecialchars($aluno['nome']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="turma">Turma:</label>
                        <select name="turma" id="turma" required>
                            <option value="">Selecione uma turma</option>
                            <?php while ($turma = $turmas->fetch_assoc()): ?>
                                <option value="<?php echo $turma['id']; ?>">
                                    <?php echo htmlspecialchars($turma['nome']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <a href="index.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Realizar Matrícula</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html> 