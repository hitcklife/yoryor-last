/**
 * Language Utility Functions (for backward compatibility)
 */

export function getCurrentLanguage() {
    return document.documentElement.lang || 'en';
}

export function getCurrentLanguageFlag() {
    const locale = getCurrentLanguage();
    switch(locale) {
        case 'uz': return '🇺🇿';
        case 'ru': return '🇷🇺';
        default: return '🇺🇸';
    }
}

export function getCurrentLanguageCode() {
    const locale = getCurrentLanguage();
    switch(locale) {
        case 'uz': return 'UZ';
        case 'ru': return 'RU';
        default: return 'EN';
    }
}

export function switchLanguage(lang) {
    // Add language parameter to current URL
    const url = new URL(window.location);
    url.searchParams.set('lang', lang);
    window.location.href = url.toString();
}

// Make functions available globally for backward compatibility
window.getCurrentLanguage = getCurrentLanguage;
window.getCurrentLanguageFlag = getCurrentLanguageFlag;
window.getCurrentLanguageCode = getCurrentLanguageCode;
window.switchLanguage = switchLanguage;