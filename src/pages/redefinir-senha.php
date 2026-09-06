<?php
session_start();
require_once __DIR__ . '/../php/conexao.php';

$token = trim((string) ($_GET['token'] ?? ''));
$erro = $_GET['erro'] ?? '';
$permitido = false;

if (preg_match('/^[a-f0-9]{64}$/', $token)) {
    $tokenHash = hash('sha256', $token);
    $stmt = $conexao->prepare(
        'SELECT expira_em, verificado
         FROM recuperacoes_senha WHERE token_hash = ? LIMIT 1'
    );
    if ($stmt) {
        $stmt->bind_param('s', $tokenHash);
        $stmt->execute();
        $stmt->bind_result($expiraEm, $verificado);
        $encontrou = $stmt->fetch();
        $stmt->close();
        $permitido = $encontrou && (int) $verificado === 1 && strtotime($expiraEm) >= time();
    }
}

$conexao->close();

if (!$permitido) {
    header('Location: ./esqueci-senha.php?erro=' . urlencode('Sua recuperação expirou. Comece novamente.'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir senha - TopTurismo</title>
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

                <h1 class="titulo-principal">Nova senha</h1>
                <p class="subtitulo">Crie uma nova senha para acessar sua conta.</p>

                <?php if ($erro): ?>
                    <div class="mensagem-erro-servidor"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="POST" action="../php/redefinir-senha.php">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="campo-entrada">
                        <label for="senha">Nova senha</label>
                        <input type="password" id="senha" name="senha" placeholder="Mínimo 8 caracteres" minlength="8" required autocomplete="new-password">
                    </div>
                    <div class="campo-entrada">
                        <label for="confirmar_senha">Confirmar nova senha</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Digite novamente" minlength="8" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="botao-continuar">Redefinir senha</button>
                </form>

                <div class="extra"><a href="./login.php">Voltar para o login</a></div>
            </div>
        </section>
        <section class="secao-hero-imagem">
            <a href="../../public/index.php" class="botao-fechar-tela" title="Sair">✕</a>
        </section>
    </main>
</body>
</html>
