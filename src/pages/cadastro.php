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
                    <a href="../../public/index.php">
                        <img src="../assets/imagens/logo-favicon.ico" alt="Logo">
                        <span>TopTurismo</span>
                    </a>
                </header>

                <h1 class="titulo-principal">Cadastre-se</h1>
                <p class="subtitulo">Crie sua conta para começar a viajar.</p>

                <?php if (!empty($erro)): ?>
                    <div class="mensagem-erro-servidor" style="font-weight: bold; color: #111827; padding: 17px; background-color: #FDF0F4; border-radius: 15px; margin-bottom: 10px">
                        <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <!-- *envia o cadastro para validação e gravação no PHP* -->
                <form method="POST" action="../php/cadastro.php" id="formCadastro" novalidate>
                    <div class="campo-entrada">
                        <label>Nome completo</label>
                        <input type="text" id="nome" name="nome" placeholder="Nome como no documento" required>
                    </div>

                    <div class="campo-entrada">
                        <label>CPF</label>
                        <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" maxlength="14" inputmode="numeric" required>
                    </div>

                    <div class="grid-2-colunas">
                        <div class="campo-entrada">
                            <label>Data de nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" required>
                        </div>
                        <div class="campo-entrada">
                            <label>Gênero</label>
                            <select id="genero" name="genero" required>
                                <option value="">Selecione</option>
                                <option>Masculino</option>
                                <option>Feminino</option>
                                <option>Outro</option>
                            </select>
                        </div>
                    </div>

                    <div class="campo-entrada">
                        <label>E-mail</label>
                        <input type="email" id="email" name="email" placeholder="exemplo@email.com" required>
                    </div>

                    <div class="campo-entrada">
                        <label>Telefone</label>
                        <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" maxlength="15" inputmode="numeric" required>
                    </div>

                    <div class="campo-entrada">
                        <label>Cidade</label>
                        <input type="text" id="cidade" name="cidade" placeholder="Sua cidade" required>
                    </div>

                    <div class="campo-entrada">
                        <label for="pergunta_seguranca">Pergunta de segurança</label>
                        <select id="pergunta_seguranca" name="pergunta_seguranca" required>
                            <option value="">Selecione uma pergunta</option>
                            <option value="Qual é o nome do seu primeiro pet?">Qual é o nome do seu primeiro pet?</option>
                            <option value="Qual é o nome da sua cidade natal?">Qual é o nome da sua cidade natal?</option>
                            <option value="Qual era o nome da sua escola?">Qual era o nome da sua escola?</option>
                            <option value="Qual é o seu destino turístico favorito?">Qual é o seu destino turístico favorito?</option>
                        </select>
                    </div>

                    <div class="campo-entrada">
                        <label for="resposta_seguranca">Resposta da pergunta</label>
                        <input type="text" id="resposta_seguranca" name="resposta_seguranca" placeholder="Digite sua resposta" minlength="2" maxlength="255" required>
                        <small>Essa resposta será usada para confirmar sua identidade caso esqueça sua senha.</small>
                    </div>

                    <div class="grid-2-colunas">
                        <div class="campo-entrada">
                            <label>Senha</label>
                            <input type="password" id="senha" name="senha" placeholder="Mínimo 8 caracteres" required>
                        </div>
                        <div class="campo-entrada">
                            <label>Confirmar senha</label>
                            <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Repita a senha" required>
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
            <a href="../../public/index.php" class="botao-fechar-tela" title="Sair">✕</a>
        </section>
    </main>

    <script src="../assets/js/validacoes.js"></script>
    <script>
        // *aplica máscaras e validações rápidas para melhorar o preenchimento*
        const formCadastro = document.getElementById("formCadastro");
        const campoNome = document.getElementById("nome");
        const campoCpf = document.getElementById("cpf");
        const campoDataNascimento = document.getElementById("data_nascimento");
        const campoGenero = document.getElementById("genero");
        const campoEmail = document.getElementById("email");
        const campoTelefone = document.getElementById("telefone");
        const campoCidade = document.getElementById("cidade");
        const campoPerguntaSeguranca = document.getElementById("pergunta_seguranca");
        const campoRespostaSeguranca = document.getElementById("resposta_seguranca");
        const campoSenha = document.getElementById("senha");
        const campoConfirmarSenha = document.getElementById("confirmar_senha");

        aplicarMascaraCPF(campoCpf);
        aplicarMascaraTelefone(campoTelefone);

        function validarCampo(input, funcaoValidadora, ...args) {
            const mensagem = funcaoValidadora(...args);
            exibirErroCampo(input, mensagem);
            return !mensagem;
        }

        campoNome.addEventListener("blur", () => validarCampo(campoNome, validarNome, campoNome.value));
        campoCpf.addEventListener("blur", () => validarCampo(campoCpf, validarCPF, campoCpf.value));
        campoDataNascimento.addEventListener("blur", () => validarCampo(campoDataNascimento, validarDataNascimento, campoDataNascimento.value));
        campoGenero.addEventListener("change", () => validarCampo(campoGenero, validarCampoObrigatorio, campoGenero.value, "O gênero"));
        campoEmail.addEventListener("blur", () => validarCampo(campoEmail, validarEmail, campoEmail.value));
        campoTelefone.addEventListener("blur", () => validarCampo(campoTelefone, validarTelefone, campoTelefone.value));
        campoCidade.addEventListener("blur", () => validarCampo(campoCidade, validarCampoObrigatorio, campoCidade.value, "A cidade"));
        campoPerguntaSeguranca.addEventListener("change", () => validarCampo(campoPerguntaSeguranca, validarCampoObrigatorio, campoPerguntaSeguranca.value, "A pergunta de segurança"));
        campoRespostaSeguranca.addEventListener("blur", () => validarCampo(campoRespostaSeguranca, (valor) => valor.trim().length < 2 ? "A resposta deve ter pelo menos 2 caracteres." : "", campoRespostaSeguranca.value));
        campoSenha.addEventListener("blur", () => validarCampo(campoSenha, validarSenha, campoSenha.value));
        campoConfirmarSenha.addEventListener("blur", () => validarCampo(campoConfirmarSenha, validarConfirmarSenha, campoSenha.value, campoConfirmarSenha.value));

        formCadastro.addEventListener("submit", (evento) => {
            const validacoes = [
                validarCampo(campoNome, validarNome, campoNome.value),
                validarCampo(campoCpf, validarCPF, campoCpf.value),
                validarCampo(campoDataNascimento, validarDataNascimento, campoDataNascimento.value),
                validarCampo(campoGenero, validarCampoObrigatorio, campoGenero.value, "O gênero"),
                validarCampo(campoEmail, validarEmail, campoEmail.value),
                validarCampo(campoTelefone, validarTelefone, campoTelefone.value),
                validarCampo(campoCidade, validarCampoObrigatorio, campoCidade.value, "A cidade"),
                validarCampo(campoPerguntaSeguranca, validarCampoObrigatorio, campoPerguntaSeguranca.value, "A pergunta de segurança"),
                validarCampo(campoRespostaSeguranca, (valor) => valor.trim().length < 2 ? "A resposta deve ter pelo menos 2 caracteres." : "", campoRespostaSeguranca.value),
                validarCampo(campoSenha, validarSenha, campoSenha.value),
                validarCampo(campoConfirmarSenha, validarConfirmarSenha, campoSenha.value, campoConfirmarSenha.value),
            ];

            if (validacoes.includes(false)) {
                evento.preventDefault();
                const primeiroInvalido = formCadastro.querySelector(".campo-invalido");
                if (primeiroInvalido) primeiroInvalido.focus();
            }
        });
    </script>
</body>

</html>
