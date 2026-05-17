const CACHE_NAME = "nutriai-shell-v1";
const ASSETS = [
  "./public/manifest.webmanifest",
  "./public/icons/nutriai-logo.svg",
  "./public/icons/nutriai-logo-192.png",
  "./public/icons/nutriai-logo-512.png"
];

self.addEventListener("install", event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS)).catch(() => null)
  );
  self.skipWaiting();
});

self.addEventListener("activate", event => {
  event.waitUntil(
    caches.keys().then(keys => Promise.all(
      keys.filter(key => key !== CACHE_NAME).map(key => caches.delete(key))
    ))
  );
  self.clients.claim();
});

self.addEventListener("fetch", event => {
  if (event.request.method !== "GET") return;

  event.respondWith(
    fetch(event.request)
      .catch(() => caches.match(event.request))
  );
});
