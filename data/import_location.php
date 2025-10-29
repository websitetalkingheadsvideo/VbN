<?php
/**
 * Location Import Script
 * Import locations from JSON files into the database
 * Usage: import_location.php?file=location-name.json
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/connect.php';

// Get the file parameter
$file = $_GET['file'] ?? '';

if (empty($file)) {
    die("❌ Error: No file specified. Usage: import_location.php?file=location-name.json");
}

// Construct file path
$file_path = __DIR__ . '/' . $file;

if (!file_exists($file_path)) {
    die("❌ Error: File '$file' not found in data directory");
}

echo "<h1>🏠 Location Import</h1>";
echo "<pre>";

try {
    // Read and parse JSON file
    $json_content = file_get_contents($file_path);
    $location_data = json_decode($json_content, true);
    
    if (!$location_data) {
        throw new Exception("Invalid JSON format in file: $file");
    }
    
    echo "📁 File: $file\n";
    echo "📋 Location: " . ($location_data['location_name'] ?? 'Unknown') . "\n\n";
    
    // Validate required fields
    $required_fields = ['location_name', 'type', 'status', 'ownership'];
    foreach ($required_fields as $field) {
        if (!isset($location_data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Check if ownership has required sub-fields
    if (!isset($location_data['ownership']['owner_type']) || !isset($location_data['ownership']['access_control'])) {
        throw new Exception("Missing required ownership fields: owner_type, access_control");
    }
    
    echo "✅ JSON validation passed\n\n";
    
    // Start transaction
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
        
        // Extract data with defaults
        $name = $location_data['location_name'];
        $type = $location_data['type'];
        $summary = $location_data['summary'] ?? '';
        $description = $location_data['description'] ?? '';
        $notes = $location_data['notes'] ?? '';
        $status = $location_data['status'];
        $status_notes = $location_data['status_notes'] ?? '';
        
        // Geography
        $district = $location_data['geography']['district'] ?? '';
        $address = $location_data['geography']['address'] ?? '';
        $latitude = $location_data['geography']['latitude'] ?? 0;
        $longitude = $location_data['geography']['longitude'] ?? 0;
        
        // Ownership
        $owner_type = $location_data['ownership']['owner_type'];
        $owner_notes = $location_data['ownership']['owner_notes'] ?? '';
        $faction = $location_data['ownership']['faction'] ?? '';
        $access_control = $location_data['ownership']['access_control'];
        $access_notes = $location_data['ownership']['access_notes'] ?? '';
        
        // Security
        $security_level = $location_data['security']['security_level'] ?? 3;
        $security_locks = $location_data['security']['security_locks'] ?? false;
        $security_alarms = $location_data['security']['security_alarms'] ?? false;
        $security_guards = $location_data['security']['security_guards'] ?? false;
        $security_hidden_entrance = $location_data['security']['security_hidden_entrance'] ?? false;
        $security_sunlight_protected = $location_data['security']['security_sunlight_protected'] ?? false;
        $security_warding_rituals = $location_data['security']['security_warding_rituals'] ?? false;
        $security_cameras = $location_data['security']['security_cameras'] ?? false;
        $security_reinforced = $location_data['security']['security_reinforced'] ?? false;
        $security_notes = $location_data['security']['security_notes'] ?? '';
        
        // Utilities
        $utility_blood_storage = $location_data['utilities']['utility_blood_storage'] ?? false;
        $utility_computers = $location_data['utilities']['utility_computers'] ?? false;
        $utility_library = $location_data['utilities']['utility_library'] ?? false;
        $utility_medical = $location_data['utilities']['utility_medical'] ?? false;
        $utility_workshop = $location_data['utilities']['utility_workshop'] ?? false;
        $utility_hidden_caches = $location_data['utilities']['utility_hidden_caches'] ?? false;
        $utility_armory = $location_data['utilities']['utility_armory'] ?? false;
        $utility_communications = $location_data['utilities']['utility_communications'] ?? false;
        $utility_notes = $location_data['utilities']['utility_notes'] ?? '';
        
        // Social
        $social_features = $location_data['social']['social_features'] ?? '';
        $capacity = $location_data['social']['capacity'] ?? 0;
        $prestige_level = $location_data['social']['prestige_level'] ?? 0;
        
        // Supernatural
        $has_supernatural = $location_data['supernatural']['has_supernatural'] ?? false;
        $node_points = $location_data['supernatural']['node_points'] ?? 0;
        $node_type = $location_data['supernatural']['node_type'] ?? '';
        $ritual_space = $location_data['supernatural']['ritual_space'] ?? '';
        $magical_protection = $location_data['supernatural']['magical_protection'] ?? '';
        $cursed_blessed = $location_data['supernatural']['cursed_blessed'] ?? '';
        
        // Relationships
        $parent_location_id = !empty($location_data['relationships']['parent_location_id']) ? (int)$location_data['relationships']['parent_location_id'] : null;
        $relationship_type = $location_data['relationships']['relationship_type'] ?? '';
        $relationship_notes = $location_data['relationships']['relationship_notes'] ?? '';
        
        // Media
        $image = $location_data['media']['image'] ?? '';
        
        // Convert boolean values to integers for database
        $security_locks = $security_locks ? 1 : 0;
        $security_alarms = $security_alarms ? 1 : 0;
        $security_guards = $security_guards ? 1 : 0;
        $security_hidden_entrance = $security_hidden_entrance ? 1 : 0;
        $security_sunlight_protected = $security_sunlight_protected ? 1 : 0;
        $security_warding_rituals = $security_warding_rituals ? 1 : 0;
        $security_cameras = $security_cameras ? 1 : 0;
        $security_reinforced = $security_reinforced ? 1 : 0;
        $utility_blood_storage = $utility_blood_storage ? 1 : 0;
        $utility_computers = $utility_computers ? 1 : 0;
        $utility_library = $utility_library ? 1 : 0;
        $utility_medical = $utility_medical ? 1 : 0;
        $utility_workshop = $utility_workshop ? 1 : 0;
        $utility_hidden_caches = $utility_hidden_caches ? 1 : 0;
        $utility_armory = $utility_armory ? 1 : 0;
        $utility_communications = $utility_communications ? 1 : 0;
        $has_supernatural = $has_supernatural ? 1 : 0;
        
        // Bind parameters
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
        
        // Commit transaction
        mysqli_commit($conn);
        
        echo "✅ Location imported successfully!\n";
        echo "🆔 Location ID: $location_id\n";
        echo "📝 Name: $name\n";
        echo "🏷️ Type: $type\n";
        echo "📍 District: $district\n";
        echo "👤 Owner Type: $owner_type\n";
        echo "🔒 Access Control: $access_control\n";
        echo "🛡️ Security Level: $security_level\n";
        
        echo "\n🎉 SUCCESS! Location is now in the database.\n";
        echo "🔗 View in admin panel: <a href='../admin/admin_locations.php'>admin_locations.php</a>\n";
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        throw $e;
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "📋 Check your JSON file format against the template.\n";
}

echo "</pre>";
$conn->close();
?>
