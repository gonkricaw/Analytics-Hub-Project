<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Admin\UserInvitationController;
use App\Http\Controllers\Admin\TermsAndConditionsController;
use App\Http\Controllers\Admin\IpBlockController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserRoleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Public terms and conditions
Route::get('/terms-and-conditions/current', [TermsAndConditionsController::class, 'current']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User authentication and profile
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/accept-terms', [AuthController::class, 'acceptTerms']);
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);

    // Admin routes - User Invitations
    Route::prefix('admin')->group(function () {
        // User Invitations
        Route::prefix('invitations')->group(function () {
            Route::get('/', [UserInvitationController::class, 'index']);
            Route::get('/mine', [UserInvitationController::class, 'myInvitations']);
            Route::post('/', [UserInvitationController::class, 'invite']);
            Route::post('/{user}/resend', [UserInvitationController::class, 'resendInvitation']);
            Route::delete('/{user}', [UserInvitationController::class, 'cancelInvitation']);
        });

        // Terms and Conditions Management
        Route::prefix('terms-and-conditions')->group(function () {
            Route::get('/', [TermsAndConditionsController::class, 'index']);
            Route::post('/', [TermsAndConditionsController::class, 'store']);
            Route::get('/{termsAndCondition}', [TermsAndConditionsController::class, 'show']);
            Route::put('/{termsAndCondition}', [TermsAndConditionsController::class, 'update']);
            Route::post('/{termsAndCondition}/activate', [TermsAndConditionsController::class, 'activate']);
            Route::post('/{termsAndCondition}/deactivate', [TermsAndConditionsController::class, 'deactivate']);
            Route::delete('/{termsAndCondition}', [TermsAndConditionsController::class, 'destroy']);
        });

        // IP Block Management
        Route::prefix('ip-blocks')->group(function () {
            Route::get('/', [IpBlockController::class, 'index']);
            Route::get('/statistics', [IpBlockController::class, 'statistics']);
            Route::get('/failed-attempts', [IpBlockController::class, 'failedAttempts']);
            Route::post('/', [IpBlockController::class, 'store']);
            Route::get('/{ipBlock}', [IpBlockController::class, 'show']);
            Route::post('/{ipBlock}/unblock', [IpBlockController::class, 'unblock']);
            Route::post('/bulk-unblock', [IpBlockController::class, 'bulkUnblock']);
        });

        // RBAC - Permission Management
        Route::prefix('permissions')->group(function () {
            Route::get('/', [PermissionController::class, 'index']);
            Route::post('/', [PermissionController::class, 'store']);
            Route::get('/{permission}', [PermissionController::class, 'show']);
            Route::put('/{permission}', [PermissionController::class, 'update']);
            Route::delete('/{permission}', [PermissionController::class, 'destroy']);
        });

        // RBAC - Role Management
        Route::prefix('roles')->group(function () {
            Route::get('/', [RoleController::class, 'index']);
            Route::post('/', [RoleController::class, 'store']);
            Route::get('/{role}', [RoleController::class, 'show']);
            Route::put('/{role}', [RoleController::class, 'update']);
            Route::delete('/{role}', [RoleController::class, 'destroy']);
            Route::post('/{role}/permissions', [RoleController::class, 'assignPermissions']);
            Route::delete('/{role}/permissions/{permission}', [RoleController::class, 'removePermission']);
        });

        // RBAC - User Role Management
        Route::prefix('user-roles')->group(function () {
            Route::get('/', [UserRoleController::class, 'index']);
            Route::get('/users/{user}', [UserRoleController::class, 'getUserRoles']);
            Route::post('/users/{user}/roles', [UserRoleController::class, 'assignRole']);
            Route::delete('/users/{user}/roles/{role}', [UserRoleController::class, 'removeRole']);
            Route::post('/users/{user}/sync-roles', [UserRoleController::class, 'syncRoles']);
        });
    });
});
