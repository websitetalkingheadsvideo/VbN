<?php
/**
 * Test Items API - Debug Version
 * This will show us what's wrong
 */

// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Test 1: Check if connect.php exists
    if (!file_exists('includes/connect.php')) {
        throw new Exception('includes/connect.php not found');
    }
    
    // Test 2: Include database connection
    require_once 'includes/connect.php';
    
    // Test 3: Check connection
    if (!isset($conn) || !$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Test 4: Check if items table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'items'");
    if ($tableCheck->num_rows === 0) {
        throw new Exception('items table does not exist - run create_items_tables.sql first');
    }
    
    // Test 5: Check if table has data
    $countResult = $conn->query("SELECT COUNT(*) as count FROM items");
    $countRow = $countResult->fetch_assoc();
    
    if ($countRow['count'] == 0) {
        throw new Exception('items table is empty - run import_items.php first');
    }
    
    // Test 6: Try to fetch items
    $query = "SELECT * FROM items ORDER BY category, name LIMIT 5";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        // Decode requirements JSON
        $row['requirements'] = json_decode($row['requirements'], true);
        $items[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'API is working!',
        'total_items' => $countRow['count'],
        'sample_items' => $items
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}

if (isset($conn)) {
    $conn->close();
}
?>

