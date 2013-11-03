-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2013 年 11 月 03 日 23:08
-- 服务器版本: 5.5.34
-- PHP 版本: 5.3.10-1ubuntu3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- 数据库: `ycms2`
--

-- --------------------------------------------------------

--
-- 表的结构 `y_authassignment`
--

CREATE TABLE IF NOT EXISTS `y_authassignment` (
  `itemname` varchar(64) NOT NULL,
  `userid` varchar(64) NOT NULL,
  `bizrule` text,
  `data` text,
  PRIMARY KEY (`itemname`,`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `y_authitem`
--

CREATE TABLE IF NOT EXISTS `y_authitem` (
  `name` varchar(64) NOT NULL,
  `type` int(11) NOT NULL,
  `description` text,
  `bizrule` text,
  `data` text,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `y_authitemchild`
--

CREATE TABLE IF NOT EXISTS `y_authitemchild` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `y_role`
--

CREATE TABLE IF NOT EXISTS `y_role` (
  `name` varchar(64) NOT NULL,
  `description` varchar(255) NOT NULL,
  `weight` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`),
  KEY `list` (`weight`,`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `y_user`
--

CREATE TABLE IF NOT EXISTS `y_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `password` varchar(128) NOT NULL,
  `email` varchar(128) NOT NULL,
  `activation_key` varchar(128) NOT NULL DEFAULT '',
  `nickname` varchar(16) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `login_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- 限制导出的表
--

--
-- 限制表 `y_authassignment`
--
ALTER TABLE `y_authassignment`
  ADD CONSTRAINT `y_authassignment_ibfk_1` FOREIGN KEY (`itemname`) REFERENCES `y_authitem` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- 限制表 `y_authitemchild`
--
ALTER TABLE `y_authitemchild`
  ADD CONSTRAINT `y_authitemchild_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `y_authitem` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `y_authitemchild_ibfk_2` FOREIGN KEY (`child`) REFERENCES `y_authitem` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

