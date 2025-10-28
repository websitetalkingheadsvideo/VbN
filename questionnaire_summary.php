<?php
// questionnaire_summary.php - Summary of questions by category and clan weights
include "includes/connect.php";

echo "<div style='max-width: 800px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;'>";
echo "<h1 style='text-align: center; color: #8B0000;'>Questionnaire Summary Report</h1>";

// Get all questions with explicit columns
$questions = db_fetch_all($conn,
    "SELECT id, question_text, category, subcategory, created_at 
     FROM questionnaire_questions ORDER BY id",
    "",
    []
);

echo "<h2 style='color: #8B0000; border-bottom: 2px solid #8B0000; padding-bottom: 5px;'>📊 Questions by Category</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 30px;'>";
echo "<tr style='background-color: #f0f0f0;'><th style='padding: 8px;'>Category</th><th style='padding: 8px;'>Count</th><th style='padding: 8px;'>Percentage</th></tr>";

// Count questions by category
$categoryCounts = [];
foreach ($questions as $question) {
    $category = $question['category'];
    if (!isset($categoryCounts[$category])) {
        $categoryCounts[$category] = 0;
    }
    $categoryCounts[$category]++;
}

$totalQuestions = count($questions);

// Sort categories by percentage (descending)
$sortedCategories = [];
foreach ($categoryCounts as $category => $count) {
    $percentage = round(($count / $totalQuestions) * 100, 1);
    $sortedCategories[] = ['category' => $category, 'count' => $count, 'percentage' => $percentage];
}
usort($sortedCategories, function($a, $b) {
    return $b['percentage'] <=> $a['percentage'];
});

foreach ($sortedCategories as $item) {
    echo "<tr>";
    echo "<td style='padding: 8px;'><strong>" . ucfirst($item['category']) . "</strong></td>";
    echo "<td style='padding: 8px; text-align: center;'>" . $item['count'] . "</td>";
    echo "<td style='padding: 8px; text-align: center;'>" . $item['percentage'] . "%</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2 style='color: #8B0000; border-bottom: 2px solid #8B0000; padding-bottom: 5px;'>🏛️ Clan Weight Distribution</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 30px;'>";
echo "<tr style='background-color: #f0f0f0;'><th style='padding: 8px;'>Clan</th><th style='padding: 8px;'>Total Points</th><th style='padding: 8px;'>Questions</th><th style='padding: 8px;'>Average Points</th></tr>";

// Count clan weights
$clanStats = [];
$clans = ['ventrue', 'tremere', 'brujah', 'gangrel', 'toreador', 'malkavian', 'nosferatu'];

foreach ($clans as $clan) {
    $clanStats[$clan] = ['total' => 0, 'questions' => 0];
}

foreach ($questions as $question) {
    for ($i = 1; $i <= 4; $i++) {
        $weightField = "clanWeight" . $i;
        if (!empty($question[$weightField])) {
            $weights = explode(',', $question[$weightField]);
            foreach ($weights as $weight) {
                if (strpos($weight, ':') !== false) {
                    list($clan, $points) = explode(':', $weight);
                    $clan = trim($clan);
                    $points = intval(trim($points));
                    if (in_array($clan, $clans)) {
                        $clanStats[$clan]['total'] += $points;
                        $clanStats[$clan]['questions']++;
                    }
                }
            }
        }
    }
}

// Sort clans by average points (descending)
$sortedClans = [];
foreach ($clanStats as $clan => $stats) {
    $average = $stats['questions'] > 0 ? round($stats['total'] / $stats['questions'], 2) : 0;
    $sortedClans[] = ['clan' => $clan, 'total' => $stats['total'], 'questions' => $stats['questions'], 'average' => $average];
}
usort($sortedClans, function($a, $b) {
    return $b['average'] <=> $a['average'];
});

foreach ($sortedClans as $item) {
    echo "<tr>";
    echo "<td style='padding: 8px;'><strong>" . ucfirst($item['clan']) . "</strong></td>";
    echo "<td style='padding: 8px; text-align: center;'>" . $item['total'] . "</td>";
    echo "<td style='padding: 8px; text-align: center;'>" . $item['questions'] . "</td>";
    echo "<td style='padding: 8px; text-align: center;'>" . $item['average'] . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2 style='color: #8B0000; border-bottom: 2px solid #8B0000; padding-bottom: 5px;'>📋 Detailed Question List</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 30px;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th style='padding: 8px;'>ID</th><th style='padding: 8px;'>Category</th><th style='padding: 8px;'>Question Preview</th><th style='padding: 8px;'>Answers</th><th style='padding: 8px;'>Clan Weights</th>";
echo "</tr>";

foreach ($questions as $question) {
    echo "<tr>";
    echo "<td style='padding: 8px; text-align: center;'>" . $question['ID'] . "</td>";
    echo "<td style='padding: 8px;'><strong>" . ucfirst($question['category']) . "</strong></td>";
    echo "<td style='padding: 8px;'>" . htmlspecialchars(substr($question['question'], 0, 80)) . "...</td>";
    
    // Count answers
    $answerCount = 0;
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($question["answer$i"])) {
            $answerCount++;
        }
    }
    echo "<td style='padding: 8px; text-align: center;'>" . $answerCount . "</td>";
    
    // Show clan weights
    $allWeights = [];
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($question["clanWeight$i"])) {
            $allWeights[] = $question["clanWeight$i"];
        }
    }
    echo "<td style='padding: 8px; font-size: 0.9em;'>" . implode('<br>', $allWeights) . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2 style='color: #8B0000; border-bottom: 2px solid #8B0000; padding-bottom: 5px;'>📈 Summary Statistics</h2>";
echo "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px;'>";
echo "<ul style='list-style-type: none; padding: 0;'>";
echo "<li style='margin: 8px 0;'><strong>Total Questions:</strong> " . $totalQuestions . "</li>";
echo "<li style='margin: 8px 0;'><strong>Categories:</strong> " . count($categoryCounts) . "</li>";
echo "<li style='margin: 8px 0;'><strong>Most Common Category:</strong> " . ucfirst(array_search(max($categoryCounts), $categoryCounts)) . " (" . max($categoryCounts) . " questions)</li>";

$mostWeightedClan = array_search(max(array_column($clanStats, 'total')), array_column($clanStats, 'total'));
echo "<li style='margin: 8px 0;'><strong>Most Weighted Clan:</strong> " . ucfirst($mostWeightedClan) . " (" . $clanStats[$mostWeightedClan]['total'] . " total points)</li>";
echo "</ul>";
echo "</div>";

echo "</div>"; // Close the main container

mysqli_close($conn);
?>
