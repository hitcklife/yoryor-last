<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use App\Models\UserPreference;
use App\Models\UserCulturalProfile;
use App\Models\UserFamilyPreference;
use App\Models\UserLocationPreference;
use App\Models\UserCareerProfile;
use App\Models\UserPhysicalProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ComprehensiveProfileController extends Controller
{
    /**
     * Get all profile information for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllProfileData(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Load all profile relationships
        $user->load([
            'profile',
            'preference',
            'culturalProfile',
            'familyPreference',
            'locationPreference',
            'careerProfile',
            'physicalProfile'
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'basic_profile' => $user->profile,
                'preferences' => $user->preference,
                'cultural_profile' => $user->culturalProfile,
                'family_preferences' => $user->familyPreference,
                'location_preferences' => $user->locationPreference,
                'career_profile' => $user->careerProfile,
                'physical_profile' => $user->physicalProfile,
            ]
        ]);
    }

    /**
     * Update all profile information for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAllProfileData(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate all profile data
        $validator = Validator::make($request->all(), [
            // Basic profile validation
            'basic_profile' => 'sometimes|array',
            'basic_profile.first_name' => 'sometimes|string|max:50',
            'basic_profile.last_name' => 'sometimes|string|max:50',
            'basic_profile.gender' => 'sometimes|in:male,female,non-binary,other',
            'basic_profile.date_of_birth' => 'sometimes|date|before:today',
            'basic_profile.city' => 'sometimes|string|max:85',
            'basic_profile.state' => 'sometimes|string|max:50',
            'basic_profile.province' => 'sometimes|string|max:50',
            'basic_profile.bio' => 'sometimes|string|max:1000',
            'basic_profile.interests' => 'sometimes|array',
            'basic_profile.looking_for' => 'sometimes|in:casual,serious,friendship,all',
            'basic_profile.occupation' => 'sometimes|string|max:100',
            'basic_profile.profession' => 'sometimes|string|max:100',

            // Preferences validation
            'preferences' => 'sometimes|array',
            'preferences.search_radius' => 'sometimes|integer|min:1|max:500',
            'preferences.country' => 'sometimes|string|size:2',
            'preferences.preferred_genders' => 'sometimes|array',
            'preferences.hobbies_interests' => 'sometimes|array',
            'preferences.min_age' => 'sometimes|integer|min:18|max:120',
            'preferences.max_age' => 'sometimes|integer|min:18|max:120',
            'preferences.languages_spoken' => 'sometimes|array',
            'preferences.deal_breakers' => 'sometimes|array',
            'preferences.must_haves' => 'sometimes|array',
            'preferences.distance_unit' => 'sometimes|in:km,miles',
            'preferences.show_me_globally' => 'sometimes|boolean',
            'preferences.notification_preferences' => 'sometimes|array',

            // Cultural profile validation
            'cultural_profile' => 'sometimes|array',
            'cultural_profile.native_languages' => 'sometimes|array',
            'cultural_profile.spoken_languages' => 'sometimes|array',
            'cultural_profile.preferred_communication_language' => 'sometimes|string|max:50',
            'cultural_profile.religion' => 'sometimes|in:muslim,christian,secular,other,prefer_not_to_say',
            'cultural_profile.religiousness_level' => 'sometimes|in:very_religious,moderately_religious,not_religious,prefer_not_to_say',
            'cultural_profile.ethnicity' => 'sometimes|string|max:100',
            'cultural_profile.uzbek_region' => 'sometimes|string|max:100',
            'cultural_profile.lifestyle_type' => 'sometimes|in:traditional,modern,mix_of_both',
            'cultural_profile.gender_role_views' => 'sometimes|in:traditional,modern,flexible',
            'cultural_profile.traditional_clothing_comfort' => 'sometimes|boolean',
            'cultural_profile.uzbek_cuisine_knowledge' => 'sometimes|in:expert,good,basic,learning',
            'cultural_profile.cultural_events_participation' => 'sometimes|in:very_active,active,sometimes,rarely',
            'cultural_profile.halal_lifestyle' => 'sometimes|boolean',

            // Family preferences validation
            'family_preferences' => 'sometimes|array',
            'family_preferences.family_importance' => 'sometimes|in:very_important,important,somewhat_important,not_important',
            'family_preferences.wants_children' => 'sometimes|in:yes,no,maybe,have_and_want_more,have_and_dont_want_more',
            'family_preferences.number_of_children_wanted' => 'sometimes|integer|min:0|max:10',
            'family_preferences.living_with_family' => 'sometimes|boolean',
            'family_preferences.family_approval_important' => 'sometimes|boolean',
            'family_preferences.marriage_timeline' => 'sometimes|in:within_1_year,1_2_years,2_5_years,someday,never',
            'family_preferences.previous_marriages' => 'sometimes|integer|min:0|max:10',
            'family_preferences.homemaker_preference' => 'sometimes|in:yes,no,flexible,both_work',

            // Location preferences validation
            'location_preferences' => 'sometimes|array',
            'location_preferences.immigration_status' => 'sometimes|in:citizen,permanent_resident,work_visa,student,other',
            'location_preferences.years_in_current_country' => 'sometimes|integer|min:0|max:100',
            'location_preferences.plans_to_return_uzbekistan' => 'sometimes|in:yes,no,maybe,for_visits',
            'location_preferences.uzbekistan_visit_frequency' => 'sometimes|in:yearly,every_few_years,rarely,never',
            'location_preferences.willing_to_relocate' => 'sometimes|boolean',
            'location_preferences.relocation_countries' => 'sometimes|array',

            // Career profile validation
            'career_profile' => 'sometimes|array',
            'career_profile.education_level' => 'sometimes|in:high_school,bachelors,masters,phd,vocational,other',
            'career_profile.university_name' => 'sometimes|string|max:200',
            'career_profile.income_range' => 'sometimes|in:prefer_not_to_say,under_25k,25k_50k,50k_75k,75k_100k,100k_plus',
            'career_profile.owns_property' => 'sometimes|boolean',
            'career_profile.financial_goals' => 'sometimes|string',

            // Physical profile validation
            'physical_profile' => 'sometimes|array',
            'physical_profile.height' => 'sometimes|integer|min:100|max:250',
            'physical_profile.body_type' => 'sometimes|in:slim,athletic,average,curvy,plus_size',
            'physical_profile.hair_color' => 'sometimes|string|max:50',
            'physical_profile.eye_color' => 'sometimes|string|max:50',
            'physical_profile.fitness_level' => 'sometimes|in:very_active,active,moderate,sedentary',
            'physical_profile.dietary_restrictions' => 'sometimes|array',
            'physical_profile.smoking_status' => 'sometimes|in:never,socially,regularly,trying_to_quit',
            'physical_profile.drinking_status' => 'sometimes|in:never,socially,regularly,only_special_occasions',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update basic profile
            if ($request->has('basic_profile')) {
                $this->updateBasicProfile($user, $request->input('basic_profile'));
            }

            // Update preferences
            if ($request->has('preferences')) {
                $this->updatePreferences($user, $request->input('preferences'));
            }

            // Update cultural profile
            if ($request->has('cultural_profile')) {
                $this->updateCulturalProfile($user, $request->input('cultural_profile'));
            }

            // Update family preferences
            if ($request->has('family_preferences')) {
                $this->updateFamilyPreferences($user, $request->input('family_preferences'));
            }

            // Update location preferences
            if ($request->has('location_preferences')) {
                $this->updateLocationPreferences($user, $request->input('location_preferences'));
            }

            // Update career profile
            if ($request->has('career_profile')) {
                $this->updateCareerProfile($user, $request->input('career_profile'));
            }

            // Update physical profile
            if ($request->has('physical_profile')) {
                $this->updatePhysicalProfile($user, $request->input('physical_profile'));
            }

            DB::commit();

            // Return updated profile data
            $user->load([
                'profile',
                'preference',
                'culturalProfile',
                'familyPreference',
                'locationPreference',
                'careerProfile',
                'physicalProfile'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'basic_profile' => $user->profile,
                    'preferences' => $user->preference,
                    'cultural_profile' => $user->culturalProfile,
                    'family_preferences' => $user->familyPreference,
                    'location_preferences' => $user->locationPreference,
                    'career_profile' => $user->careerProfile,
                    'physical_profile' => $user->physicalProfile,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update basic profile
     */
    private function updateBasicProfile(User $user, array $data): void
    {
        $profile = $user->profile;
        if (!$profile) {
            $profile = new Profile(['user_id' => $user->id]);
        }

        $profile->fill($data);
        $profile->save();
    }

    /**
     * Update preferences
     */
    private function updatePreferences(User $user, array $data): void
    {
        $preferences = $user->preference;
        if (!$preferences) {
            $preferences = new UserPreference(['user_id' => $user->id]);
        }

        $preferences->fill($data);
        $preferences->save();
    }

    /**
     * Update cultural profile
     */
    private function updateCulturalProfile(User $user, array $data): void
    {
        $culturalProfile = $user->culturalProfile;
        if (!$culturalProfile) {
            $culturalProfile = new UserCulturalProfile(['user_id' => $user->id]);
        }

        $culturalProfile->fill($data);
        $culturalProfile->save();
    }

    /**
     * Update family preferences
     */
    private function updateFamilyPreferences(User $user, array $data): void
    {
        $familyPreferences = $user->familyPreference;
        if (!$familyPreferences) {
            $familyPreferences = new UserFamilyPreference(['user_id' => $user->id]);
        }

        $familyPreferences->fill($data);
        $familyPreferences->save();
    }

    /**
     * Update location preferences
     */
    private function updateLocationPreferences(User $user, array $data): void
    {
        $locationPreferences = $user->locationPreference;
        if (!$locationPreferences) {
            $locationPreferences = new UserLocationPreference(['user_id' => $user->id]);
        }

        $locationPreferences->fill($data);
        $locationPreferences->save();
    }

    /**
     * Update career profile
     */
    private function updateCareerProfile(User $user, array $data): void
    {
        $careerProfile = $user->careerProfile;
        if (!$careerProfile) {
            $careerProfile = new UserCareerProfile(['user_id' => $user->id]);
        }

        $careerProfile->fill($data);
        $careerProfile->save();
    }

    /**
     * Update physical profile
     */
    private function updatePhysicalProfile(User $user, array $data): void
    {
        $physicalProfile = $user->physicalProfile;
        if (!$physicalProfile) {
            $physicalProfile = new UserPhysicalProfile(['user_id' => $user->id]);
        }

        $physicalProfile->fill($data);
        $physicalProfile->save();
    }
}
