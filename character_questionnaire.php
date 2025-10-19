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
    define('LOTN_VERSION', '0.2.5');
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
                            <span id="current-question">1</span> of <span id="total-questions">19</span>
                        </div>
                    </div>


                    <!-- Question 1: Transition to Vampire -->
                    <div class="question-section active" data-question="1">
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

                    <!-- Question 2: Personality Traits -->
                    <div class="question-section" data-question="2">
                        <h2 class="question-title">Mental Profile</h2>
                        <p class="question-text">Select your top three personality traits:</p>
                        <p class="question-subtext">Choose exactly 3 traits that best describe you</p>
                        
                        <div class="answer-group personality-group">
                            <label class="answer-option personality-option">
                                <input type="checkbox" name="personality[]" value="passionate">
                                <span class="answer-text">Passionate</span>
                            </label>
                            <label class="answer-option personality-option">
                                <input type="checkbox" name="personality[]" value="calculating">
                                <span class="answer-text">Calculating</span>
                            </label>
                            <label class="answer-option personality-option">
                                <input type="checkbox" name="personality[]" value="impulsive">
                                <span class="answer-text">Impulsive</span>
                            </label>
                            <label class="answer-option personality-option">
                                <input type="checkbox" name="personality[]" value="compassionate">
                                <span class="answer-text">Compassionate</span>
                            </label>
                            <label class="answer-option personality-option">
                                <input type="checkbox" name="personality[]" value="sardonic">
                                <span class="answer-text">Sardonic</span>
                            </label>
                            <label class="answer-option personality-option">
                                <input type="checkbox" name="personality[]" value="pragmatic">
                                <span class="answer-text">Pragmatic</span>
                            </label>
                        </div>
                        
                        <div class="selection-counter">
                            <span id="personality-count">0</span> of 3 traits selected
                        </div>
                    </div>

                    <!-- Question 3: View of Human Society -->
                    <div class="question-section" data-question="3">
                        <h2 class="question-title">Perspective on Humanity</h2>
                        <p class="question-text">How do you view human society now that you're immortal?</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="human_society_view" value="superior_detached">
                                <span class="answer-text">Superior and detached</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="human_society_view" value="curious_change">
                                <span class="answer-text">Curious about change</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="human_society_view" value="protect_control">
                                <span class="answer-text">Determined to protect or control</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="human_society_view" value="conflicted">
                                <span class="answer-text">Conflicted by your newfound perspective</span>
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

                    <!-- Question 6: View of Other Supernaturals -->
                    <div class="question-section" data-question="6">
                        <h2 class="question-title">Supernatural Relations</h2>
                        <p class="question-text">How do you view other supernatural beings in the world?</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="supernatural_beings_view" value="threats_eliminate">
                                <span class="answer-text">They are threats to be eliminated</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="supernatural_beings_view" value="potential_allies">
                                <span class="answer-text">Potential allies or partners</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="supernatural_beings_view" value="curious_subjects">
                                <span class="answer-text">Curious subjects of study</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="supernatural_beings_view" value="annoying_complications">
                                <span class="answer-text">Annoying complications</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 7: Hidden Secret -->
                    <div class="question-section" data-question="7">
                        <h2 class="question-title">Hidden Truth</h2>
                        <p class="question-text">Do you have a secret that even close companions don't know about?</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="hidden_secret" value="hidden_talent">
                                <span class="answer-text">A hidden talent</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="hidden_secret" value="past_trauma">
                                <span class="answer-text">A past trauma</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="hidden_secret" value="forbidden_desire">
                                <span class="answer-text">A forbidden desire</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="hidden_secret" value="supernatural_weakness">
                                <span class="answer-text">A supernatural weakness</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 8: Greatest Fear -->
                    <div class="question-section" data-question="8">
                        <h2 class="question-title">Immortal Dread</h2>
                        <p class="question-text">What is your greatest fear as an immortal being?</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="greatest_fear" value="solitude">
                                <span class="answer-text">Solitude</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="greatest_fear" value="loss_humanity">
                                <span class="answer-text">Loss of humanity</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="greatest_fear" value="exposure_nature">
                                <span class="answer-text">Exposure of your true nature</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="greatest_fear" value="becoming_powerful">
                                <span class="answer-text">Becoming too powerful</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 9: Scenario - Ventrue Politics -->
                    <div class="question-section" data-question="9">
                        <h2 class="question-title">Political Awakening</h2>
                        <p class="question-text">You've just been Embraced by a powerful Ventrue elder in your hometown. What happens when your first attempt to influence local politics goes wrong?</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="scenario_politics" value="humiliated_underground">
                                <span class="answer-text">You're publicly humiliated, forced underground and learn the harsh lessons of supernatural power</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="scenario_politics" value="true_potential">
                                <span class="answer-text">You realize your true potential as a social manipulator</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="scenario_politics" value="unexpected_allies">
                                <span class="answer-text">You discover unexpected allies within the political landscape</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 10: Scenario - Supernatural Encounter -->
                    <div class="question-section" data-question="10">
                        <h2 class="question-title">Terrifying Revelation</h2>
                        <p class="question-text">Your first supernatural encounter reveals something terrifying about your new immortal nature. How do you process this revelation?</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="scenario_encounter" value="withdrawn_isolation">
                                <span class="answer-text">You become withdrawn, seeking isolation and understanding</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="scenario_encounter" value="embrace_mystery">
                                <span class="answer-text">You decide to embrace the mystery and explore your newfound abilities</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="scenario_encounter" value="seek_others">
                                <span class="answer-text">You seek out other vampires who might explain or validate your experience</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 11: Scenario - First Hunt -->
                    <div class="question-section" data-question="11">
                        <h2 class="question-title">First Hunt</h2>
                        <p class="question-text">During your first hunt in the modern world, you realize that surviving as a vampire requires more than just supernatural strength. How do you respond?</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="scenario_hunt" value="ethical_struggle">
                                <span class="answer-text">You struggle with the ethical implications of taking human life</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="scenario_hunt" value="ruthless_efficient">
                                <span class="answer-text">You become ruthless and efficient</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="scenario_hunt" value="balance_restraint">
                                <span class="answer-text">You seek balance between hunger and restraint</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 12: Scenario - Ancient Secret -->
                    <div class="question-section" data-question="12">
                        <h2 class="question-title">Bloodline Legacy</h2>
                        <p class="question-text">Your clan elder reveals an ancient secret about your specific bloodline. What is your reaction?</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="scenario_bloodline" value="connected_legacy">
                                <span class="answer-text">You feel deeply connected to a hidden legacy</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="scenario_bloodline" value="burden_existence">
                                <span class="answer-text">You view it as another burden of supernatural existence</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="scenario_bloodline" value="protective_knowledge">
                                <span class="answer-text">You become protective of the knowledge, sensing its potential power</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 13: Scenario - Supernatural Threat -->
                    <div class="question-section" data-question="13">
                        <h2 class="question-title">Supernatural Threat</h2>
                        <p class="question-text">When confronted with a supernatural threat that threatens not just humans but other vampires, how do you respond?</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="scenario_threat" value="diplomatic_resolution">
                                <span class="answer-text">You seek diplomatic resolution and compromise</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="scenario_threat" value="direct_combat">
                                <span class="answer-text">You charge directly into combat</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="scenario_threat" value="strategic_analysis">
                                <span class="answer-text">You analyze strategically before taking action</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 14: Workplace Betrayal -->
                    <div class="question-section" data-question="14">
                        <h2 class="question-title">The Workplace Betrayal</h2>
                        <p class="question-text">You've been working on a major project for six months when your colleague, who you considered a friend, presents your ideas as their own to the board of directors. They get the promotion you were promised, and when you confront them privately, they shrug and say, "That's just how business works. You should have been smarter about protecting your ideas." Your boss is impressed with their "innovation" and has no idea it was yours.</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="workplace_betrayal" value="document_build_case">
                                <span class="answer-text">You quietly begin documenting everything and building relationships with higher-ups, planning to expose the theft when it will cause maximum damage to their reputation.</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="workplace_betrayal" value="confront_publicly">
                                <span class="answer-text">You confront them publicly at the next meeting, demanding recognition for your work and making it clear that such betrayal won't be tolerated.</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="workplace_betrayal" value="learn_political_game">
                                <span class="answer-text">You accept the loss but begin studying their methods, learning how to play the political game so this never happens to you again.</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 15: Family Crisis -->
                    <div class="question-section" data-question="15">
                        <h2 class="question-title">The Family Crisis</h2>
                        <p class="question-text">Your sibling has been struggling with addiction for years, and tonight they've stolen money from your parents' savings to buy drugs. Your parents are devastated and don't know what to do. Your sibling is passed out on the couch, and you're the only one who can see the full scope of the problem. Your parents are asking you what they should do.</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="family_crisis" value="take_charge_research">
                                <span class="answer-text">You take charge of the situation, researching treatment options and creating a structured plan to help your sibling while supporting your parents through the process.</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="family_crisis" value="hard_truth_consequences">
                                <span class="answer-text">You tell your parents the hard truth - that your sibling needs to face consequences for their actions and that enabling them will only make things worse.</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="family_crisis" value="protect_parents">
                                <span class="answer-text">You focus on protecting your parents' emotional well-being, handling the immediate crisis while keeping them from the worst details of your sibling's situation.</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 16: Social Dilemma -->
                    <div class="question-section" data-question="16">
                        <h2 class="question-title">The Social Dilemma</h2>
                        <p class="question-text">You're at a party when you witness a popular person in your social circle bullying someone who's clearly uncomfortable but too shy to stand up for themselves. The bully is making cruel jokes at their expense, and everyone is laughing along to avoid being the next target. The victim looks to you with pleading eyes, but speaking up could cost you your social standing.</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="social_dilemma" value="redirect_conversation">
                                <span class="answer-text">You subtly redirect the conversation to a different topic, defusing the situation without directly confronting the bully or drawing attention to yourself.</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="social_dilemma" value="call_out_bully">
                                <span class="answer-text">You call out the bully directly, making it clear that such behavior is unacceptable and that you won't stand by while someone is being mistreated.</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="social_dilemma" value="support_victim_later">
                                <span class="answer-text">You wait until later to approach the victim privately, offering support and advice on how to handle similar situations in the future.</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 17: Moral Test -->
                    <div class="question-section" data-question="17">
                        <h2 class="question-title">The Moral Test</h2>
                        <p class="question-text">You discover that your best friend has been cheating on their partner, who is also your friend. The cheater confides in you, asking you to keep their secret and help them cover their tracks. Meanwhile, their partner is planning to propose next month and has asked you to help plan the engagement. You're caught between loyalty to both friends.</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="moral_test" value="refuse_deception_ultimatum">
                                <span class="answer-text">You refuse to help with the deception and give the cheater an ultimatum - they either come clean or you'll tell their partner yourself.</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="moral_test" value="distance_avoid_involvement">
                                <span class="answer-text">You agree to keep the secret but begin distancing yourself from the situation, avoiding involvement in either the deception or the engagement planning.</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="moral_test" value="strategic_positioning">
                                <span class="answer-text">You use your knowledge strategically, positioning yourself to help both friends while ensuring that when the truth comes out, you're seen as someone who tried to prevent disaster.</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 18: Power Opportunity -->
                    <div class="question-section" data-question="18">
                        <h2 class="question-title">The Power Opportunity</h2>
                        <p class="question-text">You're offered a promotion that would put you in a position of authority over people you've worked alongside for years. The promotion comes with a significant salary increase and more responsibility, but it also means you'll have to make difficult decisions that could affect your former peers' livelihoods. Some of them are counting on you to look out for them.</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="power_opportunity" value="build_relationships_balance">
                                <span class="answer-text">You accept the promotion and immediately begin building relationships with your new peers, learning how to balance the needs of your former colleagues with the demands of your new role.</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="power_opportunity" value="loyalty_uncompromised">
                                <span class="answer-text">You accept the promotion but make it clear to everyone that your loyalty to your friends won't be compromised by your new responsibilities.</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="power_opportunity" value="excel_advocate_strength">
                                <span class="answer-text">You take the promotion and focus on excelling in your new role, knowing that the best way to help your former colleagues is to be successful enough to advocate for them from a position of strength.</span>
                            </label>
                        </div>
                    </div>

                    <!-- Question 19: Life Choice -->
                    <div class="question-section" data-question="19">
                        <h2 class="question-title">The Life Choice</h2>
                        <p class="question-text">You're at a crossroads in your life. You've been offered your dream job in another city, but it would mean leaving behind your aging parents, who need help with daily tasks, and your younger sibling, who's struggling with mental health issues. Your family is depending on you, but this opportunity may never come again. You have to decide tonight.</p>
                        
                        <div class="answer-group">
                            <label class="answer-option">
                                <input type="radio" name="life_choice" value="take_job_support_plan">
                                <span class="answer-text">You take the job but create a detailed plan to support your family from afar, including arranging for professional care and regular visits home.</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="life_choice" value="turn_down_family_loyalty">
                                <span class="answer-text">You turn down the job, choosing to stay and care for your family, knowing that opportunities will come again but family needs are immediate.</span>
                            </label>
                            <label class="answer-option">
                                <input type="radio" name="life_choice" value="negotiate_delayed_start">
                                <span class="answer-text">You negotiate with the company for a delayed start date, giving you time to set up support systems for your family while still pursuing your dreams.</span>
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

    <script src="js/questionnaire.js"></script>
</body>
</html>
