# Kebutuhan aset dan keputusan pembuatan

## Aset hasil revisi 13 Oktober — sudah masuk katalog

Panel dan tombol kini memakai gambar khusus tanpa label, mengikuti full UI pilihan. Teks, data hasil, klik dan aksesibilitas tetap komponen aplikasi.

| Slot resmi | Ukuran | Gambar paket | Asal |
|---|---|---|---|
| `ui.result-panel` | 1536 × 752 | [panel-hasil-referensi.webp](aset-ui/panel-hasil-referensi.webp) | built-in image_gen |
| `ui.result-button-primary` | 600 × 132 | [tombol-primer-referensi.webp](aset-ui/tombol-primer-referensi.webp) | built-in image_gen |
| `ui.result-button-secondary` | 480 × 112 | [tombol-peta-referensi.webp](aset-ui/tombol-peta-referensi.webp) | built-in image_gen |
| `ui.result-button-detail` | 320 × 112 | [tombol-detail-referensi.webp](aset-ui/tombol-detail-referensi.webp) | built-in image_gen |
| `ui.result-hud-control` | 128 × 128 | [kontrol-hud.webp](aset-ui/kontrol-hud.webp) | built-in image_gen |
| `ui.result-star` | 128 × 128 | [bintang-hasil.webp](aset-ui/bintang-hasil.webp) | built-in image_gen |
| `bg.result.temanggung` | 1920 × 1080 | [bg-tantangan-selesai-temanggung-3d.webp](aset-baru/bg-tantangan-selesai-temanggung-3d.webp) | promoted existing 2026-10-12 background; no new generation |
| `ui.result-shard-effects` | 512 × 512 | [efek-medali-serpihan.svg](aset-ui/efek-medali-serpihan.svg) | native SVG glow, arcs and sparkles; existing avatar.2 unchanged |

Enam gambar baru dipilih dari tujuh panggilan ImageGen (panel diperbaiki sekali). Lanskap adalah gambar yang sudah selesai sebelumnya dan hanya dipromosikan ke slot resmi, bukan generasi baru. SVG efek menambah halo, garis cahaya dan kilau di atas avatar.2; medali asli tidak diganti. Provenance dan prompt lengkap: [manifest generasi](../../../provenance/result-ui-generation-results-20261013.json). Semua raster WebP dan alpha luar skin transparan.

`panel-hasil.svg`, `tombol-primer.svg`, `tombol-sekunder.svg`, dan `tombol-hud.svg` adalah sketsa awal yang tetap disimpan, tetapi tidak lagi menjadi skin utama preview. `kartu-statistik.svg`, `panel-bahasa.svg`, `panel-menu.svg`, `panel-detail.svg` masih dipakai. Dua kontrol fullscreen di aset-baru tetap khusus preview. Font lokal di aset-font berasal dari aplikasi asli.

## Aset katalog yang cukup dipakai ulang

Salinan berikut identik byte demi byte dengan sumber aktif. Nama salinan memakai tanda hubung untuk paket referensi; kode dan berkas resmi katalog tidak diganti.

| Kode resmi | Salinan paket | Sumber katalog | Fungsi |
|---|---|---|---|
| `ui.logo-hero` | [logo-gelita.webp](aset-katalog/logo-gelita.webp) | [sumber](../../../assets/logo/ui.logo-hero.webp) | Logo HUD; gunakan sumber, jangan membuat logotipe baru. |
| `avatar.2` | [medali-serpihan-cahaya.webp](aset-katalog/medali-serpihan-cahaya.webp) | [sumber](../../../assets/ikon/avatar.2.webp) | Visual koleksi; pertahankan medali dan kristal emas. |
| `reference.20` | [bahasa-indonesia-id.webp](aset-katalog/bahasa-indonesia-id.webp) | [sumber](../../../assets/referensi/reference.20.webp) | Referensi medali ID lama; dipertahankan, bukan ikon preview aktif. |
| `reference.19` | [bahasa-english-en.webp](aset-katalog/bahasa-english-en.webp) | [sumber](../../../assets/referensi/reference.19.webp) | Referensi medali EN lama; dipertahankan, bukan ikon preview aktif. |
| `reference.01` | [acuan-gaya-jaka-3d.webp](aset-katalog/acuan-gaya-jaka-3d.webp) | [sumber](../../../assets/referensi/reference.01.webp) | Acuan finishing 3D saja; tidak ditampilkan pada layar hasil. |
| `icon.sound` | [ikon-speaker.svg](aset-katalog/ikon-speaker.svg) | [sumber](../../../assets/ikon/icon.sound.svg) | Suara aktif di HUD. |
| `icon.sound-off` | [ikon-speaker-nonaktif.svg](aset-katalog/ikon-speaker-nonaktif.svg) | [sumber](../../../assets/ikon/icon.sound-off.svg) | Suara nonaktif di HUD. |
| `icon.menu` | [ikon-menu.svg](aset-katalog/ikon-menu.svg) | [sumber](../../../assets/ikon/icon.menu.svg) | Pemicu menu HUD. |
| `icon.home` | [ikon-home.svg](aset-katalog/ikon-home.svg) | [sumber](../../../assets/ikon/icon.home.svg) | Home/Beranda di menu; tujuan diperiksa di aplikasi. |
| `icon.user` | [ikon-profil.svg](aset-katalog/ikon-profil.svg) | [sumber](../../../assets/ikon/icon.user.svg) | Akses profil; avatar aktual mengikuti akun. |
| `icon.logout` | [ikon-keluar.svg](aset-katalog/ikon-keluar.svg) | [sumber](../../../assets/ikon/icon.logout.svg) | Keluar akun dalam menu. |
| `icon.left` | [ikon-panah-kembali.svg](aset-katalog/ikon-panah-kembali.svg) | [sumber](../../../assets/ikon/icon.left.svg) | Kembali ke peta wilayah. |
| `icon.right` | [ikon-panah-berikutnya.svg](aset-katalog/ikon-panah-berikutnya.svg) | [sumber](../../../assets/ikon/icon.right.svg) | Tombol Berikutnya. |
| `icon.down` | [ikon-chevron-bawah.svg](aset-katalog/ikon-chevron-bawah.svg) | [sumber](../../../assets/ikon/icon.down.svg) | Detail tertutup atau popover. |
| `icon.up` | [ikon-chevron-atas.svg](aset-katalog/ikon-chevron-atas.svg) | [sumber](../../../assets/ikon/icon.up.svg) | Detail terbuka. |
| `icon.star` | [ikon-bintang.svg](aset-katalog/ikon-bintang.svg) | [sumber](../../../assets/ikon/icon.star.svg) | Rating; keadaan isi/outline diatur kode. |
| `icon.clock` | [ikon-waktu.svg](aset-katalog/ikon-waktu.svg) | [sumber](../../../assets/ikon/icon.clock.svg) | Kartu waktu. |
| `icon.replay` | [ikon-percobaan.svg](aset-katalog/ikon-percobaan.svg) | [sumber](../../../assets/ikon/icon.replay.svg) | Kartu percobaan. |
| `icon.check` | [ikon-centang.svg](aset-katalog/ikon-centang.svg) | [sumber](../../../assets/ikon/icon.check.svg) | Semua benar dan bahasa terpilih. |
| `icon.cross` | [ikon-tutup.svg](aset-katalog/ikon-tutup.svg) | [sumber](../../../assets/ikon/icon.cross.svg) | Tutup menu/popover bila diperlukan. |

## Elemen yang dibangun dengan kode

- Panel navy, motif dan tombol memakai skin gambar khusus di atas. Semua label tetap elemen teks.
- Teks judul, label, nilai statistik, pesan dan nama wilayah: teks aplikasi/i18n.
- Dua kartu hasil, badge hijau, progres 12 segmen, bintang isi WebP / outline SVG: komponen dinamis.
- Popover bahasa, panel menu, Detail hasil, hover/fokus/pressed dan kilau: kode antarmuka.
- Avatar pengguna: sumber akun yang sudah ada, bukan aset avatar baru wajib.

Tidak membuat lentera atau serpihan baru: pengguna memilih medali avatar.2. Aset ref.01 hanya membantu menjaga gaya 3D; Jaka tidak ditambahkan pada halaman ini. Prompt regenerasi aset yang sudah cukup tidak diperlukan.

## Ikon bahasa terbaru — mengganti penggunaan ID/EN

| Slot | Gambar paket | Ukuran | Pemakaian |
|---|---|---|---|
| icon.language-id | [bahasa-indonesia-flag.webp](aset-katalog/bahasa-indonesia-flag.webp) | 256 × 256, alpha | Bendera Indonesia pada HUD/pilihan Bahasa Indonesia |
| icon.language-en | [bahasa-english-uk-flag.webp](aset-katalog/bahasa-english-uk-flag.webp) | 256 × 256, alpha | Bendera UK pada HUD/pilihan English |

Dua generasi baru menggunakan alas HUD sebagai acuan material. Salinan reference.19/20 pada tabel sebelumnya tetap tersimpan sebagai riwayat, tidak lagi menjadi ikon bahasa aktif preview. [Prompt dan provenance](../../../provenance/language-flag-generation-results-20261013.json).

## Penggunaan aset lintas kota

Skin ui.result-*, logo, medali, efek dan ikon bahasa dipakai bersama pada semua kota/wilayah. Temanggung pada mockup hanya contoh. Slot bg.result.temanggung merupakan contoh mapping latar wilayah; pilih latar dari aset aplikasi yang sudah tersedia sesuai konfigurasi, tanpa mewajibkan generasi baru untuk setiap kota. Label/tulisan dan hasil tidak dimasukkan ke gambar karena mengikuti konteks permainan.
