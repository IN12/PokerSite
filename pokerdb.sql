-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2015 at 06:56 AM
-- Server version: 5.6.16
-- PHP Version: 5.5.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `pokerdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `param`
--

CREATE TABLE IF NOT EXISTS `param` (
  `name` varchar(32) NOT NULL,
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `param`
--

INSERT INTO `param` (`name`, `value`) VALUES
('abort', '1'),
('currentbet', '0'),
('dealercards', ''),
('entrancefee', '50'),
('handbrake', '1'),
('lastupdate', '2015-01-23 06:48:43'),
('message', ''),
('playercount', '0'),
('pot', '0'),
('reactionid', '-1'),
('rotationid', '0'),
('stage', '0'),
('winners', '[{"id":0,"score":0}]');

-- --------------------------------------------------------

--
-- Table structure for table `player`
--

CREATE TABLE IF NOT EXISTS `player` (
  `id` int(11) NOT NULL,
  `sid` varchar(32) NOT NULL,
  `hand` mediumtext NOT NULL,
  `eval` mediumtext NOT NULL,
  `funds` int(11) NOT NULL,
  `bet` int(11) NOT NULL,
  `data` mediumtext NOT NULL,
  `quit` int(11) NOT NULL,
  `lastupdate` mediumtext NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `player`
--

INSERT INTO `player` (`id`, `sid`, `hand`, `eval`, `funds`, `bet`, `data`, `quit`, `lastupdate`, `user_id`) VALUES
(1, '', '', '', 5000, 0, '{"action":0,"confirmed":0,"raise":0,"laststage":0}', 0, '2015-01-23 06:55:25', 0),
(2, '', '', '', 5000, 0, '{"action":0,"confirmed":0,"raise":0,"laststage":0}', 0, '2015-01-23 06:48:45', 0),
(3, '', '', '', 5000, 0, '{"action":0,"confirmed":0,"raise":0,"laststage":0}', 0, '2015-01-23 06:48:20', 0),
(4, '', '', '', 5000, 0, '{"action":0,"confirmed":0,"raise":0,"laststage":0}', 0, '2015-01-23 06:48:20', 0),
(5, '', '', '', 5000, 0, '{"action":0,"confirmed":0,"raise":0,"laststage":0}', 0, '2015-01-23 06:48:20', 0),
(6, '', '', '', 5000, 0, '{"action":0,"confirmed":0,"raise":0,"laststage":0}', 0, '2015-01-23 06:48:20', 0);

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `sid` varchar(32) NOT NULL,
  `lastupdate` datetime NOT NULL,
  `ip` varchar(32) NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
