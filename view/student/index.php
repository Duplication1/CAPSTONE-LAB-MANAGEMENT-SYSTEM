<?php
/**
 * Student Dashboard - Lab Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a student
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'student') {
    header('Location: ../index.php');
    exit();
}

$student_name = $_SESSION['full_name'];
$student_id = $_SESSION['username']; // This should be the student ID
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Lab Management System</title>
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
                <!-- Welcome Section -->
                <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-2">Student Dashboard</h2>
                        <p class="text-sm text-gray-600">View your lab schedules, book equipment, and track your academic progress.</p>
                    </div>
                </div>

                <!-- Dashboard Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <!-- My Enrollments Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">My Courses</dt>
                                        <dd class="text-lg font-medium text-gray-900">--</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <a href="#" class="font-medium text-blue-600 hover:text-blue-500">View all courses</a>
                            </div>
                        </div>
                    </div>

                    <!-- Lab Schedules Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Upcoming Labs</dt>
                                        <dd class="text-lg font-medium text-gray-900">--</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <a href="#" class="font-medium text-green-600 hover:text-green-500">View schedule</a>
                            </div>
                        </div>
                    </div>

                    <!-- Equipment Bookings Card -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">My Bookings</dt>
                                        <dd class="text-lg font-medium text-gray-900">--</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-5 py-3">
                            <div class="text-sm">
                                <a href="#" class="font-medium text-yellow-600 hover:text-yellow-500">Manage bookings</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions and Schedule -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Quick Actions -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Book Lab Equipment
                                </button>
                                <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    View Lab Schedule
                                </button>
                                <button class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Check Attendance
                                </button>
                                <button class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Submit Assignment
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Schedule -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Today's Schedule</h3>
                            <div class="space-y-4">
                                <div class="border-l-4 border-blue-400 pl-4">
                                    <div class="flex justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">No scheduled labs today</p>
                                            <p class="text-sm text-gray-500">Check back for updates</p>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            --:--
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Activity</h3>
                        <div class="flow-root">
                            <ul class="-mb-8">
                                <li>
                                    <div class="relative pb-8">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">Successfully logged into the system</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <time datetime="2025-09-15">Today</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- JavaScript for sidebar toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarClose = document.getElementById('sidebarClose');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const mainContent = document.querySelector('.main-content-area');
            
            let sidebarOpen = false;

            function initializeSidebar() {
                if (window.innerWidth >= 1024) {
                    // Desktop: sidebar open by default
                    sidebar.classList.remove('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                    mainContent.classList.add('sidebar-open');
                    mainContent.classList.remove('sidebar-closed');
                    sidebarOpen = true;
                } else {
                    // Mobile: sidebar closed by default
                    sidebar.classList.add('-translate-x-full');
                    sidebarOverlay.classList.add('hidden');
                    mainContent.classList.remove('sidebar-open', 'sidebar-closed');
                    sidebarOpen = false;
                }
            }

            function toggleSidebar() {
                if (window.innerWidth >= 1024) {
                    // Desktop toggle
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
                    // Mobile toggle
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

            // Initialize sidebar state
            initializeSidebar();

            sidebarToggle?.addEventListener('click', toggleSidebar);
            sidebarClose?.addEventListener('click', closeSidebar);
            sidebarOverlay?.addEventListener('click', closeSidebar);

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 1024) { // lg breakpoint
                    if (!sidebar.contains(event.target) && !sidebarToggle?.contains(event.target)) {
                        closeSidebar();
                    }
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                initializeSidebar();
            });
        });
    </script>
</body>
</html>