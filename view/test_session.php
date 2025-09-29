<?php
/**
 * Test Session Data - Lab Management System
 * This page displays current session data to verify lab room and PC number storage
 */

session_start();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Test - Lab Management System</title>
    <link href="../css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Session Data Test</h1>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Current Session Data -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Current Session</h2>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <?php if (empty($_SESSION)): ?>
                            <p class="text-gray-600">No session data found. Please log in first.</p>
                        <?php else: ?>
                            <div class="space-y-2">
                                <?php foreach ($_SESSION as $key => $value): ?>
                                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                        <span class="font-medium text-gray-700"><?php echo htmlspecialchars($key); ?>:</span>
                                        <span class="text-gray-900 font-mono text-sm">
                                            <?php 
                                                if (is_array($value)) {
                                                    echo json_encode($value, JSON_PRETTY_PRINT);
                                                } else {
                                                    echo htmlspecialchars($value); 
                                                }
                                            ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Key Session Fields -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Key Fields Status</h2>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-3">
                            <!-- Student ID -->
                            <div class="flex items-center space-x-3">
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                                    <span class="text-gray-700">Student ID: <strong><?php echo htmlspecialchars($_SESSION['user_id']); ?></strong></span>
                                <?php else: ?>
                                    <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                                    <span class="text-gray-500">Student ID: Not set</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Lab Room -->
                            <div class="flex items-center space-x-3">
                                <?php if (isset($_SESSION['lab_room'])): ?>
                                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                                    <span class="text-gray-700">Lab Room: <strong><?php echo htmlspecialchars($_SESSION['lab_room']); ?></strong></span>
                                <?php else: ?>
                                    <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                                    <span class="text-gray-500">Lab Room: Not set</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- PC Number -->
                            <div class="flex items-center space-x-3">
                                <?php if (isset($_SESSION['pc_number'])): ?>
                                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                                    <span class="text-gray-700">PC Number: <strong><?php echo htmlspecialchars($_SESSION['pc_number']); ?></strong></span>
                                <?php else: ?>
                                    <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                                    <span class="text-gray-500">PC Number: Not set</span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- User Type -->
                            <div class="flex items-center space-x-3">
                                <?php if (isset($_SESSION['user_type'])): ?>
                                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                                    <span class="text-gray-700">User Type: <strong><?php echo htmlspecialchars($_SESSION['user_type']); ?></strong></span>
                                <?php else: ?>
                                    <span class="w-3 h-3 bg-red-500 rounded-full"></span>
                                    <span class="text-gray-500">User Type: Not set</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-8 flex space-x-4">
                <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Student Login
                </a>
                <a href="login-faculty.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Faculty Login
                </a>
                <?php if (!empty($_SESSION)): ?>
                <a href="../controller/login_controller.php?action=logout" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Logout
                </a>
                <?php endif; ?>
                <button onclick="location.reload()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Refresh
                </button>
            </div>
        </div>
    </div>
</body>
</html>