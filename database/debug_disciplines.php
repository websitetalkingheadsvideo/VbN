<?php
/**
 * Debug script to check discipline data
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/discipline_functions.php';

echo "<!DOCTYPE html><html><head><title>Debug Disciplines</title></head><body>";
echo "<h1>Discipline Debug Info</h1>";
echo "<pre>";

// Check character_disciplines table
echo "=== Character Disciplines Table ===\n";
$result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM character_disciplines");
$row = mysqli_fetch_assoc($result);
echo "Total records: " . $row['cnt'] . "\n\n";

// Get sample character with disciplines
$result = mysqli_query($conn, "SELECT character_id, COUNT(*) as cnt FROM character_disciplines GROUP BY character_id LIMIT 5");
echo "Sample characters with disciplines:\n";
while ($r = mysqli_fetch_assoc($result)) {
    echo "  Character " . $r['character_id'] . ": " . $r['cnt'] . " disciplines\n";
}

// Get a specific character's disciplines directly
echo "\n=== Direct Query Test ===\n";
$test_char_id = 1; // Try first character
$result = mysqli_query($conn, "SELECT discipline_name, level FROM character_disciplines WHERE character_id = $test_char_id LIMIT 5");
if ($result && mysqli_num_rows($result) > 0) {
    echo "Character $test_char_id has disciplines:\n";
    while ($r = mysqli_fetch_assoc($result)) {
        echo "  - " . $r['discipline_name'] . " (level " . $r['level'] . ")\n";
    }
} else {
    echo "Character $test_char_id has no disciplines in database\n";
}

// Test getCharacterAllDisciplines function
echo "\n=== Function Test ===\n";
if ($result && mysqli_num_rows($result) > 0) {
    mysqli_data_seek($result, 0);
    $first_row = mysqli_fetch_assoc($result);
    $test_char_id = $first_row ? mysqli_fetch_field_direct($result, 0) : $test_char_id;
    
    // Reset result and get actual character_id
    $result = mysqli_query($conn, "SELECT character_id FROM character_disciplines LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $test_char_id = $row['character_id'];
        echo "Testing getCharacterAllDisciplines($test_char_id)...\n";
        $all_discs = getCharacterAllDisciplines($test_char_id);
        if (empty($all_discs)) {
            echo "❌ Function returned empty array!\n";
        } else {
            echo "✅ Function returned " . count($all_discs) . " disciplines:\n";
            foreach ($all_discs as $name => $data) {
                echo "  - $name (level {$data['level']}, " . count($data['powers']) . " powers)\n";
            }
        }
    }
} else {
    echo "No character disciplines found to test\n";
}

// Check if disciplines table has the paths
echo "\n=== Disciplines Table ===\n";
$result = mysqli_query($conn, "SELECT name FROM disciplines WHERE name LIKE '%Path%' OR name LIKE '%Thaumaturgy%'");
if ($result) {
    echo "Found " . mysqli_num_rows($result) . " path disciplines:\n";
    while ($r = mysqli_fetch_assoc($result)) {
        echo "  - " . $r['name'] . "\n";
    }
} else {
    echo "Error querying disciplines: " . mysqli_error($conn) . "\n";
}

// Check discipline_powers
echo "\n=== Discipline Powers ===\n";
$result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM discipline_powers");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "Total powers: " . $row['cnt'] . "\n";
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

echo "</pre>";
echo "</body></html>";
?>

