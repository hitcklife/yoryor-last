@props([
    'progress' => 0, // 0-100
    'size' => 'base', // sm, base, lg
    'thickness' => 'base', // thin, base, thick
    'color' => 'primary', // primary, secondary, accent, success, warning, error
    'showPercentage' => true,
    'animated' => true,
])

@php
$sizes = [
    'sm' => ['width' => 48, 'height' => 48],
    'base' => ['width' => 64, 'height' => 64],
    'lg' => ['width' => 80, 'height' => 80],
    'xl' => ['width' => 96, 'height' => 96],
];

$thicknesses = [
    'thin' => 2,
    'base' => 3,
    'thick' => 4,
];

$colors = [
    'primary' => 'stroke-pink-500',
    'secondary' => 'stroke-purple-500',
    'accent' => 'stroke-indigo-500',
    'success' => 'stroke-green-500',
    'warning' => 'stroke-yellow-500',
    'error' => 'stroke-red-500',
];

$dimension = $sizes[$size] ?? $sizes['base'];
$strokeWidth = $thicknesses[$thickness] ?? $thicknesses['base'];
$strokeColor = $colors[$color] ?? $colors['primary'];

$radius = ($dimension['width'] / 2) - ($strokeWidth * 2);
$circumference = 2 * pi() * $radius;
$strokeDasharray = $circumference;
$strokeDashoffset = $circumference - ($progress / 100) * $circumference;

$textSize = [
    'sm' => 'text-xs',
    'base' => 'text-sm',
    'lg' => 'text-base',
    'xl' => 'text-lg',
][$size] ?? 'text-sm';
@endphp

<div class="relative inline-flex items-center justify-center" style="width: {{ $dimension['width'] }}px; height: {{ $dimension['height'] }}px;">
    <svg 
        class="transform -rotate-90 {{ $animated ? 'transition-all duration-500 ease-out' : '' }}" 
        width="{{ $dimension['width'] }}" 
        height="{{ $dimension['height'] }}"
        {{ $attributes->whereDoesntStartWith('class') }}
    >
        <!-- Background circle -->
        <circle
            cx="{{ $dimension['width'] / 2 }}"
            cy="{{ $dimension['height'] / 2 }}"
            r="{{ $radius }}"
            stroke="currentColor"
            stroke-width="{{ $strokeWidth }}"
            fill="transparent"
            class="text-gray-200"
        />
        
        <!-- Progress circle -->
        <circle
            cx="{{ $dimension['width'] / 2 }}"
            cy="{{ $dimension['height'] / 2 }}"
            r="{{ $radius }}"
            stroke="currentColor"
            stroke-width="{{ $strokeWidth }}"
            fill="transparent"
            class="{{ $strokeColor }}"
            stroke-dasharray="{{ $strokeDasharray }}"
            stroke-dashoffset="{{ $strokeDashoffset }}"
            stroke-linecap="round"
            style="{{ $animated ? 'transition: stroke-dashoffset 0.5s ease-out;' : '' }}"
        />
    </svg>
    
    @if($showPercentage)
        <div class="absolute inset-0 flex items-center justify-center">
            <span class="font-semibold {{ $textSize }} {{ str_replace('stroke-', 'text-', $strokeColor) }}">
                {{ round($progress) }}%
            </span>
        </div>
    @else
        <div class="absolute inset-0 flex items-center justify-center">
            {{ $slot }}
        </div>
    @endif
</div>