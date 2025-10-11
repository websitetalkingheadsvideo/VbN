<?php
/**
 * Admin API - Remove Equipment from Character
 * POST /api_admin_remove_equipment.php
 */

session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// TODO: Add proper admin check
// if (!$_SESSION['is_admin']) { ... }

require_once 'includes/connect.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['equipment_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing equipment ID']);
    exit;
}

$equipment_id = (int)$data['equipment_id'];

try {
    $delete_sql = "DELETE FROM character_equipment WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param('i', $equipment_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Item removed successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Item not found'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>

