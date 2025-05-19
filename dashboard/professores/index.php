<?php
session_start();

include '../assets/layouts/verify.php';

// Buscar todos os professores com seus dados de usuário
$query_professores = "SELECT p.*, u.nome, u.email, u.usuario, u.nivel_acesso, u.status,
                     (SELECT COUNT(DISTINCT CONCAT(l.id_disciplina, '-', l.id_turma)) FROM LECIONA l WHERE l.id_professor = p.id_usuario) as total_disciplinas
                     FROM PROFESSOR p 
                     INNER JOIN usuarios u ON p.id_usuario = u.id 
                     ORDER BY u.nome";
$result = $conn->query($query_professores);

// Contadores
$total_professores = $result->num_rows;
$professores_ativos = $conn->query("SELECT COUNT(*) as count FROM PROFESSOR p 
                                   INNER JOIN usuarios u ON p.id_usuario = u.id 
                                   WHERE u.status = 1")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professores - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Gerenciar Professores</h1>
                <div class="btn-group">
                    <a href="vincular" class="btn btn-primary">
                        <i class="fas fa-link"></i> Vincular Usuário
                    </a>
                    <a href="novo" class="btn-new">
                        <i class="fas fa-plus"></i> Novo Professor
                    </a>
                </div>
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
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h3>Total de Professores</h3>
                    <p><?php echo $total_professores; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-check"></i>
                    <h3>Professores Ativos</h3>
                    <p><?php echo $professores_ativos; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <h3>Turmas Ativas</h3>
                    <p><?php 
                        $turmas_ativas = $conn->query("SELECT COUNT(DISTINCT t.id) as count 
                                                     FROM TURMA t 
                                                     WHERE t.data_final > NOW()")->fetch_assoc()['count'];
                        echo $turmas_ativas;
                    ?></p>
                </div>
            </div>

            <!-- Filtros de Busca -->
            <div class="search-filters">
                <input type="text" id="searchInput" placeholder="Buscar professor..." class="search-input">
                <select id="statusFilter" class="filter-select">
                    <option value="">Todos os Status</option>
                    <option value="1">Ativos</option>
                    <option value="0">Inativos</option>
                </select>
            </div>

            <!-- Tabela de professores -->
            <div class="table-container">
                <?php if ($result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Usuário</th>
                                <th>Email</th>
                                <th>Disciplinas</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($professor = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($professor['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($professor['usuario']); ?></td>
                                    <td><?php echo htmlspecialchars($professor['email']); ?></td>
                                    <td>
                                        <?php echo $professor['total_disciplinas']; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $professor['status'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $professor['status'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="visualizar?id=<?php echo $professor['id_usuario']; ?>" class="btn btn-primary btn-sm" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="atribuir_disciplinas?id=<?php echo $professor['id_usuario']; ?>" class="btn btn-success btn-sm" title="Atribuir Disciplinas">
                                            <i class="fas fa-book"></i>
                                        </a>
                                        <a href="editar?id=<?php echo $professor['id_usuario']; ?>" class="btn bg-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmarExclusao(<?php echo $professor['id_usuario']; ?>)" class="btn btn-danger btn-sm" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-user-slash"></i>
                        <p>Nenhum professor encontrado.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação -->
    <div id="deleteConfirmModal" class="modal">
        <div class="modal-content">
            <h3>Confirmar Exclusão</h3>
            <p>Tem certeza que deseja excluir este professor?</p>
            <div class="modal-buttons">
                <button onclick="deleteProfessor()" class="btn btn-danger">Sim, Excluir</button>
                <button onclick="hideDeleteConfirmation()" class="btn btn-secondary">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        let professorIdToDelete = null;

        // Função de busca
        document.getElementById('searchInput').addEventListener('keyup', function() {
            filterTable();
        });

        // Função de filtro por status
        document.getElementById('statusFilter').addEventListener('change', function() {
            filterTable();
        });


        function filterTable() {
            const searchText = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.getElementById('professoresTable').getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let row of rows) {
                const nome = row.cells[1].textContent.toLowerCase();
                const usuario = row.cells[2].textContent.toLowerCase();
                const email = row.cells[3].textContent.toLowerCase();
                const cpf = row.cells[4].textContent;
                const status = row.cells[5].textContent.trim() === 'Ativo' ? '1' : '0';

                const matchesSearch = nome.includes(searchText) || 
                                    usuario.includes(searchText) || 
                                    email.includes(searchText) || 
                                    cpf.includes(searchText);
                                    
                const matchesStatus = statusFilter === '' || status === statusFilter;

                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            }
        }

        function showDeleteConfirmation(professorId) {
            professorIdToDelete = professorId;
            document.getElementById('deleteConfirmModal').style.display = 'block';
        }

        function hideDeleteConfirmation() {
            document.getElementById('deleteConfirmModal').style.display = 'none';
        }

        function deleteProfessor() {
            if (professorIdToDelete) {
                window.location.href = 'excluir?id=' + professorIdToDelete;
            }
        }

        // Fechar modal quando clicar fora dele
        window.onclick = function(event) {
            let modal = document.getElementById('deleteConfirmModal');
            if (event.target == modal) {
                hideDeleteConfirmation();
            }
        }

        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir este professor?')) {
                window.location.href = `excluir.php?id=${id}`;
            }
        }
    </script>
</body>
</html> 