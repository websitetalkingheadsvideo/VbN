<?php
/**
 * Random Question Questionnaire - Valley by Night
 * 20 random questions with fade transitions
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include 'includes/connect.php';

// Get 20 random questions from database
$result = mysqli_query($conn, "SELECT * FROM questionnaire_questions ORDER BY RAND() LIMIT 20");
$questions = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get username from session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// Get version
if (!defined('LOTN_VERSION')) {
    define('LOTN_VERSION', '0.2.6');
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
        .questionnaire-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .question-section {
            display: none;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
        
        .question-section.active {
            display: block;
            opacity: 1;
        }
        
        .question-section.fade-out {
            opacity: 0;
        }
        
        .question-title {
            font-family: "IM Fell English SC", serif;
            font-size: 1.8em;
            color: #c9a96e;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .question-category {
            font-family: "IM Fell English SC", serif;
            font-size: 1.8em;
            color: rgba(201, 169, 110, 0.75);
            text-align: center;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.6s ease-out;
        }
        
        .question-category.animate-in {
            opacity: 1;
            transform: translateY(0);
        }
        
        .question-text {
            font-family: "Source Serif Pro", serif;
            font-size: 1.1em;
            color: #ddd;
            margin-bottom: 20px;
            text-align: center;
            line-height: 1.5;
        }
        
        .answer-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .answer-option {
            display: flex;
            align-items: center;
            padding: 12px;
            background: rgba(26, 15, 15, 0.8);
            border: 1px solid #444;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .answer-option:hover {
            background: rgba(139, 0, 0, 0.2);
            border-color: #8b0000;
        }
        
        .answer-option input[type="radio"] {
            margin-right: 12px;
            transform: scale(1.1);
        }
        
        .answer-text {
            font-family: "Libre Baskerville", serif;
            color: #bbb;
            font-size: 1em;
        }
        
        .progress-section {
            background: rgba(26, 15, 15, 0.9);
            border: 1px solid #444;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
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
            width: 5%;
            transition: width 0.5s ease;
        }
        
        .progress-text {
            text-align: center;
            color: #c9a96e;
            font-weight: bold;
        }
        
        .questionnaire-navigation {
            text-align: center;
            margin-top: 20px;
        }
        
        .nav-btn {
            background: linear-gradient(135deg, #8b0000, #c9a96e);
            color: #fff;
            border: none;
            padding: 12px 30px;
            font-size: 1.1em;
            font-family: "IM Fell English SC", serif;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: 0 10px;
        }
        
        .nav-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #a00000, #d9b96e);
            transform: translateY(-2px);
        }
        
        .nav-btn:disabled {
            background: #444;
            color: #666;
            cursor: not-allowed;
            transform: none;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, #c9a96e, #8b0000);
        }
        
        /* Tracking Popup Styles */
        .tracking-popup {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 300px;
            background: rgba(26, 15, 15, 0.95);
            border: 2px solid #8b0000;
            border-radius: 10px;
            padding: 15px;
            z-index: 1000;
            font-family: "Source Serif Pro", serif;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }
        
        .tracking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #444;
            padding-bottom: 8px;
        }
        
        .tracking-title {
            color: #c9a96e;
            font-family: "IM Fell English SC", serif;
            font-size: 1.2em;
            margin: 0;
        }
        
        .tracking-close {
            background: none;
            border: none;
            color: #666;
            font-size: 1.5em;
            cursor: pointer;
            padding: 0;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .tracking-close:hover {
            color: #c9a96e;
        }
        
        .clan-score {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid rgba(68, 68, 68, 0.3);
        }
        
        .clan-score:last-child {
            border-bottom: none;
        }
        
        .clan-name {
            color: #bbb;
            font-weight: bold;
            text-transform: capitalize;
        }
        
        .clan-points {
            color: #c9a96e;
            font-weight: bold;
            background: rgba(139, 0, 0, 0.2);
            padding: 2px 8px;
            border-radius: 4px;
        }
        
        .tracking-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #8b0000, #c9a96e);
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 6px;
            cursor: pointer;
            font-family: "IM Fell English SC", serif;
            font-size: 0.9em;
            z-index: 1001;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        
        .tracking-toggle:hover {
            background: linear-gradient(135deg, #a00000, #d9b96e);
        }
        
        /* Admin Debug Popup Styles */
        .admin-debug-popup {
            position: fixed;
            bottom: 20px;
            left: 20px;
            width: 400px;
            background: rgba(26, 15, 15, 0.95);
            border: 2px solid #c9a96e;
            border-radius: 10px;
            padding: 15px;
            z-index: 1000;
            font-family: "Source Serif Pro", serif;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            max-height: 300px;
            overflow-y: auto;
        }
        
        .admin-debug-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px solid #444;
            padding-bottom: 8px;
        }
        
        .admin-debug-title {
            color: #c9a96e;
            font-family: "IM Fell English SC", serif;
            font-size: 1.1em;
            margin: 0;
        }
        
        .admin-debug-close {
            background: none;
            border: none;
            color: #666;
            font-size: 1.3em;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .admin-debug-close:hover {
            color: #c9a96e;
        }
        
        .admin-debug-content {
            font-size: 0.85em;
            line-height: 1.4;
        }
        
        .admin-debug-json {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid #444;
            border-radius: 4px;
            padding: 8px;
            margin-top: 8px;
            font-family: monospace;
            color: #ddd;
            white-space: pre-wrap;
            word-break: break-all;
        }
        
        .admin-debug-toggle {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: linear-gradient(135deg, #c9a96e, #8b0000);
            color: #fff;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-family: "IM Fell English SC", serif;
            font-size: 0.8em;
            z-index: 1001;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        
        .admin-debug-toggle:hover {
            background: linear-gradient(135deg, #d9b96e, #a00000);
        }
        
        /* Results Section Styles */
        .results-section {
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
        }
        
        .results-section.active {
            opacity: 1;
        }
        
        .results-container {
            text-align: center;
            padding: 40px 20px;
        }
        
        .results-title {
            font-family: "IM Fell English SC", serif;
            font-size: 2.5em;
            color: #c9a96e;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        
        .clan-result {
            background: rgba(26, 15, 15, 0.9);
            border: 2px solid #c9a96e;
            border-radius: 15px;
            padding: 30px;
            margin: 20px 0;
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        }
        
        .clan-logo-container {
            margin-bottom: 25px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 400px;
        }
        
        .clan-logo {
            width: 400px;
            height: 400px;
            object-fit: fill;
            object-position: center;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }
        
        .clan-name {
            font-family: "IM Fell English SC", serif;
            font-size: 2.2em;
            color: #c9a96e;
            margin-bottom: 15px;
            text-transform: capitalize;
        }
        
        .clan-description {
            font-family: "Source Serif Pro", serif;
            font-size: 1.2em;
            color: #ddd;
            line-height: 1.6;
            margin-bottom: 25px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .clan-stats {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid #444;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .clan-stats h3 {
            font-family: "IM Fell English SC", serif;
            color: #c9a96e;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        
        .all-clan-scores {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .results-actions {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .results-actions .nav-btn {
            min-width: 180px;
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
            <!-- Tracking Toggle Button -->
            <button id="tracking-toggle" class="tracking-toggle">Show Clan Scores</button>
            
            <!-- Admin Debug Toggle Button -->
            <button id="admin-debug-toggle" class="admin-debug-toggle" style="display: none;">Admin Debug</button>
            
            <!-- Tracking Popup -->
            <div id="tracking-popup" class="tracking-popup" style="display: none;">
                <div class="tracking-header">
                    <h3 class="tracking-title">Clan Tracking</h3>
                    <button id="tracking-close" class="tracking-close">&times;</button>
                </div>
                <div id="tracking-content">
                    <!-- Clan scores will be populated here -->
                </div>
            </div>
            
            <!-- Admin Debug Popup -->
            <div id="admin-debug-popup" class="admin-debug-popup" style="display: none;">
                <div class="admin-debug-header">
                    <h3 class="admin-debug-title">Admin Debug</h3>
                    <button id="admin-debug-close" class="admin-debug-close">&times;</button>
                </div>
                <div id="admin-debug-content" class="admin-debug-content">
                    <!-- Debug content will be populated here -->
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
                            <span id="current-question">1</span> of <span id="total-questions">20</span>
                        </div>
                    </div>

                    <?php 
                    $questionNumber = 1;
                    foreach ($questions as $question): 
                    ?>
                    
                    <!-- Question -->
                    <div class="question-section <?php echo $questionNumber === 1 ? "active" : ""; ?>" data-question="<?php echo $questionNumber; ?>">
                        <h2 class="question-title">Question <?php echo $questionNumber; ?></h2>
                        <div class="question-category" data-category="<?php echo htmlspecialchars($question["category"]); ?>">
                            <?php echo ucfirst(htmlspecialchars($question["category"])); ?>
                        </div>
                        <p class="question-text"><?php echo htmlspecialchars($question["question"]); ?></p>
                        
                        <div class="answer-group">
                            <?php for ($i = 1; $i <= 4; $i++): 
                                $answer = $question["answer" . $i];
                                if (!empty($answer)):
                            ?>
                            <label class="answer-option">
                                <input type="radio" name="question_<?php echo $questionNumber; ?>" value="<?php echo $i; ?>">
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
                        <button type="button" id="next-btn" class="nav-btn next-btn" disabled>Next Question</button>
                        <button type="submit" id="submit-btn" class="nav-btn submit-btn" style="display: none;">Complete Questionnaire</button>
                    </div>
                </form>

                <!-- Results Section -->
                <div id="results-section" class="results-section" style="display: none;">
                    <div class="results-container">
                        <h1 class="results-title">🌟 Your Clan Has Been Revealed 🌟</h1>
                        <div class="clan-result">
                            <div class="clan-logo-container">
                                <img id="clan-logo" src="" alt="Clan Logo" class="clan-logo">
                            </div>
                            <h2 id="clan-name" class="clan-name"></h2>
                            <p id="clan-description" class="clan-description"></p>
                            <div class="clan-stats">
                                <h3>Your Clan Scores:</h3>
                                <div id="all-clan-scores" class="all-clan-scores"></div>
                            </div>
                        </div>
                        <div class="results-actions">
                            <button id="retake-btn" class="nav-btn">Retake Questionnaire</button>
                            <button id="create-character-btn" class="nav-btn submit-btn">Create Character</button>
                        </div>
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
        
        let currentQuestion = 1;
        const totalQuestions = 20;
        
        // Clan tracking system
        const clanTracking = {
            ventrue: 0,
            tremere: 0,
            brujah: 0,
            nosferatu: 0,
            malkavian: 0,
            toreador: 0,
            gangrel: 0
        };
        
        // Clan descriptions
        const clanDescriptions = {
            ventrue: "The Blue Bloods, rulers and leaders who command respect through authority and tradition. You are drawn to power, leadership, and the responsibility that comes with ruling over others.",
            tremere: "The Warlocks, masters of blood magic and ancient secrets. You are scholarly, mysterious, and drawn to the arcane arts and hidden knowledge.",
            brujah: "The Rabble, passionate rebels who fight for their beliefs. You are driven by strong emotions, justice, and the desire to challenge the status quo.",
            nosferatu: "The Sewer Rats, masters of information and hidden in the shadows. You are resourceful, secretive, and understand that knowledge is the true power.",
            malkavian: "The Lunatics, touched by madness but gifted with insight. You see the world differently, often speaking in riddles but possessing deep wisdom.",
            toreador: "The Degenerates, artists and aesthetes who find beauty in all things. You are passionate about art, beauty, and the finer things in unlife.",
            gangrel: "The Outlanders, wild and untamed creatures of the night. You are independent, primal, and connected to the natural world and animal instincts."
        };
        
        // Popup state
        let popupVisible = false;
        let adminDebugVisible = false;
        let isAdmin = false;
        
        // Initialize questionnaire
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Random Questionnaire loaded with', questionsData.length, 'questions');
            checkAdminStatus();
            setupEventListeners();
            updateProgress();
            updateNavigationButtons();
            initializeTrackingPopup();
            
            // Animate first question category
            setTimeout(() => {
                const firstCategory = document.querySelector('.question-section.active .question-category');
                if (firstCategory) {
                    firstCategory.classList.add('animate-in');
                }
            }, 300);
        });
        
        function setupEventListeners() {
            // Radio button changes
            const radioButtons = document.querySelectorAll('input[type="radio"]');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', handleAnswerChange);
            });
            
            // Next button
            const nextBtn = document.getElementById('next-btn');
            if (nextBtn) {
                nextBtn.addEventListener('click', nextQuestion);
            }
            
            // Submit button
            const submitBtn = document.getElementById('submit-btn');
            if (submitBtn) {
                submitBtn.addEventListener('click', submitQuestionnaire);
            }
            
            // Tracking popup controls
            const trackingToggle = document.getElementById('tracking-toggle');
            const trackingClose = document.getElementById('tracking-close');
            
            if (trackingToggle) {
                trackingToggle.addEventListener('click', toggleTrackingPopup);
            }
            
            if (trackingClose) {
                trackingClose.addEventListener('click', hideTrackingPopup);
            }
            
            // Admin debug popup controls
            const adminDebugToggle = document.getElementById('admin-debug-toggle');
            const adminDebugClose = document.getElementById('admin-debug-close');
            
            if (adminDebugToggle) {
                adminDebugToggle.addEventListener('click', toggleAdminDebugPopup);
            }
            
            if (adminDebugClose) {
                adminDebugClose.addEventListener('click', hideAdminDebugPopup);
            }
        }
        
        function handleAnswerChange(event) {
            console.log('Answer selected:', event.target.value);
            
            // Get question data and update clan tracking
            const questionNumber = parseInt(event.target.name.split('_')[1]);
            const answerValue = parseInt(event.target.value);
            updateClanTracking(questionNumber, answerValue);
            
            // Update admin debug if visible
            if (isAdmin && adminDebugVisible) {
                updateAdminDebugDisplay();
            }
            
            // Enable next button
            const nextBtn = document.getElementById('next-btn');
            if (nextBtn) {
                nextBtn.disabled = false;
            }
        }
        
        function nextQuestion() {
            // Fade out current question
            const currentSection = document.querySelector(`[data-question="${currentQuestion}"]`);
            if (currentSection) {
                currentSection.classList.add('fade-out');
                
                // Wait for fade out, then move to next question
                setTimeout(() => {
                    currentSection.classList.remove('active', 'fade-out');
                    
                    // Move to next question
                    currentQuestion++;
                    
                    if (currentQuestion <= totalQuestions) {
                        const nextSection = document.querySelector(`[data-question="${currentQuestion}"]`);
                        if (nextSection) {
                            nextSection.classList.add('active');
                            
                            // Animate category entry with delay
                            const categoryElement = nextSection.querySelector('.question-category');
                            if (categoryElement) {
                                setTimeout(() => {
                                    categoryElement.classList.add('animate-in');
                                }, 200); // Small delay for cinematic effect
                            }
                            
                            updateProgress();
                            updateNavigationButtons();
                        }
                    } else {
                        console.log('Questionnaire complete!');
                        // TODO: Handle completion
                    }
                }, 500); // Wait for fade transition
            }
        }
        
        function submitQuestionnaire(event) {
            event.preventDefault();
            console.log('Questionnaire submitted!');
            
            // Fade out the questionnaire form
            const questionnaireForm = document.getElementById('questionnaire-form');
            const progressSection = document.querySelector('.progress-section');
            
            if (questionnaireForm) {
                questionnaireForm.style.transition = 'opacity 0.8s ease-in-out';
                questionnaireForm.style.opacity = '0';
                
                // After fade out, hide form and show results
                setTimeout(() => {
                    questionnaireForm.style.display = 'none';
                    if (progressSection) progressSection.style.display = 'none';
                    
                    // Calculate and display results
                    showResults();
                }, 800);
            }
        }
        
        function updateProgress() {
            const progressFill = document.getElementById('progress-fill');
            const currentQuestionSpan = document.getElementById('current-question');
            
            if (progressFill) {
                const progressPercent = (currentQuestion / totalQuestions) * 100;
                progressFill.style.width = `${progressPercent}%`;
            }
            
            if (currentQuestionSpan) {
                currentQuestionSpan.textContent = currentQuestion;
            }
        }
        
        function updateNavigationButtons() {
            const nextBtn = document.getElementById('next-btn');
            const submitBtn = document.getElementById('submit-btn');
            
            if (currentQuestion === totalQuestions) {
                if (nextBtn) nextBtn.style.display = 'none';
                if (submitBtn) submitBtn.style.display = 'inline-block';
            } else {
                if (nextBtn) nextBtn.style.display = 'inline-block';
                if (submitBtn) submitBtn.style.display = 'none';
            }
            
            // Disable next button until answer is selected
            if (nextBtn) {
                nextBtn.disabled = true;
            }
        }
        
        // Clan tracking functions
        function updateClanTracking(questionNumber, answerValue) {
            // Find the question data
            const questionData = questionsData[questionNumber - 1];
            if (!questionData) {
                console.log('No question data found for question', questionNumber);
                return;
            }
            
            // Get clan weights for this answer (database fields are clanWeight1, clanWeight2, etc.)
            const clanWeightsField = `clanWeight${answerValue}`;
            const clanWeights = questionData[clanWeightsField];
            
            console.log('Looking for field:', clanWeightsField);
            console.log('Question data:', questionData);
            console.log('Clan weights found:', clanWeights);
            
            if (!clanWeights) {
                console.log('No clan weights found for field:', clanWeightsField);
                return;
            }
            
            try {
                // Parse clan weights (comma-separated format: "tremere:3,nosferatu:2,malkavian:2")
                const weights = {};
                const weightPairs = clanWeights.split(',');
                
                weightPairs.forEach(pair => {
                    const [clan, points] = pair.split(':');
                    if (clan && points) {
                        weights[clan.trim()] = parseInt(points.trim());
                    }
                });
                
                console.log('Parsed weights:', weights);
                
                // Update clan tracking
                Object.keys(weights).forEach(clan => {
                    if (clanTracking.hasOwnProperty(clan)) {
                        clanTracking[clan] += weights[clan];
                    }
                });
                
                console.log('Clan tracking updated:', clanTracking);
                updateTrackingDisplay();
                
            } catch (e) {
                console.log('Error parsing clan weights:', e);
                console.log('Raw clan weights:', clanWeights);
            }
        }
        
        function updateTrackingDisplay() {
            const trackingContent = document.getElementById('tracking-content');
            if (!trackingContent) {
                console.log('tracking-content element not found');
                return;
            }
            
            let html = '';
            Object.keys(clanTracking).forEach(clan => {
                html += `
                    <div class="clan-score">
                        <span class="clan-name">${clan}</span>
                        <span class="clan-points">${clanTracking[clan]}</span>
                    </div>
                `;
            });
            
            trackingContent.innerHTML = html;
            console.log('Tracking display updated with:', html);
        }
        
        function initializeTrackingPopup() {
            updateTrackingDisplay();
        }
        
        function toggleTrackingPopup() {
            const popup = document.getElementById('tracking-popup');
            const toggle = document.getElementById('tracking-toggle');
            
            console.log('toggleTrackingPopup called');
            console.log('popup element:', popup);
            console.log('toggle element:', toggle);
            
            if (!popup || !toggle) {
                console.log('Missing popup or toggle element');
                return;
            }
            
            popupVisible = !popupVisible;
            console.log('popupVisible is now:', popupVisible);
            
            if (popupVisible) {
                popup.style.display = 'block';
                toggle.textContent = 'Hide Clan Scores';
                console.log('Showing popup');
            } else {
                popup.style.display = 'none';
                toggle.textContent = 'Show Clan Scores';
                console.log('Hiding popup');
            }
        }
        
        function hideTrackingPopup() {
            const popup = document.getElementById('tracking-popup');
            const toggle = document.getElementById('tracking-toggle');
            
            if (!popup || !toggle) return;
            
            popupVisible = false;
            popup.style.display = 'none';
            toggle.textContent = 'Show Clan Scores';
        }
        
        // Admin debug functions
        function checkAdminStatus() {
            // Simple admin check - in production, this would be server-side
            const urlParams = new URLSearchParams(window.location.search);
            const adminParam = urlParams.get('admin');
            
            // Check for admin parameter or specific username
            if (adminParam === 'true' || adminParam === '1') {
                isAdmin = true;
            }
            
            // Also check if username contains 'admin' (for testing)
            const usernameElement = document.querySelector('.username');
            if (usernameElement && usernameElement.textContent.toLowerCase().includes('admin')) {
                isAdmin = true;
            }
            
            // Show admin debug button if admin
            if (isAdmin) {
                const adminDebugToggle = document.getElementById('admin-debug-toggle');
                if (adminDebugToggle) {
                    adminDebugToggle.style.display = 'block';
                }
            }
            
            console.log('Admin status:', isAdmin);
        }
        
        function updateAdminDebugDisplay() {
            const debugContent = document.getElementById('admin-debug-content');
            if (!debugContent) return;
            
            const debugInfo = {
                currentQuestion: currentQuestion,
                totalQuestions: totalQuestions,
                clanTracking: clanTracking,
                questionsData: questionsData,
                timestamp: new Date().toLocaleTimeString()
            };
            
            debugContent.innerHTML = `
                <div><strong>Current Question:</strong> ${currentQuestion}/${totalQuestions}</div>
                <div><strong>Timestamp:</strong> ${debugInfo.timestamp}</div>
                <div><strong>Clan Tracking Object:</strong></div>
                <div class="admin-debug-json">${JSON.stringify(clanTracking, null, 2)}</div>
                <div><strong>Questions Data (First Question):</strong></div>
                <div class="admin-debug-json">${JSON.stringify(questionsData[0] || {}, null, 2)}</div>
            `;
        }
        
        function toggleAdminDebugPopup() {
            const popup = document.getElementById('admin-debug-popup');
            const toggle = document.getElementById('admin-debug-toggle');
            
            if (!popup || !toggle) return;
            
            adminDebugVisible = !adminDebugVisible;
            
            if (adminDebugVisible) {
                popup.style.display = 'block';
                toggle.textContent = 'Hide Debug';
                updateAdminDebugDisplay();
            } else {
                popup.style.display = 'none';
                toggle.textContent = 'Admin Debug';
            }
        }
        
        function hideAdminDebugPopup() {
            const popup = document.getElementById('admin-debug-popup');
            const toggle = document.getElementById('admin-debug-toggle');
            
            if (!popup || !toggle) return;
            
            adminDebugVisible = false;
            popup.style.display = 'none';
            toggle.textContent = 'Admin Debug';
        }
        
        // Results functions
        function showResults() {
            const resultsSection = document.getElementById('results-section');
            if (!resultsSection) return;
            
            // Determine winning clan
            const winningClan = determineWinningClan();
            console.log('Winning clan:', winningClan);
            
            // Display results
            displayClanResult(winningClan);
            displayAllClanScores();
            
            // Show results section with fade in
            resultsSection.style.display = 'block';
            setTimeout(() => {
                resultsSection.classList.add('active');
            }, 100);
            
            // Setup results action buttons
            setupResultsActions();
        }
        
        function determineWinningClan() {
            let maxScore = -1;
            let winningClan = 'ventrue'; // default fallback
            
            Object.keys(clanTracking).forEach(clan => {
                if (clanTracking[clan] > maxScore) {
                    maxScore = clanTracking[clan];
                    winningClan = clan;
                }
            });
            
            return winningClan;
        }
        
        function displayClanResult(clan) {
            const clanLogo = document.getElementById('clan-logo');
            const clanName = document.getElementById('clan-name');
            const clanDescription = document.getElementById('clan-description');
            
            if (clanLogo) {
                clanLogo.src = `svgs/LogoClan${capitalizeFirst(clan)}.webp`;
                clanLogo.alt = `${capitalizeFirst(clan)} Clan Logo`;
            }
            
            if (clanName) {
                clanName.textContent = capitalizeFirst(clan);
            }
            
            if (clanDescription) {
                clanDescription.textContent = clanDescriptions[clan] || 'A mysterious clan with ancient secrets.';
            }
        }
        
        function displayAllClanScores() {
            const allClanScores = document.getElementById('all-clan-scores');
            if (!allClanScores) return;
            
            // Sort clans by score (highest first)
            const sortedClans = Object.keys(clanTracking).sort((a, b) => clanTracking[b] - clanTracking[a]);
            
            let html = '';
            sortedClans.forEach(clan => {
                const score = clanTracking[clan];
                html += `
                    <div class="clan-score">
                        <span class="clan-name">${capitalizeFirst(clan)}</span>
                        <span class="clan-points">${score}</span>
                    </div>
                `;
            });
            
            allClanScores.innerHTML = html;
        }
        
        function setupResultsActions() {
            const retakeBtn = document.getElementById('retake-btn');
            const createCharacterBtn = document.getElementById('create-character-btn');
            
            if (retakeBtn) {
                retakeBtn.addEventListener('click', retakeQuestionnaire);
            }
            
            if (createCharacterBtn) {
                createCharacterBtn.addEventListener('click', createCharacter);
            }
        }
        
        function retakeQuestionnaire() {
            // Reset everything and reload the page
            window.location.reload();
        }
        
        function createCharacter() {
            // Redirect to character creation with clan data
            const winningClan = determineWinningClan();
            const clanData = encodeURIComponent(JSON.stringify({
                clan: winningClan,
                scores: clanTracking
            }));
            
            // For now, just redirect to character sheet
            // In the future, this could pass the clan data
            window.location.href = 'character_sheet.php';
        }
        
        function capitalizeFirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
    </script>
</body>
</html>
