<?php
/**
 * Test script to directly test load_character.php functionality
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/connect.php';

$character_id = isset($_GET['id']) ? intval($_GET['id']) : 88; // Default to Roland Cross

echo "Testing load_character.php for character ID: $character_id\n\n";

// Test direct query
$direct_result = mysqli_query($conn,
    "SELECT id, ability_name, specialization, level 
     FROM character_abilities 
     WHERE character_id = $character_id
     ORDER BY level DESC, ability_name"
);

if ($direct_result && mysqli_num_rows($direct_result) > 0) {
    echo "✅ Direct query found " . mysqli_num_rows($direct_result) . " abilities:\n\n";
    $abilities = [];
    while ($row = mysqli_fetch_assoc($direct_result)) {
        $abilities[] = $row;
        echo "- {$row['ability_name']} (Level: {$row['level']}, Specialization: " . ($row['specialization'] ?: 'none') . ")\n";
    }
    mysqli_free_result($direct_result);
    
    echo "\n\nTesting category lookup:\n";
    foreach ($abilities as $ability) {
        $category = db_fetch_one($conn,
            "SELECT category FROM abilities_master WHERE name = ? LIMIT 1",
            "s",
            [$ability['ability_name']]
        );
        
        $cat = $category ? $category['category'] : 'Optional';
        echo "- {$ability['ability_name']} -> {$cat}\n";
    }
    
    echo "\n\nSimulating load_character.php response:\n";
    $abilities_full = [];
    foreach ($abilities as $ability) {
        $category = db_fetch_one($conn,
            "SELECT category FROM abilities_master WHERE name = ? LIMIT 1",
            "s",
            [$ability['ability_name']]
        );
        
        $abilities_full[] = [
            'ability_name' => $ability['ability_name'],
            'ability_category' => $category ? $category['category'] : 'Optional',
            'specialization' => $ability['specialization'] ?? null,
            'level' => intval($ability['level']),
            'xp_cost' => 0 // xp_cost column doesn't exist in character_abilities table
        ];
    }
    
    echo json_encode(['abilities_full' => $abilities_full], JSON_PRETTY_PRINT);
    
} else {
    echo "❌ Direct query returned 0 rows\n";
    echo "Error: " . mysqli_error($conn) . "\n";
    
    // Check if character exists
    $char_check = mysqli_query($conn, "SELECT id, character_name FROM characters WHERE id = $character_id");
    if ($char_check && mysqli_num_rows($char_check) > 0) {
        $char = mysqli_fetch_assoc($char_check);
        echo "Character exists: {$char['character_name']}\n";
        
        // Check ability count
        $count_check = mysqli_query($conn, "SELECT COUNT(*) as count FROM character_abilities WHERE character_id = $character_id");
        if ($count_check) {
            $count = mysqli_fetch_assoc($count_check);
            echo "Abilities in database: {$count['count']}\n";
        }
    } else {
        echo "Character ID $character_id not found!\n";
    }
}

mysqli_close($conn);
?>

