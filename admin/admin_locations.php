<?php
/**
 * Admin Locations Management
 * CRUD operations for locations database and character assignment
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('LOTN_VERSION', '0.7.5');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../includes/connect.php';
include __DIR__ . '/../includes/header.php';

// Get locations statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN type = 'Haven' THEN 1 ELSE 0 END) as havens,
    SUM(CASE WHEN type = 'Elysium' THEN 1 ELSE 0 END) as elysiums,
    SUM(CASE WHEN type = 'Domain' THEN 1 ELSE 0 END) as domains,
    SUM(CASE WHEN type = 'Hunting Ground' THEN 1 ELSE 0 END) as hunting_grounds,
    SUM(CASE WHEN type = 'Nightclub' THEN 1 ELSE 0 END) as nightclubs,
    SUM(CASE WHEN type = 'Business' THEN 1 ELSE 0 END) as businesses,
    SUM(CASE WHEN type = 'Other' THEN 1 ELSE 0 END) as other
    FROM locations";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get all unique types, statuses, and owner types for filters
$types_query = "SELECT DISTINCT type FROM locations ORDER BY type";
$types_result = mysqli_query($conn, $types_query);
$location_types = [];
while ($type_row = $types_result->fetch_assoc()) {
    $location_types[] = $type_row['type'];
}

$statuses_query = "SELECT DISTINCT status FROM locations ORDER BY status";
$statuses_result = mysqli_query($conn, $statuses_query);
$location_statuses = [];
while ($status_row = $statuses_result->fetch_assoc()) {
    $location_statuses[] = $status_row['status'];
}

$owners_query = "SELECT DISTINCT owner_type FROM locations ORDER BY owner_type";
$owners_result = mysqli_query($conn, $owners_query);
$location_owners = [];
while ($owner_row = $owners_result->fetch_assoc()) {
    $location_owners[] = $owner_row['owner_type'];
}

// Get all characters for assignment
$characters_query = "SELECT id, character_name, clan, player_name FROM characters ORDER BY character_name";
$characters_result = mysqli_query($conn, $characters_query);
$all_characters = [];
while ($char = $characters_result->fetch_assoc()) {
    $all_characters[] = $char;
}
?>

<div class="admin-locations-container">
    <h1 class="panel-title">🏠 Locations Database Management</h1>
    <p class="panel-subtitle">Manage locations database and assign characters to locations</p>
    
    <!-- Admin Navigation -->
    <div class="admin-nav">
        <a href="admin_panel.php" class="nav-btn">👥 Characters</a>
        <a href="admin_sire_childe.php" class="nav-btn">🧛 Sire/Childe</a>
        <a href="admin_items.php" class="nav-btn">⚔️ Items</a>
        <a href="admin_locations.php" class="nav-btn active">🏠 Locations</a>
        <a href="questionnaire_admin.php" class="nav-btn">📝 Questionnaire</a>
        <a href="admin_npc_briefing.php" class="nav-btn">📋 NPC Briefing</a>
    </div>
    
    <!-- Locations Statistics -->
    <div class="locations-stats">
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['total'] ?? 0; ?></span>
            <span class="stat-label">Total Locations</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['havens'] ?? 0; ?></span>
            <span class="stat-label">Havens</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['elysiums'] ?? 0; ?></span>
            <span class="stat-label">Elysiums</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['domains'] ?? 0; ?></span>
            <span class="stat-label">Domains</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['hunting_grounds'] ?? 0; ?></span>
            <span class="stat-label">Hunting Grounds</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['nightclubs'] ?? 0; ?></span>
            <span class="stat-label">Nightclubs</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['businesses'] ?? 0; ?></span>
            <span class="stat-label">Businesses</span>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="filter-controls">
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All Locations</button>
            <button class="filter-btn" data-filter="havens">Havens</button>
            <button class="filter-btn" data-filter="elysiums">Elysiums</button>
            <button class="filter-btn" data-filter="domains">Domains</button>
            <button class="filter-btn" data-filter="hunting-grounds">Hunting Grounds</button>
            <button class="filter-btn" data-filter="nightclubs">Nightclubs</button>
            <button class="filter-btn" data-filter="businesses">Businesses</button>
        </div>
        <div class="type-filter">
            <label for="typeFilter">Type:</label>
            <select id="typeFilter">
                <option value="all">All Types</option>
                <?php foreach ($location_types as $type): ?>
                    <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="status-filter">
            <label for="statusFilter">Status:</label>
            <select id="statusFilter">
                <option value="all">All Status</option>
                <?php foreach ($location_statuses as $status): ?>
                    <option value="<?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars($status); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="owner-filter">
            <label for="ownerFilter">Owner:</label>
            <select id="ownerFilter">
                <option value="all">All Owners</option>
                <?php foreach ($location_owners as $owner): ?>
                    <option value="<?php echo htmlspecialchars($owner); ?>"><?php echo htmlspecialchars($owner); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="search-box">
            <input type="text" id="locationSearch" placeholder="🔍 Search by name..." />
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

    <!-- Add Location Button -->
    <div style="margin-bottom: 20px;">
        <button class="modal-btn confirm-btn" onclick="openAddLocationModal()">
            <i class="fas fa-plus"></i> Add New Location
        </button>
    </div>

    <!-- Locations Table -->
    <div class="locations-table-wrapper table-responsive">
        <table class="locations-table" id="locationsTable">
            <thead>
                <tr>
                    <th data-sort="id">ID <span class="sort-icon">⇅</span></th>
                    <th data-sort="name">Name <span class="sort-icon">⇅</span></th>
                    <th data-sort="type">Type <span class="sort-icon">⇅</span></th>
                    <th data-sort="status">Status <span class="sort-icon">⇅</span></th>
                    <th data-sort="district">District <span class="sort-icon">⇅</span></th>
                    <th data-sort="owner_type">Owner Type <span class="sort-icon">⇅</span></th>
                    <th data-sort="created_at">Created <span class="sort-icon">⇅</span></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Populated by JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <div class="pagination-controls" id="paginationControls">
        <div class="pagination-info">
            <span id="paginationInfo">Loading...</span>
        </div>
        <div class="pagination-buttons" id="paginationButtons">
            <!-- Buttons will be generated by JavaScript -->
        </div>
    </div>
</div>

<!-- Add/Edit Location Modal -->
<div id="locationModal" class="modal">
    <div class="modal-content large-modal">
        <h2 class="modal-title">🏠 <span id="locationModalTitle">Add New Location</span></h2>
        <button class="modal-close" onclick="closeLocationModal()">×</button>
        
        <form id="locationForm">
            <input type="hidden" id="locationId" name="id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="locationName">Name *</label>
                    <input type="text" id="locationName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="locationType">Type *</label>
                    <select id="locationType" name="type" required>
                        <option value="">Select Type</option>
                        <option value="Haven">Haven</option>
                        <option value="Elysium">Elysium</option>
                        <option value="Domain">Domain</option>
                        <option value="Hunting Ground">Hunting Ground</option>
                        <option value="Nightclub">Nightclub</option>
                        <option value="Gathering Place">Gathering Place</option>
                        <option value="Business">Business</option>
                        <option value="Chantry">Chantry</option>
                        <option value="Temple">Temple</option>
                        <option value="Wilderness">Wilderness</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="locationStatus">Status *</label>
                    <select id="locationStatus" name="status" required>
                        <option value="">Select Status</option>
                        <option value="Active">Active</option>
                        <option value="Abandoned">Abandoned</option>
                        <option value="Destroyed">Destroyed</option>
                        <option value="Contested">Contested</option>
                        <option value="Hidden">Hidden</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="locationDistrict">District</label>
                    <input type="text" id="locationDistrict" name="district" placeholder="e.g., Downtown, Warehouse District">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="locationOwnerType">Owner Type *</label>
                    <select id="locationOwnerType" name="owner_type" required>
                        <option value="">Select Owner Type</option>
                        <option value="Personal">Personal</option>
                        <option value="Clan">Clan</option>
                        <option value="Sect">Sect</option>
                        <option value="Coterie">Coterie</option>
                        <option value="NPC">NPC</option>
                        <option value="Contested">Contested</option>
                        <option value="Public">Public</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="locationFaction">Faction</label>
                    <input type="text" id="locationFaction" name="faction" placeholder="e.g., Camarilla, Sabbat">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="locationAccessControl">Access Control *</label>
                    <select id="locationAccessControl" name="access_control" required>
                        <option value="">Select Access Control</option>
                        <option value="Open">Open</option>
                        <option value="Restricted">Restricted</option>
                        <option value="Private">Private</option>
                        <option value="Secret">Secret</option>
                        <option value="Invitation Only">Invitation Only</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="locationSecurityLevel">Security Level</label>
                    <input type="number" id="locationSecurityLevel" name="security_level" min="1" max="10" value="3">
                </div>
            </div>
            
            <div class="form-group">
                <label for="locationDescription">Description</label>
                <textarea id="locationDescription" name="description" placeholder="Detailed description of the location..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="locationSummary">Summary</label>
                <textarea id="locationSummary" name="summary" placeholder="Brief summary for quick reference..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="locationNotes">Notes</label>
                <textarea id="locationNotes" name="notes" placeholder="Additional notes, plot hooks, etc..."></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="modal-btn cancel-btn" onclick="closeLocationModal()">Cancel</button>
                <button type="submit" class="modal-btn confirm-btn">Save Location</button>
            </div>
        </form>
    </div>
</div>

<!-- View Location Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content large-modal">
        <h2 class="modal-title">📄 <span id="viewLocationName">Location Details</span></h2>
        <button class="modal-close" onclick="closeViewModal()">×</button>
        
        <div id="viewLocationContent" class="view-content">
            Loading...
        </div>
        <div class="modal-actions">
            <button class="modal-btn cancel-btn" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<!-- Character Assignment Modal -->
<div id="assignModal" class="modal">
    <div class="modal-content">
        <h2 class="modal-title">🎯 Assign Characters to Location</h2>
        <button class="modal-close" onclick="closeAssignModal()">×</button>
        
        <div class="modal-message">
            Assign characters to <strong id="assignLocationName"></strong>:
        </div>
        
        <div class="character-selection">
            <?php foreach ($all_characters as $char): ?>
                <div class="character-item" data-character-id="<?php echo $char['id']; ?>">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong><?php echo htmlspecialchars($char['character_name']); ?></strong>
                            <small style="display: block; color: #b8a090;">
                                <?php echo htmlspecialchars($char['clan']); ?> - 
                                <?php echo htmlspecialchars($char['player_name']); ?>
                            </small>
                        </div>
                        <select class="assignment-type-select" data-character-id="<?php echo $char['id']; ?>">
                            <option value="Resident">Resident</option>
                            <option value="Owner">Owner</option>
                            <option value="Visitor">Visitor</option>
                            <option value="Staff">Staff</option>
                            <option value="Guard">Guard</option>
                        </select>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="modal-actions">
            <button class="modal-btn cancel-btn" onclick="closeAssignModal()">Cancel</button>
            <button class="modal-btn confirm-btn" onclick="assignCharactersToLocation()">Assign Characters</button>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h2 class="modal-title">⚠️ Confirm Deletion</h2>
        <p class="modal-message">Delete location:</p>
        <p class="modal-character-name" id="deleteLocationName"></p>
        <p class="modal-warning" id="deleteWarning" style="display:none;">
            ⚠️ <strong>This location has character assignments</strong> - remove assignments first!
        </p>
        <div class="modal-actions">
            <button class="modal-btn cancel-btn" onclick="closeDeleteModal()">Cancel</button>
            <button class="modal-btn confirm-btn" id="confirmDeleteBtn" onclick="confirmDeleteLocation()">Delete</button>
        </div>
    </div>
</div>

<!-- Include external CSS -->
<link rel="stylesheet" href="../css/admin_locations.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Pass PHP data to JavaScript -->
<script>
    const allCharacters = <?php echo json_encode($all_characters); ?>;
    const locationTypes = <?php echo json_encode($location_types); ?>;
    const locationStatuses = <?php echo json_encode($location_statuses); ?>;
    const locationOwners = <?php echo json_encode($location_owners); ?>;
</script>

<!-- Include the external JavaScript file -->
<script src="../js/admin_locations.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>