<?php
// update_question_motivation.php - Update the motivation question to scenario
include "includes/connect.php";

echo "<h2>Updating Motivation Question</h2>";

$sql = "UPDATE questionnaire_questions SET 
    category = ?,
    question = ?, 
    answer1 = ?, 
    answer2 = ?, 
    answer3 = ?,
    answer4 = ?,
    clanWeight1 = ?,
    clanWeight2 = ?,
    clanWeight3 = ?,
    clanWeight4 = ?
WHERE question LIKE '%What is your most significant personal goal or motivation%'";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    $category = "scenario";
    $question = "Your sire offers you a choice between four different paths for your immortal existence. Each requires different sacrifices and commitments. They explain: 'You can pursue power and influence in kindred society, seek revenge against those who wronged you in life, dedicate yourself to uncovering ancient secrets, or work toward redemption for your past sins. Choose wisely - this decision will shape your eternal existence.'";
    
    $answer1 = "You choose survival above all else, focusing on securing your position and ensuring your continued existence in the dangerous world of kindred";
    $answer2 = "You choose revenge, dedicating yourself to hunting down and destroying those who caused you pain in your mortal life";
    $answer3 = "You choose knowledge, committing to uncovering the deepest mysteries and secrets of vampire existence";
    $answer4 = "You choose redemption, seeking to atone for your past mistakes and find meaning in your immortal existence";
    
    $clanWeight1 = "gangrel:3,nosferatu:2";
    $clanWeight2 = "brujah:3,nosferatu:2";
    $clanWeight3 = "tremere:3,malkavian:2";
    $clanWeight4 = "toreador:3,malkavian:1";
    
    mysqli_stmt_bind_param($stmt, "ssssssssss", $category, $question, $answer1, $answer2, $answer3, $answer4, $clanWeight1, $clanWeight2, $clanWeight3, $clanWeight4);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✅ Motivation question updated to scenario successfully!</p>";
        echo "<p>Rows affected: " . mysqli_stmt_affected_rows($stmt) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<p style='color: red;'>❌ Error preparing statement: " . mysqli_error($conn) . "</p>";
}

// Verify the update
echo "<h3>Verification:</h3>";
$verify_sql = "SELECT category, question FROM questionnaire_questions WHERE question LIKE '%sire offers you a choice%'";
$verify_result = mysqli_query($conn, $verify_sql);
if ($verify_result && $row = mysqli_fetch_assoc($verify_result)) {
    echo "<p><strong>Category:</strong> " . $row['category'] . "</p>";
    echo "<p><strong>Question:</strong> " . $row['question'] . "</p>";
}

mysqli_close($conn);
?>
