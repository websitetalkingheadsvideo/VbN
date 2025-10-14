<?php
/**
 * NPC Tracker - Submit/Edit Page
 * Form for adding or editing NPC tracker entries
 */

session_start();
require_once '../includes/connect.php';

// Require admin access
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$edit_mode = false;
$npc_data = null;
$success = '';
$error = '';

// Check if editing existing NPC
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $stmt = $pdo->prepare("SELECT * FROM npc_tracker WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $npc_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$npc_data) {
        $error = "NPC not found.";
        $edit_mode = false;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $character_name = trim($_POST['character_name']);
    $clan = trim($_POST['clan']);
    $linked_to = trim($_POST['linked_to']);
    $introduced_in = trim($_POST['introduced_in']);
    $status = $_POST['status'];
    $summary = trim($_POST['summary']);
    $plot_hooks = trim($_POST['plot_hooks']);
    $mentioned_details = trim($_POST['mentioned_details']);
    
    // Validation
    if (empty($character_name) || empty($linked_to)) {
        $error = "Character Name and Linked To are required fields.";
    } else {
        try {
            if (isset($_POST['update']) && isset($_POST['npc_id'])) {
                // Update existing
                $stmt = $pdo->prepare("UPDATE npc_tracker SET 
                    character_name = ?, clan = ?, linked_to = ?, introduced_in = ?, 
                    status = ?, summary = ?, plot_hooks = ?, mentioned_details = ?
                    WHERE id = ?");
                
                $stmt->execute([
                    $character_name, $clan, $linked_to, $introduced_in,
                    $status, $summary, $plot_hooks, $mentioned_details,
                    $_POST['npc_id']
                ]);
                
                $success = "NPC updated successfully!";
                
                // Refresh data
                $stmt = $pdo->prepare("SELECT * FROM npc_tracker WHERE id = ?");
                $stmt->execute([$_POST['npc_id']]);
                $npc_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } else {
                // Insert new
                $stmt = $pdo->prepare("INSERT INTO npc_tracker 
                    (character_name, clan, linked_to, introduced_in, status, summary, plot_hooks, mentioned_details, submitted_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $character_name, $clan, $linked_to, $introduced_in,
                    $status, $summary, $plot_hooks, $mentioned_details,
                    $_SESSION['user_id']
                ]);
                
                $success = "NPC added to tracker successfully!";
                
                // Clear form after successful insert
                $_POST = [];
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

define('LOTN_VERSION', '0.2.0');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edit' : 'Add'; ?> NPC - Valley by Night</title>
    <link rel="stylesheet" href="../css/global.css">
    <link rel="stylesheet" href="../css/header.css">
    <style>
        .form-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background: rgba(26, 15, 15, 0.8);
            border: 1px solid var(--gold);
            border-radius: 8px;
        }

        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--gold);
        }

        .form-header h1 {
            color: var(--gold);
            font-family: 'Cinzel', serif;
            margin: 0;
        }

        .back-button {
            background: rgba(139, 115, 85, 0.3);
            color: var(--gold);
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            border: 1px solid var(--gold);
            transition: all 0.3s;
        }

        .back-button:hover {
            background: var(--gold);
            color: var(--blood-red);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .alert-success {
            background: rgba(0, 128, 0, 0.2);
            border: 1px solid #28a745;
            color: #90ee90;
        }

        .alert-error {
            background: rgba(139, 0, 0, 0.2);
            border: 1px solid var(--blood-red);
            color: #ff6b6b;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: var(--gold);
            font-weight: bold;
            margin-bottom: 8px;
            font-family: 'Cinzel', serif;
        }

        .form-group .hint {
            font-size: 0.85em;
            color: var(--light-text);
            font-style: italic;
            margin-top: 5px;
            font-family: 'Lato', sans-serif;
        }

        .form-group input[type="text"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            background: rgba(26, 15, 15, 0.6);
            border: 1px solid rgba(139, 115, 85, 0.5);
            border-radius: 4px;
            color: var(--light-text);
            font-family: 'Lato', sans-serif;
            font-size: 1em;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-group input[type="text"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 5px rgba(212, 175, 55, 0.3);
        }

        .required::after {
            content: " *";
            color: var(--blood-red);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(139, 115, 85, 0.3);
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Cinzel', serif;
        }

        .btn-primary {
            background: var(--gold);
            color: var(--blood-red);
        }

        .btn-primary:hover {
            background: var(--light-gold);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: rgba(139, 115, 85, 0.3);
            color: var(--gold);
            border: 1px solid var(--gold);
        }

        .btn-secondary:hover {
            background: rgba(139, 115, 85, 0.5);
        }

        .guidelines {
            background: rgba(139, 115, 85, 0.1);
            padding: 20px;
            border-left: 3px solid var(--gold);
            margin-bottom: 30px;
        }

        .guidelines h3 {
            color: var(--gold);
            margin-top: 0;
            font-family: 'Cinzel', serif;
        }

        .guidelines ul {
            color: var(--light-text);
            margin: 10px 0;
        }

        .guidelines li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="form-container">
        <div class="form-header">
            <h1><?php echo $edit_mode ? '✏️ Edit NPC' : '➕ Add New NPC'; ?></h1>
            <a href="npc_tracker.php" class="back-button">← Back to Tracker</a>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="guidelines">
            <h3>📋 Guidelines</h3>
            <ul>
                <li><strong>Character Name:</strong> The NPC's full name as it appears in backstories</li>
                <li><strong>Clan:</strong> Leave blank if unknown or not yet determined</li>
                <li><strong>Linked To:</strong> Which PC or major NPC is this character connected to?</li>
                <li><strong>Introduced In:</strong> The filename where this character was first mentioned (e.g., "Cordelia Fairchild.json")</li>
                <li><strong>Summary:</strong> Brief description - who they are, their role, key relationships</li>
                <li><strong>Plot Hooks:</strong> How they could be used in story, potential conflicts/alliances</li>
                <li><strong>Mentioned Details:</strong> ONLY include details explicitly stated in source material (generation, disciplines, personality traits, etc.)</li>
            </ul>
        </div>

        <form method="POST">
            <?php if ($edit_mode): ?>
                <input type="hidden" name="npc_id" value="<?php echo $npc_data['id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="required">Character Name</label>
                <input type="text" name="character_name" 
                       value="<?php echo htmlspecialchars($npc_data['character_name'] ?? $_POST['character_name'] ?? ''); ?>" 
                       required>
            </div>

            <div class="form-group">
                <label>Clan</label>
                <input type="text" name="clan" 
                       value="<?php echo htmlspecialchars($npc_data['clan'] ?? $_POST['clan'] ?? ''); ?>"
                       placeholder="e.g., Gangrel, Nosferatu, Toreador, or leave blank if unknown">
            </div>

            <div class="form-group">
                <label class="required">Linked To</label>
                <input type="text" name="linked_to" 
                       value="<?php echo htmlspecialchars($npc_data['linked_to'] ?? $_POST['linked_to'] ?? ''); ?>" 
                       required
                       placeholder="e.g., Cordelia Fairchild, Player Character Name">
            </div>

            <div class="form-group">
                <label>Introduced In</label>
                <input type="text" name="introduced_in" 
                       value="<?php echo htmlspecialchars($npc_data['introduced_in'] ?? $_POST['introduced_in'] ?? ''); ?>"
                       placeholder="e.g., Cordelia Fairchild.json">
                <div class="hint">Filename where this character was first mentioned</div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <?php
                    $statuses = [
                        '💡 Concept Only' => 'Concept Only',
                        '📝 Ready for Sheet' => 'Ready for Sheet',
                        '✅ Sheet Complete' => 'Sheet Complete',
                        '❌ On Hold' => 'On Hold'
                    ];
                    $current_status = $npc_data['status'] ?? $_POST['status'] ?? '💡 Concept Only';
                    foreach ($statuses as $value => $label):
                    ?>
                        <option value="<?php echo $value; ?>" <?php echo $current_status === $value ? 'selected' : ''; ?>>
                            <?php echo $value; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Summary</label>
                <textarea name="summary" rows="5" placeholder="Brief description of who they are, their role, key relationships..."><?php echo htmlspecialchars($npc_data['summary'] ?? $_POST['summary'] ?? ''); ?></textarea>
                <div class="hint">Who they are, their position, relationships to other characters</div>
            </div>

            <div class="form-group">
                <label>Plot Hooks</label>
                <textarea name="plot_hooks" rows="5" placeholder="How they could be used in story, potential conflicts, alliances, complications..."><?php echo htmlspecialchars($npc_data['plot_hooks'] ?? $_POST['plot_hooks'] ?? ''); ?></textarea>
                <div class="hint">How this character could drive plot, create conflicts, or enable story arcs</div>
            </div>

            <div class="form-group">
                <label>Mentioned Details</label>
                <textarea name="mentioned_details" rows="5" placeholder="Only include details explicitly stated in source material: generation, disciplines, personality traits, etc."><?php echo htmlspecialchars($npc_data['mentioned_details'] ?? $_POST['mentioned_details'] ?? ''); ?></textarea>
                <div class="hint">ONLY details from source material - don't invent or assume anything</div>
            </div>

            <div class="form-actions">
                <?php if ($edit_mode): ?>
                    <button type="submit" name="update" class="btn btn-primary">💾 Update NPC</button>
                <?php else: ?>
                    <button type="submit" name="add_npc" class="btn btn-primary">➕ Add NPC</button>
                <?php endif; ?>
                <a href="npc_tracker.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>

