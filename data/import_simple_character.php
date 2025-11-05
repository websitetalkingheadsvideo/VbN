<?php
/**
 * Import Simple Character Format (Pistol Pete, Sasha, Leo style)
 * Handles disciplines as simple key-value pairs: {"potence": 3, "celerity": 2}
 * 
 * Usage from browser: https://vbn.talkingheads.video/data/import_simple_character.php?file=Pistol%20Pete.json
 * Usage from CLI: php data/import_simple_character.php Pistol\ Pete.json
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Determine if running from CLI or browser
$is_cli = php_sapi_name() === 'cli';

// Get filename from query parameter or command line
$filename = null;
if ($is_cli) {
    if (isset($argv[1])) {
        $filename = $argv[1];
    } else {
        die("ERROR: No file specified. Usage: php import_simple_character.php CharacterName.json\n");
    }
} else {
    $filename = isset($_GET['file']) ? $_GET['file'] : null;
    if (!$filename) {
        die("ERROR: No file specified. Usage: ?file=CharacterName.json\n");
    }
}

echo "=================================================================\n";
echo "Simple Character Import\n";
echo "=================================================================\n\n";

// Include database connection
echo "📡 Loading database connection...\n";
$connect_file = __DIR__ . '/../includes/connect.php';
if (!file_exists($connect_file)) {
    die("❌ Connection file not found: $connect_file\n");
}
require_once $connect_file;

// Check if connection exists
echo "🔍 Checking database connection...\n";
if (!isset($conn) || !$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error() . "\n");
}

echo "✅ Database connection established\n";
echo "   Connected to remote database\n\n";

// Read and decode JSON
$json_file = __DIR__ . '/../reference/Characters/Added to Database/' . $filename;

if (!file_exists($json_file)) {
    die("❌ JSON file not found: $json_file\n");
}

echo "📄 Reading character file: $filename\n\n";

$json_data = file_get_contents($json_file);
$character = json_decode($json_data, true);

if (!$character) {
    die("❌ Failed to parse JSON file\n");
}

echo "✅ JSON parsed successfully\n";
echo "   Character: {$character['name']}\n";
echo "   Clan: {$character['clan']}\n\n";

// Start transaction
echo "🚀 Starting import transaction...\n\n";
mysqli_begin_transaction($conn);

try {
    // 1. Insert main character record
    echo "📝 Inserting character record...\n";
    
    // Use user_id = 1 for NPCs (default ST/admin user)
    $user_id = 1;
    
    // Build main character fields - map simple format to database
    $character_name = $character['name'] ?? '';
    $player_name = 'ST/NPC'; // Default for NPCs
    $chronicle = 'Valley by Night'; // Default
    $nature = $character['nature'] ?? '';
    $demeanor = $character['demeanor'] ?? '';
    $concept = $character['concept'] ?? '';
    $clan = $character['clan'] ?? '';
    $generation = $character['generation'] ?? 0;
    $sire = $character['embrace_info'] ?? '';
    $pc = 0; // All are NPCs
    $biography = $character['goal'] ?? '';
    $equipment = ''; // Not in simple format
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
        $equipment,
        $xp_total,
        $xp_available,
        $blood_pool,
        $notes
    );
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
    }
    
    $character_id = mysqli_insert_id($conn);
    echo "✅ Character created (ID: $character_id)\n\n";

    // 2. Insert disciplines (simple format: {"potence": 3, "celerity": 2})
    echo "📝 Inserting disciplines...\n";
    
    $disc_stmt = mysqli_prepare($conn, "
        INSERT INTO character_disciplines (character_id, discipline_name, level, xp_cost)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        level = VALUES(level),
        xp_cost = VALUES(xp_cost)
    ");
    
    if (!$disc_stmt) {
        throw new Exception("Disciplines prepare failed: " . mysqli_error($conn));
    }
    
    $discipline_count = 0;
    
    // Handle simple discipline format: {"potence": 3, "celerity": 2}
    if (isset($character['disciplines']) && is_array($character['disciplines'])) {
        foreach ($character['disciplines'] as $discipline_name => $level) {
            // Capitalize first letter for consistency
            $discipline_name_capitalized = ucfirst(strtolower($discipline_name));
            $level = (int)$level;
            $xp_cost = 0;
            
            mysqli_stmt_bind_param($disc_stmt, "isii",
                $character_id,
                $discipline_name_capitalized,
                $level,
                $xp_cost
            );
            
            if (!mysqli_stmt_execute($disc_stmt)) {
                throw new Exception("Discipline insert failed for '{$discipline_name_capitalized}': " . mysqli_stmt_error($disc_stmt));
            }
            $discipline_count++;
            echo "   + {$discipline_name_capitalized} (level {$level})\n";
        }
    }
    
    echo "✅ {$discipline_count} disciplines added\n\n";

    // 3. Insert merits and flaws
    if (!empty($character['merits_flaws'])) {
        echo "📝 Inserting merits/flaws...\n";
        $mf_stmt = mysqli_prepare($conn, "
            INSERT INTO character_merits_flaws (
                character_id, name, type, category, point_value, point_cost, description
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$mf_stmt) {
            throw new Exception("Merits/flaws prepare failed: " . mysqli_error($conn));
        }
        
        $mf_count = 0;
        foreach ($character['merits_flaws'] as $mf) {
            // Capitalize first letter of type for ENUM
            $type_capitalized = ucfirst(strtolower($mf['type']));
            $category = ucfirst(strtolower($mf['type'])); // Use type as category for simple format
            
            mysqli_stmt_bind_param($mf_stmt, "isssiis",
                $character_id,
                $mf['name'],
                $type_capitalized,
                $category,
                $mf['cost'], // point_value
                $mf['cost'], // point_cost
                $mf['description']
            );
            if (!mysqli_stmt_execute($mf_stmt)) {
                throw new Exception("Merit/flaw insert failed for '{$mf['name']}': " . mysqli_stmt_error($mf_stmt));
            }
            $mf_count++;
        }
        echo "✅ {$mf_count} merits/flaws added\n\n";
    }

    // 4. Insert morality (if basic traits available)
    if (isset($character['traits']['morality']) && $character['traits']['morality']) {
        echo "📝 Inserting morality...\n";
        $moral_stmt = mysqli_prepare($conn, "
            INSERT INTO character_morality (
                character_id, path_name, path_rating, conscience, 
                self_control, courage, willpower_permanent, willpower_current
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $path_name = $character['traits']['morality'] ?? 'Humanity';
        $path_rating = 6; // Default
        $conscience = 3;
        $self_control = 3;
        $courage = 4;
        $willpower_permanent = $character['traits']['willpower'] ?? 5;
        $willpower_current = $willpower_permanent;
        
        mysqli_stmt_bind_param($moral_stmt, "isiiiiii",
            $character_id,
            $path_name,
            $path_rating,
            $conscience,
            $self_control,
            $courage,
            $willpower_permanent,
            $willpower_current
        );
        mysqli_stmt_execute($moral_stmt);
        echo "✅ Morality added\n\n";
    }

    // Commit transaction
    mysqli_commit($conn);
    
    echo "=================================================================\n";
    echo "Import Complete!\n";
    echo "=================================================================\n";
    echo "✅ {$character_name} imported successfully\n";
    echo "   Character ID: $character_id\n";
    echo "=================================================================\n\n";
    echo "🎉 Character is ready to use!\n\n";
    
    exit(0);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo "\n=================================================================\n";
    echo "❌ ERROR: Import failed\n";
    echo "=================================================================\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}

mysqli_close($conn);
?>

