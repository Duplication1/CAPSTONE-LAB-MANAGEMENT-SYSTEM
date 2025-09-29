<?php
/**
 * Test Database Integration for Lab Room and Equipment Selection
 */

require_once 'model/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h2>Testing Database Integration</h2>";
    
    // Test 1: Check laboratory_rooms table
    echo "<h3>1. Laboratory Rooms:</h3>";
    $stmt = $conn->prepare("SELECT id, room_number, room_name, capacity, status FROM laboratory_rooms WHERE status = 'active' ORDER BY room_number");
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($rooms)) {
        echo "<p style='color: red;'>No laboratory rooms found! Please add some rooms to the database.</p>";
        echo "<p>Sample SQL to add rooms:</p>";
        echo "<pre>";
        echo "INSERT INTO laboratory_rooms (room_number, room_name, capacity, status) VALUES\n";
        echo "('LAB-001', 'Computer Laboratory 1', 30, 'active'),\n";
        echo "('LAB-002', 'Computer Laboratory 2', 25, 'active'),\n";
        echo "('LAB-003', 'Physics Laboratory', 20, 'active');";
        echo "</pre>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Room Number</th><th>Room Name</th><th>Capacity</th><th>Status</th></tr>";
        foreach ($rooms as $room) {
            echo "<tr>";
            echo "<td>{$room['id']}</td>";
            echo "<td>{$room['room_number']}</td>";
            echo "<td>{$room['room_name']}</td>";
            echo "<td>{$room['capacity']}</td>";
            echo "<td>{$room['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test 2: Check equipment table
    echo "<h3>2. Equipment (if any rooms exist):</h3>";
    if (!empty($rooms)) {
        $firstRoomId = $rooms[0]['id'];
        echo "<p>Checking equipment for room ID: {$firstRoomId} ({$rooms[0]['room_name']})</p>";
        
        $stmt = $conn->prepare("
            SELECT equipment_code, equipment_name, equipment_type, status 
            FROM equipment 
            WHERE laboratory_room_id = ? AND status = 'active'
            ORDER BY equipment_code
        ");
        $stmt->execute([$firstRoomId]);
        $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($equipment)) {
            echo "<p style='color: orange;'>No equipment found for this room. This is optional but recommended.</p>";
            echo "<p>Sample SQL to add equipment:</p>";
            echo "<pre>";
            echo "INSERT INTO equipment (equipment_code, equipment_name, equipment_type, laboratory_room_id, status) VALUES\n";
            echo "('PC-001', 'Computer Workstation 1', 'computer', {$firstRoomId}, 'active'),\n";
            echo "('PC-002', 'Computer Workstation 2', 'computer', {$firstRoomId}, 'active'),\n";
            echo "('PC-003', 'Computer Workstation 3', 'computer', {$firstRoomId}, 'active');";
            echo "</pre>";
        } else {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>Equipment Code</th><th>Equipment Name</th><th>Type</th><th>Status</th></tr>";
            foreach ($equipment as $item) {
                echo "<tr>";
                echo "<td>{$item['equipment_code']}</td>";
                echo "<td>{$item['equipment_name']}</td>";
                echo "<td>{$item['equipment_type']}</td>";
                echo "<td>{$item['status']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // Test 3: Test API endpoint
    echo "<h3>3. API Endpoint Test:</h3>";
    if (!empty($rooms)) {
        $testRoomId = $rooms[0]['id'];
        echo "<p>Testing equipment API for room ID: {$testRoomId}</p>";
        echo "<p><a href='controller/login_controller.php?action=get_equipment&lab_room_id={$testRoomId}' target='_blank'>Test Equipment API</a></p>";
    }
    
    echo "<h3>4. Integration Status:</h3>";
    if (!empty($rooms)) {
        echo "<p style='color: green;'>✅ Database integration is ready!</p>";
        echo "<p>You can now test the student login form with lab room selection.</p>";
        echo "<p><a href='view/index.php'>Test Student Login Form</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Please add laboratory rooms to the database first.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database connection and table structure.</p>";
}
?>