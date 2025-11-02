<?php
/**
 * Test script to check abilities in database
 */
session_start();
require_once __DIR__ . '/../includes/connect.php';

// Get a character ID from GET or use a default
$character_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$character_id) {
    echo "Usage: test_abilities.php?id=1\n";
    echo "\nFirst, let's see what characters have abilities:\n\n";
    
    // Show characters with abilities
    $result = mysqli_query($conn, "
        SELECT c.id, c.character_name, COUNT(ca.id) as ability_count
        FROM characters c
        LEFT JOIN character_abilities ca ON c.id = ca.character_id
        GROUP BY c.id, c.character_name
        HAVING ability_count > 0
        ORDER BY ability_count DESC
        LIMIT 10
    ");
    
    if ($result) {
        echo "Characters with abilities:\n";
        while ($row = mysqli_fetch_assoc($result)) {
            echo "ID: {$row['id']} - {$row['character_name']} - {$row['ability_count']} abilities\n";
        }
    }
    exit;
}

echo "Testing abilities for character ID: $character_id\n\n";

// Get character name
$char = db_fetch_one($conn, "SELECT character_name FROM characters WHERE id = ?", "i", [$character_id]);
if (!$char) {
    echo "Character not found!\n";
    exit;
}

echo "Character: {$char['character_name']}\n\n";

// Get raw abilities
$abilities_raw = db_fetch_all($conn,
    "SELECT ca.id, ca.ability_name, ca.specialization, ca.level, ca.xp_cost 
     FROM character_abilities ca 
     WHERE ca.character_id = ?",
    "i",
    [$character_id]
);

echo "Raw abilities from database: " . count($abilities_raw) . "\n";
if (count($abilities_raw) > 0) {
    echo "First ability: " . json_encode($abilities_raw[0], JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo "No abilities found in character_abilities table for this character!\n";
    exit;
}

// Test category lookup
echo "Testing category lookup:\n";
$abilities = [];
foreach ($abilities_raw as $ability) {
    $category = db_fetch_one($conn,
        "SELECT category FROM abilities_master WHERE name = ? LIMIT 1",
        "s",
        [$ability['ability_name']]
    );
    
    $resolved_category = $category ? $category['category'] : 'Optional';
    
    $abilities[] = [
        'id' => $ability['id'],
        'ability_name' => $ability['ability_name'],
        'ability_category' => $resolved_category,
        'specialization' => $ability['specialization'],
        'level' => $ability['level'] ?? 1,
        'xp_cost' => $ability['xp_cost'] ?? 0
    ];
    
    echo "  {$ability['ability_name']} -> {$resolved_category}";
    if (!$category) {
        echo " (NOT in abilities_master)";
    }
    echo "\n";
}

echo "\nFinal abilities array:\n";
echo json_encode($abilities, JSON_PRETTY_PRINT);

mysqli_close($conn);
?>

