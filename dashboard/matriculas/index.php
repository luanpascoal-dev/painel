<?php
session_start();

include '../assets/layouts/verify.php';

// Buscar todas as matrículas com informações relacionadas
$query = "SELECT m.*, 
          a.id_usuario as aluno_id,
          u.nome as nome_aluno,
          t.nome as nome_turma,
          t.id as turma_id
          FROM MATRICULA m
          INNER JOIN ALUNO a ON m.id_aluno = a.id_usuario
          INNER JOIN usuarios u ON a.id_usuario = u.id
          INNER JOIN TURMA t ON m.id_turma = t.id
          ORDER BY m.data_hora DESC";

$result = $conn->query($query);

// Contadores
$total_matriculas = $result->num_rows;
$matriculas_ativas = $conn->query("SELECT COUNT(*) as count FROM MATRICULA WHERE status = 1")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matrículas - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Gerenciar Matrículas</h1>
                <div class="btn-group">
                    <a href="novo" class="btn-new">
                        <i class="fas fa-plus"></i> Nova Matrícula
                    </a>
                </div>
            </div>

            <?php include '../assets/layouts/alerts.php'; ?>

            <!-- Cards de estatísticas -->
            <div class="stats">
                <div class="stat-card">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>Total de Matrículas</h3>
                    <p><?php echo $total_matriculas; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3>Matrículas Ativas</h3>
                    <p><?php echo $matriculas_ativas; ?></p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filter-section">
                <form method="GET" class="filter-form" id="filterForm">
                    <div class="search-group">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search"></i>
                            <input 
                                type="text" 
                                name="search" 
                                placeholder="Buscar por aluno, RM ou turma..."
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                oninput="this.form.submit()"
                            >
                            <button type="button" class="btn btn-clear" onclick="limparFiltros()" title="Limpar filtros">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-container">
                <?php if ($result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>RM</th>
                                <th>Aluno</th>
                                <th>Turma</th>
                                <th>Data da Matrícula</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($matricula = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($matricula['RM']); ?></td>
                                    <td><?php echo htmlspecialchars($matricula['nome_aluno']); ?></td>
                                    <td><?php echo htmlspecialchars($matricula['nome_turma']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($matricula['data_hora'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $matricula['status'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $matricula['status'] ? 'Ativa' : 'Inativa'; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="editar?RM=<?php echo $matricula['RM']; ?>" class="btn bg-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmarCancelamento(<?php echo $matricula['aluno_id']; ?>, <?php echo $matricula['turma_id']; ?>)" class="btn btn-danger btn-sm" title="Cancelar Matrícula">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-clipboard-list"></i>
                        <p>Nenhuma matrícula encontrada.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function limparFiltros() {
        document.querySelector('input[name="search"]').value = '';
        document.getElementById('filterForm').submit();
    }

    function confirmarCancelamento(idAluno, idTurma) {
        if (confirm('Tem certeza que deseja cancelar esta matrícula?')) {
            window.location.href = `cancelar.php?aluno=${idAluno}&turma=${idTurma}`;
        }
    }
    </script>
</body>
</html> 