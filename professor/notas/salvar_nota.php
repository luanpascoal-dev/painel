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
    echo json_encode(['success' => false, 'error' => 'Acesso restrito a professores']);
    exit;
}

// Receber dados
$data = json_decode(file_get_contents('php://input'), true);
$id_aluno = $data['id_aluno'];
$id_avaliacao = $data['id_avaliacao'];
$nota = $data['nota'];

// Verificar se a avaliação pertence a uma disciplina que o professor leciona
$query_check = "SELECT 1 
                FROM AVALIACAO a 
                INNER JOIN DISCIPLINA d ON a.id_disciplina = d.id 
                INNER JOIN LECIONA l ON d.id = l.id_disciplina 
                WHERE a.id = ? AND l.id_professor = ?";
$stmt = $conn->prepare($query_check);
$stmt->bind_param("ii", $id_avaliacao, $id_usuario);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Sem permissão para esta avaliação']);
    exit;
}

try {
    // Verificar se já existe nota
    $query_check_nota = "SELECT 1 FROM NOTA WHERE id_aluno = ? AND id_avaliacao = ?";
    $stmt = $conn->prepare($query_check_nota);
    $stmt->bind_param("ii", $id_aluno, $id_avaliacao);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        // Atualizar nota existente
        $query = "UPDATE NOTA SET nota = ?, data_hora = NOW() WHERE id_aluno = ? AND id_avaliacao = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("dii", $nota, $id_aluno, $id_avaliacao);
    } else {
        // Inserir nova nota
        $query = "INSERT INTO NOTA (id_aluno, id_avaliacao, nota, data_hora) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iid", $id_aluno, $id_avaliacao, $nota);
    }
    
    $stmt->execute();
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 