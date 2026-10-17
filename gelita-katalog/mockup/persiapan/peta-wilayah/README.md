# Persiapan mockup — Peta wilayah

Status **brief siap; belum ada desain visual baru**. Prioritas P1, usulan urutan kerja 14 Oktober 2026, Asia/Bangkok.

## Tujuan dan halaman asal

Bahan redesain untuk layar aplikasi `map-level.php; mission-brief.php` (lokasi diperiksa pada app/Views/game di aplikasi induk; tidak disalin atau diubah). Pertahankan fungsi/navigasi/state aktual saat diintegrasikan.

## Komposisi 16:9

Satu komponen lintas wilayah: titik tantangan, jalur perjalanan, ringkasan misi dan tombol masuk.

Kanvas desain 1920 × 1080; skala seragam tanpa reflow potret. HUD memakai bendera Indonesia/UK terbaru, speaker dan menu seperti konsep hasil bersama. Ukuran kontrol harus diperiksa setelah penskalaan, dengan sasaran minimal 44 px.

## Aset awal

| Kode slot | Sumber lokal | Perlakuan |
|---|---|---|
| map.region.temanggung | [Peta wilayah Temanggung](../../../assets/peta/map.region.temanggung.webp) | Pakai ulang; review individual saat mockup dibuat |
| map.region.magelang | [Peta wilayah Magelang](../../../assets/peta/map.region.magelang.webp) | Pakai ulang; review individual saat mockup dibuat |
| map.region.wonosobo | [Peta wilayah Wonosobo](../../../assets/peta/map.region.wonosobo.webp) | Pakai ulang; review individual saat mockup dibuat |
| bg.temanggung.region | [pagi, "Di Antara Dua Gunung"](../../../assets/latar/bg.temanggung.region.webp) | Pakai ulang; review individual saat mockup dibuat |
| bg.magelang.region | [fajar berkabut tipis, "Tanah Candi Agung"](../../../assets/latar/bg.magelang.region.webp) | Pakai ulang; review individual saat mockup dibuat |
| bg.wonosobo.region | [fajar dingin, "Negeri di Atas Awan"](../../../assets/latar/bg.wonosobo.region.webp) | Pakai ulang; review individual saat mockup dibuat |

Bahan di atas adalah awal pemetaan. Panel/ornamen baru hanya dibuat setelah komposisi dipilih; kode slot baru ditentukan saat integrasi katalog. Jangan mengganti media faktual menjadi ilustrasi dekoratif tanpa dasar.

## Keadaan yang perlu diperlihatkan

- Temanggung
- Magelang
- Wonosobo
- kartu misi
- tantangan terkunci

Bahasa ID/EN, menu, speaker on/off, keyboard/fokus, loading dan error yang berlaku juga diperlihatkan dalam preview HTML. Teks mengikuti naskah/bank soal aplikasi; contoh fiktif diberi label.

## Audio

Kode untuk preview: `music/region.mp3`, `sfx/lock.mp3`. Berkas di [aset audio](../../../assets/audio/README.md). Tidak perlu narasi baru untuk mengulas visual. Suara hanya aktif setelah tindakan pengguna; volume musik lebih rendah daripada efek.

## Hasil yang harus diserahkan

- Mockup full UI WebP 1920 × 1080, bukan hanya latar.
- Preview HTML/CSS/JS dengan teks dan kontrol hidup, memakai aset relatif lokal.
- Gambar keadaan per subfolder, pemetaan aset, prompt aktual dan provenance.
- Catatan keputusan pengguna dan hasil browser; status berubah hanya sesuai pekerjaan nyata.

## Kriteria review

Teks terbaca, kontrol tidak menutupi isi, panjang English tidak memecahkan layout, warna/latar/karakter konsisten, semua berkas tersedia lokal. Uji interaksi yang berlaku dan gambar full UI; catat integrasi, budaya, lisensi dan audio yang belum ditinjau. Jangan menyatakan mockup dipilih sebelum pengguna memilihnya.

[Prompt siap pakai](prompt-detail.md) · [Arah visual](../ARAH-VISUAL.md) · [Alur produksi](../ALUR-PRODUKSI.md) · [Daftar rencana](../README.md).
