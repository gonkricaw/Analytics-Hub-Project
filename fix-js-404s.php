<?php
/**
 * Script to check for missing JavaScript files and update redirects
 */

// Define the source files we want to check
$files = [
    'admin.js',
    'ui-extended.js',
    'charts.js'
];

// Path to the public directory
$publicDir = __DIR__ . '/public';
$buildDir = $publicDir . '/build/assets';

echo "Checking for missing JS files and creating redirects...\n";

// Check if the build directory exists
if (!is_dir($buildDir)) {
    echo "Error: Build directory not found at: $buildDir\n";
    exit(1);
}

// Get all JS files in the build directory
$buildFiles = glob($buildDir . '/*.js');
echo "Found " . count($buildFiles) . " JavaScript files in build directory\n";

// Create the web.php routes backup
$webPhpPath = __DIR__ . '/routes/web.php';
$webPhpBackup = __DIR__ . '/routes/web.php.backup';

// Backup the web.php file
if (!file_exists($webPhpBackup)) {
    copy($webPhpPath, $webPhpBackup);
    echo "Created backup of web.php at: $webPhpBackup\n";
}

// Read the web.php file
$webPhpContent = file_get_contents($webPhpPath);

// For each file we want to check
foreach ($files as $file) {
    echo "Checking for $file...\n";
    
    // Check if the file is already redirected in web.php
    if (strpos($webPhpContent, "'/js/$file'") !== false) {
        echo "A redirect for $file already exists in web.php\n";
        continue;
    }
    
    // Look for a potential match in the build directory
    $potentialMatches = array_filter($buildFiles, function($buildFile) use ($file) {
        // Extract the base name (without extension) and see if it's in the original file name
        $baseName = basename($buildFile, '.js');
        $fileBase = basename($file, '.js');
        
        return strpos($baseName, $fileBase) !== false;
    });
    
    if (!empty($potentialMatches)) {
        $match = array_shift($potentialMatches);
        $relativePath = 'build/assets/' . basename($match);
        
        echo "Found potential match for $file: $relativePath\n";
        
        // Create a redirect in web.php
        $newRoute = <<<PHP

Route::get('/js/$file', function() {
    // Redirect to the Vite built JS file with proper MIME type
    return Redirect::to('/$relativePath');
});
PHP;
        
        // Add the new route before the catch-all route
        $catchAllPosition = strpos($webPhpContent, "Route::get('{any?}',");
        if ($catchAllPosition !== false) {
            $webPhpContent = substr_replace($webPhpContent, $newRoute, $catchAllPosition, 0);
        } else {
            // If catch-all not found, just append to the end
            $webPhpContent .= $newRoute;
        }
        
        echo "Added redirect route for $file -> $relativePath\n";
    } else {
        echo "No match found for $file. Creating empty file...\n";
        
        // Create an empty JS file as a fallback
        file_put_contents($publicDir . '/js/' . $file, '// Placeholder file');
        echo "Created empty placeholder file: /js/$file\n";
    }
}

// Write the updated web.php content
file_put_contents($webPhpPath, $webPhpContent);
echo "Updated web.php with new redirects\n";

echo "Script completed.\n";
