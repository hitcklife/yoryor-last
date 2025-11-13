<!-- Loading Optimizations and Performance Scripts -->
<script>
// Performance optimization script
document.addEventListener('DOMContentLoaded', function() {
    // Lazy loading for images
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                imageObserver.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));

    // Skip preload since Vite handles this automatically

    // Optimize animations for better performance
    const animatedElements = document.querySelectorAll('[class*="animate-"]');
    const animationObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animationPlayState = 'running';
            } else {
                entry.target.style.animationPlayState = 'paused';
            }
        });
    });

    animatedElements.forEach(el => animationObserver.observe(el));

    // Debounce scroll events
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        if (scrollTimeout) {
            clearTimeout(scrollTimeout);
        }
        scrollTimeout = setTimeout(function() {
            // Handle scroll events here
        }, 10);
    });

    // Optimize resize events
    let resizeTimeout;
    window.addEventListener('resize', function() {
        if (resizeTimeout) {
            clearTimeout(resizeTimeout);
        }
        resizeTimeout = setTimeout(function() {
            // Handle resize events here
        }, 250);
    });
});

// Service Worker registration for caching
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('SW registered: ', registration);
            })
            .catch(function(registrationError) {
                console.log('SW registration failed: ', registrationError);
            });
    });
}

// Critical CSS inlining
const criticalCSS = `
    .animate-fade-in { animation: fadeIn 0.6s ease-out; }
    .animate-slide-up { animation: slideUp 0.8s ease-out; }
    .animate-bounce { animation: bounce 2s infinite; }
    .animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    .animate-float-slow { animation: floatSlow 6s ease-in-out infinite; }
    .animate-float-reverse { animation: floatReverse 8s ease-in-out infinite; }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes floatSlow {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    
    @keyframes floatReverse {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(20px); }
    }
`;

// Inject critical CSS
const style = document.createElement('style');
style.textContent = criticalCSS;
document.head.appendChild(style);

// Resource hints for better performance
const resourceHints = [
    { rel: 'dns-prefetch', href: '//fonts.googleapis.com' },
    { rel: 'dns-prefetch', href: '//images.unsplash.com' },
    { rel: 'preconnect', href: '//fonts.gstatic.com' },
    { rel: 'preconnect', href: '//images.unsplash.com' }
];

resourceHints.forEach(hint => {
    const link = document.createElement('link');
    link.rel = hint.rel;
    link.href = hint.href;
    document.head.appendChild(link);
});
</script>

<!-- Loading States and Skeleton Screens -->
<style>
/* Skeleton loading animations */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Loading spinner */
.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Progressive image loading */
.progressive-image {
    filter: blur(5px);
    transition: filter 0.3s;
}

.progressive-image.loaded {
    filter: blur(0);
}

/* Intersection Observer fallback */
.no-intersection-observer .animate-fade-in,
.no-intersection-observer .animate-slide-up {
    animation: none;
    opacity: 1;
    transform: none;
}

/* Reduced motion preferences */
@media (prefers-reduced-motion: reduce) {
    .animate-fade-in,
    .animate-slide-up,
    .animate-bounce,
    .animate-pulse,
    .animate-float-slow,
    .animate-float-reverse {
        animation: none;
    }
}

/* Performance optimizations */
.gpu-accelerated {
    transform: translateZ(0);
    will-change: transform;
}

/* Lazy loading placeholder */
.lazy-placeholder {
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-size: 14px;
}
</style>

<!-- Performance monitoring -->
<script>
// Performance monitoring
window.addEventListener('load', function() {
    // Measure page load time with proper error handling
    const navigationStart = performance.timing.navigationStart;
    const loadEventEnd = performance.timing.loadEventEnd;
    
    if (loadEventEnd > 0 && navigationStart > 0) {
        const loadTime = loadEventEnd - navigationStart;
        console.log('Page load time:', loadTime + 'ms');
    } else {
        console.log('Page load time: Unable to measure (timing not available)');
    }
    
    // Measure First Contentful Paint
    const paintEntries = performance.getEntriesByType('paint');
    paintEntries.forEach(entry => {
        if (entry.name === 'first-contentful-paint') {
            console.log('First Contentful Paint:', entry.startTime + 'ms');
        }
    });
    
    // Measure Largest Contentful Paint
    const observer = new PerformanceObserver((list) => {
        const entries = list.getEntries();
        const lastEntry = entries[entries.length - 1];
        console.log('Largest Contentful Paint:', lastEntry.startTime + 'ms');
    });
    observer.observe({ entryTypes: ['largest-contentful-paint'] });
});

// Memory usage monitoring (development only) 
if (typeof window !== 'undefined' && window.location.hostname === 'localhost') {
    setInterval(() => {
        if (performance.memory) {
            console.log('Memory usage:', {
                used: Math.round(performance.memory.usedJSHeapSize / 1048576) + ' MB',
                total: Math.round(performance.memory.totalJSHeapSize / 1048576) + ' MB',
                limit: Math.round(performance.memory.jsHeapSizeLimit / 1048576) + ' MB'
            });
        }
    }, 10000);
}
</script>

<!-- Accessibility Features -->
@include('landing.home.accessibility-features')
