<div>
    <!-- Header with back button and chat info -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.chats') }}" 
                   class="flex items-center px-4 py-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Chats
                </a>
                <h2 class="text-2xl font-bold text-gray-900">
                    {{ $chat->name ?: 'Private Chat' }} #{{ $chat->id }}
                </h2>
            </div>
            
            <button wire:click="deleteChat" 
                    wire:confirm="Are you sure you want to delete this entire chat? This action cannot be undone."
                    class="flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete Chat
            </button>
        </div>

        <!-- Chat Info Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Basic Info -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Chat Information</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Type:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $chat->type === 'private' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ ucfirst($chat->type) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Status:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $chat->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $chat->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Created:</span>
                            <span class="text-sm text-gray-900">{{ $chat->created_at->format('M j, Y g:i A') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Last Activity:</span>
                            <span class="text-sm text-gray-900">
                                {{ $chat->last_activity_at ? $chat->last_activity_at->diffForHumans() : 'Never' }}
                            </span>
                        </div>
                        @if($chat->description)
                            <div>
                                <span class="text-sm font-medium text-gray-500">Description:</span>
                                <p class="text-sm text-gray-900 mt-1">{{ $chat->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Participants -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Participants ({{ $chat->users->count() }})</h3>
                    <div class="space-y-3">
                        @foreach($chat->users as $user)
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    @if(method_exists($user, 'getProfilePhotoUrl') && $user->getProfilePhotoUrl())
                                        <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-200" 
                                             src="{{ $user->getProfilePhotoUrl() }}" 
                                             alt="{{ $user->profile?->first_name ?? 'User' }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-pink-500 to-purple-600 flex items-center justify-center">
                                            <span class="text-white font-medium">
                                                {{ substr($user->profile?->first_name ?? 'U', 0, 1) }}{{ substr($user->profile?->last_name ?? 'U', 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $user->profile?->first_name . ' ' . $user->profile?->last_name ?: 'No Name' }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                                    <p class="text-xs text-gray-400">ID: {{ $user->id }}</p>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="{{ route('admin.user.profile', $user->id) }}" 
                                       class="text-pink-600 hover:text-pink-800 text-sm font-medium">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Messages -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 13V5a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2zM5 7a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm1 3a1 1 0 100 2h3a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($totalMessages) }}</div>
                            <div class="text-sm text-gray-500">Total Messages</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Messages -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9.5 9.293 10.793a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($messagesToday) }}</div>
                            <div class="text-sm text-gray-500">Today's Messages</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- This Week -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($messagesThisWeek) }}</div>
                            <div class="text-sm text-gray-500">This Week</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Types -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-indigo-100 rounded-md flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm text-gray-500">Message Types</div>
                        </div>
                    </div>
                    <div class="space-y-1">
                        @foreach($messageTypes as $type)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">{{ ucfirst($type->message_type) }}:</span>
                                <span class="font-medium text-gray-900">{{ $type->count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 max-w-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search Messages</label>
                    <div class="relative">
                        <input type="text" 
                               wire:model.live.debounce.300ms="messageFilter"
                               placeholder="Search by content, type, or sender name..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Messages per page</label>
                        <select wire:model.live="perPage" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Messages</h3>
            <p class="text-sm text-gray-500">Showing {{ $messages->total() }} messages (most recent first)</p>
        </div>

        <div class="divide-y divide-gray-200">
            @forelse($messages as $message)
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start space-x-4">
                        <!-- Sender Avatar -->
                        <div class="flex-shrink-0">
                            @if(method_exists($message->sender, 'getProfilePhotoUrl') && $message->sender->getProfilePhotoUrl())
                                <img class="h-10 w-10 rounded-full object-cover border-2 border-gray-200" 
                                     src="{{ $message->sender->getProfilePhotoUrl() }}" 
                                     alt="{{ $message->sender->profile?->first_name ?? 'User' }}">
                            @else
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-green-600 flex items-center justify-center">
                                    <span class="text-white font-medium">
                                        {{ substr($message->sender->profile?->first_name ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Message Content -->
                        <div class="flex-1 min-w-0">
                            <!-- Message Header -->
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-2">
                                    <h4 class="text-sm font-medium text-gray-900">
                                        {{ $message->sender->profile?->first_name . ' ' . $message->sender->profile?->last_name ?: $message->sender->email }}
                                    </h4>
                                    <span class="text-xs text-gray-500">
                                        {{ $message->sent_at->format('M j, Y g:i A') }}
                                    </span>
                                    @if($message->message_type !== 'text')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                            {{ $message->message_type === 'image' ? 'bg-green-100 text-green-800' : 
                                               ($message->message_type === 'call' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($message->message_type) }}
                                        </span>
                                    @endif
                                    @if($message->is_edited)
                                        <span class="text-xs text-gray-400">(edited)</span>
                                    @endif
                                </div>
                                
                                <button wire:click="deleteMessage({{ $message->id }})" 
                                        wire:confirm="Are you sure you want to delete this message?"
                                        class="text-red-600 hover:text-red-800 p-1 rounded transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Message Body -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                @if($message->message_type === 'text')
                                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $message->content ?: 'No content' }}</p>
                                @elseif($message->message_type === 'image')
                                    <div class="space-y-2">
                                        <p class="text-sm text-gray-700 italic">Image message</p>
                                        @if($message->media_url)
                                            <img src="{{ $message->media_url }}" class="max-w-xs rounded-lg shadow-sm" alt="Message image">
                                        @endif
                                        @if($message->content)
                                            <p class="text-sm text-gray-600">{{ $message->content }}</p>
                                        @endif
                                    </div>
                                @elseif($message->message_type === 'call')
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Call message</p>
                                            <p class="text-sm text-gray-600">
                                                Duration: {{ $message->getFormattedCallDuration() ?: 'Unknown' }}
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-700 italic">{{ ucfirst($message->message_type) }} message</p>
                                    @if($message->content)
                                        <p class="text-sm text-gray-600 mt-1">{{ $message->content }}</p>
                                    @endif
                                @endif
                            </div>

                            <!-- Message ID and Status -->
                            <div class="flex items-center justify-between mt-2 text-xs text-gray-400">
                                <span>Message ID: {{ $message->id }}</span>
                                <span>Status: {{ ucfirst($message->status ?? 'sent') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <div class="text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="text-lg font-medium text-gray-900 mb-2">No messages found</p>
                        <p class="text-gray-500">This chat doesn't have any messages yet or they don't match your search criteria.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($messages->hasPages())
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $messages->links() }}
            </div>
        @endif
    </div>

    <!-- Toast Notifications -->
    <script>
        window.addEventListener('message-deleted', event => {
            alert(event.detail[0].message);
        });
    </script>
</div>