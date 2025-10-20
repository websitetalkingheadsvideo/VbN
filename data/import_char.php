<?php
/**
 * Character Import Script - Direct Version
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get filename from query parameter
$filename = isset($_GET['file']) ? $_GET['file'] : null;

if (!$filename) {
    die("ERROR: No file specified. Usage: ?file=Character.json\n");
}

echo "=================================================================\n";
echo "Character Import - Direct Version\n";
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

echo "✅ Database connection established\n\n";

// Read and decode JSON - Look directly in data folder
$json_file = __DIR__ . '/' . $filename;

echo "🔍 Looking for file: $json_file\n";

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
echo "   Character: {$character['character_name']}\n";
echo "   Clan: {$character['clan']}\n\n";

// Start transaction
echo "🚀 Starting import transaction...\n\n";
$conn->begin_transaction();

try {
    // 1. Insert main character record
    echo "📝 Inserting character record...\n";
    
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
        $character['character_name'],
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
    
    $neg_count = 0;
    foreach ($character['negativeTraits'] as $category => $traits) {
        foreach ($traits as $trait) {
            $neg_trait_stmt->bind_param("iss", $character_id, $category, $trait);
            $neg_trait_stmt->execute();
            $neg_count++;
        }
    }
    echo "✅ {$neg_count} negative traits added\n\n";

    // 4. Insert abilities
    echo "📝 Inserting abilities...\n";
    $ability_stmt = $conn->prepare("
        INSERT INTO character_abilities (character_id, ability_name, level, specialization)
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($character['abilities'] as $ability) {
        $spec = isset($character['specializations'][$ability['name']]) 
            ? $character['specializations'][$ability['name']] 
            : null;
            
        $ability_stmt->bind_param("isis",
            $character_id,
            $ability['name'],
            $ability['level'],
            $spec
        );
        $ability_stmt->execute();
    }
    echo "✅ " . count($character['abilities']) . " abilities added\n\n";

    // 5. Insert disciplines
    echo "📝 Inserting disciplines...\n";
    
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
    
    $power_count = 0;
    foreach ($character['disciplines'] as $discipline) {
        if (!empty($discipline['powers'])) {
            foreach ($discipline['powers'] as $power) {
                $level_enum = $level_map[$power['level']] ?? 'Basic';
                
                $disc_stmt->bind_param("isss",
                    $character_id,
                    $discipline['name'],
                    $level_enum,
                    $power['power']
                );
                $disc_stmt->execute();
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
    
    $bg_count = 0;
    foreach ($character['backgrounds'] as $name => $level) {
        if ($level > 0) {
            $details = $character['backgroundDetails'][$name] ?? null;
            $bg_stmt->bind_param("isis", $character_id, $name, $level, $details);
            $bg_stmt->execute();
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
        
        foreach ($character['merits_flaws'] as $mf) {
            $type_capitalized = ucfirst(strtolower($mf['type']));
            
            $mf_stmt->bind_param("isssiis",
                $character_id,
                $mf['name'],
                $type_capitalized,
                $mf['category'],
                $mf['cost'],
                $mf['cost'],
                $mf['description']
            );
            $mf_stmt->execute();
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
