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
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $userType = $_POST['user_type'] ?? '';

            // Validate input
            if (empty($username) || empty($password) || empty($userType)) {
                return [
                    'success' => false,
                    'message' => 'All fields are required.'
                ];
            }

            // Authenticate user
            $user = $this->userAuth->authenticate($username, $password, $userType);

            if ($user) {
                // Create session
                $this->userAuth->createSession($user);
                
                // Redirect based on user type
                $redirectUrl = $this->getRedirectUrl($userType);
                
                return [
                    'success' => true,
                    'message' => 'Login successful!',
                    'redirect' => $redirectUrl
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid username, password, or user type.'
                ];
            }
        }

        return null;
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