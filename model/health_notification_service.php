<?php
/**
 * Health Alert Notification Service
 * Handles notifications for system health alerts
 */

require_once __DIR__ . '/../model/email_service.php';
require_once __DIR__ . '/../model/database.php';

class HealthNotificationService {
    private $database;
    private $emailService;
    
    public function __construct($database = null) {
        $this->database = $database ?? new Database();
        $this->emailService = new EmailService($this->database);
    }
    
    /**
     * Send alert notification to administrators
     */
    public function sendAlertNotification($alert) {
        try {
            // Get admin emails
            $adminEmails = $this->getAdminEmails();
            
            if (empty($adminEmails)) {
                error_log("No admin emails found for alert notifications");
                return false;
            }
            
            // Prepare email content
            $subject = $this->getAlertSubject($alert);
            $message = $this->getAlertMessage($alert);
            
            // Send to each admin
            $successCount = 0;
            foreach ($adminEmails as $email) {
                if ($this->sendEmail($email, $subject, $message)) {
                    $successCount++;
                }
            }
            
            // Log notification
            $this->logNotification($alert, $successCount, count($adminEmails));
            
            return $successCount > 0;
            
        } catch (Exception $e) {
            error_log("Error sending alert notification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send digest of recent alerts
     */
    public function sendAlertDigest($frequency = 'daily') {
        try {
            $adminEmails = $this->getAdminEmails();
            
            if (empty($adminEmails)) {
                return false;
            }
            
            // Get alerts based on frequency
            $alerts = $this->getAlertsForDigest($frequency);
            
            if (empty($alerts)) {
                // Send "all clear" message for daily digest
                if ($frequency === 'daily') {
                    $this->sendAllClearDigest($adminEmails);
                }
                return true;
            }
            
            $subject = $this->getDigestSubject($frequency, count($alerts));
            $message = $this->getDigestMessage($alerts, $frequency);
            
            $successCount = 0;
            foreach ($adminEmails as $email) {
                if ($this->sendEmail($email, $subject, $message)) {
                    $successCount++;
                }
            }
            
            return $successCount > 0;
            
        } catch (Exception $e) {
            error_log("Error sending alert digest: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send system status summary
     */
    public function sendSystemStatusSummary() {
        try {
            $adminEmails = $this->getAdminEmails();
            
            if (empty($adminEmails)) {
                return false;
            }
            
            $summary = $this->getSystemStatusSummary();
            $subject = "Lab System Status Summary - " . date('Y-m-d H:i');
            $message = $this->getStatusSummaryMessage($summary);
            
            $successCount = 0;
            foreach ($adminEmails as $email) {
                if ($this->sendEmail($email, $subject, $message)) {
                    $successCount++;
                }
            }
            
            return $successCount > 0;
            
        } catch (Exception $e) {
            error_log("Error sending system status summary: " . $e->getMessage());
            return false;
        }
    }
    
    private function getAdminEmails() {
        try {
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare("
                SELECT DISTINCT email 
                FROM users 
                WHERE user_type = 'admin' 
                AND email IS NOT NULL 
                AND email != ''
                AND is_active = 1
            ");
            $stmt->execute();
            
            $emails = [];
            while ($row = $stmt->fetch()) {
                $emails[] = $row['email'];
            }
            
            return $emails;
        } catch (Exception $e) {
            error_log("Error getting admin emails: " . $e->getMessage());
            return [];
        }
    }
    
    private function getAlertSubject($alert) {
        $severity = strtoupper($alert['severity']);
        $computerName = $alert['computer_name'];
        $alertType = $alert['alert_type'];
        
        return "[$severity ALERT] $computerName - $alertType";
    }
    
    private function getAlertMessage($alert) {
        $computerName = htmlspecialchars($alert['computer_name']);
        $severity = strtoupper($alert['severity']);
        $alertType = htmlspecialchars($alert['alert_type']);
        $message = htmlspecialchars($alert['message']);
        $timestamp = date('Y-m-d H:i:s', strtotime($alert['timestamp']));
        $value = $alert['value'] ?? 'N/A';
        $threshold = $alert['threshold_value'] ?? 'N/A';
        
        // Get severity color
        $severityColor = $severity === 'CRITICAL' ? '#dc2626' : '#f59e0b';
        $severityBg = $severity === 'CRITICAL' ? '#fef2f2' : '#fffbeb';
        
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>System Alert Notification</title>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f9fafb;'>
            <div style='max-width: 600px; margin: 20px auto; background-color: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden;'>
                <!-- Header -->
                <div style='background-color: $severityColor; color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px; font-weight: bold;'>🚨 System Alert</h1>
                    <p style='margin: 5px 0 0 0; font-size: 16px; opacity: 0.9;'>$severity Alert Detected</p>
                </div>
                
                <!-- Alert Details -->
                <div style='padding: 30px 20px;'>
                    <div style='background-color: $severityBg; border-left: 4px solid $severityColor; padding: 16px; margin-bottom: 20px; border-radius: 4px;'>
                        <h2 style='margin: 0 0 10px 0; color: $severityColor; font-size: 18px;'>$alertType</h2>
                        <p style='margin: 0; color: #374151; font-size: 16px; line-height: 1.5;'>$message</p>
                    </div>
                    
                    <!-- Details Table -->
                    <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                        <tr>
                            <td style='padding: 12px 0; border-bottom: 1px solid #e5e7eb; font-weight: bold; color: #374151; width: 30%;'>Computer:</td>
                            <td style='padding: 12px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280;'>$computerName</td>
                        </tr>
                        <tr>
                            <td style='padding: 12px 0; border-bottom: 1px solid #e5e7eb; font-weight: bold; color: #374151;'>Severity:</td>
                            <td style='padding: 12px 0; border-bottom: 1px solid #e5e7eb; color: $severityColor; font-weight: bold;'>$severity</td>
                        </tr>
                        <tr>
                            <td style='padding: 12px 0; border-bottom: 1px solid #e5e7eb; font-weight: bold; color: #374151;'>Timestamp:</td>
                            <td style='padding: 12px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280;'>$timestamp</td>
                        </tr>
                        <tr>
                            <td style='padding: 12px 0; border-bottom: 1px solid #e5e7eb; font-weight: bold; color: #374151;'>Current Value:</td>
                            <td style='padding: 12px 0; border-bottom: 1px solid #e5e7eb; color: #6b7280;'>$value</td>
                        </tr>
                        <tr>
                            <td style='padding: 12px 0; font-weight: bold; color: #374151;'>Threshold:</td>
                            <td style='padding: 12px 0; color: #6b7280;'>$threshold</td>
                        </tr>
                    </table>
                    
                    <!-- Action Button -->
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='http://localhost/CAPSTONE-LAB-MANAGEMENT-SYSTEM/view/admin/health.php' 
                           style='background-color: #2563eb; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;'>
                            View Health Dashboard
                        </a>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;'>
                    <p style='margin: 0; color: #6b7280; font-size: 14px;'>
                        Lab Management System Health Monitor<br>
                        Generated on " . date('Y-m-d H:i:s') . "
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function getDigestSubject($frequency, $alertCount) {
        $period = ucfirst($frequency);
        return "[$period Alert Digest] $alertCount alerts in your lab systems";
    }
    
    private function getDigestMessage($alerts, $frequency) {
        $alertCount = count($alerts);
        $period = ucfirst($frequency);
        
        // Group alerts by computer and severity
        $groupedAlerts = $this->groupAlertsByComputer($alerts);
        $criticalCount = count(array_filter($alerts, fn($a) => $a['severity'] === 'critical'));
        $warningCount = count(array_filter($alerts, fn($a) => $a['severity'] === 'warning'));
        
        $alertsHtml = '';
        foreach ($groupedAlerts as $computerName => $computerAlerts) {
            $alertsHtml .= "<tr><td colspan='3' style='padding: 12px 8px; background-color: #f3f4f6; font-weight: bold; color: #374151;'>" . htmlspecialchars($computerName) . "</td></tr>";
            
            foreach ($computerAlerts as $alert) {
                $severityColor = $alert['severity'] === 'critical' ? '#dc2626' : '#f59e0b';
                $timestamp = date('M j, H:i', strtotime($alert['timestamp']));
                
                $alertsHtml .= "
                <tr>
                    <td style='padding: 8px; border-bottom: 1px solid #e5e7eb; color: $severityColor; font-weight: bold;'>" . strtoupper($alert['severity']) . "</td>
                    <td style='padding: 8px; border-bottom: 1px solid #e5e7eb; color: #374151;'>" . htmlspecialchars($alert['alert_type']) . "</td>
                    <td style='padding: 8px; border-bottom: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;'>$timestamp</td>
                </tr>";
            }
        }
        
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Alert Digest</title>
        </head>
        <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f9fafb;'>
            <div style='max-width: 700px; margin: 20px auto; background-color: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden;'>
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 28px; font-weight: bold;'>📊 $period Alert Digest</h1>
                    <p style='margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;'>System health summary for your lab computers</p>
                </div>
                
                <!-- Summary Stats -->
                <div style='padding: 20px; background-color: #f8fafc; border-bottom: 1px solid #e5e7eb;'>
                    <div style='display: flex; justify-content: space-around; text-align: center;'>
                        <div style='flex: 1; padding: 0 10px;'>
                            <div style='font-size: 32px; font-weight: bold; color: #374151;'>$alertCount</div>
                            <div style='font-size: 14px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;'>Total Alerts</div>
                        </div>
                        <div style='flex: 1; padding: 0 10px; border-left: 1px solid #d1d5db;'>
                            <div style='font-size: 32px; font-weight: bold; color: #dc2626;'>$criticalCount</div>
                            <div style='font-size: 14px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;'>Critical</div>
                        </div>
                        <div style='flex: 1; padding: 0 10px; border-left: 1px solid #d1d5db;'>
                            <div style='font-size: 32px; font-weight: bold; color: #f59e0b;'>$warningCount</div>
                            <div style='font-size: 14px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;'>Warnings</div>
                        </div>
                    </div>
                </div>
                
                <!-- Alerts List -->
                <div style='padding: 20px;'>
                    <h2 style='margin: 0 0 20px 0; color: #374151; font-size: 20px;'>Alert Details</h2>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <thead>
                            <tr style='background-color: #f9fafb;'>
                                <th style='padding: 12px 8px; text-align: left; font-weight: bold; color: #374151; border-bottom: 2px solid #e5e7eb;'>Severity</th>
                                <th style='padding: 12px 8px; text-align: left; font-weight: bold; color: #374151; border-bottom: 2px solid #e5e7eb;'>Alert Type</th>
                                <th style='padding: 12px 8px; text-align: left; font-weight: bold; color: #374151; border-bottom: 2px solid #e5e7eb;'>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            $alertsHtml
                        </tbody>
                    </table>
                    
                    <!-- Action Button -->
                    <div style='text-align: center; margin: 30px 0 0 0;'>
                        <a href='http://localhost/CAPSTONE-LAB-MANAGEMENT-SYSTEM/view/admin/health.php' 
                           style='background-color: #2563eb; color: white; text-decoration: none; padding: 14px 28px; border-radius: 6px; font-weight: bold; display: inline-block;'>
                            View Full Health Dashboard
                        </a>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;'>
                    <p style='margin: 0; color: #6b7280; font-size: 14px;'>
                        Lab Management System Health Monitor<br>
                        Generated on " . date('Y-m-d H:i:s') . "
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function sendAllClearDigest($adminEmails) {
        $subject = "[Daily Digest] All systems operating normally";
        $message = "
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                <div style='background-color: #10b981; color: white; padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px;'>✅ All Clear!</h1>
                    <p style='margin: 10px 0 0 0; opacity: 0.9;'>Your lab systems are running smoothly</p>
                </div>
                <div style='padding: 30px; text-align: center;'>
                    <p style='color: #374151; font-size: 16px; margin: 0 0 20px 0;'>
                        Great news! No alerts were generated in the past 24 hours. 
                        All your lab computers are operating within normal parameters.
                    </p>
                    <a href='http://localhost/CAPSTONE-LAB-MANAGEMENT-SYSTEM/view/admin/health.php' 
                       style='background-color: #2563eb; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;'>
                        View Health Dashboard
                    </a>
                </div>
                <div style='background-color: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 14px;'>
                    Lab Management System - " . date('Y-m-d H:i:s') . "
                </div>
            </div>
        </body>
        </html>
        ";
        
        foreach ($adminEmails as $email) {
            $this->sendEmail($email, $subject, $message);
        }
    }
    
    private function getAlertsForDigest($frequency) {
        try {
            $conn = $this->database->getConnection();
            
            $interval = match($frequency) {
                'hourly' => '1 HOUR',
                'daily' => '1 DAY',
                'weekly' => '7 DAY',
                default => '1 DAY'
            };
            
            $stmt = $conn->prepare("
                SELECT 
                    ha.*, 
                    hc.hostname 
                FROM health_alerts ha
                LEFT JOIN health_computers hc ON ha.computer_name = hc.computer_name
                WHERE ha.timestamp >= DATE_SUB(NOW(), INTERVAL $interval)
                AND ha.acknowledged = FALSE
                ORDER BY ha.timestamp DESC, ha.severity DESC
                LIMIT 100
            ");
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting alerts for digest: " . $e->getMessage());
            return [];
        }
    }
    
    private function getSystemStatusSummary() {
        try {
            $conn = $this->database->getConnection();
            
            // Get computer counts by status
            $stmt = $conn->prepare("
                SELECT 
                    status,
                    COUNT(*) as count
                FROM health_computers
                GROUP BY status
            ");
            $stmt->execute();
            $statusCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Get recent alert counts
            $stmt = $conn->prepare("
                SELECT 
                    severity,
                    COUNT(*) as count
                FROM health_alerts
                WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY severity
            ");
            $stmt->execute();
            $alertCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            return [
                'computers' => $statusCounts,
                'alerts' => $alertCounts,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            error_log("Error getting system status summary: " . $e->getMessage());
            return [];
        }
    }
    
    private function getStatusSummaryMessage($summary) {
        $computers = $summary['computers'] ?? [];
        $alerts = $summary['alerts'] ?? [];
        
        $totalComputers = array_sum($computers);
        $onlineComputers = $computers['online'] ?? 0;
        $criticalComputers = $computers['critical'] ?? 0;
        $warningComputers = $computers['warning'] ?? 0;
        $offlineComputers = $computers['offline'] ?? 0;
        
        $criticalAlerts = $alerts['critical'] ?? 0;
        $warningAlerts = $alerts['warning'] ?? 0;
        
        $healthPercent = $totalComputers > 0 ? round(($onlineComputers / $totalComputers) * 100) : 0;
        $healthColor = $healthPercent >= 90 ? '#10b981' : ($healthPercent >= 70 ? '#f59e0b' : '#dc2626');
        
        return "
        <html>
        <head><meta charset='UTF-8'></head>
        <body style='font-family: Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 20px;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>
                <!-- Header -->
                <div style='background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color: white; padding: 30px; text-align: center;'>
                    <h1 style='margin: 0; font-size: 24px;'>🖥️ System Status Summary</h1>
                    <p style='margin: 10px 0 0 0; opacity: 0.9;'>Lab infrastructure health report</p>
                </div>
                
                <!-- Overall Health -->
                <div style='padding: 30px 20px; text-align: center; border-bottom: 1px solid #e5e7eb;'>
                    <div style='font-size: 48px; font-weight: bold; color: $healthColor; margin-bottom: 10px;'>$healthPercent%</div>
                    <div style='color: #374151; font-size: 18px; margin-bottom: 20px;'>Overall System Health</div>
                    <div style='background-color: #f3f4f6; border-radius: 10px; height: 10px; overflow: hidden;'>
                        <div style='background-color: $healthColor; height: 100%; width: $healthPercent%; border-radius: 10px;'></div>
                    </div>
                </div>
                
                <!-- Statistics -->
                <div style='padding: 20px;'>
                    <h2 style='color: #374151; margin: 0 0 20px 0; font-size: 18px;'>Computer Status</h2>
                    <div style='display: flex; justify-content: space-between; margin-bottom: 30px;'>
                        <div style='text-align: center; flex: 1;'>
                            <div style='font-size: 24px; font-weight: bold; color: #374151;'>$totalComputers</div>
                            <div style='font-size: 12px; color: #6b7280; text-transform: uppercase;'>Total</div>
                        </div>
                        <div style='text-align: center; flex: 1;'>
                            <div style='font-size: 24px; font-weight: bold; color: #10b981;'>$onlineComputers</div>
                            <div style='font-size: 12px; color: #6b7280; text-transform: uppercase;'>Online</div>
                        </div>
                        <div style='text-align: center; flex: 1;'>
                            <div style='font-size: 24px; font-weight: bold; color: #f59e0b;'>$warningComputers</div>
                            <div style='font-size: 12px; color: #6b7280; text-transform: uppercase;'>Warning</div>
                        </div>
                        <div style='text-align: center; flex: 1;'>
                            <div style='font-size: 24px; font-weight: bold; color: #dc2626;'>$criticalComputers</div>
                            <div style='font-size: 12px; color: #6b7280; text-transform: uppercase;'>Critical</div>
                        </div>
                    </div>
                    
                    <h2 style='color: #374151; margin: 0 0 20px 0; font-size: 18px;'>Recent Alerts (24h)</h2>
                    <div style='display: flex; justify-content: center; gap: 40px; margin-bottom: 30px;'>
                        <div style='text-align: center;'>
                            <div style='font-size: 32px; font-weight: bold; color: #dc2626;'>$criticalAlerts</div>
                            <div style='font-size: 14px; color: #6b7280; text-transform: uppercase;'>Critical Alerts</div>
                        </div>
                        <div style='text-align: center;'>
                            <div style='font-size: 32px; font-weight: bold; color: #f59e0b;'>$warningAlerts</div>
                            <div style='font-size: 14px; color: #6b7280; text-transform: uppercase;'>Warning Alerts</div>
                        </div>
                    </div>
                    
                    <!-- Action Button -->
                    <div style='text-align: center;'>
                        <a href='http://localhost/CAPSTONE-LAB-MANAGEMENT-SYSTEM/view/admin/health.php' 
                           style='background-color: #2563eb; color: white; text-decoration: none; padding: 14px 28px; border-radius: 6px; font-weight: bold; display: inline-block;'>
                            View Detailed Health Dashboard
                        </a>
                    </div>
                </div>
                
                <!-- Footer -->
                <div style='background-color: #f9fafb; padding: 20px; text-align: center; color: #6b7280; font-size: 14px; border-top: 1px solid #e5e7eb;'>
                    Lab Management System Health Monitor<br>
                    Generated on " . date('Y-m-d H:i:s') . "
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    private function groupAlertsByComputer($alerts) {
        $grouped = [];
        foreach ($alerts as $alert) {
            $computerName = $alert['computer_name'];
            if (!isset($grouped[$computerName])) {
                $grouped[$computerName] = [];
            }
            $grouped[$computerName][] = $alert;
        }
        return $grouped;
    }
    
    private function sendEmail($to, $subject, $message) {
        try {
            // Use a simple mail function for now
            // In production, you'd want to use PHPMailer or similar
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: Lab Management System <noreply@labsystem.local>" . "\r\n";
            
            // For development, just log the email
            error_log("EMAIL NOTIFICATION: To: $to, Subject: $subject");
            
            // Uncomment to actually send emails in production
            // return mail($to, $subject, $message, $headers);
            
            return true; // Simulate success for development
        } catch (Exception $e) {
            error_log("Error sending email: " . $e->getMessage());
            return false;
        }
    }
    
    private function logNotification($alert, $successCount, $totalRecipients) {
        try {
            $conn = $this->database->getConnection();
            
            // Create notifications log table if it doesn't exist
            $conn->exec("
                CREATE TABLE IF NOT EXISTS health_notifications_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    alert_id INT,
                    notification_type ENUM('email', 'sms', 'webhook') DEFAULT 'email',
                    recipients_count INT DEFAULT 0,
                    successful_count INT DEFAULT 0,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            $stmt = $conn->prepare("
                INSERT INTO health_notifications_log 
                (notification_type, recipients_count, successful_count) 
                VALUES ('email', ?, ?)
            ");
            $stmt->execute([$totalRecipients, $successCount]);
            
        } catch (Exception $e) {
            error_log("Error logging notification: " . $e->getMessage());
        }
    }
}