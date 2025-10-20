<?php
/**
 * Version Command Handler
 * Processes version increment commands
 */

// Get command from command line or web request
$command = $argv[1] ?? $_GET['cmd'] ?? '';

switch ($command) {
    case 'increment':
    case 'patch':
        echo "🔄 Incrementing PATCH version...\n";
        exec('php increment_version.php patch', $output, $returnCode);
        echo implode("\n", $output) . "\n";
        break;
        
    case 'minor':
        echo "🔄 Incrementing MINOR version...\n";
        exec('php increment_version.php minor', $output, $returnCode);
        echo implode("\n", $output) . "\n";
        break;
        
    case 'major':
        echo "🔄 Incrementing MAJOR version...\n";
        exec('php increment_version.php major', $output, $returnCode);
        echo implode("\n", $output) . "\n";
        break;
        
    case 'status':
        require_once 'includes/version.php';
        echo "📊 Current Version: " . LOTN_VERSION . "\n";
        echo "📅 Last Modified: " . date('Y-m-d H:i:s', filemtime('VERSION.md')) . "\n";
        break;
        
    default:
        echo "📋 Version Commands:\n";
        echo "  php version_commands.php increment  - Increment patch version\n";
        echo "  php version_commands.php patch      - Increment patch version\n";
        echo "  php version_commands.php minor      - Increment minor version\n";
        echo "  php version_commands.php major      - Increment major version\n";
        echo "  php version_commands.php status     - Show current version\n";
        break;
}
?>
