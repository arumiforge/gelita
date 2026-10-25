# Catatan Keputusan Revisi 10 Analitik

Per 23 Oktober 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md)

Catatan ini merekam keputusan selama revisi, dari yang terbaru. Keputusan untuk implementasi nanti dicatat di dokumen implementasi dan di catatan keputusan perombakan kelas 5 saat pekerjaannya dimulai.

## 25 Oktober 2026

**Keputusan pemilik proyek**

- Media game yang ada sekarang (tampilan, layar, interaksi, audio, gambar) juga terbuka untuk diubah, ditambah, dan dihapus sesuai kebutuhan Revisi 10.
- Telaah instrumen validasi ([07](07_TELAAH_INSTRUMEN_VALIDASI.md)) diterima seluruhnya:
  - B.5 dipecah menjadi B.5a (pelabelan buta, dua ahli) dan B.5b (relevansi dan kejelasan, semua ahli menilai semua butir, minimal tiga penilaian non-TD per butir).
  - Panel ahli materi lima orang, dengan matriks penugasan aspek.
  - κ berbobot untuk level AKM.
  - Perkiraan beban kerja dicantumkan di surat permohonan.
- Ahli media masuk daftar bidang validasi (daftar kerja no. 14).
- Claude berperan sebagai penelaah pendahuluan berbantuan AI, bukan validator: skornya tidak dihitung dan Claude tidak melakukan pelabelan buta. Telaah pendahuluan media dikerjakan di instalasi uji lokal.
- Belum ada data siswa asli di server. Bank soal baru boleh memakai susunan kode butir yang baru.
- Setiap tantangan yang dinilai cukup punya **dua variasi (a/b)**, bukan tiga.
- Analisis memakai **model multidimensi *between-item***: setiap butir punya tepat satu dimensi utama.
- **D1 bertumpu pada literasi membaca**, dengan dua label sub-aspek: D1a memahami informasi dan D1b menilai informasi dan sumber. Jumlah butir D1 **tidak dibatasi 29**; lebih banyak tidak masalah.
- Beberapa bacaan diubah menjadi **teks bergenre digital**.
- Kisi-kisi bank soal baru disusun di sesi terpisah, di [06_KISI_KISI_BANK_SOAL.md](06_KISI_KISI_BANK_SOAL.md) (branch `kisi-kisi-bank-soal`).
- Draf instrumen validasi ahli (r10 untuk ahli materi dan pengamat budaya, serta instrumen ahli media) ditelaah di [07_TELAAH_INSTRUMEN_VALIDASI.md](07_TELAAH_INSTRUMEN_VALIDASI.md). Versi 1.0 instrumen ahli materi sudah digantikan r10.
- Seluruh isi bank soal yang ada terbuka untuk diubah, diperbaiki, dihapus, dan ditambah sesuai kebutuhan penelitian. Bank soal baru disusun bersama Claude. Batas yang tetap berlaku hanya batas teknis penelitian: kunci jawaban tetap di server, butir baru atau yang diubah berstatus `needs_verification` sampai divalidasi, dan kode butir yang sudah punya data siswa tidak dipakai ulang untuk isi yang berbeda.

**Telaah, menunggu keputusan pemilik proyek**

- Pemilik proyek mengusulkan rumusan keputusan dimensi dan penskoran: satu dimensi utama per butir, D1 sekitar 29 butir, D2 sekitar 12 butir, D3 diubah menjadi *Partisipasi dan Representasi Budaya Kedu di Ruang Digital*, skor utama dari percobaan pertama.
- Telaah butir dan dimensi disusun di [05_TELAAH_BUTIR_DAN_DIMENSI.md](05_TELAAH_BUTIR_DAN_DIMENSI.md): 99 butir dinilai ditelaah satu per satu, ada rumusan keputusan yang sudah disesuaikan, dan daftar perbaikan butir. Rumusan itu belum berlaku sampai disetujui pemilik proyek.

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
