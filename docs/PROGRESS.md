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
- [ ] Upload all required images in the panel (**Gambar & mockup** → Detail per slot; Kelengkapan lists what is missing), including the 45 sample (mockup) challenge images. Prompts and style: the team's Katalog Aset ZIP (Edisi 03)
- [ ] Research-ready checklist green, study status set to "Berjalan"
- [ ] Smoke test, then `gelita:reset` (by the owner, never by Claude on production)

## Planned, not started

## Display update (`docs/rencana-perbaikan-tampilan.md`, revised 11 October 2026)

Owner decisions: phases in order 0 → A → B → C → E → F, one branch and one PR per phase, each cut from `main` after the previous PR is merged. Phase D (character frame animation) is done (PR #83).

- [x] Plan revised against the current code (PR #84)
- [ ] Phase 0 — bug fixes: music on challenge/region screens, lost and doubled password flash, dead size rules (`tampilan-0-perbaikan`)
- [ ] Phase A — app icons, zoom and animation preview, placeholder, story slide backgrounds, `bg.auth` (`tampilan-a-gambar-admin`)
- [ ] Phase B — avatars (`tampilan-b-avatar`)
- [ ] Phase C — city pins, Kenali lanterns, city loading backgrounds (`tampilan-c-kota`)
- [x] Phase E — game-style toasts (`tampilan-e-toast`)
- [ ] Phase F — touch-friendly play (`tampilan-f-sentuh`)

## Asset package (`docs/rencana-paket-aset.md`, stages in §10)

Owner decisions in its §12 (9 October 2026): full download before Mulai, admin on/off switch. Dok 3 is merged, so the plan's "before/after Dok 3" limits no longer apply; only the stage dependencies below do. Each stage is one parallel session and one PR into `main`. This file is kept by the coordinating session; stage sessions do not edit it.

- **Wave 1** (started 9 October 2026, run in parallel)
  - [ ] Stage 0 — baseline timing, `tools/qa/nav-timing.mjs` (`paket-0-ukur-dasar`, PR #78)
  - [ ] Stage 1 — package builder on the server, `gelita:paket:*`, `/api/paket/aktif` (`paket-1-pembangun`, PR #75)
  - [ ] Stage 1b — narration re-encoded at 48 kbps mono; merge only after the owner's listening test (`paket-1b-narasi`, PR #74)
  - [ ] Stage 4 — view transitions, image decode, short curtain (`paket-4-transisi`, PR #77)
  - [ ] Stage 5 — research data integrity §7.1 and §7.3 (`paket-5-integritas`, PR #76)
- **Wave 2** (after Stage 1 is merged)
  - [ ] Stage 2 — package service worker and downloader
  - [ ] Stage 6 — Rilis → Paket aset panel with the on/off switch, study package lock (§7.2)
- **Wave 3** (after Stage 2 is merged)
  - [ ] Stage 3 — "Siapkan GELITA" download screen and teacher's Periksa paket screen
  - [ ] Re-run Stage 0 timing; owner decides on Stage 7 (P1)

## Active branches

Add a row (branch, owner / scope, status) when a session starts a branch; remove it when its PR is merged.

| Branch | Owner / scope | Status |
|---|---|---|
| `sky/youthful-turing-l3pvlp` | Asset catalogue decision (team ZIP), `ImageSlots`, slot detail page `/admin/media/{id}`, challenge images in Kelengkapan | PR open |
| `sky/confident-noether-id40gg` | Display fixes on `/masuk` (crest under the HUD logo), `/daftar/3` (fields hidden below the tips box), registration error labels, and `/daftar` in the dark ornate design | PR open |
| `sky/gracious-archimedes-injcc3` | No session cookie on the no-login API endpoints (`LocaleFilter`) | PR open |
| `sky/zealous-ritchie-653apc` | Asset package: wave plan and coordination (this file) | PR open |
| `paket-0-ukur-dasar` | Asset package Stage 0: baseline timing | PR open |
| `paket-1-pembangun` | Asset package Stage 1: package builder | PR open |
| `paket-1b-narasi` | Asset package Stage 1b: narration 48 kbps | PR open |
| `paket-4-transisi` | Asset package Stage 4: transitions, decode, curtain | PR open |
| `paket-5-integritas` | Asset package Stage 5: research data integrity | PR open |
| `tampilan-0-perbaikan` | Display update Phase 0: bug fixes | PR open |
| `tampilan-e-toast` | Display update Phase E: game-style toasts | PR open |
