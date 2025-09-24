<?php
/**
 * Health Monitoring API - Lab Management System
 * Handles health data from desktop monitoring agents
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../model/database.php';

class HealthMonitoringAPI {
    private $db;
    private $connection;

    public function __construct() {
        $this->db = new Database();
        $this->connection = $this->db->getConnection();
    }

    /**
     * Authenticate API request using API key - Auto-register if needed
     */
    private function authenticateRequest($autoRegister = false) {
        $apiKey = null;
        
        // Check for API key in headers
        if (isset($_SERVER['HTTP_X_API_KEY'])) {
            $apiKey = $_SERVER['HTTP_X_API_KEY'];
        } elseif (isset($_GET['api_key'])) {
            $apiKey = $_GET['api_key'];
        } elseif (isset($_POST['api_key'])) {
            $apiKey = $_POST['api_key'];
        }

        if (!$apiKey) {
            return false;
        }

        // Verify API key exists in computers table
        try {
            $stmt = $this->connection->prepare("SELECT id, computer_name FROM computers WHERE api_key = ? AND status != 'maintenance'");
            $stmt->execute([$apiKey]);
            $computer = $stmt->fetch();
            
            // If computer not found and auto-register is enabled, create it
            if (!$computer && $autoRegister) {
                $computerName = $_SERVER['HTTP_X_COMPUTER_NAME'] ?? gethostname();
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                
                // Auto-register the computer
                $stmt = $this->connection->prepare("
                    INSERT INTO computers (computer_name, ip_address, location, lab_room, operating_system, status, api_key, created_at)
                    VALUES (?, ?, 'Auto Lab', 'AUTO-ASSIGNED', 'Windows', 'online', ?, NOW())
                    ON DUPLICATE KEY UPDATE 
                    ip_address = VALUES(ip_address), 
                    status = 'online', 
                    last_seen = NOW()
                ");
                $stmt->execute([$computerName, $ipAddress, $apiKey]);
                
                // Get the newly created/updated computer
                $stmt = $this->connection->prepare("SELECT id, computer_name FROM computers WHERE api_key = ?");
                $stmt->execute([$apiKey]);
                $computer = $stmt->fetch();
                
                error_log("Auto-registered computer: " . $computerName . " with API key: " . $apiKey);
            }
            
            return $computer;
        } catch (Exception $e) {
            error_log("API Authentication error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Register a new computer or update existing one
     */
    public function registerComputer() {
        $computer = $this->authenticateRequest();
        if (!$computer) {
            return $this->jsonError('Invalid API key', 401);
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $computerName = $input['computer_name'] ?? $computer['computer_name'];
            $ipAddress = $input['ip_address'] ?? $_SERVER['REMOTE_ADDR'];
            $macAddress = $input['mac_address'] ?? null;
            $location = $input['location'] ?? null;
            $labRoom = $input['lab_room'] ?? null;
            $operatingSystem = $input['operating_system'] ?? null;

            // Update computer info
            $stmt = $this->connection->prepare("
                UPDATE computers 
                SET ip_address = ?, mac_address = ?, location = ?, lab_room = ?, 
                    operating_system = ?, status = 'online', last_seen = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$ipAddress, $macAddress, $location, $labRoom, $operatingSystem, $computer['id']]);

            return $this->jsonSuccess([
                'message' => 'Computer registered successfully',
                'computer_id' => $computer['id']
            ]);

        } catch (Exception $e) {
            return $this->jsonError('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Submit health data from monitoring agent
     */
    public function submitHealthData() {
        $computer = $this->authenticateRequest(true); // Enable auto-registration
        if (!$computer) {
            return $this->jsonError('Invalid API key or registration failed', 401);
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Extract health data
            $cpuUsage = $input['cpu']['usage'] ?? null;
            $cpuTemperature = $input['cpu']['temperature'] ?? null;
            $memoryTotal = $input['memory']['total'] ?? null;
            $memoryUsed = $input['memory']['used'] ?? null;
            $memoryUsagePercent = $input['memory']['usedPercent'] ?? null;
            $diskTotal = $input['disk']['total'] ?? null;
            $diskUsed = $input['disk']['used'] ?? null;
            $diskUsagePercent = $input['disk']['usedPercent'] ?? null;
            $networkReceived = $input['network']['received'] ?? 0;
            $networkSent = $input['network']['sent'] ?? 0;
            $uptime = $input['system']['uptime'] ?? null;
            $loadAverage = $input['system']['loadAverage'] ?? null;
            $processesCount = $input['system']['processes'] ?? null;

            // Insert health data
            $stmt = $this->connection->prepare("
                INSERT INTO health_data 
                (computer_id, cpu_usage, cpu_temperature, memory_total, memory_used, memory_usage_percent,
                 disk_total, disk_used, disk_usage_percent, network_received, network_sent,
                 uptime, load_average, processes_count)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $computer['id'], $cpuUsage, $cpuTemperature, $memoryTotal, $memoryUsed, $memoryUsagePercent,
                $diskTotal, $diskUsed, $diskUsagePercent, $networkReceived, $networkSent,
                $uptime, $loadAverage, $processesCount
            ]);

            // Update computer last_seen and status
            $stmt = $this->connection->prepare("UPDATE computers SET last_seen = NOW(), status = 'online' WHERE id = ?");
            $stmt->execute([$computer['id']]);

            // Check for alerts
            $this->checkHealthAlerts($computer['id'], [
                'cpu_usage' => $cpuUsage,
                'memory_usage' => $memoryUsagePercent,
                'disk_usage' => $diskUsagePercent,
                'cpu_temperature' => $cpuTemperature
            ]);

            return $this->jsonSuccess([
                'message' => 'Health data submitted successfully',
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            return $this->jsonError('Failed to submit health data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get current health status for all computers
     */
    public function getHealthStatus() {
        try {
            // Get all computers with their latest health data
            $stmt = $this->connection->prepare("
                SELECT 
                    c.id, c.computer_name, c.ip_address, c.location, c.lab_room, 
                    c.status, c.last_seen, c.operating_system,
                    h.cpu_usage, h.memory_usage_percent, h.disk_usage_percent, 
                    h.cpu_temperature, h.timestamp as last_health_update,
                    (SELECT COUNT(*) FROM health_alerts WHERE computer_name = c.computer_name AND resolved = 0) as active_alerts
                FROM computers c
                LEFT JOIN (
                    SELECT h1.computer_id, h1.cpu_usage, h1.memory_usage_percent, 
                           h1.disk_usage_percent, h1.cpu_temperature, h1.timestamp
                    FROM health_data h1
                    INNER JOIN (
                        SELECT computer_id, MAX(timestamp) as max_timestamp
                        FROM health_data 
                        WHERE timestamp > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                        GROUP BY computer_id
                    ) h2 ON h1.computer_id = h2.computer_id AND h1.timestamp = h2.max_timestamp
                ) h ON c.id = h.computer_id
                ORDER BY c.computer_name
            ");
            $stmt->execute();
            $computers = $stmt->fetchAll();

            // Get recent alerts
            $stmt = $this->connection->prepare("
                SELECT * FROM health_alerts
                WHERE timestamp > DATE_SUB(NOW(), INTERVAL 24 HOUR) 
                ORDER BY timestamp DESC 
                LIMIT 50
            ");
            $stmt->execute();
            $alerts = $stmt->fetchAll();

            return $this->jsonSuccess([
                'computers' => $computers,
                'alerts' => $alerts,
                'summary' => [
                    'total_computers' => count($computers),
                    'online_computers' => count(array_filter($computers, fn($c) => $c['status'] === 'online')),
                    'active_alerts' => array_sum(array_column($computers, 'active_alerts'))
                ]
            ]);

        } catch (Exception $e) {
            return $this->jsonError('Failed to get health status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Check for health alerts based on thresholds
     */
    private function checkHealthAlerts($computerId, $metrics) {
        try {
            // Get thresholds
            $stmt = $this->connection->prepare("SELECT * FROM health_thresholds WHERE is_enabled = 1");
            $stmt->execute();
            $thresholds = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($thresholds as $threshold) {
                $metricName = $threshold['metric_name'];
                $currentValue = $metrics[$metricName] ?? null;

                if ($currentValue === null) continue;

                $severity = null;
                if ($currentValue >= $threshold['critical_threshold']) {
                    $severity = 'critical';
                } elseif ($currentValue >= $threshold['warning_threshold']) {
                    $severity = 'warning';
                }

                if ($severity) {
                    // Check if similar alert already exists and is not resolved
                    $stmt = $this->connection->prepare("
                        SELECT id FROM health_alerts 
                        WHERE computer_id = ? AND alert_type = ? AND is_resolved = 0 
                        AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                    ");
                    $stmt->execute([$computerId, $metricName]);
                    
                    if (!$stmt->fetch()) {
                        // Create new alert
                        $message = sprintf(
                            "%s is at %s%% (threshold: %s%%)", 
                            ucfirst(str_replace('_', ' ', $metricName)), 
                            $currentValue, 
                            $threshold[$severity . '_threshold']
                        );

                        $stmt = $this->connection->prepare("
                            INSERT INTO health_alerts 
                            (computer_id, alert_type, severity, message, threshold_value, current_value)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $computerId, $metricName, $severity, $message, 
                            $threshold[$severity . '_threshold'], $currentValue
                        ]);
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Alert checking error: " . $e->getMessage());
        }
    }

    /**
     * Return JSON success response
     */
    private function jsonSuccess($data) {
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $data]);
        exit();
    }

    /**
     * Return JSON error response
     */
    private function jsonError($message, $code = 400) {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $message]);
        exit();
    }
}

// Route the request
$api = new HealthMonitoringAPI();
$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['action'] ?? '';

switch ($method . ':' . $path) {
    case 'POST:register':
        $api->registerComputer();
        break;
    case 'POST:health':
        $api->submitHealthData();
        break;
    case 'GET:status':
        $api->getHealthStatus();
        break;
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Endpoint not found']);
        break;
}
?>