# Progress

The single checklist of what is done and what is open. Decisions and their reasons go in `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md` (and, for the research revision, `docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md`); this file only tracks state. Each session updates only its own rows, and ticks an item only once its PR is merged into `main`. Finished work is summed up in one line per area under *Done*; the details live in the PRs, the git history, and the decision log.

Last updated: 26 October 2026.

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
- [x] Story links and narration for the fifth challenge (`12_CERITA_PENGHUBUNG_DAN_NARASI.md`, work item 7 in `01`), approved by the owner with all proposals (26 October 2026): the link scene plays once when challenge 5 is first opened, Kenali opens only after its region is done during the study, the Wonosobo chain message becomes an earthquake-prediction hoax, only situations are read aloud, and the post-test comes before the closing story in P5 (`06` and `02` aligned)
  - [x] Draft written: *Kabut di Layar* link scene per region (new context `scenario_open`), mission cards, 31 rewritten old lines (15 shards, answer leaks found by checking the script against the draft bank), recording list (about 187 per language) and voice direction
  - [ ] Fix the leaks outside the story found by the same check (bank side, before expert validation)
    - [x] Susun Gambar captions: all 30 checked against `08` (section *Susun Gambar selama studi*). 22 leak or cue a D1 key, D3 key, or PBK clue, including 7 of the 9 first-attempt variants a–c; d–j appear only on replays, which open after the region is done, so they are harmless during the study
    - [x] Owner decided (26 October 2026): during the study the first attempt is drawn at random from two leak-free variants per region (Pasar Papringan + a new Wayang Kedu picture, Candi Mendut + Candi Pawon, Kompleks Candi Arjuna + Tari Topeng Lengger); `06` (system change 4, code table) and `01` (system change 20) updated
    - [x] Owner confirmed (26 October 2026): Wayang Kedu Temanggungan's origin (caption written in `08`, World Heritage line left out until the decree is found) and Topeng Lengger's origin in Wonosobo; the Candi Pawon picture gets replaced
    - [ ] Catalogue team makes the new Wayang Kedu picture and the replacement Candi Pawon picture (briefs in `08`), then both are re-checked against `08`
    - [ ] Researcher checks the facts of Papringan, Pawon, and Arjuna, and Lengger's 2020 listing year
    - [x] Outdated `petunjuk-*` narration mapping: moved to stage 3 (`12`, stage-3 list item 3), because the current mapping is right for the bank installed today
- [x] Research and development model decided by the owner (26 October 2026): Plomp's educational design research (2013), development-study type, with ECD inside it; Lynn's I-CVI rule kept (`04`, `07`). The supporting brief, with every reference checked against its source, is in `dokumen-pendukung-peneliti/` (reading for researchers and the team, not an implementation document). Reference errors found by that check fixed in `01`, `05`, `06`, `07`
  - [ ] Owner decides the brief's open proposals: pre/post difference test reported as exploratory, practicality measured from logs and L1/L3/L4, preliminary research written up as its own phase, prototypes numbered I–III
- [ ] Pre/post test, forms A and B, and the digital-access questionnaire (work items 9 and 10 in `01`): drafts in `13`–`16`, written in parallel by three subagents on one blueprint
  - [x] Blueprint `13`: three parts matching the dimensions (D1 reading 16 items from 4 texts, D2 8 scenarios, D3 8 items from 2 scenes; 32 items, max 40 points), item-by-item parallel forms, checkerboard form assignment, paper administration, random-groups Rasch equating without shared items, validity and change analyses
  - [x] Item drafts: reading part adapted from official samples in the *Framework AKM* (`14`), D2 and D3 parts (`15`), questionnaire (`16`), all `needs_verification`
  - [x] Consistency check of `13` against `14`–`16` (26 October 2026). `13` now matches the drafts (sources, answer formats, five sample items, `error_codes` as a list, no repeats inside the test, open issues listed). Test time recounted from every word students read: about 67 minutes with instructions, not 47, so question 1 was rewritten. `15`: the form-A D3 scene moved from sasirangan to karawo and both G1 captions changed, because `14`'s M3 texts gave away their answers; four commenter names shared with `14` replaced. `14`: matching item B04 re-lettered so no pair shares a key letter with A04
  - [x] Owner decided the four questions in `13` (26 October 2026, `04`): meeting length follows the test's needs, not school periods (the study may use all periods and after-school time), with generous hidden caps per part (about 40 and 35 minutes); the test and the questionnaire run in a GELITA module with a plain Bootstrap look, not a game look (system change 22); topics are Nusantara culture outside Kedu; 2–3 online-risk items are answered privately on screen at P5 after the post-test; the questionnaire follows the pre-test on screen, not read aloud
  - [x] Decisions carried into `13`–`16` (26 October 2026): module section and timing in `13`; on-screen instructions in `14` and `15`; questionnaire on screen, P5 risk items with ethics conditions, and automatic data in `16`; system change 22 (test and questionnaire module, Bootstrap kept locally) in `01`
  - [x] Owner follow-up decisions (26 October 2026, `04`): test and questionnaire variables enter the data dictionary at `kamus-1.1` after the pilot (`kamus-1.0` untouched); region meetings P2–P4 also drop the 2-JP limit, with about 90 minutes of play and leftovers still in the P5 make-up; the P5 online-risk items get a separate parental consent tick
  - [x] Shared docs aligned (26 October 2026): `02` L6 becomes a run log (answers live in the module) and L7 gets the separate risk-item consent; `06` meeting times (P1/P5 by need, about 90 minutes of play in P2–P4); `03` test and questionnaire codes; `07` validation package (test items, A/B equivalence, module mock-ups, questionnaire review). `11` waits for `kamus-1.1`
  - [ ] After the decisions: align `02` (L6, done with work item 11), `06` (P1/P5), `11` (test and questionnaire variables), `03` (codes), and the r10 v2.1 instrument (`07`)
- [ ] Companion attitude scale *sikap menjaga warisan* for Balai Refleksi (work item 19 in `01`): draft `17_SKALA_SIKAP.md`
  - [x] Draft written: construct kept apart from PBK and D3, four indicators SK1–SK4, 12 items (4 reverse-keyed, no "tidak"/"bukan"), scoring, P5 administration, variables for `kamus-1.1`, validation plan, ethics, knock-on changes; codes added to `03`
  - [x] Owner decided two interview rounds (26 October 2026, `04`): 4-point words-and-dots format without a midpoint or faces, on screen only, one statement per screen with back allowed, scale before the existing feedback form (feedback all optional during the study), raters Ahli Budaya Kedu, Ahli Pengukuran, Ahli Bahasa Anak 1 and 2 (Lynn's I-CVI), make-up within 7 days
  - [x] Owner settled the scale's open questions (26 October 2026, `04`): the regional-language item stays, `skp-12` names merti desa and kirab, the feedback star rating counts as practicality data, students unfinished at the end of P5 keep their data as is (scale missing as `belum_tuntas`), and a moderate correlation with PBK and D3 is expected (exact band at G4)
  - [ ] Expert validation and child readability check (stage 2); knock-on changes in `01` (system change 21), `02`, `06`, `07`, `11`, `12`, `13`, and `18` listed in `17` → *Akibat ke dokumen lain*
- [ ] Complete implementation document, after the revision documents are final and gate G2 (expert validation and ethics approval) is passed
- [ ] Implementation of the system changes listed in `01` (22 so far), one short-lived branch each, after the implementation document is approved

## Before the research starts

A server is always set up as a fresh install (`docs/08_DEPLOYMENT.md` → *Instalasi baru*), which runs the bank import, the story data, the catalogue import, and the package build. On a server updated in place instead, `docs/08` lists the commands (`gelita:katalog:impor`, `gelita:story:sync`, **Bangun ulang paket**).

**Content and art**

- [ ] Record the voice-over narration (103 lines per language today; about 187 after the research revision, see `docs/revisi-10-analitik/12_CERITA_PENGHUBUNG_DAN_NARASI.md`) from the script PDF in **Rekaman narasi**, upload it, listen, and approve it. On a server that still has old TTS takes, press **Batalkan semua rekaman** for each language first (decision log, 17 October 2026)
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
| `r10-skala-sikap` | Claude session / attitude scale, work item 19 (`17_SKALA_SIKAP.md`) | Draft done, owner decisions recorded; PR open |
