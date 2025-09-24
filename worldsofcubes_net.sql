-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Июн 25 2015 г., 14:00
-- Версия сервера: 5.5.41-MariaDB-1ubuntu0.14.04.1
-- Версия PHP: 5.5.9-1ubuntu4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `worldsofcubes_net`
--

-- --------------------------------------------------------

--
-- Структура таблицы `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `login` char(32) DEFAULT NULL,
  `female` tinyint(1) NOT NULL DEFAULT '2',
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `deadtry` tinyint(1) DEFAULT '0',
  `email` varchar(50) DEFAULT NULL,
  `mojangmail` varchar(50) DEFAULT NULL,
  `password` char(32) DEFAULT NULL,
  `tmp` char(32) NOT NULL DEFAULT '0',
  `ip` varchar(16) DEFAULT NULL,
  `group` int(10) NOT NULL DEFAULT '1',
  `comments_num` int(10) NOT NULL DEFAULT '0',
  `vote` int(10) NOT NULL DEFAULT '0',
  `gameplay_last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active_last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `play_times` int(10) NOT NULL DEFAULT '0',
  `undress_times` int(10) NOT NULL DEFAULT '0',
  `default_skin` tinyint(1) NOT NULL DEFAULT '1',
  `session` varchar(255) DEFAULT NULL,
  `clientToken` varchar(255) DEFAULT NULL,
  `server` varchar(255) DEFAULT NULL,
  `warn_lvl` smallint(10) DEFAULT '0',
  `topics` smallint(10) DEFAULT '0',
  `posts` smallint(10) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Login` (`login`),
  KEY `group_id` (`group`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=235 ;

-- --------------------------------------------------------

--
-- Структура таблицы `action_log`
--

CREATE TABLE IF NOT EXISTS `action_log` (
  `IP` varchar(16) NOT NULL,
  `first_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `query_count` int(10) NOT NULL DEFAULT '1',
  `info` varchar(255) NOT NULL,
  PRIMARY KEY (`IP`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `antiflood`
--

CREATE TABLE IF NOT EXISTS `antiflood` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` char(50) NOT NULL,
  `hour` char(50) NOT NULL,
  `num` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;
-- --------------------------------------------------------

--
-- Структура таблицы `banlist`
--

CREATE TABLE IF NOT EXISTS `banlist` (
  `name` varchar(32) NOT NULL,
  `reason` text NOT NULL,
  `admin` varchar(32) NOT NULL,
  `time` datetime NOT NULL,
  `temptime` date NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `name_2` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Структура таблицы `banlistip`
--

CREATE TABLE IF NOT EXISTS `banlistip` (
  `name` varchar(32) NOT NULL,
  `lastip` tinytext NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `message` varchar(255) NOT NULL,
  `time` datetime DEFAULT NULL,
  `item_type` smallint(3) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `uniq_item` (`item_id`,`item_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;

-- --------------------------------------------------------

--
-- Структура таблицы `data`
--

CREATE TABLE IF NOT EXISTS `data` (
  `property` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  UNIQUE KEY `property` (`property`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `data`
--

INSERT INTO `data` (`property`, `value`) VALUES
('email-mail', 'noreply@worldsofcubes.net'),
('email-name', 'WorldsOfCubes Team'),
('latest-game-build', '10746'),
('game-link-osx', '2.0b41'),
('launcher-version', '13'),
('next-reg-time', '4'),
('email-verification', '1'),
('rcon-port', '0'),
('rcon-pass', '0'),
('rcon-serv', '0'),
('smtp-user', 'noreply@worldsofcubes.net'),
('smtp-pass', ''),
('smtp-host', ''),
('smtp-port', '465'),
('smtp-hello', 'HELO'),
('game-link-win', '1.235'),
('game-link-lin', '2.0'),
('email-verification-salt', ''),
('latest-update-date', '1435254803'),
('latest-version-name', '2.1 build3'),
('latest-version-tag', '2.1b3'),
('stable-update-date', '1435254803'),
('stable-version-name', '2.0 R3'),
('stable-version-tag', '2.0_r3'),
('protection-key', '');

-- --------------------------------------------------------

--
-- Структура таблицы `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_word` char(255) DEFAULT NULL,
  `user_id` bigint(20) NOT NULL,
  `way` char(255) DEFAULT NULL,
  `name` char(255) DEFAULT NULL,
  `dislikes` int(10) DEFAULT '0',
  `likes` int(10) DEFAULT '0',
  `downloads` int(10) DEFAULT '0',
  `size` char(32) DEFAULT '0',
  `hash` char(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Структура таблицы `forum_messages`
--

CREATE TABLE IF NOT EXISTS `forum_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `partition_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `date` int(11) NOT NULL,
  `topmsg` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `forum_partition`
--

CREATE TABLE IF NOT EXISTS `forum_partition` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT '0',
  `priority` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `forum_topics`
--

CREATE TABLE IF NOT EXISTS `forum_topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `partition_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `date` int(11) NOT NULL,
  `top` char(1) NOT NULL DEFAULT 'N',
  `closed` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` char(64) NOT NULL,
  `pex_name` char(64) NOT NULL,
  `lvl` int(10) NOT NULL DEFAULT '1',
  `system` tinyint(1) NOT NULL DEFAULT '0',
  `change_skin` tinyint(1) NOT NULL DEFAULT '0',
  `change_pass` tinyint(1) NOT NULL DEFAULT '0',
  `change_login` tinyint(1) NOT NULL DEFAULT '0',
  `change_cloak` tinyint(1) NOT NULL DEFAULT '0',
  `add_news` tinyint(1) NOT NULL DEFAULT '0',
  `add_comm` tinyint(1) NOT NULL DEFAULT '0',
  `adm_comm` tinyint(1) NOT NULL DEFAULT '0',
  `max_fsize` int(10) NOT NULL DEFAULT '20',
  `max_ratio` int(10) NOT NULL DEFAULT '1',
  `sp_upload` tinyint(1) NOT NULL DEFAULT '0',
  `sp_change` tinyint(1) NOT NULL DEFAULT '0',
  `change_prefix` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=104 ;

--
-- Дамп данных таблицы `groups`
--

INSERT INTO `groups` (`id`, `name`, `pex_name`, `lvl`, `system`, `change_skin`, `change_pass`, `change_login`, `change_cloak`, `add_news`, `add_comm`, `adm_comm`, `max_fsize`, `max_ratio`, `sp_upload`, `sp_change`, `change_prefix`) VALUES
(101, 'Технический пользователь', '', 0, 0, 1, 0, 0, 1, 0, 0, 0, 8192, 32, 0, 0, 0),
(100, 'Модератор системы', '', 8, 0, 1, 1, 0, 1, 0, 1, 1, 8192, 32, 1, 1, 0),
(4, 'Непроверенный', '', 1, 1, 0, 0, 0, 0, 0, 0, 0, 20, 1, 0, 0, 0),
(3, 'Администратор', '', 15, 1, 1, 1, 1, 1, 1, 1, 1, 8192, 32, 1, 1, 0),
(2, 'Заблокированный', '', 0, 1, 0, 0, 0, 0, 0, 0, 0, 20, 32, 0, 0, 0),
(1, 'Пользователь', '', 2, 1, 1, 1, 0, 1, 0, 1, 0, 8192, 10, 1, 1, 0),
(102, 'Разработчик', '', 15, 1, 1, 1, 1, 1, 1, 1, 1, 8192, 32, 1, 1, 0),
(103, 'Демо пользователь', '', 2, 1, 1, 0, 0, 1, 0, 1, 0, 8192, 10, 1, 1, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `iconomy`
--

CREATE TABLE IF NOT EXISTS `iconomy` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `realmoney` double(64,2) NOT NULL DEFAULT '0.00',
  `balance` double(64,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=130 ;

-- --------------------------------------------------------

--
-- Структура таблицы `ip_banning`
--

CREATE TABLE IF NOT EXISTS `ip_banning` (
  `IP` varchar(16) NOT NULL,
  `time_start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ban_until` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ban_type` tinyint(1) NOT NULL DEFAULT '1',
  `reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IP`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `likes`
--

CREATE TABLE IF NOT EXISTS `likes` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `item_type` smallint(3) NOT NULL DEFAULT '1',
  `var` tinyint(1) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=73 ;

-- --------------------------------------------------------

--
-- Структура таблицы `news`
--

CREATE TABLE IF NOT EXISTS `news` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `category_id` int(10) NOT NULL DEFAULT '1',
  `user_id` bigint(20) NOT NULL,
  `dislikes` int(10) DEFAULT '0',
  `likes` int(10) DEFAULT '0',
  `vote` tinyint(1) NOT NULL DEFAULT '1',
  `hits` int(10) DEFAULT '0',
  `title` char(255) NOT NULL,
  `message` text NOT NULL,
  `message_full` mediumtext NOT NULL,
  `time` datetime DEFAULT NULL,
  `discus` tinyint(1) NOT NULL DEFAULT '1',
  `comments` int(10) NOT NULL DEFAULT '0',
  `hide_vote` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=41 ;

-- --------------------------------------------------------

--
-- Структура таблицы `news_categorys`
--

CREATE TABLE IF NOT EXISTS `news_categorys` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` char(255) NOT NULL,
  `description` char(255) NOT NULL,
  `priority` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Дамп данных таблицы `news_categorys`
--

INSERT INTO `news_categorys` (`id`, `name`, `description`, `priority`) VALUES
(1, 'Без категории', '', 1),
(2, 'Новости проекта', '', 10000),
(3, 'Владельцам проектов', '', 15),
(4, 'Обновления системы', '', 20);

-- --------------------------------------------------------

--
-- Структура таблицы `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `permission` varchar(200) NOT NULL,
  `world` varchar(50) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `permissions_entity`
--

CREATE TABLE IF NOT EXISTS `permissions_entity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `prefix` varchar(255) NOT NULL,
  `suffix` varchar(255) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `default` (`default`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `permissions_inheritance`
--

CREATE TABLE IF NOT EXISTS `permissions_inheritance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `child` varchar(50) NOT NULL,
  `parent` varchar(50) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `world` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `child` (`child`,`parent`,`type`,`world`),
  KEY `child_2` (`child`,`type`),
  KEY `parent` (`parent`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `pm`
--

CREATE TABLE IF NOT EXISTS `pm` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `sender` char(32) DEFAULT NULL,
  `reciver` char(32) DEFAULT NULL,
  `viewed` int(11) NOT NULL DEFAULT '0',
  `topic` char(255) DEFAULT NULL,
  `text` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=703 ;

-- --------------------------------------------------------

--
-- Структура таблицы `reqests`
--

CREATE TABLE IF NOT EXISTS `reqests` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `realname` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `surname` varchar(255) NOT NULL,
  `old` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `skype` varchar(20) NOT NULL,
  `answer` varchar(5) NOT NULL DEFAULT '1',
  `comment` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `servers`
--

CREATE TABLE IF NOT EXISTS `servers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `last_update` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `online` tinyint(1) DEFAULT '0',
  `rcon` varchar(255) DEFAULT '',
  `service_user` char(64) DEFAULT NULL,
  `players` text,
  `method` tinyint(1) DEFAULT '0',
  `address` varchar(255) DEFAULT NULL,
  `port` int(10) DEFAULT '25565',
  `name` varchar(255) DEFAULT NULL,
  `info` char(255) DEFAULT NULL,
  `numpl` char(32) DEFAULT NULL,
  `slots` char(32) DEFAULT NULL,
  `main_page` tinyint(1) DEFAULT '0',
  `news_page` tinyint(1) DEFAULT '0',
  `stat_page` tinyint(1) DEFAULT '0',
  `priority` tinyint(1) DEFAULT '0',
  `main` tinyint(1) DEFAULT '0',
  `refresh_time` smallint(3) NOT NULL DEFAULT '5',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `shop_cart`
--

CREATE TABLE IF NOT EXISTS `shop_cart` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `user` varchar(80) NOT NULL,
  `iid` text,
  `key` varchar(80) NOT NULL,
  `date` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `shop_cats`
--

CREATE TABLE IF NOT EXISTS `shop_cats` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `priority` bigint(20) NOT NULL DEFAULT '0',
  `description` text NOT NULL,
  `system` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Дамп данных таблицы `shop_cats`
--

INSERT INTO `shop_cats` (`id`, `title`, `url`, `priority`, `description`, `system`) VALUES
(1, 'Прочее', 'other', -100, 'Некатегоризированные товары', 1),
(2, 'Донат', 'donate', 8, 'Модули, добавляющие функции монетизации', 0),
(3, 'Расширение функциональности', 'lvlupping', 7, 'Модули, расширяющие функционал продуктов', 0),
(4, 'Темы оформления', 'templates', 6, 'Красивое оформление для продуктов', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `shop_items`
--

CREATE TABLE IF NOT EXISTS `shop_items` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `infinite` tinyint(4) DEFAULT '1',
  `title` varchar(255) NOT NULL,
  `author` bigint(20) NOT NULL,
  `cid` bigint(20) NOT NULL DEFAULT '1',
  `pic` varchar(255) NOT NULL DEFAULT '/style/shop/img/missing_texture.png',
  `description` text NOT NULL,
  `price` double(64,2) NOT NULL,
  `discount` double(64,2) NOT NULL DEFAULT '0.00',
  `num` int(10) NOT NULL DEFAULT '1',
  `server` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `shop_keys`
--

CREATE TABLE IF NOT EXISTS `shop_keys` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `key` varchar(80) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `price` double(64,2) NOT NULL,
  `realprice` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Url` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `shop_keys_log`
--

CREATE TABLE IF NOT EXISTS `shop_keys_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `kid` bigint(20) NOT NULL,
  `pid` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `shop_servers`
--

CREATE TABLE IF NOT EXISTS `shop_servers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `pic` varchar(255) NOT NULL DEFAULT '/style/shop/img/missing_texture.png',
  `url` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Дамп данных таблицы `shop_servers`
--

INSERT INTO `shop_servers` (`id`, `title`, `pic`, `url`, `description`) VALUES
(1, 'Официальные движки и лончеры', '/style/Default/shop/img/missing_texture.png', 'official_engines', 'Официальные движки WorldsOfCubes Team'),
(2, 'Официальные модули', '/style/Default/shop/img/missing_texture.png', 'official_addons', 'Дополнения к продуктам от разработчиков.');

-- --------------------------------------------------------

--
-- Структура таблицы `sp_bad_skins`
--

CREATE TABLE IF NOT EXISTS `sp_bad_skins` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `hash` char(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- Структура таблицы `sp_skins`
--

CREATE TABLE IF NOT EXISTS `sp_skins` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT '0',
  `name` char(255) DEFAULT NULL,
  `fname` char(255) DEFAULT NULL,
  `dislikes` int(10) DEFAULT '0',
  `likes` int(10) DEFAULT '0',
  `downloads` int(10) DEFAULT '0',
  `ratio` smallint(3) NOT NULL DEFAULT '1',
  `gender` tinyint(1) NOT NULL DEFAULT '2',
  `fsize` char(32) DEFAULT '0',
  `comments` int(10) NOT NULL DEFAULT '0',
  `comment_last` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `hash` char(32) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `gender` (`gender`),
  KEY `skin_spec` (`gender`,`ratio`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Структура таблицы `sp_skins_ratio`
--

CREATE TABLE IF NOT EXISTS `sp_skins_ratio` (
  `ratio` int(10) NOT NULL DEFAULT '0',
  `num` int(10) DEFAULT '1',
  PRIMARY KEY (`ratio`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `unbans`
--

CREATE TABLE IF NOT EXISTS `unbans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) DEFAULT NULL,
  `numofban` varchar(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `warnings`
--

CREATE TABLE IF NOT EXISTS `warnings` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL,
  `mid` bigint(20) NOT NULL,
  `percentage` int(11) NOT NULL,
  `reason` text NOT NULL,
  `time` datetime NOT NULL,
  `expires` date NOT NULL,
  `type` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `warnings`
--

INSERT INTO `warnings` (`id`, `uid`, `mid`, `percentage`, `reason`, `time`, `expires`, `type`) VALUES
(1, 36, 2, 100, 'Демо пользователю запрещено оставлять комменты/писать на форуме/писать ЛС', '2015-06-01 20:58:20', '2022-12-31', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `woc_projects`
--

CREATE TABLE IF NOT EXISTS `woc_projects` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user` tinytext NOT NULL,
  `hits` int(10) DEFAULT '0',
  `name` char(255) NOT NULL,
  `url` char(255) NOT NULL,
  `path` char(255) NOT NULL,
  `about` text NOT NULL,
  `security_key` tinytext NOT NULL,
  `time` datetime NOT NULL,
  `in_develop` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1041 ;

-- --------------------------------------------------------

--
-- Структура таблицы `woc_projects_players`
--

CREATE TABLE IF NOT EXISTS `woc_projects_players` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `uid` bigint(20) NOT NULL DEFAULT '0',
  `pid` bigint(20) NOT NULL DEFAULT '0',
  `hide_dialog` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=106 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
