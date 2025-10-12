<?php
// Debug UPDATE parameter count
echo "=== UPDATE PARAMETER DEBUG ===\n";

// Test the UPDATE SQL
$update_sql = "UPDATE characters SET 
    player_name = ?, chronicle = ?, nature = ?, demeanor = ?, 
    concept = ?, clan = ?, generation = ?, sire = ?, pc = ?, 
    biography = ?, appearance = ?, notes = ?, experience_total = ?, 
    experience_unspent = ?, morality_path = ?, conscience = ?, 
    self_control = ?, courage = ?, path_rating = ?, 
    willpower_permanent = ?, willpower_current = ?, 
    blood_pool_max = ?, blood_pool_current = ?, health_status = ?,
    updated_at = CURRENT_TIMESTAMP
    WHERE id = ?";

// Count placeholders
$placeholder_count = substr_count($update_sql, '?');
echo "1. Placeholders in UPDATE SQL: " . $placeholder_count . "\n";

// Count type string
$type_string = 'ssssssissssiisiiiiiiiisi';
$type_count = strlen($type_string);
echo "2. Type string length: " . $type_count . "\n";

// Count parameters (24 data + 1 WHERE id)
$param_count = 25;
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
