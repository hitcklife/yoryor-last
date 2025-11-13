const CACHE_NAME = 'yoryor-dating-v1.0.0';
const STATIC_CACHE = 'yoryor-static-v1.0.0';
const DYNAMIC_CACHE = 'yoryor-dynamic-v1.0.0';

// Files to cache for offline functionality
const STATIC_FILES = [
    '/',
    '/dashboard',
    '/matches',
    '/messages',
    '/profile',
    '/settings',
    '/search',
    '/notifications',
    '/subscription',
    '/verification',
    '/emergency',
    '/insights',
    '/manifest.json',
    '/offline.html'
];

// API endpoints to cache
const API_CACHE_PATTERNS = [
    /\/api\/v1\/user\/profile/,
    /\/api\/v1\/matches/,
    /\/api\/v1\/messages/,
    /\/api\/v1\/notifications/,
    /\/api\/v1\/search/
];

// Install event - cache static files
self.addEventListener('install', (event) => {
    console.log('Service Worker: Installing...');
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('Service Worker: Caching static files');
                return cache.addAll(STATIC_FILES);
            })
            .then(() => {
                console.log('Service Worker: Static files cached');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('Service Worker: Failed to cache static files', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('Service Worker: Activating...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
                            console.log('Service Worker: Deleting old cache', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker: Activated');
                return self.clients.claim();
            })
    );
});

// Fetch event - implement caching strategies
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Handle different types of requests
    if (isStaticAsset(request)) {
        event.respondWith(cacheFirst(request));
    } else if (isAPIRequest(request)) {
        event.respondWith(networkFirst(request));
    } else if (isPageRequest(request)) {
        event.respondWith(staleWhileRevalidate(request));
    } else {
        event.respondWith(networkFirst(request));
    }
});

// Cache first strategy for static assets
async function cacheFirst(request) {
    try {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.error('Cache first strategy failed:', error);
        return new Response('Offline', { status: 503 });
    }
}

// Network first strategy for API requests
async function networkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        
        return networkResponse;
    } catch (error) {
        console.log('Network failed, trying cache:', error);
        
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Return offline response for API requests
        return new Response(
            JSON.stringify({ 
                error: 'Offline', 
                message: 'You are currently offline. Please check your connection.' 
            }),
            { 
                status: 503,
                headers: { 'Content-Type': 'application/json' }
            }
        );
    }
}

// Stale while revalidate strategy for pages
async function staleWhileRevalidate(request) {
    const cache = await caches.open(DYNAMIC_CACHE);
    const cachedResponse = await cache.match(request);
    
    const fetchPromise = fetch(request).then((networkResponse) => {
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    }).catch(() => {
        // Network failed, return cached version or offline page
        return cachedResponse || caches.match('/offline.html');
    });
    
    return cachedResponse || fetchPromise;
}

// Helper functions
function isStaticAsset(request) {
    const url = new URL(request.url);
    return url.pathname.startsWith('/assets/') ||
           url.pathname.startsWith('/build/') ||
           url.pathname.startsWith('/vendor/') ||
           url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/);
}

function isAPIRequest(request) {
    const url = new URL(request.url);
    return url.pathname.startsWith('/api/');
}

function isPageRequest(request) {
    const url = new URL(request.url);
    return !url.pathname.startsWith('/api/') &&
           !url.pathname.startsWith('/assets/') &&
           !url.pathname.startsWith('/build/') &&
           !url.pathname.startsWith('/vendor/') &&
           !url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/);
}

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    console.log('Service Worker: Background sync triggered', event.tag);
    
    if (event.tag === 'send-message') {
        event.waitUntil(sendPendingMessages());
    } else if (event.tag === 'update-profile') {
        event.waitUntil(updatePendingProfile());
    } else if (event.tag === 'send-like') {
        event.waitUntil(sendPendingLikes());
    }
});

// Push notifications
self.addEventListener('push', (event) => {
    console.log('Service Worker: Push notification received');
    
    const options = {
        body: event.data ? event.data.text() : 'You have a new notification',
        icon: '/icons/icon-192x192.png',
        badge: '/icons/badge-72x72.png',
        vibrate: [200, 100, 200],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'open',
                title: 'Open App',
                icon: '/icons/action-open.png'
            },
            {
                action: 'close',
                title: 'Close',
                icon: '/icons/action-close.png'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification('Yoryor Dating', options)
    );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    console.log('Service Worker: Notification clicked');
    
    event.notification.close();
    
    if (event.action === 'open') {
        event.waitUntil(
            clients.openWindow('/dashboard')
        );
    } else if (event.action === 'close') {
        // Just close the notification
        return;
    } else {
        // Default action - open the app
        event.waitUntil(
            clients.openWindow('/dashboard')
        );
    }
});

// Helper functions for background sync
async function sendPendingMessages() {
    try {
        // Get pending messages from IndexedDB
        const pendingMessages = await getPendingMessages();
        
        for (const message of pendingMessages) {
            try {
                const response = await fetch('/api/v1/messages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${message.token}`
                    },
                    body: JSON.stringify(message.data)
                });
                
                if (response.ok) {
                    await removePendingMessage(message.id);
                }
            } catch (error) {
                console.error('Failed to send pending message:', error);
            }
        }
    } catch (error) {
        console.error('Background sync for messages failed:', error);
    }
}

async function updatePendingProfile() {
    try {
        // Get pending profile updates from IndexedDB
        const pendingUpdates = await getPendingProfileUpdates();
        
        for (const update of pendingUpdates) {
            try {
                const response = await fetch('/api/v1/user/profile', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${update.token}`
                    },
                    body: JSON.stringify(update.data)
                });
                
                if (response.ok) {
                    await removePendingProfileUpdate(update.id);
                }
            } catch (error) {
                console.error('Failed to update pending profile:', error);
            }
        }
    } catch (error) {
        console.error('Background sync for profile updates failed:', error);
    }
}

async function sendPendingLikes() {
    try {
        // Get pending likes from IndexedDB
        const pendingLikes = await getPendingLikes();
        
        for (const like of pendingLikes) {
            try {
                const response = await fetch(`/api/v1/likes/${like.userId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${like.token}`
                    }
                });
                
                if (response.ok) {
                    await removePendingLike(like.id);
                }
            } catch (error) {
                console.error('Failed to send pending like:', error);
            }
        }
    } catch (error) {
        console.error('Background sync for likes failed:', error);
    }
}

// IndexedDB helper functions (simplified)
async function getPendingMessages() {
    // Implementation would use IndexedDB
    return [];
}

async function removePendingMessage(id) {
    // Implementation would use IndexedDB
    return true;
}

async function getPendingProfileUpdates() {
    // Implementation would use IndexedDB
    return [];
}

async function removePendingProfileUpdate(id) {
    // Implementation would use IndexedDB
    return true;
}

async function getPendingLikes() {
    // Implementation would use IndexedDB
    return [];
}

async function removePendingLike(id) {
    // Implementation would use IndexedDB
    return true;
}