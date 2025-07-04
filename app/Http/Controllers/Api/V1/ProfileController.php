<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use App\Services\ImageProcessingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    protected $imageProcessingService;

    public function __construct(ImageProcessingService $imageProcessingService)
    {
        $this->imageProcessingService = $imageProcessingService;
    }

    /**
     * Display a listing of the profiles.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthorizationException
     */
    public function index(Request $request)
    {
        // Check if the user is authorized to view any profiles
        $this->authorize('viewAny', Profile::class);

        $profiles = Profile::with([
            'user:id,email,phone,last_active_at,registration_completed',
            'user.profilePhoto:id,user_id,thumbnail_url,medium_url,original_url',
            'country:id,name,code'
        ])->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $profiles
        ]);
    }

    /**
     * Display the specified profile.
     *
     * @param Profile $profile
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthorizationException
     */
    public function show(Profile $profile)
    {
        // Check if the user is authorized to view the profile
        $this->authorize('view', $profile);

        $profile->load([
            'user:id,email,phone,last_active_at,registration_completed',
            'user.photos' => function($query) {
                $query->approved()->ordered()->select('id', 'user_id', 'original_url', 'medium_url', 'thumbnail_url', 'is_profile_photo', 'order');
            },
            'country:id,name,code'
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->transformProfile($profile)
        ]);
    }

    /**
     * Update the specified profile.
     *
     * @param Request $request
     * @param Profile $profile
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthorizationException
     */
    public function update(Request $request, Profile $profile)
    {
        // Check if the user is authorized to update the profile
        $this->authorize('update', $profile);

        $validated = $request->validate([
            'first_name' => ['sometimes', 'string', 'max:50'],
            'last_name' => ['sometimes', 'string', 'max:50'],
            'date_of_birth' => ['sometimes', 'date', 'before:-18 years'],
            'gender' => ['sometimes', 'in:male,female,non-binary,other'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:500'],
            'city' => ['sometimes', 'nullable', 'string', 'max:85'],
            'state' => ['sometimes', 'nullable', 'string', 'max:50'],
            'province' => ['sometimes', 'nullable', 'string', 'max:50'],
            'country_id' => ['sometimes', 'nullable', 'exists:countries,id'],
            'profession' => ['sometimes', 'nullable', 'string', 'max:100'],
            'occupation' => ['sometimes', 'nullable', 'string', 'max:100'],
            'interests' => ['sometimes', 'array'],
            'interests.*' => ['string', 'max:50'],
            'looking_for' => ['sometimes', 'in:casual,serious,friendship,all'],
        ]);

        try {
            DB::transaction(function() use ($profile, $validated) {
                // Calculate age if date_of_birth is provided
                if (isset($validated['date_of_birth'])) {
                    $validated['age'] = \Carbon\Carbon::parse($validated['date_of_birth'])->age;
                }

                // Check if profile is being completed
                $wasCompleted = $this->isProfileComplete($profile);
                
                $profile->update($validated);

                // Update completion status if profile wasn't complete before
                if (!$wasCompleted && $this->isProfileComplete($profile->fresh())) {
                    $profile->update(['profile_completed_at' => now()]);
                }
            });

            // Load fresh data with relationships
            $profile->load([
                'user:id,email,phone,last_active_at,registration_completed',
                'user.profilePhoto:id,user_id,thumbnail_url,medium_url,original_url',
                'country:id,name,code'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => $this->transformProfile($profile)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified profile.
     *
     * @param Profile $profile
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Profile $profile)
    {
        // Check if the user is authorized to delete the profile
        $this->authorize('delete', $profile);

        $profile->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile deleted successfully'
        ]);
    }

    /**
     * Get the authenticated user's profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function myProfile(Request $request)
    {
        $user = $request->user();
        
        $profile = $user->profile()->with([
            'country:id,name,code'
        ])->first();

        if (!$profile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profile not found',
                'error_code' => 'profile_not_found'
            ], 404);
        }

        // Load user's photos
        $user->load([
            'photos' => function($query) {
                $query->approved()->ordered()->select('id', 'user_id', 'original_url', 'medium_url', 'thumbnail_url', 'is_profile_photo', 'order');
            }
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $this->transformProfile($profile, true)
        ]);
    }

    /**
     * Get profile completion status
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompletionStatus(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        if (!$profile) {
            return response()->json([
                'status' => 'error',
                'message' => 'Profile not found',
                'error_code' => 'profile_not_found'
            ], 404);
        }

        $completionData = $this->calculateProfileCompletion($profile);

        return response()->json([
            'status' => 'success',
            'data' => $completionData
        ]);
    }

    /**
     * Transform profile data for API response
     */
    private function transformProfile(Profile $profile, bool $includePrivate = false): array
    {
        $data = [
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'gender' => $profile->gender,
            'age' => $profile->age,
            'city' => $profile->city,
            'state' => $profile->state,
            'province' => $profile->province,
            'country' => $profile->country,
            'bio' => $profile->bio,
            'profession' => $profile->profession,
            'occupation' => $profile->occupation,
            'interests' => $profile->interests,
            'looking_for' => $profile->looking_for,
            'profile_views' => $profile->profile_views,
            'profile_completed_at' => $profile->profile_completed_at,
            'created_at' => $profile->created_at,
            'updated_at' => $profile->updated_at,
        ];

        // Add user data
        if ($profile->user) {
            $data['user'] = [
                'id' => $profile->user->id,
                'email' => $includePrivate ? $profile->user->email : null,
                'phone' => $includePrivate ? $profile->user->phone : null,
                'last_active_at' => $profile->user->last_active_at,
                'registration_completed' => $profile->user->registration_completed,
                'is_online' => $profile->user->isOnline(),
            ];
        }

        // Add photos
        if ($profile->user && $profile->user->photos) {
            $data['photos'] = $profile->user->photos->map(function($photo) {
                return [
                    'id' => $photo->id,
                    'original_url' => $photo->original_url,
                    'medium_url' => $photo->medium_url,
                    'thumbnail_url' => $photo->thumbnail_url,
                    'is_profile_photo' => $photo->is_profile_photo,
                    'order' => $photo->order,
                    // Provide convenient image URL based on context
                    'image_url' => $this->imageProcessingService->getImageUrl(
                        $photo->original_url,
                        $photo->medium_url,
                        $photo->thumbnail_url,
                        'medium'
                    )
                ];
            });

            // Add profile photo shortcut
            $profilePhoto = $profile->user->photos->firstWhere('is_profile_photo', true);
            $data['profile_photo'] = $profilePhoto ? [
                'id' => $profilePhoto->id,
                'thumbnail_url' => $profilePhoto->thumbnail_url,
                'medium_url' => $profilePhoto->medium_url,
                'original_url' => $profilePhoto->original_url,
                'image_url' => $this->imageProcessingService->getImageUrl(
                    $profilePhoto->original_url,
                    $profilePhoto->medium_url,
                    $profilePhoto->thumbnail_url,
                    'medium'
                )
            ] : null;
        }

        // Add profile completion data for own profile
        if ($includePrivate) {
            $data['completion'] = $this->calculateProfileCompletion($profile);
        }

        return $data;
    }

    /**
     * Check if profile is complete
     */
    private function isProfileComplete(Profile $profile): bool
    {
        $requiredFields = ['first_name', 'date_of_birth', 'gender', 'city', 'bio'];
        
        foreach ($requiredFields as $field) {
            if (empty($profile->$field)) {
                return false;
            }
        }

        // Check if user has at least one photo
        $hasPhotos = $profile->user && $profile->user->photos()->approved()->count() > 0;
        
        return $hasPhotos;
    }

    /**
     * Calculate profile completion percentage and missing fields
     */
    private function calculateProfileCompletion(Profile $profile): array
    {
        $fields = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'date_of_birth' => 'Date of Birth',
            'gender' => 'Gender',
            'city' => 'City',
            'bio' => 'Bio',
            'profession' => 'Profession',
            'interests' => 'Interests',
        ];

        $completed = [];
        $missing = [];
        $totalFields = count($fields) + 1; // +1 for photos

        foreach ($fields as $field => $label) {
            if (!empty($profile->$field)) {
                $completed[] = $field;
            } else {
                $missing[] = [
                    'field' => $field,
                    'label' => $label
                ];
            }
        }

        // Check photos
        $hasPhotos = $profile->user && $profile->user->photos()->approved()->count() > 0;
        if ($hasPhotos) {
            $completed[] = 'photos';
        } else {
            $missing[] = [
                'field' => 'photos',
                'label' => 'Profile Photos'
            ];
        }

        $completionPercentage = round((count($completed) / $totalFields) * 100);

        return [
            'completion_percentage' => $completionPercentage,
            'is_complete' => $completionPercentage >= 80, // 80% threshold
            'completed_fields' => $completed,
            'missing_fields' => $missing,
            'total_fields' => $totalFields,
            'completed_count' => count($completed),
        ];
    }
}
