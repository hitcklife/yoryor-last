# YorYor Theme System & Icons Guide

## Table of Contents

- [Overview](#overview)
- [Theme System](#theme-system)
- [Dark Mode Implementation](#dark-mode-implementation)
- [Theme Switcher Component](#theme-switcher-component)
- [CSS Custom Properties](#css-custom-properties)
- [Lucide Icons Integration](#lucide-icons-integration)
- [Icon Usage Patterns](#icon-usage-patterns)
- [Design Tokens](#design-tokens)
- [Component Styling Patterns](#component-styling-patterns)
- [Responsive Design](#responsive-design)
- [Accessibility](#accessibility)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

---

## Overview

YorYor implements a comprehensive theming system with support for light, dark, and system preference modes. The design system uses CSS custom properties (CSS variables) for theming, Tailwind CSS for utility classes, and Lucide Icons for a consistent icon system.

### Key Features

- **Three Theme Modes**: Light, Dark, System (auto-detect)
- **Persistent Preferences**: Saved in cookies (guests) and database (authenticated users)
- **Smooth Transitions**: Seamless theme switching without page reload
- **System Integration**: Respects user's OS theme preference
- **Icon System**: 1000+ Lucide icons with consistent styling
- **Design Tokens**: CSS variables for consistent design language
- **Mobile Optimized**: Theme-aware meta tags for mobile browsers

---

## Theme System

### Architecture

```
User Preference → ThemeSwitcher Component → Theme Manager → DOM Update
                                         ↓
                                    Storage (Cookie/DB)
```

### Theme Modes

1. **Light Mode**: Bright colors, light backgrounds
2. **Dark Mode**: Dark backgrounds, muted colors
3. **System Mode**: Automatically follows OS preference

### Implementation Files

```
resources/
├── js/
│   └── theme.js                 # Theme management logic
├── css/
│   ├── design-tokens.css        # CSS variables for theming
│   └── app.css                  # Main stylesheet
└── views/
    └── livewire/
        └── theme-switcher.blade.php  # Theme switcher UI
```

---

## Dark Mode Implementation

### Tailwind Configuration

YorYor uses Tailwind's **class-based dark mode**:

**tailwind.config.js**:
```javascript
export default {
  darkMode: 'class', // Enable class-based dark mode
  // ...
}
```

### HTML Structure

The dark mode is controlled by adding/removing the `dark` class on the `<html>` element:

```html
<!-- Light mode -->
<html lang="en">

<!-- Dark mode -->
<html lang="en" class="dark">
```

### CSS Classes

Use Tailwind's `dark:` variant for dark mode styles:

```html
<!-- Background colors -->
<div class="bg-white dark:bg-gray-900">

<!-- Text colors -->
<p class="text-gray-900 dark:text-white">

<!-- Border colors -->
<div class="border-gray-200 dark:border-gray-700">

<!-- Gradients -->
<div class="bg-gradient-to-r from-purple-600 to-pink-600
            dark:from-purple-800 dark:to-pink-800">
```

---

## Theme Switcher Component

### Livewire Component

**File**: `app/Livewire/ThemeSwitcher.php`

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ThemeSwitcher extends Component
{
    public string $theme = 'light';

    public function mount()
    {
        // Load theme from user settings or cookie
        if (Auth::check()) {
            $this->theme = Auth::user()->settings->theme ?? 'light';
        } else {
            $this->theme = request()->cookie('theme', 'light');
        }
    }

    public function setTheme(string $theme)
    {
        $this->theme = $theme;

        // Save to database for authenticated users
        if (Auth::check()) {
            Auth::user()->settings()->update(['theme' => $theme]);
        }

        // Save to cookie for guests (1 year expiration)
        cookie()->queue('theme', $theme, 525600);

        // Dispatch event to update DOM
        $this->dispatch('theme-changed', theme: $theme);
    }

    public function render()
    {
        return view('livewire.theme-switcher');
    }
}
```

### Blade Template

**File**: `resources/views/livewire/theme-switcher.blade.php`

```blade
<div x-data="{ open: false }" @click.away="open = false" class="relative">
    <!-- Trigger Button -->
    <button
        @click="open = !open"
        class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        aria-label="Theme switcher"
    >
        <!-- Current theme icon -->
        @if($theme === 'light')
            <i data-lucide="sun" class="w-5 h-5 text-yellow-500"></i>
        @elseif($theme === 'dark')
            <i data-lucide="moon" class="w-5 h-5 text-indigo-500"></i>
        @else
            <i data-lucide="monitor" class="w-5 h-5 text-gray-500"></i>
        @endif
    </button>

    <!-- Dropdown Menu -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-48 rounded-xl shadow-xl
               bg-white/95 dark:bg-gray-900/95
               backdrop-blur-md border border-gray-200 dark:border-gray-700
               overflow-hidden z-50"
        style="display: none;"
    >
        <!-- Light Mode Option -->
        <button
            wire:click="setTheme('light')"
            @click="open = false"
            class="w-full flex items-center gap-3 px-4 py-3
                   hover:bg-gradient-to-r hover:from-yellow-50 hover:to-orange-50
                   dark:hover:from-yellow-900/20 dark:hover:to-orange-900/20
                   transition-all duration-200
                   {{ $theme === 'light' ? 'bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20' : '' }}"
        >
            <div class="flex items-center justify-center w-8 h-8 rounded-lg
                        bg-gradient-to-r from-yellow-400 to-orange-400">
                <i data-lucide="sun" class="w-4 h-4 text-white"></i>
            </div>
            <span class="flex-1 text-left text-sm font-medium text-gray-900 dark:text-white">
                Light
            </span>
            @if($theme === 'light')
                <i data-lucide="check" class="w-4 h-4 text-yellow-600 dark:text-yellow-400"></i>
            @endif
        </button>

        <!-- Dark Mode Option -->
        <button
            wire:click="setTheme('dark')"
            @click="open = false"
            class="w-full flex items-center gap-3 px-4 py-3
                   hover:bg-gradient-to-r hover:from-indigo-50 hover:to-purple-50
                   dark:hover:from-indigo-900/20 dark:hover:to-purple-900/20
                   transition-all duration-200
                   {{ $theme === 'dark' ? 'bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20' : '' }}"
        >
            <div class="flex items-center justify-center w-8 h-8 rounded-lg
                        bg-gradient-to-r from-indigo-500 to-purple-500">
                <i data-lucide="moon" class="w-4 h-4 text-white"></i>
            </div>
            <span class="flex-1 text-left text-sm font-medium text-gray-900 dark:text-white">
                Dark
            </span>
            @if($theme === 'dark')
                <i data-lucide="check" class="w-4 h-4 text-indigo-600 dark:text-indigo-400"></i>
            @endif
        </button>

        <!-- System Mode Option -->
        <button
            wire:click="setTheme('system')"
            @click="open = false"
            class="w-full flex items-center gap-3 px-4 py-3
                   hover:bg-gradient-to-r hover:from-gray-50 hover:to-slate-50
                   dark:hover:from-gray-800/20 dark:hover:to-slate-800/20
                   transition-all duration-200
                   {{ $theme === 'system' ? 'bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-800/20 dark:to-slate-800/20' : '' }}"
        >
            <div class="flex items-center justify-center w-8 h-8 rounded-lg
                        bg-gradient-to-r from-gray-400 to-slate-400">
                <i data-lucide="monitor" class="w-4 h-4 text-white"></i>
            </div>
            <span class="flex-1 text-left text-sm font-medium text-gray-900 dark:text-white">
                System
            </span>
            @if($theme === 'system')
                <i data-lucide="check" class="w-4 h-4 text-gray-600 dark:text-gray-400"></i>
            @endif
        </button>
    </div>
</div>
```

### Usage

Include the theme switcher anywhere in your layout:

```blade
<!-- In header -->
<header>
    <nav>
        <!-- ... navigation items ... -->
        <livewire:theme-switcher />
    </nav>
</header>

<!-- In settings -->
<div class="settings">
    <h2>Appearance</h2>
    <livewire:theme-switcher />
</div>
```

---

## Theme Manager (JavaScript)

**File**: `resources/js/theme.js`

```javascript
/**
 * Theme management system
 * Handles theme initialization, switching, and persistence
 */

// Initialize theme on page load
const initTheme = () => {
    const savedTheme = localStorage.getItem('theme');
    const cookieTheme = getCookie('theme');
    const systemPreference = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

    // Priority: localStorage > cookie > system preference
    const theme = savedTheme || cookieTheme || systemPreference;

    applyTheme(theme);
};

// Apply theme to document
const applyTheme = (theme) => {
    const root = document.documentElement;

    if (theme === 'system') {
        const systemPreference = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        root.classList.toggle('dark', systemPreference === 'dark');
    } else {
        root.classList.toggle('dark', theme === 'dark');
    }

    // Update meta theme-color for mobile browsers
    updateMetaThemeColor(theme);

    // Save to localStorage
    localStorage.setItem('theme', theme);
};

// Update mobile browser theme color
const updateMetaThemeColor = (theme) => {
    const metaThemeColor = document.querySelector('meta[name="theme-color"]');

    if (metaThemeColor) {
        const color = theme === 'dark' ? '#1f2937' : '#ffffff';
        metaThemeColor.setAttribute('content', color);
    }
};

// Listen for theme changes from Livewire
window.addEventListener('theme-changed', (event) => {
    const theme = event.detail.theme;
    applyTheme(theme);
});

// Listen for system preference changes
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    const savedTheme = localStorage.getItem('theme');

    // Only update if user has system preference selected
    if (!savedTheme || savedTheme === 'system') {
        applyTheme(e.matches ? 'dark' : 'light');
    }
});

// Utility: Get cookie value
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

// Initialize theme when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTheme);
} else {
    initTheme();
}

// Export for use in other modules
export { initTheme, applyTheme };
```

---

## CSS Custom Properties

### Design Tokens

**File**: `resources/css/design-tokens.css`

```css
:root {
    /* ========================================
       Color Palette
       ======================================== */

    /* Primary Colors (Purple/Pink Gradient) */
    --color-primary-50: #faf5ff;
    --color-primary-100: #f3e8ff;
    --color-primary-200: #e9d5ff;
    --color-primary-300: #d8b4fe;
    --color-primary-400: #c084fc;
    --color-primary-500: #a855f7;
    --color-primary-600: #8b5cf6;  /* Main brand color */
    --color-primary-700: #7c3aed;
    --color-primary-800: #6d28d9;
    --color-primary-900: #5b21b6;

    /* Secondary Colors (Pink) */
    --color-secondary-50: #fdf2f8;
    --color-secondary-100: #fce7f3;
    --color-secondary-200: #fbcfe8;
    --color-secondary-300: #f9a8d4;
    --color-secondary-400: #f472b6;
    --color-secondary-500: #ec4899;  /* Secondary brand color */
    --color-secondary-600: #db2777;
    --color-secondary-700: #be185d;
    --color-secondary-800: #9d174d;
    --color-secondary-900: #831843;

    /* Semantic Colors */
    --color-success: #10b981;
    --color-warning: #f59e0b;
    --color-error: #ef4444;
    --color-info: #06b6d4;

    /* Neutral Colors */
    --color-gray-50: #f9fafb;
    --color-gray-100: #f3f4f6;
    --color-gray-200: #e5e7eb;
    --color-gray-300: #d1d5db;
    --color-gray-400: #9ca3af;
    --color-gray-500: #6b7280;
    --color-gray-600: #4b5563;
    --color-gray-700: #374151;
    --color-gray-800: #1f2937;
    --color-gray-900: #111827;

    /* ========================================
       Spacing Scale
       ======================================== */

    --spacing-unit: 0.25rem;  /* 4px */
    --spacing-xs: calc(var(--spacing-unit) * 1);   /* 4px */
    --spacing-sm: calc(var(--spacing-unit) * 2);   /* 8px */
    --spacing-md: calc(var(--spacing-unit) * 4);   /* 16px */
    --spacing-lg: calc(var(--spacing-unit) * 6);   /* 24px */
    --spacing-xl: calc(var(--spacing-unit) * 8);   /* 32px */
    --spacing-2xl: calc(var(--spacing-unit) * 12); /* 48px */
    --spacing-3xl: calc(var(--spacing-unit) * 16); /* 64px */

    /* ========================================
       Border Radius
       ======================================== */

    --radius-sm: 0.375rem;   /* 6px */
    --radius-md: 0.5rem;     /* 8px */
    --radius-lg: 0.75rem;    /* 12px */
    --radius-xl: 1rem;       /* 16px */
    --radius-2xl: 1.5rem;    /* 24px */
    --radius-full: 9999px;   /* Fully rounded */

    /* ========================================
       Shadows
       ======================================== */

    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);

    /* ========================================
       Transitions
       ======================================== */

    --transition-fast: 150ms;
    --transition-normal: 250ms;
    --transition-slow: 350ms;
    --transition-ease: cubic-bezier(0.4, 0, 0.2, 1);

    /* ========================================
       Typography
       ======================================== */

    --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    --font-mono: 'Fira Code', 'Courier New', monospace;

    --font-size-xs: 0.75rem;    /* 12px */
    --font-size-sm: 0.875rem;   /* 14px */
    --font-size-base: 1rem;     /* 16px */
    --font-size-lg: 1.125rem;   /* 18px */
    --font-size-xl: 1.25rem;    /* 20px */
    --font-size-2xl: 1.5rem;    /* 24px */
    --font-size-3xl: 1.875rem;  /* 30px */
    --font-size-4xl: 2.25rem;   /* 36px */

    /* ========================================
       Z-Index Scale
       ======================================== */

    --z-dropdown: 1000;
    --z-sticky: 1020;
    --z-fixed: 1030;
    --z-modal-backdrop: 1040;
    --z-modal: 1050;
    --z-popover: 1060;
    --z-tooltip: 1070;
}

/* ========================================
   Dark Mode Overrides
   ======================================== */

.dark {
    /* Backgrounds */
    --color-background: #1f2937;
    --color-surface: #111827;
    --color-card: #1f2937;

    /* Text */
    --color-text-primary: #f9fafb;
    --color-text-secondary: #d1d5db;
    --color-text-muted: #9ca3af;

    /* Borders */
    --color-border: #374151;
    --color-border-light: #4b5563;

    /* Shadows (darker in dark mode) */
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.4);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.6);
    --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
}
```

### Using Design Tokens

```css
/* In your CSS */
.my-component {
    background: var(--color-primary-600);
    padding: var(--spacing-md);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    transition: all var(--transition-normal) var(--transition-ease);
}

.my-component:hover {
    background: var(--color-primary-700);
    box-shadow: var(--shadow-lg);
}
```

---

## Lucide Icons Integration

### Installation

Lucide icons are already installed and configured in `resources/js/app.js`.

### Available Icons

YorYor imports commonly used icons:

```javascript
import {
    // UI Icons
    Sun, Moon, Monitor, Home, Settings, Search, Filter,
    Menu, X, ChevronDown, ChevronLeft, ChevronRight,
    Plus, Check, Info, AlertTriangle,

    // User Icons
    User, Users, Heart, Star, Eye,

    // Communication Icons
    MessageCircle, Phone, Video, Send, Bell,

    // Profile Icons
    Shield, ShieldCheck, ShieldAlert, Lock,
    Globe, Languages, MapPin, Clock, Calendar,

    // Other Icons
    Download, ArrowRight, ArrowLeft, Zap,
    BarChart3, CreditCard, Plane, Book, List,
    FolderGit2, BookOpenText, LogOut, Quote
} from 'lucide';
```

**Full icon list**: [lucide.dev](https://lucide.dev)

### Basic Usage

```html
<!-- Basic icon -->
<i data-lucide="heart" class="w-6 h-6"></i>

<!-- With color -->
<i data-lucide="star" class="w-6 h-6 text-yellow-500"></i>

<!-- Dark mode aware -->
<i data-lucide="user" class="w-6 h-6 text-gray-900 dark:text-white"></i>

<!-- With hover effect -->
<i data-lucide="settings"
   class="w-6 h-6 text-gray-600 hover:text-purple-600
          dark:text-gray-400 dark:hover:text-purple-400
          transition-colors"></i>
```

### Icon Sizes

```html
<!-- Extra small (16px) -->
<i data-lucide="check" class="w-4 h-4"></i>

<!-- Small (20px) -->
<i data-lucide="heart" class="w-5 h-5"></i>

<!-- Medium (24px) -->
<i data-lucide="user" class="w-6 h-6"></i>

<!-- Large (32px) -->
<i data-lucide="star" class="w-8 h-8"></i>

<!-- Extra large (40px) -->
<i data-lucide="shield" class="w-10 h-10"></i>
```

### Icons in Buttons

```html
<!-- Icon button -->
<button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
    <i data-lucide="heart" class="w-5 h-5"></i>
</button>

<!-- Button with icon and text -->
<button class="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg">
    <i data-lucide="download" class="w-4 h-4"></i>
    <span>Download</span>
</button>

<!-- Icon-only button with tooltip -->
<button
    class="p-2 rounded-lg hover:bg-gray-100"
    title="Settings"
    aria-label="Settings"
>
    <i data-lucide="settings" class="w-5 h-5"></i>
</button>
```

### Icons in Navigation

```html
<nav class="space-y-2">
    <a href="/dashboard" class="flex items-center gap-3 px-4 py-2 rounded-lg
                                hover:bg-gray-100 dark:hover:bg-gray-800">
        <i data-lucide="home" class="w-5 h-5"></i>
        <span>Dashboard</span>
    </a>

    <a href="/messages" class="flex items-center gap-3 px-4 py-2 rounded-lg
                               hover:bg-gray-100 dark:hover:bg-gray-800">
        <i data-lucide="message-circle" class="w-5 h-5"></i>
        <span>Messages</span>
        <!-- Badge -->
        <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">
            5
        </span>
    </a>
</nav>
```

### Icons with Animation

```html
<!-- Spinning loader -->
<i data-lucide="loader" class="w-6 h-6 animate-spin"></i>

<!-- Pulse effect -->
<i data-lucide="heart" class="w-6 h-6 text-red-500 animate-pulse"></i>

<!-- Bounce effect -->
<i data-lucide="bell" class="w-6 h-6 animate-bounce"></i>
```

### Re-initialization After Livewire Updates

Icons are automatically re-initialized when Livewire updates the DOM:

```javascript
// In app.js
document.addEventListener('livewire:updated', () => {
    createIcons({ icons: iconsObject });
});
```

---

## Icon Usage Patterns

### Icon Categories

#### 1. User Interface

```html
<!-- Home -->
<i data-lucide="home" class="w-5 h-5"></i>

<!-- Settings -->
<i data-lucide="settings" class="w-5 h-5"></i>

<!-- Search -->
<i data-lucide="search" class="w-5 h-5"></i>

<!-- Filter -->
<i data-lucide="filter" class="w-5 h-5"></i>

<!-- Menu -->
<i data-lucide="menu" class="w-5 h-5"></i>

<!-- Close -->
<i data-lucide="x" class="w-5 h-5"></i>
```

#### 2. User & Profile

```html
<!-- User -->
<i data-lucide="user" class="w-5 h-5"></i>

<!-- Multiple users -->
<i data-lucide="users" class="w-5 h-5"></i>

<!-- Heart/Like -->
<i data-lucide="heart" class="w-5 h-5"></i>

<!-- Star/Favorite -->
<i data-lucide="star" class="w-5 h-5"></i>

<!-- Eye/View -->
<i data-lucide="eye" class="w-5 h-5"></i>
```

#### 3. Communication

```html
<!-- Messages -->
<i data-lucide="message-circle" class="w-5 h-5"></i>

<!-- Phone -->
<i data-lucide="phone" class="w-5 h-5"></i>

<!-- Video call -->
<i data-lucide="video" class="w-5 h-5"></i>

<!-- Send -->
<i data-lucide="send" class="w-5 h-5"></i>

<!-- Notifications -->
<i data-lucide="bell" class="w-5 h-5"></i>
```

#### 4. Security & Verification

```html
<!-- Shield -->
<i data-lucide="shield" class="w-5 h-5"></i>

<!-- Shield with check (verified) -->
<i data-lucide="shield-check" class="w-5 h-5"></i>

<!-- Shield with alert -->
<i data-lucide="shield-alert" class="w-5 h-5"></i>

<!-- Lock -->
<i data-lucide="lock" class="w-5 h-5"></i>
```

#### 5. Status & Feedback

```html
<!-- Success -->
<i data-lucide="check" class="w-5 h-5 text-green-500"></i>

<!-- Error -->
<i data-lucide="x" class="w-5 h-5 text-red-500"></i>

<!-- Warning -->
<i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-500"></i>

<!-- Info -->
<i data-lucide="info" class="w-5 h-5 text-blue-500"></i>
```

---

## Design Tokens

### Color Palette

#### Primary Colors (Purple Gradient)

Used for primary actions, CTAs, and brand elements:

```css
/* Light shades */
bg-purple-50   /* #faf5ff */
bg-purple-100  /* #f3e8ff */
bg-purple-200  /* #e9d5ff */

/* Medium shades */
bg-purple-500  /* #a855f7 */
bg-purple-600  /* #8b5cf6 - Main brand color */
bg-purple-700  /* #7c3aed */

/* Dark shades */
bg-purple-800  /* #6d28d9 */
bg-purple-900  /* #5b21b6 */
```

#### Secondary Colors (Pink)

Used for accents and secondary actions:

```css
bg-pink-400    /* #f472b6 */
bg-pink-500    /* #ec4899 - Secondary brand color */
bg-pink-600    /* #db2777 */
```

#### Semantic Colors

```css
/* Success */
text-green-500  /* #10b981 */

/* Warning */
text-yellow-500 /* #f59e0b */

/* Error */
text-red-500    /* #ef4444 */

/* Info */
text-blue-500   /* #06b6d4 */
```

### Gradient Combinations

```html
<!-- Primary gradient (purple to pink) -->
<div class="bg-gradient-to-r from-purple-600 to-pink-600">

<!-- Light gradient -->
<div class="bg-gradient-to-r from-purple-400 to-pink-400">

<!-- Dark mode gradient -->
<div class="bg-gradient-to-r from-purple-800 to-pink-800
            dark:from-purple-900 dark:to-pink-900">
```

---

## Component Styling Patterns

### Card Component

```html
<div class="bg-white dark:bg-gray-800
            rounded-lg shadow-md
            p-6
            border border-gray-200 dark:border-gray-700
            hover:shadow-lg transition-shadow">
    <!-- Card content -->
</div>
```

### Button Patterns

```html
<!-- Primary button -->
<button class="bg-gradient-to-r from-purple-600 to-pink-600
               text-white px-6 py-3 rounded-lg
               font-semibold shadow-md
               hover:from-purple-700 hover:to-pink-700
               transition-all duration-200
               focus:outline-none focus:ring-2 focus:ring-purple-500">
    Click me
</button>

<!-- Secondary button -->
<button class="bg-white dark:bg-gray-800
               text-gray-900 dark:text-white
               border-2 border-gray-300 dark:border-gray-600
               px-6 py-3 rounded-lg
               font-semibold
               hover:bg-gray-50 dark:hover:bg-gray-700
               transition-colors">
    Secondary
</button>

<!-- Ghost button -->
<button class="text-purple-600 dark:text-purple-400
               px-6 py-3 rounded-lg
               hover:bg-purple-50 dark:hover:bg-purple-900/20
               transition-colors">
    Ghost
</button>
```

### Input Fields

```html
<input
    type="text"
    class="w-full
           border border-gray-300 dark:border-gray-600
           rounded-lg px-4 py-2
           bg-white dark:bg-gray-900
           text-gray-900 dark:text-white
           placeholder-gray-400 dark:placeholder-gray-500
           focus:ring-2 focus:ring-purple-500 focus:border-transparent
           transition-colors"
    placeholder="Enter text..."
/>
```

### Badge Component

```html
<!-- Status badge -->
<span class="inline-flex items-center
             px-3 py-1 rounded-full
             text-xs font-semibold
             bg-green-100 text-green-800
             dark:bg-green-900 dark:text-green-200">
    Active
</span>

<!-- With icon -->
<span class="inline-flex items-center gap-1
             px-3 py-1 rounded-full
             text-xs font-semibold
             bg-purple-100 text-purple-800
             dark:bg-purple-900 dark:text-purple-200">
    <i data-lucide="check" class="w-3 h-3"></i>
    Verified
</span>
```

### Modal Pattern

```html
<!-- Backdrop -->
<div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40"></div>

<!-- Modal -->
<div class="fixed inset-0 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-900
                rounded-2xl shadow-2xl
                max-w-lg w-full mx-4
                p-6
                border border-gray-200 dark:border-gray-700">
        <!-- Modal content -->
    </div>
</div>
```

---

## Responsive Design

### Breakpoints

```css
/* Mobile first approach */
sm: 640px   /* Small devices */
md: 768px   /* Tablets */
lg: 1024px  /* Laptops */
xl: 1280px  /* Desktops */
2xl: 1536px /* Large screens */
```

### Responsive Patterns

```html
<!-- Responsive text size -->
<h1 class="text-2xl md:text-3xl lg:text-4xl xl:text-5xl">

<!-- Responsive spacing -->
<div class="p-4 md:p-6 lg:p-8">

<!-- Responsive grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

<!-- Responsive flex -->
<div class="flex flex-col md:flex-row gap-4">

<!-- Hide on mobile -->
<div class="hidden md:block">

<!-- Show only on mobile -->
<div class="block md:hidden">
```

---

## Accessibility

### ARIA Labels

```html
<!-- Icon buttons need labels -->
<button aria-label="Close" title="Close">
    <i data-lucide="x" class="w-5 h-5"></i>
</button>

<!-- Decorative icons -->
<i data-lucide="heart" class="w-5 h-5" aria-hidden="true"></i>
```

### Focus States

```html
<!-- Always include focus states -->
<button class="focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
    Click me
</button>

<!-- Visible focus indicator -->
<a href="#" class="focus-visible:ring-2 focus-visible:ring-purple-500">
    Link
</a>
```

### Color Contrast

Ensure sufficient contrast in both light and dark modes:

```html
<!-- Good contrast -->
<div class="text-gray-900 dark:text-white
            bg-white dark:bg-gray-900">

<!-- Poor contrast (avoid) -->
<div class="text-gray-400 bg-gray-300"> ❌
```

---

## Best Practices

### 1. Theme-Aware Components

Always consider both light and dark modes:

```html
<!-- ✅ Good -->
<div class="bg-white dark:bg-gray-900
            text-gray-900 dark:text-white
            border-gray-200 dark:border-gray-700">

<!-- ❌ Bad -->
<div class="bg-white text-gray-900">
```

### 2. Consistent Spacing

Use design tokens for spacing:

```html
<!-- ✅ Good -->
<div class="p-4 md:p-6 lg:p-8">

<!-- ❌ Bad -->
<div class="p-[17px]">
```

### 3. Icon Consistency

Use consistent icon sizes:

```html
<!-- ✅ Good -->
<nav>
    <i data-lucide="home" class="w-5 h-5"></i>
    <i data-lucide="settings" class="w-5 h-5"></i>
    <i data-lucide="user" class="w-5 h-5"></i>
</nav>

<!-- ❌ Bad: Mixed sizes -->
<nav>
    <i data-lucide="home" class="w-4 h-4"></i>
    <i data-lucide="settings" class="w-6 h-6"></i>
    <i data-lucide="user" class="w-5 h-5"></i>
</nav>
```

### 4. Smooth Transitions

Add transitions for better UX:

```html
<button class="bg-purple-600 hover:bg-purple-700
               transition-colors duration-200">
    Smooth hover
</button>
```

### 5. Mobile-First

Design for mobile first, then enhance for larger screens:

```html
<!-- ✅ Good: Mobile first -->
<div class="flex-col md:flex-row">

<!-- ❌ Bad: Desktop first -->
<div class="flex-row md:flex-col">
```

---

## Troubleshooting

### Icons Not Appearing

**Problem**: Icons show as empty `<i>` tags.

**Solutions**:
```javascript
// 1. Check icons are initialized
console.log(window.lucide); // Should not be undefined

// 2. Manually re-initialize
window.lucide.createIcons({ icons: window.lucide.icons });

// 3. Check icon name is correct
<i data-lucide="heart"></i> // ✅ Correct
<i data-lucide="hearts"></i> // ❌ Wrong (doesn't exist)
```

### Theme Not Persisting

**Problem**: Theme resets on page reload.

**Solutions**:
```javascript
// 1. Check localStorage
console.log(localStorage.getItem('theme'));

// 2. Check cookie
console.log(document.cookie);

// 3. Verify theme is saved
// In ThemeSwitcher component
public function setTheme(string $theme)
{
    $this->theme = $theme;
    cookie()->queue('theme', $theme, 525600); // 1 year
}
```

### Dark Mode Classes Not Working

**Problem**: `dark:` classes have no effect.

**Solutions**:
```html
<!-- 1. Check <html> has 'dark' class -->
<html class="dark">

<!-- 2. Verify Tailwind config -->
// tailwind.config.js
darkMode: 'class', // ✅ Correct

<!-- 3. Check specificity -->
<div class="bg-white dark:bg-gray-900"> <!-- ✅ Works -->
<div class="bg-white" dark:bg-gray-900> <!-- ❌ Syntax error -->
```

### Gradient Not Showing in Dark Mode

**Problem**: Gradients look washed out in dark mode.

**Solution**:
```html
<!-- Use darker shades for dark mode -->
<div class="bg-gradient-to-r from-purple-600 to-pink-600
            dark:from-purple-800 dark:to-pink-800">
```

---

**Last Updated**: September 2025

This theming and icons guide provides everything you need to create a consistent, beautiful, and accessible user interface for the YorYor dating application.
