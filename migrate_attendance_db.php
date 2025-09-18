<?php
/**
 * Apply database migration to fix attendance logging
 * This script removes the unique constraint and verifies the changes
 */

require_once 'model/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "Starting database migration for attendance logging...\n\n";
    
    // Check if the unique constraint exists
    $stmt = $conn->prepare("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'attendance_logs' 
            AND CONSTRAINT_TYPE = 'UNIQUE'
            AND CONSTRAINT_NAME = 'unique_daily_attendance'
    ");
    $stmt->execute();
    $uniqueConstraint = $stmt->fetchColumn();
    
    if ($uniqueConstraint) {
        echo "✓ Found unique constraint: $uniqueConstraint\n";
        
        // Drop the unique constraint
        echo "Removing unique constraint...\n";
        $conn->exec("ALTER TABLE attendance_logs DROP INDEX unique_daily_attendance");
        echo "✓ Unique constraint removed successfully\n";
    } else {
        echo "✓ Unique constraint not found (already removed or never existed)\n";
    }
    
    // Add a new non-unique index for performance
    echo "Adding performance index...\n";
    try {
        $conn->exec("ALTER TABLE attendance_logs ADD INDEX idx_student_attendance_date (student_id, attendance_date, login_time)");
        echo "✓ Performance index added successfully\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
            echo "✓ Performance index already exists\n";
        } else {
            throw $e;
        }
    }
    
    // Verify foreign key relationship
    $stmt = $conn->prepare("
        SELECT 
            CONSTRAINT_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'attendance_logs' 
            AND COLUMN_NAME = 'student_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    $stmt->execute();
    $foreignKey = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($foreignKey) {
        echo "✓ Foreign key relationship verified:\n";
        echo "   student_id -> {$foreignKey['REFERENCED_TABLE_NAME']}.{$foreignKey['REFERENCED_COLUMN_NAME']}\n";
    } else {
        echo "⚠ Foreign key relationship not found\n";
    }
    
    // Show current table structure
    echo "\nCurrent table structure:\n";
    $stmt = $conn->prepare("DESCRIBE attendance_logs");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        $key = $column['Key'] ? " ({$column['Key']})" : '';
        echo "  {$column['Field']}: {$column['Type']}{$key}\n";
    }
    
    // Show current indexes
    echo "\nCurrent indexes:\n";
    $stmt = $conn->prepare("SHOW INDEX FROM attendance_logs");
    $stmt->execute();
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($indexes as $index) {
        $unique = $index['Non_unique'] == 0 ? ' (UNIQUE)' : '';
        echo "  {$index['Key_name']}: {$index['Column_name']}{$unique}\n";
    }
    
    echo "\n✅ Database migration completed successfully!\n";
    echo "\nNow multiple login entries per day will be allowed.\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>