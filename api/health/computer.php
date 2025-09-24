<?php
/**
 * Health Monitoring API - Computer Details Router
 * Handles /api/health/computer/{name} requests
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

require_once __DIR__ . '/../../model/database.php';

try {
    // Parse the computer name from the URL path
    $requestUri = $_SERVER['REQUEST_URI'];
    $basePath = '/CAPSTONE-LAB-MANAGEMENT-SYSTEM/api/health/computer/';
    
    if (strpos($requestUri, $basePath) === 0) {
        $computerName = urldecode(substr($requestUri, strlen($basePath)));
        
        // Remove query string if present
        if (($pos = strpos($computerName, '?')) !== false) {
            $computerName = substr($computerName, 0, $pos);
        }
        
        $db = new Database();
        $connection = $db->getConnection();
        
        // Get computer details with recent health data
        $stmt = $connection->prepare("
            SELECT 
                c.id, c.computer_name, c.ip_address, c.location, c.lab_room, 
                c.status, c.last_seen, c.operating_system, c.created_at,
                (SELECT COUNT(*) FROM health_alerts WHERE computer_name = c.computer_name AND resolved = 0) as active_alerts
            FROM computers c
            WHERE c.computer_name = ?
        ");
        $stmt->execute([$computerName]);
        $computer = $stmt->fetch();
        
        if (!$computer) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Computer not found'
            ]);
            exit();
        }
        
        // Get recent health data for this computer
        $stmt = $connection->prepare("
            SELECT * FROM health_data 
            WHERE computer_id = ? 
            ORDER BY timestamp DESC 
            LIMIT 100
        ");
        $stmt->execute([$computer['id']]);
        $healthData = $stmt->fetchAll();
        
        // Get recent alerts for this computer
        $stmt = $connection->prepare("
            SELECT * FROM health_alerts 
            WHERE computer_name = ? 
            ORDER BY timestamp DESC 
            LIMIT 20
        ");
        $stmt->execute([$computerName]);
        $alerts = $stmt->fetchAll();
        
        // Return success response
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'computer' => $computer,
            'health_data' => $healthData,
            'alerts' => $alerts
        ]);
        
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid request path'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to get computer details: ' . $e->getMessage()
    ]);
}
?>