<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserPhysicalProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PhysicalProfileController extends Controller
{
    /**
     * Get the authenticated user's physical profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPhysicalProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $physicalProfile = $user->physicalProfile;

        if (!$physicalProfile) {
            // Create default physical profile if none exist
            $physicalProfile = UserPhysicalProfile::create([
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $physicalProfile
        ]);
    }

    /**
     * Update the authenticated user's physical profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePhysicalProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'height' => 'sometimes|integer|min:100|max:250|nullable', // in cm
            'body_type' => 'sometimes|in:slim,athletic,average,curvy,plus_size|nullable',
            'hair_color' => 'sometimes|string|max:50|nullable',
            'eye_color' => 'sometimes|string|max:50|nullable',
            'fitness_level' => 'sometimes|in:very_active,active,moderate,sedentary|nullable',
            'dietary_restrictions' => 'sometimes|array|nullable',
            'smoking_status' => 'sometimes|in:never,socially,regularly,trying_to_quit|nullable',
            'drinking_status' => 'sometimes|in:never,socially,regularly,only_special_occasions|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create physical profile
        $physicalProfile = $user->physicalProfile;
        if (!$physicalProfile) {
            $physicalProfile = new UserPhysicalProfile(['user_id' => $user->id]);
        }

        // Update physical profile with validated data
        $physicalProfile->fill($validator->validated());
        $physicalProfile->save();

        return response()->json([
            'success' => true,
            'message' => 'Physical profile updated successfully',
            'data' => $physicalProfile
        ]);
    }
}
