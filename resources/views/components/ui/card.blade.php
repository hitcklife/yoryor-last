@props([
    'variant' => 'default',
    'padding' => 'md',
    'hover' => false,
    'clickable' => false
])

@php
    $baseClasses = 'bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-xl transition-all duration-200';
    
    $variantClasses = match($variant) {
        'default' => 'shadow-sm',
        'elevated' => 'shadow-lg',
        'flat' => 'shadow-none',
        'gradient' => 'bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-purple-200 dark:border-purple-700',
        'success' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-700',
        'warning' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-700',
        'danger' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-700',
        'info' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-700',
        default => 'shadow-sm'
    };
    
    $paddingClasses = match($padding) {
        'none' => '',
        'sm' => 'p-4',
        'md' => 'p-6',
        'lg' => 'p-8',
        'xl' => 'p-10',
        default => 'p-6'
    };
    
    $interactionClasses = '';
    if ($hover) {
        $interactionClasses .= ' hover:shadow-md';
    }
    if ($clickable) {
        $interactionClasses .= ' cursor-pointer hover:scale-[1.02] active:scale-[0.98]';
    }
    
    $classes = $baseClasses . ' ' . $variantClasses . ' ' . $paddingClasses . ' ' . $interactionClasses;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
