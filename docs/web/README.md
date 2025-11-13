# YorYor Web Application Documentation

**Quick Start Guide for Frontend Developers**

---

## 🚀 Quick Start

### Prerequisites
```bash
# Install dependencies
composer install
npm install
```

### Development Server
```bash
# Start all services (Laravel, Reverb, Queue, Vite)
composer dev

# Or individually:
php artisan serve          # Laravel server (port 8000)
php artisan reverb:start   # WebSocket server (port 8080)
npm run dev               # Vite dev server (HMR)
```

Visit: `http://localhost:8000`

---

## 📚 Documentation Files

| File | Description |
|------|-------------|
| **[COMPONENTS.md](COMPONENTS.md)** | Complete catalog of 60+ Livewire components |
| **[FRONTEND_ARCHITECTURE.md](FRONTEND_ARCHITECTURE.md)** | Frontend structure, modules, and patterns |
| **[THEMING.md](THEMING.md)** | Dark/light mode, icons, design tokens |

---

## 🛠️ Technology Stack

### Core Technologies
- **Livewire 3.6**: Full-stack reactive components
- **Flux 2.1**: Premium UI component library
- **Tailwind CSS 4.0**: Utility-first CSS framework
- **Alpine.js 3.14**: Lightweight JavaScript framework
- **Vite 6.0**: Build tool with HMR
- **Lucide Icons**: 1000+ beautiful icons

### Real-Time Features
- **Laravel Reverb**: WebSocket server
- **Laravel Echo**: WebSocket client
- **Pusher JS**: Protocol for real-time

---

## 🎨 Creating a New Livewire Component

### 1. Generate Component
```bash
php artisan make:livewire Profile/BasicInfo
```

This creates:
- `app/Livewire/Profile/BasicInfo.php` (Component class)
- `resources/views/livewire/profile/basic-info.blade.php` (View)

### 2. Component Class Example
```php
<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Livewire\Attributes\On;

class BasicInfo extends Component
{
    // Public properties (automatically reactive)
    public string $name = '';
    public string $bio = '';

    // Mount lifecycle hook
    public function mount()
    {
        $this->name = auth()->user()->name;
        $this->bio = auth()->user()->profile->bio ?? '';
    }

    // Action method
    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'bio' => 'required|string|max:500',
        ]);

        auth()->user()->update(['name' => $this->name]);
        auth()->user()->profile->update(['bio' => $this->bio]);

        $this->dispatch('profile-updated');
        session()->flash('message', 'Profile updated successfully!');
    }

    // Listen to events
    #[On('profile-refresh')]
    public function refreshProfile()
    {
        $this->mount();
    }

    public function render()
    {
        return view('livewire.profile.basic-info');
    }
}
```

### 3. View Example
```blade
<div>
    <form wire:submit="save">
        <div class="space-y-4">
            <!-- Name Input -->
            <div>
                <label for="name" class="block text-sm font-medium">
                    Name
                </label>
                <input
                    type="text"
                    id="name"
                    wire:model="name"
                    class="mt-1 block w-full rounded-md border-gray-300"
                />
                @error('name')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Bio Textarea -->
            <div>
                <label for="bio" class="block text-sm font-medium">
                    Bio
                </label>
                <textarea
                    id="bio"
                    wire:model="bio"
                    rows="4"
                    class="mt-1 block w-full rounded-md border-gray-300"
                ></textarea>
                @error('bio')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>Save Changes</span>
                <span wire:loading>Saving...</span>
            </button>
        </div>
    </form>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="mt-4 p-4 bg-green-100 text-green-700 rounded-md">
            {{ session('message') }}
        </div>
    @endif
</div>
```

### 4. Use Component in View
```blade
<livewire:profile.basic-info />

{{-- Or with props --}}
<livewire:profile.basic-info :userId="$userId" />
```

---

## 🎯 Common Patterns

### Form Validation
```php
public function save()
{
    $validated = $this->validate([
        'email' => 'required|email',
        'password' => 'required|min:8',
    ]);

    // Use validated data
}
```

### Real-Time Validation
```blade
<input
    type="email"
    wire:model.live="email"
/>
```

### Loading States
```blade
<button wire:loading.attr="disabled">
    <span wire:loading.remove>Submit</span>
    <span wire:loading>Loading...</span>
</button>

<div wire:loading class="spinner"></div>
```

### Dispatching Events
```php
// Dispatch event
$this->dispatch('profile-updated', userId: $user->id);

// Dispatch to specific component
$this->dispatch('refresh-stats')->to(DashboardStats::class);

// Dispatch globally (including JavaScript)
$this->dispatch('notification',
    title: 'Success',
    message: 'Profile updated'
);
```

### Listening to Events
```php
// In component class
#[On('profile-updated')]
public function handleProfileUpdate($userId)
{
    // Refresh data
}
```

```blade
<!-- In view (JavaScript) -->
<script>
document.addEventListener('livewire:initialized', () => {
    Livewire.on('notification', (event) => {
        alert(event.title + ': ' + event.message);
    });
});
</script>
```

---

## 🎨 Styling with Tailwind CSS

### Dark Mode
```blade
<div class="bg-white dark:bg-gray-900">
    <p class="text-gray-900 dark:text-white">
        Content that adapts to theme
    </p>
</div>
```

### Responsive Design
```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
    <!-- Content -->
</div>
```

### Custom Components
```blade
<!-- Button component -->
<button class="btn btn-primary">
    Click me
</button>

<!-- Card component -->
<div class="card">
    <div class="card-header">Title</div>
    <div class="card-body">Content</div>
</div>
```

---

## 🔄 Real-Time Features

### Setup Laravel Echo
```javascript
// resources/js/echo.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

### Listen to Events
```javascript
// Listen to private channel
Echo.private(`chat.${chatId}`)
    .listen('NewMessageEvent', (e) => {
        console.log('New message:', e.message);
        // Update UI
    });

// Listen to presence channel
Echo.join(`chat.${chatId}`)
    .here((users) => {
        console.log('Users here:', users);
    })
    .joining((user) => {
        console.log('User joined:', user.name);
    })
    .leaving((user) => {
        console.log('User left:', user.name);
    });
```

---

## 🎭 Using Icons

### Lucide Icons
```blade
<!-- Basic icon -->
<flux:icon.user class="size-6" />

<!-- With custom classes -->
<flux:icon.heart class="size-8 text-red-500" />

<!-- In buttons -->
<button class="flex items-center gap-2">
    <flux:icon.plus class="size-4" />
    Add New
</button>
```

---

## 🌙 Theme Switching

### ThemeSwitcher Component
```blade
<!-- Include in layout -->
<livewire:theme-switcher />
```

### Manual Theme Toggle
```javascript
// resources/js/theme.js
export const toggleTheme = () => {
    const theme = localStorage.getItem('theme') === 'dark' ? 'light' : 'dark';
    localStorage.setItem('theme', theme);
    document.documentElement.classList.toggle('dark', theme === 'dark');
};
```

---

## 🧪 Testing Livewire Components

### Feature Test Example
```php
<?php

use App\Livewire\Profile\BasicInfo;
use App\Models\User;

it('updates user profile', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BasicInfo::class)
        ->set('name', 'New Name')
        ->set('bio', 'New bio')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('profile-updated');

    expect($user->fresh()->name)->toBe('New Name');
});

it('validates required fields', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BasicInfo::class)
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});
```

---

## 📦 Project Structure

```
resources/
├── views/
│   ├── livewire/           # Livewire component views
│   │   ├── auth/
│   │   ├── profile/
│   │   ├── dashboard/
│   │   └── pages/
│   ├── components/         # Blade components
│   ├── layouts/           # Layout files
│   └── welcome.blade.php  # Landing page
├── js/
│   ├── app.js            # Main entry point
│   ├── echo.js           # WebSocket setup
│   ├── theme.js          # Theme management
│   └── messages.js       # Chat functionality
└── css/
    ├── app.css           # Main stylesheet
    ├── components.css    # Component styles
    └── design-tokens.css # Design tokens
```

---

## 🐛 Common Issues

### Livewire Not Updating
```blade
<!-- Add wire:key to list items -->
@foreach($items as $item)
    <div wire:key="item-{{ $item->id }}">
        {{ $item->name }}
    </div>
@endforeach
```

### CSRF Token Mismatch
```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### HMR Not Working
```bash
# Restart Vite
npm run dev
```

---

## 📚 Learn More

- **[Components Catalog](COMPONENTS.md)** - All 60+ components with examples
- **[Frontend Architecture](FRONTEND_ARCHITECTURE.md)** - Detailed architecture guide
- **[Theming Guide](THEMING.md)** - Dark mode and design system
- **[Livewire Docs](https://livewire.laravel.com)** - Official Livewire documentation
- **[Flux Docs](https://flux.laravel.com)** - Flux component library

---

## 📞 Support

- 💬 **Issues**: [GitHub Issues](https://github.com/yoryor/yoryor-dating-app/issues)
- 📖 **Full Docs**: [Documentation Hub](../README.md)
- 🎓 **Livewire**: [livewire.laravel.com](https://livewire.laravel.com)

---

**Happy Coding!** 🎨
