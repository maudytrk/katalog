const CACHE_NAME = "rahayu-katalog-v2";

const urlsToCache = [
  "./",
  "./index.php",
  "./katalog.php",
  "./detail.php",
  "/bandingkan.php", //
  "/katalog_promo.php",
  "./css/styles.css",
  "./js/scripts.js",
  "https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css",
  "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css",
];

// Instalasi dan Cache Aset Awal
self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(urlsToCache)),
  );
  self.skipWaiting(); // Memaksa SW baru untuk langsung aktif
});

// Pembersihan Cache Versi Lama
self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cache) => {
          if (cache !== CACHE_NAME) {
            console.log("Menghapus cache lama:", cache);
            return caches.delete(cache);
          }
        }),
      );
    }),
  );
});

// Strategi Fetch: Network First
self.addEventListener("fetch", (event) => {
  // Biarkan browser menangani request POST (seperti simpan pesanan) secara otomatis
  if (event.request.method !== "GET") return;

  event.respondWith(
    fetch(event.request)
      .then((networkResponse) => {
        // Update cache dengan respon terbaru dari jaringan
        return caches.open(CACHE_NAME).then((cache) => {
          cache.put(event.request, networkResponse.clone());
          return networkResponse;
        });
      })
      .catch(() => {
        // Jika gagal koneksi (offline), ambil dari cache
        return caches.match(event.request);
      }),
  );
});
