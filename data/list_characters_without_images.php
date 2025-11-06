<?php
/**
 * List all characters that don't have a character_image
 * Usage: https://vbn.talkingheads.video/data/list_characters_without_images.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
$connect_file = '/usr/home/working/public_html/vbn.talkingheads.video/includes/connect.php';
if (!file_exists($connect_file)) {
    die("❌ Connection file not found: $connect_file\n");
}
require_once $connect_file;

// Check if connection exists
if (!isset($conn) || !$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error() . "\n");
}

// Query for characters without images (NULL or empty string)
$query = "SELECT character_name 
          FROM characters 
          WHERE character_image IS NULL OR character_image = '' OR character_image = 'null'
          ORDER BY character_name ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("❌ Query failed: " . mysqli_error($conn) . "\n");
}

$names = [];
while ($row = mysqli_fetch_assoc($result)) {
    $names[] = $row['character_name'] . ',';
}

echo implode(' ', $names) . "\n";

mysqli_close($conn);
?>

