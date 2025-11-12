<?php
declare(strict_types=1);

use RuntimeException;

/**
 * @param string $logPath
 * @param array<string, mixed> $payload
 * @return void
 */
function character_agent_append_log_entry(string $logPath, array $payload): void
{
    $directory = dirname($logPath);

    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Failed creating log directory: ' . $directory);
    }

    $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

    if ($encoded === false) {
        throw new RuntimeException('Failed encoding log payload.');
    }

    $written = file_put_contents($logPath, $encoded . PHP_EOL, FILE_APPEND);

    if ($written === false) {
        throw new RuntimeException('Failed appending to log file: ' . $logPath);
    }
}

/**
 * @param string $logPath
 * @param array<string, mixed> $summary
 * @return void
 */
function character_agent_append_run_summary(string $logPath, array $summary): void
{
    character_agent_append_log_entry($logPath, $summary);
}

