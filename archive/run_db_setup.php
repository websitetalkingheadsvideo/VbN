<?php
// Simple script to run database setup via web interface
// This will be uploaded to the server and run there

echo "<h1>Database Setup for Laws Agent</h1>";

// Include the database creation script
echo "<h2>Creating Rulebook Tables...</h2>";
echo "<pre>";
include 'database/create_rulebooks_tables.php';
echo "</pre>";

echo "<h2>Importing Rulebook Data...</h2>";
echo "<pre>";
include 'database/import_rulebooks.php';
echo "</pre>";

echo "<h2>Setup Complete!</h2>";
echo "<p><a href='admin/laws_agent.php'>Test the Laws Agent</a></p>";
?>
