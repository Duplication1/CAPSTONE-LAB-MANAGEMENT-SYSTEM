<?php
/**
 * Login Controller
 * Lab Management System
 */

require_once '../model/database.php';

class LoginController {
    private $userAuth;

    public function __construct() {
        $this->userAuth = new UserAuth();
    }

    /**
     * Handle login form submission
     */
    public function handleLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $loginType = $_POST['login_type'] ?? '';
            
            if ($loginType === 'student') {
                return $this->handleStudentLogin();
            } else {
                return $this->handleStaffLogin();
            }
        }

        return null;
    }

    /**
     * Handle student login with student number only
     */
    private function handleStudentLogin() {
        $studentNumber = trim($_POST['student_number'] ?? '');

        // Validate input
        if (empty($studentNumber)) {
            return [
                'success' => false,
                'message' => 'Student number is required.'
            ];
        }

        // Authenticate student by student number
        $user = $this->userAuth->authenticateStudentByNumber($studentNumber);

        if ($user) {
            // Create session
            $this->userAuth->createSession($user);
            
            return [
                'success' => true,
                'message' => 'Login successful!',
                'redirect' => './student/'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Invalid student number or account is inactive.'
            ];
        }
    }

    /**
     * Handle staff login with username and password (auto-detect user type)
     */
    private function handleStaffLogin() {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Debug logging
        error_log("Login attempt - Username: $username, Password length: " . strlen($password));

        // Validate input
        if (empty($username) || empty($password)) {
            error_log("Login failed: Empty username or password");
            return [
                'success' => false,
                'message' => 'Username and password are required.'
            ];
        }

        // Authenticate user (auto-detect user type)
        $user = $this->userAuth->authenticateAcrossAllTables($username, $password);

        if ($user) {
            error_log("Login successful for user: " . $user['username'] . " as " . $user['user_type']);
            // Create session
            $this->userAuth->createSession($user);
            
            // Redirect based on user type
            $redirectUrl = $this->getRedirectUrl($user['user_type']);
            
            return [
                'success' => true,
                'message' => 'Login successful!',
                'redirect' => $redirectUrl
            ];
        } else {
            error_log("Login failed: Invalid credentials for username: $username");
            return [
                'success' => false,
                'message' => 'Invalid username or password.'
            ];
        }
    }

    /**
     * Get redirect URL based on user type
     * @param string $userType
     * @return string
     */
    private function getRedirectUrl($userType) {
        $redirectUrls = [
            'student' => './student/',
            'professor' => './professor/',
            'itstaff' => './itstaff/',
            'admin' => './admin/'
        ];

        return $redirectUrls[$userType] ?? './';
    }

    /**
     * Handle logout
     */
    public function handleLogout() {
        $this->userAuth->destroySession();
        header('Location: index.php');
        exit();
    }

    /**
     * Check if user is already logged in and redirect
     */
    public function checkExistingLogin() {
        if ($this->userAuth->isLoggedIn()) {
            $user = $this->userAuth->getCurrentUser();
            $redirectUrl = $this->getRedirectUrl($user['user_type']);
            header('Location: ' . $redirectUrl);
            exit();
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    $controller = new LoginController();
    
    switch ($_GET['action']) {
        case 'logout':
            $controller->handleLogout();
            break;
    }
}
?>