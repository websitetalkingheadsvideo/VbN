<?php
// update_question_werewolf_threat.php - Update the supernatural threat question
include "includes/connect.php";

$sql = "UPDATE questionnaire_questions SET 
    question = ?, 
    answer1 = ?, 
    answer2 = ?, 
    answer3 = ?,
    clanWeight1 = ?,
    clanWeight2 = ?,
    clanWeight3 = ?
WHERE question LIKE '%supernatural threat that threatens not just humans but other vampires%'";

$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    $question = "A werewolf pack has been terrorizing your city, leaving behind grisly scenes that have the mortal authorities asking too many questions. The Prince summons all kindred to Elysium with urgent news: three of your kind have been torn apart, and the pack is closing in on your haven. Panic spreads through the gathering as you realize this isn't just a territorial dispute - it's a war for survival.";
    
    $answer1 = "You step forward to propose a diplomatic solution, offering the werewolves hunting grounds and resources in exchange for a truce that protects both species";
    $answer2 = "You volunteer to lead the counter-attack, rallying kindred to hunt down the pack before they can strike again";
    $answer3 = "You quietly begin gathering intelligence on the pack's movements and weaknesses, knowing that knowledge is the key to victory";
    
    $clanWeight1 = "ventrue:3,toreador:2";
    $clanWeight2 = "brujah:3,gangrel:2";
    $clanWeight3 = "tremere:3,nosferatu:2";
    
    mysqli_stmt_bind_param($stmt, "sssssss", $question, $answer1, $answer2, $answer3, $clanWeight1, $clanWeight2, $clanWeight3);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Werewolf threat question updated successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($conn);
}
mysqli_close($conn);
?>
