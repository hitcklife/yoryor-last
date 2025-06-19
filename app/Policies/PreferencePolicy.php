<?php

namespace App\Policies;

use App\Models\Preference;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PreferencePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any preferences.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('preference.view.any');
    }

    /**
     * Determine whether the user can view the preference.
     *
     * @param User $user
     * @param Preference $preference
     * @return bool
     */
    public function view(User $user, Preference $preference): bool
    {
        // Users can always view their own preferences
        if ($user->id === $preference->user_id) {
            return true;
        }

        return $user->hasPermission('preference.view');
    }

    /**
     * Determine whether the user can create preferences.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Users can only create their own preferences
        if (!$user->preference) {
            return true;
        }

        return $user->hasPermission('preference.create');
    }

    /**
     * Determine whether the user can update the preference.
     *
     * @param User $user
     * @param Preference $preference
     * @return bool
     */
    public function update(User $user, Preference $preference): bool
    {
        // Users can always update their own preferences
        if ($user->id === $preference->user_id) {
            return true;
        }

        return $user->hasPermission('preference.update');
    }

    /**
     * Determine whether the user can delete the preference.
     *
     * @param User $user
     * @param Preference $preference
     * @return bool
     */
    public function delete(User $user, Preference $preference): bool
    {
        // Users can delete their own preferences
        if ($user->id === $preference->user_id) {
            return true;
        }

        return $user->hasPermission('preference.delete');
    }

    /**
     * Determine whether the user can restore the preference.
     *
     * @param User $user
     * @param Preference $preference
     * @return bool
     */
    public function restore(User $user, Preference $preference): bool
    {
        return $user->hasPermission('preference.restore');
    }

    /**
     * Determine whether the user can permanently delete the preference.
     *
     * @param User $user
     * @param Preference $preference
     * @return bool
     */
    public function forceDelete(User $user, Preference $preference): bool
    {
        return $user->hasPermission('preference.force.delete');
    }
}
