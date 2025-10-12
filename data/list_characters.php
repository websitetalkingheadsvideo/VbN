<?php
/**
 * Character Listing Page
 * Shows all characters in the database
 */

require_once __DIR__ . '/../includes/connect.php';

if (!$conn) {
    die("❌ Database connection failed");
}

// Get all characters
$result = $conn->query("
    SELECT 
        id,
        character_name,
        clan,
        generation,
        concept,
        pc,
        player_name,
        created_at
    FROM characters 
    ORDER BY id DESC
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Character List - Valley by Night</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Cinzel', 'Times New Roman', serif;
            background: linear-gradient(135deg, #1a0000 0%, #330000 50%, #1a0000 100%);
            color: #d4af37;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(0, 0, 0, 0.7);
            border: 2px solid #d4af37;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 30px rgba(212, 175, 55, 0.3);
        }
        h1 {
            font-size: 2.5em;
            text-align: center;
            margin-bottom: 30px;
            color: #d4af37;
            text-shadow: 0 0 10px rgba(212, 175, 55, 0.5);
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 5px;
        }
        .stat-box {
            text-align: center;
            padding: 15px;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #d4af37;
            border-radius: 5px;
        }
        .stat-label {
            font-size: 0.9em;
            color: #b49037;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 2em;
            color: #d4af37;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background: rgba(212, 175, 55, 0.2);
            color: #d4af37;
            padding: 15px;
            text-align: left;
            border-bottom: 2px solid #d4af37;
            font-size: 1.1em;
        }
        td {
            padding: 12px 15px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            color: #d4af37;
        }
        tr:hover {
            background: rgba(212, 175, 55, 0.1);
        }
        .character-link {
            color: #d4af37;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .character-link:hover {
            color: #ffffff;
            text-shadow: 0 0 10px rgba(212, 175, 55, 0.8);
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .badge-pc {
            background: #00ff00;
            color: #000;
        }
        .badge-npc {
            background: #ff6600;
            color: #000;
        }
        .concept {
            font-style: italic;
            color: #c4a037;
            font-size: 0.9em;
        }
        .import-section {
            margin-top: 30px;
            padding: 20px;
            background: rgba(0, 100, 200, 0.1);
            border: 1px solid #4a90e2;
            border-radius: 5px;
        }
        .import-section h2 {
            color: #4a90e2;
            margin-bottom: 15px;
        }
        .import-section a {
            color: #4a90e2;
            text-decoration: none;
        }
        .import-section a:hover {
            text-decoration: underline;
        }
        .delete-btn {
            color: #ff6666;
            text-decoration: none;
            font-size: 0.9em;
            padding: 5px 10px;
            border: 1px solid #ff6666;
            border-radius: 3px;
            display: inline-block;
            transition: all 0.3s;
        }
        .delete-btn:hover {
            background: rgba(255, 100, 100, 0.2);
            box-shadow: 0 0 10px rgba(255, 100, 100, 0.5);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Valley by Night - Character Database</h1>
        
        <?php
        $total = $result->num_rows;
        $pcs = $conn->query("SELECT COUNT(*) as count FROM characters WHERE pc = 1")->fetch_assoc()['count'];
        $npcs = $total - $pcs;
        
        // Get clan distribution
        $clans = $conn->query("SELECT clan, COUNT(*) as count FROM characters GROUP BY clan ORDER BY count DESC LIMIT 5");
        ?>
        
        <div class="stats">
            <div class="stat-box">
                <div class="stat-label">Total Characters</div>
                <div class="stat-value"><?= $total ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">Player Characters</div>
                <div class="stat-value"><?= $pcs ?></div>
            </div>
            <div class="stat-box">
                <div class="stat-label">NPCs</div>
                <div class="stat-value"><?= $npcs ?></div>
            </div>
        </div>

        <?php if ($total == 0): ?>
            <p style="text-align: center; padding: 40px; font-size: 1.2em;">No characters in database yet.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Clan</th>
                        <th>Gen</th>
                        <th>Concept</th>
                        <th>Type</th>
                        <th>Player</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($char = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $char['id'] ?></td>
                            <td>
                                <a href="view_character.php?id=<?= $char['id'] ?>" class="character-link">
                                    <?= htmlspecialchars($char['character_name']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($char['clan']) ?></td>
                            <td><?= $char['generation'] ?>th</td>
                            <td class="concept"><?= htmlspecialchars($char['concept']) ?></td>
                            <td>
                                <?php if ($char['pc']): ?>
                                    <span class="badge badge-pc">PC</span>
                                <?php else: ?>
                                    <span class="badge badge-npc">NPC</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($char['player_name']) ?></td>
                            <td><?= date('Y-m-d', strtotime($char['created_at'])) ?></td>
                            <td>
                                <a href="delete_character.php?id=<?= $char['id'] ?>" 
                                   class="delete-btn"
                                   title="Delete character">
                                    🗑️ Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="import-section">
            <h2>📥 Import New Characters</h2>
            <p style="margin-bottom: 10px;">To import new characters from JSON:</p>
            <ul style="list-style: none; padding-left: 0;">
                <li style="margin: 8px 0;">
                    🔹 <strong>Jax:</strong> <a href="import_character.php?file=jax.json">Import jax.json</a>
                </li>
                <li style="margin: 8px 0;">
                    🔹 <strong>Violet:</strong> <a href="import_character.php?file=Violet.json">Import Violet.json</a>
                </li>
                <li style="margin: 8px 0;">
                    🔹 <a href="IMPORT_GUIDE.md" style="color: #999;">View Import Guide</a>
                </li>
            </ul>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>

