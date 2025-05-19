<?php
session_start();
include '../assets/layouts/verify.php';

// Verificar se é professor
$id_usuario = $_SESSION['id'];
$query_professor = "SELECT p.* FROM PROFESSOR p WHERE p.id_usuario = ?";
$stmt = $conn->prepare($query_professor);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$professor = $stmt->get_result()->fetch_assoc();

if (!$professor) {
    $_SESSION['error'] = "Acesso restrito a professores.";
    header("Location: ../../");
    exit;
}

// Verificar se foi passado um ID
$id_avaliacao = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_avaliacao) {
    $_SESSION['error'] = "Avaliação não especificada.";
    header("Location: index.php");
    exit;
}

// Verificar se a avaliação pertence ao professor
$query_check = "SELECT av.* 
                FROM AVALIACAO av
                INNER JOIN DISCIPLINA d ON av.id_disciplina = d.id
                INNER JOIN LECIONA l ON d.id = l.id_disciplina
                WHERE av.id = ? AND l.id_professor = ?";
$stmt = $conn->prepare($query_check);
$stmt->bind_param("ii", $id_avaliacao, $professor['id_usuario']);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    $_SESSION['error'] = "Avaliação não encontrada ou sem permissão.";
    header("Location: index.php");
    exit;
}

try {
    // Iniciar transação
    $conn->begin_transaction();

    // Primeiro excluir as notas relacionadas
    $stmt = $conn->prepare("DELETE FROM NOTA WHERE id_avaliacao = ?");
    $stmt->bind_param("i", $id_avaliacao);
    $stmt->execute();

    // Depois excluir a avaliação
    $stmt = $conn->prepare("DELETE FROM AVALIACAO WHERE id = ?");
    $stmt->bind_param("i", $id_avaliacao);
    $stmt->execute();

    // Confirmar transação
    $conn->commit();

    $_SESSION['success'] = "Avaliação excluída com sucesso!";

} catch (Exception $e) {
    // Reverter em caso de erro
    $conn->rollback();
    $_SESSION['error'] = "Erro ao excluir avaliação: " . $e->getMessage();
}

header("Location: ./");
exit; 