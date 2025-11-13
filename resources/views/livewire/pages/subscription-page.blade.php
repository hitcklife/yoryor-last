<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Subscription & Billing</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Manage your subscription and billing preferences</p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Tab Navigation -->
        <div class="mb-8">
            <nav class="flex space-x-8">
                <button wire:click="$set('activeTab', 'overview')"
                        class="py-2 px-1 border-b-2 font-medium text-sm
                               {{ $activeTab === 'overview' 
                                   ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                                   : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                    Overview
                </button>
                <button wire:click="$set('activeTab', 'plans')"
                        class="py-2 px-1 border-b-2 font-medium text-sm
                               {{ $activeTab === 'plans' 
                                   ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                                   : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                    Plans
                </button>
                <button wire:click="$set('activeTab', 'billing')"
                        class="py-2 px-1 border-b-2 font-medium text-sm
                               {{ $activeTab === 'billing' 
                                   ? 'border-blue-500 text-blue-600 dark:text-blue-400' 
                                   : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' }}">
                    Billing History
                </button>
            </nav>
        </div>

        <!-- Overview Tab -->
        @if($activeTab === 'overview')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Current Plan -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Current Plan</h2>
                                @if($currentPlan && $currentPlan['name'] !== 'Free Plan')
                                    <button wire:click="cancelSubscription"
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium">
                                        Cancel Subscription
                                    </button>
                                @endif
                            </div>

                            @if($currentPlan)
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $currentPlan['name'] }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            @if($currentPlan['price'] > 0)
                                                ${{ $currentPlan['price'] }}/{{ $currentPlan['interval'] }}
                                            @else
                                                Free
                                            @endif
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                   {{ $currentPlan['status'] === 'active' 
                                                       ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' 
                                                       : 'bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-200' }}">
                                            {{ ucfirst($currentPlan['status']) }}
                                        </span>
                                        @if($currentPlan['renewal_date'])
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Renews {{ $currentPlan['renewal_date']->format('M j, Y') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Plan Features -->
                                <div class="mt-6">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Plan Features</h4>
                                    <ul class="space-y-2">
                                        @foreach($currentPlan['features'] as $feature)
                                            <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                                <i data-lucide="check" class="w-4 h-4 text-green-500 mr-2"></i>
                                                {{ $feature }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Usage Stats -->
                <div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Usage This Month</h3>
                            
                            <div class="space-y-4">
                                <!-- Likes -->
                                <div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Likes</span>
                                        <span class="text-gray-900 dark:text-white">
                                            {{ $usageStats['likes_used'] }}
                                            @if($usageStats['likes_limit'] > 0)
                                                / {{ $usageStats['likes_limit'] }}
                                            @elseif($usageStats['likes_limit'] === -1)
                                                (unlimited)
                                            @endif
                                        </span>
                                    </div>
                                    @if($usageStats['likes_limit'] > 0)
                                        <div class="mt-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" 
                                                 style="width: {{ $this->getUsagePercentage('likes') }}%"></div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Super Likes -->
                                @if($usageStats['super_likes_limit'] > 0)
                                    <div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">Super Likes</span>
                                            <span class="text-gray-900 dark:text-white">
                                                {{ $usageStats['super_likes_used'] }} / {{ $usageStats['super_likes_limit'] }}
                                            </span>
                                        </div>
                                        <div class="mt-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-purple-600 h-2 rounded-full" 
                                                 style="width: {{ $this->getUsagePercentage('super_likes') }}%"></div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Boosts -->
                                @if($usageStats['boosts_limit'] > 0)
                                    <div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 dark:text-gray-400">Boosts</span>
                                            <span class="text-gray-900 dark:text-white">
                                                {{ $usageStats['boosts_used'] }} / {{ $usageStats['boosts_limit'] }}
                                            </span>
                                        </div>
                                        <div class="mt-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-orange-600 h-2 rounded-full" 
                                                 style="width: {{ $this->getUsagePercentage('boosts') }}%"></div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Profile Views -->
                                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Profile Views</span>
                                        <span class="text-gray-900 dark:text-white">{{ $usageStats['profile_views'] }}</span>
                                    </div>
                                </div>

                                <!-- Matches -->
                                <div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Matches</span>
                                        <span class="text-gray-900 dark:text-white">{{ $usageStats['matches_this_month'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Plans Tab -->
        @if($activeTab === 'plans')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($availablePlans as $plan)
                    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700
                               {{ $plan['popular'] ? 'ring-2 ring-blue-500' : '' }}">
                        @if($plan['popular'])
                            <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
                                <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-medium">
                                    Most Popular
                                </span>
                            </div>
                        @endif

                        <div class="p-6">
                            <div class="text-center">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $plan['name'] }}</h3>
                                <div class="mt-4">
                                    <span class="text-4xl font-bold text-gray-900 dark:text-white">
                                        ${{ $plan['price'] }}
                                    </span>
                                    <span class="text-gray-500 dark:text-gray-400">/{{ $plan['interval'] }}</span>
                                </div>
                                @if($plan['savings'])
                                    <p class="text-sm text-green-600 dark:text-green-400 mt-1">{{ $plan['savings'] }}</p>
                                @endif
                            </div>

                            <ul class="mt-6 space-y-3">
                                @foreach($plan['features'] as $feature)
                                    <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                        <i data-lucide="check" class="w-4 h-4 text-green-500 mr-3"></i>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>

                            <div class="mt-8">
                                @if($currentPlan && $currentPlan['name'] === $plan['name'])
                                    <button disabled
                                            class="w-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 py-2 px-4 rounded-md text-sm font-medium cursor-not-allowed">
                                        Current Plan
                                    </button>
                                @else
                                    <button wire:click="upgradePlan('{{ $plan['id'] }}')"
                                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md text-sm font-medium transition-colors">
                                        @if($plan['price'] > 0)
                                            Upgrade to {{ $plan['name'] }}
                                        @else
                                            Downgrade to {{ $plan['name'] }}
                                        @endif
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Billing History Tab -->
        @if($activeTab === 'billing')
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Billing History</h2>
                    
                    @if(count($paymentHistory) > 0)
                        <div class="overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Description
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Amount
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($paymentHistory as $payment)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ $payment['date']->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                                {{ $payment['description'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                ${{ $payment['amount'] }} {{ $payment['currency'] }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                           {{ $payment['status'] === 'completed' 
                                                               ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200' 
                                                               : 'bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-200' }}">
                                                    {{ ucfirst($payment['status']) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                <button wire:click="downloadInvoice({{ $payment['id'] }})"
                                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                    Download
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <i data-lucide="receipt" class="w-12 h-12 text-gray-400 dark:text-gray-500 mx-auto mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No billing history</h3>
                            <p class="text-gray-500 dark:text-gray-400">Your payment history will appear here once you make your first purchase.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Initialize Lucide icons
    lucide.createIcons();
</script>
@endpush