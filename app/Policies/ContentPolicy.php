<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ContentPolicy
{
    /**
     * Determine whether the user can view any content.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('content.view') || $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can view the content.
     */
    public function view(User $user, Content $content): bool
    {
        return $user->hasPermissionTo('content.view') || $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can create content.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('content.create') || 
               $user->hasPermissionTo('content.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can update the content.
     */
    public function update(User $user, Content $content): bool
    {
        return $user->hasPermissionTo('content.update') || 
               $user->hasPermissionTo('content.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can delete the content.
     */
    public function delete(User $user, Content $content): bool
    {
        return $user->hasPermissionTo('content.delete') || 
               $user->hasPermissionTo('content.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can restore the content.
     */
    public function restore(User $user, Content $content): bool
    {
        return $user->hasPermissionTo('content.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can permanently delete the content.
     */
    public function forceDelete(User $user, Content $content): bool
    {
        return $user->hasPermissionTo('content.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can publish content.
     */
    public function publish(User $user): bool
    {
        return $user->hasPermissionTo('content.publish') || 
               $user->hasPermissionTo('content.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Gate methods for string-based authorization.
     */
    public function content_view(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function content_create(User $user): bool
    {
        return $this->create($user);
    }

    public function content_update(User $user): bool
    {
        return $user->hasPermissionTo('content.update') || 
               $user->hasPermissionTo('content.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    public function content_delete(User $user): bool
    {
        return $user->hasPermissionTo('content.delete') || 
               $user->hasPermissionTo('content.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    public function content_manage(User $user): bool
    {
        return $user->hasPermissionTo('content.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    public function content_publish(User $user): bool
    {
        return $this->publish($user);
    }
}
