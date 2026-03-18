// sw.js
const CACHE_NAME = 'mi-webapp-cache-v1';
const urlsToCache = [
  '/',
  '/main.html',
  '/css/style.css',
  '/main.js',
  '/img/icono-192x192.png',
  '/img/icono-512x512.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(urlsToCache))
  );
});

self.addEventListener('activate', event => {
  console.log('Service Worker activado');
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(response => response || fetch(event.request))
  );
});