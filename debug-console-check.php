<?php

echo "🔍 DETAILED CONSOLE STATEMENT ANALYSIS\n";
echo "=====================================\n\n";

$filesToCheck = [
    'resources/js/App.vue',
    'resources/js/stores/systemConfig.js',
    'resources/js/utils/crossBrowserUtils.js'
];

foreach ($filesToCheck as $file) {
    echo "📁 Checking: {$file}\n";
    echo str_repeat("─", 50) . "\n";
    
    if (file_exists(__DIR__ . '/' . $file)) {
        $content = file_get_contents(__DIR__ . '/' . $file);
        
        // Split into lines for line number reporting
        $lines = explode("\n", $content);
        
        foreach ($lines as $lineNum => $line) {
            // Look for console statements
            if (preg_match('/console\.(log|warn|error)/', $line)) {
                $actualLineNum = $lineNum + 1;
                echo "Line {$actualLineNum}: " . trim($line) . "\n";
                
                // Check if it's inside an import.meta.env.DEV check
                $context = '';
                for ($i = max(0, $lineNum - 5); $i <= min(count($lines) - 1, $lineNum + 2); $i++) {
                    $context .= $lines[$i] . "\n";
                }
                
                if (strpos($context, 'import.meta.env.DEV') !== false) {
                    echo "   → ✅ PROTECTED (inside DEV check)\n";
                } else {
                    echo "   → ❌ UNPROTECTED (needs DEV guard)\n";
                }
                echo "\n";
            }
        }
    } else {
        echo "❌ File not found\n";
    }
    echo "\n";
}

?>
