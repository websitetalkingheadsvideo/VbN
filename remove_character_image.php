<?php
/**
 * Remove Character Image Endpoint
 * Deletes character portrait image from storage and database
 */

session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Include database connection
require_once __DIR__ . '/includes/connect.php';

// Get character ID
$character_id = isset($_POST['character_id']) ? intval($_POST['character_id']) : 0;
if (!$character_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Character ID required']);
    exit;
}

try {
    // Verify character ownership and editability
    $verify_query = "SELECT id, pc, player_name, status, character_image FROM characters WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $character_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Character not found or access denied']);
        exit;
    }
    
    $character = $result->fetch_assoc();
    
    // Check if character is editable
    if ($character['pc'] == 0 || $character['player_name'] === 'ST/NPC') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Cannot modify images for NPCs']);
        exit;
    }
    
    if ($character['status'] === 'finalized') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Cannot modify images for finalized characters']);
        exit;
    }
    
    // Delete file if it exists
    if ($character['character_image']) {
        $file_path = __DIR__ . $character['character_image'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Update database - set to NULL
    $update_query = "UPDATE characters SET character_image = NULL WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $character_id);
    
    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
        exit;
    }
    
    // Success
    echo json_encode([
        'success' => true,
        'message' => 'Image removed successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Remove error: ' . $e->getMessage()]);
}

$conn->close();
?>

