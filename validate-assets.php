<?php
/**
 * Validate Assets Script
 *
 * This script validates that all assets referenced in the manifest.json actually exist
 * and are accessible. It should be run after every build to catch asset-related issues early.
 * 
 * Usage: php validate-assets.php
 *
 * @author AI Code Assistant
 * @date May 28, 2025
 */

// Bootstrap Laravel application to use helpers
require_once __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "╔═══════════════════════════════════════════════════╗\n";
echo "║            Asset Validation Script                ║\n";
echo "╚═══════════════════════════════════════════════════╝\n\n";

// Initialize counters
$totalAssets = 0;
$missingAssets = 0;
$validAssets = 0;

// Get the manifest path
$manifestPath = public_path('build/manifest.json');

if (!file_exists($manifestPath)) {
    echo "❌ ERROR: Manifest file not found at: $manifestPath\n";
    exit(1);
}

echo "✓ Manifest file found\n";

// Parse the manifest file
$manifest = json_decode(file_get_contents($manifestPath), true);

if ($manifest === null) {
    echo "❌ ERROR: Failed to parse manifest file as JSON\n";
    exit(1);
}

echo "✓ Manifest file parsed successfully\n\n";
echo "Validating assets...\n\n";

// Validate each entry in the manifest
foreach ($manifest as $entry => $details) {
    echo "Entry: $entry\n";
    
    // Check main file
    if (isset($details['file'])) {
        $totalAssets++;
        $filePath = public_path('build/' . $details['file']);
        
        if (file_exists($filePath)) {
            echo "  ✓ Main asset exists: {$details['file']}\n";
            $validAssets++;
        } else {
            echo "  ❌ MISSING: {$details['file']}\n";
            $missingAssets++;
        }
    }
    
    // Check CSS files
    if (isset($details['css']) && is_array($details['css'])) {
        foreach ($details['css'] as $cssFile) {
            $totalAssets++;
            $filePath = public_path('build/' . $cssFile);
            
            if (file_exists($filePath)) {
                echo "  ✓ CSS file exists: {$cssFile}\n";
                $validAssets++;
            } else {
                echo "  ❌ MISSING CSS: {$cssFile}\n";
                $missingAssets++;
            }
        }
    }
    
    // Check imports
    if (isset($details['imports']) && is_array($details['imports'])) {
        foreach ($details['imports'] as $importFile) {
            // Skip checking imports that are just references to other entries
            if (isset($manifest[$importFile])) {
                echo "  → Import reference: {$importFile}\n";
                continue;
            }
            
            $totalAssets++;
            $filePath = public_path('build/' . $importFile);
            
            if (file_exists($filePath)) {
                echo "  ✓ Import exists: {$importFile}\n";
                $validAssets++;
            } else {
                echo "  ❌ MISSING IMPORT: {$importFile}\n";
                $missingAssets++;
            }
        }
    }
    
    echo "  ------------------\n";
}

// Print summary
echo "\n══════════════ VALIDATION SUMMARY ══════════════\n";
echo "Total assets checked: $totalAssets\n";
echo "Valid assets found: $validAssets\n";
echo "Missing assets: $missingAssets\n";

// Set exit code based on results
if ($missingAssets > 0) {
    echo "\n❌ VALIDATION FAILED: $missingAssets assets are missing\n";
    exit(1);
} else {
    echo "\n✓ VALIDATION PASSED: All assets are valid\n";
    exit(0);
}
