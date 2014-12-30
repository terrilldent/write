<?php

/* Rename this file config.php and fill in the values */

$HOST = "";
$USER = "";
$PASS = "";
$DBNAME = "";


/* 

Required Database Tables 

CREATE TABLE IF NOT EXISTS `posts` (
  `id` varchar(20) NOT NULL DEFAULT '',
  `title` varchar(100) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `author` varchar(40) NOT NULL DEFAULT '',
  `date` date DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `index` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(10) NOT NULL,
  `type` varchar(10) NOT NULL,
  `category` varchar(150) NOT NULL,
  PRIMARY KEY (`index`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

CREATE TABLE IF NOT EXISTS `users` (
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(128) DEFAULT NULL,
  `token` varchar(128) DEFAULT NULL,
  `ipaddress` varchar(128) DEFAULT NULL,
  `challenge` varchar(128) NOT NULL,
  `expiry` datetime DEFAULT NULL,
  `attempt` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

*/

?>