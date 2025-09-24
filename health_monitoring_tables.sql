-- Health Monitoring Tables for Lab Management System
-- Add these tables to support the desktop health monitoring agents

-- Table to store computer information
CREATE TABLE IF NOT EXISTS `computers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `computer_name` (`computer_name`),
  KEY `idx_status` (`status`),
  KEY `idx_last_seen` (`last_seen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table to store system health data
CREATE TABLE IF NOT EXISTS `health_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `computer_id` (`computer_id`),
  KEY `idx_timestamp` (`timestamp`),
  CONSTRAINT `health_data_ibfk_1` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table to store health alerts and notifications
CREATE TABLE IF NOT EXISTS `health_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `computer_id` int(11) NOT NULL,
  `alert_type` enum('cpu','memory','disk','temperature','offline') NOT NULL,
  `severity` enum('info','warning','critical') NOT NULL,
  `message` text NOT NULL,
  `threshold_value` decimal(8,2) DEFAULT NULL,
  `current_value` decimal(8,2) DEFAULT NULL,
  `is_resolved` tinyint(1) DEFAULT 0,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `computer_id` (`computer_id`),
  KEY `idx_severity` (`severity`),
  KEY `idx_resolved` (`is_resolved`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `health_alerts_ibfk_1` FOREIGN KEY (`computer_id`) REFERENCES `computers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table to store alert thresholds and configuration
CREATE TABLE IF NOT EXISTS `health_thresholds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `metric_name` varchar(50) NOT NULL,
  `warning_threshold` decimal(8,2) NOT NULL,
  `critical_threshold` decimal(8,2) NOT NULL,
  `description` text DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `metric_name` (`metric_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default thresholds
INSERT IGNORE INTO `health_thresholds` (`metric_name`, `warning_threshold`, `critical_threshold`, `description`) VALUES
('cpu_usage', 75.00, 90.00, 'CPU usage percentage'),
('memory_usage', 80.00, 95.00, 'Memory usage percentage'),
('disk_usage', 85.00, 95.00, 'Disk usage percentage'),
('cpu_temperature', 70.00, 85.00, 'CPU temperature in Celsius');

-- Add some sample computers for testing
INSERT IGNORE INTO `computers` (`computer_name`, `ip_address`, `location`, `lab_room`, `operating_system`, `status`, `api_key`) VALUES
('LAB-PC-001', '192.168.1.100', 'Computer Lab 1', 'LAB-101', 'Windows 10', 'online', 'test-api-key-001'),
('LAB-PC-002', '192.168.1.101', 'Computer Lab 1', 'LAB-101', 'Windows 10', 'offline', 'test-api-key-002'),
('LAB-PC-003', '192.168.1.102', 'Computer Lab 2', 'LAB-201', 'Windows 11', 'online', 'test-api-key-003');