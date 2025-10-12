<?php
echo "=== METHOD TEST ===\n";
echo "Request method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "POST data: " . (empty($_POST) ? 'EMPTY' : 'HAS DATA') . "\n";
echo "Raw input: " . file_get_contents('php://input') . "\n";
echo "=== TEST COMPLETE ===\n";
?>
