# Peta Kedu — owner's island map (20 October 2026)

The owner replaced the three-island diorama of [revisi-04](../revisi-04/README.md) with this single-island picture. It is not a catalogue entry in `data.json`; the game installs it from here.

| File | Game slot | Use |
|---|---|---|
| `aset/diorama.kedu-pulau.webp` | `map.kedu-diorama` | Peta Kedu map, 1672 × 941 (about 16:9), installed at 1600 × 900 by `php spark gelita:katalog:impor` (`CatalogImporter::MOCKUP_FILES`). |

The regions follow the rivers in the picture: Wonosobo in the west (lake, plateau, harbour), Temanggung on the north-east plateau with the two volcanoes, Magelang in the south-east with Borobudur and the temples. The game darkens a locked region through a feathered mask per region (`public/assets/ui/kedu-region-{wilayah}.webp`, land only, measured on this picture). A different picture needs new masks and pin positions (`Config\Gelita::$mapDiorama`).
