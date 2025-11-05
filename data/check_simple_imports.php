<?php
/**
 * Debug script to check if simple format characters were imported
 */

require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/discipline_functions.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Check Simple Character Imports</title>
    <style>
        body {
            font-family: monospace;
            background: #1a0f0f;
            color: #d4c4b0;
            padding: 20px;
        }
        .error { color: #ff6b6b; }
        .success { color: #51cf66; }
        table {
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #4a3f3f;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #2a1f1f;
        }
    </style>
</head>
<body>
    <h1>🔍 Check Simple Character Imports</h1>
    <pre>

<?php

// Check for our three characters
$characters_to_check = ['Pistol Pete', 'Sasha', 'Leo'];

echo "=================================================================\n";
echo "Checking Characters in Database\n";
echo "=================================================================\n\n";

foreach ($characters_to_check as $char_name) {
    echo "Checking: $char_name\n";
    echo str_repeat("-", 70) . "\n";
    
    $stmt = mysqli_prepare($conn, "SELECT id, character_name, clan, generation FROM characters WHERE character_name = ?");
    mysqli_stmt_bind_param($stmt, "s", $char_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $char = mysqli_fetch_assoc($result);
    
    if (!$char) {
        echo "<span class='error'>❌ Character NOT FOUND in database</span>\n\n";
        continue;
    }
    
    echo "<span class='success'>✅ Character found:</span>\n";
    echo "   ID: {$char['id']}\n";
    echo "   Clan: {$char['clan']}\n";
    echo "   Generation: {$char['generation']}\n\n";
    
    // Check disciplines
    $char_id = $char['id'];
    $disc_stmt = mysqli_prepare($conn, "SELECT discipline_name, level FROM character_disciplines WHERE character_id = ?");
    mysqli_stmt_bind_param($disc_stmt, "i", $char_id);
    mysqli_stmt_execute($disc_stmt);
    $disc_result = mysqli_stmt_get_result($disc_stmt);
    
    $disciplines = [];
    while ($disc = mysqli_fetch_assoc($disc_result)) {
        $disciplines[] = $disc;
    }
    
    if (empty($disciplines)) {
        echo "<span class='error'>❌ NO DISCIPLINES found for this character!</span>\n\n";
    } else {
        echo "<span class='success'>✅ Disciplines found: " . count($disciplines) . "</span>\n";
        foreach ($disciplines as $disc) {
            echo "   - {$disc['discipline_name']} (level {$disc['level']})\n";
        }
        echo "\n";
    }
}

// Show all character_disciplines entries
echo "\n";
echo "=================================================================\n";
echo "All character_disciplines table entries (last 20)\n";
echo "=================================================================\n\n";

$result = mysqli_query($conn, "SELECT character_id, discipline_name, level FROM character_disciplines ORDER BY id DESC LIMIT 20");

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table>\n";
    echo "<tr><th>Character ID</th><th>Discipline</th><th>Level</th></tr>\n";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['character_id']}</td>";
        echo "<td>{$row['discipline_name']}</td>";
        echo "<td>{$row['level']}</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
} else {
    echo "<span class='error'>❌ No disciplines found in character_disciplines table!</span>\n";
}

// Check total count
$count_result = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM character_disciplines");
$count_row = mysqli_fetch_assoc($count_result);
echo "\nTotal disciplines in database: {$count_row['cnt']}\n\n";

?>
    </pre>
    <p><a href="../admin/admin_panel.php" style="color: #d4c4b0;">← Back to Admin Panel</a></p>
</body>
</html>

