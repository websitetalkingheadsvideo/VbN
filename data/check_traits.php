<?php
require_once __DIR__ . '/../includes/connect.php';

// Check trait_type values
$result = mysqli_query($conn, "
    SELECT DISTINCT trait_type, COUNT(*) as count 
    FROM character_traits 
    WHERE character_id = 23 
    GROUP BY trait_type
");

echo "<h3>Trait Types in Database:</h3>";
while ($row = mysqli_fetch_assoc($result)) {
    $type = $row['trait_type'];
    echo "Type: '" . $type . "' (length: " . strlen($type) . ") - Count: " . $row['count'] . "<br>";
}

// Check table structure
$result = mysqli_query($conn, "SHOW COLUMNS FROM character_traits LIKE 'trait_type'");
$col = mysqli_fetch_assoc($result);
echo "<br><h3>Column Definition:</h3>";
echo "Type: " . $col['Type'] . "<br>";
echo "Null: " . $col['Null'] . "<br>";
echo "Default: " . $col['Default'] . "<br>";

// Show actual data
echo "<br><h3>Sample Data:</h3>";
$result = mysqli_query($conn, "
    SELECT trait_name, trait_category, trait_type, 
           HEX(trait_type) as hex_type,
           LENGTH(trait_type) as type_length
    FROM character_traits 
    WHERE character_id = 23 
    LIMIT 5
");

while ($row = mysqli_fetch_assoc($result)) {
    echo "Trait: {$row['trait_name']}, Category: {$row['trait_category']}, ";
    echo "Type: '{$row['trait_type']}', Hex: {$row['hex_type']}, Length: {$row['type_length']}<br>";
}
?>

