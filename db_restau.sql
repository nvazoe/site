-- Adminer 4.3.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `category_menu`;
CREATE TABLE `category_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `category_menu` (`id`, `name`) VALUES
(1,	'Africain'),
(2,	'Ailes de poulet'),
(3,	'Américain'),
(4,	'Asiatique'),
(5,	'Fast food'),
(6,	'Dessert');

DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_menu_id` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `price` double NOT NULL,
  `category_menu_id` int(11) DEFAULT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_7D053A93E4F686E7` (`type_menu_id`),
  KEY `IDX_7D053A93B0FA96C` (`category_menu_id`),
  KEY `IDX_7D053A93B1E7706E` (`restaurant_id`),
  CONSTRAINT `FK_7D053A93B0FA96C` FOREIGN KEY (`category_menu_id`) REFERENCES `category_menu` (`id`),
  CONSTRAINT `FK_7D053A93B1E7706E` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`id`),
  CONSTRAINT `FK_7D053A93E4F686E7` FOREIGN KEY (`type_menu_id`) REFERENCES `type_menu` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menu` (`id`, `type_menu_id`, `name`, `description`, `price`, `category_menu_id`, `restaurant_id`) VALUES
(1,	1,	'Formule poulet',	'Poulet fermier des landes avec un accompagnement au choix.',	12.3,	5,	3),
(2,	1,	'Formule porc',	'Travers de porc avec sauce et un accompagnement au choix.',	13.6,	NULL,	NULL),
(3,	1,	'Formule saucissse',	'Saucisse de porc coupée au couteau avec accompagnement au choix.',	13.6,	3,	NULL),
(4,	2,	'Pommes de terre sautées à la graisse de canard',	'200 gr',	4.3,	NULL,	NULL),
(5,	2,	'Gratin dauphinois',	'300 gr',	6.4,	NULL,	NULL),
(6,	3,	'Yaourt baskalia',	'Parfum a choix.',	3.5,	6,	NULL),
(7,	4,	'Coca cola zéro',	NULL,	3,	NULL,	NULL),
(8,	7,	'Château Les Roques',	NULL,	8,	NULL,	1),
(9,	5,	'Formule Bowl ou Bo Bun',	'Bowl ou bo bun et boisson soft au choix.',	14.5,	4,	NULL),
(10,	9,	'Yassa Mix \"bœuf et poulet\"',	'Sauce légèrement citronnée aux oignons, olives et à la moutarde, accompagnée d\'une base au choix et d\'un mixe de viandes marinées.',	11.9,	1,	3),
(14,	1,	'Dinde rotis',	'dal sds sd dso sdsls dson',	10,	2,	2),
(15,	NULL,	'Ndolè plantain',	'Malaxé de plantain avec de l\'huile rouge et la banane',	2000,	NULL,	NULL),
(16,	NULL,	'Couscous pain',	'couscous pain',	4000,	NULL,	2);

DROP TABLE IF EXISTS `menu_menu_option`;
CREATE TABLE `menu_menu_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) NOT NULL,
  `menu_option_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_E13D4624CCD7E912` (`menu_id`),
  KEY `IDX_E13D4624BE847A5B` (`menu_option_id`),
  CONSTRAINT `FK_E13D4624BE847A5B` FOREIGN KEY (`menu_option_id`) REFERENCES `menu_option` (`id`),
  CONSTRAINT `FK_E13D4624CCD7E912` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menu_menu_option` (`id`, `menu_id`, `menu_option_id`) VALUES
(1,	9,	1),
(2,	9,	2),
(3,	10,	3),
(4,	10,	2),
(5,	14,	5),
(6,	14,	3),
(7,	15,	6),
(8,	16,	7),
(9,	16,	8);

DROP TABLE IF EXISTS `menu_option`;
CREATE TABLE `menu_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menu_option` (`id`, `name`, `type`, `item`) VALUES
(1,	'Plat au choix',	'radio',	1),
(2,	'Boisson au choix',	'',	NULL),
(3,	'Base au choix',	'select',	2),
(5,	'Balance au chois',	'select',	3),
(6,	'Boisson',	'radio',	1),
(7,	'complements',	'select',	2),
(8,	'Sauce',	'radio',	1);

DROP TABLE IF EXISTS `menu_option_products`;
CREATE TABLE `menu_option_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_option_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `attribut` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_AFD705DABE847A5B` (`menu_option_id`),
  KEY `IDX_AFD705DA4584665A` (`product_id`),
  CONSTRAINT `FK_AFD705DA4584665A` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  CONSTRAINT `FK_AFD705DABE847A5B` FOREIGN KEY (`menu_option_id`) REFERENCES `menu_option` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menu_option_products` (`id`, `menu_option_id`, `product_id`, `attribut`) VALUES
(1,	1,	1,	'free'),
(2,	1,	2,	'free'),
(3,	1,	3,	'paid'),
(4,	1,	4,	'free'),
(6,	2,	6,	'paid'),
(7,	2,	5,	'free'),
(8,	3,	7,	'paid'),
(9,	3,	8,	'free'),
(10,	3,	9,	'free'),
(11,	5,	1,	'2'),
(12,	6,	8,	'0'),
(13,	7,	1,	'0'),
(14,	8,	1,	'0'),
(15,	8,	4,	'0');

DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` double NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `product` (`id`, `name`, `price`, `image`) VALUES
(1,	'Bangkok Bowl',	1,	NULL),
(2,	'Tokyo Bowl',	1,	NULL),
(3,	'Angkor Bowl',	1,	NULL),
(4,	'Vegan Bowl',	1,	NULL),
(5,	'Coca Cola',	3,	NULL),
(6,	'Coca Cola Zero',	3,	NULL),
(7,	'Riz long grain',	2,	NULL),
(8,	'Riz complet',	2,	NULL),
(9,	'Attieke (semoule de manioc fermentée)',	1,	NULL);

DROP TABLE IF EXISTS `restaurant`;
CREATE TABLE `restaurant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `restaurant` (`id`, `name`) VALUES
(1,	'BBQ Corean'),
(2,	'KFC'),
(3,	'Mc Donald');

DROP TABLE IF EXISTS `sf_user`;
CREATE TABLE `sf_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `roles` json NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `type_menu`;
CREATE TABLE `type_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `type_menu` (`id`, `name`) VALUES
(1,	'Rôtisserie'),
(2,	'Accompagnements'),
(3,	'Desserts'),
(4,	'Boissons'),
(5,	'Formule avec boisson'),
(6,	'Nems maison'),
(7,	'Vins'),
(8,	'Bowl'),
(9,	'Plat');

-- 2018-09-27 01:51:55
