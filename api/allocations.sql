-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 10, 2021 at 09:15 PM
-- Server version: 10.1.36-MariaDB
-- PHP Version: 7.2.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `allocations`
--

-- --------------------------------------------------------

--
-- Table structure for table `al_allocations`
--

CREATE TABLE `al_allocations` (
  `id` int(11) NOT NULL,
  `container_number` varchar(12) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `yard_id` int(11) NOT NULL,
  `to` varchar(10) NOT NULL,
  `chassis_number` varchar(20) NOT NULL,
  `seal_number` varchar(10) NOT NULL,
  `drop_date` date DEFAULT NULL,
  `delivery_date` date NOT NULL,
  `open_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `allocation_status_id` int(11) NOT NULL,
  `is_rail_bill` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(3) NOT NULL,
  `created_by` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `last_updated_by` int(11) DEFAULT NULL,
  `last_updated_on` date DEFAULT NULL,
  `delivery_updated_by` int(11) DEFAULT NULL,
  `delivery_updated_on` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `al_allocations`
--

INSERT INTO `al_allocations` (`id`, `container_number`, `destination_id`, `yard_id`, `to`, `chassis_number`, `seal_number`, `drop_date`, `delivery_date`, `open_date`, `expiry_date`, `allocation_status_id`, `is_rail_bill`, `status`, `created_by`, `datetime`, `last_updated_by`, `last_updated_on`, `delivery_updated_by`, `delivery_updated_on`) VALUES
(5, 'BBBBBBB', 3, 7, 'ICTF', 'GFGF', 'KLGUG', '2021-07-07', '2021-06-30', '2021-07-01', '2021-07-02', 1, 1, 'NAL', 1, '2021-07-09 08:49:54', 1, '2021-07-10', NULL, NULL),
(6, 'HGHGHUU', 4, 7, 'BNSF', 'FFYFY', 'HGHJ', '2021-07-08', '0000-00-00', '2021-07-01', '2021-07-02', 2, 0, 'NAL', 1, '2021-07-10 20:13:56', 1, '2021-07-10', NULL, NULL),
(7, 'CCCCCCC', 4, 7, 'BNSF', 'FFYFY', 'HGHJ', '2021-07-08', '2021-07-15', '2021-07-14', '2021-07-19', 2, 0, 'DLY', 27, '2021-07-10 20:14:07', 27, '2021-07-10', 1, '2021-07-10'),
(8, 'DDDDDDD', 4, 7, 'BNSF', 'FFYFY', 'HGHJ', '2021-07-08', '2021-07-10', '2021-07-14', '2021-07-19', 2, 0, 'DLY', 1, '2021-07-10 20:14:11', 1, '2021-07-10', 27, '2021-07-10'),
(9, 'EEEEEEE', 4, 7, 'BNSF', 'FFYFY', 'HGHJ', '2021-07-08', '0000-00-00', '2021-07-15', '2021-07-19', 2, 0, 'ALC', 1, '2021-07-10 20:14:16', 1, '2021-07-10', NULL, NULL),
(10, 'FFFFFFF', 4, 7, 'BNSF', 'FFYFY', 'HGHJ', '2021-07-08', '0000-00-00', '2021-07-08', '2021-07-14', 2, 0, 'ALC', 1, '2021-07-10 20:14:20', 1, '2021-07-10', NULL, NULL),
(11, 'GGGGGGG', 4, 7, 'BNSF', '656775', '87858587', '2021-07-08', '2021-07-10', '2021-07-08', '2021-07-14', 1, 1, 'DLY', 1, '2021-07-10 22:17:42', 1, '2021-07-10', 1, '2021-07-10'),
(12, 'GGGGGGG', 3, 7, 'BNSF', '555555', '555555', '2021-07-07', '0000-00-00', NULL, NULL, 2, 0, 'NAL', 28, '2021-07-10 23:06:10', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `al_allocation_statuses`
--

CREATE TABLE `al_allocation_statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(15) NOT NULL,
  `status` varchar(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `al_allocation_statuses`
--

INSERT INTO `al_allocation_statuses` (`id`, `name`, `status`) VALUES
(1, 'Wheeled', 'A'),
(2, 'Deck', 'A');

-- --------------------------------------------------------

--
-- Table structure for table `al_destinations`
--

CREATE TABLE `al_destinations` (
  `id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(30) NOT NULL,
  `status` varchar(1) NOT NULL,
  `created_by` int(5) NOT NULL,
  `datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `al_destinations`
--

INSERT INTO `al_destinations` (`id`, `code`, `name`, `status`, `created_by`, `datetime`) VALUES
(3, 'NYC', 'Newyork', 'A', 1, '2021-07-07 00:55:54'),
(4, 'DAL', 'Dallas', 'A', 1, '2021-07-07 00:56:06');

-- --------------------------------------------------------

--
-- Table structure for table `al_users`
--

CREATE TABLE `al_users` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` varchar(35) DEFAULT NULL,
  `business_name` varchar(35) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `phone` varchar(12) DEFAULT NULL,
  `username` varchar(35) NOT NULL,
  `password` varchar(300) NOT NULL,
  `role` varchar(20) NOT NULL,
  `type` varchar(15) DEFAULT NULL,
  `otp_login` tinyint(1) NOT NULL DEFAULT '0',
  `fb_login` tinyint(1) NOT NULL DEFAULT '0',
  `sms_campaign` tinyint(1) NOT NULL DEFAULT '0',
  `sms_gateway` varchar(15) NOT NULL DEFAULT 'TEXTLOCAL',
  `sms_limit` int(10) DEFAULT NULL,
  `status` varchar(5) NOT NULL DEFAULT 'ACT',
  `created_by` int(11) NOT NULL,
  `settings` text,
  `updated_by` int(11) DEFAULT NULL,
  `updated_on` datetime DEFAULT NULL,
  `datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `al_users`
--

INSERT INTO `al_users` (`id`, `parent_id`, `name`, `business_name`, `email`, `phone`, `username`, `password`, `role`, `type`, `otp_login`, `fb_login`, `sms_campaign`, `sms_gateway`, `sms_limit`, `status`, `created_by`, `settings`, `updated_by`, `updated_on`, `datetime`) VALUES
(1, NULL, 'Ravi', 'Allocations Inc', 'admin@jarvis.com', '9876543212', 'admin', '$2y$12$qdqd8uB22e7aCqckiQPNXetPXHmBLc.vi8URz8Q7QrAdOjWcZFFXO', 'SUPERADMIN', 'B_ADMIN', 0, 0, 0, 'VIDEOCON', NULL, 'ACT', 1, '{\"activeInstanceId\":1}', NULL, NULL, '2019-05-26 00:00:00'),
(17, NULL, 'Travis', 'Spark Hotels Pvt Ltd', 'support@spark.com', '9638527412', 'spark', '$2y$12$K/xA3rsPz1VuF0ddIWPDZ.zH.F0IfWE0smahf7WN2VkjoNhsBOJ/K', 'CLIENTADMIN', 'CLIENT', 1, 0, 0, 'TEXTLOCAL', NULL, 'ACT', 1, '{\"activeInstanceId\":1}', 1, '2020-03-15 02:00:29', '2019-07-20 14:50:20'),
(23, NULL, 'Elon Musk', 'SpaceX', 'elon@spacex.com', '9638527410', 'elonmusk', '$2y$12$PSdJ3b9bv3vYA6ajvVCnb.3y4lJdoSkxi1vZlqOEKd8SZvUSt86YW', 'CLIENTADMIN', 'CLIENT', 1, 0, 0, 'VIDEOCON', NULL, 'ACT', 1, '{\"activeInstanceId\":5}', 1, '2019-08-30 18:24:39', '2019-08-03 09:09:54'),
(27, 1, 'Jaswinder', NULL, 'jassi@gmail.com', NULL, 'jassi', '$2y$12$vv3ULFgCjDIGsSxYPK61H.YRewy/7fKARq.nVLY3zh1QNlpUCAKnC', 'SUPERADMIN_STAFF', 'STAFF', 0, 0, 0, 'TEXTLOCAL', NULL, 'ACT', 1, NULL, NULL, NULL, '2019-08-05 19:45:36'),
(28, 1, 'Asif', NULL, 'asif@gmail.com', NULL, 'asif', '$2y$12$Im3Oaipu.eMAQigg.ewr5uKZuJ2/E/h3FUy0UrlCNSu8O7k9tWgbO', 'SUPERADMIN_STAFF', 'STAFF', 0, 0, 0, 'TEXTLOCAL', NULL, 'ACT', 1, NULL, NULL, NULL, '2019-08-05 20:06:13');

-- --------------------------------------------------------

--
-- Table structure for table `al_yards`
--

CREATE TABLE `al_yards` (
  `id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(30) NOT NULL,
  `status` varchar(1) NOT NULL,
  `created_by` int(5) NOT NULL,
  `datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `al_yards`
--

INSERT INTO `al_yards` (`id`, `code`, `name`, `status`, `created_by`, `datetime`) VALUES
(7, 'AL', 'ALAMEDA', 'A', 1, '2021-07-07 00:56:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `al_allocations`
--
ALTER TABLE `al_allocations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `al_allocation_statuses`
--
ALTER TABLE `al_allocation_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `al_destinations`
--
ALTER TABLE `al_destinations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `al_users`
--
ALTER TABLE `al_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `al_yards`
--
ALTER TABLE `al_yards`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `al_allocations`
--
ALTER TABLE `al_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `al_allocation_statuses`
--
ALTER TABLE `al_allocation_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `al_destinations`
--
ALTER TABLE `al_destinations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `al_users`
--
ALTER TABLE `al_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `al_yards`
--
ALTER TABLE `al_yards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
