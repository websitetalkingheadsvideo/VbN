<?php
/**
 * Character Creation Questionnaire - Valley by Night
 * Web-based questionnaire for determining vampire clan
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include 'includes/connect.php';

// Get username from session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// Get version
if (!defined('LOTN_VERSION')) {
    define('LOTN_VERSION', '0.2.1');
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
                            <span id="current-question">1</span> of <span id="total-questions">5</span>
                        </div>
                    </div>

                    <!-- Question 1: Mortal Background -->
                    <div class="question-section active" data-question="1">
                        <h2 class="question-title">Mortal Background</h2>
                        <p class="question-text">What was your life like before becoming immortal?</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="mortal_background" value="professional">
                                <span class="answer-text">Professional role/job</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="mortal_background" value="relationships">
                                <span class="answer-text">Personal relationships</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="mortal_background" value="memories">
                                <span class="answer-text">Key memories or defining moments</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 2: Transition to Vampire -->
                    <div class="question-section" data-question="2">
                        <h2 class="question-title">The Embrace</h2>
                        <p class="question-text">How did you transition to vampire?</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="embrace_type" value="voluntary">
                                <span class="answer-text">Voluntary transformation</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="embrace_type" value="ritualistic">
                                <span class="answer-text">Ritualistic Embrace</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="embrace_type" value="accidental">
                                <span class="answer-text">Accidental discovery</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="embrace_type" value="supernatural">
                                <span class="answer-text">Supernatural encounter</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 3: Personality Traits -->
                    <div class="question-section" data-question="3">
                        <h2 class="question-title">Mental Profile</h2>
                        <p class="question-text">What are your top three personality traits?</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="personality" value="passionate">
                                <span class="answer-text">Passionate</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="personality" value="calculating">
                                <span class="answer-text">Calculating</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="personality" value="impulsive">
                                <span class="answer-text">Impulsive</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="personality" value="compassionate">
                                <span class="answer-text">Compassionate</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="personality" value="sardonic">
                                <span class="answer-text">Sardonic</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="personality" value="pragmatic">
                                <span class="answer-text">Pragmatic</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 4: Supernatural Powers -->
                    <div class="question-section" data-question="4">
                        <h2 class="question-title">Supernatural Potential</h2>
                        <p class="question-text">If supernatural powers could represent your essence, what would they be?</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="supernatural_power" value="strength">
                                <span class="answer-text">Strength/Combat ability</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="supernatural_power" value="manipulation">
                                <span class="answer-text">Social manipulation</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="supernatural_power" value="knowledge">
                                <span class="answer-text">Mystical knowledge</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="supernatural_power" value="survival">
                                <span class="answer-text">Survival skill</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 5: Personal Goal -->
                    <div class="question-section" data-question="5">
                        <h2 class="question-title">Personal Motivation</h2>
                        <p class="question-text">What is your most significant personal goal or motivation?</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="personal_goal" value="survival">
                                <span class="answer-text">Survival</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="personal_goal" value="revenge">
                                <span class="answer-text">Revenge</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="personal_goal" value="knowledge">
                                <span class="answer-text">Knowledge</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="personal_goal" value="redemption">
                                <span class="answer-text">Redemption</span>
                            </label>
                        </div>
                    </div>

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
                        <!-- Clan recommendation will be inserted here -->
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

    <script src="js/questionnaire.js"></script>
</body>
</html>
