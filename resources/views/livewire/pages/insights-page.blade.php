<div>
    <!-- Header -->
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Insights & Analytics</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Track your dating success and optimize your profile
                </p>
            </div>
            
            <div class="flex items-center space-x-4">
                <!-- Date Range Selector -->
                <select wire:model.live="dateRange" 
                        class="px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white text-sm">
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="365">Last year</option>
                </select>
                
                <button wire:click="exportData" 
                        class="px-4 py-2 border border-gray-300 dark:border-zinc-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors duration-200 text-sm font-medium">
                    Export Data
                </button>
                
                <button wire:click="generateReport" 
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200 text-sm font-medium">
                    Generate Report
                </button>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700">
        <div class="px-6">
            <nav class="flex space-x-8">
                <button wire:click="$set('activeTab', 'overview')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'overview' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Overview
                </button>
                <button wire:click="$set('activeTab', 'profile')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'profile' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Profile Analytics
                </button>
                <button wire:click="$set('activeTab', 'matches')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'matches' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Match Insights
                </button>
                <button wire:click="$set('activeTab', 'messages')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'messages' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Message Analytics
                </button>
                <button wire:click="$set('activeTab', 'demographics')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'demographics' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Demographics
                </button>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white dark:bg-zinc-800 p-6">
        @if($activeTab === 'overview')
            <!-- Overview Dashboard -->
            <div class="max-w-7xl mx-auto">
                <!-- Key Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Profile Views -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl p-6 border border-blue-200 dark:border-blue-700">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium {{ $this->getTrendColor($profileViews['trend']) }}">
                                {{ $profileViews['trend'] }}
                            </span>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                            {{ number_format($profileViews['total_views']) }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Total Profile Views</div>
                    </div>

                    <!-- Matches -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-xl p-6 border border-green-200 dark:border-green-700">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-green-600">+{{ $matchStats['matches_this_week'] }} this week</span>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                            {{ $matchStats['total_matches'] }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Total Matches</div>
                    </div>

                    <!-- Messages -->
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-xl p-6 border border-purple-200 dark:border-purple-700">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-purple-600">{{ $messageStats['response_rate'] * 100 }}% response rate</span>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                            {{ $messageStats['total_messages_sent'] }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Messages Sent</div>
                    </div>

                    <!-- Success Score -->
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-xl p-6 border border-orange-200 dark:border-orange-700">
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <span class="text-sm font-medium {{ $this->getSuccessScoreColor($successMetrics['overall_success_score']) }}">
                                {{ $this->getSuccessScoreText($successMetrics['overall_success_score']) }}
                            </span>
                        </div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                            {{ $successMetrics['overall_success_score'] }}%
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Success Score</div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Profile Views Chart -->
                    <div class="bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Profile Views Trend</h3>
                        <div class="h-64 flex items-end justify-between space-x-2">
                            @foreach($profileViews['chart_data'] as $data)
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-full bg-blue-500 rounded-t" 
                                         style="height: {{ ($data['views'] / max(array_column($profileViews['chart_data'], 'views'))) * 200 }}px"></div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                                        {{ \Carbon\Carbon::parse($data['date'])->format('M j') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Match Success Rate -->
                    <div class="bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Match Success Rate</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Like to Match Ratio</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $matchStats['like_to_match_ratio'] * 100 }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-zinc-600 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $matchStats['like_to_match_ratio'] * 100 }}%"></div>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Super Like Success</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $matchStats['super_like_success_rate'] * 100 }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-zinc-600 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $matchStats['super_like_success_rate'] * 100 }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommendations -->
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-xl border border-blue-200 dark:border-blue-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">💡 Recommendations</h3>
                    <div class="space-y-3">
                        @foreach($successMetrics['recommendations'] as $recommendation)
                            <div class="flex items-start space-x-3">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $recommendation }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        @elseif($activeTab === 'profile')
            <!-- Profile Analytics -->
            <div class="max-w-6xl mx-auto">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Profile Performance</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Profile Completeness -->
                    <div class="bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Profile Completeness</h3>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Overall Completeness</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $activityStats['profile_completeness'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-zinc-600 rounded-full h-3">
                                <div class="bg-blue-500 h-3 rounded-full" style="width: {{ $activityStats['profile_completeness'] }}%"></div>
                            </div>
                            
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Photos</span>
                                    <span class="text-gray-900 dark:text-white">{{ $activityStats['photos_uploaded'] }}/6</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Bio Length</span>
                                    <span class="text-gray-900 dark:text-white">{{ $activityStats['bio_length'] }} characters</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Interests</span>
                                    <span class="text-gray-900 dark:text-white">{{ $activityStats['interests_added'] }}/10</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Performing Photos -->
                    <div class="bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Performing Photos</h3>
                        <div class="space-y-4">
                            @foreach($this->getTopPerformingPhotos() as $photo)
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gray-200 dark:bg-zinc-600 rounded-lg flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $photo['views'] }} views</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ $photo['engagement_rate'] }}% engagement</div>
                                    </div>
                                    <div class="text-sm font-medium text-green-600">{{ $photo['likes'] }} likes</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        @elseif($activeTab === 'matches')
            <!-- Match Insights -->
            <div class="max-w-6xl mx-auto">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Match Insights</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Matches by Day -->
                    <div class="bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Matches by Day of Week</h3>
                        <div class="space-y-3">
                            @foreach($matchStats['matches_by_day'] as $day => $count)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $day }}</span>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-24 bg-gray-200 dark:bg-zinc-600 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full" 
                                                 style="width: {{ ($count / max($matchStats['matches_by_day'])) * 100 }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white w-8">{{ $count }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Top Interests -->
                    <div class="bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Matching Interests</h3>
                        <div class="space-y-3">
                            @foreach($matchStats['top_interests'] as $interest => $count)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $interest }}</span>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-24 bg-gray-200 dark:bg-zinc-600 rounded-full h-2">
                                            <div class="bg-purple-500 h-2 rounded-full" 
                                                 style="width: {{ ($count / max($matchStats['top_interests'])) * 100 }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white w-8">{{ $count }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

        @elseif($activeTab === 'messages')
            <!-- Message Analytics -->
            <div class="max-w-6xl mx-auto">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Message Analytics</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Response Rate -->
                    <div class="bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Response Rate</h3>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ $messageStats['response_rate'] * 100 }}%
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Average Response Rate</div>
                        </div>
                    </div>

                    <!-- Average Response Time -->
                    <div class="bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Response Time</h3>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ $messageStats['average_response_time'] }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Average Response Time</div>
                        </div>
                    </div>

                    <!-- Conversation Starter Success -->
                    <div class="bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Conversation Starters</h3>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ $messageStats['conversation_starter_success'] * 100 }}%
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Success Rate</div>
                        </div>
                    </div>
                </div>

                <!-- Conversation Insights -->
                <div class="mt-6 bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Conversation Insights</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Most Engaging Topics</h4>
                            <div class="space-y-2">
                                @foreach($this->getConversationInsights()['most_engaging_topics'] as $topic => $rate)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $topic }}</span>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $rate * 100 }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Best Response Time</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ $this->getConversationInsights()['best_response_time'] }}</p>
                            
                            <h4 class="font-medium text-gray-900 dark:text-white mb-2">Most Common Opener</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400">"{{ $this->getConversationInsights()['most_common_opener'] }}"</p>
                        </div>
                    </div>
                </div>
            </div>

        @elseif($activeTab === 'demographics')
            <!-- Demographics -->
            <div class="max-w-6xl mx-auto">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Audience Demographics</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Age Distribution -->
                    <div class="bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Age Distribution</h3>
                        <div class="space-y-3">
                            @foreach($demographics['age_distribution'] as $age => $percentage)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $age }}</span>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-24 bg-gray-200 dark:bg-zinc-600 rounded-full h-2">
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white w-8">{{ $percentage }}%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Education Levels -->
                    <div class="bg-white dark:bg-zinc-700 rounded-xl border border-gray-200 dark:border-zinc-600 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Education Levels</h3>
                        <div class="space-y-3">
                            @foreach($demographics['education_levels'] as $level => $percentage)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $level }}</span>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-24 bg-gray-200 dark:bg-zinc-600 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white w-8">{{ $percentage }}%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif
</div>
