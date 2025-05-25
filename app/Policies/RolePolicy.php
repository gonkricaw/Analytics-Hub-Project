<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('roles.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('roles.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('roles.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
        // System roles cannot be updated
        if ($role->is_system) {
            return false;
        }
        
        return $user->hasPermission('roles.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        // System roles cannot be deleted
        if ($role->is_system) {
            return false;
        }
        
        return $user->hasPermission('roles.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Role $role): bool
    {
        return $user->hasPermission('roles.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return $user->hasPermission('roles.force_delete');
    }

    /**
     * Determine whether the user can assign permissions to roles.
     */
    public function assignPermissions(User $user, Role $role): bool
    {
        // Cannot assign permissions to system roles
        if ($role->is_system) {
            return false;
        }
        
        return $user->hasPermission('roles.assign_permissions');
    }

    /**
     * Determine whether the user can assign a specific permission to a role.
     */
    public function canAssignPermission(User $user, Role $role, Permission $permission): bool
    {
        // Must have permission to assign permissions
        if (!$user->hasPermission('roles.assign_permissions')) {
            return false;
        }

        // Super admin can assign any permission
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin users cannot assign certain restricted permissions
        $restrictedPermissions = [
            'roles.create',
            'roles.delete',
            'users.delete',
            'permissions.create',
            'permissions.delete',
            'admin.settings',
            'admin.maintenance'
        ];

        if (in_array($permission->name, $restrictedPermissions)) {
            return false;
        }

        return true;
    }
}
