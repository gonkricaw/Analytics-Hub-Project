<?php

echo "=== PRODUCTION ROUTE QUICK TEST ===\n\n";

// Test if we can create basic HTTP requests
echo "1. TESTING CURL AVAILABILITY\n";
if (function_exists('curl_init')) {
    echo "✅ cURL is available\n";
} else {
    echo "❌ cURL is not available\n";
    exit(1);
}

// Test routes via HTTP
$baseUrl = 'http://127.0.0.1:8000';

function testRoute($url, $expectedCodes = [200], $description = '') {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Production-Test/1.0');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ {$url} → CONNECTION ERROR: {$error}\n";
        return false;
    }
    
    if (in_array($httpCode, $expectedCodes)) {
        echo "✅ {$url} → {$httpCode} ({$description})\n";
        return true;
    } else {
        echo "⚠️  {$url} → {$httpCode} (Expected: " . implode('|', $expectedCodes) . ") ({$description})\n";
        return false;
    }
}

echo "\n2. TESTING PUBLIC ROUTES\n";
echo "─────────────────────────\n";

testRoute($baseUrl . '/', [200], 'SPA Root');
testRoute($baseUrl . '/login', [200], 'Login page');
testRoute($baseUrl . '/forgot-password', [200], 'Forgot password page');
testRoute($baseUrl . '/api/terms-and-conditions/current', [200], 'Public terms API');
testRoute($baseUrl . '/api/system-configurations/public', [200], 'Public config API');

echo "\n3. TESTING ASSET ROUTES\n";
echo "────────────────────────\n";

testRoute($baseUrl . '/favicon.ico', [200], 'Favicon');
testRoute($baseUrl . '/loader.css', [200], 'Loader CSS');
testRoute($baseUrl . '/css/app.css', [302], 'Legacy CSS redirect');
testRoute($baseUrl . '/js/app.js', [302], 'Legacy JS redirect');

echo "\n4. TESTING SPA ROUTES\n";
echo "──────────────────────\n";

testRoute($baseUrl . '/dashboard', [200], 'SPA Dashboard');
testRoute($baseUrl . '/admin', [200], 'SPA Admin');
testRoute($baseUrl . '/admin/users', [200], 'SPA Admin Users');
testRoute($baseUrl . '/profile', [200], 'SPA Profile');
testRoute($baseUrl . '/notifications', [200], 'SPA Notifications');

echo "\n5. TESTING PROTECTED API ROUTES (Should return 401)\n";
echo "────────────────────────────────────────────────────\n";

testRoute($baseUrl . '/api/user', [401], 'User info API (protected)');
testRoute($baseUrl . '/api/dashboard', [401], 'Dashboard API (protected)');
testRoute($baseUrl . '/api/admin/invitations', [401], 'Admin API (protected)');

echo "\n6. TESTING 404 ROUTES\n";
echo "──────────────────────\n";

testRoute($baseUrl . '/api/nonexistent', [404], 'Invalid API endpoint');
testRoute($baseUrl . '/build/nonexistent.js', [404], 'Invalid asset');

echo "\n7. TESTING EMBED ROUTE (Should be protected)\n";
echo "─────────────────────────────────────────────\n";

testRoute($baseUrl . '/app/embed/test-uuid', [401, 302], 'Embed route (protected)');

echo "\n=== ROUTE QUICK TEST COMPLETE ===\n";
