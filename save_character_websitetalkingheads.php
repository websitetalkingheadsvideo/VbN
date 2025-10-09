<?php
// Save script that matches websitetalkingheads.com table structure
define('LOTN_VERSION', '0.2.1');

session_start();

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

header('Content-Type: application/json');

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
    'pc' => cleanInt($data['pc'] ?? 1),
    'biography' => cleanString($data['biography'] ?? ''),
    'appearance' => cleanString($data['equipment'] ?? ''), // Map equipment to appearance
    'notes' => cleanString($data['notes'] ?? ''),
    'experience_total' => cleanInt($data['total_xp'] ?? 30), // Map total_xp to experience_total
    'experience_unspent' => cleanInt($data['spent_xp'] ?? 0), // Map spent_xp to experience_unspent
    'morality_path' => cleanString($data['morality']['path_name'] ?? 'Humanity'),
    'conscience' => cleanInt($data['morality']['conscience'] ?? 1),
    'self_control' => cleanInt($data['morality']['self_control'] ?? 1),
    'courage' => cleanInt($data['morality']['courage'] ?? 1),
    'path_rating' => cleanInt($data['morality']['path_rating'] ?? 7),
    'willpower_permanent' => cleanInt($data['morality']['willpower_permanent'] ?? 5),
    'willpower_current' => cleanInt($data['morality']['willpower_current'] ?? 5),
    'blood_pool_max' => cleanInt($data['status']['blood_pool_maximum'] ?? 10),
    'blood_pool_current' => cleanInt($data['status']['blood_pool_current'] ?? 10),
    'health_status' => cleanString($data['status']['health_levels'] ?? 'Healthy')
];

try {
    $user_id = $_SESSION['user_id'];
    
    // Insert main character record with correct column names
    $character_sql = "INSERT INTO characters (
        user_id, character_name, player_name, chronicle, nature, demeanor, 
        concept, clan, generation, sire, pc, biography, appearance, notes,
        experience_total, experience_unspent, morality_path, conscience, 
        self_control, courage, path_rating, willpower_permanent, 
        willpower_current, blood_pool_max, blood_pool_current, health_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $character_sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
    }
    
    $pc_value = $cleanData['pc'] ? 1 : 0;
    mysqli_stmt_bind_param($stmt, 'isssssssisssssiiisssiiiiis', 
        $user_id,
        $cleanData['character_name'],
        $cleanData['player_name'],
        $cleanData['chronicle'],
        $cleanData['nature'],
        $cleanData['demeanor'],
        $cleanData['concept'],
        $cleanData['clan'],
        $cleanData['generation'],
        $cleanData['sire'],
        $pc_value,
        $cleanData['biography'],
        $cleanData['appearance'],
        $cleanData['notes'],
        $cleanData['experience_total'],
        $cleanData['experience_unspent'],
        $cleanData['morality_path'],
        $cleanData['conscience'],
        $cleanData['self_control'],
        $cleanData['courage'],
        $cleanData['path_rating'],
        $cleanData['willpower_permanent'],
        $cleanData['willpower_current'],
        $cleanData['blood_pool_max'],
        $cleanData['blood_pool_current'],
        $cleanData['health_status']
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Failed to create character: ' . mysqli_stmt_error($stmt));
    }
    
    $character_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    // Save traits if they exist
    if (isset($data['traits'])) {
        foreach ($data['traits'] as $category => $traits) {
            foreach ($traits as $trait) {
                $trait_sql = "INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type, xp_cost) VALUES (?, ?, ?, 'positive', ?)";
                $trait_stmt = mysqli_prepare($conn, $trait_sql);
                $xp_cost = 0;
                mysqli_stmt_bind_param($trait_stmt, 'issi', $character_id, $trait, $category, $xp_cost);
                mysqli_stmt_execute($trait_stmt);
                mysqli_stmt_close($trait_stmt);
            }
        }
    }
    
    // Save negative traits if they exist
    if (isset($data['negativeTraits'])) {
        foreach ($data['negativeTraits'] as $category => $traits) {
            foreach ($traits as $trait) {
                $trait_sql = "INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type, xp_cost) VALUES (?, ?, ?, 'negative', ?)";
                $trait_stmt = mysqli_prepare($conn, $trait_sql);
                $xp_bonus = -4;
                mysqli_stmt_bind_param($trait_stmt, 'issi', $character_id, $trait, $category, $xp_bonus);
                mysqli_stmt_execute($trait_stmt);
                mysqli_stmt_close($trait_stmt);
            }
        }
    }
    
    // Save abilities if they exist
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
    
    // Save disciplines if they exist
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
    
    // Save backgrounds if they exist
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
    
    // Save merits & flaws if they exist
    if (isset($data['merits_flaws']) && !empty($data['merits_flaws'])) {
        foreach ($data['merits_flaws'] as $item) {
            $merit_sql = "INSERT INTO character_merits_flaws (character_id, name, type, category, cost, description, effects) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $merit_stmt = mysqli_prepare($conn, $merit_sql);
            
            $effects_json = json_encode($item['effects'] ?? []);
            $custom_description = $item['customDescription'] ?? '';
            
            mysqli_stmt_bind_param($merit_stmt, 'isssiss', 
                $character_id, 
                $item['name'], 
                $item['type'],
                $item['category'],
                $item['selectedCost'] ?? $item['cost'],
                $custom_description,
                $effects_json
            );
            mysqli_stmt_execute($merit_stmt);
            mysqli_stmt_close($merit_stmt);
        }
    }
    
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
