-- Tabela que liga o usuário à viagem/destino reservado.
-- Execute este script no banco topturismo depois de criar a tabela destinos.

CREATE TABLE reservas (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    id_destino INT NOT NULL,
    data_viagem DATE NOT NULL,
    passageiros INT NOT NULL,
    transporte VARCHAR(30) NOT NULL,
    assento VARCHAR(30) NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'Confirmada',
    criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_reservas_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_reservas_destino
        FOREIGN KEY (id_destino) REFERENCES destinos(id_destino)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE INDEX idx_reservas_usuario ON reservas(usuario_id);
CREATE INDEX idx_reservas_destino ON reservas(id_destino);
CREATE INDEX idx_reservas_data ON reservas(data_viagem);
