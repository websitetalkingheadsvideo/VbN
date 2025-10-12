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

// Get character
$char_query = "SELECT * FROM characters WHERE id = ?";
$stmt = mysqli_prepare($conn, $char_query);
mysqli_stmt_bind_param($stmt, "i", $character_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$character = mysqli_fetch_assoc($result);

if (!$character) {
    echo json_encode(['success' => false, 'message' => 'Character not found']);
    exit();
}

// Get traits
$traits_query = "SELECT * FROM character_traits WHERE character_id = ?";
$stmt = mysqli_prepare($conn, $traits_query);
mysqli_stmt_bind_param($stmt, "i", $character_id);
mysqli_stmt_execute($stmt);
$traits_result = mysqli_stmt_get_result($stmt);
$traits = mysqli_fetch_all($traits_result, MYSQLI_ASSOC);

// Get abilities
$abilities_query = "SELECT * FROM character_abilities WHERE character_id = ?";
$stmt = mysqli_prepare($conn, $abilities_query);
mysqli_stmt_bind_param($stmt, "i", $character_id);
mysqli_stmt_execute($stmt);
$abilities_result = mysqli_stmt_get_result($stmt);
$abilities = mysqli_fetch_all($abilities_result, MYSQLI_ASSOC);

// Get disciplines
$disciplines_query = "SELECT * FROM character_disciplines WHERE character_id = ?";
$stmt = mysqli_prepare($conn, $disciplines_query);
mysqli_stmt_bind_param($stmt, "i", $character_id);
mysqli_stmt_execute($stmt);
$disciplines_result = mysqli_stmt_get_result($stmt);
$disciplines = mysqli_fetch_all($disciplines_result, MYSQLI_ASSOC);

// Get backgrounds
$backgrounds_query = "SELECT * FROM character_backgrounds WHERE character_id = ?";
$stmt = mysqli_prepare($conn, $backgrounds_query);
mysqli_stmt_bind_param($stmt, "i", $character_id);
mysqli_stmt_execute($stmt);
$backgrounds_result = mysqli_stmt_get_result($stmt);
$backgrounds = mysqli_fetch_all($backgrounds_result, MYSQLI_ASSOC);

// Get morality
$morality_query = "SELECT * FROM character_morality WHERE character_id = ?";
$stmt = mysqli_prepare($conn, $morality_query);
mysqli_stmt_bind_param($stmt, "i", $character_id);
mysqli_stmt_execute($stmt);
$morality_result = mysqli_stmt_get_result($stmt);
$morality = mysqli_fetch_assoc($morality_result);

// Get merits/flaws
$merits_query = "SELECT * FROM character_merits_flaws WHERE character_id = ?";
$stmt = mysqli_prepare($conn, $merits_query);
mysqli_stmt_bind_param($stmt, "i", $character_id);
mysqli_stmt_execute($stmt);
$merits_result = mysqli_stmt_get_result($stmt);
$merits_flaws = mysqli_fetch_all($merits_result, MYSQLI_ASSOC);

// Get status
$status_query = "SELECT * FROM character_status WHERE character_id = ?";
$stmt = mysqli_prepare($conn, $status_query);
mysqli_stmt_bind_param($stmt, "i", $character_id);
mysqli_stmt_execute($stmt);
$status_result = mysqli_stmt_get_result($stmt);
$status = mysqli_fetch_assoc($status_result);

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

