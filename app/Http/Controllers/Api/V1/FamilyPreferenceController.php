<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserFamilyPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FamilyPreferenceController extends Controller
{
    /**
     * Get the authenticated user's family preferences
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFamilyPreferences(Request $request): JsonResponse
    {
        $user = $request->user();
        $familyPreferences = $user->familyPreference;

        if (!$familyPreferences) {
            // Create default family preferences if none exist
            $familyPreferences = UserFamilyPreference::create([
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $familyPreferences
        ]);
    }

    /**
     * Update the authenticated user's family preferences
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateFamilyPreferences(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'family_importance' => 'sometimes|in:very_important,important,somewhat_important,not_important|nullable',
            'wants_children' => 'sometimes|in:yes,no,maybe,have_and_want_more,have_and_dont_want_more|nullable',
            'number_of_children_wanted' => 'sometimes|integer|min:0|max:10|nullable',
            'living_with_family' => 'sometimes|boolean|nullable',
            'family_approval_important' => 'sometimes|boolean|nullable',
            'marriage_timeline' => 'sometimes|in:within_1_year,1_2_years,2_5_years,someday,never|nullable',
            'previous_marriages' => 'sometimes|integer|min:0|max:10|nullable',
            'homemaker_preference' => 'sometimes|in:yes,no,flexible,both_work|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create family preferences
        $familyPreferences = $user->familyPreference;
        if (!$familyPreferences) {
            $familyPreferences = new UserFamilyPreference(['user_id' => $user->id]);
        }

        // Update family preferences with validated data
        $familyPreferences->fill($validator->validated());
        $familyPreferences->save();

        return response()->json([
            'success' => true,
            'message' => 'Family preferences updated successfully',
            'data' => $familyPreferences
        ]);
    }
}
