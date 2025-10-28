<?php
/**
 * Update Database Schema for MySQL Best Practices Compliance
 * 
 * This script adds:
 * - Missing indexes for frequently queried columns
 * - utf8mb4_unicode_ci collation where missing
 * - Optimized foreign key constraints
 * 
 * Run this once to update existing tables
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../includes/connect.php';

if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error());
}

echo "<!DOCTYPE html>\n<html>\n<head>\n<title>MySQL Compliance Schema Update</title>\n";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#0f0;}\n";
echo "h1{color:#0f0;border-bottom:2px solid #0f0;}\n";
echo ".success{color:#0f0;}\n.error{color:#f00;}\n.warning{color:#ff0;}\n";
echo ".section{margin:20px 0;padding:10px;border-left:3px solid #0f0;}\n</style>\n</head>\n<body>\n";

echo "<h1>🛠️ MySQL Compliance Schema Update</h1>\n";
echo "<p>Updating database schema to follow MySQL best practices...</p>\n\n";

$updates_applied = 0;
$errors = 0;

/**
 * Helper function to execute SQL with error handling
 */
function execute_update($conn, $sql, $description) {
    global $updates_applied, $errors;
    
    echo "<div class='section'>";
    echo "<strong>$description</strong><br>";
    
    if (mysqli_query($conn, $sql)) {
        echo "<span class='success'>✅ Success</span><br>";
        $updates_applied++;
    } else {
        $error = mysqli_error($conn);
        // Ignore "Duplicate key" errors (index already exists)
        if (strpos($error, 'Duplicate') !== false || strpos($error, 'already exists') !== false) {
            echo "<span class='warning'>⚠️ Already exists (skipped)</span><br>";
        } else {
            echo "<span class='error'>❌ Error: $error</span><br>";
            $errors++;
        }
    }
    echo "</div>\n";
}

echo "<h2>Phase 1: Convert Tables to utf8mb4_unicode_ci</h2>\n";

// Get all tables
$tables_result = mysqli_query($conn, "SHOW TABLES");
$tables = [];
while ($row = mysqli_fetch_array($tables_result)) {
    $tables[] = $row[0];
}

foreach ($tables as $table) {
    execute_update($conn, 
        "ALTER TABLE `$table` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci",
        "Converting $table to utf8mb4_unicode_ci"
    );
}

echo "<h2>Phase 2: Add Missing Indexes - Users Table</h2>\n";

execute_update($conn,
    "CREATE INDEX idx_users_email ON users(email)",
    "Add index on users.email"
);

execute_update($conn,
    "CREATE INDEX idx_users_username ON users(username)",
    "Add index on users.username"
);

execute_update($conn,
    "CREATE INDEX idx_users_role ON users(role)",
    "Add index on users.role"
);

execute_update($conn,
    "CREATE INDEX idx_users_last_login ON users(last_login)",
    "Add index on users.last_login"
);

echo "<h2>Phase 3: Add Missing Indexes - Characters Table</h2>\n";

execute_update($conn,
    "CREATE INDEX idx_characters_user ON characters(user_id)",
    "Add index on characters.user_id"
);

execute_update($conn,
    "CREATE INDEX idx_characters_clan ON characters(clan)",
    "Add index on characters.clan (for filtering)"
);

execute_update($conn,
    "CREATE INDEX idx_characters_pc ON characters(pc)",
    "Add index on characters.pc (for PC/NPC filtering)"
);

execute_update($conn,
    "CREATE INDEX idx_characters_name ON characters(character_name)",
    "Add index on characters.character_name (for searching)"
);

execute_update($conn,
    "CREATE INDEX idx_characters_created ON characters(created_at)",
    "Add index on characters.created_at (for sorting)"
);

echo "<h2>Phase 4: Add Missing Indexes - Character Traits Table</h2>\n";

execute_update($conn,
    "CREATE INDEX idx_traits_character ON character_traits(character_id)",
    "Add index on character_traits.character_id"
);

execute_update($conn,
    "CREATE INDEX idx_traits_category ON character_traits(trait_category)",
    "Add index on character_traits.trait_category"
);

execute_update($conn,
    "CREATE INDEX idx_traits_type ON character_traits(trait_type)",
    "Add index on character_traits.trait_type"
);

execute_update($conn,
    "CREATE INDEX idx_traits_name ON character_traits(trait_name)",
    "Add index on character_traits.trait_name"
);

echo "<h2>Phase 5: Add Missing Indexes - Character Abilities Table</h2>\n";

execute_update($conn,
    "CREATE INDEX idx_abilities_character ON character_abilities(character_id)",
    "Add index on character_abilities.character_id"
);

execute_update($conn,
    "CREATE INDEX idx_abilities_name ON character_abilities(ability_name)",
    "Add index on character_abilities.ability_name"
);

execute_update($conn,
    "CREATE INDEX idx_abilities_level ON character_abilities(level)",
    "Add index on character_abilities.level"
);

echo "<h2>Phase 6: Add Missing Indexes - Character Disciplines Table</h2>\n";

execute_update($conn,
    "CREATE INDEX idx_disciplines_character ON character_disciplines(character_id)",
    "Add index on character_disciplines.character_id"
);

execute_update($conn,
    "CREATE INDEX idx_disciplines_name ON character_disciplines(discipline_name)",
    "Add index on character_disciplines.discipline_name"
);

execute_update($conn,
    "CREATE INDEX idx_disciplines_level ON character_disciplines(level)",
    "Add index on character_disciplines.level"
);

echo "<h2>Phase 7: Add Missing Indexes - Character Backgrounds Table</h2>\n";

execute_update($conn,
    "CREATE INDEX idx_backgrounds_character ON character_backgrounds(character_id)",
    "Add index on character_backgrounds.character_id"
);

execute_update($conn,
    "CREATE INDEX idx_backgrounds_name ON character_backgrounds(background_name)",
    "Add index on character_backgrounds.background_name"
);

echo "<h2>Phase 8: Add Missing Indexes - Character Merits/Flaws Table</h2>\n";

execute_update($conn,
    "CREATE INDEX idx_merits_character ON character_merits_flaws(character_id)",
    "Add index on character_merits_flaws.character_id"
);

execute_update($conn,
    "CREATE INDEX idx_merits_type ON character_merits_flaws(type)",
    "Add index on character_merits_flaws.type"
);

echo "<h2>Phase 9: Add Missing Indexes - Character Morality Table</h2>\n";

execute_update($conn,
    "CREATE INDEX idx_morality_character ON character_morality(character_id)",
    "Add index on character_morality.character_id"
);

execute_update($conn,
    "CREATE INDEX idx_morality_path ON character_morality(path_name)",
    "Add index on character_morality.path_name"
);

echo "<h2>Phase 10: Add Missing Indexes - Character Derangements Table</h2>\n";

execute_update($conn,
    "CREATE INDEX idx_derangements_character ON character_derangements(character_id)",
    "Add index on character_derangements.character_id"
);

execute_update($conn,
    "CREATE INDEX idx_derangements_severity ON character_derangements(severity)",
    "Add index on character_derangements.severity"
);

echo "<h2>Phase 11: Add Missing Indexes - Character Influences Table</h2>\n";

execute_update($conn,
    "CREATE INDEX idx_influences_character ON character_influences(character_id)",
    "Add index on character_influences.character_id"
);

execute_update($conn,
    "CREATE INDEX idx_influences_type ON character_influences(influence_type)",
    "Add index on character_influences.influence_type"
);

echo "<h2>Phase 12: Add Missing Indexes - Character Rituals Table</h2>\n";

execute_update($conn,
    "CREATE INDEX idx_rituals_character ON character_rituals(character_id)",
    "Add index on character_rituals.character_id"
);

execute_update($conn,
    "CREATE INDEX idx_rituals_type ON character_rituals(ritual_type)",
    "Add index on character_rituals.ritual_type"
);

echo "<h2>Phase 13: Add Missing Indexes - Character Status Table</h2>\n";

execute_update($conn,
    "CREATE INDEX idx_status_character ON character_status(character_id)",
    "Add index on character_status.character_id"
);

echo "<h2>Phase 14: Update Locations Table - Add Collation</h2>\n";

execute_update($conn,
    "ALTER TABLE locations COLLATE utf8mb4_unicode_ci",
    "Set locations table collation"
);

echo "<h2>Phase 15: Update Items Tables - Add Missing Indexes</h2>\n";

execute_update($conn,
    "CREATE INDEX idx_items_name ON items(name)",
    "Add index on items.name (for searching)"
);

execute_update($conn,
    "ALTER TABLE items COLLATE utf8mb4_unicode_ci",
    "Set items table collation"
);

execute_update($conn,
    "ALTER TABLE character_equipment COLLATE utf8mb4_unicode_ci",
    "Set character_equipment table collation"
);

echo "<h2>Phase 16: Update NPC Tracker - Fix Foreign Key</h2>\n";

// Drop incorrect foreign key if it exists
$fk_check = mysqli_query($conn, "
    SELECT CONSTRAINT_NAME 
    FROM information_schema.KEY_COLUMN_USAGE 
    WHERE TABLE_NAME = 'npc_tracker' 
    AND CONSTRAINT_NAME LIKE '%submitted_by%'
");

if ($fk_check && mysqli_num_rows($fk_check) > 0) {
    $fk_row = mysqli_fetch_assoc($fk_check);
    execute_update($conn,
        "ALTER TABLE npc_tracker DROP FOREIGN KEY " . $fk_row['CONSTRAINT_NAME'],
        "Drop incorrect npc_tracker foreign key"
    );
}

execute_update($conn,
    "ALTER TABLE npc_tracker ADD CONSTRAINT fk_npc_submitted_by 
     FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL",
    "Add correct npc_tracker foreign key to users.id"
);

execute_update($conn,
    "CREATE INDEX idx_npc_tracker_character ON npc_tracker(character_name)",
    "Add index on npc_tracker.character_name"
);

execute_update($conn,
    "CREATE INDEX idx_npc_tracker_clan ON npc_tracker(clan)",
    "Add index on npc_tracker.clan"
);

execute_update($conn,
    "CREATE INDEX idx_npc_tracker_status ON npc_tracker(status)",
    "Add index on npc_tracker.status"
);

execute_update($conn,
    "CREATE INDEX idx_npc_tracker_linked ON npc_tracker(linked_to)",
    "Add index on npc_tracker.linked_to"
);

echo "<h2>Phase 17: Character Disciplines - Update Schema</h2>\n";

execute_update($conn,
    "ALTER TABLE character_disciplines COLLATE utf8mb4_unicode_ci",
    "Set character_disciplines table collation"
);

echo "<h2>📊 Update Summary</h2>\n";
echo "<div class='section'>";
echo "<p><strong>Total Updates Applied:</strong> <span class='success'>$updates_applied</span></p>";
echo "<p><strong>Errors Encountered:</strong> " . ($errors > 0 ? "<span class='error'>$errors</span>" : "<span class='success'>0</span>") . "</p>";
echo "</div>";

if ($errors === 0) {
    echo "<h2 class='success'>✅ All Updates Completed Successfully!</h2>\n";
    echo "<p>Your database now follows MySQL best practices:</p>";
    echo "<ul>";
    echo "<li>✅ All tables use utf8mb4_unicode_ci collation</li>";
    echo "<li>✅ Indexes added for frequently queried columns</li>";
    echo "<li>✅ Foreign keys properly reference correct columns</li>";
    echo "<li>✅ Query performance optimized</li>";
    echo "</ul>";
} else {
    echo "<h2 class='warning'>⚠️ Updates Completed with Errors</h2>\n";
    echo "<p>Please review the errors above and address them manually if needed.</p>";
}

echo "<h2>🔍 Next Steps</h2>\n";
echo "<ol>";
echo "<li>Run EXPLAIN on your most frequent queries to verify index usage</li>";
echo "<li>Update application code to use prepared statements</li>";
echo "<li>Test all functionality to ensure compatibility</li>";
echo "<li>Monitor query performance</li>";
echo "</ol>";

echo "<p><a href='../dashboard.php'>← Back to Dashboard</a></p>";

mysqli_close($conn);

echo "</body></html>";
?>

