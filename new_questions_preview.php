<?php
// new_questions_preview.php - Preview of 5 new questions to add
echo "<div style='max-width: 800px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;'>";
echo "<h1 style='text-align: center; color: #8B0000;'>New Questions Preview</h1>";

$newQuestions = [
    [
        "category" => "embrace",
        "question" => "Your Embrace was not what you expected. Instead of the romantic transformation you imagined, you wake up in a cold, dark basement, your sire nowhere to be found. The hunger is overwhelming, and you realize you have no idea how to feed or survive. A human stumbles into the basement, clearly lost and frightened. They see your fangs and begin to scream.",
        "answer1" => "You flee immediately, terrified of what you've become and desperate to find your sire for guidance",
        "answer2" => "You embrace the hunger, allowing your predatory instincts to take over as you hunt your first victim",
        "answer3" => "You try to calm the human, using your remaining humanity to help them escape while you figure out what to do",
        "answer4" => "You study the situation carefully, learning from this first encounter and planning your next moves",
        "clanWeight1" => "toreador:3,malkavian:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "toreador:2,ventrue:3",
        "clanWeight4" => "nosferatu:3,tremere:2"
    ],
    [
        "category" => "powers",
        "question" => "During a confrontation with another vampire, your supernatural abilities manifest in an unexpected way. You feel something primal awakening within you - the ability to command animals, fade into shadows, or sense the emotions of those around you. The other vampire steps back in surprise, clearly not expecting this level of power from someone so young.",
        "answer1" => "You use your new abilities to dominate the situation, establishing your superiority through raw supernatural might",
        "answer2" => "You retreat into the shadows, using your powers to observe and learn from a safe distance",
        "answer3" => "You focus on the emotional connection, using your abilities to understand and manipulate the other vampire's feelings",
        "answer4" => "You experiment with your powers, testing their limits and discovering new ways to use them",
        "clanWeight1" => "brujah:3,gangrel:2",
        "clanWeight2" => "nosferatu:3,gangrel:2",
        "clanWeight3" => "toreador:3,malkavian:2",
        "clanWeight4" => "malkavian:3,tremere:2"
    ],
    [
        "category" => "motivation",
        "question" => "Your sire has been watching your progress carefully and finally offers you a choice that will shape your eternal existence. They can teach you the secrets of political manipulation and social control, help you hunt down those who betrayed you in your mortal life, provide access to forbidden knowledge about the true nature of vampirism, or guide you toward spiritual redemption and inner peace. Each path requires different sacrifices and will change you in ways you cannot predict.",
        "answer1" => "You choose political mastery, learning to manipulate kindred society and never being at anyone's mercy again",
        "answer2" => "You choose the path of vengeance, dedicating your immortal existence to destroying those who wronged you",
        "answer3" => "You choose forbidden knowledge, committing to uncovering the deepest mysteries of vampire existence",
        "answer4" => "You choose spiritual redemption, seeking to find peace and meaning in your immortal existence",
        "clanWeight1" => "ventrue:3,tremere:2",
        "clanWeight2" => "brujah:3,nosferatu:2",
        "clanWeight3" => "tremere:3,malkavian:2",
        "clanWeight4" => "toreador:3,malkavian:1"
    ],
    [
        "category" => "supernatural",
        "question" => "You're feeding in a secluded area when you witness something impossible: a human transforming into a werewolf right before your eyes. The transformation is violent and painful, and the newly changed werewolf immediately attacks a nearby mortal. As you watch, you realize this human was someone you knew in your mortal life - a friend who disappeared months ago. The werewolf hasn't noticed you yet, but you can see the human intelligence still flickering in its eyes.",
        "answer1" => "You intervene immediately, using your supernatural abilities to save the mortal and subdue the werewolf",
        "answer2" => "You retreat silently, knowing that werewolves are dangerous enemies and this situation is beyond your control",
        "answer3" => "You study the werewolf carefully, fascinated by the transformation and hoping to learn more about this supernatural phenomenon",
        "answer4" => "You try to reach the human part of the werewolf, calling out your friend's name in an attempt to help them regain control",
        "clanWeight1" => "brujah:3,gangrel:2",
        "clanWeight2" => "nosferatu:3,tremere:2",
        "clanWeight3" => "tremere:3,malkavian:2",
        "clanWeight4" => "toreador:3,malkavian:2"
    ],
    [
        "category" => "life",
        "question" => "You face the most difficult decision of your immortal existence. Your mortal family is in danger, and you must choose between saving them and maintaining your secret identity, or revealing your true nature to protect them. The choice will determine not only their fate but also your place in both mortal and kindred society forever.",
        "answer1" => "You save them while maintaining your secret, finding a way to protect them without revealing your true nature",
        "answer2" => "You reveal your nature to save them, accepting that you can never return to your old life",
        "answer3" => "You seek help from other kindred, using your connections in vampire society to solve the problem",
        "answer4" => "You make the hard choice to let them face the danger alone, protecting the Masquerade above all else",
        "clanWeight1" => "tremere:3,ventrue:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "ventrue:3,toreador:2",
        "clanWeight4" => "nosferatu:3,malkavian:2"
    ]
];

foreach ($newQuestions as $index => $question) {
    $questionNum = $index + 1;
    echo "<div style='border: 2px solid #8B0000; margin: 20px 0; padding: 15px; border-radius: 5px;'>";
    echo "<h3 style='color: #8B0000; margin-top: 0;'>Question " . $questionNum . " - " . ucfirst($question['category']) . "</h3>";
    echo "<p><strong>Question:</strong> " . $question['question'] . "</p>";
    echo "<p><strong>Answer 1:</strong> " . $question['answer1'] . " <em>(" . $question['clanWeight1'] . ")</em></p>";
    echo "<p><strong>Answer 2:</strong> " . $question['answer2'] . " <em>(" . $question['clanWeight2'] . ")</em></p>";
    echo "<p><strong>Answer 3:</strong> " . $question['answer3'] . " <em>(" . $question['clanWeight3'] . ")</em></p>";
    echo "<p><strong>Answer 4:</strong> " . $question['answer4'] . " <em>(" . $question['clanWeight4'] . ")</em></p>";
    echo "</div>";
}

echo "<h2 style='color: #8B0000;'>Clan Weight Analysis</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background-color: #f0f0f0;'><th>Clan</th><th>Total Points</th><th>Questions</th></tr>";

$clanTotals = [];
$clans = ['ventrue', 'tremere', 'brujah', 'gangrel', 'toreador', 'malkavian', 'nosferatu'];

foreach ($clans as $clan) {
    $clanTotals[$clan] = 0;
}

foreach ($newQuestions as $question) {
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
                        $clanTotals[$clan] += $points;
                    }
                }
            }
        }
    }
}

foreach ($clanTotals as $clan => $total) {
    echo "<tr>";
    echo "<td style='padding: 8px;'><strong>" . ucfirst($clan) . "</strong></td>";
    echo "<td style='padding: 8px; text-align: center;'>" . $total . "</td>";
    echo "<td style='padding: 8px; text-align: center;'>" . ($total > 0 ? "Yes" : "No") . "</td>";
    echo "</tr>";
}

echo "</table>";
echo "</div>";
?>
