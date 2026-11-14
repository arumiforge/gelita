# Progress

The single checklist of what is done and what is open. Decisions and their reasons go in `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md` (and, for the research revision, `docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md`); this file only tracks state. Each session updates only its own rows, and ticks an item only once its PR is merged into `main`. Finished work is summed up in one line per area under *Done*; the details live in the PRs, the git history, and the decision log.

Last updated: 14 November 2026.

## Research revision: 10 analytics (owner, 23 October 2026)

Documents in `docs/revisi-10-analitik/` (Indonesian, at the owner's request); the system changes of parts `27`–`35` are built and merged (code waves C–F, by 9 November 2026), behind study switches that are off by default. Since 1 November 2026 the system is built now and is not used with children before ethics approval; the researcher handles validation and ethics (`04`). Still in force: G3 is judged after G2, the version is locked at G4, the validator test install stays on the commit frozen under `20` prerequisite 4, and the destructive migrations (`003400`, `003600`) reach production only on the owner's call. Finished rows are under *Done*; only rows with open work remain here.

- [x] Story links and narration for the fifth challenge (`12_CERITA_PENGHUBUNG_DAN_NARASI.md`, work item 7 in `01`), approved by the owner (26 October 2026) and brought up to date (1 November 2026); one bank-side leftover:
  - [ ] Fix the leaks outside the story found by checking the script against the draft bank (bank side, before expert validation). Captions and the variant rule are done (owner, 26 October 2026: during the study the first Susun Gambar attempt is drawn at random from two leak-free variants per region, Pasar Papringan + a new Wayang Kedu picture, Candi Mendut + Candi Pawon, Kompleks Candi Arjuna + Tari Topeng Lengger; `08` → *Susun Gambar selama studi*); still open: the two new pictures (Wayang Kedu Temanggungan, Candi Pawon), which may follow after the validation packet is sent (`20`)
    - [ ] Catalogue team makes the new Wayang Kedu picture and the replacement Candi Pawon picture, then both are re-checked against `08`. Paste-ready prompts `TMG-P11` and `MGL-P05R` in `37_PROMPT_GAMBAR_SUSUN_DAN_CERITA.md` (4 November 2026)
    - [ ] Story slide backgrounds for the region scenes (`bg.cerita-{kenal,dialog,tuntas,layar}-{wilayah}-NN`, 92 slots from the `12` script): 29 paste-ready prompts in `37` (4 November 2026); the owner or the catalogue team generates them and adds them to the catalogue; `layar-*` and `tuntas-*-05` install after the `12` script and part 6
- [ ] Pre/post test, forms A and B, and the digital-access questionnaire (work items 9 and 10 in `01`): drafts in `13`–`16`, written in parallel by three subagents on one blueprint
  - Done: blueprint `13`, item drafts `14`–`16` (all `needs_verification`), the consistency check, the owner's decisions of 26 October 2026 (`04`) carried into `13`–`16` and the shared documents, and the final field sheets L0–L7 (`02`). The rows never said what keeps this parent open. Named elsewhere: the test topics in `14`/`15` still need a cultural fact-check (outside `25`'s scope), `16` no. 9 waits for the researcher, and the instrument content stays `needs_verification` (`34`)
- [x] Stage-2 documents (expert validation and ethics, `20`–`26`): gate G2 passed on 4 November 2026 (`04`, `asal: peneliti`). The validation results and the committee's letter are not in the repo yet
  - [ ] Validation-package prerequisites 4, 8, 9, 10 (`20` → *Cara memenuhi prasyarat yang tersisa*, 27 October 2026, PR #278 merged 30 October 2026, `04`): willingness message and off-repo mapping by the lead researcher (no. 9); test install on a campus lab PC (Jalur W) behind a campus HTTPS subdomain with `uji.*` accounts (no. 8; `03` → *Akun uji validator*); honorarium facts off-repo (no. 10); ten-step freeze (no. 4). Still open: campus subdomain, validators' answers, the lead researcher's honorarium confirmation, then the freeze, when the validator files are rebuilt (the cultural fact-check it waited for is merged, PR #279, and carried into `20` by PR #280)
- [x] Implementation of the 24 system changes listed in `01`, parts `27`–`35`, in code waves C–F (PR #332–#351, merged by 9 November 2026); leftovers:
  - [ ] Owner (wave E, part 6): generate the three five-stop region maps, the Skenario Digital card, and the three bridge backgrounds from `38`; then measure the five stops per map into `CatalogImporter::MAP_STOPS` (challenge 5 stays at the interim 60 %, 22 % until then)
  - [x] Manual checks on a MariaDB dev install (wave E). Server-side parts on MariaDB 10.11 in the cloud container on 9 November 2026 (29 §16.3 no. 1 and 3–5, 30 §19.3 no. 7); the rest on 14 November 2026 with Playwright, branch `r10-cek-manual-gelombang-e` (`04`): 29 §16.3 no. 2, 30 §19.3 no. 1–6 and 8, 32 §15.3 no. 1–6 all passed. Three bugs fixed on that branch: Skenario Digital result card, instruction band and its narration button, `backup.sh --ganti` with an unplugged second-location drive
  - [ ] Owner (left by the wave E manual checks, details in `04`, 14 November 2026): `backup.ps1 -Ganti` on a real Windows machine (ran only on PowerShell 7 on Linux); Skenario Digital on a real tablet and Chromebook (32 §15.3 no. 5, §15.4); the Isi Rumpang instruction band still cuts its second line (layout decision); the open points listed in `04` for the child's copy, the 7305-day default, and narration play counts
  - [ ] Still open from the manual checks of wave F (need real devices): touch tablet and school devices, axe on Gerbang/Profil, two students on one offline device, package mode with the service worker, timing on real volumes

Leftovers named inside finished rows (not checklist items; details in `04`):

- The validation scores, κ, and the revision matrix in `20` are not in the repo yet. Until they arrive `08` keeps its status, the items the validation revises are not re-translated (`36`), and the knock-on changes listed in `17` → *Akibat ke dokumen lain* (`01` system change 21, `02`, `06`, `07`, `11`, `12`, `13`, `18`) wait for the validators' notes.
- After KG3a/KG3b the thresholds, the `game_time_group` bounds, the test rapid-guess threshold, and the reason order are re-set as `kamus-1.2` (`11`).
- `25` → *Akibat ke dokumen lain* no. 6 (*Tentang GELITA* sentence, after the cultural observer answers LNT-24) and no. 7 (Papringan market days re-checked before the G4 lock). The optional `kenal-temanggung-02` wording from `25` is the owner's call. The Wayang Kedu caption in `08` leaves out the World Heritage line until the decree is found.
- `22` is handled by the researcher directly since 3 November 2026 (fill-in groups A–F stay off-repo); the repo copy is frozen at the sent version and is no longer the master copy. `23`, `24`, and `26` still are; re-send `23` with its next change.

## AI studio and multi-session work (owner, 14 November 2026)

Folders where other AI tools work for GELITA (`ai-studio/README.md`); Claude Code sessions install the results. How sessions on several Claude accounts hand work over: `docs/cloud-sessions.md`. Built in PR #354 by two sessions (full suite green, 1241 tests); decision log *AI studio folders, narration list command, multi-account hand-over*.

- [x] `ai-studio/chatgpt-images/`: instructions (`AGENTS.md`), work list of 35 pictures from `37` and `38` (`TASKS.md`), `out/`, `inbox/`; root `AGENTS.md` sends non-Claude agents to their folder
- [x] `ai-studio/elevenlabs-voice/`: instructions (`README.md`), `voices.md` to fill in, `lines.csv` (187 lines, two languages) written by the new command `php spark gelita:narration:list --csv=… --dir=…` (test in `NarrationImporterTest`)
- [x] The owner's narration decision (voice actors first, ElevenLabs the only synthetic backup) recorded in `04`, the decision log, `docs/audio.md`, and `README.md` → *Audio bawaan*
- [x] `CLAUDE.md` (quick map, spark command, hand-over rule), `README.md` (folder, command, document rows), glossary rows
- [x] Multi-account guide `docs/cloud-sessions.md` and repo skill `.claude/skills/continue-work/`
- [ ] Owner: on each Claude account, check the environment against `docs/cloud-sessions.md` §2 (setup script = current `tools/dev/cloud-setup.sh`, the two variables, GitHub connected)
- [ ] Owner: finish `ai-studio/elevenlabs-voice/voices.md` (filled in on 14 November 2026: model v4 and the Indonesian voice of Mbah Kedu; still empty: Narator and Jaka in both languages, Mbah Kedu in English), then make and accept the first takes; make and accept pictures from `ai-studio/chatgpt-images/TASKS.md` (the same pictures as the `37`/`38` rows under *Research revision* above)
- [x] Installer for ElevenLabs recordings: `php spark gelita:narration:install` copies `out/{id,en}/` to `public/assets/audio/narasi/` and writes `produksi.json` (sha256 + `voice_profile` from `voices.md`), so the import records them as `tts`; real recordings are kept unless `--timpa` (decision log *Installer for the ElevenLabs backup narration*)
- [ ] Install the first accepted takes with that command on a development install, listen, then on the server with the owner. Before such takes are used with students: new `04` entry and the researcher is told
- [ ] Install accepted pictures (`ai-studio/README.md` → *For the Claude Code session that installs*), one branch per batch

## Before the research starts

A server is always set up as a fresh install (`docs/08_DEPLOYMENT.md` → *Instalasi baru*), which runs the bank import, the story data, the catalogue import, and the package build. On a server updated in place instead, `docs/08` lists the commands (`gelita:katalog:impor`, `gelita:story:sync`, **Bangun ulang paket**).

**Content and art**

- [ ] Record the voice-over narration (103 lines per language today; about 187 after the research revision, see `docs/revisi-10-analitik/12_CERITA_PENGHUBUNG_DAN_NARASI.md`) from the script PDF in **Rekaman narasi**, upload it, listen, and approve it. On a server that still has old TTS takes, press **Batalkan semua rekaman** for each language first (decision log, 17 October 2026)
- [ ] The researcher checks every `needs_verification` item of the question bank and the facts in `gelita-katalog/update/telaah-draf-1-naskah-bank-soal.md` §6
- [ ] The researcher checks the Susun Gambar pictures against the reference photos and has two people assemble each puzzle
- [ ] Art for the region story slides `bg.cerita-{kenal,dialog,tuntas}-{wilayah}-NN` (catalogue team); until it exists every slide shows the region background. Prompts in `docs/revisi-10-analitik/37_PROMPT_GAMBAR_SUSUN_DAN_CERITA.md` (4 November 2026). The bridge-story backgrounds `bg.cerita-layar-*` have interim pictures and their prompts are also in `38` (8 November 2026)
- [ ] Replace or approve the remaining interim art: the AI Pustaka illustrations (owner: fine for now, replaced in the panel later)

**Owner checks on the class devices**

- [ ] `docs/perombakan-kelas5/device-test-checklist.md`, including the screen-reader pass (Dok 3 §12.3)
- [ ] Package screens `/siapkan` and `/periksa-paket` on an iPad (Safari tab and Home Screen), a Chromebook guest session, and a real private window
- [ ] Frame rate of the screen effects and the start-page entrance on a class tablet
- [ ] Background music sits well under the recorded voices
- [ ] Load test (or Ringkasan and `fpm-status` in a full class) on the real server once the school hardware is known; re-run the Stage 0 timing, then the owner decides on asset-package Stage 7 (P1)

**Open owner decisions (asset package, Wave 2)**

None open: the owner decided on 9 November 2026 (`04`) to build the Wave 2 leftovers, and they are built (see *Done*). Still to check on a real server and device: a deploy with package mode on, and a mid-session publish.

**Release**

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
- **Research revision, documents and decisions** (25 October–2 November 2026): five owner interview rounds and the open questions of `01` closed; item and dimension review `05`; data dictionary `11` (`kamus-1.0`, then `kamus-1.1`); story links and narration `12`; Bloom label review `19`; 26 mock-ups of the new screens `18`; final field sheets L0–L8 in `02`; research model Plomp, replaced by the researcher's ADDIE with three trials KG3a/KG3b and the main collection (1 November 2026); developer, researcher, and expert roles with origin labels; print-ready PDFs of every document through `cetak.mjs` (PR #247, #249, #253, #259–#260, #293, #296–#299).
- **Research revision, question bank, blueprint, translation**: blueprint `06` approved in full (25 October 2026); draft bank `08` (116 digital-literacy items in variants a/b, 6 anchors, 12 PBK items); AI-assisted preliminary review `09`; `wnb-3-b02` rewrite; fictional handles searched by the owner; the four part-5 content warnings fixed; English translation `36` approved (3 November 2026) and carried into the bank data and workbook (PR #219–#222, #225, #345–#346).
- **Research revision, expert instruments and validation package**: instrument review `07`; subject-expert and cultural-observer instruments r10 v2.1 and media v1.1 approved (27 October 2026) for eleven validators; media pre-review `10`; validation package `20`; validator files `21` with their generator; cultural fact-check `25` (135 claims); researcher handover brief up to v1.5; expert validation confirmed done by the researcher (4 November 2026) (PR #263, #267–#268, #279–#280, #291, #293, #298).
- **Research revision, test, questionnaire, and attitude scale documents**: blueprint `13` and item drafts `14`–`16` with the owner's decisions of 26 October 2026; one developer level per D2/D3 test item; attitude scale `17`, `tervalidasi ahli` since 4 November 2026; the 24 test stimulus pictures converted to WebP as `instr.*` (PR #253, #303).
- **Research revision, ethics documents**: application `22` to the BRIN social-humanities committee, letters `23`, readability-test protocol `24`, data-protection impact assessment `26`; aligned with KEPPKN 2021, PP 33/2026 (articles checked against the official copy), and the BRIN guide; PDFs handed to the researcher; ethics approval confirmed by the researcher, gate G2 passed (4 November 2026) (PR #264–#266, #268, #272–#277, #305, #311–#312).
- **Research revision, implementation documents `27`–`35`**: early drafts `27`–`30` and their coordinator pass (30 October 2026); freshness checks of `12`, `13`, `25`, `26`; wave A (`22`, `23`, `27`–`30`) and wave B (`31`–`35`) with their knock-ons carried into the other documents, closed 3 November 2026; all parts `disetujui pengembang` (PR #282, #284–#288, #302–#305, #308–#331).
- **Research revision, code parts 27–35**: wave C part 1 roles (PR #332); wave D part 2 field sessions and part 5 new question bank (PR #339–#340); wave E parts 3, 4, 6: research data, consent and data rights, Skenario Digital with its 24 situation pictures (PR #349, 8 November 2026); wave F parts 7, 8, 9: research mode, test module, derived data layer (PR #350, 9 November 2026); migrations `003400`–`004300`, owner answers after each wave, re-check on MariaDB 10.11 and Playwright, suite green (1233 tests).
- **Leftover-work interview** (9 November 2026, branch `r10-wawancara-sisa-kerja`, `04`): stale rows closed, `kamus-1.1` approved by the developer, server-side manual checks on MariaDB 10.11 in the cloud container (among them migration `2026-01-01-003300`, the study package lock, with every migration rolled back and re-run), `StaffUserSeeder` no longer writes the removed `school_id`.
- **Asset package leftovers, Wave 2** (built 9 November 2026, branch `pw/intelligent-davinci-i6ysvq`, decision log *Asset package leftovers*): `gelita:paket:bangun --deploy` at the end of both deploy scripts, the study's package lock shown on Rilis and Paket aset and in the research-ready checklist, a preview pill for the admin, open sessions keep their package.

## Active branches

Add a row (branch, owner / scope, status) when a session starts a branch; remove it when its PR is merged.

| Branch | Owner / scope | Status |
|---|---|---|
