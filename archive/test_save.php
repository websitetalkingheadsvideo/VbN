<?php
// Simple test script to isolate the save issue
header('Content-Type: application/json');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Global error handler
set_error_handler(function($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => "PHP Error: $message in $file on line $line"
        ]);
        exit();
    }
});

try {
    error_log('Test save script started');
    
    // Check if user is logged in
    session_start();
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit();
    }
    
    error_log('User is logged in: ' . $_SESSION['user_id']);
    
    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit();
    }
    
    error_log('Request method is POST');
    
    // Get JSON data
    $input = file_get_contents('php://input');
    error_log('Raw input: ' . substr($input, 0, 200) . '...');
    
    $data = json_decode($input, true);
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        exit();
    }
    
    error_log('JSON decoded successfully');
    
    // Test database connection
    include 'includes/connect.php';
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }
    
    error_log('Database connected successfully');
    
    // Simple test insert
    $user_id = $_SESSION['user_id'];
    $character_name = $data['character_name'] ?? 'Test Character';
    
    $sql = "INSERT INTO characters (user_id, character_name, player_name, chronicle) VALUES (?, ?, 'Test Player', 'Test Chronicle')";
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . mysqli_error($conn)]);
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, 'is', $user_id, $character_name);
    
    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => false, 'message' => 'Failed to execute: ' . mysqli_stmt_error($stmt)]);
        exit();
    }
    
    $character_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    error_log('Character created with ID: ' . $character_id);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Test character saved successfully!',
        'character_id' => $character_id
    ]);
    
} catch (Exception $e) {
    error_log('Exception: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Exception: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
