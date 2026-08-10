<?php
session_start();

// Limpa todas as variáveis de sessão
$_SESSION = [];

// Destrói o cookie de sessão, se existir
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão de vez
session_destroy();

header("Location: ../pages/login.html");
exit();
?>