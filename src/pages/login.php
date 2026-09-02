<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../php/conexao.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}

/* Processa as duas primeiras etapas da recuperação antes de renderizar a página. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    if ($acao === 'verificar_email') {
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: login.php?recuperacao=1&erro=' . urlencode('Informe um e-mail válido.'));
            exit;
        }

        $stmt = $conexao->prepare(
            'SELECT id, pergunta_seguranca, resposta_seguranca_hash FROM usuarios WHERE email = ? LIMIT 1'
        );

        if (!$stmt) {
            header('Location: login.php?recuperacao=1&erro=' . urlencode('Não foi possível iniciar a recuperação. Tente novamente.'));
            exit;
        }

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$usuario || empty($usuario['pergunta_seguranca']) || empty($usuario['resposta_seguranca_hash'])) {
            header('Location: login.php?recuperacao=1&erro=' . urlencode('Não foi possível iniciar a recuperação com esse e-mail.'));
            exit;
        }

        $_SESSION['recuperacao_email'] = $email;
        $_SESSION['id_recuperacao'] = (int) $usuario['id'];
        $_SESSION['etapa_recuperacao'] = 2;
        $_SESSION['recuperacao_expira'] = time() + 900;
        $_SESSION['pergunta_recuperacao'] = $usuario['pergunta_seguranca'];

        header('Location: login.php');
        exit;
    }

    if ($acao === 'validar_resposta') {
        $idUsuario = (int) ($_SESSION['id_recuperacao'] ?? 0);
        $expira = (int) ($_SESSION['recuperacao_expira'] ?? 0);
        $resposta = trim($_POST['resposta_seguranca'] ?? '');

        if (!$idUsuario || time() > $expira) {
            unset(
                $_SESSION['id_recuperacao'],
                $_SESSION['etapa_recuperacao'],
                $_SESSION['recuperacao_email'],
                $_SESSION['pergunta_recuperacao'],
                $_SESSION['recuperacao_expira']
            );
            header('Location: login.php?erro=' . urlencode('Sua recuperação expirou. Comece novamente.'));
            exit;
        }

        $stmt = $conexao->prepare(
            'SELECT resposta_seguranca_hash FROM usuarios WHERE id = ? LIMIT 1'
        );

        if (!$stmt) {
            header('Location: login.php?erro=' . urlencode('Não foi possível validar sua resposta.'));
            exit;
        }

        $stmt->bind_param('i', $idUsuario);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $respostaNormalizada = mb_strtolower($resposta, 'UTF-8');

        if (
            !$usuario
            || empty($usuario['resposta_seguranca_hash'])
            || !password_verify($respostaNormalizada, $usuario['resposta_seguranca_hash'])
        ) {
            header('Location: login.php?erro=' . urlencode('Resposta incorreta. Tente novamente.'));
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['id_recuperacao'] = $idUsuario;
        $_SESSION['etapa_recuperacao'] = 3;
        $_SESSION['recuperacao_expira'] = time() + 900;

        header('Location: login.php');
        exit;
    }

}

/*
 * O fluxo de recuperação segue o mesmo padrão do movieAppMat:
 * 1. usuário informa o e-mail;
 * 2. responde à pergunta de segurança cadastrada;
 * 3. cria uma nova senha.
 *
 * A resposta de segurança continua armazenada como hash no banco.
 */
if (isset($_GET['cancelar_recuperacao'])) {
    unset(
        $_SESSION['id_recuperacao'],
        $_SESSION['etapa_recuperacao'],
        $_SESSION['recuperacao_email'],
        $_SESSION['pergunta_recuperacao'],
        $_SESSION['recuperacao_expira']
    );
    header('Location: login.php');
    exit;
}

$etapaRecuperacao = (int) ($_SESSION['etapa_recuperacao'] ?? 1);
$emailRecuperacao = $_SESSION['recuperacao_email'] ?? '';

if ($etapaRecuperacao < 1 || $etapaRecuperacao > 3) {
    $etapaRecuperacao = 1;
}

if ($etapaRecuperacao > 1 && $emailRecuperacao === '') {
    $etapaRecuperacao = 1;
    unset($_SESSION['id_recuperacao'], $_SESSION['etapa_recuperacao'], $_SESSION['pergunta_recuperacao'], $_SESSION['recuperacao_expira']);
}

$erro = $_GET['erro'] ?? '';
$sucesso = $_GET['sucesso'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $etapaRecuperacao === 1 ? 'Login' : ($etapaRecuperacao === 2 ? 'Recuperar senha' : 'Redefinir senha') ?> - TopTurismo</title>
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

                <?php if ($sucesso === 'senha'): ?>
                    <div style="font-weight: bold; color: #166534; padding: 17px; background-color: #DCFCE7; border-radius: 15px; margin-bottom: 10px">
                        Senha redefinida com sucesso. Faça login com sua nova senha.
                    </div>
                <?php endif; ?>

                <?php if ($erro): ?>
                    <div id="mensagem-erro" style="font-weight: bold; color: #111827; padding: 17px; background-color: #FDF0F4; border-radius: 15px; margin-bottom: 10px">
                        <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php else: ?>
                    <div id="mensagem-erro" style="font-weight: bold; color: #111827; display: none; padding: 17px; background-color: #FDF0F4; border-radius: 15px; margin-bottom: 10px"></div>
                <?php endif; ?>

                <?php if ($etapaRecuperacao === 3): ?>
                    <div id="bloco-nova-senha">
                        <h1 class="titulo-principal">Nova senha</h1>
                        <p class="subtitulo">Identidade confirmada. Crie uma nova senha para acessar sua conta.</p>

                        <form method="POST" action="../php/processar_nova_senha.php">
                            <div class="campo-entrada">
                                <label for="nova_senha">Nova senha</label>
                                <input type="password" id="nova_senha" name="nova_senha" placeholder="Mínimo 8 caracteres" minlength="8" required>
                            </div>

                            <div class="campo-entrada">
                                <label for="confirmar_senha">Confirmar nova senha</label>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="Digite novamente" minlength="8" required>
                            </div>

                            <button type="submit" class="botao-continuar">Redefinir senha</button>
                        </form>

                        <div class="extra">
                            <a href="login.php?cancelar_recuperacao=1">Cancelar e voltar ao login</a>
                        </div>
                    </div>

                <?php elseif ($etapaRecuperacao === 2): ?>
                    <div id="bloco-recuperacao">
                        <h1 class="titulo-principal">Confirme sua identidade</h1>
                        <p class="subtitulo">
                            Responda à pergunta de segurança cadastrada na sua conta.
                        </p>

                        <form method="POST" action="login.php">
                            <input type="hidden" name="acao" value="validar_resposta">

                            <div class="campo-entrada">
                                <label>Pergunta de segurança</label>
                                <div class="campo-pergunta" style="padding: 14px 16px; background: #f5f7fb; border-radius: 12px; font-weight: 600;">
                                    <?= htmlspecialchars($_SESSION['pergunta_recuperacao'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>

                            <div class="campo-entrada">
                                <label for="resposta_seguranca">Resposta</label>
                                <input type="text" id="resposta_seguranca" name="resposta_seguranca"
                                    placeholder="Digite sua resposta" minlength="2" maxlength="255" required>
                            </div>

                            <button type="submit" class="botao-continuar">Validar resposta</button>
                        </form>

                        <div class="extra">
                            <a href="login.php?cancelar_recuperacao=1">← Voltar para o login</a>
                        </div>
                    </div>

                <?php else: ?>
                    <div id="bloco-login">
                        <h1 class="titulo-principal">Olá!</h1>
                        <p class="subtitulo">Acesse sua conta para continuar.</p>

                        <form method="POST" action="../php/login.php" id="formLogin" novalidate>
                            <div class="campo-entrada">
                                <label for="email">E-mail</label>
                                <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
                            </div>

                            <div class="campo-entrada">
                                <label for="senha">Senha</label>
                                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                            </div>

                            <button type="submit" class="botao-continuar">Entrar</button>
                        </form>

                        <div class="extra">
                            <p class="mb-3">Novo por aqui? <a href="./cadastro.php">Cadastre-se</a></p>
                            <p><a href="#" id="link-esqueci-senha">Esqueci minha senha</a></p>
                        </div>
                    </div>

                    <div id="bloco-recuperar" style="display: none;">
                        <h1 class="titulo-principal">Recuperar senha</h1>
                        <p class="subtitulo">Informe seu e-mail para iniciar a recuperação.</p>

                        <form method="POST" action="login.php">
                            <input type="hidden" name="acao" value="verificar_email">

                            <div class="campo-entrada">
                                <label for="email_recuperacao">E-mail da conta</label>
                                <input type="email" id="email_recuperacao" name="email"
                                    placeholder="Digite seu e-mail cadastrado" required>
                            </div>

                            <button type="submit" class="botao-continuar">Continuar</button>
                        </form>

                        <div class="extra">
                            <a href="#" id="link-voltar-login">← Voltar para o login</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="secao-hero-imagem">
            <a href="../../public/index.php" class="botao-fechar-tela" title="Voltar">✕</a>
        </section>
    </main>

    <script src="../assets/js/validacoes.js"></script>

    <?php if ($etapaRecuperacao === 1): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const blocoLogin = document.getElementById('bloco-login');
                const blocoRecuperar = document.getElementById('bloco-recuperar');
                const linkEsqueci = document.getElementById('link-esqueci-senha');
                const linkVoltar = document.getElementById('link-voltar-login');
                const formLogin = document.getElementById('formLogin');

                linkEsqueci?.addEventListener('click', (evento) => {
                    evento.preventDefault();
                    blocoLogin.style.display = 'none';
                    blocoRecuperar.style.display = 'block';
                });

                linkVoltar?.addEventListener('click', (evento) => {
                    evento.preventDefault();
                    blocoRecuperar.style.display = 'none';
                    blocoLogin.style.display = 'block';
                });

                const parametros = new URLSearchParams(window.location.search);
                if (parametros.get('recuperacao') === '1' && blocoLogin && blocoRecuperar) {
                    blocoLogin.style.display = 'none';
                    blocoRecuperar.style.display = 'block';
                }

                if (formLogin) {
                    const campoEmail = document.getElementById('email');
                    const campoSenha = document.getElementById('senha');

                    formLogin.addEventListener('submit', (evento) => {
                        const erroEmail = validarEmail(campoEmail.value);
                        const erroSenha = validarCampoObrigatorio(campoSenha.value, 'A senha');

                        exibirErroCampo(campoEmail, erroEmail);
                        exibirErroCampo(campoSenha, erroSenha);

                        if (erroEmail || erroSenha) {
                            evento.preventDefault();
                        }
                    });
                }
            });
        </script>
    <?php endif; ?>
</body>

</html>
