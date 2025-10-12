<?php
// Debug parameter count issue
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== PARAMETER DEBUG ===\n";

// Test the exact SQL and parameter count
$character_sql = "INSERT INTO characters (
    user_id, character_name, player_name, chronicle, nature, demeanor, 
    concept, clan, generation, sire, pc, biography, appearance, notes,
    experience_total, experience_unspent, morality_path, conscience, 
    self_control, courage, path_rating, willpower_permanent, willpower_current,
    blood_pool_max, blood_pool_current, health_status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Count placeholders
$placeholder_count = substr_count($character_sql, '?');
echo "1. Placeholders in SQL: " . $placeholder_count . "\n";

// Count type string
$type_string = 'isssssssissssiisiiiiiiiis';
$type_count = strlen($type_string);
echo "2. Type string length: " . $type_count . "\n";

// Count parameters
$params = [
    'user_id', 'character_name', 'player_name', 'chronicle', 'nature', 'demeanor', 
    'concept', 'clan', 'generation', 'sire', 'pc', 'biography', 'appearance', 'notes',
    'experience_total', 'experience_unspent', 'morality_path', 'conscience', 
    'self_control', 'courage', 'path_rating', 'willpower_permanent', 'willpower_current',
    'blood_pool_max', 'blood_pool_current', 'health_status'
];
$param_count = count($params);
echo "3. Parameter count: " . $param_count . "\n";

// Check if they match
if ($placeholder_count === $type_count && $type_count === $param_count) {
    echo "4. All counts match: YES\n";
} else {
    echo "4. All counts match: NO\n";
    echo "   Placeholders: " . $placeholder_count . "\n";
    echo "   Types: " . $type_count . "\n";
    echo "   Params: " . $param_count . "\n";
}

echo "=== DEBUG COMPLETE ===\n";
?>
