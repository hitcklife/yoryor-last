<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any permissions.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('permission.view.any');
    }

    /**
     * Determine whether the user can view the permission.
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function view(User $user, Permission $permission): bool
    {
        return $user->hasPermission('permission.view');
    }

    /**
     * Determine whether the user can create permissions.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('permission.create');
    }

    /**
     * Determine whether the user can update the permission.
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function update(User $user, Permission $permission): bool
    {
        return $user->hasPermission('permission.update');
    }

    /**
     * Determine whether the user can delete the permission.
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function delete(User $user, Permission $permission): bool
    {
        return $user->hasPermission('permission.delete');
    }

    /**
     * Determine whether the user can restore the permission.
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function restore(User $user, Permission $permission): bool
    {
        return $user->hasPermission('permission.restore');
    }

    /**
     * Determine whether the user can permanently delete the permission.
     *
     * @param User $user
     * @param Permission $permission
     * @return bool
     */
    public function forceDelete(User $user, Permission $permission): bool
    {
        return $user->hasPermission('permission.force.delete');
    }
}
