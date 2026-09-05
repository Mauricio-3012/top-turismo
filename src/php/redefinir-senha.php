<?php
/**
 * TopTurismo - processamento da nova senha.
 *
 * A autorização é criada por esqueci-senha.php depois que a pergunta de
 * segurança é validada corretamente. É válida por 15 minutos e de uso único.
 */

session_start();

$idUsuario = (int) ($_SESSION['recuperacao_usuario_id'] ?? 0);
$expira = (int) ($_SESSION['recuperacao_expira'] ?? 0);
$token = (string) ($_SESSION['recuperacao_token'] ?? '');

$autorizado = $idUsuario > 0 && $token !== '' && time() <= $expira;

if (!$autorizado) {
    unset(
        $_SESSION['recuperacao_usuario_id'],
        $_SESSION['recuperacao_expira'],
        $_SESSION['recuperacao_token']
    );
    header('Location: ../pages/esqueci-senha.php?erro=' . urlencode('Sua recuperação expirou. Comece novamente.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/redefinir-senha.php');
    exit;
}

$senha = (string) ($_POST['senha'] ?? '');
$confirmar = (string) ($_POST['confirmar_senha'] ?? '');

if ($senha === '' || $confirmar === '') {
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('Preencha os dois campos de senha.'));
    exit;
}

if (
    strlen($senha) < 8
    || !preg_match('/[a-z]/', $senha)
    || !preg_match('/[A-Z]/', $senha)
    || !preg_match('/[0-9]/', $senha)
) {
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('A senha deve ter no mínimo 8 caracteres, com letra maiúscula, minúscula e número.'));
    exit;
}

if ($senha !== $confirmar) {
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('As senhas não coincidem.'));
    exit;
}

require_once __DIR__ . '/conexao.php';

if (!isset($conexao) || !($conexao instanceof mysqli) || $conexao->connect_errno) {
    error_log('TopTurismo redefinição - sem conexão com o banco.');
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('Não foi possível conectar ao banco de dados.'));
    exit;
}

$hash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $conexao->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
$stmt->bind_param('si', $hash, $idUsuario);
$executou = $stmt->execute();
$erroSql = $stmt->error;
$afetou = $stmt->affected_rows;
$stmt->close();

// affected_rows pode vir 0 se, por coincidência, o hash novo colidir byte a
// byte com o antigo (extremamente improvável com bcrypt) - então tratamos
// "executou com sucesso" como sucesso, mesmo com affected_rows = 0.
if (!$executou) {
    error_log("TopTurismo redefinição - falha no UPDATE. ID: {$idUsuario}; SQL Error: {$erroSql}");
    $conexao->close();
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('Não foi possível atualizar sua senha. Tente novamente.'));
    exit;
}

error_log("TopTurismo redefinição - senha atualizada com sucesso. ID: {$idUsuario}; affected_rows: {$afetou}");

// DEBUG TEMPORÁRIO - remover depois de confirmar. Não expõe a senha, só o hash.
$stmtCheck = $conexao->prepare('SELECT senha FROM usuarios WHERE id = ?');
$stmtCheck->bind_param('i', $idUsuario);
$stmtCheck->execute();
$hashLido = $stmtCheck->get_result()->fetch_assoc()['senha'] ?? null;
$stmtCheck->close();
error_log('DEBUG verify imediato | hash gerado=[' . $hash . '] | hash lido=[' . $hashLido . '] '
    . 'iguais=' . var_export($hash === $hashLido, true) . ' '
    . 'verify_com_senha_digitada=' . var_export(password_verify($senha, $hashLido ?? ''), true));

$conexao->close();

// Autorização é de uso único.
unset(
    $_SESSION['recuperacao_usuario_id'],
    $_SESSION['recuperacao_expira'],
    $_SESSION['recuperacao_token']
);

header('Location: ../pages/login.php?sucesso=senha');
exit;