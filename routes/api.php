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
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Api\UserNotificationController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AnalyticsTrackingController;

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

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::post('/jumbotron', [DashboardController::class, 'updateJumbotron']);
        Route::post('/marquee', [DashboardController::class, 'updateMarquee']);
    });

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

        // Menu Management
        Route::prefix('menus')->group(function () {
            Route::get('/', [AdminMenuController::class, 'index']);
            Route::post('/', [AdminMenuController::class, 'store']);
            Route::get('/hierarchy', [AdminMenuController::class, 'hierarchy']);
            Route::get('/available-content', [AdminMenuController::class, 'availableContent']);
            Route::get('/{menu}', [AdminMenuController::class, 'show']);
            Route::put('/{menu}', [AdminMenuController::class, 'update']);
            Route::delete('/{menu}', [AdminMenuController::class, 'destroy']);
            Route::post('/reorder', [AdminMenuController::class, 'reorder']);
            Route::get('/{menu}/children', [AdminMenuController::class, 'children']);
        });

        // Content Management
        Route::prefix('contents')->group(function () {
            Route::get('/', [AdminContentController::class, 'index']);
            Route::post('/', [AdminContentController::class, 'store']);
            Route::get('/{content}', [AdminContentController::class, 'show']);
            Route::put('/{content}', [AdminContentController::class, 'update']);
            Route::delete('/{content}', [AdminContentController::class, 'destroy']);
            Route::post('/{content}/duplicate', [AdminContentController::class, 'duplicate']);
            Route::get('/{content}/preview', [AdminContentController::class, 'preview']);
            Route::get('/statistics', [AdminContentController::class, 'statistics']);
        });

        // Notification Management
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::post('/', [NotificationController::class, 'store']);
            Route::get('/statistics', [NotificationController::class, 'statistics']);
            Route::get('/{notification}', [NotificationController::class, 'show']);
            Route::put('/{notification}', [NotificationController::class, 'update']);
            Route::delete('/{notification}', [NotificationController::class, 'destroy']);
            Route::post('/{notification}/resend', [NotificationController::class, 'resend']);
        });

        // Email Template Management
        Route::prefix('email-templates')->group(function () {
            Route::get('/', [EmailTemplateController::class, 'index']);
            Route::post('/', [EmailTemplateController::class, 'store']);
            Route::get('/types', [EmailTemplateController::class, 'types']);
            Route::post('/create-defaults', [EmailTemplateController::class, 'createDefaults']);
            Route::get('/{emailTemplate}', [EmailTemplateController::class, 'show']);
            Route::put('/{emailTemplate}', [EmailTemplateController::class, 'update']);
            Route::delete('/{emailTemplate}', [EmailTemplateController::class, 'destroy']);
            Route::post('/{emailTemplate}/preview', [EmailTemplateController::class, 'preview']);
            Route::post('/{emailTemplate}/clone', [EmailTemplateController::class, 'clone']);
            Route::post('/{emailTemplate}/toggle-status', [EmailTemplateController::class, 'toggleStatus']);
        });
    });

    // User Notifications
    Route::prefix('user/notifications')->group(function () {
        Route::get('/', [UserNotificationController::class, 'index']);
        Route::get('/unread-count', [UserNotificationController::class, 'unreadCount']);
        Route::post('/mark-all-read', [UserNotificationController::class, 'markAllAsRead']);
        Route::post('/{notification}/mark-read', [UserNotificationController::class, 'markAsRead']);
        Route::post('/{notification}/mark-unread', [UserNotificationController::class, 'markAsUnread']);
        Route::delete('/{notification}', [UserNotificationController::class, 'destroy']);
    });

    // Analytics Tracking
    Route::prefix('analytics')->group(function () {
        Route::post('/visit-duration', [AnalyticsTrackingController::class, 'updateVisitDuration']);
        Route::get('/popular-content', [AnalyticsTrackingController::class, 'getPopularContent']);
        Route::get('/popular-menus', [AnalyticsTrackingController::class, 'getPopularMenus']);
        Route::post('/track-event', [AnalyticsTrackingController::class, 'trackCustomEvent']);
    });

    // Frontend Menu Access
    Route::prefix('menus')->group(function () {
        Route::get('/', [MenuController::class, 'index']);
        Route::get('/hierarchy', [MenuController::class, 'hierarchy']);
        Route::get('/{menu}', [MenuController::class, 'show']);
    });

    // Frontend Content Access
    Route::prefix('contents')->group(function () {
        Route::get('/', [ContentController::class, 'index']);
        Route::get('/{content}', [ContentController::class, 'show']);
        Route::get('/slug/{slug}', [ContentController::class, 'showBySlug']);
    });
});
