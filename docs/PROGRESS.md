# Progress

The single checklist of what is done and what is open. Decisions and their reasons go in `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`; this file only tracks state. Each session updates only its own rows, and ticks an item only once its PR is merged into `main`.

Last updated: 9 October 2026.

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
  - [ ] Code issues found in the §11 audit (decision log, "Dok 3 — documentation (§11)" items 1–9): branches below
  - [ ] Owner decision: should the research-ready gate also apply to the study that `gelita:reset` seeds as active?
  - [ ] §12.3/§12.4 tests and done criteria: keyboard pass (`tools/qa/a11y-check.mjs`) and exports in LibreOffice, pandas, and R done on `dok3-s12-uji-manual`; stage and admin re-run waits for the audit-fix branches
  - [ ] §12.3 owner pass on real devices and a screen reader: `docs/perombakan-kelas5/device-test-checklist.md`
  - [~] §13 one-off upgrade script: **dropped**. A release is a fresh install per `docs/08_DEPLOYMENT.md`

## Before the research starts (after install, done in the panel)

- [ ] Approve draft narration
- [ ] Replace the 45 sample (mockup) images
- [ ] Research-ready checklist green, study status set to "Berjalan"
- [ ] Smoke test, then `gelita:reset` (by the owner, never by Claude on production)

## Planned, not started

- [ ] Asset package (download once, instant screen and language switch): `docs/rencana-paket-aset.md`. Waiting on the owner decisions in its §12.

## Active branches

| Branch | Owner / scope | Status |
|---|---|---|
| `fix-admin-copy` | §11 audit items 5–6: "Gambar & mockup" in messages, admin layout lang keys, auth skip link | in progress |
| `fix-game-details` | §11 audit items 7–9: Mbah Kedu pose, `statement_image` level code, `stage.css` comment | in progress |
| `dok3-s12-uji-manual` | Dok 3 §12.3 container tests, §12.4 evidence, device checklist | in progress |
