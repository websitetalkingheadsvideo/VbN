<?php
echo "Current directory: " . __DIR__ . "\n";
echo "Looking for: " . __DIR__ . '/../reference/Characters/' . "\n";
echo "Absolute path: /usr/home/working/public_html/wth/vbn/reference/Characters/\n";

$test_file = '/usr/home/working/public_html/wth/vbn/reference/Characters/Bayside Bob.json';
echo "Testing file: $test_file\n";
echo "File exists: " . (file_exists($test_file) ? 'YES' : 'NO') . "\n";

if (file_exists($test_file)) {
    echo "File contents preview:\n";
    $content = file_get_contents($test_file);
    $data = json_decode($content, true);
    if ($data) {
        echo "Character: " . $data['character_name'] . "\n";
        echo "Clan: " . $data['clan'] . "\n";
    }
}
?>
