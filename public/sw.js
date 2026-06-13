const CACHE_NAME = 'in-shop-v2'; // ← bumped to kill old cache

// Only cache truly static assets — NEVER HTML pages (they are dynamic/session-based)
const STATIC_ASSETS = [
  'manifest.json',
  'in_shop_app_icon.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  // Activate the new SW immediately, don't wait for old tabs to close
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  // Delete ALL old caches (e.g. in-shop-v1)
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name !== CACHE_NAME)
          .map((name) => caches.delete(name))
      );
    }).then(() => clients.claim()) // take control of all open pages now
  );
});

self.addEventListener('fetch', (event) => {
  // NETWORK-FIRST for ALL HTML navigation (Laravel pages, login, logout, etc.)
  // These are session-driven and must NEVER be served from cache
  if (
    event.request.mode === 'navigate' ||
    event.request.headers.get('Accept')?.includes('text/html')
  ) {
    event.respondWith(
      fetch(event.request, { credentials: 'same-origin' })
        .catch(() => caches.match(event.request))
    );
    return;
  }

  // CACHE-FIRST only for static assets (images, icons, fonts)
  event.respondWith(
    caches.match(event.request).then((cached) => {
      return cached || fetch(event.request);
    })
  );
});
