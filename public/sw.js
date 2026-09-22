// Kharchify Service Worker v1.0.0
const CACHE_NAME = 'kharchify-cache-v1';
const OFFLINE_URL = '/offline.html';

const ASSETS_TO_CACHE = [
    '/',
    '/manifest.json',
    '/favicon.svg',
    '/images/logo-icon.svg',
    '/images/icon-192.png',
    '/images/icon-512.png',
    '/images/icon-maskable-192.png',
    '/images/icon-maskable-512.png',
    '/offline.html'
];

// Install Event
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(ASSETS_TO_CACHE).catch((err) => {
                console.warn('Some assets could not be pre-cached during SW install:', err);
            });
        })
    );
    self.skipWaiting();
});

// Activate Event (Cache Management)
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch Event
self.addEventListener('fetch', (event) => {
    // Only handle GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip non-http(s) requests
    if (!event.request.url.startsWith('http')) {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // If valid response, optionally cache static assets
                if (response && response.status === 200 && response.type === 'basic') {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        // Cache images, styles, scripts
                        if (
                            event.request.url.includes('/images/') ||
                            event.request.url.includes('/build/') ||
                            event.request.url.includes('fonts.googleapis.com') ||
                            event.request.url.includes('cdn.tailwindcss.com')
                        ) {
                            cache.put(event.request, responseClone);
                        }
                    });
                }
                return response;
            })
            .catch(() => {
                // Return cached version if available
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // If navigation request fails, return offline page
                    if (event.request.mode === 'navigate') {
                        return caches.match(OFFLINE_URL);
                    }
                    return new Response('Network error occurred', {
                        status: 503,
                        statusText: 'Service Unavailable',
                        headers: new Headers({ 'Content-Type': 'text/plain' })
                    });
                });
            })
    );
});
