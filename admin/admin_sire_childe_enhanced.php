<?php
/**
 * Enhanced Admin Panel - Sire/Childe Relationship Tracker with Biography Analysis
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

<link rel="stylesheet" href="../css/admin_sire_childe.css">

<div class="admin-panel-container">
    <h1 class="panel-title">🧛 Enhanced Sire/Childe Relationships</h1>
    <p class="panel-subtitle">Track vampire lineage with biography analysis and manual verification</p>
    
    <!-- Admin Navigation -->
    <div class="admin-nav">
        <a href="admin_panel.php" class="nav-btn">👥 Characters</a>
        <a href="admin_sire_childe.php" class="nav-btn">🧛 Basic Sire/Childe</a>
        <a href="admin_sire_childe_enhanced.php" class="nav-btn active">🔍 Enhanced Analysis</a>
        <a href="admin_equipment.php" class="nav-btn">⚔️ Equipment</a>
        <a href="admin_locations.php" class="nav-btn">📍 Locations</a>
    </div>
    
    <!-- Analysis Controls -->
    <div class="analysis-controls">
        <div class="control-group">
            <button class="action-btn analyze-btn" onclick="analyzeBiographies()">🔍 Analyze Biographies</button>
            <button class="action-btn verify-btn" onclick="showVerificationPanel()">✅ Verify Relationships</button>
            <button class="action-btn export-btn" onclick="exportRelationships()">📤 Export Data</button>
            <button class="action-btn refresh-btn" onclick="window.location.reload()">🔄 Refresh Page</button>
        </div>
    <div class="analysis-status" id="analysisStatus">
        Ready to analyze character biographies for sire/childe relationships
    </div>
    
    <!-- Future Development Note -->
    <div class="future-note">
        <h4>🔮 Future Development Ideas</h4>
        <ul>
            <li><strong>Family Tree Visualization:</strong> Create an interactive family tree page when we have more characters (10+ vampires) to show sire/childe relationships visually</li>
            <li><strong>Boon Tracker:</strong> New database table and admin interface to track favors, debts, and boons between vampires (who owes what to whom)</li>
            <li><strong>Blood Bond Tracking:</strong> Add fields to track blood bond status and strength between sire/childe pairs</li>
            <li><strong>Generation Analysis:</strong> Show generation distribution and identify potential generation conflicts</li>
            <li><strong>Relationship Timeline:</strong> Track when embraces happened and relationship changes over time</li>
        </ul>
    </div>
    </div>

    <!-- Relationship Statistics -->
    <div class="relationship-stats">
        <?php
        // Get enhanced relationship statistics
        $stats_query = "SELECT 
            COUNT(*) as total_vampires,
            COUNT(CASE WHEN sire IS NOT NULL AND sire != '' THEN 1 END) as with_sire_field,
            COUNT(CASE WHEN sire IS NULL OR sire = '' THEN 1 END) as without_sire_field,
            COUNT(CASE WHEN biography IS NOT NULL AND biography != '' THEN 1 END) as with_biography,
            (SELECT COUNT(*) FROM characters c1 WHERE c1.sire IN (SELECT character_name FROM characters c2 WHERE c1.sire = c2.character_name)) as confirmed_childer
            FROM characters";
        $stats_result = mysqli_query($conn, $stats_query);
        
        if ($stats_result) {
            $stats = mysqli_fetch_assoc($stats_result);
        } else {
            $stats = ['total_vampires' => 0, 'with_sire_field' => 0, 'without_sire_field' => 0, 'with_biography' => 0, 'confirmed_childer' => 0];
        }
        ?>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['total_vampires'] ?? 0; ?></span>
            <span class="stat-label">Total Vampires</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['with_sire_field'] ?? 0; ?></span>
            <span class="stat-label">Sire Field Set</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['with_biography'] ?? 0; ?></span>
            <span class="stat-label">With Biography</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['confirmed_childer'] ?? 0; ?></span>
            <span class="stat-label">Confirmed Childer</span>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="filter-controls">
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All Characters</button>
            <button class="filter-btn" data-filter="needs-analysis">Needs Analysis</button>
            <button class="filter-btn" data-filter="suggestions">Suggestions</button>
            <button class="filter-btn" data-filter="verified">Verified</button>
            <button class="filter-btn" data-filter="conflicts">Conflicts</button>
        </div>
        <div class="search-box">
            <input type="text" id="relationshipSearch" placeholder="🔍 Search by name, sire, or biography..." />
        </div>
    </div>

    <!-- Enhanced Relationship Table -->
    <div class="relationship-table-wrapper">
        <table class="relationship-table" id="relationshipTable">
            <thead>
                <tr>
                    <th data-sort="character_name">Vampire <span class="sort-icon">⇅</span></th>
                    <th data-sort="clan">Clan <span class="sort-icon">⇅</span></th>
                    <th data-sort="generation">Gen <span class="sort-icon">⇅</span></th>
                    <th data-sort="sire">Sire Field <span class="sort-icon">⇅</span></th>
                    <th>Biography Analysis</th>
                    <th>Suggested Sire</th>
                    <th>Confidence</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $enhanced_query = "SELECT c.*, 
                    (SELECT COUNT(*) FROM characters c2 WHERE c2.sire = c.character_name) as childe_count,
                    (SELECT GROUP_CONCAT(c2.character_name SEPARATOR ', ') FROM characters c2 WHERE c2.sire = c.character_name) as childe_names
                    FROM characters c 
                    ORDER BY c.character_name";
                $enhanced_result = mysqli_query($conn, $enhanced_query);
                
                if (!$enhanced_result) {
                    echo "<tr><td colspan='9'>Query Error: " . mysqli_error($conn) . "</td></tr>";
                } elseif (mysqli_num_rows($enhanced_result) > 0) {
                    while ($char = mysqli_fetch_assoc($enhanced_result)) {
                        $is_npc = ($char['player_name'] === 'NPC');
                        $has_sire_field = !empty($char['sire']);
                        $has_biography = !empty($char['biography']);
                        $has_childer = $char['childe_count'] > 0;
                        
                        // Analyze biography for sire mentions
                        $suggested_sire = '';
                        $confidence = 'none';
                        $analysis_status = 'needs-analysis';
                        
                        if ($has_biography) {
                            $suggested_sire = analyzeBiographyForSire($char['biography']);
                            if ($suggested_sire) {
                                $confidence = calculateConfidence($char['biography'], $suggested_sire);
                                $analysis_status = 'suggested';
                                
                                // Check for conflicts
                                if ($has_sire_field && strtolower($char['sire']) !== strtolower($suggested_sire)) {
                                    $analysis_status = 'conflict';
                                } elseif ($has_sire_field && strtolower($char['sire']) === strtolower($suggested_sire)) {
                                    $analysis_status = 'verified';
                                }
                            } else {
                                $analysis_status = 'no-mention';
                            }
                        }
                ?>
                    <tr class="relationship-row" 
                        data-type="<?php echo $is_npc ? 'npc' : 'pc'; ?>" 
                        data-name="<?php echo htmlspecialchars($char['character_name']); ?>"
                        data-sire="<?php echo htmlspecialchars($char['sire'] ?? ''); ?>"
                        data-suggested="<?php echo htmlspecialchars($suggested_sire); ?>"
                        data-status="<?php echo $analysis_status; ?>"
                        data-has-sire="<?php echo $has_sire_field ? 'true' : 'false'; ?>"
                        data-has-childer="<?php echo $has_childer ? 'true' : 'false'; ?>">
                        <td>
                            <strong><?php echo htmlspecialchars($char['character_name']); ?></strong>
                            <?php if ($has_sire_field): ?>
                                <span class="childe-badge">Childe</span>
                            <?php endif; ?>
                            <?php if ($has_childer): ?>
                                <span class="sire-badge">Sire</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($char['clan'] ?? 'Unknown'); ?></td>
                        <td><?php echo $char['generation']; ?>th</td>
                        <td>
                            <?php if ($has_sire_field && strtolower($char['sire']) !== 'unknown'): ?>
                                <span class="sire-name"><?php echo htmlspecialchars($char['sire']); ?></span>
                            <?php else: ?>
                                <select class="sire-dropdown" data-character-id="<?php echo $char['id']; ?>" onchange="updateSireField(this)">
                                    <option value="">Select Sire...</option>
                                    <option value="Unknown" <?php echo (strtolower($char['sire'] ?? '') === 'unknown') ? 'selected' : ''; ?>>Unknown</option>
                                    <?php
                                    // Get all character names for dropdown
                                    $sire_query = "SELECT character_name FROM characters WHERE character_name != '" . mysqli_real_escape_string($conn, $char['character_name']) . "' ORDER BY character_name";
                                    $sire_result = mysqli_query($conn, $sire_query);
                                    if ($sire_result) {
                                        while ($sire = mysqli_fetch_assoc($sire_result)) {
                                            $selected = (strtolower($char['sire'] ?? '') === strtolower($sire['character_name'])) ? 'selected' : '';
                                            echo "<option value='{$sire['character_name']}' {$selected}>{$sire['character_name']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($has_biography): ?>
                                <div class="biography-preview" title="<?php echo htmlspecialchars($char['biography']); ?>">
                                    <?php echo htmlspecialchars(substr($char['biography'], 0, 80)) . (strlen($char['biography']) > 80 ? '...' : ''); ?>
                                </div>
                            <?php else: ?>
                                <span class="no-biography">No Biography</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($suggested_sire): ?>
                                <span class="suggested-sire"><?php echo htmlspecialchars($suggested_sire); ?></span>
                            <?php else: ?>
                                <span class="no-suggestion">None</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="confidence-badge confidence-<?php echo $confidence; ?>">
                                <?php echo ucfirst($confidence); ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo $analysis_status; ?>">
                                <?php echo ucfirst(str_replace('-', ' ', $analysis_status)); ?>
                            </span>
                        </td>
                        <td class="actions">
                            <button class="action-btn edit-btn" 
                                    data-id="<?php echo $char['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($char['character_name']); ?>"
                                    data-sire="<?php echo htmlspecialchars($char['sire'] ?? ''); ?>"
                                    data-suggested="<?php echo htmlspecialchars($suggested_sire); ?>"
                                    data-biography="<?php echo htmlspecialchars($char['biography'] ?? ''); ?>"
                                    title="Edit Relationship">✏️</button>
                            <button class="action-btn verify-btn" 
                                    data-id="<?php echo $char['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($char['character_name']); ?>"
                                    data-sire="<?php echo htmlspecialchars($char['sire'] ?? ''); ?>"
                                    data-suggested="<?php echo htmlspecialchars($suggested_sire); ?>"
                                    title="Verify Relationship">✅</button>
                            <button class="action-btn view-btn" 
                                    data-id="<?php echo $char['id']; ?>"
                                    title="View Details">👁️</button>
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='9' class='empty-state'>No characters found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Enhanced Relationship Modal -->
<div id="relationshipModal" class="modal">
    <div class="modal-content large-modal">
        <h2 class="modal-title">🔍 <span id="modalTitle">Analyze Relationship</span></h2>
        <button class="modal-close" onclick="closeRelationshipModal()">×</button>
        
        <div class="analysis-panel">
            <div class="analysis-section">
                <h3>Current Sire Field</h3>
                <div id="currentSire" class="field-display"></div>
            </div>
            
            <div class="analysis-section">
                <h3>Biography Analysis</h3>
                <div id="biographyText" class="biography-display"></div>
                <div id="analysisResults" class="analysis-results"></div>
            </div>
            
            <div class="analysis-section">
                <h3>Suggested Actions</h3>
                <div id="suggestedActions" class="suggested-actions"></div>
            </div>
        </div>
        
        <form id="relationshipForm">
            <input type="hidden" id="characterId" name="character_id">
            
            <div class="form-group">
                <label for="sireSelect">Set Sire:</label>
                <select id="sireSelect" name="sire">
                    <option value="">No sire (Sireless)</option>
                    <?php
                    $sire_query = "SELECT id, character_name FROM characters ORDER BY character_name";
                    $sire_result = mysqli_query($conn, $sire_query);
                    if ($sire_result) {
                        while ($sire = mysqli_fetch_assoc($sire_result)) {
                            echo "<option value='{$sire['character_name']}'>{$sire['character_name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="relationshipNotes">Analysis Notes:</label>
                <textarea id="relationshipNotes" name="notes" placeholder="Notes about this relationship analysis..."></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="modal-btn cancel-btn" onclick="closeRelationshipModal()">Cancel</button>
                <button type="button" class="modal-btn analyze-btn" onclick="reanalyzeCharacter()">Re-analyze</button>
                <button type="submit" class="modal-btn confirm-btn">Save Relationship</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Enhanced Analysis Styles */
.analysis-controls {
    background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%);
    border: 2px solid #8B0000;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 25px;
}

.control-group {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.control-group .action-btn {
    white-space: nowrap;
    min-width: 180px;
    flex-shrink: 0;
    padding: 12px 24px;
    font-size: 0.9em;
    text-align: center;
}

.analyze-btn { background: rgba(0, 139, 0, 0.2); border-color: rgba(0, 139, 0, 0.4); }
.analyze-btn:hover { background: rgba(0, 139, 0, 0.4); }
.verify-btn { background: rgba(139, 100, 0, 0.2); border-color: rgba(139, 100, 0, 0.4); }
.verify-btn:hover { background: rgba(139, 100, 0, 0.4); }
.export-btn { background: rgba(0, 100, 200, 0.2); border-color: rgba(0, 100, 200, 0.4); }
.export-btn:hover { background: rgba(0, 100, 200, 0.4); }
.refresh-btn { background: rgba(26, 107, 58, 0.2); border-color: rgba(26, 107, 58, 0.4); }
.refresh-btn:hover { background: rgba(26, 107, 58, 0.4); }

.analysis-status {
    color: #b8a090;
    font-style: italic;
    padding: 10px;
    background: rgba(139, 0, 0, 0.1);
    border-radius: 5px;
}

.biography-preview {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    cursor: help;
}

.suggested-sire { color: #4a1a6b; font-weight: bold; }
.no-suggestion { color: #666; font-style: italic; }
.no-biography { color: #666; font-style: italic; }

.sire-name { color: #1a6b3a; font-weight: bold; }
.unknown-sire { color: #8B6508; font-style: italic; }
.no-sire { color: #8B0000; font-style: italic; }

.sire-dropdown {
    background: rgba(26, 15, 15, 0.8);
    border: 1px solid rgba(139, 0, 0, 0.4);
    border-radius: 3px;
    color: #f5e6d3;
    padding: 4px 8px;
    font-family: var(--font-body), 'Source Serif Pro', serif;
    font-size: 0.9em;
    min-width: 120px;
    cursor: pointer;
}

.sire-dropdown:focus {
    outline: none;
    border-color: #8B0000;
    box-shadow: 0 0 5px rgba(139, 0, 0, 0.3);
}

.sire-dropdown option {
    background: #1a0f0f;
    color: #f5e6d3;
    padding: 5px;
}

.future-note {
    background: rgba(26, 15, 15, 0.4);
    border: 1px solid rgba(139, 100, 0, 0.3);
    border-radius: 5px;
    padding: 15px;
    margin-top: 15px;
    color: #d4c4b0;
}

.future-note h4 {
    color: #f5e6d3;
    margin-bottom: 10px;
    font-family: var(--font-heading), 'Cinzel', serif;
}

.future-note ul {
    margin: 0;
    padding-left: 20px;
}

.future-note li {
    margin-bottom: 8px;
    line-height: 1.4;
}

.future-note strong {
    color: #8B6508;
}

.confidence-badge {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 0.8em;
    font-weight: bold;
}
.confidence-high { background: #1a6b3a; color: #f5e6d3; }
.confidence-medium { background: #8B6508; color: #f5e6d3; }
.confidence-low { background: #8B0000; color: #f5e6d3; }
.confidence-none { background: #666; color: #999; }

.status-badge {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 0.8em;
    font-weight: bold;
}
.status-verified { background: #1a6b3a; color: #f5e6d3; }
.status-suggested { background: #4a1a6b; color: #f5e6d3; }
.status-conflict { background: #8B0000; color: #f5e6d3; }
.status-needs-analysis { background: #8B6508; color: #f5e6d3; }
.status-no-mention { background: #666; color: #999; }

.analysis-panel {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.analysis-section {
    background: rgba(26, 15, 15, 0.3);
    padding: 15px;
    border-radius: 5px;
    border: 1px solid rgba(139, 0, 0, 0.3);
}

.analysis-section h3 {
    color: #f5e6d3;
    margin-bottom: 10px;
    border-bottom: 1px solid rgba(139, 0, 0, 0.3);
    padding-bottom: 5px;
}

.field-display, .biography-display {
    color: #d4c4b0;
    font-family: var(--font-body), 'Source Serif Pro', serif;
    line-height: 1.4;
}

.analysis-results {
    margin-top: 10px;
    padding: 10px;
    background: rgba(139, 0, 0, 0.1);
    border-radius: 3px;
}

.suggested-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.action-suggestion {
    padding: 8px 12px;
    background: rgba(139, 0, 0, 0.2);
    border-radius: 3px;
    cursor: pointer;
    transition: all 0.2s;
}

.action-suggestion:hover {
    background: rgba(139, 0, 0, 0.3);
}

.analysis-results-list {
    max-height: 400px;
    overflow-y: auto;
    margin-bottom: 20px;
}

.result-item {
    background: rgba(26, 15, 15, 0.3);
    border: 1px solid rgba(139, 0, 0, 0.3);
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 10px;
    transition: all 0.2s;
}

.result-item:hover {
    background: rgba(139, 0, 0, 0.1);
    border-color: #8B0000;
}

.result-item.conflict {
    border-color: #8B0000;
    background: rgba(139, 0, 0, 0.1);
}

.result-item.updated {
    border-color: #1a6b3a;
    background: rgba(26, 107, 58, 0.1);
}

.result-item h4 {
    color: #f5e6d3;
    margin-bottom: 8px;
    font-family: var(--font-title), 'Libre Baskerville', serif;
}

.result-item p {
    margin: 5px 0;
    color: #d4c4b0;
    font-family: var(--font-body), 'Source Serif Pro', serif;
}

.analysis-result {
    background: rgba(139, 0, 0, 0.1);
    border: 1px solid rgba(139, 0, 0, 0.3);
    border-radius: 3px;
    padding: 10px;
    margin-top: 10px;
    color: #d4c4b0;
    font-family: var(--font-body), 'Source Serif Pro', serif;
}

@media (max-width: 768px) {
    .analysis-panel {
        grid-template-columns: 1fr;
    }
    
    .control-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .control-group .action-btn {
        width: 100%;
        text-align: center;
    }
}
</style>

<script>
let currentFilter = 'all';
let analysisResults = {};

document.addEventListener('DOMContentLoaded', function() {
    initializeEnhanced();
});

function initializeEnhanced() {
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
    document.getElementById('relationshipSearch').addEventListener('input', applyFilters);
    
    // Action buttons
    document.querySelectorAll('.edit-btn, .verify-btn, .view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.classList.contains('edit-btn') ? 'edit' : 
                          this.classList.contains('verify-btn') ? 'verify' : 'view';
            handleAction(action, this.dataset);
        });
    });
    
    // Form submission
    document.getElementById('relationshipForm').addEventListener('submit', handleFormSubmit);
    
    // Initial filter
    applyFilters();
}

function applyFilters() {
    const searchTerm = document.getElementById('relationshipSearch').value.toLowerCase();
    const rows = document.querySelectorAll('.relationship-row');
    
    rows.forEach(row => {
        const name = row.dataset.name.toLowerCase();
        const sire = row.dataset.sire.toLowerCase();
        const suggested = row.dataset.suggested.toLowerCase();
        const status = row.dataset.status;
        const biography = row.querySelector('.biography-preview')?.textContent.toLowerCase() || '';
        
        let show = true;
        
        // Apply filter
        if (currentFilter === 'needs-analysis' && status !== 'needs-analysis') show = false;
        if (currentFilter === 'suggestions' && status !== 'suggested') show = false;
        if (currentFilter === 'verified' && status !== 'verified') show = false;
        if (currentFilter === 'conflicts' && status !== 'conflict') show = false;
        
        // Apply search
        if (searchTerm && !name.includes(searchTerm) && !sire.includes(searchTerm) && 
            !suggested.includes(searchTerm) && !biography.includes(searchTerm)) show = false;
        
        if (show) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
}

function analyzeBiographies() {
    document.getElementById('analysisStatus').textContent = 'Analyzing character biographies and auto-populating sire fields...';
    
    fetch('api_analyze_sire_relationships.php?action=analyze_all', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const stats = data.stats;
            document.getElementById('analysisStatus').textContent = 
                `Analysis complete! Analyzed ${stats.total_analyzed} characters, found ${stats.suggestions_found} potential relationships, auto-updated ${stats.updates_made} sire fields, found ${stats.conflicts_found} conflicts.`;
            
            // Show detailed results
            showAnalysisResults(data.results);
            
            // Update status to show completion
            document.getElementById('analysisStatus').textContent = `Analysis complete! Found ${data.results.length} results. Click "Refresh Page" to see updated data.`;
        } else {
            document.getElementById('analysisStatus').textContent = 'Error: ' + data.message;
        }
    })
    .catch(error => {
        document.getElementById('analysisStatus').textContent = 'Error analyzing biographies: ' + error.message;
        console.error(error);
    });
}

function showVerificationPanel() {
    // Show characters that need verification
    currentFilter = 'suggestions';
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    document.querySelector('[data-filter="suggestions"]').classList.add('active');
    applyFilters();
}

function exportRelationships() {
    // Export relationship data
    alert('Export functionality would be implemented here');
}

function handleAction(action, data) {
    if (action === 'edit' || action === 'verify') {
        openAnalysisModal(data);
    } else if (action === 'view') {
        viewCharacter(data.id);
    }
}

function openAnalysisModal(data) {
    document.getElementById('modalTitle').textContent = 'Analyze Relationship: ' + data.name;
    document.getElementById('characterId').value = data.id;
    document.getElementById('currentSire').textContent = data.sire || 'Not set';
    document.getElementById('biographyText').textContent = data.biography || 'No biography available';
    document.getElementById('sireSelect').value = data.sire || '';
    
    // Show analysis results
    const analysisResults = document.getElementById('analysisResults');
    if (data.suggested) {
        analysisResults.innerHTML = `
            <div class="analysis-result">
                <strong>Suggested Sire:</strong> ${data.suggested}<br>
                <strong>Confidence:</strong> High (based on biography analysis)
            </div>
        `;
    } else {
        analysisResults.innerHTML = '<div class="analysis-result">No sire mentions found in biography</div>';
    }
    
    // Show suggested actions
    const suggestedActions = document.getElementById('suggestedActions');
    suggestedActions.innerHTML = `
        <div class="action-suggestion" onclick="setSire('${data.suggested || ''}')">
            ${data.suggested ? 'Accept suggested sire: ' + data.suggested : 'Set as sireless'}
        </div>
        <div class="action-suggestion" onclick="markAsVerified()">
            Mark relationship as verified
        </div>
    `;
    
    document.getElementById('relationshipModal').classList.add('active');
}

function setSire(sireName) {
    document.getElementById('sireSelect').value = sireName;
}

function markAsVerified() {
    // Mark the relationship as verified
    alert('Relationship marked as verified');
}

function reanalyzeCharacter() {
    const characterId = document.getElementById('characterId').value;
    
    const formData = new FormData();
    formData.append('action', 'analyze_single');
    formData.append('character_id', characterId);
    
    fetch('api_analyze_sire_relationships.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const analysis = data.analysis;
            
            // Update the analysis results display
            const analysisResults = document.getElementById('analysisResults');
            if (analysis.suggested_sire) {
                analysisResults.innerHTML = `
                    <div class="analysis-result">
                        <strong>Suggested Sire:</strong> ${analysis.suggested_sire}<br>
                        <strong>Confidence:</strong> ${analysis.confidence}<br>
                        ${analysis.has_conflict ? '<strong style="color: #8B0000;">⚠️ Conflict with existing sire field!</strong>' : ''}
                        ${analysis.updated ? '<strong style="color: #1a6b3a;">✅ Sire field auto-updated!</strong>' : ''}
                    </div>
                `;
                
                // Update the sire select if we found a suggestion
                if (analysis.confidence === 'high' && !analysis.has_conflict) {
                    document.getElementById('sireSelect').value = analysis.suggested_sire;
                }
            } else {
                analysisResults.innerHTML = '<div class="analysis-result">No sire mentions found in biography</div>';
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error re-analyzing character: ' + error.message);
        console.error(error);
    });
}

function showAnalysisResults(results) {
    // Create a modal to show detailed analysis results
    const modal = document.createElement('div');
    modal.className = 'modal active';
    modal.id = 'analysisResultsModal';
    modal.innerHTML = `
        <div class="modal-content large-modal">
            <h2 class="modal-title">📊 Analysis Results</h2>
            <button class="modal-close" onclick="closeAnalysisModal()">×</button>
            <div class="analysis-results-list">
                ${results.map(result => `
                    <div class="result-item ${result.has_conflict ? 'conflict' : ''} ${result.updated ? 'updated' : ''}">
                        <h4>${result.character_name}</h4>
                        ${result.relationship_type === 'sire_of' ? 
                            `<p><strong>Relationship:</strong> <span style="color: #8B0000;">This character is the SIRE of:</span> ${result.suggested_sire}</p>` :
                            `<p><strong>Current Sire:</strong> ${result.current_sire || 'Not set'}</p>
                             <p><strong>Suggested Sire:</strong> ${result.suggested_sire}</p>`
                        }
                        <p><strong>Confidence:</strong> <span class="confidence-badge confidence-${result.confidence}">${result.confidence}</span></p>
                        <p><strong>Found in:</strong> ${result.text_sources ? result.text_sources.join(', ') : 'Unknown'}</p>
                        ${result.has_conflict ? '<p style="color: #8B0000;"><strong>⚠️ Conflict detected!</strong></p>' : ''}
                        ${result.updated ? '<p style="color: #1a6b3a;"><strong>✅ Auto-updated!</strong></p>' : ''}
                    </div>
                `).join('')}
            </div>
            <div class="modal-actions">
                <button class="modal-btn confirm-btn" onclick="closeAnalysisModal()">Close</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function closeAnalysisModal() {
    const modal = document.getElementById('analysisResultsModal');
    if (modal) {
        modal.remove();
    }
}

function updateSireField(selectElement) {
    const characterId = selectElement.dataset.characterId;
    const selectedSire = selectElement.value;
    
    if (!selectedSire) {
        return; // No sire selected
    }
    
    // Confirm the update
    if (!confirm(`Set sire to "${selectedSire}"?`)) {
        selectElement.value = ''; // Reset selection
        return;
    }
    
    // Send update to server
    fetch('api_sire_childe.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'update_sire',
            character_id: characterId,
            sire: selectedSire
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Replace dropdown with the selected sire name
            const cell = selectElement.parentNode;
            cell.innerHTML = `<span class="sire-name">${selectedSire}</span>`;
            
            // Update the row's data attributes
            const row = selectElement.closest('tr');
            row.dataset.sire = selectedSire;
            row.dataset.hasSire = 'true';
            
            // Update status to verified if it matches suggested sire
            const suggestedSire = row.dataset.suggested;
            if (suggestedSire && selectedSire.toLowerCase() === suggestedSire.toLowerCase()) {
                const statusCell = row.querySelector('.status-badge');
                statusCell.textContent = 'Verified';
                statusCell.className = 'status-badge status-verified';
                row.dataset.status = 'verified';
            }
            
            // Show success message
            showNotification('Sire field updated successfully!', 'success');
        } else {
            alert('Error updating sire field: ' + data.message);
            selectElement.value = ''; // Reset on error
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating sire field. Please try again.');
        selectElement.value = ''; // Reset on error
    });
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // Style the notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#1a6b3a' : '#8B0000'};
        color: #f5e6d3;
        padding: 12px 20px;
        border-radius: 5px;
        font-family: var(--font-body), 'Source Serif Pro', serif;
        font-weight: bold;
        z-index: 10000;
        box-shadow: 0 4px 8px rgba(0,0,0,0.3);
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

function closeRelationshipModal() {
    document.getElementById('relationshipModal').classList.remove('active');
}

function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData);
    
    fetch('api_sire_childe.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            closeRelationshipModal();
            window.location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        alert('Error saving relationship');
        console.error(error);
    });
}

function viewCharacter(characterId) {
    window.open(`view_character_api.php?id=${characterId}`, '_blank');
}
</script>

<?php
// Helper functions for biography analysis
function analyzeBiographyForSire($biography) {
    $biography = strtolower($biography);
    
    // Common patterns for sire mentions
    $patterns = [
        '/sired by ([a-zA-Z\s]+)/',
        '/embraced by ([a-zA-Z\s]+)/',
        '/created by ([a-zA-Z\s]+)/',
        '/turned by ([a-zA-Z\s]+)/',
        '/my sire ([a-zA-Z\s]+)/',
        '/sire ([a-zA-Z\s]+)/',
        '/mentor ([a-zA-Z\s]+)/',
        '/teacher ([a-zA-Z\s]+)/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $biography, $matches)) {
            $sire = trim($matches[1]);
            // Clean up common words
            $sire = preg_replace('/\b(was|is|the|a|an|my|their|his|her)\b/', '', $sire);
            $sire = trim($sire);
            if (strlen($sire) > 2) {
                return ucwords($sire);
            }
        }
    }
    
    return '';
}

function calculateConfidence($biography, $suggestedSire) {
    $biography = strtolower($biography);
    $sire = strtolower($suggestedSire);
    
    $confidence = 'low';
    
    // High confidence indicators
    if (strpos($biography, 'sired by') !== false || 
        strpos($biography, 'embraced by') !== false ||
        strpos($biography, 'my sire') !== false) {
        $confidence = 'high';
    }
    // Medium confidence indicators
    elseif (strpos($biography, 'mentor') !== false || 
             strpos($biography, 'teacher') !== false ||
             strpos($biography, 'created by') !== false) {
        $confidence = 'medium';
    }
    
    return $confidence;
}
?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
