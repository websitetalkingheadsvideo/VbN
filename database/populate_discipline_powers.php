<?php
/**
 * Populate discipline_powers table with all discipline powers
 * 
 * Reads from reference/mechanics/Disiplines.MD and inserts all powers
 * for each discipline (levels 1-5).
 */

require_once __DIR__ . '/../includes/connect.php';

// Check if running via web browser or CLI
$is_web = php_sapi_name() !== 'cli';

if ($is_web) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>Populate Discipline Powers</title><style>
        body { font-family: monospace; background: #1a0f0f; color: #d4c4b0; padding: 20px; }
        pre { white-space: pre-wrap; word-wrap: break-word; }
        .error { color: #ff6b6b; }
        .success { color: #51cf66; }
        .warning { color: #ffd43b; }
    </style></head><body>";
    echo "<h1>🦇 Populating Discipline Powers</h1><pre>";
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

// Discipline powers data from reference file
$discipline_powers = [
    'Animalism' => [
        1 => ['name' => 'Sense the Beast', 'desc' => 'Sense the presence of animals and the Beast within vampires'],
        2 => ['name' => 'Feral Whispers', 'desc' => 'Communicate with and command animals'],
        3 => ['name' => 'Quell the Beast', 'desc' => 'Calm the Beast in yourself or others'],
        4 => ['name' => 'Beckoning', 'desc' => 'Summon animals from afar'],
        5 => ['name' => 'Animal Control', 'desc' => 'Complete mental control over animals']
    ],
    'Auspex' => [
        1 => ['name' => 'Aura Perception', 'desc' => 'See the emotional and spiritual aura of others'],
        2 => ['name' => 'Telepathy', 'desc' => 'Read surface thoughts and communicate mentally'],
        3 => ['name' => 'Psychometry', 'desc' => 'Read the history of objects through touch'],
        4 => ['name' => 'Premonition', 'desc' => 'Sense future events and danger'],
        5 => ['name' => 'Sense the Unseen', 'desc' => 'Detect spirits, wraiths, and other invisible entities']
    ],
    'Celerity' => [
        1 => ['name' => 'Quickness', 'desc' => 'Move and react faster than normal'],
        2 => ['name' => 'Sprint', 'desc' => 'Run at incredible speeds for short bursts'],
        3 => ['name' => 'Enhanced Reflexes', 'desc' => 'React instantly to threats and opportunities'],
        4 => ['name' => 'Blur', 'desc' => 'Move so fast you appear as a blur'],
        5 => ['name' => 'Accelerated Movement', 'desc' => 'Move faster than the eye can follow']
    ],
    'Dominate' => [
        1 => ['name' => 'Command', 'desc' => 'Issue simple one-word commands that must be obeyed'],
        2 => ['name' => 'Mesmerize', 'desc' => 'Hypnotize others into a trance'],
        3 => ['name' => 'Memory Alteration', 'desc' => 'Modify or erase memories'],
        4 => ['name' => 'Suggestion', 'desc' => 'Plant compelling suggestions in the mind'],
        5 => ['name' => 'Mental Domination', 'desc' => 'Complete control over another\'s actions']
    ],
    'Fortitude' => [
        1 => ['name' => 'Resistance', 'desc' => 'Resist damage and environmental hazards'],
        2 => ['name' => 'Endurance', 'desc' => 'Withstand great physical trauma'],
        3 => ['name' => 'Pain Tolerance', 'desc' => 'Ignore pain and continue functioning'],
        4 => ['name' => 'Damage Reduction', 'desc' => 'Reduce incoming damage significantly'],
        5 => ['name' => 'Supernatural Stamina', 'desc' => 'Possess incredible resistance to all harm']
    ],
    'Obfuscate' => [
        1 => ['name' => 'Cloak of Shadows', 'desc' => 'Hide in shadows and darkness'],
        2 => ['name' => 'Vanish', 'desc' => 'Become completely invisible'],
        3 => ['name' => 'Mask of a Thousand Faces', 'desc' => 'Disguise your appearance as anyone'],
        4 => ['name' => 'Silent Movement', 'desc' => 'Move without making any sound'],
        5 => ['name' => 'Unseen Presence', 'desc' => 'Become undetectable by all senses']
    ],
    'Potence' => [
        1 => ['name' => 'Prowess', 'desc' => 'Exert superhuman strength'],
        2 => ['name' => 'Shove', 'desc' => 'Send opponents flying with brute force'],
        3 => ['name' => 'Knockdown', 'desc' => 'Strike with enough force to knock down any target'],
        4 => ['name' => 'Crushing Blow', 'desc' => 'Deliver devastating attacks'],
        5 => ['name' => 'Leap', 'desc' => 'Jump incredible distances']
    ],
    'Presence' => [
        1 => ['name' => 'Awe', 'desc' => 'Command attention and respect from others'],
        2 => ['name' => 'Dread Gaze', 'desc' => 'Instill fear with a single look'],
        3 => ['name' => 'Entrancement', 'desc' => 'Charm others into compliance'],
        4 => ['name' => 'Majesty', 'desc' => 'Project an aura of undeniable authority'],
        5 => ['name' => 'Inspire', 'desc' => 'Rally others to your cause with overwhelming charisma']
    ],
    'Protean' => [
        1 => ['name' => 'Shape of the Beast', 'desc' => 'Grow fangs and claws'],
        2 => ['name' => 'Claws', 'desc' => 'Extend deadly claws for combat'],
        3 => ['name' => 'Feral Leap', 'desc' => 'Leap great distances like a predator'],
        4 => ['name' => 'Flight (Bat Form)', 'desc' => 'Transform into a bat and fly'],
        5 => ['name' => 'Natural Armor', 'desc' => 'Harden skin to resist damage']
    ],
    'Thaumaturgy' => [
        1 => ['name' => 'Lure of Flames', 'desc' => 'Control and create fire'],
        2 => ['name' => 'Shield of Thorns', 'desc' => 'Create defensive barriers'],
        3 => ['name' => 'Rite of Blood', 'desc' => 'Ritual to enhance blood potency'],
        4 => ['name' => 'Circle of Protection', 'desc' => 'Create protective wards'],
        5 => ['name' => 'Blood Bond', 'desc' => 'Create powerful blood bonds']
    ],
    'Necromancy' => [
        1 => ['name' => 'Sense Death', 'desc' => 'Detect the presence of death and the dead'],
        2 => ['name' => 'Command Dead', 'desc' => 'Command and control ghosts and spirits'],
        3 => ['name' => 'Drain Life', 'desc' => 'Drain life force from the living'],
        4 => ['name' => 'Haunt', 'desc' => 'Haunt locations and terrorize the living'],
        5 => ['name' => 'Animate Corpse', 'desc' => 'Animate and control dead bodies']
    ],
    'Obtenebration' => [
        1 => ['name' => 'Shadow Cloak', 'desc' => 'Wrap yourself in protective shadows'],
        2 => ['name' => 'Dark Tendrils', 'desc' => 'Create shadowy tendrils to attack'],
        3 => ['name' => 'Shroud of Night', 'desc' => 'Summon an area of absolute darkness'],
        4 => ['name' => 'Shadow Walk', 'desc' => 'Travel through shadows instantly'],
        5 => ['name' => 'Nightmarish Strike', 'desc' => 'Attack through shadows from a distance']
    ],
    'Chimerstry' => [
        1 => ['name' => 'Minor Illusion', 'desc' => 'Create small, simple illusions'],
        2 => ['name' => 'Disguise', 'desc' => 'Create convincing illusions of other people'],
        3 => ['name' => 'Confusion', 'desc' => 'Befuddle and confuse opponents'],
        4 => ['name' => 'Hallucinatory Image', 'desc' => 'Create complex, realistic illusions'],
        5 => ['name' => 'Invisibility Illusion', 'desc' => 'Make yourself appear invisible']
    ],
    'Dementation' => [
        1 => ['name' => 'Awe of Madness', 'desc' => 'Project an aura of unsettling strangeness'],
        2 => ['name' => 'Fear Projection', 'desc' => 'Project intense fear into others'],
        3 => ['name' => 'Confusion', 'desc' => 'Induce confusion and disorientation'],
        4 => ['name' => 'Irrational Fear', 'desc' => 'Instill overwhelming, irrational fears'],
        5 => ['name' => 'Frenzy Inducement', 'desc' => 'Force others into frenzy']
    ],
    'Quietus' => [
        1 => ['name' => 'Poison Glands', 'desc' => 'Create and secrete deadly poisons'],
        2 => ['name' => 'Silent Kill', 'desc' => 'Kill silently and efficiently'],
        3 => ['name' => 'Respiratory Poison', 'desc' => 'Create airborne poisons'],
        4 => ['name' => 'Hemorrhage', 'desc' => 'Cause victims to bleed to death'],
        5 => ['name' => 'Lethal Strike', 'desc' => 'Deliver instantly lethal attacks']
    ],
    'Vicissitude' => [
        1 => ['name' => 'Fleshcraft', 'desc' => 'Reshape flesh and bone'],
        2 => ['name' => 'Alter Form', 'desc' => 'Change your physical appearance'],
        3 => ['name' => 'Skin Hardening', 'desc' => 'Harden skin into armor'],
        4 => ['name' => 'Stretch Limb', 'desc' => 'Extend limbs to impossible lengths'],
        5 => ['name' => 'Weaponize Flesh', 'desc' => 'Turn body parts into deadly weapons']
    ],
    'Serpentis' => [
        1 => ['name' => 'Hypnotic Gaze', 'desc' => 'Hypnotize others with your eyes'],
        2 => ['name' => 'Venomous Bite', 'desc' => 'Deliver poisonous bites'],
        3 => ['name' => 'Serpent\'s Strike', 'desc' => 'Attack with lightning speed'],
        4 => ['name' => 'Mesmerize', 'desc' => 'Enthrall others completely'],
        5 => ['name' => 'Shape Serpent', 'desc' => 'Transform into a large serpent']
    ],
    'Koldunic Sorcery' => [
        1 => ['name' => 'Elemental Bolt', 'desc' => 'Hurl bolts of elemental energy'],
        2 => ['name' => 'Minor Ward', 'desc' => 'Create simple protective wards'],
        3 => ['name' => 'Fire Blast', 'desc' => 'Summon blasts of fire'],
        4 => ['name' => 'Ice Shard', 'desc' => 'Create and throw shards of ice'],
        5 => ['name' => 'Earth Spike', 'desc' => 'Manipulate earth to create spikes']
    ],
    'Daimoinon' => [
        1 => ['name' => 'Fear Aura', 'desc' => 'Project an aura of fear'],
        2 => ['name' => 'Infernal Grasp', 'desc' => 'Summon infernal powers'],
        3 => ['name' => 'Summon Demon', 'desc' => 'Summon demonic entities'],
        4 => ['name' => 'Curse', 'desc' => 'Place powerful curses on others'],
        5 => ['name' => 'Dark Inspiration', 'desc' => 'Gain dark knowledge from infernal sources']
    ],
    'Melpominee' => [
        1 => ['name' => 'Captivating Song', 'desc' => 'Enchant others with your voice'],
        2 => ['name' => 'Charm', 'desc' => 'Charm others through music'],
        3 => ['name' => 'Enthrall Audience', 'desc' => 'Completely captivate an audience'],
        4 => ['name' => 'Inspire Emotion', 'desc' => 'Evoke powerful emotions through music'],
        5 => ['name' => 'Hypnotic Performance', 'desc' => 'Place entire crowds under your control']
    ],
    'Valeren' => [
        1 => ['name' => 'Healing Touch', 'desc' => 'Heal others through touch'],
        2 => ['name' => 'Restore Vitality', 'desc' => 'Restore life force and energy'],
        3 => ['name' => 'Detox', 'desc' => 'Remove poisons and toxins'],
        4 => ['name' => 'Protective Ward', 'desc' => 'Create protective wards'],
        5 => ['name' => 'Ritual Aid', 'desc' => 'Enhance ritual magic']
    ],
    'Obeah' => [
        1 => ['name' => 'Healing Touch', 'desc' => 'Heal others through touch'],
        2 => ['name' => 'Restore Vitality', 'desc' => 'Restore life force and energy'],
        3 => ['name' => 'Detox', 'desc' => 'Remove poisons and toxins'],
        4 => ['name' => 'Protective Ward', 'desc' => 'Create protective wards'],
        5 => ['name' => 'Ritual Aid', 'desc' => 'Enhance ritual magic']
    ],
    'Mortis' => [
        1 => ['name' => 'Sense Death', 'desc' => 'Detect the presence of death'],
        2 => ['name' => 'Drain Life', 'desc' => 'Drain life force from the living'],
        3 => ['name' => 'Haunting Presence', 'desc' => 'Project a presence of death'],
        4 => ['name' => 'Wither', 'desc' => 'Cause decay and withering'],
        5 => ['name' => 'Deathly Chill', 'desc' => 'Create an aura of cold death']
    ]
];

try {
    log_message("Starting discipline powers population...", 'info');
    
    // Define all disciplines that should exist
    $all_disciplines = [
        // Clan Disciplines
        ['Animalism', 'Clan', 'The ability to communicate with and control animals'],
        ['Auspex', 'Clan', 'The power of supernatural perception and awareness'],
        ['Celerity', 'Clan', 'The ability to move and react at superhuman speeds'],
        ['Dominate', 'Clan', 'The power to control the minds of others'],
        ['Fortitude', 'Clan', 'The ability to resist damage and endure hardship'],
        ['Obfuscate', 'Clan', 'The power to hide from sight and become invisible'],
        ['Potence', 'Clan', 'The ability to possess superhuman physical strength'],
        ['Presence', 'Clan', 'The power to influence and charm others through sheer presence'],
        ['Protean', 'Clan', 'The ability to change form and shape'],
        
        // Blood Sorcery Disciplines
        ['Thaumaturgy', 'BloodSorcery', 'The art of blood magic and ritual'],
        ['Necromancy', 'BloodSorcery', 'The power to communicate with and control the dead'],
        ['Koldunic Sorcery', 'BloodSorcery', 'Elemental magic tied to specific locations'],
        
        // Advanced Disciplines
        ['Obtenebration', 'Advanced', 'The power to control and manipulate shadows'],
        ['Chimerstry', 'Advanced', 'The ability to create and maintain illusions'],
        ['Dementation', 'Advanced', 'The power to drive others to madness'],
        ['Quietus', 'Advanced', 'The art of silent assassination and poison'],
        ['Vicissitude', 'Advanced', 'The ability to reshape flesh and bone'],
        ['Serpentis', 'Advanced', 'The power of the serpent and hypnotic abilities'],
        ['Daimoinon', 'Advanced', 'The power to summon and control infernal entities'],
        ['Melpominee', 'Advanced', 'The power of song and musical influence'],
        ['Valeren', 'Advanced', 'The power of healing and spiritual guidance'],
        ['Mortis', 'Advanced', 'The power over death and decay']
    ];
    
    // Check if category column exists in disciplines table
    $column_check = mysqli_query($conn, "SHOW COLUMNS FROM disciplines LIKE 'category'");
    $has_category = mysqli_num_rows($column_check) > 0;
    
    // Ensure all disciplines exist in database
    log_message("Ensuring all disciplines exist in database...", 'info');
    
    if ($has_category) {
        log_message("Category column exists, inserting with category...", 'info');
        $insert_stmt = mysqli_prepare($conn, "INSERT IGNORE INTO disciplines (name, category, description) VALUES (?, ?, ?)");
        if (!$insert_stmt) {
            throw new Exception("Failed to prepare discipline insert: " . mysqli_error($conn));
        }
        
        $inserted_count = 0;
        foreach ($all_disciplines as $disc) {
            mysqli_stmt_bind_param($insert_stmt, 'sss', $disc[0], $disc[1], $disc[2]);
            if (mysqli_stmt_execute($insert_stmt)) {
                if (mysqli_affected_rows($conn) > 0) {
                    $inserted_count++;
                }
            }
        }
        mysqli_stmt_close($insert_stmt);
    } else {
        log_message("Category column does not exist, inserting without category...", 'info');
        $insert_stmt = mysqli_prepare($conn, "INSERT IGNORE INTO disciplines (name, description) VALUES (?, ?)");
        if (!$insert_stmt) {
            throw new Exception("Failed to prepare discipline insert: " . mysqli_error($conn));
        }
        
        $inserted_count = 0;
        foreach ($all_disciplines as $disc) {
            mysqli_stmt_bind_param($insert_stmt, 'ss', $disc[0], $disc[2]); // name, description
            if (mysqli_stmt_execute($insert_stmt)) {
                if (mysqli_affected_rows($conn) > 0) {
                    $inserted_count++;
                }
            }
        }
        mysqli_stmt_close($insert_stmt);
    }
    
    if ($inserted_count > 0) {
        log_message("Inserted {$inserted_count} missing disciplines", 'success');
    }
    
    // Get all discipline IDs
    $discipline_query = "SELECT id, name FROM disciplines";
    $result = mysqli_query($conn, $discipline_query);
    
    if (!$result) {
        throw new Exception("Failed to query disciplines: " . mysqli_error($conn));
    }
    
    $discipline_ids = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $discipline_ids[$row['name']] = $row['id'];
    }
    
    log_message("Found " . count($discipline_ids) . " disciplines in database", 'success');
    
    // Check if Obeah exists, if not add it
    if (!isset($discipline_ids['Obeah']) && isset($discipline_ids['Valeren'])) {
        log_message("Adding Obeah discipline (alias for Valeren)...", 'info');
        $insert_obeah = "INSERT INTO disciplines (name, category, description) 
                         SELECT 'Obeah', category, description FROM disciplines WHERE name = 'Valeren'";
        if (mysqli_query($conn, $insert_obeah)) {
            $discipline_ids['Obeah'] = mysqli_insert_id($conn);
            log_message("Added Obeah discipline", 'success');
        }
    }
    
    // Prepare insert statement
    $insert_sql = "INSERT INTO discipline_powers 
                   (discipline_id, power_level, power_name, description, prerequisites) 
                   VALUES (?, ?, ?, ?, NULL)
                   ON DUPLICATE KEY UPDATE 
                   power_name = VALUES(power_name),
                   description = VALUES(description)";
    $stmt = mysqli_prepare($conn, $insert_sql);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . mysqli_error($conn));
    }
    
    $inserted = 0;
    $updated = 0;
    $skipped = 0;
    
    // Insert powers for each discipline
    foreach ($discipline_powers as $discipline_name => $powers) {
        if (!isset($discipline_ids[$discipline_name])) {
            log_message("Skipping {$discipline_name} - not found in database", 'warning');
            $skipped += 5;
            continue;
        }
        
        $discipline_id = $discipline_ids[$discipline_name];
        
        foreach ($powers as $level => $power_data) {
            // Check if it already exists
            $check_sql = "SELECT id FROM discipline_powers 
                         WHERE discipline_id = ? AND power_level = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            mysqli_stmt_bind_param($check_stmt, 'ii', $discipline_id, $level);
            mysqli_stmt_execute($check_stmt);
            $exists = mysqli_stmt_get_result($check_stmt);
            $is_update = mysqli_num_rows($exists) > 0;
            mysqli_stmt_close($check_stmt);
            
            mysqli_stmt_bind_param($stmt, 'iiss', 
                $discipline_id, 
                $level, 
                $power_data['name'], 
                $power_data['desc']
            );
            
            if (mysqli_stmt_execute($stmt)) {
                if ($is_update) {
                    $updated++;
                } else {
                    $inserted++;
                }
            } else {
                log_message("Failed to insert {$discipline_name} level {$level}: " . mysqli_stmt_error($stmt), 'error');
                $skipped++;
            }
        }
        
        log_message("Processed {$discipline_name} (5 powers)", 'success');
    }
    
    mysqli_stmt_close($stmt);
    
    log_message("Population complete!", 'success');
    log_message("  Inserted: {$inserted} powers", 'success');
    log_message("  Updated: {$updated} powers", 'success');
    if ($skipped > 0) {
        log_message("  Skipped: {$skipped} powers", 'warning');
    }
    
    // Verify count
    $count_sql = "SELECT COUNT(*) as total FROM discipline_powers";
    $count_result = mysqli_query($conn, $count_sql);
    $count_row = mysqli_fetch_assoc($count_result);
    log_message("Total powers in database: " . $count_row['total'], 'info');
    
    // Expected: 22 disciplines × 5 powers = 110 (or 23 if Obeah added = 115)
    $expected = count($discipline_powers) * 5;
    if ($count_row['total'] == $expected || $count_row['total'] == 115) {
        log_message("✅ All powers successfully populated!", 'success');
    } else {
        log_message("⚠️ Expected {$expected} powers but found {$count_row['total']}", 'warning');
    }
    
} catch (Exception $e) {
    log_message("Population failed: " . $e->getMessage(), 'error');
    exit(1);
}

if ($is_web) {
    echo "</pre>";
    echo "<p style='margin-top: 20px;'><strong>Population complete!</strong></p>";
    echo "</body></html>";
}

mysqli_close($conn);
?>

