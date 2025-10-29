<?php
/**
 * Admin Location Assignments API
 * Handles character assignment to locations
 */

header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/../includes/connect.php';

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
            assignCharactersToLocation();
            break;
        case 'DELETE':
            removeCharacterFromLocation();
            break;
        case 'GET':
            getLocationAssignments();
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function assignCharactersToLocation() {
    global $conn;
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['location_id']) || !isset($input['assignments'])) {
        throw new Exception('Location ID and assignments required');
    }
    
    $location_id = intval($input['location_id']);
    $assignments = $input['assignments'];
    
    if (!is_array($assignments) || empty($assignments)) {
        throw new Exception('Assignments array is required');
    }
    
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        // First, remove existing assignments for this location
        $delete_query = "DELETE FROM character_location_assignments WHERE location_id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, "i", $location_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Insert new assignments
        $insert_query = "INSERT INTO character_location_assignments (location_id, character_id, assignment_type, notes) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        
        foreach ($assignments as $assignment) {
            $character_id = intval($assignment['character_id']);
            $assignment_type = $assignment['assignment_type'] ?? 'Resident';
            $notes = $assignment['notes'] ?? null;
            
            mysqli_stmt_bind_param($stmt, "iiss", $location_id, $character_id, $assignment_type, $notes);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Assignment failed: " . mysqli_stmt_error($stmt));
            }
        }
        
        mysqli_stmt_close($stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode([
            'success' => true,
            'message' => 'Characters assigned to location successfully',
            'assigned_count' => count($assignments)
        ]);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}

function removeCharacterFromLocation() {
    global $conn;
    
    $location_id = isset($_GET['location_id']) ? intval($_GET['location_id']) : 0;
    $character_id = isset($_GET['character_id']) ? intval($_GET['character_id']) : 0;
    
    if (!$location_id || !$character_id) {
        throw new Exception('Location ID and Character ID required');
    }
    
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        $delete_query = "DELETE FROM character_location_assignments WHERE location_id = ? AND character_id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, "ii", $location_id, $character_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Delete failed: " . mysqli_stmt_error($stmt));
        }
        
        if (mysqli_stmt_affected_rows($stmt) === 0) {
            throw new Exception("Assignment not found");
        }
        
        mysqli_stmt_close($stmt);
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo json_encode([
            'success' => true,
            'message' => 'Character removed from location successfully'
        ]);
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        throw $e;
    }
}

function getLocationAssignments() {
    global $conn;
    
    $location_id = isset($_GET['location_id']) ? intval($_GET['location_id']) : 0;
    
    if (!$location_id) {
        echo json_encode(['success' => false, 'error' => 'Location ID required']);
        return;
    }
    
    try {
        // First check if the table exists
        $table_check = "SHOW TABLES LIKE 'character_location_assignments'";
        $result = mysqli_query($conn, $table_check);
        
        if (mysqli_num_rows($result) == 0) {
            // Table doesn't exist, create it
            $create_table = "CREATE TABLE IF NOT EXISTS character_location_assignments (
                character_id INT NOT NULL,
                location_id INT NOT NULL,
                assignment_type VARCHAR(100) DEFAULT 'Resident',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (character_id, location_id),
                FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
                FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            if (!mysqli_query($conn, $create_table)) {
                throw new Exception("Failed to create table: " . mysqli_error($conn));
            }
        }
        
        $query = "SELECT 
            cla.character_id, cla.assignment_type, cla.notes, cla.created_at,
            c.character_name, c.clan, c.player_name
            FROM character_location_assignments cla
            JOIN characters c ON cla.character_id = c.id
            WHERE cla.location_id = ?
            ORDER BY c.character_name";
        
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $location_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $assignments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $assignments[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        
        echo json_encode([
            'success' => true,
            'assignments' => $assignments,
            'count' => count($assignments)
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

mysqli_close($conn);
?>
