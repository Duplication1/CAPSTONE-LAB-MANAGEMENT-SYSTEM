-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 24, 2025 at 03:51 PM
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
-- Database: `lab_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrators`
--

CREATE TABLE `administrators` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `access_level` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administrators`
--

INSERT INTO `administrators` (`id`, `username`, `password`, `employee_id`, `first_name`, `last_name`, `email`, `phone`, `position`, `access_level`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', '12345', 'ADM-001', 'Admin', 'User', 'admin@university.edu', NULL, 'System Administrator', 'super_admin', 'active', '2025-09-18 04:49:29', '2025-09-24 13:15:33'),
(2, 'lab_manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADM-002', 'Lisa', 'Anderson', 'lisa.anderson@university.edu', NULL, 'Lab Manager', 'admin', 'active', '2025-09-18 04:49:29', '2025-09-18 04:49:29');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `logout_time` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `session_duration` int(11) DEFAULT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','partial','absent') DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_logs`
--

INSERT INTO `attendance_logs` (`id`, `student_id`, `login_time`, `logout_time`, `ip_address`, `user_agent`, `session_duration`, `attendance_date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(2, 1, '2025-09-18 05:46:21', NULL, '192.168.1.1', 'Test Agent 1', NULL, '2025-09-18', 'present', NULL, '2025-09-18 05:46:21', '2025-09-18 05:46:21'),
(3, 1, '2025-09-18 05:46:22', NULL, '192.168.1.2', 'Test Agent 2', NULL, '2025-09-18', 'present', NULL, '2025-09-18 05:46:22', '2025-09-18 05:46:22'),
(4, 1, '2025-09-18 05:46:23', '2025-09-18 05:46:24', '192.168.1.3', 'Test Agent 3', -21599, '2025-09-18', 'present', NULL, '2025-09-18 05:46:23', '2025-09-18 05:46:24'),
(5, 1, '2025-09-18 05:47:39', '2025-09-18 05:47:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', -21580, '2025-09-18', 'present', NULL, '2025-09-18 05:47:39', '2025-09-18 05:47:59'),
(6, 1, '2025-09-23 13:51:04', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', NULL, '2025-09-23', 'present', NULL, '2025-09-23 13:51:04', '2025-09-23 13:51:04');

-- --------------------------------------------------------

--
-- Table structure for table `computers`
--

CREATE TABLE `computers` (
  `id` int(11) NOT NULL,
  `computer_name` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `mac_address` varchar(17) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `lab_room` varchar(50) DEFAULT NULL,
  `operating_system` varchar(100) DEFAULT NULL,
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('online','offline','maintenance') DEFAULT 'offline',
  `api_key` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `computers`
--

INSERT INTO `computers` (`id`, `computer_name`, `ip_address`, `mac_address`, `location`, `lab_room`, `operating_system`, `last_seen`, `status`, `api_key`, `created_at`, `updated_at`) VALUES
(1, 'TEST-PC-i60sbw', '::1', NULL, 'Auto Lab', 'AUTO-ASSIGNED', 'Windows', '2025-09-24 13:39:23', 'online', 'test-health-monitor-u5oqnj', '2025-09-24 13:39:23', '2025-09-24 13:39:23'),
(2, 'TEST-PC-c7wupo', '::1', NULL, 'Auto Lab', 'AUTO-ASSIGNED', 'Windows', '2025-09-24 13:39:56', 'online', 'test-health-monitor-7dhzbh', '2025-09-24 13:39:56', '2025-09-24 13:39:56'),
(3, 'TEST-PC-ciycy', '::1', NULL, 'Auto Lab', 'AUTO-ASSIGNED', 'Windows', '2025-09-24 13:41:35', 'online', 'test-health-monitor-kdfb3r', '2025-09-24 13:41:35', '2025-09-24 13:41:35');

-- --------------------------------------------------------

--
-- Table structure for table `email_config`
--

CREATE TABLE `email_config` (
  `id` int(11) NOT NULL,
  `smtp_host` varchar(255) NOT NULL DEFAULT 'smtp.gmail.com',
  `smtp_port` int(11) NOT NULL DEFAULT 587,
  `smtp_username` varchar(255) NOT NULL,
  `smtp_password` varchar(255) NOT NULL,
  `smtp_encryption` enum('tls','ssl','none') DEFAULT 'tls',
  `mail_from_address` varchar(255) NOT NULL,
  `mail_from_name` varchar(255) NOT NULL DEFAULT 'Lab Management System',
  `is_active` tinyint(1) DEFAULT 1,
  `daily_limit` int(11) DEFAULT 1000,
  `monthly_limit` int(11) DEFAULT 10000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message_content` text NOT NULL,
  `status` enum('pending','sent','delivered','failed') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivered_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `recipient_email`, `subject`, `message_content`, `status`, `error_message`, `sent_at`, `delivered_at`) VALUES
(1, 'student1@example.com', 'Lab Management System - Verification Code', 'Email verification code', 'sent', NULL, '2025-09-18 04:50:02', NULL),
(2, 'gamot.kim.fernandez@gmail.com', 'Lab Management System - Verification Code', 'Email verification code', 'sent', NULL, '2025-09-18 04:50:30', NULL),
(3, 'gamot.kim.fernandez@gmail.com', 'Lab Management System - Verification Code', 'Email verification code', 'sent', NULL, '2025-09-18 05:00:13', NULL),
(4, 'gamot.kim.fernandez@gmail.com', 'Lab Management System - Verification Code', 'Email verification code', 'sent', NULL, '2025-09-18 05:07:01', NULL),
(5, 'gamot.kim.fernandez@gmail.com', 'Lab Management System - Verification Code', 'Email verification code', 'sent', NULL, '2025-09-18 05:08:27', NULL),
(6, 'gamot.kim.fernandez@gmail.com', 'Lab Management System - Verification Code', 'Email verification code', 'sent', NULL, '2025-09-18 05:12:06', NULL),
(7, 'gamot.kim.fernandez@gmail.com', 'Lab Management System - Verification Code', 'Email verification code', 'sent', NULL, '2025-09-18 05:16:08', NULL),
(8, 'gamot.kim.fernandez@gmail.com', 'Lab Management System - Verification Code', 'Email verification code', 'sent', NULL, '2025-09-18 05:31:32', NULL),
(9, 'gamot.kim.fernandez@gmail.com', 'Lab Management System - Verification Code', 'Email verification code', 'sent', NULL, '2025-09-18 05:34:06', NULL),
(10, 'gamot.kim.fernandez@gmail.com', 'Lab Management System - Verification Code', 'Email verification code', 'sent', NULL, '2025-09-18 05:47:23', NULL),
(11, 'gamot.kim.fernandez@gmail.com', 'Lab Management System - Verification Code', 'Email verification code', 'sent', NULL, '2025-09-23 13:50:59', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `equipment_code` varchar(50) NOT NULL,
  `equipment_name` varchar(100) NOT NULL,
  `equipment_type` varchar(50) NOT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `laboratory_room_id` int(11) DEFAULT NULL,
  `status` enum('available','in_use','maintenance','damaged','retired') DEFAULT 'available',
  `purchase_date` date DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `equipment_code`, `equipment_name`, `equipment_type`, `brand`, `model`, `serial_number`, `laboratory_room_id`, `status`, `purchase_date`, `warranty_expiry`, `created_at`, `updated_at`) VALUES
(1, 'PC-001', 'Desktop Computer', 'Computer', 'Dell', 'OptiPlex 7090', NULL, 1, 'available', '2024-01-15', NULL, '2025-09-18 04:49:29', '2025-09-18 04:49:29'),
(2, 'PC-002', 'Desktop Computer', 'Computer', 'HP', 'EliteDesk 800', NULL, 1, 'available', '2024-01-15', NULL, '2025-09-18 04:49:29', '2025-09-18 04:49:29'),
(3, 'MON-001', 'Monitor', 'Display', 'Samsung', '24\" LED', NULL, 1, 'available', '2024-01-15', NULL, '2025-09-18 04:49:29', '2025-09-18 04:49:29'),
(4, 'KB-001', 'Keyboard', 'Input Device', 'Logitech', 'K380', NULL, 1, 'available', '2024-01-15', NULL, '2025-09-18 04:49:29', '2025-09-18 04:49:29'),
(5, 'MS-001', 'Mouse', 'Input Device', 'Logitech', 'M705', NULL, 1, 'available', '2024-01-15', NULL, '2025-09-18 04:49:29', '2025-09-18 04:49:29');

-- --------------------------------------------------------

--
-- Table structure for table `hardware_assets`
--

CREATE TABLE `hardware_assets` (
  `asset_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `condition` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hardware_assets`
--

INSERT INTO `hardware_assets` (`asset_id`, `name`, `type`, `condition`, `date`) VALUES
(4, 'Kim Gamot', NULL, 'Under Maintenance', '2025-09-03');

-- --------------------------------------------------------

--
-- Table structure for table `health_alerts`
--

CREATE TABLE `health_alerts` (
  `id` int(11) NOT NULL,
  `computer_name` varchar(255) NOT NULL,
  `alert_type` varchar(100) NOT NULL,
  `severity` enum('info','warning','critical') NOT NULL,
  `message` text NOT NULL,
  `value` decimal(10,2) DEFAULT NULL,
  `threshold_value` decimal(10,2) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `acknowledged` tinyint(1) DEFAULT 0,
  `acknowledged_by` varchar(255) DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `resolved` tinyint(1) DEFAULT 0,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_api_keys`
--

CREATE TABLE `health_api_keys` (
  `id` int(11) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `computer_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_api_keys`
--

INSERT INTO `health_api_keys` (`id`, `api_key`, `computer_name`, `description`, `is_active`, `created_at`, `last_used`) VALUES
(1, '0154d465ace3dec26da128a0aa31bbcc1f917b8409af18fe8b50307125d9c5a4', 'default', 'Default API key for health monitoring', 1, '2025-09-24 12:53:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `health_computers`
--

CREATE TABLE `health_computers` (
  `id` int(11) NOT NULL,
  `computer_name` varchar(255) NOT NULL,
  `hostname` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('online','offline','warning','critical') DEFAULT 'offline',
  `system_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`system_info`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_data`
--

CREATE TABLE `health_data` (
  `id` int(11) NOT NULL,
  `computer_id` int(11) NOT NULL,
  `cpu_usage` decimal(5,2) DEFAULT NULL,
  `cpu_temperature` decimal(5,2) DEFAULT NULL,
  `memory_total` bigint(20) DEFAULT NULL,
  `memory_used` bigint(20) DEFAULT NULL,
  `memory_usage_percent` decimal(5,2) DEFAULT NULL,
  `disk_total` bigint(20) DEFAULT NULL,
  `disk_used` bigint(20) DEFAULT NULL,
  `disk_usage_percent` decimal(5,2) DEFAULT NULL,
  `network_received` bigint(20) DEFAULT 0,
  `network_sent` bigint(20) DEFAULT 0,
  `uptime` bigint(20) DEFAULT NULL,
  `load_average` decimal(5,2) DEFAULT NULL,
  `processes_count` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_data`
--

INSERT INTO `health_data` (`id`, `computer_id`, `cpu_usage`, `cpu_temperature`, `memory_total`, `memory_used`, `memory_usage_percent`, `disk_total`, `disk_used`, `disk_usage_percent`, `network_received`, `network_sent`, `uptime`, `load_average`, `processes_count`, `timestamp`) VALUES
(1, 1, 45.20, 62.10, 8589934592, 4294967296, 50.00, 500000000000, 250000000000, 50.00, 1024000, 512000, 3600, 1.50, 120, '2025-09-24 13:39:23'),
(2, 2, 45.20, 62.10, 8589934592, 4294967296, 50.00, 500000000000, 250000000000, 50.00, 1024000, 512000, 3600, 1.50, 120, '2025-09-24 13:39:56'),
(3, 3, 45.20, 62.10, 8589934592, 4294967296, 50.00, 500000000000, 250000000000, 50.00, 1024000, 512000, 3600, 1.50, 120, '2025-09-24 13:41:35');

-- --------------------------------------------------------

--
-- Table structure for table `health_system_info`
--

CREATE TABLE `health_system_info` (
  `id` int(11) NOT NULL,
  `computer_name` varchar(255) NOT NULL,
  `system_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`system_info`)),
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `health_thresholds`
--

CREATE TABLE `health_thresholds` (
  `id` int(11) NOT NULL,
  `metric_name` varchar(50) NOT NULL,
  `warning_threshold` decimal(8,2) NOT NULL,
  `critical_threshold` decimal(8,2) NOT NULL,
  `description` text DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_thresholds`
--

INSERT INTO `health_thresholds` (`id`, `metric_name`, `warning_threshold`, `critical_threshold`, `description`, `is_enabled`, `created_at`, `updated_at`) VALUES
(1, 'cpu_usage', 75.00, 90.00, 'CPU usage percentage', 1, '2025-09-24 13:19:33', '2025-09-24 13:19:33'),
(2, 'memory_usage', 80.00, 95.00, 'Memory usage percentage', 1, '2025-09-24 13:19:33', '2025-09-24 13:19:33'),
(3, 'disk_usage', 85.00, 95.00, 'Disk usage percentage', 1, '2025-09-24 13:19:33', '2025-09-24 13:19:33'),
(4, 'cpu_temperature', 70.00, 85.00, 'CPU temperature in Celsius', 1, '2025-09-24 13:19:33', '2025-09-24 13:19:33');

-- --------------------------------------------------------

--
-- Table structure for table `it_staff`
--

CREATE TABLE `it_staff` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT 'IT Department',
  `position` varchar(100) DEFAULT NULL,
  `access_level` enum('basic','advanced','full') DEFAULT 'basic',
  `status` enum('active','inactive','on_leave') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `it_staff`
--

INSERT INTO `it_staff` (`id`, `username`, `password`, `employee_id`, `first_name`, `last_name`, `email`, `phone`, `department`, `position`, `access_level`, `status`, `created_at`, `updated_at`) VALUES
(1, 'tech_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'IT-001', 'David', 'Johnson', 'david.johnson@university.edu', NULL, 'IT Department', 'Lab Technician', 'advanced', 'active', '2025-09-18 04:49:29', '2025-09-18 04:49:29'),
(2, 'sys_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'IT-002', 'Sarah', 'Davis', 'sarah.davis@university.edu', NULL, 'IT Department', 'System Administrator', 'full', 'active', '2025-09-18 04:49:29', '2025-09-18 04:49:29');

-- --------------------------------------------------------

--
-- Table structure for table `laboratory_rooms`
--

CREATE TABLE `laboratory_rooms` (
  `id` int(11) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `room_name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `equipment_count` int(11) DEFAULT 0,
  `status` enum('available','occupied','maintenance','closed') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `laboratory_rooms`
--

INSERT INTO `laboratory_rooms` (`id`, `room_number`, `room_name`, `capacity`, `equipment_count`, `status`, `created_at`, `updated_at`) VALUES
(1, 'LAB-101', 'Computer Laboratory 1', 30, 0, 'available', '2025-09-18 04:49:29', '2025-09-18 04:49:29'),
(2, 'LAB-102', 'Computer Laboratory 2', 25, 0, 'available', '2025-09-18 04:49:29', '2025-09-18 04:49:29'),
(3, 'LAB-201', 'Advanced Computing Lab', 20, 0, 'available', '2025-09-18 04:49:29', '2025-09-18 04:49:29'),
(4, 'LAB-301', 'Research Laboratory', 15, 0, 'available', '2025-09-18 04:49:29', '2025-09-18 04:49:29');

-- --------------------------------------------------------

--
-- Table structure for table `lab_sessions`
--

CREATE TABLE `lab_sessions` (
  `id` int(11) NOT NULL,
  `session_name` varchar(100) NOT NULL,
  `laboratory_room_id` int(11) NOT NULL,
  `professor_id` int(11) DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `max_students` int(11) DEFAULT 30,
  `status` enum('scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_sessions`
--

INSERT INTO `lab_sessions` (`id`, `session_name`, `laboratory_room_id`, `professor_id`, `start_time`, `end_time`, `max_students`, `status`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Programming', 1, 2, '2025-09-23 21:58:00', '2025-09-23 23:58:00', 30, 'completed', '', '2025-09-23 13:58:10', '2025-09-23 14:00:03');

-- --------------------------------------------------------

--
-- Table structure for table `professors`
--

CREATE TABLE `professors` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `specialization` varchar(200) DEFAULT NULL,
  `status` enum('active','inactive','on_leave') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `professors`
--

INSERT INTO `professors` (`id`, `username`, `password`, `employee_id`, `first_name`, `last_name`, `email`, `phone`, `department`, `position`, `specialization`, `status`, `created_at`, `updated_at`) VALUES
(1, 'prof_garcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'PROF-001', 'Maria', 'Garcia', 'maria.garcia@university.edu', NULL, 'Computer Science', 'Professor', 'Software Engineering', 'active', '2025-09-18 04:49:29', '2025-09-18 04:49:29'),
(2, 'prof_brown', '12345', 'PROF-002', 'Robert', 'Brown', 'robert.brown@university.edu', NULL, 'Information Technology', 'Associate Professor', 'Database Systems', 'active', '2025-09-18 04:49:29', '2025-09-18 05:29:26');

-- --------------------------------------------------------

--
-- Table structure for table `session_attendance`
--

CREATE TABLE `session_attendance` (
  `id` int(11) NOT NULL,
  `lab_session_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `attendance_status` enum('present','absent','late') DEFAULT 'present',
  `check_in_time` timestamp NULL DEFAULT NULL,
  `check_out_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `session_attendance`
--

INSERT INTO `session_attendance` (`id`, `lab_session_id`, `student_id`, `attendance_status`, `check_in_time`, `check_out_time`, `created_at`) VALUES
(1, 1, 1, 'present', '2025-09-23 13:58:26', NULL, '2025-09-23 13:58:26');

-- --------------------------------------------------------

--
-- Table structure for table `software_assets`
--

CREATE TABLE `software_assets` (
  `asset_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `license_key` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `software_assets`
--

INSERT INTO `software_assets` (`asset_id`, `name`, `license_key`, `date`, `type`) VALUES
(2, 'Kim Gamot', '21312312312', '2025-09-23', NULL),
(5, '3232', '21312312312', '2025-09-23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year') NOT NULL,
  `section` varchar(10) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `username`, `password`, `student_id`, `first_name`, `last_name`, `email`, `phone`, `course`, `year_level`, `section`, `status`, `created_at`, `updated_at`) VALUES
(1, 'john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-001', 'John', 'Doe', 'gamot.kim.fernandez@gmail.com', NULL, 'Computer Science', '3rd Year', 'A', 'active', '2025-09-18 04:49:29', '2025-09-18 04:50:21'),
(2, 'jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-002', 'Jane', 'Smith', 'student2@example.com', NULL, 'Information Technology', '2nd Year', 'B', 'active', '2025-09-18 04:49:29', '2025-09-18 04:49:49'),
(3, 'mike_wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-003', 'Mike', 'Wilson', 'student3@example.com', NULL, 'Computer Engineering', '4th Year', 'A', 'active', '2025-09-18 04:49:29', '2025-09-18 04:49:49');

-- --------------------------------------------------------

--
-- Table structure for table `user_2fa_codes`
--

CREATE TABLE `user_2fa_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('student','professor','itstaff','admin') NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `verification_code` varchar(6) NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_2fa_codes`
--

INSERT INTO `user_2fa_codes` (`id`, `user_id`, `user_type`, `email_address`, `verification_code`, `is_used`, `attempts`, `max_attempts`, `expires_at`, `created_at`) VALUES
(1, 1, 'student', 'student1@example.com', '782576', 1, 0, 3, '2025-09-18 04:50:27', '2025-09-18 04:49:58'),
(2, 1, 'student', 'gamot.kim.fernandez@gmail.com', '529513', 1, 0, 3, '2025-09-18 05:00:09', '2025-09-18 04:50:27'),
(3, 1, 'student', 'gamot.kim.fernandez@gmail.com', '247331', 1, 0, 3, '2025-09-18 05:06:57', '2025-09-18 05:00:09'),
(4, 1, 'student', 'gamot.kim.fernandez@gmail.com', '279637', 1, 1, 3, '2025-09-18 05:08:24', '2025-09-18 05:06:57'),
(5, 1, 'student', 'gamot.kim.fernandez@gmail.com', '340753', 1, 1, 3, '2025-09-18 05:12:02', '2025-09-18 05:08:24'),
(6, 1, 'student', 'gamot.kim.fernandez@gmail.com', '692376', 1, 0, 3, '2025-09-18 05:12:26', '2025-09-18 05:12:02'),
(7, 1, 'student', 'gamot.kim.fernandez@gmail.com', '983135', 1, 0, 3, '2025-09-18 05:16:22', '2025-09-18 05:16:05'),
(8, 1, 'student', 'gamot.kim.fernandez@gmail.com', '854406', 1, 0, 3, '2025-09-18 05:31:45', '2025-09-18 05:31:28'),
(9, 1, 'student', 'gamot.kim.fernandez@gmail.com', '340309', 1, 0, 3, '2025-09-18 05:34:13', '2025-09-18 05:34:02'),
(10, 1, 'student', 'gamot.kim.fernandez@gmail.com', '218468', 1, 0, 3, '2025-09-18 05:47:39', '2025-09-18 05:47:20'),
(11, 1, 'student', 'gamot.kim.fernandez@gmail.com', '408160', 1, 0, 3, '2025-09-23 13:51:04', '2025-09-23 13:50:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrators`
--
ALTER TABLE `administrators`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_date` (`student_id`,`attendance_date`),
  ADD KEY `idx_attendance_date` (`attendance_date`),
  ADD KEY `idx_student_attendance_date` (`student_id`,`attendance_date`,`login_time`);

--
-- Indexes for table `computers`
--
ALTER TABLE `computers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `computer_name` (`computer_name`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_last_seen` (`last_seen`);

--
-- Indexes for table `email_config`
--
ALTER TABLE `email_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_status` (`recipient_email`,`status`),
  ADD KEY `idx_sent_date` (`sent_at`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipment_code` (`equipment_code`),
  ADD KEY `laboratory_room_id` (`laboratory_room_id`);

--
-- Indexes for table `hardware_assets`
--
ALTER TABLE `hardware_assets`
  ADD PRIMARY KEY (`asset_id`);

--
-- Indexes for table `health_alerts`
--
ALTER TABLE `health_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_computer_name` (`computer_name`),
  ADD KEY `idx_severity` (`severity`),
  ADD KEY `idx_timestamp` (`timestamp`),
  ADD KEY `idx_acknowledged` (`acknowledged`);

--
-- Indexes for table `health_api_keys`
--
ALTER TABLE `health_api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`),
  ADD KEY `idx_api_key` (`api_key`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `health_computers`
--
ALTER TABLE `health_computers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `computer_name` (`computer_name`),
  ADD KEY `idx_computer_name` (`computer_name`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_last_seen` (`last_seen`);

--
-- Indexes for table `health_data`
--
ALTER TABLE `health_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `computer_id` (`computer_id`),
  ADD KEY `idx_timestamp` (`timestamp`);

--
-- Indexes for table `health_system_info`
--
ALTER TABLE `health_system_info`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `computer_name` (`computer_name`);

--
-- Indexes for table `health_thresholds`
--
ALTER TABLE `health_thresholds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `metric_name` (`metric_name`);

--
-- Indexes for table `it_staff`
--
ALTER TABLE `it_staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `laboratory_rooms`
--
ALTER TABLE `laboratory_rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_number` (`room_number`);

--
-- Indexes for table `lab_sessions`
--
ALTER TABLE `lab_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laboratory_room_id` (`laboratory_room_id`),
  ADD KEY `professor_id` (`professor_id`);

--
-- Indexes for table `professors`
--
ALTER TABLE `professors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `session_attendance`
--
ALTER TABLE `session_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`lab_session_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `software_assets`
--
ALTER TABLE `software_assets`
  ADD PRIMARY KEY (`asset_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_2fa_codes`
--
ALTER TABLE `user_2fa_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_lookup` (`user_id`,`user_type`),
  ADD KEY `idx_email_code` (`email_address`,`verification_code`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrators`
--
ALTER TABLE `administrators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `computers`
--
ALTER TABLE `computers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `email_config`
--
ALTER TABLE `email_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `hardware_assets`
--
ALTER TABLE `hardware_assets`
  MODIFY `asset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `health_alerts`
--
ALTER TABLE `health_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `health_api_keys`
--
ALTER TABLE `health_api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `health_computers`
--
ALTER TABLE `health_computers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `health_data`
--
ALTER TABLE `health_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `health_system_info`
--
ALTER TABLE `health_system_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `health_thresholds`
--
ALTER TABLE `health_thresholds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `it_staff`
--
ALTER TABLE `it_staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `laboratory_rooms`
--
ALTER TABLE `laboratory_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lab_sessions`
--
ALTER TABLE `lab_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `professors`
--
ALTER TABLE `professors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `session_attendance`
--
ALTER TABLE `session_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `software_assets`
--
ALTER TABLE `software_assets`
  MODIFY `asset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_2fa_codes`
--
ALTER TABLE `user_2fa_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`laboratory_room_id`) REFERENCES `laboratory_rooms` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `health_alerts`
--
ALTER TABLE `health_alerts`
  ADD CONSTRAINT `health_alerts_ibfk_1` FOREIGN KEY (`computer_name`) REFERENCES `health_computers` (`computer_name`) ON DELETE CASCADE;

--
-- Constraints for table `health_data`
--
ALTER TABLE `health_data`
  ADD CONSTRAINT `health_data_ibfk_1` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `health_system_info`
--
ALTER TABLE `health_system_info`
  ADD CONSTRAINT `health_system_info_ibfk_1` FOREIGN KEY (`computer_name`) REFERENCES `health_computers` (`computer_name`) ON DELETE CASCADE;

--
-- Constraints for table `lab_sessions`
--
ALTER TABLE `lab_sessions`
  ADD CONSTRAINT `lab_sessions_ibfk_1` FOREIGN KEY (`laboratory_room_id`) REFERENCES `laboratory_rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lab_sessions_ibfk_2` FOREIGN KEY (`professor_id`) REFERENCES `professors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `session_attendance`
--
ALTER TABLE `session_attendance`
  ADD CONSTRAINT `session_attendance_ibfk_1` FOREIGN KEY (`lab_session_id`) REFERENCES `lab_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `session_attendance_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
