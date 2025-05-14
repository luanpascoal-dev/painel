<?php
session_start();

// Verificar se está logado
if(!isset($_SESSION['id']) || !isset($_SESSION['usuario'])) {
    header("Location: ../login");
    exit();
}

// Conexão com o banco de dados
include '../database.php';

// Verificar se o usuário é admin
$stmt = $conn->prepare("SELECT nivel_acesso FROM usuarios WHERE id = ? AND nivel_acesso = 'admin'");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows !== 1) {
    header("Location: ../");
    exit();
}

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Impedir que o admin exclua a si mesmo
    if($id == $_SESSION['id']) {
        $_SESSION['error'] = "Você não pode excluir seu próprio usuário!";
        header("Location: ./usuarios");
        exit();
    }
    
    // Verificar se o usuário existe e excluir
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Usuário excluído com sucesso!";
    } else {
        $_SESSION['error'] = "Erro ao excluir usuário!";
    }
}

header("Location: ./usuarios");
exit();
?> 