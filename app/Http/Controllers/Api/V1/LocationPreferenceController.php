<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserLocationPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class LocationPreferenceController extends Controller
{
    /**
     * Get the authenticated user's location preferences
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLocationPreferences(Request $request): JsonResponse
    {
        $user = $request->user();
        $locationPreferences = $user->locationPreference;

        if (!$locationPreferences) {
            // Create default location preferences if none exist
            $locationPreferences = UserLocationPreference::create([
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $locationPreferences
        ]);
    }

    /**
     * Update the authenticated user's location preferences
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateLocationPreferences(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'immigration_status' => 'sometimes|in:citizen,permanent_resident,work_visa,student,other|nullable',
            'years_in_current_country' => 'sometimes|integer|min:0|max:100|nullable',
            'plans_to_return_uzbekistan' => 'sometimes|in:yes,no,maybe,for_visits|nullable',
            'uzbekistan_visit_frequency' => 'sometimes|in:yearly,every_few_years,rarely,never|nullable',
            'willing_to_relocate' => 'sometimes|boolean|nullable',
            'relocation_countries' => 'sometimes|array|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create location preferences
        $locationPreferences = $user->locationPreference;
        if (!$locationPreferences) {
            $locationPreferences = new UserLocationPreference(['user_id' => $user->id]);
        }

        // Update location preferences with validated data
        $locationPreferences->fill($validator->validated());
        $locationPreferences->save();

        return response()->json([
            'success' => true,
            'message' => 'Location preferences updated successfully',
            'data' => $locationPreferences
        ]);
    }
}
