<?php
/**
 * Diagnostic script to check character disciplines data
 */

require_once __DIR__ . '/../includes/connect.php';

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Check Character Disciplines</title><style>
    body { font-family: monospace; background: #1a0f0f; color: #d4c4b0; padding: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #8b0000; padding: 8px; text-align: left; }
    th { background: #8b0000; color: #fff; }
    .empty { color: #ff6b6b; }
    .has-data { color: #51cf66; }
</style></head><body>";
echo "<h1>🔍 Character Disciplines Diagnostic</h1>";

// Check table structure
echo "<h2>Table Structure</h2>";
$desc = mysqli_query($conn, "DESCRIBE character_disciplines");
echo "<table>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = mysqli_fetch_assoc($desc)) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "</tr>";
}
echo "</table>";

// Check total count
$total = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM character_disciplines");
$total_row = mysqli_fetch_assoc($total);
echo "<p><strong>Total discipline records:</strong> {$total_row['cnt']}</p>";

// Check unique characters
$chars = mysqli_query($conn, "SELECT COUNT(DISTINCT character_id) as cnt FROM character_disciplines");
$chars_row = mysqli_fetch_assoc($chars);
echo "<p><strong>Characters with disciplines:</strong> {$chars_row['cnt']}</p>";

// Check for NULL or invalid levels
$null_levels = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM character_disciplines WHERE level IS NULL OR level = 0");
$null_row = mysqli_fetch_assoc($null_levels);
if ($null_row['cnt'] > 0) {
    echo "<p class='empty'><strong>⚠️ Records with NULL or 0 level:</strong> {$null_row['cnt']}</p>";
}

// Show sample of first 20 records
echo "<h2>Sample Records (First 20)</h2>";
$sample = mysqli_query($conn, 
    "SELECT character_id, discipline_name, level, xp_cost 
     FROM character_disciplines 
     ORDER BY character_id, discipline_name 
     LIMIT 20");
echo "<table>";
echo "<tr><th>Character ID</th><th>Discipline Name</th><th>Level</th><th>XP Cost</th></tr>";
while ($row = mysqli_fetch_assoc($sample)) {
    $class = ($row['level'] > 0) ? 'has-data' : 'empty';
    echo "<tr class='{$class}'>";
    echo "<td>{$row['character_id']}</td>";
    echo "<td>{$row['discipline_name']}</td>";
    echo "<td>{$row['level']}</td>";
    echo "<td>{$row['xp_cost']}</td>";
    echo "</tr>";
}
echo "</table>";

// Check specific characters
if (isset($_GET['char_id'])) {
    $char_id = (int)$_GET['char_id'];
    echo "<h2>Character ID {$char_id} Disciplines</h2>";
    $char_discs = mysqli_query($conn,
        "SELECT discipline_name, level, xp_cost 
         FROM character_disciplines 
         WHERE character_id = {$char_id}");
    
    if (mysqli_num_rows($char_discs) > 0) {
        echo "<table>";
        echo "<tr><th>Discipline Name</th><th>Level</th><th>XP Cost</th></tr>";
        while ($row = mysqli_fetch_assoc($char_discs)) {
            echo "<tr>";
            echo "<td>{$row['discipline_name']}</td>";
            echo "<td>{$row['level']}</td>";
            echo "<td>{$row['xp_cost']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='empty'>No disciplines found for character {$char_id}</p>";
    }
}

// List characters with discipline counts
echo "<h2>Characters with Discipline Counts</h2>";
$char_counts = mysqli_query($conn,
    "SELECT character_id, COUNT(*) as disc_count, GROUP_CONCAT(discipline_name SEPARATOR ', ') as disciplines
     FROM character_disciplines 
     GROUP BY character_id 
     ORDER BY disc_count DESC 
     LIMIT 20");
echo "<table>";
echo "<tr><th>Character ID</th><th>Discipline Count</th><th>Disciplines</th></tr>";
while ($row = mysqli_fetch_assoc($char_counts)) {
    echo "<tr>";
    echo "<td><a href='?char_id={$row['character_id']}'>{$row['character_id']}</a></td>";
    echo "<td>{$row['disc_count']}</td>";
    echo "<td>{$row['disciplines']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "</body></html>";
mysqli_close($conn);
?>

