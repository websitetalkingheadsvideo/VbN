<?php
// update_question_personality_continued.php - Update the personality traits continued question
include "includes/connect.php";

$sql = "UPDATE questionnaire_questions SET 
    question = ?, 
    answer1 = ?, 
    answer2 = ?, 
    answer3 = ?,
    clanWeight1 = ?,
    clanWeight2 = ?,
    clanWeight3 = ?
WHERE question LIKE '%Select your top three personality traits (continued)%'";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    $question = "Your sire has been watching you carefully during your first month as a vampire, testing your reactions to various situations. Tonight, they present you with a moral dilemma: a human who once wronged you is now vulnerable and alone. Your sire asks, 'What does your heart tell you to do?' as they study your face for your true nature.";
    
    $answer1 = "You feel a cold satisfaction at their vulnerability, recognizing this as poetic justice for their past actions";
    $answer2 = "You see an opportunity to demonstrate mercy and prove that you haven't lost your humanity completely";
    $answer3 = "You remain detached, analyzing the situation purely in terms of what serves your long-term interests";
    
    $clanWeight1 = "nosferatu:3,malkavian:2";
    $clanWeight2 = "toreador:3,ventrue:2";
    $clanWeight3 = "tremere:3,ventrue:2";
    
    mysqli_stmt_bind_param($stmt, "sssssss", $question, $answer1, $answer2, $answer3, $clanWeight1, $clanWeight2, $clanWeight3);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Personality traits continued question updated successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($conn);
}
mysqli_close($conn);
?>
