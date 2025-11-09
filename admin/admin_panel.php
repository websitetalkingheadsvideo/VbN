<?php
/**
 * Admin Panel - Character Management
 */
// Updated 2025-11-09: VbN Agents Page Styling + Link Integration
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('LOTN_VERSION', '0.9.22');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../includes/connect.php';
$extra_css = ['css/admin-agents.css'];
include __DIR__ . '/../includes/header.php';

function render_status_badge($status) {
    $status = strtolower($status ?? '');
    if ($status === 'draft' || $status === 'finalized') {
        return '';
    }

    $classMap = [
        'npc' => 'badge-npc',
        'active' => 'badge-active',
        'inactive' => 'badge-inactive',
        'archived' => 'badge-archived',
        'dead' => 'badge-dead',
        'missing' => 'badge-missing',
        'unknown' => 'badge-neutral'
    ];

    $labelMap = [
        'npc' => 'NPC',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'archived' => 'Archived',
        'dead' => 'Dead',
        'missing' => 'Missing',
        'unknown' => 'Unknown'
    ];

    if ($status === '' || !isset($labelMap[$status])) {
        return '';
    }

    $class = $classMap[$status] ?? 'badge-neutral';
    $label = $labelMap[$status];

    return sprintf('<span class="%s">%s</span>', $class, htmlspecialchars($label));
}

function render_clan_badge(string $clan): string {
    $name = trim($clan);
    if ($name === '') {
        return '<span class="text-muted">—</span>';
    }

    static $palette = [
        'assamite' => '#2E3192',
        'banu haqim' => '#2E3192',
        'banu haqim (assamite)' => '#2E3192',
        'brujah' => '#B22222',
        'caitiff' => '#708090',
        'followers of set' => '#8B6C37',
        'setite' => '#8B6C37',
        'gangrel' => '#228B22',
        'giovanni' => '#556B2F',
        'lasombra' => '#1A1A40',
        'malkavian' => '#6A0DAD',
        'nosferatu' => '#556B5D',
        'ravnos' => '#008B8B',
        'toreador' => '#C71585',
        'tremere' => '#8B008B',
        'tzimisce' => '#99CC00',
        'ventrue' => '#1F3A93',
        'ghoul' => '#8B4513'
    ];

    $key = strtolower($name);
    if (!isset($palette[$key])) {
        return htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    }

    $color = $palette[$key];
    return sprintf(
        '<span class="clan-badge" style="background-color:%s;">%s</span>',
        $color,
        htmlspecialchars($name, ENT_QUOTES, 'UTF-8')
    );
}
?>

<div class="admin-panel-container container-fluid py-4 px-3 px-md-4">
    <h1 class="panel-title display-5 text-light fw-bold mb-1">🔧 Character Management</h1>
    <p class="panel-subtitle lead text-light fst-italic mb-4">Manage all characters across the chronicle</p>
    
    <!-- Admin Navigation -->
    <div class="admin-nav row g-2 g-md-3 mb-4">
        <div class="col-12 col-sm-6 col-md-4 col-lg">
            <a href="admin_panel.php" class="nav-btn btn btn-outline-danger btn-sm w-100 text-center active">👥 Characters</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg">
            <a href="admin_sire_childe.php" class="nav-btn btn btn-outline-danger btn-sm w-100 text-center">🧛 Sire/Childe</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg">
            <a href="admin_equipment.php" class="nav-btn btn btn-outline-danger btn-sm w-100 text-center">⚔️ Equipment</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg">
            <a href="admin_locations.php" class="nav-btn btn btn-outline-danger btn-sm w-100 text-center">📍 Locations</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg">
            <a href="questionnaire_admin.php" class="nav-btn btn btn-outline-danger btn-sm w-100 text-center">📝 Questionnaire</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg">
            <a href="admin_npc_briefing.php" class="nav-btn btn btn-outline-danger btn-sm w-100 text-center">📋 NPC Briefing</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg">
            <a href="boon_ledger.php" class="nav-btn btn btn-outline-danger btn-sm w-100 text-center">💎 Boons</a>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-lg">
            <a href="agents.php" class="nav-btn btn btn-outline-danger btn-sm w-100 text-center">👥 Agents</a>
        </div>
    </div>
    
    <!-- Character Statistics -->
    <div class="character-stats row g-3 mb-4">
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
        <div class="col-12 col-sm-4 col-lg-3">
            <div class="stat-mini text-center">
            <span class="stat-number"><?php echo $stats['total'] ?? 0; ?></span>
            <span class="stat-label">Total</span>
            </div>
        </div>
        <div class="col-12 col-sm-4 col-lg-3">
            <div class="stat-mini text-center">
            <span class="stat-number"><?php echo $stats['pcs'] ?? 0; ?></span>
            <span class="stat-label">PCs</span>
            </div>
        </div>
        <div class="col-12 col-sm-4 col-lg-3">
            <div class="stat-mini text-center">
            <span class="stat-number"><?php echo $stats['npcs'] ?? 0; ?></span>
            <span class="stat-label">NPCs</span>
            </div>
        </div>
    </div>

    <section class="mb-4">
        <h2 class="h5 text-light mb-3 text-uppercase">Agents</h2>
        <div class="row g-2 g-md-3">
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <a href="agents.php" class="nav-btn btn btn-outline-danger btn-sm w-100 text-center">👥 Agents Dashboard</a>
            </div>
        </div>
    </section>

    <!-- Questionnaire Statistics -->
    <div class="questionnaire-stats d-flex flex-wrap gap-3 mb-4 align-items-center">
        <h2 class="h6 text-light mb-0">Story Questionnaire</h2>
        <?php
        // Get questionnaire statistics
        $questionnaire_query = "SELECT COUNT(*) as total_questions FROM questionnaire_questions";
        $questionnaire_result = mysqli_query($conn, $questionnaire_query);
        $questionnaire_count = $questionnaire_result ? mysqli_fetch_assoc($questionnaire_result)['total_questions'] : 0;
        ?>
        <div class="stat-mini text-center">
            <span class="stat-number"><?php echo $questionnaire_count; ?></span>
            <span class="stat-label">Questions</span>
        </div>
        <div class="d-flex align-items-center">
            <a href="questionnaire_admin.php" class="btn btn-outline-danger btn-sm">📝 Manage</a>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="filter-controls row gy-3 align-items-center mb-4">
        <div class="filter-buttons col-12 col-md-auto d-flex flex-wrap gap-2">
            <button class="filter-btn btn btn-outline-danger active" data-filter="all">All Characters</button>
            <button class="filter-btn btn btn-outline-danger" data-filter="pcs">PCs Only</button>
            <button class="filter-btn btn btn-outline-danger" data-filter="npcs">NPCs Only</button>
        </div>
        <div class="clan-filter col-12 col-md-auto d-flex align-items-center gap-2">
            <label for="clanFilter" class="text-light text-uppercase small mb-0">Sort by Clan:</label>
            <select id="clanFilter" class="form-select form-select-sm bg-dark text-light border-danger">
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
        <div class="search-box col-12 col-lg col-xl-4">
            <input type="text" id="characterSearch" class="form-control form-control-sm bg-dark text-light border-danger" placeholder="🔍 Search by name..." />
        </div>
        <div class="page-size-control col-12 col-md-auto d-flex align-items-center gap-2">
            <label for="pageSize" class="text-light text-uppercase small mb-0">Per page:</label>
            <select id="pageSize" class="form-select form-select-sm bg-dark text-light border-danger">
                <option value="20" selected>20</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    <!-- Character Table -->
    <div class="character-table-wrapper table-responsive rounded-3">
        <table class="character-table table table-dark table-hover align-middle mb-0" id="characterTable">
            <thead>
                <tr>
                    <th data-sort="character_name" class="text-start">Name <span class="sort-icon">⇅</span></th>
                    <th data-sort="player_name" class="text-center text-nowrap">NPC <span class="sort-icon">⇅</span></th>
                    <th data-sort="clan" class="text-center text-nowrap">Clan <span class="sort-icon">⇅</span></th>
                    <th data-sort="generation" class="text-center text-nowrap">Gen <span class="sort-icon">⇅</span></th>
                    <th data-sort="status" class="text-center text-nowrap">Status <span class="sort-icon">⇅</span></th>
                    <th class="text-center text-nowrap" style="width: 150px;">Actions</th>
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
                    echo "<tr><td colspan='6'>Query Error: " . mysqli_error($conn) . "</td></tr>";
                } elseif (mysqli_num_rows($char_result) > 0) {
                    while ($char = mysqli_fetch_assoc($char_result)) {
                        $is_npc = ($char['player_name'] === 'NPC');
                        $playerName = trim($char['player_name'] ?? '') !== '' ? $char['player_name'] : ($is_npc ? 'NPC' : '—');
                        $clanName = $char['clan'] ?? 'Unknown';
                        $generation = $char['generation'] ?? '';
                        $statusRaw = trim((string)($char['status'] ?? ''));
                        $status = strtolower($statusRaw);
                        if ($status === '') {
                            $status = 'active';
                        }

                        $allowedStatuses = ['active', 'inactive', 'archived', 'dead', 'missing', 'npc', 'unknown'];
                        if (!in_array($status, $allowedStatuses, true)) {
                            $status = 'unknown';
                        }

                        $camarilla = $char['camarilla_status'] ?? 'Unknown';
                        $owner = $char['owner_username'] ?? '—';
                ?>
                    <tr class="character-row" 
                        data-id="<?php echo $char['id']; ?>"
                        data-type="<?php echo $is_npc ? 'npc' : 'pc'; ?>" 
                        data-name="<?php echo htmlspecialchars($char['character_name']); ?>"
                        data-clan="<?php echo htmlspecialchars($clanName); ?>"
                        data-player="<?php echo htmlspecialchars($playerName); ?>"
                        data-generation="<?php echo htmlspecialchars($generation); ?>"
                        data-status="<?php echo htmlspecialchars($status); ?>"
                        data-camarilla="<?php echo htmlspecialchars($camarilla); ?>"
                        data-owner="<?php echo htmlspecialchars($owner); ?>">
                        <td class="character-cell align-top text-light">
                            <strong><?php echo htmlspecialchars($char['character_name']); ?></strong>
                        </td>
                        <td class="align-top text-center text-nowrap">
                            <?php
                            if ($is_npc) {
                                echo render_status_badge('npc');
                            } elseif ($playerName && $playerName !== '—') {
                                echo '<span class="text-light">' . htmlspecialchars($playerName) . '</span>';
                            } else {
                                echo '<span class="text-muted">—</span>';
                            }
                            ?>
                        </td>
                        <td class="align-top text-center text-nowrap"><?php echo render_clan_badge($clanName); ?></td>
                        <td class="align-top text-center text-nowrap">
                            <?php echo htmlspecialchars($generation ?: '—'); ?>
                        </td>
                        <td class="align-top text-center text-nowrap">
                            <?php
                                $statusBadge = render_status_badge($status);
                                if ($statusBadge === '') {
                                    echo '<span class="badge-neutral">' . htmlspecialchars(ucfirst($status)) . '</span>';
                                } else {
                                    echo $statusBadge;
                                }
                            ?>
                        </td>
                        <td class="actions text-center align-top" style="width: 150px;">
                            <div class="btn-group btn-group-sm" role="group" aria-label="Character actions">
                                <button class="action-btn view-btn btn btn-primary" 
                                        data-id="<?php echo $char['id']; ?>"
                                        title="View Character">👁️</button>
                                <a href="../lotn_char_create.php?id=<?php echo $char['id']; ?>" 
                                   class="action-btn edit-btn btn btn-warning" 
                                   title="Edit Character">✏️</a>
                                <button class="action-btn delete-btn btn btn-danger" 
                                        data-id="<?php echo $char['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($char['character_name']); ?>"
                                        data-status="<?php echo $status; ?>"
                                        title="Delete Character">🗑️</button>
                            </div>
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='6' class='empty-state'>No characters found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <div class="pagination-controls d-flex flex-column flex-lg-row gap-3 align-items-center justify-content-between" id="paginationControls">
        <div class="pagination-info fw-semibold">
            <span id="paginationInfo">Showing 1-26 of 26 characters</span>
        </div>
        <div class="pagination-buttons d-flex flex-wrap gap-2 justify-content-center" id="paginationButtons">
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
.admin-panel-container { max-width: 1600px; margin: 0 auto; }
.panel-title { font-family: var(--font-brand), 'IM Fell English', serif; }
.panel-subtitle { font-family: var(--font-body), 'Source Serif Pro', serif; }

.nav-btn { background: rgba(139, 0, 0, 0.2); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 5px; color: #b8a090; font-family: var(--font-body), 'Source Serif Pro', serif; font-weight: 600; text-decoration: none; transition: all 0.3s ease; }
.nav-btn:hover { background: rgba(139, 0, 0, 0.3); border-color: #8B0000; color: #f5e6d3; }
.nav-btn.active { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); border-color: #b30000; color: #f5e6d3; }
.filter-btn { padding: 10px 20px; background: rgba(139, 0, 0, 0.2); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 5px; color: #b8a090; font-family: var(--font-body), 'Source Serif Pro', serif; font-weight: 600; cursor: pointer; transition: all 0.3s ease; }
.filter-btn:hover { background: rgba(139, 0, 0, 0.3); border-color: #8B0000; color: #f5e6d3; }
.filter-btn.active { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); border-color: #b30000; color: #f5e6d3; }
.pagination-controls { margin-top: 20px; padding: 15px; background: rgba(26, 15, 15, 0.3); border-radius: 5px; }
.pagination-info { font-family: var(--font-body), 'Source Serif Pro', serif; color: #b8a090; }
.page-btn { padding: 8px 12px; background: rgba(139, 0, 0, 0.2); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 4px; color: #b8a090; font-family: var(--font-body), 'Source Serif Pro', serif; cursor: pointer; transition: all 0.2s; }
.page-btn:hover { background: rgba(139, 0, 0, 0.3); border-color: #8B0000; color: #f5e6d3; }
.page-btn.active { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); border-color: #b30000; color: #f5e6d3; }
.stat-mini { background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%); border: 2px solid #8B0000; border-radius: 5px; padding: 12px 20px; display: flex; flex-direction: column; align-items: center; min-width: 100px; }
.stat-mini .stat-number { font-family: var(--font-brand), 'IM Fell English', serif; font-size: 1.8em; color: #8B0000; font-weight: bold; }
.stat-mini .stat-label { font-family: var(--font-body), 'Source Serif Pro', serif; font-size: 0.85em; color: #b8a090; margin-top: 5px; }

.character-table-wrapper { 
    background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%); 
    border: 2px solid #8B0000; 
    border-radius: 8px; 
    overflow-x: auto; 
    overflow-y: visible;
    scrollbar-width: thin; /* Firefox - thin scrollbar on desktop */
    scrollbar-color: rgba(139, 0, 0, 0.6) rgba(26, 15, 15, 0.3);
    padding: 0 !important; /* Remove any default padding */
    margin-left: -20px; /* Compensate for container padding */
    margin-right: -20px; /* Compensate for container padding */
    width: calc(100% + 40px); /* Extend to full width including negative margins */
    box-sizing: border-box; /* Include border in width calculation */
}

/* Override global.css table-responsive styles if class is present */
.character-table-wrapper.table-responsive {
    padding: 0 !important;
    margin-left: -20px !important;
    margin-right: -20px !important;
    width: calc(100% + 40px) !important;
}

/* Ensure table takes full width of wrapper */
.character-table-wrapper .character-table {
    width: 100% !important;
    margin: 0 !important;
}

/* Hide scrollbar on mobile/tablet to prevent clipping sticky columns */
@media (max-width: 768px) {
    .character-table-wrapper {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE/Edge */
    }
    
    .character-table-wrapper::-webkit-scrollbar {
        display: none; /* Chrome/Safari/Opera */
    }
}
.character-table { 
    width: 100%; 
    border-collapse: collapse; 
    margin: 0;
    padding: 0;
    table-layout: fixed; /* Force table to respect column widths */
}
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
.character-cell { position: relative; }
.character-heading { display: flex; flex-direction: column; gap: 6px; }
.col-header,
.col-cell { display: none; }
.mobile-meta { margin-top: 6px; display: flex; flex-direction: column; gap: 6px; font-size: 0.9em; color: #b8a090; }
.mobile-meta .meta-item { display: flex; gap: 6px; align-items: baseline; padding: 4px 0; border-bottom: 1px solid rgba(139, 0, 0, 0.15); }
.mobile-meta .meta-item:last-child { border-bottom: none; }
.mobile-meta .meta-label { font-weight: 600; color: #d4c4b0; text-transform: uppercase; letter-spacing: 0.4px; font-size: 0.75em; }
.mobile-meta .meta-value { color: #f5e6d3; font-weight: 500; }
.status-cell span { display: inline-block; }

@media (max-width: 572.98px) {
    .character-heading { gap: 6px; }
    .mobile-meta { padding-top: 6px; }
    .mobile-meta .meta-item { background: none; border-radius: 0; padding: 4px 0; font-size: 0.9em; }
}

@media (min-width: 573px) and (max-width: 991.98px) {
    .character-heading { flex-direction: row; align-items: center; gap: 12px; }
    .character-heading strong { white-space: nowrap; }
    .mobile-meta {
        flex-direction: row;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 0;
    }
    .mobile-meta .meta-item {
        align-items: center;
        background: rgba(139, 0, 0, 0.2);
        border: 1px solid rgba(139, 0, 0, 0.4);
        border-radius: 999px;
        padding: 4px 12px;
        font-size: 0.82em;
        color: #f5e6d3;
        gap: 6px;
    }
    .mobile-meta .meta-item .meta-label {
        display: none;
    }
    .mobile-meta .meta-item .meta-value {
        color: #f5e6d3;
        font-weight: 600;
    }
    .mobile-meta .meta-item.meta-status .badge-npc,
    .mobile-meta .meta-item.meta-status .badge-draft,
    .mobile-meta .meta-item.meta-status .badge-finalized,
    .mobile-meta .meta-item.meta-status .badge-active,
    .mobile-meta .meta-item.meta-status .badge-dead,
    .mobile-meta .meta-item.meta-status .badge-missing {
        border-radius: 999px;
        padding: 4px 12px;
        font-size: 0.85em;
        display: inline-block;
    }
    .mobile-meta .meta-item:nth-child(n+4) {
        display: none;
    }
}

@media (min-width: 992px) {
    .mobile-meta {
        display: none;
    }
    .col-header.col-lg-visible,
    .col-cell.col-lg-visible {
        display: table-cell;
    }
}

@media (min-width: 1200px) {
    .col-header.col-xl-visible,
    .col-cell.col-xl-visible {
        display: table-cell;
    }
}

.badge-npc { background: #4a1a6b; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-draft { background: #8B6508; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-finalized { background: #1a6b3a; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-active { background: #0d7a4a; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-inactive { background: #8B6508; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-archived { background: #3a3a3a; color: #d4c4b0; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-neutral { background: rgba(139, 0, 0, 0.2); color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.clan-badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: 600; color: #f5e6d3; letter-spacing: 0.4px; }
.badge-dead { background: #3a3a3a; color: #999; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
.badge-missing { background: #5a4a2a; color: #f5e6d3; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }

/* Character Name column - expand to fill available space */
.character-table th[data-sort="character_name"],
.character-table td:first-child {
    padding: 12px;
    width: auto; /* Takes remaining space */
}

/* Actions column - fixed narrow width on the right */
.character-table th:last-child {
    padding: 15px 6px 15px 12px !important;
    text-align: right !important;
    width: 120px !important; /* Fixed width instead of 1% */
}

.character-table td.actions {
    padding: 12px 6px 12px 12px !important;
    text-align: right !important;
    overflow: visible;
    width: 120px !important; /* Fixed width to match header */
    max-width: 120px !important;
}

.actions { 
    display: flex; 
    gap: 8px; 
    justify-content: flex-end; 
    align-items: center; 
    margin: 0;
    padding: 0;
}
.action-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 4px; text-decoration: none; font-size: 1.1em; cursor: pointer; background: rgba(139, 0, 0, 0.2); border: 1px solid rgba(139, 0, 0, 0.4); transition: all 0.2s; flex-shrink: 0; }
.action-btn:hover { background: rgba(139, 0, 0, 0.4); transform: scale(1.1); }
.view-btn { background: rgba(0, 100, 200, 0.2); border-color: rgba(0, 100, 200, 0.4); }
.view-btn:hover { background: rgba(0, 100, 200, 0.4); }
.edit-btn { background: rgba(139, 100, 0, 0.2); border-color: rgba(139, 100, 0, 0.4); }
.edit-btn:hover { background: rgba(139, 100, 0, 0.4); }
.delete-btn { 
    display: inline-flex !important;
    background: rgba(139, 0, 0, 0.2); 
    border-color: rgba(139, 0, 0, 0.4); 
}
.delete-btn:hover { background: rgba(139, 0, 0, 0.4); }
.empty-state { text-align: center; padding: 40px; color: #b8a090; font-style: italic; }

.modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); align-items: center; justify-content: center; }
.modal.active { display: flex; }
.modal-content { background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%); border: 3px solid #8B0000; border-radius: 10px; padding: 30px; max-width: 500px; position: relative; }
.modal-content.large-modal { max-width: 900px; max-height: 85vh; overflow-y: auto; }
.modal-content.large-modal.compact-mode { 
    max-height: 90vh; 
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

/* Character Header Section - styles only, layout handled by Bootstrap grid */
.character-header-section {
    display: block;
    margin-bottom: 0;
    padding: 25px;
    background: linear-gradient(135deg, rgba(42, 21, 21, 0.6) 0%, rgba(26, 15, 15, 0.6) 100%);
    border: 2px solid rgba(139, 0, 0, 0.3);
    border-radius: 8px;
}

.compact-mode .character-header-section {
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

/* iPad and Tablet Devices (768px) */
@media (min-width: 431px) and (max-width: 768px) {
    .admin-panel-container {
        padding: 25px 20px;
    }
    
    .panel-title {
        font-size: 2.2em;
    }
    
    .panel-subtitle {
        font-size: 1.1em;
    }
    
    /* Optimize table wrapper for tablet */
    .character-table-wrapper {
        position: relative;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%);
        border: 2px solid #8B0000;
        border-radius: 8px;
        padding: 5px;
        /* Hide scrollbar but keep functionality */
        scrollbar-width: thin;
        scrollbar-color: rgba(139, 0, 0, 0.5) transparent;
    }
    
    /* Hide scrollbar on mobile to prevent clipping buttons */
    .character-table-wrapper {
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE/Edge */
    }
    
    .character-table-wrapper::-webkit-scrollbar {
        display: none; /* Chrome/Safari/Opera */
    }
    
    /* Add subtle scroll indicator */
    .character-table-wrapper::before {
        content: '';
        position: sticky;
        left: 0;
        top: 0;
        bottom: 0;
        width: 25px;
        pointer-events: none;
        z-index: 5;
        background: linear-gradient(to right, rgba(26, 15, 15, 0.9), transparent);
    }
    
    /* Table sizing for better tablet readability */
    .character-table {
        width: 100%;
    }
    
    .character-table th,
    .character-table td {
        padding: 14px 12px;
        font-size: 0.95em;
        white-space: nowrap;
    }
    
    /* Simplified 2-column layout - no sticky positioning needed */
    .character-table th:first-child,
    .character-table td:first-child {
        padding: 14px 12px;
    }
    
    .character-table th:last-child,
    .character-table td.actions {
        padding: 14px 12px;
        text-align: center;
    }
    
    /* Larger action buttons for tablet touch */
    .action-btn {
        min-width: 40px;
        min-height: 40px;
        font-size: 1.3em;
        padding: 8px;
        margin: 0 4px;
        flex-shrink: 0; /* Prevent buttons from shrinking */
    }
    
    /* Remove scroll hint on tablet - scrollbar is visible */
    .character-table-wrapper::after {
        display: none;
    }
    
    /* Tablet-optimized pagination */
    .pagination-controls {
        flex-direction: row;
        gap: 15px;
        align-items: center;
        justify-content: space-between;
    }
    
    .pagination-buttons {
        justify-content: center;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .page-btn {
        min-width: 48px;
        min-height: 48px;
        font-size: 1em;
    }
}

/* iPhone SE and Small Mobile Devices (max-width: 375px) */
@media (max-width: 375px) {
    .admin-panel-container {
        padding: 15px 10px;
    }
    
    .panel-title {
        font-size: 1.8em;
    }
    
    .panel-subtitle {
        font-size: 1em;
    }
    
    /* Make table wrapper more obviously scrollable */
    .character-table-wrapper {
        position: relative;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        /* Add visual scroll indicator */
        background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%);
        border: 2px solid #8B0000;
        border-radius: 8px;
    }
    
    /* Add scroll shadow indicators */
    .character-table-wrapper::before {
        content: '';
        position: sticky;
        left: 0;
        top: 0;
        bottom: 0;
        width: 20px;
        pointer-events: none;
        z-index: 5;
        background: linear-gradient(to right, rgba(26, 15, 15, 0.95), transparent);
    }
    
    /* Make table cells more compact */
    .character-table {
        width: 100%;
    }
    
    .character-table th,
    .character-table td {
        padding: 10px 8px;
        font-size: 0.85em;
        white-space: nowrap;
    }
    
    /* Simplified 2-column layout */
    .character-table th:last-child,
    .character-table td.actions {
        padding: 10px 8px;
        text-align: center;
    }
    
    /* Make action buttons larger and more touch-friendly */
    .action-btn {
        min-width: 36px;
        min-height: 36px;
        font-size: 1.2em;
        padding: 6px;
        margin: 0 2px;
    }
    
    /* Add scroll hint */
    .character-table-wrapper {
        position: relative;
    }
    
    .character-table-wrapper::after {
        content: '← Scroll →';
        position: absolute;
        top: 50%;
        right: 15px;
        transform: translateY(-50%);
        color: rgba(201, 169, 110, 0.6);
        font-size: 0.75em;
        font-family: var(--font-body), 'Source Serif Pro', serif;
        pointer-events: none;
        z-index: 4;
        animation: fadeInOut 3s ease-in-out infinite;
        text-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
    }
    
    @keyframes fadeInOut {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 0.8; }
    }
    
    /* Better pagination on mobile */
    .pagination-controls {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }
    
    .pagination-buttons {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .page-btn {
        min-width: 44px;
        min-height: 44px;
    }
}

/* iPhone 14 Pro Max and Medium Mobile Devices (max-width: 430px) */
@media (max-width: 430px) and (min-width: 376px) {
    .admin-panel-container {
        padding: 20px 15px;
    }
    
    .panel-title {
        font-size: 2em;
    }
    
    .panel-subtitle {
        font-size: 1.05em;
    }
    
    /* Make table wrapper more obviously scrollable */
    .character-table-wrapper {
        position: relative;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%);
        border: 2px solid #8B0000;
        border-radius: 8px;
    }
    
    /* Add scroll shadow indicator */
    .character-table-wrapper::before {
        content: '';
        position: sticky;
        left: 0;
        top: 0;
        bottom: 0;
        width: 20px;
        pointer-events: none;
        z-index: 5;
        background: linear-gradient(to right, rgba(26, 15, 15, 0.95), transparent);
    }
    
    /* Make table cells more compact but slightly larger than iPhone SE */
    .character-table {
        width: 100%;
    }
    
    .character-table th,
    .character-table td {
        padding: 12px 10px;
        font-size: 0.9em;
        white-space: nowrap;
    }
    
    /* Simplified 2-column layout */
    .character-table th:first-child,
    .character-table td:first-child {
        padding: 12px 10px;
    }
    
    .character-table th:last-child,
    .character-table td.actions {
        padding: 12px 10px;
        text-align: center;
    }
    
    /* Make action buttons larger and more touch-friendly */
    .action-btn {
        min-width: 38px;
        min-height: 38px;
        font-size: 1.25em;
        padding: 7px;
        margin: 0 3px;
    }
    
    /* Add scroll hint */
    .character-table-wrapper::after {
        content: '← Scroll →';
        position: absolute;
        top: 50%;
        right: 15px;
        transform: translateY(-50%);
        color: rgba(201, 169, 110, 0.6);
        font-size: 0.8em;
        font-family: var(--font-body), 'Source Serif Pro', serif;
        pointer-events: none;
        z-index: 4;
        animation: fadeInOut 3s ease-in-out infinite;
        text-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
    }
    
    /* Better pagination on mobile */
    .pagination-controls {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
    }
    
    .pagination-buttons {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .page-btn {
        min-width: 44px;
        min-height: 44px;
    }
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
<script>
// No auto-scroll needed - Name and Actions columns are sticky and always visible
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
