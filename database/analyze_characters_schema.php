<?php
/**
 * Character Description Fields - Schema Analysis Script
 * Analyzes the current characters table schema to determine what columns need to be added
 * 
 * Usage: Run this script via web browser or command line to check current schema
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/connect.php';

echo "<h2>Characters Table Schema Analysis</h2>\n";
echo "<pre>\n";

// Check if characters table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'characters'");
if (mysqli_num_rows($table_check) == 0) {
    echo "ERROR: Characters table does not exist!\n";
    exit;
}

// Get current table structure
echo "=== Current Table Structure ===\n";
$describe_result = mysqli_query($conn, "DESCRIBE characters");
if ($describe_result) {
    echo "\nColumn Details:\n";
    echo str_pad("Field", 25) . str_pad("Type", 30) . str_pad("Null", 8) . str_pad("Key", 8) . "Extra\n";
    echo str_repeat("-", 100) . "\n";
    
    $columns = [];
    while ($row = mysqli_fetch_assoc($describe_result)) {
        $columns[] = $row['Field'];
        echo str_pad($row['Field'], 25) . 
             str_pad($row['Type'], 30) . 
             str_pad($row['Null'], 8) . 
             str_pad($row['Key'], 8) . 
             $row['Extra'] . "\n";
    }
    
    echo "\n=== Required Columns Check ===\n";
    $required_columns = ['appearance', 'biography', 'notes'];
    $missing_columns = [];
    
    foreach ($required_columns as $col) {
        if (in_array($col, $columns)) {
            echo "✓ Column '$col' EXISTS\n";
            
            // Check character set and collation for existing columns
            $col_info = mysqli_query($conn, "SHOW FULL COLUMNS FROM characters WHERE Field = '$col'");
            if ($col_info && $row = mysqli_fetch_assoc($col_info)) {
                echo "  - Type: " . $row['Type'] . "\n";
                echo "  - Collation: " . ($row['Collation'] ?? 'N/A') . "\n";
                echo "  - Character Set: " . ($row['Charset'] ?? 'N/A') . "\n";
            }
        } else {
            echo "✗ Column '$col' MISSING\n";
            $missing_columns[] = $col;
        }
    }
    
    echo "\n=== Table Character Set ===\n";
    $table_info = mysqli_query($conn, "SHOW CREATE TABLE characters");
    if ($table_info && $row = mysqli_fetch_assoc($table_info)) {
        $create_table = $row['Create Table'];
        if (preg_match('/CHARACTER SET\s+(\w+)/i', $create_table, $matches)) {
            echo "Table Character Set: " . $matches[1] . "\n";
        }
        if (preg_match('/COLLATE\s+(\w+)/i', $create_table, $matches)) {
            echo "Table Collation: " . $matches[1] . "\n";
        }
    }
    
    echo "\n=== Summary ===\n";
    if (empty($missing_columns)) {
        echo "All required columns exist!\n";
        echo "Migration may still be needed to ensure proper character set/collation.\n";
    } else {
        echo "Missing columns: " . implode(', ', $missing_columns) . "\n";
        echo "Migration script needed to add these columns.\n";
    }
    
} else {
    echo "ERROR: Could not describe characters table: " . mysqli_error($conn) . "\n";
}

echo "</pre>\n";
mysqli_close($conn);
?>

