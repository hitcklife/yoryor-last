<div class="relative" x-data="{ open: false }">
    <!-- Language Button -->
    <button @click="open = !open" 
            class="flex items-center space-x-2 p-2 rounded-xl hover:bg-gray-100 transition-colors duration-200">
        <span class="text-lg">{{ $availableLocales[$currentLocale]['flag'] }}</span>
        <span class="hidden sm:block text-sm font-medium text-gray-700">
            {{ $availableLocales[$currentLocale]['native'] }}
        </span>
        <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" 
             :class="{ 'rotate-180': open }" 
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Language Dropdown -->
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-2xl border border-gray-100 py-2 z-50">
        
        @foreach($availableLocales as $locale => $details)
            <button wire:click="switchLanguage('{{ $locale }}')"
                    @click="open = false"
                    class="w-full flex items-center space-x-3 px-4 py-3 text-left hover:bg-gray-50 transition-colors duration-200 
                           {{ $currentLocale === $locale ? 'bg-gradient-to-r from-pink-50 to-purple-50 text-purple-600' : 'text-gray-700' }}">
                <span class="text-xl">{{ $details['flag'] }}</span>
                <div class="flex-1">
                    <div class="font-medium text-sm">{{ $details['native'] }}</div>
                    <div class="text-xs text-gray-500">{{ $details['name'] }}</div>
                </div>
                @if($currentLocale === $locale)
                    <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </button>
        @endforeach
        
        <!-- Divider -->
        <div class="border-t border-gray-100 my-2"></div>
        
        <!-- Footer -->
        <div class="px-4 py-2">
            <p class="text-xs text-gray-500 text-center">
                {{ __('dashboard.app_name') }} speaks your language 💕
            </p>
        </div>
    </div>
</div>
