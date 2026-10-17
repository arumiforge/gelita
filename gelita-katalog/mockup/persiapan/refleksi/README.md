# Persiapan mockup — Refleksi dan penutup wilayah

Status **brief siap; belum ada desain visual baru**. Prioritas P2, usulan urutan kerja 14 Oktober 2026, Asia/Bangkok.

## Tujuan dan halaman asal

Bahan redesain untuk layar aplikasi `reflection.php; region-done.php` (lokasi diperiksa pada app/Views/game di aplikasi induk; tidak disalin atau diubah). Pertahankan fungsi/navigasi/state aktual saat diintegrasikan.

## Komposisi 16:9

Jaka/Mbah Kedu mendampingi pertanyaan refleksi, serpihan sebagai penanda capaian dan tombol lanjut yang jelas.

Kanvas desain 1920 × 1080; skala seragam tanpa reflow potret. HUD memakai bendera Indonesia/UK terbaru, speaker dan menu seperti konsep hasil bersama. Ukuran kontrol harus diperiksa setelah penskalaan, dengan sasaran minimal 44 px.

## Aset awal

| Kode slot | Sumber lokal | Perlakuan |
|---|---|---|
| bg.reflection | [bg.reflection](../../../assets/latar/bg.reflection.webp) | Pakai ulang; review individual saat mockup dibuat |
| char.jaka.happy.1 | [Jaka · happy · frame 1](../../../assets/karakter/char.jaka.happy.1.webp) | Pakai ulang; review individual saat mockup dibuat |
| char.kedu.smile.1 | [Mbah Kedu · smile · frame 1](../../../assets/karakter/char.kedu.smile.1.webp) | Pakai ulang; review individual saat mockup dibuat |
| avatar.2 | [Avatar Serpihan Cahaya](../../../assets/ikon/avatar.2.webp) | Pakai ulang; review individual saat mockup dibuat |

Bahan di atas adalah awal pemetaan. Panel/ornamen baru hanya dibuat setelah komposisi dipilih; kode slot baru ditentukan saat integrasi katalog. Jangan mengganti media faktual menjadi ilustrasi dekoratif tanpa dasar.

## Keadaan yang perlu diperlihatkan

- Pertanyaan
- jawaban terisi
- wilayah selesai
- English

Bahasa ID/EN, menu, speaker on/off, keyboard/fokus, loading dan error yang berlaku juga diperlihatkan dalam preview HTML. Teks mengikuti naskah/bank soal aplikasi; contoh fiktif diberi label.

## Audio

Kode untuk preview: `sfx/region-done.mp3`, `sfx/shard.mp3`. Berkas di [aset audio](../../../assets/audio/README.md). Tidak perlu narasi baru untuk mengulas visual. Suara hanya aktif setelah tindakan pengguna; volume musik lebih rendah daripada efek.

## Hasil yang harus diserahkan

- Mockup full UI WebP 1920 × 1080, bukan hanya latar.
- Preview HTML/CSS/JS dengan teks dan kontrol hidup, memakai aset relatif lokal.
- Gambar keadaan per subfolder, pemetaan aset, prompt aktual dan provenance.
- Catatan keputusan pengguna dan hasil browser; status berubah hanya sesuai pekerjaan nyata.

## Kriteria review

Teks terbaca, kontrol tidak menutupi isi, panjang English tidak memecahkan layout, warna/latar/karakter konsisten, semua berkas tersedia lokal. Uji interaksi yang berlaku dan gambar full UI; catat integrasi, budaya, lisensi dan audio yang belum ditinjau. Jangan menyatakan mockup dipilih sebelum pengguna memilihnya.

[Prompt siap pakai](prompt-detail.md) · [Arah visual](../ARAH-VISUAL.md) · [Alur produksi](../ALUR-PRODUKSI.md) · [Daftar rencana](../README.md).
