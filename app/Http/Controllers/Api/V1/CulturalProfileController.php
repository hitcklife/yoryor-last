<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserCulturalProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CulturalProfileController extends Controller
{
    /**
     * Get the authenticated user's cultural profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCulturalProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $culturalProfile = $user->culturalProfile;

        if (!$culturalProfile) {
            // Create default cultural profile if none exist
            $culturalProfile = UserCulturalProfile::create([
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $culturalProfile
        ]);
    }

    /**
     * Update the authenticated user's cultural profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCulturalProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'native_languages' => 'sometimes|array|nullable',
            'spoken_languages' => 'sometimes|array|nullable',
            'preferred_communication_language' => 'sometimes|string|max:50|nullable',
            'religion' => 'sometimes|in:muslim,christian,secular,other,prefer_not_to_say|nullable',
            'religiousness_level' => 'sometimes|in:very_religious,moderately_religious,not_religious,prefer_not_to_say|nullable',
            'ethnicity' => 'sometimes|string|max:100|nullable',
            'uzbek_region' => 'sometimes|string|max:100|nullable',
            'lifestyle_type' => 'sometimes|in:traditional,modern,mix_of_both|nullable',
            'gender_role_views' => 'sometimes|in:traditional,modern,flexible|nullable',
            'traditional_clothing_comfort' => 'sometimes|boolean|nullable',
            'uzbek_cuisine_knowledge' => 'sometimes|in:expert,good,basic,learning|nullable',
            'cultural_events_participation' => 'sometimes|in:very_active,active,sometimes,rarely|nullable',
            'halal_lifestyle' => 'sometimes|boolean|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create cultural profile
        $culturalProfile = $user->culturalProfile;
        if (!$culturalProfile) {
            $culturalProfile = new UserCulturalProfile(['user_id' => $user->id]);
        }

        // Update cultural profile with validated data
        $culturalProfile->fill($validator->validated());
        $culturalProfile->save();

        return response()->json([
            'success' => true,
            'message' => 'Cultural profile updated successfully',
            'data' => $culturalProfile
        ]);
    }
}
