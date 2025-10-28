<?php
// Populate questionnaire_questions table with all 19 questions
include "includes/connect.php";

// Question data with categories, questions, answers, and clan weights
$questions = [
    [
        "category" => "embrace",
        "question" => "How did you transition to vampire?",
        "answer1" => "Voluntary transformation",
        "answer2" => "Ritualistic Embrace", 
        "answer3" => "Accidental discovery",
        "answer4" => "Supernatural encounter",
        "clanWeight1" => "ventrue:3,tremere:2",
        "clanWeight2" => "tremere:3,nosferatu:2",
        "clanWeight3" => "brujah:3,gangrel:2",
        "clanWeight4" => "malkavian:3,nosferatu:2"
    ],
    [
        "category" => "personality",
        "question" => "Select your top three personality traits:",
        "answer1" => "Passionate",
        "answer2" => "Calculating",
        "answer3" => "Impulsive", 
        "answer4" => "Compassionate",
        "clanWeight1" => "brujah:2,toreador:2",
        "clanWeight2" => "ventrue:2,tremere:2",
        "clanWeight3" => "brujah:2,gangrel:2",
        "clanWeight4" => "toreador:2,malkavian:1"
    ],
    [
        "category" => "personality",
        "question" => "Select your top three personality traits (continued):",
        "answer1" => "Sardonic",
        "answer2" => "Pragmatic",
        "answer3" => "",
        "answer4" => "",
        "clanWeight1" => "malkavian:2,nosferatu:2",
        "clanWeight2" => "ventrue:2,tremere:2",
        "clanWeight3" => "",
        "clanWeight4" => ""
    ],
    [
        "category" => "perspective",
        "question" => "How do you view human society now that you are immortal?",
        "answer1" => "Superior and detached",
        "answer2" => "Curious about change",
        "answer3" => "Determined to protect or control",
        "answer4" => "Conflicted by your newfound perspective",
        "clanWeight1" => "ventrue:3,tremere:2",
        "clanWeight2" => "toreador:3,malkavian:2",
        "clanWeight3" => "ventrue:2,tremere:3",
        "clanWeight4" => "toreador:2,malkavian:3"
    ],
    [
        "category" => "powers",
        "question" => "If supernatural powers could represent your essence, what would they be?",
        "answer1" => "Strength/Combat ability",
        "answer2" => "Social manipulation",
        "answer3" => "Mystical knowledge",
        "answer4" => "Survival skill",
        "clanWeight1" => "brujah:3,gangrel:2",
        "clanWeight2" => "ventrue:3,tremere:2",
        "clanWeight3" => "tremere:3,malkavian:2",
        "clanWeight4" => "gangrel:3,nosferatu:2"
    ],
    [
        "category" => "motivation",
        "question" => "What is your most significant personal goal or motivation?",
        "answer1" => "Survival",
        "answer2" => "Revenge",
        "answer3" => "Knowledge",
        "answer4" => "Redemption",
        "clanWeight1" => "gangrel:3,nosferatu:2",
        "clanWeight2" => "brujah:3,nosferatu:2",
        "clanWeight3" => "tremere:3,malkavian:2",
        "clanWeight4" => "toreador:3,malkavian:1"
    ],
    [
        "category" => "supernatural",
        "question" => "How do you view other supernatural beings in the world?",
        "answer1" => "They are threats to be eliminated",
        "answer2" => "Potential allies or partners",
        "answer3" => "Curious subjects of study",
        "answer4" => "Annoying complications",
        "clanWeight1" => "brujah:3,gangrel:2",
        "clanWeight2" => "tremere:3,toreador:2",
        "clanWeight3" => "tremere:2,malkavian:3",
        "clanWeight4" => "nosferatu:3,gangrel:2"
    ],
    [
        "category" => "secrets",
        "question" => "Do you have a secret that even close companions do not know about?",
        "answer1" => "A hidden talent",
        "answer2" => "A past trauma",
        "answer3" => "A forbidden desire",
        "answer4" => "A supernatural weakness",
        "clanWeight1" => "toreador:3,tremere:2",
        "clanWeight2" => "nosferatu:3,malkavian:2",
        "clanWeight3" => "malkavian:3,toreador:2",
        "clanWeight4" => "nosferatu:2,gangrel:3"
    ],
    [
        "category" => "fears",
        "question" => "What is your greatest fear as an immortal being?",
        "answer1" => "Solitude",
        "answer2" => "Loss of humanity",
        "answer3" => "Exposure of your true nature",
        "answer4" => "Becoming too powerful",
        "clanWeight1" => "nosferatu:3,malkavian:2",
        "clanWeight2" => "toreador:3,tremere:2",
        "clanWeight3" => "nosferatu:2,tremere:3",
        "clanWeight4" => "gangrel:3,brujah:2"
    ],
    [
        "category" => "scenario",
        "question" => "You have just been Embraced by a powerful Ventrue elder in your hometown. What happens when your first attempt to influence local politics goes wrong?",
        "answer1" => "You are publicly humiliated, forced underground and learn the harsh lessons of supernatural power",
        "answer2" => "You realize your true potential as a social manipulator",
        "answer3" => "You discover unexpected allies within the political landscape",
        "answer4" => "",
        "clanWeight1" => "nosferatu:3,brujah:2",
        "clanWeight2" => "ventrue:3,tremere:2",
        "clanWeight3" => "tremere:2,toreador:3",
        "clanWeight4" => ""
    ],
    [
        "category" => "scenario",
        "question" => "Your first supernatural encounter reveals something terrifying about your new immortal nature. How do you process this revelation?",
        "answer1" => "You become withdrawn, seeking isolation and understanding",
        "answer2" => "You decide to embrace the mystery and explore your newfound abilities",
        "answer3" => "You seek out other vampires who might explain or validate your experience",
        "answer4" => "",
        "clanWeight1" => "nosferatu:3,gangrel:2",
        "clanWeight2" => "malkavian:3,tremere:2",
        "clanWeight3" => "toreador:3,ventrue:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "scenario",
        "question" => "During your first hunt in the modern world, you realize that surviving as a vampire requires more than just supernatural strength. How do you respond?",
        "answer1" => "You struggle with the ethical implications of taking human life",
        "answer2" => "You become ruthless and efficient",
        "answer3" => "You seek balance between hunger and restraint",
        "answer4" => "",
        "clanWeight1" => "toreador:3,tremere:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "ventrue:3,tremere:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "scenario",
        "question" => "Your clan elder reveals an ancient secret about your specific bloodline. What is your reaction?",
        "answer1" => "You feel deeply connected to a hidden legacy",
        "answer2" => "You view it as another burden of supernatural existence",
        "answer3" => "You become protective of the knowledge, sensing its potential power",
        "answer4" => "",
        "clanWeight1" => "tremere:3,ventrue:2",
        "clanWeight2" => "nosferatu:3,malkavian:2",
        "clanWeight3" => "tremere:2,ventrue:3",
        "clanWeight4" => ""
    ],
    [
        "category" => "scenario",
        "question" => "When confronted with a supernatural threat that threatens not just humans but other vampires, how do you respond?",
        "answer1" => "You seek diplomatic resolution and compromise",
        "answer2" => "You charge directly into combat",
        "answer3" => "You analyze strategically before taking action",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,tremere:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "tremere:3,ventrue:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "workplace",
        "question" => "You have been working on a major project for six months when your colleague, who you considered a friend, presents your ideas as their own to the board of directors. They get the promotion you were promised, and when you confront them privately, they shrug and say, \"That is just how business works. You should have been smarter about protecting your ideas.\" Your boss is impressed with their \"innovation\" and has no idea it was yours.",
        "answer1" => "You quietly begin documenting everything and building relationships with higher-ups, planning to expose the theft when it will cause maximum damage to their reputation",
        "answer2" => "You confront them publicly at the next meeting, demanding recognition for your work and making it clear that such betrayal will not be tolerated",
        "answer3" => "You accept the loss but begin studying their methods, learning how to play the political game so this never happens to you again",
        "answer4" => "",
        "clanWeight1" => "ventrue:3,tremere:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "tremere:2,ventrue:3",
        "clanWeight4" => ""
    ],
    [
        "category" => "family",
        "question" => "Your sibling has been struggling with addiction for years, and tonight they have stolen money from your parents savings to buy drugs. Your parents are devastated and do not know what to do. Your sibling is passed out on the couch, and you are the only one who can see the full scope of the problem. Your parents are asking you what they should do.",
        "answer1" => "You take charge of the situation, researching treatment options and creating a structured plan to help your sibling while supporting your parents through the process",
        "answer2" => "You tell your parents the hard truth - that your sibling needs to face consequences for their actions and that enabling them will only make things worse",
        "answer3" => "You focus on protecting your parents emotional well-being, handling the immediate crisis while keeping them from the worst details of your siblings situation",
        "answer4" => "",
        "clanWeight1" => "tremere:3,ventrue:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "toreador:3,malkavian:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "social",
        "question" => "You are at a party when you witness a popular person in your social circle bullying someone who is clearly uncomfortable but too shy to stand up for themselves. The bully is making cruel jokes at their expense, and everyone is laughing along to avoid being the next target. The victim looks to you with pleading eyes, but speaking up could cost you your social standing.",
        "answer1" => "You subtly redirect the conversation to a different topic, defusing the situation without directly confronting the bully or drawing attention to yourself",
        "answer2" => "You call out the bully directly, making it clear that such behavior is unacceptable and that you will not stand by while someone is being mistreated",
        "answer3" => "You wait until later to approach the victim privately, offering support and advice on how to handle similar situations in the future",
        "answer4" => "",
        "clanWeight1" => "tremere:3,ventrue:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "toreador:3,malkavian:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "moral",
        "question" => "You discover that your best friend has been cheating on their partner, who is also your friend. The cheater confides in you, asking you to keep their secret and help them cover their tracks. Meanwhile, their partner is planning to propose next month and has asked you to help plan the engagement. You are caught between loyalty to both friends.",
        "answer1" => "You refuse to help with the deception and give the cheater an ultimatum - they either come clean or you will tell their partner yourself",
        "answer2" => "You agree to keep the secret but begin distancing yourself from the situation, avoiding involvement in either the deception or the engagement planning",
        "answer3" => "You use your knowledge strategically, positioning yourself to help both friends while ensuring that when the truth comes out, you are seen as someone who tried to prevent disaster",
        "answer4" => "",
        "clanWeight1" => "toreador:3,tremere:2",
        "clanWeight2" => "nosferatu:3,malkavian:2",
        "clanWeight3" => "ventrue:3,tremere:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "power",
        "question" => "You are offered a promotion that would put you in a position of authority over people you have worked alongside for years. The promotion comes with a significant salary increase and more responsibility, but it also means you will have to make difficult decisions that could affect your former peers livelihoods. Some of them are counting on you to look out for them.",
        "answer1" => "You accept the promotion and immediately begin building relationships with your new peers, learning how to balance the needs of your former colleagues with the demands of your new role",
        "answer2" => "You accept the promotion but make it clear to everyone that your loyalty to your friends will not be compromised by your new responsibilities",
        "answer3" => "You take the promotion and focus on excelling in your new role, knowing that the best way to help your former colleagues is to be successful enough to advocate for them from a position of strength",
        "answer4" => "",
        "clanWeight1" => "toreador:3,tremere:2",
        "clanWeight2" => "brujah:3,gangrel:2",
        "clanWeight3" => "ventrue:3,tremere:2",
        "clanWeight4" => ""
    ],
    [
        "category" => "life",
        "question" => "You are at a crossroads in your life. You have been offered your dream job in another city, but it would mean leaving behind your aging parents, who need help with daily tasks, and your younger sibling, who is struggling with mental health issues. Your family is depending on you, but this opportunity may never come again. You have to decide tonight.",
        "answer1" => "You take the job but create a detailed plan to support your family from afar, including arranging for professional care and regular visits home",
        "answer2" => "You turn down the job, choosing to stay and care for your family, knowing that opportunities will come again but family needs are immediate",
        "answer3" => "You negotiate with the company for a delayed start date, giving you time to set up support systems for your family while still pursuing your dreams",
        "answer4" => "",
        "clanWeight1" => "tremere:3,ventrue:2",
        "clanWeight2" => "toreador:3,malkavian:2",
        "clanWeight3" => "ventrue:3,tremere:2",
        "clanWeight4" => ""
    ]
];

// Insert questions into database
$inserted = 0;
$errors = 0;

foreach ($questions as $question) {
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
        } else {
            $errors++;
            echo "Error inserting question: " . mysqli_error($conn) . "<br>";
        }
        mysqli_stmt_close($stmt);
    } else {
        $errors++;
        echo "Error preparing statement: " . mysqli_error($conn) . "<br>";
    }
}

echo "<h2>Questionnaire Population Complete!</h2>";
echo "<p>Successfully inserted: $inserted questions</p>";
echo "<p>Errors: $errors</p>";

// Show sample of inserted data
$result = mysqli_query($conn, "SELECT * FROM questionnaire_questions ORDER BY ID LIMIT 5");
if ($result) {
    echo "<h3>Sample Questions:</h3>";
    echo "<table border=1>";
    echo "<tr><th>ID</th><th>Category</th><th>Question</th><th>Answer1</th><th>ClanWeight1</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row["ID"] . "</td>";
        echo "<td>" . $row["category"] . "</td>";
        echo "<td>" . substr($row["question"], 0, 50) . "...</td>";
        echo "<td>" . substr($row["answer1"], 0, 30) . "...</td>";
        echo "<td>" . $row["clanWeight1"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

mysqli_close($conn);
?>
