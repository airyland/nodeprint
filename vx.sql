-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- 主机: 127.0.0.1
-- 生成日期: 2012 年 12 月 25 日 08:50
-- 服务器版本: 5.5.27
-- PHP 版本: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `vx`
--

-- --------------------------------------------------------

--
-- 表的结构 `vx_comment`
--

CREATE TABLE IF NOT EXISTS `vx_comment` (
  `cm_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '评论id',
  `cm_content` text NOT NULL COMMENT '评论内容',
  `user_id` bigint(20) NOT NULL COMMENT '用户id',
  `user_name` varchar(100) NOT NULL,
  `cm_reply_to` bigint(20) NOT NULL,
  `cm_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_id` bigint(20) NOT NULL,
  `cm_reply_name` varchar(100) NOT NULL,
  `cm_reply_id` bigint(20) NOT NULL,
  `cm_other` text NOT NULL,
  PRIMARY KEY (`cm_id`),
  UNIQUE KEY `cm_id` (`cm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_config`
--

CREATE TABLE IF NOT EXISTS `vx_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- 转存表中的数据 `vx_config`
--

INSERT INTO `vx_config` (`id`, `name`, `value`) VALUES
(1, 'name', 'NodePrint'),
(2, 'description', 'A lightweight BBS '),
(3, 'keyword', 'bbs'),
(4, 'url', 'http://localhost/'),
(6, 'lang', 'zh'),
(7, 'topic_no', '15'),
(8, 'local_upload', '0'),
(9, 'show_status', '0'),
(10, 'auto_backup', '1');

-- --------------------------------------------------------

--
-- 表的结构 `vx_follow`
--

CREATE TABLE IF NOT EXISTS `vx_follow` (
  `f_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `f_keyid` bigint(20) NOT NULL,
  `f_keyname` varchar(200) NOT NULL,
  `f_subject` text,
  `f_type` tinyint(1) NOT NULL,
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_message`
--

CREATE TABLE IF NOT EXISTS `vx_message` (
  `m_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `m_type` tinyint(1) NOT NULL,
  `m_to_username` varchar(100) NOT NULL,
  `m_from_username` varchar(100) NOT NULL,
  `m_reply_to` bigint(20) NOT NULL DEFAULT '0',
  `m_subject` longtext NOT NULL,
  `m_read` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`m_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_meta`
--

CREATE TABLE IF NOT EXISTS `vx_meta` (
  `mid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '项目主键',
  `name` varchar(200) DEFAULT NULL COMMENT '名称',
  `slug` varchar(200) DEFAULT NULL COMMENT '项目缩略名',
  `type` varchar(32) NOT NULL COMMENT '项目类型',
  `description` varchar(200) DEFAULT NULL COMMENT '选项描述',
  `count` int(10) unsigned DEFAULT '0' COMMENT '项目所属内容个数',
  `order` int(10) unsigned DEFAULT '0' COMMENT '项目排序',
  PRIMARY KEY (`mid`),
  KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_node`
--

CREATE TABLE IF NOT EXISTS `vx_node` (
  `node_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `node_type` tinyint(1) NOT NULL,
  `node_name` varchar(20) NOT NULL,
  `node_slug` varchar(20) NOT NULL,
  `node_onindex` tinyint(1) NOT NULL DEFAULT '1',
  `node_icon` varchar(20) NOT NULL,
  `node_parent` bigint(20) NOT NULL,
  `node_related` varchar(100) NOT NULL,
  `node_intro` text,
  `node_post_no` bigint(20) NOT NULL DEFAULT '0',
  `node_ad` text,
  `node_css` text,
  PRIMARY KEY (`node_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- 转存表中的数据 `vx_node`
--

INSERT INTO `vx_node` (`node_id`, `node_type`, `node_name`, `node_slug`, `node_onindex`, `node_icon`, `node_parent`, `node_related`, `node_intro`, `node_post_no`, `node_ad`, `node_css`) VALUES
(1, 1, '测试父节点', 'test', 1, '', 0, '', '0', 0, NULL, NULL),
(2, 2, '测试子节点', 'subtest', 1, '', 1, '', '0', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `vx_oauth`
--

CREATE TABLE IF NOT EXISTS `vx_oauth` (
  `o_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `o_type` tinyint(1) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `o_token` varchar(100) NOT NULL,
  `o_openid` varchar(100) NOT NULL,
  PRIMARY KEY (`o_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_page`
--

CREATE TABLE IF NOT EXISTS `vx_page` (
  `page_id` int(11) NOT NULL AUTO_INCREMENT,
  `page_slug` varchar(200) NOT NULL,
  `page_title` varchar(100) NOT NULL,
  `page_md_content` int(11) NOT NULL,
  `page_last_edit` varchar(20) NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_post`
--

CREATE TABLE IF NOT EXISTS `vx_post` (
  `post_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_type` tinyint(1) NOT NULL DEFAULT '1',
  `post_title` varchar(200) NOT NULL,
  `post_content` longtext NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `post_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_comment_no` int(11) NOT NULL DEFAULT '0',
  `post_last_comment` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `node_id` int(11) NOT NULL,
  `post_hit` bigint(20) NOT NULL DEFAULT '0',
  `post_last_comment_author` varchar(100) DEFAULT NULL,
  `post_up` bigint(20) NOT NULL DEFAULT '0',
  `post_down` bigint(20) NOT NULL DEFAULT '0',
  `post_close` tinyint(1) NOT NULL DEFAULT '0',
  `post_weight` tinyint(2) NOT NULL DEFAULT '100',
  `post_fav_no_cache` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_rate`
--

CREATE TABLE IF NOT EXISTS `vx_rate` (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_id` longtext NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_temp`
--

CREATE TABLE IF NOT EXISTS `vx_temp` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `key1` text NOT NULL,
  `key2` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_user`
--

CREATE TABLE IF NOT EXISTS `vx_user` (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(100) NOT NULL,
  `user_flag` tinyint(1) NOT NULL DEFAULT '0',
  `user_email` varchar(100) NOT NULL,
  `user_email_confirm` varchar(16) NOT NULL,
  `user_email_confirm_sent` tinyint(1) NOT NULL DEFAULT '0',
  `user_pwd` varchar(32) NOT NULL,
  `user_salt` varchar(5) NOT NULL,
  `user_site_info` varchar(200) NOT NULL DEFAULT '{"follower":"0","following":"0","favtopic":"0","favnode":"0"}',
  `user_profile_info` varchar(400) NOT NULL DEFAULT '{"github":"","twitter":"","site":"","location":"","sign":"","intro":""}',
  `user_register_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_last_login` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

--
-- 转存表中的数据 `vx_user`
--

INSERT INTO `vx_user` (`user_id`, `user_name`, `user_flag`, `user_email`, `user_email_confirm`, `user_email_confirm_sent`, `user_pwd`, `user_salt`, `user_site_info`, `user_profile_info`, `user_register_time`, `user_last_login`) VALUES
(1, 'admin', 9, 'i@nodeprint.com', '43b9d9285dbb7782', 0, '73676265c2648c9639782e5d0c0a0cd8', '828a5', '{"follower":1,"following":0,"favtopic":1,"favnode":0}', '', '2012-04-04 02:54:32', '2012-12-25 00:46:58');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
