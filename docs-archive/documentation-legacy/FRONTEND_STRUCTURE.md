# YorYor Frontend Structure Documentation

## Table of Contents
- [Overview](#overview)
- [Technology Stack](#technology-stack)
- [Livewire Components](#livewire-components)
- [View Structure](#view-structure)
- [JavaScript Architecture](#javascript-architecture)
- [Styling System](#styling-system)
- [WebSocket Client](#websocket-client)
- [Component Patterns](#component-patterns)
- [State Management](#state-management)
- [Performance Optimization](#performance-optimization)

---

## Overview

YorYor's frontend is built using a modern, hybrid approach combining Laravel Livewire for server-side reactivity with Alpine.js for client-side interactivity. This architecture provides the developer experience of a SPA while maintaining the simplicity and SEO benefits of server-side rendering.

### Architecture Principles
- **Server-Side First**: Livewire handles most UI logic
- **Progressive Enhancement**: JavaScript enhances, doesn't require
- **Component-Based**: Reusable, isolated components
- **Real-Time Ready**: WebSocket integration for live updates
- **Mobile-First**: Responsive design from the ground up
- **Performance Optimized**: Lazy loading, caching, minimal JavaScript

---

## Technology Stack

### Core Technologies
- **Laravel 12**: Backend framework
- **Livewire 3**: Full-stack reactive components
- **Alpine.js 3**: Lightweight JavaScript framework
- **Tailwind CSS 4**: Utility-first CSS framework
- **Vite**: Modern build tool and dev server
- **Flowbite**: Tailwind component library

### Additional Libraries
- **Laravel Reverb**: WebSocket server (Laravel Echo)
- **Pusher JS**: WebSocket client library
- **Lucide Icons**: Icon library
- **Flatpickr**: Date picker
- **VideoSDK**: Video calling
- **Axios**: HTTP client

---

## Livewire Components

YorYor includes 60+ Livewire components organized by feature area.

### Authentication Components
Located in: `app/Livewire/Auth/`

#### Register.php
Multi-step registration flow component.

**Features:**
- Step-by-step wizard (email, password, profile info)
- Real-time validation
- Progress tracking
- Country selection with search
- Date picker integration

**Usage:**
```blade
<livewire:auth.register />
```

#### Login.php
Login form with multiple authentication methods.

**Features:**
- Email/password login
- OTP login option
- Remember me functionality
- Error handling

**Usage:**
```blade
<livewire:auth.login />
```

#### VerifyEmail.php
Email verification component.

**Features:**
- Verification code input
- Resend code functionality
- Timer countdown
- Auto-submit on complete

#### ForgotPassword.php
Password reset request form.

#### ResetPassword.php
Password reset form with token validation.

#### ConfirmPassword.php
Password confirmation for sensitive operations.

---

### Profile Components
Located in: `app/Livewire/Profile/`

#### BasicInfo.php
Basic profile information editor.

**Props:**
- User model

**Emits:**
- `profile-updated`

**Methods:**
- `save()`: Update basic info
- `rules()`: Validation rules

#### AboutYou.php
Bio and personal description editor.

**Features:**
- Character counter
- Rich text hints
- Preview mode

#### Photos.php
Photo management component.

**Features:**
- Drag-and-drop upload
- Reorder photos
- Set primary photo
- Delete photos
- Upload progress
- Image preview

**Livewire Features:**
```php
use WithFileUploads;

public function uploadPhoto()
{
    $this->validate([
        'photo' => 'image|max:5120', // 5MB
    ]);

    $path = $this->photo->store('photos', 's3');
    // Process and save
}
```

#### Preferences.php
Match preference editor.

**Features:**
- Age range slider
- Distance radius selector
- Multi-select filters
- Real-time preview of match count

#### CulturalBackground.php
Cultural and religious profile editor.

**Features:**
- Religion selection
- Sect selection (conditional)
- Religiosity level
- Prayer frequency
- Dietary preferences
- Language multi-select

#### LocationPreferences.php
Location and relocation preferences.

**Features:**
- Current city autocomplete
- Country multi-select
- Relocation willingness
- Distance preference

#### FamilyMarriage.php
Family and marriage preferences.

**Features:**
- Marital status
- Children preferences
- Family involvement level
- Living situation

#### CareerEducation.php
Career and education profile.

**Features:**
- Education level dropdown
- Field of study
- Occupation
- Income level (ranges)

#### EnhanceProfile.php
Profile enhancement suggestions.

**Features:**
- Completion percentage
- Missing sections highlight
- Quick actions
- Guided completion

#### Preview.php
Profile preview component.

**Features:**
- Full profile preview
- Edit shortcuts
- Visibility toggles
- Share profile (coming soon)

---

### Dashboard Components
Located in: `app/Livewire/Dashboard/`

#### MainDashboard.php
Main dashboard layout component.

**Features:**
- User stats
- Quick actions
- Activity feed
- Match suggestions

#### DiscoveryGrid.php
Profile discovery grid interface.

**Features:**
- Grid/card layout toggle
- Infinite scroll
- Quick like/pass actions
- Filter sidebar
- Match notifications

**Livewire Methods:**
```php
public function loadMore()
{
    $this->page++;
    $this->loadProfiles();
}

public function likeProfile($userId)
{
    $this->dispatch('profile-liked', userId: $userId);
}
```

#### SwipeCards.php
Tinder-style card swipe interface.

**Features:**
- Swipeable cards
- Swipe animations (via Alpine)
- Like/pass/super like
- Undo last swipe (premium)
- Match animation

**Alpine.js Integration:**
```html
<div x-data="swipeCards()" @swipe="handleSwipe($event)">
    <!-- Card content -->
</div>
```

#### ProfileModal.php
Profile detail modal.

**Features:**
- Full profile view
- Photo gallery
- Action buttons (like, pass, message)
- Report/block options
- Modal animations

#### ComprehensiveProfile.php
Comprehensive profile view with all sections.

**Features:**
- Tabbed interface
- Section navigation
- Edit mode toggle
- Save progress

#### StoriesBar.php
Stories horizontal scroll bar.

**Features:**
- Story circles
- Unviewed indicator
- Story upload button
- Click to view

**Template:**
```blade
<div class="flex gap-4 overflow-x-auto">
    @foreach($stories as $story)
        <div wire:click="viewStory({{ $story->id }})"
             class="story-circle {{ $story->viewed ? 'viewed' : 'unviewed' }}">
            <img src="{{ $story->user->photo }}" />
        </div>
    @endforeach
</div>
```

#### StoryViewer.php
Story viewer modal.

**Features:**
- Full-screen viewer
- Auto-advance
- Progress bars
- Swipe navigation
- Reply input
- Close button

#### ActivitySidebar.php
Real-time activity sidebar.

**Features:**
- Online matches
- Recent activity
- New likes
- Unread messages
- Quick chat access

**WebSocket Integration:**
```php
protected $listeners = [
    'echo:presence-online,here' => 'updateOnlineUsers',
    'echo-private:user.{userId},MatchCreated' => 'newMatch',
];
```

#### ModernHeader.php
Dashboard header component.

**Features:**
- Navigation menu
- Notifications dropdown
- Profile dropdown
- Search bar
- Mobile menu toggle

---

### Page Components
Located in: `app/Livewire/Pages/`

Full-page Livewire components:

#### DiscoverPage.php
Main discovery page.

#### MatchesPage.php
View all matches.

**Features:**
- Filter matches
- Sort options
- Grid/list view
- Quick chat access

#### ChatPage.php
Chat interface.

**Features:**
- Conversation list
- Message area
- Real-time updates
- Typing indicators
- Media upload

**WebSocket Listeners:**
```php
protected function getListeners()
{
    return [
        "echo-private:chat.{$this->chatId},NewMessageEvent" => 'messageReceived',
        "echo-private:chat.{$this->chatId},UserTyping" => 'userTyping',
    ];
}
```

#### MessagesPage.php
Messages inbox.

#### LikesPage.php
View received and sent likes.

#### MyProfilePage.php
User's own profile view and edit.

#### UserProfilePage.php
View other user's profile.

**Props:**
- `$userId`: User to view

**Features:**
- Public profile view
- Like/pass actions
- Report/block
- Send message (if matched)

#### SettingsPage.php
Settings main page with tabs.

#### BlockedUsersPage.php
Manage blocked users.

#### SubscriptionPage.php
Subscription plans and management.

**Features:**
- Plan comparison
- Subscribe/upgrade
- Payment integration
- Usage statistics

#### VerificationPage.php
Verification submission and status.

**Features:**
- Verification type selection
- Document upload
- Status tracking
- Requirements display

#### VideoCallPage.php
Video call interface.

**Features:**
- Video SDK integration
- Call controls
- Participant video
- Chat during call
- Screen sharing (coming soon)

#### InsightsPage.php
User analytics and insights.

**Features:**
- Profile views
- Match statistics
- Activity charts
- Comparison to averages

#### NotificationsPage.php
Notification center.

**Features:**
- Notification list
- Mark as read
- Delete notifications
- Filter by type

#### SearchPage.php
Advanced search interface.

**Features:**
- Advanced filters
- Search results
- Save search
- Sort options

---

### Admin Components
Located in: `app/Livewire/Admin/`

#### Dashboard.php
Admin dashboard overview.

**Features:**
- User statistics
- Revenue metrics
- Activity graphs
- Quick actions

#### Users.php
User management table.

**Features:**
- Search users
- Filter options
- Bulk actions
- User details modal

#### UserProfile.php
Admin view of user profile.

**Features:**
- Edit user data
- View activity
- Moderation actions
- Account status

#### Matches.php
Match monitoring.

#### Chats.php
Chat monitoring and moderation.

#### ChatDetails.php
Detailed chat view for moderation.

#### Reports.php
User report management.

**Features:**
- Report queue
- Priority sorting
- Review interface
- Action buttons
- Evidence viewer

#### Verification.php
Verification request queue.

**Features:**
- Document viewer
- Approve/reject
- Add notes
- Request more info

#### Settings.php
Platform settings management.

#### Analytics.php
Detailed analytics dashboard.

---

### Settings Components
Located in: `app/Livewire/Settings/`

#### Profile.php
Profile settings editor.

#### Password.php
Password change form.

**Features:**
- Current password verification
- Strength indicator
- Confirmation field
- Show/hide toggle

#### Appearance.php
Theme and appearance settings.

**Features:**
- Light/dark mode toggle
- Language selection
- Font size adjustment
- Color scheme (coming soon)

**Theme Toggle:**
```php
public function toggleTheme()
{
    $this->theme = $this->theme === 'light' ? 'dark' : 'light';
    $this->dispatch('theme-changed', theme: $this->theme);
}
```

#### DeleteUserForm.php
Account deletion form.

**Features:**
- Password confirmation
- Deletion reason
- Warning message
- Confirmation checkbox

---

### Component Components
Located in: `app/Livewire/Components/`

Reusable UI components:

#### Header.php
Global header component.

#### Footer.php
Global footer component.

#### LanguageSwitcher.php
Language selection dropdown.

**Features:**
- Current language display
- Language list
- Flag icons
- Persist preference

#### UnifiedSidebar.php
Main navigation sidebar.

**Features:**
- Navigation links
- Active state
- Icons
- Collapse/expand
- Mobile responsive

#### PanicButton.php
Emergency panic button component.

**Features:**
- Always visible
- One-click activation
- Confirmation modal
- Status indicator

**Usage:**
```blade
<livewire:components.panic-button />
```

---

### Utility Components

#### ThemeSwitcher.php
Theme toggle component.

#### NewsletterSignup.php
Newsletter subscription form.

#### ComingSoon.php
Coming soon page component.

---

## View Structure

### Layouts
Located in: `resources/views/components/layouts/`

#### app.blade.php
Main application layout.

**Structure:**
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
<body>
    {{ $slot }}

    @livewireScripts
</body>
</html>
```

#### landing.blade.php
Landing page layout.

#### user.blade.php
Authenticated user layout.

**Features:**
- Header
- Sidebar navigation
- Main content area
- Footer

#### admin.blade.php
Admin dashboard layout.

#### registration.blade.php
Registration flow layout.

#### coming-soon.blade.php
Coming soon page layout.

---

### View Organization
```
resources/views/
├── components/           # Blade components
│   ├── layouts/         # Layout files
│   ├── ui/              # UI components (buttons, cards, etc.)
│   ├── navigation/      # Navigation components
│   ├── dashboard/       # Dashboard-specific components
│   └── registration/    # Registration components
├── livewire/            # Livewire component views
│   ├── auth/
│   ├── profile/
│   ├── dashboard/
│   ├── pages/
│   ├── admin/
│   ├── settings/
│   └── components/
├── landing/             # Landing pages
│   ├── home.blade.php
│   ├── about.blade.php
│   ├── faq.blade.php
│   ├── privacy.blade.php
│   ├── terms.blade.php
│   ├── safety.blade.php
│   └── success-stories.blade.php
├── dashboard.blade.php  # Main dashboard
└── welcome.blade.php    # Welcome/landing
```

---

## JavaScript Architecture

### Main Entry Point
**File**: `resources/js/app.js`

```javascript
import './bootstrap';
import './echo';
import './theme';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
```

### JavaScript Modules

#### app.js
Main application entry point.

**Initializes:**
- Axios configuration
- CSRF token handling
- Global error handlers
- Service worker registration (PWA)

```javascript
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}
```

#### echo.js
WebSocket client configuration.

**Features:**
- Laravel Echo initialization
- Reverb connection setup
- Authentication endpoint
- Connection event handlers

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        },
    },
});
```

#### theme.js
Theme management system.

**Features:**
- Light/dark mode toggle
- System preference detection
- Theme persistence
- CSS variable updates

```javascript
// Initialize theme
const theme = localStorage.getItem('theme') ||
    (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

document.documentElement.classList.toggle('dark', theme === 'dark');

// Listen for theme changes
window.addEventListener('theme-changed', (event) => {
    const theme = event.detail.theme;
    document.documentElement.classList.toggle('dark', theme === 'dark');
    localStorage.setItem('theme', theme);
});
```

#### auth.js
Authentication-related JavaScript.

**Features:**
- OTP input handling
- Password visibility toggle
- Form validation
- Social login handlers

#### messages.js
Chat functionality.

**Features:**
- Message input handling
- Emoji picker integration
- Media upload preview
- Scroll to bottom
- Typing indicators

```javascript
Alpine.data('chatComponent', () => ({
    message: '',
    typing: false,

    sendMessage() {
        if (this.message.trim()) {
            this.$wire.sendMessage(this.message);
            this.message = '';
        }
    },

    startTyping() {
        if (!this.typing) {
            this.typing = true;
            this.$wire.updateTyping(true);

            setTimeout(() => {
                this.typing = false;
                this.$wire.updateTyping(false);
            }, 3000);
        }
    }
}));
```

#### video-call.js
Video call interface logic.

**Features:**
- VideoSDK initialization
- Camera/mic controls
- Call state management
- Screen sharing

#### videosdk.js
VideoSDK service wrapper.

```javascript
import { VideoSDK } from '@videosdk.live/js-sdk';

class VideoCallService {
    constructor(token) {
        this.token = token;
    }

    async initMeeting(meetingId) {
        const meeting = new VideoSDK.Meeting({
            meetingId: meetingId,
            token: this.token,
            // ... configuration
        });

        return meeting;
    }
}
```

#### registration-store.js
Registration state management.

**Features:**
- Multi-step form state
- Progress tracking
- Data persistence
- Validation state

#### country-data.js
Country selection data and utilities.

#### date-picker.js
Date picker initialization.

**Uses Flatpickr:**
```javascript
import flatpickr from 'flatpickr';

flatpickr('.date-picker', {
    dateFormat: 'Y-m-d',
    maxDate: 'today',
    minDate: new Date(new Date().getFullYear() - 100, 0, 1),
});
```

#### landing.js
Landing page interactions.

**Features:**
- Smooth scroll
- Animation triggers
- Video controls
- Newsletter signup

#### flowbite-init.js
Flowbite component initialization.

**Components:**
- Modals
- Dropdowns
- Tooltips
- Tabs
- Accordions

---

## Styling System

### Tailwind CSS Configuration
**File**: `tailwind.config.js`

```javascript
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./app/Livewire/**/*.php",
        "./node_modules/flowbite/**/*.js"
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#f0f9ff',
                    // ... color scale
                    900: '#1e3a8a',
                },
                // Custom brand colors
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('flowbite/plugin'),
    ],
}
```

### CSS Files

#### app.css
Main stylesheet.

**Includes:**
```css
@import 'tailwindcss';
@import './design-tokens.css';
@import './components.css';
@import './scrollbar.css';
```

#### design-tokens.css
CSS custom properties for theming.

```css
:root {
    --color-primary: #3b82f6;
    --color-secondary: #8b5cf6;
    --color-success: #10b981;
    --color-danger: #ef4444;
    --color-warning: #f59e0b;

    --spacing-unit: 0.25rem;
    --border-radius: 0.5rem;
    --transition-speed: 150ms;
}

.dark {
    --color-background: #1f2937;
    --color-text: #f9fafb;
}
```

#### components.css
Custom component styles.

```css
.btn-primary {
    @apply bg-primary-600 text-white px-4 py-2 rounded-lg
           hover:bg-primary-700 transition-colors;
}

.card {
    @apply bg-white dark:bg-gray-800 rounded-lg shadow-md p-6;
}

.input {
    @apply border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2
           focus:ring-2 focus:ring-primary-500;
}
```

#### scrollbar.css
Custom scrollbar styles.

```css
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
```

#### landing-optimized.css
Landing page specific styles.

#### telegram-mobile.css
Mobile-specific styles (Telegram-inspired design).

---

## WebSocket Client

### Echo Configuration

**Connection Setup:**
```javascript
// Private channels
Echo.private(`user.${userId}`)
    .listen('MatchCreated', (e) => {
        console.log('New match!', e.match);
    });

// Private chat channel
Echo.private(`chat.${chatId}`)
    .listen('NewMessageEvent', (e) => {
        console.log('New message', e.message);
    })
    .listenForWhisper('typing', (e) => {
        console.log('User typing', e);
    });

// Presence channel
Echo.join('presence-online')
    .here((users) => {
        console.log('Users online', users);
    })
    .joining((user) => {
        console.log('User joined', user);
    })
    .leaving((user) => {
        console.log('User left', user);
    });
```

### Livewire WebSocket Integration

**Component:**
```php
protected function getListeners()
{
    return [
        "echo-private:chat.{$this->chatId},NewMessageEvent" => 'messageReceived',
        "echo-private:user.{$this->userId},MatchCreated" => 'matchCreated',
        'echo:presence-online,here' => 'updateOnlineUsers',
    ];
}

public function messageReceived($payload)
{
    $this->messages[] = $payload['message'];
    $this->dispatch('scroll-to-bottom');
}
```

---

## Component Patterns

### Livewire Patterns

#### Form Component Pattern
```php
class MyForm extends Component
{
    public $formData = [];

    protected $rules = [
        'formData.name' => 'required|string|max:255',
        'formData.email' => 'required|email',
    ];

    public function save()
    {
        $validated = $this->validate();

        // Save logic

        session()->flash('message', 'Saved successfully!');
        $this->dispatch('form-saved');
    }

    public function render()
    {
        return view('livewire.my-form');
    }
}
```

#### Data Table Pattern
```php
class UsersTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(20);

        return view('livewire.users-table', compact('users'));
    }
}
```

#### Modal Pattern
```php
class UserModal extends Component
{
    public $showModal = false;
    public $userId;

    protected $listeners = ['openModal'];

    public function openModal($userId)
    {
        $this->userId = $userId;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['userId']);
    }
}
```

### Alpine.js Patterns

#### Dropdown Pattern
```html
<div x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open">Toggle</button>

    <div x-show="open" x-transition>
        <!-- Dropdown content -->
    </div>
</div>
```

#### Tabs Pattern
```html
<div x-data="{ tab: 'profile' }">
    <button @click="tab = 'profile'" :class="{ 'active': tab === 'profile' }">
        Profile
    </button>
    <button @click="tab = 'settings'" :class="{ 'active': tab === 'settings' }">
        Settings
    </button>

    <div x-show="tab === 'profile'">Profile content</div>
    <div x-show="tab === 'settings'">Settings content</div>
</div>
```

---

## State Management

### Livewire State
- Component properties for local state
- Session for persistent state
- Database for long-term state

### Alpine State
- `x-data` for component state
- `Alpine.store()` for global state
- LocalStorage for persistence

**Global Store Example:**
```javascript
Alpine.store('app', {
    theme: 'light',
    notifications: [],

    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
    },

    addNotification(notification) {
        this.notifications.push(notification);
    }
});
```

---

## Performance Optimization

### Lazy Loading
- Defer non-critical component loading
- Lazy load images
- Infinite scroll for lists

### Livewire Optimization
```php
// Use wire:loading for better UX
<button wire:click="save" wire:loading.attr="disabled">
    <span wire:loading.remove>Save</span>
    <span wire:loading>Saving...</span>
</button>

// Debounce input
<input wire:model.debounce.500ms="search" />

// Lazy loading
<input wire:model.lazy="email" />

// Defer loading
<livewire:heavy-component lazy />
```

### Caching Strategies
- Cache rendered components
- Cache API responses
- Browser caching for assets

### Code Splitting
Vite automatically splits code:
```javascript
// Dynamic imports
const module = await import('./heavy-module.js');
```

---

*This frontend architecture provides a solid foundation for building a reactive, real-time dating application with excellent performance and user experience.*

Last Updated: September 2025