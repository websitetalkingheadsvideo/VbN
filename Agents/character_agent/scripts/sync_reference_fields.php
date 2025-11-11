<?php
declare(strict_types=1);

use RuntimeException;

if (ini_get('display_errors') !== '1') {
    ini_set('display_errors', '1');
}

error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

$logDirectory = dirname(__DIR__) . '/logs';
$logWritable = is_dir($logDirectory) && is_writable($logDirectory);
$logFile = $logWritable ? $logDirectory . '/sync_reference_fields.log' : null;

/**
 * @param string $message
 */
function logSyncMessage(string $message): void
{
    global $logFile, $logWritable;

    if (!$logWritable || $logFile === null) {
        return;
    }

    $timestamp = date('Y-m-d H:i:s');
    $line = '[' . $timestamp . '] ' . $message . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND);
}

echo 'Sync reference script booting…' . PHP_EOL;
logSyncMessage('--- sync_reference_fields.php invoked ---');

$connectPath = realpath(__DIR__ . '/../../../includes/connect.php');

if ($connectPath === false) {
    http_response_code(500);
    echo 'Sync failed: includes/connect.php not found relative to script.' . PHP_EOL;
    logSyncMessage('connect.php not found using path resolution.');
    exit;
}
logSyncMessage('Resolved connect.php path: ' . $connectPath);

$referenceDirectory = realpath(__DIR__ . '/../../../reference/Characters/Added to Database');

if ($referenceDirectory === false || !is_dir($referenceDirectory)) {
    http_response_code(500);
    echo 'Sync failed: reference directory not found relative to script.' . PHP_EOL;
    logSyncMessage('Reference directory missing.');
    exit;
}

logSyncMessage('Resolved reference directory: ' . $referenceDirectory);

try {
    require_once $connectPath;
} catch (Throwable $error) {
    http_response_code(500);
    echo 'Sync failed: unable to require includes/connect.php (' . $error->getMessage() . ')' . PHP_EOL;
    logSyncMessage('require_once failed: ' . $error->getMessage());
    exit;
}
define('REFERENCE_DIRECTORY', $referenceDirectory);
const TARGET_CHRONICLE = 'Valley by Night';

/**
 * Normalize names for tolerant matching.
 */
function normalizeCharacterName(string $value): string
{
    $trimmed = strtolower(trim($value));

    if ($trimmed === '') {
        return '';
    }

    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $trimmed);

    if ($ascii === false || $ascii === null) {
        $ascii = $trimmed;
    }

    return preg_replace('/[^a-z0-9]/', '', $ascii) ?? '';
}

/**
 * @param mixed $connection
 * @return mysqli
 */
function getConnection($connection): mysqli
{
    if (!$connection instanceof mysqli) {
        throw new RuntimeException('Database connection unavailable; includes/connect.php did not expose $conn.');
    }

    return $connection;
}

/**
 * @return list<string>
 */
function findJsonFiles(string $directory): array
{
    if (!is_dir($directory)) {
        throw new RuntimeException('Reference directory not found: ' . $directory);
    }

    $files = glob($directory . '/*.json');

    if ($files === false) {
        throw new RuntimeException('Unable to enumerate JSON files in ' . $directory);
    }

    sort($files);

    return $files;
}

/**
 * @param string $filePath
 * @return list<array{name:string, biography:?string, appearance:?string, notes:?string}>
 */
function loadCharacterPayloads(string $filePath): array
{
    $contents = file_get_contents($filePath);

    if ($contents === false) {
        throw new RuntimeException('Failed to read file: ' . $filePath);
    }

    $decoded = json_decode($contents, true);

    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException(
            'Failed to decode JSON (' . json_last_error_msg() . '): ' . $filePath
        );
    }

    $records = [];

    if (isCharacterRecord($decoded)) {
        $records[] = $decoded;
    } elseif (is_array($decoded)) {
        foreach ($decoded as $record) {
            if (!isCharacterRecord($record)) {
                continue;
            }

            $records[] = $record;
        }
    } else {
        throw new RuntimeException('Unrecognized JSON structure in ' . $filePath);
    }

    $payloads = [];

    foreach ($records as $record) {
        $name = extractCharacterName($record);

        $payloads[] = [
            'name' => $name,
            'biography' => extractOptionalString($record, ['biography', 'backstory']),
            'appearance' => extractOptionalString($record, ['appearance']),
            'notes' => extractOptionalString($record, ['notes']),
        ];
    }

    return $payloads;
}

/**
 * @param mixed $value
 */
function isCharacterRecord($value): bool
{
    if (!is_array($value)) {
        return false;
    }

    return array_key_exists('character_name', $value) || array_key_exists('name', $value);
}

/**
 * @param array<string, mixed> $data
 */
function extractCharacterName(array $data): string
{
    $candidates = [
        $data['character_name'] ?? null,
        $data['name'] ?? null,
    ];

    foreach ($candidates as $candidate) {
        $trimmed = trim((string) ($candidate ?? ''));

        if ($trimmed !== '') {
            return $trimmed;
        }
    }

    throw new RuntimeException('Character JSON missing name field.');
}

/**
 * @param array<string, mixed> $data
 * @param list<string> $keys
 */
function extractOptionalString(array $data, array $keys): ?string
{
    foreach ($keys as $key) {
        if (!array_key_exists($key, $data)) {
            continue;
        }

        $value = (string) $data[$key];
        $trimmed = trim($value);

        if ($trimmed !== '') {
            return $trimmed;
        }
    }

    return null;
}

/**
 * @param mysqli $connection
 * @param array{name:string, biography:?string, appearance:?string, notes:?string} $payload
 */
function applyReferenceUpdate(mysqli $connection, array $payload): void
{
    $current = fetchCharacterRow($connection, $payload['name']);

    if ($current === null) {
        echo 'Skipping (not found in DB): ' . $payload['name'] . PHP_EOL;
        return;
    }

    $updates = buildUpdateSet($current, $payload);

    if (empty($updates)) {
        echo 'No changes required: ' . $payload['name'] . PHP_EOL;
        return;
    }

    persistUpdates($connection, (int) $current['id'], $updates);

    echo 'Updated ' . $payload['name'] . ' [' . implode(', ', array_keys($updates)) . ']' . PHP_EOL;
}

/**
 * @param mysqli $connection
 * @return array{id:string, biography:?string, appearance:?string, notes:?string}|null
 */
function fetchCharacterRow(mysqli $connection, string $name): ?array
{
    $query = 'SELECT id, character_name, biography, appearance, notes FROM characters WHERE chronicle = ?';

    $statement = mysqli_prepare($connection, $query);

    if ($statement === false) {
        throw new RuntimeException('Failed to prepare lookup query: ' . mysqli_error($connection));
    }

    $chronicle = TARGET_CHRONICLE;

    if (!mysqli_stmt_bind_param($statement, 's', $chronicle)) {
        $error = mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        throw new RuntimeException('Failed to bind lookup parameters: ' . $error);
    }

    if (!mysqli_stmt_execute($statement)) {
        $error = mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        throw new RuntimeException('Failed to execute lookup query: ' . $error);
    }

    if (!mysqli_stmt_store_result($statement)) {
        $error = mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        throw new RuntimeException('Failed to buffer lookup result: ' . $error);
    }

    $targetNormalized = normalizeCharacterName($name);

    if (!mysqli_stmt_bind_result($statement, $id, $dbName, $biography, $appearance, $notes)) {
        $error = mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        throw new RuntimeException('Failed to bind lookup result: ' . $error);
    }

    $row = null;

    while (mysqli_stmt_fetch($statement)) {
        if (normalizeCharacterName((string) $dbName) !== $targetNormalized) {
            continue;
        }

        $row = [
            'id' => (string) $id,
            'name' => (string) $dbName,
            'biography' => $biography,
            'appearance' => $appearance,
            'notes' => $notes,
        ];

        break;
    }

    mysqli_stmt_free_result($statement);
    mysqli_stmt_close($statement);

    return $row;
}

/**
 * @param array{id:?string, biography:?string, appearance:?string, notes:?string} $current
 * @param array{name:string, biography:?string, appearance:?string, notes:?string} $payload
 * @return array<string, string>
 */
function buildUpdateSet(array $current, array $payload): array
{
    $updates = [];

    if (shouldUpdateColumn($current['biography'] ?? null, $payload['biography'])) {
        $updates['biography'] = (string) $payload['biography'];
    }

    if (shouldUpdateColumn($current['appearance'] ?? null, $payload['appearance'])) {
        $updates['appearance'] = (string) $payload['appearance'];
    }

    if (shouldUpdateColumn($current['notes'] ?? null, $payload['notes'])) {
        $updates['notes'] = (string) $payload['notes'];
    }

    return $updates;
}

function shouldUpdateColumn(?string $existing, ?string $replacement): bool
{
    if ($replacement === null) {
        return false;
    }

    $existingTrimmed = trim((string) ($existing ?? ''));

    if ($existingTrimmed !== '') {
        return false;
    }

    return trim($replacement) !== '';
}

/**
 * @param array<string, string> $updates
 */
function persistUpdates(mysqli $connection, int $characterId, array $updates): void
{
    $columns = array_keys($updates);
    $setFragments = array_map(
        static function (string $column): string {
            return $column . ' = ?';
        },
        $columns
    );

    $query = 'UPDATE characters SET ' . implode(', ', $setFragments) . ', updated_at = NOW() WHERE id = ? LIMIT 1';

    $statement = mysqli_prepare($connection, $query);

    if ($statement === false) {
        throw new RuntimeException('Failed to prepare update statement: ' . mysqli_error($connection));
    }

    $types = str_repeat('s', count($columns)) . 'i';
    $values = array_values($updates);
    $values[] = $characterId;

    $bindParams = [];

    foreach ($values as $index => $value) {
        $bindParams[$index] = &$values[$index];
    }

    array_unshift($bindParams, $statement, $types);

    if (!call_user_func_array('mysqli_stmt_bind_param', $bindParams)) {
        $error = mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        throw new RuntimeException('Failed to bind update parameters: ' . $error);
    }

    if (!mysqli_stmt_execute($statement)) {
        $error = mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        throw new RuntimeException('Failed to execute update statement: ' . $error);
    }

    mysqli_stmt_close($statement);
}

if (!isset($conn)) {
    http_response_code(500);
    echo 'Sync failed: $conn is not defined after including connect.php.' . PHP_EOL;
    logSyncMessage('$conn undefined after include.');
    exit;
}

try {
    $connection = getConnection($conn);
    logSyncMessage('Database connection established.');
} catch (Throwable $error) {
    http_response_code(500);
    echo 'Sync failed: ' . $error->getMessage() . PHP_EOL;
    logSyncMessage('getConnection failed: ' . $error->getMessage());
    exit;
}

try {
    $files = findJsonFiles(REFERENCE_DIRECTORY);
    logSyncMessage('Found ' . count($files) . ' JSON files.');
} catch (Throwable $error) {
    http_response_code(500);
    echo 'Sync failed: ' . $error->getMessage() . PHP_EOL;
    mysqli_close($connection);
    logSyncMessage('findJsonFiles failed: ' . $error->getMessage());
    exit;
}

echo 'Found ' . count($files) . ' reference files.' . PHP_EOL;

$processedNames = [];

foreach ($files as $filePath) {
    $filename = basename($filePath);

    try {
        $payloads = loadCharacterPayloads($filePath);
    } catch (Throwable $error) {
        echo 'Skipped ' . $filename . ': ' . $error->getMessage() . PHP_EOL;
        logSyncMessage('Processing skipped for ' . $filename . ': ' . $error->getMessage());
        continue;
    }

    if (empty($payloads)) {
        echo 'Skipped ' . $filename . ': no character entries.' . PHP_EOL;
        logSyncMessage('Processing skipped for ' . $filename . ': empty payload set.');
        continue;
    }

    foreach ($payloads as $payload) {
        $normalizedName = normalizeCharacterName($payload['name']);

        if ($normalizedName === '') {
            echo 'Skipped entry in ' . $filename . ': missing character_name.' . PHP_EOL;
            logSyncMessage('Entry skipped in ' . $filename . ' due to missing character_name.');
            continue;
        }

        if (isset($processedNames[$normalizedName])) {
            echo 'Duplicate entry skipped: ' . $payload['name'] . PHP_EOL;
            logSyncMessage('Duplicate entry skipped for ' . $payload['name']);
            continue;
        }

        $processedNames[$normalizedName] = true;

        try {
            applyReferenceUpdate($connection, $payload);
        } catch (Throwable $error) {
            echo 'Skipped ' . $payload['name'] . ' (from ' . $filename . '): ' . $error->getMessage() . PHP_EOL;
            logSyncMessage('Processing skipped for ' . $payload['name'] . ': ' . $error->getMessage());
        }
    }
}

mysqli_close($connection);
echo 'Sync complete.' . PHP_EOL;
logSyncMessage('Sync completed successfully.');

