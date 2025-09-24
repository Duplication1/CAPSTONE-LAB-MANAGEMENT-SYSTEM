<?php
/**
 * Health Alert Scheduler
 * Manages scheduled notifications and digest emails
 */

require_once __DIR__ . '/../model/database.php';
require_once __DIR__ . '/../model/health_notification_service.php';

class HealthAlertScheduler {
    private $database;
    private $notificationService;
    
    public function __construct() {
        $this->database = new Database();
        $this->notificationService = new HealthNotificationService($this->database);
    }
    
    /**
     * Send daily health digest
     */
    public function sendDailyDigest() {
        echo "Sending daily health digest...\n";
        
        try {
            $result = $this->notificationService->sendAlertDigest('daily');
            
            if ($result) {
                echo "Daily digest sent successfully.\n";
                $this->logScheduledTask('daily_digest', 'success');
            } else {
                echo "No alerts to report or no admin emails configured.\n";
                $this->logScheduledTask('daily_digest', 'no_alerts');
            }
        } catch (Exception $e) {
            echo "Error sending daily digest: " . $e->getMessage() . "\n";
            $this->logScheduledTask('daily_digest', 'error', $e->getMessage());
        }
    }
    
    /**
     * Send weekly health summary
     */
    public function sendWeeklyDigest() {
        echo "Sending weekly health digest...\n";
        
        try {
            $result = $this->notificationService->sendAlertDigest('weekly');
            
            if ($result) {
                echo "Weekly digest sent successfully.\n";
                $this->logScheduledTask('weekly_digest', 'success');
            } else {
                echo "No alerts to report or no admin emails configured.\n";
                $this->logScheduledTask('weekly_digest', 'no_alerts');
            }
        } catch (Exception $e) {
            echo "Error sending weekly digest: " . $e->getMessage() . "\n";
            $this->logScheduledTask('weekly_digest', 'error', $e->getMessage());
        }
    }
    
    /**
     * Send system status summary
     */
    public function sendStatusSummary() {
        echo "Sending system status summary...\n";
        
        try {
            $result = $this->notificationService->sendSystemStatusSummary();
            
            if ($result) {
                echo "Status summary sent successfully.\n";
                $this->logScheduledTask('status_summary', 'success');
            } else {
                echo "No admin emails configured for status summary.\n";
                $this->logScheduledTask('status_summary', 'no_admins');
            }
        } catch (Exception $e) {
            echo "Error sending status summary: " . $e->getMessage() . "\n";
            $this->logScheduledTask('status_summary', 'error', $e->getMessage());
        }
    }
    
    /**
     * Check for stale computers and send alerts
     */
    public function checkStaleComputers() {
        echo "Checking for stale/offline computers...\n";
        
        try {
            $conn = $this->database->getConnection();
            
            // Find computers that haven't sent data in over 10 minutes
            $stmt = $conn->prepare("
                SELECT computer_name, hostname, last_seen 
                FROM health_computers 
                WHERE last_seen < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                AND status != 'offline'
            ");
            $stmt->execute();
            
            $staleComputers = $stmt->fetchAll();
            
            foreach ($staleComputers as $computer) {
                // Mark as offline
                $updateStmt = $conn->prepare("
                    UPDATE health_computers 
                    SET status = 'offline', last_seen = NOW()
                    WHERE computer_name = ?
                ");
                $updateStmt->execute([$computer['computer_name']]);
                
                // Create offline alert
                $alertStmt = $conn->prepare("
                    INSERT INTO health_alerts (
                        computer_name, alert_type, severity, message, timestamp
                    ) VALUES (?, 'connection', 'warning', ?, NOW())
                ");
                $message = "Computer went offline. Last seen: " . $computer['last_seen'];
                $alertStmt->execute([$computer['computer_name'], $message]);
                
                echo "Marked {$computer['computer_name']} as offline.\n";
            }
            
            if (!empty($staleComputers)) {
                $this->logScheduledTask('stale_check', 'found_stale', count($staleComputers) . ' computers marked offline');
            } else {
                $this->logScheduledTask('stale_check', 'all_online');
            }
            
        } catch (Exception $e) {
            echo "Error checking stale computers: " . $e->getMessage() . "\n";
            $this->logScheduledTask('stale_check', 'error', $e->getMessage());
        }
    }
    
    /**
     * Clean up old data
     */
    public function cleanupOldData() {
        echo "Cleaning up old health data...\n";
        
        try {
            $conn = $this->database->getConnection();
            
            // Delete health data older than 30 days
            $stmt = $conn->prepare("DELETE FROM health_data WHERE timestamp < DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $deletedData = $stmt->execute();
            $dataCount = $stmt->rowCount();
            
            // Delete acknowledged alerts older than 7 days
            $stmt = $conn->prepare("DELETE FROM health_alerts WHERE acknowledged = TRUE AND timestamp < DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $deletedAlerts = $stmt->execute();
            $alertCount = $stmt->rowCount();
            
            echo "Deleted $dataCount old health records and $alertCount old alerts.\n";
            $this->logScheduledTask('cleanup', 'success', "Deleted $dataCount data records, $alertCount alerts");
            
        } catch (Exception $e) {
            echo "Error during cleanup: " . $e->getMessage() . "\n";
            $this->logScheduledTask('cleanup', 'error', $e->getMessage());
        }
    }
    
    /**
     * Run maintenance tasks
     */
    public function runMaintenance() {
        echo "Running health monitoring maintenance tasks...\n";
        
        $this->checkStaleComputers();
        $this->cleanupOldData();
        
        echo "Maintenance tasks completed.\n";
    }
    
    private function logScheduledTask($taskType, $status, $details = null) {
        try {
            $conn = $this->database->getConnection();
            
            // Create scheduler log table if it doesn't exist
            $conn->exec("
                CREATE TABLE IF NOT EXISTS health_scheduler_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    task_type VARCHAR(50) NOT NULL,
                    status VARCHAR(20) NOT NULL,
                    details TEXT,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_task_type (task_type),
                    INDEX idx_executed_at (executed_at)
                )
            ");
            
            $stmt = $conn->prepare("
                INSERT INTO health_scheduler_log (task_type, status, details) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$taskType, $status, $details]);
            
        } catch (Exception $e) {
            error_log("Error logging scheduled task: " . $e->getMessage());
        }
    }
}

// CLI interface for running scheduled tasks
if (php_sapi_name() === 'cli') {
    $scheduler = new HealthAlertScheduler();
    
    $task = $argv[1] ?? 'help';
    
    switch ($task) {
        case 'daily':
            $scheduler->sendDailyDigest();
            break;
            
        case 'weekly':
            $scheduler->sendWeeklyDigest();
            break;
            
        case 'status':
            $scheduler->sendStatusSummary();
            break;
            
        case 'maintenance':
            $scheduler->runMaintenance();
            break;
            
        case 'stale':
            $scheduler->checkStaleComputers();
            break;
            
        case 'cleanup':
            $scheduler->cleanupOldData();
            break;
            
        case 'help':
        default:
            echo "Health Alert Scheduler\n";
            echo "Usage: php health_scheduler.php [task]\n\n";
            echo "Available tasks:\n";
            echo "  daily      - Send daily alert digest\n";
            echo "  weekly     - Send weekly alert digest\n";
            echo "  status     - Send system status summary\n";
            echo "  maintenance - Run maintenance tasks (stale check + cleanup)\n";
            echo "  stale      - Check for stale/offline computers\n";
            echo "  cleanup    - Clean up old data\n";
            echo "  help       - Show this help message\n";
            break;
    }
}