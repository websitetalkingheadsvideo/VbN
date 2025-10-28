<?php
// update_question_perspective.php - Update the perspective question to scenario
include "includes/connect.php";

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
WHERE question LIKE '%How do you view human society now that you are immortal%'";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    $category = "scenario";
    $question = "You're at a crowded human gathering, watching mortals interact with their petty concerns and fleeting lives. An old friend approaches, excited to tell you about their new job and relationship. As they speak, you realize how trivial their problems seem compared to your eternal existence. They ask for your advice, but you can barely relate to their mortal struggles anymore.";
    
    $answer1 = "You feel superior and detached, recognizing that you've transcended their limited understanding of the world";
    $answer2 = "You're curious about how they've changed and what new experiences they might offer to your immortal perspective";
    $answer3 = "You feel determined to protect or control them, seeing their vulnerability and your responsibility as their superior";
    $answer4 = "You're conflicted by your newfound perspective, struggling to balance your old humanity with your new nature";
    
    $clanWeight1 = "ventrue:3,tremere:2";
    $clanWeight2 = "toreador:3,malkavian:2";
    $clanWeight3 = "ventrue:2,tremere:3";
    $clanWeight4 = "toreador:2,malkavian:3";
    
    mysqli_stmt_bind_param($stmt, "ssssssssss", $category, $question, $answer1, $answer2, $answer3, $answer4, $clanWeight1, $clanWeight2, $clanWeight3, $clanWeight4);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Perspective question updated to scenario successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($conn);
}
mysqli_close($conn);
?>
