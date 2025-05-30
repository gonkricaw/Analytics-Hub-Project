<?php
// This file is used to test and fix MIME type issues

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Function to check if a file exists and is readable
function checkFile($path) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $path;
    $exists = file_exists($fullPath);
    $readable = is_readable($fullPath);
    $fileSize = $exists ? filesize($fullPath) : 0;
    
    return [
        'path' => $path,
        'fullPath' => $fullPath,
        'exists' => $exists,
        'readable' => $readable,
        'fileSize' => $fileSize,
        'status' => $exists && $readable ? 'OK' : 'MISSING'
    ];
}

// Function to fix common .htaccess issues
function fixHtaccess() {
    $htaccessPath = $_SERVER['DOCUMENT_ROOT'] . '/.htaccess';
    $modified = false;
    
    if (file_exists($htaccessPath) && is_writable($htaccessPath)) {
        $content = file_get_contents($htaccessPath);
        
        // Check if we need to add JavaScript MIME type declarations
        if (!str_contains($content, 'AddType application/javascript')) {
            $mimeTypeSection = "
# MIME Types for JavaScript and CSS
<IfModule mod_mime.c>
    AddType application/javascript .js
    AddType application/javascript .mjs
    AddType text/css .css
    AddType application/json .json
    
    # Force JavaScript modules to have correct MIME type
    <FilesMatch \"\\.js$\">
        Header set Content-Type \"application/javascript\"
    </FilesMatch>
</IfModule>
";
            $content .= $mimeTypeSection;
            $modified = true;
        }
        
        // Check if we need to remove problematic rules
        if (str_contains($content, 'RewriteCond %{REQUEST_URI} \.(js|css|woff|woff2|ttf|eot|svg|png|jpg|jpeg|gif|ico)$ [NC]')) {
            $content = str_replace(
                "    # Return 404 for missing static assets (js, css, fonts, images) instead of serving Laravel
    RewriteCond %{REQUEST_URI} \.(js|css|woff|woff2|ttf|eot|svg|png|jpg|jpeg|gif|ico)$ [NC]
    RewriteRule .* - [R=404,L]",
                
                "    # Handle build directory specifically - forward to Laravel if file not found
    RewriteCond %{REQUEST_URI} ^/build/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule .* index.php [QSA,L]",
                
                $content
            );
            $modified = true;
        }
        
        // Write changes if modifications were made
        if ($modified) {
            file_put_contents($htaccessPath, $content);
            return ['modified' => true, 'message' => '.htaccess file has been updated with MIME type fixes'];
        }
        
        return ['modified' => false, 'message' => '.htaccess file already has the correct configuration'];
    }
    
    return ['modified' => false, 'message' => '.htaccess file could not be modified (not found or not writable)'];
}

// Check for vite manifest
function checkViteManifest() {
    $manifestPath = $_SERVER['DOCUMENT_ROOT'] . '/build/manifest.json';
    
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        return [
            'exists' => true,
            'entryCount' => count($manifest),
            'entries' => array_keys($manifest)
        ];
    }
    
    return [
        'exists' => false,
        'message' => 'Vite manifest not found. The application may not be built correctly.'
    ];
}

// Get server information
$serverInfo = [
    'server' => $_SERVER['SERVER_SOFTWARE'],
    'php' => phpversion(),
    'document_root' => $_SERVER['DOCUMENT_ROOT'],
    'script_filename' => $_SERVER['SCRIPT_FILENAME'],
];

// Check key files
$fileChecks = [
    checkFile('/index.php'),
    checkFile('/.htaccess'),
    checkFile('/build/manifest.json'),
    checkFile('/build/assets/index-[a-zA-Z0-9]*.js'), // Using a pattern
];

// Check for the Vite manifest
$manifestCheck = checkViteManifest();

// Fix .htaccess if requested
$htaccessFixed = ['modified' => false, 'message' => 'No fixes applied'];
if (isset($_GET['fix']) && $_GET['fix'] === 'htaccess') {
    $htaccessFixed = fixHtaccess();
}

// Create results
$results = [
    'serverInfo' => $serverInfo,
    'fileChecks' => $fileChecks,
    'manifestCheck' => $manifestCheck,
    'htaccessFixed' => $htaccessFixed
];

// Output as HTML or JSON
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');
    echo json_encode($results, JSON_PRETTY_PRINT);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIME Fix Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #161d31;
            color: #eeedfd;
        }
        .section {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 4px;
            background-color: #283046;
        }
        h1, h2 {
            color: #7367f0;
        }
        .success {
            color: #28c76f;
        }
        .error {
            color: #ea5455;
        }
        .warning {
            color: #ff9f43;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #404656;
        }
        pre {
            background-color: #161d31;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        button, .button {
            background-color: #7367f0;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            display: inline-block;
            text-decoration: none;
            margin: 5px 0;
        }
        button:hover, .button:hover {
            background-color: #635ce0;
        }
    </style>
</head>
<body>
    <h1>MIME Type Fix Results</h1>
    
    <div class="section">
        <h2>Server Information</h2>
        <table>
            <tr>
                <th>Server Software</th>
                <td><?php echo htmlspecialchars($serverInfo['server']); ?></td>
            </tr>
            <tr>
                <th>PHP Version</th>
                <td><?php echo htmlspecialchars($serverInfo['php']); ?></td>
            </tr>
            <tr>
                <th>Document Root</th>
                <td><?php echo htmlspecialchars($serverInfo['document_root']); ?></td>
            </tr>
        </table>
    </div>
    
    <div class="section">
        <h2>File Checks</h2>
        <table>
            <tr>
                <th>File</th>
                <th>Status</th>
                <th>Size</th>
            </tr>
            <?php foreach ($fileChecks as $check): ?>
                <tr>
                    <td><?php echo htmlspecialchars($check['path']); ?></td>
                    <td class="<?php echo $check['status'] === 'OK' ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($check['status']); ?>
                    </td>
                    <td><?php echo $check['fileSize']; ?> bytes</td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="section">
        <h2>Vite Manifest</h2>
        <?php if ($manifestCheck['exists']): ?>
            <p class="success">Manifest found with <?php echo $manifestCheck['entryCount']; ?> entries</p>
            <details>
                <summary>Show entries</summary>
                <pre><?php echo htmlspecialchars(implode("\n", $manifestCheck['entries'])); ?></pre>
            </details>
        <?php else: ?>
            <p class="error"><?php echo htmlspecialchars($manifestCheck['message']); ?></p>
            <p>Try running: <code>npm run build</code></p>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>.htaccess Fix</h2>
        <?php if ($htaccessFixed['modified']): ?>
            <p class="success"><?php echo htmlspecialchars($htaccessFixed['message']); ?></p>
            <p>Please refresh the page to see if the MIME type issues are resolved.</p>
        <?php else: ?>
            <p><?php echo htmlspecialchars($htaccessFixed['message']); ?></p>
            <a href="?fix=htaccess" class="button">Apply .htaccess Fix</a>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>Test Links</h2>
        <p><a href="/asset-test.php" class="button">Test Asset Loading</a></p>
        <p><a href="/" class="button">Back to Homepage</a></p>
    </div>
    
    <script>
        // Simple script to check if JavaScript is working
        document.addEventListener('DOMContentLoaded', function() {
            console.log('MIME fix page loaded successfully');
        });
    </script>
</body>
</html>
