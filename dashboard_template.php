<?php
/**
 * Dashboard Template - Lab Management System
 * This is a template file that can be copied to each user type directory
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

$user = [
    'id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'],
    'user_type' => $_SESSION['user_type'],
    'full_name' => $_SESSION['full_name'],
    'email' => $_SESSION['email']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($user['user_type']); ?> Dashboard - Lab Management System</title>
    <link href="../../css/output.css" rel="stylesheet">
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-800">Lab Management System</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Welcome, <?php echo htmlspecialchars($user['full_name']); ?></span>
                    <span class="text-sm text-gray-500">(<?php echo ucfirst($user['user_type']); ?>)</span>
                    <a href="../../controller/login_controller.php?action=logout" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 px-4">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                <?php echo ucfirst($user['user_type']); ?> Dashboard
            </h2>
            <p class="text-gray-600">Welcome to your dashboard, <?php echo htmlspecialchars($user['full_name']); ?>!</p>
        </div>

        <!-- Dashboard Content -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Profile Information</h3>
                <div class="space-y-2">
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>User Type:</strong> <?php echo ucfirst($user['user_type']); ?></p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Quick Actions</h3>
                <div class="space-y-2">
                    <button class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg text-sm">
                        View Schedule
                    </button>
                    <button class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg text-sm">
                        Lab Reservations
                    </button>
                    <button class="w-full bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded-lg text-sm">
                        Reports
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Recent Activity</h3>
                <div class="space-y-2 text-sm text-gray-600">
                    <p>• Logged in successfully</p>
                    <p>• Dashboard accessed</p>
                    <p>• System status: Active</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>