@props([
    'model' => '',
    'id' => '',
    'size' => 'md', // sm, md, lg
    'color' => 'purple', // purple, red, blue, green
    'disabled' => false,
    'wireModel' => null,
    'wireChange' => null,
])

@php
    $sizeClasses = [
        'sm' => 'w-8 h-4',
        'md' => 'w-12 h-6', 
        'lg' => 'w-16 h-8'
    ];
    
    $thumbSizeClasses = [
        'sm' => 'w-3 h-3 top-0.5 left-0.5',
        'md' => 'w-5 h-5 top-0.5 left-0.5',
        'lg' => 'w-7 h-7 top-0.5 left-0.5'
    ];
    
    $translateClasses = [
        'sm' => 'translate-x-4',
        'md' => 'translate-x-6',
        'lg' => 'translate-x-8'
    ];
    
    $colorClasses = [
        'purple' => 'bg-purple-600 dark:bg-purple-500',
        'red' => 'bg-red-600 dark:bg-red-500',
        'blue' => 'bg-blue-600 dark:bg-blue-500',
        'green' => 'bg-green-600 dark:bg-green-500'
    ];
    
    $trackSize = $sizeClasses[$size];
    $thumbSize = $thumbSizeClasses[$size];
    $translateSize = $translateClasses[$size];
    $colorClass = $colorClasses[$color];
    
    $inputId = $id ?: 'toggle-' . uniqid();
    $wireModelAttr = $wireModel ? "wire:model=\"{$wireModel}\"" : '';
    $wireChangeAttr = $wireChange ? "wire:change=\"{$wireChange}\"" : '';
@endphp

<div class="relative">
    <input 
        type="checkbox" 
        id="{{ $inputId }}" 
        {{ $wireModelAttr }}
        {{ $wireChangeAttr }}
        {{ $disabled ? 'disabled' : '' }}
        class="sr-only peer"
        {{ $attributes->except(['class']) }}
    >
    <label for="{{ $inputId }}" class="flex items-center cursor-pointer {{ $disabled ? 'cursor-not-allowed opacity-50' : '' }}">
        <div class="relative">
            <!-- Track -->
            <div class="{{ $trackSize }} bg-gray-200 dark:bg-zinc-600 rounded-full transition-colors duration-200 ease-in-out peer-checked:{{ $colorClass }} {{ $disabled ? 'opacity-50' : '' }}"></div>
            <!-- Thumb -->
            <div class="absolute {{ $thumbSize }} bg-white dark:bg-zinc-200 rounded-full transition-transform duration-200 ease-in-out transform peer-checked:{{ $translateSize }} {{ $disabled ? 'opacity-50' : '' }}"></div>
        </div>
    </label>
</div>
