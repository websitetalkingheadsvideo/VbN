<?php
declare(strict_types=1);

use mysqli;
use mysqli_stmt;
use RuntimeException;

/**
 * Validate the provided database handle and ensure it is ready for queries.
 *
 * @param mixed $connection
 * @return mysqli
 */
function character_agent_require_connection($connection): mysqli
{
    if (!$connection instanceof mysqli) {
        throw new RuntimeException('Character Agent pipeline requires a mysqli connection from includes/connect.php.');
    }

    if (!mysqli_ping($connection)) {
        throw new RuntimeException('Character Agent pipeline cannot use a closed MySQL connection.');
    }

    return $connection;
}

/**
 * Fetch a batch of characters for the specified chronicle.
 *
 * @param mysqli $connection
 * @param string $chronicle
 * @param int $limit
 * @param int $offset
 * @return array<int, array<string, mixed>>
 */
function character_agent_fetch_batch(mysqli $connection, string $chronicle, int $limit, int $offset): array
{
    if ($limit <= 0) {
        throw new RuntimeException('Batch size must be positive.');
    }

    if ($offset < 0) {
        throw new RuntimeException('Batch offset cannot be negative.');
    }

    $sql = <<<'SQL'
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
        WHERE chronicle = ?
        ORDER BY updated_at DESC, id ASC
        LIMIT ? OFFSET ?
    SQL;

    $statement = prepare_character_agent_statement($connection, $sql);

    if (!mysqli_stmt_bind_param($statement, 'sii', $chronicle, $limit, $offset)) {
        $error = mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        throw new RuntimeException('Failed binding parameters for character batch query: ' . $error);
    }

    if (!mysqli_stmt_execute($statement)) {
        $error = mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        throw new RuntimeException('Failed executing character batch query: ' . $error);
    }

    $result = mysqli_stmt_get_result($statement);

    if ($result === false) {
        $error = mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        throw new RuntimeException('Failed retrieving character batch result set: ' . $error);
    }

    /** @var array<int, array<string, mixed>> $rows */
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC) ?: [];

    mysqli_free_result($result);
    mysqli_stmt_close($statement);

    return array_map('normalize_character_agent_record', $rows);
}

/**
 * Stream all characters for a chronicle, yielding batches to a handler.
 *
 * @param mysqli $connection
 * @param string $chronicle
 * @param int $batchSize
 * @param callable $handler
 * @return void
 */
function character_agent_stream_characters(
    mysqli $connection,
    string $chronicle,
    int $batchSize,
    callable $handler
): void {
    $offset = 0;

    while (true) {
        $batch = character_agent_fetch_batch($connection, $chronicle, $batchSize, $offset);

        if ($batch === []) {
            break;
        }

        $handler($batch);
        $offset += count($batch);
    }
}

/**
 * Prepare a mysqli statement and handle errors consistently.
 *
 * @param mysqli $connection
 * @param string $sql
 * @return mysqli_stmt
 */
function prepare_character_agent_statement(mysqli $connection, string $sql): mysqli_stmt
{
    $statement = mysqli_prepare($connection, $sql);

    if ($statement === false) {
        throw new RuntimeException('Failed preparing character agent query: ' . mysqli_error($connection));
    }

    return $statement;
}

/**
 * Normalize DB row into a typed associative array for downstream processing.
 *
 * @param array<string, mixed> $row
 * @return array<string, mixed>
 */
function normalize_character_agent_record(array $row): array
{
    return [
        'id' => isset($row['id']) ? (int) $row['id'] : 0,
        'name' => normalize_character_agent_string($row['character_name'] ?? null),
        'player' => normalize_character_agent_string($row['player_name'] ?? null),
        'chronicle' => normalize_character_agent_string($row['chronicle'] ?? null),
        'concept' => normalize_character_agent_string($row['concept'] ?? null),
        'clan' => normalize_character_agent_string($row['clan'] ?? null),
        'generation' => normalize_character_agent_optional_int($row['generation'] ?? null),
        'sire' => normalize_character_agent_string($row['sire'] ?? null),
        'pc' => normalize_character_agent_string($row['pc'] ?? null),
        'status' => normalize_character_agent_string($row['status'] ?? null),
        'camarilla_status' => normalize_character_agent_string($row['camarilla_status'] ?? null),
        'biography' => normalize_character_agent_text($row['biography'] ?? null),
        'appearance' => normalize_character_agent_text($row['appearance'] ?? null),
        'notes' => normalize_character_agent_text($row['notes'] ?? null),
        'agent_notes' => normalize_character_agent_text($row['agentNotes'] ?? null),
        'acting_notes' => normalize_character_agent_text($row['actingNotes'] ?? null),
        'image' => normalize_character_agent_string($row['character_image'] ?? null),
        'coterie' => normalize_character_agent_string($row['Coterie'] ?? null),
        'relationships' => normalize_character_agent_text($row['Relationships'] ?? null),
        'created_at' => normalize_character_agent_string($row['created_at'] ?? null),
        'updated_at' => normalize_character_agent_string($row['updated_at'] ?? null),
    ];
}

/**
 * @param mixed $value
 * @return int|null
 */
function normalize_character_agent_optional_int($value): ?int
{
    if ($value === null) {
        return null;
    }

    if ($value === '') {
        return null;
    }

    return (int) $value;
}

/**
 * @param mixed $value
 * @return string|null
 */
function normalize_character_agent_string($value): ?string
{
    if (!is_string($value)) {
        return null;
    }

    $trimmed = trim($value);

    return $trimmed === '' ? null : $trimmed;
}

/**
 * @param mixed $value
 * @return string|null
 */
function normalize_character_agent_text($value): ?string
{
    if ($value === null) {
        return null;
    }

    if (!is_string($value)) {
        throw new RuntimeException('Character Agent expected text column to be string.');
    }

    return trim($value);
}

