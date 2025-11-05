<?php
/**
 * Test discipline function with error reporting
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/discipline_functions.php';

echo "<!DOCTYPE html><html><head><title>Test Discipline Function</title></head><body>";
echo "<h1>Testing getCharacterAllDisciplines</h1>";
echo "<pre>";

try {
    // Test with character 26 (which has disciplines according to debug)
    $character_id = 26;
    
    echo "Testing character_id: $character_id\n";
    echo "Connection status: " . (isset($conn) && $conn ? "Connected" : "Not connected") . "\n\n";
    
    if (!$conn) {
        throw new Exception("Database connection not available");
    }
    
    // First, verify data exists
    $check_sql = "SELECT discipline_name, level FROM character_disciplines WHERE character_id = $character_id";
    echo "Direct query: $check_sql\n";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (!$check_result) {
        throw new Exception("Direct query failed: " . mysqli_error($conn));
    }
    
    echo "Found " . mysqli_num_rows($check_result) . " disciplines:\n";
    while ($row = mysqli_fetch_assoc($check_result)) {
        echo "  - " . $row['discipline_name'] . " (level " . $row['level'] . ")\n";
    }
    
    echo "\n--- Calling getCharacterAllDisciplines($character_id) ---\n";
    $all_discs = getCharacterAllDisciplines($character_id);
    
    if (empty($all_discs)) {
        echo "❌ Function returned empty array!\n";
        
        // Try to see what went wrong
        echo "\nChecking for errors...\n";
        $test_sql = "SELECT discipline_name, level FROM character_disciplines WHERE character_id = $character_id ORDER BY discipline_name ASC";
        $test_result = mysqli_query($conn, $test_sql);
        if ($test_result) {
            echo "Direct query works, found " . mysqli_num_rows($test_result) . " rows\n";
            while ($test_row = mysqli_fetch_assoc($test_result)) {
                echo "  Row: " . print_r($test_row, true);
            }
        } else {
            echo "Direct query failed: " . mysqli_error($conn) . "\n";
        }
    } else {
        echo "✅ Function returned " . count($all_discs) . " disciplines:\n";
        foreach ($all_discs as $name => $data) {
            echo "  - $name (level {$data['level']}, " . count($data['powers']) . " powers)\n";
            if (!empty($data['powers'])) {
                foreach ($data['powers'] as $power) {
                    echo "    • " . $power['power_name'] . " (Level " . $power['level'] . ")\n";
                }
            }
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString();
}

echo "</pre>";
echo "</body></html>";
?>

