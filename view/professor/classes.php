<?php
/**
 * Professor Classes Page - Lab Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a professor
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'professor') {
    header('Location: ../index.php');
    exit();
}

// Use your Database class
require_once __DIR__ . '/../../model/database.php';
$db = new Database();
$pdo = $db->getConnection();

// Handle Add Class form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject'])) {
    $professorId = $_SESSION['user_id'] ?? 1; // Change to your session user_id
    $subject = trim($_POST['subject']);
    $description = trim($_POST['subject_description'] ?? '');
    $schedule = trim($_POST['schedule'] ?? '');
    $labroom = trim($_POST['labroom'] ?? '');
    $students = (int)($_POST['students'] ?? 0);

    $stmt = $pdo->prepare("INSERT INTO classes (professor_id, subject, description, schedule, lab_room, students) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$professorId, $subject, $description, $schedule, $labroom, $students]);

    // Redirect to avoid resubmission
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit();
}

// Fetch classes for this professor
$professorId = $_SESSION['user_id'] ?? 1; // Change to your session user_id
$stmt = $pdo->prepare("SELECT * FROM classes WHERE professor_id = ? ORDER BY created_at DESC");
$stmt->execute([$professorId]);
$classes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Classes - Lab Management System</title>
    <link href="../../css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <div class="flex dashboard-layout">
        <!-- Sidebar -->
        <?php include '../components/sidebar.php'; ?>

        <!-- Main content area -->
        <div class="flex flex-col flex-1 main-content-area">
            <!-- Header -->
            <?php include '../components/header.php'; ?>

            <!-- Main content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                <div class="max-w-7xl mx-auto">
                    <h1 class="text-2xl font-bold text-gray-900 mb-6">My Classes</h1>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Add Class Card -->
                        <div class="bg-white shadow rounded-lg p-6 border-2 border-dashed border-gray-300 flex items-center justify-center">
                            <button id="addClassBtn" class="w-full py-6 text-gray-600 hover:text-gray-800">+ Add New Class</button>
                        </div>
                        <!-- Display classes from database -->
                        <?php if (!empty($classes)): ?>
                            <?php foreach ($classes as $class): ?>
                                <div class="bg-white shadow rounded-lg p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($class['subject']); ?></h3>
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full"><?php echo htmlspecialchars($class['status']); ?></span>
                                    </div>
                                    <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($class['description']); ?></p>
                                    <div class="space-y-2 text-sm text-gray-500 mb-4">
                                        <p><strong>Schedule:</strong> <?php echo htmlspecialchars($class['schedule']); ?></p>
                                        <p><strong>Lab Room:</strong> <?php echo htmlspecialchars($class['lab_room']); ?></p>
                                        <p><strong>Students:</strong> <?php echo (int)$class['students']; ?> enrolled</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm flex-1">
                                            Manage Class
                                        </button>
                                        <button class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 rounded text-sm">
                                            View Lab
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-span-full text-center text-gray-500">
                                No classes yet. Click "Add New Class" to create one.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Class Modal -->
    <div id="addClassModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative text-left">
            <button id="closeModalBtn" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            <h2 class="text-xl font-bold mb-4 text-left">Add New Class</h2>
            <form id="addClassForm" method="post" class="space-y-4 text-left">
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 text-left">Subject:</label>
                    <input type="text" id="subject" name="subject" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-left" required>
                </div>
                <div>
                    <label for="subject_description" class="block text-sm font-medium text-gray-700 text-left">Subject Description:</label>
                    <input type="text" id="subject_description" name="subject_description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-left" required>
                </div>
                <div>
                    <label for="schedule" class="block text-sm font-medium text-gray-700 text-left">Schedule:</label>
                    <input type="text" id="schedule" name="schedule" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-left" required>
                </div>
                <div>
                    <label for="labroom" class="block text-sm font-medium text-gray-700 text-left">Lab Room:</label>
                    <input type="text" id="labroom" name="labroom" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-left" required>
                </div>
                <div>
                    <label for="students" class="block text-sm font-medium text-gray-700 text-left">Students:</label>
                    <input type="number" id="students" name="students" min="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-left" required>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript for sidebar toggle & modal -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar sliding logic
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainContent = document.querySelector('.main-content-area');
        let sidebarOpen = false;
        function initializeSidebar() {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
                mainContent.classList.add('sidebar-open');
                mainContent.classList.remove('sidebar-closed');
                sidebarOpen = true;
            } else {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
                mainContent.classList.remove('sidebar-open', 'sidebar-closed');
                sidebarOpen = false;
            }
        }
        function toggleSidebar() {
            if (window.innerWidth >= 1024) {
                if (sidebarOpen) {
                    sidebar.classList.add('-translate-x-full');
                    mainContent.classList.remove('sidebar-open');
                    mainContent.classList.add('sidebar-closed');
                    sidebarOpen = false;
                } else {
                    sidebar.classList.remove('-translate-x-full');
                    mainContent.classList.remove('sidebar-closed');
                    mainContent.classList.add('sidebar-open');
                    sidebarOpen = true;
                }
                sidebarOverlay.classList.add('hidden');
            } else {
                sidebar.classList.toggle('-translate-x-full');
                sidebarOverlay.classList.toggle('hidden');
            }
        }
        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
            if (window.innerWidth >= 1024) {
                mainContent.classList.remove('sidebar-open');
                mainContent.classList.add('sidebar-closed');
                sidebarOpen = false;
            }
        }
        initializeSidebar();
        sidebarToggle?.addEventListener('click', toggleSidebar);
        sidebarClose?.addEventListener('click', closeSidebar);
        sidebarOverlay?.addEventListener('click', closeSidebar);
        document.addEventListener('click', function(event) {
            if (window.innerWidth < 1024) {
                if (!sidebar.contains(event.target) && !sidebarToggle?.contains(event.target)) {
                    closeSidebar();
                }
            }
        });
        window.addEventListener('resize', function() {
            initializeSidebar();
        });

        // Modal logic
        const addClassBtn = document.getElementById('addClassBtn');
        const addClassModal = document.getElementById('addClassModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        addClassBtn?.addEventListener('click', function() {
            addClassModal.classList.remove('hidden');
        });
        closeModalBtn?.addEventListener('click', function() {
            addClassModal.classList.add('hidden');
        });
        addClassModal?.addEventListener('click', function(e) {
            if (e.target === addClassModal) {
                addClassModal.classList.add('hidden');
            }
        });
    });
</script>
</body>
</html>

<!-- CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    professor_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT,
    schedule VARCHAR(255),
    lab_room VARCHAR(100),
    students INT DEFAULT 0,
    status VARCHAR(50) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
); -->