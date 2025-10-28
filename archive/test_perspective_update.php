<?php
// test_perspective_update.php - Debug version
include "includes/connect.php";

// First, let's see if we can find the question
$check_sql = "SELECT id, question FROM questionnaire_questions WHERE question LIKE '%How do you view human society now that you are immortal%'";
$result = mysqli_query($conn, $check_sql);

if ($result && mysqli_num_rows($result) > 0) {
    echo "Found question:<br>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: " . $row['id'] . " - " . $row['question'] . "<br>";
    }
} else {
    echo "Question not found!<br>";
    echo "Available questions:<br>";
    $all_sql = "SELECT id, question FROM questionnaire_questions WHERE question LIKE '%immortal%' OR question LIKE '%human society%'";
    $all_result = mysqli_query($conn, $all_sql);
    if ($all_result) {
        while ($row = mysqli_fetch_assoc($all_result)) {
            echo "ID: " . $row['id'] . " - " . $row['question'] . "<br>";
        }
    }
}

mysqli_close($conn);
?>
