<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/esqueci-senha.php');
    exit;
}

require_once __DIR__ . '/conexao.php';

$etapa = $_POST['etapa'] ?? 'email';

/* Limpa uma recuperação anterior quando o usuário começa novamente. */
if ($etapa === 'email') {
    unset(
        $_SESSION['recuperacao_usuario_id'],
        $_SESSION['recuperacao_expira'],
        $_SESSION['id_recuperacao'],
        $_SESSION['etapa_recuperacao'],
        $_SESSION['recuperacao_email'],
        $_SESSION['recuperacao_pergunta']
    );

    $email = mb_strtolower(trim($_POST['email'] ?? ''), 'UTF-8');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $conexao->close();
        header('Location: ../pages/esqueci-senha.php?erro=' . urlencode('E-mail inválido.'));
        exit;
    }

    $stmt = $conexao->prepare(
        'SELECT id, pergunta_recuperacao, resposta_recuperacao_hash, chave_recuperacao_hash
         FROM usuarios
         WHERE email = ?
         LIMIT 1'
    );

    if (!$stmt) {
        error_log('TopTurismo recuperação - erro no SELECT email: ' . $conexao->error);
        $conexao->close();
        header('Location: ../pages/esqueci-senha.php?erro=' . urlencode('Não foi possível iniciar a recuperação. Verifique se o banco está atualizado.'));
        exit;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conexao->close();

    if (!$usuario) {
        header('Location: ../pages/esqueci-senha.php?erro=' . urlencode('Não encontramos uma conta com esse e-mail.'));
        exit;
    }

    // Fluxo atual: pergunta + resposta.
    if (!empty($usuario['pergunta_recuperacao']) && !empty($usuario['resposta_recuperacao_hash'])) {
        $_SESSION['recuperacao_email'] = $email;
        $_SESSION['recuperacao_pergunta'] = $usuario['pergunta_recuperacao'];
        $_SESSION['etapa_recuperacao'] = 'pergunta';

        header('Location: ../pages/esqueci-senha.php?etapa=pergunta');
        exit;
    }

    // Compatibilidade com contas antigas que ainda possuem palavra-chave.
    if (!empty($usuario['chave_recuperacao_hash'])) {
        $_SESSION['recuperacao_email'] = $email;
        $_SESSION['etapa_recuperacao'] = 'chave';

        header('Location: ../pages/esqueci-senha.php?etapa=chave');
        exit;
    }

    header('Location: ../pages/esqueci-senha.php?erro=' . urlencode('Esta conta não possui uma forma de recuperação cadastrada.'));
    exit;
}

/* Validação da pergunta/resposta. */
if ($etapa === 'pergunta') {
    $email = $_SESSION['recuperacao_email'] ?? '';
    $resposta = trim($_POST['resposta_recuperacao'] ?? '');

    if ($email === '' || $resposta === '') {
        $conexao->close();
        header('Location: ../pages/esqueci-senha.php?etapa=pergunta&erro=' . urlencode('Informe a resposta da pergunta.'));
        exit;
    }

    $stmt = $conexao->prepare(
        'SELECT id, pergunta_recuperacao, resposta_recuperacao_hash
         FROM usuarios
         WHERE email = ?
         LIMIT 1'
    );

    if (!$stmt) {
        error_log('TopTurismo recuperação - erro no SELECT resposta: ' . $conexao->error);
        $conexao->close();
        header('Location: ../pages/esqueci-senha.php?etapa=pergunta&erro=' . urlencode('Não foi possível validar a resposta.'));
        exit;
    }

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
        header('Location: ../pages/esqueci-senha.php?etapa=pergunta&erro=' . urlencode('Resposta incorreta.'));
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['recuperacao_usuario_id'] = (int) $usuario['id'];
    $_SESSION['recuperacao_expira'] = time() + 900; // 15 minutos
    $_SESSION['etapa_recuperacao'] = 3;
    unset($_SESSION['recuperacao_email'], $_SESSION['recuperacao_pergunta']);

    // Mantém compatibilidade com o código antigo que usava id_recuperacao.
    $_SESSION['id_recuperacao'] = (int) $usuario['id'];

    header('Location: ../pages/redefinir-senha.php');
    exit;
}

/* Compatibilidade com a palavra-chave da versão anterior. */
if ($etapa === 'chave') {
    $email = $_SESSION['recuperacao_email'] ?? '';
    $chave = trim($_POST['chave_recuperacao'] ?? '');

    if ($email === '' || $chave === '') {
        $conexao->close();
        header('Location: ../pages/esqueci-senha.php?etapa=chave&erro=' . urlencode('Informe sua palavra-chave de recuperação.'));
        exit;
    }

    $stmt = $conexao->prepare('SELECT id, chave_recuperacao_hash FROM usuarios WHERE email = ? LIMIT 1');
    if (!$stmt) {
        error_log('TopTurismo recuperação - erro no SELECT chave: ' . $conexao->error);
        $conexao->close();
        header('Location: ../pages/esqueci-senha.php?etapa=chave&erro=' . urlencode('Não foi possível validar a palavra-chave.'));
        exit;
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conexao->close();

    if (!$usuario || empty($usuario['chave_recuperacao_hash']) || !password_verify($chave, $usuario['chave_recuperacao_hash'])) {
        header('Location: ../pages/esqueci-senha.php?etapa=chave&erro=' . urlencode('Palavra-chave incorreta.'));
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['recuperacao_usuario_id'] = (int) $usuario['id'];
    $_SESSION['recuperacao_expira'] = time() + 900;
    $_SESSION['etapa_recuperacao'] = 3;
    $_SESSION['id_recuperacao'] = (int) $usuario['id'];
    unset($_SESSION['recuperacao_email']);

    header('Location: ../pages/redefinir-senha.php');
    exit;
}

$conexao->close();
header('Location: ../pages/esqueci-senha.php');
exit;
