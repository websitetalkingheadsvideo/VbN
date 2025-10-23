<?php
// Diagnostic script to see the actual question IDs and categories
include "includes/connect.php";

echo "<h2>Current Question Categories in Database</h2>";
$result = mysqli_query($conn, "SELECT ID, category, LEFT(question, 50) as question_preview FROM questionnaire_questions ORDER BY ID");

if ($result) {
    echo "<table border=1 style=\"border-collapse: collapse; width: 100%;\">";
    echo "<tr style=\"background: #1a0f0f; color: #c9a96e;\"><th style=\"padding: 10px;\">ID</th><th style=\"padding: 10px;\">Category</th><th style=\"padding: 10px;\">Question Preview</th></tr>";
    
    while ($row = mysqli_fetch_assoc($result)) {
        $id = $row["ID"];
        $category = $row["category"];
        $preview = $row["question_preview"];
        
        $bgColor = ($id <= 20) ? "#2a1f1f" : "#1f2a1f";
        echo "<tr style=\"background: $bgColor; color: #ddd;\">";
        echo "<td style=\"padding: 10px; text-align: center;\">$id</td>";
        echo "<td style=\"padding: 10px; font-weight: bold; color: #c9a96e;\">$category</td>";
        echo "<td style=\"padding: 10px;\">$preview...</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
