<?php
// Test simple database insert
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Test database connection
include 'includes/connect.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get JSON data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

// Try a simple insert with minimal data
try {
    $user_id = 1;
    $character_name = $data['character_name'] ?? 'Test Character';
    
    $sql = "INSERT INTO characters (user_id, character_name) VALUES (?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $character_name);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to execute: ' . mysqli_error($conn));
    }
    
    $character_id = mysqli_insert_id($conn);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Character saved successfully!',
        'character_id' => $character_id,
        'character_name' => $character_name
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
