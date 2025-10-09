<?php
// Minimal save script to test basic functionality
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

// Simple character insert with minimal fields
try {
    $user_id = $_SESSION['user_id'];
    
    $character_sql = "INSERT INTO characters (
        user_id, character_name, player_name, chronicle, nature, demeanor, 
        concept, clan, generation, sire, pc, biography, appearance, notes,
        experience_total, experience_unspent, morality_path, conscience, 
        self_control, courage, path_rating, willpower_permanent, willpower_current,
        blood_pool_max, blood_pool_current, health_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $character_sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }
    
    // Clean data
    $character_name = trim($data['character_name'] ?? '');
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
    
    mysqli_stmt_bind_param($stmt, 'isssssssissssiisiiiiiiiis',
        $user_id,
        $character_name,
        $player_name,
        $chronicle,
        $nature,
        $demeanor,
        $concept,
        $clan,
        $generation,
        $sire,
        $pc,
        $biography,
        $appearance,
        $notes,
        $experience_total,
        $experience_unspent,
        $morality_path,
        $conscience,
        $self_control,
        $courage,
        $path_rating,
        $willpower_permanent,
        $willpower_current,
        $blood_pool_max,
        $blood_pool_current,
        $health_status
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to create character: ' . mysqli_stmt_error($stmt));
    }
    
    $character_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Character saved successfully!',
        'character_id' => $character_id
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error saving character: ' . $e->getMessage()
    ]);
}

mysqli_close($conn);
?>
