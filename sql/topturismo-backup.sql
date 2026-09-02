-- TOPTURISMO - BANCO DE DADOS BASE
-- Banco de dados base para o funcionamento do sistema
--
-- IMPORTANTE:
-- 1. Este arquivo cria o banco do zero.
-- 2. Execute somente se puder apagar as tabelas atuais.
-- 3. O projeto NÃO precisa de uma tabela de programação:
--    os horários são definidos pelo PHP em programacao-dados.php.

CREATE DATABASE IF NOT EXISTS topturismo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE topturismo;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS reservas;
DROP TABLE IF EXISTS destinos;
DROP TABLE IF EXISTS usuarios;

SET FOREIGN_KEY_CHECKS = 1;

-- USUÁRIOS
CREATE TABLE usuarios (
    id INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(100) DEFAULT NULL,
    cpf VARCHAR(14) DEFAULT NULL,
    data_nascimento DATE DEFAULT NULL,
    genero VARCHAR(20) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    telefone VARCHAR(20) DEFAULT NULL,
    cidade VARCHAR(100) DEFAULT NULL,
    senha VARCHAR(255) DEFAULT NULL,
    chave_recuperacao_hash VARCHAR(255) DEFAULT NULL,
    tipo ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente',
    PRIMARY KEY (id),
    UNIQUE KEY uk_usuarios_email (email),
    UNIQUE KEY uk_usuarios_cpf (cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- DESTINOS
CREATE TABLE destinos (
    id_destino INT NOT NULL AUTO_INCREMENT,
    nome_destino VARCHAR(255) NOT NULL,
    descricao_destino TEXT NOT NULL,
    cidade_destino VARCHAR(255) NOT NULL,
    estado_destino VARCHAR(2) NOT NULL DEFAULT '',
    pais_destino VARCHAR(255) NOT NULL DEFAULT 'Brasil',
    regiao_destino VARCHAR(30) NOT NULL DEFAULT 'sudeste',
    img_destino VARCHAR(255) NOT NULL,
    img_destino_2 VARCHAR(255) DEFAULT NULL,
    img_destino_3 VARCHAR(255) DEFAULT NULL,
    preco_destino DECIMAL(10,2) NOT NULL,
    avaliacao_destino DECIMAL(2,1) NOT NULL DEFAULT 5.0,
    popularidade_destino TINYINT NOT NULL DEFAULT 3,
    PRIMARY KEY (id_destino)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO destinos
    (id_destino, nome_destino, descricao_destino, cidade_destino, estado_destino,
     pais_destino, regiao_destino, img_destino, img_destino_2, img_destino_3,
     preco_destino, avaliacao_destino, popularidade_destino)
VALUES
(1, 'Maceió', 'Praias de águas cristalinas, piscinas naturais e muito descanso no litoral de Alagoas.', 'Maceió', 'AL', 'Brasil', 'nordeste', 'assets/imagens/maceio.jpg', 'assets/imagens/destinos/maceio/foto-2.jpeg', 'assets/imagens/destinos/maceio/foto-3.jpg', 650.00, 4.8, 5),
(2, 'Rio de Janeiro', 'Cidade maravilhosa, com praias, montanhas e atrações famosas.', 'Rio de Janeiro', 'RJ', 'Brasil', 'sudeste', 'assets/imagens/rio-de-janeiro.jpg', 'assets/imagens/destinos/rio-de-janeiro/foto-2.jpg', 'assets/imagens/destinos/rio-de-janeiro/foto-3.jpg', 500.00, 4.8, 5),
(3, 'Salvador', 'Cultura, história, praias e gastronomia baiana em uma cidade cheia de energia.', 'Salvador', 'BA', 'Brasil', 'nordeste', 'assets/imagens/salvador.jpg', 'assets/imagens/destinos/salvador/foto-2.jpg', 'assets/imagens/destinos/salvador/foto-3.jpg', 550.00, 4.6, 5),
(4, 'Gramado', 'Destino turístico conhecido pelo clima europeu, gastronomia e atrações familiares.', 'Gramado', 'RS', 'Brasil', 'sul', 'assets/imagens/gramado.jpg', 'assets/imagens/destinos/gramado/foto-2.png', 'assets/imagens/destinos/gramado/foto-3.jpg', 700.00, 4.7, 4),
(5, 'São Paulo', 'A maior cidade do país reúne gastronomia, cultura, negócios, museus e vida noturna.', 'São Paulo', 'SP', 'Brasil', 'sudeste', 'assets/imagens/sao-paulo.jpg', 'assets/imagens/destinos/sao-paulo/foto-2.png', 'assets/imagens/destinos/sao-paulo/foto-3.png', 450.00, 4.5, 5),
(6, 'Foz do Iguaçu', 'Conheça as Cataratas do Iguaçu e uma das maiores experiências de natureza do Brasil.', 'Foz do Iguaçu', 'PR', 'Brasil', 'sul', 'assets/imagens/foz-do-iguacu.jpg', 'assets/imagens/destinos/foz-do-iguacu/foto-2.jpg', 'assets/imagens/destinos/foz-do-iguacu/foto-3.png', 600.00, 4.9, 5),
(7, 'Lençois Maranhenses', 'Dunas de areia branca e lagoas cristalinas formam uma paisagem única.', 'Barreirinhas', 'MA', 'Brasil', 'nordeste', 'assets/imagens/maranhao.jpg', 'assets/imagens/destinos/lencois-maranhenses/foto-2.jpg', 'assets/imagens/destinos/lencois-maranhenses/foto-3.jpg', 750.00, 4.9, 5),
(8, 'Manaus', 'Porta de entrada para a Amazônia, com natureza exuberante e experiências na floresta.', 'Manaus', 'AM', 'Brasil', 'norte', 'assets/imagens/amazonia.jpg', 'assets/imagens/destinos/manaus/foto-2.jpg', 'assets/imagens/destinos/manaus/foto-3.jpg', 800.00, 4.7, 4),
(9, 'Florianópolis', 'A Ilha da Magia combina praias, natureza, gastronomia e infraestrutura moderna.', 'Florianópolis', 'SC', 'Brasil', 'sul', 'assets/imagens/florianopolis.jpg', 'assets/imagens/destinos/florianopolis/foto-2.jpg', 'assets/imagens/destinos/florianopolis/foto-3.jpg', 500.00, 4.8, 5),
(10, 'Curitiba', 'Capital conhecida pelo planejamento urbano, parques, cultura e gastronomia.', 'Curitiba', 'PR', 'Brasil', 'sul', 'assets/imagens/curitiba.png', 'assets/imagens/destinos/curitiba/foto-2.jpg', 'assets/imagens/destinos/curitiba/foto-3.jpg', 500.00, 4.5, 4),
(11, 'Fernando de Noronha', 'Paraíso brasileiro com praias cristalinas, trilhas, mergulho e vida marinha.', 'Fernando de Noronha', 'PE', 'Brasil', 'nordeste', 'assets/imagens/fernando-de-nornoha.png', 'assets/imagens/destinos/fernando-de-noronha/foto-2.png', 'assets/imagens/destinos/fernando-de-noronha/foto-3.png', 1500.00, 4.9, 5),
(12, 'Campo Grande', 'Porta de entrada para o Pantanal, com natureza, cultura e gastronomia regional.', 'Campo Grande', 'MS', 'Brasil', 'centro-oeste', 'assets/imagens/campo-grande.png', 'assets/imagens/destinos/campo-grande/foto-2.jpg', 'assets/imagens/destinos/campo-grande/foto-3.jpg', 400.00, 4.4, 3),
(13, 'Fortaleza', 'A capital do sol encanta com praias, falésias, cultura e excelente gastronomia.', 'Fortaleza', 'CE', 'Brasil', 'nordeste', 'assets/imagens/Fortaleza.png', 'assets/imagens/destinos/fortaleza/foto-2.jpg', 'assets/imagens/destinos/fortaleza/foto-3.jpg', 700.00, 4.7, 5),
(14, 'Goiânia', 'Cidade arborizada que une boa gastronomia, parques, cultura e vida urbana.', 'Goiânia', 'GO', 'Brasil', 'centro-oeste', 'assets/imagens/Goiania.png', 'assets/imagens/destinos/goiania/foto-2.jpg', 'assets/imagens/destinos/goiania/foto-3.png', 250.00, 4.3, 3),
(15, 'Jericoacoara', 'Vilarejo paradisíaco cercado por dunas, lagoas e praias de águas tranquilas.', 'Jijoca de Jericoacoara', 'CE', 'Brasil', 'nordeste', 'assets/imagens/Jericoacoara.png', 'assets/imagens/destinos/jericoacoara/foto-2.png', 'assets/imagens/destinos/jericoacoara/foto-3.jpg', 750.00, 4.9, 5),
(16, 'Porto Alegre', 'A capital gaúcha reúne tradição, gastronomia, cultura e belas áreas ao ar livre.', 'Porto Alegre', 'RS', 'Brasil', 'sul', 'assets/imagens/porto-alegre.jpg', 'assets/imagens/destinos/porto-alegre/foto-2.png', 'assets/imagens/destinos/porto-alegre/foto-3.jpg', 700.00, 4.5, 4);

-- RESERVAS
CREATE TABLE reservas (
    id_reserva INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_destino INT NOT NULL,
    data_viagem DATE NOT NULL,
    data_volta DATE DEFAULT NULL,
    tipo_viagem VARCHAR(20) NOT NULL DEFAULT 'ida',
    quantidade_passageiros INT NOT NULL,
    transporte VARCHAR(100) NOT NULL,
    classe VARCHAR(30) DEFAULT NULL,
    assento VARCHAR(255) DEFAULT NULL,
    tipo_assento VARCHAR(60) DEFAULT NULL,
    pagamento VARCHAR(30) DEFAULT NULL,
    parcelas TINYINT NOT NULL DEFAULT 1,
    taxa_juros_percentual DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    horario_ida TIME DEFAULT NULL,
    horario_volta TIME DEFAULT NULL,
    duracao_voo_minutos INT DEFAULT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'confirmada',
    PRIMARY KEY (id_reserva),
    KEY idx_reservas_usuario (id_usuario),
    KEY idx_reservas_destino (id_destino),
    KEY idx_reservas_data (id_destino, data_viagem),
    CONSTRAINT fk_reserva_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_reserva_destino
        FOREIGN KEY (id_destino) REFERENCES destinos (id_destino)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- *o banco base começa sem usuários e sem reservas*
-- *os usuários são criados pelo formulário de cadastro*
-- *as reservas são criadas pelo formulário de reservas*

-- CONFERÊNCIA RÁPIDA
SELECT 'Banco TopTurismo criado com sucesso.' AS mensagem;

-- *base limpa: 0 usuários, 16 destinos e 0 reservas no início*
-- FIM
