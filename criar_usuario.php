<?php
// Salve como criar_usuario.php e execute uma vez
include 'database.php';

$nome = "Luan Pascoal";
$usuario = "luanpascoal";
$senha = password_hash("luanpascoal", PASSWORD_DEFAULT);
$email = "luanpascoal@gmail.com";

$sql = "INSERT INTO usuarios (nome, usuario, senha, email) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $nome, $usuario, $senha, $email);

if ($stmt->execute()) {
    echo "Usuário criado com sucesso!";
} else {
    echo "Erro ao criar usuário: " . $conn->error;
}
?>