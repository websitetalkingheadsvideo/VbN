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

$character_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($character_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid character ID']);
    exit();
}

// Get character with explicit columns using helper function
$character = db_fetch_one($conn,
    "SELECT id, user_id, character_name, player_name, chronicle, nature, demeanor, concept,
            clan, generation, sire, pc, biography, character_image, equipment, notes,
            total_xp, spent_xp, created_at, updated_at
     FROM characters WHERE id = ?",
    "i",
    [$character_id]
);

if (!$character) {
    echo json_encode(['success' => false, 'message' => 'Character not found']);
    exit();
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

echo json_encode([
    'success' => true,
    'character' => $character,
    'traits' => $traits,
    'abilities' => $abilities,
    'disciplines' => $disciplines,
    'backgrounds' => $backgrounds,
    'morality' => $morality,
    'merits_flaws' => $merits_flaws,
    'status' => $status
]);

mysqli_close($conn);
?>

