-- TOPTURISMO - MIGRAÇÃO DA RECUPERAÇÃO DE SENHA
--
-- Use este arquivo SOMENTE se o banco topturismo já existia.
-- Para uma instalação nova, prefira sql/top-turismo-base.sql.
--
-- Esta migração mantém a coluna antiga chave_recuperacao_hash para não
-- quebrar contas criadas por versões anteriores, mas os novos cadastros
-- usam pergunta_recuperacao + resposta_recuperacao_hash.

USE topturismo;

-- Em MariaDB/MySQL recentes, IF NOT EXISTS evita erro se as colunas já existirem.
ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS pergunta_recuperacao VARCHAR(100) DEFAULT NULL AFTER chave_recuperacao_hash;

ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS resposta_recuperacao_hash VARCHAR(255) DEFAULT NULL AFTER pergunta_recuperacao;

-- A coluna da pergunta precisa comportar as perguntas completas do formulário.
ALTER TABLE usuarios
    MODIFY COLUMN pergunta_recuperacao VARCHAR(100) DEFAULT NULL;

-- Conferência da estrutura.
SHOW COLUMNS FROM usuarios LIKE 'pergunta_recuperacao';
SHOW COLUMNS FROM usuarios LIKE 'resposta_recuperacao_hash';

SELECT 'Migração da recuperação de senha concluída.' AS mensagem;
