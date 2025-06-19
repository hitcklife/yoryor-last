<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Enable two-factor authentication for a user
     *
     * @param User $user
     * @return array
     */
    public function enableTwoFactor(User $user): array
    {
        // Generate a secret key
        $secretKey = $this->google2fa->generateSecretKey();

        // Generate recovery codes
        $recoveryCodes = $this->generateRecoveryCodes();

        // Update user
        $user->update([
            'two_factor_secret' => $secretKey,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_enabled' => true,
        ]);

        // Generate QR code URL
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email ?? $user->phone ?? 'user',
            $secretKey
        );

        return [
            'secret_key' => $secretKey,
            'qr_code_url' => $qrCodeUrl,
            'recovery_codes' => $recoveryCodes,
        ];
    }

    /**
     * Disable two-factor authentication for a user
     *
     * @param User $user
     * @return void
     */
    public function disableTwoFactor(User $user): void
    {
        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_enabled' => false,
        ]);
    }

    /**
     * Verify a two-factor authentication code
     *
     * @param User $user
     * @param string $code
     * @return bool
     */
    public function verifyCode(User $user, string $code): bool
    {
        if (!$user->two_factor_enabled || !$user->two_factor_secret) {
            return false;
        }

        // Check if it's a recovery code
        if ($this->verifyRecoveryCode($user, $code)) {
            return true;
        }

        // Verify the code with Google2FA
        return $this->google2fa->verifyKey($user->two_factor_secret, $code);
    }

    /**
     * Verify a recovery code
     *
     * @param User $user
     * @param string $code
     * @return bool
     */
    protected function verifyRecoveryCode(User $user, string $code): bool
    {
        if (!$user->two_factor_recovery_codes) {
            return false;
        }

        $recoveryCodes = $user->two_factor_recovery_codes;

        // Find the recovery code
        $index = array_search($code, $recoveryCodes);

        if ($index !== false) {
            // Remove the used recovery code
            unset($recoveryCodes[$index]);

            // Update the user's recovery codes
            $user->update([
                'two_factor_recovery_codes' => array_values($recoveryCodes),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Generate recovery codes
     *
     * @param int $count
     * @return array
     */
    protected function generateRecoveryCodes(int $count = 8): array
    {
        $recoveryCodes = [];

        for ($i = 0; $i < $count; $i++) {
            $recoveryCodes[] = Str::random(10);
        }

        return $recoveryCodes;
    }
}
