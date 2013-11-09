-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2013 年 11 月 09 日 08:18
-- 服务器版本: 5.5.20
-- PHP 版本: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- 数据库: `ycms2`
--

-- --------------------------------------------------------

--
-- 表的结构 `y_article`
--

CREATE TABLE IF NOT EXISTS `y_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `channel_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `excerpt` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `sticky` int(11) NOT NULL DEFAULT '0',
  `promote` int(11) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `list` (`status`,`sticky`,`create_time`),
  KEY `frontpage` (`status`,`promote`,`sticky`,`create_time`),
  KEY `channel_id` (`channel_id`),
  KEY `update_time` (`update_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

-- --------------------------------------------------------

--
-- 表的结构 `y_article_meta`
--

CREATE TABLE IF NOT EXISTS `y_article_meta` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `object_id` int(11) unsigned NOT NULL,
  `meta_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `meta_value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_meta` (`object_id`,`meta_key`),
  KEY `post_id` (`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

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

--
-- 转存表中的数据 `y_authitem`
--

INSERT INTO `y_authitem` (`name`, `type`, `description`, `bizrule`, `data`) VALUES
('admin', 2, '管理员', NULL, 'N;'),
('Banner.*', 1, 'Banner管理', '', 'N;'),
('Channel.*', 1, '栏目管理', '', 'N;'),
('Content.*', 1, '内容管理', '', 'N;'),
('File.*', 1, '文件管理', '', 'N;'),
('Link.*', 1, '链接管理', '', 'N;'),
('Permission.*', 1, '权限管理', '', 'N;'),
('Role.*', 1, '角色管理', '', 'N;'),
('Site.Index', 1, '控制面板', '', 'N;'),
('site.setting', 1, '站点设置', '', 'N;'),
('Taxonomy.*', 1, '分类管理', '', 'N;'),
('Term.*', 1, '分类术语管理', '', 'N;'),
('User.*', 1, '用户管理', '', 'N;');

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

--
-- 转存表中的数据 `y_authitemchild`
--

INSERT INTO `y_authitemchild` (`parent`, `child`) VALUES
('admin', 'Channel.*'),
('admin', 'Content.*'),
('admin', 'File.*'),
('admin', 'Link.*'),
('admin', 'Permission.*'),
('admin', 'Role.*'),
('admin', 'Site.Index'),
('admin', 'site.setting'),
('admin', 'Taxonomy.*'),
('admin', 'Term.*'),
('admin', 'User.*');

-- --------------------------------------------------------

--
-- 表的结构 `y_banner`
--

CREATE TABLE IF NOT EXISTS `y_banner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `link_href` varchar(255) NOT NULL,
  `link_target` varchar(16) NOT NULL,
  `visible` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `weight` int(11) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `list` (`weight`,`name`),
  KEY `category` (`category_id`),
  KEY `visible` (`visible`),
  KEY `update_time` (`update_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `y_channel`
--

CREATE TABLE IF NOT EXISTS `y_channel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `model` varchar(64) NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  `weight` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `list` (`weight`,`title`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `y_file`
--

CREATE TABLE IF NOT EXISTS `y_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `meta` text,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `bundle` varchar(32) NOT NULL,
  `filename` varchar(255) NOT NULL DEFAULT '',
  `uri` varchar(255) NOT NULL DEFAULT '',
  `filemime` varchar(255) NOT NULL DEFAULT '',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `bundle` (`bundle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `y_file_usage`
--

CREATE TABLE IF NOT EXISTS `y_file_usage` (
  `file_id` int(11) NOT NULL DEFAULT '0',
  `object_id` int(11) NOT NULL DEFAULT '0',
  `bundle` varchar(32) NOT NULL DEFAULT '',
  `field` varchar(32) NOT NULL DEFAULT '',
  `weight` int(11) NOT NULL DEFAULT '0',
  `download_count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`file_id`),
  KEY `file_list` (`object_id`,`bundle`,`field`,`weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `y_link`
--

CREATE TABLE IF NOT EXISTS `y_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `link_href` varchar(255) NOT NULL,
  `link_target` varchar(16) NOT NULL,
  `visible` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `weight` int(11) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `visible` (`visible`),
  KEY `category_id` (`category_id`),
  KEY `list` (`weight`,`name`),
  KEY `update_time` (`update_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `y_node`
--

CREATE TABLE IF NOT EXISTS `y_node` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `channel_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `excerpt` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `sticky` int(11) NOT NULL DEFAULT '0',
  `promote` int(11) NOT NULL DEFAULT '0',
  `create_time` int(11) NOT NULL DEFAULT '0',
  `update_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `list` (`status`,`sticky`,`create_time`),
  KEY `frontpage` (`status`,`promote`,`sticky`,`create_time`),
  KEY `channel_id` (`channel_id`),
  KEY `update_time` (`update_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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

--
-- 转存表中的数据 `y_role`
--

INSERT INTO `y_role` (`name`, `description`, `weight`) VALUES
('admin', '管理员', 0);

-- --------------------------------------------------------

--
-- 表的结构 `y_setting`
--

CREATE TABLE IF NOT EXISTS `y_setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(64) NOT NULL DEFAULT 'system',
  `key` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_key` (`category`,`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- 转存表中的数据 `y_setting`
--

INSERT INTO `y_setting` (`id`, `category`, `key`, `value`) VALUES
(1, 'general', 'site_name', 'dsfsd'),
(2, 'general', 'site_keywords', 'dsf'),
(3, 'general', 'site_description', 'dfdfsfdssdfsdf');

-- --------------------------------------------------------

--
-- 表的结构 `y_taxonomy`
--

CREATE TABLE IF NOT EXISTS `y_taxonomy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `hierarchy` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `weight` int(11) NOT NULL DEFAULT '0',
  `model` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `name` (`slug`),
  KEY `list` (`weight`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- 转存表中的数据 `y_taxonomy`
--

INSERT INTO `y_taxonomy` (`id`, `name`, `slug`, `description`, `hierarchy`, `weight`, `model`) VALUES
(1, '标签', 'tags', '', 0, 0, ''),
(2, '新闻中心', 'news', '', 0, 0, '');

-- --------------------------------------------------------

--
-- 表的结构 `y_term`
--

CREATE TABLE IF NOT EXISTS `y_term` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `taxonomy_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `weight` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `taxonomy_tree` (`taxonomy_id`,`weight`,`name`),
  KEY `slug_taxonomy` (`slug`,`taxonomy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `y_term_hierarchy`
--

CREATE TABLE IF NOT EXISTS `y_term_hierarchy` (
  `term_id` int(10) unsigned NOT NULL DEFAULT '0',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_id`,`parent_id`),
  KEY `parent` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `y_term_object`
--

CREATE TABLE IF NOT EXISTS `y_term_object` (
  `bundle` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `term_id` int(11) unsigned NOT NULL,
  `create_time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`bundle`,`object_id`,`term_id`),
  KEY `term_object` (`bundle`,`term_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- 转存表中的数据 `y_user`
--

INSERT INTO `y_user` (`id`, `username`, `password`, `email`, `activation_key`, `nickname`, `status`, `create_time`, `update_time`, `login_time`) VALUES
(1, 'superadmin', '$2a$13$RZzPR8WMZ1Yys/ice6n0du1QY6mwpHs4r0kjZfFA6EyoUWHpQ/HjG', '261496560@qq.com', '', '超级管理员', 0, 1383525507, 1383979010, 1383979192);

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
