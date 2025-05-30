<?php
// Cache-Test.php - A tool for testing caching, MIME types and HTTP headers

header('Content-Type: text/html; charset=utf-8');

// Configuration
$baseUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$baseUrl .= $_SERVER['HTTP_HOST'];
$buildPath = '/build';

// Functions
function testAssetHeaders($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Extract headers
    $headers = [];
    $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
    foreach (explode("\r\n", $header_text) as $i => $line) {
        if ($i === 0) {
            $headers['http_code'] = $line;
        } else {
            list($key, $value) = explode(': ', $line, 2);
            $headers[$key] = $value;
        }
    }
    
    return [
        'url' => $url,
        'status_code' => $httpCode,
        'content_type' => $headers['Content-Type'] ?? 'Not set',
        'cache_control' => $headers['Cache-Control'] ?? 'Not set',
        'expires' => $headers['Expires'] ?? 'Not set',
        'etag' => $headers['ETag'] ?? 'Not set',
        'last_modified' => $headers['Last-Modified'] ?? 'Not set',
        'cors' => $headers['Access-Control-Allow-Origin'] ?? 'Not set',
        'all_headers' => $headers,
    ];
}

// Find asset files in the build directory
function findAssetFiles($directory) {
    $assets = [];
    
    // Load the manifest.json if it exists
    $manifestPath = $_SERVER['DOCUMENT_ROOT'] . '/build/manifest.json';
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if ($manifest && is_array($manifest)) {
            foreach ($manifest as $entry) {
                if (isset($entry['file'])) {
                    $assets[] = '/build/' . $entry['file'];
                }
                if (isset($entry['css']) && is_array($entry['css'])) {
                    foreach ($entry['css'] as $css) {
                        $assets[] = '/build/' . $css;
                    }
                }
            }
        }
    }
    
    // If manifest didn't work, try direct directory scanning (fallback)
    if (count($assets) === 0) {
        $assets = scanDirectory($directory);
    }
    
    // Limit to 10 assets to avoid too much output
    return array_slice($assets, 0, 10);
}

function scanDirectory($dir) {
    $result = [];
    $root = $_SERVER['DOCUMENT_ROOT'] . $dir;
    
    if (!is_dir($root)) {
        return $result;
    }
    
    $files = scandir($root);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $path = $root . '/' . $file;
        
        if (is_dir($path)) {
            $subDirAssets = scanDirectory($dir . '/' . $file);
            $result = array_merge($result, $subDirAssets);
        } else {
            // Only include JS, CSS, images
            if (preg_match('/\.(js|css|png|jpg|jpeg|svg|woff|woff2)$/i', $file)) {
                $result[] = $dir . '/' . $file;
            }
        }
    }
    
    return $result;
}

// Get assets and test them
$assets = findAssetFiles('/build');
$results = [];

foreach ($assets as $asset) {
    $results[] = testAssetHeaders($baseUrl . $asset);
}

// Group results by type
$jsResults = array_filter($results, function($r) { 
    return strpos($r['content_type'], 'javascript') !== false; 
});

$cssResults = array_filter($results, function($r) { 
    return strpos($r['content_type'], 'css') !== false; 
});

$otherResults = array_filter($results, function($r) { 
    return strpos($r['content_type'], 'javascript') === false && 
           strpos($r['content_type'], 'css') === false; 
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cache and Headers Test</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            color: #444;
        }
        h2 {
            margin-top: 30px;
            color: #666;
        }
        .result {
            margin-bottom: 40px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            background-color: #f9f9f9;
        }
        .result h3 {
            margin-top: 0;
            word-break: break-all;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .success { color: green; }
        .warning { color: orange; }
        .error { color: red; }
        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            margin-right: 5px;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <h1>Cache and HTTP Headers Test</h1>
    <p>This tool checks if your assets are being served with the correct MIME types and caching headers.</p>
    
    <div class="summary">
        <h2>Summary</h2>
        <ul>
            <li>JavaScript files tested: <?php echo count($jsResults); ?></li>
            <li>CSS files tested: <?php echo count($cssResults); ?></li>
            <li>Other assets tested: <?php echo count($otherResults); ?></li>
        </ul>
    </div>
    
    <h2>JavaScript Files</h2>
    <?php foreach ($jsResults as $result): ?>
    <div class="result">
        <h3><?php echo htmlspecialchars($result['url']); ?></h3>
        <table>
            <tr>
                <th>Property</th>
                <th>Value</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Status Code</td>
                <td><?php echo $result['status_code']; ?></td>
                <td>
                    <?php if ($result['status_code'] == 200): ?>
                        <span class="badge badge-success">OK</span>
                    <?php else: ?>
                        <span class="badge badge-error">Error</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>Content-Type</td>
                <td><?php echo htmlspecialchars($result['content_type']); ?></td>
                <td>
                    <?php if (strpos($result['content_type'], 'application/javascript') !== false): ?>
                        <span class="badge badge-success">OK</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Incorrect</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>Cache-Control</td>
                <td><?php echo htmlspecialchars($result['cache_control']); ?></td>
                <td>
                    <?php if (strpos($result['cache_control'], 'max-age=') !== false): ?>
                        <span class="badge badge-success">OK</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Missing</span>
                    <?php endif; ?>
                    
                    <?php if (strpos($result['cache_control'], 'immutable') !== false): ?>
                        <span class="badge badge-success">Immutable</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>CORS Headers</td>
                <td><?php echo htmlspecialchars($result['cors']); ?></td>
                <td>
                    <?php if ($result['cors'] != 'Not set'): ?>
                        <span class="badge badge-success">OK</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Missing</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
    <?php endforeach; ?>
    
    <h2>CSS Files</h2>
    <?php foreach ($cssResults as $result): ?>
    <div class="result">
        <h3><?php echo htmlspecialchars($result['url']); ?></h3>
        <table>
            <tr>
                <th>Property</th>
                <th>Value</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Status Code</td>
                <td><?php echo $result['status_code']; ?></td>
                <td>
                    <?php if ($result['status_code'] == 200): ?>
                        <span class="badge badge-success">OK</span>
                    <?php else: ?>
                        <span class="badge badge-error">Error</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>Content-Type</td>
                <td><?php echo htmlspecialchars($result['content_type']); ?></td>
                <td>
                    <?php if (strpos($result['content_type'], 'text/css') !== false): ?>
                        <span class="badge badge-success">OK</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Incorrect</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>Cache-Control</td>
                <td><?php echo htmlspecialchars($result['cache_control']); ?></td>
                <td>
                    <?php if (strpos($result['cache_control'], 'max-age=') !== false): ?>
                        <span class="badge badge-success">OK</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Missing</span>
                    <?php endif; ?>
                    
                    <?php if (strpos($result['cache_control'], 'immutable') !== false): ?>
                        <span class="badge badge-success">Immutable</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
    <?php endforeach; ?>
    
    <h2>Other Assets</h2>
    <?php foreach ($otherResults as $result): ?>
    <div class="result">
        <h3><?php echo htmlspecialchars($result['url']); ?></h3>
        <table>
            <tr>
                <th>Property</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Status Code</td>
                <td><?php echo $result['status_code']; ?></td>
            </tr>
            <tr>
                <td>Content-Type</td>
                <td><?php echo htmlspecialchars($result['content_type']); ?></td>
            </tr>
            <tr>
                <td>Cache-Control</td>
                <td><?php echo htmlspecialchars($result['cache_control']); ?></td>
            </tr>
        </table>
    </div>
    <?php endforeach; ?>

    <footer>
        <p><em>Cache Test Tool - Generated <?php echo date('Y-m-d H:i:s'); ?></em></p>
    </footer>
</body>
</html>
