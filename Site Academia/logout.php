<?php
// Iniciar sessão
session_start();

// Destruir todas as variáveis de sessão
$_SESSION = array();

// Se é desejado matar a sessão, também delete o cookie de sessão
// Remover cookie "Lembrar-me"
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir a sessão
session_destroy();

// Redirecionar para página de login
header('Location: login.html');
exit;
?>

