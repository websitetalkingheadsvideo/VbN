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
    // Get character data - specify only needed columns (avoid SELECT *)
    $character = db_fetch_one($conn,
        "SELECT id, user_id, character_name, player_name, chronicle, nature, demeanor, concept, 
                clan, generation, sire, pc, biography, character_image, equipment, notes, 
                total_xp, spent_xp, created_at, updated_at 
         FROM characters WHERE id = ?",
        "i",
        [$character_id]
    );
    
    if (!$character) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Character not found']);
        exit;
    }
    
    // Get all related data using helper functions
    $traits = db_fetch_all($conn,
        "SELECT id, trait_name, trait_category, trait_type, xp_cost 
         FROM character_traits 
         WHERE character_id = ? 
         ORDER BY trait_category, trait_name",
        "i",
        [$character_id]
    );
    
    $negative_traits = db_fetch_all($conn,
        "SELECT id, trait_name, trait_category, xp_cost 
         FROM character_negative_traits 
         WHERE character_id = ? 
         ORDER BY trait_category, trait_name",
        "i",
        [$character_id]
    );
    
    $abilities = db_fetch_all($conn,
        "SELECT id, ability_name, ability_category, specialization, level, xp_cost 
         FROM character_abilities 
         WHERE character_id = ? 
         ORDER BY level DESC, ability_name",
        "i",
        [$character_id]
    );
    
    $disciplines = db_fetch_all($conn,
        "SELECT id, discipline_name, level, xp_cost 
         FROM character_disciplines 
         WHERE character_id = ? 
         ORDER BY discipline_name, level",
        "i",
        [$character_id]
    );
    
    $backgrounds = db_fetch_all($conn,
        "SELECT id, background_name, level, xp_cost 
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
