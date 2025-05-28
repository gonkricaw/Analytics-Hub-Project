<?php
echo "🔍 Checking Vite Manifest for Main Entry Points\n";
echo "=================================================\n";

$manifestPath = 'public/build/manifest.json';
if (!file_exists($manifestPath)) {
    echo "❌ Manifest file not found: $manifestPath\n";
    exit(1);
}

$manifest = json_decode(file_get_contents($manifestPath), true);
if (!$manifest) {
    echo "❌ Failed to parse manifest JSON\n";
    exit(1);
}

echo "📋 Found " . count($manifest) . " entries in manifest\n\n";

// Look for main entry points
$mainEntries = [];
foreach ($manifest as $key => $entry) {
    if (isset($entry['isEntry']) && $entry['isEntry'] === true) {
        $mainEntries[] = [
            'key' => $key,
            'file' => $entry['file'],
            'css' => $entry['css'] ?? []
        ];
    }
}

if (empty($mainEntries)) {
    echo "⚠️  No main entry points found with 'isEntry' flag\n";
    echo "🔍 Looking for likely main files...\n\n";
    
    // Look for files that might be main entries
    foreach ($manifest as $key => $entry) {
        if (strpos($key, 'main') !== false || strpos($key, 'app') !== false || strpos($key, 'index') !== false) {
            echo "🎯 Potential main file: $key\n";
            echo "   File: " . $entry['file'] . "\n";
            if (isset($entry['css'])) {
                echo "   CSS: " . implode(', ', $entry['css']) . "\n";
            }
            echo "\n";
        }
    }
} else {
    echo "✅ Found " . count($mainEntries) . " main entry point(s):\n\n";
    foreach ($mainEntries as $entry) {
        echo "🎯 Entry: " . $entry['key'] . "\n";
        echo "   JS File: " . $entry['file'] . "\n";
        if (!empty($entry['css'])) {
            echo "   CSS Files: " . implode(', ', $entry['css']) . "\n";
        }
        echo "\n";
    }
}

// Check what our test script is looking for
echo "🔧 Checking what our test script expects:\n";
echo "Expected JS: build/assets/main-D4DSGRtK.js\n";
echo "Expected CSS: build/assets/main-Czk1McQF.css\n\n";

// Show the first few actual files for reference
echo "📂 First 10 actual asset files:\n";
$count = 0;
foreach ($manifest as $key => $entry) {
    if (isset($entry['file']) && strpos($entry['file'], 'assets/') === 0) {
        echo "   " . $entry['file'] . "\n";
        $count++;
        if ($count >= 10) break;
    }
}
?>
