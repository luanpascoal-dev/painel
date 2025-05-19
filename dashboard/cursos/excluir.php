<?php
session_start();

include '../assets/layouts/verify.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $sql = "DELETE FROM CURSO WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header('Location: ../cursos');
exit(); 