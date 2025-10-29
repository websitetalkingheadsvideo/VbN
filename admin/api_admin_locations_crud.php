<?php
/**
 * Admin Locations CRUD API
 * Handles POST (create), PUT (update), DELETE operations for locations
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/../includes/connect.php';

// Debug: Check if connection exists
if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Check if user is admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Admin access required']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            createLocation();
            break;
        case 'PUT':
            updateLocation();
            break;
        case 'DELETE':
            deleteLocation();
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function createLocation() {
    global $conn;
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    // Validate required fields
    $required_fields = ['name', 'type', 'status', 'owner_type', 'access_control'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Required field missing: $field");
        }
    }
    
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Insert location
        $query = "INSERT INTO locations (
            name, type, status, district, owner_type, faction, access_control,
            security_level, description, summary, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "sssssssisss",
            $input['name'],
            $input['type'],
            $input['status'],
            $input['district'] ?? null,
            $input['owner_type'],
            $input['faction'] ?? null,
            $input['access_control'],
            $input['security_level'] ?? 3,
            $input['description'] ?? null,
            $input['summary'] ?? null,
            $input['notes'] ?? null
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        $location_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode([
            'success' => true,
            'message' => 'Location created successfully',
            'location_id' => $location_id
        ]);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}

function updateLocation() {
    global $conn;
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        throw new Exception('Location ID required for update');
    }
    
    $location_id = intval($input['id']);
    
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Update location
        $query = "UPDATE locations SET 
            name = ?, type = ?, status = ?, district = ?, owner_type = ?, 
            faction = ?, access_control = ?, security_level = ?, 
            description = ?, summary = ?, notes = ?
            WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "sssssssisssi",
            $input['name'],
            $input['type'],
            $input['status'],
            $input['district'] ?? null,
            $input['owner_type'],
            $input['faction'] ?? null,
            $input['access_control'],
            $input['security_level'] ?? 3,
            $input['description'] ?? null,
            $input['summary'] ?? null,
            $input['notes'] ?? null,
            $location_id
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        if (mysqli_stmt_affected_rows($stmt) === 0) {
            throw new Exception("Location not found or no changes made");
        }
        
        mysqli_stmt_close($stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode([
            'success' => true,
            'message' => 'Location updated successfully'
        ]);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}

function deleteLocation() {
    global $conn;
    
    // Debug logging
    error_log("Delete function called with ID: " . ($_GET['id'] ?? 'none'));
    
    // Get location ID from URL parameter
    $location_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    error_log("Parsed location ID: $location_id");
    
    if (!$location_id) {
        error_log("No location ID provided");
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Location ID required for deletion']);
        return;
    }
    
    // Check if location exists first
    $check_query = "SELECT id, name FROM locations WHERE id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database prepare failed: ' . mysqli_error($conn)]);
        return;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $location_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $location = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$location) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Location not found']);
        return;
    }
    
    // Check if location_assignments table exists before checking assignments
    $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'location_assignments'");
    
    if (mysqli_num_rows($table_check) > 0) {
        // Check if location has character assignments
        $assignment_query = "SELECT COUNT(*) as assignment_count FROM location_assignments WHERE location_id = ?";
        $stmt = mysqli_prepare($conn, $assignment_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $location_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $assignment_data = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            if ($assignment_data['assignment_count'] > 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => "Cannot delete location: {$assignment_data['assignment_count']} character(s) are assigned to this location"]);
                return;
            }
        }
    }
    
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Delete location
        $delete_query = "DELETE FROM locations WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        
        if (!$stmt) {
            throw new Exception("Delete prepare failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $location_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Delete execute failed: " . mysqli_stmt_error($stmt));
        }
        
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($affected_rows === 0) {
            throw new Exception("No rows affected - location not found");
        }
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode([
            'success' => true,
            'message' => "Location '{$location['name']}' deleted successfully"
        ]);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

mysqli_close($conn);
?>
