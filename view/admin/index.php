<?php
/**
 * Administrator Dashboard - Lab Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$admin_name = $_SESSION['full_name'];
$admin_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard - Lab Management System</title>
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
                        <h2 class="text-lg font-medium text-gray-900 mb-2">Administrator Control Panel</h2>
                        <p class="text-sm text-gray-600">Manage the entire lab management system, users, and administrative functions.</p>
                    </div>
                </div>

                <!-- System Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Users Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                                <dd class="text-lg font-medium text-gray-900">--</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Labs Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Labs</dt>
                                <dd class="text-lg font-medium text-gray-900">--</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Items Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Equipment</dt>
                                <dd class="text-lg font-medium text-gray-900">--</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Status Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">System Status</dt>
                                <dd class="text-lg font-medium text-green-600">Operational</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Administration Tools -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- User Management -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">User Management</h3>
                            <div class="space-y-3">
                                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Manage Students
                                </button>
                                <button class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Manage Professors
                                </button>
                                <button class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Manage IT Staff
                                </button>
                                <button class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    User Permissions
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Lab Management -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Lab Management</h3>
                            <div class="space-y-3">
                                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Lab Rooms
                                </button>
                                <button class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Equipment Inventory
                                </button>
                                <button class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Scheduling
                                </button>
                                <button class="w-full bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Maintenance Logs
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- System Administration -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">System Administration</h3>
                            <div class="space-y-3">
                                <button class="w-full bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    System Settings
                                </button>
                                <button class="w-full bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Database Backup
                                </button>
                                <button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    System Logs
                                </button>
                                <button class="w-full bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                                    Generate Reports
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity & System Logs -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Activity -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent System Activity</h3>
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    <li>
                                        <div class="relative pb-8">
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </span>
                                                </div>
                                                <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                    <div>
                                                        <p class="text-sm text-gray-500">System administrator logged in</p>
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

                    <!-- Quick Stats -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Statistics</h3>
                            <dl class="grid grid-cols-1 gap-5">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Active Sessions</dt>
                                    <dd class="text-sm text-gray-900">1</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Database Size</dt>
                                    <dd class="text-sm text-gray-900">-- MB</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Last Backup</dt>
                                    <dd class="text-sm text-gray-900">--</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">System Uptime</dt>
                                    <dd class="text-sm text-gray-900">--</dd>
                                </div>
                            </dl>
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
                            <li>
                                <div class="relative pb-8">
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-sm text-gray-500">System administrator logged in</p>
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

            <!-- Quick Stats -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Quick Statistics</h3>
                    <dl class="grid grid-cols-1 gap-5">
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Active Sessions</dt>
                            <dd class="text-sm text-gray-900">1</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Database Size</dt>
                            <dd class="text-sm text-gray-900">-- MB</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Last Backup</dt>
                            <dd class="text-sm text-gray-900">--</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">System Uptime</dt>
                            <dd class="text-sm text-gray-900">--</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-8">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
            <p class="text-sm text-gray-500 text-center">
                &copy; 2025 Lab Management System. All rights reserved.
            </p>
        </div>
    </footer>
</body>
</html>