<?php
/**
 * Database Setup Script for Health Monitoring
 * Run this to create the health monitoring tables
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Health Monitoring Database Setup</h2>\n";

require_once 'model/database.php';

try {
    $db = new Database();
    $connection = $db->getConnection();
    
    echo "<p>✓ Database connection established</p>\n";
    
    // Read the SQL file
    $sql = file_get_contents('health_monitoring_tables.sql');
    
    // Split SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    echo "<h3>Executing SQL Statements:</h3>\n";
    
    foreach ($statements as $statement) {
        if (trim($statement)) {
            try {
                $connection->exec($statement);
                
                // Extract table name from CREATE TABLE statements
                if (preg_match('/CREATE TABLE.*?`([^`]+)`/', $statement, $matches)) {
                    echo "<p>✓ Created/Updated table: {$matches[1]}</p>\n";
                } elseif (preg_match('/INSERT.*?INTO.*?`([^`]+)`/', $statement, $matches)) {
                    echo "<p>✓ Inserted data into: {$matches[1]}</p>\n";
                } else {
                    echo "<p>✓ Executed statement</p>\n";
                }
                
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'already exists') !== false || 
                    strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    echo "<p>⚠ Skipped (already exists): " . htmlspecialchars($e->getMessage()) . "</p>\n";
                } else {
                    echo "<p style='color:red'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
                }
            }
        }
    }
    
    // Verify tables were created
    echo "<h3>Verifying Tables:</h3>\n";
    $tables = ['computers', 'health_data', 'health_alerts', 'health_thresholds'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $connection->query("DESCRIBE $table");
            $columns = $stmt->fetchAll();
            echo "<p>✓ Table '$table' exists with " . count($columns) . " columns</p>\n";
        } catch (Exception $e) {
            echo "<p style='color:red'>✗ Table '$table' not found: " . htmlspecialchars($e->getMessage()) . "</p>\n";
        }
    }
    
    // Check sample data
    echo "<h3>Sample Data:</h3>\n";
    try {
        $stmt = $connection->query("SELECT COUNT(*) as count FROM computers");
        $result = $stmt->fetch();
        echo "<p>✓ Computers table has {$result['count']} records</p>\n";
        
        $stmt = $connection->query("SELECT COUNT(*) as count FROM health_thresholds");
        $result = $stmt->fetch();
        echo "<p>✓ Health thresholds table has {$result['count']} records</p>\n";
        
    } catch (Exception $e) {
        echo "<p style='color:red'>✗ Error checking data: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
    
    echo "<h3 style='color:green'>✓ Health Monitoring Database Setup Complete!</h3>\n";
    
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Database setup failed: " . htmlspecialchars($e->getMessage()) . "</p>\n";
}
?>