<?php
/**
 * Test Delete Location API
 * Simple test to check what's happening with the delete function
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 Test Delete Location API</h1>";
echo "<pre>";

try {
    // Test the API endpoint directly
    $test_url = "https://www.websitetalkingheads.com/vbn/admin/api_admin_locations_crud.php?id=3";
    
    echo "Testing DELETE request to: $test_url\n\n";
    
    // Use cURL to test the API
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
    
    echo "HTTP Status Code: $http_code\n";
    echo "Response Headers:\n";
    echo substr($response, 0, $header_size) . "\n";
    echo "Response Body:\n";
    echo substr($response, $header_size) . "\n";
    
    if ($http_code === 200) {
        $body = substr($response, $header_size);
        $json = json_decode($body, true);
        
        if ($json) {
            echo "\n✅ JSON Response:\n";
            echo json_encode($json, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "\n❌ Invalid JSON response\n";
        }
    }
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>
