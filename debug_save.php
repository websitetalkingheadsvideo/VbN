<?php
// Debug save script - shows all errors and responses
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to output
ini_set('log_errors', 1);

// Start output buffering to catch any unexpected output
ob_start();

// Set content type
header('Content-Type: application/json');

// Check request method (only when called via HTTP)
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Test database connection
include 'includes/connect.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get JSON data
$input = file_get_contents('php://input');

if (empty($input)) {
    echo json_encode(['success' => false, 'message' => 'No input data received']);
    exit();
}

$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data. Error: ' . json_last_error_msg()]);
    exit();
}

// Actually save the character to database
try {
    $user_id = 1; // Default test user
    
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
        throw new Exception('Failed to create character: ' . mysqli_error($conn));
    }
    
    $character_id = mysqli_insert_id($conn);
    
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
    
    echo json_encode([
        'success' => true, 
        'message' => 'Character saved successfully!',
        'character_id' => $character_id,
        'character_name' => $data['character_name'] ?? 'Unnamed Character'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error saving character: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

// Clean up any buffered output and ensure only JSON is sent
$output = ob_get_clean();
if (!empty($output)) {
    // If there was unexpected output, log it and send error
    error_log("Unexpected output in debug_save.php: " . $output);
    echo json_encode(['success' => false, 'message' => 'Server error - unexpected output']);
}
?>
