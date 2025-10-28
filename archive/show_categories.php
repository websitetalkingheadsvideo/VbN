<?php
// Display all questionnaire categories
include "includes/connect.php";

$result = mysqli_query($conn, "SELECT category, COUNT(*) as count FROM questionnaire_questions GROUP BY category ORDER BY category");
if ($result) {
    echo "<h2>Questionnaire Categories</h2>";
    echo "<table border=1 style=\"border-collapse: collapse; width: 100%;\">";
    echo "<tr style=\"background: #1a0f0f; color: #c9a96e;\"><th style=\"padding: 10px;\">Category</th><th style=\"padding: 10px;\">Count</th><th style=\"padding: 10px;\">Description</th></tr>";
    
    $descriptions = [
        "pre_embrace" => "Pre-Embrace Life Scenarios - Your mortal experiences before becoming a vampire",
        "post_embrace" => "Post-Embrace Vampire Experiences - Your experiences after becoming immortal", 
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
    
    // Show total
    $total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM questionnaire_questions");
    $total = mysqli_fetch_assoc($total_result)["total"];
    echo "<p style=\"margin-top: 20px; font-size: 1.2em; color: #c9a96e;\"><strong>Total Questions: " . $total . "</strong></p>";
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
