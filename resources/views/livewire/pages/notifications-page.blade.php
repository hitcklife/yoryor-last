<!-- Professional Notifications Dashboard -->
<div>
    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex transition-colors duration-300">

        <!-- Left Navigation Sidebar - Full Mode -->
        <x-navigation-sidebar mode="full" />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 p-6 overflow-y-auto">
                <div class="max-w-6xl mx-auto">
                    <!-- Professional Header Section -->
                    <div class="relative bg-gradient-to-r from-purple-600 via-pink-600 to-indigo-600 rounded-3xl p-8 text-white mb-8 overflow-hidden shadow-2xl">
                        <!-- Decorative elements -->
                        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-32 translate-x-32"></div>
                        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/10 rounded-full translate-y-24 -translate-x-24"></div>

                        <div class="relative flex items-center justify-between">
                            <div class="flex items-center space-x-6">
                                <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                                    <x-lucide-bell class="w-10 h-10 text-white" />
                                </div>
                                <div>
                                    <h1 class="text-4xl font-bold mb-2">Your Notifications</h1>
                                    <p class="text-white/90 text-lg">Stay connected with your dating journey</p>
                                    @if($unreadCount > 0)
                                        <p class="text-pink-200 text-sm mt-1">{{ $unreadCount }} unread notification{{ $unreadCount > 1 ? 's' : '' }} waiting for you</p>
                                    @else
                                        <p class="text-pink-200 text-sm mt-1">All caught up! • {{ now()->format('l, F j') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="hidden lg:block">
                                <div class="text-right space-y-3">
                                    @if($unreadCount > 0)
                                        <button wire:click="markAllAsRead"
                                                class="inline-flex items-center px-6 py-3 bg-white/20 backdrop-blur-sm text-white font-medium rounded-2xl hover:bg-white/30 transition-all duration-300 transform hover:scale-105 shadow-lg">
                                            <x-lucide-check class="w-4 h-4 mr-2" />
                                            Mark All Read
                                        </button>
                                    @endif
                                    <button wire:click="clearAllRead"
                                            class="block w-full px-6 py-3 bg-white/10 backdrop-blur-sm text-white font-medium rounded-2xl hover:bg-white/20 transition-all duration-300">
                                        <x-lucide-trash-2 class="w-4 h-4 mr-2 inline" />
                                        Clear Read
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Layout -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        <!-- Professional Categories Sidebar -->
                        <div class="lg:col-span-3 space-y-6">
                            <!-- Categories Card -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border-0 p-6 transition-colors duration-300">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center mr-3">
                                        <x-lucide-filter class="w-4 h-4 text-white" />
                                    </div>
                                    Categories
                                </h3>
                                <nav class="space-y-3">
                                    @foreach($this->getCategories() as $key => $label)
                                        <button wire:click="$set('selectedCategory', '{{ $key }}')"
                                                class="group w-full flex items-center justify-between p-4 text-sm font-medium rounded-2xl transition-all duration-300 transform hover:scale-105
                                                       {{ $selectedCategory === $key
                                                           ? 'bg-gradient-to-r from-purple-500 to-pink-600 text-white shadow-lg'
                                                           : 'text-gray-600 dark:text-gray-400 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 dark:hover:from-purple-900/20 dark:hover:to-pink-900/20 hover:text-purple-700 dark:hover:text-purple-300' }}">
                                            <span class="flex items-center">
                                                <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-3 {{ $selectedCategory === $key ? 'bg-white/20' : 'bg-purple-100 dark:bg-purple-900/30 group-hover:bg-purple-200 dark:group-hover:bg-purple-900/50' }} transition-colors duration-300">
                                                    @if($key === 'match')
                                                        <x-lucide-heart class="w-5 h-5 {{ $selectedCategory === $key ? 'text-white' : $this->getNotificationColor($key) }}" />
                                                    @elseif($key === 'message')
                                                        <x-lucide-message-circle class="w-5 h-5 {{ $selectedCategory === $key ? 'text-white' : $this->getNotificationColor($key) }}" />
                                                    @elseif($key === 'like')
                                                        <x-lucide-thumbs-up class="w-5 h-5 {{ $selectedCategory === $key ? 'text-white' : $this->getNotificationColor($key) }}" />
                                                    @elseif($key === 'view')
                                                        <x-lucide-eye class="w-5 h-5 {{ $selectedCategory === $key ? 'text-white' : $this->getNotificationColor($key) }}" />
                                                    @elseif($key === 'system')
                                                        <x-lucide-bell class="w-5 h-5 {{ $selectedCategory === $key ? 'text-white' : $this->getNotificationColor($key) }}" />
                                                    @else
                                                        <x-lucide-bell class="w-5 h-5 {{ $selectedCategory === $key ? 'text-white' : $this->getNotificationColor($key) }}" />
                                                    @endif
                                                </div>
                                                {{ $label }}
                                            </span>
                                            @if($key !== 'all')
                                                <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full
                                                           {{ $selectedCategory === $key
                                                               ? 'bg-white/20 text-white'
                                                               : 'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 group-hover:bg-purple-200 dark:group-hover:bg-purple-800' }} transition-colors duration-300">
                                                    {{ $notifications->where('type', $key)->count() }}
                                                </span>
                                            @endif
                                        </button>
                                    @endforeach
                                </nav>
                            </div>

                            <!-- Filter Options Card -->
                            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border-0 p-6 transition-colors duration-300">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mr-3">
                                        <x-lucide-settings class="w-4 h-4 text-white" />
                                    </div>
                                    Filters
                                </h3>
                                <div class="space-y-4">
                                    <label class="group flex items-center p-4 bg-gradient-to-r from-gray-50 to-purple-50 dark:from-gray-700 dark:to-purple-900/20 rounded-2xl hover:from-purple-50 hover:to-pink-50 dark:hover:from-purple-900/30 dark:hover:to-pink-900/30 transition-all duration-300 cursor-pointer">
                                        <input type="checkbox"
                                               wire:model.live="showUnreadOnly"
                                               class="rounded-lg border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50 w-5 h-5">
                                        <span class="ml-4 text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-purple-700 dark:group-hover:text-purple-300">
                                            Show unread only
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Notifications Content -->
                        <div class="lg:col-span-9">
                            @if($notifications->count() > 0)
                                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border-0 overflow-hidden transition-colors duration-300">
                                    <div class="px-8 py-6 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-b border-purple-100 dark:border-purple-800 transition-colors duration-300">
                                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mr-4">
                                                @if($selectedCategory === 'match')
                                                    <x-lucide-heart class="w-5 h-5 text-white" />
                                                @elseif($selectedCategory === 'message')
                                                    <x-lucide-message-circle class="w-5 h-5 text-white" />
                                                @elseif($selectedCategory === 'like')
                                                    <x-lucide-thumbs-up class="w-5 h-5 text-white" />
                                                @elseif($selectedCategory === 'view')
                                                    <x-lucide-eye class="w-5 h-5 text-white" />
                                                @elseif($selectedCategory === 'system')
                                                    <x-lucide-bell class="w-5 h-5 text-white" />
                                                @else
                                                    <x-lucide-bell class="w-5 h-5 text-white" />
                                                @endif
                                            </div>
                                            {{ $this->getCategories()[$selectedCategory] }}
                                            <span class="ml-4 bg-purple-100 dark:bg-purple-800 text-purple-700 dark:text-purple-300 text-sm font-bold px-4 py-2 rounded-full">
                                                {{ $notifications->count() }} notification{{ $notifications->count() > 1 ? 's' : '' }}
                                            </span>
                                        </h2>
                                    </div>

                                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                        @foreach($notifications as $notification)
                                            <div class="group p-8 hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 dark:hover:from-purple-900/20 dark:hover:to-pink-900/20 transition-all duration-300
                                                       {{ $notification->read_at ? 'opacity-80' : 'bg-gradient-to-r from-purple-25 to-pink-25 dark:from-purple-900/10 dark:to-pink-900/10 border-l-4 border-purple-500' }}">
                                                <div class="flex items-start space-x-6">
                                                    <!-- Professional Icon -->
                                                    <div class="flex-shrink-0">
                                                        <div class="relative">
                                                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-all duration-300
                                                                       {{ $notification->read_at
                                                                           ? 'bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-600 dark:to-gray-700'
                                                                           : 'bg-gradient-to-br from-purple-500 to-pink-600' }}">
                                                                @if($notification->type === 'match')
                                                                    <x-lucide-heart class="w-7 h-7 {{ $notification->read_at ? $this->getNotificationColor($notification->type) : 'text-white' }}" />
                                                                @elseif($notification->type === 'message')
                                                                    <x-lucide-message-circle class="w-7 h-7 {{ $notification->read_at ? $this->getNotificationColor($notification->type) : 'text-white' }}" />
                                                                @elseif($notification->type === 'like')
                                                                    <x-lucide-thumbs-up class="w-7 h-7 {{ $notification->read_at ? $this->getNotificationColor($notification->type) : 'text-white' }}" />
                                                                @elseif($notification->type === 'view')
                                                                    <x-lucide-eye class="w-7 h-7 {{ $notification->read_at ? $this->getNotificationColor($notification->type) : 'text-white' }}" />
                                                                @elseif($notification->type === 'system')
                                                                    <x-lucide-bell class="w-7 h-7 {{ $notification->read_at ? $this->getNotificationColor($notification->type) : 'text-white' }}" />
                                                                @else
                                                                    <x-lucide-bell class="w-7 h-7 {{ $notification->read_at ? $this->getNotificationColor($notification->type) : 'text-white' }}" />
                                                                @endif
                                                            </div>
                                                            @if(!$notification->read_at)
                                                                <div class="absolute -top-1 -right-1 w-5 h-5 bg-green-500 border-2 border-white rounded-full"></div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Enhanced Content -->
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-start justify-between">
                                                            <div class="flex-1">
                                                                <div class="flex items-center mb-2">
                                                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                                                        {{ $notification->title ?? 'Notification' }}
                                                                    </h3>
                                                                    @if(!$notification->read_at)
                                                                        <span class="ml-3 inline-flex items-center px-3 py-1 text-xs font-bold rounded-full bg-gradient-to-r from-green-500 to-emerald-600 text-white">
                                                                            NEW
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                <p class="text-gray-600 dark:text-gray-400 text-lg mb-3 leading-relaxed">
                                                                    {{ $notification->message ?? 'You have a new notification' }}
                                                                </p>
                                                                <div class="flex items-center text-sm text-gray-500 dark:text-gray-500">
                                                                    <x-lucide-clock class="w-4 h-4 mr-2" />
                                                                    {{ $notification->created_at->diffForHumans() }}
                                                                </div>
                                                            </div>

                                                            <!-- Professional Action Buttons -->
                                                            <div class="flex items-center space-x-3 ml-6">
                                                                @if(!$notification->read_at)
                                                                    <button wire:click="markAsRead({{ $notification->id }})"
                                                                            class="group/btn p-3 bg-green-100 hover:bg-green-200 dark:bg-green-900/30 dark:hover:bg-green-900/50 text-green-600 dark:text-green-400 rounded-2xl transition-all duration-300 transform hover:scale-110"
                                                                            title="Mark as read">
                                                                        <x-lucide-check class="w-5 h-5" />
                                                                    </button>
                                                                @endif

                                                                <button wire:click="deleteNotification({{ $notification->id }})"
                                                                        class="group/btn p-3 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-2xl transition-all duration-300 transform hover:scale-110"
                                                                        title="Delete notification"
                                                                        onclick="return confirm('Are you sure you want to delete this notification?')">
                                                                    <x-lucide-trash-2 class="w-5 h-5" />
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <!-- Professional Empty State -->
                                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border-0 p-16 text-center transition-colors duration-300 relative overflow-hidden">
                                    <!-- Decorative elements -->
                                    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-purple-100 to-pink-100 dark:from-purple-900/20 dark:to-pink-900/20 rounded-full -translate-y-20 translate-x-20 opacity-50"></div>
                                    <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-full translate-y-16 -translate-x-16 opacity-50"></div>

                                    <div class="relative">
                                        <div class="mx-auto w-32 h-32 bg-gradient-to-br from-purple-500 to-pink-600 rounded-3xl flex items-center justify-center mb-8 shadow-2xl">
                                            @if($selectedCategory === 'match')
                                                <x-lucide-heart class="w-16 h-16 text-white" />
                                            @elseif($selectedCategory === 'message')
                                                <x-lucide-message-circle class="w-16 h-16 text-white" />
                                            @elseif($selectedCategory === 'like')
                                                <x-lucide-thumbs-up class="w-16 h-16 text-white" />
                                            @elseif($selectedCategory === 'view')
                                                <x-lucide-eye class="w-16 h-16 text-white" />
                                            @elseif($selectedCategory === 'system')
                                                <x-lucide-bell class="w-16 h-16 text-white" />
                                            @else
                                                <x-lucide-bell class="w-16 h-16 text-white" />
                                            @endif
                                        </div>

                                        <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                                            @if($selectedCategory === 'all')
                                                All Caught Up! 🎉
                                            @else
                                                No {{ $this->getCategories()[$selectedCategory] }} Yet
                                            @endif
                                        </h3>

                                        <p class="text-xl text-gray-600 dark:text-gray-400 max-w-lg mx-auto mb-8 leading-relaxed">
                                            @if($selectedCategory === 'all')
                                                You're all up to date! New notifications about matches, messages, and dating activities will appear here.
                                            @else
                                                {{ $this->getCategories()[$selectedCategory] }} will appear here when they arrive. Keep engaging with the community!
                                            @endif
                                        </p>

                                        @if($selectedCategory === 'all')
                                            <div class="flex justify-center space-x-4 mt-8">
                                                <a href="{{ route('discover') }}"
                                                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-medium rounded-2xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                                                    <x-lucide-heart class="w-4 h-4 mr-2" />
                                                    Find Matches
                                                </a>
                                                <a href="{{ route('messages') }}"
                                                   class="inline-flex items-center px-6 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-300">
                                                    <x-lucide-message-circle class="w-4 h-4 mr-2" />
                                                    View Messages
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Mobile Action Buttons (shown on mobile only) -->
                    <div class="lg:hidden mt-8 flex flex-col sm:flex-row gap-4">
                        @if($unreadCount > 0)
                            <button wire:click="markAllAsRead"
                                    class="flex-1 inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-medium rounded-2xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                                <x-lucide-check class="w-5 h-5 mr-3" />
                                Mark All Read ({{ $unreadCount }})
                            </button>
                        @endif
                        <button wire:click="clearAllRead"
                                class="flex-1 inline-flex items-center justify-center px-6 py-4 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-300">
                            <x-lucide-trash-2 class="w-5 h-5 mr-3" />
                            Clear Read
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Toast (if needed) -->
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)"
             class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-4 rounded-2xl shadow-lg z-50">
            <div class="flex items-center">
                <x-lucide-check class="w-5 h-5 mr-3" />
                {{ session('success') }}
            </div>
        </div>
    @endif
</div>
