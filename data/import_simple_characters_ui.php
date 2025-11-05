<?php
/**
 * UI for importing simple character format JSON files
 * Displays links to import individual characters or run batch import
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Character Import</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #1a0f0f;
            color: #d4c4b0;
            padding: 20px;
            max-width: 900px;
            margin: 0 auto;
        }
        h1 {
            color: #8b0000;
            border-bottom: 2px solid #8b0000;
            padding-bottom: 10px;
        }
        h2 {
            color: #d4c4b0;
            margin-top: 30px;
        }
        .character-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .character-card {
            background: #2a1f1f;
            border: 1px solid #4a3f3f;
            border-radius: 5px;
            padding: 15px;
        }
        .character-card h3 {
            margin: 0 0 10px 0;
            color: #d4c4b0;
        }
        .character-card p {
            margin: 5px 0;
            font-size: 14px;
        }
        a.button {
            display: inline-block;
            background: #8b0000;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px 5px 5px 0;
            transition: background 0.3s;
        }
        a.button:hover {
            background: #a00000;
        }
        a.button.success {
            background: #006600;
        }
        a.button.success:hover {
            background: #008000;
        }
        .batch-section {
            background: #2a1f1f;
            border: 1px solid #4a3f3f;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .info {
            background: #3a2f2f;
            border-left: 4px solid #ffa500;
            padding: 10px 15px;
            margin: 15px 0;
        }
        .clan-badge {
            display: inline-block;
            background: #8b0000;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>🦇 Simple Character Import System</h1>
    
    <div class="info">
        <strong>Format:</strong> This import system handles characters with disciplines as simple key-value pairs (e.g., {"potence": 3, "celerity": 2}).
        <br><strong>Location:</strong> Files are read from <code>reference/Characters/Added to Database/</code>
    </div>

    <div class="batch-section">
        <h2>⚡ Batch Import</h2>
        <p>Import all three characters at once:</p>
        <a href="batch_import_simple_characters.php" class="button success">Import All (Pistol Pete, Sasha, Leo)</a>
    </div>

    <h2>📋 Individual Imports</h2>
    <div class="character-list">
        <div class="character-card">
            <h3>Pistol Pete</h3>
            <p><span class="clan-badge">Brujah</span></p>
            <p><strong>Disciplines:</strong> Potence (3), Celerity (2), Presence (1)</p>
            <a href="import_simple_character.php?file=Pistol%20Pete.json" class="button">Import</a>
        </div>

        <div class="character-card">
            <h3>Sasha</h3>
            <p><span class="clan-badge">Malkavian</span></p>
            <p><strong>Disciplines:</strong> Auspex (3), Dementation (3), Dominate (1)</p>
            <a href="import_simple_character.php?file=Sasha.json" class="button">Import</a>
        </div>

        <div class="character-card">
            <h3>Leo</h3>
            <p><span class="clan-badge">Nosferatu</span></p>
            <p><strong>Disciplines:</strong> Obfuscate (3), Potence (1), Animalism (1)</p>
            <a href="import_simple_character.php?file=Leo.json" class="button">Import</a>
        </div>
    </div>

    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #4a3f3f;">
        <p><a href="../admin/admin_panel.php" style="color: #d4c4b0;">← Back to Admin Panel</a></p>
    </div>
</body>
</html>

