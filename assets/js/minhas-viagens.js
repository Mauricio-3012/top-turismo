document.addEventListener('DOMContentLoaded', async () => {
    const secao = document.querySelector('#minhas-viagens .card');
    if (!secao) return;

    const titulo = secao.querySelector('h4');
    secao.innerHTML = '';
    if (titulo) secao.appendChild(titulo);

    const loading = document.createElement('p');
    loading.className = 'text-muted text-center mb-0';
    loading.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i>Carregando suas viagens...';
    secao.appendChild(loading);

    try {
        const resposta = await fetch('../php/minhas-viagens.php');
        if (!resposta.ok) throw new Error('Não foi possível consultar suas viagens.');
        const viagens = await resposta.json();

        loading.remove();

        if (!viagens.length) {
            const vazio = document.createElement('div');
            vazio.className = 'text-center py-4';
            vazio.innerHTML = `
                <i class="bi bi-airplane fs-1 text-muted"></i>
                <h5 class="mt-3">Você ainda não possui viagens.</h5>
                <p class="text-muted">Escolha um destino e faça sua primeira reserva.</p>
                <a href="../index.php#destinos" class="btn btn-custom">Explorar destinos</a>
            `;
            secao.appendChild(vazio);
            return;
        }

        viagens.forEach(viagem => {
            const data = viagem.data_viagem.split('-').reverse().join('/');
            const valor = Number(viagem.valor_total).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            const status = viagem.status || 'Confirmada';

            const card = document.createElement('div');
            card.className = 'border rounded-3 p-3 mb-3';
            card.innerHTML = `
                <div class="row align-items-center g-3">
                    <div class="col-md-3">
                        <img src="../${viagem.img_destino}" class="img-fluid rounded-3" alt="${viagem.nome_destino}">
                    </div>
                    <div class="col-md-9">
                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                            <div>
                                <h5 class="fw-bold mb-1">${viagem.cidade_destino} - ${viagem.pais_destino}</h5>
                                <small class="text-muted">Reserva #${viagem.id_reserva}</small>
                            </div>
                            <span class="badge text-bg-success">${status}</span>
                        </div>
                        <div class="row mt-3 small">
                            <div class="col-sm-6 mb-2"><i class="bi bi-calendar3 me-1"></i><b>Data:</b> ${data}</div>
                            <div class="col-sm-6 mb-2"><i class="bi bi-people me-1"></i><b>Passageiros:</b> ${viagem.passageiros}</div>
                            <div class="col-sm-6 mb-2"><i class="bi bi-bus-front me-1"></i><b>Transporte:</b> ${viagem.transporte}</div>
                            <div class="col-sm-6 mb-2"><i class="bi bi-person-seat me-1"></i><b>Assento:</b> ${viagem.assento}</div>
                        </div>
                        <div class="mt-2 fw-bold" style="color: var(--btn-bg);">Total: ${valor}</div>
                    </div>
                </div>
            `;
            secao.appendChild(card);
        });
    } catch (erro) {
        loading.className = 'text-danger text-center mb-0';
        loading.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i>${erro.message}`;
    }
});
