<?php
// Find and update the actual pre_embrace and post_embrace questions by content
include "includes/connect.php";

// First, let us see all questions and their current categories
echo "<h3>All questions in database:</h3>";
$result = mysqli_query($conn, "SELECT ID, category, LEFT(question, 80) as question_preview FROM questionnaire_questions ORDER BY ID");
if ($result) {
    echo "<table border=1><tr><th>ID</th><th>Category</th><th>Question Preview</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr><td>" . $row["ID"] . "</td><td>" . $row["category"] . "</td><td>" . $row["question_preview"] . "...</td></tr>";
    }
    echo "</table>";
}

// Now find the pre_embrace and post_embrace questions by their category
echo "<h3>Finding pre_embrace and post_embrace questions...</h3>";

// Update pre_embrace questions (should be 10 of them)
$pre_embrace_mappings = [
    "workplace", // Cafeteria humiliation
    "social",    // Parking lot confrontation  
    "family",    // Family dinner
    "social",    // Broken promise
    "workplace", // Stolen credit
    "social",    // Public mockery
    "moral",     // Unexpected betrayal
    "workplace", // Ruined opportunity
    "workplace", // Manipulated reputation
    "family"     // Undeserved reward
];

// Update post_embrace questions (should be 10 of them)
$post_embrace_mappings = [
    "scenario", // First hunt
    "scenario", // Elysium test
    "scenario", // Masquerade breach
    "scenario", // Rival territory
    "scenario", // Unexpected gift
    "scenario", // Dangerous question
    "scenario", // Failed promise
    "scenario", // Starving night
    "scenario", // Domain choice
    "moral"     // Impossible order
];

// Get all pre_embrace questions
$pre_result = mysqli_query($conn, "SELECT ID FROM questionnaire_questions WHERE category = \"pre_embrace\" ORDER BY ID");
$pre_ids = [];
if ($pre_result) {
    while ($row = mysqli_fetch_assoc($pre_result)) {
        $pre_ids[] = $row["ID"];
    }
}

// Get all post_embrace questions  
$post_result = mysqli_query($conn, "SELECT ID FROM questionnaire_questions WHERE category = \"post_embrace\" ORDER BY ID");
$post_ids = [];
if ($post_result) {
    while ($row = mysqli_fetch_assoc($post_result)) {
        $post_ids[] = $row["ID"];
    }
}

echo "<p>Found " . count($pre_ids) . " pre_embrace questions with IDs: " . implode(", ", $pre_ids) . "</p>";
echo "<p>Found " . count($post_ids) . " post_embrace questions with IDs: " . implode(", ", $post_ids) . "</p>";

// Update pre_embrace questions
$updated = 0;
$errors = 0;

for ($i = 0; $i < count($pre_ids) && $i < count($pre_embrace_mappings); $i++) {
    $id = $pre_ids[$i];
    $newCategory = $pre_embrace_mappings[$i];
    
    $sql = "UPDATE questionnaire_questions SET category = ? WHERE ID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $newCategory, $id);
        if (mysqli_stmt_execute($stmt)) {
            $updated++;
            echo "Updated pre_embrace question ID $id to category: $newCategory<br>";
        } else {
            $errors++;
            echo "Error updating question $id: " . mysqli_error($conn) . "<br>";
        }
        mysqli_stmt_close($stmt);
    } else {
        $errors++;
        echo "Error preparing statement for question $id: " . mysqli_error($conn) . "<br>";
    }
}

// Update post_embrace questions
for ($i = 0; $i < count($post_ids) && $i < count($post_embrace_mappings); $i++) {
    $id = $post_ids[$i];
    $newCategory = $post_embrace_mappings[$i];
    
    $sql = "UPDATE questionnaire_questions SET category = ? WHERE ID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $newCategory, $id);
        if (mysqli_stmt_execute($stmt)) {
            $updated++;
            echo "Updated post_embrace question ID $id to category: $newCategory<br>";
        } else {
            $errors++;
            echo "Error updating question $id: " . mysqli_error($conn) . "<br>";
        }
        mysqli_stmt_close($stmt);
    } else {
        $errors++;
        echo "Error preparing statement for question $id: " . mysqli_error($conn) . "<br>";
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
