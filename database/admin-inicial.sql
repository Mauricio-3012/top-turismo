-- *use este arquivo somente se quiser criar o administrador inicial no banco local*
-- Login: admin@topturismo.com
-- Senha: Admin@123
-- Palavra-chave: TopTurismo2026

USE topturismo;

-- *cria o administrador somente se o e-mail ainda não estiver cadastrado*
INSERT INTO usuarios (nome, email, senha, chave_recuperacao_hash, tipo)
SELECT
    'Administrador TopTurismo',
    'admin@topturismo.com',
    '$2y$12$l4Fc5UdyPAj2I.FwOjvYUOCx52TKq/JY9XrMurxBSPfmbVWhQ.WBq',
    '$2y$12$.gOEJdZaeSyDax6dzcXx5umWtlPzNrrssHn8I0P.AQn7kS7JUlwy6',
    'admin'
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE email = 'admin@topturismo.com'
);
