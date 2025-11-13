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
            'status' => 'success',
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
            'immigration_status' => 'sometimes|in:citizen,permanent_resident,work_visa,student_visa,tourist_visa,asylum_refugee,other|nullable',
            'years_in_current_country' => 'sometimes|integer|min:0|max:100|nullable',
            'plans_to_return_uzbekistan' => 'sometimes|in:definitely_yes,probably_yes,maybe,probably_no,definitely_no,undecided|nullable',
            'uzbekistan_visit_frequency' => 'sometimes|in:never,rarely,annually,twice_yearly,quarterly,monthly,frequently|nullable',
            'willing_to_relocate' => 'sometimes|in:no,within_city,within_state,within_country,internationally,for_right_person|nullable',
            'relocation_countries' => 'sometimes|array|nullable',
            'relocation_countries.*' => 'sometimes|in:uzbekistan,united_states,canada,united_kingdom,germany,australia,turkey,russia,kazakhstan,other',
            'preferred_locations' => 'sometimes|array|nullable',
            'preferred_locations.*' => 'sometimes|in:city_center,suburbs,countryside,near_family,near_work,quiet_area',
            'live_with_family' => 'sometimes|boolean|nullable',
            'future_location_plans' => 'sometimes|string|max:1000|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
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
            'status' => 'success',
            'message' => 'Location preferences updated successfully',
            'data' => $locationPreferences
        ]);
    }
}
