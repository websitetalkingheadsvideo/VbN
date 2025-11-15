<?php
/**
 * Populate Archetypes Table
 * 
 * Merges all unique nature and demeanor values from the characters table
 * and populates the archetypes table with auto-generated descriptions
 */

require_once __DIR__ . '/../includes/connect.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Populate Archetypes Table</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            padding: 20px; 
            background: #1a0f0f; 
            color: #d4c4b0; 
        }
        .success { 
            color: #0d7a4a; 
            padding: 10px; 
            background: rgba(13, 122, 74, 0.2); 
            border: 1px solid #0d7a4a; 
            margin: 10px 0; 
        }
        .error { 
            color: #8B0000; 
            padding: 10px; 
            background: rgba(139, 0, 0, 0.2); 
            border: 1px solid #8B0000; 
            margin: 10px 0; 
        }
        .info { 
            color: #b8a090; 
            padding: 10px; 
            background: rgba(184, 160, 144, 0.2); 
            border: 1px solid #b8a090; 
            margin: 10px 0; 
        }
        .warning {
            color: #ffa500;
            padding: 10px;
            background: rgba(255, 165, 0, 0.2);
            border: 1px solid #ffa500;
            margin: 10px 0;
        }
        h2 { color: #d4c4b0; margin-top: 30px; }
        ul { margin: 10px 0; padding-left: 30px; }
        li { margin: 5px 0; }
        code { 
            background: rgba(0, 0, 0, 0.3); 
            padding: 2px 6px; 
            border-radius: 3px; 
            font-family: monospace;
        }
        .description-preview {
            font-style: italic;
            color: #b8a090;
            margin-left: 20px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <h1>📝 Populate Archetypes Table</h1>

<?php

if (!$conn) {
    echo '<div class="error">❌ Database connection failed</div>';
    exit;
}

/**
 * Generate a basic description for an archetype
 * Format: Dictionary definition + World of Darkness note
 */
function generateArchetypeDescription($name) {
    // Basic dictionary-style definitions
    $definitions = [
        'Architect' => 'One who designs and builds structures, systems, or organizations.',
        'Autist' => 'One who is deeply focused on a specific interest or pursuit, often to the exclusion of social interaction.',
        'Bon Vivant' => 'A person who enjoys a sociable and luxurious lifestyle.',
        'Bravo' => 'A bold, aggressive person who acts with confidence and bravado.',
        'Caregiver' => 'One who provides care, support, and protection to others.',
        'Capitalist' => 'One who focuses on acquiring wealth, resources, and economic power.',
        'Competitor' => 'One who strives to win and excel in contests or challenges.',
        'Conformist' => 'One who follows established rules, norms, and social expectations.',
        'Conniver' => 'One who schemes and plots to achieve goals through manipulation.',
        'Critic' => 'One who judges and evaluates, often pointing out flaws or shortcomings.',
        'Curmudgeon' => 'A bad-tempered, often elderly person who is easily annoyed.',
        'Deviant' => 'One who deviates from accepted norms or standards.',
        'Director' => 'One who guides, controls, or manages others toward a goal.',
        'Fanatic' => 'One who is extremely enthusiastic and devoted to a cause or belief.',
        'Gallant' => 'A brave, chivalrous person who acts with honor and courage.',
        'Judge' => 'One who makes decisions and passes judgment based on principles of justice.',
        'Loner' => 'One who prefers to be alone and avoids social interaction.',
        'Martyr' => 'One who sacrifices themselves for a cause or principle.',
        'Masochist' => 'One who derives pleasure from their own pain or humiliation.',
        'Monster' => 'One who acts without regard for morality or the suffering of others.',
        'Pedagogue' => 'A teacher or educator who instructs and guides others.',
        'Penitent' => 'One who feels remorse and seeks to atone for wrongdoing.',
        'Perfectionist' => 'One who demands the highest standards and refuses to accept anything less.',
        'Rebel' => 'One who resists authority and established systems.',
        'Rogue' => 'A dishonest or unprincipled person who operates outside the law.',
        'Survivor' => 'One who endures hardship and adapts to overcome challenges.',
        'Thrill-Seeker' => 'One who actively pursues excitement, danger, and intense experiences.',
        'Traditionalist' => 'One who adheres to established customs and resists change.',
        'Visionary' => 'One who has original ideas and can see future possibilities.',
    ];
    
    $definition = $definitions[$name] ?? 'A personality archetype representing a specific behavioral pattern and worldview.';
    
    // Add World of Darkness context
    $wod_note = ' In the World of Darkness, this archetype shapes how a character perceives their actions and maintains their humanity.';
    
    return $definition . $wod_note;
}

try {
    // Step 1: Check if archetypes table exists
    echo '<h2>Step 1: Checking Table</h2>';
    
    $check_table = "SHOW TABLES LIKE 'archetypes'";
    $result = mysqli_query($conn, $check_table);
    $table_exists = $result && mysqli_num_rows($result) > 0;
    
    if (!$table_exists) {
        echo '<div class="error">❌ Archetypes table does not exist. Please run <code>database/create_archetypes_table.php</code> first.</div>';
        exit;
    }
    
    echo '<div class="success">✅ Archetypes table exists</div>';
    
    // Step 2: Get all unique nature values
    echo '<h2>Step 2: Collecting Nature Values</h2>';
    
    $nature_query = "SELECT DISTINCT nature 
                     FROM characters 
                     WHERE nature IS NOT NULL 
                     AND nature != '' 
                     ORDER BY nature ASC";
    
    $nature_result = mysqli_query($conn, $nature_query);
    if (!$nature_result) {
        throw new Exception("Failed to query nature values: " . mysqli_error($conn));
    }
    
    $nature_values = [];
    while ($row = mysqli_fetch_assoc($nature_result)) {
        $nature_values[] = trim($row['nature']);
    }
    mysqli_free_result($nature_result);
    
    echo '<div class="info">ℹ️ Found ' . count($nature_values) . ' unique nature values</div>';
    
    // Step 3: Get all unique demeanor values
    echo '<h2>Step 3: Collecting Demeanor Values</h2>';
    
    $demeanor_query = "SELECT DISTINCT demeanor 
                      FROM characters 
                      WHERE demeanor IS NOT NULL 
                      AND demeanor != '' 
                      ORDER BY demeanor ASC";
    
    $demeanor_result = mysqli_query($conn, $demeanor_query);
    if (!$demeanor_result) {
        throw new Exception("Failed to query demeanor values: " . mysqli_error($conn));
    }
    
    $demeanor_values = [];
    while ($row = mysqli_fetch_assoc($demeanor_result)) {
        $demeanor_values[] = trim($row['demeanor']);
    }
    mysqli_free_result($demeanor_result);
    
    echo '<div class="info">ℹ️ Found ' . count($demeanor_values) . ' unique demeanor values</div>';
    
    // Step 4: Merge and deduplicate
    echo '<h2>Step 4: Merging Values</h2>';
    
    $all_archetypes = array_unique(array_merge($nature_values, $demeanor_values));
    sort($all_archetypes);
    
    echo '<div class="success">✅ Merged to ' . count($all_archetypes) . ' unique archetypes</div>';
    
    // Step 5: Insert into archetypes table
    echo '<h2>Step 5: Populating Archetypes Table</h2>';
    
    $insert_stmt = mysqli_prepare($conn, 
        "INSERT INTO archetypes (name, description) 
         VALUES (?, ?) 
         ON DUPLICATE KEY UPDATE description = VALUES(description)"
    );
    
    if (!$insert_stmt) {
        throw new Exception("Failed to prepare insert statement: " . mysqli_error($conn));
    }
    
    $inserted_count = 0;
    $updated_count = 0;
    $skipped_count = 0;
    
    foreach ($all_archetypes as $archetype) {
        $description = generateArchetypeDescription($archetype);
        
        mysqli_stmt_bind_param($insert_stmt, "ss", $archetype, $description);
        
        // Check if archetype already exists
        $check_existing = mysqli_query($conn, "SELECT id FROM archetypes WHERE name = '" . mysqli_real_escape_string($conn, $archetype) . "'");
        $exists = $check_existing && mysqli_num_rows($check_existing) > 0;
        if ($check_existing) {
            mysqli_free_result($check_existing);
        }
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $affected = mysqli_affected_rows($conn);
            if ($affected > 0) {
                // ON DUPLICATE KEY UPDATE returns 2 for updates, 1 for inserts
                if ($affected == 2 || $exists) {
                    $updated_count++;
                    echo '<div class="info">🔄 Updated: <code>' . htmlspecialchars($archetype) . '</code></div>';
                } else {
                    $inserted_count++;
                    echo '<div class="success">✅ Inserted: <code>' . htmlspecialchars($archetype) . '</code></div>';
                }
            } else {
                $skipped_count++;
            }
        } else {
            echo '<div class="error">❌ Failed to insert <code>' . htmlspecialchars($archetype) . '</code>: ' . mysqli_stmt_error($insert_stmt) . '</div>';
        }
    }
    
    mysqli_stmt_close($insert_stmt);
    
    // Step 6: Summary
    echo '<h2>Summary</h2>';
    echo '<div class="success">✅ Inserted: ' . $inserted_count . ' archetype(s)</div>';
    if ($updated_count > 0) {
        echo '<div class="info">🔄 Updated: ' . $updated_count . ' archetype(s)</div>';
    }
    if ($skipped_count > 0) {
        echo '<div class="info">ℹ️ Skipped: ' . $skipped_count . ' archetype(s) (already exist with same description)</div>';
    }
    echo '<div class="success"><strong>✅ Archetypes table populated!</strong></div>';
    echo '<div class="info">📝 You can now edit descriptions in the database as needed.</div>';
    echo '<div class="info">🔄 Next: Run <code>database/update_dropdowns_to_use_archetypes.php</code> to update the form dropdowns</div>';
    
} catch (Exception $e) {
    echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>

</body>
</html>

