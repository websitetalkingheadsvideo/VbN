<?php
// update_question_powers.php - Update the supernatural powers question
include "includes/connect.php";

$sql = "UPDATE questionnaire_questions SET 
    question = ?, 
    answer1 = ?, 
    answer2 = ?, 
    answer3 = ?,
    clanWeight1 = ?,
    clanWeight2 = ?,
    clanWeight3 = ?
WHERE question LIKE '%supernatural powers could represent your essence%'";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    $question = "During your first hunt, you discover your supernatural abilities manifesting. A mugger attacks you in a dark alley, but instead of fear, you feel something primal awakening within you. Your body responds with inhuman speed and strength, and you realize you could easily tear them apart. The Beast whispers in your mind, urging you to embrace your true nature.";
    
    $answer1 = "You unleash your full supernatural might, reveling in the power and making an example of anyone who would threaten you";
    $answer2 = "You use just enough force to subdue them, then dominate their mind to ensure they never remember this encounter";
    $answer3 = "You restrain yourself, using only what's necessary to escape while struggling to control the hunger for more";
    
    $clanWeight1 = "brujah:3,gangrel:2";
    $clanWeight2 = "ventrue:3,tremere:2";
    $clanWeight3 = "toreador:3,malkavian:2";
    
    mysqli_stmt_bind_param($stmt, "sssssss", $question, $answer1, $answer2, $answer3, $clanWeight1, $clanWeight2, $clanWeight3);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Powers question updated successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($conn);
}
mysqli_close($conn);
?>
