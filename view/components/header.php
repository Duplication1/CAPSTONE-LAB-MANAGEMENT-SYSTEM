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

<header class="bg-white shadow-lg relative z-30 w-full">
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
                <div class="flex items-center space-x-3">
                    <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">Lab Management System</h1>
                        <p class="text-xs text-gray-600 hidden sm:block"><?php echo htmlspecialchars($current_label); ?></p>
                    </div>
                </div>
            </div>

            <!-- Right section with user info and logout -->
            <div class="flex items-center space-x-4">
                <!-- User information -->
                <div class="hidden md:flex items-center space-x-3">
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($user_name); ?></p>
                        <p class="text-xs text-gray-500 capitalize"><?php echo htmlspecialchars($user_type); ?></p>
                    </div>
                    <?php if (!empty($user_id)): ?>
                        <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($user_id); ?></span>
                    <?php endif; ?>
                </div>

                <!-- User avatar placeholder -->
                <div class="h-8 w-8 bg-gray-300 rounded-full flex items-center justify-center">
                    <svg class="h-5 w-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                    </svg>
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