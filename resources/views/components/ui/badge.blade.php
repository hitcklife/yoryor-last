@props([
    'variant' => 'default',
    'size' => 'md',
    'icon' => null,
    'removable' => false
])

@php
    $baseClasses = 'inline-flex items-center font-medium rounded-full transition-colors duration-200';
    
    $variantClasses = match($variant) {
        'default' => 'bg-gray-100 text-gray-800 dark:bg-zinc-700 dark:text-gray-300',
        'primary' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        'success' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
        'danger' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
        'outline' => 'border border-gray-300 text-gray-700 bg-white dark:border-zinc-600 dark:text-gray-300 dark:bg-zinc-700',
        default => 'bg-gray-100 text-gray-800 dark:bg-zinc-700 dark:text-gray-300'
    };
    
    $sizeClasses = match($size) {
        'xs' => 'px-2 py-0.5 text-xs',
        'sm' => 'px-2.5 py-1 text-xs',
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-4 py-1.5 text-sm',
        default => 'px-3 py-1 text-sm'
    };
    
    $classes = $baseClasses . ' ' . $variantClasses . ' ' . $sizeClasses;
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"></path>
        </svg>
    @endif
    
    {{ $slot }}
    
    @if($removable)
        <button type="button" class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-black hover:bg-opacity-10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
            <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    @endif
</span>
