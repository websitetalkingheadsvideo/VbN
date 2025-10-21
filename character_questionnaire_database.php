<?php
// Database-driven questionnaire with cinematic category display
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Database connection
include "includes/connect.php";

// Get all questions from database
$result = mysqli_query($conn, "SELECT * FROM questionnaire_questions ORDER BY ID");
$questions = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get username from session
$username = isset($_SESSION["username"]) ? $_SESSION["username"] : "Guest";

// Get version
if (!defined("LOTN_VERSION")) {
    define("LOTN_VERSION", "0.2.5");
}
$version = LOTN_VERSION;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Character Questionnaire - Valley by Night</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/questionnaire.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IM+Fell+English:ital@0;1&family=IM+Fell+English+SC&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Nosifer&family=Source+Serif+Pro:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400&display=swap" rel="stylesheet">
    <style>
        .category-header {
            background: linear-gradient(135deg, #1a0f0f 0%, #2a1f1f 50%, #1a0f0f 100%);
            border: 2px solid #8b0000;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: categoryFadeIn 1.5s ease-in-out;
        }
        
        .category-header::before {
            content: "";
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #8b0000, #c9a96e, #8b0000, #c9a96e);
            border-radius: 12px;
            z-index: -1;
            animation: borderGlow 3s ease-in-out infinite;
        }
        
        .category-title {
            font-family: "IM Fell English SC", serif;
            font-size: 2.2em;
            color: #c9a96e;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
            letter-spacing: 2px;
        }
        
        .category-subtitle {
            font-family: "Libre Baskerville", serif;
            font-size: 1.1em;
            color: #ddd;
            margin: 8px 0 0 0;
            font-style: italic;
            opacity: 0.9;
        }
        
        .category-description {
            font-family: "Source Serif Pro", serif;
            font-size: 0.95em;
            color: #bbb;
            margin: 12px 0 0 0;
            line-height: 1.4;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        @keyframes categoryFadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes borderGlow {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
        
        .question-section {
            display: none;
        }
        
        .question-section.active {
            display: block;
            animation: questionSlideIn 0.8s ease-out;
        }
        
        @keyframes questionSlideIn {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .progress-section {
            background: rgba(26, 15, 15, 0.9);
            border: 1px solid #444;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .progress-bar {
            background: #333;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .progress-fill {
            background: linear-gradient(90deg, #8b0000, #c9a96e);
            height: 100%;
            width: 0%;
            transition: width 0.5s ease;
        }
        
        .progress-text {
            text-align: center;
            color: #c9a96e;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <!-- Header -->
        <header class="valley-header">
            <div class="header-container">
                <div class="header-left">
                    <div class="logo-placeholder" title="Valley by Night Logo">
                        <div class="logo-frame">
                            <span class="logo-initial">VbN</span>
                        </div>
                    </div>
                    <div class="title-section">
                        <h1 class="site-title">
                            <a href="index.php">Valley by Night</a>
                        </h1>
                        <p class="site-subtitle">A Vampire Tale</p>
                    </div>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <span class="user-label">Kindred:</span>
                        <span class="username"><?php echo htmlspecialchars($username); ?></span>
                        <a href="logout.php" class="logout-btn" title="Logout">Logout</a>
                    </div>
                    <div class="version-info">
                        <span class="version">v<?php echo htmlspecialchars($version); ?></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-wrapper">
            <!-- Admin Debug Panel -->
            <div id="admin-debug-panel" class="admin-debug-panel" style="display: none;">
                <div class="debug-header">
                    <h3>Admin Debug - Clan Scores</h3>
                    <button id="debug-toggle" class="debug-toggle">Hide</button>
                </div>
                <div class="debug-content">
                    <div id="clan-scores-display"></div>
                    <div id="current-answers-display"></div>
                </div>
            </div>

            <div class="questionnaire-container">
                <div class="questionnaire-header">
                    <h1 class="questionnaire-title">🌟 The Night Creates You</h1>
                    <p class="questionnaire-subtitle">Character Creation Questionnaire</p>
                    <p class="questionnaire-description">
                        Answer these questions to discover which vampire clan calls to your soul. 
                        Your responses will guide the ancient blood to reveal your true nature.
                    </p>
                </div>

                <form id="questionnaire-form" class="questionnaire-form">
                    <!-- Progress Indicator -->
                    <div class="progress-section">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progress-fill"></div>
                        </div>
                        <div class="progress-text">
                            <span id="current-question">1</span> of <span id="total-questions"><?php echo count($questions); ?></span>
                        </div>
                    </div>

                    <?php 
                    $currentCategory = "";
                    $questionNumber = 1;
                    foreach ($questions as $question): 
                        // Check if we need to show a new category header
                        if ($currentCategory !== $question["category"]) {
                            $currentCategory = $question["category"];
                            $categoryInfo = [
                                "pre_embrace" => [
                                    "title" => "Before the Embrace",
                                    "subtitle" => "Your Mortal Life",
                                    "description" => "These questions explore who you were before the curse of undeath transformed you. Your mortal experiences shape the foundation of your immortal existence."
                                ],
                                "post_embrace" => [
                                    "title" => "After the Embrace", 
                                    "subtitle" => "Your Immortal Journey",
                                    "description" => "Now that you have tasted the blood and felt the Beast awaken, how do you navigate your new existence in the shadows?"
                                ],
                                "embrace" => [
                                    "title" => "The Embrace",
                                    "subtitle" => "The Moment of Transformation",
                                    "description" => "The Embrace is the most profound moment in a vampire existence. How did you experience this life-changing transformation?"
                                ],
                                "personality" => [
                                    "title" => "Mental Profile",
                                    "subtitle" => "The Essence of Your Soul",
                                    "description" => "Your core personality traits define how you interact with the world and how the Beast manifests within you."
                                ],
                                "perspective" => [
                                    "title" => "Worldview",
                                    "subtitle" => "Your Philosophy of Existence",
                                    "description" => "Immortality changes everything. How do you view the world now that you exist beyond the natural order?"
                                ],
                                "powers" => [
                                    "title" => "Supernatural Potential",
                                    "subtitle" => "The Power Within",
                                    "description" => "Every vampire possesses unique gifts. What supernatural abilities resonate with your immortal soul?"
                                ],
                                "motivation" => [
                                    "title" => "Personal Motivation",
                                    "subtitle" => "What Drives You",
                                    "description" => "In the endless night, what purpose gives meaning to your immortal existence?"
                                ],
                                "supernatural" => [
                                    "title" => "Supernatural Relations",
                                    "subtitle" => "Beyond the Veil",
                                    "description" => "You are not alone in the shadows. How do you view the other supernatural beings that share your world?"
                                ],
                                "secrets" => [
                                    "title" => "Hidden Truths",
                                    "subtitle" => "The Secrets We Keep",
                                    "description" => "Every vampire carries secrets that could destroy them. What hidden truth defines your existence?"
                                ],
                                "fears" => [
                                    "title" => "Immortal Dread",
                                    "subtitle" => "The Fear That Never Dies",
                                    "description" => "Even immortals know fear. What terrifies you most about your eternal existence?"
                                ],
                                "scenario" => [
                                    "title" => "Hypothetical Scenarios",
                                    "subtitle" => "Testing Your Nature",
                                    "description" => "These scenarios reveal how you would respond to the challenges of immortal life."
                                ],
                                "workplace" => [
                                    "title" => "Professional Situations",
                                    "subtitle" => "The Business of Immortality",
                                    "description" => "Even vampires must navigate the professional world. How do you handle workplace politics and power struggles?"
                                ],
                                "family" => [
                                    "title" => "Family & Relationships",
                                    "subtitle" => "Bonds That Endure",
                                    "description" => "Family relationships become complex when you live forever. How do you handle the ties that bind?"
                                ],
                                "social" => [
                                    "title" => "Social Interactions",
                                    "subtitle" => "The Dance of Society",
                                    "description" => "Social dynamics take on new meaning when you can manipulate minds and hearts. How do you navigate social situations?"
                                ],
                                "moral" => [
                                    "title" => "Moral Dilemmas",
                                    "subtitle" => "The Weight of Choice",
                                    "description" => "Immortality forces difficult choices. How do you balance your humanity with your monstrous nature?"
                                ],
                                "power" => [
                                    "title" => "Power & Authority",
                                    "subtitle" => "The Thirst for Control",
                                    "description" => "Power is the currency of the night. How do you seek and wield authority in immortal society?"
                                ],
                                "life" => [
                                    "title" => "Life-Changing Decisions",
                                    "subtitle" => "The Crossroads of Fate",
                                    "description" => "Some decisions define your entire existence. How do you approach the most important choices of your immortal life?"
                                ]
                            ];
                            
                            $info = $categoryInfo[$currentCategory] ?? [
                                "title" => ucfirst(str_replace("_", " ", $currentCategory)),
                                "subtitle" => "Your Journey Continues",
                                "description" => "Each question reveals more about your true nature."
                            ];
                    ?>
                    
                    <!-- Category Header -->
                    <div class="category-header" data-category="<?php echo $currentCategory; ?>">
                        <h2 class="category-title"><?php echo $info["title"]; ?></h2>
                        <p class="category-subtitle"><?php echo $info["subtitle"]; ?></p>
                        <p class="category-description"><?php echo $info["description"]; ?></p>
                    </div>
                    
                    <?php } ?>
                    
                    <!-- Question -->
                    <div class="question-section <?php echo $questionNumber === 1 ? "active" : ""; ?>" data-question="<?php echo $questionNumber; ?>" data-category="<?php echo $question["category"]; ?>">
                        <h2 class="question-title">Question <?php echo $questionNumber; ?></h2>
                        <p class="question-text"><?php echo htmlspecialchars($question["question"]); ?></p>
                        
                        <div class="answer-group">
                            <?php for ($i = 1; $i <= 4; $i++): 
                                $answer = $question["answer" . $i];
                                if (!empty($answer)):
                            ?>
                            <label class="answer-option">
                                <input type="radio" name="question_<?php echo $questionNumber; ?>" value="<?php echo $i; ?>" data-clan-weights="<?php echo htmlspecialchars($question["clanWeight" . $i]); ?>">
                                <span class="answer-text"><?php echo htmlspecialchars($answer); ?></span>
                            </label>
                            <?php endif; endfor; ?>
                        </div>
                    </div>
                    
                    <?php 
                    $questionNumber++;
                    endforeach; 
                    ?>

                    <!-- Navigation Buttons -->
                    <div class="questionnaire-navigation">
                        <button type="button" id="prev-btn" class="nav-btn prev-btn" disabled>Previous</button>
                        <button type="button" id="next-btn" class="nav-btn next-btn" disabled>Next</button>
                        <button type="submit" id="submit-btn" class="nav-btn submit-btn" style="display: none;">Discover Your Clan</button>
                    </div>
                </form>

                <!-- Results Section (Hidden Initially) -->
                <div id="results-section" class="results-section" style="display: none;">
                    <h2 class="results-title">Your Clan Awaits</h2>
                    <div id="clan-result" class="clan-result">
                        <div class="clan-logo-container">
                            <img id="clan-logo" class="clan-logo" src="" alt="Clan Logo">
                            <div class="clan-text-overlay">
                                <h3 id="clan-name" class="clan-name"></h3>
                                <p id="clan-description" class="clan-description"></p>
                            </div>
                        </div>
                    </div>
                    <div class="results-actions">
                        <button type="button" id="retake-btn" class="action-btn">Retake Questionnaire</button>
                        <button type="button" id="create-character-btn" class="action-btn primary">Create Character</button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="valley-footer">
            <div class="footer-container">
                <div class="footer-content">
                    <h2 class="footer-title">
                        <a href="index.php">Valley by Night</a>
                    </h2>
                    <p class="footer-tagline">A Vampire Tale</p>
                </div>
                <div class="footer-bottom">
                    <p class="copyright">© 2025 Valley by Night. All rights reserved.</p>
                    <p class="disclaimer">Vampire: The Masquerade is a trademark of White Wolf Entertainment.</p>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Pass questions data to JavaScript
        const questionsData = <?php echo json_encode($questions); ?>;
    </script>
    <script src="js/questionnaire_database.js"></script>
</body>
</html>
