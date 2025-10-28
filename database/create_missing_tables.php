<?php
// Create the missing character_disciplines table
session_start();
$_SESSION['user_id'] = 1;

echo "<h1>Creating Missing Tables</h1>";

include 'includes/connect.php';

if (!$conn) {
    echo "❌ Database connection failed<br>";
    exit;
}

echo "✅ Database connected<br>";

// Create character_disciplines table
$sql = "CREATE TABLE IF NOT EXISTS character_disciplines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    character_id INT NOT NULL,
    discipline_name VARCHAR(100) NOT NULL,
    level INT NOT NULL DEFAULT 1,
    xp_cost INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (character_id) REFERENCES characters(id) ON DELETE CASCADE,
    INDEX idx_character (character_id),
    INDEX idx_discipline (discipline_name),
    INDEX idx_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($conn, $sql)) {
    echo "✅ character_disciplines table created successfully<br>";
} else {
    echo "❌ Error creating character_disciplines table: " . mysqli_error($conn) . "<br>";
}

// Verify the table was created
$result = mysqli_query($conn, "SHOW TABLES LIKE 'character_disciplines'");
if ($result && mysqli_num_rows($result) > 0) {
    echo "✅ character_disciplines table now exists<br>";
    
    // Show table structure
    $desc_result = mysqli_query($conn, "DESCRIBE character_disciplines");
    if ($desc_result) {
        echo "Table structure:<br>";
        echo "<ul>";
        while ($row = mysqli_fetch_assoc($desc_result)) {
            echo "<li>" . $row['Field'] . " - " . $row['Type'] . "</li>";
        }
        echo "</ul>";
    }
} else {
    echo "❌ character_disciplines table still missing<br>";
}

echo "<h2>Testing save script now...</h2>";
echo "<p>Try the save button test again: <a href='test_save_button.html'>test_save_button.html</a></p>";
?>
