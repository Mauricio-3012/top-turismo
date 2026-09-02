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
// *acessa os elementos usados pelo controle de tema*
const body = document.body;
const dropdownButton = document.getElementById("temaMenu");

document.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("theme") || "light";
  setTheme(savedTheme);
});

// ============================================================
// MOSTRAR / OCULTAR SENHAS
// ============================================================
// A alteração da senha é feita pelo PHP. Aqui fica somente a interação visual.
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
});

// ============================================================
// ACESSO À RESERVA
// ============================================================
// O PHP já informa a sessão no <body>. O JavaScript só controla a interação.
document.addEventListener("DOMContentLoaded", () => {
  const botoesReservar = document.querySelectorAll(".btn-reservar");
  if (!botoesReservar.length) return;

  const modalElement = document.getElementById("loginModal");
  const modalLogin = modalElement ? new bootstrap.Modal(modalElement) : null;
  const logado = document.body.dataset.logado === "1";

  botoesReservar.forEach((botao) => {
    botao.addEventListener("click", () => {
      const destino = botao.dataset.destino || "";
      if (logado) {
        const query = destino ? "?destino=" + encodeURIComponent(destino) : "";
        window.location.href = "pages/reservas.php" + query;
      } else if (modalLogin) {
        modalLogin.show();
      }
    });
  });
});

// ============================================================
// MODAL DE DETALHES DOS DESTINOS
// ============================================================
// Delegação de eventos mantém os botões funcionando após filtros e ordenação.
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

    destinoModalAtual = card.dataset.idDestino || botao.dataset.destino || nomeMaps;

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

    const logado = document.body.dataset.logado === "1";
    if (logado) {
      abrirDetalhes(botao);
      return;
    }

    const loginModalElement = document.getElementById("loginModal");
    if (loginModalElement) {
      bootstrap.Modal.getOrCreateInstance(loginModalElement).show();
    }
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
    const logado = document.body.dataset.logado === "1";
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

// *aplica o tema escolhido e salva a preferência no navegador*
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
// *filtra e ordena os cards sem alterar os dados vindos do PHP*
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
// O formulário de reservas fica em assets/js/reservas.js.
// Manter essa lógica separada deixa este arquivo menor e mais fácil de estudar.

// *fecha o menu mobile antes de rolar até a seção*
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
