-- MIGRAÇÃO - PERGUNTA E RESPOSTA DE SEGURANÇA
-- Execute em um banco TopTurismo já existente.
-- O script verifica as colunas antes de criá-las para evitar erro por duplicidade.

USE topturismo;

SET @coluna_pergunta = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'pergunta_seguranca'
);
SET @sql_pergunta = IF(@coluna_pergunta = 0,
    'ALTER TABLE usuarios ADD COLUMN pergunta_seguranca VARCHAR(255) DEFAULT NULL AFTER senha',
    'SELECT 1');
PREPARE stmt_pergunta FROM @sql_pergunta;
EXECUTE stmt_pergunta;
DEALLOCATE PREPARE stmt_pergunta;

SET @coluna_resposta = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'resposta_seguranca_hash'
);
SET @sql_resposta = IF(@coluna_resposta = 0,
    'ALTER TABLE usuarios ADD COLUMN resposta_seguranca_hash VARCHAR(255) DEFAULT NULL AFTER pergunta_seguranca',
    'SELECT 1');
PREPARE stmt_resposta FROM @sql_resposta;
EXECUTE stmt_resposta;
DEALLOCATE PREPARE stmt_resposta;

-- Usuários existentes podem cadastrar a pergunta e resposta pelo Meu Perfil.
