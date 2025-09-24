<?php
/**
 * Health Monitoring API - Alerts Endpoint
 * Returns list of health alerts
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
    
    // Get limit from query parameter
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
    $limit = min($limit, 100); // Max 100 alerts
    
    // Get recent alerts
    $stmt = $connection->prepare("
        SELECT * FROM health_alerts
        WHERE timestamp > DATE_SUB(NOW(), INTERVAL 7 DAY) 
        ORDER BY timestamp DESC 
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    $alerts = $stmt->fetchAll();

    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'alerts' => $alerts,
        'count' => count($alerts)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to get alerts: ' . $e->getMessage()
    ]);
}
?>