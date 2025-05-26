<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MenuPolicy
{
    /**
     * Determine whether the user can view any menus.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('menus.view') || $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can view the menu.
     */
    public function view(User $user, Menu $menu): bool
    {
        return $user->hasPermissionTo('menus.view') || $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can create menus.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('menus.create') || 
               $user->hasPermissionTo('menus.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can update the menu.
     */
    public function update(User $user, Menu $menu): bool
    {
        return $user->hasPermissionTo('menus.update') || 
               $user->hasPermissionTo('menus.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can delete the menu.
     */
    public function delete(User $user, Menu $menu): bool
    {
        return $user->hasPermissionTo('menus.delete') || 
               $user->hasPermissionTo('menus.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can restore the menu.
     */
    public function restore(User $user, Menu $menu): bool
    {
        return $user->hasPermissionTo('menus.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can permanently delete the menu.
     */
    public function forceDelete(User $user, Menu $menu): bool
    {
        return $user->hasPermissionTo('menus.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Determine whether the user can reorder menus.
     */
    public function reorder(User $user): bool
    {
        return $user->hasPermissionTo('menus.reorder') || 
               $user->hasPermissionTo('menus.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Gate methods for string-based authorization.
     */
    public function menus_view(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function menus_create(User $user): bool
    {
        return $this->create($user);
    }

    public function menus_update(User $user): bool
    {
        return $user->hasPermissionTo('menus.update') || 
               $user->hasPermissionTo('menus.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    public function menus_delete(User $user): bool
    {
        return $user->hasPermissionTo('menus.delete') || 
               $user->hasPermissionTo('menus.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    public function menus_manage(User $user): bool
    {
        return $user->hasPermissionTo('menus.manage') || 
               $user->hasAnyRole(['admin', 'super_admin']);
    }

    public function menus_reorder(User $user): bool
    {
        return $this->reorder($user);
    }
}
