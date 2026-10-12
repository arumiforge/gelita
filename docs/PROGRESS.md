# Progress

The single checklist of what is done and what is open. Decisions and their reasons go in `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`; this file only tracks state. Each session updates only its own rows, and ticks an item only once its PR is merged into `main`.

Last updated: 11 October 2026.

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

- [ ] Approve draft narration
- [ ] Install the catalogue art: `php spark gelita:katalog:impor` (dry run first; `docs/08_DEPLOYMENT.md` → *Installing the Katalog Aset images*). This fills every slot with the interim art of [`gelita-katalog/`](../gelita-katalog/README.md); the panel's **Gambar & mockup** replaces any of it later
- [x] New character frames in the catalogue: 10 poses × 24 frames, red-scarf Jaka and Mbah Kedu (catalogue edition of 12 October 2026). On a server that already ran the import, run `gelita:katalog:impor` again: it replaces the 30 old frames and adds 210
- [ ] Before data collection, replace or approve the remaining interim art: the three Cari Objek scenes (objects do not sit on the drawn shelves) and the AI Pustaka illustrations (owner: fine for now, replaced in the panel later)
- [ ] Research-ready checklist green, study status set to "Berjalan"
- [ ] Listening test of the 48 kbps narration, 5 files per language (plan P2). Stage 1b (PR #74) was merged before this gate was recorded as done; the 96 kbps takes are in git history before PR #74 (details in `docs/audio.md` → *Narration at 48 kbps*)
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
- **Wave 2** (Stage 1 is merged, so this can start; not started yet)
  - [ ] Stage 2 — package service worker and downloader
  - [ ] Stage 6 — Rilis → Paket aset panel with the on/off switch, study package lock (§7.2)
- **Wave 3** (after Stage 2 is merged)
  - [ ] Stage 3 — "Siapkan GELITA" download screen and teacher's Periksa paket screen
  - [ ] Re-run Stage 0 timing; owner decides on Stage 7 (P1)

## Active branches

Add a row (branch, owner / scope, status) when a session starts a branch; remove it when its PR is merged.

| Branch | Owner / scope | Status |
|---|---|---|
| `pbb/nifty-faraday-fcw9r6` | 240 new character frames from the catalogue; import reinstalls changed catalogue images | PR open |
| `pbb/practical-ramanujan-sogpm3` | Production console errors: CSP policy, inline viewport script, `/api/events` 500 | PR open |
