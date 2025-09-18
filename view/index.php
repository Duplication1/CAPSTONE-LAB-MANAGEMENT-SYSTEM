<?php
/**
 * Login Page with SMS 2FA Support - Lab Management System
 */

require_once '../controller/login_controller.php';

$controller = new LoginController();
$loginResult = null;

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $loginResult = $controller->handleLogin();
    header('Content-Type: application/json');
    echo json_encode($loginResult);
    exit();
}

// Handle regular form submission (fallback)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    $loginResult = $controller->handleLogin();
    
    // If successful and doesn't require 2FA, redirect immediately
    if ($loginResult && $loginResult['success'] && !isset($loginResult['requires_2fa'])) {
        header('Location: ' . $loginResult['redirect']);
        exit();
    }
    
    // If requires 2FA, we'll handle it in the frontend
}

// Check if user is already logged in (for page loads without form submission)
$controller->checkExistingLogin();
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

                    <form id="studentLoginForm" method="POST" action="" class="space-y-6">
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

                    <form id="staffLoginForm" method="POST" action="" class="space-y-6">
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

        // Handle form submissions with AJAX for 2FA support
        function handleFormSubmission(form, isStudentForm = true) {
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Signing in...
            `;

            fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.requires_2fa) {
                        // Store 2FA state and redirect to verification page
                        sessionStorage.setItem('2fa_active', 'true');
                        showAlert(data.message, 'success');
                        
                        setTimeout(() => {
                            window.location.href = 'verify_2fa.php?email=' + encodeURIComponent(data.email_masked);
                        }, 1500);
                    } else {
                        // Direct login success
                        showAlert(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    }
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Login error:', error);
                showAlert('Network error. Please try again.', 'error');
            })
            .finally(() => {
                // Restore button state
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            });
        }

        function showAlert(message, type) {
            // Remove any existing alerts
            const existingAlert = document.querySelector('.alert-message');
            if (existingAlert) {
                existingAlert.remove();
            }

            // Create alert element
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert-message fixed top-4 right-4 max-w-sm p-4 rounded-lg shadow-lg z-50 transition-all transform translate-x-full';
            
            if (type === 'success') {
                alertDiv.classList.add('bg-green-500', 'text-white');
            } else {
                alertDiv.classList.add('bg-red-500', 'text-white');
            }
            
            alertDiv.innerHTML = `
                <div class="flex items-center">
                    <span class="flex-1">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Animate in
            setTimeout(() => {
                alertDiv.classList.remove('translate-x-full');
            }, 100);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.classList.add('translate-x-full');
                    setTimeout(() => {
                        if (alertDiv.parentElement) {
                            alertDiv.remove();
                        }
                    }, 300);
                }
            }, 5000);
        }

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing tabs...');
            
            // Initialize tab based on previous submission or default to student
            <?php if (isset($_POST['login_type']) && $_POST['login_type'] === 'staff'): ?>
                switchTab('staff');
            <?php else: ?>
                switchTab('student');
            <?php endif; ?>

            // Handle PHP login result if present
            <?php if ($loginResult): ?>
                <?php if ($loginResult['success'] && isset($loginResult['requires_2fa'])): ?>
                    sessionStorage.setItem('2fa_active', 'true');
                    showAlert('<?php echo addslashes($loginResult['message']); ?>', 'success');
                    setTimeout(() => {
                        window.location.href = 'verify_2fa.php?email=<?php echo urlencode($loginResult['email_masked'] ?? '****@****.com'); ?>';
                    }, 1500);
                <?php elseif (!$loginResult['success']): ?>
                    showAlert('<?php echo addslashes($loginResult['message']); ?>', 'error');
                <?php endif; ?>
            <?php endif; ?>

            // Add event listeners to forms
            const studentForm = document.getElementById('studentLoginForm');
            const staffForm = document.getElementById('staffLoginForm');

            if (studentForm) {
                studentForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    handleFormSubmission(this, true);
                });
            }

            if (staffForm) {
                staffForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    handleFormSubmission(this, false);
                });
            }
        });
    </script>
</body>
</html>