<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /admin/login.php');
    exit();
}

header('Content-Type: application/json; charset=utf-8');

/**
 * @return array<string, string>
 */
function character_agent_php_version_payload(): array
{
    return [
        'php_version' => PHP_VERSION,
        'php_os' => PHP_OS_FAMILY,
        'sapi' => PHP_SAPI,
    ];
}

echo json_encode(character_agent_php_version_payload(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

