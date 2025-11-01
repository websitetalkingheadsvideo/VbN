<?php
/**
 * Check which characters have disciplines and which don't
 */

require_once __DIR__ . '/../includes/connect.php';

echo "<!DOCTYPE html><html><head><title>Character Disciplines Status</title>";
echo "<style>
    body { font-family: monospace; padding: 20px; background: #1a0f0f; color: #f5e6d3; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #8b0000; padding: 8px; text-align: left; }
    th { background: #8b0000; color: #fff; }
    .has-disc { background: #2a4a2a; }
    .no-disc { background: #4a2a2a; }
    .empty { color: #999; font-style: italic; }
</style></head><body>";
echo "<h1>Character Disciplines Status Check</h1>";

// Get all characters
$characters_sql = "SELECT id, character_name, player_name, clan FROM characters ORDER BY id";
$characters_result = mysqli_query($conn, $characters_sql);

if (!$characters_result) {
    echo "Error: " . mysqli_error($conn);
    exit;
}

echo "<table>";
echo "<tr><th>ID</th><th>Character Name</th><th>Player</th><th>Clan</th><th>Has Disciplines?</th><th>Disciplines</th></tr>";

$with_disc = 0;
$without_disc = 0;

while ($char = mysqli_fetch_assoc($characters_result)) {
    $char_id = $char['id'];
    
    // Check for disciplines
    $disc_sql = "SELECT discipline_name, level FROM character_disciplines WHERE character_id = $char_id";
    $disc_result = mysqli_query($conn, $disc_sql);
    
    $disc_count = mysqli_num_rows($disc_result);
    $has_disc = $disc_count > 0;
    
    if ($has_disc) {
        $with_disc++;
    } else {
        $without_disc++;
    }
    
    $disc_list = [];
    if ($disc_result && $disc_count > 0) {
        while ($disc = mysqli_fetch_assoc($disc_result)) {
            $disc_list[] = $disc['discipline_name'] . ' ' . $disc['level'];
        }
    }
    
    $row_class = $has_disc ? 'has-disc' : 'no-disc';
    $disc_text = !empty($disc_list) ? implode(', ', $disc_list) : '<span class="empty">None</span>';
    $status = $has_disc ? '<span style="color: #0f0;">✓ Yes (' . $disc_count . ')</span>' : '<span style="color: #f00;">✗ No</span>';
    
    echo "<tr class='$row_class'>";
    echo "<td>" . $char['id'] . "</td>";
    echo "<td>" . htmlspecialchars($char['character_name']) . "</td>";
    echo "<td>" . htmlspecialchars($char['player_name']) . "</td>";
    echo "<td>" . htmlspecialchars($char['clan']) . "</td>";
    echo "<td>$status</td>";
    echo "<td>$disc_text</td>";
    echo "</tr>";
}

echo "</table>";

echo "<div style='margin-top: 20px; padding: 10px; background: #2a1a1a; border: 1px solid #8b0000;'>";
echo "<h3>Summary</h3>";
echo "<p>Characters WITH disciplines: <strong style='color: #0f0;'>$with_disc</strong></p>";
echo "<p>Characters WITHOUT disciplines: <strong style='color: #f00;'>$without_disc</strong></p>";
echo "</div>";

// Check if there are characters that might have disciplines in JSON files but not in DB
echo "<div style='margin-top: 20px; padding: 10px; background: #2a1a1a; border: 1px solid #8b0000;'>";
echo "<h3>Next Steps</h3>";
echo "<p>If characters are missing disciplines, they may need to be re-imported from JSON files.</p>";
echo "<p>Check the JSON files in <code>reference/Characters/Added to Database/</code> to see if they have discipline data.</p>";
echo "</div>";

echo "</body></html>";
?>

