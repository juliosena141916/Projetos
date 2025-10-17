<?php
session_start();

// Destroi todas as variáveis de sessão
$_SESSION = array();

// Destroi a sessão
session_destroy();

// Redireciona para a página de login
header("Location: loginAdm.html?success=" . urlencode("Logout realizado com sucesso"));
exit();
?>