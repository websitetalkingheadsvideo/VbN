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

function cleanJsonData($value) {
    // If empty, return NULL for JSON column
    if (empty($value) || trim($value) === '') {
        return null;
    }
    
    // If it's already an array or object, encode it
    if (is_array($value) || is_object($value)) {
        return json_encode($value);
    }
    
    // If it's a string, try to validate it's valid JSON
    $trimmed = trim($value);
    if ($trimmed === '') {
        return null;
    }
    
    // Try to decode and re-encode to validate JSON
    $decoded = json_decode($trimmed, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        // Valid JSON, return the original trimmed string
        return $trimmed;
    }
    
    // Not valid JSON, treat as plain text and wrap in JSON object
    return json_encode(['text' => $trimmed]);
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
    'custom_data' => cleanJsonData($data['custom_data'] ?? ''),
    'character_image' => cleanString($data['imagePath'] ?? $data['character_image'] ?? ''),
    'status' => cleanString($data['status'] ?? $data['current_state'] ?? ($data['status_details']['current_state'] ?? ($data['status']['current_state'] ?? 'active'))),
    'camarilla_status' => cleanString($data['camarilla_status'] ?? ($data['status_details']['camarilla_status'] ?? ($data['status']['camarilla_status'] ?? 'Unknown')))
];

$validStates = ['active', 'inactive', 'archived'];
$cleanData['status'] = strtolower($cleanData['status'] ?? 'active');
if (!in_array($cleanData['status'], $validStates, true)) {
    $cleanData['status'] = 'active';
}

$validCamarilla = ['Camarilla', 'Anarch', 'Independent', 'Sabbat', 'Unknown'];
$camarillaValue = $cleanData['camarilla_status'] ?? 'Unknown';
$camarillaValue = $camarillaValue ? ucfirst(strtolower($camarillaValue)) : 'Unknown';
if (!in_array($camarillaValue, $validCamarilla, true)) {
    $camarillaValue = 'Unknown';
}
$cleanData['camarilla_status'] = $camarillaValue;

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
            $update_sql = "UPDATE characters SET character_name = ?, player_name = ?, chronicle = ?, nature = ?, demeanor = ?, concept = ?, clan = ?, generation = ?, sire = ?, pc = ?, biography = ?, notes = ?, custom_data = ?, status = ?, camarilla_status = ?" .
                         ($cleanData['character_image'] !== '' ? ", character_image = ?" : "") .
                         " WHERE id = ?";

            $stmt = mysqli_prepare($conn, $update_sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare update: ' . mysqli_error($conn));
            }

            if ($cleanData['character_image'] !== '') {
                mysqli_stmt_bind_param(
                    $stmt,
                    'sssssssisissssssi',
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
                    $cleanData['custom_data'],
                    $cleanData['status'],
                    $cleanData['camarilla_status'],
                    $cleanData['character_image'],
                    $character_id
                );
            } else {
                mysqli_stmt_bind_param(
                    $stmt,
                    'sssssssisisssssi',
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
                    $cleanData['custom_data'],
                    $cleanData['status'],
                    $cleanData['camarilla_status'],
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
            $character_sql = "INSERT INTO characters (user_id, character_name, player_name, chronicle, character_image, status, camarilla_status) VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $character_sql);
            if (!$stmt) {
                throw new Exception('Failed to prepare statement: ' . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, 'issssss',
                $user_id,
                $cleanData['character_name'],
                $cleanData['player_name'],
                $cleanData['chronicle'],
                $cleanData['character_image'],
                $cleanData['status'],
                $cleanData['camarilla_status']
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
            $insert_sql = "INSERT INTO character_disciplines (character_id, discipline_name, level) 
                          VALUES (?, ?, ?)
                          ON DUPLICATE KEY UPDATE 
                          level = VALUES(level)";
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
                    
                    mysqli_stmt_bind_param($disc_stmt, 'isi', 
                        $character_id,
                        $discipline_name,
                        $max_level
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
        if (isset($data['abilities']) && (is_array($data['abilities']) || is_object($data['abilities']))) {
            error_log('Saving abilities for character: ' . $character_id);
            error_log('Abilities data: ' . json_encode($data['abilities']));
            
            // Convert object to array if needed
            $abilities = is_object($data['abilities']) ? (array)$data['abilities'] : $data['abilities'];
            
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
            $insert_sql = "INSERT INTO character_abilities (character_id, ability_name, level, specialization) 
                         VALUES (?, ?, ?, ?)";
            $ability_stmt = mysqli_prepare($conn, $insert_sql);
            
            if ($ability_stmt) {
                $ability_count = 0;
                foreach ($abilities as $category => $abilityNames) {
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
                        
                        mysqli_stmt_bind_param($ability_stmt, 'isis', 
                            $character_id,
                            $abilityName,
                            $level,
                            $specialization
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

        // Save positive traits (Physical/Social/Mental)
        if (isset($data['traits']) && is_array($data['traits'])) {
            error_log('Saving positive traits for character: ' . $character_id);

            $allowedCategories = ['Physical', 'Social', 'Mental'];

            $delete_sql = "DELETE FROM character_traits WHERE character_id = ? AND (trait_type IS NULL OR trait_type = 'positive')";
            $delete_stmt = mysqli_prepare($conn, $delete_sql);
            if ($delete_stmt) {
                mysqli_stmt_bind_param($delete_stmt, 'i', $character_id);
                mysqli_stmt_execute($delete_stmt);
                mysqli_stmt_close($delete_stmt);
            } else {
                error_log('Failed to prepare positive trait delete statement: ' . mysqli_error($conn));
            }

            $insert_sql = "INSERT INTO character_traits (character_id, trait_name, trait_category, trait_type) VALUES (?, ?, ?, 'positive')";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            if ($insert_stmt) {
                $positiveCount = 0;
                foreach ($data['traits'] as $category => $traitNames) {
                    $normalizedCategory = ucfirst(strtolower($category));
                    if (!in_array($normalizedCategory, $allowedCategories, true)) {
                        continue;
                    }

                    if (!is_array($traitNames)) {
                        continue;
                    }

                    foreach ($traitNames as $traitName) {
                        $cleanName = cleanString($traitName ?? '');
                        if ($cleanName === '') {
                            continue;
                        }

                        mysqli_stmt_bind_param(
                            $insert_stmt,
                            'iss',
                            $character_id,
                            $cleanName,
                            $normalizedCategory
                        );

                        if (mysqli_stmt_execute($insert_stmt)) {
                            $positiveCount++;
                        } else {
                            error_log('Failed to save positive trait ' . $cleanName . ': ' . mysqli_stmt_error($insert_stmt));
                        }
                    }
                }

                mysqli_stmt_close($insert_stmt);
                error_log("Saved {$positiveCount} positive traits for character {$character_id}");
            } else {
                error_log('Failed to prepare positive trait insert statement: ' . mysqli_error($conn));
            }
        }

        // Save negative traits separately to preserve legacy APIs
        if (isset($data['negativeTraits']) && is_array($data['negativeTraits'])) {
            error_log('Saving negative traits for character: ' . $character_id);

            $allowedCategories = ['Physical', 'Social', 'Mental'];

            $delete_sql = "DELETE FROM character_negative_traits WHERE character_id = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_sql);
            if ($delete_stmt) {
                mysqli_stmt_bind_param($delete_stmt, 'i', $character_id);
                mysqli_stmt_execute($delete_stmt);
                mysqli_stmt_close($delete_stmt);
            } else {
                error_log('Failed to prepare negative trait delete statement: ' . mysqli_error($conn));
            }

            $insert_sql = "INSERT INTO character_negative_traits (character_id, trait_category, trait_name) VALUES (?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            if ($insert_stmt) {
                $negativeCount = 0;
                foreach ($data['negativeTraits'] as $category => $traitNames) {
                    $normalizedCategory = ucfirst(strtolower($category));
                    if (!in_array($normalizedCategory, $allowedCategories, true)) {
                        continue;
                    }

                    if (!is_array($traitNames)) {
                        continue;
                    }

                    foreach ($traitNames as $traitName) {
                        $cleanName = cleanString($traitName ?? '');
                        if ($cleanName === '') {
                            continue;
                        }

                        mysqli_stmt_bind_param(
                            $insert_stmt,
                            'iss',
                            $character_id,
                            $normalizedCategory,
                            $cleanName
                        );

                        if (mysqli_stmt_execute($insert_stmt)) {
                            $negativeCount++;
                        } else {
                            error_log('Failed to save negative trait ' . $cleanName . ': ' . mysqli_stmt_error($insert_stmt));
                        }
                    }
                }

                mysqli_stmt_close($insert_stmt);
                error_log("Saved {$negativeCount} negative traits for character {$character_id}");
            } else {
                error_log('Failed to prepare negative trait insert statement: ' . mysqli_error($conn));
            }
        }
        
        // Save coteries
        if (isset($data['coteries']) && is_array($data['coteries'])) {
            error_log('Saving coteries for character: ' . $character_id);
            
            // Delete existing coteries
            $delete_sql = "DELETE FROM character_coteries WHERE character_id = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_sql);
            if ($delete_stmt) {
                mysqli_stmt_bind_param($delete_stmt, 'i', $character_id);
                mysqli_stmt_execute($delete_stmt);
                mysqli_stmt_close($delete_stmt);
            }
            
            // Insert new coteries
            $insert_sql = "INSERT INTO character_coteries (character_id, coterie_name, coterie_type, role, description, notes) VALUES (?, ?, ?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            if ($insert_stmt) {
                $coterie_count = 0;
                foreach ($data['coteries'] as $coterie) {
                    if (empty($coterie['coterie_name'])) {
                        continue;
                    }
                    
                    mysqli_stmt_bind_param(
                        $insert_stmt,
                        'isssss',
                        $character_id,
                        cleanString($coterie['coterie_name'] ?? ''),
                        cleanString($coterie['coterie_type'] ?? ''),
                        cleanString($coterie['role'] ?? ''),
                        cleanString($coterie['description'] ?? ''),
                        cleanString($coterie['notes'] ?? '')
                    );
                    
                    if (mysqli_stmt_execute($insert_stmt)) {
                        $coterie_count++;
                    } else {
                        error_log('Failed to save coterie: ' . mysqli_stmt_error($insert_stmt));
                    }
                }
                mysqli_stmt_close($insert_stmt);
                error_log("Saved {$coterie_count} coteries for character {$character_id}");
            } else {
                error_log('Failed to prepare coterie insert statement: ' . mysqli_error($conn));
            }
        }
        
        // Save relationships
        if (isset($data['relationships']) && is_array($data['relationships'])) {
            error_log('Saving relationships for character: ' . $character_id);
            
            // Delete existing relationships
            $delete_sql = "DELETE FROM character_relationships WHERE character_id = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_sql);
            if ($delete_stmt) {
                mysqli_stmt_bind_param($delete_stmt, 'i', $character_id);
                mysqli_stmt_execute($delete_stmt);
                mysqli_stmt_close($delete_stmt);
            }
            
            // Insert new relationships
            $insert_sql = "INSERT INTO character_relationships (character_id, related_character_name, relationship_type, relationship_subtype, strength, description) VALUES (?, ?, ?, ?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            if ($insert_stmt) {
                $relationship_count = 0;
                foreach ($data['relationships'] as $relationship) {
                    if (empty($relationship['related_character_name'])) {
                        continue;
                    }
                    
                    mysqli_stmt_bind_param(
                        $insert_stmt,
                        'isssss',
                        $character_id,
                        cleanString($relationship['related_character_name'] ?? ''),
                        cleanString($relationship['relationship_type'] ?? ''),
                        cleanString($relationship['relationship_subtype'] ?? ''),
                        cleanString($relationship['strength'] ?? ''),
                        cleanString($relationship['description'] ?? '')
                    );
                    
                    if (mysqli_stmt_execute($insert_stmt)) {
                        $relationship_count++;
                    } else {
                        error_log('Failed to save relationship: ' . mysqli_stmt_error($insert_stmt));
                    }
                }
                mysqli_stmt_close($insert_stmt);
                error_log("Saved {$relationship_count} relationships for character {$character_id}");
            } else {
                error_log('Failed to prepare relationship insert statement: ' . mysqli_error($conn));
            }
        }
        
        // TODO: Add backgrounds, merits_flaws saving later
        
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