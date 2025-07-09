# Mobile App Integration Guide: Optimized Call-Message System

## Overview

This guide provides step-by-step instructions for implementing the optimized call-message integration system in a mobile app. Follow these instructions to create a seamless video/voice calling experience with integrated chat messaging.

## 🎯 Implementation Requirements

### Core Features to Implement:
1. **Automatic Call Message Creation** - No manual message creation needed
2. **Real-time Call Status Updates** - Messages update automatically as call status changes
3. **Enhanced Chat Display** - Show call messages with duration, status, and controls
4. **Call History Integration** - Seamless call history within chat context
5. **Offline Handling** - Graceful handling of missed calls and network issues

## 📱 API Integration

### 1. Call Initiation with Automatic Message Creation

```typescript
// Call Service Implementation
class CallService {
  async initiateCall(recipientId: number, callType: 'video' | 'voice') {
    try {
      const response = await fetch('/api/v1/video-call/initiate', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          recipient_id: recipientId,
          call_type: callType
        })
      });

      const data = await response.json();
      
      if (data.status === 'success') {
        // Call message is automatically created
        return {
          callId: data.data.call_id,
          meetingId: data.data.meeting_id,
          token: data.data.token,
          messageId: data.data.message_id, // ← Auto-created message
          type: data.data.type,
          caller: data.data.caller,
          receiver: data.data.receiver
        };
      }
      
      throw new Error(data.message || 'Failed to initiate call');
    } catch (error) {
      console.error('Call initiation failed:', error);
      throw error;
    }
  }

  async joinCall(callId: number) {
    try {
      const response = await fetch(`/api/v1/video-call/${callId}/join`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        }
      });

      const data = await response.json();
      
      if (data.status === 'success') {
        // Message automatically updates to "Call in progress"
        return {
          callId: data.data.call_id,
          meetingId: data.data.meeting_id,
          token: data.data.token,
          messageId: data.data.message_id,
          type: data.data.type
        };
      }
      
      throw new Error(data.message || 'Failed to join call');
    } catch (error) {
      console.error('Call join failed:', error);
      throw error;
    }
  }

  async endCall(callId: number) {
    try {
      const response = await fetch(`/api/v1/video-call/${callId}/end`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        }
      });

      const data = await response.json();
      
      if (data.status === 'success') {
        // Message automatically updates with duration
        return {
          callId: data.data.call_id,
          duration: data.data.duration,
          formattedDuration: data.data.formatted_duration,
          messageId: data.data.message_id
        };
      }
      
      throw new Error(data.message || 'Failed to end call');
    } catch (error) {
      console.error('Call end failed:', error);
      throw error;
    }
  }

  async rejectCall(callId: number) {
    try {
      const response = await fetch(`/api/v1/video-call/${callId}/reject`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        }
      });

      const data = await response.json();
      
      if (data.status === 'success') {
        // Message automatically updates to "Call declined"
        return {
          callId: data.data.call_id,
          messageId: data.data.message_id
        };
      }
      
      throw new Error(data.message || 'Failed to reject call');
    } catch (error) {
      console.error('Call rejection failed:', error);
      throw error;
    }
  }
}
```

### 2. Chat Service with Enhanced Call Support

```typescript
// Enhanced Chat Service
class ChatService {
  async getChatWithCallData(chatId: number, page: number = 1, includeCallData: boolean = true) {
    try {
      const response = await fetch(
        `/api/v1/chats/${chatId}?page=${page}&per_page=20&include_call_data=${includeCallData}`,
        {
          headers: {
            'Authorization': `Bearer ${authToken}`,
          }
        }
      );

      const data = await response.json();
      
      if (data.status === 'success') {
        return {
          chat: data.data.chat,
          messages: data.data.messages.data.map(this.transformMessage),
          pagination: data.data.messages.pagination
        };
      }
      
      throw new Error(data.message || 'Failed to get chat');
    } catch (error) {
      console.error('Get chat failed:', error);
      throw error;
    }
  }

  async getCallMessages(chatId: number, filters?: CallMessageFilters) {
    try {
      const queryParams = new URLSearchParams({
        page: (filters?.page || 1).toString(),
        per_page: (filters?.perPage || 20).toString(),
        ...(filters?.callType && { call_type: filters.callType }),
        ...(filters?.callStatus && { call_status: filters.callStatus })
      });

      const response = await fetch(
        `/api/v1/chats/${chatId}/call-messages?${queryParams}`,
        {
          headers: {
            'Authorization': `Bearer ${authToken}`,
          }
        }
      );

      const data = await response.json();
      
      if (data.status === 'success') {
        return {
          callMessages: data.data.call_messages.map(this.transformCallMessage),
          pagination: data.data.pagination
        };
      }
      
      throw new Error(data.message || 'Failed to get call messages');
    } catch (error) {
      console.error('Get call messages failed:', error);
      throw error;
    }
  }

  async getCallStatistics(chatId: number) {
    try {
      const response = await fetch(`/api/v1/chats/${chatId}/call-statistics`, {
        headers: {
          'Authorization': `Bearer ${authToken}`,
        }
      });

      const data = await response.json();
      
      if (data.status === 'success') {
        return data.data;
      }
      
      throw new Error(data.message || 'Failed to get call statistics');
    } catch (error) {
      console.error('Get call statistics failed:', error);
      throw error;
    }
  }

  private transformMessage(message: any): ChatMessage {
    return {
      id: message.id,
      chatId: message.chat_id,
      senderId: message.sender_id,
      content: message.content,
      messageType: message.message_type,
      mediaUrl: message.media_url,
      mediaData: message.media_data,
      sentAt: new Date(message.sent_at),
      isRead: message.is_read,
      readAt: message.read_at ? new Date(message.read_at) : null,
      isMine: message.is_mine,
      sender: message.sender,
      replyTo: message.reply_to,
      // Enhanced call data
      callDetails: message.call_details ? {
        callId: message.call_details.call_id,
        type: message.call_details.type,
        status: message.call_details.status,
        durationSeconds: message.call_details.duration_seconds,
        formattedDuration: message.call_details.formatted_duration,
        startedAt: message.call_details.started_at ? new Date(message.call_details.started_at) : null,
        endedAt: message.call_details.ended_at ? new Date(message.call_details.ended_at) : null,
        isActive: message.call_details.is_active,
        otherParticipant: message.call_details.other_participant
      } : null
    };
  }

  private transformCallMessage(message: any): CallMessage {
    return {
      ...this.transformMessage(message),
      callDetails: message.call_details!
    };
  }
}

// Type definitions
interface CallMessageFilters {
  page?: number;
  perPage?: number;
  callType?: 'video' | 'voice';
  callStatus?: 'completed' | 'rejected' | 'missed' | 'ongoing';
}

interface ChatMessage {
  id: number;
  chatId: number;
  senderId: number;
  content: string;
  messageType: string;
  mediaUrl?: string;
  mediaData?: any;
  sentAt: Date;
  isRead: boolean;
  readAt?: Date | null;
  isMine: boolean;
  sender: any;
  replyTo?: any;
  callDetails?: CallDetails | null;
}

interface CallDetails {
  callId: number;
  type: 'video' | 'voice';
  status: 'initiated' | 'ongoing' | 'completed' | 'missed' | 'rejected';
  durationSeconds: number;
  formattedDuration: string;
  startedAt?: Date | null;
  endedAt?: Date | null;
  isActive: boolean;
  otherParticipant?: any;
}

interface CallMessage extends ChatMessage {
  callDetails: CallDetails;
}
```

## 🔄 Real-time Updates with WebSocket

### WebSocket Integration for Call Status Updates

```typescript
// WebSocket Service for Real-time Updates
class WebSocketService {
  private socket: WebSocket | null = null;
  private reconnectAttempts = 0;
  private maxReconnectAttempts = 5;
  private reconnectDelay = 1000;

  connect(userId: number) {
    try {
      this.socket = new WebSocket(`wss://your-app.com/ws?user_id=${userId}&token=${authToken}`);
      
      this.socket.onopen = () => {
        console.log('WebSocket connected');
        this.reconnectAttempts = 0;
        
        // Subscribe to user-specific channel
        this.socket?.send(JSON.stringify({
          type: 'subscribe',
          channel: `private-user.${userId}`
        }));
      };

      this.socket.onmessage = (event) => {
        try {
          const data = JSON.parse(event.data);
          this.handleWebSocketMessage(data);
        } catch (error) {
          console.error('Failed to parse WebSocket message:', error);
        }
      };

      this.socket.onclose = () => {
        console.log('WebSocket disconnected');
        this.attemptReconnect(userId);
      };

      this.socket.onerror = (error) => {
        console.error('WebSocket error:', error);
      };
    } catch (error) {
      console.error('Failed to connect WebSocket:', error);
      this.attemptReconnect(userId);
    }
  }

  private handleWebSocketMessage(data: any) {
    switch (data.event) {
      case 'CallInitiated':
        this.handleCallInitiated(data.data);
        break;
      case 'CallStatusChanged':
        this.handleCallStatusChanged(data.data);
        break;
      case 'NewMessage':
        this.handleNewMessage(data.data);
        break;
      case 'MessageEdited':
        this.handleMessageEdited(data.data);
        break;
      default:
        console.log('Unknown WebSocket event:', data.event);
    }
  }

  private handleCallInitiated(callData: any) {
    // Show incoming call UI
    EventBus.emit('call:incoming', {
      callId: callData.call.id,
      type: callData.call.type,
      caller: callData.call.caller,
      receiver: callData.call.receiver
    });

    // Auto-refresh chat to show new call message
    EventBus.emit('chat:refresh', {
      chatId: this.getChatIdFromUsers(callData.call.caller.id, callData.call.receiver.id)
    });
  }

  private handleCallStatusChanged(callData: any) {
    // Update call UI based on status
    EventBus.emit('call:statusChanged', {
      callId: callData.call.id,
      status: callData.call.status,
      duration: callData.call.duration_seconds
    });

    // Auto-refresh chat to show updated message
    EventBus.emit('chat:refresh', {
      chatId: this.getChatIdFromUsers(callData.call.caller_id, callData.call.receiver_id)
    });
  }

  private handleNewMessage(messageData: any) {
    // Handle new message (including auto-created call messages)
    EventBus.emit('message:new', messageData);
  }

  private handleMessageEdited(messageData: any) {
    // Handle message edits (including call message updates)
    EventBus.emit('message:edited', messageData);
  }

  private attemptReconnect(userId: number) {
    if (this.reconnectAttempts < this.maxReconnectAttempts) {
      this.reconnectAttempts++;
      const delay = this.reconnectDelay * Math.pow(2, this.reconnectAttempts - 1);
      
      setTimeout(() => {
        console.log(`Reconnecting WebSocket (attempt ${this.reconnectAttempts})...`);
        this.connect(userId);
      }, delay);
    }
  }

  disconnect() {
    if (this.socket) {
      this.socket.close();
      this.socket = null;
    }
  }

  private getChatIdFromUsers(userId1: number, userId2: number): number {
    // Implement logic to get chat ID from user IDs
    // This might involve a local cache or API call
    return 0; // Placeholder
  }
}

// Event Bus for app-wide communication
class EventBus {
  private static listeners: { [key: string]: Function[] } = {};

  static on(event: string, callback: Function) {
    if (!this.listeners[event]) {
      this.listeners[event] = [];
    }
    this.listeners[event].push(callback);
  }

  static emit(event: string, data?: any) {
    if (this.listeners[event]) {
      this.listeners[event].forEach(callback => callback(data));
    }
  }

  static off(event: string, callback: Function) {
    if (this.listeners[event]) {
      this.listeners[event] = this.listeners[event].filter(cb => cb !== callback);
    }
  }
}
```

## 🎨 UI Component Implementation

### 1. Call Message Component

```tsx
// React Native Example - Call Message Component
import React from 'react';
import { View, Text, TouchableOpacity, StyleSheet } from 'react-native';
import Icon from 'react-native-vector-icons/MaterialIcons';

interface CallMessageProps {
  message: CallMessage;
  onCallBack?: (callId: number, type: 'video' | 'voice') => void;
  onViewDetails?: (callId: number) => void;
}

const CallMessageComponent: React.FC<CallMessageProps> = ({
  message,
  onCallBack,
  onViewDetails
}) => {
  const { callDetails } = message;
  
  const getCallIcon = () => {
    if (callDetails.type === 'video') {
      return callDetails.status === 'missed' ? 'videocam-off' : 'videocam';
    }
    return callDetails.status === 'missed' ? 'phone-missed' : 'phone';
  };

  const getCallColor = () => {
    switch (callDetails.status) {
      case 'completed':
        return '#4CAF50'; // Green
      case 'missed':
        return '#F44336'; // Red
      case 'rejected':
        return '#FF9800'; // Orange
      case 'ongoing':
        return '#2196F3'; // Blue
      default:
        return '#757575'; // Gray
    }
  };

  const getStatusText = () => {
    switch (callDetails.status) {
      case 'completed':
        return `${callDetails.formattedDuration}`;
      case 'missed':
        return 'Missed';
      case 'rejected':
        return 'Declined';
      case 'ongoing':
        return 'In progress...';
      default:
        return message.content;
    }
  };

  const canCallBack = callDetails.status !== 'ongoing' && callDetails.status !== 'initiated';

  return (
    <View style={[
      styles.container,
      message.isMine ? styles.myMessage : styles.theirMessage
    ]}>
      <View style={styles.callInfo}>
        <Icon 
          name={getCallIcon()} 
          size={24} 
          color={getCallColor()} 
          style={styles.callIcon}
        />
        
        <View style={styles.callDetails}>
          <Text style={styles.callType}>
            {callDetails.type === 'video' ? 'Video Call' : 'Voice Call'}
          </Text>
          <Text style={[styles.callStatus, { color: getCallColor() }]}>
            {getStatusText()}
          </Text>
          {callDetails.endedAt && (
            <Text style={styles.callTime}>
              {callDetails.endedAt.toLocaleTimeString()}
            </Text>
          )}
        </View>
      </View>

      {canCallBack && (
        <View style={styles.actions}>
          <TouchableOpacity
            style={[styles.actionButton, styles.callBackButton]}
            onPress={() => onCallBack?.(callDetails.callId, callDetails.type)}
          >
            <Icon 
              name={callDetails.type === 'video' ? 'videocam' : 'phone'} 
              size={16} 
              color="#fff" 
            />
            <Text style={styles.actionButtonText}>Call Back</Text>
          </TouchableOpacity>

          <TouchableOpacity
            style={[styles.actionButton, styles.detailsButton]}
            onPress={() => onViewDetails?.(callDetails.callId)}
          >
            <Icon name="info" size={16} color="#2196F3" />
            <Text style={[styles.actionButtonText, { color: '#2196F3' }]}>
              Details
            </Text>
          </TouchableOpacity>
        </View>
      )}
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    maxWidth: '80%',
    marginVertical: 4,
    marginHorizontal: 16,
    padding: 12,
    borderRadius: 12,
    backgroundColor: '#f5f5f5',
  },
  myMessage: {
    alignSelf: 'flex-end',
    backgroundColor: '#E3F2FD',
  },
  theirMessage: {
    alignSelf: 'flex-start',
    backgroundColor: '#f5f5f5',
  },
  callInfo: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  callIcon: {
    marginRight: 12,
  },
  callDetails: {
    flex: 1,
  },
  callType: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
  },
  callStatus: {
    fontSize: 12,
    marginTop: 2,
  },
  callTime: {
    fontSize: 10,
    color: '#757575',
    marginTop: 2,
  },
  actions: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 8,
  },
  actionButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 6,
    flex: 0.48,
    justifyContent: 'center',
  },
  callBackButton: {
    backgroundColor: '#4CAF50',
  },
  detailsButton: {
    backgroundColor: '#E3F2FD',
    borderWidth: 1,
    borderColor: '#2196F3',
  },
  actionButtonText: {
    fontSize: 12,
    fontWeight: '500',
    marginLeft: 4,
    color: '#fff',
  },
});

export default CallMessageComponent;
```

### 2. Incoming Call Modal

```tsx
// Incoming Call Modal Component
import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  Modal,
  Image,
  Animated,
  Vibration,
} from 'react-native';
import Icon from 'react-native-vector-icons/MaterialIcons';

interface IncomingCallModalProps {
  visible: boolean;
  callData: {
    callId: number;
    type: 'video' | 'voice';
    caller: {
      id: number;
      name: string;
      profile_photo_path?: string;
    };
  } | null;
  onAccept: (callId: number) => void;
  onReject: (callId: number) => void;
}

const IncomingCallModal: React.FC<IncomingCallModalProps> = ({
  visible,
  callData,
  onAccept,
  onReject
}) => {
  const [pulseAnim] = useState(new Animated.Value(1));

  useEffect(() => {
    if (visible) {
      // Start pulsing animation
      const pulse = Animated.loop(
        Animated.sequence([
          Animated.timing(pulseAnim, {
            toValue: 1.1,
            duration: 1000,
            useNativeDriver: true,
          }),
          Animated.timing(pulseAnim, {
            toValue: 1,
            duration: 1000,
            useNativeDriver: true,
          }),
        ])
      );
      pulse.start();

      // Vibrate for incoming call
      const vibrationPattern = [0, 1000, 1000];
      Vibration.vibrate(vibrationPattern, true);

      return () => {
        pulse.stop();
        Vibration.cancel();
      };
    }
  }, [visible, pulseAnim]);

  if (!visible || !callData) return null;

  return (
    <Modal
      visible={visible}
      animationType="slide"
      presentationStyle="fullScreen"
      statusBarTranslucent
    >
      <View style={styles.container}>
        <View style={styles.header}>
          <Text style={styles.incomingText}>
            Incoming {callData.type === 'video' ? 'Video' : 'Voice'} Call
          </Text>
        </View>

        <View style={styles.callerInfo}>
          <Animated.View style={[
            styles.avatarContainer,
            { transform: [{ scale: pulseAnim }] }
          ]}>
            {callData.caller.profile_photo_path ? (
              <Image
                source={{ uri: callData.caller.profile_photo_path }}
                style={styles.avatar}
              />
            ) : (
              <View style={[styles.avatar, styles.avatarPlaceholder]}>
                <Icon name="person" size={60} color="#fff" />
              </View>
            )}
          </Animated.View>

          <Text style={styles.callerName}>{callData.caller.name}</Text>
          <Text style={styles.callType}>
            {callData.type === 'video' ? 'Video Call' : 'Voice Call'}
          </Text>
        </View>

        <View style={styles.actions}>
          <TouchableOpacity
            style={[styles.actionButton, styles.rejectButton]}
            onPress={() => onReject(callData.callId)}
          >
            <Icon name="call-end" size={32} color="#fff" />
          </TouchableOpacity>

          <TouchableOpacity
            style={[styles.actionButton, styles.acceptButton]}
            onPress={() => onAccept(callData.callId)}
          >
            <Icon 
              name={callData.type === 'video' ? 'videocam' : 'call'} 
              size={32} 
              color="#fff" 
            />
          </TouchableOpacity>
        </View>

        <View style={styles.additionalActions}>
          <TouchableOpacity style={styles.messageButton}>
            <Icon name="message" size={24} color="#fff" />
            <Text style={styles.messageText}>Message</Text>
          </TouchableOpacity>
        </View>
      </View>
    </Modal>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#1a1a1a',
    justifyContent: 'space-between',
    paddingVertical: 60,
  },
  header: {
    alignItems: 'center',
    paddingTop: 40,
  },
  incomingText: {
    fontSize: 18,
    color: '#fff',
    opacity: 0.8,
  },
  callerInfo: {
    alignItems: 'center',
    flex: 1,
    justifyContent: 'center',
  },
  avatarContainer: {
    marginBottom: 24,
  },
  avatar: {
    width: 120,
    height: 120,
    borderRadius: 60,
  },
  avatarPlaceholder: {
    backgroundColor: '#4CAF50',
    justifyContent: 'center',
    alignItems: 'center',
  },
  callerName: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#fff',
    marginBottom: 8,
  },
  callType: {
    fontSize: 16,
    color: '#fff',
    opacity: 0.8,
  },
  actions: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    paddingHorizontal: 60,
    marginBottom: 40,
  },
  actionButton: {
    width: 70,
    height: 70,
    borderRadius: 35,
    justifyContent: 'center',
    alignItems: 'center',
    elevation: 4,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 4,
  },
  acceptButton: {
    backgroundColor: '#4CAF50',
  },
  rejectButton: {
    backgroundColor: '#F44336',
  },
  additionalActions: {
    alignItems: 'center',
  },
  messageButton: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 10,
  },
  messageText: {
    color: '#fff',
    marginLeft: 8,
    fontSize: 16,
  },
});

export default IncomingCallModal;
```

### 3. Call History Screen

```tsx
// Call History Screen Component
import React, { useEffect, useState, useCallback } from 'react';
import {
  View,
  Text,
  FlatList,
  TouchableOpacity,
  StyleSheet,
  RefreshControl,
  ActivityIndicator,
} from 'react-native';
import Icon from 'react-native-vector-icons/MaterialIcons';

interface CallHistoryScreenProps {
  navigation: any;
  route: {
    params: {
      chatId: number;
    };
  };
}

const CallHistoryScreen: React.FC<CallHistoryScreenProps> = ({
  navigation,
  route
}) => {
  const { chatId } = route.params;
  const [callMessages, setCallMessages] = useState<CallMessage[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [pagination, setPagination] = useState<any>(null);
  const [filters, setFilters] = useState({
    callType: null as 'video' | 'voice' | null,
    callStatus: null as 'completed' | 'missed' | 'rejected' | null,
  });

  const chatService = new ChatService();
  const callService = new CallService();

  const loadCallMessages = useCallback(async (page: number = 1, isRefresh: boolean = false) => {
    try {
      if (isRefresh) setRefreshing(true);
      else if (page === 1) setLoading(true);

      const result = await chatService.getCallMessages(chatId, {
        page,
        perPage: 20,
        callType: filters.callType || undefined,
        callStatus: filters.callStatus || undefined,
      });

      if (page === 1 || isRefresh) {
        setCallMessages(result.callMessages);
      } else {
        setCallMessages(prev => [...prev, ...result.callMessages]);
      }

      setPagination(result.pagination);
    } catch (error) {
      console.error('Failed to load call messages:', error);
      // Show error toast
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [chatId, filters]);

  useEffect(() => {
    loadCallMessages();
  }, [loadCallMessages]);

  const handleCallBack = async (callId: number, type: 'video' | 'voice') => {
    try {
      // Get other participant info from the call message
      const callMessage = callMessages.find(msg => msg.callDetails.callId === callId);
      if (!callMessage?.callDetails.otherParticipant) return;

      const recipientId = callMessage.callDetails.otherParticipant.id;
      
      // Initiate new call
      const callData = await callService.initiateCall(recipientId, type);
      
      // Navigate to call screen
      navigation.navigate('CallScreen', {
        callId: callData.callId,
        meetingId: callData.meetingId,
        token: callData.token,
        type: callData.type,
        isInitiator: true,
      });
    } catch (error) {
      console.error('Failed to initiate callback:', error);
      // Show error toast
    }
  };

  const handleViewDetails = (callId: number) => {
    navigation.navigate('CallDetails', { callId });
  };

  const renderCallMessage = ({ item }: { item: CallMessage }) => (
    <CallMessageComponent
      message={item}
      onCallBack={handleCallBack}
      onViewDetails={handleViewDetails}
    />
  );

  const renderFilter = (
    label: string,
    value: string | null,
    options: { label: string; value: string | null }[],
    onSelect: (value: string | null) => void
  ) => (
    <View style={styles.filterContainer}>
      <Text style={styles.filterLabel}>{label}:</Text>
      <View style={styles.filterOptions}>
        {options.map(option => (
          <TouchableOpacity
            key={option.value || 'all'}
            style={[
              styles.filterOption,
              value === option.value && styles.filterOptionActive
            ]}
            onPress={() => onSelect(option.value)}
          >
            <Text style={[
              styles.filterOptionText,
              value === option.value && styles.filterOptionTextActive
            ]}>
              {option.label}
            </Text>
          </TouchableOpacity>
        ))}
      </View>
    </View>
  );

  const loadMore = () => {
    if (pagination && pagination.current_page < pagination.last_page && !loading) {
      loadCallMessages(pagination.current_page + 1);
    }
  };

  if (loading && callMessages.length === 0) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#2196F3" />
        <Text style={styles.loadingText}>Loading call history...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
        >
          <Icon name="arrow-back" size={24} color="#333" />
        </TouchableOpacity>
        <Text style={styles.title}>Call History</Text>
      </View>

      <View style={styles.filters}>
        {renderFilter(
          'Type',
          filters.callType,
          [
            { label: 'All', value: null },
            { label: 'Video', value: 'video' },
            { label: 'Voice', value: 'voice' },
          ],
          (value) => setFilters(prev => ({ ...prev, callType: value as any }))
        )}

        {renderFilter(
          'Status',
          filters.callStatus,
          [
            { label: 'All', value: null },
            { label: 'Completed', value: 'completed' },
            { label: 'Missed', value: 'missed' },
            { label: 'Rejected', value: 'rejected' },
          ],
          (value) => setFilters(prev => ({ ...prev, callStatus: value as any }))
        )}
      </View>

      <FlatList
        data={callMessages}
        renderItem={renderCallMessage}
        keyExtractor={(item) => item.id.toString()}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={() => loadCallMessages(1, true)}
            tintColor="#2196F3"
          />
        }
        onEndReached={loadMore}
        onEndReachedThreshold={0.5}
        ListFooterComponent={
          loading && callMessages.length > 0 ? (
            <View style={styles.loadMoreContainer}>
              <ActivityIndicator size="small" color="#2196F3" />
            </View>
          ) : null
        }
        ListEmptyComponent={
          <View style={styles.emptyContainer}>
            <Icon name="phone" size={48} color="#ccc" />
            <Text style={styles.emptyText}>No call history found</Text>
            <Text style={styles.emptySubtext}>
              Your call history will appear here
            </Text>
          </View>
        }
        contentContainerStyle={
          callMessages.length === 0 ? styles.emptyList : undefined
        }
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 16,
    fontSize: 16,
    color: '#666',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  backButton: {
    marginRight: 16,
    padding: 8,
  },
  title: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  filters: {
    padding: 16,
    backgroundColor: '#f8f8f8',
    borderBottomWidth: 1,
    borderBottomColor: '#e0e0e0',
  },
  filterContainer: {
    marginBottom: 12,
  },
  filterLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#333',
    marginBottom: 8,
  },
  filterOptions: {
    flexDirection: 'row',
    flexWrap: 'wrap',
  },
  filterOption: {
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
    backgroundColor: '#e0e0e0',
    marginRight: 8,
    marginBottom: 8,
  },
  filterOptionActive: {
    backgroundColor: '#2196F3',
  },
  filterOptionText: {
    fontSize: 12,
    color: '#666',
  },
  filterOptionTextActive: {
    color: '#fff',
  },
  loadMoreContainer: {
    padding: 20,
    alignItems: 'center',
  },
  emptyContainer: {
    alignItems: 'center',
    justifyContent: 'center',
    padding: 40,
  },
  emptyList: {
    flex: 1,
  },
  emptyText: {
    fontSize: 18,
    fontWeight: '600',
    color: '#666',
    marginTop: 16,
  },
  emptySubtext: {
    fontSize: 14,
    color: '#999',
    marginTop: 8,
    textAlign: 'center',
  },
});

export default CallHistoryScreen;
```

## 🚀 State Management Integration

### Redux/Context Integration

```typescript
// Call State Management
interface CallState {
  activeCall: {
    callId: number;
    type: 'video' | 'voice';
    status: 'initiated' | 'ongoing' | 'ended';
    participants: any[];
    startTime?: Date;
  } | null;
  incomingCall: {
    callId: number;
    type: 'video' | 'voice';
    caller: any;
  } | null;
  callHistory: CallMessage[];
  loading: boolean;
  error: string | null;
}

// Call Actions
const callActions = {
  // Initiate call and automatically create message
  initiateCall: (recipientId: number, callType: 'video' | 'voice') => async (dispatch: any) => {
    try {
      dispatch({ type: 'CALL_INITIATE_START' });
      
      const callService = new CallService();
      const callData = await callService.initiateCall(recipientId, callType);
      
      dispatch({
        type: 'CALL_INITIATE_SUCCESS',
        payload: {
          callId: callData.callId,
          type: callData.type,
          messageId: callData.messageId, // Auto-created message
          participants: [callData.caller, callData.receiver]
        }
      });

      // Auto-refresh chat to show new call message
      dispatch(chatActions.refreshChat(callData.chatId));
      
      return callData;
    } catch (error) {
      dispatch({
        type: 'CALL_INITIATE_FAILURE',
        payload: error.message
      });
      throw error;
    }
  },

  // Join call and update message
  joinCall: (callId: number) => async (dispatch: any) => {
    try {
      dispatch({ type: 'CALL_JOIN_START' });
      
      const callService = new CallService();
      const result = await callService.joinCall(callId);
      
      dispatch({
        type: 'CALL_JOIN_SUCCESS',
        payload: {
          callId: result.callId,
          messageId: result.messageId
        }
      });

      return result;
    } catch (error) {
      dispatch({
        type: 'CALL_JOIN_FAILURE',
        payload: error.message
      });
      throw error;
    }
  },

  // End call and update message with duration
  endCall: (callId: number) => async (dispatch: any) => {
    try {
      dispatch({ type: 'CALL_END_START' });
      
      const callService = new CallService();
      const result = await callService.endCall(callId);
      
      dispatch({
        type: 'CALL_END_SUCCESS',
        payload: {
          callId: result.callId,
          duration: result.duration,
          messageId: result.messageId
        }
      });

      return result;
    } catch (error) {
      dispatch({
        type: 'CALL_END_FAILURE',
        payload: error.message
      });
      throw error;
    }
  },

  // Set incoming call from WebSocket
  setIncomingCall: (callData: any) => ({
    type: 'CALL_INCOMING',
    payload: callData
  }),

  // Clear incoming call
  clearIncomingCall: () => ({
    type: 'CALL_INCOMING_CLEAR'
  }),

  // Update call status from WebSocket
  updateCallStatus: (callId: number, status: string, data?: any) => ({
    type: 'CALL_STATUS_UPDATE',
    payload: { callId, status, data }
  })
};

// Chat Actions Enhancement
const chatActions = {
  // Load chat with enhanced call data
  loadChat: (chatId: number, includeCallData: boolean = true) => async (dispatch: any) => {
    try {
      dispatch({ type: 'CHAT_LOAD_START' });
      
      const chatService = new ChatService();
      const chatData = await chatService.getChatWithCallData(chatId, 1, includeCallData);
      
      dispatch({
        type: 'CHAT_LOAD_SUCCESS',
        payload: chatData
      });

      return chatData;
    } catch (error) {
      dispatch({
        type: 'CHAT_LOAD_FAILURE',
        payload: error.message
      });
      throw error;
    }
  },

  // Refresh chat (called when call messages are auto-created/updated)
  refreshChat: (chatId: number) => async (dispatch: any) => {
    // Silent refresh without loading states
    try {
      const chatService = new ChatService();
      const chatData = await chatService.getChatWithCallData(chatId, 1, true);
      
      dispatch({
        type: 'CHAT_REFRESH',
        payload: chatData
      });
    } catch (error) {
      console.error('Failed to refresh chat:', error);
    }
  }
};
```

## 📋 Implementation Checklist

### Phase 1: Basic Integration
- [ ] Implement CallService with automatic message creation
- [ ] Create enhanced ChatService with call data support
- [ ] Set up WebSocket service for real-time updates
- [ ] Create CallMessageComponent for displaying call messages
- [ ] Implement IncomingCallModal for incoming calls

### Phase 2: Advanced Features
- [ ] Create CallHistoryScreen with filtering
- [ ] Implement call analytics and statistics
- [ ] Add call-back functionality from chat
- [ ] Create call details screen
- [ ] Implement offline handling for missed calls

### Phase 3: User Experience
- [ ] Add haptic feedback for call events
- [ ] Implement custom ring tones
- [ ] Add call quality indicators
- [ ] Create call recording features (if needed)
- [ ] Implement call transfer functionality

### Phase 4: Performance & Polish
- [ ] Optimize WebSocket reconnection logic
- [ ] Implement proper error handling and retry mechanisms
- [ ] Add comprehensive logging for debugging
- [ ] Optimize list performance for large call histories
- [ ] Add accessibility features

## 🎯 Key Implementation Notes

### 1. **Automatic Message Handling**
- Call messages are automatically created - DO NOT manually create them
- Messages automatically update when call status changes
- Always listen for WebSocket events to refresh chat UI

### 2. **Real-time Updates**
- Use WebSocket for immediate call status updates
- Auto-refresh chat when call events occur
- Handle offline scenarios gracefully

### 3. **Error Handling**
- Wrap all API calls in try-catch blocks
- Provide user-friendly error messages
- Implement retry mechanisms for network failures

### 4. **Performance**
- Use pagination for call history
- Implement proper list optimization (FlatList/VirtualizedList)
- Cache frequently accessed data

### 5. **User Experience**
- Show clear call status indicators
- Provide immediate feedback for user actions
- Handle edge cases (network issues, app backgrounding)

This implementation guide provides a complete foundation for integrating the optimized call-message system into your mobile app with automatic message creation, real-time updates, and enhanced user experience. 