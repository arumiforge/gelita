# GELITA — working rules for Claude Code

Topic rules live in `.claude/rules/` and load automatically:
`language.md` (chat, code, routes, docs), `ui-copy.md` (UI text and local formats, loads when editing UI files), `glossary.md` (Indonesian ↔ English terms), `parallel-agents.md` (subagents and file ownership), `revisi-10.md` (keeping research-revision sessions small, loads with `docs/revisi-10-analitik/`).

## Where things stand (read first)
- **Progress checklist:** `docs/PROGRESS.md` — what is done, what is open, who owns which branch.
- **Decision log:** `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md` — record every decision or deviation from the plan documents there.
- **Research revision (current focus):** start with `docs/revisi-10-analitik/00_INDEKS.md` (reading order, document status, remaining document stages); its decisions go in `04_CATATAN_KEPUTUSAN.md` there.
- Auto memory does not survive cloud sessions. Anything worth remembering goes into this file, `.claude/rules/`, `docs/PROGRESS.md`, or the decision log, and is committed.

## Identity and attribution
- Every commit uses the repo owner identity `Arumi Studios <arumiforge@gmail.com>` (set via `env` in `.claude/settings.json`; if it is not active, run `git config user.name "Arumi Studios"` and `git config user.email "arumiforge@gmail.com"`). Never use a Claude identity.
- Commit messages and PR descriptions carry **no** Claude attribution lines (`Co-Authored-By: Claude …`, `Claude-Session: …`, "Generated with Claude Code"). This overrides the default attribution instructions.

## Branches
- The only permanent branch is `main`.
- Work happens on short-lived branches cut from `main`, without a `claude/` prefix (e.g. `dok2-klien-kesempatan`). They merge into `main` via PR and are deleted right after (GitHub deletes them on merge).
- Never push directly to `main` unless the owner asks.
- `main` history was rewritten on 6 October 2026. Clones or branches from before that date no longer match; re-fetch from `main`.

## Tests
- `composer install`, then `vendor/bin/phpunit --no-coverage` (without the flag the suite exits 1 on machines without a coverage driver). Everything must be green before pushing.

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
  - Sample images via `gelita:mockups`; the team's art via `gelita:katalog:impor` from `gelita-katalog/` (Katalog Aset: images, references, prompts; its own rules in `gelita-katalog/AGENTS.md`).

  `ContentRepository` caches content; call `flush()` after changing it.
- **Art direction (owner, 11 October 2026).** Stylized 3D, Jaka with a red scarf, WebP as the default raster format (`app.*` icons PNG), up to 24 looping frames per character pose. Finished plans live in `docs/arsip/`.
- **Challenge client.** `game/challenge.js` is the shared frame: `check`/`submit`, `ruleError`, tries indicator, fill time, and `state.leaving` so no false `challenge_abandoned` is sent. Engines live in `engines/{puzzle,rumpang,boleh,cari}.js`.
- **Research events.** `EventService` and the `game_event_logs` table. `screen_viewed`, `challenge_tries_exhausted`, and `clue_failed` may only be written by the server.
- **Spark commands** (`app/Commands`): `gelita:reset`, `gelita:bank:import`, `gelita:content:verify`, `gelita:mockups`, `gelita:narration:import`, `gelita:media:scan`, `gelita:retention:run`, `gelita:schools:import`, `gelita:staff:password`, `gelita:library:thumbnails`, `gelita:katalog:impor` (installs the `gelita-katalog/` images into the slots), `gelita:story:sync` (updates the story lines of an installed server to `data/story.php`; earlier versions in `data/story-previous.php`), `gelita:simulasi` (simulated `sim_*` students for development; FEATURE 19 in `docs/07`), `gelita:aset:minify` (minified JS/CSS mirror in `public/assets/min/`; sources stay unminified; `docs/08` → *Minified JS/CSS*), `gelita:uji:akun` (test accounts `uji.*` for the validators' test install; `--buat`, `--reset=uji.x`, `--daftar`; 28 §13).

### Testing and known pitfalls

- **Test environment.** Tests use SQLite (group `tests`). Game tests use the trait `Tests\Support\Database\GradeFiveWorld`, which sets up the schema, `gelita:reset`, the kelas 5 bank, and one student ready to play. See `tests/unit/TryLimitTest.php` for the pattern.
- **Worktrees need their own `vendor/`.** A `vendor` symlinked from another checkout makes Composer load `App\`/`Config\` from that checkout, so tests silently run the wrong code. Run `composer install` (or copy `vendor/` and run `composer dump-autoload`) in the worktree.
- **SQLite does not emulate MySQL row locks.** Race tests simulate stale data, e.g. an old entity or a mock model via `Factories::injectMock`. Real behaviour must be checked on MariaDB/MySQL.
- **Content cache in tests.** Tests that change `challenge_items` need `service('contentRepository')->flush()` **and** `service('cache')->clean()`, because MockCache stores keys without a prefix.
- **Upload folder in tests.** Under `ENVIRONMENT === 'testing'`, `Config\Gelita::$uploadDir` is `assets/uploads-test/` (gitignored), so tests never overwrite or delete the real `public/assets/uploads/`. Build upload paths with `MediaStore::uploadDir()`, never a literal `assets/uploads/`; `Tests\Support\RealUploadsGuard` checks that a test left the real folder alone.
- **UI pictures need a versioned URL.** Pictures in `public/assets/ui/` go through `asset_url_versioned('ui/…')` (`?v=` from the file's mtime and size), never `game_asset_url()` or a bare `base_url('assets/ui/…')`: a replaced picture under an unversioned URL stays in the browser cache after a refresh. `game_asset_url()` is only for fonts.
- **Language files.** Every key in `app/Language/id/*` needs a counterpart in `app/Language/en/*` (`LanguageFilesTest`), except `Admin.php`, which is Indonesian only.
- **GitHub.** The GitHub MCP server sometimes still appends "Generated by Claude Code" to PR descriptions; remove it with `update_pull_request`.
- **Browser.** Chromium is installed for Playwright (`/opt/pw-browsers`); never run `playwright install`.
