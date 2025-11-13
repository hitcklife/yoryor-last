# YorYor Mobile App Integration Guide

## React Native (Expo) Integration

### API Configuration

#### Base Setup
```javascript
// config/api.js
const API_BASE_URL = __DEV__ 
  ? 'http://localhost:8000/api/v1'
  : 'https://api.yoryor.com/api/v1';

const API_CONFIG = {
  baseURL: API_BASE_URL,
  timeout: 30000,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
};
```

#### Axios Instance
```javascript
// services/api.js
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

const api = axios.create(API_CONFIG);

// Request interceptor for auth
api.interceptors.request.use(async (config) => {
  const token = await AsyncStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor for error handling
api.interceptors.response.use(
  response => response,
  async error => {
    if (error.response?.status === 401) {
      await AsyncStorage.removeItem('auth_token');
      // Navigate to login
    }
    return Promise.reject(error);
  }
);
```

### Authentication Flow

#### Login Implementation
```javascript
// screens/LoginScreen.js
const login = async (credentials) => {
  try {
    const response = await api.post('/auth/authenticate', {
      email: credentials.email,
      password: credentials.password,
      device_name: Device.deviceName,
    });
    
    if (response.data.success) {
      await AsyncStorage.setItem('auth_token', response.data.data.token);
      await AsyncStorage.setItem('user', JSON.stringify(response.data.data.user));
      
      // Register device token for push notifications
      await registerDeviceToken();
      
      navigation.navigate('Home');
    }
  } catch (error) {
    handleAuthError(error);
  }
};
```

#### Token Management
```javascript
// utils/tokenManager.js
export const TokenManager = {
  getToken: async () => {
    return await AsyncStorage.getItem('auth_token');
  },
  
  setToken: async (token) => {
    await AsyncStorage.setItem('auth_token', token);
  },
  
  removeToken: async () => {
    await AsyncStorage.removeItem('auth_token');
  },
  
  isAuthenticated: async () => {
    const token = await AsyncStorage.getItem('auth_token');
    return !!token;
  }
};
```

### Real-time Features (WebSocket)

#### Pusher Integration
```javascript
// services/pusher.js
import Pusher from 'pusher-js/react-native';
import Echo from 'laravel-echo';

const setupEcho = async () => {
  const token = await TokenManager.getToken();
  
  window.Echo = new Echo({
    broadcaster: 'pusher',
    key: PUSHER_APP_KEY,
    cluster: PUSHER_CLUSTER,
    forceTLS: true,
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
      },
      endpoint: `${API_BASE_URL}/broadcasting/auth`,
    },
  });
};
```

#### Chat Real-time Updates
```javascript
// hooks/useChat.js
export const useChat = (chatId) => {
  const [messages, setMessages] = useState([]);
  const [typingUsers, setTypingUsers] = useState([]);
  
  useEffect(() => {
    // Join chat channel
    const channel = window.Echo.private(`chat.${chatId}`);
    
    // Listen for new messages
    channel.listen('.new-message', (e) => {
      setMessages(prev => [...prev, e.message]);
      // Show notification if app is in background
      if (AppState.currentState !== 'active') {
        showMessageNotification(e.message);
      }
    });
    
    // Listen for message edits
    channel.listen('.message-edited', (e) => {
      setMessages(prev => 
        prev.map(msg => msg.id === e.message.id ? e.message : msg)
      );
    });
    
    // Listen for typing indicators
    channel.listenForWhisper('typing', (e) => {
      setTypingUsers(prev => [...prev, e.user]);
      setTimeout(() => {
        setTypingUsers(prev => prev.filter(u => u.id !== e.user.id));
      }, 3000);
    });
    
    return () => {
      window.Echo.leave(`chat.${chatId}`);
    };
  }, [chatId]);
  
  return { messages, typingUsers };
};
```

### Push Notifications (Expo)

#### Setup
```javascript
// services/notifications.js
import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';

Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: true,
  }),
});

export const registerForPushNotifications = async () => {
  if (!Device.isDevice) return null;
  
  const { status: existingStatus } = await Notifications.getPermissionsAsync();
  let finalStatus = existingStatus;
  
  if (existingStatus !== 'granted') {
    const { status } = await Notifications.requestPermissionsAsync();
    finalStatus = status;
  }
  
  if (finalStatus !== 'granted') {
    return null;
  }
  
  const token = (await Notifications.getExpoPushTokenAsync()).data;
  
  // Register token with backend
  await api.post('/device-tokens', {
    token: token,
    platform: Platform.OS,
    device_id: Device.deviceId,
  });
  
  return token;
};
```

#### Notification Handling
```javascript
// App.js
useEffect(() => {
  // Handle notification received while app is foregrounded
  const subscription1 = Notifications.addNotificationReceivedListener(notification => {
    console.log('Notification received:', notification);
  });
  
  // Handle notification response (user tapped notification)
  const subscription2 = Notifications.addNotificationResponseReceivedListener(response => {
    const data = response.notification.request.content.data;
    
    switch (data.type) {
      case 'new_message':
        navigation.navigate('Chat', { chatId: data.chat_id });
        break;
      case 'new_match':
        navigation.navigate('Matches');
        break;
      case 'video_call':
        navigation.navigate('VideoCall', { callId: data.call_id });
        break;
    }
  });
  
  return () => {
    subscription1.remove();
    subscription2.remove();
  };
}, []);
```

### Media Upload

#### Image Upload
```javascript
// services/mediaUpload.js
import * as ImagePicker from 'expo-image-picker';
import * as FileSystem from 'expo-file-system';

export const uploadImage = async (uri, type = 'profile') => {
  const formData = new FormData();
  
  formData.append('image', {
    uri: uri,
    type: 'image/jpeg',
    name: 'photo.jpg',
  });
  formData.append('type', type);
  
  const response = await api.post('/photos/upload', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
    onUploadProgress: (progressEvent) => {
      const progress = progressEvent.loaded / progressEvent.total;
      console.log('Upload progress:', progress);
    },
  });
  
  return response.data;
};
```

#### Video Upload with Compression
```javascript
// services/videoUpload.js
import { Video } from 'expo-av';
import * as VideoThumbnails from 'expo-video-thumbnails';

export const uploadVideo = async (videoUri) => {
  // Generate thumbnail
  const { uri: thumbnailUri } = await VideoThumbnails.getThumbnailAsync(videoUri, {
    time: 1000,
  });
  
  // Get video info
  const videoInfo = await FileSystem.getInfoAsync(videoUri);
  
  // Check file size (limit to 100MB)
  if (videoInfo.size > 100 * 1024 * 1024) {
    throw new Error('Video file too large');
  }
  
  const formData = new FormData();
  formData.append('video', {
    uri: videoUri,
    type: 'video/mp4',
    name: 'video.mp4',
  });
  formData.append('thumbnail', {
    uri: thumbnailUri,
    type: 'image/jpeg',
    name: 'thumbnail.jpg',
  });
  
  return await api.post('/media/upload', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
    timeout: 300000, // 5 minutes for video upload
  });
};
```

### Video Calling Integration

#### Video SDK Setup
```javascript
// services/videoCall.js
import { VideoSDK } from '@videosdk.live/react-native-sdk';

export const initializeVideoCall = async (callData) => {
  // Get token from backend
  const { data } = await api.post('/video-call/token', {
    meeting_id: callData.meetingId,
  });
  
  // Initialize Video SDK
  const meeting = VideoSDK.initMeeting({
    meetingId: callData.meetingId,
    participantId: data.participantId,
    token: data.token,
    name: data.userName,
    micEnabled: true,
    webcamEnabled: true,
  });
  
  return meeting;
};
```

#### Call Screen Implementation
```javascript
// screens/VideoCallScreen.js
const VideoCallScreen = ({ route }) => {
  const { callId, isIncoming } = route.params;
  const [meeting, setMeeting] = useState(null);
  
  useEffect(() => {
    if (isIncoming) {
      // Join existing call
      joinCall();
    } else {
      // Initiate new call
      startCall();
    }
  }, []);
  
  const startCall = async () => {
    const { data } = await api.post('/video-call/initiate', {
      receiver_id: route.params.receiverId,
    });
    
    const meeting = await initializeVideoCall(data);
    setMeeting(meeting);
  };
  
  const joinCall = async () => {
    const { data } = await api.post(`/video-call/${callId}/join`);
    const meeting = await initializeVideoCall(data);
    setMeeting(meeting);
  };
  
  // Render video UI
  return (
    <MeetingProvider meeting={meeting}>
      <MeetingView />
    </MeetingProvider>
  );
};
```

### Offline Support

#### Data Caching
```javascript
// services/cache.js
import AsyncStorage from '@react-native-async-storage/async-storage';

export const CacheService = {
  set: async (key, data, expiryMinutes = 60) => {
    const item = {
      data: data,
      expiry: new Date().getTime() + (expiryMinutes * 60 * 1000),
    };
    await AsyncStorage.setItem(key, JSON.stringify(item));
  },
  
  get: async (key) => {
    const itemStr = await AsyncStorage.getItem(key);
    if (!itemStr) return null;
    
    const item = JSON.parse(itemStr);
    if (new Date().getTime() > item.expiry) {
      await AsyncStorage.removeItem(key);
      return null;
    }
    
    return item.data;
  },
};
```

#### Offline Queue
```javascript
// services/offlineQueue.js
import NetInfo from '@react-native-community/netinfo';

class OfflineQueue {
  constructor() {
    this.queue = [];
    this.isProcessing = false;
    
    // Listen for network changes
    NetInfo.addEventListener(state => {
      if (state.isConnected && !this.isProcessing) {
        this.processQueue();
      }
    });
  }
  
  async add(request) {
    this.queue.push({
      id: Date.now(),
      request: request,
      timestamp: new Date(),
    });
    
    await this.saveQueue();
    
    const netInfo = await NetInfo.fetch();
    if (netInfo.isConnected) {
      this.processQueue();
    }
  }
  
  async processQueue() {
    if (this.isProcessing || this.queue.length === 0) return;
    
    this.isProcessing = true;
    
    while (this.queue.length > 0) {
      const item = this.queue[0];
      
      try {
        await api(item.request);
        this.queue.shift();
        await this.saveQueue();
      } catch (error) {
        if (error.response?.status >= 400 && error.response?.status < 500) {
          // Client error, remove from queue
          this.queue.shift();
          await this.saveQueue();
        } else {
          // Server error, retry later
          break;
        }
      }
    }
    
    this.isProcessing = false;
  }
  
  async saveQueue() {
    await AsyncStorage.setItem('offline_queue', JSON.stringify(this.queue));
  }
  
  async loadQueue() {
    const data = await AsyncStorage.getItem('offline_queue');
    if (data) {
      this.queue = JSON.parse(data);
    }
  }
}

export default new OfflineQueue();
```

### Performance Optimization

#### Image Caching
```javascript
// components/CachedImage.js
import FastImage from 'react-native-fast-image';

export const CachedImage = ({ source, ...props }) => {
  return (
    <FastImage
      {...props}
      source={{
        uri: source.uri,
        priority: FastImage.priority.normal,
        cache: FastImage.cacheControl.immutable,
      }}
      resizeMode={FastImage.resizeMode.cover}
    />
  );
};
```

#### List Optimization
```javascript
// components/OptimizedList.js
import { FlashList } from '@shopify/flash-list';

export const UserList = ({ users, onEndReached }) => {
  const renderUser = useCallback(({ item }) => (
    <UserCard user={item} />
  ), []);
  
  return (
    <FlashList
      data={users}
      renderItem={renderUser}
      estimatedItemSize={100}
      onEndReached={onEndReached}
      onEndReachedThreshold={0.5}
      keyExtractor={item => item.id.toString()}
      removeClippedSubviews={true}
      maxToRenderPerBatch={10}
      windowSize={10}
    />
  );
};
```

### Error Handling

#### Global Error Handler
```javascript
// utils/errorHandler.js
export const handleApiError = (error) => {
  if (error.response) {
    // Server responded with error
    const { status, data } = error.response;
    
    switch (status) {
      case 401:
        // Unauthorized - redirect to login
        break;
      case 422:
        // Validation error
        return data.errors;
      case 429:
        // Rate limited
        Alert.alert('Too many requests', 'Please try again later');
        break;
      case 500:
        // Server error
        Alert.alert('Server Error', 'Something went wrong. Please try again.');
        break;
    }
  } else if (error.request) {
    // No response received
    Alert.alert('Network Error', 'Please check your internet connection');
  }
  
  throw error;
};
```

### Security Best Practices

1. **Token Storage**: Use secure storage for sensitive data
```javascript
import * as SecureStore from 'expo-secure-store';

await SecureStore.setItemAsync('auth_token', token);
```

2. **Certificate Pinning**: Implement for production
```javascript
// Implement certificate pinning for API calls
```

3. **Biometric Authentication**: Add for app access
```javascript
import * as LocalAuthentication from 'expo-local-authentication';

const authenticate = async () => {
  const result = await LocalAuthentication.authenticateAsync({
    promptMessage: 'Authenticate to access YorYor',
  });
  return result.success;
};
```