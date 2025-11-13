@props([
    'size' => 'md',
    'color' => 'blue',
    'text' => null,
    'overlay' => false
])

@php
$sizeClasses = [
    'sm' => 'w-4 h-4',
    'md' => 'w-6 h-6',
    'lg' => 'w-8 h-8',
    'xl' => 'w-12 h-12'
];

$colorClasses = [
    'blue' => 'text-blue-600',
    'gray' => 'text-gray-600',
    'white' => 'text-white',
    'green' => 'text-green-600',
    'red' => 'text-red-600',
    'yellow' => 'text-yellow-600'
];

$sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
$colorClass = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<div @if($overlay) class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @endif>
    <div class="flex flex-col items-center justify-center {{ $overlay ? '' : 'p-4' }}">
        <!-- Spinner -->
        <div class="animate-spin rounded-full border-2 border-gray-300 border-t-{{ $color === 'blue' ? 'blue' : $color }}-600 {{ $sizeClass }} {{ $colorClass }}"></div>
        
        @if($text)
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $text }}</p>
        @endif
    </div>
</div>
