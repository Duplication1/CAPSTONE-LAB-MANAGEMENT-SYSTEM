-- Lab Management System Database Structure
-- Create the database
CREATE DATABASE IF NOT EXISTS lab_management_system;
USE lab_management_system;

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    course VARCHAR(100),
    year_level ENUM('1st Year', '2nd Year', '3rd Year', '4th Year') NOT NULL,
    section VARCHAR(10),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Professors table
CREATE TABLE IF NOT EXISTS professors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    department VARCHAR(100) NOT NULL,
    position VARCHAR(100),
    specialization VARCHAR(200),
    status ENUM('active', 'inactive', 'on_leave') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- IT Staff table
CREATE TABLE IF NOT EXISTS it_staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    department VARCHAR(100) DEFAULT 'IT Department',
    position VARCHAR(100),
    access_level ENUM('basic', 'advanced', 'full') DEFAULT 'basic',
    status ENUM('active', 'inactive', 'on_leave') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Administrators table
CREATE TABLE IF NOT EXISTS administrators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    position VARCHAR(100),
    access_level ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Laboratory rooms table
CREATE TABLE IF NOT EXISTS laboratory_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(20) UNIQUE NOT NULL,
    room_name VARCHAR(100) NOT NULL,
    capacity INT NOT NULL,
    equipment_count INT DEFAULT 0,
    status ENUM('available', 'occupied', 'maintenance', 'closed') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Equipment table
CREATE TABLE IF NOT EXISTS equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_code VARCHAR(50) UNIQUE NOT NULL,
    equipment_name VARCHAR(100) NOT NULL,
    equipment_type VARCHAR(50) NOT NULL,
    brand VARCHAR(50),
    model VARCHAR(50),
    serial_number VARCHAR(100),
    laboratory_room_id INT,
    status ENUM('available', 'in_use', 'maintenance', 'damaged', 'retired') DEFAULT 'available',
    purchase_date DATE,
    warranty_expiry DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (laboratory_room_id) REFERENCES laboratory_rooms(id) ON DELETE SET NULL
);

-- Lab sessions table
CREATE TABLE IF NOT EXISTS lab_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_name VARCHAR(100) NOT NULL,
    laboratory_room_id INT NOT NULL,
    professor_id INT,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    max_students INT DEFAULT 30,
    status ENUM('scheduled', 'ongoing', 'completed', 'cancelled') DEFAULT 'scheduled',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (laboratory_room_id) REFERENCES laboratory_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (professor_id) REFERENCES professors(id) ON DELETE SET NULL
);

-- Student session attendance table
CREATE TABLE IF NOT EXISTS session_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lab_session_id INT NOT NULL,
    student_id INT NOT NULL,
    attendance_status ENUM('present', 'absent', 'late') DEFAULT 'present',
    check_in_time TIMESTAMP NULL,
    check_out_time TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lab_session_id) REFERENCES lab_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (lab_session_id, student_id)
);

-- Insert sample data
-- Sample Students
INSERT INTO students (username, password, student_id, first_name, last_name, email, course, year_level, section) VALUES
('john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-001', 'John', 'Doe', 'john.doe@student.edu', 'Computer Science', '3rd Year', 'A'),
('jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-002', 'Jane', 'Smith', 'jane.smith@student.edu', 'Information Technology', '2nd Year', 'B'),
('mike_wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-003', 'Mike', 'Wilson', 'mike.wilson@student.edu', 'Computer Engineering', '4th Year', 'A');

-- Sample Professors
INSERT INTO professors (username, password, employee_id, first_name, last_name, email, department, position, specialization) VALUES
('prof_garcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'PROF-001', 'Maria', 'Garcia', 'maria.garcia@university.edu', 'Computer Science', 'Professor', 'Software Engineering'),
('prof_brown', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'PROF-002', 'Robert', 'Brown', 'robert.brown@university.edu', 'Information Technology', 'Associate Professor', 'Database Systems');

-- Sample IT Staff
INSERT INTO it_staff (username, password, employee_id, first_name, last_name, email, position, access_level) VALUES
('tech_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'IT-001', 'David', 'Johnson', 'david.johnson@university.edu', 'Lab Technician', 'advanced'),
('sys_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'IT-002', 'Sarah', 'Davis', 'sarah.davis@university.edu', 'System Administrator', 'full');

-- Sample Administrators
INSERT INTO administrators (username, password, employee_id, first_name, last_name, email, position, access_level) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADM-001', 'Admin', 'User', 'admin@university.edu', 'System Administrator', 'super_admin'),
('lab_manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADM-002', 'Lisa', 'Anderson', 'lisa.anderson@university.edu', 'Lab Manager', 'admin');

-- Sample Laboratory Rooms
INSERT INTO laboratory_rooms (room_number, room_name, capacity) VALUES
('LAB-101', 'Computer Laboratory 1', 30),
('LAB-102', 'Computer Laboratory 2', 25),
('LAB-201', 'Advanced Computing Lab', 20),
('LAB-301', 'Research Laboratory', 15);

-- Sample Equipment
INSERT INTO equipment (equipment_code, equipment_name, equipment_type, brand, model, laboratory_room_id, purchase_date) VALUES
('PC-001', 'Desktop Computer', 'Computer', 'Dell', 'OptiPlex 7090', 1, '2024-01-15'),
('PC-002', 'Desktop Computer', 'Computer', 'HP', 'EliteDesk 800', 1, '2024-01-15'),
('MON-001', 'Monitor', 'Display', 'Samsung', '24" LED', 1, '2024-01-15'),
('KB-001', 'Keyboard', 'Input Device', 'Logitech', 'K380', 1, '2024-01-15'),
('MS-001', 'Mouse', 'Input Device', 'Logitech', 'M705', 1, '2024-01-15');

-- Note: Default password for all sample users is 'password123'
-- In production, ensure to use strong, unique passwords and proper password hashing