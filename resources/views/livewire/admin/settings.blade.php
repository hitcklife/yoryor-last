<div>
    <!-- Settings Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">System Settings</h2>
        <p class="text-gray-600 mt-1">Configure your application settings and preferences</p>
    </div>

    <!-- Settings Tabs -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6" x-data="{ activeTab: 'general' }">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 px-6" aria-label="Tabs">
                <button @click="activeTab = 'general'" 
                        :class="activeTab === 'general' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    General
                </button>
                <button @click="activeTab = 'security'" 
                        :class="activeTab === 'security' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Security
                </button>
                <button @click="activeTab = 'limits'" 
                        :class="activeTab === 'limits' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    User Limits
                </button>
                <button @click="activeTab = 'moderation'" 
                        :class="activeTab === 'moderation' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Moderation
                </button>
                <button @click="activeTab = 'notifications'" 
                        :class="activeTab === 'notifications' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Notifications
                </button>
                <button @click="activeTab = 'payment'" 
                        :class="activeTab === 'payment' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Payment
                </button>
                <button @click="activeTab = 'features'" 
                        :class="activeTab === 'features' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Features
                </button>
                <button @click="activeTab = 'maintenance'" 
                        :class="activeTab === 'maintenance' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    Maintenance
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- General Settings -->
            <div x-show="activeTab === 'general'" x-transition>
                <form wire:submit.prevent="saveGeneralSettings">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">General Application Settings</h3>
                            
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Application Name</label>
                                    <input type="text" wire:model="appName" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                    @error('appName') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Application Description</label>
                                    <textarea wire:model="appDescription" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500"></textarea>
                                    @error('appDescription') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Maintenance Mode</label>
                                        <p class="text-sm text-gray-600">Enable to put the app in maintenance mode</p>
                                    </div>
                                    <x-toggle-switch wire:model="maintenanceMode" id="maintenanceMode" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">User Registration</label>
                                        <p class="text-sm text-gray-600">Allow new users to register</p>
                                    </div>
                                    <x-toggle-switch wire:model="registrationEnabled" id="registrationEnabled" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Email Verification Required</label>
                                        <p class="text-sm text-gray-600">Require users to verify their email</p>
                                    </div>
                                    <x-toggle-switch wire:model="emailVerificationRequired" id="emailVerificationRequired" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Phone Verification Required</label>
                                        <p class="text-sm text-gray-600">Require users to verify their phone number</p>
                                    </div>
                                    <x-toggle-switch wire:model="phoneVerificationRequired" id="phoneVerificationRequired" color="purple" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" 
                                class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors">
                            Save General Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Security Settings -->
            <div x-show="activeTab === 'security'" x-transition>
                <form wire:submit.prevent="saveSecuritySettings">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Security Configuration</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Login Attempts</label>
                                    <input type="number" wire:model="maxLoginAttempts" min="1" max="10"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                    @error('maxLoginAttempts') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Lockout Time (minutes)</label>
                                    <input type="number" wire:model="lockoutTime" min="1" max="60"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                    @error('lockoutTime') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Session Timeout (minutes)</label>
                                    <input type="number" wire:model="sessionTimeout" min="30" max="480"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                    @error('sessionTimeout') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Minimum Length</label>
                                    <input type="number" wire:model="passwordMinLength" min="6" max="20"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                    @error('passwordMinLength') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mt-6 space-y-4">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Two-Factor Authentication Required</label>
                                        <p class="text-sm text-gray-600">Require 2FA for all users</p>
                                    </div>
                                    <x-toggle-switch wire:model="twoFactorRequired" id="twoFactorRequired" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Password Special Characters</label>
                                        <p class="text-sm text-gray-600">Require special characters in passwords</p>
                                    </div>
                                    <x-toggle-switch wire:model="passwordRequireSpecialChars" id="passwordRequireSpecialChars" color="purple" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" 
                                class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors">
                            Save Security Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- User Limits -->
            <div x-show="activeTab === 'limits'" x-transition>
                <form wire:submit.prevent="saveUserLimits">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">User Limits & Restrictions</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Photos per User</label>
                                    <input type="number" wire:model="maxPhotosPerUser" min="1" max="20"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                    @error('maxPhotosPerUser') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Messages per Day</label>
                                    <input type="number" wire:model="maxMessagesPerDay" min="10" max="1000"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                    @error('maxMessagesPerDay') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Likes per Day</label>
                                    <input type="number" wire:model="maxLikesPerDay" min="5" max="500"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                    @error('maxLikesPerDay') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Reports per User</label>
                                    <input type="number" wire:model="maxReportsPerUser" min="1" max="50"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                    @error('maxReportsPerUser') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" 
                                class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors">
                            Save User Limits
                        </button>
                    </div>
                </form>
            </div>

            <!-- Content Moderation -->
            <div x-show="activeTab === 'moderation'" x-transition>
                <form wire:submit.prevent="saveContentModerationSettings">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Content Moderation</h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Auto-Moderate Messages</label>
                                        <p class="text-sm text-gray-600">Automatically check messages for inappropriate content</p>
                                    </div>
                                    <x-toggle-switch wire:model="autoModerateMessages" id="autoModerateMessages" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Auto-Moderate Photos</label>
                                        <p class="text-sm text-gray-600">Automatically check photos for inappropriate content</p>
                                    </div>
                                    <x-toggle-switch wire:model="autoModeratePhotos" id="autoModeratePhotos" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Profanity Filter</label>
                                        <p class="text-sm text-gray-600">Filter out profanity and inappropriate language</p>
                                    </div>
                                    <x-toggle-switch wire:model="profanityFilterEnabled" id="profanityFilterEnabled" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Adult Content Detection</label>
                                        <p class="text-sm text-gray-600">Detect and flag adult content automatically</p>
                                    </div>
                                    <x-toggle-switch wire:model="adultContentDetection" id="adultContentDetection" color="purple" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" 
                                class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors">
                            Save Moderation Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Notifications -->
            <div x-show="activeTab === 'notifications'" x-transition>
                <form wire:submit.prevent="saveNotificationSettings">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Notification Settings</h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Email Notifications</label>
                                        <p class="text-sm text-gray-600">Send notifications via email</p>
                                    </div>
                                    <x-toggle-switch wire:model="emailNotificationsEnabled" id="emailNotificationsEnabled" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Push Notifications</label>
                                        <p class="text-sm text-gray-600">Send push notifications to mobile devices</p>
                                    </div>
                                    <x-toggle-switch wire:model="pushNotificationsEnabled" id="pushNotificationsEnabled" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">SMS Notifications</label>
                                        <p class="text-sm text-gray-600">Send notifications via SMS</p>
                                    </div>
                                    <x-toggle-switch wire:model="smsNotificationsEnabled" id="smsNotificationsEnabled" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Marketing Emails</label>
                                        <p class="text-sm text-gray-600">Send promotional and marketing emails</p>
                                    </div>
                                    <x-toggle-switch wire:model="marketingEmailsEnabled" id="marketingEmailsEnabled" color="purple" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" 
                                class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors">
                            Save Notification Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Payment -->
            <div x-show="activeTab === 'payment'" x-transition>
                <form wire:submit.prevent="savePaymentSettings">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Payment & Subscription Settings</h3>
                            
                            <div class="space-y-6">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Premium Subscriptions</label>
                                        <p class="text-sm text-gray-600">Enable premium subscription features</p>
                                    </div>
                                    <x-toggle-switch wire:model="premiumSubscriptionEnabled" id="premiumSubscriptionEnabled" color="purple" />
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Free Trial Days</label>
                                        <input type="number" wire:model="freeTrialDays" min="0" max="30"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                        @error('freeTrialDays') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Subscription Price</label>
                                        <input type="number" step="0.01" wire:model="subscriptionPrice" min="0"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                        @error('subscriptionPrice') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                                        <select wire:model="subscriptionCurrency" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-pink-500">
                                            <option value="USD">USD - US Dollar</option>
                                            <option value="EUR">EUR - Euro</option>
                                            <option value="GBP">GBP - British Pound</option>
                                            <option value="CAD">CAD - Canadian Dollar</option>
                                            <option value="AUD">AUD - Australian Dollar</option>
                                        </select>
                                        @error('subscriptionCurrency') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" 
                                class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors">
                            Save Payment Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Features -->
            <div x-show="activeTab === 'features'" x-transition>
                <form wire:submit.prevent="saveFeatureToggles">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Feature Toggles</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Video Calling</label>
                                        <p class="text-sm text-gray-600">Enable video calling feature</p>
                                    </div>
                                    <x-toggle-switch wire:model="videoCallingEnabled" id="videoCallingEnabled" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Voice Calling</label>
                                        <p class="text-sm text-gray-600">Enable voice calling feature</p>
                                    </div>
                                    <x-toggle-switch wire:model="voiceCallingEnabled" id="voiceCallingEnabled" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Group Chats</label>
                                        <p class="text-sm text-gray-600">Enable group messaging</p>
                                    </div>
                                    <x-toggle-switch wire:model="groupChatsEnabled" id="groupChatsEnabled" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Stories</label>
                                        <p class="text-sm text-gray-600">Enable user stories feature</p>
                                    </div>
                                    <x-toggle-switch wire:model="storiesEnabled" id="storiesEnabled" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Virtual Gifts</label>
                                        <p class="text-sm text-gray-600">Enable virtual gifting system</p>
                                    </div>
                                    <x-toggle-switch wire:model="giftingEnabled" id="giftingEnabled" color="purple" />
                                </div>

                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900">Live Streaming</label>
                                        <p class="text-sm text-gray-600">Enable live streaming feature</p>
                                    </div>
                                    <x-toggle-switch wire:model="liveStreamingEnabled" id="liveStreamingEnabled" color="purple" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" 
                                class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition-colors">
                            Save Feature Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Maintenance -->
            <div x-show="activeTab === 'maintenance'" x-transition>
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">System Maintenance</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Clear Cache -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                                <div class="flex items-center mb-4">
                                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold text-blue-900">Clear Application Cache</h4>
                                        <p class="text-sm text-blue-700">Clear all cached data and configuration</p>
                                    </div>
                                </div>
                                <button wire:click="clearCache" 
                                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                    Clear Cache
                                </button>
                            </div>

                            <!-- Run Maintenance -->
                            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                                <div class="flex items-center mb-4">
                                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold text-green-900">Run Maintenance Tasks</h4>
                                        <p class="text-sm text-green-700">Execute scheduled maintenance tasks</p>
                                    </div>
                                </div>
                                <button wire:click="runMaintenance" 
                                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                                    Run Maintenance
                                </button>
                            </div>
                        </div>

                        <!-- System Information -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mt-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">System Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <div class="text-sm text-gray-600">PHP Version</div>
                                    <div class="text-lg font-semibold text-gray-900">{{ PHP_VERSION }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-600">Laravel Version</div>
                                    <div class="text-lg font-semibold text-gray-900">{{ app()->version() }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-600">Environment</div>
                                    <div class="text-lg font-semibold text-gray-900">{{ app()->environment() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <script>
        window.addEventListener('settings-saved', event => {
            alert(event.detail[0].message);
        });

        window.addEventListener('cache-cleared', event => {
            alert(event.detail[0].message);
        });

        window.addEventListener('maintenance-completed', event => {
            alert(event.detail[0].message);
        });
    </script>
</div>