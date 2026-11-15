# Katalog Aset: rules for any agent working in this folder

Rewritten 15 November 2026 after the owner's audit. Earlier versions of this file, and everything it used to point at (`docs/`, `reports/`, `provenance/`, `archive/`, `scripts/`, `update/`, the builder), are in git history before that date.

## What this folder is

The committed master pictures the game installs with `php spark gelita:katalog:impor`, plus an old gallery page. Nothing here is loaded by the game at run time, and the folder is never served (it holds answer-revealing pictures, so it stays outside `public/`).

| Path | Read by | Notes |
|---|---|---|
| `data.json` | `App\Libraries\CatalogImporter::entries()` | One entry per picture: `key`, `file`, `cat`. Entries whose `size` is `Audio` are skipped. |
| `assets/{referensi,karakter,latar,peta,tantangan,pustaka,logo,antarmuka,ikon,lencana}/` | the importer, through `data.json` | Lossless WebP masters (SVG for vector icons), named after their key. |
| `mockup/gambar-mockup/*/aset/` | `CatalogImporter::MOCKUP_FILES` and `MOCKUP_DIRS` | Pictures that are not in `data.json`; each package has a short `README.md`. |
| `index.html`, `assets/catalog/`, `assets/vendor/` | a person opening the page | Frozen gallery page, see below. |

## The game leads, this folder follows

- The list of picture slots is `app/Libraries/ImageSlots.php`; challenge pictures follow the question bank (`docs/bank-soal/`); story slides follow `app/Database/Seeds/data/story.php`. When one of those changes, this folder is brought in line with it.
- Never change the game (slot keys, bank codes, layouts, positions) to fit something in this folder, unless the owner asks for exactly that in the session.
- A statement in `data.json` or `index.html` about the game (counts, statuses, "four stops per region map", audio briefs) is a record of October 2026, not a fact about the game today.

## Who writes here

- Only a Claude Code session, and only to install pictures the owner accepted or to follow a change in the game. Other AI tools never write here (root `AGENTS.md`); they read `assets/referensi/` as style references.
- Where accepted pictures from `ai-studio/chatgpt-images/out/` end up (copied here, moved here, or referenced from there) is an open owner decision since 15 November 2026: `docs/PROGRESS.md` → *Asset catalogue audit*. Until it is made, follow `ai-studio/README.md` → *For the Claude Code session that installs*.

## Rules for a change

- A file is named after its key and keeps the key's official code. Raster pictures are WebP; do not add PNG variants.
- Replacing a region map or a Cari Objek scene changes its sha256, so the measured positions in `CatalogImporter::MAP_STOPS` or `SCENE_OBJECTS` and `tests/unit/CatalogImportTest.php` change with it.
- After a change, run `php spark gelita:katalog:impor --dry-run` on a development install (`tools/dev/serve.sh`) and `vendor/bin/phpunit --no-coverage --filter CatalogImportTest`.
- Add no reports, backups, generation logs, one-off scripts or progress notes here. History is git; decisions go in the project's decision log and open work in `docs/PROGRESS.md`.
- Pictures stay interim art (credit `Katalog Aset GELITA (gambar sementara)`) until the owner accepts them; cultural checks by the validators still apply. Never describe them as ready for production.

## Art direction (owner, 11 October 2026)

Stylized 3D, Jaka with a red scarf (`assets/referensi/reference.01.webp`); render finish and mood as in `reference.06.webp` and `reference.07.webp`. Current prompts are in `docs/revisi-10-analitik/` (`32A`, `37`, `38`), not in this folder.

## The gallery page is frozen

`index.html` is the page as built in October 2026, kept unchanged at the owner's wish (15 November 2026). Its builder and validator were removed, so it cannot be rebuilt; do not edit it by hand. Known gaps: the nine sound players, the page mock-up section and the links to briefs, reports and generation records point at removed files, and its counts are those of October 2026. `data.json` still carries the fields that page used (prompts, style status, 97 audio entries, paths to removed records); the importer ignores them.
