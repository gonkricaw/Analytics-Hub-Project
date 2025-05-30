<?php
// Configuration Test File

// Display server information
echo "<h1>Server Configuration Test</h1>";
echo "<h2>Server Information</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "</pre>";

// Check for necessary PHP extensions
echo "<h2>PHP Extensions</h2>";
echo "<pre>";
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'json'];
foreach ($required_extensions as $ext) {
    echo $ext . ": " . (extension_loaded($ext) ? "Loaded ✓" : "Not loaded ✗") . "\n";
}
echo "</pre>";

// Check file permissions
echo "<h2>Directory Write Permissions</h2>";
echo "<pre>";
$directories = ['storage', 'bootstrap/cache', 'public/build'];
foreach ($directories as $dir) {
    $full_path = realpath(__DIR__ . '/../' . $dir);
    echo $dir . ": " . (is_writable($full_path) ? "Writable ✓" : "Not Writable ✗") . " ($full_path)\n";
}
echo "</pre>";

// Check for specific .htaccess rules
echo "<h2>.htaccess Configuration</h2>";
echo "<pre>";
$htaccess_path = __DIR__ . '/.htaccess';
if (file_exists($htaccess_path)) {
    $htaccess = file_get_contents($htaccess_path);
    echo "File exists ✓\n";
    
    // Check for MIME types
    if (strpos($htaccess, 'AddType application/javascript') !== false) {
        echo "JavaScript MIME type defined ✓\n";
    } else {
        echo "JavaScript MIME type not defined ✗\n";
    }
    
    // Check for no 404 redirect for static assets
    if (strpos($htaccess, 'RewriteCond %{REQUEST_URI} \.(js|css|woff|woff2|ttf|eot|svg|png|jpg|jpeg|gif|ico)$ [NC]') !== false) {
        echo "WARNING: Static asset 404 rule found - this might cause MIME type issues ✗\n";
    } else {
        echo "No problematic static asset rules found ✓\n";
    }
} else {
    echo ".htaccess file not found ✗\n";
}
echo "</pre>";

// Check Vite configuration
echo "<h2>Vite Configuration</h2>";
echo "<pre>";
$vite_config_path = __DIR__ . '/../vite.config.js';
if (file_exists($vite_config_path)) {
    $vite_config = file_get_contents($vite_config_path);
    echo "File exists ✓\n";
    
    // Check for base path
    if (strpos($vite_config, "base: '/'") !== false) {
        echo "Base path is set correctly ✓\n";
    } else {
        echo "Base path might not be set correctly ✗\n";
    }
    
    // Check Laravel plugin
    if (strpos($vite_config, 'laravel({') !== false) {
        echo "Laravel plugin found ✓\n";
    } else {
        echo "Laravel plugin not found ✗\n";
    }
} else {
    echo "vite.config.js file not found ✗\n";
}
echo "</pre>";

// Check for manifest.json file
echo "<h2>Vite Manifest</h2>";
echo "<pre>";
$manifest_path = __DIR__ . '/build/manifest.json';
if (file_exists($manifest_path)) {
    echo "File exists ✓\n";
    $manifest = json_decode(file_get_contents($manifest_path), true);
    if ($manifest) {
        echo "Valid JSON ✓\n";
        echo "Number of entries: " . count($manifest) . "\n";
    } else {
        echo "Invalid JSON ✗\n";
    }
} else {
    echo "manifest.json file not found ✗\n";
    echo "Try running: npm run build\n";
}
echo "</pre>";

// Test module request
echo "<h2>Module Request Test</h2>";
echo "<pre>";
$test_url = 'http://' . $_SERVER['HTTP_HOST'] . '/build/assets/index.js';
echo "Testing URL: $test_url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $test_url);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

if ($response) {
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $headers = substr($response, 0, $header_size);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo "Status: $status\n";
    echo "Headers:\n$headers\n";
    
    if (preg_match('/Content-Type: ([^\r\n]+)/i', $headers, $matches)) {
        $content_type = $matches[1];
        if (strpos($content_type, 'application/javascript') !== false) {
            echo "Correct MIME type ($content_type) ✓\n";
        } else {
            echo "Incorrect MIME type ($content_type) ✗\n";
        }
    } else {
        echo "Content-Type header not found ✗\n";
    }
} else {
    echo "Request failed: " . curl_error($ch) . " ✗\n";
}
curl_close($ch);
echo "</pre>";
