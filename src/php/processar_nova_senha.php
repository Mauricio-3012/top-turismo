<?php
/**
 * TopTurismo - processamento definitivo da nova senha.
 *
 * Este é o único arquivo responsável por gravar a nova senha.
 * O login também usa a coluna usuarios.senha, portanto os dois fluxos
 * trabalham sobre o mesmo campo e com password_hash/password_verify.
 */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/login.php');
    exit;
}

$idUsuario = (int) ($_SESSION['recuperacao_usuario_id'] ?? 0);
$etapa = (int) ($_SESSION['recuperacao_etapa'] ?? 0);
$expira = (int) ($_SESSION['recuperacao_expira'] ?? 0);

if ($idUsuario <= 0 || $etapa !== 3 || $expira <= 0 || time() > $expira) {
    unset(
        $_SESSION['recuperacao_usuario_id'],
        $_SESSION['recuperacao_email'],
        $_SESSION['recuperacao_pergunta'],
        $_SESSION['recuperacao_etapa'],
        $_SESSION['recuperacao_expira']
    );

    header('Location: ../pages/esqueci-senha.php?erro=' . urlencode('Sua recuperação expirou. Comece novamente.'));
    exit;
}

$novaSenha = (string) ($_POST['nova_senha'] ?? '');
$confirmaSenha = (string) ($_POST['confirma_senha'] ?? '');

if ($novaSenha === '' || $confirmaSenha === '') {
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('Preencha os dois campos de senha.'));
    exit;
}

if ($novaSenha !== $confirmaSenha) {
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('A nova senha e a confirmação não coincidem.'));
    exit;
}

if (
    strlen($novaSenha) < 8 ||
    !preg_match('/[a-z]/', $novaSenha) ||
    !preg_match('/[A-Z]/', $novaSenha) ||
    !preg_match('/[0-9]/', $novaSenha)
) {
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode(
        'A senha deve ter no mínimo 8 caracteres, com letra maiúscula, minúscula e número.'
    ));
    exit;
}

require_once __DIR__ . '/conexao.php';

$hash = password_hash($novaSenha, PASSWORD_DEFAULT);

if ($hash === false) {
    error_log('TopTurismo redefinição: password_hash falhou.');
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('Não foi possível gerar a nova senha.'));
    exit;
}

/*
 * Importante:
 * - tabela: usuarios
 * - chave: id
 * - senha usada pelo login: senha
 */
$stmt = $conexao->prepare('UPDATE usuarios SET senha = ? WHERE id = ? LIMIT 1');

if (!$stmt) {
    error_log('TopTurismo redefinição: prepare UPDATE falhou: ' . $conexao->error);
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('Não foi possível atualizar a senha.'));
    exit;
}

$stmt->bind_param('si', $hash, $idUsuario);

if (!$stmt->execute()) {
    error_log('TopTurismo redefinição: UPDATE falhou: ' . $stmt->error);
    $stmt->close();
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('Não foi possível atualizar a senha.'));
    exit;
}

$stmt->close();
$conexao->close();

/* A autorização de recuperação é de uso único. */
unset(
    $_SESSION['recuperacao_usuario_id'],
    $_SESSION['recuperacao_email'],
    $_SESSION['recuperacao_pergunta'],
    $_SESSION['recuperacao_etapa'],
    $_SESSION['recuperacao_expira']
);

header('Location: ../pages/login.php?sucesso=senha');
exit;
