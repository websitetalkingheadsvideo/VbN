<?php
/**
 * Import All 3 Tremere Characters
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=================================================================\n";
echo "Import All Tremere Characters\n";
echo "=================================================================\n\n";

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
$characters = json_decode($json_content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("❌ JSON decode error: " . json_last_error_msg() . "\n");
}

if (!is_array($characters)) {
    die("❌ JSON is not an array\n");
}

echo "✅ Loaded " . count($characters) . " characters from JSON\n\n";

// Helper functions
function getBloodPerTurn($generation) {
    if ($generation >= 12) return 1;
    if ($generation >= 9) return 2;
    if ($generation >= 6) return 3;
    if ($generation >= 4) return 4;
    if ($generation == 3) return 6;
    return 8;
}

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

function getLevelName($level) {
    if ($level <= 2) return 'Basic';
    if ($level <= 4) return 'Intermediate';
    return 'Advanced';
}

// Import each character
$imported_ids = [];
$user_id = 1; // Admin user

foreach ($characters as $index => $char) {
    $char_num = $index + 1;
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Character $char_num: {$char['character_name']}\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    mysqli_begin_transaction($conn);
    
    try {
        // Prepare custom_data JSON
        $custom_data = [];
        if (isset($char['research_notes'])) {
            $custom_data['research_notes'] = $char['research_notes'];
        }
        if (isset($char['disciplines'])) {
            foreach ($char['disciplines'] as $disc) {
                if (isset($disc['notes'])) {
                    $custom_data['discipline_notes'][$disc['path'] ?? $disc['name']] = $disc['notes'];
                }
            }
        }
        if (isset($char['artifacts'])) {
            $custom_data['artifacts'] = $char['artifacts'];
        }
        $custom_data_json = json_encode($custom_data);
        
        // Insert character
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
            $char['character_name'],
            $char['player_name'],
            $char['chronicle'],
            $char['nature'],
            $char['demeanor'],
            $char['concept'],
            $char['clan'],
            $char['generation'],
            $char['sire'],
            $char['pc'],
            $char['biography'],
            $char['equipment'],
            $char['status']['xp_total'],
            $char['status']['xp_spent'],
            $char['status']['notes'],
            $custom_data_json
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to insert character: " . mysqli_stmt_error($stmt));
        }
        
        $character_id = mysqli_insert_id($conn);
        echo "✅ Character created (ID: $character_id)\n";
        mysqli_stmt_close($stmt);
        
        // Traits
        $stmt = mysqli_prepare($conn, "
            INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type)
            VALUES (?, ?, ?, 'positive')
        ");
        
        $trait_count = 0;
        foreach (['Physical', 'Social', 'Mental'] as $category) {
            if (isset($char['traits'][$category])) {
                foreach ($char['traits'][$category] as $trait) {
                    mysqli_stmt_bind_param($stmt, "iss", $character_id, $trait, $category);
                    mysqli_stmt_execute($stmt);
                    $trait_count++;
                }
            }
        }
        mysqli_stmt_close($stmt);
        
        // Negative traits
        $stmt = mysqli_prepare($conn, "
            INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type)
            VALUES (?, ?, ?, 'negative')
        ");
        
        $neg_trait_count = 0;
        foreach (['Physical', 'Social', 'Mental'] as $category) {
            if (isset($char['negativeTraits'][$category])) {
                foreach ($char['negativeTraits'][$category] as $trait) {
                    mysqli_stmt_bind_param($stmt, "iss", $character_id, $trait, $category);
                    mysqli_stmt_execute($stmt);
                    $neg_trait_count++;
                }
            }
        }
        mysqli_stmt_close($stmt);
        echo "✅ Traits: $trait_count positive, $neg_trait_count negative\n";
        
        // Abilities
        $stmt = mysqli_prepare($conn, "
            INSERT INTO character_abilities (character_id, ability_name, level)
            VALUES (?, ?, ?)
        ");
        
        $ability_count = 0;
        foreach ($char['abilities'] as $ability) {
            mysqli_stmt_bind_param($stmt, "isi", $character_id, $ability['name'], $ability['level']);
            mysqli_stmt_execute($stmt);
            $ability_count++;
        }
        mysqli_stmt_close($stmt);
        
        // Specializations
        $spec_count = 0;
        if (isset($char['specializations'])) {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO character_ability_specializations 
                (character_id, ability_name, specialization, is_primary, grants_bonus)
                VALUES (?, ?, ?, TRUE, ?)
            ");
            
            foreach ($char['specializations'] as $ability_name => $specialization) {
                $ability_level = 0;
                foreach ($char['abilities'] as $ab) {
                    if ($ab['name'] === $ability_name) {
                        $ability_level = $ab['level'];
                        break;
                    }
                }
                $grants_bonus = $ability_level >= 4 ? 1 : 0;
                
                mysqli_stmt_bind_param($stmt, "issi", $character_id, $ability_name, $specialization, $grants_bonus);
                mysqli_stmt_execute($stmt);
                $spec_count++;
            }
            mysqli_stmt_close($stmt);
        }
        echo "✅ Abilities: $ability_count ($spec_count with specializations)\n";
        
        // Disciplines
        foreach ($char['disciplines'] as $disc) {
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
        
        $stmt = mysqli_prepare($conn, "
            INSERT INTO character_disciplines (character_id, discipline_name, level)
            VALUES (?, ?, ?)
        ");
        
        $disc_count = 0;
        foreach ($char['disciplines'] as $disc) {
            $disc_name = $disc['path'] ?? $disc['name'];
            $level_name = getLevelName($disc['level']);
            
            mysqli_stmt_bind_param($stmt, "iss", $character_id, $disc_name, $level_name);
            mysqli_stmt_execute($stmt);
            $disc_count++;
        }
        mysqli_stmt_close($stmt);
        echo "✅ Disciplines: $disc_count\n";
        
        // Backgrounds
        $stmt = mysqli_prepare($conn, "
            INSERT INTO character_backgrounds (character_id, background_name, level)
            VALUES (?, ?, ?)
        ");
        
        $bg_count = 0;
        foreach ($char['backgrounds'] as $bg_name => $level) {
            if ($level > 0) {
                mysqli_stmt_bind_param($stmt, "isi", $character_id, $bg_name, $level);
                mysqli_stmt_execute($stmt);
                $bg_count++;
            }
        }
        mysqli_stmt_close($stmt);
        echo "✅ Backgrounds: $bg_count\n";
        
        // Morality
        $stmt = mysqli_prepare($conn, "
            INSERT INTO character_morality (
                character_id, path_name, path_rating, conscience, self_control, 
                courage, willpower_permanent, willpower_current, humanity
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        mysqli_stmt_bind_param($stmt, "isiiiiiii",
            $character_id,
            $char['morality']['path_name'],
            $char['morality']['path_rating'],
            $char['morality']['conscience'],
            $char['morality']['self_control'],
            $char['morality']['courage'],
            $char['morality']['willpower_permanent'],
            $char['morality']['willpower_current'],
            $char['morality']['humanity']
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "✅ Morality\n";
        
        // Merits & Flaws
        $stmt = mysqli_prepare($conn, "
            INSERT INTO character_merits_flaws (
                character_id, name, type, category, point_value, description
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $mf_count = 0;
        foreach ($char['merits_flaws'] as $mf) {
            $type = ucfirst(strtolower($mf['type']));
            
            mysqli_stmt_bind_param($stmt, "isssis",
                $character_id,
                $mf['name'],
                $type,
                $mf['category'],
                $mf['cost'],
                $mf['description']
            );
            mysqli_stmt_execute($stmt);
            $mf_count++;
        }
        mysqli_stmt_close($stmt);
        echo "✅ Merits & Flaws: $mf_count\n";
        
        // Rituals
        $stmt = mysqli_prepare($conn, "
            INSERT INTO character_rituals (
                character_id, ritual_name, ritual_type, level, is_custom
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        $ritual_count = 0;
        foreach ($char['rituals'] as $ritual) {
            $is_custom = stripos($ritual, 'custom') !== false ? 1 : 0;
            
            if (preg_match('/^(.+?)\s*\(Level\s+(\d+)/i', $ritual, $matches)) {
                $ritual_name = trim($matches[1]);
                $level = (int)$matches[2];
            } else {
                $ritual_name = preg_replace('/\s*\(Level.*?\)/', '', $ritual);
                $level = 0;
            }
            
            $ritual_type = 'Thaumaturgy';
            
            mysqli_stmt_bind_param($stmt, "issii", $character_id, $ritual_name, $ritual_type, $level, $is_custom);
            mysqli_stmt_execute($stmt);
            $ritual_count++;
        }
        mysqli_stmt_close($stmt);
        echo "✅ Rituals: $ritual_count\n";
        
        // Character Status
        $blood_pool_max = getBloodPoolMax($char['generation']);
        
        $stmt = mysqli_prepare($conn, "
            INSERT INTO character_status (
                character_id, health_levels, blood_pool_current, blood_pool_maximum
            ) VALUES (?, ?, ?, ?)
        ");
        
        mysqli_stmt_bind_param($stmt, "isii",
            $character_id,
            $char['status']['health_levels'],
            $char['status']['blood_pool'],
            $blood_pool_max
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo "✅ Character Status\n";
        
        // Commit
        mysqli_commit($conn);
        $imported_ids[] = $character_id;
        
        echo "\n✅ Successfully imported: {$char['character_name']} (ID: $character_id)\n\n";
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "\n❌ Failed to import {$char['character_name']}: " . $e->getMessage() . "\n\n";
    }
}

echo "=================================================================\n";
echo "Import Summary\n";
echo "=================================================================\n";
echo "Total attempted: " . count($characters) . "\n";
echo "Successfully imported: " . count($imported_ids) . "\n";
if (count($imported_ids) > 0) {
    echo "Character IDs: " . implode(', ', $imported_ids) . "\n";
}
echo "=================================================================\n\n";

if (count($imported_ids) === count($characters)) {
    echo "🎉 All characters imported successfully!\n";
} else {
    echo "⚠️  Some characters failed to import. See errors above.\n";
}

mysqli_close($conn);
?>

