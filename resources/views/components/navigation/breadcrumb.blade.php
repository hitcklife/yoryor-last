@props([
    'items' => []
])

@php
// Default breadcrumb items based on current route
$defaultItems = [
    'dashboard' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')]
    ],
    'matches' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Matches', 'url' => route('matches')]
    ],
    'messages' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Messages', 'url' => route('messages')]
    ],
    'messages.chat' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Messages', 'url' => route('messages')],
        ['name' => 'Chat', 'url' => '#']
    ],
    'my-profile' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'My Profile', 'url' => route('my-profile')]
    ],
    'user.profile.show' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Profile', 'url' => '#']
    ],
    'settings' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Settings', 'url' => route('settings')]
    ],
    'notifications' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Notifications', 'url' => route('notifications')]
    ],
    'blocked-users' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Blocked Users', 'url' => route('blocked-users')]
    ],
    'subscription' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Subscription', 'url' => route('subscription')]
    ],
    'verification' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Verification', 'url' => route('verification')]
    ],
    'search' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Search', 'url' => route('search')]
    ],
    'video-call' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Video Call', 'url' => route('video-call')]
    ],
    'emergency' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Emergency', 'url' => route('emergency')]
    ],
    'insights' => [
        ['name' => 'Dashboard', 'url' => route('dashboard')],
        ['name' => 'Insights', 'url' => route('insights')]
    ]
];

// Get current route name
$currentRoute = request()->route()?->getName();
$breadcrumbItems = $items ?: ($defaultItems[$currentRoute] ?? []);
@endphp

@if(count($breadcrumbItems) > 1)
    <nav class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400 mb-6">
        <ol class="flex items-center space-x-2">
            @foreach($breadcrumbItems as $index => $item)
                <li class="flex items-center">
                    @if($index > 0)
                        <i data-lucide="chevron-right" class="w-4 h-4 mx-2 text-gray-400"></i>
                    @endif
                    
                    @if($index === count($breadcrumbItems) - 1)
                        <!-- Current page -->
                        <span class="text-gray-900 dark:text-white font-medium">
                            {{ $item['name'] }}
                        </span>
                    @else
                        <!-- Link to previous page -->
                        <a href="{{ $item['url'] }}" 
                           class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                            {{ $item['name'] }}
                        </a>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif

@push('scripts')
<script>
    // Initialize Lucide icons for breadcrumb
    document.addEventListener('DOMContentLoaded', function() {
        if (window.lucide && window.lucide.createIcons) {
            window.lucide.createIcons({ icons: window.lucide.icons });
        }
    });
</script>
@endpush
