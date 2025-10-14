<?php
/**
 * NPC Tracker - View Page
 * Displays all tracked NPCs that need character sheets
 */

session_start();
require_once '../includes/connect.php';

// Require admin access
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch all NPCs
$stmt = $pdo->query("
    SELECT n.*, u.username as submitted_by_name 
    FROM npc_tracker n 
    LEFT JOIN users u ON n.submitted_by = u.user_id 
    ORDER BY 
        CASE 
            WHEN n.status = '📝 Ready for Sheet' THEN 1
            WHEN n.status = '💡 Concept Only' THEN 2
            WHEN n.status = '✅ Sheet Complete' THEN 3
            WHEN n.status = '❌ On Hold' THEN 4
            ELSE 5
        END,
        n.created_at DESC
");
$npcs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group by status for display
$grouped_npcs = [
    '📝 Ready for Sheet' => [],
    '💡 Concept Only' => [],
    '✅ Sheet Complete' => [],
    '❌ On Hold' => []
];

foreach ($npcs as $npc) {
    $status = $npc['status'] ?? '💡 Concept Only';
    if (isset($grouped_npcs[$status])) {
        $grouped_npcs[$status][] = $npc;
    }
}

define('LOTN_VERSION', '0.2.0');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NPC Creation Tracker - Valley by Night</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/header.css">
    <style>
        .tracker-container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 20px;
        }

        .tracker-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--gold);
        }

        .tracker-header h1 {
            color: var(--gold);
            font-family: 'Cinzel', serif;
            margin: 0;
        }

        .add-button {
            background: var(--gold);
            color: var(--blood-red);
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .add-button:hover {
            background: var(--light-gold);
            transform: translateY(-2px);
        }

        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(139, 115, 85, 0.2);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--gold);
            text-align: center;
        }

        .stat-card .number {
            font-size: 2em;
            color: var(--gold);
            font-weight: bold;
        }

        .stat-card .label {
            color: var(--light-text);
            font-size: 0.9em;
        }

        .status-section {
            margin-bottom: 40px;
        }

        .status-section h2 {
            color: var(--gold);
            font-family: 'Cinzel', serif;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--gold);
        }

        .npc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: rgba(26, 15, 15, 0.6);
        }

        .npc-table th {
            background: rgba(139, 115, 85, 0.3);
            color: var(--gold);
            padding: 12px;
            text-align: left;
            border: 1px solid var(--gold);
            font-family: 'Cinzel', serif;
        }

        .npc-table td {
            padding: 10px 12px;
            border: 1px solid rgba(139, 115, 85, 0.3);
            color: var(--light-text);
        }

        .npc-table tr:hover {
            background: rgba(139, 115, 85, 0.2);
        }

        .npc-name {
            font-weight: bold;
            color: var(--gold);
            cursor: pointer;
        }

        .npc-details {
            display: none;
            padding: 15px;
            background: rgba(26, 15, 15, 0.8);
            margin: 10px 0;
            border-left: 3px solid var(--gold);
        }

        .npc-details.active {
            display: block;
        }

        .detail-section {
            margin-bottom: 15px;
        }

        .detail-section h4 {
            color: var(--gold);
            margin-bottom: 5px;
        }

        .detail-section p {
            color: var(--light-text);
            white-space: pre-wrap;
        }

        .action-links {
            display: flex;
            gap: 10px;
        }

        .action-links a {
            color: var(--gold);
            text-decoration: none;
            padding: 4px 8px;
            border: 1px solid var(--gold);
            border-radius: 4px;
            font-size: 0.9em;
        }

        .action-links a:hover {
            background: var(--gold);
            color: var(--blood-red);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--light-text);
            font-style: italic;
        }

        .introduced-link {
            color: var(--light-gold);
            text-decoration: none;
        }

        .introduced-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="tracker-container">
        <div class="tracker-header">
            <h1>📚 NPC Creation Tracker</h1>
            <a href="npc_tracker_submit.php" class="add-button">+ Add New NPC</a>
        </div>

        <div class="quick-stats">
            <div class="stat-card">
                <div class="number"><?php echo count($grouped_npcs['📝 Ready for Sheet']); ?></div>
                <div class="label">Ready for Sheet</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo count($grouped_npcs['💡 Concept Only']); ?></div>
                <div class="label">Concept Only</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo count($grouped_npcs['✅ Sheet Complete']); ?></div>
                <div class="label">Complete</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo count($npcs); ?></div>
                <div class="label">Total NPCs</div>
            </div>
        </div>

        <?php foreach ($grouped_npcs as $status => $npc_list): ?>
            <?php if (count($npc_list) > 0): ?>
                <div class="status-section">
                    <h2><?php echo $status; ?> (<?php echo count($npc_list); ?>)</h2>
                    
                    <table class="npc-table">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Character Name</th>
                                <th style="width: 12%;">Clan</th>
                                <th style="width: 15%;">Linked To</th>
                                <th style="width: 18%;">Introduced In</th>
                                <th style="width: 12%;">Submitted By</th>
                                <th style="width: 15%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($npc_list as $npc): ?>
                                <tr>
                                    <td>
                                        <span class="npc-name" onclick="toggleDetails(<?php echo $npc['id']; ?>)">
                                            <?php echo htmlspecialchars($npc['character_name']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($npc['clan'] ?? 'Unknown'); ?></td>
                                    <td><?php echo htmlspecialchars($npc['linked_to']); ?></td>
                                    <td>
                                        <?php if ($npc['introduced_in']): ?>
                                            <a href="../reference/Characters/<?php echo htmlspecialchars($npc['introduced_in']); ?>" 
                                               class="introduced-link" target="_blank">
                                                <?php echo htmlspecialchars($npc['introduced_in']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #888;">Not specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($npc['submitted_by_name'] ?? 'Unknown'); ?></td>
                                    <td>
                                        <div class="action-links">
                                            <a href="#" onclick="toggleDetails(<?php echo $npc['id']; ?>); return false;">View</a>
                                            <a href="npc_tracker_submit.php?edit=<?php echo $npc['id']; ?>">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="6" style="padding: 0; border: none;">
                                        <div id="details-<?php echo $npc['id']; ?>" class="npc-details">
                                            <?php if ($npc['summary']): ?>
                                                <div class="detail-section">
                                                    <h4>📝 Summary</h4>
                                                    <p><?php echo nl2br(htmlspecialchars($npc['summary'])); ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($npc['plot_hooks']): ?>
                                                <div class="detail-section">
                                                    <h4>🎭 Plot Hooks</h4>
                                                    <p><?php echo nl2br(htmlspecialchars($npc['plot_hooks'])); ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($npc['mentioned_details']): ?>
                                                <div class="detail-section">
                                                    <h4>📌 Mentioned Details</h4>
                                                    <p><?php echo nl2br(htmlspecialchars($npc['mentioned_details'])); ?></p>
                                                </div>
                                            <?php endif; ?>

                                            <div class="detail-section">
                                                <h4>ℹ️ Metadata</h4>
                                                <p>
                                                    Created: <?php echo date('M j, Y g:i A', strtotime($npc['created_at'])); ?><br>
                                                    Last Updated: <?php echo date('M j, Y g:i A', strtotime($npc['updated_at'])); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if (count($npcs) === 0): ?>
            <div class="empty-state">
                <p>No NPCs tracked yet. Add your first NPC to get started!</p>
                <a href="npc_tracker_submit.php" class="add-button">+ Add New NPC</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleDetails(id) {
            const details = document.getElementById('details-' + id);
            details.classList.toggle('active');
        }
    </script>
</body>
</html>

