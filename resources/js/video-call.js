// Video Call Alpine.js Component
export default function videoCall() {
    return {
        // State
        isInitialized: false,
        isConnected: false,
        isCallActive: false,
        callType: 'video', // 'video' or 'audio'

        // Meeting info
        meetingId: null,
        participantName: 'User',

        // Controls state
        isMicEnabled: true,
        isWebcamEnabled: true,
        isScreenSharing: false,

        // Participants
        localParticipant: null,
        remoteParticipants: [],

        // UI state
        callStatus: 'idle', // idle, connecting, connected, ended
        callDuration: '00:00',
        callStartTime: null,

        // Error handling
        error: null,

        // Initialize component
        async init() {
            console.log('🎥 Initializing video call component');

            // Set up Livewire listeners
            this.setupLivewireListeners();

            // Set up VideoSDK event listeners
            this.setupVideoSDKListeners();

            // Check if VideoSDK service is available
            if (window.videoSDKService) {
                console.log('✅ VideoSDK service is available');
            } else {
                console.error('❌ VideoSDK service not found');
                this.error = 'VideoSDK service not available';
            }
        },

        // Set up Livewire event listeners
        setupLivewireListeners() {
            // Listen for call initiation from Livewire
            Livewire.on('initiate-call', (data) => {
                console.log('📞 Initiating call:', data);
                this.initiateCall(data.callType, data.participantName);
            });

            // Listen for call answer from Livewire
            Livewire.on('answer-call', (data) => {
                console.log('📞 Answering call:', data);
                this.answerCall();
            });

            // Listen for call end from Livewire
            Livewire.on('end-call', () => {
                console.log('📞 Ending call');
                this.endCall();
            });

            // Listen for control toggles
            Livewire.on('toggle-mute', (data) => {
                this.toggleMic();
            });

            Livewire.on('toggle-video', (data) => {
                this.toggleWebcam();
            });

            Livewire.on('toggle-screen-share', (data) => {
                this.toggleScreenShare();
            });

            // Listen for auto-join call (from video call page redirect)
            Livewire.on('auto-join-call', (data) => {
                console.log('🎯 Auto-joining call:', data);
                this.autoJoinCall(data.meetingId, data.token, data.participantName, data.callType);
            });
        },

        // Set up VideoSDK event listeners
        setupVideoSDKListeners() {
            if (!window.videoSDKService) return;

            const sdk = window.videoSDKService;

            // Meeting joined
            sdk.on('meeting-joined', (data) => {
                console.log('✅ Meeting joined successfully');
                this.isConnected = true;
                this.callStatus = 'connected';
                this.callStartTime = new Date();
                this.startCallTimer();

                // Update Livewire component
                this.$wire.call('updateCallStatus', 'connected');
            });

            // Meeting left
            sdk.on('meeting-left', () => {
                console.log('👋 Left meeting');
                this.resetCallState();
                this.$wire.call('updateCallStatus', 'ended');
            });

            // Participant joined
            sdk.on('participant-joined', (participant) => {
                console.log('👤 Participant joined:', participant.displayName);
                this.remoteParticipants.push(participant);
            });

            // Participant left
            sdk.on('participant-left', (participant) => {
                console.log('👋 Participant left:', participant.displayName);
                this.remoteParticipants = this.remoteParticipants.filter(
                    p => p.id !== participant.id
                );
            });

            // Stream events
            sdk.on('stream-enabled', ({ participant, stream }) => {
                console.log('📹 Stream enabled:', stream.kind);
                this.handleStreamEnabled(participant, stream);
            });

            sdk.on('stream-disabled', ({ participant, stream }) => {
                console.log('📹 Stream disabled:', stream.kind);
                this.handleStreamDisabled(participant, stream);
            });
        },

        // Initialize VideoSDK with token
        async initializeSDK(token) {
            if (!window.videoSDKService) {
                this.error = 'VideoSDK service not available';
                return false;
            }

            try {
                const success = await window.videoSDKService.initialize(token);
                if (success) {
                    this.isInitialized = true;
                    console.log('✅ VideoSDK initialized');
                    return true;
                } else {
                    this.error = 'Failed to initialize VideoSDK';
                    return false;
                }
            } catch (error) {
                console.error('❌ VideoSDK initialization error:', error);
                this.error = error.message;
                return false;
            }
        },

        // Initiate a call
        async initiateCall(callType = 'video', participantName = 'User') {
            if (!this.isInitialized) {
                // Get token from server first
                const token = await this.getAuthToken();
                if (!token) {
                    this.error = 'Failed to get auth token';
                    return false;
                }

                const success = await this.initializeSDK(token);
                if (!success) return false;
            }

            this.callType = callType;
            this.participantName = participantName;
            this.callStatus = 'connecting';

            // Generate meeting ID or get from server
            this.meetingId = await this.createMeeting();
            if (!this.meetingId) {
                this.error = 'Failed to create meeting';
                return false;
            }

            // Join the meeting
            const success = await window.videoSDKService.joinMeeting(
                this.meetingId,
                this.participantName,
                this.isMicEnabled,
                callType === 'video' ? this.isWebcamEnabled : false
            );

            if (!success) {
                this.error = 'Failed to join meeting';
                this.callStatus = 'ended';
                return false;
            }

            this.isCallActive = true;
            return true;
        },

        // Answer incoming call
        async answerCall() {
            // Implementation would depend on your call flow
            // For now, treat it similar to initiating a call
            await this.initiateCall(this.callType, this.participantName);
        },

        // Auto-join call (called when redirected from messages page)
        async autoJoinCall(meetingId, token, participantName, callType) {
            console.log('🚀 Auto-joining call with meeting ID:', meetingId);

            this.meetingId = meetingId;
            this.callType = callType;
            this.participantName = participantName;
            this.callStatus = 'connecting';

            // Initialize SDK if not already done
            if (!this.isInitialized) {
                const success = await this.initializeSDK(token);
                if (!success) {
                    console.error('Failed to initialize SDK for auto-join');
                    return false;
                }
            }

            // Join the meeting directly
            const success = await window.videoSDKService.joinMeeting(
                meetingId,
                participantName,
                this.isMicEnabled,
                callType === 'video' ? this.isWebcamEnabled : false
            );

            if (!success) {
                this.error = 'Failed to join meeting';
                this.callStatus = 'ended';
                return false;
            }

            this.isCallActive = true;
            return true;
        },

        // End call
        endCall() {
            if (window.videoSDKService && this.isCallActive) {
                window.videoSDKService.leaveMeeting();
            }
            this.resetCallState();
        },

        // Toggle microphone
        toggleMic() {
            if (window.videoSDKService && this.isCallActive) {
                window.videoSDKService.toggleMic();
                this.isMicEnabled = !this.isMicEnabled;
            }
        },

        // Toggle webcam
        toggleWebcam() {
            if (window.videoSDKService && this.isCallActive) {
                window.videoSDKService.toggleWebcam();
                this.isWebcamEnabled = !this.isWebcamEnabled;
            }
        },

        // Toggle screen sharing
        async toggleScreenShare() {
            if (!window.videoSDKService || !this.isCallActive) return;

            if (this.isScreenSharing) {
                const success = await window.videoSDKService.stopScreenShare();
                if (success) {
                    this.isScreenSharing = false;
                }
            } else {
                const success = await window.videoSDKService.startScreenShare();
                if (success) {
                    this.isScreenSharing = true;
                }
            }
        },

        // Handle stream enabled
        handleStreamEnabled(participant, stream) {
            // This will be handled by the VideoSDK service automatically
            // But you can add additional UI updates here
        },

        // Handle stream disabled
        handleStreamDisabled(participant, stream) {
            // This will be handled by the VideoSDK service automatically
            // But you can add additional UI updates here
        },

        // Get auth token from server
        async getAuthToken() {
            try {
                const response = await fetch('/api/v1/video-call/token', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + this.getApiToken()
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    return data.data.token;
                } else {
                    console.error('Failed to get auth token:', response.status);
                    return null;
                }
            } catch (error) {
                console.error('Error getting auth token:', error);
                return null;
            }
        },

        // Create meeting
        async createMeeting() {
            try {
                const response = await fetch('/api/v1/video-call/create-meeting', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + this.getApiToken()
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    return data.data.meetingId;
                } else {
                    console.error('Failed to create meeting:', response.status);
                    return null;
                }
            } catch (error) {
                console.error('Error creating meeting:', error);
                return null;
            }
        },

        // Get API token (this would need to be set somewhere)
        getApiToken() {
            return document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
        },

        // Start call timer
        startCallTimer() {
            if (this.callTimer) {
                clearInterval(this.callTimer);
            }

            this.callTimer = setInterval(() => {
                if (this.callStartTime) {
                    const now = new Date();
                    const diff = now - this.callStartTime;
                    const minutes = Math.floor(diff / 60000);
                    const seconds = Math.floor((diff % 60000) / 1000);
                    this.callDuration = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }, 1000);
        },

        // Reset call state
        resetCallState() {
            this.isCallActive = false;
            this.isConnected = false;
            this.callStatus = 'idle';
            this.meetingId = null;
            this.remoteParticipants = [];
            this.localParticipant = null;
            this.callDuration = '00:00';
            this.callStartTime = null;
            this.isScreenSharing = false;
            this.error = null;

            if (this.callTimer) {
                clearInterval(this.callTimer);
                this.callTimer = null;
            }
        },

        // Get call status display
        get callStatusDisplay() {
            switch (this.callStatus) {
                case 'connecting': return 'Connecting...';
                case 'connected': return 'Connected';
                case 'ended': return 'Call Ended';
                default: return 'Ready';
            }
        },

        // Check if call is active
        get isActive() {
            return this.isCallActive && this.callStatus === 'connected';
        },

        // Cleanup on destroy
        destroy() {
            if (this.callTimer) {
                clearInterval(this.callTimer);
            }
            if (this.isCallActive) {
                this.endCall();
            }
        }
    }
}

// Make available globally
window.videoCallComponent = videoCall;