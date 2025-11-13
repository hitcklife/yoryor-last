/**
 * YorYor Theme System
 * Handles light/dark mode with system preference detection
 */

class ThemeManager {
    constructor() {
        this.theme = this.getStoredTheme() || 'system';
        this.init();
        
        // Apply theme immediately to avoid flash
        this.applyTheme(this.theme);
    }

    init() {
        // Apply initial theme
        this.applyTheme(this.theme);
        
        // Listen for system preference changes
        this.watchSystemPreference();
        
        // Listen for Livewire theme changes
        this.listenForThemeChanges();
        
        // Handle page visibility changes (for real-time system updates)
        this.handleVisibilityChange();
    }

    getStoredTheme() {
        // Check cookie first
        const cookieTheme = this.getCookie('theme');
        if (cookieTheme) return cookieTheme;
        
        // Check localStorage as fallback
        return localStorage.getItem('theme');
    }

    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    getSystemPreference() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    resolveTheme(theme) {
        if (theme === 'system') {
            return this.getSystemPreference();
        }
        return theme;
    }

    applyTheme(theme) {
        const resolvedTheme = this.resolveTheme(theme);
        const htmlElement = document.documentElement;
        
        // Remove existing theme classes
        htmlElement.classList.remove('light', 'dark');
        
        // Add the resolved theme class
        htmlElement.classList.add(resolvedTheme);
        
        // Store current theme
        this.theme = theme;
        
        // Update meta theme-color for mobile browsers
        this.updateMetaThemeColor(resolvedTheme);
        
        // Dispatch custom event for other components
        this.dispatchThemeEvent(resolvedTheme);
        
        // Log for debugging
        console.log(`Theme applied: ${theme} → ${resolvedTheme}`);
    }

    updateMetaThemeColor(resolvedTheme) {
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (!metaThemeColor) {
            metaThemeColor = document.createElement('meta');
            metaThemeColor.name = 'theme-color';
            document.head.appendChild(metaThemeColor);
        }
        
        // Set appropriate theme color based on mode
        const themeColors = {
            light: '#ffffff',
            dark: '#1a1a1a'
        };
        
        metaThemeColor.content = themeColors[resolvedTheme] || themeColors.light;
    }

    dispatchThemeEvent(resolvedTheme) {
        window.dispatchEvent(new CustomEvent('theme-applied', {
            detail: {
                theme: this.theme,
                resolvedTheme: resolvedTheme,
                isSystemTheme: this.theme === 'system'
            }
        }));
    }

    watchSystemPreference() {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        mediaQuery.addEventListener('change', (e) => {
            // Only apply if current theme is 'system'
            if (this.theme === 'system') {
                this.applyTheme('system');
            }
        });
    }

    listenForThemeChanges() {
        // Listen for Livewire theme changes
        window.addEventListener('theme-changed', (e) => {
            const newTheme = e.detail.theme;
            this.applyTheme(newTheme);
            
            // Store in localStorage as backup
            localStorage.setItem('theme', newTheme);
        });

        // Listen for direct theme changes (for programmatic usage)
        window.addEventListener('set-theme', (e) => {
            const newTheme = e.detail.theme;
            this.setTheme(newTheme);
        });
    }

    handleVisibilityChange() {
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && this.theme === 'system') {
                // Re-check system preference when page becomes visible
                this.applyTheme('system');
            }
        });
    }

    setTheme(theme) {
        // Validate theme
        const validThemes = ['light', 'dark', 'system'];
        if (!validThemes.includes(theme)) {
            console.warn(`Invalid theme: ${theme}. Using 'system' instead.`);
            theme = 'system';
        }

        this.applyTheme(theme);
        
        // Store in cookie and localStorage
        this.setCookie('theme', theme, 365); // 1 year
        localStorage.setItem('theme', theme);
        
        // Notify Livewire if available
        if (window.Livewire) {
            window.Livewire.dispatch('theme-updated', { theme: theme });
        }
    }

    setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/;SameSite=Lax`;
    }

    getCurrentTheme() {
        return this.theme;
    }

    getCurrentResolvedTheme() {
        return this.resolveTheme(this.theme);
    }

    isDarkMode() {
        return this.getCurrentResolvedTheme() === 'dark';
    }

    isLightMode() {
        return this.getCurrentResolvedTheme() === 'light';
    }

    isSystemTheme() {
        return this.theme === 'system';
    }

    toggle() {
        const currentResolved = this.getCurrentResolvedTheme();
        const newTheme = currentResolved === 'dark' ? 'light' : 'dark';
        this.setTheme(newTheme);
    }
}

// Initialize theme manager immediately (before DOM ready to avoid flash)
let themeManager;

// Apply theme as early as possible
(function() {
    const storedTheme = (() => {
        // Check cookie first
        const cookieTheme = document.cookie
            .split('; ')
            .find(row => row.startsWith('theme='))
            ?.split('=')[1];
        if (cookieTheme) return cookieTheme;
        
        // Check localStorage as fallback
        try {
            return localStorage.getItem('theme');
        } catch (e) {
            return null;
        }
    })();
    
    const theme = storedTheme || 'system';
    const resolvedTheme = theme === 'system' 
        ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
        : theme;
    
    // Apply theme class immediately
    document.documentElement.classList.remove('light', 'dark');
    document.documentElement.classList.add(resolvedTheme);
    
    console.log(`Early theme application: ${theme} → ${resolvedTheme}`);
})();

// Initialize full theme manager when ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        themeManager = new ThemeManager();
        window.themeManager = themeManager;
    });
} else {
    themeManager = new ThemeManager();
    window.themeManager = themeManager;
}

// Utility functions for global access
window.setTheme = (theme) => {
    if (window.themeManager) {
        window.themeManager.setTheme(theme);
    }
};

window.toggleTheme = () => {
    if (window.themeManager) {
        window.themeManager.toggle();
    }
};

window.getCurrentTheme = () => {
    return window.themeManager ? window.themeManager.getCurrentTheme() : 'system';
};

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeManager;
}