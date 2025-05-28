<?php
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
    echo "📊 Found " . count($manifest) . " entries in manifest\n";
}

echo "\n2. CHECKING MAIN ENTRY POINTS...\n";

// Check main JS entry
if (isset($manifest['resources/js/main.js'])) {
    $mainEntry = $manifest['resources/js/main.js'];
    $mainJsPath = 'public/build/' . $mainEntry['file'];
    
    if (file_exists($mainJsPath)) {
        echo "✅ Main JS asset found: /build/{$mainEntry['file']}\n";
        
        // Check file size to ensure it's not empty
        $size = filesize($mainJsPath);
        echo "📊 Main JS size: " . number_format($size) . " bytes\n";
        
        if ($size < 1000) {
            echo "⚠️  WARNING: Main JS file seems very small, might be incomplete\n";
        }
    } else {
        echo "❌ CRITICAL: Main JS asset file missing: {$mainJsPath}\n";
    }
    
    // Check CSS
    if (isset($mainEntry['css']) && count($mainEntry['css']) > 0) {
        $mainCssFile = $mainEntry['css'][0];
        $mainCssPath = 'public/build/' . $mainCssFile;
        
        if (file_exists($mainCssPath)) {
            echo "✅ Main CSS asset found: /build/{$mainCssFile}\n";
            
            $cssSize = filesize($mainCssPath);
            echo "📊 Main CSS size: " . number_format($cssSize) . " bytes\n";
            
            if ($cssSize < 1000) {
                echo "⚠️  WARNING: Main CSS file seems very small, might be incomplete\n";
            }
        } else {
            echo "❌ CRITICAL: Main CSS asset file missing: {$mainCssPath}\n";
        }
    } else {
        echo "❌ WARNING: No CSS files found for main entry\n";
    }
} else {
    echo "❌ CRITICAL: Main JS entry 'resources/js/main.js' not found in manifest\n";
}

echo "\n3. VALIDATING ALL ASSETS...\n";
$totalAssets = 0;
$validAssets = 0;
$missingAssets = [];

foreach ($manifest as $entryName => $details) {
    // Check main file
    if (isset($details['file'])) {
        $totalAssets++;
        $filePath = 'public/build/' . $details['file'];
        
        if (file_exists($filePath)) {
            $validAssets++;
        } else {
            $missingAssets[] = "/build/{$details['file']}";
        }
    }
    
    // Check CSS files
    if (isset($details['css']) && is_array($details['css'])) {
        foreach ($details['css'] as $cssFile) {
            $totalAssets++;
            $filePath = 'public/build/' . $cssFile;
            
            if (file_exists($filePath)) {
                $validAssets++;
            } else {
                $missingAssets[] = "/build/{$cssFile}";
            }
        }
    }
}

echo "📊 Asset Validation Summary:\n";
echo "   Total assets: {$totalAssets}\n";
echo "   Valid assets: {$validAssets}\n";
echo "   Missing assets: " . count($missingAssets) . "\n";

if (count($missingAssets) > 0) {
    echo "\n❌ MISSING ASSETS:\n";
    foreach ($missingAssets as $missing) {
        echo "   - {$missing}\n";
    }
} else {
    echo "✅ All assets are present\n";
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
    
    if (strpos($content, 'loader.css') !== false) {
        echo "✅ Loader CSS reference found\n";
    } else {
        echo "❌ WARNING: Loader CSS reference not found\n";
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

// Check .htaccess
if (file_exists('public/.htaccess')) {
    echo "✅ Apache .htaccess found\n";
} else {
    echo "❌ WARNING: Apache .htaccess missing\n";
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
    
    // Check for development artifacts
    if (strpos($envContent, 'VITE_DEV_SERVER_KEY') !== false) {
        echo "⚠️  WARNING: Development Vite keys found in .env\n";
    }
} else {
    echo "❌ CRITICAL: .env file not found\n";
}

echo "\n7. CHECKING BUILD OPTIMIZATION...\n";

// Check if source maps are exposed (shouldn't be in production)
$buildDir = 'public/build/assets';
if (is_dir($buildDir)) {
    $mapFiles = glob($buildDir . '/*.map');
    if (count($mapFiles) > 0) {
        echo "⚠️  WARNING: " . count($mapFiles) . " source map files found (should be removed for production)\n";
        foreach ($mapFiles as $mapFile) {
            echo "   - " . basename($mapFile) . "\n";
        }
    } else {
        echo "✅ No source map files found in build\n";
    }
    
    // Check for unminified files (basic check)
    $jsFiles = glob($buildDir . '/*.js');
    $potentiallyUnminified = 0;
    
    foreach ($jsFiles as $jsFile) {
        $content = file_get_contents($jsFile);
        // Simple heuristic: minified files usually have very few line breaks
        $lineCount = substr_count($content, "\n");
        $fileSize = filesize($jsFile);
        
        if ($fileSize > 10000 && $lineCount > 100) { // If file is large but has many lines
            $potentiallyUnminified++;
        }
    }
    
    if ($potentiallyUnminified > 0) {
        echo "⚠️  WARNING: {$potentiallyUnminified} JavaScript files might not be properly minified\n";
    } else {
        echo "✅ JavaScript files appear to be minified\n";
    }
} else {
    echo "❌ CRITICAL: Build assets directory not found\n";
}

echo "\n=== ASSET CHECK COMPLETE ===\n";
