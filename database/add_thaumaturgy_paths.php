<?php
/**
 * Add Thaumaturgy Paths as separate disciplines
 * 
 * Extracts all unique Thaumaturgy path names from character_disciplines
 * and adds them to the disciplines table, then populates their powers
 * from the reference character files.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/connect.php';

// Check if running via web browser or CLI
$is_web = php_sapi_name() !== 'cli';

if ($is_web) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>Add Thaumaturgy Paths</title><style>
        body { font-family: monospace; background: #1a0f0f; color: #d4c4b0; padding: 20px; }
        pre { white-space: pre-wrap; word-wrap: break-word; }
        .error { color: #ff6b6b; }
        .success { color: #51cf66; }
        .warning { color: #ffd43b; }
    </style></head><body>";
    echo "<h1>🦇 Adding Thaumaturgy Paths</h1><pre>";
    flush();
}

function log_message($message, $type = 'info') {
    global $is_web;
    $prefix = '';
    switch ($type) {
        case 'error':
            $prefix = $is_web ? '<span class="error">❌</span> ' : '❌ ';
            break;
        case 'success':
            $prefix = $is_web ? '<span class="success">✅</span> ' : '✅ ';
            break;
        case 'warning':
            $prefix = $is_web ? '<span class="warning">⚠️</span> ' : '⚠️ ';
            break;
        default:
            $prefix = $is_web ? '<span>ℹ️</span> ' : 'ℹ️ ';
    }
    echo $prefix . $message . "\n";
    if ($is_web) flush();
}

try {
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    log_message("Step 1: Finding all Thaumaturgy paths in character_disciplines...", 'info');
    
    // Find all unique discipline names that are paths
    $path_query = "SELECT DISTINCT discipline_name 
                   FROM character_disciplines 
                   WHERE discipline_name LIKE '%Path%' 
                   OR discipline_name LIKE '%Thaumaturgy%'
                   ORDER BY discipline_name";
    
    $result = mysqli_query($conn, $path_query);
    if (!$result) {
        throw new Exception("Failed to query paths: " . mysqli_error($conn));
    }
    
    $found_paths = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $found_paths[] = $row['discipline_name'];
    }
    
    log_message("Found " . count($found_paths) . " unique path/discipline names", 'success');
    
    // Thaumaturgy path powers data from reference files
    // Note: Using double quotes for strings with apostrophes to avoid escaping issues
    $thaumaturgy_paths = [
        'Path of Blood' => [
            1 => ['name' => 'A Taste for Blood', 'desc' => 'Sense and identify blood sources and their properties'],
            2 => ['name' => 'Blood Rage', 'desc' => 'Enrage the blood of others, causing frenzy'],
            3 => ['name' => 'Blood of Potency', 'desc' => 'Temporarily increase generation/blood potency'],
            4 => ['name' => 'Theft of Vitae', 'desc' => 'Steal blood from others at a distance'],
            5 => ['name' => 'Cauldron of Blood', 'desc' => 'Create a pool of blood that can be consumed']
        ],
        'Path of Geomancy' => [
            1 => ['name' => 'Hands of Earth', 'desc' => 'Manipulate earth and stone'],
            2 => ['name' => 'Wooden Tongues', 'desc' => 'Communicate with plants and trees'],
            3 => ['name' => 'Animate the Unmoving', 'desc' => 'Bring inanimate objects to life'],
            4 => ['name' => 'Spirit of the Land', 'desc' => 'Bind and control nature spirits'],
            5 => ['name' => 'Earthquake', 'desc' => 'Cause seismic disturbances']
        ],
        'Hearth Path' => [
            1 => ['name' => 'Warm the Hearth', 'desc' => 'Create warmth and light in an area'],
            2 => ['name' => 'Enchant the Threshold', 'desc' => 'Protect a dwelling from harm'],
            3 => ['name' => 'Rhyme of Discord', 'desc' => 'Create disharmony and conflict'],
            4 => ['name' => "Hearth's Protection", 'desc' => 'Shield a location from supernatural threats'],
            5 => ['name' => 'Sanctuary', 'desc' => 'Make a place a true sanctuary']
        ],
        'Path of Warding' => [
            1 => ['name' => 'Ward Against Ghouls', 'desc' => 'Create protective barrier against ghouls'],
            2 => ['name' => 'Ward Against Spirits', 'desc' => 'Protect against spiritual entities'],
            3 => ['name' => 'Glyph of Scrying', 'desc' => 'Create a scrying window'],
            4 => ['name' => 'Ward Against Kindred', 'desc' => 'Protect against other vampires'],
            5 => ['name' => 'Unbreachable Sanctum', 'desc' => 'Create an unbreakable ward']
        ],
        'Path of Conjuring' => [
            1 => ['name' => "Witch's Sight", 'desc' => 'See through illusions and glamour'],
            2 => ['name' => 'Hermetic Sight', 'desc' => 'Perceive magical energies'],
            3 => ['name' => 'Summon the Simple Form', 'desc' => 'Summon small objects'],
            4 => ['name' => 'Calling the Shadows', 'desc' => 'Summon darkness and shadows'],
            5 => ['name' => 'Create Phantasm', 'desc' => 'Create temporary illusions']
        ],
        'Path of Technomancy' => [
            1 => ['name' => 'Ghost in the Machine', 'desc' => 'Sense and communicate with electronic devices'],
            2 => ['name' => 'Fatal Flaw', 'desc' => 'Cause technology to fail'],
            3 => ['name' => 'System Crash', 'desc' => 'Overload electronic systems'],
            4 => ['name' => 'Electric Discharge', 'desc' => 'Release electrical energy'],
            5 => ['name' => 'Technological Mastery', 'desc' => 'Complete control over technology']
        ],
        'Dehydrate Path (Experimental)' => [
            1 => ['name' => 'Dessicate', 'desc' => 'Remove moisture from objects by touch'],
            2 => ['name' => 'Thirst', 'desc' => 'Cause intense dehydration at range'],
            3 => ['name' => 'Dry the Well', 'desc' => 'Remove all moisture from an area'],
            4 => ['name' => 'Dust to Dust', 'desc' => 'Completely dehydrate a living being'],
            5 => ['name' => "Desert's Curse", 'desc' => 'Create area of permanent dryness']
        ]
    ];
    
    // Check if category column exists
    $column_check = mysqli_query($conn, "SHOW COLUMNS FROM disciplines LIKE 'category'");
    if (!$column_check) {
        throw new Exception("Failed to check for category column: " . mysqli_error($conn));
    }
    $has_category = mysqli_num_rows($column_check) > 0;
    
    log_message("Step 2: Adding paths to disciplines table...", 'info');
    
    $insert_stmt = $has_category 
        ? mysqli_prepare($conn, "INSERT IGNORE INTO disciplines (name, category, description) VALUES (?, ?, ?)")
        : mysqli_prepare($conn, "INSERT IGNORE INTO disciplines (name, description) VALUES (?, ?)");
    
    if (!$insert_stmt) {
        throw new Exception("Failed to prepare discipline insert: " . mysqli_error($conn));
    }
    
    // Combine found paths with paths from powers array to ensure all are added
    $all_path_names = array_unique(array_merge($found_paths, array_keys($thaumaturgy_paths)));
    
    $paths_added = 0;
    foreach ($all_path_names as $path_name) {
        // Skip if it's just "Thaumaturgy" (we want paths, not the base discipline)
        if (strtolower($path_name) === 'thaumaturgy') {
            continue;
        }
        
        $description = "A Thaumaturgy path - " . $path_name;
        $category = 'BloodSorcery';
        
        if ($has_category) {
            mysqli_stmt_bind_param($insert_stmt, 'sss', $path_name, $category, $description);
        } else {
            mysqli_stmt_bind_param($insert_stmt, 'ss', $path_name, $description);
        }
        
        if (mysqli_stmt_execute($insert_stmt)) {
            if (mysqli_affected_rows($conn) > 0) {
                $paths_added++;
                log_message("Added path: {$path_name}", 'success');
            }
        } else {
            log_message("Failed to insert path {$path_name}: " . mysqli_stmt_error($insert_stmt), 'warning');
        }
    }
    
    if ($paths_added > 0) {
        log_message("Added {$paths_added} new paths to disciplines table", 'success');
    } else {
        log_message("All paths already exist in disciplines table", 'info');
    }
    
    mysqli_stmt_close($insert_stmt);
    
    // Step 3: Populate powers for known paths
    log_message("Step 3: Populating powers for known Thaumaturgy paths...", 'info');
    
    // Get discipline IDs (refresh after inserts to include new paths)
    $disc_query = "SELECT id, name FROM disciplines WHERE name LIKE '%Path%' OR name = 'Thaumaturgy'";
    $disc_result = mysqli_query($conn, $disc_query);
    if (!$disc_result) {
        throw new Exception("Failed to query disciplines: " . mysqli_error($conn));
    }
    
    $discipline_ids = [];
    while ($row = mysqli_fetch_assoc($disc_result)) {
        $discipline_ids[$row['name']] = $row['id'];
    }
    
    log_message("Found " . count($discipline_ids) . " path disciplines in database", 'info');
    
    $power_insert = mysqli_prepare($conn,
        "INSERT INTO discipline_powers (discipline_id, power_level, power_name, description, prerequisites)
         VALUES (?, ?, ?, ?, NULL)
         ON DUPLICATE KEY UPDATE
         power_name = VALUES(power_name),
         description = VALUES(description)");
    
    if (!$power_insert) {
        throw new Exception("Failed to prepare power insert: " . mysqli_error($conn));
    }
    
    $powers_added = 0;
    $powers_updated = 0;
    
    foreach ($thaumaturgy_paths as $path_name => $powers) {
        if (!isset($discipline_ids[$path_name])) {
            log_message("Path '{$path_name}' not found in disciplines table, skipping", 'warning');
            continue;
        }
        
        $discipline_id = $discipline_ids[$path_name];
        
        foreach ($powers as $level => $power_data) {
            // Try to insert/update (ON DUPLICATE KEY will handle it)
            mysqli_stmt_bind_param($power_insert, 'iiss',
                $discipline_id,
                $level,
                $power_data['name'],
                $power_data['desc']
            );
            
            if (mysqli_stmt_execute($power_insert)) {
                // Check affected rows to determine if inserted or updated
                $affected = mysqli_affected_rows($conn);
                if ($affected == 2) { // ON DUPLICATE KEY UPDATE affects 2 rows (old + new)
                    $powers_updated++;
                } elseif ($affected == 1) { // New insert
                    $powers_added++;
                }
            } else {
                log_message("Failed to insert power for {$path_name} level {$level}: " . mysqli_stmt_error($power_insert), 'warning');
            }
        }
        
        log_message("Processed {$path_name} (5 powers)", 'success');
    }
    
    mysqli_stmt_close($power_insert);
    
    log_message("Population complete!", 'success');
    log_message("  Powers inserted: {$powers_added}", 'success');
    log_message("  Powers updated: {$powers_updated}", 'success');
    log_message("", 'info');
    log_message("Note: Some paths (like 'Dehydrate Path (Experimental)') may have custom powers", 'info');
    log_message("that need to be added manually or extracted from character data.", 'info');
    
} catch (Exception $e) {
    log_message("Operation failed: " . $e->getMessage(), 'error');
    log_message("Stack trace: " . $e->getTraceAsString(), 'error');
    if ($is_web) {
        echo "</pre>";
        echo "<p style='margin-top: 20px; color: #ff6b6b;'><strong>Error occurred!</strong></p>";
        echo "<pre style='background: #2a1a1a; padding: 10px; border: 1px solid #ff6b6b;'>";
        echo htmlspecialchars($e->getMessage());
        echo "\n\n" . htmlspecialchars($e->getTraceAsString());
        echo "</pre>";
    }
    exit(1);
}

if ($is_web) {
    echo "</pre>";
    echo "<p style='margin-top: 20px;'><strong>Operation complete!</strong></p>";
    echo "</body></html>";
}

mysqli_close($conn);
?>

