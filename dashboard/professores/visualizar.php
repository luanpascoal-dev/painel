<?php
session_start();

include '../assets/layouts/verify.php';

if(!isset($_GET['id'])) {
    header("Location: ./");
    exit();
}

$id = $_GET['id'];

// Buscar dados do professor
$query = "SELECT p.*, u.nome, u.email, u.usuario, u.nivel_acesso, u.status, u.data_cadastro
          FROM PROFESSOR p 
          INNER JOIN usuarios u ON p.id_usuario = u.id 
          WHERE p.id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$professor = $stmt->get_result()->fetch_assoc();

if(!$professor) {
    header("Location: ./");
    exit();
}

// Buscar turmas do professor
$query_turmas = "SELECT t.*, c.nome as nome_curso 
                FROM TURMA t 
                INNER JOIN CURSO c ON t.id_curso = c.id 
                WHERE t.id IN (SELECT id_turma FROM AULA WHERE id_professor = ?)
                ORDER BY t.data_inicio DESC";
$stmt = $conn->prepare($query_turmas);
$stmt->bind_param("i", $id);
$stmt->execute();
$turmas = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Professor - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Detalhes do Professor</h1>
                <div class="btn-group">
                    <a href="./" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <a href="editar?id=<?php echo $id; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
            </div>

            <div class="profile-container">
                <!-- Informações Básicas -->
                <div class="profile-card">
                    <div class="profile-header">
                        <i class="fas fa-user-circle fa-4x"></i>
                        <h2><?php echo htmlspecialchars($professor['nome']); ?></h2>
                        <span class="badge <?php echo $professor['status'] ? 'badge-admin' : 'badge-user'; ?>">
                            <?php echo $professor['status'] ? 'Ativo' : 'Inativo'; ?>
                        </span>
                    </div>
                    <div class="profile-info">
                        <p><strong>Usuário:</strong> <?php echo htmlspecialchars($professor['usuario']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($professor['email']); ?></p>
                        <p><strong>CPF:</strong> <?php echo htmlspecialchars($professor['CPF']); ?></p>
                        <p><strong>Data de Cadastro:</strong> <?php echo date('d/m/Y', strtotime($professor['data_cadastro'])); ?></p>
                    </div>
                </div>

                <!-- Turmas Atuais -->
                <div class="profile-card">
                    <h3><i class="fas fa-chalkboard"></i> Turmas Atuais</h3>
                    <div class="turmas-grid">
                        <?php 
                        $turmas_ativas = 0;
                        while($turma = $turmas->fetch_assoc()): 
                            if(strtotime($turma['data_final']) > time()):
                                $turmas_ativas++;
                        ?>
                            <div class="turma-card">
                                <h4><?php echo htmlspecialchars($turma['nome']); ?></h4>
                                <p><strong>Curso:</strong> <?php echo htmlspecialchars($turma['nome_curso']); ?></p>
                                <p><strong>Período:</strong> <?php echo date('d/m/Y', strtotime($turma['data_inicio'])); ?> - <?php echo date('d/m/Y', strtotime($turma['data_final'])); ?></p>
                                <p><strong>Carga Horária:</strong> <?php echo $turma['carga_horaria']; ?>h</p>
                            </div>
                        <?php 
                            endif;
                        endwhile; 
                        if($turmas_ativas == 0):
                        ?>
                            <p class="no-data">Nenhuma turma ativa no momento.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Histórico de Turmas -->
                <div class="profile-card">
                    <h3><i class="fas fa-history"></i> Histórico de Turmas</h3>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Turma</th>
                                    <th>Curso</th>
                                    <th>Período</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $turmas->data_seek(0); // Reinicia o ponteiro do resultado
                                while($turma = $turmas->fetch_assoc()): 
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($turma['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($turma['nome_curso']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($turma['data_inicio'])) . ' - ' . date('d/m/Y', strtotime($turma['data_final'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo strtotime($turma['data_final']) > time() ? 'badge-admin' : 'badge-user'; ?>">
                                            <?php echo strtotime($turma['data_final']) > time() ? 'Em Andamento' : 'Concluída'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 