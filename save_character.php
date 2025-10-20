<?php
// LOTN Character Save Handler - Version 0.2.1 (FIXED)
define('LOTN_VERSION', '0.2.1');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Global error handler
set_error_handler(function($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => "PHP Error: $message in $file on line $line"
        ]);
        exit();
    }
});

// Start session first
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
try {
    include 'includes/connect.php';
    
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()]);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Debug: Log that we've reached this point
error_log('Save character script started successfully');

// Get JSON data
$input = file_get_contents('php://input');
error_log('Raw input: ' . $input);

$data = json_decode($input, true);
error_log('Decoded data: ' . json_encode($data));

if (!$data) {
    error_log('JSON decode failed');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

// Clean the data
function cleanString($value) {
    if (is_string($value)) {
        return trim($value);
    }
    return $value;
}

function cleanInt($value) {
    return (int)$value;
}

$cleanData = [
    'character_name' => cleanString($data['character_name'] ?? ''),
    'player_name' => cleanString($data['player_name'] ?? ''),
    'chronicle' => cleanString($data['chronicle'] ?? 'Valley by Night'),
    'nature' => cleanString($data['nature'] ?? ''),
    'demeanor' => cleanString($data['demeanor'] ?? ''),
    'concept' => cleanString($data['concept'] ?? ''),
    'clan' => cleanString($data['clan'] ?? ''),
    'generation' => cleanInt($data['generation'] ?? 13),
    'sire' => cleanString($data['sire'] ?? ''),
    'pc' => cleanInt($data['pc'] ?? $data['is_pc'] ?? 1),
    'biography' => cleanString($data['biography'] ?? ''),
    'notes' => cleanString($data['notes'] ?? '')
];

try {
    $user_id = $_SESSION['user_id'];
    
    // Log the received data for debugging
    error_log('Save character data: ' . json_encode($data));
    
    // Simple insert like the working test_save.php
    $character_sql = "INSERT INTO characters (user_id, character_name, player_name, chronicle) VALUES (?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $character_sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, 'isss',
        $user_id,
        $cleanData['character_name'],
        $cleanData['player_name'],
        $cleanData['chronicle']
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        error_log('Character insert error: ' . mysqli_stmt_error($stmt));
        throw new Exception('Failed to create character: ' . mysqli_stmt_error($stmt));
    }
    
    $character_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    // TODO: Add traits, abilities, disciplines, backgrounds, merits_flaws saving later
    error_log('Character saved with ID: ' . $character_id . ' - Additional data saving skipped for now');
    
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