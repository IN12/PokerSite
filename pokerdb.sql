-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 22, 2015 at 11:57 AM
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
('currentbet', '50'),
('dealercards', '[{"color":2,"weight":11,"frontImage":"jack_of_hearts.png"},{"color":1,"weight":5,"frontImage":"5_of_diamonds.png"},{"color":1,"weight":4,"frontImage":"4_of_diamonds.png"},{"color":0,"weight":8,"frontImage":"8_of_clubs.png"},{"color":2,"weight":8,"frontImage":"8_of_hearts.png"}]'),
('entrancefee', '50'),
('handbrake', '0'),
('lastupdate', '2015-01-22 11:57:35'),
('message', ''),
('playercount', '0'),
('pot', '50'),
('stage', '10');

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
(1, '3u1gurbfqif3ed9rs8vo1h7k47', '[{"color":0,"weight":2,"frontImage":"2_of_clubs.png"},{"color":3,"weight":8,"frontImage":"8_of_spades.png"}]', '{"score":"3.081105","note":"Three Of A Kind - Eights"}', 4050, 50, '{"action":2,"confirmed":1,"raise":45645}', 0, '2015-01-22 11:35:34', 0),
(2, '78luu5bq4e1q140lprbae7vdk1', '[{"color":1,"weight":6,"frontImage":"6_of_diamonds.png"},{"color":2,"weight":10,"frontImage":"10_of_hearts.png"}]', '{"score":"1.08111006","note":"One Pair of Eights"}', 5000, 0, '{"action":0,"confirmed":1,"raise":0}', 0, '2015-01-22 11:53:35', 0),
(3, '', '', '', 5000, 0, '{"action":0,"confirmed":0,"raise":0}', 0, '', 0),
(4, '', '', '', 5000, 0, '{"action":0,"confirmed":0,"raise":0}', 0, '', 0),
(5, '', '', '', 5000, 0, '{"action":0,"confirmed":0,"raise":0}', 0, '', 0),
(6, '', '', '', 5000, 0, '{"action":0,"confirmed":0,"raise":0}', 0, '', 0);

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
('3u1gurbfqif3ed9rs8vo1h7k47', '2015-01-22 11:57:34', '78.58.90.220'),
('78luu5bq4e1q140lprbae7vdk1', '2015-01-22 11:57:34', '77.79.28.106');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
