<?php
echo "<pre>";
echo "Parameter Counting Tool\n";
echo "=======================\n\n";

$params = [
    'name' => 's',
    'type' => 's',
    'summary' => 's',
    'description' => 's',
    'notes' => 's',
    'status' => 's',
    'status_notes' => 's',
    'district' => 's',
    'address' => 's',
    'latitude' => 'd',
    'longitude' => 'd',
    'owner_type' => 's',
    'owner_notes' => 's',
    'faction' => 's',
    'access_control' => 's',
    'access_notes' => 's',
    'security_level' => 'i',
    'security_locks' => 'i',
    'security_alarms' => 'i',
    'security_guards' => 'i',
    'security_hidden_entrance' => 'i',
    'security_sunlight_protected' => 'i',
    'security_warding_rituals' => 'i',
    'security_cameras' => 'i',
    'security_reinforced' => 'i',
    'security_notes' => 's',
    'utility_blood_storage' => 'i',
    'utility_computers' => 'i',
    'utility_library' => 'i',
    'utility_medical' => 'i',
    'utility_workshop' => 'i',
    'utility_hidden_caches' => 'i',
    'utility_armory' => 'i',
    'utility_communications' => 'i',
    'utility_notes' => 's',
    'social_features' => 's',
    'capacity' => 'i',
    'prestige_level' => 'i',
    'has_supernatural' => 'i',
    'node_points' => 'i',
    'node_type' => 's',
    'ritual_space' => 's',
    'magical_protection' => 's',
    'cursed_blessed' => 's',
    'parent_location_id' => 'i',
    'relationship_type' => 's',
    'relationship_notes' => 's',
    'image' => 's'
];

$type_string = '';
$count = 0;

foreach ($params as $name => $type) {
    $count++;
    echo "$count. $name => $type\n";
    $type_string .= $type;
}

echo "\n=======================\n";
echo "Total parameters: $count\n";
echo "Type string: $type_string\n";
echo "Type string length: " . strlen($type_string) . "\n";
echo "\nCopy this type string:\n";
echo "'$type_string'\n";

echo "</pre>";
?>

