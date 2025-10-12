<?php
// Don't start session here since API will start it
// Simulate a logged-in user for testing
$_SESSION['user_id'] = 1;

echo "<h1>API Test - Get Characters</h1>";

// Include the API
ob_start();
include 'api_get_characters.php';
$api_output = ob_get_clean();

echo "<h2>API Response:</h2>";
echo "<pre>" . htmlspecialchars($api_output) . "</pre>";

// Try to decode JSON
$json_data = json_decode($api_output, true);
if ($json_data) {
    echo "<h2>Decoded JSON:</h2>";
    echo "<pre>" . print_r($json_data, true) . "</pre>";
} else {
    echo "<h2>JSON Decode Error:</h2>";
    echo "<p>Could not decode JSON response</p>";
}
?>
