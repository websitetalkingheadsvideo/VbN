<?php
// Fix the questionnaire issues: delete the unwanted question and check why RAND() isnt working
include "includes/connect.php";

echo "<h2>Fixing Questionnaire Issues</h2>";

// 1. Delete the unwanted question
echo "<h3>1. Deleting unwanted question...</h3>";
$delete_result = mysqli_query($conn, "DELETE FROM questionnaire_questions WHERE question LIKE \"%What was your life like before becoming immortal%\"");
if ($delete_result) {
    $deleted = mysqli_affected_rows($conn);
    echo "<p>Deleted $deleted question(s) containing \"What was your life like before becoming immortal\"</p>";
} else {
    echo "<p>Error deleting question: " . mysqli_error($conn) . "</p>";
}

// 2. Check if RAND() is working by testing it multiple times
echo "<h3>2. Testing RAND() function...</h3>";
echo "<p>Testing ORDER BY RAND() multiple times:</p>";

for ($i = 1; $i <= 5; $i++) {
    $test_result = mysqli_query($conn, "SELECT ID, LEFT(question, 50) as preview FROM questionnaire_questions ORDER BY RAND() LIMIT 1");
    if ($test_result && $row = mysqli_fetch_assoc($test_result)) {
        echo "<p>Test $i: ID {$row[\"ID\"]} - {$row[\"preview\"]}...</p>";
    }
}

// 3. Show current question count
$count_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM questionnaire_questions");
if ($count_result) {
    $count = mysqli_fetch_assoc($count_result)["total"];
    echo "<h3>3. Current question count: $count questions</h3>";
}

// 4. Show first few questions in current order
echo "<h3>4. First 5 questions in current database order:</h3>";
$first_result = mysqli_query($conn, "SELECT ID, LEFT(question, 60) as preview FROM questionnaire_questions ORDER BY ID LIMIT 5");
if ($first_result) {
    echo "<table border=1><tr><th>ID</th><th>Question Preview</th></tr>";
    while ($row = mysqli_fetch_assoc($first_result)) {
        echo "<tr><td>{$row[\"ID\"]}</td><td>{$row[\"preview\"]}...</td></tr>";
    }
    echo "</table>";
}

mysqli_close($conn);
?>
