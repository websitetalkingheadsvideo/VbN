<?php
/**
 * List all character IDs in the database
 */

require_once __DIR__ . '/../includes/connect.php';

echo "<!DOCTYPE html><html><head><title>Character IDs</title></head><body>";
echo "<h1>Character IDs in Database</h1>";
echo "<pre>";

$sql = "SELECT id, character_name, player_name, clan, pc 
        FROM characters 
        ORDER BY id ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    echo "Error: " . mysqli_error($conn);
} else {
    echo "Total characters: " . mysqli_num_rows($result) . "\n\n";
    echo "ID | Name | Player | Clan | Type\n";
    echo str_repeat("-", 80) . "\n";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $type = $row['pc'] ? 'PC' : 'NPC';
        printf("%-3d | %-30s | %-20s | %-20s | %s\n", 
            $row['id'],
            substr($row['character_name'], 0, 30),
            substr($row['player_name'], 0, 20),
            substr($row['clan'], 0, 20),
            $type
        );
    }
    
    echo "\n\nCharacter IDs (comma-separated):\n";
    mysqli_data_seek($result, 0);
    $ids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $ids[] = $row['id'];
    }
    echo implode(', ', $ids);
}

echo "</pre>";
echo "</body></html>";
?>

