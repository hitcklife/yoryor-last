/**
 * Flowbite Initialization
 * Properly initialize Flowbite components for Laravel + Livewire
 */

import { initFlowbite } from 'flowbite';

// Initialize Flowbite when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing Flowbite...');
    initFlowbite();
    console.log('Flowbite initialized successfully');
});

// Re-initialize Flowbite when Livewire updates the DOM
document.addEventListener('livewire:navigated', function() {
    console.log('Livewire navigated, re-initializing Flowbite...');
    initFlowbite();
});

document.addEventListener('livewire:updated', function() {
    console.log('Livewire updated, re-initializing Flowbite...');
    initFlowbite();
});

// Export for manual initialization if needed
window.initFlowbite = initFlowbite;
