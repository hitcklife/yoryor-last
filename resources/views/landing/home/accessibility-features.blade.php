<!-- Accessibility Features and Scripts -->
<script>
// Accessibility enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Skip to main content link
    const skipLink = document.createElement('a');
    skipLink.href = '#main-content';
    skipLink.textContent = 'Skip to main content';
    skipLink.className = 'sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded z-50';
    document.body.insertBefore(skipLink, document.body.firstChild);

    // Keyboard navigation for carousels
    const carousels = document.querySelectorAll('[x-data*="currentSlide"]');
    carousels.forEach(carousel => {
        carousel.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                const prevButton = carousel.querySelector('button[\\@click*="prevSlide"]');
                if (prevButton) prevButton.click();
            } else if (e.key === 'ArrowRight') {
                const nextButton = carousel.querySelector('button[\\@click*="nextSlide"]');
                if (nextButton) nextButton.click();
            }
        });
    });

    // Focus management for modals and dropdowns
    const modals = document.querySelectorAll('[x-show]');
    modals.forEach(modal => {
        modal.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const closeButton = modal.querySelector('[\\@click*="close"], [\\@click*="toggle"]');
                if (closeButton) closeButton.click();
            }
        });
    });

    // Announce dynamic content changes to screen readers
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                const addedNode = mutation.addedNodes[0];
                if (addedNode.nodeType === Node.ELEMENT_NODE) {
                    const ariaLive = addedNode.querySelector('[aria-live]');
                    if (ariaLive) {
                        // Content will be announced automatically
                    }
                }
            }
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // High contrast mode detection
    if (window.matchMedia('(prefers-contrast: high)').matches) {
        document.body.classList.add('high-contrast');
    }

    // Reduced motion detection
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.body.classList.add('reduced-motion');
    }

    // Focus visible for better keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });

    document.addEventListener('mousedown', function() {
        document.body.classList.remove('keyboard-navigation');
    });
});

// Screen reader announcements
function announceToScreenReader(message) {
    const announcement = document.createElement('div');
    announcement.setAttribute('aria-live', 'polite');
    announcement.setAttribute('aria-atomic', 'true');
    announcement.className = 'sr-only';
    announcement.textContent = message;
    document.body.appendChild(announcement);
    
    setTimeout(() => {
        document.body.removeChild(announcement);
    }, 1000);
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Alt + M for main menu
    if (e.altKey && e.key === 'm') {
        e.preventDefault();
        const menuButton = document.querySelector('[aria-label*="menu"], [aria-label*="Menu"]');
        if (menuButton) menuButton.focus();
    }
    
    // Alt + S for search
    if (e.altKey && e.key === 's') {
        e.preventDefault();
        const searchInput = document.querySelector('input[type="search"], input[placeholder*="search" i]');
        if (searchInput) searchInput.focus();
    }
    
    // Alt + H for help
    if (e.altKey && e.key === 'h') {
        e.preventDefault();
        const helpSection = document.querySelector('#help, [aria-label*="help" i]');
        if (helpSection) helpSection.scrollIntoView({ behavior: 'smooth' });
    }
});
</script>

<!-- Accessibility Styles -->
<style>
/* Screen reader only content */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.sr-only:focus {
    position: static;
    width: auto;
    height: auto;
    padding: inherit;
    margin: inherit;
    overflow: visible;
    clip: auto;
    white-space: normal;
}

/* Focus styles */
.focus\:not-sr-only:focus {
    position: static;
    width: auto;
    height: auto;
    padding: inherit;
    margin: inherit;
    overflow: visible;
    clip: auto;
    white-space: normal;
}

/* High contrast mode */
.high-contrast {
    --tw-bg-opacity: 1;
    --tw-text-opacity: 1;
}

.high-contrast .bg-white {
    background-color: white !important;
    color: black !important;
}

.high-contrast .bg-gray-900 {
    background-color: black !important;
    color: white !important;
}

.high-contrast .text-gray-600 {
    color: #666 !important;
}

.high-contrast .border-gray-300 {
    border-color: #000 !important;
}

/* Reduced motion */
.reduced-motion * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
}

.reduced-motion .animate-bounce,
.reduced-motion .animate-pulse,
.reduced-motion .animate-ping {
    animation: none !important;
}

/* Keyboard navigation focus */
.keyboard-navigation *:focus {
    outline: 2px solid #3b82f6 !important;
    outline-offset: 2px !important;
}

/* Focus trap for modals */
.focus-trap {
    position: relative;
}

.focus-trap::before,
.focus-trap::after {
    content: '';
    position: absolute;
    width: 1px;
    height: 1px;
    opacity: 0;
    pointer-events: none;
}

/* Skip links */
.skip-link {
    position: absolute;
    top: -40px;
    left: 6px;
    background: #000;
    color: #fff;
    padding: 8px;
    text-decoration: none;
    z-index: 1000;
    border-radius: 4px;
}

.skip-link:focus {
    top: 6px;
}

/* ARIA live regions */
[aria-live] {
    position: absolute;
    left: -10000px;
    width: 1px;
    height: 1px;
    overflow: hidden;
}

/* Button focus styles */
button:focus,
a:focus,
input:focus,
select:focus,
textarea:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Form labels */
label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

/* Required field indicators */
.required::after {
    content: ' *';
    color: #ef4444;
}

/* Error states */
.error {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
}

.error-message {
    color: #ef4444;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

/* Success states */
.success {
    border-color: #10b981 !important;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1) !important;
}

/* Loading states */
.loading {
    position: relative;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Color contrast improvements */
.text-gray-500 {
    color: #6b7280 !important;
}

.text-gray-600 {
    color: #4b5563 !important;
}

/* Interactive element sizing */
button,
a,
input,
select,
textarea {
    min-height: 44px;
    min-width: 44px;
}

/* Touch target improvements */
@media (max-width: 768px) {
    button,
    a,
    input,
    select,
    textarea {
        min-height: 48px;
        min-width: 48px;
    }
}

/* Print styles */
@media print {
    .no-print {
        display: none !important;
    }
    
    .print-only {
        display: block !important;
    }
    
    * {
        background: transparent !important;
        color: black !important;
        box-shadow: none !important;
        text-shadow: none !important;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .dark-mode {
        background-color: #1f2937;
        color: #f9fafb;
    }
}

/* Large text support */
@media (prefers-font-size: large) {
    html {
        font-size: 18px;
    }
}

/* Custom focus indicators for interactive elements */
.interactive:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
    border-radius: 4px;
}

/* Status indicators */
.status-success {
    color: #10b981;
    background-color: #d1fae5;
    border: 1px solid #a7f3d0;
}

.status-warning {
    color: #f59e0b;
    background-color: #fef3c7;
    border: 1px solid #fde68a;
}

.status-error {
    color: #ef4444;
    background-color: #fee2e2;
    border: 1px solid #fecaca;
}

.status-info {
    color: #3b82f6;
    background-color: #dbeafe;
    border: 1px solid #bfdbfe;
}
</style>

<!-- ARIA landmarks and semantic HTML enhancements -->
<script>
// Add ARIA landmarks to sections
document.addEventListener('DOMContentLoaded', function() {
    // Add main landmark
    const mainContent = document.querySelector('main') || document.querySelector('#main-content');
    if (mainContent) {
        mainContent.setAttribute('role', 'main');
        mainContent.id = 'main-content';
    }

    // Add navigation landmarks
    const navElements = document.querySelectorAll('nav');
    navElements.forEach((nav, index) => {
        nav.setAttribute('role', 'navigation');
        if (!nav.getAttribute('aria-label')) {
            nav.setAttribute('aria-label', `Navigation ${index + 1}`);
        }
    });

    // Add banner landmark
    const header = document.querySelector('header');
    if (header) {
        header.setAttribute('role', 'banner');
    }

    // Add contentinfo landmark
    const footer = document.querySelector('footer');
    if (footer) {
        footer.setAttribute('role', 'contentinfo');
    }

    // Add complementary landmarks
    const asideElements = document.querySelectorAll('aside');
    asideElements.forEach((aside, index) => {
        aside.setAttribute('role', 'complementary');
        if (!aside.getAttribute('aria-label')) {
            aside.setAttribute('aria-label', `Complementary content ${index + 1}`);
        }
    });

    // Enhance form accessibility
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        if (!form.getAttribute('aria-label') && !form.querySelector('legend')) {
            const formTitle = form.querySelector('h1, h2, h3, h4, h5, h6');
            if (formTitle) {
                form.setAttribute('aria-labelledby', formTitle.id || 'form-title');
                if (!formTitle.id) {
                    formTitle.id = 'form-title';
                }
            }
        }
    });

    // Add live regions for dynamic content
    const liveRegion = document.createElement('div');
    liveRegion.setAttribute('aria-live', 'polite');
    liveRegion.setAttribute('aria-atomic', 'true');
    liveRegion.className = 'sr-only';
    liveRegion.id = 'live-region';
    document.body.appendChild(liveRegion);

    // Enhance button accessibility
    const buttons = document.querySelectorAll('button:not([aria-label]):not([aria-labelledby])');
    buttons.forEach(button => {
        if (!button.textContent.trim() && !button.querySelector('img[alt]')) {
            button.setAttribute('aria-label', 'Button');
        }
    });

    // Enhance link accessibility
    const links = document.querySelectorAll('a:not([aria-label])');
    links.forEach(link => {
        if (!link.textContent.trim() && !link.querySelector('img[alt]')) {
            const href = link.getAttribute('href');
            if (href) {
                link.setAttribute('aria-label', `Link to ${href}`);
            }
        }
    });
});
</script>
