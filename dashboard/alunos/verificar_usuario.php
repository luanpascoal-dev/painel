<?php
session_start();
include '../assets/layouts/verify.php';

if (!isset($_GET['usuario'])) {
    echo json_encode(['error' => 'Usuário não especificado']);
    exit;
}

$usuario = $_GET['usuario'];

$query = "SELECT 1 FROM USUARIOS WHERE usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $usuario);
$stmt->execute();

$exists = $stmt->get_result()->num_rows > 0;

echo json_encode(['exists' => $exists]);