<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Call;
use App\Models\User;
use App\Services\VideoSDKService;

class VideoCallPage extends Component
{
    public $conversationId = null;
    public $callStatus = 'idle'; // idle, ringing, connecting, connected, ended
    public $callHistory = [];
    public $scheduledCalls = [];
    public $activeTab = 'call';
    public $isMuted = false;
    public $isVideoOn = true;
    public $isScreenSharing = false;
    public $callDuration = 0;
    public $callStartTime = null;
    public $otherParticipant = null;
    public $callQuality = 'good'; // poor, good, excellent
    public $networkStatus = 'connected';

    // VideoSDK specific properties
    public $callId = null;
    public $meetingId = null;
    public $token = null;
    public $callType = 'video'; // video or voice
    public $recipientName = null;
    public $recipientId = null;

    protected $queryString = [
        'conversationId' => ['except' => null],
        'activeTab' => ['except' => 'call'],
    ];

    public function mount($conversationId = null)
    {
        $this->conversationId = $conversationId;
        $this->loadCallHistory();
        $this->loadScheduledCalls();

        // Check for call data from session (when redirected from messages page)
        if (session('call_id')) {
            $this->callId = session('call_id');
            $this->meetingId = session('meeting_id');
            $this->token = session('token');
            $this->callType = session('call_type', 'video');
            $this->recipientName = session('recipient_name');
            $this->recipientId = session('recipient_id');
            $this->callStatus = 'connecting';

            // Set video on based on call type
            $this->isVideoOn = $this->callType === 'video';

            // Clear session data
            session()->forget(['call_id', 'meeting_id', 'token', 'call_type', 'recipient_name', 'recipient_id']);

            // Auto-join the call
            $this->dispatch('auto-join-call', [
                'meetingId' => $this->meetingId,
                'token' => $this->token,
                'participantName' => auth()->user()->profile->first_name ?? 'User',
                'callType' => $this->callType
            ]);
        }

        if ($conversationId) {
            $this->loadConversationData();
        }
    }

    public function loadCallHistory()
    {
        // TODO: Load from actual call history model
        $this->callHistory = [
            [
                'id' => 1,
                'participant_name' => 'Sarah Johnson',
                'participant_avatar' => null,
                'call_type' => 'video',
                'duration' => 1200, // seconds
                'status' => 'completed',
                'started_at' => now()->subDays(1),
                'ended_at' => now()->subDays(1)->addSeconds(1200),
                'quality' => 'excellent'
            ],
            [
                'id' => 2,
                'participant_name' => 'Mike Chen',
                'participant_avatar' => null,
                'call_type' => 'video',
                'duration' => 600,
                'status' => 'missed',
                'started_at' => now()->subDays(2),
                'ended_at' => null,
                'quality' => null
            ],
            [
                'id' => 3,
                'participant_name' => 'Emma Wilson',
                'participant_avatar' => null,
                'call_type' => 'audio',
                'duration' => 1800,
                'status' => 'completed',
                'started_at' => now()->subDays(3),
                'ended_at' => now()->subDays(3)->addSeconds(1800),
                'quality' => 'good'
            ]
        ];
    }

    public function loadScheduledCalls()
    {
        // TODO: Load from actual scheduled calls model
        $this->scheduledCalls = [
            [
                'id' => 1,
                'participant_name' => 'Alex Rodriguez',
                'participant_avatar' => null,
                'scheduled_at' => now()->addHours(2),
                'duration' => 30, // minutes
                'type' => 'video',
                'status' => 'upcoming'
            ],
            [
                'id' => 2,
                'participant_name' => 'Lisa Park',
                'participant_avatar' => null,
                'scheduled_at' => now()->addDays(1),
                'duration' => 45,
                'type' => 'video',
                'status' => 'upcoming'
            ]
        ];
    }

    public function loadConversationData()
    {
        // TODO: Load actual conversation and participant data
        $this->otherParticipant = [
            'id' => 1,
            'name' => 'Sarah Johnson',
            'avatar' => null,
            'is_online' => true,
            'last_seen' => now()->subMinutes(2)
        ];
    }

    public function initiateCall($participantId, $callType = 'video')
    {
        $this->callStatus = 'ringing';
        $this->callStartTime = now();
        
        // TODO: Implement actual call initiation with VideoSDK
        $this->dispatch('initiate-call', [
            'participantId' => $participantId,
            'callType' => $callType,
            'conversationId' => $this->conversationId
        ]);
        
        session()->flash('success', 'Call initiated successfully');
    }

    public function answerCall()
    {
        $this->callStatus = 'connecting';
        
        // TODO: Implement actual call answering
        $this->dispatch('answer-call');
        
        session()->flash('success', 'Call answered');
    }

    public function endCall()
    {
        $this->callStatus = 'ended';
        $this->callDuration = $this->callStartTime ? now()->diffInSeconds($this->callStartTime) : 0;
        
        // TODO: Implement actual call ending
        $this->dispatch('end-call');
        
        session()->flash('success', 'Call ended');
        
        // Reset call state
        $this->callStartTime = null;
        $this->isMuted = false;
        $this->isVideoOn = true;
        $this->isScreenSharing = false;
    }

    public function toggleMute()
    {
        $this->isMuted = !$this->isMuted;
        
        // TODO: Implement actual mute toggle
        $this->dispatch('toggle-mute', ['muted' => $this->isMuted]);
    }

    public function toggleVideo()
    {
        $this->isVideoOn = !$this->isVideoOn;
        
        // TODO: Implement actual video toggle
        $this->dispatch('toggle-video', ['videoOn' => $this->isVideoOn]);
    }

    public function toggleScreenShare()
    {
        $this->isScreenSharing = !$this->isScreenSharing;
        
        // TODO: Implement actual screen sharing toggle
        $this->dispatch('toggle-screen-share', ['sharing' => $this->isScreenSharing]);
    }

    public function scheduleCall($participantId, $scheduledAt, $duration = 30)
    {
        // TODO: Implement actual call scheduling
        session()->flash('success', 'Call scheduled successfully');
        $this->loadScheduledCalls();
    }

    public function cancelScheduledCall($callId)
    {
        // TODO: Implement actual call cancellation
        session()->flash('success', 'Scheduled call cancelled');
        $this->loadScheduledCalls();
    }

    public function joinScheduledCall($callId)
    {
        // TODO: Implement joining scheduled call
        $this->callStatus = 'connecting';
        session()->flash('success', 'Joining scheduled call...');
    }

    public function getCallDuration()
    {
        if (!$this->callStartTime) return '00:00';
        
        $duration = now()->diffInSeconds($this->callStartTime);
        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);
        $seconds = $duration % 60;
        
        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getCallQualityColor()
    {
        return match($this->callQuality) {
            'poor' => 'text-red-500',
            'good' => 'text-yellow-500',
            'excellent' => 'text-green-500',
            default => 'text-gray-500'
        };
    }

    public function getCallQualityIcon()
    {
        return match($this->callQuality) {
            'poor' => 'signal-0',
            'good' => 'signal-1',
            'excellent' => 'signal-2',
            default => 'signal'
        };
    }

    public function getNetworkStatusColor()
    {
        return match($this->networkStatus) {
            'connected' => 'text-green-500',
            'poor' => 'text-yellow-500',
            'disconnected' => 'text-red-500',
            default => 'text-gray-500'
        };
    }

    public function formatCallDuration($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        
        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getCallTypeIcon($type)
    {
        return match($type) {
            'video' => 'video',
            'audio' => 'phone',
            default => 'phone'
        };
    }

    public function getCallStatusColor($status)
    {
        return match($status) {
            'completed' => 'text-green-600 bg-green-100',
            'missed' => 'text-red-600 bg-red-100',
            'cancelled' => 'text-gray-600 bg-gray-100',
            default => 'text-gray-600 bg-gray-100'
        };
    }

    public function render()
    {
        return view('livewire.pages.video-call-page')
            ->layout('layouts.app', ['title' => 'Video Calls']);
    }
}
