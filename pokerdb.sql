-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 20, 2015 at 07:13 PM
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
('abort', '0'),
('dealercards', ''),
('handbrake', '0'),
('lastupdate', '2015-01-20 19:13:20'),
('message', ''),
('playercount', '0'),
('stage', '0');

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
  `data` mediumtext NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `player`
--

INSERT INTO `player` (`id`, `sid`, `hand`, `eval`, `funds`, `data`, `user_id`) VALUES
(1, '78luu5bq4e1q140lprbae7vdk1', '', '{"score":"0.1413120705","note":"High Cards A, K, Q, 7, 5"}', 5000, '{"action":0,"quit":0,"confirmed":0,"raise":0}', 0),
(2, '', '', '', 5000, '{"action":0,"quit":0,"confirmed":0,"raise":0}', 0),
(3, '', '', '', 5000, '{"action":0,"quit":0,"confirmed":0,"raise":0}', 0),
(4, '', '', '', 5000, '{"action":0,"quit":0,"confirmed":0,"raise":0}', 0),
(5, '', '', '', 5000, '{"action":0,"quit":0,"confirmed":0,"raise":0}', 0),
(6, '', '', '', 5000, '{"action":0,"quit":0,"confirmed":0,"raise":0}', 0);

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

--
-- Dumping data for table `session`
--

INSERT INTO `session` (`sid`, `lastupdate`, `ip`) VALUES
('78luu5bq4e1q140lprbae7vdk1', '2015-01-20 19:13:24', '77.79.28.106');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
