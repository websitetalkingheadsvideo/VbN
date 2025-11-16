<?php
/**
 * Character Questionnaire (Dynamic) — Valley by Night
 * Loads 20 random questions with defensive schema detection and shared includes.
 */

session_start();

require_once __DIR__ . '/includes/auth_bypass.php';
if (!isset($_SESSION['user_id']) && !isAuthBypassEnabled()) {
    header('Location: login.php');
    exit();
}
if (isAuthBypassEnabled() && !isset($_SESSION['user_id'])) {
    setupBypassSession();
}

include __DIR__ . '/includes/connect.php';

// Detect available columns to support prod/dev schema differences
$columnsResult = db_select($conn, 'SHOW COLUMNS FROM questionnaire_questions');
if ($columnsResult === false) {
    die('Database error: Failed to inspect questionnaire schema');
}
$available = [];
while ($row = mysqli_fetch_assoc($columnsResult)) {
    $available[strtolower($row['Field'])] = true;
}

// Build a resilient SELECT list
$selects = [];

// Always try to include id/ID
if (isset($available['id'])) {
    $selects[] = 'id';
} elseif (isset($available['ID'])) { // case-insensitivity safeguard
    $selects[] = 'ID as id';
}

// Category/subcategory when present
if (isset($available['category'])) {
    $selects[] = 'category';
}
if (isset($available['subcategory'])) {
    $selects[] = 'subcategory';
}

// Question text — prefer `question`, fallback to `question_text`
if (isset($available['question'])) {
    $selects[] = 'question as question';
} elseif (isset($available['question_text'])) {
    $selects[] = 'question_text as question';
}

// Answers (if table stores them)
for ($i = 1; $i <= 4; $i++) {
    $col = 'answer' . $i;
    if (isset($available[$col])) {
        $selects[] = $col;
    }
}

// Clan weights for scoring in JS (comma-delimited strings)
for ($i = 1; $i <= 4; $i++) {
    $col = 'clanweight' . $i; // handle case-insensitive variations
    if (isset($available[$col])) {
        $selects[] = 'clanWeight' . $i;
    } elseif (isset($available['clanWeight' . $i])) {
        $selects[] = 'clanWeight' . $i;
    }
}

// Fallback if question text not found
if (!in_array('question as question', $selects, true) && !in_array('question_text as question', $selects, true)) {
    // Provide at least id and category to avoid total failure; inform user
    die('Database error: Missing question text column (expected `question` or `question_text`)');
}

$sql = 'SELECT ' . implode(', ', $selects) . ' FROM questionnaire_questions ORDER BY RAND() LIMIT 20';
$result = db_select($conn, $sql);
if ($result === false) {
    // Log was already recorded by db_select; show friendly message
    die('Database error: Failed to load questions');
}
$questions = mysqli_fetch_all($result, MYSQLI_ASSOC);
if (empty($questions)) {
    die('No questions found in database.');
}

$extra_css = [
    'css/questionnaire.css'
];
include __DIR__ . '/includes/header.php';
?>
        <!-- Tracking Toggle Button (optional reveal) -->
        <button id="tracking-toggle" class="tracking-toggle nav-btn next-btn">Show Clan Scores</button>

        <!-- Admin Debug Toggle Button (hidden by default; can be enabled via ?admin=1) -->
        <button id="admin-debug-toggle" class="admin-debug-toggle nav-btn next-btn" style="display: none;">Admin Debug</button>

        <!-- aria-live status for async/UI updates -->
        <div id="questionnaire-status" class="visually-hidden" role="status" aria-live="polite"></div>

        <!-- Tracking Popup -->
        <div id="tracking-popup" class="tracking-popup" style="display: none;">
            <div class="tracking-header">
                <h3 class="tracking-title">Clan Tracking</h3>
                <button id="tracking-close" class="tracking-close" aria-label="Close tracking popup">&times;</button>
            </div>
            <div id="tracking-content"><!-- Clan scores populated here --></div>
        </div>

        <!-- Admin Debug Popup -->
        <div id="admin-debug-popup" class="admin-debug-popup" style="display: none;">
            <div class="admin-debug-header">
                <h3 class="admin-debug-title">Admin Debug</h3>
                <button id="admin-debug-close" class="admin-debug-close" aria-label="Close admin debug popup">&times;</button>
            </div>
            <div id="admin-debug-content" class="admin-debug-content"></div>
        </div>

        <div class="questionnaire-container">
            <div class="questionnaire-header">
                <h1 class="questionnaire-title">The Night Creates You</h1>
                <p class="questionnaire-subtitle">Character Creation Questionnaire</p>
                <p class="questionnaire-description">
                    Answer these questions to discover which vampire clan calls to your soul.
                </p>
            </div>

            <form id="questionnaire-form" class="questionnaire-form" novalidate>
                <!-- Progress Indicator -->
                <div class="progress-section" aria-live="polite">
                    <div class="progress-bar"><div class="progress-fill" id="progress-fill"></div></div>
                    <div class="progress-text">
                        <span id="current-question">1</span> of <span id="total-questions">20</span>
                    </div>
                </div>

                <?php $qNum = 1; foreach ($questions as $q): ?>
                <section class="question-section <?php echo $qNum === 1 ? 'active' : ''; ?>" data-question="<?php echo $qNum; ?>" aria-labelledby="q<?php echo $qNum; ?>-title">
                    <h2 id="q<?php echo $qNum; ?>-title" class="question-title">Question <?php echo $qNum; ?></h2>
                    <?php if (!empty($q['category'])): ?>
                    <div class="question-category" data-category="<?php echo htmlspecialchars($q['category']); ?>">
                        <?php echo ucfirst(htmlspecialchars($q['category'])); ?>
                    </div>
                    <?php endif; ?>
                    <p class="question-text"><?php echo htmlspecialchars($q['question'] ?? ''); ?></p>

                    <fieldset class="answer-group form-check">
                        <legend class="visually-hidden">Answer options</legend>
                        <?php for ($i = 1; $i <= 4; $i++): $key = 'answer' . $i; if (!empty($q[$key])): ?>
                            <label class="answer-option form-check-label">
                                <input type="radio" name="question_<?php echo $qNum; ?>" class="form-check-input" value="<?php echo $i; ?>">
                                <span class="answer-text"><?php echo htmlspecialchars($q[$key]); ?></span>
                            </label>
                        <?php endif; endfor; ?>
                    </fieldset>
                </section>
                <?php $qNum++; endforeach; ?>

                <nav class="questionnaire-navigation" aria-label="Questionnaire Navigation">
                    <button type="button" id="next-btn" class="nav-btn next-btn" disabled>Next Question</button>
                    <button type="submit" id="submit-btn" class="nav-btn next-btn" style="display: none;">Complete Questionnaire</button>
                </nav>
            </form>

            <section id="results-section" class="results-section" hidden>
                <div class="results-container">
                    <h1 class="results-title">Your Clan Has Been Revealed</h1>
                    <div class="clan-result">
                        <div class="clan-logo-container">
                            <img id="clan-logo" src="" alt="" class="clan-logo">
                        </div>
                        <h2 id="clan-name" class="clan-name"></h2>
                        <p id="clan-description" class="clan-description"></p>
                        <div class="clan-stats">
                            <h3>Your Clan Scores:</h3>
                            <div id="all-clan-scores" class="all-clan-scores"></div>
                        </div>
                    </div>
                    <div class="results-actions">
                        <button id="retake-btn" class="nav-btn next-btn" type="button">Retake Questionnaire</button>
                        <button id="create-character-btn" class="nav-btn next-btn" type="button">Create Character</button>
                    </div>
                </div>
            </section>
        </div>

        <script>
            // Expose questions to JS for scoring via clanWeight1..4 when present
            const questionsData = <?php echo json_encode($questions); ?>;
        </script>
        <script src="js/questionnaire.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
