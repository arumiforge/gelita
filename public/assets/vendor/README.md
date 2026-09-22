# `public/assets/vendor/`

Library frontend yang **di-host sendiri**. Tidak ada CDN: seluruh berkas di
bawah folder ini harus ada di repositori dan dimuat dari domain sendiri.

**Folder ini sengaja masih kosong.** Berkas binernya ditambahkan pada tahap
view/JavaScript (tahap 5–6); sampai saat itu isinya hanya `.gitkeep` dan
catatan ini.

## Berkas yang diperlukan

| Berkas | Library | Versi | Dipakai di |
|---|---|---|---|
| `echarts.min.js` | Apache ECharts | 6.x, dikunci di repo, diperbarui manual | `/admin/dashboard`, `/admin/analitik/*`, `/admin/peserta/{id}` |
| `howler.min.js` | Howler.js | 2.2.x, dikunci di repo | seluruh halaman game |

Keduanya diambil dari unduhan rilis resmi, bukan CDN, lalu di-commit apa
adanya.

## Catatan `.gitignore`

Folder ini sempat terabaikan Git karena pola `vendor/` cocok dengan folder
bernama `vendor` di kedalaman mana pun. Pola tersebut sudah dipersempit
menjadi `/vendor/` (khusus folder Composer di root). Jangan mengembalikan
pola lama — berkas di sini tidak akan ikut ter-commit.

## Rujukan

- Status dan versi: [`docs/02_PROJECT_FOUNDATION.md`](../../../docs/02_PROJECT_FOUNDATION.md) → *Status `public/assets/vendor/`*
- Alasan pemilihan library: [`docs/05_VIEW_UI.md`](../../../docs/05_VIEW_UI.md) → *External CSS/JS Library*, [`docs/06_JAVASCRIPT.md`](../../../docs/06_JAVASCRIPT.md) → *Library JavaScript*
