# Progress

The single checklist of what is done and what is open. Decisions and their reasons go in `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md` (and, for the research revision, `docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md`); this file only tracks state. Each session updates only its own rows, and ticks an item only once its PR is merged into `main`. Finished work is summed up in one line per area under *Done*; the details live in the PRs, the git history, and the decision log.

Last updated: 23 October 2026.

## Research revision: 10 analytics (owner, 23 October 2026)

Documentation only, in `docs/revisi-10-analitik/` (Indonesian, at the owner's request): no game or system change yet. The owner finishes the revision documents elsewhere; the complete implementation document comes after that, and implementation only after it is approved. Everything in the game may be rebuilt as part of it (owner, 23 October 2026).

- [x] Revision documents: main document with the 10 analytics, constructs, admin/researcher split, log cleanup, token, participant code, and the 15 later system changes (`01`), field sheets L0–L7 (`02`), codes and meanings (`03`), decision record (`04`), three diagrams in `gambar/` (PR #214). The owner continues the revision separately from here
- [ ] Owner and team settle the open questions in `01_REVISI_10_ANALITIK.md` → *Pertanyaan yang masih terbuka* (constructs with three dimensions, token, participant code `S03-A07`, `guru` role)
  - [ ] Owner decides the rest of the item and dimension review (`05_TELAAH_BUTIR_DAN_DIMENSI.md`): where D3 lives (Cari Objek in two rounds), D2 scoring, the adjusted decision text, and the item fixes. Already decided on 25 October 2026: two variants (a/b), between-item model, D1 on reading literacy with D1a/D1b, more than 29 D1 items allowed, digital-genre passages
  - [ ] Test blueprint for the new bank (`06_KISI_KISI_BANK_SOAL.md`, branch `kisi-kisi-bank-soal`, separate session)
  - [x] Owner accepted the expert-instrument review (`07_TELAAH_INSTRUMEN_VALIDASI.md`) and added the media expert (25 October 2026)
  - [ ] Rewrite the instruments once as r10 v2.1 and media v1.1, after the blueprint and the D3 decision
  - [ ] AI-assisted media pre-review on a local test install (branch `pbb/eloquent-ritchie-wz20bn`)
- [ ] Complete implementation document, after the revision documents are final
- [ ] Implementation of the 15 system changes, one short-lived branch each, after the implementation document is approved

## Before the research starts

A server is always set up as a fresh install (`docs/08_DEPLOYMENT.md` → *Instalasi baru*), which runs the bank import, the story data, the catalogue import, and the package build. On a server updated in place instead, `docs/08` lists the commands (`gelita:katalog:impor`, `gelita:story:sync`, **Bangun ulang paket**).

**Content and art**

- [ ] Record the voice-over narration (103 lines per language today, plus the lines the Skenario Digital challenge will add) from the script PDF in **Rekaman narasi**, upload it, listen, and approve it. On a server that still has old TTS takes, press **Batalkan semua rekaman** for each language first (decision log, 17 October 2026)
- [ ] The researcher checks every `needs_verification` item of the question bank and the facts in `gelita-katalog/update/telaah-draf-1-naskah-bank-soal.md` §6
- [ ] The researcher checks the Susun Gambar pictures against the reference photos and has two people assemble each puzzle
- [ ] Art for the region story slides `bg.cerita-{kenal,dialog,tuntas}-{wilayah}-NN` (catalogue team); until it exists every slide shows the region background
- [ ] Replace or approve the remaining interim art: the AI Pustaka illustrations (owner: fine for now, replaced in the panel later)

**Owner checks on the class devices**

- [ ] `docs/perombakan-kelas5/device-test-checklist.md`, including the screen-reader pass (Dok 3 §12.3)
- [ ] Package screens `/siapkan` and `/periksa-paket` on an iPad (Safari tab and Home Screen), a Chromebook guest session, and a real private window
- [ ] Frame rate of the screen effects and the start-page entrance on a class tablet
- [ ] Background music sits well under the recorded voices
- [ ] Load test (or Ringkasan and `fpm-status` in a full class) on the real server once the school hardware is known; re-run the Stage 0 timing, then the owner decides on asset-package Stage 7 (P1)

**Open owner decisions (asset package, Wave 2)**

- [ ] Deploy scripts rebuilding the package while the switch is on (`docs/rencana-paket-aset.md` §6.3); running-study lock compared with the active release's package (§7.2); preview banner in the game; pinning a running session's URLs to its own package (S8)

**Release**

- [ ] Migration `2026-01-01-003300` (study package lock) checked on MariaDB/MySQL; so far it ran on SQLite only
- [ ] Research-ready checklist green, study status set to "Berjalan"
- [ ] Smoke test, then `gelita:reset` (by the owner, never by Claude on production)

## Done

- **Kelas 5 overhaul**, Dok 1–3: data foundation and reset, challenges and question bank, 16:9 stage and admin panel, raw data and exports, audits and fixes (PR #41–#63). The one-off upgrade script was dropped: a release is a fresh install.
- **Display update** phases 0, A–F and the audit follow-ups (PR #83–#93).
- **Asset package** stages 0–6 and the Stage 3 screens (PR #74–#78, #109–#110, #139). The 48 kbps listening test was dropped with the TTS takes.
- **Story script and question bank** draft 2, puzzle variants a–j, `gelita:story:sync` (PR #115–#116).
- **Asynchronous logging** Phases 1 and 2 and the load test; Phase 3 not built by owner decision (PR #119–#127).
- **Page and screen redesigns** from the catalogue mockups and the owner's pictures, 18–22 October 2026: result screen, Peta Kedu island map, story screens, Kenali, challenge screens, region map, account pages and gate, cinematic start, round gold buttons, framed dialogs and gold pills (PR #128–#213).
- **Research revision** documents (PR #214–#215).

## Active branches

Add a row (branch, owner / scope, status) when a session starts a branch; remove it when its PR is merged.

| Branch | Owner / scope | Status |
|---|---|---|
