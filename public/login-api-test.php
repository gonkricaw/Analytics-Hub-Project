<?php
/**
 * Quick Login API Test
 * Run this file directly to test login API endpoint
 */

// Test different base URLs for the login endpoint
$baseUrls = [
    'http://localhost/Analytics-Hub-Project',
    'http://127.0.0.1/Analytics-Hub-Project',
    'http://localhost:8000',
    'http://127.0.0.1:8000'
];

echo "<h1>Login API Endpoint Test</h1>";

foreach ($baseUrls as $baseUrl) {
    echo "<h2>Testing: $baseUrl</h2>";
    
    $loginUrl = $baseUrl . '/api/login';
    
    // Test OPTIONS request first
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $loginUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'OPTIONS');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Origin: ' . $baseUrl,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "<div style='color: red;'>OPTIONS Error: $error</div>";
        continue;
    }
    
    echo "<div>OPTIONS request: HTTP $httpCode</div>";
    
    // Test POST request with test data
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $loginUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'email' => 'test@example.com',
        'password' => 'test123'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "<div style='color: red;'>POST Error: $error</div>";
    } else {
        $color = ($httpCode >= 200 && $httpCode < 500) ? 'green' : 'red';
        echo "<div style='color: $color;'>POST request: HTTP $httpCode</div>";
        
        if ($response) {
            echo "<div><strong>Response:</strong><pre style='max-height: 200px; overflow: auto;'>" . 
                 htmlspecialchars(substr($response, 0, 1000)) . "</pre></div>";
        }
    }
    
    echo "<hr>";
}

// Test CSRF cookie endpoint
echo "<h2>Testing CSRF Cookie Endpoint</h2>";
foreach ($baseUrls as $baseUrl) {
    $csrfUrl = $baseUrl . '/sanctum/csrf-cookie';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $csrfUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $color = ($httpCode >= 200 && $httpCode < 400) ? 'green' : 'red';
    echo "<div style='color: $color;'>$baseUrl/sanctum/csrf-cookie: HTTP $httpCode</div>";
}

?>

<script>
// Test frontend API call
console.log('Testing frontend API call...');

// Test if we can reach the API from JavaScript
fetch('/Analytics-Hub-Project/api/login', {
    method: 'OPTIONS',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }
})
.then(response => {
    console.log('Frontend OPTIONS test:', response.status);
    return fetch('/Analytics-Hub-Project/api/login', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            email: 'test@example.com',
            password: 'test123'
        })
    });
})
.then(response => {
    console.log('Frontend POST test:', response.status);
    return response.json();
})
.then(data => {
    console.log('Frontend response:', data);
})
.catch(error => {
    console.error('Frontend error:', error);
});
</script>
