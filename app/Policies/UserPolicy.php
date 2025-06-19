<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any users.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('user.view.any');
    }

    /**
     * Determine whether the user can view the user.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function view(User $user, User $model): bool
    {
        // Users can always view their own profile
        if ($user->id === $model->id) {
            return true;
        }

        return $user->hasPermission('user.view');
    }

    /**
     * Determine whether the user can create users.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('user.create');
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function update(User $user, User $model): bool
    {
        // Users can always update their own profile
        if ($user->id === $model->id) {
            return true;
        }

        return $user->hasPermission('user.update');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function delete(User $user, User $model): bool
    {
        // Users can delete their own account
        if ($user->id === $model->id) {
            return true;
        }

        return $user->hasPermission('user.delete');
    }

    /**
     * Determine whether the user can restore the user.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasPermission('user.restore');
    }

    /**
     * Determine whether the user can permanently delete the user.
     *
     * @param User $user
     * @param User $model
     * @return bool
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasPermission('user.force.delete');
    }
}
