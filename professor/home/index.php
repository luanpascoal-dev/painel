<?php
session_start();
include '../assets/layouts/verify.php';

// Verificar se o usuário é professor
$id_usuario = $_SESSION['id'];
$query_professor = "SELECT p.* FROM PROFESSOR p WHERE p.id_usuario = ?";
$stmt = $conn->prepare($query_professor);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$professor = $stmt->get_result()->fetch_assoc();

if (!$professor) {
    $_SESSION['error'] = "Acesso restrito a professores.";
    header("Location: ../");
    exit;
}

// Buscar estatísticas do professor
$query_stats = "SELECT 
    (SELECT COUNT(DISTINCT a.id) FROM AULA a WHERE a.id_professor = ?) as total_aulas,
    (SELECT COUNT(DISTINCT CONCAT(l.id_disciplina, '-', l.id_turma)) FROM LECIONA l WHERE l.id_professor = ?) as total_disciplinas,
    (SELECT COUNT(DISTINCT av.id) FROM AVALIACAO av 
     INNER JOIN LECIONA l ON av.id_disciplina = l.id_disciplina 
     WHERE l.id_professor = ?) as total_avaliacoes";

$stmt = $conn->prepare($query_stats);
$stmt->bind_param("iii", $professor['id_usuario'], $professor['id_usuario'], $professor['id_usuario']);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área do Professor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .menu-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .menu-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .menu-card i {
            font-size: 3em;
            margin-bottom: 15px;
            color: #2196F3;
        }

        .menu-card h3 {
            margin-bottom: 10px;
            color: #333;
        }

        .menu-card p {
            color: #666;
            font-size: 0.9em;
        }

        .welcome-section {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .welcome-section h2 {
            margin-bottom: 10px;
        }

        .welcome-section p {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Área do Professor</h1>
            </div>

            <?php include '../assets/layouts/alerts.php'; ?>

            <div class="welcome-section">
                <h2>Bem-vindo(a), Professor(a) <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</h2>
                <p>Gerencie suas aulas, avaliações e acompanhe o desempenho dos alunos.</p>
            </div>

            <!-- Cards de estatísticas -->
            <div class="stats">
                <div class="stat-card">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h3>Total de Aulas</h3>
                    <p><?php echo $stats['total_aulas']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <h3>Disciplinas</h3>
                    <p><?php echo $stats['total_disciplinas']; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-tasks"></i>
                    <h3>Avaliações</h3>
                    <p><?php echo $stats['total_avaliacoes']; ?></p>
                </div>
            </div>

            <div class="menu-cards">
                <a href="../aulas" class="menu-card">
                    <i class="fas fa-chalkboard"></i>
                    <h3>Gerenciar Aulas</h3>
                    <p>Registre aulas, faça chamada e gerencie o conteúdo ministrado</p>
                </a>

                <a href="../avaliacoes" class="menu-card">
                    <i class="fas fa-file-alt"></i>
                    <h3>Avaliações e Notas</h3>
                    <p>Crie avaliações e gerencie as notas dos alunos</p>
                </a>

                <a href="../faltas" class="menu-card">
                    <i class="fas fa-user-clock"></i>
                    <h3>Controle de Faltas</h3>
                    <p>Registre e consulte a frequência dos alunos</p>
                </a>

                <a href="../relatorios" class="menu-card">
                    <i class="fas fa-chart-bar"></i>
                    <h3>Relatórios</h3>
                    <p>Visualize relatórios de desempenho e frequência</p>
                </a>
            </div>
        </div>
    </div>
</body>
</html> 