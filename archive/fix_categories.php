<?php
// Fix the recategorization - update questions 1-20 to specific categories
include "includes/connect.php";

// First, let us see what categories questions 1-20 currently have
echo "<h3>Current categories for questions 1-20:</h3>";
$result = mysqli_query($conn, "SELECT ID, category FROM questionnaire_questions WHERE ID BETWEEN 1 AND 20 ORDER BY ID");
if ($result) {
    echo "<table border=1><tr><th>ID</th><th>Current Category</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr><td>" . $row["ID"] . "</td><td>" . $row["category"] . "</td></tr>";
    }
    echo "</table>";
}

// Now update them to specific categories
$categoryMappings = [
    1 => "workplace", // Cafeteria humiliation
    2 => "social",    // Parking lot confrontation
    3 => "family",    // Family dinner
    4 => "social",    // Broken promise
    5 => "workplace", // Stolen credit
    6 => "social",    // Public mockery
    7 => "moral",     // Unexpected betrayal
    8 => "workplace", // Ruined opportunity
    9 => "workplace", // Manipulated reputation
    10 => "family",   // Undeserved reward
    11 => "scenario", // First hunt
    12 => "scenario", // Elysium test
    13 => "scenario", // Masquerade breach
    14 => "scenario", // Rival territory
    15 => "scenario", // Unexpected gift
    16 => "scenario", // Dangerous question
    17 => "scenario", // Failed promise
    18 => "scenario", // Starving night
    19 => "scenario", // Domain choice
    20 => "moral"     // Impossible order
];

echo "<h3>Updating categories...</h3>";
$updated = 0;
$errors = 0;

foreach ($categoryMappings as $questionId => $newCategory) {
    $sql = "UPDATE questionnaire_questions SET category = ? WHERE ID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $newCategory, $questionId);
        if (mysqli_stmt_execute($stmt)) {
            $updated++;
            echo "Updated question $questionId to category: $newCategory<br>";
        } else {
            $errors++;
            echo "Error updating question $questionId: " . mysqli_error($conn) . "<br>";
        }
        mysqli_stmt_close($stmt);
    } else {
        $errors++;
        echo "Error preparing statement for question $questionId: " . mysqli_error($conn) . "<br>";
    }
}

echo "<h2>Recategorization Results:</h2>";
echo "<p>Updated: $updated questions</p>";
echo "<p>Errors: $errors</p>";

// Show final category distribution
$result = mysqli_query($conn, "SELECT category, COUNT(*) as count FROM questionnaire_questions GROUP BY category ORDER BY category");
if ($result) {
    echo "<h3>Final Category Distribution:</h3>";
    echo "<table border=1><tr><th>Category</th><th>Count</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr><td>" . $row["category"] . "</td><td>" . $row["count"] . "</td></tr>";
    }
    echo "</table>";
}

mysqli_close($conn);
?>
