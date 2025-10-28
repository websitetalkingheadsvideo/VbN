<?php
/**
 * Generic Character Import Script
 * Usage: https://websitetalkingheads.com/vbn/data/import_character.php?file=Character%20Name.json
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get filename from query parameter
$filename = isset($_GET['file']) ? $_GET['file'] : null;

if (!$filename) {
    die("ERROR: No file specified. Usage: ?file=Character%20Name.json\n");
}

echo "=================================================================\n";
echo "Character Import\n";
echo "=================================================================\n\n";

// Include database connection
echo "📡 Loading database connection...\n";
$connect_file = '/usr/home/working/public_html/vbn.talkingheads.video/includes/connect.php';
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
$json_file = '/usr/home/working/public_html/vbn.talkingheads.video/reference/Characters/' . $filename;

echo "🔍 Looking for file: $json_file\n";
echo "🔍 __DIR__ is: " . __DIR__ . "\n";

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
$char_name = $character['character_name'] ?? $character['name'] ?? 'Unknown';
echo "   Character: $char_name\n";
echo "   Clan: {$character['clan']}\n\n";

// Start transaction
echo "🚀 Starting import transaction...\n\n";
$conn->begin_transaction();

try {
    // 1. Insert main character record
    echo "📝 Inserting character record...\n";
    
    // Use user_id = 1 for NPCs (default ST/admin user)
    $user_id = 1;
    
    $stmt = $conn->prepare("
        INSERT INTO characters (
            user_id, character_name, player_name, chronicle, nature, demeanor, 
            concept, clan, generation, sire, pc, biography, equipment,
            experience_total, experience_unspent, blood_pool_current, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("isssssssisissiiis",
        $user_id,
        $char_name,
        $character['player_name'],
        $character['chronicle'],
        $character['nature'],
        $character['demeanor'],
        $character['concept'],
        $character['clan'],
        $character['generation'],
        $character['sire'],
        $character['pc'],
        $character['biography'],
        $character['equipment'],
        $character['status']['xp_total'],
        $character['status']['xp_available'],
        $character['status']['blood_pool'],
        $character['status']['notes']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $character_id = $conn->insert_id;
    echo "✅ Character created (ID: $character_id)\n\n";

    // 2. Insert traits (positive)
    echo "📝 Inserting positive traits...\n";
    $trait_stmt = $conn->prepare("
        INSERT INTO character_traits (character_id, trait_category, trait_name)
        VALUES (?, ?, ?)
    ");
    
    $trait_count = 0;
    foreach ($character['traits'] as $category => $traits) {
        foreach ($traits as $trait) {
            $trait_stmt->bind_param("iss", $character_id, $category, $trait);
            $trait_stmt->execute();
            $trait_count++;
        }
    }
    echo "✅ {$trait_count} positive traits added\n\n";

    // 3. Insert negative traits
    echo "📝 Inserting negative traits...\n";
    $neg_trait_stmt = $conn->prepare("
        INSERT INTO character_negative_traits (character_id, trait_category, trait_name)
        VALUES (?, ?, ?)
    ");
    
    if (!$neg_trait_stmt) {
        throw new Exception("Negative traits prepare failed: " . $conn->error);
    }
    
    $neg_count = 0;
    foreach ($character['negativeTraits'] as $category => $traits) {
        foreach ($traits as $trait) {
            $neg_trait_stmt->bind_param("iss", $character_id, $category, $trait);
            if (!$neg_trait_stmt->execute()) {
                throw new Exception("Negative trait insert failed: " . $neg_trait_stmt->error);
            }
            $neg_count++;
        }
    }
    echo "✅ {$neg_count} negative traits added\n\n";

    // 4. Insert abilities (with specializations in same table)
    echo "📝 Inserting abilities...\n";
    $ability_stmt = $conn->prepare("
        INSERT INTO character_abilities (character_id, ability_name, level, specialization)
        VALUES (?, ?, ?, ?)
    ");
    
    if (!$ability_stmt) {
        throw new Exception("Abilities prepare failed: " . $conn->error);
    }
    
    foreach ($character['abilities'] as $ability) {
        // Check if this ability has a specialization
        $spec = isset($character['specializations'][$ability['name']]) 
            ? $character['specializations'][$ability['name']] 
            : null;
            
        $ability_stmt->bind_param("isis",
            $character_id,
            $ability['name'],
            $ability['level'],
            $spec
        );
        if (!$ability_stmt->execute()) {
            throw new Exception("Ability insert failed for '{$ability['name']}': " . $ability_stmt->error);
        }
    }
    echo "✅ " . count($character['abilities']) . " abilities added\n\n";

    // 5. Insert disciplines (each power is a separate row)
    echo "📝 Inserting disciplines...\n";
    
    // Map numeric levels to enum values
    $level_map = [
        1 => 'Basic',
        2 => 'Basic',
        3 => 'Intermediate',
        4 => 'Advanced',
        5 => 'Advanced'
    ];
    
    $disc_stmt = $conn->prepare("
        INSERT INTO character_disciplines (character_id, discipline_name, level, power_name)
        VALUES (?, ?, ?, ?)
    ");
    
    if (!$disc_stmt) {
        throw new Exception("Disciplines prepare failed: " . $conn->error);
    }
    
    $power_count = 0;
    foreach ($character['disciplines'] as $discipline) {
        if (!empty($discipline['powers'])) {
            // Insert each power as a separate row
            foreach ($discipline['powers'] as $power) {
                $level_enum = $level_map[$power['level']] ?? 'Basic';
                
                $disc_stmt->bind_param("isss",
                    $character_id,
                    $discipline['name'],
                    $level_enum,
                    $power['power']
                );
                if (!$disc_stmt->execute()) {
                    throw new Exception("Discipline power insert failed for '{$discipline['name']} - {$power['power']}': " . $disc_stmt->error);
                }
                $power_count++;
            }
        }
    }
    echo "✅ {$power_count} discipline powers added\n\n";

    // 6. Insert backgrounds
    echo "📝 Inserting backgrounds...\n";
    $bg_stmt = $conn->prepare("
        INSERT INTO character_backgrounds (character_id, background_name, level, description)
        VALUES (?, ?, ?, ?)
    ");
    
    if (!$bg_stmt) {
        throw new Exception("Backgrounds prepare failed: " . $conn->error);
    }
    
    $bg_count = 0;
    foreach ($character['backgrounds'] as $name => $level) {
        if ($level > 0) {
            $details = $character['backgroundDetails'][$name] ?? null;
            $bg_stmt->bind_param("isis", $character_id, $name, $level, $details);
            if (!$bg_stmt->execute()) {
                throw new Exception("Background insert failed for '{$name}': " . $bg_stmt->error);
            }
            $bg_count++;
        }
    }
    echo "✅ {$bg_count} backgrounds added\n\n";

    // 7. Insert morality
    echo "📝 Inserting morality...\n";
    $moral_stmt = $conn->prepare("
        INSERT INTO character_morality (
            character_id, path_name, path_rating, conscience, 
            self_control, courage, willpower_permanent, willpower_current
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $moral_stmt->bind_param("isiiiiii",
        $character_id,
        $character['morality']['path_name'],
        $character['morality']['path_rating'],
        $character['morality']['conscience'],
        $character['morality']['self_control'],
        $character['morality']['courage'],
        $character['morality']['willpower_permanent'],
        $character['morality']['willpower_current']
    );
    $moral_stmt->execute();
    echo "✅ Morality added\n\n";

    // 8. Insert merits and flaws
    if (!empty($character['merits_flaws'])) {
        echo "📝 Inserting merits/flaws...\n";
        $mf_stmt = $conn->prepare("
            INSERT INTO character_merits_flaws (
                character_id, name, type, category, point_value, point_cost, description
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$mf_stmt) {
            throw new Exception("Merits/flaws prepare failed: " . $conn->error);
        }
        
        foreach ($character['merits_flaws'] as $mf) {
            // Capitalize first letter of type for ENUM
            $type_capitalized = ucfirst(strtolower($mf['type']));
            
            $mf_stmt->bind_param("isssiis",
                $character_id,
                $mf['name'],
                $type_capitalized,
                $mf['category'],
                $mf['cost'], // point_value
                $mf['cost'], // point_cost
                $mf['description']
            );
            if (!$mf_stmt->execute()) {
                throw new Exception("Merit/flaw insert failed for '{$mf['name']}': " . $mf_stmt->error);
            }
        }
        echo "✅ " . count($character['merits_flaws']) . " merits/flaws added\n\n";
    }

    // Commit transaction
    $conn->commit();
    
    echo "=================================================================\n";
    echo "Import Complete!\n";
    echo "=================================================================\n";
    echo "✅ {$character['character_name']} imported successfully\n";
    echo "   Character ID: $character_id\n";
    echo "=================================================================\n\n";
    echo "🎉 Character is ready to use!\n\n";
    
    exit(0);

} catch (Exception $e) {
    $conn->rollback();
    echo "\n=================================================================\n";
    echo "❌ ERROR: Import failed\n";
    echo "=================================================================\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}

mysqli_close($conn);
?>

