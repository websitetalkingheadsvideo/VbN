<?php
declare(strict_types=1);

use RuntimeException;

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../includes/connect.php';
$agentRoots = [
    __DIR__ . '/../agents/character_agent',
    __DIR__ . '/../Agents/character_agent',
];

$agentRoot = null;

foreach ($agentRoots as $candidate) {
    if (is_dir($candidate)) {
        $agentRoot = $candidate;
        break;
    }
}

if ($agentRoot === null) {
    throw new RuntimeException('Character Agent directory not found. Expected /agents/character_agent/ (lowercase) on production or /Agents/character_agent/ locally.');
}

require_once $agentRoot . '/pipeline.php';

$config = require $agentRoot . '/config/pipeline.php';
$errors = [];
$summary = null;
$mode = 'daily';
$limitValue = '';
$dryRun = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? 'daily';
    $limitValue = trim((string) ($_POST['limit'] ?? ''));
    $dryRun = isset($_POST['dry_run']);

    if (!in_array($mode, ['daily', 'weekly', 'history'], true)) {
        $errors[] = 'Invalid run mode selected.';
    }

    $limit = null;

    if ($limitValue !== '') {
        if (!ctype_digit($limitValue) || (int) $limitValue <= 0) {
            $errors[] = 'Limit must be a positive integer.';
        } else {
            $limit = (int) $limitValue;
        }
    }

    if ($errors === []) {
        $args = [
            'mode' => $mode,
            'limit' => $limit,
            'dry_run' => $dryRun,
        ];

        try {
            $runtime = character_agent_build_runtime($config, $args);
            $connection = character_agent_require_connection($conn ?? null);
            $summary = character_agent_run($runtime, $connection);
        } catch (Throwable $throwable) {
            $errors[] = $throwable->getMessage();
        } finally {
            if (isset($connection) && $connection instanceof mysqli) {
                mysqli_close($connection);
            }
            $conn = null;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Character Agent Runner</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/css/global.css">
    <link rel="stylesheet" href="/css/admin-agents.css">
</head>
<body class="bg-dark text-light">
<main class="container py-4">
    <section class="mb-4">
        <h1 class="h3 text-light">Character Agent Runner</h1>
        <p class="text-muted">Execute the Valley by Night character pipeline without shell access.</p>
    </section>

    <?php if ($errors !== []): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($summary !== null): ?>
        <div class="alert alert-success">
            <p class="mb-1"><?php echo htmlspecialchars($summary['message']); ?></p>
            <ul class="mb-0">
                <li>Timestamp: <?php echo htmlspecialchars($summary['timestamp']); ?></li>
                <li>Mode: <?php echo htmlspecialchars($summary['mode']); ?></li>
                <li>Dry Run: <?php echo $summary['dry_run'] ? 'Yes' : 'No'; ?></li>
                <li>Processed: <?php echo htmlspecialchars((string) $summary['processed']); ?></li>
                <li>Briefs Written: <?php echo htmlspecialchars((string) $summary['briefs_written']); ?></li>
                <li>Missing Reports: <?php echo htmlspecialchars((string) $summary['missing_reports']); ?></li>
                <?php if (!empty($summary['run_id'])): ?>
                    <li>Run ID: <?php echo htmlspecialchars($summary['run_id']); ?></li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="card bg-secondary border-0 p-4">
        <form method="post" class="row g-3">
            <div class="col-12 col-md-4">
                <label for="mode" class="form-label text-light">Mode</label>
                <select id="mode" name="mode" class="form-select">
                    <option value="daily" <?php echo $mode === 'daily' ? 'selected' : ''; ?>>Daily</option>
                    <option value="weekly" <?php echo $mode === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                    <option value="history" <?php echo $mode === 'history' ? 'selected' : ''; ?>>History Compilation</option>
                </select>
            </div>

            <div class="col-12 col-md-4">
                <label for="limit" class="form-label text-light">Character Limit (optional)</label>
                <input
                    type="number"
                    min="1"
                    id="limit"
                    name="limit"
                    class="form-control"
                    value="<?php echo htmlspecialchars($limitValue); ?>"
                >
            </div>

            <div class="col-12 col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="dry_run"
                        name="dry_run"
                        <?php echo $dryRun ? 'checked' : ''; ?>
                    >
                    <label class="form-check-label text-light" for="dry_run">
                        Dry run (no files or logs written)
                    </label>
                </div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-outline-danger">Run Character Agent</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>

