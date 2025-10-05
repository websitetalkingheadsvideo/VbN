<?php
// LOTN Character Save Handler - Test Version (No Login Required)
define('LOTN_VERSION', '0.2.9');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include 'includes/connect.php';

// Test database connection
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Set content type to JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Debug logging
error_log("Save character input: " . $input);
error_log("Decoded data: " . print_r($data, true));

// Debug logging only (no HTML output)

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Use a default user_id for testing (or create a test user)
    $user_id = 1; // Default test user
    
    error_log("Starting character save for user_id: $user_id");
    
    // Insert main character record
    $character_sql = "INSERT INTO characters (
        user_id, character_name, player_name, chronicle, nature, demeanor, 
        concept, clan, generation, sire, pc, biography, equipment, 
        total_xp, spent_xp
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $character_sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }
    
    $pc_value = isset($data['pc']) && $data['pc'] ? 1 : 0;
    mysqli_stmt_bind_param($stmt, 'isssssssissssii', 
        $user_id,
        $data['character_name'] ?? 'Unnamed Character',
        $data['player_name'] ?? 'Unknown Player',
        $data['chronicle'] ?? 'Valley by Night',
        $data['nature'] ?? 'Unknown',
        $data['demeanor'] ?? 'Unknown',
        $data['concept'] ?? 'Unknown',
        $data['clan'] ?? 'Unknown',
        $data['generation'] ?? 13,
        $data['sire'] ?? null,
        $pc_value,
        $data['biography'] ?? null,
        $data['equipment'] ?? null,
        $data['total_xp'] ?? 30,
        $data['spent_xp'] ?? 0
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Failed to execute character insert: " . mysqli_error($conn));
        throw new Exception('Failed to create character: ' . mysqli_error($conn));
    }
    
    $character_id = mysqli_insert_id($conn);
    error_log("Character created with ID: $character_id");
    
    // Save traits
    if (isset($data['traits'])) {
        foreach ($data['traits'] as $category => $traits) {
            if (is_array($traits)) {
                foreach ($traits as $trait) {
                    $trait_sql = "INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type, xp_cost) VALUES (?, ?, ?, 'positive', ?)";
                    $trait_stmt = mysqli_prepare($conn, $trait_sql);
                    $xp_cost = 0; // First 7 traits are free
                    mysqli_stmt_bind_param($trait_stmt, 'issi', $character_id, $trait, $category, $xp_cost);
                    mysqli_stmt_execute($trait_stmt);
                    mysqli_stmt_close($trait_stmt);
                }
            }
        }
    }
    
    // Save negative traits
    if (isset($data['negativeTraits'])) {
        foreach ($data['negativeTraits'] as $category => $traits) {
            if (is_array($traits)) {
                foreach ($traits as $trait) {
                    $trait_sql = "INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type, xp_cost) VALUES (?, ?, ?, 'negative', ?)";
                    $trait_stmt = mysqli_prepare($conn, $trait_sql);
                    $xp_bonus = -4; // Negative traits give +4 XP
                    mysqli_stmt_bind_param($trait_stmt, 'issi', $character_id, $trait, $category, $xp_bonus);
                    mysqli_stmt_execute($trait_stmt);
                    mysqli_stmt_close($trait_stmt);
                }
            }
        }
    }
    
    // Save abilities
    if (isset($data['abilities'])) {
        foreach ($data['abilities'] as $category => $abilities) {
            if (is_array($abilities)) {
                foreach ($abilities as $ability) {
                    $ability_sql = "INSERT INTO character_abilities (character_id, ability_name, specialization, level, xp_cost) VALUES (?, ?, ?, ?, ?)";
                    $ability_stmt = mysqli_prepare($conn, $ability_sql);
                    mysqli_stmt_bind_param($ability_stmt, 'issii', 
                        $character_id, 
                        $ability, 
                        null,
                        1,
                        0
                    );
                    mysqli_stmt_execute($ability_stmt);
                    mysqli_stmt_close($ability_stmt);
                }
            }
        }
    }
    
    // Save disciplines
    if (isset($data['disciplines'])) {
        foreach ($data['disciplines'] as $category => $powers) {
            if (is_array($powers)) {
                foreach ($powers as $power) {
                    $discipline_sql = "INSERT INTO character_disciplines (character_id, discipline_name, level, xp_cost) VALUES (?, ?, ?, ?)";
                    $discipline_stmt = mysqli_prepare($conn, $discipline_sql);
                    $discipline_name = is_array($power) ? $power['name'] : $power;
                    $level = is_array($power) ? $power['level'] : 1;
                    $xp_cost = is_array($power) ? ($power['xp_cost'] ?? 0) : 0;
                    mysqli_stmt_bind_param($discipline_stmt, 'issi', 
                        $character_id, 
                        $discipline_name, 
                        $level,
                        $xp_cost
                    );
                    mysqli_stmt_execute($discipline_stmt);
                    mysqli_stmt_close($discipline_stmt);
                }
            }
        }
    }
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Character saved successfully!',
        'character_id' => $character_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    mysqli_rollback($conn);
    
    // Log the error for debugging
    error_log("Character save error: " . $e->getMessage());
    error_log("MySQL error: " . mysqli_error($conn));
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error saving character: ' . $e->getMessage()
    ]);
} finally {
    mysqli_close($conn);
}
?>
