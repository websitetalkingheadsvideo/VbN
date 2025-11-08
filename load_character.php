<?php
/**
 * Load Character API
 * Returns character data as JSON for editing
 * Usage: load_character.php?id=42
 */

header('Content-Type: application/json');
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/discipline_functions.php';

$character_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$character_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No character ID provided']);
    exit;
}

try {
    // Get character data - specify only needed columns (avoid SELECT *)
    $character = db_fetch_one($conn,
        "SELECT id, user_id, character_name, player_name, chronicle, nature, demeanor, concept, 
                clan, generation, sire, pc, appearance, biography, character_image, equipment, notes, 
                total_xp, spent_xp, status, camarilla_status, created_at, updated_at 
         FROM characters WHERE id = ?",
        "i",
        [$character_id]
    );
    
    if (!$character) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Character not found']);
        exit;
    }

    $valid_states = ['active', 'inactive', 'archived'];
    $lifecycle_state = strtolower($character['status'] ?? $character['current_state'] ?? 'active');
    if (!in_array($lifecycle_state, $valid_states, true)) {
        $lifecycle_state = 'active';
    }
    $character['status'] = $lifecycle_state;
    $character['current_state'] = $lifecycle_state;

    $valid_camarilla = ['Camarilla', 'Anarch', 'Independent', 'Sabbat', 'Unknown'];
    $camarilla = $character['camarilla_status'] ?? 'Unknown';
    $camarilla = $camarilla ? ucfirst(strtolower($camarilla)) : 'Unknown';
    if (!in_array($camarilla, $valid_camarilla, true)) {
        $camarilla = 'Unknown';
    }
    $character['camarilla_status'] = $camarilla;
    
    // Get all related data using helper functions
    $traits = db_fetch_all($conn,
        "SELECT id, trait_name, trait_category, trait_type 
         FROM character_traits 
         WHERE character_id = ? 
         ORDER BY trait_category, trait_name",
        "i",
        [$character_id]
    );
    
    $negative_traits = db_fetch_all($conn,
        "SELECT id, trait_name, trait_category 
         FROM character_negative_traits 
         WHERE character_id = ? 
         ORDER BY trait_category, trait_name",
        "i",
        [$character_id]
    );
    
    // Get abilities and look up their categories from abilities_master table
    // Use direct query as primary method since prepared statements may have issues
    $abilities_raw = [];
    $sanitized_id = intval($character_id);
    
    $direct_query = "SELECT id, ability_name, specialization, level 
                     FROM character_abilities 
                     WHERE character_id = $sanitized_id
                     ORDER BY level DESC, ability_name";
    
    $direct_result = mysqli_query($conn, $direct_query);
    
    if (!$direct_result) {
        error_log("load_character.php - Direct query FAILED: " . mysqli_error($conn));
        error_log("load_character.php - Query was: " . $direct_query);
    } else {
        $row_count = mysqli_num_rows($direct_result);
        error_log("load_character.php - Direct query returned $row_count rows for character {$character_id}");
        
        if ($row_count > 0) {
            while ($row = mysqli_fetch_assoc($direct_result)) {
                $abilities_raw[] = $row;
            }
            mysqli_free_result($direct_result);
            error_log("load_character.php - Direct query SUCCESS: Found " . count($abilities_raw) . " abilities");
        } else {
            mysqli_free_result($direct_result);
            error_log("load_character.php - Direct query returned 0 rows (abilities may not exist for this character)");
        }
    }
    
    if (empty($abilities_raw)) {
        // Fallback to prepared statement if direct query fails
        error_log("load_character.php - Direct query returned 0 rows, trying prepared statement");
        $abilities_raw = db_fetch_all($conn,
            "SELECT ca.id, ca.ability_name, ca.specialization, ca.level 
             FROM character_abilities ca 
             WHERE ca.character_id = ? 
             ORDER BY ca.level DESC, ca.ability_name",
            "i",
            [$character_id]
        );
        
        if (!empty($abilities_raw)) {
            error_log("load_character.php - Prepared statement SUCCESS: Found " . count($abilities_raw) . " abilities");
        } else {
            error_log("load_character.php - Both queries returned empty for character {$character_id}");
        }
    }
    
    // Look up categories from abilities_master table
    $abilities = [];
    foreach ($abilities_raw as $ability) {
        $category = db_fetch_one($conn,
            "SELECT category FROM abilities_master WHERE name = ? LIMIT 1",
            "s",
            [$ability['ability_name']]
        );
        
        $abilities[] = [
            'id' => $ability['id'],
            'ability_name' => $ability['ability_name'],
            'ability_category' => $category ? $category['category'] : 'Optional',
            'specialization' => $ability['specialization'] ?? null,
            'level' => isset($ability['level']) ? intval($ability['level']) : 1
        ];
    }
    
    // Get disciplines using helper function (already normalized structure)
    $all_disciplines_data = getCharacterAllDisciplines($character_id);
    
    $backgrounds = db_fetch_all($conn,
        "SELECT id, background_name, level 
         FROM character_backgrounds 
         WHERE character_id = ? 
         ORDER BY level DESC",
        "i",
        [$character_id]
    );
    
    $morality = db_fetch_one($conn,
        "SELECT id, path_name, path_rating, conscience, self_control, courage, 
                willpower_permanent, willpower_current, humanity 
         FROM character_morality 
         WHERE character_id = ?",
        "i",
        [$character_id]
    );
    
    $merits_flaws = db_fetch_all($conn,
        "SELECT id, name, type, category, point_value, description, xp_bonus 
         FROM character_merits_flaws 
         WHERE character_id = ? 
         ORDER BY type, category",
        "i",
        [$character_id]
    );
    
    // Organize traits by category
    $trait_categories = ['Physical' => [], 'Social' => [], 'Mental' => []];
    foreach ($traits as $trait) {
        $trait_categories[$trait['trait_category']][] = $trait['trait_name'];
    }
    
    $neg_trait_categories = ['Physical' => [], 'Social' => [], 'Mental' => []];
    foreach ($negative_traits as $trait) {
        $neg_trait_categories[$trait['trait_category']][] = $trait['trait_name'];
    }
    
    // Organize abilities by category for backward compatibility (ability names only)
    $ability_categories = ['Physical' => [], 'Social' => [], 'Mental' => [], 'Optional' => []];
    
    // Debug: Check if abilities were found
    error_log("load_character.php - Character ID: $character_id");
    error_log("load_character.php - abilities_raw count: " . count($abilities_raw));
    error_log("load_character.php - abilities count after category lookup: " . count($abilities));
    
    if (count($abilities) > 0) {
        error_log("load_character.php - First ability: " . json_encode($abilities[0]));
        
        foreach ($abilities as $ability) {
            $category = $ability['ability_category'] ?? 'Optional';
            $ability_categories[$category][] = $ability['ability_name'];
        }
        
        // Also provide full ability data with levels, specializations, etc. for character sheet
        $abilities_full = [];
        foreach ($abilities as $ability) {
            $abilities_full[] = [
                'ability_name' => $ability['ability_name'],
                'ability_category' => $ability['ability_category'] ?? 'Optional',
                'specialization' => $ability['specialization'] ?? null,
                'level' => isset($ability['level']) ? intval($ability['level']) : 1
            ];
        }
        error_log("load_character.php - abilities_full count: " . count($abilities_full));
        error_log("load_character.php - abilities_full sample: " . json_encode($abilities_full[0] ?? null));
    } else {
        // No abilities found - but check if they exist in database
        $check_query = "SELECT COUNT(*) as count FROM character_abilities WHERE character_id = ?";
        $check_result = db_fetch_one($conn, $check_query, "i", [$character_id]);
        if ($check_result && $check_result['count'] > 0) {
            error_log("load_character.php - ERROR: Database has {$check_result['count']} abilities but processing returned 0");
            error_log("load_character.php - abilities_raw was: " . json_encode($abilities_raw));
        } else {
            error_log("load_character.php - Character {$character_id} truly has no abilities in database");
        }
        $abilities_full = [];
    }
    
    // Format disciplines for frontend: 
    // - disciplines: array of discipline names
    // - disciplinePowers: object with discipline names as keys, arrays of power levels (1 through level) as values
    $discipline_names = [];
    $discipline_powers_obj = [];
    
    foreach ($all_disciplines_data as $disc_name => $disc_data) {
        $discipline_names[] = $disc_name;
        // Build array of power levels from 1 to character's level
        $power_levels = [];
        for ($i = 1; $i <= $disc_data['level']; $i++) {
            $power_levels[] = $i;
        }
        $discipline_powers_obj[$disc_name] = $power_levels;
    }
    
    // Determine if character is PC or NPC based on player_name
    $character['is_pc'] = ($character['player_name'] !== 'NPC');
    
    // Prepare response
    // Debug: Log what we're about to return
    error_log("load_character.php - FINAL: abilities_full count = " . count($abilities_full));
    error_log("load_character.php - FINAL: abilities (categories) = Physical:" . count($ability_categories['Physical']) . 
        ", Social:" . count($ability_categories['Social']) . 
        ", Mental:" . count($ability_categories['Mental']) . 
        ", Optional:" . count($ability_categories['Optional']));
    
    $response = [
        'success' => true,
        'character' => $character,
        'traits' => $trait_categories,
        'negative_traits' => $neg_trait_categories,
        'abilities' => $ability_categories, // Backward compatibility: category-based arrays
        'abilities_full' => $abilities_full, // Full ability data with levels, specializations, etc.
        'disciplines' => $discipline_names,
        'disciplinePowers' => $discipline_powers_obj,
        'backgrounds' => $backgrounds,
        'morality' => $morality,
        'merits_flaws' => $merits_flaws,
        'status_meta' => [
            'status' => $character['status'],
            'current_state' => $character['status'],
            'camarilla_status' => $character['camarilla_status']
        ]
    ];
    
    error_log("load_character.php - Response includes abilities_full: " . (isset($response['abilities_full']) ? 'YES' : 'NO'));
    error_log("load_character.php - abilities_full is array: " . (is_array($response['abilities_full']) ? 'YES' : 'NO'));
    error_log("load_character.php - abilities_full length: " . (is_array($response['abilities_full']) ? count($response['abilities_full']) : 'N/A'));
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
