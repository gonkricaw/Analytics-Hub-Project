<?php
/**
 * Test script to verify the public system configurations endpoint
 */

// Set up headers for the request
$headers = [
    'Accept: application/json'
];

// Initialize cURL session
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/api/system-configurations/public');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// Execute cURL session and get the response
$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Close cURL session
curl_close($ch);

// Output the results
echo "HTTP Status Code: " . $httpCode . "\n\n";

if ($error) {
    echo "cURL Error: " . $error . "\n";
} else {
    echo "Response Body:\n";
    $formattedJson = json_encode(json_decode($response), JSON_PRETTY_PRINT);
    echo $formattedJson . "\n";
}
