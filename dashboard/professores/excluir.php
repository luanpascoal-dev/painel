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

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // Verificar se existem turmas associadas
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM ALOCACAO WHERE id_professor = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $turmas_count = $stmt->get_result()->fetch_assoc()['count'];
        
        if($turmas_count > 0) {
            throw new Exception("Não é possível excluir o professor pois existem turmas associadas a ele.");
        }
        
        // Excluir professor
        $stmt = $conn->prepare("DELETE FROM PROFESSOR WHERE id_usuario = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Confirmar transação
        $conn->commit();
        
        $_SESSION['success'] = "Professor excluído com sucesso!";
    } catch (Exception $e) {
        // Reverter em caso de erro
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
}

header("Location: ./");
exit(); 