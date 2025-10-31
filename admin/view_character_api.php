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

$character_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($character_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid character ID']);
    exit();
}

// Get character with explicit columns using helper function
$character = db_fetch_one($conn,
    "SELECT id, user_id, character_name, player_name, chronicle, nature, demeanor, concept,
            clan, generation, sire, pc, biography, character_image, equipment, notes,
            total_xp, spent_xp, custom_data, created_at, updated_at
     FROM characters WHERE id = ?",
    "i",
    [$character_id]
);

if (!$character) {
    echo json_encode(['success' => false, 'message' => 'Character not found']);
    exit();
}

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
    "SELECT id, trait_name, trait_category, trait_type, xp_cost 
     FROM character_traits WHERE character_id = ?",
    "i",
    [$character_id]
);

$abilities = db_fetch_all($conn,
    "SELECT id, ability_name, ability_category, specialization, level, xp_cost 
     FROM character_abilities WHERE character_id = ?",
    "i",
    [$character_id]
);

$disciplines = db_fetch_all($conn,
    "SELECT id, discipline_name, level, xp_cost 
     FROM character_disciplines WHERE character_id = ?",
    "i",
    [$character_id]
);

$backgrounds = db_fetch_all($conn,
    "SELECT id, background_name, level, xp_cost 
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
    "SELECT id, name, type, category, point_value, description, xp_bonus 
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

echo json_encode([
    'success' => true,
    'character' => array_merge($character, [ 'clan_logo_url' => $clan_logo_url ]),
    'traits' => $traits,
    'abilities' => $abilities,
    'disciplines' => $disciplines,
    'backgrounds' => $backgrounds,
    'morality' => $morality,
    'merits_flaws' => $merits_flaws,
    'status' => $status,
    'coteries' => $coteries,
    'relationships' => $relationship_data
]);

mysqli_close($conn);
?>

