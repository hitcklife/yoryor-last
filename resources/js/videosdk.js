import VideoSDK from "@videosdk.live/js-sdk";

class VideoSDKService {
    constructor() {
        this.meeting = null;
        this.participants = new Map();
        this.localParticipant = null;
        this.token = null;
        this.meetingId = null;
        this.isInitialized = false;
        this.callbacks = {};
        this.localVideoStream = null;
        this.localAudioStream = null;
    }

    // Initialize VideoSDK with auth token
    async initialize(token) {
        try {
            this.token = token;
            VideoSDK.config(token);
            this.isInitialized = true;
            console.log('✅ VideoSDK initialized successfully');
            return true;
        } catch (error) {
            console.error('❌ VideoSDK initialization failed:', error);
            return false;
        }
    }

    // Join a meeting
    async joinMeeting(meetingId, participantName, micEnabled = true, webcamEnabled = true) {
        if (!this.isInitialized) {
            throw new Error('VideoSDK not initialized');
        }

        try {
            this.meetingId = meetingId;

            // Create meeting instance
            this.meeting = VideoSDK.initMeeting({
                meetingId: meetingId,
                name: participantName,
                micEnabled: micEnabled,
                webcamEnabled: webcamEnabled,
                participantId: `participant_${Date.now()}`,
            });

            // Set up event listeners
            this.setupEventListeners();

            // Join the meeting
            this.meeting.join();

            console.log('✅ Joining meeting:', meetingId);
            return true;
        } catch (error) {
            console.error('❌ Failed to join meeting:', error);
            return false;
        }
    }

    // Set up all event listeners
    setupEventListeners() {
        if (!this.meeting) return;

        // Meeting events
        this.meeting.on("meeting-joined", () => {
            console.log('✅ Meeting joined successfully');
            this.localParticipant = this.meeting.localParticipant;
            this.triggerCallback('meeting-joined', { meetingId: this.meetingId });
        });

        this.meeting.on("meeting-left", () => {
            console.log('📤 Left meeting');
            this.cleanup();
            this.triggerCallback('meeting-left');
        });

        // Participant events
        this.meeting.on("participant-joined", (participant) => {
            console.log('👤 Participant joined:', participant.displayName);
            this.participants.set(participant.id, participant);
            this.setupParticipantEventListeners(participant);
            this.triggerCallback('participant-joined', participant);
        });

        this.meeting.on("participant-left", (participant) => {
            console.log('👋 Participant left:', participant.displayName);
            this.participants.delete(participant.id);
            this.triggerCallback('participant-left', participant);
        });

        // Stream events for local participant
        if (this.localParticipant) {
            this.setupParticipantEventListeners(this.localParticipant);
        }
    }

    // Set up event listeners for individual participants
    setupParticipantEventListeners(participant) {
        // Video stream events
        participant.on("stream-enabled", (stream) => {
            console.log(`📹 ${stream.kind} stream enabled for ${participant.displayName}`);
            this.handleStreamEnabled(participant, stream);
        });

        participant.on("stream-disabled", (stream) => {
            console.log(`📹 ${stream.kind} stream disabled for ${participant.displayName}`);
            this.handleStreamDisabled(participant, stream);
        });

        // Audio events
        participant.on("mic-toggled", () => {
            console.log(`🎤 Mic toggled for ${participant.displayName}`);
            this.triggerCallback('participant-mic-toggled', participant);
        });

        // Video events
        participant.on("webcam-toggled", () => {
            console.log(`📹 Webcam toggled for ${participant.displayName}`);
            this.triggerCallback('participant-webcam-toggled', participant);
        });
    }

    // Handle stream enabled
    handleStreamEnabled(participant, stream) {
        if (stream.kind === 'video') {
            const videoElement = this.getVideoElement(participant.id);
            if (videoElement) {
                const mediaStream = new MediaStream();
                mediaStream.addTrack(stream.track);
                videoElement.srcObject = mediaStream;
                videoElement.play().catch(console.error);
            }
        } else if (stream.kind === 'audio') {
            const audioElement = this.getAudioElement(participant.id);
            if (audioElement) {
                const mediaStream = new MediaStream();
                mediaStream.addTrack(stream.track);
                audioElement.srcObject = mediaStream;
                audioElement.play().catch(console.error);
            }
        }

        this.triggerCallback('stream-enabled', { participant, stream });
    }

    // Handle stream disabled
    handleStreamDisabled(participant, stream) {
        if (stream.kind === 'video') {
            const videoElement = this.getVideoElement(participant.id);
            if (videoElement) {
                videoElement.srcObject = null;
            }
        } else if (stream.kind === 'audio') {
            const audioElement = this.getAudioElement(participant.id);
            if (audioElement) {
                audioElement.srcObject = null;
            }
        }

        this.triggerCallback('stream-disabled', { participant, stream });
    }

    // Get video element for participant
    getVideoElement(participantId) {
        return document.getElementById(`video-${participantId}`);
    }

    // Get audio element for participant
    getAudioElement(participantId) {
        return document.getElementById(`audio-${participantId}`);
    }

    // Toggle microphone
    toggleMic() {
        if (this.meeting) {
            if (this.meeting.localParticipant.micEnabled) {
                this.meeting.muteMic();
            } else {
                this.meeting.unmuteMic();
            }
        }
    }

    // Toggle webcam
    toggleWebcam() {
        if (this.meeting) {
            if (this.meeting.localParticipant.webcamEnabled) {
                this.meeting.disableWebcam();
            } else {
                this.meeting.enableWebcam();
            }
        }
    }

    // Start screen share
    async startScreenShare() {
        if (this.meeting) {
            try {
                await this.meeting.enableScreenShare();
                console.log('🖥️ Screen sharing started');
                return true;
            } catch (error) {
                console.error('❌ Failed to start screen sharing:', error);
                return false;
            }
        }
    }

    // Stop screen share
    async stopScreenShare() {
        if (this.meeting) {
            try {
                await this.meeting.disableScreenShare();
                console.log('🖥️ Screen sharing stopped');
                return true;
            } catch (error) {
                console.error('❌ Failed to stop screen sharing:', error);
                return false;
            }
        }
    }

    // Leave meeting
    leaveMeeting() {
        if (this.meeting) {
            this.meeting.leave();
        }
    }

    // End meeting for all
    endMeeting() {
        if (this.meeting) {
            this.meeting.end();
        }
    }

    // Register callback
    on(event, callback) {
        if (!this.callbacks[event]) {
            this.callbacks[event] = [];
        }
        this.callbacks[event].push(callback);
    }

    // Remove callback
    off(event, callback) {
        if (this.callbacks[event]) {
            const index = this.callbacks[event].indexOf(callback);
            if (index > -1) {
                this.callbacks[event].splice(index, 1);
            }
        }
    }

    // Trigger callback
    triggerCallback(event, data = null) {
        if (this.callbacks[event]) {
            this.callbacks[event].forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`Error in callback for ${event}:`, error);
                }
            });
        }
    }

    // Get meeting info
    getMeetingInfo() {
        if (!this.meeting) return null;

        return {
            meetingId: this.meetingId,
            localParticipant: {
                id: this.meeting.localParticipant.id,
                name: this.meeting.localParticipant.displayName,
                micEnabled: this.meeting.localParticipant.micEnabled,
                webcamEnabled: this.meeting.localParticipant.webcamEnabled
            },
            participants: Array.from(this.participants.values()).map(p => ({
                id: p.id,
                name: p.displayName,
                micEnabled: p.micEnabled,
                webcamEnabled: p.webcamEnabled
            }))
        };
    }

    // Cleanup
    cleanup() {
        this.meeting = null;
        this.participants.clear();
        this.localParticipant = null;
        this.meetingId = null;
        this.callbacks = {};

        // Clean up video elements
        document.querySelectorAll('video[id^="video-"], audio[id^="audio-"]').forEach(element => {
            if (element.srcObject) {
                element.srcObject.getTracks().forEach(track => track.stop());
                element.srcObject = null;
            }
        });
    }

    // Get participants list
    getParticipants() {
        return Array.from(this.participants.values());
    }

    // Check if meeting is active
    isActive() {
        return this.meeting !== null;
    }
}

// Create global instance
window.videoSDKService = new VideoSDKService();

// Export for module usage
export default VideoSDKService;