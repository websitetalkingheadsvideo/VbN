<?php
/**
 * Get Character Equipment API
 * GET /api_get_equipment.php?character_id=X
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once 'includes/connect.php';

if (!isset($_GET['character_id']) || !is_numeric($_GET['character_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid character ID'
    ]);
    exit;
}

$characterId = (int)$_GET['character_id'];

try {
    // First check if character exists
    $char_check = $conn->query("SELECT id FROM characters WHERE id = $characterId");
    if ($char_check->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Character not found',
            'equipment' => []
        ]);
        exit;
    }
    
    // Get character equipment with item details
    $query = "
        SELECT 
            ce.*,
            i.name,
            i.type,
            i.category,
            i.damage,
            i.range,
            i.requirements,
            i.description,
            i.rarity,
            i.price,
            i.notes
        FROM character_equipment ce
        INNER JOIN items i ON ce.item_id = i.id
        WHERE ce.character_id = ?
        ORDER BY i.category, i.name
    ";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Query preparation failed: ' . $conn->error);
    }
    
    $stmt->bind_param('i', $characterId);
    
    if (!$stmt->execute()) {
        throw new Exception('Query execution failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    $equipment = [];
    while ($row = $result->fetch_assoc()) {
        // Decode JSON fields
        $row['requirements'] = json_decode($row['requirements'], true);
        $row['mechanics'] = json_decode($row['mechanics'], true);
        $equipment[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'equipment' => $equipment
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'equipment' => []
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?>
