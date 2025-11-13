<div class="w-full max-w-md mx-auto" x-data="{ isVisible: true }"
     @hide-message.window="setTimeout(() => isVisible = false, 100)">

    @if($isSubmitted && $message)
        <div x-show="isVisible"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="glass-card rounded-xl p-4 mb-4 border-green-200/30 bg-green-50/80 animate-bounce-in">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-sm text-green-800 font-medium">{{ $message }}</p>
                <button wire:click="hideMessage" class="ml-auto text-green-500 hover:text-green-600 transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <form wire:submit="subscribe" class="space-y-4">
        <div class="relative">
            <input
                wire:model="email"
                type="email"
                placeholder="Enter your email address"
                class="w-full glass-input rounded-xl px-4 py-3 text-gray-800 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-pink-500/50 transition-all duration-200 @error('email') border-red-300 @enderror"
                required>

            @error('email')
                <div class="absolute -bottom-6 left-0 text-xs text-red-500 font-medium animate-fade-in">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <button
            type="submit"
            wire:loading.attr="disabled"
            class="w-full gradient-primary hover:shadow-purple text-white font-semibold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-pink-500/50 disabled:opacity-70 disabled:cursor-not-allowed disabled:transform-none">

            <span wire:loading.remove>Get Early Access</span>

            <span wire:loading class="flex items-center justify-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Subscribing...
            </span>
        </button>

        <p class="text-xs text-gray-500 text-center">
            Be the first to know when YorYor launches in Uzbekistan. No spam, ever.
        </p>
    </form>
</div>
