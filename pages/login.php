<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TopTurismo</title>
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

                <h1 class="titulo-principal">Olá!</h1>
                <p class="subtitulo">Acesse sua conta para continuar.</p>

                <p id="mensagem-erro" style="color: red; display: none;"></p>

                <form method="POST" action="../php/login.php">
                    <div class="campo-entrada">
                        <label>E-mail</label>
                        <input type="email" name="email" placeholder="Digite seu e-mail" required>
                    </div>

                    <div class="campo-entrada">
                        <label>Senha</label>
                        <input type="password" name="senha" placeholder="Digite sua senha" required>
                    </div>

                    <button type="submit" class="botao-continuar">Entrar</button>
                </form>

                <div class="extra">
                    <p class="mb-3">Novo por aqui? <a href="./cadastro.php">Cadastre-se</a></p>

                    <p><a href="./esqueci-senha.php">Esqueci minha senha</a></p>
                </div>
            </div>
        </section>

        <section class="secao-hero-imagem">
            <a href="../index.php" class="botao-fechar-tela" title="Sair">✕</a>
        </section>
    </main>
</body>

</html>