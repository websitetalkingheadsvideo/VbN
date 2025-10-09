<?php
// Test if all required tables exist
session_start();
$_SESSION['user_id'] = 1;

echo "<h1>Database Tables Check</h1>";

include 'includes/connect.php';

if (!$conn) {
    echo "❌ Database connection failed<br>";
    exit;
}

echo "✅ Database connected<br>";

// List of tables that save_character.php tries to insert into
$required_tables = [
    'characters',
    'character_traits', 
    'character_abilities',
    'character_disciplines',
    'character_backgrounds',
    'character_merits_flaws',
    'character_morality',
    'character_status'
];

echo "<h2>Checking required tables:</h2>";

foreach ($required_tables as $table) {
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if ($result && mysqli_num_rows($result) > 0) {
        echo "✅ $table exists<br>";
        
        // Check table structure
        $desc_result = mysqli_query($conn, "DESCRIBE $table");
        if ($desc_result) {
            echo "&nbsp;&nbsp;&nbsp;Columns: ";
            $columns = [];
            while ($row = mysqli_fetch_assoc($desc_result)) {
                $columns[] = $row['Field'];
            }
            echo implode(', ', $columns) . "<br>";
        }
    } else {
        echo "❌ $table MISSING<br>";
    }
}

echo "<h2>All tables in database:</h2>";
$result = mysqli_query($conn, "SHOW TABLES");
if ($result) {
    $tables = [];
    while ($row = mysqli_fetch_array($result)) {
        $tables[] = $row[0];
    }
    echo "Found " . count($tables) . " tables: " . implode(', ', $tables) . "<br>";
} else {
    echo "❌ Error listing tables: " . mysqli_error($conn) . "<br>";
}
?>
