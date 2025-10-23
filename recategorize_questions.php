<?php
// Recategorize pre_embrace and post_embrace questions to more specific categories
include "includes/connect.php";

// Define the better category mappings based on question content
$categoryMappings = [
    // Pre-Embrace Questions (1-10) - more specific categories
    1 => "workplace", // Cafeteria humiliation - workplace social dynamics
    2 => "social",    // Parking lot confrontation - social interaction
    3 => "family",    // Family dinner - family dynamics
    4 => "social",    // Broken promise - friendship/social
    5 => "workplace", // Stolen credit - workplace politics
    6 => "social",    // Public mockery - social interaction
    7 => "moral",     // Unexpected betrayal - moral dilemma
    8 => "workplace", // Ruined opportunity - workplace situation
    9 => "workplace", // Manipulated reputation - workplace politics
    10 => "family",   // Undeserved reward - family inheritance
    
    // Post-Embrace Questions (11-20) - more specific categories
    11 => "scenario", // First hunt - hypothetical vampire scenario
    12 => "scenario", // Elysium test - vampire society scenario
    13 => "scenario", // Masquerade breach - vampire scenario
    14 => "scenario", // Rival territory - vampire scenario
    15 => "scenario", // Unexpected gift - vampire society scenario
    16 => "scenario", // Dangerous question - vampire politics scenario
    17 => "scenario", // Failed promise - vampire society scenario
    18 => "scenario", // Starving night - vampire scenario
    19 => "scenario", // Domain choice - vampire society scenario
    20 => "moral"     // Impossible order - moral dilemma
];

// Update the categories
$updated = 0;
$errors = 0;

foreach ($categoryMappings as $questionId => $newCategory) {
    $sql = "UPDATE questionnaire_questions SET category = ? WHERE ID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "si", $newCategory, $questionId);
        if (mysqli_stmt_execute($stmt)) {
            $updated++;
        } else {
            $errors++;
            echo "Error updating question $questionId: " . mysqli_error($conn) . "<br>";
        }
        mysqli_stmt_close($stmt);
    } else {
        $errors++;
        echo "Error preparing statement for question $questionId: " . mysqli_error($conn) . "<br>";
    }
}

echo "<h2>Question Recategorization Complete!</h2>";
echo "<p>Updated: $updated questions</p>";
echo "<p>Errors: $errors</p>";

// Show the new category distribution
$result = mysqli_query($conn, "SELECT category, COUNT(*) as count FROM questionnaire_questions GROUP BY category ORDER BY category");
if ($result) {
    echo "<h3>Updated Category Distribution:</h3>";
    echo "<table border=1 style=\"border-collapse: collapse; width: 100%;\">";
    echo "<tr style=\"background: #1a0f0f; color: #c9a96e;\"><th style=\"padding: 10px;\">Category</th><th style=\"padding: 10px;\">Count</th><th style=\"padding: 10px;\">Description</th></tr>";
    
    $descriptions = [
        "embrace" => "The Embrace & Transformation - The moment of becoming a vampire",
        "personality" => "Core Personality Traits - Your fundamental character traits",
        "perspective" => "Worldview & Philosophy - How you view the world as an immortal",
        "powers" => "Supernatural Abilities - What supernatural powers resonate with you",
        "motivation" => "Personal Goals & Drives - What motivates you in immortal life",
        "supernatural" => "Other Supernatural Beings - How you view other creatures of the night",
        "secrets" => "Hidden Truths & Secrets - The secrets that define your existence",
        "fears" => "Deepest Fears & Dreads - What terrifies you most about immortality",
        "scenario" => "Hypothetical Scenarios - How you would handle various situations",
        "workplace" => "Professional Situations - How you handle workplace politics and power",
        "family" => "Family & Relationships - How you manage family ties and relationships",
        "social" => "Social Interactions - How you navigate social situations and dynamics",
        "moral" => "Moral Dilemmas - How you balance humanity with your monstrous nature",
        "power" => "Power & Authority - How you seek and wield power in immortal society",
        "life" => "Life-Changing Decisions - How you approach the most important choices"
    ];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $category = $row["category"];
        $count = $row["count"];
        $description = isset($descriptions[$category]) ? $descriptions[$category] : "No description available";
        
        echo "<tr style=\"background: #2a1f1f; color: #ddd;\">";
        echo "<td style=\"padding: 10px; font-weight: bold; color: #c9a96e;\">" . ucfirst(str_replace("_", " ", $category)) . "</td>";
        echo "<td style=\"padding: 10px; text-align: center;\">" . $count . "</td>";
        echo "<td style=\"padding: 10px;\">" . $description . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($conn);
?>
