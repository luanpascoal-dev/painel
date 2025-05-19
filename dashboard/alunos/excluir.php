<?php
session_start();

include '../assets/layouts/verify.php';

if(isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Iniciar transação
    $conn->begin_transaction();
    
    try {
        // Verificar se existem matrículas
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM MATRICULA WHERE id_aluno = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $matriculas_count = $stmt->get_result()->fetch_assoc()['count'];
        
        if($matriculas_count > 0) {
            throw new Exception("Não é possível excluir o aluno pois existem matrículas associadas a ele.");
        }
        
        // Excluir aluno
        $stmt = $conn->prepare("DELETE FROM ALUNO WHERE id_usuario = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Confirmar transação
        $conn->commit();
        
        $_SESSION['success'] = "Aluno excluído com sucesso!";
    } catch (Exception $e) {
        // Reverter em caso de erro
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
}

header("Location: ./");
exit(); 