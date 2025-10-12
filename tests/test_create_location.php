<?php
/**
 * Test Location Creation - Debug Version
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/connect.php';

echo "<h1>Testing Location Creation</h1>";
echo "<pre>";

// Check if locations table exists
echo "Step 1: Checking if locations table exists...\n";
$table_check = $conn->query("SHOW TABLES LIKE 'locations'");
if ($table_check->num_rows === 0) {
    die("❌ locations table does not exist! Run create_locations_table.php first\n");
}
echo "✅ locations table exists\n\n";

// Test with sample data
echo "Step 2: Testing INSERT with sample data...\n";

// Extract all variables individually (bind_param needs actual variables)
$name = 'Test Haven';
$type = 'Haven';
$summary = 'A test location';
$description = 'This is a test location for debugging.';
$notes = 'Admin notes here';
$status = 'Active';
$status_notes = '';
$district = 'Downtown Phoenix';
$address = '123 Test St';
$latitude = 33.4484;
$longitude = -112.0740;
$owner_type = 'Individual';
$owner_notes = 'Test owner';
$faction = 'Camarilla';
$access_control = 'Private';
$access_notes = 'Invitation only';
$security_level = 3;
$security_locks = 1;
$security_alarms = 1;
$security_guards = 0;
$security_hidden_entrance = 0;
$security_sunlight_protected = 1;
$security_warding_rituals = 0;
$security_cameras = 0;
$security_reinforced = 0;
$security_notes = 'Basic security';
$utility_blood_storage = 1;
$utility_computers = 0;
$utility_library = 0;
$utility_medical = 0;
$utility_workshop = 0;
$utility_hidden_caches = 0;
$utility_armory = 0;
$utility_communications = 0;
$utility_notes = '';
$social_features = 'Private haven';
$capacity = 5;
$prestige_level = 2;
$has_supernatural = 0;
$node_points = 0;
$node_type = '';
$ritual_space = '';
$magical_protection = '';
$cursed_blessed = '';
$parent_location_id = null;
$relationship_type = '';
$relationship_notes = '';
$image = '';

try {
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
        die("❌ Failed to prepare statement: " . $conn->error . "\n");
    }
    
    echo "✅ Statement prepared\n";
    echo "Binding parameters...\n";
    echo "Parameters to bind: 48\n";
    
    // Type string: sssssssssddsssssiiiiiiiiisiiiiiiiissiiiissssisss (48 chars)
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
    
    echo "✅ Parameters bound\n";
    echo "Executing query...\n";
    
    if (!$stmt->execute()) {
        die("❌ Failed to execute: " . $stmt->error . "\n");
    }
    
    $location_id = $conn->insert_id;
    echo "✅ Location created with ID: $location_id\n\n";
    
    // Verify it was inserted
    $verify = $conn->query("SELECT name, type FROM locations WHERE id = $location_id");
    $row = $verify->fetch_assoc();
    echo "✅ Verified in database: {$row['name']} ({$row['type']})\n\n";
    
    echo "🎉 SUCCESS! Location creation API should work now!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre>";
$conn->close();
?>
