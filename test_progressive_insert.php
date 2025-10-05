<?php
// Progressive field insertion to find the problematic field
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

// Try inserting with just a few more fields first
try {
    $user_id = 1;
    
    $sql = "INSERT INTO characters (
        user_id, character_name, player_name, chronicle, nature, demeanor, concept, clan
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, 'isssssss', 
        $user_id,
        $data['character_name'] ?? 'Unnamed Character',
        $data['player_name'] ?? 'Unknown Player',
        $data['chronicle'] ?? 'Valley by Night',
        $data['nature'] ?? 'Unknown',
        $data['demeanor'] ?? 'Unknown',
        $data['concept'] ?? 'Unknown',
        $data['clan'] ?? 'Unknown'
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to execute: ' . mysqli_error($conn));
    }
    
    $character_id = mysqli_insert_id($conn);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Character saved with basic fields!',
        'character_id' => $character_id,
        'character_name' => $data['character_name'] ?? 'Unnamed Character'
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
