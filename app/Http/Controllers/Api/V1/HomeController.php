<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Get home page data for the authenticated user.
     *
     * This endpoint returns all necessary data for the home page of the mobile app,
     * including user profile information, unread messages count, and new likes count.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profile not found',
                'error_code' => 'profile_not_found'
            ], 404);
        }

        // Get unread messages count
        $unreadMessagesCount = $user->getUnreadMessagesCount();

        // Get new likes count (likes received after the user's last login)
        $newLikesCount = $user->receivedLikes()
            ->where('created_at', '>', $user->last_login_at ?? now()->subYears(10))
            ->count();

        // Get matches count
        $matchesCount = $user->matches()->count();

        // Update last active timestamp
        $user->updateLastActive();

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'username' => $user->username,
                    'full_name' => $user->full_name,
                    'age' => $user->age,
                    'is_online' => $user->isOnline(),
                    'last_active_at' => $user->last_active_at,
                    'registration_completed' => (bool) $user->registration_completed,
                ],
                'profile' => $profile,
                'stats' => [
                    'unread_messages_count' => $unreadMessagesCount,
                    'new_likes_count' => $newLikesCount,
                    'matches_count' => $matchesCount,
                ],
            ]
        ]);
    }
}
