<?php
session_start();

include '../assets/layouts/verify.php';

if(!isset($_GET['id'])) {
    header("Location: ./");
    exit();
}

$id = $_GET['id'];

// Buscar dados do aluno
$query = "SELECT a.*, u.nome, u.email, u.usuario, u.nivel_acesso, u.status, u.data_cadastro
          FROM ALUNO a 
          INNER JOIN usuarios u ON a.id_usuario = u.id 
          WHERE a.id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$aluno = $stmt->get_result()->fetch_assoc();

if(!$aluno) {
    header("Location: ./");
    exit();
}

// Buscar todas as matrículas do aluno
$sql_matriculas = "SELECT m.*, c.nome as nome_curso, t.nome as nome_turma
                  FROM matricula m 
                  LEFT JOIN turma t ON m.id_turma = t.id 
                  LEFT JOIN curso c ON t.id_curso = c.id 
                  WHERE m.id_aluno = ?";

$stmt_matriculas = $conn->prepare($sql_matriculas);
$stmt_matriculas->bind_param("i", $id);
$stmt_matriculas->execute();
$result_matriculas = $stmt_matriculas->get_result();
$matriculas = $result_matriculas->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Aluno - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Detalhes do Aluno</h1>
                <div class="btn-group">
                    <a href="./" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
            <div class="card">
                <div class="card-content">
                    <div class="info-section">
                        <div class="info-group">
                            <h3><i class="fas fa-user"></i> Informações Pessoais</h3>
                            <div class="info-item">
                                <label>Nome:</label>
                                <span><?php echo htmlspecialchars($aluno['nome']); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Email:</label>
                                <span><?php echo htmlspecialchars($aluno['email']); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Usuário:</label>
                                <span><?php echo htmlspecialchars($aluno['usuario']); ?></span>
                            </div>
                        </div>

                        <div class="info-group">
                            <h3><i class="fas fa-graduation-cap"></i> Informações Acadêmicas</h3>
                            <div class="info-item">
                                <label>Nascimento:</label>
                                <span><?php echo date('d/m/Y', strtotime($aluno['data_nascimento'])); ?></span>
                            </div>
                            <div class="info-item">
                                <label>Status:</label>
                                <span class="status-badge <?php echo $aluno['status'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $aluno['status'] ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </div>
                            
                            <div class="matriculas-section">
                                <h4><i class="fas fa-book"></i> Matrículas</h4>
                                <?php if (count($matriculas) > 0): ?>
                                    <div class="matriculas-grid">
                                        <?php foreach ($matriculas as $matricula): ?>
                                            <div class="matricula-card">
                                                <div class="matricula-header">
                                                    <span class="matricula-id"><?php echo htmlspecialchars($matricula['RM']); ?></span>
                                                    <span class="status-badge <?php echo $matricula['status'] ? 'active' : 'inactive'; ?>">
                                                        <?php echo $matricula['status'] ? 'Ativa' : 'Inativa'; ?>
                                                    </span>
                                                </div>
                                                <div class="matricula-body">
                                                    <p><strong>Curso:</strong> <?php echo htmlspecialchars($matricula['nome_curso']); ?></p>
                                                    <p><strong>Turma:</strong> <?php echo htmlspecialchars($matricula['nome_turma']); ?></p>
                                                    <p><strong>Data:</strong> <?php echo date('d/m/Y', strtotime($matricula['data_hora'])); ?></p>
                                                </div>
                                                <div class="matricula-actions">
                                                    <a href="../matriculas/editar?RM=<?php echo $matricula['RM']; ?>" class="btn-small btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button onclick="confirmarExclusaoMatricula(<?php echo $matricula['RM']; ?>)" class="btn-small btn-danger">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="no-matriculas">Nenhuma matrícula encontrada</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card-actions">
                        <a href="editar.php?id=<?php echo $aluno['id_usuario']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <button onclick="confirmarExclusao(<?php echo $aluno['id_usuario']; ?>)" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Excluir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function confirmarExclusao(id) {
        if (confirm('Tem certeza que deseja excluir este aluno?')) {
            window.location.href = 'excluir?id=' + id;
        }
    }

    function confirmarExclusaoMatricula(id) {
        if (confirm('Tem certeza que deseja excluir esta matrícula?')) {
            window.location.href = '../matriculas/excluir?id=' + id;
        }
    }
    </script>

    <style>
    .card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin: 20px 0;
    }

    .card-content {
        padding: 24px;
    }

    .info-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
    }

    .info-group {
        margin-bottom: 24px;
    }

    .info-group h3 {
        color: #333;
        font-size: 1.2rem;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 2px solid #f0f0f0;
    }

    .info-group h3 i {
        margin-right: 8px;
        color: #4a90e2;
    }

    .info-item {
        display: flex;
        margin-bottom: 12px;
        padding: 8px;
        background: #f8f9fa;
        border-radius: 4px;
    }

    .info-item label {
        font-weight: 600;
        min-width: 100px;
        color: #666;
    }

    .info-item span {
        color: #333;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .status-badge.active {
        background: #e3f2fd;
        color: #1976d2;
    }   

    .status-badge.inactive {
        background: #ffebee;
        color: #d32f2f;
    }

    .card-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
        padding-top: 16px;
        border-top: 1px solid #eee;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 4px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn i {
        font-size: 1rem;
    }

    .btn-primary {
        background: #4a90e2;
        color: white;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    .matriculas-section {
        margin-top: 20px;
    }

    .matriculas-section h4 {
        color: #333;
        font-size: 1.1rem;
        margin-bottom: 16px;
    }

    .matriculas-section h4 i {
        margin-right: 8px;
        color: #4a90e2;
    }

    .matriculas-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 16px;
    }

    .matricula-card {
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .matricula-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .matricula-header {
        background: #f8f9fa;
        padding: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #e0e0e0;
    }

    .matricula-id {
        font-weight: 600;
        color: #4a90e2;
    }

    .matricula-body {
        padding: 12px;
    }

    .matricula-body p {
        margin: 8px 0;
        font-size: 0.9rem;
    }

    .matricula-actions {
        padding: 12px;
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        background: #f8f9fa;
        border-top: 1px solid #e0e0e0;
    }

    .btn-small {
        padding: 6px 10px;
        font-size: 0.9rem;
        border-radius: 4px;
        cursor: pointer;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .no-matriculas {
        color: #666;
        font-style: italic;
        padding: 16px;
        text-align: center;
        background: #f8f9fa;
        border-radius: 4px;
    }
    </style>
</body>
</html>