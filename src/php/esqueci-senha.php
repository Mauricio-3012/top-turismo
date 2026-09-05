<?php
/**
 * TopTurismo - início da recuperação de senha (e-mail + pergunta de segurança).
 *
 * Fluxo:
 *   1) etapa=email     -> usuário informa o e-mail
 *   2) etapa=pergunta   -> usuário responde a pergunta de segurança cadastrada
 *   3) sucesso          -> autoriza a sessão a acessar redefinir-senha.php
 */

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/esqueci-senha.php');
    exit;
}

require_once __DIR__ . '/conexao.php';

if (!isset($conexao) || !($conexao instanceof mysqli) || $conexao->connect_errno) {
    error_log('TopTurismo recuperação - sem conexão com o banco.');
    header('Location: ../pages/esqueci-senha.php?erro=' . urlencode('Não foi possível conectar ao banco de dados.'));
    exit;
}

$etapa = $_POST['etapa'] ?? 'email';

/* ---------- Etapa 1: recebe o e-mail ---------- */
if ($etapa === 'email') {
    // Começa uma recuperação nova: limpa qualquer tentativa anterior.
    session_unset_recuperacao();

    $email = mb_strtolower(trim($_POST['email'] ?? ''), 'UTF-8');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $conexao->close();
        header('Location: ../pages/esqueci-senha.php?erro=' . urlencode('E-mail inválido.'));
        exit;
    }

    $stmt = $conexao->prepare(
        'SELECT id, pergunta_recuperacao, resposta_recuperacao_hash FROM usuarios WHERE email = ? LIMIT 1'
    );
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conexao->close();

    if (!$usuario || empty($usuario['pergunta_recuperacao']) || empty($usuario['resposta_recuperacao_hash'])) {
        // Mensagem genérica de propósito: não revela se o e-mail existe ou não.
        error_log('TopTurismo recuperação - e-mail sem conta/pergunta cadastrada.');
        header('Location: ../pages/esqueci-senha.php?erro=' . urlencode('Não encontramos uma conta com esse e-mail.'));
        exit;
    }

    $_SESSION['recuperacao_email'] = $email;
    $_SESSION['recuperacao_pergunta'] = $usuario['pergunta_recuperacao'];

    header('Location: ../pages/esqueci-senha.php?etapa=pergunta');
    exit;
}

/* ---------- Etapa 2: valida a resposta da pergunta de segurança ---------- */
if ($etapa === 'pergunta') {
    $email = $_SESSION['recuperacao_email'] ?? '';
    $resposta = trim($_POST['resposta_recuperacao'] ?? '');

    if ($email === '' || $resposta === '') {
        $conexao->close();
        header('Location: ../pages/esqueci-senha.php?etapa=pergunta&erro=' . urlencode('Informe a resposta da pergunta.'));
        exit;
    }

    $stmt = $conexao->prepare(
        'SELECT id, resposta_recuperacao_hash FROM usuarios WHERE email = ? LIMIT 1'
    );
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conexao->close();

    $respostaNormalizada = mb_strtolower(preg_replace('/\s+/', ' ', $resposta), 'UTF-8');

    if (
        !$usuario
        || empty($usuario['resposta_recuperacao_hash'])
        || !password_verify($respostaNormalizada, $usuario['resposta_recuperacao_hash'])
    ) {
        error_log('TopTurismo recuperação - resposta incorreta para o usuário id=' . ($usuario['id'] ?? '?'));
        header('Location: ../pages/esqueci-senha.php?etapa=pergunta&erro=' . urlencode('Resposta incorreta.'));
        exit;
    }

    // Autoriza a troca de senha por 15 minutos, com um token de uso único.
    session_regenerate_id(true);
    $_SESSION['recuperacao_usuario_id'] = (int) $usuario['id'];
    $_SESSION['recuperacao_expira'] = time() + 900;
    $_SESSION['recuperacao_token'] = bin2hex(random_bytes(16));
    unset($_SESSION['recuperacao_email'], $_SESSION['recuperacao_pergunta']);

    error_log('TopTurismo recuperação - autorizado id=' . $usuario['id'] . ' até ' . date('H:i:s', $_SESSION['recuperacao_expira']));

    header('Location: ../pages/redefinir-senha.php');
    exit;
}

$conexao->close();
header('Location: ../pages/esqueci-senha.php');
exit;

/**
 * Remove todas as variáveis de sessão usadas pelo fluxo de recuperação.
 */
function session_unset_recuperacao(): void
{
    unset(
        $_SESSION['recuperacao_usuario_id'],
        $_SESSION['recuperacao_expira'],
        $_SESSION['recuperacao_token'],
        $_SESSION['recuperacao_email'],
        $_SESSION['recuperacao_pergunta']
    );
}
