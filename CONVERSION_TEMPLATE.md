# Livewire to React Conversion Template

This guide provides step-by-step instructions for converting Livewire components to React + Inertia.js.

---

## 📋 Conversion Checklist

For each component conversion:

- [ ] Read Livewire component to understand logic
- [ ] Read Livewire Blade view to understand UI
- [ ] Identify state (public properties)
- [ ] Identify methods (public functions)
- [ ] Identify real-time listeners
- [ ] Create React component file
- [ ] Convert markup from Blade to JSX
- [ ] Convert state to `useState`
- [ ] Convert methods to functions
- [ ] Convert events to callbacks
- [ ] Add Inertia `useForm` for forms
- [ ] Test functionality
- [ ] Test on mobile
- [ ] Update route to use Inertia
- [ ] Mark as complete in `MIGRATION_PROGRESS.md`

---

## 🔄 Pattern Conversions

### 1. Public Properties → useState

**Livewire:**
```php
class Counter extends Component
{
    public int $count = 0;
    public string $message = 'Hello';
}
```

**React:**
```jsx
export default function Counter() {
    const [count, setCount] = useState(0);
    const [message, setMessage] = useState('Hello');
}
```

---

### 2. Public Methods → Functions

**Livewire:**
```php
public function increment()
{
    $this->count++;
}

public function decrement()
{
    $this->count--;
}
```

**React:**
```jsx
const increment = () => {
    setCount(count + 1);
};

const decrement = () => {
    setCount(count - 1);
};
```

---

### 3. Computed Properties → useMemo

**Livewire:**
```php
public function getFullNameProperty()
{
    return $this->firstName . ' ' . $this->lastName;
}

// Usage: {{ $this->fullName }}
```

**React:**
```jsx
const fullName = useMemo(() => {
    return firstName + ' ' + lastName;
}, [firstName, lastName]);

// Usage: {fullName}
```

---

### 4. Lifecycle Hooks → useEffect

**Livewire:**
```php
public function mount()
{
    $this->loadData();
}

public function updated($property)
{
    if ($property === 'search') {
        $this->searchUsers();
    }
}
```

**React:**
```jsx
// mount equivalent
useEffect(() => {
    loadData();
}, []);

// updated equivalent
useEffect(() => {
    searchUsers();
}, [search]);
```

---

### 5. wire:model → Controlled Input

**Livewire:**
```blade
<input wire:model="email" type="email" />
<input wire:model.debounce="search" type="text" />
```

**React:**
```jsx
<input
    type="email"
    value={email}
    onChange={(e) => setEmail(e.target.value)}
/>

<input
    type="text"
    value={search}
    onChange={(e) => {
        const value = e.target.value;
        clearTimeout(debounceTimer);
        setDebounceTimer(setTimeout(() => setSearch(value), 300));
    }}
/>
```

---

### 6. wire:click → onClick

**Livewire:**
```blade
<button wire:click="save">Save</button>
<button wire:click="delete({{ $id }})">Delete</button>
```

**React:**
```jsx
<button onClick={save}>Save</button>
<button onClick={() => deleteItem(id)}>Delete</button>
```

---

### 7. wire:submit → onSubmit

**Livewire:**
```blade
<form wire:submit.prevent="save">
    <input wire:model="name" />
    <button type="submit">Save</button>
</form>
```

**React:**
```jsx
const handleSubmit = (e) => {
    e.preventDefault();
    save();
};

<form onSubmit={handleSubmit}>
    <input
        value={name}
        onChange={(e) => setName(e.target.value)}
    />
    <button type="submit">Save</button>
</form>
```

---

### 8. Forms with Inertia

**Livewire:**
```php
public function save()
{
    $this->validate([
        'name' => 'required',
        'email' => 'required|email',
    ]);

    User::create([
        'name' => $this->name,
        'email' => $this->email,
    ]);

    session()->flash('success', 'User created!');
    return redirect()->route('users.index');
}
```

**React:**
```jsx
import { useForm } from '@inertiajs/react';

export default function CreateUser() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post('/users', {
            onSuccess: () => {
                // Flash message handled by HandleInertiaRequests
            },
        });
    };

    return (
        <form onSubmit={submit}>
            <input
                value={data.name}
                onChange={e => setData('name', e.target.value)}
            />
            {errors.name && <div>{errors.name}</div>}

            <input
                value={data.email}
                onChange={e => setData('email', e.target.value)}
            />
            {errors.email && <div>{errors.email}</div>}

            <button type="submit" disabled={processing}>
                Save
            </button>
        </form>
    );
}
```

---

### 9. File Uploads

**Livewire:**
```blade
<input type="file" wire:model="photo">
```

**React:**
```jsx
const { data, setData, post } = useForm({
    photo: null,
});

<input
    type="file"
    onChange={(e) => setData('photo', e.target.files[0])}
/>

// Submit
post('/profile/photo', {
    forceFormData: true,
});
```

---

### 10. Events / Dispatching

**Livewire:**
```php
// Dispatch
$this->dispatch('user-created', userId: $user->id);

// Listen
#[On('user-created')]
public function handleUserCreated($userId)
{
    $this->refreshUsers();
}
```

**React:**
```jsx
// Use props for parent-child communication
<ChildComponent onUserCreated={(userId) => refreshUsers(userId)} />

// Or use Context for global state
const { dispatch } = useAppContext();
dispatch({ type: 'USER_CREATED', payload: userId });

// Or use a state management library (Zustand, Redux)
```

---

### 11. Real-time with Echo

**Livewire:**
```php
protected $listeners = ['echo:chat.{chatId},NewMessageEvent' => 'handleNewMessage'];

public function handleNewMessage($data)
{
    $this->messages[] = $data['message'];
}
```

**React:**
```jsx
useEffect(() => {
    window.Echo.private(`chat.${chatId}`)
        .listen('NewMessageEvent', (e) => {
            setMessages(prev => [...prev, e.message]);
        });

    return () => {
        window.Echo.leave(`chat.${chatId}`);
    };
}, [chatId]);
```

---

### 12. Conditional Rendering

**Livewire:**
```blade
@if($isAdmin)
    <div>Admin Panel</div>
@elseif($isModerator)
    <div>Moderator Panel</div>
@else
    <div>User Panel</div>
@endif
```

**React:**
```jsx
{isAdmin ? (
    <div>Admin Panel</div>
) : isModerator ? (
    <div>Moderator Panel</div>
) : (
    <div>User Panel</div>
)}
```

---

### 13. Loops

**Livewire:**
```blade
@foreach($users as $user)
    <div wire:key="user-{{ $user->id }}">
        {{ $user->name }}
    </div>
@endforeach
```

**React:**
```jsx
{users.map(user => (
    <div key={user.id}>
        {user.name}
    </div>
))}
```

---

### 14. Loading States

**Livewire:**
```blade
<button wire:click="save" wire:loading.attr="disabled">
    Save
</button>

<div wire:loading>
    Saving...
</div>
```

**React:**
```jsx
const [loading, setLoading] = useState(false);

<button onClick={save} disabled={loading}>
    Save
</button>

{loading && <div>Saving...</div>}

// Or with Inertia useForm
const { processing } = useForm();

<button disabled={processing}>Save</button>
{processing && <div>Saving...</div>}
```

---

### 15. Polling / Intervals

**Livewire:**
```blade
<div wire:poll.5s="refreshData">
    {{ $lastUpdated }}
</div>
```

**React:**
```jsx
useEffect(() => {
    const interval = setInterval(() => {
        refreshData();
    }, 5000);

    return () => clearInterval(interval);
}, []);
```

---

## 🏗️ Component Structure Template

```jsx
import { Head, useForm, usePage } from '@inertiajs/react';
import { useState, useEffect, useMemo } from 'react';

export default function ComponentName({ propFromController }) {
    // 1. Shared props from Inertia
    const { auth, flash } = usePage().props;

    // 2. Local state (was Livewire public properties)
    const [count, setCount] = useState(0);
    const [loading, setLoading] = useState(false);

    // 3. Form state (if needed)
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
    });

    // 4. Computed values (was Livewire computed properties)
    const displayName = useMemo(() => {
        return data.name.toUpperCase();
    }, [data.name]);

    // 5. Effects (was Livewire mount/updated hooks)
    useEffect(() => {
        // On mount
        loadInitialData();
    }, []);

    useEffect(() => {
        // When count changes
        console.log('Count changed:', count);
    }, [count]);

    // 6. Event handlers (was Livewire public methods)
    const handleIncrement = () => {
        setCount(count + 1);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/endpoint');
    };

    // 7. Helper functions
    const loadInitialData = async () => {
        // Load data
    };

    // 8. Render
    return (
        <>
            <Head title="Page Title" />

            <div className="container">
                <h1>{displayName}</h1>

                {flash.success && (
                    <div className="alert alert-success">
                        {flash.success}
                    </div>
                )}

                <button onClick={handleIncrement}>
                    Count: {count}
                </button>

                <form onSubmit={handleSubmit}>
                    <input
                        value={data.name}
                        onChange={e => setData('name', e.target.value)}
                    />
                    {errors.name && <div>{errors.name}</div>}

                    <button type="submit" disabled={processing}>
                        Submit
                    </button>
                </form>
            </div>
        </>
    );
}
```

---

## 🎨 Blade to JSX Conversions

| Blade | JSX |
|-------|-----|
| `{{ $variable }}` | `{variable}` |
| `{!! $html !!}` | `<div dangerouslySetInnerHTML={{__html: html}} />` |
| `@if($condition)` | `{condition && ...}` |
| `@foreach($items as $item)` | `{items.map(item => ...)}` |
| `class="..."` | `className="..."` |
| `@auth` | `{auth.user && ...}` |
| `@guest` | `{!auth.user && ...}` |
| `{{ route('name') }}` | `{route('name')}` |
| `@csrf` | Not needed (Inertia handles) |
| `@method('PUT')` | Use `put()` method |

---

## 🚀 Conversion Workflow

1. **Analyze Livewire Component**
   ```bash
   # Read the PHP class
   cat app/Livewire/Pages/ExamplePage.php

   # Read the Blade view
   cat resources/views/livewire/pages/example-page.blade.php
   ```

2. **Create React Component**
   ```bash
   # Create the file
   touch resources/js/Pages/Example.jsx
   ```

3. **Convert Logic**
   - Copy component structure
   - Convert properties to `useState`
   - Convert methods to functions
   - Convert computed to `useMemo`
   - Convert lifecycle to `useEffect`

4. **Convert View**
   - Copy Blade markup
   - Convert Blade syntax to JSX
   - Replace `wire:` directives
   - Update class to className
   - Fix self-closing tags

5. **Update Route**
   ```php
   // Before
   Route::get('/example', App\Livewire\Pages\ExamplePage::class);

   // After
   Route::get('/example', fn() => Inertia::render('Example'));
   ```

6. **Test**
   - Visit the page
   - Test all interactions
   - Test on mobile
   - Check console for errors

7. **Document**
   - Update `MIGRATION_PROGRESS.md`
   - Mark component as complete
   - Note any issues or edge cases

---

## ✅ Conversion Complete When:

- [ ] All functionality works exactly like Livewire version
- [ ] No console errors
- [ ] Mobile responsive
- [ ] Forms validate correctly
- [ ] Real-time features work (if applicable)
- [ ] Loading states implemented
- [ ] Error handling in place
- [ ] Route updated to use Inertia
- [ ] Livewire component can be safely deleted
- [ ] Documentation updated

---

## 💡 Tips

1. **Test incrementally** - Don't convert everything at once
2. **Keep Livewire version** - Until React version is 100% working
3. **Use browser DevTools** - React DevTools extension is helpful
4. **Check the Network tab** - Verify Inertia requests working
5. **Console log liberally** - During development
6. **Mobile first** - Test responsiveness early
7. **Accessibility** - Maintain keyboard navigation and ARIA labels

---

## 🐛 Common Mistakes

1. **Forgetting dependencies** in `useEffect`
2. **Not preventing default** on form submit
3. **Mutating state directly** instead of using setState
4. **Forgetting keys** in `.map()` loops
5. **Not cleaning up** Echo listeners
6. **Using `class`** instead of `className`
7. **Self-closing tags** not closed: `<br>` → `<br />`
8. **Forgetting `forceFormData`** for file uploads

---

## 📚 References

- [React Hooks](https://react.dev/reference/react/hooks)
- [Inertia.js Manual](https://inertiajs.com/manual-visits)
- [useForm Hook](https://inertiajs.com/forms#form-helper)
- [Shared Data](https://inertiajs.com/shared-data)

---

Use this template for each component you convert. Good luck! 🚀
