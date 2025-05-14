<?php
$host = "localhost";
$usuario = "root"; // Default XAMPP username
$senha = ""; // Default XAMPP password
$database = "painel";

$conn = new mysqli($host, $usuario, $senha, $database);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}


