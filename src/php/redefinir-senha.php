<?php
// Última etapa: valida o token e grava a nova senha.
session_start();
require_once __DIR__ . '/conexao.php';

function erroReset(string $token, string $mensagem): void
{
    header('Location: ../pages/redefinir-senha.php?token=' . urlencode($token) . '&erro=' . urlencode($mensagem));
    exit;
}

$token = trim((string) ($_POST['token'] ?? $_GET['token'] ?? ''));

if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    header('Location: ../pages/esqueci-senha.php?erro=' . urlencode('Sua recuperação expirou. Comece novamente.'));
    exit;
}

$tokenHash = hash('sha256', $token);

// Garante compatibilidade com um banco criado antes desta versão.
$conexao->query("\n    CREATE TABLE IF NOT EXISTS recuperacoes_senha (\n        id INT NOT NULL AUTO_INCREMENT,\n        id_usuario INT NOT NULL,\n        token_hash CHAR(64) NOT NULL,\n        expira_em DATETIME NOT NULL,\n        verificado TINYINT(1) NOT NULL DEFAULT 0,\n        criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,\n        PRIMARY KEY (id),\n        UNIQUE KEY uk_recuperacao_token (token_hash),\n        KEY idx_recuperacao_usuario (id_usuario),\n        CONSTRAINT fk_recuperacao_usuario\n            FOREIGN KEY (id_usuario) REFERENCES usuarios(id)\n            ON DELETE CASCADE ON UPDATE CASCADE\n    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\n");

$stmt = $conexao->prepare(
    'SELECT id, id_usuario, expira_em, verificado
     FROM recuperacoes_senha WHERE token_hash = ? LIMIT 1'
);

if (!$stmt) {
    $conexao->close();
    header('Location: ../pages/esqueci-senha.php?erro=' . urlencode('Não foi possível validar a recuperação.'));
    exit;
}

$stmt->bind_param('s', $tokenHash);
$stmt->execute();
$stmt->bind_result($idRecuperacao, $idUsuario, $expiraEm, $verificado);
$encontrou = $stmt->fetch();
$stmt->close();

if (!$encontrou || (int) $verificado !== 1 || strtotime($expiraEm) < time()) {
    $conexao->close();
    header('Location: ../pages/esqueci-senha.php?erro=' . urlencode('Sua recuperação expirou. Comece novamente.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $conexao->close();
    return;
}

$senha = (string) ($_POST['senha'] ?? '');
$confirmar = (string) ($_POST['confirmar_senha'] ?? '');

if ($senha === '' || $confirmar === '') {
    $conexao->close();
    erroReset($token, 'Preencha os dois campos de senha.');
}

if ($senha !== $confirmar) {
    $conexao->close();
    erroReset($token, 'As senhas não coincidem.');
}

if (
    strlen($senha) < 8 ||
    !preg_match('/[a-z]/', $senha) ||
    !preg_match('/[A-Z]/', $senha) ||
    !preg_match('/[0-9]/', $senha)
) {
    $conexao->close();
    erroReset($token, 'A senha deve ter no mínimo 8 caracteres, com letra maiúscula, minúscula e número.');
}

$novoHash = password_hash($senha, PASSWORD_DEFAULT);

if ($novoHash === false) {
    $conexao->close();
    erroReset($token, 'Não foi possível criar a nova senha.');
}

// Atualiza a senha somente do usuário ligado ao token verificado.
$stmt = $conexao->prepare('UPDATE usuarios SET senha = ? WHERE id = ? LIMIT 1');

if (!$stmt) {
    error_log('TopTurismo recuperação - UPDATE senha: ' . $conexao->error);
    $conexao->close();
    erroReset($token, 'Não foi possível atualizar a senha da conta.');
}

$id = (int) $idUsuario;
$stmt->bind_param('si', $novoHash, $id);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
    error_log('TopTurismo recuperação - erro UPDATE senha: ' . $conexao->error);
    $conexao->close();
    erroReset($token, 'Não foi possível atualizar a senha da conta.');
}

// Confirma no mesmo banco que recebeu o UPDATE.
$stmt = $conexao->prepare('SELECT senha FROM usuarios WHERE id = ? LIMIT 1');

if (!$stmt) {
    $conexao->close();
    erroReset($token, 'Não foi possível confirmar a nova senha.');
}

$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($hashBanco);
$encontrouSenha = $stmt->fetch();
$stmt->close();

if (!$encontrouSenha || !is_string($hashBanco) || !password_verify($senha, $hashBanco)) {
    $conexao->close();
    erroReset($token, 'A nova senha não pôde ser confirmada. Tente novamente.');
}

// Token de uso único.
$stmt = $conexao->prepare('DELETE FROM recuperacoes_senha WHERE id = ?');
if ($stmt) {
    $idRec = (int) $idRecuperacao;
    $stmt->bind_param('i', $idRec);
    $stmt->execute();
    $stmt->close();
}

$conexao->close();

header('Location: ../pages/login.php?sucesso=senha');
exit;
