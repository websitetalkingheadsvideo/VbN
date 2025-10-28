<?php
/**
 * Admin API - Add Equipment to Character
 * POST /api_admin_add_equipment.php
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

if (!isset($data['character_id']) || !isset($data['item_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$character_id = (int)$data['character_id'];
$item_id = (int)$data['item_id'];
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

try {
    // Start transaction for atomic equipment operation
    mysqli_begin_transaction($conn);
    
    try {
        // Check if item already exists for character
        $check_sql = "SELECT id, quantity FROM character_equipment 
                      WHERE character_id = ? AND item_id = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param('ii', $character_id, $item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Item exists, update quantity
            $row = $result->fetch_assoc();
            $new_quantity = $row['quantity'] + $quantity;
            
            $update_sql = "UPDATE character_equipment SET quantity = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('ii', $new_quantity, $row['id']);
            $update_stmt->execute();
            
            mysqli_commit($conn);
            
            echo json_encode([
                'success' => true,
                'message' => 'Item quantity updated'
            ]);
        } else {
            // Add new item
            $insert_sql = "INSERT INTO character_equipment (character_id, item_id, quantity, equipped) 
                          VALUES (?, ?, ?, 0)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param('iii', $character_id, $item_id, $quantity);
            $insert_stmt->execute();
            
            mysqli_commit($conn);
            
            echo json_encode([
                'success' => true,
                'message' => 'Item added successfully'
            ]);
        }
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
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

