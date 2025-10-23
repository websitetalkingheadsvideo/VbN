<?php
// update_question_secrets.php - Update the secrets question to scenario
include "includes/connect.php";

echo "<h2>Updating Secrets Question</h2>";

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
WHERE question LIKE '%Do you have a secret that even close companions do not know about%'";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    $category = "scenario";
    $question = "A kindred you trust has discovered something about your past that you've kept hidden from everyone. They approach you privately at Elysium, their expression serious. 'I know about your secret,' they whisper, glancing around to ensure no one else is listening. 'What are you willing to do to keep it quiet?' The weight of their knowledge hangs in the air between you.";
    
    $answer1 = "You reveal a hidden talent or ability you've been concealing, hoping to turn this into an opportunity rather than a threat";
    $answer2 = "You acknowledge a past trauma that still haunts you, trusting them with your vulnerability";
    $answer3 = "You admit to a forbidden desire or obsession that could damage your reputation if revealed";
    $answer4 = "You confess to a supernatural weakness that could make you vulnerable to your enemies";
    
    $clanWeight1 = "toreador:3,tremere:2";
    $clanWeight2 = "nosferatu:3,malkavian:2";
    $clanWeight3 = "malkavian:3,toreador:2";
    $clanWeight4 = "nosferatu:2,gangrel:3";
    
    mysqli_stmt_bind_param($stmt, "ssssssssss", $category, $question, $answer1, $answer2, $answer3, $answer4, $clanWeight1, $clanWeight2, $clanWeight3, $clanWeight4);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>✅ Secrets question updated to scenario successfully!</p>";
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
$verify_sql = "SELECT category, question FROM questionnaire_questions WHERE question LIKE '%kindred you trust has discovered%'";
$verify_result = mysqli_query($conn, $verify_sql);
if ($verify_result && $row = mysqli_fetch_assoc($verify_result)) {
    echo "<p><strong>Category:</strong> " . $row['category'] . "</p>";
    echo "<p><strong>Question:</strong> " . $row['question'] . "</p>";
}

mysqli_close($conn);
?>
