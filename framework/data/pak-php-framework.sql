-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 11, 2019 at 02:38 PM
-- Server version: 5.7.25-0ubuntu0.18.04.2
-- PHP Version: 7.2.16-1+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pakjiddat_pakphp`
--

-- --------------------------------------------------------

--
-- Table structure for table `pakphp_access_data`
--

CREATE TABLE `pakphp_access_data` (
  `id` int(11) NOT NULL,
  `url` mediumtext NOT NULL COMMENT 'the requested url',
  `post_params` text NOT NULL COMMENT 'the parameters sent with post request',
  `get_params` text NOT NULL COMMENT 'the the parameters sent with get request',
  `http_method` varchar(20) NOT NULL COMMENT 'the http method',
  `ip_address` varchar(50) NOT NULL COMMENT 'the users ip address',
  `browser` varchar(255) NOT NULL COMMENT 'the users browser',
  `time_taken` float NOT NULL COMMENT 'the time taken by server to process request',
  `app_name` varchar(255) DEFAULT NULL COMMENT 'the application name',
  `site_url` varchar(255) NOT NULL COMMENT 'the site url',
  `created_on` int(11) DEFAULT NULL COMMENT 'the url request time'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pakphp_error_data`
--

CREATE TABLE `pakphp_error_data` (
  `id` int(11) NOT NULL,
  `level` varchar(50) NOT NULL COMMENT 'the error level',
  `type` enum('Error','Exception') NOT NULL COMMENT 'the error type',
  `message` longtext NOT NULL COMMENT 'the error message',
  `file` mediumtext NOT NULL COMMENT 'the error file',
  `line` varchar(15) NOT NULL COMMENT 'the error line',
  `details` longtext NOT NULL COMMENT 'the error details',
  `server_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'the server data in json format. includes user ip and request url',
  `db_log` longtext NOT NULL COMMENT 'the sql queries run',
  `html` longtext NOT NULL COMMENT 'the error message in html format',
  `app_name` varchar(255) NOT NULL COMMENT 'the application name',
  `created_on` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pakphp_test_data`
--

CREATE TABLE `pakphp_test_data` (
  `id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `params` text,
  `app_name` varchar(255) DEFAULT NULL,
  `created_on` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `pakphp_test_results`
--

CREATE TABLE `pakphp_test_results` (
  `id` int(11) NOT NULL,
  `app_name` varchar(255) NOT NULL COMMENT 'application name',
  `results` longtext NOT NULL COMMENT 'summary of the unit test',
  `time_taken` int(11) NOT NULL COMMENT 'time taken to run the unit test',
  `created_on` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pakphp_access_data`
--
ALTER TABLE `pakphp_access_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pakphp_error_data`
--
ALTER TABLE `pakphp_error_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pakphp_test_data`
--
ALTER TABLE `pakphp_test_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pakphp_test_results`
--
ALTER TABLE `pakphp_test_results`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pakphp_access_data`
--
ALTER TABLE `pakphp_access_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pakphp_error_data`
--
ALTER TABLE `pakphp_error_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pakphp_test_data`
--
ALTER TABLE `pakphp_test_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pakphp_test_results`
--
ALTER TABLE `pakphp_test_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
