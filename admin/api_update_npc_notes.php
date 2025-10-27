<?php
/**
 * Update NPC Notes API
 * Saves agentNotes and actingNotes for a character
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../includes/connect.php';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

$character_id = isset($input['character_id']) ? intval($input['character_id']) : 0;
$agentNotes = isset($input['agentNotes']) ? $input['agentNotes'] : null;
$actingNotes = isset($input['actingNotes']) ? $input['actingNotes'] : null;

if ($character_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid character ID']);
    exit();
}

// Update the notes
$update_query = "UPDATE characters 
                 SET agentNotes = ?, actingNotes = ? 
                 WHERE id = ?";

$stmt = mysqli_prepare($conn, $update_query);
mysqli_stmt_bind_param($stmt, "ssi", $agentNotes, $actingNotes, $character_id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        'success' => true,
        'message' => 'Notes updated successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

