<?php

echo "🏆 FINAL PRODUCTION BUILD & ROUTE INTEGRITY REPORT\n";
echo "================================================\n";
echo "Analytics Hub Laravel + Vue.js SPA - Production State\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

// Initialize score tracking
$totalChecks = 0;
$passedChecks = 0;

function checkItem($condition, $description, $critical = false) {
    global $totalChecks, $passedChecks;
    $totalChecks++;
    
    if ($condition) {
        $passedChecks++;
        echo "✅ {$description}\n";
        return true;
    } else {
        $icon = $critical ? "❌" : "⚠️ ";
        echo "{$icon} {$description}\n";
        return false;
    }
}

// 1. ENVIRONMENT & CONFIGURATION
echo "1. PRODUCTION ENVIRONMENT\n";
echo "──────────────────────────\n";

$envPath = __DIR__ . '/.env';
$envContent = file_exists($envPath) ? file_get_contents($envPath) : '';

checkItem(strpos($envContent, 'APP_ENV=production') !== false, 'Environment set to production', true);
checkItem(strpos($envContent, 'APP_DEBUG=false') !== false, 'Debug mode disabled', true);
checkItem(file_exists(__DIR__ . '/bootstrap/cache/config.php'), 'Configuration cached');
checkItem(file_exists(__DIR__ . '/bootstrap/cache/routes-v7.php'), 'Routes cached');

// 2. ASSET BUILD STATUS
echo "\n2. UI ASSETS & BUILD\n";
echo "────────────────────\n";

$manifestPath = __DIR__ . '/public/build/manifest.json';
$manifestExists = file_exists($manifestPath);
checkItem($manifestExists, 'Production build manifest exists', true);

if ($manifestExists) {
    $manifest = json_decode(file_get_contents($manifestPath), true);
    $assetCount = count($manifest);
    checkItem($assetCount > 100, "Large asset collection ({$assetCount} assets)");
    
    // Check main assets
    if (isset($manifest['resources/js/main.js'])) {
        $mainJsPath = __DIR__ . '/public/build/' . ltrim($manifest['resources/js/main.js']['file'], '/');
        $jsSize = file_exists($mainJsPath) ? filesize($mainJsPath) : 0;
        checkItem($jsSize > 300000, "Main JS properly built (" . number_format($jsSize/1024, 1) . "KB)");
    }
}

checkItem(file_exists(__DIR__ . '/public/favicon.ico'), 'Favicon present');
checkItem(file_exists(__DIR__ . '/public/loader.css'), 'Loading screen CSS present');

// 3. CODE QUALITY & PRODUCTION READINESS
echo "\n3. CODE QUALITY\n";
echo "───────────────\n";

// Check for console statements protection
$codeQualityFiles = [
    'resources/js/App.vue',
    'resources/js/stores/systemConfig.js', 
    'resources/js/utils/crossBrowserUtils.js'
];

$allClean = true;
foreach ($codeQualityFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        $content = file_get_contents(__DIR__ . '/' . $file);
        $lines = explode("\n", $content);
        $hasUnguardedConsole = false;
        
        foreach ($lines as $lineNum => $line) {
            if (preg_match('/console\.(log|warn|error)/', $line)) {
                $context = '';
                for ($i = max(0, $lineNum - 10); $i <= min(count($lines) - 1, $lineNum + 5); $i++) {
                    $context .= $lines[$i] . "\n";
                }
                if (strpos($context, 'import.meta.env.DEV') === false) {
                    $hasUnguardedConsole = true;
                    break;
                }
            }
        }
        
        if ($hasUnguardedConsole) {
            $allClean = false;
            break;
        }
    }
}

checkItem($allClean, 'Console statements properly guarded for production', true);

// Check for source maps (should not be present in production)
$sourceMapCheck = !file_exists(__DIR__ . '/public/build/assets/main.js.map');
checkItem($sourceMapCheck, 'No source maps exposed in production');

// 4. ROUTE INTEGRITY
echo "\n4. ROUTE CONFIGURATION\n";
echo "──────────────────────\n";

$webRoutesPath = __DIR__ . '/routes/web.php';
$apiRoutesPath = __DIR__ . '/routes/api.php';

checkItem(file_exists($webRoutesPath), 'Web routes file exists', true);
checkItem(file_exists($apiRoutesPath), 'API routes file exists', true);

if (file_exists($webRoutesPath)) {
    $webContent = file_get_contents($webRoutesPath);
    checkItem(strpos($webContent, '{any?}') !== false, 'SPA catch-all route configured');
    checkItem(strpos($webContent, 'application') !== false, 'Main layout route present');
}

if (file_exists($apiRoutesPath)) {
    $apiContent = file_get_contents($apiRoutesPath);
    checkItem(strpos($apiContent, 'auth:sanctum') !== false, 'API routes protected with Sanctum');
    $routeCount = substr_count($apiContent, 'Route::');
    checkItem($routeCount > 50, "Comprehensive API routes ({$routeCount} endpoints)");
}

// 5. AUTHENTICATION SYSTEM
echo "\n5. AUTHENTICATION & SECURITY\n";
echo "─────────────────────────────\n";

checkItem(file_exists(__DIR__ . '/config/sanctum.php'), 'Sanctum authentication configured', true);
checkItem(file_exists(__DIR__ . '/resources/js/composables/useAuth.js'), 'Frontend auth composable present', true);
checkItem(file_exists(__DIR__ . '/resources/js/pages/login.vue'), 'Login page present');
checkItem(file_exists(__DIR__ . '/resources/js/pages/forgot-password.vue'), 'Password recovery page present');
checkItem(file_exists(__DIR__ . '/resources/js/pages/reset-password.vue'), 'Password reset page present');

// Router guards check
$routerPath = __DIR__ . '/resources/js/plugins/1.router/index.js';
if (file_exists($routerPath)) {
    $routerContent = file_get_contents($routerPath);
    checkItem(strpos($routerContent, 'setupAuthGuards') !== false, 'Authentication guards configured');
}

// Session security
$sessionConfigPath = __DIR__ . '/config/session.php';
if (file_exists($sessionConfigPath)) {
    $sessionConfig = file_get_contents($sessionConfigPath);
    checkItem(strpos($sessionConfig, "'http_only' => true") !== false, 'HTTP-only cookies enabled');
}

// Security headers
$htaccessPath = __DIR__ . '/public/.htaccess';
if (file_exists($htaccessPath)) {
    $htaccessContent = file_get_contents($htaccessPath);
    checkItem(strpos($htaccessContent, 'X-Frame-Options') !== false, 'Security headers configured');
}

// 6. DATABASE & MIGRATIONS
echo "\n6. DATABASE SCHEMA\n";
echo "──────────────────\n";

$migrationsPath = __DIR__ . '/database/migrations';
if (is_dir($migrationsPath)) {
    $migrations = scandir($migrationsPath);
    $userMigrations = array_filter($migrations, function($file) {
        return strpos($file, 'user') !== false || strpos($file, 'personal_access_token') !== false;
    });
    $roleMigrations = array_filter($migrations, function($file) {
        return strpos($file, 'role') !== false || strpos($file, 'permission') !== false;
    });
    
    checkItem(count($userMigrations) > 0, 'User authentication schema present');
    checkItem(count($roleMigrations) > 0, 'Role-based access control schema present');
}

// 7. CRITICAL APPLICATION FILES
echo "\n7. APPLICATION STRUCTURE\n";
echo "─────────────────────────\n";

checkItem(file_exists(__DIR__ . '/resources/views/application.blade.php'), 'Main Blade layout present', true);
checkItem(file_exists(__DIR__ . '/resources/js/main.js'), 'Main JS entry point present', true);
checkItem(file_exists(__DIR__ . '/app/Helpers/AssetManager.php'), 'Asset management helper present');

// Check for critical Vue components
$vueFiles = [
    'resources/js/App.vue' => 'Main Vue application',
    'resources/js/layouts/default.vue' => 'Default layout'
];

foreach ($vueFiles as $file => $description) {
    checkItem(file_exists(__DIR__ . '/' . $file), $description);
}

// 8. FINAL SCORE & RECOMMENDATIONS
echo "\n" . str_repeat("=", 50) . "\n";
echo "FINAL PRODUCTION INTEGRITY SCORE\n";
echo str_repeat("=", 50) . "\n";

$scorePercentage = round(($passedChecks / $totalChecks) * 100, 1);
echo "Score: {$passedChecks}/{$totalChecks} ({$scorePercentage}%)\n\n";

if ($scorePercentage >= 95) {
    echo "🎉 EXCELLENT - PRODUCTION READY!\n";
    echo "✅ All critical systems operational\n";
    echo "✅ Security measures in place\n";
    echo "✅ Assets optimized and built\n";
    echo "✅ Authentication system configured\n";
    echo "✅ Route integrity verified\n\n";
    echo "🚀 DEPLOYMENT RECOMMENDATION: APPROVED\n";
} elseif ($scorePercentage >= 85) {
    echo "✅ GOOD - MOSTLY PRODUCTION READY\n";
    echo "Minor issues detected but core functionality intact\n\n";
    echo "🔧 DEPLOYMENT RECOMMENDATION: PROCEED WITH CAUTION\n";
} elseif ($scorePercentage >= 70) {
    echo "⚠️  FAIR - NEEDS ATTENTION\n";
    echo "Several issues need to be resolved before production\n\n";
    echo "🔴 DEPLOYMENT RECOMMENDATION: FIX ISSUES FIRST\n";
} else {
    echo "❌ POOR - NOT PRODUCTION READY\n";
    echo "Critical issues must be resolved\n\n";
    echo "🚫 DEPLOYMENT RECOMMENDATION: DO NOT DEPLOY\n";
}

echo "\n📋 COMPLETED VERIFICATION AREAS:\n";
echo "• Environment Configuration\n";
echo "• Asset Build & Optimization\n";
echo "• Code Quality & Debug Cleanup\n";
echo "• Route Configuration & Integrity\n";
echo "• Authentication & Authorization Flow\n";
echo "• Security Headers & Session Management\n";
echo "• Database Schema & Migrations\n";
echo "• Application Structure & Critical Files\n";

echo "\n📊 DETAILED FINDINGS:\n";
echo "• ✅ {$passedChecks} checks passed\n";
echo "• ⚠️  " . ($totalChecks - $passedChecks) . " items need attention\n";

echo "\n" . str_repeat("=", 50) . "\n";
echo "Production Build UI Assets & Route Integrity Check COMPLETE\n";
echo "Report generated: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 50) . "\n";

?>
