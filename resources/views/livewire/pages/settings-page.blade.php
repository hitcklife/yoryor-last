<!-- Settings Page -->
<div>
    <div class="h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-900 flex overflow-hidden transition-colors duration-300">
        
        <!-- Left Navigation Sidebar - Full Mode -->
        <x-navigation-sidebar mode="full" />

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Header -->
            <div class="bg-white/80 dark:bg-zinc-800/80 backdrop-blur-lg border-b border-white/20 dark:border-zinc-700/50 transition-colors duration-300">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white transition-colors duration-300">Settings</h1>
                            <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Manage your account and preferences</p>
                        </div>
                        
                        <a href="{{ route('dashboard') }}"
                           class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-gray-700 dark:text-zinc-300 rounded-xl font-medium transition-colors">
                            <x-lucide-arrow-left class="w-4 h-4 inline mr-2" />
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto overflow-x-hidden p-6">
                <div class="max-w-4xl mx-auto">
        <div class="space-y-8">
            
            <!-- Account Settings -->
            <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-8 transition-colors duration-300">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center transition-colors duration-300">
                    <x-lucide-user class="w-6 h-6 mr-3 text-purple-600 dark:text-purple-400" />
                    Account Information
                </h2>
                
                <form wire:submit="updateAccount" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- First Name -->
                        <div>
                            <label for="firstName" class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">First Name</label>
                            <input type="text" id="firstName" wire:model="firstName"
                                   class="w-full px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors text-gray-900 dark:text-white">
                            @error('firstName') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Last Name -->
                        <div>
                            <label for="lastName" class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">Last Name</label>
                            <input type="text" id="lastName" wire:model="lastName"
                                   class="w-full px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors text-gray-900 dark:text-white">
                            @error('lastName') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">Email Address</label>
                            <input type="email" id="email" wire:model="email"
                                   class="w-full px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors text-gray-900 dark:text-white">
                            @error('email') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">Phone Number</label>
                            <input type="tel" id="phone" wire:model="phone"
                                   class="w-full px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors text-gray-900 dark:text-white">
                            @error('phone') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Language -->
                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">Language</label>
                            <select id="language" wire:model="language_preference"
                                    class="w-full px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors text-gray-900 dark:text-white">
                                <option value="en">🇺🇸 English</option>
                                <option value="uz">🇺🇿 O'zbekcha</option>
                                <option value="ru">🇷🇺 Русский</option>
                            </select>
                            @error('language_preference') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <button type="submit" 
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Update Account Info
                    </button>
                </form>
                
                <!-- Change Password Section -->
                <div class="mt-8 pt-8 border-t border-gray-200 dark:border-zinc-700 transition-colors duration-300">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 transition-colors duration-300">Change Password</h3>
                    <form wire:submit="changePassword" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="currentPassword" class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">Current Password</label>
                                <input type="password" id="currentPassword" wire:model="currentPassword"
                                       class="w-full px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors text-gray-900 dark:text-white">
                                @error('currentPassword') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="newPassword" class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">New Password</label>
                                <input type="password" id="newPassword" wire:model="newPassword"
                                       class="w-full px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors text-gray-900 dark:text-white">
                                @error('newPassword') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="confirmPassword" class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">Confirm Password</label>
                                <input type="password" id="confirmPassword" wire:model="confirmPassword"
                                       class="w-full px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors text-gray-900 dark:text-white">
                            </div>
                        </div>
                        <button type="submit" 
                                class="px-6 py-3 bg-gray-800 dark:bg-zinc-700 hover:bg-gray-900 dark:hover:bg-zinc-600 text-white rounded-xl font-medium transition-colors">
                            Change Password
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Discovery Preferences -->
            <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-8 transition-colors duration-300">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center transition-colors duration-300">
                    <x-lucide-search class="w-6 h-6 mr-3 text-purple-600 dark:text-purple-400" />
                    🔍 Discovery Settings
                </h2>
                
                <form wire:submit="updateDiscoveryPreferences" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Show Me -->
                        <div>
                            <label for="showMe" class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">Show Me</label>
                            <select id="showMe" wire:model="showMe"
                                    class="w-full px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors text-gray-900 dark:text-white">
                                <option value="all">Everyone</option>
                                <option value="men">Men</option>
                                <option value="women">Women</option>
                                <option value="non_binary">Non-binary</option>
                            </select>
                        </div>
                        
                        <!-- Age Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">Age Range</label>
                            <div class="flex items-center space-x-3">
                                <input type="number" wire:model="ageMin" min="18" max="100"
                                       class="flex-1 px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors text-gray-900 dark:text-white">
                                <span class="text-gray-500 dark:text-zinc-400">to</span>
                                <input type="number" wire:model="ageMax" min="18" max="100"
                                       class="flex-1 px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors text-gray-900 dark:text-white">
                            </div>
                            @error('ageMin') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                            @error('ageMax') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <!-- Distance -->
                        <div>
                            <label for="distance" class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">Max Distance (km)</label>
                            <input type="number" id="distance" wire:model="distance" min="1" max="500"
                                   class="w-full px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors text-gray-900 dark:text-white">
                            @error('distance') <span class="text-red-500 dark:text-red-400 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <!-- Additional Filters -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <x-lucide-check-circle class="w-5 h-5 text-green-600" />
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Only show verified profiles</p>
                                    <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Profiles with ID verification</p>
                                </div>
                            </div>
                            <x-toggle-switch wire:model="onlyVerified" id="onlyVerified" />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <x-lucide-image class="w-5 h-5 text-blue-600" />
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Only show profiles with photos</p>
                                    <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Skip profiles without pictures</p>
                                </div>
                            </div>
                            <x-toggle-switch wire:model="onlyWithPhotos" id="onlyWithPhotos" />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <x-lucide-sun class="w-5 h-5 text-purple-600" />
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Recently active only</p>
                                    <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Show users active in last 7 days</p>
                                </div>
                            </div>
                            <x-toggle-switch wire:model="recentlyActiveOnly" id="recentlyActiveOnly" />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 4v10a2 2 0 002 2h6a2 2 0 002-2V8M9 8V6a1 1 0 011-1h4a1 1 0 011 1v2M9 12h6"/>
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Smart photos</p>
                                    <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Show your best photos first</p>
                                </div>
                            </div>
                            <x-toggle-switch wire:model="smartPhotos" id="smartPhotos" />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Global mode</p>
                                    <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">See people from around the world</p>
                                </div>
                            </div>
                            <x-toggle-switch wire:model="globalMode" id="globalMode" />
                        </div>
                    </div>
                    
                    <button type="submit" 
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Update Discovery Preferences
                    </button>
                </form>
            </div>
            
            <!-- Privacy & Safety -->
            <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-8 transition-colors duration-300">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center transition-colors duration-300">
                    <x-lucide-shield-check class="w-6 h-6 mr-3 text-purple-600 dark:text-purple-400" />
                    🔒 Privacy & Safety Settings
                </h2>
                
                <form wire:submit="updatePrivacySettings" class="space-y-6">
                    <!-- Profile Visibility -->
                    <div>
                        <label for="profileVisibility" class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">Profile Visibility</label>
                        <select id="profileVisibility" wire:model="profileVisibility"
                                class="w-full px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors max-w-md text-gray-900 dark:text-white">
                            <option value="everyone">Visible to Everyone</option>
                            <option value="matches">Only Matches</option>
                            <option value="premium">Premium Users Only</option>
                        </select>
                    </div>
                    
                    <!-- Privacy Toggles -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Show my distance</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Others can see how far you are</p>
                            </div>
                            <x-toggle-switch wire:model="showDistance" id="showDistance" />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Show my age</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Display your age on your profile</p>
                            </div>
                            <x-toggle-switch wire:model="showAge" id="showAge" />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Show online status</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Others can see when you're active</p>
                            </div>
                            <x-toggle-switch wire:model="showOnlineStatus" id="showOnlineStatus" />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Incognito mode</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Browse without being seen</p>
                            </div>
                            <x-toggle-switch wire:model="incognitoMode" id="incognitoMode" />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Show last active</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Display when you were last active</p>
                            </div>
                            <x-toggle-switch wire:model="showLastActive" id="showLastActive" />
                        </div>
                    </div>

                    <!-- Age Display Settings -->
                    <div>
                        <label for="ageDisplayType" class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">Age Display</label>
                        <select id="ageDisplayType" wire:model="ageDisplayType"
                                class="w-full px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors max-w-md text-gray-900 dark:text-white">
                            <option value="exact">Exact Age (e.g., 25)</option>
                            <option value="range">Age Range (e.g., 25-30)</option>
                        </select>
                    </div>

                    <!-- Message & Safety Settings -->
                    <div class="space-y-4">
                        <div>
                            <label for="allowMessages" class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">Who can message me</label>
                            <select id="allowMessages" wire:model="allowMessages"
                                    class="w-full px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors max-w-md text-gray-900 dark:text-white">
                                <option value="everyone">Everyone</option>
                                <option value="matches">Only Matches</option>
                                <option value="premium">Premium Users Only</option>
                                <option value="nobody">Nobody</option>
                            </select>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Read receipts</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Let people know when you've read their messages</p>
                            </div>
                            <x-toggle-switch wire:model="readReceipts" id="readReceipts" />
                        </div>
                    </div>

                    <!-- Safety & Security -->
                    <div class="pt-6 border-t border-gray-200 dark:border-zinc-700 space-y-4 transition-colors duration-300">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white transition-colors duration-300">🛡️ Safety & Security</h3>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Prevent screenshots</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Block screenshots in the app</p>
                            </div>
                            <x-toggle-switch wire:model="preventScreenshots" id="preventScreenshots" />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Hide from contacts</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Don't show your profile to phone contacts</p>
                            </div>
                            <x-toggle-switch wire:model="hideFromContacts" id="hideFromContacts" />
                        </div>
                    </div>
                    
                    <button type="submit" 
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Update Privacy Settings
                    </button>
                </form>
            </div>
            
            <!-- Enhanced Notifications -->
            <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-8 transition-colors duration-300">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center transition-colors duration-300">
                    <x-lucide-download class="w-6 h-6 mr-3 text-purple-600 dark:text-purple-400" />
                    📱 Notification Settings
                </h2>
                
                <form wire:submit="updateNotificationSettings" class="space-y-6">
                    <!-- Global Notification Settings -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Email notifications</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Receive important updates via email</p>
                            </div>
                            <x-toggle-switch wire:model="emailNotifications" id="emailNotifications" />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Push notifications</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Get notified on your phone</p>
                            </div>
                            <x-toggle-switch wire:model="pushNotifications" id="pushNotifications" />
                        </div>
                    </div>
                    
                    <!-- Specific Notifications -->
                    <div class="pt-6 border-t border-gray-200 space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900">Notification Types</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">💕 New matches</p>
                                    <p class="text-sm text-gray-600">When someone likes you back</p>
                                </div>
                                <x-toggle-switch wire:model="newMatches" id="newMatches" />
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">💬 New messages</p>
                                    <p class="text-sm text-gray-600">When someone sends you a message</p>
                                </div>
                                <x-toggle-switch wire:model="newMessages" id="newMessages" />
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">❤️ New likes</p>
                                    <p class="text-sm text-gray-600">When someone likes your profile</p>
                                </div>
                                <x-toggle-switch wire:model="likes" id="likes" />
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">⭐ Super likes</p>
                                    <p class="text-sm text-gray-600">When someone super likes you</p>
                                </div>
                                <x-toggle-switch wire:model="superLikes" id="superLikes" />
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">👁️ Profile views</p>
                                    <p class="text-sm text-gray-600">When someone views your profile</p>
                                </div>
                                <x-toggle-switch wire:model="profileViews" id="profileViews" />
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">🎁 Promotions</p>
                                    <p class="text-sm text-gray-600">Special offers and features</p>
                                </div>
                                <x-toggle-switch wire:model="promotions" id="promotions" />
                            </div>
                        </div>
                    </div>

                    <!-- Notification Methods -->
                    <div class="pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Notification Methods</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">🔊 In-App Sounds</p>
                                    <p class="text-sm text-gray-600">Play sounds for notifications</p>
                                </div>
                                <x-toggle-switch wire:model="inAppSounds" id="inAppSounds" />
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-gray-900">📳 Vibration</p>
                                    <p class="text-sm text-gray-600">Vibrate for notifications</p>
                                </div>
                                <x-toggle-switch wire:model="vibration" id="vibration" />
                            </div>
                        </div>
                    </div>

                    <!-- Quiet Hours -->
                    <div class="pt-6 border-t border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">🌙 Quiet Hours</h3>
                                <p class="text-sm text-gray-600">Disable notifications during specific hours</p>
                            </div>
                            <x-toggle-switch wire:model="quietHoursEnabled" id="quietHoursEnabled" />
                        </div>
                        
                        @if($quietHoursEnabled)
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="quietHoursStart" class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                                <input type="time" id="quietHoursStart" wire:model="quietHoursStart"
                                       class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                            </div>
                            <div>
                                <label for="quietHoursEnd" class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                                <input type="time" id="quietHoursEnd" wire:model="quietHoursEnd"
                                       class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    <button type="submit" 
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Update Notification Settings
                    </button>
                </form>
            </div>

            <!-- Data Privacy Settings -->
            <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-8 transition-colors duration-300">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center transition-colors duration-300">
                    <x-lucide-file-text class="w-6 h-6 mr-3 text-purple-600 dark:text-purple-400" />
                    📊 Data & Privacy Control
                </h2>
                
                <form wire:submit="updateDataPrivacySettings" class="space-y-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Analytics data sharing</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Help improve our service with anonymous usage data</p>
                            </div>
                            <x-toggle-switch wire:model="shareAnalyticsData" id="shareAnalyticsData" />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Location data sharing</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Share location data to improve matches</p>
                            </div>
                            <x-toggle-switch wire:model="shareLocationData" id="shareLocationData" />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Personalized ads</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Show ads based on your interests and activity</p>
                            </div>
                            <x-toggle-switch wire:model="personalizedAds" id="personalizedAds" />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Data for improvements</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Use my data to improve app features</p>
                            </div>
                            <x-toggle-switch wire:model="dataForImprovements" id="dataForImprovements" />
                        </div>
                    </div>
                    
                    <button type="submit" 
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Update Data Privacy Settings
                    </button>
                </form>
            </div>

            <!-- Security & Verification -->
            <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-8 transition-colors duration-300">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center transition-colors duration-300">
                    <x-lucide-lock class="w-6 h-6 mr-3 text-purple-600 dark:text-purple-400" />
                    🛡️ Security & Verification
                </h2>
                
                <div class="space-y-6">
                    <!-- Verification Status -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 border border-gray-200 dark:border-zinc-700 rounded-xl transition-colors duration-300">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-semibold text-gray-900 dark:text-white transition-colors duration-300">Photo Verification</h3>
                                @if(auth()->user()->userSetting?->photo_verified_at)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓ Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Not Verified
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Verify your photos to get a blue checkmark</p>
                            @if(!auth()->user()->userSetting?->photo_verified_at)
                                <button wire:click="startPhotoVerification" class="mt-2 text-sm text-purple-600 hover:text-purple-800">
                                    Start Verification →
                                </button>
                            @endif
                        </div>
                        
                        <div class="p-4 border border-gray-200 dark:border-zinc-700 rounded-xl transition-colors duration-300">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-semibold text-gray-900 dark:text-white transition-colors duration-300">Phone Verification</h3>
                                @if(auth()->user()->userSetting?->phone_verified_at)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✓ Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Not Verified
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Verify your phone number for security</p>
                        </div>
                    </div>
                    
                    <!-- Security Settings -->
                    <form wire:submit="updateSecuritySettings" class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Two-Factor Authentication</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Add extra security to your account</p>
                            </div>
                            <x-toggle-switch wire:model="twoFactorEnabled" id="twoFactorEnabled" />
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">Login alerts</p>
                                <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Get notified of login attempts</p>
                            </div>
                            <x-toggle-switch wire:model="loginAlerts" id="loginAlerts" />
                        </div>
                        
                        <button type="submit" 
                                class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                            Update Security Settings
                        </button>
                    </form>
                </div>
            </div>

            <!-- Theme Preferences -->
            <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-8 transition-colors duration-300">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center transition-colors duration-300">
                    <x-lucide-sun class="w-6 h-6 mr-3 text-purple-600 dark:text-purple-400" />
                    🎨 Theme Preferences
                </h2>
                
                <form wire:submit="updateThemePreference" class="space-y-6">
                    <div>
                        <label for="themePreference" class="block text-sm font-medium text-gray-700 dark:text-zinc-300 mb-2 transition-colors duration-300">Appearance Theme</label>
                        <select id="themePreference" wire:model="themePreference"
                                class="w-full px-4 py-3 bg-white/50 dark:bg-zinc-700/50 border border-gray-200 dark:border-zinc-600 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors text-gray-900 dark:text-white max-w-md">
                            <option value="system">🌓 System (Auto)</option>
                            <option value="light">☀️ Light Mode</option>
                            <option value="dark">🌙 Dark Mode</option>
                        </select>
                        <p class="text-sm text-gray-600 dark:text-zinc-400 mt-2 transition-colors duration-300">
                            Choose how the app appears. System will automatically switch between light and dark based on your device settings.
                        </p>
                    </div>
                    
                    <button type="submit" 
                            class="px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                        Update Theme Preference
                    </button>
                </form>
            </div>

            <!-- Blocked Users Management -->
            <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-8 transition-colors duration-300">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center transition-colors duration-300">
                    <x-lucide-user-x class="w-6 h-6 mr-3 text-red-600 dark:text-red-400" />
                    🚫 Blocked Users
                </h2>
                
                <div class="space-y-4">
                    <p class="text-gray-600 dark:text-zinc-400 transition-colors duration-300">Manage users you have blocked. Blocked users cannot see your profile or send you messages.</p>
                    
                    @if(auth()->user()->blockedUsers && auth()->user()->blockedUsers->count() > 0)
                        <div class="space-y-3">
                            @foreach(auth()->user()->blockedUsers as $userBlock)
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-zinc-700 rounded-xl transition-colors duration-300">
                                    <div class="flex items-center space-x-3">
                                        @if($userBlock->blocked->profilePhoto)
                                            <img src="{{ $userBlock->blocked->profilePhoto->thumbnail_url }}" 
                                                 alt="{{ $userBlock->blocked->profile?->first_name }}"
                                                 class="w-12 h-12 rounded-full object-cover">
                                        @else
                                            <div class="w-12 h-12 bg-gradient-to-br from-gray-400 to-gray-500 rounded-full flex items-center justify-center">
                                                <span class="text-white font-bold text-lg">{{ substr($userBlock->blocked->profile?->first_name ?? 'U', 0, 1) }}</span>
                                            </div>
                                        @endif
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white transition-colors duration-300">{{ $userBlock->blocked->profile?->first_name }} {{ $userBlock->blocked->profile?->last_name }}</p>
                                            <p class="text-sm text-gray-600 dark:text-zinc-400 transition-colors duration-300">Blocked on {{ $userBlock->created_at->format('M j, Y') }}</p>
                                        </div>
                                    </div>
                                    <button wire:click="unblockUser({{ $userBlock->blocked_id }})" 
                                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">
                                        Unblock
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                            </svg>
                            <p class="text-gray-500 dark:text-zinc-400 font-medium transition-colors duration-300">No blocked users</p>
                            <p class="text-sm text-gray-400 dark:text-zinc-500 transition-colors duration-300">You haven't blocked anyone yet.</p>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Premium Upgrade -->
            <div class="bg-gradient-to-br from-purple-500 via-pink-500 to-rose-500 rounded-3xl shadow-xl p-8 text-white relative overflow-hidden">
                <!-- Background decoration -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-16 translate-x-16"></div>
                <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full translate-y-12 -translate-x-12"></div>
                
                <div class="relative">
                    <div class="flex items-center mb-6">
                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold">Upgrade to Premium</h3>
                            <p class="text-white/90">Unlock all features and get better matches</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>See who likes you</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Unlimited likes</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Rewind last swipe</span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>5 Super Likes per day</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Monthly Boost</span>
                            </div>
                            <div class="flex items-center space-x-3">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Passport (change location)</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button class="flex-1 bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white py-4 px-6 rounded-xl font-bold transition-colors border border-white/30">
                            1 Month - $9.99
                        </button>
                        <button class="flex-1 bg-white text-purple-600 py-4 px-6 rounded-xl font-bold hover:bg-gray-100 transition-colors">
                            6 Months - $39.99 <span class="text-sm font-normal">(Save 33%)</span>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Advanced Actions -->
            <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-3xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-8 transition-colors duration-300">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center transition-colors duration-300">
                    <x-lucide-alert-triangle class="w-6 h-6 mr-3 text-red-600 dark:text-red-400" />
                    Account Actions
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Export Data -->
                    <div class="p-6 border border-gray-200 dark:border-zinc-700 rounded-2xl transition-colors duration-300">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 transition-colors duration-300">Export My Data</h3>
                        <p class="text-gray-600 dark:text-zinc-400 text-sm mb-4 transition-colors duration-300">Download a copy of all your data including messages, photos, and profile information.</p>
                        <button wire:click="exportData"
                                class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                            Export Data
                        </button>
                    </div>
                    
                    <!-- Clear Matches -->
                    <div class="p-6 border border-gray-200 dark:border-zinc-700 rounded-2xl transition-colors duration-300">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 transition-colors duration-300">Reset Discovery</h3>
                        <p class="text-gray-600 dark:text-zinc-400 text-sm mb-4 transition-colors duration-300">Clear all your likes and matches to start fresh. This cannot be undone.</p>
                        <button wire:click="clearAllMatches"
                                wire:confirm="Are you sure? This will delete all your matches and likes."
                                class="w-full px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg font-medium transition-colors">
                            Clear All Matches
                        </button>
                    </div>
                    
                    <!-- Deactivate Account -->
                    <div class="p-6 border border-red-200 dark:border-red-800 rounded-2xl bg-red-50 dark:bg-red-900/20 transition-colors duration-300">
                        <h3 class="text-lg font-semibold text-red-900 dark:text-red-400 mb-2 transition-colors duration-300">Deactivate Account</h3>
                        <p class="text-red-600 dark:text-red-400 text-sm mb-4 transition-colors duration-300">Temporarily hide your profile. You can reactivate anytime by logging back in.</p>
                        <button class="w-full px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg font-medium transition-colors">
                            Deactivate Account
                        </button>
                    </div>
                    
                    <!-- Delete Account -->
                    <div class="p-6 border border-red-200 dark:border-red-800 rounded-2xl bg-red-50 dark:bg-red-900/20 transition-colors duration-300">
                        <h3 class="text-lg font-semibold text-red-900 dark:text-red-400 mb-2 transition-colors duration-300">Delete Account</h3>
                        <p class="text-red-600 dark:text-red-400 text-sm mb-4 transition-colors duration-300">Permanently delete your account and all data. This action cannot be undone.</p>
                        <button wire:click="deleteAccount"
                                wire:confirm="Are you absolutely sure? This will permanently delete your account and all data. This cannot be undone."
                                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                            Delete Account
                        </button>
                    </div>
                </div>
            </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Success Message -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" 
             class="fixed bottom-4 right-4 bg-green-500 dark:bg-green-600 text-white px-6 py-3 rounded-xl shadow-lg z-50 transition-colors duration-300"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100">
            {{ session('message') }}
        </div>
    @endif

    <!-- Custom Styles -->
    <style>
        /* Smooth transitions for all form elements */
        input, select, button {
            transition: all 0.2s ease;
        }
        
        /* Focus states */
        input:focus, select:focus {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        /* Custom scrollbar for content area */
        .overflow-y-auto::-webkit-scrollbar {
            width: 8px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.5);
            border-radius: 4px;
            border: 1px solid transparent;
        }
        
        .dark .overflow-y-auto::-webkit-scrollbar-thumb {
            background: rgba(82, 82, 91, 0.7);
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 0.8);
        }
        
        .dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: rgba(113, 113, 122, 0.9);
        }
        
        /* Dark mode form focus states */
        .dark input:focus, .dark select:focus {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.1);
        }
    </style>

    <!-- Theme Change Handler -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('theme-changed', (event) => {
                const theme = event.theme;
                const html = document.documentElement;
                
                if (theme === 'dark') {
                    html.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else if (theme === 'light') {
                    html.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    // System theme - remove stored preference and let system handle it
                    localStorage.removeItem('theme');
                    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        html.classList.add('dark');
                    } else {
                        html.classList.remove('dark');
                    }
                }
            });
        });
    </script>
</div>
