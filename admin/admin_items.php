<?php
/**
 * Admin Items Management
 * CRUD operations for items database and character equipment assignment
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

define('LOTN_VERSION', '0.2.1');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../includes/connect.php';
include __DIR__ . '/../includes/header.php';

// Get items statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN type = 'Weapon' THEN 1 ELSE 0 END) as weapons,
    SUM(CASE WHEN type = 'Armor' THEN 1 ELSE 0 END) as armor,
    SUM(CASE WHEN type = 'Tool' THEN 1 ELSE 0 END) as tools,
    SUM(CASE WHEN type = 'Consumable' THEN 1 ELSE 0 END) as consumables,
    SUM(CASE WHEN type = 'Artifact' THEN 1 ELSE 0 END) as artifacts,
    SUM(CASE WHEN type = 'Misc' THEN 1 ELSE 0 END) as misc
    FROM items";
$stats_result = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get all unique types and categories for filters
$types_query = "SELECT DISTINCT type FROM items ORDER BY type";
$types_result = mysqli_query($conn, $types_query);
$item_types = [];
while ($type_row = $types_result->fetch_assoc()) {
    $item_types[] = $type_row['type'];
}

$categories_query = "SELECT DISTINCT category FROM items ORDER BY category";
$categories_result = mysqli_query($conn, $categories_query);
$item_categories = [];
while ($cat_row = $categories_result->fetch_assoc()) {
    $item_categories[] = $cat_row['category'];
}

// Get all characters for equipment assignment
$characters_query = "SELECT id, character_name, clan, player_name FROM characters ORDER BY character_name";
$characters_result = mysqli_query($conn, $characters_query);
$all_characters = [];
while ($char = $characters_result->fetch_assoc()) {
    $all_characters[] = $char;
}
?>

<div class="admin-items-container">
    <h1 class="panel-title">⚔️ Items Database Management</h1>
    <p class="panel-subtitle">Manage items database and assign equipment to characters</p>
    
    <!-- Admin Navigation -->
    <div class="admin-nav">
        <a href="admin_panel.php" class="nav-btn">👥 Characters</a>
        <a href="admin_sire_childe.php" class="nav-btn">🧛 Sire/Childe</a>
        <a href="admin_items.php" class="nav-btn active">⚔️ Items</a>
        <a href="admin_locations.php" class="nav-btn">📍 Locations</a>
        <a href="questionnaire_admin.php" class="nav-btn">📝 Questionnaire</a>
        <a href="admin_npc_briefing.php" class="nav-btn">📋 NPC Briefing</a>
    </div>
    
    <!-- Items Statistics -->
    <div class="items-stats">
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['total'] ?? 0; ?></span>
            <span class="stat-label">Total Items</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['weapons'] ?? 0; ?></span>
            <span class="stat-label">Weapons</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['armor'] ?? 0; ?></span>
            <span class="stat-label">Armor</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['tools'] ?? 0; ?></span>
            <span class="stat-label">Tools</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['consumables'] ?? 0; ?></span>
            <span class="stat-label">Consumables</span>
        </div>
        <div class="stat-mini">
            <span class="stat-number"><?php echo $stats['artifacts'] ?? 0; ?></span>
            <span class="stat-label">Artifacts</span>
        </div>
    </div>

    <!-- Filter Controls -->
    <div class="filter-controls">
        <div class="filter-buttons">
            <button class="filter-btn active" data-filter="all">All Items</button>
            <button class="filter-btn" data-filter="weapons">Weapons</button>
            <button class="filter-btn" data-filter="armor">Armor</button>
            <button class="filter-btn" data-filter="tools">Tools</button>
            <button class="filter-btn" data-filter="consumables">Consumables</button>
            <button class="filter-btn" data-filter="artifacts">Artifacts</button>
        </div>
        <div class="type-filter">
            <label for="typeFilter">Type:</label>
            <select id="typeFilter">
                <option value="all">All Types</option>
                <?php foreach ($item_types as $type): ?>
                    <option value="<?php echo htmlspecialchars($type); ?>"><?php echo htmlspecialchars($type); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="rarity-filter">
            <label for="rarityFilter">Rarity:</label>
            <select id="rarityFilter">
                <option value="all">All Rarities</option>
                <option value="common">Common</option>
                <option value="uncommon">Uncommon</option>
                <option value="rare">Rare</option>
                <option value="epic">Epic</option>
                <option value="legendary">Legendary</option>
            </select>
        </div>
        <div class="search-box">
            <input type="text" id="itemSearch" placeholder="🔍 Search by name..." />
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

    <!-- Add Item Button -->
    <div style="margin-bottom: 20px;">
        <button class="modal-btn confirm-btn" onclick="openAddItemModal()">
            <i class="fas fa-plus"></i> Add New Item
        </button>
    </div>

    <!-- Items Table -->
    <div class="items-table-wrapper table-responsive">
        <table class="items-table" id="itemsTable">
            <thead>
                <tr>
                    <th data-sort="id">ID <span class="sort-icon">⇅</span></th>
                    <th data-sort="name">Name <span class="sort-icon">⇅</span></th>
                    <th data-sort="type">Type <span class="sort-icon">⇅</span></th>
                    <th data-sort="category">Category <span class="sort-icon">⇅</span></th>
                    <th data-sort="damage">Damage <span class="sort-icon">⇅</span></th>
                    <th data-sort="range">Range <span class="sort-icon">⇅</span></th>
                    <th data-sort="rarity">Rarity <span class="sort-icon">⇅</span></th>
                    <th data-sort="price">Price <span class="sort-icon">⇅</span></th>
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

<!-- Add/Edit Item Modal -->
<div id="itemModal" class="modal">
    <div class="modal-content large-modal">
        <h2 class="modal-title">📦 <span id="itemModalTitle">Add New Item</span></h2>
        <button class="modal-close" onclick="closeItemModal()">×</button>
        
        <form id="itemForm">
            <input type="hidden" id="itemId" name="id">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="itemName">Name *</label>
                    <input type="text" id="itemName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="itemType">Type *</label>
                    <select id="itemType" name="type" required>
                        <option value="">Select Type</option>
                        <option value="Weapon">Weapon</option>
                        <option value="Armor">Armor</option>
                        <option value="Tool">Tool</option>
                        <option value="Consumable">Consumable</option>
                        <option value="Artifact">Artifact</option>
                        <option value="Misc">Misc</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="itemCategory">Category *</label>
                    <input type="text" id="itemCategory" name="category" required>
                </div>
                <div class="form-group">
                    <label for="itemRarity">Rarity *</label>
                    <select id="itemRarity" name="rarity" required>
                        <option value="">Select Rarity</option>
                        <option value="common">Common</option>
                        <option value="uncommon">Uncommon</option>
                        <option value="rare">Rare</option>
                        <option value="epic">Epic</option>
                        <option value="legendary">Legendary</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="itemDamage">Damage</label>
                    <input type="text" id="itemDamage" name="damage" placeholder="e.g., 2L, 3B">
                </div>
                <div class="form-group">
                    <label for="itemRange">Range</label>
                    <input type="text" id="itemRange" name="range" placeholder="e.g., Close, Medium">
                </div>
            </div>
            
            <div class="form-group">
                <label for="itemPrice">Price *</label>
                <input type="number" id="itemPrice" name="price" required min="0">
            </div>
            
            <div class="form-group">
                <label for="itemDescription">Description *</label>
                <textarea id="itemDescription" name="description" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="itemRequirements">Requirements (JSON)</label>
                <textarea id="itemRequirements" name="requirements" placeholder='{"strength": 3, "dexterity": 2}'></textarea>
            </div>
            
            <div class="form-group">
                <label for="itemImage">Image URL</label>
                <input type="url" id="itemImage" name="image" placeholder="https://example.com/image.jpg">
            </div>
            
            <div class="form-group">
                <label for="itemNotes">Notes</label>
                <textarea id="itemNotes" name="notes"></textarea>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="modal-btn cancel-btn" onclick="closeItemModal()">Cancel</button>
                <button type="submit" class="modal-btn confirm-btn">Save Item</button>
            </div>
        </form>
    </div>
</div>

<!-- View Item Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content large-modal">
        <h2 class="modal-title">📄 <span id="viewItemName">Item Details</span></h2>
        <button class="modal-close" onclick="closeViewModal()">×</button>
        
        <div id="viewItemContent" class="view-content">
            Loading...
        </div>
        <div class="modal-actions">
            <button class="modal-btn cancel-btn" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<!-- Equipment Assignment Modal -->
<div id="assignModal" class="modal">
    <div class="modal-content">
        <h2 class="modal-title">🎯 Assign Item to Characters</h2>
        <button class="modal-close" onclick="closeAssignModal()">×</button>
        
        <div class="modal-message">
            Assign <strong id="assignItemName"></strong> to characters:
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
                        <input type="number" class="quantity-input" value="1" min="1" max="99" 
                               data-character-id="<?php echo $char['id']; ?>">
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="modal-actions">
            <button class="modal-btn cancel-btn" onclick="closeAssignModal()">Cancel</button>
            <button class="modal-btn confirm-btn" onclick="assignItemsToCharacters()">Assign Items</button>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h2 class="modal-title">⚠️ Confirm Deletion</h2>
        <p class="modal-message">Delete item:</p>
        <p class="modal-character-name" id="deleteItemName"></p>
        <p class="modal-warning" id="deleteWarning" style="display:none;">
            ⚠️ <strong>This item is assigned to characters</strong> - remove assignments first!
        </p>
        <div class="modal-actions">
            <button class="modal-btn cancel-btn" onclick="closeDeleteModal()">Cancel</button>
            <button class="modal-btn confirm-btn" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>

<!-- Include external CSS -->
<link rel="stylesheet" href="../css/admin_items.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Pass PHP data to JavaScript -->
<script>
    const allCharacters = <?php echo json_encode($all_characters); ?>;
    const itemTypes = <?php echo json_encode($item_types); ?>;
    const itemCategories = <?php echo json_encode($item_categories); ?>;
</script>

<!-- Include the external JavaScript file -->
<script src="../js/admin_items.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
