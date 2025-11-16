<?php
/**
 * Boon Ledger - Admin Page
 * Manage boons (favors) between characters
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('LOTN_VERSION', '0.9.22');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../includes/connect.php';
include __DIR__ . '/../includes/header.php';

// Get all characters for dropdown
$characters_query = "SELECT id, character_name FROM characters ORDER BY character_name ASC";
$characters_result = mysqli_query($conn, $characters_query);
$characters = [];
if ($characters_result) {
    while ($row = mysqli_fetch_assoc($characters_result)) {
        $characters[] = $row;
    }
}

function render_boon_type_badge($type) {
    $colors = [
        'Trivial' => '#666',
        'Minor' => '#8B6508',
        'Major' => '#8B0000',
        'Life' => '#1a0f0f'
    ];
    $color = $colors[$type] ?? '#666';
    return sprintf('<span class="boon-type-badge" style="background-color:%s;">%s</span>', 
                   $color, htmlspecialchars($type));
}

function render_status_badge($status) {
    $colors = [
        'Owed' => '#8B6508',
        'Called' => '#B22222',
        'Paid' => '#1a6b3a',
        'Broken' => '#3a3a3a'
    ];
    $color = $colors[$status] ?? '#666';
    return sprintf('<span class="boon-status-badge" style="background-color:%s;">%s</span>', 
                   $color, htmlspecialchars($status));
}
?>

<div class="admin-panel-container container-fluid py-4 px-3 px-md-4">
    <h1 class="panel-title display-5 text-light fw-bold mb-1">💎 Boon Ledger</h1>
    <p class="panel-subtitle lead text-muted fst-italic mb-2">Track favors and debts between characters</p>
    <div class="mb-3">
        <a href="admin_panel.php" class="btn btn-outline-secondary btn-sm">
            ← Back to Admin Panel
        </a>
    </div>
    
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
            <a href="boon_ledger.php" class="nav-btn btn btn-outline-danger btn-sm w-100 text-center active">💎 Boons</a>
        </div>
    </nav>
    
    <!-- Action Bar -->
    <div class="action-bar d-flex justify-content-between align-items-center mb-4">
        <div>
            <button class="btn btn-primary" onclick="openBoonModal()">
                ➕ New Boon
            </button>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <label for="statusFilter" class="text-light text-uppercase small mb-0">Filter:</label>
            <select id="statusFilter" class="form-select form-select-sm bg-dark text-light border-danger">
                <option value="all">All Status</option>
                <option value="Owed">Owed</option>
                <option value="Called">Called</option>
                <option value="Paid">Paid</option>
                <option value="Broken">Broken</option>
            </select>
        </div>
    </div>

    <!-- Boons Table -->
    <div class="character-table-wrapper table-responsive rounded-3">
        <table class="character-table table table-dark table-hover align-middle mb-0" id="boonsTable">
            <thead>
                <tr>
                    <th class="text-start">ID</th>
                    <th class="text-start">Giver</th>
                    <th class="text-start">Receiver</th>
                    <th class="text-center">Type</th>
                    <th class="text-center">Status</th>
                    <th class="text-start">Description</th>
                    <th class="text-center">Created</th>
                    <th class="text-center text-nowrap" style="width: 200px;">Actions</th>
                </tr>
            </thead>
            <tbody id="boonsTableBody">
                <tr>
                    <td colspan="8" class="text-center text-muted">Loading boons...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Boon Modal -->
<div id="boonModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="boonForm">
    <div class="modal-content">
        <div class="modal-header-section">
            <h2 class="modal-title">💎 <span id="modalTitle">New Boon</span></h2>
            <button class="modal-close" onclick="closeBoonModal()">×</button>
        </div>
        
        <form id="boonForm" class="needs-validation" novalidate>
            <input type="hidden" id="boonId" name="boon_id">
            
            <div class="form-group mb-3">
                <label for="giverSelect" class="form-label text-light">Giver Name *</label>
                <select id="giverSelect" name="giver_select" class="form-select bg-dark text-light border-danger" required>
                    <option value="">-- Select Character --</option>
                    <?php foreach ($characters as $char): ?>
                        <option value="<?php echo htmlspecialchars($char['character_name']); ?>">
                            <?php echo htmlspecialchars($char['character_name']); ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="__CUSTOM__">-- Custom / Other --</option>
                </select>
                <div class="invalid-feedback">Please select a giver or provide a custom name.</div>
                <input type="text" id="giverNameCustom" name="giver_name_custom" 
                       class="form-control bg-dark text-light border-danger mt-2" 
                       placeholder="Enter custom name" 
                       style="display: none;">
                <input type="hidden" id="giverName" name="giver_name">
            </div>
            
            <div class="form-group mb-3">
                <label for="receiverSelect" class="form-label text-light">Receiver Name *</label>
                <select id="receiverSelect" name="receiver_select" class="form-select bg-dark text-light border-danger" required>
                    <option value="">-- Select Character --</option>
                    <?php foreach ($characters as $char): ?>
                        <option value="<?php echo htmlspecialchars($char['character_name']); ?>">
                            <?php echo htmlspecialchars($char['character_name']); ?>
                        </option>
                    <?php endforeach; ?>
                    <option value="__CUSTOM__">-- Custom / Other --</option>
                </select>
                <div class="invalid-feedback">Please select a receiver or provide a custom name.</div>
                <input type="text" id="receiverNameCustom" name="receiver_name_custom" 
                       class="form-control bg-dark text-light border-danger mt-2" 
                       placeholder="Enter custom name" 
                       style="display: none;">
                <input type="hidden" id="receiverName" name="receiver_name">
            </div>
            
            <div class="form-group mb-3">
                <label for="boonType" class="form-label text-light">Boon Type *</label>
                <select id="boonType" name="boon_type" class="form-select bg-dark text-light border-danger" required>
                    <option value="Trivial">Trivial</option>
                    <option value="Minor">Minor</option>
                    <option value="Major">Major</option>
                    <option value="Life">Life</option>
                </select>
                <div class="invalid-feedback">Please select a boon type.</div>
            </div>
            
            <div class="form-group mb-3">
                <label for="boonStatus" class="form-label text-light">Status</label>
                <select id="boonStatus" name="status" class="form-select bg-dark text-light border-danger">
                    <option value="Owed">Owed</option>
                    <option value="Called">Called</option>
                    <option value="Paid">Paid</option>
                    <option value="Broken">Broken</option>
                </select>
            </div>
            
            <div class="form-group mb-3">
                <label for="description" class="form-label text-light">Description</label>
                <textarea id="description" name="description" class="form-control bg-dark text-light border-danger" rows="4"></textarea>
            </div>
            
            <div class="form-group mb-3">
                <label for="relatedEvent" class="form-label text-light">Related Event</label>
                <input type="text" id="relatedEvent" name="related_event" class="form-control bg-dark text-light border-danger">
            </div>
            
            <div class="modal-actions">
                <button type="button" class="modal-btn cancel-btn btn btn-secondary" onclick="closeBoonModal()">Cancel</button>
                <button type="submit" class="modal-btn confirm-btn btn btn-primary">Save Boon</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteBoonModal" class="modal" role="dialog" aria-modal="true" aria-label="Confirm Deletion" aria-describedby="deleteBoonInfo">
    <div class="modal-content">
        <h2 class="modal-title">⚠️ Confirm Deletion</h2>
        <p class="modal-message">Delete boon:</p>
        <p class="modal-character-name" id="deleteBoonInfo"></p>
        <div class="modal-actions">
            <button class="modal-btn cancel-btn btn btn-secondary" onclick="closeDeleteBoonModal()">Cancel</button>
            <button class="modal-btn confirm-btn btn btn-danger" id="confirmDeleteBoonBtn">Delete</button>
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

.action-bar { padding: 15px; background: rgba(26, 15, 15, 0.3); border-radius: 5px; }

.character-table-wrapper { 
    background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%); 
    border: 2px solid #8B0000; 
    border-radius: 8px; 
    overflow-x: auto; 
    overflow-y: visible;
    padding: 0 !important;
    margin-left: -20px;
    margin-right: -20px;
    width: calc(100% + 40px);
    box-sizing: border-box;
}

.character-table { 
    width: 100%; 
    border-collapse: collapse; 
    margin: 0;
    padding: 0;
}
.character-table thead { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); }
.character-table th { padding: 15px 12px; text-align: left; font-family: var(--font-title), 'Libre Baskerville', serif; color: #f5e6d3; font-weight: 700; }
.character-table tbody tr { border-bottom: 1px solid rgba(139, 0, 0, 0.2); transition: all 0.2s ease; }
.character-table tbody tr:hover { background: rgba(139, 0, 0, 0.15); }
.character-table td { padding: 12px; font-family: var(--font-body), 'Source Serif Pro', serif; color: #d4c4b0; }

.boon-type-badge, .boon-status-badge { 
    display: inline-block; 
    padding: 4px 10px; 
    border-radius: 4px; 
    font-size: 0.85em; 
    font-weight: bold; 
    color: #f5e6d3;
}

.modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); align-items: center; justify-content: center; }
.modal.active { display: flex; }
.modal-content { background: linear-gradient(135deg, #2a1515 0%, #1a0f0f 100%); border: 3px solid #8B0000; border-radius: 10px; padding: 30px; max-width: 600px; width: 90%; position: relative; }
.modal-header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid rgba(139, 0, 0, 0.3); }
.modal-title { font-family: var(--font-brand), 'IM Fell English', serif; color: #f5e6d3; font-size: 2em; margin: 0; }
.modal-close { background: rgba(139, 0, 0, 0.3); border: 1px solid #8B0000; border-radius: 50%; width: 35px; height: 35px; font-size: 1.5em; color: #f5e6d3; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
.modal-close:hover { background: rgba(139, 0, 0, 0.6); transform: scale(1.1); }
.form-label { font-family: var(--font-body), 'Source Serif Pro', serif; font-weight: 600; }
.modal-actions { display: flex; gap: 15px; justify-content: center; margin-top: 25px; }
.modal-btn { padding: 12px 30px; border-radius: 5px; font-family: var(--font-body), 'Source Serif Pro', serif; font-weight: 600; cursor: pointer; border: 2px solid; }
.cancel-btn { background: rgba(100, 100, 100, 0.2); border-color: #666; color: #d4c4b0; }
.cancel-btn:hover { background: rgba(100, 100, 100, 0.4); }
.confirm-btn { background: linear-gradient(135deg, #8B0000 0%, #600000 100%); border-color: #b30000; color: #f5e6d3; }
.confirm-btn:hover { background: linear-gradient(135deg, #b30000 0%, #8B0000 100%); }
.modal-message { font-family: var(--font-body), 'Source Serif Pro', serif; color: #d4c4b0; font-size: 1.1em; margin-bottom: 10px; }
.modal-character-name { font-family: var(--font-title), 'Libre Baskerville', serif; color: #f5e6d3; font-size: 1.4em; text-align: center; margin: 20px 0; font-weight: bold; }

.action-btn { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 4px; text-decoration: none; font-size: 1.1em; cursor: pointer; background: rgba(139, 0, 0, 0.2); border: 1px solid rgba(139, 0, 0, 0.4); transition: all 0.2s; margin: 0 2px; }
.action-btn:hover { background: rgba(139, 0, 0, 0.4); transform: scale(1.1); }
.view-btn { background: rgba(0, 100, 200, 0.2); border-color: rgba(0, 100, 200, 0.4); }
.view-btn:hover { background: rgba(0, 100, 200, 0.4); }
.paid-btn { background: rgba(0, 150, 0, 0.2); border-color: rgba(0, 150, 0, 0.4); }
.paid-btn:hover { background: rgba(0, 150, 0, 0.4); }
.broken-btn { background: rgba(100, 0, 0, 0.2); border-color: rgba(100, 0, 0, 0.4); }
.broken-btn:hover { background: rgba(100, 0, 0, 0.4); }
.delete-btn { background: rgba(139, 0, 0, 0.2); border-color: rgba(139, 0, 0, 0.4); }
.delete-btn:hover { background: rgba(139, 0, 0, 0.4); }

@media (max-width: 768px) {
    .character-table-wrapper { margin-left: -15px; margin-right: -15px; width: calc(100% + 30px); }
    .modal-content { width: 95%; padding: 20px; }
}
</style>

<script src="../js/admin_boons.js"></script>
<script src="../js/form_validation.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

