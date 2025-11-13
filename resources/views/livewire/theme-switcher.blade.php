<div class="relative" x-data="{ open: false }" @click.away="open = false">
    {{-- Theme Toggle Button --}}
    <button @click="open = !open" 
            class="relative p-2 text-gray-600 hover:text-purple-600 transition-all duration-300 rounded-xl hover:bg-gradient-to-r hover:from-purple-50 hover:to-pink-50 hover:shadow-lg hover:shadow-purple/20 transform hover:scale-105">
        {{-- Sun Icon for Light Mode --}}
        <x-lucide-sun x-show="$wire.currentTheme === 'light'" 
           class="w-5 h-5 transition-colors duration-300" />
        
        {{-- Moon Icon for Dark Mode --}}
        <x-lucide-moon x-show="$wire.currentTheme === 'dark'" 
           class="w-5 h-5 transition-colors duration-300"
           style="display: none;" />
        
        {{-- System Icon for System Mode --}}
        <x-lucide-monitor x-show="$wire.currentTheme === 'system'" 
           class="w-5 h-5 transition-colors duration-300"
           style="display: none;" />
    </button>
    
    {{-- Dropdown Menu --}}
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="transform opacity-0 scale-95 translate-y-2"
         class="absolute right-0 mt-2 w-48 bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-gray-200/30 dark:border-gray-700/30 py-3 z-50 transition-colors duration-300"
         style="display: none;">
        <div class="py-1">
            {{-- Light Mode Option --}}
            <button wire:click="setTheme('light')" 
                    class="w-full px-5 py-3 text-left text-sm hover:bg-gradient-to-r hover:from-yellow-50 hover:to-orange-50 dark:hover:from-yellow-900/20 dark:hover:to-orange-900/20 flex items-center space-x-3 transition-all duration-200 rounded-xl mx-2"
                    :class="{ 'bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20': $wire.currentTheme === 'light' }">
                <x-lucide-sun class="w-4 h-4 text-yellow-600 dark:text-yellow-400" />
                <span class="text-gray-700 dark:text-gray-300 transition-colors duration-300">Light</span>
                <x-lucide-check x-show="$wire.currentTheme === 'light'" 
                   class="ml-auto w-4 h-4 text-blue-600 dark:text-blue-400" />
            </button>
            
            {{-- Dark Mode Option --}}
            <button wire:click="setTheme('dark')" 
                    class="w-full px-5 py-3 text-left text-sm hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50 dark:hover:from-indigo-900/20 dark:hover:to-purple-900/20 flex items-center space-x-3 transition-all duration-200 rounded-xl mx-2"
                    :class="{ 'bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20': $wire.currentTheme === 'dark' }">
                <x-lucide-moon class="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                <span class="text-gray-700 dark:text-gray-300 transition-colors duration-300">Dark</span>
                <x-lucide-check x-show="$wire.currentTheme === 'dark'" 
                   class="ml-auto w-4 h-4 text-blue-600 dark:text-blue-400"
                   style="display: none;" />
            </button>
            
            {{-- System Mode Option --}}
            <button wire:click="setTheme('system')" 
                    class="w-full px-5 py-3 text-left text-sm hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50 dark:hover:from-gray-700/20 dark:hover:to-slate-700/20 flex items-center space-x-3 transition-all duration-200 rounded-xl mx-2"
                    :class="{ 'bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-700/20 dark:to-slate-700/20': $wire.currentTheme === 'system' }">
                <x-lucide-monitor class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                <span class="text-gray-700 dark:text-gray-300 transition-colors duration-300">System</span>
                <x-lucide-check x-show="$wire.currentTheme === 'system'" 
                   class="ml-auto w-4 h-4 text-blue-600 dark:text-blue-400"
                   style="display: none;" />
            </button>
        </div>
    </div>
</div>

{{-- Theme Application Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Theme switcher initialized');

        // Theme manager should already be initialized
        // Listen for Livewire theme changes and apply via theme manager
        window.addEventListener('theme-changed', function(e) {
            console.log('Theme changed to:', e.detail.theme);
            if (window.themeManager) {
                window.themeManager.applyTheme(e.detail.theme);
            }
        });

        // Ensure theme is applied immediately
        if (window.themeManager) {
            // Re-apply current theme to ensure consistency
            const currentTheme = window.themeManager.getCurrentTheme();
            console.log('Applying current theme:', currentTheme);
            window.themeManager.applyTheme(currentTheme);
        }

    });

</script>
