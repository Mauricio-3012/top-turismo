-- TopTurismo — atualização completa da tabela reservas.
-- Pode ser executado no banco existente. O IF NOT EXISTS evita erro
-- caso alguma coluna já tenha sido criada em uma atualização anterior.

ALTER TABLE reservas
  ADD COLUMN IF NOT EXISTS data_volta DATE DEFAULT NULL AFTER data_viagem,
  ADD COLUMN IF NOT EXISTS tipo_viagem VARCHAR(20) NOT NULL DEFAULT 'ida' AFTER data_volta,
  ADD COLUMN IF NOT EXISTS classe VARCHAR(30) DEFAULT NULL AFTER transporte,
  ADD COLUMN IF NOT EXISTS tipo_assento VARCHAR(60) DEFAULT NULL AFTER assento,
  ADD COLUMN IF NOT EXISTS pagamento VARCHAR(30) DEFAULT NULL AFTER tipo_assento,
  ADD COLUMN IF NOT EXISTS parcelas TINYINT NOT NULL DEFAULT 1 AFTER pagamento,
  ADD COLUMN IF NOT EXISTS taxa_juros_percentual DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER parcelas,
  ADD COLUMN IF NOT EXISTS horario_ida TIME DEFAULT NULL AFTER taxa_juros_percentual,
  ADD COLUMN IF NOT EXISTS horario_volta TIME DEFAULT NULL AFTER horario_ida,
  ADD COLUMN IF NOT EXISTS duracao_voo_minutos INT DEFAULT NULL AFTER horario_volta;

-- O projeto não armazena número, validade ou CVV de cartão.
-- O pagamento é somente uma simulação acadêmica.


-- O sistema trabalha somente com os status Confirmada e Cancelada.
UPDATE reservas SET status = 'confirmada' WHERE LOWER(status) = 'pendente';
UPDATE reservas SET status = 'cancelada' WHERE LOWER(status) IN ('cancelada', 'cancelado');
UPDATE reservas SET status = 'confirmada' WHERE status IS NULL OR TRIM(status) = '' OR LOWER(status) IN ('concluida', 'concluída', 'pendente');
