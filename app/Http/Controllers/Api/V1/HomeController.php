<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Message;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
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

        // Always update last active timestamp outside of cache
        $user->updateLastActive();

        return $this->cacheService->remember(
            "home_stats:{$user->id}",
            CacheService::TTL_SHORT, // Short TTL for real-time data like unread counts
            function() use ($user) {
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

                // Get new matches count
                $newMatchesCount = $user->matches()
                    ->where('created_at', '>', $user->last_login_at ?? now()->subYears(10))
                    ->count();

                // Get profile views count (simplified for now)
                $profileViews = 0;

                // Get profile photo URL
                $profilePhoto = $user->photos()->where('is_profile_photo', true)->first();
                $profilePhotoUrl = $profilePhoto ? $profilePhoto->original_url : null;

                // Get suggested users (simplified for now)
                $suggestedUsers = [];
                $suggestionsCount = 0;

                return response()->json([
                    'status' => 'success',
                    'message' => 'Home stats retrieved successfully',
                    'data' => [
                        'user' => [
                            'id' => $user->id,
                            'first_name' => $profile->first_name,
                            'profile_photo_url' => $profilePhotoUrl,
                            'is_premium' => false, // Simplified for now
                        ],
                        'stats' => [
                            'new_likes' => $newLikesCount,
                            'new_matches' => $newMatchesCount,
                            'unread_messages' => $unreadMessagesCount,
                            'profile_views' => $profileViews,
                        ],
                        'suggestions' => [
                            'count' => $suggestionsCount,
                            'users' => $suggestedUsers,
                        ],
                    ]
                ]);
            },
            ["user_{$user->id}_stats", "user_{$user->id}_chats", "user_{$user->id}_likes", "user_{$user->id}_matches"]
        );
    }
}
