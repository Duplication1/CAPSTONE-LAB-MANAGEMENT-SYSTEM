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
     * Authenticate student by student number only
     * @param string $studentNumber
     * @return array|false
     */
    public function authenticateStudentByNumber($studentNumber) {
        try {
            $query = "SELECT * FROM students WHERE student_id = :student_id AND status = 'active'";
            $stmt = $this->connection->prepare($query);
            $stmt->bindParam(':student_id', $studentNumber);
            $stmt->execute();

            $user = $stmt->fetch();

            if ($user) {
                // Remove password from returned data
                unset($user['password']);
                $user['user_type'] = 'student';
                return $user;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Student authentication error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Authenticate user login (for staff/faculty)
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
     * Authenticate user across all tables (auto-detect user type)
     * @param string $username
     * @param string $password
     * @return array|false
     */
    public function authenticateAcrossAllTables($username, $password) {
        // Define all user tables and their corresponding user types
        $userTables = [
            'professors' => 'professor',
            'it_staff' => 'itstaff', 
            'administrators' => 'admin'
        ];

        try {
            // Check each table for the username
            foreach ($userTables as $table => $userType) {
                $query = "SELECT * FROM " . $table . " WHERE username = :username AND status = 'active'";
                $stmt = $this->connection->prepare($query);
                $stmt->bindParam(':username', $username);
                $stmt->execute();

                $user = $stmt->fetch();
                
                // Debug logging
                error_log("Checking table: $table for username: $username");
                error_log("User found: " . ($user ? 'YES' : 'NO'));
                
                if ($user) {
                    error_log("User status: " . $user['status']);
                    error_log("Password in DB: " . substr($user['password'], 0, 10) . "...");
                    error_log("Input password: " . $password);
                    
                    // Check if password is hashed or plain text
                    if (password_verify($password, $user['password'])) {
                        error_log("Password verified with password_verify");
                        // Remove password from returned data
                        unset($user['password']);
                        $user['user_type'] = $userType;
                        return $user;
                    } else if ($password === $user['password']) {
                        // For plain text passwords (temporary compatibility)
                        error_log("Password matched as plain text");
                        unset($user['password']);
                        $user['user_type'] = $userType;
                        return $user;
                    } else {
                        error_log("Password verification failed");
                    }
                }
            }

            error_log("No user found in any table");
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
        $_SESSION['username'] = $user['username'] ?? $user['student_id'];
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