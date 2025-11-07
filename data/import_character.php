<?php
/**
 * Generic Character Import Script
 * Usage: https://websitetalkingheads.com/vbn/data/import_character.php?file=Character%20Name.json
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get filename from query parameter
$filename = isset($_GET['file']) ? $_GET['file'] : null;

if (!$filename) {
    die("ERROR: No file specified. Usage: ?file=Character%20Name.json\n");
}

echo "=================================================================\n";
echo "Character Import\n";
echo "=================================================================\n\n";

// Include database connection
echo "📡 Loading database connection...\n";
$connect_file = '/usr/home/working/public_html/vbn.talkingheads.video/includes/connect.php';
if (!file_exists($connect_file)) {
    die("❌ Connection file not found: $connect_file\n");
}
require_once $connect_file;

// Check if connection exists
echo "🔍 Checking database connection...\n";
if (!isset($conn) || !$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error() . "\n");
}

echo "✅ Database connection established\n";
echo "   Connected to remote database\n\n";

// Read and decode JSON
$json_file = '/usr/home/working/public_html/vbn.talkingheads.video/reference/Characters/' . $filename;

echo "🔍 Looking for file: $json_file\n";
echo "🔍 __DIR__ is: " . __DIR__ . "\n";

if (!file_exists($json_file)) {
    die("❌ JSON file not found: $json_file\n");
}

echo "📄 Reading character file: $filename\n\n";

$json_data = file_get_contents($json_file);
$character = json_decode($json_data, true);

if (!$character) {
    die("❌ Failed to parse JSON file\n");
}

echo "✅ JSON parsed successfully\n";
$char_name = $character['character_name'] ?? $character['name'] ?? 'Unknown';
echo "   Character: $char_name\n";
echo "   Clan: {$character['clan']}\n\n";

// Helper to ensure ASCII-only strings for DB
if (!function_exists('vbn_ascii_clean')) {
    function vbn_ascii_clean($s) {
        if ($s === null) return null;
        $out = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string)$s);
        if ($out === false) {
            $out = preg_replace('/[^\x20-\x7E]/', '', (string)$s);
        }
        return $out;
    }
}

// Start transaction
echo "🚀 Starting import transaction...\n\n";
$conn->begin_transaction();

try {
    // 1. Insert main character record
    echo "📝 Inserting character record...\n";
    
    // Use user_id = 1 for NPCs (default ST/admin user)
    $user_id = 1;
    
    $stmt = $conn->prepare("
        INSERT INTO characters (
            user_id, character_name, player_name, chronicle, nature, demeanor, 
            concept, clan, generation, sire, pc, biography, equipment,
            experience_total, experience_unspent, blood_pool_current, notes,
            status, camarilla_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    // Build sanitized values and move freeform status text into notes
    $player_name = vbn_ascii_clean($character['player_name'] ?? 'NPC');
    $chronicle = vbn_ascii_clean($character['chronicle'] ?? '');
    $nature = vbn_ascii_clean($character['nature'] ?? '');
    $demeanor = vbn_ascii_clean($character['demeanor'] ?? '');
    $concept = vbn_ascii_clean($character['concept'] ?? '');
    $clan = vbn_ascii_clean($character['clan'] ?? '');
    $generation = (int)($character['generation'] ?? 0);
    $sire = vbn_ascii_clean($character['sire'] ?? '');
    $pc = (int)($character['pc'] ?? 0);
    $biography = vbn_ascii_clean($character['biography'] ?? '');
    $equipment = vbn_ascii_clean($character['equipment'] ?? '');

    $xp_total = (int)($character['total_xp'] ?? ($character['status']['xp_total'] ?? 0));
    $spent_xp = (int)($character['spent_xp'] ?? 0);
    $xp_unspent = (int)($character['status']['xp_available'] ?? max(0, $xp_total - $spent_xp));

    $blood_pool_current = (int)($character['status']['blood_pool_current'] ?? ($character['status']['blood_pool'] ?? 10));

    $notes_parts = [];
    if (!empty($character['notes']) && is_string($character['notes'])) { $notes_parts[] = $character['notes']; }
    if (isset($character['status']) && is_string($character['status'])) { $notes_parts[] = $character['status']; }
    if (!empty($character['status']['notes']) && is_string($character['status']['notes'])) { $notes_parts[] = $character['status']['notes']; }
    $notes = vbn_ascii_clean(implode(' | ', $notes_parts));

    $valid_states = ['active', 'inactive', 'archived'];
    $status_value = strtolower(vbn_ascii_clean($character['current_state'] ?? ($character['status']['current_state'] ?? 'active')));
    if (!in_array($status_value, $valid_states, true)) {
        $status_value = 'active';
    }

    $valid_camarilla = ['Camarilla', 'Anarch', 'Independent', 'Sabbat', 'Unknown'];
    $camarilla_value_raw = $character['camarilla_status'] ?? ($character['status']['camarilla_status'] ?? 'Unknown');
    $camarilla_value = vbn_ascii_clean($camarilla_value_raw);
    $camarilla_value = $camarilla_value ? ucfirst(strtolower($camarilla_value)) : 'Unknown';
    if (!in_array($camarilla_value, $valid_camarilla, true)) {
        $camarilla_value = 'Unknown';
    }

    $stmt->bind_param("isssssssisissiiisss",
        $user_id,
        vbn_ascii_clean($char_name),
        $player_name,
        $chronicle,
        $nature,
        $demeanor,
        $concept,
        $clan,
        $generation,
        $sire,
        $pc,
        $biography,
        $equipment,
        $xp_total,
        $xp_unspent,
        $blood_pool_current,
        $notes,
        $status_value,
        $camarilla_value
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $character_id = $conn->insert_id;
    echo "✅ Character created (ID: $character_id)\n\n";

    // 2. Insert traits (positive)
    echo "📝 Inserting positive traits...\n";
    $trait_stmt = $conn->prepare("
        INSERT INTO character_traits (character_id, trait_category, trait_name)
        VALUES (?, ?, ?)
    ");
    
    $trait_count = 0;
    if (!empty($character['traits']) && is_array($character['traits'])) {
        foreach ($character['traits'] as $category => $traits) {
            if (!is_array($traits)) continue;
            foreach ($traits as $trait) {
                $trait_name = null;
                if (is_string($trait)) {
                    $trait_name = $trait;
                } elseif (is_array($trait)) {
                    // Handle format like [{"Strength": 2}]
                    $trait_name = array_key_first($trait);
                }
                if ($trait_name) {
                    $trait_stmt->bind_param("iss", $character_id, $category, $trait_name);
                    $trait_stmt->execute();
                    $trait_count++;
                }
            }
        }
    }
    echo "✅ {$trait_count} positive traits added\n\n";

    // 3. Insert negative traits
    echo "📝 Inserting negative traits...\n";
    $neg_trait_stmt = $conn->prepare("
        INSERT INTO character_negative_traits (character_id, trait_category, trait_name)
        VALUES (?, ?, ?)
    ");
    
    if (!$neg_trait_stmt) {
        throw new Exception("Negative traits prepare failed: " . $conn->error);
    }
    
    $neg_count = 0;
    if (!empty($character['negativeTraits']) && is_array($character['negativeTraits'])) {
        foreach ($character['negativeTraits'] as $category => $traits) {
            if (!is_array($traits)) continue;
            foreach ($traits as $trait) {
                $trait_name = null;
                if (is_string($trait)) {
                    $trait_name = $trait;
                } elseif (is_array($trait)) {
                    $trait_name = array_key_first($trait);
                }
                if ($trait_name) {
                    $neg_trait_stmt->bind_param("iss", $character_id, $category, $trait_name);
                    if (!$neg_trait_stmt->execute()) {
                        throw new Exception("Negative trait insert failed: " . $neg_trait_stmt->error);
                    }
                    $neg_count++;
                }
            }
        }
    }
    echo "✅ {$neg_count} negative traits added\n\n";

    // 4. Insert abilities (with specializations in same table)
    echo "📝 Inserting abilities...\n";
    $ability_stmt = $conn->prepare("
        INSERT INTO character_abilities (character_id, ability_name, level, specialization)
        VALUES (?, ?, ?, ?)
    ");
    
    if (!$ability_stmt) {
        throw new Exception("Abilities prepare failed: " . $conn->error);
    }
    
    $ability_count = 0;
    if (!empty($character['abilities']) && is_array($character['abilities'])) {
        // Check if abilities are organized by category (talents/skills/knowledges)
        $abilities_list = [];
        if (isset($character['abilities']['talents']) || isset($character['abilities']['skills']) || isset($character['abilities']['knowledges'])) {
            // Format: {"talents": ["Alertness 3", ...], "skills": [...], "knowledges": [...]}
            foreach (['talents', 'skills', 'knowledges'] as $category) {
                if (!empty($character['abilities'][$category]) && is_array($character['abilities'][$category])) {
                    foreach ($character['abilities'][$category] as $ability) {
                        $abilities_list[] = $ability;
                    }
                }
            }
        } else {
            // Format: [{"name": "...", "level": ...}, ...] or [{"Academics": 2}, ...]
            $abilities_list = $character['abilities'];
        }
        
        foreach ($abilities_list as $ability) {
            $ability_name = null;
            $ability_level = 0;
            $spec = null;
            
            // Handle different formats
            if (is_string($ability)) {
                // Format: "Alertness 3" or "Performance 1 (emcee)"
                if (preg_match('/^(.+?)\s+(\d+)(?:\s*\((.+?)\))?$/i', $ability, $matches)) {
                    $ability_name = trim($matches[1]);
                    $ability_level = (int)$matches[2];
                    if (isset($matches[3])) {
                        $spec = trim($matches[3]);
                    }
                } else {
                    continue; // Skip malformed entries
                }
            } elseif (is_array($ability)) {
                // Format: {"name": "Academics", "level": 2} or {"Academics": 2}
                if (isset($ability['name']) && isset($ability['level'])) {
                    $ability_name = $ability['name'];
                    $ability_level = (int)$ability['level'];
                    $spec = $ability['specialization'] ?? null;
                } elseif (count($ability) === 1) {
                    // Format: {"Academics": 2}
                    $ability_name = array_key_first($ability);
                    $ability_level = (int)$ability[$ability_name];
                } else {
                    continue; // Skip malformed entries
                }
            } else {
                continue; // Skip non-array, non-string entries
            }
            
            if (empty($ability_name) || $ability_level <= 0) {
                continue; // Skip invalid entries
            }
            
            // Check if this ability has a specialization in specializations object
            if (!$spec && isset($character['specializations'][$ability_name])) {
                $spec = $character['specializations'][$ability_name];
            }
            
            $ability_stmt->bind_param("isis",
                $character_id,
                $ability_name,
                $ability_level,
                $spec
            );
            if (!$ability_stmt->execute()) {
                throw new Exception("Ability insert failed for '{$ability_name}': " . $ability_stmt->error);
            }
            $ability_count++;
        }
    }
    echo "✅ {$ability_count} abilities added\n\n";

    // 5. Insert disciplines (new normalized structure: one row per discipline with max level)
    echo "📝 Inserting disciplines...\n";
    
    $disc_stmt = $conn->prepare("
        INSERT INTO character_disciplines (character_id, discipline_name, level)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        level = VALUES(level)
    ");
    
    if (!$disc_stmt) {
        throw new Exception("Disciplines prepare failed: " . $conn->error);
    }
    
    $discipline_levels = [];
    
    if (!empty($character['disciplines']) && is_array($character['disciplines'])) {
        // Check if disciplines are in flat object format {"animalism": 2, "auspex": 0}
        $is_flat_format = false;
        foreach ($character['disciplines'] as $key => $value) {
            if (is_string($key) && (is_numeric($value) || is_int($value))) {
                $is_flat_format = true;
                break;
            }
        }
        
        if ($is_flat_format) {
            // Format: {"animalism": 2, "auspex": 0, "celerity": 0}
            foreach ($character['disciplines'] as $discipline_name => $level) {
                $level = (int)$level;
                if ($level > 0) {
                    $discipline_name = ucfirst(strtolower($discipline_name));
                    if (!isset($discipline_levels[$discipline_name])) {
                        $discipline_levels[$discipline_name] = [];
                    }
                    $discipline_levels[$discipline_name][] = $level;
                }
            }
        } else {
            // First pass: collect all power levels per discipline (array format)
            foreach ($character['disciplines'] as $discipline) {
                $discipline_name = null;
                $discipline_level = 0;
                
                // Handle different formats
                if (is_array($discipline)) {
                    if (isset($discipline['name'])) {
                        // Format: {"name": "Auspex", "level": 2, "powers": [...]}
                        $discipline_name = $discipline['name'];
                        if (!empty($discipline['powers'])) {
                            foreach ($discipline['powers'] as $power) {
                                $level = (int)($power['level'] ?? 1);
                                if ($level >= 1 && $level <= 5) {
                                    if (!isset($discipline_levels[$discipline_name])) {
                                        $discipline_levels[$discipline_name] = [];
                                    }
                                    $discipline_levels[$discipline_name][] = $level;
                                }
                            }
                        } elseif (isset($discipline['level'])) {
                            // Format: {"name": "Auspex", "level": 2}
                            $discipline_level = (int)$discipline['level'];
                            if ($discipline_level > 0) {
                                if (!isset($discipline_levels[$discipline_name])) {
                                    $discipline_levels[$discipline_name] = [];
                                }
                                $discipline_levels[$discipline_name][] = $discipline_level;
                            }
                        }
                    } elseif (count($discipline) === 1) {
                        // Format: {"Auspex": 2}
                        $discipline_name = array_key_first($discipline);
                        $discipline_level = (int)$discipline[$discipline_name];
                        if ($discipline_level > 0) {
                            if (!isset($discipline_levels[$discipline_name])) {
                                $discipline_levels[$discipline_name] = [];
                            }
                            $discipline_levels[$discipline_name][] = $discipline_level;
                        }
                    }
                }
            }
        }
    }
    
    // Second pass: insert one row per discipline with max level
    $discipline_count = 0;
    foreach ($discipline_levels as $discipline_name => $levels) {
        if (empty($levels)) {
            continue;
        }
        
        $max_level = max($levels);
        
        $disc_stmt->bind_param("isi",
            $character_id,
            $discipline_name,
            $max_level
        );
        
        if (!$disc_stmt->execute()) {
            throw new Exception("Discipline insert failed for '{$discipline_name}': " . $disc_stmt->error);
        }
        $discipline_count++;
    }
    
    echo "✅ {$discipline_count} disciplines added (with max levels)\n\n";

    // 6. Insert backgrounds
    echo "📝 Inserting backgrounds...\n";
    $bg_stmt = $conn->prepare("
        INSERT INTO character_backgrounds (character_id, background_name, level, description)
        VALUES (?, ?, ?, ?)
    ");
    
    if (!$bg_stmt) {
        throw new Exception("Backgrounds prepare failed: " . $conn->error);
    }
    
    $bg_count = 0;
    if (!empty($character['backgrounds']) && is_array($character['backgrounds'])) {
        foreach ($character['backgrounds'] as $name => $level) {
            if (is_int($level) && $level > 0) {
                $details = $character['backgroundDetails'][$name] ?? null;
                $bg_stmt->bind_param("isis", $character_id, $name, $level, $details);
                if (!$bg_stmt->execute()) {
                    throw new Exception("Background insert failed for '{$name}': " . $bg_stmt->error);
                }
                $bg_count++;
            }
        }
    }
    echo "✅ {$bg_count} backgrounds added\n\n";

    // 7. Insert morality
    echo "📝 Inserting morality...\n";
    $moral_stmt = $conn->prepare("
        INSERT INTO character_morality (
            character_id, path_name, path_rating, conscience, 
            self_control, courage, willpower_permanent, willpower_current
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $moral_stmt->bind_param("isiiiiii",
        $character_id,
        $character['morality']['path_name'],
        $character['morality']['path_rating'],
        $character['morality']['conscience'],
        $character['morality']['self_control'],
        $character['morality']['courage'],
        $character['morality']['willpower_permanent'],
        $character['morality']['willpower_current']
    );
    $moral_stmt->execute();
    echo "✅ Morality added\n\n";

    // 8. Insert merits and flaws
    if (!empty($character['merits_flaws']) && is_array($character['merits_flaws'])) {
        echo "📝 Inserting merits/flaws...\n";
        $mf_stmt = $conn->prepare("
            INSERT INTO character_merits_flaws (
                character_id, name, type, category, point_value, point_cost, description
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$mf_stmt) {
            throw new Exception("Merits/flaws prepare failed: " . $conn->error);
        }
        
        $mf_count = 0;
        foreach ($character['merits_flaws'] as $mf) {
            $mf_name = null;
            $mf_type = null;
            $mf_category = null;
            $mf_point_value = 0;
            $mf_description = null;
            
            // Handle different formats
            if (is_array($mf)) {
                if (isset($mf['name']) && isset($mf['type'])) {
                    // Format: {"name": "Natural Leader", "type": "merit", "category": "Social", "cost": 1, "description": "..."}
                    // or with "point_value" instead of "cost"
                    $mf_name = $mf['name'];
                    $mf_type = $mf['type'];
                    $mf_category = $mf['category'] ?? null;
                    $mf_point_value = (int)($mf['cost'] ?? $mf['point_value'] ?? 0);
                    $mf_description = $mf['description'] ?? null;
                } elseif (count($mf) === 1) {
                    // Format: {"Merit": "Calm Heart (3 pt)"} or {"Flaw": "Obsession (2 pt)"}
                    $mf_type = array_key_first($mf);
                    $mf_value = $mf[$mf_type];
                    
                    // Extract name and point value from string like "Calm Heart (3 pt)"
                    if (preg_match('/^(.+?)\s*\((\d+)\s*pt\)/i', $mf_value, $matches)) {
                        $mf_name = trim($matches[1]);
                        $mf_point_value = (int)$matches[2];
                    } else {
                        $mf_name = trim($mf_value);
                        $mf_point_value = 0;
                    }
                    
                    $mf_category = null; // Not available in this format
                    $mf_description = null;
                }
            }
            
            if ($mf_name && $mf_type) {
                // Capitalize first letter of type for ENUM
                $type_capitalized = ucfirst(strtolower($mf_type));
                
                $mf_stmt->bind_param("isssiis",
                    $character_id,
                    $mf_name,
                    $type_capitalized,
                    $mf_category,
                    $mf_point_value, // point_value
                    $mf_point_value, // point_cost
                    $mf_description
                );
                if (!$mf_stmt->execute()) {
                    throw new Exception("Merit/flaw insert failed for '{$mf_name}': " . $mf_stmt->error);
                }
                $mf_count++;
            }
        }
        echo "✅ {$mf_count} merits/flaws added\n\n";
    }

    // Commit transaction
    $conn->commit();
    
    echo "=================================================================\n";
    echo "Import Complete!\n";
    echo "=================================================================\n";
    echo "✅ {$character['character_name']} imported successfully\n";
    echo "   Character ID: $character_id\n";
    echo "=================================================================\n\n";
    echo "🎉 Character is ready to use!\n\n";
    
    exit(0);

} catch (Exception $e) {
    $conn->rollback();
    echo "\n=================================================================\n";
    echo "❌ ERROR: Import failed\n";
    echo "=================================================================\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}

mysqli_close($conn);
?>
