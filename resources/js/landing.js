/**
 * Landing Page JavaScript
 * Main entry point for all landing page functionality
 */

import { initBackToTop, scrollToTop } from './components/back-to-top.js';
import { getCurrentLanguage, getCurrentLanguageFlag, getCurrentLanguageCode, switchLanguage } from './components/language-utils.js';

// Initialize all components when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize back to top functionality
    initBackToTop();
    
    // Add click handler to back to top button
    const backToTopBtn = document.getElementById('backToTop');
    if (backToTopBtn) {
        backToTopBtn.addEventListener('click', scrollToTop);
    }
    
    console.log('Landing page initialized');
});

// Export utilities for global access
export {
    getCurrentLanguage,
    getCurrentLanguageFlag, 
    getCurrentLanguageCode,
    switchLanguage,
    scrollToTop
};