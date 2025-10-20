<?php
/**
 * Admin Panel - Character Management
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('LOTN_VERSION', '0.6.0');
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
    </div>
    
    <!-- Character Statistics -->
    <div class="character-stats">
    <?php
        $stats_query = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN player_name = 'ST/NPC' THEN 1 ELSE 0 END) as npcs,
            SUM(CASE WHEN player_name != 'ST/NPC' THEN 1 ELSE 0 END) as pcs
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

    <!-- Filter Controls -->
    <div class="filter-controls">
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All Characters</button>
            <button class="filter-btn" data-filter="pcs">PCs Only</button>
            <button class="filter-btn" data-filter="npcs">NPCs Only</button>
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
                        $is_npc = ($char['player_name'] === 'ST/NPC');
                ?>
                    <tr class="character-row" data-type="<?php echo $is_npc ? 'npc' : 'pc'; ?>" 
                        data-name="<?php echo htmlspecialchars($char['character_name']); ?>">
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
        <h2 class="modal-title">📄 <span id="viewCharacterName">Character Details</span></h2>
        <button class="modal-close" onclick="closeViewModal()">×</button>
        
        <!-- View Mode Toggle -->
        <div class="view-mode-toggle">
            <button class="mode-btn active" onclick="setViewMode('compact')">Compact</button>
            <button class="mode-btn" onclick="setViewMode('full')">Full Details</button>
        </div>
        
        <div id="viewCharacterContent" class="view-content">
            Loading...
        </div>
        <div class="modal-actions">
            <button class="modal-btn cancel-btn" onclick="closeViewModal()">Close</button>
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
.modal-close { position: absolute; top: 15px; right: 15px; background: rgba(139, 0, 0, 0.3); border: 1px solid #8B0000; border-radius: 50%; width: 35px; height: 35px; font-size: 1.5em; color: #f5e6d3; cursor: pointer; transition: all 0.2s; }
.modal-close:hover { background: rgba(139, 0, 0, 0.6); transform: scale(1.1); }
.view-mode-toggle { display: flex; gap: 10px; justify-content: center; margin-bottom: 20px; }
.mode-btn { padding: 8px 20px; background: rgba(139, 0, 0, 0.2); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 5px; color: #b8a090; font-family: var(--font-body), 'Source Serif Pro', serif; cursor: pointer; transition: all 0.3s; }
.mode-btn:hover { background: rgba(139, 0, 0, 0.3); border-color: #8B0000; color: #f5e6d3; }
.mode-btn.active { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); border-color: #b30000; color: #f5e6d3; }

.view-content { color: #d4c4b0; font-family: var(--font-body), 'Source Serif Pro', serif; line-height: 1.6; }
.view-content h3 { color: #f5e6d3; font-family: var(--font-title), 'Libre Baskerville', serif; margin-top: 20px; margin-bottom: 10px; border-bottom: 1px solid rgba(139, 0, 0, 0.3); padding-bottom: 5px; }
.view-content p { margin: 8px 0; }
.view-content strong { color: #b8a090; }
.view-content .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
.view-content .trait-list { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.view-content .trait-badge { background: rgba(139, 0, 0, 0.2); border: 1px solid rgba(139, 0, 0, 0.4); padding: 4px 10px; border-radius: 4px; font-size: 0.9em; }
.modal-title { font-family: var(--font-brand), 'IM Fell English', serif; color: #f5e6d3; font-size: 2em; margin-bottom: 20px; text-align: center; }
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

<script>
// Inline script to avoid path issues
let currentFilter = 'all';
let currentPage = 1;
let pageSize = 20;
let deleteCharacterId = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeAll();
});

function initializeAll() {
    // Filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            applyFilters();
        });
    });
    
    // Search
    document.getElementById('characterSearch').addEventListener('input', applyFilters);
    
    // Page size
    document.getElementById('pageSize').addEventListener('change', function() {
        pageSize = parseInt(this.value);
        currentPage = 1;
        applyFilters();
    });
    
    // View buttons
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            viewCharacter(this.dataset.id);
        });
    });
    
    // Delete buttons
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            deleteCharacterId = this.dataset.id;
            document.getElementById('deleteCharacterName').textContent = this.dataset.name;
            if (this.dataset.status === 'finalized') {
                document.getElementById('deleteWarning').style.display = 'block';
    } else {
                document.getElementById('deleteWarning').style.display = 'none';
            }
            document.getElementById('deleteModal').classList.add('active');
        });
    });
    
    document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);
    
    // Initial pagination
    applyFilters();
}

function applyFilters() {
    const searchTerm = document.getElementById('characterSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.character-row');
    let visibleRows = [];
    
    rows.forEach(row => {
        const type = row.dataset.type;
        const name = row.dataset.name.toLowerCase();
        
        let show = true;
        if (currentFilter === 'pcs' && type !== 'pc') show = false;
        if (currentFilter === 'npcs' && type !== 'npc') show = false;
        if (searchTerm && !name.includes(searchTerm)) show = false;
        
        if (show) {
            visibleRows.push(row);
        }
    });
    
    currentPage = 1;
    updatePagination(visibleRows);
}

function updatePagination(visibleRows) {
    const rows = document.querySelectorAll('.character-row');
    const totalVisible = visibleRows.length;
    const totalPages = Math.ceil(totalVisible / pageSize);
    const startIndex = (currentPage - 1) * pageSize;
    const endIndex = Math.min(startIndex + pageSize, totalVisible);
    
    // Hide all rows
    rows.forEach(row => row.classList.add('hidden'));
    
    // Show only current page rows
    for (let i = startIndex; i < endIndex; i++) {
        if (visibleRows[i]) {
            visibleRows[i].classList.remove('hidden');
        }
    }
    
    // Update info
    const showing = totalVisible === 0 ? 0 : startIndex + 1;
    document.getElementById('paginationInfo').textContent = 
        `Showing ${showing}-${endIndex} of ${totalVisible} characters`;
    
    // Generate buttons
    const buttonsDiv = document.getElementById('paginationButtons');
    buttonsDiv.innerHTML = '';
    
    if (totalPages <= 1) return;
    
    if (currentPage > 1) {
        buttonsDiv.innerHTML += '<button class="page-btn" onclick="goToPage(' + (currentPage - 1) + ')">← Prev</button>';
    }
    
    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
            const active = i === currentPage ? ' active' : '';
            buttonsDiv.innerHTML += '<button class="page-btn' + active + '" onclick="goToPage(' + i + ')">' + i + '</button>';
        } else if (i === currentPage - 3 || i === currentPage + 3) {
            buttonsDiv.innerHTML += '<span style="color: #666; padding: 0 5px;">...</span>';
        }
    }
    
    if (currentPage < totalPages) {
        buttonsDiv.innerHTML += '<button class="page-btn" onclick="goToPage(' + (currentPage + 1) + ')">Next →</button>';
    }
}

function goToPage(page) {
    currentPage = page;
    applyFilters();
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    deleteCharacterId = null;
}

function confirmDelete() {
    if (!deleteCharacterId) return;
    
    fetch('delete_character_api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ character_id: deleteCharacterId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeDeleteModal();
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error deleting character');
        console.error(error);
    });
}

let currentViewMode = 'compact';
let currentViewData = null;

function viewCharacter(characterId) {
    document.getElementById('viewModal').classList.add('active');
    document.getElementById('viewCharacterContent').innerHTML = 'Loading...';
    
    fetch('view_character_api.php?id=' + characterId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentViewData = data;
                document.getElementById('viewCharacterName').textContent = data.character.character_name;
                renderCharacterView(currentViewMode);
            } else {
                document.getElementById('viewCharacterContent').innerHTML = '<p style="color: red;">Error: ' + data.message + '</p>';
            }
        })
        .catch(error => {
            document.getElementById('viewCharacterContent').innerHTML = '<p style="color: red;">Error loading character</p>';
            console.error(error);
        });
}

function setViewMode(mode) {
    currentViewMode = mode;
    document.querySelectorAll('.mode-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    if (currentViewData) {
        renderCharacterView(mode);
    }
}

function renderCharacterView(mode) {
    const char = currentViewData.character;
    let html = '';
    
    if (mode === 'compact') {
        // Compact view - essential info only
        html = '<div class="character-details compact">';
        html += '<div class="info-grid">';
        html += '<p><strong>Player:</strong> ' + char.player_name + '</p>';
        html += '<p><strong>Clan:</strong> ' + char.clan + '</p>';
        html += '<p><strong>Generation:</strong> ' + char.generation + 'th</p>';
        html += '<p><strong>Concept:</strong> ' + char.concept + '</p>';
        html += '<p><strong>Nature:</strong> ' + char.nature + '</p>';
        html += '<p><strong>Demeanor:</strong> ' + char.demeanor + '</p>';
        html += '</div>';
        
        if (currentViewData.traits && currentViewData.traits.length > 0) {
            const physical = currentViewData.traits.filter(t => t.trait_category === 'Physical');
            const social = currentViewData.traits.filter(t => t.trait_category === 'Social');
            const mental = currentViewData.traits.filter(t => t.trait_category === 'Mental');
            
            html += '<h3>Traits</h3>';
            if (physical.length > 0) html += '<p><strong>Physical:</strong> ' + physical.map(t => t.trait_name).join(', ') + '</p>';
            if (social.length > 0) html += '<p><strong>Social:</strong> ' + social.map(t => t.trait_name).join(', ') + '</p>';
            if (mental.length > 0) html += '<p><strong>Mental:</strong> ' + mental.map(t => t.trait_name).join(', ') + '</p>';
        }
        
        if (currentViewData.disciplines && currentViewData.disciplines.length > 0) {
            html += '<h3>Disciplines</h3>';
            html += '<p>' + currentViewData.disciplines.map(d => d.discipline_name + ' ' + d.level).join(', ') + '</p>';
        }
        
        html += '</div>';
    } else {
        // Full view - all details
        html = '<div class="character-details full">';
        
        html += '<h3>Basic Information</h3>';
        html += '<div class="info-grid">';
        html += '<p><strong>Player:</strong> ' + char.player_name + '</p>';
        html += '<p><strong>Chronicle:</strong> ' + char.chronicle + '</p>';
        html += '<p><strong>Nature:</strong> ' + char.nature + '</p>';
        html += '<p><strong>Demeanor:</strong> ' + char.demeanor + '</p>';
        html += '<p><strong>Concept:</strong> ' + char.concept + '</p>';
        html += '<p><strong>Clan:</strong> ' + char.clan + '</p>';
        html += '<p><strong>Generation:</strong> ' + char.generation + 'th</p>';
        html += '<p><strong>Sire:</strong> ' + (char.sire || 'Unknown') + '</p>';
        html += '</div>';
        
        // Traits
        if (currentViewData.traits && currentViewData.traits.length > 0) {
            const physical = currentViewData.traits.filter(t => t.trait_category === 'Physical');
            const social = currentViewData.traits.filter(t => t.trait_category === 'Social');
            const mental = currentViewData.traits.filter(t => t.trait_category === 'Mental');
            
            html += '<h3>Physical Traits (' + physical.length + ')</h3>';
            html += '<div class="trait-list">';
            physical.forEach(t => html += '<span class="trait-badge">' + t.trait_name + '</span>');
            html += '</div>';
            
            html += '<h3>Social Traits (' + social.length + ')</h3>';
            html += '<div class="trait-list">';
            social.forEach(t => html += '<span class="trait-badge">' + t.trait_name + '</span>');
            html += '</div>';
            
            html += '<h3>Mental Traits (' + mental.length + ')</h3>';
            html += '<div class="trait-list">';
            mental.forEach(t => html += '<span class="trait-badge">' + t.trait_name + '</span>');
            html += '</div>';
        }
        
        // Abilities
        if (currentViewData.abilities && currentViewData.abilities.length > 0) {
            html += '<h3>Abilities</h3>';
            html += '<div class="trait-list">';
            currentViewData.abilities.forEach(a => {
                html += '<span class="trait-badge">' + a.ability_name + ' ' + a.level + '</span>';
            });
            html += '</div>';
        }
        
        // Disciplines
        if (currentViewData.disciplines && currentViewData.disciplines.length > 0) {
            html += '<h3>Disciplines</h3>';
            html += '<div class="trait-list">';
            currentViewData.disciplines.forEach(d => {
                html += '<span class="trait-badge">' + d.discipline_name + ' ' + d.level + '</span>';
            });
            html += '</div>';
        }
        
        // Backgrounds
        if (currentViewData.backgrounds && currentViewData.backgrounds.length > 0) {
            html += '<h3>Backgrounds</h3>';
            html += '<div class="trait-list">';
            currentViewData.backgrounds.forEach(b => {
                html += '<span class="trait-badge">' + b.background_name + ' ' + b.level + '</span>';
            });
            html += '</div>';
        }
        
        // Morality
        if (currentViewData.morality) {
            const m = currentViewData.morality;
            html += '<h3>Morality & Virtues</h3>';
            html += '<div class="info-grid">';
            html += '<p><strong>Humanity:</strong> ' + m.humanity + '/10</p>';
            html += '<p><strong>Willpower:</strong> ' + m.willpower_current + '/' + m.willpower_permanent + '</p>';
            html += '<p><strong>Conscience:</strong> ' + m.conscience + '</p>';
            html += '<p><strong>Self-Control:</strong> ' + m.self_control + '</p>';
            html += '<p><strong>Courage:</strong> ' + m.courage + '</p>';
            html += '</div>';
        }
        
        // Merits & Flaws
        if (currentViewData.merits_flaws && currentViewData.merits_flaws.length > 0) {
            const merits = currentViewData.merits_flaws.filter(m => m.type === 'merit');
            const flaws = currentViewData.merits_flaws.filter(m => m.type === 'flaw');
            
            if (merits.length > 0) {
                html += '<h3>Merits</h3>';
                html += '<div class="trait-list">';
                merits.forEach(m => html += '<span class="trait-badge">' + m.name + ' (' + m.point_value + ')</span>');
                html += '</div>';
            }
            
            if (flaws.length > 0) {
                html += '<h3>Flaws</h3>';
                html += '<div class="trait-list">';
                flaws.forEach(f => html += '<span class="trait-badge">' + f.name + ' (' + f.point_value + ')</span>');
                html += '</div>';
            }
        }
        
        // Status
        if (currentViewData.status) {
            const s = currentViewData.status;
            html += '<h3>Status & Resources</h3>';
            html += '<div class="info-grid">';
            html += '<p><strong>Health:</strong> ' + s.health_levels + '</p>';
            html += '<p><strong>Blood Pool:</strong> ' + s.blood_pool_current + '/' + s.blood_pool_maximum + '</p>';
            if (s.sect_status) html += '<p><strong>Sect Status:</strong> ' + s.sect_status + '</p>';
            if (s.clan_status) html += '<p><strong>Clan Status:</strong> ' + s.clan_status + '</p>';
            html += '</div>';
        }
        
        html += '</div>';
    }
    
    document.getElementById('viewCharacterContent').innerHTML = html;
}

function closeViewModal() {
    document.getElementById('viewModal').classList.remove('active');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
