<?php
// update_question_supernatural_encounter.php - Update the supernatural encounter question
include "includes/connect.php";

$sql = "UPDATE questionnaire_questions SET 
    question = ?, 
    answer1 = ?, 
    answer2 = ?, 
    answer3 = ?,
    clanWeight1 = ?,
    clanWeight2 = ?,
    clanWeight3 = ?
WHERE question LIKE '%first supernatural encounter reveals something terrifying%'";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    $question = "During your first supernatural encounter, you witness another vampire feeding on a human. As you watch, you realize with horror that you feel no disgust - only hunger. The sight of blood makes your fangs extend involuntarily, and you understand that you are no longer human. The victim's terrified eyes meet yours, and you know you could easily do the same thing.";
    
    $answer1 = "You flee the scene immediately, seeking solitude to process what you've become and what it means for your humanity";
    $answer2 = "You embrace the revelation, recognizing this as your true nature and feeling liberated from human limitations";
    $answer3 = "You approach the other vampire cautiously, hoping they can help you understand and control your new instincts";
    
    $clanWeight1 = "nosferatu:3,gangrel:2";
    $clanWeight2 = "malkavian:3,brujah:2";
    $clanWeight3 = "toreador:3,ventrue:2";
    
    mysqli_stmt_bind_param($stmt, "sssssss", $question, $answer1, $answer2, $answer3, $clanWeight1, $clanWeight2, $clanWeight3);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Supernatural encounter question updated successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($conn);
}
mysqli_close($conn);
?>
