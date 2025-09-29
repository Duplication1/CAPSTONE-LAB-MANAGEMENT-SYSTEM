<?php
/**
 * Attendance Service for Auto Attendance Tracking
 * Lab Management System
 */

class AttendanceService {
    private $db;

    public function __construct($database = null) {
        $this->db = $database ?? new Database();
    }

    /**
     * Log student login attendance automatically
     */
    public function logStudentLogin($studentId, $ipAddress = null, $userAgent = null, $labRoom = null, $pcNumber = null) {
        try {
            $today = date('Y-m-d');
            $ipAddress = $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $userAgent ?? $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            error_log("Attendance Debug - Logging login for student ID: $studentId on $today, Lab: $labRoom, PC: $pcNumber");

            // Always create new attendance record for each login
            $insertStmt = $this->db->getConnection()->prepare("
                INSERT INTO attendance_logs (student_id, login_time, ip_address, user_agent, attendance_date, status, laboratory_room, pc_number) 
                VALUES (?, CURRENT_TIMESTAMP, ?, ?, ?, 'present', ?, ?)
            ");
            $insertStmt->execute([$studentId, $ipAddress, $userAgent, $today, $labRoom, $pcNumber]);
            
            $insertId = $this->db->getConnection()->lastInsertId();
            
            error_log("Attendance Debug - Created new attendance log with ID: $insertId");
            
            return [
                'success' => true,
                'message' => 'New login session recorded',
                'type' => 'new',
                'attendance_id' => $insertId,
                'login_time' => date('Y-m-d H:i:s'),
                'lab_room' => $labRoom,
                'pc_number' => $pcNumber
            ];

        } catch (Exception $e) {
            error_log("Attendance Log Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to log attendance: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Log student logout and calculate session duration
     */
    public function logStudentLogout($studentId) {
        try {
            $today = date('Y-m-d');
            
            error_log("Attendance Debug - Logging logout for student ID: $studentId on $today");
            
            // Find the most recent attendance record without logout time for today
            $stmt = $this->db->getConnection()->prepare("
                SELECT id, login_time FROM attendance_logs 
                WHERE student_id = ? AND attendance_date = ? AND logout_time IS NULL
                ORDER BY login_time DESC LIMIT 1
            ");
            $stmt->execute([$studentId, $today]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($record) {
                error_log("Attendance Debug - Found active session with ID: " . $record['id']);
                
                // Let MySQL calculate the duration to avoid timezone issues
                $updateStmt = $this->db->getConnection()->prepare("
                    UPDATE attendance_logs SET 
                        logout_time = CURRENT_TIMESTAMP,
                        session_duration = TIMESTAMPDIFF(SECOND, login_time, CURRENT_TIMESTAMP),
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");
                $updateStmt->execute([$record['id']]);

                // Get the calculated duration from the database
                $durationStmt = $this->db->getConnection()->prepare("
                    SELECT session_duration FROM attendance_logs WHERE id = ?
                ");
                $durationStmt->execute([$record['id']]);
                $updatedRecord = $durationStmt->fetch(PDO::FETCH_ASSOC);
                $duration = $updatedRecord['session_duration'];

                error_log("Attendance Debug - Logout recorded with duration: $duration seconds");

                return [
                    'success' => true,
                    'message' => 'Logout logged successfully',
                    'session_duration' => $duration,
                    'duration_formatted' => $this->formatDuration($duration),
                    'attendance_id' => $record['id']
                ];
            } else {
                error_log("Attendance Debug - No active session found for logout");
                return [
                    'success' => false,
                    'message' => 'No active session found for today'
                ];
            }

        } catch (Exception $e) {
            error_log("Logout Log Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to log logout: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get student attendance summary
     */
    public function getStudentAttendanceSummary($studentId, $startDate = null, $endDate = null) {
        try {
            $startDate = $startDate ?? date('Y-m-01'); // First day of current month
            $endDate = $endDate ?? date('Y-m-t');     // Last day of current month

            $stmt = $this->db->getConnection()->prepare("
                SELECT 
                    attendance_date,
                    login_time,
                    logout_time,
                    session_duration,
                    status,
                    ip_address
                FROM attendance_logs 
                WHERE student_id = ? AND attendance_date BETWEEN ? AND ?
                ORDER BY attendance_date DESC
            ");
            $stmt->execute([$studentId, $startDate, $endDate]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate statistics
            $totalDays = count($records);
            $presentDays = count(array_filter($records, fn($r) => $r['status'] === 'present'));
            $totalDuration = array_sum(array_column($records, 'session_duration'));
            $avgDuration = $totalDays > 0 ? $totalDuration / $totalDays : 0;

            return [
                'success' => true,
                'data' => [
                    'records' => $records,
                    'summary' => [
                        'total_days' => $totalDays,
                        'present_days' => $presentDays,
                        'attendance_rate' => $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0,
                        'total_duration' => $totalDuration,
                        'average_duration' => $avgDuration,
                        'total_duration_formatted' => $this->formatDuration($totalDuration),
                        'average_duration_formatted' => $this->formatDuration($avgDuration)
                    ]
                ]
            ];

        } catch (Exception $e) {
            error_log("Attendance Summary Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get attendance summary: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get today's attendance status for a student
     */
    public function getTodayAttendance($studentId) {
        try {
            $today = date('Y-m-d');
            
            $stmt = $this->db->getConnection()->prepare("
                SELECT * FROM attendance_logs 
                WHERE student_id = ? AND attendance_date = ?
                ORDER BY login_time DESC LIMIT 1
            ");
            $stmt->execute([$studentId, $today]);
            $record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($record) {
                $currentDuration = null;
                if ($record['logout_time'] === null && $record['login_time']) {
                    // Calculate current session duration
                    $loginTime = new DateTime($record['login_time']);
                    $now = new DateTime();
                    $currentDuration = $now->getTimestamp() - $loginTime->getTimestamp();
                }

                return [
                    'success' => true,
                    'data' => [
                        'has_attendance' => true,
                        'login_time' => $record['login_time'],
                        'logout_time' => $record['logout_time'],
                        'session_duration' => $record['session_duration'],
                        'current_session_duration' => $currentDuration,
                        'status' => $record['status'],
                        'is_active_session' => $record['logout_time'] === null
                    ]
                ];
            } else {
                return [
                    'success' => true,
                    'data' => [
                        'has_attendance' => false,
                        'status' => 'absent'
                    ]
                ];
            }

        } catch (Exception $e) {
            error_log("Today Attendance Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get today\'s attendance: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format duration in seconds to human readable format
     */
    private function formatDuration($seconds) {
        if ($seconds <= 0) return '0 minutes';
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        $formatted = '';
        if ($hours > 0) {
            $formatted .= $hours . ' hour' . ($hours > 1 ? 's' : '');
        }
        if ($minutes > 0) {
            if ($formatted) $formatted .= ' ';
            $formatted .= $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        }
        
        return $formatted ?: '0 minutes';
    }

    /**
     * Get attendance statistics for admin/reports
     */
    public function getAttendanceStatistics($startDate = null, $endDate = null) {
        try {
            $startDate = $startDate ?? date('Y-m-01');
            $endDate = $endDate ?? date('Y-m-t');

            // Overall statistics
            $overallStmt = $this->db->getConnection()->prepare("
                SELECT 
                    COUNT(DISTINCT student_id) as total_students,
                    COUNT(*) as total_attendance_records,
                    AVG(session_duration) as avg_session_duration,
                    SUM(session_duration) as total_session_time
                FROM attendance_logs 
                WHERE attendance_date BETWEEN ? AND ?
            ");
            $overallStmt->execute([$startDate, $endDate]);
            $overall = $overallStmt->fetch(PDO::FETCH_ASSOC);

            // Daily breakdown
            $dailyStmt = $this->db->getConnection()->prepare("
                SELECT 
                    attendance_date,
                    COUNT(*) as student_count,
                    AVG(session_duration) as avg_duration
                FROM attendance_logs 
                WHERE attendance_date BETWEEN ? AND ?
                GROUP BY attendance_date
                ORDER BY attendance_date DESC
            ");
            $dailyStmt->execute([$startDate, $endDate]);
            $daily = $dailyStmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'data' => [
                    'overall' => $overall,
                    'daily_breakdown' => $daily
                ]
            ];

        } catch (Exception $e) {
            error_log("Attendance Statistics Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to get attendance statistics: ' . $e->getMessage()
            ];
        }
    }
}
?>