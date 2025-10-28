<?php
// Import with progress tracking and timeout handling
echo "<h1>Rulebook Import with Progress</h1>";
echo "<p>This may take several minutes for large books...</p>";

// Set longer execution time
set_time_limit(300); // 5 minutes

// Flush output immediately
if (ob_get_level()) {
    ob_end_flush();
}

echo "<pre>";
echo "Starting rulebook import...\n";
echo "Data directory: " . __DIR__ . "/data/extracted_rulebooks\n\n";

try {
    include 'database/import_rulebooks.php';
    echo "\n✅ Import completed successfully!\n";
    echo "<p style='color: green;'>✅ All rulebooks imported!</p>";
    echo "<p><a href='admin/laws_agent.php' style='background: #8b0000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Laws Agent</a></p>";
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "<p style='color: red;'>❌ Import failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</pre>";
?>
