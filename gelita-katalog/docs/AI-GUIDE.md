# Panduan terstruktur untuk AI

## 1. Tujuan dan batas paket

Ini katalog aset statis, bukan aplikasi game lengkap. `index.html` menanam data JSON; browser tidak mengambil `data.json` melalui `fetch`. Aset dibaca melalui jalur relatif dari root katalog.

Seluruh aset gambar digunakan di aplikasi GELITA dengan rasio layar tetap **16:9** (landscape); halaman aplikasi tidak mendukung tata letak responsif untuk rasio layar lain. Pahami komposisi dan penempatan aset dalam konteks halaman tersebut. Rasio halaman tidak mengubah ukuran atau proporsi slot tiap aset. Pemeriksaan katalog pada desktop/mobile hanya memeriksa halaman katalog, bukan dukungan responsif aplikasi game.

Sebelum mengedit, baca [AGENTS.md](../AGENTS.md), [README.md](../README.md), [CONTEXT.md](CONTEXT.md), [MEMORY.md](MEMORY.md), dan [PROGRESS.md](PROGRESS.md). Instruksi historis dalam arsip bukan panduan aktif.

## 2. Aliran data

```text
data.json + templates/catalog.template.html
    + reports/icon-update-report.json
    + reports/prompt-review-report.json
    + archive/audio-baseline.json (pemeriksaan pembekuan)
            |
       node build.mjs
            |
            +-- index.html
            +-- docs/PROMPTS-ENGLISH.md
            +-- reports/STATUS-GAMBAR.json
            +-- reports/STYLE-AUDIT.json
            +-- reports/STYLE-AUDIT.csv

node scripts/validate.mjs --> reports/validation-report.json
```

`build.mjs` root memanggil `scripts/build.mjs`. Skrip aktif mencari root berdasarkan lokasi skrip, bukan lokasi terminal. Contoh perintah dijalankan dari root agar mudah diikuti. Entri dan prompt bersumber dari data, hasil build hanya turunan.

## 3. Lokasi perubahan

| Permintaan | Lokasi utama |
|---|---|
| Nama/deskripsi/prompt/status entri | `data.json`, lihat [skema](DATA-SCHEMA.md) |
| Mengganti gambar | Jalur `file` di `assets/<kelompok>/`; pertahankan kode |
| Tampilan/filter/dialog/ekspor | `templates/catalog.template.html`, lalu build |
| Proses build/pemeriksaan | `scripts/` |
| Konteks/keputusan/progres | `docs/`, README/AGENTS bila relevan |
| Riwayat generasi/nama lama | `provenance/`; lokasi sumber pembuat bisa tidak tersedia |
| Peninjauan gambar | `review/`; perhatikan tanggal dan lingkup |
| Kondisi sebelum perubahan | `archive/`; bukan prosedur aktif |

## 4. Invarian yang dipertahankan

- 590 entri: 493 visual dan 97 audio; ID dan `key` unik. Jangan mengubah kode slot untuk menyesuaikan ekstensi.
- Objek audio identik dengan baseline; dikenali melalui `size === "Audio"`.
- Visual memakai status “Sesuai gaya” berbasis `user_override` dan memiliki `previous_style_review`. Ini keputusan pengguna, bukan review baru.
- Ilustrasi 3D/Jaka syal merah mengikuti reference.01; Mbah Kedu reference.02. SVG kontrol jelas pada ukuran kecil; skin panel/tombol ui.result-* mengikuti full UI pilihan, media faktual dokumenter.
- `file`, `preview`, `generated_asset`, `vector_source` yang terisi menunjuk berkas tersedia relatif root. SVG kontrol di `assets/ikon/vektor/`.
- Raster aktif WebP; 440 raster tepat target. Jangan membuat PNG atau memperbaiki gambar hanya karena audit lamanya berbeda tanpa permintaan baru.
- Jangan membuat kembali folder sumber atau folder karakter tambahan yang dihapus.

## 5. Verifikasi dan pencatatan

Ikuti [MAINTENANCE.md](MAINTENANCE.md). Untuk gambar ukur berkas nyata; untuk UI/perpindahan tautan periksa browser bila dependensi tersedia. Tinjau tautan Markdown setelah pemindahan.

Catat hasil nyata dan batas pemeriksaan di [PROGRESS.md](PROGRESS.md). Laporan lama bukan pemeriksaan terbaru hanya karena dipindahkan. Perubahan dokumentasi tidak mengizinkan regenerasi gambar/audio. Jangan menyatakan seluruh gambar siap produksi.

