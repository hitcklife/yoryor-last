<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CompleteRegistrationRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\OtpRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     *
     * @param RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {

        try {
            // Begin transaction
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'registration_completed' => true
            ]);

            // Create profile
            $user->profile()->create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender
            ]);

            // Create default preferences
            $user->preference()->create([
                'search_radius' => 10, // default value
                'min_age' => 18,
                'max_age' => 99
            ]);

            // Commit transaction
            DB::commit();

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'user' => $user->load('profile'),
                    'token' => $token
                ]
            ], 201);

        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        try {
            // Get credentials from request
            $credentials = $request->credentials();

            if (!Auth::attempt($credentials)) {
                throw ValidationException::withMessages([
                    'credentials' => ['The provided credentials are incorrect.']
                ]);
            }

            $user = Auth::user();

            // Check if user is disabled
            if ($user->disabled_at !== null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account is disabled'
                ], 403);
            }

            // Generate new token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => $user->load(['profile', 'preference']),
                    'token' => $token
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // Revoke all tokens
            $request->user()->tokens()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Logout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request OTP for phone login
     *
     * @param OtpRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestOtp(OtpRequest $request)
    {
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($validated) {
                // Delete any existing unused OTP for this phone
                OtpCode::where('phone', $validated['phone'])
                    ->where('used', false)
                    ->delete();

                // Generate new OTP
                $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                // Store OTP
                OtpCode::create([
                    'phone' => $validated['phone'],
                    'code' => Hash::make($otp),
                    'expires_at' => now()->addMinutes(5), // OTP valid for 5 minutes
                ]);

                // Here you would integrate with your SMS service
                // For example:
                // $this->smsService->sendOtp($validated['phone'], $otp);

                return response()->json([
                    'status' => 'success',
                    'message' => 'OTP sent successfully',
                    'data' => [
                        'phone' => $validated['phone'],
                        'expires_in' => 300 // 5 minutes in seconds
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify OTP and login/register user
     *
     * @param VerifyOtpRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(VerifyOtpRequest $request)
    {
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($validated) {
                // Find the latest unused OTP for this phone
                $otpCode = OtpCode::where('phone', $validated['phone'])
                    ->where('used', false)
                    ->where('expires_at', '>', now())
                    ->latest()
                    ->first();

                if (!$otpCode || !Hash::check($validated['otp'], $otpCode->code)) {
                    throw ValidationException::withMessages([
                        'otp' => ['The OTP is invalid or expired.']
                    ]);
                }

                // Mark OTP as used
                $otpCode->update(['used' => true]);

                // Find or create user
                $user = User::firstOrCreate(
                    ['phone' => $validated['phone']],
                    [
                        'registration_completed' => false,
                        'phone_verified_at' => now(),
                    ]
                );

                // Generate token
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'status' => 'success',
                    'message' => 'OTP verified successfully',
                    'data' => [
                        'user' => $user->load(['profile', 'preference']),
                        'token' => $token,
                        'is_new_user' => $user->wasRecentlyCreated,
                    ]
                ]);
            });
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP verification failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'OTP verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete registration for OTP users
     *
     * @param CompleteRegistrationRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeRegistration(CompleteRegistrationRequest $request)
    {
        $user = $request->user();

        if ($user->registration_completed) {
            return response()->json([
                'status' => 'error',
                'message' => 'Registration already completed'
            ], 422);
        }

        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($user, $validated) {
                $user->profile()->create([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender']
                ]);

                $user->preference()->create([
                    'search_radius' => 10,
                    'min_age' => 18,
                    'max_age' => 99
                ]);

                $user->update(['registration_completed' => true]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Registration completed successfully',
                    'data' => [
                        'user' => $user->load(['profile', 'preference'])
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to complete registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
