<?php
session_start();

$etapa = $_GET["etapa"] ?? "email";
$erro = $_GET["erro"] ?? "";
$email = $_SESSION["recuperacao_email"] ?? "";
$pergunta = $_SESSION["recuperacao_pergunta"] ?? "";

if (($etapa === "pergunta" || $etapa === "chave") && $email === "") {
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

                <?php if ($etapa === "email"): ?>
                    <h1 class="titulo-principal">Esqueceu a senha?</h1>
                    <p class="subtitulo">Informe seu e-mail para iniciar a recuperação.</p>
                    <?php if ($erro): ?><div class="mensagem-erro-servidor"><?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?></div><?php endif; ?>

                    <form method="POST" action="../php/esqueci-senha.php">
                        <input type="hidden" name="etapa" value="email">
                        <div class="campo-entrada">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" placeholder="Digite seu e-mail cadastrado" required autocomplete="email">
                        </div>
                        <button type="submit" class="botao-continuar">Continuar</button>
                    </form>

                <?php elseif ($etapa === "pergunta"): ?>
                    <h1 class="titulo-principal">Confirme sua identidade</h1>
                    <p class="subtitulo">Responda à pergunta cadastrada para continuar.</p>
                    <?php if ($erro): ?><div class="mensagem-erro-servidor"><?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?></div><?php endif; ?>

                    <form method="POST" action="../php/esqueci-senha.php">
                        <input type="hidden" name="etapa" value="pergunta">
                        <div class="campo-entrada">
                            <label for="resposta_recuperacao"><?= htmlspecialchars($pergunta, ENT_QUOTES, "UTF-8") ?></label>
                            <input type="text" id="resposta_recuperacao" name="resposta_recuperacao" placeholder="Digite sua resposta" required autocomplete="off">
                        </div>
                        <button type="submit" class="botao-continuar">Validar resposta</button>
                    </form>

                <?php else: ?>
                    <h1 class="titulo-principal">Confirme sua identidade</h1>
                    <p class="subtitulo">Digite a palavra-chave de recuperação cadastrada na sua conta.</p>
                    <?php if ($erro): ?><div class="mensagem-erro-servidor"><?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?></div><?php endif; ?>

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
            <a href="../../public/index.php" class="botao-fechar-tela" title="Sair">✕</a>
        </section>
    </main>
</body>
</html>
