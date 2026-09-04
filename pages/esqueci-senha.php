<?php
session_start();

$etapa = $_GET["etapa"] ?? "email";
$erro = $_GET["erro"] ?? "";
$email = $_SESSION["recuperacao_email"] ?? "";

if ($etapa === "chave" && $email === "") {
    $etapa = "email";
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
                    <a href="../index.php">
                        <img src="../assets/imagens/logo-favicon.ico" alt="Logo">
                        <span>TopTurismo</span>
                    </a>
                </header>

                <!-- *primeira etapa: identifica a conta pelo e-mail* -->
                <?php if ($etapa === "email"): ?>
                    <h1 class="titulo-principal">Esqueceu a senha?</h1>
                    <p class="subtitulo">Informe seu e-mail para iniciar a recuperação.</p>
                    <?php if ($erro): ?>
                        <div class="mensagem-erro-servidor"><?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?></div>
                    <?php endif; ?>

                    <!-- *envia cada etapa da recuperação para o PHP* -->
                    <form method="POST" action="../php/esqueci-senha.php">
                        <input type="hidden" name="etapa" value="email">
                        <div class="campo-entrada">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" placeholder="Digite seu e-mail cadastrado" required>
                        </div>
                        <button type="submit" class="botao-continuar">Continuar</button>
                    </form>
                <!-- *segunda etapa: confirma a palavra-chave cadastrada* -->
                <?php else: ?>
                    <h1 class="titulo-principal">Confirme sua identidade</h1>
                    <p class="subtitulo">Digite a palavra-chave de recuperação cadastrada na sua conta.</p>
                    <?php if ($erro): ?>
                        <div class="mensagem-erro-servidor"><?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?></div>
                    <?php endif; ?>

                    <form method="POST" action="../php/esqueci-senha.php">
                        <input type="hidden" name="etapa" value="chave">
                        <div class="campo-entrada">
                            <label for="chave_recuperacao">Palavra-chave de recuperação</label>
                            <input type="password" id="chave_recuperacao" name="chave_recuperacao" placeholder="Digite sua palavra-chave" required minlength="4">
                        </div>
                        <button type="submit" class="botao-continuar">Validar palavra-chave</button>
                    </form>
                <?php endif; ?>

                <div class="extra">
                    Lembrou a senha? <a href="./login.php">Voltar para o login</a>
                </div>
            </div>
        </section>

        <section class="secao-hero-imagem">
            <a href="../index.php" class="botao-fechar-tela" title="Sair">✕</a>
        </section>
    </main>
</body>
</html>