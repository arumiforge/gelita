# Instrumen — test stimulus pictures (3 November 2026)

Pictures the owner supplied for the test module of revision 10, part 34 (`docs/revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md` §6.1). They are not catalogue entries in `data.json`. Each file is named after its future game slot `instr.{stimulus code}`; the stimulus texts are in docs `14` (M3, M4) and `15` (D2, D3).

| Files | Stimuli |
|---|---|
| `aset/instr.tes-mb-{A,B}-M{3,4}.webp` | Bagian 1: M3 official post with comments, M4 forwarded message vs official notice |
| `aset/instr.tes-ae-{A,B}0{1..8}.webp` | Bagian 2 (D2): the eight mock screens, forms A and B |
| `aset/instr.tes-bd-{A,B}-G{1,2}.webp` | Bagian 3 (D3): scenes G1 (karawo, besurek) and G2 (Gantar, Maengket) |

The PNG masters stay in `docs/revisi-10-analitik/gambar/doc-34/`. These WebPs are quality 82, portrait pictures 1024 × 1536, landscape 1536 × 1024. Not installed yet: part 34 adds them to `CatalogImporter::MOCKUP_FILES` and `AssetChecklist` when it creates the `instr.*` slots.
