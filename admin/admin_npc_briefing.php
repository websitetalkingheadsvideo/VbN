<?php
/**
 * Admin Panel - NPC Agent Briefing
 * Quick reference for playing NPCs in sessions
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('LOTN_VERSION', '0.6.1');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../includes/connect.php';
include __DIR__ . '/../includes/header.php';
?>

<div class="admin-panel-container">
    <h1 class="panel-title">📋 NPC Agent Briefing</h1>
    <p class="panel-subtitle">Quick reference for playing NPCs in sessions</p>
    
    <!-- Admin Navigation -->
    <div class="admin-nav">
        <a href="admin_panel.php" class="nav-btn">👥 Characters</a>
        <a href="admin_sire_childe.php" class="nav-btn">🧛 Sire/Childe</a>
        <a href="admin_equipment.php" class="nav-btn">⚔️ Equipment</a>
        <a href="admin_locations.php" class="nav-btn">📍 Locations</a>
        <a href="questionnaire_admin.php" class="nav-btn">📝 Questionnaire</a>
        <a href="admin_npc_briefing.php" class="nav-btn active">📋 NPC Briefing</a>
    </div>
    
    <!-- NPC Statistics -->
    <div class="character-stats">
    <?php
        $stats_query = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' OR status = 'finalized' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'dead' OR status = 'missing' THEN 1 ELSE 0 END) as retired
            FROM characters
            WHERE player_name = 'NPC'";
        $stats_result = mysqli_query($conn, $stats_query);
        
        if ($stats_result) {
            $stats = mysqli_fetch_assoc($stats_result);
        } else {
            $stats = ['total' => 0, 'active' => 0, 'retired' => 0];
            echo "<p style='color: red;'>Stats query error: " . mysqli_error($conn) . "</p>";
        }
        ?>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['total'] ?? 0; ?></span>
            <span class="stat-label">Total NPCs</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['active'] ?? 0; ?></span>
            <span class="stat-label">Active</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['retired'] ?? 0; ?></span>
            <span class="stat-label">Retired</span>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="filter-controls">
        <div class="clan-filter">
            <label for="clanFilter">Filter by Clan:</label>
            <select id="clanFilter">
                <option value="all">All Clans</option>
                <option value="Assamite">Assamite</option>
                <option value="Brujah">Brujah</option>
                <option value="Caitiff">Caitiff</option>
                <option value="Followers of Set">Followers of Set</option>
                <option value="Gangrel">Gangrel</option>
                <option value="Giovanni">Giovanni</option>
                <option value="Lasombra">Lasombra</option>
                <option value="Malkavian">Malkavian</option>
                <option value="Nosferatu">Nosferatu</option>
                <option value="Ravnos">Ravnos</option>
                <option value="Toreador">Toreador</option>
                <option value="Tremere">Tremere</option>
                <option value="Tzimisce">Tzimisce</option>
                <option value="Ventrue">Ventrue</option>
                <option value="Ghoul">Ghoul</option>
            </select>
        </div>
        <div class="search-box">
            <input type="text" id="characterSearch" placeholder="🔍 Search by name..." />
        </div>
        <div class="page-size-control">
            <label for="pageSize">Per page:</label>
            <select id="pageSize">
                <option value="20" selected>20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    <!-- Character Table -->
    <div class="character-table-wrapper">
        <table class="character-table" id="characterTable">
            <thead>
                <tr>
                    <th data-sort="id">ID <span class="sort-icon">⇅</span></th>
                    <th data-sort="character_name">Name <span class="sort-icon">⇅</span></th>
                    <th data-sort="clan">Clan <span class="sort-icon">⇅</span></th>
                    <th data-sort="generation">Gen <span class="sort-icon">⇅</span></th>
                    <th data-sort="status">Status <span class="sort-icon">⇅</span></th>
                    <th data-sort="created_at">Created <span class="sort-icon">⇅</span></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $char_query = "SELECT c.*, u.username as owner_username
                               FROM characters c 
                               LEFT JOIN users u ON c.user_id = u.id
                               WHERE c.player_name = 'NPC'
                               ORDER BY c.id DESC";
                $char_result = mysqli_query($conn, $char_query);
                
                if (!$char_result) {
                    echo "<tr><td colspan='7'>Query Error: " . mysqli_error($conn) . "</td></tr>";
                } elseif (mysqli_num_rows($char_result) > 0) {
                    while ($char = mysqli_fetch_assoc($char_result)) {
                ?>
                    <tr class="character-row" 
                        data-name="<?php echo htmlspecialchars($char['character_name']); ?>"
                        data-clan="<?php echo htmlspecialchars($char['clan'] ?? 'Unknown'); ?>">
                        <td><?php echo $char['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($char['character_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($char['clan'] ?? 'Unknown'); ?></td>
                        <td><?php echo $char['generation']; ?>th</td>
                        <td>
                            <?php
                            $status = $char['status'] ?? 'draft';
                            $badge_class = 'badge-' . $status;
                            $status_display = ucfirst($status);
                            ?>
                            <span class="<?php echo $badge_class; ?>"><?php echo $status_display; ?></span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($char['created_at'])); ?></td>
                        <td class="actions">
                            <button class="action-btn briefing-btn" 
                                    data-id="<?php echo $char['id']; ?>"
                                    title="View Agent Briefing">📋</button>
                            <button class="action-btn edit-btn" 
                                    data-id="<?php echo $char['id']; ?>"
                                    title="Edit Notes">✏️</button>
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='7' class='empty-state'>No NPCs found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <div class="pagination-controls" id="paginationControls">
        <div class="pagination-info">
            <span id="paginationInfo">Showing NPCs</span>
        </div>
        <div class="pagination-buttons" id="paginationButtons">
            <!-- Buttons will be generated by JavaScript -->
        </div>
    </div>
</div>

<!-- Briefing Modal -->
<div id="briefingModal" class="modal">
    <div class="modal-content large-modal">
        <h2 class="modal-title">📋 Agent Briefing: <span id="briefingCharacterName"></span></h2>
        <button class="modal-close" onclick="closeBriefingModal()">×</button>
        
        <div id="briefingContent" class="briefing-content">
            Loading...
        </div>
        
        <div class="modal-actions">
            <button class="modal-btn cancel-btn" onclick="closeBriefingModal()">Close</button>
        </div>
    </div>
</div>

<!-- Edit Notes Modal -->
<div id="editNotesModal" class="modal">
    <div class="modal-content large-modal">
        <h2 class="modal-title">✏️ Edit Notes: <span id="editCharacterName"></span></h2>
        <button class="modal-close" onclick="closeEditNotesModal()">×</button>
        
        <div class="briefing-content">
            <h3>AGENT NOTES</h3>
            <p style="color: #b8a090; font-size: 0.9em; margin-bottom: 10px;">
                AI-formatted briefing with nature/demeanor, traits, and key information for playing this character.
            </p>
            <textarea id="editAgentNotes" rows="10" style="width: 100%; padding: 10px; background: rgba(26, 15, 15, 0.6); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 5px; color: #f5e6d3; font-family: var(--font-body), 'Source Serif Pro', serif; resize: vertical;"></textarea>
            
            <h3 style="margin-top: 30px;">ACTING NOTES (Post-Session)</h3>
            <p style="color: #b8a090; font-size: 0.9em; margin-bottom: 10px;">
                Your notes after playing this character in a session.
            </p>
            <textarea id="editActingNotes" rows="10" style="width: 100%; padding: 10px; background: rgba(26, 15, 15, 0.6); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 5px; color: #f5e6d3; font-family: var(--font-body), 'Source Serif Pro', serif; resize: vertical;"></textarea>
        </div>
        
        <div class="modal-actions">
            <button class="modal-btn cancel-btn" onclick="closeEditNotesModal()">Cancel</button>
            <button class="modal-btn confirm-btn" id="saveEditNotesBtn" onclick="saveNotesFromEdit()">Save Notes</button>
        </div>
    </div>
</div>

<style>
.admin-panel-container { max-width: 1600px; margin: 0 auto; padding: 30px 20px; }
.panel-title { font-family: var(--font-brand), 'IM Fell English', serif; color: #f5e6d3; font-size: 2.5em; margin-bottom: 10px; }
.panel-subtitle { font-family: var(--font-body), 'Source Serif Pro', serif; color: #b8a090; font-size: 1.1em; font-style: italic; margin-bottom: 30px; }

.admin-nav { display: flex; gap: 10px; margin-bottom: 30px; flex-wrap: wrap; }
.nav-btn { padding: 12px 20px; background: rgba(139, 0, 0, 0.2); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 5px; color: #b8a090; font-family: var(--font-body), 'Source Serif Pro', serif; font-weight: 600; text-decoration: none; transition: all 0.3s ease; }
.nav-btn:hover { background: rgba(139, 0, 0, 0.3); border-color: #8B0000; color: #f5e6d3; }
.nav-btn.active { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); border-color: #b30000; color: #f5e6d3; }

.filter-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 20px; flex-wrap: wrap; }

.search-box { flex: 1; max-width: 400px; }
.search-box input { width: 100%; padding: 10px 15px; background: rgba(26, 15, 15, 0.6); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 5px; color: #f5e6d3; font-family: var(--font-body), 'Source Serif Pro', serif; }
.search-box input:focus { outline: none; border-color: #8B0000; }
.search-box input::placeholder { color: #666; }

.clan-filter { display: flex; align-items: center; gap: 10px; }
.clan-filter label { font-family: var(--font-body), 'Source Serif Pro', serif; color: #b8a090; font-size: 0.95em; }
.clan-filter select { padding: 8px 12px; background: rgba(26, 15, 15, 0.6); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 5px; color: #f5e6d3; font-family: var(--font-body), 'Source Serif Pro', serif; cursor: pointer; min-width: 120px; }
.clan-filter select:focus { outline: none; border-color: #8B0000; }

.page-size-control { display: flex; align-items: center; gap: 10px; }
.page-size-control label { font-family: var(--font-body), 'Source Serif Pro', serif; color: #b8a090; font-size: 0.95em; }
.page-size-control select { padding: 8px 12px; background: rgba(26, 15, 15, 0.6); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 5px; color: #f5e6d3; font-family: var(--font-body), 'Source Serif Pro', serif; cursor: pointer; }
.page-size-control select:focus { outline: none; border-color: #8B0000; }

.pagination-controls { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding: 15px; background: rgba(26, 15, 15, 0.3); border-radius: 5px; }
.pagination-info { font-family: var(--font-body), 'Source Serif Pro', serif; color: #b8a090; }
.pagination-buttons { display: flex; gap: 8px; }
.page-btn { padding: 8px 12px; background: rgba(139, 0, 0, 0.2); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 4px; color: #b8a090; font-family: var(--font-body), 'Source Serif Pro', serif; cursor: pointer; transition: all 0.2s; }
.page-btn:hover { background: rgba(139, 0, 0, 0.3); border-color: #8B0000; color: #f5e6d3; }
.page-btn.active { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); border-color: #b30000; color: #f5e6d3; }

.character-stats { display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; }
.stat-mini { background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%); border: 2px solid #8B0000; border-radius: 5px; padding: 12px 20px; display: flex; flex-direction: column; align-items: center; min-width: 100px; }
.stat-mini .stat-number { font-family: var(--font-brand), 'IM Fell English', serif; font-size: 1.8em; color: #8B0000; font-weight: bold; }
.stat-mini .stat-label { font-family: var(--font-body), 'Source Serif Pro', serif; font-size: 0.85em; color: #b8a090; margin-top: 5px; }

.character-table-wrapper { background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%); border: 2px solid #8B0000; border-radius: 8px; overflow: hidden; }
.character-table { width: 100%; border-collapse: collapse; }
.character-table thead { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); }
.character-table th { padding: 15px 12px; text-align: left; font-family: var(--font-title), 'Libre Baskerville', serif; color: #f5e6d3; font-weight: 700; cursor: pointer; user-select: none; }
.character-table th:hover { background: rgba(179, 0, 0, 0.3); }
.sort-icon { font-size: 0.9em; opacity: 0.4; margin-left: 5px; }
.character-table th.sorted-asc .sort-icon { opacity: 1; }
.character-table th.sorted-asc .sort-icon::before { content: '▲ '; }
.character-table th.sorted-desc .sort-icon { opacity: 1; }
.character-table th.sorted-desc .sort-icon::before { content: '▼ '; }
.character-table tbody tr { border-bottom: 1px solid rgba(139, 0, 0, 0.2); transition: all 0.2s ease; }
.character-table tbody tr:hover { background: rgba(139, 0, 0, 0.15); }
.character-table tbody tr.hidden { display: none; }
.character-table td { padding: 12px; font-family: var(--font-body), 'Source Serif Pro', serif; color: #d4c4b0; }
.character-table td strong { color: #f5e6d3; font-size: 1.05em; }

.badge-draft { background: #8B6508; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-finalized { background: #1a6b3a; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-active { background: #0d7a4a; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-dead { background: #3a3a3a; color: #999; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-missing { background: #5a4a2a; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }

.actions { display: flex; gap: 8px; justify-content: center; }
.action-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 4px; text-decoration: none; font-size: 1.1em; cursor: pointer; background: rgba(139, 0, 0, 0.2); border: 1px solid rgba(139, 0, 0, 0.4); transition: all 0.2s; }
.action-btn:hover { background: rgba(139, 0, 0, 0.4); transform: scale(1.1); }
.briefing-btn { background: rgba(100, 50, 200, 0.2); border-color: rgba(100, 50, 200, 0.4); }
.briefing-btn:hover { background: rgba(100, 50, 200, 0.4); }
.edit-btn { background: rgba(139, 100, 0, 0.2); border-color: rgba(139, 100, 0, 0.4); }
.edit-btn:hover { background: rgba(139, 100, 0, 0.4); }
.empty-state { text-align: center; padding: 40px; color: #b8a090; font-style: italic; }

.modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); align-items: center; justify-content: center; }
.modal.active { display: flex; }
.modal-content { background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%); border: 3px solid #8B0000; border-radius: 10px; padding: 30px; max-width: 900px; max-height: 90vh; overflow-y: auto; position: relative; }
.modal-close { position: absolute; top: 15px; right: 15px; background: rgba(139, 0, 0, 0.3); border: 1px solid #8B0000; border-radius: 50%; width: 35px; height: 35px; font-size: 1.5em; color: #f5e6d3; cursor: pointer; transition: all 0.2s; }
.modal-close:hover { background: rgba(139, 0, 0, 0.6); transform: scale(1.1); }
.view-mode-toggle { display: flex; gap: 10px; justify-content: center; margin-bottom: 20px; }
.mode-btn { padding: 8px 20px; background: rgba(139, 0, 0, 0.2); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 5px; color: #b8a090; font-family: var(--font-body), 'Source Serif Pro', serif; cursor: pointer; transition: all 0.3s; }
.mode-btn:hover { background: rgba(139, 0, 0, 0.3); border-color: #8B0000; color: #f5e6d3; }
.mode-btn.active { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); border-color: #b30000; color: #f5e6d3; }

.briefing-content { color: #d4c4b0; font-family: var(--font-body), 'Source Serif Pro', serif; line-height: 1.6; }
.briefing-content h3 { color: #f5e6d3; font-family: var(--font-title), 'Libre Baskerville', serif; margin-top: 20px; margin-bottom: 10px; border-bottom: 2px solid rgba(139, 0, 0, 0.5); padding-bottom: 5px; font-size: 1.3em; }
.briefing-content p { margin: 8px 0; }
.briefing-content strong { color: #b8a090; }
.briefing-content .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 15px 0; }
.briefing-content .trait-list { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.briefing-content .trait-badge { background: rgba(139, 0, 0, 0.2); border: 1px solid rgba(139, 0, 0, 0.4); padding: 4px 10px; border-radius: 4px; font-size: 0.9em; }
.briefing-content .trait-badge.negative { background: rgba(139, 0, 0, 0.4); border-color: rgba(139, 0, 0, 0.6); }
.briefing-content .ability-list { margin-top: 10px; color: #d4c4b0; }
.briefing-content textarea { width: 100%; min-height: 150px; padding: 10px; background: rgba(26, 15, 15, 0.6); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 5px; color: #f5e6d3; font-family: var(--font-body), 'Source Serif Pro', serif; resize: vertical; }
.briefing-content textarea:focus { outline: none; border-color: #8B0000; }

.modal-title { font-family: var(--font-brand), 'IM Fell English', serif; color: #f5e6d3; font-size: 2em; margin-bottom: 20px; text-align: center; }
.modal-actions { display: flex; gap: 15px; justify-content: center; margin-top: 25px; }
.modal-btn { padding: 12px 30px; border-radius: 5px; font-family: var(--font-body), 'Source Serif Pro', serif; font-weight: 600; cursor: pointer; border: 2px solid; }
.cancel-btn { background: rgba(100, 100, 100, 0.2); border-color: #666; color: #d4c4b0; }
.cancel-btn:hover { background: rgba(100, 100, 100, 0.4); }
.confirm-btn { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); border-color: #b30000; color: #f5e6d3; }
.confirm-btn:hover { background: linear-gradient(135deg, #b30000 0%, #8B0000 100%); }
</style>

<script src="../js/admin_npc_briefing.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

