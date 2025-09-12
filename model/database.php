<?php
/**
 * Database Connection Configuration
 * Lab Management System
 */

class Database {
    private $host = "localhost";
    private $database = "lab_management_system";
    private $username = "root";
    private $password = "";
    private $connection;

    public function __construct() {
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->database . ";charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function closeConnection() {
        $this->connection = null;
    }
}

/**
 * User Authentication Model
 */
class UserAuth {
    private $db;
    private $connection;

    public function __construct() {
        $this->db = new Database();
        $this->connection = $this->db->getConnection();
    }

    /**
     * Authenticate user login
     * @param string $username
     * @param string $password
     * @param string $userType
     * @return array|false
     */
    public function authenticate($username, $password, $userType) {
        try {
            // Determine table based on user type
            $table = $this->getUserTable($userType);
            
            if (!$table) {
                return false;
            }

            $query = "SELECT * FROM " . $table . " WHERE username = :username AND status = 'active'";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Remove password from returned data
                unset($user['password']);
                $user['user_type'] = $userType;
                return $user;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Authentication error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get table name based on user type
     * @param string $userType
     * @return string|false
     */
    private function getUserTable($userType) {
        $tables = [
            'student' => 'students',
            'professor' => 'professors', 
            'itstaff' => 'it_staff',
            'admin' => 'administrators'
        ];

        return isset($tables[$userType]) ? $tables[$userType] : false;
    }

    /**
     * Create user session
     * @param array $user
     */
    public function createSession($user) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['full_name'] = $user['full_name'] ?? $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['logged_in'] = true;
    }

    /**
     * Destroy user session
     */
    public function destroySession() {
        session_start();
        session_destroy();
    }

    /**
     * Check if user is logged in
     * @return bool
     */
    public function isLoggedIn() {
        session_start();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Get current user data from session
     * @return array|null
     */
    public function getCurrentUser() {
        session_start();
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'user_type' => $_SESSION['user_type'],
                'full_name' => $_SESSION['full_name'],
                'email' => $_SESSION['email']
            ];
        }
        return null;
    }
}
?>