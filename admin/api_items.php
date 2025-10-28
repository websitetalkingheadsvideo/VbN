<?php
/**
 * Items API Endpoint
 * GET /api_items.php - Retrieve items with optional filters
 * 
 * Parameters:
 *   - category: Filter by category (Firearms, Melee, etc.)
 *   - type: Filter by type (Weapon, Armor, Tool, etc.)
 *   - rarity: Filter by rarity (common, uncommon, rare)
 *   - search: Search by name
 *   - limit: Limit number of results (default: all)
 */

header('Content-Type: application/json');
require_once '../includes/connect.php';

try {
    // Build base query - specify columns explicitly (avoid SELECT *)
    $query = "SELECT id, name, type, category, damage, `range`, requirements, description, 
                     rarity, price, image, notes, created_at 
              FROM items WHERE 1=1";
    $params = [];
    $types = '';
    
    // Add filters if provided
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $query .= " AND category = ?";
        $params[] = $_GET['category'];
        $types .= 's';
    }
    
    if (isset($_GET['type']) && !empty($_GET['type'])) {
        $query .= " AND type = ?";
        $params[] = $_GET['type'];
        $types .= 's';
    }
    
    if (isset($_GET['rarity']) && !empty($_GET['rarity'])) {
        $query .= " AND rarity = ?";
        $params[] = $_GET['rarity'];
        $types .= 's';
    }
    
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $query .= " AND name LIKE ?";
        $params[] = '%' . $_GET['search'] . '%';
        $types .= 's';
    }
    
    // Add ordering
    $query .= " ORDER BY category, name";
    
    // Add limit if specified
    if (isset($_GET['limit']) && is_numeric($_GET['limit'])) {
        $query .= " LIMIT ?";
        $params[] = (int)$_GET['limit'];
        $types .= 'i';
    }
    
    // Prepare and execute
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        // Decode JSON fields
        $row['requirements'] = json_decode($row['requirements'], true);
        $row['mechanics'] = json_decode($row['mechanics'], true);
        $items[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'count' => count($items),
        'items' => $items
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>

