-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 27, 2026 at 03:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `repair_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`) VALUES
(9, 'admin', '$2y$10$gCOLE7jPpGKmBdjqXDCIdeuA00LV88mDe6hSbafP/M4dIg69fWpiy', '2026-01-21 01:26:13');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `asset_id` varchar(50) NOT NULL,
  `equipment_name` varchar(100) DEFAULT NULL,
  `model_name` varchar(255) NOT NULL,
  `equipment_type` varchar(50) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `qr_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `asset_id`, `equipment_name`, `model_name`, `equipment_type`, `serial_no`, `location`, `image_path`, `qr_path`, `created_at`) VALUES
(17, '220000007766-1', 'NB-01', 'ThinkPad X1 Carbon Gen 9', 'COMPUTER EQUIPMENT', 'PF39YS60', 'ห้อง IT 203', 'uploads/Img_220000007766-1.jpg', 'qrcodes/QR_220000007766-1.png', '2026-01-17 06:54:24'),
(19, '22005-02', 'COM-02', ' UBIQUITI UniFi (U7-Lite) Wireless BE5000', 'ACCESS POINT', 'PF16DC85', 'ตึก 26 TC203', 'uploads/Img_22005-02.jpg', 'qrcodes/QR_22005-02.png', '2026-01-19 17:23:39');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `tracking_id` varchar(50) NOT NULL COMMENT 'ใช้สำหรับสร้าง QR Code เพื่อติดตามสถานะ',
  `status` varchar(50) NOT NULL DEFAULT 'Pending' COMMENT 'สถานะงาน (รอรับเรื่อง, \r\nกำลังซ่อม, เสร็จสิ้น)',
  `reported_by` varchar(100) NOT NULL COMMENT 'ชื่อผู้แจ้ง',
  `reporter_id` varchar(50) DEFAULT NULL,
  `asset_id` varchar(50) DEFAULT NULL COMMENT 'รหัสพนักงาน/นักศึกษา',
  `tel` varchar(20) NOT NULL COMMENT 'เบอร์โทรศัพท์',
  `reporter_email` varchar(100) DEFAULT NULL COMMENT 'อีเมล',
  `device_type` varchar(50) NOT NULL COMMENT 'ประเภทอุปกรณ์',
  `device_model` varchar(100) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `device_name` varchar(100) DEFAULT NULL,
  `building` varchar(50) NOT NULL COMMENT 'ตึก',
  `room` varchar(50) DEFAULT NULL COMMENT 'ห้อง',
  `problem_description` text NOT NULL COMMENT 'รายละเอียดปัญหา',
  `img_path` varchar(255) DEFAULT NULL COMMENT 'พาธไฟล์รูปภาพ',
  `created_at` datetime DEFAULT current_timestamp() COMMENT 'เวลาที่แจ้ง',
  `updated_at` datetime DEFAULT NULL,
  `repair_started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `tracking_id`, `status`, `reported_by`, `reporter_id`, `asset_id`, `tel`, `reporter_email`, `device_type`, `device_model`, `serial_no`, `device_name`, `building`, `room`, `problem_description`, `img_path`, `created_at`, `updated_at`, `repair_started_at`, `completed_at`) VALUES
(14, 'REP-20251212-693', 'รอรับเรื่อง', 'นาธาน บุญสุภาพ', NULL, '009', '0991039926', '6708@lbtech.ac.th', 'Computer', NULL, NULL, NULL, 'ตึก 14', '1421', 'เสียมาก', 'uploads/REP-20251212-693.png', '2025-12-12 15:13:43', NULL, NULL, NULL),
(15, 'REP-20251212-427', 'กำลังซ่อม', 'นภดล สอนเจตน์', NULL, '67319090008', '0962863480', '67319090006@lbtech.ac.th', 'Other', NULL, NULL, NULL, 'Other', 'ห้อง IOT', 'หดหกหกดหกด', 'uploads/REP-20251212-427.png', '2025-12-12 15:22:03', NULL, NULL, NULL),
(16, 'REP-20251216-914', 'กำลังซ่อม', 'นภดล สอนเจตน์', NULL, '67319090008', '0962863480', '67319090006@lbtech.ac.th', 'Computer', NULL, NULL, NULL, 'ตึก 26', 'TC203', 'bluescreen', 'uploads/REP-20251216-914.png', '2025-12-16 10:03:45', '2026-01-01 14:31:22', '2026-01-01 14:31:22', '2026-01-01 14:25:48'),
(17, 'REP-20251216-973', 'กำลังซ่อม', 'TNK', NULL, 'TC10', '0822322992', 'waivers_potter.0i@lbtech.ac.th', 'AccessPoint', NULL, NULL, NULL, 'ตึก 14', '1425', 'No connection', 'uploads/REP-20251216-973.jpg', '2025-12-16 11:11:19', '2026-01-04 10:29:10', '2026-01-04 10:29:10', '2026-01-03 03:55:52'),
(18, 'REP-20260118-692', 'เสร็จสิ้น', 'นาธาน บุญสุภาพ', NULL, '67319090009', '0991039926', '67319090009@lbtech.ac.th', 'Computer', NULL, NULL, NULL, 'ตึก 26', 'TC201', 'ทำน้ำหก, มดขึ้นจอ, ส่งเสียงดัง ติ๊ด ๆ 3 ครั้งติดต่อกัน', 'uploads/REP-20260118-692.JPG', '2026-01-18 20:42:11', '2026-01-18 21:27:53', '2026-01-18 21:27:50', '2026-01-18 21:27:53'),
(19, 'REP-20260118-340', 'รอรับเรื่อง', 'นาธาน บุญสุภาพ', NULL, '67319090009', '0991039926', '67319090009@lbtech.ac.th', 'Computer', NULL, NULL, NULL, 'ตึก 14', '1425', 'ปัญหาในอนาคต', 'uploads/REP-20260118-340.jpg', '2026-01-18 21:07:41', NULL, NULL, NULL),
(20, 'REP-20260118-136', 'กำลังซ่อม', 'นภดล สอนเจตน์', NULL, '67319090008', '0962863480', '67319090008@lbtech.ac.th', 'Computer', NULL, NULL, NULL, 'ตึก 26', 'TC203', 'ฟหกวสดืกานห่สดิ้หก่า้ิยรานสฟ้ืดาฟกวหดื', 'uploads/REP-20260118-136.jpg', '2026-01-18 21:20:58', '2026-01-18 21:29:58', '2026-01-18 21:29:58', NULL),
(21, 'REP-20260118-993', 'รอรับเรื่อง', 'นาธาน บุญสุภาพ', NULL, '67319090009', '0991039926', '67319090009@lbtech.ac.th', 'Computer', NULL, NULL, NULL, 'ตึก 14', '1415', 'หน้าจอเปิดไม่ติด', 'uploads/REP-20260118-993.png', '2026-01-18 21:38:40', NULL, NULL, NULL),
(22, 'REP-20260119-655', 'เสร็จสิ้น', 'นภดล สอนเจตน์', '6731909008', '22005-02', '0962863480', '67319090008@lbtech.ac.th', 'AccessPoint', ' UBIQUITI UniFi (U7-Lite) Wireless BE5000', 'PF16DC85', 'COM-02', 'ตึก 26', 'TC203', 'เชื่อมต่อไม่ได้', 'uploads/REP-20260119-655.jpg', '2026-01-20 02:11:59', '2026-01-26 22:09:07', NULL, '2026-01-26 22:09:07'),
(23, 'REP-20260126-881', 'รอรับเรื่อง', 'นาธาน บุญสุภาพ', '67319090009', '', '0991039926', '67319090009@lbtech.ac.th', 'Computer', '', '', '', 'ตึก 14', '1415', 'คอมเปิดไม่ติด', 'uploads/REP-20260126-881.jpg', '2026-01-26 21:55:36', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
