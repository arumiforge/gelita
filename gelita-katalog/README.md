# Katalog Aset GELITA

The team's picture store: the masters the game installs into its picture slots. Internal; never placed under `public/`. Rules for changing anything here are in [AGENTS.md](AGENTS.md).

## What is in it (15 November 2026)

| Folder | Files | Content |
|---|---|---|
| `assets/karakter/` | 240 | Jaka and Mbah Kedu, 10 poses × 24 frames (`char.*`) |
| `assets/tantangan/` | 66 | Susun Gambar pictures, Benar atau Salah pictures, Cari Objek scenes and objects (`challenge.*`) |
| `assets/pustaka/` | 60 | Pustaka illustrations and video covers (`library.*`) |
| `assets/ikon/` | 84 | App icons, avatars, vector icons, language flags |
| `assets/latar/` | 27 | Backgrounds and story slides (`bg.*`) |
| `assets/referensi/` | 26 | Style references (`reference.*`); `reference.01` is Jaka with the red scarf |
| `assets/antarmuka/`, `logo/`, `lencana/`, `peta/` | 19 | Result screen parts, logo, badges, maps |
| `mockup/gambar-mockup/skenario/aset/` | 24 | Skenario Digital situation pictures |
| `mockup/gambar-mockup/instrumen/aset/` | 24 | Test stimulus pictures |
| `mockup/gambar-mockup/cerita-layar/aset/` | 18 | Interim bridge-story backgrounds |
| `mockup/gambar-mockup/{kenali,peta-kedu/revisi-05}/aset/` | 2 | Jaka's avatar and the Peta Kedu island map |
| `assets/catalog/`, `assets/vendor/` | 10 | Styles, scripts and fonts of the gallery page only |

`data.json` has 514 picture entries for the files under `assets/`, each with its key and file (and 97 audio entries from October 2026 that nothing uses).

## Installing into the game

```bash
php spark gelita:katalog:impor --dry-run
```

```bash
php spark gelita:katalog:impor
```

The command shrinks each master to its slot size, stores it as lossy WebP in the game's upload folder, and leaves pictures an admin uploaded alone unless `--timpa` is given. Options and what goes where: root `README.md` → *Command `gelita:*`*, `docs/08_DEPLOYMENT.md` → *Installing the Katalog Aset images*, and the class comment of `app/Libraries/CatalogImporter.php`.

On a fresh development install the dry run reported 507 pictures to install and none failing (15 November 2026).

## The gallery page

[index.html](index.html) opens in a browser without a server. It is the page of October 2026, kept unchanged and no longer rebuilt: pictures and their prompts show, but the sound players, the page mock-up section and the links to removed reports do not work. The admin panel shows every slot with its current picture (*Gambar & mockup*, *Kelengkapan aset*).

## What was removed, and where it went

Removed on 15 November 2026 after the owner's audit (all of it is in git history): `archive/`, `reports/`, `provenance/`, `scripts/`, `templates/`, `build.mjs`, `docs/`, `update/`, `assets/audio/` (the game's sound comes from `public/assets/audio/` and `ai-studio/`), `mockup/persiapan/`, and the mock-up packages for pages already built or replaced (Peta Kedu candidates 01 to 04, the result screen).

Two documents moved instead of being removed: `docs/bank-soal/telaah-draf-1-naskah-bank-soal.md` and `docs/panduan-gambar-puzzle-gelita.md`.
