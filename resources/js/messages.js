/**
 * Messages page real-time functionality
 * Handles chat subscriptions, typing indicators, and message updates
 */

function initializeMessages() {
    // Only initialize on messages page
    if (!document.querySelector('[data-page="messages"]')) {
        console.log('Not on messages page, skipping initialization');
        return;
    }
    
    console.log('On messages page, initializing...');

    // Wait for Echo to be available
    function waitForEcho(callback) {
        if (window.Echo && window.Echo.connector) {
            callback();
        } else {
            setTimeout(() => waitForEcho(callback), 100);
        }
    }

    waitForEcho(() => {
        console.log('Echo instance available');
        
        // Listen for Pusher connection state changes
        if (window.Echo.connector && window.Echo.connector.pusher) {
            const pusher = window.Echo.connector.pusher;
            
            pusher.connection.bind('connected', () => {
                console.log('✅ WebSocket connected to Reverb');
                // Subscribe to channels after connection is established
                subscribeToChannels();
            });
            
            pusher.connection.bind('disconnected', () => {
                console.log('❌ WebSocket disconnected from Reverb');
            });
            
            pusher.connection.bind('error', (error) => {
                console.error('WebSocket error:', error);
            });
            
            console.log('Current connection state:', pusher.connection.state);
            
            // If already connected, subscribe immediately
            if (pusher.connection.state === 'connected') {
                subscribeToChannels();
            }
        }
    });

    // Function to subscribe to channels
    function subscribeToChannels() {
        // Get current user ID from meta tag or data attribute
        const currentUserId = document.querySelector('meta[name="user-id"]')?.content || 
                            document.querySelector('[data-user-id]')?.dataset.userId;
        
        if (!currentUserId) {
            console.warn('User ID not found, cannot initialize chat subscriptions');
            return;
        }

        console.log('Current user ID:', currentUserId);

        // Subscribe to user's conversation updates
        try {
            console.log(`Attempting to subscribe to user.${currentUserId}`);
            
            const userChannel = window.Echo.private(`user.${currentUserId}`);
            
            userChannel.subscribed(() => {
                console.log(`✅ Successfully subscribed to user.${currentUserId}`);
            });
            
            userChannel.listen('.conversation.updated', (e) => {
                console.log('📨 Conversation updated event received:', e);
                // Dispatch to the optimized handler instead of full refresh
                if (window.Livewire) {
                    Livewire.dispatch('conversationUpdated', [e]);
                }
            });
            
            userChannel.error((error) => {
                console.error(`❌ Failed to subscribe to user.${currentUserId}:`, error);
            });
                
        } catch (error) {
            console.error('Error subscribing to user channel:', error);
        }
    }

    // Wait for both Livewire and Echo to be ready
    document.addEventListener('livewire:initialized', () => {
        console.log('Livewire initialized');
        
        // Find the Livewire component
        function getMessagesComponent() {
            const messagesElement = document.querySelector('[wire\\:id]');
            return messagesElement ? Livewire.find(messagesElement.getAttribute('wire:id')) : null;
        }

        // Chat channel management
        let currentChatChannel = null;
        let currentChatSubscription = null;
        
        // Function to join a chat channel
        window.joinChatChannel = function(chatId) {
            if (!window.Echo) {
                console.error('Echo not available');
                return;
            }
            
            console.log('Attempting to join chat channel:', chatId);
            
            // Leave previous channel if exists
            if (currentChatSubscription) {
                window.Echo.leave(`chat.${currentChatChannel}`);
                currentChatSubscription = null;
                console.log('Left previous channel:', currentChatChannel);
            }
            
            currentChatChannel = chatId;
            
            // Subscribe to new channel
            try {
                currentChatSubscription = window.Echo.private(`chat.${chatId}`);
                
                currentChatSubscription.subscribed(() => {
                    console.log('✅ Successfully subscribed to chat channel:', chatId);
                });
                
                currentChatSubscription.listen('.message.sent', (e) => {
                    console.log('📨 New message received in chat:', e);
                    if (window.Livewire) {
                        // Add the message to the current chat - send as array
                        Livewire.dispatch('newMessageReceived', [e]);

                        // Update conversation list efficiently
                        Livewire.dispatch('conversationUpdated', [{
                            type: 'new_message',
                            data: {
                                chat_id: chatId,
                                message: e.message || e,
                                unread_count: 1
                            }
                        }]);
                    }
                });
                
                // Listen for typing events
                currentChatSubscription.listen('.user.typing', (e) => {
                    console.log('⌨️ Typing event received:', e);
                    if (window.Livewire) {
                        Livewire.dispatch('userTyping', [e]);
                    }
                });
                
                currentChatSubscription.error((error) => {
                    console.error('❌ Error subscribing to chat channel:', error);
                });
                
            } catch (error) {
                console.error('Failed to create channel:', error);
            }
        }
        
        // Listen for chat selection changes
        if (window.Livewire) {
            Livewire.on('chatSelected', (data) => {
                console.log('Chat selected event:', data);
                // Handle both array and object formats
                const eventData = Array.isArray(data) ? data[0] : data;
                if (eventData && eventData.chatId) {
                    window.joinChatChannel(eventData.chatId);
                }
            });
        }


        // Scroll to bottom functionality
        function scrollToBottom() {
            setTimeout(() => {
                const container = document.getElementById('messagesContainer');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            }, 100);
        }

        // Listen for message sent event
        Livewire.on('messageSent', () => {
            console.log('Message sent, scrolling to bottom');
            scrollToBottom();
        });

        // Scroll to bottom when chat opens
        Livewire.hook('morph.updated', ({ el, component }) => {
            const container = document.getElementById('messagesContainer');
            if (container && container.children.length > 1) {
                scrollToBottom();
            }
        });
    });
}

// Global debug function
window.checkChannels = function() {
    if (!window.Echo) {
        console.log('Echo not available');
        return [];
    }
    
    if (window.Echo.connector && window.Echo.connector.pusher) {
        const pusher = window.Echo.connector.pusher;
        console.log('Connection state:', pusher.connection.state);
        console.log('Socket ID:', pusher.connection.socket_id);
        
        // Get all subscribed channels
        const channels = Object.keys(pusher.channels.channels);
        console.log('Subscribed channels:', channels);
        
        if (channels.length === 0) {
            console.log('No channels subscribed. Attempting to subscribe...');
            // Try to get user ID and subscribe
            const userId = document.querySelector('meta[name="user-id"]')?.content;
            if (userId) {
                console.log('Found user ID:', userId);
                // Force subscription
                try {
                    const channel = window.Echo.private(`user.${userId}`);
                    channel.subscribed(() => {
                        console.log('Subscribed to user channel!');
                    });
                } catch (error) {
                    console.error('Subscription error:', error);
                }
            }
        }
        
        return channels;
    }
    
    return [];
};

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeMessages);
} else {
    initializeMessages();
}

// Export for module system
export default initializeMessages;