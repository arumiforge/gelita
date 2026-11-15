# Game images: the one source of the game's pictures

Since 15 November 2026 (owner) every picture master of the game lives here and nowhere else. Until that day this was `gelita-katalog/`; that folder is emptied and will be rebuilt as a page once the review below is done. This is not a tool's work folder: ChatGPT and ElevenLabs never write here (root `AGENTS.md`), only a Claude Code session does.

Nothing here is loaded by the game at run time, and the folder is never served (it holds answer-revealing pictures).

## What is here

| Path | Read by | Notes |
|---|---|---|
| `data.json` | `App\Libraries\CatalogImporter::entries()` | 464 picture entries (514 until the 50 vector icons were dropped in the review of 15 November 2026): `key`, `file`, `cat`, plus title, size, and the prompt the picture was made from. A Pustaka entry may carry `caption` (`{"id": …, "en": …}`): the importer then replaces the caption of the photo the picture stands in for. Slimmed on 15 November 2026: the 97 audio entries and the status fields of the old gallery page are gone (git history). |
| `assets/{karakter,tantangan,pustaka,ikon,latar,referensi,antarmuka,logo,lencana,peta}/` | the importer, through `data.json` | Lossless WebP masters (SVG for vector icons), named after their key. |
| `mockup/gambar-mockup/*/aset/` | `CatalogImporter::MOCKUP_FILES` and `MOCKUP_DIRS` | 68 pictures that are not in `data.json` (Skenario Digital, test stimuli, interim bridge-story backgrounds, Jaka's avatar, the Peta Kedu island map). The folder names are those of the old catalogue. |
| `REVIEW.md`, `review/` | the owner | The review list of every picture and its contact sheets. Temporary: removed when the review is done. |

Not here: the UI art that ships with the game (`public/assets/ui/`, `public/assets/app/`) and the new pictures still waiting in `../chatgpt-images/out/`. `REVIEW.md` lists both next to the pictures of this folder.

## Installing into the game

```bash
php spark gelita:katalog:impor --dry-run
```

```bash
php spark gelita:katalog:impor
```

The command reads this folder (`CatalogImporter::DEFAULT_DIR`), shrinks each master to its slot size and stores it as lossy WebP in the game's upload folder. Details: root `README.md` → *Command `gelita:*`*, `docs/08_DEPLOYMENT.md` → *Installing the Katalog Aset images*.

## Rules

- **The game leads.** The slot list is `app/Libraries/ImageSlots.php`; challenge pictures follow the question bank (`docs/bank-soal/`); story slides follow `app/Database/Seeds/data/story.php`. This folder is brought in line with them, never the other way round, unless the owner asks for exactly that in the session.
- **One copy.** An accepted picture from `../chatgpt-images/out/` is moved here (`git mv`), not copied. A picture that fills several slots is one file with several `data.json` entries pointing at it.
- A file is named after its key. Raster pictures are WebP; no PNG variants.
- Replacing a region map or a Cari Objek scene changes its sha256, so `CatalogImporter::MAP_STOPS` or `SCENE_OBJECTS` and `tests/unit/CatalogImportTest.php` change with it.
- After a change: `php spark gelita:katalog:impor --dry-run` on a development install (`tools/dev/serve.sh`) and `vendor/bin/phpunit --no-coverage --filter CatalogImportTest`.
- No reports, backups, generation logs or progress notes here. History is git; decisions go in the project's decision log, open work in `docs/PROGRESS.md`.
- Pictures are interim art (credit `Katalog Aset GELITA (gambar sementara)`) until the owner accepts them. The developer's review in `REVIEW.md` is not expert validation and is not shown to the validators before they rate (`docs/revisi-10-analitik/01` → *Peran pengembang, peneliti, dan ahli*).

## Art direction (owner, 11 October 2026; under review since 15 November 2026)

Stylized 3D, Jaka with a red scarf (`assets/referensi/reference.01.webp`); render finish and mood as in `reference.06.webp` and `reference.07.webp`. The owner's aim for the review: one look across all pictures, matching the newest UI art (navy and gold, carved plates, round gold buttons in `public/assets/ui/`), and fit for the expert validation of the research. Current prompts for new pictures are in `docs/revisi-10-analitik/` (`32A`, `37`, `38`).
