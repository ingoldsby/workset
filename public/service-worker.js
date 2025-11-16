const CACHE_VERSION = 'v1';
const CACHE_NAME = `workset-${CACHE_VERSION}`;
const OFFLINE_URL = '/offline';

// Cache-first resources (app shell, static assets)
const CACHE_FIRST_URLS = [
    '/',
    '/today',
    '/plan',
    '/log',
    '/programs',
    '/exercises',
    '/history',
    '/analytics',
    '/offline',
    '/build/assets/app.css',
    '/build/assets/app.js',
];

// Exercise library API endpoint (cache-first for offline access)
const EXERCISE_LIBRARY_PATTERN = /\/api\/exercises/;

// Dynamic content patterns (stale-while-revalidate)
const STALE_WHILE_REVALIDATE_PATTERNS = [
    /\/api\/programs/,
    /\/api\/sessions/,
    /\/api\/history/,
];

// Network-first patterns (always try network first)
const NETWORK_FIRST_PATTERNS = [
    /\/api\/auth/,
    /\/livewire/,
    /\/api\/sync/,
];

/**
 * Install event - cache app shell and critical resources
 */
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');

    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[Service Worker] Caching app shell');
                return cache.addAll(CACHE_FIRST_URLS);
            })
            .then(() => self.skipWaiting())
    );
});

/**
 * Activate event - clean up old caches
 */
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating...');

    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('[Service Worker] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

/**
 * Fetch event - implement caching strategies
 */
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip chrome extensions and non-http(s) requests
    if (!url.protocol.startsWith('http')) {
        return;
    }

    // Determine caching strategy based on URL pattern
    if (EXERCISE_LIBRARY_PATTERN.test(url.pathname)) {
        event.respondWith(cacheFirstStrategy(request));
    } else if (STALE_WHILE_REVALIDATE_PATTERNS.some(pattern => pattern.test(url.pathname))) {
        event.respondWith(staleWhileRevalidateStrategy(request));
    } else if (NETWORK_FIRST_PATTERNS.some(pattern => pattern.test(url.pathname))) {
        event.respondWith(networkFirstStrategy(request));
    } else if (CACHE_FIRST_URLS.includes(url.pathname)) {
        event.respondWith(cacheFirstStrategy(request));
    } else {
        event.respondWith(networkFirstStrategy(request));
    }
});

/**
 * Cache-first strategy: Check cache, fallback to network
 */
async function cacheFirstStrategy(request) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);

    if (cached) {
        console.log('[Service Worker] Cache hit:', request.url);
        return cached;
    }

    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.error('[Service Worker] Fetch failed:', error);

        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            const offlinePage = await cache.match(OFFLINE_URL);
            if (offlinePage) {
                return offlinePage;
            }
        }

        throw error;
    }
}

/**
 * Stale-while-revalidate strategy: Return cached, update in background
 */
async function staleWhileRevalidateStrategy(request) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);

    const fetchPromise = fetch(request).then((response) => {
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    }).catch((error) => {
        console.error('[Service Worker] Background fetch failed:', error);
        return cached || new Response('Offline', { status: 503 });
    });

    return cached || fetchPromise;
}

/**
 * Network-first strategy: Try network, fallback to cache
 */
async function networkFirstStrategy(request) {
    try {
        const response = await fetch(request);

        if (response.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        console.error('[Service Worker] Network failed, trying cache:', error);

        const cache = await caches.open(CACHE_NAME);
        const cached = await cache.match(request);

        if (cached) {
            return cached;
        }

        // Return offline page for navigation requests
        if (request.mode === 'navigate') {
            const offlinePage = await cache.match(OFFLINE_URL);
            if (offlinePage) {
                return offlinePage;
            }
        }

        throw error;
    }
}

/**
 * Background sync event - sync offline data
 */
self.addEventListener('sync', (event) => {
    console.log('[Service Worker] Background sync:', event.tag);

    if (event.tag === 'sync-session-sets') {
        event.waitUntil(syncSessionSets());
    } else if (event.tag === 'sync-session-completion') {
        event.waitUntil(syncSessionCompletion());
    }
});

/**
 * Sync session sets that were logged offline
 */
async function syncSessionSets() {
    try {
        const db = await openDatabase();
        const offlineSets = await getOfflineSets(db);

        if (offlineSets.length === 0) {
            console.log('[Service Worker] No offline sets to sync');
            return;
        }

        console.log(`[Service Worker] Syncing ${offlineSets.length} offline sets`);

        for (const set of offlineSets) {
            try {
                const response = await fetch('/api/session-sets', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(set.data),
                });

                if (response.ok) {
                    await deleteOfflineSet(db, set.id);
                    console.log('[Service Worker] Synced set:', set.id);
                }
            } catch (error) {
                console.error('[Service Worker] Failed to sync set:', error);
            }
        }
    } catch (error) {
        console.error('[Service Worker] Sync failed:', error);
        throw error;
    }
}

/**
 * Sync session completion
 */
async function syncSessionCompletion() {
    try {
        const db = await openDatabase();
        const offlineSessions = await getOfflineSessions(db);

        for (const session of offlineSessions) {
            try {
                const response = await fetch(`/api/sessions/${session.id}/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ completed_at: session.completed_at }),
                });

                if (response.ok) {
                    await deleteOfflineSession(db, session.id);
                    console.log('[Service Worker] Synced session completion:', session.id);
                }
            } catch (error) {
                console.error('[Service Worker] Failed to sync session:', error);
            }
        }
    } catch (error) {
        console.error('[Service Worker] Session sync failed:', error);
        throw error;
    }
}

/**
 * Push notification event
 */
self.addEventListener('push', (event) => {
    console.log('[Service Worker] Push received');

    const data = event.data ? event.data.json() : {};
    const title = data.title || 'Workset';
    const options = {
        body: data.body || 'New notification',
        icon: '/images/icons/icon-192x192.png',
        badge: '/images/icons/badge-72x72.png',
        tag: data.tag || 'default',
        data: data.data || {},
        actions: data.actions || [],
        requireInteraction: data.requireInteraction || false,
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

/**
 * Notification click event
 */
self.addEventListener('notificationclick', (event) => {
    console.log('[Service Worker] Notification clicked:', event.notification.tag);

    event.notification.close();

    const urlToOpen = event.notification.data?.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // Focus existing window if available
                for (const client of clientList) {
                    if (client.url === urlToOpen && 'focus' in client) {
                        return client.focus();
                    }
                }

                // Open new window
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});

/**
 * IndexedDB helper functions
 */
const DB_NAME = 'workset-offline';
const DB_VERSION = 1;

function openDatabase() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;

            if (!db.objectStoreNames.contains('offlineSets')) {
                db.createObjectStore('offlineSets', { keyPath: 'id', autoIncrement: true });
            }

            if (!db.objectStoreNames.contains('offlineSessions')) {
                db.createObjectStore('offlineSessions', { keyPath: 'id' });
            }
        };
    });
}

function getOfflineSets(db) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['offlineSets'], 'readonly');
        const store = transaction.objectStore('offlineSets');
        const request = store.getAll();

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function deleteOfflineSet(db, id) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['offlineSets'], 'readwrite');
        const store = transaction.objectStore('offlineSets');
        const request = store.delete(id);

        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}

function getOfflineSessions(db) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['offlineSessions'], 'readonly');
        const store = transaction.objectStore('offlineSessions');
        const request = store.getAll();

        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

function deleteOfflineSession(db, id) {
    return new Promise((resolve, reject) => {
        const transaction = db.transaction(['offlineSessions'], 'readwrite');
        const store = transaction.objectStore('offlineSessions');
        const request = store.delete(id);

        request.onsuccess = () => resolve();
        request.onerror = () => reject(request.error);
    });
}
