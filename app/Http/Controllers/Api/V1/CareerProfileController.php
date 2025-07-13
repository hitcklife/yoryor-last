<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserCareerProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CareerProfileController extends Controller
{
    /**
     * Get the authenticated user's career profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCareerProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $careerProfile = $user->careerProfile;

        if (!$careerProfile) {
            // Create default career profile if none exist
            $careerProfile = UserCareerProfile::create([
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $careerProfile
        ]);
    }

    /**
     * Update the authenticated user's career profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCareerProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'education_level' => 'sometimes|in:high_school,bachelors,masters,phd,vocational,other|nullable',
            'university_name' => 'sometimes|string|max:200|nullable',
            'income_range' => 'sometimes|in:prefer_not_to_say,under_25k,25k_50k,50k_75k,75k_100k,100k_plus|nullable',
            'owns_property' => 'sometimes|boolean|nullable',
            'financial_goals' => 'sometimes|string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create career profile
        $careerProfile = $user->careerProfile;
        if (!$careerProfile) {
            $careerProfile = new UserCareerProfile(['user_id' => $user->id]);
        }

        // Update career profile with validated data
        $careerProfile->fill($validator->validated());
        $careerProfile->save();

        return response()->json([
            'success' => true,
            'message' => 'Career profile updated successfully',
            'data' => $careerProfile
        ]);
    }
}
