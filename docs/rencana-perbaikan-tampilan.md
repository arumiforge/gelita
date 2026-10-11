# Plan: GELITA display update — bug fixes, admin images, avatars, city pins and lanterns, game-style toasts, touch-friendly play

> **Status: revised plan, approved for implementation** (revised 11 October 2026 against `main` at PR #83). Phases run in order, **0 → A → B → C → E → F**. Each phase gets its own short-lived branch cut from `main` and its own PR; the next phase starts once the previous PR is merged.
>
> The first version of this plan (29 September 2026, in Indonesian) was written for the old responsive layout (`layout.css`, pixel breakpoints, landscape-phone layout). That layout is gone: the game is now one fixed 1280 × 720 dp stage (Dok 3), so most of the old file references and sizes no longer apply. The old text is in git history (commit `1943499`); this version replaces it.
>
> **Already done, not repeated here:**
> - **Phase D, character frame animation** (PR #83, 11 October 2026): `core/sprite.js`, `character_frames()`, `CharacterFramesTest`; Mbah Kedu's `happy` pose was already changed to `smile`. Only the admin preview of the animation is left (now A4).
> - **Single slot catalogue `App\Libraries\ImageSlots`** (PR #80, 10 October 2026), in a text-only form, plus the slot detail page `/admin/media/{id}`, filters on `/admin/media`, and the "Gambar tantangan" group in Kelengkapan.
> - **Shared lightbox `core/lightbox.js`** (Dok 3 §7), used in Pustaka and on Benar atau Salah cards.
> - **Exact-size rule relaxed** (PR #81): larger images with the same ratio are accepted and shrunk (`ImageFitter`).

## Owner decisions (11 October 2026)

1. **Order:** bug fixes first (Phase 0), then A → B → C → E → F.
2. **Region loading curtain:** a new image slot per city, `bg.loading.{wilayah}`, falling back to the region background `bg.{wilayah}.region`, then to today's gradient.
3. **Touch scope:** full (Phase F below), not essentials only.
4. **Branches:** one branch and one PR per phase, cut from `main`.

Decisions from the first version that still stand: the avatar is chosen in the profile and in the sign-up form; one pin image per city, with automatic status marks and a global fallback pin; app icons are uploaded per size, with no generator.

## Superseded parts of the first version (not built)

| Old item | Why it is dropped |
|---|---|
| Everything referring to `layout.css`, `.screen-medium`/`.screen-full`, pixel breakpoints, `vw`/`vh`, landscape-phone layouts | `layout.css` is deleted. Layout lives in `stage.css` with no width/height media queries (`StageCssTest`). |
| Phase B4, full-width profile | The profile is a stage layout with two panels and four stat tiles (Dok 3 §4). |
| "Tampilan permainan" admin page with a card grid (old Phase A3) | `/admin/media` (filters per group, region, challenge) and the slot detail page from PR #80 already do this. New slots join `ImageSlots` and appear there. |
| Phase E layout (toast in the centre, `min(560px, 90vw)`) | The toast sits inside `#stage` at x 856, y 72, width 400 dp (Dok 3 §3.3). It keeps that place; only its look and behaviour change. |
| Phase F1 tables of px targets, text sizes, safe-area per phone breakpoint | All sizes are dp and scale with the stage; targets are handled as hit areas (F2). |
| Phase F2 item 5 ("Selesaikan dengan jawaban ini" modal) and the `pilihan` notes | Replaced by the 3-tries rule and the four grade 5 engines (Dok 2). |
| Rumpang keyboard handling (old F2.3/F2.4) | Rumpang has no text inputs any more: blanks and words are buttons. |
| SVG favicon slot `app.favicon-svg` | Not needed; the PNG favicon plus the static `favicon.ico` cover every browser. |

---

## Phase 0 — Bug fixes found in the audit

Branch `tampilan-0-perbaikan`. Small, independent fixes; each gets a test.

1. **Music never plays on challenge and region screens.** `Sfx.music()` (`core/audio.js:113`) returns at once while audio is locked, and audio is locked on every new page until the first tap. So `challenge.js` `Sfx.music('challenge')`, `map.js` `Sfx.music('region')`, and the no-curtain path of `Sfx.music('map')` never play; only the map curtain path works because its tap unlocks first.
   - Fix: `music(name)` remembers the requested track while locked (`pendingMusic`), and `unlock()` starts it. `stopMusic()` clears it. Turning sound off still wins.
   - Check in Chromium: open a challenge, tap once, music starts; with sound off it does not.
2. **"Kata sandi baru tersimpan" never shows.** `LoginController` (change password, line 141) redirects to `/mulai` with the `message` flash; `HomeController::start()` sees a logged-in student and redirects again to `/gerbang` (and `/gerbang` may redirect to `/intro`), so the flash is consumed by `/mulai` and lost.
   - Fix: redirect straight to `/gerbang`, and let the "intro not seen" redirect in `BaseGameController` keep flash data (`keepFlashdata('message')`), so the toast shows on whichever screen the student lands on.
   - Test: feature test that follows the redirects and finds the message.
3. **"Kamu harus mengganti kata sandi" shows twice** on `/ganti-sandi`: once as the flash toast set by `LoginController` (line 48) and once as the alert in `change-password.php`. Fix: drop the flash; the alert stays. Same check for the admin (`AuthController` line 80 against `admin/account/password.php`).
4. **Uploads to region maps, badges, and library images skip the size check.** The `$assetSizes` keys `map.region`, `reward.badge`, and `library.image` match no real key (`requiredSize()` and `media-upload.js` use an exact match or `fnmatch`; real keys are `map.region.temanggung`, `reward.badge.temanggung`, `library.*`).
   - Fix: `map.region.*` (1400 × 900) and `reward.badge.*` (320 × 320) in `$assetSizes` and `$assetDisplaySizes`. Remove `library.image`: `ImageSlots` already treats library sizes as recommended only, and Commons images arrive in many shapes.
   - `AssetChecklist::FALLBACK_SIZES` and `MediaController::displaySize()` use prefix matching to work around the same mismatch; switch them to the shared `fnmatch` rule.
   - **Slots with a required size accept raster images only.** An SVG has no pixel size, so today it skips the check on any slot; refuse SVG wherever `requiredSize()` returns a size.
   - New test: every `$assetSizes` / `$assetDisplaySizes` pattern matches at least one key registered by `MediaAssetSeeder::slots()`, so a dead pattern cannot come back.
   - Files already uploaded at a wrong size stay; Kelengkapan already lists them as "Ukuran tidak sesuai".

Docs: `05_VIEW_UI` (flash flow), decision log entry.

## Phase A — Every game image replaceable from the panel

Branch `tampilan-a-gambar-admin`.

1. **One source for slot lists.**
   - `MediaAssetSeeder::slots()` takes region codes from `Config\Gelita::$levelPrefixes` instead of its hard-coded list.
   - `ImageSlots` gets a machine-readable `types` field per slot (for example `['png', 'webp']` for transparent slots). `MediaStore::store()` refuses other types, and `admin/media-upload.js` checks the same list before upload. Today `format` and `transparent` are advice text only.
   - `ImageSlotsTest` also checks that every `$assetSizes` pattern belongs to a described slot pattern.
2. **New group "Ikon aplikasi"** in `ImageSlots::GROUPS`, with slots (PNG only):

   | Slot | Size | Used for |
   |---|---|---|
   | `app.favicon` | 48 × 48 | `<link rel=icon>` in the game, admin, and admin login layouts |
   | `app.apple-touch` | 180 × 180 | `apple-touch-icon` |
   | `app.icon-192` | 192 × 192 | manifest |
   | `app.icon-512` | 512 × 512 | manifest |
   | `app.icon-maskable` | 512 × 512, safe zone 80% | manifest, `purpose: maskable` |

3. **Dynamic manifest and icons.**
   - New `Game\AppController::manifest()` serves `GET manifest.json` at the same URL, so installed apps keep updating. The static `public/manifest.json` is removed; its fields move into the controller. Header `application/manifest+json`.
   - Icons use the active slot with `?v={sha}`; an empty slot falls back to the static files in `public/assets/app/` (kept in git).
   - New helper `app_icon_src($slot, $fallback)` in `ui_helper.php`, used by the three layouts.
   - Check that Nginx and `.htaccess` pass `/manifest.json` to `index.php` once the file is gone; document it in `08_DEPLOYMENT`.
   - `offline.html` keeps its inline SVG (it must work with no network). Icons and manifest stay out of the asset package, as the package plan says.
   - `InstallableAppTest` is rewritten for the controller: manifest fields, static fallback icons at the right sizes, slot icons with `?v`, `link rel` tags in the layouts.
4. **Zoom and animation preview in the panel.**
   - `core/lightbox.js` is reused in `admin.js` (delegation on `[data-zoom]`), with its own `.lightbox` rules in `admin.css`. Used on the `/admin/media` thumbnails, the detail page preview, and `components/media-field.php`. Without JavaScript the link opens the image in a new tab, as today.
   - The detail page of a `char.*` slot shows the pose's frames side by side plus a looping preview with `core/sprite.js` (left open by Phase D).
5. **`ui.placeholder` actually used.** `media_src()` falls back to the `ui.placeholder` slot, then to `assets/ui/placeholder.svg`. The string check `str_ends_with(…, 'placeholder.svg')` in `GameProgress` becomes a helper `media_is_placeholder()`. `ImageSlots` marks the slot as used.
6. **Story slide backgrounds from the panel.** The dialogue editor (`admin/content/dialogues.php`, contexts `intro` and `ending`) gets a `media-field` per slide; the form becomes multipart; `ContentController::saveDialogues()` resolves each row like the passage editor does. Default key `bg.story.{context}.{sequence}`, described in `ImageSlots` (group "Latar layar", optional, fallback = `bg.intro` → `bg.welcome`). It falls under `bg.*`, so 1920 × 1080 is enforced.
7. **`bg.auth` actually used.** `/masuk`, `/daftar/1–3`, and `/ganti-sandi` use `bg.auth`, falling back to `bg.welcome`. Nothing changes on screen until a picture is uploaded. `ImageSlots` and Kelengkapan list it as optional.

Docs: `05_VIEW_UI`, `06_JAVASCRIPT`, `08_DEPLOYMENT`, README asset section, decision log.

## Phase B — Avatars

Branch `tampilan-b-avatar`.

1. **Data.** New migration `2026-01-01-003200_AddParticipantAvatar.php` adds `participants.avatar_no TINYINT UNSIGNED NULL`. It is a new file, as the fresh-install rule requires; `ResetTables` and `SystemResetService::REQUIRED_MIGRATION` are updated. `ParticipantModel::$allowedFields` and `Participant::toSafeArray()` gain the field. It is not exported and not logged as a research event (it is not research data).
2. **Slots and helpers.**
   - `Config\Gelita::$avatarSlots = 10`. Slots `avatar.1` … `avatar.10`, 512 × 512, PNG or WebP with transparency, new `ImageSlots` group "Avatar", optional. The seeder registers them empty.
   - `avatar_choices()` returns only active `avatar.N` slots; `avatar_src(?int)` returns the image or null.
   - New component `components/avatar.php`: the image when there is one, otherwise today's initial. Used in the HUD (`.user-avatar`, 36 dp) and the profile (`.profile-avatar`, 72 dp).
3. **Picker** `components/avatar-picker.php`: a grid of round tiles of at least 64 dp, "Inisial" first, then the active avatars. The chosen tile has a gold ring and a ✓ (not colour alone). Built on the `.choice` radio pattern (`:has(input:checked)`, `:has(input:focus-visible)`). Not rendered when no avatar is uploaded. Works without JavaScript.
   - **Sign-up:** optional, on `/daftar/1` below gender (step 1 has the most room; the fields area scrolls). Kept in the `reg_wizard` session with the other step 1 fields and saved by `SessionService::registerAndStart()`. `RegisterController` validates `permit_empty|in_list[active numbers]`.
   - **Profile:** a `<details>` "Ganti avatar" under the identity card in the left panel (`.profile-side`, which scrolls). Each tile is a submit button, so one tap saves. Route `POST profil/avatar` → `ProfileController::avatar()` in the `gameSession` group, flash "Avatar tersimpan", back to `/profil`.
   - New texts in `app/Language/{id,en}/Game.php`.

Docs: `01_DATABASE`, `05_VIEW_UI`, `04_CONTROLLER_ROUTE`, decision log.

## Phase C — City pins, Kenali lanterns, city loading backgrounds

Branch `tampilan-c-kota`.

1. **Slots** (new `ImageSlots` entries, all optional, 256 × 256 PNG/WebP with transparency unless noted):

   | Slot | Fallback | Used for |
   |---|---|---|
   | `ui.pin` | today's SVG icon | map pin of any city without its own pin |
   | `map.pin.{wilayah}` | `ui.pin` | that city's pin on `/peta` |
   | `ui.lantern` | today's SVG icon | HUD lantern, map curtain lantern, Kenali fallback |
   | `map.lantern.{wilayah}` | `ui.lantern` | that city's Kenali button and card button |
   | `bg.loading.{wilayah}` (1920 × 1080) | `bg.{wilayah}.region`, then the gradient | the "Menuju {wilayah}…" curtain |

   `AssetPackageBuilder::groupFor()` maps the three per-city patterns to their `wilayah-{code}` group.
2. **Pins.** Helper `region_art_src('pin'|'lantern', $code)` resolves the chain above. With art, `.pin-icon.has-art` shows the picture (`object-fit: contain`) without the gold disc. Status marks are added automatically on top of the art: locked = greyscale plus a small 🔒 badge, completed = ★ badge, in progress = the existing pulse. The pin keeps its 56 dp size and its percent position (`levels.map_x`/`map_y`).
3. **Kenali lantern.** `.kenal-btn-face` and `.kenal-card-btn` use the city lantern. Check the overlap the first version found (the 48 dp Kenali hit area covering a corner of the pin) and move the button clear of the pin if it still overlaps.
4. **HUD and map curtain lantern** use `ui.lantern`; the flame stages `flame-0..3` become a `drop-shadow` glow on the picture.
5. **Region curtain background.**
   - `GameProgress::regionCurtain()` adds `bg` (the chain above) and puts it first in `preload`.
   - `curtain_attrs()` gets a `?string $bg` parameter that writes `data-curtain-bg`. The `region` template gets `<img class="curtain-bg" hidden>`; `core/curtain.js` fills it and fades it in after `load`.
   - Callers pass it: map pins and region cards (`map-kedu.php`), the last Kenali slide (`partials/region-intro.php`), "Lanjut ke …" on `region-done.php` and `challenge-finished.php`. The mission brief's `challenge` curtain uses the same city background.
   - Once the asset package is on, the curtain shows for about 0.6 s, so the picture must already be decoded: `map-kedu`'s `data-next-assets` list and `map.js` `preload()` include it.
6. **Level editor** (`admin/content/level.php`) gets three key-based `media-field`s (map pin, Kenali lantern, loading background) through `MediaStore::resolveKey()`. No new FK columns.

Docs: `05_VIEW_UI`, `06_JAVASCRIPT`, decision log.

## Phase E — Game-style toasts

Branch `tampilan-e-toast`. Applies to `body.game`; the admin keeps its look. The API `toast(message, type, requestId)` is unchanged, so every caller keeps working.

1. **Look.** Same place on the stage (x 856, y 72, width 400 dp). A navy card with a glowing gold frame, a large round icon badge (✓ ! ✕ i) from `<template data-toast-icon>` in `components/toast.php` (JS toasts have no icon today), text at body size. Enters with a short pop (0.6 → 1.06 → 1), leaves by fading upward; reduced motion only fades. Today the toast is simply removed with no exit.
2. **Behaviour** (`core/toast.js`).
   - One toast at a time, at most 3 waiting; identical consecutive messages are merged.
   - Duration follows text length (1.8–5 s).
   - Tap the card, or press Esc while no modal is open, to close it. The layer stays `pointer-events: none` outside the card.
   - Toasts do not wait for modals: errors must be seen. The layer stays above the modal veil (z 60 over 50).
   - `bad` uses `role=alert`, the others polite status.
3. **Server flash.** Without JavaScript the flash toast fades by CSS after about 3.5 s, as today. With JavaScript it joins the same queue, and faded flash toasts are removed from the DOM (today they stay hidden there).
4. **Flash `error` in the game** is shown as `warn`: every game `error` flash is a "not open yet" lock message (`MapController`, `ChallengeController`, `DialogueController`, `LibraryController`, `ReflectionController`), the same as the lock toast from JS.
5. New text (`toastClose`) in `app/Language/{id,en}/Js.php`.

Docs: `05_VIEW_UI`, `06_JAVASCRIPT`, decision log.

## Phase F — Touch-friendly play on tablets and phones

Branch `tampilan-f-sentuh`. On a landscape phone (844 × 390) the stage scale is about 0.54, so 48 dp is about 26 px on screen; an iPad (1024 × 768) is 0.8. The fixed stage cannot enlarge controls on phones only, so the work makes every target use its full area, removes touch-specific annoyances, and adds touch gestures. PC, laptop, and tablet stay the main research devices.

**F1 — CSS basics** (`stage.css`, `base.css`, `components.css`, `game.css`, `cinematic.css`; inside the stage rules of `StageCssTest`)

1. `touch-action: manipulation` on game controls and play surfaces (stops the iOS double-tap zoom on fast taps; pinch zoom still works). `-webkit-tap-highlight-color: transparent` for the whole game (today only on `.piece`).
2. `user-select: none` and `-webkit-touch-callout: none` on play surfaces (pieces, objects, word chips, map, characters); reading text stays selectable. `draggable="false"` on scene, map, and character images.
3. **Press feedback:** `:active` (scale .96 or brighter) on buttons, options, words, blanks, pieces, objects, pins, and cards. iOS applies `:active` only when a `touchstart` listener exists, so `game.js` adds one (passive).
4. **No sticky hover:** about 38 `:hover` rules apply today without `@media (hover: hover)`; the ones that transform or outline (`.object`, `.piece`, `.map-pin`, `.card-link`, `.rating-star`, HUD `.icon-btn`, `.btn`) move inside it (the query is already allowed by `StageCssTest`).
5. **Reduced motion gaps:** the base rule has no `!important`, so shorthand animations win. Add explicit rules for `.btn-start` `start-glow` (pulses forever), `.kenal.is-open` `cine-in`, `.cine-stage .character-img` `cine-rise`, and the hover/selection transforms above.

**F2 — Hit areas of at least 48 dp** (the stage's `--tap`)

Controls below 48 dp keep their look, but their hit area grows to 48 dp with `::before`:

| Control | Today | Where |
|---|---|---|
| Slide dots | 14 dp, 10 dp in story/dialogue | `game.css:492`, `cinematic.css:467` |
| `.btn-sm` (challenge "Keluar", map-level, profile) | 36 dp | `components.css:49` |
| Narrator controls and auto switch | 40 dp, 36 dp on the map story | `cinematic.css:257, 501, 504` |
| Story/dialogue Lanjut and Kembali | 40 dp | `cinematic.css:465` |
| HUD icon buttons | 40 dp | `stage.css:150` |
| Hint chips | 40 dp | `game.css:747` |
| `.lang-opt` | 38 dp tall | `components.css:251` |
| Word chips, in-text blanks | 44 dp | `game.css:903, 921` |
| Checkbox and radio | 22 dp (label is clickable) | `components.css:156` |

`tools/qa/stage-check.mjs` gets a check that every interactive element in the stage has a 48 dp hit area.

**F3 — Gestures and screens** (JS)

1. **Finger drag for puzzle pieces.** New `core/drag.js` (pointer events, `setPointerCapture`, 8 px threshold): drag a piece onto another to swap them. It replaces the HTML5 drag-and-drop path in `engines/puzzle.js` for mouse and touch alike; tap-swap and the keyboard stay. Swaps send the same events as tap-swap. The puzzle hint text says "ketuk dua keping atau seret".
2. **Cari Objek "Perbesar adegan"** (⤢ button, 48 dp). The scene (864 × 486 dp, about 468 × 263 px on a phone) grows to the full stage with the clue on a ribbon at the top; close with the button or Esc. Objects are placed in percent, so no data changes. Logged as `overlay_opened` with `overlay: hunt_zoom` (an existing event; the value list in the docs and export dictionary gets the new value).
3. **Swipe left/right** for story slides, dialogue, Kenali, and Pustaka pages (`game/slides.js`, narrator, library): 50 px threshold, vertical movement and touches that start inside a scrolling panel or on a control are ignored. A swipe does exactly what the Lanjut/Kembali button does, including its events.
4. **On-screen keyboard on form screens** (`/masuk`, `/daftar/1–3`, `/ganti-sandi`, reflection, feedback). New `core/keyboard.js`: when a field is focused and `visualViewport` shows the keyboard covering it, the stage frame is shifted up just enough to show the field, and shifted back on blur or when the keyboard closes. `autocorrect="off"` on usernames and school fields, `enterkeyhint` next/done.
5. **No input zoom on iOS.** Inputs are 20 dp, about 11 px on a phone, and iOS zooms the page on focus below 16 px. `game.js` adds `maximum-scale=1` to the viewport meta on iOS only: iOS then skips the focus zoom but still allows pinch zoom (Android would block pinch zoom, so it does not get the flag).

**Suggested, not in this plan:** iPad Split View shows the rotate screen by mistake; modal background not `inert`; iOS splash images; pins for challenge posts on the region map.

---

## Per phase

- **Before starting:** cut the branch from the latest `main`, add a row to "Active branches" in `docs/PROGRESS.md`.
- **Tests:** `vendor/bin/phpunit --no-coverage` green; `php -l` on changed PHP; new or updated tests as listed per phase. Phases that change the game screen also run `tools/qa/stage-check.mjs` at all six stage sizes, and Phase F runs Playwright with `hasTouch`/`isMobile` at 844 × 390 and 1024 × 768.
- **Real devices (owner):** items for touch, keyboard, and audio are added to `docs/perombakan-kelas5/device-test-checklist.md` by the phase that adds them.
- **After merge:** tick the phase in `docs/PROGRESS.md`, remove the branch row, and add the phase's entry to the decision log.

## Main files per phase

| Phase | New | Changed |
|---|---|---|
| 0 | — | `core/audio.js`, `LoginController`, `BaseGameController`, `HomeController`, `Config/Gelita.php`, `MediaStore`, `AssetChecklist`, `MediaController`, `admin/media-upload.js` |
| A | `Game\AppController` | `ImageSlots`, `MediaStore`, `MediaAssetSeeder`, `content_helper.php`, `ui_helper.php`, `GameProgress`, `ContentController`, `layouts/*`, `admin/media/*`, `admin/content/dialogues.php`, `components/media-field.php`, `admin.css`, `admin.js`, `Routes.php`; `public/manifest.json` removed |
| B | migration `003200`, `components/{avatar,avatar-picker}.php` | `Participant`, `ParticipantModel`, `SessionService`, `RegisterController`, `ProfileController`, `hud.php`, `profile.php`, `register/step-1.php`, `ResetTables`, `SystemResetService`, `Routes.php`, `Language/{id,en}/Game.php` |
| C | — | `ImageSlots`, `MediaAssetSeeder`, `AssetPackageBuilder`, `GameProgress`, `ui_helper.php`, `curtain.php`, `core/curtain.js`, `map-kedu.php`, `lantern.php`, `region-intro.php`, `region-done.php`, `challenge-finished.php`, `mission-brief.php`, `game/map.js`, `admin/content/level.php`, `ContentController`, `game.css`, `cinematic.css` |
| E | — | `core/toast.js`, `components/toast.php`, `components.css`, `stage.css`, `Language/{id,en}/Js.php` |
| F | `core/drag.js`, `core/keyboard.js` | `game.js`, `engines/puzzle.js`, `engines/cari.js`, `game/slides.js`, `game/narrator.js`, `game/library.js`, game CSS files, form views, `tools/qa/stage-check.mjs`, `device-test-checklist.md` |
