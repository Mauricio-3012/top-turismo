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
const dentroDeAdmin = window.location.pathname.includes("/admin/");
const caminhoPhp = dentroDePages || dentroDeAdmin ? "../php/" : "php/";

// Faz uma única checagem de login e compartilha o resultado entre o menu
// de usuário e os botões de reserva. Isso evita várias requisições iguais.
const statusLogin = fetch(caminhoPhp + "usuario-logado.php")
  .then((resposta) => resposta.ok)
  .catch(() => false);

const dadosUsuario = fetch(caminhoPhp + "usuario-logado.php")
  .then((resposta) => (resposta.ok ? resposta.json() : null))
  .catch(() => null);

// ============================================================
// AUTENTICAÇÃO E MENU DO USUÁRIO
// ============================================================
// Atualiza o menu conforme a sessão retornada pelo backend.
document.addEventListener("DOMContentLoaded", () => {
  const menuDesktop = document.getElementById("userAuthMenuList");
  const menuMobile = document.getElementById("userAuthMenuMobileList");

  if (!menuDesktop && !menuMobile) return;

  const linkDashboard = dentroDePages ? "dashboard.php" : (dentroDeAdmin ? "../pages/dashboard.php" : "./pages/dashboard.php");
  const linkLogout = caminhoPhp + "logout.php";

  statusLogin.then((logado) => {
    if (!logado) return;

    dadosUsuario.then((usuario) => {
      const admin = usuario?.tipo === "admin";
      const linkAdmin = dentroDePages ? "../admin/dashboard.php" : (dentroDeAdmin ? "dashboard.php" : "./admin/dashboard.php");
      const itemAdmin = admin ? `<li><a class="dropdown-item" href="${linkAdmin}"><i class="bi bi-speedometer2 me-2"></i>Painel Admin</a></li>` : "";
      const itemAdminMobile = admin ? `<li class="mb-2"><a href="${linkAdmin}" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item"><i class="bi bi-speedometer2 fs-4"></i> Painel Admin</a></li>` : "";
      const itensLogado = `
        <li><a class="dropdown-item" href="${linkDashboard}"><i class="bi bi-person-fill me-2"></i>Meu Perfil</a></li>
        ${itemAdmin}
        <li><a class="dropdown-item" href="${linkLogout}"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
      `;
      if (menuDesktop) menuDesktop.innerHTML = itensLogado;

      const itensLogadoMobile = `
        <li class="mb-2"><a href="${linkDashboard}" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item"><i class="bi bi-person-circle fs-4"></i> Meu Painel</a></li>
        ${itemAdminMobile}
        <li><a href="${linkLogout}" class="d-flex align-items-center gap-3 p-2 rounded menu-mobile-item"><i class="bi bi-box-arrow-right fs-4"></i> Sair</a></li>
      `;
      if (menuMobile) menuMobile.innerHTML = itensLogadoMobile;
    });
  });
});

// ============================================================
// ALTERAÇÃO DE SENHA NO DASHBOARD
// ============================================================
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".btn-toggle-senha").forEach((botao) => {
    botao.addEventListener("click", () => {
      const campo = document.getElementById(botao.dataset.target);
      if (!campo) return;
      const visivel = campo.type === "text";
      campo.type = visivel ? "password" : "text";
      botao.innerHTML = visivel ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
      botao.setAttribute("aria-label", visivel ? "Mostrar senha" : "Ocultar senha");
    });
  });

  const formPerfil = document.querySelector('form[action="../php/dashboard.php"]');
  if (!formPerfil) return;
  formPerfil.addEventListener("submit", (event) => {
    const atual = document.getElementById("senhaAtual")?.value.trim() || "";
    const nova = document.getElementById("senhaNova")?.value || "";
    const confirmacao = document.getElementById("senhaConfirmacao")?.value || "";
    if (!atual && !nova && !confirmacao) return;
    if (!atual || !nova || !confirmacao) { event.preventDefault(); alert("Para alterar a senha, preencha a senha atual, a nova senha e a confirmação."); return; }
    if (nova.length < 6) { event.preventDefault(); alert("A nova senha deve ter pelo menos 6 caracteres."); return; }
    if (nova !== confirmacao) { event.preventDefault(); alert("A confirmação da nova senha não confere."); }
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
// DESTINOS VINDOS DO BANCO DE DADOS
// ============================================================
document.addEventListener("DOMContentLoaded", () => {
  const container = document.querySelector("#destinos .row");
  if (!container) return;

  const apiDestinos = caminhoPhp + "destinos.php";
  const escapeHtml = (valor) => String(valor ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");

  const normalizarImagem = (caminho) => {
    if (!caminho) return "";
    return String(caminho).replace(/^\.\//, "");
  };

  const renderizarDestinos = (destinos) => {
    if (!Array.isArray(destinos) || !destinos.length) return;

    container.innerHTML = destinos.map((destino) => {
      const nome = escapeHtml(destino.nome_destino);
      const local = escapeHtml(`${destino.cidade_destino}${destino.estado_destino ? " - " + destino.estado_destino : ""}`);
      const descricao = escapeHtml(destino.descricao_destino);
      const imagem = escapeHtml(normalizarImagem(destino.img_destino));
      const foto2 = escapeHtml(normalizarImagem(destino.img_destino_2));
      const foto3 = escapeHtml(normalizarImagem(destino.img_destino_3));
      const preco = Number(destino.preco_destino || 0).toLocaleString("pt-BR", { minimumFractionDigits: 0, maximumFractionDigits: 2 });
      const avaliacao = Number(destino.avaliacao_destino || 0).toFixed(1);
      const regiao = escapeHtml(destino.regiao_destino || "");
      const popularidade = Number(destino.popularidade_destino || 0);
      const cidade = escapeHtml(destino.cidade_destino || "");
      const estado = escapeHtml(destino.estado_destino || "");
      const pais = escapeHtml(destino.pais_destino || "Brasil");
      const consultaMaps = escapeHtml([destino.nome_destino, destino.cidade_destino, destino.estado_destino, destino.pais_destino || "Brasil"].filter(Boolean).join(", "));
      const fotos = [foto2, foto3].filter(Boolean).join("|");

      return `
        <div class="col-md-6 mb-4">
          <div class="card h-100 card-destino-custom"
            data-avaliacao="${avaliacao}"
            data-fotos="${fotos}"
            data-popularidade="${popularidade}"
            data-regiao="${regiao}"
            data-maps-query="${consultaMaps}">
            <div class="card-img-container">
              <img alt="${nome}" class="card-img-top" src="./${imagem}">
              <div class="card-info-fixa">
                <div class="card-top-row">
                  <h5 class="nome-destino-overlay">${local}</h5>
                  <div class="preco-badges">
                    <div class="preco-badge">R$ ${preco}</div>
                    <div class="ida-badge"><i class="bi bi-arrow-right"></i> Ida</div>
                  </div>
                </div>
                <div class="avaliacao-estrelas" aria-label="Avaliação ${avaliacao} de 5"></div>
              </div>
              <div class="card-overlay">
                <div class="overlay-conteudo">
                  <p class="descricao-destino">${descricao}</p>
                  <button class="btn btn-custom btn-ver-mais p-4" data-destino="${nome}" type="button">
                    <i class="bi bi-info-circle"></i> Mais Detalhes
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>`;
    }).join("");
  };

  fetch(apiDestinos, { cache: "no-store" })
    .then((resposta) => resposta.ok ? resposta.json() : Promise.reject(new Error("API indisponível")))
    .then((destinos) => renderizarDestinos(destinos))
    .catch(() => {
      // Mantém os 16 cards que já acompanham o projeto como fallback.
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
    ? bootstrap.Carousel.getOrCreateInstance(carouselElement, { interval: 4000, ride: false, pause: false, wrap: true })
    : null;
  let carrosselInterval = null;
  let carrosselPausado = false;
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
    const nomeMaps = card.dataset.mapsQuery || titulo.replace(/\s+-\s+[A-Z]{2}$/i, "");
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
      elMaps.setAttribute("aria-label", `Abrir ${nomeMaps} no Google Maps`);
      elMaps.innerHTML = '<img class="google-maps-icon" src="./assets/imagens/google-maps.svg" alt="Google Maps">';
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

    // Inicia a troca automática das 3 fotos. O intervalo é reiniciado
    // sempre que um novo destino é aberto para evitar múltiplos timers.
    clearInterval(carrosselInterval);
    carrosselPausado = false;
    carrosselInterval = setInterval(() => {
      if (!carrosselPausado && carouselElement && modalElement.classList.contains("show")) {
        carousel?.next();
      }
    }, 4000);

    modal.show();
  }

  container.addEventListener("click", (event) => {
    const botao = event.target.closest(".btn-ver-mais");
    if (!botao || !container.contains(botao)) return;

    event.preventDefault();
    event.stopPropagation();

    statusLogin.then((logado) => {
      if (logado) {
        abrirDetalhes(botao);
        return;
      }

      const loginModalElement = document.getElementById("loginModal");
      if (loginModalElement) {
        bootstrap.Modal.getOrCreateInstance(loginModalElement).show();
      }
    });
  });

  // Pausa o carrossel enquanto o usuário mantém o mouse sobre as imagens.
  carouselElement?.addEventListener("mouseenter", () => {
    carrosselPausado = true;
  });

  carouselElement?.addEventListener("mouseleave", () => {
    carrosselPausado = false;
  });

  // Para o timer ao fechar o modal e libera o ciclo ao abrir novamente.
  modalElement?.addEventListener("hidden.bs.modal", () => {
    clearInterval(carrosselInterval);
    carrosselInterval = null;
    carrosselPausado = false;
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
  const modoEscuro = theme === "dark";
  body.classList.toggle("dark-mode", modoEscuro);
  document.documentElement.classList.toggle("dark-mode", modoEscuro);
  if (dropdownButton) {
    dropdownButton.classList.toggle("btn-dark", modoEscuro);
    dropdownButton.classList.toggle("btn-light", !modoEscuro);
    dropdownButton.innerHTML = modoEscuro
      ? '<i class="bi bi-moon-fill"></i>'
      : '<i class="bi bi-sun-fill"></i>';
  }
  localStorage.setItem("theme", modoEscuro ? "dark" : "light");
}

// ============================================================
// BUSCA, FILTRO, ORDENAÇÃO E AVALIAÇÕES
// ============================================================
document.addEventListener("DOMContentLoaded", () => {
    const inputBusca = document.querySelector(".busca-caixa input");
    const filtro = document.getElementById("filtroDestinos");
    const filtroLabel = document.querySelector(".filtro-destinos-label");
    const filtroOpcoes = document.querySelectorAll(".filtro-opcao");
    const container = document.querySelector("#destinos .row");
    let criterioFiltro = "todos";

    filtroOpcoes.forEach((opcao) => {
        opcao.addEventListener("click", () => {
            criterioFiltro = opcao.dataset.value || "todos";
            if (filtroLabel) filtroLabel.textContent = opcao.textContent.trim();
            filtroOpcoes.forEach((item) => item.classList.toggle("active", item === opcao));
            aplicarFiltros();
        });
    });

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
        const criterio = criterioFiltro;

        const colunas = container ? Array.from(container.children) : [];
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
    if (container) {
      const observer = new MutationObserver(() => { renderizarEstrelas(); aplicarFiltros(); });
      observer.observe(container, { childList: true });
    }
    aplicarFiltros();
});

// ============================================================
// RESERVA — ASSENTOS, HORÁRIOS PROGRAMADOS E PAGAMENTO
// ============================================================

const form = document.getElementById("reservaForm");
const btnConfirmar = document.getElementById("btnConfirmar");

if (form && btnConfirmar) {
    const $ = (id) => document.getElementById(id);
    const campoNomeReserva = $("nome");
    const campoDestino = $("destino");
    const campoPassageiros = $("passageiros");
    const campoTipoViagem = $("tipoViagem");
    const campoDataIda = $("dataIda");
    const campoDataVolta = $("dataVolta");
    const campoTransporte = $("transporte");
    const campoClasse = $("classe");
    const mapaAssentos = $("mapaAssentos");
    const assentosContador = $("assentosContador");
    const assentoInstrucao = $("assentoInstrucao");
    const programacaoVoo = $("programacaoVoo");
    const programacaoVolta = $("programacaoVolta");
    const msgErro = $("erro");
    const resultado = $("resultado");
    const resumo = $("resumo");
    const pagamentoCard = $("pagamentoCard");
    const sucessoReserva = $("sucessoReserva");

    const reservaImagemForm = $("reservaImagemForm");
    const reservaImagemLegendaForm = $("reservaImagemLegendaForm");
    const reservaPrecoForm = $("reservaPrecoForm");
    const reservaTipoForm = $("reservaTipoForm");
    const reservaImagemDestino = $("reservaImagemDestino");
    const reservaImagemLegenda = $("reservaImagemLegenda");
    const reservaDestinoHero = $("reservaDestinoHero");
    const precoPassageiroMeta = $("precoPassageiroMeta");
    const tipoViagemMeta = $("tipoViagemMeta");
    const campoPrecoPassageiro = $("precoPassageiro");
    const campoSubtotal = $("subtotal");
    const campoDesconto = $("desconto");
    const campoTotal = $("total");
    const textoDesconto = $("textoDesconto");

    const state = {
        schedule: null,
        selectedSeats: [],
        occupiedSeats: [],
        reservationValues: null,
        paymentMethod: "",
        paymentTotal: 0
    };

    const imagensDestinos = {
        "campo grande": "../assets/imagens/campo-grande.png", "curitiba": "../assets/imagens/curitiba.png",
        "fernando de noronha": "../assets/imagens/fernando-de-nornoha.png", "florianopolis": "../assets/imagens/florianopolis.jpg",
        "fortaleza": "../assets/imagens/Fortaleza.png", "foz do iguacu": "../assets/imagens/foz-do-iguacu.jpg",
        "goiania": "../assets/imagens/Goiania.png", "gramado": "../assets/imagens/gramado.jpg",
        "jericoacoara": "../assets/imagens/Jericoacoara.png", "lencois maranhenses": "../assets/imagens/maranhao.jpg",
        "maceio": "../assets/imagens/maceio.jpg", "manaus": "../assets/imagens/amazonia.jpg",
        "porto alegre": "../assets/imagens/porto-alegre.jpg", "rio de janeiro": "../assets/imagens/rio-de-janeiro.jpg",
        "salvador": "../assets/imagens/salvador.jpg", "sao paulo": "../assets/imagens/sao-paulo.jpg"
    };

    function normalizarTexto(texto) {
        return String(texto || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().trim();
    }
    function normalizarNumero(valor) {
        const texto = String(valor ?? "").trim();
        if (!texto) return 0;
        if (texto.includes(",") && texto.includes(".")) return Number(texto.replace(/\./g, "").replace(",", "."));
        return Number(texto.replace(",", "."));
    }
    function formatarMoeda(valor) { return Number(valor || 0).toLocaleString("pt-BR", { style: "currency", currency: "BRL" }); }
    function formatarData(data) { return data ? String(data).split("-").reverse().join("/") : ""; }
    function formatarDuracao(minutos) { const h = Math.floor(Number(minutos) / 60); const m = Number(minutos) % 60; return `${h}h${String(m).padStart(2, "0")}`; }
    function calcularChegada(horario, minutos) {
        const [h, m] = String(horario).slice(0, 5).split(":").map(Number);
        const total = h * 60 + m + Number(minutos);
        const dia = Math.floor(total / 1440);
        const restante = total % 1440;
        return `${String(Math.floor(restante / 60)).padStart(2, "0")}:${String(restante % 60).padStart(2, "0")}${dia ? " (+1 dia)" : ""}`;
    }
    function calcularTempoEspera(data, horario) {
        if (!data || !horario) return "—";
        const partida = new Date(`${data}T${horario}:00`);
        const diferenca = partida.getTime() - Date.now();
        if (diferenca <= 0) return "Saída próxima";
        const minutos = Math.floor(diferenca / 60000), dias = Math.floor(minutos / 1440), horas = Math.floor((minutos % 1440) / 60), mins = minutos % 60;
        return dias ? `${dias}d ${horas}h ${mins}min` : `${horas}h ${mins}min`;
    }
    function imagemDestino(nome) { return imagensDestinos[normalizarTexto(nome)] || "../assets/imagens/hero-bg.jpg"; }
    function escaparHtml(valor) { const div = document.createElement("div"); div.textContent = valor ?? ""; return div.innerHTML; }

    function adicionarMesesSeguro(dataBase, meses) {
        const d = new Date(dataBase.getFullYear(), dataBase.getMonth(), dataBase.getDate());
        const dia = d.getDate();
        d.setDate(1);
        d.setMonth(d.getMonth() + meses);
        const ultimoDia = new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate();
        d.setDate(Math.min(dia, ultimoDia));
        d.setHours(0, 0, 0, 0);
        return d;
    }

    function limitesDatasReserva() {
        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0);
        return { hoje, maximo: adicionarMesesSeguro(hoje, 9) };
    }

    function aplicarLimitesDatas() {
        const { hoje, maximo } = limitesDatasReserva();
        const iso = d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,"0")}-${String(d.getDate()).padStart(2,"0")}`;
        campoDataIda.min = iso(hoje);
        campoDataIda.max = iso(maximo);
        campoDataVolta.min = campoDataIda.value && campoDataIda.value > iso(hoje) ? campoDataIda.value : iso(hoje);
        campoDataVolta.max = iso(maximo);
        const texto = `Disponibilidade: ${iso(hoje).split("-").reverse().join("/")} até ${iso(maximo).split("-").reverse().join("/")}.`;
        const limiteIda = $("limiteDataIda");
        const limiteVolta = $("limiteDataVolta");
        if (limiteIda) limiteIda.textContent = texto;
        if (limiteVolta) limiteVolta.textContent = texto + " A volta deve ser igual ou posterior à ida.";
    }

    function validarDataReserva(data) {
        if (!data) return "Selecione uma data.";
        const d = new Date(`${data}T00:00:00`);
        if (Number.isNaN(d.getTime())) return "Data inválida.";
        const { hoje, maximo } = limitesDatasReserva();
        if (d < hoje) return "A data da viagem não pode estar no passado.";
        if (d > maximo) return "A data máxima para a viagem é de 9 meses a partir de hoje.";
        return null;
    }
    function validarPassageiros(valor) {
        const n = Number.parseInt(valor, 10);
        if (!valor || Number.isNaN(n)) return "Informe a quantidade de passageiros.";
        if (n < 1 || n > 9) return "A quantidade deve estar entre 1 e 9 passageiros.";
        return null;
    }
    function limparErro(campo) { if (!campo) return; campo.classList.remove("is-invalid"); const el = $(`erro-${campo.id}`); if (el) el.textContent = ""; }
    function erroCampo(campo, mensagem) { if (!campo) return; campo.classList.toggle("is-invalid", !!mensagem); const el = $(`erro-${campo.id}`); if (el) el.textContent = mensagem || ""; }
    function limparErros() { [campoNomeReserva, campoDestino, campoPassageiros, campoTipoViagem, campoDataIda, campoDataVolta, campoTransporte, campoClasse].forEach(limparErro); erroCampo($("mapaAssentos"), ""); msgErro.textContent = ""; }

    function atualizarLocalizacaoDestino() {
        const opt = campoDestino.selectedOptions[0];
        const nome = opt?.dataset.nome || opt?.textContent?.split(" - ")[0] || "Selecione um destino";
        const preco = normalizarNumero(opt?.dataset.preco);
        if (reservaImagemForm && opt?.value) reservaImagemForm.src = imagemDestino(nome);
        if (reservaImagemLegendaForm) reservaImagemLegendaForm.innerHTML = `<i class="bi bi-geo-alt-fill"></i> ${escaparHtml(nome)}`;
        if (reservaPrecoForm) reservaPrecoForm.innerHTML = preco ? `${formatarMoeda(preco)} <small>/ passageiro</small>` : "R$ 0,00";
        if (reservaTipoForm) reservaTipoForm.textContent = campoTipoViagem.value === "ida_volta" ? "Ida e volta" : "Ida";
        atualizarProgramacao();
        renderizarMapaAssentos();
    }

    function calcularValores(precoBase, passageiros, tipoViagem, transporte, classe) {
        let subtotal = precoBase * passageiros;
        if (tipoViagem === "ida_volta") subtotal *= 2;
        if (transporte === "Ônibus") subtotal *= 0.70;
        if (classe === "VIP") subtotal += 150 * passageiros;
        if (classe === "Executiva") subtotal += 300 * passageiros;
        const descontoEconomica = transporte === "Avião" && classe === "Econômica" ? 0.08 : 0;
        const descontoGrupo = Math.min(Math.floor(passageiros / 2) * 0.03, 0.12);
        const desconto = subtotal * (descontoEconomica + descontoGrupo);
        return { subtotal, descontoEconomica, descontoGrupo, desconto, total: subtotal - desconto };
    }

    async function atualizarProgramacao() {
        const idDestino = Number(campoDestino.value), transporte = campoTransporte.value, data = campoDataIda.value;
        if (!idDestino || !transporte) { programacaoVoo?.classList.add("d-none"); state.schedule = null; return; }
        if (data && validarDataReserva(data)) { programacaoVoo?.classList.add("d-none"); state.schedule = null; return; }
        try {
            const url = `../php/programacao.php?id_destino=${encodeURIComponent(idDestino)}&transporte=${encodeURIComponent(transporte)}`;
            const resposta = await fetch(url, { headers: { Accept: "application/json" } });
            const dados = await resposta.json();
            if (!resposta.ok || !dados.sucesso) throw new Error(dados.mensagem || "Programação indisponível.");
            state.schedule = dados.programacao;
            programacaoVoo.classList.remove("d-none");
            $("programacaoSaida").textContent = state.schedule.saida;
            $("programacaoChegada").textContent = calcularChegada(state.schedule.saida, state.schedule.duracao_minutos);
            $("programacaoDuracao").textContent = formatarDuracao(state.schedule.duracao_minutos);
            $("programacaoEspera").textContent = data ? calcularTempoEspera(data, state.schedule.saida) : "Informe a data";
            const idaVolta = campoTipoViagem.value === "ida_volta";
            programacaoVolta.classList.toggle("d-none", !idaVolta);
            if (idaVolta) {
                $("programacaoSaidaVolta").textContent = state.schedule.volta;
                $("programacaoChegadaVolta").textContent = calcularChegada(state.schedule.volta, state.schedule.duracao_minutos);
            }
        } catch (erro) {
            state.schedule = null;
            programacaoVoo?.classList.add("d-none");
            console.error(erro);
        }
    }

    async function atualizarAssentosOcupados() {
        const idDestino = Number(campoDestino.value), data = campoDataIda.value, transporte = campoTransporte.value, classe = campoClasse.value;
        state.occupiedSeats = [];
        if (!idDestino || !data || !transporte || !classe) return;
        try {
            const params = new URLSearchParams({ id_destino: idDestino, data_viagem: data, transporte, classe });
            const resposta = await fetch(`../php/assentos-disponiveis.php?${params}`, { headers: { Accept: "application/json" } });
            const dados = await resposta.json();
            if (resposta.ok && dados.sucesso) state.occupiedSeats = dados.ocupados || [];
        } catch (erro) { console.error("Não foi possível consultar assentos:", erro); }
    }

    function classeDisponivel() {
        if (campoTransporte.value === "Ônibus") return "Econômica";
        return campoClasse.value;
    }

    async function renderizarMapaAssentos() {
        if (!mapaAssentos) return;
        const classe = classeDisponivel();
        const passageiros = Number.parseInt(campoPassageiros.value, 10) || 0;
        if (!classe || !campoTransporte.value) {
            mapaAssentos.innerHTML = '<div class="mapa-assentos-vazio"><i class="bi bi-airplane"></i><span>Escolha o transporte e a classe para visualizar os assentos.</span></div>';
            assentosContador.textContent = "0 selecionado(s)";
            return;
        }
        await atualizarAssentosOcupados();
        const maxRows = campoTransporte.value === "Ônibus" ? 12 : 14;
        const rows = campoTransporte.value === "Ônibus" ? 12 : (classe === "VIP" ? 2 : classe === "Executiva" ? 4 : maxRows);
        const inicio = campoTransporte.value === "Ônibus" ? 1 : (classe === "VIP" ? 1 : classe === "Executiva" ? 3 : 7);
        const colunas = ["A", "B", "C", "D"];
        const titulo = campoTransporte.value === "Ônibus" ? "Frente do ônibus" : "Frente do avião";
        let html = `<div class="mapa-veiculo-titulo"><i class="bi ${campoTransporte.value === "Ônibus" ? "bi-bus-front-fill" : "bi-airplane-fill"}"></i>${titulo}<span>${escaparHtml(classe)}</span></div><div class="mapa-veiculo">`;
        for (let r = 0; r < rows; r++) {
            const numero = inicio + r;
            html += `<div class="fileira-assentos"><span class="numero-fileira">${numero}</span>`;
            colunas.forEach((letra, index) => {
                const seat = `${numero}${letra}`;
                const ocupado = state.occupiedSeats.includes(seat);
                const selected = state.selectedSeats.includes(seat);
                const tipo = (letra === "A" || letra === "D") ? "Janela" : "Corredor";
                html += `<button type="button" class="assento-btn ${index === 2 ? "corredor-inicio" : ""} ${ocupado ? "ocupado" : ""} ${selected ? "selecionado" : ""}" data-seat="${seat}" data-tipo="${tipo}" ${ocupado ? "disabled" : ""} aria-label="Assento ${seat}, ${tipo}${ocupado ? ", indisponível" : ""}">${seat}</button>`;
            });
            html += `</div>`;
        }
        html += `</div>`;
        mapaAssentos.innerHTML = html;
        assentoInstrucao.textContent = passageiros > 1 ? `Selecione ${passageiros} assentos da classe ${classe}.` : `Selecione 1 assento da classe ${classe}.`;
        mapaAssentos.querySelectorAll(".assento-btn:not(.ocupado)").forEach(btn => btn.addEventListener("click", () => {
            const seat = btn.dataset.seat;
            if (state.selectedSeats.includes(seat)) state.selectedSeats = state.selectedSeats.filter(v => v !== seat);
            else if (state.selectedSeats.length < passageiros) state.selectedSeats.push(seat);
            else { msgErro.textContent = `Você já selecionou ${passageiros} assento(s).`; return; }
            renderizarMapaAssentos();
            atualizarResumoAssentos();
        }));
        atualizarResumoAssentos();
    }

    function atualizarResumoAssentos() {
        const passageiros = Number.parseInt(campoPassageiros.value, 10) || 0;
        assentosContador.textContent = `${state.selectedSeats.length}/${passageiros || 0} selecionado(s)`;
        if (state.selectedSeats.length > passageiros && passageiros > 0) state.selectedSeats = state.selectedSeats.slice(0, passageiros);
    }

    function atualizarClasseTransporte() {
        Array.from(campoClasse.options).forEach(opt => {
            if (!opt.value) return;
            opt.disabled = campoTransporte.value === "Ônibus" && opt.value !== "Econômica";
        });
        if (campoTransporte.value === "Ônibus") campoClasse.value = "Econômica";
        if (campoTransporte.value !== "Ônibus" && campoClasse.value === "Econômica") campoClasse.value = "";
        state.selectedSeats = [];
        atualizarProgramacao();
        renderizarMapaAssentos();
    }

    function atualizarDatas() {
        aplicarLimitesDatas();
        const idaVolta = campoTipoViagem.value === "ida_volta";
        $("campoDataVolta").classList.toggle("d-none", !idaVolta);
        campoDataVolta.required = idaVolta;
        atualizarProgramacao();
    }

    function montarResumo(precoNumerico, valores) {
        const destino = campoDestino.selectedOptions[0]?.textContent?.trim() || "";
        const nomeDestino = campoDestino.selectedOptions[0]?.dataset.nome || destino.split(" - ")[0];
        const ida = state.schedule?.saida || "—";
        const chegada = state.schedule ? calcularChegada(ida, state.schedule.duracao_minutos) : "—";
        const assentos = state.selectedSeats.join(", ");
        const tipos = state.selectedSeats.map(seat => /[AD]$/.test(seat) ? "Janela" : "Corredor");
        reservaDestinoHero.textContent = destino;
        precoPassageiroMeta.innerHTML = `${formatarMoeda(precoNumerico)} <small>/ passageiro</small>`;
        tipoViagemMeta.textContent = campoTipoViagem.value === "ida_volta" ? "Ida e volta" : "Ida";
        reservaImagemDestino.src = imagemDestino(nomeDestino);
        reservaImagemLegenda.innerHTML = `<i class="bi bi-geo-alt-fill"></i> ${escaparHtml(nomeDestino)}`;
        campoPrecoPassageiro.textContent = formatarMoeda(precoNumerico);
        campoSubtotal.textContent = formatarMoeda(valores.subtotal);
        campoDesconto.textContent = `- ${formatarMoeda(valores.desconto)}`;
        campoTotal.textContent = formatarMoeda(valores.total);
        textoDesconto.textContent = valores.desconto > 0 ? "Desconto aplicado" : "Desconto";
        resumo.innerHTML = `
            <section class="reserva-info-grupo reserva-info-destaque-horario">
                <div class="resumo-item"><strong><i class="bi bi-calendar-event-fill"></i> Data de ida</strong><span>${formatarData(campoDataIda.value)}</span></div>
                <div class="resumo-item"><strong><i class="bi bi-clock-fill"></i> Horário disponibilizado</strong><span>${ida} → ${chegada}</span></div>
                <div class="resumo-item"><strong><i class="bi bi-hourglass-split"></i> Duração da viagem</strong><span>${formatarDuracao(state.schedule.duracao_minutos)}</span></div>
                <div class="resumo-item"><strong><i class="bi bi-stopwatch-fill"></i> Tempo até a saída</strong><span>${calcularTempoEspera(campoDataIda.value, ida)}</span></div>
            </section>
            <section class="reserva-info-grupo">
                <div class="resumo-item resumo-largo"><strong><i class="bi bi-person-fill"></i> Passageiro responsável</strong><span>${escaparHtml(campoNomeReserva.value.trim())}</span></div>
                <div class="resumo-item"><strong><i class="bi bi-people-fill"></i> Passageiros</strong><span>${passageirosTexto()}</span></div>
                <div class="resumo-item"><strong><i class="bi bi-airplane-fill"></i> Transporte</strong><span>${escaparHtml(campoTransporte.value)}</span></div>
                <div class="resumo-item"><strong><i class="bi bi-person-badge-fill"></i> Classe</strong><span>${escaparHtml(campoClasse.value)}</span></div>
                <div class="resumo-item resumo-largo"><strong><i class="bi bi-grid-3x3-gap-fill"></i> Assentos</strong><span>${escaparHtml(assentos)} — ${escaparHtml([...new Set(tipos)].join(" / "))}</span></div>
                ${campoTipoViagem.value === "ida_volta" ? `<div class="resumo-item resumo-largo"><strong><i class="bi bi-arrow-return-left"></i> Volta</strong><span>${formatarData(campoDataVolta.value)} às ${state.schedule.volta} → ${calcularChegada(state.schedule.volta, state.schedule.duracao_minutos)}</span></div>` : ""}
            </section>`;
    }
    function passageirosTexto() { return `${Number.parseInt(campoPassageiros.value, 10)} passageiro(s)`; }

    function validarFormulario() {
        limparErros();
        let temErro = false;
        const nomeErro = validarNome(campoNomeReserva.value.trim());
        erroCampo(campoNomeReserva, nomeErro); temErro ||= !!nomeErro;
        const destinoErro = !campoDestino.value ? "O destino é obrigatório." : null;
        erroCampo(campoDestino, destinoErro); temErro ||= !!destinoErro;
        const passageirosErro = validarPassageiros(campoPassageiros.value);
        erroCampo(campoPassageiros, passageirosErro); temErro ||= !!passageirosErro;
        const tipoErro = !campoTipoViagem.value ? "Selecione o tipo de viagem." : null;
        erroCampo(campoTipoViagem, tipoErro); temErro ||= !!tipoErro;
        const dataErro = validarDataReserva(campoDataIda.value);
        erroCampo(campoDataIda, dataErro); temErro ||= !!dataErro;
        const transporteErro = !campoTransporte.value ? "Selecione o transporte." : null;
        erroCampo(campoTransporte, transporteErro); temErro ||= !!transporteErro;
        const classeErro = !campoClasse.value ? "Selecione a classe." : (campoTransporte.value === "Ônibus" && campoClasse.value !== "Econômica" ? "Ônibus disponível somente na classe Econômica." : null);
        erroCampo(campoClasse, classeErro); temErro ||= !!classeErro;
        if (campoTipoViagem.value === "ida_volta") {
            const voltaErro = validarDataReserva(campoDataVolta.value);
            erroCampo(campoDataVolta, voltaErro); temErro ||= !!voltaErro;
            if (!voltaErro && campoDataVolta.value < campoDataIda.value) { erroCampo(campoDataVolta, "A data de volta deve ser posterior à data de ida."); temErro = true; }
        } else limparErro(campoDataVolta);
        const qtd = Number.parseInt(campoPassageiros.value, 10) || 0;
        if (state.selectedSeats.length !== qtd) { assentosContador.classList.add("is-invalid"); msgErro.textContent = `Selecione exatamente ${qtd} assento(s) no layout.`; temErro = true; } else assentosContador.classList.remove("is-invalid");
        if (!state.schedule) { msgErro.textContent = "Não foi possível obter a programação da viagem. Verifique destino e transporte."; temErro = true; }
        return !temErro;
    }

    function prepararPagamento() {
        const total = state.reservationValues.total;
        $("pagamentoTotal").textContent = formatarMoeda(total);
        state.paymentMethod = "";
        state.paymentTotal = total;
        document.querySelectorAll(".pagamento-opcao").forEach(btn => btn.classList.remove("ativo"));
        $("pixPagamento").classList.add("d-none"); $("cartaoPagamento").classList.add("d-none"); $("erroPagamento").textContent = "";
        $("parcelas").value = "1"; atualizarTotalPagamento();
    }
    function atualizarTotalPagamento() {
        if (!state.reservationValues) return;
        let total = state.reservationValues.total, juros = 0;
        if (state.paymentMethod === "Pix") total *= 0.95;
        if (state.paymentMethod === "Cartão") {
            const parcelas = Number($("parcelas").value || 1);
            juros = parcelas > 1 ? (parcelas - 1) * 1.5 : 0;
            total *= 1 + juros / 100;
        }
        state.paymentTotal = Number(total.toFixed(2));
        $("pagamentoTotal").textContent = formatarMoeda(state.paymentTotal);
        $("taxaJuros").textContent = state.paymentMethod === "Pix" ? "5% de desconto" : `${juros.toFixed(1)}%`;
        $("totalCartao").textContent = formatarMoeda(state.paymentTotal);
    }

    function validarPagamento() {
        const erro = $("erroPagamento"); erro.textContent = "";
        [$("nomeCartao"), $("numeroCartao"), $("validadeCartao"), $("cvvCartao")].forEach(limparErro);
        if (!state.paymentMethod) { erro.textContent = "Selecione Pix ou Cartão para continuar."; return false; }
        if (state.paymentMethod === "Pix") return true;
        const nome = $("nomeCartao").value.trim(), numero = $("numeroCartao").value.replace(/\D/g, ""), validade = $("validadeCartao").value.trim(), cvv = $("cvvCartao").value.replace(/\D/g, "");
        let ok = true;
        if (!nome || nome.length < 3) { erroCampo($("nomeCartao"), "Informe o nome do cartão."); ok = false; }
        if (numero.length < 13 || numero.length > 19) { erroCampo($("numeroCartao"), "Informe um número de cartão válido."); ok = false; }
        if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(validade)) { erroCampo($("validadeCartao"), "Use o formato MM/AA."); ok = false; }
        if (cvv.length < 3 || cvv.length > 4) { erroCampo($("cvvCartao"), "CVV inválido."); ok = false; }
        return ok;
    }

    campoDestino.addEventListener("change", () => { atualizarLocalizacaoDestino(); });
    campoTransporte.addEventListener("change", atualizarClasseTransporte);
    campoClasse.addEventListener("change", () => { state.selectedSeats = []; renderizarMapaAssentos(); atualizarProgramacao(); });
    campoPassageiros.addEventListener("input", () => { state.selectedSeats = state.selectedSeats.slice(0, Number.parseInt(campoPassageiros.value, 10) || 0); renderizarMapaAssentos(); });
    campoDataIda.addEventListener("change", () => {
        aplicarLimitesDatas();
        if (campoDataVolta.value && campoDataVolta.value < campoDataIda.value) campoDataVolta.value = campoDataIda.value;
        atualizarProgramacao();
        renderizarMapaAssentos();
    });
    campoTipoViagem.addEventListener("change", atualizarDatas);
    campoDataVolta.addEventListener("change", atualizarProgramacao);

    btnConfirmar.addEventListener("click", async () => {
        if (!validarFormulario()) return;
        const opt = campoDestino.selectedOptions[0];
        const preco = normalizarNumero(opt?.dataset.preco);
        const passageiros = Number.parseInt(campoPassageiros.value, 10);
        const valores = calcularValores(preco, passageiros, campoTipoViagem.value, campoTransporte.value, campoClasse.value);
        state.reservationValues = valores;
        montarResumo(preco, valores);
        form.classList.add("d-none"); resultado.classList.remove("d-none"); pagamentoCard.classList.add("d-none"); sucessoReserva.classList.add("d-none");
        resultado.scrollIntoView({ behavior: "smooth", block: "start" });
    });

    $("irPagamento").addEventListener("click", () => { prepararPagamento(); resultado.classList.add("d-none"); pagamentoCard.classList.remove("d-none"); pagamentoCard.scrollIntoView({ behavior: "smooth", block: "start" }); });
    $("voltarFormulario").addEventListener("click", () => { resultado.classList.add("d-none"); form.classList.remove("d-none"); form.scrollIntoView({ behavior: "smooth", block: "start" }); });
    $("voltarConfirmacao").addEventListener("click", () => { pagamentoCard.classList.add("d-none"); resultado.classList.remove("d-none"); resultado.scrollIntoView({ behavior: "smooth", block: "start" }); });

    document.querySelectorAll(".pagamento-opcao").forEach(btn => btn.addEventListener("click", () => {
        state.paymentMethod = btn.dataset.pagamento;
        document.querySelectorAll(".pagamento-opcao").forEach(item => item.classList.toggle("ativo", item === btn));
        $("pixPagamento").classList.toggle("d-none", state.paymentMethod !== "Pix");
        $("cartaoPagamento").classList.toggle("d-none", state.paymentMethod !== "Cartão");
        atualizarTotalPagamento();
    }));
    $("parcelas").addEventListener("change", atualizarTotalPagamento);

    $("numeroCartao").addEventListener("input", e => { let v = e.target.value.replace(/\D/g, "").slice(0, 19); e.target.value = v.replace(/(.{4})/g, "$1 ").trim(); });
    $("validadeCartao").addEventListener("input", e => { let v = e.target.value.replace(/\D/g, "").slice(0, 4); e.target.value = v.length > 2 ? `${v.slice(0, 2)}/${v.slice(2)}` : v; });
    $("cvvCartao").addEventListener("input", e => { e.target.value = e.target.value.replace(/\D/g, "").slice(0, 4); });

    $("finalizarPagamento").addEventListener("click", async () => {
        if (!validarPagamento()) return;
        const botao = $("finalizarPagamento");
        botao.disabled = true; botao.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processando pagamento...';
        try {
            const dadosReserva = {
                id_destino: Number(campoDestino.value), data_viagem: campoDataIda.value,
                data_volta: campoTipoViagem.value === "ida_volta" ? campoDataVolta.value : null,
                tipo_viagem: campoTipoViagem.value, quantidade_passageiros: Number(campoPassageiros.value),
                transporte: campoTransporte.value, classe: campoClasse.value,
                assento: state.selectedSeats.join(","),
                tipo_assento: [...new Set(state.selectedSeats.map(seat => /[AD]$/.test(seat) ? "Janela" : "Corredor"))].join(", "),
                pagamento: state.paymentMethod, parcelas: state.paymentMethod === "Cartão" ? Number($("parcelas").value) : 1,
                nome_cartao: state.paymentMethod === "Cartão" ? $("nomeCartao").value.trim() : "",
                horario_ida: state.schedule.saida, horario_volta: campoTipoViagem.value === "ida_volta" ? state.schedule.volta : null,
                duracao_voo_minutos: state.schedule.duracao_minutos
            };
            const resposta = await fetch("../php/criar-reserva.php", { method: "POST", headers: { "Content-Type": "application/json", Accept: "application/json" }, body: JSON.stringify(dadosReserva) });
            const textoResposta = await resposta.text();
            let dados;
            try {
                dados = JSON.parse(textoResposta);
            } catch (parseErro) {
                console.error("Resposta inválida da API de reserva:", textoResposta);
                throw new Error("O servidor retornou uma resposta inválida. Verifique a conexão com o MySQL e as colunas da tabela reservas.");
            }
            if (!resposta.ok || !dados.sucesso) throw new Error(dados.mensagem || "Não foi possível concluir a reserva.");
            $("numeroReservaSucesso").textContent = `#${dados.id_reserva}`;
            $("sucessoMensagem").textContent = `Pagamento simulado com ${state.paymentMethod}. Sua viagem foi registrada pelo valor de ${formatarMoeda(dados.valor_total)} e já está disponível em Minhas Viagens.`;
            pagamentoCard.classList.add("d-none"); sucessoReserva.classList.remove("d-none"); sucessoReserva.scrollIntoView({ behavior: "smooth", block: "start" });
        } catch (erro) {
            console.error(erro); $("erroPagamento").textContent = erro.message || "Erro ao finalizar a reserva.";
        } finally { botao.disabled = false; botao.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Simular pagamento e confirmar reserva'; }
    });

    atualizarDatas();
    atualizarLocalizacaoDestino();
}

// fecha o menu mobile antes de rolar até a seção
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

// ============================================================
// NAV — destaca a seção atualmente selecionada/visível
// ============================================================
document.addEventListener("DOMContentLoaded", () => {
  const links = [...document.querySelectorAll('.navbar [data-section-link]')];
  if (!links.length) return;
  const ativar = id => links.forEach(link => link.classList.toggle('active', link.dataset.sectionLink === id));
  links.forEach(link => link.addEventListener('click', () => ativar(link.dataset.sectionLink)));
  const hash = window.location.hash.replace('#', '');
  if (hash) ativar(hash);
  const secoes = links.map(l => document.getElementById(l.dataset.sectionLink)).filter(Boolean);
  if ('IntersectionObserver' in window && secoes.length) {
    const observer = new IntersectionObserver(entries => {
      const visiveis = entries.filter(e => e.isIntersecting).sort((a,b) => b.intersectionRatio-a.intersectionRatio);
      if (visiveis[0]) ativar(visiveis[0].target.id);
    }, { rootMargin: '-25% 0px -55% 0px', threshold: [0.1, 0.3, 0.6] });
    secoes.forEach(secao => observer.observe(secao));
  }
});
