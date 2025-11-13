@props([
    'src' => '',
    'alt' => '',
    'placeholder' => null,
    'class' => '',
    'width' => null,
    'height' => null,
    'loading' => 'lazy'
])

@php
$placeholderSrc = $placeholder ?? 'data:image/svg+xml;base64,' . base64_encode('<svg width="400" height="300" xmlns="http://www.w3.org/2000/svg"><rect width="100%" height="100%" fill="#f3f4f6"/><text x="50%" y="50%" text-anchor="middle" dy=".3em" fill="#9ca3af" font-family="system-ui, sans-serif" font-size="14">Loading...</text></svg>');
@endphp

<div class="lazy-image-container {{ $class }}" 
     x-data="lazyImage()" 
     x-init="init('{{ $src }}', '{{ $alt }}', '{{ $placeholderSrc }}', {{ $width }}, {{ $height }})"
     {{ $attributes }}>
    
    <!-- Placeholder -->
    <div x-show="!loaded" 
         class="w-full h-full bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
        <div class="animate-pulse">
            <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
        </div>
    </div>
    
    <!-- Actual Image -->
    <img x-show="loaded" 
         :src="imageSrc" 
         :alt="imageAlt"
         class="w-full h-full object-cover rounded-lg transition-opacity duration-300"
         :class="{ 'opacity-0': !loaded, 'opacity-100': loaded }"
         @load="onLoad"
         @error="onError"
         loading="{{ $loading }}">
    
    <!-- Error State -->
    <div x-show="error" 
         class="w-full h-full bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center">
        <div class="text-center">
            <i data-lucide="image" class="w-8 h-8 text-gray-400 dark:text-gray-500 mx-auto mb-2"></i>
            <p class="text-xs text-gray-500 dark:text-gray-400">Failed to load</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function lazyImage() {
        return {
            loaded: false,
            error: false,
            imageSrc: '',
            imageAlt: '',
            
            init(src, alt, placeholder, width, height) {
                this.imageSrc = placeholder;
                this.imageAlt = alt;
                
                // Use Intersection Observer for lazy loading
                if ('IntersectionObserver' in window) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                this.loadImage(src);
                                observer.unobserve(entry.target);
                            }
                        });
                    }, {
                        rootMargin: '50px'
                    });
                    
                    observer.observe(this.$el);
                } else {
                    // Fallback for browsers without IntersectionObserver
                    this.loadImage(src);
                }
            },
            
            loadImage(src) {
                const img = new Image();
                img.onload = () => {
                    this.imageSrc = src;
                    this.loaded = true;
                };
                img.onerror = () => {
                    this.error = true;
                };
                img.src = src;
            },
            
            onLoad() {
                this.loaded = true;
                this.error = false;
            },
            
            onError() {
                this.error = true;
                this.loaded = false;
            }
        }
    }
</script>
@endpush
