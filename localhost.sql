-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2018 �?12 �?05 �?08:46
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
CREATE DATABASE `wechat` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `wechat`;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5417 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=75 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
