<?php

// *bloqueia o painel quando a sessão não pertence a um administrador*
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_tipo'] ?? 'cliente') !== 'admin') {
    header('Location: ../../pages/login.php?erro=admin');
    exit;
}
