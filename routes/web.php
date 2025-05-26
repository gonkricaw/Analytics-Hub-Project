<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContentController;

// Secure embed URL route - requires authentication
Route::middleware('auth:sanctum')->get('/app/embed/{uuid}', [ContentController::class, 'embed'])->name('content.embed');

Route::get('{any?}', function() {
    return view('application');
})->where('any', '.*');
