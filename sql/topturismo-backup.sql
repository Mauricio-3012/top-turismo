-- Backup do banco TopTurismo corrigido para importação no phpMyAdmin
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `topturismo`;
USE `topturismo`;

-- Estrutura da tabela `usuarios`
-- Copiando estrutura para tabela topturismo.usuarios
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `genero` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cidade` varchar(100) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `tipo` enum('admin','cliente') DEFAULT 'cliente',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Estrutura da tabela `destinos`
-- Copiando estrutura para tabela topturismo.destinos
CREATE TABLE IF NOT EXISTS `destinos` (
  `id_destino` int NOT NULL AUTO_INCREMENT,
  `nome_destino` varchar(255) NOT NULL,
  `descricao_destino` text NOT NULL,
  `cidade_destino` varchar(255) NOT NULL,
  `pais_destino` varchar(255) NOT NULL,
  `img_destino` varchar(255) NOT NULL,
  `preco_destino` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_destino`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dados da tabela `destinos`
-- Copiando dados para a tabela topturismo.destinos: ~0 rows (aproximadamente)
INSERT INTO `destinos` (`id_destino`, `nome_destino`, `descricao_destino`, `cidade_destino`, `pais_destino`, `img_destino`, `preco_destino`) VALUES
	(1, 'Maceió', 'Praias de águas cristalinas, piscinas naturais e muito descanso no litoral de Alagoas.', 'Maceió', 'Brasil', 'assets/imagens/maceio.jpg', 650.00),
	(2, 'Rio de Janeiro', 'Cidade maravilhosa, com praias, montanhas e atrações famosas.', 'Rio de Janeiro', 'Brasil', 'assets/imagens/rio-de-janeiro.jpg', 500.00),
	(3, 'Salvador', 'Cultura, história, praias e gastronomia baiana em uma cidade cheia de energia.', 'Salvador', 'Brasil', 'assets/imagens/salvador.jpg', 550.00),
	(4, 'Gramado', 'Destino turístico conhecido pelo clima europeu, gastronomia e atrações familiares.', 'Gramado', 'Brasil', 'assets/imagens/gramado.jpg', 700.00),
	(5, 'São Paulo', 'A maior cidade do país reúne gastronomia, cultura, negócios, museus e vida noturna.', 'São Paulo', 'Brasil', 'assets/imagens/sao-paulo.jpg', 450.00),
	(6, 'Foz do Iguaçu', 'Conheça as Cataratas do Iguaçu e uma das maiores experiências de natureza do Brasil.', 'Foz do Iguaçu', 'Brasil', 'assets/imagens/foz-do-iguacu.jpg', 600.00),
	(7, 'Lençois Maranhenses', 'Dunas de areia branca e lagoas cristalinas formam uma paisagem única.', 'Barreirinhas', 'Brasil', 'assets/imagens/maranhao.jpg', 750.00),
	(8, 'Manaus', 'Porta de entrada para a Amazônia, com natureza exuberante e experiências na floresta.', 'Manaus', 'Brasil', 'assets/imagens/amazonia.jpg', 800.00),
	(9, 'Florianópolis', 'A Ilha da Magia combina praias, natureza, gastronomia e infraestrutura moderna.', 'Florianópolis', 'Brasil', 'assets/imagens/florianopolis.jpg', 500.00),
	(10, 'Curitiba', 'Capital conhecida pelo planejamento urbano, parques, cultura e gastronomia.', 'Curitiba', 'Brasil', 'assets/imagens/curitiba.png', 500.00),
	(11, 'Fernando de Noronha', 'Paraíso brasileiro com praias cristalinas, trilhas, mergulho e vida marinha.', 'Fernando de Noronha', 'Brasil', 'assets/imagens/fernando-de-nornoha.png', 1500.00),
	(12, 'Campo Grande', 'Porta de entrada para o Pantanal, com natureza, cultura e gastronomia regional.', 'Campo Grande', 'Brasil', 'assets/imagens/campo-grande.png', 400.00),
	(13, 'Fortaleza', 'A capital do sol encanta com praias, falésias, cultura e excelente gastronomia.', 'Fortaleza', 'Brasil', 'assets/imagens/Fortaleza.png', 700.00),
	(14, 'Goiânia', 'Cidade arborizada que une boa gastronomia, parques, cultura e vida urbana.', 'Goiânia', 'Brasil', 'assets/imagens/Goiania.png', 250.00),
	(15, 'Jericoacoara', 'Vilarejo paradisíaco cercado por dunas, lagoas e praias de águas tranquilas.', 'Jijoca de Jericoacoara', 'Brasil', 'assets/imagens/Jericoacoara.png', 750.00),
	(16, 'Porto Alegre', 'A capital gaúcha reúne tradição, gastronomia, cultura e belas áreas ao ar livre.', 'Porto Alegre', 'Brasil', 'assets/imagens/porto-alegre.jpg', 700.00);

-- Estrutura da tabela `reservas`
-- Copiando estrutura para tabela topturismo.reservas
CREATE TABLE IF NOT EXISTS `reservas` (
  `id_reserva` int NOT NULL AUTO_INCREMENT,
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
  `status` varchar(30) NOT NULL DEFAULT 'pendente',
  PRIMARY KEY (`id_reserva`),
  KEY `fk_reserva_usuario` (`id_usuario`),
  KEY `fk_reserva_destino` (`id_destino`),
  CONSTRAINT `fk_reserva_destino` FOREIGN KEY (`id_destino`) REFERENCES `destinos` (`id_destino`),
  CONSTRAINT `fk_reserva_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Outros comandos do backup original
-- --------------------------------------------------------
-- Servidor:                     127.0.0.1
-- Versão do servidor:           8.4.3 - MySQL Community Server - GPL
-- OS do Servidor:               Win64
-- HeidiSQL Versão:              12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Copiando estrutura do banco de dados para topturismo
CREATE DATABASE IF NOT EXISTS `topturismo` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `topturismo`;
-- Copiando dados para a tabela topturismo.reservas: ~2 rows (aproximadamente)
-- Copiando dados para a tabela topturismo.usuarios: ~0 rows (aproximadamente)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

SET FOREIGN_KEY_CHECKS = 1;