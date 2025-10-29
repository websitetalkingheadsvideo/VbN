<?php
/**
 * Simple Delete Location API
 * Minimal version to test delete functionality
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // Include database connection
    require_once __DIR__ . '/../includes/connect.php';
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Check if user is admin
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Admin access required']);
        exit();
    }
    
    // Get location ID
    $location_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if (!$location_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Location ID required']);
        exit();
    }
    
    // Check if location exists
    $check_query = "SELECT id, name FROM locations WHERE id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $location_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $location = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$location) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Location not found']);
        exit();
    }
    
    // Delete location
    $delete_query = "DELETE FROM locations WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    
    if (!$stmt) {
        throw new Exception('Delete prepare failed: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $location_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Delete execute failed: ' . mysqli_stmt_error($stmt));
    }
    
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);
    
    if ($affected_rows === 0) {
        throw new Exception('No rows affected');
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Location '{$location['name']}' deleted successfully"
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
