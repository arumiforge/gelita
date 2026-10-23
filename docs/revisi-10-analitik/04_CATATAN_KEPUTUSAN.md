# Catatan Keputusan Revisi 10 Analitik

Per 23 Oktober 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md)

Catatan ini merekam keputusan selama revisi, dari yang terbaru. Keputusan untuk implementasi nanti dicatat di dokumen implementasi dan di catatan keputusan perombakan kelas 5 saat pekerjaannya dimulai.

## 23 Oktober 2026

**Keputusan pemilik proyek**

- Judul penelitian ditetapkan: *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*.
- Admin dan peneliti adalah dua entitas yang berbeda.
- Log lama yang tidak dibutuhkan dihapus; yang masih perlu dipisah untuk admin atau untuk peneliti.
- Skenario Digital jadi tantangan kelima di tiap wilayah (15 tantangan, 15 Serpihan Cahaya).
- Semua yang ada di game boleh dirombak total.
- Literasi digital wajib ada dalam penelitian.
- Semua rekomendasi telaah diterima.
- Guru tidak ikut dalam penelitian; tugasnya hanya menyiapkan perangkat dan internet. Laporan untuk guru cuma ide tambahan di luar penelitian.
- Belum ada implementasi di sistem. Dokumentasi revisi diselesaikan dulu; dokumen implementasi yang lengkap disusun setelahnya.
- Dokumentasi revisi disimpan di `docs/revisi-10-analitik/`.

**Usulan yang menunggu persetujuan tim** (rinciannya di dokumen utama)

- Literasi digital sebagai konstruk utama dengan tiga dimensi: D1 literasi informasi, D2 aman dan etis, D3 budaya digital Kedu. Usulan ini turunan langsung dari judul penelitian.
- Token sesi versi ringan, dibuat peneliti per pertemuan.
- Kode peserta `S03-A07` (sekolah, rombel, nomor urut).
- Pembagian log: dihapus, admin, atau peneliti, sesuai tabel di dokumen utama.
- Peran `peneliti` baru dan peran `guru` dinonaktifkan selama penelitian.
- Olah data dua lapis di sistem (mentah dan turunan), analisis statistik di luar sistem.

**Hasil cek fakta**

- Dieng Culture Festival digelar di Banjarnegara, bukan Wonosobo. Contoh skenario Wonosobo diganti ke Telaga Warna (Dieng Wetan, Kejajar, Wonosobo).
- Sampai 2025, AKM diikuti sampel siswa kelas 5. Mulai 2026 aturannya mengikuti Kepmendikdasmen Nomor 56 Tahun 2026; beberapa sumber menyebut literasi membaca kini masuk TKA Bahasa Indonesia kelas 6. Teks resminya masih perlu dicek.
- Rujukan diperbarui ke PIRLS 2026, DigComp 3.0 (2025), dan nama Komdigi.

## Titik awal: dokumen 10 analitik dari pemilik proyek

Revisi ini berangkat dari tabel berikut (dokumen *Analitik Game Gelita Terbaru*).

| No | Analitik | Data yang direkam dalam game | Informasi bagi guru (kolom asal) |
| --- | --- | --- | --- |
| 1 | Penyelesaian aktivitas | Soal yang dikerjakan, dilewati, serta level yang selesai | Sejauh mana siswa menyelesaikan kegiatan |
| 2 | Ketepatan jawaban | Jawaban siswa dan status benar–salah pada setiap soal | Indikator yang sudah atau belum dikuasai |
| 3 | Ketepatan pada percobaan pertama | Jawaban pertama sebelum mengulang atau menerima umpan balik | Gambaran kemampuan awal siswa pada tugas tersebut |
| 4 | Jumlah percobaan | Banyaknya percobaan sampai berhasil | Tugas yang membutuhkan pengulangan |
| 5 | Waktu pengerjaan | Waktu mulai dan selesai setiap soal; waktu tidak aktif dipisahkan jika memungkinkan | Tugas yang memerlukan waktu lebih lama untuk diselesaikan |
| 6 | Pola kesalahan | Pilihan jawaban salah berdasarkan kategori kesalahan yang telah ditentukan | Jenis kesulitan, misalnya salah memahami informasi atau memilih tindakan digital |
| 7 | Penggunaan bantuan | Pemutaran audio, pembukaan petunjuk, dan pengulangan instruksi | Dukungan yang digunakan siswa selama mengerjakan |
| 8 | Navigasi permainan | Perpindahan halaman, kembali ke materi, dan soal yang dilewati | Alur siswa ketika mencari informasi dan menyelesaikan tugas |
| 9 | Perubahan setelah umpan balik | Jawaban sebelum dan sesudah umpan balik | Apakah siswa berhasil memperbaiki jawabannya |
| 10 | Pilihan dan alasan pada skenario digital | Pilihan tindakan dan alasan tentang data pribadi, kata sandi, izin berbagi, dan pesan orang asing | Pemahaman siswa mengenai keamanan dan etika digital |

Karena guru tidak ikut dalam penelitian, kolom terakhir diganti "Makna bagi penelitian" di dokumen utama.
