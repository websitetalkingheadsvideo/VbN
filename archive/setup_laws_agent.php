<?php
// Database setup with missing file detection
echo "<h1>Laws Agent Database Setup</h1>";

// Check if extracted data exists
$extracted_dir = 'data/extracted_rulebooks/';
$missing_files = [];

if (!is_dir($extracted_dir)) {
    echo "<p style='color: red;'>❌ Extracted rulebooks directory not found: {$extracted_dir}</p>";
    echo "<p>Please upload the extracted PDF data files to the server first.</p>";
    exit;
}

// Check for key files
$required_files = [
    '_extraction_summary.json',
    'Guide to the Camarilla.json',
    'MET - VTM - Camarilla Guide (5017).json'
];

foreach ($required_files as $file) {
    if (!file_exists($extracted_dir . $file)) {
        $missing_files[] = $file;
    }
}

if (!empty($missing_files)) {
    echo "<p style='color: orange;'>⚠️ Some extracted files are missing:</p>";
    echo "<ul>";
    foreach ($missing_files as $file) {
        echo "<li>{$file}</li>";
    }
    echo "</ul>";
    echo "<p>Please ensure all files from <code>data/extracted_rulebooks/</code> are uploaded to the server.</p>";
    echo "<p><a href='create_tables.php'>Create tables only</a> (without importing data)</p>";
    exit;
}

echo "<h2>✅ All extracted files found!</h2>";

try {
    echo "<h2>Creating Tables...</h2>";
    echo "<pre>";
    include 'database/create_rulebooks_tables.php';
    echo "</pre>";
    
    echo "<h2>Importing Data...</h2>";
    echo "<pre>";
    include 'database/import_rulebooks.php';
    echo "</pre>";
    
    echo "<h2>🎉 Setup Complete!</h2>";
    echo "<p><a href='admin/laws_agent.php' style='background: #8b0000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Laws Agent</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
