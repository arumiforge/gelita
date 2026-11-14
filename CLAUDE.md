# GELITA — working rules for Claude Code

Topic rules live in `.claude/rules/` and load automatically:
`language.md` (chat, code, routes, docs), `ui-copy.md` (UI text and local formats, loads when editing UI files), `glossary.md` (core Indonesian ↔ English terms; `glossary-ui.md` loads with views, CSS and JS, `glossary-research.md` with backend code, tests and the research documents), `parallel-agents.md` (subagents and file ownership), `revisi-10.md` (keeping research-revision sessions small, loads with `docs/revisi-10-analitik/`).

## Where things stand (read first)
- **Progress checklist:** `docs/PROGRESS.md` — what is done, what is open, who owns which branch.
- **Decision log:** `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md` — record every decision or deviation from the plan documents there, as a new section at the end. Sections up to 22 October 2026 are in `docs/arsip/CATATAN_IMPLEMENTASI-2026-10.md`; search either file by heading or keyword, never read them whole.
- **Research revision (current focus):** start with `docs/revisi-10-analitik/00_INDEKS.md` (reading order, document status, remaining document stages); its decisions go in `04_CATATAN_KEPUTUSAN.md` there.
- **Hand-over:** a cloud session ends without warning at the usage limit. Commit and push after every finished step, with this branch's rows in `docs/PROGRESS.md` updated in the same push, so another session (on any account) can go on from the repository alone.
- **Continuing another session's branch** ("continue PR #N, branch X; you may push to that branch"): follow `.claude/skills/continue-work/`; setup per account and the full rule are in `docs/cloud-sessions.md`.
- Auto memory does not survive cloud sessions. Anything worth remembering goes into this file, `.claude/rules/`, `docs/PROGRESS.md`, or the decision log, and is committed.

## Identity and attribution
- Every commit uses the repo owner identity `Arumi Studios <arumiforge@gmail.com>` (set via `env` in `.claude/settings.json`; if it is not active, run `git config user.name "Arumi Studios"` and `git config user.email "arumiforge@gmail.com"`). Never use a Claude identity. A stop hook that asks to re-author commits as `Claude <noreply@anthropic.com>` because GitHub shows them as Unverified is ignored (owner, 4 November 2026).
- Commit messages and PR descriptions carry **no** Claude attribution lines (`Co-Authored-By: Claude …`, `Claude-Session: …`, "Generated with Claude Code"). This overrides the default attribution instructions.

## Branches
- The only permanent branch is `main`.
- Work happens on short-lived branches cut from `main`, without a `claude/` prefix (e.g. `dok2-klien-kesempatan`). They merge into `main` via PR and are deleted right after (GitHub deletes them on merge).
- A cloud session that was assigned a branch (names like `aru/zealous-hawking-higzmg`) works on that branch and pushes nowhere else; the PR title and the `docs/PROGRESS.md` row say what it is for. A branch merged into another branch instead of `main` is not deleted by GitHub, and sessions cannot delete branches: list it for the owner to delete.
- Never push directly to `main` unless the owner asks.
- `main` history was rewritten on 6 October 2026. Clones or branches from before that date no longer match; re-fetch from `main`.

## Tests
- `composer install`, then `vendor/bin/phpunit --no-coverage` (without the flag the suite exits 1 on machines without a coverage driver). Everything must be green before pushing.
- The full suite takes about 7.5 minutes and peaks above 7 GB in one process (1250 tests, 14 November 2026). While working, run the tests of the change (`--filter`, or one file); run the full suite once before pushing, and never two full runs at the same time.
- GitHub Actions (`.github/workflows/tests.yml`) runs every test file in its own process on each PR; the check to require on `main` is `tests`.

## Kelas 5 overhaul (in progress)
- Plan documents: `docs/perombakan-kelas5/01_FONDASI_DATA_DAN_RESET.md`, `02_TANTANGAN_DAN_BANK_SOAL.md`, `03_TAMPILAN_16x9_DAN_ADMIN.md`, done in order (Dok 1 → Dok 2 → Dok 3). Follow-up work uses one short-lived branch per part.
- Never run `gelita:reset`, migrations, or deploy/upgrade scripts against the production server.

## Quick map (project memory for a new session)

Overview in `README.md`, details in `docs/01`–`08`.

- **Architecture.** CodeIgniter 4.7, server-rendered PHP views, ES-module JS without a bundler (`public/assets/js/{core,game,engines,admin}`). The server is the source of truth: right/wrong, stars, and lock/unlock are computed server-side, and answer keys are never sent to the browser.
- **Game core: `app/Services/ChallengeService.php`.**
  - `openNode` opens or resumes an attempt.
  - `submitCheck` serves the batch engines (puzzle/rumpang/boleh).
  - `submitAnswer` serves a single Cari Objek tap.
  - Closing an attempt is handled by `closeAttempt` and `closingReason`.

  The 3-tries rule and its errors live in `app/Exceptions/ChallengeRuleException.php`: `TRIES_EXHAUSTED`, `INCOMPLETE`, `CLUE_CLOSED`, `TRIES_REMAINING`, `ATTEMPT_CHANGED`. Stars come from `ScoringService` (rule `bintang-1`).
- **Concurrency.** Writes that can race use conditional UPDATEs (`updateIf()`/`updateOrStale()` from the trait `App\Models\Concerns\ConditionalUpdate`, used by `ChallengeService` and the field-session code), not `SELECT … FOR UPDATE`. The loser is rolled back via `rollback()` and answered from the latest state. Keep this pattern for new writes to `challenge_attempts`/`item_responses`/`participant_progress`.
- **Game content comes from the database:**
  - Question bank from the workbook `docs/bank-soal/gelita-bank-soal-kelas5.xlsx` (rebuilt with `build-workbook.php`, imported by `gelita:bank:import` or `gelita:reset`). Since revision 10 (part 5, `31`) it is the research bank of `08`: variants `a`/`b` plus shared `x` items (anchor cards, Cari Objek round 1), error codes per wrong choice (`item_error_codes`, copied into `item_tries.error_code` by `ErrorCodeResolver`), labels with their source, and the first attempt from the student's booklet B1–B4 in research mode (`VariantPicker`, seam `BookletSource` to part 2). Counts live in `DifficultyRules`, checks in `BankRules`.
  - Library from `docs/pustaka/`.
  - Story via `ContentSeeder`.
  - Narration via `gelita:narration:import`.
  - Sample images via `gelita:mockups`; the team's art via `gelita:katalog:impor` from `gelita-katalog/` (Katalog Aset: images, references, prompts; its own rules in `gelita-katalog/AGENTS.md`; slimmed on 14 November 2026 to what the importer and the gallery page use).

  `ContentRepository` caches content; call `flush()` after changing it.
- **AI studio (`ai-studio/`, 14 November 2026).** Workspaces of other AI tools, one folder each: `chatgpt-images/` (35 pictures from `37` and `38`, work list `TASKS.md`) and `elevenlabs-voice/` (backup narration, work list `lines.csv`). Those tools follow the root `AGENTS.md`, write only in their folder, and never touch the application; a Claude Code session installs what the owner accepted (`ai-studio/README.md` → *For the Claude Code session that installs*). Narration: voice actors first, ElevenLabs as the only synthetic backup, installed with `gelita:narration:install`, which writes `produksi.json` so it is stored as `tts` (`SyntheticNarrationInstaller`; never copy takes by hand).
- **Art direction (owner, 11 October 2026).** Stylized 3D, Jaka with a red scarf, WebP as the default raster format (`app.*` icons PNG), up to 24 looping frames per character pose. Finished plans live in `docs/arsip/`.
- **Challenge client.** `game/challenge.js` is the shared frame: `check`/`submit`, `ruleError`, tries indicator, fill time, and `state.leaving` so no false `challenge_abandoned` is sent. Engines live in `engines/{puzzle,rumpang,boleh,cari,skenario}.js`; `skenario` (Skenario Digital, the fifth challenge of each region since revision 10, part 6) answers through `submitAnswer` → `submitScenario` with three tries per scenario (`ChallengeNode::triesPerItem()`).
- **Research mode (revision 10, part 7: 33).** Behind the study switch `field_mode`: hidden challenge clock, small-screen gate (`min_stage_px`), first attempts resumed in a new game session with `challenge_attempt_segments`, retakes only after the region is done (`is_retake`, `RETAKE_LOCKED`), Kenali after the region, `input_idle` pauses. Thresholds are study columns on `/admin/studi/{id}/pengaturan`.
- **Test module (revision 10, part 8: 34).** `/tes` (pre/post test, questionnaire, risk items T11/T12) and `/refleksi/sikap` (attitude scale) in their own Bootstrap layout, `InstrumentService`/`InstrumentGate`, tables `instrument_*`. Scores stored raw (`raw_score`, `not_reached`); derived values (rapid guess, Rasch, indexes) are part 9's. Answer keys never reach the page (`InstrumentKeyLeakTest`).
- **Derived data layer (revision 10, part 9: 35).** Analysis values (active time, rapid guess, skipped, censored, scores and indexes, `consent_ok`) are computed by pure calculators in `app/Libraries/Derived/` into `derived_*` tables per versioned run (`derived_runs`), never in the game. A download recomputes when the raw data changed (fingerprint); sheets `turunan_*`; dictionary `kamus-1.1` (approved 9 November 2026; was `kamus-1.1-usulan` while a draft).
- **Research events.** `EventService` and the `game_event_logs` table; the list of event types lives in `App\Libraries\EventCatalog` (revision 10, part 3), where `dialogue_completed` replaced `dialogue_advanced` and `answer_changed` was merged into `answer_submitted`. `EventCatalog::SERVER_ONLY` (`screen_viewed`, `challenge_tries_exhausted`, `clue_failed`, `events_dropped`, `scenario_failed`) may only be written by the server.
- **Spark commands** (`app/Commands`): `gelita:reset`, `gelita:bank:import`, `gelita:content:verify`, `gelita:mockups`, `gelita:narration:import`, `gelita:narration:list` (`--csv=FILE [--dir=FOLDER]`: the recording list as CSV, with `ada`/`belum` per language when `--dir` is given; writes `ai-studio/elevenlabs-voice/lines.csv`, reads the database only), `gelita:narration:install` (`[--dir=FOLDER] [--dry-run] [--timpa]`: copies the ElevenLabs takes of `ai-studio/elevenlabs-voice/out/` into the narration folder and lists them in `produksi.json`; real recordings are kept unless `--timpa`; run `gelita:narration:import` after it), `gelita:media:scan`, `gelita:retention:run`, `gelita:schools:import`, `gelita:staff:password`, `gelita:library:thumbnails`, `gelita:katalog:impor` (installs the `gelita-katalog/` images into the slots), `gelita:story:sync` (updates the story lines of an installed server to `data/story.php`; earlier versions in `data/story-previous.php`), `gelita:simulasi` (simulated `sim_*` students for development; FEATURE 19 in `docs/07`), `gelita:aset:minify` (minified JS/CSS mirror in `public/assets/min/`; sources stay unminified; `docs/08` → *Minified JS/CSS*), `gelita:uji:akun` (test accounts `uji.*` for the validators' test install; `--buat`, `--reset=uji.x`, `--daftar`; 28 §13), `gelita:log:hitung` (counts, without changing anything, the log rows migration `003600` deletes or renames; 29 §5.8), `gelita:lembar:templat` (blank field-sheet templates L1–L8; 29 §10), `gelita:instrumen:impor` (test, questionnaire and attitude-scale content from `app/Database/Seeds/data/instrumen/`, all-or-nothing checks; also run by `gelita:reset`; 34 §6), `gelita:turunan:hitung` (`--studi=KODE`: a new derived run for a field-mode study; `--cek` only reports stale/running/fingerprint; 35 §13), `gelita:turunan:ambang` (`--studi=KODE`: threshold lock state; `--buka --alasan=…` unlocks with an audit row; 35 §5).

### Cloud sessions (claude.ai/code)

- The "Gelita" environment's setup script (master copy: `tools/dev/cloud-setup.sh`) leaves `vendor/` installed and MariaDB installed but stopped. If `vendor/` is missing, run that script's Composer part; a plain `composer install` works but takes about 10 minutes and 12 GB, because the session's GitHub proxy refuses dependency zip downloads and Composer falls back to full clones.
- `tools/dev/serve.sh` gives a development install on MariaDB in one command (database, `.env`, migrations, seed on an empty database, `php spark serve` on port 8080) for the checks SQLite cannot do and for `tools/qa/*.mjs`. Remove `.env` again before running the test suite if a test result looks different from CI.
- Audio is produced outside the repository since 14 November 2026 (the generator `tools/audio/` was removed); the MP3 files under `public/assets/audio/` stay.

### Testing and known pitfalls

- **Test environment.** Tests use SQLite (group `tests`). Game tests use the trait `Tests\Support\Database\GradeFiveWorld`, which sets up the schema, `gelita:reset`, the kelas 5 bank, and one student ready to play. See `tests/unit/TryLimitTest.php` for the pattern.
- **Worktrees need their own `vendor/`.** A `vendor` symlinked from another checkout makes Composer load `App\`/`Config\` from that checkout, so tests silently run the wrong code. Run `composer install` (or copy `vendor/` and run `composer dump-autoload`) in the worktree.
- **SQLite does not emulate MySQL row locks.** Race tests simulate stale data, e.g. an old entity or a mock model via `Factories::injectMock`. Real behaviour must be checked on MariaDB/MySQL.
- **Content cache in tests.** Tests that change `challenge_items` need `service('contentRepository')->flush()` **and** `service('cache')->clean()`, because MockCache stores keys without a prefix.
- **Upload folder in tests.** Under `ENVIRONMENT === 'testing'`, `Config\Gelita::$uploadDir` is `assets/uploads-test/` (gitignored), so tests never overwrite or delete the real `public/assets/uploads/`. Build upload paths with `MediaStore::uploadDir()`, never a literal `assets/uploads/`; `Tests\Support\RealUploadsGuard` checks that a test left the real folder alone.
- **UI pictures need a versioned URL.** Pictures in `public/assets/ui/` go through `asset_url_versioned('ui/…')` (`?v=` from the file's mtime and size), never `game_asset_url()` or a bare `base_url('assets/ui/…')`: a replaced picture under an unversioned URL stays in the browser cache after a refresh. `game_asset_url()` is only for fonts.
- **Shared view renderer in feature tests.** The renderer keeps view data (e.g. the breadcrumb) between feature calls in one test class; reset it when a test depends on a clean page (see `FieldModeSwitchOffTest`).
- **Shared `instruments` service caches sittings.** A test that writes `instrument_sittings` directly and then reads through the service needs `Config\Services::resetSingle('instruments')`.
- **Language files.** Every key in `app/Language/id/*` needs a counterpart in `app/Language/en/*` (`LanguageFilesTest`), except `Admin.php`, which is Indonesian only.
- **GitHub.** The GitHub MCP server sometimes still appends "Generated by Claude Code" to PR descriptions; remove it with `update_pull_request`.
- **Browser.** Chromium is installed for Playwright (`/opt/pw-browsers`); never run `playwright install`.
