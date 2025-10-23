<?php
// update_question_elder_betrayal.php - Update the sire betrayal question
include "includes/connect.php";

$sql = "UPDATE questionnaire_questions SET 
    question = ?, 
    answer1 = ?, 
    answer2 = ?, 
    answer3 = ?,
    clanWeight1 = ?,
    clanWeight2 = ?,
    clanWeight3 = ?
WHERE question LIKE '%Your sire promised to introduce you to someone important%'";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    $question = "A respected elder promised to introduce you to the Prince's most trusted advisor - a meeting that could secure your place in kindred society. You wait alone in the designated location for three hours, watching other vampires come and go while you remain ignored. Later, you discover the elder was seen at an exclusive gathering, toasting with the very person you were supposed to meet, while you were left waiting like a forgotten child.";
    
    $answer1 = "You immediately begin networking with other kindred, using your own charm and intelligence to secure the introduction the elder failed to provide";
    $answer2 = "You confront the elder publicly at the next gathering, making it clear that such betrayal will not be tolerated, regardless of their status";
    $answer3 = "You quietly begin building your own power base, systematically reducing your dependence on the elder while gathering information about their weaknesses";
    
    $clanWeight1 = "ventrue:3,toreador:2";
    $clanWeight2 = "brujah:3";
    $clanWeight3 = "tremere:3,nosferatu:2,gangrel:2";
    
    mysqli_stmt_bind_param($stmt, "sssssss", $question, $answer1, $answer2, $answer3, $clanWeight1, $clanWeight2, $clanWeight3);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Elder betrayal question updated successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($conn);
}
mysqli_close($conn);
?>
