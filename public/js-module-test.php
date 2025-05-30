<?php
// JS Module Test Tool - For detecting module script loading issues

header('Content-Type: text/html; charset=utf-8');

// Configuration
$baseUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$baseUrl .= $_SERVER['HTTP_HOST'];
$buildPath = '/build';
$assetsPath = $buildPath . '/assets';

// Functions
function testFile($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    $contentType = null;
    if (preg_match('/Content-Type: (.*?)(\r\n|\n)/i', $response, $matches)) {
        $contentType = trim($matches[1]);
    }
    
    curl_close($ch);
    
    return [
        'url' => $url,
        'status' => $httpCode,
        'content_type' => $contentType,
        'valid_js' => ($httpCode == 200 && (
            strpos($contentType, 'application/javascript') !== false || 
            strpos($contentType, 'text/javascript') !== false
        )),
        'valid_css' => ($httpCode == 200 && strpos($contentType, 'text/css') !== false),
        'response' => substr($response, 0, 600) // First 600 chars of the response for inspection
    ];
}

function printTestResult($result) {
    $statusClass = $result['status'] == 200 ? 'success' : 'error';
    $contentClass = ($result['valid_js'] || $result['valid_css']) ? 'success' : 'error';
    
    echo "<div class='test-result'>";
    echo "<h3>" . htmlspecialchars($result['url']) . "</h3>";
    echo "<div class='details'>";
    echo "<p><strong>Status:</strong> <span class='{$statusClass}'>{$result['status']}</span></p>";
    echo "<p><strong>Content Type:</strong> <span class='{$contentClass}'>" . htmlspecialchars($result['content_type'] ?? 'undefined') . "</span></p>";
    
    if ($result['status'] == 200) {
        if ($result['valid_js']) {
            echo "<p class='success'>✓ Valid JavaScript MIME type</p>";
        } elseif ($result['valid_css']) {
            echo "<p class='success'>✓ Valid CSS MIME type</p>";
        } else {
            echo "<p class='error'>✗ Invalid MIME type for JavaScript/CSS</p>";
        }
    }
    
    echo "<details>";
    echo "<summary>Response Headers</summary>";
    echo "<pre>" . htmlspecialchars($result['response']) . "</pre>";
    echo "</details>";
    echo "</div>";
    echo "</div>";
}

// Determine the problematic files from the errors
$problematicFiles = [
    'login-DjK59r0b.js',
    'AppTextField-BfMjutI9.js',
    'VTextField-8qrUc9Rj.js',
    'forwardRefs-D3j0TLhE.js',
    'useFormAccessibility-BIal-seK.js',
    'misc-mask-light-D4w3fdoF.js',
    'auth-v2-login-illustration-light-8SJRNknr.js',
    'VRow-CvKbWEXr.js',
    'VCard-DBIwVFKE.js',
    'createSimpleFunctional-CTh012Nf.js',
    'VCardText-XskGZF3I.js',
    'VForm-D3zfnfj8.js',
    'VAlert-CbV-o8SA.js',
    'VCheckbox-C-8Mfzjk.js',
    'VCheckboxBtn-JNZtca3c.js',
    'VSelectionControl-TWezQneW.js',
    'VDivider-B-ZUub_P.js'
];

// Get main bundles first
$mainFiles = [
    'main-DobhLvk-.js',
    'main-Czk1McQF.css',
];

// Test manifest
$manifestResult = testFile($baseUrl . $buildPath . '/manifest.json');

// Check if we're running the test
$runTests = isset($_GET['run']) && $_GET['run'] === '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module Loading Tests - Indonet Analytics Hub</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #161d31;
            color: #eeedfd;
        }
        
        h1, h2, h3 {
            color: #7367f0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .panel {
            background-color: #283046;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .btn {
            background-color: #7367f0;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin: 10px 0;
        }
        
        .btn:hover {
            background-color: #635ce0;
        }
        
        .test-result {
            background-color: #1e2a3e;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .test-result h3 {
            margin-top: 0;
            margin-bottom: 10px;
        }
        
        .details {
            padding-left: 15px;
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
        
        .section-title {
            border-bottom: 1px solid #3b4253;
            padding-bottom: 10px;
            margin: 30px 0 20px;
        }
        
        pre {
            background-color: #10163a;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 200px;
            overflow-y: auto;
        }
        
        summary {
            cursor: pointer;
            padding: 10px;
            background-color: #10163a;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        details {
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="panel">
            <h1>Module Loading Tests - Indonet Analytics Hub</h1>
            <p>This tool checks for JavaScript module loading issues and diagnoses MIME type problems.</p>
            
            <?php if (!$runTests): ?>
                <a href="?run=1" class="btn">Run Tests</a>
            <?php else: ?>
                <a href="?" class="btn">Reset</a>
            <?php endif; ?>
            
            <a href="/mime-fix-results.php" class="btn">MIME Fix Tool</a>
            <a href="/config-test.php" class="btn">Config Test</a>
            <a href="/" class="btn">Back to App</a>
        </div>
        
        <?php if ($runTests): ?>
            <div class="panel">
                <h2 class="section-title">Manifest Test</h2>
                <?php printTestResult($manifestResult); ?>
                
                <h2 class="section-title">Main Bundle Tests</h2>
                <?php foreach ($mainFiles as $file): ?>
                    <?php printTestResult(testFile($baseUrl . $assetsPath . '/' . $file)); ?>
                <?php endforeach; ?>
                
                <h2 class="section-title">Problematic Module Tests</h2>
                <?php foreach ($problematicFiles as $file): ?>
                    <?php printTestResult(testFile($baseUrl . $buildPath . '/assets/' . $file)); ?>
                <?php endforeach; ?>
            </div>
            
            <div class="panel">
                <h2>Issue Analysis</h2>
                <div id="analysis">
                    <p>Please review the test results above. Common issues include:</p>
                    <ul>
                        <li>JavaScript files being served with incorrect MIME types (should be <code>application/javascript</code>)</li>
                        <li>Server returning HTML instead of JavaScript (usually a 404 error page)</li>
                        <li>Missing files in the build directory</li>
                        <li>URL rewriting issues in .htaccess</li>
                        <li>Vite configuration issues with the base path</li>
                    </ul>
                    
                    <h3>Suggested Fixes:</h3>
                    <ol>
                        <li>Ensure your server is correctly configured to serve .js files with the <code>application/javascript</code> MIME type</li>
                        <li>Check that your .htaccess file is not blocking requests to JavaScript files</li>
                        <li>Verify that Vite's base path is properly configured (should be <code>'/'</code> for most Laravel apps)</li>
                        <li>Try rebuilding your assets with <code>npm run build</code></li>
                        <li>Check if your server has module mod_mime and mod_headers enabled</li>
                    </ol>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
