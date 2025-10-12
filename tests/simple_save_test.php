<?php
// Simple save test - minimal version
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

// Debug: log what we received
error_log("Simple save - Input: " . $input);
error_log("Simple save - Decoded: " . print_r($data, true));

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data. Input: ' . substr($input, 0, 100)]);
    exit();
}

try {
    // Simple insert test
    $character_name = $data['character_name'] ?? 'Test Character';
    $user_id = 1;
    
    $sql = "INSERT INTO characters (user_id, character_name, player_name, chronicle, nature, demeanor, concept, clan, generation, sire, pc, biography, equipment, total_xp, spent_xp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, 'isssssssissssii', 
        $user_id,
        $character_name,
        'Test Player',
        'Valley by Night',
        'Unknown',
        'Unknown',
        'Unknown',
        'Unknown',
        13,
        null,
        0,
        null,
        null,
        30,
        0
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to create character: ' . mysqli_error($conn));
    }
    
    $character_id = mysqli_insert_id($conn);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Character saved successfully!',
        'character_id' => $character_id
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
