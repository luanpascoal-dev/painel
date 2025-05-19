<?php
session_start();

include '../assets/layouts/verify.php';

// Buscar todos os alunos com seus dados de usuário
$query_alunos = "SELECT a.*, u.nome, u.email, u.usuario, u.nivel_acesso, u.status 
                 FROM ALUNO a 
                 INNER JOIN usuarios u ON a.id_usuario = u.id 
                 ORDER BY u.nome";
$result_alunos = $conn->query($query_alunos);

// Contadores
$total_alunos = $result_alunos->num_rows;
$alunos_ativos = $conn->query("SELECT COUNT(*) as count FROM ALUNO a 
                              INNER JOIN usuarios u ON a.id_usuario = u.id 
                              WHERE a.status = 1")->fetch_assoc()['count'];


// Parâmetros de filtro e pesquisa
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : 'todos';
$turma = isset($_GET['turma']) ? $_GET['turma'] : 'todas';
$order = isset($_GET['order']) ? $_GET['order'] : 'nome_asc';

// Construir a query base
$sql = "SELECT a.*, u.nome, u.email, u.usuario, t.nome as nome_turma, m.RM as matricula
        FROM aluno a 
        LEFT JOIN usuarios u ON a.id_usuario = u.id 
        LEFT JOIN matricula m ON a.id_usuario = m.id_aluno
        LEFT JOIN turma t ON m.id_turma = t.id WHERE 1=1";

// Adicionar condições de pesquisa
if ($search) {
    $sql .= " AND (u.nome LIKE ? OR u.email LIKE ? OR u.usuario LIKE ? OR m.RM LIKE ?)";
}

if ($status !== 'todos') {
    $sql .= " AND a.status = ?";
}

if ($turma !== 'todas') {
    $sql .= " AND m.id_turma = ?";
}

// Ordenação
switch ($order) {
    case 'nome_desc':
        $sql .= " ORDER BY u.nome DESC";
        break;
    case 'matricula_asc':
        $sql .= " ORDER BY m.RM ASC";
        break;
    case 'matricula_desc':
        $sql .= " ORDER BY m.RM DESC";
        break;
    default:
        $sql .= " ORDER BY u.nome ASC";
}

// Preparar e executar a query
$stmt = $conn->prepare($sql);

// Bind dos parâmetros
$paramTypes = '';
$paramValues = [];

if ($search) {
    $text = "%$search%";
    $paramTypes .= 'ssss';
    array_push($paramValues, $text, $text, $text, $text);
}

if ($status !== 'todos') {
    $paramTypes .= 'i';
    array_push($paramValues, $status === 'ativo' ? 1 : 0);
}

if ($turma !== 'todas') {
    $paramTypes .= 'i';
    array_push($paramValues, $turma);
}

if (!empty($paramValues)) {
    $stmt->bind_param($paramTypes, ...$paramValues);
}

$stmt->execute();
$result = $stmt->get_result();

// Buscar todas as turmas para o filtro
$sql_turmas = "SELECT id, nome FROM turma ORDER BY nome";
$turmas_result = $conn->query($sql_turmas);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alunos - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <div class="main-content">
            <div class="header">
                <h1>Gerenciar Alunos</h1>
                <div class="btn-group">
                    <a href="vincular" class="btn btn-primary">
                        <i class="fas fa-link"></i> Vincular Usuário
                    </a>
                    <a href="novo" class="btn-new">
                        <i class="fas fa-plus"></i> Novo Aluno
                    </a>
                </div>
                
            </div>
            <?php include '../assets/layouts/alerts.php'; ?>
            <!-- Cards de estatísticas -->
            <div class="stats">
                <div class="stat-card">
                    <i class="fas fa-user-graduate"></i>
                    <h3>Total de Alunos</h3>
                    <p><?php echo $total_alunos; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-check"></i>
                    <h3>Alunos Ativos</h3>
                    <p><?php echo $alunos_ativos; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <h3>Matrículas Ativas</h3>
                    <p><?php 
                        $matriculas_ativas = $conn->query("SELECT COUNT(*) as count FROM MATRICULA WHERE status = 1")->fetch_assoc()['count'];
                        echo $matriculas_ativas;
                    ?></p>
                </div>
            </div>
            <div class="filter-section">
                <form method="GET" class="filter-form" id="filterForm">
                    <div class="search-group">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search"></i>
                            <input 
                                type="text" 
                                name="search" 
                                placeholder="Buscar por nome, email, usuário ou matrícula..."
                                value="<?php echo htmlspecialchars($search); ?>"
                            >
                        </div>
                    </div>
                    <div class="filter-groups">
                        <div class="filter-group">
                            <label for="status">Status:</label>
                            <select name="status" id="status">
                                <option value="todos" <?php echo $status === 'todos' ? 'selected' : ''; ?>>Todos</option>
                                <option value="ativo" <?php echo $status === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="inativo" <?php echo $status === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="turma">Turma:</label>
                            <select name="turma" id="turma">
                                <option value="todas">Todas as Turmas</option>
                                <?php while ($turma_row = $turmas_result->fetch_assoc()): ?>
                                    <option value="<?php echo $turma_row['id']; ?>" 
                                            <?php echo $turma == $turma_row['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($turma_row['nome']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
            

            <div class="filter-group">
                            <label for="order">Ordenar por:</label>
                            <select name="order" id="order">
                                <option value="nome_asc" <?php echo $order === 'nome_asc' ? 'selected' : ''; ?>>Nome (A-Z)</option>
                                <option value="nome_desc" <?php echo $order === 'nome_desc' ? 'selected' : ''; ?>>Nome (Z-A)</option>
                                <option value="matricula_asc" <?php echo $order === 'matricula_asc' ? 'selected' : ''; ?>>Matrícula (Crescente)</option>
                                <option value="matricula_desc" <?php echo $order === 'matricula_desc' ? 'selected' : ''; ?>>Matrícula (Decrescente)</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="limparFiltros()">
                            <i class="fas fa-times"></i> Limpar
                        </button>
                    </div>
            <div class="table-container">
                <?php if ($result->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>RA</th>
                                <th>Nome</th>
                                <th>Usuário</th>
                                <th>Email</th>
                                <th>Matrícula</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($aluno = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($aluno['RA']) ?: '<span class="na-text">N/A</span>'; ?></td>
                                    <td><?php echo htmlspecialchars($aluno['nome']) ?: '<span class="na-text">N/A</span>'; ?></td>
                                    <td><?php echo htmlspecialchars($aluno['usuario']) ?: '<span class="na-text">N/A</span>'; ?></td>
                                    <td><?php echo htmlspecialchars($aluno['email']) ?: '<span class="na-text">N/A</span>'; ?></td>
                                    <td><?php echo htmlspecialchars($aluno['matricula']) ?: '<span class="na-text">N/A</span>'; ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $aluno['status'] ? 'active' : 'inactive'; ?>">
                                            <?php echo $aluno['status'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                    <td class="actions">
                                        <a href="visualizar?id=<?php echo $aluno['id_usuario']; ?>" class="btn btn-primary btn-sm" title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="editar?id=<?php echo $aluno['id_usuario']; ?>" class="btn bg-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button onclick="confirmarExclusao(<?php echo $aluno['id_usuario']; ?>)" class="btn btn-danger btn-sm" title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <p>Nenhum aluno encontrado com os filtros selecionados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <script>
    function limparFiltros() {
        document.querySelector('input[name="search"]').value = '';
        document.querySelector('select[name="status"]').value = 'todos';
        document.querySelector('select[name="turma"]').value = 'todas';
        document.querySelector('select[name="order"]').value = 'nome_asc';
        document.getElementById('filterForm').submit();
    }

    function confirmarExclusao(id) {
        if (confirm('Tem certeza que deseja excluir este aluno?')) {
            window.location.href = 'excluir?id=' + id;
        }
    }
    </script>
</body>
</html> 