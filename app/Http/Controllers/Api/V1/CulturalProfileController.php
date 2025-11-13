<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserCulturalProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CulturalProfileController extends Controller
{
    /**
     * Get the authenticated user's cultural profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getCulturalProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $culturalProfile = $user->culturalProfile;

        if (!$culturalProfile) {
            // Create default cultural profile if none exist
            $culturalProfile = UserCulturalProfile::create([
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $culturalProfile
        ]);
    }

    /**
     * Update the authenticated user's cultural profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCulturalProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        // Prepare input and normalize fields before validation
        $input = $request->all();
        if (array_key_exists('religion', $input) && $input['religion'] !== null) {
            $input['religion'] = $this->normalizeReligion($input['religion']);
        }
        if (array_key_exists('religiousness_level', $input) && $input['religiousness_level'] !== null) {
            $input['religiousness_level'] = $this->normalizeReligiousnessLevel($input['religiousness_level']);
        }
        if (array_key_exists('gender_role_views', $input) && $input['gender_role_views'] !== null) {
            $input['gender_role_views'] = $this->normalizeGenderRoleViews($input['gender_role_views']);
        }
        if (array_key_exists('cultural_events_participation', $input) && $input['cultural_events_participation'] !== null) {
            $input['cultural_events_participation'] = $this->normalizeCulturalEventsParticipation($input['cultural_events_participation']);
        }
        if (array_key_exists('halal_lifestyle', $input)) {
            $input['halal_lifestyle'] = $this->normalizeBooleanish($input['halal_lifestyle']);
        }
        if (array_key_exists('observes_ramadan', $input)) {
            $input['observes_ramadan'] = $this->normalizeBooleanish($input['observes_ramadan']);
        }
        if (array_key_exists('prefers_halal_dates', $input)) {
            $input['prefers_halal_dates'] = $this->normalizeBooleanish($input['prefers_halal_dates']);
        }
        if (array_key_exists('mosque_attendance', $input) && $input['mosque_attendance'] !== null) {
            $input['mosque_attendance'] = $this->normalizeMosqueAttendance($input['mosque_attendance']);
        }

        // Validate the request data
        $validator = Validator::make($input, [
            'native_languages' => 'sometimes|array|nullable',
            'spoken_languages' => 'sometimes|array|nullable',
            'languages' => 'sometimes|array|nullable', // Support alternative field name
            'preferred_communication_language' => 'sometimes|string|max:50|nullable',
            'religion' => 'sometimes|in:islam,christianity,judaism,buddhism,agnostic,atheist,spiritual,other,prefer_not_to_say|nullable',
            'religiousness_level' => 'sometimes|in:very_religious,religious,somewhat_religious,not_religious,cultural_only|nullable',
            'ethnicity' => 'sometimes|in:uzbek,russian,tajik,kazakh,tatar,kyrgyz,korean,other|nullable',
            'uzbek_region' => 'sometimes|in:tashkent,samarkand,bukhara,andijan,namangan,fergana,khorezm,karakalpakstan,kashkadarya,surkhandarya,navoiy,jizzakh,sirdaryo|nullable',
            'lifestyle_type' => 'sometimes|in:traditional,modern,mix|nullable',
            'gender_role_views' => 'sometimes|in:egalitarian,balanced,traditional|nullable',
            'traditional_clothing_comfort' => 'sometimes|in:very_uncomfortable,uncomfortable,neutral,comfortable,very_comfortable|nullable',
            'uzbek_cuisine_knowledge' => 'sometimes|in:none,basic,good,expert|nullable',
            'cultural_events_participation' => 'sometimes|in:never,occasionally,monthly,weekly,daily|nullable',
            'halal_lifestyle' => 'sometimes|boolean|nullable',
            'culturalValues' => 'sometimes|array|nullable', // Support additional fields
            'traditions' => 'sometimes|array|nullable',
            'dietaryPreferences' => 'sometimes|string|max:255|nullable',
            // Prayer/religious practice related (added via migration)
            'observes_ramadan' => 'sometimes|boolean|nullable',
            'prefers_halal_dates' => 'sometimes|boolean|nullable',
            'mosque_attendance' => 'sometimes|in:never,occasionally,monthly,weekly,daily|nullable',
            'quran_reading' => 'sometimes|in:never,occasionally,monthly,weekly,daily|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create cultural profile
        $culturalProfile = $user->culturalProfile;
        if (!$culturalProfile) {
            $culturalProfile = new UserCulturalProfile(['user_id' => $user->id]);
        }

        // Get validated data and handle field mappings
        $validatedData = $validator->validated();
        
        // Map alternative field names to model fields
        if (isset($validatedData['languages'])) {
            $validatedData['spoken_languages'] = $validatedData['languages'];
            unset($validatedData['languages']);
        }
        
        // Normalize religion value (ensure final canonical value)
        if (isset($validatedData['religion'])) {
            $validatedData['religion'] = $this->normalizeReligion($validatedData['religion']);
        }
        if (isset($validatedData['religiousness_level'])) {
            $validatedData['religiousness_level'] = $this->normalizeReligiousnessLevel($validatedData['religiousness_level']);
        }
        if (isset($validatedData['gender_role_views'])) {
            $validatedData['gender_role_views'] = $this->normalizeGenderRoleViews($validatedData['gender_role_views']);
        }
        if (isset($validatedData['cultural_events_participation'])) {
            $validatedData['cultural_events_participation'] = $this->normalizeCulturalEventsParticipation($validatedData['cultural_events_participation']);
        }
        if (array_key_exists('halal_lifestyle', $validatedData)) {
            $validatedData['halal_lifestyle'] = $this->normalizeBooleanish($validatedData['halal_lifestyle']);
        }
        if (array_key_exists('observes_ramadan', $validatedData)) {
            $validatedData['observes_ramadan'] = $this->normalizeBooleanish($validatedData['observes_ramadan']);
        }
        if (array_key_exists('prefers_halal_dates', $validatedData)) {
            $validatedData['prefers_halal_dates'] = $this->normalizeBooleanish($validatedData['prefers_halal_dates']);
        }
        if (isset($validatedData['mosque_attendance'])) {
            $validatedData['mosque_attendance'] = $this->normalizeMosqueAttendance($validatedData['mosque_attendance']);
        }
        
        // Handle additional fields that may need to be stored as JSON or in related tables
        // For now, we'll store only the fields that exist in the model
        $modelFields = $culturalProfile->getFillable();
        $dataToSave = array_intersect_key($validatedData, array_flip($modelFields));
        
        // Update cultural profile with validated data
        $culturalProfile->fill($dataToSave);
        $culturalProfile->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cultural profile updated successfully',
            'data' => $culturalProfile
        ]);
    }

    /**
     * Normalize religion input to allowed canonical values used by validation/storage.
     */
    private function normalizeReligion(?string $religion): ?string
    {
        if ($religion === null) {
            return null;
        }

        $value = strtolower(trim($religion));

        // Normalize spacing and separators
        $value = str_replace(['-', ' '], '_', $value);

        $map = [
            // Islam
            'islam' => 'islam',
            'muslim' => 'islam',

            // Christianity  
            'christian' => 'christianity',
            'christianity' => 'christianity',
            'catholic' => 'christianity',
            'protestant' => 'christianity',
            'orthodox' => 'christianity',

            // Judaism
            'jewish' => 'judaism',
            'judaism' => 'judaism',

            // Buddhism
            'buddhist' => 'buddhism',
            'buddhism' => 'buddhism',

            // Non-religious
            'atheist' => 'atheist',
            'agnostic' => 'agnostic',
            'secular' => 'atheist',
            'none' => 'atheist',
            'no_religion' => 'atheist',

            // Spiritual
            'spiritual' => 'spiritual',

            // Other
            'other' => 'other',
            'hindu' => 'other',
            'hinduism' => 'other',
            'sikh' => 'other',
            'sikhism' => 'other',
            'bahai' => 'other',
            'baháʼí' => 'other',
            'bahai_faith' => 'other',
            'zoroastrian' => 'other',
            'zoroastrianism' => 'other',

            // Prefer not to say
            'prefer_not_to_say' => 'prefer_not_to_say',
            'prefer_not_to_answer' => 'prefer_not_to_say',
            'prefer_to_skip' => 'prefer_not_to_say',
        ];

        return $map[$value] ?? $value;
    }

    /**
     * Normalize religiousness level to allowed values.
     */
    private function normalizeReligiousnessLevel(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $v = strtolower(trim($value));
        $v = str_replace(['-', ' '], '_', $v);
        $map = [
            'very_religious' => 'very_religious',
            'religious' => 'religious',
            'somewhat_religious' => 'somewhat_religious',
            'moderately_religious' => 'somewhat_religious',
            'not_religious' => 'not_religious',
            'cultural_only' => 'cultural_only',
            'secular' => 'not_religious',
            'prefer_not_to_say' => 'not_religious',
        ];
        return $map[$v] ?? $v;
    }

    /**
     * Normalize gender role views to allowed values.
     */
    private function normalizeGenderRoleViews(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $v = strtolower(trim($value));
        $v = str_replace(['-', ' '], '_', $v);
        $map = [
            'egalitarian' => 'egalitarian',
            'balanced' => 'balanced', 
            'traditional' => 'traditional',
            'equal' => 'egalitarian',
            'shared' => 'balanced',
            'flexible' => 'balanced',
            'modern' => 'egalitarian',
        ];
        return $map[$v] ?? $v;
    }

    /**
     * Normalize cultural events participation to allowed values.
     */
    private function normalizeCulturalEventsParticipation(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $v = strtolower(trim($value));
        $v = str_replace(['-', ' '], '_', $v);
        $map = [
            'never' => 'never',
            'occasionally' => 'occasionally',
            'monthly' => 'monthly',
            'weekly' => 'weekly',
            'daily' => 'daily',
            'sometimes' => 'occasionally',
            'often' => 'weekly',
            'very_often' => 'daily',
            'rarely' => 'occasionally',
            'active' => 'weekly',
            'very_active' => 'daily',
        ];
        return $map[$v] ?? $v;
    }

    /**
     * Normalize boolean-ish inputs like yes/no/true/false/1/0 strings.
     */
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
        $truthy = ['true', '1', 'yes', 'y', 'on', 'agree', 'comfortable'];
        $falsy = ['false', '0', 'no', 'n', 'off', 'disagree', 'uncomfortable'];
        if (in_array($v, $truthy, true)) {
            return true;
        }
        if (in_array($v, $falsy, true)) {
            return false;
        }
        return null;
    }

    /**
     * Map comfort scale strings to boolean, leaving null for neutral/unknown.
     */
    private function normalizeBooleanishComfort($value): ?bool
    {
        if (is_bool($value) || $value === null || $value === '') {
            return is_bool($value) ? $value : null;
        }
        $v = strtolower(trim((string)$value));
        $v = str_replace(['-', ' '], '_', $v);
        $falseMap = ['very_uncomfortable', 'uncomfortable', 'not_comfortable'];
        $trueMap = ['very_comfortable', 'comfortable'];
        if (in_array($v, $falseMap, true)) {
            return false;
        }
        if (in_array($v, $trueMap, true)) {
            return true;
        }
        // neutral/unknown
        if (in_array($v, ['neutral', 'unknown', 'n_a', 'na'], true)) {
            return null;
        }
        // fallback to generic booleanish handler
        return $this->normalizeBooleanish($value);
    }

    /**
     * Normalize mosque attendance to allowed enum values.
     */
    private function normalizeMosqueAttendance(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $v = strtolower(trim($value));
        $v = str_replace(['-', ' '], '_', $v);
        $map = [
            'never' => 'never',
            'occasionally' => 'occasionally', 
            'monthly' => 'monthly',
            'weekly' => 'weekly',
            'daily' => 'daily',
            'sometimes' => 'occasionally',
            'rarely' => 'occasionally',
        ];
        return $map[$v] ?? $v;
    }

    /**
     * Normalize Quran reading to boolean; any frequency except 'never' => true.
     */
    private function normalizeQuranReading($value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_bool($value)) {
            return $value;
        }
        $v = strtolower(trim((string)$value));
        $v = str_replace(['-', ' '], '_', $v);
        if (in_array($v, ['never', 'no', 'false', '0'], true)) {
            return false;
        }
        return true;
    }
}
