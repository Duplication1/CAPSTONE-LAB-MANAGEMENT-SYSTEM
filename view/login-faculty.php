<?php
/**
 * Faculty/Staff Login Page - Lab Management System
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
    <title>Faculty/Staff Login - Lab Management System</title>
    <link href="../css/output.css" rel="stylesheet">
    <style>
        .hidden {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        
        <div class="max-w-md w-full space-y-8"> 


        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <div class="h-450px w-450px rounded-full overflow-hidden shadow-md flex items-center justify-center bg-white border border-gray-200">
                <img src="imgs/QCU Logo.png" alt="QCU Logo" class="h-16 w-16 object-contain">
            </div>
        </div>

        <!-- Login Form -->
        <div class="bg-white rounded-lg shadow-2xl p-8">
        <!-- Title -->
        <h1 class="text-2xl font-bold text-center text-gray-900">Welcome</h1>
            <p class="text-gray-600 text-center mb-8">Please Log in to Continue</p>

                <!-- Error Messages -->
                <?php if ($loginResult && !$loginResult['success']): ?>
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

                <!-- Login Form -->
                <form id="staffLoginForm" method="POST" action="" class="space-y-6">
                    <input type="hidden" name="login_type" value="staff">
                    
                <!-- Employee Number -->
                <div class="mb-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-900">
                            Employee number
                        </label>

                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required 
                            autocomplete="username"
                            value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                            class="w-full h-12 px-4 py-3 bg-[#F2F4F8] border border-[#C1C7CD] text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter employee number"
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-900">
                        Password
                    </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            autocomplete="current-password"
                            class="w-full h-12 px-4 py-3 bg-[#F2F4F8] border border-[#C1C7CD] text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter your password"
                            >

                        <p class="mt-1 text-xs text-gray-500"> It must be a combination of minimum 8 letters, numbers, and symbols. </p>

                </div>

                    <!-- Remember Me + Forgot -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                        <input type="checkbox" name="remember_me" class="mr-2">
                            <span class="text-sm text-gray-600">Remember me</span>
                        </label>
                    <a href="#" class="text-sm text-blue-600 hover:underline">Forgot Password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                    class="w-full py-2 rounded-md text-white font-semibold bg-blue-600 hover:bg-blue-700 transition">
                    Log In
                    </button>

                </form>
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
        // Handle form submission with AJAX for 2FA support
        function handleFormSubmission(form) {
            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
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
                submitButton.innerHTML = originalText;
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

            // Add event listener to form
            const staffForm = document.getElementById('staffLoginForm');
            if (staffForm) {
                staffForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    handleFormSubmission(this);
                });
            }

            // Focus on username input
            const usernameInput = document.getElementById('username');
            if (usernameInput) {
                usernameInput.focus();
            }
        });
    </script>
</body>
</html>