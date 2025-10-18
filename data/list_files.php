<?php
echo "Files in data directory:\n";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if (strpos($file, '.json') !== false) {
        echo "- $file\n";
    }
}

echo "\nLooking for Leo.json: ";
$leo_file = __DIR__ . '/Leo.json';
echo file_exists($leo_file) ? "FOUND" : "NOT FOUND";
echo "\n";
?>
