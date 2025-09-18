<?php
/**
 * Email Service for 2FA
 * Lab Management System
 */

// Load environment variables
require_once __DIR__ . '/../config/env_loader.php';

// Load PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $db;
    private $config;

    public function __construct($database = null) {
        $this->db = $database ?? new Database();
        $this->loadConfig();
    }

    /**
     * Load email configuration from environment
     */
    private function loadConfig() {
        try {
            $this->config = [
                'smtp_host' => EnvLoader::get('SMTP_HOST', 'localhost'),
                'smtp_port' => EnvLoader::get('SMTP_PORT', '587'),
                'smtp_username' => EnvLoader::get('SMTP_USERNAME', ''),
                'smtp_password' => EnvLoader::get('SMTP_PASSWORD', ''),
                'smtp_encryption' => EnvLoader::get('SMTP_ENCRYPTION', 'tls'),
                'mail_from_address' => EnvLoader::get('MAIL_FROM_ADDRESS', 'noreply@labmanagement.com'),
                'mail_from_name' => EnvLoader::get('MAIL_FROM_NAME', 'Lab Management System'),
                'is_active' => true
            ];
            error_log("Email Config: Loaded from environment variables");
        } catch (Exception $e) {
            error_log("Email Config Error: " . $e->getMessage());
            $this->config = null;
        }
    }

    /**
     * Generate a 6-digit verification code
     */
    public function generateCode() {
        return sprintf('%06d', mt_rand(100000, 999999));
    }

    /**
     * Send email verification code
     */
    public function sendVerificationCode($userId, $userType, $email) {
        try {
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email address format");
            }

            // Generate verification code
            $code = $this->generateCode();
            // Use FROM_UNIXTIME to ensure consistent timezone handling
            $expiresTimestamp = time() + 600; // 10 minutes from now
            $expiresAt = date('Y-m-d H:i:s', $expiresTimestamp);

            error_log("Email Send Debug - Generated code: $code for user $userId ($userType)");
            error_log("Email Send Debug - Expires at: $expiresAt (timestamp: $expiresTimestamp)");

            // Store verification code in database
            $this->storeVerificationCode($userId, $userType, $email, $code, $expiresTimestamp);

            // Send email
            $subject = "Lab Management System - Verification Code";
            $message = $this->getVerificationEmailTemplate($code);
            $result = $this->sendEmail($email, $subject, $message);

            if ($result['success']) {
                return [
                    'success' => true,
                    'message' => 'Verification code sent to your email',
                    'expires_in' => 600 // 10 minutes in seconds
                ];
            } else {
                throw new Exception($result['error'] ?? 'Failed to send email');
            }

        } catch (Exception $e) {
            error_log("Email Send Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to send verification code: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify email code
     */
    public function verifyCode($userId, $userType, $code) {
        try {
            // Set timezone to match PHP
            $this->db->getConnection()->exec("SET time_zone = '" . date('P') . "'");
            
            error_log("Email Verify Debug - UserId: $userId, UserType: $userType, Code: $code");
            
            // Use timezone-consistent comparison
            $stmt = $this->db->getConnection()->prepare("
                SELECT * FROM user_2fa_codes 
                WHERE user_id = ? AND user_type = ? AND verification_code = ? 
                AND is_used = FALSE AND expires_at > NOW() 
                AND attempts < max_attempts
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$userId, $userType, $code]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            error_log("Email Verify Debug - Record found: " . ($record ? 'Yes' : 'No'));
            if ($record) {
                error_log("Email Verify Debug - Record details: " . json_encode($record));
            }

            // Also check what codes exist for this user
            $checkStmt = $this->db->getConnection()->prepare("
                SELECT id, verification_code, expires_at, is_used, attempts, created_at,
                       UNIX_TIMESTAMP(expires_at) as expires_timestamp,
                       UNIX_TIMESTAMP(NOW()) as now_timestamp
                FROM user_2fa_codes 
                WHERE user_id = ? AND user_type = ? 
                ORDER BY created_at DESC LIMIT 3
            ");
            $checkStmt->execute([$userId, $userType]);
            $allCodes = $checkStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Email Verify Debug - All recent codes for user: " . json_encode($allCodes));

            if (!$record) {
                // Increment attempts for any existing non-expired codes
                $this->incrementAttempts($userId, $userType);
                return [
                    'success' => false,
                    'message' => 'Invalid or expired verification code'
                ];
            }

            // Mark code as used
            $updateStmt = $this->db->getConnection()->prepare("
                UPDATE user_2fa_codes SET is_used = TRUE WHERE id = ?
            ");
            $updateStmt->execute([$record['id']]);

            return [
                'success' => true,
                'message' => 'Code verified successfully'
            ];

        } catch (Exception $e) {
            error_log("Email Verify Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Verification failed'
            ];
        }
    }

    /**
     * Store verification code in database
     */
    private function storeVerificationCode($userId, $userType, $email, $code, $expiresTimestamp) {
        // Set timezone to match PHP
        $this->db->getConnection()->exec("SET time_zone = '" . date('P') . "'");
        
        // Clean up old codes for this user
        $cleanupStmt = $this->db->getConnection()->prepare("
            UPDATE user_2fa_codes SET is_used = TRUE 
            WHERE user_id = ? AND user_type = ? AND is_used = FALSE
        ");
        $cleanupStmt->execute([$userId, $userType]);

        // Insert new code using the timestamp directly as datetime string
        $expiresAt = date('Y-m-d H:i:s', $expiresTimestamp);
        $stmt = $this->db->getConnection()->prepare("
            INSERT INTO user_2fa_codes (user_id, user_type, email_address, verification_code, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$userId, $userType, $email, $code, $expiresAt]);
    }

    /**
     * Increment failed attempts
     */
    private function incrementAttempts($userId, $userType) {
        // Set timezone to match PHP
        $this->db->getConnection()->exec("SET time_zone = '" . date('P') . "'");
        
        $stmt = $this->db->getConnection()->prepare("
            UPDATE user_2fa_codes SET attempts = attempts + 1 
            WHERE user_id = ? AND user_type = ? AND is_used = FALSE 
            AND expires_at > NOW()
        ");
        $stmt->execute([$userId, $userType]);
    }

    /**
     * Send email using PHPMailer
     */
    private function sendEmail($to, $subject, $body) {
        try {
            if (!$this->config) {
                return ['success' => false, 'error' => 'Email service not configured'];
            }

            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_username'];
            $mail->Password = $this->config['smtp_password'];
            $mail->SMTPSecure = $this->config['smtp_encryption'];
            $mail->Port = $this->config['smtp_port'];

            // Recipients
            $mail->setFrom($this->config['mail_from_address'], $this->config['mail_from_name']);
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();

            // Log successful email
            $this->logEmail($to, $subject, 'smtp', 'sent');
            error_log("Email sent successfully to: $to");

            return [
                'success' => true,
                'message_id' => uniqid('email_')
            ];

        } catch (Exception $e) {
            $errorMsg = "PHPMailer Error: " . $mail->ErrorInfo;
            $this->logEmail($to, $subject, 'smtp', 'failed', $errorMsg);
            error_log("Email failed: $errorMsg");

            return [
                'success' => false,
                'error' => $errorMsg
            ];
        }
    }

    /**
     * Get verification email template
     */
    private function getVerificationEmailTemplate($code) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f4; }
                .container { max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                .header { text-align: center; color: #333; margin-bottom: 30px; }
                .code { font-size: 32px; font-weight: bold; color: #007bff; text-align: center; letter-spacing: 5px; margin: 30px 0; padding: 20px; background-color: #f8f9fa; border-radius: 5px; }
                .footer { margin-top: 30px; font-size: 14px; color: #666; text-align: center; }
                .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>Lab Management System</h1>
                    <h2>Email Verification Code</h2>
                </div>
                
                <p>Hello,</p>
                <p>You have requested a verification code for your Lab Management System account. Please use the code below to complete your verification:</p>
                
                <div class='code'>$code</div>
                
                <div class='warning'>
                    <strong>Important:</strong>
                    <ul>
                        <li>This code will expire in 10 minutes</li>
                        <li>Do not share this code with anyone</li>
                        <li>If you didn't request this code, please ignore this email</li>
                    </ul>
                </div>
                
                <div class='footer'>
                    <p>This is an automated message from Lab Management System.</p>
                    <p>If you need assistance, please contact your system administrator.</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Log email in database
     */
    private function logEmail($email, $subject, $provider, $status, $error = null) {
        try {
            $stmt = $this->db->getConnection()->prepare("
                INSERT INTO email_logs (recipient_email, subject, message_content, status, error_message) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$email, $subject, 'Email verification code', $status, $error]);
        } catch (Exception $e) {
            error_log("Email Log Error: " . $e->getMessage());
        }
    }

    /**
     * Check if user has exceeded daily email limit
     */
    public function checkDailyLimit($email) {
        $stmt = $this->db->getConnection()->prepare("
            SELECT COUNT(*) as count FROM email_logs 
            WHERE recipient_email = ? AND DATE(sent_at) = CURDATE()
        ");
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] < 10; // Max 10 emails per day per address
    }
}
?>