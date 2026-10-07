# `public/assets/vendor/`

Library frontend yang **di-host sendiri**. Tidak ada CDN: seluruh berkas di
bawah folder ini ada di repositori dan dimuat dari domain sendiri.

## Isi (ditambahkan tahap 5)

| Berkas | Library | Versi | SHA-256 | Dipakai di |
|---|---|---|---|---|
| `howler.min.js` | Howler.js | 2.2.4 | `736c339444c88baad593e24afdf1d6e8f574019b4e37a110ecb453ff745ffd41` | `layouts/game.php`, seluruh halaman game |
| `LICENSE-howler.md` | — | — | — | lisensi MIT |

Diambil dari paket rilis resmi di registry npm (`howler@2.2.4`
`dist/howler.min.js`), lalu di-commit apa adanya tanpa diubah. Periksa checksum di atas setelah memperbarui:

```bash
sha256sum public/assets/vendor/*.js
```

Font lokal ada di `public/assets/fonts/` (Cinzel, Plus Jakarta Sans,
IBM Plex Mono — subset latin `.woff2` dari paket Fontsource 5.3.0, lisensi SIL OFL 1.1 disertakan);
dimuat `base.css` lewat `@font-face` tanpa Google Fonts.

## Memperbarui versi

1. Unduh paket rilis resmi versi baru, ambil build `.min.js`-nya saja.
2. Ganti berkas di sini, perbarui tabel versi & SHA-256 di atas.
3. Naikkan `gelita.assetVersion` di `.env` agar cache browser ikut berganti
   (`asset_url_versioned()` menambahkan `?v=`).
4. Buka satu layar tantangan; pastikan konsol bersih.

Howler dikunci pada 2.2.x: naik versi mayor adalah keputusan tersendiri,
bukan pembaruan rutin.

Panel admin hanya menampilkan tabel data mentah, jadi tidak ada pustaka chart.

## Catatan `.gitignore`

Folder ini sempat terabaikan Git karena pola `vendor/` cocok dengan folder
bernama `vendor` di kedalaman mana pun. Pola tersebut sudah dipersempit
menjadi `/vendor/` (khusus folder Composer di root). Jangan mengembalikan
pola lama — berkas di sini tidak akan ikut ter-commit.

## Rujukan

- Status dan versi: [`docs/02_PROJECT_FOUNDATION.md`](../../../docs/02_PROJECT_FOUNDATION.md) → *Status `public/assets/vendor/`*
- Alasan pemilihan library: [`docs/05_VIEW_UI.md`](../../../docs/05_VIEW_UI.md) → *External CSS/JS Library*, [`docs/06_JAVASCRIPT.md`](../../../docs/06_JAVASCRIPT.md) → *Library JavaScript*
