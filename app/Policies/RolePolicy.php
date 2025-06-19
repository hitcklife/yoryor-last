<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any roles.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('role.view.any');
    }

    /**
     * Determine whether the user can view the role.
     *
     * @param User $user
     * @param Role $role
     * @return bool
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('role.view');
    }

    /**
     * Determine whether the user can create roles.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('role.create');
    }

    /**
     * Determine whether the user can update the role.
     *
     * @param User $user
     * @param Role $role
     * @return bool
     */
    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission('role.update');
    }

    /**
     * Determine whether the user can delete the role.
     *
     * @param User $user
     * @param Role $role
     * @return bool
     */
    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermission('role.delete');
    }

    /**
     * Determine whether the user can restore the role.
     *
     * @param User $user
     * @param Role $role
     * @return bool
     */
    public function restore(User $user, Role $role): bool
    {
        return $user->hasPermission('role.restore');
    }

    /**
     * Determine whether the user can permanently delete the role.
     *
     * @param User $user
     * @param Role $role
     * @return bool
     */
    public function forceDelete(User $user, Role $role): bool
    {
        return $user->hasPermission('role.force.delete');
    }
}
