<?php
/**
 * Health Monitoring Controller
 * Handles health data collection and management
 */

class HealthController {
    private $database;
    private $conn;
    private $notificationService;
    
    public function __construct($database) {
        $this->database = $database;
        $this->conn = $database->getConnection();
        $this->initializeTables();
        
        // Initialize notification service
        require_once __DIR__ . '/../../model/health_notification_service.php';
        $this->notificationService = new HealthNotificationService($this->database);
    }
    
    private function initializeTables() {
        $this->createComputersTable();
        $this->createHealthDataTable();
        $this->createAlertsTable();
        $this->createSystemInfoTable();
    }
    
    private function createComputersTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS health_computers (
                id INT AUTO_INCREMENT PRIMARY KEY,
                computer_name VARCHAR(255) UNIQUE NOT NULL,
                hostname VARCHAR(255),
                ip_address VARCHAR(45),
                last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status ENUM('online', 'offline', 'warning', 'critical') DEFAULT 'offline',
                system_info JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_computer_name (computer_name),
                INDEX idx_status (status),
                INDEX idx_last_seen (last_seen)
            )
        ";
        $this->conn->exec($sql);
    }
    
    private function createHealthDataTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS health_data (
                id INT AUTO_INCREMENT PRIMARY KEY,
                computer_name VARCHAR(255) NOT NULL,
                timestamp TIMESTAMP NOT NULL,
                cpu_usage DECIMAL(5,2),
                cpu_temperature DECIMAL(5,2),
                memory_total BIGINT,
                memory_used BIGINT,
                memory_percent DECIMAL(5,2),
                disk_total BIGINT,
                disk_used BIGINT,
                disk_percent DECIMAL(5,2),
                network_bytes_sent BIGINT DEFAULT 0,
                network_bytes_received BIGINT DEFAULT 0,
                uptime BIGINT,
                load_average JSON,
                active_processes INT,
                top_processes JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_computer_timestamp (computer_name, timestamp),
                INDEX idx_timestamp (timestamp),
                FOREIGN KEY (computer_name) REFERENCES health_computers(computer_name) ON DELETE CASCADE
            )
        ";
        $this->conn->exec($sql);
    }
    
    private function createAlertsTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS health_alerts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                computer_name VARCHAR(255) NOT NULL,
                alert_type VARCHAR(100) NOT NULL,
                severity ENUM('info', 'warning', 'critical') NOT NULL,
                message TEXT NOT NULL,
                value DECIMAL(10,2),
                threshold_value DECIMAL(10,2),
                timestamp TIMESTAMP NOT NULL,
                acknowledged BOOLEAN DEFAULT FALSE,
                acknowledged_by VARCHAR(255),
                acknowledged_at TIMESTAMP NULL,
                resolved BOOLEAN DEFAULT FALSE,
                resolved_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_computer_name (computer_name),
                INDEX idx_severity (severity),
                INDEX idx_timestamp (timestamp),
                INDEX idx_acknowledged (acknowledged),
                FOREIGN KEY (computer_name) REFERENCES health_computers(computer_name) ON DELETE CASCADE
            )
        ";
        $this->conn->exec($sql);
    }
    
    private function createSystemInfoTable() {
        $sql = "
            CREATE TABLE IF NOT EXISTS health_system_info (
                id INT AUTO_INCREMENT PRIMARY KEY,
                computer_name VARCHAR(255) UNIQUE NOT NULL,
                system_info JSON NOT NULL,
                timestamp TIMESTAMP NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (computer_name) REFERENCES health_computers(computer_name) ON DELETE CASCADE
            )
        ";
        $this->conn->exec($sql);
    }
    
    public function testConnection() {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Health monitoring API is online',
            'timestamp' => date('c'),
            'server_time' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function receiveHealthData() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                return;
            }
            
            $computerName = $_SERVER['HEALTH_API_COMPUTER'] ?? $input['computer_name'] ?? null;
            
            if (!$computerName) {
                http_response_code(400);
                echo json_encode(['error' => 'Computer name is required']);
                return;
            }
            
            // Ensure computer exists
            $this->ensureComputerExists($computerName);
            
            // Insert health data
            $stmt = $this->conn->prepare("
                INSERT INTO health_data (
                    computer_name, timestamp, cpu_usage, cpu_temperature, 
                    memory_total, memory_used, memory_percent,
                    disk_total, disk_used, disk_percent,
                    network_bytes_sent, network_bytes_received,
                    uptime, load_average, active_processes, top_processes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $timestamp = $input['timestamp'] ?? date('Y-m-d H:i:s');
            
            $stmt->execute([
                $computerName,
                $timestamp,
                $input['cpu_usage'] ?? null,
                $input['cpu_temperature'] ?? null,
                $input['memory_total'] ?? null,
                $input['memory_used'] ?? null,
                $input['memory_percent'] ?? null,
                $input['disk_total'] ?? null,
                $input['disk_used'] ?? null,
                $input['disk_percent'] ?? null,
                $input['network_bytes_sent'] ?? 0,
                $input['network_bytes_received'] ?? 0,
                $input['uptime'] ?? null,
                isset($input['load_average']) ? json_encode($input['load_average']) : null,
                $input['active_processes'] ?? null,
                isset($input['top_processes']) ? json_encode($input['top_processes']) : null
            ]);
            
            // Update computer status
            $this->updateComputerStatus($computerName, $input);
            
            // Cleanup old data (keep last 7 days)
            $this->cleanupOldData();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Health data received',
                'computer_name' => $computerName,
                'timestamp' => $timestamp
            ]);
            
        } catch (Exception $e) {
            error_log("Error receiving health data: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to store health data']);
        }
    }
    
    public function receiveAlert() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                return;
            }
            
            $computerName = $_SERVER['HEALTH_API_COMPUTER'] ?? $input['computer_name'] ?? null;
            
            if (!$computerName) {
                http_response_code(400);
                echo json_encode(['error' => 'Computer name is required']);
                return;
            }
            
            // Ensure computer exists
            $this->ensureComputerExists($computerName);
            
            // Insert alert
            $stmt = $this->conn->prepare("
                INSERT INTO health_alerts (
                    computer_name, alert_type, severity, message, 
                    value, threshold_value, timestamp
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $timestamp = $input['timestamp'] ?? date('Y-m-d H:i:s');
            
            $stmt->execute([
                $computerName,
                $input['alert_type'] ?? 'unknown',
                $input['severity'] ?? 'warning',
                $input['message'] ?? 'No message provided',
                $input['value'] ?? null,
                $input['threshold'] ?? null,
                $timestamp
            ]);
            
            // Update computer status based on severity
            if ($input['severity'] === 'critical') {
                $this->updateComputerStatus($computerName, [], 'critical');
            } elseif ($input['severity'] === 'warning') {
                $this->updateComputerStatus($computerName, [], 'warning');
            }
            
            // Send email notification for critical alerts
            if ($input['severity'] === 'critical') {
                $this->sendAlertNotification($computerName, $input);
            }
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Alert received',
                'computer_name' => $computerName,
                'alert_type' => $input['alert_type'] ?? 'unknown'
            ]);
            
        } catch (Exception $e) {
            error_log("Error receiving alert: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to store alert']);
        }
    }
    
    public function receiveSystemInfo() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                return;
            }
            
            $computerName = $_SERVER['HEALTH_API_COMPUTER'] ?? $input['computer_name'] ?? null;
            
            if (!$computerName) {
                http_response_code(400);
                echo json_encode(['error' => 'Computer name is required']);
                return;
            }
            
            // Ensure computer exists
            $this->ensureComputerExists($computerName);
            
            // Insert or update system info
            $stmt = $this->conn->prepare("
                INSERT INTO health_system_info (computer_name, system_info, timestamp)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                system_info = VALUES(system_info),
                timestamp = VALUES(timestamp),
                updated_at = CURRENT_TIMESTAMP
            ");
            
            $timestamp = $input['timestamp'] ?? date('Y-m-d H:i:s');
            $systemInfo = $input['system_info'] ?? '{}';
            
            if (is_array($systemInfo)) {
                $systemInfo = json_encode($systemInfo);
            }
            
            $stmt->execute([
                $computerName,
                $systemInfo,
                $timestamp
            ]);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'System info received',
                'computer_name' => $computerName
            ]);
            
        } catch (Exception $e) {
            error_log("Error receiving system info: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to store system info']);
        }
    }
    
    public function registerComputer() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON data']);
                return;
            }
            
            $computerName = $_SERVER['HEALTH_API_COMPUTER'] ?? $input['computer_name'] ?? null;
            
            if (!$computerName) {
                http_response_code(400);
                echo json_encode(['error' => 'Computer name is required']);
                return;
            }
            
            // Register/update computer
            $this->ensureComputerExists($computerName, $input);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Computer registered',
                'computer_name' => $computerName
            ]);
            
        } catch (Exception $e) {
            error_log("Error registering computer: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to register computer']);
        }
    }
    
    public function receiveHeartbeat() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $computerName = $_SERVER['HEALTH_API_COMPUTER'] ?? $input['computer_name'] ?? null;
            
            if (!$computerName) {
                http_response_code(400);
                echo json_encode(['error' => 'Computer name is required']);
                return;
            }
            
            // Update last seen
            $this->ensureComputerExists($computerName);
            
            $stmt = $this->conn->prepare("
                UPDATE health_computers 
                SET last_seen = CURRENT_TIMESTAMP, status = 'online'
                WHERE computer_name = ?
            ");
            $stmt->execute([$computerName]);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Heartbeat received',
                'computer_name' => $computerName,
                'server_time' => date('Y-m-d H:i:s')
            ]);
            
        } catch (Exception $e) {
            error_log("Error receiving heartbeat: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to process heartbeat']);
        }
    }
    
    public function getSettings($computerName) {
        try {
            // Return default settings for now
            // In the future, this could be customized per computer
            $settings = [
                'monitoring_interval' => 30,
                'alert_thresholds' => [
                    'cpu' => 80,
                    'memory' => 85,
                    'disk' => 90,
                    'temperature' => 70
                ],
                'enabled_monitoring' => [
                    'cpu' => true,
                    'memory' => true,
                    'disk' => true,
                    'network' => true,
                    'temperature' => true,
                    'processes' => true
                ]
            ];
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'computer_name' => $computerName,
                'settings' => $settings
            ]);
            
        } catch (Exception $e) {
            error_log("Error getting settings: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to get settings']);
        }
    }
    
    public function getComputers() {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    c.*,
                    COALESCE(latest.cpu_usage, 0) as current_cpu,
                    COALESCE(latest.memory_percent, 0) as current_memory,
                    COALESCE(latest.disk_percent, 0) as current_disk,
                    alert_counts.critical_alerts,
                    alert_counts.warning_alerts
                FROM health_computers c
                LEFT JOIN (
                    SELECT 
                        computer_name,
                        cpu_usage,
                        memory_percent,
                        disk_percent,
                        ROW_NUMBER() OVER (PARTITION BY computer_name ORDER BY timestamp DESC) as rn
                    FROM health_data
                ) latest ON c.computer_name = latest.computer_name AND latest.rn = 1
                LEFT JOIN (
                    SELECT 
                        computer_name,
                        SUM(CASE WHEN severity = 'critical' AND NOT resolved THEN 1 ELSE 0 END) as critical_alerts,
                        SUM(CASE WHEN severity = 'warning' AND NOT resolved THEN 1 ELSE 0 END) as warning_alerts
                    FROM health_alerts
                    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    GROUP BY computer_name
                ) alert_counts ON c.computer_name = alert_counts.computer_name
                ORDER BY c.last_seen DESC
            ");
            $stmt->execute();
            
            $computers = $stmt->fetchAll();
            
            // Update offline status for computers not seen recently
            $this->updateOfflineComputers();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'computers' => $computers
            ]);
            
        } catch (Exception $e) {
            error_log("Error getting computers: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to get computers']);
        }
    }
    
    public function getComputerDetails($computerName) {
        try {
            // Get computer info
            $stmt = $this->conn->prepare("
                SELECT * FROM health_computers WHERE computer_name = ?
            ");
            $stmt->execute([$computerName]);
            $computer = $stmt->fetch();
            
            if (!$computer) {
                http_response_code(404);
                echo json_encode(['error' => 'Computer not found']);
                return;
            }
            
            // Get latest health data (last 24 hours)
            $stmt = $this->conn->prepare("
                SELECT * FROM health_data 
                WHERE computer_name = ? 
                AND timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                ORDER BY timestamp DESC
                LIMIT 100
            ");
            $stmt->execute([$computerName]);
            $healthData = $stmt->fetchAll();
            
            // Get recent alerts
            $stmt = $this->conn->prepare("
                SELECT * FROM health_alerts 
                WHERE computer_name = ? 
                AND timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY timestamp DESC
                LIMIT 50
            ");
            $stmt->execute([$computerName]);
            $alerts = $stmt->fetchAll();
            
            // Get system info
            $stmt = $this->conn->prepare("
                SELECT system_info FROM health_system_info 
                WHERE computer_name = ?
            ");
            $stmt->execute([$computerName]);
            $systemInfoRow = $stmt->fetch();
            $systemInfo = $systemInfoRow ? json_decode($systemInfoRow['system_info'], true) : null;
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'computer' => $computer,
                'health_data' => $healthData,
                'alerts' => $alerts,
                'system_info' => $systemInfo
            ]);
            
        } catch (Exception $e) {
            error_log("Error getting computer details: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to get computer details']);
        }
    }
    
    public function getAlerts() {
        try {
            $limit = $_GET['limit'] ?? 100;
            $severity = $_GET['severity'] ?? null;
            $computerName = $_GET['computer_name'] ?? null;
            $unacknowledged = $_GET['unacknowledged'] ?? false;
            
            $whereClause = "WHERE 1=1";
            $params = [];
            
            if ($severity) {
                $whereClause .= " AND severity = ?";
                $params[] = $severity;
            }
            
            if ($computerName) {
                $whereClause .= " AND computer_name = ?";
                $params[] = $computerName;
            }
            
            if ($unacknowledged) {
                $whereClause .= " AND acknowledged = FALSE";
            }
            
            $stmt = $this->conn->prepare("
                SELECT 
                    a.*,
                    c.hostname,
                    c.status as computer_status
                FROM health_alerts a
                LEFT JOIN health_computers c ON a.computer_name = c.computer_name
                $whereClause
                ORDER BY a.timestamp DESC
                LIMIT ?
            ");
            
            $params[] = (int)$limit;
            $stmt->execute($params);
            $alerts = $stmt->fetchAll();
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'alerts' => $alerts
            ]);
            
        } catch (Exception $e) {
            error_log("Error getting alerts: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to get alerts']);
        }
    }
    
    private function ensureComputerExists($computerName, $data = []) {
        $stmt = $this->conn->prepare("
            INSERT INTO health_computers (computer_name, hostname, ip_address, status, last_seen)
            VALUES (?, ?, ?, 'online', CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE 
            last_seen = CURRENT_TIMESTAMP,
            hostname = COALESCE(VALUES(hostname), hostname),
            ip_address = COALESCE(VALUES(ip_address), ip_address)
        ");
        
        $hostname = $data['hostname'] ?? $_SERVER['REMOTE_HOST'] ?? null;
        $ipAddress = $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        
        $stmt->execute([$computerName, $hostname, $ipAddress]);
    }
    
    private function updateComputerStatus($computerName, $healthData = [], $forceStatus = null) {
        if ($forceStatus) {
            $status = $forceStatus;
        } else {
            // Determine status based on health data
            $status = 'online';
            
            if (isset($healthData['cpu_usage']) && $healthData['cpu_usage'] > 95) {
                $status = 'critical';
            } elseif (isset($healthData['memory_percent']) && $healthData['memory_percent'] > 95) {
                $status = 'critical';
            } elseif (isset($healthData['disk_percent']) && $healthData['disk_percent'] > 95) {
                $status = 'critical';
            } elseif (
                (isset($healthData['cpu_usage']) && $healthData['cpu_usage'] > 80) ||
                (isset($healthData['memory_percent']) && $healthData['memory_percent'] > 85) ||
                (isset($healthData['disk_percent']) && $healthData['disk_percent'] > 90)
            ) {
                $status = 'warning';
            }
        }
        
        $stmt = $this->conn->prepare("
            UPDATE health_computers 
            SET status = ?, last_seen = CURRENT_TIMESTAMP 
            WHERE computer_name = ?
        ");
        $stmt->execute([$status, $computerName]);
    }
    
    private function updateOfflineComputers() {
        // Mark computers as offline if not seen in the last 5 minutes
        $stmt = $this->conn->prepare("
            UPDATE health_computers 
            SET status = 'offline' 
            WHERE last_seen < DATE_SUB(NOW(), INTERVAL 5 MINUTE) 
            AND status != 'offline'
        ");
        $stmt->execute();
    }
    
    private function cleanupOldData() {
        // Remove health data older than 30 days
        $stmt = $this->conn->prepare("
            DELETE FROM health_data 
            WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute();
        
        // Remove resolved alerts older than 90 days
        $stmt = $this->conn->prepare("
            DELETE FROM health_alerts 
            WHERE resolved = TRUE 
            AND resolved_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
        ");
        $stmt->execute();
    }
    
    private function sendAlertNotification($computerName, $alert) {
        try {
            // Log the alert
            error_log("CRITICAL ALERT - {$computerName}: {$alert['message']}");
            
            // Prepare alert data for notification service
            $alertData = [
                'computer_name' => $computerName,
                'alert_type' => $alert['alert_type'] ?? 'unknown',
                'severity' => $alert['severity'] ?? 'critical',
                'message' => $alert['message'] ?? 'No message provided',
                'value' => $alert['value'] ?? null,
                'threshold_value' => $alert['threshold'] ?? null,
                'timestamp' => $alert['timestamp'] ?? date('Y-m-d H:i:s')
            ];
            
            // Send email notification using the notification service
            $notificationSent = $this->notificationService->sendAlertNotification($alertData);
            
            if ($notificationSent) {
                error_log("Alert notification sent successfully for {$computerName}");
            } else {
                error_log("Failed to send alert notification for {$computerName}");
            }
            
        } catch (Exception $e) {
            error_log("Failed to send alert notification: " . $e->getMessage());
        }
    }
    
    private function getAdminEmails() {
        try {
            $stmt = $this->conn->prepare("
                SELECT email FROM users 
                WHERE user_type = 'admin' AND email IS NOT NULL
            ");
            $stmt->execute();
            
            $emails = [];
            while ($row = $stmt->fetch()) {
                $emails[] = $row['email'];
            }
            
            return $emails;
        } catch (Exception $e) {
            return ['admin@labsystem.local']; // Default fallback
        }
    }
}