<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserPhysicalProfile;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PhysicalProfileController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    /**
     * Get the authenticated user's physical profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPhysicalProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->cacheService->remember(
                "user_physical_profile:{$user->id}",
                CacheService::TTL_MEDIUM,
                function() use ($user) {
                    $physicalProfile = $user->physicalProfile;

                    if (!$physicalProfile) {
                        // Create default physical profile if none exist
                        $physicalProfile = UserPhysicalProfile::create([
                            'user_id' => $user->id,
                        ]);
                    }

                    return response()->json([
                        'status' => 'success',
                        'data' => $physicalProfile
                    ]);
                },
                ["user_{$user->id}_profile"]
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get physical profile',
                'error' => $e->getMessage()
            ], 500);
        }
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

        // Get input data and handle legacy field mappings
        $input = $request->all();
        
        // Handle legacy field mappings for backward compatibility
        if (array_key_exists('smoking_status', $input)) {
            $input['smoking_habit'] = $input['smoking_status'];
        }
        if (array_key_exists('drinking_status', $input)) {
            $input['drinking_habit'] = $input['drinking_status'];
        }
        if (array_key_exists('diet', $input)) {
            $input['diet_preference'] = $input['diet'];
        }
        if (array_key_exists('fitness_level', $input)) {
            $input['exercise_frequency'] = $input['fitness_level'];
        }

        // Validate the request data
        $validator = Validator::make($input, [
            // Your app's fields
            'smoking_habit' => 'sometimes|in:never,socially,regularly,trying_to_quit|nullable',
            'drinking_habit' => 'sometimes|in:never,socially,occasionally,regularly|nullable',
            'exercise_frequency' => 'sometimes|in:never,rarely,1_2_week,3_4_week,daily|nullable',
            'diet_preference' => 'sometimes|in:everything,vegetarian,vegan,halal,kosher,pescatarian,keto|nullable',
            'pet_preference' => 'sometimes|in:love_pets,have_pets,allergic,dont_like,no_preference|nullable',
            'hobbies' => 'sometimes|array|nullable',
            'hobbies.*' => 'sometimes|in:reading,cooking,travel,sports,music,movies,gaming,art,photography,hiking,dancing,meditation',
            'sleep_schedule' => 'sometimes|string|max:255|nullable',
            
            // Legacy fields for backward compatibility
            'fitness_level' => 'sometimes|in:never,rarely,1_2_week,3_4_week,daily|nullable',
            'smoking_status' => 'sometimes|in:never,socially,regularly,trying_to_quit|nullable',
            'drinking_status' => 'sometimes|in:never,socially,occasionally,regularly|nullable',
            'diet' => 'sometimes|in:everything,vegetarian,vegan,halal,kosher,pescatarian,keto|nullable',
            
            // Existing fields
            'height' => 'sometimes|integer|min:100|max:250|nullable', // in cm
            'weight' => 'sometimes|numeric|min:30|max:300|nullable', // in kg
            'dietary_restrictions' => 'sometimes|array|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create physical profile
        $physicalProfile = $user->physicalProfile;
        if (!$physicalProfile) {
            $physicalProfile = new UserPhysicalProfile(['user_id' => $user->id]);
        }

        // Update physical profile with validated data
        $validatedData = $validator->validated();
        
        // Filter out only the fields that exist in the model
        $modelFields = $physicalProfile->getFillable();
        $dataToSave = array_intersect_key($validatedData, array_flip($modelFields));
        
        $physicalProfile->fill($dataToSave);
        $physicalProfile->save();

        // Clear user profile cache
        $this->cacheService->invalidateUserCaches($user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Physical profile updated successfully',
            'data' => $physicalProfile
        ]);
    }
}
