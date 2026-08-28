-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Tempo de geração: 26/08/2026 às 19:51
-- Versão do servidor: 8.0.44
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `topturismo`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `destinos`
--

CREATE TABLE `destinos` (
  `id_destino` int NOT NULL,
  `nome_destino` varchar(255) NOT NULL,
  `descricao_destino` text NOT NULL,
  `cidade_destino` varchar(255) NOT NULL,
  `pais_destino` varchar(255) NOT NULL,
  `estado_destino` varchar(2) NOT NULL DEFAULT '',
  `regiao_destino` varchar(30) NOT NULL DEFAULT 'sudeste',
  `img_destino` varchar(255) NOT NULL,
  `img_destino_2` varchar(255) DEFAULT NULL,
  `img_destino_3` varchar(255) DEFAULT NULL,
  `preco_destino` decimal(10,2) NOT NULL,
  `avaliacao_destino` decimal(2,1) NOT NULL DEFAULT 5.0,
  `popularidade_destino` tinyint NOT NULL DEFAULT 3
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `destinos`
--

INSERT INTO `destinos` (`id_destino`, `nome_destino`, `descricao_destino`, `cidade_destino`, `estado_destino`, `pais_destino`, `regiao_destino`, `img_destino`, `img_destino_2`, `img_destino_3`, `preco_destino`, `avaliacao_destino`, `popularidade_destino`) VALUES
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

-- --------------------------------------------------------

--
-- Estrutura para tabela `reservas`
--

CREATE TABLE `reservas` (
  `id_reserva` int NOT NULL,
  `id_usuario` int NOT NULL,
  `id_destino` int NOT NULL,
  `data_viagem` date NOT NULL,
  `data_volta` date DEFAULT NULL,
  `tipo_viagem` varchar(20) NOT NULL DEFAULT 'ida',
  `quantidade_passageiros` int NOT NULL,
  `transporte` varchar(100) NOT NULL,
  `classe` varchar(30) DEFAULT NULL,
  `assento` varchar(255) DEFAULT NULL,
  `tipo_assento` varchar(60) DEFAULT NULL,
  `pagamento` varchar(30) DEFAULT NULL,
  `parcelas` tinyint NOT NULL DEFAULT 1,
  `taxa_juros_percentual` decimal(5,2) NOT NULL DEFAULT 0.00,
  `horario_ida` time DEFAULT NULL,
  `horario_volta` time DEFAULT NULL,
  `duracao_voo_minutos` int DEFAULT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `genero` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `tipo` enum('admin','cliente') DEFAULT 'cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `cpf`, `data_nascimento`, `genero`, `email`, `telefone`, `cidade`, `senha`, `tipo`) VALUES
(8, 'Top Turismo', '22509975609', '2001-01-01', 'Masculino', 'contato@topturismo.com', '6140028922', 'Brazlandia DF', '$2y$10$n7HgfZMPs2X5y3IYk0703uMvqFRxtUfAePLo4lsIvPaQGIY1Je21e', 'cliente');

-- Conta administrativa inicial: admin@topturismo.com / Admin@123
INSERT INTO `usuarios` (`id`, `nome`, `cpf`, `data_nascimento`, `genero`, `email`, `telefone`, `cidade`, `senha`, `tipo`) VALUES
(9, 'Administrador TopTurismo', NULL, NULL, NULL, 'admin@topturismo.com', NULL, NULL, '$2y$12$hdYIZXE1dZeJo366HcRjRuSth8z5Ndi/phMjYJf094B.APFKJiyNq', 'admin');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `destinos`
--
ALTER TABLE `destinos`
  ADD PRIMARY KEY (`id_destino`);

--
-- Índices de tabela `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id_reserva`),
  ADD KEY `fk_reserva_usuario` (`id_usuario`),
  ADD KEY `fk_reserva_destino` (`id_destino`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `destinos`
--
ALTER TABLE `destinos`
  MODIFY `id_destino` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id_reserva` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_reserva_destino` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id_destino`),
  ADD CONSTRAINT `fk_reserva_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
