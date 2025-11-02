<?php
/**
 * Verify Thaumaturgy path powers are in the database
 */
require_once __DIR__ . '/../includes/connect.php';

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><title>Verify Thaumaturgy Powers</title><style>
    body { font-family: monospace; background: #1a0f0f; color: #d4c4b0; padding: 20px; }
    .success { color: #51cf66; }
    .error { color: #ff6b6b; }
    .warning { color: #ffd43b; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #555; padding: 8px; text-align: left; }
    th { background: #2a1a1a; }
</style></head><body>";
echo "<h1>🦇 Thaumaturgy Path Powers Verification</h1>";

if (!$conn) {
    echo "<p class='error'>Database connection failed</p>";
    exit;
}

// Expected paths from add_thaumaturgy_paths.php
// Keys must start at 1 (not 0) to match database power_level
$expected_paths = [
    'Path of Blood' => [1 => 'A Taste for Blood', 2 => 'Blood Rage', 3 => 'Blood of Potency', 4 => 'Theft of Vitae', 5 => 'Cauldron of Blood'],
    'Path of Geomancy' => [1 => 'Hands of Earth', 2 => 'Wooden Tongues', 3 => 'Animate the Unmoving', 4 => 'Spirit of the Land', 5 => 'Earthquake'],
    'Hearth Path' => [1 => 'Warm the Hearth', 2 => 'Enchant the Threshold', 3 => 'Rhyme of Discord', 4 => "Hearth's Protection", 5 => 'Sanctuary'],
    'Path of Warding' => [1 => 'Ward Against Ghouls', 2 => 'Ward Against Spirits', 3 => 'Glyph of Scrying', 4 => 'Ward Against Kindred', 5 => 'Unbreachable Sanctum'],
    'Path of Conjuring' => [1 => "Witch's Sight", 2 => 'Hermetic Sight', 3 => 'Summon the Simple Form', 4 => 'Calling the Shadows', 5 => 'Create Phantasm'],
    'Path of Technomancy' => [1 => 'Ghost in the Machine', 2 => 'Fatal Flaw', 3 => 'System Crash', 4 => 'Electric Discharge', 5 => 'Technological Mastery'],
    'Dehydrate Path (Experimental)' => [1 => 'Dessicate', 2 => 'Thirst', 3 => 'Dry the Well', 4 => 'Dust to Dust', 5 => "Desert's Curse"]
];

echo "<h2>Checking Database...</h2>";

$all_good = true;

foreach ($expected_paths as $path_name => $expected_powers) {
    echo "<h3>{$path_name}</h3>";
    
    // Check if discipline exists
    $disc_query = "SELECT id, name FROM disciplines WHERE name = ?";
    $stmt = mysqli_prepare($conn, $disc_query);
    mysqli_stmt_bind_param($stmt, 's', $path_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $discipline = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$discipline) {
        echo "<p class='error'>❌ Discipline '{$path_name}' NOT FOUND in database</p>";
        $all_good = false;
        continue;
    }
    
    echo "<p class='success'>✅ Discipline '{$path_name}' exists (ID: {$discipline['id']})</p>";
    
    // Check powers
    $power_query = "SELECT power_level, power_name FROM discipline_powers WHERE discipline_id = ? ORDER BY power_level";
    $stmt = mysqli_prepare($conn, $power_query);
    mysqli_stmt_bind_param($stmt, 'i', $discipline['id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $found_powers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $found_powers[$row['power_level']] = $row['power_name'];
    }
    mysqli_stmt_close($stmt);
    
    echo "<table><tr><th>Level</th><th>Expected Power</th><th>Database Power</th><th>Status</th></tr>";
    
    // Loop through expected powers - keys should be 1-5 (power_level starts at 1)
    foreach ($expected_powers as $level_num => $expected_name) {
        $found_name = $found_powers[$level_num] ?? null;
        $match = $found_name && strtolower(trim($found_name)) === strtolower(trim($expected_name));
        
        echo "<tr>";
        echo "<td>{$level_num}</td>";
        echo "<td>{$expected_name}</td>";
        echo "<td>" . ($found_name ?: "<span class='error'>MISSING</span>") . "</td>";
        echo "<td>" . ($match ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . "</td>";
        echo "</tr>";
        
        if (!$match) {
            $all_good = false;
        }
    }
    
    echo "</table>";
    
    if (count($found_powers) < count($expected_powers)) {
        echo "<p class='warning'>⚠️ Found " . count($found_powers) . " powers, expected " . count($expected_powers) . "</p>";
    }
}

echo "<hr>";
if ($all_good) {
    echo "<h2 class='success'>✅ All Thaumaturgy path powers are in the database!</h2>";
} else {
    echo "<h2 class='error'>❌ Some powers are missing. Run <code>database/add_thaumaturgy_paths.php</code> to populate them.</h2>";
    echo "<p><a href='../database/add_thaumaturgy_paths.php' style='color: #51cf66;'>Run Population Script</a></p>";
}

echo "</body></html>";
?>

