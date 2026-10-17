# Mockup desain baru GELITA

Folder ini menyimpan **mockup yang dipilih pengguna** beserta bahan implementasinya. Acuan aplikasi adalah kanvas tetap **16:9 landscape**, termasuk saat digunakan di HP; tidak ada perubahan susunan menjadi potret melalui breakpoint responsif.

Mulai dari [daftar mockup](list-mockup.md). Setiap halaman memiliki subfolder sendiri di `gambar-mockup/`, dengan nama berkas deskriptif memakai tanda hubung.

## Struktur setiap mockup

```text
gambar-mockup/<nama-halaman>/
  README.md
  mockup-<nama-halaman>-16-9.webp
  list-file.md
  keterangan-mockup.md
  kebutuhan-aset.md
  prompt-detail.md
  petunjuk-agent.md
  spesifikasi-ui.md
  preview-ui-lengkap.html
  aset-katalog/
  aset-baru/
  aset-ui/
  variasi-ui/<keadaan>/
```

- `aset-katalog/`: salinan berkas yang cukup dipakai ulang; kode dan sumber resmi tetap tercatat dalam daftar aset.
- `aset-baru/`: hasil baru untuk rancangan ini, terpisah dari katalog aktif sampai diintegrasikan melalui pemetaan slot aplikasi.
- `aset-ui/`: sumber visual panel, tombol, kartu, dan popover; `preview-ui-lengkap.html` merangkainya menjadi halaman lengkap. `variasi-ui/` menyimpan gambar setiap keadaan dalam subfolder terpisah.
- Catatan generasi dan hash berada di `../provenance/`; hasil pemeriksaan berada di `../reports/`.
- Panel dan tombol menggunakan skin WebP revisi 13 Oktober; teks, state, progres dan hit area dibuat melalui HTML/CSS/SVG. Gambar mockup bukan gambar halaman yang dipasang sebagai pengganti UI.

Paket ini bukan sumber aplikasi lengkap, bukan audit visual baru seluruh katalog, dan bukan bukti bahwa integrasi, ukuran sentuh, budaya, atau lisensi telah disahkan. 88 Narasi tetap dibekukan; sembilan pratinjau audio nonnarasi tersedia atas permintaan 14 Oktober. Mockup yang tidak dipilih tidak disalin ke folder ini.

Mockup harus menunjukkan **halaman beserta UI**, bukan hanya lanskap. Lanskap terpisah adalah media pendukung; kriteria keberhasilan perombakan mencakup HUD, panel, tombol, informasi, dan interaksinya.

Mockup 01 “Tantangan selesai” adalah konsep bersama untuk seluruh kota/wilayah. Temanggung hanya contoh pada gambar; implementasi memakai satu komponen dengan konteks wilayah dinamis.


[Galeri pada katalog](../index.html#mockups) memuat referensi pilihan, empat keadaan UI dan preview interaktif. Inventaris ada di [catalog.json](catalog.json). [Persiapan 12 halaman berikutnya](persiapan/README.md) menyediakan brief/prompt; [Peta Kedu kandidat 04](gambar-mockup/peta-kedu/revisi-04/README.md) memiliki full UI WebP, preview interaktif dan riwayat 01–03, sementara 11 halaman lainnya belum memiliki gambar baru. Status planned dipertahankan pada kandidat sampai dipilih pengguna; label kandidat dan tautan gambar/prompt dicatat terpisah dari hasil terpilih.
