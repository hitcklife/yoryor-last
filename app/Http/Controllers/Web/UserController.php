<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Show the dashboard with smart redirection logic
     */
    public function dashboard()
    {
        $user = Auth::user()->load(['profile', 'photos']);

        // Check if registration is completed
        if (!$user->registration_completed) {
            // Find the appropriate onboarding step to redirect to
            if (!$user->profile?->first_name) {
                return redirect()->route('onboard.basic-info')->with('info', 'Please complete your basic information.');
            } elseif (!$user->profile?->status) {
                return redirect()->route('onboard.about-you')->with('info', 'Please tell us about yourself.');
            } elseif (!$user->profile?->looking_for_relationship) {
                return redirect()->route('onboard.preferences')->with('info', 'Please set your preferences.');
            } elseif (!$user->profile?->interests) {
                return redirect()->route('onboard.interests')->with('info', 'Please select your interests.');
            } elseif ($user->photos->count() === 0) {
                return redirect()->route('onboard.photos')->with('info', 'Please add your photos.');
            } elseif (!$user->profile?->country) {
                return redirect()->route('onboard.location')->with('info', 'Please set your location.');
            } else {
                return redirect()->route('onboard.preview')->with('info', 'Please review and complete your registration.');
            }
        }

        return view('user.dashboard', compact('user'));
    }

    /**
     * Show the user profile page
     */
    public function profile()
    {
        return view('user.profile');
    }

    /**
     * Show the discover/matching page
     */
    public function discover()
    {
        return view('user.discover');
    }

    /**
     * Show the user's matches
     */
    public function matches()
    {
        return view('user.matches');
    }

    /**
     * Show the user's likes
     */
    public function likes()
    {
        return view('user.likes');
    }

    /**
     * Show the user's messages/chat
     */
    public function messages()
    {
        return view('user.messages');
    }

    /**
     * Show specific chat conversation
     */
    public function chat($conversationId)
    {
        return view('user.chat', compact('conversationId'));
    }

    /**
     * Show user settings
     */
    public function settings()
    {
        return view('user.settings');
    }

    /**
     * Show privacy settings
     */
    public function settingsPrivacy()
    {
        return view('user.settings.privacy');
    }

    /**
     * Show notifications settings
     */
    public function settingsNotifications()
    {
        return view('user.settings.notifications');
    }

    /**
     * Show subscription settings
     */
    public function settingsSubscription()
    {
        return view('user.settings.subscription');
    }
}
