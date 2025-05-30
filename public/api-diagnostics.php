<?php
/**
 * API Diagnostic Tool
 * This script checks various aspects of the application to diagnose common issues
 */

// Set headers to prevent caching and ensure proper content type
header('Content-Type: text/plain');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// =============================================
// Configuration
// =============================================

$apiEndpoints = [
    '/api/system-configurations/public',
    '/system-configurations/public', // Without /api prefix
    '/api/user',
    '/api/terms-and-conditions/current'
];

$jsFiles = [
    '/js/app.js',
    '/js/admin.js',
    '/js/ui-extended.js',
    '/js/charts.js',
    '/build/assets/main-D4DSGRtK.js' // Current Vite asset
];

$baseUrl = 'http://localhost:8000';

// =============================================
// Helper Functions
// =============================================

/**
 * Check if a URL returns a valid response
 */
function checkEndpoint($url, $withAuth = false) {
    $headers = ['Accept: application/json'];
    
    if ($withAuth) {
        // Add auth token if available
        if (isset($_COOKIE['token'])) {
            $headers[] = 'Authorization: Bearer ' . $_COOKIE['token'];
        }
    }
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 5
    ]);
    
    $response = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'status' => $info['http_code'],
        'response' => $response ? json_decode($response, true) : null,
        'error' => $error,
        'time' => $info['total_time'],
    ];
}

/**
 * Format the result of an endpoint check
 */
function formatEndpointResult($url, $result) {
    $output = "URL: {$url}\n";
    $output .= "Status: {$result['status']}\n";
    
    if ($result['error']) {
        $output .= "Error: {$result['error']}\n";
    }
    
    $output .= "Response Time: " . number_format($result['time'], 4) . " seconds\n";
    
    if ($result['response']) {
        $output .= "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    }
    
    return $output;
}

/**
 * Check if a file exists and is accessible
 */
function checkFile($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_TIMEOUT => 5
    ]);
    
    curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'status' => $info['http_code'],
        'size' => $info['content_length_download'],
        'type' => $info['content_type'],
        'error' => $error,
    ];
}

/**
 * Format the result of a file check
 */
function formatFileResult($url, $result) {
    $output = "File: {$url}\n";
    $output .= "Status: {$result['status']}\n";
    
    if ($result['error']) {
        $output .= "Error: {$result['error']}\n";
    } else {
        $output .= "Size: " . ($result['size'] < 0 ? 'Unknown' : formatBytes($result['size'])) . "\n";
        $output .= "Type: {$result['type']}\n";
    }
    
    return $output;
}

/**
 * Format bytes to human readable format
 */
function formatBytes($bytes, $precision = 2) {
    if ($bytes <= 0) return '0 Bytes';
    
    $units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $index = floor(log($bytes, 1024));
    
    return round($bytes / pow(1024, $index), $precision) . ' ' . $units[$index];
}

// =============================================
// Main Script
// =============================================

echo "=== Analytics Hub API Diagnostic Tool ===\n\n";
echo "Running diagnostics at: " . date('Y-m-d H:i:s') . "\n";
echo "Base URL: {$baseUrl}\n\n";

// Check API endpoints
echo "== API Endpoint Checks ==\n\n";

foreach ($apiEndpoints as $endpoint) {
    $fullUrl = $baseUrl . $endpoint;
    echo formatEndpointResult($fullUrl, checkEndpoint($fullUrl)) . "\n";
}

// Check JS files
echo "== JavaScript File Checks ==\n\n";

foreach ($jsFiles as $file) {
    $fullUrl = $baseUrl . $file;
    echo formatFileResult($fullUrl, checkFile($fullUrl)) . "\n";
}

// Report environment variables
echo "== Environment ==\n\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "Current Script: " . __FILE__ . "\n\n";

echo "Diagnostic complete.\n";
