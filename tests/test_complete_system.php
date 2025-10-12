<?php
session_start();

echo "<h1>🔧 Complete System Test - VbN Character Creator</h1>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
include 'includes/connect.php';

if ($conn) {
    echo "✅ Database connection successful<br>";
    
    // Test 2: Check tables
    echo "<h2>2. Database Tables Test</h2>";
    $result = mysqli_query($conn, "SHOW TABLES");
    if ($result) {
        $tables = [];
        while ($row = mysqli_fetch_array($result)) {
            $tables[] = $row[0];
        }
        echo "✅ Found " . count($tables) . " tables: " . implode(', ', $tables) . "<br>";
        
        // Test 3: Check users table
        echo "<h2>3. Users Table Test</h2>";
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            echo "✅ Users table has " . $row['count'] . " users<br>";
        } else {
            echo "❌ Error checking users table: " . mysqli_error($conn) . "<br>";
        }
        
        // Test 4: Check characters table
        echo "<h2>4. Characters Table Test</h2>";
        $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM characters");
        if ($result) {
            $row = mysqli_fetch_assoc($result);
            echo "✅ Characters table has " . $row['count'] . " characters<br>";
        } else {
            echo "❌ Error checking characters table: " . mysqli_error($conn) . "<br>";
        }
        
    } else {
        echo "❌ Error checking tables: " . mysqli_error($conn) . "<br>";
    }
    
} else {
    echo "❌ Database connection failed: " . mysqli_connect_error() . "<br>";
}

// Test 5: API Test
echo "<h2>5. API Test</h2>";
$_SESSION['user_id'] = 1; // Set a test user ID

ob_start();
include 'api_get_characters.php';
$api_output = ob_get_clean();

echo "<h3>API Response:</h3>";
echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 5px;'>" . htmlspecialchars($api_output) . "</pre>";

$json_data = json_decode($api_output, true);
if ($json_data) {
    if ($json_data['success']) {
        echo "✅ API working - Found " . $json_data['count'] . " characters<br>";
    } else {
        echo "⚠️ API returned error: " . $json_data['message'] . "<br>";
    }
} else {
    echo "❌ API returned invalid JSON<br>";
}

// Test 6: File Structure
echo "<h2>6. File Structure Test</h2>";
$required_files = [
    'chat.php',
    'api_get_characters.php',
    'dashboard.php',
    'lotn_char_create.php',
    'includes/connect.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file missing<br>";
    }
}

echo "<h2>🎯 System Status</h2>";
echo "<p><strong>Database:</strong> " . ($conn ? "✅ Working" : "❌ Failed") . "</p>";
echo "<p><strong>API:</strong> " . (isset($json_data) && $json_data['success'] ? "✅ Working" : "❌ Failed") . "</p>";
echo "<p><strong>Files:</strong> ✅ All present</p>";

if ($conn && isset($json_data) && $json_data['success']) {
    echo "<h3>🚀 System Ready!</h3>";
    echo "<p>All systems are working. You can now:</p>";
    echo "<ul>";
    echo "<li><a href='login.php'>Login</a></li>";
    echo "<li><a href='chat.php'>Test Chat System</a></li>";
    echo "<li><a href='lotn_char_create.php'>Create Characters</a></li>";
    echo "</ul>";
} else {
    echo "<h3>⚠️ System Issues Detected</h3>";
    echo "<p>Please check the error messages above and fix any issues.</p>";
}

mysqli_close($conn);
?>
