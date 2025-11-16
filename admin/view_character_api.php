<?php
/**
 * View Character API
 * Returns character data with all related tables
 */
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/urls.php';
require_once __DIR__ . '/../includes/discipline_functions.php';

$character_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($character_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid character ID']);
    exit();
}

// Get character with explicit columns using helper function
$character = db_fetch_one($conn,
    "SELECT id, user_id, character_name, player_name, chronicle, nature, demeanor, concept,
            clan, generation, sire, pc, biography, appearance, character_image, equipment, notes,
            total_xp, spent_xp, custom_data, status AS current_state, camarilla_status,
            created_at, updated_at
     FROM characters WHERE id = ?",
    "i",
    [$character_id]
);

if (!$character) {
    echo json_encode(['success' => false, 'message' => 'Character not found']);
    exit();
}

$valid_states = ['active', 'inactive', 'archived'];
$character['current_state'] = strtolower($character['current_state'] ?? 'active');
if (!in_array($character['current_state'], $valid_states, true)) {
    $character['current_state'] = 'active';
}

$valid_camarilla = ['Camarilla', 'Anarch', 'Independent', 'Sabbat', 'Unknown'];
$camarilla_state = $character['camarilla_status'] ?? 'Unknown';
$camarilla_state = $camarilla_state ? ucfirst(strtolower($camarilla_state)) : 'Unknown';
if (!in_array($camarilla_state, $valid_camarilla, true)) {
    $camarilla_state = 'Unknown';
}
$character['camarilla_status'] = $camarilla_state;

// Resolve clan logo URL from DB mapping (relative to admin path)
$clan_logo_url = null;
if (!empty($character['clan'])) {
    $clanRow = db_fetch_one($conn,
        "SELECT logo_filename FROM clans WHERE LOWER(name) = LOWER(?) LIMIT 1",
        "s",
        [$character['clan']]
    );
    if ($clanRow && !empty($clanRow['logo_filename'])) {
        $clan_logo_url = rtrim(VBN_BASE_URL, '/') . '/images/Clan%20Logos/' . $clanRow['logo_filename'];
    }
}

// Get all related data using helper functions with explicit columns
$traits = db_fetch_all($conn,
    "SELECT id, trait_name, trait_category, trait_type 
     FROM character_traits WHERE character_id = ?",
    "i",
    [$character_id]
);

// Get abilities and look up their categories from abilities_master table
// First check if abilities exist directly (no join, simpler query)
$abilities_raw = db_fetch_all($conn,
    "SELECT ca.id, ca.ability_name, ca.specialization, ca.level 
     FROM character_abilities ca 
     WHERE ca.character_id = ?",
    "i",
    [$character_id]
);

// Debug: Check if query returned results
if (empty($abilities_raw)) {
    // Try a direct query to see if abilities exist at all for this character
    $direct_check = mysqli_query($conn, 
        "SELECT COUNT(*) as count FROM character_abilities WHERE character_id = " . intval($character_id)
    );
    if ($direct_check) {
        $count_row = mysqli_fetch_assoc($direct_check);
        if ($count_row['count'] > 0) {
            // Abilities exist but db_fetch_all returned empty - likely a query issue
            error_log("WARNING: Character {$character_id} has {$count_row['count']} abilities but db_fetch_all returned empty");
            // Try fetching without prepared statement
            $direct_result = mysqli_query($conn,
                "SELECT id, ability_name, specialization, level 
                 FROM character_abilities 
                 WHERE character_id = " . intval($character_id)
            );
            if ($direct_result) {
                $abilities_raw = [];
                while ($row = mysqli_fetch_assoc($direct_result)) {
                    $abilities_raw[] = $row;
                }
                mysqli_free_result($direct_result);
            }
        }
    }
}

// Look up categories from abilities_master table
$abilities = [];
foreach ($abilities_raw as $ability) {
    if (!isset($ability['ability_name'])) {
        continue;
    }
    
    // Look up category from abilities_master
    $category = db_fetch_one($conn,
        "SELECT category FROM abilities_master WHERE name = ? LIMIT 1",
        "s",
        [$ability['ability_name']]
    );
    
    // If not in abilities_master, try to infer from common patterns or default to Optional
    $resolved_category = 'Optional';
    if ($category && isset($category['category'])) {
        $resolved_category = $category['category'];
    }
    
    $abilities[] = [
        'id' => $ability['id'],
        'ability_name' => $ability['ability_name'],
        'ability_category' => $resolved_category,
        'specialization' => $ability['specialization'] ?? null,
        'level' => isset($ability['level']) ? intval($ability['level']) : 1
    ];
}


// Get disciplines using helper function (includes powers)
$all_disciplines_data = getCharacterAllDisciplines($character_id);

// Convert to format expected by admin panel (simple list with powers info)
$disciplines = [];
foreach ($all_disciplines_data as $disc_name => $disc_data) {
    $disciplines[] = [
        'id' => 0, // Not needed for display
        'discipline_name' => $disc_name,
        'level' => $disc_data['level'],
        'powers' => $disc_data['powers'], // Include powers for detailed display
        'power_count' => count($disc_data['powers']),
        'is_custom' => $disc_data['is_custom']
    ];
}

$backgrounds = db_fetch_all($conn, 
    "SELECT id, background_name, level 
     FROM character_backgrounds WHERE character_id = ?",
    "i",
    [$character_id]
);

$morality = db_fetch_one($conn,
    "SELECT id, path_name, path_rating, conscience, self_control, courage,
            willpower_permanent, willpower_current, humanity 
     FROM character_morality WHERE character_id = ?",
    "i",
    [$character_id]
);

$merits_flaws = db_fetch_all($conn,
    "SELECT id, name, type, category, point_value, description
     FROM character_merits_flaws WHERE character_id = ?",
    "i",
    [$character_id]
);

$status = db_fetch_one($conn,
    "SELECT id, sect_status, clan_status, city_status, health_levels,
            blood_pool_current, blood_pool_maximum 
     FROM character_status WHERE character_id = ?",
    "i",
    [$character_id]
);

$status_details = $status ?: [];
$status_details['current_state'] = $character['current_state'];
$status_details['camarilla_status'] = $character['camarilla_status'];

$coteries = db_fetch_all($conn,
    "SELECT id, coterie_name, coterie_type, role, description, notes
     FROM character_coteries WHERE character_id = ?",
    "i",
    [$character_id]
);

$relationships = db_fetch_all($conn,
    "SELECT id, related_character_id, related_character_name, relationship_type, 
            relationship_subtype, strength, description
     FROM character_relationships WHERE character_id = ?",
    "i",
    [$character_id]
);

// Get related character names if only ID is present
$relationship_data = [];
foreach ($relationships as $rel) {
    if ($rel['related_character_id'] && empty($rel['related_character_name'])) {
        $target_char = db_fetch_one($conn,
            "SELECT character_name FROM characters WHERE id = ?",
            "i",
            [$rel['related_character_id']]
        );
        $rel['related_character_name'] = $target_char ? $target_char['character_name'] : 'Unknown';
    }
    $relationship_data[] = $rel;
}

header('Content-Type: application/json; charset=utf-8');

$responsePayload = [
    'success' => true,
    'character' => array_merge($character, [
        'clan_logo_url' => $clan_logo_url,
        'current_state' => $character['current_state'],
        'camarilla_status' => $character['camarilla_status']
    ]),
    'traits' => $traits,
    'abilities' => $abilities,
    'disciplines' => $disciplines,
    'backgrounds' => $backgrounds,
    'morality' => $morality,
    'merits_flaws' => $merits_flaws,
    'status' => $status_details,
    'coteries' => $coteries,
    'relationships' => $relationship_data
];

$json = json_encode($responsePayload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

if ($json === false) {
    error_log('view_character_api: json_encode failed - ' . json_last_error_msg());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Unable to encode character data. Please check character content for invalid characters.'
    ], JSON_UNESCAPED_UNICODE);
    mysqli_close($conn);
    exit();
}

echo $json;

mysqli_close($conn);
