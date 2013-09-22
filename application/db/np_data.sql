-- phpMyAdmin SQL Dump
-- version 3.5.0
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2013 年 09 月 21 日 20:17
-- 服务器版本: 5.5.19
-- PHP 版本: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
USE vx;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `np_test`
--

-- --------------------------------------------------------

--
-- 表的结构 `vx_comment`
--

CREATE TABLE IF NOT EXISTS `vx_comment` (
  `cm_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `cm_content` text NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `cm_reply_to` bigint(20) NOT NULL,
  `cm_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_id` bigint(20) NOT NULL,
  `cm_reply_name` varchar(100) NOT NULL,
  `cm_reply_id` bigint(20) NOT NULL,
  `cm_other` text NOT NULL,
  PRIMARY KEY (`cm_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=71 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_config`
--

CREATE TABLE IF NOT EXISTS `vx_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- 转存表中的数据 `vx_config`
--

INSERT INTO `vx_config` (`id`, `name`, `value`) VALUES
(1, 'name', 'NodePrint'),
(2, 'description', 'A lightweight BBS '),
(3, 'keyword', 'bbs'),
(4, 'url', 'http://nodeprint.com/'),
(6, 'lang', 'zh'),
(7, 'topic_no', '15'),
(8, 'local_upload', '1'),
(9, 'comment_no', '100'),
(10, 'auto_backup', '0'),
(11, 'show_status', '1'),
(12, 'topic_edit_expire', '10'),
(13, 'plugin', '["categories"]'),
(14, 'ga', 'UA-31226733-1'),
(15, 'msg_check_interval', '20000');

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=517 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_fuck`
--

CREATE TABLE IF NOT EXISTS `vx_fuck` (
  `time` date NOT NULL DEFAULT '0000-00-00',
  `key` varchar(100) NOT NULL,
  `no` int(11) NOT NULL DEFAULT '0',
  `no2` int(20) NOT NULL DEFAULT '0',
  `cat1` int(20) NOT NULL DEFAULT '0',
  `cat2` int(20) NOT NULL DEFAULT '0',
  `cat3` int(20) NOT NULL DEFAULT '0',
  `cat4` int(20) NOT NULL DEFAULT '0',
  `cat9` int(20) NOT NULL DEFAULT '0',
  `cat5` int(20) NOT NULL DEFAULT '0',
  `cat6` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

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
  `m_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`m_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=64 ;

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
  `node_recommend` int(1) NOT NULL DEFAULT '1',
  `node_icon` varchar(20) NOT NULL,
  `node_parent` bigint(20) NOT NULL,
  `node_related` varchar(100) NOT NULL,
  `node_intro` text,
  `node_post_no` bigint(20) NOT NULL DEFAULT '0',
  `node_ad` text,
  `node_css` text,
  PRIMARY KEY (`node_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_oauth`
--

CREATE TABLE IF NOT EXISTS `vx_oauth` (
  `o_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `o_type` varchar(10) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `o_access_token` varchar(100) NOT NULL,
  `o_openid` varchar(100) NOT NULL,
  `o_refresh_token` varchar(100) NOT NULL,
  `o_time` varchar(100) NOT NULL,
  `o_expire` bigint(20) NOT NULL,
  PRIMARY KEY (`o_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_rate`
--

CREATE TABLE IF NOT EXISTS `vx_rate` (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_id` longtext NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_rumors`
--

CREATE TABLE IF NOT EXISTS `vx_rumors` (
  `rid` varchar(30) NOT NULL,
  `be` varchar(100) NOT NULL,
  `becount` int(10) NOT NULL,
  `becredit` varchar(10) NOT NULL,
  `beindex` varchar(100) NOT NULL,
  `beingContent` text NOT NULL,
  `being` varchar(100) NOT NULL,
  `beingcredit` varchar(10) NOT NULL,
  `beinglastPage` int(11) NOT NULL,
  `beinglink` varchar(100) NOT NULL,
  `beingtime` datetime NOT NULL,
  `beingv` tinyint(1) NOT NULL DEFAULT '0',
  `beingwin` tinyint(1) NOT NULL,
  `betime` datetime NOT NULL,
  `bev` tinyint(1) NOT NULL DEFAULT '0',
  `bewin` tinyint(1) NOT NULL,
  `finals` text NOT NULL,
  `offset` varchar(100) NOT NULL,
  `title` varchar(200) NOT NULL,
  `isMulti` tinyint(1) NOT NULL,
  PRIMARY KEY (`rid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `vx_search`
--

CREATE TABLE IF NOT EXISTS `vx_search` (
  `item_id` bigint(20) NOT NULL,
  `item` text NOT NULL,
  `key` varchar(20) NOT NULL,
  UNIQUE KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `vx_seedit`
--

CREATE TABLE IF NOT EXISTS `vx_seedit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` bigint(20) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3356 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_temp`
--

CREATE TABLE IF NOT EXISTS `vx_temp` (
  `t_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `t_type` varchar(50) NOT NULL,
  `t_content` text NOT NULL,
  `t_other` text NOT NULL,
  `t_keyid` bigint(20) NOT NULL,
  PRIMARY KEY (`t_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_tweet`
--

CREATE TABLE IF NOT EXISTS `vx_tweet` (
  `aid` bigint(100) NOT NULL AUTO_INCREMENT,
  `id` bigint(100) NOT NULL,
  `time` bigint(100) NOT NULL,
  `atime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `html` text CHARACTER SET utf8 NOT NULL,
  `content` text CHARACTER SET utf8 NOT NULL,
  `repost` bigint(40) NOT NULL,
  `comment` bigint(40) NOT NULL,
  `dorepost` tinyint(1) NOT NULL DEFAULT '0',
  `user` text CHARACTER SET utf8 NOT NULL,
  `key` varchar(100) CHARACTER SET utf8 NOT NULL,
  `cat` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `docomment` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`),
  KEY `id_3` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=139029 ;

-- --------------------------------------------------------

--
-- 表的结构 `vx_tweets`
--

CREATE TABLE IF NOT EXISTS `vx_tweets` (
  `id` bigint(100) NOT NULL,
  `time` bigint(100) NOT NULL,
  `html` text CHARACTER SET utf8 NOT NULL,
  `content` text CHARACTER SET utf8 NOT NULL,
  `repost` bigint(40) NOT NULL,
  `comment` bigint(40) NOT NULL,
  `dorepost` tinyint(1) NOT NULL DEFAULT '0',
  `user` text CHARACTER SET utf8 NOT NULL,
  `key` varchar(100) CHARACTER SET utf8 NOT NULL,
  `cat` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `vx_user`
--

CREATE TABLE IF NOT EXISTS `vx_user` (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_from` varchar(10) NOT NULL,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=35 ;

--
-- 转存表中的数据 `vx_user`
--

INSERT INTO `vx_user` (`user_id`, `user_from`, `user_name`, `user_flag`, `user_email`, `user_email_confirm`, `user_email_confirm_sent`, `user_pwd`, `user_salt`, `user_site_info`, `user_profile_info`, `user_register_time`, `user_last_login`) VALUES
(34, 'admin', 'np_admin', 9, 'np@nodeprint.com', '', 0, 'e15aed6cffa997e6f0f22cb7bebb12fd', '24722', '{"follower":"0","following":"0","favtopic":"0","favnode":"0"}', '{"github":"","twitter":"","site":"","location":"","sign":"","intro":""}', '0000-00-00 00:00:00', '2013-09-21 12:14:56');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
