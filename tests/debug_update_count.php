<?php
// Debug exact UPDATE parameter count
echo "=== UPDATE COUNT DEBUG ===\n";

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

$placeholder_count = substr_count($update_sql, '?');
echo "1. Placeholders: " . $placeholder_count . "\n";

// Count each parameter type manually
$params = [
    'player_name', 'chronicle', 'nature', 'demeanor', 'concept', 'clan', 
    'generation', 'sire', 'pc', 'biography', 'appearance', 'notes', 
    'experience_total', 'experience_unspent', 'morality_path', 
    'conscience', 'self_control', 'courage', 'path_rating', 
    'willpower_permanent', 'willpower_current', 'blood_pool_max', 
    'blood_pool_current', 'health_status', 'character_id'
];

echo "2. Parameter list:\n";
foreach ($params as $i => $param) {
    $type = in_array($param, ['generation', 'pc', 'experience_total', 'experience_unspent', 
                              'conscience', 'self_control', 'courage', 'path_rating', 
                              'willpower_permanent', 'willpower_current', 'blood_pool_max', 
                              'blood_pool_current', 'character_id']) ? 'i' : 's';
    echo "   " . ($i+1) . ". " . $param . " (" . $type . ")\n";
}

$param_count = count($params);
echo "3. Total parameters: " . $param_count . "\n";

// Generate correct type string
$type_string = '';
foreach ($params as $param) {
    $type_string .= in_array($param, ['generation', 'pc', 'experience_total', 'experience_unspent', 
                                      'conscience', 'self_control', 'courage', 'path_rating', 
                                      'willpower_permanent', 'willpower_current', 'blood_pool_max', 
                                      'blood_pool_current', 'character_id']) ? 'i' : 's';
}

echo "4. Correct type string: " . $type_string . "\n";
echo "5. Type string length: " . strlen($type_string) . "\n";

if ($placeholder_count === $param_count && $param_count === strlen($type_string)) {
    echo "6. All counts match: YES\n";
} else {
    echo "6. All counts match: NO\n";
}

echo "=== DEBUG COMPLETE ===\n";
?>
