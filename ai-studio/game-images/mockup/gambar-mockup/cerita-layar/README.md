# Cerita layar — interim bridge-story backgrounds (8 November 2026)

Placeholder backgrounds for the bridge story *Kabut di Layar* before each region's fifth challenge (context `scenario_open`, research revision 10, part 6: `docs/revisi-10-analitik/32_IMPLEMENTASI_SKENARIO_DIGITAL.md` §7.2, §7.5). They are not catalogue entries in `data.json`; each file is named after its catalogue code `bg.cerita-layar-{wilayah}-NN`, one per slide of the `12` script.

| Files | Picture |
|---|---|
| `aset/bg.cerita-layar-temanggung-0{1..6}.webp` | Sky and twin volcanoes of the Temanggung region map, blurred under the blue-grey Kabut Lupa veil |
| `aset/bg.cerita-layar-magelang-0{1..6}.webp` | Borobudur and the mountains of the Magelang region map, same treatment |
| `aset/bg.cerita-layar-wonosobo-0{1..6}.webp` | Sindoro over the Dieng clouds and lake of the Wonosobo region map, same treatment (no smoke or crater) |

Made by cropping the sky band of `assets/peta/map.region.{wilayah}.webp` (640 × 360 from the top), enlarged to 1920 × 1080, blurred, a radial mist (#8C97AA → #5B6478) and a navy lower-third shade added, WebP quality 78. The six files of a region are the same picture, as one generated picture will serve all six slides.

`php spark gelita:katalog:impor` installs them (`CatalogImporter::MOCKUP_DIRS`) as `bg.story.scenario_open.{wilayah}.{n}` and links each to its slide, like the `bg.cerita-*` entries of `data.json`; an entry with the same code in `data.json` wins. Replace them with the generated art from `docs/revisi-10-analitik/38_PROMPT_PETA_LIMA_TANTANGAN_DAN_SKENARIO.md` (T-L1, M-L1, W-L1), added to `data.json` by the catalogue team. Interim art: credited as `Katalog Aset GELITA (gambar sementara)`.
