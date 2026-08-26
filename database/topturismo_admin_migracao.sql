-- Migração para o sistema administrativo do TopTurismo
-- Execute uma vez em um banco que já possui a tabela destinos/usuarios.
ALTER TABLE destinos
  ADD COLUMN estado_destino varchar(2) NOT NULL DEFAULT '' AFTER cidade_destino,
  ADD COLUMN regiao_destino varchar(30) NOT NULL DEFAULT 'sudeste' AFTER pais_destino,
  ADD COLUMN img_destino_2 varchar(255) DEFAULT NULL AFTER img_destino,
  ADD COLUMN img_destino_3 varchar(255) DEFAULT NULL AFTER img_destino_2,
  ADD COLUMN avaliacao_destino decimal(2,1) NOT NULL DEFAULT 5.0 AFTER preco_destino,
  ADD COLUMN popularidade_destino tinyint NOT NULL DEFAULT 3 AFTER avaliacao_destino;


UPDATE destinos SET estado_destino='AL', regiao_destino='nordeste', img_destino_2='assets/imagens/destinos/maceio/foto-2.jpeg', img_destino_3='assets/imagens/destinos/maceio/foto-3.jpg', avaliacao_destino=4.8, popularidade_destino=5 WHERE id_destino=1;
UPDATE destinos SET estado_destino='RJ', regiao_destino='sudeste', img_destino_2='assets/imagens/destinos/rio-de-janeiro/foto-2.jpg', img_destino_3='assets/imagens/destinos/rio-de-janeiro/foto-3.jpg', avaliacao_destino=4.8, popularidade_destino=5 WHERE id_destino=2;
UPDATE destinos SET estado_destino='BA', regiao_destino='nordeste', img_destino_2='assets/imagens/destinos/salvador/foto-2.jpg', img_destino_3='assets/imagens/destinos/salvador/foto-3.jpg', avaliacao_destino=4.6, popularidade_destino=5 WHERE id_destino=3;
UPDATE destinos SET estado_destino='RS', regiao_destino='sul', img_destino_2='assets/imagens/destinos/gramado/foto-2.png', img_destino_3='assets/imagens/destinos/gramado/foto-3.jpg', avaliacao_destino=4.7, popularidade_destino=4 WHERE id_destino=4;
UPDATE destinos SET estado_destino='SP', regiao_destino='sudeste', img_destino_2='assets/imagens/destinos/sao-paulo/foto-2.png', img_destino_3='assets/imagens/destinos/sao-paulo/foto-3.png', avaliacao_destino=4.5, popularidade_destino=5 WHERE id_destino=5;
UPDATE destinos SET estado_destino='PR', regiao_destino='sul', img_destino_2='assets/imagens/destinos/foz-do-iguacu/foto-2.jpg', img_destino_3='assets/imagens/destinos/foz-do-iguacu/foto-3.png', avaliacao_destino=4.9, popularidade_destino=5 WHERE id_destino=6;
UPDATE destinos SET estado_destino='MA', regiao_destino='nordeste', img_destino_2='assets/imagens/destinos/lencois-maranhenses/foto-2.jpg', img_destino_3='assets/imagens/destinos/lencois-maranhenses/foto-3.jpg', avaliacao_destino=4.9, popularidade_destino=5 WHERE id_destino=7;
UPDATE destinos SET estado_destino='AM', regiao_destino='norte', img_destino_2='assets/imagens/destinos/manaus/foto-2.jpg', img_destino_3='assets/imagens/destinos/manaus/foto-3.jpg', avaliacao_destino=4.7, popularidade_destino=4 WHERE id_destino=8;
UPDATE destinos SET estado_destino='SC', regiao_destino='sul', img_destino_2='assets/imagens/destinos/florianopolis/foto-2.jpg', img_destino_3='assets/imagens/destinos/florianopolis/foto-3.jpg', avaliacao_destino=4.8, popularidade_destino=5 WHERE id_destino=9;
UPDATE destinos SET estado_destino='PR', regiao_destino='sul', img_destino_2='assets/imagens/destinos/curitiba/foto-2.jpg', img_destino_3='assets/imagens/destinos/curitiba/foto-3.jpg', avaliacao_destino=4.5, popularidade_destino=4 WHERE id_destino=10;
UPDATE destinos SET estado_destino='PE', regiao_destino='nordeste', img_destino_2='assets/imagens/destinos/fernando-de-noronha/foto-2.png', img_destino_3='assets/imagens/destinos/fernando-de-noronha/foto-3.png', avaliacao_destino=4.9, popularidade_destino=5 WHERE id_destino=11;
UPDATE destinos SET estado_destino='MS', regiao_destino='centro-oeste', img_destino_2='assets/imagens/destinos/campo-grande/foto-2.jpg', img_destino_3='assets/imagens/destinos/campo-grande/foto-3.jpg', avaliacao_destino=4.4, popularidade_destino=3 WHERE id_destino=12;
UPDATE destinos SET estado_destino='CE', regiao_destino='nordeste', img_destino_2='assets/imagens/destinos/fortaleza/foto-2.jpg', img_destino_3='assets/imagens/destinos/fortaleza/foto-3.jpg', avaliacao_destino=4.7, popularidade_destino=5 WHERE id_destino=13;
UPDATE destinos SET estado_destino='GO', regiao_destino='centro-oeste', img_destino_2='assets/imagens/destinos/goiania/foto-2.jpg', img_destino_3='assets/imagens/destinos/goiania/foto-3.png', avaliacao_destino=4.3, popularidade_destino=3 WHERE id_destino=14;
UPDATE destinos SET estado_destino='CE', regiao_destino='nordeste', img_destino_2='assets/imagens/destinos/jericoacoara/foto-2.png', img_destino_3='assets/imagens/destinos/jericoacoara/foto-3.jpg', avaliacao_destino=4.9, popularidade_destino=5 WHERE id_destino=15;
UPDATE destinos SET estado_destino='RS', regiao_destino='sul', img_destino_2='assets/imagens/destinos/porto-alegre/foto-2.png', img_destino_3='assets/imagens/destinos/porto-alegre/foto-3.jpg', avaliacao_destino=4.5, popularidade_destino=4 WHERE id_destino=16;

INSERT INTO usuarios (nome, email, senha, tipo)
SELECT 'Administrador TopTurismo', 'admin@topturismo.com', '$2y$12$hdYIZXE1dZeJo366HcRjRuSth8z5Ndi/phMjYJf094B.APFKJiyNq', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE email='admin@topturismo.com');
