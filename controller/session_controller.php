<?php
/**
 * Session Controller - Lab Management System
 * Handles lab session creation, retrieval, and attendance management
 */

session_start();
require_once __DIR__ . '/../model/database.php';

class SessionController {
    private $db;
    private $connection;

    public function __construct() {
        $this->db = new Database();
        $this->connection = $this->db->getConnection();
    }

    /**
     * Create a new lab session
     */
    public function createSession($data) {
        try {
            // Validate required fields
            $required_fields = ['sessionTitle', 'labRoom', 'sessionDate', 'sessionTime', 'duration', 'maxStudents'];
            foreach ($required_fields as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'message' => "Field '$field' is required"];
                }
            }

            // Calculate end time
            $startDateTime = $data['sessionDate'] . ' ' . $data['sessionTime'];
            $endDateTime = date('Y-m-d H:i:s', strtotime($startDateTime . ' + ' . $data['duration'] . ' hours'));

            // Check for scheduling conflicts
            $conflictQuery = "SELECT id FROM lab_sessions 
                            WHERE laboratory_room_id = :room_id 
                            AND status != 'cancelled'
                            AND (
                                (start_time <= :start_time AND end_time > :start_time) OR
                                (start_time < :end_time AND end_time >= :end_time) OR
                                (start_time >= :start_time AND end_time <= :end_time)
                            )";
            
            $conflictStmt = $this->connection->prepare($conflictQuery);
            $conflictStmt->execute([
                ':room_id' => $data['labRoom'],
                ':start_time' => $startDateTime,
                ':end_time' => $endDateTime
            ]);

            if ($conflictStmt->fetch()) {
                return ['success' => false, 'message' => 'Time slot conflicts with existing session'];
            }

            // Insert new session
            $query = "INSERT INTO lab_sessions 
                     (session_name, laboratory_room_id, professor_id, start_time, end_time, max_students, description, status) 
                     VALUES (:session_name, :lab_room_id, :professor_id, :start_time, :end_time, :max_students, :description, 'scheduled')";

            $stmt = $this->connection->prepare($query);
            $result = $stmt->execute([
                ':session_name' => $data['sessionTitle'],
                ':lab_room_id' => $data['labRoom'],
                ':professor_id' => $_SESSION['user_id'] ?? null,
                ':start_time' => $startDateTime,
                ':end_time' => $endDateTime,
                ':max_students' => $data['maxStudents'],
                ':description' => $data['description'] ?? null
            ]);

            if ($result) {
                return ['success' => true, 'message' => 'Session created successfully', 'session_id' => $this->connection->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to create session'];
            }

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Get sessions for a professor
     */
    public function getProfessorSessions($professorId, $date = null) {
        try {
            // First, update session statuses automatically
            $this->updateSessionStatusesAutomatically();
            
            $dateCondition = $date ? "AND DATE(ls.start_time) = :date" : "";
            
            $query = "SELECT ls.*, lr.room_number, lr.room_name, lr.capacity,
                            COUNT(sa.id) as enrolled_students
                     FROM lab_sessions ls
                     LEFT JOIN laboratory_rooms lr ON ls.laboratory_room_id = lr.id
                     LEFT JOIN session_attendance sa ON ls.id = sa.lab_session_id
                     WHERE ls.professor_id = :professor_id $dateCondition
                     GROUP BY ls.id
                     ORDER BY ls.start_time ASC";

            $stmt = $this->connection->prepare($query);
            $params = [':professor_id' => $professorId];
            if ($date) {
                $params[':date'] = $date;
            }
            
            $stmt->execute($params);
            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("Error fetching professor sessions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get available sessions for students by room
     */
    public function getAvailableSessionsByRoom($roomId, $studentId = null) {
        try {
            // First, update session statuses automatically
            $this->updateSessionStatusesAutomatically();
            
            $studentEnrollmentQuery = "";
            if ($studentId) {
                $studentEnrollmentQuery = ", (SELECT COUNT(*) FROM session_attendance WHERE lab_session_id = ls.id AND student_id = :student_id) as is_enrolled";
            }

            $query = "SELECT ls.*, lr.room_number, lr.room_name, lr.capacity,
                            p.first_name, p.last_name,
                            COUNT(sa.id) as enrolled_students
                            $studentEnrollmentQuery
                     FROM lab_sessions ls
                     LEFT JOIN laboratory_rooms lr ON ls.laboratory_room_id = lr.id
                     LEFT JOIN professors p ON ls.professor_id = p.id
                     LEFT JOIN session_attendance sa ON ls.id = sa.lab_session_id
                     WHERE ls.laboratory_room_id = :room_id 
                     AND ls.status = 'ongoing'
                     GROUP BY ls.id
                     HAVING enrolled_students < ls.max_students
                     ORDER BY ls.start_time ASC";

            $stmt = $this->connection->prepare($query);
            $params = [':room_id' => $roomId];
            if ($studentId) {
                $params[':student_id'] = $studentId;
            }
            $stmt->execute($params);
            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("Error fetching available sessions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get student's enrolled sessions (upcoming)
     */
    public function getStudentSessions($studentId) {
        try {
            $query = "SELECT ls.*, lr.room_number, lr.room_name,
                            p.first_name, p.last_name,
                            sa.attendance_status, sa.check_in_time,
                            COUNT(all_sa.id) as total_enrolled
                     FROM session_attendance sa
                     JOIN lab_sessions ls ON sa.lab_session_id = ls.id
                     LEFT JOIN laboratory_rooms lr ON ls.laboratory_room_id = lr.id
                     LEFT JOIN professors p ON ls.professor_id = p.id
                     LEFT JOIN session_attendance all_sa ON ls.id = all_sa.lab_session_id
                     WHERE sa.student_id = :student_id 
                     AND ls.status IN ('scheduled', 'ongoing')
                     GROUP BY ls.id
                     ORDER BY ls.start_time ASC";

            $stmt = $this->connection->prepare($query);
            $stmt->execute([':student_id' => $studentId]);
            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("Error fetching student sessions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get student's session history (past sessions)
     */
    public function getStudentSessionHistory($studentId) {
        try {
            $query = "SELECT ls.*, lr.room_number, lr.room_name,
                            p.first_name, p.last_name,
                            sa.attendance_status, sa.check_in_time, sa.check_out_time
                     FROM session_attendance sa
                     JOIN lab_sessions ls ON sa.lab_session_id = ls.id
                     LEFT JOIN laboratory_rooms lr ON ls.laboratory_room_id = lr.id
                     LEFT JOIN professors p ON ls.professor_id = p.id
                     WHERE sa.student_id = :student_id 
                     AND ls.status = 'completed'
                     ORDER BY ls.start_time DESC
                     LIMIT 20";

            $stmt = $this->connection->prepare($query);
            $stmt->execute([':student_id' => $studentId]);
            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("Error fetching student session history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Join a session (student enrollment)
     */
    public function joinSession($sessionId, $studentId) {
        try {
            // First, update session statuses automatically
            $this->updateSessionStatusesAutomatically();
            
            // Check if session exists and is joinable (must be ongoing)
            $sessionQuery = "SELECT ls.*, COUNT(sa.id) as enrolled_students
                           FROM lab_sessions ls
                           LEFT JOIN session_attendance sa ON ls.id = sa.lab_session_id
                           WHERE ls.id = :session_id AND ls.status = 'ongoing'
                           GROUP BY ls.id";
            
            $sessionStmt = $this->connection->prepare($sessionQuery);
            $sessionStmt->execute([':session_id' => $sessionId]);
            $session = $sessionStmt->fetch();

            if (!$session) {
                return ['success' => false, 'message' => 'Session not found or not available (session must be active to join)'];
            }

            if ($session['enrolled_students'] >= $session['max_students']) {
                return ['success' => false, 'message' => 'Session is full'];
            }

            // Check if student is already enrolled
            $enrollmentQuery = "SELECT id FROM session_attendance WHERE lab_session_id = :session_id AND student_id = :student_id";
            $enrollmentStmt = $this->connection->prepare($enrollmentQuery);
            $enrollmentStmt->execute([':session_id' => $sessionId, ':student_id' => $studentId]);
            
            if ($enrollmentStmt->fetch()) {
                return ['success' => false, 'message' => 'Already enrolled in this session'];
            }

            // Determine attendance status based on current time (using database time for consistency)
            $dbTimeQuery = "SELECT NOW() as current_db_time";
            $dbTimeStmt = $this->connection->prepare($dbTimeQuery);
            $dbTimeStmt->execute();
            $dbTimeResult = $dbTimeStmt->fetch();
            $currentTime = new DateTime($dbTimeResult['current_db_time']);
            
            $sessionStart = new DateTime($session['start_time']);
            $sessionEnd = new DateTime($session['end_time']);
            $lateThreshold = clone $sessionStart;
            $lateThreshold->add(new DateInterval('PT15M')); // Add 15 minutes

            $attendanceStatus = 'present';
            $checkInTime = $currentTime->format('Y-m-d H:i:s');

            if ($currentTime > $sessionEnd) {
                $attendanceStatus = 'absent';
            } elseif ($currentTime > $lateThreshold) {
                $attendanceStatus = 'late';
            }

            // Insert attendance record
            $insertQuery = "INSERT INTO session_attendance 
                          (lab_session_id, student_id, attendance_status, check_in_time) 
                          VALUES (:session_id, :student_id, :status, :check_in_time)";
            
            $insertStmt = $this->connection->prepare($insertQuery);
            $result = $insertStmt->execute([
                ':session_id' => $sessionId,
                ':student_id' => $studentId,
                ':status' => $attendanceStatus,
                ':check_in_time' => $checkInTime
            ]);

            if ($result) {
                return [
                    'success' => true, 
                    'message' => 'Successfully joined session',
                    'status' => $attendanceStatus
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to join session'];
            }

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Get laboratory rooms
     */
    public function getLaboratoryRooms() {
        try {
            $query = "SELECT * FROM laboratory_rooms WHERE status = 'available' ORDER BY room_number";
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error fetching laboratory rooms: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update session status
     */
    public function updateSessionStatus($sessionId, $status) {
        try {
            $query = "UPDATE lab_sessions SET status = :status WHERE id = :session_id";
            $stmt = $this->connection->prepare($query);
            return $stmt->execute([':status' => $status, ':session_id' => $sessionId]);
        } catch (Exception $e) {
            error_log("Error updating session status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Automatically update session statuses based on current time
     */
    public function updateSessionStatusesAutomatically() {
        try {
            // Start sessions that should be ongoing (use database time for consistency)
            $startQuery = "UPDATE lab_sessions 
                          SET status = 'ongoing' 
                          WHERE status = 'scheduled' 
                          AND start_time <= NOW() 
                          AND end_time > NOW()";
            
            $stmt = $this->connection->prepare($startQuery);
            $stmt->execute();
            $startedSessions = $stmt->rowCount();
            
            // End sessions that should be completed
            $endQuery = "UPDATE lab_sessions 
                        SET status = 'completed' 
                        WHERE status = 'ongoing' 
                        AND end_time <= NOW()";
            
            $stmt = $this->connection->prepare($endQuery);
            $stmt->execute();
            $endedSessions = $stmt->rowCount();
            
            return [
                'started' => $startedSessions,
                'ended' => $endedSessions
            ];
            
        } catch (Exception $e) {
            error_log("Error updating session statuses automatically: " . $e->getMessage());
            return false;
        }
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    $controller = new SessionController();
    $action = $_POST['action'] ?? 'create';

    switch ($action) {
        case 'create':
            if ($_SESSION['user_type'] !== 'professor') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            $result = $controller->createSession($_POST);
            echo json_encode($result);
            break;

        case 'join':
            if ($_SESSION['user_type'] !== 'student') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            $sessionId = $_POST['sessionId'] ?? null;
            $studentId = $_SESSION['user_id'] ?? null;
            
            if (!$sessionId || !$studentId) {
                echo json_encode(['success' => false, 'message' => 'Missing required data']);
                exit;
            }
            
            $result = $controller->joinSession($sessionId, $studentId);
            echo json_encode($result);
            break;

        case 'get_sessions_by_room':
            $roomId = $_POST['roomId'] ?? null;
            if (!$roomId) {
                echo json_encode(['success' => false, 'message' => 'Room ID required']);
                exit;
            }
            
            $studentId = ($_SESSION['user_type'] === 'student') ? $_SESSION['user_id'] : null;
            $sessions = $controller->getAvailableSessionsByRoom($roomId, $studentId);
            echo json_encode(['success' => true, 'sessions' => $sessions]);
            break;

        case 'update_status':
            if ($_SESSION['user_type'] !== 'professor') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            
            $sessionId = $_POST['sessionId'] ?? null;
            $status = $_POST['status'] ?? null;
            
            if (!$sessionId || !$status) {
                echo json_encode(['success' => false, 'message' => 'Session ID and status required']);
                exit;
            }
            
            $validStatuses = ['scheduled', 'ongoing', 'completed', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
                exit;
            }
            
            $result = $controller->updateSessionStatus($sessionId, $status);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Session status updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update session status']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// Handle GET requests for data retrieval
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    $controller = new SessionController();
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'get_rooms':
            $rooms = $controller->getLaboratoryRooms();
            echo json_encode(['success' => true, 'rooms' => $rooms]);
            break;

        case 'get_professor_sessions':
            if ($_SESSION['user_type'] !== 'professor') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            $professorId = $_SESSION['user_id'];
            $date = $_GET['date'] ?? null;
            $sessions = $controller->getProfessorSessions($professorId, $date);
            echo json_encode(['success' => true, 'sessions' => $sessions]);
            break;

        case 'get_student_sessions':
            if ($_SESSION['user_type'] !== 'student') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            $studentId = $_SESSION['user_id'];
            $sessions = $controller->getStudentSessions($studentId);
            echo json_encode(['success' => true, 'sessions' => $sessions]);
            break;

        case 'get_student_history':
            if ($_SESSION['user_type'] !== 'student') {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                exit;
            }
            $studentId = $_SESSION['user_id'];
            $history = $controller->getStudentSessionHistory($studentId);
            echo json_encode(['success' => true, 'history' => $history]);
            break;

        case 'update_session_statuses':
            // This endpoint can be called to manually trigger automatic status updates
            $result = $controller->updateSessionStatusesAutomatically();
            if ($result !== false) {
                echo json_encode([
                    'success' => true, 
                    'message' => "Status updated: {$result['started']} sessions started, {$result['ended']} sessions ended"
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update session statuses']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}
?>