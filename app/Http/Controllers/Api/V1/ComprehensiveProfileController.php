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
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ComprehensiveProfileController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    /**
     * Get all profile information for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllProfileData(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            return $this->cacheService->remember(
                "comprehensive_profile:{$user->id}",
                CacheService::TTL_MEDIUM,
                function() use ($user) {
                    // Load all profile relationships including country
                    $user->load([
                        'profile.country',
                        'preference',
                        'culturalProfile',
                        'familyPreference',
                        'locationPreference',
                        'careerProfile',
                        'physicalProfile'
                    ]);

                    // Format basic profile to include country
                    $basicProfile = $user->profile ? $user->profile->toArray() : null;
                    if ($basicProfile && $user->profile) {
                        // Load country directly if not already loaded
                        if (!$user->profile->relationLoaded('country') && $user->profile->country_id) {
                            $user->profile->load('country');
                        }
                        
                        // Add country if it exists
                        if ($user->profile->country) {
                            $basicProfile['country'] = $user->profile->country->toArray();
                        } else if ($user->profile->country_id) {
                            // Fallback: fetch country directly if relationship failed
                            $country = \App\Models\Country::find($user->profile->country_id);
                            if ($country) {
                                $basicProfile['country'] = $country->toArray();
                            }
                        }
                    }

                    return response()->json([
                        'status' => 'success',
                        'data' => [
                            'basic_profile' => $basicProfile,
                            'preferences' => $user->preference,
                            'cultural_profile' => $user->culturalProfile,
                            'family_preferences' => $user->familyPreference,
                            'location_preferences' => $user->locationPreference,
                            'career_profile' => $user->careerProfile,
                            'physical_profile' => $user->physicalProfile,
                        ]
                    ]);
                },
                ["user_{$user->id}_profile", "user_{$user->id}_preferences"]
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get profile data',
                'error' => $e->getMessage()
            ], 500);
        }
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
            'basic_profile.looking_for_relationship' => 'sometimes|in:casual,serious,friendship,open',
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

            // Cultural profile validation - updated with new fields
            'cultural_profile' => 'sometimes|array',
            'cultural_profile.native_languages' => 'sometimes|array',
            'cultural_profile.spoken_languages' => 'sometimes|array',
            'cultural_profile.preferred_communication_language' => 'sometimes|string|max:50',
            'cultural_profile.religion' => 'sometimes|in:islam,christianity,judaism,buddhism,agnostic,atheist,spiritual,other,prefer_not_to_say',
            'cultural_profile.religiousness_level' => 'sometimes|in:very_religious,religious,somewhat_religious,not_religious,cultural_only',
            'cultural_profile.ethnicity' => 'sometimes|in:uzbek,russian,tajik,kazakh,tatar,kyrgyz,korean,other',
            'cultural_profile.uzbek_region' => 'sometimes|in:tashkent,samarkand,bukhara,andijan,namangan,fergana,khorezm,karakalpakstan,kashkadarya,surkhandarya,navoiy,jizzakh,sirdaryo',
            'cultural_profile.lifestyle_type' => 'sometimes|in:traditional,modern,mix',
            'cultural_profile.gender_role_views' => 'sometimes|in:egalitarian,balanced,traditional',
            'cultural_profile.traditional_clothing_comfort' => 'sometimes|in:very_uncomfortable,uncomfortable,neutral,comfortable,very_comfortable',
            'cultural_profile.uzbek_cuisine_knowledge' => 'sometimes|in:none,basic,good,expert',
            'cultural_profile.cultural_events_participation' => 'sometimes|in:never,occasionally,monthly,weekly,daily',
            'cultural_profile.halal_lifestyle' => 'sometimes|boolean',
            'cultural_profile.observes_ramadan' => 'sometimes|boolean',
            'cultural_profile.mosque_attendance' => 'sometimes|in:never,occasionally,monthly,weekly,daily',
            'cultural_profile.quran_reading' => 'sometimes|in:never,occasionally,monthly,weekly,daily',

            // Family preferences validation - updated with new fields
            'family_preferences' => 'sometimes|array',
            'family_preferences.marriage_intention' => 'sometimes|in:seeking_marriage,open_to_marriage,not_ready_yet,undecided',
            'family_preferences.children_preference' => 'sometimes|in:want_children,have_and_want_more,have_and_dont_want_more,dont_want_children,undecided',
            'family_preferences.current_children' => 'sometimes|integer|min:0|max:20',
            'family_preferences.family_values' => 'sometimes|array',
            'family_preferences.family_values.*' => 'sometimes|in:close_knit,traditional,family_first,independent,supportive,respect_elders',
            'family_preferences.living_situation' => 'sometimes|in:alone,with_family,with_roommates,with_partner,other',
            'family_preferences.family_involvement' => 'sometimes|string|max:1000',
            'family_preferences.marriage_timeline' => 'sometimes|in:within_6_months,within_1_year,within_2_years,within_5_years,no_timeline',
            'family_preferences.family_importance' => 'sometimes|in:extremely_important,very_important,moderately_important,somewhat_important,not_important',
            'family_preferences.family_approval_important' => 'sometimes|boolean',
            'family_preferences.previous_marriages' => 'sometimes|integer|min:0|max:10',
            'family_preferences.homemaker_preference' => 'sometimes|in:prefer_traditional_roles,both_work_equally,flexible_arrangement,career_focused,no_preference',
            // Legacy fields
            'family_preferences.number_of_children_wanted' => 'sometimes|integer|min:0|max:20',
            'family_preferences.living_with_family' => 'sometimes|boolean',

            // Location preferences validation - updated with new fields
            'location_preferences' => 'sometimes|array',
            'location_preferences.immigration_status' => 'sometimes|in:citizen,permanent_resident,work_visa,student_visa,tourist_visa,asylum_refugee,other',
            'location_preferences.years_in_current_country' => 'sometimes|integer|min:0|max:100|nullable',
            'location_preferences.plans_to_return_uzbekistan' => 'sometimes|in:definitely_yes,probably_yes,maybe,probably_no,definitely_no,undecided',
            'location_preferences.uzbekistan_visit_frequency' => 'sometimes|in:never,rarely,annually,twice_yearly,quarterly,monthly,frequently',
            'location_preferences.willing_to_relocate' => 'sometimes|in:no,within_city,within_state,within_country,internationally,for_right_person',
            'location_preferences.relocation_countries' => 'sometimes|array',
            'location_preferences.relocation_countries.*' => 'sometimes|in:uzbekistan,united_states,canada,united_kingdom,germany,australia,turkey,russia,kazakhstan,other',
            'location_preferences.preferred_locations' => 'sometimes|array',
            'location_preferences.preferred_locations.*' => 'sometimes|in:city_center,suburbs,countryside,near_family,near_work,quiet_area',
            'location_preferences.live_with_family' => 'sometimes|boolean',
            'location_preferences.future_location_plans' => 'sometimes|string|max:1000',

            // Career profile validation - updated with new fields
            'career_profile' => 'sometimes|array',
            'career_profile.education_level' => 'sometimes|in:high_school,associate,bachelor,master,doctorate,professional,trade_school,other',
            'career_profile.field_of_study' => 'sometimes|string|max:255',
            'career_profile.work_status' => 'sometimes|in:full_time,part_time,self_employed,freelance,student,unemployed,retired',
            'career_profile.occupation' => 'sometimes|string|max:255',
            'career_profile.employer' => 'sometimes|string|max:255',
            'career_profile.career_goals' => 'sometimes|array',
            'career_profile.career_goals.*' => 'sometimes|in:entrepreneurship,leadership,expertise,work_life_balance,financial_success,make_impact',
            'career_profile.income_range' => 'sometimes|in:under_25k,25k_50k,50k_75k,75k_100k,100k_150k,150k_plus,prefer_not_to_say',
            // Legacy fields
            'career_profile.university_name' => 'sometimes|string|max:200',
            'career_profile.owns_property' => 'sometimes|boolean',
            'career_profile.financial_goals' => 'sometimes|string',
            'career_profile.profession' => 'sometimes|string|max:255',
            'career_profile.company' => 'sometimes|string|max:255',
            'career_profile.job_title' => 'sometimes|string|max:255',
            'career_profile.income' => 'sometimes|string|max:100',

            // Physical profile validation - updated with new fields
            'physical_profile' => 'sometimes|array',
            'physical_profile.height' => 'sometimes|integer|min:100|max:250',
            'physical_profile.weight' => 'sometimes|numeric|min:30|max:300',
            'physical_profile.smoking_habit' => 'sometimes|in:never,socially,regularly,trying_to_quit',
            'physical_profile.drinking_habit' => 'sometimes|in:never,socially,occasionally,regularly',
            'physical_profile.exercise_frequency' => 'sometimes|in:never,rarely,1_2_week,3_4_week,daily',
            'physical_profile.diet_preference' => 'sometimes|in:everything,vegetarian,vegan,halal,kosher,pescatarian,keto',
            'physical_profile.pet_preference' => 'sometimes|in:love_pets,have_pets,allergic,dont_like,no_preference',
            'physical_profile.hobbies' => 'sometimes|array',
            'physical_profile.hobbies.*' => 'sometimes|in:reading,cooking,travel,sports,music,movies,gaming,art,photography,hiking,dancing,meditation',
            'physical_profile.sleep_schedule' => 'sometimes|string|max:255',
            // Legacy fields
            'physical_profile.fitness_level' => 'sometimes|in:never,rarely,1_2_week,3_4_week,daily',
            'physical_profile.dietary_restrictions' => 'sometimes|array',
            'physical_profile.smoking_status' => 'sometimes|in:never,socially,regularly,trying_to_quit',
            'physical_profile.drinking_status' => 'sometimes|in:never,socially,occasionally,regularly',
            'physical_profile.diet' => 'sometimes|in:everything,vegetarian,vegan,halal,kosher,pescatarian,keto',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
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

            // Clear user profile cache
            $this->cacheService->invalidateUserCaches($user->id);

            // Return updated profile data including country
            $user->load([
                'profile.country',
                'preference',
                'culturalProfile',
                'familyPreference',
                'locationPreference',
                'careerProfile',
                'physicalProfile'
            ]);

            // Format basic profile to include country
            $basicProfile = $user->profile ? $user->profile->toArray() : null;
            if ($basicProfile && $user->profile) {
                // Load country directly if not already loaded
                if (!$user->profile->relationLoaded('country') && $user->profile->country_id) {
                    $user->profile->load('country');
                }
                
                // Add country if it exists
                if ($user->profile->country) {
                    $basicProfile['country'] = $user->profile->country->toArray();
                } else if ($user->profile->country_id) {
                    // Fallback: fetch country directly if relationship failed
                    $country = \App\Models\Country::find($user->profile->country_id);
                    if ($country) {
                        $basicProfile['country'] = $country->toArray();
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => [
                    'basic_profile' => $basicProfile,
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
                'status' => 'error',
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
