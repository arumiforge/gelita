# Penilaian Dampak Pelindungan Data Pribadi: Penelitian GELITA

Per 27 Oktober 2026 · **Status: `disetujui pengembang`** (31 Oktober 2026; `asal: pengembang`, disusun dengan bantuan AI; belum disetujui peneliti dan belum diberi saran PPDP); nomor KG di *Kegiatan* disamakan dengan 03 dan 22, dan akibat dokumen implementasi bagian 1, 3, dan 4 (27, 29, 30) dibawa sesi koordinator 30 Oktober 2026 (`asal: pengembang`, [Akibat ke dokumen lain](#akibat-ke-dokumen-lain)) · Lampiran 18 [permohonan izin etik](22_PERMOHONAN_IZIN_ETIK.md#lampiran) · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md)

Dokumen ini adalah penilaian dampak pelindungan data pribadi (selanjutnya *penilaian dampak*) yang lengkap untuk penelitian GELITA. Ringkasannya ada di [22 → Penilaian dampak pelindungan data](22_PERMOHONAN_IZIN_ETIK.md#penilaian-dampak-pelindungan-data); dokumen ini menguraikannya untuk pembaca *Lembaga Komite Etik* dan untuk PPDP atau narahubung data. Isinya mengikuti 22 dan tidak membuat aturan baru tentang data. Kalau ada yang berbeda, 22 yang berlaku, dan bedanya dicatat di [Akibat ke dokumen lain](#akibat-ke-dokumen-lain).

Ini pemeriksaan dokumen, bukan nasihat hukum. Semua nomor pasal PP No. 33 Tahun 2026 dan UU No. 27 Tahun 2022 di dokumen ini sama dengan di 22 dan sudah dicek ke salinan naskah ([Dasar hukum](#dasar-hukum)); sebelum berkas diunggah, nomornya dicocokkan sekali lagi dengan PDF resmi (`needs_verification`).

## Daftar isi

1. [Ringkasan untuk komite](#ringkasan-untuk-komite)
2. [Identitas penilaian](#identitas-penilaian)
3. [Mengapa penilaian dampak diperlukan](#mengapa-penilaian-dampak-diperlukan)
4. [Uraian pengolahan dan tujuannya](#uraian-pengolahan-dan-tujuannya)
5. [Dasar hukum](#dasar-hukum)
6. [Inventaris data](#inventaris-data)
7. [Aliran data](#aliran-data)
8. [Kebutuhan dan proporsionalitas](#kebutuhan-dan-proporsionalitas)
9. [Hak orang tua dan anak](#hak-orang-tua-dan-anak)
10. [Penilaian risiko](#penilaian-risiko)
11. [Langkah perlindungan](#langkah-perlindungan)
12. [Risiko sisa dan simpulan](#risiko-sisa-dan-simpulan)
13. [Konsultasi](#konsultasi)
14. [Persetujuan dan tanda tangan](#persetujuan-dan-tanda-tangan)
15. [Tinjauan ulang](#tinjauan-ulang)
16. [Keputusan pengembang](#keputusan-pengembang)
17. [Akibat ke dokumen lain](#akibat-ke-dokumen-lain)

## Ringkasan untuk komite

- **Apa yang diolah.** Data sekitar 360 anak kelas 5 SD/MI (umur ± 10–11 tahun) yang bermain GELITA dan mengerjakan tes, angket, dan skala sikap dalam lima pertemuan (pengambilan data utama KG5, dengan wawancara KG4), didahului berpikir lantang (KG2) dan uji coba satu kelas (KG3) di luar sampel, ditambah empat atau lima anak di uji keterbacaan (KG1). Juga data orang tua (nama, tanda tangan, nomor HP di formulir izin), sekolah, dan tim.
- **Untuk apa.** Menilai apakah butir dan catatan permainan GELITA mengukur literasi membaca dan literasi digital anak dengan baik ([22 → Tujuan](22_PERMOHONAN_IZIN_ETIK.md#tujuan)). Data tidak dipakai untuk menilai, memberi nilai rapor, atau memutuskan apa pun tentang anak.
- **Mengapa berisiko tinggi.** Data anak termasuk data pribadi yang bersifat spesifik, dan tes, skor permainan, serta pengamatan adalah evaluasi dan penskoran yang sistematis. Dua pemicu itu mewajibkan penilaian dampak sebelum pengolahan ([Mengapa penilaian dampak diperlukan](#mengapa-penilaian-dampak-diperlukan)).
- **Risiko terbesar sebelum perlindungan:** kebocoran yang mengungkap nama anak bersama jawabannya; anak dikenali dari gabungan data walaupun namanya tidak ada; jawaban butir risiko daring (ang-21 sampai ang-23) terbaca orang lain. Ketiganya dinilai **tinggi** sebelum perlindungan ([Penilaian risiko](#penilaian-risiko)).
- **Perlindungan pokok:** nama terpisah dari data, dengan kode peserta yang hanya dibuka ketua peneliti; peran admin dan peneliti dipisah; server, cadangan, dan penyimpanan di Indonesia; enkripsi salinan; izin terpisah untuk rekaman suara dan butir risiko daring; butir risiko hanya dilaporkan sebagai angka gabungan; batas 3 × 24 jam untuk permintaan orang tua dan pemberitahuan kebocoran; nama dimusnahkan saat studi ditutup, data berkode disimpan paling singkat 20 tahun.
- **Risiko sisa:** semua **rendah**, kecuali kebocoran nama bersama jawaban dan pengenalan kembali dari gabungan data, yang tetap **sedang** karena tidak ada sistem daring yang sepenuhnya bebas kebocoran. Risiko sisa itu dinilai dapat diterima, dengan tinjauan ulang tahunan ([Risiko sisa dan simpulan](#risiko-sisa-dan-simpulan)).

## Identitas penilaian

| Hal | Isi |
| --- | --- |
| Nama kegiatan | Penelitian `[judul penelitian]` (GELITA), kegiatan KG1–KG5 ([22 → Kegiatan yang dimintakan izin](22_PERMOHONAN_IZIN_ETIK.md#kegiatan-yang-dimintakan-izin)) |
| Pengendali data | `[nama lembaga peneliti]`, diwakili ketua peneliti `[nama ketua peneliti]` |
| Prosesor data | Penyedia server IDCloudHost (PT Cloud Hosting Indonesia), pusat data di Jakarta Pusat ([22 → Keputusan pengembang](22_PERMOHONAN_IZIN_ETIK.md#keputusan-pengembang) no. 23), dengan perjanjian tertulis (PP 33/2026 Pasal 14) |
| PPDP atau narahubung | `[kontak PPDP atau narahubung]` |
| Penyusun draf | Pengembang GELITA, dibantu AI (`asal: pengembang`), 27 Oktober 2026 |
| Versi | 1.0 (`disetujui pengembang`, 31 Oktober 2026; v0.1 `draf pengembang`, 27 Oktober 2026) |
| Kapan dibuat | Sebelum permohonan diajukan (minggu kedua–keempat November 2026), jadi sebelum KG1 dan sebelum data anak pertama dikumpulkan (PP 33/2026 Pasal 120 ayat (1)) |
| Tinjauan ulang berikutnya | Bersama laporan kemajuan tahunan pertama, `[tanggal tinjauan pertama]`; selengkapnya di [Tinjauan ulang](#tinjauan-ulang) |

## Mengapa penilaian dampak diperlukan

UU No. 27 Tahun 2022 Pasal 34 dan PP No. 33 Tahun 2026 Pasal 120–121 mewajibkan pengendali membuat penilaian dampak **sebelum** pengolahan yang berpotensi berisiko tinggi bagi subjek data. Pemicu yang berlaku di penelitian ini:

| Pemicu | Berlaku? | Alasannya |
| --- | --- | --- |
| Data pribadi yang bersifat spesifik (data anak; PP Pasal 6 ayat (1) huruf e, UU Pasal 4 ayat (2) huruf e) | **Ya** | Semua peserta di bawah 18 tahun |
| Evaluasi, penskoran, atau pemantauan yang sistematis (PP Pasal 120 ayat (2) huruf b dan d) | **Ya** | Tes awal–akhir, skor per dimensi, sepuluh analitik dari catatan permainan, lembar pengamatan L3–L5 |
| Pengambilan keputusan otomatis yang berakibat hukum atau signifikan | Tidak | Skor tidak dipakai untuk memutuskan apa pun tentang anak; tidak ada nilai per anak untuk guru atau orang tua |
| Skala besar | Tidak secara tegas | ± 365 anak di beberapa sekolah satu wilayah; tidak dianggap skala besar, tetapi tetap dinilai karena dua pemicu di atas |
| Pencocokan atau penggabungan data dari beberapa sumber | Sebagian | Data sistem digabung dengan lembar lapangan L1–L6 lewat kode peserta ([11 → Aturan gabung lembar lapangan](11_KAMUS_DATA.md#aturan-gabung-lembar-lapangan)); tidak digabung dengan data dari luar penelitian |
| Teknologi baru | Sebagian | Game berbasis web yang mencatat setiap percobaan dan waktu; bukan teknologi pemantauan, pengenalan wajah, atau lokasi |
| Pemindahan ke luar negeri | Tidak | Server, cadangan, dan penyimpanan di Indonesia ([22 no. 14](22_PERMOHONAN_IZIN_ETIK.md#keputusan-pengembang)) |

Karena dua pemicu pertama berlaku, penilaian dampak ini wajib. Isinya memuat isi minimum PP Pasal 121 ayat (2): uraian kegiatan dan tujuan, kebutuhan dan proporsionalitas, risiko terhadap hak subjek data, dan langkah perlindungan, ditambah saran PPDP atau narahubung (Pasal 121 ayat (3)) dan rencana tinjauan ulang (Pasal 121 ayat (5)–(6)). Peraturan Lembaga tentang tata cara penilaian dampak (PP Pasal 122 ayat (2)) belum terbit per 27 Oktober 2026 ([22 → Dasar hukum](22_PERMOHONAN_IZIN_ETIK.md#dasar-hukum)); sampai terbit, dokumen ini mengikuti PP (Pasal 223).

## Uraian pengolahan dan tujuannya

### Kegiatan

| Kode | Kegiatan | Peserta | Data yang timbul |
| --- | --- | --- | --- |
| KG1 | Uji keterbacaan bahan anak ([24](24_UJI_KETERBACAAN.md)) | 4 anak, ditambah 1 di putaran kedua, dari sekolah Kedu di luar sampel | Lembar LK1–LK3, kartu cetak yang ditandai, waktu baca, rekaman suara (dengan izin), kode `K01`–`K05`; tidak ada akun GELITA |
| KG2 | Berpikir lantang: anak bermain sambil mengucapkan pikirannya (L5), tahap 4 | 5–8 anak di luar sampel utama | Akun GELITA dan data permainan, rekaman berpikir lantang (dengan izin terpisah), transkrip |
| KG3 | Uji coba satu kelas dengan semua lembar, lima pertemuan, tahap 4 | Satu kelas (± 28 siswa), sekolah berawalan `U` | Sama dengan KG5 |
| KG4 | Wawancara retrospektif L5 di P2–P4, bersama KG3 dan KG5 | 3 siswa per pertemuan wilayah, paling banyak sekali per siswa | Catatan wawancara L5, rekaman (dengan izin terpisah), transkrip |
| KG5 | Pengambilan data utama, P1–P5 dan sesi susulan, tahap 5 | ± 360 anak | P1: akun dan pendaftaran, tes awal, angket; P2–P4: data permainan tiga wilayah, lembar L1–L4; P5: tes akhir, skala sikap, kritik dan saran, butir risiko daring (hanya dengan izin terpisah, jawaban ang-21–23); lembar L6 |

Nomornya sama dengan [03 → Kegiatan dengan anak](03_KODE_DAN_ARTI.md#kegiatan-dengan-anak) dan [22 → Kegiatan yang dimintakan izin](22_PERMOHONAN_IZIN_ETIK.md#kegiatan-yang-dimintakan-izin) (disamakan sesi koordinator 30 Oktober 2026; draf sebelumnya memakai KG2–KG5 untuk P1, P2–P4, P5, dan uji coba, [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#4-tenggat-sebelum-akun-gelita-anak-pertama-kali-dipakai) §4). Kegiatan pertama yang memakai akun GELITA anak adalah KG2 atau KG3, mana yang lebih dulu; keduanya setelah G3. KG1 tidak memakai akun. Rinciannya ada di [22 → Prosedur per pertemuan dan beban bagi anak](22_PERMOHONAN_IZIN_ETIK.md#prosedur-per-pertemuan-dan-beban-bagi-anak).

### Tujuan pengolahan

Setiap jenis data hanya dipakai untuk tujuan di bawah ini (pembatasan tujuan):

1. **Mengukur dan memvalidasi**: skor tes, butir bank soal, dan sepuluh analitik dari catatan permainan ([11 → Sepuluh analitik](11_KAMUS_DATA.md#sepuluh-analitik)).
2. **Menjelaskan konteks**: umur, jenis kelamin, kelas, akses digital, kondisi sesi, sebagai kovariat dan deskripsi sampel.
3. **Memperbaiki bahan**: uji keterbacaan, kritik dan saran, wawancara L5.
4. **Melindungi anak**: penanda tindak lanjut butir risiko daring, hanya untuk prosedur rujukan ([22 → Prosedur rujukan](22_PERMOHONAN_IZIN_ETIK.md#prosedur-rujukan-butir-risiko-daring)).
5. **Menjalankan persetujuan dan hak**: L7, formulir izin dan pengunduran diri, daftar izin berkode, kontak orang tua.
6. **Menjaga sistem**: akun, log teknis, jejak audit, hanya untuk admin.

Yang **tidak** menjadi tujuan: menilai atau memeringkat anak, guru, atau sekolah; nilai rapor; pemasaran atau penjualan GELITA; profil anak di luar penelitian. Pemakaian untuk tujuan lain adalah perubahan protokol yang memerlukan persetujuan komite dan, kalau perlu, persetujuan orang tua yang baru.

### Pihak yang terlibat

| Pihak | Kedudukan | Yang dipegangnya |
| --- | --- | --- |
| Ketua peneliti | Mewakili pengendali; satu-satunya yang membuka kode | L7, formulir izin dan pengunduran diri, catatan rujukan, daftar nama–kode KG1; lewat akun `ketua`: data penelitian semua siswa, satu-satunya unduhan data penelitian (berkode), penanda tindak lanjut butir risiko, konfirmasi permintaan hapus |
| Tim peneliti P1–P3 (akun `koordinator`, `pengamat`, `pewawancara`, peran sistem `peneliti`) | Bagian dari pengendali, terikat pernyataan kerahasiaan | Data satu siswa lewat kodenya di panel, tanpa mengunduh; P1 juga L7, formulir izin, dan izin berkode di panel |
| Pengembang dan admin (akun `admin`) | **Bagian dari tim pengendali**, terikat pernyataan kerahasiaan, bukan prosesor terpisah ([keputusan no. 1](#keputusan-pengembang)) | Akun GELITA (nama, nama pengguna), log teknis, jejak audit; tidak melihat dan tidak mengunduh data penelitian |
| Pengode transkrip | Bagian dari tim, terikat pernyataan kerahasiaan | Rekaman dan transkrip berkode |
| Penyedia server | Prosesor (PP Pasal 13–15) | Server dan salinan cadangan, tanpa akses ke isi data kecuali untuk menjalankan layanan |
| Sekolah dan guru kelas | Bukan pengendali bersama | Membagikan dan mengumpulkan amplop; tidak menerima data per anak |
| Penerima data berkode (atas permintaan, setelah analisis) | Pengendali tersendiri untuk salinannya | Data berkode yang sudah dibersihkan, dengan perjanjian pemakaian data ([22 → Yang dibagikan](22_PERMOHONAN_IZIN_ETIK.md#yang-dibagikan-di-osf-dan-yang-tidak-pernah)) |

## Dasar hukum

Sama dengan [22 → Dasar hukum](22_PERMOHONAN_IZIN_ETIK.md#dasar-hukum). Nomor pasal PP 33/2026 dicek ke transkripsi salinan naskah, dan nomor pasal UU 27/2022 (termasuk Pasal 4 ayat (2) huruf e dan Pasal 25) ke naskah anotasi Mahkamah Konstitusi, pada 27 Oktober 2026 di 22; dokumen ini memakai nomor yang sama (diselaraskan sesi koordinator 30 Oktober 2026). Yang tersisa **`needs_verification`**: pencocokan sekali lagi dengan PDF resmi JDIH atau peraturan.go.id sebelum berkas diunggah.

| Hal | Rujukan | Penerapan |
| --- | --- | --- |
| Peraturan pokok | UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi; PP No. 33 Tahun 2026 (berlaku 16 Januari 2027, Pasal 225) | KG1–KG5 dimulai setelah persetujuan etik terbit (target akhir 2027), jadi PP berlaku penuh |
| Pedoman etik | Panduan Klirens Etik Riset Bidang Sosial Humaniora BRIN (2025) sebagai pedoman utama, butir 3.5–3.6; PSEPPKN 2021 sebagai cadangan, standar 6 | Penilaian dampak ini menjawab matriks 3.5–3.6 formulir BRIN |
| Data anak sebagai data spesifik | PP Pasal 1 angka 21, Pasal 6 ayat (1) huruf e; UU Pasal 4 ayat (2) huruf e | Seluruh data anak diperlakukan sebagai data spesifik |
| Dasar pengolahan | Persetujuan yang sah secara eksplisit (PP Pasal 30 ayat (3) huruf a, Pasal 32), dari orang tua atau wali untuk anak (PP Pasal 38 ayat (2); UU Pasal 25) | Formulir T2 (dan T8b untuk KG1); centang terpisah untuk rekaman suara dan butir risiko daring. Persetujuan lisan anak (T3) dan centang di halaman daftar (T4) adalah persetujuan etik anak, bukan dasar hukum |
| Informasi sebelum persetujuan | PP Pasal 33, 62 | Surat T1 sebelum formulir T2 |
| Bukti persetujuan | PP Pasal 36 | Formulir T2, lalu daftar izin berkode setelah L7 dimusnahkan |
| Verifikasi persetujuan orang tua | PP Pasal 38 ayat (4) | Amplop lewat guru kelas, pencocokan nama dan tanda tangan oleh P1, telepon kalau ragu |
| Hak subjek data | PP Pasal 38 ayat (5)–(7), 70–71, 76–78, 82, 87, 92 | [Hak orang tua dan anak](#hak-orang-tua-dan-anak) |
| Pengecualian untuk penelitian | PP Pasal 148 ayat (1) huruf e, 149, 154; UU Pasal 15 | **Tidak dipakai** ([22 no. 17](22_PERMOHONAN_IZIN_ETIK.md#keputusan-pengembang)) |
| Prosesor | PP Pasal 13–15, 88, 118 | Perjanjian tertulis dengan penyedia server |
| Catatan kegiatan pengolahan, kebijakan masa simpan | PP Pasal 74, 75 | Disimpan ketua peneliti, diperlihatkan kalau diminta |
| Penilaian dampak | UU Pasal 34; PP Pasal 120–121 | Dokumen ini |
| Kebocoran | UU Pasal 46; PP Pasal 1 angka 18, 114–118 | Pemberitahuan tertulis 3 × 24 jam ([22 → Langkah saat kejadian](22_PERMOHONAN_IZIN_ETIK.md#langkah-saat-kejadian)) |
| PPDP | UU Pasal 53; PP Pasal 142–147 | `[kontak PPDP atau narahubung]`; kewajiban menunjuk PPDP sendiri menunggu Peraturan Lembaga, yang belum terbit per 27 Oktober 2026 |
| Pemindahan ke luar negeri | UU Pasal 56; PP Bab V, Pasal 160–162 | Tidak ada |
| Pemusnahan | PP Pasal 86 | Berita acara di catatan ketua peneliti |
| Masa simpan data penelitian | UU No. 11 Tahun 2019 Pasal 40 ayat (5) (teks dicek 27 Oktober 2026) | Data berkode paling singkat 20 tahun |
| Satuan waktu | PP Pasal 1 angka 19 ("hari" = hari kerja) | Batas "3 × 24 jam" ditulis dalam jam, jadi definisi hari kerja tidak berlaku untuknya; dihitung 3 × 24 jam kalender, tafsiran paling aman, sama dengan 22 |

## Inventaris data

Semua data di bawah ini diambil dari [22 → Data yang dikumpulkan](22_PERMOHONAN_IZIN_ETIK.md#data-yang-dikumpulkan), [01 → Log: dihapus atau dipisah](01_REVISI_10_ANALITIK.md#log-dihapus-atau-dipisah), [11 → Variabel peserta dan sesi](11_KAMUS_DATA.md#variabel-peserta-dan-sesi), dan skema database (`docs/01_DATABASE.md`). Kolom *Kepekaan*: **T** tinggi (langsung mengenali anak atau menyangkut hal pribadi), **S** sedang (bisa mengenali kalau digabung), **R** rendah (tidak mengenali sendiri).

### Anak

| No | Data | Contoh | Sumber | Tempat | Yang bisa melihat | Kepekaan | Masa simpan |
| --- | --- | --- | --- | --- | --- | --- | --- |
| A1 | Identitas | Nama lengkap, nama pengguna | Pendaftaran; L7 | Akun GELITA; L7 kertas dan salinan digitalnya | Admin (akun); P1 dan ketua peneliti (L7) | T | Sampai studi ditutup; nama di akun diganti kode |
| A2 | Kode peserta dan sekolah | `S03-A07`, kode sekolah, rombel | Sistem | Server; semua lembar | Tim | S | Kode: bersama data berkode; pemetaan NPSN–kode sampai studi ditutup |
| A3 | Data pendaftaran | Umur, jenis kelamin, kelas; metrik kata sandi saat daftar (jumlah syarat terpenuhi, jumlah penolakan, bukan kata sandinya) | Pendaftaran | Server | Peneliti, dengan kode | S | ≥ 20 tahun |
| A4 | Kata sandi | Hash kata sandi | Pendaftaran | Server | Tidak ada orang (hanya sistem) | T | Bersama akun |
| A5 | Data permainan | Jawaban, percobaan, kode kesalahan, alasan di Skenario Digital, bantuan, navigasi, waktu, `page_hidden`/`page_visible` | Sistem | Server | Peneliti, dengan kode | R | ≥ 20 tahun |
| A6 | Tes awal–akhir, angket akses digital (ang-1–20), skala sikap, kritik dan saran | Jawaban di modul tes dan Balai Refleksi | Sistem | Server | Peneliti, dengan kode | S (angket akses menyangkut keadaan rumah) | ≥ 20 tahun |
| A7 | **Butir risiko daring** (ang-21–23) | Pengalaman tidak menyenangkan di internet | Sistem, hanya dengan izin terpisah | Server | Peneliti: angka gabungan saja; penanda tindak lanjut hanya akun `ketua`, tidak masuk ekspor | **T** | Jawaban: ≥ 20 tahun berkode, tidak pernah dibagikan; penanda: tidak disimpan, dihitung saat halaman `ketua` dibuka sampai studi ditutup ([30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#6-penanda-tindak-lanjut-butir-risiko-daring) §6, K7) |
| A8 | Lembar lapangan L1–L6 | Kursi, perangkat, bantuan, perilaku, kondisi sesi, catatan wawancara (tanpa kutipan ucapan L5 dan kolom token L1) | Peneliti | Kertas, lalu templat dalam 24 jam, lalu diunggah ke server (berkode, tanpa nama; [29](29_IMPLEMENTASI_DATA_PENELITIAN.md#104-mengunggah-lembar-yang-sudah-diketik) §10.4) | Peneliti, dengan kode; isi yang tersimpan di server hanya lewat unduhan `ketua`, tidak dilihat admin | S | ≥ 20 tahun |
| A9 | Rekaman suara | Wawancara L5, berpikir lantang, KG1 | Perekam atau ponsel, dengan izin terpisah | Penyimpanan terenkripsi hari itu juga | P3, dua pengode; di KG1 pemandu, pencatat, ketua peneliti | **T** (suara bisa mengenali) | Sampai transkrip diperiksa, lalu dihapus |
| A10 | Transkrip | Ucapan anak, nama diganti `[nama]` | Pengode | Folder terenkripsi | Sama dengan A9 | S | ≥ 20 tahun berkode; tidak pernah dibagikan |
| A11 | Uji keterbacaan (KG1) | LK1–LK3, kartu cetak, waktu baca, kutipan parafrase; kode `K01`–`K05`; tanpa jawaban tentang diri sendiri | Pemandu, pencatat | Map terkunci; ketikan di folder tim | Pemandu, pencatat, ketua peneliti | S | ≥ 20 tahun berkode |
| A12 | Catatan perangkat sesi | Jenis perangkat, ukuran layar (peneliti); nama dan versi OS dan peramban, ukuran jendela, orientasi (admin, halaman *Log teknis*). Tanpa user agent, client hints, merek, model, rasio piksel, titik sentuh, zona waktu, dan bahasa peramban; data lama ikut dihapus di migrasi ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md#55-rincian-perangkat) §5.5, K3) | Sistem | Server | Lihat kolom contoh | R | Bersama data sesi |
| A13 | Catatan rujukan | Tindak lanjut butir risiko atau cerita anak yang mengkhawatirkan | Ketua peneliti | Berkas terpisah, terkunci atau terenkripsi | Ketua peneliti | **T** | Sampai studi ditutup |
| A14 | Status persetujuan | Izin orang tua, izin rekaman, izin butir risiko (dengan tanggal), persetujuan anak, status mundur | L7 | L7; izin berkode di server (diisi P1 dari L7, tanpa nama; [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#52-izin-berkode-per-siswa) §5.2); daftar izin berkode yang diunduh dari sistem | P1 dan ketua peneliti (L7); P1–P3 dan ketua peneliti (izin berkode di panel, 30 §14); admin hanya melihat akun aktif atau nonaktif | S | L7 sampai studi ditutup; izin berkode di server dan daftar berkode selama data berkode disimpan |

### Orang tua atau wali

| No | Data | Tempat | Yang bisa melihat | Kepekaan | Masa simpan |
| --- | --- | --- | --- | --- | --- |
| O1 | Nama, hubungan dengan anak, tanda tangan atau cap jempol, nama dan tanda tangan saksi (kalau surat dibacakan) | Formulir T2, T8b, T10 | P1, ketua peneliti | T | Sampai studi ditutup |
| O2 | Nomor HP (tidak wajib) | Formulir T2, T10; pesan WA atau surel permintaan mundur | P1, ketua peneliti | T | Sampai studi ditutup; tidak masuk sistem GELITA atau data penelitian |
| O3 | Isi permintaan (mundur, hapus, akses, pembetulan) dan jawabannya | Formulir T10, catatan ketua peneliti | Ketua peneliti | S | Sampai studi ditutup; berita acara pemusnahan tanpa nama |

### Sekolah

| No | Data | Tempat | Yang bisa melihat | Kepekaan | Masa simpan |
| --- | --- | --- | --- | --- | --- |
| K1 | Nama sekolah, NPSN, alamat sampai desa/kelurahan dan dusun | Sistem (`schools`, admin), pemetaan NPSN–kode, formulir BRIN butir 2.5 dan usulan penelitian | Admin, P1, ketua peneliti; lokasi lengkap juga komite | S (bisa mempersempit pengenalan anak) | Pemetaan sampai studi ditutup; nama sekolah tidak pernah di laporan atau publikasi |
| K2 | Profil sekolah L8 (letak, jumlah kelas 5, perangkat, internet, listrik) | L8 | Peneliti, dengan kode sekolah | R | Bersama data berkode |
| K3 | Nama dan tanda tangan kepala sekolah dan guru kelas | Surat T6, lembar T7 | P1, ketua peneliti | S | Sampai studi ditutup |

### Tim dan staf

| No | Data | Tempat | Yang bisa melihat | Kepekaan | Masa simpan |
| --- | --- | --- | --- | --- | --- |
| S1 | Akun staf: nama pengguna peran (`admin`, `koordinator`, `pengamat`, `pewawancara`, `ketua`) dengan tiga peran sistem (`admin`; `peneliti` untuk P1–P3; `ketua`, [27](27_IMPLEMENTASI_PERAN_PENELITI.md#2-keputusan-yang-dikunci) K1), surel, nama tampilan, hash kata sandi, waktu masuk terakhir | Server (`staff_users`) | Admin | S | Akun dinonaktifkan, bukan dihapus; siapa pemegangnya dicatat ketua peneliti di luar repo |
| S2 | Jejak audit: siapa melakukan apa di panel (masuk, reset sandi, ekspor, penghapusan), dengan `ip_hash` (SHA-256 IP + garam) | Server (`audit_logs`) | Admin; `ketua` melihat riwayat unduhan (hanya `ketua` yang mengunduh) | S | Bersama data studi |
| S3 | Identitas tim untuk formulir etik (nama, pendidikan, kepakaran, CV, sertifikat pelatihan etik, pernyataan kerahasiaan) | Berkas permohonan | Komite, ketua peneliti | S | Mengikuti aturan komite |

### Log teknis dan sesi

| No | Data | Tempat | Yang bisa melihat | Kepekaan | Catatan |
| --- | --- | --- | --- | --- | --- |
| L1 | Log teknis: `viewport_changed`, `package_changed`, `clock_offset_ms`, `events_dropped`, `password_changed`, waktu mentah, dan kolom perangkat bertanda Admin (A12), tanpa rincian perangkat yang dihapus (L4) | Server | Admin, di halaman *Log teknis* tanpa jawaban, skor, atau event penelitian; jumlah event hilang tampil di panel peneliti | R | [01 → Log](01_REVISI_10_ANALITIK.md#log-dihapus-atau-dipisah), [29](29_IMPLEMENTASI_DATA_PENELITIAN.md#56-data-bertanda-admin-dan-halaman-log-teknis) §5.6 |
| L2 | `ip_hash` di sesi dan log perangkat | Server | Admin | S | IP mentah tidak disimpan di tabel domain |
| L3 | **IP mentah di tabel sesi peramban** (`ci_sessions.ip_address`) | Server | Admin, sampai sesi kedaluwarsa dibersihkan | S | Penangan IP berhash sudah ada tetapi belum dipasang; diperbaiki sebelum KG2 ([keputusan no. 2](#keputusan-pengembang)) |
| L4 | Log yang dihapus di revisi: `answer_changed`, `answer_selected`, `audio_pause`, `dialogue_advanced`, rincian perangkat (merek, model, user agent, client hints, rasio piksel, titik sentuh, zona waktu, bahasa peramban) | — | — | — | Dihapus, bukan disembunyikan (01 → *Log*); perubahan sistem tahap 3 |

### Yang tidak dikumpulkan

Foto, video, lokasi perangkat, nomor HP anak, penghasilan, pekerjaan atau pendidikan orang tua, agama, suku, kesehatan ([16 → Yang sengaja tidak ditanyakan](16_ANGKET_AKSES_DIGITAL.md#yang-sengaja-tidak-ditanyakan)). Tidak ada papan peringkat, iklan, pelacak pihak ketiga, atau masuk lewat akun media sosial.

## Aliran data

Gambaran dari formulir sampai pemusnahan. Garis putus memisahkan **sisi identitas** (nama) dan **sisi penelitian** (kode); yang menyeberangi garis hanya ketua peneliti, untuk tiga keperluan di [22 → Kode peserta](22_PERMOHONAN_IZIN_ETIK.md#kode-peserta-dan-pemisahan-peran).

```text
 SISI IDENTITAS (nama)                         SISI PENELITIAN (kode saja)
 ─────────────────────                         ──────────────────────────
 Orang tua ── T1 surat ──> rumah
           <── T2 formulir (amplop) ── guru kelas ── P1
                    │
                    ▼
            L7 kertas + salinan terenkripsi
            (P1, ketua peneliti)
                    │ status izin, kode
 - - - - - - - - - -│- - - - - - - - - - - - - - - - - - - - - - - - - - -
                    ▼
 Anak daftar di GELITA ──> akun (nama, admin) ═══> kode peserta S03-A07
                                                  │
                    game, tes, angket, skala ─────┤──> server di Indonesia
                    (P1–P5, token sesi)           │    IDCloudHost, Jakarta Pusat
                                                  │        │ cadangan (di Indonesia)
                    lembar L1–L6 (diunggah) ──────┤        ▼
                    rekaman L5 → transkrip ───────┤    unduhan ketua (dihapus di server
                                                  │    setelah 7 hari) → penyimpanan
                                                  │    terenkripsi tim (di Indonesia)
                                                  ▼
                                    analisis → hasil gabungan → laporan, OSF
                                    data berkode yang dibersihkan → penerima
                                    (atas permintaan, persetujuan komite)

 Studi ditutup: nama, L7, formulir, pemetaan NPSN–kode, catatan rujukan
 dimusnahkan; nama dan sekolah di akun diganti kode; cadangan diganti;
 tersisa daftar izin berkode
 dan data berkode (paling singkat 20 tahun).
```

| Langkah | Data | Dari → ke | Saluran | Perlindungan |
| --- | --- | --- | --- | --- |
| 1 | Surat T1, formulir T2 | Peneliti → orang tua → guru → P1 | Amplop tertutup (H-14 sampai H-3) | Guru tidak membuka amplop; formulir yang tidak kembali = tidak ada izin |
| 2 | L7 | P1 | Kertas, lalu salinan digital | Lemari terkunci; laptop terenkripsi; hanya P1 dan ketua peneliti |
| 3 | Pendaftaran | Anak → server | HTTPS | Nama hanya terlihat admin; persetujuan anak (T4) dicatat `assented_at` |
| 4 | Data game, tes, angket, skala | Perangkat → server | HTTPS, token sesi lapangan | Game tertutup di luar sesi; kunci jawaban tidak dikirim ke peramban; butir risiko hanya tampil untuk anak dengan izin |
| 5 | Lembar L1–L6 | Peneliti → templat → server | Kertas, diketik ke templat terisi per sesi dalam 24 jam, lalu diunggah lewat panel (HTTPS) | Hanya kode dan nomor kursi; kutipan L5 dan token L1 tidak diunggah; peringatan bila isian memuat kata yang sama dengan nama siswa rombel itu, dikonfirmasi pengunggah sebelum disimpan (29 K13); isinya hanya di unduhan `ketua` |
| 6 | Rekaman L5, berpikir lantang, KG1 | Perekam → penyimpanan terenkripsi | Dipindah hari itu juga | Dihapus setelah transkrip diperiksa; nama di transkrip jadi `[nama]` |
| 7 | Ekspor | Server → ketua peneliti | Unduhan lewat akun `ketua` saja, selalu berkode, tercatat di jejak audit dan riwayat unduhan (27 K14) | Berkas di server terhapus otomatis setelah 7 hari; salinan di penyimpanan terenkripsi di Indonesia |
| 8 | Cadangan | Server → tempat cadangan (dua lokasi) | Skrip cadangan; perjanjian prosesor untuk snapshot penyedia | Di Indonesia; 30 hari di kedua lokasi; diganti setelah setiap penghapusan dan saat studi ditutup, sebelum orang tua diberi tahu ([30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#75-salinan-cadangan) §7.5, K3) |
| 9 | Hasil | Peneliti → sekolah, publikasi, OSF | Laporan | Angka gabungan, tidak untuk kelompok < 5 siswa; tanpa nama sekolah |
| 10 | Data berkode atas permintaan | Peneliti → penerima | Perjanjian pemakaian data | Kode acak baru, tanpa kode sekolah dan rombel, umur dikelompokkan, tanpa butir risiko dan transkrip |
| 11 | Pemusnahan | — | Saat studi ditutup; ≥ 20 tahun untuk data berkode | Berita acara (PP Pasal 86) |

Catatan kegiatan pengolahan (PP Pasal 74) memakai tabel inventaris dan aliran ini, ditambah daftar pemegang akun yang dicatat ketua peneliti di luar repo.

## Kebutuhan dan proporsionalitas

Setiap data diuji dengan tiga pertanyaan: dipakai untuk tujuan apa, bisakah tujuan itu dicapai dengan data yang lebih sedikit, dan apakah gangguannya sebanding.

| Data | Perlu untuk | Bisa lebih sedikit? | Simpulan |
| --- | --- | --- | --- |
| Nama anak (A1) | Masuk akun, mencocokkan izin, menjalankan hak, rujukan | Tidak sepenuhnya: tanpa nama, izin tidak bisa dicocokkan dan permintaan orang tua tidak bisa dijalankan. Tetapi nama dikurung di akun dan berkas izin, dan dimusnahkan saat studi ditutup | Perlu, dibatasi |
| Umur, jenis kelamin, kelas (A3) | Kovariat dan deskripsi sampel | Umur dikelompokkan saat dibagikan | Perlu |
| Metrik kata sandi (A3) | Bukti tambahan dimensi D2 (aman dan etis) | Kata sandinya sendiri tidak disimpan, hanya jumlah syarat dan penolakan | Perlu, sudah minimal |
| Data permainan (A5) | Sepuluh analitik | Log yang tidak menjawab analitik **dihapus**: pilihan sebelum Cek, jeda narasi, geser slide, rincian perangkat ([01 → Log](01_REVISI_10_ANALITIK.md#log-dihapus-atau-dipisah)) | Perlu, sudah diperkecil |
| Angket akses digital (A6) | Kovariat akses | Tanpa penghasilan, pekerjaan orang tua, agama, suku | Perlu, sudah minimal |
| Butir risiko daring (A7) | Konteks D2 dan perlindungan anak | Tidak wajib: hanya dengan izin terpisah, ditanyakan ulang di layar, pilihan *Tidak mau menjawab*; peneliti hanya melihat angka gabungan | Sebanding karena dibatasi; satu-satunya bagian yang sedikit di atas risiko minimal ([22 → Risiko dan mitigasi](22_PERMOHONAN_IZIN_ETIK.md#risiko-dan-mitigasi)) |
| Lembar L1–L6 (A8) | Penjelas data sistem (bantuan, kondisi sesi) | Hanya kode dan nomor kursi | Perlu |
| Rekaman suara (A9) | Wawancara L5 dan berpikir lantang, keterbacaan | Tidak wajib, izin terpisah; dihapus setelah transkrip diperiksa | Sebanding |
| Nomor HP orang tua (O2) | Memperjelas formulir, memberi tahu setelah permintaan dijalankan atau kebocoran | Tidak wajib; tidak masuk sistem | Sebanding |
| NPSN dan alamat sekolah (K1) | Izin sekolah, formulir BRIN butir 2.5 | Tidak masuk data peneliti; tidak pernah di publikasi | Perlu, dibatasi |
| Jenis perangkat, ukuran layar (A12) | Kovariat | Merek, model, user agent dihapus | Perlu, sudah minimal |
| IP mentah di tabel sesi (L3) | Tidak perlu untuk penelitian | **Ya**: penangan IP berhash dipasang sebelum KG2 | Belum sebanding; diperbaiki ([keputusan no. 2](#keputusan-pengembang)) |

**Masa simpan juga diuji.** Nama dan kunci kode hanya disimpan selama hak orang tua masih bisa dijalankan, yaitu sampai studi ditutup. Data berkode disimpan paling singkat 20 tahun karena UU No. 11 Tahun 2019 Pasal 40 ayat (5) mewajibkannya, tanpa nama. Rekaman dihapus secepat mungkin. Ini sebanding dengan tujuannya ([22 → Masa simpan dan pemusnahan](22_PERMOHONAN_IZIN_ETIK.md#masa-simpan-dan-pemusnahan)).

**Kualitas data.** Umur, jenis kelamin, dan kelas bisa dibetulkan orang tua lewat permintaan pembetulan; pembetulan oleh admin dengan jejak audit dicatat sebagai perubahan sistem tahap 3 (01, perubahan no. 23).

## Hak orang tua dan anak

Sama dengan [22 → Mengundurkan diri dan hapus data](22_PERMOHONAN_IZIN_ETIK.md#mengundurkan-diri-dan-hapus-data). Pengecualian penelitian tidak dipakai, jadi semua hak berlaku sampai studi ditutup.

| Hak | Cara | Batas waktu | Siap di sistem? |
| --- | --- | --- | --- |
| Informasi | Surat T1 (dan T8a untuk KG1) sebelum formulir | Sebelum persetujuan | Ya (kertas) |
| Menarik persetujuan, berhenti | Formulir T10 pilihan A, B, atau C, atau pesan tertulis | Pengumpulan dihentikan ≤ 3 × 24 jam setelah terverifikasi | Akun dinonaktifkan: ya; di bagian 4 akun langsung nonaktif saat P1 menyimpan status mundur ([30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#53-status-mundur-dan-akun-nonaktif) §5.3) |
| Hapus | T10 pilihan B (semua) atau C (rekaman dan/atau butir risiko) | Orang tua diberi tahu ≤ 3 × 24 jam setelah penghapusan | B: ya, penghapusan dua langkah; C (sebagian): tahap 3, dirancang di 30 §7.2. Permintaan dari peneliti dikonfirmasi `ketua` sebelum admin menjalankan; cadangan diganti (30 §7.1, §7.5). Pada pilihan C, transkrip ikut dihapus bersama rekaman (30 §22 no. 5) |
| Akses dan salinan | Lewat kontak peneliti atau P1 | Dijawab ≤ 3 × 24 jam | Ambil data satu anak lewat kode: tahap 3, dirancang di 30 §8; jawaban butir risiko diberikan setelah anak ditanya, bila anak setuju atau orang tua tetap meminta, kecuali membahayakan anak (UU No. 27 Tahun 2022 Pasal 7, 32; 30 §22 no. 6; pasal sejenis di PP 33/2026 `needs_verification`) |
| Pembetulan | Sama | Dijawab ≤ 3 × 24 jam | Pembetulan oleh admin dengan jejak audit: tahap 3, dirancang di 30 §9 |
| Mengadu | Ke lembaga pelindungan data pribadi; ke komite | — | Kontak di T1 |
| Diberi tahu kalau ada kebocoran | Pemberitahuan tertulis | ≤ 3 × 24 jam | Prosedur di 22 |

Anak boleh mengajukan sendiri; pemenuhannya mempertimbangkan kemampuan anak memahami akibatnya dan memerlukan persetujuan orang tua (PP Pasal 38 ayat (5)–(7)). Anak yang menyatakan ingin berhenti dicatat sebagai pilihan A hari itu. Setelah studi ditutup, data tidak bisa lagi dihubungkan dengan anak, jadi hak hapus, akses, dan pembetulan tidak bisa lagi dijalankan; ini disebut di surat T1.

Tiga fungsi yang belum ada di sistem (hapus sebagian, ambil data satu anak, pembetulan dengan jejak audit) harus selesai di tahap 3, **sebelum KG2**, yaitu sebelum kegiatan pertama yang memakai akun GELITA anak (KG2 atau KG3, mana yang lebih dulu; [Kegiatan](#kegiatan)). Rancangannya di [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md) §7–§9. Kalau belum selesai, admin menjalankannya secara manual dengan berita acara, dan ini dicatat sebagai risiko R8 di bawah.

## Penilaian risiko

### Cara menilai

Risiko dinilai dari sudut **anak dan orang tua** sebagai subjek data, bukan dari sudut penelitian. Setiap risiko dinilai dua kali: **sebelum perlindungan** (risiko bawaan) dan **sesudah perlindungan** (risiko sisa).

| Kemungkinan | Arti |
| --- | --- |
| Kecil | Tidak diperkirakan terjadi selama penelitian |
| Sedang | Bisa terjadi sekali selama penelitian |
| Besar | Diperkirakan terjadi lebih dari sekali |

| Keparahan | Arti bagi anak |
| --- | --- |
| Ringan | Rasa tidak nyaman sebentar, tanpa akibat lanjutan |
| Sedang | Malu, cemas, atau perlakuan berbeda dari guru atau teman, yang bisa dipulihkan |
| Berat | Anak dikenali bersama hal pribadi (misalnya pengalaman buruk di internet), dirundung, dihukum, atau dirugikan dalam waktu lama |

| | Ringan | Sedang | Berat |
| --- | --- | --- | --- |
| **Besar** | Sedang | Tinggi | Tinggi |
| **Sedang** | Rendah | Sedang | Tinggi |
| **Kecil** | Rendah | Rendah | Sedang |

### Daftar risiko

| No | Risiko bagi anak | Kemungkinan bawaan | Keparahan | Risiko bawaan | Perlindungan (no. di [Langkah perlindungan](#langkah-perlindungan)) | Kemungkinan sisa | Risiko sisa |
| --- | --- | --- | --- | --- | --- | --- | --- |
| R1 | **Kebocoran yang mengungkap nama bersama jawaban**: server diretas, akun staf dibobol, ekspor atau laptop lapangan hilang, sehingga nama anak terbaca bersama skor atau jawabannya | Sedang | Berat | **Tinggi** | P1, P2, P3, P4, P5, P6, P12 | Kecil | Sedang |
| R2 | **Pengenalan kembali dari gabungan data**: anak dikenali dari kode sekolah, rombel, umur, jenis kelamin, nomor kursi, atau kutipan, walaupun namanya tidak ada (PP Pasal 7 ayat (2), triangulasi) | Sedang | Berat | **Tinggi** | P1, P8, P9, P10 | Kecil | Sedang |
| R3 | **Jawaban butir risiko daring terbaca orang lain** (teman di sebelahnya, guru, peneliti selain ketua) | Sedang | Berat | **Tinggi** | P7, P8, P2 | Kecil | Rendah (keparahannya turun ke sedang karena hanya angka gabungan yang dilihat peneliti) |
| R4 | Kebocoran berkas izin kertas (L7, T2, T10): nama anak, nama dan nomor HP orang tua tersebar | Kecil | Sedang | Rendah | P3, P5 | Kecil | Rendah |
| R5 | **Data dipakai di luar tujuan**: skor dipakai guru untuk menilai atau menghukum anak; sekolah dibandingkan; data dipakai untuk memasarkan GELITA | Sedang | Sedang | Sedang | P2, P9, P11 | Kecil | Rendah |
| R6 | **Rekaman suara** tersimpan lebih lama dari perlu, atau suara anak dikenali | Sedang | Sedang | Sedang | P7, P13 | Kecil | Rendah |
| R7 | Data berkode yang dibagikan ke peneliti lain dipakai untuk mengenali anak, atau dipindah ke luar negeri | Kecil | Berat | Sedang | P10, P6 | Kecil | Rendah |
| R8 | **Permintaan orang tua tidak dijalankan tepat waktu** (hapus, hapus sebagian, akses, pembetulan), termasuk karena fungsi sistemnya belum ada atau cadangan lupa dihapus (dikurangi penggantian cadangan di P14) | Sedang | Sedang | Sedang | P14, P15 | Kecil | Rendah |
| R9 | Kebocoran tidak diketahui atau tidak diberitahukan dalam 3 × 24 jam | Sedang | Sedang | Sedang | P12, P4 | Kecil | Rendah |
| R10 | Persetujuan tidak sah: tanda tangan dipalsukan anak, formulir tertukar, atau anak bermain sebelum izin lengkap | Sedang | Sedang | Sedang | P16 | Kecil | Rendah |
| R11 | Akun anak dipakai orang lain, atau anak bermain di luar sesi dan datanya tercampur | Kecil | Ringan | Rendah | P17 | Kecil | Rendah |
| R12 | Prosesor (penyedia server) mengolah di luar perintah, memakai subprosesor tanpa izin, atau menyimpan cadangan di luar negeri | Kecil | Berat | Sedang | P6 | Kecil | Rendah |
| R13 | IP mentah di tabel sesi peramban dipakai untuk menebak sekolah atau rumah anak | Sedang | Ringan | Rendah | P18 | Kecil | Rendah |
| R14 | Data disimpan lebih lama dari perlu (nama tidak dimusnahkan saat studi ditutup, atau tetap ada di cadangan lama) atau dimusnahkan terlalu cepat (melanggar kewajiban simpan 20 tahun) | Sedang | Sedang | Sedang | P13, P14, P15 | Kecil | Rendah |

**Risiko yang dinilai tinggi sebelum perlindungan: R1, R2, dan R3.** Ketiganya berasal dari hal yang sama: data penelitian anak menyangkut kemampuan dan, untuk sebagian anak, pengalaman pribadi di internet, sehingga kalau terhubung kembali dengan nama, akibatnya bagi anak bisa berat dan lama. Karena itu ketiganya mendapat perlindungan berlapis.

## Langkah perlindungan

Semua langkah di bawah sudah diputuskan di 22 atau dokumen yang dirujuknya, kecuali yang ditandai *baru di 26*. Kolom *Siap* menunjukkan keadaan per 27 Oktober 2026: **ada** (sudah di sistem atau prosedur), **tahap 3** (perubahan sistem yang dikerjakan setelah gerbang G2, sebelum KG2), atau **sebelum KG1** (dokumen atau tindakan pengendali).

| No | Langkah | Mengurangi | Rujukan | Siap |
| --- | --- | --- | --- | --- |
| P1 | **Kode peserta (pseudonim).** Kode `S03-A07` dibuat sistem saat anak mendaftar; nama tidak pernah ada di data penelitian; kunci kode adalah L7 dan akun yang dikelola admin; kode hanya dibuka ketua peneliti untuk tiga keperluan, dengan tanggal dan alasan dicatat. Ini pembatasan prosedur: secara teknis P1 memegang L7 dan admin memegang akun, jadi yang menjaganya pernyataan kerahasiaan dan larangan tertulis ([27](27_IMPLEMENTASI_PERAN_PENELITI.md#16-pertanyaan-terbuka) no. 6). Lembar lapangan yang diunggah diperiksa: kata yang sama dengan nama siswa rombel itu memberi peringatan yang harus dikonfirmasi pengunggah sebelum disimpan ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md#104-mengunggah-lembar-yang-sudah-diketik) K13) | R1, R2 | [22 → Kode peserta](22_PERMOHONAN_IZIN_ETIK.md#kode-peserta-dan-pemisahan-peran), [01 → Kode peserta baru](01_REVISI_10_ANALITIK.md#kode-peserta-baru) | Tahap 3 (kode baru, peringatan nama); prosedur ada |
| P2 | **Pemisahan peran.** Tiga peran staf: `admin` memegang identitas dan tidak melihat atau mengunduh data penelitian; `peneliti` (P1–P3) melihat data satu siswa lewat kodenya dan tidak mengunduh; `ketua` satu-satunya yang mengunduh data penelitian, selalu berkode; peneliti dan `ketua` tidak pernah melihat nama; satu orang dengan dua peran memakai dua akun; peran `guru` dihapus; akun staf dengan nama samaran per peran | R1, R3, R5 | [01 → Admin dan peneliti](01_REVISI_10_ANALITIK.md#admin-dan-peneliti-dua-peran-berbeda), [27](27_IMPLEMENTASI_PERAN_PENELITI.md#2-keputusan-yang-dikunci) K1–K3, K14 | Tahap 3 (27) |
| P3 | **Enkripsi dan penyimpanan terkunci.** Salinan ekspor, salinan digital L7, rekaman, dan ketikan KG1 di penyimpanan terenkripsi `[alat enkripsi diisi tim]`; kertas di lemari atau map terkunci, terpisah dari lembar lain | R1, R4 | [22 → Penyimpanan dan enkripsi](22_PERMOHONAN_IZIN_ETIK.md#penyimpanan-dan-enkripsi) | Sebelum KG1 |
| P4 | **Keamanan server dan akun.** HTTPS; kata sandi berhash; akun staf terkunci 15 menit setelah 5 kali gagal; jejak audit untuk masuk, reset sandi, ekspor, dan penghapusan; kunci jawaban tidak dikirim ke peramban; ekspor di server terhapus otomatis setelah 7 hari | R1, R9 | `docs/01_DATABASE.md`, [22](22_PERMOHONAN_IZIN_ETIK.md#penyimpanan-dan-enkripsi) | Ada |
| P5 | **Pernyataan kerahasiaan** ditandatangani setiap anggota tim, termasuk pengembang, admin, dan pengode, sebelum lapangan | R1, R4 | 22 lampiran 12 | Sebelum KG1 |
| P6 | **Server, cadangan, dan penyimpanan di Indonesia**, dengan **perjanjian prosesor tertulis** yang memuat sebelas hal di PP Pasal 14, larangan subprosesor tanpa persetujuan tertulis, kewajiban lapor kebocoran pada kesempatan pertama, dan ikut menghapus cadangan saat diperintahkan | R1, R7, R12 | [22 → Pengendali, prosesor, dan narahubung data](22_PERMOHONAN_IZIN_ETIK.md#pengendali-prosesor-dan-narahubung-data) | Sebelum KG1 |
| P7 | **Izin terpisah** untuk rekaman suara dan butir risiko daring; butir risiko ditanyakan ulang di layar (T11), diisi sendiri tanpa dibacakan dan tanpa orang yang melihat layar, dengan *Tidak mau menjawab*; pesan dukungan yang sama untuk semua anak (T12) | R3, R6 | [23](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md), [16 → Etika](16_ANGKET_AKSES_DIGITAL.md#etika) | Tahap 3 (layar); surat ada |
| P8 | **Butir risiko daring hanya angka gabungan** untuk peneliti (sel di bawah 5 siswa ditulis `<5`); penanda tindak lanjut hanya terlihat akun `ketua`, tidak disimpan, dan tidak masuk ekspor; jawabannya tidak pernah dibagikan; untuk salinan orang tua, anak ditanya lebih dulu dan jawaban tidak diberikan bila membahayakan anak | R2, R3 | 22, 01 perubahan no. 23, [29](29_IMPLEMENTASI_DATA_PENELITIAN.md#76-butir-risiko-daring) §7.6, [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#6-penanda-tindak-lanjut-butir-risiko-daring) §6, §8 | Tahap 3 (29, 30) |
| P9 | **Pelaporan gabungan**: tidak ada nilai per anak untuk guru atau orang tua; laporan sekolah tidak untuk kelompok < 5 siswa; nama sekolah dan anak tidak pernah di laporan atau publikasi; kutipan diberi kode samaran baru | R2, R5 | [22 → Risiko dan mitigasi](22_PERMOHONAN_IZIN_ETIK.md#risiko-dan-mitigasi) | Prosedur ada |
| P10 | **Pembersihan sebelum berbagi**: kode acak baru, tanpa kode sekolah dan rombel, umur dikelompokkan, kelompok < 5 siswa digabung, tanpa butir risiko dan transkrip; perjanjian pemakaian data; persetujuan komite; penerima di luar negeri hanya lewat amandemen dan penilaian transfer Bab V | R2, R7 | [22 → Yang dibagikan](22_PERMOHONAN_IZIN_ETIK.md#yang-dibagikan-di-osf-dan-yang-tidak-pernah) | Prosedur ada |
| P11 | **Pembatasan tujuan** tertulis di surat dan di catatan kegiatan pengolahan; pemakaian lain adalah amandemen | R5 | [Tujuan pengolahan](#tujuan-pengolahan) | Sebelum KG1 |
| P12 | **Prosedur kebocoran**: amankan akses, dokumentasikan, pemberitahuan tertulis ke orang tua yang anaknya terdampak dan ke lembaga pelindungan data pribadi **≤ 3 × 24 jam**, sekolah dan komite diberi tahu bersamaan; ditinjau di laporan kemajuan tahunan | R1, R9 | [22 → Langkah saat kejadian](22_PERMOHONAN_IZIN_ETIK.md#langkah-saat-kejadian) | Prosedur ada |
| P13 | **Masa simpan dan pemusnahan**: rekaman dihapus setelah transkrip diperiksa; nama, L7, formulir, pemetaan NPSN–kode, dan catatan rujukan dimusnahkan **saat studi ditutup**, nama dan sekolah di akun diganti kode, lalu cadangan diganti; data berkode disimpan **paling singkat 20 tahun** terenkripsi; setiap pemusnahan dengan berita acara | R6, R14 | [22 → Masa simpan dan pemusnahan](22_PERMOHONAN_IZIN_ETIK.md#masa-simpan-dan-pemusnahan), [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#10-penutupan-studi) §10–§11 | Prosedur ada; `retention_days` 7305 sejak studi ditutup dan penggantian nama dan sekolah di tahap 3 (30) |
| P14 | **Batas 3 × 24 jam untuk permintaan orang tua**, dengan verifikasi pemohon terhadap T2; akun nonaktif saat status mundur disimpan; permintaan dari peneliti dikonfirmasi `ketua` sebelum admin menjalankan; penghapusan termasuk ekspor dan salinan peneliti; **cadangan diganti** (cadangan baru, yang lama dihapus di kedua lokasi) sebelum orang tua diberi tahu, juga saat studi ditutup; daftar periksa, batas waktu di panel, dan berita acara; pemberitahuan setelahnya | R8, R14 | [22 → Mengundurkan diri](22_PERMOHONAN_IZIN_ETIK.md#mengundurkan-diri-dan-hapus-data), [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#7-permintaan-hapus-data) §5.3, §7 | Penghapusan seluruhnya ada; konfirmasi `ketua`, sebagian, akses, pembetulan, dan penggantian cadangan di tahap 3 (30) |
| P15 | **Jalan manual sementara** (*baru di 26*): selama fungsi tahap 3 belum ada, admin menjalankan hapus sebagian, ambil data satu anak, dan pembetulan langsung di basis data atas perintah ketua peneliti, dengan berita acara; KG2 tidak dimulai sebelum fungsi itu ada atau jalan manualnya diuji sekali di instalasi uji | R8, R14 | [Hak orang tua dan anak](#hak-orang-tua-dan-anak), [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#13-jalan-manual-sementara) §13 | Sebelum KG2 (kegiatan pertama yang memakai akun anak) |
| P16 | **Verifikasi persetujuan**: amplop tertutup lewat guru kelas, pencocokan nama dengan daftar kelas dan tanda tangan oleh P1, telepon kalau ragu; anak tanpa izin lengkap tidak bermain dan mendapat kegiatan pengganti | R10 | [22 → Dasar hukum](22_PERMOHONAN_IZIN_ETIK.md#dasar-hukum), [02 → L7](02_LEMBAR_LAPANGAN.md#l7-izin-orang-tua-dan-status-peserta) | Prosedur ada |
| P17 | **Token sesi**: game tertutup di luar sesi lapangan; data di luar sesi ditandai `outside_session` dan tidak dianalisis; reset kata sandi lewat kode tanpa melihat nama, tercatat di audit | R11 | [01 → Token sesi](01_REVISI_10_ANALITIK.md#token-sesi-perlu-atau-tidak) | Tahap 3 |
| P18 | **IP berhash di tabel sesi peramban** (*baru di 26*): penangan IP berhash dipasang di konfigurasi sesi dan sesi kedaluwarsa dibersihkan rutin, sebelum KG2 | R13 | [keputusan no. 2](#keputusan-pengembang), [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#12-ip-berhash-di-sesi-peramban) §12 | Tahap 3 (30) |

## Risiko sisa dan simpulan

| Tingkat sisa | Risiko |
| --- | --- |
| Tinggi | Tidak ada |
| Sedang | R1 (kebocoran nama bersama jawaban), R2 (pengenalan kembali dari gabungan data) |
| Rendah | R3–R14 |

**Mengapa R1 dan R2 tetap sedang.** Tidak ada sistem daring yang sepenuhnya bebas kebocoran, dan surat orang tua mengatakannya dengan jujur (23 → T1 bagian 3). Keparahannya tetap berat kalau sampai terjadi, walaupun kemungkinannya kecil. Untuk R2, data berkode yang disimpan 20 tahun masih bisa dianggap data pribadi selama gabungan sekolah, rombel, dan umur masih ada; karena itu data berkode diperlakukan sebagai data pribadi selama disimpan, dan dibersihkan lebih dulu sebelum dibagikan (`needs_verification` di 22 tentang kedudukan data berkode tanpa kunci).

**Simpulan.** Dengan langkah P1–P18, pengolahan dapat dimulai. Risiko sisa sedang pada R1 dan R2 dapat diterima, karena (a) data yang dikumpulkan sudah dikurangi sampai yang dibutuhkan analisis, (b) nama terpisah dari data dan dimusnahkan saat studi ditutup, (c) bagian paling peka (butir risiko daring, rekaman) hanya dengan izin terpisah, dan (d) manfaat pengetahuannya, yaitu alat ukur literasi digital anak yang tervalidasi, sepadan ([22 → Risiko dan mitigasi](22_PERMOHONAN_IZIN_ETIK.md#risiko-dan-mitigasi), simpulan risiko dan manfaat). Konsultasi dengan lembaga pelindungan data pribadi sebelum pengolahan **tidak** diperkirakan perlu, karena tidak ada risiko sisa tinggi; kalau Peraturan Lembaga atau saran PPDP menyatakan lain, ketua peneliti meminta saran lembaga sebelum KG1.

**Syarat sebelum kegiatan dengan anak:**

| Sebelum | Yang harus selesai |
| --- | --- |
| Permohonan diajukan | Dokumen ini disetujui pengembang dan peneliti; saran PPDP atau narahubung dicatat; nomor pasal dicek ke naskah resmi (bersama 22) |
| KG1 | Perjanjian prosesor (P6); pernyataan kerahasiaan (P5); penyimpanan terenkripsi siap (P3); catatan kegiatan pengolahan dan kebijakan masa simpan disimpan ketua peneliti |
| KG2 (kegiatan pertama yang memakai akun GELITA anak; kalau KG3 lebih dulu, sebelum KG3) | Perubahan sistem tahap 3 untuk P1, P2, P7, P8, P13, P17, P18 selesai dan diuji; fungsi hak orang tua atau jalan manualnya (P14, P15) diuji sekali; ringkasan penilaian dampak diperlihatkan ke kepala sekolah ([Konsultasi](#konsultasi)) |

## Konsultasi

| Dengan siapa | Bagaimana | Kapan | Hasil |
| --- | --- | --- | --- |
| PPDP `[nama lembaga peneliti]` atau narahubung `[kontak PPDP atau narahubung]` (PP Pasal 121 ayat (3), Pasal 145) | Membaca draf ini dan memberi saran tertulis | Sebelum permohonan diajukan | `[saran PPDP atau narahubung]` |
| Kepala sekolah setiap sekolah sampel dan uji coba | Ringkasan satu halaman ([Ringkasan untuk komite](#ringkasan-untuk-komite), ditulis ulang untuk sekolah) diperlihatkan bersama surat T6 saat sekolah setuju ikut; tanggapan dicatat ([keputusan no. 3](#keputusan-pengembang)) | Saat sekolah setuju ikut, sebelum kegiatan pertama dengan anak di sekolah itu: KG1 (sekolah uji keterbacaan), KG2 atau KG3 (sekolah uji coba), atau KG5 (sekolah sampel) | `[tanggapan sekolah]` |
| Orang tua | Tidak dikonsultasikan tersendiri; isi pokoknya disampaikan lewat surat T1 (cara data dijaga, batas pengamanan daring, hak, kontak) | H-14 sebelum P1 | — |
| *Lembaga Komite Etik* | Lampiran 18 permohonan | Saat pengajuan | Tanggapan komite dicatat dengan `asal: komite etik` |
| Pengembang dan admin | Menyusun draf, memeriksa inventaris terhadap skema basis data | 27 Oktober 2026 | Dokumen ini; keputusan no. 1–4 |

## Persetujuan dan tanda tangan

Ditandatangani di luar repo; yang dicatat di sini hanya statusnya.

| Peran | Nama | Keputusan | Tanggal |
| --- | --- | --- | --- |
| Disusun: pengembang (berbantuan AI) | `[nama pengembang]` | Draf | 27 Oktober 2026 |
| Disetujui: pengembang | `[nama pengembang]` | Disetujui | 31 Oktober 2026 |
| Saran: PPDP atau narahubung | `[kontak PPDP atau narahubung]` | `[saran]` | `[tanggal]` |
| Ditetapkan: ketua peneliti, mewakili pengendali `[nama lembaga peneliti]` | `[nama ketua peneliti]` | `[ditetapkan / direvisi]`; risiko sisa sedang R1 dan R2 diterima | `[tanggal penandatanganan]` |
| Diketahui: pembimbing/promotor | `[nama pembimbing]` | — | `[tanggal]` |

## Tinjauan ulang

Ditinjau ulang ([keputusan no. 4](#keputusan-pengembang); PP Pasal 121 ayat (5)–(6)):

1. **setiap tahun**, bersama laporan kemajuan dan permohonan perpanjangan ke komite (masa berlaku persetujuan 12 bulan), pertama kali `[tanggal tinjauan pertama]`;
2. **sebelum setiap amandemen** yang mengubah data, tempat penyimpanan, penerima, atau peserta;
3. **setelah setiap kebocoran** atau kejadian serius yang menyangkut data;
4. **saat Peraturan Lembaga** tentang penilaian dampak, PPDP, atau kebocoran terbit, atau saat nomor pasal PP berubah setelah dicek ke naskah resmi;
5. **saat studi ditutup**, untuk memastikan nama dan kunci kode sudah dimusnahkan dan yang tersisa hanya data berkode dan daftar izin berkode.

Setiap tinjauan menaikkan nomor versi, dicatat di [04](04_CATATAN_KEPUTUSAN.md) dengan asalnya, dan, kalau mengubah bahan untuk anak atau orang tua, diajukan sebagai amandemen ([22 → Perubahan protokol](22_PERMOHONAN_IZIN_ETIK.md#perubahan-protokol)).

## Keputusan pengembang

Empat pertanyaan dijawab pengembang pada 27 Oktober 2026 (`asal: pengembang`, `disetujui pengembang`; [04](04_CATATAN_KEPUTUSAN.md)). Semuanya pilihan yang diusulkan.

1. **Pengembang dan admin adalah bagian dari tim pengendali**, terikat pernyataan kerahasiaan, bukan prosesor terpisah. Prosesor hanya penyedia server, dengan perjanjian tertulis. Sama dengan 22. Tidak dipilih: pengembang/admin sebagai prosesor tersendiri dengan perjanjian Pasal 14 kedua.
2. **IP mentah di `ci_sessions` diperbaiki sebelum KG2**: penangan IP berhash (`HashedIpSessionHandler`) dipasang dan sesi kedaluwarsa dibersihkan rutin, sebagai perubahan sistem tahap 3 (langkah P18). Tidak dipilih: diterima sebagai risiko sisa karena hanya admin yang melihat.
3. **Konsultasi dengan PPDP atau narahubung dan dengan kepala sekolah**: saran tertulis PPDP sebelum permohonan diajukan, dan ringkasan satu halaman diperlihatkan ke kepala sekolah bersama T6. Orang tua menerima isinya lewat T1. Tidak dipilih: ditambah wakil orang tua per sekolah; atau PPDP saja.
4. **Tinjauan ulang tahunan dan saat ada perubahan**: bersama laporan kemajuan tahunan, sebelum amandemen yang mengubah data, setelah kebocoran, saat Peraturan Lembaga terbit, dan saat studi ditutup. Tidak dipilih: hanya saat ada perubahan.

## Akibat ke dokumen lain

Dicatat untuk sesi yang memegang dokumen itu atau sesi koordinator. Dokumen ini tidak mengubah dokumen lain.

**Masuk ke dokumen ini** (sesi koordinator, 30 Oktober 2026; `asal: pengembang`): nomor KG di [Kegiatan](#kegiatan) disamakan dengan 03 dan 22, dan setiap sebutan KG2–KG5 diperiksa; akibat 27 §18 (tiga peran staf, unduhan lewat `ketua`, pembatasan prosedur membuka kode) di *Pihak yang terlibat*, *Tim dan staf*, *Aliran data*, P1, dan P2; akibat 29 §20 (lembar L1–L6 di server, log teknis, rincian perangkat, peringatan nama) di A8, A12, L1, *Aliran data* baris 5, dan P1; akibat 30 §23 (konfirmasi `ketua`, akun nonaktif, salinan tanpa jawaban butir risiko, penggantian cadangan, izin berkode di server, sekolah diganti kode, 7305 hari) di *Hak orang tua dan anak*, A7, A14, *Aliran data* baris 8, P8, P13, P14, P15, P18, R8, dan R14. Tidak ada keputusan baru; dua pertanyaan 30 §21 (no. 1 dasar pasal, `needs_verification`; no. 2 transkrip pada pilihan C) tetap terbuka.

**Status per 30 Oktober 2026:** semua baris di bawah sudah dibawa. Tautan lampiran 18 di 22 dibawa sesi `r10-formulir-etik`; sisanya oleh sesi koordinator (cabang `sky/dazzling-planck-6cl9vy`), termasuk penyelarasan nomor pasal di *Dasar hukum* dokumen ini.

| Dokumen | Bagian | Perubahan |
| --- | --- | --- |
| 22 | *Lampiran*, baris 18 | Kolom berkas: ganti `[lampiran penilaian dampak]` dengan tautan ke [26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md). Kolom status: "Draf pengembang (27 Oktober 2026); menunggu persetujuan pengembang dan peneliti dan saran PPDP atau narahubung" |
| 22 | *Penilaian dampak pelindungan data* | Kalimat "penilaian lengkapnya dilampirkan" diberi tautan ke 26. Baris *Risiko terhadap hak anak* boleh menyebut bahwa tiga risiko dinilai tinggi sebelum perlindungan (R1–R3) dan tidak ada yang tinggi sesudahnya |
| 22 | *Pengendali, prosesor, dan narahubung data* | Tambah satu kalimat: pengembang dan admin adalah bagian dari tim pengendali, terikat pernyataan kerahasiaan, bukan prosesor ([keputusan no. 1](#keputusan-pengembang)) |
| 22 | *Kerahasiaan dan pengelolaan data* → *Data yang dikumpulkan* | Baris *Log teknis* bisa menyebut data akun staf dan jejak audit dengan `ip_hash`; inventaris lengkap di 26 |
| 22 | *Mengundurkan diri dan hapus data* | Sebut jalan manual sementara dengan berita acara selama fungsi tahap 3 belum ada (P15), dan bahwa KG2 tidak dimulai sebelum jalan itu diuji |
| 22 | *Dasar hukum* | Hasil cek nomor pasal di 22 dibawa ke 26 → *Dasar hukum* dan *Mengapa penilaian dampak diperlukan* (keduanya memakai nomor yang sama). UU Pasal 4 ayat (2) huruf e dan Pasal 25 dipakai di 26 tetapi belum tertulis di 22; ikut dicek |
| 22 | *Langkah saat kejadian* | Tidak ada perubahan; 26 merujuknya |
| 01 | *Catatan perubahan sistem untuk tahap nanti*, no. 23 | Tambah: pasang `HashedIpSessionHandler` di `Config\Session` dan bersihkan `ci_sessions` kedaluwarsa secara rutin sebelum KG2 ([keputusan no. 2](#keputusan-pengembang)) |
| 01 | *Peta jalan dan daftar kerja*, no. 15 | Penilaian dampak lengkap ditulis di 26 (draf pengembang, 27 Oktober 2026) |
| 23 | T6 surat kepala sekolah | Sebut ringkasan penilaian dampak satu halaman yang diperlihatkan bersama surat ([keputusan no. 3](#keputusan-pengembang)); teks ringkasannya ditulis sesi yang memegang 23 |
| 23 | Tempat kosong | Tidak ada tempat kosong baru selain yang sudah ada di 22 dan 23; tempat kosong khusus 26: `[tanggal tinjauan pertama]`, `[tanggal penandatanganan]`, `[nama pengembang]`, `[nama pembimbing]`, `[saran PPDP atau narahubung]`, `[tanggapan sekolah]` |
| 00 | *Daftar dokumen* | Baris 26 (dibuat bersama dokumen ini) |
