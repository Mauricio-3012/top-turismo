// modo claro/escuro
const body = document.body;
const dropdownButton = document.getElementById("temaMenu");

document.addEventListener("DOMContentLoaded", () => {
  const savedTheme = localStorage.getItem("theme") || "light";
  setTheme(savedTheme);
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
// (evita erro em páginas como o index.html, que não têm esse formulário)
if (form && btnConfirmar) {

  function calcularTotal(distancia, passageiros, assento, transporte) {
    let precoBase = distancia * 0.8 * passageiros;
    let adicional = 0;

    if (transporte === "Ônibus") precoBase = (distancia * 0.8 * passageiros) / 2

    if (assento === "VIP") adicional = 150 * passageiros;
    if (assento === "Executiva") adicional = 300 * passageiros;

    return precoBase + adicional;
  }

  // verificacao de dados / execucao do botao de confirmar reserva
  btnConfirmar.addEventListener("click", () => {
    const nome = document.getElementById("nome").value.trim();
    const destinoSelect = document.getElementById("destino");
    const destino = destinoSelect.value;
    const distancia = destinoSelect.selectedOptions[0]?.dataset.distancia;
    const passageiros = parseInt(document.getElementById("passageiros").value);
    const transporte = document.getElementById("transporte").value;
    const assento = document.getElementById("assento").value;
    const data = document.getElementById("data").value

    if (!nome || !destino || !passageiros || !assento || !transporte) {
      msgErro.innerText = "Por favor, preencha todos os campos corretamente!";
      form.reset();
      return;
    }
    if (!data) {
      msgErro.innerText = "Por favor, selecione uma data.";
      return;
    }
    if (passageiros > 9) {
      msgErro.innerText = "Não é possível adicionar mais de 9 passageiros.";
      return;
    }

    // requisitos data -> 1 mes de antecedencia / nao pode ser feita no passado
    const dataReserva = new Date(data + "T00:00:00");
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);

    const dataMinima = new Date();
    dataMinima.setMonth(dataMinima.getMonth() + 1);
    dataMinima.setHours(0, 0, 0, 0);

    if (dataReserva < hoje) {
      msgErro.innerText = "A reserva não pode ser feita no passado!";
      return;
    }
    if (dataReserva < dataMinima) {
      msgErro.innerText = "A reserva deve ser feita com pelo menos 1 mês de antecedência!";
      return;
    }
    msgErro.innerText = "";

    // formata data
    const dataFormatada = data.split('-').reverse().join('/');

    const total = calcularTotal(Number(distancia), passageiros, assento, transporte);

    // formata valor total
    const valorFormatado = total.toLocaleString('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    });

    // mostra resultado
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