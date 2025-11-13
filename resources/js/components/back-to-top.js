/**
 * Back to Top Button Functionality
 */

export function initBackToTop() {
    // Show/hide back to top button based on scroll position
    window.addEventListener('scroll', function() {
        const backToTop = document.getElementById('backToTop');
        if (backToTop && window.scrollY > 300) {
            backToTop.style.opacity = '1';
            backToTop.style.visibility = 'visible';
        } else if (backToTop) {
            backToTop.style.opacity = '0';
            backToTop.style.visibility = 'hidden';
        }
    });
}

export function scrollToTop() {
    window.scrollTo({
        top: 0, 
        behavior: 'smooth'
    });
}