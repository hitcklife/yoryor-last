<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-2xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-4 transition-colors duration-300">
            <flux:navlist>
                <flux:navlist.item :href="route('settings.profile')" wire:navigate class="dark:text-zinc-300 dark:hover:text-white dark:hover:bg-zinc-700/50 rounded-lg px-3 py-2 transition-colors duration-200">{{ __('Profile') }}</flux:navlist.item>
                <flux:navlist.item :href="route('settings.password')" wire:navigate class="dark:text-zinc-300 dark:hover:text-white dark:hover:bg-zinc-700/50 rounded-lg px-3 py-2 transition-colors duration-200">{{ __('Password') }}</flux:navlist.item>
                <flux:navlist.item :href="route('settings.appearance')" wire:navigate class="dark:text-zinc-300 dark:hover:text-white dark:hover:bg-zinc-700/50 rounded-lg px-3 py-2 transition-colors duration-200">{{ __('Appearance') }}</flux:navlist.item>
            </flux:navlist>
        </div>
    </div>

    <flux:separator class="md:hidden dark:border-zinc-700" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <div class="bg-white/70 dark:bg-zinc-800/70 backdrop-blur-sm rounded-2xl shadow-sm border border-white/50 dark:border-zinc-700/50 p-6 transition-colors duration-300">
            <flux:heading class="dark:text-white">{{ $heading ?? '' }}</flux:heading>
            <flux:subheading class="dark:text-zinc-400">{{ $subheading ?? '' }}</flux:subheading>

            <div class="mt-5 w-full max-w-lg">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
