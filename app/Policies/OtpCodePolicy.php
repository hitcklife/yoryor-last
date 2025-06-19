<?php

namespace App\Policies;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OtpCodePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any OTP codes.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Only admins can view all OTP codes
        return $user->hasPermission('otp.view.any');
    }

    /**
     * Determine whether the user can view the OTP code.
     *
     * @param User $user
     * @param OtpCode $otpCode
     * @return bool
     */
    public function view(User $user, OtpCode $otpCode): bool
    {
        // Users can only view their own OTP codes
        if ($otpCode->phone === $user->phone) {
            return true;
        }

        return $user->hasPermission('otp.view');
    }

    /**
     * Determine whether the user can create OTP codes.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // OTP codes are created by the system, not by users
        return false;
    }

    /**
     * Determine whether the user can update the OTP code.
     *
     * @param User $user
     * @param OtpCode $otpCode
     * @return bool
     */
    public function update(User $user, OtpCode $otpCode): bool
    {
        // OTP codes should not be updated
        return false;
    }

    /**
     * Determine whether the user can delete the OTP code.
     *
     * @param User $user
     * @param OtpCode $otpCode
     * @return bool
     */
    public function delete(User $user, OtpCode $otpCode): bool
    {
        // Only admins can delete OTP codes
        return $user->hasPermission('otp.delete');
    }

    /**
     * Determine whether the user can restore the OTP code.
     *
     * @param User $user
     * @param OtpCode $otpCode
     * @return bool
     */
    public function restore(User $user, OtpCode $otpCode): bool
    {
        // Only admins can restore OTP codes
        return $user->hasPermission('otp.restore');
    }

    /**
     * Determine whether the user can permanently delete the OTP code.
     *
     * @param User $user
     * @param OtpCode $otpCode
     * @return bool
     */
    public function forceDelete(User $user, OtpCode $otpCode): bool
    {
        // Only admins can force delete OTP codes
        return $user->hasPermission('otp.force.delete');
    }
}
