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
            'status' => 'success',
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

        // Get input data and handle legacy field mappings
        $input = $request->all();
        
        // Handle legacy field mappings for backward compatibility
        if (array_key_exists('profession', $input)) {
            $input['occupation'] = $input['profession'];
        }
        if (array_key_exists('company', $input)) {
            $input['employer'] = $input['company'];
        }
        if (array_key_exists('job_title', $input)) {
            $input['occupation'] = $input['job_title'];
        }
        if (array_key_exists('income', $input)) {
            $input['income_range'] = $input['income'];
        }

        // Validate the request data
        $validator = Validator::make($input, [
            'education_level' => 'sometimes|in:high_school,associate,bachelor,master,doctorate,professional,trade_school,other|nullable',
            'field_of_study' => 'sometimes|string|max:255|nullable',
            'work_status' => 'sometimes|in:full_time,part_time,self_employed,freelance,student,unemployed,retired|nullable',
            'occupation' => 'sometimes|string|max:255|nullable',
            'employer' => 'sometimes|string|max:255|nullable',
            'career_goals' => 'sometimes|array|nullable',
            'career_goals.*' => 'sometimes|in:entrepreneurship,leadership,expertise,work_life_balance,financial_success,make_impact',
            'income_range' => 'sometimes|in:under_25k,25k_50k,50k_75k,75k_100k,100k_150k,150k_plus,prefer_not_to_say|nullable',
            
            // Legacy fields for backward compatibility
            'profession' => 'sometimes|string|max:255|nullable',
            'company' => 'sometimes|string|max:255|nullable',
            'job_title' => 'sometimes|string|max:255|nullable',
            'income' => 'sometimes|string|max:100|nullable',
            'university_name' => 'sometimes|string|max:200|nullable',
            'owns_property' => 'sometimes|boolean|nullable',
            'financial_goals' => 'sometimes|string|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
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
            'status' => 'success',
            'message' => 'Career profile updated successfully',
            'data' => $careerProfile
        ]);
    }
}
