<?php
/**
 * Delete Character API
 * Handles character deletion with CASCADE to all related tables
 */
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Include database
require_once '../includes/connect.php';

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$character_id = isset($input['character_id']) ? intval($input['character_id']) : 0;

if ($character_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid character ID']);
    exit();
}

// Start transaction using helper function
db_begin_transaction($conn);

try {
    // Delete from all related tables (CASCADE will handle most, but being explicit)
    $tables = [
        'character_traits',
        'character_negative_traits',
        'character_abilities',
        'character_ability_specializations',
        'character_disciplines',
        'character_backgrounds',
        'character_merits_flaws',
        'character_morality',
        'character_derangements',
        'character_equipment',
        'character_influences',
        'character_rituals',
        'character_status'
    ];
    
    foreach ($tables as $table) {
        db_execute("DELETE FROM $table WHERE character_id = ?", [$character_id], 'i');
    }
    
    // Finally delete the character itself
    $affected = db_execute("DELETE FROM characters WHERE id = ?", [$character_id], 'i');
    
    if ($affected > 0) {
        db_commit($conn);
        echo json_encode([
            'success' => true, 
            'message' => 'Character deleted successfully',
            'character_id' => $character_id
        ]);
    } else {
        db_rollback($conn);
        echo json_encode([
            'success' => false, 
            'message' => 'Character not found'
        ]);
    }
    
} catch (Exception $e) {
    db_rollback($conn);
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>

