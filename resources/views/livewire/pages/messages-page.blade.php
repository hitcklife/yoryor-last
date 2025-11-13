<div data-page="messages" data-user-id="{{ auth()->id() }}"
     x-data="messagesComponent()"
     x-init="init()">
    <!-- Messages Page with Sidebar -->
    <div class="h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 dark:bg-zinc-900 flex overflow-hidden transition-colors duration-300">

        <!-- Left Navigation Sidebar - Compact Mode -->
        <x-navigation-sidebar mode="compact" />

        <!-- Main Messages Area -->
        <div class="flex-1 flex bg-white dark:bg-zinc-800 relative overflow-hidden transition-colors duration-300">
            <!-- Messages List Panel - Compact Width -->
            <div class="w-20 sm:w-64 md:w-80 lg:w-96 bg-gray-50 dark:bg-zinc-900 border-r border-gray-200 dark:border-zinc-700 flex flex-col h-full transition-colors duration-300">
        <!-- Header -->
        <div class="p-2 sm:p-4 lg:p-6 border-b border-gray-200 dark:border-zinc-700 transition-colors duration-300">
            <div class="flex items-center justify-between mb-4">
                <h1 class="hidden sm:block text-xl lg:text-2xl font-bold text-gray-900 dark:text-white transition-colors duration-300">Messages</h1>
                <div class="sm:hidden w-10 h-10 bg-gradient-to-r from-pink-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold text-lg">Y</span>
                </div>
                <button class="text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                    <x-lucide-edit class="w-6 h-6" />
                </button>
            </div>

            <!-- Search Bar - Hide on mobile -->
            <div class="relative hidden sm:block">
                <input type="text"
                       wire:model.live="searchTerm"
                       placeholder="Search messages"
                       class="w-full px-4 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-zinc-500 border border-gray-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-300">
                <x-lucide-search class="absolute right-3 top-2.5 w-5 h-5 text-gray-500 dark:text-zinc-400" />
            </div>

            <!-- Filter Pills - Hide on mobile -->
            <div class="hidden sm:flex space-x-2 mt-4">
                <button wire:click="setFilter('all')"
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition-all {{ $activeFilter === 'all' ? 'bg-purple-600 text-white' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white' }}">
                    All
                </button>
                <button wire:click="setFilter('unread')"
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition-all {{ $activeFilter === 'unread' ? 'bg-purple-600 text-white' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white' }}">
                    Unread
                    @if(collect($conversations)->where('unread_count', '>', 0)->count() > 0)
                        <span class="ml-1 text-xs">{{ collect($conversations)->where('unread_count', '>', 0)->count() }}</span>
                    @endif
                </button>
                <button wire:click="setFilter('online')"
                        class="px-4 py-1.5 rounded-full text-sm font-medium transition-all {{ $activeFilter === 'online' ? 'bg-purple-600 text-white' : 'bg-gray-100 dark:bg-zinc-800 text-gray-600 dark:text-zinc-400 hover:text-gray-900 dark:hover:text-white' }}">
                    Online
                </button>
            </div>
        </div>

        <!-- Conversations List -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden">
            @forelse($filteredConversations as $conversation)
                <div wire:click="openChat({{ $conversation['id'] }})"
                     class="flex items-center px-2 sm:px-4 lg:px-6 py-3 hover:bg-white dark:hover:bg-zinc-800 cursor-pointer transition-all {{ $selectedConversation == $conversation['id'] ? 'bg-purple-50 dark:bg-purple-900/20' : '' }}">

                    <!-- Avatar -->
                    <div class="relative sm:mr-3">
                        @if($conversation['avatar'])
                            <img src="{{ $conversation['avatar'] }}"
                                 alt="{{ $conversation['name'] }}"
                                 class="w-12 sm:w-14 h-12 sm:h-14 rounded-full object-cover">
                        @else
                            <div class="w-12 sm:w-14 h-12 sm:h-14 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-sm sm:text-lg">{{ substr($conversation['name'], 0, 1) }}</span>
                            </div>
                        @endif

                        @if($conversation['is_online'])
                            <div class="absolute bottom-0 right-0 w-4 h-4 bg-green-500 border-2 border-white rounded-full"></div>
                        @endif
                    </div>

                    <!-- Content - Hide on mobile -->
                    <div class="hidden sm:block flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="text-gray-900 dark:text-white font-medium truncate transition-colors duration-300">{{ $conversation['name'] }}</h3>
                            <span class="text-xs text-gray-500 dark:text-zinc-400">
                                @if($conversation['last_message']['is_today'])
                                    {{ $conversation['last_message']['time_formatted'] }}
                                @else
                                    {{ $conversation['last_message']['sent_at']->format('M j') }}
                                @endif
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <p class="text-sm {{ $conversation['unread_count'] > 0 ? 'text-gray-900 dark:text-white font-semibold' : 'text-gray-600 dark:text-zinc-400' }} truncate">
                                @if($conversation['is_typing'])
                                    <span class="text-purple-600">typing...</span>
                                @else
                                    @if($conversation['last_message']['is_from_me'])
                                        <span class="text-gray-500 dark:text-zinc-500">You: </span>
                                    @endif
                                    @if($conversation['last_message']['type'] === 'image')
                                        📷 Photo
                                    @elseif($conversation['last_message']['type'] === 'voice')
                                        🎤 Voice message
                                    @else
                                        {{ Str::limit($conversation['last_message']['content'], 30) }}
                                    @endif
                                @endif
                            </p>

                            @if($conversation['unread_count'] > 0)
                                <span class="bg-purple-600 text-white text-xs font-bold rounded-full min-w-[20px] h-5 flex items-center justify-center px-1.5">
                                    {{ $conversation['unread_count'] }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="flex flex-col items-center justify-center h-full px-6 py-12">
                    <div class="w-20 h-20 mb-4 bg-zinc-900 dark:bg-zinc-800 rounded-full flex items-center justify-center">
                        <x-lucide-message-circle class="w-10 h-10 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <h3 class="text-white dark:text-white font-semibold mb-2">
                        @if($activeFilter === 'unread')
                            No unread messages
                        @elseif($activeFilter === 'online')
                            No one online
                        @elseif(!empty($searchTerm))
                            No results found
                        @else
                            No messages yet
                        @endif
                    </h3>
                    <p class="text-gray-500 dark:text-zinc-400 text-sm text-center">
                        @if($activeFilter === 'all' && empty($searchTerm))
                            Start a conversation with your matches
                        @else
                            Try adjusting your filters
                        @endif
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Chat Area -->
    @if($selectedConversation)
        @if($showInfoPanel)
            <!-- Information Panel - Replaces Chat -->
            <div class="flex-1 flex flex-col h-full overflow-hidden bg-white dark:bg-zinc-900 transition-all duration-300 slide-in-right">
                <!-- Info Panel Header -->
                <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700 px-4 lg:px-6 py-4 transition-colors duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <!-- Back Button (Mobile) -->
                            <button wire:click="closeChat" class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-full transition-colors">
                                <x-lucide-arrow-left class="w-5 h-5 text-gray-600 dark:text-zinc-400" />
                            </button>

                            <!-- Avatar -->
                            @if($selectedUser?->profilePhoto?->thumbnail_url)
                                <img src="{{ $selectedUser->profilePhoto->thumbnail_url }}"
                                     alt="{{ $selectedUser->profile?->first_name }}"
                                     class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold">{{ substr($selectedUser?->profile?->first_name ?? 'U', 0, 1) }}</span>
                                </div>
                            @endif

                            <!-- User Info -->
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $selectedUser?->profile?->first_name ?? 'User' }} {{ $selectedUser?->profile?->last_name ?? '' }}
                                </h2>
                                <p class="text-sm text-gray-500 dark:text-zinc-400">
                                    @if($selectedUser?->last_active_at && $selectedUser->last_active_at->gt(now()->subMinutes(5)))
                                        Active now
                                    @else
                                        Active {{ $selectedUser?->last_active_at?->diffForHumans() ?? 'recently' }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2">
                            <button wire:click="startAudioCall" class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-full transition-colors group">
                                <x-lucide-phone class="w-6 h-6 text-gray-600 dark:text-zinc-400 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors" />
                            </button>
                            <button wire:click="startVideoCall" class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-full transition-colors group">
                                <x-lucide-video class="w-6 h-6 text-gray-600 dark:text-zinc-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" />
                            </button>
                            <button wire:click="toggleInfoPanel" class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-full transition-colors bg-purple-100 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400">
                                <x-lucide-info class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                            </button>
                        </div>
                    </div>
                </div>
        @else
            <!-- Active Chat -->
            <div class="flex-1 flex flex-col h-full overflow-hidden">
                <!-- Chat Header -->
                <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700 px-4 lg:px-6 py-4 transition-colors duration-300">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <!-- Back Button (Mobile) -->
                            <button wire:click="closeChat" class="lg:hidden p-2 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-full transition-colors">
                                <x-lucide-arrow-left class="w-5 h-5 text-gray-600 dark:text-zinc-400" />
                            </button>

                            <!-- Avatar -->
                            @if($selectedUser?->profilePhoto?->thumbnail_url)
                                <img src="{{ $selectedUser->profilePhoto->thumbnail_url }}"
                                     alt="{{ $selectedUser->profile?->first_name }}"
                                     class="w-10 h-10 rounded-full object-cover">
                            @else
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold">{{ substr($selectedUser?->profile?->first_name ?? 'U', 0, 1) }}</span>
                                </div>
                            @endif

                            <!-- User Info -->
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $selectedUser?->profile?->first_name ?? 'User' }} {{ $selectedUser?->profile?->last_name ?? '' }}
                                </h2>
                                <p class="text-sm text-gray-500 dark:text-zinc-400">
                                    @if($selectedUser?->last_active_at && $selectedUser->last_active_at->gt(now()->subMinutes(5)))
                                        Active now
                                    @else
                                        Active {{ $selectedUser?->last_active_at?->diffForHumans() ?? 'recently' }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center space-x-2">
                            <button wire:click="startAudioCall" class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-full transition-colors group">
                                <x-lucide-phone class="w-6 h-6 text-gray-600 dark:text-zinc-400 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors" />
                            </button>
                            <button wire:click="startVideoCall" class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-full transition-colors group">
                                <x-lucide-video class="w-6 h-6 text-gray-600 dark:text-zinc-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" />
                            </button>
                            <button wire:click="toggleInfoPanel" class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-full transition-colors">
                                <x-lucide-info class="w-6 h-6 text-gray-600 dark:text-zinc-400" />
                            </button>
                        </div>
                    </div>
                </div>
        @endif

        @if(!$showInfoPanel)
            <!-- Video Call Interface (when active) -->
            @if($isVideoCallActive)
                <div class="flex-1 bg-black relative overflow-hidden" x-data="videoCallInterface()" x-init="initVideoCall()">
                    <!-- Video Grid -->
                    <div class="h-full flex">
                        <!-- Remote Participant Video -->
                        <div class="flex-1 bg-gray-900 flex items-center justify-center relative">
                            <!-- Remote Video Element -->
                            <video
                                id="remote-video"
                                class="w-full h-full object-cover"
                                autoplay
                                playsinline
                                x-show="remoteVideoEnabled"
                            ></video>

                            <!-- Remote Audio Element -->
                            <audio
                                id="remote-audio"
                                autoplay
                            ></audio>

                            <!-- Fallback Avatar when video is off -->
                            <div x-show="!remoteVideoEnabled" class="text-center">
                                <div class="w-32 h-32 mx-auto mb-4 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-4xl font-bold">{{ substr($selectedUser?->profile?->first_name ?? 'U', 0, 1) }}</span>
                                </div>
                                <h3 class="text-xl font-semibold text-white">{{ $selectedUser?->profile?->first_name ?? 'User' }}</h3>
                                </div>

                            <!-- Remote Participant Info Overlay -->
                            <div class="absolute bottom-4 left-4 bg-black bg-opacity-50 backdrop-blur-sm rounded-lg px-3 py-2">
                                <span class="text-white text-sm font-medium">{{ $selectedUser?->profile?->first_name ?? 'User' }}</span>
                                <div class="flex items-center space-x-2 mt-1">
                                    <!-- Mic Status -->
                                    <div class="flex items-center">
                                        <x-lucide-mic x-show="remoteMicEnabled" class="w-3 h-3 text-green-400" />
                                        <x-lucide-mic-off x-show="!remoteMicEnabled" class="w-3 h-3 text-red-400" />
                            </div>
                                    <!-- Video Status -->
                                    <div class="flex items-center">
                                        <x-lucide-video x-show="remoteVideoEnabled" class="w-3 h-3 text-green-400" />
                                        <x-lucide-video-off x-show="!remoteVideoEnabled" class="w-3 h-3 text-red-400" />
                                </div>
                            </div>
                        </div>
                    </div>

                        <!-- Local Video (Your Video) -->
                        <div class="w-80 bg-gray-800 flex items-center justify-center relative">
                            <!-- Local Video Element -->
                            <video
                                id="local-video"
                                class="w-full h-full object-cover"
                                autoplay
                                playsinline
                                muted
                                x-show="localVideoEnabled"
                            ></video>

                            <!-- Fallback Avatar when video is off -->
                            <div x-show="!localVideoEnabled" class="text-center">
                                <div class="w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-blue-400 to-green-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-2xl font-bold">You</span>
                        </div>
                                <h3 class="text-lg font-semibold text-white">You</h3>
                    </div>

                            <!-- Local Status Overlay -->
                            <div class="absolute bottom-4 right-4 bg-black bg-opacity-50 backdrop-blur-sm rounded-lg px-3 py-2">
                                <span class="text-white text-sm font-medium">You</span>
                                <div class="flex items-center space-x-2 mt-1">
                                    <!-- Mic Status -->
                                    <div class="flex items-center">
                                        <x-lucide-mic x-show="localMicEnabled" class="w-3 h-3 text-green-400" />
                                        <x-lucide-mic-off x-show="!localMicEnabled" class="w-3 h-3 text-red-400" />
                    </div>
                                    <!-- Video Status -->
                                    <div class="flex items-center">
                                        <x-lucide-video x-show="localVideoEnabled" class="w-3 h-3 text-green-400" />
                                        <x-lucide-video-off x-show="!localVideoEnabled" class="w-3 h-3 text-red-400" />
                            </div>
                            </div>
                            </div>
                        </div>
                    </div>

                    <!-- Call Controls -->
                    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
                        <div class="bg-black bg-opacity-50 backdrop-blur-sm rounded-full px-8 py-4 flex items-center space-x-4">
                            <!-- Mute Button -->
                            <button @click="toggleMic()"
                                    class="w-12 h-12 text-white rounded-full hover:bg-opacity-80 transition-colors duration-200 flex items-center justify-center"
                                    :class="localMicEnabled ? 'bg-gray-600' : 'bg-red-600'">
                                <x-lucide-mic x-show="localMicEnabled" class="w-6 h-6" />
                                <x-lucide-mic-off x-show="!localMicEnabled" class="w-6 h-6" />
                        </button>

                            <!-- Video Toggle -->
                            <button @click="toggleVideo()"
                                    class="w-12 h-12 text-white rounded-full hover:bg-opacity-80 transition-colors duration-200 flex items-center justify-center"
                                    :class="localVideoEnabled ? 'bg-gray-600' : 'bg-red-600'">
                                <x-lucide-video x-show="localVideoEnabled" class="w-6 h-6" />
                                <x-lucide-video-off x-show="!localVideoEnabled" class="w-6 h-6" />
                        </button>

                            <!-- End Call -->
                            <button @click="endCall()"
                                    class="w-12 h-12 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors duration-200 flex items-center justify-center">
                                <x-lucide-phone-off class="w-6 h-6" />
                        </button>
                        </div>
                    </div>

                    <!-- Call Duration -->
                    <div class="absolute top-4 right-4">
                        <div class="bg-black bg-opacity-50 backdrop-blur-sm rounded-lg px-4 py-2">
                            <div class="text-white text-lg font-mono" x-text="callDuration"></div>
                            </div>
                    </div>

                    <!-- Call Status -->
                    <div class="absolute top-4 left-1/2 transform -translate-x-1/2">
                        <div class="bg-black bg-opacity-50 backdrop-blur-sm rounded-lg px-4 py-2">
                            <div class="text-white text-sm font-medium" x-text="callStatus"></div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Messages Area -->
                <div class="flex-1 overflow-y-auto overflow-x-hidden px-4 lg:px-6 py-4 space-y-4" id="messagesContainer">
                @if(empty($messages))
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-zinc-400">No messages yet. Start the conversation!</p>
                    </div>
                @else
                    @foreach($messages as $message)
                        <div class="flex {{ $message['is_mine'] ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-xs lg:max-w-md">
                                <div class="{{ $message['is_mine'] ? 'bg-purple-600 text-white' : 'bg-gray-100 dark:bg-zinc-700 text-gray-900 dark:text-white' }} px-4 py-2 rounded-2xl">
                                    <p>{{ $message['content'] }}</p>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1 {{ $message['is_mine'] ? 'text-right' : 'text-left' }}">
                                    {{ $message['time'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                @endif

                <!-- Typing Indicator -->
                @if(!empty($typingUsers))
                    <div class="flex justify-start">
                        <div class="max-w-xs lg:max-w-md">
                            <div class="bg-gray-100 dark:bg-zinc-700 text-gray-900 dark:text-white px-4 py-2 rounded-2xl">
                                <div class="flex items-center space-x-1">
                                    <div class="flex space-x-1">
                                        <div class="w-2 h-2 bg-gray-400 dark:bg-zinc-500 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                        <div class="w-2 h-2 bg-gray-400 dark:bg-zinc-500 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                        <div class="w-2 h-2 bg-gray-400 dark:bg-zinc-500 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                                    </div>
                                    <span class="text-sm text-gray-600 dark:text-zinc-400 ml-2">
                                        @if(count($typingUsers) === 1)
                                            {{ array_values($typingUsers)[0] }} is typing...
                                        @else
                                            {{ count($typingUsers) }} people are typing...
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

                <!-- Message Input -->
                <div class="bg-white dark:bg-zinc-800 border-t border-gray-200 dark:border-zinc-700 px-4 lg:px-6 py-4 transition-colors duration-300">
                    <form wire:submit.prevent="sendMessage" class="flex items-center space-x-2">
                        <button type="button" class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-full transition-colors">
                            <x-lucide-smile class="w-6 h-6 text-gray-600 dark:text-zinc-400" />
                        </button>

                        <input type="text"
                               id="messageInput"
                               wire:model="newMessage"
                               wire:keydown="handleStartTyping"
                               wire:keyup="stopTyping"
                               placeholder="Message..."
                               class="flex-1 px-4 py-2 bg-gray-50 dark:bg-zinc-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-zinc-400 rounded-full border border-gray-200 dark:border-zinc-600 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors duration-300">

                        @if(!empty($newMessage))
                            <button type="submit" class="text-purple-600 font-semibold hover:text-purple-700 transition-colors px-2">
                                Send
                            </button>
                        @else
                            <button type="button" class="p-2 hover:bg-gray-100 dark:hover:bg-zinc-700 rounded-full transition-colors">
                                <x-lucide-image class="w-6 h-6 text-gray-600 dark:text-zinc-400" />
                            </button>
                        @endif
                    </form>
                </div>
            @endif
        @endif

        @if($showInfoPanel)
            <!-- Enhanced Two-Column Info Panel -->
            <div class="flex-1 flex h-full overflow-hidden bg-white dark:bg-zinc-900 transition-all duration-300 slide-in-right">
                <!-- Left Column - Photo Section -->
                <div class="w-2/5 relative overflow-hidden">
                    <!-- Main Photo Display -->
                    <div class="relative h-full overflow-hidden">
                        @if($selectedUser?->profilePhoto?->thumbnail_url)
                            <!-- Glass Morphism Background -->
                            <div class="absolute inset-0">
                                <img src="{{ $selectedUser->profilePhoto->thumbnail_url }}"
                                     alt="Background blur"
                                     class="w-full h-full object-cover blur-3xl scale-110 opacity-30">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/20 via-pink-500/20 to-indigo-500/20"></div>
                                <div class="absolute inset-0 backdrop-blur-sm bg-white/10 dark:bg-black/10"></div>
                            </div>

                            <!-- Main Image with Glass Effects -->
                            <div class="relative z-10 h-full flex items-center justify-center p-6">
                                <div class="relative group w-full max-w-sm">
                                    <!-- Glass container -->
                                    <div class="absolute inset-0 bg-white/20 dark:bg-white/10 backdrop-blur-md rounded-2xl border border-white/30 dark:border-white/20 shadow-2xl"></div>
                                    
                                    <img src="{{ $selectedUser->profilePhoto->thumbnail_url }}"
                                         alt="{{ $selectedUser->profile?->first_name }}"
                                         class="relative w-full aspect-[3/4] object-cover rounded-2xl shadow-2xl profile-photo-hover border-4 border-white/30 dark:border-white/20">
                                    
                                    <!-- Photo overlay on hover -->
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 rounded-2xl transition-all duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                        <x-lucide-zoom-in class="w-10 h-10 text-white" />
                                    </div>
                                    
                                    <!-- Glass reflection effect -->
                                    <div class="absolute top-0 left-0 right-0 h-1/3 bg-gradient-to-b from-white/30 to-transparent rounded-t-2xl"></div>
                                </div>
                            </div>
                        @else
                            <!-- Glass Morphism Fallback -->
                            <div class="w-full h-full flex items-center justify-center">
                                <div class="text-center">
                                    <!-- Glass container for fallback -->
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-white/20 dark:bg-white/10 backdrop-blur-md rounded-2xl border border-white/30 dark:border-white/20 shadow-2xl"></div>
                                        <div class="relative w-32 h-32 bg-gradient-to-br from-purple-500/30 via-pink-500/30 to-indigo-500/30 rounded-full flex items-center justify-center mb-4 backdrop-blur-sm border border-white/30 dark:border-white/20">
                                            <span class="text-white font-bold text-6xl">{{ substr($selectedUser?->profile?->first_name ?? 'U', 0, 1) }}</span>
                                        </div>
                                        <!-- Glass reflection effect -->
                                        <div class="absolute top-0 left-0 right-0 h-1/3 bg-gradient-to-b from-white/30 to-transparent rounded-t-2xl"></div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Glass Navigation Buttons -->
                        <button class="absolute left-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-white/20 dark:bg-white/10 backdrop-blur-md rounded-full flex items-center justify-center text-white telegram-button group telegram-focus opacity-0 group-hover:opacity-100 transition-all duration-300 border border-white/30 dark:border-white/20 shadow-lg">
                            <x-lucide-chevron-left class="w-6 h-6 group-hover:scale-110 transition-transform" />
                        </button>
                        <button class="absolute right-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-white/20 dark:bg-white/10 backdrop-blur-md rounded-full flex items-center justify-center text-white telegram-button group telegram-focus opacity-0 group-hover:opacity-100 transition-all duration-300 border border-white/30 dark:border-white/20 shadow-lg">
                            <x-lucide-chevron-right class="w-6 h-6 group-hover:scale-110 transition-transform" />
                        </button>

                        <!-- Glass Pagination Dots -->
                        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex space-x-3">
                            @for($i = 0; $i < 3; $i++)
                                <div class="w-3 h-3 rounded-full pagination-dot {{ $i === 0 ? 'bg-white/80 shadow-lg backdrop-blur-sm' : 'bg-white/40 backdrop-blur-sm' }} cursor-pointer transition-all duration-300 hover:scale-125 border border-white/30"></div>
                            @endfor
                        </div>

                        <!-- Glass Photo Info Overlay -->
                        <div class="absolute bottom-0 left-0 right-0 z-20 bg-gradient-to-t from-black/80 via-black/30 to-transparent backdrop-blur-md p-6 border-t border-white/20">
                            <div class="text-white">
                                <div class="flex items-center justify-between mb-2">
                                    <h2 class="text-2xl font-bold">
                                        {{ $selectedUser?->profile?->first_name ?? 'User' }} {{ $selectedUser?->profile?->last_name ?? '' }}
                                    </h2>
                                    @if($selectedUser?->profile?->age)
                                        <span class="text-white/80 text-lg">{{ $selectedUser->profile->age }}</span>
                                    @endif
                                </div>
                                @if($selectedUser?->profile?->bio)
                                    <p class="text-white/90 text-sm mb-2 line-clamp-2">{{ $selectedUser->profile->bio }}</p>
                                @endif
                                
                                <div class="flex items-center space-x-4">
                                    @if($selectedUser?->last_active_at && $selectedUser->last_active_at->gt(now()->subMinutes(5)))
                                        <span class="inline-flex items-center px-3 py-1 bg-green-500/20 rounded-full">
                                            <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                                            <span class="text-sm font-medium">Online</span>
                                        </span>
                                    @else
                                        <span class="text-white/80 text-sm">
                                            Last seen {{ $selectedUser?->last_active_at?->diffForHumans() ?? 'recently' }}
                                        </span>
                                    @endif
                                    
                                    @if($selectedUser?->profile?->location)
                                        <span class="inline-flex items-center text-white/80 text-sm">
                                            <x-lucide-map-pin class="w-4 h-4 mr-1" />
                                            {{ $selectedUser->profile->location }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Profile Details & Actions -->
                <div class="w-3/5 flex flex-col overflow-y-auto">
                    <!-- Quick Action Buttons -->
                    <div class="p-6 bg-white dark:bg-zinc-900 border-b border-gray-100 dark:border-zinc-800">
                        <div class="grid grid-cols-3 gap-3">
                            <!-- Audio Call -->
                            <button wire:click="startAudioCall"
                                    class="flex flex-col items-center justify-center space-y-2 py-4 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 hover:from-green-100 hover:to-emerald-100 dark:hover:from-green-900/30 dark:hover:to-emerald-900/30 rounded-xl telegram-button group telegram-focus border border-green-200 dark:border-green-800">
                                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <x-lucide-phone class="w-6 h-6 text-white" />
                                </div>
                                <span class="text-sm font-semibold text-green-700 dark:text-green-300">Call</span>
                            </button>

                            <!-- Video Call -->
                            <button wire:click="startVideoCall"
                                    class="flex flex-col items-center justify-center space-y-2 py-4 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 hover:from-blue-100 hover:to-indigo-100 dark:hover:from-blue-900/30 dark:hover:to-indigo-900/30 rounded-xl telegram-button group telegram-focus border border-blue-200 dark:border-blue-800">
                                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <x-lucide-video class="w-6 h-6 text-white" />
                                </div>
                                <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">Video</span>
                            </button>

                            <!-- View Profile -->
                            <button wire:click="viewFullProfile" class="flex flex-col items-center justify-center space-y-2 py-4 bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 hover:from-purple-100 hover:to-pink-100 dark:hover:from-purple-900/30 dark:hover:to-pink-900/30 rounded-xl telegram-button group telegram-focus border border-purple-200 dark:border-purple-800">
                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <x-lucide-user class="w-6 h-6 text-white" />
                                </div>
                                <span class="text-sm font-semibold text-purple-700 dark:text-purple-300">Profile</span>
                            </button>
                        </div>
                    </div>

                    <!-- Profile Details Section -->
                    <div class="p-6 bg-white dark:bg-zinc-900 border-b border-gray-100 dark:border-zinc-800">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Profile Details</h3>
                        <div class="grid grid-cols-2 gap-4">
                            @if($selectedUser?->profile?->age)
                                <div class="bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg">
                                    <div class="text-xs text-gray-500 dark:text-zinc-400 uppercase tracking-wide mb-1">Age</div>
                                    <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $selectedUser->profile->age }}</div>
                                </div>
                            @endif
                            
                            @if($selectedUser?->profile?->location)
                                <div class="bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg">
                                    <div class="text-xs text-gray-500 dark:text-zinc-400 uppercase tracking-wide mb-1">Location</div>
                                    <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $selectedUser->profile->location }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Media & Files Section -->
                    <div class="p-6 bg-white dark:bg-zinc-900 border-b border-gray-100 dark:border-zinc-800">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <x-lucide-image class="w-5 h-5 mr-2 text-purple-600" />
                            Media & Files
                        </h3>

                        <!-- Media Stats -->
                        <div class="grid grid-cols-3 gap-3">
                            <div class="cursor-pointer group hover:bg-gradient-to-br hover:from-purple-50 hover:to-pink-50 dark:hover:from-purple-900/20 dark:hover:to-pink-900/20 p-4 rounded-xl transition-all duration-300 border border-transparent hover:border-purple-200 dark:hover:border-purple-800">
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                        <x-lucide-image class="w-6 h-6 text-white" />
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">15</div>
                                    <div class="text-sm text-gray-500 dark:text-zinc-400">Photos</div>
                                </div>
                            </div>
                            <div class="cursor-pointer group hover:bg-gradient-to-br hover:from-blue-50 hover:to-indigo-50 dark:hover:from-blue-900/20 dark:hover:to-indigo-900/20 p-4 rounded-xl transition-all duration-300 border border-transparent hover:border-blue-200 dark:hover:border-blue-800">
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-full flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                        <x-lucide-video class="w-6 h-6 text-white" />
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">3</div>
                                    <div class="text-sm text-gray-500 dark:text-zinc-400">Videos</div>
                                </div>
                            </div>
                            <div class="cursor-pointer group hover:bg-gradient-to-br hover:from-green-50 hover:to-emerald-50 dark:hover:from-green-900/20 dark:hover:to-emerald-900/20 p-4 rounded-xl transition-all duration-300 border border-transparent hover:border-green-200 dark:hover:border-green-800">
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                                        <x-lucide-file class="w-6 h-6 text-white" />
                                    </div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">7</div>
                                    <div class="text-sm text-gray-500 dark:text-zinc-400">Files</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings & Actions -->
                    <div class="p-6 bg-white dark:bg-zinc-900 space-y-3">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Settings & Actions</h3>
                        
                        <!-- Notifications -->
                        <button class="w-full flex items-center space-x-4 py-4 px-4 hover:bg-gray-50 dark:hover:bg-zinc-800 rounded-xl transition-all duration-300 text-left group border border-transparent hover:border-gray-200 dark:hover:border-zinc-700">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                <x-lucide-bell class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="flex-1">
                                <div class="text-base font-semibold text-gray-900 dark:text-white">Notifications</div>
                                <div class="text-sm text-gray-500 dark:text-zinc-400">On</div>
                            </div>
                            <x-lucide-chevron-right class="w-5 h-5 text-gray-400" />
                        </button>

                        <!-- Report -->
                        <button wire:click="reportUser" class="w-full flex items-center space-x-4 py-4 px-4 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all duration-300 text-left group border border-transparent hover:border-red-200 dark:hover:border-red-800">
                            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                <x-lucide-flag class="w-5 h-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="flex-1">
                                <div class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">Report User</div>
                                <div class="text-sm text-gray-500 dark:text-zinc-400">Report inappropriate behavior</div>
                            </div>
                            <x-lucide-chevron-right class="w-5 h-5 text-gray-400" />
                        </button>

                        <!-- Block -->
                        <button wire:click="blockUser" class="w-full flex items-center space-x-4 py-4 px-4 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all duration-300 text-left group border border-transparent hover:border-red-200 dark:hover:border-red-800">
                            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                <x-lucide-user-x class="w-5 h-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="flex-1">
                                <div class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">Block User</div>
                                <div class="text-sm text-gray-500 dark:text-zinc-400">Block this user permanently</div>
                            </div>
                            <x-lucide-chevron-right class="w-5 h-5 text-gray-400" />
                        </button>

                        <!-- Delete Chat -->
                        <button wire:click="deleteConversation" class="w-full flex items-center space-x-4 py-4 px-4 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all duration-300 text-left group border border-transparent hover:border-red-200 dark:hover:border-red-800">
                            <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform">
                                <x-lucide-trash-2 class="w-5 h-5 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="flex-1">
                                <div class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">Delete Chat</div>
                                <div class="text-sm text-gray-500 dark:text-zinc-400">Delete all messages in this chat</div>
                            </div>
                            <x-lucide-chevron-right class="w-5 h-5 text-gray-400" />
                        </button>
                    </div>
                </div>
            </div>
        @endif
        </div>
    @else
        <!-- No Chat Selected -->
        <div class="flex-1 bg-white dark:bg-zinc-800 flex items-center justify-center h-full overflow-hidden transition-colors duration-300">
            <div class="text-center">
                <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 dark:bg-zinc-700 rounded-full flex items-center justify-center">
                    <x-lucide-message-circle class="w-12 h-12 text-gray-400 dark:text-zinc-500" />
                </div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Your Messages</h2>
                <p class="text-gray-500 dark:text-zinc-400 mb-6">Send private photos and messages to a friend</p>
                <button class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                    Send Message
                </button>
            </div>
        </div>
    @endif

    <!-- Success Message -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
             class="fixed bottom-4 right-4 bg-gray-900 dark:bg-zinc-800 text-white px-6 py-3 rounded-lg shadow-lg z-50 border border-gray-800 dark:border-zinc-700"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100">
            {{ session('message') }}
        </div>
    @endif
    </div>

    <style>
        /* Instagram-like font */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        /* Custom scrollbar for messages list */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .overflow-y-auto::-webkit-scrollbar-track {
            background: transparent;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #D1D5DB;
            border-radius: 3px;
        }

        .dark .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #52525b;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #9CA3AF;
        }

        .dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #71717a;
        }

        /* Enhanced Telegram-style enhancements */
        .telegram-blur {
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
        }

        .telegram-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
        }

        .telegram-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%, rgba(255,255,255,0.1) 100%);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.7; }
        }

        .telegram-shadow {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
        }

        .dark .telegram-shadow {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }

        /* Enhanced blur effect for navigation buttons */
        .nav-button-blur {
            backdrop-filter: blur(15px) saturate(180%);
            -webkit-backdrop-filter: blur(15px) saturate(180%);
            background-color: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .nav-button-blur:hover {
            background-color: rgba(0, 0, 0, 0.5);
            border-color: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        /* Smooth page transitions */
        .page-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Enhanced dot animation */
        .pagination-dot {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        .pagination-dot:hover {
            transform: scale(1.3);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }

        /* Glass morphism effects */
        .glass-effect {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .dark .glass-effect {
            background: rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        /* Enhanced button animations */
        .telegram-button {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .telegram-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .telegram-button:hover::before {
            left: 100%;
        }

        .telegram-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .telegram-button:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Enhanced focus states */
        .telegram-focus:focus {
            outline: 2px solid rgba(59, 130, 246, 0.6);
            outline-offset: 2px;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        /* Smooth entrance animation */
        @keyframes slideInFromRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .slide-in-right {
            animation: slideInFromRight 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Enhanced profile photo animation */
        .profile-photo-hover {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .profile-photo-hover:hover {
            transform: scale(1.08) rotate(1deg);
            filter: brightness(1.15) contrast(1.1);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        /* Line clamp utility */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Enhanced gradient backgrounds */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Floating animation for online indicator */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
        }

        .animate-float {
            animation: float 2s ease-in-out infinite;
        }

        /* Enhanced hover effects for media stats */
        .media-stat-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .media-stat-hover:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        /* Pulse animation for online status */
        @keyframes pulse-glow {
            0%, 100% { 
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            }
            50% { 
                box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
            }
        }

        .pulse-glow {
            animation: pulse-glow 2s infinite;
        }
    </style>

    <!-- Mobile Bottom Navigation -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-zinc-800 border-t border-gray-200 dark:border-zinc-700 z-50 transition-colors duration-300">
        <div class="flex justify-around items-center py-2">
            <!-- Home -->
            <a href="{{ route('dashboard') }}" class="p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700 transition-all">
                <x-lucide-home class="w-6 h-6 text-gray-700 dark:text-zinc-400" />
            </a>

            <!-- Search -->
            <a href="#" class="p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700 transition-all">
                <x-lucide-search class="w-6 h-6 text-gray-700 dark:text-zinc-400" />
            </a>

            <!-- Messages (Active) -->
            <a href="{{ route('messages') }}" class="p-3 rounded-lg bg-purple-50 dark:bg-purple-900/20 transition-all relative">
                <x-lucide-message-circle class="w-6 h-6 text-purple-600" />
                @if(collect($conversations)->where('unread_count', '>', 0)->count() > 0)
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold">
                        {{ collect($conversations)->where('unread_count', '>', 0)->count() }}
                    </span>
                @endif
            </a>

            <!-- Likes -->
            <a href="#" class="p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700 transition-all">
                <x-lucide-heart class="w-6 h-6 text-gray-700 dark:text-zinc-400" />
            </a>

            <!-- Profile -->
            <a href="{{ route('user.profile.show', auth()->id()) }}" class="p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700 transition-all">
                @if(auth()->user()->photos?->first())
                    <img src="{{ auth()->user()->photos->first()->url }}"
                         alt="Profile"
                         class="w-6 h-6 rounded-full object-cover ring-2 ring-gray-200 dark:ring-zinc-600">
                @else
                    <div class="w-6 h-6 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-xs">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                @endif
            </a>
        </div>
    </div>

    <!-- VideoSDK Script -->
    <script src="https://sdk.videosdk.live/js-sdk/0.1.6/videosdk.js"></script>
    
    <!-- Test Mode Script (for development without VideoSDK) -->
    <script>
        // Test mode configuration
        const TEST_MODE = {{ config('app.debug') ? 'true' : 'false' }};
        const VIDEOSDK_CONFIGURED = {{ !empty(config('services.videosdk.api_key')) ? 'true' : 'false' }};
        
        if (TEST_MODE && !VIDEOSDK_CONFIGURED) {
            console.warn('⚠️ VideoSDK not configured. Running in test mode.');
            
            // Mock VideoSDK for testing
            window.VideoSDK = {
                config: function(token) {
                    console.log('🎭 Mock VideoSDK config called with token:', token ? '***' : 'null');
                },
                initMeeting: function(options) {
                    console.log('🎭 Mock VideoSDK initMeeting called with options:', options);
                    return {
                        join: function() {
                            console.log('🎭 Mock meeting joined');
                            setTimeout(() => {
                                if (window.mockMeeting) {
                                    window.mockMeeting.emit('meeting-joined');
                                }
                            }, 1000);
                        },
                        leave: function() {
                            console.log('🎭 Mock meeting left');
                        },
                        muteMic: function() {
                            console.log('🎭 Mock mic muted');
                        },
                        unmuteMic: function() {
                            console.log('🎭 Mock mic unmuted');
                        },
                        disableWebcam: function() {
                            console.log('🎭 Mock webcam disabled');
                        },
                        enableWebcam: function() {
                            console.log('🎭 Mock webcam enabled');
                        },
                        localParticipant: {
                            on: function(event, callback) {
                                console.log('🎭 Mock local participant event listener:', event);
                                if (event === 'stream-enabled') {
                                    // Simulate stream after a delay
                                    setTimeout(() => {
                                        callback({
                                            kind: 'video',
                                            track: {
                                                // Mock track
                                            }
                                        });
                                    }, 2000);
                                }
                            }
                        },
                        on: function(event, callback) {
                            console.log('🎭 Mock meeting event listener:', event);
                            if (!window.mockMeeting) {
                                window.mockMeeting = { callbacks: {} };
                            }
                            window.mockMeeting.callbacks[event] = callback;
                        },
                        emit: function(event) {
                            if (window.mockMeeting && window.mockMeeting.callbacks[event]) {
                                window.mockMeeting.callbacks[event]();
                            }
                        }
                    };
                }
            };
        }
    </script>

    <script>
        // VideoSDK Configuration
        let meeting = null;
        let isVideoCallActive = false;
        let callStartTime = null;
        let callDurationInterval = null;

        // Ultra-simple typing management - one timer approach
        let typingState = {
            isTyping: false,
            startTimer: null,
            stopTimer: null
        };

        // Check if there's already a selected chat on page load and trigger subscription
        document.addEventListener('livewire:initialized', () => {
            @if($selectedChat)
                // Dispatch event to join chat channel for existing selected chat
                console.log('Page loaded with selected chat: {{ $selectedChat->id }}');
                setTimeout(() => {
                    Livewire.dispatch('chatSelected', { chatId: {{ $selectedChat->id }} });
                }, 1000);
            @endif

            // Handle typing timer events - improved version
            Livewire.on('setTypingTimer', (data) => {
                // Handle both array and object formats
                const eventData = Array.isArray(data) ? data[0] : data;
                const chatId = eventData?.chatId;
                console.log('Setting typing timer for chat:', chatId, 'data:', eventData);

                // Clear existing timer
                if (typingTimer) {
                    clearTimeout(typingTimer);
                    typingTimer = null;
                }

                // Set new timer to stop typing after 3 seconds
                typingTimer = setTimeout(() => {
                    console.log('Typing timer expired, stopping typing');
                    if (isTyping) {
                        stopTyping();
                    }
                    typingTimer = null;
                }, 3000);
            });

            Livewire.on('clearTypingTimer', () => {
                console.log('Clearing typing timer');
                cleanupTypingTimers();
            });

            Livewire.on('removeTestTyping', () => {
                setTimeout(() => {
                    console.log('Removing test typing indicator');
                    Livewire.dispatch('userTyping', [{
                        user_id: 999,
                        user_name: 'Test User',
                        is_typing: false,
                        chat_id: {{ $selectedChat?->id ?? 0 }}
                    }]);
                }, 3000);
            });

            // Debug WebSocket connection
            window.checkWebSocket = function() {
                if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
                    const pusher = window.Echo.connector.pusher;
                    console.log('WebSocket State:', pusher.connection.state);
                    console.log('Socket ID:', pusher.connection.socket_id);
                    console.log('Channels:', Object.keys(pusher.channels.channels));
                } else {
                    console.error('Echo not initialized');
                }
            };

            console.log('Check WebSocket status by running: checkWebSocket()');

            // Function to set up typing detection - improved version
            function setupTypingDetection() {
                const messageInput = document.getElementById('messageInput');
                if (messageInput) {
                    console.log('Setting up typing detection for message input');

                    // Remove existing listeners to prevent duplicates
                    messageInput.removeEventListener('input', handleInput);
                    messageInput.removeEventListener('blur', handleBlur);
                    messageInput.removeEventListener('keydown', handleKeyDown);

                    // Add new listeners with improved event handling
                    messageInput.addEventListener('input', handleInput);
                    messageInput.addEventListener('blur', handleBlur);
                    messageInput.addEventListener('keydown', handleKeyDown);
                } else {
                    console.log('Message input element not found yet, will retry when chat is selected');
                }
            }

            // Ultra-simple input handler - single timer approach
            function handleInput() {
                console.log('Input detected, current state:', typingState.isTyping);

                // Clear any existing timers
                clearTimers();

                // If not typing yet, set a delayed start
                if (!typingState.isTyping) {
                    typingState.startTimer = setTimeout(() => {
                        startTyping();
                    }, 1000); // 1 second delay
                }

                // Always set stop timer
                typingState.stopTimer = setTimeout(() => {
                    stopTyping();
                }, 3000); // 3 seconds to stop
            }

            // Keydown handler for immediate stop on Enter
            function handleKeyDown(event) {
                if (event.key === 'Enter') {
                    console.log('Enter pressed - stopping typing immediately');
                    clearTimers();
                    if (typingState.isTyping) {
                        stopTyping();
                    }
                }
            }

            // Clear all timers
            function clearTimers() {
                if (typingState.startTimer) {
                    clearTimeout(typingState.startTimer);
                    typingState.startTimer = null;
                }
                if (typingState.stopTimer) {
                    clearTimeout(typingState.stopTimer);
                    typingState.stopTimer = null;
                }
            }

            // Start typing - only called once
            function startTyping() {
                if (typingState.isTyping) return;

                typingState.isTyping = true;
                console.log('🚀 Starting typing indicator');

                try {
                    const component = document.querySelector('[wire\\:id]');
                    if (component) {
                        const livewireComponent = window.Livewire.find(component.getAttribute('wire:id'));
                        if (livewireComponent) {
                            livewireComponent.call('handleStartTyping');
                            return;
                        }
                    }
                    Livewire.dispatch('handleStartTyping');
                } catch (error) {
                    console.error('Failed to start typing:', error);
                }
            }

            // Stop typing
            function stopTyping() {
                if (!typingState.isTyping) return;

                typingState.isTyping = false;
                console.log('🛑 Stopping typing indicator');

                try {
                    const component = document.querySelector('[wire\\:id]');
                    if (component) {
                        const livewireComponent = window.Livewire.find(component.getAttribute('wire:id'));
                        if (livewireComponent) {
                            livewireComponent.call('stopTyping');
                            return;
                        }
                    }
                    Livewire.dispatch('stopTyping');
                } catch (error) {
                    console.error('Failed to stop typing:', error);
                }
            }

            // Blur handler function
            function handleBlur() {
                console.log('Input blurred, stopping typing');
                clearTimers();
                if (typingState.isTyping) {
                    stopTyping();
                }
            }

            // Cleanup function to prevent memory leaks
            function cleanupTypingTimers() {
                clearTimers();
                typingState.isTyping = false;
                console.log('Cleaned up all typing timers');
            }

            // Set up typing detection initially
            setupTypingDetection();

            // Also set up typing detection when Livewire updates the DOM
            Livewire.hook('morph.updated', ({ el, component }) => {
                // Check if the message input was added/updated
                if (document.getElementById('messageInput')) {
                    console.log('Message input detected after DOM update, setting up typing detection');
                    setupTypingDetection();
                }
            });

            // Cleanup on page unload
            window.addEventListener('beforeunload', cleanupTypingTimers);

            // Listen for debug info
            Livewire.on('debugInfo', (data) => {
                console.log('🐛 Debug Info:', data);
            });

            // Listen for Livewire method calls
            Livewire.on('handleStartTyping', () => {
                console.log('🚀 handleStartTyping event dispatched to Livewire');
            });

            Livewire.on('stopTyping', () => {
                console.log('🛑 stopTyping event dispatched to Livewire');
            });

            // Listen for confirmation that startTyping method was called
            Livewire.on('startTypingCalled', (data) => {
                console.log('✅ startTyping method was called on backend', data);
            });

            // Video Call Event Listeners
            Livewire.on('startVideoCall', (data) => {
                console.log('🎥 Starting video call', data);
                startVideoCall(data);
            });

            Livewire.on('startAudioCall', (data) => {
                console.log('📞 Starting audio call', data);
                startAudioCall(data);
            });

            Livewire.on('endVideoCall', () => {
                console.log('📞 Ending video call');
                endVideoCall();
            });
        });

        // Video Call Interface Functions
        function videoCallInterface() {
            return {
                localMicEnabled: true,
                localVideoEnabled: true,
                remoteMicEnabled: true,
                remoteVideoEnabled: true,
                callDuration: '00:00',
                callStatus: 'Connecting...',

                initVideoCall() {
                    console.log('Initializing video call interface');
                    this.startCallDuration();
                },

                toggleMic() {
                    if (meeting) {
                        if (this.localMicEnabled) {
                            meeting.muteMic();
                        } else {
                            meeting.unmuteMic();
                        }
                        this.localMicEnabled = !this.localMicEnabled;
                    }
                },

                toggleVideo() {
                    if (meeting) {
                        if (this.localVideoEnabled) {
                            meeting.disableWebcam();
                        } else {
                            meeting.enableWebcam();
                        }
                        this.localVideoEnabled = !this.localVideoEnabled;
                    }
                },

                endCall() {
                    if (meeting) {
                        meeting.leave();
                    }
                    endVideoCall();
                },

                startCallDuration() {
                    callStartTime = new Date();
                    callDurationInterval = setInterval(() => {
                        if (callStartTime) {
                            const now = new Date();
                            const diff = Math.floor((now - callStartTime) / 1000);
                            const minutes = Math.floor(diff / 60);
                            const seconds = diff % 60;
                            this.callDuration = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                        }
                    }, 1000);
                }
            }
        }

        // Video Call Functions
        async function startVideoCall(callData) {
            try {
                console.log('Starting video call with data:', callData);
                
                let token = null;
                
                // Check if we're in test mode
                if (TEST_MODE && !VIDEOSDK_CONFIGURED) {
                    console.log('🎭 Using mock token for test mode');
                    token = 'mock_token_for_testing';
                } else {
                    // Get token from backend
                    const response = await fetch('/api/v1/video-call/token', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            meeting_id: callData.meetingId || 'new'
                        })
                    });

                    // Check if response is HTML (error page)
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        const htmlText = await response.text();
                        console.error('API returned HTML instead of JSON:', htmlText.substring(0, 200));
                        throw new Error('Server error: API endpoint not responding correctly');
                    }

                    const tokenData = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(tokenData.message || 'Failed to get token');
                    }
                    
                    if (!tokenData.data || !tokenData.data.token) {
                        throw new Error('Invalid token response from server');
                    }
                    
                    token = tokenData.data.token;
                }

                // Configure VideoSDK
                window.VideoSDK.config(token);

                // Initialize meeting
                meeting = window.VideoSDK.initMeeting({
                    meetingId: callData.meetingId,
                    name: callData.participantName || 'User',
                    micEnabled: true,
                    webcamEnabled: true,
                });

                // Set up event listeners
                setupMeetingEvents();

                // Join the meeting
                meeting.join();

                // Update UI state
                isVideoCallActive = true;
                document.querySelector('[wire\\:id]').__livewire.$set('isVideoCallActive', true);

            } catch (error) {
                console.error('Failed to start video call:', error);
                
                // Show a more helpful error message
                if (error.message.includes('Configuration missing')) {
                    alert('VideoSDK is not configured. Please contact your administrator to set up video calling.');
                } else if (error.message.includes('Server error')) {
                    alert('Server error occurred. Please try again or contact support.');
                } else {
                    alert(`Failed to start video call: ${error.message}`);
                }
            }
        }

        async function startAudioCall(callData) {
            try {
                console.log('Starting audio call with data:', callData);
                
                let token = null;
                
                // Check if we're in test mode
                if (TEST_MODE && !VIDEOSDK_CONFIGURED) {
                    console.log('🎭 Using mock token for test mode');
                    token = 'mock_token_for_testing';
                } else {
                    // Get token from backend
                    const response = await fetch('/api/v1/video-call/token', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            meeting_id: callData.meetingId || 'new'
                        })
                    });

                    // Check if response is HTML (error page)
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        const htmlText = await response.text();
                        console.error('API returned HTML instead of JSON:', htmlText.substring(0, 200));
                        throw new Error('Server error: API endpoint not responding correctly');
                    }

                    const tokenData = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(tokenData.message || 'Failed to get token');
                    }
                    
                    if (!tokenData.data || !tokenData.data.token) {
                        throw new Error('Invalid token response from server');
                    }
                    
                    token = tokenData.data.token;
                }

                // Configure VideoSDK
                window.VideoSDK.config(token);

                // Initialize meeting (audio only)
                meeting = window.VideoSDK.initMeeting({
                    meetingId: callData.meetingId,
                    name: callData.participantName || 'User',
                    micEnabled: true,
                    webcamEnabled: false, // Audio only
                });

                // Set up event listeners
                setupMeetingEvents();

                // Join the meeting
                meeting.join();

                // Update UI state
                isVideoCallActive = true;
                document.querySelector('[wire\\:id]').__livewire.$set('isVideoCallActive', true);

            } catch (error) {
                console.error('Failed to start audio call:', error);
                
                // Show a more helpful error message
                if (error.message.includes('Configuration missing')) {
                    alert('VideoSDK is not configured. Please contact your administrator to set up audio calling.');
                } else if (error.message.includes('Server error')) {
                    alert('Server error occurred. Please try again or contact support.');
                } else {
                    alert(`Failed to start audio call: ${error.message}`);
                }
            }
        }

        function setupMeetingEvents() {
            if (!meeting) return;

            // Meeting joined
            meeting.on('meeting-joined', () => {
                console.log('✅ Meeting joined successfully');
                callStartTime = new Date();
            });

            // Meeting left
            meeting.on('meeting-left', () => {
                console.log('📞 Meeting left');
                endVideoCall();
            });

            // Local participant stream enabled
            meeting.localParticipant.on('stream-enabled', (stream) => {
                console.log('🎥 Local stream enabled:', stream.kind);
                if (stream.kind === 'video') {
                    const localVideo = document.getElementById('local-video');
                    if (localVideo) {
                        const mediaStream = new MediaStream();
                        mediaStream.addTrack(stream.track);
                        localVideo.srcObject = mediaStream;
                        localVideo.play().catch(console.error);
                    }
                }
            });

            // Participant joined
            meeting.on('participant-joined', (participant) => {
                console.log('👤 Participant joined:', participant.displayName);
                
                // Set up participant events
                participant.on('stream-enabled', (stream) => {
                    console.log('🎥 Remote stream enabled:', stream.kind);
                    if (stream.kind === 'video') {
                        const remoteVideo = document.getElementById('remote-video');
                        if (remoteVideo) {
                            const mediaStream = new MediaStream();
                            mediaStream.addTrack(stream.track);
                            remoteVideo.srcObject = mediaStream;
                            remoteVideo.play().catch(console.error);
                        }
                    } else if (stream.kind === 'audio') {
                        const remoteAudio = document.getElementById('remote-audio');
                        if (remoteAudio) {
                            const mediaStream = new MediaStream();
                            mediaStream.addTrack(stream.track);
                            remoteAudio.srcObject = mediaStream;
                            remoteAudio.play().catch(console.error);
                        }
                    }
                });

                participant.on('stream-disabled', (stream) => {
                    console.log('🔇 Remote stream disabled:', stream.kind);
                });
            });

            // Participant left
            meeting.on('participant-left', (participant) => {
                console.log('👤 Participant left:', participant.displayName);
            });
        }

        function endVideoCall() {
            console.log('📞 Ending video call');
            
            if (meeting) {
                meeting.leave();
                meeting = null;
            }

            // Clear video elements
            const localVideo = document.getElementById('local-video');
            const remoteVideo = document.getElementById('remote-video');
            const remoteAudio = document.getElementById('remote-audio');

            if (localVideo) {
                localVideo.srcObject = null;
            }
            if (remoteVideo) {
                remoteVideo.srcObject = null;
            }
            if (remoteAudio) {
                remoteAudio.srcObject = null;
            }

            // Clear duration interval
            if (callDurationInterval) {
                clearInterval(callDurationInterval);
                callDurationInterval = null;
            }

            // Update UI state
            isVideoCallActive = false;
            callStartTime = null;
            
            // Update Livewire component
            const livewireComponent = document.querySelector('[wire\\:id]');
            if (livewireComponent && livewireComponent.__livewire) {
                livewireComponent.__livewire.$set('isVideoCallActive', false);
            }
        }
    </script>

    <script>
        // Alpine.js component for optimized messages handling
        function messagesComponent() {
            return {
                conversations: @json($conversations),
                selectedConversation: @json($selectedConversation),
                typingUsers: {},
                unreadCounts: {},
                lastMessageTimestamps: {},

                init() {
                    // Initialize WebSocket listeners
                    this.setupWebSocketListeners();

                    // Initialize conversation tracking
                    this.initializeConversationTracking();

                    // Setup periodic refresh for online status
                    this.setupOnlineStatusRefresh();
                },

                setupWebSocketListeners() {
                    // Listen for Livewire events
                    Livewire.on('conversationUpdated', (data) => {
                        this.handleConversationUpdate(data[0]);
                    });

                    Livewire.on('messageRead', (data) => {
                        this.handleMessageRead(data[0]);
                    });

                    Livewire.on('userTyping', (data) => {
                        this.handleTyping(data[0]);
                    });
                },

                initializeConversationTracking() {
                    // Track last message timestamps for efficient updates
                    this.conversations.forEach(conv => {
                        this.lastMessageTimestamps[conv.id] = conv.last_message?.sent_at;
                        this.unreadCounts[conv.id] = conv.unread_count || 0;
                    });
                },

                setupOnlineStatusRefresh() {
                    // Refresh online status every 30 seconds
                    setInterval(() => {
                        this.refreshOnlineStatus();
                    }, 30000);
                },

                handleConversationUpdate(data) {
                    if (!data || !data.data) return;

                    const updateData = data.data;
                    const chatId = updateData.chat_id;

                    // Find and update conversation efficiently
                    const convIndex = this.conversations.findIndex(c => c.chat_id === chatId);
                    if (convIndex !== -1) {
                        const conv = this.conversations[convIndex];

                        // Update last message
                        if (updateData.message) {
                            conv.last_message = {
                                ...conv.last_message,
                                ...updateData.message,
                                sent_at_human: 'Just now',
                                time_formatted: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
                            };
                        }

                        // Update unread count
                        if (updateData.unread_count !== undefined) {
                            conv.unread_count = updateData.unread_count;
                            this.unreadCounts[conv.id] = updateData.unread_count;
                        }

                        // Move conversation to top if new message
                        if (updateData.message) {
                            this.conversations.splice(convIndex, 1);
                            this.conversations.unshift(conv);
                        }
                    }
                },

                handleMessageRead(data) {
                    if (!data || !data.chat_id) return;

                    const conv = this.conversations.find(c => c.chat_id === data.chat_id);
                    if (conv) {
                        conv.unread_count = 0;
                        this.unreadCounts[conv.id] = 0;
                    }
                },

                handleTyping(data) {
                    if (!data) return;

                    const userId = data.user_id;
                    const isTyping = data.is_typing;

                    if (isTyping) {
                        this.typingUsers[userId] = true;

                        // Clear typing after 3 seconds
                        setTimeout(() => {
                            delete this.typingUsers[userId];
                        }, 3000);
                    } else {
                        delete this.typingUsers[userId];
                    }

                    // Update conversation display
                    const conv = this.conversations.find(c => c.id === userId);
                    if (conv) {
                        conv.is_typing = isTyping;
                    }
                },

                refreshOnlineStatus() {
                    // Update online status based on last_active times
                    this.conversations.forEach(conv => {
                        if (conv.last_active_raw) {
                            const lastActive = new Date(conv.last_active_raw);
                            const now = new Date();
                            const diffMinutes = (now - lastActive) / (1000 * 60);

                            conv.is_online = diffMinutes <= 5;
                        }
                    });
                },

                isUserTyping(userId) {
                    return this.typingUsers[userId] || false;
                }
            }
        }
    </script>
        </div>
    </div>
</div>
