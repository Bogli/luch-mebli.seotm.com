-- phpMyAdmin SQL Dump
-- version 2.6.1
-- http://www.phpmyadmin.net
-- 
-- Хост: localhost
-- Время создания: Апр 11 2008 г., 15:33
-- Версия сервера: 5.0.18
-- Версия PHP: 5.2.1
-- 
-- БД: `kr_db`
-- 

-- --------------------------------------------------------

-- 
-- Структура таблицы `mod_search_result`
-- 

CREATE TABLE `mod_search_result` (
  `id` int(11) NOT NULL auto_increment,
  `query` varchar(255) NOT NULL default '',
  `ip` varchar(40) NOT NULL default '',
  `date` varchar(20) NOT NULL default '',
  `time` varchar(20) NOT NULL default '',
  `result` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=640 ;

-- 
-- Дамп данных таблицы `mod_search_result`
-- 


-- --------------------------------------------------------

-- 
-- Структура таблицы `mod_search_spr_txt`
-- 

CREATE TABLE `mod_search_spr_txt` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `cod` varchar(255) NOT NULL default '',
  `lang_id` int(11) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `move` int(11) unsigned default NULL,
  `img` varchar(255) default NULL,
  `short` varchar(255) default NULL,
  PRIMARY KEY  (`id`),
  KEY `cod` (`cod`,`lang_id`),
  KEY `move` (`move`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=103 ;

-- 
-- Дамп данных таблицы `mod_search_spr_txt`
-- 

INSERT INTO `mod_search_spr_txt` VALUES (1, 'FLD_ID', 1, 'Id', 0, '', '');
INSERT INTO `mod_search_spr_txt` VALUES (2, 'FLD_ID', 2, 'Ід.', 0, '', '');
INSERT INTO `mod_search_spr_txt` VALUES (3, 'FLD_ID', 3, 'Id', 0, NULL, NULL);
INSERT INTO `mod_search_spr_txt` VALUES (100, 'FLD_QUERY', 1, '', 4, '', '');
INSERT INTO `mod_search_spr_txt` VALUES (101, 'FLD_QUERY', 2, 'Запит', 4, '', '');
INSERT INTO `mod_search_spr_txt` VALUES (102, 'FLD_QUERY', 3, 'Посиковый запрос', 4, NULL, NULL);
INSERT INTO `mod_search_spr_txt` VALUES (16, 'FLD_DATE', 1, 'Date', 0, '', '');
INSERT INTO `mod_search_spr_txt` VALUES (17, 'FLD_DATE', 2, 'Дата', 0, '', '');
INSERT INTO `mod_search_spr_txt` VALUES (18, 'FLD_DATE', 3, 'Дата', 0, NULL, NULL);
INSERT INTO `mod_search_spr_txt` VALUES (99, 'FLD_RESULT', 3, 'Результат поиска', 3, NULL, NULL);
INSERT INTO `mod_search_spr_txt` VALUES (91, 'FLD_IP', 1, '', 1, '', '');
INSERT INTO `mod_search_spr_txt` VALUES (92, 'FLD_IP', 2, 'ИП', 1, '', '');
INSERT INTO `mod_search_spr_txt` VALUES (93, 'FLD_IP', 3, 'IP Адрес', 1, NULL, NULL);
INSERT INTO `mod_search_spr_txt` VALUES (94, 'FLD_TIME', 1, '', 2, '', '');
INSERT INTO `mod_search_spr_txt` VALUES (95, 'FLD_TIME', 2, 'Час', 2, '', '');
INSERT INTO `mod_search_spr_txt` VALUES (96, 'FLD_TIME', 3, 'Время', 2, NULL, NULL);
INSERT INTO `mod_search_spr_txt` VALUES (97, 'FLD_RESULT', 1, '', 3, '', '');
INSERT INTO `mod_search_spr_txt` VALUES (98, 'FLD_RESULT', 2, 'Результат', 3, '', '');
        