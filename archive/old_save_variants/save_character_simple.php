<?php
// Simplified save script for testing
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Database connection
include 'includes/connect.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get data from either JSON input or POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// If JSON parsing failed, try POST data
if (!$data) {
    $data = $_POST;
}

// If still no data, return error
if (!$data || empty($data)) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Insert main character record only (no complex inserts)
    $character_sql = "INSERT INTO characters (user_id, character_name, player_name, chronicle, nature, demeanor, concept, clan, generation, sire, pc, biography, equipment, total_xp, spent_xp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $character_sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }
    
    $pc_value = isset($data['pc']) && $data['pc'] ? 1 : 0;
    mysqli_stmt_bind_param($stmt, 'isssssssissssii', 
        $user_id,
        $data['character_name'],
        $data['player_name'],
        $data['chronicle'] ?? 'Valley by Night',
        $data['nature'],
        $data['demeanor'],
        $data['concept'],
        $data['clan'],
        $data['generation'],
        $data['sire'],
        $pc_value,
        $data['biography'],
        $data['equipment'],
        $data['total_xp'],
        $data['spent_xp']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $character_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Character saved successfully!',
            'character_id' => $character_id
        ]);
    } else {
        throw new Exception('Character insert failed: ' . mysqli_error($conn));
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error saving character: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
