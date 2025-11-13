<?php

namespace App\Services;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OtpService
{
    /**
     * Generate and store OTP for a phone number
     *
     * @param string $phone
     * @param int $expiryMinutes
     * @return array
     */
    public function generateOtp(string $phone, int $expiryMinutes = 5): array
    {
        // Delete any existing unused OTP for this phone
        OtpCode::where('phone', $phone)
            ->where('used', false)
            ->delete();

        // Generate new OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP
        OtpCode::create([
            'phone' => $phone,
            'code' => $otp,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);

        return [
            'phone' => $phone,
            'otp' => $otp, // In production, this would be sent via SMS and not returned
            'expires_in' => $expiryMinutes * 60 // in seconds
        ];
    }

    /**
     * Verify OTP and return or create user
     *
     * @param string $phone
     * @param string $otp
     * @return array
     * @throws ValidationException
     */
    public function verifyOtp(string $phone, string $otp): array
    {
        // Find the latest unused OTP for this phone
        $otpCode = OtpCode::where('phone', $phone)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpCode) {
            throw ValidationException::withMessages([
                'otp' => ['The OTP is invalid or expired.']
            ]);
        }

        // Check if the OTP code matches
        if ($otpCode->code !== $otp) {
            throw ValidationException::withMessages([
                'otp' => ['The OTP is invalid or expired.']
            ]);
        }

        // Mark OTP as used
        $otpCode->update(['used' => true]);

        // Find or create user
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'registration_completed' => false,
                'phone_verified_at' => now(),
            ]
        );

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load(['profile', 'preference']),
            'token' => $token,
            'is_new_user' => $user->wasRecentlyCreated,
        ];
    }

    /**
     * Send OTP to phone number
     *
     * @param string $phone
     * @return array
     */
    public function sendOtp(string $phone): array
    {
        try {
            $otpData = $this->generateOtp($phone);
            $sent = $this->sendOtpSms($phone, $otpData['otp']);
            
            return [
                'success' => $sent,
                'phone' => $phone,
                'expires_in' => $otpData['expires_in'],
                'message' => $sent ? 'OTP sent successfully' : 'Failed to send OTP'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.'
            ];
        }
    }

    /**
     * Send OTP via SMS (mock implementation)
     *
     * @param string $phone
     * @param string $otp
     * @return bool
     */
    public function sendOtpSms(string $phone, string $otp): bool
    {
        // This is a mock implementation
        // In a real application, you would integrate with an SMS service
        // For example:
        // $smsService->send($phone, "Your verification code is: {$otp}");

        // Log for development purposes
        \Log::info("OTP sent to {$phone}: {$otp}");

        return true;
    }
}
