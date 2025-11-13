<div>
    <!-- Instagram Style Chat Page -->
    <div class="h-screen flex bg-white">
    
    <!-- Left Sidebar - Navigation Icons Only -->
    <div class="w-16 lg:w-20 bg-white border-r border-gray-200 flex flex-col">
        <!-- Logo -->
        <div class="p-4 lg:p-6">
            <a href="{{ route('dashboard') }}" class="block">
                <div class="w-8 h-8 lg:w-10 lg:h-10 bg-gradient-to-r from-pink-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold text-lg lg:text-xl">Y</span>
                </div>
            </a>
        </div>
        
        <!-- Navigation Icons -->
        <nav class="flex-1 flex flex-col items-center space-y-4 lg:space-y-6 py-4">
            <!-- Back to Messages -->
            <a href="{{ route('messages') }}" 
               class="p-2 lg:p-3 rounded-lg hover:bg-gray-100 transition-all group">
                <svg class="w-6 h-6 lg:w-7 lg:h-7 text-gray-700 group-hover:text-purple-600 transition-colors" 
                     fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                </svg>
            </a>
        </nav>
    </div>
    
    <!-- Chat Area -->
    <div class="flex-1 flex flex-col">
        <!-- Chat Header -->
        <div class="bg-white border-b border-gray-200 px-4 lg:px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <!-- Avatar -->
                    @if($otherUser->profilePhoto?->thumbnail_url)
                        <img src="{{ $otherUser->profilePhoto->thumbnail_url }}"
                             alt="{{ $otherUser->profile?->first_name }}"
                             class="w-10 h-10 rounded-full object-cover">
                    @else
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold">{{ substr($otherUser->profile?->first_name ?? 'U', 0, 1) }}</span>
                        </div>
                    @endif
                    
                    <!-- User Info -->
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ $otherUser->profile?->first_name ?? 'User' }} {{ $otherUser->profile?->last_name ?? '' }}
                        </h2>
                        <p class="text-sm text-gray-500">
                            @if($otherUser->last_active_at && $otherUser->last_active_at->gt(now()->subMinutes(5)))
                                Active now
                            @else
                                Active {{ $otherUser->last_active_at?->diffForHumans() ?? 'recently' }}
                            @endif
                        </p>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex items-center space-x-2">
                    <button class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </button>
                    <button class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </button>
                    <button class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Messages Area -->
        <div class="flex-1 overflow-y-auto px-4 lg:px-6 py-4 space-y-4" id="messagesContainer">
            @if(empty($messages))
                <div class="text-center py-8">
                    <p class="text-gray-500">No messages yet. Start the conversation!</p>
                </div>
            @else
                @foreach($messages as $message)
                    <div class="flex {{ $message['is_mine'] ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs lg:max-w-md">
                            <div class="{{ $message['is_mine'] ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-900' }} px-4 py-2 rounded-2xl">
                                <p>{{ $message['content'] }}</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 {{ $message['is_mine'] ? 'text-right' : 'text-left' }}">
                                {{ $message['time'] }}
                            </p>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        
        <!-- Message Input -->
        <div class="bg-white border-t border-gray-200 px-4 lg:px-6 py-4">
            <form wire:submit.prevent="sendMessage" class="flex items-center space-x-2">
                <button type="button" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </button>
                
                <input type="text"
                       wire:model="newMessage"
                       placeholder="Message..."
                       class="flex-1 px-4 py-2 bg-gray-50 text-gray-900 placeholder-gray-500 rounded-full border border-gray-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                
                @if(!empty($newMessage))
                    <button type="submit" class="text-purple-600 font-semibold hover:text-purple-700 transition-colors px-2">
                        Send
                    </button>
                @else
                    <button type="button" class="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </button>
                @endif
            </form>
        </div>
    </div>
    
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Scroll to bottom when new message is sent
            Livewire.on('messageSent', () => {
                const container = document.getElementById('messagesContainer');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
            
            // Initial scroll to bottom
            const container = document.getElementById('messagesContainer');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        });
    </script>
</div>