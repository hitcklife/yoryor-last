<div>
    <!-- Header -->
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Safety & Emergency</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Your safety is our priority
                </p>
            </div>
            
            <!-- Safety Score -->
            <div class="text-right">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $safetyScore }}%</div>
                <div class="text-sm {{ $this->getSafetyScoreColor() }} px-2 py-1 rounded-full">
                    {{ $this->getSafetyScoreText() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white dark:bg-zinc-800 border-b border-gray-200 dark:border-zinc-700">
        <div class="px-6">
            <nav class="flex space-x-8">
                <button wire:click="$set('activeTab', 'panic')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'panic' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Panic Button
                </button>
                <button wire:click="$set('activeTab', 'contacts')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'contacts' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Emergency Contacts
                </button>
                <button wire:click="$set('activeTab', 'alerts')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'alerts' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Safety Alerts
                </button>
                <button wire:click="$set('activeTab', 'settings')" 
                        class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'settings' ? 'border-red-500 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Safety Settings
                </button>
            </nav>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white dark:bg-zinc-800">
        @if($activeTab === 'panic')
            <!-- Panic Button Interface -->
            <div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 flex items-center justify-center p-6">
                <div class="text-center max-w-2xl mx-auto">
                    @if(!$isPanicActive)
                        <!-- Inactive State -->
                        <div class="mb-8">
                            <div class="w-32 h-32 mx-auto mb-6 bg-gradient-to-br from-red-500 to-orange-500 rounded-full flex items-center justify-center shadow-2xl">
                                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Emergency Panic Button</h2>
                            <p class="text-gray-600 dark:text-gray-400 mb-8">
                                Press and hold to activate emergency alert. Your emergency contacts will be notified immediately.
                            </p>
                            
                            <button wire:click="activatePanicButton" 
                                    class="w-24 h-24 bg-red-600 hover:bg-red-700 text-white rounded-full shadow-2xl transition-all duration-200 transform hover:scale-105 active:scale-95 flex items-center justify-center mx-auto">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </button>
                            
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-4">
                                Press and hold for 3 seconds to activate
                            </p>
                        </div>

                        <!-- Quick Actions -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-8">
                            <button wire:click="sendSafetyCheck" 
                                    class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors duration-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <h3 class="font-medium text-gray-900 dark:text-white">Safety Check</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Send a safety check to contacts</p>
                                    </div>
                                </div>
                            </button>
                            
                            <button wire:click="shareLocation" 
                                    class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors duration-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <h3 class="font-medium text-gray-900 dark:text-white">Share Location</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Share your current location</p>
                                    </div>
                                </div>
                            </button>
                        </div>

                    @else
                        <!-- Active Panic State -->
                        <div class="mb-8">
                            <div class="w-32 h-32 mx-auto mb-6 bg-red-600 rounded-full flex items-center justify-center shadow-2xl animate-pulse">
                                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            
                            <h2 class="text-3xl font-bold text-red-600 mb-4">EMERGENCY ALERT ACTIVE</h2>
                            <p class="text-gray-600 dark:text-gray-400 mb-8">
                                Emergency contacts will be notified in {{ $panicCountdown }} seconds
                            </p>
                            
                            <div class="flex items-center justify-center space-x-4">
                                <button wire:click="cancelPanicButton" 
                                        class="px-8 py-4 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium">
                                    Cancel Alert
                                </button>
                                
                                <button wire:click="sendPanicAlert" 
                                        class="px-8 py-4 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 font-medium">
                                    Send Now
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        @elseif($activeTab === 'contacts')
            <!-- Emergency Contacts -->
            <div class="p-6">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Emergency Contacts</h2>
                        <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 text-sm font-medium">
                            Add Contact
                        </button>
                    </div>
                    
                    @if(count($emergencyContacts) > 0)
                        <div class="space-y-4">
                            @foreach($emergencyContacts as $contact)
                                <div class="bg-white dark:bg-zinc-700 rounded-lg border border-gray-200 dark:border-zinc-600 p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-12 h-12 {{ $contact['is_primary'] ? 'bg-red-500' : 'bg-gray-500' }} rounded-full flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                            </div>
                                            
                                            <div>
                                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $contact['name'] }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $contact['phone'] }}</p>
                                                <span class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-200">
                                                    {{ ucfirst($contact['type']) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2">
                                            @if($contact['is_primary'])
                                                <span class="px-3 py-1 text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 rounded-full">
                                                    Primary
                                                </span>
                                            @endif
                                            
                                            <button wire:click="removeEmergencyContact({{ $contact['id'] }})" 
                                                    wire:confirm="Are you sure you want to remove this emergency contact?"
                                                    class="px-3 py-1 text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200 text-sm">
                                                Remove
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No emergency contacts</h3>
                            <p class="text-gray-600 dark:text-gray-400">Add emergency contacts to ensure your safety.</p>
                        </div>
                    @endif
                </div>
            </div>

        @elseif($activeTab === 'alerts')
            <!-- Safety Alerts -->
            <div class="p-6">
                <div class="max-w-4xl mx-auto">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Safety Alerts</h2>
                    
                    @if(count($recentAlerts) > 0)
                        <div class="space-y-4">
                            @foreach($recentAlerts as $alert)
                                <div class="bg-white dark:bg-zinc-700 rounded-lg border border-gray-200 dark:border-zinc-600 p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 {{ $this->getAlertTypeColor($alert['type']) }} rounded-full flex items-center justify-center">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                </svg>
                                            </div>
                                            
                                            <div>
                                                <h3 class="font-medium text-gray-900 dark:text-white">{{ $alert['message'] }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $alert['timestamp']->format('M j, Y \a\t g:i A') }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <span class="px-3 py-1 text-xs font-medium rounded-full {{ $this->getAlertTypeColor($alert['type']) }}">
                                            {{ ucfirst($alert['status']) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 dark:bg-zinc-700 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-12a7.5 7.5 0 0 0-15 0v12h5l-5 5-5-5h5"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No safety alerts</h3>
                            <p class="text-gray-600 dark:text-gray-400">Your safety alerts will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>

        @elseif($activeTab === 'settings')
            <!-- Safety Settings -->
            <div class="p-6">
                <div class="max-w-4xl mx-auto">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Safety Settings</h2>
                    
                    <div class="space-y-6">
                        @foreach($safetySettings as $setting => $enabled)
                            <div class="bg-white dark:bg-zinc-700 rounded-lg border border-gray-200 dark:border-zinc-600 p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="font-medium text-gray-900 dark:text-white">
                                            {{ ucwords(str_replace('_', ' ', $setting)) }}
                                        </h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $this->getSettingDescription($setting) }}
                                        </p>
                                    </div>
                                    
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               wire:model.live="safetySettings.{{ $setting }}"
                                               wire:change="updateSafetySettings('{{ $setting }}', $event.target.checked)"
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-red-300 dark:peer-focus:ring-red-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-red-600"></div>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
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

    @if (session()->has('warning'))
        <div class="fixed top-4 right-4 bg-yellow-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('warning') }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    // Panic button countdown
    document.addEventListener('livewire:init', () => {
        Livewire.on('start-panic-countdown', () => {
            let countdown = 10;
            const interval = setInterval(() => {
                countdown--;
                @this.set('panicCountdown', countdown);
                
                if (countdown <= 0) {
                    clearInterval(interval);
                    @this.sendPanicAlert();
                }
            }, 1000);
        });
    });
</script>
@endpush
