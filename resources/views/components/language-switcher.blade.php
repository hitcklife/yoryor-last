<!-- Language Switcher Component -->
<div class="relative" x-data="{ open: false }">
    <!-- Current Language Button -->
    <button @click="open = !open" 
            @click.outside="open = false"
            class="flex items-center space-x-2 px-4 py-2 bg-white/80 backdrop-blur-sm border border-gray-200 rounded-lg hover:bg-white transition-colors duration-200">
        @php
            $currentLocale = app()->getLocale();
            $languages = [
                'en' => ['name' => 'English', 'flag' => '🇬🇧'],
                'uz' => ['name' => 'O\'zbekcha', 'flag' => '🇺🇿'],
                'ru' => ['name' => 'Русский', 'flag' => '🇷🇺'],
            ];
        @endphp
        
        <span class="text-lg">{{ $languages[$currentLocale]['flag'] }}</span>
        <span class="font-medium text-gray-700">{{ $languages[$currentLocale]['name'] }}</span>
        <svg class="w-4 h-4 text-gray-500 transition-transform duration-200" 
             :class="{ 'rotate-180': open }"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    
    <!-- Language Dropdown -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
        
        <div class="py-2">
            @foreach($languages as $locale => $language)
                @if($locale !== $currentLocale)
                    <a href="{{ route('locale.switch', $locale) }}" 
                       class="flex items-center space-x-3 px-4 py-2 hover:bg-gray-50 transition-colors duration-200">
                        <span class="text-lg">{{ $language['flag'] }}</span>
                        <span class="text-gray-700">{{ $language['name'] }}</span>
                        @if($locale === 'uz')
                            <span class="ml-auto text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">Recommended</span>
                        @endif
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</div>