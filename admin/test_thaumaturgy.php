<?php
/**
 * Test script to check Thaumaturgy paths and their powers in the database
 */
require_once __DIR__ . '/../includes/connect.php';

if (!$conn) {
    die("Database connection failed");
}

// Check Thaumaturgy paths and their powers
$query = "SELECT d.id, d.name as discipline_name, d.parent_discipline, 
                 dp.power_level, dp.power_name, dp.description as power_description
          FROM disciplines d
          LEFT JOIN discipline_powers dp ON d.id = dp.discipline_id
          WHERE d.name LIKE '%Path%' OR d.parent_discipline = 'Thaumaturgy' OR d.name = 'Thaumaturgy'
          ORDER BY d.name, dp.power_level";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

header('Content-Type: text/plain');
echo "Thaumaturgy Paths and Powers:\n";
echo str_repeat("=", 80) . "\n\n";

$current_discipline = '';
while ($row = mysqli_fetch_assoc($result)) {
    if ($current_discipline !== $row['discipline_name']) {
        if ($current_discipline !== '') {
            echo "\n";
        }
        $current_discipline = $row['discipline_name'];
        echo "Discipline: {$row['discipline_name']}\n";
        echo "Parent: " . ($row['parent_discipline'] ?? 'None') . "\n";
        echo "Powers:\n";
    }
    
    if ($row['power_level']) {
        echo "  Level {$row['power_level']}: {$row['power_name']}\n";
        if ($row['power_description']) {
            echo "    Description: " . substr($row['power_description'], 0, 60) . "...\n";
        }
    } else {
        echo "  (No powers found)\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "Total disciplines found: " . mysqli_num_rows($result) . "\n";
?>

