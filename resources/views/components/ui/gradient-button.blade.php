@props([
    'variant' => 'primary', // primary, secondary, accent
    'size' => 'base', // sm, base, lg, xl
    'type' => 'button',
    'disabled' => false,
    'loading' => false,
    'icon' => null,
    'iconPosition' => 'left', // left, right
])

@php
$baseClasses = 'inline-flex items-center justify-center font-semibold transition-all duration-300 ease-out transform border-0 focus:outline-none focus:ring-4 focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed';

$variantClasses = [
    'primary' => 'gradient-primary text-white shadow-pink hover:shadow-lg hover:-translate-y-0.5 focus:ring-pink-300',
    'secondary' => 'gradient-secondary text-white shadow-purple hover:shadow-lg hover:-translate-y-0.5 focus:ring-purple-300', 
    'accent' => 'gradient-accent text-white shadow-indigo hover:shadow-lg hover:-translate-y-0.5 focus:ring-indigo-300',
    'glass' => 'glass-button text-pink-600 hover:text-pink-700 hover:bg-white/20 focus:ring-pink-300',
    'outline' => 'border-2 border-pink-500 text-pink-600 hover:bg-pink-50 focus:ring-pink-300',
][$variant] ?? 'gradient-primary text-white shadow-pink hover:shadow-lg hover:-translate-y-0.5 focus:ring-pink-300';

$sizeClasses = [
    'sm' => 'px-3 py-2 text-sm rounded-lg min-h-[2.25rem]',
    'base' => 'px-6 py-3 text-base rounded-xl min-h-[2.75rem]',
    'lg' => 'px-8 py-4 text-lg rounded-xl min-h-[3rem]',
    'xl' => 'px-10 py-5 text-xl rounded-2xl min-h-[3.5rem]',
][$size] ?? 'px-6 py-3 text-base rounded-xl min-h-[2.75rem]';

$classes = trim("{$baseClasses} {$variantClasses} {$sizeClasses}");
@endphp

<button 
    type="{{ $type }}" 
    {{ $attributes->merge(['class' => $classes]) }}
    @if($disabled || $loading) disabled @endif
>
    @if($loading)
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Loading...
    @else
        @if($icon && $iconPosition === 'left')
            <span class="mr-2">
                {!! $icon !!}
            </span>
        @endif
        
        {{ $slot }}
        
        @if($icon && $iconPosition === 'right')
            <span class="ml-2">
                {!! $icon !!}
            </span>
        @endif
    @endif
</button>