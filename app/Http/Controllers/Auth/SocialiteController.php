<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class SocialiteController extends Controller
{
    /**
     * Redirect to the OAuth provider
     */
    public function redirect($provider)
    {
        if (!in_array($provider, ['google', 'facebook'])) {
            return redirect()->route('start')->with('error', 'Invalid social provider.');
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the OAuth callback
     */
    public function callback($provider)
    {
        try {
            if (!in_array($provider, ['google', 'facebook'])) {
                return redirect()->route('start')->with('error', 'Invalid social provider.');
            }

            $socialUser = Socialite::driver($provider)->user();

            // Check if user already exists with this email
            $existingUser = User::where('email', $socialUser->getEmail())->first();

            if ($existingUser) {
                // Update social provider info if not set
                if (!$existingUser->{$provider . '_id'}) {
                    $existingUser->update([
                        $provider . '_id' => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                    ]);
                }

                Auth::login($existingUser);
                return redirect()->intended('/dashboard');
            }

            // Create new user
            $user = User::create([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(24)), // Random password for social users
                $provider . '_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'provider' => $provider,
            ]);

            Auth::login($user);

            // Redirect to complete registration if profile incomplete
            if (!$user->hasCompletedProfile()) {
                return redirect()->route('register.basic-info');
            }

            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            return redirect()->route('start')->with('error', 'Unable to authenticate with ' . ucfirst($provider) . '. Please try again.');
        }
    }
}
