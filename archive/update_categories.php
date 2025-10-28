<?php
// Update questionnaire categories to use the more nuanced system
include "includes/connect.php";

// Update categories for better organization
$categoryUpdates = [
    // Pre-Embrace categories
    "pre_embrace" => "pre_embrace",
    
    // Post-Embrace categories  
    "post_embrace" => "post_embrace",
    
    // Original questionnaire categories (keep these)
    "embrace" => "embrace",
    "personality" => "personality", 
    "perspective" => "perspective",
    "powers" => "powers",
    "motivation" => "motivation",
    "supernatural" => "supernatural",
    "secrets" => "secrets",
    "fears" => "fears",
    "scenario" => "scenario",
    "workplace" => "workplace",
    "family" => "family",
    "social" => "social",
    "moral" => "moral",
    "power" => "power",
    "life" => "life"
];

// Update the database with proper categories
$updates = [
    // Questions 1-10 (Pre-Embrace) - keep as pre_embrace
    "UPDATE questionnaire_questions SET category = \"pre_embrace\" WHERE ID BETWEEN 1 AND 10",
    
    // Questions 11-20 (Post-Embrace) - keep as post_embrace  
    "UPDATE questionnaire_questions SET category = \"post_embrace\" WHERE ID BETWEEN 11 AND 20",
    
    // Questions 21-39 (Original) - update to more specific categories
    "UPDATE questionnaire_questions SET category = \"embrace\" WHERE ID = 21",
    "UPDATE questionnaire_questions SET category = \"personality\" WHERE ID BETWEEN 22 AND 23", 
    "UPDATE questionnaire_questions SET category = \"perspective\" WHERE ID = 24",
    "UPDATE questionnaire_questions SET category = \"powers\" WHERE ID = 25",
    "UPDATE questionnaire_questions SET category = \"motivation\" WHERE ID = 26",
    "UPDATE questionnaire_questions SET category = \"supernatural\" WHERE ID = 27",
    "UPDATE questionnaire_questions SET category = \"secrets\" WHERE ID = 28",
    "UPDATE questionnaire_questions SET category = \"fears\" WHERE ID = 29",
    "UPDATE questionnaire_questions SET category = \"scenario\" WHERE ID BETWEEN 30 AND 34",
    "UPDATE questionnaire_questions SET category = \"workplace\" WHERE ID = 35",
    "UPDATE questionnaire_questions SET category = \"family\" WHERE ID = 36",
    "UPDATE questionnaire_questions SET category = \"social\" WHERE ID = 37",
    "UPDATE questionnaire_questions SET category = \"moral\" WHERE ID = 38",
    "UPDATE questionnaire_questions SET category = \"power\" WHERE ID = 39",
    "UPDATE questionnaire_questions SET category = \"life\" WHERE ID = 40"
];

$success = 0;
$errors = 0;

foreach ($updates as $sql) {
    if (mysqli_query($conn, $sql)) {
        $success++;
    } else {
        $errors++;
        echo "Error: " . mysqli_error($conn) . "<br>";
    }
}

echo "<h2>Category Updates Complete!</h2>";
echo "<p>Successful updates: $success</p>";
echo "<p>Errors: $errors</p>";

// Show final category breakdown
$result = mysqli_query($conn, "SELECT category, COUNT(*) as count FROM questionnaire_questions GROUP BY category ORDER BY category");
if ($result) {
    echo "<h3>Final Category Distribution:</h3>";
    echo "<table border=1><tr><th>Category</th><th>Count</th><th>Description</th></tr>";
    
    $descriptions = [
        "pre_embrace" => "Pre-Embrace Life Scenarios",
        "post_embrace" => "Post-Embrace Vampire Experiences", 
        "embrace" => "The Embrace & Transformation",
        "personality" => "Core Personality Traits",
        "perspective" => "Worldview & Philosophy",
        "powers" => "Supernatural Abilities",
        "motivation" => "Personal Goals & Drives",
        "supernatural" => "Other Supernatural Beings",
        "secrets" => "Hidden Truths & Secrets",
        "fears" => "Deepest Fears & Dreads",
        "scenario" => "Hypothetical Scenarios",
        "workplace" => "Professional Situations",
        "family" => "Family & Relationships",
        "social" => "Social Interactions",
        "moral" => "Moral Dilemmas",
        "power" => "Power & Authority",
        "life" => "Life-Changing Decisions"
    ];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $desc = isset($descriptions[$row["category"]]) ? $descriptions[$row["category"]] : "Unknown";
        echo "<tr><td>" . ucfirst(str_replace("_", " ", $row["category"])) . "</td><td>" . $row["count"] . "</td><td>" . $desc . "</td></tr>";
    }
    echo "</table>";
}

mysqli_close($conn);
?>
