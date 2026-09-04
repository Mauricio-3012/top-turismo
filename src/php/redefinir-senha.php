<?php
session_start();

/*
 * TopTurismo - processamento da nova senha.
 *
 * A autorização da recuperação é criada por src/php/esqueci-senha.php
 * depois que a pergunta/resposta de recuperação é validada.
 *
 * Compatibilidade: também aceitamos os nomes de sessão usados em versões
 * antigas do projeto (id_recuperacao / etapa_recuperacao), mas o projeto
 * passa a usar recuperacao_usuario_id / recuperacao_expira como padrão.
 */

$idUsuario = (int) ($_SESSION['recuperacao_usuario_id'] ?? $_SESSION['id_recuperacao'] ?? 0);
$expira = (int) ($_SESSION['recuperacao_expira'] ?? 0);
$etapaRecuperacao = (string) ($_SESSION['etapa_recuperacao'] ?? 'autorizada');

// Se a versão antiga não possuía timestamp, mantemos a sessão antiga válida
// somente para a etapa explicitamente autorizada. Novas recuperações sempre
// recebem prazo de 15 minutos.
$autorizado = $idUsuario > 0
    && ($expira === 0 || time() <= $expira)
    && in_array($etapaRecuperacao, ['autorizada', 'resposta_validada', 'chave_validada', '3'], true);

if (!$autorizado) {
    unset(
        $_SESSION['recuperacao_usuario_id'],
        $_SESSION['recuperacao_expira'],
        $_SESSION['id_recuperacao'],
        $_SESSION['etapa_recuperacao'],
        $_SESSION['recuperacao_email'],
        $_SESSION['recuperacao_pergunta']
    );

    header('Location: ../pages/esqueci-senha.php?erro=' . urlencode('Sua recuperação expirou. Comece novamente.'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/redefinir-senha.php');
    exit;
}

$senha = (string) ($_POST['senha'] ?? $_POST['nova_senha'] ?? '');
$confirmar = (string) ($_POST['confirmar_senha'] ?? $_POST['confirma_senha'] ?? '');

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
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('Não foi possível conectar ao banco de dados.'));
    exit;
}

$hash = password_hash($senha, PASSWORD_DEFAULT);
if ($hash === false) {
    $conexao->close();
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('Não foi possível gerar a nova senha. Tente novamente.'));
    exit;
}

/* Confirma que o usuário ainda existe antes de alterar a senha. */
$stmt = $conexao->prepare('SELECT id FROM usuarios WHERE id = ? LIMIT 1');
if (!$stmt) {
    error_log('TopTurismo redefinição - erro no SELECT: ' . $conexao->error);
    $conexao->close();
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('Não foi possível validar a conta. Tente novamente.'));
    exit;
}

$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$stmt->store_result();
$usuarioExiste = $stmt->num_rows === 1;
$stmt->close();

if (!$usuarioExiste) {
    $conexao->close();
    unset($_SESSION['recuperacao_usuario_id'], $_SESSION['recuperacao_expira'], $_SESSION['id_recuperacao'], $_SESSION['etapa_recuperacao']);
    header('Location: ../pages/esqueci-senha.php?erro=' . urlencode('A conta não foi encontrada.'));
    exit;
}

/*
 * O nome correto da coluna é senha.
 * A versão antiga do código usava senha_segura, que não existe no SQL
 * do TopTurismo e fazia o UPDATE falhar.
 */
$stmt = $conexao->prepare('UPDATE usuarios SET senha = ? WHERE id = ? LIMIT 1');
if (!$stmt) {
    error_log('TopTurismo redefinição - erro no UPDATE prepare: ' . $conexao->error);
    $conexao->close();
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('Não foi possível processar a redefinição. Tente novamente.'));
    exit;
}

$stmt->bind_param('si', $hash, $idUsuario);
$executou = $stmt->execute();
$erroSql = $stmt->error;
$alterou = $stmt->affected_rows >= 1;
$stmt->close();
$conexao->close();

if (!$executou || !$alterou) {
    error_log("TopTurismo redefinição - falha no UPDATE. ID: {$idUsuario}; SQL Error: {$erroSql}");
    header('Location: ../pages/redefinir-senha.php?erro=' . urlencode('Não foi possível atualizar sua senha. Tente novamente.'));
    exit;
}

// A autorização é de uso único.
unset(
    $_SESSION['recuperacao_usuario_id'],
    $_SESSION['recuperacao_expira'],
    $_SESSION['id_recuperacao'],
    $_SESSION['etapa_recuperacao'],
    $_SESSION['recuperacao_email'],
    $_SESSION['recuperacao_pergunta']
);

header('Location: ../pages/login.php?sucesso=senha');
exit;
