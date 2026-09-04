<?php
session_start();

$idRecuperacao = (int) ($_SESSION["recuperacao_usuario_id"] ?? $_SESSION["id_recuperacao"] ?? 0);
$expiraRecuperacao = (int) ($_SESSION["recuperacao_expira"] ?? 0);
$etapaRecuperacao = (string) ($_SESSION["etapa_recuperacao"] ?? "autorizada");

$autorizado = $idRecuperacao > 0
    && ($expiraRecuperacao === 0 || time() <= $expiraRecuperacao)
    && in_array($etapaRecuperacao, ["autorizada", "resposta_validada", "chave_validada", "3"], true);

if (!$autorizado) {
    header("Location: ./esqueci-senha.php?erro=Sua recuperação expirou. Comece novamente.");
    exit;
}

$erro = $_GET["erro"] ?? "";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir senha - TopTurismo</title>
    <link rel="stylesheet" href="../assets/css/login-cadastro.css">
    <link rel="shortcut icon" href="../assets/imagens/logo-favicon.ico" type="image/x-icon">
<script>window.TOP_TURISMO_BASE = "../../";</script>
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

                <!-- *mostra qualquer erro devolvido pelo PHP durante a redefinição* -->
                <?php if ($erro): ?>
                    <div class="mensagem-erro-servidor"><?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?></div>
                <?php endif; ?>

                <!-- *envia a nova senha para ser armazenada com hash pelo PHP* -->
                <form method="POST" action="../php/redefinir-senha.php">
                    <div class="campo-entrada">
                        <label for="senha">Nova senha</label>
                        <input type="password" id="senha" name="senha" placeholder="Mínimo 8 caracteres" minlength="8" required>
                    </div>
                    <div class="campo-entrada">
                        <label for="confirmar_senha">Confirmar nova senha</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Digite novamente" minlength="8" required>
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
