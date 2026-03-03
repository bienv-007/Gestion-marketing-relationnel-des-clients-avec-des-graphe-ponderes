-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 26 fév. 2026 à 11:46
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `db_mlm`
--

-- --------------------------------------------------------

--
-- Structure de la table `t_achats`
--

DROP TABLE IF EXISTS `t_achats`;
CREATE TABLE IF NOT EXISTS `t_achats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int DEFAULT NULL,
  `montant` float DEFAULT NULL,
  `date_achat` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `t_achats`
--

INSERT INTO `t_achats` (`id`, `client_id`, `montant`, `date_achat`) VALUES
(18, 17, 10, '2026-02-23'),
(19, 26, 21, '2026-02-23'),
(20, 27, 450, '2026-02-23'),
(21, 33, 34, '2026-02-25'),
(22, 29, 100, '2026-02-25'),
(23, 27, 200, '2026-02-25'),
(24, 17, 20, '2026-02-25'),
(25, 25, 34, '2026-02-25'),
(26, 17, 12, '2026-01-29'),
(27, 32, 10, '2026-02-25');

-- --------------------------------------------------------

--
-- Structure de la table `t_clients`
--

DROP TABLE IF EXISTS `t_clients`;
CREATE TABLE IF NOT EXISTS `t_clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `t_clients`
--

INSERT INTO `t_clients` (`id`, `nom`) VALUES
(32, 'eric'),
(31, 'janvier'),
(30, 'Liza'),
(29, 'Jojo'),
(28, 'gentil'),
(27, 'fanny'),
(26, 'vasima'),
(17, 'bienv'),
(33, 'Julien'),
(25, 'admin'),
(34, 'ananas');

-- --------------------------------------------------------

--
-- Structure de la table `t_relations`
--

DROP TABLE IF EXISTS `t_relations`;
CREATE TABLE IF NOT EXISTS `t_relations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parrain_id` int DEFAULT NULL,
  `filleuil_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filleuil_id` (`filleuil_id`),
  KEY `parrain_id` (`parrain_id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `t_relations`
--

INSERT INTO `t_relations` (`id`, `parrain_id`, `filleuil_id`) VALUES
(11, 17, 25),
(12, 25, 27),
(13, 30, 17),
(14, 17, 28),
(16, 26, 29),
(17, 33, 32),
(18, 25, 34),
(19, 17, 31);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
