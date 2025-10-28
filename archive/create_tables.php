<?php
// Quick database table creation script
// Run this first to create the tables

echo "<h1>Creating Rulebook Database Tables</h1>";

try {
    echo "<pre>";
    include 'database/create_rulebooks_tables.php';
    echo "</pre>";
    echo "<p style='color: green;'>✅ Tables created successfully!</p>";
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ul>";
    echo "<li>Upload all files from <code>data/extracted_rulebooks/</code> to the server</li>";
    echo "<li>Then run <a href='setup_laws_agent.php'>setup_laws_agent.php</a> to import the data</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
