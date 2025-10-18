<?php
/**
 * API for Analyzing and Auto-Populating Sire Relationships from Biography
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Check if user is admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once __DIR__ . '/../includes/connect.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'analyze_all':
        analyzeAllCharacters();
        break;
    case 'analyze_single':
        analyzeSingleCharacter();
        break;
    case 'update_sire':
        updateSireField();
        break;
    default:
        analyzeAllCharacters();
        break;
}

function analyzeAllCharacters() {
    global $conn;
    
    $query = "SELECT c.id, c.character_name, c.sire, c.biography, c.equipment,
                     GROUP_CONCAT(CONCAT(mf.name, ': ', mf.description) SEPARATOR ' | ') as merits_flaws
              FROM characters c
              LEFT JOIN character_merits_flaws mf ON c.id = mf.character_id
              WHERE (c.biography IS NOT NULL AND c.biography != '') 
                 OR (c.equipment IS NOT NULL AND c.equipment != '')
                 OR (mf.name LIKE '%sire%' OR mf.description LIKE '%sire%')
              GROUP BY c.id";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
        return;
    }
    
    $analysis_results = [];
    $updates_made = 0;
    $suggestions_found = 0;
    $conflicts_found = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Combine all text fields for analysis
        $combined_text = '';
        $text_sources = [];
        
        if (!empty($row['biography'])) {
            $combined_text .= $row['biography'] . ' ';
            $text_sources[] = 'biography';
        }
        if (!empty($row['equipment'])) {
            $combined_text .= $row['equipment'] . ' ';
            $text_sources[] = 'equipment';
        }
        if (!empty($row['merits_flaws'])) {
            $combined_text .= $row['merits_flaws'] . ' ';
            $text_sources[] = 'merits/flaws';
        }
        
        $analysis = analyzeBiographyForSire($combined_text, $row['character_name']);
        $analysis['text_sources'] = $text_sources;
        
        if ($analysis['sire_found']) {
            $suggestions_found++;
            
            // Check if there's a conflict with existing sire field
            $has_conflict = false;
            if (!empty($row['sire']) && strtolower($row['sire']) !== strtolower($analysis['sire_name'])) {
                $has_conflict = true;
                $conflicts_found++;
            }
            
            // If no existing sire field or high confidence, update it
            if (empty($row['sire']) && $analysis['confidence'] === 'high') {
                updateCharacterSire($row['id'], $analysis['sire_name']);
                $updates_made++;
            }
            
            $analysis_results[] = [
                'character_id' => $row['id'],
                'character_name' => $row['character_name'],
                'current_sire' => $row['sire'],
                'suggested_sire' => $analysis['sire_name'],
                'confidence' => $analysis['confidence'],
                'has_conflict' => $has_conflict,
                'updated' => (empty($row['sire']) && $analysis['confidence'] === 'high'),
                'text_sources' => $analysis['text_sources'],
                'pattern_used' => $analysis['pattern_used'],
                'relationship_type' => $analysis['relationship_type']
            ];
        }
        
        // Also check if this character is mentioned as a sire of others
        $sire_analysis = analyzeBiographyForSireRelationships($combined_text, $row['character_name']);
        if ($sire_analysis['childer_found']) {
            foreach ($sire_analysis['childer'] as $childe_name) {
                $suggestions_found++;
                $analysis_results[] = [
                    'character_id' => $row['id'],
                    'character_name' => $row['character_name'],
                    'current_sire' => 'N/A (This character is the sire)',
                    'suggested_sire' => $childe_name,
                    'confidence' => $sire_analysis['confidence'],
                    'has_conflict' => false,
                    'updated' => false,
                    'text_sources' => $sire_analysis['text_sources'],
                    'pattern_used' => $sire_analysis['pattern_used'],
                    'relationship_type' => 'sire_of'
                ];
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Analysis complete. Found {$suggestions_found} potential relationships, made {$updates_made} updates, found {$conflicts_found} conflicts.",
        'results' => $analysis_results,
        'stats' => [
            'total_analyzed' => mysqli_num_rows($result),
            'suggestions_found' => $suggestions_found,
            'updates_made' => $updates_made,
            'conflicts_found' => $conflicts_found
        ]
    ]);
}

function analyzeSingleCharacter() {
    global $conn;
    
    $character_id = intval($_POST['character_id'] ?? 0);
    
    if (!$character_id) {
        echo json_encode(['success' => false, 'message' => 'Character ID required']);
        return;
    }
    
    $query = "SELECT id, character_name, sire, biography FROM characters WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $character_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result || mysqli_num_rows($result) === 0) {
        echo json_encode(['success' => false, 'message' => 'Character not found']);
        return;
    }
    
    $row = mysqli_fetch_assoc($result);
    $analysis = analyzeBiographyForSire($row['biography']);
    
    if ($analysis['sire_found']) {
        // Check if there's a conflict
        $has_conflict = false;
        if (!empty($row['sire']) && strtolower($row['sire']) !== strtolower($analysis['sire_name'])) {
            $has_conflict = true;
        }
        
        // Auto-update if no existing sire and high confidence
        $updated = false;
        if (empty($row['sire']) && $analysis['confidence'] === 'high') {
            updateCharacterSire($row['id'], $analysis['sire_name']);
            $updated = true;
        }
        
        echo json_encode([
            'success' => true,
            'analysis' => [
                'character_id' => $row['id'],
                'character_name' => $row['character_name'],
                'current_sire' => $row['sire'],
                'suggested_sire' => $analysis['sire_name'],
                'confidence' => $analysis['confidence'],
                'has_conflict' => $has_conflict,
                'updated' => $updated,
                'biography_excerpt' => substr($row['biography'], 0, 200) . '...'
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'analysis' => [
                'character_id' => $row['id'],
                'character_name' => $row['character_name'],
                'current_sire' => $row['sire'],
                'suggested_sire' => null,
                'confidence' => 'none',
                'has_conflict' => false,
                'updated' => false,
                'biography_excerpt' => substr($row['biography'], 0, 200) . '...'
            ]
        ]);
    }
}

function updateSireField() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        return;
    }
    
    $character_id = intval($input['character_id'] ?? 0);
    $sire_name = trim($input['sire_name'] ?? '');
    
    if (!$character_id) {
        echo json_encode(['success' => false, 'message' => 'Character ID required']);
        return;
    }
    
    updateCharacterSire($character_id, $sire_name);
    
    echo json_encode(['success' => true, 'message' => 'Sire field updated successfully']);
}

function updateCharacterSire($character_id, $sire_name) {
    global $conn;
    
    $update_query = "UPDATE characters SET sire = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    
    if (empty($sire_name)) {
        $sire_name = null;
        mysqli_stmt_bind_param($stmt, "si", $sire_name, $character_id);
    } else {
        mysqli_stmt_bind_param($stmt, "si", $sire_name, $character_id);
    }
    
    return mysqli_stmt_execute($stmt);
}

function analyzeBiographyForSire($biography, $character_name = '') {
    $original_biography = $biography;
    $biography = strtolower($biography);
    $character_name_lower = strtolower($character_name);
    
    // Common patterns for sire mentions with confidence levels
    $high_confidence_patterns = [
        // Patterns where THIS character is the childe (being sired)
        '/sired by ([a-zA-Z\s\']+?)(?:\s|,|\.|$)/',
        '/embraced by ([a-zA-Z\s\']+?)(?:\s|,|\.|$)/',
        '/my sire ([a-zA-Z\s\']+?)(?:\s|,|\.|$)/',
        '/their sire ([a-zA-Z\s\']+?)(?:\s|,|\.|$)/',
        '/his sire ([a-zA-Z\s\']+?)(?:\s|,|\.|$)/',
        '/her sire ([a-zA-Z\s\']+?)(?:\s|,|\.|$)/',
        '/created by ([a-zA-Z\s\']+?)(?:\s|,|\.|$)/',
        '/turned by ([a-zA-Z\s\']+?)(?:\s|,|\.|$)/',
        
        // Patterns where THIS character is the sire (doing the embracing)
        '/he embraced ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/',
        '/she embraced ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/',
        '/embraced ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/',
        '/sired ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/',
        '/created ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/',
        '/turned ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/',
        '/my childe ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/',
        '/my childer ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/'
    ];
    
    $medium_confidence_patterns = [
        '/mentor ([a-zA-Z\s\']+?)(?:\s|,|\.|$)/',
        '/teacher ([a-zA-Z\s\']+?)(?:\s|,|\.|$)/',
        '/taught by ([a-zA-Z\s\']+?)(?:\s|,|\.|$)/',
        '/guided by ([a-zA-Z\s\']+?)(?:\s|,|\.|$)/',
        '/trained by ([a-zA-Z\s\']+?)(?:\s|,|\.|$)/'
    ];
    
    // Check high confidence patterns first
    foreach ($high_confidence_patterns as $pattern) {
        if (preg_match($pattern, $biography, $matches)) {
            $sire = cleanSireName($matches[1]);
            if (isValidSireName($sire, $character_name_lower)) {
                return [
                    'sire_found' => true,
                    'sire_name' => $sire,
                    'confidence' => 'high',
                    'pattern_used' => $pattern,
                    'relationship_type' => 'childe_of'
                ];
            }
        }
    }
    
    // Check medium confidence patterns
    foreach ($medium_confidence_patterns as $pattern) {
        if (preg_match($pattern, $biography, $matches)) {
            $sire = cleanSireName($matches[1]);
            if (isValidSireName($sire, $character_name_lower)) {
                return [
                    'sire_found' => true,
                    'sire_name' => $sire,
                    'confidence' => 'medium',
                    'pattern_used' => $pattern,
                    'relationship_type' => 'childe_of'
                ];
            }
        }
    }
    
    return [
        'sire_found' => false,
        'sire_name' => null,
        'confidence' => 'none',
        'pattern_used' => null,
        'relationship_type' => 'none'
    ];
}

function analyzeBiographyForSireRelationships($biography, $character_name = '') {
    $original_biography = $biography;
    $biography = strtolower($biography);
    $character_name_lower = strtolower($character_name);
    
    // Patterns where THIS character is mentioned as the sire of others
    $sire_patterns = [
        '/he embraced ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/',
        '/she embraced ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/',
        '/embraced ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/',
        '/sired ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/',
        '/created ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/',
        '/turned ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/',
        '/my childe ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/',
        '/my childer ([a-zA-Z\s\']+?)(?:\s|,|\.|$|"|—|–|-|;|:)/'
    ];
    
    $childer = [];
    $text_sources = [];
    $pattern_used = null;
    
    foreach ($sire_patterns as $pattern) {
        if (preg_match_all($pattern, $biography, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $childe = cleanSireName($match[1]);
                if (isValidSireName($childe, $character_name_lower)) {
                    $childer[] = $childe;
                    $pattern_used = $pattern;
                }
            }
        }
    }
    
    // Debug logging for Duke Tiki specifically
    if (strpos($character_name_lower, 'duke tiki') !== false) {
        error_log("Duke Tiki analysis - Biography: " . substr($biography, 0, 200));
        error_log("Duke Tiki analysis - Found childer: " . implode(', ', $childer));
    }
    
    if (!empty($childer)) {
        return [
            'childer_found' => true,
            'childer' => array_unique($childer),
            'confidence' => 'high',
            'pattern_used' => $pattern_used,
            'text_sources' => ['biography']
        ];
    }
    
    return [
        'childer_found' => false,
        'childer' => [],
        'confidence' => 'none',
        'pattern_used' => null,
        'text_sources' => []
    ];
}

function cleanSireName($name) {
    // Remove common words and clean up the name
    $name = trim($name);
    
    // Remove common prefixes/suffixes
    $name = preg_replace('/\b(was|is|the|a|an|my|their|his|her|who|that|which)\b/i', '', $name);
    $name = preg_replace('/\b(and|or|but|so|yet|for|nor)\b/i', '', $name);
    
    // Remove punctuation at the end
    $name = rtrim($name, '.,!?;:');
    
    // Clean up extra spaces
    $name = preg_replace('/\s+/', ' ', $name);
    $name = trim($name);
    
    return $name;
}

function isValidSireName($name, $character_name = '') {
    // Basic validation for sire names
    if (empty($name) || strlen($name) < 2) {
        return false;
    }
    
    // Check if it's not just common words
    $common_words = ['the', 'and', 'or', 'but', 'so', 'yet', 'for', 'nor', 'a', 'an', 'is', 'was', 'are', 'were'];
    if (in_array(strtolower($name), $common_words)) {
        return false;
    }
    
    // Check if it contains at least one letter
    if (!preg_match('/[a-zA-Z]/', $name)) {
        return false;
    }
    
    // Check if it's not the character's own name (self-reference)
    if (!empty($character_name)) {
        $name_lower = strtolower($name);
        $character_lower = strtolower($character_name);
        
        // Check for exact match or if the name is contained in the character name
        if ($name_lower === $character_lower || 
            strpos($character_lower, $name_lower) !== false || 
            strpos($name_lower, $character_lower) !== false) {
            return false;
        }
        
        // Check for common name variations (e.g., "Bob" vs "Bayside Bob")
        $name_parts = explode(' ', $name_lower);
        $character_parts = explode(' ', $character_lower);
        
        foreach ($name_parts as $name_part) {
            if (strlen($name_part) > 2) { // Only check meaningful parts
                foreach ($character_parts as $char_part) {
                    if (strlen($char_part) > 2 && $name_part === $char_part) {
                        return false; // Found matching name part
                    }
                }
            }
        }
    }
    
    return true;
}

mysqli_close($conn);
?>
