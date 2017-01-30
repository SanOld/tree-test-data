-- --------------------------------------------------------
-- Хост:                         127.0.0.1
-- Версия сервера:               5.5.48 - MySQL Community Server (GPL)
-- ОС Сервера:                   Win32
-- HeidiSQL Версия:              9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Дамп структуры для таблица test_ocad_tree_data.dictionary
CREATE TABLE IF NOT EXISTS `dictionary` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '0',
  `field_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_ocad_tree_data.dictionary: ~3 rows (приблизительно)
/*!40000 ALTER TABLE `dictionary` DISABLE KEYS */;
INSERT INTO `dictionary` (`id`, `title`, `field_id`, `user_id`) VALUES
	(7, 'Новое значение', 1, 1),
	(8, 'Оптовая цена 1 ДРАМ', 5, 1),
	(9, 'Оптовая цена 2 ДРАМ', 4, 1);
/*!40000 ALTER TABLE `dictionary` ENABLE KEYS */;


-- Дамп структуры для таблица test_ocad_tree_data.fields
CREATE TABLE IF NOT EXISTS `fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_ocad_tree_data.fields: ~8 rows (приблизительно)
/*!40000 ALTER TABLE `fields` DISABLE KEYS */;
INSERT INTO `fields` (`id`, `name`) VALUES
	(1, 'Наименование'),
	(2, 'Артикул'),
	(3, 'Производитель'),
	(4, 'Цена оптовая'),
	(5, 'Цена розничная'),
	(6, 'Валюта'),
	(7, 'Описание'),
	(8, 'Категория');
/*!40000 ALTER TABLE `fields` ENABLE KEYS */;


-- Дамп структуры для таблица test_ocad_tree_data.prices
CREATE TABLE IF NOT EXISTS `prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы test_ocad_tree_data.prices: ~1 rows (приблизительно)
/*!40000 ALTER TABLE `prices` DISABLE KEYS */;
INSERT INTO `prices` (`id`, `name`, `url`, `link`) VALUES
	(1, 'Axiomplus', 'http://axiomplus.com.ua/', 'https://axiomplus.com.ua/feed/platform/?code=prom.ua');
/*!40000 ALTER TABLE `prices` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
