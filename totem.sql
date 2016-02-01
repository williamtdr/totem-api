SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `backgrounds` (
  `scope` varchar(30) NOT NULL,
  `url` varchar(200) NOT NULL,
  PRIMARY KEY (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `chatkeys` (
  `auth` varchar(20) NOT NULL,
  `display_name` varchar(50) NOT NULL,
  `id` decimal(21,0) NOT NULL,
  PRIMARY KEY (`auth`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `custom_icons` (
  `scope` varchar(30) NOT NULL,
  `url` varchar(200) NOT NULL,
  PRIMARY KEY (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `custom_profile_pictures` (
  `scope` decimal(21,0) NOT NULL,
  `url` varchar(255) NOT NULL,
  PRIMARY KEY (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `custom_settings` (
  `id` decimal(21,0) NOT NULL,
  `notif_song_change` tinyint(1) NOT NULL DEFAULT '1',
  `notif_chat` varchar(10) NOT NULL DEFAULT 'mention',
  `hide_hints` tinyint(1) NOT NULL DEFAULT '0',
  `video_quality` varchar(5) NOT NULL DEFAULT '720p',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` decimal(21,0) NOT NULL,
  `level` int(2) NOT NULL,
  `scope` varchar(30) NOT NULL,
  UNIQUE KEY `id` (`id`,`level`,`scope`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `profiles` (
  `id` decimal(21,0) NOT NULL,
  `bio` varchar(255) NOT NULL,
  `twitter` varchar(255) NOT NULL,
  `steam` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `display_name` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `rooms` (
  `id` varchar(30) NOT NULL,
  `user_counter` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_used` timestamp NULL DEFAULT NULL,
  `display_name` varchar(80) NOT NULL,
  `song_name` varchar(200) DEFAULT NULL,
  `song_artist` varchar(50) DEFAULT NULL,
  `song_started_at` int(10) NOT NULL DEFAULT '0',
  `song_url_fragment` varchar(50) DEFAULT NULL,
  `song_source` int(1) NOT NULL DEFAULT '0',
  `song_picture_url` varchar(200) DEFAULT NULL,
  `description` varchar(500) NOT NULL,
  `password` varchar(80) NOT NULL DEFAULT 'false',
  `blacklist` mediumtext NOT NULL,
  `whitelist` mediumtext NOT NULL,
  `icon` varchar(200) NOT NULL DEFAULT 'http://static.totem.fm/default_notification.png',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `subscriptions` (
  `email` varchar(200) NOT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `users` (
  `id` decimal(21,0) NOT NULL,
  `email` varchar(60) NOT NULL,
  `avatar_link` varchar(200) NOT NULL,
  `display_name` varchar(30) NOT NULL,
  `refresh_token` varchar(255) NOT NULL,
  `remaining_username_changes` int(1) NOT NULL DEFAULT '3',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
