<?php
/**
 * Database Migration: Add NPC Briefing Fields
 * Adds agentNotes and actingNotes fields to characters table
 */

require_once __DIR__ . '/../includes/connect.php';

echo "<h2>NPC Briefing Fields Migration</h2>";

// Check if columns already exist
$check_query = "SHOW COLUMNS FROM characters LIKE 'agentNotes'";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) > 0) {
    echo "<p style='color: orange;'>⚠️ agentNotes column already exists. Skipping...</p>";
} else {
    // Add agentNotes column
    $sql1 = "ALTER TABLE characters 
             ADD COLUMN agentNotes TEXT DEFAULT NULL 
             AFTER biography";
    
    if (mysqli_query($conn, $sql1)) {
        echo "<p style='color: green;'>✅ Successfully added agentNotes column</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding agentNotes: " . mysqli_error($conn) . "</p>";
    }
}

// Check if actingNotes column exists
$check_query2 = "SHOW COLUMNS FROM characters LIKE 'actingNotes'";
$result2 = mysqli_query($conn, $check_query2);

if (mysqli_num_rows($result2) > 0) {
    echo "<p style='color: orange;'>⚠️ actingNotes column already exists. Skipping...</p>";
} else {
    // Add actingNotes column
    $sql2 = "ALTER TABLE characters 
             ADD COLUMN actingNotes TEXT DEFAULT NULL 
             AFTER agentNotes";
    
    if (mysqli_query($conn, $sql2)) {
        echo "<p style='color: green;'>✅ Successfully added actingNotes column</p>";
    } else {
        echo "<p style='color: red;'>❌ Error adding actingNotes: " . mysqli_error($conn) . "</p>";
    }
}

// Verify the columns were added
echo "<h3>Verification</h3>";
$verify_query = "SHOW COLUMNS FROM characters WHERE Field IN ('agentNotes', 'actingNotes')";
$verify_result = mysqli_query($conn, $verify_query);

if (mysqli_num_rows($verify_result) == 2) {
    echo "<p style='color: green;'>✅ Both columns verified successfully!</p>";
    echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Default</th></tr>";
    while ($row = mysqli_fetch_assoc($verify_result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Verification failed. Expected 2 columns, found " . mysqli_num_rows($verify_result) . "</p>";
}

mysqli_close($conn);

echo "<br><br><a href='../admin/admin_npc_briefing.php'>Go to NPC Briefing Page</a>";
?>

