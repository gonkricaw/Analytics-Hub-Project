<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Menu;
use App\Models\Content;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserRolePolicy;
use App\Policies\MenuPolicy;
use App\Policies\ContentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Permission::class => PermissionPolicy::class,
        Role::class => RolePolicy::class,
        User::class => UserRolePolicy::class,
        Menu::class => MenuPolicy::class,
        Content::class => ContentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define additional gates if needed
        Gate::define('manage-rbac', function (User $user) {
            return $user->hasAnyRole(['super_admin', 'admin']);
        });

        Gate::define('manage-users', function (User $user) {
            return $user->hasPermission('users.manage');
        });

        Gate::define('super-admin-only', function (User $user) {
            return $user->hasRole('super_admin');
        });

        // Menu Management Gates
        Gate::define('menus.view', function (User $user) {
            return $user->hasPermissionTo('menus.view') || $user->hasAnyRole(['admin', 'super_admin']);
        });

        Gate::define('menus.create', function (User $user) {
            return $user->hasPermissionTo('menus.create') || $user->hasPermissionTo('menus.manage') || $user->hasAnyRole(['admin', 'super_admin']);
        });

        Gate::define('menus.update', function (User $user) {
            return $user->hasPermissionTo('menus.update') || $user->hasPermissionTo('menus.manage') || $user->hasAnyRole(['admin', 'super_admin']);
        });

        Gate::define('menus.delete', function (User $user) {
            return $user->hasPermissionTo('menus.delete') || $user->hasPermissionTo('menus.manage') || $user->hasAnyRole(['admin', 'super_admin']);
        });

        Gate::define('menus.reorder', function (User $user) {
            return $user->hasPermissionTo('menus.reorder') || $user->hasPermissionTo('menus.manage') || $user->hasAnyRole(['admin', 'super_admin']);
        });

        // Content Management Gates
        Gate::define('content.view', function (User $user) {
            return $user->hasPermissionTo('content.view') || $user->hasAnyRole(['admin', 'super_admin']);
        });

        Gate::define('content.create', function (User $user) {
            return $user->hasPermissionTo('content.create') || $user->hasPermissionTo('content.manage') || $user->hasAnyRole(['admin', 'super_admin']);
        });

        Gate::define('content.update', function (User $user) {
            return $user->hasPermissionTo('content.update') || $user->hasPermissionTo('content.manage') || $user->hasAnyRole(['admin', 'super_admin']);
        });

        Gate::define('content.delete', function (User $user) {
            return $user->hasPermissionTo('content.delete') || $user->hasPermissionTo('content.manage') || $user->hasAnyRole(['admin', 'super_admin']);
        });

        Gate::define('content.publish', function (User $user) {
            return $user->hasPermissionTo('content.publish') || $user->hasPermissionTo('content.manage') || $user->hasAnyRole(['admin', 'super_admin']);
        });
    }
}
