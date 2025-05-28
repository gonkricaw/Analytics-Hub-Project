<?php
/**
 * Complete Fix Diagnostic Script for Indonet Analytics Hub
 * This script diagnoses and fixes all remaining issues with API and file loading
 */

class CompleteDiagnostic {
    private $results = [];
    private $fixes = [];
    
    public function run() {
        echo "<h1>🔍 Complete Diagnostic for Indonet Analytics Hub</h1>\n";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .success { color: #28a745; background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0; }
            .error { color: #dc3545; background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0; }
            .warning { color: #856404; background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; border-radius: 4px; margin: 10px 0; }
            .info { color: #0c5460; background: #d1ecf1; padding: 10px; border: 1px solid #bee5eb; border-radius: 4px; margin: 10px 0; }
            .fix { background: #e2e3e5; padding: 10px; border-left: 4px solid #007bff; margin: 10px 0; }
            pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
            .test-section { border: 1px solid #dee2e6; margin: 20px 0; padding: 20px; border-radius: 8px; }
        </style>";
        
        $this->checkEnvironment();
        $this->testAPIEndpoints();
        $this->checkFileStructure();
        $this->checkViteBuilds();
        $this->checkURLRouting();
        $this->generateFixActions();
        
        echo "<h2>🎯 Summary and Next Steps</h2>";
        $this->showSummary();
    }
    
    private function checkEnvironment() {
        echo "<div class='test-section'>";
        echo "<h2>🌍 Environment Check</h2>";
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
            echo "<div class='success'>✅ PHP Version: " . PHP_VERSION . " (Compatible)</div>";
        } else {
            echo "<div class='error'>❌ PHP Version: " . PHP_VERSION . " (Needs 8.0+)</div>";
            $this->fixes[] = "Upgrade PHP to version 8.0 or higher";
        }
        
        // Check Laravel
        $artisan = file_exists(__DIR__ . '/artisan');
        if ($artisan) {
            echo "<div class='success'>✅ Laravel project detected</div>";
        } else {
            echo "<div class='error'>❌ Laravel artisan not found</div>";
        }
        
        // Check .env file
        $env = file_exists(__DIR__ . '/.env');
        if ($env) {
            echo "<div class='success'>✅ .env file exists</div>";
        } else {
            echo "<div class='error'>❌ .env file missing</div>";
            $this->fixes[] = "Create .env file from .env.example";
        }
        
        // Check storage permissions
        $storage = __DIR__ . '/storage';
        if (is_dir($storage) && is_writable($storage)) {
            echo "<div class='success'>✅ Storage directory writable</div>";
        } else {
            echo "<div class='error'>❌ Storage directory not writable</div>";
            $this->fixes[] = "Set proper permissions: chmod -R 775 storage";
        }
        
        echo "</div>";
    }
    
    private function testAPIEndpoints() {
        echo "<div class='test-section'>";
        echo "<h2>🔗 API Endpoints Test</h2>";
        
        $baseUrl = $this->getBaseUrl();
        $endpoints = [
            'System Config Public' => '/api/system-configurations/public',
            'Login Endpoint' => '/api/login',
            'Dashboard Endpoint' => '/api/dashboard',
            'Direct System Config' => '/system-configurations/public'
        ];
        
        foreach ($endpoints as $name => $endpoint) {
            $url = $baseUrl . $endpoint;
            echo "<h4>Testing: $name</h4>";
            echo "<div class='info'>URL: $url</div>";
            
            $result = $this->makeHttpRequest($url);
            
            if ($result['success']) {
                echo "<div class='success'>✅ Status: {$result['status']} - Response received</div>";
                if ($result['data']) {
                    echo "<pre>" . json_encode($result['data'], JSON_PRETTY_PRINT) . "</pre>";
                }
            } else {
                echo "<div class='error'>❌ Failed: {$result['error']}</div>";
                if ($result['status'] == 404) {
                    $this->fixes[] = "Fix 404 error for endpoint: $endpoint";
                }
            }
        }
        
        echo "</div>";
    }
    
    private function checkFileStructure() {
        echo "<div class='test-section'>";
        echo "<h2>📁 File Structure Check</h2>";
        
        $criticalFiles = [
            'public/build/manifest.json' => 'Vite manifest file',
            'public/js/admin.js' => 'Admin JavaScript file',
            'public/js/charts.js' => 'Charts JavaScript file', 
            'public/js/ui-extended.js' => 'UI Extended JavaScript file',
            'resources/js/main.js' => 'Main Vue entry point',
            'resources/js/composables/useApiClient.js' => 'API Client composable',
            'resources/js/composables/useSystemConfiguration.js' => 'System Config composable',
            'resources/js/stores/systemConfig.js' => 'System Config store',
            'routes/api.php' => 'API routes file',
            'routes/web.php' => 'Web routes file'
        ];
        
        foreach ($criticalFiles as $file => $description) {
            $fullPath = __DIR__ . '/' . $file;
            if (file_exists($fullPath)) {
                echo "<div class='success'>✅ $description: $file</div>";
                
                // Check file size for JS files to ensure they're not empty
                if (strpos($file, '.js') !== false) {
                    $size = filesize($fullPath);
                    if ($size < 100) {
                        echo "<div class='warning'>⚠️ File is very small ($size bytes) - might be empty or placeholder</div>";
                        $this->fixes[] = "Check content of $file - appears to be empty or minimal";
                    }
                }
            } else {
                echo "<div class='error'>❌ Missing: $description ($file)</div>";
                $this->fixes[] = "Create missing file: $file";
            }
        }
        
        echo "</div>";
    }
    
    private function checkViteBuilds() {
        echo "<div class='test-section'>";
        echo "<h2>⚡ Vite Build Check</h2>";
        
        $buildDir = __DIR__ . '/public/build';
        if (!is_dir($buildDir)) {
            echo "<div class='error'>❌ Build directory missing: /public/build</div>";
            $this->fixes[] = "Run: npm run build";
            echo "</div>";
            return;
        }
        
        $manifestPath = $buildDir . '/manifest.json';
        if (!file_exists($manifestPath)) {
            echo "<div class='error'>❌ Vite manifest missing</div>";
            $this->fixes[] = "Run: npm run build to generate manifest";
            echo "</div>";
            return;
        }
        
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (!$manifest) {
            echo "<div class='error'>❌ Invalid manifest.json</div>";
            $this->fixes[] = "Rebuild with: npm run build";
            echo "</div>";
            return;
        }
        
        echo "<div class='success'>✅ Vite manifest found with " . count($manifest) . " entries</div>";
        
        // Check for main entry point
        $mainEntry = null;
        foreach ($manifest as $key => $entry) {
            if (strpos($key, 'main.js') !== false || strpos($key, 'resources/js/main.js') !== false) {
                $mainEntry = $entry;
                break;
            }
        }
        
        if ($mainEntry) {
            echo "<div class='success'>✅ Main entry point found in manifest</div>";
            $assetPath = $buildDir . '/' . $mainEntry['file'];
            if (file_exists($assetPath)) {
                echo "<div class='success'>✅ Main asset file exists: {$mainEntry['file']}</div>";
            } else {
                echo "<div class='error'>❌ Main asset file missing: {$mainEntry['file']}</div>";
                $this->fixes[] = "Rebuild assets: npm run build";
            }
        } else {
            echo "<div class='error'>❌ Main entry point not found in manifest</div>";
            $this->fixes[] = "Check vite.config.js entry point configuration";
        }
        
        echo "</div>";
    }
    
    private function checkURLRouting() {
        echo "<div class='test-section'>";
        echo "<h2>🛣️ URL Routing Check</h2>";
        
        $baseUrl = $this->getBaseUrl();
        $routes = [
            'Home' => '/',
            'Login' => '/login',
            'Build Login' => '/build/login',
            'API Login' => '/api/login'
        ];
        
        foreach ($routes as $name => $route) {
            $url = $baseUrl . $route;
            echo "<h4>Testing Route: $name</h4>";
            echo "<div class='info'>URL: $url</div>";
            
            $result = $this->makeHttpRequest($url);
            
            if ($result['success'] && $result['status'] < 400) {
                echo "<div class='success'>✅ Route accessible (Status: {$result['status']})</div>";
            } else {
                echo "<div class='error'>❌ Route failed (Status: {$result['status']}) - {$result['error']}</div>";
                if ($route === '/build/login') {
                    $this->fixes[] = "Fix SPA routing - /build/login should redirect to /login";
                }
            }
        }
        
        echo "</div>";
    }
    
    private function generateFixActions() {
        echo "<div class='test-section'>";
        echo "<h2>🔧 Automated Fixes</h2>";
        
        // Create missing JS files
        $this->createMissingJSFiles();
        
        // Fix API URL configuration
        $this->fixApiUrlConfiguration();
        
        // Generate .htaccess fixes
        $this->generateHtaccessFix();
        
        echo "</div>";
    }
    
    private function createMissingJSFiles() {
        echo "<h3>Creating Missing JavaScript Files</h3>";
        
        $jsDir = __DIR__ . '/public/js';
        if (!is_dir($jsDir)) {
            mkdir($jsDir, 0755, true);
            echo "<div class='success'>✅ Created /public/js directory</div>";
        }
        
        $jsFiles = [
            'admin.js' => '// Admin functionality placeholder
console.log("Admin.js loaded");
// Add your admin-specific JavaScript here',
            
            'charts.js' => '// Charts functionality placeholder
console.log("Charts.js loaded"); 
// Add your chart libraries and configurations here',
            
            'ui-extended.js' => '// UI Extended functionality placeholder
console.log("UI-Extended.js loaded");
// Add your extended UI components here'
        ];
        
        foreach ($jsFiles as $filename => $content) {
            $filePath = $jsDir . '/' . $filename;
            if (!file_exists($filePath) || filesize($filePath) < 50) {
                file_put_contents($filePath, $content);
                echo "<div class='success'>✅ Created/Updated: $filename</div>";
            }
        }
    }
    
    private function fixApiUrlConfiguration() {
        echo "<h3>API Configuration Fix</h3>";
        
        $configFix = "
// API URL Fix for useApiClient.js
// Add this to your useApiClient.js file around line 127:

const normalizeApiUrl = (url) => {
    // Remove duplicate /api/ prefixes
    if (url.startsWith('/api/api/')) {
        url = url.replace('/api/api/', '/api/');
        console.log('Fixed duplicate API prefix:', url);
    }
    
    // Ensure single /api prefix for non-absolute URLs
    if (!url.startsWith('http') && !url.startsWith('/api/')) {
        url = '/api' + (url.startsWith('/') ? '' : '/') + url;
    }
    
    return url;
};

// Update your apiCall function to use normalizeApiUrl:
const apiCall = async (url, options = {}) => {
    // ... existing code ...
    
    let normalizedUrl = normalizeApiUrl(url);
    
    const config = {
        method: method.toLowerCase(),
        url: normalizedUrl,
        // ... rest of config
    };
    
    // ... rest of function
};
";
        
        echo "<div class='fix'>";
        echo "<h4>API Client Fix</h4>";
        echo "<pre>$configFix</pre>";
        echo "</div>";
    }
    
    private function generateHtaccessFix() {
        echo "<h3>.htaccess Configuration</h3>";
        
        $htaccessContent = "
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Handle Angular and React Router 
    RewriteCond %{REQUEST_URI} !^/api
    RewriteCond %{REQUEST_URI} !^/build
    RewriteCond %{REQUEST_URI} !^/storage
    RewriteCond %{REQUEST_URI} !^/images
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.+)$ /index.php [L]
    
    # Fix for /build/login to redirect to /login
    RewriteRule ^build/login$ /login [R=302,L]
    
    # API route handling
    RewriteCond %{REQUEST_URI} ^/api
    RewriteRule ^(.*)$ /index.php [L]
</IfModule>

# CORS Headers for API
<IfModule mod_headers.c>
    Header always set Access-Control-Allow-Origin \"*\"
    Header always set Access-Control-Allow-Methods \"GET, POST, PUT, DELETE, OPTIONS\"
    Header always set Access-Control-Allow-Headers \"Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN\"
</IfModule>

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection \"1; mode=block\"
</IfModule>

# MIME Types for JavaScript and CSS
<IfModule mod_mime.c>
    AddType application/javascript .js
    AddType text/css .css
    AddType application/json .json
</IfModule>

# Gzip Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>

# Cache Control
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css \"access plus 1 week\"
    ExpiresByType application/javascript \"access plus 1 week\"
    ExpiresByType image/png \"access plus 1 month\"
    ExpiresByType image/jpg \"access plus 1 month\"
    ExpiresByType image/jpeg \"access plus 1 month\"
    ExpiresByType image/gif \"access plus 1 month\"
    ExpiresByType image/svg+xml \"access plus 1 month\"
</IfModule>
";
        
        $htaccessPath = __DIR__ . '/public/.htaccess';
        file_put_contents($htaccessPath, $htaccessContent);
        echo "<div class='success'>✅ Updated .htaccess file with proper configurations</div>";
    }
    
    private function showSummary() {
        echo "<div class='info'>";
        echo "<h3>🎯 Issues Found and Actions Needed:</h3>";
        echo "<ol>";
        foreach ($this->fixes as $fix) {
            echo "<li>$fix</li>";
        }
        echo "</ol>";
        echo "</div>";
        
        echo "<div class='warning'>";
        echo "<h3>⚡ Quick Fix Commands:</h3>";
        echo "<pre>";
        echo "# 1. Clear Laravel cache\n";
        echo "php artisan cache:clear\n";
        echo "php artisan config:clear\n";
        echo "php artisan route:clear\n\n";
        
        echo "# 2. Rebuild frontend assets\n";
        echo "npm install\n";
        echo "npm run build\n\n";
        
        echo "# 3. Set proper permissions\n";
        echo "chmod -R 775 storage\n";
        echo "chmod -R 775 bootstrap/cache\n\n";
        
        echo "# 4. Start Laravel server\n";
        echo "php artisan serve --host=127.0.0.1 --port=8000\n";
        echo "</pre>";
        echo "</div>";
        
        echo "<div class='success'>";
        echo "<h3>✅ Next Steps:</h3>";
        echo "<ol>";
        echo "<li>Run the quick fix commands above</li>";
        echo "<li>Test the login page at: <a href='http://localhost:8000/login' target='_blank'>http://localhost:8000/login</a></li>";
        echo "<li>Check browser console for any remaining errors</li>";
        echo "<li>Verify API endpoints are working</li>";
        echo "</ol>";
        echo "</div>";
    }
    
    private function getBaseUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        return $protocol . $host;
    }
    
    private function makeHttpRequest($url) {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 10,
                'ignore_errors' => true
            ]
        ]);
        
        $result = @file_get_contents($url, false, $context);
        $status = 0;
        
        if (isset($http_response_header)) {
            preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches);
            $status = (int)($matches[1] ?? 0);
        }
        
        if ($result !== false) {
            $data = json_decode($result, true);
            return [
                'success' => true,
                'status' => $status,
                'data' => $data ?: substr($result, 0, 200),
                'error' => null
            ];
        } else {
            return [
                'success' => false,
                'status' => $status,
                'data' => null,
                'error' => error_get_last()['message'] ?? 'Unknown error'
            ];
        }
    }
}

// Run the diagnostic
$diagnostic = new CompleteDiagnostic();
$diagnostic->run();
?>
