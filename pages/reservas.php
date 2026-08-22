<?php
session_start();

// Só quem estiver logado pode acessar o formulário de reserva.
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TopTurismo - Reservas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <link rel="shortcut icon" href="../assets/imagens/logo-favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="pagina-reservas">
    <header>
        <nav class="navbar fixed-top navbar-expand-lg custom-bg p-3">

            <div class="container-fluid d-flex align-items-center flex-wrap">

                <div class="d-flex align-items-center">
                    <a href="../index.php"><img src="../assets/imagens/logo-white.png" width="50" height="50"></a>
                    <a href="../index.php" class="text-a ms-2 logo-texto">TopTurismo</a>
                </div>

                <div class="d-flex align-items-center ms-auto gap-3">
                    <div class="dropdown">
                        <a href="#" class="text-white fs-4 user-icon dropdown-toggle" id="userAuthMenu" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" id="userAuthMenuList">
                            <li><a class="dropdown-item" href="login.php"><i
                                        class="bi bi-box-arrow-in-right me-2"></i>Entrar</a></li>
                            <li><a class="dropdown-item" href="cadastro.php"><i
                                        class="bi bi-person-plus me-2"></i>Cadastre-se</a></li>
                        </ul>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="temaMenu"
                            data-bs-toggle="dropdown">
                            <i class="bi bi-circle-half"></i> Tema
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" onclick="setTheme('light')">
                                    <i class="bi bi-sun-fill me-2"></i>Claro
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" onclick="setTheme('dark')">
                                    <i class="bi bi-moon-fill me-2"></i>Escuro
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>

        </nav>
    </header>

    <main class="reserva-main">
        <form id="reservaForm" class=" shadow-lg w-100 p-3 rounded-3" style="max-width: 700px;">
            <div class="p-2 form-info">
                <a href="../index.php" class="btn-voltar">
                    <i class="bi bi-arrow-left-circle"></i> Voltar
                </a>
            </div>
            <div class="p-2">
                <h2>Reserve sua Viagem</h2>
                <p>Preencha os dados abaixo para finalizar sua reserva.</p>
            </div>

            <div class="reserva-imagem-form reserva-destino-preview">
                <img id="reservaImagemForm" src="../assets/imagens/hero-bg.jpg" alt="Destino da viagem">
                <div class="reserva-imagem-overlay"></div>
                <div class="reserva-imagem-legenda" id="reservaImagemLegendaForm">
                    <i class="bi bi-geo-alt-fill"></i> Selecione um destino
                </div>
            </div>

            <div class="reserva-destino-meta">
                <div class="reserva-destino-meta-item">
                    <span><i class="bi bi-geo-alt-fill"></i> Destino</span>
                    <strong id="reservaDestinoForm">Selecione um destino</strong>
                </div>
                <div class="reserva-destino-meta-item">
                    <span><i class="bi bi-tag-fill"></i> Preço base</span>
                    <strong id="reservaPrecoForm">R$ 0,00 <small>/ passageiro</small></strong>
                </div>
                <div class="reserva-destino-meta-item">
                    <span><i class="bi bi-arrow-right-circle-fill"></i> Modalidade</span>
                    <strong id="reservaTipoForm">Ida</strong>
                </div>
            </div>

            <div class="mb-2 p-2">
                <label for="nome" class="form-label">Nome </label>
                <input type="text" id="nome" class="form-control" placeholder="Nome Completo">
            </div>
            <div class="mb-2 p-2">
                <label for="destino" class="form-label">Destino</label>
                <select id="destino" class="form-select" required>
    <option value="">Carregando destinos...</option>
</select>
            </div>
            <div class="mb-2 p-2">
                <label for="passageiros" class="form-label">Quantidade de passageiros</label>
                <input type="number" id="passageiros" class="form-control" min="1" max="9" placeholder="1-9" required>
            </div>

            <div class="mb-2 p-2">
                <label for="tipoViagem" class="form-label">Tipo de viagem</label>

                <select id="tipoViagem" class="form-select" required>
                    <option value="">Selecione o tipo</option>
                    <option value="ida">Somente ida</option>
                    <option value="ida_volta">Ida e volta</option>
                </select>
            </div>

            <div class="mb-2 p-2">
                <div class="row g-3" id="grupoDatas">

                    <div class="col-12" id="campoDataIda">
                        <label for="dataIda" class="form-label">Data de ida</label>
                        <input type="date" id="dataIda" class="form-control" required>
                    </div>

                    <div class="col-12 d-none" id="campoDataVolta">
                        <label for="dataVolta" class="form-label">Data de volta</label>
                        <input type="date" id="dataVolta" class="form-control">
                    </div>

                </div>
            </div>

            <div class="mb-2 p-2">
                <label for="transporte" class="form-label">Tipo de Transporte</label>
                <select id="transporte" class="form-select" required>
                    <option value="">Selecione o tipo</option>
                    <option value="Avião">Avião</option>
                    <option value="Ônibus">Ônibus</option>
                </select>
            </div>
            <div class="mb-2 p-2">
                <label for="classe" class="form-label">Classe</label>

                <select id="classe" class="form-select" required>
                    <option value="">Selecione a classe</option>
                    <option value="Econômica">Econômica</option>
                    <option value="Executiva">Executiva</option>
                    <option value="VIP">VIP</option>
                </select>
            </div>

            <div id="beneficiosDesconto" class="beneficios-desconto mb-3" aria-live="polite"></div>

            <div class="mb-2 p-2">
                <button type="button" class="btn btn-custom w-100" id="btnConfirmar">CONTINUAR</button>
            </div>
            <div id="erro" class="text-center"></div>
        </form>

        <div id="resultado" class="card reserva-confirmacao d-none">

            <a href="./reservas.php" class="btn-voltar reserva-voltar">
                <i class="bi bi-arrow-left-circle"></i> Voltar
            </a>

            <div class="reserva-titulo">
                <span class="reserva-titulo-kicker">RESERVA DE VIAGEM</span>
                <h4>Confirme sua Reserva</h4>
                <p>Confira os dados da sua viagem antes de continuar.</p>
            </div>

            <div class="reserva-imagem-resultado">
                <img id="reservaImagemDestino" src="../assets/imagens/hero-bg.jpg" alt="Destino da viagem">
                <div class="reserva-imagem-overlay"></div>
                <div class="reserva-imagem-legenda" id="reservaImagemLegenda">
                    <i class="bi bi-geo-alt-fill"></i> Seu destino
                </div>
            </div>

            <div class="reserva-destino-meta reserva-destino-meta-resultado">
                <div class="reserva-destino-meta-item">
                    <span><i class="bi bi-geo-alt-fill"></i> Destino</span>
                    <strong id="reservaDestinoHero">Sua viagem</strong>
                </div>
                <div class="reserva-destino-meta-item">
                    <span><i class="bi bi-tag-fill"></i> Preço base</span>
                    <strong id="precoPassageiroMeta">R$ 0,00 <small>/ passageiro</small></strong>
                </div>
                <div class="reserva-destino-meta-item">
                    <span><i class="bi bi-arrow-right-circle-fill"></i> Modalidade</span>
                    <strong id="tipoViagemMeta">Ida</strong>
                </div>
            </div>

            <div id="resumo" class="resumo-reserva"></div>

            <div class="resumo-precos">
                <div class="preco-linha">
                    <span>Preço por passageiro</span>
                    <strong id="precoPassageiro">R$ 0,00</strong>
                </div>
                <div class="preco-linha">
                    <span>Subtotal da reserva</span>
                    <strong id="subtotal">R$ 0,00</strong>
                </div>
                <div class="preco-linha desconto-linha" id="linhaDesconto">
                    <span id="textoDesconto">Desconto</span>
                    <strong id="desconto">- R$ 0,00</strong>
                </div>
                <div class="preco-divisor"></div>
                <div class="preco-total">
                    <span>Total</span>
                    <strong id="total">R$ 0,00</strong>
                </div>
            </div>

            <button
                type="button"
                class="btn btn-custom w-100 reserva-confirmar-btn"
                id="novaReserva">
                <i class="bi bi-check-circle me-2"></i>Confirmar reserva
            </button>

        </div>
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
                    <div class="footer-contact-item">
                        <i class="bi bi-whatsapp"></i> <span>(99) 99999-9999</span>
                    </div>
                    <div class="footer-contato-item">
                        <i class="bi bi-envelope"></i> <span>contato@topturismo.com</span>
                    </div>
                    <div class="d-flex justify-content-center gap-3 mt-3 social-icons-pro">
                        <a href="#"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-facebook"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
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

            <div class="row">
                <div class="col-12 text-center footer-bottom">
                    <p>TopTurismo Agência de Viagens Ltda. </p>
                    <p class="mb-0">&copy; 2025 TopTurismo - Todos os direitos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/validacoes.js"></script>
    <script src="../assets/js/script.js?v=20260822-modalfix"></script>
    <script>
document.addEventListener("DOMContentLoaded", async () => {

    const selectDestino = document.getElementById("destino");

    try {

        // Busca os destinos no banco através da API
        const resposta = await fetch("../php/destinos.php");

        if (!resposta.ok) {
            throw new Error("Erro ao buscar destinos.");
        }

        const destinos = await resposta.json();

        // Limpa o "Carregando destinos..."
        selectDestino.innerHTML =
            '<option value="">Selecione o destino</option>';

        // Recupera o destino enviado pela página inicial
        const params = new URLSearchParams(window.location.search);
        const destinoSelecionado = params.get("destino");

        // Cria as opções vindas do MySQL
        destinos.forEach(destino => {

            const option = document.createElement("option");

            // O value será o ID do destino no banco
            option.value = destino.id_destino;

            // Texto mostrado para o usuário
            option.textContent =
                `${destino.nome_destino} - ${destino.pais_destino}`;

            // Guarda apenas o preço no option para o cálculo da reserva.
            option.dataset.preco = destino.preco_destino;

            // Se veio um destino pela URL, tenta selecioná-lo
            if (destinoSelecionado) {
                const normalizarDestino = (valor) => String(valor || "")
                    .normalize("NFD")
                    .replace(/[\u0300-\u036f]/g, "")
                    .toLowerCase()
                    .trim();

                const alvo = normalizarDestino(destinoSelecionado);
                const nomeBanco = normalizarDestino(destino.nome_destino);
                const cidadeBanco = normalizarDestino(destino.cidade_destino);
                const idBanco = String(destino.id_destino);

                if (alvo === nomeBanco || alvo === cidadeBanco || alvo === idBanco) {
                    option.selected = true;
                }
            }

            selectDestino.appendChild(option);
        });

        // Atualiza a localização do formulário depois que os destinos do banco foram carregados.
        selectDestino.dispatchEvent(new Event("change"));

    } catch (erro) {

        console.error(erro);

        selectDestino.innerHTML =
            '<option value="">Erro ao carregar destinos</option>';
    }

});

document.addEventListener("DOMContentLoaded", () => {

    const transporte = document.getElementById("transporte");
    const classe = document.getElementById("classe");

    transporte.addEventListener("change", () => {

        const valor = transporte.value;

        // Limpa a seleção atual
        classe.value = "";

        // Percorre as opções da classe
        Array.from(classe.options).forEach(opcao => {

            if (opcao.value === "") {
                opcao.disabled = false;
                return;
            }

            // Ônibus só permite Econômica
            if (valor === "Ônibus") {
                opcao.disabled = opcao.value !== "Econômica";
            } else {
                // Avião permite todas
                opcao.disabled = false;
            }

        });

    });

});

</script>
<script>
document.addEventListener("DOMContentLoaded", () => {

    const tipoViagem = document.getElementById("tipoViagem");

    const campoDataIda = document.getElementById("campoDataIda");
    const campoDataVolta = document.getElementById("campoDataVolta");

    const dataIda = document.getElementById("dataIda");
    const dataVolta = document.getElementById("dataVolta");


    tipoViagem.addEventListener("change", () => {

        if (tipoViagem.value === "ida_volta") {

            // Mostra a data de volta
            campoDataVolta.classList.remove("d-none");

            // Divide igualmente em duas colunas
            campoDataIda.classList.remove("col-12");
            campoDataIda.classList.add("col-md-6");

            campoDataVolta.classList.remove("col-12");
            campoDataVolta.classList.add("col-md-6");

            // Torna obrigatória
            dataVolta.required = true;

        } else {

            // Esconde a data de volta
            campoDataVolta.classList.add("d-none");

            // Ida ocupa toda a largura
            campoDataIda.classList.remove("col-md-6");
            campoDataIda.classList.add("col-12");

            // Volta deixa de ser obrigatória
            dataVolta.required = false;
            dataVolta.value = "";
        }

    });


    // Impede datas anteriores a hoje
    const hoje = new Date().toISOString().split("T")[0];

    dataIda.min = hoje;
    dataVolta.min = hoje;


    // A volta não pode ser anterior à ida
    dataIda.addEventListener("change", () => {

        dataVolta.min = dataIda.value;

        if (
            dataVolta.value &&
            dataVolta.value < dataIda.value
        ) {
            dataVolta.value = "";
        }

    });

});
</script>

</body>

</html>