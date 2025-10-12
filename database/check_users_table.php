<?php
/**
 * Check Users Table Structure
 * Diagnostic tool to verify table columns
 */

require_once __DIR__ . '/../includes/connect.php';

echo "<h2>Users Table Structure</h2>";

$result = mysqli_query($conn, "DESCRIBE users");

if ($result) {
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p style='color: red;'>Error: " . mysqli_error($conn) . "</p>";
}

mysqli_close($conn);

echo "<hr>";
echo "<p><strong>Check if these columns exist:</strong></p>";
echo "<ul>";
echo "<li>email</li>";
echo "<li>email_verified</li>";
echo "<li>verification_token</li>";
echo "<li>verification_expires</li>";
echo "</ul>";
?>

