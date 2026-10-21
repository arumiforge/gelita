# 06_JAVASCRIPT.md — Perilaku Frontend

## Tujuan

Menjelaskan seluruh JavaScript GELITA di `public/assets/js/`: lapisan inti (API, antrean event, audio, modal, tirai), perilaku tiap layar permainan, kerangka tantangan beserta keempat engine (`puzzle`, `rumpang`, `boleh`, `cari`), dan perilaku panel admin. Isi dokumen ini mengikuti kode; bila berbeda, kode yang benar dan dokumen ini yang diperbaiki.

---

## Prinsip

**ES module tanpa build.** Semua berkas ditulis sebagai ES module modern, tanpa bundler, transpiler, atau build step. Layout memuat satu titik masuk dengan `<script type="module">` (`js/game.js` di `layouts/game.php`, `js/admin.js` di `layouts/admin.php`), lewat `asset_url_versioned()` sehingga `?v=` ikut berganti saat `gelita.assetVersion` dinaikkan. Modul khusus halaman dimuat dengan `import()` dinamis, jadi tiap halaman hanya memuat yang dipakainya. Tidak ada Vue, React, Alpine, htmx, atau SPA framework. Target: Chrome/Edge/Firefox/Safari dua tahun terakhir, termasuk Safari iPad.

**Progressive enhancement.** Halaman dirender server dan tetap dapat dibaca serta dipakai tanpa JavaScript: slide berpindah lewat jangkar `#prefix-n` + CSS `:target`, lentera dan konfirmasi Keluar memakai `<details>`, formulir dikirim sebagai POST biasa, pilihan Benar/Salah memakai radio bawaan. Begitu `game.js`/`admin.js` jalan, `<html>` diberi kelas `js`; aturan CSS yang bergantung pada JavaScript (mis. gambar Pustaka yang memudar masuk) hanya berlaku di bawah `html.js`. Pengecualian yang disengaja: arena tantangan butuh JavaScript, dan `_frame.php` menampilkan pesan `Game.needsJs` di `<noscript>`.

**Server sumber kebenaran.** Klien tidak pernah menghitung benar/salah, bintang, sisa kesempatan, status buka-kunci, atau jumlah serpihan. Payload soal tidak memuat kunci jawaban; tampilan hasil selalu dari respons server.

**Aksesibilitas.**

- Seluruh interaksi tantangan dapat diselesaikan dengan keyboard (panah pada papan Susun Gambar, Tab/Enter pada benda Cari Objek, radio pada Benar atau Salah).
- Modal memindahkan fokus ke tombol pertama, memerangkap fokus selama terbuka, dan mengembalikannya ke pemicu.
- Lapisan yang menutupi halaman (tirai, overlay Kenali, layar putar) membuat sisa halaman `inert`.
- Pengumuman singkat lewat `announce()` (wilayah `aria-live` bersama `#sr-announcer`); hasil tanda ✓/✗ ikut masuk `aria-label`.
- Teks yang diketik mesin ketik narasi tetap utuh untuk pembaca layar (`visually-hidden`).

**Gerak dikurangi.** `prefers-reduced-motion: reduce` dihormati di semua modul: konfeti tidak digambar, mesin ketik dan efek layar narasi mati, animasi tukar dan penyusunan keping Susun Gambar dilewati, `scrollToCenter()` menggulir tanpa animasi, tirai menutup tanpa memudar.

---

## Struktur Berkas

```text
public/
├── sw.js                    service worker: offline.html when the network is down + asset package cache-first (Stage 2)
├── sw-kosong.js             empty service worker, kill switch for sw.js
└── assets/js/
    ├── game.js              titik masuk halaman permainan
    ├── admin.js             titik masuk panel admin
    ├── core/
    │   ├── config.js        membaca #app-config: CONFIG, LOCALE, url(), t()
    │   ├── package-boot.js  classic early script: data-assets-local, window.GELITA_PACKAGE (asset package)
    │   ├── package.js       asset package on the device: check(), download(), verify(), clear(), chooseOnline()
    │   ├── api.js           apiRequest(), ApiError, handleApiError()
    │   ├── stage.js         panggung 16:9: initStage(), stageRect(), stageScale(), viewport_changed, layar penuh
    │   ├── events.js        antrean event + pengiriman batch: emit(), emitAudio(), flush(), resendQueued(), restoreQueued(), lastBatchAt(), page_hidden/visible
    │   ├── storage.js       antrean offline & preferensi di localStorage
    │   ├── audio.js         Sfx (Howler) + pemutar narasi (NarrationPlayer)
    │   ├── curtain.js       tirai pemuatan layar penuh: playCurtain(), initCurtains()
    │   ├── device.js        sinyal perangkat, overlay_opened, reading_progress
    │   ├── lightbox.js      perbesar gambar dalam <dialog>: createLightbox()
    │   ├── backdrop.js      per-slide background, crossfade or dip to black: createBackdrop([imgA, imgB], { fallback, veil }) → show(src, { always }), preload(src)
    │   ├── sprite.js        character pose frames: initSprites(), setFrames(), parseFrames()
    │   ├── modal.js         showModal(), confirmDialog(), closeModal(), isModalOpen()
    │   ├── toast.js         toast()
    │   ├── timer.js         jam tantangan: startTimer(), formatClock()
    │   ├── confetti.js      konfeti canvas
    │   └── dom.js           $, $$, on, el, esc, readJson, announce, scrollToCenter, debounce, …
    ├── game/
    │   ├── hud.js           tombol suara, lentera, heartbeat sesi
    │   ├── rotate-gate.js   aksesibilitas layar putar (potret)
    │   ├── install.js       service worker + tawaran pasang aplikasi (dialog di halaman awal)
    │   ├── register.js      pendaftaran tiga langkah
    │   ├── password-meter.js meter & syarat kata sandi
    │   ├── login.js         formulir masuk
    │   ├── slides.js        slide bersama (cerita, dialog, Pustaka)
    │   ├── narrator.js      pemutar narasi sinematik
    │   ├── screen-fx.js     WebGL screen effects (three.js): fog, sparks, light rays, bloom; createScreenFx()
    │   ├── cine.js          cinematic entrances of the start screens ([data-cine]): initCine()
    │   ├── parallax.js      parallax depth: acquireParallax(), releaseParallax(), parallaxValue()
    │   ├── intro.js         cerita pembuka & penutup
    │   ├── dialogue.js      dialog wilayah & wilayah tuntas
    │   ├── map.js           peta Kedu (narasi peta, overlay Kenali) & peta wilayah
    │   ├── library.js       buku Pustaka & rak Pustaka Kedu
    │   ├── reflection.js    Balai Refleksi
    │   ├── finished.js      layar selesai tantangan
    │   └── challenge.js     kerangka tantangan + pemilih engine
    ├── engines/
    │   ├── puzzle.js        Susun Gambar
    │   ├── rumpang.js       Isi Rumpang
    │   ├── boleh.js         Benar atau Salah
    │   └── cari.js          Cari Objek
    └── admin/
        ├── filters.js       filter bar tanpa muat ulang
        ├── tables.js        urut & saring tabel
        ├── tabs.js          tab di dalam satu halaman (editor tantangan)
        ├── content-editor.js editor soal terpandu + pengecoh rumpang
        ├── object-picker.js penempatan benda Cari Objek
        ├── bank-import.js   impor workbook bank soal
        ├── media-upload.js  unggah media + cek ukuran
        ├── narration-upload.js unggah banyak berkas narasi
        └── export.js        pantau status ekspor
```

Library pihak ketiga hanya `public/assets/vendor/howler.min.js` dan three.js (`public/assets/vendor/three-0.186.1/`, only for the screen effects) (lihat [Library JavaScript](#library-javascript)).

---

## Konfigurasi dan Teks dari Server

Data untuk JavaScript selalu dikirim sebagai `<script type="application/json">` yang di-encode `json_encode()` dengan flag `JSON_HEX_*`, tidak pernah diinterpolasi ke string JavaScript.

| Elemen | Dirender oleh | Isi | Dibaca |
|---|---|---|---|
| `#app-config` | `layouts/game.php` (`js_config($locale)`) dan `layouts/admin.php` (`js_config('id')`) | `locale`, `csrfName`, `csrfHash`, `apiBase`, `baseUrl`, `sessionTag`, `sequenceFloor`, `maxTries`, `stage`, `audioUrls`, `text` | `core/config.js` |
| `#challenge-data` | `game/challenge/_frame.php` (`ChallengeService::buildPayload()`) | payload pengerjaan tanpa kunci jawaban | `game/challenge.js` |
| `#password-policy` | `components/password-field.php` (`PasswordPolicy::toClient()`) | aturan sandi dari `Config\Gelita::$passwordPolicy` | `game/password-meter.js` |
| `#asset-sizes` | halaman Media admin | `sizes`: ukuran wajib per pola `asset_key` (`Config\Gelita::$assetSizes`); `types`: jenis berkas per pola slot (`ImageSlots::typeRules()`) | `admin/media-upload.js` |

`js_config()` (`app/Helpers/gelita_helper.php`) tidak memuat identitas siswa. Identitas dipegang session server (cookie HttpOnly CodeIgniter). `sessionTag` = `session_tag(game_session_id)`: HMAC 16 karakter atas id sesi permainan, buram tetapi stabil selama sesi itu, dan `null` sebelum siswa masuk. Penanda ini hanya dipakai antrean event offline (lihat [Penyimpanan Lokal](#penyimpanan-lokal)).

`core/config.js` membekukan `CONFIG` dengan kunci `locale`, `csrfName`, `csrfHash`, `apiBase`, `baseUrl`, `sessionTag`, dan `text`; bila `#app-config` tidak ada, nilainya diambil dari `<meta name="csrf-token">`/`csrf-name` dan `body[data-locale]`/`data-base`. Fungsi yang diekspor:

```js
CONFIG          // objek beku di atas
LOCALE          // = CONFIG.locale
url('masuk')    // → {baseUrl}masuk
t('blankEmptyText', 3)  // teks Js.* dengan {0}, {1}, … diganti; kunci tak dikenal dikembalikan apa adanya
                       // bentuk tunggal EN memakai kunci …One (blankEmptyTextOne, cardsEmptyTextOne, puzzleWrongTextOne)
```

**Teks antarmuka.** Semua teks yang ditulis JavaScript area game ada di `app/Language/id/Js.php` dan `app/Language/en/Js.php`. `js_config()` mengirim setiap kunci `Js.*` dalam bahasa aktif sebagai `CONFIG.text`, jadi tidak ada kamus kedua di JavaScript. Teks yang sudah dirender server ke atribut `data-*` (label tingkat sandi dari `Auth.*`, label kesempatan dari `Game.triesLabel` + `Game.triesOf`, pesan kunci peta dan Pustaka dari `Game.*`) dibaca langsung dari atributnya. Panel admin hanya berbahasa Indonesia, sehingga teks di `admin/*.js` ditulis langsung.

**`maxTries` dan `stage`** ikut dikirim `js_config()` tetapi tidak dibaca `core/config.js`; jumlah kesempatan yang dipakai layar tantangan diambil dari `#challenge-data` (`max_tries`).

### Two-language texts and asset package hooks (asset package Stage 4)

Since Stage 4 (`docs/rencana-paket-aset.md` §4.4), `js_config()` sends `text` for **every** game locale: `text.id` and `text.en`, each with every `Js.*` key (about 10 KB instead of 5 KB). `locale` is still the active language. `core/config.js` also accepts the old flat `text` map (one language) and treats it as the active locale.

```js
t('cancel')          // text of the active locale (see setLocale)
currentLocale()      // locale t() uses now; starts at CONFIG.locale
textLocales()        // ['id', 'en']
setLocale('en')      // switch t() with no request; sets <html lang>, fires
                     // `gelita:locale` { detail: { from, to } } on document;
                     // false when the page has no texts for that locale
assetsLocal()        // "assets are local" hook, false by default (below)
setAssetsLocal(true) // switch the hook on (Stage 2)
```

The language button (`components/lang-switch`, `game/hud.js`) still posts to `/bahasa` and the page reloads (plan option L1), now under a view transition. On submit, `hud.js` calls `setLocale()` first, so any toast or modal built during the reload is already in the new language, and the sound button title follows (`gelita:locale`). Tapping the active language sends nothing. During a challenge the button is hidden (`body.is-challenge .lang-switch`, owner decision P5) until Stage 7 can keep the engine state.

**"Assets are local" hook — contract for Stage 2.** `assetsLocal()` returns true when the page's images, audio, and modules come from the on-device asset package. Stage 4 never switches it on. Stage 2 (service worker package) switches it on in one of two ways:

1. `<html data-assets-local="1">` set **before** `game.js` runs (an inline script in `layouts/game.php`, e.g. from a value the service worker or the download page left in `sessionStorage`), or
2. `setAssetsLocal(true)` from a module once the service worker confirms the active package is complete. `setAssetsLocal(false)` switches it off again and wins over the attribute.

Readers call `assetsLocal()` at the moment they decide, so a curtain that already started keeps its minimum. Today the only reader is `core/curtain.js` (`curtainMinMs()`). Option 1 is preferred because the page's own curtain starts as soon as `game.js` loads. Stage 2 uses option 1 from the external classic script `core/package-boot.js` (CSP: no inline scripts), and `setAssetsLocal(false)` when the package turns out to be gone (next section).

### Asset package — service worker and downloader (Stage 2)

*(New section, English.)* Plan: `docs/rencana-paket-aset.md` §4.1, §5.5, §5.6, §6.2, §8, §9.1. With package mode **off** (the active release names no package, or the `.env` kill switch) nothing here changes what a student sees: pages use the normal URLs, `package-boot.js` reports `nonaktif`, and the service worker leaves every asset request to the browser.

**Pieces.**

| Piece | Role |
|---|---|
| `asset_url_versioned()` / `game_asset_url()` (PHP) | With a package: CSS/JS entries, Howler, `viewport.js`, and the two font preloads point at `assets/k/{code_version}/…` (only files the package lists and that exist on disk); otherwise the old `assets/…?v=` URLs. Media helpers switched in Stage 1. |
| `js_config()` `audioUrls` | `{ 'music/map': url, 'sfx/click': url, … }` from the package's `audio.music.*` / `audio.sfx.*` keys, empty when off; `core/audio.js` `soundUrl()` uses it. |
| `core/install-boot.js` | Classic blocking script in `<head>`, after `package-boot.js` and before the manifest link. Holds Chrome's `beforeinstallprompt` in `window.GELITA_INSTALL_PROMPT` for `game/install.js` (install dialog). |
| `core/package-boot.js` | Classic blocking script in `<head>`, before the stylesheets and `game.js`. Its tag carries `data-package="{code}"` (package mode on) and `data-sw="off"` (kill switch), from `asset_package_attrs()`. Sets `<html data-assets-local="1">` only when the page's package equals the device's active package (localStorage mirror), a service worker controls the page, and online mode was not chosen. Sets `window.GELITA_PACKAGE_BOOT` and a first `window.GELITA_PACKAGE`. |
| `game.js` | Imports `core/package.js` only on pages that name a package and calls `initPackage({ setAssetsLocal })`: `window.GELITA_PACKAGE` becomes the async `packageInfo()`, the mirror is reconciled with Cache Storage, and the local-assets hook is switched off when the package is gone. |
| `game/install.js` | Registers `sw.js`, or `sw-kosong.js` while `GELITA_PACKAGE_BOOT.killed`. |
| `public/sw.js` | Serves package URLs cache-first from `gelita-aset`; stores nothing itself except `offline.html`. |
| `core/package.js` | Downloader, device state, online-mode choice: the API the Stage 3 screens call. |
| `X-Gelita-Paket` | `App\Filters\PackageHeaderFilter`: the active package code on API responses handled by a controller; absent while package mode is off. No client reads it yet. |

**Service worker (`sw.js`, `SW_VERSION = 2`).**

- Package URLs (`assets/p/{sha16}.{ext}`, `assets/k/{version}/…`, `paket/pkt-*.json`): answered from `gelita-aset`; on a miss, fetched from the network unchanged (not stored). `Range` requests (`<audio>`) get a `206` built from the full cached copy (one range; suffix and open ranges; past the end `416`; a header it does not understand gets the whole file).
- Navigations: network with navigation preload, `offline.html` from `gelita-offline-v1` when the network is down; HTML is never cached.
- `/api*` and `/admin*`: no `respondWith()`. Everything else (normal assets, uploads, other origins) is left to the browser.
- One cache write function, `putChecked()`: only package URLs and `offline.html`; refuses non-200, redirected, `Set-Cookie`, and `Cache-Control: private/no-store` responses. `tests/unit/AssetPackageServiceWorkerTest.php` checks these rules statically.
- Messages on a `MessageChannel`: `{type:'gelita:ping'}` → `{type:'gelita:pong', version}`; `{type:'gelita:prune'}` deletes `gelita-aset` entries the active package does not list, only while a single window is open (`clients.matchAll`), else `deferred: true` and `core/package.js` asks again on its next `check()` or page.
- Updating `sw.js` keeps `gelita-aset`, `gelita-meta`, and `gelita-offline-v1`; other `gelita-*` caches are deleted on activation.
- `sw-kosong.js`: no fetch handler, deletes `gelita-meta` on activation (no device reports `lengkap` while it runs), keeps the package files. See `docs/08_DEPLOYMENT.md` §Asset package.

**Device state.** `gelita-meta/aktif.json` in cache `gelita-meta` is the package active on this device: `{ format, package_code, manifest_sha256, manifest_url, groups, file_count, total_bytes, activated_at }`. It is written last, after every manifest URL is in `gelita-aset` (atomic switch; the previous package stays in use until then). localStorage `gelita.paket` mirrors `package_code` + `manifest_sha256` for the synchronous boot script.

**`core/package.js` API (for the Stage 3 screens "Siapkan GELITA" and "Periksa paket").**

```js
import * as Package from './core/package.js';

const state = await Package.check();      // throws PackageError 'server' when /api/paket/aktif fails
// state.status   'off' | 'ready' | 'online' | 'unsupported' | 'download'
// state.canStart true unless status === 'download'   → gate Mulai on this (P11)
// state.mode     'nonaktif' | 'lengkap' | 'daring'   (what the session would record)
// state.server   { package_code, manifest_url, manifest_sha256, total_bytes, file_count,
//                  disabled_by_server, online_lab_allowed }   (missing online_lab_allowed → false)
// state.device   { package_code, manifest_sha256, activated_at, complete }
// state.support  { serviceWorker, cacheStorage, crypto, download, serviceWorkerReady }
// state.storage  { usage, quota, persisted, neededBytes, availableBytes, enough }   (estimate, × 1.2)
// state.online   online reason chosen in this browser session, or null
// state.onlineReasons  reasons the screen may offer for "Main daring di perangkat ini"

await Package.download({
  onProgress(p) {},   // { phase: 'files'|'activate'|'done', totalBytes, doneBytes, totalFiles, doneFiles,
                      //   groups: [{ name, totalBytes, doneBytes, complete }] }   (groups in §5.1 order)
  signal,             // AbortSignal; finished files are kept
  server: state.server, // optional, saves a request
  // activate: default true only while no student session runs in the page (S8)
});
// → { package_code, activated, downloadedFiles, downloadedBytes, skippedFiles, persisted, serviceWorkerReady }

Package.chooseOnline(reason, state); // 'unsupported' | 'storage' | 'lab' → true when allowed; sessionStorage only
Package.onlineChoice();              // reason or null;  Package.clearOnlineChoice()
await Package.verify({ onProgress }); // teacher: re-hash every file → { package_code, files, missing, corrupt, ok };
                                      // broken files are removed and the package is no longer active
await Package.clear();               // "Hapus paket di perangkat ini"
await Package.persist();             // "Simpan permanen" (Firefox asks; after a download it is skipped there)
await Package.storageEstimate();     // { usage, quota, persisted }
await Package.packageInfo();         // { package_code, package_mode } (also window.GELITA_PACKAGE)
```

Errors are `PackageError` with a `code` from `Package.ERRORS` and a `detail` object. The screen turns codes into text (`Js.*`); the module has no user text.

| `code` | Meaning | `detail` | Screen |
|---|---|---|---|
| `unsupported` | no service worker, Cache Storage, or `crypto.subtle` | — | play online, no download screen (§8.6) |
| `disabled` | package mode is off | `{ disabledByServer }` | play as today |
| `server` | `/api/paket/aktif` failed | `{ status }` | Coba lagi |
| `manifest` | manifest missing, unreadable, or its sha256 differs | `{ url }` | Coba lagi; tell the teacher |
| `quota` | not enough storage (before starting, or `QuotaExceededError` while writing) | `{ neededBytes, availableBytes, shortBytes }` (nulls after a write error) | "Ruang penyimpanan perangkat ini kurang ±{n} MB", Coba lagi, **Main daring di perangkat ini** (`chooseOnline('storage')`) |
| `network` | a file failed after retries at 2, 4, 8 s | `{ key, url, status }` | **Coba lagi** (finished files are kept) |
| `missing` | the server has no such file (404/410) | `{ key, url, status }` | tell the teacher |
| `corrupt` | sha256 still wrong after one retry | `{ key, url }` | "berkas rusak: {key}" for the teacher |
| `incomplete` | files vanished from the cache before the switch | `{ keys }` | Coba lagi |
| `aborted` | the signal fired | — | — |

**Rules the module enforces.**

- 4 files in parallel; sha256 of every file (`crypto.subtle`) before `cache.put`; one retry on a mismatch (a cached copy is dropped first), then `corrupt`.
- Resume per file: files already in `gelita-aset` (same URL) are skipped without re-hashing (S7). URLs are content-addressed, so a new package only downloads changed files (S9).
- Storage check before anything is written: missing bytes × 1.2 against `quota − usage`; `QuotaExceededError` while writing stops with the same `quota` error.
- One download at a time per browser (Web Locks, where available).
- No switch during a running session (S8): with `CONFIG.sessionTag` set, `download()` fetches the files but does not write `aktif.json`; the next call without a session switches at once.
- After a switch: `requestPrune()` and `navigator.storage.persist()` (not automatically in Firefox).
- Online mode (§8.7): `unsupported` whenever the browser cannot hold a package; `storage` after a check or download found too little space; `lab` only when the server sends `online_lab_allowed: true` (Stage 6). Kept in `sessionStorage` `gelita.paket.daring`, so it never outlives the browser session.

**`package_mode` at login and sign-up** (`window.GELITA_PACKAGE`, read by `core/device.js`): `nonaktif` when the page names no package; `daring` when online mode was chosen, the device does not hold the page's package, or no package service worker answers; `lengkap` otherwise. `package_code` is the page's package (the one whose URLs the session uses).

**Browser check:** `tools/qa/package-check.mjs` (usage in its header).

### Asset package screens: Mulai gate, Siapkan GELITA, Periksa paket (Stage 3)

*(New section, English.)* Plan §5.6, §8.1–§8.7, P7, P11. Views and routes in `docs/05_VIEW_UI.md` and `docs/04_CONTROLLER_ROUTE.md`.

| Module | Loaded by `game.js` when | Role |
|---|---|---|
| `game/package-gate.js` | the page has `[data-package-gate]`, names a package, and no game session runs | Decides whether Mulai may go on. Returns and records on `<body data-package-gate>`: `skip` (no package, session running, online mode chosen, or the browser cannot hold a package), `pass` (`check().canStart`), `hold`. `hold` on the welcome screen rewrites `[data-package-start]` to `/siapkan`; on `/mulai`, `/masuk`, `/daftar/{n}` it calls `location.replace('/siapkan?lanjut={next}')`. When the boot mirror says the device does not hold the page's package the decision is immediate (no request); otherwise `check()` confirms it, and a failing `/api/paket/aktif` lets the page be. |
| `game/prepare.js` | `data-screen="prepare"` | Runs `check()`; `canStart` → Mulai enabled (`ready`, or `online` text). Otherwise `download()` with per-part progress (painted once per animation frame), then `check()` again (waits up to 10 s for the worker; a worker that never answers gives `packageErrWorker`, and Coba lagi reloads). Errors through `package-ui.js`; `network`, `server`, `incomplete` retry by themselves after 10 s and on the `online` event, finished files kept. `quota` shows the private-mode text when the whole quota is below the package × 1.2, else the shortfall, and offers **Main daring di perangkat ini** (`chooseOnline('storage')`). The lab choice is shown while `onlineReasons` includes `lab`. Choosing online aborts the download (files kept). With a game session running nothing is downloaded (S8). |
| `game/package-inspect.js` | `data-screen="package-inspect"` | Shows `check()` + `activePackage()`; buttons call `verify()` (progress, missing/damaged keys, up to 8 named), `download()` (only what is missing), `persist()`, and `clear()` after `confirmDialog()`. |
| `game/package-ui.js` | (imported) | `formatMb()`, `formatCount()`, `formatDate()` (WIB, like `fmt_date()`), `quotaTooSmall()`, `errorText()` for every `PackageError` code, `retriesAlone()`. |

`core/package.js` and `public/sw.js` are unchanged in Stage 3.

**Browser check:** `tools/qa/package-screens.mjs` (usage in its header): welcome and `/masuk` gate, held on Siapkan GELITA until verified, progress per part, server stopped mid-download and resumed without re-downloading, Mulai focus and Enter, sign-up `lengkap`; Periksa paket verify/damaged file/repair/clear; private mode, storage too small, storage full mid-download, lab device (`daring`); package mode off (`nonaktif`); no service worker.

### Next-screen decode (`game/next-assets.js`, asset package Stage 4)

Screens that know where the student most likely goes next carry `data-next-assets` (JSON list of image URLs, from the PHP helper `next_assets_attr()`). `game.js` calls `initNextAssets({ ready: page })`: after the page module is ready and the browser is idle (`requestIdleCallback`, at most 2 s), each image is loaded with `new Image()` and `img.decode()` and held until `pagehide`, then released; a page restored from the bfcache decodes again. Limits (plan §4.5, iPad memory): one screen ahead only, at most 6 images, images only, nothing with Save-Data. A missing image is skipped quietly. `<html data-next-decoded="n">` records how many were decoded (for QA). `game/map.js` keeps its own `[data-preload]` warm-up of region backgrounds (no decode).

| Screen | `data-next-assets` |
|---|---|
| `intro` | `bg.loading`, `map.kedu`, `bg.map` (Peta Kedu behind the opening curtain) |
| `map-kedu` | entry assets of the first region still open or in progress (its region curtain preload) |
| `dialogue` | region map image |
| `map-level` | mission brief of the first open node: node background, Jaka |
| `mission-brief` | the node's scene image (Cari Objek, Susun Gambar), when there is one |
| `result` | region map image |
| `finished` | next region's entry assets when this attempt completed the region, else the region map |
| `region-done` | next region's entry assets, else `map.kedu` and `bg.map` |

---

## Lapisan Inti (`core/`)

### `core/api.js`

Satu pintu untuk semua permintaan JSON ke server. Bentuk respons server: `{ success, data }` atau `{ success: false, code, message, request_id }`.

```js
apiRequest(path, { method = 'GET', body = null, keepalive = false, retry = true })  // → json.data
class ApiError { code, message, status, payload, requestId, isNetwork }
handleApiError(error, { mapUrl, onNetwork, quiet })  // → Promise<kode galat yang ditangani>
```

- `path` relatif terhadap `CONFIG.apiBase` (`/events` → `{baseUrl}api/events`) atau URL lengkap.
- Header: `Accept: application/json`, `X-Requested-With: XMLHttpRequest`; selain GET juga `X-CSRF-TOKEN: CONFIG.csrfHash`. Cookie ikut (`credentials: 'same-origin'`).
- HTTP 429 dicoba ulang otomatis dengan jeda 2, 4, lalu 8 detik (kecuali `retry: false`).
- `fetch` gagal, status 5xx, atau respons bukan JSON → `ApiError('NETWORK')` dengan teks `errNetwork`. Galat lain tanpa kode → `INVALID_RESPONSE`.

Tindakan seragam `handleApiError()`:

| `code` | Tindakan UI |
|---|---|
| `INVALID_SESSION` | modal `sessionTitle`/`sessionExpired` tanpa tombol tutup → ke `/masuk` |
| `CSRF_EXPIRED` | modal `pageExpiredTitle` (masih login, token halaman kedaluwarsa) → muat ulang |
| `PASSWORD_CHANGE_REQUIRED` | langsung ke `/ganti-sandi` |
| `LEVEL_LOCKED` | toast, lalu ke `mapUrl` setelah 1,2 detik |
| `ATTEMPT_CLOSED` | muat ulang halaman |
| `RATE_LIMITED` | toast `errRateLimited` (setelah percobaan ulang otomatis habis) |
| `NETWORK` | `onNetwork()` bila diberikan, selain itu toast `errNetwork` |
| lainnya | toast berisi `message` server + `request_id` kecil |
| bukan `ApiError` | toast `errUnknown`; detailnya hanya ke konsol pengembang |

Modal sesi hanya muncul sekali walau beberapa permintaan gagal bersamaan. Anak tidak pernah melihat stack trace atau isi respons mentah.

### `core/events.js`

*(Rewritten in English for asynchronous logging Phase 1, `docs/rencana-log-asinkron.md` §5.1.)* Event queue with batched sending and idempotency. Two channels, one transport:

```js
emit(type, { levelId, nodeId, attemptId, itemId, payload })  // channel `events` → game_event_logs
emitAudio({ audio_asset_id, action, play_index, listened_ms, completed, attempt_id })
                                                             // channel `audio` → audio_usage_events
flush(force = false)   // send now; without force it returns the running send
resendQueued()         // offline queue of this session → send now (`online`, quiet retry)
restoreQueued()        // offline queue of this session → joins the first batch (page load)
lastBatchAt()          // performance.now() of the last accepted request (game/hud.js)
uuid(), now()          // client_event_id (crypto.randomUUID or a getRandomValues fallback) and ISO time
```

- Events are queued only when `CONFIG.sessionTag` exists; screens before login send nothing.
- **Rhythm (R1).** A batch leaves `FLUSH_MS` after the first waiting event, or at once when a channel holds `FLUSH_AT` events; at most 50 events per channel per request (`BATCH_MAX` = `Config\Gelita::$maxEventsPerBatch`). `FLUSH_MS`/`FLUSH_AT` come from `CONFIG.eventFlush = { intervalMs, maxEvents }` (`Gelita::$eventFlush` via `js_config()`), falling back to 30 000 ms / 40 events when the server does not send it.
- **One request for both channels (R2).** `POST /api/events` carries `{ events, audio?, client_sent_at, dropped?, dropped_id?, audio_dropped?, audio_dropped_id? }`; `events` may be empty when only audio waits. The answer `{ accepted, duplicate, rejected, audio?: { accepted, duplicate, rejected }, server_time }` has an `audio` key whenever the request carried audio. When it does not (a server without the combined request), the client sends that audio batch to `POST /api/audio-events` as before and keeps using that endpoint for the rest of the page, so an older server never loses audio.
- **Persist-on-push (R1).** Every event is written to the offline queue (`Storage.persistEvents`) when it is created and removed (`Storage.removeEvents`) only after the server accepted the batch that carried it. A browser crash between two batches therefore loses nothing: the next page sends the event again and the server answers `duplicate` for what had arrived. Every event id in memory (waiting or in flight) is tracked, so restoring the offline queue never queues an event twice. When the browser refuses to store an event at push time nothing is counted yet; it is stored (and a loss counted) only if its batch then fails.
- **Forced sends stay immediate.** Tab hidden (`visibilitychange`) and page closed (`pagehide`) call `flush(true)`: the in-flight batch and the waiting events go out by `navigator.sendBeacon()` (Blob `application/json`, fallback `fetch(…, { keepalive: true })`), **one beacon per channel to its own endpoint** (`/api/events`, `/api/audio-events`), so a server of any version keeps the audio. A beacon cannot set headers, so the CSRF token rides in the JSON body. Beaconed events stay in the offline queue, marked as beaconed, and are not beaconed twice by the same page. The curtain (`core/curtain.js`), the narrator's last slide (`game/narrator.js`), and closing the Kenali overlay (`game/map.js`) await or call `flush()`, so `dialogue_advanced` reaches the server by fetch before the next screen and the map's "Belum didengar" badge is right (F6).
- **Page load (R4).** `game.js` calls `restoreQueued()`: the session's offline queue (mostly copies of the previous page's beacon) joins this page's queue and leaves with its first batch, on the timer or at `FLUSH_AT`, instead of a request of its own. The `online` event still calls `resendQueued()`, which sends at once.
- **Failures.** A network error, 5xx, or `RATE_LIMITED` leaves the events in the offline queue for a re-send. The `savedOffline` toast ("Koneksi terputus …") shows once per page, only for a failure that holds: the browser reports offline, `RATE_LIMITED` persists after `apiRequest`'s retries, or one quiet re-send 5 s later (`RETRY_MS`) fails too. Leaving the screen cancels a batch still on its way (`net::ERR_ABORTED`), which looks like a network error; the page is gone before the re-send, so an online student does not see the toast. Other refusals (401, 403, 422) are dropped from memory and the offline queue because they would never be accepted.
- `client_event_id` is created once when the event is born and is stored with it; a re-send is answered `duplicate`. `sequence_no` continues across the pages of a session (`Storage.nextSequence`, never below `CONFIG.sequenceFloor`), and every request carries its own `client_sent_at`.
- Events the offline queue cannot keep (over `QUEUE_MAX`, or storage refused) are counted per session and channel; the `eventQueueFull` toast shows once, and the count goes to the server with the next fetch batch (`dropped`/`dropped_id` for events, `audio_dropped`/`audio_dropped_id` for audio; `dropped`/`dropped_id` on the `/api/audio-events` fallback), never with a beacon.

### `core/storage.js`

Lihat [Penyimpanan Lokal](#penyimpanan-lokal). Since asynchronous logging Phase 1 the event queue holds every event that is not acknowledged yet (persist-on-push), not only events whose batch failed.

### `core/audio.js`

Dua peran terpisah.

**`Sfx` — efek suara dan musik latar (Howler):**

```js
Sfx.unlock()       // dipanggil pada interaksi pengguna; juga membuka elemen narasi bersama
Sfx.isUnlocked()
Sfx.play(name)     // sfx/{name}.mp3: correct, wrong, click, shard, region-done, lock
Sfx.music(name)    // music/{name}.mp3, berulang: map, region, challenge; diingat bila suara masih terkunci
soundUrl(folder, name) // package URL from CONFIG.audioUrls (`music/map`, `sfx/click`) when a package is active,
                       // else assets/audio/{folder}/{name}.mp3
Sfx.stopMusic()    // juga melupakan musik yang diminta
Sfx.setVolume(v)   // 0–1, preferensi `volume`
Sfx.setEnabled(on) // preferensi `sound`; mematikan juga menghentikan musik dan narasi
Sfx.isEnabled()
```

Berkas di `public/assets/audio/sfx/` dan `public/assets/audio/music/`. Tidak ada yang berbunyi sebelum `unlock()`. Bila Howler atau berkasnya tidak ada, aplikasi tetap berjalan tanpa bunyi; tidak ada nada sintetis pengganti. `game.js` memanggil `Sfx.unlock()` pada `pointerdown`/`keydown` pertama. Layar memanggil `Sfx.music()` saat dimuat, sebelum ketukan pertama; musik yang diminta disimpan dan baru diputar oleh `unlock()`, atau oleh `setEnabled(true)` bila suara tadinya mati (Phase 0, `docs/arsip/rencana-perbaikan-tampilan.md`).

**Narasi — elemen `<audio>` per `.audio-player[data-src]` + telemetry:**

```js
initAudioPlayers(root, { attemptId, attention })  // pasang semua pemutar di root
narrationPlayer(node, { attemptId, shared, autoplay, onEnded, onError, onState })  // → NarrationPlayer | null
pauseNarration()                                  // hentikan narasi yang sedang berbunyi
```

`NarrationPlayer` membaca `data-src` dan `data-audio-id`, menangani tombol `[data-action="play"|"pause"|"replay"]` dan bilah `.audio-bar i`, dan mengirim telemetry lewat `emitAudio()`:

| Action | Kapan |
|---|---|
| `play` | pemain menekan putar (pemutaran baru) |
| `autoplay` | layar bernarasi memutar sendiri (`NarrationPlayer#autoplay()`) |
| `pause` | dijeda |
| `replay` | diputar ulang dari awal |
| `complete` | audio selesai sampai akhir (`onEnded` dipanggil sesudahnya) |

- Melanjutkan dari jeda tidak dikirim sebagai `play` baru; waktu dengarnya terbawa ke `pause`/`complete` berikutnya.
- `listened_ms` = akumulasi waktu benar-benar berbunyi (bukan `duration`). Server tetap memakai `duration_ms` aset sebagai pembagi.
- `play_index` yang disimpan dihitung server (`AudioUsageEventModel::nextPlayIndex()`); hitungan klien dimulai ulang setiap halaman.
- `attempt_id` ikut bila pemutar berada di layar tantangan; server menambah `challenge_attempts.audio_use_count` dan menolak attempt milik sesi lain.
- `autoplay()` mengembalikan `Promise<boolean>` dan menolak berbunyi sebelum `Sfx.unlock()`, saat suara dimatikan, tanpa berkas, atau setelah berkas gagal.
- Suara dimatikan → menekan putar membuka transkrip (`details.audio-transcript`). Berkas gagal dimuat → tombol disembunyikan, transkrip dibuka, pesan `audioNoSound` tampil.
- `attention: true` (layar `dialogue`, `region-done`, `intro`, `ending`, `mission-brief`) memberi `.is-attention` agar tombol putar terlihat.

**Elemen narasi bersama (`shared: true`).** Safari iOS/iPadOS hanya mengizinkan `play()` tanpa ketukan pada elemen yang pernah diputar di dalam ketukan. Layar bernarasi dan narasi petunjuk Cari Objek memakai satu elemen `<audio>` bersama: pemutar yang mengambilnya menjeda pemilik sebelumnya (telemetry `pause`-nya tetap terkirim), dan event elemen hanya diproses pemiliknya. `Sfx.unlock()` membuka elemen itu dengan memutar WAV hening 10 ms, jadi harus dipanggil di dalam handler ketukan (kartu ketuk, tirai, tombol Kenali).

### `core/curtain.js`

Tirai pemuatan layar penuh (`components/curtain.php`).

```js
playCurtain(kind, { urls, minMs, maxMs, tap, skip, keep, text, onProgress })
  // → Promise<{ loaded, failed, timedOut, skipped, cut }>
initCurtains()  // dipanggil game.js: pembersihan bfcache + delegasi a[data-curtain]
```

- Memakai `[data-curtain-layer="{kind}"]` yang sudah ada di halaman (tampil sejak paint pertama) atau `<template id="tpl-curtain-{kind}">`; tanpa keduanya langsung `{ skipped: true }`.
- `.is-live` membatalkan animasi pengaman CSS (tirai memudar sendiri bila modul gagal dimuat); bila pengaman sudah memudarkannya, hasilnya `skipped`.
- Progres nyata: tiap URL (`opts.urls`, bawaannya `data-preload`) dimuat lewat `Image()` untuk gambar atau `fetch` untuk selain gambar; `--p` dan `aria-valuenow` = selesai ÷ total, termasuk yang gagal.
- Selesai setelah semua aset termuat dan `minMs` lewat (bawaan 2,5 detik), atau paling lambat `maxMs` (bawaan 8 detik). Untuk tirai bawaan halaman waktunya dihitung sejak halaman dibuka. `data-min-ms`, `data-max-ms`, `data-tap`, `data-skip` pada lapisan menimpa nilai bawaan.
- Baris status bergilir tiap 1,8 detik dari `data-statuses`.
- `tap`: tombol `[data-curtain-tap]` tampil; ketukan di mana pun memanggil `Sfx.unlock()` lalu tirai memudar. `skip`: ketukan atau Enter/Spasi/Esc mengakhirinya lebih awal (`cut: true`). `keep`: tirai dibiarkan menutupi sebelum pindah halaman.
- Selama tirai tampil, `body > .hud`, `body > main`, dan `body > .nav-bar` diberi `inert`.
- Tautan `a[data-curtain="{kind}"]` (klik kiri tanpa tombol pengubah) memutar tirai jenis itu tanpa ketukan, lalu berpindah halaman. `data-curtain-text` (JSON slot → teks) mengisi `[data-curtain-text="slot"]` lewat `textContent` (slot kosong disembunyikan), `data-curtain-preload` mengganti daftar aset, dan `data-curtain-bg` mengisi `img.curtain-bg.is-deferred` tirai `region`/`challenge` lalu memudarkannya masuk sesudah `load` (`showBackground()`). Klik kedua selama tirai berjalan diabaikan. Sebelum pindah, antrean event di-`flush()` dan ditunggu paling lama 1,5 detik.
- `pageshow` dari bfcache membersihkan tirai yang masih aktif.

Jenis yang dipakai: `map` (tirai peta, dengan ketukan), `region` ("Menuju {wilayah}…", tanpa ketukan), `challenge` (sebelum layar tantangan, dapat dilewati).

**Short curtain (asset package Stage 4, owner decision P8).** `curtainMinMs(layer, local = assetsLocal())` picks the minimum: `data-local-min-ms` when assets are local, otherwise `data-min-ms` (default 2.5 s). `components/curtain` puts `data-local-min-ms="600"` on the between-screen curtains `region` and `challenge`. The `map` curtain that opens the game after the story has none and keeps 2.5 s. `opts.minMs` still wins over both.

### `core/stage.js` — panggung 16:9

```js
initStage({ track })   // --app-w/--app-h, tombol layar penuh, viewport_changed (track = ada sessionTag)
stageRect()            // kotak #stage di layar (CSS px)
stageScale()           // lebar panggung ÷ 1280
```

- Skrip sebaris di `<head>` `layouts/game.php` mengisi `--app-w`/`--app-h` dari `innerWidth`/`innerHeight` sebelum CSS dimuat (`load`, `resize`, `orientationchange`); `initStage()` melanjutkannya. CSS menghitung sisanya (`--stage-w`, `font-size` akar = lebar panggung ÷ 80), jadi JavaScript tidak pernah menghitung tata letak. `visualViewport` sengaja tidak dipakai: papan ketik layar dan cubit-zoom tidak boleh mengecilkan panggung.
- `viewport_changed` `{ w, h, orientation, rotate_gate, stage_w, stage_h, scale }` saat halaman dibuka dan setiap ukuran/orientasi berubah, termasuk saat layar putar tampil/hilang (debounce 1 detik, paling sering satu per 5 detik, tanpa kiriman bila nilainya sama). `stage_w`/`stage_h` dalam CSS px, `scale` = `stage_w / 1280` (3 desimal).
- No fullscreen button since 21 October 2026 (owner removed **Layar penuh** from the HUD menu; `initFullscreen` is gone).
- Posisi pointer (seret keping, ketuk benda) memakai `getBoundingClientRect()`, yang sudah menyertakan skala; tidak ada pembagian dengan skala secara manual.

### `core/device.js`

Sinyal perangkat dan tampilan layar untuk data penelitian. Tidak ada sidik jari peramban (canvas, font, audio).

```js
deviceSignals()          // → { screen_w, screen_h, viewport_w, viewport_h, pixel_ratio, touch_points,
                         //     orientation, is_standalone, language, timezone, ua_data }
attachDeviceField(form)  // isi input[name="device_json"] sesaat sebelum formulir terkirim
trackReading(section, refs)  // reading_progress 25/50/75/100 %
overlayOpened(overlay, { level, levelId })  // overlay_opened
```

- `attachDeviceField()` dipakai formulir masuk dan langkah 3 pendaftaran. Kiriman pertama ditahan paling lama 800 ms menunggu `navigator.userAgentData.getHighEntropyValues()`, lalu dikirim ulang lewat `requestSubmit()`. JSON lebih dari 4.000 karakter dikirim tanpa `ua_data`; bila gagal, formulir tetap terkirim tanpa sinyal (server memakai User-Agent saja). Harus dipasang sebelum penjaga kirim-ganda.
- `viewport_changed` dikirim `core/stage.js`; `page_hidden`/`page_visible` `{ screen }` di `core/events.js` `initVisibilityTracking()`, dengan kode layar dari `body[data-screen-code]` (resolver yang sama dengan `screen_viewed` di server; Pustaka terkunci → `library_locked`).
- `trackReading()` memantau panel bacaan bergulir di panggung: `.rumpang-reading`, daftar kalimat rumpang, dan kolom bacaan Benar atau Salah `.boleh-passages`; posisi gulir panel itulah yang dihitung (panggung tidak pernah bergulir). Tiap batas dikirim sekali per pengerjaan, dengan `passage_id` bila panel berisi satu bacaan. Panel yang tidak perlu digulir langsung bernilai 100 %.

### `core/sprite.js` — character pose frames

Plays the frames of a character pose. `components/character.php` renders frame 1 in `.character-img`; when the pose has 2 or more uploaded frames the figure also carries `data-frames` (JSON list of URLs), `data-fps`, and `data-loop` from `character_frames()` (`Config\Gelita::$characterAnimations`).

- `initSprites(root)` binds every `.character[data-frames]` once. `setFrames(figure, frames, { fps, loop })` replaces the frames and restarts from frame 1; fewer than 2 frames stops the player. `parseFrames(json)` reads the attribute safely.
- Only the `src` of the existing image changes, never the node, so entrance animations (`cine-rise`) do not replay and the mirrored right-hand figure stays mirrored.
- Frames are decoded while idle before the cycle starts; a frame that fails to load is skipped. The curtain still preloads frame 1 only.
- `loop` cycles; otherwise the pose plays once and holds its last frame. Since 11 October 2026 every pose loops (up to 24 frames, `Config\Gelita::$characterAnimations`), and `data-fps` may be below 1 when only a few interim frames are uploaded. It restarts from frame 1 each time the figure comes back into view.
- Paused while the tab is hidden (`visibilitychange`) and while the figure is not rendered (IntersectionObserver; hidden slides are `display: none`). With `reducedMotion()` only frame 1 shows.

### `core/lightbox.js`

`createLightbox({ closeLabel, loadingLabel, variant })` → `open(src, alt, caption)`. `<dialog>` bawaan (`showModal()`: Esc menutup, fokus terkunci) ditambahkan ke `#stage`; karena dialog modal berada di top layer, ukurannya ditulis dalam rem agar tetap di dalam panggung. Dipakai `game/library.js` (selebar panggung) dan `engines/boleh.js` (`variant: 'card'`, gambar pernyataan 640 × 480 dp).

### `core/modal.js`

```js
showModal({ type, icon, character, title, text, buttons, dismissible })  // → Promise<value tombol | null>
confirmDialog({ title, text, confirmLabel, cancelLabel, danger })          // → Promise<boolean>
closeModal(value), isModalOpen()
```

- Satu markup dari `<template id="tpl-modal">` (`components/modal.php`) di `#modal-layer`, yang berada di dalam `#stage` (modal ikut skala panggung); cadangan dibuat dengan `el()` bila template tidak ada.
- `type`: `correct`, `wrong`, `hint`, `confirm`, `info`. `icon` memilih `template[data-modal-icon]`. `text` boleh berupa larik: tiap unsur menjadi paragraf (`#modal-text`, `#modal-text-2`, …), dan `aria-describedby` modal menunjuk **semua** paragraf itu, sehingga pembaca layar juga membacakan "Kesempatanmu tinggal n lagi". Tombol dengan `href` dirender sebagai tautan.
- Fokus pindah ke tombol pertama, terperangkap di dalam modal (Tab/Shift+Tab), dan kembali ke pemicu saat ditutup. `Escape` dan klik latar hanya menutup modal `dismissible`; modal hasil pemeriksaan dibuat `dismissible: false` dengan tombol eksplisit.
- `body.has-modal` selama terbuka (pintasan keyboard slide berhenti). Semua teks lewat `textContent`.

### `core/toast.js`, `core/timer.js`, `core/confetti.js`, `core/dom.js`

- `toast(message, type, requestId)` — layer `#toast-layer` inside `#stage` (856, 72, 400 dp). `type`: `info`, `ok`, `bad`, `warn`; `request_id` shows small when given. Game behaviour (display plan Phase E, `body.game`):
  - One toast at a time, at most 3 waiting (the oldest waiting one is dropped). A message identical to the one showing restarts its timer; one identical to the last waiting one is skipped.
  - Duration follows text length: `toastDuration(message)` = 1.2 s + 60 ms per character, clamped to 1.8–5 s.
  - Tap the card (title `toastClose`), or press Esc while no modal is open, to close it (`dismissToast()`); the card fades upward and the next one shows. An Esc that a modal already handled (`defaultPrevented`) is ignored.
  - Toasts do not wait for modals. `bad` cards are `role=alert`, the others `role=status` polite; the layer itself drops its own role once JavaScript runs.
  - Badges are copied from `<template data-toast-icon>` in `components/toast.php`. Server flash toasts (`.is-flash`, `data-toast-type`) are removed from the DOM on load and join the queue first.
  - Admin panel (`body.admin`): unchanged, up to 3 stacked toasts, each removed after 4 s.
- `startTimer(el, elapsedMs)` → `{ stop(), elapsed() }`. Menampilkan `m:ss` (atau `h:mm:ss`) mulai dari `elapsed_ms` payload, yang dihitung server, sehingga benar setelah muat ulang dan kebal jam komputer yang salah. Tidak berdetak selama halaman tersembunyi, lalu menyusul waktu sebenarnya.
- `confetti(count)` — canvas selebar panggung (ditambahkan ke `#stage`, ukuran dari `stageRect()`), paling banyak 60 partikel, ±2,6 detik, warna dari token CSS; tidak digambar sama sekali bila `prefers-reduced-motion`.
- `dom.js` — `$`, `$$`, `on(target, type, selector?, handler)` (delegasi, mengembalikan pelepas), `el(tag, attrs, ...children)` (anak string lewat `textContent`), `esc()`, `readJson(id)`, `reducedMotion()`, `sleep()`, `scrollToCenter()` (menggulir panel `.stage-scroll` terdekat ke tengah, bukan `window`), `icon(name)` (salinan `template[data-icon]`), `announce(message)`, `debounce()`.

---

## Perilaku Halaman Game (`game/`)

### Touch (display plan Phase F)

- **`core/drag.js`.**
  - `initDrag(container, { selector, threshold = 8, canStart, onStart, onMove, onDrop, onCancel })`: pointer-event drag with `setPointerCapture`, primary pointer only, an 8 px threshold. A press below the threshold stays a tap (its click fires); the click after a real drag is swallowed. `onMove` gets `{ dx, dy, event, target }`, where `target` is the `selector` element under the pointer. Cancelled on a second finger, `pointercancel`, or `lostpointercapture`. Used by `engines/puzzle.js`: `.is-dragging` on the piece, `.piece-art` follows with an inline translate, `.is-drop-target` on the piece under the finger.
  - `onSwipe(element, { left, right, threshold = 50, ignore })`: touch/pen only. Ignored when the horizontal move is under 50 px or not larger than the vertical one, when a modal is open, or when the touch starts on a control, inside `ignore`, or inside an element that actually scrolls. `game/slides.js` (`swipe` option, on by default) turns a swipe into a click on the current slide's own Lanjut/Kembali link, so events (`dialogue_advanced`, `library_page_viewed`) and narration follow the buttons' path; it never follows the final link that leaves the screen. Story, dialogue, region-done, Kenali, and the map story go through `game/narrator.js`; Pustaka through `game/library.js`.
- **Cari Objek zoom** (`engines/cari.js`). The ⤢ button `#hunt-zoom` (48 dp, `Game.huntZoom` / `Game.huntZoomClose`) toggles `.hunt.is-zoomed`: the scene fills the stage (z 40, below modals and toasts), the clue becomes a ribbon on top. Opening sends `overlay_opened` `{ overlay: 'hunt_zoom', level }`. Esc closes it unless a modal, dialog, or lantern is open; it also closes itself when the attempt completes.
- **On-screen keyboard** (`core/keyboard.js`, loaded by `game.js` when `#stage` has a form field). While a text field has focus and `visualViewport` shows the keyboard covering it, `.stage-frame` gets `translateY(-n px)`, just enough to show the field with a 12 px margin and never pushing its top out of view; it goes back on blur, when the keyboard closes, and on `pageshow`.
- **`game.js`.** A passive `touchstart` listener so iOS applies `:active`; scene, map, and character images get `draggable=false` (plus a `dragstart` guard for images swapped in later); on iOS only, `maximum-scale=1` is added to the viewport meta so focusing a field does not zoom the page (pinch zoom stays on iOS; Android would lose it, so it does not get the flag).
- **Form fields.** `autocorrect="off"` on usernames and school fields, `enterkeyhint="next"`/`"done"` on login, sign-up, and change-password fields.

### `game.js` — titik masuk

Selalu aktif di setiap halaman layout game:

1. `<html>` diberi kelas `js`; `Sfx.unlock()` dipasang pada `pointerdown`/`keydown` pertama.
2. `initStage({ track: Boolean(sessionTag) })` (`core/stage.js`). With a `sessionTag`: `restoreQueued()` on load (the offline queue leaves with the page's first batch), `resendQueued()` on the `online` event (sends at once), then `initVisibilityTracking()`.
3. `initHud()`, `initRotateGate()`, `initInstall()`, `initCurtains()`.
4. `initAudioPlayers(document, { attemptId, attention })`.
5. Modul halaman dipilih dari `[data-screen]` (lalu `initNextAssets({ ready })`, see "Next-screen decode"):

| `data-screen` | Modul |
|---|---|
| `intro`, `ending` | `game/intro.js` → `initStory('intro' \| 'ending')` |
| `dialogue`, `region-done` | `game/dialogue.js` → `initDialogue()` |
| `map-kedu`, `map-level` | `game/map.js` → `initMap()` |
| `library` | `game/library.js` → `initLibrary()` |
| `library-index` | `game/library.js` → `initLibraryIndex()` |
| `reflection` | `game/reflection.js` → `initReflection()` |
| `challenge` | `game/challenge.js` → `bootChallenge()` |
| `finished` | `game/finished.js` → `initFinished()` |

Character pose frames: when the page has a `.character[data-frames]`, `game.js` also loads `core/sprite.js` and calls `initSprites()` (see `core/sprite.js`).

Tanpa `data-screen` yang cocok: `form.register-form` → `register.js`, `[data-login-form]` → `login.js`, `.password-field` (ganti sandi) → `password-meter.js`. Layar lain (`mission-brief`, `result`, `profile`, `library-locked`, halaman awal) cukup memakai lapisan inti. Setelah modul halaman terpasang, `body[data-page-ready="1"]` dipasang sebagai penanda untuk uji ujung ke ujung.

### `game/hud.js`

- Tombol suara `#btn-sound` → `Sfx.setEnabled()`; `aria-pressed` dan judul `soundOn`/`soundOff` ikut berubah.
- Lentera (`details.lantern`) tetap dapat dibuka tanpa JavaScript; di sini ditambah tutup dengan Esc dan klik di luar. `updateLantern(shards)` memperbarui angka dan bilah serpihan dari angka server (dipanggil `challenge.js` sebelum pindah ke layar selesai), tidak pernah dari hitungan sendiri.
- Heartbeat `POST /api/session/heartbeat`: a 60-second tick while the tab is visible, only inside the game (lantern and `sessionTag` present), without retries. When the server folds the heartbeat into every event batch (it sends `CONFIG.eventFlush`; `rencana-log-asinkron` R3), the tick is skipped if an event batch was accepted in the last 60 s (`lastBatchAt()`), so only an idle open tab sends its own heartbeat. Without `CONFIG.eventFlush` the heartbeat goes every 60 s as before.
- Language switch: on submit of `form.lang-switch`, `setLocale(code)` switches JS texts at once and marks the pressed button; the form then posts as before. The active language is a no-op (see "Two-language texts").

### `game/rotate-gate.js` dan `game/install.js`

- `initRotateGate()`: penghalang potret `.rotate-gate` tampil/hilang sepenuhnya lewat CSS dengan media query `(orientation: portrait) and (pointer: coarse)`. Modul ini membuat sisa isi `<body>` `inert` dan memindah fokus ke penghalang selama tampil, lalu mengembalikan fokus saat perangkat diputar mendatar.
- `initInstall()`: mendaftarkan `sw.js` di base URL (gagal didaftarkan tidak masalah). Di halaman awal (`[data-install]`), hanya pada perangkat sentuh yang belum menjalankan GELITA sebagai aplikasi terpasang: Chrome/Android menahan `beforeinstallprompt` dan menampilkan tombol `[data-install-button]`; Safari iOS/iPadOS menampilkan petunjuk `[data-install-ios]` (Bagikan → Tambah ke Layar Utama).
- *Install dialog (English, 18 October 2026, owner request).* `core/install-boot.js` is a classic blocking script in `<head>`, before the manifest link: it calls `preventDefault()` on `beforeinstallprompt` and keeps the event in `window.GELITA_INSTALL_PROMPT`, because Chrome can make its offer before the `game.js` module graph has loaded and a missed event meant no install button. The welcome page marks its box `data-install="modal"` and carries the dialog text as `data-install-*` attributes (`Game.installTitle`, `installText`, `installNow`, `installLater`, `installOk`, `installMenu`, `installIos`, `installAlready`). On a touch device that is not running GELITA as an installed app, `initInstall()` shows the **Pasang GELITA** button at once and opens `showModal({ icon: 'install', blur: true })` over the blurred stage: with the held offer it has *Nanti saja* / *Pasang sekarang* (`prompt()` runs inside the tap's user activation); without an offer after 2.5 s it explains the Chrome menu (⋮ → *Instal aplikasi* / *Tambahkan ke layar utama*) and says to open an already installed GELITA from its icon; on iPhone/iPad it shows Share → Add to Home Screen at once. It waits while the rotate screen is up and opens once per browser session (`sessionStorage` `gelita.install.later` after *Nanti saja*, *Mengerti*, Escape, or a tap outside); the button reopens it. On a computer the button only appears with Chrome's offer and the dialog never opens by itself. Other pages with `[data-install]` (Siapkan GELITA) keep the inline hint. `showModal()` gained the `blur` option (`.modal-layer.is-blur`) and the modal icon `install`.
- `public/sw.js`: offline page for navigations and the asset package, see [Asset package — service worker and downloader](#asset-package--service-worker-and-downloader-stage-2). With the server kill switch `initInstall()` registers `sw-kosong.js` instead.

### `game/register.js` — pendaftaran tiga langkah

Setiap langkah (`/daftar/1`, `/2`, `/3`) adalah formulir POST biasa; server menyimpan isian langkah 1–2 dan menjaga urutannya. Perilaku dipilih dari `form.register-form[data-step]`:

| Langkah | Perilaku |
|---|---|
| semua | `markInvalidFields()`: kolom wajib kosong diberi `.has-error` dan digulirkan ke tengah; `preventDoubleSubmit()`: tombol kirim dinonaktifkan setelah terkirim, aktif lagi saat kembali lewat bfcache |
| `2` | wilayah: kabupaten sudah dirender server sebagai `<optgroup data-province>`; JavaScript menyaring dengan melepas/memasang optgroup menurut provinsi (tanpa fetch). Negara selain Indonesia melepas `required` provinsi/kabupaten |
| `2` | sekolah: di provinsi berdirektori (`option[data-directory]`, Jawa Tengah) siswa mengetik NPSN; 8 angka → `GET /api/schools/lookup?npsn=…` (debounce 300 ms) → kartu `[data-school-card]` berisi nama resmi, dengan peringatan bila kabupaten/kota tidak cocok atau sekolah bukan SD/MI (`allowed: false`). NPSN tak dikenal → centang `school_manual` "Sekolahku tidak ada di daftar" muncul. Blok NPSN/nama ditukar CSS `:has()`; JavaScript hanya menyelaraskan `required` |
| `3` | nama pengguna: huruf kecil dan tanpa spasi saat mengetik, pola `^[a-z0-9._]{3,30}$` dicek di klien, lalu `GET /api/auth/username-available?u=…` (debounce 500 ms) → `{ available, reason }`, pesan dari `data-available`/`data-unavailable`/`data-format` |
| `3` | `initPasswordField()` dan `attachDeviceField()` |

Galat jaringan atau 429 pada pengecekan nama pengguna/NPSN → diam; server tetap memeriksa saat formulir dikirim. Tombol Daftar tidak dinonaktifkan walau sandi belum kuat: penolakan server adalah bagian dari pembelajaran dan tercatat sebagai `pw_weak_submit_count`.

### `game/password-meter.js`

Dipakai langkah 3 pendaftaran dan halaman ganti sandi; `bindToggle()` dan `bindCapsLock()` juga dipakai `login.js`.

```js
evaluate(password, username, policy)  // → { criteria, met, level, containsUsername }
initPasswordField(root)               // root = .password-field
bindToggle(button, input, { show, hide })
bindCapsLock(inputs, notice)
```

- Aturan dibaca dari `#password-policy` (`min_length`, `max_length`, `max_bytes`, `require_*`, `forbid_username`, `levels`); angka dan syarat tidak ditulis ulang di JavaScript. `evaluate()` meniru `PasswordPolicy::check()` hanya untuk tampilan.
- Tiap `.pw-rules [data-rule]` diberi `.is-met`; `.pw-meter i[data-level]` = `weak`/`medium`/`strong`; `.pw-level` berisi label dari `data-level-*`, atau `data-contains-username` bila sandi memuat nama pengguna (walau semua syarat terpenuhi). `.pw-match` membandingkan `#password_confirm`.
- Caps Lock (`getModifierState('CapsLock')`) menampilkan `.pw-caps`; tombol `.pw-toggle` mengganti `type` beserta `aria-pressed` dan `aria-label`.
- Tanpa `#password-policy` komponen diam dan daftar syarat tetap tampil statis.
- Isi sandi tidak pernah dikirim lewat AJAX, disimpan di browser storage, ditulis ke `console`, atau masuk antrean event.

### `game/login.js`

Lihat/sembunyikan sandi, peringatan Caps Lock, `attachDeviceField()`, dan `preventDoubleSubmit()` pada `[data-login-form]`. Formulir POST biasa; semua keputusan (gagal, terkunci, wajib ganti sandi) datang dari server.

### `game/slides.js`

```js
initSlides({ list, prefix, keyboard = true, hash = true, onChange })
  // → { show(n, { notify, focus }), current, total } | null
```

- Tanpa JavaScript slide berpindah lewat jangkar `#{prefix}{n}` + CSS `:target`. Dengan JavaScript hash yang sama diganti lewat `location.replace('#…')`: `:target` ikut berubah, tetapi riwayat browser tidak bertambah per slide.
- `.is-current` dipasang pada slide aktif; `list[data-managed]` = `1` (hash) atau `class`.
- Spasi, panah kanan, PageDown = lanjut; panah kiri, PageUp = kembali; di slide terakhir lanjut mengklik tautan akhir `.slide-nav a.btn-primary`. Pintasan diabaikan di dalam kontrol, saat modal terbuka, atau bila `keyboard: false`.
- `hash: false` untuk beberapa daftar slide dalam satu halaman (overlay Kenali): URL tidak disentuh.
- Berganti slide menghentikan narasi (`pauseNarration()`) dan memindah fokus ke judul/teks slide; `show(n, { focus: false })` dipakai perpindahan otomatis agar fokus pengguna tidak direbut.

### `game/screen-fx.js` and `game/parallax.js` — WebGL screen effects (owner, 19 October 2026)

- **Where.** A narrated scene root with `data-fx3d` (`screen_fx_attr()`: intro, ending, Kenali overlay, region dialogue, region done; not the Peta Kedu story bar). The value is the three.js entry URL (`asset_url_versioned(Config\Gelita::$screenFxThree)`, so the asset package serves it offline). `Config\Gelita::$screenFx = false` (`.env` `gelita.screenFx = false`) leaves the attribute out.
- **When.** `narrator.js` starts it the first time the root is on screen (`getClientRects()`), pauses it when the root leaves the screen (a closed Kenali overlay), and never starts it under `prefers-reduced-motion`. `screen-fx.js` is loaded with `import()`; it probes for WebGL 2 before it imports three.js (2.1 MB), so a device without WebGL 2 downloads nothing and logs nothing.
- **What it draws.** One transparent `<canvas class="screen-fx">` after `.narrator-fx`, under the characters and the caption: layered-noise fog, 110 lantern sparks (points), light rays, and a bloom (bright pass, two blur rounds at 1/4 size, soft tone curve). Everything is built from three.js core classes; the `examples/jsm` add-ons are not used because they need an import map (inline script, blocked by the CSP, iOS 16.4+).
- **Options (game/cine.js).** `light: { x, y }` (stage fractions) moves the rays' origin and halo onto that point and lets the rays spread all round, those falling below it faint; `quality` starts at a lower step. The story scenes pass neither and look as before. Effect `sun`: rays ease in over 4 s to a soft level.
- **Script effects.** `narrator.js` forwards each slide's effect (`setEffect()`): `fog` thickens the mist, `fog-lift` clears it, `glow` brings the rays and a warm halo, `flash` a burst of sparks, `dim` dims the sparks; anything else returns to a thin base mist with slow sparks. While the canvas runs the root has `.has-fx3d`, and `cinematic.css` blanks the CSS fog, fog-lift, and glow and the CSS light motes of the region dialogue. Flash, dim, and shake stay CSS.
- **Fallback.** No WebGL 2, a failed import, a shader that does not compile (`renderer.debug.onShaderError`), or a lost WebGL context remove the canvas and the class: the CSS effects play again. A watchdog steps down when frames stay slower than 45 ms for 4 s: canvas at 0.75× size, then 0.5× without bloom, then the CSS effects.
- **Parallax.** `parallax.js` keeps one smoothed value per axis (-1…1) and writes `--plx`/`--ply` on `#stage` with `.has-parallax`. Source: a mouse or pen; else device tilt where the browser sends it without a permission prompt (not iOS); else a slow sway. `cinematic.css` moves the backgrounds (`.scene-img`, `.kenal-bg`, `.cine-bg`, scaled 1.04) by 10/6 dp and the character layers (`.dialogue-stage`, `.cine-stage`) by 20/10 dp through the `translate` property, which composes with Ken Burns and the speaking zoom; the WebGL fog and sparks move as nearer layers. Users are counted (three Kenali overlays share the map), and the last release removes the variables.

### `game/cine.js` — cinematic entrances of the start screens (owner, 20 October 2026)

- **Where.** A root with `data-cine`: `welcome` (`/`), `gate` (`/gerbang`), `account` (`/mulai`, `/masuk`, `/daftar/1…3`). `game.js` imports the module first, before the page modules.
- **Cue sheets.** Each name has a list of cues `[selector, keyframes, start, duration, easing, stagger]`, played with `element.animate()` (Web Animations API, no library). Welcome: letterbox bars fold away, the background pushes in from dark and blur (2.8 s), a flare, the logo comes into focus, tagline, Mulai with a small overshoot, then HUD and the staff link. Gate: the picture pushes in, Jaka walks in, the crest turns into place, head, divider, the two cards slide in 160 ms apart. Account: the picture pushes in, Jaka rises, the panel and its crest. Cues animate only `opacity` and the individual `translate`/`scale`/`rotate` properties (compositor-only; no `filter`, since an animated blur or brightness re-renders the full-screen picture every frame) with `fill: 'backwards'`, so hover transforms, stage positions, and parallax are untouched once a cue ends. Easing is a gentle ease-out (`cubic-bezier(.25, .46, .45, .94)`); the welcome film runs about 4 s.
- **Start.** It waits until the background picture and the logo are decoded (at most 1.5 s), so the picture never pops in during its push-in. If the page is already 3.5 s old by then (a slow connection), it plays nothing: the CSS fallback is about to show the parts, and they must not be hidden again.
- **Once per session.** `sessionStorage` (`gelita.cine.seen`, per sheet and path): the first visit plays the full sheet; later visits (Back to the welcome page, a form shown again with an error) only fade the held parts in over 450 ms. Blocked storage plays the full sheet. A tap or a key during the entrance finishes it at once.
- **No flash, no trap.** `core/viewport.js` (blocking, in `<head>`) sets `html.cine-ok` when motion is allowed; `cinematic.css` then holds `.scene`, the HUD, and the page parts hidden until the root gets `.is-cine-live`, which the module adds in the same task as its first frames. If the module never runs, a CSS animation shows them after 4 s; a failed import adds `.is-cine-live` at once. Under `prefers-reduced-motion` nothing is held or played.
- **Afterwards.** On `welcome` (effect `sun`) and `gate` (the calm base: thin mist and sparks), when the root carries `data-fx3d`, it starts `game/screen-fx.js` one quality step down (`quality: 1`) and `acquireParallax()`, and pauses the effects while the tab is hidden. The modules (three.js, 2.1 MB) are downloaded while the entrance plays, after the pictures and only with WebGL 2; the canvas fades in over 2.5 s. On the welcome page `findLight()` reads the background picture on a 96 × 54 canvas and finds its light (the brightest warm patch of the upper 70 %, averaged over 5 × 5 samples so a single village lamp does not win): the rays and the halo come from there, whatever picture the admin uploaded; with no clear light (or a picture the canvas may not read) the rays keep their source above the top edge. Layers moved by the parallax have `will-change: translate`; the logo's light sweep is a `translate` of a band inside `.logo-sheen` (masked by the logo), so it runs on the compositor. CSS (motion allowed only) adds a slow 40 s drift of the background on both pages, and on the welcome logo a 6 s float, a breathing warm glow, and a light sweep every 7 s masked by the logo itself (`--logo-src`).
- **Why not Theatre.js.** Considered on the owner's suggestion. Its runtime adds a library download to the very first page for timelines that are a handful of cues here, and its visual editor (Studio) is AGPL and only useful while authoring. The browser's Web Animations API plays the same kind of timeline natively.

### `game/narrator.js` — pemutar narasi sinematik

Dipakai cerita pembuka dan penutup, narasi peta (`map_intro`), overlay Kenali wilayah (`region_intro`), dialog wilayah (`level_open`), dan wilayah tuntas (`level_done`).

```js
initNarrator(root, { onFinish, onSlide })
  // root = [data-narrator][data-context][data-prefix][data-mode][data-level-id?][data-keyboard="0"?][data-hash="0"?]
  // → { start({ userInitiated }), stop(), show(n, opts), next(), prev(), advance(), current, total, started } | null
```

| Mode (`data-mode`) | Mulai | Action audio pertama |
|---|---|---|
| `tap` | kartu "Ketuk untuk mulai" `[data-narrator-tap]`; ketukannya memanggil `Sfx.unlock()` | `autoplay` |
| `external` | pemanggil memanggil `start()` (peta setelah tirai, overlay Kenali) | `autoplay`, atau `play` dengan `userInitiated` |
| `manual` | tombol ⏸/▶ pada kontrol | `play` |

- **Mesin ketik** pada `.slide-text` (±35 karakter/detik). Teks utuh tetap ada untuk pembaca layar; sisa teks memakan tempat agar kotak tidak melompat. Ketukan pertama pada `[data-narrator-advance]` (atau Spasi/→/Enter) menuntaskan teks, ketukan berikutnya lanjut (`advance()`).
- **Maju otomatis** 1,2 detik setelah audio selesai bila sakelar Otomatis menyala (preferensi `narrationAuto`, bawaan aktif). Slide terakhir tidak berpindah halaman sendiri; root diberi `.is-finished`.
- **Kontrol** `[data-narrator-controls]`: `[data-narrator="prev"|"toggle"|"replay"|"next"|"auto"]`. ⏸/▶ dan ↻ disembunyikan pada slide tanpa audio yang dapat diputar; sakelar Otomatis disembunyikan bila tak satu slide pun beraudio. Slide tanpa audio tampil sebagai teks (`.is-text-only`) dan dilanjutkan manual.
- **Efek layar** dari `slide[data-effect]` (`fog`, `fog-lift`, `glow`, `flash`, `shake`, `dim`) → kelas `fx-{effect}` pada `[data-narrator-fx]`.
- **Event** `dialogue_advanced` `{ index, total, context, character }` (+ `levelId` dari `data-level-id`) setiap perpindahan slide; hanya narrator yang mengirimnya. Mencapai slide terakhir memanggil `flush()`.
- `onSlide(index, slide, total)` dipanggil sesudah event; `onFinish()` dipanggil saat pemain meminta lanjut di slide terakhir (tanpa `onFinish`, tautan akhir slide diklik).
- `stop()` mengembalikan pemutar ke keadaan belum mulai, jadi `start()` berikutnya memutar ulang.
- Audio setiap slide lewat `narrationPlayer(…, { shared: true })`; latar slide berikutnya (`img.cine-bg`) dipramuat.
- `prefers-reduced-motion`: tanpa mesin ketik dan tanpa efek (Ken Burns dimatikan CSS).

### `game/intro.js` dan `game/dialogue.js`

- `initStory('intro' | 'ending')` memasang narrator mode `tap` pada `[data-screen="intro"][data-narrator]` (`/intro`) atau `[data-screen="ending"][data-narrator]` (`/penutup`).
- Welcome after sign-up (owner, 19 October 2026): when the screen holds the server's `.welcome-card` (flash `welcome`), `initStory()` removes it and opens `showModal({ icon: 'key', blur: true })` with its title, message, and tip and one **Lanjut** button (`t('next')`), plus `confetti(40)`. The modal sits above the "Ketuk untuk mulai" card; closing it (Lanjut, Escape, or a tap outside) returns focus to that card, so the intro starts with the next tap as before. `components/modal` gained the `key` icon.
- **Slide backgrounds (owner, 19 October 2026).** A slide of `region_intro`, `level_open`, or `level_done` with `data-bg` (`slide_bg_attr()`, from `dialogues.background_media_id`) shows that picture; a slide without it shows the region background again. `narrator.js` takes the picture pair (the Kenali overlay's two `.kenal-bg`, or the layout's `#scene-a`/`#scene-b` when the root has `data-backdrop="stage"`) and hands it to `core/backdrop.js`, which crossfades (0.6 s), marks a slide's own picture `.is-own` (slow Ken Burns zoom on every screen; `.is-leaving` keeps the zoom while fading out), and preloads the next slide's picture. A `MutationObserver` on the root follows `.is-current` and the overlay's `.is-open`, so `show(n, { notify: false })` from `map.js` is covered and a closed overlay loads nothing. Without JavaScript the slides keep the region background.
- **Dip to black on the story screens (owner, 19 October 2026).** `/intro` and `/penutup` carry `data-backdrop="stage" data-backdrop-mode="dip" data-bg-default="…"` (the story background, `bg.intro` then `bg.welcome`), and the layout starts on slide 1's own picture. `narrator.js` passes `#stage > .scene` as the `veil`: on every slide change (`show(src, { always: true })`, only when the current slide really changed) the veil goes black in 0.35 s (`.scene.is-dark::before`), the pictures swap under it with no transition, it holds 0.1 s, and it lifts in 0.5 s. The next picture loads during the fade-out; one still loading after 2.5 s is skipped and the current picture comes back. A newer slide takes over at any step and compares with the picture actually on screen. With JavaScript the slides' own `.cine-bg` are hidden and the slides do not fade (`.cine.is-live`), so the caption and characters stay put and no other picture shows between two slides; under reduced motion the picture changes without black.
- `initDialogue()` memasang narrator pada `[data-screen="dialogue"]` (`/dialog/{code}`, context `level_open`) atau `[data-screen="region-done"]` (`/tuntas/{code}`, context `level_done`), markup `partials/dialogue-scene`. `onSlide` memberi tokoh yang berbicara `.is-speaking` dan yang mendengar `.is-listening`, lalu menukar gambarnya ke frame pose baris itu (`data-pose`, `data-pose-src`; monogram diganti `<img class="character-img">` begitu ada frame, baris tanpa frame membiarkan tokoh apa adanya). Frame pose dipramuat saat idle. A line whose pose has several frames carries `data-pose-frames`/`-fps`/`-loop`; `setPose()` hands them to `core/sprite.js` `setFrames()`, so the pose plays from frame 1 on every line.

### `game/map.js`

- Titik terkunci (`a[aria-disabled="true"]`) → bunyi `lock` + toast dari `data-locked-message`, bukan navigasi (server tetap menolak bila tautan dibuka langsung).
- Tombol Pustaka wilayah yang masih terkunci (`a[data-library-locked]` di nav-bar) → `showModal()` berikon gembok dari `data-locked-title`, `data-locked-message`, `data-locked-progress`, tombol `data-locked-ok`, dan tautan ke rak (`data-index-label`, `data-index-href`). Tanpa JavaScript tautannya membuka halaman Pustaka terkunci.
- Gambar `[data-preload]` dipramuat saat idle.
- **Peta wilayah** (`map-level`): `level_opened` `{ code }` + `levelId`, musik `region`.
- **Peta Kedu** (`map-kedu`): narasi `map_intro` lewat `initNarrator()` pada panduan peta. Bila halaman membawa tirai peta (`[data-curtain-layer="map"]`), `playCurtain('map')` → setelah ketukan, musik `map` lalu `start()`. Tanpa tirai, musik diminta biasa dan narasi menunggu ▶.
- **Kenali wilayah.** Setiap overlay `[data-kenal]` diberi narrator mode `external` (`data-hash="0"`, `data-keyboard="0"`). Klik `[data-kenal-open]` (pin dan kartu): `overlay_opened` `{ overlay: 'kenal', level }`, `Sfx.unlock()` di dalam ketukan, narasi peta dihentikan, overlay `.is-open`, `body.has-kenal`, skip-link/HUD/`<main>`/nav `inert`, kembali ke slide 1 tanpa event, `start({ userInitiated: true })`, fokus ke `.kenal-back`. Selama terbuka: Esc menutup, → atau Spasi `advance()`, ← `prev()`. Tutup (`[data-kenal-close]`, Esc, atau bfcache) → `stop()`, `flush()`, fokus kembali ke pembuka. Slide terakhir menghapus lencana `[data-kenal-badge]`; server menghitung status "sudah didengar" dari `dialogue_advanced` context `region_intro`. `onFinish` mengklik tombol akhir `[data-slide-final]` ("Masuk ke {wilayah}" memutar tirai `region`). Tanpa JavaScript overlay adalah target `:target`. Ketukan sebelum modul siap hanya sempat mengubah hash (`#kenal-{code}` atau `#kenal-{code}-{n}`); saat siap, hash itu membuka overlay di slide n **tanpa** memutar narasi (belum ada ketukan untuk membuka kunci audio), lalu hash dibersihkan lewat `history.replaceState`. Narasi peta tidak dimulai selama overlay Kenali terbuka. Lapisan yang dibuat `inert` dicari di `#stage` (`#stage > .hud`, `main`, `.nav-bar`, `.skip-link`).

### `game/library.js`

`initLibrary()` untuk buku `/pustaka/{code}` (`data-screen="library"`), `initLibraryIndex()` untuk rak `/pustaka`. Halaman terkunci tidak punya modul.

- Navigasi halaman lewat `initSlides({ prefix: 'page-' })`; setiap halaman yang dibuka (termasuk halaman pertama) mengirim `library_page_viewed` `{ page, total, page_id }` + `levelId`. `library_opened` dicatat server.
- Gambar/video yang gagal dimuat disembunyikan beserta `.book-figure`; halaman yang kehilangan semua medianya menjadi teks saja.
- Gambar di `.book-frame` memudar masuk (`.is-loaded`) dari skeleton; gambar `loading="lazy"` di halaman sesudahnya diubah `eager` saat berpindah.
- Panel kredit `details.media-credit`: membuka satu menutup yang lain; klik di luar, Esc, atau pindah halaman menutup semuanya.
- Perbesar gambar: `.book-zoom[data-zoom]` membuka lightbox `core/lightbox.js` (selebar panggung) dengan pemutar tunggu (`data-label-loading`) sampai gambar besar termuat; mengirim `overlay_opened` `{ overlay: 'lightbox', level }`.
- Video tertanam: facade `.book-embed[data-embed]` (poster + `.embed-play`) diganti iframe hanya setelah ditekan; pindah halaman mengembalikan facade dan menjeda `<video>`.
- Rak: sampul yang gagal dimuat disembunyikan sehingga gradien kartu tampil.

### `game/reflection.js` dan `game/finished.js`

- Balai Refleksi: bintang 1–5 memakai radio bawaan. Sebelum kirim, klien memeriksa jumlah textarea terisi minimal `data-min-answers` (bawaan 2); kurang → pesan `needTwo`, kolom kosong ditandai dan difokus. Server memeriksa ulang. `preventDoubleSubmit()` dipasang sesudahnya.
- Layar selesai (`/selesai/{attemptId}`): bunyi `region-done` bila wilayah tuntas (`.finished-kedu`), selain itu `shard`; konfeti canvas bila halaman tidak membawa konfeti CSS. Bintang dan angka sudah dirender server.

---

## Kerangka Tantangan dan Kontrak Engine

### Payload `#challenge-data`

Dibangun `ChallengeService::buildPayload()` dan ditanam `game/challenge/_frame.php`. Tidak memuat kunci jawaban, penanda pengecoh, atau pemetaan kata → rumpang.

| Kunci | Isi |
|---|---|
| `attempt_id`, `attempt_no` | pengerjaan yang sedang berjalan |
| `variant_group`, `variant` | variasi a/b/c yang terpilih acak; `variant.title` judul variasinya |
| `max_tries`, `tries_used` (= `check_count`) | batas kesempatan (salinan `challenge_attempts.max_tries`) dan cek yang sudah dipakai |
| `resumed` | `true` bila pengerjaan `in_progress` dilanjutkan |
| `elapsed_ms` | waktu sejak pengerjaan dibuka, dihitung server |
| `node` | `id`, `level_id`, `sequence`, `engine_type`, `variant_code`, `title`, `instruction`, `description`, `background`, `scene` |
| `items[]` | `toPlayerArray()` + `state`: `{ locked: true, answer, tries }` untuk soal yang sudah benar, selain itu `{ tries }` (jawaban salah tidak pernah dikembalikan) |
| `hints[]`, `hints_count` | `{ id, item_id, sequence }` tanpa teks |
| `word_bank` | `rumpang`: jawaban + pengecoh variasi itu, diacak server |
| `passages` | bacaan yang dirujuk soal (`boleh`) |
| `verdict_options` | `boleh`: `['benar', 'salah']` (tombolnya dirender server) |
| `objects[]`, `clues[]` | `cari`, menggantikan `items` (lihat [Cari Objek](#fitur-cari-objek-budaya-cari)) |

### `game/challenge.js`

`bootChallenge()` membaca `[data-screen="challenge"]` dan `#challenge-data`, memilih engine dari `node.engine_type` (`ENGINES`: `puzzle`, `rumpang`, `boleh`, `cari`, dimuat dinamis), lalu:

- memulai jam (`startTimer($('#timer'), elapsed_ms)`) dan musik `challenge`;
- mengisi penanda kesempatan `#tries` dari `max_tries − tries_used`;
- menampilkan tombol petunjuk `#btn-hint` bila `hints` tidak kosong;
- memasang konfirmasi Keluar, penjaga tombol Back, dan event `pagehide`;
- mengirim `challenge_opened` `{ resumed: true, attempt_no }` hanya bila `resumed` (pembukaan pertama dicatat server);
- memasang `trackReading()` untuk panel bacaan;
- memanggil `engine.mount(ctx)`.

### Kontrak engine (`ctx`)

Setiap engine mengekspor `mount(ctx)`. `ctx` berisi:

| Anggota | Kegunaan |
|---|---|
| `data`, `section`, `items` | payload, `<section>` tantangan, `data.items` (larik kosong untuk `cari`) |
| `attemptId`, `nodeId`, `levelId`, `levelCode`, `refs` | rujukan; `refs = { levelId, nodeId, attemptId }` untuk `emit()`; `levelCode` = kode wilayah (`data-level`) untuk payload `level` |
| `submit(itemId, answer, extra)` | `POST /api/attempts/{id}/responses` dengan `client_event_id` dan `occurred_at` (dipakai `cari`) |
| `check(answers)` | `POST /api/attempts/{id}/check` untuk semua isian sekaligus; menambahkan `first_input_ms`/`last_input_ms` per soal (dipakai `puzzle`, `rumpang`, `boleh`) |
| `selected(itemId, change)` | event `answer_selected` `{ …change, t_ms }` + mencatat waktu isian soal itu |
| `finish(result)` | ikuti penutupan dari server: tetap di layar tantangan (waktu berhenti, lentera diperbarui dari `result.shards`, tombol Cek/Petunjuk/Keluar disembunyikan) dan tampilkan tombol Lanjut `#btn-next`; Lanjut pindah ke `result.redirect`, tanpa `redirect` → cadangan `POST /complete` |
| `showCheckResult(result, opts)` | modal hasil cek engine batch (lihat di bawah) → `'complete'` atau `'fix'` |
| `setTries(node, left, label)` | perbarui penanda bulatan kesempatan (`.tries-dot.is-left`, `data-left`, `aria-label`) |
| `triesText(left)` | `triesLast` bila sisa 1, selain itu `triesLeft` |
| `maxTries` | dari `data.max_tries` |
| `tally({ correct, wrong, addCorrect, addWrong })` | angka ✓/✗ di bar (`#count-correct`, `#count-wrong`), selalu dari respons server |
| `setBusy(button, busy)` | nonaktifkan tombol selama request (`aria-busy`) |
| `networkPause(button)` | galat jaringan: toast `errNetworkRetry`, tombol istirahat 3 detik |
| `report(error, options)` | galat aturan (`TRIES_EXHAUSTED`, `ATTEMPT_CHANGED`, `CLUE_CLOSED`, `ATTEMPT_CLOSED`) lalu `handleApiError()` dengan `mapUrl` = peta wilayah |
| `currentItemId()` | ditimpa engine: soal yang sedang dikerjakan (untuk petunjuk soal) |
| `t` | `t()` dari `core/config.js` |

**Waktu isian per soal.** `selected()` mencatat milidetik sejak tantangan dibuka (`performance.now()`) saat siswa pertama kali dan terakhir kali mengisi/mengubah soal dalam satu percobaan. `check()` mengirimnya sebagai `first_input_ms`/`last_input_ms`, lalu mengosongkannya untuk percobaan berikutnya. Soal yang tidak disentuh dalam satu percobaan dikirim tanpa waktu. Ini hanya catatan proses; urutan dan waktu resmi tetap `occurred_at` server.

**Respons `/check`** (engine batch):

```json
{ "results": { "88": false }, "correct_count": 0, "total": 1, "all_correct": false,
  "check_count": 1, "try_no": 1, "tries_left": 2, "exhausted": false,
  "detail": { "88": { "pieces_correct": 6, "misplaced": [0, 4, 7] } },
  "completed": false, "completion_reason": null, "redirect": null, "shards": null }
```

**Respons `/responses`** (Cari Objek, satu ketukan):

```json
{ "correct": false, "first_pass": true, "wrong_click": true, "decoy": true, "feedback": "…",
  "try_no": 1, "tries_left": 2, "clue_closed": false, "change_count": 0, "wrong_click_count": 1,
  "progress": { "answered": 0, "total": 4 },
  "completed": false, "completion_reason": null, "redirect": null, "shards": null }
```

Bila pengerjaan tertutup di respons itu: `completed: true`, `completion_reason` = `solved` | `tries_exhausted`, `redirect` = `/selesai/{attemptId}`, `shards` = jumlah serpihan sesi.

**`showCheckResult(result, { okTitle, okText, partialTitle, partialText, exhaustedText, beforeExhausted })`:**

1. `tries_left` → `setTries(#tries)`.
2. `completed` + `solved` → bunyi `correct`, konfeti, modal sukses (tombol Lanjut, tidak dapat ditutup) → `finish()`.
3. `completed` + `tries_exhausted` → bunyi `wrong`, `beforeExhausted()` bila ada, modal `triesExhaustedTitle` + `exhaustedText` dengan satu tombol Lanjut → `finish()`. Kunci jawaban tidak ditampilkan.
4. Selain itu → modal "belum tepat" berisi `partialText` + `triesText(tries_left)` dengan tombol `fixAnswers` (Perbaiki) → `'fix'`.

**Galat aturan dari server** (mis. tab lain sudah memakai kesempatan terakhir) ditangani `ruleError()` di dalam `report()`: `TRIES_EXHAUSTED` dan `ATTEMPT_CHANGED` (409, tab lain atau kiriman ulang lebih dulu mengubah pengerjaan) → pindah ke `redirect` di payload galat, atau muat ulang; `CLUE_CLOSED` dan `ATTEMPT_CLOSED` (409) → muat ulang agar tampilan mengikuti payload. Muat ulang ini menyetel `state.leaving`, jadi tidak mengirim `challenge_abandoned`; begitu pula respons `/check` atau `/responses` yang menutup pengerjaan (`completed`), sehingga menutup tab saat modal hasil akhir masih tampil tidak tercatat sebagai meninggalkan tantangan. `/check` mengirim `check_no` (cek ke-berapa menurut layar ini, dari `tries_used` lalu `check_count` respons terakhir): cek dari tab lama yang tiba sesudah cek tab lain dibalas `ATTEMPT_CHANGED`, bukan dihitung sebagai cek baru. Selama `/check` berjalan, isian engine batch dibekukan, dan di Cari Objek tombol lompat petunjuk diabaikan selama ketukan menunggu server. `INCOMPLETE` (422) tidak terjadi dalam pemakaian biasa karena engine menolak kotak/kartu kosong sebelum mengirim.

**`POST /complete` hanya cadangan.** Dipanggil saat tombol Lanjut diketuk bila respons `completed` datang tanpa `redirect`. Endpoint ini idempoten (pengerjaan selesai dibalas hasil dan `redirect` yang sama) dan menolak pengerjaan yang masih punya kesempatan dengan `TRIES_REMAINING` (klien menampilkan toast `itemsPending`).

### Petunjuk

```text
Trigger   tombol #btn-hint pada bar tantangan (hanya tampil bila payload punya hints)
Pilihan   petunjuk soal yang sedang dikerjakan (ctx.currentItemId()) yang belum dibuka,
          lalu petunjuk node; masing-masing urut `sequence`
Process   overlay_opened { overlay: 'hint', level } → confirmDialog(hintTitle, hintPenalty)
Request   POST /api/attempts/{id}/hints { hint_id, item_id?, client_event_id }
Response  { text, hint_count }
UI        modal berisi teks; penghitung di tombol = hint_count dari server
          semua petunjuk sudah dibuka → modal hintAllOpened + teks yang sudah dibuka (tanpa request)
          tidak ada petunjuk lagi → modal hintNone
```

Teks yang sudah dibuka disimpan di halaman dan tidak diminta ulang, karena setiap permintaan dihitung server (`hint_count`, `item_responses.hint_used`, event `hint_opened`). Kalimat konfirmasinya netral, tanpa angka; petunjuk tidak mengurangi bintang.

### Keluar dari tantangan

```text
Trigger   summary Keluar (details.challenge-exit), tombol [data-exit-confirm], atau tombol Back browser
Process   overlay_opened { overlay: 'exit', level } → confirmDialog(exitTitle, exitText, danger)
Request   bila ya → POST /api/attempts/{id}/abandon { reason: 'user_exit' }  (gagal pun tetap keluar)
UI        flush(), lalu ke data-exit-url (/wilayah/{code})
```

- Tombol Back: satu entri penjaga di riwayat (`history.pushState`); `popstate` meminta konfirmasi yang sama dan memasang penjaga lagi bila dibatalkan.
- Halaman ditinggalkan tanpa Keluar/selesai (tutup tab, muat ulang) → `pagehide` mengirim `challenge_abandoned` `{ via: 'pagehide', attempt_open: true }` lalu `flush(true)` lewat beacon; pengerjaan tetap `in_progress` dan dapat dilanjutkan. Tidak ada `preventDefault()` pada `beforeunload`.
- Submit `form.lang-switch` (ganti bahasa) tidak dihitung meninggalkan.

---

## Engine Tantangan

Urutan tantangan di setiap wilayah: 1 Susun Gambar (`puzzle`), 2 Isi Rumpang (`rumpang`), 3 Benar atau Salah (`boleh`), 4 Cari Objek (`cari`). Ketiga engine pertama adalah engine **batch**: satu tombol "Cek jawabanku" (`#btn-check`) memeriksa semua isian sekaligus, dan setiap cek memakai satu dari 3 kesempatan. Cari Objek menilai per ketukan, dengan 3 kesempatan per petunjuk.

### Fitur: Susun Gambar (`puzzle`)

```text
Markup    game/challenge/puzzle.php: #puzzle-wrap[data-item] > #puzzle-board[data-grid][data-shape]
          > .piece[data-slot][data-piece][data-pos] berisi svg.piece-art; #puzzle-caption (hidden)
Trigger   ketuk dua keping untuk menukar, lalu Cek jawabanku
Input     answer: { order: [nomor keping per slot, urut slot] }
Process   klien hanya menukar posisi; tiap tukar → answer_selected { action: 'swap', from, to }
Request   POST /api/attempts/{id}/check { answers: [{ item_id, answer: { order } }] }
Response  detail[item] = { pieces_correct, misplaced }
UI        benar       → keping terkunci, papan .is-solved, keterangan gambar tampil, modal sukses
          salah       → slot di `misplaced` diberi .is-misplaced dan label "… — belum di tempatnya"
                        (pieceMisplaced) untuk pembaca layar; modal puzzleWrongText / puzzleWrongTextOne
                        ("masih ada N keping yang keliru") + sisa kesempatan; fokus ke keping keliru
          habis       → keping disusun ke posisi utuh (FLIP 600 ms), keterangan tampil,
                        lalu modal triesExhaustedPuzzle + teks keterangan
```

- Papan n × n keping SVG; ukuran dan bentuk mengikuti tingkat wilayah (`App\Libraries\DifficultyRules`): Temanggung 3 × 3 kotak, Magelang 4 × 4 kotak, Wonosobo 4 × 4 jigsaw. Urutan awal diacak server tanpa satu keping pun di tempatnya.
- Slot (tombol) tetap di tempat; yang berpindah adalah `.piece-art` beserta `data-piece`. Keping tidak bernomor; label pembaca layar menyebut letak slot, dan setiap tukar diumumkan (`piecesSwapped`).
- Ketuk-untuk-tukar wajib (paling andal di tablet). Dragging uses `core/drag.js` (pointer events, mouse and finger alike, display plan Phase F3.1) instead of HTML5 drag-and-drop: drag a piece onto another to swap them, through the same `swap()` and `answer_selected` event as tap-swap. Keyboard: panah berpindah antar keping (roving focus), Enter/Spasi memilih atau menukar, Esc membatalkan pilihan.
- Muat ulang sesudah cek yang belum utuh (`tries_used > 0`): hitungan ✓/✗ di kepala dipulihkan ✓ 0 / ✗ 1 (satu gambar), seperti engine lain memulihkannya dari `state`.
- Susunan benarnya memang publik (keping 0…n²−1 dari kiri atas), jadi server boleh mengirim slot yang keliru (`misplaced`) dan klien boleh menyusun gambar utuh setelah kesempatan habis.

### Fitur: Isi Rumpang (`rumpang`)

```text
Markup    game/challenge/rumpang.php: .rumpang > .blank[data-item] (tombol), #word-bank > .word
Trigger   ketuk kotak rumpang, lalu kata di bank; kemudian Cek jawabanku
Input     answers: [{ item_id, answer: { text } }] untuk semua rumpang
Process   klien mengelola kotak aktif dan kata terpakai;
          pasang kata → answer_selected { action: 'set', value, from? }, lepas → { action: 'clear', value }
Request   POST /api/attempts/{id}/check
UI        benar → .is-correct, kotak terkunci; salah → .is-wrong (label ikut "Belum tepat")
          semua benar → modal blankOkTitle; sebagian → blankWrongText "{0} dari {1}" + sisa kesempatan,
          fokus ke kotak merah pertama
          habis → semua kotak dan kata dikunci, tanda merah tetap, kunci tidak ditampilkan
Error     ada kotak kosong → modal blankEmptyText, request TIDAK dikirim (tidak memakai kesempatan)
```

- Bank kata dari `payload.word_bank` (jawaban variasi itu + pengecoh, diacak server); klien tidak tahu kata mana milik rumpang mana. Tiap chip punya id sendiri (`data-word-id`), sehingga dua kata yang sama tidak saling menandai "terpakai".
- Mengetuk kotak yang sudah terisi mengembalikan katanya ke bank dan menjadikan kotak itu aktif. Setelah mengisi, fokus pindah ke kotak kosong berikutnya.
- Tantangan bacaan (`as_reading`): semua soal disambung menjadi satu bacaan dengan rumpang bernomor (`App\Libraries\ClozeReading`), diberi judul variasi (`h2.rumpang-title`) bila berbeda dari judul node; panel `.rumpang-reading` dipantau `trackReading()`.
- Jawaban yang sudah benar tidak dihapus dan terkunci pada cek berikutnya. Muat ulang: rumpang dengan `items[].state.locked` diisi lagi dengan jawaban siswa sendiri (chip pertama yang belum terpakai dengan teks sama) dan terkunci.

### Fitur: Benar atau Salah (`boleh`)

```text
Markup    game/challenge/boleh.php: details.passage (bacaan), .verdict-card[data-item]
          dengan radio bawaan bernilai benar / salah
Trigger   pilih Benar atau Salah pada tiap pernyataan, lalu Cek jawabanku
Input     answers: [{ item_id, answer: { verdict: 'benar' | 'salah' } }]
Process   tiap pilihan → answer_selected { action: 'set', value }
Request   POST /api/attempts/{id}/check
UI        benar → .is-correct + tanda "✓ Benar", radio dikunci; salah → .is-wrong + "✗ Belum tepat"
          memilih ulang pada kartu salah melepas tandanya dan menggulir kartu salah berikutnya ke tengah
          sebagian → cardsWrongText + sisa kesempatan; habis → semua radio dikunci, tanda merah tetap
Error     ada kartu belum dipilih → .has-error 2,6 detik, digulirkan ke tengah,
          modal cardsEmptyText, request TIDAK dikirim
```

- Tombol verdict adalah radio yang dirender server, jadi dapat dipilih dengan keyboard atau sentuhan tanpa JavaScript; markup tidak memuat kunci.
- Bacaan dirender sekali di kolom kiri yang bergulir sendiri (`.boleh-passages`); tidak ada pelipatan bacaan, karena panggung berukuran tetap. Tautan "Berdasarkan …" (`a.card-passage`) membuka dan menggulir ke bacaannya di panelnya.
- Gambar pernyataan `button.card-image[data-zoom]` (192 × 144 dp) membuka lightbox 640 × 480 dp (`createLightbox({ variant: 'card' })`) dan mengirim `overlay_opened` `{ overlay: 'statement_image', level }` (kode wilayah dari `ctx.levelCode`).
- Petunjuk soal memakai kartu yang terakhir difokus. Muat ulang: pernyataan dengan `state.locked` dipulihkan dengan pilihan siswa sendiri, bertanda benar, dan terkunci.

### Fitur: Cari Objek Budaya (`cari`)

```text
Markup    game/challenge/cari.php: #hunt-scene > .object[data-object=ref] (posisi %),
          #clue-text, #clue-index, #clue-tries, #hunt-list .hunt-target, <template id="tpl-clue-voice">
Trigger   ketuk benda di adegan
Input     { item_id: <clues[aktif].item_id>, answer: { object: <ref benda yang diketuk> } }
Request   POST /api/attempts/{id}/responses (+ duration_ms sejak petunjuk tampil)
UI        benar    → benda .is-found dan dinonaktifkan, petunjuk dicoret dari daftar,
                     petunjuk terbuka berikutnya tampil dan dibacakan otomatis
          salah    → benda bergetar 700 ms, modal huntDecoyTitle/huntOtherTitle berisi `feedback`
                     server (atau teks bawaan) + sisa kesempatan petunjuk itu
          clue_closed → modal clueFailedTitle "Petunjuk ini dilewati"; petunjuk diberi ✗
                     (.is-skipped), bendanya TIDAK ditandai, lanjut ke petunjuk terbuka berikutnya
          completed → narasi dihentikan; solved: konfeti + huntDoneTitle,
                     tries_exhausted: triesExhaustedTitle (tanpa konfeti); lalu finish()
```

- **Payload tanpa pembeda jebakan.** `objects[]` memuat semua benda, target maupun jebakan, berbentuk identik `{ ref, x, y, w, media, found }` dan diurutkan menurut posisi di layar. `clues[]` hanya untuk target yang dinilai: `{ item_id, text, audio, audio_id, state: { found, closed, tries } }`, urutannya diacak dengan benih id pengerjaan (tetap saat dimuat ulang). `ref` adalah token HMAC per pengerjaan dari `encryption.key`, sehingga id petunjuk tidak dapat dicocokkan dengan benda mana pun di klien.
- Server hanya membaca `answer.object` dan menerjemahkannya sendiri; `item_id` mentah dari klien tidak pernah dipakai sebagai jawaban, dan token yang tidak dikenal ditolak `422 INVALID_RESPONSE`. Benda jebakan dirender sama persis dengan benda asli (tanpa kelas, atribut, atau urutan DOM pembeda); label tombolnya netral ("Objek 3"). `progress.total` tidak menghitung jebakan.
- **Kesempatan per petunjuk.** Setiap ketukan adalah satu percobaan untuk petunjuk yang sedang aktif. `#clue-tries` menampilkan sisa kesempatan petunjuk itu: saat dimuat dari `clues[i].state.tries`, lalu dari `try_no` respons. Ketukan salah ke-3 menutup petunjuk (`clue_closed`). Pengerjaan ditutup server begitu semua petunjuk tertutup; bila ketukan itu sekaligus menutup pengerjaan, hanya modal penutupan yang tampil.
- Daftar sisa (`#hunt-list`) berisi tombol `.hunt-jump` untuk melompat ke petunjuk yang masih terbuka; melompat tidak menghitung percobaan dan tidak memutar narasi. Keyboard: Tab berpindah antar benda, Enter memilih.
- **Narasi petunjuk.** `clues[i].audio` = URL rekaman petunjuk pada bahasa aktif (hanya `approved` dengan media aktif), `audio_id` = id `audio_assets`-nya; keduanya `null` tanpa rekaman. Tombol ▶/⏸ dari `#tpl-clue-voice` dipasang di samping teks, satu `NarrationPlayer` per petunjuk dengan `shared: true` dan `attemptId`. Setiap petunjuk hanya diputar lewat tombol (action `play`), tidak pernah otomatis, juga tidak sesudah jawaban benar (permintaan pemilik, 12 Oktober 2026). Berganti petunjuk menghentikan narasi sebelumnya. Rekaman yang gagal dimuat menyisakan tanda suara mati (`.clue-voice-off`) di tempat tombol; kalimat galatnya hanya untuk pembaca layar, jadi teks petunjuk tidak bergeser. Petunjuk tanpa rekaman tampil tanpa tombol.
- **Muat ulang**: `clues[i].state` dan `objects[].found` memulihkan temuan, petunjuk yang dilewati, dan sisa kesempatan; permainan dilanjutkan dari petunjuk terbuka pertama. Angka ✓ dimulai dari jumlah temuan, ✗ dari 0.

---

## Panel Admin (`admin/`)

Panel admin satu bahasa (Indonesia) dan semua halamannya tetap berfungsi tanpa JavaScript. Panel hanya menampilkan tabel dan hitungan mentah; tidak ada chart.

### `admin.js` — titik masuk

- Perilaku umum dipasang pada `document` dan dipasang ulang setiap kali `filters.js` mengganti isi halaman: `initTables()`, tombol salin `[data-copy="#selector"]` (mis. sandi sementara; tanpa izin papan klip, teksnya dipilih dan toast meminta Ctrl+C), `initConfirm()` (modal konfirmasi), dan `initTabs()` (`admin/tabs.js`).
- `a[data-zoom]` (pratinjau di Gambar & mockup, halaman detail slot, `components/media-field`) dibuka di lightbox bersama `core/lightbox.js` (delegasi pada `document`, jadi baris yang diganti `filters.js` ikut); klik dengan Ctrl/Shift/tombol tengah tetap membuka tab baru, dan tanpa JavaScript tautannya membuka gambar di tab baru.
- Modul khusus halaman dimuat hanya bila markup-nya ada:

| Penanda markup | Modul |
|---|---|
| `form.item-form`, `[name="engine_type"]` | `admin/content-editor.js` → `initContentEditor()` |
| `form[action$="impor-bank/pratinjau"]` | `admin/bank-import.js` → `initBankImport()` |
| `form.upload-box[action$="media/unggah"]` | `admin/media-upload.js` → `initMediaUpload()` |
| `form[data-narration-upload]` | `admin/narration-upload.js` → `initNarrationUpload()` |
| `.media-sprite .character[data-frames]` | `core/sprite.js` → `initSprites()`; tombol `[data-sprite-replay]` memutar ulang pose sekali-putar |
| `[data-export-id]` | `admin/export.js` → `initExport()` |

### `admin/filters.js`

- Berlaku untuk `form.filter-bar` (GET) yang menjadi anak langsung `#admin-main`. Perubahan filter memperbarui URL lewat `history.replaceState` (tetap dapat dibagikan), mengambil HTML halaman untuk filter baru, lalu mengganti semua saudara form filter di `#admin-main`; form-nya sendiri tetap sehingga fokus keyboard tidak hilang. Tombol "Atur ulang" dan chip filter aktif (`.filter-chips`) ikut versi server; chip adalah tautan biasa yang melepas satu filter.
- Kolom teks/pencarian diterapkan setelah jeda 450 ms; pilihan dan tanggal langsung. Permintaan sebelumnya dibatalkan (`AbortController`).
- Gagal memuat → toast, lalu navigasi biasa ke URL filter. Tanpa JavaScript tombol Terapkan mengirim form seperti biasa.

### `admin/tables.js`

- Tabel server (`components/data-table`, `data-sortable="server"`): urut, halaman, dan CSV sudah berupa tautan biasa. Modul ini hanya menerapkan pilihan "Baris per halaman" langsung (`select[data-auto-submit]`), menambah bayangan kolom pertama lengket saat tabel digulir ke samping (`.table-wrap.is-scrolled`), dan menyembunyikan baris rincian `tr.row-expand` (mis. riwayat percobaan) di balik tombol `.expand-toggle` (`aria-expanded`, `aria-controls`).
- Tabel kecil (`components/admin-table`, `data-sortable="client"`, ≤ 500 baris): judul kolom menjadi tombol urut dengan `aria-sort`. Nilai dibaca dalam format Indonesia (`1.234,5`, `85,0%`, `2:05`, `23 Sep 2026 10:14`, satuan seperti "tahun", "kali", "siswa"); `data-sort` pada sel menimpa teksnya; sel kosong "—" selalu di akhir. Tabel ≥ 10 baris mendapat kotak "Saring baris di tabel ini…".

### `admin/tabs.js` dan modal konfirmasi

- `[data-tabs]` berisi tautan `#tab-{kunci}`; isinya elemen `[data-tab-panel~="{kunci}"]` di mana pun di halaman, sehingga satu form boleh terbagi ke beberapa tab (field tersembunyi tetap terkirim). Tab dipilih tersimpan di hash; hash `#item-…` (kembali sesudah menyimpan soal) membuka tab pemuatnya. Tautan `a[data-open-item]` membuka tab dan kartu soalnya. Tanpa JavaScript semua bagian tampil berurutan.
- `admin.js` → `initConfirm()`: `form[data-confirm]` menahan submit lalu membuka `core/modal.js` (judul `data-confirm`, teks `data-confirm-text`, tombol `data-confirm-ok`, gaya `data-confirm-style`); "Batal" tidak mengirim apa pun. Tanpa JavaScript form langsung terkirim dan server tetap memvalidasi.

### `admin/content-editor.js` dan `admin/object-picker.js`

```text
Feature   Form soal yang menyesuaikan interaction_type
Trigger   form.item-form; ganti interaction_type, atau edit kotak format teknis
Process   blok terpandu per jenis soal:
            fill_blank_bank → jawaban Indonesia/Inggris (text_id, text_en)
            verdict_card    → kunci Benar / Salah (verdict)
            find_object     → pemilih posisi (object-picker.js), centang benda jebakan
                              (decoy, target), penjelasan saat diklik ID/EN (wrong_feedback_id/en)
            puzzle_arrange  → keterangan ukuran papan dari data-puzzle-grid / data-puzzle-shape
                              (ditetapkan server dari tingkat wilayah); kunci `order` 0…n²−1 dibuat otomatis
Validasi  kalimat rumpang memuat ___ (Inggris boleh kosong), jawaban Indonesia wajib,
          kunci verdict terisi, benda punya posisi, benda jebakan punya penjelasan Indonesia,
          kunci susun gambar ada; format teknis yang tidak terbaca ditolak
UI        pratinjau "Pratinjau untuk siswa"; galat dikumpulkan di atas form, submit ditahan
```

- Editor terpandu hanya antarmuka: yang dikirim tetap `answer_key_json` dan `config_json`, jadi server memvalidasi persis seperti tanpa JavaScript. Kunci JSON yang tidak dikelola editor dipertahankan; format teknis dapat dibuka lewat tombol "Lihat format teknis". JSON yang tidak terbaca membuka kotak format teknis dan menyembunyikan editor terpandu sampai diperbaiki.
- Teks untuk guru memakai bahasa sehari-hari: "soal", "benda", "format teknis".
- Form tantangan (`[name="engine_type"]`): setiap `fieldset.variant-row` Isi Rumpang mendapat tombol "+ Tambah kata pengecoh" dan ✕ per baris (pasangan ID/EN `…[distractors][n][id|en]`; dua baris terakhir hanya dikosongkan).
- `createObjectPicker({ scene, value, onChange })` menampilkan gambar adegan (`form[data-scene]`, atau bidang kosong 16:9 bila belum ada). Ketuk menaruh benda berpusat di titik ketuk; penanda dapat digeser (pointer) atau dipindah dengan panah (0,5 %, Shift 2 %); lebar diatur penggeser 3–40 %. Koordinat disimpan dalam persen (`x`, `y` sudut kiri atas, `w` lebar; benda persegi), jadi tetap benar bila gambar adegan diganti dengan resolusi lain.

### `admin/bank-import.js`

```text
Feature   Impor workbook bank soal
Trigger   pilih berkas lalu Pratinjau (form POST multipart biasa ke /admin/konten/impor-bank/pratinjau)
Process   cek di klien: ekstensi .xlsx dan ukuran ≤ 20 MB (umpan balik cepat saja)
Response  halaman dirender ulang server dengan ringkasan, galat, dan peringatan per baris
UI        tabel galat/peringatan dapat disaring per sheet; klik baris menyorotnya
          tombol "Simpan soal ke permainan" (form[data-import-run], hanya dirender bila tanpa galat)
          → confirmDialog berisi data-items soal untuk data-nodes tantangan → kirim
```

### `admin/media-upload.js`

```text
Feature   Unggah media
Trigger   seret berkas ke kotak unggah, atau tombol Unggah per baris aset ([data-upload-key])
Process   1. ukuran berkas ≤ 64 MB
          2. dimensi gambar dibaca createImageBitmap() sebelum mengunggah
          3. jenis berkas dibandingkan dengan jenis slot dari #asset-sizes `types` (mis. PNG atau WebP
             untuk slot transparan); SVG ditolak di slot berukuran wajib
          4. dibandingkan dengan ukuran wajib slot dari #asset-sizes `sizes` (pola asset_key, '*' = sembarang)
          5. tidak cocok → ditolak di klien dengan pesan yang menyebut jenis atau ukuran yang diminta
          6. lolos → form POST multipart biasa ke /admin/media/unggah { asset_key, file }
UI        kotak info menyebut ukuran wajib kode berkas yang diketik; tombol Unggah per baris
          mengisi asset_key lalu membuka pemilih berkas
```

Pemeriksaan di klien hanya umpan balik cepat. Server selalu memeriksa ulang (`getimagesize()`), dan nama berkas asli diabaikan: berkas disimpan dengan nama resmi dari `asset_key`.

### `admin/narration-upload.js`

Formulir unggah banyak rekaman narasi (`form[data-narration-upload]`). Batas PHP dibaca dari `data-max-files`, `data-max-file-bytes`, dan `data-max-post-bytes`; pilihan yang pasti gagal (terlalu banyak berkas, total melebihi `post_max_size`, berkas melebihi batas per berkas) ditahan sebelum dikirim, karena PHP membuang kelebihan berkas diam-diam atau kehilangan seluruh kiriman. Pencocokan nama berkas tetap dilakukan server (`NarrationImporter`).

### `admin/export.js`

```text
Feature   Status ekspor
Trigger   lencana [data-export-id][data-status] berstatus queued/running
Request   GET /api/admin/exports/{id}/status tiap 2 detik, paling lama 5 menit;
          jeda berlipat (sampai 30 detik) bila permintaan gagal
Response  { status: queued | running | done | failed, error? }
UI        label status diperbarui; saat done/failed → toast, lalu panel tabel ekspor dimuat ulang
          dari server (tanpa memuat ulang halaman), sehingga tombol Unduh, jumlah baris, dan
          SHA-256 tampil persis seperti yang dirender server
```

---

## Pencatatan Event

### Amplop

```json
POST /api/events
{ "events": [ {
    "client_event_id": "6f0c…", "event_type": "answer_selected",
    "occurred_at": "2026-10-06T02:14:22.481Z", "sequence_no": 7,
    "level_id": 1, "node_id": 2, "attempt_id": 118, "item_id": 452,
    "payload": { "action": "set", "value": "Kedu", "t_ms": 18342 } } ] }
→ { "accepted": 1, "duplicate": 0, "rejected": [], "server_time": "…" }
```

The client sends audio telemetry in the same request as an `audio` array (with `audio_dropped`/`audio_dropped_id`), and the answer then has an `audio` key with its own counts; see `core/events.js` and Telemetry audio below.

`EventService::ingest()` menerima `event_type` yang ada di `Config\Gelita::$eventTypes`, menolak `client_event_id` kosong atau ganda dalam satu batch, dan membalas `duplicate` untuk id yang sudah tersimpan. Waktu klien yang tidak dapat diurai diganti waktu server.

### Event yang dikirim klien

| Event | Pengirim | Payload utama |
|---|---|---|
| `viewport_changed` | `core/stage.js` | `w`, `h`, `orientation`, `rotate_gate`, `stage_w`, `stage_h`, `scale` |
| `page_hidden`, `page_visible` | `core/events.js` | `screen` (kode layar) |
| `reading_progress` | `core/device.js` (layar tantangan) | `percent` (25/50/75/100), `passage_id?` |
| `overlay_opened` | `challenge.js`, `map.js`, `library.js`, `engines/boleh.js`, `engines/cari.js` | `overlay` (`hint`, `exit`, `kenal`, `lightbox`, `statement_image`, `hunt_zoom`), `level` (kode wilayah) |
| `dialogue_advanced` | `game/narrator.js` | `index`, `total`, `context` (`intro`, `map_intro`, `region_intro`, `level_open`, `level_done`, `ending`), `character` |
| `level_opened` | `game/map.js` (peta wilayah) | `code` |
| `library_page_viewed` | `game/library.js` | `page`, `total`, `page_id` |
| `challenge_opened` | `game/challenge.js` (hanya melanjutkan) | `resumed: true`, `attempt_no` |
| `answer_selected` | engine batch lewat `ctx.selected()` | `action` (`swap` \| `set` \| `clear`), `value`, `from?`, `to?`, `t_ms` |
| `challenge_abandoned` | `game/challenge.js` (`pagehide`) | `via: 'pagehide'`, `attempt_open: true` |

Kolom rujukan (`level_id`, `node_id`, `attempt_id`, `item_id`) diisi bila relevan; server menyimpannya ke `challenge_node_id`, `challenge_attempt_id`, `challenge_item_id`.

### Event yang ditulis server

Hasil tantangan dicatat server di dalam transaksi yang sama dengan datanya, bukan oleh klien: `challenge_opened` (pembukaan pertama), `challenge_checked`, `challenge_completed`, `challenge_abandoned` (tombol Keluar, `via: 'user_exit'`, `attempt_no`), `answer_submitted`/`answer_changed` dan `wrong_target_clicked` (Cari Objek), `hint_opened`, `level_completed`, serta event sesi dan navigasi lainnya. Tiga event **hanya boleh ditulis server** (`EventService::SERVER_ONLY`) dan ditolak bila dikirim klien (`event_type_server_only`): `screen_viewed`, `challenge_tries_exhausted`, `clue_failed`.

### Telemetry audio

Since asynchronous logging Phase 1 the client sends audio events as the `audio` array of `POST /api/events` (see `core/events.js`); `POST /api/audio-events` takes the beacons of the audio channel and the fallback for a server without the combined request. The audio objects are the same in both.

```json
POST /api/audio-events
{ "events": [ { "client_event_id": "…", "occurred_at": "2026-10-06T02:14:22.481Z",
    "audio_asset_id": 42, "action": "complete", "play_index": 2,
    "listened_ms": 11840, "completed": true, "attempt_id": 118 } ] }
```

`EventService::recordAudio()` menulis satu baris `audio_usage_events` beserta raw event pendampingnya (`audio_play`, `audio_autoplay`, `audio_pause`, `audio_replay`, `audio_completed`) dalam satu transaksi, idempoten terhadap `client_event_id`. `attempt_id` milik sesi lain ditolak (`attempt_not_in_session`).

---

## Penyimpanan Lokal

`localStorage` hanya dipakai untuk antrean event sementara dan preferensi tampilan. Ia bukan tempat catatan penelitian: semua yang bernilai sudah ada di server. Akses yang gagal (penyimpanan penuh atau diblokir) diabaikan dan aplikasi tetap berjalan.

| Kunci | Isi | Pemakai |
|---|---|---|
| `gelita.queue.events` | gameplay events not acknowledged by the server yet (persist-on-push), `{ tag, event, b? }`; `b: 1` = also sent by beacon | `core/events.js` |
| `gelita.queue.audio` | audio events not acknowledged yet, same shape | `core/events.js` |
| `gelita.prefs` | `sound` (bawaan `true`), `volume` (bawaan 0,8), `narrationAuto` (bawaan `true`) | `core/audio.js`, `game/narrator.js` |

```js
Storage.persistEvents(batch, tag, channel)          // persist-on-push → { stored, lost }; nothing counted when refused
Storage.queueEvents(batch, tag, channel, beaconed)  // store and count what cannot be kept (QUEUE_MAX = 5000)
Storage.pendingEvents(tag, channel)                 // read this session's events without removing them
Storage.removeEvents(tag, channel, ids)             // acknowledged (or refused for good): forget
Storage.markBeaconed(tag, channel, ids)             // also sent by beacon: losing it over QUEUE_MAX is not counted
Storage.size(tag, channel)
Storage.getPref(name, fallback), Storage.setPref(name, value)
```

Setiap event yang disimpan ditandai `sessionTag`. Komputer kelas dipakai bergantian: tanpa tanda ini, event siswa A yang tertunda akan terkirim ke sesi siswa B yang masuk sesudahnya. `restoreQueued()` (page load) and `resendQueued()` (`online`) only read events stamped with the running session; the server answers `duplicate` for those that had already arrived.

Isi kata sandi tidak pernah disimpan di browser storage. Service worker tidak menyimpan data penelitian; cache-nya hanya berisi `offline.html` dan berkas paket aset (konten publik).

Asset package keys (Stage 2): `localStorage` `gelita.paket` (mirror of the device's active package: `package_code`, `manifest_sha256`) and `gelita.paket.prune` (old files still to remove); `sessionStorage` `gelita.paket.daring` (online mode chosen for this browser session). Cache Storage `gelita-aset`, `gelita-meta`, `gelita-offline-v1`. Details in [Asset package — service worker and downloader](#asset-package--service-worker-and-downloader-stage-2).

---

## Keputusan Perilaku

- **`challenge_opened`**: pembukaan pertama dicatat server saat pengerjaan dibuat; klien hanya mencatat pembukaan ulang (`payload.resumed = true`), agar satu pembukaan tidak terhitung dua kali.
- **`challenge_abandoned` dari klien** hanya saat halaman ditinggalkan tanpa Keluar/selesai (tutup tab, muat ulang), dengan payload `{ via: 'pagehide', attempt_open: true }`; pengerjaan tetap `in_progress` dan dapat dilanjutkan. Keluar lewat tombol memanggil `/abandon`, sehingga server mencatat event dengan `attempt_no`. Ganti bahasa tidak dihitung meninggalkan.
- **Aturan 3 kesempatan**: server menghitung dan menutup pengerjaan sendiri. `/check` dan `/responses` membalas `tries_left`, `completed`, `completion_reason`, dan `redirect`; `challenge.js` menampilkan penanda 3 bulatan (engine batch di kepala tantangan, Cari Objek per petunjuk), modal "Kesempatanmu tinggal {n} lagi" / "Tinggal satu kesempatan lagi", modal "Kesempatanmu sudah habis" (Susun Gambar: keping disusun utuh dulu, 600 ms) atau "Petunjuk ini dilewati". Modal penutup bertombol "Oke"; siswa tetap di layar tantangan (mis. melihat gambar utuh) dan baru pindah ke `redirect` lewat tombol Lanjut. Tidak ada tombol untuk menyelesaikan sebelum semua benar atau kesempatan habis. `/complete` hanya cadangan.
- **Cek dengan isian kosong** ditolak di klien sebelum request dikirim, jadi tidak memakai kesempatan. Soal yang sudah benar terkunci dan tidak dinilai ulang.
- **Kunci jawaban tidak pernah ditampilkan**, juga setelah kesempatan habis: kotak dan kartu yang salah tetap merah, benda Cari Objek yang dilewati tidak diungkap. Satu-satunya pengecualian adalah gambar utuh Susun Gambar, karena susunan benarnya memang publik.
- **Muat ulang** memulihkan soal yang sudah benar dari `items[].state` / `clues[].state` dan sisa kesempatan dari `tries_used`. Jawaban salah tidak dikembalikan server, jadi isiannya kembali kosong.
- **Angka ✓/✗ di bar** menghitung hasil dari respons server pada tampilan halaman ini; setelah muat ulang ✓ dimulai dari soal yang sudah benar dan ✗ diperkirakan dari sisanya bila sudah ada cek (Cari Objek: ✗ mulai dari 0). Catatan per soal tetap utuh di server.
- **Petunjuk**: setiap petunjuk baru didahului konfirmasi bernada netral; teks yang sudah dibuka disimpan di halaman dan tidak diminta ulang (setiap permintaan dihitung server). Urutan: petunjuk soal yang sedang dikerjakan, lalu petunjuk tantangan.
- **Cari Objek**: teks petunjuk aktif tampil dan diumumkan ke pembaca layar; bila petunjuknya punya rekaman yang disetujui, tombol ▶ membacakannya; tidak ada yang diputar otomatis.
- **Konfirmasi keluar** dan tombol Back memakai `confirmDialog`; `<details>` tanpa JavaScript tetap ada di markup.
- **Tidak ada yang berbunyi sebelum interaksi pengguna.** Layar bernarasi memakai kartu "Ketuk untuk mulai" atau ketukan tirai, yang sekaligus membuka elemen narasi bersama untuk Safari iPad.
- **Event penting tidak menunggu antrean.** Slide terakhir narasi, penutupan overlay Kenali, dan tirai sebelum pindah halaman memanggil `flush()`, agar tanda "sudah didengar" sampai di server lewat fetch biasa, bukan bergantung pada beacon saat `pagehide`. This matters more with the 30-second flush interval (asynchronous logging Phase 1): everything else waits for the timer.

---

## Library JavaScript

| Nama | Tujuan | Versi | Lokasi | Dimuat |
|---|---|---|---|---|
| **Howler.js** | efek suara dan musik latar; kebijakan autoplay dan pemutaran tumpang tindih yang konsisten lintas browser | 2.2.4 (dikunci pada 2.2.x) | `public/assets/vendor/howler.min.js` (+ `LICENSE-howler.md`) | `layouts/game.php`, `<script defer>` sebelum `game.js` |
| **three.js** | WebGL screen effects of the narrated scenes (owner, 19 October 2026) | 0.186.1 (core build only, no add-ons) | `public/assets/vendor/three-0.186.1/three.module.js` + `three.core.js` (+ `LICENSE-three.txt`) | `game/screen-fx.js` by dynamic `import()` of the URL in `data-fx3d`, only on a narrated scene, with WebGL 2, without reduced motion |

Selain itu tidak ada library. Tidak ada CDN: berkas vendor ada di repositori dan dimuat dari domain sendiri; versi dan checksum dicatat di `public/assets/vendor/README.md`. Narasi memakai elemen `<audio>` bawaan, bukan Howler, karena telemetry dengarnya perlu dihitung sendiri.

Alasan library lain tidak dipakai:

- **Library chart** — panel admin hanya menampilkan tabel dan hitungan mentah.
- **SheetJS/XLSX** — ekspor dan impor workbook dikerjakan server dengan PhpSpreadsheet; membangun workbook di browser membatasi ukuran data dan melewati pemeriksaan otorisasi.
- **localForage** — antrean offline hanya menyimpan event sementara; `localStorage` dengan batas 500 entri sudah cukup.
- **anime.js** — animasi yang dibutuhkan (keping bertukar dan tersusun, benda bergetar, efek narasi, tokoh berbicara) cukup dengan CSS transition dan Web Animations API.
- **Alpine/htmx** — halaman sudah dirender server; interaksi yang tersisa spesifik dan lebih jelas ditulis langsung.

---

## Aturan Sistem

1. Klien tidak pernah menentukan benar/salah, bintang, sisa kesempatan, buka-kunci, atau jumlah serpihan. Semuanya datang dari respons server.
2. Payload soal tidak memuat kunci jawaban atau penanda pengecoh, dan kunci tidak dikirim sesudah dijawab.
3. Setiap request yang menulis membawa `client_event_id` unik, sehingga pengiriman ulang tidak menggandakan data.
4. Event dikirim berkelompok paling banyak 50 per request, otomatis tiap 3 detik atau setiap 20 event, dan dipaksa saat tab disembunyikan atau ditutup.
5. `localStorage` hanya untuk antrean event sementara dan preferensi tampilan.
6. Penulisan ke DOM dari data server memakai `textContent` atau `el()`, bukan `innerHTML` dengan string gabungan. Bila `innerHTML` tidak terhindarkan untuk markup statis, nilai dinamis di-escape lebih dulu (`esc()`).
7. Tombol yang mengirim request dinonaktifkan selama request berjalan.
8. Galat tidak pernah ditampilkan kepada anak sebagai pesan teknis; yang muncul berupa kalimat biasa dan langkah berikutnya.
9. `prefers-reduced-motion` dihormati: konfeti, mesin ketik, efek layar, animasi keping, dan gulir halus dimatikan.
10. Seluruh interaksi tantangan dapat diselesaikan dengan keyboard.
11. Tidak ada permintaan ke domain pihak ketiga dari halaman permainan, kecuali iframe video Pustaka yang baru dibuat setelah tombol Putar ditekan.
12. Isi kata sandi tidak pernah dikirim lewat AJAX, disimpan di browser storage, ditulis ke `console`, atau masuk antrean event. Satu-satunya jalur keluarnya adalah submit form.
13. Aturan kekuatan sandi dibaca dari `#password-policy`; JavaScript tidak memuat angka atau syarat versinya sendiri.
14. Teks game yang ditulis JavaScript selalu lewat `t()` dengan kunci `Js.*`, sehingga ikut bahasa ID/EN.

---

## Pengujian

Tidak ada test runner JavaScript; kontrak JavaScript dijaga dari sisi PHP (`vendor/bin/phpunit`) dan diperiksa di peramban.

| Uji | Yang dijaga |
|---|---|
| `tests/unit/JsConfigTest.php` | `sessionTag` buram, stabil, dan berbeda per sesi; `js_config()` tanpa identitas; setiap `t('…')` di berkas JS punya kunci `Js.*` |
| `tests/unit/LanguageFilesTest.php` | kunci `id`/`en` sejajar; kunci yang sudah dihapus tidak dipakai lagi oleh `t('…')` |
| `tests/unit/HuntPayloadTest.php` | payload Cari Objek tanpa pembeda jebakan, token `ref` per pengerjaan, `item_id` mentah tidak dipercaya, audio petunjuk hanya rekaman yang disetujui |
| `tests/unit/TryLimitTest.php` | aturan 3 kesempatan di `/check` dan `/responses`, soal benar terkunci, cek tidak lengkap tidak memakai kesempatan, `/complete` ditolak selama kesempatan masih ada |
| `tests/unit/HuntClueAudioTest.php` | tombol ▶ narasi petunjuk di view `cari` dan pemilih rekamannya di panel |
| `tests/unit/StageCssTest.php` | CSS game dalam rem tanpa satuan viewport, media query lebar/tinggi, atau `layout.css` |
| `tests/unit/RotateGateTest.php` | media query layar putar sama di `stage.css` dan `game/rotate-gate.js` |
| `tests/unit/InstallableAppTest.php` | manifest, ikon, dan `offline.html` yang berdiri sendiri |
| `tests/unit/AssetPackageServiceWorkerTest.php` | asset package: `sw.js` and `core/package.js` write caches only through a guard for package URLs + `offline.html`; navigations, `/api`, `/admin`, login pages, POST, and private responses never cached; `/api` and `/admin` without `respondWith`; Range → 206; kill-switch worker; boot script before `game.js` |
| `tests/unit/AssetPackageCodeUrlTest.php` | `asset_url_versioned()`/`game_asset_url()` → `assets/k/…` with a package, normal URLs without one or with the kill switch; boot script attributes; `audioUrls`; `X-Gelita-Paket` on API responses |

Sintaks modul dapat diperiksa cepat dengan `node --check public/assets/js/<berkas>.js`. Skrip Playwright + Chromium di `tools/qa/` menguji JavaScript di peramban sungguhan terhadap `php spark serve`: `stage-check.mjs` (panggung 16:9 di berbagai ukuran layar, lihat [05_VIEW_UI.md](05_VIEW_UI.md)) dan `challenge-races.mjs` (MariaDB/MySQL; `--only=freeze|timing|cari|reload|race`: isian dibekukan selama `/check`, `first_input_ms`/`last_input_ms` tersimpan, lompat petunjuk diabaikan selama ketukan menunggu, muat ulang karena galat aturan tidak menulis `challenge_abandoned`, dan dua tab yang mengecek bersamaan), `package-check.mjs` (asset package Stage 2: download, resume, sha256, cache contents, 206, online mode, kill switch; see its header), serta `a11y-check.mjs` (keempat engine dengan keyboard saja: Tab mencapai semua kontrol, nama aksesibel dan cincin fokus, Enter/Spasi/panah mengoperasikan engine, dialog petunjuk dan hasil menerima fokus, Escape mengembalikan fokus ke tombol petunjuk; `--axe=…/axe.min.js` menambah pemindaian WCAG 2.1 A/AA). Semuanya keluar dengan kode 1 bila ada yang gagal. Pemeriksaan di peramban mencakup alur siswa penuh (pendaftaran, cerita, peta, keempat engine benar dan salah sampai kesempatan habis, muat ulang di tengah tantangan, Pustaka, Balai Refleksi), JavaScript mati, `prefers-reduced-motion`, dan konsol tanpa galat.

---

## Dependency

- [04_CONTROLLER_ROUTE.md](04_CONTROLLER_ROUTE.md): endpoint API, bentuk request dan response, kode galat.
- [05_VIEW_UI.md](05_VIEW_UI.md): markup, id, dan kelas yang dipakai JavaScript (`#app-config`, `#challenge-data`, `#password-policy`, `#puzzle-board`, `.blank`, `.verdict-card`, `#hunt-scene`, `.audio-player`, tirai, narrator), token CSS.
- [02_PROJECT_FOUNDATION.md](02_PROJECT_FOUNDATION.md): `Config\Gelita` (`$maxTries`, `$eventTypes`, `$maxEventsPerBatch`, `$passwordPolicy`, `$assetSizes`) dan berkas bahasa.
- [01_DATABASE.md](01_DATABASE.md): `game_event_logs`, `audio_usage_events`, `item_tries`, dan `interaction_type`.
- [docs/perombakan-kelas5/03_TAMPILAN_16x9_DAN_ADMIN.md](perombakan-kelas5/03_TAMPILAN_16x9_DAN_ADMIN.md) (Dok 3): rencana panggung 16:9 (`core/stage.js`) dan panel admin; penyimpangannya dicatat di `CATATAN_IMPLEMENTASI.md`.
