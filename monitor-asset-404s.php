<?php
/**
 * Asset 404 Error Monitor
 *
 * This script parses the server logs for 404 errors related to asset files
 * and provides alerts and reports about missing assets.
 * 
 * Usage: php monitor-asset-404s.php
 * 
 * @author AI Code Assistant
 * @date May 28, 2025
 */

// Bootstrap Laravel application to use helpers
require_once __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Configuration
$config = [
    'log_path' => storage_path('logs/laravel.log'),
    'error_threshold' => 5, // Number of 404 errors before considering it critical
    'asset_paths' => [
        '/build/assets/',
        '/css/',
        '/js/',
        '/images/',
    ],
    'send_email_alerts' => false,
    'email_recipients' => ['admin@example.com'], // Change to actual email addresses
    'include_recent_lines' => 10, // Number of lines to include around each 404 error
    'max_log_size_to_process' => 10 * 1024 * 1024, // 10MB
];

echo "╔═════════════════════════════════════════════════╗\n";
echo "║         Asset 404 Error Monitor                 ║\n";
echo "╚═════════════════════════════════════════════════╝\n\n";

// Check if log file exists
if (!file_exists($config['log_path'])) {
    echo "❌ ERROR: Log file not found at: {$config['log_path']}\n";
    exit(1);
}

// Get the log file size
$logSize = filesize($config['log_path']);
echo "Log file size: " . formatBytes($logSize) . "\n";

// Initialize error tracking
$asset404Errors = [];
$missedAssets = [];
$criticalAssets = [];

// Read the log file
$logContent = readLogFile($config['log_path'], $config['max_log_size_to_process']);
$logLines = explode("\n", $logContent);
$totalLines = count($logLines);

echo "Processing $totalLines log lines...\n";

// Process each line looking for 404 errors related to assets
foreach ($logLines as $index => $line) {
    if (strpos($line, '404 Not Found') === false) {
        continue; // Skip non-404 lines
    }
    
    foreach ($config['asset_paths'] as $assetPath) {
        if (strpos($line, $assetPath) !== false) {
            // Extract the full URL from the log line
            if (preg_match('/(\/[^\s"\']+\.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2))/', $line, $matches)) {
                $assetUrl = $matches[1];
                
                if (!isset($asset404Errors[$assetUrl])) {
                    $asset404Errors[$assetUrl] = [
                        'count' => 0,
                        'context' => [],
                    ];
                }
                
                $asset404Errors[$assetUrl]['count']++;
                
                // Add context lines around this error
                $startLine = max(0, $index - $config['include_recent_lines']);
                $endLine = min($totalLines - 1, $index + $config['include_recent_lines']);
                
                $contextLines = [];
                for ($i = $startLine; $i <= $endLine; $i++) {
                    if ($i == $index) {
                        $contextLines[] = '>> ' . $logLines[$i];
                    } else {
                        $contextLines[] = '   ' . $logLines[$i];
                    }
                }
                
                $asset404Errors[$assetUrl]['context'][] = $contextLines;
                
                // If this error exceeds the threshold, mark it as critical
                if ($asset404Errors[$assetUrl]['count'] >= $config['error_threshold']) {
                    $criticalAssets[$assetUrl] = $asset404Errors[$assetUrl]['count'];
                }
                
                break; // No need to check other asset paths
            }
        }
    }
}

// Generate report
echo "\n══════════════ 404 ERROR REPORT ══════════════\n";

if (empty($asset404Errors)) {
    echo "✓ No asset 404 errors detected\n";
} else {
    echo "Total unique asset 404 errors: " . count($asset404Errors) . "\n";
    echo "Critical assets (>= {$config['error_threshold']} errors): " . count($criticalAssets) . "\n\n";
    
    // Sort the errors by count (descending)
    uasort($asset404Errors, function($a, $b) {
        return $b['count'] - $a['count'];
    });
    
    // Display critical errors first
    if (!empty($criticalAssets)) {
        echo "CRITICAL ERRORS:\n";
        echo "---------------\n";
        
        foreach ($criticalAssets as $asset => $count) {
            echo "❌ [$count errors] $asset\n";
        }
        
        echo "\n";
    }
    
    // Display top errors
    echo "TOP 404 ERRORS:\n";
    echo "---------------\n";
    $count = 0;
    foreach ($asset404Errors as $asset => $data) {
        echo "• [$data[count] errors] $asset\n";
        
        // Show context for the first occurrence of this error
        if (!empty($data['context'][0])) {
            echo "  Context (first occurrence):\n";
            foreach ($data['context'][0] as $line) {
                if (strpos($line, '>>') === 0) {
                    echo "  \033[31m$line\033[0m\n"; // Red for the error line
                } else {
                    echo "  $line\n";
                }
            }
        }
        
        echo "\n";
        
        $count++;
        if ($count >= 5) break; // Show only top 5 errors in detail
    }
    
    // Generate recommendations
    echo "RECOMMENDATIONS:\n";
    echo "---------------\n";
    
    // Check if the error might be related to a stale asset reference
    foreach ($asset404Errors as $asset => $data) {
        if (strpos($asset, '/build/assets/') !== false && strpos($asset, '.js') !== false) {
            echo "• The JS file '$asset' might be a stale reference. Check if a newer hash exists in manifest.json.\n";
        } else if (strpos($asset, '/build/assets/') !== false && strpos($asset, '.css') !== false) {
            echo "• The CSS file '$asset' might be a stale reference. Check if a newer hash exists in manifest.json.\n";
        }
    }
    
    echo "• Run 'php validate-assets.php' to check if all assets in manifest.json actually exist.\n";
    echo "• Ensure your application is using dynamic asset loading instead of hardcoded paths.\n";
    echo "• Consider adding monitoring to check for 404 errors in real-time.\n";
    
    // If configured, send email alerts for critical errors
    if ($config['send_email_alerts'] && !empty($criticalAssets)) {
        $subject = '[ALERT] ' . count($criticalAssets) . ' critical asset 404 errors detected';
        $message = "Critical 404 errors were detected for the following assets:\n\n";
        
        foreach ($criticalAssets as $asset => $count) {
            $message .= "- [$count errors] $asset\n";
        }
        
        $message .= "\nPlease check the application logs for more details.";
        
        foreach ($config['email_recipients'] as $recipient) {
            mail($recipient, $subject, $message);
        }
        
        echo "\n✓ Email alerts sent to " . implode(', ', $config['email_recipients']) . "\n";
    }
}

// Helper functions
function readLogFile($path, $maxSize) {
    $size = filesize($path);
    
    if ($size > $maxSize) {
        // If file is too large, read only the last $maxSize bytes
        $handle = fopen($path, 'r');
        fseek($handle, -$maxSize, SEEK_END);
        $content = fread($handle, $maxSize);
        fclose($handle);
        
        // Remove the first line which might be incomplete
        $content = substr($content, strpos($content, "\n") + 1);
        
        echo "⚠️ Log file is large, processing only the last " . formatBytes($maxSize) . "...\n";
        return $content;
    } else {
        // Read the entire file
        return file_get_contents($path);
    }
}

function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}
