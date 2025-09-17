<?php
/**
 * Student Lab Schedule Page - Lab Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a student
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'student') {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Schedule - Lab Management System</title>
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
                    <h1 class="text-2xl font-bold text-gray-900 mb-6">Lab Schedule</h1>
                    
                    <div class="bg-white shadow rounded-lg p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Weekly Lab Schedule</h2>
                        
                        <!-- Calendar Grid -->
                        <div class="grid grid-cols-7 gap-4 mb-4 text-center">
                            <div class="font-semibold text-gray-700 py-2">Monday</div>
                            <div class="font-semibold text-gray-700 py-2">Tuesday</div>
                            <div class="font-semibold text-gray-700 py-2">Wednesday</div>
                            <div class="font-semibold text-gray-700 py-2">Thursday</div>
                            <div class="font-semibold text-gray-700 py-2">Friday</div>
                            <div class="font-semibold text-gray-700 py-2">Saturday</div>
                            <div class="font-semibold text-gray-700 py-2">Sunday</div>
                        </div>
                        
                        <div class="grid grid-cols-7 gap-4">
                            <!-- Monday -->
                            <div class="border rounded p-2 min-h-24">
                                <div class="bg-blue-100 text-blue-800 p-2 rounded text-xs">
                                    <div class="font-semibold">CS101 Lab</div>
                                    <div>10:00 AM</div>
                                    <div>Room 101</div>
                                </div>
                            </div>
                            
                            <!-- Tuesday -->
                            <div class="border rounded p-2 min-h-24"></div>
                            
                            <!-- Wednesday -->
                            <div class="border rounded p-2 min-h-24">
                                <div class="bg-green-100 text-green-800 p-2 rounded text-xs">
                                    <div class="font-semibold">CS201 Lab</div>
                                    <div>2:00 PM</div>
                                    <div>Room 102</div>
                                </div>
                            </div>
                            
                            <!-- Thursday -->
                            <div class="border rounded p-2 min-h-24"></div>
                            
                            <!-- Friday -->
                            <div class="border rounded p-2 min-h-24">
                                <div class="bg-blue-100 text-blue-800 p-2 rounded text-xs">
                                    <div class="font-semibold">CS101 Lab</div>
                                    <div>10:00 AM</div>
                                    <div>Room 101</div>
                                </div>
                            </div>
                            
                            <!-- Weekend -->
                            <div class="border rounded p-2 min-h-24"></div>
                            <div class="border rounded p-2 min-h-24"></div>
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