<?php
// Check what questions are actually in the database
include "includes/connect.php";

echo "<h2>Current Questions in Database</h2>";
$result = mysqli_query($conn, "SELECT ID, category, LEFT(question, 100) as question_preview FROM questionnaire_questions ORDER BY ID");

if ($result) {
    echo "<table border=1 style=\"border-collapse: collapse; width: 100%;\">";
    echo "<tr style=\"background: #1a0f0f; color: #c9a96e;\"><th style=\"padding: 10px;\">ID</th><th style=\"padding: 10px;\">Category</th><th style=\"padding: 10px;\">Question Preview</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row["ID"];
        $category = $row["category"];
        $preview = $row["question_preview"];
        
        echo "<tr style=\"background: #2a1f1f; color: #ddd;\">";
        echo "<td style=\"padding: 10px; text-align: center;\">$id</td>";
        echo "<td style=\"padding: 10px; font-weight: bold; color: #c9a96e;\">$category</td>";
        echo "<td style=\"padding: 10px;\">$preview...</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Count by category
    echo "<h3>Question Count by Category:</h3>";
    $count_result = mysqli_query($conn, "SELECT category, COUNT(*) as count FROM questionnaire_questions GROUP BY category ORDER BY category");
    if ($count_result) {
        echo "<table border=1><tr><th>Category</th><th>Count</th></tr>";
        while ($row = mysqli_fetch_assoc($count_result)) {
            echo "<tr><td>" . $row["category"] . "</td><td>" . $row["count"] . "</td></tr>";
        }
        echo "</table>";
    }
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
