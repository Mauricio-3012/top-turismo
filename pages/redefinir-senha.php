<!DOCTYPE html>
<html lang="pt-br">

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
                    <a href="../index.php">
                        <img src="../assets/imagens/logo-favicon.ico" alt="Logo">
                        <span>TopTurismo</span>
                    </a>
                </header>

                <h1 class="titulo-principal">Nova senha</h1>
                <p class="subtitulo">Digite e confirme sua nova senha de acesso.</p>

                <p id="mensagem-erro" style="color: red; display: none;"></p>

                <form method="POST" action="../php/redefinir-senha.php" id="form-redefinir">
                    <input type="hidden" name="token" id="token">

                    <div class="campo-entrada">
                        <label>Nova senha</label>
                        <input type="password" name="senha" id="senha" placeholder="Digite a nova senha" required minlength="6">
                    </div>

                    <div class="campo-entrada">
                        <label>Confirmar nova senha</label>
                        <input type="password" name="confirmar_senha" id="confirmar_senha" placeholder="Digite novamente" required minlength="6">
                    </div>

                    <button type="submit" class="botao-continuar">Redefinir senha</button>
                </form>

                <div class="extra">
                    <a href="./login.php">Voltar para o login</a>
                </div>
            </div>
        </section>

        <section class="secao-hero-imagem">
            <a href="../index.php" class="botao-fechar-tela" title="Sair">✕</a>
        </section>
    </main>

    <script>
        // O token vem da URL e é enviado ao endpoint PHP junto com a nova senha.
        // A validação definitiva do token deve sempre acontecer no servidor.
        const params = new URLSearchParams(window.location.search);
        const token = params.get('token');

        document.getElementById('token').value = token || '';

        // Validação de interface para evitar o envio de duas senhas diferentes.
        // O backend também deve repetir essa validação antes de atualizar a senha.
        document.getElementById('form-redefinir').addEventListener('submit', function (e) {
            const senha = document.getElementById('senha').value;
            const confirmar = document.getElementById('confirmar_senha').value;
            const mensagemErro = document.getElementById('mensagem-erro');

            if (senha !== confirmar) {
                e.preventDefault();
                mensagemErro.textContent = 'As senhas não coincidem.';
                mensagemErro.style.display = 'block';
            }
        });
    </script>
</body>

</html>