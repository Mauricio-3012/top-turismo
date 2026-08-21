// modo claro/escuro
const body = document.body;
const dropdownButton = document.getElementById("temaMenu");

document.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("theme") || "light";
  setTheme(savedTheme);
});

  // Detecta se estamos dentro de /pages/ ou na raiz do site, pra montar os links certos
const dentroDePages = window.location.pathname.includes("/pages/");
const caminhoPhp = dentroDePages ? "../php/" : "php/";

// Faz UMA única checagem de login e compartilha o resultado com quem precisar
// (menu de usuário no header e botões "Reservar" da página principal).
const statusLogin = fetch(caminhoPhp + "usuario-logado.php")
  .then((resposta) => resposta.ok)
  .catch(() => false);

// Verifica se o usuário está logado e atualiza o menu (Entrar/Cadastre-se -> Meu Painel/Sair)
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

// Controla os botões "Reservar" da página principal:
// - Usuário logado -> vai direto para a página de reserva, já com o destino escolhido.
// - Usuário não logado -> abre o modal pedindo login/cadastro.
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

document.addEventListener("DOMContentLoaded", () => {
    // Seleciona todos os inputs de busca da página (header e seção de destinos)
    const inputsBusca = document.querySelectorAll(".busca-caixa input");

    // Seleciona todos os cards de destinos (as colunas do Bootstrap)
    const cardsDestinos = document.querySelectorAll("#destinos .row > div");

    inputsBusca.forEach(input => {
        input.addEventListener("input", (e) => {
            const termoBusca = e.target.value.toLowerCase().trim();

            // Sincroniza o valor de todos os campos de busca na tela
            inputsBusca.forEach(inEl => {
                if (inEl !== e.target) {
                    inEl.value = e.target.value;
                }
            });

            // Filtra os cards
            cardsDestinos.forEach(card => {
                const titulo = card.querySelector(".nome-destino-overlay")?.textContent.toLowerCase() || "";
                const descricao = card.querySelector(".descricao-destino")?.textContent.toLowerCase() || "";

                // Verifica se o título ou a descrição contêm o termo buscado
                if (titulo.includes(termoBusca) || descricao.includes(termoBusca)) {
                    card.style.display = ""; // Exibe a coluna
                } else {
                    card.style.display = "none"; // Oculta a coluna
                }
            });
        });
    });
});

// form reserva
const form = document.getElementById("reservaForm");
const btnConfirmar = document.getElementById("btnConfirmar");
const resultado = document.getElementById("resultado");
const resumo = document.getElementById("resumo");
const novaReserva = document.getElementById("novaReserva");
const msgErro = document.getElementById("erro");

// Só executa a lógica de reserva se os elementos existirem na página atual
// (evita erro em páginas como o index.php, que não têm esse formulário)
if (form && btnConfirmar) {

  function calcularTotal(distancia, passageiros, assento, transporte) {
    let precoBase = distancia * 0.8 * passageiros;
    let adicional = 0;

    if (transporte === "Ônibus") precoBase = (distancia * 0.8 * passageiros) / 2

    if (assento === "VIP") adicional = 150 * passageiros;
    if (assento === "Executiva") adicional = 300 * passageiros;

    return precoBase + adicional;
  }

  const campoNomeReserva = document.getElementById("nome");
  const campoDestino = document.getElementById("destino");
  const campoPassageiros = document.getElementById("passageiros");
  const campoData = document.getElementById("data");
  const campoTransporte = document.getElementById("transporte");
  const campoAssento = document.getElementById("assento");

  function validarDataReserva(data) {
    if (!data) return "Selecione uma data.";

    const dataReserva = new Date(data + "T00:00:00");
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);

    const dataMinima = new Date();
    dataMinima.setMonth(dataMinima.getMonth() + 1);
    dataMinima.setHours(0, 0, 0, 0);

    if (dataReserva < hoje) return "A reserva não pode ser feita no passado.";
    if (dataReserva < dataMinima) return "A reserva deve ser feita com pelo menos 1 mês de antecedência.";
    return null;
  }

  function validarPassageiros(valor) {
    const numero = parseInt(valor);
    if (!valor || isNaN(numero)) return "Informe a quantidade de passageiros.";
    if (numero < 1) return "É necessário pelo menos 1 passageiro.";
    if (numero > 9) return "Não é possível adicionar mais de 9 passageiros.";
    return null;
  }

  btnConfirmar.addEventListener("click", () => {
    const nome = campoNomeReserva.value.trim();
    const destino = campoDestino.value;
    const distancia = campoDestino.selectedOptions[0]?.dataset.distancia;
    const passageiros = parseInt(campoPassageiros.value);
    const transporte = campoTransporte.value;
    const assento = campoAssento.value;
    const data = campoData.value;

    const validacoes = [
      [campoNomeReserva, validarNome(nome)],
      [campoDestino, validarCampoObrigatorio(destino, "O destino")],
      [campoPassageiros, validarPassageiros(campoPassageiros.value)],
      [campoData, validarDataReserva(data)],
      [campoTransporte, validarCampoObrigatorio(transporte, "O tipo de transporte")],
      [campoAssento, validarCampoObrigatorio(assento, "O tipo de assento")],
    ];

    let temErro = false;
    validacoes.forEach(([campo, mensagem]) => {
      exibirErroCampo(campo, mensagem);
      if (mensagem) temErro = true;
    });

    if (temErro) {
      msgErro.innerText = "Corrija os campos destacados para continuar.";
      return;
    }
    msgErro.innerText = "";

    const dataFormatada = data.split('-').reverse().join('/');
    const total = calcularTotal(Number(distancia), passageiros, assento, transporte);

    const valorFormatado = total.toLocaleString('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    });

    form.classList.add("d-none");
    resultado.classList.remove("d-none");
    resumo.innerHTML = `
      <p> Destino: <b>${destino}</b></p>
      <p> Data: <b>${dataFormatada}</b></p>
      <p> Passageiros: <b>${passageiros}</b></p>
      <p> Tipo de transporte: <b>${transporte}</b>
      <p> Tipo de assento: <b>${assento}</b></p>
      <p>Valor total: <b>${valorFormatado}</b></p>
    `;
  });

  novaReserva.addEventListener("click", () => {
    form.reset();
    form.classList.remove("d-none");
    resultado.classList.add("d-none");
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