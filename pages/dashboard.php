<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../php/conexao.php";

$idUsuario = (int) $_SESSION["usuario_id"];

$stmtUsuario = $conexao->prepare("SELECT id, nome, email, telefone, cidade FROM usuarios WHERE id = ? LIMIT 1");
$stmtUsuario->bind_param("i", $idUsuario);
$stmtUsuario->execute();
$usuarioLogado = $stmtUsuario->get_result()->fetch_assoc();
$stmtUsuario->close();

if (!$usuarioLogado) {
    session_unset();
    session_destroy();
    header("Location: login.php?erro=1");
    exit;
}

$stmtReservas = $conexao->prepare("SELECT
    r.id_reserva, r.id_destino, r.data_viagem, r.data_volta, r.tipo_viagem, r.quantidade_passageiros,
    r.transporte, r.classe, r.assento, r.tipo_assento, r.pagamento, r.parcelas, r.taxa_juros_percentual,
    r.horario_ida, r.horario_volta, r.duracao_voo_minutos, r.valor_total,
    CASE WHEN LOWER(r.status) = 'cancelada' THEN 'cancelada' ELSE 'confirmada' END AS status,
    d.nome_destino, d.cidade_destino, d.pais_destino, d.img_destino
    FROM reservas r
    INNER JOIN destinos d ON d.id_destino = r.id_destino
    WHERE r.id_usuario = ?
    ORDER BY r.data_viagem DESC, r.id_reserva DESC");
$stmtReservas->bind_param("i", $idUsuario);
$stmtReservas->execute();
$resultadoReservas = $stmtReservas->get_result();
$reservasIniciais = $resultadoReservas->fetch_all(MYSQLI_ASSOC);
$stmtReservas->close();
$conexao->close();

$_SESSION["usuario_nome"] = $usuarioLogado["nome"];
$reservasJson = json_encode($reservasIniciais, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="shortcut icon" href="../assets/imagens/logo-favicon.ico" type="image/x-icon">
    <title>Meu Perfil - TopTurismo</title>
</head>

<body>
    <header>
        <nav class="navbar fixed-top navbar-expand-lg custom-bg p-3">
            <div class="container-fluid d-flex align-items-center flex-wrap">
                <div class="d-flex align-items-center">
                    <img src="../assets/imagens/logo-white.png" width="50" height="50" alt="Logo">
                    <a href="../index.php" class="text-a ms-2 logo-texto">TopTurismo</a>
                </div>

                <div class="flex-grow-1 d-none d-lg-flex justify-content-center">
                    <ul class="navbar-nav flex-row gap-4">
                        <li class="nav-item"><a class="nav-link text-white" href="../index.php#destinos">Destinos</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="../index.php#reservar">Fazer Reserva</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="../index.php#sobre-nos">Sobre nós</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="../index.php#contato">Contato</a></li>
                    </ul>
                </div>

                <div class="d-flex align-items-center ms-auto gap-3">
                    <div class="dropdown">
                        <a href="#" class="text-white fs-4 user-icon dropdown-toggle" id="userAuthMenu" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" id="userAuthMenuList">
                            <li><a class="dropdown-item" href="#meus-dados"><i class="bi bi-person-fill me-2"></i>Meu Perfil</a></li>
                            <?php if (($_SESSION["usuario_tipo"] ?? "cliente") === "admin"): ?><li><a class="dropdown-item" href="../admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Painel Admin</a></li><?php endif; ?>
                            <li><a class="dropdown-item" href="../php/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                        </ul>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="temaMenu"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-circle-half"></i> Tema
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" onclick="setTheme('light')"><i
                                        class="bi bi-sun-fill me-2"></i>Claro</a></li>
                            <li><a class="dropdown-item" onclick="setTheme('dark')"><i
                                        class="bi bi-moon-fill me-2"></i>Escuro</a></li>
                        </ul>
                    </div>
                    <button class="btn text-white d-lg-none" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#menuMobile" aria-controls="menuMobile">
                        <i class="bi bi-list fs-2"></i>
                    </button>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="profile-header">
            <div class="container text-center">
                <div class="profile-img-container">
                    <i class="bi bi-person-circle"></i>
                </div>
                <h2 class="mt-3 fw-bold secao-intro-h2" id="nomeUsuarioBoasVindas">Bem-vindo, <?= htmlspecialchars($usuarioLogado["nome"], ENT_QUOTES, "UTF-8") ?>!</h2>
            </div>
            <div class="container py-3">
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <a href="#meus-dados" class="btn btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-person-fill me-1"></i> Meus Dados
                    </a>
                    <a href="#minhas-viagens" class="btn btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-geo-alt-fill me-1"></i> Minhas Viagens
                    </a>
                </div>
            </div>
        </section>

        <section id="meus-dados" class="py-4">
            <div class="container">
                <div class="col-lg-8 mx-auto">
                    <div class="card border-0 shadow-sm p-4">
                        <h4 class="fw-bold mb-4" style="color: var(--btn-bg);">
                            <i class="bi bi-person-fill me-2"></i>Meus Dados
                        </h4>
                        <?php if (isset($_GET["sucesso"]) || isset($_GET["sucesso_cancelamento"])): ?>
                            <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                                <i class="bi bi-check-circle-fill"></i> Dados atualizados com sucesso.
                            </div>
                        <?php elseif (isset($_GET["erro"])): ?>
                            <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($_GET["erro"], ENT_QUOTES, "UTF-8") ?>
                            </div>
                        <?php endif; ?>
                        <form class="row g-3 m-0 p-0 bg-transparent" action="../php/dashboard.php" method="POST">
    <div class="col-md-6">
        <label class="form-label">Nome Completo</label>
        <input type="text" class="form-control" id="campoNome" name="nome" value="<?= htmlspecialchars($usuarioLogado["nome"], ENT_QUOTES, "UTF-8") ?>" placeholder="Digite seu nome" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">E-mail</label>
        <input type="email" class="form-control" id="campoEmail" name="email" value="<?= htmlspecialchars($usuarioLogado["email"], ENT_QUOTES, "UTF-8") ?>" placeholder="Digite seu email" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Telefone</label>
        <input type="text" class="form-control" id="campoTelefone" name="telefone" value="<?= htmlspecialchars(($usuarioLogado["telefone"] ?? ""), ENT_QUOTES, "UTF-8") ?>" placeholder="Digite seu telefone" required>
    </div>
    <div class="col-md-6">
    <label class="form-label">Cidade</label>
    <input type="text" class="form-control" id="campoCidade" name="cidade" value="<?= htmlspecialchars(($usuarioLogado["cidade"] ?? ""), ENT_QUOTES, "UTF-8") ?>" placeholder="Digite sua cidade" required>
    </div>
    <div class="col-12 mt-3">
        <div class="alterar-senha-box">
            <div class="mb-3">
                <h6 class="fw-bold mb-1"><i class="bi bi-shield-lock me-2"></i>Alterar senha</h6>
                <p class="text-muted small mb-0">Preencha somente se quiser trocar sua senha atual.</p>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" for="senhaAtual">Senha atual</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="senhaAtual" name="senha_atual" autocomplete="current-password" placeholder="Senha atual">
                        <button class="btn btn-outline-secondary btn-toggle-senha" type="button" data-target="senhaAtual" aria-label="Mostrar senha"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="senhaNova">Nova senha</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="senhaNova" name="senha_nova" minlength="6" autocomplete="new-password" placeholder="Mínimo 6 caracteres">
                        <button class="btn btn-outline-secondary btn-toggle-senha" type="button" data-target="senhaNova" aria-label="Mostrar senha"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="senhaConfirmacao">Confirmar nova senha</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="senhaConfirmacao" name="senha_confirmacao" minlength="6" autocomplete="new-password" placeholder="Repita a nova senha">
                        <button class="btn btn-outline-secondary btn-toggle-senha" type="button" data-target="senhaConfirmacao" aria-label="Mostrar senha"><i class="bi bi-eye"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mt-4">
        <button type="submit" class="btn btn-custom px-5 w-100">Salvar Alterações</button>
    </div>
</form>
                        <hr class="my-4">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h6 class="fw-bold text-danger mb-1">Excluir Conta</h6>
        <p class="text-muted small mb-0">Essa ação é permanente e não pode ser desfeita.</p>
    </div>
    <button type="button" class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#modalExcluirConta">
        <i class="bi bi-trash me-1"></i> Excluir minha conta
    </button>
</div>
                    </div>
                </div>
            </div>
        </section>

        <section id="minhas-viagens" class="py-5">
            <div class="container">
                <div class="col-xl-10 mx-auto">
                    <div class="card border-0 shadow-sm p-3 p-md-4 mb-3 minhas-viagens-card">
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
                            <div>
                                <h4 class="fw-bold mb-1" style="color: var(--text);">
                                    <i class="bi bi-suitcase-lg me-2" style="color: var(--btn-bg);"></i>Minhas Viagens
                                </h4>
                                <p class="text-muted mb-0">Suas reservas e status.</p>
                            </div>
                            <a href="../index.php#reservar" class="btn btn-custom rounded-pill px-4">
                                <i class="bi bi-plus-circle me-1"></i> Nova reserva
                            </a>
                        </div>

                        <?php if (!$reservasIniciais): ?>
                            <div class="reserva-vazia text-center">
                                <div class="reserva-vazia-icone"><i class="bi bi-suitcase-lg"></i></div>
                                <h5 class="fw-bold mb-2">Você ainda não possui viagens</h5>
                                <p class="text-muted mb-4">Faça sua primeira reserva e acompanhe tudo por aqui.</p>
                                <a href="../index.php#reservar" class="btn btn-custom rounded-pill px-4">
                                    <i class="bi bi-calendar-check me-1"></i> Fazer uma reserva
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                            <?php foreach ($reservasIniciais as $indice => $reserva):
                                $cancelavel = strtolower((string)$reserva['status']) !== 'cancelada' && strtotime((string)$reserva['data_viagem']) >= strtotime('today');
                                $statusCancelada = strtolower((string)$reserva['status']) === 'cancelada';
                                $img = basename((string)($reserva['img_destino'] ?? ''));
                                $img = $img ? '../assets/imagens/' . rawurlencode($img) : '../assets/imagens/hero-bg.jpg';
                                $chegada = 'Não informado';
                                if (!empty($reserva['horario_ida']) && !empty($reserva['duracao_voo_minutos'])) {
                                    [$h,$m] = array_map('intval', explode(':', substr((string)$reserva['horario_ida'],0,5)));
                                    $totalMin = $h*60+$m+(int)$reserva['duracao_voo_minutos'];
                                    $chegada = sprintf('%02d:%02d', intdiv($totalMin%1440,60), $totalMin%60) . ($totalMin >= 1440 ? ' (+1 dia)' : '');
                                }
                                $duracao = !empty($reserva['duracao_voo_minutos']) ? intdiv((int)$reserva['duracao_voo_minutos'],60).'h'.str_pad((string)((int)$reserva['duracao_voo_minutos']%60),2,'0',STR_PAD_LEFT) : 'Não informado';
                            ?>
                                <div class="col-12">
                                    <article class="reserva-dashboard-card compacta">
                                        <div class="reserva-dashboard-conteudo">
                                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                                <div>
                                                    <h5 class="reserva-dashboard-destino fw-bold mb-1"><i class="bi bi-geo-alt-fill me-1" style="color: var(--btn-bg);"></i><?= htmlspecialchars($reserva['nome_destino'] ?? 'Destino', ENT_QUOTES, 'UTF-8') ?></h5>
                                                    <div class="reserva-dashboard-local"><?= htmlspecialchars($reserva['cidade_destino'] ?? '', ENT_QUOTES, 'UTF-8') ?><?= !empty($reserva['pais_destino']) ? ', '.htmlspecialchars($reserva['pais_destino'], ENT_QUOTES, 'UTF-8') : '' ?></div>
                                                </div>
                                                <span class="reserva-status reserva-status-<?= $statusCancelada ? 'cancelada' : 'confirmada' ?>"><i class="bi <?= $statusCancelada ? 'bi-x-circle-fill' : 'bi-check-circle-fill' ?>"></i><?= $statusCancelada ? 'Cancelada' : 'Confirmada' ?></span>
                                            </div>
                                            <div class="row g-2 reserva-dashboard-info">
                                                <div class="col-6 col-md-3 reserva-dashboard-info-item"><span class="reserva-dashboard-info-label">Data</span><span class="reserva-dashboard-info-value"><i class="bi bi-calendar3 me-1"></i><?= date('d/m/Y', strtotime($reserva['data_viagem'])) ?></span></div>
                                                <div class="col-6 col-md-3 reserva-dashboard-info-item"><span class="reserva-dashboard-info-label">Horário</span><span class="reserva-dashboard-info-value"><i class="bi bi-clock-fill me-1"></i><?= htmlspecialchars(substr((string)($reserva['horario_ida'] ?? ''),0,5) ?: 'Não informado', ENT_QUOTES, 'UTF-8') ?></span></div>
                                                <div class="col-6 col-md-3 reserva-dashboard-info-item"><span class="reserva-dashboard-info-label">Passageiros</span><span class="reserva-dashboard-info-value"><i class="bi bi-people-fill me-1"></i><?= (int)$reserva['quantidade_passageiros'] ?></span></div>
                                                <div class="col-6 col-md-3 reserva-dashboard-info-item"><span class="reserva-dashboard-info-label">Valor</span><span class="reserva-dashboard-info-value"><i class="bi bi-cash-coin me-1"></i><?= number_format((float)$reserva['valor_total'],2,',','.') ?></span></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mt-3">
                                                <small class="text-muted">Reserva #<?= (int)$reserva['id_reserva'] ?> · <?= htmlspecialchars($reserva['transporte'] ?? '', ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($reserva['classe'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalDetalhes<?= (int)$reserva['id_reserva'] ?>"><i class="bi bi-eye me-1"></i>Detalhes</button>
                                                    <?php if ($cancelavel): ?>
                                                        <form method="post" action="../php/cancelar-reserva.php" onsubmit="return confirm('Tem certeza que deseja cancelar esta reserva?');">
                                                            <input type="hidden" name="id_reserva" value="<?= (int)$reserva['id_reserva'] ?>">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle me-1"></i>Cancelar</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                </div>

                                <div class="modal fade" id="modalDetalhes<?= (int)$reserva['id_reserva'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Reserva #<?= (int)$reserva['id_reserva'] ?> — <?= htmlspecialchars($reserva['nome_destino'] ?? 'Destino', ENT_QUOTES, 'UTF-8') ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button></div>
                                        <div class="modal-body"><div class="row g-4 align-items-start">
                                            <div class="col-md-5"><img class="modal-reserva-destino-img" src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="Destino da viagem" onerror="this.src='../assets/imagens/hero-bg.jpg'"></div>
                                            <div class="col-md-7">
                                                <div class="d-flex justify-content-between align-items-center gap-2 mb-3 flex-wrap"><strong><?= htmlspecialchars($reserva['cidade_destino'] ?? '', ENT_QUOTES, 'UTF-8') ?><?= !empty($reserva['pais_destino']) ? ', '.htmlspecialchars($reserva['pais_destino'], ENT_QUOTES, 'UTF-8') : '' ?></strong><span class="reserva-status reserva-status-<?= $statusCancelada ? 'cancelada' : 'confirmada' ?>"><?= $statusCancelada ? 'Cancelada' : 'Confirmada' ?></span></div>
                                                <div class="reserva-detalhe-linha"><span class="reserva-detalhe-label">Data da viagem</span><span class="reserva-detalhe-valor"><?= date('d/m/Y', strtotime($reserva['data_viagem'])) ?></span></div>
                                                <?php if (!empty($reserva['data_volta'])): ?><div class="reserva-detalhe-linha"><span class="reserva-detalhe-label">Data da volta</span><span class="reserva-detalhe-valor"><?= date('d/m/Y', strtotime($reserva['data_volta'])) ?></span></div><?php endif; ?>
                                                <div class="reserva-detalhe-linha"><span class="reserva-detalhe-label">Passageiros</span><span class="reserva-detalhe-valor"><?= (int)$reserva['quantidade_passageiros'] ?></span></div>
                                                <div class="reserva-detalhe-linha"><span class="reserva-detalhe-label">Transporte</span><span class="reserva-detalhe-valor"><?= htmlspecialchars($reserva['transporte'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></div>
                                                <div class="reserva-detalhe-linha"><span class="reserva-detalhe-label">Classe</span><span class="reserva-detalhe-valor"><?= htmlspecialchars($reserva['classe'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></div>
                                                <div class="reserva-detalhe-linha"><span class="reserva-detalhe-label">Assento</span><span class="reserva-detalhe-valor"><?= htmlspecialchars($reserva['assento'] ?? '', ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($reserva['tipo_assento'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></div>
                                                <div class="reserva-detalhe-linha"><span class="reserva-detalhe-label">Pagamento</span><span class="reserva-detalhe-valor"><?= htmlspecialchars($reserva['pagamento'] ?? '', ENT_QUOTES, 'UTF-8') ?> (simulação)</span></div>
                                                <?php if (($reserva['pagamento'] ?? '') === 'Cartão'): ?><div class="reserva-detalhe-linha"><span class="reserva-detalhe-label">Parcelamento</span><span class="reserva-detalhe-valor"><?= (int)($reserva['parcelas'] ?? 1) ?>x · juros <?= number_format((float)($reserva['taxa_juros_percentual'] ?? 0),1,',','.') ?>%</span></div><?php endif; ?>
                                                <div class="reserva-detalhe-linha"><span class="reserva-detalhe-label">Saída</span><span class="reserva-detalhe-valor"><?= htmlspecialchars(substr((string)($reserva['horario_ida'] ?? ''),0,5) ?: 'Não informado', ENT_QUOTES, 'UTF-8') ?></span></div>
                                                <div class="reserva-detalhe-linha"><span class="reserva-detalhe-label">Chegada estimada</span><span class="reserva-detalhe-valor"><?= $chegada ?></span></div>
                                                <?php if (!empty($reserva['horario_volta'])): ?><div class="reserva-detalhe-linha"><span class="reserva-detalhe-label">Saída da volta</span><span class="reserva-detalhe-valor"><?= htmlspecialchars(substr((string)$reserva['horario_volta'],0,5), ENT_QUOTES, 'UTF-8') ?></span></div><?php endif; ?>
                                                <div class="reserva-detalhe-linha"><span class="reserva-detalhe-label">Duração</span><span class="reserva-detalhe-valor"><?= htmlspecialchars($duracao, ENT_QUOTES, 'UTF-8') ?></span></div>
                                                <div class="reserva-detalhe-linha"><span class="reserva-detalhe-label">Valor total</span><span class="reserva-detalhe-valor" style="color: var(--btn-bg);">R$ <?= number_format((float)$reserva['valor_total'],2,',','.') ?></span></div>
                                            </div>
                                        </div></div>
                                    </div></div>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer-principal">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>Informações Gerais</h5>
                    <ul class="footer-links">
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Política de Privacidade</a></li>
                        <li><a href="#">Termos de Uso</a></li>
                        <li><a href="#">Sobre a TopTurismo</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4 text-center">
                    <h5>Contatos</h5>
                    <div class="footer-contato-item"><i class="bi bi-whatsapp"></i> (99) 99999-9999</div>
                    <div class="footer-contato-item"><i class="bi bi-envelope"></i> contato@topturismo.com</div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Receba Novidades</h5>
                    <div class="newsletter-form">
                        <input type="email" placeholder="Seu melhor e-mail" class="footer-input">
                        <button class="footer-btn">Quero assinar!</button>
                    </div>
                </div>
            </div>
            <hr class="footer-divisor">
            <div class="text-center footer-bottom">
                <p>TopTurismo Agência de Viagens Ltda. </p>
                <p> &copy; 2025 - Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js?v=20260822-modalfix"></script>

    <!-- menu mobile -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="menuMobile" aria-labelledby="menuMobileLabel">
        <div class="offcanvas-header">
            <div class="d-flex align-items-center">
                <img src="../assets/imagens/logo-favicon.ico" width="35" height="35" alt="Logo">
                <span class="ms-2 fw-bold" id="menuMobileLabel">TopTurismo</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
        </div>
        <div class="offcanvas-body">
            <ul class="list-unstyled">
                <li class="mb-2">
                    <a href="../index.php" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item">
                        <i class="bi bi-house fs-4"></i> Início
                    </a>
                </li>
                <li class="mb-2">
                    <a href="../index.php#destinos" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item">
                        <i class="bi bi-geo-alt fs-4"></i> Destinos
                    </a>
                </li>
                <li class="mb-2">
                    <a href="../index.php#reservar" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item">
                        <i class="bi bi-calendar-check fs-4"></i> Fazer Reserva
                    </a>
                </li>
                <li class="mb-2">
                    <a href="../index.php#sobre-nos" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item">
                        <i class="bi bi-info-circle fs-4"></i> Sobre nós
                    </a>
                </li>
                <li class="mb-2">
                    <a href="../index.php#contato" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item">
                        <i class="bi bi-telephone fs-4"></i> Contato
                    </a>
                </li>
            </ul>
            <hr>
            <ul class="list-unstyled" id="userAuthMenuMobileList">
                <li class="mb-2">
                    <a href="../php/logout.php" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item">
                        <i class="bi bi-box-arrow-right fs-4"></i> Sair
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Modal de detalhes da reserva -->
    <div class="modal fade modal-reserva" id="modalDetalhesReserva" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header px-4 py-3">
                    <div>
                        <span class="small fw-bold text-uppercase" style="color: var(--btn-bg); letter-spacing: 1px;">Detalhes da viagem</span>
                        <h5 class="modal-title mt-1" id="detalhesTitulo">Sua reserva</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4" id="detalhesConteudo"></div>
                <div class="modal-footer px-4">
                    <button type="button" class="btn btn-reserva-outline rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmação de cancelamento -->
    <div class="modal fade modal-reserva" id="modalCancelarReserva" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header px-4 py-3">
                    <h5 class="modal-title"><i class="bi bi-exclamation-circle text-danger me-2"></i>Cancelar reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-2">Tem certeza que deseja cancelar esta reserva?</p>
                    <p class="text-muted small mb-0" id="cancelamentoResumo">Esta ação alterará o status da sua reserva para cancelada.</p>
                </div>
                <div class="modal-footer px-4">
                    <button type="button" class="btn btn-reserva-outline rounded-pill px-4" data-bs-dismiss="modal">Voltar</button>
                    <button type="button" class="btn btn-danger rounded-pill px-4" id="btnConfirmarCancelamento">
                        <i class="bi bi-x-circle me-1"></i> Confirmar cancelamento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalExcluirConta" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="../php/excluir-conta.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title text-danger">Excluir Conta</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p>Tem certeza que deseja excluir sua conta? Essa ação é <strong>permanente</strong>.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-custom" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-custom">Sim, excluir minha conta</button>
        </div>
      </form>
    </div>
  </div>
</div>
</body>

</html>
