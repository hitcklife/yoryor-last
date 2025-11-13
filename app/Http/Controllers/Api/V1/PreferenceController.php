<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserPreference;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PreferenceController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    /**
     * Get the authenticated user's preferences
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPreferences(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->cacheService->remember(
                "user_preferences:{$user->id}",
                CacheService::TTL_MEDIUM,
                function() use ($user) {
                    $preferences = $user->preference;

                    if (!$preferences) {
                        // Create default preferences if none exist
                        $preferences = UserPreference::create([
                            'user_id' => $user->id,
                        ]);
                    }

                    return response()->json([
                        'status' => 'success',
                        'data' => $preferences
                    ]);
                },
                ["user_{$user->id}_preferences"]
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get preferences',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the authenticated user's preferences
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'search_radius' => 'sometimes|integer|min:1|max:500',
            'country' => 'sometimes|string|size:2|nullable',
            'preferred_genders' => 'sometimes|array|nullable',
            'hobbies_interests' => 'sometimes|array|nullable',
            'min_age' => 'sometimes|integer|min:18|max:120|nullable',
            'max_age' => 'sometimes|integer|min:18|max:120|nullable',
            'languages_spoken' => 'sometimes|array|nullable',
            'deal_breakers' => 'sometimes|array|nullable',
            'must_haves' => 'sometimes|array|nullable',
            'distance_unit' => 'sometimes|in:km,miles',
            'show_me_globally' => 'sometimes|boolean',
            'notification_preferences' => 'sometimes|array|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create preferences
        $preferences = $user->preference;
        if (!$preferences) {
            $preferences = new UserPreference(['user_id' => $user->id]);
        }

        // Update preferences with validated data
        $preferences->fill($validator->validated());
        $preferences->save();

        // Clear user preferences cache
        $this->cacheService->invalidateUserCaches($user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Preferences updated successfully',
            'data' => $preferences
        ]);
    }
}
