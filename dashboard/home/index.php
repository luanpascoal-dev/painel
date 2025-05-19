<?php
session_start();

// Verificar se está logado
if(!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
    header("Location: ../login");
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

// Buscar estatísticas básicas
$total = "SELECT COUNT(*) as total_users FROM usuarios";
$query_users = "SELECT id, nome, usuario, email, nivel_acesso, data_cadastro FROM usuarios ORDER BY data_cadastro DESC";
$result_users = $conn->query($query_users);
$result_total = $conn->query($total);
$total_users = $result_total->fetch_assoc()['total_users'];


// Função para verificar se é um IP de rede local
function isPrivateIP($ip) {
    $ip = trim($ip);
    // Remove qualquer texto após o IP (como máscara de rede)
    $ip = preg_replace('/\/.*/', '', $ip);
    
    // Converte IP para número
    $ip_parts = explode('.', $ip);
    if (count($ip_parts) !== 4) return false;
    
    // Verifica se é um IP válido
    if (!filter_var($ip, FILTER_VALIDATE_IP)) return false;
    
    // Verifica ranges de IPs privados
    return (
        // 192.168.x.x
        preg_match('/^192\.168\./', $ip) ||
        // 10.x.x.x
        preg_match('/^10\./', $ip) ||
        // 172.16.x.x até 172.31.x.x
        preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)
    );
}

// Função para obter o IPv4 local
function getLocalIPv4() {
    // Windows
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        exec('ipconfig', $output);
        foreach ($output as $line) {
            if (strpos($line, 'IPv4') !== false) {
                $ip = explode(':', $line)[1];
                $ip = trim($ip);
                if (isPrivateIP($ip)) {
                    return $ip;
                }
            }
        }
    } 
    // Linux/Unix/Mac
    else {
        exec('ip addr show', $output);
        foreach ($output as $line) {
            if (strpos($line, 'inet') !== false) {
                $ip = explode(' ', trim($line))[1];
                $ip = explode('/', $ip)[0];
                if (isPrivateIP($ip)) {
                    return $ip;
                }
            }
        }
    }
    
    // Método alternativo usando socket
    try {
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_connect($sock, "8.8.8.8", 53);
        socket_getsockname($sock, $name);
        socket_close($sock);
        if (isPrivateIP($name)) {
            return $name;
        }
    } catch (Exception $e) {
        // Ignora erro do socket
    }
    
    return 'IP local não encontrado';
}

$ip_local = getLocalIPv4();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrativo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard">
        <?php include '../assets/layouts/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <i class="fas fa-user-circle fa-2x"></i>
                    <span><?php echo htmlspecialchars($_SESSION['usuario' ]); ?></span>
                </div>
            </div>

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

            <!-- Cards -->
            <div class="cards">
                <div class="card">
                    <div class="card-header">
                        <h3>Total de Usuários</h3>
                        <div class="card-icon bg-primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <h2><?php echo $total_users; ?></h2>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Acessos Hoje</h3>
                        <div class="card-icon bg-success">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <h2>25</h2>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Novos Usuários</h3>
                        <div class="card-icon bg-warning">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                    <h2>5</h2>
                </div>
            </div>

            <!-- IP -->
            <div class="container">
                <div class="ip-card">
                    <h1 class="welcome-text">Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</h1>
                    <p>Seu endereço de IP é:</p>
                    <div class="ip-display">
                        <?php echo htmlspecialchars($ip_local) . "/painel"; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html> 