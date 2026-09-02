-- MIGRAÇÃO - PERGUNTA E RESPOSTA DE SEGURANÇA
-- Execute este arquivo em um banco TopTurismo já existente.
-- Se o banco for criado novamente pelo topturismo-backup.sql, esta migração não é necessária.

USE topturismo;

ALTER TABLE usuarios
    ADD COLUMN pergunta_seguranca VARCHAR(255) DEFAULT NULL AFTER senha,
    ADD COLUMN resposta_seguranca_hash VARCHAR(255) DEFAULT NULL AFTER pergunta_seguranca;

-- Usuários existentes precisarão cadastrar uma pergunta e resposta novamente
-- antes de conseguirem utilizar a recuperação de senha.
