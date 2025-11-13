@props([
    'type' => 'text',
    'size' => 'md',
    'error' => false,
    'label' => null,
    'help' => null,
    'required' => false,
    'disabled' => false
])

@php
    $baseClasses = 'block w-full rounded-lg border transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-2 text-sm',
        'md' => 'px-4 py-2.5 text-sm',
        'lg' => 'px-4 py-3 text-base',
        default => 'px-4 py-2.5 text-sm'
    };
    
    $stateClasses = $error 
        ? 'border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500 dark:border-red-600 dark:bg-red-900/20 dark:text-red-300'
        : 'border-gray-300 text-gray-900 placeholder-gray-400 focus:border-purple-500 focus:ring-purple-500 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:placeholder-gray-500';
    
    $classes = $baseClasses . ' ' . $sizeClasses . ' ' . $stateClasses;
@endphp

<div class="space-y-1">
    @if($label)
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <input 
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes, 'disabled' => $disabled]) }}
    />
    
    @if($help && !$error)
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $help }}</p>
    @endif
    
    @if($error)
        <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
    @endif
</div>
