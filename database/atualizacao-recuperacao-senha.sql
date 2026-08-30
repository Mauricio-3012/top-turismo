-- ============================================================
-- TopTurismo - atualização para recuperação de senha
-- ============================================================
-- Execute este arquivo somente se o banco já foi criado sem a
-- coluna chave_recuperacao_hash.
--
-- A palavra-chave nunca é salva em texto puro. O PHP grava apenas
-- o hash usando password_hash().
-- ============================================================

USE topturismo;

ALTER TABLE usuarios
    ADD COLUMN chave_recuperacao_hash VARCHAR(255) DEFAULT NULL
    AFTER senha;

-- Contas de teste da base completa:
-- admin@topturismo.com / Admin@123
-- palavra-chave: TopTurismo2026
-- cliente@topturismo.com / Cliente@123
-- palavra-chave: Cliente2026
--
-- Os hashes abaixo correspondem somente às palavras-chave de teste.
UPDATE usuarios
SET chave_recuperacao_hash = '$2y$12$.gOEJdZaeSyDax6dzcXx5umWtlPzNrrssHn8I0P.AQn7kS7JUlwy6'
WHERE email = 'admin@topturismo.com';

UPDATE usuarios
SET chave_recuperacao_hash = '$2y$12$AaRJQb0/dMxmiOrBBcpTLO6CMdXQGbXOnfZ1Paakw3p3/5fhibmba'
WHERE email = 'cliente@topturismo.com';

-- Usuários já existentes que não sejam contas de teste deverão
-- cadastrar uma nova palavra-chave para ter recuperação disponível.
