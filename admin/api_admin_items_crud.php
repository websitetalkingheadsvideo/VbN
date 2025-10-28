<?php
/**
 * Admin Items CRUD API
 * Handle POST/PUT/DELETE operations for items table
 */

session_start();
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// TODO: Add proper admin check
// if (!$_SESSION['is_admin']) { ... }

require_once '../includes/connect.php';

// Handle assignment check request
if (isset($_GET['check_assignments']) && is_numeric($_GET['check_assignments'])) {
    $item_id = (int)$_GET['check_assignments'];
    
    try {
        $check_sql = "SELECT COUNT(*) as count FROM character_equipment WHERE item_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('i', $item_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        echo json_encode([
            'success' => true,
            'assignment_count' => $count
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'assignment_count' => 0,
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            handleCreateItem();
            break;
        case 'PUT':
            handleUpdateItem();
            break;
        case 'DELETE':
            handleDeleteItem();
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

function handleCreateItem() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required_fields = ['name', 'type', 'category', 'description', 'rarity', 'price'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit;
        }
    }
    
    // Prepare data
    $name = trim($data['name']);
    $type = trim($data['type']);
    $category = trim($data['category']);
    $damage = isset($data['damage']) ? trim($data['damage']) : null;
    $range = isset($data['range']) ? trim($data['range']) : null;
    $requirements = isset($data['requirements']) ? json_encode($data['requirements']) : null;
    $description = trim($data['description']);
    $rarity = trim($data['rarity']);
    $price = (int)$data['price'];
    $image = isset($data['image']) ? trim($data['image']) : null;
    $notes = isset($data['notes']) ? trim($data['notes']) : null;
    
    // Insert new item
    $sql = "INSERT INTO items (name, type, category, damage, `range`, requirements, description, rarity, price, image, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssssiss', $name, $type, $category, $damage, $range, $requirements, $description, $rarity, $price, $image, $notes);
    
    if ($stmt->execute()) {
        $item_id = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Item created successfully',
            'item_id' => $item_id
        ]);
    } else {
        throw new Exception('Failed to create item: ' . $stmt->error);
    }
}

function handleUpdateItem() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing or invalid item ID']);
        exit;
    }
    
    $item_id = (int)$data['id'];
    
    // Validate required fields
    $required_fields = ['name', 'type', 'category', 'description', 'rarity', 'price'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
            exit;
        }
    }
    
    // Prepare data
    $name = trim($data['name']);
    $type = trim($data['type']);
    $category = trim($data['category']);
    $damage = isset($data['damage']) ? trim($data['damage']) : null;
    $range = isset($data['range']) ? trim($data['range']) : null;
    $requirements = isset($data['requirements']) ? json_encode($data['requirements']) : null;
    $description = trim($data['description']);
    $rarity = trim($data['rarity']);
    $price = (int)$data['price'];
    $image = isset($data['image']) ? trim($data['image']) : null;
    $notes = isset($data['notes']) ? trim($data['notes']) : null;
    
    // Update item
    $sql = "UPDATE items SET name=?, type=?, category=?, damage=?, `range`=?, requirements=?, 
            description=?, rarity=?, price=?, image=?, notes=? WHERE id=?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssssssssissi', $name, $type, $category, $damage, $range, $requirements, $description, $rarity, $price, $image, $notes, $item_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Item updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Item not found or no changes made'
            ]);
        }
    } else {
        throw new Exception('Failed to update item: ' . $stmt->error);
    }
}

function handleDeleteItem() {
    global $conn;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing or invalid item ID']);
        exit;
    }
    
    $item_id = (int)$data['id'];
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Check if item is assigned to any characters
        $check_sql = "SELECT COUNT(*) as count FROM character_equipment WHERE item_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('i', $item_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        if ($count > 0) {
            mysqli_rollback($conn);
            echo json_encode([
                'success' => false,
                'message' => "Cannot delete item: it is assigned to $count character(s). Remove assignments first."
            ]);
            exit;
        }
        
        // Delete the item
        $delete_sql = "DELETE FROM items WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param('i', $item_id);
        
        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                mysqli_commit($conn);
                echo json_encode([
                    'success' => true,
                    'message' => 'Item deleted successfully'
                ]);
            } else {
                mysqli_rollback($conn);
                echo json_encode([
                    'success' => false,
                    'message' => 'Item not found'
                ]);
            }
        } else {
            mysqli_rollback($conn);
            throw new Exception('Failed to delete item: ' . $delete_stmt->error);
        }
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}

$conn->close();
?>
