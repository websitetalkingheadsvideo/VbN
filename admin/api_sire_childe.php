<?php
/**
 * API for Sire/Childe Relationship Management
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Check if user is admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../includes/connect.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'update':
    case 'update_sire':
        handleUpdateRelationship();
        break;
    case 'tree':
        handleGetFamilyTree();
        break;
    default:
        handleUpdateRelationship();
        break;
}

function handleUpdateRelationship() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        return;
    }
    
    $character_id = intval($input['character_id'] ?? 0);
    $sire = trim($input['sire'] ?? '');
    
    if (!$character_id) {
        echo json_encode(['success' => false, 'message' => 'Character ID required']);
        return;
    }
    
    // Validate that sire exists if provided
    if (!empty($sire)) {
        $sire_check = "SELECT id FROM characters WHERE character_name = ?";
        $stmt = mysqli_prepare($conn, $sire_check);
        mysqli_stmt_bind_param($stmt, "s", $sire);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 0) {
            echo json_encode(['success' => false, 'message' => 'Sire not found in database']);
            return;
        }
    }
    
    // Update the character's sire
    $update_query = "UPDATE characters SET sire = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    
    if (empty($sire)) {
        $sire = null;
        mysqli_stmt_bind_param($stmt, "si", $sire, $character_id);
    } else {
        mysqli_stmt_bind_param($stmt, "si", $sire, $character_id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Relationship updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}

function handleGetFamilyTree() {
    global $conn;
    
    // Get all characters with their relationships
    $query = "SELECT c.id, c.character_name, c.clan, c.generation, c.sire,
                     (SELECT GROUP_CONCAT(c2.character_name SEPARATOR ',') 
                      FROM characters c2 
                      WHERE c2.sire = c.character_name) as childer
              FROM characters c 
              ORDER BY c.generation DESC, c.character_name";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
        return;
    }
    
    $tree = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $row['childer'] = $row['childer'] ? explode(',', $row['childer']) : [];
        $tree[] = $row;
    }
    
    echo json_encode(['success' => true, 'tree' => $tree]);
}

mysqli_close($conn);
?>
