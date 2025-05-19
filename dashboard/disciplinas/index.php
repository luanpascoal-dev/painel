<?php
session_start();

include '../assets/layouts/verify.php';

// Buscar todas as disciplinas
$query_disciplinas = "SELECT d.*, c.nome as nome_curso FROM DISCIPLINA d 
                     LEFT JOIN CURSO c ON d.id_curso = c.id 
                     ORDER BY d.id DESC";
$result_disciplinas = $conn->query($query_disciplinas);

// Contadores
$total_disciplinas = $result_disciplinas->num_rows;
$disciplinas_com_avaliacao = $conn->query("SELECT COUNT(*) as count FROM DISCIPLINA WHERE metodo_avaliacao IS NOT NULL")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disciplinas - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Gerenciar Disciplinas</h1>
                <a href="novo" class="btn-new">
                    <i class="fas fa-plus"></i> Nova Disciplina
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
                    <i class="fas fa-book-open"></i>
                    <h3>Total de Disciplinas</h3>
                    <p><?php echo $total_disciplinas; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-tasks"></i>
                    <h3>Com Avaliação</h3>
                    <p><?php echo $disciplinas_com_avaliacao; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3>Carga Horária Total</h3>
                    <p><?php 
                        $total_horas = $conn->query("SELECT SUM(carga_horaria) as total FROM DISCIPLINA")->fetch_assoc()['total'];
                        echo $total_horas ? $total_horas . 'h' : '0h';
                    ?></p>
                </div>
            </div>

            <!-- Tabela de disciplinas -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Código</th>
                            <th>Curso</th>
                            <th>Carga Horária</th>
                            <th>Método Avaliação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($disciplina = $result_disciplinas->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($disciplina['id']); ?></td>
                            <td><?php echo htmlspecialchars($disciplina['nome']); ?></td>
                            <td><?php echo htmlspecialchars($disciplina['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($disciplina['nome_curso']); ?></td>
                            <td><?php echo htmlspecialchars($disciplina['carga_horaria']) . 'h'; ?></td>
                            <td><?php echo htmlspecialchars($disciplina['metodo_avaliacao']); ?></td>
                            <td>
                                <a href="editar?id=<?php echo $disciplina['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <a href="#" onclick="showDeleteConfirmation(<?php echo $disciplina['id']; ?>)" class="btn btn-danger btn-sm">
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
            <p>Tem certeza que deseja excluir esta disciplina?</p>
            <div class="modal-buttons">
                <button onclick="deleteDisciplina()" class="btn btn-danger">Sim, Excluir</button>
                <button onclick="hideDeleteConfirmation()" class="btn btn-secondary">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        let disciplinaIdToDelete = null;

        function showDeleteConfirmation(disciplinaId) {
            disciplinaIdToDelete = disciplinaId;
            document.getElementById('deleteConfirmModal').style.display = 'block';
        }

        function hideDeleteConfirmation() {
            document.getElementById('deleteConfirmModal').style.display = 'none';
        }

        function deleteDisciplina() {
            if (disciplinaIdToDelete) {
                window.location.href = 'excluir?id=' + disciplinaIdToDelete;
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