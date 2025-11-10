<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$projectRoot = realpath(__DIR__ . '/../../');
if ($projectRoot === false) {
    http_response_code(500);
    echo 'Unable to resolve project root.';
    exit();
}

$archiveRoot = $projectRoot . DIRECTORY_SEPARATOR . 'archive';
$logPath = $archiveRoot . DIRECTORY_SEPARATOR . '_cleanup_log.json';
$manifestPath = $archiveRoot . DIRECTORY_SEPARATOR . 'cleanup_manifest.json';
$duplicateReportPath = $archiveRoot . DIRECTORY_SEPARATOR . 'image_duplicates.json';
$purgeLogPath = $archiveRoot . DIRECTORY_SEPARATOR . '_purge_list.txt';
$statusPath = $archiveRoot . DIRECTORY_SEPARATOR . 'archive_dashboard_state.json';

$messages = [];
$errors = [];

function sanitizeRelativePath(string $path): string
{
    $normalized = ltrim(str_replace('\\', '/', $path), '/');
    if ($normalized === '' || strpos($normalized, '..') !== false) {
        throw new RuntimeException('Invalid path provided.');
    }
    return $normalized;
}

function normalizeLogPath(string $relativePath): string
{
    $normalized = ltrim(str_replace('\\', '/', $relativePath), '/');
    if ($normalized === '') {
        return '/';
    }

    return '/' . $normalized;
}

function detectLogCategory(string $relativePath): string
{
    $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        return 'image';
    }

    if ($extension === 'json') {
        return 'json';
    }

    if (in_array($extension, ['md', 'markdown'], true)) {
        return 'md';
    }

    return 'php';
}

function appendLogEntry(array &$logEntries, string $category, string $action, string $source, string $archive, string $reason): void
{
    $logEntries[] = [
        'file' => normalizeLogPath($source),
        'archivePath' => normalizeLogPath($archive),
        'category' => $category,
        'action' => $action,
        'timestamp' => (new \DateTimeImmutable('now'))->format(\DateTimeInterface::ATOM),
        'reason' => $reason,
    ];
}

function refreshCleanupManifest(string $projectRoot): void
{
    $binary = PHP_BINARY;
    $script = $projectRoot . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'generate_cleanup_manifest.php';
    if (!is_file($script)) {
        return;
    }
    $command = escapeshellarg($binary) . ' ' . escapeshellarg($script);
    @shell_exec($command);
}

$statuses = [];
if (is_file($statusPath)) {
    $decodedStatuses = json_decode((string) file_get_contents($statusPath), true);
    if (is_array($decodedStatuses)) {
        $statuses = $decodedStatuses;
    }
}

$logEntries = [];
if (is_file($logPath)) {
    $decodedLog = json_decode((string) file_get_contents($logPath), true);
    if (is_array($decodedLog)) {
        $logEntries = $decodedLog;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $file = $_POST['file'] ?? '';
    $archive = $_POST['archivePath'] ?? '';

    $logWasUpdated = false;

    try {
        $relativeFile = sanitizeRelativePath($file);
        $relativeArchive = sanitizeRelativePath($archive);
        $sourcePath = $archiveRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeArchive);
        $destinationPath = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeFile);

        switch ($action) {
            case 'restore':
                if (!is_file($sourcePath)) {
                    throw new RuntimeException('Archived file not found.');
                }
                $destinationDir = dirname($destinationPath);
                if (!is_dir($destinationDir)) {
                    if (!mkdir($destinationDir, 0775, true) && !is_dir($destinationDir)) {
                        throw new RuntimeException('Unable to prepare destination directory.');
                    }
                }
                if (!rename($sourcePath, $destinationPath)) {
                    throw new RuntimeException('File restore failed.');
                }
                unset($statuses[$relativeFile]);
                $messages[] = sprintf('Restored %s.', $relativeFile);
                if (is_file($purgeLogPath)) {
                    file_put_contents($purgeLogPath, sprintf("[RESTORE] /%s <- /%s%s", $relativeFile, $relativeArchive, PHP_EOL), FILE_APPEND);
                }
                appendLogEntry(
                    $logEntries,
                    detectLogCategory($relativeFile),
                    'restore',
                    $relativeFile,
                    $relativeArchive,
                    'Restored from archive'
                );
                $logWasUpdated = true;
                break;
            case 'mark-safe':
                $statuses[$relativeFile] = [
                    'status' => 'safe',
                    'timestamp' => time(),
                    'archivePath' => $relativeArchive,
                ];
                $messages[] = sprintf('%s marked as safe for archive.', $relativeFile);
                appendLogEntry(
                    $logEntries,
                    detectLogCategory($relativeFile),
                    'mark-safe',
                    $relativeFile,
                    $relativeArchive,
                    'Marked safe for archive retention'
                );
                $logWasUpdated = true;
                break;
            case 'approve-deletion':
                $statuses[$relativeFile] = [
                    'status' => 'ready-delete',
                    'timestamp' => time(),
                    'archivePath' => $relativeArchive,
                ];
                $messages[] = sprintf('%s approved for deletion.', $relativeFile);
                appendLogEntry(
                    $logEntries,
                    detectLogCategory($relativeFile),
                    'approve-deletion',
                    $relativeFile,
                    $relativeArchive,
                    'Approved for deletion'
                );
                $logWasUpdated = true;
                break;
            default:
                throw new RuntimeException('Unsupported action.');
        }

        if ($logWasUpdated) {
            $encodedLog = json_encode($logEntries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            if ($encodedLog === false) {
                throw new RuntimeException('Failed to encode cleanup log.');
            }
            if (file_put_contents($logPath, $encodedLog . PHP_EOL, LOCK_EX) === false) {
                throw new RuntimeException('Failed to write cleanup log.');
            }
        }
    } catch (Throwable $exception) {
        $errors[] = $exception->getMessage();
    }

    file_put_contents(
        $statusPath,
        json_encode($statuses, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    );

    refreshCleanupManifest($projectRoot);

    // Redirect to avoid form resubmission
    header('Location: archive_dashboard.php');
    exit();
}

$manifestSummary = [
    'php' => ['active' => 0, 'inactive' => 0, 'archived' => 0],
    'json' => ['referenced' => 0, 'unreferenced' => 0],
    'md' => ['referenced' => 0, 'unreferenced' => 0],
    'images' => ['total' => 0, 'duplicateGroups' => 0],
];
if (is_file($manifestPath)) {
    $manifestData = json_decode((string) file_get_contents($manifestPath), true);
    if (is_array($manifestData) && isset($manifestData['summary'])) {
        $summary = $manifestData['summary'];
        $manifestSummary['php'] = $summary['php'] ?? $manifestSummary['php'];
        $manifestSummary['json'] = $summary['json'] ?? $manifestSummary['json'];
        $manifestSummary['md'] = $summary['md'] ?? $manifestSummary['md'];
        $manifestSummary['images'] = $summary['images'] ?? $manifestSummary['images'];
    }
}

$duplicateReport = [];
if (is_file($duplicateReportPath)) {
    $decodedDuplicates = json_decode((string) file_get_contents($duplicateReportPath), true);
    if (is_array($decodedDuplicates)) {
        $duplicateReport = $decodedDuplicates;
    }
}

$groupedEntries = [
    'php' => [],
    'json' => [],
    'md' => [],
    'image' => [],
];
foreach ($logEntries as $entry) {
    $category = $entry['category'] ?? 'php';
    if (!isset($groupedEntries[$category])) {
        $groupedEntries[$category] = [];
    }
    $groupedEntries[$category][] = $entry;
}

function getStatusBadge(?array $statuses, string $relativePath): string
{
    if (!isset($statuses[$relativePath])) {
        return '<span class="badge bg-secondary">Pending</span>';
    }
    $status = $statuses[$relativePath]['status'];
    if ($status === 'safe') {
        return '<span class="badge bg-success">Safe</span>';
    }
    if ($status === 'ready-delete') {
        return '<span class="badge bg-danger">Ready for Deletion</span>';
    }
    return '<span class="badge bg-secondary">Pending</span>';
}

$additionalStyles = ['css/archive-dashboard.css'];
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="container py-4 archive-dashboard">
    <div class="d-flex justify-content-between flex-wrap gap-3 align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Archive Dashboard</h1>
            <p class="text-muted mb-0">Review archived assets, restore items, or mark them for final purge.</p>
        </div>
        <a class="btn btn-outline-secondary" href="../admin_panel.php">&larr; Back to Admin Panel</a>
    </div>

    <?php if ($messages): ?>
        <div class="alert alert-success" role="alert">
            <ul class="mb-0">
                <?php foreach ($messages as $message): ?>
                    <li><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">PHP</h2>
                    <p class="display-6 mb-0"><?php echo (int) ($manifestSummary['php']['archived'] ?? 0); ?></p>
                    <small class="text-muted">Archived • Remaining inactive: <?php echo (int) ($manifestSummary['php']['inactive'] ?? 0); ?></small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">JSON</h2>
                    <p class="display-6 mb-0"><?php echo (int) ($manifestSummary['json']['unreferenced'] ?? 0); ?></p>
                    <small class="text-muted">Unreferenced files</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Markdown</h2>
                    <p class="display-6 mb-0"><?php echo (int) ($manifestSummary['md']['unreferenced'] ?? 0); ?></p>
                    <small class="text-muted">Unreferenced docs</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Duplicate Groups</h2>
                    <p class="display-6 mb-0"><?php echo (int) ($manifestSummary['images']['duplicateGroups'] ?? 0); ?></p>
                    <small class="text-muted">Images isolated: <?php echo count($duplicateReport); ?></small>
                </div>
            </div>
        </div>
    </div>

    <?php
    $sections = [
        'php' => 'PHP Files',
        'json' => 'JSON Files',
        'md' => 'Markdown Files',
        'image' => 'Duplicate Images',
    ];
    ?>
    <ul class="nav nav-pills mb-3" id="archiveTabs" role="tablist">
        <?php $tabIndex = 0; foreach ($sections as $key => $label): ?>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link <?php echo $tabIndex === 0 ? 'active' : ''; ?>"
                    id="<?php echo $key; ?>-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#<?php echo $key; ?>"
                    type="button"
                    role="tab"
                >
                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                </button>
            </li>
        <?php $tabIndex++; endforeach; ?>
    </ul>
    <div class="tab-content" id="archiveTabContent">
        <?php
        $tabIndex = 0;
        foreach ($sections as $key => $label):
            $entries = $groupedEntries[$key] ?? [];
            $activeClass = $tabIndex === 0 ? 'show active' : '';
            $tabIndex++;
            ?>
            <div class="tab-pane fade <?php echo $activeClass; ?>" id="<?php echo $key; ?>" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h6 mb-0"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></h2>
                        <span class="text-muted small">Total records: <?php echo count($entries); ?></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Source</th>
                                    <th scope="col">Archive Location</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Reason</th>
                                    <th scope="col" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!$entries): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No records available.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($entries as $entry):
                                    $source = $entry['file'] ?? '';
                                    $archive = $entry['archivePath'] ?? '';
                                    $reason = $entry['reason'] ?? 'archived';
                                    $statusBadge = getStatusBadge($statuses, ltrim($source, '/'));
                                    $safe = ($statuses[ltrim($source, '/')] ?? [])['status'] ?? null;
                                    $archiveExists = is_file($archiveRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($archive, '/')));
                                    ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($source, ENT_QUOTES, 'UTF-8'); ?></code></td>
                                        <td><code><?php echo htmlspecialchars($archive, ENT_QUOTES, 'UTF-8'); ?></code></td>
                                        <td><?php echo $statusBadge; ?></td>
                                        <td><?php echo htmlspecialchars($reason, ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="file" value="<?php echo htmlspecialchars($source, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="archivePath" value="<?php echo htmlspecialchars($archive, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button type="submit" name="action" value="restore" class="btn btn-outline-primary" <?php echo $archiveExists ? '' : 'disabled'; ?>>Restore</button>
                                                </form>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="file" value="<?php echo htmlspecialchars($source, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="archivePath" value="<?php echo htmlspecialchars($archive, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button type="submit" name="action" value="mark-safe" class="btn btn-outline-success" <?php echo $safe === 'safe' ? 'disabled' : ''; ?>>Mark Safe</button>
                                                </form>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="file" value="<?php echo htmlspecialchars($source, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="archivePath" value="<?php echo htmlspecialchars($archive, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button type="submit" name="action" value="approve-deletion" class="btn btn-outline-danger" <?php echo $safe === 'ready-delete' ? 'disabled' : ''; ?>>Approve Deletion</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-header">
            <h2 class="h6 mb-0">Duplicate Image Report</h2>
        </div>
        <div class="card-body">
            <?php if (!$duplicateReport): ?>
                <p class="text-muted mb-0">No duplicate image data available.</p>
            <?php else: ?>
                <div class="accordion" id="duplicateAccordion">
                    <?php foreach ($duplicateReport as $index => $group):
                        $hash = htmlspecialchars((string) ($group['hash'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $retained = htmlspecialchars((string) ($group['retained']['relativePath'] ?? ''), ENT_QUOTES, 'UTF-8');
                        $files = $group['files'] ?? [];
                        ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-<?php echo $index; ?>">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $index; ?>">
                                    Hash: <?php echo $hash; ?> — Retained: <?php echo $retained; ?>
                                </button>
                            </h2>
                            <div id="collapse-<?php echo $index; ?>" class="accordion-collapse collapse" data-bs-parent="#duplicateAccordion">
                                <div class="accordion-body">
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($files as $fileInfo):
                                            $path = htmlspecialchars((string) ($fileInfo['relativePath'] ?? ''), ENT_QUOTES, 'UTF-8');
                                            $status = htmlspecialchars((string) ($fileInfo['status'] ?? ''), ENT_QUOTES, 'UTF-8');
                                            ?>
                                            <li><code><?php echo $path; ?></code> <span class="badge bg-secondary"><?php echo $status; ?></span></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php';
?>
