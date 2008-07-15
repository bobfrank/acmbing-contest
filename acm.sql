-- phpMyAdmin SQL Dump
-- version 2.11.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 08, 2008 at 12:50 PM
-- Server version: 5.0.45
-- PHP Version: 5.2.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `acm`
--


CREATE TABLE IF NOT EXISTS `contests` (
  `contest_id` int(11) NOT NULL auto_increment,
  `start` int(11) NOT NULL,
  `length` int(11) NOT NULL,
  PRIMARY KEY  (`contest_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;


CREATE TABLE IF NOT EXISTS `problem_files` (
  `contest_id` int(11) NOT NULL,
  `problem` int(11) NOT NULL,
  `filename` varchar(120) NOT NULL,
  PRIMARY KEY  (`contest_id`,`filename`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `server_info` (
  `name` varchar(30) NOT NULL,
  `value` varchar(30) NOT NULL,
  PRIMARY KEY  (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `server_info` (`name`, `value`) VALUES
('update_time', '1202505554'),
('update_rate', '60'),
('timeout', '30');


CREATE TABLE IF NOT EXISTS `submitted` (
  `submitted_id` int(11) NOT NULL auto_increment,
  `contest_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `problem` int(11) NOT NULL,
  `filename` varchar(120) NOT NULL,
  `tested` tinyint(1) NOT NULL default '0',
  `success` tinyint(1) NOT NULL default '0',
  `time` int(11) NOT NULL,
  `message` varchar(50) NOT NULL,
  PRIMARY KEY  (`submitted_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;


CREATE TABLE IF NOT EXISTS `testcases` (
  `testcase_id` int(11) NOT NULL auto_increment,
  `contest_id` int(11) NOT NULL,
  `problem` int(11) NOT NULL,
  `input` longtext NOT NULL,
  `output` longtext NOT NULL,
  PRIMARY KEY  (`testcase_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;


CREATE TABLE IF NOT EXISTS `test_output` (
  `submitted_id` int(11) NOT NULL,
  `testcase_id` int(11) NOT NULL,
  `output` longtext NOT NULL,
  PRIMARY KEY  (`submitted_id`,`testcase_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL,
  `contest_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY  (`user_id`,`contest_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
