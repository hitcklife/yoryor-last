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
            'status' => 'success',
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

        // Get input data - your app sends the exact format we expect
        $input = $request->all();

        // Handle boolean normalization for specific fields
        if (array_key_exists('family_approval_important', $input)) {
            $input['family_approval_important'] = $this->normalizeBooleanish($input['family_approval_important']);
        }
        if (array_key_exists('living_with_family', $input)) {
            $input['living_with_family'] = $this->normalizeBooleanish($input['living_with_family']);
        }

        // Validate the request data
        $validator = Validator::make($input, [
            'marriage_intention' => 'sometimes|in:seeking_marriage,open_to_marriage,not_ready_yet,undecided|nullable',
            'children_preference' => 'sometimes|in:want_children,have_and_want_more,have_and_dont_want_more,dont_want_children,undecided|nullable',
            'current_children' => 'sometimes|integer|min:0|max:20|nullable',
            'family_values' => 'sometimes|array|nullable',
            'family_values.*' => 'sometimes|in:close_knit,traditional,family_first,independent,supportive,respect_elders',
            'living_situation' => 'sometimes|in:alone,with_family,with_roommates,with_partner,other|nullable',
            'family_involvement' => 'sometimes|string|max:1000|nullable',
            'marriage_timeline' => 'sometimes|in:within_6_months,within_1_year,within_2_years,within_5_years,no_timeline|nullable',
            'family_importance' => 'sometimes|in:extremely_important,very_important,moderately_important,somewhat_important,not_important|nullable',
            'family_approval_important' => 'sometimes|boolean|nullable',
            'previous_marriages' => 'sometimes|integer|min:0|max:10|nullable',
            'homemaker_preference' => 'sometimes|in:prefer_traditional_roles,both_work_equally,flexible_arrangement,career_focused,no_preference|nullable',
            
            // Keep backward compatibility
            'number_of_children_wanted' => 'sometimes|integer|min:0|max:20|nullable',
            'living_with_family' => 'sometimes|boolean|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
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
            'status' => 'success',
            'message' => 'Family preferences updated successfully',
            'data' => $familyPreferences
        ]);
    }

    private function normalizeBooleanish($value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value !== 0;
        }
        $v = strtolower(trim((string)$value));
        $truthy = ['true', '1', 'yes', 'y', 'on', 'agree'];
        $falsy = ['false', '0', 'no', 'n', 'off', 'disagree'];
        if (in_array($v, $truthy, true)) {
            return true;
        }
        if (in_array($v, $falsy, true)) {
            return false;
        }
        return null;
    }

}
