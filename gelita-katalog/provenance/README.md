# Catatan asal dan generasi

[Peta Kedu kandidat04 —15Oktober](peta-kedu-mockup-04-20261015.json) mencatat satu master diorama ImageGen1774×887, tiga potongan viewport CSS, sumber SVG Kenali baru, skin katalog yang dipakai ulang, empat render kode serta hash/preservasi kandidat01–03. Preview mengetik/dot/menu termasuk paket, belum terintegrasi game.

[Peta Kedu kandidat03 — 14 Oktober](peta-kedu-mockup-03-20261014.json) merekam generasi dan dua koreksi ImageGen untuk perombakan bebas mockup berfokus HP landscape, acuan kandidat02/hasil pilihan/Jaka, draf, native/final dan rencana sentuh. Diorama baru berupa usulan dalam full UI; belum mengganti aset map.kedu.

[Peta Kedu kandidat 02 — 14 Oktober](peta-kedu-mockup-02-20261014.json) mengikuti dua screenshot pengguna dan pembacaan DOM web asli di Chrome. Merekam satu panggilan ImageGen, lima input, prompt/hash, koordinat asli dan catatan pergeseran visual. Revisi terbatas; kandidat belum dipilih, tidak mengganti kandidat 01 atau data web.

[Peta Kedu kandidat 01 — 14 Oktober](peta-kedu-mockup-20261014.json) mencatat satu panggilan built-in ImageGen, empat input, prompt aktual, native WebP/final 16:9 serta hash. Kandidat belum dipilih dan belum dipecah menjadi aset aplikasi.

Catatan ini membantu menelusuri pekerjaan terdahulu. Prompt aktif untuk pekerjaan baru tetap ada di `../data.json` dan [daftar prompt hasil build](../docs/PROMPTS-ENGLISH.md).

| Berkas | Isi |
|---|---|
| `generation-manifest.json` | Prompt/output sembilan referensi baru serta generasi ikon terpilih |
| `icon-generation-jobs.json` | Daftar pekerjaan generasi ikon terdahulu; bukan antrian yang harus dijalankan ulang |
| `icon-generation-results.json` | 20 hasil AI terpilih, kode aset, prompt, dan lokasi sumber pembuat |
| `migration-map.json` | 475 nama/jalur lama ke tujuan saat ini, termasuk format alternatif |
| `character-generation-results.json` | Sepuluh prompt dan sumber key pose karakter 3D, 12 Oktober 2026 |
| `character-keyposes/` | Key pose WebP lokal untuk mereproduksi frame gerak sekunder |

475 pemetaan bukan 475 visual unik. Sesudah penghapusan PNG, beberapa nama lama menunjuk tujuan WebP yang sama. Nama PNG/ICO pada kode resmi tidak diubah.

Lokasi absolut `source`/`path` di manifest bisa berada di komputer pembuat dan tidak disertakan di GitHub. Pertahankan sebagai bukti sejarah; jangan menganggapnya tautan aktif katalog. Hasil yang tersedia dibaca melalui `file`, `preview`, atau `generated_asset` di data.

Revisi hasil 13 Oktober: [prompt pekerjaan](result-ui-generation-prompts-20261013.json), [hasil dan pemetaan delapan slot](result-ui-generation-results-20261013.json), serta sumber WebP lossless di result-ui-keyassets-20261013/. Enam gambar AI dipilih dari tujuh panggilan; lanskap dipakai ulang dan efek medali SVG native.

Ikon bahasa bendera: [prompt](language-flag-prompts-20261013.json) dan [hasil/pemetaan](language-flag-generation-results-20261013.json); dua hasil AI WebP native disimpan dalam language-flag-keyassets-20261013/.

Puzzle 14 Oktober: [manifest final](puzzle-generation-results-20261014.json), [prompt panduan](puzzle-generation-20261014/jobs.json), [prompt aktual dan koreksi](puzzle-generation-20261014/results.json), [sumber foto dan lisensi](puzzle-generation-20261014/factual-references/sources.json). Native WebP, kandidat, dan riwayat koreksi disimpan lokal di puzzle-generation-20261014/. 30 panggilan ImageGen: satu percobaan awal tidak dipakai, 25 generasi, empat koreksi; 25 hasil dipilih, 21 slot baru dan empat penggantian.


14 Oktober: [produksi audio nonnarasi](nonnarration-audio-20261014.json) merekam sintesis lokal, master/hash dan batas klaim; [penggantian slot bahasa](language-slot-update-20261014.json) merekam reuse bendera dan cadangan slot lama. Prompt rencana mockup di mockup/persiapan belum merupakan riwayat generasi.


[Galeri/persiapan halaman 14 Oktober](catalog-page-update-20261014.json) mencatat reuse referensi pilihan, hash empat render terkini, alias bahasa dan 12 brief/prompt. Tidak ada generasi gambar AI baru dalam pembaruan katalog ini.
