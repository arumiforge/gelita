# Progress

The single checklist of what is done and what is open. Decisions and their reasons go in `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md` (and, for the research revision, `docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md`); this file only tracks state. Each session updates only its own rows, and ticks an item only once its PR is merged into `main`. Finished work is summed up in one line per area under *Done*; the details live in the PRs, the git history, and the decision log.

Last updated: 25 October 2026.

## Research revision: 10 analytics (owner, 23 October 2026)

Documentation only, in `docs/revisi-10-analitik/` (Indonesian, at the owner's request): no game or system change yet. The owner finishes the revision documents elsewhere; the complete implementation document comes after that, and implementation only after it is approved. Everything in the game may be rebuilt as part of it (owner, 23 October 2026).

- [x] Revision documents: main document with the 10 analytics, constructs, admin/researcher split, log cleanup, token, participant code, and the 15 later system changes (`01`), field sheets L0–L7 (`02`), codes and meanings (`03`), decision record (`04`), three diagrams in `gambar/` (PR #214). The owner continues the revision separately from here
- [ ] Owner and team settle the open questions in `01_REVISI_10_ANALITIK.md` → *Pertanyaan yang masih terbuka* (constructs with three dimensions, token, participant code `S03-A07`, `guru` role)
  - [x] Owner interview on the remaining open questions, five rounds (`04`, 25 October 2026). Round 1 decided: strict stage order (no implementation before gate G2), light session token that also opens one region per meeting, game fully closed outside sessions until the study ends, participant code `S03-A07`. Round 2 decided: Skenario Digital uses 3 tries with the first action and reason scored, P1 enters booklets from L2 in the researcher panel, research sessions refuse small screens, retakes open only after the meeting's region is done. Round 3 decided: `guru` role removed entirely, P1 resets forgotten passwords by participant code, full names stay admin-only, `sikap` becomes a companion attitude scale in Balai Refleksi (new instrument, system change 21). Round 4 decided: three researchers per class, pre/post tests with AKM-based reading and own D2/D3 parts, OSF pre-registration at gate G4, media expert in two steps (mock-ups in stage 2, built screens before the pilot). Round 5 decided: Claude drafts the 5th-challenge story links and narration (same voice actors), the attitude scale is filled once at P5, the data dictionary is the next document. Until the owner names them, the seven validators use pseudonyms (`03` → *Peran*); the ethics committee is the pseudonym *Lembaga Komite Etik*, with submission in weeks 2–4 of November 2026 and approval due by late September to the first week of November 2027. Gate G2 still waits for that approval, so stage 3 (implementation document and system changes) may only start around then. Pre-stage-1 check: blind labelling happens once, by two experts in stage 2 (κ moves from gate G1 to G2); stale D2 topic and error-code text in `01` aligned with `06`
  - [x] Owner decided the item and dimension review (`05_TELAAH_BUTIR_DAN_DIMENSI.md`) by approving the blueprint (25 October 2026): D3 in Cari Objek round 2 *Periksa Unggahan*, round 1 as the Pengetahuan Budaya Kedu scale, D2 actions scored 0–1–2, BUD-* codes; item fixes carried into the draft bank
  - [x] Test blueprint for the new bank (`06_KISI_KISI_BANK_SOAL.md`), approved in full by the owner (25 October 2026): 61 digital-literacy items per student, four booklets, between-variant anchors, 300 analysable students, five meetings (PR #219, #221)
  - [x] Draft of the new question bank (`08_DRAF_BANK_SOAL.md`): 116 digital-literacy items in variants a/b, 6 anchors, 12 PBK items, all `needs_verification`; fictional names checked, posts on the fictional app *Kedugram* (PR #220, #221)
  - [x] AI-assisted preliminary review T6 of the draft bank (`09_TELAAH_PENDAHULUAN_BANK_SOAL.md`), fixes applied; residual findings R1–R4 approved by the owner (PR #222, #225)
  - [ ] Owner writes the English translation of the new bank (after validation)
  - [ ] Someone on the team searches every uploader handle in `08` on Instagram and TikTok and renames any that belong to a real, active account
  - [ ] Expert validation of the new bank: blind labelling (B.5a) and relevance ratings (B.5b), with the R1–R4 questions from `08`/`09`
  - [x] Owner accepted the expert-instrument review (`07_TELAAH_INSTRUMEN_VALIDASI.md`) and added the media expert (25 October 2026)
  - [ ] Rewrite the instruments once as r10 v2.1 and media v1.1, after the blueprint and the D3 decision
  - [x] AI-assisted media pre-review on a local test install (`10_TELAAH_PENDAHULUAN_MEDIA.md`)
  - [x] Owner approved the media decisions (25 October 2026): no phones for data collection, re-laid Cari Objek tap areas with an automatic overlap check, timer hidden during the study, narration rule (D1 never read aloud). Added as system changes 16–19 in `01` and to the L0 checklist
- [x] Data dictionary `11_KAMUS_DATA.md` (work item 2 in `01`), approved by the owner as `kamus-1.0` (26 October 2026); the remaining stage-1 documents are listed in `00_INDEKS.md`
  - [x] Draft written: evidence map (diagram updated), scored items and first attempt, missing data and analysis flags, frozen definitions of the 10 analytics, item and participant variables, field-sheet join rules, data requirements for stage 3; `03` and the raw-data example in `01` aligned
  - [x] Owner decided the four questions (26 October 2026): other tap areas in *Periksa Unggahan* get the clue type's BUD code (`08` updated); idle gaps cut only above a provisional 3-minute threshold; analysable = consent, not pilot, first try on at least 80% of the 61 items; rapid guesses kept as given in the main analysis with a provisional threshold under 3 seconds. Both thresholds are study settings, re-set after the pilot
- [ ] Complete implementation document, after the revision documents are final and gate G2 (expert validation and ethics approval) is passed
- [ ] Implementation of the system changes listed in `01` (21 so far), one short-lived branch each, after the implementation document is approved

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
| `pbb/eloquent-sagan-j40sg8` | Pre-stage-1 check of the research revision docs | PR open |
