<?php
/**
 * NPC Briefing API
 * Returns comprehensive NPC data for agent briefing display
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

// Get character basic info
$char_query = "SELECT 
    id,
    character_name,
    nature,
    demeanor,
    concept,
    clan,
    generation,
    sire,
    biography,
    agentNotes,
    actingNotes,
    player_name
FROM characters 
WHERE id = ?";

$stmt = mysqli_prepare($conn, $char_query);
mysqli_stmt_bind_param($stmt, "i", $character_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$character = mysqli_fetch_assoc($result);

if (!$character) {
    echo json_encode(['success' => false, 'message' => 'Character not found']);
    exit();
}

// Get traits organized by category
$traits_query = "SELECT trait_name, category, is_negative 
                 FROM character_traits 
                 WHERE character_id = ? 
                 ORDER BY category, trait_name";
$stmt = mysqli_prepare($conn, $traits_query);
mysqli_stmt_bind_param($stmt, "i", $character_id);
mysqli_stmt_execute($stmt);
$traits_result = mysqli_stmt_get_result($stmt);

$traits = [
    'physical' => [],
    'social' => [],
    'mental' => []
];

while ($trait = mysqli_fetch_assoc($traits_result)) {
    $category = strtolower($trait['category']);
    if (isset($traits[$category])) {
        $traits[$category][] = [
            'name' => $trait['trait_name'],
            'is_negative' => (bool)$trait['is_negative']
        ];
    }
}

// Get abilities with levels
$abilities_query = "SELECT ability_name, level, specialization 
                    FROM character_abilities 
                    WHERE character_id = ? AND level > 0
                    ORDER BY level DESC, ability_name";
$stmt = mysqli_prepare($conn, $abilities_query);
mysqli_stmt_bind_param($stmt, "i", $character_id);
mysqli_stmt_execute($stmt);
$abilities_result = mysqli_stmt_get_result($stmt);
$abilities = mysqli_fetch_all($abilities_result, MYSQLI_ASSOC);

// Get disciplines
$disciplines_query = "SELECT discipline_name, level 
                      FROM character_disciplines 
                      WHERE character_id = ? AND level > 0
                      ORDER BY level DESC, discipline_name";
$stmt = mysqli_prepare($conn, $disciplines_query);
mysqli_stmt_bind_param($stmt, "i", $character_id);
mysqli_stmt_execute($stmt);
$disciplines_result = mysqli_stmt_get_result($stmt);
$disciplines = mysqli_fetch_all($disciplines_result, MYSQLI_ASSOC);

// Get backgrounds
$backgrounds_query = "SELECT background_name, level, description 
                      FROM character_backgrounds 
                      WHERE character_id = ? AND level > 0
                      ORDER BY level DESC, background_name";
$stmt = mysqli_prepare($conn, $backgrounds_query);
mysqli_stmt_bind_param($stmt, "i", $character_id);
mysqli_stmt_execute($stmt);
$backgrounds_result = mysqli_stmt_get_result($stmt);
$backgrounds = mysqli_fetch_all($backgrounds_result, MYSQLI_ASSOC);

echo json_encode([
    'success' => true,
    'character' => $character,
    'traits' => $traits,
    'abilities' => $abilities,
    'disciplines' => $disciplines,
    'backgrounds' => $backgrounds
]);

mysqli_close($conn);
?>

