<?php
/**
 * Upload Character Image Endpoint
 * Handles secure upload and storage of character portrait images
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

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

// Get character ID
$character_id = isset($_POST['character_id']) ? intval($_POST['character_id']) : 0;
if (!$character_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Character ID required']);
    exit;
}

try {
    // Verify character ownership and editability
    $verify_query = "SELECT id, pc, player_name, status FROM characters WHERE id = ? AND user_id = ?";
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
    
    // Check if character is editable (not NPC or finalized)
    if ($character['pc'] == 0 || $character['player_name'] === 'ST/NPC') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Cannot upload images for NPCs']);
        exit;
    }
    
    if ($character['status'] === 'finalized') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Cannot upload images for finalized characters']);
        exit;
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $file_type = $_FILES['image']['type'];
    
    if (!in_array($file_type, $allowed_types)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, and GIF allowed.']);
        exit;
    }
    
    // Validate file size (2MB max)
    $max_size = 2 * 1024 * 1024; // 2MB
    if ($_FILES['image']['size'] > $max_size) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'File size exceeds 2MB limit']);
        exit;
    }
    
    // Generate unique filename
    $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $sanitized_name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $_FILES['image']['name']);
    $unique_filename = $character_id . '_' . time() . '_' . substr(md5($sanitized_name), 0, 8) . '.' . $file_extension;
    
    // Full path for storage
    $upload_dir = __DIR__ . '/uploads/characters/';
    $file_path = $upload_dir . $unique_filename;
    
    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
        exit;
    }
    
    // Update database with relative path
    $db_path = '/uploads/characters/' . $unique_filename;
    $update_query = "UPDATE characters SET character_image = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $db_path, $character_id);
    
    if (!$stmt->execute()) {
        // Delete uploaded file if database update fails
        unlink($file_path);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update database']);
        exit;
    }
    
    // Success
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded successfully',
        'image_path' => $db_path
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $e->getMessage()]);
}

$conn->close();
?>

