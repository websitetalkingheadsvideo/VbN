<?php
// Test the fixed import script
echo "<h1>Testing Rulebook Import (Fixed Paths)</h1>";

try {
    echo "<pre>";
    include 'database/import_rulebooks.php';
    echo "</pre>";
    echo "<p style='color: green;'>✅ Import completed!</p>";
    echo "<p><a href='admin/laws_agent.php' style='background: #8b0000; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Test Laws Agent</a></p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
