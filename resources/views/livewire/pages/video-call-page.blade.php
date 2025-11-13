<div x-data="videoCall()" x-init="init()">
    <!-- Header -->
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Video Calls</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Connect face-to-face with your matches
                </p>
            </div>
            
            @if($conversationId && $otherParticipant)
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $otherParticipant['name'] }}
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            {{ $otherParticipant['is_online'] ? 'Online' : 'Last seen ' . $otherParticipant['last_seen']->diffForHumans() }}
                        </div>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold">
                            {{ substr($otherParticipant['name'], 0, 1) }}
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700">
        <div class="px-6">
            <nav class="flex space-x-8">
                <button wire:click="$set('activeTab', 'call')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'call' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Call
                </button>
                <button wire:click="$set('activeTab', 'history')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'history' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    History
                </button>
                <button wire:click="$set('activeTab', 'scheduled')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'scheduled' ? 'border-purple-500 text-purple-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Scheduled
                </button>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white dark:bg-zinc-800">
        @if($activeTab === 'call')
            <!-- Call Interface -->
            <div class="min-h-screen bg-gradient-to-br from-gray-900 to-black">
                @if($callStatus === 'idle')
                    <!-- Call Initiation -->
                    <div class="flex items-center justify-center min-h-screen p-6">
                        <div class="text-center">
                            <div class="w-32 h-32 mx-auto mb-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            
                            <h2 class="text-2xl font-bold text-white mb-4">Start a Video Call</h2>
                            <p class="text-gray-400 mb-8">Connect with your match face-to-face</p>
                            
                            <div class="flex items-center justify-center space-x-4">
                                <button wire:click="initiateCall(1, 'video')" 
                                        class="px-8 py-4 bg-green-600 text-white rounded-full hover:bg-green-700 transition-colors duration-200 font-medium flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Video Call</span>
                                </button>
                                
                                <button wire:click="initiateCall(1, 'audio')" 
                                        class="px-8 py-4 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors duration-200 font-medium flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span>Audio Call</span>
                                </button>
                            </div>
                        </div>
                    </div>

                @elseif($callStatus === 'ringing')
                    <!-- Incoming Call -->
                    <div class="flex items-center justify-center min-h-screen p-6">
                        <div class="text-center">
                            <div class="w-32 h-32 mx-auto mb-8 bg-gradient-to-br from-green-500 to-blue-500 rounded-full flex items-center justify-center animate-pulse">
                                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            
                            <h2 class="text-2xl font-bold text-white mb-2">Incoming Call</h2>
                            <p class="text-gray-400 mb-8">{{ $otherParticipant['name'] ?? 'Unknown' }} is calling...</p>
                            
                            <div class="flex items-center justify-center space-x-4">
                                <button wire:click="answerCall" 
                                        class="w-16 h-16 bg-green-600 text-white rounded-full hover:bg-green-700 transition-colors duration-200 flex items-center justify-center">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </button>
                                
                                <button wire:click="endCall" 
                                        class="w-16 h-16 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors duration-200 flex items-center justify-center">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                @elseif($callStatus === 'connected')
                    <!-- Active Call -->
                    <div class="relative min-h-screen">
                        <!-- Video Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 h-screen">
                            <!-- Remote Participants -->
                            <template x-for="participant in remoteParticipants" :key="participant.id">
                                <div class="bg-gray-800 flex items-center justify-center relative">
                                    <!-- Remote Video Element -->
                                    <video
                                        :id="'video-' + participant.id"
                                        class="w-full h-full object-cover"
                                        autoplay
                                        playsinline
                                        x-show="participant.webcamEnabled"
                                    ></video>

                                    <!-- Remote Audio Element -->
                                    <audio
                                        :id="'audio-' + participant.id"
                                        autoplay
                                    ></audio>

                                    <!-- Fallback Avatar when video is off -->
                                    <div x-show="!participant.webcamEnabled" class="text-center">
                                        <div class="w-32 h-32 mx-auto mb-4 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center">
                                            <span class="text-white text-4xl font-bold" x-text="participant.displayName?.charAt(0) || 'U'"></span>
                                        </div>
                                        <h3 class="text-xl font-semibold text-white" x-text="participant.displayName || 'Participant'"></h3>
                                    </div>

                                    <!-- Participant Info Overlay -->
                                    <div class="absolute bottom-4 left-4 bg-black bg-opacity-50 rounded-lg px-3 py-1">
                                        <span class="text-white text-sm font-medium" x-text="participant.displayName || 'Participant'"></span>
                                        <div class="flex items-center space-x-2 mt-1">
                                            <!-- Mic Status -->
                                            <div class="flex items-center">
                                                <x-lucide-mic x-show="participant.micEnabled" class="w-3 h-3 text-green-400" />
                                                <x-lucide-mic-off x-show="!participant.micEnabled" class="w-3 h-3 text-red-400" />
                                            </div>
                                            <!-- Video Status -->
                                            <div class="flex items-center">
                                                <x-lucide-video x-show="participant.webcamEnabled" class="w-3 h-3 text-green-400" />
                                                <x-lucide-video-off x-show="!participant.webcamEnabled" class="w-3 h-3 text-red-400" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Call Quality Indicator -->
                                    <div class="absolute top-4 left-4 flex items-center space-x-2">
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4 {{ $this->getCallQualityColor() }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                            <span class="text-white text-sm">{{ ucfirst($callQuality) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <!-- Local Video (Your Video) -->
                            <div class="bg-gray-900 flex items-center justify-center relative">
                                <!-- Local Video Element -->
                                <video
                                    id="video-local"
                                    class="w-full h-full object-cover"
                                    autoplay
                                    playsinline
                                    muted
                                    x-show="isWebcamEnabled && isCallActive"
                                ></video>

                                <!-- Fallback Avatar when video is off -->
                                <div x-show="!isWebcamEnabled || !isCallActive" class="text-center">
                                    <div class="w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-blue-400 to-green-500 rounded-full flex items-center justify-center">
                                        <span class="text-white text-2xl font-bold">You</span>
                                    </div>
                                    <h3 class="text-lg font-semibold text-white">You</h3>
                                </div>

                                <!-- Local Status Overlay -->
                                <div class="absolute bottom-4 right-4 bg-black bg-opacity-50 rounded-lg px-3 py-1">
                                    <span class="text-white text-sm font-medium">You</span>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <!-- Mic Status -->
                                        <div class="flex items-center">
                                            <x-lucide-mic x-show="isMicEnabled" class="w-3 h-3 text-green-400" />
                                            <x-lucide-mic-off x-show="!isMicEnabled" class="w-3 h-3 text-red-400" />
                                        </div>
                                        <!-- Video Status -->
                                        <div class="flex items-center">
                                            <x-lucide-video x-show="isWebcamEnabled" class="w-3 h-3 text-green-400" />
                                            <x-lucide-video-off x-show="!isWebcamEnabled" class="w-3 h-3 text-red-400" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Call Controls -->
                        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
                            <div class="bg-black bg-opacity-50 backdrop-blur-sm rounded-full px-8 py-4 flex items-center space-x-4">
                                <!-- Mute Button -->
                                <button @click="toggleMic()"
                                        class="w-12 h-12 text-white rounded-full hover:bg-opacity-80 transition-colors duration-200 flex items-center justify-center"
                                        :class="isMicEnabled ? 'bg-gray-600' : 'bg-red-600'">
                                    <x-lucide-mic x-show="isMicEnabled" class="w-6 h-6" />
                                    <x-lucide-mic-off x-show="!isMicEnabled" class="w-6 h-6" />
                                </button>

                                <!-- Video Toggle -->
                                <button @click="toggleWebcam()"
                                        class="w-12 h-12 text-white rounded-full hover:bg-opacity-80 transition-colors duration-200 flex items-center justify-center"
                                        :class="isWebcamEnabled ? 'bg-gray-600' : 'bg-red-600'">
                                    <x-lucide-video x-show="isWebcamEnabled" class="w-6 h-6" />
                                    <x-lucide-video-off x-show="!isWebcamEnabled" class="w-6 h-6" />
                                </button>

                                <!-- Screen Share -->
                                <button @click="toggleScreenShare()"
                                        class="w-12 h-12 text-white rounded-full hover:bg-opacity-80 transition-colors duration-200 flex items-center justify-center"
                                        :class="isScreenSharing ? 'bg-blue-600' : 'bg-gray-600'">
                                    <x-lucide-monitor class="w-6 h-6" />
                                </button>

                                <!-- End Call -->
                                <button @click="endCall()"
                                        class="w-12 h-12 bg-red-600 text-white rounded-full hover:bg-red-700 transition-colors duration-200 flex items-center justify-center">
                                    <x-lucide-phone-off class="w-6 h-6" />
                                </button>
                            </div>
                        </div>

                        <!-- Call Duration -->
                        <div class="absolute top-4 right-4">
                            <div class="bg-black bg-opacity-50 backdrop-blur-sm rounded-lg px-4 py-2">
                                <div class="text-white text-lg font-mono" x-text="callDuration"></div>
                            </div>
                        </div>

                        <!-- Call Status -->
                        <div class="absolute top-4 left-1/2 transform -translate-x-1/2">
                            <div class="bg-black bg-opacity-50 backdrop-blur-sm rounded-lg px-4 py-2">
                                <div class="text-white text-sm font-medium" x-text="callStatusDisplay"></div>
                            </div>
                        </div>
                    </div>

                @elseif($callStatus === 'ended')
                    <!-- Call Ended -->
                    <div class="flex items-center justify-center min-h-screen p-6">
                        <div class="text-center">
                            <div class="w-32 h-32 mx-auto mb-8 bg-gray-600 rounded-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            
                            <h2 class="text-2xl font-bold text-white mb-2">Call Ended</h2>
                            <p class="text-gray-400 mb-8">Duration: {{ $this->formatCallDuration($callDuration) }}</p>
                            
                            <button wire:click="$set('callStatus', 'idle')" 
                                    class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200 font-medium">
                                Start New Call
                            </button>
                        </div>
                    </div>
                @endif
            </div>

        @elseif($activeTab === 'history')
            <!-- Call History -->
            <div class="p-6">
                <div class="max-w-4xl mx-auto">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Call History</h2>
                    
                    @if(count($callHistory) > 0)
                        <div class="space-y-4">
                            @foreach($callHistory as $call)
                                <div class="bg-white dark:bg-zinc-700 rounded-lg border border-gray-200 dark:border-zinc-600 p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center">
                                                <span class="text-white font-bold">
                                                    {{ substr($call['participant_name'], 0, 1) }}
                                                </span>
                                            </div>
                                            
                                            <div>
                                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $call['participant_name'] }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $call['started_at']->format('M j, Y \a\t g:i A') }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-4">
                                            <div class="text-right">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $this->formatCallDuration($call['duration']) }}
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                    <span class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ $call['call_type'] }}</span>
                                                </div>
                                            </div>
                                            
                                            <span class="px-3 py-1 text-sm font-medium rounded-full {{ $this->getCallStatusColor($call['status']) }}">
                                                {{ ucfirst($call['status']) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-zinc-700 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No call history</h3>
                            <p class="text-gray-600 dark:text-gray-400">Your call history will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>

        @elseif($activeTab === 'scheduled')
            <!-- Scheduled Calls -->
            <div class="p-6">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Scheduled Calls</h2>
                        <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200 text-sm font-medium">
                            Schedule Call
                        </button>
                    </div>
                    
                    @if(count($scheduledCalls) > 0)
                        <div class="space-y-4">
                            @foreach($scheduledCalls as $call)
                                <div class="bg-white dark:bg-zinc-700 rounded-lg border border-gray-200 dark:border-zinc-600 p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                                                <span class="text-white font-bold">
                                                    {{ substr($call['participant_name'], 0, 1) }}
                                                </span>
                                            </div>
                                            
                                            <div>
                                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $call['participant_name'] }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $call['scheduled_at']->format('M j, Y \a\t g:i A') }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    Duration: {{ $call['duration'] }} minutes
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2">
                                            <button wire:click="joinScheduledCall({{ $call['id'] }})" 
                                                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 text-sm font-medium">
                                                Join
                                            </button>
                                            
                                            <button wire:click="cancelScheduledCall({{ $call['id'] }})" 
                                                    wire:confirm="Are you sure you want to cancel this scheduled call?"
                                                    class="px-4 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200 text-sm font-medium">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-zinc-700 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No scheduled calls</h3>
                            <p class="text-gray-600 dark:text-gray-400">Schedule calls with your matches to connect at a convenient time.</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif
</div>
