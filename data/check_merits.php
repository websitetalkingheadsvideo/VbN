<?php
require_once __DIR__ . '/../includes/connect.php';

echo "<h3>Merits & Flaws Raw Data (Character 25):</h3>";

$result = mysqli_query($conn, "
    SELECT name, type, category, point_value,
           HEX(type) as hex_type,
           LENGTH(type) as type_length
    FROM character_merits_flaws 
    WHERE character_id = 25
    ORDER BY name
");

while ($row = mysqli_fetch_assoc($result)) {
    echo "Name: {$row['name']}<br>";
    echo "  Type: '{$row['type']}' (hex: {$row['hex_type']}, length: {$row['type_length']})<br>";
    echo "  Category: {$row['category']}, Points: {$row['point_value']}<br><br>";
}

// Check column definition
$result = mysqli_query($conn, "SHOW COLUMNS FROM character_merits_flaws LIKE 'type'");
$col = mysqli_fetch_assoc($result);
echo "<h3>Column Type Definition:</h3>";
echo $col['Type'];
?>

