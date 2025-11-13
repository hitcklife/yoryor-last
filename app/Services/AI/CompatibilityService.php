<?php

namespace App\Services\AI;

use App\Models\User;
use App\Models\UserCompatibilityScore;
use App\Services\CacheService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompatibilityService
{
    private OpenAIService $openAIService;
    private CacheService $cacheService;

    public function __construct(OpenAIService $openAIService, CacheService $cacheService)
    {
        $this->openAIService = $openAIService;
        $this->cacheService = $cacheService;
    }

    /**
     * Calculate comprehensive compatibility score between two users
     */
    public function calculateCompatibility(User $user1, User $user2): array
    {
        $cacheKey = "compatibility_score:{$user1->id}:{$user2->id}";
        
        return $this->cacheService->remember($cacheKey, 3600, function () use ($user1, $user2) {
            // Calculate different compatibility factors
            $basicCompatibility = $this->calculateBasicCompatibility($user1, $user2);
            $culturalCompatibility = $this->calculateCulturalCompatibility($user1, $user2);
            $lifestyleCompatibility = $this->calculateLifestyleCompatibility($user1, $user2);
            $interestCompatibility = $this->calculateInterestCompatibility($user1, $user2);
            
            // Get AI-enhanced compatibility if available
            $aiCompatibility = $this->openAIService->analyzeCompatibility($user1, $user2);
            
            // Weighted scoring
            $weights = [
                'basic' => 0.2,
                'cultural' => 0.25,
                'lifestyle' => 0.2,
                'interests' => 0.15,
                'ai' => 0.2,
            ];
            
            $totalScore = 
                ($basicCompatibility * $weights['basic']) +
                ($culturalCompatibility * $weights['cultural']) +
                ($lifestyleCompatibility * $weights['lifestyle']) +
                ($interestCompatibility * $weights['interests']) +
                (($aiCompatibility['success'] ? $aiCompatibility['compatibility_score'] : 50) * $weights['ai']);
            
            // Store in database for analytics
            $this->storeCompatibilityScore($user1, $user2, $totalScore, [
                'basic' => $basicCompatibility,
                'cultural' => $culturalCompatibility,
                'lifestyle' => $lifestyleCompatibility,
                'interests' => $interestCompatibility,
                'ai' => $aiCompatibility['success'] ? $aiCompatibility['compatibility_score'] : null,
            ]);
            
            return [
                'overall_score' => round($totalScore, 1),
                'breakdown' => [
                    'basic_compatibility' => round($basicCompatibility, 1),
                    'cultural_compatibility' => round($culturalCompatibility, 1),
                    'lifestyle_compatibility' => round($lifestyleCompatibility, 1),
                    'interest_compatibility' => round($interestCompatibility, 1),
                    'ai_compatibility' => $aiCompatibility['success'] ? round($aiCompatibility['compatibility_score'], 1) : null,
                ],
                'insights' => $this->generateInsights($user1, $user2, $aiCompatibility),
                'ai_analysis' => $aiCompatibility['success'] ? [
                    'strengths' => $aiCompatibility['strengths'] ?? [],
                    'challenges' => $aiCompatibility['challenges'] ?? [],
                    'conversation_topics' => $aiCompatibility['conversation_topics'] ?? [],
                ] : null,
            ];
        });
    }

    /**
     * Calculate basic compatibility (age, location, preferences)
     */
    private function calculateBasicCompatibility(User $user1, User $user2): float
    {
        $score = 100.0;
        
        // Age compatibility
        $age1 = $user1->profile->age ?? 25;
        $age2 = $user2->profile->age ?? 25;
        $ageDiff = abs($age1 - $age2);
        
        if ($ageDiff <= 2) {
            // Perfect age match
        } elseif ($ageDiff <= 5) {
            $score -= 10;
        } elseif ($ageDiff <= 10) {
            $score -= 20;
        } else {
            $score -= 30;
        }
        
        // Location compatibility
        if ($user1->profile->country_id === $user2->profile->country_id) {
            // Same country
            if ($user1->profile->city === $user2->profile->city) {
                // Same city - perfect
            } elseif ($user1->profile->state === $user2->profile->state) {
                $score -= 10; // Same state/region
            } else {
                $score -= 20; // Different regions
            }
        } else {
            $score -= 30; // Different countries
        }
        
        // Check if users meet each other's preferences
        if (!$this->meetsPreferences($user1, $user2)) {
            $score -= 20;
        }
        if (!$this->meetsPreferences($user2, $user1)) {
            $score -= 20;
        }
        
        return max(0, $score);
    }

    /**
     * Calculate cultural compatibility
     */
    private function calculateCulturalCompatibility(User $user1, User $user2): float
    {
        $cultural1 = $user1->culturalProfile;
        $cultural2 = $user2->culturalProfile;
        
        if (!$cultural1 || !$cultural2) {
            return 50.0; // Default if no cultural profile
        }
        
        $score = 100.0;
        $factors = 0;
        
        // Religious practice compatibility
        if ($cultural1->religious_practice_level && $cultural2->religious_practice_level) {
            $religionDiff = abs(
                $this->getReligiousLevel($cultural1->religious_practice_level) -
                $this->getReligiousLevel($cultural2->religious_practice_level)
            );
            $score -= $religionDiff * 10;
            $factors++;
        }
        
        // Prayer frequency compatibility
        if ($cultural1->prayer_frequency && $cultural2->prayer_frequency) {
            if ($cultural1->prayer_frequency === $cultural2->prayer_frequency) {
                // Perfect match
            } else {
                $score -= 15;
            }
            $factors++;
        }
        
        // Family values compatibility
        if ($cultural1->family_values === $cultural2->family_values) {
            // Perfect match
        } elseif ($this->areFamilyValuesCompatible($cultural1->family_values, $cultural2->family_values)) {
            $score -= 10;
        } else {
            $score -= 25;
        }
        $factors++;
        
        // Marriage timeline compatibility
        if ($cultural1->marriage_timeline && $cultural2->marriage_timeline) {
            if ($cultural1->marriage_timeline === $cultural2->marriage_timeline) {
                // Perfect match
            } else {
                $timelineDiff = abs(
                    $this->getTimelineMonths($cultural1->marriage_timeline) -
                    $this->getTimelineMonths($cultural2->marriage_timeline)
                );
                $score -= min(30, $timelineDiff);
            }
            $factors++;
        }
        
        // Children preference
        if ($cultural1->children_preference === $cultural2->children_preference) {
            // Perfect match
        } elseif ($this->areChildrenPreferencesCompatible($cultural1->children_preference, $cultural2->children_preference)) {
            $score -= 15;
        } else {
            $score -= 30;
        }
        $factors++;
        
        return max(0, $score);
    }

    /**
     * Calculate lifestyle compatibility
     */
    private function calculateLifestyleCompatibility(User $user1, User $user2): float
    {
        $profile1 = $user1->profile;
        $profile2 = $user2->profile;
        
        $score = 100.0;
        $factors = 0;
        
        // Smoking compatibility
        if ($profile1->smoking && $profile2->smoking) {
            if ($profile1->smoking === $profile2->smoking) {
                // Perfect match
            } elseif ($this->areSmokingHabitsCompatible($profile1->smoking, $profile2->smoking)) {
                $score -= 15;
            } else {
                $score -= 30;
            }
            $factors++;
        }
        
        // Drinking compatibility
        if ($profile1->drinking && $profile2->drinking) {
            if ($profile1->drinking === $profile2->drinking) {
                // Perfect match
            } elseif ($this->areDrinkingHabitsCompatible($profile1->drinking, $profile2->drinking)) {
                $score -= 15;
            } else {
                $score -= 30;
            }
            $factors++;
        }
        
        // Exercise compatibility
        if ($profile1->exercise && $profile2->exercise) {
            if ($profile1->exercise === $profile2->exercise) {
                // Perfect match
            } else {
                $exerciseDiff = abs(
                    $this->getExerciseLevel($profile1->exercise) -
                    $this->getExerciseLevel($profile2->exercise)
                );
                $score -= $exerciseDiff * 10;
            }
            $factors++;
        }
        
        // Diet compatibility
        if ($profile1->diet && $profile2->diet) {
            if ($profile1->diet === $profile2->diet) {
                // Perfect match
            } elseif ($this->areDietsCompatible($profile1->diet, $profile2->diet)) {
                $score -= 10;
            } else {
                $score -= 25;
            }
            $factors++;
        }
        
        // Education level
        if ($profile1->education_level && $profile2->education_level) {
            $eduDiff = abs(
                $this->getEducationLevel($profile1->education_level) -
                $this->getEducationLevel($profile2->education_level)
            );
            $score -= $eduDiff * 5;
            $factors++;
        }
        
        return $factors > 0 ? max(0, $score) : 50.0;
    }

    /**
     * Calculate interest compatibility
     */
    private function calculateInterestCompatibility(User $user1, User $user2): float
    {
        $interests1 = $user1->profile->interests ?? [];
        $interests2 = $user2->profile->interests ?? [];
        
        if (empty($interests1) || empty($interests2)) {
            return 50.0; // Default if no interests
        }
        
        $commonInterests = array_intersect($interests1, $interests2);
        $totalInterests = count(array_unique(array_merge($interests1, $interests2)));
        
        if ($totalInterests === 0) {
            return 50.0;
        }
        
        // Jaccard similarity coefficient
        $similarity = count($commonInterests) / $totalInterests;
        
        // Convert to 0-100 scale with bonus for multiple common interests
        $score = $similarity * 80; // Max 80 from similarity
        $score += min(20, count($commonInterests) * 5); // Bonus up to 20 for common interests
        
        return min(100, $score);
    }

    /**
     * Check if user meets another user's preferences
     */
    private function meetsPreferences(User $user, User $targetUser): bool
    {
        $preferences = $targetUser->preference;
        if (!$preferences) {
            return true; // No preferences set
        }
        
        $profile = $user->profile;
        
        // Check age preference
        if ($preferences->min_age && $profile->age < $preferences->min_age) {
            return false;
        }
        if ($preferences->max_age && $profile->age > $preferences->max_age) {
            return false;
        }
        
        // Check gender preference
        if ($preferences->gender_preference && $preferences->gender_preference !== 'all') {
            if ($profile->gender !== $preferences->gender_preference) {
                return false;
            }
        }
        
        // Check distance preference (if both have location data)
        if ($preferences->max_distance && $profile->latitude && $profile->longitude &&
            $targetUser->profile->latitude && $targetUser->profile->longitude) {
            $distance = $this->calculateDistance(
                $profile->latitude,
                $profile->longitude,
                $targetUser->profile->latitude,
                $targetUser->profile->longitude
            );
            
            if ($distance > $preferences->max_distance) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Generate compatibility insights
     */
    private function generateInsights(User $user1, User $user2, array $aiAnalysis): array
    {
        $insights = [];
        
        // Location insight
        if ($user1->profile->city === $user2->profile->city) {
            $insights[] = "You both live in the same city, making it easy to meet!";
        } elseif ($user1->profile->country_id !== $user2->profile->country_id) {
            $insights[] = "Long-distance relationship potential - you're in different countries.";
        }
        
        // Age insight
        $ageDiff = abs(($user1->profile->age ?? 25) - ($user2->profile->age ?? 25));
        if ($ageDiff <= 2) {
            $insights[] = "You're very close in age, which often helps with shared life experiences.";
        } elseif ($ageDiff >= 10) {
            $insights[] = "There's a significant age difference, which can bring diverse perspectives.";
        }
        
        // Cultural insights
        if ($user1->culturalProfile && $user2->culturalProfile) {
            if ($user1->culturalProfile->religious_practice_level === $user2->culturalProfile->religious_practice_level) {
                $insights[] = "You share similar religious values and practices.";
            }
            
            if ($user1->culturalProfile->family_values === $user2->culturalProfile->family_values) {
                $insights[] = "Your family values align well.";
            }
        }
        
        // Interest insights
        $commonInterests = array_intersect(
            $user1->profile->interests ?? [],
            $user2->profile->interests ?? []
        );
        if (count($commonInterests) >= 3) {
            $insights[] = "You share many common interests: " . implode(', ', array_slice($commonInterests, 0, 3));
        }
        
        // Add AI insights if available
        if ($aiAnalysis['success'] && !empty($aiAnalysis['overall_summary'])) {
            $insights[] = $aiAnalysis['overall_summary'];
        }
        
        return array_slice($insights, 0, 5); // Return top 5 insights
    }

    /**
     * Store compatibility score in database
     */
    private function storeCompatibilityScore(User $user1, User $user2, float $score, array $breakdown): void
    {
        try {
            DB::table('user_compatibility_scores')->updateOrInsert(
                [
                    'user_id_1' => min($user1->id, $user2->id),
                    'user_id_2' => max($user1->id, $user2->id),
                ],
                [
                    'overall_score' => $score,
                    'basic_score' => $breakdown['basic'],
                    'cultural_score' => $breakdown['cultural'],
                    'lifestyle_score' => $breakdown['lifestyle'],
                    'interest_score' => $breakdown['interests'],
                    'ai_score' => $breakdown['ai'],
                    'calculated_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to store compatibility score', [
                'error' => $e->getMessage(),
                'user1' => $user1->id,
                'user2' => $user2->id,
            ]);
        }
    }

    /**
     * Helper methods for compatibility calculations
     */
    private function getReligiousLevel(string $level): int
    {
        return match ($level) {
            'very_religious' => 5,
            'religious' => 4,
            'moderately_religious' => 3,
            'slightly_religious' => 2,
            'not_religious' => 1,
            default => 3,
        };
    }

    private function getTimelineMonths(string $timeline): int
    {
        return match ($timeline) {
            'asap' => 6,
            'within_year' => 12,
            '1-2_years' => 18,
            '2-3_years' => 30,
            '3+_years' => 48,
            'not_sure' => 36,
            default => 24,
        };
    }

    private function getExerciseLevel(string $exercise): int
    {
        return match ($exercise) {
            'daily' => 5,
            'often' => 4,
            'sometimes' => 3,
            'rarely' => 2,
            'never' => 1,
            default => 3,
        };
    }

    private function getEducationLevel(string $education): int
    {
        return match ($education) {
            'high_school' => 1,
            'some_college' => 2,
            'bachelors' => 3,
            'masters' => 4,
            'doctorate' => 5,
            default => 3,
        };
    }

    private function areFamilyValuesCompatible(string $value1, string $value2): bool
    {
        $compatible = [
            'traditional' => ['traditional', 'moderate'],
            'moderate' => ['traditional', 'moderate', 'modern'],
            'modern' => ['moderate', 'modern'],
        ];
        
        return in_array($value2, $compatible[$value1] ?? []);
    }

    private function areChildrenPreferencesCompatible(string $pref1, string $pref2): bool
    {
        $compatible = [
            'want_children' => ['want_children', 'open_to_children'],
            'open_to_children' => ['want_children', 'open_to_children', 'dont_want_children'],
            'dont_want_children' => ['dont_want_children', 'open_to_children'],
            'have_children' => ['have_children', 'want_more', 'open_to_children'],
            'want_more' => ['want_children', 'want_more', 'have_children'],
        ];
        
        return in_array($pref2, $compatible[$pref1] ?? []);
    }

    private function areSmokingHabitsCompatible(string $habit1, string $habit2): bool
    {
        $compatible = [
            'never' => ['never', 'rarely'],
            'rarely' => ['never', 'rarely', 'sometimes'],
            'sometimes' => ['rarely', 'sometimes', 'regularly'],
            'regularly' => ['sometimes', 'regularly'],
        ];
        
        return in_array($habit2, $compatible[$habit1] ?? []);
    }

    private function areDrinkingHabitsCompatible(string $habit1, string $habit2): bool
    {
        return $this->areSmokingHabitsCompatible($habit1, $habit2); // Same logic
    }

    private function areDietsCompatible(string $diet1, string $diet2): bool
    {
        $compatible = [
            'halal' => ['halal'],
            'vegetarian' => ['vegetarian', 'vegan'],
            'vegan' => ['vegan'],
            'kosher' => ['kosher'],
            'no_restrictions' => ['no_restrictions', 'halal', 'vegetarian'],
        ];
        
        return in_array($diet2, $compatible[$diet1] ?? []);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earth_radius = 6371; // km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earth_radius * $c;
    }
}