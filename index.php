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
                    SUM(CASE WHEN player_name = 'NPC' THEN 1 ELSE 0 END) as npcs,
                    SUM(CASE WHEN player_name != 'NPC' THEN 1 ELSE 0 END) as pcs
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
                    <a href="questionnaire.php" class="gothic-button">Take Quiz</a>
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
                
                <a href="questionnaire.php" class="quiz-character-btn">
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

<!-- Include external dashboard CSS -->
<link rel="stylesheet" href="css/dashboard.css">

<?php
// Include footer
include 'includes/footer.php';
?>

