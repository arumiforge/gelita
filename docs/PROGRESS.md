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
  - [ ] Browser tests for PR #42, `check_no` fix for checks from a stale tab, `gelita:simulasi` docs (PR #50, branch `qa-simulasi`)
  - [ ] §11 documentation: only the sections Dok 3 changed are updated
  - [ ] §12.3 manual tests on a real laptop, tablet, and phone; keyboard and screen reader pass; open CSV/XLSX in LibreOffice or R
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
| `qa-simulasi` | browser tests for PR #42, `check_no`, `gelita:simulasi` docs | PR #50 open |
