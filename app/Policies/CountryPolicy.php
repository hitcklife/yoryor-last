<?php

namespace App\Policies;

use App\Models\Country;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CountryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any countries.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // All users can view countries
        return true;
    }

    /**
     * Determine whether the user can view the country.
     *
     * @param User $user
     * @param Country $country
     * @return bool
     */
    public function view(User $user, Country $country): bool
    {
        // All users can view countries
        return true;
    }

    /**
     * Determine whether the user can create countries.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Only admins can create countries
        return $user->hasPermission('country.create');
    }

    /**
     * Determine whether the user can update the country.
     *
     * @param User $user
     * @param Country $country
     * @return bool
     */
    public function update(User $user, Country $country): bool
    {
        // Only admins can update countries
        return $user->hasPermission('country.update');
    }

    /**
     * Determine whether the user can delete the country.
     *
     * @param User $user
     * @param Country $country
     * @return bool
     */
    public function delete(User $user, Country $country): bool
    {
        // Only admins can delete countries
        return $user->hasPermission('country.delete');
    }

    /**
     * Determine whether the user can restore the country.
     *
     * @param User $user
     * @param Country $country
     * @return bool
     */
    public function restore(User $user, Country $country): bool
    {
        // Only admins can restore countries
        return $user->hasPermission('country.restore');
    }

    /**
     * Determine whether the user can permanently delete the country.
     *
     * @param User $user
     * @param Country $country
     * @return bool
     */
    public function forceDelete(User $user, Country $country): bool
    {
        // Only admins can force delete countries
        return $user->hasPermission('country.force.delete');
    }
}
