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

// Check for testing mode
$testingMode = isset($_GET['test']) && in_array($_GET['test'], ['brujah', 'tremere', 'gangrel']);

if ($testingMode) {
    // Skip questions for testing - go directly to results
    $questions = [];
} else {
    // Get 20 random questions from database
    $result = mysqli_query($conn, "SELECT * FROM questionnaire_questions ORDER BY RAND() LIMIT 20");
    if (!$result) {
        die("Database error: " . mysqli_error($conn));
    }
    $questions = mysqli_fetch_all($result, MYSQLI_ASSOC);
    if (empty($questions)) {
        die("No questions found in database. Please run populate_complete_39_questions.php first.");
    }
}

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

    <!-- Pass questions data to JavaScript -->
    <script>
        const questionsData = <?php echo json_encode($questions); ?>;
        const testingMode = <?php echo $testingMode ? 'true' : 'false'; ?>;
        
        if (testingMode) {
            // Skip questionnaire and go directly to test clan results
            document.addEventListener('DOMContentLoaded', function() {
                const testClan = '<?php echo $_GET['test'] ?? 'brujah'; ?>';
                showTestClanResults(testClan);
            });
        } else if (!questionsData || questionsData.length === 0) {
            throw new Error("No questions data available. Database may be empty or corrupted.");
        }
    </script>
    
    <!-- Include external JavaScript file -->
    <script src="js/questionnaire.js"></script>
</body>
</html>
