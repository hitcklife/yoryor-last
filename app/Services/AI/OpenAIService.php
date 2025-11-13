<?php

namespace App\Services\AI;

use OpenAI\Laravel\Facades\OpenAI;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class OpenAIService
{
    private CacheService $cacheService;
    private string $model;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
        $this->model = config('openai.default_model', 'gpt-4o-mini');
    }

    /**
     * Analyze compatibility between two users
     */
    public function analyzeCompatibility(User $user1, User $user2): array
    {
        // Check rate limit
        $rateLimitKey = "openai_compatibility:{$user1->id}";
        if (!$this->checkRateLimit($rateLimitKey, 'compatibility_analysis')) {
            return [
                'success' => false,
                'error' => 'Rate limit exceeded. Please try again later.',
            ];
        }

        // Check cache first
        $cacheKey = "ai_compatibility:{$user1->id}:{$user2->id}";
        $cached = $this->cacheService->remember(
            $cacheKey,
            config('openai.cache_ttl.compatibility_score'),
            function () use ($user1, $user2) {
                return $this->performCompatibilityAnalysis($user1, $user2);
            }
        );

        return $cached;
    }

    /**
     * Perform the actual compatibility analysis
     */
    private function performCompatibilityAnalysis(User $user1, User $user2): array
    {
        try {
            // Prepare user profiles data
            $profile1 = $this->prepareUserProfile($user1);
            $profile2 = $this->prepareUserProfile($user2);

            $prompt = $this->buildCompatibilityPrompt($profile1, $profile2);

            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => config('openai.prompts.compatibility_analyzer')],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => config('openai.max_tokens.compatibility_analysis'),
                'temperature' => config('openai.temperature.compatibility_analysis'),
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = json_decode($response->choices[0]->message->content, true);

            // Log token usage for cost tracking
            $this->logTokenUsage('compatibility_analysis', $response->usage->totalTokens);

            return [
                'success' => true,
                'compatibility_score' => $content['compatibility_score'] ?? 0,
                'strengths' => $content['strengths'] ?? [],
                'challenges' => $content['challenges'] ?? [],
                'shared_interests' => $content['shared_interests'] ?? [],
                'cultural_alignment' => $content['cultural_alignment'] ?? [],
                'conversation_topics' => $content['conversation_topics'] ?? [],
                'overall_summary' => $content['overall_summary'] ?? '',
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI compatibility analysis failed', [
                'error' => $e->getMessage(),
                'user1_id' => $user1->id,
                'user2_id' => $user2->id,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to analyze compatibility',
            ];
        }
    }

    /**
     * Generate conversation starters
     */
    public function generateConversationStarters(User $user, User $match): array
    {
        // Check rate limit
        $rateLimitKey = "openai_conversation:{$user->id}";
        if (!$this->checkRateLimit($rateLimitKey, 'conversation_starter')) {
            return [
                'success' => false,
                'error' => 'Rate limit exceeded. Please try again later.',
            ];
        }

        // Cache key includes both users
        $cacheKey = "ai_conversation_starters:{$user->id}:{$match->id}";
        
        return $this->cacheService->remember(
            $cacheKey,
            config('openai.cache_ttl.conversation_starter'),
            function () use ($user, $match) {
                return $this->performConversationGeneration($user, $match);
            }
        );
    }

    /**
     * Perform conversation starter generation
     */
    private function performConversationGeneration(User $user, User $match): array
    {
        try {
            $userProfile = $this->prepareUserProfile($user);
            $matchProfile = $this->prepareUserProfile($match);

            $prompt = "Generate 5 conversation starters for {$userProfile['name']} to send to {$matchProfile['name']}. 
                      Consider their shared interests: " . implode(', ', array_intersect($userProfile['interests'] ?? [], $matchProfile['interests'] ?? [])) . "
                      Match's bio: {$matchProfile['bio']}
                      Make them engaging, culturally appropriate, and personalized. Return as JSON with 'starters' array.";

            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => config('openai.prompts.conversation_starter')],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => config('openai.max_tokens.conversation_starter'),
                'temperature' => config('openai.temperature.conversation_starter'),
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = json_decode($response->choices[0]->message->content, true);

            $this->logTokenUsage('conversation_starter', $response->usage->totalTokens);

            return [
                'success' => true,
                'starters' => $content['starters'] ?? [],
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI conversation generation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'match_id' => $match->id,
            ]);

            // Return fallback starters
            return [
                'success' => true,
                'starters' => $this->getFallbackStarters($user, $match),
            ];
        }
    }

    /**
     * Analyze user bio for personality insights
     */
    public function analyzeBio(User $user): array
    {
        if (!$user->profile || !$user->profile->bio) {
            return [
                'success' => false,
                'error' => 'No bio available to analyze',
            ];
        }

        $cacheKey = "ai_bio_analysis:{$user->id}";
        
        return $this->cacheService->remember(
            $cacheKey,
            config('openai.cache_ttl.bio_analysis'),
            function () use ($user) {
                return $this->performBioAnalysis($user);
            }
        );
    }

    /**
     * Perform bio analysis
     */
    private function performBioAnalysis(User $user): array
    {
        try {
            $prompt = "Analyze this dating profile bio and extract personality traits, interests, values, and relationship goals. 
                      Bio: {$user->profile->bio}
                      Return as JSON with: personality_traits (array), interests (array), values (array), relationship_goals (array), summary (string).";

            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => config('openai.prompts.bio_analyzer')],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => config('openai.max_tokens.bio_analysis'),
                'temperature' => config('openai.temperature.bio_analysis'),
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = json_decode($response->choices[0]->message->content, true);

            $this->logTokenUsage('bio_analysis', $response->usage->totalTokens);

            // Update user's profile with AI insights
            $user->profile->update([
                'ai_personality_traits' => $content['personality_traits'] ?? [],
                'ai_extracted_interests' => $content['interests'] ?? [],
                'ai_values' => $content['values'] ?? [],
                'ai_relationship_goals' => $content['relationship_goals'] ?? [],
            ]);

            return [
                'success' => true,
                'analysis' => $content,
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI bio analysis failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to analyze bio',
            ];
        }
    }

    /**
     * Analyze cultural compatibility
     */
    public function analyzeCulturalCompatibility(User $user1, User $user2): array
    {
        try {
            $cultural1 = $user1->culturalProfile;
            $cultural2 = $user2->culturalProfile;

            if (!$cultural1 || !$cultural2) {
                return [
                    'success' => false,
                    'error' => 'Cultural profiles not available',
                ];
            }

            $prompt = $this->buildCulturalCompatibilityPrompt($user1, $user2, $cultural1, $cultural2);

            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => config('openai.prompts.cultural_matcher')],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => config('openai.max_tokens.cultural_matching'),
                'temperature' => config('openai.temperature.cultural_matching'),
                'response_format' => ['type' => 'json_object'],
            ]);

            $content = json_decode($response->choices[0]->message->content, true);

            $this->logTokenUsage('cultural_matching', $response->usage->totalTokens);

            return [
                'success' => true,
                'cultural_score' => $content['cultural_score'] ?? 0,
                'religious_compatibility' => $content['religious_compatibility'] ?? '',
                'family_values_alignment' => $content['family_values_alignment'] ?? '',
                'lifestyle_compatibility' => $content['lifestyle_compatibility'] ?? '',
                'recommendations' => $content['recommendations'] ?? [],
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI cultural analysis failed', [
                'error' => $e->getMessage(),
                'user1_id' => $user1->id,
                'user2_id' => $user2->id,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to analyze cultural compatibility',
            ];
        }
    }

    /**
     * Calculate AI-enhanced match score
     */
    public function calculateEnhancedMatchScore(User $user1, User $user2): float
    {
        $baseScore = 50.0; // Start with base score

        // Get compatibility analysis
        $compatibility = $this->analyzeCompatibility($user1, $user2);
        if ($compatibility['success']) {
            $baseScore = $compatibility['compatibility_score'];
        }

        // Add cultural compatibility if available
        $culturalAnalysis = $this->analyzeCulturalCompatibility($user1, $user2);
        if ($culturalAnalysis['success']) {
            $baseScore = ($baseScore * 0.7) + ($culturalAnalysis['cultural_score'] * 0.3);
        }

        // Factor in activity and engagement
        $activityBonus = $this->calculateActivityBonus($user1, $user2);
        $finalScore = min(100, $baseScore + $activityBonus);

        return round($finalScore, 1);
    }

    /**
     * Prepare user profile data for AI analysis
     */
    private function prepareUserProfile(User $user): array
    {
        $profile = $user->profile;
        $preferences = $user->preference;
        $cultural = $user->culturalProfile;

        return [
            'name' => $profile->first_name ?? 'User',
            'age' => $profile->age ?? null,
            'gender' => $profile->gender ?? null,
            'location' => $profile->city ?? null,
            'bio' => $profile->bio ?? '',
            'interests' => array_merge(
                $profile->interests ?? [],
                $profile->ai_extracted_interests ?? []
            ),
            'profession' => $profile->profession ?? null,
            'education_level' => $profile->education_level ?? null,
            'relationship_goal' => $preferences->looking_for ?? null,
            'religious_practice' => $cultural->religious_practice_level ?? null,
            'family_values' => $cultural->family_values ?? null,
            'lifestyle' => [
                'smoking' => $profile->smoking ?? null,
                'drinking' => $profile->drinking ?? null,
                'exercise' => $profile->exercise ?? null,
            ],
            'languages' => $profile->languages ?? [],
        ];
    }

    /**
     * Build compatibility analysis prompt
     */
    private function buildCompatibilityPrompt(array $profile1, array $profile2): string
    {
        return "Analyze compatibility between these two users and provide a detailed assessment.

User 1: " . json_encode($profile1) . "

User 2: " . json_encode($profile2) . "

Return a JSON object with:
- compatibility_score (0-100)
- strengths (array of compatibility strengths)
- challenges (array of potential challenges)
- shared_interests (array)
- cultural_alignment (array of cultural compatibility factors)
- conversation_topics (array of 3 suggested topics)
- overall_summary (brief summary of compatibility)

Focus on meaningful compatibility factors and be culturally sensitive.";
    }

    /**
     * Build cultural compatibility prompt
     */
    private function buildCulturalCompatibilityPrompt(User $user1, User $user2, $cultural1, $cultural2): string
    {
        return "Analyze cultural compatibility between two users for a dating match.

User 1 Cultural Profile:
- Religious Practice: {$cultural1->religious_practice_level}
- Prayer Frequency: {$cultural1->prayer_frequency}
- Family Values: {$cultural1->family_values}
- Marriage Timeline: {$cultural1->marriage_timeline}
- Children Preference: {$cultural1->children_preference}
- Living Arrangement: {$cultural1->living_arrangement_preference}

User 2 Cultural Profile:
- Religious Practice: {$cultural2->religious_practice_level}
- Prayer Frequency: {$cultural2->prayer_frequency}
- Family Values: {$cultural2->family_values}
- Marriage Timeline: {$cultural2->marriage_timeline}
- Children Preference: {$cultural2->children_preference}
- Living Arrangement: {$cultural2->living_arrangement_preference}

Return JSON with:
- cultural_score (0-100)
- religious_compatibility (description)
- family_values_alignment (description)
- lifestyle_compatibility (description)
- recommendations (array of suggestions)";
    }

    /**
     * Calculate activity bonus for match scoring
     */
    private function calculateActivityBonus(User $user1, User $user2): float
    {
        $bonus = 0;

        // Recent activity bonus
        if ($user2->last_active_at && $user2->last_active_at->isAfter(now()->subDays(7))) {
            $bonus += 5;
        }

        // Profile completion bonus
        $profileCompletion = $this->calculateProfileCompletion($user2);
        $bonus += $profileCompletion * 0.1; // Up to 10 points

        // Photo bonus
        if ($user2->photos()->count() >= 3) {
            $bonus += 3;
        }

        // Verified profile bonus
        if ($user2->is_verified ?? false) {
            $bonus += 5;
        }

        return min(20, $bonus); // Cap at 20 points
    }

    /**
     * Calculate profile completion percentage
     */
    private function calculateProfileCompletion(User $user): float
    {
        $fields = [
            'profile.bio',
            'profile.profession',
            'profile.education_level',
            'profile.interests',
            'culturalProfile.religious_practice_level',
            'culturalProfile.family_values',
            'preference.looking_for',
            'photos' => fn() => $user->photos()->count() > 0,
        ];

        $completed = 0;
        $total = count($fields);

        foreach ($fields as $key => $field) {
            if (is_callable($field)) {
                if ($field()) $completed++;
            } else {
                $value = data_get($user, $field);
                if (!empty($value)) $completed++;
            }
        }

        return ($completed / $total) * 100;
    }

    /**
     * Check rate limits
     */
    private function checkRateLimit(string $key, string $type): bool
    {
        $limits = config("openai.rate_limits.{$type}");
        
        // Check hourly limit
        $hourlyKey = $key . ':hourly';
        $hourlyAttempts = RateLimiter::attempts($hourlyKey);
        
        if ($hourlyAttempts >= $limits['requests_per_hour']) {
            return false;
        }

        // Check daily limit
        $dailyKey = $key . ':daily';
        $dailyAttempts = RateLimiter::attempts($dailyKey);
        
        if ($dailyAttempts >= $limits['requests_per_day']) {
            return false;
        }

        // Increment counters
        RateLimiter::hit($hourlyKey, 3600); // 1 hour
        RateLimiter::hit($dailyKey, 86400); // 24 hours

        return true;
    }

    /**
     * Log token usage for cost tracking
     */
    private function logTokenUsage(string $operation, int $tokens): void
    {
        Log::info('OpenAI token usage', [
            'operation' => $operation,
            'tokens' => $tokens,
            'estimated_cost' => $this->estimateCost($tokens),
            'model' => $this->model,
        ]);

        // You could also store this in database for billing/analytics
    }

    /**
     * Estimate cost based on tokens
     */
    private function estimateCost(int $tokens): float
    {
        // GPT-4o-mini pricing (as of 2024)
        $costPer1kTokens = 0.00015; // $0.15 per 1M tokens
        
        return round(($tokens / 1000) * $costPer1kTokens, 4);
    }

    /**
     * Get fallback conversation starters
     */
    private function getFallbackStarters(User $user, User $match): array
    {
        $matchName = $match->profile->first_name ?? 'there';
        
        return [
            "Hi {$matchName}! I noticed we both enjoy " . ($match->profile->interests[0] ?? 'similar things') . ". What got you interested in that?",
            "Hey {$matchName}! Your profile really caught my attention. How's your day going?",
            "Hi {$matchName}! I see you're from " . ($match->profile->city ?? 'a great place') . ". How do you like it there?",
            "Hello {$matchName}! What's been the highlight of your week so far?",
            "Hi {$matchName}! I'd love to know more about you. What do you enjoy doing in your free time?",
        ];
    }
}