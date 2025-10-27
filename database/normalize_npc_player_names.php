<?php
/**
 * Normalize NPC Player Names to "NPC"
 * Fixes inconsistent player_name values for NPCs - standardizes to "NPC"
 */

require_once __DIR__ . '/../includes/connect.php';

echo "<h2>NPC Player Name Normalization</h2>";
echo "<p>Standardizing all NPC player_name values to 'NPC'</p>";

// Track what's being changed
$before_query = "SELECT player_name, COUNT(*) as count 
                 FROM characters 
                 WHERE LOWER(TRIM(COALESCE(player_name, ''))) IN ('npc', 'st/npc', 'st / npc', '', 'player name or st/npc', 'st / npc', 'player name or st / npc')
                    OR player_name IS NULL
                    OR player_name = ''
                 GROUP BY player_name
                 ORDER BY count DESC";

echo "<h3>Before Normalization:</h3>";
echo "<table border='1' style='border-collapse: collapse; margin-bottom: 20px;'>";
echo "<tr><th>Current Value</th><th>Count</th></tr>";

$before_result = mysqli_query($conn, $before_query);
$changes = [];

if ($before_result) {
    while ($row = mysqli_fetch_assoc($before_result)) {
        $value = $row['player_name'] ?? 'NULL';
        $count = $row['count'];
        echo "<tr><td>" . htmlspecialchars($value) . "</td><td>{$count}</td></tr>";
        $changes[$value] = $count;
    }
} else {
    echo "<tr><td colspan='2'>No variations found or error: " . mysqli_error($conn) . "</td></tr>";
}

echo "</table>";

// Normalize all variations to "NPC"
echo "<h3>Normalizing to 'NPC'...</h3>";

$update_query = "UPDATE characters 
                 SET player_name = 'NPC'
                 WHERE LOWER(TRIM(COALESCE(player_name, ''))) IN ('npc', 'st/npc', 'st / npc', '', 'player name or st/npc', 'player name or st / npc')
                    OR player_name IS NULL
                    OR player_name = ''";

$result = mysqli_query($conn, $update_query);

if ($result) {
    $affected = mysqli_affected_rows($conn);
    echo "<p style='color: green;'>✅ Normalized $affected records to 'NPC'</p>";
    
    if ($affected > 0) {
        echo "<h3>Changes Made:</h3>";
        echo "<ul>";
        foreach ($changes as $old_value => $count) {
            echo "<li>$count records changed from <strong>'$old_value'</strong> → <strong>'NPC'</strong></li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
}

// Verify the changes
echo "<h3>Verification:</h3>";

$verify_query = "SELECT COUNT(*) as total FROM characters WHERE player_name = 'NPC'";
$verify_result = mysqli_query($conn, $verify_query);

if ($verify_result) {
    $row = mysqli_fetch_assoc($verify_result);
    echo "<p style='color: green;'>✅ Total NPCs with 'NPC': <strong>{$row['total']}</strong></p>";
} else {
    echo "<p style='color: red;'>❌ Could not verify counts</p>";
}

// Also show breakdown
$breakdown_query = "SELECT player_name, COUNT(*) as count FROM characters GROUP BY player_name ORDER BY count DESC";
$breakdown_result = mysqli_query($conn, $breakdown_query);

if ($breakdown_result && mysqli_num_rows($breakdown_result) > 0) {
    echo "<h3>Current player_name Distribution:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Player Name</th><th>Count</th></tr>";
    while ($row = mysqli_fetch_assoc($breakdown_result)) {
        $value = $row['player_name'] ?? 'NULL';
        $count = $row['count'];
        echo "<tr><td>" . htmlspecialchars($value) . "</td><td>{$count}</td></tr>";
    }
    echo "</table>";
}

mysqli_close($conn);

echo "<br><br>";
echo "<div style='padding: 15px; background: #1a0f0f; border: 2px solid #8B0000; border-radius: 5px; margin-top: 20px;'>";
echo "<h3>✅ Normalization Complete!</h3>";
echo "<p>All NPCs now have player_name = 'NPC'</p>";
echo "<p>Visit the NPC Briefing page to see all your NPCs.</p>";
echo "<p><a href='../admin/admin_npc_briefing.php' style='display: inline-block; padding: 10px 20px; background: #8B0000; color: #f5e6d3; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Go to NPC Briefing →</a></p>";
echo "</div>";
?>

