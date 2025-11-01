<?php
/**
 * Character View Page
 * Displays complete character data from database
 * Usage: view_character.php?id=42
 */

require_once __DIR__ . '/../includes/connect.php';
require_once __DIR__ . '/../includes/discipline_functions.php';

$character_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$character_id) {
    die("ERROR: No character ID provided. Usage: view_character.php?id=42");
}

// Get character data using helper function with explicit columns
$char = db_fetch_one($conn,
    "SELECT id, user_id, character_name, player_name, chronicle, nature, demeanor, concept,
            clan, generation, sire, pc, biography, character_image, equipment, notes,
            total_xp, spent_xp, created_at, updated_at
     FROM characters WHERE id = ?",
    "i",
    [$character_id]
);

if (!$char) {
    die("ERROR: Character ID $character_id not found");
}

// Get all related data using prepared statements with explicit columns
$traits_result = db_select($conn,
    "SELECT id, trait_name, trait_category, trait_type, xp_cost
     FROM character_traits WHERE character_id = ? ORDER BY trait_category, trait_name",
    "i",
    [$character_id]
);

$neg_traits_result = db_select($conn,
    "SELECT id, trait_name, trait_category, xp_cost
     FROM character_negative_traits WHERE character_id = ? ORDER BY trait_category, trait_name",
    "i",
    [$character_id]
);

$abilities_result = db_select($conn,
    "SELECT id, ability_name, ability_category, specialization, level, xp_cost
     FROM character_abilities WHERE character_id = ? ORDER BY level DESC, ability_name",
    "i",
    [$character_id]
);

$disciplines_result = db_select($conn,
    "SELECT id, discipline_name, level, xp_cost
     FROM character_disciplines WHERE character_id = ? ORDER BY discipline_name",
    "i",
    [$character_id]
);

$backgrounds_result = db_select($conn,
    "SELECT id, background_name, level, xp_cost
     FROM character_backgrounds WHERE character_id = ? ORDER BY level DESC",
    "i",
    [$character_id]
);

$morality = db_fetch_one($conn,
    "SELECT id, path_name, path_rating, conscience, self_control, courage,
            willpower_permanent, willpower_current, humanity
     FROM character_morality WHERE character_id = ?",
    "i",
    [$character_id]
);

$merits_flaws_result = db_select($conn,
    "SELECT id, name, type, category, point_value, description, xp_bonus
     FROM character_merits_flaws WHERE character_id = ? ORDER BY type, category",
    "i",
    [$character_id]
);

// Convert results to arrays for iteration
$traits = $traits_result ? $traits_result : null;
$neg_traits = $neg_traits_result ? $neg_traits_result : null;
$abilities = $abilities_result ? $abilities_result : null;
$disciplines = $disciplines_result ? $disciplines_result : null;
$backgrounds = $backgrounds_result ? $backgrounds_result : null;
$merits_flaws = $merits_flaws_result ? $merits_flaws_result : null;

// Organize traits by category
$trait_categories = ['Physical' => [], 'Social' => [], 'Mental' => []];
if ($traits) {
    while ($trait = $traits->fetch_assoc()) {
        $trait_categories[$trait['trait_category']][] = $trait['trait_name'];
    }
}

$neg_trait_categories = ['Physical' => [], 'Social' => [], 'Mental' => []];
if ($neg_traits) {
    while ($trait = $neg_traits->fetch_assoc()) {
        $neg_trait_categories[$trait['trait_category']][] = $trait['trait_name'];
    }
}

// Get disciplines with their powers using helper function
try {
    $all_disciplines = getCharacterAllDisciplines($character_id);
    if (!is_array($all_disciplines)) {
        error_log("getCharacterAllDisciplines returned non-array for character $character_id");
        $all_disciplines = [];
    }
} catch (Exception $e) {
    error_log("Error getting character disciplines: " . $e->getMessage());
    $all_disciplines = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($char['character_name']) ?> - Character Sheet</title>
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
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(0, 0, 0, 0.7);
            border: 2px solid #d4af37;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 30px rgba(212, 175, 55, 0.3);
        }
        .back-link {
            color: #4a90e2;
            text-decoration: none;
            font-size: 1.1em;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        h1 {
            font-size: 2.5em;
            text-align: center;
            margin-bottom: 10px;
            color: #d4af37;
            text-shadow: 0 0 10px rgba(212, 175, 55, 0.5);
        }
        h2 {
            font-size: 1.8em;
            margin: 25px 0 15px 0;
            color: #c4a037;
            border-bottom: 2px solid #d4af37;
            padding-bottom: 5px;
        }
        h3 {
            font-size: 1.3em;
            margin: 15px 0 10px 0;
            color: #b49037;
        }
        .header-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
            padding: 20px;
            background: rgba(212, 175, 55, 0.1);
            border-radius: 5px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-weight: bold;
            font-size: 0.9em;
            color: #b49037;
            margin-bottom: 5px;
        }
        .info-value {
            color: #d4af37;
            font-size: 1.1em;
        }
        .trait-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        .trait-box {
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid #d4af37;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .ability-list, .discipline-list, .background-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }
        .ability-item, .discipline-item, .background-item {
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid #d4af37;
            padding: 12px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dots {
            color: #ff0000;
            font-size: 1.2em;
            letter-spacing: 2px;
        }
        .biography, .notes {
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid #d4af37;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            line-height: 1.6;
        }
        .merit-flaw-item {
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid #d4af37;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .merit { border-left: 4px solid #00ff00; }
        .flaw { border-left: 4px solid #ff0000; }
        .powers-list {
            margin-top: 8px;
            padding-left: 20px;
            font-size: 0.9em;
            color: #c4a037;
        }
        .specialization {
            font-size: 0.85em;
            color: #b49037;
            font-style: italic;
            margin-top: 3px;
        }
        .morality-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .morality-item {
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid #d4af37;
            padding: 12px;
            border-radius: 5px;
            text-align: center;
        }
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .delete-btn {
            color: #ff6666;
            text-decoration: none;
            padding: 10px 20px;
            border: 2px solid #ff6666;
            border-radius: 5px;
            font-size: 1em;
            transition: all 0.3s;
        }
        .delete-btn:hover {
            background: rgba(255, 100, 100, 0.2);
            box-shadow: 0 0 15px rgba(255, 100, 100, 0.5);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="actions-bar">
            <a href="list_characters.php" class="back-link">← Back to Character List</a>
            <a href="delete_character.php?id=<?= $character_id ?>" class="delete-btn">🗑️ Delete Character</a>
        </div>
        
        <h1><?= htmlspecialchars($char['character_name']) ?></h1>
        
        <div class="header-info">
            <div class="info-item">
                <span class="info-label">Player</span>
                <span class="info-value"><?= htmlspecialchars($char['player_name']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Chronicle</span>
                <span class="info-value"><?= htmlspecialchars($char['chronicle']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Clan</span>
                <span class="info-value"><?= htmlspecialchars($char['clan']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Generation</span>
                <span class="info-value"><?= $char['generation'] ?>th</span>
            </div>
            <div class="info-item">
                <span class="info-label">Nature</span>
                <span class="info-value"><?= htmlspecialchars($char['nature']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Demeanor</span>
                <span class="info-value"><?= htmlspecialchars($char['demeanor']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Concept</span>
                <span class="info-value"><?= htmlspecialchars($char['concept']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Sire</span>
                <span class="info-value"><?= htmlspecialchars($char['sire']) ?></span>
            </div>
        </div>

        <h2>Biography</h2>
        <div class="biography"><?= nl2br(htmlspecialchars($char['biography'])) ?></div>

        <h2>Traits</h2>
        <?php foreach (['Physical', 'Social', 'Mental'] as $category): ?>
            <h3><?= $category ?> (<?= count($trait_categories[$category]) ?>)</h3>
            <div class="trait-grid">
                <?php foreach ($trait_categories[$category] as $trait): ?>
                    <div class="trait-box"><?= htmlspecialchars($trait) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <?php if (array_sum(array_map('count', $neg_trait_categories)) > 0): ?>
            <h2>Negative Traits</h2>
            <?php foreach (['Physical', 'Social', 'Mental'] as $category): ?>
                <?php if (!empty($neg_trait_categories[$category])): ?>
                    <h3><?= $category ?> (<?= count($neg_trait_categories[$category]) ?>)</h3>
                    <div class="trait-grid">
                        <?php foreach ($neg_trait_categories[$category] as $trait): ?>
                            <div class="trait-box"><?= htmlspecialchars($trait) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <h2>Abilities</h2>
        <div class="ability-list">
            <?php 
            if ($abilities):
                $abilities->data_seek(0);
                while ($ability = $abilities->fetch_assoc()): 
                $dots = str_repeat('●', $ability['level']);
            ?>
                <div class="ability-item">
                    <div>
                        <strong><?= htmlspecialchars($ability['ability_name']) ?></strong>
                        <?php if ($ability['specialization']): ?>
                            <div class="specialization">(<?= htmlspecialchars($ability['specialization']) ?>)</div>
                        <?php endif; ?>
                    </div>
                    <span class="dots"><?= $dots ?></span>
                </div>
            <?php endwhile; 
            endif; ?>
        </div>

        <h2>Disciplines</h2>
        <?php if (empty($all_disciplines)): ?>
            <p class="empty-state">No disciplines recorded.</p>
        <?php else: ?>
            <div class="discipline-list">
                <?php foreach ($all_disciplines as $disc_name => $disc_data): ?>
                    <div class="discipline-item">
                        <div style="width: 100%;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong><?= htmlspecialchars($disc_name) ?> <?= $disc_data['level'] ?></strong>
                                <?php if (!empty($disc_data['powers'])): ?>
                                    <span style="color: #c4a037;"><?= count($disc_data['powers']) ?> powers</span>
                                <?php else: ?>
                                    <span style="color: #999; font-style: italic;">Custom/Path</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($disc_data['powers'])): ?>
                                <div class="powers-list">
                                    <?php foreach ($disc_data['powers'] as $power): ?>
                                        <div>• <?= htmlspecialchars($power['power_name']) ?> <span style="color: #999; font-size: 0.85em;">(Level <?= $power['level'] ?>)</span></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h2>Backgrounds</h2>
        <div class="background-list">
            <?php 
            if ($backgrounds):
                $backgrounds->data_seek(0);
                while ($bg = $backgrounds->fetch_assoc()): 
                if ($bg['level'] > 0):
                    $dots = str_repeat('●', $bg['level']);
            ?>
                <div class="background-item">
                    <div style="width: 100%;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <strong><?= htmlspecialchars($bg['background_name']) ?></strong>
                            <span class="dots"><?= $dots ?></span>
                        </div>
                        <?php if ($bg['description']): ?>
                            <div class="specialization"><?= htmlspecialchars($bg['description']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
                endif;
            endwhile;
            endif; ?>
        </div>

        <?php if ($morality): ?>
            <h2>Morality</h2>
            <div class="morality-grid">
                <div class="morality-item">
                    <div class="info-label"><?= htmlspecialchars($morality['path_name']) ?></div>
                    <div class="dots"><?= str_repeat('●', $morality['path_rating']) ?></div>
                </div>
                <div class="morality-item">
                    <div class="info-label">Conscience</div>
                    <div class="dots"><?= str_repeat('●', $morality['conscience']) ?></div>
                </div>
                <div class="morality-item">
                    <div class="info-label">Self-Control</div>
                    <div class="dots"><?= str_repeat('●', $morality['self_control']) ?></div>
                </div>
                <div class="morality-item">
                    <div class="info-label">Courage</div>
                    <div class="dots"><?= str_repeat('●', $morality['courage']) ?></div>
                </div>
                <div class="morality-item">
                    <div class="info-label">Willpower</div>
                    <div class="dots"><?= str_repeat('●', $morality['willpower_permanent']) ?></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($merits_flaws && $merits_flaws->num_rows > 0): ?>
            <h2>Merits & Flaws</h2>
            <?php 
            $merits_flaws->data_seek(0);
            while ($mf = $merits_flaws->fetch_assoc()): ?>
                <div class="merit-flaw-item <?= strtolower($mf['type']) ?>">
                    <strong><?= htmlspecialchars($mf['name']) ?></strong>
                    <span style="color: #999;"> (<?= $mf['type'] ?>, <?= $mf['point_value'] ?> pts)</span>
                    <div style="margin-top: 8px; color: #c4a037;">
                        <?= htmlspecialchars($mf['description']) ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>

        <?php if ($char['equipment']): ?>
            <h2>Equipment</h2>
            <div class="biography"><?= nl2br(htmlspecialchars($char['equipment'])) ?></div>
        <?php endif; ?>

        <?php if ($char['notes']): ?>
            <h2>Notes</h2>
            <div class="notes"><?= nl2br(htmlspecialchars($char['notes'])) ?></div>
        <?php endif; ?>

        <h2>Status</h2>
        <div class="header-info">
            <div class="info-item">
                <span class="info-label">XP Total</span>
                <span class="info-value"><?= $char['experience_total'] ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">XP Available</span>
                <span class="info-value"><?= $char['experience_unspent'] ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Blood Pool</span>
                <span class="info-value"><?= $char['blood_pool_current'] ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Created</span>
                <span class="info-value"><?= date('Y-m-d', strtotime($char['created_at'])) ?></span>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>

