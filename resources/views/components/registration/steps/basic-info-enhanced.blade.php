@props([
    'title' => 'Tell Us About Yourself',
    'subtitle' => 'Help us create the perfect profile for you'
])

<div class="space-y-8">
    <!-- Header -->
    <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $title }}</h1>
        <p class="text-gray-600">{{ $subtitle }}</p>
    </div>

    <!-- Success/Error Messages -->
    <!-- TODO: Add Alpine.js integration -->

    <!-- Form -->
    <form class="space-y-8">
        <!-- Gender Selection -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-6">I am a</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="cursor-pointer group">
                    <input type="radio" 
                           name="gender" 
                           value="male" 
                           class="sr-only peer" 
                           required>
                    <x-ui.glass-card 
                        hover="true" 
                        interactive="true"
                        class="p-6 text-center peer-checked:border-pink-500 peer-checked:bg-gradient-to-br peer-checked:from-pink-50 peer-checked:to-purple-50 hover:border-pink-300 transition-all duration-300">
                        <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center peer-checked:from-pink-500 peer-checked:to-purple-600 transition-all duration-300 shadow-lg">
                            <svg class="w-8 h-8 text-blue-600 peer-checked:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-lg text-gray-800 peer-checked:text-pink-700 transition-colors">Male</span>
                    </x-ui.glass-card>
                </label>

                <label class="cursor-pointer group">
                    <input type="radio" 
                           name="gender" 
                           value="female" 
                           class="sr-only peer" 
                           required>
                    <x-ui.glass-card 
                        hover="true" 
                        interactive="true"
                        class="p-6 text-center peer-checked:border-pink-500 peer-checked:bg-gradient-to-br peer-checked:from-pink-50 peer-checked:to-purple-50 hover:border-pink-300 transition-all duration-300">
                        <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-pink-100 to-rose-200 rounded-2xl flex items-center justify-center peer-checked:from-pink-500 peer-checked:to-purple-600 transition-all duration-300 shadow-lg">
                            <svg class="w-8 h-8 text-pink-600 peer-checked:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-lg text-gray-800 peer-checked:text-pink-700 transition-colors">Female</span>
                    </x-ui.glass-card>
                </label>
            </div>
        </div>

        <!-- Name Inputs -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <x-ui.floating-input
                label="First Name"
                :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z&quot;/></svg>'"
                required="true"
                size="lg"
            />
            
            <x-ui.floating-input
                label="Last Name"
                :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z&quot;/></svg>'"
                required="true"
                size="lg"
            />
        </div>

        <!-- Date of Birth -->
        <div>
            <x-ui.floating-input
                label="Date of Birth"
                type="date"
                :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z&quot;/></svg>'"
                required="true"
                size="lg"
            />
            <p class="mt-3 text-sm text-pink-600 flex items-center bg-pink-50 p-3 rounded-lg border border-pink-200">
                <svg class="w-4 h-4 mr-2 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                You must be at least 18 years old to join YorYor
            </p>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 pt-8">
            <x-ui.gradient-button
                variant="outline"
                size="lg"
                type="button"
                class="flex-1"
                :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M15 19l-7-7 7-7&quot;/></svg>'"
                icon-position="left"
            >
                Previous
            </x-ui.gradient-button>
            
            <x-ui.gradient-button
                variant="primary"
                size="lg"
                type="submit"
                class="flex-1"
                :icon="'<svg class=&quot;w-5 h-5&quot; fill=&quot;none&quot; stroke=&quot;currentColor&quot; viewBox=&quot;0 0 24 24&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M13 7l5 5m0 0l-5 5m5-5H6&quot;/></svg>'"
                icon-position="right"
            >
                Continue
            </x-ui.gradient-button>
        </div>
    </form>
</div>

<!-- TODO: Add Alpine.js functionality -->