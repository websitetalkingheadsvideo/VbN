<?php
/**
 * Delete Character
 * Handles character deletion with confirmation
 */

require_once __DIR__ . '/../includes/connect.php';

$character_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] : '';

if (!$character_id) {
    die("ERROR: No character ID provided");
}

// Get character info using prepared statement
$char = db_fetch_one("SELECT character_name, clan, id FROM characters WHERE id = ?", [$character_id], 'i');

if (!$char) {
    die("ERROR: Character ID $character_id not found");
}

// If not confirmed, show confirmation page
if ($confirm !== 'yes') {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Character - Confirmation</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cinzel', 'Times New Roman', serif;
            background: linear-gradient(135deg, #1a0000 0%, #330000 50%, #1a0000 100%);
            color: #d4af37;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            max-width: 600px;
            background: rgba(0, 0, 0, 0.7);
            border: 2px solid #d4af37;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 0 30px rgba(212, 175, 55, 0.3);
            text-align: center;
        }
        h1 {
            font-size: 2em;
            margin-bottom: 20px;
            color: #ff6666;
            text-shadow: 0 0 10px rgba(255, 100, 100, 0.5);
        }
        .warning {
            background: rgba(255, 100, 100, 0.1);
            border: 2px solid #ff6666;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .character-info {
            font-size: 1.3em;
            margin: 20px 0;
            color: #fff;
        }
        .buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
        }
        .btn {
            padding: 15px 30px;
            font-size: 1.1em;
            border: 2px solid;
            border-radius: 5px;
            text-decoration: none;
            font-family: 'Cinzel', serif;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-delete {
            background: #660000;
            border-color: #ff6666;
            color: #fff;
        }
        .btn-delete:hover {
            background: #990000;
            box-shadow: 0 0 20px rgba(255, 100, 100, 0.5);
        }
        .btn-cancel {
            background: #003366;
            border-color: #4a90e2;
            color: #fff;
        }
        .btn-cancel:hover {
            background: #004488;
            box-shadow: 0 0 20px rgba(74, 144, 226, 0.5);
        }
        ul {
            text-align: left;
            margin: 15px 0;
            color: #c4a037;
        }
        li {
            margin: 8px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ Delete Character</h1>
        
        <div class="warning">
            <p style="font-size: 1.2em; margin-bottom: 15px;">
                <strong>This action cannot be undone!</strong>
            </p>
            
            <div class="character-info">
                <?= htmlspecialchars($char['character_name']) ?><br>
                <span style="font-size: 0.8em; color: #c4a037;"><?= htmlspecialchars($char['clan']) ?> (ID: <?= $char['id'] ?>)</span>
            </div>
            
            <p style="margin-top: 20px;">This will permanently delete:</p>
            <ul>
                <li>Character record</li>
                <li>All traits (positive & negative)</li>
                <li>All abilities & specializations</li>
                <li>All disciplines & powers</li>
                <li>All backgrounds</li>
                <li>Morality data</li>
                <li>All merits & flaws</li>
            </ul>
        </div>
        
        <div class="buttons">
            <a href="delete_character.php?id=<?= $character_id ?>&confirm=yes" class="btn btn-delete">
                Yes, Delete Character
            </a>
            <a href="list_characters.php" class="btn btn-cancel">
                Cancel
            </a>
        </div>
    </div>
</body>
</html>
<?php
    exit;
}

// Confirmed - proceed with deletion
echo "=================================================================\n";
echo "Deleting Character\n";
echo "=================================================================\n\n";

db_begin_transaction($conn);

try {
    echo "📝 Deleting character: {$char['character_name']} (ID: {$char['id']})\n\n";
    
    // Delete in reverse order of dependencies
    $tables = [
        'character_traits',
        'character_negative_traits',
        'character_abilities',
        'character_disciplines',
        'character_backgrounds',
        'character_morality',
        'character_merits_flaws'
    ];
    
    foreach ($tables as $table) {
        // Use prepared statement for safety
        $affected = db_execute("DELETE FROM $table WHERE character_id = ?", [$character_id], 'i');
        echo "✅ Deleted from $table: $affected rows\n";
    }
    
    // Finally delete the character itself
    db_execute("DELETE FROM characters WHERE id = ?", [$character_id], 'i');
    echo "\n✅ Character record deleted\n";
    
    db_commit($conn);
    
    echo "\n=================================================================\n";
    echo "Deletion Complete!\n";
    echo "=================================================================\n";
    echo "✅ {$char['character_name']} has been permanently deleted\n\n";
    
    // Redirect after 2 seconds
    echo "<meta http-equiv='refresh' content='2;url=list_characters.php'>\n";
    echo "<p style='color: #4a90e2;'>Redirecting to character list...</p>\n";
    
} catch (Exception $e) {
    db_rollback($conn);
    echo "\n=================================================================\n";
    echo "❌ ERROR: Deletion failed\n";
    echo "=================================================================\n";
    echo $e->getMessage() . "\n\n";
    exit(1);
}

$conn->close();
?>


