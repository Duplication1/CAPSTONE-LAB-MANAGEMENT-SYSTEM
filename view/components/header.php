<?php
/**
 * Header Component - Lab Management System
 * Reusable header for all dashboard pages
 */

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user information from session
$user_name = $_SESSION['full_name'] ?? 'User';
$user_type = $_SESSION['user_type'] ?? 'user';
$user_id = $_SESSION['username'] ?? $_SESSION['user_id'] ?? '';

// Define user type labels
$user_type_labels = [
    'student' => 'Student Portal',
    'professor' => 'Professor Dashboard',
    'itstaff' => 'IT Staff Dashboard',
    'admin' => 'Administrator Panel'
];

$current_label = $user_type_labels[$user_type] ?? 'Dashboard';
?>

<header class="bg-white shadow-md w-full h-[50px]">
     <div class="max-w-[1440px] mx-auto flex items-center justify-between h-full px-10">

    <div class="w-full px-4">
        <div class="flex justify-between items-center h-16">

            <!-- Left section with menu toggle and title -->
            <div class="flex items-center space-x-4">
                <!-- Mobile menu toggle button -->
                <button 
                    id="sidebarToggle" 
                    class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-colors duration-200"
                    aria-label="Toggle sidebar"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Logo and title -->
                <div class="flex items-center space-x-2">
                    <div class="flex items-center space-x-2">
                        <span class="font-poppins font-bold text-[31px] leading-[31px] tracking-normal text-[#504848] inline-flex">
                        one
                        <span class="text-red-600">Q</span>
                        <span class="text-yellow-500">C</span>
                        <span class="text-blue-600">U</span>
                        </span>
                    </div>
                </div>
                
            </div>

            <!-- Right section with user info and logout -->
            <div class="flex items-center space-x-6">

            <!-- Notification Bell -->
            <button class="relative text-blue-600 hover:text-blue-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" 
                    viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="absolute top-0 right-0 inline-flex w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- User Avatar -->
            <div class="h-10 w-10 rounded-full bg-gray-800 text-white flex items-center justify-center font-semibold">
            JC
            </div>

                <!-- Logout button -->
                <a href="../index.php?action=logout" 
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500"
                >
                    <span class="hidden sm:inline">Logout</span>
                    <svg class="h-4 w-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</header>