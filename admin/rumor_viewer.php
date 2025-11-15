<?php
/**
 * Admin Panel - Rumor Debug / Viewer
 * 
 * Read-only debugging tool for browsing rumor JSON files and inspecting how RumorEngine processes them.
 * 
 * Rumor files are loaded from: data/rumors/*.json
 * Rumor history files are loaded from: data/state/rumor_history_<PC_ID>.json
 * 
 * This is a read-only viewer. Future extension points:
 * - Add editing functionality to modify rumors
 * - Add ability to create new rumors
 * - Add ability to delete rumors
 * - Add validation against RumorEngine requirements
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('LOTN_VERSION', '0.10.16');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../includes/connect.php';
include __DIR__ . '/../includes/header.php';

/**
 * Render rarity badge with color
 * 
 * @param string $rarity
 * @return string HTML badge
 */
function render_rarity_badge(string $rarity): string {
    $rarity = strtolower(trim($rarity));
    
    $rarityMap = [
        'common' => ['color' => '#8B8989', 'bg' => 'rgba(139, 137, 137, 0.2)', 'border' => 'rgba(139, 137, 137, 0.5)'],
        'uncommon' => ['color' => '#228B22', 'bg' => 'rgba(34, 139, 34, 0.2)', 'border' => 'rgba(34, 139, 34, 0.5)'],
        'rare' => ['color' => '#FFD700', 'bg' => 'rgba(255, 215, 0, 0.2)', 'border' => 'rgba(255, 215, 0, 0.5)'],
        'very rare' => ['color' => '#FF4500', 'bg' => 'rgba(255, 69, 0, 0.2)', 'border' => 'rgba(255, 69, 0, 0.5)'],
        'legendary' => ['color' => '#9370DB', 'bg' => 'rgba(147, 112, 219, 0.2)', 'border' => 'rgba(147, 112, 219, 0.5)'],
    ];
    
    if (!isset($rarityMap[$rarity])) {
        $rarity = 'common';
    }
    
    $style = $rarityMap[$rarity];
    return sprintf(
        '<span class="rarity-badge" style="background: %s; border: 1px solid %s; color: %s; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; white-space: nowrap;">%s</span>',
        $style['bg'],
        $style['border'],
        $style['color'],
        htmlspecialchars(ucfirst($rarity))
    );
}

/**
 * Render category badge with color
 * 
 * @param string $category
 * @return string HTML badge
 */
function render_category_badge(string $category): string {
    $category = trim($category);
    
    $categoryMap = [
        'Media' => ['color' => '#4A90E2', 'bg' => 'rgba(74, 144, 226, 0.2)', 'border' => 'rgba(74, 144, 226, 0.5)'],
        'Street' => ['color' => '#FF6B35', 'bg' => 'rgba(255, 107, 53, 0.2)', 'border' => 'rgba(255, 107, 53, 0.5)'],
        'Elysium' => ['color' => '#8B4513', 'bg' => 'rgba(139, 69, 19, 0.2)', 'border' => 'rgba(139, 69, 19, 0.5)'],
        'Politics' => ['color' => '#8B0000', 'bg' => 'rgba(139, 0, 0, 0.2)', 'border' => 'rgba(139, 0, 0, 0.5)'],
        'Society' => ['color' => '#9370DB', 'bg' => 'rgba(147, 112, 219, 0.2)', 'border' => 'rgba(147, 112, 219, 0.5)'],
        'Crime' => ['color' => '#DC143C', 'bg' => 'rgba(220, 20, 60, 0.2)', 'border' => 'rgba(220, 20, 60, 0.5)'],
        'Supernatural' => ['color' => '#6A0DAD', 'bg' => 'rgba(106, 13, 173, 0.2)', 'border' => 'rgba(106, 13, 173, 0.5)'],
    ];
    
    if (!isset($categoryMap[$category])) {
        // Default styling for unknown categories
        $style = ['color' => '#b8a090', 'bg' => 'rgba(139, 0, 0, 0.2)', 'border' => 'rgba(139, 0, 0, 0.4)'];
    } else {
        $style = $categoryMap[$category];
    }
    
    return sprintf(
        '<span class="category-badge" style="background: %s; border: 1px solid %s; color: %s; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; white-space: nowrap;">%s</span>',
        $style['bg'],
        $style['border'],
        $style['color'],
        htmlspecialchars($category)
    );
}

/**
 * Truncate text with ellipsis
 * 
 * @param string $text
 * @param int $maxLength
 * @return string
 */
function truncate_text(string $text, int $maxLength = 50): string {
    if (strlen($text) <= $maxLength) {
        return $text;
    }
    return substr($text, 0, $maxLength) . '...';
}

/**
 * Load all rumors from all JSON files in data/rumors/
 * 
 * @return array Array of rumors, each with optional 'source_file' metadata
 */
function loadAllRumors(): array {
    $dir = __DIR__ . '/../data/rumors';
    $allRumors = [];
    
    if (!is_dir($dir)) {
        return $allRumors;
    }
    
    $files = glob($dir . '/*.json');
    if ($files === false) {
        return $allRumors;
    }
    
    foreach ($files as $file) {
        $json = @file_get_contents($file);
        if ($json === false) {
            continue;
        }
        
        $data = json_decode($json, true);
        if (is_array($data)) {
            // Tag each rumor with its source file
            $filename = basename($file);
            foreach ($data as $rumor) {
                if (is_array($rumor)) {
                    // Create a new array with source_file added
                    $rumorWithSource = $rumor;
                    $rumorWithSource['source_file'] = $filename;
                    $allRumors[] = $rumorWithSource;
                }
            }
        }
    }
    
    return $allRumors;
}

/**
 * Load rumors from a specific file
 * 
 * @param string $filename Filename (e.g., "rumors_global.json")
 * @return array Array of rumors from that file
 */
function loadRumorFile(string $filename): array {
    $file = __DIR__ . '/../data/rumors/' . $filename;
    
    if (!file_exists($file)) {
        return [];
    }
    
    $json = @file_get_contents($file);
    if ($json === false) {
        return [];
    }
    
    $data = json_decode($json, true);
    if (!is_array($data)) {
        return [];
    }
    
    // Tag each rumor with its source file
    foreach ($data as &$rumor) {
        if (is_array($rumor)) {
            $rumor['source_file'] = $filename;
        }
    }
    
    return $data;
}

/**
 * Load rumor history for a specific PC
 * 
 * @param string $pcId PC identifier
 * @return array|null Array of history entries or null if file doesn't exist
 */
function loadRumorHistory(string $pcId): ?array {
    $path = __DIR__ . '/../data/state/rumor_history_' . $pcId . '.json';
    
    if (!file_exists($path)) {
        return null;
    }
    
    $json = @file_get_contents($path);
    if ($json === false) {
        return null;
    }
    
    $data = json_decode($json, true);
    return is_array($data) ? $data : null;
}

/**
 * Get list of available rumor files
 * 
 * @return array Array of filenames
 */
function getRumorFiles(): array {
    $dir = __DIR__ . '/../data/rumors';
    
    if (!is_dir($dir)) {
        return [];
    }
    
    $files = glob($dir . '/*.json');
    if ($files === false) {
        return [];
    }
    
    return array_map('basename', $files);
}

// Load rumors based on filter
$selectedFile = $_GET['file'] ?? 'all';
$pcId = $_GET['pc_id'] ?? '';

// Validate file selection
$rumorFiles = getRumorFiles();
if ($selectedFile !== 'all' && !in_array($selectedFile, $rumorFiles)) {
    $selectedFile = 'all';
}

if ($selectedFile === 'all') {
    $rumors = loadAllRumors();
} else {
    $rumors = loadRumorFile($selectedFile);
}

// Extract unique categories and rarities for filters
$categories = [];
$rarities = [];
foreach ($rumors as $rumor) {
    if (isset($rumor['category']) && !in_array($rumor['category'], $categories)) {
        $categories[] = $rumor['category'];
    }
    if (isset($rumor['rarity']) && !in_array($rumor['rarity'], $rarities)) {
        $rarities[] = $rumor['rarity'];
    }
}
sort($categories);
sort($rarities);

// Load PC rumor history if requested
$history = null;
$historyError = null;
if (!empty($pcId)) {
    $history = loadRumorHistory($pcId);
    if ($history === null) {
        $historyError = "No history found for PC ID: " . htmlspecialchars($pcId);
    }
}

?>

<div class="admin-panel-container container-fluid py-4 px-3 px-md-4">
    <h1 class="panel-title display-5 text-light fw-bold mb-1">📰 Rumor Debug / Viewer</h1>
    <p class="panel-subtitle lead text-light fst-italic mb-4">Browse and inspect rumor JSON files as RumorEngine sees them</p>
    
    <!-- Admin Navigation -->
    <nav class="admin-nav row g-2 g-md-3 mb-4" aria-label="Admin Navigation">
        <div class="col-12 col-sm-6 col-md-4 col-lg">
            <a href="admin_panel.php" class="nav-btn btn btn-outline-danger btn-sm w-100 text-center">👥 Characters</a>
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
        <div class="col-12 col-sm-6 col-md-4 col-lg">
            <a href="rumor_viewer.php" class="nav-btn btn btn-outline-danger btn-sm w-100 text-center active">📰 Rumors</a>
        </div>
    </nav>
    
    <!-- Filter Controls -->
    <div class="filter-controls row gy-3 align-items-center mb-4">
        <div class="col-12 col-md-3">
            <label for="fileFilter" class="text-light text-uppercase small mb-2 d-block">Source File:</label>
            <select id="fileFilter" class="form-select form-select-sm bg-dark text-light border-danger">
                <option value="all" <?php echo $selectedFile === 'all' ? 'selected' : ''; ?>>All Rumors (merged)</option>
                <?php foreach ($rumorFiles as $file): ?>
                    <option value="<?php echo htmlspecialchars($file); ?>" <?php echo $selectedFile === $file ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($file); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-3 filter-category">
            <label for="categoryFilter" class="text-light text-uppercase small mb-2 d-block">Category:</label>
            <select id="categoryFilter" class="form-select form-select-sm bg-dark text-light border-danger">
                <option value="all">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-3 filter-rarity">
            <label for="rarityFilter" class="text-light text-uppercase small mb-2 d-block">Rarity:</label>
            <select id="rarityFilter" class="form-select form-select-sm bg-dark text-light border-danger">
                <option value="all">All Rarities</option>
                <?php foreach ($rarities as $rar): ?>
                    <option value="<?php echo htmlspecialchars($rar); ?>"><?php echo htmlspecialchars($rar); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <label for="searchBox" class="text-light text-uppercase small mb-2 d-block">Search:</label>
            <input type="text" id="searchBox" class="form-control form-control-sm bg-dark text-light border-danger" placeholder="Search text, notes..." />
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
    
    <!-- Rumor Count -->
    <div class="mb-3">
        <p class="text-light mb-0">
            <span id="paginationInfo">Showing 1-<?php echo min(20, count($rumors)); ?> of <?php echo count($rumors); ?> rumors</span>
        </p>
    </div>
    
    <!-- Rumor Table -->
    <div class="character-table-wrapper table-responsive rounded-3">
        <?php if (empty($rumors)): ?>
            <div class="alert alert-warning text-center">
                <p class="mb-0">No rumor files found in <code>data/rumors/</code></p>
            </div>
        <?php else: ?>
            <table class="character-table table table-dark table-hover align-middle mb-0" id="rumorTable">
                <thead>
                    <tr>
                        <th data-sort="id" class="text-center">ID <span class="sort-icon">⇅</span></th>
                        <th data-sort="category" class="text-center text-nowrap">Category <span class="sort-icon">⇅</span></th>
                        <th data-sort="rarity" class="text-center text-nowrap">Rarity <span class="sort-icon">⇅</span></th>
                        <th data-sort="targets" class="text-center" style="max-width: 200px;">Targets</th>
                        <th data-sort="source_types" class="text-center" style="max-width: 150px;">Sources</th>
                        <th data-sort="text" class="text-center" style="max-width: 300px;">Text Preview</th>
                        <th class="text-center text-nowrap" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rumors as $index => $rumor): ?>
                        <?php
                        $id = $rumor['id'] ?? '';
                        $category = $rumor['category'] ?? '—';
                        $rarity = $rumor['rarity'] ?? '—';
                        $targets = $rumor['targets'] ?? [];
                        $sourceTypes = $rumor['source_types'] ?? [];
                        $deliveryContexts = $rumor['delivery_contexts'] ?? [];
                        $plotIds = $rumor['connects_to_plot_ids'] ?? [];
                        $sourceFile = isset($rumor['source_file']) ? $rumor['source_file'] : '—';
                        $text = $rumor['text'] ?? '';
                        $ghostText = $rumor['ghost_text'] ?? null;
                        $gmNotes = $rumor['gm_notes'] ?? null;
                        $prerequisites = $rumor['prerequisites'] ?? [];
                        $weightModifiers = $rumor['weight_modifiers'] ?? [];
                        ?>
                        <tr class="rumor-row character-row" 
                            data-id="<?php echo htmlspecialchars($id); ?>"
                            data-category="<?php echo htmlspecialchars($category); ?>"
                            data-rarity="<?php echo htmlspecialchars($rarity); ?>"
                            data-text="<?php echo htmlspecialchars($text); ?>"
                            data-ghost-text="<?php echo htmlspecialchars($ghostText ?? ''); ?>"
                            data-gm-notes="<?php echo htmlspecialchars($gmNotes ?? ''); ?>"
                            data-source-file="<?php echo htmlspecialchars($sourceFile); ?>"
                            data-targets="<?php echo htmlspecialchars(implode(', ', $targets)); ?>"
                            data-source-types="<?php echo htmlspecialchars(implode(', ', $sourceTypes)); ?>"
                            data-delivery-contexts="<?php echo htmlspecialchars(implode(', ', $deliveryContexts)); ?>"
                            data-plot-ids="<?php echo htmlspecialchars(implode(', ', $plotIds)); ?>"
                            data-prerequisites="<?php echo htmlspecialchars(json_encode($prerequisites)); ?>"
                            data-weight-modifiers="<?php echo htmlspecialchars(json_encode($weightModifiers)); ?>">
                            <td class="character-cell align-top text-light">
                                <strong><?php echo htmlspecialchars($id); ?></strong>
                            </td>
                            <td class="align-top text-center text-nowrap">
                                <?php echo render_category_badge($category); ?>
                            </td>
                            <td class="align-top text-center text-nowrap">
                                <?php echo render_rarity_badge($rarity); ?>
                            </td>
                            <td class="align-top text-start" style="max-width: 200px; width: 200px;">
                                <?php if (!empty($targets)): ?>
                                    <div class="text-truncate" title="<?php echo htmlspecialchars(implode(', ', $targets)); ?>">
                                        <?php echo htmlspecialchars(truncate_text(implode(', ', $targets), 30)); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-top text-start" style="max-width: 150px; width: 150px;">
                                <?php if (!empty($sourceTypes)): ?>
                                    <div class="text-truncate" title="<?php echo htmlspecialchars(implode(', ', $sourceTypes)); ?>">
                                        <?php echo htmlspecialchars(truncate_text(implode(', ', $sourceTypes), 20)); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-top text-start" style="max-width: 300px; width: 300px;">
                                <div class="text-truncate" title="<?php echo htmlspecialchars($text); ?>">
                                    <?php echo htmlspecialchars(truncate_text($text, 60)); ?>
                                </div>
                            </td>
                            <td class="actions text-center align-top" style="width: 120px;">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Rumor actions">
                                    <button class="action-btn view-btn btn btn-primary" 
                                            data-id="<?php echo htmlspecialchars($id); ?>"
                                            title="View Details">👁️</button>
                                    <button class="action-btn edit-btn btn btn-warning" 
                                            data-id="<?php echo htmlspecialchars($id); ?>"
                                            title="Edit Details">✏️</button>
                                    <button class="action-btn delete-btn btn btn-danger" 
                                            data-id="<?php echo htmlspecialchars($id); ?>"
                                            data-text="<?php echo htmlspecialchars(truncate_text($text, 50)); ?>"
                                            title="Delete Rumor">🗑️</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Pagination Controls -->
    <div class="pagination-controls d-flex flex-column flex-lg-row gap-3 align-items-center justify-content-between mt-4" id="paginationControls">
        <div class="pagination-info fw-semibold">
            <span id="paginationInfoFooter"></span>
        </div>
        <div class="pagination-buttons d-flex flex-wrap gap-2 justify-content-center" id="paginationButtons">
            <!-- Buttons will be generated by JavaScript -->
        </div>
    </div>
    
    <!-- View Rumor Modal -->
    <div class="modal fade" id="viewRumorModal" tabindex="-1" aria-labelledby="viewRumorTitle" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content character-view-modal">
                <div class="modal-header align-items-start flex-wrap gap-2">
                    <div class="d-flex flex-column">
                        <h5 class="modal-title d-flex align-items-center gap-2" id="viewRumorTitle">
                            <span aria-hidden="true">📰</span>
                            <span>Rumor Details</span>
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="viewRumorContent" class="view-content" aria-live="polite">
                        Loading...
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- PC Rumor History Section -->
    <div class="mt-5 pt-4 border-top border-danger">
        <h2 class="h4 text-light mb-3">PC Rumor History Lookup</h2>
        <form method="get" action="rumor_viewer.php" class="row g-3 mb-4">
            <input type="hidden" name="file" value="<?php echo htmlspecialchars($selectedFile); ?>">
            <div class="col-12 col-md-4">
                <label for="pcIdInput" class="text-light text-uppercase small mb-2 d-block">PC ID:</label>
                <input type="text" id="pcIdInput" name="pc_id" class="form-control form-control-sm bg-dark text-light border-danger" 
                       value="<?php echo htmlspecialchars($pcId); ?>" placeholder="Enter PC ID...">
            </div>
            <div class="col-12 col-md-2">
                <label class="text-light text-uppercase small mb-2 d-block">&nbsp;</label>
                <button type="submit" class="btn btn-outline-danger btn-sm w-100">Load History</button>
            </div>
        </form>
        
        <?php if (!empty($pcId)): ?>
            <?php if ($historyError): ?>
                <div class="alert alert-warning">
                    <p class="mb-0"><?php echo $historyError; ?></p>
                </div>
            <?php elseif ($history !== null): ?>
                <?php if (empty($history)): ?>
                    <div class="alert alert-info">
                        <p class="mb-0">History file exists but contains no entries.</p>
                    </div>
                <?php else: ?>
                    <div class="rumor-history-wrapper table-responsive rounded-3">
                        <table class="rumor-history-table table table-dark table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-start">Rumor ID</th>
                                    <th class="text-center">Times Shown</th>
                                    <th class="text-center">Last Shown Night</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $rumorId => $entry): ?>
                                    <?php
                                    $timesShown = $entry['times_shown'] ?? 0;
                                    $lastShownNight = $entry['last_shown_night'] ?? null;
                                    ?>
                                    <tr>
                                        <td class="text-start"><strong><?php echo htmlspecialchars($rumorId); ?></strong></td>
                                        <td class="text-center"><?php echo htmlspecialchars($timesShown); ?></td>
                                        <td class="text-center">
                                            <?php echo $lastShownNight !== null ? htmlspecialchars($lastShownNight) : '<span class="text-muted">Never</span>'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.admin-panel-container { max-width: 1600px; margin: 0 auto; }
.panel-title { font-family: var(--font-brand), 'IM Fell English', serif; }
.panel-subtitle { font-family: var(--font-body), 'Source Serif Pro', serif; }

.nav-btn { background: rgba(139, 0, 0, 0.2); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 5px; color: #b8a090; font-family: var(--font-body), 'Source Serif Pro', serif; font-weight: 600; text-decoration: none; transition: all 0.3s ease; }
.nav-btn:hover { background: rgba(139, 0, 0, 0.3); border-color: #8B0000; color: #f5e6d3; }
.nav-btn.active { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); border-color: #b30000; color: #f5e6d3; }

/* Filter controls - Category and Rarity at 50% width */
@media (min-width: 768px) {
    .filter-category,
    .filter-rarity {
        flex: 0 0 12.5% !important;
        max-width: 12.5% !important;
    }
}

/* Use same styling as character-table from admin_panel.php */
.character-table-wrapper { 
    background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%); 
    border: 2px solid #8B0000; 
    border-radius: 8px; 
    overflow-x: auto; 
    overflow-y: visible;
    scrollbar-width: thin;
    scrollbar-color: rgba(139, 0, 0, 0.6) rgba(26, 15, 15, 0.3);
    padding: 0 !important;
    margin-left: -20px;
    margin-right: -20px;
    width: calc(100% + 40px);
    box-sizing: border-box;
}

.character-table-wrapper.table-responsive {
    padding: 0 !important;
    margin-left: -20px !important;
    margin-right: -20px !important;
    width: calc(100% + 40px) !important;
}

.character-table-wrapper .character-table {
    width: 100% !important;
    margin: 0 !important;
}

.character-table { 
    width: 100%; 
    border-collapse: collapse; 
    margin: 0;
    padding: 0;
    table-layout: fixed;
}

/* Ensure column widths match between headers and cells */
#rumorTable th:nth-child(1),
#rumorTable td:nth-child(1) {
    width: 360px !important;
    min-width: 360px !important;
}

#rumorTable th:nth-child(2),
#rumorTable td:nth-child(2) {
    width: 120px !important;
    min-width: 120px !important;
}

#rumorTable th:nth-child(3),
#rumorTable td:nth-child(3) {
    width: 140px !important;
    min-width: 140px !important;
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
.character-table tbody tr.rumor-row:hover { cursor: default; }
.text-truncate { 
    display: block; 
    width: 100%;
    overflow: hidden; 
    text-overflow: ellipsis; 
    white-space: nowrap; 
}
.character-table tbody tr.hidden { display: none; }
.character-table td { padding: 12px; font-family: var(--font-body), 'Source Serif Pro', serif; color: #d4c4b0; }
.character-table td strong { color: #f5e6d3; font-size: 1.05em; }
.character-cell { position: relative; }

/* Action buttons */
.actions { 
    display: flex; 
    gap: 8px; 
    justify-content: flex-end; 
    align-items: center; 
    margin: 0;
    padding: 0;
}
.action-btn { 
    display: inline-flex; 
    align-items: center; 
    justify-content: center; 
    width: 32px; 
    height: 32px; 
    border-radius: 4px; 
    text-decoration: none; 
    font-size: 1.1em; 
    cursor: pointer; 
    background: rgba(139, 0, 0, 0.2); 
    border: 1px solid rgba(139, 0, 0, 0.4); 
    transition: all 0.2s; 
    flex-shrink: 0; 
}
.action-btn:hover { background: rgba(139, 0, 0, 0.4); transform: scale(1.1); }
.view-btn { background: rgba(0, 100, 200, 0.2); border-color: rgba(0, 100, 200, 0.4); }
.view-btn:hover { background: rgba(0, 100, 200, 0.4); }
.delete-btn { 
    display: inline-flex !important;
    background: rgba(139, 0, 0, 0.2); 
    border-color: rgba(139, 0, 0, 0.4); 
}
.delete-btn:hover { background: rgba(139, 0, 0, 0.4); }

/* Actions column fixed width */
.character-table th:last-child {
    padding: 15px 6px 15px 12px !important;
    text-align: center !important;
    width: 120px !important;
}

.character-table td.actions {
    padding: 12px 6px 12px 12px !important;
    text-align: right !important;
    overflow: visible;
    width: 120px !important;
    max-width: 120px !important;
}


.rumor-history-wrapper {
    background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%); 
    border: 2px solid #8B0000; 
    border-radius: 8px; 
    overflow-x: auto;
}

.rumor-history-table {
    width: 100%;
    border-collapse: collapse;
}

.rumor-history-table thead { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); }
.rumor-history-table th { padding: 15px 12px; text-align: left; font-family: var(--font-title), 'Libre Baskerville', serif; color: #f5e6d3; font-weight: 700; }
.rumor-history-table tbody tr { border-bottom: 1px solid rgba(139, 0, 0, 0.2); }
.rumor-history-table tbody tr:hover { background: rgba(139, 0, 0, 0.15); }
.rumor-history-table td { padding: 12px; font-family: var(--font-body), 'Source Serif Pro', serif; color: #d4c4b0; }

/* View Rumor Modal - Use same styling as character-view-modal */
.character-view-modal {
    background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%);
    border: 3px solid #8B0000;
    border-radius: 12px;
    color: #d4c4b0;
}
.character-view-modal .modal-header {
    border-bottom: 2px solid rgba(139, 0, 0, 0.3);
    margin-bottom: 1rem;
    padding-bottom: 1rem;
}
.character-view-modal .modal-title {
    font-family: var(--font-brand), 'IM Fell English', serif;
    color: #f5e6d3;
    font-size: 1.75rem;
    margin: 0;
}
.character-view-modal .btn-close {
    filter: invert(1) grayscale(100%);
    opacity: 0.75;
}
.character-view-modal .btn-close:hover {
    opacity: 1;
}
.view-content { 
    color: #d4c4b0; 
    font-family: var(--font-body), 'Source Serif Pro', serif; 
    line-height: 1.6; 
    margin-top: 20px;
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
.view-content h4 {
    color: #d4c4b0;
    font-family: var(--font-title), 'Libre Baskerville', serif;
    font-size: 1.1em;
    margin-top: 15px;
    margin-bottom: 8px;
    border-bottom: 1px solid rgba(139, 0, 0, 0.3);
    padding-bottom: 5px;
}
.view-content p { margin: 10px 0; }
.view-content strong { color: #b8a090; }

/* Pagination controls */
.pagination-controls { margin-top: 20px; padding: 15px; background: rgba(26, 15, 15, 0.3); border-radius: 5px; }
.pagination-info { font-family: var(--font-body), 'Source Serif Pro', serif; color: #b8a090; }
.page-btn { padding: 8px 12px; background: rgba(139, 0, 0, 0.2); border: 2px solid rgba(139, 0, 0, 0.4); border-radius: 4px; color: #b8a090; font-family: var(--font-body), 'Source Serif Pro', serif; cursor: pointer; transition: all 0.2s; }
.page-btn:hover { background: rgba(139, 0, 0, 0.3); border-color: #8B0000; color: #f5e6d3; }
.page-btn.active { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); border-color: #b30000; color: #f5e6d3; }

@media (max-width: 768px) {
    .character-table-wrapper {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .character-table-wrapper::-webkit-scrollbar {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileFilter = document.getElementById('fileFilter');
    const categoryFilter = document.getElementById('categoryFilter');
    const rarityFilter = document.getElementById('rarityFilter');
    const searchBox = document.getElementById('searchBox');
    const pageSizeSelect = document.getElementById('pageSize');
    const rumorRows = document.querySelectorAll('.rumor-row');
    const paginationInfo = document.getElementById('paginationInfo');
    const paginationInfoFooter = document.getElementById('paginationInfoFooter');
    const totalCount = <?php echo count($rumors); ?>;
    
    // Pagination state
    let currentPage = 1;
    let pageSize = 20;
    const pageSizeStorageKey = 'rumorViewerPageSize';
    
    // Load saved page size
    function loadSavedPageSize() {
        const stored = sessionStorage.getItem(pageSizeStorageKey);
        if (!stored) {
            return;
        }
        const parsed = parseInt(stored, 10);
        if (!Number.isFinite(parsed) || parsed <= 0) {
            return;
        }
        pageSize = parsed;
        if (pageSizeSelect) {
            const optionExists = Array.from(pageSizeSelect.options).some(option => parseInt(option.value, 10) === parsed);
            if (optionExists) {
                pageSizeSelect.value = String(parsed);
            }
        }
    }
    
    // Persist page size
    function persistPageSize() {
        try {
            sessionStorage.setItem(pageSizeStorageKey, String(pageSize));
        } catch (error) {
            console.error('Unable to persist page size', error);
        }
    }
    
    // Initialize page size
    loadSavedPageSize();
    
    // File filter - reload page
    if (fileFilter) {
        fileFilter.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('file', this.value);
            window.location.href = url.toString();
        });
    }
    
    // View button - open modal with rumor details
    document.querySelectorAll('.view-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const rumorRow = this.closest('.rumor-row');
            if (!rumorRow) return;
            
            const rumorId = rumorRow.getAttribute('data-id');
            const category = rumorRow.getAttribute('data-category');
            const rarity = rumorRow.getAttribute('data-rarity');
            const text = rumorRow.getAttribute('data-text');
            const ghostText = rumorRow.getAttribute('data-ghost-text');
            const gmNotes = rumorRow.getAttribute('data-gm-notes');
            const targets = rumorRow.getAttribute('data-targets');
            const sourceTypes = rumorRow.getAttribute('data-source-types');
            const sourceFile = rumorRow.getAttribute('data-source-file');
            const prerequisitesJson = rumorRow.getAttribute('data-prerequisites');
            const weightModifiersJson = rumorRow.getAttribute('data-weight-modifiers');
            
            let prerequisites = '';
            let weightModifiers = '';
            
            // Parse JSON data
            try {
                if (prerequisitesJson) {
                    const prereqObj = JSON.parse(prerequisitesJson);
                    if (prereqObj && Object.keys(prereqObj).length > 0) {
                        prerequisites = JSON.stringify(prereqObj, null, 2);
                    }
                }
            } catch (e) {
                // Invalid JSON, leave empty
            }
            
            try {
                if (weightModifiersJson) {
                    const modObj = JSON.parse(weightModifiersJson);
                    if (modObj && Object.keys(modObj).length > 0) {
                        weightModifiers = JSON.stringify(modObj, null, 2);
                    }
                }
            } catch (e) {
                // Invalid JSON, leave empty
            }
            
            // Build modal content
            let modalHtml = '<div class="rumor-modal-content">';
            
            modalHtml += '<div class="mb-4">';
            modalHtml += '<h3 class="text-light mb-3">' + escapeHtml(rumorId) + '</h3>';
            modalHtml += '<div class="d-flex flex-wrap gap-2 mb-3">';
            modalHtml += renderCategoryBadge(category);
            modalHtml += renderRarityBadge(rarity);
            modalHtml += '</div>';
            modalHtml += '</div>';
            
            modalHtml += '<div class="mb-3">';
            modalHtml += '<h4 class="text-light mb-2">Text:</h4>';
            modalHtml += '<p class="text-light ms-2">' + nl2br(escapeHtml(text || '—')) + '</p>';
            modalHtml += '</div>';
            
            if (ghostText && ghostText !== '') {
                modalHtml += '<div class="mb-3">';
                modalHtml += '<h4 class="text-light mb-2">Ghost Text:</h4>';
                modalHtml += '<p class="text-light ms-2">' + nl2br(escapeHtml(ghostText)) + '</p>';
                modalHtml += '</div>';
            }
            
            if (gmNotes && gmNotes !== '') {
                modalHtml += '<div class="mb-3">';
                modalHtml += '<h4 class="text-light mb-2">GM Notes:</h4>';
                modalHtml += '<p class="text-light ms-2">' + nl2br(escapeHtml(gmNotes)) + '</p>';
                modalHtml += '</div>';
            }
            
            modalHtml += '<div class="mb-3">';
            modalHtml += '<h4 class="text-light mb-2">Targets:</h4>';
            modalHtml += '<p class="text-light ms-2">' + (targets ? escapeHtml(targets) : '<span class="text-muted">—</span>') + '</p>';
            modalHtml += '</div>';
            
            modalHtml += '<div class="mb-3">';
            modalHtml += '<h4 class="text-light mb-2">Source Types:</h4>';
            modalHtml += '<p class="text-light ms-2">' + (sourceTypes ? escapeHtml(sourceTypes) : '<span class="text-muted">—</span>') + '</p>';
            modalHtml += '</div>';
            
            modalHtml += '<div class="mb-3">';
            modalHtml += '<h4 class="text-light mb-2">Delivery Contexts:</h4>';
            const deliveryContexts = rumorRow.getAttribute('data-delivery-contexts');
            modalHtml += '<p class="text-light ms-2">' + (deliveryContexts ? escapeHtml(deliveryContexts) : '<span class="text-muted">—</span>') + '</p>';
            modalHtml += '</div>';
            
            modalHtml += '<div class="mb-3">';
            modalHtml += '<h4 class="text-light mb-2">Plot IDs:</h4>';
            const plotIds = rumorRow.getAttribute('data-plot-ids');
            modalHtml += '<p class="text-light ms-2">' + (plotIds ? escapeHtml(plotIds) : '<span class="text-muted">—</span>') + '</p>';
            modalHtml += '</div>';
            
            modalHtml += '<div class="mb-3">';
            modalHtml += '<h4 class="text-light mb-2">Source File:</h4>';
            modalHtml += '<p class="text-light ms-2">' + (sourceFile && sourceFile !== '—' ? escapeHtml(sourceFile) : '<span class="text-muted">—</span>') + '</p>';
            modalHtml += '</div>';
            
            if (prerequisites && prerequisites.trim() !== '') {
                modalHtml += '<div class="mb-3">';
                modalHtml += '<h4 class="text-light mb-2">Prerequisites:</h4>';
                modalHtml += '<pre class="bg-dark text-light p-3 rounded mt-2" style="font-size: 0.85em; max-height: 300px; overflow-y: auto;">' + escapeHtml(prerequisites) + '</pre>';
                modalHtml += '</div>';
            }
            
            if (weightModifiers && weightModifiers.trim() !== '') {
                modalHtml += '<div class="mb-3">';
                modalHtml += '<h4 class="text-light mb-2">Weight Modifiers:</h4>';
                modalHtml += '<pre class="bg-dark text-light p-3 rounded mt-2" style="font-size: 0.85em; max-height: 300px; overflow-y: auto;">' + escapeHtml(weightModifiers) + '</pre>';
                modalHtml += '</div>';
            }
            
            modalHtml += '</div>';
            
            // Update modal
            const modalTitle = document.getElementById('viewRumorTitle');
            const modalContent = document.getElementById('viewRumorContent');
            
            if (modalTitle) {
                modalTitle.textContent = 'Rumor: ' + rumorId;
            }
            
            if (modalContent) {
                modalContent.innerHTML = modalHtml;
            }
            
            // Show modal
            const modalEl = document.getElementById('viewRumorModal');
            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const viewModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl, {
                    backdrop: true,
                    focus: true,
                    keyboard: true
                });
                
                // Show modal - Bootstrap handles aria-hidden automatically
                viewModalInstance.show();
                
                // After modal is fully shown, ensure ARIA attributes are correct
                modalEl.addEventListener('shown.bs.modal', function() {
                    // Bootstrap should have removed aria-hidden by now
                    // Just ensure aria-modal is set
                    modalEl.setAttribute('aria-modal', 'true');
                }, { once: true });
            }
        });
    });
    
    // Edit button - open same modal as view
    document.querySelectorAll('.edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const rumorRow = this.closest('.rumor-row');
            if (!rumorRow) return;
            
            const rumorId = rumorRow.getAttribute('data-id');
            const category = rumorRow.getAttribute('data-category');
            const rarity = rumorRow.getAttribute('data-rarity');
            const text = rumorRow.getAttribute('data-text');
            const ghostText = rumorRow.getAttribute('data-ghost-text');
            const gmNotes = rumorRow.getAttribute('data-gm-notes');
            const targets = rumorRow.getAttribute('data-targets');
            const sourceTypes = rumorRow.getAttribute('data-source-types');
            const sourceFile = rumorRow.getAttribute('data-source-file');
            const prerequisitesJson = rumorRow.getAttribute('data-prerequisites');
            const weightModifiersJson = rumorRow.getAttribute('data-weight-modifiers');
            
            let prerequisites = '';
            let weightModifiers = '';
            
            // Parse JSON data
            try {
                if (prerequisitesJson) {
                    const prereqObj = JSON.parse(prerequisitesJson);
                    if (prereqObj && Object.keys(prereqObj).length > 0) {
                        prerequisites = JSON.stringify(prereqObj, null, 2);
                    }
                }
            } catch (e) {
                // Invalid JSON, leave empty
            }
            
            try {
                if (weightModifiersJson) {
                    const modObj = JSON.parse(weightModifiersJson);
                    if (modObj && Object.keys(modObj).length > 0) {
                        weightModifiers = JSON.stringify(modObj, null, 2);
                    }
                }
            } catch (e) {
                // Invalid JSON, leave empty
            }
            
            // Build modal content
            let modalHtml = '<div class="rumor-modal-content">';
            
            modalHtml += '<div class="mb-4">';
            modalHtml += '<h3 class="text-light mb-3">' + escapeHtml(rumorId) + '</h3>';
            modalHtml += '<div class="d-flex flex-wrap gap-2 mb-3">';
            modalHtml += renderCategoryBadge(category);
            modalHtml += renderRarityBadge(rarity);
            modalHtml += '</div>';
            modalHtml += '</div>';
            
            modalHtml += '<div class="mb-3">';
            modalHtml += '<h4 class="text-light mb-2">Text:</h4>';
            modalHtml += '<p class="text-light ms-2">' + nl2br(escapeHtml(text || '—')) + '</p>';
            modalHtml += '</div>';
            
            if (ghostText && ghostText !== '') {
                modalHtml += '<div class="mb-3">';
                modalHtml += '<h4 class="text-light mb-2">Ghost Text:</h4>';
                modalHtml += '<p class="text-light ms-2">' + nl2br(escapeHtml(ghostText)) + '</p>';
                modalHtml += '</div>';
            }
            
            if (gmNotes && gmNotes !== '') {
                modalHtml += '<div class="mb-3">';
                modalHtml += '<h4 class="text-light mb-2">GM Notes:</h4>';
                modalHtml += '<p class="text-light ms-2">' + nl2br(escapeHtml(gmNotes)) + '</p>';
                modalHtml += '</div>';
            }
            
            modalHtml += '<div class="mb-3">';
            modalHtml += '<h4 class="text-light mb-2">Targets:</h4>';
            modalHtml += '<p class="text-light ms-2">' + (targets ? escapeHtml(targets) : '<span class="text-muted">—</span>') + '</p>';
            modalHtml += '</div>';
            
            modalHtml += '<div class="mb-3">';
            modalHtml += '<h4 class="text-light mb-2">Source Types:</h4>';
            modalHtml += '<p class="text-light ms-2">' + (sourceTypes ? escapeHtml(sourceTypes) : '<span class="text-muted">—</span>') + '</p>';
            modalHtml += '</div>';
            
            modalHtml += '<div class="mb-3">';
            modalHtml += '<h4 class="text-light mb-2">Delivery Contexts:</h4>';
            const deliveryContexts = rumorRow.getAttribute('data-delivery-contexts');
            modalHtml += '<p class="text-light ms-2">' + (deliveryContexts ? escapeHtml(deliveryContexts) : '<span class="text-muted">—</span>') + '</p>';
            modalHtml += '</div>';
            
            modalHtml += '<div class="mb-3">';
            modalHtml += '<h4 class="text-light mb-2">Plot IDs:</h4>';
            const plotIds = rumorRow.getAttribute('data-plot-ids');
            modalHtml += '<p class="text-light ms-2">' + (plotIds ? escapeHtml(plotIds) : '<span class="text-muted">—</span>') + '</p>';
            modalHtml += '</div>';
            
            modalHtml += '<div class="mb-3">';
            modalHtml += '<h4 class="text-light mb-2">Source File:</h4>';
            modalHtml += '<p class="text-light ms-2">' + (sourceFile && sourceFile !== '—' ? escapeHtml(sourceFile) : '<span class="text-muted">—</span>') + '</p>';
            modalHtml += '</div>';
            
            if (prerequisites && prerequisites.trim() !== '') {
                modalHtml += '<div class="mb-3">';
                modalHtml += '<h4 class="text-light mb-2">Prerequisites:</h4>';
                modalHtml += '<pre class="bg-dark text-light p-3 rounded mt-2" style="font-size: 0.85em; max-height: 300px; overflow-y: auto;">' + escapeHtml(prerequisites) + '</pre>';
                modalHtml += '</div>';
            }
            
            if (weightModifiers && weightModifiers.trim() !== '') {
                modalHtml += '<div class="mb-3">';
                modalHtml += '<h4 class="text-light mb-2">Weight Modifiers:</h4>';
                modalHtml += '<pre class="bg-dark text-light p-3 rounded mt-2" style="font-size: 0.85em; max-height: 300px; overflow-y: auto;">' + escapeHtml(weightModifiers) + '</pre>';
                modalHtml += '</div>';
            }
            
            modalHtml += '</div>';
            
            // Update modal
            const modalTitle = document.getElementById('viewRumorTitle');
            const modalContent = document.getElementById('viewRumorContent');
            
            if (modalTitle) {
                modalTitle.textContent = 'Rumor: ' + rumorId + ' (Edit)';
            }
            
            if (modalContent) {
                modalContent.innerHTML = modalHtml;
            }
            
            // Show modal
            const modalEl = document.getElementById('viewRumorModal');
            if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const viewModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl, {
                    backdrop: true,
                    focus: true,
                    keyboard: true
                });
                
                // Show modal - Bootstrap handles aria-hidden automatically
                viewModalInstance.show();
                
                // After modal is fully shown, ensure ARIA attributes are correct
                modalEl.addEventListener('shown.bs.modal', function() {
                    // Bootstrap should have removed aria-hidden by now
                    // Just ensure aria-modal is set
                    modalEl.setAttribute('aria-modal', 'true');
                }, { once: true });
            }
        });
    });
    
    // Helper functions for modal
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function nl2br(text) {
        if (!text) return '';
        return escapeHtml(text).replace(/\n/g, '<br>');
    }
    
    // Render rarity badge with same colors as PHP function
    function renderRarityBadge(rarity) {
        if (!rarity) return '—';
        
        const rarityLower = rarity.toLowerCase().trim();
        
        const rarityMap = {
            'common': { color: '#8B8989', bg: 'rgba(139, 137, 137, 0.2)', border: 'rgba(139, 137, 137, 0.5)' },
            'uncommon': { color: '#228B22', bg: 'rgba(34, 139, 34, 0.2)', border: 'rgba(34, 139, 34, 0.5)' },
            'rare': { color: '#FFD700', bg: 'rgba(255, 215, 0, 0.2)', border: 'rgba(255, 215, 0, 0.5)' },
            'very rare': { color: '#FF4500', bg: 'rgba(255, 69, 0, 0.2)', border: 'rgba(255, 69, 0, 0.5)' },
            'legendary': { color: '#9370DB', bg: 'rgba(147, 112, 219, 0.2)', border: 'rgba(147, 112, 219, 0.5)' }
        };
        
        const style = rarityMap[rarityLower] || rarityMap['common'];
        const displayText = rarity.charAt(0).toUpperCase() + rarity.slice(1);
        
        return `<span class="rarity-badge" style="background: ${style.bg}; border: 1px solid ${style.border}; color: ${style.color}; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; white-space: nowrap;">${escapeHtml(displayText)}</span>`;
    }
    
    // Render category badge with same colors as PHP function
    function renderCategoryBadge(category) {
        if (!category) return '—';
        
        const categoryTrimmed = category.trim();
        
        const categoryMap = {
            'Media': { color: '#4A90E2', bg: 'rgba(74, 144, 226, 0.2)', border: 'rgba(74, 144, 226, 0.5)' },
            'Street': { color: '#FF6B35', bg: 'rgba(255, 107, 53, 0.2)', border: 'rgba(255, 107, 53, 0.5)' },
            'Elysium': { color: '#8B4513', bg: 'rgba(139, 69, 19, 0.2)', border: 'rgba(139, 69, 19, 0.5)' },
            'Politics': { color: '#8B0000', bg: 'rgba(139, 0, 0, 0.2)', border: 'rgba(139, 0, 0, 0.5)' },
            'Society': { color: '#9370DB', bg: 'rgba(147, 112, 219, 0.2)', border: 'rgba(147, 112, 219, 0.5)' },
            'Crime': { color: '#DC143C', bg: 'rgba(220, 20, 60, 0.2)', border: 'rgba(220, 20, 60, 0.5)' },
            'Supernatural': { color: '#6A0DAD', bg: 'rgba(106, 13, 173, 0.2)', border: 'rgba(106, 13, 173, 0.5)' }
        };
        
        const style = categoryMap[categoryTrimmed] || { color: '#b8a090', bg: 'rgba(139, 0, 0, 0.2)', border: 'rgba(139, 0, 0, 0.4)' };
        
        return `<span class="category-badge" style="background: ${style.bg}; border: 1px solid ${style.border}; color: ${style.color}; padding: 4px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; white-space: nowrap;">${escapeHtml(categoryTrimmed)}</span>`;
    }
    
    // Initialize sorting
    initializeRumorSorting();
    
    // Initialize delete buttons
    initializeDeleteButtons();
    
    // Filtering function
    function applyFilters(resetPage = true) {
        const categoryValue = categoryFilter.value;
        const rarityValue = rarityFilter.value;
        const searchValue = searchBox.value.toLowerCase().trim();
        
        let visibleRows = [];
        
        rumorRows.forEach(function(row) {
            let show = true;
            
            // Category filter
            if (categoryValue !== 'all') {
                if (row.getAttribute('data-category') !== categoryValue) {
                    show = false;
                }
            }
            
            // Rarity filter
            if (rarityValue !== 'all' && show) {
                if (row.getAttribute('data-rarity') !== rarityValue) {
                    show = false;
                }
            }
            
            // Search filter
            if (searchValue !== '' && show) {
                const text = (row.getAttribute('data-text') || '').toLowerCase();
                const ghostText = (row.getAttribute('data-ghost-text') || '').toLowerCase();
                const gmNotes = (row.getAttribute('data-gm-notes') || '').toLowerCase();
                
                if (!text.includes(searchValue) && 
                    !ghostText.includes(searchValue) && 
                    !gmNotes.includes(searchValue)) {
                    show = false;
                }
            }
            
            if (show) {
                row.classList.remove('hidden', 'filtered-out');
                visibleRows.push(row);
            } else {
                row.classList.add('hidden', 'filtered-out');
                // Also hide details row if visible
                const rumorId = row.getAttribute('data-id');
                const detailsRow = document.getElementById('details-' + rumorId);
                if (detailsRow) {
                    detailsRow.classList.add('hidden');
                }
            }
        });
        
        if (resetPage) {
            currentPage = 1;
        } else {
            const totalPages = Math.max(1, Math.ceil(visibleRows.length / pageSize));
            if (currentPage > totalPages) {
                currentPage = totalPages;
            }
        }
        
        updatePagination(visibleRows);
    }
    
    // Update pagination
    function updatePagination(visibleRows = null) {
        if (!visibleRows) {
            visibleRows = Array.from(document.querySelectorAll('.rumor-row:not(.filtered-out)'));
        }
        
        const totalVisible = visibleRows.length;
        const totalPages = Math.max(1, Math.ceil(totalVisible / pageSize));
        
        // Hide all rows first
        document.querySelectorAll('.rumor-row').forEach(row => {
            row.classList.add('hidden');
        });
        
        // Show only rows for current page
        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = Math.min(startIndex + pageSize, totalVisible);
        
        for (let i = startIndex; i < endIndex; i++) {
            if (visibleRows[i]) {
                visibleRows[i].classList.remove('hidden');
            }
        }
        
        // Update pagination info
        const showing = totalVisible === 0 ? 0 : startIndex + 1;
        const infoText = `Showing ${showing}-${endIndex} of ${totalVisible} rumors`;
        if (paginationInfo) {
            paginationInfo.textContent = infoText;
        }
        if (paginationInfoFooter) {
            paginationInfoFooter.textContent = infoText;
        }
        
        // Generate pagination buttons
        const buttonsDiv = document.getElementById('paginationButtons');
        if (!buttonsDiv) return;
        
        buttonsDiv.innerHTML = '';
        
        if (totalPages <= 1) return;
        
        // Previous button
        if (currentPage > 1) {
            const prevBtn = createPageButton('← Prev', currentPage - 1);
            buttonsDiv.appendChild(prevBtn);
        }
        
        // Page number buttons
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                const pageBtn = createPageButton(i, i);
                if (i === currentPage) pageBtn.classList.add('active');
                buttonsDiv.appendChild(pageBtn);
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                const dots = document.createElement('span');
                dots.textContent = '...';
                dots.style.color = '#666';
                dots.style.padding = '8px 12px';
                buttonsDiv.appendChild(dots);
            }
        }
        
        // Next button
        if (currentPage < totalPages) {
            const nextBtn = createPageButton('Next →', currentPage + 1);
            buttonsDiv.appendChild(nextBtn);
        }
    }
    
    // Create page button
    function createPageButton(text, page) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'page-btn';
        btn.textContent = text;
        btn.addEventListener('click', function() {
            currentPage = page;
            applyFilters(false);
        });
        return btn;
    }
    
    // Sorting functionality
    function initializeRumorSorting() {
        const headers = document.querySelectorAll('.character-table th[data-sort]');
        let currentSortColumn = null;
        let currentSortDirection = 'asc';
        
        headers.forEach(header => {
            header.addEventListener('click', function() {
                const column = this.dataset.sort;
                
                // Toggle direction if same column, otherwise start with ascending
                if (currentSortColumn === column) {
                    currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSortColumn = column;
                    currentSortDirection = 'asc';
                }
                
                // Update header styling
                headers.forEach(h => {
                    h.classList.remove('sorted-asc', 'sorted-desc');
                });
                this.classList.add('sorted-' + currentSortDirection);
                
                // Sort table
                sortRumorTable(column, currentSortDirection);
            });
        });
    }
    
    function sortRumorTable(column, direction) {
        const tbody = document.querySelector('#rumorTable tbody');
        const rows = Array.from(tbody.querySelectorAll('.rumor-row'));
        
        rows.sort((a, b) => {
            let aVal = '';
            let bVal = '';

            switch(column) {
                case 'id':
                    aVal = (a.dataset.id || '').toLowerCase();
                    bVal = (b.dataset.id || '').toLowerCase();
                    break;
                case 'category':
                    aVal = (a.dataset.category || '').toLowerCase();
                    bVal = (b.dataset.category || '').toLowerCase();
                    break;
                case 'rarity':
                    const rarityOrder = {'common': 1, 'uncommon': 2, 'rare': 3, 'very rare': 4, 'legendary': 5};
                    aVal = rarityOrder[a.dataset.rarity?.toLowerCase()] || 0;
                    bVal = rarityOrder[b.dataset.rarity?.toLowerCase()] || 0;
                    break;
                case 'targets':
                    aVal = (a.dataset.targets || '').toLowerCase();
                    bVal = (b.dataset.targets || '').toLowerCase();
                    break;
                case 'source_types':
                    aVal = (a.dataset.sourceTypes || '').toLowerCase();
                    bVal = (b.dataset.sourceTypes || '').toLowerCase();
                    break;
                case 'text':
                    aVal = (a.dataset.text || '').toLowerCase();
                    bVal = (b.dataset.text || '').toLowerCase();
                    break;
                default:
                    aVal = (a.dataset.id || '').toLowerCase();
                    bVal = (b.dataset.id || '').toLowerCase();
            }
            
            let comparison = 0;
            if (aVal > bVal) comparison = 1;
            if (aVal < bVal) comparison = -1;
            
            return direction === 'asc' ? comparison : -comparison;
        });
        
        // Re-append rows and their detail rows in sorted order
        rows.forEach(row => {
            const rumorId = row.dataset.id;
            const detailsRow = document.getElementById('details-' + rumorId);
            tbody.appendChild(row);
            if (detailsRow) {
                tbody.appendChild(detailsRow);
            }
        });
    }
    
    // Delete functionality
    function initializeDeleteButtons() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const rumorId = this.dataset.id;
                const rumorText = this.dataset.text || rumorId;
                
                if (confirm('Are you sure you want to delete this rumor?\n\n' + rumorText + '\n\nThis action cannot be undone.')) {
                    // TODO: Implement delete functionality
                    // For now, this is a placeholder
                    alert('Delete functionality not yet implemented. This is a read-only viewer.');
                }
            });
        });
    }
    
    // Page size change handler
    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', function() {
            pageSize = parseInt(this.value, 10);
            currentPage = 1;
            persistPageSize();
            applyFilters(false);
        });
    }
    
    // Apply filters on change
    if (categoryFilter) {
        categoryFilter.addEventListener('change', function() {
            applyFilters(true);
        });
    }
    if (rarityFilter) {
        rarityFilter.addEventListener('change', function() {
            applyFilters(true);
        });
    }
    if (searchBox) {
        searchBox.addEventListener('input', function() {
            applyFilters(true);
        });
    }
    
    // Initial pagination
    updatePagination();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

