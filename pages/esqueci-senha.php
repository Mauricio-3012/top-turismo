<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci minha senha - TopTurismo</title>
    <link rel="stylesheet" href="../assets/css/login-cadastro.css">
    <link rel="shortcut icon" href="../assets/imagens/logo-favicon.ico" type="image/x-icon">
</head>

<body>
    <main class="container-principal">
        <section class="secao-login">
            <div class="conteudo-central">
                <header class="logotipo">
                    <a href="../index.html">
                        <img src="../assets/imagens/logo-favicon.ico" alt="Logo">
                        <span>TopTurismo</span>
                    </a>
                </header>

                <h1 class="titulo-principal">Esqueceu a senha?</h1>
                <p class="subtitulo">Digite seu e-mail e enviaremos as instruções para redefinir sua senha.</p>

                <p id="mensagem-erro" style="color: red; display: none;"></p>

                <form method="POST" action="../php/esqueci-senha.php">
                    <div class="campo-entrada">
                        <label>E-mail</label>
                        <input type="email" name="email" placeholder="Digite seu e-mail cadastrado" required>
                    </div>

                    <button type="submit" class="botao-continuar">Enviar instruções</button>
                </form>

                <div class="extra">
                    Lembrou a senha? <a href="./login.html">Voltar para o login</a>
                </div>
            </div>
        </section>

        <section class="secao-hero-imagem">
            <a href="../index.html" class="botao-fechar-tela" title="Sair">✕</a>
        </section>
    </main>
</body>

</html>