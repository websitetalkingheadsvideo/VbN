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

// Start transaction
mysqli_begin_transaction($conn);

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
        $delete_query = "DELETE FROM $table WHERE character_id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $character_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    // Finally delete the character itself
    $delete_char = "DELETE FROM characters WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_char);
    mysqli_stmt_bind_param($stmt, "i", $character_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $affected = mysqli_stmt_affected_rows($stmt);
        
        if ($affected > 0) {
            mysqli_commit($conn);
            echo json_encode([
                'success' => true, 
                'message' => 'Character deleted successfully',
                'character_id' => $character_id
            ]);
        } else {
            mysqli_rollback($conn);
            echo json_encode([
                'success' => false, 
                'message' => 'Character not found'
            ]);
        }
    } else {
        mysqli_rollback($conn);
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: ' . mysqli_error($conn)
        ]);
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>

