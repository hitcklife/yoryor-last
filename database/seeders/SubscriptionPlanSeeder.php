<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;
use App\Models\PlanPricing;
use App\Models\PlanFeature;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create subscription features first
        $features = [
            ['key' => 'see_who_liked', 'name' => 'See Who Liked You', 'icon' => '👁️'],
            ['key' => 'unlimited_rewinds', 'name' => 'Unlimited Rewinds', 'icon' => '↩️'],
            ['key' => 'boost_profile', 'name' => 'Monthly Profile Boosts', 'icon' => '🚀'],
            ['key' => 'advanced_filters', 'name' => 'Advanced Search Filters', 'icon' => '🔍'],
            ['key' => 'priority_support', 'name' => 'Priority Support', 'icon' => '⭐'],
            ['key' => 'read_receipts', 'name' => 'Message Read Receipts', 'icon' => '✓✓'],
            ['key' => 'incognito_mode', 'name' => 'Browse Anonymously', 'icon' => '🥷'],
            ['key' => 'family_approval', 'name' => 'Family Approval Access', 'icon' => '👨‍👩‍👧‍👦'],
            ['key' => 'matchmaker_mode', 'name' => 'Professional Matchmaker', 'icon' => '💑'],
            ['key' => 'verified_badge', 'name' => 'Verified Profile Badge', 'icon' => '✅'],
        ];

        foreach ($features as $index => $feature) {
            PlanFeature::updateOrCreate(
                ['key' => $feature['key']],
                array_merge($feature, ['sort_order' => $index + 1])
            );
        }

        // Create subscription plans
        $plans = [
            [
                'name' => 'Free',
                'tier' => 'free',
                'swipes_per_day' => 100,
                'video_calls_per_month' => 50,
                'voice_calls_per_month' => 100,
                'max_call_duration_minutes' => 10,
                'features' => [],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Basic',
                'tier' => 'basic',
                'swipes_per_day' => 500,
                'video_calls_per_month' => 200,
                'voice_calls_per_month' => 300,
                'max_call_duration_minutes' => 30,
                'features' => ['see_who_liked', 'unlimited_rewinds', 'read_receipts'],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Gold',
                'tier' => 'gold',
                'swipes_per_day' => 1000,
                'video_calls_per_month' => 500,
                'voice_calls_per_month' => -1, // Unlimited
                'max_call_duration_minutes' => 60,
                'features' => [
                    'see_who_liked', 
                    'unlimited_rewinds', 
                    'boost_profile', 
                    'advanced_filters', 
                    'priority_support',
                    'read_receipts',
                    'incognito_mode'
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Platinum',
                'tier' => 'platinum',
                'swipes_per_day' => -1, // Unlimited
                'video_calls_per_month' => -1, // Unlimited
                'voice_calls_per_month' => -1, // Unlimited
                'max_call_duration_minutes' => -1, // Unlimited
                'features' => [
                    'see_who_liked', 
                    'unlimited_rewinds', 
                    'boost_profile', 
                    'advanced_filters', 
                    'priority_support',
                    'read_receipts',
                    'incognito_mode',
                    'family_approval',
                    'matchmaker_mode',
                    'verified_badge'
                ],
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $planData) {
            $features = $planData['features'];
            unset($planData['features']); // Remove features from plan data
            
            $plan = SubscriptionPlan::updateOrCreate(
                ['tier' => $planData['tier']],
                $planData
            );

            // Associate features with the plan
            if (!empty($features)) {
                $featureIds = PlanFeature::whereIn('key', $features)->pluck('id');
                $plan->features()->sync($featureIds);
            }

            // Create pricing for different regions
            $this->createPricingForPlan($plan);
        }
    }

    /**
     * Create regional pricing for a plan
     */
    private function createPricingForPlan(SubscriptionPlan $plan): void
    {
        // Pricing structure based on tier
        $pricingStructure = [
            'free' => [
                ['country_code' => 'US', 'currency' => 'USD', 'price' => 0],
                ['country_code' => 'UZ', 'currency' => 'UZS', 'price' => 0],
                ['country_code' => 'RU', 'currency' => 'RUB', 'price' => 0],
                ['country_code' => 'TR', 'currency' => 'TRY', 'price' => 0],
                ['country_code' => 'KZ', 'currency' => 'KZT', 'price' => 0],
                ['country_code' => 'AE', 'currency' => 'AED', 'price' => 0],
                ['country_code' => 'SA', 'currency' => 'SAR', 'price' => 0],
                ['country_code' => 'GB', 'currency' => 'GBP', 'price' => 0],
                ['country_code' => 'EU', 'currency' => 'EUR', 'price' => 0],
            ],
            'basic' => [
                ['country_code' => 'US', 'currency' => 'USD', 'price' => 9.99],
                ['country_code' => 'UZ', 'currency' => 'UZS', 'price' => 50000],
                ['country_code' => 'RU', 'currency' => 'RUB', 'price' => 499],
                ['country_code' => 'TR', 'currency' => 'TRY', 'price' => 99],
                ['country_code' => 'KZ', 'currency' => 'KZT', 'price' => 2999],
                ['country_code' => 'AE', 'currency' => 'AED', 'price' => 29],
                ['country_code' => 'SA', 'currency' => 'SAR', 'price' => 29],
                ['country_code' => 'GB', 'currency' => 'GBP', 'price' => 7.99],
                ['country_code' => 'EU', 'currency' => 'EUR', 'price' => 8.99],
            ],
            'gold' => [
                ['country_code' => 'US', 'currency' => 'USD', 'price' => 19.99, 'original_price' => 24.99],
                ['country_code' => 'UZ', 'currency' => 'UZS', 'price' => 100000, 'original_price' => 125000],
                ['country_code' => 'RU', 'currency' => 'RUB', 'price' => 999, 'original_price' => 1249],
                ['country_code' => 'TR', 'currency' => 'TRY', 'price' => 199, 'original_price' => 249],
                ['country_code' => 'KZ', 'currency' => 'KZT', 'price' => 5999, 'original_price' => 7499],
                ['country_code' => 'AE', 'currency' => 'AED', 'price' => 59, 'original_price' => 74],
                ['country_code' => 'SA', 'currency' => 'SAR', 'price' => 59, 'original_price' => 74],
                ['country_code' => 'GB', 'currency' => 'GBP', 'price' => 15.99, 'original_price' => 19.99],
                ['country_code' => 'EU', 'currency' => 'EUR', 'price' => 17.99, 'original_price' => 22.49],
            ],
            'platinum' => [
                ['country_code' => 'US', 'currency' => 'USD', 'price' => 39.99, 'original_price' => 49.99],
                ['country_code' => 'UZ', 'currency' => 'UZS', 'price' => 200000, 'original_price' => 250000],
                ['country_code' => 'RU', 'currency' => 'RUB', 'price' => 1999, 'original_price' => 2499],
                ['country_code' => 'TR', 'currency' => 'TRY', 'price' => 399, 'original_price' => 499],
                ['country_code' => 'KZ', 'currency' => 'KZT', 'price' => 11999, 'original_price' => 14999],
                ['country_code' => 'AE', 'currency' => 'AED', 'price' => 119, 'original_price' => 149],
                ['country_code' => 'SA', 'currency' => 'SAR', 'price' => 119, 'original_price' => 149],
                ['country_code' => 'GB', 'currency' => 'GBP', 'price' => 31.99, 'original_price' => 39.99],
                ['country_code' => 'EU', 'currency' => 'EUR', 'price' => 35.99, 'original_price' => 44.99],
            ],
        ];

        $pricingData = $pricingStructure[$plan->tier] ?? [];

        foreach ($pricingData as $pricing) {
            PlanPricing::updateOrCreate(
                [
                    'plan_id' => $plan->id,
                    'country_code' => $pricing['country_code'],
                ],
                [
                    'currency' => $pricing['currency'],
                    'price' => $pricing['price'],
                    'original_price' => $pricing['original_price'] ?? null,
                ]
            );
        }
    }
}