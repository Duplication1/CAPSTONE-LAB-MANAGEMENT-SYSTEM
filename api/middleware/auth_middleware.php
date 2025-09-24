<?php
/**
 * Health API Authentication Middleware
 * Handles authentication for health monitoring API endpoints
 */

class HealthApiAuthMiddleware {
    private $database;
    private $validApiKeys;
    
    public function __construct($database) {
        $this->database = $database;
        $this->loadApiKeys();
    }
    
    private function loadApiKeys() {
        // Load API keys from database or configuration
        try {
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare("
                SELECT api_key, computer_name, is_active 
                FROM health_api_keys 
                WHERE is_active = 1
            ");
            $stmt->execute();
            
            $this->validApiKeys = [];
            while ($row = $stmt->fetch()) {
                $this->validApiKeys[$row['api_key']] = $row['computer_name'];
            }
        } catch (PDOException $e) {
            // If table doesn't exist, create it and use default key
            $this->createApiKeysTable();
            $this->createDefaultApiKey();
        }
    }
    
    private function createApiKeysTable() {
        try {
            $conn = $this->database->getConnection();
            $sql = "
                CREATE TABLE IF NOT EXISTS health_api_keys (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    api_key VARCHAR(255) UNIQUE NOT NULL,
                    computer_name VARCHAR(255),
                    description TEXT,
                    is_active BOOLEAN DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_used TIMESTAMP NULL,
                    INDEX idx_api_key (api_key),
                    INDEX idx_active (is_active)
                )
            ";
            $conn->exec($sql);
        } catch (PDOException $e) {
            error_log("Error creating health_api_keys table: " . $e->getMessage());
        }
    }
    
    private function createDefaultApiKey() {
        try {
            $conn = $this->database->getConnection();
            $defaultKey = $this->generateApiKey();
            
            $stmt = $conn->prepare("
                INSERT INTO health_api_keys (api_key, computer_name, description, is_active) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE computer_name = computer_name
            ");
            $stmt->execute([
                $defaultKey,
                'default',
                'Default API key for health monitoring',
                1
            ]);
            
            $this->validApiKeys = [$defaultKey => 'default'];
            
            // Log the default key for initial setup
            error_log("Default Health API Key created: " . $defaultKey);
            
        } catch (PDOException $e) {
            error_log("Error creating default API key: " . $e->getMessage());
        }
    }
    
    public function authenticate() {
        $apiKey = $this->getApiKeyFromRequest();
        $computerName = $this->getComputerNameFromRequest();
        
        if (empty($apiKey)) {
            $this->sendUnauthorizedResponse('Missing API key');
            return false;
        }
        
        if (!isset($this->validApiKeys[$apiKey])) {
            $this->sendUnauthorizedResponse('Invalid API key');
            return false;
        }
        
        // Update last used timestamp
        $this->updateLastUsed($apiKey);
        
        // Store computer name in global for use by controllers
        $_SERVER['HEALTH_API_COMPUTER'] = $computerName ?: $this->validApiKeys[$apiKey];
        $_SERVER['HEALTH_API_KEY'] = $apiKey;
        
        return true;
    }
    
    private function getApiKeyFromRequest() {
        // Check header first
        if (isset($_SERVER['HTTP_X_API_KEY'])) {
            return $_SERVER['HTTP_X_API_KEY'];
        }
        
        // Check Authorization header
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
                return $matches[1];
            }
        }
        
        // Check POST data
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['api_key'])) {
            return $input['api_key'];
        }
        
        // Check GET parameter (less secure, for testing only)
        if (isset($_GET['api_key'])) {
            return $_GET['api_key'];
        }
        
        return null;
    }
    
    private function getComputerNameFromRequest() {
        // Check header
        if (isset($_SERVER['HTTP_X_COMPUTER_NAME'])) {
            return $_SERVER['HTTP_X_COMPUTER_NAME'];
        }
        
        // Check POST data
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['computer_name'])) {
            return $input['computer_name'];
        }
        
        return null;
    }
    
    private function updateLastUsed($apiKey) {
        try {
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare("
                UPDATE health_api_keys 
                SET last_used = CURRENT_TIMESTAMP 
                WHERE api_key = ?
            ");
            $stmt->execute([$apiKey]);
        } catch (PDOException $e) {
            // Ignore errors for now
        }
    }
    
    private function sendUnauthorizedResponse($message) {
        http_response_code(401);
        echo json_encode([
            'error' => 'Unauthorized',
            'message' => $message
        ]);
    }
    
    public function generateApiKey($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    public function createApiKey($computerName, $description = null) {
        $apiKey = $this->generateApiKey();
        
        try {
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare("
                INSERT INTO health_api_keys (api_key, computer_name, description, is_active) 
                VALUES (?, ?, ?, 1)
            ");
            $stmt->execute([$apiKey, $computerName, $description]);
            
            // Reload API keys
            $this->loadApiKeys();
            
            return $apiKey;
        } catch (PDOException $e) {
            throw new Exception("Failed to create API key: " . $e->getMessage());
        }
    }
    
    public function revokeApiKey($apiKey) {
        try {
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare("
                UPDATE health_api_keys 
                SET is_active = 0 
                WHERE api_key = ?
            ");
            $stmt->execute([$apiKey]);
            
            // Reload API keys
            $this->loadApiKeys();
            
            return true;
        } catch (PDOException $e) {
            throw new Exception("Failed to revoke API key: " . $e->getMessage());
        }
    }
    
    public function listApiKeys() {
        try {
            $conn = $this->database->getConnection();
            $stmt = $conn->prepare("
                SELECT api_key, computer_name, description, is_active, created_at, last_used 
                FROM health_api_keys 
                ORDER BY created_at DESC
            ");
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Failed to list API keys: " . $e->getMessage());
        }
    }
}