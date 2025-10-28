<?php
/**
 * Admin API - Update Equipment (quantity, equipped status, etc.)
 * POST /api_admin_update_equipment.php
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

require_once '../includes/connect.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['equipment_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing equipment ID']);
    exit;
}

$equipment_id = (int)$data['equipment_id'];

try {
    $updates = [];
    $params = [];
    $types = '';
    
    // Build dynamic update query based on provided fields
    if (isset($data['quantity'])) {
        $updates[] = "quantity = ?";
        $params[] = (int)$data['quantity'];
        $types .= 'i';
    }
    
    if (isset($data['equipped'])) {
        $updates[] = "equipped = ?";
        $params[] = (int)$data['equipped'];
        $types .= 'i';
    }
    
    if (isset($data['custom_notes'])) {
        $updates[] = "custom_notes = ?";
        $params[] = $data['custom_notes'];
        $types .= 's';
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit;
    }
    
    // Add equipment_id to params
    $params[] = $equipment_id;
    $types .= 'i';
    
    $update_sql = "UPDATE character_equipment SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0 || $stmt->errno == 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Equipment updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No changes made or item not found'
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

