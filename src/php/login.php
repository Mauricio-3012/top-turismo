<?php
// Autentica o usuário e cria a sessão.
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit;
}

require_once __DIR__ . '/conexao.php';

$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$senha = (string) ($_POST['senha'] ?? '');

if ($email === '' || $senha === '') {
    header('Location: ../pages/login.php?erro=2');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../pages/login.php?erro=1');
    exit;
}

$stmt = $conexao->prepare(
    'SELECT id, nome, senha, tipo FROM usuarios WHERE email = ? LIMIT 1'
);

if (!$stmt) {
    $conexao->close();
    header('Location: ../pages/login.php?erro=3');
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->bind_result($id, $nome, $hash, $tipo);
$encontrou = $stmt->fetch();
$stmt->close();
$conexao->close();

if (!$encontrou) {
    header('Location: ../pages/login.php?erro=1');
    exit;
}

if (!is_string($hash) || $hash === '' || !password_verify($senha, $hash)) {
    header('Location: ../pages/login.php?erro=4');
    exit;
}

session_regenerate_id(true);
$_SESSION['usuario_id'] = (int) $id;
$_SESSION['usuario_nome'] = $nome;
$_SESSION['usuario_tipo'] = $tipo;

if ($tipo === 'admin') {
    header('Location: ../pages/admin/dashboard.php');
} else {
    header('Location: ../pages/dashboard.php');
}
exit;
