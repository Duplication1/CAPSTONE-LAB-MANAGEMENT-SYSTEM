<?php
/**
 * Real-time notifications endpoint for admin dashboard
 */

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../model/database.php';

// Keep connection alive
ignore_user_abort(true);
set_time_limit(0);

$database = new Database();
$conn = $database->getConnection();

// Track last alert ID to only send new alerts
$lastAlertId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

// Function to send SSE data
function sendSSE($event, $data) {
    echo "event: $event\n";
    echo "data: " . json_encode($data) . "\n\n";
    ob_flush();
    flush();
}

try {
    while (true) {
        // Check for new alerts
        $stmt = $conn->prepare("
            SELECT 
                ha.*,
                hc.hostname,
                hc.status as computer_status
            FROM health_alerts ha
            LEFT JOIN health_computers hc ON ha.computer_name = hc.computer_name
            WHERE ha.id > ? 
            AND ha.acknowledged = FALSE
            ORDER BY ha.id ASC
            LIMIT 10
        ");
        $stmt->execute([$lastAlertId]);
        $newAlerts = $stmt->fetchAll();
        
        foreach ($newAlerts as $alert) {
            sendSSE('alert', [
                'id' => $alert['id'],
                'computer_name' => $alert['computer_name'],
                'hostname' => $alert['hostname'],
                'alert_type' => $alert['alert_type'],
                'severity' => $alert['severity'],
                'message' => $alert['message'],
                'value' => $alert['value'],
                'threshold_value' => $alert['threshold_value'],
                'timestamp' => $alert['timestamp'],
                'computer_status' => $alert['computer_status']
            ]);
            
            $lastAlertId = max($lastAlertId, $alert['id']);
        }
        
        // Check for computer status changes
        $stmt = $conn->prepare("
            SELECT 
                computer_name,
                hostname,
                status,
                last_seen,
                TIMESTAMPDIFF(MINUTE, last_seen, NOW()) as minutes_offline
            FROM health_computers
            WHERE last_seen > DATE_SUB(NOW(), INTERVAL 2 MINUTE)
            ORDER BY last_seen DESC
        ");
        $stmt->execute();
        $recentActivity = $stmt->fetchAll();
        
        if (!empty($recentActivity)) {
            sendSSE('activity', $recentActivity);
        }
        
        // Send heartbeat every 30 seconds
        sendSSE('heartbeat', ['timestamp' => time()]);
        
        // Sleep for 5 seconds before checking again
        sleep(5);
        
        // Check if client disconnected
        if (connection_aborted()) {
            break;
        }
    }
    
} catch (Exception $e) {
    sendSSE('error', ['message' => $e->getMessage()]);
    error_log("SSE Error: " . $e->getMessage());
}