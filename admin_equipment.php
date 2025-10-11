<?php
/**
 * Admin Equipment Manager
 * Assign/remove items to/from characters
 */

session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// TODO: Add proper admin check
// if (!$_SESSION['is_admin']) { header('Location: dashboard.php'); exit(); }

require_once 'includes/connect.php';

// Get all characters
$characters_query = "SELECT id, character_name, clan, player_name FROM characters ORDER BY character_name";
$characters = $conn->query($characters_query);

// Get all unique item types for dropdown
$types_query = "SELECT DISTINCT type FROM items ORDER BY type";
$types_result = $conn->query($types_query);
$item_types = [];
while ($type_row = $types_result->fetch_assoc()) {
    $item_types[] = $type_row['type'];
}

// Get all items organized by category
$items_query = "SELECT * FROM items ORDER BY category, name";
$items_result = $conn->query($items_query);
$all_items = [];
while ($row = $items_result->fetch_assoc()) {
    $row['requirements'] = json_decode($row['requirements'], true);
    $all_items[] = $row;
}

// Group items by category
$items_by_category = [];
foreach ($all_items as $item) {
    $items_by_category[$item['category']][] = $item;
}

define('LOTN_VERSION', '0.2.1');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Equipment Manager - VbN</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin_equipment.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-equipment-container">
        <div class="admin-header">
            <h1><i class="fas fa-shield-alt"></i> Admin Equipment Manager</h1>
            <p>Assign items to characters and manage their inventory</p>
            <a href="dashboard.php" class="btn-secondary">← Back to Dashboard</a>
        </div>

        <div class="admin-layout">
            <!-- Character List -->
            <div class="character-list">
                <h3>Characters</h3>
                <input type="text" id="character-search" placeholder="Search characters..." 
                       style="width: 100%; padding: 8px; margin-bottom: 15px; border-radius: 4px; border: 1px solid var(--border-color);">
                
                <div id="character-list-items">
                    <?php while ($char = $characters->fetch_assoc()): ?>
                        <div class="character-item" data-character-id="<?= $char['id'] ?>">
                            <h4><?= htmlspecialchars($char['character_name']) ?></h4>
                            <small><?= htmlspecialchars($char['clan']) ?></small><br>
                            <small>Player: <?= htmlspecialchars($char['player_name']) ?></small>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Equipment Manager -->
            <div class="equipment-manager">
                <div id="no-character-selected" class="empty-state">
                    <i class="fas fa-hand-pointer"></i>
                    <h3>Select a Character</h3>
                    <p>Choose a character from the list to manage their equipment</p>
                </div>

                <div id="equipment-interface" style="display: none;">
                    <h2 id="selected-character-name"></h2>

                    <!-- Tabs -->
                    <div class="equipment-tabs">
                        <button class="equipment-tab-btn active" data-tab="current">
                            <i class="fas fa-backpack"></i> Current Inventory
                        </button>
                        <button class="equipment-tab-btn" data-tab="add">
                            <i class="fas fa-plus-circle"></i> Add Items
                        </button>
                    </div>

                    <!-- Current Inventory Tab -->
                    <div id="tab-current" class="equipment-tab-content active">
                        <div class="current-inventory">
                            <div id="current-inventory-grid" class="inventory-grid">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Add Items Tab -->
                    <div id="tab-add" class="equipment-tab-content">
                        <div class="add-items-section">
                            <!-- Filters -->
                            <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                                <div style="flex: 1;">
                                    <label for="type-filter" style="display: block; margin-bottom: 5px; font-weight: bold;">Filter by Type:</label>
                                    <select id="type-filter" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid var(--border-color); background: var(--bg-primary); color: var(--text-primary);">
                                        <option value="all">All Types (<?= count($all_items) ?>)</option>
                                        <?php foreach ($item_types as $type): ?>
                                            <?php 
                                                // Count items of this type
                                                $type_count = count(array_filter($all_items, function($item) use ($type) {
                                                    return $item['type'] === $type;
                                                }));
                                            ?>
                                            <option value="<?= htmlspecialchars($type) ?>">
                                                <?= htmlspecialchars($type) ?> (<?= $type_count ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                    <label for="item-search" style="display: block; margin-bottom: 5px; font-weight: bold;">Search:</label>
                                    <input type="text" id="item-search" placeholder="Search items..." 
                                           style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid var(--border-color);">
                                </div>
                            </div>
                            
                            <div id="items-catalog">
                                <?php foreach ($items_by_category as $category => $items): ?>
                                    <div class="category-section">
                                        <div class="category-header">
                                            <span><i class="fas fa-caret-down"></i> <?= htmlspecialchars($category) ?> (<?= count($items) ?>)</span>
                                        </div>
                                        <div class="category-items">
                                            <?php foreach ($items as $item): ?>
                                                <div class="add-item-card" data-item-id="<?= $item['id'] ?>" data-item-type="<?= htmlspecialchars($item['type']) ?>">
                                                    <h5><?= htmlspecialchars($item['name']) ?></h5>
                                                    <div class="item-stats">
                                                        <span class="stat-badge"><?= htmlspecialchars($item['type']) ?></span>
                                                        <?php if ($item['damage'] != 'N/A'): ?>
                                                            <span class="stat-badge">⚔️ <?= htmlspecialchars($item['damage']) ?></span>
                                                        <?php endif; ?>
                                                        <span class="stat-badge rarity-<?= $item['rarity'] ?>">
                                                            <?= htmlspecialchars($item['rarity']) ?>
                                                        </span>
                                                    </div>
                                                    <p style="font-size: 0.9em; margin: 8px 0;"><?= htmlspecialchars(substr($item['description'], 0, 100)) ?>...</p>
                                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                                                        <strong style="color: var(--accent-color);">$<?= number_format($item['price']) ?></strong>
                                                        <button class="add-item-btn" onclick="addItemToCharacter(<?= $item['id'] ?>)">
                                                            <i class="fas fa-plus"></i> Add
                                                        </button>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Pass PHP data to JavaScript
        const itemsData = <?= json_encode($all_items) ?>;
    </script>
    <script src="js/admin_equipment.js"></script>
</body>
</html>