-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 14, 2017 at 02:31 AM
-- Server version: 5.7.16-0ubuntu0.16.04.1
-- PHP Version: 7.1.1-1+deb.sury.org~xenial+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `test`
--

-- --------------------------------------------------------

--
-- Table structure for table `author`
--

CREATE TABLE `author` (
  `id` int(10) NOT NULL,
  `name` varchar(60) NOT NULL,
  `type` enum('beginner','professional','senior','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='dfgdfg';

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `content` text NOT NULL,
  `publicated_at` date DEFAULT NULL,
  `fk_author` int(10) NOT NULL,
  `aproved` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='nd=Posts';

-- --------------------------------------------------------

--
-- Table structure for table `tagged`
--

CREATE TABLE `tagged` (
  `fk_post` int(11) NOT NULL,
  `fk_tag` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='pivot=tags|posts';

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `label` varchar(24) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `author`
--
ALTER TABLE `author`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_author` (`fk_author`);

--
-- Indexes for table `tagged`
--
ALTER TABLE `tagged`
  ADD KEY `fk_post` (`fk_post`),
  ADD KEY `fk_tag` (`fk_tag`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `label` (`label`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `author`
--
ALTER TABLE `author`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`fk_author`) REFERENCES `author` (`id`);

--
-- Constraints for table `tagged`
--
ALTER TABLE `tagged`
  ADD CONSTRAINT `asdasd` FOREIGN KEY (`fk_tag`) REFERENCES `tags` (`id`),
  ADD CONSTRAINT `tagged_ibfk_1` FOREIGN KEY (`fk_post`) REFERENCES `posts` (`id`);
