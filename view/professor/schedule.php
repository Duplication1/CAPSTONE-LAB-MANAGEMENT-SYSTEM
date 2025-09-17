<?php
/**
 * Professor Schedule Page - Lab Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a professor
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'professor') {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule - Lab Management System</title>
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
                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">My Schedule</h1>
                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                            Add Event
                        </button>
                    </div>
                    
                    <!-- Calendar View -->
                    <div class="bg-white shadow rounded-lg mb-6">
                        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                            <h2 class="text-lg font-medium text-gray-900">Weekly Schedule</h2>
                            <div class="flex space-x-2">
                                <button class="px-3 py-1 text-sm border rounded hover:bg-gray-50">Previous</button>
                                <span class="px-3 py-1 text-sm font-medium">September 16-22, 2025</span>
                                <button class="px-3 py-1 text-sm border rounded hover:bg-gray-50">Next</button>
                            </div>
                        </div>
                        <div class="p-6">
                            <!-- Time slots grid -->
                            <div class="grid grid-cols-8 gap-1">
                                <!-- Header row -->
                                <div class="p-2 text-center font-medium text-gray-500">Time</div>
                                <div class="p-2 text-center font-medium text-gray-700">Monday</div>
                                <div class="p-2 text-center font-medium text-gray-700">Tuesday</div>
                                <div class="p-2 text-center font-medium text-gray-700">Wednesday</div>
                                <div class="p-2 text-center font-medium text-gray-700">Thursday</div>
                                <div class="p-2 text-center font-medium text-gray-700">Friday</div>
                                <div class="p-2 text-center font-medium text-gray-700">Saturday</div>
                                <div class="p-2 text-center font-medium text-gray-700">Sunday</div>

                                <!-- 8:00 AM -->
                                <div class="p-2 text-sm text-gray-500 border-t">8:00 AM</div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>

                                <!-- 9:00 AM -->
                                <div class="p-2 text-sm text-gray-500 border-t">9:00 AM</div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>

                                <!-- 10:00 AM -->
                                <div class="p-2 text-sm text-gray-500 border-t">10:00 AM</div>
                                <div class="p-1 border-t">
                                    <div class="bg-blue-100 text-blue-800 p-2 rounded text-xs">
                                        <div class="font-semibold">CS101 Lab</div>
                                        <div>LAB-101</div>
                                    </div>
                                </div>
                                <div class="p-2 border-t"></div>
                                <div class="p-1 border-t">
                                    <div class="bg-blue-100 text-blue-800 p-2 rounded text-xs">
                                        <div class="font-semibold">CS101 Lab</div>
                                        <div>LAB-101</div>
                                    </div>
                                </div>
                                <div class="p-2 border-t"></div>
                                <div class="p-1 border-t">
                                    <div class="bg-blue-100 text-blue-800 p-2 rounded text-xs">
                                        <div class="font-semibold">CS101 Lab</div>
                                        <div>LAB-101</div>
                                    </div>
                                </div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>

                                <!-- 11:00 AM -->
                                <div class="p-2 text-sm text-gray-500 border-t">11:00 AM</div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>

                                <!-- 12:00 PM -->
                                <div class="p-2 text-sm text-gray-500 border-t">12:00 PM</div>
                                <div class="p-2 border-t bg-gray-100">
                                    <div class="text-xs text-gray-600 text-center">Lunch Break</div>
                                </div>
                                <div class="p-2 border-t bg-gray-100">
                                    <div class="text-xs text-gray-600 text-center">Lunch Break</div>
                                </div>
                                <div class="p-2 border-t bg-gray-100">
                                    <div class="text-xs text-gray-600 text-center">Lunch Break</div>
                                </div>
                                <div class="p-2 border-t bg-gray-100">
                                    <div class="text-xs text-gray-600 text-center">Lunch Break</div>
                                </div>
                                <div class="p-2 border-t bg-gray-100">
                                    <div class="text-xs text-gray-600 text-center">Lunch Break</div>
                                </div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>

                                <!-- 1:00 PM -->
                                <div class="p-2 text-sm text-gray-500 border-t">1:00 PM</div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>

                                <!-- 2:00 PM -->
                                <div class="p-2 text-sm text-gray-500 border-t">2:00 PM</div>
                                <div class="p-2 border-t"></div>
                                <div class="p-1 border-t">
                                    <div class="bg-green-100 text-green-800 p-2 rounded text-xs">
                                        <div class="font-semibold">CS201 Lab</div>
                                        <div>LAB-102</div>
                                    </div>
                                </div>
                                <div class="p-2 border-t"></div>
                                <div class="p-1 border-t">
                                    <div class="bg-green-100 text-green-800 p-2 rounded text-xs">
                                        <div class="font-semibold">CS201 Lab</div>
                                        <div>LAB-102</div>
                                    </div>
                                </div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>

                                <!-- 3:00 PM -->
                                <div class="p-2 text-sm text-gray-500 border-t">3:00 PM</div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                                <div class="p-2 border-t"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Today's Schedule -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-lg font-medium text-gray-900">Today's Schedule</h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-center p-4 bg-blue-50 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">CS101 Programming Lab</h3>
                                        <p class="text-gray-600">Introduction to Programming</p>
                                        <p class="text-sm text-gray-500">10:00 AM - 12:00 PM • LAB-101 • 25 students</p>
                                    </div>
                                    <div class="ml-4">
                                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                            Start Lab
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center p-4 bg-green-50 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547A1 1 0 004 17h5l-1.405 1.405A2 2 0 008.5 20h7a2 2 0 00.905-1.595L15 17h5a1 1 0 00-.072-1.572z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">CS201 Data Structures Lab</h3>
                                        <p class="text-gray-600">Advanced Data Structures</p>
                                        <p class="text-sm text-gray-500">2:00 PM - 4:00 PM • LAB-102 • 20 students</p>
                                    </div>
                                    <div class="ml-4">
                                        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
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
        });
    </script>
</body>
</html>