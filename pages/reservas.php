<?php
session_start();

// *garante que somente usuários logados possam abrir o formulário de reserva*
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . "/../php/destinos-data.php";
require_once __DIR__ . "/../php/programacao-dados.php";
$destinos = buscarDestinos();
$hoje = date("Y-m-d");
$limiteData = date("Y-m-d", strtotime("+9 months"));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TopTurismo - Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="shortcut icon" href="../assets/imagens/logo-favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="pagina-reservas" data-logado="1">
<header>
    <nav class="navbar fixed-top navbar-expand-lg custom-bg p-3">
        <div class="container-fluid d-flex align-items-center flex-wrap">
            <div class="d-flex align-items-center">
                <a href="../index.php"><img src="../assets/imagens/logo-white.png" width="50" height="50" alt="Logo TopTurismo"></a>
                <a href="../index.php" class="text-a ms-2 logo-texto">TopTurismo</a>
            </div>
            <div class="d-flex align-items-center ms-auto gap-3">
                <div class="dropdown">
                    <a href="#" class="text-white fs-4 user-icon dropdown-toggle" id="userAuthMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" id="userAuthMenuList">
                        <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-person-fill me-2"></i>Meu Perfil</a></li>
                        <?php if (($_SESSION["usuario_tipo"] ?? "cliente") === "admin"): ?>
                        <li><a class="dropdown-item" href="../admin/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Painel Admin</a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="../php/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="temaMenu" data-bs-toggle="dropdown"><i class="bi bi-circle-half"></i> Tema</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" onclick="setTheme('light')"><i class="bi bi-sun-fill me-2"></i>Claro</a></li>
                        <li><a class="dropdown-item" onclick="setTheme('dark')"><i class="bi bi-moon-fill me-2"></i>Escuro</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>

<main class="reserva-main">
    <!-- *o JavaScript cuida da interação e o PHP faz a validação definitiva* -->
    <form id="reservaForm" class="shadow-lg w-100 p-3 rounded-3" style="max-width: 760px;" data-hoje="<?= $hoje ?>" data-limite="<?= $limiteData ?>">
        <div class="p-2 form-info"><a href="../index.php" class="btn-voltar"><i class="bi bi-arrow-left-circle"></i> Voltar</a></div>
        <div class="p-2">
            <h2>Reserve sua Viagem</h2>
            <p>Escolha sua viagem e seu assento. O horário é definido pela programação disponível da TopTurismo.</p>
        </div>

        <div class="reserva-imagem-form reserva-destino-preview">
            <img id="reservaImagemForm" src="../assets/imagens/hero-bg.jpg" alt="Destino da viagem">
            <div class="reserva-imagem-overlay"></div>
            <div class="reserva-imagem-legenda" id="reservaImagemLegendaForm"><i class="bi bi-geo-alt-fill"></i> Selecione um destino</div>
        </div>

        <div class="reserva-destino-meta">
            <div class="reserva-destino-meta-item"><span><i class="bi bi-geo-alt-fill"></i> Destino</span><strong id="reservaDestinoForm">Selecione um destino</strong></div>
            <div class="reserva-destino-meta-item"><span><i class="bi bi-tag-fill"></i> Preço base</span><strong id="reservaPrecoForm">R$ 0,00 <small>/ passageiro</small></strong></div>
            <div class="reserva-destino-meta-item"><span><i class="bi bi-arrow-right-circle-fill"></i> Modalidade</span><strong id="reservaTipoForm">Ida</strong></div>
        </div>

        <div class="mb-2 p-2">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" id="nome" class="form-control" placeholder="Nome completo" autocomplete="name">
            <div class="campo-erro" id="erro-nome"></div>
        </div>
        <div class="mb-2 p-2">
            <label for="destino" class="form-label">Destino</label>
            <select id="destino" class="form-select" required>
                <option value="">Selecione o destino</option>
<?php foreach ($destinos as $destino):
    // *o PHP define o horário de cada destino, inclusive os novos destinos*
    $aviao = programacaoPorId((int)$destino['id_destino'], 'Avião');
    $onibus = programacaoPorId((int)$destino['id_destino'], 'Ônibus');
?>
                <option value="<?= (int)$destino['id_destino'] ?>"
                    data-preco="<?= htmlspecialchars((string)$destino['preco_destino'], ENT_QUOTES, 'UTF-8') ?>"
                    data-nome="<?= htmlspecialchars($destino['nome_destino'], ENT_QUOTES, 'UTF-8') ?>"
                    data-imagem="../<?= htmlspecialchars(ltrim($destino['img_destino'], './'), ENT_QUOTES, 'UTF-8') ?>"
                    data-saida-aviao="<?= htmlspecialchars($aviao['saida'], ENT_QUOTES, 'UTF-8') ?>"
                    data-volta-aviao="<?= htmlspecialchars($aviao['volta'], ENT_QUOTES, 'UTF-8') ?>"
                    data-duracao-aviao="<?= (int)$aviao['duracao'] ?>"
                    data-saida-onibus="<?= htmlspecialchars($onibus['saida'], ENT_QUOTES, 'UTF-8') ?>"
                    data-volta-onibus="<?= htmlspecialchars($onibus['volta'], ENT_QUOTES, 'UTF-8') ?>"
                    data-duracao-onibus="<?= (int)$onibus['duracao'] ?>">
                    <?= htmlspecialchars($destino['nome_destino'] . ' - ' . $destino['pais_destino'], ENT_QUOTES, 'UTF-8') ?>
                </option>
<?php endforeach; ?>
            </select>
            <div class="campo-erro" id="erro-destino"></div>
        </div>
        <div class="mb-2 p-2">
            <label for="passageiros" class="form-label">Quantidade de passageiros</label>
            <input type="number" id="passageiros" class="form-control" min="1" max="9" placeholder="1 a 9" required>
            <div class="campo-erro" id="erro-passageiros"></div>
        </div>
        <div class="mb-2 p-2">
            <label for="tipoViagem" class="form-label">Tipo de viagem</label>
            <select id="tipoViagem" class="form-select" required>
                <option value="">Selecione o tipo</option>
                <option value="ida">Somente ida</option>
                <option value="ida_volta">Ida e volta</option>
            </select>
            <div class="campo-erro" id="erro-tipoViagem"></div>
        </div>
        <div class="mb-2 p-2">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label for="dataIda" class="form-label">Data de ida</label>
                    <input type="date" id="dataIda" class="form-control" required>
                    <small class="form-text text-muted" id="limiteDataIda">Disponibilidade: hoje até 9 meses.</small>
                    <div class="campo-erro" id="erro-dataIda"></div>
                </div>
                <div class="col-12 col-md-6 d-none" id="campoDataVolta">
                    <label for="dataVolta" class="form-label">Data de volta</label>
                    <input type="date" id="dataVolta" class="form-control">
                    <small class="form-text text-muted" id="limiteDataVolta">Disponibilidade: hoje até 9 meses.</small>
                    <div class="campo-erro" id="erro-dataVolta"></div>
                </div>
            </div>
        </div>
        <div class="mb-2 p-2">
            <label for="transporte" class="form-label">Tipo de transporte</label>
            <select id="transporte" class="form-select" required>
                <option value="">Selecione o tipo</option>
                <option value="Avião">Avião</option>
                <option value="Ônibus">Ônibus</option>
            </select>
            <div class="campo-erro" id="erro-transporte"></div>
        </div>
        <div class="mb-2 p-2">
            <label for="classe" class="form-label">Classe / tipo de assento</label>
            <select id="classe" class="form-select" required>
                <option value="">Selecione a classe</option>
                <option value="Econômica">Econômica</option>
                <option value="Executiva">Executiva</option>
                <option value="VIP">VIP</option>
            </select>
            <small class="form-text text-muted">A classe escolhida define a área de assentos disponível no layout.</small>
            <div class="campo-erro" id="erro-classe"></div>
        </div>

        <div id="programacaoVoo" class="programacao-viagem mb-3 d-none" aria-live="polite">
            <div class="programacao-viagem-cabecalho"><i class="bi bi-calendar2-check-fill"></i> Horário disponibilizado pela reserva</div>
            <div class="programacao-viagem-grid">
                <div><span>Saída</span><strong id="programacaoSaida">--:--</strong></div>
                <div><span>Chegada</span><strong id="programacaoChegada">--:--</strong></div>
                <div><span>Duração</span><strong id="programacaoDuracao">--</strong></div>
                <div><span>Tempo até a saída</span><strong id="programacaoEspera">--</strong></div>
            </div>
            <div id="programacaoVolta" class="programacao-volta d-none">
                <span>Volta</span><strong id="programacaoSaidaVolta">--:--</strong><b>→</b><strong id="programacaoChegadaVolta">--:--</strong>
            </div>
            <p class="programacao-observacao mb-0"><i class="bi bi-info-circle me-1"></i>O passageiro não escolhe o horário; ele é atribuído automaticamente conforme a programação disponível.</p>
        </div>

        <div class="mb-2 p-2">
            <div class="d-flex justify-content-between align-items-end gap-3 flex-wrap mb-2">
                <div>
                    <label class="form-label mb-1">Escolha do assento</label>
                    <p class="text-muted small mb-0" id="assentoInstrucao">Selecione a classe para visualizar o layout.</p>
                </div>
                <span class="assentos-contador" id="assentosContador">0 selecionado(s)</span>
            </div>
            <div id="mapaAssentos" class="mapa-assentos" aria-label="Layout de assentos"></div>
            <div class="legenda-assentos">
                <span><i class="assento-legenda livre"></i> Livre</span>
                <span><i class="assento-legenda selecionado"></i> Selecionado</span>
                <span><i class="assento-legenda ocupado"></i> Indisponível</span>
            </div>
            <div class="campo-erro" id="erro-assento"></div>
        </div>

        <div id="beneficiosDesconto" class="beneficios-desconto mb-3" aria-live="polite"></div>
        <div class="mb-2 p-2"><button type="button" class="btn btn-custom w-100" id="btnConfirmar">REVISAR RESERVA</button></div>
        <div id="erro" class="text-center"></div>
    </form>

    <section id="resultado" class="card reserva-confirmacao d-none">
        <a href="./reservas.php" class="btn-voltar reserva-voltar"><i class="bi bi-arrow-left-circle"></i> Voltar</a>
        <div class="reserva-titulo">
            <span class="reserva-titulo-kicker">RESERVA DE VIAGEM</span>
            <h4>Confirme sua Reserva</h4>
            <p>Confira os dados da viagem, horário e assentos antes de ir para o pagamento.</p>
        </div>
        <div class="reserva-imagem-resultado"><img id="reservaImagemDestino" src="../assets/imagens/hero-bg.jpg" alt="Destino da viagem"><div class="reserva-imagem-overlay"></div><div class="reserva-imagem-legenda" id="reservaImagemLegenda"><i class="bi bi-geo-alt-fill"></i> Seu destino</div></div>
        <div class="reserva-destino-meta reserva-destino-meta-resultado">
            <div class="reserva-destino-meta-item"><span><i class="bi bi-geo-alt-fill"></i> Destino</span><strong id="reservaDestinoHero">Sua viagem</strong></div>
            <div class="reserva-destino-meta-item"><span><i class="bi bi-tag-fill"></i> Preço base</span><strong id="precoPassageiroMeta">R$ 0,00 <small>/ passageiro</small></strong></div>
            <div class="reserva-destino-meta-item"><span><i class="bi bi-arrow-right-circle-fill"></i> Modalidade</span><strong id="tipoViagemMeta">Ida</strong></div>
        </div>
        <div id="resumo" class="resumo-reserva"></div>
        <div class="resumo-precos">
            <div class="preco-linha"><span>Preço por passageiro</span><strong id="precoPassageiro">R$ 0,00</strong></div>
            <div class="preco-linha"><span>Subtotal da reserva</span><strong id="subtotal">R$ 0,00</strong></div>
            <div class="preco-linha desconto-linha"><span id="textoDesconto">Desconto</span><strong id="desconto">- R$ 0,00</strong></div>
            <div class="preco-divisor"></div>
            <div class="preco-total"><span>Total antes do pagamento</span><strong id="total">R$ 0,00</strong></div>
        </div>
        <button type="button" class="btn btn-custom w-100 reserva-confirmar-btn" id="irPagamento"><i class="bi bi-credit-card me-2"></i>Ir para pagamento</button>
        <button type="button" class="btn btn-link w-100 mt-2" id="voltarFormulario">Voltar e editar</button>
    </section>

    <section id="pagamentoCard" class="card reserva-confirmacao pagamento-card d-none">
        <div class="reserva-titulo">
            <span class="reserva-titulo-kicker">ETAPA 2</span>
            <h4>Pagamento</h4>
            <p>Simulação de pagamento. Nenhum valor real será cobrado.</p>
        </div>
        <div class="pagamento-resumo-total"><span>Total da reserva</span><strong id="pagamentoTotal">R$ 0,00</strong></div>
        <div class="pagamento-opcoes" role="radiogroup" aria-label="Forma de pagamento">
            <button type="button" class="pagamento-opcao" data-pagamento="Pix"><i class="bi bi-qr-code"></i><span><b>Pix</b><small>À vista com desconto</small></span></button>
            <button type="button" class="pagamento-opcao" data-pagamento="Cartão"><i class="bi bi-credit-card-2-front"></i><span><b>Cartão</b><small>Parcelado com juros</small></span></button>
        </div>
        <div id="pixPagamento" class="pagamento-detalhes d-none">
            <div class="pix-simulacao"><i class="bi bi-qr-code-scan"></i><div><strong>Pix à vista</strong><p>Desconto de 5% aplicado automaticamente no pagamento.</p></div></div>
        </div>
        <div id="cartaoPagamento" class="pagamento-detalhes d-none">
            <div class="row g-3">
                <div class="col-12"><label for="nomeCartao" class="form-label">Nome no cartão</label><input id="nomeCartao" class="form-control" autocomplete="cc-name" placeholder="Nome como está no cartão"><div class="campo-erro" id="erro-nomeCartao"></div></div>
                <div class="col-12"><label for="numeroCartao" class="form-label">Número do cartão</label><input id="numeroCartao" class="form-control" inputmode="numeric" maxlength="19" autocomplete="cc-number" placeholder="0000 0000 0000 0000"><div class="campo-erro" id="erro-numeroCartao"></div></div>
                <div class="col-6"><label for="validadeCartao" class="form-label">Validade</label><input id="validadeCartao" class="form-control" inputmode="numeric" maxlength="5" autocomplete="cc-exp" placeholder="MM/AA"><div class="campo-erro" id="erro-validadeCartao"></div></div>
                <div class="col-6"><label for="cvvCartao" class="form-label">CVV</label><input id="cvvCartao" class="form-control" inputmode="numeric" maxlength="4" autocomplete="cc-csc" placeholder="123"><div class="campo-erro" id="erro-cvvCartao"></div></div>
                <div class="col-12"><label for="parcelas" class="form-label">Parcelamento</label><select id="parcelas" class="form-select"><option value="1">1x sem juros</option><option value="2">2x</option><option value="3">3x</option><option value="4">4x</option><option value="5">5x</option><option value="6">6x</option><option value="8">8x</option><option value="10">10x</option><option value="12">12x</option></select></div>
            </div>
            <div class="juros-box"><span>Taxa de juros</span><strong id="taxaJuros">0%</strong><span>Total no cartão</span><strong id="totalCartao">R$ 0,00</strong></div>
        </div>
        <div id="erroPagamento" class="text-center"></div>
        <button type="button" class="btn btn-custom w-100 reserva-confirmar-btn" id="finalizarPagamento"><i class="bi bi-lock-fill me-2"></i>Simular pagamento e confirmar reserva</button>
        <button type="button" class="btn btn-link w-100 mt-2" id="voltarConfirmacao">Voltar para confirmação</button>
    </section>

    <section id="sucessoReserva" class="card reserva-confirmacao sucesso-reserva d-none">
        <div class="sucesso-icone"><i class="bi bi-check-lg"></i></div>
        <span class="reserva-titulo-kicker">PAGAMENTO APROVADO — SIMULAÇÃO</span>
        <h3>Reserva confirmada com sucesso!</h3>
        <p id="sucessoMensagem">Sua viagem foi registrada e já está disponível em Minhas Viagens.</p>
        <div class="sucesso-codigo">Número da reserva: <strong id="numeroReservaSucesso">#---</strong></div>
        <div class="sucesso-acoes"><a href="dashboard.php#minhas-viagens" class="btn btn-custom"><i class="bi bi-suitcase-lg me-1"></i>Ver minhas viagens</a><a href="../index.php" class="btn btn-outline-secondary rounded-pill">Voltar ao início</a></div>
    </section>
</main>

<footer class="footer-principal">
    <div class="container"><div class="row">
        <div class="col-md-4 mb-4"><h5>Informações Gerais</h5><ul class="footer-links"><li><a href="#">FAQ</a></li><li><a href="#">Política de Privacidade</a></li><li><a href="#">Termos de Uso</a></li><li><a href="#">Sobre a TopTurismo</a></li></ul></div>
        <div class="col-md-4 mb-4 text-center"><h5>Contatos</h5><div class="footer-contact-item"><i class="bi bi-whatsapp"></i> <span>(99) 99999-9999</span></div><div class="footer-contato-item"><i class="bi bi-envelope"></i> <span>contato@topturismo.com</span></div></div>
        <div class="col-md-4 mb-4"><h5>Receba Novidades</h5><div class="newsletter-form"><input type="email" placeholder="Seu melhor e-mail" class="footer-input"><button class="footer-btn">Quero assinar!</button></div></div>
    </div><hr class="footer-divisor"><div class="row"><div class="col-12 text-center footer-bottom"><p>TopTurismo Agência de Viagens Ltda.</p><p class="mb-0">&copy; 2025 TopTurismo - Todos os direitos reservados.</p></div></div></div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/validacoes.js"></script>
<script src="../assets/js/script.js?v=20260829-final"></script>
<script src="../assets/js/reservas.js?v=20260830-clean"></script>
</body>
</html>