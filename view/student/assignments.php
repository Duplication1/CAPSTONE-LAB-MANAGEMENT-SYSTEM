<?php
/**
 * Student Assignments Page - Lab Management System
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
    <title>Assignments - Lab Management System</title>
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
                    <h1 class="text-2xl font-bold text-gray-900 mb-6">Assignments</h1>
                    
                    <!-- Assignment Tabs -->
                    <div class="mb-6">
                        <div class="border-b border-gray-200">
                            <nav class="-mb-px flex space-x-8">
                                <button class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                                    All Assignments
                                </button>
                                <button class="border-blue-500 text-blue-600 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                                    Pending
                                </button>
                                <button class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                                    Submitted
                                </button>
                                <button class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                                    Graded
                                </button>
                            </nav>
                        </div>
                    </div>

                    <!-- Assignments List -->
                    <div class="space-y-6">
                        <!-- Assignment Item -->
                        <div class="bg-white shadow rounded-lg p-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Programming Lab 1: Variables and Data Types</h3>
                                    <p class="text-gray-600 mt-1">CS101 - Computer Programming</p>
                                    <p class="text-gray-500 text-sm mt-2">Create a program that demonstrates different data types and variable declarations.</p>
                                    
                                    <div class="flex items-center mt-4 space-x-4">
                                        <span class="text-sm text-gray-500">Due: September 20, 2025</span>
                                        <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                                        <span class="text-sm text-gray-500">Points: 100</span>
                                    </div>
                                </div>
                                
                                <div class="ml-6 flex flex-col space-y-2">
                                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                        View Assignment
                                    </button>
                                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                                        Submit Work
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Item -->
                        <div class="bg-white shadow rounded-lg p-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Data Structure Implementation: Arrays</h3>
                                    <p class="text-gray-600 mt-1">CS201 - Data Structures</p>
                                    <p class="text-gray-500 text-sm mt-2">Implement various array operations including sorting and searching algorithms.</p>
                                    
                                    <div class="flex items-center mt-4 space-x-4">
                                        <span class="text-sm text-gray-500">Due: September 25, 2025</span>
                                        <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                                        <span class="text-sm text-gray-500">Points: 150</span>
                                    </div>
                                </div>
                                
                                <div class="ml-6 flex flex-col space-y-2">
                                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                        View Assignment
                                    </button>
                                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                                        Submit Work
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Submitted Assignment -->
                        <div class="bg-white shadow rounded-lg p-6">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">Basic Programming Concepts</h3>
                                    <p class="text-gray-600 mt-1">CS101 - Computer Programming</p>
                                    <p class="text-gray-500 text-sm mt-2">Introduction to basic programming concepts and syntax.</p>
                                    
                                    <div class="flex items-center mt-4 space-x-4">
                                        <span class="text-sm text-gray-500">Submitted: September 10, 2025</span>
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Graded</span>
                                        <span class="text-sm text-gray-500">Score: 85/100</span>
                                    </div>
                                </div>
                                
                                <div class="ml-6 flex flex-col space-y-2">
                                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                        View Feedback
                                    </button>
                                    <button class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                                        Download
                                    </button>
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