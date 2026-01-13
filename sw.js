const CACHE_NAME = 'agco-finance-v1';
const urlsToCache = [
    '/',
    '/assets/global/css/all.min.css',
    '/assets/global/js/jquery-3.6.0.min.js',
    '/assets/images/logoIcon/logo.png'
];

// Install service worker and cache resources
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('AGCO: Cache opened');
                return cache.addAll(urlsToCache);
            })
            .catch(err => {
                console.log('AGCO: Cache failed', err);
            })
    );
    self.skipWaiting();
});

// Activate and clean old caches
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('AGCO: Deleting old cache', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch strategy: Network first, fallback to cache
self.addEventListener('fetch', event => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') return;
    
    // Skip admin and API requests
    if (event.request.url.includes('/admin') || 
        event.request.url.includes('/api/') ||
        event.request.url.includes('/user/')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then(response => {
                // Clone the response
                const responseToCache = response.clone();
                
                // Cache successful responses
                if (response.status === 200) {
                    caches.open(CACHE_NAME)
                        .then(cache => {
                            cache.put(event.request, responseToCache);
                        });
                }
                
                return response;
            })
            .catch(() => {
                // If network fails, try cache
                return caches.match(event.request)
                    .then(response => {
                        if (response) {
                            return response;
                        }
                        // Return offline page for navigation requests
                        if (event.request.mode === 'navigate') {
                            return caches.match('/');
                        }
                    });
            })
    );
});

// Handle push notifications (future use)
self.addEventListener('push', event => {
    const options = {
        body: event.data ? event.data.text() : 'AGCO Finance থেকে নতুন আপডেট!',
        icon: '/assets/images/logoIcon/logo.png',
        badge: '/assets/images/logoIcon/logo.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        }
    };
    
    event.waitUntil(
        self.registration.showNotification('AGCO Finance', options)
    );
});

// Handle notification click
self.addEventListener('notificationclick', event => {
    event.notification.close();
    event.waitUntil(
        clients.openWindow('/')
    );
});
