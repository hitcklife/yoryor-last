<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ValidationService
{
    /**
     * Common validation rules
     */
    public const RULES = [
        'user_id' => 'required|integer|exists:users,id',
        'email' => 'required|email:rfc,dns|max:255',
        'phone' => 'nullable|string|max:20|regex:/^[\+\-\(\)\s\d]+$/',
        'password' => 'required|string|min:8|max:128|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
        'name' => 'required|string|max:50|regex:/^[a-zA-Z\s\-\'\.]+$/',
        'bio' => 'nullable|string|max:1000',
        'age' => 'required|integer|min:18|max:120',
        'gender' => 'required|in:male,female,non-binary,other',
        'location' => 'nullable|string|max:255',
        'latitude' => 'nullable|numeric|between:-90,90',
        'longitude' => 'nullable|numeric|between:-180,180',
        'date_of_birth' => 'required|date|before:today|after:1900-01-01',
        'interests' => 'nullable|array|max:10',
        'interests.*' => 'string|max:50',
        'photos' => 'nullable|array|max:10',
        'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240', // 10MB
        'message_content' => 'required|string|max:2000',
        'search_radius' => 'nullable|integer|min:1|max:500',
        'per_page' => 'nullable|integer|min:1|max:100',
        'page' => 'nullable|integer|min:1',
    ];

    /**
     * Profile validation rules
     */
    public static function getProfileValidationRules(): array
    {
        return [
            'first_name' => self::RULES['name'],
            'last_name' => self::RULES['name'],
            'email' => self::RULES['email'],
            'phone' => self::RULES['phone'],
            'gender' => self::RULES['gender'],
            'date_of_birth' => self::RULES['date_of_birth'],
            'bio' => self::RULES['bio'],
            'city' => 'nullable|string|max:85',
            'state' => 'nullable|string|max:50',
            'province' => 'nullable|string|max:50',
            'country_id' => 'nullable|integer|exists:countries,id',
            'latitude' => self::RULES['latitude'],
            'longitude' => self::RULES['longitude'],
            'profession' => 'nullable|string|max:100',
            'occupation' => 'nullable|string|max:100',
            'interests' => self::RULES['interests'],
            'interests.*' => self::RULES['interests.*'],
            'looking_for_relationship' => 'nullable|in:casual,serious,friendship,open',
        ];
    }

    /**
     * Message validation rules
     */
    public static function getMessageValidationRules(): array
    {
        return [
            'content' => self::RULES['message_content'],
            'type' => 'nullable|in:text,image,audio,video,file,location,call,system',
            'media_url' => 'nullable|url|max:2048',
            'thumbnail_url' => 'nullable|url|max:2048',
            'metadata' => 'nullable|array',
        ];
    }

    /**
     * User preferences validation rules
     */
    public static function getPreferencesValidationRules(): array
    {
        return [
            'search_radius' => self::RULES['search_radius'],
            'min_age' => 'nullable|integer|min:18|max:120',
            'max_age' => 'nullable|integer|min:18|max:120|gte:min_age',
            'preferred_genders' => 'nullable|array',
            'preferred_genders.*' => 'in:male,female,non-binary,other',
            'country' => 'nullable|string|size:2',
            'show_me_globally' => 'nullable|boolean',
            'distance_unit' => 'nullable|in:km,miles',
            'hobbies_interests' => self::RULES['interests'],
            'hobbies_interests.*' => self::RULES['interests.*'],
            'languages_spoken' => 'nullable|array',
            'languages_spoken.*' => 'string|max:50',
            'deal_breakers' => 'nullable|array',
            'deal_breakers.*' => 'string|max:100',
            'must_haves' => 'nullable|array',
            'must_haves.*' => 'string|max:100',
        ];
    }

    /**
     * Authentication validation rules
     */
    public static function getAuthValidationRules(): array
    {
        return [
            'email' => self::RULES['email'],
            'password' => self::RULES['password'],
            'phone' => self::RULES['phone'],
            'verification_code' => 'required|string|size:6|regex:/^\d{6}$/',
            'device_type' => 'nullable|in:ios,android,web',
            'device_token' => 'nullable|string|max:255',
        ];
    }

    /**
     * Photo upload validation rules
     */
    public static function getPhotoValidationRules(): array
    {
        return [
            'photo' => self::RULES['photos.*'],
            'is_profile_photo' => 'nullable|boolean',
            'is_private' => 'nullable|boolean',
            'caption' => 'nullable|string|max:500',
            'order' => 'nullable|integer|min:1|max:10',
        ];
    }

    /**
     * Validate request with custom rules
     */
    public static function validateRequest(Request $request, array $rules, array $messages = []): array
    {
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Validate profile data
     */
    public static function validateProfile(Request $request): array
    {
        return self::validateRequest($request, self::getProfileValidationRules(), [
            'first_name.regex' => 'First name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'last_name.regex' => 'Last name can only contain letters, spaces, hyphens, apostrophes, and periods.',
            'email.email' => 'Please provide a valid email address.',
            'phone.regex' => 'Please provide a valid phone number.',
            'date_of_birth.before' => 'Date of birth must be in the past.',
            'date_of_birth.after' => 'You must be under 120 years old.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'interests.max' => 'You can select up to 10 interests.',
            'interests.*.max' => 'Each interest must be no more than 50 characters.',
        ]);
    }

    /**
     * Validate message data
     */
    public static function validateMessage(Request $request): array
    {
        return self::validateRequest($request, self::getMessageValidationRules(), [
            'content.required' => 'Message content is required.',
            'content.max' => 'Message content cannot exceed 2000 characters.',
        ]);
    }

    /**
     * Validate preferences data
     */
    public static function validatePreferences(Request $request): array
    {
        return self::validateRequest($request, self::getPreferencesValidationRules(), [
            'max_age.gte' => 'Maximum age must be greater than or equal to minimum age.',
            'search_radius.min' => 'Search radius must be at least 1 km.',
            'search_radius.max' => 'Search radius cannot exceed 500 km.',
        ]);
    }

    /**
     * Validate authentication data
     */
    public static function validateAuth(Request $request, string $type = 'login'): array
    {
        $rules = match($type) {
            'login' => [
                'email' => self::RULES['email'],
                'password' => 'required|string',
                'device_type' => 'nullable|in:ios,android,web',
                'device_token' => 'nullable|string|max:255',
            ],
            'register' => [
                'email' => self::RULES['email'] . '|unique:users,email',
                'password' => self::RULES['password'],
                'phone' => self::RULES['phone'] . '|unique:users,phone',
                'device_type' => 'nullable|in:ios,android,web',
                'device_token' => 'nullable|string|max:255',
            ],
            'verify' => [
                'verification_code' => self::RULES['verification_code'],
            ],
            'reset_password' => [
                'email' => self::RULES['email'],
                'password' => self::RULES['password'],
                'verification_code' => self::RULES['verification_code'],
            ],
            default => []
        };

        return self::validateRequest($request, $rules, [
            'email.unique' => 'This email address is already registered.',
            'phone.unique' => 'This phone number is already registered.',
            'verification_code.regex' => 'Verification code must be exactly 6 digits.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);
    }

    /**
     * Validate photo upload
     */
    public static function validatePhoto(Request $request): array
    {
        return self::validateRequest($request, self::getPhotoValidationRules(), [
            'photo.image' => 'The uploaded file must be an image.',
            'photo.mimes' => 'The image must be in JPEG, PNG, JPG, or WebP format.',
            'photo.max' => 'The image size cannot exceed 10MB.',
        ]);
    }

    /**
     * Sanitize input data
     */
    public static function sanitizeInput(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove potentially dangerous characters but preserve normal punctuation
                $sanitized[$key] = preg_replace('/[<>{}\\\\]/', '', trim($value));
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Validate pagination parameters
     */
    public static function validatePagination(Request $request): array
    {
        return self::validateRequest($request, [
            'per_page' => self::RULES['per_page'],
            'page' => self::RULES['page'],
        ], [
            'per_page.min' => 'Items per page must be at least 1.',
            'per_page.max' => 'Items per page cannot exceed 100.',
            'page.min' => 'Page number must be at least 1.',
        ]);
    }

    /**
     * Check if content contains inappropriate words
     */
    public static function containsProfanity(string $content): bool
    {
        // Basic profanity filter - in production, use a more comprehensive service
        $profanityWords = [
            'fuck', 'shit', 'bitch', 'asshole', 'damn', 'bastard',
            // Add more words as needed or use a proper profanity filter service
        ];
        
        $lowercaseContent = strtolower($content);
        
        foreach ($profanityWords as $word) {
            if (strpos($lowercaseContent, $word) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Validate and sanitize message content
     */
    public static function validateAndSanitizeMessage(string $content): array
    {
        // Check for profanity
        if (self::containsProfanity($content)) {
            return [
                'valid' => false,
                'message' => 'Message contains inappropriate content.',
                'sanitized' => null
            ];
        }

        // Sanitize content
        $sanitized = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
        $sanitized = trim($sanitized);

        return [
            'valid' => true,
            'message' => 'Content is valid.',
            'sanitized' => $sanitized
        ];
    }
}