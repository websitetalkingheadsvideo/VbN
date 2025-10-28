<?php
include "includes/connect.php";
$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM questionnaire_questions");
$count = mysqli_fetch_assoc($result);
echo "Total questions in database: " . $count["total"] . "<br><br>";

$result = mysqli_query($conn, "SELECT ID, category, question FROM questionnaire_questions ORDER BY ID");
echo "<table border=1><tr><th>ID</th><th>Category</th><th>Question</th></tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>" . $row["ID"] . "</td><td>" . $row["category"] . "</td><td>" . substr($row["question"], 0, 50) . "...</td></tr>";
}
echo "</table>";
mysqli_close($conn);
?>
