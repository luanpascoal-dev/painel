<?php
session_start();
include '../assets/layouts/verify.php';

if (isset($_GET['aluno']) && isset($_GET['turma'])) {
    $id_aluno = $_GET['aluno'];
    $id_turma = $_GET['turma'];

    try {
        $stmt = $conn->prepare("UPDATE MATRICULA SET status = 0 WHERE id_aluno = ? AND id_turma = ?");
        $stmt->bind_param("ii", $id_aluno, $id_turma);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Matrícula cancelada com sucesso!";
        } else {
            throw new Exception("Erro ao cancelar matrícula");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Erro ao cancelar matrícula: " . $e->getMessage();
    }
}

header("Location: index.php");
exit; 