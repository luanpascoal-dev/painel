<?php
session_start();

include '../assets/layouts/verify.php';

// Buscar todos os cursos
$query_cursos = "SELECT * FROM CURSO ORDER BY data_criacao DESC";
$result_cursos = $conn->query($query_cursos);

// Contadores
$total_cursos = $result_cursos->num_rows;
$cursos_ativos = $conn->query("SELECT COUNT(*) as count FROM CURSO WHERE status = 1")->fetch_assoc()['count'];
$cursos_inativos = $total_cursos - $cursos_ativos;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Gerenciar Cursos</h1>
                <a href="novo" class="btn-new">
                    <i class="fas fa-plus"></i> Novo Curso
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
                    <i class="fas fa-book"></i>
                    <h3>Total de Cursos</h3>
                    <p><?php echo $total_cursos; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Cursos Ativos</h3>
                    <p><?php echo $cursos_ativos; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-times-circle"></i>
                    <h3>Cursos Inativos</h3>
                    <p><?php echo $cursos_inativos; ?></p>
                </div>
            </div>

            <!-- Tabela de cursos -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Unidade</th>
                            <th>Duração</th>
                            <th>Status</th>
                            <th>Data Criação</th>
                            <th>Última Atualização</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($curso = $result_cursos->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($curso['id']); ?></td>
                            <td><?php echo htmlspecialchars($curso['nome']); ?></td>
                            <td><?php echo htmlspecialchars($curso['unidade']); ?></td>
                            <td><?php echo htmlspecialchars($curso['duracao']); ?></td>
                            <td>
                                <span class="badge <?php echo $curso['status'] ? 'badge-admin' : 'badge-user'; ?>">
                                    <?php echo $curso['status'] ? 'Ativo' : 'Inativo'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($curso['data_criacao'])); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($curso['ultima_atualizacao'])); ?></td>
                            <td>
                                <a href="editar?id=<?php echo $curso['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="#" onclick="showDeleteConfirmation(<?php echo $curso['id']; ?>)" class="btn btn-danger btn-sm">
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
            <p>Tem certeza que deseja excluir este curso?</p>
            <div class="modal-buttons">
                <button onclick="deleteCurso()" class="btn btn-danger">Sim, Excluir</button>
                <button onclick="hideDeleteConfirmation()" class="btn btn-secondary">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        let cursoIdToDelete = null;

        function showDeleteConfirmation(cursoId) {
            cursoIdToDelete = cursoId;
            document.getElementById('deleteConfirmModal').style.display = 'block';
        }

        function hideDeleteConfirmation() {
            document.getElementById('deleteConfirmModal').style.display = 'none';
        }

        function deleteCurso() {
            if (cursoIdToDelete) {
                window.location.href = 'excluir?id=' + cursoIdToDelete;
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