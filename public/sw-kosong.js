/*
 * GELITA empty service worker ("kill switch" for sw.js, docs/rencana-paket-aset.md §11.1).
 *
 * It replaces sw.js on every device that loads a game page and then stays out
 * of the way: no fetch handler, so every request goes to the network exactly
 * as without a service worker. On activation it drops the marker of the
 * device's active package (`gelita-meta`), so no device reports
 * `package_mode = lengkap` or turns on short curtains while it runs. The
 * downloaded package files (`gelita-aset`, public assets only) and the
 * offline page are kept, so nothing has to be downloaded again once sw.js
 * is back.
 *
 * How it gets installed (docs/08_DEPLOYMENT.md §Asset package):
 * - `gelita.paket.aktif = false` in .env: game pages register this file
 *   instead of sw.js (game/install.js reads the flag from the boot script);
 * - or, without PHP, serve this file at /sw.js (Nginx `location = /sw.js`).
 * Removing the flag brings sw.js back on the next page load.
 */
self.addEventListener('install', (event) => {
  event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    await caches.delete('gelita-meta');
    // sw.js enabled it; with no fetch handler there is nothing to preload for
    if (self.registration.navigationPreload) await self.registration.navigationPreload.disable();
    await self.clients.claim();
  })());
});
