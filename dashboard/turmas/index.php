<?php
session_start();

include '../assets/layouts/verify.php';

// Buscar todas as turmas
$query_turmas = "SELECT t.*, c.nome as nome_curso FROM TURMA t 
                 LEFT JOIN CURSO c ON t.id_curso = c.id 
                 ORDER BY t.data_inicio DESC";
$result_turmas = $conn->query($query_turmas);

// Contadores
$total_turmas = $result_turmas->num_rows;
$turmas_ativas = $conn->query("SELECT COUNT(*) as count FROM TURMA WHERE data_final > NOW()")->fetch_assoc()['count'];
$turmas_encerradas = $total_turmas - $turmas_ativas;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Turmas - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Gerenciar Turmas</h1>
                <a href="novo" class="btn-new">
                    <i class="fas fa-plus"></i> Nova Turma
                </a>
            </div>

            <!-- Mensagens de sucesso/erro -->
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Cards de estatísticas -->
            <div class="stats">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3>Total de Turmas</h3>
                    <p><?php echo $total_turmas; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-graduate"></i>
                    <h3>Turmas Ativas</h3>
                    <p><?php echo $turmas_ativas; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-clock"></i>
                    <h3>Turmas Encerradas</h3>
                    <p><?php echo $turmas_encerradas; ?></p>
                </div>
            </div>

            <!-- Tabela de turmas -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Curso</th>
                            <th>Início</th>
                            <th>Término</th>
                            <th>Carga Horária</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($turma = $result_turmas->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($turma['id']); ?></td>
                            <td><?php echo htmlspecialchars($turma['nome']); ?></td>
                            <td><?php echo htmlspecialchars($turma['nome_curso']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($turma['data_inicio'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($turma['data_final'])); ?></td>
                            <td><?php echo htmlspecialchars($turma['carga_horaria']) . 'h'; ?></td>
                            <td>
                                <span class="badge <?php echo strtotime($turma['data_final']) > time() ? 'badge-admin' : 'badge-user'; ?>">
                                    <?php echo strtotime($turma['data_final']) > time() ? 'Em Andamento' : 'Encerrada'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="editar?id=<?php echo $turma['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="#" onclick="showDeleteConfirmation(<?php echo $turma['id']; ?>)" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Excluir
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação -->
    <div id="deleteConfirmModal" class="modal">
        <div class="modal-content">
            <h3>Confirmar Exclusão</h3>
            <p>Tem certeza que deseja excluir esta turma?</p>
            <div class="modal-buttons">
                <button onclick="deleteTurma()" class="btn btn-danger">Sim, Excluir</button>
                <button onclick="hideDeleteConfirmation()" class="btn btn-secondary">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        let turmaIdToDelete = null;

        function showDeleteConfirmation(turmaId) {
            turmaIdToDelete = turmaId;
            document.getElementById('deleteConfirmModal').style.display = 'block';
        }

        function hideDeleteConfirmation() {
            document.getElementById('deleteConfirmModal').style.display = 'none';
        }

        function deleteTurma() {
            if (turmaIdToDelete) {
                window.location.href = 'excluir?id=' + turmaIdToDelete;
            }
        }

        // Fechar modal quando clicar fora dele
        window.onclick = function(event) {
            let modal = document.getElementById('deleteConfirmModal');
            if (event.target == modal) {
                hideDeleteConfirmation();
            }
        }
    </script>
</body>
</html> 