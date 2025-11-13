<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use App\Models\Chat;
use App\Models\MatchModel;
use App\Models\MessageRead;
use App\Services\VideoSDKService;
use App\Events\CallInitiatedEvent;
use Carbon\Carbon;
use App\Events\MessageSent;
use App\Events\ConversationUpdated;
use App\Events\UserTyping;

class MessagesPage extends Component
{
    public $conversations = [];
    public $searchTerm = '';
    public $activeFilter = 'all'; // all, unread, online
    public $selectedConversation = null;
    public $selectedUser = null;
    public $selectedChat = null;
    public $messages = [];
    public $newMessage = '';
    public $typingUsers = [];
    public $isCurrentlyTyping = false;
    public $lastTypingBroadcast = null;
    public $lastTypingEvent = null;
    public $showInfoPanel = false;
    public $isVideoCallActive = false;

    protected $listeners = [
        'refreshConversations' => 'loadConversations',
        'newMessageReceived' => 'handleNewMessage',
        'userTyping' => 'handleUserTyping',
        'conversationUpdated' => 'handleConversationUpdate',
        'messageRead' => 'handleMessageRead'
    ];

    // Explicitly define callable methods for JavaScript
    protected $rules = [];
    
    // Make methods explicitly callable
    public function getPublicPropertyTypes(): array
    {
        return [];
    }

    public function mount()
    {
        $this->loadConversations();
    }

    public function loadConversations()
    {
        $user = Auth::user();
        
        // Get all matches with their last messages
        $matches = MatchModel::with([
            'matchedUser:id,email,last_active_at',
            'matchedUser.profile:user_id,first_name,last_name,bio',
            'matchedUser.profilePhoto:id,user_id,thumbnail_url'
        ])
        ->where('user_id', $user->id)
        ->whereHas('matchedUser')
        ->get();

        $conversations = $matches->map(function($match) use ($user) {
            $matchedUser = $match->matchedUser;
            
            // Find or create chat between users
            $chat = Chat::whereHas('users', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->whereHas('users', function($query) use ($matchedUser) {
                $query->where('user_id', $matchedUser->id);
            })->where('type', 'private')->first();
            
            // If no chat exists, skip this conversation for now
            if (!$chat) {
                return null;
            }
            
            // Get last message in the chat
            $lastMessage = Message::where('chat_id', $chat->id)
                ->orderBy('sent_at', 'desc')
                ->first();

            // Skip chats without messages
            if (!$lastMessage) {
                return null;
            }

            // Count unread messages in this chat
            $unreadCount = Message::where('chat_id', $chat->id)
                                ->where('sender_id', $matchedUser->id)
                                ->whereDoesntHave('messageReads', function($query) use ($user) {
                                    $query->where('user_id', $user->id);
                                })
                                ->count();

            // Check if user is typing (mock for now)
            $isTyping = false;
            
            // Check if user is online (active in last 5 minutes)
            $isOnline = $matchedUser->last_active_at && $matchedUser->last_active_at->gt(now()->subMinutes(5));
            
            // Format last seen
            $lastActive = null;
            if ($matchedUser->last_active_at) {
                if ($isOnline) {
                    $lastActive = 'Active now';
                } elseif ($matchedUser->last_active_at->isToday()) {
                    $lastActive = 'Active today at ' . $matchedUser->last_active_at->format('g:i A');
                } elseif ($matchedUser->last_active_at->isYesterday()) {
                    $lastActive = 'Active yesterday at ' . $matchedUser->last_active_at->format('g:i A');
                } elseif ($matchedUser->last_active_at->gt(now()->subDays(7))) {
                    $lastActive = 'Active ' . $matchedUser->last_active_at->format('l') . ' at ' . $matchedUser->last_active_at->format('g:i A');
                } else {
                    $lastActive = 'Active ' . $matchedUser->last_active_at->format('M j') . ' at ' . $matchedUser->last_active_at->format('g:i A');
                }
            }

            return [
                'id' => $matchedUser->id,
                'chat_id' => $chat->id,
                'name' => $matchedUser->profile?->first_name ?? 'User',
                'full_name' => trim(($matchedUser->profile?->first_name ?? '') . ' ' . ($matchedUser->profile?->last_name ?? '')) ?: 'User',
                'avatar' => $matchedUser->profilePhoto?->thumbnail_url,
                'bio' => $matchedUser->profile?->bio,
                'is_online' => $isOnline,
                'last_active' => $lastActive,
                'last_active_raw' => $matchedUser->last_active_at,
                'is_typing' => $isTyping,
                'last_message' => [
                    'id' => $lastMessage->id,
                    'content' => $lastMessage->content,
                    'type' => $lastMessage->message_type,
                    'sent_at' => $lastMessage->sent_at ?? $lastMessage->created_at,
                    'sent_at_human' => ($lastMessage->sent_at ?? $lastMessage->created_at)->diffForHumans(),
                    'is_from_me' => $lastMessage->sender_id === $user->id,
                    'is_today' => ($lastMessage->sent_at ?? $lastMessage->created_at)->isToday(),
                    'time_formatted' => ($lastMessage->sent_at ?? $lastMessage->created_at)->format('H:i')
                ],
                'unread_count' => $unreadCount,
                'matched_at' => $match->created_at,
                'conversation_priority' => $unreadCount > 0 ? 1 : 0, // For sorting
            ];
        })->filter()->values(); // Remove nulls

        // Sort conversations by latest message time (most recent first)
        $this->conversations = $conversations->sortByDesc(function($conversation) {
            return $conversation['last_message']['sent_at']->timestamp;
        })->values()->toArray();
    }

    public function getFilteredConversations()
    {
        $conversations = collect($this->conversations);

        // Apply search filter
        if (!empty($this->searchTerm)) {
            $conversations = $conversations->filter(function($conv) {
                return stripos($conv['name'], $this->searchTerm) !== false ||
                       stripos($conv['full_name'], $this->searchTerm) !== false ||
                       stripos($conv['last_message']['content'], $this->searchTerm) !== false;
            });
        }

        // Apply status filter
        switch ($this->activeFilter) {
            case 'unread':
                $conversations = $conversations->where('unread_count', '>', 0);
                break;
            case 'online':
                $conversations = $conversations->where('is_online', true);
                break;
            case 'all':
            default:
                // No additional filtering
                break;
        }

        return $conversations->toArray();
    }

    public function setFilter($filter)
    {
        $this->activeFilter = $filter;
    }

    public function openChat($userId)
    {
        $this->selectedConversation = $userId;
        $this->typingUsers = []; // Clear typing users when opening a new chat
        $this->loadSelectedChat($userId);
        $this->loadMessages();
        $this->markAsReadWithoutReload($userId);
        
        // Dispatch event to frontend to subscribe to chat channel
        if ($this->selectedChat) {
            $this->dispatch('chatSelected', ['chatId' => $this->selectedChat->id]);
        }
    }
    
    public function loadSelectedChat($userId)
    {
        $user = Auth::user();
        $this->selectedUser = \App\Models\User::with(['profile', 'profilePhoto'])->find($userId);
        
        if (!$this->selectedUser) {
            return;
        }
        
        // Find or create chat between users
        $this->selectedChat = Chat::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->whereHas('users', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('type', 'private')->first();
        
        if (!$this->selectedChat) {
            // Create new chat
            $this->selectedChat = Chat::create([
                'type' => 'private',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Attach users to chat
            $this->selectedChat->users()->attach([
                $user->id => ['joined_at' => now()],
                $userId => ['joined_at' => now()]
            ]);
        }
    }
    
    public function loadMessages()
    {
        if ($this->selectedChat) {
            $this->messages = Message::where('chat_id', $this->selectedChat->id)
                ->with('sender:id,email', 'sender.profile:user_id,first_name,last_name')
                ->orderBy('sent_at', 'asc')
                ->get()
                ->map(function($message) {
                    return [
                        'id' => $message->id,
                        'content' => $message->content,
                        'sender_id' => $message->sender_id,
                        'sender_name' => $message->sender->profile?->first_name ?? 'User',
                        'is_mine' => $message->sender_id === Auth::id(),
                        'sent_at' => $message->sent_at ?? $message->created_at,
                        'time' => ($message->sent_at ?? $message->created_at)->format('H:i')
                    ];
                })->toArray();
        }
    }
    
    public function sendMessage()
    {
        \Log::info('sendMessage called', [
            'newMessage' => $this->newMessage,
            'selectedChat' => $this->selectedChat?->id,
            'user_id' => Auth::id()
        ]);

        $this->validate([
            'newMessage' => 'required|string|max:1000'
        ]);

        if ($this->selectedChat) {
            $message = Message::create([
                'chat_id' => $this->selectedChat->id,
                'sender_id' => Auth::id(),
                'content' => $this->newMessage,
                'message_type' => 'text',
                'sent_at' => now()
            ]);

            \Log::info('Message created', ['message_id' => $message->id]);

            $user = Auth::user();
            
            // Broadcast to chat participants (not to sender)
            broadcast(new MessageSent($message, $user, $this->selectedChat->id))->toOthers();
            
            // Broadcast conversation update to ALL users in the chat
            foreach ($this->selectedChat->users as $chatUser) {
                if ($chatUser->id !== $user->id) {
                    \Log::info('Broadcasting to user:', ['user_id' => $chatUser->id]);
                    broadcast(new ConversationUpdated($chatUser->id, 'new_message', [
                        'chat_id' => $this->selectedChat->id,
                        'sender_name' => $user->profile?->first_name ?? 'User',
                        'message_preview' => $this->newMessage
                    ]));
                }
            }

            $this->newMessage = '';
            $this->loadMessages();
            $this->loadConversations();
            
            // Dispatch browser event to scroll to bottom
            $this->dispatch('messageSent');
            
            \Log::info('Message sent successfully');
        } else {
            \Log::error('No selected chat for sending message');
        }
    }
    
    public function closeChat()
    {
        $this->selectedConversation = null;
        $this->selectedUser = null;
        $this->selectedChat = null;
        $this->messages = [];
        $this->newMessage = '';
        $this->typingUsers = [];
    }

    public function markAsRead($userId)
    {
        $user = Auth::user();
        
        // Find chat between users
        $chat = Chat::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->whereHas('users', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('type', 'private')->first();
        
        if (!$chat) {
            return;
        }
        
        // Mark all messages from this user as read in this chat
        $messages = Message::where('chat_id', $chat->id)
                          ->where('sender_id', $userId)
                          ->whereDoesntHave('messageReads', function($query) use ($user) {
                              $query->where('user_id', $user->id);
                          })
                          ->get();

        foreach ($messages as $message) {
            MessageRead::create([
                'message_id' => $message->id,
                'user_id' => $user->id,
                'read_at' => now()
            ]);
        }

        // Refresh conversations to update unread counts
        $this->loadConversations();
    }
    
    public function markAsReadWithoutReload($userId)
    {
        $user = Auth::user();
        
        // Find chat between users
        $chat = Chat::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->whereHas('users', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('type', 'private')->first();
        
        if (!$chat) {
            return;
        }
        
        // Mark all messages from this user as read in this chat
        $messages = Message::where('chat_id', $chat->id)
                          ->where('sender_id', $userId)
                          ->whereDoesntHave('messageReads', function($query) use ($user) {
                              $query->where('user_id', $user->id);
                          })
                          ->get();

        foreach ($messages as $message) {
            MessageRead::create([
                'message_id' => $message->id,
                'user_id' => $user->id,
                'read_at' => now()
            ]);
        }
        
        // Just update the unread count for this specific conversation without reloading all
        foreach ($this->conversations as &$conversation) {
            if ($conversation['id'] == $userId) {
                $conversation['unread_count'] = 0;
                break;
            }
        }
    }

    public function deleteConversation($userId = null)
    {
        // Use provided userId or fall back to selected user
        $targetUserId = $userId ?? $this->selectedUser?->id;

        if (!$targetUserId) {
            return;
        }

        $user = Auth::user();

        // Find chat between users
        $chat = Chat::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->whereHas('users', function($query) use ($targetUserId) {
            $query->where('user_id', $targetUserId);
        })->where('type', 'private')->first();

        if (!$chat) {
            return;
        }

        // Delete all messages in this chat (soft delete)
        Message::where('chat_id', $chat->id)->delete();

        $this->loadConversations();

        session()->flash('message', 'Conversation deleted successfully.');

        // Close the chat if we're deleting the currently selected conversation
        if ($this->selectedUser && $this->selectedUser->id == $targetUserId) {
            $this->closeChat();
        }
    }

    public function viewFullProfile()
    {
        if (!$this->selectedUser) {
            return;
        }

        // Redirect to user profile page using UUID
        $profileUuid = $this->selectedUser->profile_uuid ?: $this->selectedUser->generateProfileUuid();
        return redirect()->route('user.profile.show', $profileUuid);
    }

    public function handleConversationUpdate($data = null)
    {
        // Handle the case where data might be wrapped in an array
        if (is_array($data) && isset($data[0])) {
            $data = $data[0];
        }

        \Log::info('handleConversationUpdate called', ['data' => $data]);

        if (!$data || !isset($data['data'])) {
            return;
        }

        $updateData = $data['data'];

        // Update conversation in the list without full reload
        if (isset($updateData['chat_id']) && isset($updateData['message'])) {
            $chatId = $updateData['chat_id'];
            $message = $updateData['message'];

            // Find and update the conversation in the list
            foreach ($this->conversations as &$conversation) {
                if ($conversation['chat_id'] === $chatId) {
                    // Update last message
                    $conversation['last_message'] = [
                        'id' => $message['id'] ?? null,
                        'content' => $message['content'] ?? '',
                        'type' => $message['type'] ?? 'text',
                        'sent_at' => isset($message['sent_at']) ? Carbon::parse($message['sent_at']) : now(),
                        'sent_at_human' => isset($message['sent_at']) ? Carbon::parse($message['sent_at'])->diffForHumans() : 'Just now',
                        'is_from_me' => $message['sender_id'] === Auth::id(),
                        'is_today' => true,
                        'time_formatted' => isset($message['sent_at']) ? Carbon::parse($message['sent_at'])->format('H:i') : now()->format('H:i')
                    ];

                    // Update unread count
                    if (isset($updateData['unread_count'])) {
                        $conversation['unread_count'] = $updateData['unread_count'];
                    }

                    break;
                }
            }

            // Resort conversations by latest message time
            $this->conversations = collect($this->conversations)
                ->sortByDesc(function($conv) {
                    return $conv['last_message']['sent_at'] ?? now();
                })
                ->values()
                ->toArray();
        }
    }

    public function handleMessageRead($data = null)
    {
        // Handle the case where data might be wrapped in an array
        if (is_array($data) && isset($data[0])) {
            $data = $data[0];
        }

        \Log::info('handleMessageRead called', ['data' => $data]);

        if (!$data || !isset($data['chat_id'])) {
            return;
        }

        // Update unread count for the specific chat
        foreach ($this->conversations as &$conversation) {
            if ($conversation['chat_id'] === $data['chat_id']) {
                $conversation['unread_count'] = 0;
                break;
            }
        }
    }

    public function handleNewMessage($message = null)
    {
        // Handle the case where $message might be wrapped in an array
        if (is_array($message) && isset($message[0])) {
            $message = $message[0];
        }
        
        // Extract message data from the event
        $messageData = isset($message['message']) ? $message['message'] : $message;
        
        // Log for debugging
        \Log::info('handleNewMessage called', [
            'messageData' => $messageData, 
            'selectedChat' => $this->selectedChat?->id,
            'selectedUser' => $this->selectedUser?->id
        ]);
        
        // Check if message belongs to current chat
        if ($this->selectedChat && $this->selectedUser) {
            // Only add if sender is not current user (to avoid duplicates)
            if (isset($messageData['sender_id']) && $messageData['sender_id'] !== Auth::id()) {
                // Check if this message is for the current chat
                if ($messageData['sender_id'] == $this->selectedUser->id) {
                    $newMessage = [
                        'id' => $messageData['id'] ?? null,
                        'content' => $messageData['content'] ?? '',
                        'sender_id' => $messageData['sender_id'],
                        'sender_name' => $messageData['sender_name'] ?? 'User',
                        'is_mine' => false,
                        'sent_at' => isset($messageData['sent_at']) ? 
                            (is_string($messageData['sent_at']) ? 
                                \Carbon\Carbon::parse($messageData['sent_at']) : 
                                $messageData['sent_at']) : 
                            now(),
                        'time' => $messageData['time'] ?? now()->format('H:i')
                    ];
                    
                    $this->messages[] = $newMessage;
                    $this->dispatch('messageSent'); // Scroll to bottom
                    
                    \Log::info('New message added to chat', ['newMessage' => $newMessage]);
                }
            }
        }
        
        // Always refresh conversations to update the list
        $this->loadConversations();
    }

    public function handleStartTyping()
    {
        \Log::info('handleStartTyping method called', ['selected_chat' => $this->selectedChat?->id, 'user_id' => Auth::id()]);
        
        if ($this->selectedChat) {
            // Only send typing event if we're not already typing
            if (!$this->isCurrentlyTyping) {
                $this->isCurrentlyTyping = true;
                \Log::info('User started typing', ['user_id' => Auth::id(), 'chat_id' => $this->selectedChat->id]);
                
                try {
                    broadcast(new UserTyping(Auth::user(), $this->selectedChat->id, true))->toOthers();
                    \Log::info('Typing broadcast sent successfully');
                } catch (\Exception $e) {
                    \Log::error('Failed to broadcast typing event', ['error' => $e->getMessage()]);
                }
                
                // Set a timer to automatically stop typing after 5 seconds of inactivity
                $this->dispatch('setTypingTimer', ['chatId' => $this->selectedChat->id]);
            } else {
                \Log::info('Already typing, ignoring duplicate start typing event');
            }
        } else {
            \Log::warning('handleStartTyping called but no selected chat');
        }
    }
    
    public function stopTypingIndicator()
    {
        \Log::info('stopTypingIndicator method called', ['selected_chat' => $this->selectedChat?->id, 'user_id' => Auth::id()]);
        
        if ($this->selectedChat && $this->isCurrentlyTyping) {
            $this->isCurrentlyTyping = false;
            \Log::info('User stopped typing', ['user_id' => Auth::id(), 'chat_id' => $this->selectedChat->id]);
            
            try {
                broadcast(new UserTyping(Auth::user(), $this->selectedChat->id, false))->toOthers();
                \Log::info('Stop typing broadcast sent successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to broadcast stop typing event', ['error' => $e->getMessage()]);
            }
            
            // Clear the typing timer
            $this->dispatch('clearTypingTimer');
        } else {
            \Log::info('Stop typing called but not currently typing or no selected chat');
        }
    }
    
    public function stopTypingSimple()
    {
        \Log::info('stopTypingSimple method called');
        return 'stopTypingSimple works!';
    }
    
    public function stop()
    {
        \Log::info('stop method called');
        if ($this->selectedChat) {
            broadcast(new UserTyping(Auth::user(), $this->selectedChat->id, false))->toOthers();
            $this->dispatch('clearTypingTimer');
        }
        return 'stop method works!';
    }
    
    // Alias for the old method name
    public function stopTyping()
    {
        $this->stopTypingIndicator();
    }
    
    // Alias for backward compatibility
    public function handleStopTyping()
    {
        $this->stopTyping();
    }
    
    public function handleUserTyping(...$data)
    {
        \Log::info('handleUserTyping method called', ['raw_data' => $data, 'current_typing_users' => $this->typingUsers]);
        
        // Handle the case where $data might be wrapped in an array
        $eventData = $data[0] ?? null;
        if (is_array($eventData) && isset($eventData[0])) {
            $eventData = $eventData[0];
        }
        
        if (!$eventData || !is_array($eventData)) {
            \Log::warning('handleUserTyping called with invalid data', ['data' => $data, 'eventData' => $eventData]);
            return;
        }
        
        \Log::info('handleUserTyping processing', ['data' => $eventData, 'current_user_id' => Auth::id()]);
        
        // Only process typing events from other users, not the current user
        if ($eventData['user_id'] != Auth::id()) {
            if ($eventData['is_typing']) {
                $this->typingUsers[$eventData['user_id']] = $eventData['user_name'];
                \Log::info('Added user to typing list', ['user_id' => $eventData['user_id'], 'user_name' => $eventData['user_name']]);
            } else {
                unset($this->typingUsers[$eventData['user_id']]);
                \Log::info('Removed user from typing list', ['user_id' => $eventData['user_id']]);
            }
            
            // Update conversations list to show typing indicators
            $this->updateConversationsWithTypingStatus($eventData['user_id'], $eventData['is_typing']);
            
            \Log::info('Updated typing users', ['typing_users' => $this->typingUsers, 'count' => count($this->typingUsers)]);
            
            // Force Livewire to update the UI
            $this->dispatch('$refresh');
        } else {
            \Log::info('Ignoring typing event from current user', ['user_id' => $eventData['user_id'], 'current_user_id' => Auth::id()]);
        }
    }

    /**
     * Update conversations list to show typing indicators
     */
    private function updateConversationsWithTypingStatus($userId, $isTyping)
    {
        if (!is_array($this->conversations)) {
            \Log::warning('Conversations is not an array', ['conversations' => $this->conversations]);
            return;
        }

        foreach ($this->conversations as &$conversation) {
            if (!is_array($conversation) || !isset($conversation['id'])) {
                \Log::warning('Invalid conversation structure', ['conversation' => $conversation]);
                continue;
            }

            if ($conversation['id'] == $userId) {
                $conversation['is_typing'] = $isTyping;
                \Log::info('Updated conversation typing status', [
                    'conversation_id' => $conversation['id'],
                    'user_id' => $userId,
                    'is_typing' => $isTyping
                ]);
                break;
            }
        }
    }

    public function testAddTyping()
    {
        $this->handleUserTyping(['user_id' => 999, 'user_name' => 'Test User', 'is_typing' => true]);
    }
    
    public function testRemoveTyping()
    {
        $this->handleUserTyping(['user_id' => 999, 'user_name' => 'Test User', 'is_typing' => false]);
    }
    
    public function testBroadcastTyping()
    {
        if ($this->selectedChat) {
            \Log::info('Manual typing broadcast test', ['chat_id' => $this->selectedChat->id, 'user_id' => Auth::id()]);
            
            // Broadcast typing event to others in the chat
            broadcast(new UserTyping(Auth::user(), $this->selectedChat->id, true))->toOthers();
            
            // Log the broadcast
            \Log::info('Broadcast sent to channel: chat.' . $this->selectedChat->id);
        }
    }

    public function testBroadcast()
    {
        \Log::info('Test broadcast to user: ' . Auth::id());
        broadcast(new ConversationUpdated(Auth::id(), 'test', ['message' => 'Test broadcast']));
        return 'Broadcast sent to user.' . Auth::id();
    }
    
    public function testTypingIndicator()
    {
        if ($this->selectedChat) {
            // Simulate another user typing
            $this->handleUserTyping([
                'user_id' => 999,
                'user_name' => 'Test User',
                'is_typing' => true,
                'chat_id' => $this->selectedChat->id
            ]);
            
            // Remove after 3 seconds
            $this->dispatch('removeTestTyping');
            
            return 'Test typing indicator added';
        }
        
        return 'No chat selected';
    }
    
    public function debugTypingState()
    {
        $debugInfo = [
            'selected_chat' => $this->selectedChat?->id,
            'typing_users' => $this->typingUsers,
            'typing_users_count' => count($this->typingUsers),
            'current_user_id' => Auth::id()
        ];
        
        \Log::info('Debug typing state', $debugInfo);
        
        // Also dispatch to frontend for console logging
        $this->dispatch('debugInfo', $debugInfo);
        
        return 'Debug info logged to console and Laravel logs';
    }
    
    public function forceShowTyping()
    {
        $this->typingUsers[999] = 'Test User';
        \Log::info('Force set typing users', ['typing_users' => $this->typingUsers]);
        $this->dispatch('$refresh');
        return 'Forced typing indicator to show';
    }
    
    public function clearTyping()
    {
        $this->typingUsers = [];
        \Log::info('Cleared typing users');
        $this->dispatch('$refresh');
        return 'Cleared typing indicator';
    }
    
    public function testWebSocketBroadcast()
    {
        if ($this->selectedChat) {
            \Log::info('Testing WebSocket broadcast', ['chat_id' => $this->selectedChat->id, 'user_id' => Auth::id()]);
            
            // Test broadcasting a typing event
            broadcast(new UserTyping(Auth::user(), $this->selectedChat->id, true))->toOthers();
            
            // Also test the event that should be received (using a different user ID)
            $this->handleUserTyping([
                'user_id' => 999, // Use a different user ID to avoid current user filtering
                'user_name' => 'Test User',
                'is_typing' => true,
                'chat_id' => $this->selectedChat->id
            ]);
            
            return 'WebSocket broadcast test sent';
        }
        
        return 'No chat selected for WebSocket test';
    }

    public function getCurrentUserId()
    {
        return Auth::id();
    }
    
    public function debugMethods()
    {
        $methods = get_class_methods($this);
        $publicMethods = array_filter($methods, function($method) {
            $reflection = new \ReflectionMethod($this, $method);
            return $reflection->isPublic() && !$reflection->isConstructor();
        });
        
        \Log::info('Available public methods', ['methods' => array_values($publicMethods)]);
        
        return [
            'all_methods' => $methods,
            'public_methods' => array_values($publicMethods),
            'has_stopTyping' => method_exists($this, 'stopTyping'),
            'has_handleStopTyping' => method_exists($this, 'handleStopTyping')
        ];
    }
    
    public function testStopTyping()
    {
        \Log::info('testStopTyping called - this method exists and works');
        return 'testStopTyping method works!';
    }

    public function toggleInfoPanel()
    {
        $this->showInfoPanel = !$this->showInfoPanel;
    }

    public function startAudioCall()
    {
        if (!$this->selectedUser) {
            return;
        }

        try {
            $videoSDKService = app(VideoSDKService::class);
            $callData = $videoSDKService->createCall(auth()->user(), $this->selectedUser, 'voice');

            $call = $callData['call'];
            $token = $callData['token'];

            // Broadcast call event to the receiver
            event(new CallInitiatedEvent($call));

            // Dispatch to frontend to start audio call
            $this->dispatch('startAudioCall', [
                'meetingId' => $call->channel_name,
                'token' => $token,
                'participantName' => auth()->user()->profile->first_name ?? 'User',
                'callType' => 'voice',
                'recipientName' => $this->selectedUser->profile->first_name,
                'recipientId' => $this->selectedUser->id
            ]);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to start audio call. Please try again.');
        }
    }

    public function startVideoCall()
    {
        if (!$this->selectedUser) {
            return;
        }

        try {
            $videoSDKService = app(VideoSDKService::class);
            $callData = $videoSDKService->createCall(auth()->user(), $this->selectedUser, 'video');

            $call = $callData['call'];
            $token = $callData['token'];

            // Broadcast call event to the receiver
            event(new CallInitiatedEvent($call));

            // Dispatch to frontend to start video call
            $this->dispatch('startVideoCall', [
                'meetingId' => $call->channel_name,
                'token' => $token,
                'participantName' => auth()->user()->profile->first_name ?? 'User',
                'callType' => 'video',
                'recipientName' => $this->selectedUser->profile->first_name,
                'recipientId' => $this->selectedUser->id
            ]);

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to start video call. Please try again.');
        }
    }

    public function blockUser()
    {
        if (!$this->selectedUser) {
            return;
        }

        // Implement block user functionality
        session()->flash('message', 'User has been blocked');
        $this->closeChat();
    }

    public function reportUser()
    {
        if (!$this->selectedUser) {
            return;
        }

        // Implement report user functionality
        session()->flash('message', 'User has been reported');
    }


    public function render()
    {
        return view('livewire.pages.messages-page', [
            'filteredConversations' => $this->getFilteredConversations()
        ])->layout('layouts.app-sidebar', ['title' => 'Messages']);
    }
}
