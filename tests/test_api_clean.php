<!DOCTYPE html>
<html>
<head>
    <title>API Test</title>
</head>
<body>
<?php
echo "<h1>API Test - Get Characters</h1>";

// Start session and set user ID
session_start();
$_SESSION['user_id'] = 1;

echo "<h2>Session Info:</h2>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "<br>";

// Test the API by making a direct request
echo "<h2>Testing API Directly:</h2>";

// Capture any output
ob_start();

// Include the API file
include 'api_get_characters.php';

// Get the output
$api_output = ob_get_clean();

echo "<h3>Raw API Response:</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($api_output) . "</pre>";

// Try to decode JSON
$json_data = json_decode($api_output, true);

if ($json_data !== null) {
    echo "<h3>✅ JSON Decoded Successfully:</h3>";
    echo "<pre style='background: #e8f5e8; padding: 10px; border-radius: 5px;'>" . print_r($json_data, true) . "</pre>";
    
    if ($json_data['success']) {
        echo "<h3>✅ API Working - Found " . $json_data['count'] . " characters</h3>";
    } else {
        echo "<h3>⚠️ API Error: " . $json_data['message'] . "</h3>";
    }
} else {
    echo "<h3>❌ JSON Decode Failed</h3>";
    echo "<p>Raw response: " . htmlspecialchars($api_output) . "</p>";
    echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
}

// Test database connection directly
echo "<h2>Database Connection Test:</h2>";
include 'includes/connect.php';

if ($conn) {
    echo "✅ Database connected<br>";
    
    // Check if we have any characters
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM characters WHERE user_id = 1");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        echo "✅ Found " . $row['count'] . " characters for user ID 1<br>";
    } else {
        echo "❌ Error querying characters: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "❌ Database connection failed<br>";
}
?>
</body>
</html>
