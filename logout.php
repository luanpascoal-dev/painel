<?php
// Inicia a sessão
session_start();

// Limpa todas as variáveis da sessão
$_SESSION = array();

// Destrói o cookie da sessão se existir
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destrói a sessão
session_destroy();

// Redireciona para a página de login
header("Location: login");
exit();
?>