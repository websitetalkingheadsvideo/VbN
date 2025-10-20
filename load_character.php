<?php
/**
 * Load Character API
 * Returns character data as JSON for editing
 * Usage: load_character.php?id=42
 */

header('Content-Type: application/json');
require_once __DIR__ . '/includes/connect.php';

$character_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$character_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No character ID provided']);
    exit;
}

try {
    // Get character data
    $char_query = "SELECT * FROM characters WHERE id = ?";
    $stmt = $conn->prepare($char_query);
    $stmt->bind_param("i", $character_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Character not found']);
        exit;
    }
    
    $character = $result->fetch_assoc();
    
    // Get all related data
    $traits_query = "SELECT * FROM character_traits WHERE character_id = ? ORDER BY trait_category, trait_name";
    $stmt = $conn->prepare($traits_query);
    $stmt->bind_param("i", $character_id);
    $stmt->execute();
    $traits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $neg_traits_query = "SELECT * FROM character_negative_traits WHERE character_id = ? ORDER BY trait_category, trait_name";
    $stmt = $conn->prepare($neg_traits_query);
    $stmt->bind_param("i", $character_id);
    $stmt->execute();
    $negative_traits = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $abilities_query = "SELECT * FROM character_abilities WHERE character_id = ? ORDER BY level DESC, ability_name";
    $stmt = $conn->prepare($abilities_query);
    $stmt->bind_param("i", $character_id);
    $stmt->execute();
    $abilities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $disciplines_query = "SELECT * FROM character_disciplines WHERE character_id = ? ORDER BY discipline_name, level";
    $stmt = $conn->prepare($disciplines_query);
    $stmt->bind_param("i", $character_id);
    $stmt->execute();
    $disciplines = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $backgrounds_query = "SELECT * FROM character_backgrounds WHERE character_id = ? ORDER BY level DESC";
    $stmt = $conn->prepare($backgrounds_query);
    $stmt->bind_param("i", $character_id);
    $stmt->execute();
    $backgrounds = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $morality_query = "SELECT * FROM character_morality WHERE character_id = ?";
    $stmt = $conn->prepare($morality_query);
    $stmt->bind_param("i", $character_id);
    $stmt->execute();
    $morality_result = $stmt->get_result();
    $morality = $morality_result->num_rows > 0 ? $morality_result->fetch_assoc() : null;
    
    $merits_flaws_query = "SELECT * FROM character_merits_flaws WHERE character_id = ? ORDER BY type, category";
    $stmt = $conn->prepare($merits_flaws_query);
    $stmt->bind_param("i", $character_id);
    $stmt->execute();
    $merits_flaws = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Organize traits by category
    $trait_categories = ['Physical' => [], 'Social' => [], 'Mental' => []];
    foreach ($traits as $trait) {
        $trait_categories[$trait['trait_category']][] = $trait['trait_name'];
    }
    
    $neg_trait_categories = ['Physical' => [], 'Social' => [], 'Mental' => []];
    foreach ($negative_traits as $trait) {
        $neg_trait_categories[$trait['trait_category']][] = $trait['trait_name'];
    }
    
    // Organize abilities by category
    $ability_categories = ['Physical' => [], 'Social' => [], 'Mental' => [], 'Optional' => []];
    foreach ($abilities as $ability) {
        $category = $ability['ability_category'] ?? 'Optional';
        $ability_categories[$category][] = $ability['ability_name'];
    }
    
    // Group disciplines by name
    $discipline_groups = [];
    foreach ($disciplines as $disc) {
        $discipline_groups[$disc['discipline_name']][] = $disc;
    }
    
    // Determine if character is PC or NPC based on player_name
    $character['is_pc'] = ($character['player_name'] !== 'NPC');
    
    // Prepare response
    $response = [
        'success' => true,
        'character' => $character,
        'traits' => $trait_categories,
        'negative_traits' => $neg_trait_categories,
        'abilities' => $ability_categories,
        'disciplines' => $discipline_groups,
        'backgrounds' => $backgrounds,
        'morality' => $morality,
        'merits_flaws' => $merits_flaws
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
