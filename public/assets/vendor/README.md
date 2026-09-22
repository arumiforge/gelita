# `public/assets/vendor/`

Library frontend yang **di-host sendiri**. Tidak ada CDN: seluruh berkas di
bawah folder ini harus ada di repositori dan dimuat dari domain sendiri
(docs/05_VIEW_UI.md → *External CSS/JS Library*, docs/06_JAVASCRIPT.md →
*Library JavaScript*).

Folder ini sempat terabaikan Git karena pola `vendor/` pada `.gitignore`
cocok dengan folder bernama `vendor` di kedalaman mana pun. Pola tersebut
sudah dipersempit menjadi `/vendor/` (khusus folder Composer di root), jadi
berkas di bawah ini kembali dapat di-commit.

## Berkas yang diperlukan

| Berkas | Library | Versi | Dipakai di |
|---|---|---|---|
| `echarts.min.js` | Apache ECharts | 6.x (dikunci di repo, diperbarui manual) | `/admin/dashboard`, `/admin/analitik/*`, `/admin/peserta/{id}` |
| `howler.min.js` | Howler.js | 2.2.x (dikunci di repo) | seluruh halaman game |

Keduanya diambil dari unduhan rilis resmi, bukan CDN, lalu di-commit apa
adanya. Berkas binernya sendiri ditambahkan pada tahap view/JavaScript
(tahap 5–6); sampai saat itu folder ini hanya berisi `.gitkeep` dan catatan
ini.
