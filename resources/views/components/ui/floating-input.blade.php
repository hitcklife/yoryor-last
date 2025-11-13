@props([
    'id' => null,
    'label' => '',
    'type' => 'text',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'icon' => null,
    'iconPosition' => 'right', // left, right
    'size' => 'base', // sm, base, lg
])

@php
$inputId = $id ?? 'input-' . Str::random(6);

$containerClasses = 'relative';

$inputClasses = collect([
    'w-full transition-all duration-200 ease-out border-0 focus:outline-none focus:ring-0 bg-transparent',
    'peer placeholder-transparent',
    
    // Size variants
    'size' => [
        'sm' => 'px-4 py-3 text-sm',
        'base' => 'px-4 py-4 text-base',
        'lg' => 'px-4 py-5 text-lg',
    ][$size] ?? 'px-4 py-4 text-base',
    
    // Icon spacing
    $icon && $iconPosition === 'left' ? 'pl-12' : '',
    $icon && $iconPosition === 'right' ? 'pr-12' : '',
    
    // Error state
    $error ? 'text-red-600' : 'text-gray-900',
])->filter()->implode(' ');

$wrapperClasses = collect([
    'relative rounded-xl border-2 transition-all duration-200 ease-out',
    'bg-white/80 backdrop-blur-sm',
    
    // Focus and error states
    $error 
        ? 'border-red-300 focus-within:border-red-500 focus-within:ring-4 focus-within:ring-red-500/10' 
        : 'border-gray-200 focus-within:border-pink-500 focus-within:ring-4 focus-within:ring-pink-500/10',
])->filter()->implode(' ');

$labelClasses = collect([
    'absolute left-4 transition-all duration-200 ease-out pointer-events-none',
    'peer-placeholder-shown:text-gray-500 peer-focus:text-pink-600',
    
    // Size-based positioning
    'positioning' => [
        'sm' => 'peer-placeholder-shown:top-3 peer-focus:-top-2 peer-focus:text-sm',
        'base' => 'peer-placeholder-shown:top-4 peer-focus:-top-2 peer-focus:text-sm',
        'lg' => 'peer-placeholder-shown:top-5 peer-focus:-top-2 peer-focus:text-sm',
    ][$size] ?? 'peer-placeholder-shown:top-4 peer-focus:-top-2 peer-focus:text-sm',
    
    // Background for floating effect
    'peer-focus:bg-white peer-focus:px-2 peer-focus:rounded',
    
    // Error state
    $error ? 'text-red-600 peer-focus:text-red-600' : '',
    
    // Required indicator
    $required ? "after:content-['*'] after:text-red-500 after:ml-1" : '',
])->filter()->implode(' ');
@endphp

<div class="{{ $containerClasses }}">
    <div class="{{ $wrapperClasses }}">
        @if($icon && $iconPosition === 'left')
            <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                {!! $icon !!}
            </div>
        @endif
        
        <input 
            type="{{ $type }}"
            id="{{ $inputId }}"
            class="{{ $inputClasses }}"
            placeholder=" "
            @if($disabled) disabled @endif
            @if($required) required @endif
            {{ $attributes->whereDoesntStartWith('class') }}
        />
        
        <label for="{{ $inputId }}" class="{{ $labelClasses }}">
            {{ $label }}
        </label>
        
        @if($icon && $iconPosition === 'right')
            <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                {!! $icon !!}
            </div>
        @endif
    </div>
    
    @if($error)
        <p class="mt-2 text-sm text-red-600 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ $error }}
        </p>
    @endif
</div>