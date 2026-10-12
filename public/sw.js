/*
 * GELITA service worker: offline page and asset package
 * (docs/rencana-paket-aset.md §4.1, §5.5, §5.6, §9.1; docs/06_JAVASCRIPT.md
 * §Asset package — service worker and downloader).
 *
 * What it does:
 * 1. Navigations (GET) go to the network, with navigation preload. When the
 *    network is down, offline.html from the cache is shown instead of the
 *    browser's error page. HTML is never cached: it carries the student's
 *    name, progress, and CSRF token, and the server must see every GET
 *    (screen_viewed, access guards).
 * 2. Package URLs (assets/p/*, assets/k/*, paket/pkt-*.json) are answered
 *    cache-first from `gelita-aset`. The page-side downloader
 *    (core/package.js) fills that cache after checking every file's sha256;
 *    this worker never adds package files itself. A file missing from the
 *    cache comes from the network unchanged. Range requests (audio) get a
 *    206 built from the full cached copy.
 * 3. Everything else is left to the browser. /api/* and /admin* never reach
 *    respondWith(), so the panel and the API behave as without a worker.
 *
 * Invariants (tests/unit/AssetPackageServiceWorkerTest.php):
 * - every cache write goes through putChecked(), which accepts only package
 *   URLs and the offline page, and refuses responses that are not a plain
 *   200 or that carry Set-Cookie or Cache-Control private/no-store;
 * - the package holds public assets only, so a classroom device shared by
 *   many students keeps no research data in its caches.
 *
 * Emergency: sw-kosong.js is an empty worker that takes this one out of the
 * request path (docs/08_DEPLOYMENT.md §Asset package).
 *
 * Changing offline.html: raise OFFLINE_CACHE so the old copy is dropped.
 */
const SW_VERSION = 2;
const OFFLINE_CACHE = 'gelita-offline-v1';
const ASSET_CACHE = 'gelita-aset';
const META_CACHE = 'gelita-meta';
const KEEP = [OFFLINE_CACHE, ASSET_CACHE, META_CACHE];

const SCOPE = self.registration.scope;
const OFFLINE_URL = new URL('offline.html', self.registration.scope).href;
// Marker of the package active on this device, written by core/package.js
const ACTIVE_URL = new URL('gelita-meta/aktif.json', self.registration.scope).href;

// Paths relative to the scope
const PACKAGE_PATH = /^(?:assets\/p\/[0-9a-f]{16}\.[a-z0-9]{1,5}|assets\/k\/[A-Za-z0-9._-]+\/(?:[A-Za-z0-9_-][A-Za-z0-9._-]*\/)*[A-Za-z0-9_-][A-Za-z0-9._-]*|paket\/pkt-[a-z0-9.-]+\.json)$/;
const PASS_THROUGH = /^(?:api|admin)(?:[/?#]|$)/;
const NEVER_CACHE = /^(?:api|admin|masuk|daftar|keluar)(?:[/?#]|$)/;

/** Path of a same-origin URL relative to the scope, or null outside it. */
function scopedPath(url) {
  const href = typeof url === 'string' ? url : url.href;
  return href.startsWith(SCOPE) ? href.slice(SCOPE.length).replace(/[?#].*$/, '') : null;
}

/** Cache a package URL or the offline page may go to, or null. */
function cacheFor(url) {
  const path = scopedPath(url);
  if (path === null || NEVER_CACHE.test(path)) return null;
  if (path === 'offline.html') return OFFLINE_CACHE;
  return PACKAGE_PATH.test(path) ? ASSET_CACHE : null;
}

/**
 * The only cache write in this worker. Refuses anything but package URLs and
 * the offline page, and any response that could hold private data.
 */
async function putChecked(url, response) {
  const name = cacheFor(url);
  const control = (response.headers.get('Cache-Control') || '').toLowerCase();
  if (!name || response.status !== 200 || response.redirected
    || response.headers.has('Set-Cookie') || /\b(?:private|no-store)\b/.test(control)) {
    return false;
  }
  const cache = await caches.open(name);
  await cache.put(new Request(url), response);
  return true;
}

self.addEventListener('install', (event) => {
  event.waitUntil((async () => {
    try {
      const response = await fetch(new Request(OFFLINE_URL, { cache: 'reload' }));
      await putChecked(OFFLINE_URL, response);
    } catch {
      // No offline page this time; the worker still installs
    }
    await self.skipWaiting();
  })());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter((key) => key.startsWith('gelita-') && !KEEP.includes(key)).map((key) => caches.delete(key)));
    // Navigations do not wait for the worker to start (matters on cheap phones)
    if (self.registration.navigationPreload) await self.registration.navigationPreload.enable();
    await self.clients.claim();
  })());
});

self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.method !== 'GET') return;
  const path = scopedPath(request.url);
  // Other origins, other scopes, the API, and the admin panel: straight to the network
  if (path === null || PASS_THROUGH.test(path)) return;

  if (request.mode === 'navigate') {
    event.respondWith(navigate(event));
    return;
  }

  if (PACKAGE_PATH.test(path)) event.respondWith(fromPackage(request));
});

/** Navigation: network only (never cached); offline.html when the network is down. */
async function navigate(event) {
  try {
    const preloaded = await event.preloadResponse;
    return preloaded || await fetch(event.request);
  } catch {
    return (await caches.match(OFFLINE_URL, { cacheName: OFFLINE_CACHE })) || Response.error();
  }
}

/** Package file: cache first (URLs never change content), else the network. */
async function fromPackage(request) {
  const key = request.url.replace(/[?#].*$/, '');
  let cached = null;
  try {
    cached = await (await caches.open(ASSET_CACHE)).match(key, { ignoreVary: true });
  } catch {
    cached = null;
  }
  if (!cached) return fetch(request);
  return request.headers.has('Range') ? rangeResponse(request.headers.get('Range'), cached) : cached;
}

/**
 * 206 Partial Content from a full cached copy (<audio> asks with Range).
 * One range per request; a header this does not understand gets the whole
 * file (200), which a server may always answer.
 */
async function rangeResponse(header, cached) {
  const match = /^bytes=(\d*)-(\d*)$/.exec(String(header).trim());
  if (!match || (match[1] === '' && match[2] === '')) return cached;

  const blob = await cached.blob();
  const size = blob.size;
  let start;
  let end;
  if (match[1] === '') {
    // Suffix range: the last n bytes
    start = Math.max(0, size - Number(match[2]));
    end = size - 1;
  } else {
    start = Number(match[1]);
    end = match[2] === '' ? size - 1 : Math.min(Number(match[2]), size - 1);
  }

  if (start >= size || start > end) {
    return new Response(null, { status: 416, statusText: 'Range Not Satisfiable', headers: { 'Content-Range': `bytes */${size}` } });
  }

  return new Response(blob.slice(start, end + 1), {
    status: 206,
    statusText: 'Partial Content',
    headers: {
      'Content-Type': cached.headers.get('Content-Type') || 'application/octet-stream',
      'Content-Length': String(end - start + 1),
      'Content-Range': `bytes ${start}-${end}/${size}`,
      'Accept-Ranges': 'bytes',
    },
  });
}

/*
 * Messages from core/package.js, answered on the MessageChannel port:
 *   { type: 'gelita:ping' }   → { type: 'gelita:pong', version }
 *   { type: 'gelita:prune' }  → { type: 'gelita:pruned', removed, deferred }
 */
self.addEventListener('message', (event) => {
  const type = event.data && event.data.type;
  const reply = (message) => event.ports[0]?.postMessage(message);

  if (type === 'gelita:ping') {
    reply({ type: 'gelita:pong', version: SW_VERSION });
  } else if (type === 'gelita:prune') {
    event.waitUntil(prune().then(reply, () => reply({ type: 'gelita:pruned', removed: 0, deferred: true })));
  }
});

/**
 * Remove package files the active package does not list (§5.6). Only when a
 * single window is open: another tab may still show pages of the previous
 * package. Deferred otherwise; core/package.js asks again on its next run.
 */
async function prune() {
  const windows = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
  if (windows.length > 1) return { type: 'gelita:pruned', removed: 0, deferred: true };

  const marker = await (await caches.open(META_CACHE)).match(ACTIVE_URL);
  const active = marker ? await marker.json() : null;
  if (!active || !active.manifest_url) return { type: 'gelita:pruned', removed: 0, deferred: false };

  const cache = await caches.open(ASSET_CACHE);
  const manifestUrl = new URL(active.manifest_url, SCOPE).href;
  const manifest = await cache.match(manifestUrl);
  if (!manifest) return { type: 'gelita:pruned', removed: 0, deferred: false };

  const keep = new Set([manifestUrl]);
  for (const file of (await manifest.json()).files || []) keep.add(new URL(file.url, SCOPE).href);

  let removed = 0;
  for (const request of await cache.keys()) {
    if (!keep.has(request.url) && await cache.delete(request)) removed += 1;
  }
  return { type: 'gelita:pruned', removed, deferred: false };
}
