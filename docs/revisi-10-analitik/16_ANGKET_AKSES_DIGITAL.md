# Angket Akses Digital

**Judul penelitian:** *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*

Per 26 Oktober 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · Daftar kerja no. 10 · Status: **draf**; keputusan pemilik proyek diambil 26 Oktober 2026 ([bagian akhir](#keputusan-pemilik-proyek)); menunggu validasi ahli

Dokumen ini berisi angket akses digital yang diisi siswa kelas 5 di layar modul tes GELITA, langsung setelah tes awal, ditambah dua–tiga butir pengalaman risiko daring yang diisi di P5 setelah tes akhir: butir, kode jawaban, nama variabel, gunanya, sumber, pelaksanaan, pemasukan data, etika, dan validasinya. Rancangan tesnya ada di [13](13_TES_AWAL_AKHIR.md).

Belum ada yang diubah di sistem; modulnya dibangun di tahap 3 (perubahan sistem no. 22 di [01](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti)). Angket tidak mengukur literasi digital dan tidak diskor. Jawabannya menjadi variabel peserta (kovariat) yang digabung lewat kode peserta ([11](11_KAMUS_DATA.md#aturan-gabung-lembar-lapangan)).

## Ringkasan

1. **Gunanya tiga:** menggambarkan akses dan kebiasaan digital sampel, menjadi kovariat dan kelompok DIF di analisis, dan membantu menafsirkan jawaban D2 dan D3.
2. **17 butir dalam delapan bagian:** perangkat dan internet di rumah, lama memakai, kegiatan, akun dan media sosial, pendampingan orang tua, belajar tentang internet, membaca di layar, serta bahasa dan buku di rumah. Empat isi wajib L6 ada di ang-01, ang-06, ang-08, dan ang-09–10.
3. **Dibaca sendiri di layar, ± 10 menit** (diputuskan 26 Oktober 2026). Koordinator sesi tidak membacakan pertanyaan. Karena itu kalimatnya dibuat sangat sederhana, penjelasan baku tampil sebagai keterangan kecil di bawah pertanyaan, dan keterbacaannya diuji di tahap 2.
4. **Pilihan di layar, tanpa isian.** Siswa memilih satu jawaban atau mencentang beberapa kotak; kodenya tersimpan otomatis dan tidak tampil. "Tidak tahu" atau "Tidak ingat" ada di butir yang mungkin tidak diketahui anak.
5. **Diadaptasi dari angket anak yang sudah dipakai luas:** PIRLS 2021, Global Kids Online, EU Kids Online 2017, dan ICILS 2023, lalu disesuaikan dengan keadaan Indonesia (HP dipakai bersama, WhatsApp, game daring).
6. **Yang tidak ditanyakan:** penghasilan, pekerjaan atau pendidikan orang tua, agama, keterampilan digital menurut diri sendiri, dan topik butir D2 dan D3 di game. Pengalaman risiko daring tidak ditanyakan di P1; dua–tiga butirnya diisi sendiri di layar di P5 setelah tes akhir (diputuskan 26 Oktober 2026).
7. **Diisi langsung setelah tes awal di P1, di modul yang sama,** supaya pertanyaannya tidak memengaruhi tes. Siswa absen ikut susulan dalam tiga hari, bersama tes susulan.

## Dasar yang sudah diputuskan

| Keputusan | Akibatnya untuk angket |
| --- | --- |
| L6: angket diisi siswa di kelas, ± 10 menit; isinya paling sedikit punya HP sendiri, memakai media sosial, didampingi orang tua, dan lama main game per hari ([02](02_LEMBAR_LAPANGAN.md#l6-log-tes-awalakhir-dan-angket-akses-digital)) | 17 butir, ± 10 menit dibaca sendiri; empat isi wajib ada di ang-01, ang-08, ang-09–10, dan ang-06 |
| Data L6 menjadi variabel peserta dengan kunci kode peserta ([11](11_KAMUS_DATA.md#aturan-gabung-lembar-lapangan)) | Satu baris per siswa; nama variabel bahasa Inggris, sama dengan kolom lain di kamus |
| Usia, jenis kelamin, dan kelas sudah direkam saat daftar ([11](11_KAMUS_DATA.md#variabel-peserta-dan-sesi)) | Angket tidak menanyakannya lagi |
| Nama siswa hanya di L7 ([01](01_REVISI_10_ANALITIK.md#kerja-lapangan-cara-kerjanya)) | Layar angket hanya menampilkan kode peserta |
| Surat izin orang tua menyebut angket sebagai data yang dikumpulkan ([02, L7](02_LEMBAR_LAPANGAN.md#l7-izin-orang-tua-dan-status-peserta)) | Hanya siswa yang punya izin yang mengisi; yang lain ikut kegiatan pengganti |
| Keputusan pemilik proyek 26 Oktober 2026: angket di modul tes GELITA, langsung setelah tes awal, hanya di layar dan tidak dibacakan; dua–tiga butir risiko daring di P5 setelah tes akhir ([13](13_TES_AWAL_AKHIR.md#keputusan-pemilik-proyek)) | Pelaksanaan dan pemasukan data mengikuti modul; kode `ang-{nn}` tetap |
| Tes dan game tidak boleh saling menjawab ([13](13_TES_AWAL_AKHIR.md#aturan-menulis-butir-tes)) | Angket tidak memuat topik dan tindakan kunci D2 dan D3 (lihat [Etika](#etika)) |
| Data yang bisa dianalisis ditentukan izin dan percobaan pertama, bukan angket ([11](11_KAMUS_DATA.md#data-siswa-yang-bisa-dianalisis)) | Siswa tanpa angket tetap masuk analisis utama; ia hanya hilang di analisis yang memakai variabel angket |

## Gunanya di analisis

Angket tidak menjawab pertanyaan penelitian sendiri. Ia melengkapi analisis di [01](01_REVISI_10_ANALITIK.md#rencana-analisis-singkat) dan [13](13_TES_AWAL_AKHIR.md#analisis):

| Analisis | Variabel angket | Cara pakai |
| --- | --- | --- |
| Deskripsi sampel (bab hasil) | Semua butir | Proporsi per sekolah dan rombel. Angka nasional dipakai hanya sebagai konteks, karena usianya berbeda (lihat [Konteks](#konteks-dari-data-nasional)) |
| Model Rasch per dimensi, DIF (01: *Apakah urutan kesulitan wilayah terbukti?*) | `own_phone`, `social_media_own`, `home_indonesian`, `books_home`, `game_time_group` (dan `gender` dari pendaftaran) | **Usulan perluasan:** DIF butir game dan tes antarkelompok latar belakang, sebagai bukti bahwa butir adil untuk anak dengan akses berbeda. Hanya dijalankan kalau kelompok terkecil berisi sekitar 100 siswa atau lebih; ambangnya ditetapkan bersama Ahli Pengukuran sebelum G4 |
| *Tugas mana yang lama?* (model campuran) | `access_index`, `school_device_use`, `digital_quiz_exp`, `game_time_school` | Kovariat di tingkat siswa: anak yang jarang memakai perangkat mungkin lebih lama karena alatnya, bukan karena soalnya |
| *Apakah bantuan menolong?* (regresi logistik campuran) | `access_index`, `digital_quiz_exp` | Kovariat |
| *Bagaimana pemahaman keamanan dan etika digital siswa?* (01 sudah menyebut angket di baris ini) | `social_media_own`, `own_accounts`, `parent_active`, `parent_rules`, `safety_info_*`, `act_post` | Tabulasi silang pilihan dan alasan Skenario Digital (D2) serta kode BUD di Periksa Unggahan (D3) per kelompok. Eksploratif, tanpa klaim sebab-akibat |
| *Apakah skor game sejalan dengan kemampuan nyata?* | `game_time_school`, `game_time_holiday`, `access_index`, `read_medium_pref` | Bukti diskriminan: korelasi kemampuan game dengan kebiasaan main game seharusnya lebih rendah daripada korelasinya dengan tes. Kalau tidak, game mungkin ikut mengukur kebiasaan main game. Tes dan game kini sama-sama di layar, jadi `read_medium_pref` menjadi kovariat membaca di layar untuk keduanya |
| Perubahan tes awal–akhir (13, model campuran) | `access_index`, `parent_active`, `safety_info_sources` | Kovariat, bersama urutan bentuk tes |
| Deskripsi pengalaman risiko daring (butir P5) | `risk_upset`, `risk_stranger_contact`, `support_*` | Deskripsi sampel dan tabulasi eksploratif dengan pilihan D2 tentang orang asing; hanya angka gabungan, tidak pernah per siswa |

Catatan untuk semua analisis di atas:

- Jawaban anak 10–11 tahun tentang lama waktu adalah perkiraan kasar. Variabel waktu dipakai sebagai kategori urut, bukan menit.
- Batas kelompok (misalnya ≤ 25 atau > 25 buku) ditetapkan dari sebaran data uji coba dan dibekukan di pra-registrasi sebelum data utama dilihat.
- Banyak perbandingan kelompok berarti banyak uji. Analisis angket dilaporkan sebagai eksploratif, kecuali yang dipra-registrasi.

## Isi angket

### Aturan umum

- **Satu angket untuk semua siswa,** tanpa bentuk A dan B, di layar terpisah dari tes.
- **Kode angka** tersimpan otomatis di belakang setiap pilihan dan tidak tampil di layar.
- **Kode khusus:** `9` = tidak tahu atau tidak ingat, `8` = tidak berlaku (misalnya "Aku tidak memakai HP atau internet di rumah").
- **Tanpa pilihan "Tidak mau menjawab".** Global Kids Online menyarankan pilihan *Tidak tahu* dan *Tidak mau menjawab* di semua butir. Di sini *Tidak tahu* hanya ada di butir yang mungkin tidak diketahui anak, dan *Tidak mau menjawab* diganti izin melewati butir (tertulis di petunjuk), supaya angket tetap pendek. Butir angket utama tidak sensitif.
- **Urutan pilihan** mengikuti cara anak membaca, bukan urutan kode. Kodenya tetap: angka lebih besar berarti lebih banyak, lebih sering, atau lebih lama.

### Ringkasan butir

| Kode | Bagian | Isi singkat | Variabel |
| --- | --- | --- | --- |
| ang-01 | A Perangkat dan internet | Punya HP sendiri | `phone_access` |
| ang-02 | A | Alat di rumah yang bisa dipakai | `home_phone`, `home_tablet`, `home_computer`, `home_console`, `home_device_none` |
| ang-03 | A | Bisa memakai internet di rumah | `home_internet` |
| ang-04 | B Lama dan waktu | Mulai memakai HP atau tablet | `first_use_grade` |
| ang-05 | B | Lama memakai layar, hari sekolah dan hari libur | `screen_time_school`, `screen_time_holiday` |
| ang-06 | B | Lama main game, hari sekolah dan hari libur | `game_time_school`, `game_time_holiday` |
| ang-07 | C Kegiatan | Tujuh kegiatan di HP, tablet, atau komputer | `act_video`, `act_game`, `act_chat`, `act_schoolwork`, `act_search`, `act_read`, `act_post` |
| ang-08 | D Akun dan media sosial | Memakai atau punya akun di enam aplikasi | `acct_whatsapp`, `acct_youtube`, `acct_tiktok`, `acct_instagram`, `acct_facebook`, `acct_game` |
| ang-09 | E Pendampingan orang tua | Ditemani orang tua | `parent_couse` |
| ang-10 | E | Mengobrol dengan orang tua tentang internet | `parent_talk` |
| ang-11 | E | Aturan orang tua | `parent_rule_time`, `parent_rule_content` |
| ang-12 | F Belajar tentang internet | Siapa yang memberi tahu cara aman | `safety_info_parent`, `safety_info_teacher`, `safety_info_family`, `safety_info_friend`, `safety_info_media`, `safety_info_none`, `safety_info_dk` |
| ang-13 | F | Memakai alat di sekolah untuk belajar | `school_device_use` |
| ang-14 | F | Pernah mengerjakan kuis atau soal di layar | `digital_quiz_exp` |
| ang-15 | G Membaca di layar | Lebih mudah membaca di kertas atau layar | `read_medium_pref` |
| ang-16 | H Bahasa dan buku di rumah | Bahasa Indonesia di rumah | `home_indonesian` |
| ang-17 | H | Banyaknya buku di rumah | `books_home` |

### Konteks dari data nasional

Pilihan jawaban disusun dengan memperhatikan data anak Indonesia berikut. Tidak ada yang berasal dari anak usia 10–11 tahun sendiri, jadi angka ini hanya konteks, bukan pembanding:

- Disrupting Harm di Indonesia (survei rumah tangga 2020–2021, anak 12–17 tahun): 92% pengguna internet; dari anak yang memakai telepon pintar, 23% memakainya bersama orang lain (27% di usia 12–13); 82% anak kota dan 72% anak desa punya telepon pintar sendiri; 43% pernah menerima informasi cara aman di internet dan 41% tidak pernah. Ini alasan ang-01 punya pilihan "memakai HP orang lain" dan ang-12 punya pilihan "belum ada yang pernah memberi tahu".
- APJII, Profil Internet Indonesia 2025: penetrasi internet Gen Alpha (di bawah 12 tahun) 79,73%. Surveinya mewawancarai warga berusia minimal 13 tahun, jadi angka anak tidak berasal dari jawaban anak sendiri.
- PP Nomor 17 Tahun 2025 (PP Tunas), menurut siaran pers Komdigi: anak usia 13 tahun ke bawah hanya boleh mengakses platform berisiko rendah dengan persetujuan orang tua. Siswa kelas 5 yang punya akun media sosial akan ada, jadi kalimat ang-08 dibuat tidak menghakimi (lihat [Etika](#etika)).

### A. Perangkat dan internet di rumah

#### ang-01 HP

> **1. Apakah kamu punya HP sendiri?** *Pilih satu.*

| Kode | Pilihan di layar |
| --- | --- |
| 2 | Ya, aku punya HP sendiri |
| 1 | Tidak, tapi aku memakai HP orang lain di rumah (misalnya HP ayah, ibu, atau kakak) |
| 0 | Tidak, aku tidak memakai HP |

- **Variabel:** `phone_access`
- **Gunanya:** isi wajib L6 (punya HP sendiri); kelompok DIF (`own_phone`); bagian indeks akses.
- **Sumber:** adaptasi EU Kids Online 2017 QB6 (*Do you have any of these devices just for your own use…*). Pilihan tengah dari temuan Disrupting Harm di Indonesia tentang HP yang dipakai bersama.

#### ang-02 Alat di rumah

> **2. Di rumahmu, ada alat apa saja yang bisa kamu pakai?** *Centang semua yang ada.*

| Kotak di layar | Variabel (1 = dicentang, 0 = tidak) |
| --- | --- |
| HP (punyamu atau punya keluargamu) | `home_phone` |
| Tablet | `home_tablet` |
| Laptop atau komputer | `home_computer` |
| Konsol game (misalnya PlayStation atau Nintendo) | `home_console` |
| Tidak ada satu pun | `home_device_none` |

- **Gunanya:** jumlah jenis alat untuk indeks akses. Laptop atau komputer menunjukkan pengalaman memakai alat selain HP.
- **Sumber:** PIRLS 2021 G5a (*A shared computer or tablet that you can use*); daftar alat di Global Kids Online Q4Devices; Framework Survei Lingkungan Belajar (fasilitas belajar digital di rumah).

#### ang-03 Internet di rumah

> **3. Kalau kamu ingin memakai internet di rumah, apakah bisa?** *Pilih satu.*

| Kode | Pilihan di layar |
| --- | --- |
| 3 | Hampir selalu bisa |
| 2 | Kadang-kadang bisa |
| 1 | Jarang bisa |
| 0 | Tidak pernah bisa |
| 9 | Tidak tahu |

- **Variabel:** `home_internet`
- **Gunanya:** akses nyata (sinyal, kuota, dan izin), bukan sekadar ada alat; bagian indeks akses. Penyebabnya tidak ditanyakan supaya singkat dan tidak menyentuh biaya keluarga.
- **Sumber:** Global Kids Online Q1Access (*Are you able to access the internet when you want to or need to?*), lima pilihan diringkas jadi empat ditambah *Tidak tahu*.

### B. Lama dan waktu memakai

#### ang-04 Mulai memakai

> **4. Sejak kapan kamu mulai memakai HP atau tablet?** *Pilih satu.*

| Kode | Pilihan di layar |
| --- | --- |
| 4 | Sebelum masuk SD |
| 3 | Kelas 1 atau kelas 2 |
| 2 | Kelas 3 atau kelas 4 |
| 1 | Kelas 5 |
| 0 | Aku belum pernah memakai HP atau tablet |
| 9 | Tidak ingat |

- **Variabel:** `first_use_grade`
- **Gunanya:** lama pengalaman memakai perangkat; kovariat.
- **Sumber:** ditulis sendiri. Kategorinya kelas, bukan umur atau tahun, karena anak lebih mudah mengingat kelas.

#### ang-05 Lama memakai layar

> **5. Berapa lama kamu biasanya memakai HP, tablet, atau komputer di luar jam sekolah?** *Pilih satu di setiap kolom.*

| Kode | Pilihan di layar | Hari sekolah | Hari Minggu atau hari libur |
| --- | --- | --- | --- |
| 0 | Tidak memakai | ☐ | ☐ |
| 1 | Kurang dari 1 jam | ☐ | ☐ |
| 2 | Sekitar 1–2 jam | ☐ | ☐ |
| 3 | Sekitar 3–4 jam | ☐ | ☐ |
| 4 | Lebih dari 4 jam | ☐ | ☐ |
| 9 | Tidak tahu | ☐ | ☐ |
| | **Variabel** | `screen_time_school` | `screen_time_holiday` |

- **Gunanya:** banyaknya pemakaian; kovariat.
- **Sumber:** EU Kids Online 2017 QB7/QB8 (hari sekolah dan akhir pekan dalam dua kolom). Sembilan kategori jam diringkas menjadi lima.

#### ang-06 Lama main game

> **6. Berapa lama kamu biasanya bermain game di HP, tablet, komputer, atau konsol?** *Pilih satu di setiap kolom.*

| Kode | Pilihan di layar | Hari sekolah | Hari Minggu atau hari libur |
| --- | --- | --- | --- |
| 0 | Tidak bermain game | ☐ | ☐ |
| 1 | Kurang dari setengah jam | ☐ | ☐ |
| 2 | Setengah jam sampai 1 jam | ☐ | ☐ |
| 3 | Lebih dari 1 jam sampai 2 jam | ☐ | ☐ |
| 4 | Lebih dari 2 jam | ☐ | ☐ |
| 9 | Tidak tahu | ☐ | ☐ |
| | **Variabel** | `game_time_school` | `game_time_holiday` |

- **Gunanya:** isi wajib L6 (lama main game per hari); bukti diskriminan (kemampuan game seharusnya tidak sekadar mengikuti kebiasaan main game); kelompok DIF (`game_time_group`); kovariat waktu aktif.
- **Sumber:** format dua kolom EU Kids Online 2017 QB7/QB8; batas setengah jam mengikuti PIRLS 2021 G8, yang memisahkan "30 menit atau kurang" dan "lebih dari 30 menit".

### C. Kegiatan di internet

#### ang-07 Kegiatan

> **7. Seberapa sering kamu melakukan hal-hal ini di HP, tablet, atau komputer?** *Pilih satu di setiap baris.*

| Kegiatan di layar | Hampir setiap hari (2) | Kadang-kadang (1) | Tidak pernah (0) | Tidak tahu (9) | Variabel |
| --- | --- | --- | --- | --- | --- |
| a. Menonton video (misalnya di YouTube atau TikTok) | ☐ | ☐ | ☐ | ☐ | `act_video` |
| b. Bermain game | ☐ | ☐ | ☐ | ☐ | `act_game` |
| c. Mengirim pesan atau menelepon keluarga dan teman (misalnya lewat WhatsApp) | ☐ | ☐ | ☐ | ☐ | `act_chat` |
| d. Mengerjakan tugas sekolah | ☐ | ☐ | ☐ | ☐ | `act_schoolwork` |
| e. Mencari tahu sesuatu (misalnya lewat Google) | ☐ | ☐ | ☐ | ☐ | `act_search` |
| f. Membaca cerita, berita, atau buku | ☐ | ☐ | ☐ | ☐ | `act_read` |
| g. Membuat foto atau video, lalu mengunggahnya ke internet | ☐ | ☐ | ☐ | ☐ | `act_post` |

- **Gunanya:** indeks keluasan kegiatan (`activity_breadth`). Baris e–f dekat dengan D1, baris g dengan D3, dan baris b dicek terhadap ang-06.
- **Sumber:** Global Kids Online Q5Activities (belajar, tugas sekolah, pesan instan, video, game daring, membuat dan mengunggah video) dan EU Kids Online 2017 QC3, diringkas dari 15 menjadi 7 baris dengan contoh aplikasi yang dikenal anak Indonesia. Baris f menambah kegiatan membaca, sejalan dengan PIRLS 2021 R5 (waktu membaca di luar sekolah).

### D. Akun dan media sosial

#### ang-08 Akun

> Ada anak yang punya akun sendiri, ada yang memakai akun orang lain, ada juga yang tidak memakai aplikasi ini. Semua jawaban boleh.
>
> **8. Bagaimana denganmu?** *Pilih satu di setiap baris.*

| Aplikasi di layar | Tidak memakai (0) | Memakai, tapi bukan akunku sendiri (1) | Punya akun sendiri (2) | Tidak tahu (9) | Variabel |
| --- | --- | --- | --- | --- | --- |
| a. WhatsApp | ☐ | ☐ | ☐ | ☐ | `acct_whatsapp` |
| b. YouTube | ☐ | ☐ | ☐ | ☐ | `acct_youtube` |
| c. TikTok | ☐ | ☐ | ☐ | ☐ | `acct_tiktok` |
| d. Instagram | ☐ | ☐ | ☐ | ☐ | `acct_instagram` |
| e. Facebook | ☐ | ☐ | ☐ | ☐ | `acct_facebook` |
| f. Game online yang memakai akun (misalnya Roblox, Free Fire, atau Mobile Legends) | ☐ | ☐ | ☐ | ☐ | `acct_game` |

- **Gunanya:** isi wajib L6 (memakai media sosial). Butir ini membedakan *memakai* dan *punya akun sendiri*, karena kebiasaan yang dekat dengan D2 (menjaga akun dan data diri) baru muncul kalau anak mengelola akunnya sendiri. Dipakai untuk kelompok DIF (`social_media_own`) dan untuk menafsirkan D2 dan D3.
- **Sumber:** EU Kids Online 2017 QD3 (*Do you have your own profile on a social networking or social media or gaming site…*), dipecah per aplikasi. Global Kids Online mencatat bahwa anak lebih mudah mengenali nama merek dan menyarankan contoh setempat. WhatsApp dan Facebook juga muncul di temuan Disrupting Harm di Indonesia. Kalimat pembuka mengikuti [Etika](#etika).

### E. Pendampingan orang tua

Kalimat di layar, di atas bagian ini: *"Orang tua di sini artinya ayah, ibu, atau orang dewasa yang merawatmu di rumah (misalnya kakek, nenek, om, atau tante)."*

#### ang-09 Ditemani

> **9. Saat kamu memakai HP atau internet di rumah, seberapa sering orang tuamu menemanimu?** *Pilih satu.*

| Kode | Pilihan di layar |
| --- | --- |
| 3 | Hampir selalu |
| 2 | Kadang-kadang |
| 1 | Jarang |
| 0 | Tidak pernah |
| 8 | Aku tidak memakai HP atau internet di rumah |
| 9 | Tidak tahu |

- **Variabel:** `parent_couse`
- **Gunanya:** isi wajib L6 (didampingi orang tua); bagian `parent_active`.
- **Sumber:** EU Kids Online 2017 QI4d (*Sits with me while I use the internet*), lima pilihan diringkas jadi empat.

#### ang-10 Mengobrol

> **10. Seberapa sering orang tuamu mengobrol denganmu tentang hal yang kamu lihat atau lakukan di internet?** *Pilih satu.*

Pilihan dan kodenya sama dengan ang-09.

- **Variabel:** `parent_talk`
- **Gunanya:** pendampingan aktif; bagian `parent_active`.
- **Sumber:** kelompok pendampingan aktif EU Kids Online 2017 QI4, dan QI5c (*Started a discussion with my parent/carer about what I do on the internet*), dibalik menjadi dari sisi orang tua.

#### ang-11 Aturan

> **11. Apakah orang tuamu punya aturan tentang hal-hal ini?** *Pilih satu di setiap baris.*

| Aturan di layar | Ada aturan (1) | Tidak ada aturan (0) | Tidak tahu (9) | Variabel |
| --- | --- | --- | --- | --- |
| a. Berapa lama kamu boleh memakai HP atau internet | ☐ | ☐ | ☐ | `parent_rule_time` |
| b. Aplikasi, game, atau tontonan yang boleh kamu buka | ☐ | ☐ | ☐ | `parent_rule_content` |

- **Gunanya:** pendampingan yang membatasi. Dipisah dari ang-09–10 karena arah hubungannya dengan kemampuan anak bisa berbeda.
- **Sumber:** ICILS 2023 Q21 (*Do your parents or guardians place a limit on the amount of screen time…*); EU Kids Online 2017 QI6 (hal yang boleh dilakukan di internet).

### F. Belajar tentang internet dan memakai alat di sekolah

#### ang-12 Sumber cara aman

> **12. Siapa saja yang pernah memberi tahu kamu cara memakai internet dengan aman?** *Centang semua yang benar.*

| Kotak di layar | Variabel (1 = dicentang, 0 = tidak) |
| --- | --- |
| Orang tua | `safety_info_parent` |
| Guru | `safety_info_teacher` |
| Kakak atau saudara | `safety_info_family` |
| Teman | `safety_info_friend` |
| Video, TV, atau internet | `safety_info_media` |
| Belum ada yang pernah memberi tahu | `safety_info_none` |
| Tidak ingat | `safety_info_dk` |

- **Gunanya:** pengalaman belajar keamanan daring sebelum game, untuk menafsirkan pilihan dan alasan D2 serta perubahan tes awal–akhir. Butir ini sengaja tidak menyebut topik (misalnya kata sandi atau data diri), supaya tidak memancing jawaban D2.
- **Sumber:** EU Kids Online 2017 QI4b, QJ2a, dan QK2a (*Suggested ways to use the internet safely* oleh orang tua, guru, dan teman), digabung menjadi satu butir centang; Disrupting Harm di Indonesia.

#### ang-13 Alat di sekolah

> **13. Di sekolah, seberapa sering kamu memakai komputer, laptop, atau tablet untuk belajar?** *Pilih satu.*

| Kode | Pilihan di layar |
| --- | --- |
| 2 | Hampir setiap minggu |
| 1 | Kadang-kadang |
| 0 | Belum pernah |
| 9 | Tidak ingat |

- **Variabel:** `school_device_use`
- **Gunanya:** keakraban dengan alat di kelas atau lab, tempat game dimainkan; kovariat waktu aktif dan percobaan pertama.
- **Sumber:** EU Kids Online 2017 QC5 (memakai internet di sekolah) dan bagian pemakaian TIK di sekolah pada ICILS 2023, diringkas menjadi satu butir.

#### ang-14 Kuis di layar

> **14. Pernahkah kamu mengerjakan kuis atau soal di HP, tablet, atau komputer? (Misalnya kuis di kelas, Kahoot, atau latihan ANBK.)** *Pilih satu.*

| Kode | Pilihan di layar |
| --- | --- |
| 2 | Sering |
| 1 | Pernah, satu atau dua kali |
| 0 | Belum pernah |
| 9 | Tidak ingat |

- **Variabel:** `digital_quiz_exp`
- **Gunanya:** keakraban dengan bentuk asesmen digital. GELITA adalah model asesmen, jadi anak yang terbiasa dengan kuis digital mungkin lebih cepat memahami cara menjawab. Kovariat.
- **Sumber:** ditulis sendiri.

### G. Membaca di layar

Kebiasaan membaca di layar juga tercatat di ang-07 baris f.

#### ang-15 Kertas atau layar

> **15. Menurutmu, membaca di mana yang lebih mudah?** *Pilih satu.*

| Kode | Pilihan di layar |
| --- | --- |
| 1 | Di kertas atau buku |
| 2 | Sama saja |
| 3 | Di layar HP, tablet, atau komputer |
| 9 | Tidak tahu |

- **Variabel:** `read_medium_pref`
- **Gunanya:** tes awal–akhir dan D1 di game sama-sama dibaca di layar. Butir ini menjadi kovariat di analisis D1: anak yang lebih mudah membaca di kertas mungkin dirugikan di kedua instrumen.
- **Sumber:** ditulis sendiri.

### H. Bahasa dan buku di rumah

#### ang-16 Bahasa di rumah

> **16. Seberapa sering kamu memakai bahasa Indonesia saat berbicara di rumah?** *Pilih satu.*

| Kode | Pilihan di layar |
| --- | --- |
| 3 | Selalu |
| 2 | Hampir selalu |
| 1 | Kadang-kadang |
| 0 | Tidak pernah |

- **Variabel:** `home_indonesian`
- **Gunanya:** tes dan game memakai bahasa Indonesia, sedangkan sebagian anak di wilayah Kedu mungkin lebih banyak berbahasa Jawa di rumah. Kovariat D1; kelompok DIF (misalnya "selalu atau hampir selalu" dan "kadang-kadang atau tidak pernah").
- **Sumber:** PIRLS 2021 G3 (*How often do you speak <language of test> at home?*), empat pilihan yang sama.

#### ang-17 Buku di rumah

> **17. Kira-kira ada berapa buku di rumahmu? Jangan hitung majalah, koran, dan buku pelajaran sekolahmu.** *Pilih satu.*

| Kode | Pilihan di layar (masing-masing dengan gambar kecil rak atau lemari) |
| --- | --- |
| 1 | Tidak ada atau sangat sedikit (0–10 buku) |
| 2 | Cukup untuk satu rak (11–25 buku) |
| 3 | Cukup untuk satu lemari buku (26–100 buku) |
| 4 | Cukup untuk dua lemari buku (101–200 buku) |
| 5 | Cukup untuk tiga lemari buku atau lebih (lebih dari 200 buku) |
| 9 | Tidak tahu |

- **Variabel:** `books_home`
- **Gunanya:** penanda sumber bacaan di rumah. Butir ini menggantikan pekerjaan dan pendidikan orang tua, yang sengaja tidak ditanyakan. Kovariat D1; kelompok DIF (misalnya ≤ 25 dan > 25 buku).
- **Sumber:** PIRLS 2021 G4 (lima kategori dengan gambar). Framework Survei Lingkungan Belajar juga memakai kepemilikan buku sebagai bagian indeks sosial-ekonomi murid.

### Yang sengaja tidak ditanyakan

| Topik | Ada di | Alasan tidak dipakai |
| --- | --- | --- |
| Usia, jenis kelamin, kelas | — | Sudah direkam saat daftar ([11](11_KAMUS_DATA.md#variabel-peserta-dan-sesi)) |
| Pekerjaan dan pendidikan orang tua, penghasilan, barang mewah di rumah | ICILS 2023 Q9–Q14; Framework Survei Lingkungan Belajar | Sensitif, dan anak kelas 5 sering tidak tahu jawabannya. Diganti ang-17 |
| Agama, suku, siapa saja yang tinggal di rumah | — | Sensitif dan tidak dibutuhkan analisis |
| Keterampilan digital menurut diri sendiri (misalnya "Aku tahu cara mengatur privasi", "Mudah bagiku memeriksa apakah informasi benar") | Global Kids Online Q6Skills; EU Kids Online 2017 QE | Tumpang tindih dengan yang diukur game dan tes, dan bisa memancing jawaban D1b dan D2 |
| Pengalaman risiko daring | Global Kids Online Q7Harm dan Q8Risks | Tidak di angket P1, supaya tidak memancing D2 sebelum game; dua–tiga butirnya diisi di P5 ([Keputusan pemilik proyek](#keputusan-pemilik-proyek)) |
| Hambatan memakai internet (biaya, sinyal) | Global Kids Online Q2Barriers | Biaya menyentuh keadaan ekonomi keluarga; ang-03 sudah cukup |
| Aplikasi yang paling sering dipakai (isian) | EU Kids Online 2017 QD1 | Isian sulit ditulis anak dan sulit dikode; ang-08 sudah cukup |

## Petunjuk untuk siswa

Teks di layar pembuka angket. Siswa membacanya sendiri; koordinator sesi tidak membacakannya:

> Halo! Bagian ini berisi pertanyaan tentang kebiasaanmu memakai HP, komputer, dan internet. Ini bukan tes. Tidak ada jawaban yang benar atau salah, dan tidak ada nilainya.
>
> Guru dan orang tuamu tidak akan melihat jawabanmu. Jawabanmu hanya dibaca tim peneliti, dan namamu tidak ditulis di mana pun.
>
> Jawablah sesuai keadaanmu yang sebenarnya, ya. Kalau ada pertanyaan yang tidak ingin kamu jawab, lewati saja. Tidak apa-apa.
>
> 1. Baca setiap pertanyaan, lalu pilih jawabanmu.
> 2. Kalau kamu tidak tahu atau tidak ingat, pilih *Tidak tahu* atau *Tidak ingat*. Itu juga jawaban yang baik.
> 3. Kalau ada kata yang kurang jelas, baca keterangan kecil di bawah pertanyaan, atau angkat tangan.
> 4. Kamu boleh kembali ke pertanyaan sebelumnya dan mengganti jawaban sebelum menekan *Selesai*.

## Cara pelaksanaan

1. **Waktu dan urutan.** Di P1, langsung setelah Bagian 3 tes awal dikirim, di modul yang sama, sebelum cerita pembuka ([13](13_TES_AWAL_AKHIR.md#waktu-di-p1-dan-p5)). Lama ± 10 menit, tanpa batas waktu. Siswa yang belum selesai saat P1 berakhir melanjutkan di awal P2, sebelum game dimainkan.
2. **Layar.** Tampilan Bootstrap modul tes, tanpa unsur game. Satu bagian angket (A–H) per layar, huruf besar, tombol pilihan dan kotak centang besar, dan penanda bagian yang sedang dikerjakan (misalnya "Bagian 3 dari 8").
3. **Kode peserta.** Tersimpan otomatis dari akun siswa. Tidak ada yang perlu ditulis atau dicocokkan dengan L2.
4. **Tidak dibacakan** (diputuskan 26 Oktober 2026). Siswa membaca sendiri. Kalau siswa bertanya, koordinator sesi, P2, atau P3 hanya memberi penjelasan baku di bawah, pelan-pelan dan hanya kepada siswa itu, dengan nada datar yang tidak menunjukkan jawaban mana yang lebih baik. Karena angket tidak dibacakan, kemampuan membaca bisa ikut memengaruhi jawaban; ini dikurangi dengan kalimat sederhana dan keterangan di layar, dan dicek di uji keterbacaan.
5. **Penjelasan baku.** Tampil sebagai keterangan kecil di bawah pertanyaan yang memakainya, dan hanya ini yang boleh diucapkan kalau siswa bertanya:
   - "Orang tua artinya ayah, ibu, atau orang dewasa yang merawatmu di rumah, misalnya kakek, nenek, om, atau tante."
   - "Akun sendiri artinya akun yang dibuat untukmu dan biasanya hanya kamu yang memakainya."
   - "Satu jam itu, misalnya, dari pukul empat sampai pukul lima sore."
   - "Hari sekolah artinya hari ketika kamu masuk sekolah. Hari libur artinya hari Minggu atau hari ketika sekolah libur."
   - "Kalau tidak tahu, pilih *Tidak tahu*. Kalau tidak ingin menjawab, lewati saja."
6. **Peneliti lain dan guru.** P2 dan P3 berkeliling hanya untuk membantu cara memilih jawaban di layar, tidak membaca atau mengomentari jawaban. Guru kelas diminta tidak berkeliling melihat layar siswa.
7. **Selesai.** Siswa menekan *Selesai*, dan modul menampilkan layar terima kasih. Tidak ada lembar yang perlu dikumpulkan atau disimpan.
8. **Catatan di L6.** Status angket (`sudah`, `susulan` dengan tanggalnya, atau `hilang`) dibaca dari modul. P3 mencatat pertanyaan yang sering ditanyakan siswa di kolom *Catatan*. Catatan ini dipakai di uji coba.
9. **Siswa absen.** Ikut susulan dalam tiga hari, sesudah tes susulan; peneliti membuka angket di modul untuk kelompok kecil itu. Kalau baru bisa setelah siswa mulai bermain di P2, angket masih boleh diisi sampai P5 dan ditandai `questionnaire_late = 1`. Kalau tidak sama sekali, ditandai `hilang`.
10. **Siswa tanpa izin.** Tidak mengisi angket dan ikut kegiatan pengganti sesuai L7.

## Pemasukan data dan variabel turunan

### Data dari modul

Modul menyimpan satu baris per siswa: `participant_code`, `questionnaire_date`, `questionnaire_makeup`, `questionnaire_late`, `questionnaire_minutes`, lalu satu kolom per variabel butir menurut urutan ang-01 sampai ang-17 (41 kolom).

| Yang dilakukan siswa | Disimpan |
| --- | --- |
| Memilih satu jawaban (butir pilih satu atau satu baris tabel) | Kode pilihan itu |
| Melewati butir atau baris | `-` |
| Mengganti jawaban | Kode jawaban terakhir; jumlah penggantian dicatat |
| Butir centang semua (ang-02, ang-12) | `1` untuk kotak yang dicentang, `0` untuk yang tidak. Kalau tidak ada satu kotak pun yang dicentang, semua kolom butir itu `-` |

Kode `*` (lebih dari satu jawaban) tidak muncul lagi, karena butir pilih satu memakai tombol pilihan yang hanya menerima satu jawaban. Data mentah disimpan apa adanya dan tidak diperbaiki. Pemeriksaan di bawah hanya mengisi kolom penanda `q_flags` (daftar kode butir yang isinya bertentangan):

- ang-02: *Tidak ada satu pun* dicentang bersama alat lain. Modul tidak mencegahnya, supaya jawaban anak tetap terekam apa adanya.
- ang-12: *Belum ada yang pernah memberi tahu* atau *Tidak ingat* dicentang bersama sumber lain.
- ang-01 = 0 tetapi ang-08 punya akun sendiri, atau ang-06 = 0 di kedua kolom tetapi ang-07b = 2. Ini tidak selalu salah (akun bisa dibuka di tablet), jadi hanya dihitung dan dilaporkan.

**Kode khusus di analisis.** `-`, `8`, dan `9` diperlakukan sebagai hilang untuk skala urut, tetapi jumlahnya dilaporkan per butir. Di deskripsi sampel, `8` di ang-09 dan ang-10 ditampilkan sebagai kategori sendiri ("tidak memakai di rumah").

Tidak ada pengetikan, jadi ketik ulang 10% tidak diperlukan. Sebagai gantinya, penyimpanan kode diuji otomatis di tahap 3 dengan jawaban contoh untuk setiap butir.

### Variabel turunan

| Variabel | Cara menghitung | Rentang |
| --- | --- | --- |
| `own_phone` | 1 kalau `phone_access` = 2; 0 kalau 0 atau 1 | 0–1 |
| `device_count` | Jumlah `home_phone`, `home_tablet`, `home_computer`, `home_console` | 0–4 |
| `access_index` | `phone_access` + `device_count` + `home_internet` | 0–9 |
| `activity_breadth` | Jumlah baris ang-07 yang berkode 1 atau 2 | 0–7 |
| `own_accounts` | Jumlah baris ang-08 yang berkode 2 | 0–6 |
| `social_media_own` | 1 kalau kode 2 di TikTok, Instagram, atau Facebook | 0–1 |
| `social_media_use` | 1 kalau kode 1 atau 2 di TikTok, Instagram, atau Facebook | 0–1 |
| `parent_active` | Rata-rata `parent_couse` dan `parent_talk` | 0–3 |
| `parent_rules` | Jumlah `parent_rule_time` dan `parent_rule_content` yang bernilai 1 | 0–2 |
| `safety_info_sources` | Jumlah lima kotak sumber di ang-12 | 0–5 |
| `game_time_group` | Dari `game_time_school`: `rendah` (0–1), `sedang` (2–3), `tinggi` (4); batasnya ditinjau setelah uji coba | 3 kelompok |
| `questionnaire_status` | `lengkap` (paling banyak 2 butir kosong), `sebagian` (lebih dari 2 butir kosong), `hilang` (tidak mengisi) | — |

- **Media sosial** di sini adalah TikTok, Instagram, dan Facebook: aplikasi tempat anak mengunggah dan punya pengikut. WhatsApp (pesan) dan YouTube (kebanyakan menonton) dilaporkan sendiri-sendiri.
- **Data hilang.** Indeks hilang kalau salah satu komponennya hilang; tidak ada imputasi. Kalau lebih dari 10% siswa kehilangan satu indeks, aturannya ditinjau setelah uji coba.
- **Konsistensi indeks** (`access_index`, `parent_active`) dicek di uji coba dan data utama. Kalau komponennya tidak sejalan, komponen dilaporkan terpisah, bukan dijumlahkan.

### Variabel untuk kamus data

Ditambahkan ke [11, *Variabel peserta dan sesi*](11_KAMUS_DATA.md#variabel-peserta-dan-sesi) setelah dokumen ini disetujui:

| Satuan | Kolom | Isi |
| --- | --- | --- |
| Peserta | `questionnaire_status`, `questionnaire_date`, `questionnaire_makeup`, `questionnaire_late`, `questionnaire_minutes` | Status, tanggal, susulan, diisi setelah mulai bermain, dan lama mengisi |
| | `phone_access` … `books_home` (41 kolom, urutan ang-01 sampai ang-17) | Jawaban mentah berkode, termasuk `-`, `8`, `9` |
| | `own_phone`, `device_count`, `access_index`, `activity_breadth`, `own_accounts`, `social_media_own`, `social_media_use`, `parent_active`, `parent_rules`, `safety_info_sources`, `game_time_group` | Variabel turunan |
| | `q_flags` | Kode butir yang isinya bertentangan |
| | `risk_status`, `risk_upset`, `risk_stranger_contact`, `support_parent` … `support_na` | Butir risiko daring di P5 (ang-21 sampai ang-23), termasuk kode `7` (tidak mau menjawab). Penanda rujukan tidak disimpan di data penelitian; lihat [Etika](#etika) |

## Etika

1. **Izin.** Hanya siswa dengan izin orang tua dan persetujuan siswa di L7 yang mengisi. Surat izin orang tua sebaiknya menyebut topik angket: HP dan internet di rumah, media sosial, game, pendampingan orang tua, serta bahasa dan buku di rumah.
2. **Kerahasiaan.** Layar hanya menampilkan kode peserta. Jawaban dibaca tim peneliti saja. Guru dan orang tua tidak melihat jawaban per siswa. Laporan ke sekolah hanya berisi angka gabungan, tidak pernah per siswa, dan tidak untuk kelompok yang berisi kurang dari 5 siswa. Angket tidak dipakai untuk menilai siswa, guru, atau sekolah.
3. **Tidak ada pertanyaan sensitif.** Tidak ada pertanyaan tentang penghasilan keluarga, pekerjaan atau pendidikan orang tua, agama, suku, keadaan keluarga, kesehatan, atau pengalaman buruk (lihat [Yang sengaja tidak ditanyakan](#yang-sengaja-tidak-ditanyakan)). Banyaknya buku di rumah (ang-17) adalah satu-satunya penanda latar belakang rumah, dan pilihannya tidak menyebut kaya atau miskin.
4. **Kalimat tidak menghakimi.** Menurut PP Tunas, anak usia 13 tahun ke bawah hanya boleh memakai platform berisiko rendah dengan persetujuan orang tua, tetapi siswa kelas 5 yang punya akun media sosial tetap akan ada. Pertanyaan tidak boleh membuat mereka merasa ketahuan:
   - ang-08 dibuka dengan kalimat bahwa semua jawaban boleh.
   - Kata "dilarang", "melanggar", "seharusnya", dan "boleh tidak" tidak dipakai di angket.
   - Pilihan seperti "Lebih dari 4 jam" atau "Tidak ada aturan" tidak diberi tanda bahwa itu buruk.
   - Koordinator tidak menanggapi jawaban siswa, dan tidak ada tindak lanjut ke guru atau orang tua.
5. **Tidak mengajarkan jawaban game.** Angket diisi sebelum game dan sebelum tes akhir, jadi tidak ada butir yang menyebut topik atau tindakan kunci D2 dan D3: data pribadi (DIG-PRI), kata sandi dan kode OTP (DIG-SAN), izin membagikan foto atau obrolan (DIG-IZN, DIG-ETK), orang asing (DIG-ASG), kabar bohong (DIG-HOX), atribusi (BUD-ATR), keterangan budaya yang keliru (BUD-REP), izin pelaku dan pemilik karya (BUD-IZN), dan menghargai tradisi (BUD-ETK). Butir seperti "Pernahkah kamu memberikan kode OTP?" atau "Apakah kamu memeriksa kebenaran berita?" tidak dipakai. Satu-satunya rujukan ke keamanan adalah frasa umum "memakai internet dengan aman" di ang-12, yang juga sudah ada di layar pembuka tes.
6. **Boleh tidak menjawab.** Setiap butir boleh dilewati, dan ini tertulis di petunjuk. Siswa yang ingin berhenti boleh berhenti tanpa akibat.
7. **Kalau anak bercerita.** Angket P1 tidak menanyakan pengalaman buruk, jadi kecil kemungkinan anak bercerita hal yang mengkhawatirkan saat itu. Kalau itu terjadi, peneliti mengikuti protokol etik: koordinator melapor ke ketua peneliti, yang menjalankan langkah rujukan di protokol. Ceritanya tidak dicatat sebagai data angket.
8. **Butir risiko daring di P5** (diputuskan 26 Oktober 2026). Syaratnya:
   - Butir ini disebut di permohonan izin etik dan di surat izin orang tua. Siswa yang orang tuanya tidak setuju dengan butir ini tidak melihatnya.
   - Diisi sendiri di layar, tidak dibacakan, dan tidak ada orang lain yang melihat layar siswa. Denah papan catur dan jarak antarperangkat ikut menjaga privasi.
   - Setelah butir ini, semua siswa, apa pun jawabannya, melihat pesan yang sama tentang kepada siapa bisa bercerita (orang tua, guru, atau kakak peneliti). Dengan begitu, tidak ada siswa yang terlihat berbeda.
   - Prosedur rujukan ditulis di protokol etik: siapa yang melihat jawaban yang perlu ditindaklanjuti, kapan, dan bagaimana kode dihubungkan dengan nama di L7 (hanya oleh ketua peneliti). Penanda rujukan dan catatan tindak lanjut disimpan di luar data penelitian.

## Validasi dan uji coba

| Tahap | Yang dilakukan | Oleh |
| --- | --- | --- |
| 2 | Telaah isi: relevansi setiap butir dengan gunanya di analisis, butir yang kurang atau berlebih, butir yang bisa memancing jawaban D2 atau D3, dan kepekaan kalimat | Ahli Literasi Digital 1 dan 2 |
| 2 | Telaah bahasa: kata yang sulit, panjang kalimat, pilihan yang tumpang tindih, dan nada yang menghakimi | Ahli Bahasa Anak 1 dan 2 |
| 2 | Uji keterbacaan dengan 3–5 anak kelas 5 setelah izin etik terbit, bersama uji keterbacaan bank dan tes, di mock-up layar modul. Karena angket tidak dibacakan, anak membaca sendiri, lalu diminta menjelaskan dengan kata-katanya sendiri arti "akun sendiri", "menemanimu", "hari sekolah", dan "1 jam", dan butir risiko daring | Peneliti |
| 2 | Mock-up layar angket ikut ditelaah ahli media langkah 1 | Ahli Media Pembelajaran |
| 4 | Uji coba satu kelas di modul, dibaca sendiri seperti pelaksanaan sebenarnya. Yang dilihat: waktu total (sasaran ≤ 10 menit), butir yang sering dilewati, butir dengan banyak *Tidak tahu*, pilihan yang tidak pernah dipilih, pertanyaan yang sering ditanyakan siswa (catatan P3 di L6), dan isian yang bertentangan (`q_flags`) | Peneliti |
| G4 | Angket final dan batas kelompok DIF dibekukan bersama pra-registrasi | Pemilik proyek |

Usulan patokan uji coba: butir yang kosong pada lebih dari 10% siswa, atau dijawab *Tidak tahu* oleh lebih dari 20% siswa, ditulis ulang atau dibuang. Satu kelas uji coba hanya berisi ± 28 siswa, jadi angka ini hanya petunjuk kasar.

## Penyesuaian di dokumen lain

Keputusan pemilik proyek 26 Oktober 2026 membuat penyesuaian ini bisa dikerjakan:

- **02, L6:** baris angket di tabel instrumen merujuk ke 16 (17 butir di modul, dibaca sendiri, ± 10 menit, ditambah butir risiko daring di P5); status angket dibaca dari modul. Bagian dari daftar kerja no. 11 (lembar final).
- **02, L7, dan surat izin orang tua:** topik angket disebut, termasuk butir risiko daring di P5 dan persetujuan terpisahnya.
- **11:** variabel angket di *Variabel peserta dan sesi*.
- **03:** pola kode `ang-*` serta kode jawaban `8` dan `9`.
- **07:** angket masuk paket validasi tahap 2 (telaah isi dan bahasa).
- **01 dan 00:** status daftar kerja no. 10.

## Keputusan pemilik proyek

Pertanyaan 4 di draf ini (pengalaman risiko daring) dan media angket diputuskan pemilik proyek pada 26 Oktober 2026, bersama pertanyaan 1–3 di [13](13_TES_AWAL_AKHIR.md#keputusan-pemilik-proyek). Rinciannya di [04](04_CATATAN_KEPUTUSAN.md).

1. **Angket di modul tes GELITA**, langsung setelah tes awal, hanya di layar dan tidak dibacakan koordinator sesi.
2. **Dua–tiga butir pengalaman risiko daring diisi di P5 setelah tes akhir**, sendiri di layar. Di P5 tes dan game sudah selesai, jadi butir ini tidak memancing D2. Syarat etiknya ada di [Etika](#etika) no. 8. Butirnya ang-21 sampai ang-23 di bawah; jumlah akhirnya (dua atau tiga) ditetapkan setelah telaah ahli dan uji keterbacaan. Tambahannya ± 3 menit di P5.

Pertimbangan yang dicatat sebelum keputusan, dan yang masih berlaku: anak 10–11 tahun bisa merasa sedih atau malu, jadi butir harus diisi secara privat; jawaban "pernah" mewajibkan tindak lanjut; angka pengalaman buruk kemungkinan kurang dilaporkan, sehingga hasilnya hanya deskriptif.

> *Pertanyaan berikut tentang hal-hal yang bisa terjadi di internet. Kalau tidak ingin menjawab, pilih "Tidak mau menjawab" atau lewati saja.*

| Kode | Pertanyaan di layar | Pilihan dan kode | Variabel | Sumber |
| --- | --- | --- | --- | --- |
| ang-21 | Dalam setahun ini, pernahkah ada sesuatu di internet atau HP yang membuatmu tidak nyaman, takut, atau sedih? | 0 Tidak pernah · 1 Pernah, sekali atau dua kali · 2 Beberapa kali atau lebih · 9 Tidak tahu · 7 Tidak mau menjawab | `risk_upset` | Global Kids Online Q7Harm |
| ang-22 | Dalam setahun ini, pernahkah orang yang tidak kamu kenal di dunia nyata mengirim pesan kepadamu lewat internet atau HP? | 0 Tidak · 1 Ya · 9 Tidak tahu · 7 Tidak mau menjawab | `risk_stranger_contact` | Global Kids Online Q8Risks butir a |
| ang-23 | Kalau ada sesuatu di internet yang membuatmu tidak nyaman, kepada siapa kamu biasanya bercerita? *Centang semua yang benar.* | Orang tua · Kakak atau saudara · Teman · Guru · Orang dewasa lain · Aku tidak bercerita kepada siapa pun · Belum pernah ada hal seperti itu (masing-masing 1/0) | `support_parent`, `support_family`, `support_friend`, `support_teacher`, `support_adult`, `support_none`, `support_na` | Global Kids Online Q11Support; EU Kids Online 2017 QF04 |

Kode `7` (tidak mau menjawab) hanya dipakai di butir ini. Nomor 18–20 sengaja dikosongkan supaya butir P5 tidak tercampur dengan butir angket utama. `risk_status` di data mencatat `sudah`, `tanpa_izin` (orang tua tidak setuju dengan butir ini), atau `hilang`.

## Rujukan

- IEA (2020), [*PIRLS 2021 Student Questionnaire*](https://pirls2021.org/wp-content/uploads/2023/05/P21_StudentQuestionnaire.pdf): G3 bahasa tes di rumah, G4 banyaknya buku di rumah dengan gambar, G5 barang di rumah termasuk komputer atau tablet yang bisa dipakai, G8 waktu memakai perangkat untuk tugas sekolah, R5 waktu membaca di luar sekolah.
- Global Kids Online (2020), [*Key Measures Global Kids Online* (diperbarui April 2020)](http://globalkidsonline.net/wp-content/uploads/2016/04/Key-measures-Global-Kids-Online-Updated-Apr-2020.pdf): Q1Access, Q2Barriers, Q4Devices, Q5Activities, Q6Skills, Q7Harm, Q8Risks, Q11Support; saran pilihan *Don't know* dan *Prefer not to say*; pertanyaan risiko diajukan secara privat; anak lebih mudah mengenali nama merek.
- EU Kids Online (2017), [*EU Kids Online 2017 questionnaire (core)*](https://www.lse.ac.uk/media-and-communications/assets/documents/research/eu-kids-online/toolkit/Questionnaire-core-EUKO-2017.pdf): QB6 perangkat milik sendiri, QB7/QB8 lama di internet pada hari sekolah dan akhir pekan, QC3 dan QC5 kegiatan, QD1 dan QD3 aplikasi dan akun, QE keterampilan, QF04 kepada siapa bercerita, QI4–QI6 pendampingan orang tua, QJ2 guru, QK2 teman.
- IEA (2023), [*ICILS 2023 Student Questionnaire* (versi Amerika Serikat, NCES)](https://nces.ed.gov/surveys/icils/pdf/ICILS-2023-MS-StudentQuest-US.pdf): Q9–Q14 pekerjaan dan pendidikan orang tua (tidak dipakai), Q21 batas waktu layar dari orang tua, pemakaian TIK di sekolah.
- Kemendikbudristek, [*Framework Survei Lingkungan Belajar*](https://pusmendik.kemendikdasmen.go.id/an/page/download_file/965875_8) (berkas 2022 di situs Pusmendik): indeks sosial-ekonomi murid dari pendidikan dan pekerjaan orang tua, fasilitas belajar di rumah (digital dan nondigital), kepemilikan buku, dan barang tersier.
- ECPAT, INTERPOL, dan UNICEF Office of Research – Innocenti (2022), [*Disrupting Harm di Indonesia*](https://safeonline.global/wp-content/uploads/2023/12/DH_Indonesia_ONLINE_ID_Nov22.pdf): anak 12–17 tahun pengguna internet, HP yang dipakai bersama, kepemilikan telepon pintar di kota dan desa, hambatan akses, informasi keamanan daring, dan angka pengalaman buruk yang kemungkinan kurang dilaporkan.
- ANTARA (6 Agustus 2025), [*APJII catat tingkat penetrasi internet Indonesia capai 80,66 persen*](https://www.antaranews.com/berita/5019229/apjii-catat-tingkat-penetrasi-internet-indonesia-capai-8066-persen): hasil survei Profil Internet Indonesia 2025, termasuk penetrasi Gen Alpha 79,73% dan responden berusia minimal 13 tahun.
- Kementerian Komunikasi dan Digital (14 Mei 2025), [*Menteri Meutya dan Gubernur KDM Bekerja Sama Lindungi Anak Sekolah*](https://portal.komdigi.go.id/kanal-publik/berita-kini/9330): klasifikasi akses platform menurut usia dan tingkat risiko dalam PP Nomor 17 Tahun 2025 (PP Tunas).
