-- Email 2FA and Auto Attendance Database Updates
-- Lab Management System

USE lab_management_system;

-- Drop SMS-related tables if they exist
DROP TABLE IF EXISTS sms_logs;
DROP TABLE IF EXISTS sms_config;

-- Add email to students table if not exists (ensure it's required for 2FA)
ALTER TABLE students MODIFY COLUMN email VARCHAR(255) NOT NULL;

-- Update 2FA verification codes table for email instead of phone
DROP TABLE IF EXISTS user_2fa_codes;
CREATE TABLE user_2fa_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('student', 'professor', 'itstaff', 'admin') NOT NULL,
    email_address VARCHAR(255) NOT NULL,
    verification_code VARCHAR(6) NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 3,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_lookup (user_id, user_type),
    INDEX idx_email_code (email_address, verification_code),
    INDEX idx_expires (expires_at)
);

-- Create attendance logs table for auto attendance tracking
CREATE TABLE IF NOT EXISTS attendance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_duration INT NULL, -- in seconds
    attendance_date DATE NOT NULL,
    status ENUM('present', 'partial', 'absent') DEFAULT 'present',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student_date (student_id, attendance_date),
    INDEX idx_attendance_date (attendance_date),
    UNIQUE KEY unique_daily_attendance (student_id, attendance_date)
);

-- Create email configuration table for SMTP settings
CREATE TABLE IF NOT EXISTS email_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    smtp_host VARCHAR(255) NOT NULL DEFAULT 'smtp.gmail.com',
    smtp_port INT NOT NULL DEFAULT 587,
    smtp_username VARCHAR(255) NOT NULL,
    smtp_password VARCHAR(255) NOT NULL,
    smtp_encryption ENUM('tls', 'ssl', 'none') DEFAULT 'tls',
    mail_from_address VARCHAR(255) NOT NULL,
    mail_from_name VARCHAR(255) NOT NULL DEFAULT 'Lab Management System',
    is_active BOOLEAN DEFAULT TRUE,
    daily_limit INT DEFAULT 1000,
    monthly_limit INT DEFAULT 10000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create email logs table for tracking
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message_content TEXT NOT NULL,
    status ENUM('pending', 'sent', 'delivered', 'failed') DEFAULT 'pending',
    error_message TEXT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivered_at TIMESTAMP NULL,
    INDEX idx_email_status (recipient_email, status),
    INDEX idx_sent_date (sent_at)
);

-- Update sample students with email addresses for testing
UPDATE students SET email = 'student1@example.com' WHERE student_id = '2024-001';
UPDATE students SET email = 'student2@example.com' WHERE student_id = '2024-002'; 
UPDATE students SET email = 'student3@example.com' WHERE student_id = '2024-003';

-- Remove phone requirement and make it optional
ALTER TABLE students MODIFY COLUMN phone VARCHAR(20) NULL;