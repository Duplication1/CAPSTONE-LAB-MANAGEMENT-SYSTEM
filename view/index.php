<?php
/**
 * Login Page - Lab Management System
 */

require_once '../controller/login_controller.php';

$controller = new LoginController();

// Check if user is already logged in
$controller->checkExistingLogin();

// Handle login form submission
$loginResult = $controller->handleLogin();

// Handle redirect
if ($loginResult && $loginResult['success']) {
    header('Location: ' . $loginResult['redirect']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lab Management System</title>
    <link href="../css/output.css" rel="stylesheet">
    <style>
        .hidden {
            display: none !important;
        }
        .tab-content {
            display: block;
        }
        .tab-content.hidden {
            display: none !important;
        }
    </style>
</head>
<body class="login-container">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-12 w-12 bg-white rounded-full flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-extrabold text-white">
                    Lab Management System
                </h2>
                <p class="mt-2 text-sm text-gray-200">
                    Sign in to your account
                </p>
            </div>

            <!-- Tab-based Login Interface -->
            <div class="bg-white rounded-lg shadow-2xl p-8">
                <!-- Tab Navigation -->
                <div class="flex mb-6 bg-gray-100 rounded-lg p-1">
                    <button 
                        id="studentTab"
                        onclick="switchTab('student')" 
                        class="flex-1 py-2 px-4 text-sm font-medium text-center rounded-md transition-all duration-200 bg-blue-600 text-white shadow-sm"
                    >
                        Student Login
                    </button>
                    <button 
                        id="staffTab"
                        onclick="switchTab('staff')" 
                        class="flex-1 py-2 px-4 text-sm font-medium text-center rounded-md transition-all duration-200 text-gray-600 hover:text-gray-800"
                    >
                        Staff/Faculty Login
                    </button>
                </div>

                <!-- Student Login Content -->
                <div id="studentContent" class="tab-content">
                    <?php if ($loginResult && !$loginResult['success'] && isset($_POST['student_number'])): ?>
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium"><?php echo htmlspecialchars($loginResult['message']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-6">
                        <input type="hidden" name="login_type" value="student">
                        
                        <!-- Student Number -->
                        <div>
                            <label for="student_number" class="block text-sm font-medium text-gray-700 mb-2">
                                Student Number
                            </label>
                            <input 
                                type="text" 
                                id="student_number" 
                                name="student_number" 
                                required 
                                value="<?php echo htmlspecialchars($_POST['student_number'] ?? ''); ?>"
                                class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="Enter your student number (e.g., 2024-001)"
                            >
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button 
                                type="submit" 
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Sign In as Student
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Staff/Faculty Login Content -->
                <div id="staffContent" class="tab-content hidden">
                    <?php if ($loginResult && !$loginResult['success'] && !isset($_POST['student_number'])): ?>
                        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium"><?php echo htmlspecialchars($loginResult['message']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-6">
                        <input type="hidden" name="login_type" value="staff">
                        
                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                Username
                            </label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                required 
                                value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                placeholder="Enter your username"
                            >
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                Password
                            </label>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                required 
                                class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                placeholder="Enter your password"
                            >
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button 
                                type="submit" 
                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013 3v1"></path>
                                </svg>
                                Sign In
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center">
                <p class="text-sm text-gray-200">
                    &copy; 2025 Lab Management System. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabType) {
            console.log('Switching to tab:', tabType);
            
            // Get tab buttons
            const studentTab = document.getElementById('studentTab');
            const staffTab = document.getElementById('staffTab');
            
            // Get content divs
            const studentContent = document.getElementById('studentContent');
            const staffContent = document.getElementById('staffContent');
            
            console.log('Elements found:', {
                studentTab: !!studentTab,
                staffTab: !!staffTab,
                studentContent: !!studentContent,
                staffContent: !!staffContent
            });
            
            if (tabType === 'student') {
                // Activate student tab
                studentTab.className = 'flex-1 py-2 px-4 text-sm font-medium text-center rounded-md transition-all duration-200 bg-blue-600 text-white shadow-sm';
                staffTab.className = 'flex-1 py-2 px-4 text-sm font-medium text-center rounded-md transition-all duration-200 text-gray-600 hover:text-gray-800';
                
                // Show student content, hide staff content
                studentContent.classList.remove('hidden');
                staffContent.classList.add('hidden');
                
                console.log('Student content classes:', studentContent.className);
                console.log('Staff content classes:', staffContent.className);
                
                // Focus on student number input
                setTimeout(() => {
                    const studentInput = document.getElementById('student_number');
                    if (studentInput) studentInput.focus();
                }, 100);
            } else {
                // Activate staff tab
                staffTab.className = 'flex-1 py-2 px-4 text-sm font-medium text-center rounded-md transition-all duration-200 bg-green-600 text-white shadow-sm';
                studentTab.className = 'flex-1 py-2 px-4 text-sm font-medium text-center rounded-md transition-all duration-200 text-gray-600 hover:text-gray-800';
                
                // Show staff content, hide student content
                staffContent.classList.remove('hidden');
                studentContent.classList.add('hidden');
                
                console.log('Student content classes:', studentContent.className);
                console.log('Staff content classes:', staffContent.className);
                
                // Focus on username input
                setTimeout(() => {
                    const usernameInput = document.getElementById('username');
                    if (usernameInput) usernameInput.focus();
                }, 100);
            }
        }

        // Initialize the correct tab based on previous submission
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing tabs...');
            <?php if (isset($_POST['login_type']) && $_POST['login_type'] === 'staff'): ?>
                switchTab('staff');
            <?php else: ?>
                switchTab('student');
            <?php endif; ?>
        });
    </script>
</body>
</html>