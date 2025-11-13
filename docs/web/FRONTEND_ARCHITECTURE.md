# YorYor Frontend Architecture Guide

## Table of Contents

- [Overview](#overview)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [JavaScript Modules](#javascript-modules)
- [CSS Architecture](#css-architecture)
- [State Management](#state-management)
- [Real-Time Integration](#real-time-integration)
- [Asset Compilation](#asset-compilation)
- [Development Workflow](#development-workflow)
- [Performance Optimization](#performance-optimization)
- [Testing](#testing)
- [Best Practices](#best-practices)

---

## Overview

YorYor employs a **hybrid frontend architecture** combining server-side rendering with client-side interactivity. This approach provides the best of both worlds: the simplicity and SEO benefits of traditional server-rendered apps with the rich interactivity of modern SPAs.

### Architectural Principles

1. **Server-Side First**: Livewire handles most UI logic on the server
2. **Progressive Enhancement**: JavaScript enhances the experience but isn't required
3. **Component-Based**: Modular, reusable components throughout
4. **Real-Time Ready**: WebSocket integration for live features
5. **Mobile-First**: Responsive design from the ground up
6. **Performance Optimized**: Lazy loading, code splitting, minimal JavaScript

### Why This Architecture?

- **SEO Friendly**: Server-rendered HTML is indexable by search engines
- **Fast Initial Load**: No large JavaScript bundles to download
- **Simple Data Flow**: No complex state management libraries needed
- **Easy to Reason About**: Component logic lives in PHP, not split between frontend/backend
- **Real-Time Capable**: WebSocket integration for chat and notifications
- **Developer Experience**: Write less JavaScript, more productive PHP

---

## Technology Stack

### Core Technologies

| Technology | Version | Purpose |
|------------|---------|---------|
| **Laravel** | 12.x | Backend framework |
| **Livewire** | 3.6 | Full-stack reactive components |
| **Flux** | 2.1 | UI component library |
| **Alpine.js** | 3.x | Lightweight JavaScript framework |
| **Tailwind CSS** | 4.0 | Utility-first CSS framework |
| **Vite** | 6.x | Modern build tool and dev server |

### Supporting Libraries

| Library | Purpose |
|---------|---------|
| **Laravel Reverb** | WebSocket server for real-time features |
| **Laravel Echo** | WebSocket client library |
| **Pusher JS** | WebSocket protocol implementation |
| **Lucide Icons** | Icon library (1000+ icons) |
| **Flatpickr** | Date and time picker |
| **VideoSDK** | Video calling integration |
| **Flowbite** | Additional Tailwind components |
| **Axios** | HTTP client for AJAX requests |

---

## Project Structure

### Directory Organization

```
resources/
├── views/
│   ├── components/           # Blade components
│   │   ├── layouts/         # Layout files
│   │   │   ├── app.blade.php
│   │   │   ├── landing.blade.php
│   │   │   ├── user.blade.php
│   │   │   └── admin.blade.php
│   │   ├── ui/              # UI components
│   │   ├── navigation/      # Navigation components
│   │   ├── dashboard/       # Dashboard-specific
│   │   └── registration/    # Registration components
│   ├── livewire/            # Livewire component views
│   │   ├── auth/
│   │   ├── profile/
│   │   ├── dashboard/
│   │   ├── pages/
│   │   ├── admin/
│   │   ├── settings/
│   │   └── components/
│   ├── landing/             # Landing pages
│   │   ├── home.blade.php
│   │   ├── about.blade.php
│   │   ├── faq.blade.php
│   │   └── ...
│   ├── dashboard.blade.php
│   └── welcome.blade.php
├── js/
│   ├── app.js               # Main entry point
│   ├── echo.js              # WebSocket client
│   ├── theme.js             # Theme system
│   ├── auth.js              # Authentication flows
│   ├── messages.js          # Chat functionality
│   ├── video-call.js        # Video calling
│   ├── videosdk.js          # VideoSDK wrapper
│   ├── registration-store.js # Multi-step registration
│   ├── country-data.js      # Country selection
│   ├── date-picker.js       # Date picker
│   ├── landing.js           # Landing page
│   └── flowbite-init.js     # Flowbite initialization
├── css/
│   ├── app.css              # Main stylesheet
│   ├── design-tokens.css    # CSS variables
│   ├── components.css       # Component styles
│   ├── scrollbar.css        # Scrollbar styles
│   ├── landing-optimized.css # Landing page
│   └── telegram-mobile.css  # Mobile-specific
└── lang/                    # Translation files
    ├── en/
    ├── uz/
    └── ru/
```

### Layout Files

#### app.blade.php - Main Application Layout

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    {{ $slot }}

    @livewireScripts
</body>
</html>
```

#### landing.blade.php - Landing Page Layout

Public-facing layout for marketing pages with optimized performance.

#### user.blade.php - Authenticated User Layout

```blade
<x-layouts.app>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <livewire:components.unified-sidebar />

        <!-- Main content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <livewire:dashboard.modern-header />

            <!-- Page content -->
            <main class="p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.app>
```

#### admin.blade.php - Admin Dashboard Layout

Similar to user layout but with admin-specific navigation.

---

## JavaScript Modules

### Main Entry Point: app.js

```javascript
/**
 * Main application entry point
 * Initializes all JavaScript modules and libraries
 */

// Echo (WebSocket) initialization
import './echo';

// Lucide Icons
import { createIcons, ShieldCheck, Check, Info, Users, ... } from 'lucide';

// Alpine.js initialization
import Alpine from 'alpinejs';
import 'flowbite';
import './flowbite-init';

// Import modules
import './registration-store';
import './country-data';
import datePicker from './date-picker';
import initializeMessages from './messages';
import './theme';
import './videosdk';
import videoCall from './video-call';

// Register Alpine components
Alpine.data('datePicker', datePicker);
Alpine.data('videoCall', videoCall);

// Start Alpine
if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}

// Initialize Lucide icons
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons: iconsObject });

    // Make Lucide available globally
    window.lucide = { createIcons, icons: iconsObject };

    // Initialize messages after Echo is ready
    setTimeout(() => {
        if (window.Echo) {
            initializeMessages();
        }
    }, 500);
});

// Re-initialize icons on Livewire updates
document.addEventListener('livewire:navigated', () => {
    createIcons({ icons: iconsObject });
});

document.addEventListener('livewire:updated', () => {
    createIcons({ icons: iconsObject });
});
```

### echo.js - WebSocket Client Configuration

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Get CSRF token
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Initialize Echo
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    cluster: '',  // Empty cluster for Reverb
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': token,
        },
    },
});

console.log('Echo initialized with config:', {
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
});
```

**Usage in Components**:

```javascript
// Listen to private channel
Echo.private(`user.${userId}`)
    .listen('MatchCreated', (e) => {
        console.log('New match!', e.match);
    });

// Listen to private chat channel
Echo.private(`chat.${chatId}`)
    .listen('NewMessageEvent', (e) => {
        console.log('New message', e.message);
    })
    .listenForWhisper('typing', (e) => {
        console.log('User typing', e);
    });

// Join presence channel
Echo.join('presence-online')
    .here((users) => console.log('Users online', users))
    .joining((user) => console.log('User joined', user))
    .leaving((user) => console.log('User left', user));
```

### theme.js - Theme Management System

```javascript
/**
 * Theme system with light/dark/system modes
 * Syncs with localStorage and system preferences
 */

// Initialize theme on page load
const initTheme = () => {
    const savedTheme = localStorage.getItem('theme');
    const systemPreference = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    const theme = savedTheme || systemPreference;

    applyTheme(theme);
};

// Apply theme to document
const applyTheme = (theme) => {
    if (theme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    // Update meta theme-color for mobile browsers
    const metaThemeColor = document.querySelector('meta[name="theme-color"]');
    if (metaThemeColor) {
        metaThemeColor.setAttribute('content', theme === 'dark' ? '#1f2937' : '#ffffff');
    }
};

// Listen for theme changes from Livewire
window.addEventListener('theme-changed', (event) => {
    const theme = event.detail.theme;

    if (theme === 'system') {
        const systemPreference = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        applyTheme(systemPreference);
    } else {
        applyTheme(theme);
    }

    localStorage.setItem('theme', theme);
});

// Listen for system preference changes
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
    const savedTheme = localStorage.getItem('theme');
    if (!savedTheme || savedTheme === 'system') {
        applyTheme(e.matches ? 'dark' : 'light');
    }
});

// Initialize on load
initTheme();
```

### messages.js - Chat Functionality

```javascript
/**
 * Real-time chat functionality
 */

export default function initializeMessages() {
    Alpine.data('chatComponent', () => ({
        message: '',
        typing: false,
        typingTimeout: null,

        init() {
            // Listen for new messages via Echo
            if (window.Echo && this.chatId) {
                this.listenForMessages();
                this.listenForTyping();
            }

            // Scroll to bottom on mount
            this.$nextTick(() => this.scrollToBottom());
        },

        listenForMessages() {
            Echo.private(`chat.${this.chatId}`)
                .listen('NewMessageEvent', (e) => {
                    this.$wire.messageReceived(e);
                    this.scrollToBottom();
                });
        },

        listenForTyping() {
            Echo.private(`chat.${this.chatId}`)
                .listenForWhisper('typing', (e) => {
                    this.$wire.userTyping(e);
                });
        },

        sendMessage() {
            if (this.message.trim()) {
                this.$wire.sendMessage(this.message);
                this.message = '';
                this.stopTyping();
            }
        },

        startTyping() {
            if (!this.typing) {
                this.typing = true;
                this.$wire.updateTyping(true);
            }

            // Auto-stop typing after 3 seconds
            clearTimeout(this.typingTimeout);
            this.typingTimeout = setTimeout(() => {
                this.stopTyping();
            }, 3000);
        },

        stopTyping() {
            this.typing = false;
            this.$wire.updateTyping(false);
            clearTimeout(this.typingTimeout);
        },

        scrollToBottom() {
            const container = this.$refs.messagesContainer;
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
    }));
}
```

**Usage in Blade**:

```blade
<div x-data="chatComponent" x-init="init()">
    <!-- Messages container -->
    <div x-ref="messagesContainer" class="overflow-y-auto">
        @foreach($messages as $message)
            <div class="message">{{ $message->content }}</div>
        @endforeach
    </div>

    <!-- Input -->
    <input
        x-model="message"
        @input="startTyping()"
        @keydown.enter="sendMessage()"
    />

    <button @click="sendMessage()">Send</button>
</div>
```

### video-call.js - Video Calling

```javascript
/**
 * Video call component using VideoSDK
 */

export default (config) => ({
    meetingId: config.meetingId,
    token: config.token,
    videoEnabled: true,
    audioEnabled: true,
    meeting: null,
    participants: [],

    async init() {
        const meeting = await window.VideoSDK.initMeeting({
            meetingId: this.meetingId,
            token: this.token,
            name: config.userName,
            micEnabled: this.audioEnabled,
            webcamEnabled: this.videoEnabled,
        });

        this.meeting = meeting;

        // Listen for participant events
        meeting.on('participant-joined', (participant) => {
            this.participants.push(participant);
        });

        meeting.on('participant-left', (participant) => {
            this.participants = this.participants.filter(p => p.id !== participant.id);
        });

        meeting.join();
    },

    toggleVideo() {
        if (this.videoEnabled) {
            this.meeting.disableWebcam();
        } else {
            this.meeting.enableWebcam();
        }
        this.videoEnabled = !this.videoEnabled;
    },

    toggleAudio() {
        if (this.audioEnabled) {
            this.meeting.muteMic();
        } else {
            this.meeting.unmuteMic();
        }
        this.audioEnabled = !this.audioEnabled;
    },

    endCall() {
        this.meeting.leave();
        window.location.href = '/dashboard';
    }
});
```

### registration-store.js - Multi-Step Registration State

```javascript
/**
 * Registration form state management
 */

Alpine.store('registration', {
    step: 1,
    maxSteps: 3,
    formData: {
        email: '',
        password: '',
        first_name: '',
        last_name: '',
        date_of_birth: '',
        gender: '',
        country_id: 1,
    },

    nextStep() {
        if (this.step < this.maxSteps) {
            this.step++;
        }
    },

    previousStep() {
        if (this.step > 1) {
            this.step--;
        }
    },

    updateField(field, value) {
        this.formData[field] = value;
    },

    getProgress() {
        return (this.step / this.maxSteps) * 100;
    },

    isFirstStep() {
        return this.step === 1;
    },

    isLastStep() {
        return this.step === this.maxSteps;
    }
});
```

### date-picker.js - Date Picker Component

```javascript
import flatpickr from 'flatpickr';

export default () => ({
    picker: null,

    init() {
        this.picker = flatpickr(this.$refs.input, {
            dateFormat: 'Y-m-d',
            maxDate: 'today',
            minDate: new Date(new Date().getFullYear() - 100, 0, 1),
            defaultDate: this.value || null,
            onChange: (selectedDates, dateStr) => {
                this.$wire.set(this.field, dateStr);
            }
        });
    },

    destroy() {
        if (this.picker) {
            this.picker.destroy();
        }
    }
});
```

---

## CSS Architecture

### Tailwind CSS 4.0 Configuration

**File**: `tailwind.config.js`

```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Livewire/**/*.php",
    "./app/Http/Livewire/**/*.php",
    "./node_modules/flowbite/**/*.js"
  ],
  theme: {
    extend: {},
  },
  plugins: [
    require('flowbite/plugin')
  ],
  safelist: [
    'animate-pulse',
    'animate-bounce',
    'animate-spin',
    'animate-ping',
    'animate-fade-in',
    'animate-slide-up',
    'animate-bounce-in',
    'animate-float',
  ]
}
```

### Main Stylesheet: app.css

```css
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';
@import './design-tokens.css';
@import './components.css';
@import './scrollbar.css';

/* Flatpickr Date Picker Styles */
@media print, (min-width: 1px) {
    @import 'flatpickr/dist/flatpickr.min.css';
    @import 'flatpickr/dist/themes/material_blue.css';
}

/* Global reset */
@layer base {
    html, body {
        margin: 0;
        padding: 0;
        width: 100%;
        height: 100%;
    }
}

/* Flux customization */
[data-flux-field]:not(ui-radio) {
    @apply grid gap-2;
}

[data-flux-label] {
    @apply !mb-0 !leading-tight;
}

input:focus[data-flux-control],
textarea:focus[data-flux-control],
select:focus[data-flux-control] {
    @apply outline-hidden ring-2 ring-accent ring-offset-2;
}
```

### Design Tokens: design-tokens.css

```css
:root {
    /* Colors */
    --color-primary: #8b5cf6;
    --color-secondary: #ec4899;
    --color-success: #10b981;
    --color-danger: #ef4444;
    --color-warning: #f59e0b;
    --color-info: #06b6d4;

    /* Spacing */
    --spacing-unit: 0.25rem;
    --spacing-xs: calc(var(--spacing-unit) * 1);
    --spacing-sm: calc(var(--spacing-unit) * 2);
    --spacing-md: calc(var(--spacing-unit) * 4);
    --spacing-lg: calc(var(--spacing-unit) * 6);
    --spacing-xl: calc(var(--spacing-unit) * 8);

    /* Border Radius */
    --border-radius-sm: 0.375rem;
    --border-radius-md: 0.5rem;
    --border-radius-lg: 0.75rem;
    --border-radius-xl: 1rem;

    /* Transitions */
    --transition-speed-fast: 150ms;
    --transition-speed-normal: 250ms;
    --transition-speed-slow: 350ms;

    /* Shadows */
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.dark {
    --color-background: #1f2937;
    --color-text: #f9fafb;
    --color-border: #374151;
}
```

### Component Styles: components.css

```css
/* Button Styles */
.btn-primary {
    @apply bg-gradient-to-r from-purple-600 to-pink-600
           text-white px-6 py-3 rounded-lg
           font-semibold shadow-md
           hover:from-purple-700 hover:to-pink-700
           transition-all duration-200
           focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2;
}

.btn-secondary {
    @apply bg-white dark:bg-gray-800
           text-gray-900 dark:text-white
           border-2 border-gray-300 dark:border-gray-600
           px-6 py-3 rounded-lg
           font-semibold
           hover:bg-gray-50 dark:hover:bg-gray-700
           transition-colors duration-200;
}

/* Card Styles */
.card {
    @apply bg-white dark:bg-gray-800
           rounded-lg shadow-md
           p-6
           border border-gray-200 dark:border-gray-700;
}

.card-hover {
    @apply card
           hover:shadow-lg hover:scale-105
           transition-all duration-200
           cursor-pointer;
}

/* Input Styles */
.input {
    @apply border border-gray-300 dark:border-gray-600
           rounded-lg px-4 py-2
           bg-white dark:bg-gray-900
           text-gray-900 dark:text-white
           focus:ring-2 focus:ring-purple-500 focus:border-transparent
           transition-colors duration-200;
}

/* Badge Styles */
.badge {
    @apply inline-flex items-center
           px-3 py-1 rounded-full
           text-xs font-semibold;
}

.badge-primary {
    @apply badge bg-purple-100 text-purple-800
           dark:bg-purple-900 dark:text-purple-200;
}

.badge-success {
    @apply badge bg-green-100 text-green-800
           dark:bg-green-900 dark:text-green-200;
}

/* Animation Classes */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}

.animate-slide-up {
    animation: slideUp 0.4s ease-out;
}
```

### Scrollbar Styles: scrollbar.css

```css
/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    @apply bg-gray-100 dark:bg-gray-800;
}

::-webkit-scrollbar-thumb {
    @apply bg-gray-300 dark:bg-gray-600 rounded-full;
}

::-webkit-scrollbar-thumb:hover {
    @apply bg-gray-400 dark:bg-gray-500;
}

/* Firefox */
* {
    scrollbar-width: thin;
    scrollbar-color: theme('colors.gray.300') theme('colors.gray.100');
}

.dark * {
    scrollbar-color: theme('colors.gray.600') theme('colors.gray.800');
}
```

---

## State Management

### Livewire State

Livewire components manage state on the server:

```php
class UserProfile extends Component
{
    // State properties
    public User $user;
    public string $search = '';
    public array $filters = [];

    // Computed property
    public function getFilteredPostsProperty()
    {
        return $this->user->posts()
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->get();
    }

    // State mutation
    public function updateFilters($filters)
    {
        $this->filters = $filters;
    }
}
```

### Alpine.js State

For client-side state:

```javascript
// Component-scoped state
Alpine.data('myComponent', () => ({
    open: false,
    selected: null,

    toggle() {
        this.open = !this.open;
    }
}));

// Global store
Alpine.store('app', {
    notifications: [],
    theme: 'light',

    addNotification(notification) {
        this.notifications.push(notification);
    },

    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
    }
});
```

**Usage**:

```html
<!-- Component state -->
<div x-data="myComponent">
    <button @click="toggle()">Toggle</button>
    <div x-show="open">Content</div>
</div>

<!-- Global store -->
<div x-data>
    <span x-text="$store.app.notifications.length"></span>
    <button @click="$store.app.toggleTheme()">Toggle Theme</button>
</div>
```

---

## Real-Time Integration

### Broadcasting Architecture

```
User Action → Laravel Event → Reverb Server → WebSocket → Echo Client → Livewire Component
```

### Example: Real-Time Chat

**1. Create Broadcast Event**:

```php
class NewMessageEvent implements ShouldBroadcast
{
    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("chat.{$this->message->chat_id}")];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'content' => $this->message->content,
                'user' => $this->message->user->only('id', 'name', 'avatar'),
                'created_at' => $this->message->created_at->toISOString(),
            ]
        ];
    }
}
```

**2. Dispatch Event**:

```php
// In controller or service
event(new NewMessageEvent($message));
```

**3. Listen in Livewire**:

```php
class ChatPage extends Component
{
    public $messages = [];

    protected function getListeners()
    {
        return [
            "echo-private:chat.{$this->chatId},NewMessageEvent" => 'messageReceived',
        ];
    }

    public function messageReceived($payload)
    {
        $this->messages[] = $payload['message'];
        $this->dispatch('scroll-to-bottom');
    }
}
```

**4. Update UI**:

```blade
<div wire:poll.5s>
    @foreach($messages as $message)
        <div class="message">{{ $message['content'] }}</div>
    @endforeach
</div>
```

---

## Asset Compilation

### Vite Configuration

**File**: `vite.config.js`

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});
```

### Build Commands

```bash
# Development (watch mode)
npm run dev

# Production build
npm run build

# Preview production build
npm run preview
```

### Asset Loading in Blade

```blade
<!-- Development -->
@vite(['resources/css/app.css', 'resources/js/app.js'])

<!-- Production -->
<!-- Vite automatically handles this -->
```

---

## Development Workflow

### Setting Up Development Environment

```bash
# 1. Install dependencies
composer install
npm install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Start services
composer dev  # Starts Laravel, Reverb, Queue, Vite

# Or individually:
php artisan serve          # Laravel (port 8000)
php artisan reverb:start   # WebSocket (port 8080)
php artisan queue:listen   # Queue worker
npm run dev                # Vite dev server
```

### Hot Module Replacement (HMR)

Vite provides instant feedback during development:

- CSS changes reflect immediately
- JavaScript updates without full page reload
- Livewire components auto-refresh

### Browser DevTools

**Livewire DevTools**:
- View component state
- See network requests
- Debug events

**Vue DevTools** (for Alpine.js):
- Install Alpine.js DevTools extension
- Inspect Alpine components
- View reactive data

---

## Performance Optimization

### Code Splitting

Vite automatically splits code:

```javascript
// Dynamic import for heavy modules
const module = await import('./heavy-module.js');

// Route-based code splitting
const VideoCall = () => import('./components/VideoCall.vue');
```

### Lazy Loading

**Livewire Components**:

```blade
<!-- Defer loading until component is visible -->
<livewire:heavy-component lazy />

<!-- Load on specific event -->
<livewire:component wire:init="loadData" />
```

**Images**:

```html
<!-- Native lazy loading -->
<img src="photo.jpg" loading="lazy" />

<!-- Livewire wire:ignore for third-party libraries -->
<div wire:ignore>
    <!-- Content won't be re-rendered by Livewire -->
</div>
```

### Caching Strategies

**Browser Caching**:

```php
// In production, assets have hashed filenames
// vite automatically handles cache busting
// app.abc123.js, app.def456.css
```

**Component Caching**:

```php
// Cache expensive queries
public function render()
{
    return view('livewire.component', [
        'data' => Cache::remember("component-{$this->id}", 3600, function () {
            return $this->fetchExpensiveData();
        }),
    ]);
}
```

### Database Query Optimization

```php
// Eager loading
User::with('profile', 'photos', 'preferences')->get();

// Select only needed columns
User::select('id', 'name', 'email')->get();

// Pagination
User::paginate(20);
```

---

## Testing

### Frontend Testing with Pest

```php
// Livewire component test
test('user can update profile', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BasicInfo::class)
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->call('save')
        ->assertDispatched('profile-updated')
        ->assertHasNoErrors();

    expect($user->fresh()->first_name)->toBe('John');
});

// JavaScript test with Pest + Playwright
test('theme switcher works', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/dashboard')
            ->click('@theme-switcher')
            ->click('@dark-mode')
            ->assertPresent('.dark')
            ->assertCookie('theme', 'dark');
    });
});
```

### Visual Regression Testing

```bash
# Install Percy
npm install --save-dev @percy/cli

# Take snapshots
npx percy snapshot screenshots/
```

---

## Best Practices

### 1. Component Organization

```php
// ✅ Good: Single responsibility
class UserProfile extends Component
{
    public User $user;

    public function render()
    {
        return view('livewire.user-profile');
    }
}

// ❌ Bad: Too many responsibilities
class Dashboard extends Component
{
    // Handles users, posts, comments, messages...
}
```

### 2. Property Types

```php
// ✅ Good: Type-hinted properties
public User $user;
public string $search = '';
public int $page = 1;

// ❌ Bad: No types
public $user;
public $search;
```

### 3. Events

```php
// ✅ Good: Descriptive names
$this->dispatch('profile-updated');
$this->dispatch('match-created', matchId: $match->id);

// ❌ Bad: Generic names
$this->dispatch('update');
$this->dispatch('done');
```

### 4. Alpine.js Usage

```html
<!-- ✅ Good: Simple UI interactions -->
<div x-data="{ open: false }">
    <button @click="open = !open">Toggle</button>
    <div x-show="open">Content</div>
</div>

<!-- ❌ Bad: Complex business logic in Alpine -->
<div x-data="{ users: [], async loadUsers() { /* complex logic */ } }">
    <!-- Use Livewire for this -->
</div>
```

### 5. CSS Organization

```css
/* ✅ Good: Utility-first with custom components */
.btn-primary {
    @apply bg-purple-600 text-white px-4 py-2 rounded-lg
           hover:bg-purple-700 transition-colors;
}

/* ❌ Bad: Writing all styles from scratch */
.btn-primary {
    background-color: #8b5cf6;
    color: white;
    padding: 0.5rem 1rem;
    /* ... many more properties */
}
```

### 6. Performance

```php
// ✅ Good: Pagination and eager loading
public function render()
{
    return view('livewire.users', [
        'users' => User::with('profile')->paginate(20),
    ]);
}

// ❌ Bad: Loading all records
public function render()
{
    return view('livewire.users', [
        'users' => User::all(), // Could be thousands
    ]);
}
```

---

**Last Updated**: September 2025

This frontend architecture guide provides the foundation for building a modern, performant, and maintainable dating application with Laravel, Livewire, and Alpine.js.
