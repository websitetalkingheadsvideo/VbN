<?php
/**
 * API - Create Location
 * POST /api_create_location.php
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

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Validate required fields
$required = ['name', 'type', 'status', 'owner_type', 'access_control'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

try {
    // Start transaction for atomic location creation
    mysqli_begin_transaction($conn);
    
    try {
        // Prepare INSERT statement
        $sql = "INSERT INTO locations (
            name, type, summary, description, notes, status, status_notes,
            district, address, latitude, longitude,
            owner_type, owner_notes, faction, access_control, access_notes,
            security_level, security_locks, security_alarms, security_guards,
            security_hidden_entrance, security_sunlight_protected, security_warding_rituals,
            security_cameras, security_reinforced, security_notes,
            utility_blood_storage, utility_computers, utility_library, utility_medical,
            utility_workshop, utility_hidden_caches, utility_armory, utility_communications,
            utility_notes,
            social_features, capacity, prestige_level,
            has_supernatural, node_points, node_type, ritual_space,
            magical_protection, cursed_blessed,
            parent_location_id, relationship_type, relationship_notes,
            image
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?,
            ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?,
            ?, ?, ?,
            ?
        )";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $conn->error);
        }
    
    // Extract variables (bind_param needs actual variables, not array values)
    $name = $data['name'];
    $type = $data['type'];
    $summary = $data['summary'] ?? '';
    $description = $data['description'] ?? '';
    $notes = $data['notes'] ?? '';
    $status = $data['status'];
    $status_notes = $data['status_notes'] ?? '';
    $district = $data['district'] ?? '';
    $address = $data['address'] ?? '';
    $latitude = $data['latitude'] ?? 0;
    $longitude = $data['longitude'] ?? 0;
    $owner_type = $data['owner_type'];
    $owner_notes = $data['owner_notes'] ?? '';
    $faction = $data['faction'] ?? '';
    $access_control = $data['access_control'];
    $access_notes = $data['access_notes'] ?? '';
    $security_level = $data['security_level'] ?? 3;
    $security_locks = $data['security_locks'] ?? 0;
    $security_alarms = $data['security_alarms'] ?? 0;
    $security_guards = $data['security_guards'] ?? 0;
    $security_hidden_entrance = $data['security_hidden_entrance'] ?? 0;
    $security_sunlight_protected = $data['security_sunlight_protected'] ?? 0;
    $security_warding_rituals = $data['security_warding_rituals'] ?? 0;
    $security_cameras = $data['security_cameras'] ?? 0;
    $security_reinforced = $data['security_reinforced'] ?? 0;
    $security_notes = $data['security_notes'] ?? '';
    $utility_blood_storage = $data['utility_blood_storage'] ?? 0;
    $utility_computers = $data['utility_computers'] ?? 0;
    $utility_library = $data['utility_library'] ?? 0;
    $utility_medical = $data['utility_medical'] ?? 0;
    $utility_workshop = $data['utility_workshop'] ?? 0;
    $utility_hidden_caches = $data['utility_hidden_caches'] ?? 0;
    $utility_armory = $data['utility_armory'] ?? 0;
    $utility_communications = $data['utility_communications'] ?? 0;
    $utility_notes = $data['utility_notes'] ?? '';
    $social_features = $data['social_features'] ?? '';
    $capacity = $data['capacity'] ?? 0;
    $prestige_level = $data['prestige_level'] ?? 0;
    $has_supernatural = $data['has_supernatural'] ?? 0;
    $node_points = $data['node_points'] ?? 0;
    $node_type = $data['node_type'] ?? '';
    $ritual_space = $data['ritual_space'] ?? '';
    $magical_protection = $data['magical_protection'] ?? '';
    $cursed_blessed = $data['cursed_blessed'] ?? '';
    $parent_location_id = !empty($data['parent_location_id']) ? (int)$data['parent_location_id'] : null;
    $relationship_type = $data['relationship_type'] ?? '';
    $relationship_notes = $data['relationship_notes'] ?? '';
    $image = $data['image'] ?? '';
    
    // Bind parameters (48 parameters total)
    $stmt->bind_param(
        'sssssssssddsssssiiiiiiiiisiiiiiiiissiiiissssisss',
        $name, $type, $summary, $description, $notes, $status, $status_notes,
        $district, $address, $latitude, $longitude,
        $owner_type, $owner_notes, $faction, $access_control, $access_notes,
        $security_level, $security_locks, $security_alarms, $security_guards,
        $security_hidden_entrance, $security_sunlight_protected, $security_warding_rituals,
        $security_cameras, $security_reinforced, $security_notes,
        $utility_blood_storage, $utility_computers, $utility_library, $utility_medical,
        $utility_workshop, $utility_hidden_caches, $utility_armory, $utility_communications,
        $utility_notes, $social_features, $capacity, $prestige_level,
        $has_supernatural, $node_points, $node_type, $ritual_space,
        $magical_protection, $cursed_blessed,
        $parent_location_id, $relationship_type, $relationship_notes,
        $image
    );
    
        if (!$stmt->execute()) {
            throw new Exception('Failed to create location: ' . $stmt->error);
        }
        
        $location_id = $conn->insert_id;
        $stmt->close();
        
        // Commit transaction if location creation succeeds
        mysqli_commit($conn);
        
        echo json_encode([
            'success' => true,
            'message' => 'Location created successfully!',
            'location_id' => $location_id
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on any error
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

