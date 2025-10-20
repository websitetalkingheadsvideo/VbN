<?php
/**
 * Valley by Night - Home Dashboard
 * Main landing page with role-based views
 */

// Define version constant
// Include centralized version management
require_once __DIR__ . '/includes/version.php';

// Start session
session_start();

// Include database connection
require_once 'includes/connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Kindred';
$user_role = $_SESSION['role'] ?? 'player';

// Determine if user is admin/storyteller
$is_admin = ($user_role === 'admin' || $user_role === 'storyteller');

// Debug (remove after testing)
echo "<!-- Debug: user_role = " . htmlspecialchars($user_role) . ", is_admin = " . ($is_admin ? 'true' : 'false') . " -->";

// Chronicle information
$tagline = "On your first night among the Kindred, the Prince dies—and the city of Phoenix bleeds intrigue";
$chronicle_summary = "Phoenix, 1994. On the very night you're introduced to Kindred society, the Prince is murdered, plunging the Camarilla into chaos. As a neonate with everything to prove, you must navigate shifting alliances, enforce the Masquerade, and survive a city where Anarchs, Sabbat, Giovanni, and darker powers all compete for control. The Prince's death is only the beginning.";

// Include header
include 'includes/header.php';
?>

<div class="dashboard-container">
    <?php if ($is_admin): ?>
        <!-- ADMIN/STORYTELLER VIEW -->
        <div class="dashboard-admin">
            <h2 class="section-heading">Storyteller's Domain</h2>
            <p class="welcome-text">Welcome, <?php echo htmlspecialchars($username); ?>. The chronicle awaits your guidance.</p>
            
            <!-- Statistics Panel -->
            <div class="stats-panel">
                <?php
                // Get character statistics
                $stats_query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN player_name = 'ST/NPC' THEN 1 ELSE 0 END) as npcs,
                    SUM(CASE WHEN player_name != 'ST/NPC' THEN 1 ELSE 0 END) as pcs
                    FROM characters";
                $stats_result = mysqli_query($conn, $stats_query);
                $stats = mysqli_fetch_assoc($stats_result);
                ?>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total'] ?? 0; ?></div>
                    <div class="stat-label">Total Characters</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['pcs'] ?? 0; ?></div>
                    <div class="stat-label">Player Characters</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['npcs'] ?? 0; ?></div>
                    <div class="stat-label">NPCs</div>
                </div>
            </div>
            
            <!-- Admin Actions -->
            <div class="action-grid">
                <div class="action-card">
                    <div class="card-icon">✏️</div>
                    <h3>Create Character</h3>
                    <p>Bring a new kindred into the world</p>
                    <a href="lotn_char_create.php" class="gothic-button">Create New</a>
                </div>
                
                <div class="action-card">
                    <div class="card-icon">🏛️</div>
                    <h3>AI Locations Manager</h3>
                    <p>Craft and manage chronicle locations</p>
                    <a href="admin/admin_locations.php" class="gothic-button">Manage Locations</a>
                </div>
                
                <div class="action-card">
                    <div class="card-icon">⚔️</div>
                    <h3>Items Database</h3>
                    <p>Manage equipment and artifacts</p>
                    <a href="admin/admin_equipment.php" class="gothic-button">Manage Items</a>
                </div>
                
                <div class="action-card">
                    <div class="card-icon">📋</div>
                    <h3>Character List</h3>
                    <p>View, edit, and delete characters</p>
                    <a href="admin/admin_panel.php" class="gothic-button">View Characters</a>
                </div>
                
                <div class="action-card">
                    <div class="card-icon">🌟</div>
                    <h3>Clan Discovery Quiz</h3>
                    <p>Test the character creation questionnaire</p>
                    <a href="character_questionnaire.php" class="gothic-button">Take Quiz</a>
                </div>
                
                <div class="action-card disabled">
                    <div class="card-icon">📖</div>
                    <h3>AI Plots Manager</h3>
                    <p>Coming soon: Weave storylines with AI</p>
                    <span class="gothic-button-disabled">Coming Soon</span>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <!-- PLAYER VIEW -->
        <div class="dashboard-player">
            <!-- Chronicle Tagline -->
            <div class="dashboard-hero">
                <div class="chronicle-tagline">
                    <p class="tagline-text"><?php echo htmlspecialchars($tagline); ?></p>
                </div>
            </div>
            
            <!-- Chronicle Summary -->
            <div class="chronicle-summary">
                <div class="gothic-panel">
                    <h2 class="chronicle-title">The Chronicle Begins</h2>
                    <p class="chronicle-text"><?php echo htmlspecialchars($chronicle_summary); ?></p>
                </div>
            </div>
            
            <h2 class="section-heading">Your Domain</h2>
            <p class="welcome-text">Welcome, <?php echo htmlspecialchars($username); ?>. The night is yours to command.</p>
            
            <!-- Player Actions -->
            <div class="player-actions">
                <a href="lotn_char_create.php" class="create-character-btn">
                    <span class="btn-icon">✏️</span>
                    <span class="btn-text">Create New Character</span>
                </a>
                
                <a href="character_questionnaire.php" class="quiz-character-btn">
                    <span class="btn-icon">🌟</span>
                    <span class="btn-text">Discover Your Clan</span>
                </a>
            </div>
            
            <!-- Player's Characters -->
            <div class="character-list">
                <h3 class="list-heading">Your Characters</h3>
                <?php
                // Get player's characters
                $char_query = "SELECT c.*, cl.name as clan_name 
                               FROM characters c 
                               LEFT JOIN clans cl ON c.clan_id = cl.id 
                               WHERE c.user_id = ? 
                               ORDER BY c.status DESC, c.character_name ASC";
                $stmt = mysqli_prepare($conn, $char_query);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $char_result = mysqli_stmt_get_result($stmt);
                
                if (mysqli_num_rows($char_result) > 0):
                    while ($character = mysqli_fetch_assoc($char_result)):
                ?>
                    <div class="character-card">
                        <div class="character-header">
                            <h4 class="character-name">
                                <?php echo htmlspecialchars($character['character_name']); ?>
                                <?php if ($character['status'] == 'draft'): ?>
                                    <span class="badge-draft">DRAFT</span>
                                <?php endif; ?>
                            </h4>
                            <span class="character-clan"><?php echo htmlspecialchars($character['clan_name']); ?></span>
                        </div>
                        <div class="character-details">
                            <p class="character-concept">
                                <strong>Concept:</strong> <?php echo htmlspecialchars($character['concept'] ?? 'Unknown'); ?>
                            </p>
                        </div>
                        <div class="character-actions">
                            <a href="character_sheet.php?id=<?php echo $character['id']; ?>" class="gothic-button-small">
                                View/Edit
                            </a>
                        </div>
                    </div>
                <?php 
                    endwhile;
                else:
                ?>
                    <div class="empty-state">
                        <p>You have not created any characters yet.</p>
                        <p class="empty-hint">Begin your journey by creating your first kindred.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Additional Player Links -->
            <div class="player-links">
                <div class="link-card">
                    <div class="card-icon">💬</div>
                    <h3>Chat Room</h3>
                    <p>Connect with other kindred (Coming Soon)</p>
                    <span class="gothic-button-disabled">Unavailable</span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Dashboard Styles */
.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

/* Hero Section */
.dashboard-hero {
    text-align: center;
    margin-bottom: 40px;
}

.chronicle-tagline {
    background: linear-gradient(135deg, rgba(139, 0, 0, 0.2) 0%, rgba(26, 15, 15, 0.4) 100%);
    border-left: 4px solid #8B0000;
    border-right: 4px solid #8B0000;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
}

.tagline-text {
    font-family: var(--font-brand), 'IM Fell English', serif;
    font-size: 1.8em;
    color: #f5e6d3;
    font-style: italic;
    line-height: 1.4;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
    margin: 0;
}

/* Chronicle Summary */
.chronicle-summary {
    margin-bottom: 50px;
}

.gothic-panel {
    background: linear-gradient(135deg, #1a0f0f 0%, #2a1515 100%);
    border: 2px solid #8B0000;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(139, 0, 0, 0.3);
}

.chronicle-title {
    font-family: var(--font-brand), 'IM Fell English', serif;
    color: #f5e6d3;
    font-size: 2em;
    margin-bottom: 20px;
    text-align: center;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
}

.chronicle-text {
    font-family: var(--font-body), 'Source Serif Pro', serif;
    color: #d4c4b0;
    font-size: 1.1em;
    line-height: 1.8;
    text-align: justify;
}

/* Section Headings */
.section-heading {
    font-family: var(--font-title), 'Libre Baskerville', serif;
    color: #f5e6d3;
    font-size: 2.2em;
    margin-bottom: 15px;
    border-bottom: 2px solid #8B0000;
    padding-bottom: 10px;
}

.welcome-text {
    font-family: var(--font-body), 'Source Serif Pro', serif;
    color: #b8a090;
    font-size: 1.1em;
    margin-bottom: 30px;
    font-style: italic;
}

/* Stats Panel (Admin) */
.stats-panel {
    display: flex;
    gap: 20px;
    margin-bottom: 40px;
    flex-wrap: wrap;
}

.stat-card {
    flex: 1;
    min-width: 200px;
    background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%);
    border: 2px solid #8B0000;
    border-radius: 8px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(139, 0, 0, 0.3);
}

.stat-number {
    font-family: var(--font-brand), 'IM Fell English', serif;
    font-size: 3em;
    color: #8B0000;
    font-weight: bold;
    text-shadow: 0 0 10px rgba(139, 0, 0, 0.5);
}

.stat-label {
    font-family: var(--font-body), 'Source Serif Pro', serif;
    color: #b8a090;
    font-size: 1em;
    margin-top: 10px;
}

/* Action Grid */
.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.action-card {
    background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%);
    border: 2px solid #8B0000;
    border-radius: 8px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(139, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.action-card:not(.disabled):hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 25px rgba(139, 0, 0, 0.5);
    border-color: #b30000;
}

.action-card.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.card-icon {
    font-size: 3em;
    margin-bottom: 15px;
}

.action-card h3 {
    font-family: var(--font-title), 'Libre Baskerville', serif;
    color: #f5e6d3;
    font-size: 1.3em;
    margin-bottom: 10px;
}

.action-card p {
    font-family: var(--font-body), 'Source Serif Pro', serif;
    color: #b8a090;
    font-size: 0.95em;
    margin-bottom: 20px;
    line-height: 1.5;
}

/* Buttons */
.gothic-button,
.gothic-button-small {
    display: inline-block;
    background: linear-gradient(135deg, #8B0000 0%, #600000 100%);
    color: #f5e6d3;
    padding: 12px 25px;
    border: 1px solid #b30000;
    border-radius: 5px;
    text-decoration: none;
    font-family: var(--font-body), 'Source Serif Pro', serif;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(139, 0, 0, 0.3);
}

.gothic-button-small {
    padding: 8px 16px;
    font-size: 0.9em;
}

.gothic-button:hover,
.gothic-button-small:hover {
    background: linear-gradient(135deg, #b30000 0%, #8B0000 100%);
    box-shadow: 0 4px 15px rgba(139, 0, 0, 0.6);
    transform: translateY(-2px);
}

.gothic-button-disabled {
    display: inline-block;
    background: #3a3a3a;
    color: #666;
    padding: 12px 25px;
    border: 1px solid #555;
    border-radius: 5px;
    font-family: var(--font-body), 'Source Serif Pro', serif;
    cursor: not-allowed;
}

/* Player Specific */
.player-actions {
    margin-bottom: 40px;
    text-align: center;
}

.create-character-btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, #8B0000 0%, #600000 100%);
    color: #f5e6d3;
    padding: 15px 35px;
    border: 2px solid #b30000;
    border-radius: 8px;
    text-decoration: none;
    font-family: var(--font-title), 'Libre Baskerville', serif;
    font-size: 1.2em;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(139, 0, 0, 0.4);
}

.create-character-btn:hover {
    background: linear-gradient(135deg, #b30000 0%, #8B0000 100%);
    box-shadow: 0 6px 25px rgba(139, 0, 0, 0.6);
    transform: translateY(-3px);
}

.btn-icon {
    font-size: 1.5em;
}

/* Character List */
.character-list {
    margin-bottom: 40px;
}

.list-heading {
    font-family: var(--font-title), 'Libre Baskerville', serif;
    color: #f5e6d3;
    font-size: 1.6em;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #8B0000;
}

.character-card {
    background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%);
    border: 2px solid #8B0000;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(139, 0, 0, 0.3);
    transition: all 0.3s ease;
}

.character-card:hover {
    border-color: #b30000;
    box-shadow: 0 6px 20px rgba(139, 0, 0, 0.5);
}

.character-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 10px;
}

.character-name {
    font-family: var(--font-title), 'Libre Baskerville', serif;
    color: #f5e6d3;
    font-size: 1.4em;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.badge-draft {
    display: inline-block;
    background: #8B6508;
    color: #f5e6d3;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 0.6em;
    font-weight: bold;
    letter-spacing: 1px;
}

.character-clan {
    font-family: var(--font-body), 'Source Serif Pro', serif;
    color: #b8a090;
    font-size: 1.1em;
    font-style: italic;
}

.character-details {
    margin-bottom: 15px;
}

.character-concept {
    font-family: var(--font-body), 'Source Serif Pro', serif;
    color: #d4c4b0;
    font-size: 1em;
    margin: 0;
}

.character-actions {
    text-align: right;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: rgba(26, 15, 15, 0.3);
    border: 2px dashed rgba(139, 0, 0, 0.3);
    border-radius: 8px;
}

.empty-state p {
    font-family: var(--font-body), 'Source Serif Pro', serif;
    color: #b8a090;
    font-size: 1.1em;
    margin: 10px 0;
}

.empty-hint {
    font-style: italic;
    color: #888;
}

/* Player Links */
.player-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

.link-card {
    background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%);
    border: 2px solid rgba(139, 0, 0, 0.5);
    border-radius: 8px;
    padding: 25px;
    text-align: center;
    opacity: 0.6;
}

/* Responsive Design */
@media (max-width: 768px) {
    .tagline-text {
        font-size: 1.3em;
    }
    
    .section-heading {
        font-size: 1.8em;
    }
    
    .stats-panel {
        flex-direction: column;
    }
    
    .action-grid {
        grid-template-columns: 1fr;
    }
    
    .character-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>

<?php
// Include footer
include 'includes/footer.php';
?>

