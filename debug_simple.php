<?php
// Simple debug script to isolate the issue
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: application/json');

// Check request method
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Test database connection
include 'includes/connect.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get JSON data
$input = file_get_contents('php://input');

if (empty($input)) {
    echo json_encode(['success' => false, 'message' => 'No input data received']);
    exit();
}

$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data. Error: ' . json_last_error_msg()]);
    exit();
}

// Try to save the main character record
try {
    $user_id = 1; // Default test user
    
    // Insert main character record
    $character_sql = "INSERT INTO characters (
        user_id, character_name, player_name, chronicle, nature, demeanor, 
        concept, clan, generation, sire, pc, biography, equipment, 
        total_xp, spent_xp
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $character_sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }
    
    $pc_value = isset($data['pc']) && $data['pc'] ? 1 : 0;
    mysqli_stmt_bind_param($stmt, 'isssssssissssii', 
        $user_id,
        $data['character_name'] ?? 'Unnamed Character',
        $data['player_name'] ?? 'Unknown Player',
        $data['chronicle'] ?? 'Valley by Night',
        $data['nature'] ?? 'Unknown',
        $data['demeanor'] ?? 'Unknown',
        $data['concept'] ?? 'Unknown',
        $data['clan'] ?? 'Unknown',
        $data['generation'] ?? 13,
        $data['sire'] ?? null,
        $pc_value,
        $data['biography'] ?? null,
        $data['equipment'] ?? null,
        $data['total_xp'] ?? 30,
        $data['spent_xp'] ?? 0
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to create character: ' . mysqli_error($conn));
    }
    
    $character_id = mysqli_insert_id($conn);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Character saved successfully!',
        'character_id' => $character_id,
        'character_name' => $data['character_name'] ?? 'Unnamed Character'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error saving character: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
