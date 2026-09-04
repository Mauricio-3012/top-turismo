-- TOPTURISMO - MIGRAÇÃO DA RECUPERAÇÃO DE SENHA
-- Execute este arquivo se o banco topturismo já existia antes da atualização.
-- A migração é segura para executar novamente em MariaDB/MySQL que aceite IF NOT EXISTS.

USE topturismo;

ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS pergunta_recuperacao VARCHAR(30) DEFAULT NULL AFTER chave_recuperacao_hash;

ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS resposta_recuperacao_hash VARCHAR(255) DEFAULT NULL AFTER pergunta_recuperacao;

-- Novos cadastros usam pergunta + resposta.
-- Contas antigas que ainda possuem chave_recuperacao_hash continuam funcionando.
