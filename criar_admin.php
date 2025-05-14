<?php
include 'database.php';

$nome = "Administrador";
$usuario = "admin";
$senha = password_hash("admin123", PASSWORD_DEFAULT);
$nivel = "admin";

$stmt = $conn->prepare("INSERT INTO usuarios (nome, usuario, senha, nivel_acesso) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nome, $usuario, $senha, $nivel);

if($stmt->execute()) {
    echo "Administrador criado com sucesso!";
} else {
    echo "Erro ao criar administrador: " . $conn->error;
}
?> 