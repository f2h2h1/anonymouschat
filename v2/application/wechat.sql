-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 服务器版本: 5.5.53
-- PHP 版本: 7.2.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `wechat`
--

-- --------------------------------------------------------

--
-- 表的结构 `anonymouschat`
--

CREATE TABLE IF NOT EXISTS `anonymouschat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` char(32) NOT NULL,
  `ghid` char(32) NOT NULL,
  `sex` tinyint(2) NOT NULL,
  `tag_sex` tinyint(2) NOT NULL,
  `tag_id` int(11) NOT NULL,
  `state` tinyint(2) NOT NULL,
  `update_time` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5775 ;

-- --------------------------------------------------------

--
-- 表的结构 `match_log`
--

CREATE TABLE IF NOT EXISTS `match_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(32) NOT NULL,
  `match_count` int(11) NOT NULL,
  `share_count` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1797 ;

-- --------------------------------------------------------

--
-- 表的结构 `openid2unionid`
--

CREATE TABLE IF NOT EXISTS `openid2unionid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(32) NOT NULL,
  `unionid` varchar(64) NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `openid` (`openid`),
  UNIQUE KEY `unionid` (`unionid`),
  UNIQUE KEY `unionid_2` (`unionid`),
  UNIQUE KEY `unionid_3` (`unionid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=327 ;

-- --------------------------------------------------------

--
-- 表的结构 `project_config`
--

CREATE TABLE IF NOT EXISTS `project_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config` varchar(4096) NOT NULL,
  `value` varchar(4096) NOT NULL,
  `update_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- 表的结构 `share_log`
--

CREATE TABLE IF NOT EXISTS `share_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unionid_form` varchar(64) NOT NULL DEFAULT '0',
  `unionid_to` varchar(64) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=329 ;

-- --------------------------------------------------------

--
-- 表的结构 `wechat_cache`
--

CREATE TABLE IF NOT EXISTS `wechat_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(128) NOT NULL,
  `value` varchar(512) NOT NULL,
  `expire_time` int(11) NOT NULL,
  `create_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_3` (`key`),
  KEY `key` (`key`),
  KEY `key_2` (`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=493 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
