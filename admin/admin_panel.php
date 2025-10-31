<?php
/**
 * Admin Panel - Character Management
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('LOTN_VERSION', '0.9.0');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../includes/connect.php';
include __DIR__ . '/../includes/header.php';
?>

<div class="admin-panel-container">
    <h1 class="panel-title">🔧 Character Management</h1>
    <p class="panel-subtitle">Manage all characters across the chronicle</p>
    
    <!-- Admin Navigation -->
    <div class="admin-nav">
        <a href="admin_panel.php" class="nav-btn active">👥 Characters</a>
        <a href="admin_sire_childe.php" class="nav-btn">🧛 Sire/Childe</a>
        <a href="admin_equipment.php" class="nav-btn">⚔️ Equipment</a>
        <a href="admin_locations.php" class="nav-btn">📍 Locations</a>
        <a href="questionnaire_admin.php" class="nav-btn">📝 Questionnaire</a>
        <a href="admin_npc_briefing.php" class="nav-btn">📋 NPC Briefing</a>
    </div>
    
    <!-- Character Statistics -->
    <div class="character-stats">
    <?php
        $stats_query = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN player_name = 'NPC' THEN 1 ELSE 0 END) as npcs,
            SUM(CASE WHEN player_name IS NOT NULL AND player_name != '' AND player_name != 'NPC' THEN 1 ELSE 0 END) as pcs
            FROM characters";
        $stats_result = mysqli_query($conn, $stats_query);
        
        if ($stats_result) {
            $stats = mysqli_fetch_assoc($stats_result);
        } else {
            $stats = ['total' => 0, 'npcs' => 0, 'pcs' => 0];
            echo "<p style='color: red;'>Stats query error: " . mysqli_error($conn) . "</p>";
        }
        ?>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['total'] ?? 0; ?></span>
            <span class="stat-label">Total</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['pcs'] ?? 0; ?></span>
            <span class="stat-label">PCs</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['npcs'] ?? 0; ?></span>
            <span class="stat-label">NPCs</span>
        </div>
    </div>

    <!-- Questionnaire Statistics -->
    <div class="questionnaire-stats">
        <?php
        // Get questionnaire statistics
        $questionnaire_query = "SELECT COUNT(*) as total_questions FROM questionnaire_questions";
        $questionnaire_result = mysqli_query($conn, $questionnaire_query);
        $questionnaire_count = $questionnaire_result ? mysqli_fetch_assoc($questionnaire_result)['total_questions'] : 0;
        ?>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $questionnaire_count; ?></span>
            <span class="stat-label">Questions</span>
        </div>
        <div class="stat-mini">
            <a href="questionnaire_admin.php" class="stat-link">📝 Manage</a>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="filter-controls">
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All Characters</button>
            <button class="filter-btn" data-filter="pcs">PCs Only</button>
            <button class="filter-btn" data-filter="npcs">NPCs Only</button>
        </div>
        <div class="clan-filter">
            <label for="clanFilter">Sort by Clan:</label>
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
    <div class="character-table-wrapper table-responsive">
        <table class="character-table" id="characterTable">
            <thead>
                <tr>
                    <th data-sort="id">ID <span class="sort-icon">⇅</span></th>
                    <th data-sort="character_name">Name <span class="sort-icon">⇅</span></th>
                    <th data-sort="player_name">Player <span class="sort-icon">⇅</span></th>
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
                               ORDER BY c.id DESC";
                $char_result = mysqli_query($conn, $char_query);
                
                if (!$char_result) {
                    echo "<tr><td colspan='8'>Query Error: " . mysqli_error($conn) . "</td></tr>";
                } elseif (mysqli_num_rows($char_result) > 0) {
                    while ($char = mysqli_fetch_assoc($char_result)) {
                        $is_npc = ($char['player_name'] === 'NPC');
                ?>
                    <tr class="character-row" data-type="<?php echo $is_npc ? 'npc' : 'pc'; ?>" 
                        data-name="<?php echo htmlspecialchars($char['character_name']); ?>"
                        data-clan="<?php echo htmlspecialchars($char['clan'] ?? 'Unknown'); ?>">
                        <td><?php echo $char['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($char['character_name']); ?></strong></td>
                        <td>
                            <?php if ($is_npc): ?>
                                <span class="badge-npc">NPC</span>
                            <?php else: ?>
                                <?php echo htmlspecialchars($char['player_name']); ?>
                            <?php endif; ?>
                        </td>
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
                            <button class="action-btn view-btn" 
                                    data-id="<?php echo $char['id']; ?>"
                                    title="View Character">👁️</button>
                            <a href="../lotn_char_create.php?id=<?php echo $char['id']; ?>" 
                               class="action-btn edit-btn" 
                               title="Edit Character">✏️</a>
                            <button class="action-btn delete-btn" 
                                    data-id="<?php echo $char['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($char['character_name']); ?>"
                                    data-status="<?php echo $char['status'] ?? 'draft'; ?>"
                                    title="Delete Character">🗑️</button>
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='8' class='empty-state'>No characters found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <div class="pagination-controls" id="paginationControls">
        <div class="pagination-info">
            <span id="paginationInfo">Showing 1-26 of 26 characters</span>
        </div>
        <div class="pagination-buttons" id="paginationButtons">
            <!-- Buttons will be generated by JavaScript -->
        </div>
    </div>
</div>

<!-- View Character Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content large-modal">
        <div class="modal-header-section">
            <h2 class="modal-title">📄 <span id="viewCharacterName">Character Details</span></h2>
            <!-- View Mode Toggle -->
            <div class="view-mode-toggle">
                <button class="mode-btn active" onclick="setViewMode('compact', event)">Compact</button>
                <button class="mode-btn" onclick="setViewMode('full', event)">Full Details</button>
            </div>
            <button class="modal-close" onclick="closeViewModal()">×</button>
        </div>
        
        <!-- Character Header - Two Column Layout -->
        <div id="characterHeader" class="character-header-section">
            <!-- Content will be populated by JavaScript -->
        </div>
        
        <!-- Character Details Content -->
        <div id="viewCharacterContent" class="view-content">
            Loading...
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h2 class="modal-title">⚠️ Confirm Deletion</h2>
        <p class="modal-message">Delete character:</p>
        <p class="modal-character-name" id="deleteCharacterName"></p>
        <p class="modal-warning" id="deleteWarning" style="display:none;">
            ⚠️ <strong>Finalized character</strong> - all data will be lost!
        </p>
        <div class="modal-actions">
            <button class="modal-btn cancel-btn" onclick="closeDeleteModal()">Cancel</button>
            <button class="modal-btn confirm-btn" id="confirmDeleteBtn">Delete</button>
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
.filter-buttons { display: flex; gap: 10px; }
.filter-btn { padding: 10px 20px; background: rgba(139, 0, 0, 0.2); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 5px; color: #b8a090; font-family: var(--font-body), 'Source Serif Pro', serif; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
.filter-btn:hover { background: rgba(139, 0, 0, 0.3); border-color: #8B0000; color: #f5e6d3; }
.filter-btn.active { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); border-color: #b30000; color: #f5e6d3; }

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

.badge-npc { background: #4a1a6b; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-draft { background: #8B6508; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-finalized { background: #1a6b3a; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-active { background: #0d7a4a; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-dead { background: #3a3a3a; color: #999; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-missing { background: #5a4a2a; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }

.actions { display: flex; gap: 8px; justify-content: center; }
.action-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 4px; text-decoration: none; font-size: 1.1em; cursor: pointer; background: rgba(139, 0, 0, 0.2); border: 1px solid rgba(139, 0, 0, 0.4); transition: all 0.2s; }
.action-btn:hover { background: rgba(139, 0, 0, 0.4); transform: scale(1.1); }
.view-btn { background: rgba(0, 100, 200, 0.2); border-color: rgba(0, 100, 200, 0.4); }
.view-btn:hover { background: rgba(0, 100, 200, 0.4); }
.edit-btn { background: rgba(139, 100, 0, 0.2); border-color: rgba(139, 100, 0, 0.4); }
.edit-btn:hover { background: rgba(139, 100, 0, 0.4); }
.delete-btn { background: rgba(139, 0, 0, 0.2); border-color: rgba(139, 0, 0, 0.4); }
.empty-state { text-align: center; padding: 40px; color: #b8a090; font-style: italic; }

.modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); align-items: center; justify-content: center; }
.modal.active { display: flex; }
.modal-content { background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%); border: 3px solid #8B0000; border-radius: 10px; padding: 30px; max-width: 500px; position: relative; }
.modal-content.large-modal { max-width: 900px; max-height: 90vh; overflow-y: auto; }
.modal-content.large-modal.compact-mode { 
    max-height: 95vh; 
    overflow-y: hidden; 
    display: flex;
    flex-direction: column;
}

.modal-content.large-modal.compact-mode .view-content { 
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}
.modal-header-section { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 20px; 
    padding-bottom: 15px; 
    border-bottom: 2px solid rgba(139, 0, 0, 0.3); 
    gap: 15px;
}

.modal-title { 
    font-family: var(--font-brand), 'IM Fell English', serif; 
    color: #f5e6d3; 
    font-size: 2em; 
    margin: 0; 
    flex: 1;
}

.view-mode-toggle { 
    display: flex; 
    gap: 8px; 
    justify-content: center; 
    margin: 0;
}

.modal-close { 
    background: rgba(139, 0, 0, 0.3); 
    border: 1px solid #8B0000; 
    border-radius: 50%; 
    width: 35px; 
    height: 35px; 
    font-size: 1.5em; 
    color: #f5e6d3; 
    cursor: pointer; 
    transition: all 0.2s; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    flex-shrink: 0;
}

.modal-close:hover { background: rgba(139, 0, 0, 0.6); transform: scale(1.1); }
.mode-btn { padding: 8px 20px; background: rgba(139, 0, 0, 0.2); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 5px; color: #b8a090; font-family: var(--font-body), 'Source Serif Pro', serif; cursor: pointer; transition: all 0.3s; }
.mode-btn:hover { background: rgba(139, 0, 0, 0.3); border-color: #8B0000; color: #f5e6d3; }
.mode-btn.active { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); border-color: #b30000; color: #f5e6d3; }

/* Character Header Section - Two Column Layout */
.character-header-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    margin-bottom: 0;
    padding: 25px;
    background: linear-gradient(135deg, rgba(42, 21, 21, 0.6) 0%, rgba(26, 15, 15, 0.6) 100%);
    border: 2px solid rgba(139, 0, 0, 0.3);
    border-radius: 8px;
}

.compact-mode .character-header-section {
    gap: 20px;
    margin-bottom: 0;
    padding: 20px;
}

.compact-mode .character-image-wrapper {
    max-width: 320px;
    height: 320px;
}

.compact-mode .character-image-wrapper img {
    width: 280px;
    height: 280px;
    padding: 20px;
}

.character-info-column {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.character-info-row {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.character-info-label {
    font-family: var(--font-title), 'Libre Baskerville', serif;
    font-size: 0.85em;
    color: #b8a090;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.character-info-value {
    font-family: var(--font-body), 'Source Serif Pro', serif;
    font-size: 1.1em;
    color: #f5e6d3;
    font-weight: 500;
}

.character-image-column {
    display: flex;
    align-items: center;
    justify-content: center;
}

.character-image-wrapper {
    width: 100%;
    max-width: 400px;
    height: 400px;
    background: radial-gradient(circle at center, #a00000, #8b0000, #600000);
    border: 3px solid #c9a96e;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 16px rgba(0,0,0,0.8), inset 0 4px 8px rgba(0,0,0,0.6);
    margin: 0 auto;
}

.character-image-wrapper img {
    width: 350px;
    height: 350px;
    padding: 25px;
    object-fit: fill;
    object-position: center;
    border: none !important;
    filter: drop-shadow(0 8px 16px rgba(0,0,0,0.8));
}

.character-image-placeholder {
    color: #888;
    font-family: var(--font-body), 'Source Serif Pro', serif;
    font-size: 0.9em;
    text-align: center;
    padding: 20px;
}

.view-content { 
    color: #d4c4b0; 
    font-family: var(--font-body), 'Source Serif Pro', serif; 
    line-height: 1.6; 
    margin-top: 20px;
}

.compact-mode .view-content {
    margin-top: 10px;
}

.compact-mode .view-content h3 {
    margin-top: 15px;
    margin-bottom: 8px;
    font-size: 1.1em;
    padding-bottom: 5px;
}

.compact-mode .view-content p {
    margin: 6px 0;
    font-size: 0.95em;
}

.view-content h3 { 
    color: #f5e6d3; 
    font-family: var(--font-title), 'Libre Baskerville', serif; 
    margin-top: 25px; 
    margin-bottom: 12px; 
    border-bottom: 2px solid rgba(139, 0, 0, 0.4); 
    padding-bottom: 8px;
    font-size: 1.3em;
}

.view-content p { margin: 10px 0; }

.view-content strong { color: #b8a090; }

/* Bootstrap grid used instead - custom styles removed */

.view-content h4 {
    color: #d4c4b0;
    font-family: var(--font-title), 'Libre Baskerville', serif;
    font-size: 1.1em;
    margin-top: 15px;
    margin-bottom: 8px;
    border-bottom: 1px solid rgba(139, 0, 0, 0.3);
    padding-bottom: 5px;
}

.view-content .text-content {
    background: rgba(26, 15, 15, 0.4);
    border: 1px solid rgba(139, 0, 0, 0.3);
    border-radius: 5px;
    padding: 15px;
    margin-top: 10px;
    line-height: 1.7;
    white-space: pre-wrap;
    font-family: var(--font-body), 'Source Serif Pro', serif;
    color: #d4c4b0;
}

.view-content .merit-flaw-item {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid rgba(139, 0, 0, 0.2);
}

.view-content .merit-flaw-item:last-child {
    border-bottom: none;
}

.view-content .item-category {
    display: inline-block;
    margin-left: 10px;
    font-size: 0.85em;
    color: #b8a090;
    font-style: italic;
}

.view-content .item-description {
    margin-top: 8px;
    margin-bottom: 0;
    font-size: 0.9em;
    color: #b8a090;
    line-height: 1.5;
    padding-left: 10px;
    border-left: 2px solid rgba(139, 0, 0, 0.3);
}

.view-content .empty-state {
    color: #8B0000;
    font-style: italic;
}

.view-content .coterie-card,
.view-content .relationship-card {
    background: rgba(26, 15, 15, 0.4);
    border: 1px solid rgba(139, 0, 0, 0.3);
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 15px;
}

.view-content .coterie-card h4,
.view-content .relationship-card h4 {
    color: #f5e6d3;
    margin-top: 0;
    margin-bottom: 10px;
    font-family: var(--font-title), 'Libre Baskerville', serif;
    font-size: 1.1em;
}

.view-content .custom-data-json {
    color: #d4c4b0;
    white-space: pre-wrap;
    font-family: var(--font-body), 'Source Serif Pro', serif;
}

@media (max-width: 768px) {
    .view-content .two-column-section {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}

.view-content .trait-list { 
    display: flex; 
    flex-wrap: wrap; 
    gap: 10px; 
    margin-top: 12px; 
}

.view-content .trait-badge { 
    background: rgba(139, 0, 0, 0.25); 
    border: 1px solid rgba(139, 0, 0, 0.5); 
    padding: 6px 12px; 
    border-radius: 5px; 
    font-size: 0.9em;
    font-family: var(--font-body), 'Source Serif Pro', serif;
    color: #d4c4b0;
    transition: all 0.2s ease;
}

.view-content .trait-badge:hover {
    background: rgba(139, 0, 0, 0.4);
    border-color: rgba(139, 0, 0, 0.7);
    transform: translateY(-1px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .character-header-section {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .character-image-wrapper {
        max-width: 100%;
        height: auto;
        aspect-ratio: 1;
    }
    
    .character-image-wrapper img {
        width: 100%;
        height: auto;
        max-width: 350px;
        max-height: 350px;
    }
    
/* Bootstrap handles responsive columns automatically */
}
.modal-message { font-family: var(--font-body), 'Source Serif Pro', serif; color: #d4c4b0; font-size: 1.1em; margin-bottom: 10px; }
.modal-character-name { font-family: var(--font-title), 'Libre Baskerville', serif; color: #f5e6d3; font-size: 1.4em; text-align: center; margin: 20px 0; font-weight: bold; }
.modal-warning { background: rgba(139, 0, 0, 0.3); border-left: 4px solid #8B0000; padding: 15px; margin: 20px 0; color: #f5e6d3; }
.modal-actions { display: flex; gap: 15px; justify-content: center; margin-top: 25px; }
.modal-btn { padding: 12px 30px; border-radius: 5px; font-family: var(--font-body), 'Source Serif Pro', serif; font-weight: 600; cursor: pointer; border: 2px solid; }
.cancel-btn { background: rgba(100, 100, 100, 0.2); border-color: #666; color: #d4c4b0; }
.cancel-btn:hover { background: rgba(100, 100, 100, 0.4); }
.confirm-btn { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); border-color: #b30000; color: #f5e6d3; }
.confirm-btn:hover { background: linear-gradient(135deg, #b30000 0%, #8B0000 100%); }
</style>

<!-- Include the external JavaScript file for admin panel functionality -->
<script src="../js/admin_panel.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
