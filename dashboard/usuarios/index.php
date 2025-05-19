<?php
session_start();

// Verificar se está logado
if(!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
    header("Location: ../../login");
    exit();
}

// Conexão com o banco de dados
include '../../database.php';

// Verificar se o usuário é admin
$stmt = $conn->prepare("SELECT nivel_acesso FROM usuarios WHERE id = ? AND nivel_acesso = 'admin'");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows !== 1) {
    header("Location: ../../index");
    exit();
}

// Buscar todos os usuários
$query_users = "SELECT id, nome, usuario, email, nivel_acesso, data_cadastro FROM usuarios ORDER BY data_cadastro DESC";
$result_users = $conn->query($query_users);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        

        /* Responsividade */
        @media (max-width: 768px) {
            .hamburger {
                display: block;
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding-top: 60px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>  
    <div class="dashboard">

        <!-- Sidebar -->
        <?php include '../assets/layouts/sidebar.php'; ?>

        <!-- Conteúdo Principal -->
        <div class="main-content">
            <!-- Header da página -->
            <div class="header">
                <h1>Gerenciar Usuários</h1>
                <a href="novo_usuario" class="btn-new">
                    <i class="fas fa-plus"></i> Novo Usuário
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
                    <h3>Total de Usuários</h3>
                    <p><?php echo $result_users->num_rows; ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-shield"></i>
                    <h3>Administradores</h3>
                    <p><?php 
                        $admin_count = $conn->query("SELECT COUNT(*) as count FROM usuarios WHERE nivel_acesso = 'admin'")->fetch_assoc()['count'];
                        echo $admin_count;
                    ?></p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user"></i>
                    <h3>Usuários Comuns</h3>
                    <p><?php 
                        $user_count = $conn->query("SELECT COUNT(*) as count FROM usuarios WHERE nivel_acesso = 'usuario'")->fetch_assoc()['count'];
                        echo $user_count;
                    ?></p>
                </div>
            </div>

            <!-- Tabela de usuários -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Usuário</th>
                            <th>Email</th>
                            <th>Nível</th>
                            <th>Data Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $result_users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['nome']); ?></td>
                            <td><?php echo htmlspecialchars($user['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $user['nivel_acesso'] == 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($user['nivel_acesso'])); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($user['data_cadastro'])); ?></td>
                            <td>
                                <a href="editar_usuario?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Editar</a>
                                <a href="#" onclick="showDeleteConfirmation(<?php echo $user['id']; ?>)" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Excluir</a>
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
            <p>Tem certeza que deseja excluir este usuário?</p>
            <div class="modal-buttons">
                <button onclick="deleteUser()" class="btn btn-danger">Sim, Excluir</button>
                <button onclick="hideDeleteConfirmation()" class="btn btn-secondary">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        let userIdToDelete = null;

        function showDeleteConfirmation(userId) {
            userIdToDelete = userId;
            document.getElementById('deleteConfirmModal').style.display = 'block';
        }

        function hideDeleteConfirmation() {
            document.getElementById('deleteConfirmModal').style.display = 'none';
        }

        function deleteUser() {
            if (userIdToDelete) {
                window.location.href = 'excluir_usuario?id=' + userIdToDelete;
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