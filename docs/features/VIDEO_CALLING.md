# Video & Voice Calling Documentation

## Overview

YorYor implements a robust video and voice calling system using VideoSDK.live as the primary provider and Agora RTC as a backup. The system supports HD video quality, adaptive bitrate streaming, and comprehensive call management features integrated seamlessly with the chat system.

---

## Video Calling Architecture

### Dual Provider System

**Primary Provider: VideoSDK.live**
- Advanced features and better pricing
- HD video quality (up to 1080p)
- Lower latency
- Better bandwidth optimization
- Active development and support

**Backup Provider: Agora RTC**
- Industry-standard reliability
- Automatic fallback if VideoSDK fails
- Global infrastructure
- Proven scalability

**Fallback Logic:**
```php
public function initiateCall(int $chatId, string $type): array
{
    try {
        // Try VideoSDK first
        return app(VideoSDKService::class)->initiateCall($chatId, $type);
    } catch (\Exception $e) {
        // Fall back to Agora
        Log::warning('VideoSDK failed, falling back to Agora', [
            'error' => $e->getMessage(),
            'chat_id' => $chatId,
        ]);

        return app(AgoraService::class)->initiateCall($chatId, $type);
    }
}
```

### System Architecture

```
User A initiates call
    ↓
Laravel API creates meeting
    ↓
VideoSDK/Agora generates token
    ↓
Token sent to both users
    ↓
WebRTC connection established
    ↓
Call in progress (peer-to-peer)
    ↓
Call ended (stats recorded)
```

---

## VideoSDK Integration

### Configuration

**Environment Variables:**
```env
VIDEOSDK_API_KEY=your_api_key_here
VIDEOSDK_SECRET_KEY=your_secret_key_here
VIDEOSDK_API_ENDPOINT=https://api.videosdk.live/v2
```

**Service Configuration:**
```php
// config/services.php
'videosdk' => [
    'api_key' => env('VIDEOSDK_API_KEY'),
    'secret_key' => env('VIDEOSDK_SECRET_KEY'),
    'api_endpoint' => env('VIDEOSDK_API_ENDPOINT', 'https://api.videosdk.live/v2'),
    'token_expiry' => 3600,  // 1 hour
],
```

### VideoSDK Service

**app/Services/VideoSDKService.php:**
```php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Firebase\JWT\JWT;

class VideoSDKService
{
    private string $apiKey;
    private string $secretKey;
    private string $apiEndpoint;

    public function __construct()
    {
        $this->apiKey = config('services.videosdk.api_key');
        $this->secretKey = config('services.videosdk.secret_key');
        $this->apiEndpoint = config('services.videosdk.api_endpoint');
    }

    /**
     * Generate VideoSDK authentication token
     */
    public function generateToken(array $permissions = ['allow_join']): string
    {
        $payload = [
            'apikey' => $this->apiKey,
            'permissions' => $permissions,
            'version' => 2,
            'iat' => time(),
            'exp' => time() + config('services.videosdk.token_expiry'),
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    /**
     * Create a new meeting room
     */
    public function createMeeting(): array
    {
        $token = $this->generateToken(['allow_join', 'allow_mod']);

        $response = Http::withHeaders([
            'Authorization' => $token,
            'Content-Type' => 'application/json',
        ])->post("{$this->apiEndpoint}/rooms");

        if ($response->failed()) {
            throw new \Exception('Failed to create VideoSDK meeting: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Get meeting details
     */
    public function getMeeting(string $meetingId): array
    {
        $token = $this->generateToken();

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->get("{$this->apiEndpoint}/rooms/{$meetingId}");

        if ($response->failed()) {
            throw new \Exception('Failed to get meeting details: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * End an active meeting
     */
    public function endMeeting(string $meetingId): bool
    {
        $token = $this->generateToken(['allow_mod']);

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->post("{$this->apiEndpoint}/rooms/{$meetingId}/end");

        return $response->successful();
    }

    /**
     * Get meeting participants
     */
    public function getParticipants(string $meetingId): array
    {
        $token = $this->generateToken();

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->get("{$this->apiEndpoint}/rooms/{$meetingId}/participants");

        if ($response->failed()) {
            return [];
        }

        return $response->json();
    }
}
```

### Client-Side Integration

**resources/js/videosdk.js:**
```javascript
import { VideoSDK } from '@videosdk.live/js-sdk';

class VideoCallManager {
    constructor() {
        this.meeting = null;
        this.localStream = null;
        this.remoteStream = null;
    }

    /**
     * Initialize VideoSDK meeting
     */
    async initializeMeeting(meetingId, token, participantName) {
        try {
            // Initialize meeting
            this.meeting = VideoSDK.initMeeting({
                meetingId: meetingId,
                name: participantName,
                micEnabled: true,
                webcamEnabled: true,
                token: token,
            });

            // Event listeners
            this.setupEventListeners();

            // Join meeting
            this.meeting.join();

            return this.meeting;
        } catch (error) {
            console.error('Failed to initialize meeting:', error);
            throw error;
        }
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Meeting joined
        this.meeting.on('meeting-joined', () => {
            console.log('Meeting joined successfully');
            this.onMeetingJoined();
        });

        // Participant joined
        this.meeting.on('participant-joined', (participant) => {
            console.log('Participant joined:', participant.displayName);
            this.onParticipantJoined(participant);
        });

        // Participant left
        this.meeting.on('participant-left', (participant) => {
            console.log('Participant left:', participant.displayName);
            this.onParticipantLeft(participant);
        });

        // Meeting ended
        this.meeting.on('meeting-left', () => {
            console.log('Meeting ended');
            this.onMeetingLeft();
        });

        // Stream enabled/disabled
        this.meeting.on('stream-enabled', (stream) => {
            this.handleStreamEnabled(stream);
        });

        this.meeting.on('stream-disabled', (stream) => {
            this.handleStreamDisabled(stream);
        });
    }

    /**
     * Enable/disable webcam
     */
    toggleWebcam() {
        if (this.meeting.webcam.enabled) {
            this.meeting.disableWebcam();
        } else {
            this.meeting.enableWebcam();
        }
    }

    /**
     * Enable/disable microphone
     */
    toggleMic() {
        if (this.meeting.mic.enabled) {
            this.meeting.disableMic();
        } else {
            this.meeting.enableMic();
        }
    }

    /**
     * Switch camera (front/back)
     */
    async switchCamera() {
        const cameraStream = await this.meeting.getCameras();
        const nextCamera = cameraStream[1] || cameraStream[0];
        this.meeting.changeWebcam(nextCamera.deviceId);
    }

    /**
     * Leave meeting
     */
    leaveMeeting() {
        this.meeting?.leave();
        this.cleanup();
    }

    /**
     * End meeting for all
     */
    endMeeting() {
        this.meeting?.end();
        this.cleanup();
    }

    /**
     * Cleanup resources
     */
    cleanup() {
        this.localStream = null;
        this.remoteStream = null;
        this.meeting = null;
    }

    /**
     * Handle stream enabled
     */
    handleStreamEnabled(stream) {
        if (stream.kind === 'video') {
            const videoElement = document.getElementById('remote-video');
            if (videoElement) {
                videoElement.srcObject = stream;
                videoElement.play();
            }
        } else if (stream.kind === 'audio') {
            const audioElement = document.getElementById('remote-audio');
            if (audioElement) {
                audioElement.srcObject = stream;
                audioElement.play();
            }
        }
    }

    /**
     * Event callbacks (override these)
     */
    onMeetingJoined() {}
    onParticipantJoined(participant) {}
    onParticipantLeft(participant) {}
    onMeetingLeft() {}
}

export default VideoCallManager;
```

---

## Call Flow

### 1. Call Initiation

**User Flow:**
1. User clicks "Video Call" or "Voice Call" button in chat
2. API creates meeting and generates tokens
3. Call notification sent to recipient via WebSocket
4. Recipient sees incoming call interface
5. Recipient can accept or decline

**Backend Implementation:**
```php
public function initiateCall(Request $request, int $chatId): JsonResponse
{
    $validated = $request->validate([
        'type' => 'required|in:video,audio',
    ]);

    DB::beginTransaction();
    try {
        // Create meeting via VideoSDK
        $meeting = app(VideoSDKService::class)->createMeeting();

        // Generate tokens for both participants
        $token = app(VideoSDKService::class)->generateToken();

        // Get chat participants
        $chat = Chat::with('users')->findOrFail($chatId);
        $participants = $chat->users;
        $caller = auth()->user();
        $recipient = $participants->where('id', '!=', $caller->id)->first();

        // Create call record
        $call = Call::create([
            'meeting_id' => $meeting['roomId'],
            'type' => $validated['type'],
            'caller_id' => $caller->id,
            'receiver_id' => $recipient->id,
            'status' => 'ringing',
            'provider' => 'videosdk',
        ]);

        // Create call message in chat
        Message::create([
            'chat_id' => $chatId,
            'user_id' => $caller->id,
            'type' => 'call',
            'content' => "Call started",
            'metadata' => json_encode(['call_id' => $call->id]),
        ]);

        // Broadcast call notification
        event(new CallInitiatedEvent($call, $recipient));

        DB::commit();

        return response()->json([
            'status' => 'success',
            'call' => [
                'id' => $call->id,
                'meeting_id' => $meeting['roomId'],
                'token' => $token,
                'type' => $validated['type'],
            ],
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

**API Endpoint:**
```http
POST /api/v1/video-call/initiate
Content-Type: application/json

{
  "chat_id": 123,
  "type": "video"  // or "audio"
}
```

### 2. Call Acceptance

**Recipient receives WebSocket event:**
```javascript
Echo.private(`user.${userId}`)
    .listen('CallInitiatedEvent', (event) => {
        // Show incoming call UI
        showIncomingCall({
            callId: event.call.id,
            callerName: event.caller.name,
            callerPhoto: event.caller.photo,
            type: event.call.type,
        });
    });
```

**Accept Call:**
```php
public function acceptCall(int $callId): JsonResponse
{
    $call = Call::findOrFail($callId);

    // Authorization
    if ($call->receiver_id !== auth()->id()) {
        abort(403, 'Unauthorized');
    }

    // Generate token for receiver
    $token = app(VideoSDKService::class)->generateToken();

    // Update call status
    $call->update([
        'status' => 'active',
        'started_at' => now(),
    ]);

    // Broadcast call accepted
    event(new CallAcceptedEvent($call));

    return response()->json([
        'status' => 'success',
        'meeting_id' => $call->meeting_id,
        'token' => $token,
    ]);
}
```

**API Endpoint:**
```http
POST /api/v1/video-call/{callId}/accept
```

### 3. Call Rejection

```php
public function rejectCall(int $callId): JsonResponse
{
    $call = Call::findOrFail($callId);

    // Authorization
    if ($call->receiver_id !== auth()->id()) {
        abort(403, 'Unauthorized');
    }

    // Update call status
    $call->update([
        'status' => 'declined',
        'ended_at' => now(),
    ]);

    // Broadcast call rejected
    event(new CallRejectedEvent($call));

    return response()->json(['status' => 'success']);
}
```

**API Endpoint:**
```http
POST /api/v1/video-call/{callId}/reject
```

### 4. Call Ending

```php
public function endCall(int $callId): JsonResponse
{
    $call = Call::findOrFail($callId);

    // Authorization (either participant can end)
    if ($call->caller_id !== auth()->id() && $call->receiver_id !== auth()->id()) {
        abort(403, 'Unauthorized');
    }

    // End meeting on VideoSDK
    app(VideoSDKService::class)->endMeeting($call->meeting_id);

    // Calculate duration
    $duration = $call->started_at ? $call->started_at->diffInSeconds(now()) : 0;

    // Update call record
    $call->update([
        'status' => 'ended',
        'ended_at' => now(),
        'duration' => $duration,
    ]);

    // Update call message
    Message::where('metadata->call_id', $call->id)->update([
        'content' => "Call ended · {$this->formatDuration($duration)}",
    ]);

    // Broadcast call ended
    event(new CallEndedEvent($call));

    return response()->json([
        'status' => 'success',
        'duration' => $duration,
    ]);
}

private function formatDuration(int $seconds): string
{
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    return sprintf('%02d:%02d', $minutes, $seconds);
}
```

**API Endpoint:**
```http
POST /api/v1/video-call/{callId}/end
```

---

## Call Features

### Video Calling Features

**Video Quality:**
- HD quality: 1080p (1920x1080)
- Standard quality: 720p (1280x720)
- Low quality: 480p (640x480)
- Adaptive bitrate based on connection

**Video Controls:**
- Enable/disable camera
- Switch between front/back camera (mobile)
- Video quality selection
- Picture-in-picture mode
- Full-screen mode
- Screen orientation lock

**Implementation:**
```javascript
// Quality selection
videoCallManager.setVideoQuality('hd');  // hd, standard, low

// PiP mode
videoCallManager.enablePictureInPicture();

// Full screen
videoCallManager.toggleFullscreen();
```

### Audio Calling Features

**Audio Quality:**
- High-quality audio codec (Opus)
- Noise cancellation
- Echo cancellation
- Auto gain control

**Audio Controls:**
- Mute/unmute microphone
- Speaker/headphone switching
- Volume control
- Audio routing (earpiece, speaker, bluetooth)

**Implementation:**
```javascript
// Noise cancellation
videoCallManager.enableNoiseCancellation(true);

// Speaker toggle
videoCallManager.setSpeakerMode(true);  // true = speaker, false = earpiece
```

### In-Call Features

**Controls Available:**
- **Mute Audio**: Toggle microphone
- **Disable Video**: Toggle camera
- **Switch Camera**: Front/back (mobile)
- **Speaker**: Toggle speaker mode
- **End Call**: Terminate call
- **Add Time**: Request extension (Premium feature)

**UI Layout:**
```html
<div class="video-call-container">
    <!-- Remote video (large) -->
    <video id="remote-video" class="remote-stream"></video>

    <!-- Local video (small, draggable) -->
    <video id="local-video" class="local-stream"></video>

    <!-- Call info -->
    <div class="call-info">
        <span class="participant-name">Fatima</span>
        <span class="call-duration">05:32</span>
        <span class="connection-quality">Excellent</span>
    </div>

    <!-- Controls -->
    <div class="call-controls">
        <button id="toggle-mic" class="control-btn">
            <i class="microphone-icon"></i>
        </button>
        <button id="toggle-camera" class="control-btn">
            <i class="camera-icon"></i>
        </button>
        <button id="switch-camera" class="control-btn">
            <i class="switch-icon"></i>
        </button>
        <button id="toggle-speaker" class="control-btn">
            <i class="speaker-icon"></i>
        </button>
        <button id="end-call" class="end-call-btn">
            <i class="end-icon"></i>
        </button>
    </div>
</div>
```

---

## Call States

### Call State Management

**Call States:**
1. **Ringing**: Call initiated, waiting for answer
2. **Connecting**: Call accepted, establishing connection
3. **Active**: Call in progress
4. **Ended**: Call completed normally
5. **Missed**: Recipient didn't answer
6. **Declined**: Recipient rejected call
7. **Failed**: Technical error occurred

**State Transitions:**
```
Ringing → Connecting → Active → Ended
Ringing → Declined
Ringing → Missed (after 60 seconds)
Any State → Failed (on error)
```

**Database Schema:**
```sql
CREATE TABLE calls (
    id BIGINT PRIMARY KEY,
    meeting_id VARCHAR(255) NOT NULL,
    type ENUM('video', 'audio') NOT NULL,
    caller_id BIGINT NOT NULL,
    receiver_id BIGINT NOT NULL,
    status ENUM('ringing', 'connecting', 'active', 'ended', 'missed', 'declined', 'failed'),
    provider ENUM('videosdk', 'agora') DEFAULT 'videosdk',
    started_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,
    duration INTEGER DEFAULT 0,  -- seconds
    quality_rating INTEGER NULL,  -- 1-5 stars
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_caller (caller_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_status (status)
);
```

---

## Call History

### Call Log

**Features:**
- Complete call history
- Filter by type (video/audio)
- Filter by status (answered, missed, declined)
- Call duration display
- Timestamp with smart formatting
- Quick actions (call back, view profile)

**API Endpoint:**
```http
GET /api/v1/video-call/history?type=video&status=ended&page=1
```

**Response:**
```json
{
  "data": [
    {
      "type": "call",
      "id": "123",
      "attributes": {
        "call_type": "video",
        "status": "ended",
        "duration": 342,  // seconds
        "created_at": "2025-10-07T10:30:00Z",
        "ended_at": "2025-10-07T10:35:42Z"
      },
      "relationships": {
        "participant": {
          "data": {"type": "user", "id": "uuid"}
        }
      }
    }
  ],
  "meta": {
    "total": 25,
    "total_duration": 12450  // total seconds
  }
}
```

### Call Statistics

**User Statistics:**
- Total calls made
- Total calls received
- Total call duration
- Average call duration
- Missed calls count
- Video vs audio ratio

**Per Match Statistics:**
- Calls with specific match
- Total duration together
- Average call length
- Last call date

**API Endpoint:**
```http
GET /api/v1/video-call/statistics
GET /api/v1/video-call/statistics/{matchId}
```

---

## Call Notifications

### Push Notifications

**Incoming Call:**
```json
{
  "type": "call",
  "title": "Incoming Video Call",
  "body": "Fatima is calling you...",
  "data": {
    "call_id": 123,
    "caller_id": "uuid",
    "caller_name": "Fatima",
    "caller_photo": "https://...",
    "type": "video"
  },
  "priority": "high",
  "sound": "ringtone.mp3"
}
```

**Missed Call:**
```json
{
  "type": "missed_call",
  "title": "Missed Call",
  "body": "You missed a video call from Fatima",
  "data": {
    "call_id": 123,
    "caller_id": "uuid"
  }
}
```

### In-App Notifications

**Incoming Call UI:**
- Full-screen incoming call interface
- Caller name and photo
- Accept button (green)
- Decline button (red)
- Ringtone sound
- Vibration (mobile)

**Call in Progress UI:**
- Minimizable to notification
- Always-on-top option
- Quick access from anywhere in app

---

## Call Limits & Restrictions

### Free Tier
- **Video Calls**: 3 per week (max 10 minutes each)
- **Audio Calls**: 5 per week (max 15 minutes each)
- **Total Monthly**: 100 minutes

### Premium Tier
- **Video Calls**: Unlimited (max 30 minutes each)
- **Audio Calls**: Unlimited (max 60 minutes each)
- **Total Monthly**: 1000 minutes

### Premium Plus Tier
- **Video Calls**: Unlimited (max 2 hours each)
- **Audio Calls**: Unlimited (max 4 hours each)
- **Total Monthly**: Unlimited

**Limit Enforcement:**
```php
public function canMakeCall(User $user, string $type): bool
{
    if ($user->hasActivePremiumPlus()) {
        return true;  // Unlimited
    }

    $weekStart = now()->startOfWeek();

    if ($user->hasActivePremium()) {
        // Premium limits
        $videoCalls = Call::where('caller_id', $user->id)
            ->where('type', 'video')
            ->where('created_at', '>=', $weekStart)
            ->count();

        $audioCalls = Call::where('caller_id', $user->id)
            ->where('type', 'audio')
            ->where('created_at', '>=', $weekStart)
            ->count();

        if ($type === 'video' && $videoCalls >= PHP_INT_MAX) return true;
        if ($type === 'audio' && $audioCalls >= PHP_INT_MAX) return true;

        return true;
    }

    // Free tier limits
    $calls = Call::where('caller_id', $user->id)
        ->where('type', $type)
        ->where('created_at', '>=', $weekStart)
        ->count();

    $limit = $type === 'video' ? 3 : 5;

    return $calls < $limit;
}
```

---

## Call Quality & Optimization

### Connection Quality Monitoring

**Quality Indicators:**
- **Excellent**: >1 Mbps, <50ms latency, 0% packet loss
- **Good**: >500 Kbps, <100ms latency, <5% packet loss
- **Fair**: >200 Kbps, <200ms latency, <10% packet loss
- **Poor**: <200 Kbps, >200ms latency, >10% packet loss

**Display:**
```html
<div class="connection-quality">
    <span class="quality-indicator excellent">Excellent</span>
    <span class="network-stats">1.2 Mbps · 45ms</span>
</div>
```

### Adaptive Bitrate

**Automatic Quality Adjustment:**
- Monitor network conditions in real-time
- Adjust video quality automatically
- Prioritize audio over video in poor conditions
- Notify users of quality changes

**Implementation:**
```javascript
videoCallManager.on('connection-quality-changed', (quality) => {
    if (quality === 'poor') {
        // Reduce video quality
        videoCallManager.setVideoQuality('low');

        // Notify user
        showNotification('Connection quality is poor. Video quality reduced.');
    }
});
```

---

## Best Practices

### For Users

**Before Calling:**
1. Ensure stable internet connection (WiFi recommended)
2. Check camera and microphone permissions
3. Find quiet location with good lighting
4. Test audio/video before important calls

**During Calls:**
1. Speak clearly and at normal pace
2. Mute when not speaking (reduce noise)
3. Use headphones to prevent echo
4. Maintain appropriate distance from camera

### For Developers

**Performance:**
1. Preload VideoSDK library
2. Cache meeting tokens
3. Implement reconnection logic
4. Monitor call quality metrics
5. Log call failures for debugging

**User Experience:**
1. Show connection status clearly
2. Provide call quality feedback
3. Implement graceful degradation
4. Handle network interruptions
5. Provide retry mechanisms

---

## API Reference

### Call Endpoints

```http
POST /api/v1/video-call/token
POST /api/v1/video-call/create-meeting
POST /api/v1/video-call/initiate
POST /api/v1/video-call/{callId}/accept
POST /api/v1/video-call/{callId}/reject
POST /api/v1/video-call/{callId}/end
GET /api/v1/video-call/history
GET /api/v1/video-call/statistics
GET /api/v1/video-call/statistics/{matchId}
```

---

*Last Updated: October 2025*
