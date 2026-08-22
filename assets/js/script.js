/**
 * Script principal do TopTurismo.
 *
 * Responsabilidades:
 * - controlar tema claro/escuro;
 * - sincronizar o estado de autenticação com a interface;
 * - abrir reservas e o modal de detalhes;
 * - filtrar/ordenar os destinos;
 * - controlar a interação do formulário de reservas.
 *
 * As regras de segurança e cálculo financeiro continuam no PHP.
 * O JavaScript apenas melhora a experiência e prepara os dados para a API.
 */

// ============================================================
// TEMA CLARO / ESCURO
// ============================================================
const body = document.body;
const dropdownButton = document.getElementById("temaMenu");

document.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("theme") || "light";
  setTheme(savedTheme);
});

// Detecta se estamos dentro de /pages/ ou na raiz do site para montar os links corretamente.
const dentroDePages = window.location.pathname.includes("/pages/");
const caminhoPhp = dentroDePages ? "../php/" : "php/";

// Faz uma única checagem de login e compartilha o resultado entre o menu
// de usuário e os botões de reserva. Isso evita várias requisições iguais.
const statusLogin = fetch(caminhoPhp + "usuario-logado.php")
  .then((resposta) => resposta.ok)
  .catch(() => false);

// ============================================================
// AUTENTICAÇÃO E MENU DO USUÁRIO
// ============================================================
// Atualiza o menu conforme a sessão retornada pelo backend.
document.addEventListener("DOMContentLoaded", () => {
  const menuDesktop = document.getElementById("userAuthMenuList");
  const menuMobile = document.getElementById("userAuthMenuMobileList");

  if (!menuDesktop && !menuMobile) return;

  const linkDashboard = dentroDePages ? "dashboard.php" : "./pages/dashboard.php";
  const linkLogout = caminhoPhp + "logout.php";

  statusLogin.then((logado) => {
    if (!logado) return;

    const itensLogado = `
      <li><a class="dropdown-item" href="${linkDashboard}"><i class="bi bi-person-fill me-2"></i>Meu Perfil</a></li>
      <li><a class="dropdown-item" href="${linkLogout}"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
    `;
    if (menuDesktop) menuDesktop.innerHTML = itensLogado;

    const itensLogadoMobile = `
      <li class="mb-2"><a href="${linkDashboard}" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item"><i class="bi bi-person-circle fs-4"></i> Meu Painel</a></li>
      <li><a href="${linkLogout}" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item"><i class="bi bi-box-arrow-right fs-4"></i> Sair</a></li>
    `;
    if (menuMobile) menuMobile.innerHTML = itensLogadoMobile;
  });
});

// ============================================================
// ACESSO À RESERVA
// ============================================================
// Usuário logado: segue para o formulário com o destino selecionado.
// Usuário não logado: recebe o modal de login/cadastro.
document.addEventListener("DOMContentLoaded", () => {
  const botoesReservar = document.querySelectorAll(".btn-reservar");
  if (!botoesReservar.length) return;

  const modalElement = document.getElementById("loginModal");
  const modalLogin = modalElement ? new bootstrap.Modal(modalElement) : null;

  botoesReservar.forEach((botao) => {
    botao.addEventListener("click", () => {
      const destino = botao.dataset.destino || "";

      statusLogin.then((logado) => {
        if (logado) {
          const query = destino ? "?destino=" + encodeURIComponent(destino) : "";
          window.location.href = "pages/reservas.php" + query;
        } else if (modalLogin) {
          modalLogin.show();
        }
      });
    });
  });
});

// ============================================================
// MODAL DE DETALHES DOS DESTINOS
// ============================================================
// A delegação de eventos permite que o botão continue funcionando mesmo
// quando os cards forem filtrados ou reordenados.
// Usa delegação de eventos para continuar funcionando mesmo depois que
// os cards forem filtrados/ordenados pelo usuário.
document.addEventListener("DOMContentLoaded", () => {
  const container = document.querySelector("#destinos .row");
  const modalElement = document.getElementById("destinoDetalhesModal");
  const modal = modalElement ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
  const btnReservarModal = document.getElementById("btnReservarDestinoModal");
  const carouselElement = document.getElementById("destinoModalCarousel");
  const carousel = carouselElement
    ? bootstrap.Carousel.getOrCreateInstance(carouselElement, { interval: false })
    : null;
  let destinoModalAtual = "";

  if (!container || !modal) return;

  function abrirDetalhes(botao) {
    const card = botao.closest(".card-destino-custom");
    if (!card) return;

    const titulo = card.querySelector(".nome-destino-overlay")?.textContent.trim() || "Destino";
    const imagem = card.querySelector(".card-img-top")?.getAttribute("src") || "./assets/imagens/hero-bg.jpg";
    const descricao = card.querySelector(".descricao-destino")?.textContent.replace(/\s+/g, " ").trim()
      || "Conheça este destino com a TopTurismo.";
    const precoTexto = card.querySelector(".preco-badge")?.textContent.replace(/\s+/g, " ").trim() || "R$ 0";
    const avaliacao = card.dataset.avaliacao || "4.5";
    const nomeMaps = titulo.replace(/\s+-\s+[A-Z]{2}$/i, "");
    const fotos = (card.dataset.fotos || "")
      .split("|")
      .map(v => v.trim())
      .filter(Boolean);

    destinoModalAtual = botao.dataset.destino || nomeMaps;

    // O modal pode existir em páginas diferentes da home. Por isso, todos os
    // elementos são tratados de forma defensiva para nunca interromper o JS.
    const elTitulo = document.getElementById("destinoDetalhesTitulo");
    const elDescricao = document.getElementById("destinoModalDescricao");
    const elPreco = document.getElementById("destinoModalPreco");
    const elLocal = document.getElementById("destinoModalLocal");
    const elAvaliacao = document.getElementById("destinoModalAvaliacao");
    const elMaps = document.getElementById("destinoModalMaps");

    if (elTitulo) elTitulo.textContent = titulo;
    if (elDescricao) elDescricao.textContent = descricao;
    if (elPreco) elPreco.textContent = precoTexto.split("/")[0].trim();
    if (elLocal) elLocal.textContent = titulo;
    if (elAvaliacao) elAvaliacao.textContent = avaliacao;
    if (elMaps) {
      elMaps.href = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(nomeMaps)}`;
    }

    // O carrossel sempre mantém exatamente 3 posições.
    // Se o destino tiver menos fotos, as posições restantes viram placeholders.
    // Não dependemos de um <img id="destinoModalImagem"> fixo: isso evita o
    // TypeError que ocorria quando o primeiro slide era recriado.
    const slides = carouselElement ? carouselElement.querySelectorAll(".carousel-item") : [];
    const imagens = [imagem, ...fotos].filter(Boolean).slice(0, 3);

    slides.forEach((slide, index) => {
      const foto = imagens[index];
      slide.classList.toggle("active", index === 0);

      if (foto) {
        slide.innerHTML = "";
        const img = document.createElement("img");
        img.src = foto;
        img.alt = `${titulo} - foto ${index + 1}`;
        img.className = "destino-modal-carousel-img";
        slide.appendChild(img);
      } else {
        slide.innerHTML = `<div class="destino-modal-foto-placeholder"><i class="bi bi-image"></i><span>Foto adicional</span></div>`;
      }
    });

    // Mantém os três indicadores sincronizados com as três posições.
    carouselElement?.querySelectorAll(".carousel-indicators [data-bs-slide-to]").forEach((indicator, index) => {
      indicator.classList.toggle("active", index === 0);
      indicator.setAttribute("aria-current", index === 0 ? "true" : "false");
    });

    carousel?.to(0);
    modal.show();
  }

  container.addEventListener("click", (event) => {
    const botao = event.target.closest(".btn-ver-mais");
    if (!botao || !container.contains(botao)) return;

    event.preventDefault();
    event.stopPropagation();
    abrirDetalhes(botao);
  });

  btnReservarModal?.addEventListener("click", () => {
    const destino = destinoModalAtual;
    statusLogin.then((logado) => {
      if (logado) {
        window.location.href = `pages/reservas.php?destino=${encodeURIComponent(destino)}`;
      } else {
        modal.hide();
        const loginModalElement = document.getElementById("loginModal");
        if (loginModalElement) {
          bootstrap.Modal.getOrCreateInstance(loginModalElement).show();
        }
      }
    });
  });
});

function setTheme(theme) {
  if (theme === "dark") {
    body.classList.add("dark-mode");
    dropdownButton.innerHTML = '<i class="bi bi-moon-fill"></i>';
    localStorage.setItem("theme", "dark");
  } else {
    body.classList.remove("dark-mode");
    dropdownButton.classList.remove("btn-dark");
    dropdownButton.classList.add("btn-light");
    dropdownButton.innerHTML = '<i class="bi bi-sun-fill"></i>';
    localStorage.setItem("theme", "light");
  }
}

// ============================================================
// BUSCA, FILTRO, ORDENAÇÃO E AVALIAÇÕES
// ============================================================
document.addEventListener("DOMContentLoaded", () => {
    const inputBusca = document.querySelector(".busca-caixa input");
    const filtro = document.getElementById("filtroDestinos");
    const container = document.querySelector("#destinos .row");
    const colunas = container ? Array.from(container.children) : [];

    // Remove acentos para que "João" também seja encontrado como "Joao".
    function normalizarBusca(texto) {
        return String(texto || "")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .trim();
    }

    // Converte a nota numérica de cada card em estrelas visuais.
    function renderizarEstrelas() {
        document.querySelectorAll("#destinos .card-destino-custom").forEach(card => {
            const rating = Number(card.dataset.avaliacao || 0);
            const estrelas = card.querySelector(".avaliacao-estrelas");
            if (!estrelas) return;

            estrelas.innerHTML = "";
            for (let i = 1; i <= 5; i++) {
                const icon = document.createElement("i");
                if (rating >= i) {
                    icon.className = "bi bi-star-fill";
                } else if (rating >= i - 0.5) {
                    icon.className = "bi bi-star-half";
                } else {
                    icon.className = "bi bi-star";
                }
                estrelas.appendChild(icon);
            }
            estrelas.setAttribute("aria-label", `Avaliação ${rating.toFixed(1).replace(".", ",")} de 5`);
        });
    }

    // Filtra por texto/região e ordena por popularidade ou avaliação.
    function aplicarFiltros() {
        const termo = normalizarBusca(inputBusca?.value);
        const criterio = filtro?.value || "todos";

        const dados = colunas.map((coluna, indice) => {
            const card = coluna.querySelector(".card-destino-custom");
            const titulo = normalizarBusca(card?.querySelector(".nome-destino-overlay")?.textContent);
            const descricao = normalizarBusca(card?.querySelector(".descricao-destino")?.textContent);
            const regiao = normalizarBusca(card?.dataset.regiao);
            const popularidade = Number(card?.dataset.popularidade || 0);
            const avaliacao = Number(card?.dataset.avaliacao || 0);

            const encontrado = !termo || titulo.includes(termo) || descricao.includes(termo);
            let passaFiltro = true;

            if (["norte", "nordeste", "centro-oeste", "sudeste", "sul"].includes(criterio)) {
                passaFiltro = regiao === criterio;
            }

            return { coluna, indice, card, visivel: encontrado && passaFiltro, popularidade, avaliacao };
        });

        const visiveis = dados.filter(item => item.visivel);
        const ordenados = [...visiveis];

        if (criterio === "populares") {
            ordenados.sort((a, b) => b.popularidade - a.popularidade || b.avaliacao - a.avaliacao);
        } else if (criterio === "avaliados") {
            ordenados.sort((a, b) => b.avaliacao - a.avaliacao || b.popularidade - a.popularidade);
        }

        // Usa a ordem flexível do Bootstrap em vez de mover nós do DOM.
        // Assim os listeners dos cards continuam preservados.
        dados.forEach(item => {
            item.coluna.style.display = item.visivel ? "" : "none";
            item.coluna.style.order = item.visivel ? ordenados.indexOf(item) : 999;
        });

        const vazio = document.getElementById("nenhumDestinoEncontrado");
        if (vazio) vazio.classList.toggle("d-none", visiveis.length > 0);
    }

    renderizarEstrelas();
    inputBusca?.addEventListener("input", aplicarFiltros);
    filtro?.addEventListener("change", aplicarFiltros);
    aplicarFiltros();
});

// ============================================================
// FORMULÁRIO DE RESERVA
// ============================================================

const form = document.getElementById("reservaForm");
const btnConfirmar = document.getElementById("btnConfirmar");
const resultado = document.getElementById("resultado");
const resumo = document.getElementById("resumo");
const novaReserva = document.getElementById("novaReserva");
const msgErro = document.getElementById("erro");

// Só executa a lógica de reserva se os elementos existirem na página atual.
if (form && btnConfirmar) {

    const campoNomeReserva = document.getElementById("nome");
    const campoDestino = document.getElementById("destino");
    const campoPassageiros = document.getElementById("passageiros");
    const campoTipoViagem = document.getElementById("tipoViagem");
    const campoDataIda = document.getElementById("dataIda");
    const campoDataVolta = document.getElementById("dataVolta");
    const campoTransporte = document.getElementById("transporte");
    const campoClasse = document.getElementById("classe");

    const reservaDestinoForm = document.getElementById("reservaDestinoForm");
    const reservaImagemDestino = document.getElementById("reservaImagemDestino");
    const reservaImagemLegenda = document.getElementById("reservaImagemLegenda");
    const reservaImagemForm = document.getElementById("reservaImagemForm");
    const reservaImagemLegendaForm = document.getElementById("reservaImagemLegendaForm");
    const reservaPrecoForm = document.getElementById("reservaPrecoForm");
    const reservaTipoForm = document.getElementById("reservaTipoForm");
    const precoPassageiroMeta = document.getElementById("precoPassageiroMeta");
    const tipoViagemMeta = document.getElementById("tipoViagemMeta");
    const campoPrecoPassageiro = document.getElementById("precoPassageiro");
    const campoSubtotal = document.getElementById("subtotal");
    const campoDesconto = document.getElementById("desconto");
    const campoTotal = document.getElementById("total");
    const textoDesconto = document.getElementById("textoDesconto");

    function normalizarNumero(valor) {
        const texto = String(valor ?? "").trim();
        if (!texto) return 0;

        // Aceita tanto 1300.00 quanto 1.300,00.
        if (texto.includes(",") && texto.includes(".")) {
            return Number(texto.replace(/\./g, "").replace(",", "."));
        }

        return Number(texto.replace(",", "."));
    }

    function formatarMoeda(valor) {
        return Number(valor || 0).toLocaleString("pt-BR", {
            style: "currency",
            currency: "BRL"
        });
    }

    function validarDataReserva(data) {
        if (!data) return "Selecione uma data.";

        const dataReserva = new Date(data + "T00:00:00");
        if (Number.isNaN(dataReserva.getTime())) return "Data inválida.";

        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0);

        const dataMinima = new Date();
        dataMinima.setMonth(dataMinima.getMonth() + 1);
        dataMinima.setHours(0, 0, 0, 0);

        if (dataReserva < hoje) {
            return "A reserva não pode ser feita no passado.";
        }

        if (dataReserva < dataMinima) {
            return "A reserva deve ser feita com pelo menos 1 mês de antecedência.";
        }

        return null;
    }

    function validarPassageiros(valor) {
        const numero = Number.parseInt(valor, 10);

        if (!valor || Number.isNaN(numero)) {
            return "Informe a quantidade de passageiros.";
        }

        if (numero < 1) return "É necessário pelo menos 1 passageiro.";
        if (numero > 9) return "Não é possível adicionar mais de 9 passageiros.";

        return null;
    }

    function formatarData(data) {
        if (!data) return "";
        return data.split("-").reverse().join("/");
    }

    // Imagens locais usadas nesta etapa visual. Depois, essa informação poderá
    // vir diretamente do banco de dados sem alterar o layout.
    const imagensDestinos = {
        "campo grande": "../assets/imagens/campo-grande.png",
        "curitiba": "../assets/imagens/curitiba.png",
        "fernando de noronha": "../assets/imagens/fernando-de-nornoha.png",
        "florianopolis": "../assets/imagens/florianopolis.jpg",
        "fortaleza": "../assets/imagens/Fortaleza.png",
        "foz do iguacu": "../assets/imagens/foz-do-iguacu.jpg",
        "goiania": "../assets/imagens/Goiania.png",
        "gramado": "../assets/imagens/gramado.jpg",
        "jericoacoara": "../assets/imagens/Jericoacoara.png",
        "lencois maranhenses": "../assets/imagens/maranhao.jpg",
        "maceio": "../assets/imagens/maceio.jpg",
        "manaus": "../assets/imagens/amazonia.jpg",
        "porto alegre": "../assets/imagens/porto-alegre.jpg",
        "rio de janeiro": "../assets/imagens/rio-de-janeiro.jpg",
        "salvador": "../assets/imagens/salvador.jpg",
        "sao paulo": "../assets/imagens/sao-paulo.jpg"
    };

    function normalizarTexto(texto) {
        return String(texto || "")
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .toLowerCase()
            .trim();
    }

    function obterImagemDestino(nomeDestino) {
        return imagensDestinos[normalizarTexto(nomeDestino)] || "../assets/imagens/hero-bg.jpg";
    }

    function calcularValores(precoBase, passageiros, tipoViagem, transporte, classe) {
        let subtotal = normalizarNumero(precoBase) * passageiros;

        if (tipoViagem === "ida_volta") subtotal *= 2;
        if (transporte === "Ônibus") subtotal *= 0.70;

        if (classe === "VIP") {
            subtotal += 150 * passageiros;
        } else if (classe === "Executiva") {
            subtotal += 300 * passageiros;
        }

        // Benefício da classe econômica no avião: 8%.
        const descontoEconomica = transporte === "Avião" && classe === "Econômica" ? 0.08 : 0;

        // Benefício de grupo: 3% a cada 2 passageiros, até 12%.
        const descontoGrupo = Math.min(Math.floor(passageiros / 2) * 0.03, 0.12);
        const percentualDesconto = descontoEconomica + descontoGrupo;
        const desconto = subtotal * percentualDesconto;
        const total = subtotal - desconto;

        return {
            subtotal,
            descontoEconomica,
            descontoGrupo,
            percentualDesconto,
            desconto,
            total
        };
    }

    function atualizarLocalizacaoDestino() {
        const opcao = campoDestino?.selectedOptions?.[0];
        const nomeCompleto = opcao?.textContent?.trim() || "Selecione um destino";
        const nomeDestino = nomeCompleto.split(" - ")[0].trim();
        const preco = normalizarNumero(opcao?.dataset.preco);

        if (reservaDestinoForm) reservaDestinoForm.textContent = nomeCompleto;
        if (reservaImagemForm && opcao?.value) {
            reservaImagemForm.src = obterImagemDestino(nomeDestino);
            reservaImagemForm.alt = `Imagem de ${nomeDestino}`;
        }
        if (reservaImagemLegendaForm) reservaImagemLegendaForm.innerHTML = `<i class="bi bi-geo-alt-fill"></i> ${nomeDestino}`;
        if (reservaPrecoForm) reservaPrecoForm.textContent = preco ? formatarMoeda(preco) : "R$ 0,00";
        if (reservaTipoForm) reservaTipoForm.textContent = "Ida";
    }

    campoDestino.addEventListener("change", atualizarLocalizacaoDestino);
    atualizarLocalizacaoDestino();

    btnConfirmar.addEventListener("click", () => {
        const nome = campoNomeReserva.value.trim();
        const destinoSelecionado = campoDestino.selectedOptions[0];
        const destino = destinoSelecionado?.textContent?.trim() || "";
        const nomeDestino = destinoSelecionado?.dataset.nome || destino.split(" - ")[0].trim();
        const precoDestino = destinoSelecionado?.dataset.preco || 0;
        const passageiros = Number.parseInt(campoPassageiros.value, 10);
        const tipoViagem = campoTipoViagem.value;
        const dataIda = campoDataIda.value;
        const dataVolta = campoDataVolta.value;
        const transporte = campoTransporte.value;
        const classe = campoClasse.value;

        const validacoes = [
            [campoNomeReserva, validarNome(nome)],
            [campoDestino, destinoSelecionado && campoDestino.value ? null : "O destino é obrigatório."],
            [campoPassageiros, validarPassageiros(campoPassageiros.value)],
            [campoTipoViagem, validarCampoObrigatorio(tipoViagem, "O tipo de viagem")],
            [campoDataIda, validarDataReserva(dataIda)],
            [campoTransporte, validarCampoObrigatorio(transporte, "O tipo de transporte")],
            [campoClasse, validarCampoObrigatorio(classe, "A classe")]
        ];

        let temErro = false;

        validacoes.forEach(([campo, mensagem]) => {
            exibirErroCampo(campo, mensagem);
            if (mensagem) temErro = true;
        });

        if (tipoViagem === "ida_volta") {
            const erroDataVolta = validarDataReserva(dataVolta);
            exibirErroCampo(campoDataVolta, erroDataVolta);

            if (erroDataVolta) {
                temErro = true;
            } else if (dataVolta < dataIda) {
                exibirErroCampo(
                    campoDataVolta,
                    "A data de volta deve ser posterior à data de ida."
                );
                temErro = true;
            }
        } else {
            exibirErroCampo(campoDataVolta, null);
        }

        if (transporte === "Ônibus" && classe !== "Econômica") {
            exibirErroCampo(
                campoClasse,
                "Ônibus disponível somente na classe Econômica."
            );
            temErro = true;
        }

        const precoNumerico = normalizarNumero(precoDestino);
        if (!precoNumerico) {
            exibirErroCampo(campoDestino, "Não foi possível obter o preço deste destino.");
            temErro = true;
        }

        if (temErro) {
            msgErro.innerText = "Corrija os campos destacados para continuar.";
            return;
        }

        msgErro.innerText = "";

        const valores = calcularValores(
            precoNumerico,
            passageiros,
            tipoViagem,
            transporte,
            classe
        );

        const textoTipoViagem =
            tipoViagem === "ida_volta" ? "Ida e volta" : "Somente ida";

        if (reservaDestinoForm) reservaDestinoForm.textContent = destino;
        if (reservaImagemForm) reservaImagemForm.src = obterImagemDestino(nomeDestino);
        if (reservaImagemForm) reservaImagemForm.alt = `Imagem de ${nomeDestino}`;
        if (reservaImagemLegendaForm) reservaImagemLegendaForm.innerHTML = `<i class="bi bi-geo-alt-fill"></i> ${nomeDestino}`;
        if (reservaPrecoForm) reservaPrecoForm.textContent = formatarMoeda(precoNumerico);
        if (reservaTipoForm) reservaTipoForm.textContent = tipoViagem === "ida_volta" ? "Ida e volta" : "Ida";
        if (precoPassageiroMeta) precoPassageiroMeta.innerHTML = `${formatarMoeda(precoNumerico)} <small>/ passageiro</small>`;
        if (tipoViagemMeta) tipoViagemMeta.textContent = tipoViagem === "ida_volta" ? "Ida e volta" : "Ida";

        // No card de resultado, a foto acompanha o destino reservado.
        if (reservaImagemDestino) {
            reservaImagemDestino.src = obterImagemDestino(nomeDestino);
            reservaImagemDestino.alt = `Imagem de ${nomeDestino}`;
        }
        if (reservaImagemLegenda) {
            reservaImagemLegenda.innerHTML = `<i class="bi bi-geo-alt-fill"></i> ${nomeDestino}`;
        }

        // O preço exibido nos cards é o preço-base por passageiro.
        // O subtotal/total considera quantidade, ida e volta, transporte, classe e desconto.
        if (campoPrecoPassageiro) {
            campoPrecoPassageiro.textContent = formatarMoeda(precoNumerico);
        }
        campoSubtotal.textContent = formatarMoeda(valores.subtotal);
        campoDesconto.textContent = `- ${formatarMoeda(valores.desconto)}`;
        campoTotal.textContent = formatarMoeda(valores.total);

        textoDesconto.textContent = valores.desconto > 0 ? "Desconto aplicado" : "Desconto";

        // Monta as informações dentro de blocos visuais, evitando o aspecto de
        // informações soltas no card.
        resumo.innerHTML = `
            <section class="reserva-info-grupo">
                <div class="resumo-item">
                    <strong><i class="bi bi-person-fill"></i> Passageiro</strong>
                    <span>${nome}</span>
                </div>
                <div class="resumo-item">
                    <strong><i class="bi bi-people-fill"></i> Passageiros</strong>
                    <span>${passageiros}</span>
                </div>
            </section>

            <section class="reserva-info-grupo">
                <div class="resumo-item resumo-largo resumo-destino-item">
                    <strong><i class="bi bi-geo-alt-fill"></i> Destino</strong>
                    <span>${destino}</span>
                </div>
                <div class="resumo-item resumo-largo">
                    <strong><i class="bi bi-briefcase-fill"></i> Tipo de viagem</strong>
                    <span>${textoTipoViagem}</span>
                </div>
            </section>

            <section class="reserva-info-grupo reserva-info-detalhes">
                <div class="resumo-item">
                    <strong><i class="bi bi-calendar-event-fill"></i> Data de ida</strong>
                    <span>${formatarData(dataIda)}</span>
                </div>

                ${tipoViagem === "ida_volta" ? `
                    <div class="resumo-item">
                        <strong><i class="bi bi-calendar-check-fill"></i> Data de volta</strong>
                        <span>${formatarData(dataVolta)}</span>
                    </div>
                ` : ""}

                <div class="resumo-item">
                    <strong><i class="bi bi-airplane-fill"></i> Transporte</strong>
                    <span>${transporte}</span>
                </div>

                <div class="resumo-item">
                    <strong><i class="bi bi-person-badge-fill"></i> Classe</strong>
                    <span>${classe}</span>
                </div>
            </section>
        `;

        form.classList.add("d-none");
        resultado.classList.remove("d-none");

        // Mantém o topo do card visível quando o resultado aparece.
        resultado.scrollIntoView({ behavior: "smooth", block: "start" });
    });

    novaReserva.addEventListener("click", async () => {
        if (novaReserva.disabled) return;

        const destinoSelecionado = campoDestino.selectedOptions[0];
        const idDestino = Number.parseInt(campoDestino.value, 10);
        const passageiros = Number.parseInt(campoPassageiros.value, 10);
        const tipoViagem = campoTipoViagem.value;
        const dataIda = campoDataIda.value;
        const dataVolta = campoDataVolta.value;
        const transporte = campoTransporte.value;
        const classe = campoClasse.value;
        const precoDestino = normalizarNumero(destinoSelecionado?.dataset.preco);

        if (!idDestino || !dataIda || !passageiros || !transporte || !classe || !precoDestino) {
            msgErro.innerText = "Não foi possível confirmar a reserva. Volte ao formulário e confira os dados.";
            return;
        }

        const valores = calcularValores(
            precoDestino,
            passageiros,
            tipoViagem,
            transporte,
            classe
        );

        const dadosReserva = {
            id_destino: idDestino,
            data_viagem: dataIda,
            data_volta: tipoViagem === "ida_volta" ? dataVolta : null,
            tipo_viagem: tipoViagem,
            quantidade_passageiros: passageiros,
            transporte: transporte,
            assento: classe,
            valor_total: Number(valores.total.toFixed(2))
        };

        novaReserva.disabled = true;
        novaReserva.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Salvando reserva...';
        msgErro.innerText = "";

        try {
            const resposta = await fetch("../php/criar-reserva.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify(dadosReserva)
            });

            const resultadoApi = await resposta.json();

            if (!resposta.ok || !resultadoApi.sucesso) {
                throw new Error(resultadoApi.mensagem || "Não foi possível salvar a reserva.");
            }

            novaReserva.innerHTML = '<i class="bi bi-check-circle me-2"></i>Reserva confirmada';
            window.location.href = "dashboard.php#minhas-viagens";

        } catch (erro) {
            console.error("Erro ao confirmar reserva:", erro);
            msgErro.innerText = erro.message || "Erro ao salvar a reserva. Tente novamente.";
            novaReserva.disabled = false;
            novaReserva.innerHTML = '<i class="bi bi-check-circle me-2"></i>Confirmar reserva';
        }
    });
}

// fecha o menu mobile antes de rolar até a seção
document.addEventListener("DOMContentLoaded", () => {
  const offcanvasEl = document.getElementById("menuMobile");
  if (!offcanvasEl) return;

  const linksAncora = offcanvasEl.querySelectorAll('a[href^="#"]');

  linksAncora.forEach(link => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const destino = link.getAttribute("href");
      const targetEl = document.querySelector(destino);
      const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);

      offcanvasEl.addEventListener("hidden.bs.offcanvas", () => {
        targetEl?.scrollIntoView({ behavior: "smooth" });
      }, { once: true });

      offcanvas.hide();

      setTimeout(() => {
        if (offcanvasEl.classList.contains("show")) {
          offcanvasEl.classList.remove("show");
          document.querySelectorAll(".offcanvas-backdrop").forEach(el => el.remove());
          document.body.style.overflow = "";
          document.body.style.paddingRight = "";
          targetEl?.scrollIntoView({ behavior: "smooth" });
        }
      }, 500);
    });
  });
});