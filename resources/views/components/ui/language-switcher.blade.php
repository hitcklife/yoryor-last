@props([
    'currentLocale' => null,
    'availableLocales' => []
])

@php
$currentLocale = $currentLocale ?? app()->getLocale();
$availableLocales = $availableLocales ?: [
    'en' => ['name' => 'English', 'flag' => '🇺🇸', 'native' => 'English'],
    'ru' => ['name' => 'Russian', 'flag' => '🇷🇺', 'native' => 'Русский'],
    'uz' => ['name' => 'Uzbek', 'flag' => '🇺🇿', 'native' => 'O\'zbek'],
    'ar' => ['name' => 'Arabic', 'flag' => '🇸🇦', 'native' => 'العربية'],
    'he' => ['name' => 'Hebrew', 'flag' => '🇮🇱', 'native' => 'עברית']
];
@endphp

<div class="relative" x-data="{ open: false }">
    <!-- Language Button -->
    <button @click="open = !open"
            class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <span class="text-lg">{{ $availableLocales[$currentLocale]['flag'] ?? '🌐' }}</span>
        <span>{{ $availableLocales[$currentLocale]['native'] ?? $availableLocales[$currentLocale]['name'] ?? 'Language' }}</span>
        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
    </button>
    
    <!-- Language Dropdown -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         @click.away="open = false"
         class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg border border-gray-200 dark:border-gray-700 z-50">
        
        <div class="py-1">
            @foreach($availableLocales as $locale => $info)
                <a href="{{ route('locale.switch', $locale) }}"
                   class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors
                          {{ $locale === $currentLocale ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' : '' }}">
                    <span class="text-lg mr-3">{{ $info['flag'] }}</span>
                    <div class="flex-1">
                        <div class="font-medium">{{ $info['native'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $info['name'] }}</div>
                    </div>
                    @if($locale === $currentLocale)
                        <i data-lucide="check" class="w-4 h-4 text-blue-600 dark:text-blue-400"></i>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Initialize Lucide icons for language switcher
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
</script>
@endpush
