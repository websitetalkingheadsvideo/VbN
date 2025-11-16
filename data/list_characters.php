<?php
/**
 * Character Listing Page
 * Shows all characters in the database
 */

require_once __DIR__ . '/../includes/connect.php';

if (!$conn) {
    die("❌ Database connection failed");
}

// Get all characters
$result = $conn->query("
    SELECT 
        id,
        character_name,
        clan,
        generation,
        concept,
        pc,
        player_name,
        created_at
    FROM characters 
    ORDER BY id DESC
");

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-4">
    <h1 class="h3 text-light text-center mb-4">Valley by Night - Character Database</h1>
    
    <?php
    $total = $result->num_rows;
    $pcs = $conn->query("SELECT COUNT(*) as count FROM characters WHERE pc = 1")->fetch_assoc()['count'];
    $npcs = $total - $pcs;
    
    // Get clan distribution
    $clans = $conn->query("SELECT clan, COUNT(*) as count FROM characters GROUP BY clan ORDER BY count DESC LIMIT 5");
    ?>
    
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-uppercase text-muted small">Total Characters</div>
                    <div class="display-6 text-light"><?php echo $total; ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-uppercase text-muted small">Player Characters</div>
                    <div class="display-6 text-light"><?php echo $pcs; ?></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-uppercase text-muted small">NPCs</div>
                    <div class="display-6 text-light"><?php echo $npcs; ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($total == 0): ?>
        <div class="alert alert-warning text-center" role="alert">No characters in database yet.</div>
    <?php else: ?>
        <div class="table-responsive rounded-3">
            <table class="table table-dark table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-start">ID</th>
                        <th class="text-start">Name</th>
                        <th class="text-start">Clan</th>
                        <th class="text-center">Gen</th>
                        <th class="text-start">Concept</th>
                        <th class="text-center">Type</th>
                        <th class="text-start">Player</th>
                        <th class="text-center">Created</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($char = $result->fetch_assoc()): ?>
                        <tr>
                            <td class="text-light"><?php echo $char['id']; ?></td>
                            <td class="text-light">
                                <a href="view_character.php?id=<?php echo $char['id']; ?>" class="link-light fw-bold text-decoration-none">
                                    <?php echo htmlspecialchars($char['character_name']); ?>
                                </a>
                            </td>
                            <td class="text-light"><?php echo htmlspecialchars($char['clan']); ?></td>
                            <td class="text-center text-light"><?php echo $char['generation']; ?>th</td>
                            <td class="text-light fst-italic"><?php echo htmlspecialchars($char['concept']); ?></td>
                            <td class="text-center">
                                <?php if ($char['pc']): ?>
                                    <span class="badge bg-success text-dark">PC</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">NPC</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-light"><?php echo htmlspecialchars($char['player_name']); ?></td>
                            <td class="text-center text-light"><?php echo date('Y-m-d', strtotime($char['created_at'])); ?></td>
                            <td class="text-center">
                                <a href="delete_character.php?id=<?php echo $char['id']; ?>" 
                                   class="btn btn-outline-danger btn-sm"
                                   title="Delete character">
                                    🗑️ Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div class="card mt-4">
        <div class="card-body">
            <h2 class="h5 text-light">📥 Import New Characters</h2>
            <p class="mb-2 text-muted">To import new characters from JSON:</p>
            <ul class="mb-0">
                <li class="mb-1">
                    🔹 <strong>Jax:</strong> <a href="import_character.php?file=jax.json" class="link-primary">Import jax.json</a>
                </li>
                <li class="mb-1">
                    🔹 <strong>Violet:</strong> <a href="import_character.php?file=Violet.json" class="link-primary">Import Violet.json</a>
                </li>
                <li class="mb-1">
                    🔹 <a href="IMPORT_GUIDE.md" class="link-secondary">View Import Guide</a>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../includes/footer.php';
$conn->close();
?>

