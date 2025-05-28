#!/usr/bin/env php
<?php
/**
 * API Endpoint Test Script
 * Tests all critical API endpoints to ensure they're working correctly
 */

echo "🔍 Testing API Endpoints for Indonet Analytics Hub\n";
echo "=" . str_repeat("=", 50) . "\n\n";

$baseUrl = 'http://localhost:8000';
$endpoints = [
    // Public endpoints
    [
        'name' => 'Public System Configurations',
        'url' => '/api/system-configurations/public',
        'method' => 'GET',
        'auth' => false
    ],
    [
        'name' => 'Terms and Conditions',
        'url' => '/api/terms-and-conditions/current',
        'method' => 'GET',
        'auth' => false
    ],
    [
        'name' => 'Login Endpoint',
        'url' => '/api/login',
        'method' => 'POST',
        'auth' => false,
        'skip' => true // Skip actual login test
    ],
    // Static file endpoints
    [
        'name' => 'Main CSS File',
        'url' => '/build/assets/main-Czk1McQF.css',
        'method' => 'GET',
        'auth' => false,
        'type' => 'static'
    ],
    [
        'name' => 'Main JS File',
        'url' => '/build/assets/main-D4DSGRtK.js',
        'method' => 'GET',
        'auth' => false,
        'type' => 'static'
    ],
    [
        'name' => 'Admin JS File',
        'url' => '/js/admin.js',
        'method' => 'GET',
        'auth' => false,
        'type' => 'static'
    ],
    [
        'name' => 'Charts JS File',
        'url' => '/js/charts.js',
        'method' => 'GET',
        'auth' => false,
        'type' => 'static'
    ]
];

function testEndpoint($baseUrl, $endpoint) {
    if (isset($endpoint['skip']) && $endpoint['skip']) {
        echo "⏭️  {$endpoint['name']}: SKIPPED\n";
        return;
    }

    $url = $baseUrl . $endpoint['url'];
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, true);

    if ($endpoint['method'] === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    $error = curl_error($ch);

    curl_close($ch);

    // Determine status
    $status = '';
    $message = '';

    if (!empty($error)) {
        $status = '❌ ERROR';
        $message = $error;
    } else {
        switch ($httpCode) {
            case 200:
                $status = '✅ SUCCESS';
                if (isset($endpoint['type']) && $endpoint['type'] === 'static') {
                    $size = strlen($body);
                    $message = "Size: " . formatBytes($size);
                } else {
                    // Try to decode JSON
                    $json = json_decode($body, true);
                    if ($json && isset($json['success'])) {
                        $message = $json['success'] ? 'API responds correctly' : 'API error: ' . ($json['message'] ?? 'Unknown error');
                    } else {
                        $message = 'Response received';
                    }
                }
                break;
            case 404:
                $status = '❌ NOT FOUND';
                $message = 'Endpoint not found';
                break;
            case 500:
                $status = '❌ SERVER ERROR';
                $message = 'Internal server error';
                break;
            default:
                $status = "⚠️  HTTP $httpCode";
                $message = 'Unexpected response code';
        }
    }

    echo "{$status} {$endpoint['name']}: {$message}\n";
    
    if ($httpCode !== 200) {
        echo "   URL: {$url}\n";
    }
}

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB');
    $base = log($size, 1024);
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
}

echo "Testing endpoints...\n\n";

foreach ($endpoints as $endpoint) {
    testEndpoint($baseUrl, $endpoint);
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "✨ API Endpoint Testing Complete!\n";
echo "\nFor any failing endpoints:\n";
echo "1. Check if Apache/Nginx is running\n";
echo "2. Verify Laravel application is properly configured\n";
echo "3. Check .htaccess rules\n";
echo "4. Ensure assets are built (npm run build)\n";
