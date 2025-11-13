<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserReport;
use App\Services\ImageProcessingService;
use App\Services\CacheService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    protected $imageProcessingService;
    protected $cacheService;

    public function __construct(ImageProcessingService $imageProcessingService, CacheService $cacheService)
    {
        $this->imageProcessingService = $imageProcessingService;
        $this->cacheService = $cacheService;
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
//        $this->authorize('viewAny', Profile::class);

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
//        $this->authorize('view', $profile);

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
            'looking_for_relationship' => ['sometimes', 'in:casual,serious,friendship,open'],
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

            // Clear profile caches after update
            $this->cacheService->invalidateUserCaches($profile->user_id);

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
//        $this->authorize('delete', $profile);

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

        return $this->cacheService->cacheUserProfile($user->id, function() use ($user) {
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
                'message' => 'Profile retrieved successfully',
                'data' => $this->transformProfile($profile, true)
            ]);
        });
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
     * Get another user's profile with comprehensive information
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserProfile(Request $request, int $userId)
    {
        try {
            $currentUser = $request->user();
            
            // Use caching with composite key that includes both users to handle relationship status
            $cacheKey = "user_profile_{$userId}_viewer_{$currentUser->id}";
            
            return $this->cacheService->remember($cacheKey, CacheService::TTL_MEDIUM, function() use ($currentUser, $userId) {
                $targetUser = User::with([
                    'profile.country:id,name,code',
                    'profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo',
                    'photos' => function($query) {
                        $query->where('is_private', false)
                              ->where('status', 'approved')
                              ->orderBy('order', 'asc')
                              ->select('id', 'user_id', 'original_url', 'thumbnail_url', 'medium_url', 'is_profile_photo', 'order', 'status');
                    }
                ])->findOrFail($userId);

                // Check if users can view each other's profiles
                if (!$currentUser->canViewProfile($targetUser)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'You cannot view this profile',
                        'error_code' => 'profile_blocked'
                    ], 403);
                }

                $profile = $targetUser->profile;
                if (!$profile) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Profile not found',
                        'error_code' => 'profile_not_found'
                    ], 404);
                }

                // Load comprehensive profile data with all relationships
                $targetUser->load([
                    'profile.country:id,name,code',
                    'preference',
                    'culturalProfile',
                    'familyPreference',
                    'locationPreference',
                    'careerProfile',
                    'physicalProfile',
                    'photos' => function($query) {
                        $query->approved()->public()->ordered()->select('id', 'user_id', 'original_url', 'medium_url', 'thumbnail_url', 'is_profile_photo', 'order');
                    },
                    'profilePhoto:id,user_id,original_url,medium_url,thumbnail_url',
                    'activeStories' => function($query) {
                        $query->select('id', 'user_id', 'media_url', 'type', 'created_at');
                    }
                ]);

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'profile' => $this->transformComprehensiveProfile($targetUser, false),
                        'relationship_status' => [
                            'is_matched' => $currentUser->hasMatched($targetUser),
                            'is_blocked' => $currentUser->hasBlocked($targetUser),
                            'has_reported' => $currentUser->hasReported($targetUser),
                            'has_liked' => $currentUser->hasLiked($targetUser),
                            'has_disliked' => $currentUser->hasDisliked($targetUser),
                        ]
                    ]
                ]);
            }, ["user_{$userId}_profile", "user_{$currentUser->id}_relationships"]);

            // Note: We increment profile views outside cache to ensure it's always counted
            // This is done after the cached response is returned
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
                'error_code' => 'user_not_found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        } finally {
            // Increment profile views outside cache (fire and forget)
            try {
                if (isset($userId)) {
                    \DB::table('profiles')->where('user_id', $userId)->increment('profile_views');
                }
            } catch (\Exception $e) {
                // Log but don't fail request
                \Log::warning('Failed to increment profile views', ['user_id' => $userId, 'error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Block a user profile
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function blockUser(Request $request, int $userId)
    {
        try {
            $currentUser = $request->user();
            $targetUser = User::with('profile:id,user_id,first_name,last_name')->findOrFail($userId);

            if ($currentUser->id === $targetUser->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot block yourself',
                    'error_code' => 'cannot_block_self'
                ], 400);
            }

            if ($currentUser->hasBlocked($targetUser)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User is already blocked',
                    'error_code' => 'already_blocked'
                ], 400);
            }

            $validated = $request->validate([
                'reason' => 'sometimes|string|max:255'
            ]);

            // Create the block record
            $currentUser->blockedUsers()->create([
                'blocked_id' => $targetUser->id,
                'reason' => $validated['reason'] ?? null
            ]);

            // Remove any existing matches between the users
            $currentUser->matches()->where('matched_user_id', $targetUser->id)->delete();
            $targetUser->matches()->where('matched_user_id', $currentUser->id)->delete();

            // Clear relationship caches for both users
            $this->cacheService->invalidateUserCaches($currentUser->id);
            $this->cacheService->invalidateUserCaches($targetUser->id);

            return response()->json([
                'status' => 'success',
                'message' => 'User blocked successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
                'error_code' => 'user_not_found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to block user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Report a user profile
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function reportUser(Request $request, int $userId)
    {
        try {
            $currentUser = $request->user();
            $targetUser = User::with('profile:id,user_id,first_name,last_name')->findOrFail($userId);

            if ($currentUser->id === $targetUser->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot report yourself',
                    'error_code' => 'cannot_report_self'
                ], 400);
            }

            if ($currentUser->hasReported($targetUser)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You have already reported this user',
                    'error_code' => 'already_reported'
                ], 400);
            }

            $validated = $request->validate([
                'reason' => 'required|string|in:' . implode(',', array_keys(\App\Models\UserReport::getReportReasons())),
                'description' => 'sometimes|string|max:1000',
                'metadata' => 'sometimes|array'
            ]);

            // Create the report record
            $currentUser->reportsMade()->create([
                'reported_id' => $targetUser->id,
                'reason' => $validated['reason'],
                'description' => $validated['description'] ?? null,
                'metadata' => $validated['metadata'] ?? null,
                'status' => 'pending'
            ]);

            // Clear relationship caches for the reporting user
            $this->cacheService->invalidateUserCaches($currentUser->id);

            return response()->json([
                'status' => 'success',
                'message' => 'User reported successfully. We will review your report.',
                'data' => [
                    'report_reasons' => \App\Models\UserReport::getReportReasons()
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
                'error_code' => 'user_not_found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to report user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available report reasons
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReportReasons()
    {
        return response()->json([
            'status' => 'success',
            'data' => \App\Models\UserReport::getReportReasons()
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
            'date_of_birth' => $profile->date_of_birth,
            'city' => $profile->city,
            'state' => $profile->state,
            'province' => $profile->province,
            'country' => $profile->country,
            'bio' => $profile->bio,
            'latitude' => $profile->latitude,
            'longitude' => $profile->longitude,
            'profession' => $profile->profession,
            'occupation' => $profile->occupation,
            'education' => $profile->education ?? null,
            'height' => $profile->height ?? null,
            'religion' => $profile->religion ?? null,
            'drinking' => $profile->drinking ?? null,
            'smoking' => $profile->smoking ?? null,
            'languages' => $profile->languages ?? null,
            'interests' => $profile->interests,
            'looking_for_relationship' => $profile->looking_for_relationship,
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

            // Add profile photo shortcut using the new method
            $data['profile_photo'] = [
                'thumbnail_url' => $profile->user->getProfilePhotoUrl('thumbnail'),
                'medium_url' => $profile->user->getProfilePhotoUrl('medium'),
                'original_url' => $profile->user->getProfilePhotoUrl('original'),
                'image_url' => $profile->user->getProfilePhotoUrl('medium')
            ];
        } else {
            // No photos available, use fallback
            $data['photos'] = [];
            $data['profile_photo'] = [
                'thumbnail_url' => $profile->user->getProfilePhotoUrl('thumbnail'),
                'medium_url' => $profile->user->getProfilePhotoUrl('medium'),
                'original_url' => $profile->user->getProfilePhotoUrl('original'),
                'image_url' => $profile->user->getProfilePhotoUrl('medium')
            ];
        }

        // Add profile completion data for own profile
        if ($includePrivate) {
            $completionData = $this->calculateProfileCompletion($profile);
            $data['completion'] = $completionData;
            $data['completion_percentage'] = $completionData['percentage'] ?? 0;
            
            // Add verification status
            $data['verification_status'] = [
                'identity_verified' => false,  // Simplified for now
                'photo_verified' => false,      // Simplified for now
                'employment_verified' => false  // Simplified for now
            ];
        }

        return $data;
    }

    /**
     * Transform comprehensive profile data including all relationships
     */
    private function transformComprehensiveProfile(User $user, bool $includePrivate = false): array
    {
        $profile = $user->profile;
        
        $data = [
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'full_name' => $user->full_name,
            'gender' => $profile->gender,
            'age' => $profile->age,
            'date_of_birth' => $profile->date_of_birth,
            'city' => $profile->city,
            'state' => $profile->state,
            'province' => $profile->province,
            'country' => $profile->country,
            'bio' => $profile->bio,
            'profession' => $profile->profession,
            'occupation' => $profile->occupation,
            'interests' => $profile->interests,
            'looking_for_relationship' => $profile->looking_for_relationship,
            'profile_views' => $profile->profile_views,
            'profile_completed_at' => $profile->profile_completed_at,
            'created_at' => $profile->created_at,
            'updated_at' => $profile->updated_at,
        ];

        // Add user data
        $data['user'] = [
            'id' => $user->id,
            'email' => $includePrivate ? $user->email : null,
            'phone' => $includePrivate ? $user->phone : null,
            'last_active_at' => $user->last_active_at,
            'registration_completed' => $user->registration_completed,
            'is_online' => $user->isOnline(),
            'online_status' => $user->getOnlineStatus(),
        ];

        // Add photos
        $data['photos'] = $user->photos->map(function($photo) {
            return [
                'id' => $photo->id,
                'original_url' => $photo->original_url,
                'medium_url' => $photo->medium_url,
                'thumbnail_url' => $photo->thumbnail_url,
                'is_profile_photo' => $photo->is_profile_photo,
                'order' => $photo->order,
                'image_url' => $this->imageProcessingService->getImageUrl(
                    $photo->original_url,
                    $photo->medium_url,
                    $photo->thumbnail_url,
                    'medium'
                )
            ];
        });

        // Profile photo shortcut
        $data['profile_photo'] = [
            'thumbnail_url' => $user->getProfilePhotoUrl('thumbnail'),
            'medium_url' => $user->getProfilePhotoUrl('medium'),
            'original_url' => $user->getProfilePhotoUrl('original'),
            'image_url' => $user->getProfilePhotoUrl('medium')
        ];

        // Add comprehensive profile sections
        $data['preferences'] = $user->preference;
        $data['cultural_profile'] = $user->culturalProfile;
        $data['family_preferences'] = $user->familyPreference;
        $data['location_preferences'] = $user->locationPreference;
        $data['career_profile'] = $user->careerProfile;
        $data['physical_profile'] = $user->physicalProfile;

        // Add active stories
        $data['active_stories'] = $user->activeStories->map(function($story) {
            return [
                'id' => $story->id,
                'media_url' => $story->media_url,
                'media_type' => $story->type, // Using 'type' from database
                'created_at' => $story->created_at,
            ];
        });

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

    /**
     * Get discovery profiles for the grid view
     */
    public function getDiscoveryProfiles(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            $page = $request->input('page', 1);
            $filters = $request->input('filters', []);
        
        // Get excluded user IDs (already liked, disliked, or matched)
        $excludedUserIds = collect()
            ->merge(\App\Models\Like::where('user_id', $user->id)->pluck('liked_user_id'))
            ->merge(\App\Models\Dislike::where('user_id', $user->id)->pluck('disliked_user_id'))
            ->merge(\App\Models\MatchModel::where('user_id', $user->id)->pluck('matched_user_id'))
            ->merge(\App\Models\MatchModel::where('matched_user_id', $user->id)->pluck('user_id'))
            ->push($user->id) // Exclude current user
            ->unique()
            ->values()
            ->toArray();
        
        // Build query with filters
        $query = User::with([
            'profile:user_id,first_name,last_name,age,bio,occupation,city,country_code',
            'profilePhoto:id,user_id,original_url,thumbnail_url,medium_url',
            'photos:id,user_id,original_url,thumbnail_url,medium_url,order',
            'culturalProfile:user_id,ethnicity,religion,religiousness_level,spoken_languages',
            'careerProfile:user_id,education_level,field_of_study,work_status,job_title'
        ])
        ->whereNotIn('id', $excludedUserIds)
        ->where('registration_completed', true)
        ->whereNull('disabled_at')
        ->whereHas('profile')
        ->whereHas('photos');
        
        // Apply age filter using pre-computed age column
        if (!empty($filters['ageMin']) || !empty($filters['ageMax'])) {
            $query->whereHas('profile', function($q) use ($filters) {
                if (!empty($filters['ageMin'])) {
                    $q->where('age', '>=', $filters['ageMin']);
                }
                if (!empty($filters['ageMax'])) {
                    $q->where('age', '<=', $filters['ageMax']);
                }
            });
        }
        
        // Apply profession filter
        if (!empty($filters['profession'])) {
            $query->whereHas('profile', function($q) use ($filters) {
                $q->where('occupation', 'like', '%' . $filters['profession'] . '%');
            });
        }
        
        // Apply religion filter
        if (!empty($filters['religion'])) {
            $query->whereHas('culturalProfile', function($q) use ($filters) {
                $q->where('religion', $filters['religion']);
            });
        }
        
        // Apply education filter
        if (!empty($filters['education'])) {
            $query->whereHas('careerProfile', function($q) use ($filters) {
                $q->where('education_level', $filters['education']);
            });
        }
        
        $profiles = $query
            ->orderBy('last_active_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(20) // Load 20 profiles at a time
            ->skip(($page - 1) * 20)
            ->get()
            ->map(function($profile) {
                return $this->formatProfileData($profile);
            });
        
        return response()->json([
            'status' => 'success',
            'profiles' => $profiles,
            'hasMore' => count($profiles) === 20
        ]);
        
        } catch (\Exception $e) {
            \Log::error('Error in getDiscoveryProfiles: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'page' => $request->input('page', 1),
                'filters' => $request->input('filters', [])
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load profiles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function formatProfileData($profile)
    {
        // Use pre-computed age from database
        $age = $profile->profile?->age;
            
        // Calculate distance (mock for now)
        $distance = rand(1, 50);
        
        // Get primary photo
        $primaryPhoto = $profile->profilePhoto?->medium_url ?? 
                       ($profile->photos->first()?->medium_url ?? null);
        
        // Get additional photos
        $additionalPhotos = $profile->photos->take(5)->map(function($photo) {
            return [
                'id' => $photo->id,
                'url' => $photo->medium_url,
                'thumbnail' => $photo->thumbnail_url
            ];
        })->toArray();
        
        // Calculate compatibility score (mock)
        $compatibilityScore = rand(65, 95);
        
        return [
            'id' => $profile->id,
            'name' => $profile->profile?->first_name ?? 'User',
            'age' => $age,
            'distance' => $distance,
            'location' => $profile->profile?->city ?? 'Unknown',
            'bio' => $profile->profile?->bio,
            'occupation' => $profile->profile?->occupation,
            'primary_photo' => $primaryPhoto,
            'photos' => $additionalPhotos,
            'compatibility_score' => $compatibilityScore,
            'is_online' => $profile->last_active_at && $profile->last_active_at->gt(now()->subMinutes(5)),
            'verified' => $profile->email_verified_at !== null,
            
            // Profile completion indicators
            'has_bio' => !empty($profile->profile?->bio),
            'has_occupation' => !empty($profile->profile?->occupation),
            'photos_count' => $profile->photos->count(),
            
            // Cultural/Religious info
            'religion' => $profile->culturalProfile?->religion,
            'education' => $profile->careerProfile?->education_level,
            'religiousness' => $profile->culturalProfile?->religiousness_level,
        ];
    }
}
