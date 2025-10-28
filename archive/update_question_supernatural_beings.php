<?php
// update_question_supernatural_beings.php - Update the supernatural beings question
include "includes/connect.php";

$sql = "UPDATE questionnaire_questions SET 
    question = ?, 
    answer1 = ?, 
    answer2 = ?, 
    answer3 = ?,
    clanWeight1 = ?,
    clanWeight2 = ?,
    clanWeight3 = ?
WHERE question LIKE '%How do you view other supernatural beings in the world%'";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    $question = "During a late-night hunt, you encounter a werewolf in human form at a bar. They recognize what you are immediately, and you can sense their supernatural nature. The tension is palpable as other patrons remain oblivious to the dangerous standoff between two predators. The werewolf's eyes flash with amber light as they lean forward and whisper, 'We need to talk.'";
    
    $answer1 = "You prepare for combat, knowing that werewolves are dangerous enemies who must be eliminated before they can threaten your kind";
    $answer2 = "You cautiously engage in conversation, recognizing that supernatural beings might have common interests and could be valuable allies";
    $answer3 = "You study them intently, fascinated by the opportunity to learn about another supernatural species and their capabilities";
    
    $clanWeight1 = "brujah:3,gangrel:2";
    $clanWeight2 = "tremere:3,toreador:2";
    $clanWeight3 = "tremere:2,malkavian:3";
    
    mysqli_stmt_bind_param($stmt, "sssssss", $question, $answer1, $answer2, $answer3, $clanWeight1, $clanWeight2, $clanWeight3);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Supernatural beings question updated successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($conn);
}
mysqli_close($conn);
?>
