<?php
/**
 * Add Character Description Fields Migration
 * Adds appearance, biography (if missing/needs update), and notes columns to characters table
 * Following MySQL best practices with utf8mb4_unicode_ci collation
 * 
 * Usage: Access via web browser: https://vbn.talkingheads.video/database/add_character_description_fields.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Character Description Fields - Migration</title>
    <style>
        body {
            font-family: 'Source Serif Pro', serif;
            background: #1a0f0f;
            color: #f5e6d3;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 { color: #8B0000; }
        h2 { color: #c9a96e; margin-top: 30px; }
        .success { color: #1a6b3a; background: rgba(26, 107, 58, 0.2); padding: 10px; border-left: 3px solid #1a6b3a; margin: 10px 0; }
        .error { color: #8B0000; background: rgba(139, 0, 0, 0.2); padding: 10px; border-left: 3px solid #8B0000; margin: 10px 0; }
        .info { color: #b8a090; background: rgba(184, 160, 144, 0.2); padding: 10px; border-left: 3px solid #b8a090; margin: 10px 0; }
        pre { background: #2a1515; padding: 15px; border: 1px solid #8B0000; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #444; }
        th { background: #2a1515; color: #c9a96e; }
    </style>
</head>
<body>
    <h1>🦇 Add Character Description Fields Migration</h1>
    <p>This script adds <strong>appearance</strong>, <strong>biography</strong> (if needed), and <strong>notes</strong> columns to the characters table.</p>
    
<?php

// Check if characters table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'characters'");
if (mysqli_num_rows($table_check) == 0) {
    echo "<div class='error'>ERROR: Characters table does not exist!</div>";
    exit;
}

echo "<h2>Step 1: Current Schema Analysis</h2>\n";
echo "<div class='info'>Analyzing current characters table structure...</div>\n";

// Get current columns
$columns_result = mysqli_query($conn, "SHOW COLUMNS FROM characters");
$existing_columns = [];
while ($row = mysqli_fetch_assoc($columns_result)) {
    $existing_columns[$row['Field']] = $row;
}

echo "<table>\n";
echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Collation</th><th>Status</th></tr>\n";

$required_columns = [
    'appearance' => ['comment' => 'Physical appearance description', 'after' => 'biography'],
    'biography' => ['comment' => 'Character background story and history', 'after' => null],
    'notes' => ['comment' => 'Additional character notes', 'after' => 'appearance']
];

$columns_to_add = [];
$columns_to_update = [];

foreach ($required_columns as $col_name => $col_info) {
    $exists = isset($existing_columns[$col_name]);
    $collation = $exists ? ($existing_columns[$col_name]['Collation'] ?? 'N/A') : 'N/A';
    $type = $exists ? $existing_columns[$col_name]['Type'] : 'N/A';
    $null = $exists ? $existing_columns[$col_name]['Null'] : 'N/A';
    
    $status = $exists ? '✓ EXISTS' : '✗ MISSING';
    $status_class = $exists ? 'success' : 'error';
    
    echo "<tr>";
    echo "<td><strong>$col_name</strong></td>";
    echo "<td>$type</td>";
    echo "<td>$null</td>";
    echo "<td>$collation</td>";
    echo "<td class='$status_class'>$status</td>";
    echo "</tr>\n";
    
    if (!$exists) {
        $columns_to_add[] = $col_name;
    } elseif ($collation !== 'utf8mb4_unicode_ci' && $col_name === 'biography') {
        $columns_to_update[] = $col_name;
    }
}

echo "</table>\n";

// Start transaction
mysqli_begin_transaction($conn);

$errors = [];
$successes = [];

echo "<h2>Step 2: Migration Execution</h2>\n";

// Add appearance column if missing
if (in_array('appearance', $columns_to_add)) {
    $sql = "ALTER TABLE characters ADD COLUMN appearance TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT 'Physical appearance description of the character'";
    
    // Try to add after biography if it exists
    if (isset($existing_columns['biography'])) {
        $sql .= " AFTER biography";
    }
    
    if (mysqli_query($conn, $sql)) {
        $successes[] = "Added 'appearance' column";
        echo "<div class='success'>✓ Added 'appearance' column</div>\n";
    } else {
        $error = mysqli_error($conn);
        $errors[] = "Failed to add 'appearance': $error";
        echo "<div class='error'>✗ Failed to add 'appearance': $error</div>\n";
    }
} else {
    echo "<div class='info'>ℹ Column 'appearance' already exists</div>\n";
}

// Add notes column if missing
if (in_array('notes', $columns_to_add)) {
    $sql = "ALTER TABLE characters ADD COLUMN notes TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT 'Additional character notes, player notes, or storyteller notes'";
    
    // Try to add after appearance if it exists (or after biography)
    if (isset($existing_columns['appearance'])) {
        $sql .= " AFTER appearance";
    } elseif (isset($existing_columns['biography'])) {
        $sql .= " AFTER biography";
    }
    
    if (mysqli_query($conn, $sql)) {
        $successes[] = "Added 'notes' column";
        echo "<div class='success'>✓ Added 'notes' column</div>\n";
    } else {
        $error = mysqli_error($conn);
        $errors[] = "Failed to add 'notes': $error";
        echo "<div class='error'>✗ Failed to add 'notes': $error</div>\n";
    }
} else {
    echo "<div class='info'>ℹ Column 'notes' already exists</div>\n";
}

// Update biography column collation if needed
if (isset($existing_columns['biography'])) {
    $bio_collation = $existing_columns['biography']['Collation'] ?? '';
    if ($bio_collation !== 'utf8mb4_unicode_ci') {
        $sql = "ALTER TABLE characters MODIFY COLUMN biography TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT 'Character background story and history'";
        
        if (mysqli_query($conn, $sql)) {
            $successes[] = "Updated 'biography' column collation to utf8mb4_unicode_ci";
            echo "<div class='success'>✓ Updated 'biography' column collation</div>\n";
        } else {
            $error = mysqli_error($conn);
            $errors[] = "Failed to update 'biography': $error";
            echo "<div class='error'>✗ Failed to update 'biography': $error</div>\n";
        }
    } else {
        echo "<div class='info'>ℹ Column 'biography' already has correct collation</div>\n";
    }
}

// Update appearance column collation if needed
if (isset($existing_columns['appearance'])) {
    $app_collation = $existing_columns['appearance']['Collation'] ?? '';
    if ($app_collation !== 'utf8mb4_unicode_ci') {
        $sql = "ALTER TABLE characters MODIFY COLUMN appearance TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT 'Physical appearance description of the character'";
        
        if (mysqli_query($conn, $sql)) {
            $successes[] = "Updated 'appearance' column collation to utf8mb4_unicode_ci";
            echo "<div class='success'>✓ Updated 'appearance' column collation</div>\n";
        } else {
            $error = mysqli_error($conn);
            $errors[] = "Failed to update 'appearance': $error";
            echo "<div class='error'>✗ Failed to update 'appearance': $error</div>\n";
        }
    } else {
        echo "<div class='info'>ℹ Column 'appearance' already has correct collation</div>\n";
    }
}

// Update notes column collation if needed
if (isset($existing_columns['notes'])) {
    $notes_collation = $existing_columns['notes']['Collation'] ?? '';
    if ($notes_collation !== 'utf8mb4_unicode_ci') {
        $sql = "ALTER TABLE characters MODIFY COLUMN notes TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT 'Additional character notes, player notes, or storyteller notes'";
        
        if (mysqli_query($conn, $sql)) {
            $successes[] = "Updated 'notes' column collation to utf8mb4_unicode_ci";
            echo "<div class='success'>✓ Updated 'notes' column collation</div>\n";
        } else {
            $error = mysqli_error($conn);
            $errors[] = "Failed to update 'notes': $error";
            echo "<div class='error'>✗ Failed to update 'notes': $error</div>\n";
        }
    } else {
        echo "<div class='info'>ℹ Column 'notes' already has correct collation</div>\n";
    }
}

// Commit or rollback
if (empty($errors)) {
    mysqli_commit($conn);
    echo "<div class='success'><strong>✓ Migration completed successfully!</strong></div>\n";
} else {
    mysqli_rollback($conn);
    echo "<div class='error'><strong>✗ Migration failed. Transaction rolled back.</strong></div>\n";
    foreach ($errors as $error) {
        echo "<div class='error'>$error</div>\n";
    }
}

echo "<h2>Step 3: Verification</h2>\n";

// Verify final state
$verify_result = mysqli_query($conn, "
    SELECT 
        COLUMN_NAME,
        DATA_TYPE,
        CHARACTER_SET_NAME,
        COLLATION_NAME,
        IS_NULLABLE,
        COLUMN_COMMENT
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'characters'
    AND COLUMN_NAME IN ('appearance', 'biography', 'notes')
    ORDER BY ORDINAL_POSITION
");

if ($verify_result && mysqli_num_rows($verify_result) > 0) {
    echo "<table>\n";
    echo "<tr><th>Column</th><th>Type</th><th>Character Set</th><th>Collation</th><th>Nullable</th><th>Comment</th></tr>\n";
    
    while ($row = mysqli_fetch_assoc($verify_result)) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($row['COLUMN_NAME']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['DATA_TYPE']) . "</td>";
        echo "<td>" . htmlspecialchars($row['CHARACTER_SET_NAME'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['COLLATION_NAME'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['IS_NULLABLE']) . "</td>";
        echo "<td>" . htmlspecialchars($row['COLUMN_COMMENT'] ?? '') . "</td>";
        echo "</tr>\n";
    }
    
    echo "</table>\n";
    
    // Check if all columns have correct collation
    $all_correct = true;
    mysqli_data_seek($verify_result, 0);
    while ($row = mysqli_fetch_assoc($verify_result)) {
        if ($row['COLLATION_NAME'] !== 'utf8mb4_unicode_ci') {
            $all_correct = false;
            break;
        }
    }
    
    if ($all_correct) {
        echo "<div class='success'><strong>✓ All columns have correct utf8mb4_unicode_ci collation!</strong></div>\n";
    }
} else {
    echo "<div class='error'>Could not verify columns. Some columns may be missing.</div>\n";
}

mysqli_close($conn);
?>

    <hr style="margin: 30px 0; border-color: #8B0000;">
    <p><strong>Migration complete!</strong> You can now proceed with implementing the Description tab in the character creation form.</p>
    <p><a href="../admin/admin_panel.php" style="color: #c9a96e;">← Back to Admin Panel</a></p>
</body>
</html>

