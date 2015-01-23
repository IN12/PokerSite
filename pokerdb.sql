-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2015 at 01:14 AM
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
('dealercards', '[{"color":2,"weight":9,"frontImage":"9_of_hearts.png"},{"color":2,"weight":4,"frontImage":"4_of_hearts.png"},{"color":1,"weight":8,"frontImage":"8_of_diamonds.png"},{"color":3,"weight":7,"frontImage":"7_of_spades.png"},{"color":0,"weight":14,"frontImage":"ace_of_clubs.png"}]'),
('entrancefee', '50'),
('handbrake', '0'),
('lastupdate', '2015-01-23 01:14:12'),
('message', ''),
('playercount', '0'),
('pot', '100'),
('reactionid', '-1'),
('rotationid', '1'),
('stage', '8'),
('winners', '[{"id":"1","score":4.12}]');

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
(1, '78luu5bq4e1q140lprbae7vdk1', '[{"color":2,"weight":13,"frontImage":"king_of_hearts.png"},{"color":0,"weight":7,"frontImage":"7_of_clubs.png"}]', '{"score":"4.12","note":"Straight - Queens high"}', 3190, 50, '{"action":0,"confirmed":0,"raise":0}', 0, '2015-01-23 01:14:12', 0),
(2, 'lkef5b8qsbjp344rvhu7j68h70', '[{"color":2,"weight":8,"frontImage":"8_of_hearts.png"},{"color":2,"weight":7,"frontImage":"7_of_hearts.png"}]', '{"score":"2.110912","note":"Two Pairs - Jacks and Nines"}', 4549, 50, '{"action":0,"confirmed":0,"raise":0}', 0, '2015-01-23 01:14:12', 0),
(3, '', '', '', 6584, 0, '{"action":0,"confirmed":0,"raise":0}', 0, '2015-01-23 01:14:12', 0),
(4, '', '', '', 11332, 0, '{"action":0,"confirmed":0,"raise":0}', 0, '2015-01-23 01:14:12', 0),
(5, '', '', '', 4245, 0, '{"action":0,"confirmed":0,"raise":0}', 0, '2015-01-23 01:14:12', 0),
(6, '', '', '', 5000, 0, '{"action":0,"confirmed":0,"raise":0}', 0, '2015-01-23 01:14:12', 0);

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
('78luu5bq4e1q140lprbae7vdk1', '2015-01-23 01:14:14', '77.79.28.106'),
('lkef5b8qsbjp344rvhu7j68h70', '2015-01-23 01:14:14', '95.92.219.38');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
