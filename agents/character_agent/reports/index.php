<?php
declare(strict_types=1);

session_start();

use RuntimeException;

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /admin/login.php');
    exit();
}

/**
 * @return array<int, string>
 */
function character_agent_report_roots(): array
{
    static $roots = null;

    if ($roots !== null) {
        return $roots;
    }

    $candidates = array_filter([
        realpath(__DIR__),
        realpath(dirname(__DIR__, 2) . '/Agents/character_agent/reports'),
    ]);

    $roots = array_values(array_unique($candidates));

    if ($roots === []) {
        throw new RuntimeException('Unable to resolve character agent report directories.');
    }

    return $roots;
}

/**
 * @param array<int, string> $roots
 * @return array<int, array<string, string>>
 */
function character_agent_list_reports(array $roots, string $subDirectory): array
{
    $entries = [];

    foreach ($roots as $root) {
        $path = $root . DIRECTORY_SEPARATOR . $subDirectory;

        if (!is_dir($path)) {
            continue;
        }

        $files = array_filter(scandir($path) ?: [], static function (string $item): bool {
            return $item !== '.' && $item !== '..';
        });

        foreach ($files as $file) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $file;

            if (!is_file($fullPath)) {
                continue;
            }

            $entries[$fullPath] = [
                'name' => $file,
                'path' => $fullPath,
            ];
        }
    }

    $entries = array_values($entries);

    usort($entries, static function (array $a, array $b): int {
        return $a['name'] < $b['name'] ? 1 : -1;
    });

    return $entries;
}

/**
 * @param string $filePath
 * @return string
 */
function character_agent_read_file(string $filePath): string
{
    $contents = file_get_contents($filePath);

    if ($contents === false) {
        return '';
    }

    if (substr($filePath, -5) === '.json') {
        $decoded = json_decode($contents, true);

        if (is_array($decoded)) {
            return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }

    return $contents;
}

/**
 * @param array<int, array<string, string>> $entries
 * @param string $sectionTitle
 * @return void
 */
function character_agent_render_section(array $entries, string $sectionTitle): void
{
    if ($entries === []) {
        echo '<div class="card bg-secondary text-light border-0 shadow-sm"><div class="card-body"><p class="mb-0">No ' . htmlspecialchars($sectionTitle) . ' reports available yet.</p></div></div>';
        return;
    }

    echo '<div class="accordion" id="accordion-' . htmlspecialchars($sectionTitle) . '">';

    foreach ($entries as $index => $entry) {
        $collapseId = $sectionTitle . '-item-' . $index;
        $headingId = $collapseId . '-heading';
        $content = htmlspecialchars(character_agent_read_file($entry['path']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        echo '
        <div class="accordion-item bg-secondary border-0 text-light mb-2 shadow-sm">
            <h2 class="accordion-header" id="' . htmlspecialchars($headingId) . '">
                <button class="accordion-button collapsed bg-dark text-light" type="button" data-bs-toggle="collapse" data-bs-target="#' . htmlspecialchars($collapseId) . '" aria-expanded="false" aria-controls="' . htmlspecialchars($collapseId) . '">
                    ' . htmlspecialchars($entry['name']) . '
                </button>
            </h2>
            <div id="' . htmlspecialchars($collapseId) . '" class="accordion-collapse collapse" aria-labelledby="' . htmlspecialchars($headingId) . '" data-bs-parent="#accordion-' . htmlspecialchars($sectionTitle) . '">
                <div class="accordion-body bg-dark text-light">
                    <pre class="report-content mb-0">' . $content . '</pre>
                </div>
            </div>
        </div>';
    }

    echo '</div>';
}

$roots = character_agent_report_roots();

$dailyReports = character_agent_list_reports($roots, 'daily');
$weeklyReports = character_agent_list_reports($roots, 'weekly');
$continuityReports = character_agent_list_reports($roots, 'continuity');
$historyReports = character_agent_list_reports($roots, 'history');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Character Agent Reports</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/css/global.css">
    <link rel="stylesheet" href="/css/admin-agents.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/character-agent-reports.css">
</head>
<body class="character-agent-reports">
<main class="container py-4 character-agent-reports">
    <header class="mb-4">
        <h1 class="h3 text-light">Character Agent Reports</h1>
        <p class="page-lead text-light">Browse generated pipeline outputs for Valley by Night.</p>
        <a href="/admin/agents.php" class="btn btn-outline-danger btn-sm">← Back to Agents Dashboard</a>
    </header>

    <section class="mb-5">
        <h2 class="h5 text-light">Daily Reports</h2>
        <?php character_agent_render_section($dailyReports, 'daily'); ?>
    </section>

    <section class="mb-5">
        <h2 class="h5 text-light">Weekly Reports</h2>
        <?php character_agent_render_section($weeklyReports, 'weekly'); ?>
    </section>

    <section class="mb-5">
        <h2 class="h5 text-light">Continuity Reports</h2>
        <?php character_agent_render_section($continuityReports, 'continuity'); ?>
    </section>

    <section class="mb-5">
        <h2 class="h5 text-light">City History Compilations</h2>
        <?php character_agent_render_section($historyReports, 'history'); ?>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

