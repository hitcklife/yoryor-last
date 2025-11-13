<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\OtpService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    /**
     * Handle OTP authentication for web
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'otp' => 'nullable|string|size:6',
        ]);

        try {
            $phone = $request->input('phone');
            $otp = $request->input('otp');

            // If OTP is not provided, generate and send one
            if (!$otp) {
                $otpData = $this->otpService->generateOtp($phone);
                $this->otpService->sendOtpSms($phone, $otpData['otp']);

                return response()->json([
                    'status' => 'success',
                    'message' => 'OTP sent successfully',
                    'data' => [
                        'otp_sent' => true,
                        'authenticated' => false,
                        'registration_completed' => false,
                        'phone' => $phone,
                        'expires_in' => $otpData['expires_in']
                    ]
                ]);
            }

            // If OTP is provided, verify it
            $userData = $this->otpService->verifyOtp($phone, $otp);
            $user = $userData['user'];

            // Update last login timestamp
            $user->last_login_at = now();
            $user->save();

            // Log the user in for web session
            Auth::login($user);

            // Store user data in session
            Session::put('auth_user_id', $user->id);
            Session::put('auth_token', $userData['token']);

            return response()->json([
                'status' => 'success',
                'message' => 'Authentication successful',
                'data' => [
                    'otp_sent' => false,
                    'authenticated' => true,
                    'registration_completed' => $user->registration_completed,
                    'user' => $user->load(['profile']),
                    'token' => $userData['token'],
                    'redirect_url' => $this->getRedirectUrl($user)
                ]
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid OTP',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get redirect URL based on user registration status
     */
    private function getRedirectUrl(User $user): string
    {
        if ($user->registration_completed) {
            // User has completed registration, redirect to dashboard/matches
            return route('dashboard');
        } else {
            // User needs to complete registration, redirect to onboarding flow
            return route('onboard.basic-info');
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->tokens()->delete(); // Revoke all tokens
        }

        Auth::logout();
        Session::forget(['auth_user_id', 'auth_token']);
        Session::invalidate();
        Session::regenerateToken();

        return redirect()->route('home')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show login form (fallback for non-JS users)
     */
    public function showLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return redirect($this->getRedirectUrl($user));
        }

        return view('auth.login');
    }

    /**
     * Handle login form submission (fallback for non-JS users)
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required_without:phone|email|nullable',
            'phone' => 'required_without:email|nullable',
            'password' => 'required',
        ]);

        $credentials = $request->only(['email', 'phone', 'password']);

        // Remove null values
        $credentials = array_filter($credentials, function($value) {
            return $value !== null;
        });

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
            
            // Update last login
            $user->last_login_at = now();
            $user->save();

            return redirect()->intended($this->getRedirectUrl($user));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email', 'phone');
    }

    /**
     * Check authentication status for AJAX requests
     */
    public function checkAuth(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            return response()->json([
                'status' => 'success',
                'authenticated' => true,
                'user' => $user,
                'registration_completed' => $user->registration_completed,
                'redirect_url' => $this->getRedirectUrl($user)
            ]);
        }

        return response()->json([
            'status' => 'success',
            'authenticated' => false
        ]);
    }
}