<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ContentController;
use App\Helpers\AssetManager;

// Secure embed URL route - requires authentication
Route::middleware('auth:sanctum')->get('/app/embed/{uuid}', [ContentController::class, 'embed'])->name('content.embed');

// Redirect old asset paths to new Vite build paths
Route::get('/css/app.css', function() {
    // Dynamic CSS asset loading using the AssetManager helper
    try {
        $cssPath = AssetManager::getMainCss();
        
        if ($cssPath) {
            return Redirect::to($cssPath);
        }
        
        Log::warning('Main CSS asset not found in manifest');
        return abort(404, 'CSS asset not found in manifest');
    } catch (Exception $e) {
        Log::error('CSS asset loading error: ' . $e->getMessage());
        return abort(500, 'Asset loading failed');
    }
});

Route::get('/js/app.js', function() {
    // Dynamic asset loading using the AssetManager helper
    try {
        $jsPath = AssetManager::getMainJs();
        
        if ($jsPath) {
            return Redirect::to($jsPath);
        }
        
        Log::warning('Main JS asset not found in manifest');
        return abort(404, 'Main JS asset not found in manifest');
    } catch (Exception $e) {
        Log::error('JS asset loading error: ' . $e->getMessage());
        return abort(500, 'Asset loading failed');
    }
});

// Catch-all route for SPA - exclude asset paths
Route::get('{any?}', function() {
    return view('application');
})->where('any', '^(?!build|css|js|images|storage|api).*$');
