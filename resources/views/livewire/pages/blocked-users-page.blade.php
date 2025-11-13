<div>
    <!-- Header -->
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Safety & Privacy</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Manage blocked users and view your safety reports
                </p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700">
        <div class="px-6">
            <nav class="flex space-x-8">
                <button wire:click="$set('activeTab', 'blocked')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'blocked' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Blocked Users ({{ $blockedUsers->count() }})
                </button>
                <button wire:click="$set('activeTab', 'reports')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'reports' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Reports ({{ $reports->count() }})
                </button>
            </nav>
        </div>
    </div>

    <!-- Search -->
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700 px-6 py-4">
        <div class="max-w-md">
            <div class="relative">
                <input type="text" 
                       wire:model.live.debounce.300ms="searchTerm"
                       placeholder="Search blocked users..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white dark:bg-zinc-800">
        @if($activeTab === 'blocked')
            <!-- Blocked Users Tab -->
            @if($this->getFilteredBlockedUsers()->count() > 0)
                <div class="divide-y divide-gray-200 dark:divide-zinc-700">
                    @foreach($this->getFilteredBlockedUsers() as $block)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <!-- User Avatar -->
                                    @if($block->blockedUser->profilePhoto)
                                        <img src="{{ $block->blockedUser->profilePhoto->thumbnail_url }}" 
                                             alt="{{ $block->blockedUser->name }}"
                                             class="w-12 h-12 rounded-full object-cover">
                                    @else
                                        <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center">
                                            <span class="text-white font-bold text-lg">
                                                {{ substr($block->blockedUser->name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif

                                    <!-- User Info -->
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $block->blockedUser->profile?->first_name ?? $block->blockedUser->name }}
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Blocked {{ $block->created_at->diffForHumans() }}
                                        </p>
                                        @if($block->reason)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Reason: {{ $block->reason }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center space-x-2">
                                    <button wire:click="unblockUser({{ $block->id }})" 
                                            wire:confirm="Are you sure you want to unblock this user? They will be able to see your profile and message you again."
                                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 text-sm font-medium">
                                        Unblock
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State for Blocked Users -->
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-zinc-700 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No blocked users</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        You haven't blocked any users yet. Blocked users won't be able to see your profile or message you.
                    </p>
                </div>
            @endif

        @elseif($activeTab === 'reports')
            <!-- Reports Tab -->
            @if($reports->count() > 0)
                <div class="divide-y divide-gray-200 dark:divide-zinc-700">
                    @foreach($reports as $report)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <!-- Report Icon -->
                                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                    </div>

                                    <!-- Report Info -->
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                            Report #{{ $report->id ?? 'N/A' }}
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Reported {{ $report->created_at ?? now() }} - {{ $report->reason ?? 'Inappropriate behavior' }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Status: {{ $report->status ?? 'Under review' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Safety Score -->
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        Safety Score: {{ $this->getSafetyScore($report->reported_user_id ?? 1) }}%
                                    </div>
                                    <div class="w-20 bg-gray-200 dark:bg-zinc-700 rounded-full h-2 mt-1">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $this->getSafetyScore($report->reported_user_id ?? 1) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State for Reports -->
                <div class="text-center py-12">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-zinc-700 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No reports</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        You haven't reported any users yet. Reports help keep our community safe.
                    </p>
                </div>
            @endif
        @endif
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif
</div>
