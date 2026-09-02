<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
 * A etapa final também é protegida por sessão e tempo de expiração.
 * A senha nunca é salva em texto puro.
 */
$idUsuario = (int) ($_SESSION['id_recuperacao'] ?? 0);
$expira = (int) ($_SESSION['recuperacao_expira'] ?? 0);

if (!$idUsuario || ($_SESSION['etapa_recuperacao'] ?? 0) !== 3 || time() > $expira) {
    unset(
        $_SESSION['id_recuperacao'],
        $_SESSION['etapa_recuperacao'],
        $_SESSION['recuperacao_email'],
        $_SESSION['recuperacao_expira']
    );

    header('Location: ../pages/login.php?erro=' . urlencode('Sua recuperação expirou. Comece novamente.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit;
}

$novaSenha = $_POST['nova_senha'] ?? '';
$confirmarSenha = $_POST['confirmar_senha'] ?? '';

if (
    strlen($novaSenha) < 8
    || !preg_match('/[a-z]/', $novaSenha)
    || !preg_match('/[A-Z]/', $novaSenha)
    || !preg_match('/[0-9]/', $novaSenha)
) {
    header('Location: ../pages/login.php?erro=' . urlencode(
        'A senha deve ter no mínimo 8 caracteres, com letra maiúscula, minúscula e número.'
    ));
    exit;
}

if ($novaSenha !== $confirmarSenha) {
    header('Location: ../pages/login.php?erro=' . urlencode('As senhas não coincidem.'));
    exit;
}

require_once __DIR__ . '/conexao.php';

$hash = password_hash($novaSenha, PASSWORD_DEFAULT);

$stmt = $conexao->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');

if (!$stmt) {
    header('Location: ../pages/login.php?erro=' . urlencode('Não foi possível atualizar sua senha. Tente novamente.'));
    exit;
}

$stmt->bind_param('si', $hash, $idUsuario);
$sucesso = $stmt->execute();

$stmt->close();
$conexao->close();

unset(
    $_SESSION['id_recuperacao'],
    $_SESSION['etapa_recuperacao'],
    $_SESSION['recuperacao_email'],
    $_SESSION['recuperacao_expira']
);

if (!$sucesso) {
    header('Location: ../pages/login.php?erro=' . urlencode('Não foi possível atualizar sua senha. Tente novamente.'));
    exit;
}

header('Location: ../pages/login.php?sucesso=senha');
exit;
?>
