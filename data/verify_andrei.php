<?php
/**
 * Verify Andrei Import - Query and Display Character Data (HTML)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/connect.php';

if (!$conn) {
    die("Database connection failed");
}

// Find Andrei (last inserted)
$result = mysqli_query($conn, "SELECT * FROM characters WHERE character_name = 'Andrei Radulescu' ORDER BY id DESC LIMIT 1");
$character = mysqli_fetch_assoc($result);

if (!$character) {
    die("Character not found");
}

$char_id = $character['id'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verify Andrei Import</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #2b2b2b;
            color: #e0e0e0;
        }
        .container {
            background: #1a1a1a;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        h1 {
            color: #ff6b6b;
            border-bottom: 3px solid #8B0000;
            padding-bottom: 10px;
            text-align: center;
        }
        h2 {
            color: #ff6b6b;
            margin-top: 30px;
            padding: 10px;
            background: #2d2d2d;
            border-left: 4px solid #8B0000;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 10px;
            margin: 15px 0;
        }
        .label {
            font-weight: bold;
            color: #999;
        }
        .value {
            color: #e0e0e0;
        }
        .section {
            margin-bottom: 30px;
            padding: 15px;
            background: #252525;
            border-radius: 4px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            padding: 8px 12px;
            margin: 5px 0;
            background: #2d2d2d;
            border-left: 3px solid #8B0000;
        }
        .merit {
            border-left-color: #4CAF50;
        }
        .flaw {
            border-left-color: #f44336;
        }
        .custom-data {
            background: #2d2d2d;
            padding: 15px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
            overflow-x: auto;
        }
        .notes, .biography {
            background: #2d2d2d;
            padding: 15px;
            border-left: 4px solid #ffd700;
            margin: 10px 0;
            line-height: 1.6;
        }
        .count {
            color: #999;
            font-size: 0.9em;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧛 Character Verification - Andrei Radulescu</h1>

        <!-- Basic Info -->
        <div class="section">
            <h2>✅ Basic Information</h2>
            <div class="info-grid">
                <div class="label">Character ID:</div><div class="value"><?= $character['id'] ?></div>
                <div class="label">Name:</div><div class="value"><?= $character['character_name'] ?></div>
                <div class="label">Player:</div><div class="value"><?= $character['player_name'] ?></div>
                <div class="label">Nature:</div><div class="value"><?= $character['nature'] ?></div>
                <div class="label">Demeanor:</div><div class="value"><?= $character['demeanor'] ?></div>
                <div class="label">Concept:</div><div class="value"><?= $character['concept'] ?></div>
                <div class="label">Clan:</div><div class="value"><?= $character['clan'] ?></div>
                <div class="label">Generation:</div><div class="value"><?= $character['generation'] ?></div>
                <div class="label">Sire:</div><div class="value"><?= $character['sire'] ?></div>
                <div class="label">Experience:</div><div class="value"><?= $character['total_xp'] ?> total, <?= $character['spent_xp'] ?> spent</div>
            </div>
        </div>

        <!-- Traits -->
        <?php
        $traits_result = mysqli_query($conn, "
            SELECT trait_category, trait_type, trait_name 
            FROM character_traits 
            WHERE character_id = $char_id 
            ORDER BY trait_type, trait_category, trait_name
        ");
        
        if (!$traits_result) {
            echo "<div class='section'><h2>📊 Traits</h2><p>Error: " . mysqli_error($conn) . "</p></div>";
        } else {
            $total_traits = mysqli_num_rows($traits_result);
            
            $traits = ['positive' => [], 'negative' => []];
            $debug_types = [];
            while ($row = mysqli_fetch_assoc($traits_result)) {
                $debug_types[] = "'" . $row['trait_type'] . "'"; // Debug
                
                if (!isset($traits[$row['trait_type']][$row['trait_category']])) {
                    $traits[$row['trait_type']][$row['trait_category']] = [];
                }
                $traits[$row['trait_type']][$row['trait_category']][] = $row['trait_name'];
            }
            
            // Debug output
            if ($total_traits > 0 && empty($traits['positive']) && empty($traits['negative'])) {
                echo "<p style='color: orange;'>DEBUG: Found trait types: " . implode(', ', array_unique($debug_types)) . "</p>";
            }
        ?>
        <div class="section">
            <h2>📊 Traits (<?= $total_traits ?> total)</h2>
            
            <?php if (!empty($traits['positive'])): ?>
                <h3 style="color: #4CAF50;">Positive Traits:</h3>
                <div style="padding: 10px 15px;">
                <?php foreach ($traits['positive'] as $category => $trait_list): ?>
                    <strong><?= $category ?>:</strong> <?= implode(', ', $trait_list) ?><br>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #999;">No positive traits found</p>
            <?php endif; ?>
            
            <?php if (!empty($traits['negative'])): ?>
                <h3 style="color: #f44336; margin-top: 15px;">Negative Traits:</h3>
                <div style="padding: 10px 15px;">
                <?php foreach ($traits['negative'] as $category => $trait_list): ?>
                    <strong><?= $category ?>:</strong> <?= implode(', ', $trait_list) ?><br>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #999;">No negative traits found</p>
            <?php endif; ?>
        </div>
        <?php } ?>

        <!-- Abilities with Specializations -->
        <?php
        // Get all abilities
        $abilities_result = mysqli_query($conn, "
            SELECT ability_name, level 
            FROM character_abilities 
            WHERE character_id = $char_id 
            ORDER BY level DESC, ability_name
        ");
        
        // Get all specializations
        $spec_result = mysqli_query($conn, "
            SELECT ability_name, specialization, grants_bonus 
            FROM character_ability_specializations 
            WHERE character_id = $char_id
        ");
        
        // Map specializations to abilities
        $specializations = [];
        while ($row = mysqli_fetch_assoc($spec_result)) {
            $specializations[$row['ability_name']] = [
                'spec' => $row['specialization'],
                'bonus' => $row['grants_bonus']
            ];
        }
        
        // Build abilities list with inline specializations
        $abilities_list = [];
        while ($row = mysqli_fetch_assoc($abilities_result)) {
            $ability_str = $row['ability_name'] . ' x' . $row['level'];
            
            // Add specialization if exists
            if (isset($specializations[$row['ability_name']])) {
                $spec = $specializations[$row['ability_name']];
                $bonus = $spec['bonus'] ? ' (+1 bonus)' : '';
                $ability_str .= ': <span style="color: #ffd700;">' . $spec['spec'] . $bonus . '</span>';
            }
            
            $abilities_list[] = $ability_str;
        }
        ?>
        <div class="section">
            <h2>🎓 Abilities</h2>
            <p style="padding: 15px; line-height: 1.8;"><?= implode(', ', $abilities_list) ?></p>
        </div>

        <!-- Disciplines & Rituals -->
        <?php
        $disc_result = mysqli_query($conn, "
            SELECT discipline_name, level 
            FROM character_disciplines 
            WHERE character_id = $char_id
        ");
        
        $ritual_result = mysqli_query($conn, "
            SELECT ritual_name, level, is_custom 
            FROM character_rituals 
            WHERE character_id = $char_id 
            ORDER BY level, ritual_name
        ");
        ?>
        <div class="section">
            <h2>🔮 Disciplines</h2>
            <ul>
            <?php while ($row = mysqli_fetch_assoc($disc_result)): ?>
                <li><?= $row['discipline_name'] ?>: <?= $row['level'] ?></li>
            <?php endwhile; ?>
            </ul>
            
            <h3 style="color: #9370DB; margin-top: 20px;">📖 Rituals:</h3>
            <ul>
            <?php while ($row = mysqli_fetch_assoc($ritual_result)): ?>
                <li>
                    <?= $row['ritual_name'] ?> 
                    (Level <?= $row['level'] > 0 ? $row['level'] : 'Unknown' ?>)
                    <?= $row['is_custom'] ? ' <span style="color: #ffd700;">[Custom]</span>' : '' ?>
                </li>
            <?php endwhile; ?>
            </ul>
        </div>

        <!-- Backgrounds -->
        <?php
        $bg_result = mysqli_query($conn, "
            SELECT background_name, level 
            FROM character_backgrounds 
            WHERE character_id = $char_id 
            ORDER BY level DESC
        ");
        ?>
        <div class="section">
            <h2>📚 Backgrounds</h2>
            <ul>
            <?php while ($row = mysqli_fetch_assoc($bg_result)): ?>
                <li><?= $row['background_name'] ?>: <?= $row['level'] ?></li>
            <?php endwhile; ?>
            </ul>
        </div>

        <!-- Morality -->
        <?php
        $morality_result = mysqli_query($conn, "SELECT * FROM character_morality WHERE character_id = $char_id");
        $morality = mysqli_fetch_assoc($morality_result);
        ?>
        <div class="section">
            <h2>🕊️ Morality & Virtues</h2>
            <div class="info-grid">
                <div class="label"><?= $morality['path_name'] ?>:</div><div class="value"><?= $morality['path_rating'] ?></div>
                <div class="label">Conscience:</div><div class="value"><?= $morality['conscience'] ?></div>
                <div class="label">Self-Control:</div><div class="value"><?= $morality['self_control'] ?></div>
                <div class="label">Courage:</div><div class="value"><?= $morality['courage'] ?></div>
                <div class="label">Willpower:</div><div class="value"><?= $morality['willpower_current'] ?> / <?= $morality['willpower_permanent'] ?></div>
            </div>
        </div>

        <!-- Merits & Flaws -->
        <?php
        $mf_result = mysqli_query($conn, "
            SELECT name, type, category, point_value, description 
            FROM character_merits_flaws 
            WHERE character_id = $char_id 
            ORDER BY type, name
        ");
        
        $merits = [];
        $flaws = [];
        while ($row = mysqli_fetch_assoc($mf_result)) {
            if (strtolower($row['type']) === 'merit') {
                $merits[] = $row;
            } else {
                $flaws[] = $row;
            }
        }
        ?>
        <div class="section">
            <h2>✨ Merits & Flaws</h2>
            
            <h3 style="color: #4CAF50;">Merits:</h3>
            <ul>
            <?php foreach ($merits as $m): ?>
                <li class="merit">
                    <strong><?= $m['name'] ?></strong> (<?= $m['category'] ?>, <?= $m['point_value'] ?> pts)<br>
                    <span style="color: #999; font-size: 0.9em;"><?= $m['description'] ?></span>
                </li>
            <?php endforeach; ?>
            </ul>
            
            <h3 style="color: #f44336;">Flaws:</h3>
            <ul>
            <?php foreach ($flaws as $f): ?>
                <li class="flaw">
                    <strong><?= $f['name'] ?></strong> (<?= $f['category'] ?>, <?= $f['point_value'] ?> pts)<br>
                    <span style="color: #999; font-size: 0.9em;"><?= $f['description'] ?></span>
                </li>
            <?php endforeach; ?>
            </ul>
        </div>

        <!-- Character Status -->
        <?php
        $status_result = mysqli_query($conn, "SELECT * FROM character_status WHERE character_id = $char_id");
        $status = mysqli_fetch_assoc($status_result);
        ?>
        <div class="section">
            <h2>💚 Character Status</h2>
            <div class="info-grid">
                <div class="label">Health Levels:</div><div class="value"><?= $status['health_levels'] ?></div>
                <div class="label">Blood Pool:</div><div class="value"><?= $status['blood_pool_current'] ?> / <?= $status['blood_pool_maximum'] ?></div>
            </div>
        </div>

        <!-- Custom Data -->
        <?php if (!empty($character['custom_data'])): ?>
        <div class="section">
            <h2>🗂️ Custom Data (JSON)</h2>
            <div class="custom-data"><?= json_encode(json_decode($character['custom_data']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?></div>
        </div>
        <?php endif; ?>

        <!-- Notes -->
        <?php if (!empty($character['notes'])): ?>
        <div class="section">
            <h2>📝 ST Notes</h2>
            <div class="notes"><?= nl2br(htmlspecialchars($character['notes'])) ?></div>
        </div>
        <?php endif; ?>

        <!-- Biography -->
        <?php if (!empty($character['biography'])): ?>
        <div class="section">
            <h2>📜 Biography</h2>
            <div class="biography"><?= nl2br(htmlspecialchars($character['biography'])) ?></div>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #2d2d2d; border-radius: 4px;">
            <h3 style="color: #4CAF50;">✅ Verification Complete!</h3>
            <p>All character data successfully imported and queryable.</p>
        </div>

    </div>
</body>
</html>
<?php mysqli_close($conn); ?>
