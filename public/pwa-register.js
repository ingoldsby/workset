/**
 * PWA Service Worker Registration
 * Handles registration, updates, and offline functionality
 */

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        registerServiceWorker();
    });
}

/**
 * Register the service worker
 */
async function registerServiceWorker() {
    try {
        const registration = await navigator.serviceWorker.register('/service-worker.js', {
            scope: '/',
        });

        console.log('[PWA] Service Worker registered:', registration.scope);

        // Check for updates periodically
        setInterval(() => {
            registration.update();
        }, 60 * 60 * 1000); // Check every hour

        // Handle updates
        registration.addEventListener('updatefound', () => {
            const newWorker = registration.installing;

            newWorker.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                    showUpdateNotification();
                }
            });
        });

        // Request notification permission after first successful registration
        if (Notification.permission === 'default') {
            setTimeout(requestNotificationPermission, 5000);
        }

    } catch (error) {
        console.error('[PWA] Service Worker registration failed:', error);
    }
}

/**
 * Show update notification when new version is available
 */
function showUpdateNotification() {
    const notification = document.createElement('div');
    notification.className = 'fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-blue-600 text-white rounded-lg shadow-lg p-4 z-50 flex items-center justify-between';
    notification.innerHTML = `
        <div class="flex-1">
            <p class="font-semibold">New version available!</p>
            <p class="text-sm text-blue-100">Reload to get the latest features</p>
        </div>
        <button
            onclick="window.location.reload()"
            class="ml-4 px-4 py-2 bg-white text-blue-600 rounded font-semibold text-sm hover:bg-blue-50 transition"
        >
            Reload
        </button>
    `;

    document.body.appendChild(notification);

    // Auto-dismiss after 10 seconds
    setTimeout(() => {
        notification.remove();
    }, 10000);
}

/**
 * Request notification permission (just-in-time)
 */
async function requestNotificationPermission() {
    if (!('Notification' in window)) {
        console.log('[PWA] Notifications not supported');
        return;
    }

    if (Notification.permission === 'granted') {
        await subscribeUserToPush();
        return;
    }

    if (Notification.permission !== 'denied') {
        // Show custom prompt
        showNotificationPrompt();
    }
}

/**
 * Show custom notification permission prompt
 */
function showNotificationPrompt() {
    const prompt = document.createElement('div');
    prompt.className = 'fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-white rounded-lg shadow-xl p-4 z-50 border border-gray-200';
    prompt.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-semibold text-gray-900">Stay updated with notifications</h3>
                <p class="mt-1 text-xs text-gray-600">Get reminders for sessions and updates from your PT</p>
                <div class="mt-3 flex space-x-2">
                    <button
                        onclick="handleNotificationResponse(true)"
                        class="px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 transition"
                    >
                        Enable
                    </button>
                    <button
                        onclick="handleNotificationResponse(false)"
                        class="px-3 py-1.5 bg-gray-200 text-gray-700 text-xs font-semibold rounded hover:bg-gray-300 transition"
                    >
                        Not now
                    </button>
                </div>
            </div>
            <button
                onclick="this.closest('div').parentElement.remove()"
                class="ml-2 text-gray-400 hover:text-gray-600"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `;

    document.body.appendChild(prompt);

    window.handleNotificationResponse = async (enable) => {
        prompt.remove();

        if (enable) {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                await subscribeUserToPush();
            }
        }
    };
}

/**
 * Subscribe user to push notifications
 */
async function subscribeUserToPush() {
    try {
        const registration = await navigator.serviceWorker.ready;

        // Get VAPID public key from backend
        const response = await fetch('/api/push/vapid-public-key');
        const { publicKey } = await response.json();

        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(publicKey),
        });

        // Send subscription to backend
        await fetch('/api/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(subscription),
        });

        console.log('[PWA] Push subscription successful');
    } catch (error) {
        console.error('[PWA] Push subscription failed:', error);
    }
}

/**
 * Convert VAPID key from base64 to Uint8Array
 */
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
}

/**
 * Check if app is running in standalone mode (installed PWA)
 */
function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches ||
           window.navigator.standalone ||
           document.referrer.includes('android-app://');
}

/**
 * Show install prompt
 */
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent default mini-infobar
    e.preventDefault();
    deferredPrompt = e;

    // Show custom install prompt after delay
    if (!isStandalone()) {
        setTimeout(showInstallPrompt, 30000); // Show after 30 seconds
    }
});

/**
 * Show custom install prompt
 */
function showInstallPrompt() {
    if (!deferredPrompt) return;

    const prompt = document.createElement('div');
    prompt.className = 'fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg shadow-xl p-4 z-50';
    prompt.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="ml-3 flex-1">
                <h3 class="text-sm font-bold">Install Workset</h3>
                <p class="mt-1 text-xs text-blue-100">Add to your home screen for quick access and offline use</p>
                <div class="mt-3 flex space-x-2">
                    <button
                        onclick="handleInstallResponse(true)"
                        class="px-3 py-1.5 bg-white text-blue-600 text-xs font-semibold rounded hover:bg-blue-50 transition"
                    >
                        Install
                    </button>
                    <button
                        onclick="handleInstallResponse(false)"
                        class="px-3 py-1.5 bg-blue-500 text-white text-xs font-semibold rounded hover:bg-blue-400 transition"
                    >
                        Not now
                    </button>
                </div>
            </div>
            <button
                onclick="this.closest('div').parentElement.remove()"
                class="ml-2 text-blue-200 hover:text-white"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `;

    document.body.appendChild(prompt);

    window.handleInstallResponse = async (install) => {
        prompt.remove();

        if (install && deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            console.log('[PWA] Install prompt outcome:', outcome);
            deferredPrompt = null;
        }
    };
}

/**
 * Detect when app is successfully installed
 */
window.addEventListener('appinstalled', () => {
    console.log('[PWA] App installed successfully');
    deferredPrompt = null;
});

/**
 * Online/Offline status handling
 */
window.addEventListener('online', () => {
    console.log('[PWA] Back online');
    showConnectionStatus('online');

    // Trigger background sync
    if ('serviceWorker' in navigator && 'sync' in navigator.serviceWorker) {
        navigator.serviceWorker.ready.then((registration) => {
            registration.sync.register('sync-session-sets');
            registration.sync.register('sync-session-completion');
        });
    }
});

window.addEventListener('offline', () => {
    console.log('[PWA] Offline');
    showConnectionStatus('offline');
});

/**
 * Show connection status toast
 */
function showConnectionStatus(status) {
    const toast = document.createElement('div');
    const isOnline = status === 'online';

    toast.className = `fixed top-4 right-4 ${isOnline ? 'bg-green-600' : 'bg-orange-600'} text-white rounded-lg shadow-lg p-3 z-50 flex items-center space-x-2`;
    toast.innerHTML = `
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            ${isOnline
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
            }
        </svg>
        <span class="text-sm font-medium">
            ${isOnline ? 'Back online - Syncing data...' : 'You\'re offline - Changes will sync later'}
        </span>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 4000);
}
