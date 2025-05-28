<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * AssetManager - A helper class for managing dynamic asset loading and validation
 * 
 * This class provides methods to dynamically load assets from the Vite manifest.json
 * file, helping prevent hardcoded asset references that can lead to 404 errors.
 * 
 * @author AI Code Assistant
 * @date May 28, 2025
 */
class AssetManager
{
    /**
     * Get the path to an asset from the manifest file
     *
     * @param string $entryPoint The entry point in the manifest (e.g., 'resources/js/main.js')
     * @param string $type The type of asset ('file', 'css', 'imports')
     * @param int $index The index of the asset within the type array (for CSS and imports)
     * 
     * @return string|null The asset path relative to public directory, or null if not found
     * 
     * @throws Exception If manifest cannot be loaded or parsed
     */
    public static function getAssetPath($entryPoint, $type = 'file', $index = 0)
    {
        $manifestPath = public_path('build/manifest.json');
        
        if (!file_exists($manifestPath)) {
            Log::error('Asset manifest not found at: ' . $manifestPath);
            throw new Exception('Asset manifest not found');
        }
        
        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        if ($manifest === null) {
            Log::error('Failed to parse manifest JSON');
            throw new Exception('Failed to parse asset manifest');
        }
        
        // Check if the entry point exists
        if (!isset($manifest[$entryPoint])) {
            Log::warning("Entry point '$entryPoint' not found in manifest");
            return null;
        }
        
        // Get the asset based on type
        if ($type === 'file') {
            return isset($manifest[$entryPoint]['file']) ? 
                   '/build/' . $manifest[$entryPoint]['file'] : 
                   null;
        }
        
        if ($type === 'css' && isset($manifest[$entryPoint]['css']) && isset($manifest[$entryPoint]['css'][$index])) {
            return '/build/' . $manifest[$entryPoint]['css'][$index];
        }
        
        if ($type === 'imports' && isset($manifest[$entryPoint]['imports']) && isset($manifest[$entryPoint]['imports'][$index])) {
            return '/build/' . $manifest[$entryPoint]['imports'][$index];
        }
        
        return null;
    }
    
    /**
     * Get the main JS asset path from the manifest
     *
     * @return string|null The JS asset path relative to public directory, or null if not found
     */
    public static function getMainJs()
    {
        try {
            return self::getAssetPath('resources/js/main.js', 'file');
        } catch (Exception $e) {
            Log::error('Failed to get main JS asset: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get the main CSS asset path from the manifest
     *
     * @return string|null The CSS asset path relative to public directory, or null if not found
     */
    public static function getMainCss()
    {
        try {
            return self::getAssetPath('resources/js/main.js', 'css', 0);
        } catch (Exception $e) {
            Log::error('Failed to get main CSS asset: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verify that an asset exists on the filesystem
     *
     * @param string $path The path to check, relative to public directory
     * @return bool True if the asset exists, false otherwise
     */
    public static function assetExists($path)
    {
        if (empty($path)) return false;
        
        $fullPath = public_path(ltrim($path, '/'));
        return file_exists($fullPath);
    }
    
    /**
     * Validate all assets in the manifest to ensure they exist
     *
     * @return array An array with validation results
     */
    public static function validateAllAssets()
    {
        $result = [
            'total' => 0,
            'valid' => 0,
            'missing' => 0,
            'missing_assets' => [],
        ];
        
        try {
            $manifestPath = public_path('build/manifest.json');
            
            if (!file_exists($manifestPath)) {
                Log::error('Asset manifest not found at: ' . $manifestPath);
                throw new Exception('Asset manifest not found');
            }
            
            $manifest = json_decode(file_get_contents($manifestPath), true);
            
            if ($manifest === null) {
                Log::error('Failed to parse manifest JSON');
                throw new Exception('Failed to parse asset manifest');
            }
            
            foreach ($manifest as $entry => $details) {
                // Check main file
                if (isset($details['file'])) {
                    $result['total']++;
                    $path = '/build/' . $details['file'];
                    
                    if (self::assetExists($path)) {
                        $result['valid']++;
                    } else {
                        $result['missing']++;
                        $result['missing_assets'][] = $path;
                    }
                }
                
                // Check CSS files
                if (isset($details['css']) && is_array($details['css'])) {
                    foreach ($details['css'] as $cssFile) {
                        $result['total']++;
                        $path = '/build/' . $cssFile;
                        
                        if (self::assetExists($path)) {
                            $result['valid']++;
                        } else {
                            $result['missing']++;
                            $result['missing_assets'][] = $path;
                        }
                    }
                }
            }
            
            return $result;
        } catch (Exception $e) {
            Log::error('Asset validation error: ' . $e->getMessage());
            throw $e;
        }
    }
}
