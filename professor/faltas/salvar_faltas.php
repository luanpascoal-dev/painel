<?php
session_start();
include '../assets/layouts/verify.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index");
    exit;
}

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

$data = $_POST['data'];
$presencas = isset($_POST['presenca']) ? $_POST['presenca'] : [];

try {
    $conn->begin_transaction();

    // Primeiro, excluir todas as faltas existentes para as aulas do dia
    $query_aulas = "SELECT id FROM AULA WHERE DATE(data_hora_inicio) = ?";
    $stmt = $conn->prepare($query_aulas);
    $stmt->bind_param("s", $data);
    $stmt->execute();
    $aulas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (!empty($aulas)) {
        $aula_ids = array_column($aulas, 'id');
        $aula_ids_str = implode(',', $aula_ids);
        
        $conn->query("DELETE FROM FALTA WHERE id_aula IN ($aula_ids_str)");
        
        // Inserir novas faltas (alunos não presentes)
        $stmt_insert = $conn->prepare("INSERT INTO FALTA (id_aluno, id_aula) VALUES (?, ?)");
        
        foreach ($aula_ids as $aula_id) {
            $alunos_presentes = isset($presencas) ? array_keys($presencas) : [];
            
            // Buscar todos os alunos da turma
            $query_alunos = "SELECT DISTINCT a.id_usuario 
                           FROM ALUNO a
                           INNER JOIN MATRICULA m ON a.id_usuario = m.id_aluno
                           INNER JOIN AULA au ON m.id_turma = au.id_turma
                           WHERE au.id = ?";
            $stmt_alunos = $conn->prepare($query_alunos);
            $stmt_alunos->bind_param("i", $aula_id);
            $stmt_alunos->execute();
            $todos_alunos = $stmt_alunos->get_result()->fetch_all(MYSQLI_ASSOC);
            
            foreach ($todos_alunos as $aluno) {
                $id_aluno = $aluno['id_usuario'];
                // Se o aluno não está na lista de presentes, registrar falta
                if (!isset($presencas[$id_aluno][$aula_id])) {
                    $stmt_insert->bind_param("ii", $id_aluno, $aula_id);
                    $stmt_insert->execute();
                }
            }
        }
    }

    $conn->commit();
    $_SESSION['success'] = "Faltas registradas com sucesso!";

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Erro ao registrar faltas: " . $e->getMessage();
}

header("Location: index?disciplina=" . $_GET['disciplina'] . "&turma=" . $_GET['turma'] . "&data=" . $data);
exit; 