<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading class="dark:text-white">{{ __('Delete account') }}</flux:heading>
        <flux:subheading class="dark:text-zinc-400">{{ __('Delete your account and all of its resources') }}</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="dark:bg-red-600 dark:hover:bg-red-700 dark:text-white">
            {{ __('Delete account') }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg dark:bg-zinc-800 dark:border-zinc-700">
        <form wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg" class="dark:text-white">{{ __('Are you sure you want to delete your account?') }}</flux:heading>

                <flux:subheading class="dark:text-zinc-400">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" :label="__('Password')" type="password" class="dark:bg-zinc-700 dark:border-zinc-600 dark:text-white" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled" class="dark:bg-zinc-600 dark:hover:bg-zinc-700 dark:text-white">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit" class="dark:bg-red-600 dark:hover:bg-red-700 dark:text-white">{{ __('Delete account') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
