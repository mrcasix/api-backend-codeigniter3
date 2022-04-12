-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 12, 2022 at 05:19 PM
-- Server version: 5.7.31-0ubuntu0.16.04.1
-- PHP Version: 7.2.34-9+ubuntu16.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `backend_apps`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_log`
--

CREATE TABLE `api_log` (
  `id` int(11) NOT NULL,
  `caller` varchar(255) DEFAULT NULL,
  `record_type` varchar(255) DEFAULT NULL,
  `record_content` int(11) DEFAULT NULL,
  `debug` tinyint(4) DEFAULT '0',
  `user_upd` varchar(255) NOT NULL,
  `timestamp_upd` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `auth_app_access`
--

CREATE TABLE `auth_app_access` (
  `id` int(11) NOT NULL,
  `app_name` varchar(255) NOT NULL,
  `client_id` varchar(500) NOT NULL,
  `client_secret` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `auth_device_access`
--

CREATE TABLE `auth_device_access` (
  `id` int(11) NOT NULL,
  `device_info` varchar(500) NOT NULL,
  `auth_app_access` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `auth_oidc_token`
--

CREATE TABLE `auth_oidc_token` (
  `id` int(11) NOT NULL,
  `auth_device_access` int(11) NOT NULL,
  `token` varchar(1000) NOT NULL,
  `first_access` int(11) NOT NULL,
  `duration` int(11) NOT NULL DEFAULT '900'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `api_log`
--
ALTER TABLE `api_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `auth_app_access`
--
ALTER TABLE `auth_app_access`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `auth_device_access`
--
ALTER TABLE `auth_device_access`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `auth_oidc_token`
--
ALTER TABLE `auth_oidc_token`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `api_log`
--
ALTER TABLE `api_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auth_app_access`
--
ALTER TABLE `auth_app_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auth_device_access`
--
ALTER TABLE `auth_device_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auth_oidc_token`
--
ALTER TABLE `auth_oidc_token`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
