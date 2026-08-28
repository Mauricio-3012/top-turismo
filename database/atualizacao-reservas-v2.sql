-- TopTurismo — atualização da etapa de pagamento e informações da viagem.
-- Execute este arquivo UMA vez no banco existente.

ALTER TABLE reservas
  ADD COLUMN IF NOT EXISTS data_volta DATE DEFAULT NULL AFTER data_viagem,
  ADD COLUMN IF NOT EXISTS tipo_viagem VARCHAR(20) NOT NULL DEFAULT 'ida' AFTER data_volta,
  ADD COLUMN IF NOT EXISTS parcelas TINYINT NOT NULL DEFAULT 1 AFTER pagamento,
  ADD COLUMN IF NOT EXISTS taxa_juros_percentual DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER parcelas;

-- A nova versão não armazena número, validade ou CVV do cartão.
-- O pagamento é apenas uma simulação para o projeto.
