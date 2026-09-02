document.addEventListener("DOMContentLoaded", () => {
    const $ = id => document.getElementById(id);
    const form = $("reservaForm");
    if (!form) return;

    // *guarda somente os dados que mudam durante a reserva*
    let assentos = [];
    let ocupados = [];
    let pagamento = "";
    let totalReserva = 0;
    let programacao = null;
    let consulta = 0;
    const hoje = form.dataset.hoje;
    const limite = form.dataset.limite;

    // *formata valores para reais*
    const moeda = valor => Number(valor || 0).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });

    // *protege textos do banco antes de colocá-los dentro do HTML*
    function escapar(texto) {
        const div = document.createElement("div");
        div.textContent = texto ?? "";
        return div.innerHTML;
    }

    // *mostra a mensagem de erro do campo informado*
    function erroCampo(campo, mensagem = "") {
        campo?.classList.toggle("is-invalid", !!mensagem);
        const erro = campo && $("erro-" + campo.id);
        if (erro) erro.textContent = mensagem;
    }

    // *atualiza nome, imagem e preço do destino escolhido*
    function atualizarDestino() {
        const opcao = $("destino").selectedOptions[0];
        const nome = opcao?.dataset.nome || "Selecione um destino";
        const preco = Number(opcao?.dataset.preco || 0);
        $("reservaImagemForm").src = opcao?.dataset.imagem || "../assets/imagens/hero-bg.jpg";
        $("reservaImagemLegendaForm").innerHTML = `<i class="bi bi-geo-alt-fill"></i> ${escapar(nome)}`;
        $("reservaDestinoForm").textContent = nome;
        $("reservaPrecoForm").innerHTML = preco ? `${moeda(preco)} <small>/ passageiro</small>` : "R$ 0,00";
        $("reservaTipoForm").textContent = $("tipoViagem").value === "ida_volta" ? "Ida e volta" : "Ida";
    }

    // *usa a programação preparada pelo PHP no próprio destino*
    function atualizarProgramacao() {
        const opcao = $("destino").selectedOptions[0];
        const prefixo = $("transporte").value === "Avião" ? "Aviao" : "Onibus";
        if (!opcao?.value || !$("transporte").value) {
            programacao = null;
            $("programacaoVoo").classList.add("d-none");
            return;
        }

        programacao = {
            saida: opcao.dataset[`saida${prefixo}`],
            volta: opcao.dataset[`volta${prefixo}`],
            duracao: Number(opcao.dataset[`duracao${prefixo}`] || 0)
        };

        // *destinos novos recebem o horário padrão definido pelo PHP*
        if (!programacao.saida) {
            programacao = $("transporte").value === "Avião"
                ? { saida: "09:00", volta: "18:00", duracao: 120 }
                : { saida: "07:00", volta: "19:00", duracao: 480 };
        }

        const [h, m] = programacao.saida.split(":").map(Number);
        const chegada = h * 60 + m + programacao.duracao;
        $("programacaoVoo").classList.remove("d-none");
        $("programacaoSaida").textContent = programacao.saida;
        $("programacaoChegada").textContent = horaTexto(chegada);
        $("programacaoDuracao").textContent = `${Math.floor(programacao.duracao / 60)}h${String(programacao.duracao % 60).padStart(2, "0")}`;

        if (!$("dataIda").value) {
            $("programacaoEspera").textContent = "Informe a data";
        } else {
            const partida = new Date(`${$("dataIda").value}T${programacao.saida}:00`);
            const minutos = Math.max(0, Math.floor((partida - Date.now()) / 60000));
            $("programacaoEspera").textContent = minutos > 1440
                ? `${Math.floor(minutos / 1440)}d ${Math.floor((minutos % 1440) / 60)}h ${minutos % 60}min`
                : `${Math.floor(minutos / 60)}h ${minutos % 60}min`;
        }

        const idaVolta = $("tipoViagem").value === "ida_volta";
        $("programacaoVolta").classList.toggle("d-none", !idaVolta);
        if (idaVolta) {
            const [vh, vm] = programacao.volta.split(":").map(Number);
            $("programacaoSaidaVolta").textContent = programacao.volta;
            $("programacaoChegadaVolta").textContent = horaTexto(vh * 60 + vm + programacao.duracao);
        }
    }

    // *transforma minutos em horário para mostrar a chegada*
    function horaTexto(minutos) {
        return `${String(Math.floor((minutos % 1440) / 60)).padStart(2, "0")}:${String(minutos % 60).padStart(2, "0")}${minutos >= 1440 ? " (+1 dia)" : ""}`;
    }

    // *consulta no PHP os assentos já ocupados para aquela viagem*
    async function carregarOcupados() {
        const params = new URLSearchParams({
            id_destino: $("destino").value,
            data_viagem: $("dataIda").value,
            transporte: $("transporte").value,
            classe: $("classe").value
        });
        if ([...params.values()].some(valor => !valor)) return;

        const numeroConsulta = ++consulta;
        try {
            const resposta = await fetch(`../php/assentos-disponiveis.php?${params}`);
            const dados = await resposta.json();
            if (!resposta.ok || !dados.sucesso) throw new Error();
            if (numeroConsulta === consulta) ocupados = (dados.ocupados || []).map(String).map(item => item.toUpperCase());
        } catch {
            ocupados = [];
        }
    }

    // *define a faixa de assentos permitida pela classe*
    function faixaAssentos() {
        if ($("transporte").value === "Ônibus") return [1, 12];
        if ($("classe").value === "VIP") return [1, 2];
        if ($("classe").value === "Executiva") return [3, 6];
        return [7, 20];
    }

    // *cria o mapa de assentos depois da consulta ao PHP*
    async function desenharAssentos() {
        const mapa = $("mapaAssentos");
        if (!$("transporte").value || !$("classe").value) {
            mapa.innerHTML = '<div class="mapa-assentos-vazio"><i class="bi bi-airplane"></i><span>Escolha o transporte e a classe para visualizar os assentos.</span></div>';
            $("assentosContador").textContent = "0 selecionado(s)";
            return;
        }

        await carregarOcupados();
        const [inicio, fim] = faixaAssentos();
        const colunas = ["A", "B", "C", "D"];
        let html = `<div class="mapa-veiculo-titulo"><i class="bi ${$("transporte").value === "Ônibus" ? "bi-bus-front-fill" : "bi-airplane-fill"}"></i>${$("transporte").value === "Ônibus" ? "Frente do ônibus" : "Frente do avião"}<span>${$("classe").value}</span></div><div class="mapa-veiculo">`;

        for (let numero = inicio; numero <= fim; numero++) {
            html += `<div class="fileira-assentos"><span class="numero-fileira">${numero}</span>`;
            colunas.forEach((letra, indice) => {
                const assento = numero + letra;
                const ocupado = ocupados.includes(assento);
                html += `<button type="button" class="assento-btn ${indice === 2 ? "corredor-inicio" : ""} ${ocupado ? "ocupado" : ""} ${assentos.includes(assento) ? "selecionado" : ""}" data-seat="${assento}" ${ocupado ? "disabled" : ""}>${assento}</button>`;
            });
            html += "</div>";
        }

        mapa.innerHTML = html + "</div>";
        $("assentoInstrucao").textContent = `Selecione ${Number($("passageiros").value) || 1} assento(s) da classe ${$("classe").value}.`;
        mapa.querySelectorAll(".assento-btn:not(.ocupado)").forEach(botao => botao.addEventListener("click", () => selecionarAssento(botao.dataset.seat)));
        atualizarContador();
    }

    // *adiciona ou remove assentos sem passar da quantidade de passageiros*
    function selecionarAssento(assento) {
        const limite = Number($("passageiros").value) || 0;
        if (assentos.includes(assento)) assentos = assentos.filter(item => item !== assento);
        else if (assentos.length < limite) assentos.push(assento);
        else {
            $("erro").textContent = `Você já selecionou ${limite} assento(s).`;
            return;
        }
        desenharAssentos();
    }

    // *mostra a quantidade de assentos escolhidos*
    function atualizarContador() {
        const limite = Number($("passageiros").value) || 0;
        $("assentosContador").textContent = `${assentos.length}/${limite} selecionado(s)`;
        $("assentosContador").classList.toggle("is-invalid", assentos.length !== limite);
    }

    // *o ônibus usa somente a classe econômica*
    function atualizarClasse() {
        const onibus = $("transporte").value === "Ônibus";
        [...$("classe").options].forEach(opcao => {
            if (opcao.value) opcao.disabled = onibus && opcao.value !== "Econômica";
        });
        if (onibus) $("classe").value = "Econômica";
        else if ($("classe").value === "Econômica") $("classe").value = "";
        assentos = [];
        atualizarProgramacao();
        desenharAssentos();
    }

    // *calcula somente o valor de prévia; o valor oficial é calculado pelo PHP*
    function calcularTotal() {
        const preco = Number($("destino").selectedOptions[0]?.dataset.preco || 0);
        const passageiros = Number($("passageiros").value) || 0;
        let subtotal = preco * passageiros;
        if ($("tipoViagem").value === "ida_volta") subtotal *= 2;
        if ($("transporte").value === "Ônibus") subtotal *= 0.70;
        if ($("classe").value === "VIP") subtotal += 150 * passageiros;
        if ($("classe").value === "Executiva") subtotal += 300 * passageiros;
        const desconto = subtotal * (($('transporte').value === "Avião" && $("classe").value === "Econômica" ? 0.08 : 0) + Math.min(Math.floor(passageiros / 2) * 0.03, 0.12));
        return { preco, subtotal, desconto, total: subtotal - desconto };
    }

    // *confere os dados básicos antes de abrir a confirmação*
    function validarFormulario() {
        document.querySelectorAll(".campo-erro").forEach(erro => erro.textContent = "");
        $("erro").textContent = "";
        let valido = true;
        const nomeErro = typeof validarNome === "function" ? validarNome($("nome").value) : (!$("nome").value.trim() ? "Informe seu nome." : "");
        erroCampo($("nome"), nomeErro);
        if (nomeErro) valido = false;

        [["destino", "Selecione um destino."], ["passageiros", "A quantidade deve estar entre 1 e 9 passageiros."], ["tipoViagem", "Selecione o tipo de viagem."], ["transporte", "Selecione o transporte."], ["classe", "Selecione a classe."]].forEach(([id, mensagem]) => {
            const campo = $(id);
            const invalido = !campo.value || (id === "passageiros" && (campo.value < 1 || campo.value > 9));
            if (invalido) { erroCampo(campo, mensagem); valido = false; }
        });

        if (!$("dataIda").value || $("dataIda").value < hoje || $("dataIda").value > limite) {
            erroCampo($("dataIda"), "Escolha uma data válida dentro do período disponível.");
            valido = false;
        }
        if ($("tipoViagem").value === "ida_volta" && (!$('dataVolta').value || $('dataVolta').value < $('dataIda').value || $('dataVolta').value > limite)) {
            erroCampo($("dataVolta"), "A volta deve ser igual ou posterior à ida.");
            valido = false;
        }
        if ($("transporte").value === "Ônibus" && $("classe").value !== "Econômica") {
            erroCampo($("classe"), "Ônibus disponível somente na classe Econômica.");
            valido = false;
        }
        if (assentos.length !== Number($("passageiros").value)) {
            $("erro").textContent = `Selecione exatamente ${$("passageiros").value || 0} assento(s).`;
            valido = false;
        }
        if (!programacao) {
            $("erro").textContent = "A programação desta viagem não está disponível.";
            valido = false;
        }
        return valido;
    }

    // *preenche a etapa de confirmação com os dados escolhidos*
    function mostrarResumo() {
        const valores = calcularTotal();
        totalReserva = valores.total;
        const opcao = $("destino").selectedOptions[0];
        const nome = opcao?.dataset.nome || "Destino";
        $("reservaImagemDestino").src = opcao?.dataset.imagem || "../assets/imagens/hero-bg.jpg";
        $("reservaImagemLegenda").innerHTML = `<i class="bi bi-geo-alt-fill"></i> ${escapar(nome)}`;
        $("reservaDestinoHero").textContent = nome;
        $("precoPassageiroMeta").innerHTML = `${moeda(valores.preco)} <small>/ passageiro</small>`;
        $("tipoViagemMeta").textContent = $("tipoViagem").value === "ida_volta" ? "Ida e volta" : "Ida";
        $("resumo").innerHTML = `<div class="resumo-item"><strong>Destino</strong><span>${escapar(nome)}</span></div><div class="resumo-item"><strong>Ida</strong><span>${$("dataIda").value.split("-").reverse().join("/")}</span></div>${$("tipoViagem").value === "ida_volta" ? `<div class="resumo-item"><strong>Volta</strong><span>${$("dataVolta").value.split("-").reverse().join("/")}</span></div>` : ""}<div class="resumo-item"><strong>Passageiros</strong><span>${$("passageiros").value}</span></div><div class="resumo-item"><strong>Transporte</strong><span>${$("transporte").value}</span></div><div class="resumo-item"><strong>Assentos</strong><span>${assentos.join(", ")}</span></div><div class="resumo-item"><strong>Saída</strong><span>${programacao.saida}</span></div>`;
        $("precoPassageiro").textContent = moeda(valores.preco);
        $("subtotal").textContent = moeda(valores.subtotal);
        $("desconto").textContent = `- ${moeda(valores.desconto)}`;
        $("total").textContent = moeda(valores.total);
        $("textoDesconto").textContent = "Desconto aplicado";
    }

    // *prepara a tela de pagamento para uma nova tentativa*
    function prepararPagamento() {
        pagamento = "";
        $("pagamentoTotal").textContent = moeda(totalReserva);
        $("totalCartao").textContent = moeda(totalReserva);
        $("taxaJuros").textContent = "0%";
        $("erroPagamento").textContent = "";
        document.querySelectorAll(".pagamento-opcao").forEach(item => item.classList.remove("ativo"));
        $("pixPagamento").classList.add("d-none");
        $("cartaoPagamento").classList.add("d-none");
        $("parcelas").value = "1";
    }

    // *atualiza o valor visual conforme Pix ou cartão*
    function atualizarPagamento() {
        let total = totalReserva;
        const parcelas = Number($("parcelas").value) || 1;
        let juros = 0;
        if (pagamento === "Pix") total *= 0.95;
        if (pagamento === "Cartão") {
            juros = parcelas > 1 ? (parcelas - 1) * 1.5 : 0;
            total *= 1 + juros / 100;
        }
        $("pagamentoTotal").textContent = moeda(total);
        $("totalCartao").textContent = moeda(total);
        $("taxaJuros").textContent = pagamento === "Pix" ? "5% de desconto" : `${juros.toFixed(1)}%`;
    }

    // *confere os campos mínimos do cartão simulado*
    function validarPagamento() {
        $("erroPagamento").textContent = "";
        if (!pagamento) {
            $("erroPagamento").textContent = "Selecione Pix ou Cartão para continuar.";
            return false;
        }
        if (pagamento === "Pix") return true;
        const erros = [["nomeCartao", $("nomeCartao").value.trim().length < 3, "Informe o nome do cartão."], ["numeroCartao", $("numeroCartao").value.replace(/\D/g, "").length < 13, "Informe um número de cartão válido."], ["validadeCartao", !/^(0[1-9]|1[0-2])\/\d{2}$/.test($("validadeCartao").value.trim()), "Use o formato MM/AA."], ["cvvCartao", $("cvvCartao").value.replace(/\D/g, "").length < 3, "CVV inválido."]];
        let valido = true;
        erros.forEach(([id, erro, mensagem]) => { erroCampo($(id), erro ? mensagem : ""); if (erro) valido = false; });
        return valido;
    }

    // *envia a reserva ao PHP, que valida e calcula o valor oficial*
    async function finalizarReserva() {
        if (!validarPagamento()) return;
        const botao = $("finalizarPagamento");
        botao.disabled = true;
        botao.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processando...';
        const dados = { id_destino: Number($("destino").value), data_viagem: $("dataIda").value, data_volta: $("tipoViagem").value === "ida_volta" ? $("dataVolta").value : null, tipo_viagem: $("tipoViagem").value, quantidade_passageiros: Number($("passageiros").value), transporte: $("transporte").value, classe: $("classe").value, assento: assentos.join(", "), pagamento, parcelas: pagamento === "Cartão" ? Number($("parcelas").value) : 1 };

        try {
            const resposta = await fetch("../php/criar-reserva.php", { method: "POST", headers: { "Content-Type": "application/json", Accept: "application/json" }, body: JSON.stringify(dados) });
            const retorno = await resposta.json();
            if (!resposta.ok || !retorno.sucesso) throw new Error(retorno.mensagem || "Não foi possível concluir a reserva.");
            $("numeroReservaSucesso").textContent = `#${retorno.id_reserva}`;
            $("sucessoMensagem").textContent = `Pagamento simulado com ${pagamento}. Sua viagem foi registrada pelo valor de ${moeda(retorno.valor_total)} e já está disponível em Minhas Viagens.`;
            $("pagamentoCard").classList.add("d-none");
            $("sucessoReserva").classList.remove("d-none");
            $("sucessoReserva").scrollIntoView({ behavior: "smooth" });
        } catch (erro) {
            $("erroPagamento").textContent = erro.message || "Erro ao finalizar a reserva.";
        } finally {
            botao.disabled = false;
            botao.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Simular pagamento e confirmar reserva';
        }
    }

    // *liga os campos às funções de atualização da tela*
    $("destino").addEventListener("change", () => { assentos = []; atualizarDestino(); atualizarProgramacao(); desenharAssentos(); });
    $("transporte").addEventListener("change", atualizarClasse);
    $("classe").addEventListener("change", () => { assentos = []; atualizarProgramacao(); desenharAssentos(); });
    $("passageiros").addEventListener("input", () => { assentos = assentos.slice(0, Number($("passageiros").value) || 0); atualizarContador(); desenharAssentos(); });
    $("tipoViagem").addEventListener("change", () => { const idaVolta = $("tipoViagem").value === "ida_volta"; $("campoDataVolta").classList.toggle("d-none", !idaVolta); $("dataVolta").required = idaVolta; if (!idaVolta) $("dataVolta").value = ""; atualizarDestino(); atualizarProgramacao(); });
    $("dataIda").addEventListener("change", () => { $("dataVolta").min = $("dataIda").value || hoje; if ($("dataVolta").value && $("dataVolta").value < $("dataIda").value) $("dataVolta").value = $("dataIda").value; assentos = []; atualizarProgramacao(); desenharAssentos(); });
    $("dataVolta").addEventListener("change", atualizarProgramacao);
    $("btnConfirmar").addEventListener("click", () => { if (!validarFormulario()) return; mostrarResumo(); form.classList.add("d-none"); $("resultado").classList.remove("d-none"); $("pagamentoCard").classList.add("d-none"); $("sucessoReserva").classList.add("d-none"); });
    $("irPagamento").addEventListener("click", () => { prepararPagamento(); $("resultado").classList.add("d-none"); $("pagamentoCard").classList.remove("d-none"); });
    $("voltarFormulario").addEventListener("click", () => { $("resultado").classList.add("d-none"); form.classList.remove("d-none"); });
    $("voltarConfirmacao").addEventListener("click", () => { $("pagamentoCard").classList.add("d-none"); $("resultado").classList.remove("d-none"); });
    document.querySelectorAll(".pagamento-opcao").forEach(botao => botao.addEventListener("click", () => { pagamento = botao.dataset.pagamento; document.querySelectorAll(".pagamento-opcao").forEach(item => item.classList.toggle("ativo", item === botao)); $("pixPagamento").classList.toggle("d-none", pagamento !== "Pix"); $("cartaoPagamento").classList.toggle("d-none", pagamento !== "Cartão"); atualizarPagamento(); }));
    $("parcelas").addEventListener("change", atualizarPagamento);
    $("finalizarPagamento").addEventListener("click", finalizarReserva);
    $("numeroCartao").addEventListener("input", e => e.target.value = e.target.value.replace(/\D/g, "").slice(0, 19).replace(/(.{4})/g, "$1 ").trim());
    $("validadeCartao").addEventListener("input", e => { const valor = e.target.value.replace(/\D/g, "").slice(0, 4); e.target.value = valor.length > 2 ? `${valor.slice(0, 2)}/${valor.slice(2)}` : valor; });
    $("cvvCartao").addEventListener("input", e => e.target.value = e.target.value.replace(/\D/g, "").slice(0, 4));

    // *permite abrir a reserva já com o destino escolhido no card*
    const destinoInicial = new URLSearchParams(location.search).get("destino");
    if (destinoInicial) $("destino").value = destinoInicial;
    $("dataIda").min = hoje;
    $("dataIda").max = limite;
    $("dataVolta").min = hoje;
    $("dataVolta").max = limite;
    atualizarDestino();
    atualizarProgramacao();
    desenharAssentos();
});
