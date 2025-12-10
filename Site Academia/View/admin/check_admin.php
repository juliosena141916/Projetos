<?php
// Iniciar sessão apenas se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Função para redirecionar para o login
function redirectToLogin() {
    // Redireciona para a página de login
    header("Location: ../login.html");
    exit;
}

// 1. Verificar se a sessão existe
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['tipo_usuario'])) {
    redirectToLogin();
}

// 2. Verificar se o usuário é administrador
if ($_SESSION['tipo_usuario'] !== 'admin') {
    // Se não for admin, destrói a sessão e redireciona
    session_unset();
    session_destroy();
    redirectToLogin();
}

// Se chegou até aqui, o usuário é um administrador logado.
// As variáveis de sessão $_SESSION['usuario_id'], $_SESSION['usuario_nome'], $_SESSION['tipo_usuario'] estão disponíveis.
?>
