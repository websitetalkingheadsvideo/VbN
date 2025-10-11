<?php
/**
 * Test Import Script - Andrei Radulescu
 * Tests character import with first Tremere character
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=================================================================\n";
echo "Character Import Test - Andrei Radulescu\n";
echo "=================================================================\n\n";

// Include database connection
require_once __DIR__ . '/../includes/connect.php';

if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error() . "\n");
}

echo "✅ Database connection established\n\n";

// Read JSON file
$json_file = __DIR__ . '/Tremere.json';
if (!file_exists($json_file)) {
    die("❌ JSON file not found: $json_file\n");
}

$json_content = file_get_contents($json_file);
$json_content = trim($json_content);

// Fix JSON structure - file has 3 objects not wrapped in array
// Extract just the first character (Andrei)
preg_match('/^\{(.+?)\s*\}\s*\{/s', $json_content, $matches);
if (!$matches) {
    // If that didn't work, try getting everything up to the first closing brace
    preg_match('/^\{(.+?)\}\s*$/s', $json_content, $matches);
}

$andrei_json = '{' . ($matches[1] ?? '') . '}';
$andrei = json_decode($andrei_json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("❌ JSON decode error: " . json_last_error_msg() . "\n");
}

if (!$andrei || !isset($andrei['character_name'])) {
    die("❌ Failed to parse Andrei's data\n");
}

echo "✅ Loaded character: {$andrei['character_name']}\n\n";

// Helper function: Calculate blood per turn from generation
function getBloodPerTurn($generation) {
    if ($generation >= 12) return 1;
    if ($generation >= 9) return 2;
    if ($generation >= 6) return 3;
    if ($generation >= 4) return 4;
    if ($generation == 3) return 6;
    return 8;
}

// Helper function: Calculate blood pool maximum from generation
function getBloodPoolMax($generation) {
    if ($generation >= 12) return 11;
    if ($generation == 11) return 12;
    if ($generation == 10) return 13;
    if ($generation == 9) return 14;
    if ($generation == 8) return 15;
    if ($generation == 7) return 20;
    if ($generation == 6) return 30;
    return 50;
}

// Begin transaction
mysqli_begin_transaction($conn);

try {
    echo "🚀 Starting import...\n\n";
    
    // ============================================================================
    // STEP 1: Insert basic character data
    // ============================================================================
    echo "📝 Step 1: Inserting basic character data...\n";
    
    // For testing, use a dummy user_id (1 = admin)
    $user_id = 1;
    
    // Prepare custom_data JSON
    $custom_data = [];
    if (isset($andrei['research_notes'])) {
        $custom_data['research_notes'] = $andrei['research_notes'];
    }
    if (isset($andrei['disciplines'])) {
        foreach ($andrei['disciplines'] as $disc) {
            if (isset($disc['notes'])) {
                $custom_data['discipline_notes'][$disc['path'] ?? $disc['name']] = $disc['notes'];
            }
        }
    }
    $custom_data_json = json_encode($custom_data);
    
    $stmt = mysqli_prepare($conn, "
        INSERT INTO characters (
            user_id, character_name, player_name, chronicle, nature, demeanor, 
            concept, clan, generation, sire, pc, biography, equipment, 
            total_xp, spent_xp, notes, custom_data
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "isssssssississiis",
        $user_id,
        $andrei['character_name'],
        $andrei['player_name'],
        $andrei['chronicle'],
        $andrei['nature'],
        $andrei['demeanor'],
        $andrei['concept'],
        $andrei['clan'],
        $andrei['generation'],
        $andrei['sire'],
        $andrei['pc'],
        $andrei['biography'],
        $andrei['equipment'],
        $andrei['status']['xp_total'],
        $andrei['status']['xp_spent'],
        $andrei['status']['notes'],
        $custom_data_json
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to insert character: " . mysqli_stmt_error($stmt));
    }
    
    $character_id = mysqli_insert_id($conn);
    echo "   ✅ Character created with ID: $character_id\n";
    mysqli_stmt_close($stmt);
    
    // ============================================================================
    // STEP 2: Insert traits
    // ============================================================================
    echo "\n📝 Step 2: Inserting traits...\n";
    
    $stmt = mysqli_prepare($conn, "
        INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type)
        VALUES (?, ?, ?, 'positive')
    ");
    
    if (!$stmt) {
        throw new Exception("Traits prepare failed: " . mysqli_error($conn));
    }
    
    $trait_count = 0;
    foreach (['Physical', 'Social', 'Mental'] as $category) {
        if (isset($andrei['traits'][$category])) {
            foreach ($andrei['traits'][$category] as $trait) {
                mysqli_stmt_bind_param($stmt, "iss", $character_id, $trait, $category);
                if (mysqli_stmt_execute($stmt)) {
                    $trait_count++;
                } else {
                    echo "   ⚠️  Warning: Failed to insert trait $trait\n";
                }
            }
        }
    }
    echo "   ✅ Inserted $trait_count positive traits\n";
    mysqli_stmt_close($stmt);
    
    // Insert negative traits
    $stmt = mysqli_prepare($conn, "
        INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type)
        VALUES (?, ?, ?, 'negative')
    ");
    
    $neg_trait_count = 0;
    foreach (['Physical', 'Social', 'Mental'] as $category) {
        if (isset($andrei['negativeTraits'][$category])) {
            foreach ($andrei['negativeTraits'][$category] as $trait) {
                mysqli_stmt_bind_param($stmt, "iss", $character_id, $trait, $category);
                if (mysqli_stmt_execute($stmt)) {
                    $neg_trait_count++;
                }
            }
        }
    }
    echo "   ✅ Inserted $neg_trait_count negative traits\n";
    mysqli_stmt_close($stmt);
    
    // ============================================================================
    // STEP 3: Insert abilities & specializations
    // ============================================================================
    echo "\n📝 Step 3: Inserting abilities...\n";
    
    $stmt = mysqli_prepare($conn, "
        INSERT INTO character_abilities (character_id, ability_name, level)
        VALUES (?, ?, ?)
    ");
    
    if (!$stmt) {
        throw new Exception("Abilities prepare failed: " . mysqli_error($conn));
    }
    
    $ability_count = 0;
    foreach ($andrei['abilities'] as $ability) {
        mysqli_stmt_bind_param($stmt, "isi", 
            $character_id, 
            $ability['name'], 
            $ability['level']
        );
        if (mysqli_stmt_execute($stmt)) {
            $ability_count++;
        }
    }
    echo "   ✅ Inserted $ability_count abilities\n";
    mysqli_stmt_close($stmt);
    
    // Insert specializations
    if (isset($andrei['specializations'])) {
        echo "\n📝 Step 3b: Inserting specializations...\n";
        
        $stmt = mysqli_prepare($conn, "
            INSERT INTO character_ability_specializations 
            (character_id, ability_name, specialization, is_primary, grants_bonus)
            VALUES (?, ?, ?, TRUE, ?)
        ");
        
        $spec_count = 0;
        foreach ($andrei['specializations'] as $ability_name => $specialization) {
            // Find ability level to determine if it grants bonus (level >= 4)
            $ability_level = 0;
            foreach ($andrei['abilities'] as $ab) {
                if ($ab['name'] === $ability_name) {
                    $ability_level = $ab['level'];
                    break;
                }
            }
            $grants_bonus = $ability_level >= 4 ? 1 : 0;
            
            mysqli_stmt_bind_param($stmt, "issi", 
                $character_id, 
                $ability_name, 
                $specialization,
                $grants_bonus
            );
            if (mysqli_stmt_execute($stmt)) {
                $spec_count++;
            }
        }
        echo "   ✅ Inserted $spec_count specializations\n";
        mysqli_stmt_close($stmt);
    }
    
    // ============================================================================
    // STEP 4: Insert disciplines (including Blood Magic paths)
    // ============================================================================
    echo "\n📝 Step 4: Inserting disciplines...\n";
    
    // First, ensure disciplines exist in master table
    foreach ($andrei['disciplines'] as $disc) {
        $disc_name = $disc['path'] ?? $disc['name'];
        $parent = (isset($disc['path']) && $disc['name'] === 'Thaumaturgy') ? 'Thaumaturgy' : null;
        
        $stmt = mysqli_prepare($conn, "
            INSERT INTO disciplines (name, parent_discipline)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE name=name
        ");
        mysqli_stmt_bind_param($stmt, "ss", $disc_name, $parent);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // Convert level to Basic/Intermediate/Advanced
    function getLevelName($level) {
        if ($level <= 2) return 'Basic';
        if ($level <= 4) return 'Intermediate';
        return 'Advanced';
    }
    
    $stmt = mysqli_prepare($conn, "
        INSERT INTO character_disciplines (character_id, discipline_name, level)
        VALUES (?, ?, ?)
    ");
    
    $disc_count = 0;
    foreach ($andrei['disciplines'] as $disc) {
        $disc_name = $disc['path'] ?? $disc['name'];
        $level_name = getLevelName($disc['level']);
        
        mysqli_stmt_bind_param($stmt, "iss", 
            $character_id, 
            $disc_name, 
            $level_name
        );
        if (mysqli_stmt_execute($stmt)) {
            $disc_count++;
        }
    }
    echo "   ✅ Inserted $disc_count disciplines\n";
    mysqli_stmt_close($stmt);
    
    // ============================================================================
    // STEP 5: Insert backgrounds
    // ============================================================================
    echo "\n📝 Step 5: Inserting backgrounds...\n";
    
    $stmt = mysqli_prepare($conn, "
        INSERT INTO character_backgrounds (character_id, background_name, level)
        VALUES (?, ?, ?)
    ");
    
    $bg_count = 0;
    foreach ($andrei['backgrounds'] as $bg_name => $level) {
        if ($level > 0) {
            mysqli_stmt_bind_param($stmt, "isi", $character_id, $bg_name, $level);
            if (mysqli_stmt_execute($stmt)) {
                $bg_count++;
            }
        }
    }
    echo "   ✅ Inserted $bg_count backgrounds\n";
    mysqli_stmt_close($stmt);
    
    // ============================================================================
    // STEP 6: Insert morality
    // ============================================================================
    echo "\n📝 Step 6: Inserting morality...\n";
    
    $stmt = mysqli_prepare($conn, "
        INSERT INTO character_morality (
            character_id, path_name, path_rating, conscience, self_control, 
            courage, willpower_permanent, willpower_current, humanity
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    mysqli_stmt_bind_param($stmt, "isiiiiiii",
        $character_id,
        $andrei['morality']['path_name'],
        $andrei['morality']['path_rating'],
        $andrei['morality']['conscience'],
        $andrei['morality']['self_control'],
        $andrei['morality']['courage'],
        $andrei['morality']['willpower_permanent'],
        $andrei['morality']['willpower_current'],
        $andrei['morality']['humanity']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo "   ✅ Inserted morality data\n";
    }
    mysqli_stmt_close($stmt);
    
    // ============================================================================
    // STEP 7: Insert merits & flaws
    // ============================================================================
    echo "\n📝 Step 7: Inserting merits & flaws...\n";
    
    $stmt = mysqli_prepare($conn, "
        INSERT INTO character_merits_flaws (
            character_id, name, type, category, point_value, description
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        throw new Exception("Merits/Flaws prepare failed: " . mysqli_error($conn));
    }
    
    $mf_count = 0;
    foreach ($andrei['merits_flaws'] as $mf) {
        // Capitalize type to match database ENUM ('Merit', 'Flaw')
        $type = ucfirst(strtolower($mf['type']));
        
        mysqli_stmt_bind_param($stmt, "isssis",
            $character_id,
            $mf['name'],
            $type,
            $mf['category'],
            $mf['cost'],
            $mf['description']
        );
        if (mysqli_stmt_execute($stmt)) {
            $mf_count++;
        }
    }
    echo "   ✅ Inserted $mf_count merits & flaws\n";
    mysqli_stmt_close($stmt);
    
    // ============================================================================
    // STEP 8: Insert rituals
    // ============================================================================
    echo "\n📝 Step 8: Inserting rituals...\n";
    
    $stmt = mysqli_prepare($conn, "
        INSERT INTO character_rituals (
            character_id, ritual_name, ritual_type, level, is_custom
        ) VALUES (?, ?, ?, ?, ?)
    ");
    
    $ritual_count = 0;
    foreach ($andrei['rituals'] as $ritual) {
        // Parse ritual string: "Name (Level X)" or "Name (Level X - Custom)"
        $is_custom = stripos($ritual, 'custom') !== false ? 1 : 0;
        
        // Extract name and level
        if (preg_match('/^(.+?)\s*\(Level\s+(\d+)/i', $ritual, $matches)) {
            $ritual_name = trim($matches[1]);
            $level = (int)$matches[2];
        } else {
            // No level found, use 0 for unknown
            $ritual_name = preg_replace('/\s*\(Level.*?\)/', '', $ritual);
            $level = 0;
        }
        
        $ritual_type = 'Thaumaturgy'; // Tremere default
        
        mysqli_stmt_bind_param($stmt, "issii",
            $character_id,
            $ritual_name,
            $ritual_type,
            $level,
            $is_custom
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $ritual_count++;
        }
    }
    echo "   ✅ Inserted $ritual_count rituals\n";
    mysqli_stmt_close($stmt);
    
    // ============================================================================
    // STEP 9: Insert character status
    // ============================================================================
    echo "\n📝 Step 9: Inserting character status...\n";
    
    $blood_pool_max = getBloodPoolMax($andrei['generation']);
    
    $stmt = mysqli_prepare($conn, "
        INSERT INTO character_status (
            character_id, health_levels, blood_pool_current, blood_pool_maximum
        ) VALUES (?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        throw new Exception("Character status prepare failed: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "isii",
        $character_id,
        $andrei['status']['health_levels'],
        $andrei['status']['blood_pool'],
        $blood_pool_max
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo "   ✅ Inserted character status\n";
    }
    mysqli_stmt_close($stmt);
    
    // ============================================================================
    // Commit transaction
    // ============================================================================
    mysqli_commit($conn);
    
    echo "\n";
    echo "=================================================================\n";
    echo "✅ Import Complete!\n";
    echo "=================================================================\n";
    echo "Character ID: $character_id\n";
    echo "Name: {$andrei['character_name']}\n";
    echo "Generation: {$andrei['generation']}\n";
    echo "Blood per turn: " . getBloodPerTurn($andrei['generation']) . "\n";
    echo "Blood pool max: $blood_pool_max\n";
    echo "=================================================================\n\n";
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "\n❌ Import failed: " . $e->getMessage() . "\n";
    echo "   All changes rolled back.\n\n";
    exit(1);
}

mysqli_close($conn);
?>

