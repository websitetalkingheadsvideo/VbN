<?php
// LOTN Character Save Handler - Version 0.2.0
define('LOTN_VERSION', '0.3.0');

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

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

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    $user_id = $_SESSION['user_id'];
    
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
    
    $pc_value = $data['pc'] ? 1 : 0;
    mysqli_stmt_bind_param($stmt, 'isssssssissssii', 
        $user_id,
        $data['character_name'],
        $data['player_name'],
        $data['chronicle'] ?? 'Valley by Night',
        $data['nature'],
        $data['demeanor'],
        $data['concept'],
        $data['clan'],
        $data['generation'],
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
    
    // Save negative traits
    if (isset($data['negativeTraits'])) {
        foreach ($data['negativeTraits'] as $category => $traits) {
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
    
    // Save abilities
    if (isset($data['abilities'])) {
        foreach ($data['abilities'] as $ability) {
            $ability_sql = "INSERT INTO character_abilities (character_id, ability_name, specialization, level, xp_cost) VALUES (?, ?, ?, ?, ?)";
            $ability_stmt = mysqli_prepare($conn, $ability_sql);
            mysqli_stmt_bind_param($ability_stmt, 'issii', 
                $character_id, 
                $ability['name'], 
                $ability['specialization'] ?? null,
                $ability['level'] ?? 1,
                $ability['xp_cost'] ?? 0
            );
            mysqli_stmt_execute($ability_stmt);
            mysqli_stmt_close($ability_stmt);
        }
    }
    
    // Save disciplines
    if (isset($data['disciplines'])) {
        foreach ($data['disciplines'] as $discipline) {
            $discipline_sql = "INSERT INTO character_disciplines (character_id, discipline_name, level, xp_cost) VALUES (?, ?, ?, ?)";
            $discipline_stmt = mysqli_prepare($conn, $discipline_sql);
            mysqli_stmt_bind_param($discipline_stmt, 'issi', 
                $character_id, 
                $discipline['name'], 
                $discipline['level'],
                $discipline['xp_cost'] ?? 0
            );
            mysqli_stmt_execute($discipline_stmt);
            mysqli_stmt_close($discipline_stmt);
        }
    }
    
    // Save backgrounds
    if (isset($data['backgrounds'])) {
        foreach ($data['backgrounds'] as $background) {
            $background_sql = "INSERT INTO character_backgrounds (character_id, background_name, level, xp_cost) VALUES (?, ?, ?, ?)";
            $background_stmt = mysqli_prepare($conn, $background_sql);
            mysqli_stmt_bind_param($background_stmt, 'isii', 
                $character_id, 
                $background['name'], 
                $background['level'],
                $background['xp_cost'] ?? 0
            );
            mysqli_stmt_execute($background_stmt);
            mysqli_stmt_close($background_stmt);
        }
    }
    
    // Save merits & flaws
    if (isset($data['merits_flaws'])) {
        foreach ($data['merits_flaws'] as $item) {
            $merit_sql = "INSERT INTO character_merits_flaws (character_id, name, type, point_value, description, xp_bonus) VALUES (?, ?, ?, ?, ?, ?)";
            $merit_stmt = mysqli_prepare($conn, $merit_sql);
            mysqli_stmt_bind_param($merit_stmt, 'issisi', 
                $character_id, 
                $item['name'], 
                $item['type'],
                $item['point_value'],
                $item['description'] ?? null,
                $item['xp_bonus'] ?? 0
            );
            mysqli_stmt_execute($merit_stmt);
            mysqli_stmt_close($merit_stmt);
        }
    }
    
    // Save morality data
    if (isset($data['morality'])) {
        $morality_sql = "INSERT INTO character_morality (
            character_id, path_name, path_rating, conscience, self_control, courage, 
            willpower_permanent, willpower_current, humanity
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $morality_stmt = mysqli_prepare($conn, $morality_sql);
        mysqli_stmt_bind_param($morality_stmt, 'isiiiiiii', 
            $character_id,
            $data['morality']['path_name'] ?? 'Humanity',
            $data['morality']['path_rating'] ?? 7,
            $data['morality']['conscience'] ?? 1,
            $data['morality']['self_control'] ?? 1,
            $data['morality']['courage'] ?? 1,
            $data['morality']['willpower_permanent'] ?? 5,
            $data['morality']['willpower_current'] ?? 5,
            $data['morality']['humanity'] ?? 7
        );
        mysqli_stmt_execute($morality_stmt);
        mysqli_stmt_close($morality_stmt);
    }
    
    // Save status data
    if (isset($data['status'])) {
        $status_sql = "INSERT INTO character_status (
            character_id, sect_status, clan_status, city_status, 
            health_levels, blood_pool_current, blood_pool_maximum
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $status_stmt = mysqli_prepare($conn, $status_sql);
        mysqli_stmt_bind_param($status_stmt, 'issssii', 
            $character_id,
            $data['status']['sect_status'] ?? null,
            $data['status']['clan_status'] ?? null,
            $data['status']['city_status'] ?? null,
            $data['status']['health_levels'] ?? 'Healthy',
            $data['status']['blood_pool_current'] ?? 10,
            $data['status']['blood_pool_maximum'] ?? 10
        );
        mysqli_stmt_execute($status_stmt);
        mysqli_stmt_close($status_stmt);
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
