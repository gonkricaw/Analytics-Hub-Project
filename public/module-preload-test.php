<?php
// Module-Preload-Test.php - A tool for testing module preloading capabilities

header('Content-Type: text/html; charset=utf-8');

// Configuration
$baseUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$baseUrl .= $_SERVER['HTTP_HOST'];

// Find entry points from the manifest.json
function getEntryPoints() {
    $manifestPath = $_SERVER['DOCUMENT_ROOT'] . '/build/manifest.json';
    $entryPoints = [];
    
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        if ($manifest && is_array($manifest)) {
            foreach ($manifest as $key => $entry) {
                if (isset($entry['file'])) {
                    $entryPoints[] = [
                        'name' => $key,
                        'file' => '/build/' . $entry['file'],
                        'css' => isset($entry['css']) ? array_map(function($css) { 
                            return '/build/' . $css; 
                        }, $entry['css']) : []
                    ];
                }
            }
        }
    }
    
    return $entryPoints;
}

$entryPoints = getEntryPoints();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module Preloading Test</title>
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
        .test-section {
            margin-bottom: 40px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            background-color: #f9f9f9;
        }
        button {
            padding: 8px 16px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px 0;
        }
        button:hover {
            background: #45a049;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            overflow: auto;
            max-height: 300px;
        }
        .module-container {
            margin-top: 20px;
            padding: 10px;
            border: 1px dashed #ccc;
            background-color: #f9f9f9;
        }
        .results-container {
            margin-top: 20px;
        }
        .timing-result {
            margin: 5px 0;
            padding: 8px;
            background-color: #e9f7ef;
            border-radius: 4px;
            font-family: monospace;
        }
        .status {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
        .status-success { background-color: #d4edda; color: #155724; }
        .status-error { background-color: #f8d7da; color: #721c24; }
        .status-pending { background-color: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <h1>Module Preloading Test</h1>
    <p>This tool tests the effectiveness of module preloading in your application.</p>
    
    <div class="test-section">
        <h2>Browser Support Check</h2>
        <div id="browser-support-result">Checking browser support...</div>
        
        <script>
            // Check if the browser supports modulepreload
            const supportsModulepreload = 'supports' in document.createElement('link') && 
                                          document.createElement('link').supports('rel', 'modulepreload');
            
            document.getElementById('browser-support-result').innerHTML = 
                supportsModulepreload ? 
                '<span class="status status-success">Your browser supports modulepreload!</span>' : 
                '<span class="status status-error">Your browser does not support modulepreload.</span>';
        </script>
    </div>
    
    <div class="test-section">
        <h2>Manual Module Loading Test</h2>
        <p>This test will load a JavaScript module with and without preloading to compare performance.</p>
        
        <?php if (count($entryPoints) > 0): ?>
            <h3>Available Entry Points:</h3>
            <?php foreach ($entryPoints as $index => $entry): ?>
            <div>
                <button id="load-btn-<?php echo $index; ?>" onclick="testModuleLoading('<?php echo $entry['file']; ?>', <?php echo $index; ?>)">
                    Test Load: <?php echo htmlspecialchars($entry['name']); ?>
                </button>
                <span id="status-<?php echo $index; ?>" class="status status-pending">Pending</span>
            </div>
            <?php endforeach; ?>
            
            <div class="results-container" id="results-container">
                <h3>Results:</h3>
                <div id="timing-results"></div>
            </div>
        <?php else: ?>
            <p>No entry points found in the manifest. Make sure your build is complete and manifest.json exists.</p>
        <?php endif; ?>
        
        <script>
            // Store timing results
            const results = [];
            
            // Test loading a module with and without preloading
            async function testModuleLoading(modulePath, index) {
                const statusEl = document.getElementById(`status-${index}`);
                const button = document.getElementById(`load-btn-${index}`);
                
                statusEl.className = "status status-pending";
                statusEl.textContent = "Testing...";
                button.disabled = true;
                
                try {
                    // First test - without preloading
                    const start1 = performance.now();
                    const moduleWithoutPreload = await import(modulePath);
                    const end1 = performance.now();
                    const timeWithoutPreload = end1 - start1;
                    
                    // Create a preload link
                    const link = document.createElement('link');
                    link.rel = 'modulepreload';
                    link.href = modulePath;
                    document.head.appendChild(link);
                    
                    // Wait a bit to ensure preloading happens
                    await new Promise(resolve => setTimeout(resolve, 500));
                    
                    // Second test - with preloading
                    const start2 = performance.now();
                    const moduleWithPreload = await import(modulePath);
                    const end2 = performance.now();
                    const timeWithPreload = end2 - start2;
                    
                    // Calculate improvement
                    const improvement = ((timeWithoutPreload - timeWithPreload) / timeWithoutPreload) * 100;
                    
                    // Store result
                    results.push({
                        modulePath,
                        timeWithoutPreload,
                        timeWithPreload,
                        improvement
                    });
                    
                    // Update UI
                    statusEl.className = "status status-success";
                    statusEl.textContent = "Complete";
                    
                    // Add to results
                    const resultDiv = document.createElement('div');
                    resultDiv.className = 'timing-result';
                    resultDiv.innerHTML = `
                        Module: ${modulePath}<br>
                        Without Preload: ${timeWithoutPreload.toFixed(2)}ms<br>
                        With Preload: ${timeWithPreload.toFixed(2)}ms<br>
                        Improvement: ${improvement.toFixed(2)}%
                    `;
                    document.getElementById('timing-results').appendChild(resultDiv);
                    
                } catch (error) {
                    console.error("Error testing module loading:", error);
                    statusEl.className = "status status-error";
                    statusEl.textContent = "Error";
                    
                    const resultDiv = document.createElement('div');
                    resultDiv.className = 'timing-result';
                    resultDiv.style.backgroundColor = '#f8d7da';
                    resultDiv.textContent = `Error loading module ${modulePath}: ${error.message}`;
                    document.getElementById('timing-results').appendChild(resultDiv);
                } finally {
                    button.disabled = false;
                }
            }
        </script>
    </div>
    
    <div class="test-section">
        <h2>HTML Head Inspection</h2>
        <p>Check if your HTML includes proper module preloading tags.</p>
        
        <button onclick="checkHeadTags()">Check &lt;head&gt; Tags</button>
        <div id="head-tags-result"></div>
        
        <script>
            function checkHeadTags() {
                const headEl = document.getElementById('head-tags-result');
                const preloadLinks = document.querySelectorAll('link[rel="modulepreload"]');
                const preloadStyles = document.querySelectorAll('link[rel="preload"][as="style"]');
                const preloadScripts = document.querySelectorAll('link[rel="preload"][as="script"]');
                
                let html = '<h3>Preload Links Found:</h3>';
                
                if (preloadLinks.length === 0 && preloadStyles.length === 0 && preloadScripts.length === 0) {
                    html += '<p><span class="status status-error">No preload links found in the document head!</span></p>';
                } else {
                    html += `<p><span class="status status-success">Found ${preloadLinks.length} modulepreload links, 
                           ${preloadStyles.length} style preloads, and ${preloadScripts.length} script preloads.</span></p>`;
                    
                    html += '<pre>';
                    
                    if (preloadLinks.length > 0) {
                        html += 'ModulePreload Links:\n';
                        preloadLinks.forEach(link => {
                            html += `  <link rel="modulepreload" href="${link.href}">\n`;
                        });
                        html += '\n';
                    }
                    
                    if (preloadStyles.length > 0) {
                        html += 'Style Preload Links:\n';
                        preloadStyles.forEach(link => {
                            html += `  <link rel="preload" as="style" href="${link.href}">\n`;
                        });
                        html += '\n';
                    }
                    
                    if (preloadScripts.length > 0) {
                        html += 'Script Preload Links:\n';
                        preloadScripts.forEach(link => {
                            html += `  <link rel="preload" as="script" href="${link.href}">\n`;
                        });
                    }
                    
                    html += '</pre>';
                }
                
                headEl.innerHTML = html;
            }
        </script>
    </div>

    <div class="test-section">
        <h2>Network Analysis</h2>
        <p>Open your browser's developer tools (F12) and check the Network tab to analyze module loading patterns.</p>
        <p>Key things to look for:</p>
        <ul>
            <li>JavaScript files should have Content-Type: application/javascript</li>
            <li>Modules should load in parallel rather than sequentially</li>
            <li>Preloaded modules should load earlier in the waterfall chart</li>
            <li>Cache headers should be present for optimal performance</li>
        </ul>
    </div>
    
    <footer>
        <p><em>Module Preloading Test - Generated <?php echo date('Y-m-d H:i:s'); ?></em></p>
    </footer>
</body>
</html>
