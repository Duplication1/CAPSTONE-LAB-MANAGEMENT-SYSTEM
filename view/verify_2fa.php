<?php
/**
 * Email 2FA Verification Page - Lab Management System
 */

// Check if 2FA session exists
session_start();
if (!isset($_SESSION['2fa_user_id']) || !isset($_SESSION['2fa_expires']) || $_SESSION['2fa_expires'] < time()) {
    header('Location: index.php');
    exit();
}

$email = $_GET['email'] ?? '****@****.com';
$timeLeft = $_SESSION['2fa_expires'] - time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Lab Management System</title>
    <link href="../css/output.css" rel="stylesheet">
</head>
<body class="login-container">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-12 w-12 bg-white rounded-full flex items-center justify-center mb-4">
                    <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-extrabold text-white">
                    Email Verification
                </h2>
                <p class="mt-2 text-sm text-gray-200">
                    Enter the verification code sent to <?php echo htmlspecialchars($email); ?>
                </p>
            </div>

            <!-- Verification Form -->
            <div class="bg-white rounded-lg shadow-2xl p-8">
                <form id="verifyForm" class="space-y-6">
                    <input type="hidden" name="action" value="verify">
                    
                    <div>
                        <label for="verification_code" class="block text-sm font-medium text-gray-700">
                            Verification Code
                        </label>
                        <input id="verification_code" name="verification_code" type="text" 
                               maxlength="6" pattern="[0-9]{6}" required 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 text-center text-2xl font-mono"
                               placeholder="000000" autocomplete="off">
                    </div>

                    <div>
                        <button type="submit" id="submitBtn"
                                class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Verify Code
                        </button>
                    </div>
                </form>

                <!-- Resend and Timer -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 mb-3">
                        Code expires in: <span id="timer" class="font-mono font-bold text-red-600"><?php echo gmdate('i:s', $timeLeft); ?></span>
                    </p>
                    
                    <form id="resendForm" style="display: inline;">
                        <input type="hidden" name="action" value="resend">
                        <button type="submit" id="resendBtn" 
                                class="text-indigo-600 hover:text-indigo-500 text-sm font-medium">
                            Resend Code
                        </button>
                    </form>
                    
                    <div class="mt-2">
                        <a href="index.php" class="text-gray-500 hover:text-gray-700 text-sm">
                            Back to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Timer countdown
        let timeLeft = <?php echo $timeLeft; ?>;
        const timerElement = document.getElementById('timer');
        const resendBtn = document.getElementById('resendBtn');
        
        function updateTimer() {
            if (timeLeft <= 0) {
                timerElement.textContent = '00:00';
                timerElement.className = 'font-mono font-bold text-red-600';
                resendBtn.textContent = 'Code Expired - Resend';
                return;
            }
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            
            if (timeLeft < 60) {
                timerElement.className = 'font-mono font-bold text-red-600';
            }
            
            timeLeft--;
        }
        
        // Update timer every second
        updateTimer();
        setInterval(updateTimer, 1000);
        
        // Auto-focus on code input
        document.getElementById('verification_code').focus();
        
        // Handle form submission with AJAX
        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.textContent;
            
            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.textContent = 'Verifying...';
            
            fetch('../controller/login_controller.php?action=verify_2fa', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    showMessage(data.message, 'error');
                    document.getElementById('verification_code').value = '';
                    document.getElementById('verification_code').focus();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
        
        // Handle resend form submission with AJAX
        document.getElementById('resendForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('action', 'resend');
            
            fetch('../controller/login_controller.php?action=resend_2fa', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    timeLeft = data.expires_in;
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('Failed to resend code. Please try again.', 'error');
            });
        });
        
        // Auto-submit when 6 digits are entered
        document.getElementById('verification_code').addEventListener('input', function(e) {
            if (e.target.value.length === 6) {
                document.getElementById('verifyForm').dispatchEvent(new Event('submit'));
            }
        });
        
        // Show message function
        function showMessage(message, type) {
            // Remove existing alerts
            const existingAlerts = document.querySelectorAll('.alert-message');
            existingAlerts.forEach(alert => alert.remove());
            
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert-message mb-4 p-4 rounded-md ${type === 'success' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'}`;
            alertDiv.innerHTML = `<p class="text-sm ${type === 'success' ? 'text-green-800' : 'text-red-800'}">${message}</p>`;
            
            const form = document.getElementById('verifyForm');
            form.parentNode.insertBefore(alertDiv, form);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>