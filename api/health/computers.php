<?php
/**
 * Health Monitoring API - Computers Endpoint
 * Returns list of all computers and their health status
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
    $db = new Database();
    $connection = $db->getConnection();
    
    // Get all computers with their latest health data
    $stmt = $connection->prepare("
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

    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'computers' => $computers,
        'summary' => [
            'total_computers' => count($computers),
            'online_computers' => count(array_filter($computers, fn($c) => $c['status'] === 'online')),
            'active_alerts' => array_sum(array_column($computers, 'active_alerts'))
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to get computers: ' . $e->getMessage()
    ]);
}
?>