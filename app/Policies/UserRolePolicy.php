<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserRolePolicy
{
    /**
     * Determine whether the user can view user roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('user_roles.view');
    }

    /**
     * Determine whether the user can view specific user roles.
     */
    public function view(User $user, User $targetUser): bool
    {
        return $user->hasPermission('user_roles.view') || $user->id === $targetUser->id;
    }

    /**
     * Determine whether the user can assign roles to users.
     */
    public function assignRole(User $user, User $targetUser): bool
    {
        // Super admin can assign roles to anyone
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Admin can assign roles to non-admin users
        if ($user->hasRole('admin') && !$targetUser->hasRole(['admin', 'super_admin'])) {
            return $user->hasPermission('user_roles.assign');
        }

        // Users cannot assign roles to themselves or others
        return false;
    }

    /**
     * Determine whether the user can remove roles from users.
     */
    public function removeRole(User $user, User $targetUser): bool
    {
        // Super admin can remove roles from anyone except other super admins
        if ($user->hasRole('super_admin')) {
            return !($targetUser->hasRole('super_admin') && $user->id !== $targetUser->id);
        }

        // Admin can remove roles from non-admin users
        if ($user->hasRole('admin') && !$targetUser->hasRole(['admin', 'super_admin'])) {
            return $user->hasPermission('user_roles.remove');
        }

        // Users cannot remove roles
        return false;
    }

    /**
     * Determine whether the user can sync roles for users.
     */
    public function syncRoles(User $user, User $targetUser): bool
    {
        return $this->assignRole($user, $targetUser);
    }
}
