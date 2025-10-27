<?php
/**
 * Admin Panel - Sire/Childe Relationship Tracker
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
    <h1 class="panel-title">🧛 Sire/Childe Relationships</h1>
    <p class="panel-subtitle">Track vampire lineage and blood bonds in the city</p>
    
    <!-- Admin Navigation -->
    <div class="admin-nav">
        <a href="admin_panel.php" class="nav-btn">👥 Characters</a>
        <a href="admin_sire_childe.php" class="nav-btn active">🧛 Sire/Childe</a>
        <a href="admin_sire_childe_enhanced.php" class="nav-btn">🔍 Enhanced Analysis</a>
        <a href="admin_equipment.php" class="nav-btn">⚔️ Equipment</a>
        <a href="admin_locations.php" class="nav-btn">📍 Locations</a>
        <a href="questionnaire_admin.php" class="nav-btn">📝 Questionnaire</a>
        <a href="admin_npc_briefing.php" class="nav-btn">📋 NPC Briefing</a>
    </div>
    
    <!-- Relationship Statistics -->
    <div class="relationship-stats">
        <?php
        // Get relationship statistics
        $stats_query = "SELECT 
            COUNT(*) as total_vampires,
            COUNT(CASE WHEN sire IS NOT NULL AND sire != '' THEN 1 END) as with_sire,
            COUNT(CASE WHEN sire IS NULL OR sire = '' THEN 1 END) as without_sire,
            (SELECT COUNT(*) FROM characters c1 WHERE c1.sire IN (SELECT character_name FROM characters c2 WHERE c1.sire = c2.character_name)) as childer_count
            FROM characters";
        $stats_result = mysqli_query($conn, $stats_query);
        
        if ($stats_result) {
            $stats = mysqli_fetch_assoc($stats_result);
        } else {
            $stats = ['total_vampires' => 0, 'with_sire' => 0, 'without_sire' => 0, 'childer_count' => 0];
        }
        ?>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['total_vampires'] ?? 0; ?></span>
            <span class="stat-label">Total Vampires</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['with_sire'] ?? 0; ?></span>
            <span class="stat-label">With Sire</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['without_sire'] ?? 0; ?></span>
            <span class="stat-label">Sireless</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['childer_count'] ?? 0; ?></span>
            <span class="stat-label">Childer</span>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="filter-controls">
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All Relationships</button>
            <button class="filter-btn" data-filter="sires">Sires Only</button>
            <button class="filter-btn" data-filter="childer">Childer Only</button>
            <button class="filter-btn" data-filter="sireless">Sireless</button>
        </div>
        <div class="search-box">
            <input type="text" id="relationshipSearch" placeholder="🔍 Search by name or sire..." />
        </div>
        <div class="action-buttons">
            <button class="action-btn add-btn" onclick="openAddRelationshipModal()">+ Add Relationship</button>
            <button class="action-btn tree-btn" onclick="showFamilyTree()">🌳 Family Tree</button>
        </div>
    </div>

    <!-- Relationship Table -->
    <div class="relationship-table-wrapper">
        <table class="relationship-table" id="relationshipTable">
            <thead>
                <tr>
                    <th data-sort="character_name">Vampire <span class="sort-icon">⇅</span></th>
                    <th data-sort="clan">Clan <span class="sort-icon">⇅</span></th>
                    <th data-sort="generation">Gen <span class="sort-icon">⇅</span></th>
                    <th data-sort="sire">Sire <span class="sort-icon">⇅</span></th>
                    <th>Childer</th>
                    <th data-sort="player_name">Player <span class="sort-icon">⇅</span></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $relationship_query = "SELECT c.*, 
                    (SELECT COUNT(*) FROM characters c2 WHERE c2.sire = c.character_name) as childe_count,
                    (SELECT GROUP_CONCAT(c2.character_name SEPARATOR ', ') FROM characters c2 WHERE c2.sire = c.character_name) as childe_names
                    FROM characters c 
                    ORDER BY c.character_name";
                $relationship_result = mysqli_query($conn, $relationship_query);
                
                if (!$relationship_result) {
                    echo "<tr><td colspan='7'>Query Error: " . mysqli_error($conn) . "</td></tr>";
                } elseif (mysqli_num_rows($relationship_result) > 0) {
                    while ($char = mysqli_fetch_assoc($relationship_result)) {
                        $is_npc = ($char['player_name'] === 'NPC');
                        $has_sire = !empty($char['sire']);
                        $has_childer = $char['childe_count'] > 0;
                ?>
                    <tr class="relationship-row" 
                        data-type="<?php echo $is_npc ? 'npc' : 'pc'; ?>" 
                        data-name="<?php echo htmlspecialchars($char['character_name']); ?>"
                        data-sire="<?php echo htmlspecialchars($char['sire'] ?? ''); ?>"
                        data-has-sire="<?php echo $has_sire ? 'true' : 'false'; ?>"
                        data-has-childer="<?php echo $has_childer ? 'true' : 'false'; ?>">
                        <td>
                            <strong><?php echo htmlspecialchars($char['character_name']); ?></strong>
                            <?php if ($has_sire): ?>
                                <span class="childe-badge">Childe</span>
                            <?php endif; ?>
                            <?php if ($has_childer): ?>
                                <span class="sire-badge">Sire</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($char['clan'] ?? 'Unknown'); ?></td>
                        <td><?php echo $char['generation']; ?>th</td>
                        <td>
                            <?php if ($has_sire): ?>
                                <span class="sire-name"><?php echo htmlspecialchars($char['sire']); ?></span>
                            <?php else: ?>
                                <span class="no-sire">Sireless</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($has_childer): ?>
                                <div class="childe-list">
                                    <span class="childe-count"><?php echo $char['childe_count']; ?> childe<?php echo $char['childe_count'] > 1 ? 's' : ''; ?></span>
                                    <div class="childe-names"><?php echo htmlspecialchars($char['childe_names']); ?></div>
                                </div>
                            <?php else: ?>
                                <span class="no-childer">None</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($is_npc): ?>
                                <span class="badge-npc">NPC</span>
                            <?php else: ?>
                                <?php echo htmlspecialchars($char['player_name']); ?>
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <button class="action-btn edit-btn" 
                                    data-id="<?php echo $char['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($char['character_name']); ?>"
                                    data-sire="<?php echo htmlspecialchars($char['sire'] ?? ''); ?>"
                                    title="Edit Relationship">✏️</button>
                            <button class="action-btn view-btn" 
                                    data-id="<?php echo $char['id']; ?>"
                                    title="View Details">👁️</button>
                        </td>
                    </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='7' class='empty-state'>No characters found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Relationship Modal -->
<div id="relationshipModal" class="modal">
    <div class="modal-content">
        <h2 class="modal-title">🧛 <span id="modalTitle">Add Relationship</span></h2>
        <button class="modal-close" onclick="closeRelationshipModal()">×</button>
        
        <form id="relationshipForm">
            <input type="hidden" id="characterId" name="character_id">
            
            <div class="form-group">
                <label for="characterSelect">Vampire:</label>
                <select id="characterSelect" name="character_name" required>
                    <option value="">Select a vampire...</option>
                    <?php
                    $char_query = "SELECT id, character_name FROM characters ORDER BY character_name";
                    $char_result = mysqli_query($conn, $char_query);
                    if ($char_result) {
                        while ($char = mysqli_fetch_assoc($char_result)) {
                            echo "<option value='{$char['id']}'>{$char['character_name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="sireSelect">Sire:</label>
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
                <label for="relationshipNotes">Notes:</label>
                <textarea id="relationshipNotes" name="notes" placeholder="Additional notes about this relationship..."></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="modal-btn cancel-btn" onclick="closeRelationshipModal()">Cancel</button>
                <button type="submit" class="modal-btn confirm-btn">Save Relationship</button>
            </div>
        </form>
    </div>
</div>

<!-- Family Tree Modal -->
<div id="treeModal" class="modal">
    <div class="modal-content large-modal">
        <h2 class="modal-title">🌳 Vampire Family Tree</h2>
        <button class="modal-close" onclick="closeTreeModal()">×</button>
        
        <div id="familyTreeContent">
            <div class="tree-loading">Loading family tree...</div>
        </div>
    </div>
</div>


<script>
let currentFilter = 'all';
let currentRelationshipId = null;

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
    document.getElementById('relationshipSearch').addEventListener('input', applyFilters);
    
    // Edit buttons
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            editRelationship(this.dataset.id, this.dataset.name, this.dataset.sire);
        });
    });
    
    // View buttons
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            viewCharacter(this.dataset.id);
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
        const hasSire = row.dataset.hasSire === 'true';
        const hasChilder = row.dataset.hasChilder === 'true';
        
        let show = true;
        
        // Apply filter
        if (currentFilter === 'sires' && !hasChilder) show = false;
        if (currentFilter === 'childer' && !hasSire) show = false;
        if (currentFilter === 'sireless' && hasSire) show = false;
        
        // Apply search
        if (searchTerm && !name.includes(searchTerm) && !sire.includes(searchTerm)) show = false;
        
        if (show) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
}

function openAddRelationshipModal() {
    currentRelationshipId = null;
    document.getElementById('modalTitle').textContent = 'Add Relationship';
    document.getElementById('relationshipForm').reset();
    document.getElementById('characterId').value = '';
    document.getElementById('relationshipModal').classList.add('active');
}

function editRelationship(id, name, sire) {
    currentRelationshipId = id;
    document.getElementById('modalTitle').textContent = 'Edit Relationship';
    document.getElementById('characterId').value = id;
    document.getElementById('characterSelect').value = id;
    document.getElementById('sireSelect').value = sire;
    document.getElementById('relationshipModal').classList.add('active');
}

function closeRelationshipModal() {
    document.getElementById('relationshipModal').classList.remove('active');
    currentRelationshipId = null;
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

function showFamilyTree() {
    document.getElementById('treeModal').classList.add('active');
    document.getElementById('familyTreeContent').innerHTML = '<div class="tree-loading">Loading family tree...</div>';
    
    // Simple family tree display
    fetch('api_sire_childe.php?action=tree')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderFamilyTree(data.tree);
            } else {
                document.getElementById('familyTreeContent').innerHTML = '<p style="color: red;">Error: ' + data.message + '</p>';
            }
        })
        .catch(error => {
            document.getElementById('familyTreeContent').innerHTML = '<p style="color: red;">Error loading family tree</p>';
            console.error(error);
        });
}

function renderFamilyTree(tree) {
    let html = '<div class="family-tree">';
    
    // Group by generation
    const generations = {};
    tree.forEach(char => {
        if (!generations[char.generation]) {
            generations[char.generation] = [];
        }
        generations[char.generation].push(char);
    });
    
    // Sort generations (highest first)
    const sortedGens = Object.keys(generations).sort((a, b) => parseInt(b) - parseInt(a));
    
    sortedGens.forEach(gen => {
        html += `<div class="generation">`;
        html += `<h3>Generation ${gen}</h3>`;
        html += `<div class="generation-vampires">`;
        
        generations[gen].forEach(char => {
            html += `<div class="vampire-node">`;
            html += `<div class="vampire-name">${char.character_name}</div>`;
            html += `<div class="vampire-clan">${char.clan}</div>`;
            if (char.sire) {
                html += `<div class="sire-info">Sired by: ${char.sire}</div>`;
            }
            if (char.childer && char.childer.length > 0) {
                html += `<div class="childe-info">Childer: ${char.childer.join(', ')}</div>`;
            }
            html += `</div>`;
        });
        
        html += `</div></div>`;
    });
    
    html += '</div>';
    document.getElementById('familyTreeContent').innerHTML = html;
}

function closeTreeModal() {
    document.getElementById('treeModal').classList.remove('active');
}

function viewCharacter(characterId) {
    // Redirect to character view or open modal
    window.open(`view_character_api.php?id=${characterId}`, '_blank');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
