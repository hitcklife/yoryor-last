<section class="w-full min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-900 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 py-8">
        @include('partials.settings-heading')

        <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('Current password')"
                type="password"
                required
                autocomplete="current-password"
                class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-white"
            />
            <flux:input
                wire:model="password"
                :label="__('New password')"
                type="password"
                required
                autocomplete="new-password"
                class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-white"
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirm Password')"
                type="password"
                required
                autocomplete="new-password"
                class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-white"
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full dark:bg-purple-600 dark:hover:bg-purple-700">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3 dark:text-green-400" on="password-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
    </div>
</section>
