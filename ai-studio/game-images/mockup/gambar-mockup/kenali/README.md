# Kenali — owner pictures (19 October 2026)

Pictures the owner supplied with the Kenali screen request ("Mengenal {wilayah}" on Peta Kedu). They are not catalogue entries in `data.json`; the game installs them from here.

| File | Game slot | Use |
|---|---|---|
| `aset/ui.jaka-avatar.webp` | `ui.jaka-avatar` | Jaka's avatar in a gold ring like the Mbah Kedu avatar (`reference.02`). Replaces the bust `reference.03` everywhere Jaka's avatar shows: the Peta Kedu story bar and the Kenali screen. Installed by `php spark gelita:katalog:impor` (`CatalogImporter::MOCKUP_FILES`). |

The two button plates of the same request (gold for Lanjut, navy for Kembali and the nameplate) ship with the game in `public/assets/ui/kenal-plate-{gold,navy}.webp`, because they are only used on the Kenali screen and are not image slots.
