<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $content->title ?? 'Embedded Content' }} - Indonet Analytics Hub</title>
    
    <!-- Security headers for iframe embedding -->
    <meta http-equiv="Content-Security-Policy" content="default-src 'self' 'unsafe-inline' 'unsafe-eval' data: blob: *; frame-src *;">
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
        }
        
        .embed-container {
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .embed-header {
            background-color: #fff;
            padding: 12px 20px;
            border-bottom: 1px solid #e9ecef;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .embed-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .embed-info {
            font-size: 12px;
            color: #6c757d;
            margin-top: 4px;
        }
        
        .embed-content {
            flex: 1;
            border: none;
            background-color: #fff;
        }
        
        .loading-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1001;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007bff;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .error-message {
            text-align: center;
            padding: 40px;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="embed-container">
        @if($content->title)
        <div class="embed-header">
            <h1 class="embed-title">{{ $content->title }}</h1>
            <div class="embed-info">
                Embedded content | Accessed by: {{ $user->name }}
            </div>
        </div>
        @endif
        
        <div id="loading" class="loading-overlay">
            <div class="spinner"></div>
            <div style="margin-top: 10px; text-align: center;">Loading content...</div>
        </div>
        
        <iframe 
            id="embed-frame"
            class="embed-content" 
            src="{{ $url }}"
            frameborder="0"
            allowfullscreen
            onload="hideLoading()"
            onerror="showError()"
            style="display: none;">
        </iframe>
        
        <div id="error" class="error-message" style="display: none;">
            <h3>Unable to load content</h3>
            <p>The embedded content could not be loaded. Please contact your administrator.</p>
        </div>
    </div>

    <script>
        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('embed-frame').style.display = 'block';
        }
        
        function showError() {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('error').style.display = 'block';
        }
        
        // Timeout fallback
        setTimeout(function() {
            if (document.getElementById('loading').style.display !== 'none') {
                hideLoading();
            }
        }, 10000);
    </script>
</body>
</html>
