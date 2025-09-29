<?php
/**
 * Login Controller with Email 2FA and Auto Attendance
 * Lab Management System
 */

require_once '../model/database.php';
require_once '../model/email_service.php';
require_once '../model/attendance_service.php';

class LoginController {
    private $userAuth;
    private $emailService;
    private $attendanceService;

    public function __construct() {
        $this->userAuth = new UserAuth();
        $this->emailService = new EmailService();
        $this->attendanceService = new AttendanceService();
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
     * Handle student login with student number and Email 2FA
     */
    private function handleStudentLogin() {
        $studentNumber = trim($_POST['student_number'] ?? '');
        $labRoom = trim($_POST['lab_room'] ?? '');
        $pcNumber = trim($_POST['pc_number'] ?? '');

        // Validate input
        if (empty($studentNumber)) {
            return [
                'success' => false,
                'message' => 'Student number is required.'
            ];
        }

        if (empty($labRoom)) {
            return [
                'success' => false,
                'message' => 'Laboratory room selection is required.'
            ];
        }

        if (empty($pcNumber)) {
            return [
                'success' => false,
                'message' => 'Computer/PC selection is required.'
            ];
        }

        // Get student by student number (first step verification)
        $user = $this->userAuth->getStudentByNumber($studentNumber);

        if (!$user) {
            return [
                'success' => false,
                'message' => 'Invalid student number or account is inactive.'
            ];
        }

        // Check if email is available for 2FA
        if (empty($user['email'])) {
            return [
                'success' => false,
                'message' => 'Email address not found. Please contact administration to update your email address.'
            ];
        }

        // Check daily email limit
        if (!$this->emailService->checkDailyLimit($user['email'])) {
            return [
                'success' => false,
                'message' => 'Daily email limit exceeded. Please try again tomorrow or contact support.'
            ];
        }

        // Send email verification code
        $emailResult = $this->emailService->sendVerificationCode($user['id'], 'student', $user['email']);
        
        if ($emailResult['success']) {
            // Store user info in session temporarily for 2FA process
            session_start();
            $_SESSION['2fa_user_id'] = $user['id'];
            $_SESSION['2fa_user_type'] = 'student';
            $_SESSION['2fa_email'] = $user['email'];
            $_SESSION['2fa_expires'] = time() + 600; // 10 minutes
            $_SESSION['2fa_lab_room'] = $labRoom;
            $_SESSION['2fa_pc_number'] = $pcNumber;
            
            return [
                'success' => true,
                'message' => 'Verification code sent to your email',
                'requires_2fa' => true,
                'email_masked' => $this->maskEmail($user['email']),
                'expires_in' => 600
            ];
        } else {
            return [
                'success' => false,
                'message' => $emailResult['message']
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
            'student' => '../view/student/',
            'professor' => '../view/professor/',
            'itstaff' => '../view/itstaff/',
            'admin' => '../view/admin/'
        ];

        return $redirectUrls[$userType] ?? '../view/';
    }

    /**
     * Handle logout with attendance tracking
     */
    public function handleLogout() {
        session_start();
        $user = $this->userAuth->getCurrentUser();
        
        // Log attendance for students
        if ($user && $user['user_type'] === 'student') {
            $this->attendanceService->logStudentLogout($user['id']);
        }
        
        $this->userAuth->destroySession();
        header('Location: index.php');
        exit();
    }

    /**
     * Verify Email 2FA code
     */
    public function verify2FA() {
        session_start();
        
        error_log("Verify2FA Debug - Session data: " . json_encode([
            '2fa_user_id' => $_SESSION['2fa_user_id'] ?? 'not set',
            '2fa_expires' => $_SESSION['2fa_expires'] ?? 'not set',
            'current_time' => time(),
            'time_left' => ($_SESSION['2fa_expires'] ?? 0) - time()
        ]));
        
        // Check if 2FA session is valid
        if (!isset($_SESSION['2fa_user_id']) || !isset($_SESSION['2fa_expires']) || $_SESSION['2fa_expires'] < time()) {
            error_log("Verify2FA Debug - Session expired or invalid");
            return [
                'success' => false,
                'message' => '2FA session expired. Please login again.'
            ];
        }

        $code = trim($_POST['verification_code'] ?? '');
        error_log("Verify2FA Debug - Code entered: '$code'");
        
        if (empty($code)) {
            return [
                'success' => false,
                'message' => 'Verification code is required.'
            ];
        }

        // Verify the code
        $verifyResult = $this->emailService->verifyCode(
            $_SESSION['2fa_user_id'], 
            $_SESSION['2fa_user_type'], 
            $code
        );

        error_log("Verify2FA Debug - Verify result: " . json_encode($verifyResult));

        if ($verifyResult['success']) {
            // Get full user details
            $user = $this->getUserById($_SESSION['2fa_user_id'], $_SESSION['2fa_user_type']);
            
            error_log("Verify2FA Debug - User retrieved: " . ($user ? 'Yes' : 'No'));
            if ($user) {
                error_log("Verify2FA Debug - User type: " . $user['user_type']);
                
                // Store lab room and PC info before clearing 2FA session
                $labRoom = $_SESSION['2fa_lab_room'] ?? null;
                $pcNumber = $_SESSION['2fa_pc_number'] ?? null;
                
                // Clear 2FA session data
                unset($_SESSION['2fa_user_id'], $_SESSION['2fa_user_type'], $_SESSION['2fa_email'], $_SESSION['2fa_expires'], $_SESSION['2fa_lab_room'], $_SESSION['2fa_pc_number']);
                
                // Create main session
                $this->userAuth->createSession($user);
                
                // Add lab room and PC information to main session for students
                if ($user['user_type'] === 'student' && $labRoom && $pcNumber) {
                    $_SESSION['lab_room'] = $labRoom;
                    $_SESSION['pc_number'] = $pcNumber;
                }
                
                // Log attendance for students
                if ($user['user_type'] === 'student') {
                    $attendanceResult = $this->attendanceService->logStudentLogin(
                        $user['id'], 
                        $_SERVER['REMOTE_ADDR'] ?? null, 
                        $_SERVER['HTTP_USER_AGENT'] ?? null,
                        $labRoom,
                        $pcNumber
                    );
                }
                
                $redirectUrl = $this->getRedirectUrl($user['user_type']);
                error_log("Verify2FA Debug - Redirect URL: " . $redirectUrl);
                
                return [
                    'success' => true,
                    'message' => 'Login successful!',
                    'redirect' => $redirectUrl,
                    'attendance' => $attendanceResult ?? null
                ];
            } else {
                error_log("Verify2FA Debug - Failed to get user details");
                return [
                    'success' => false,
                    'message' => 'User not found or inactive'
                ];
            }
        }

        error_log("Verify2FA Debug - Code verification failed: " . $verifyResult['message']);
        return [
            'success' => false,
            'message' => $verifyResult['message']
        ];
    }

    /**
     * Resend Email verification code
     */
    public function resend2FA() {
        session_start();
        
        // Check if 2FA session is valid
        if (!isset($_SESSION['2fa_user_id']) || !isset($_SESSION['2fa_expires']) || $_SESSION['2fa_expires'] < time()) {
            return [
                'success' => false,
                'message' => '2FA session expired. Please login again.'
            ];
        }

        // Check daily email limit
        if (!$this->emailService->checkDailyLimit($_SESSION['2fa_email'])) {
            return [
                'success' => false,
                'message' => 'Daily email limit exceeded. Please try again tomorrow.'
            ];
        }

        // Resend email
        $emailResult = $this->emailService->sendVerificationCode(
            $_SESSION['2fa_user_id'], 
            $_SESSION['2fa_user_type'], 
            $_SESSION['2fa_email']
        );

        if ($emailResult['success']) {
            // Extend 2FA session
            $_SESSION['2fa_expires'] = time() + 600;
            
            return [
                'success' => true,
                'message' => 'New verification code sent to your email',
                'expires_in' => 600
            ];
        }

        return [
            'success' => false,
            'message' => $emailResult['message']
        ];
    }

    /**
     * Get user by ID and type
     */
    private function getUserById($userId, $userType) {
        try {
            $table = '';
            switch ($userType) {
                case 'student':
                    $table = 'students';
                    break;
                case 'professor':
                    $table = 'professors';
                    break;
                case 'itstaff':
                    $table = 'it_staff';
                    break;
                case 'admin':
                    $table = 'administrators';
                    break;
                default:
                    return null;
            }

            $db = new Database();
            $stmt = $db->getConnection()->prepare("SELECT * FROM {$table} WHERE id = ? AND status = 'active'");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $user['user_type'] = $userType;
                return $user;
            }
        } catch (Exception $e) {
            error_log("Get User Error: " . $e->getMessage());
        }
        
        return null;
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

    /**
     * Mask email address for privacy
     */
    private function maskEmail($email) {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email; // Invalid email format
        }
        
        $username = $parts[0];
        $domain = $parts[1];
        
        // Mask the username part
        if (strlen($username) <= 2) {
            $maskedUsername = str_repeat('*', strlen($username));
        } else {
            $maskedUsername = $username[0] . str_repeat('*', strlen($username) - 2) . $username[-1];
        }
        
        return $maskedUsername . '@' . $domain;
    }
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    $controller = new LoginController();
    
    switch ($_GET['action']) {
        case 'logout':
            $controller->handleLogout();
            break;
            
        case 'verify_2fa':
            header('Content-Type: application/json');
            echo json_encode($controller->verify2FA());
            exit();
            
        case 'resend_2fa':
            header('Content-Type: application/json');
            echo json_encode($controller->resend2FA());
            exit();
    }
}

// Handle regular login form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['action'])) {
    $controller = new LoginController();
    $result = $controller->handleLogin();
    
    if ($result) {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit();
    }
}



?>