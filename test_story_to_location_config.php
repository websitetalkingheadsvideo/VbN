<?php
/**
 * Test Configuration for Story to Location Feature
 * Run this file to verify your setup is correct
 */

echo "<h1>Story to Location - Configuration Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .section { background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 5px; }
    pre { background: #333; color: #fff; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

// Test 1: Check if config.env exists
echo "<div class='section'>";
echo "<h2>1. Config File Check</h2>";
if (file_exists(__DIR__ . '/config.env')) {
    echo "<p class='success'>✓ config.env file found</p>";
    
    // Load environment variables
    $lines = file(__DIR__ . '/config.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env_vars = [];
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $env_vars[trim($key)] = trim($value);
            if (!getenv(trim($key))) {
                putenv(trim($key) . "=" . trim($value));
            }
        }
    }
    echo "<p>Found " . count($env_vars) . " environment variables</p>";
} else {
    echo "<p class='error'>✗ config.env file not found</p>";
    echo "<p>Create config.env in project root</p>";
}
echo "</div>";

// Test 2: Check AI configuration file
echo "<div class='section'>";
echo "<h2>2. AI Configuration Check</h2>";
$config_path = __DIR__ . '/.taskmaster/config.json';
if (file_exists($config_path)) {
    echo "<p class='success'>✓ .taskmaster/config.json found</p>";
    
    $config = json_decode(file_get_contents($config_path), true);
    if ($config && isset($config['models']['main'])) {
        $main_model = $config['models']['main'];
        echo "<p><strong>Provider:</strong> {$main_model['provider']}</p>";
        echo "<p><strong>Model ID:</strong> {$main_model['modelId']}</p>";
        echo "<p><strong>Max Tokens:</strong> {$main_model['maxTokens']}</p>";
        echo "<p><strong>Temperature:</strong> {$main_model['temperature']}</p>";
    } else {
        echo "<p class='error'>✗ Invalid config.json format</p>";
    }
} else {
    echo "<p class='error'>✗ .taskmaster/config.json not found</p>";
}
echo "</div>";

// Test 3: Check API keys
echo "<div class='section'>";
echo "<h2>3. API Key Check</h2>";

$anthropic_key = getenv('ANTHROPIC_API_KEY');
$openai_key = getenv('OPENAI_API_KEY');

if ($anthropic_key && $anthropic_key !== 'your_anthropic_api_key_here') {
    echo "<p class='success'>✓ Anthropic API key configured</p>";
    echo "<p>Key starts with: " . substr($anthropic_key, 0, 10) . "...</p>";
} else {
    echo "<p class='warning'>⚠ Anthropic API key not configured or using placeholder</p>";
    echo "<p>Add your key to config.env: ANTHROPIC_API_KEY=sk-ant-api03-...</p>";
}

if ($openai_key && $openai_key !== 'your_openai_api_key_here') {
    echo "<p class='success'>✓ OpenAI API key configured</p>";
    echo "<p>Key starts with: " . substr($openai_key, 0, 10) . "...</p>";
} else {
    echo "<p class='warning'>⚠ OpenAI API key not configured (optional if using Anthropic)</p>";
}
echo "</div>";

// Test 4: Check PHP extensions
echo "<div class='section'>";
echo "<h2>4. PHP Extensions Check</h2>";

if (extension_loaded('curl')) {
    echo "<p class='success'>✓ cURL extension enabled</p>";
} else {
    echo "<p class='error'>✗ cURL extension not enabled</p>";
    echo "<p>Enable cURL in php.ini to make API calls</p>";
}

if (extension_loaded('json')) {
    echo "<p class='success'>✓ JSON extension enabled</p>";
} else {
    echo "<p class='error'>✗ JSON extension not enabled</p>";
}

if (extension_loaded('mysqli')) {
    echo "<p class='success'>✓ MySQLi extension enabled</p>";
} else {
    echo "<p class='error'>✗ MySQLi extension not enabled</p>";
}

echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "</div>";

// Test 5: Check required files
echo "<div class='section'>";
echo "<h2>5. Feature Files Check</h2>";

$files = [
    'admin_create_location_story.php' => 'Main admin interface',
    'api_parse_location_story.php' => 'AI parsing endpoint',
    'js/location_story_parser.js' => 'JavaScript handler',
    'api_create_location.php' => 'Save location endpoint',
    'includes/connect.php' => 'Database connection'
];

foreach ($files as $file => $description) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<p class='success'>✓ $file ($description)</p>";
    } else {
        echo "<p class='error'>✗ $file missing</p>";
    }
}
echo "</div>";

// Test 6: Database connection
echo "<div class='section'>";
echo "<h2>6. Database Connection Check</h2>";
require_once 'includes/connect.php';

if ($conn && !$conn->connect_error) {
    echo "<p class='success'>✓ Database connection successful</p>";
    
    // Check if locations table exists
    $result = $conn->query("SHOW TABLES LIKE 'locations'");
    if ($result && $result->num_rows > 0) {
        echo "<p class='success'>✓ 'locations' table exists</p>";
        
        // Count fields
        $fields = $conn->query("DESCRIBE locations");
        $field_count = $fields->num_rows;
        echo "<p>Table has $field_count fields</p>";
    } else {
        echo "<p class='error'>✗ 'locations' table not found</p>";
        echo "<p>Create the locations table first</p>";
    }
} else {
    echo "<p class='error'>✗ Database connection failed</p>";
    echo "<p>Check includes/connect.php settings</p>";
}
echo "</div>";

// Summary
echo "<div class='section'>";
echo "<h2>Summary & Next Steps</h2>";

$anthropic_ready = $anthropic_key && $anthropic_key !== 'your_anthropic_api_key_here';
$files_ready = file_exists(__DIR__ . '/admin_create_location_story.php');
$config_ready = file_exists($config_path);

if ($anthropic_ready && $files_ready && $config_ready && extension_loaded('curl')) {
    echo "<p class='success'>✓✓✓ All systems ready! You can use Story to Location!</p>";
    echo "<p><strong>Next step:</strong> Visit <a href='admin_create_location_story.php'>admin_create_location_story.php</a></p>";
} else {
    echo "<p class='warning'>⚠ Some configuration needed:</p>";
    echo "<ul>";
    if (!$anthropic_ready) {
        echo "<li>Add your Anthropic API key to config.env</li>";
    }
    if (!extension_loaded('curl')) {
        echo "<li>Enable cURL extension in php.ini</li>";
    }
    if (!$config_ready) {
        echo "<li>Verify .taskmaster/config.json exists</li>";
    }
    echo "</ul>";
}

echo "<hr>";
echo "<p><strong>Documentation:</strong></p>";
echo "<ul>";
echo "<li><a href='SETUP_STORY_TO_LOCATION.md' target='_blank'>Quick Setup Guide</a></li>";
echo "<li><a href='STORY_TO_LOCATION_README.md' target='_blank'>Full Documentation</a></li>";
echo "<li><a href='STORY_TO_LOCATION_VISUAL_GUIDE.md' target='_blank'>Visual Guide</a></li>";
echo "</ul>";

echo "</div>";
?>

