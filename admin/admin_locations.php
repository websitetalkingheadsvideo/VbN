<?php
/**
 * Admin - Locations List
 * View and manage all locations
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// TODO: Add proper admin check
// if (!$_SESSION['is_admin']) { header('Location: dashboard.php'); exit(); }

require_once 'includes/connect.php';

// Get all locations
$locations_query = "SELECT * FROM locations ORDER BY type, name";
$locations = $conn->query($locations_query);

define('LOTN_VERSION', '0.2.1');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Locations Manager - VbN Admin</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin_locations.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-map-marked-alt"></i> Locations Manager</h1>
            <p>View and manage all game locations</p>
            <div class="header-actions">
                <a href="admin_create_location.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Create New Location
                </a>
                <a href="dashboard.php" class="btn-secondary">← Back to Dashboard</a>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <div class="filter-group">
                <label for="type-filter">Filter by Type:</label>
                <select id="type-filter">
                    <option value="all">All Types</option>
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

            <div class="filter-group">
                <label for="status-filter">Filter by Status:</label>
                <select id="status-filter">
                    <option value="all">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Abandoned">Abandoned</option>
                    <option value="Destroyed">Destroyed</option>
                    <option value="Contested">Contested</option>
                    <option value="Hidden">Hidden</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="search">Search:</label>
                <input type="text" id="search" placeholder="Search locations...">
            </div>
        </div>

        <!-- Locations Grid -->
        <div class="locations-grid">
            <?php while ($location = $locations->fetch_assoc()): ?>
                <div class="location-card" 
                     data-type="<?= htmlspecialchars($location['type']) ?>"
                     data-status="<?= htmlspecialchars($location['status']) ?>">
                    
                    <div class="card-header">
                        <h3><?= htmlspecialchars($location['name']) ?></h3>
                        <span class="location-type type-<?= strtolower(str_replace(' ', '-', $location['type'])) ?>">
                            <?= htmlspecialchars($location['type']) ?>
                        </span>
                    </div>

                    <?php if ($location['summary']): ?>
                        <p class="location-summary"><?= htmlspecialchars($location['summary']) ?></p>
                    <?php endif; ?>

                    <div class="location-meta">
                        <?php if ($location['district']): ?>
                            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($location['district']) ?></span>
                        <?php endif; ?>
                        
                        <span class="status-badge status-<?= strtolower($location['status']) ?>">
                            <?= htmlspecialchars($location['status']) ?>
                        </span>
                    </div>

                    <div class="location-features">
                        <?php if ($location['owner_type']): ?>
                            <span class="feature-badge">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($location['owner_type']) ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($location['access_control']): ?>
                            <span class="feature-badge">
                                <i class="fas fa-lock"></i> <?= htmlspecialchars($location['access_control']) ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($location['security_level'] >= 4): ?>
                            <span class="feature-badge">
                                <i class="fas fa-shield-alt"></i> High Security
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($location['has_supernatural']): ?>
                            <span class="feature-badge supernatural">
                                <i class="fas fa-magic"></i> Supernatural
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="card-actions">
                        <a href="admin_create_location.php?id=<?= $location['id'] ?>" class="btn-edit">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <button class="btn-view" onclick="viewLocation(<?= $location['id'] ?>)">
                            <i class="fas fa-eye"></i> View
                        </button>
                        <button class="btn-delete" onclick="deleteLocation(<?= $location['id'] ?>, '<?= htmlspecialchars($location['name'], ENT_QUOTES) ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <?php if ($locations->num_rows === 0): ?>
            <div class="empty-state">
                <i class="fas fa-map-marked-alt"></i>
                <h3>No Locations Yet</h3>
                <p>Create your first location to get started!</p>
                <a href="admin_create_location.php" class="btn-primary">
                    <i class="fas fa-plus"></i> Create Location
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/admin_locations.js"></script>
</body>
</html>

