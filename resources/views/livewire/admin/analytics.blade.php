<div>
    <!-- Analytics Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-900">Analytics & Insights</h2>
            <div class="flex items-center space-x-4">
                <!-- Date Range Selector -->
                <select wire:model.live="dateRange" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="365">Last year</option>
                </select>
                
                <!-- Metric Selector -->
                <select wire:model.live="selectedMetric" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                    <option value="users">Users</option>
                    <option value="matches">Matches</option>
                    <option value="messages">Messages</option>
                </select>
            </div>
        </div>

        <!-- Key Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-3xl font-bold">{{ number_format($totalUsers) }}</div>
                        <div class="text-blue-100 mt-1">Total Users</div>
                        <div class="text-sm text-blue-200 mt-2">+{{ number_format($newUsers) }} this period</div>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Matches -->
            <div class="bg-gradient-to-br from-pink-500 to-pink-600 text-white rounded-lg p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-3xl font-bold">{{ number_format($totalMatches) }}</div>
                        <div class="text-pink-100 mt-1">Total Matches</div>
                        <div class="text-sm text-pink-200 mt-2">+{{ number_format($newMatches) }} this period</div>
                    </div>
                    <div class="bg-pink-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Messages -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-3xl font-bold">{{ number_format($totalMessages) }}</div>
                        <div class="text-green-100 mt-1">Total Messages</div>
                        <div class="text-sm text-green-200 mt-2">+{{ number_format($newMessages) }} this period</div>
                    </div>
                    <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-lg p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-3xl font-bold">{{ number_format($activeUsers) }}</div>
                        <div class="text-purple-100 mt-1">Active Users (30d)</div>
                        <div class="text-sm text-purple-200 mt-2">{{ $totalUsers > 0 ? number_format(($activeUsers / $totalUsers) * 100, 1) : 0 }}% of total</div>
                    </div>
                    <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Secondary Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-8">
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <div class="text-2xl font-bold text-gray-900">{{ number_format($totalLikes) }}</div>
                <div class="text-sm text-gray-500">Total Likes</div>
                <div class="text-xs text-green-600 mt-1">+{{ number_format($newLikes) }}</div>
            </div>
            
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <div class="text-2xl font-bold text-gray-900">{{ $matchRate }}%</div>
                <div class="text-sm text-gray-500">Match Rate</div>
                <div class="text-xs text-gray-600 mt-1">Matches/Likes</div>
            </div>
            
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <div class="text-2xl font-bold text-gray-900">{{ $averageMessagesPerUser }}</div>
                <div class="text-sm text-gray-500">Avg Messages/User</div>
                <div class="text-xs text-gray-600 mt-1">Engagement</div>
            </div>
            
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <div class="text-2xl font-bold text-orange-600">{{ number_format($pendingReports) }}</div>
                <div class="text-sm text-gray-500">Pending Reports</div>
                <div class="text-xs text-gray-600 mt-1">of {{ number_format($totalReports) }} total</div>
            </div>
            
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <div class="text-2xl font-bold text-yellow-600">{{ number_format($pendingVerifications) }}</div>
                <div class="text-sm text-gray-500">Pending Verifications</div>
                <div class="text-xs text-gray-600 mt-1">of {{ number_format($totalVerifications) }} total</div>
            </div>
            
            <div class="bg-white rounded-lg p-4 border border-gray-200">
                <div class="text-lg font-bold text-gray-900">{{ $mostActiveDay?->day ?? 'N/A' }}</div>
                <div class="text-sm text-gray-500">Most Active Day</div>
                <div class="text-xs text-gray-600 mt-1">{{ $peakHour ? ($peakHour->hour . ':00') : 'N/A' }} peak hour</div>
            </div>
        </div>
    </div>

    <!-- Charts and Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Growth Chart -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ ucfirst($selectedMetric) }} Growth ({{ $dateRange }} days)
                </h3>
            </div>
            <div class="p-6">
                <div class="h-64 flex items-end justify-between space-x-1">
                    @php
                        $data = $selectedMetric === 'users' ? $userGrowthData : 
                               ($selectedMetric === 'matches' ? $matchesData : $messagesData);
                        $maxCount = $data->max('count') ?: 1;
                    @endphp
                    @if($data->count() > 0)
                        @foreach($data->take(20) as $day)
                            <div class="flex-1 flex flex-col items-center">
                                <div class="w-full 
                                    {{ $selectedMetric === 'users' ? 'bg-blue-500' : 
                                       ($selectedMetric === 'matches' ? 'bg-pink-500' : 'bg-green-500') }} 
                                    rounded-sm" 
                                     style="height: {{ $day['count'] > 0 ? max(4, ($day['count'] / $maxCount) * 200) : 4 }}px">
                                </div>
                                <span class="text-xs text-gray-500 mt-2 transform -rotate-45 origin-left">{{ $day['date'] }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-500">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                                </svg>
                                <p class="text-sm">No data available</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Top Countries -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Top Countries</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($topCountries as $country)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-600">{{ substr($country->country_name, 0, 2) }}</span>
                                </div>
                                <span class="font-medium text-gray-900">{{ $country->country_name }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-bold text-gray-900">{{ number_format($country->user_count) }}</span>
                                <div class="w-16 h-2 bg-gray-200 rounded-full">
                                    <div class="h-2 bg-blue-500 rounded-full" 
                                         style="width: {{ $topCountries->count() > 0 ? ($country->user_count / $topCountries->first()->user_count) * 100 : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">No country data available</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Demographics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Gender Breakdown -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Gender Distribution</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($genderBreakdown as $gender)
                        @php
                            $total = $genderBreakdown->sum('count');
                            $percentage = $total > 0 ? ($gender->count / $total) * 100 : 0;
                            $color = match(strtolower($gender->gender)) {
                                'male' => 'blue',
                                'female' => 'pink',
                                'other' => 'purple',
                                default => 'gray'
                            };
                        @endphp
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 bg-{{ $color }}-500 rounded-full"></div>
                                <span class="font-medium text-gray-900">{{ ucfirst($gender->gender) }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-bold text-gray-900">{{ number_format($gender->count) }}</span>
                                <span class="text-xs text-gray-500">({{ number_format($percentage, 1) }}%)</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-{{ $color }}-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">No gender data available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Age Groups -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Age Distribution</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($ageGroups as $ageGroup)
                        @php
                            $total = $ageGroups->sum('count');
                            $percentage = $total > 0 ? ($ageGroup->count / $total) * 100 : 0;
                        @endphp
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-4 h-4 bg-indigo-500 rounded-full"></div>
                                <span class="font-medium text-gray-900">{{ $ageGroup->age_group }} years</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-bold text-gray-900">{{ number_format($ageGroup->count) }}</span>
                                <span class="text-xs text-gray-500">({{ number_format($percentage, 1) }}%)</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-4">No age data available</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Insights and Recommendations -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Key Insights & Recommendations</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- User Growth Insight -->
                <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-400">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <h4 class="font-semibold text-blue-900">User Growth</h4>
                    </div>
                    <p class="text-sm text-blue-800">
                        {{ $newUsers }} new users in the last {{ $dateRange }} days. 
                        @if($newUsers > 0)
                            Growth is {{ $newUsers > 100 ? 'strong' : 'steady' }}. Consider marketing campaigns during peak days.
                        @else
                            Focus on user acquisition strategies.
                        @endif
                    </p>
                </div>

                <!-- Engagement Insight -->
                <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-400">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7z" clip-rule="evenodd"/>
                        </svg>
                        <h4 class="font-semibold text-green-900">Engagement</h4>
                    </div>
                    <p class="text-sm text-green-800">
                        {{ $averageMessagesPerUser }} messages per user average. 
                        @if($averageMessagesPerUser > 10)
                            Excellent engagement! Users are actively communicating.
                        @elseif($averageMessagesPerUser > 5)
                            Good engagement levels. Consider features to boost conversations.
                        @else
                            Low engagement. Implement conversation starters and icebreakers.
                        @endif
                    </p>
                </div>

                <!-- Safety Insight -->
                <div class="bg-orange-50 rounded-lg p-4 border-l-4 border-orange-400">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-orange-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92z" clip-rule="evenodd"/>
                        </svg>
                        <h4 class="font-semibold text-orange-900">Safety Status</h4>
                    </div>
                    <p class="text-sm text-orange-800">
                        {{ $pendingReports }} reports and {{ $pendingVerifications }} verifications pending review.
                        @if($pendingReports > 10)
                            High report volume - prioritize review process.
                        @elseif($pendingReports > 0)
                            Some reports need attention.
                        @else
                            Platform safety is well maintained.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>