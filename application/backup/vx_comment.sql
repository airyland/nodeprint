-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2012 年 12 月 24 日 22:55
-- 服务器版本: 5.5.24-log
-- PHP 版本: 5.3.13

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- 转存表中的数据 `vx_comment`
--

INSERT INTO `vx_comment` (`cm_id`, `cm_content`, `user_id`, `user_name`, `cm_reply_to`, `cm_time`, `post_id`, `cm_reply_name`, `cm_reply_id`, `cm_other`) VALUES
(2, 'hello  ', 1, 'admin', 0, '2012-12-24 05:43:52', 1, '', 0, '{"bs":"chrome-23"}');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
