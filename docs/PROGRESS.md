# Progress

The single checklist of what is done and what is open. Decisions and their reasons go in `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`; this file only tracks state. Each session updates only its own rows, and ticks an item only once its PR is merged into `main`.

Last updated: 20 October 2026.

## Kelas 5 overhaul

- [x] **Dok 1 — data foundation and reset.** In `main`.
- [x] **Dok 2 — challenges and question bank** (§2–§12, manual test §13.2). In `main`.
- [x] **Cleanup — fresh install only, no legacy compatibility** (PR #41). In `main`.
- [x] **Fixes before Dok 3 — Dok 2 review findings** (PR #42). In `main`.
- **Dok 3 — 16:9 stage and admin panel**
  - [x] §1–§7 16:9 game stage, docs 05/06 (PR #48, PR #49)
  - [x] §8.1–§8.3 admin panel (PR #47)
  - [x] §8.4, §9, §10 raw data, export, PDF (PR #45)
  - [x] `gelita:simulasi` and browser tests for PR #42 (PR #46)
  - [x] Browser tests for PR #42, `check_no` fix for checks from a stale tab, `gelita:simulasi` docs (PR #50)
  - [x] §11 documentation, audited against the code (PR #52, PR #53, PR #54)
  - [x] Code issues found in the §11 audit, items 1–9 (PR #56, PR #57, PR #58; item 5 through PR #60)
  - [x] Owner decision on the research-ready gate: keep as is, check the list by hand per `docs/08` (option a, 9 October 2026)
  - [x] §12.3/§12.4 tests and done criteria: keyboard pass (`tools/qa/a11y-check.mjs`) and exports in LibreOffice, pandas, and R (PR #59); stage re-run at all six sizes and admin re-run at 1280, 1440, and 1920 px (`tools/qa/admin-check.mjs`) after the audit fixes, all pass
  - [x] Duplicate student codes when two students finish sign-up at the same moment, found in the re-run (PR #63)
  - [ ] §12.3 owner pass on real devices and a screen reader: `docs/perombakan-kelas5/device-test-checklist.md`
  - [~] §13 one-off upgrade script: **dropped**. A release is a fresh install per `docs/08_DEPLOYMENT.md`

## Before the research starts (after install, done in the panel)

- [ ] Record the voice-over narration (100 lines per language) from the script PDF in **Rekaman narasi**, upload it, listen, and approve it. On a server that still has the old TTS takes, press **Batalkan semua rekaman** for each language first (decision log, 17 October 2026)
- [ ] Install the catalogue art: `php spark gelita:katalog:impor` (dry run first; `docs/08_DEPLOYMENT.md` → *Installing the Katalog Aset images*). This fills every slot with the interim art of [`gelita-katalog/`](../gelita-katalog/README.md); the panel's **Gambar & mockup** replaces any of it later
- [x] New character frames in the catalogue: 10 poses × 24 frames, red-scarf Jaka and Mbah Kedu (catalogue edition of 12 October 2026). On a server that already ran the import, run `gelita:katalog:impor` again: it replaces the 30 old frames and adds 210
- [x] Cari Objek objects moved onto the tables and shelves of the three catalogue scenes (question bank layout change, 12 October 2026). On a server that already ran the import, run `gelita:katalog:impor` again (or `gelita:bank:import`) to apply the new positions
- [ ] Susun Gambar images (catalogue edition of 14 October 2026): all 30 puzzle variants a–j now have catalogue art (21 new d–j, four redrawn: `tmg-1-01`, `tmg-1-03`, `mgl-1-01`, `wnb-1-07`). On a server that already ran the import, run `gelita:katalog:impor` again (dry run first); if one of the four was uploaded by hand, see `docs/08_DEPLOYMENT.md` → *Installing the Katalog Aset images*. Before data collection, the researcher still checks facts against the reference photos and has two people assemble each puzzle
- [ ] Language icons and old uploads (12 October 2026): run `gelita:katalog:impor` again to install the HUD language icons (`ui.lang.id`/`ui.lang.en`). If it reports a pose as "perlu dicek", or the server still shows the old start button or the lurik Jaka, replace only those slots: `gelita:katalog:impor --timpa --kunci="ui.btn-start*,ui.lang.*,char.jaka.*"` (dry run first), then rebuild the asset package if a release uses one
- [ ] Before data collection, replace or approve the remaining interim art: the AI Pustaka illustrations (owner: fine for now, replaced in the panel later)
- [ ] Research-ready checklist green, study status set to "Berjalan"
- [~] Listening test of the 48 kbps narration (plan P2): **dropped**. The TTS takes were removed for the voice-over (17 October 2026)
- [ ] Smoke test, then `gelita:reset` (by the owner, never by Claude on production)

## Display update (`docs/arsip/rencana-perbaikan-tampilan.md`, revised 11 October 2026)

Owner decisions: phases in order 0 → A → B → C → E → F, one branch and one PR per phase, each cut from `main` after the previous PR is merged. Since 11 October 2026 two sessions run in parallel (owner request): one does A → B → C, the other E → F. Phase D (character frame animation) is done (PR #83).

- [x] Plan revised against the current code (PR #84)
- [x] Phase 0 — bug fixes: music on challenge/region screens, lost and doubled password flash, dead size rules (PR #85)
- [x] Phase A — app icons, zoom and animation preview, placeholder, story slide backgrounds, `bg.auth` (PR #87)
- [x] Phase B — avatars (PR #88)
- [x] Phase C — city pins, Kenali lanterns, city loading backgrounds (PR #89)
- [x] Phase E — game-style toasts (PR #86)
- [x] Phase F — touch-friendly play (PR #90); real-device items in `docs/perombakan-kelas5/device-test-checklist.md` §4 are for the owner

- **Follow-ups from the audit of 11 October 2026** (every phase checked against the plan on `main`; recorded deviations stay as they are)
  - [x] Phase 0: `reward.badge.*` display size, automated music check `tools/qa/music-check.mjs` (PR #93)
  - [x] Phase F: Next/Done keyboard hints on the new-password fields (PR #92)
  - [x] Phase A: Nginx/Apache routing of `/manifest.json` in `docs/08_DEPLOYMENT.md` (PR #91)
  - [x] Owner call (11 October 2026): slide dots keep their narrow tap area (14–22 dp wide); Lanjut/Kembali and swipe stay the full-size way to move

## Asset package (`docs/rencana-paket-aset.md`, stages in §10)

Owner decisions in its §12 (9 October 2026): full download before Mulai, admin on/off switch. Dok 3 is merged, so the plan's "before/after Dok 3" limits no longer apply; only the stage dependencies below do. Each stage is one parallel session and one PR into `main`. This file is kept by the coordinating session; stage sessions do not edit it.

- **Wave 1** (started 9 October 2026, run in parallel; all merged 10 October 2026)
  - [x] Stage 0 — baseline timing, `tools/qa/nav-timing.mjs` (`paket-0-ukur-dasar`, PR #78)
  - [x] Stage 1 — package builder on the server, `gelita:paket:*`, `/api/paket/aktif` (`paket-1-pembangun`, PR #75)
  - [x] Stage 1b — narration re-encoded at 48 kbps mono; the owner's listening test is still open (see *Before the research starts*) (`paket-1b-narasi`, PR #74)
  - [x] Stage 4 — view transitions, image decode, short curtain (`paket-4-transisi`, PR #77)
  - [x] Stage 5 — research data integrity §7.1 and §7.3 (`paket-5-integritas`, PR #76)
- **Wave 2** (started 12 October 2026; both stages ran in parallel on one branch, `sky/laughing-rubin-l2tmm2`; merged 12 October 2026)
  - [x] Stage 2 — package service worker and downloader (`core/package.js` API for Stage 3) (PR #109, PR #110)
  - [x] Stage 6 — Rilis → Paket aset panel with the on/off switch, study package lock (§7.2) (PR #109, PR #110)
  - [ ] Owner decisions from Wave 2: deploy scripts rebuilding the package while the switch is on (§6.3); running-study lock compares with the active release's package (§7.2); preview banner in the game; pin a running session's URLs to its own package (S8)
  - [ ] Run migration `2026-01-01-003300` (study package lock) on MariaDB/MySQL before release; checked on SQLite only
- **Wave 3** (unblocked: Stage 2 is merged)
  - [x] Stage 3 — "Siapkan GELITA" download screen (`/siapkan`, Mulai gate) and teacher's Periksa paket screen (`/periksa-paket`) (`paket-3-layar`, PR #139)
  - [ ] Stage 3 manual pass on real devices: iPad (Safari tab and Home Screen), Chromebook guest session, a real private window
  - [ ] Re-run Stage 0 timing; owner decides on Stage 7 (P1)

## Story script and question bank revision (draft in `gelita-katalog/update/`)

- [x] Review of the owner's draft 1 against the live script and bank, and revised draft 2 in `gelita-katalog/update/` (review: `telaah-draf-1-naskah-bank-soal.md`; changes: `catatan-revisi-gelita.md` §7)
- [ ] Researcher checks every `needs_verification` item and the facts in review §6
- [x] Integrate draft 2 into the app (review §7): puzzle variants a–j with the first attempt drawn from a–c, skill `membedakan_fakta_pendapat`, one Temanggung distractor, explicit item keys in `docs/bank-soal/data/*.php`, 32 script lines in `data/story.php`, tests (PR #115)
- [x] `gelita:story:sync` to update the story lines of an installed server to the current script (PR #116)
- [~] Re-record 32 ID and 28 EN narration lines after integration: **replaced** by the full voice-over (see *Before the research starts*)
- [ ] On a server that already has content: re-import the bank (`gelita:bank:import`), update the 32 script lines with `gelita:story:sync` (dry run first; lines edited in the panel are kept unless `--timpa`), run `gelita:katalog:impor` for the 21 new puzzle images, and fix old slot notes in **Gambar & mockup** (the import does not overwrite filled notes)

## Asynchronous logging (`docs/rencana-log-asinkron.md`)

- [x] Recommendation document merged (PR #119)
- [x] Owner decisions in its §10 (17 October 2026): Phases 1 and 2 approved, one branch each, developed in parallel (split by layer, Phase 2 merges first); 30 s flush; Phase 3 after measuring; dashboard lag accepted with a note
- [x] Phase 1 — browser (PR #123): 30 s flush with persist-on-push, events + audio in one request, heartbeat only when idle, page-load re-send in the first batch
- [x] Phase 2 — server (PR #122): `/api/events` takes `audio`, heartbeat in the batch, session lock released early, batched audio writes, `Gelita::$eventFlush`, dashboard note
- [x] Load measurement before and after (§7.1, `tools/qa/load-test.mjs`): at 90 students every target is met; Phases 1 + 2 cut telemetry requests by 83 % and database statements, fsyncs, and CPU by about two thirds
- [x] Owner decision (18 October 2026): Phase 3 (write-behind inbox) is not built; the targets are met at 90 students
- [ ] On the real server: re-run the load test (or watch Ringkasan and `fpm-status` in a full class) once the school hardware is known, since the test machine had more RAM than `docs/08` recommends

## Page redesigns (`gelita-katalog/mockup/gambar-mockup/`)

Owner request (18 October 2026): several pages get a new design from the catalogue mockups, one page per branch.

- [x] Page 1 — Tantangan selesai `/selesai/{id}` (mockup `tantangan-selesai-temanggung`): redesigned panel, opt-in menu HUD, result skin slots, `ui.result-medal` from `avatar.2` (PR #128)
- [ ] After merge, on a server that already ran the import: `php spark gelita:katalog:impor --kunci="ui.result*,bg.result*"` (dry run first) to install the new skins
- [x] Page 1 follow-up (PR #129): HUD language icons from the catalogue's newest flags `icon.language-id/-en`; region-done card as a modal over a blurred, dimmed stage. After merge, run `php spark gelita:katalog:impor --kunci="ui.lang.*"` (dry run first; add `--timpa` if an admin uploaded the language icons)
- [x] Page 1 touch fix: the region-done modal and its Pustaka card enlarged (card 1040 × 560 dp, close button 64 dp, Baca Pustaka 72 dp) (PR #130)
- [x] Page 2 — Peta Kedu `/peta` (mockup Peta Kedu candidate 04 with owner changes): diorama `map.kedu-diorama`, one region panel, menu HUD with shard pill, story bar, bottom row Pustaka/Penutup/Refleksi (PR #133)
- [x] Page 2 follow-up (PR #134): story bar as wide as the map with Jaka's avatar `ui.jaka-avatar` (from catalogue `reference.03`) instead of a pose frame and a round Next like Previous; page buttons right of the story under the region panel; Mulai/Lanjut without the arrow. After merge, run `php spark gelita:katalog:impor --kunci="ui.jaka-avatar"` (dry run first)
- [ ] One look on every page: the game HUD (logo, shard pill with the bar beside the bold count, language, sound, menu) on every page, compact on pages laid out for the old 56 dp strip; every main button with the Lanjut / Balai Refleksi gold skin; mission card after the owner's picture; check badge on completed diorama regions; the owner's Kenali picture (`assets/ui/icon-kenali.webp`); story line `intro-07` names the lantern "at the top of the screen". After merge, run `php spark gelita:story:sync` (dry run first) for `intro-07`; its voice-over line changes too
- [ ] Intro redesign (owner, 18 October 2026): full-size HUD with "Bagian n dari N" and the page dots in its centre, Lewati under the menu button, full-width caption at the bottom with the narrator controls on its title row (▶ reads "Lanjut", the caption's Lanjut button is gone), "Mulai petualangan" centred on the page; bigger touch targets. The ending (`/penutup`) shares the layout
- [ ] Account pages and tap card (owner, 18 October 2026): `/masuk` and `/ganti-sandi` on the owner's lantern-tree picture, `/daftar/1–3` on the gate picture (both shipped in `public/assets/ui/`), form panel in the empty right side, no Jaka; "Ketuk untuk mulai" covers the whole stage with the HUD, shows the `ui.lantern` picture, large text, no frame; CSS/JS `?v=` follows each file while package mode is off. After merge, run `php spark gelita:katalog:impor --kunci="ui.lantern"` (dry run first) if the server has no lantern picture yet
- [ ] Jaka on the account pages (owner, 18 October 2026): the owner's waving Jaka as one still picture (`public/assets/ui/jaka-wave.webp`, no animation frames) on `/masuk`, `/ganti-sandi`, and `/daftar/1–3`, standing on each background's path
- [ ] Intro, curtain, and Peta Kedu polish (owner, 18 October 2026): intro without focus ring on the title or borders in the controls, character beside the caption on top, more room under the eyebrow, dark ring on the current dot, menu with Profil · Kembali ke awal · Layar penuh · Keluar, "MULAI PETUALANGAN" without arrow; map curtain lantern as a plain larger picture and the tap button pulsing as its picture; Peta Kedu city captions on the panel skin without outline, region panel on `bg.loading.{wilayah}` with status icons and colours, wider progress bar, Lanjut as tall as Kenali, small story controls top right, story bar 144 tall and the whole diorama shown
- [ ] After merge, on a server that already has content: `php spark gelita:katalog:impor --kunci="map.kedu-diorama"` (dry run first) and `php spark gelita:story:sync` (dry run first) for the new `peta-03` line; its voice-over line changes too
- [x] Welcome modal and region dialogue (owner, 19 October 2026): the welcome card after sign-up opens as a modal over the intro's tap card; `/dialog/{code}` uses the intro layout (full-size HUD, Lewati under the menu button, no Peta Kedu button, full-width caption with room for both characters at its ends, "Bagian n dari N" and the dots right of the speaker's name) and shows the region's difficulty as an animated badge in the HUD left of the language control, instead of the screen head (PR #154)
- [x] Region dialogue, second follow-up (owner, 19 October 2026): the parchment box is back (not full width) with the speaker's name, small dots (no "Bagian x dari x"), and the narrator controls in its first row; characters at the stage's edges; shard pill under the logo; region name in capitals (PR #158)

## Story screen backgrounds and effects (owner, 19 October 2026)

Decided in an interview with the owner; decisions in the decision log, section *Own background per slide for the region scenes*.

- [x] Part 1 — own background per slide for `region_intro`, `level_open`, `level_done`: field in **Konten → Dialog**, crossfade with slow zoom, fallback to the region background, catalogue keys `bg.cerita-{kenal|dialog|tuntas}-{wilayah}-NN`, optional `bg` in `data/story.php` (PR #164)
- [ ] Art for the region slides (catalogue team); until it exists every slide shows the region background. After it lands: `php spark gelita:katalog:impor --kunci="bg.cerita-*"` (dry run first)
- [x] Part 2 — screen effects with three.js 0.186.1 (vendored, loaded only on the narrated scenes, CSS effects as the fallback): drifting fog, lantern sparks, light rays with bloom, background parallax; switch `gelita.screenFx` (PR #165)
- [x] Dip to black between opening and ending story slides (owner report: another picture flashed between slides); only the background goes dark (PR #167)
- [ ] On a server with package mode on: **Bangun ulang paket** so the package carries the three.js files (PR #165)
- [ ] Owner check of the screen effects on the class devices (frame rate, look): `docs/perombakan-kelas5/device-test-checklist.md`

## Kenali screen redesign (owner, 19 October 2026)

- [x] Mengenal {wilayah} overlay (PR #169, test fix in the follow-up): Mbah Kedu's round avatar `ui.kedu-avatar` (catalogue `reference.02`) on the caption's left end instead of animation frames; no "Wilayah n · tingkat" and "Bagian n dari N" text; speaker, title, dots, and Lanjut on one row; Kembali (no cross) at the top left, the logo in the middle of the top band, the title centred under it. After merge, run `php spark gelita:katalog:impor --kunci="ui.kedu-avatar"` (dry run first)

- [x] Kenali follow-up (owner, 19 October 2026): avatar and caption at the bottom; Cinzel Decorative as the display font everywhere; Kembali on the owner's navy plate, Lanjut on the gold plate (Kenali only); the speaker's name on a navy nameplate over the avatar ring; "Mengenal {wilayah}" in gold letters over an ornament rule; narrator controls on the HUD skin without a band; the framed Jaka avatar everywhere (Peta Kedu too) (PR #171). After merge, run `php spark gelita:katalog:impor --kunci="ui.jaka-avatar"` (dry run first), then **Bangun ulang paket** if package mode is on

- [x] Background music and Kenali title (owner, 19 October 2026): twelve synthesized gamelan tracks (home/account pages, opening, ending, and per region Kenali, dialogue, and region done), music down to about 20 % under a narration voice, the mission card with the challenge music; Kenali title 32 dp; the HUD flag "overflow" was a missing file in the local check, not a bug (PR #173). After merge, on a server with package mode on: `php spark gelita:media:scan`, then **Bangun ulang paket**. Owner: listen on a class device that the music sits well under the recorded voices

## Challenge screen redesign (owner, 20 October 2026)

- [x] Challenge header (number ring, title, one stats capsule, no region/position line), centred gold action bar, Isi Rumpang dotted blanks without box or number, words and Benar/Salah on the navy plate, "Tentang gambar ini" panel, the star picture for every game star, status medallions on the lantern, region map, and Peta Kedu (PR #175)
- [x] Add the owner's three status medallions as `public/assets/ui/status-open.webp`, `status-progress.webp`, `status-done.webp` (PR #180); then **Bangun ulang paket** if package mode is on

## Story screens: tap card first, lighter caption (owner, 20 October 2026)

- [ ] Intro, ending, region dialogue, and region done: the slide picture stays dark until "Ketuk untuk mulai" is tapped; intro and ending caption and narrator controls without band or shadow, round controls on the `ui.result-hud-control` skin and Lanjut on `ui.result-button-primary`, all 40 dp; no "Bagian n dari N" text, only the dots (also the region dialogue's round controls)

## Active branches

Add a row (branch, owner / scope, status) when a session starts a branch; remove it when its PR is merged.

| Branch | Owner / scope | Status |
|---|---|---|
| `pbb/determined-tesla-qpw54u` | Story screens: tap card first, lighter caption | PR open |
