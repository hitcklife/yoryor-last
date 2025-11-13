<section class="w-full min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-900 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 py-8">
        @include('partials.settings-heading')

        <x-settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <p class="text-green-700 dark:text-green-400 text-sm">{{ session('success') }}</p>
            </div>
        @endif

        <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Theme Preference</h3>
            <p class="text-sm text-gray-600 dark:text-zinc-400">Choose how the interface looks. System will automatically switch between light and dark mode based on your device settings.</p>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Light Theme Option -->
                <button wire:click="setTheme('light')" 
                        class="p-4 border-2 rounded-xl transition-all duration-200 {{ $currentTheme === 'light' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 dark:border-zinc-700 hover:border-gray-300 dark:hover:border-zinc-600' }} dark:bg-zinc-800/50">
                    <div class="flex flex-col items-center space-y-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-yellow-400 to-orange-400 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="text-center">
                            <p class="font-medium text-gray-900 dark:text-white">Light</p>
                            <p class="text-xs text-gray-500 dark:text-zinc-400">Always use light mode</p>
                        </div>
                        @if($currentTheme === 'light')
                            <div class="flex items-center text-purple-600 dark:text-purple-400">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-xs font-medium">Selected</span>
                            </div>
                        @endif
                    </div>
                </button>

                <!-- Dark Theme Option -->
                <button wire:click="setTheme('dark')" 
                        class="p-4 border-2 rounded-xl transition-all duration-200 {{ $currentTheme === 'dark' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 dark:border-zinc-700 hover:border-gray-300 dark:hover:border-zinc-600' }} dark:bg-zinc-800/50">
                    <div class="flex flex-col items-center space-y-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-gray-700 to-gray-900 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                        </div>
                        <div class="text-center">
                            <p class="font-medium text-gray-900 dark:text-white">Dark</p>
                            <p class="text-xs text-gray-500 dark:text-zinc-400">Always use dark mode</p>
                        </div>
                        @if($currentTheme === 'dark')
                            <div class="flex items-center text-purple-600 dark:text-purple-400">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-xs font-medium">Selected</span>
                            </div>
                        @endif
                    </div>
                </button>

                <!-- System Theme Option -->
                <button wire:click="setTheme('system')" 
                        class="p-4 border-2 rounded-xl transition-all duration-200 {{ $currentTheme === 'system' ? 'border-purple-500 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 dark:border-zinc-700 hover:border-gray-300 dark:hover:border-zinc-600' }} dark:bg-zinc-800/50">
                    <div class="flex flex-col items-center space-y-3">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="text-center">
                            <p class="font-medium text-gray-900 dark:text-white">System</p>
                            <p class="text-xs text-gray-500 dark:text-zinc-400">Match device settings</p>
                        </div>
                        @if($currentTheme === 'system')
                            <div class="flex items-center text-purple-600 dark:text-purple-400">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-xs font-medium">Selected</span>
                            </div>
                        @endif
                    </div>
                </button>
            </div>

            <!-- Additional Information -->
            <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/30 rounded-lg">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">About System Theme</h4>
                        <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                            When set to "System", the app will automatically switch between light and dark modes based on your device's current theme preference. This works just like Instagram and other modern apps.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </x-settings.layout>
    </div>
</section>
