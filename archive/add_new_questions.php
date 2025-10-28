<?php
// add_new_questions.php - Add 5 new questions to the database
include "includes/connect.php";

echo "<h2>Adding 5 New Questions to Database</h2>";

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

$inserted = 0;
$errors = 0;

foreach ($newQuestions as $question) {
    $sql = "INSERT INTO questionnaire_questions (category, question, answer1, answer2, answer3, answer4, clanWeight1, clanWeight2, clanWeight3, clanWeight4) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssssss", 
            $question["category"],
            $question["question"], 
            $question["answer1"],
            $question["answer2"],
            $question["answer3"],
            $question["answer4"],
            $question["clanWeight1"],
            $question["clanWeight2"],
            $question["clanWeight3"],
            $question["clanWeight4"]
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $inserted++;
            echo "<p style='color: green;'>✅ Added " . ucfirst($question["category"]) . " question</p>";
        } else {
            $errors++;
            echo "<p style='color: red;'>❌ Error inserting " . $question["category"] . " question: " . mysqli_error($conn) . "</p>";
        }
        mysqli_stmt_close($stmt);
    } else {
        $errors++;
        echo "<p style='color: red;'>❌ Error preparing statement for " . $question["category"] . ": " . mysqli_error($conn) . "</p>";
    }
}

echo "<h3>Summary:</h3>";
echo "<p><strong>Successfully inserted:</strong> $inserted questions</p>";
echo "<p><strong>Errors:</strong> $errors</p>";

if ($inserted > 0) {
    echo "<p style='color: green;'><strong>🎉 New questions added successfully!</strong></p>";
    echo "<p>You now have " . (39 + $inserted) . " total questions in your questionnaire.</p>";
}

mysqli_close($conn);
?>
