/**
 * Asset package on this device: state, downloader, and the online-mode choice
 * (docs/rencana-paket-aset.md §5.5, §5.6, §6.2, §8.1–§8.7, P7, P11).
 * API and contract for the "Siapkan GELITA" and "Periksa paket" screens:
 * docs/06_JAVASCRIPT.md §Asset package — service worker and downloader.
 *
 * The download runs in the page, not in the service worker, so progress and
 * errors are easy to show; public/sw.js only serves what is cached here.
 *
 *   check()           where this device stands (server package, device
 *                     package, support, storage, online choice, canStart)
 *   download()        fetch the whole package: 4 files in parallel, sha256 of
 *                     every file before cache.put, resumes per file, quota
 *                     checks; switches to the new package only when every
 *                     file is in place (the previous one stays until then)
 *   verify()          re-hash every cached file of the active package
 *   clear()           remove the package from this device
 *   chooseOnline()    play without the package for this browser session (§8.7)
 *   packageInfo()     { package_code, package_mode } for core/device.js
 *
 * Caches (shared by every package version, content-addressed):
 *   gelita-aset   package files and manifests, keyed by their URL
 *   gelita-meta   gelita-meta/aktif.json: the package active on this device
 * localStorage `gelita.paket` mirrors aktif.json for the synchronous boot
 * script (core/package-boot.js); sessionStorage `gelita.paket.daring` holds
 * the online-mode choice, so it never outlives the browser session.
 *
 * Errors are PackageError with a `code` from ERRORS and a `detail` object;
 * the screens map codes to text (no user text here).
 */
import { CONFIG, url } from './config.js';
import { apiRequest } from './api.js';

export const ASSET_CACHE = 'gelita-aset';
export const META_CACHE = 'gelita-meta';
export const MARKER_PATH = 'gelita-meta/aktif.json';

/** Service worker generation that serves the package (public/sw.js SW_VERSION). */
const SW_VERSION = 2;
const MIRROR_KEY = 'gelita.paket';
const ONLINE_KEY = 'gelita.paket.daring';
const PRUNE_KEY = 'gelita.paket.prune';
const LOCK_NAME = 'gelita-paket';
const PARALLEL = 4;
const RETRY_DELAYS = [2000, 4000, 8000];
const QUOTA_FACTOR = 1.2;

// Same package URL pattern as public/sw.js (paths relative to the base URL)
const PACKAGE_PATH = /^(?:assets\/p\/[0-9a-f]{16}\.[a-z0-9]{1,5}|assets\/k\/[A-Za-z0-9._-]+\/(?:[A-Za-z0-9_-][A-Za-z0-9._-]*\/)*[A-Za-z0-9_-][A-Za-z0-9._-]*|paket\/pkt-[a-z0-9.-]+\.json)$/;

export const ERRORS = Object.freeze({
  UNSUPPORTED: 'unsupported', // no service worker, Cache Storage, or crypto.subtle
  DISABLED: 'disabled', // package mode is off on the server
  SERVER: 'server', // /api/paket/aktif did not answer
  MANIFEST: 'manifest', // manifest missing, unreadable, or sha256 differs from the server's
  QUOTA: 'quota', // not enough storage: { neededBytes, availableBytes, shortBytes }
  NETWORK: 'network', // a file failed after retries 2, 4, 8 s: { key, url, status }
  MISSING: 'missing', // the server has no such file (404/410): { key, url, status }
  CORRUPT: 'corrupt', // sha256 still wrong after one retry: { key, url }
  INCOMPLETE: 'incomplete', // files vanished from the cache before the switch: { keys }
  ABORTED: 'aborted', // the AbortSignal fired
});

/** Why a device may play without the package (§8.7). */
export const ONLINE_REASONS = Object.freeze({
  UNSUPPORTED: 'unsupported', // no service worker or Cache Storage (§8.6)
  STORAGE: 'storage', // private mode or not enough storage (§8.3, §8.4)
  LAB: 'lab', // lab profile wiped on logout; only when the admin allows it (§8.2)
});

export class PackageError extends Error {
  constructor(code, detail = {}) {
    super(`package: ${code}${detail.key ? ` (${detail.key})` : ''}`);
    this.name = 'PackageError';
    this.code = code;
    this.detail = detail;
  }
}

const sleep = (ms, signal) => new Promise((resolve, reject) => {
  const timer = setTimeout(resolve, ms);
  signal?.addEventListener('abort', () => {
    clearTimeout(timer);
    reject(new PackageError(ERRORS.ABORTED));
  }, { once: true });
});

const markerUrl = () => url(MARKER_PATH);

const storageGet = (store, key) => {
  try {
    return window[store].getItem(key);
  } catch {
    return null;
  }
};

const storageSet = (store, key, value) => {
  try {
    if (value === null) window[store].removeItem(key);
    else window[store].setItem(key, value);
  } catch {
    // Storage refused (private mode, full): the in-memory value still holds for this page
  }
};

let onlineMemory = null;
let quotaShort = false;

// ------------------------------------------------------------ support

/** What this browser can do; a package needs all of serviceWorker, caches, and crypto. */
export function support() {
  const serviceWorker = window.isSecureContext !== false && 'serviceWorker' in navigator;
  const cacheStorage = typeof window.caches?.open === 'function';
  const crypto = typeof window.crypto?.subtle?.digest === 'function';
  return { serviceWorker, cacheStorage, crypto, download: serviceWorker && cacheStorage && crypto };
}

/**
 * True when the package service worker is active for this page's scope.
 * Waits up to `timeoutMs` for an installing worker.
 */
export async function serviceWorkerReady(timeoutMs = 1500) {
  if (!support().serviceWorker) return false;
  const registration = await Promise.race([
    navigator.serviceWorker.ready,
    new Promise((resolve) => { setTimeout(() => resolve(null), timeoutMs); }),
  ]).catch(() => null);
  const worker = registration?.active;
  if (!worker) return false;

  return new Promise((resolve) => {
    const channel = new MessageChannel();
    const timer = setTimeout(() => resolve(false), timeoutMs);
    channel.port1.onmessage = (event) => {
      clearTimeout(timer);
      resolve(event.data?.type === 'gelita:pong' && Number(event.data.version) >= SW_VERSION);
    };
    worker.postMessage({ type: 'gelita:ping' }, [channel.port2]);
  });
}

// ------------------------------------------------------------ cache access

/**
 * The only cache write in the page: package URLs into gelita-aset, the marker
 * into gelita-meta. Anything else is refused (plan §9.1 item 1).
 */
async function putChecked(cacheName, href, body, type) {
  const path = href.startsWith(CONFIG.baseUrl) ? href.slice(CONFIG.baseUrl.length) : null;
  const allowed = (cacheName === ASSET_CACHE && path !== null && PACKAGE_PATH.test(path))
    || (cacheName === META_CACHE && href === markerUrl());
  if (!allowed) throw new PackageError(ERRORS.MANIFEST, { url: href });

  const size = typeof body === 'string' ? new Blob([body]).size : body.byteLength;
  try {
    const cache = await caches.open(cacheName);
    await cache.put(new Request(href), new Response(body, {
      headers: { 'Content-Type': type || 'application/octet-stream', 'Content-Length': String(size) },
    }));
  } catch (error) {
    if (isQuotaError(error)) {
      quotaShort = true;
      throw new PackageError(ERRORS.QUOTA, { neededBytes: size, availableBytes: null, shortBytes: null });
    }
    throw error;
  }
}

function isQuotaError(error) {
  return error?.name === 'QuotaExceededError' || error?.code === 22 || error?.name === 'NS_ERROR_DOM_QUOTA_REACHED';
}

/** aktif.json: the package active on this device, or null. */
export async function activePackage() {
  if (!support().cacheStorage) return null;
  try {
    // caches.match with cacheName does not create the cache on devices without a package
    const response = await caches.match(markerUrl(), { cacheName: META_CACHE });
    return response ? await response.json() : null;
  } catch {
    return null;
  }
}

function mirror(marker) {
  storageSet('localStorage', MIRROR_KEY, marker
    ? JSON.stringify({ package_code: marker.package_code, manifest_sha256: marker.manifest_sha256 })
    : null);
}

// ------------------------------------------------------------ server

/** GET /api/paket/aktif, normalised. `online_lab_allowed` is false unless the server says true. */
export async function serverPackage() {
  let data;
  try {
    data = await apiRequest('/paket/aktif', { retry: false });
  } catch (error) {
    throw new PackageError(ERRORS.SERVER, { status: error?.status ?? 0 });
  }
  return {
    package_code: typeof data?.package_code === 'string' && data.package_code !== '' ? data.package_code : null,
    manifest_url: data?.manifest_url ?? null,
    manifest_sha256: data?.manifest_sha256 ?? null,
    total_bytes: Number(data?.total_bytes) || 0,
    file_count: Number(data?.file_count) || 0,
    disabled_by_server: data?.disabled_by_server === true,
    online_lab_allowed: data?.online_lab_allowed === true,
  };
}

// ------------------------------------------------------------ online mode (§8.7)

/** Online-mode reason chosen in this browser session, or null. */
export function onlineChoice() {
  return storageGet('sessionStorage', ONLINE_KEY) || onlineMemory;
}

/**
 * Play without the package for this browser session. Allowed reasons:
 *   'unsupported'  the browser cannot hold a package;
 *   'storage'      a storage check or a download found too little space;
 *   'lab'          only when the server allows online mode on lab devices.
 * `state` is a check() result. Returns false when the reason is not allowed.
 */
export function chooseOnline(reason, state = null) {
  if (!allowedOnlineReasons(state).includes(reason)) return false;
  onlineMemory = reason;
  storageSet('sessionStorage', ONLINE_KEY, reason);
  return true;
}

export function clearOnlineChoice() {
  onlineMemory = null;
  storageSet('sessionStorage', ONLINE_KEY, null);
}

/** Online reasons a check() result allows (the screen offers the button only then). */
export function allowedOnlineReasons(state) {
  const reasons = [];
  if (!support().download) reasons.push(ONLINE_REASONS.UNSUPPORTED);
  if (quotaShort || state?.storage?.enough === false) reasons.push(ONLINE_REASONS.STORAGE);
  if (state?.server?.online_lab_allowed === true) reasons.push(ONLINE_REASONS.LAB);
  return reasons;
}

// ------------------------------------------------------------ state

/** navigator.storage.estimate() and persisted(), or nulls when unknown. */
export async function storageEstimate() {
  const result = { usage: null, quota: null, persisted: null };
  try {
    const estimate = await navigator.storage?.estimate?.();
    if (estimate) Object.assign(result, { usage: estimate.usage ?? null, quota: estimate.quota ?? null });
    result.persisted = (await navigator.storage?.persisted?.()) ?? null;
  } catch {
    // unknown
  }
  return result;
}

/**
 * Where this device stands. `status`:
 *   'off'          package mode is off: play as today (package_mode nonaktif)
 *   'ready'        the server's package is complete here and the worker serves it (lengkap)
 *   'online'       online mode was chosen for this session (daring)
 *   'unsupported'  no service worker / Cache Storage / crypto: play online, no download screen (daring)
 *   'download'     the package must be downloaded before Mulai (P11)
 * `canStart` is true for every status except 'download'.
 *
 * @param {{ server?: object }} [options] pass a serverPackage() result to skip the request
 */
export async function check({ server = null } = {}) {
  const caps = support();
  const srv = server ?? await serverPackage();
  const marker = caps.cacheStorage ? await activePackage() : null;
  mirror(marker);

  const complete = Boolean(srv.package_code && marker
    && marker.package_code === srv.package_code && marker.manifest_sha256 === srv.manifest_sha256);
  const swReady = caps.serviceWorker ? await serviceWorkerReady() : false;
  const online = onlineChoice();
  const estimate = await storageEstimate();
  const needed = complete ? 0 : Math.ceil(srv.total_bytes * QUOTA_FACTOR);
  const available = estimate.quota !== null && estimate.usage !== null ? estimate.quota - estimate.usage : null;

  let status;
  if (!srv.package_code) status = 'off';
  else if (complete && swReady && !online) status = 'ready';
  else if (online) status = 'online';
  else if (!caps.download) status = 'unsupported';
  else status = 'download';

  if (marker && storageGet('localStorage', PRUNE_KEY)) requestPrune();

  const state = {
    status,
    mode: { off: 'nonaktif', ready: 'lengkap' }[status] ?? 'daring',
    canStart: status !== 'download',
    server: srv,
    device: {
      package_code: marker?.package_code ?? null,
      manifest_sha256: marker?.manifest_sha256 ?? null,
      activated_at: marker?.activated_at ?? null,
      complete,
    },
    support: { ...caps, serviceWorkerReady: swReady },
    storage: { ...estimate, neededBytes: needed, availableBytes: available, enough: available === null ? null : available >= needed },
    online,
  };
  state.onlineReasons = allowedOnlineReasons(state);
  return state;
}

/**
 * { package_code, package_mode } of this page for core/device.js (§7.1).
 * The package code is the one the page's URLs come from (core/package-boot.js);
 * `lengkap` only when that package is active on this device and the package
 * service worker runs.
 */
export async function packageInfo() {
  const code = window.GELITA_PACKAGE_BOOT?.code || null;
  if (!code) return { package_code: null, package_mode: 'nonaktif' };
  if (onlineChoice()) return { package_code: code, package_mode: 'daring' };
  const marker = await activePackage();
  const lengkap = marker?.package_code === code && await serviceWorkerReady(300);
  return { package_code: code, package_mode: lengkap ? 'lengkap' : 'daring' };
}

/**
 * Called by game.js on pages rendered with a package: installs the
 * GELITA_PACKAGE hook and reconciles the boot mirror with Cache Storage
 * (a package the browser evicted switches the local-assets hook off).
 */
export async function initPackage({ setAssetsLocal } = {}) {
  window.GELITA_PACKAGE = packageInfo;
  const marker = await activePackage();
  mirror(marker);
  const code = window.GELITA_PACKAGE_BOOT?.code || null;
  if (window.GELITA_PACKAGE_BOOT?.local && marker?.package_code !== code) setAssetsLocal?.(false);
  if (marker && storageGet('localStorage', PRUNE_KEY)) requestPrune();
}

// ------------------------------------------------------------ download

async function sha256Hex(bytes) {
  const digest = await crypto.subtle.digest('SHA-256', bytes);
  return Array.from(new Uint8Array(digest), (b) => b.toString(16).padStart(2, '0')).join('');
}

/** One file's bytes; network errors and 5xx retried after each delay, 404/410 at once. */
async function fetchBytes(file, { signal, delays, reload }) {
  for (let attempt = 0; ; attempt++) {
    if (signal?.aborted) throw new PackageError(ERRORS.ABORTED);
    let status = 0;
    try {
      const response = await fetch(file.href, { cache: reload ? 'reload' : 'default', credentials: 'same-origin', signal });
      status = response.status;
      if (response.ok) return await response.arrayBuffer();
      if (status === 404 || status === 410) throw new PackageError(ERRORS.MISSING, { key: file.key, url: file.url, status });
    } catch (error) {
      if (error instanceof PackageError) throw error;
      if (error?.name === 'AbortError' || signal?.aborted) throw new PackageError(ERRORS.ABORTED);
      // Network error, or the body was cut off: retry below
    }
    if (attempt >= delays.length) throw new PackageError(ERRORS.NETWORK, { key: file.key, url: file.url, status });
    await sleep(delays[attempt], signal);
  }
}

/**
 * Bytes whose sha256 matches the manifest; one retry from the network, then
 * CORRUPT. Before the retry a cached copy is dropped, since the service
 * worker would otherwise answer the retry from it.
 */
async function fetchVerified(file, options) {
  for (let round = 0; round < 2; round++) {
    const bytes = await fetchBytes(file, { ...options, reload: round > 0 });
    if (await sha256Hex(bytes) === file.sha256) return bytes;
    await caches.open(ASSET_CACHE).then((cache) => cache.delete(file.href)).catch(() => false);
  }
  throw new PackageError(ERRORS.CORRUPT, { key: file.key, url: file.url });
}

async function loadManifest(server, options) {
  if (!server.manifest_url || !server.manifest_sha256) throw new PackageError(ERRORS.MANIFEST, { url: server.manifest_url });
  const file = { href: url(server.manifest_url), url: server.manifest_url, key: 'manifest', sha256: server.manifest_sha256 };
  let bytes;
  try {
    bytes = await fetchVerified(file, options);
  } catch (error) {
    if (error.code === ERRORS.CORRUPT || error.code === ERRORS.MISSING) throw new PackageError(ERRORS.MANIFEST, { url: server.manifest_url });
    throw error;
  }
  let manifest;
  try {
    manifest = JSON.parse(new TextDecoder().decode(bytes));
  } catch {
    manifest = null;
  }
  if (!manifest || manifest.package_code !== server.package_code || !Array.isArray(manifest.files)) {
    throw new PackageError(ERRORS.MANIFEST, { url: server.manifest_url });
  }
  return { manifest, file, bytes };
}

function withLock(task) {
  return navigator.locks?.request ? navigator.locks.request(LOCK_NAME, task) : task();
}

/**
 * Download the server's active package and make it the device's package.
 *
 * Options:
 *   onProgress(p)  after the manifest and after every file:
 *                  { phase: 'files'|'activate'|'done', totalBytes, doneBytes,
 *                    totalFiles, doneFiles, groups: [{ name, totalBytes, doneBytes, complete }] }
 *   signal         AbortSignal; aborting keeps every finished file
 *   server         a serverPackage() result (saves a request)
 *   activate       switch to the package when complete. Default: only while
 *                  no student session runs in this page (S8: a running
 *                  session never changes package; the files are kept and the
 *                  switch happens on the next call without a session)
 *   retryDelays    network retry delays in ms (default 2000, 4000, 8000)
 *
 * Resolves { package_code, activated, downloadedFiles, downloadedBytes,
 * skippedFiles, persisted, serviceWorkerReady }. Rejects with PackageError.
 */
export async function download({ onProgress = null, signal = null, server = null, activate = !CONFIG.sessionTag, retryDelays = RETRY_DELAYS } = {}) {
  if (!support().download) throw new PackageError(ERRORS.UNSUPPORTED);
  const srv = server ?? await serverPackage();
  if (!srv.package_code) throw new PackageError(ERRORS.DISABLED, { disabledByServer: srv.disabled_by_server });

  return withLock(() => runDownload(srv, { onProgress, signal, activate, delays: retryDelays }));
}

async function runDownload(server, { onProgress, signal, activate, delays }) {
  const options = { signal, delays };
  const cache = await caches.open(ASSET_CACHE);
  const have = new Set((await cache.keys()).map((request) => request.url));
  const { manifest, file: manifestFile, bytes: manifestBytes } = await loadManifest(server, options);

  const files = manifest.files.map((file) => ({ ...file, href: url(file.url) }));
  const missing = () => files.filter((file) => !have.has(file.href));
  const todo = missing();

  // Progress per group (§5.1 order as in the manifest)
  const groups = new Map(Object.keys(manifest.groups || {}).map((name) => [name, { name, totalBytes: 0, doneBytes: 0, files: 0, done: 0 }]));
  for (const file of files) {
    if (!groups.has(file.group)) groups.set(file.group, { name: file.group, totalBytes: 0, doneBytes: 0, files: 0, done: 0 });
    const group = groups.get(file.group);
    group.totalBytes += file.bytes;
    group.files += 1;
    if (have.has(file.href)) {
      group.doneBytes += file.bytes;
      group.done += 1;
    }
  }
  const report = (phase) => {
    if (!onProgress) return;
    const list = [...groups.values()].filter((group) => group.files > 0);
    onProgress({
      phase,
      totalBytes: list.reduce((sum, group) => sum + group.totalBytes, 0),
      doneBytes: list.reduce((sum, group) => sum + group.doneBytes, 0),
      totalFiles: files.length,
      doneFiles: list.reduce((sum, group) => sum + group.done, 0),
      groups: list.map(({ name, totalBytes, doneBytes, files: count, done }) => ({ name, totalBytes, doneBytes, complete: done === count })),
    });
  };

  // Storage check before anything is written (§8.4): missing bytes × 1.2
  const neededRaw = todo.reduce((sum, file) => sum + file.bytes, 0) + (have.has(manifestFile.href) ? 0 : manifestBytes.byteLength);
  const estimate = await storageEstimate();
  if (neededRaw > 0 && estimate.quota !== null && estimate.usage !== null) {
    const neededBytes = Math.ceil(neededRaw * QUOTA_FACTOR);
    const availableBytes = estimate.quota - estimate.usage;
    if (availableBytes < neededBytes) {
      quotaShort = true;
      throw new PackageError(ERRORS.QUOTA, { neededBytes, availableBytes, shortBytes: neededBytes - availableBytes });
    }
  }

  if (!have.has(manifestFile.href)) {
    await putChecked(ASSET_CACHE, manifestFile.href, manifestBytes, 'application/json');
    have.add(manifestFile.href);
  }
  report('files');

  let downloadedFiles = 0;
  let downloadedBytes = 0;

  const fetchAll = async (list) => {
    let next = 0;
    let failure = null;
    const worker = async () => {
      while (!failure && next < list.length) {
        const file = list[next++];
        try {
          const bytes = await fetchVerified(file, options);
          await putChecked(ASSET_CACHE, file.href, bytes, file.type);
          have.add(file.href);
          downloadedFiles += 1;
          downloadedBytes += file.bytes;
          const group = groups.get(file.group);
          group.doneBytes += file.bytes;
          group.done += 1;
          report('files');
        } catch (error) {
          failure ??= error;
        }
      }
    };
    await Promise.all(Array.from({ length: Math.min(PARALLEL, list.length) }, worker));
    if (failure) throw failure;
  };

  await fetchAll(todo);

  if (!activate) {
    report('done');
    return { package_code: server.package_code, activated: false, downloadedFiles, downloadedBytes, skippedFiles: files.length - todo.length, persisted: null, serviceWorkerReady: await serviceWorkerReady() };
  }

  // Atomic switch (§5.6): every manifest URL must be in the cache (no re-hash)
  report('activate');
  const present = new Set((await cache.keys()).map((request) => request.url));
  const gone = files.filter((file) => !present.has(file.href));
  if (gone.length) {
    gone.forEach((file) => have.delete(file.href));
    await fetchAll(gone);
    const again = new Set((await cache.keys()).map((request) => request.url));
    const lost = files.filter((file) => !again.has(file.href));
    if (lost.length) throw new PackageError(ERRORS.INCOMPLETE, { keys: lost.map((file) => file.key) });
  }

  const marker = {
    format: 1,
    package_code: server.package_code,
    manifest_sha256: server.manifest_sha256,
    manifest_url: server.manifest_url,
    groups: [...groups.keys()],
    file_count: files.length,
    total_bytes: files.reduce((sum, file) => sum + file.bytes, 0),
    activated_at: new Date().toISOString(),
  };
  await putChecked(META_CACHE, markerUrl(), JSON.stringify(marker), 'application/json');
  mirror(marker);
  quotaShort = false;

  storageSet('localStorage', PRUNE_KEY, '1');
  requestPrune();
  const persisted = await persist({ auto: true });
  report('done');

  return {
    package_code: server.package_code,
    activated: true,
    downloadedFiles,
    downloadedBytes,
    skippedFiles: files.length - todo.length,
    persisted,
    serviceWorkerReady: await serviceWorkerReady(),
  };
}

/**
 * Ask the browser to keep the package (navigator.storage.persist()). After a
 * download (`auto`) this is skipped in Firefox, which shows a dialog; the
 * teacher's "Simpan permanen" button calls it without `auto`.
 */
export async function persist({ auto = false } = {}) {
  if (auto && /Firefox\//.test(navigator.userAgent || '')) return null;
  try {
    return (await navigator.storage?.persist?.()) ?? null;
  } catch {
    return null;
  }
}

/** Ask the worker to delete files the active package no longer lists (§5.6). */
export async function requestPrune() {
  if (!support().serviceWorker) return null;
  const worker = navigator.serviceWorker.controller
    ?? (await navigator.serviceWorker.getRegistration().catch(() => null))?.active;
  if (!worker) return null;
  return new Promise((resolve) => {
    const channel = new MessageChannel();
    const timer = setTimeout(() => resolve(null), 10000);
    channel.port1.onmessage = (event) => {
      clearTimeout(timer);
      if (event.data?.deferred === false) storageSet('localStorage', PRUNE_KEY, null);
      resolve(event.data ?? null);
    };
    worker.postMessage({ type: 'gelita:prune' }, [channel.port2]);
  });
}

// ------------------------------------------------------------ teacher tools

/**
 * Full check of the active package (teacher's "Periksa paket", §5.5): every
 * cached file is hashed again. Missing or broken files are removed and the
 * package is no longer active, so the next download() fetches only those.
 * Resolves { package_code, files, missing: [keys], corrupt: [keys], ok }.
 */
export async function verify({ onProgress = null } = {}) {
  const marker = await activePackage();
  if (!marker) return { package_code: null, files: 0, missing: [], corrupt: [], ok: false };

  const cache = await caches.open(ASSET_CACHE);
  const manifestResponse = await cache.match(url(marker.manifest_url));
  const manifest = manifestResponse ? await manifestResponse.json().catch(() => null) : null;
  if (!manifest || !Array.isArray(manifest.files)) {
    await dropMarker();
    return { package_code: marker.package_code, files: 0, missing: ['manifest'], corrupt: [], ok: false };
  }

  const missing = [];
  const corrupt = [];
  let checked = 0;
  for (const file of manifest.files) {
    const href = url(file.url);
    const response = await cache.match(href);
    if (!response) {
      missing.push(file.key);
    } else if (await sha256Hex(await response.arrayBuffer()) !== file.sha256) {
      corrupt.push(file.key);
      await cache.delete(href);
    }
    checked += 1;
    onProgress?.({ doneFiles: checked, totalFiles: manifest.files.length });
  }

  const ok = missing.length === 0 && corrupt.length === 0;
  if (!ok) await dropMarker();
  return { package_code: marker.package_code, files: manifest.files.length, missing, corrupt, ok };
}

async function dropMarker() {
  try {
    await (await caches.open(META_CACHE)).delete(markerUrl());
  } catch {
    // nothing to drop
  }
  mirror(null);
}

/** "Hapus paket di perangkat ini" (§6.3): remove every package file and the marker. */
export async function clear() {
  mirror(null);
  storageSet('localStorage', PRUNE_KEY, null);
  if (!support().cacheStorage) return false;
  await caches.delete(META_CACHE);
  await caches.delete(ASSET_CACHE);
  return true;
}
