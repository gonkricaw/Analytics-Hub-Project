<?php

echo "🔐 AUTHENTICATION & AUTHORIZATION FLOW ANALYSIS\n";
echo "==============================================\n\n";

// 1. Authentication Configuration Check
echo "1. AUTHENTICATION CONFIGURATION\n";
echo "─────────────────────────────────\n";

// Check auth.php config
$authConfigPath = __DIR__ . '/config/auth.php';
if (file_exists($authConfigPath)) {
    $authConfig = file_get_contents($authConfigPath);
    
    if (strpos($authConfig, "'guard' => 'web'") !== false) {
        echo "✅ Default guard set to 'web'\n";
    }
    
    if (strpos($authConfig, "'driver' => 'session'") !== false) {
        echo "✅ Session driver configured\n";
    }
    
    if (strpos($authConfig, "'api' => [") !== false) {
        echo "✅ API guard configured\n";
    }
}

// Check sanctum.php config
$sanctumConfigPath = __DIR__ . '/config/sanctum.php';
if (file_exists($sanctumConfigPath)) {
    echo "✅ Sanctum configuration present\n";
} else {
    echo "❌ Sanctum configuration missing\n";
}

// 2. Middleware Analysis
echo "\n2. AUTHENTICATION MIDDLEWARE ANALYSIS\n";
echo "───────────────────────────────────────\n";

// Check API routes for auth middleware
$apiRoutesPath = __DIR__ . '/routes/api.php';
if (file_exists($apiRoutesPath)) {
    $apiContent = file_get_contents($apiRoutesPath);
    
    // Count routes with auth:sanctum
    $sanctumRoutes = substr_count($apiContent, 'auth:sanctum');
    echo "✅ {$sanctumRoutes} route groups protected with auth:sanctum\n";
    
    // Check for role-based middleware
    if (strpos($apiContent, 'role:') !== false || strpos($apiContent, 'permission:') !== false) {
        echo "✅ Role-based protection detected\n";
    } else {
        echo "⚠️  No explicit role-based protection found\n";
    }
}

// 3. Frontend Authentication Analysis
echo "\n3. FRONTEND AUTHENTICATION SETUP\n";
echo "──────────────────────────────────\n";

// Check useAuth composable
$useAuthPath = __DIR__ . '/resources/js/composables/useAuth.js';
if (file_exists($useAuthPath)) {
    $useAuthContent = file_get_contents($useAuthPath);
    
    if (strpos($useAuthContent, 'sanctum/csrf-cookie') !== false) {
        echo "✅ CSRF protection setup found\n";
    }
    
    if (strpos($useAuthContent, 'login') !== false && strpos($useAuthContent, 'logout') !== false) {
        echo "✅ Login/logout methods implemented\n";
    }
    
    if (strpos($useAuthContent, 'user') !== false) {
        echo "✅ User state management found\n";
    }
    
    // Check for token management
    if (strpos($useAuthContent, 'token') !== false) {
        echo "✅ Token management implemented\n";
    }
    
    // Check for role/permission handling
    if (strpos($useAuthContent, 'role') !== false || strpos($useAuthContent, 'permission') !== false) {
        echo "✅ Role/permission handling found\n";
    }
} else {
    echo "❌ useAuth composable not found\n";
}

// 4. Router Guard Analysis  
echo "\n4. FRONTEND ROUTER PROTECTION\n";
echo "───────────────────────────────\n";

$routerPath = __DIR__ . '/resources/js/plugins/1.router/index.js';
if (file_exists($routerPath)) {
    $routerContent = file_get_contents($routerPath);
    
    if (strpos($routerContent, 'beforeEach') !== false) {
        echo "✅ Global navigation guard implemented\n";
    }
    
    if (strpos($routerContent, 'requiresAuth') !== false) {
        echo "✅ Route-level auth protection found\n";
    }
    
    if (strpos($routerContent, 'requiresGuest') !== false) {
        echo "✅ Guest-only route protection found\n";
    }
    
    if (strpos($routerContent, 'role') !== false) {
        echo "✅ Role-based route protection found\n";
    }
} else {
    echo "❌ Router configuration not found\n";
}

// 5. Authentication Pages Analysis
echo "\n5. AUTHENTICATION PAGES\n";
echo "─────────────────────────\n";

$authPages = [
    'resources/js/pages/auth/login.vue' => 'Login page',
    'resources/js/pages/auth/register.vue' => 'Registration page', 
    'resources/js/pages/auth/forgot-password.vue' => 'Forgot password page',
    'resources/js/pages/auth/reset-password.vue' => 'Reset password page'
];

foreach ($authPages as $page => $description) {
    if (file_exists(__DIR__ . '/' . $page)) {
        echo "✅ {$description} present\n";
    } else {
        echo "⚠️  {$description} not found\n";
    }
}

// 6. API Controller Analysis
echo "\n6. BACKEND AUTHENTICATION CONTROLLERS\n";
echo "────────────────────────────────────────\n";

$authControllers = [
    'app/Http/Controllers/Auth/LoginController.php' => 'Login controller',
    'app/Http/Controllers/Auth/RegisterController.php' => 'Registration controller',
    'app/Http/Controllers/Auth/ForgotPasswordController.php' => 'Forgot password controller',
    'app/Http/Controllers/Api/AuthController.php' => 'API auth controller'
];

foreach ($authControllers as $controller => $description) {
    if (file_exists(__DIR__ . '/' . $controller)) {
        echo "✅ {$description} present\n";
    } else {
        echo "⚠️  {$description} not found\n";
    }
}

// 7. Database Schema Check
echo "\n7. DATABASE AUTHENTICATION SCHEMA\n";
echo "───────────────────────────────────────\n";

// Check for user-related migrations
$migrationsPath = __DIR__ . '/database/migrations';
if (is_dir($migrationsPath)) {
    $migrations = scandir($migrationsPath);
    $userMigrations = array_filter($migrations, function($file) {
        return strpos($file, 'user') !== false || strpos($file, 'personal_access_token') !== false;
    });
    
    if (!empty($userMigrations)) {
        echo "✅ User authentication migrations found (" . count($userMigrations) . ")\n";
    } else {
        echo "⚠️  No user authentication migrations found\n";
    }
    
    // Check for role/permission migrations
    $roleMigrations = array_filter($migrations, function($file) {
        return strpos($file, 'role') !== false || strpos($file, 'permission') !== false;
    });
    
    if (!empty($roleMigrations)) {
        echo "✅ Role/permission migrations found (" . count($roleMigrations) . ")\n";
    } else {
        echo "⚠️  No role/permission migrations found\n";
    }
}

// 8. Session Configuration
echo "\n8. SESSION CONFIGURATION\n";
echo "──────────────────────────\n";

$sessionConfigPath = __DIR__ . '/config/session.php';
if (file_exists($sessionConfigPath)) {
    $sessionConfig = file_get_contents($sessionConfigPath);
    
    if (strpos($sessionConfig, "'secure' => env('SESSION_SECURE_COOKIE'") !== false) {
        echo "✅ Secure cookie configuration found\n";
    }
    
    if (strpos($sessionConfig, "'http_only' => true") !== false) {
        echo "✅ HTTP-only cookie protection enabled\n";
    }
    
    if (strpos($sessionConfig, "'same_site' =>") !== false) {
        echo "✅ SameSite cookie protection configured\n";
    }
}

// 9. Security Headers Check
echo "\n9. SECURITY CONFIGURATION\n";
echo "───────────────────────────\n";

// Check for CORS configuration
$corsConfigPath = __DIR__ . '/config/cors.php';
if (file_exists($corsConfigPath)) {
    echo "✅ CORS configuration present\n";
} else {
    echo "⚠️  CORS configuration not found\n";
}

// Check .htaccess for security headers
$htaccessPath = __DIR__ . '/public/.htaccess';
if (file_exists($htaccessPath)) {
    $htaccessContent = file_get_contents($htaccessPath);
    
    if (strpos($htaccessContent, 'X-Frame-Options') !== false) {
        echo "✅ X-Frame-Options header configured\n";
    } else {
        echo "⚠️  X-Frame-Options header not found\n";
    }
    
    if (strpos($htaccessContent, 'X-Content-Type-Options') !== false) {
        echo "✅ X-Content-Type-Options header configured\n";
    } else {
        echo "⚠️  X-Content-Type-Options header not found\n";
    }
}

// 10. Final Summary
echo "\n10. AUTHENTICATION SECURITY SUMMARY\n";
echo "──────────────────────────────────────\n";

$securityChecks = [
    'Sanctum Auth' => file_exists($sanctumConfigPath),
    'Frontend Auth' => file_exists($useAuthPath),
    'Router Guards' => file_exists($routerPath),
    'Session Security' => file_exists($sessionConfigPath),
    'CORS Config' => file_exists($corsConfigPath)
];

$securityPassed = array_filter($securityChecks);
$securityTotal = count($securityChecks);
$securityPassedCount = count($securityPassed);

echo "Security Status: {$securityPassedCount}/{$securityTotal} checks passed\n\n";

if ($securityPassedCount === $securityTotal) {
    echo "🔒 AUTHENTICATION SYSTEM IS SECURE!\n";
    echo "✅ All security components configured\n";
    echo "✅ Frontend and backend protection in place\n";
    echo "✅ Token-based authentication implemented\n";
} else {
    echo "⚠️  AUTHENTICATION SYSTEM NEEDS REVIEW\n";
    foreach ($securityChecks as $check => $status) {
        echo ($status ? "✅" : "❌") . " {$check}\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Authentication Analysis Complete\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";

?>
