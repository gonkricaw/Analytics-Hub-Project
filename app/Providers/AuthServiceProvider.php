<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserRolePolicy;

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
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define additional gates if needed
        Gate::define('manage-rbac', function (User $user) {
            return $user->hasRole(['super_admin', 'admin']);
        });

        Gate::define('manage-users', function (User $user) {
            return $user->hasPermission('users.manage');
        });

        Gate::define('super-admin-only', function (User $user) {
            return $user->hasRole('super_admin');
        });
    }
}
