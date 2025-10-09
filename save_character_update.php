<?php
// Save script that updates existing characters instead of creating new ones
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Set headers
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

// Database connection
include 'includes/connect.php';

if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $character_name = trim($data['character_name'] ?? '');
    
    // Clean data
    $player_name = trim($data['player_name'] ?? '');
    $chronicle = trim($data['chronicle'] ?? 'Valley by Night');
    $nature = trim($data['nature'] ?? '');
    $demeanor = trim($data['demeanor'] ?? '');
    $concept = trim($data['concept'] ?? '');
    $clan = trim($data['clan'] ?? '');
    $generation = (int)($data['generation'] ?? 13);
    $sire = trim($data['sire'] ?? '');
    $pc = ($data['pc'] ?? 1) ? 1 : 0;
    $biography = trim($data['biography'] ?? '');
    $appearance = trim($data['equipment'] ?? '');
    $notes = trim($data['notes'] ?? '');
    $experience_total = (int)($data['total_xp'] ?? 30);
    $experience_unspent = (int)($data['spent_xp'] ?? 0);
    $morality_path = trim($data['morality']['path_name'] ?? 'Humanity');
    $conscience = (int)($data['morality']['conscience'] ?? 1);
    $self_control = (int)($data['morality']['self_control'] ?? 1);
    $courage = (int)($data['morality']['courage'] ?? 1);
    $path_rating = (int)($data['morality']['path_rating'] ?? 7);
    $willpower_permanent = (int)($data['morality']['willpower_permanent'] ?? 5);
    $willpower_current = (int)($data['morality']['willpower_current'] ?? 5);
    $blood_pool_max = (int)($data['blood_pool_max'] ?? 10);
    $blood_pool_current = (int)($data['blood_pool_current'] ?? 10);
    $health_status = trim($data['health_status'] ?? 'Healthy');
    
    // Check if character already exists
    $check_sql = "SELECT id FROM characters WHERE user_id = ? AND character_name = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, 'is', $user_id, $character_name);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    $existing_character = mysqli_fetch_assoc($result);
    mysqli_stmt_close($check_stmt);
    
    if ($existing_character) {
        // UPDATE existing character
        $character_id = $existing_character['id'];
        
        $update_sql = "UPDATE characters SET 
            player_name = ?, chronicle = ?, nature = ?, demeanor = ?, 
            concept = ?, clan = ?, generation = ?, sire = ?, pc = ?, 
            biography = ?, appearance = ?, notes = ?, experience_total = ?, 
            experience_unspent = ?, morality_path = ?, conscience = ?, 
            self_control = ?, courage = ?, path_rating = ?, 
            willpower_permanent = ?, willpower_current = ?, 
            blood_pool_max = ?, blood_pool_current = ?, health_status = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $update_sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare update statement: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, 'ssssssissssiisiiiiiiiisi',
            $player_name, $chronicle, $nature, $demeanor, $concept, $clan, 
            $generation, $sire, $pc, $biography, $appearance, $notes, 
            $experience_total, $experience_unspent, $morality_path, 
            $conscience, $self_control, $courage, $path_rating, 
            $willpower_permanent, $willpower_current, $blood_pool_max, 
            $blood_pool_current, $health_status, $character_id
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to update character: ' . mysqli_stmt_error($stmt));
        }
        
        mysqli_stmt_close($stmt);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Character updated successfully!',
            'character_id' => $character_id,
            'action' => 'updated'
        ]);
        
    } else {
        // INSERT new character
        $insert_sql = "INSERT INTO characters (
            user_id, character_name, player_name, chronicle, nature, demeanor, 
            concept, clan, generation, sire, pc, biography, appearance, notes,
            experience_total, experience_unspent, morality_path, conscience, 
            self_control, courage, path_rating, willpower_permanent, willpower_current,
            blood_pool_max, blood_pool_current, health_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $insert_sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare insert statement: ' . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, 'isssssssissssiisiiiiiiiiis',
            $user_id, $character_name, $player_name, $chronicle, $nature, $demeanor, 
            $concept, $clan, $generation, $sire, $pc, $biography, $appearance, $notes,
            $experience_total, $experience_unspent, $morality_path, $conscience, 
            $self_control, $courage, $path_rating, $willpower_permanent, $willpower_current,
            $blood_pool_max, $blood_pool_current, $health_status
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception('Failed to create character: ' . mysqli_stmt_error($stmt));
        }
        
        $character_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Character created successfully!',
            'character_id' => $character_id,
            'action' => 'created'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error saving character: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
