@props([
    'type' => 'card',
    'lines' => 3,
    'height' => 'h-4'
])

@php
$skeletonTypes = [
    'card' => 'rounded-lg',
    'avatar' => 'rounded-full',
    'text' => 'rounded',
    'button' => 'rounded-md',
    'image' => 'rounded-lg'
];
@endphp

<div class="animate-pulse">
    @if($type === 'card')
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center space-x-4 mb-4">
                <div class="w-12 h-12 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-3/4"></div>
                    <div class="h-3 bg-gray-300 dark:bg-gray-600 rounded w-1/2"></div>
                </div>
            </div>
            <div class="space-y-2">
                @for($i = 0; $i < $lines; $i++)
                    <div class="h-3 bg-gray-300 dark:bg-gray-600 rounded {{ $i === $lines - 1 ? 'w-2/3' : 'w-full' }}"></div>
                @endfor
            </div>
        </div>
    @elseif($type === 'list')
        <div class="space-y-4">
            @for($i = 0; $i < $lines; $i++)
                <div class="flex items-center space-x-4 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="w-10 h-10 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-3/4"></div>
                        <div class="h-3 bg-gray-300 dark:bg-gray-600 rounded w-1/2"></div>
                    </div>
                    <div class="w-6 h-6 bg-gray-300 dark:bg-gray-600 rounded"></div>
                </div>
            @endfor
        </div>
    @elseif($type === 'avatar')
        <div class="w-12 h-12 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
    @elseif($type === 'text')
        <div class="space-y-2">
            @for($i = 0; $i < $lines; $i++)
                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded {{ $i === $lines - 1 ? 'w-2/3' : 'w-full' }}"></div>
            @endfor
        </div>
    @elseif($type === 'button')
        <div class="h-10 bg-gray-300 dark:bg-gray-600 rounded-md w-24"></div>
    @elseif($type === 'image')
        <div class="w-full h-48 bg-gray-300 dark:bg-gray-600 rounded-lg"></div>
    @elseif($type === 'table')
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-1/4"></div>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @for($i = 0; $i < $lines; $i++)
                    <div class="p-4 flex items-center space-x-4">
                        <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-4 bg-gray-300 dark:bg-gray-600 rounded w-1/3"></div>
                            <div class="h-3 bg-gray-300 dark:bg-gray-600 rounded w-1/2"></div>
                        </div>
                        <div class="w-16 h-4 bg-gray-300 dark:bg-gray-600 rounded"></div>
                    </div>
                @endfor
            </div>
        </div>
    @endif
</div>
