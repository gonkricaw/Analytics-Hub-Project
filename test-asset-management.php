<?php
/**
 * Asset Management System Test
 * 
 * This file tests the AssetManager helper class and asset-related tools
 * to ensure they're working properly.
 * 
 * Usage: php test-asset-management.php
 */

// Bootstrap Laravel application
require_once __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Helpers\AssetManager;

echo "╔═══════════════════════════════════════════════════════╗\n";
echo "║          ASSET MANAGEMENT SYSTEM TEST                 ║\n";
echo "╚═══════════════════════════════════════════════════════╝\n\n";

// Test 1: Test AssetManager::getMainJs()
echo "TEST 1: AssetManager::getMainJs()\n";
echo "---------------------------------\n";
try {
    $jsPath = AssetManager::getMainJs();
    echo $jsPath ? "✓ JS Path: $jsPath\n" : "✗ JS Path not found\n";
    
    if ($jsPath) {
        $exists = AssetManager::assetExists($jsPath);
        echo $exists ? "✓ JS file exists on disk\n" : "✗ JS file does not exist on disk\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: Test AssetManager::getMainCss()
echo "TEST 2: AssetManager::getMainCss()\n";
echo "----------------------------------\n";
try {
    $cssPath = AssetManager::getMainCss();
    echo $cssPath ? "✓ CSS Path: $cssPath\n" : "✗ CSS Path not found\n";
    
    if ($cssPath) {
        $exists = AssetManager::assetExists($cssPath);
        echo $exists ? "✓ CSS file exists on disk\n" : "✗ CSS file does not exist on disk\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Test AssetManager::validateAllAssets()
echo "TEST 3: AssetManager::validateAllAssets()\n";
echo "---------------------------------------\n";
try {
    $results = AssetManager::validateAllAssets();
    echo "✓ Validation completed\n";
    echo "  Total assets: {$results['total']}\n";
    echo "  Valid assets: {$results['valid']}\n";
    echo "  Missing assets: {$results['missing']}\n";
    
    if ($results['missing'] > 0) {
        echo "\nMissing assets:\n";
        foreach ($results['missing_assets'] as $asset) {
            echo "  - $asset\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Test web.php route for /js/app.js
echo "TEST 4: Testing /js/app.js route\n";
echo "-------------------------------\n";
try {
    $response = makeRequest('/js/app.js');
    echo "Response status: {$response['status']}\n";
    
    if ($response['status'] === 302) {
        echo "✓ Redirect location: {$response['location']}\n";
        echo "✓ Route is correctly redirecting to the dynamic asset\n";
        
        // Check that the target asset exists
        $targetExists = AssetManager::assetExists($response['location']);
        echo $targetExists ? "✓ Target asset exists\n" : "✗ Target asset does not exist\n";
    } else {
        echo "✗ Route is not redirecting as expected\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Test web.php route for /css/app.css
echo "TEST 5: Testing /css/app.css route\n";
echo "--------------------------------\n";
try {
    $response = makeRequest('/css/app.css');
    echo "Response status: {$response['status']}\n";
    
    if ($response['status'] === 302) {
        echo "✓ Redirect location: {$response['location']}\n";
        echo "✓ Route is correctly redirecting to the dynamic asset\n";
        
        // Check that the target asset exists
        $targetExists = AssetManager::assetExists($response['location']);
        echo $targetExists ? "✓ Target asset exists\n" : "✗ Target asset does not exist\n";
    } else {
        echo "✗ Route is not redirecting as expected\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "╔═══════════════════════════════════════════════════════╗\n";
echo "║              TEST SUMMARY                             ║\n";
echo "╚═══════════════════════════════════════════════════════╝\n\n";

// Check that both asset paths are valid and exist
$jsPath = AssetManager::getMainJs();
$cssPath = AssetManager::getMainCss();
$jsExists = $jsPath && AssetManager::assetExists($jsPath);
$cssExists = $cssPath && AssetManager::assetExists($cssPath);

if ($jsExists && $cssExists) {
    echo "✓ OVERALL RESULT: SUCCESS - Asset management system is working correctly\n";
} else {
    echo "✗ OVERALL RESULT: FAILED - Asset management system has issues\n";
    if (!$jsExists) echo "  - JS asset issues detected\n";
    if (!$cssExists) echo "  - CSS asset issues detected\n";
}

/**
 * Helper function to make an internal request to the Laravel application
 * and return the response status and headers.
 *
 * @param string $uri The URI to request
 * @return array Response details (status and headers)
 */
function makeRequest($uri) {
    // Create a request to the application
    $request = Illuminate\Http\Request::create($uri, 'GET');
    
    // Disable exception handling to capture the response
    $kernel = app(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    
    // Return response details
    return [
        'status' => $response->getStatusCode(),
        'location' => $response->headers->get('Location'),
        'content' => $response->getContent(),
    ];
}
