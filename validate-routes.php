<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== ROUTE INTEGRITY CHECK ===\n\n";

echo "1. TESTING PUBLIC ROUTES (Unauthenticated Access)\n";
echo "─────────────────────────────────────────────────\n";

$publicRoutes = [
    'GET /' => 'SPA Root (should load application)',
    'GET /login' => 'Login page',
    'GET /forgot-password' => 'Forgot password page',
    'GET /reset-password' => 'Reset password page',
    'POST /api/login' => 'Login API endpoint',
    'POST /api/forgot-password' => 'Forgot password API',
    'POST /api/reset-password' => 'Reset password API',
    'GET /api/terms-and-conditions/current' => 'Current terms and conditions',
    'GET /api/system-configurations/public' => 'Public system configurations',
];

foreach ($publicRoutes as $route => $description) {
    [$method, $uri] = explode(' ', $route, 2);
    
    try {
        $request = \Illuminate\Http\Request::create($uri, $method);
        $response = $kernel->handle($request);
        $statusCode = $response->getStatusCode();
        
        if ($statusCode >= 200 && $statusCode < 400) {
            echo "✅ {$route} → {$statusCode} ({$description})\n";
        } else {
            echo "⚠️  {$route} → {$statusCode} ({$description})\n";
        }
    } catch (Exception $e) {
        echo "❌ {$route} → ERROR: {$e->getMessage()}\n";
    }
}

echo "\n2. TESTING ASSET ROUTES\n";
echo "─────────────────────────\n";

$assetRoutes = [
    'GET /css/app.css' => 'Legacy CSS redirect',
    'GET /js/app.js' => 'Legacy JS redirect',
    'GET /favicon.ico' => 'Favicon',
    'GET /loader.css' => 'Loading CSS',
];

foreach ($assetRoutes as $route => $description) {
    [$method, $uri] = explode(' ', $route, 2);
    
    try {
        $request = \Illuminate\Http\Request::create($uri, $method);
        $response = $kernel->handle($request);
        $statusCode = $response->getStatusCode();
        
        if ($statusCode >= 200 && $statusCode < 400) {
            echo "✅ {$route} → {$statusCode} ({$description})\n";
        } else {
            echo "⚠️  {$route} → {$statusCode} ({$description})\n";
        }
    } catch (Exception $e) {
        echo "❌ {$route} → ERROR: {$e->getMessage()}\n";
    }
}

echo "\n3. TESTING SPA CATCH-ALL ROUTES\n";
echo "─────────────────────────────────\n";

$spaRoutes = [
    'GET /dashboard' => 'SPA Dashboard route',
    'GET /admin' => 'SPA Admin route',
    'GET /admin/users' => 'SPA Admin Users route',
    'GET /admin/menus' => 'SPA Admin Menus route',
    'GET /profile' => 'SPA Profile route',
    'GET /notifications' => 'SPA Notifications route',
    'GET /second-page' => 'SPA Second Page route',
    'GET /change-password' => 'SPA Change Password route',
];

foreach ($spaRoutes as $route => $description) {
    [$method, $uri] = explode(' ', $route, 2);
    
    try {
        $request = \Illuminate\Http\Request::create($uri, $method);
        $response = $kernel->handle($request);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        
        // Check if it returns the SPA layout
        if ($statusCode == 200 && strpos($content, '<div id="app">') !== false) {
            echo "✅ {$route} → {$statusCode} (SPA layout loaded)\n";
        } else {
            echo "⚠️  {$route} → {$statusCode} ({$description})\n";
        }
    } catch (Exception $e) {
        echo "❌ {$route} → ERROR: {$e->getMessage()}\n";
    }
}

echo "\n4. TESTING AUTHENTICATION-PROTECTED API ROUTES (Without Auth)\n";
echo "─────────────────────────────────────────────────────────────\n";

$protectedApiRoutes = [
    'GET /api/user' => 'Current user info',
    'POST /api/logout' => 'Logout',
    'GET /api/dashboard' => 'Dashboard data',
    'GET /api/menus' => 'Menu data',
    'GET /api/contents' => 'Content data',
    'GET /api/admin/invitations' => 'Admin invitations',
    'GET /api/user/notifications' => 'User notifications',
];

foreach ($protectedApiRoutes as $route => $description) {
    [$method, $uri] = explode(' ', $route, 2);
    
    try {
        $request = \Illuminate\Http\Request::create($uri, $method);
        $response = $kernel->handle($request);
        $statusCode = $response->getStatusCode();
        
        // Protected routes should return 401 without authentication
        if ($statusCode == 401) {
            echo "✅ {$route} → {$statusCode} (Properly protected)\n";
        } elseif ($statusCode == 419) {
            echo "✅ {$route} → {$statusCode} (CSRF protection active)\n";
        } else {
            echo "⚠️  {$route} → {$statusCode} (Expected 401 for unauth access)\n";
        }
    } catch (Exception $e) {
        echo "❌ {$route} → ERROR: {$e->getMessage()}\n";
    }
}

echo "\n5. TESTING EMBED ROUTE (Authentication Required)\n";
echo "─────────────────────────────────────────────────\n";

try {
    $embedRoute = '/app/embed/test-uuid';
    $request = \Illuminate\Http\Request::create($embedRoute, 'GET');
    $response = $kernel->handle($request);
    $statusCode = $response->getStatusCode();
    
    if ($statusCode == 401) {
        echo "✅ {$embedRoute} → {$statusCode} (Properly protected by auth:sanctum)\n";
    } else {
        echo "⚠️  {$embedRoute} → {$statusCode} (Expected 401 for embed route)\n";
    }
} catch (Exception $e) {
    echo "❌ Embed route → ERROR: {$e->getMessage()}\n";
}

echo "\n6. TESTING INVALID ROUTES\n";
echo "──────────────────────────\n";

$invalidRoutes = [
    'GET /nonexistent-page' => 'Should return SPA (catch-all)',
    'GET /api/nonexistent-endpoint' => 'Should return 404',
    'GET /build/nonexistent-asset.js' => 'Should return 404',
];

foreach ($invalidRoutes as $route => $description) {
    [$method, $uri] = explode(' ', $route, 2);
    
    try {
        $request = \Illuminate\Http\Request::create($uri, $method);
        $response = $kernel->handle($request);
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        
        if (strpos($uri, '/api/') !== false) {
            // API routes should return 404
            if ($statusCode == 404) {
                echo "✅ {$route} → {$statusCode} (Correct API 404)\n";
            } else {
                echo "⚠️  {$route} → {$statusCode} (Expected 404 for invalid API)\n";
            }
        } elseif (strpos($uri, '/build/') !== false) {
            // Build assets should return 404
            if ($statusCode == 404) {
                echo "✅ {$route} → {$statusCode} (Correct asset 404)\n";
            } else {
                echo "⚠️  {$route} → {$statusCode} (Expected 404 for invalid asset)\n";
            }
        } else {
            // SPA routes should return the app layout
            if ($statusCode == 200 && strpos($content, '<div id="app">') !== false) {
                echo "✅ {$route} → {$statusCode} (SPA catch-all working)\n";
            } else {
                echo "⚠️  {$route} → {$statusCode} (SPA catch-all issue)\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ {$route} → ERROR: {$e->getMessage()}\n";
    }
}

echo "\n=== ROUTE INTEGRITY CHECK COMPLETE ===\n";
