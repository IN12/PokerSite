-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 21, 2015 at 09:00 PM
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
('currentbet', '0'),
('dealercards', '[{"color":0,"weight":14,"frontImage":"ace_of_clubs.png"},{"color":2,"weight":3,"frontImage":"3_of_hearts.png"},{"color":3,"weight":6,"frontImage":"6_of_spades.png"},{"color":1,"weight":12,"frontImage":"queen_of_diamonds.png"},{"color":1,"weight":13,"frontImage":"king_of_diamonds.png"}]'),
('handbrake', '0'),
('lastupdate', '2015-01-21 21:00:25'),
('message', ''),
('playercount', '0'),
('stage', '8');

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
(1, '3nj0imcpferd67p2l4emrhqiq6', '[{"color":2,"weight":10,"frontImage":"10_of_hearts.png"},{"color":1,"weight":7,"frontImage":"7_of_diamonds.png"}]', '{"score":"0.1410080706","note":"High Cards A, 10, 8, 7, 6"}', 5000, 0, '{"action":1,"confirmed":1,"raise":20}', 0, '2015-01-21 20:15:45', 0),
(2, '78luu5bq4e1q140lprbae7vdk1', '[{"color":2,"weight":13,"frontImage":"king_of_hearts.png"},{"color":2,"weight":12,"frontImage":"queen_of_hearts.png"}]', '{"score":"0.1410080704","note":"High Cards A, 10, 8, 7, 4"}', 5000, 0, '{"action":0,"confirmed":1,"raise":0}', 0, '2015-01-21 20:57:59', 0),
(3, 'gt10d83624tgqkft47ivqv6875', '[{"color":2,"weight":11,"frontImage":"jack_of_hearts.png"},{"color":2,"weight":5,"frontImage":"5_of_hearts.png"}]', '{"score":"1.02141007","note":"One Pair of Twos"}', 5000, 0, '{"action":0,"confirmed":0,"raise":0}', 0, '', 0),
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
('3nj0imcpferd67p2l4emrhqiq6', '2015-01-21 21:00:29', '78.58.199.223'),
('78luu5bq4e1q140lprbae7vdk1', '2015-01-21 21:00:29', '77.79.28.106'),
('gt10d83624tgqkft47ivqv6875', '2015-01-21 21:00:29', '86.100.43.70');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
