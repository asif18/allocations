-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2020 at 08:25 PM
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
-- Table structure for table `al_data_usage_limits`
--

CREATE TABLE `al_data_usage_limits` (
  `id` int(11) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `value` varchar(5) DEFAULT NULL,
  `size` varchar(2) DEFAULT NULL,
  `status` varchar(1) DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `al_data_usage_limits`
--

INSERT INTO `al_data_usage_limits` (`id`, `user_id`, `value`, `size`, `status`) VALUES
(1, 1, '1', 'GB', 'A'),
(2, 23, '2', 'MB', 'D'),
(3, 23, '2', 'MB', 'A'),
(4, 23, '2', 'GB', 'D'),
(5, 23, '2', 'GB', 'A'),
(6, 23, '1', 'GB', 'A'),
(7, 23, '1', 'MB', 'A'),
(8, 1, '2', 'GB', 'A'),
(9, 17, '2', 'GB', 'A');

-- --------------------------------------------------------

--
-- Table structure for table `al_instances`
--

CREATE TABLE `al_instances` (
  `id` int(11) NOT NULL,
  `name` varchar(35) DEFAULT NULL,
  `mik_ip` varchar(30) NOT NULL,
  `mik_port` varchar(10) NOT NULL,
  `mik_username` varchar(100) NOT NULL,
  `mik_password` varchar(100) NOT NULL,
  `mik_lan_ip` varchar(30) DEFAULT NULL,
  `mik_dns_ip` varchar(50) DEFAULT NULL,
  `mik_dns_port` varchar(10) DEFAULT NULL,
  `mik_default_password` varchar(100) DEFAULT NULL,
  `mik_system_date` varchar(30) DEFAULT NULL,
  `mik_system_time` varchar(30) DEFAULT NULL,
  `mik_system_timezone` varchar(35) DEFAULT 'Asia/Kolkata',
  `user_id` int(11) NOT NULL,
  `created_by` int(10) DEFAULT NULL,
  `destination` varchar(1024) DEFAULT NULL,
  `wifi_user_settings` text,
  `status` varchar(5) DEFAULT 'ACT',
  `datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `al_instances`
--

INSERT INTO `al_instances` (`id`, `name`, `mik_ip`, `mik_port`, `mik_username`, `mik_password`, `mik_lan_ip`, `mik_dns_ip`, `mik_dns_port`, `mik_default_password`, `mik_system_date`, `mik_system_time`, `mik_system_timezone`, `user_id`, `created_by`, `destination`, `wifi_user_settings`, `status`, `datetime`) VALUES
(1, 'President Hotel', '163.47.212.130', '8728', 'asif', 'MTIzNDU=', '192.63.56.21', NULL, NULL, NULL, NULL, NULL, NULL, 17, 1, 'https://google.com', NULL, 'ACT', '2019-07-20 14:51:27'),
(2, 'Spark Instance', '123.45.67.89', '1234', 'dummy', 'ZHVtbXk=', '123.23.63.14', '123.23.63.14', '1234', 'MTIzNDU=', NULL, NULL, NULL, 17, 1, 'https://yahoo.co.in', NULL, 'ACT', '2019-07-20 14:54:44'),
(4, 'ElonMusk', '103.217.127.228', '8728', 'asif', 'YXNpZg==', '163.47.212.130', NULL, NULL, 'MTIzNDU=', NULL, NULL, NULL, 23, 1, 'https://google.com', NULL, 'ACT', '2019-08-03 09:45:10'),
(5, 'Main Router', '103.217.127.228', '8728', 'asif', 'YXNpZg==', '103.217.127.228', '139.59.74.160', '6301', 'MTIzNDU2Nzg5', NULL, NULL, NULL, 23, 1, 'https://google.com', '{\"canShowNameField\":false,\"isNameFieldRequired\":false,\"canShowEmailField\":true,\"isEmailFieldRequired\":true,\"profile\":\"3 guest\\/1 day\",\"repeatInterval\":\"2 Days\",\"usageTimeLimit\":\"2 Days\",\"validTill\":\"2 Days\",\"dataUsageLimit\":\"2 GB\"}', 'ACT', '2019-08-23 13:10:49');

-- --------------------------------------------------------

--
-- Table structure for table `al_otp_log`
--

CREATE TABLE `al_otp_log` (
  `id` int(11) NOT NULL,
  `client_id` int(10) DEFAULT NULL,
  `wifi_user_id` int(10) DEFAULT NULL,
  `otp` varchar(10) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `room_no` varchar(10) DEFAULT NULL,
  `profile` varchar(100) DEFAULT NULL,
  `allocated_usage` varchar(100) NOT NULL,
  `sms_message` text NOT NULL,
  `sent_by` int(10) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `al_otp_log`
--

INSERT INTO `al_otp_log` (`id`, `client_id`, `wifi_user_id`, `otp`, `username`, `password`, `room_no`, `profile`, `allocated_usage`, `sms_message`, `sent_by`, `datetime`) VALUES
(2, 23, 3, NULL, '101', 'NTYzODY4MTQ=', '101', '1 guest/ 1 day', '', 'Welcome to iberrywifi.in , Your user name is : 101 , your password is : 56386814', 23, '2020-04-16 22:53:10'),
(3, 23, 4, NULL, '9715648767_1234567899012', '7253730', NULL, NULL, '', 'OTP is 58553 : Welcome to http://iberrywifi.in', 23, '2020-04-16 23:05:53'),
(4, 23, 7, NULL, '9715648767_1234567899012', '8724089', NULL, NULL, '', 'OTP is 59441 : Welcome to http://iberrywifi.in', 23, '2020-04-16 23:20:41'),
(5, 23, NULL, '00531', '9715648767_50-7B-9D-69-E8-86', '5855383', NULL, '3 guest/1 day', '2147483648', 'OTP is 00531 : Welcome to http://iberrywifi.in', 23, '2020-05-05 23:12:11'),
(6, 23, NULL, '75534', '9715648767_50-7B-9D-69-E8-86', '0350869', NULL, '3 guest/1 day', '2147483648', 'OTP is 75534 : Welcome to http://iberrywifi.in', 23, '2020-05-06 20:02:14'),
(7, 23, NULL, '75630', '9715648767_50-7B-9D-69-E8-86', '4023392', NULL, '3 guest/1 day', '2147483648', 'OTP is 75630 : Welcome to http://iberrywifi.in', 23, '2020-05-06 20:03:50');

-- --------------------------------------------------------

--
-- Table structure for table `al_sms_counter`
--

CREATE TABLE `al_sms_counter` (
  `id` int(20) NOT NULL,
  `client_id` int(11) NOT NULL,
  `sms_for` varchar(100) NOT NULL,
  `sms_count` int(11) NOT NULL,
  `sms_vendor` varchar(20) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `al_sms_counter`
--

INSERT INTO `al_sms_counter` (`id`, `client_id`, `sms_for`, `sms_count`, `sms_vendor`, `date`) VALUES
(2, 1, 'OTP', 1, 'TEXTLOCAL', '2019-08-30'),
(3, 1, 'OTP', 9, 'VIDEOCON', '2019-08-30'),
(4, 1, 'OTP', 5, 'VIDEOCON', '2019-08-31'),
(5, 17, 'OTP', 12, 'TEXTLOCAL', '2019-10-27'),
(6, 1, 'OTP', 3, 'VIDEOCON', '2019-11-12'),
(7, 23, 'OTP', 3, 'VIDEOCON', '2020-04-16'),
(8, 23, 'OTP', 1, 'VIDEOCON', '2020-05-05'),
(9, 23, 'OTP', 2, 'VIDEOCON', '2020-05-06');

-- --------------------------------------------------------

--
-- Table structure for table `al_sms_templates`
--

CREATE TABLE `al_sms_templates` (
  `id` int(10) NOT NULL,
  `client_id` int(10) NOT NULL,
  `template` text NOT NULL,
  `template_for` varchar(30) NOT NULL,
  `status` varchar(10) NOT NULL,
  `approval_status` varchar(10) NOT NULL,
  `datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `al_usage_timing_limits`
--

CREATE TABLE `al_usage_timing_limits` (
  `id` int(11) NOT NULL,
  `user_id` int(5) DEFAULT NULL,
  `value` varchar(5) DEFAULT NULL,
  `time` varchar(7) DEFAULT NULL,
  `status` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `al_usage_timing_limits`
--

INSERT INTO `al_usage_timing_limits` (`id`, `user_id`, `value`, `time`, `status`) VALUES
(1, 23, '2', 'Hours', 'A'),
(2, 23, '2', 'Days', 'D'),
(3, 23, '2', 'Days', 'A'),
(4, 1, '2', 'Hours', 'A'),
(5, 17, '1', 'Hour', 'A'),
(6, 17, '2', 'Days', 'A'),
(7, 23, '1', 'Day', 'A');

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
(1, NULL, 'Jarvis', 'Jarvis Inc', 'admin@jarvis.com', '9638527410', 'admin', '$2y$12$s2xb1.dEzR6PUUT9hG2Ab.LZyMIRZ5Q.UyLa1V8RTiVvI1BqvOHFq', 'SUPERADMIN', 'B_ADMIN', 0, 0, 0, 'VIDEOCON', NULL, 'ACT', 1, '{\"activeInstanceId\":5}', NULL, NULL, '2019-05-26 00:00:00'),
(17, NULL, 'Travis', 'Spark Hotels Pvt Ltd', 'support@spark.com', '9638527412', 'spark', '$2y$12$K/xA3rsPz1VuF0ddIWPDZ.zH.F0IfWE0smahf7WN2VkjoNhsBOJ/K', 'CLIENTADMIN', 'CLIENT', 1, 0, 0, 'TEXTLOCAL', NULL, 'ACT', 1, '{\"activeInstanceId\":1}', 1, '2020-03-15 02:00:29', '2019-07-20 14:50:20'),
(23, NULL, 'Elon Musk', 'SpaceX', 'elon@spacex.com', '9638527410', 'elonmusk', '$2y$12$PSdJ3b9bv3vYA6ajvVCnb.3y4lJdoSkxi1vZlqOEKd8SZvUSt86YW', 'CLIENTADMIN', 'CLIENT', 1, 0, 0, 'VIDEOCON', NULL, 'ACT', 1, '{\"activeInstanceId\":5}', 1, '2019-08-30 18:24:39', '2019-08-03 09:09:54'),
(27, 1, 'Sruthi', NULL, 'sruthi@iberrywifi.com', NULL, 'sruthi', '$2y$12$vv3ULFgCjDIGsSxYPK61H.YRewy/7fKARq.nVLY3zh1QNlpUCAKnC', 'SUPERADMIN_STAFF', 'STAFF', 0, 0, 0, 'TEXTLOCAL', NULL, 'DEL', 1, NULL, NULL, NULL, '2019-08-05 19:45:36'),
(28, 1, 'Promila', NULL, 'promila@iberrywifi.com', NULL, 'promila', '$2y$12$Im3Oaipu.eMAQigg.ewr5uKZuJ2/E/h3FUy0UrlCNSu8O7k9tWgbO', 'SUPERADMIN_STAFF', 'STAFF', 0, 0, 0, 'TEXTLOCAL', NULL, 'ACT', 1, NULL, NULL, NULL, '2019-08-05 20:06:13'),
(29, NULL, 'test 1', 'test 1 - 1', 'tester@test.com', '9876543212', 'tester', '$2y$12$zvjXg.XhfcBjrDWd5HQa3.P5GHq84.EHFbZ.z0DJ0z/PRAa6VVWp.', 'CLIENTADMIN', 'CLIENT', 1, 0, 1, 'TEXTLOCAL', 1000, 'ACT', 1, NULL, NULL, NULL, '2019-08-22 13:33:45'),
(30, NULL, 'test 1', 'test 1 - 1', 'tester@test.com', '9876543212', 'tester2', '$2y$12$g5j0V.xj0vpzpJLnBDT3xO6YtRSAfUwDkJuGbLdNeh/Nx23l8gUmO', 'CLIENTADMIN', 'CLIENT', 1, 1, 0, 'VIDEOCON', 1000, 'ACT', 1, NULL, 1, '2019-08-30 18:41:27', '2019-08-22 13:35:39');

-- --------------------------------------------------------

--
-- Table structure for table `al_wifi_usage_log`
--

CREATE TABLE `al_wifi_usage_log` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `wifi_user_id` int(11) DEFAULT NULL,
  `username` varchar(300) DEFAULT NULL,
  `profile` varchar(100) DEFAULT NULL,
  `allocated_usage` varchar(30) DEFAULT NULL,
  `bytes_in` varchar(300) DEFAULT NULL,
  `bytes_out` varchar(300) DEFAULT NULL,
  `total_usage` varchar(300) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `uptime` varchar(50) DEFAULT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `mac_address` varchar(100) DEFAULT NULL,
  `login_time` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `al_wifi_usage_log`
--

INSERT INTO `al_wifi_usage_log` (`id`, `client_id`, `vendor_id`, `wifi_user_id`, `username`, `profile`, `allocated_usage`, `bytes_in`, `bytes_out`, `total_usage`, `datetime`, `uptime`, `ip_address`, `mac_address`, `login_time`, `logout_time`) VALUES
(3, 31, NULL, 5, 'jassi12', NULL, '524288000', '536554', '855526', '1392080', '2020-04-19 00:33:13', '18', '10.5.50.238', 'C0:38:96:62:72:11', '2020-04-19 00:32:55', '2020-04-19 00:33:13');

-- --------------------------------------------------------

--
-- Table structure for table `al_wifi_users`
--

CREATE TABLE `al_wifi_users` (
  `id` int(10) NOT NULL,
  `client_id` int(10) DEFAULT NULL,
  `vendor_id` int(10) NOT NULL,
  `mac_address` varchar(60) DEFAULT NULL,
  `ip_address` varchar(50) NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `mobile_number` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `password` varchar(150) DEFAULT 'A',
  `fb_id` varchar(30) DEFAULT NULL,
  `fb_liked` tinyint(1) DEFAULT '0',
  `otp` varchar(10) NOT NULL,
  `status` varchar(1) NOT NULL,
  `last_visit_date` datetime NOT NULL,
  `otp_sent_on` datetime NOT NULL,
  `created_date` datetime NOT NULL,
  `auto_login_count` int(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `al_wifi_users`
--

INSERT INTO `al_wifi_users` (`id`, `client_id`, `vendor_id`, `mac_address`, `ip_address`, `username`, `mobile_number`, `email`, `name`, `password`, `fb_id`, `fb_liked`, `otp`, `status`, `last_visit_date`, `otp_sent_on`, `created_date`, `auto_login_count`) VALUES
(1, 1, 0, NULL, '', '203', '9715648767', NULL, 'Mohamed Asif', '53810196', NULL, 0, '', '', '2019-11-12 22:33:34', '2019-11-12 22:33:34', '2019-08-30 17:56:37', 0),
(2, 17, 0, NULL, '', 'bypass', '9715648767', NULL, 'Asif', '59771619', NULL, 0, '', '', '2019-10-27 14:47:25', '2019-10-27 14:47:25', '2019-10-27 12:33:35', 0),
(3, 23, 0, NULL, '', '101', '9715648767', NULL, 'Asif', '56386814', NULL, 0, '', '', '2020-04-16 22:53:10', '2020-04-16 22:53:10', '2020-04-16 22:52:18', 0),
(4, 23, 0, '123:456:789:9012', '', '9715648767_1234567899012', '9715648767', NULL, '', '7253730', NULL, 0, '58553', 'A', '2020-04-16 23:05:53', '0000-00-00 00:00:00', '2020-04-16 23:05:53', 0),
(5, 23, 0, '123:456:789:9012', '', '9715648767_1234567899012', '9715648767', NULL, 'Asif', '6701689', NULL, 0, '58761', 'A', '2020-04-16 23:09:21', '0000-00-00 00:00:00', '2020-04-16 23:09:21', 0),
(6, 23, 0, '123:456:789:9012', '', '9715648767_1234567899012', '9715648767', NULL, NULL, '5385505', NULL, 0, '59350', 'A', '2020-04-16 23:19:10', '0000-00-00 00:00:00', '2020-04-16 23:19:10', 0),
(7, 23, 0, '123:456:789:9012', '', '9715648767_1234567899012', '9715648767', NULL, NULL, '8724089', NULL, 0, '59441', 'A', '2020-04-16 23:20:41', '0000-00-00 00:00:00', '2020-04-16 23:20:41', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `al_data_usage_limits`
--
ALTER TABLE `al_data_usage_limits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `al_instances`
--
ALTER TABLE `al_instances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `al_otp_log`
--
ALTER TABLE `al_otp_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `al_sms_counter`
--
ALTER TABLE `al_sms_counter`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `al_usage_timing_limits`
--
ALTER TABLE `al_usage_timing_limits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `al_users`
--
ALTER TABLE `al_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `al_wifi_usage_log`
--
ALTER TABLE `al_wifi_usage_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `al_wifi_users`
--
ALTER TABLE `al_wifi_users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `al_data_usage_limits`
--
ALTER TABLE `al_data_usage_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `al_instances`
--
ALTER TABLE `al_instances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `al_otp_log`
--
ALTER TABLE `al_otp_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `al_sms_counter`
--
ALTER TABLE `al_sms_counter`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `al_usage_timing_limits`
--
ALTER TABLE `al_usage_timing_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `al_users`
--
ALTER TABLE `al_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `al_wifi_usage_log`
--
ALTER TABLE `al_wifi_usage_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `al_wifi_users`
--
ALTER TABLE `al_wifi_users`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
