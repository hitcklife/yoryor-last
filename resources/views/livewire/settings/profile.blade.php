<section class="w-full min-h-screen bg-gradient-to-br from-purple-50 via-pink-50 to-indigo-50 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-900 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 py-8">
        @include('partials.settings-heading')

        <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-white" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-white" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4 dark:text-zinc-300">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer dark:text-purple-400 dark:hover:text-purple-300" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full dark:bg-purple-600 dark:hover:bg-purple-700">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3 dark:text-green-400" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
    </div>
</section>
