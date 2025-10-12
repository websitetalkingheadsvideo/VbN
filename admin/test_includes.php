<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('LOTN_VERSION', '0.5.0');
session_start();

echo "Testing includes path...<br>";

$connect_path = __DIR__ . '/../includes/connect.php';
echo "Connect path: " . $connect_path . "<br>";
echo "File exists: " . (file_exists($connect_path) ? 'YES' : 'NO') . "<br>";

if (file_exists($connect_path)) {
    require_once $connect_path;
    echo "Connect included successfully<br>";
    echo "Connection variable exists: " . (isset($conn) ? 'YES' : 'NO') . "<br><br>";
}

echo "Now testing header include...<br>";
$header_path = __DIR__ . '/../includes/header.php';
echo "Header path: " . $header_path . "<br>";
echo "File exists: " . (file_exists($header_path) ? 'YES' : 'NO') . "<br>";

if (file_exists($header_path)) {
    include $header_path;
    echo "Header included successfully<br>";
}
?>

