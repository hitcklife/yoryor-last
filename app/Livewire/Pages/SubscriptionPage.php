<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class SubscriptionPage extends Component
{
    public $currentPlan = null;
    public $availablePlans = [];
    public $paymentHistory = [];
    public $usageStats = [];
    public $activeTab = 'overview';

    protected $queryString = [
        'activeTab' => ['except' => 'overview'],
    ];

    public function mount()
    {
        $this->loadCurrentPlan();
        $this->loadAvailablePlans();
        $this->loadPaymentHistory();
        $this->loadUsageStats();
    }

    public function loadCurrentPlan()
    {
        // TODO: Load from actual subscription model
        $this->currentPlan = [
            'name' => 'Free Plan',
            'price' => 0,
            'currency' => 'USD',
            'interval' => 'month',
            'features' => [
                '5 likes per day',
                'Basic matching',
                'Standard profile',
                'Basic filters'
            ],
            'status' => 'active',
            'renewal_date' => null,
            'auto_renew' => false
        ];
    }

    public function loadAvailablePlans()
    {
        $this->availablePlans = [
            [
                'id' => 'premium',
                'name' => 'Premium',
                'price' => 19.99,
                'currency' => 'USD',
                'interval' => 'month',
                'popular' => true,
                'features' => [
                    'Unlimited likes',
                    'See who liked you',
                    'Advanced filters',
                    'Boost profile',
                    'Read receipts',
                    'Priority support'
                ],
                'savings' => null
            ],
            [
                'id' => 'premium_annual',
                'name' => 'Premium Annual',
                'price' => 199.99,
                'currency' => 'USD',
                'interval' => 'year',
                'popular' => false,
                'features' => [
                    'Everything in Premium',
                    '2 months free',
                    'Exclusive features',
                    'Priority customer support'
                ],
                'savings' => 'Save 17%'
            ],
            [
                'id' => 'platinum',
                'name' => 'Platinum',
                'price' => 39.99,
                'currency' => 'USD',
                'interval' => 'month',
                'popular' => false,
                'features' => [
                    'Everything in Premium',
                    'Platinum badge',
                    'Super likes',
                    'Advanced insights',
                    'VIP events access',
                    'Concierge service'
                ],
                'savings' => null
            ]
        ];
    }

    public function loadPaymentHistory()
    {
        // TODO: Load from actual payment history
        $this->paymentHistory = [
            [
                'id' => 1,
                'date' => now()->subDays(30),
                'amount' => 19.99,
                'currency' => 'USD',
                'description' => 'Premium Monthly',
                'status' => 'completed',
                'invoice_url' => '#'
            ],
            [
                'id' => 2,
                'date' => now()->subDays(60),
                'amount' => 19.99,
                'currency' => 'USD',
                'description' => 'Premium Monthly',
                'status' => 'completed',
                'invoice_url' => '#'
            ]
        ];
    }

    public function loadUsageStats()
    {
        // TODO: Load from actual usage data
        $this->usageStats = [
            'likes_used' => 15,
            'likes_limit' => 5,
            'super_likes_used' => 2,
            'super_likes_limit' => 0,
            'boosts_used' => 0,
            'boosts_limit' => 0,
            'profile_views' => 45,
            'matches_this_month' => 8
        ];
    }

    public function upgradePlan($planId)
    {
        // TODO: Implement actual upgrade logic
        session()->flash('success', 'Plan upgrade initiated! Redirecting to payment...');
        
        // Simulate redirect to payment
        $this->dispatch('redirect-to-payment', planId: $planId);
    }

    public function cancelSubscription()
    {
        // TODO: Implement cancellation logic
        session()->flash('success', 'Subscription cancelled. You will retain access until the end of your billing period.');
    }

    public function updateAutoRenew($enabled)
    {
        // TODO: Implement auto-renewal toggle
        session()->flash('success', 'Auto-renewal ' . ($enabled ? 'enabled' : 'disabled') . ' successfully.');
    }

    public function downloadInvoice($paymentId)
    {
        // TODO: Implement invoice download
        session()->flash('success', 'Invoice download started.');
    }

    public function getPlanFeatures($planId)
    {
        $plan = collect($this->availablePlans)->firstWhere('id', $planId);
        return $plan ? $plan['features'] : [];
    }

    public function getCurrentPlanLimits()
    {
        if (!$this->currentPlan) {
            return [
                'likes' => 5,
                'super_likes' => 0,
                'boosts' => 0
            ];
        }

        return match($this->currentPlan['name']) {
            'Premium' => ['likes' => -1, 'super_likes' => 5, 'boosts' => 1],
            'Platinum' => ['likes' => -1, 'super_likes' => 10, 'boosts' => 3],
            default => ['likes' => 5, 'super_likes' => 0, 'boosts' => 0]
        };
    }

    public function getUsagePercentage($type)
    {
        $stats = $this->usageStats;
        $limits = $this->getCurrentPlanLimits();
        
        if ($limits[$type] === -1) return 0; // Unlimited
        
        $used = $stats[$type . '_used'];
        $limit = $limits[$type];
        
        return $limit > 0 ? round(($used / $limit) * 100) : 0;
    }

    public function render()
    {
        return view('livewire.pages.subscription-page')
            ->layout('layouts.app', ['title' => 'Subscription & Billing']);
    }
}
