-- Atualização da tabela reservas para os novos campos da reserva.
-- Execute este arquivo UMA vez no banco TopTurismo existente pelo phpMyAdmin.
ALTER TABLE reservas
  ADD COLUMN classe VARCHAR(30) DEFAULT NULL AFTER transporte,
  ADD COLUMN tipo_assento VARCHAR(30) DEFAULT NULL AFTER assento,
  ADD COLUMN pagamento VARCHAR(30) DEFAULT NULL AFTER tipo_assento,
  ADD COLUMN horario_ida TIME DEFAULT NULL AFTER pagamento,
  ADD COLUMN horario_volta TIME DEFAULT NULL AFTER horario_ida,
  ADD COLUMN duracao_voo_minutos INT DEFAULT NULL AFTER horario_volta;

-- Reservas antigas usavam a coluna assento para guardar a classe.
-- A classe pode ser corrigida manualmente, se houver reservas antigas.
