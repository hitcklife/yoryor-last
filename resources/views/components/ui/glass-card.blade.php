@props([
    'size' => 'base', // sm, base, lg
    'padding' => 'base', // sm, base, lg, none
    'hover' => false,
    'interactive' => false,
])

@php
$classes = [
    'glass-card rounded-xl transition-all duration-300',
    
    // Size variants
    'size' => [
        'sm' => 'max-w-sm',
        'base' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        'full' => 'w-full',
    ][$size] ?? 'max-w-md',
    
    // Padding variants
    'padding' => [
        'none' => '',
        'sm' => 'p-4',
        'base' => 'p-6',
        'lg' => 'p-8',
    ][$padding] ?? 'p-6',
    
    // Interactive states
    'hover' => $hover ? 'hover:shadow-lg hover:-translate-y-1' : '',
    'interactive' => $interactive ? 'cursor-pointer select-none' : '',
];

$classString = collect($classes)->filter()->implode(' ');
@endphp

<div {{ $attributes->merge(['class' => $classString]) }}>
    {{ $slot }}
</div>