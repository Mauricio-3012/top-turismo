document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('reservaFormDB');
    if (!form) return;

    const campoDestino = document.getElementById('destino');
    const campoPassageiros = document.getElementById('passageiros');
    const campoData = document.getElementById('data');
    const campoTransporte = document.getElementById('transporte');
    const campoAssento = document.getElementById('assento');
    const campoNome = document.getElementById('nome');
    const btnConfirmar = document.getElementById('btnConfirmar');
    const resultado = document.getElementById('resultado');
    const resumo = document.getElementById('resumo');
    const btnSalvar = document.getElementById('novaReserva');
    const msgErro = document.getElementById('erro');

    let destinos = [];
    let reservaAtual = null;

    function mensagemErro(texto) {
        msgErro.textContent = texto || '';
        msgErro.className = texto ? 'text-center text-danger mt-2' : 'text-center';
    }

    function formatarMoeda(valor) {
        return Number(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    }

    function formatarData(data) {
        return data.split('-').reverse().join('/');
    }

    function validarData(data) {
        if (!data) return 'Selecione uma data.';
        const escolhida = new Date(`${data}T00:00:00`);
        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0);
        const minimo = new Date();
        minimo.setMonth(minimo.getMonth() + 1);
        minimo.setHours(0, 0, 0, 0);

        if (escolhida < hoje) return 'A reserva não pode ser feita no passado.';
        if (escolhida < minimo) return 'A reserva deve ser feita com pelo menos 1 mês de antecedência.';
        return null;
    }

    async function carregarDestinos() {
        try {
            const resposta = await fetch('../php/destinos.php');
            if (!resposta.ok) throw new Error('Falha ao carregar destinos.');

            destinos = await resposta.json();
            campoDestino.innerHTML = '<option value="">Selecione o destino</option>';

            destinos.forEach(destino => {
                const option = document.createElement('option');
                option.value = destino.id_destino;
                option.textContent = `${destino.cidade_destino} - ${destino.pais_destino}`;
                option.dataset.nome = destino.nome_destino;
                option.dataset.preco = destino.preco_destino;
                campoDestino.appendChild(option);
            });

            const destinoQuery = new URLSearchParams(window.location.search).get('destino');
            if (destinoQuery) {
                const encontrado = destinos.find(d => d.nome_destino === destinoQuery || d.cidade_destino === destinoQuery);
                if (encontrado) campoDestino.value = encontrado.id_destino;
            }
        } catch (erro) {
            campoDestino.innerHTML = '<option value="">Erro ao carregar destinos</option>';
            mensagemErro('Não foi possível carregar os destinos do banco de dados.');
        }
    }

    btnConfirmar.addEventListener('click', () => {
        mensagemErro('');

        const destinoId = Number(campoDestino.value);
        const destino = destinos.find(d => Number(d.id_destino) === destinoId);
        const passageiros = Number(campoPassageiros.value);
        const data = campoData.value;
        const transporte = campoTransporte.value;
        const assento = campoAssento.value;
        const nome = campoNome.value.trim();

        const erroData = validarData(data);
        if (!nome) return mensagemErro('Informe o nome do passageiro.');
        if (!destino) return mensagemErro('Selecione um destino.');
        if (!passageiros || passageiros < 1 || passageiros > 9) return mensagemErro('Informe de 1 a 9 passageiros.');
        if (erroData) return mensagemErro(erroData);
        if (!transporte) return mensagemErro('Selecione o tipo de transporte.');
        if (!assento) return mensagemErro('Selecione o tipo de assento.');

        const precoBase = Number(destino.preco_destino) * passageiros * (transporte === 'Ônibus' ? 0.5 : 1);
        const adicional = assento === 'VIP' ? 150 * passageiros : assento === 'Executiva' ? 300 * passageiros : 0;
        const total = precoBase + adicional;

        reservaAtual = {
            destinoId,
            destino: destino.cidade_destino,
            data,
            passageiros,
            transporte,
            assento,
            total
        };

        resumo.innerHTML = `
            <p>Destino: <b>${destino.cidade_destino} - ${destino.pais_destino}</b></p>
            <p>Data: <b>${formatarData(data)}</b></p>
            <p>Passageiros: <b>${passageiros}</b></p>
            <p>Tipo de transporte: <b>${transporte}</b></p>
            <p>Tipo de assento: <b>${assento}</b></p>
            <p>Valor total: <b>${formatarMoeda(total)}</b></p>
        `;

        form.classList.add('d-none');
        resultado.classList.remove('d-none');
    });

    btnSalvar.addEventListener('click', async () => {
        if (!reservaAtual) return;

        btnSalvar.disabled = true;
        btnSalvar.textContent = 'Salvando...';
        mensagemErro('');

        const dados = new FormData();
        dados.append('destino_id', reservaAtual.destinoId);
        dados.append('data', reservaAtual.data);
        dados.append('passageiros', reservaAtual.passageiros);
        dados.append('transporte', reservaAtual.transporte);
        dados.append('assento', reservaAtual.assento);

        try {
            const resposta = await fetch('../php/criar-reserva.php', { method: 'POST', body: dados });
            const retorno = await resposta.json();

            if (!resposta.ok || !retorno.sucesso) {
                throw new Error(retorno.erro || 'Não foi possível salvar a reserva.');
            }

            resumo.innerHTML += '<p class="text-success fw-bold mt-3">✓ Viagem salva em Minhas Viagens!</p>';
            btnSalvar.textContent = 'Reserva salva';
            btnSalvar.disabled = true;
        } catch (erro) {
            mensagemErro(erro.message);
            btnSalvar.disabled = false;
            btnSalvar.textContent = 'Confirmar reserva';
        }
    });

    carregarDestinos();
});
