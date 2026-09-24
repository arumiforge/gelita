/*
 * Service worker GELITA (aplikasi terpasang di layar utama).
 *
 * Tugasnya hanya satu: bila jaringan putus saat anak berpindah halaman,
 * tampilkan offline.html alih-alih halaman galat browser. Hanya navigasi GET
 * yang ditangani. API, event gameplay, POST formulir, dan aset tidak pernah
 * disentuh, jadi tidak ada data penelitian yang tersimpan di cache, dan antrean
 * event offline (localStorage) tetap bekerja seperti di browser biasa.
 * Penangan fetch ini juga syarat Chrome untuk menawarkan pemasangan.
 *
 * Mengubah halaman offline: naikkan versi CACHE agar salinan lama dibuang.
 */
const CACHE = 'gelita-offline-v1';
const OFFLINE_URL = new URL('offline.html', self.registration.scope).href;

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE)
      .then((cache) => cache.add(new Request(OFFLINE_URL, { cache: 'reload' })))
      .then(() => self.skipWaiting()),
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter((key) => key.startsWith('gelita-') && key !== CACHE).map((key) => caches.delete(key)));
    // Navigasi tidak menunggu service worker bangun (penting di HP murah)
    if (self.registration.navigationPreload) await self.registration.navigationPreload.enable();
    await self.clients.claim();
  })());
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.mode !== 'navigate' || request.method !== 'GET') return;

  event.respondWith((async () => {
    try {
      const preloaded = await event.preloadResponse;
      return preloaded || await fetch(request);
    } catch {
      return (await caches.match(OFFLINE_URL)) || Response.error();
    }
  })());
});
