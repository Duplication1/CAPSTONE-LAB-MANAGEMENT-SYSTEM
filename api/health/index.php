<?php
/**
 * Health Monitoring API Router
 * Routes health monitoring requests to appropriate handlers
 */

// Enable CORS for cross-origin requests
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key, X-Computer-Name');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load dependencies
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/env_loader.php';
require_once __DIR__ . '/../../model/database.php';
require_once __DIR__ . '/../middleware/auth_middleware.php';
require_once __DIR__ . '/health_controller.php';

try {
    // Load environment variables
    EnvLoader::load();
    
    // Initialize database connection
    $database = new Database();
    
    // Initialize auth middleware
    $authMiddleware = new HealthApiAuthMiddleware($database);
    
    // Initialize health controller
    $healthController = new HealthController($database);
    
    // Get request method and path
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Remove base path (adjust based on your setup)
    $basePath = '/CAPSTONE-LAB-MANAGEMENT-SYSTEM/api/health';
    if (strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }
    
    // Remove leading slash
    $path = ltrim($path, '/');
    
    // Split path into segments
    $segments = explode('/', $path);
    $endpoint = $segments[0] ?? '';
    
    // Route requests
    switch ($endpoint) {
        case '':
        case 'test':
            if ($method === 'GET') {
                $healthController->testConnection();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'data':
            if ($method === 'POST') {
                // Authenticate request
                if (!$authMiddleware->authenticate()) {
                    exit();
                }
                $healthController->receiveHealthData();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'alert':
            if ($method === 'POST') {
                // Authenticate request
                if (!$authMiddleware->authenticate()) {
                    exit();
                }
                $healthController->receiveAlert();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'system-info':
            if ($method === 'POST') {
                // Authenticate request
                if (!$authMiddleware->authenticate()) {
                    exit();
                }
                $healthController->receiveSystemInfo();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'register':
            if ($method === 'POST') {
                // Authenticate request
                if (!$authMiddleware->authenticate()) {
                    exit();
                }
                $healthController->registerComputer();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'heartbeat':
            if ($method === 'POST') {
                // Authenticate request
                if (!$authMiddleware->authenticate()) {
                    exit();
                }
                $healthController->receiveHeartbeat();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'settings':
            if ($method === 'GET' && isset($segments[1])) {
                // Authenticate request
                if (!$authMiddleware->authenticate()) {
                    exit();
                }
                $computerName = $segments[1];
                $healthController->getSettings($computerName);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'computers':
            if ($method === 'GET') {
                // Authenticate admin session
                session_start();
                if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'admin') {
                    http_response_code(401);
                    echo json_encode(['error' => 'Unauthorized']);
                    exit();
                }
                $healthController->getComputers();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'computer':
            if ($method === 'GET' && isset($segments[1])) {
                // Authenticate admin session
                session_start();
                if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'admin') {
                    http_response_code(401);
                    echo json_encode(['error' => 'Unauthorized']);
                    exit();
                }
                $computerName = $segments[1];
                $healthController->getComputerDetails($computerName);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        case 'alerts':
            if ($method === 'GET') {
                // Authenticate admin session
                session_start();
                if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'admin') {
                    http_response_code(401);
                    echo json_encode(['error' => 'Unauthorized']);
                    exit();
                }
                $healthController->getAlerts();
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
            break;
    }
    
} catch (Exception $e) {
    error_log('Health API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}