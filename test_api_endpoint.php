<?php
/**
 * Test API Endpoint Directly
 * Check if the API file is accessible and what error it's producing
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Test API Endpoint</h1>";
echo "<pre>";

// Test the API endpoint directly
$test_url = "https://vbn.talkingheads.video/admin/api_admin_locations_crud.php?id=3";

echo "Testing: $test_url\n\n";

// Use cURL to test
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

curl_close($ch);

echo "HTTP Status: $http_code\n";
echo "Headers:\n" . substr($response, 0, $header_size) . "\n";
echo "Body:\n" . substr($response, $header_size) . "\n";

if ($http_code === 500) {
    echo "\n❌ 500 Error - Server error\n";
    echo "This usually means:\n";
    echo "1. PHP fatal error\n";
    echo "2. Database connection failed\n";
    echo "3. File not found\n";
    echo "4. Permission issue\n";
}

echo "</pre>";
?>
