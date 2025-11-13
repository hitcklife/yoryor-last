@props([
    'user' => null
])

@php
$user = $user ?? auth()->user();
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Profile Views Widget -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="eye" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Profile Views</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ $user->profile_views_count ?? 0 }}
                </p>
                <p class="text-xs text-green-600 dark:text-green-400">
                    +12% from last week
                </p>
            </div>
        </div>
    </div>
    
    <!-- Matches Widget -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-pink-100 dark:bg-pink-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="heart" class="w-4 h-4 text-pink-600 dark:text-pink-400"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">New Matches</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ $user->new_matches_count ?? 0 }}
                </p>
                <p class="text-xs text-green-600 dark:text-green-400">
                    {{ $user->new_matches_count ?? 0 }} this week
                </p>
            </div>
        </div>
    </div>
    
    <!-- Messages Widget -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="message-circle" class="w-4 h-4 text-green-600 dark:text-green-400"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Messages</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ $user->unread_messages_count ?? 0 }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Unread messages
                </p>
            </div>
        </div>
    </div>
    
    <!-- Verification Widget -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i data-lucide="shield-check" class="w-4 h-4 text-purple-600 dark:text-purple-400"></i>
                </div>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Verification</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ $user->verification_score ?? 0 }}%
                </p>
                <p class="text-xs text-blue-600 dark:text-blue-400">
                    <a href="{{ route('verification') }}" class="hover:underline">Complete profile</a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Activity Summary -->
    <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Activity Summary</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Likes sent this week</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->likes_sent_this_week ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Super likes remaining</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->super_likes_remaining ?? 0 }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Profile boost available</span>
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->boosts_remaining ?? 0 }}</span>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Quick Actions</h3>
        <div class="space-y-3">
            <a href="{{ route('search') }}" 
               class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                <i data-lucide="search" class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3"></i>
                <span class="text-sm font-medium text-blue-900 dark:text-blue-300">Find Matches</span>
            </a>
            
            <a href="{{ route('messages') }}" 
               class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                <i data-lucide="message-circle" class="w-5 h-5 text-green-600 dark:text-green-400 mr-3"></i>
                <span class="text-sm font-medium text-green-900 dark:text-green-300">View Messages</span>
            </a>
            
            <a href="{{ route('my-profile') }}" 
               class="flex items-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                <i data-lucide="user" class="w-5 h-5 text-purple-600 dark:text-purple-400 mr-3"></i>
                <span class="text-sm font-medium text-purple-900 dark:text-purple-300">Edit Profile</span>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize Lucide icons for dashboard widgets
    document.addEventListener('DOMContentLoaded', function() {
        if (window.lucide && window.lucide.createIcons) {
            window.lucide.createIcons({ icons: window.lucide.icons });
        }
    });
</script>
@endpush
