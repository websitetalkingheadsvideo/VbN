<?php
declare(strict_types=1);

use RuntimeException;

header('Content-Type: text/plain; charset=utf-8');

require_once dirname(__DIR__) . '/../includes/connect.php';

/**
 * @param mixed $connection
 * @return mysqli
 */
function getDatabaseConnection($connection): mysqli
{
    if (!$connection instanceof mysqli) {
        throw new RuntimeException('Database connection is not available from includes/connect.php.');
    }

    return $connection;
}

/**
 * @param mysqli $connection
 * @return array<int, array<string, mixed>>
 */
function fetchValleyByNightCharacters(mysqli $connection): array
{
    $query = <<<SQL
        SELECT
            id,
            character_name,
            player_name,
            chronicle,
            concept,
            clan,
            generation,
            sire,
            pc,
            status,
            camarilla_status,
            biography,
            appearance,
            notes,
            agentNotes,
            actingNotes,
            character_image,
            Coterie,
            Relationships,
            created_at,
            updated_at
        FROM characters
        WHERE chronicle = 'Valley by Night'
        ORDER BY character_name ASC
    SQL;

    $result = mysqli_query($connection, $query);

    if ($result === false) {
        throw new RuntimeException('Failed to execute character query: ' . mysqli_error($connection));
    }

    /** @var array<int, array<string, mixed>> $characters */
    $characters = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);

    return $characters;
}

/**
 * @param array<string, mixed> $character
 * @return array<int, string>
 */
function buildReportFlags(array $character): array
{
    $flags = [];

    if (shouldGenerateCharacterBrief($character['player_name'] ?? null)) {
        $flags[] = 'character_brief';
    }

    foreach (collectMissingFieldReasons($character) as $reason) {
        $flags[] = 'missing_fields (' . $reason . ')';
    }

    return $flags;
}

/**
 * @param array<string, mixed> $character
 * @return array<int, string>
 */
function collectMissingFieldReasons(array $character): array
{
    $reasons = [];

    if (isFieldEmpty($character['biography'] ?? null)) {
        $reasons[] = 'biography empty';
    }

    if (isFieldEmpty($character['appearance'] ?? null)) {
        $reasons[] = 'appearance empty';
    }

    if (isFieldEmpty($character['notes'] ?? null)) {
        $reasons[] = 'notes empty';
    }

    if (isFieldEmpty($character['player_name'] ?? null)) {
        $reasons[] = 'player_name empty';
    }

    return $reasons;
}

/**
 * @param array<string, mixed> $character
 * @return array<int, string>
 */
function buildOutputLines(array $character): array
{
    $heading = sprintf(
        'Character: %s (id %d)',
        formatCharacterName($character['character_name'] ?? ''),
        (int) ($character['id'] ?? 0)
    );

    $lines = [$heading];

    foreach (buildReportFlags($character) as $flag) {
        $lines[] = '-> would generate: ' . $flag;
    }

    if (!shouldGenerateCharacterBrief($character['player_name'] ?? null)) {
        $lines[] = '-> note: character_brief skipped (player_name is NPC placeholder)';
    }

    $lines[] = '-> would log: processed';

    return $lines;
}

function formatCharacterName(string $name): string
{
    $trimmed = trim($name);
    return $trimmed === '' ? '(no name provided)' : $trimmed;
}

function isFieldEmpty(?string $value): bool
{
    return trim((string) ($value ?? '')) === '';
}

function shouldGenerateCharacterBrief(?string $playerName): bool
{
    if ($playerName === null) {
        return false;
    }

    $normalized = strtolower(trim($playerName));

    if ($normalized === '') {
        return false;
    }

    return $normalized !== 'npc';
}

try {
    $connection = getDatabaseConnection($conn ?? null);
    $characters = fetchValleyByNightCharacters($connection);
    $processedCount = 0;

    foreach ($characters as $character) {
        foreach (buildOutputLines($character) as $line) {
            echo $line . PHP_EOL;
        }

        echo PHP_EOL;
        $processedCount++;
    }

    echo 'Total characters processed: ' . $processedCount . PHP_EOL;
} catch (Throwable $error) {
    http_response_code(500);
    echo 'Character agent dry run failed: ' . $error->getMessage() . PHP_EOL;
} finally {
    if (isset($connection) && $connection instanceof mysqli) {
        mysqli_close($connection);
    }
}

