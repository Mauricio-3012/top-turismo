<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - TopTurismo</title>
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

                <h1 class="titulo-principal">Cadastre-se</h1>
                <p class="subtitulo">Crie sua conta para começar a viajar.</p>

                <form method="POST" action="../php/cadastro.php">
                    <div class="campo-entrada">
                        <label>Nome completo</label>
                        <input type="text" name="nome" placeholder="Nome como no documento" required>
                    </div>

                    <div class="campo-entrada">
                        <label>CPF</label>
                        <input type="text" name="cpf" placeholder="000.000.000-00" required>
                    </div>

                    <div class="grid-2-colunas">
                        <div class="campo-entrada">
                            <label>Data de nascimento</label>
                            <input type="date" name="data_nascimento" required>
                        </div>
                        <div class="campo-entrada">
                            <label>Gênero</label>
                            <select name="genero" required>
                                <option value="">Selecione</option>
                                <option>Masculino</option>
                                <option>Feminino</option>
                                <option>Outro</option>
                            </select>
                        </div>
                    </div>

                    <div class="campo-entrada">
                        <label>E-mail</label>
                        <input type="email" name="email" placeholder="exemplo@email.com" required>
                    </div>

                    <div class="campo-entrada">
                        <label>Telefone</label>
                        <input type="tel" name="telefone" placeholder="(00) 00000-0000" required>
                    </div>

                    <div class="grid-2-colunas">
                        <div class="campo-entrada">
                            <label>Senha</label>
                            <input type="password" name="senha" placeholder="Mínimo 8 caracteres" required>
                        </div>
                        <div class="campo-entrada">
                            <label>Confirmar senha</label>
                            <input type="password" name="confirmar_senha" placeholder="Repita a senha" required>
                        </div>
                    </div>

                    <button type="submit" class="botao-continuar">Finalizar Cadastro</button>
                </form>

                <div class="extra">
                    Já possui conta? <a href="./login.php">Fazer Login</a>
                </div>
            </div>
        </section>

        <section class="secao-hero-imagem">
            <a href="../index.php" class="botao-fechar-tela" title="Sair">✕</a>
        </section>
    </main>
</body>

</html>