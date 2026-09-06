<?php
session_start();
require_once __DIR__ . '/../php/conexao.php';

$etapa = $_GET['etapa'] ?? 'email';
$erro = $_GET['erro'] ?? '';
$token = trim((string) ($_GET['token'] ?? ''));
$pergunta = '';

// Na etapa da pergunta, o token identifica a recuperação no banco.
if ($etapa === 'pergunta' && preg_match('/^[a-f0-9]{64}$/', $token)) {
    $tokenHash = hash('sha256', $token);
    $stmt = $conexao->prepare(
        'SELECT u.pergunta_recuperacao, r.expira_em
         FROM recuperacoes_senha r
         INNER JOIN usuarios u ON u.id = r.id_usuario
         WHERE r.token_hash = ? LIMIT 1'
    );

    if ($stmt) {
        $stmt->bind_param('s', $tokenHash);
        $stmt->execute();
        $stmt->bind_result($perguntaBanco, $expiraEm);
        $ok = $stmt->fetch();
        $stmt->close();
        if ($ok && strtotime($expiraEm) >= time()) {
            $pergunta = (string) $perguntaBanco;
        }
    }
}

$conexao->close();

if ($etapa === 'pergunta' && $pergunta === '') {
    $etapa = 'email';
    if ($erro === '') {
        $erro = 'Sua recuperação expirou. Comece novamente.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar senha - TopTurismo</title>
    <link rel="stylesheet" href="../assets/css/login-cadastro.css">
    <link rel="shortcut icon" href="../assets/imagens/logo-favicon.ico" type="image/x-icon">
</head>
<body>
    <main class="container-principal">
        <section class="secao-login">
            <div class="conteudo-central">
                <header class="logotipo">
                    <a href="../../public/index.php">
                        <img src="../assets/imagens/logo-favicon.ico" alt="Logo">
                        <span>TopTurismo</span>
                    </a>
                </header>

                <?php if ($etapa === 'email'): ?>
                    <h1 class="titulo-principal">Esqueceu a senha?</h1>
                    <p class="subtitulo">Informe seu e-mail para recuperar o acesso à sua conta.</p>

                    <?php if ($erro): ?>
                        <div class="mensagem-erro-servidor"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>

                    <form method="POST" action="../php/esqueci-senha.php">
                        <input type="hidden" name="acao" value="email">
                        <div class="campo-entrada">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" placeholder="Digite seu e-mail cadastrado" required autocomplete="email">
                        </div>
                        <button type="submit" class="botao-continuar">Continuar</button>
                    </form>
                <?php else: ?>
                    <h1 class="titulo-principal">Confirme sua identidade</h1>
                    <p class="subtitulo">Responda à pergunta de segurança da sua conta.</p>

                    <?php if ($erro): ?>
                        <div class="mensagem-erro-servidor"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>

                    <form method="POST" action="../php/esqueci-senha.php">
                        <input type="hidden" name="acao" value="resposta">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="campo-entrada">
                            <label for="resposta_recuperacao"><?= htmlspecialchars($pergunta, ENT_QUOTES, 'UTF-8') ?></label>
                            <input type="text" id="resposta_recuperacao" name="resposta_recuperacao" placeholder="Digite sua resposta" required autocomplete="off">
                        </div>
                        <button type="submit" class="botao-continuar">Continuar</button>
                    </form>
                <?php endif; ?>

                <div class="extra">Lembrou a senha? <a href="./login.php">Voltar para o login</a></div>
            </div>
        </section>
        <section class="secao-hero-imagem">
            <a href="../../public/index.php" class="botao-fechar-tela" title="Sair">✕</a>
        </section>
    </main>
</body>
</html>
