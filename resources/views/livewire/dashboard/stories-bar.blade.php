<!-- Instagram-Style Stories Bar -->
<div class="bg-white dark:bg-zinc-800 rounded-2xl shadow-sm border border-gray-100 dark:border-zinc-700 p-4 mb-6 transition-colors duration-300">
    <div class="flex items-center space-x-4 overflow-x-auto scrollbar-hide pb-2">

        <!-- Your Story -->
        <div class="flex-shrink-0">
            <button wire:click="createStory"
                    class="flex flex-col items-center space-y-2 group">
                <div class="relative">
                    @if($userHasStory)
                        <!-- User has active story - show avatar with gradient ring -->
                        <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-purple-500 via-pink-500 to-orange-400 p-1">
                            <div class="w-full h-full bg-white rounded-full p-0.5">
                                @if(auth()->check() && auth()->user()->profilePhoto)
                                    <img src="{{ auth()->user()->profilePhoto->thumbnail_url }}"
                                         alt="{{ auth()->user()->name }}"
                                         class="w-full h-full rounded-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">{{ substr(auth()->user()?->name ?? 'U', 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <!-- Plus icon for existing story -->
                        <div class="absolute bottom-0 right-0 w-5 h-5 bg-blue-500 hover:bg-blue-600 text-white rounded-full border-2 border-white shadow-lg flex items-center justify-center transition-colors duration-200">
                            <x-lucide-plus class="w-3 h-3" />
                        </div>
                    @else
                        <!-- User has no story - show profile image with plus icon overlay -->
                        <div class="relative w-16 h-16">
                            @if(auth()->check() && auth()->user()->profilePhoto)
                                <img src="{{ auth()->user()->profilePhoto->thumbnail_url }}"
                                     alt="{{ auth()->user()->name }}"
                                     class="w-full h-full rounded-full object-cover border-2 border-gray-200 group-hover:border-purple-300 transition-colors duration-300">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center border-2 border-gray-200 group-hover:border-purple-300 transition-colors duration-300">
                                    <span class="text-white font-bold text-sm">{{ substr(auth()->user()?->name ?? 'U', 0, 1) }}</span>
                                </div>
                            @endif
                            <!-- Plus icon overlay -->
                            <div class="absolute bottom-0 right-0 w-5 h-5 bg-blue-500 hover:bg-blue-600 text-white rounded-full border-2 border-white shadow-lg flex items-center justify-center transition-colors duration-200">
                                <x-lucide-plus class="w-3 h-3" />
                            </div>
                        </div>
                    @endif
                </div>

                <span class="text-xs font-medium text-gray-700 dark:text-zinc-300 group-hover:text-purple-600 transition-colors duration-300 text-center w-16 truncate">
                    {{ $userHasStory ? 'Your Story' : 'Your Story' }}
                </span>
            </button>
        </div>

        <!-- Active Stories from Other Users -->
        @forelse($activeStories as $story)
            <div class="flex-shrink-0">
                <button wire:click="viewStory({{ $story['user_id'] }})"
                        class="flex flex-col items-center space-y-2 group">
                    <div class="relative">
                        <!-- Story ring - gradient for unviewed, gray for viewed -->
                        <div class="w-16 h-16 rounded-full {{ $story['is_viewed'] ? 'bg-gray-300' : 'bg-gradient-to-tr from-purple-500 via-pink-500 to-orange-400' }} p-1 group-hover:scale-105 transition-transform duration-300">
                            <div class="w-full h-full bg-white rounded-full p-0.5">
                                @if($story['user_avatar'])
                                    <img src="{{ $story['user_avatar'] }}"
                                         alt="{{ $story['user_name'] }}"
                                         class="w-full h-full rounded-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">{{ substr($story['user_name'], 0, 1) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- New indicator for recent stories -->
                        @if($story['created_at']->isToday())
                            <div class="absolute top-0 right-0 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
                        @endif
                    </div>

                    <span class="text-xs font-medium {{ $story['is_viewed'] ? 'text-gray-500 dark:text-zinc-500' : 'text-gray-700 dark:text-zinc-300' }} group-hover:text-purple-600 transition-colors duration-300 text-center w-16 truncate">
                        {{ $story['user_name'] }}
                    </span>
                </button>
            </div>
        @empty
            <!-- No stories placeholder -->
            <div class="flex-shrink-0 opacity-50">
                <div class="flex flex-col items-center space-y-2">
                    <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-zinc-700 border-2 border-dashed border-gray-200 dark:border-zinc-600 flex items-center justify-center">
                        <x-lucide-clock class="w-6 h-6 text-gray-300 dark:text-zinc-500" />
                    </div>
                    <span class="text-xs text-gray-400 dark:text-zinc-500 text-center w-16">No stories yet</span>
                </div>
            </div>
        @endforelse

        <!-- Scroll indicator for mobile -->
        @if(count($activeStories) > 5)
            <div class="flex-shrink-0 flex items-center justify-center w-16 h-16 opacity-50 md:hidden">
                <x-lucide-chevron-right class="w-6 h-6 text-gray-400 dark:text-zinc-500" />
            </div>
        @endif
    </div>

    <!-- Custom Scrollbar Styles -->
    <style>
        .scrollbar-hide {
            -ms-overflow-style: none;  /* Internet Explorer 10+ */
            scrollbar-width: none;  /* Firefox */
        }
        .scrollbar-hide::-webkit-scrollbar {
            display: none;  /* Safari and Chrome */
        }
    </style>
</div>

