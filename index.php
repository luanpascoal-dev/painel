<?php
session_start();

// Verificar se o usuário está logado
if(!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
    header("Location: login");
    exit();
}


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-content">
            <a href="index.php" class="logo">Painel</a>
            <a href="logout" class="logout-btn">Sair</a>
        </div>
    </nav>


</body>
</html>

