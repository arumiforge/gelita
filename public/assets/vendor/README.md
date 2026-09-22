# `public/assets/vendor/`

Library frontend yang **di-host sendiri**. Tidak ada CDN: seluruh berkas di
bawah folder ini ada di repositori dan dimuat dari domain sendiri.

## Isi (ditambahkan tahap 5)

| Berkas | Library | Versi | SHA-256 | Dipakai di |
|---|---|---|---|---|
| `echarts.min.js` | Apache ECharts | 6.1.0 | `b66b25aeb4df84e33199dc21694014d336d222cbd9deb0e5a7c14bd6aa0d0fd0` | `layouts/admin.php`, hanya bila halaman mengisi section `charts` (dashboard, analitik, detail peserta) |
| `howler.min.js` | Howler.js | 2.2.4 | `736c339444c88baad593e24afdf1d6e8f574019b4e37a110ecb453ff745ffd41` | `layouts/game.php`, seluruh halaman game |
| `LICENSE-echarts.txt`, `NOTICE-echarts.txt` | — | — | — | lisensi Apache-2.0 beserta NOTICE yang wajib disertakan |
| `LICENSE-howler.md` | — | — | — | lisensi MIT |

Keduanya diambil dari paket rilis resmi di registry npm (`echarts@6.1.0`
`dist/echarts.min.js`, `howler@2.2.4` `dist/howler.min.js`), lalu di-commit apa
adanya tanpa diubah. Periksa checksum di atas setelah memperbarui:

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
4. Buka dashboard admin dan satu layar tantangan; pastikan konsol bersih.

ECharts dikunci pada 6.x dan Howler pada 2.2.x: naik versi mayor adalah
keputusan tersendiri, bukan pembaruan rutin.

## Catatan `.gitignore`

Folder ini sempat terabaikan Git karena pola `vendor/` cocok dengan folder
bernama `vendor` di kedalaman mana pun. Pola tersebut sudah dipersempit
menjadi `/vendor/` (khusus folder Composer di root). Jangan mengembalikan
pola lama — berkas di sini tidak akan ikut ter-commit.

## Rujukan

- Status dan versi: [`docs/02_PROJECT_FOUNDATION.md`](../../../docs/02_PROJECT_FOUNDATION.md) → *Status `public/assets/vendor/`*
- Alasan pemilihan library: [`docs/05_VIEW_UI.md`](../../../docs/05_VIEW_UI.md) → *External CSS/JS Library*, [`docs/06_JAVASCRIPT.md`](../../../docs/06_JAVASCRIPT.md) → *Library JavaScript*
