@props([
    'steps' => [],
    'currentStep' => 1,
    'orientation' => 'horizontal', // horizontal, vertical
    'size' => 'base', // sm, base, lg
    'showLabels' => true,
    'clickable' => false,
])

@php
$containerClasses = $orientation === 'horizontal' 
    ? 'flex items-center justify-between w-full' 
    : 'flex flex-col space-y-4';

$stepSizes = [
    'sm' => 'w-8 h-8 text-sm',
    'base' => 'w-10 h-10 text-base',
    'lg' => 'w-12 h-12 text-lg',
];

$stepSize = $stepSizes[$size] ?? $stepSizes['base'];

$connectorClasses = $orientation === 'horizontal'
    ? 'flex-1 h-0.5 mx-2'
    : 'w-0.5 h-8 ml-5';
@endphp

<div class="{{ $containerClasses }}" {{ $attributes->whereDoesntStartWith('class') }}>
    @foreach($steps as $index => $step)
        @php
        $stepNumber = $index + 1;
        $isActive = $stepNumber === $currentStep;
        $isCompleted = $stepNumber < $currentStep;
        $isUpcoming = $stepNumber > $currentStep;
        
        $stepClasses = collect([
            'relative flex items-center justify-center rounded-full font-semibold transition-all duration-300',
            $stepSize,
            
            // State classes
            $isCompleted ? 'bg-gradient-to-r from-pink-500 to-purple-600 text-white shadow-md' : '',
            $isActive ? 'bg-gradient-to-r from-pink-500 to-purple-600 text-white shadow-lg ring-4 ring-pink-300 ring-opacity-50' : '',
            $isUpcoming ? 'bg-gray-200 text-gray-500 border-2 border-gray-300' : '',
            
            // Interactive
            $clickable && !$isUpcoming ? 'cursor-pointer hover:scale-105' : '',
        ])->filter()->implode(' ');
        
        $labelClasses = collect([
            'text-sm font-medium transition-colors duration-200',
            $isActive ? 'text-pink-600' : '',
            $isCompleted ? 'text-gray-700' : '',
            $isUpcoming ? 'text-gray-400' : '',
        ])->filter()->implode(' ');
        @endphp
        
        <div class="flex {{ $orientation === 'horizontal' ? 'flex-col items-center' : 'items-center space-x-3' }}">
            <!-- Step Circle -->
            <div 
                class="{{ $stepClasses }}"
                @if($clickable && !$isUpcoming)
                    @click="$dispatch('step-clicked', { step: {{ $stepNumber }} })"
                @endif
            >
                @if($isCompleted)
                    <!-- Checkmark for completed steps -->
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                @else
                    <!-- Step number -->
                    {{ $stepNumber }}
                @endif
            </div>
            
            @if($showLabels && isset($step['label']))
                <div class="{{ $orientation === 'horizontal' ? 'mt-2 text-center' : '' }}">
                    <div class="{{ $labelClasses }}">{{ $step['label'] }}</div>
                    @if(isset($step['description']) && $orientation !== 'horizontal')
                        <div class="text-xs text-gray-500 mt-1">{{ $step['description'] }}</div>
                    @endif
                </div>
            @endif
        </div>
        
        <!-- Connector Line (except for last step) -->
        @if(!$loop->last && $orientation === 'horizontal')
            <div class="{{ $connectorClasses }} {{ $isCompleted ? 'bg-gradient-to-r from-pink-500 to-purple-600' : 'bg-gray-300' }}"></div>
        @elseif(!$loop->last && $orientation === 'vertical')
            <div class="{{ $connectorClasses }} {{ $isCompleted ? 'bg-gradient-to-b from-pink-500 to-purple-600' : 'bg-gray-300' }}"></div>
        @endif
    @endforeach
</div>

@if($clickable)
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('stepIndicator', () => ({
        currentStep: {{ $currentStep }},
        
        init() {
            this.$watch('currentStep', (value) => {
                this.$dispatch('current-step-changed', { step: value });
            });
        }
    }));
});
</script>
@endif