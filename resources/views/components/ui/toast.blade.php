@props([
    'type' => 'success',
    'title' => null,
    'message' => null,
    'duration' => 5000,
    'dismissible' => true,
    'position' => 'top-right'
])

@php
$typeConfig = [
    'success' => [
        'icon' => 'check-circle',
        'color' => 'text-green-600',
        'bgColor' => 'bg-green-50',
        'borderColor' => 'border-green-200',
        'iconColor' => 'text-green-400'
    ],
    'error' => [
        'icon' => 'x-circle',
        'color' => 'text-red-600',
        'bgColor' => 'bg-red-50',
        'borderColor' => 'border-red-200',
        'iconColor' => 'text-red-400'
    ],
    'warning' => [
        'icon' => 'alert-triangle',
        'color' => 'text-yellow-600',
        'bgColor' => 'bg-yellow-50',
        'borderColor' => 'border-yellow-200',
        'iconColor' => 'text-yellow-400'
    ],
    'info' => [
        'icon' => 'info',
        'color' => 'text-blue-600',
        'bgColor' => 'bg-blue-50',
        'borderColor' => 'border-blue-200',
        'iconColor' => 'text-blue-400'
    ]
];

$positionClasses = [
    'top-right' => 'top-4 right-4',
    'top-left' => 'top-4 left-4',
    'bottom-right' => 'bottom-4 right-4',
    'bottom-left' => 'bottom-4 left-4',
    'top-center' => 'top-4 left-1/2 transform -translate-x-1/2',
    'bottom-center' => 'bottom-4 left-1/2 transform -translate-x-1/2'
];

$config = $typeConfig[$type] ?? $typeConfig['info'];
$positionClass = $positionClasses[$position] ?? $positionClasses['top-right'];
@endphp

<div class="fixed {{ $positionClass }} z-50 max-w-sm w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg border {{ $config['borderColor'] }} {{ $config['bgColor'] }} p-4 transform transition-all duration-300 ease-in-out"
     x-data="{ show: true }"
     x-show="show"
     x-transition:enter="transform ease-out duration-300 transition"
     x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
     x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-100"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @if($duration > 0)
     x-init="setTimeout(() => show = false, {{ $duration }})"
     @endif>
    
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <i data-lucide="{{ $config['icon'] }}" class="w-5 h-5 {{ $config['iconColor'] }}"></i>
        </div>
        
        <div class="ml-3 w-0 flex-1">
            @if($title)
                <p class="text-sm font-medium {{ $config['color'] }} dark:text-gray-900">
                    {{ $title }}
                </p>
            @endif
            
            @if($message)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $message }}
                </p>
            @endif
        </div>
        
        @if($dismissible)
            <div class="ml-4 flex-shrink-0 flex">
                <button @click="show = false"
                        class="inline-flex text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:text-gray-500 transition ease-in-out duration-150">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Initialize Lucide icons for toast
    document.addEventListener('DOMContentLoaded', function() {
        if (window.lucide && window.lucide.createIcons) {
            window.lucide.createIcons({ icons: window.lucide.icons });
        }
    });
</script>
@endpush
