<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserUsageLimits;
use App\Models\UserMonthlyUsage;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UsageLimitsService
{
    private CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Check if user can perform an action
     */
    public function canPerformAction(User $user, string $action, int $count = 1): bool
    {
        $limits = $this->getUserLimits($user);
        $usage = $this->getCurrentUsage($user, $action);
        
        $limit = $limits[$action] ?? 0;
        
        // Unlimited (-1) or within limit
        return $limit === -1 || ($usage + $count) <= $limit;
    }

    /**
     * Record action usage
     */
    public function recordUsage(User $user, string $action, int $count = 1, array $metadata = []): bool
    {
        // Check if user can perform action
        if (!$this->canPerformAction($user, $action, $count)) {
            return false;
        }

        DB::transaction(function () use ($user, $action, $count, $metadata) {
            $today = now()->toDateString();
            $currentMonth = now()->format('Y-m');
            
            // Update daily usage
            $dailyUsage = UserUsageLimits::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $today,
                ],
                [
                    'swipes_used' => 0,
                    'likes_used' => 0,
                    'video_calls_used' => 0,
                    'voice_calls_used' => 0,
                    'video_minutes_used' => 0,
                    'voice_minutes_used' => 0,
                ]
            );

            // Update the specific counter
            switch ($action) {
                case 'swipe':
                    $dailyUsage->increment('swipes_used', $count);
                    break;
                case 'like':
                    $dailyUsage->increment('likes_used', $count);
                    break;
                case 'video_call':
                    $dailyUsage->increment('video_calls_used', $count);
                    $this->updateMonthlyCallUsage($user, 'video', $count);
                    break;
                case 'voice_call':
                    $dailyUsage->increment('voice_calls_used', $count);
                    $this->updateMonthlyCallUsage($user, 'voice', $count);
                    break;
                case 'video_minutes':
                    $dailyUsage->increment('video_minutes_used', $count);
                    $this->updateMonthlyMinutesUsage($user, 'video', $count);
                    break;
                case 'voice_minutes':
                    $dailyUsage->increment('voice_minutes_used', $count);
                    $this->updateMonthlyMinutesUsage($user, 'voice', $count);
                    break;
            }

            // Clear cache
            $this->clearUsageCache($user);
        });

        // Log usage for analytics
        Log::info('Usage recorded', [
            'user_id' => $user->id,
            'action' => $action,
            'count' => $count,
            'metadata' => $metadata,
        ]);

        return true;
    }

    /**
     * Get user's current limits based on subscription
     */
    public function getUserLimits(User $user): array
    {
        $cacheKey = "user_limits:{$user->id}";
        
        return $this->cacheService->remember($cacheKey, 3600, function () use ($user) {
            // Get active subscription
            $subscription = $user->subscriptions()
                ->whereIn('status', ['active', 'trialing'])
                ->with('plan')
                ->first();

            if (!$subscription || !$subscription->plan) {
                // Return free tier limits
                return $this->getFreeTierLimits();
            }

            $plan = $subscription->plan;

            return [
                'swipe' => $plan->swipes_per_day,
                'like' => $plan->swipes_per_day, // Same as swipes
                'video_call' => $plan->video_calls_per_month,
                'voice_call' => $plan->voice_calls_per_month,
                'max_call_duration' => $plan->max_call_duration_minutes,
                'features' => $plan->features ?? [],
            ];
        });
    }

    /**
     * Get current usage for an action
     */
    public function getCurrentUsage(User $user, string $action): int
    {
        $cacheKey = "user_usage:{$user->id}:{$action}:" . now()->toDateString();
        
        return $this->cacheService->remember($cacheKey, 300, function () use ($user, $action) {
            $today = now()->toDateString();
            
            // For monthly limits (calls), get monthly usage
            if (in_array($action, ['video_call', 'voice_call'])) {
                return $this->getMonthlyUsage($user, $action);
            }
            
            // For daily limits, get today's usage
            $usage = UserUsageLimits::where('user_id', $user->id)
                ->where('date', $today)
                ->first();

            if (!$usage) {
                return 0;
            }

            return match ($action) {
                'swipe' => $usage->swipes_used,
                'like' => $usage->likes_used,
                'video_minutes' => $usage->video_minutes_used,
                'voice_minutes' => $usage->voice_minutes_used,
                default => 0,
            };
        });
    }

    /**
     * Get usage statistics for user
     */
    public function getUsageStatistics(User $user): array
    {
        $limits = $this->getUserLimits($user);
        $today = now()->toDateString();
        
        // Get daily usage
        $dailyUsage = UserUsageLimits::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // Get monthly usage
        $monthlyUsage = UserMonthlyUsage::where('user_id', $user->id)
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->first();

        return [
            'daily' => [
                'swipes' => [
                    'used' => $dailyUsage->swipes_used ?? 0,
                    'limit' => $limits['swipe'],
                    'remaining' => $this->calculateRemaining($dailyUsage->swipes_used ?? 0, $limits['swipe']),
                ],
                'likes' => [
                    'used' => $dailyUsage->likes_used ?? 0,
                    'limit' => $limits['like'],
                    'remaining' => $this->calculateRemaining($dailyUsage->likes_used ?? 0, $limits['like']),
                ],
            ],
            'monthly' => [
                'video_calls' => [
                    'used' => $monthlyUsage->video_calls_count ?? 0,
                    'limit' => $limits['video_call'],
                    'remaining' => $this->calculateRemaining($monthlyUsage->video_calls_count ?? 0, $limits['video_call']),
                    'minutes_used' => $monthlyUsage->video_minutes_total ?? 0,
                ],
                'voice_calls' => [
                    'used' => $monthlyUsage->voice_calls_count ?? 0,
                    'limit' => $limits['voice_call'],
                    'remaining' => $this->calculateRemaining($monthlyUsage->voice_calls_count ?? 0, $limits['voice_call']),
                    'minutes_used' => $monthlyUsage->voice_minutes_total ?? 0,
                ],
            ],
            'max_call_duration_minutes' => $limits['max_call_duration'] ?? 10,
            'reset_times' => [
                'daily' => now()->endOfDay()->toISOString(),
                'monthly' => now()->endOfMonth()->toISOString(),
            ],
        ];
    }

    /**
     * Reset daily limits (run at midnight)
     */
    public function resetDailyLimits(): void
    {
        // This is handled automatically by date-based queries
        // But we clear the cache
        Cache::flush(); // Or use more targeted cache clearing
        
        Log::info('Daily limits reset completed');
    }

    /**
     * Check and enforce call duration limit
     */
    public function checkCallDuration(User $user, string $callType, int $currentDurationMinutes): bool
    {
        $limits = $this->getUserLimits($user);
        $maxDuration = $limits['max_call_duration'] ?? 10;
        
        return $maxDuration === -1 || $currentDurationMinutes <= $maxDuration;
    }

    /**
     * Get free tier limits
     */
    private function getFreeTierLimits(): array
    {
        // Get free plan or use defaults
        $freePlan = SubscriptionPlan::where('tier', 'free')->first();
        
        if ($freePlan) {
            return [
                'swipe' => $freePlan->swipes_per_day,
                'like' => $freePlan->swipes_per_day,
                'video_call' => $freePlan->video_calls_per_month,
                'voice_call' => $freePlan->voice_calls_per_month,
                'max_call_duration' => $freePlan->max_call_duration_minutes,
                'features' => $freePlan->features ?? [],
            ];
        }

        // Default free tier limits
        return [
            'swipe' => 100,
            'like' => 100,
            'video_call' => 50,
            'voice_call' => 100,
            'max_call_duration' => 10,
            'features' => [],
        ];
    }

    /**
     * Get monthly usage for calls
     */
    private function getMonthlyUsage(User $user, string $action): int
    {
        $usage = UserMonthlyUsage::where('user_id', $user->id)
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->first();

        if (!$usage) {
            return 0;
        }

        return match ($action) {
            'video_call' => $usage->video_calls_count,
            'voice_call' => $usage->voice_calls_count,
            default => 0,
        };
    }

    /**
     * Update monthly call usage
     */
    private function updateMonthlyCallUsage(User $user, string $type, int $count): void
    {
        $usage = UserMonthlyUsage::firstOrCreate(
            [
                'user_id' => $user->id,
                'year' => now()->year,
                'month' => now()->month,
            ],
            [
                'video_calls_count' => 0,
                'voice_calls_count' => 0,
                'video_minutes_total' => 0,
                'voice_minutes_total' => 0,
            ]
        );

        if ($type === 'video') {
            $usage->increment('video_calls_count', $count);
        } else {
            $usage->increment('voice_calls_count', $count);
        }
    }

    /**
     * Update monthly minutes usage
     */
    private function updateMonthlyMinutesUsage(User $user, string $type, int $minutes): void
    {
        $usage = UserMonthlyUsage::firstOrCreate(
            [
                'user_id' => $user->id,
                'year' => now()->year,
                'month' => now()->month,
            ],
            [
                'video_calls_count' => 0,
                'voice_calls_count' => 0,
                'video_minutes_total' => 0,
                'voice_minutes_total' => 0,
            ]
        );

        if ($type === 'video') {
            $usage->increment('video_minutes_total', $minutes);
        } else {
            $usage->increment('voice_minutes_total', $minutes);
        }
    }

    /**
     * Calculate remaining usage
     */
    private function calculateRemaining(int $used, int $limit): int
    {
        if ($limit === -1) {
            return -1; // Unlimited
        }
        
        return max(0, $limit - $used);
    }

    /**
     * Clear usage cache for user
     */
    private function clearUsageCache(User $user): void
    {
        $patterns = [
            "user_usage:{$user->id}:*",
            "user_limits:{$user->id}",
        ];

        foreach ($patterns as $pattern) {
            $this->cacheService->forgetByPattern($pattern);
        }
    }

    /**
     * Check if feature is available for user
     */
    public function hasFeature(User $user, string $feature): bool
    {
        $limits = $this->getUserLimits($user);
        $features = $limits['features'] ?? [];
        
        return in_array($feature, $features) || isset($features[$feature]);
    }

    /**
     * Get upgrade benefits for user
     */
    public function getUpgradeBenefits(User $user): array
    {
        $currentLimits = $this->getUserLimits($user);
        $plans = SubscriptionPlan::where('tier', '!=', 'free')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $benefits = [];

        foreach ($plans as $plan) {
            $planBenefits = [];

            // Compare swipes
            if ($plan->swipes_per_day > $currentLimits['swipe']) {
                $planBenefits[] = [
                    'type' => 'swipes',
                    'current' => $currentLimits['swipe'],
                    'upgraded' => $plan->swipes_per_day,
                    'improvement' => $plan->swipes_per_day === -1 ? 'Unlimited' : 
                                   '+' . ($plan->swipes_per_day - $currentLimits['swipe']) . ' per day',
                ];
            }

            // Compare video calls
            if ($plan->video_calls_per_month > $currentLimits['video_call']) {
                $planBenefits[] = [
                    'type' => 'video_calls',
                    'current' => $currentLimits['video_call'],
                    'upgraded' => $plan->video_calls_per_month,
                    'improvement' => $plan->video_calls_per_month === -1 ? 'Unlimited' : 
                                   '+' . ($plan->video_calls_per_month - $currentLimits['video_call']) . ' per month',
                ];
            }

            // Compare call duration
            if ($plan->max_call_duration_minutes > $currentLimits['max_call_duration']) {
                $planBenefits[] = [
                    'type' => 'call_duration',
                    'current' => $currentLimits['max_call_duration'],
                    'upgraded' => $plan->max_call_duration_minutes,
                    'improvement' => $plan->max_call_duration_minutes === -1 ? 'Unlimited' : 
                                   '+' . ($plan->max_call_duration_minutes - $currentLimits['max_call_duration']) . ' minutes',
                ];
            }

            // Add features
            $newFeatures = array_diff($plan->features ?? [], $currentLimits['features'] ?? []);
            foreach ($newFeatures as $feature) {
                $planBenefits[] = [
                    'type' => 'feature',
                    'name' => $feature,
                    'description' => $this->getFeatureDescription($feature),
                ];
            }

            if (!empty($planBenefits)) {
                $benefits[$plan->tier] = [
                    'plan' => $plan->only(['id', 'name', 'tier']),
                    'benefits' => $planBenefits,
                ];
            }
        }

        return $benefits;
    }

    /**
     * Get feature description
     */
    private function getFeatureDescription(string $feature): string
    {
        $descriptions = [
            'see_who_liked' => 'See who liked you',
            'priority_support' => 'Priority customer support',
            'advanced_filters' => 'Advanced search filters',
            'unlimited_rewinds' => 'Unlimited profile rewinds',
            'boost_profile' => 'Boost your profile visibility',
            'incognito_mode' => 'Browse profiles anonymously',
            'read_receipts' => 'See when messages are read',
            'family_approval' => 'Family member approval access',
            'matchmaker_mode' => 'Professional matchmaker features',
            'verified_badge' => 'Verified profile badge',
        ];

        return $descriptions[$feature] ?? $feature;
    }
}