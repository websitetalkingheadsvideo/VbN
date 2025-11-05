<?php
/**
 * Batch Import Simple Character Format
 * Imports Pistol Pete, Sasha, and Leo
 * 
 * Usage from browser: https://vbn.talkingheads.video/data/batch_import_simple_characters.php
 * Usage from CLI: php data/batch_import_simple_characters.php
 */

// Set longer execution time
set_time_limit(120);

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Determine if running from CLI or browser
$is_cli = php_sapi_name() === 'cli';

if (!$is_cli) {
    echo "<!DOCTYPE html><html><head><title>Batch Character Import</title><style>
        body { font-family: monospace; background: #1a0f0f; color: #d4c4b0; padding: 20px; }
        pre { white-space: pre-wrap; word-wrap: break-word; }
        .error { color: #ff6b6b; }
        .success { color: #51cf66; }
        .warning { color: #ffd43b; }
    </style></head><body>";
    echo "<h1>🦇 Batch Character Import</h1><pre>";
}

echo "=================================================================\n";
echo "Batch Simple Character Import\n";
echo "=================================================================\n\n";

// Include the import script logic
require_once __DIR__ . '/../includes/connect.php';

// Characters to import
$characters_to_import = [
    'Pistol Pete.json',
    'Sasha.json',
    'Leo.json'
];

$success_count = 0;
$error_count = 0;
$errors = [];

// Function to import a single character
function importCharacter($conn, $filename) {
    $json_file = __DIR__ . '/../reference/Characters/Added to Database/' . $filename;
    
    if (!file_exists($json_file)) {
        echo "❌ JSON file not found: $json_file\n";
        return false;
    }
    
    $json_data = file_get_contents($json_file);
    $character = json_decode($json_data, true);
    
    if (!$character) {
        echo "❌ Failed to parse JSON file\n";
        return false;
    }
    
    echo "✅ JSON parsed: {$character['name']} ({$character['clan']})\n";
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Use user_id = 1 for NPCs
        $user_id = 1;
        
        // Build character fields
        $character_name = $character['name'] ?? '';
        $player_name = 'ST/NPC';
        $chronicle = 'Valley by Night';
        $nature = $character['nature'] ?? '';
        $demeanor = $character['demeanor'] ?? '';
        $concept = $character['concept'] ?? '';
        $clan = $character['clan'] ?? '';
        $generation = $character['generation'] ?? 0;
        $sire = $character['embrace_info'] ?? '';
        $pc = 0;
        $biography = $character['goal'] ?? '';
        $equipment = '';
        $xp_total = 0;
        $xp_available = 0;
        $blood_pool = $character['traits']['blood_pool'] ?? 10;
        $notes = "Haven: " . ($character['haven'] ?? 'Unknown');
        
        $stmt = mysqli_prepare($conn, "
            INSERT INTO characters (
                user_id, character_name, player_name, chronicle, nature, demeanor, 
                concept, clan, generation, sire, pc, biography, equipment,
                experience_total, experience_unspent, blood_pool_current, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, "isssssssisissiiis",
            $user_id, $character_name, $player_name, $chronicle, $nature,
            $demeanor, $concept, $clan, $generation, $sire, $pc, $biography,
            $equipment, $xp_total, $xp_available, $blood_pool, $notes
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
        }
        
        $character_id = mysqli_insert_id($conn);
        echo "✅ Character created (ID: $character_id)\n";
        
        // Insert disciplines
        if (isset($character['disciplines']) && is_array($character['disciplines'])) {
            $disc_stmt = mysqli_prepare($conn, "
                INSERT INTO character_disciplines (character_id, discipline_name, level, xp_cost)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE level = VALUES(level)
            ");
            
            $disc_count = 0;
            foreach ($character['disciplines'] as $discipline_name => $level) {
                $discipline_name_capitalized = ucfirst(strtolower($discipline_name));
                $level = (int)$level;
                $xp_cost = 0;
                
                mysqli_stmt_bind_param($disc_stmt, "isii", $character_id, $discipline_name_capitalized, $level, $xp_cost);
                mysqli_stmt_execute($disc_stmt);
                $disc_count++;
            }
            echo "✅ $disc_count disciplines added\n";
        }
        
        // Insert merits/flaws
        if (!empty($character['merits_flaws'])) {
            $mf_stmt = mysqli_prepare($conn, "
                INSERT INTO character_merits_flaws (
                    character_id, name, type, category, point_value, point_cost, description
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($character['merits_flaws'] as $mf) {
                $type_capitalized = ucfirst(strtolower($mf['type']));
                $category = ucfirst(strtolower($mf['type']));
                
                mysqli_stmt_bind_param($mf_stmt, "isssiis",
                    $character_id, $mf['name'], $type_capitalized, $category,
                    $mf['cost'], $mf['cost'], $mf['description']
                );
                mysqli_stmt_execute($mf_stmt);
            }
            echo "✅ " . count($character['merits_flaws']) . " merits/flaws added\n";
        }
        
        mysqli_commit($conn);
        return true;
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "❌ Error: " . $e->getMessage() . "\n";
        return false;
    }
}

// Import each character
foreach ($characters_to_import as $filename) {
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "Processing: $filename\n";
    echo str_repeat("=", 70) . "\n\n";
    
    if (importCharacter($conn, $filename)) {
        $success_count++;
        echo "✅ SUCCESS: $filename imported successfully\n";
    } else {
        $error_count++;
        $errors[] = $filename;
        echo "❌ FAILED: $filename import failed\n";
    }
}

// Summary
echo "\n" . str_repeat("=", 70) . "\n";
echo "BATCH IMPORT SUMMARY\n";
echo str_repeat("=", 70) . "\n";
echo "✅ Successfully imported: $success_count\n";
echo "❌ Failed: $error_count\n";

if ($error_count > 0) {
    echo "\nFailed files:\n";
    foreach ($errors as $error_file) {
        echo "  - $error_file\n";
    }
}

echo str_repeat("=", 70) . "\n\n";

if (!$is_cli) {
    echo "</pre>";
    echo "<p style='margin-top: 20px;'><strong>Batch import complete!</strong></p>";
    echo "</body></html>";
}
?>

