<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TopTurismo</title>
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

                <h1 class="titulo-principal">Olá!</h1>
                <p class="subtitulo">Acesse sua conta para continuar.</p>

                <?php if (($_GET["sucesso"] ?? "") === "senha"): ?>
                    <div style="font-weight: bold; color: #166534; padding: 17px; background-color: #DCFCE7; border-radius: 15px; margin-bottom: 10px">Senha redefinida com sucesso. Faça login com sua nova senha.</div>
                <?php endif; ?>
                <div id="mensagem-erro" style="font-weight: bold; color: #111827; display: none; padding: 17px; background-color: #FDF0F4; border-radius: 15px; margin-bottom: 10px"></div>

                <!-- *envia o e-mail e a senha para o PHP autenticar o usuário* -->
                <form method="POST" action="../php/login.php" id="formLogin" novalidate>
                    <div class="campo-entrada">
                        <label>E-mail</label>
                        <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                    </div>

                    <div class="campo-entrada">
                        <label>Senha</label>
                        <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                    </div>

                    <button type="submit" class="botao-continuar">Entrar</button>
                </form>
                <div class="extra">
                    <p class="mb-3">Novo por aqui? <a href="./cadastro.php">Cadastre-se</a></p>
                    <!-- *abre o fluxo de recuperação de senha* -->
                    <p><a href="./esqueci-senha.php">Esqueci minha senha</a></p>
                </div>
            </div>
        </section>

        <section class="secao-hero-imagem">
            <a href="../../public/index.php" class="botao-fechar-tela" title="Sair">✕</a>
        </section>
    </main>

    <script src="../assets/js/validacoes.js"></script>
    <script>
        // *mostra na tela a mensagem retornada pelo PHP após uma tentativa de login*
        document.addEventListener("DOMContentLoaded", () => {
            const params = new URLSearchParams(window.location.search);
            const erro = params.get("erro");
            const mensagemErro = document.getElementById("mensagem-erro");

            if (!erro || !mensagemErro) return;

            const mensagens = {
                "1": "Não encontramos seu usuário. Verifique seus dados e tente novamente.",
                "2": "Preencha o e-mail e a senha para continuar.",
            };

            mensagemErro.textContent = mensagens[erro] || "Não foi possível fazer login. Tente novamente.";
            mensagemErro.style.display = "block";
        });

        // *valida os campos antes de enviar o formulário ao servidor*
        const formLogin = document.getElementById("formLogin");
        const campoEmailLogin = document.getElementById("email");
        const campoSenhaLogin = document.getElementById("senha");

        formLogin.addEventListener("submit", (evento) => {
            const erroEmail = validarEmail(campoEmailLogin.value);
            const erroSenha = validarCampoObrigatorio(campoSenhaLogin.value, "A senha");

            exibirErroCampo(campoEmailLogin, erroEmail);
            exibirErroCampo(campoSenhaLogin, erroSenha);

            if (erroEmail || erroSenha) {
                evento.preventDefault();
            }
        });
    </script>
</body>

</html>
