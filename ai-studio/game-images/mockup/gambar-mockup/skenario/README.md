# Skenario — Skenario Digital situation pictures (4 November 2026)

The 24 situation pictures of the fifth challenge, Skenario Digital (research revision 10, part 6: `docs/revisi-10-analitik/32_IMPLEMENTASI_SKENARIO_DIGITAL.md` §7.5; prompts in `32A_PROMPT_GAMBAR_SITUASI.md`). They are not catalogue entries in `data.json`. Each file is named after its game slot `item.skenario.{item code}`, one per scenario item of the question bank.

| Files | Scenarios |
|---|---|
| `aset/item.skenario.tmg-5-{a,b}0{1..4}.webp` | Temanggung, variants a and b |
| `aset/item.skenario.mgl-5-{a,b}0{1..4}.webp` | Magelang, variants a and b |
| `aset/item.skenario.wnb-5-{a,b}0{1..4}.webp` | Wonosobo, variants a and b |

The PNG masters stay in `docs/revisi-10-analitik/gambar/32a/`. These WebPs are quality 82 at the masters' 1536 × 1024 (3:2 landscape, not downscaled: the game shows them at up to about 1200 px wide). `php spark gelita:katalog:impor` installs them (`CatalogImporter::MOCKUP_DIRS`) into the slots the question bank names (`challenge_items.media_asset_id`); a scenario without a picture still shows its text. Interim art: credited as `Katalog Aset GELITA (gambar sementara)` until the owner approves it.
