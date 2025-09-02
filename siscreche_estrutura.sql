CREATE DATABASE  IF NOT EXISTS `siscreche` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `siscreche`;
-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: siscreche
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `contratuais`
--

DROP TABLE IF EXISTS `contratuais`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contratuais` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_iniciativa` int(11) NOT NULL,
  `processo_licitatorio` varchar(35) DEFAULT NULL,
  `empresa` varchar(35) DEFAULT NULL,
  `data_assinatura_contrato` date DEFAULT NULL,
  `dara_os` date DEFAULT NULL,
  `prazo_execucao_original` varchar(35) DEFAULT NULL,
  `prazo_execucao_atual` varchar(35) DEFAULT NULL,
  `valor_inicial_obra` int(11) DEFAULT NULL,
  `valor_aditivo_obra` decimal(15,2) DEFAULT NULL,
  `valor_total_obra` int(11) DEFAULT NULL,
  `valor_inicial_contrato` int(11) DEFAULT NULL,
  `valor_aditivo` int(11) DEFAULT NULL,
  `valor_contrato` int(11) DEFAULT NULL,
  `cod_subtracao` varchar(50) DEFAULT NULL,
  `secretaria_demandante` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fotos`
--

DROP TABLE IF EXISTS `fotos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fotos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `id_iniciativa` int(11) DEFAULT NULL,
  `caminho` varchar(255) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `data_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_fotos_usuarios` (`id_usuario`),
  KEY `fk_fotos_iniciativas` (`id_iniciativa`),
  CONSTRAINT `fk_fotos_iniciativas` FOREIGN KEY (`id_iniciativa`) REFERENCES `iniciativas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fotos_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `iniciativas`
--

DROP TABLE IF EXISTS `iniciativas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `iniciativas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `iniciativa` varchar(45) DEFAULT NULL,
  `data_vistoria` date DEFAULT NULL,
  `ib_status` varchar(45) DEFAULT NULL,
  `ib_execucao` varchar(45) DEFAULT NULL,
  `ib_previsto` varchar(45) DEFAULT NULL,
  `ib_variacao` varchar(45) DEFAULT NULL,
  `ib_valor_medio` varchar(20) DEFAULT NULL,
  `ib_secretaria` varchar(20) DEFAULT NULL,
  `ib_orgao` varchar(10) DEFAULT NULL,
  `ib_gestor_responsavel` varchar(45) DEFAULT NULL,
  `ib_fiscal` varchar(45) DEFAULT NULL,
  `ib_numero_processo_sei` varchar(110) DEFAULT NULL,
  `objeto` varchar(1500) DEFAULT NULL,
  `informacoes_gerais` varchar(1500) DEFAULT NULL,
  `observacoes` varchar(1500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_iniciativas_usuarios` (`id_usuario`),
  CONSTRAINT `fk_iniciativas_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `marcos`
--

DROP TABLE IF EXISTS `marcos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `marcos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_iniciativa` int(11) NOT NULL,
  `tipo_etapa` enum('linha','subtitulo') DEFAULT 'linha',
  `etapa` varchar(255) NOT NULL,
  `inicio_previsto` date DEFAULT NULL,
  `termino_previsto` date DEFAULT NULL,
  `inicio_real` date DEFAULT NULL,
  `termino_real` date DEFAULT NULL,
  `evolutivo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_iniciativa` (`id_iniciativa`),
  CONSTRAINT `marcos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `marcos_ibfk_2` FOREIGN KEY (`id_iniciativa`) REFERENCES `iniciativas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `medicoes`
--

DROP TABLE IF EXISTS `medicoes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `medicoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_iniciativa` int(11) NOT NULL,
  `valor_orcamento` decimal(15,2) DEFAULT NULL,
  `valor_bm` decimal(15,2) DEFAULT NULL,
  `saldo_obra` decimal(15,2) DEFAULT NULL,
  `bm` int(11) DEFAULT NULL,
  `data_inicio` date DEFAULT NULL,
  `data_fim` date DEFAULT NULL,
  `data_vistoria` date DEFAULT NULL,
  `data_registro` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_iniciativa` (`id_iniciativa`),
  CONSTRAINT `medicoes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  CONSTRAINT `medicoes_ibfk_2` FOREIGN KEY (`id_iniciativa`) REFERENCES `iniciativas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pendencias`
--

DROP TABLE IF EXISTS `pendencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pendencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `id_iniciativa` int(11) DEFAULT NULL,
  `problema` varchar(45) DEFAULT NULL,
  `contramedida` varchar(45) DEFAULT NULL,
  `prazo` date DEFAULT NULL,
  `responsavel` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pendencias_usuarios` (`id_usuario`),
  KEY `fk_pendencias_iniciativas` (`id_iniciativa`),
  CONSTRAINT `fk_pendencias_iniciativas` FOREIGN KEY (`id_iniciativa`) REFERENCES `iniciativas` (`id`),
  CONSTRAINT `fk_pendencias_usuarios` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'siscreche'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-05-16 16:01:42
