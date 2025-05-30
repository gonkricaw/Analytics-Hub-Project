<?php
// This file is used to test if assets are being served correctly with proper MIME types

// Set the correct content type for JavaScript files
if (str_ends_with($_SERVER['REQUEST_URI'], '.js')) {
    header('Content-Type: application/javascript');
    echo "console.log('JavaScript asset test loaded successfully with proper MIME type');";
    exit;
} elseif (str_ends_with($_SERVER['REQUEST_URI'], '.css')) {
    header('Content-Type: text/css');
    echo "body { color: green; } /* CSS asset test loaded successfully */";
    exit;
} else {
    // Default HTML response
    header('Content-Type: text/html');
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset MIME Type Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #161D31; /* Dark mode background */
            color: #EEEDFD; /* Light text for dark mode */
        }
        .test-results {
            margin-top: 20px;
            border: 1px solid #ccc;
            padding: 15px;
            background-color: #283046; /* Darker background for card */
        }
        .success {
            color: #28C76F; /* Green */
        }
        .error {
            color: #EA5455; /* Red */
        }
        h1 {
            color: #7367F0; /* Primary color */
        }
        button {
            background-color: #7367F0;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin: 10px 0;
        }
        button:hover {
            background-color: #635CE0;
        }
    </style>
</head>
<body>
    <h1>Asset MIME Type Test</h1>
    <p>This page tests if your server is correctly serving JavaScript and CSS files with the proper MIME types.</p>
    
    <div class="test-results">
        <h2>Test Results:</h2>
        <div id="js-module-result">Testing JavaScript modules...</div>
        <div id="css-result">Testing CSS...</div>
    </div>
    
    <button id="runTests">Run Tests Again</button>

    <script type="module">
        // Test loading a JavaScript module
        async function testJsModule() {
            try {
                const timestamp = new Date().getTime();
                const testUrl = `/asset-test.php?test=js&t=${timestamp}`;
                
                const response = await fetch(testUrl);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                const contentType = response.headers.get('content-type');
                const resultElement = document.getElementById('js-module-result');
                
                if (contentType && contentType.includes('application/javascript')) {
                    resultElement.textContent = `SUCCESS: Server is correctly serving JavaScript files with MIME type: ${contentType}`;
                    resultElement.className = 'success';
                } else {
                    resultElement.textContent = `ERROR: Server is serving JavaScript files with incorrect MIME type: ${contentType || 'none'}`;
                    resultElement.className = 'error';
                }
            } catch (error) {
                document.getElementById('js-module-result').textContent = `ERROR: ${error.message}`;
                document.getElementById('js-module-result').className = 'error';
            }
        }
        
        // Test loading CSS
        async function testCss() {
            try {
                const timestamp = new Date().getTime();
                const testUrl = `/asset-test.php?test=css&t=${timestamp}`;
                
                const response = await fetch(testUrl);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                const contentType = response.headers.get('content-type');
                const resultElement = document.getElementById('css-result');
                
                if (contentType && contentType.includes('text/css')) {
                    resultElement.textContent = `SUCCESS: Server is correctly serving CSS files with MIME type: ${contentType}`;
                    resultElement.className = 'success';
                } else {
                    resultElement.textContent = `ERROR: Server is serving CSS files with incorrect MIME type: ${contentType || 'none'}`;
                    resultElement.className = 'error';
                }
            } catch (error) {
                document.getElementById('css-result').textContent = `ERROR: ${error.message}`;
                document.getElementById('css-result').className = 'error';
            }
        }
        
        // Run the tests
        async function runTests() {
            await testJsModule();
            await testCss();
        }
        
        // Run tests when page loads
        runTests();
        
        // Run tests again when button is clicked
        document.getElementById('runTests').addEventListener('click', runTests);
    </script>
</body>
</html>
