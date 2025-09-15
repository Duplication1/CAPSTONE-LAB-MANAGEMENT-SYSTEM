<?php
/**
 * Debug Script for Login Issues
 */

require_once 'model/database.php';

// Test database connection
try {
    $db = new Database();
    $connection = $db->getConnection();
    echo "<h2>✅ Database connection successful!</h2>";
} catch (Exception $e) {
    echo "<h2>❌ Database connection failed: " . $e->getMessage() . "</h2>";
    exit;
}

// Test tables exist
$tables = ['professors', 'it_staff', 'administrators', 'students'];
echo "<h3>Table Check:</h3>";
foreach ($tables as $table) {
    try {
        $query = "SELECT COUNT(*) as count FROM $table";
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        echo "✅ Table '$table' exists with {$result['count']} records<br>";
    } catch (Exception $e) {
        echo "❌ Table '$table' error: " . $e->getMessage() . "<br>";
    }
}

// Show sample users (without passwords)
echo "<h3>Sample Users:</h3>";
foreach (['professors', 'it_staff', 'administrators'] as $table) {
    try {
        $query = "SELECT username, status FROM $table LIMIT 3";
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        echo "<h4>$table:</h4>";
        if ($users) {
            foreach ($users as $user) {
                echo "- Username: {$user['username']}, Status: {$user['status']}<br>";
            }
        } else {
            echo "No users found in $table<br>";
        }
    } catch (Exception $e) {
        echo "Error reading $table: " . $e->getMessage() . "<br>";
    }
}

// Test authentication function
echo "<h3>Test Authentication:</h3>";
echo "<form method='POST'>";
echo "Username: <input type='text' name='test_username' value='" . ($_POST['test_username'] ?? '') . "'><br>";
echo "Password: <input type='password' name='test_password'><br>";
echo "<input type='submit' value='Test Login'>";
echo "</form>";

if (isset($_POST['test_username']) && isset($_POST['test_password'])) {
    $userAuth = new UserAuth();
    $result = $userAuth->authenticateAcrossAllTables($_POST['test_username'], $_POST['test_password']);
    
    if ($result) {
        echo "<p>✅ Authentication successful!</p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    } else {
        echo "<p>❌ Authentication failed!</p>";
    }
}
?>