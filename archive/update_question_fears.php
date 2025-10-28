<?php
// update_question_fears.php - Update the fears question to scenario
include "includes/connect.php";

echo "<h2>Updating Fears Question</h2>";

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
WHERE question LIKE '%What is your greatest fear as an immortal being%'";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    $category = "scenario";
    $question = "You're forced to confront your deepest fear in a moment of crisis. The situation demands that you face what terrifies you most: being abandoned by all other kindred and left to face eternity alone, watching your humanity slip away completely as you become a monster, having your true nature exposed to mortals who will hunt you down, or becoming so powerful that you lose touch with reality and become a danger to everyone around you. The choice is yours, but you cannot avoid it.";
    
    $answer1 = "You face the fear of eternal solitude, accepting that you may have to walk your immortal path alone";
    $answer2 = "You confront the loss of your humanity, struggling to maintain your connection to what makes you human";
    $answer3 = "You deal with the threat of exposure, taking measures to protect your secret identity from discovery";
    $answer4 = "You grapple with the danger of becoming too powerful, seeking to control your growing abilities";
    
    $clanWeight1 = "nosferatu:3,malkavian:2";
    $clanWeight2 = "toreador:3,tremere:2";
    $clanWeight3 = "nosferatu:2,tremere:3";
    $clanWeight4 = "gangrel:3,brujah:2";
    
    mysqli_stmt_bind_param($stmt, "ssssssssss", $category, $question, $answer1, $answer2, $answer3, $answer4, $clanWeight1, $clanWeight2, $clanWeight3, $clanWeight4);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✅ Fears question updated to scenario successfully!</p>";
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
$verify_sql = "SELECT category, question FROM questionnaire_questions WHERE question LIKE '%forced to confront your deepest fear%'";
$verify_result = mysqli_query($conn, $verify_sql);
if ($verify_result && $row = mysqli_fetch_assoc($verify_result)) {
    echo "<p><strong>Category:</strong> " . $row['category'] . "</p>";
    echo "<p><strong>Question:</strong> " . $row['question'] . "</p>";
}

mysqli_close($conn);
?>
