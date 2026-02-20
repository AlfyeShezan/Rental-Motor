const CACHE_NAME = 'js-rent-v1';
const ASSETS = [
    '/Rental-Motor/',
    '/Rental-Motor/index.php',
    '/Rental-Motor/assets/css/style.css',
    '/Rental-Motor/assets/img/favicon.ico'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(ASSETS))
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => response || fetch(event.request))
    );
});
