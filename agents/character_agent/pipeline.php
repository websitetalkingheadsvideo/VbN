<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/connect.php';
require_once __DIR__ . '/src/character_fetcher.php';
require_once __DIR__ . '/src/character_processor.php';
require_once __DIR__ . '/src/report_writer.php';
require_once __DIR__ . '/src/logger.php';

/**
 * @param array<int, string> $arguments
 * @return array<string, mixed>
 */
function character_agent_parse_arguments(array $arguments): array
{
    $options = getopt('', ['mode:', 'limit:', 'dry-run']);

    return [
        'mode' => isset($options['mode']) ? (string) $options['mode'] : 'daily',
        'limit' => isset($options['limit']) ? (int) $options['limit'] : null,
        'dry_run' => array_key_exists('dry-run', $options),
    ];
}

/**
 * @param array<string, mixed> $config
 * @param array<string, mixed> $args
 * @return array<string, mixed>
 */
function character_agent_build_runtime(array $config, array $args): array
{
    $mode = $args['mode'];

    if (!in_array($mode, ['daily', 'weekly', 'history'], true)) {
        throw new RuntimeException('Unsupported pipeline mode: ' . $mode);
    }

    $limit = $args['limit'];

    if ($limit !== null && $limit <= 0) {
        throw new RuntimeException('Limit must be positive when provided.');
    }

    $reportsBase = rtrim($config['paths']['reports'], DIRECTORY_SEPARATOR);

    return [
        'mode' => $mode,
        'limit' => $limit,
        'dry_run' => $args['dry_run'],
        'chronicle' => $config['chronicle'],
        'batch_size' => (int) $config['batch']['size'],
        'report_dir' => $reportsBase . DIRECTORY_SEPARATOR . $mode,
        'history_dir' => $reportsBase . DIRECTORY_SEPARATOR . 'history',
        'log_path' => $config['paths']['logs'],
        'features' => $config['features'],
    ];
}

/**
 * @param array<string, mixed> $runtime
 * @param array<string, mixed> $character
 * @param bool $dryRun
 * @return array{briefWritten: bool, missingCount: int}
 */
function character_agent_handle_character(array $runtime, array $character, bool $dryRun): array
{
    $missingFields = character_agent_collect_missing_fields($character);
    $slug = character_agent_slug_for_character($character);
    $briefWritten = false;

    if ($runtime['features']['generate_briefs'] && character_agent_should_render_brief($character)) {
        $context = character_agent_build_brief_context($character);
        $markdown = character_agent_render_markdown_brief($context);
        $briefPath = $runtime['report_dir'] . DIRECTORY_SEPARATOR . $slug . '.md';

        if (!$dryRun) {
            character_agent_write_file($briefPath, $markdown);
        }

        $briefWritten = true;
    }

    if ($runtime['features']['missing_field_reports'] && $missingFields !== []) {
        $timestamp = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Ymd_His');
        $missingPath = $runtime['report_dir'] . DIRECTORY_SEPARATOR . $timestamp . '_' . $slug . '_missing.json';
        $payload = character_agent_build_missing_field_payload($character);

        if (!$dryRun) {
            character_agent_write_json($missingPath, $payload);
        }
    }

    if (!$dryRun) {
        $logPayload = character_agent_build_log_payload($character, $briefWritten, $missingFields);
        character_agent_append_log_entry($runtime['log_path'], $logPayload);
    }

    return [
        'briefWritten' => $briefWritten,
        'missingCount' => count($missingFields),
    ];
}

/**
 * @param array<string, mixed> $runtime
 * @param array<int, array<string, mixed>> $historySegments
 * @param bool $dryRun
 * @return void
 */
function character_agent_write_history(array $runtime, array $historySegments, bool $dryRun): void
{
    if ($historySegments === []) {
        return;
    }

    $generatedAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeImmutable::ATOM);
    $markdown = character_agent_render_history_markdown($historySegments, $runtime['chronicle'], $generatedAt);
    $timestamp = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Ymd_His');
    $path = $runtime['history_dir'] . DIRECTORY_SEPARATOR . $timestamp . '_city_history.md';

    if (!$dryRun) {
        character_agent_write_file($path, $markdown);
    }
}

/**
 * @param array<string, mixed> $runtime
 * @param mysqli $connection
 * @return array<string, mixed>
 */
function character_agent_run(array $runtime, mysqli $connection): array
{
    $offset = 0;
    $processed = 0;
    $briefs = 0;
    $missingReports = 0;
    $historySegments = [];
    $dryRun = $runtime['dry_run'];
    $limit = $runtime['limit'];

    while (true) {
        $batch = character_agent_fetch_batch(
            $connection,
            $runtime['chronicle'],
            $runtime['batch_size'],
            $offset
        );

        if ($batch === []) {
            break;
        }

        foreach ($batch as $character) {
            $result = character_agent_handle_character($runtime, $character, $dryRun);

            if ($runtime['features']['history_compilation']) {
                $historySegments[] = character_agent_build_history_segment($character);
            }

            $processed++;

            if ($result['briefWritten']) {
                $briefs++;
            }

            if ($result['missingCount'] > 0) {
                $missingReports++;
            }

            if ($limit !== null && $processed >= $limit) {
                break 2;
            }
        }

        $offset += count($batch);
    }

    if ($runtime['features']['history_compilation'] && $runtime['mode'] === 'history') {
        character_agent_write_history($runtime, $historySegments, $dryRun);
    }

    $timestamp = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DateTimeImmutable::ATOM);
    $runId = $dryRun ? null : bin2hex(random_bytes(6));

    $summary = [
        'timestamp' => $timestamp,
        'run_id' => $runId,
        'processed' => $processed,
        'briefs_written' => $briefs,
        'missing_reports' => $missingReports,
        'mode' => $runtime['mode'],
        'dry_run' => $dryRun,
        'message' => sprintf(
            'Processed %d characters (%d briefs, %d missing reports) in %s mode%s.',
            $processed,
            $briefs,
            $missingReports,
            $runtime['mode'],
            $dryRun ? ' [dry run]' : ''
        ),
    ];

    if (!$dryRun) {
        character_agent_append_run_summary($runtime['log_path'], $summary);
    }

    return $summary;
}

if (PHP_SAPI === 'cli') {
    $args = character_agent_parse_arguments($argv ?? []);

    /** @var array<string, mixed> $config */
    $config = require __DIR__ . '/config/pipeline.php';
    $runtime = character_agent_build_runtime($config, $args);
    $connection = character_agent_require_connection($conn ?? null);

    try {
        fwrite(
            STDOUT,
            sprintf(
                "[Character Agent] Starting run (mode=%s, limit=%s, dry_run=%s)\n",
                $runtime['mode'],
                $runtime['limit'] ?? 'none',
                $runtime['dry_run'] ? 'yes' : 'no'
            )
        );
        fflush(STDOUT);
        $summary = character_agent_run($runtime, $connection);
        fwrite(STDOUT, $summary['message'] . PHP_EOL);
    } catch (Throwable $throwable) {
        fwrite(STDERR, 'Character Agent pipeline failed: ' . $throwable->getMessage() . PHP_EOL);
        exit(1);
    } finally {
        mysqli_close($connection);
    }
}
