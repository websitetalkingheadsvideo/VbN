<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/connect.php';

function column_exists(mysqli $conn, string $dbName, string $table, string $column): bool {
    $sql = "SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'sss', $dbName, $table, $column);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $exists = false;
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $exists = isset($row['cnt']) && (int)$row['cnt'] > 0;
        mysqli_free_result($result);
    }
    mysqli_stmt_close($stmt);
    return $exists;
}

function index_exists(mysqli $conn, string $dbName, string $table, string $index): bool {
    $sql = "SELECT COUNT(*) AS cnt FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return false;
    }
    mysqli_stmt_bind_param($stmt, 'sss', $dbName, $table, $index);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $exists = false;
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $exists = isset($row['cnt']) && (int)$row['cnt'] > 0;
        mysqli_free_result($result);
    }
    mysqli_stmt_close($stmt);
    return $exists;
}

function run_update(mysqli $conn, string $sql, string $description): void {
    echo "<li>{$description}: ";
    if (mysqli_query($conn, $sql)) {
        echo "<span style='color: #0a0;'>✅ Success</span>";
    } else {
        echo "<span style='color: #a00;'>⚠️ " . htmlspecialchars(mysqli_error($conn)) . "</span>";
    }
    echo "</li>";
}

$dbName = $dbname ?? mysqli_fetch_assoc(mysqli_query($conn, 'SELECT DATABASE() AS db'))['db'];

echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Character Status Field Update</title>";
echo "<style>body{font-family: 'Source Serif Pro', serif;background:#10070f;color:#f5e6d3;padding:20px;} h1{color:#f5e6d3;} ul{line-height:1.8;} .section{margin-bottom:30px;} a{color:#f5e6d3;}</style></head><body>";
echo "<h1>Character Table Status Field Update</h1>";
echo "<p>Database: <strong>" . htmlspecialchars($dbName) . "</strong></p>";

echo "<div class='section'><h2>Column Checks</h2><ul>";
$statusExists = column_exists($conn, $dbName, 'characters', 'status');
$camarillaExists = column_exists($conn, $dbName, 'characters', 'camarilla_status');
echo "<li>Status column present: " . ($statusExists ? "<span style='color:#0a0;'>Yes</span>" : "<span style='color:#a00;'>No</span>") . "</li>";
echo "<li>Camarilla status column present: " . ($camarillaExists ? "<span style='color:#0a0;'>Yes</span>" : "<span style='color:#a00;'>No</span>") . "</li>";
echo "</ul></div>";

echo "<div class='section'><h2>Applying Updates</h2><ul>";
if (!$statusExists) {
    run_update($conn, "ALTER TABLE characters ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'active' AFTER pc", "Add status column");
} else {
    echo "<li>Status column already exists – <span style='color:#0a0;'>skipped</span></li>";
}

if (!$camarillaExists) {
    $position = $statusExists ? " AFTER status" : " AFTER pc";
    run_update($conn, "ALTER TABLE characters ADD COLUMN camarilla_status VARCHAR(50) NOT NULL DEFAULT 'Unknown'{$position}", "Add camarilla_status column");
} else {
    echo "<li>Camarilla status column already exists – <span style='color:#0a0;'>skipped</span></li>";
}

$statusIndexExists = index_exists($conn, $dbName, 'characters', 'idx_characters_status');
if (!$statusIndexExists) {
    run_update($conn, "CREATE INDEX idx_characters_status ON characters(status)", "Ensure index on characters.status");
} else {
    echo "<li>Index idx_characters_status already exists – <span style='color:#0a0;'>skipped</span></li>";
}

$camarillaIndexExists = index_exists($conn, $dbName, 'characters', 'idx_characters_camarilla');
if (!$camarillaIndexExists) {
    run_update($conn, "CREATE INDEX idx_characters_camarilla ON characters(camarilla_status)", "Ensure index on characters.camarilla_status");
} else {
    echo "<li>Index idx_characters_camarilla already exists – <span style='color:#0a0;'>skipped</span></li>";
}

echo "</ul></div>";

echo "<div class='section'><h2>Backfilling Data</h2><ul>";
run_update($conn, "UPDATE characters SET status = 'active' WHERE status IS NULL OR status = ''", "Set empty status values to 'active'");
run_update($conn, "UPDATE characters SET camarilla_status = 'Unknown' WHERE camarilla_status IS NULL OR camarilla_status = ''", "Set empty camarilla_status values to 'Unknown'");
echo "</ul></div>";

echo "<div class='section'><h2>Summary</h2><p>Review messages above to confirm updates. This script is safe to re-run; existing columns will be skipped automatically.</p><p><a href='../admin/dashboard.php'>&larr; Back to Dashboard</a></p></div>";

echo "</body></html>";

mysqli_close($conn);

