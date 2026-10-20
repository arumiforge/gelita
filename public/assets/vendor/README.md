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

Font lokal ada di `public/assets/fonts/` (Plus Jakarta Sans,
IBM Plex Mono — subset latin `.woff2` dari paket Fontsource 5.3.0, lisensi SIL OFL 1.1 disertakan);
dimuat `base.css` lewat `@font-face` tanpa Google Fonts.

Since 20 October 2026 (owner) every game text uses Marcellus 400 (latin
subset `.woff2` from Fontsource `@fontsource/marcellus` 5.3.0, licence in
`OFL-Marcellus.txt`), replacing Cinzel Decorative and, in the game, Plus
Jakarta Sans. The digits 0–9 still come from Plus Jakarta Sans (`unicode-range`
face `Gelita Digits` in `base.css`), because Marcellus draws 1 like I and 0
like O. The panel keeps Plus Jakarta Sans.

## Memperbarui versi

1. Unduh paket rilis resmi versi baru, ambil build `.min.js`-nya saja.
2. Ganti berkas di sini, perbarui tabel versi & SHA-256 di atas.
3. Naikkan `gelita.assetVersion` di `.env` agar cache browser ikut berganti
   (`asset_url_versioned()` menambahkan `?v=`).
4. Buka satu layar tantangan; pastikan konsol bersih.

Howler dikunci pada 2.2.x: naik versi mayor adalah keputusan tersendiri,
bukan pembaruan rutin.

## Three.js (screen effects, owner decision 19 October 2026)

| File | Library | Version | SHA-256 | Used by |
|---|---|---|---|---|
| `three-0.186.1/three.module.js` | three.js | 0.186.1 | `9052042d676cb0fdc1ddfefe193053f34b7ac0513a616fdac4535d49987812ea` | `game/screen-fx.js` (narrated scenes) |
| `three-0.186.1/three.core.js` | three.js | 0.186.1 | `9edde002b066a9a05676a6127f67735b62baf399bdea529f2f7e31657da769e6` | imported by `three.module.js` |
| `three-0.186.1/LICENSE-three.txt` | — | — | — | MIT license |

- Taken from the official npm release (`three@0.186.1`, `build/three.module.js` and `build/three.core.js`) and committed unchanged. Since r18x three.js ships no `.min.js` builds; the owner accepted the size (about 2.1 MB, loaded only on the narrated scenes and cached by the asset package).
- **The version is in the folder name on purpose.** `three.module.js` imports `./three.core.js` without `?v=`, so a new version must get a new folder (`three-{version}/`), otherwise a browser could pair a new `three.module.js` with a cached old `three.core.js`. Update `Config\Gelita::$screenFxThree` to the new path.
- Only the core build is used. The `examples/jsm` add-ons (EffectComposer, UnrealBloomPass) import the bare specifier `'three'`, which needs an inline import map; the production CSP blocks inline scripts and import maps need iOS 16.4 or newer. The bloom in `game/screen-fx.js` is built from core render targets instead.
- Check after updating: `sha256sum public/assets/vendor/three-*/*.js`.

Panel admin hanya menampilkan tabel data mentah, jadi tidak ada pustaka chart.

## Catatan `.gitignore`

Folder ini sempat terabaikan Git karena pola `vendor/` cocok dengan folder
bernama `vendor` di kedalaman mana pun. Pola tersebut sudah dipersempit
menjadi `/vendor/` (khusus folder Composer di root). Jangan mengembalikan
pola lama — berkas di sini tidak akan ikut ter-commit.

## Rujukan

- Status dan versi: [`docs/02_PROJECT_FOUNDATION.md`](../../../docs/02_PROJECT_FOUNDATION.md) → *Status `public/assets/vendor/`*
- Alasan pemilihan library: [`docs/05_VIEW_UI.md`](../../../docs/05_VIEW_UI.md) → *External CSS/JS Library*, [`docs/06_JAVASCRIPT.md`](../../../docs/06_JAVASCRIPT.md) → *Library JavaScript*
