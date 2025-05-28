<?php
require_once 'vendor/autoload.php';

use App\Helpers\AssetManager;

echo "=== PRODUCTION BUILD UI ASSETS & ROUTE INTEGRITY CHECK ===\n\n";

echo "1. CHECKING ASSET MANIFEST...\n";
$manifestPath = 'public/build/manifest.json';

if (!file_exists($manifestPath)) {
    echo "❌ CRITICAL: Asset manifest not found at {$manifestPath}\n";
    exit(1);
} else {
    echo "✅ Asset manifest found\n";
}

$manifest = json_decode(file_get_contents($manifestPath), true);
if ($manifest === null) {
    echo "❌ CRITICAL: Invalid JSON in manifest file\n";
    exit(1);
} else {
    echo "✅ Manifest JSON is valid\n";
}

echo "\n2. CHECKING MAIN ENTRY POINTS...\n";

// Check main JS
$mainJs = AssetManager::getMainJs();
if ($mainJs && AssetManager::assetExists($mainJs)) {
    echo "✅ Main JS asset found: {$mainJs}\n";
} else {
    echo "❌ CRITICAL: Main JS asset missing or not found\n";
}

// Check main CSS
$mainCss = AssetManager::getMainCss();
if ($mainCss && AssetManager::assetExists($mainCss)) {
    echo "✅ Main CSS asset found: {$mainCss}\n";
} else {
    echo "❌ CRITICAL: Main CSS asset missing or not found\n";
}

echo "\n3. VALIDATING ALL ASSETS...\n";
try {
    $validation = AssetManager::validateAllAssets();
    echo "📊 Asset Validation Summary:\n";
    echo "   Total assets: {$validation['total']}\n";
    echo "   Valid assets: {$validation['valid']}\n";
    echo "   Missing assets: {$validation['missing']}\n";
    
    if ($validation['missing'] > 0) {
        echo "\n❌ MISSING ASSETS:\n";
        foreach ($validation['missing_assets'] as $missing) {
            echo "   - {$missing}\n";
        }
    } else {
        echo "✅ All assets are present\n";
    }
} catch (Exception $e) {
    echo "❌ CRITICAL: Asset validation failed: " . $e->getMessage() . "\n";
}

echo "\n4. CHECKING BLADE LAYOUT...\n";
$layoutPath = 'resources/views/application.blade.php';
if (file_exists($layoutPath)) {
    echo "✅ Main Blade layout found\n";
    $content = file_get_contents($layoutPath);
    if (strpos($content, '@vite') !== false) {
        echo "✅ Vite directive found in layout\n";
    } else {
        echo "❌ WARNING: Vite directive not found in layout\n";
    }
    if (strpos($content, 'favicon.ico') !== false) {
        echo "✅ Favicon reference found\n";
    } else {
        echo "❌ WARNING: Favicon reference not found\n";
    }
} else {
    echo "❌ CRITICAL: Main Blade layout not found\n";
}

echo "\n5. CHECKING CRITICAL ASSETS...\n";

// Check favicon
if (file_exists('public/favicon.ico')) {
    echo "✅ Favicon found\n";
} else {
    echo "❌ WARNING: Favicon missing\n";
}

// Check loader.css
if (file_exists('public/loader.css')) {
    echo "✅ Loader CSS found\n";
} else {
    echo "❌ WARNING: Loader CSS missing\n";
}

echo "\n6. ENVIRONMENT CHECK...\n";

// Load environment
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    if (strpos($envContent, 'APP_ENV=production') !== false) {
        echo "✅ Environment set to production\n";
    } else {
        echo "❌ WARNING: Environment is not set to production\n";
    }
    
    if (strpos($envContent, 'APP_DEBUG=false') !== false) {
        echo "✅ Debug mode disabled\n";
    } else {
        echo "❌ WARNING: Debug mode is not disabled\n";
    }
} else {
    echo "❌ CRITICAL: .env file not found\n";
}

echo "\n=== ASSET CHECK COMPLETE ===\n";
