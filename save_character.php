<?php
// LOTN Character Save Handler - Version 0.2.1 (FIXED)
// Include centralized version management
require_once __DIR__ . '/includes/version.php';

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
    'notes' => cleanString($data['notes'] ?? ''),
    'character_image' => cleanString($data['imagePath'] ?? $data['character_image'] ?? '')
];

// Identify if this is an update or create
$character_id = 0;
if (isset($data['character_id'])) {
    $character_id = (int)$data['character_id'];
} elseif (isset($data['id'])) {
    $character_id = (int)$data['id'];
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Log the received data for debugging
    error_log('Save character data: ' . json_encode($data));
    
    // Start transaction for atomic character creation
    db_begin_transaction($conn);
    
    try {
        if ($character_id > 0) {
            // Update existing character (no strict ownership gating here)
            $update_sql = "UPDATE characters SET character_name = ?, player_name = ?, chronicle = ?, nature = ?, demeanor = ?, concept = ?, clan = ?, generation = ?, sire = ?, pc = ?, biography = ?, notes = ?" .
                         ($cleanData['character_image'] !== '' ? ", character_image = ?" : "") .
                         " WHERE id = ?";

            $stmt = mysqli_prepare($conn, $update_sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare update: ' . mysqli_error($conn));
            }

            if ($cleanData['character_image'] !== '') {
                mysqli_stmt_bind_param(
                    $stmt,
                    'sssssssisssssi',
                    $cleanData['character_name'],
                    $cleanData['player_name'],
                    $cleanData['chronicle'],
                    $cleanData['nature'],
                    $cleanData['demeanor'],
                    $cleanData['concept'],
                    $cleanData['clan'],
                    $cleanData['generation'],
                    $cleanData['sire'],
                    $cleanData['pc'],
                    $cleanData['biography'],
                    $cleanData['notes'],
                    $cleanData['character_image'],
                    $character_id
                );
            } else {
                mysqli_stmt_bind_param(
                    $stmt,
                    'sssssssissssi',
                    $cleanData['character_name'],
                    $cleanData['player_name'],
                    $cleanData['chronicle'],
                    $cleanData['nature'],
                    $cleanData['demeanor'],
                    $cleanData['concept'],
                    $cleanData['clan'],
                    $cleanData['generation'],
                    $cleanData['sire'],
                    $cleanData['pc'],
                    $cleanData['biography'],
                    $cleanData['notes'],
                    $character_id
                );
            }

            if (!mysqli_stmt_execute($stmt)) {
                error_log('Character update error: ' . mysqli_stmt_error($stmt));
                throw new Exception('Failed to update character: ' . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        } else {
            // Create new character
            $character_sql = "INSERT INTO characters (user_id, character_name, player_name, chronicle, character_image) VALUES (?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $character_sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, 'issss',
                $user_id,
                $cleanData['character_name'],
                $cleanData['player_name'],
                $cleanData['chronicle'],
                $cleanData['character_image']
            );

            if (!mysqli_stmt_execute($stmt)) {
                error_log('Character insert error: ' . mysqli_stmt_error($stmt));
                throw new Exception('Failed to create character: ' . mysqli_stmt_error($stmt));
            }

            $character_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
        }
        
        // Save disciplines (new normalized structure: one row per discipline with max level)
        if (isset($data['disciplinePowers']) && is_array($data['disciplinePowers'])) {
            error_log('Saving disciplines for character: ' . $character_id);
            
            // Delete existing disciplines for this character
            $delete_sql = "DELETE FROM character_disciplines WHERE character_id = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_sql);
            if ($delete_stmt) {
                mysqli_stmt_bind_param($delete_stmt, 'i', $character_id);
                mysqli_stmt_execute($delete_stmt);
                mysqli_stmt_close($delete_stmt);
            }
            
            // Insert disciplines with their max level
            $insert_sql = "INSERT INTO character_disciplines (character_id, discipline_name, level, xp_cost) 
                          VALUES (?, ?, ?, ?)
                          ON DUPLICATE KEY UPDATE 
                          level = VALUES(level),
                          xp_cost = VALUES(xp_cost)";
            $disc_stmt = mysqli_prepare($conn, $insert_sql);
            
            if ($disc_stmt) {
                $discipline_count = 0;
                foreach ($data['disciplinePowers'] as $discipline_name => $power_levels) {
                    if (!is_array($power_levels) || empty($power_levels)) {
                        continue;
                    }
                    
                    // Get max level from the array of power levels
                    $max_level = max($power_levels);
                    
                    // Ensure level is between 1 and 5
                    $max_level = max(1, min(5, (int)$max_level));
                    
                    $xp_cost = 0; // Can be extended later if XP tracking is needed
                    
                    mysqli_stmt_bind_param($disc_stmt, 'isii', 
                        $character_id,
                        $discipline_name,
                        $max_level,
                        $xp_cost
                    );
                    
                    if (mysqli_stmt_execute($disc_stmt)) {
                        $discipline_count++;
                    } else {
                        error_log('Failed to save discipline ' . $discipline_name . ': ' . mysqli_stmt_error($disc_stmt));
                    }
                }
                
                mysqli_stmt_close($disc_stmt);
                error_log("Saved {$discipline_count} disciplines for character {$character_id}");
            } else {
                error_log('Failed to prepare discipline insert statement: ' . mysqli_error($conn));
            }
        }
        
        // Save abilities
        if (isset($data['abilities']) && is_array($data['abilities'])) {
            error_log('Saving abilities for character: ' . $character_id);
            
            // Delete existing abilities for this character
            $delete_sql = "DELETE FROM character_abilities WHERE character_id = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_sql);
            if ($delete_stmt) {
                mysqli_stmt_bind_param($delete_stmt, 'i', $character_id);
                mysqli_stmt_execute($delete_stmt);
                mysqli_stmt_close($delete_stmt);
            }
            
            // Insert abilities
            // Data format: { Physical: ['Athletics', 'Athletics', 'Brawl'], Social: [...], Mental: [...] }
            // Count occurrences to get level
            $insert_sql = "INSERT INTO character_abilities (character_id, ability_name, level, specialization, xp_cost) 
                         VALUES (?, ?, ?, ?, ?)";
            $ability_stmt = mysqli_prepare($conn, $insert_sql);
            
            if ($ability_stmt) {
                $ability_count = 0;
                foreach ($data['abilities'] as $category => $abilityNames) {
                    if (!is_array($abilityNames)) {
                        continue;
                    }
                    
                    // Count occurrences of each ability name to get level
                    $abilityCounts = [];
                    foreach ($abilityNames as $abilityName) {
                        // Clean the ability name (remove specialization if present, e.g., "Athletics (Running)" -> "Athletics")
                        $cleanName = trim($abilityName);
                        if (strpos($cleanName, ' (') !== false) {
                            $cleanName = substr($cleanName, 0, strpos($cleanName, ' ('));
                        }
                        
                        $abilityCounts[$cleanName] = ($abilityCounts[$cleanName] ?? 0) + 1;
                    }
                    
                    // Insert each unique ability with its level
                    foreach ($abilityCounts as $abilityName => $level) {
                        // Ensure level is between 1 and 5
                        $level = max(1, min(5, (int)$level));
                        
                        // Check for specialization in the original name
                        $specialization = null;
                        foreach ($abilityNames as $origName) {
                            if (strpos($origName, $abilityName . ' (') === 0) {
                                // Extract specialization from "AbilityName (Specialization)"
                                $specStart = strpos($origName, ' (') + 2;
                                $specEnd = strrpos($origName, ')');
                                if ($specEnd > $specStart) {
                                    $specialization = substr($origName, $specStart, $specEnd - $specStart);
                                }
                                break; // Use first specialization found
                            }
                        }
                        
                        $xp_cost = 0; // Can be extended later if XP tracking is needed
                        
                        mysqli_stmt_bind_param($ability_stmt, 'isisi', 
                            $character_id,
                            $abilityName,
                            $level,
                            $specialization,
                            $xp_cost
                        );
                        
                        if (mysqli_stmt_execute($ability_stmt)) {
                            $ability_count++;
                        } else {
                            error_log('Failed to save ability ' . $abilityName . ': ' . mysqli_stmt_error($ability_stmt));
                        }
                    }
                }
                
                mysqli_stmt_close($ability_stmt);
                error_log("Saved {$ability_count} abilities for character {$character_id}");
            } else {
                error_log('Failed to prepare ability insert statement: ' . mysqli_error($conn));
            }
        }
        
        // TODO: Add traits, backgrounds, merits_flaws saving later
        
        // Commit transaction if all operations succeed
        db_commit($conn);
        
        echo json_encode([
            'success' => true,
            'message' => ($data['id'] ?? $data['character_id'] ?? null) ? 'Character updated successfully!' : 'Character created successfully!',
            'character_id' => $character_id
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on any error
        db_rollback($conn);
        throw $e;
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