<?php
/**
 * Boons API Endpoint
 * Handles CRUD operations for boons (favors between characters)
 */

session_start();
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../includes/connect.php';

// Check if boons table exists and get its structure
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'boons'");
if (!$table_check || mysqli_num_rows($table_check) == 0) {
    echo json_encode([
        'success' => false, 
        'error' => 'Boons table does not exist. Please run database/create_boons_table.sql to create it.'
    ]);
    exit();
}

// Check table structure to determine which schema we're using
$columns_check = mysqli_query($conn, "SHOW COLUMNS FROM boons");
$has_boon_id = false;
$has_giver_name = false;
$has_creditor_id = false;
$table_schema = 'new'; // 'new' or 'old'

if ($columns_check) {
    while ($col = mysqli_fetch_assoc($columns_check)) {
        if ($col['Field'] === 'boon_id') $has_boon_id = true;
        if ($col['Field'] === 'giver_name') $has_giver_name = true;
        if ($col['Field'] === 'creditor_id') $has_creditor_id = true;
    }
    
    if ($has_creditor_id && !$has_giver_name) {
        $table_schema = 'old';
    }
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            handleGet($conn, $action);
            break;
        case 'POST':
            handlePost($conn);
            break;
        case 'PUT':
            handlePut($conn);
            break;
        case 'DELETE':
            handleDelete($conn);
            break;
        default:
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function handleGet($conn, $action) {
    global $table_schema;
    
    if ($action === 'list' || $action === '') {
        // Get all boons with optional status filter
        $status = $_GET['status'] ?? '';
        
        if ($table_schema === 'old') {
            // Use old schema with joins
            $query = "SELECT b.id as boon_id, 
                             creditor.character_name as giver_name,
                             debtor.character_name as receiver_name,
                             b.creditor_id as giver_ref,
                             b.debtor_id as receiver_ref,
                             b.boon_type, 
                             b.status, 
                             b.description, 
                             b.notes as related_event,
                             b.created_date as date_created,
                             b.updated_at as date_updated
                      FROM boons b
                      LEFT JOIN characters creditor ON b.creditor_id = creditor.id
                      LEFT JOIN characters debtor ON b.debtor_id = debtor.id";
        } else {
            // Use new schema
            $query = "SELECT boon_id, giver_name, receiver_name, giver_ref, receiver_ref, 
                             boon_type, status, description, related_event, 
                             date_created, date_updated 
                      FROM boons";
        }
        
        $params = [];
        $types = '';
        
        if ($status && $status !== 'all') {
            // Map status values between schemas
            $status_map = [
                'Owed' => 'active',
                'Called' => 'active',
                'Paid' => 'fulfilled',
                'Broken' => 'cancelled'
            ];
            $db_status = ($table_schema === 'old' && isset($status_map[$status])) ? $status_map[$status] : $status;
            
            if ($table_schema === 'old') {
                $valid_statuses = ['active', 'fulfilled', 'cancelled', 'disputed'];
            } else {
                $valid_statuses = ['Owed', 'Called', 'Paid', 'Broken'];
            }
            
            if (in_array($db_status, $valid_statuses)) {
                $query .= " WHERE status = ?";
                $params[] = $db_status;
                $types = 's';
            }
        }
        
        $query .= " ORDER BY " . ($table_schema === 'old' ? 'created_date' : 'date_created') . " DESC";
        
        $result = db_select($conn, $query, $types, $params);
        
        if ($result === false) {
            echo json_encode(['success' => false, 'error' => 'Failed to fetch boons: ' . mysqli_error($conn)]);
            return;
        }
        
        $boons = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Normalize status for display
            if ($table_schema === 'old') {
                $status_map = [
                    'active' => 'Owed',
                    'fulfilled' => 'Paid',
                    'cancelled' => 'Broken',
                    'disputed' => 'Broken'
                ];
                $row['status'] = $status_map[$row['status']] ?? $row['status'];
                
                // Normalize boon_type capitalization
                $row['boon_type'] = ucfirst(strtolower($row['boon_type']));
            }
            $boons[] = $row;
        }
        mysqli_free_result($result);
        
        echo json_encode(['success' => true, 'data' => $boons]);
    } elseif ($action === 'get' && isset($_GET['id'])) {
        // Get single boon
        $boon_id = (int)$_GET['id'];
        
        if ($table_schema === 'old') {
            $query = "SELECT b.id as boon_id, 
                             creditor.character_name as giver_name,
                             debtor.character_name as receiver_name,
                             b.creditor_id as giver_ref,
                             b.debtor_id as receiver_ref,
                             b.boon_type, 
                             b.status, 
                             b.description, 
                             b.notes as related_event,
                             b.created_date as date_created,
                             b.updated_at as date_updated
                      FROM boons b
                      LEFT JOIN characters creditor ON b.creditor_id = creditor.id
                      LEFT JOIN characters debtor ON b.debtor_id = debtor.id
                      WHERE b.id = ?";
        } else {
            $query = "SELECT boon_id, giver_name, receiver_name, giver_ref, receiver_ref, 
                             boon_type, status, description, related_event, 
                             date_created, date_updated 
                      FROM boons 
                      WHERE boon_id = ?";
        }
        
        $boon = db_fetch_one($conn, $query, 'i', [$boon_id]);
        
        if ($boon) {
            // Normalize for display
            if ($table_schema === 'old') {
                $status_map = [
                    'active' => 'Owed',
                    'fulfilled' => 'Paid',
                    'cancelled' => 'Broken',
                    'disputed' => 'Broken'
                ];
                $boon['status'] = $status_map[$boon['status']] ?? $boon['status'];
                $boon['boon_type'] = ucfirst(strtolower($boon['boon_type']));
            }
            echo json_encode(['success' => true, 'data' => $boon]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Boon not found']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
}

function handlePost($conn) {
    global $table_schema;
    
    // Create new boon
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    $giver_name = trim($data['giver_name'] ?? '');
    $receiver_name = trim($data['receiver_name'] ?? '');
    $boon_type = $data['boon_type'] ?? 'Trivial';
    $description = trim($data['description'] ?? '');
    $related_event = trim($data['related_event'] ?? '');
    
    // Validation
    if (empty($giver_name) || empty($receiver_name)) {
        echo json_encode(['success' => false, 'error' => 'Giver and Receiver names are required']);
        return;
    }
    
    if ($table_schema === 'old') {
        // Old schema: need to look up character IDs and use lowercase boon types
        $boon_type_lower = strtolower($boon_type);
        if (!in_array($boon_type_lower, ['trivial', 'minor', 'major', 'life'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid boon type']);
            return;
        }
        
        // Get character IDs
        $giver_query = "SELECT id FROM characters WHERE character_name = ? LIMIT 1";
        $giver_result = db_fetch_one($conn, $giver_query, 's', [$giver_name]);
        
        $receiver_query = "SELECT id FROM characters WHERE character_name = ? LIMIT 1";
        $receiver_result = db_fetch_one($conn, $receiver_query, 's', [$receiver_name]);
        
        if (!$giver_result || !$receiver_result) {
            echo json_encode([
                'success' => false, 
                'error' => 'Could not find character(s). Giver: ' . ($giver_result ? 'found' : 'not found') . ', Receiver: ' . ($receiver_result ? 'found' : 'not found')
            ]);
            return;
        }
        
        $giver_id = $giver_result['id'];
        $receiver_id = $receiver_result['id'];
        $created_by = $_SESSION['user_id'] ?? 1;
        
        $query = "INSERT INTO boons (creditor_id, debtor_id, boon_type, description, status, notes, created_by) 
                  VALUES (?, ?, ?, ?, 'active', ?, ?)";
        
        $result = db_execute($conn, $query, 'iisssi', [
            $giver_id,
            $receiver_id,
            $boon_type_lower,
            $description ?: 'No description provided',
            $related_event,
            $created_by
        ]);
    } else {
        // New schema
        if (!in_array($boon_type, ['Trivial', 'Minor', 'Major', 'Life'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid boon type']);
            return;
        }
        
        $query = "INSERT INTO boons (giver_name, receiver_name, boon_type, description, related_event, status) 
                  VALUES (?, ?, ?, ?, ?, 'Owed')";
        
        $result = db_execute($conn, $query, 'sssss', [
            $giver_name,
            $receiver_name,
            $boon_type,
            $description,
            $related_event
        ]);
    }
    
    if ($result !== false) {
        echo json_encode([
            'success' => true, 
            'message' => 'Boon created successfully',
            'boon_id' => $result
        ]);
    } else {
        $error_msg = mysqli_error($conn);
        error_log("Boon creation error: " . $error_msg);
        echo json_encode([
            'success' => false, 
            'error' => 'Failed to create boon: ' . ($error_msg ?: 'Database error')
        ]);
    }
}

function handlePut($conn) {
    global $table_schema;
    
    // Update boon
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        parse_str(file_get_contents('php://input'), $data);
    }
    
    $boon_id = (int)($data['boon_id'] ?? 0);
    $id_field = $table_schema === 'old' ? 'id' : 'boon_id';
    
    if ($boon_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid boon ID']);
        return;
    }
    
    // Check if this is a status-only update (Mark Paid/Broken)
    if (isset($data['status']) && !isset($data['giver_name'])) {
        $status = $data['status'];
        
        if ($table_schema === 'old') {
            // Map status values
            $status_map = [
                'Owed' => 'active',
                'Called' => 'active',
                'Paid' => 'fulfilled',
                'Broken' => 'cancelled'
            ];
            $db_status = $status_map[$status] ?? 'active';
        } else {
            $db_status = $status;
            if (!in_array($db_status, ['Owed', 'Called', 'Paid', 'Broken'])) {
                echo json_encode(['success' => false, 'error' => 'Invalid status']);
                return;
            }
        }
        
        $query = "UPDATE boons SET status = ? WHERE $id_field = ?";
        $result = db_execute($conn, $query, 'si', [$db_status, $boon_id]);
        
        if ($result !== false) {
            echo json_encode(['success' => true, 'message' => 'Boon status updated']);
        } else {
            $error_msg = mysqli_error($conn);
            echo json_encode(['success' => false, 'error' => 'Failed to update boon status: ' . ($error_msg ?: 'Database error')]);
        }
        return;
    }
    
    // Full update
    $giver_name = trim($data['giver_name'] ?? '');
    $receiver_name = trim($data['receiver_name'] ?? '');
    $boon_type = $data['boon_type'] ?? 'Trivial';
    $description = trim($data['description'] ?? '');
    $related_event = trim($data['related_event'] ?? '');
    $status = $data['status'] ?? 'Owed';
    
    if (empty($giver_name) || empty($receiver_name)) {
        echo json_encode(['success' => false, 'error' => 'Giver and Receiver names are required']);
        return;
    }
    
    if ($table_schema === 'old') {
        // Old schema: need character IDs
        $boon_type_lower = strtolower($boon_type);
        if (!in_array($boon_type_lower, ['trivial', 'minor', 'major', 'life'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid boon type']);
            return;
        }
        
        $status_map = [
            'Owed' => 'active',
            'Called' => 'active',
            'Paid' => 'fulfilled',
            'Broken' => 'cancelled'
        ];
        $db_status = $status_map[$status] ?? 'active';
        
        // Get character IDs
        $giver_query = "SELECT id FROM characters WHERE character_name = ? LIMIT 1";
        $giver_result = db_fetch_one($conn, $giver_query, 's', [$giver_name]);
        $receiver_query = "SELECT id FROM characters WHERE character_name = ? LIMIT 1";
        $receiver_result = db_fetch_one($conn, $receiver_query, 's', [$receiver_name]);
        
        if (!$giver_result || !$receiver_result) {
            echo json_encode(['success' => false, 'error' => 'Could not find character(s)']);
            return;
        }
        
        $query = "UPDATE boons 
                  SET creditor_id = ?, debtor_id = ?, boon_type = ?, 
                      description = ?, status = ?, notes = ?
                  WHERE id = ?";
        
        $result = db_execute($conn, $query, 'iissssi', [
            $giver_result['id'],
            $receiver_result['id'],
            $boon_type_lower,
            $description ?: 'No description provided',
            $db_status,
            $related_event,
            $boon_id
        ]);
    } else {
        // New schema
        if (!in_array($boon_type, ['Trivial', 'Minor', 'Major', 'Life'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid boon type']);
            return;
        }
        
        if (!in_array($status, ['Owed', 'Called', 'Paid', 'Broken'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid status']);
            return;
        }
        
        $query = "UPDATE boons 
                  SET giver_name = ?, receiver_name = ?, boon_type = ?, 
                      description = ?, related_event = ?, status = ?
                  WHERE boon_id = ?";
        
        $result = db_execute($conn, $query, 'ssssssi', [
            $giver_name,
            $receiver_name,
            $boon_type,
            $description,
            $related_event,
            $status,
            $boon_id
        ]);
    }
    
    if ($result !== false) {
        echo json_encode(['success' => true, 'message' => 'Boon updated successfully']);
    } else {
        $error_msg = mysqli_error($conn);
        error_log("Boon update error: " . $error_msg);
        echo json_encode([
            'success' => false, 
            'error' => 'Failed to update boon: ' . ($error_msg ?: 'Database error')
        ]);
    }
}

function handleDelete($conn) {
    global $table_schema;
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        parse_str(file_get_contents('php://input'), $data);
    }
    
    $boon_id = (int)($data['boon_id'] ?? $_GET['id'] ?? 0);
    $id_field = $table_schema === 'old' ? 'id' : 'boon_id';
    
    if ($boon_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid boon ID']);
        return;
    }
    
    $query = "DELETE FROM boons WHERE $id_field = ?";
    $result = db_execute($conn, $query, 'i', [$boon_id]);
    
    if ($result !== false) {
        echo json_encode(['success' => true, 'message' => 'Boon deleted successfully']);
    } else {
        $error_msg = mysqli_error($conn);
        echo json_encode(['success' => false, 'error' => 'Failed to delete boon: ' . ($error_msg ?: 'Database error')]);
    }
}

