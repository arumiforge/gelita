# Dokumen Implementasi Bagian 2 — Sesi Lapangan, Token, Kode Peserta, Pemantauan, dan Akun Uji

Per 1 November 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · perubahan sistem no. 11, 12, 13, dan 24 di [01 → Catatan perubahan sistem](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti)

> **Status: `disetujui pengembang`, `asal: pengembang`** (disusun dengan bantuan AI 30 Oktober 2026; dicek kemutakhirannya, ditinjau, dan disetujui pengembang 1 November 2026, [§22](#22-keputusan-pengembang)). Ini dokumen, bukan perubahan sistem.
>
> **Kapan dibangun** ([04](04_CATATAN_KEPUTUSAN.md), entri 1 November 2026, menggantikan "tahap 3, setelah G2"): kodenya dibangun sekarang, di gelombang kode D bersama bagian 5 (31), setelah bagian 1 ([27](27_IMPLEMENTASI_PERAN_PENELITI.md)) di gelombang C ([00 → Tahap dokumen 3](00_INDEKS.md)). Semua perilaku penelitian di sini ada di balik sakelar `field_mode` yang mati secara bawaan ([§5.1](#51-sakelar-field_mode)), jadi `main` tetap bisa dirilis. Sistem baru tidak dipakai dengan anak sebelum persetujuan etik terbit; G3 tetap dinilai setelah G2, versi dikunci di G4, dan instalasi uji validator tahap 2 tetap memakai *commit* yang dibekukan di [20](20_PAKET_VALIDASI.md) prasyarat no. 4. Migrasi bagian ini (`003500`) tidak termasuk migrasi yang menghapus data: ia mengisi `participant_progress` dari pengerjaan yang sudah tuntas sebelum membuang `session_progress`, jadi tidak ada kemajuan yang hilang ([§8](#8-kemajuan-mengikuti-siswa-bukan-sesi-game), [§22](#22-keputusan-pengembang) no. 11).
>
> Setelah hasil validasi kembali, isinya dicek ulang sekali terhadap matriks revisi induk ([20](20_PAKET_VALIDASI.md)) dan keputusan komite etik.
>
> **Bagian 2 dari 4.** Bagian ini memakai peran dan peta kemampuan dari [27](27_IMPLEMENTASI_PERAN_PENELITI.md) (`field_sessions.manage`, `monitoring.view`). Bagian 3 (`29`) dan bagian 4 (`30`) memakai sesi lapangan dan kode peserta dari sini.

---

## Daftar isi

0. [Cara memakai dokumen ini](#0-cara-memakai-dokumen-ini)
1. [Kebutuhan, sumber, dan tempatnya](#1-kebutuhan-sumber-dan-tempatnya)
2. [Keputusan yang dikunci](#2-keputusan-yang-dikunci)
3. [Yang tidak berubah](#3-yang-tidak-berubah)
4. [Satu pertemuan di sistem, dari awal sampai akhir](#4-satu-pertemuan-di-sistem-dari-awal-sampai-akhir)
5. [Mode penelitian dan rencana pertemuan](#5-mode-penelitian-dan-rencana-pertemuan)
6. [Sesi lapangan dan token](#6-sesi-lapangan-dan-token)
7. [Game tertutup di luar sesi](#7-game-tertutup-di-luar-sesi)
8. [Kemajuan mengikuti siswa, bukan sesi game](#8-kemajuan-mengikuti-siswa-bukan-sesi-game)
9. [Wilayah yang dibuka per pertemuan](#9-wilayah-yang-dibuka-per-pertemuan)
10. [Kode sekolah penelitian dan kode peserta](#10-kode-sekolah-penelitian-dan-kode-peserta)
11. [Isian L2: kursi, buklet, dan bentuk tes](#11-isian-l2-kursi-buklet-dan-bentuk-tes)
12. [Panel pemantauan](#12-panel-pemantauan)
13. [Alat bantu `gelita:uji:akun` (perubahan no. 24)](#13-alat-bantu-gelitaujiakun-perubahan-no-24)
14. [Skema database](#14-skema-database)
15. [Rute](#15-rute)
16. [Layar dan teks](#16-layar-dan-teks)
17. [Data untuk kamus dan unduhan](#17-data-untuk-kamus-dan-unduhan)
18. [Titik sambung ke perubahan lain](#18-titik-sambung-ke-perubahan-lain)
19. [Pengujian dan kriteria selesai](#19-pengujian-dan-kriteria-selesai)
20. [Dokumen yang ikut diperbarui saat implementasi](#20-dokumen-yang-ikut-diperbarui-saat-implementasi)
21. [Pertanyaan terbuka](#21-pertanyaan-terbuka)
22. [Keputusan pengembang](#22-keputusan-pengembang)
23. [Akibat ke dokumen lain](#23-akibat-ke-dokumen-lain)

---

## 0. Cara memakai dokumen ini

**Urutan.** Implementasinya dikerjakan setelah bagian 1 ([27](27_IMPLEMENTASI_PERAN_PENELITI.md)), karena halaman panel di sini memakai peta kemampuan dan menu per peran dari sana.

**Batas.** Dokumen ini mengatur:
- sesi lapangan dan token;
- game yang tertutup di luar sesi;
- kemajuan siswa lintas pertemuan;
- wilayah yang dibuka per pertemuan;
- kode sekolah penelitian dan kode peserta;
- isian L2;
- panel pemantauan;
- alat bantu `gelita:uji:akun`.

Modul tes (no. 22), layar skala sikap (no. 21), mode penelitian lainnya (no. 16), dan buklet yang menentukan variasi (no. 20) hanya disebut sebagai **titik sambung** ([§18](#18-titik-sambung-ke-perubahan-lain)).

**Saat implementasi:**
- Satu cabang pendek dari `main`, commit berbahasa Inggris.
- `vendor/bin/phpunit --no-coverage` hijau sebelum push.
- Jangan menjalankan migrasi, `gelita:reset`, atau skrip rilis di server produksi.
- Setiap tulisan yang bisa bersaing (token, nomor urut, penutupan sesi) memakai UPDATE bersyarat, bukan `SELECT … FOR UPDATE` (aturan kerja repo, bagian *Concurrency*).

**Istilah.** *Sesi game* di dokumen ini adalah satu baris `game_sessions`, yang di panel dan di 27 disebut *sesi bermain* (glosarium: `game session`). *Sesi lapangan* adalah `field_sessions` dari bagian 2.

**Rujukan kode.** Ditulis per berkas dan metode, sesekali dengan nomor baris. Dicek ulang 1 November 2026 terhadap kode yang terakhir berubah 26 Oktober 2026; nomor baris bisa bergeser.

---

## 1. Kebutuhan, sumber, dan tempatnya

| # | Kebutuhan | Sumber | Di sini |
| ---: | --- | --- | --- |
| 1 | Token sesi versi ringan, bukan sistem ujian; token mengatur wilayah yang dibuka di tiap pertemuan | [01 → Token sesi](01_REVISI_10_ANALITIK.md#token-sesi-perlu-atau-tidak); 04, 25 Oktober 2026 | §5, §6, §9 |
| 2 | P1 membuat sesi lapangan di panel: sekolah, rombel, pertemuan, tanggal, jam buka–tutup, wilayah | 01 → Token sesi, langkah 1; 01 no. 11; [02 → L0](02_LEMBAR_LAPANGAN.md#l0-daftar-periksa-persiapan-sesi) | §6.1 |
| 3 | Token 6 karakter tanpa O, 0, I, 1 (L ditambahkan di §6.3); ditulis di papan; berlaku di jam sesi; bisa ditutup lebih awal atau diganti kalau bocor | 01 → Token sesi, langkah 2–4; [02 → L1](02_LEMBAR_LAPANGAN.md#l1-kondisi-sesi-dan-penyimpangan-protokol) | §6.3 |
| 4 | Di luar sesi, seluruh game tertutup sampai studi ditutup, termasuk Pustaka dan Profil, dengan pesan ramah | 01 → Token sesi, langkah 5; [18](18_MOCKUP_LAYAR_BARU.md) `mk-tk-03` | §7 |
| 5 | Sesi game menyimpan kode sesi lapangan dan nomor pertemuan; satu sesi game milik tepat satu sesi lapangan | [11 → Yang harus disediakan sistem di tahap 3](11_KAMUS_DATA.md#yang-harus-disediakan-sistem-di-tahap-3); [11 → Kunci penggabungan dan waktu](11_KAMUS_DATA.md#kunci-penggabungan-dan-waktu) | §6.4, §8 |
| 6 | Wilayah yang dibuka token terbuka untuk semua siswa sesi itu walau wilayah sebelumnya belum tuntas; tantangan di dalamnya tetap berurutan | 11 → Yang harus disediakan sistem; [06 → Waktu bermain](06_KISI_KISI_BANK_SOAL.md#waktu-bermain) | §9 |
| 7 | Sesi susulan dengan token sendiri dan kode berakhiran `-S` | [01 → Kerja lapangan](01_REVISI_10_ANALITIK.md#kerja-lapangan-cara-kerjanya); [03](03_KODE_DAN_ARTI.md) | §6.7 |
| 8 | Kode peserta `S03-A07` dari token, nomor urut aman saat banyak siswa mendaftar bersamaan, tidak pernah diubah | [01 → Kode peserta baru](01_REVISI_10_ANALITIK.md#kode-peserta-baru); 01 no. 12 | §10 |
| 9 | Kode sekolah dibuat peneliti, bukan NPSN; pemetaan NPSN hanya dilihat admin; sekolah uji coba berawalan `U` | 01 → Kode peserta baru | §10.1 |
| 10 | Setelah token ada, pilihan provinsi, kabupaten, dan sekolah di pendaftaran dihapus (M-11) | [10](10_TELAAH_PENDAHULUAN_MEDIA.md) | §6.5 |
| 11 | P1 memasukkan kode peserta, baris, dan kursi ke panel langsung setelah pendaftaran; panel memeriksa keseimbangan buklet per rombel dan menetapkan bentuk tes | 01 no. 11; [02 → L2](02_LEMBAR_LAPANGAN.md#l2-peta-tempat-duduk-perangkat-dan-kode-peserta); [13](13_TES_AWAL_AKHIR.md) | §11 |
| 12 | Panel pemantauan: siapa belum selesai, sesi aktif, event hilang; hanya untuk operasional | [01 → Olah data](01_REVISI_10_ANALITIK.md#olah-data-di-sistem-atau-di-alat-lain); 01 no. 13 | §12 |
| 13 | Alat bantu `gelita:uji:akun` untuk instalasi uji validator | [20 → Prasyarat no. 8](20_PAKET_VALIDASI.md#prasyarat-no-8-instalasi-uji-dan-akun-uji), langkah 6; 01 no. 24 | §13 |
| 14 | Data di luar sesi diberi `outside_session = 1` dan dikeluarkan dari analisis | [11 → Penanda untuk analisis](11_KAMUS_DATA.md#penanda-untuk-analisis) | §7, §17 |

---

## 2. Keputusan yang dikunci

K2, K10, K12, K15, K19, dan K20 diputuskan pengembang ([§22](#22-keputusan-pengembang)). K6 menurunkan kamus data yang sudah disetujui (`kamus-1.0`). Sisanya menurunkan 01, 02, 06, 11, 13, 18, dan 20.

| Kode | Keputusan |
| --- | --- |
| K1 | **Mode penelitian** adalah sakelar per studi (`research_studies.field_mode`, mati secara bawaan), diubah `ketua` ([27](27_IMPLEMENTASI_PERAN_PENELITI.md)). Setiap uji dengan siswa menjadi studi tersendiri: berpikir lantang (KG2), uji kelompok kecil (KG3a), uji kelompok menengah (KG3b), dan pengambilan data utama (KG5) ([03](03_KODE_DAN_ARTI.md); [04](04_CATATAN_KEPUTUSAN.md), 1 November 2026). Bila menyala dan status studi `active`, game hanya terbuka di dalam sesi lapangan yang sedang buka. Bila status studi `closed`, game terbuka seperti biasa. Perubahan no. 16 memakai sakelar yang sama. |
| K2 | **Admin melihat panel pemantauan sebagai angka saja**, tanpa kode peserta dan tanpa baris per siswa. |
| K3 | **Sesi lapangan** memuat sekolah (lewat kode sekolah penelitian), rombel, nomor pertemuan 1–5, nomor susulan, tanggal, jam buka, jam tutup, dan jam ditutup lebih awal. Isi yang dibuka diturunkan dari rencana pertemuan ([§5](#5-mode-penelitian-dan-rencana-pertemuan)), tidak dipilih bebas. |
| K4 | **Kode sesi lapangan** `S03-A-P1`. Susulan pertama `S03-A-P3-S`, susulan berikutnya `-S2`, `-S3`. Susulan skala sikap 7 hari di P5 adalah susulan P5. |
| K5 | **Token** 6 karakter dari `ABCDEFGHJKMNPQRSTUVWXYZ23456789` (tanpa O, 0, I, 1, L). Huruf besar-kecil tidak dibedakan. Unik di semua riwayat. Hanya berlaku saat sesinya buka. *Buat token baru* mencabut token lama. |
| K6 | **Satu sesi game milik tepat satu sesi lapangan** (11). Karena itu kemajuan siswa disimpan per siswa per studi, bukan per sesi game, dan dibawa dari pertemuan ke pertemuan ([§8](#8-kemajuan-mengikuti-siswa-bukan-sesi-game)). |
| K7 | **Token diketik sekali per sesi lapangan.** Siswa yang keluar lalu masuk lagi di sesi lapangan yang sama tidak mengetik ulang. |
| K8 | **Token sesi utama hanya diterima dari siswa sekolah dan rombel itu.** Token sesi susulan hanya diterima dari siswa yang didaftarkan P1 sebagai anggota susulan. |
| K9 | **Pendaftaran di P1 dimulai dengan token.** Sekolah dan rombel diambil dari sesi lapangan; langkah provinsi, kabupaten, dan sekolah dilewati selama mode penelitian. |
| K10 | **Buklet dan bentuk tes dihitung panel dari baris dan kursi**, tidak diketik. Panel menampilkan jumlah B1–B4 per rombel dan memberi peringatan, tanpa menahan, bila satu buklet berbeda lebih dari satu. |
| K11 | **Kode peserta `S03-A07`** dibuat saat mendaftar, dengan nomor urut dari tabel penghitung per sekolah dan rombel yang dinaikkan lewat UPDATE bersyarat. Paling banyak 99 per rombel. Kode tidak pernah diubah setelah ada data. |
| K12 | **Token susulan hanya untuk siswa yang dicentang P1.** Panel mencentang lebih dulu siswa rombel itu yang tidak punya sesi game di sesi utamanya. |
| K13 | **Di luar sesi, permintaan halaman dialihkan ke layar "sampai jumpa"** dan permintaan API dijawab `FIELD_SESSION_CLOSED`. Kiriman event, rekaman audio, dan detak sesi tetap diterima, supaya event yang tertunda di perangkat tidak hilang. |
| K14 | **Saat sesi lapangan tutup, pengerjaan yang sedang berjalan dibiarkan terbuka.** Sesi game yang terikat dicatat `session_paused` dengan `via: field_session_closed`. Kamus menghitungnya sebagai pengerjaan yang ditinggal bila tidak dilanjutkan di sesi lapangan yang sama. |
| K15 | **`gelita:uji:akun` berjalan di luar alur token.** Alat ini membuat akun `uji.*` langsung, dengan sekolah `Sekolah Uji Validator` (kode `V01`) dan kode `UJI-01`, `UJI-02`, …. Opsi `--reset` menghapus data permainan akun itu. Di G3 pengembang membuka satu sesi lapangan panjang untuk sekolah itu. |
| K16 | **Kode sekolah penelitian** disimpan di kolom baru `schools.research_code` dan diisi admin. Polanya `S` (sampel utama), `U` (uji coba), atau `V` (instalasi uji validator), diikuti dua angka. |
| K17 | **Urutan tantangan di dalam wilayah tetap berurutan, dan diperiksa juga di API.** Sekarang `ChallengeService::assertLevelUnlocked()` hanya memeriksa wilayah. |
| K18 | **Awalan nama pengguna `uji.` dan `sim_` dicadangkan**, sehingga siswa sungguhan tidak bisa memakainya. Alat bantu dan simulator tetap boleh. |
| K19 | **Rencana pertemuan dipilih per studi** (`research_studies.meeting_plan`): `lima_pertemuan` (bawaan; KG3a, KG3b, KG5) atau `satu_sesi` (KG2: satu sesi lapangan yang mendaftarkan anak lalu membuka wilayah yang dipilih saat sesi dibuat) ([§5.2](#52-rencana-pertemuan)). |
| K20 | **Satu akun, satu studi.** Token menolak akun yang sudah punya sesi game di studi lain (`other_study`), supaya anak yang sudah ikut satu uji tidak ikut uji berikutnya dengan akun lamanya, walau sekolah `U` yang sama menjadi tempat kedua uji ([04](04_CATATAN_KEPUTUSAN.md), 1 November 2026). Pengaman utamanya tetap daftar di lapangan. |

---

## 3. Yang tidak berubah

| Bagian | Aturan |
| --- | --- |
| Masuk dan daftar | Nama pengguna dan kata sandi kuat, metrik kata sandi, centang persetujuan anak, perangkat (`device_logs`), `must_change_password` dan `/ganti-sandi` |
| Aturan 3 kesempatan | `ChallengeService` (`openNode`, `submitCheck`, `submitAnswer`, `closeAttempt`) tidak berubah, kecuali cara membaca kemajuan ([§8](#8-kemajuan-mengikuti-siswa-bukan-sesi-game)) dan pemeriksaan urutan tantangan (K17) |
| Event | `EventService`, `game_event_logs`, `events_dropped`, antrean di perangkat. Tidak ada jenis event baru. |
| Pustaka | Pustaka wilayah terbuka setelah wilayahnya tuntas, seperti sekarang (`GameProgress::libraryAccess()`), dan hanya di dalam sesi lapangan |
| Di luar mode penelitian | Bila `field_mode` mati, semua alur sekarang berlaku, termasuk pilihan sekolah lewat NPSN dan kode `GLT-…` |
| Tes yang ada | `GradeFiveWorld` mematikan `field_mode`, jadi tes game yang ada tidak perlu token |

---

## 4. Satu pertemuan di sistem, dari awal sampai akhir

1. **H-1.**
   - P1 (akun `koordinator`) membuat sesi lapangan di panel: kode sekolah, rombel, pertemuan, tanggal, jam buka–tutup.
   - Panel menampilkan kode sesi (`S03-A-P2`) dan tokennya. P1 mencatat keduanya di L1.
2. **Saat sesi buka.**
   - P1 menulis token di papan.
   - Di P1: siswa membuka **Daftar** dan mengetik token lebih dulu ([§6.5](#65-pendaftaran-dengan-token-p1)).
   - Di P2–P5: siswa masuk, lalu mengetik token ([§6.4](#64-siswa-mengetik-token-p2p5)).
3. **Di P1, langsung setelah semua siswa mendaftar.** P1 memasukkan kode peserta, baris, dan kursi ke panel (± 5 menit). Panel menghitung buklet dan bentuk tes, lalu menampilkan keseimbangan ([§11](#11-isian-l2-kursi-buklet-dan-bentuk-tes)).
4. **Selama bermain.**
   - Siswa hanya melihat isi pertemuan itu ([§9](#9-wilayah-yang-dibuka-per-pertemuan)).
   - Peneliti memantau di panel pemantauan ([§12](#12-panel-pemantauan)).
   - Bila token bocor, P1 menekan *Buat token baru*; siswa yang sudah di dalam tidak terganggu.
5. **Tutup.**
   - P1 menekan *Tutup sesi sekarang*, atau jam tutup lewat.
   - Siswa melihat layar "Sampai jumpa di sesi berikutnya!" di permintaan berikutnya.
   - P1 mencatat jam tutup di L1.
6. **Susulan dalam tiga hari.**
   - P1 membuat sesi susulan dari sesi utamanya.
   - Panel mencentang siswa yang tidak hadir; P1 memeriksa dan menyimpan.
   - Hanya siswa itu yang bisa memakai tokennya ([§6.7](#67-sesi-susulan-dan-anggotanya)).

---

## 5. Mode penelitian dan rencana pertemuan

### 5.1 Sakelar `field_mode`

- **Tempat:** kolom baru `research_studies.field_mode` (`TINYINT(1)`, bawaan 0), di halaman studi milik `ketua` dengan label "Mode penelitian".
- **Mati secara bawaan.** Studi baru, `gelita:reset`, dan instalasi yang sudah ada mulai dengan `field_mode = 0`, jadi `main` dengan bagian ini tetap bisa dirilis tanpa mengubah perilaku game ([04](04_CATATAN_KEPUTUSAN.md), 1 November 2026). Sakelar dinyalakan `ketua` untuk studi uji atau pengambilan data, dan tidak dipakai dengan anak sebelum persetujuan etik terbit.
- **Satu studi per uji.** Studi yang `active` adalah yang dipakai (`ResearchStudyModel::active()`, studi aktif terbaru). Uji dijalankan berurutan, satu studi aktif pada satu waktu; sesi lapangan, kursi, kemajuan, dan unduhan terikat ke studinya.
- **Berlaku bila:** `field_mode = 1` dan status studi `active`. Saat itu berlaku §6–§11 dan kemajuan per siswa (§8).
- **Bila mati:** perilaku sekarang, termasuk `unlock_mode` (`sequential` atau `free`) untuk instalasi uji tahap 2.
- **Kunci:** sakelar tidak bisa dimatikan selama ada sesi lapangan yang buka. Panel menolak dengan pesan: "Masih ada sesi lapangan yang buka. Tutup dulu sesinya."
- **Audit:** `study_field_mode_changed`.

### 5.2 Rencana pertemuan

`Config\Gelita::$meetingPlans` memetakan nama rencana dan nomor pertemuan ke isinya, mengikuti [06 → Waktu bermain](06_KISI_KISI_BANK_SOAL.md#waktu-bermain) dan [02 → Pertemuan dan lembar yang dipakai](02_LEMBAR_LAPANGAN.md#pertemuan-dan-lembar-yang-dipakai). Studi memilih rencananya di kolom baru `research_studies.meeting_plan` (K19), diubah `ketua` bersama `field_mode` dan dikunci dengan aturan yang sama (tidak bisa diubah selama ada sesi lapangan yang buka).

```php
// Plan name => meeting number => what a field session of that meeting opens (revision 10, part 2).
public array $meetingPlans = [
    // KG3a, KG3b, KG5 (default).
    'lima_pertemuan' => [
        1 => ['register' => true, 'intro' => true, 'regions' => [],             'test' => 'pre'],
        2 => ['regions' => ['temanggung']],
        3 => ['regions' => ['magelang']],
        4 => ['regions' => ['wonosobo']],
        5 => ['regions' => ['temanggung', 'magelang', 'wonosobo'], 'leftovers' => true, 'ending' => true, 'test' => 'post'],
    ],
    // KG2 think-aloud: one session per child; the region is picked when the field session is created.
    'satu_sesi' => [
        1 => ['register' => true, 'regions' => 'chosen'],
    ],
];
```

- Kode wilayah mengikuti `levels.code`.
- Kunci `test`, `ending`, dan `leftovers` adalah titik sambung untuk no. 22, cerita penutup, dan no. 16.
- Variabel kamus `region_opened` diturunkan dari rencana ini untuk setiap sesi lapangan ([§17](#17-data-untuk-kamus-dan-unduhan)).
- Susulan memakai rencana pertemuan sesi utamanya.
- **`satu_sesi` (KG2).** Hanya pertemuan 1 (`U01-A-P1`). Formulir sesi lapangan menampilkan pilihan satu wilayah, disimpan di `field_sessions.region_code`; token mendaftarkan anak (§6.5) lalu langsung membuka wilayah itu, tanpa cerita pembuka, modul tes, angket, skala sikap, dan butir risiko daring ([23](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md) → T9; ± 40 menit termasuk membuat akun). Anak berpikir lantang satu per satu, jadi satu sesi lapangan bisa dipakai beberapa anak berurutan dalam batas 4 jam. Tidak ada susulan.

---

## 6. Sesi lapangan dan token

### 6.1 Isi dan keadaan sesi lapangan

| Isian | Aturan |
| --- | --- |
| Sekolah | Dipilih dari sekolah yang punya `research_code`; peneliti hanya melihat kodenya (`S03`) |
| Rombel | Satu huruf besar `A`–`Z`; sekolah dengan satu kelas 5 memakai `A` |
| Pertemuan | 1–5 |
| Susulan | 0 untuk sesi utama; 1, 2, … untuk susulan, dengan rujukan ke sesi utamanya |
| Tanggal, jam buka, jam tutup | WIB, jam tutup sesudah jam buka, paling lama 4 jam |
| Ditutup lebih awal | Jam saat P1 menekan *Tutup sesi sekarang* |

**Keadaan diturunkan dari jam, tidak disimpan sebagai status:**
- **belum buka:** sebelum jam buka;
- **buka:** di antara jam buka dan jam tutup, dan belum ditutup lebih awal;
- **tutup:** sesudahnya.

P1 bisa memajukan jam buka ("Buka sekarang") atau mengundurkan jam tutup selama sesi belum tutup. Setiap perubahan dicatat di log audit.

**Satu rombel, satu sesi utama buka.** Dalam satu rombel hanya boleh ada satu sesi utama yang buka atau belum buka per pertemuan. Panel menolak sesi ganda dengan pesan.

### 6.2 Kode sesi

- Kode dibuat sistem dari isian, misalnya `S03-A-P2`, `S03-A-P2-S`, `S03-A-P2-S2`.
- Kode unik per studi dan tidak pernah diubah.
- Kode ini yang ditulis di L1 dan dipakai sebagai kunci gabung (`field_session_code`).

### 6.3 Token

| Aturan | Isi |
| --- | --- |
| Bentuk | 6 karakter dari `ABCDEFGHJKMNPQRSTUVWXYZ23456789`, dibuat dengan `random_int` |
| Ketikan siswa | Huruf kecil diterima, spasi dibuang |
| Keunikan | Indeks unik di semua riwayat token. Bila bentrok, buat ulang (paling banyak 5 kali) |
| Berlaku | Hanya saat sesinya buka, dan hanya token terakhir sesi itu |
| Ganti token | *Buat token baru* mencabut token lama dan membuat yang baru. Riwayatnya disimpan untuk L1 (`field_session_tokens`). Siswa yang sudah masuk dengan token lama tidak terganggu |
| Batas coba | 10 token salah per 10 menit per siswa (atau per alamat IP berhash saat mendaftar), lalu jeda 5 menit dengan pesan ramah |
| Audit | `field_session_created`, `field_session_token_regenerated`, `field_session_closed`, `field_session_hours_changed` |

Pemeriksaan token sekaligus memeriksa sekolah dan rombel siswa (K8), keanggotaan susulan (K12), dan studi akun (K20).

| Penolakan | Kode alasan |
| --- | --- |
| Token tidak ada | `token_unknown` |
| Sesi belum buka | `not_open_yet` |
| Sesi sudah tutup | `closed` |
| Token untuk kelas lain | `other_class` |
| Susulan untuk teman lain | `not_member` |
| Akun sudah punya sesi game di studi lain (K20) | `other_study` |
| Siswa nonaktif karena status mundur ([30 → Status mundur dan akun nonaktif](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#53-status-mundur-dan-akun-nonaktif)) | `inactive` |

Setiap penolakan punya pesannya sendiri ([§16](#16-layar-dan-teks)). Penolakan `inactive` memakai pesan `Game.loginInactive` dari bagian 4; biasanya sesi siswa itu sudah berakhir lebih dulu lewat `ParticipantCheck`, jadi pemeriksaan token adalah pengaman kedua.

### 6.4 Siswa mengetik token (P2–P5)

1. Siswa masuk lewat `/masuk` seperti sekarang.
2. Bila `field_mode` menyala, `SessionService::login()` tidak membuka atau melanjutkan sesi game. Akun diikat ke sesi PHP tanpa sesi game, lalu siswa diarahkan ke `/token`.
   - **Pengecualian:** bila siswa punya sesi game yang terikat ke sesi lapangan yang sedang buka, sesi itu dilanjutkan dan siswa langsung ke peta (K7).
3. Di `/token`, siswa mengetik token (`mk-tk-01`). Kode penelitiannya tampil di atas kotak token.
4. Token diterima:
   - sesi game baru dibuat dengan `field_session_id` dan `meeting_no`;
   - event `session_started` dengan `via: token`;
   - siswa diarahkan ke isi pertemuan: peta wilayah yang dibuka, atau `/intro` di P1.
5. Token ditolak: pesan sesuai alasan, siswa tetap di `/token` (`mk-tk-02`).
6. **Layar kecil ([33 §7.2](33_IMPLEMENTASI_MODE_PENELITIAN.md#72-layar-kecil)).** Formulir `/token` juga mengirim `stage_w` (lebar panggung, diukur klien). Dalam mode penelitian, nilai di bawah `research_studies.min_stage_px` (bawaan 960) ditolak dengan pesan layar kecil **sebelum** sesi game dibuat, supaya tidak ada sesi lapangan yang tercatat dari ponsel. Nilai kosong (JavaScript mati) diterima; klien tetap menahan. Penolakan dicatat sebagai audit sesi lapangan `small_screen_refused`, tanpa data siswa.

`/token` memakai formulir biasa (bekerja tanpa JavaScript), seperti wizard pendaftaran. Halaman ini, `/ganti-sandi`, dan `/keluar` bisa dibuka siswa yang sudah masuk tanpa sesi game.

### 6.5 Pendaftaran dengan token (P1)

1. `/daftar` dalam mode penelitian dimulai dengan layar token (langkah 0). Formulir langkah 0 juga mengirim `stage_w`, dan panggung di bawah `research_studies.min_stage_px` ditolak seperti di `/token` ([§6.4](#64-siswa-mengetik-token-p2p5) no. 6, [33 §7.2](33_IMPLEMENTASI_MODE_PENELITIAN.md#72-layar-kecil)): pesan layar kecil yang sama, sebelum wizard berlanjut dan akun dibuat, dengan audit sesi lapangan `small_screen_refused` tanpa data siswa ([§22](#22-keputusan-pengembang) no. 14).
2. Token harus milik sesi pertemuan 1 yang buka, atau susulannya. Untuk susulan P1 belum ada akun yang bisa dicentang, jadi keanggotaan tidak diperiksa saat mendaftar.
3. Sesi lapangan disimpan di wizard (`reg_wizard`).
4. Langkah 1 (nama, umur, jenis kelamin, avatar) tetap.
5. Langkah 2 (negara, provinsi, kabupaten, sekolah) dilewati. Data perangkat tetap direkam (M-11).
6. Langkah 3 (nama pengguna, kata sandi, persetujuan) tetap.
7. `SessionService::registerAndStart()` membuat siswa dengan `school_id` dan `rombel` dari sesi lapangan, membuat kode peserta ([§10.2](#102-kode-peserta)), menyisipkan satu baris `participant_consents` berisi bawaan (izin rekaman dan butir risiko Tidak, [30 → Izin berkode per siswa](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#52-izin-berkode-per-siswa)), dan membuat sesi game yang terikat ke sesi lapangan, semuanya dalam satu transaksi.
8. Halaman berikutnya menampilkan kartu besar "Kode penelitian" ([10](10_TELAAH_PENDAHULUAN_MEDIA.md), M-11). Halaman itu `/tes` (pembuka modul tes) bila modul terbuka ([34 §5.1–5.2 → Arah setelah masuk](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#52-arah-setelah-masuk)), selain itu `/intro`.

### 6.6 Sesi ditutup saat siswa sedang bermain

- Sesi game yang terikat ke sesi lapangan yang tutup dicatat sekali saja:
  - status `paused`, `ended_at` diisi;
  - event `session_paused {via: field_session_closed}`;
  - lewat UPDATE bersyarat `status IN ('active','paused') AND ended_at IS NULL`, jadi dua permintaan bersamaan tidak menulis event ganda.
- Ini terjadi saat P1 menutup sesi (untuk semua sesi game terikat), atau saat permintaan pertama siswa setelah jam tutup.
- Pengerjaan yang sedang terbuka dibiarkan apa adanya (K14). Melanjutkannya di pertemuan lain adalah urusan no. 16.
- `RetentionService::abandonStaleSessions()` melewati sesi game yang punya `field_session_id`, supaya tidak menulis `session_abandoned` 30 hari kemudian.
- `GameSessionModel::resumable()` (dipanggil dari `SessionService::login()`) dalam mode penelitian hanya mengembalikan sesi game yang sesi lapangannya sedang buka.

### 6.7 Sesi susulan dan anggotanya

1. Di halaman sesi utama yang sudah tutup, P1 menekan *Buat sesi susulan*. Kodenya `-S`, berikutnya `-S2`.
2. Panel menampilkan siswa rombel itu (kode saja). Yang tidak punya sesi game di sesi utamanya sudah tercentang.
3. P1 memeriksa, mengubah bila perlu, lalu menyimpan ke `field_session_members`. Perubahan anggota dicatat di log audit.
4. Siswa yang bukan anggota mendapat pesan `not_member`.
5. Susulan P1 (siswa belum punya akun) memakai pendaftaran dengan token tanpa pemeriksaan anggota ([§6.5](#65-pendaftaran-dengan-token-p1)).
6. Susulan skala sikap 7 hari setelah P5 (no. 21) adalah susulan P5 biasa. Sesinya dibuka pada hari pelaksanaannya dan tetap dalam batas 4 jam sesi lapangan ([§6.1](#61-isi-dan-keadaan-sesi-lapangan); keputusan pengembang 3 November 2026, [04](04_CATATAN_KEPUTUSAN.md)).

---

## 7. Game tertutup di luar sesi

**Pemeriksaan.** `ParticipantCheck::checkParticipant()` mendapat status baru `CLOSED` sesudah pemeriksaan sesi game. Statusnya `CLOSED` bila `field_mode` menyala dan salah satu benar:
- sesi game tidak terikat sesi lapangan;
- sesi lapangannya tutup;
- siswa belum punya sesi game (masuk tanpa token).

Pemeriksaan ini dipakai `GameSessionFilter`, `ApiSessionFilter`, dan `ParticipantAuthFilter`. `ParticipantAuthFilter` menjaga `/token` dan `/sampai-jumpa` (§15), jadi ia **tidak** menolak status `CLOSED`; ia tetap hanya memastikan siswa sudah masuk.

**Jawaban:**
- `GameSessionFilter`: tanpa sesi game, arahkan ke `/token`; dengan sesi game yang sesi lapangannya tutup, arahkan ke `/sampai-jumpa`.
- `ApiSessionFilter`: `409` dengan kode `FIELD_SESSION_CLOSED`. `public/assets/js/core/api.js` mendapat cabang baru yang membuka layar "sampai jumpa".

**Pengecualian (tetap terbuka):**

| Jalur | Alasan |
| --- | --- |
| `POST /api/events`, `POST /api/audio-events` | Event yang tertunda di perangkat tetap masuk. Sekarang jawaban 401, 403, atau 422 membuat `core/events.js` membuang antrean (`keepForLater()`). Penanda `outside_session` dihitung kemudian dari jam event dan jam sesi (bagian 3, kamus) |
| `POST /api/session/heartbeat` | Diterima tanpa memperpanjang apa pun bila sesi lapangan tutup |
| `/token`, `/sampai-jumpa`, `/ganti-sandi`, `/keluar` | Halaman keluar-masuk |
| `/`, `/mulai`, `/masuk`, `/daftar…`, `/tentang`, `/bahasa` | Sudah terbuka untuk tamu |

**Layar `/sampai-jumpa`:**
- Isi: "Permainan dibuka waktu sesi bersama kakak peneliti, ya." (`mk-tk-03`).
- Tombol *Keluar*, dan *Masukkan token* bila siswa sudah di sekolah untuk sesi berikutnya.
- Tanpa HUD serpihan dan tanpa tautan ke Pustaka atau Profil.

**Studi ditutup.** Saat status studi `closed`, pemeriksaan ini mati dan game terbuka seperti biasa. Kemajuan per siswa (§8) tetap dipakai.

---

## 8. Kemajuan mengikuti siswa, bukan sesi game

**Masalah.** Sekarang kemajuan tersimpan per sesi game:
- `session_progress` satu baris per sesi;
- `ChallengeService::refreshProgress()` membaca `ChallengeAttemptModel::completedForSession($session->id)`.

Bila setiap pertemuan membuat sesi game baru (K6), siswa akan mulai dari kosong di setiap pertemuan.

**Perubahan:**
- **Tabel baru `participant_progress`** (unik per `participant_id`, `study_id`) menggantikan `session_progress`. Kolomnya sama: wilayah dan tantangan terakhir, `unlocked_level_sequence`, tantangan dan wilayah tuntas, total bintang.
- **`refreshProgress()`** membaca pengerjaan tuntas semua sesi game siswa itu di studi yang sama (metode baru `ChallengeAttemptModel::completedForParticipant()`), lalu menulis `participant_progress` dengan UPDATE bersyarat.
- **Semua pembaca `session_progress` pindah:**
  - `Concerns\GameProgress`, `GameContext`;
  - `MapController`, `ChallengeApiController`;
  - `SessionService`, `ChallengeService`;
  - `DashboardController`, `SessionController`, `ParticipantController`;
  - `RawDataService`, `SystemResetService`, `RetentionService`;
  - `Simulation\SimClock`, label di `Admin.php`.
- **Tabel `session_progress` dibuang** di migrasi yang sama, **setelah** `participant_progress` diisi dari pengerjaan tuntas yang sudah ada (per siswa per studi, dengan aturan yang sama dengan `refreshProgress()`), supaya siswa di server produksi tidak kehilangan kemajuan dan `main` tetap bisa dirilis ([§22](#22-keputusan-pengembang) no. 11). `gelita:reset` tetap dijalankan sebelum penelitian.
- **Riwayat variasi** sudah per siswa lintas sesi (`ChallengeService`, `variantsServed`), jadi tidak berubah.
- **Kemajuan tidak berpindah antarstudi.** Setiap uji adalah studi sendiri (K1), jadi akun baru di uji berikutnya mulai dari awal; akun lama tidak bisa dipakai di studi lain (K20).
- **Berlaku juga saat `field_mode` mati.** Siswa yang sesinya selesai lalu masuk lagi tidak lagi mulai dari kosong. Ini juga menutup celah di `SessionService::login()`.

---

## 9. Wilayah yang dibuka per pertemuan

Dalam mode penelitian, aturan buka dibaca dari rencana pertemuan sesi lapangan yang sedang dipakai, bukan dari `unlock_mode`.

| Pertemuan | Yang terbuka |
| --- | --- |
| P1 | Cerita pembuka (`/intro`) dan pembuka modul tes (titik sambung no. 22). Tidak ada wilayah. Peta Kedu tampil dengan semua wilayah terkunci |
| P2, P3, P4 | Satu wilayah dari rencana, walau wilayah sebelumnya belum tuntas (11). Wilayah lain terkunci, termasuk yang sudah tuntas |
| P5 | Ketiga wilayah, untuk melanjutkan tantangan yang tertinggal. Batas 20 menit dijaga P1 (lihat [§21](#21-pertanyaan-terbuka) no. 2), lalu modul tes, cerita penutup, skala sikap, dan kritik dan saran (titik sambung no. 21, 22) |
| KG2 (`satu_sesi`) | Wilayah yang dipilih saat sesi lapangan dibuat, langsung setelah mendaftar ([§5.2](#52-rencana-pertemuan)) |

**Di dalam wilayah**, tantangan tetap berurutan (tantangan n terbuka bila n − 1 tuntas). Sekarang urutan ini hanya diperiksa di halaman (`Game\ChallengeController`, baris 25 dan 47, memanggil `nodeUnlocked()` sebelum `openNode()`). Aturan ini kini diperiksa juga di `ChallengeService::openNode()` lewat metode baru `assertNodeUnlocked()`, sehingga `ChallengeApiController::open` tidak bisa melompati urutan (K17).

**Kenali wilayah, pengerjaan ulang, dan layar kecil** mengikuti no. 16 dan tidak diatur di sini.

**Tempat perubahan:**
- `Concerns\GameProgress::levelOverview()`, `levelUnlocked()`, `nodeOverview()`, `nodeUnlocked()`, `lanternMap()`, `regionStatus()`: bila `field_mode` menyala, wilayah terbuka = wilayah di rencana pertemuan.
- `ChallengeService::assertLevelUnlocked()` dan `refreshProgress()`: `unlocked_level_sequence` tidak dipakai dalam mode penelitian.
- Semua pemanggil di `MapController`, `ChallengeController`, `DialogueController`, `LibraryController`, `ContentApiController`, `ChallengeApiController`, `ProgressApiController` tetap memanggil metode yang sama.

---

## 10. Kode sekolah penelitian dan kode peserta

### 10.1 Kode sekolah penelitian

- **Kolom:** `schools.research_code VARCHAR(3)`, unik bila terisi, pola `^[SUV][0-9]{2}$`.
- **Diisi admin** di halaman sekolah (`/admin/sekolah`), dari daftar yang disusun peneliti (daftar kerja no. 12, belum ada). Pemetaan NPSN ke kode hanya dilihat admin.
- **Bila sudah ada data**, kode tidak bisa diubah. Panel menolak bila sudah ada siswa dengan sekolah itu.
- **Peneliti** memilih sekolah dengan kode saja di formulir sesi lapangan.
- **`RawDataService::schoolRef()`** mengembalikan `research_code` bila terisi, lalu `SCH-000123` seperti sekarang. Ini satu-satunya tempat tampilan kode sekolah ([27 → Panel peneliti](27_IMPLEMENTASI_PERAN_PENELITI.md#8-panel-peneliti)).
- **Awalan `U`** menandai sekolah uji coba: satu urutan `U01`, `U02`, … untuk semua uji (KG2, KG3a, KG3b). Uji yang diikuti dicatat lewat studi, bukan di kode, dan satu sekolah boleh menjadi tempat lebih dari satu uji ([03](03_KODE_DAN_ARTI.md); [04](04_CATATAN_KEPUTUSAN.md), 1 November 2026). Variabel `is_pilot` diturunkan dari awalan ini ([11](11_KAMUS_DATA.md#penanda-untuk-analisis)).
- **Awalan `V`** hanya untuk instalasi uji validator.

### 10.2 Kode peserta

- **Bentuk:** `{kode sekolah}-{rombel}{nomor dua angka}`, misalnya `S03-A07`, `U01-A03`.
- **Helper `participant_code()`** di `gelita_helper.php` mendapat bentuk baru; bentuk `GLT-%06d` tetap untuk pendaftaran di luar mode penelitian.
- **Nomor urut** dari tabel `participant_code_counters` (unik per `school_id`, `rombel`):
  1. Baris penghitung dibuat (`INSERT IGNORE`) saat sesi lapangan pertama rombel itu dibuat, supaya tidak ada perlombaan saat menyisipkan.
  2. Saat mendaftar: baca `next_no`, lalu `UPDATE … SET next_no = next_no + 1 WHERE id = ? AND next_no = ?`. Bila baris terubah 0, baca ulang dan coba lagi, paling banyak 5 kali.
  3. Indeks unik `participants.participant_code` tetap menjadi penjaga terakhir.
  4. Penghitung **tidak** per studi. Bila sekolah `U01` menjadi tempat KG3a lalu KG3b, nomor rombel A berlanjut (`U01-A13`, …), sehingga kode tidak pernah berulang antaruji.
- **Helper bersama.** `ChallengeService::updateIf()` dan `updateOrStale()` sekarang privat. Keduanya dipindah ke trait baru `App\Models\Concerns\ConditionalUpdate` (folder `Concerns` di `app/Models/` juga baru) supaya penghitung ini, penutupan sesi (§6.6), dan `ChallengeService` memakai pola yang sama.
- **Batas 99 per rombel.** Pendaftaran ke-100 ditolak dengan pesan untuk siswa: "Kode untuk kelasmu sudah penuh. Panggil kakak peneliti, ya." Panel menampilkan peringatan untuk P1.
- **Kolom baru `participants.rombel CHAR(1)`**, diisi dari sesi lapangan.
- **Tidak pernah diubah** (01). Tidak ada halaman atau perintah yang mengubah `participant_code`, `school_id`, atau `rombel` setelah siswa punya sesi game. Pembetulan data pendaftaran di bagian 4 tidak mencakup ketiganya.
- **Tampil di:** kartu "Kode penelitian" setelah mendaftar, atas layar token, dan halaman Profil ([02 → L2](02_LEMBAR_LAPANGAN.md#l2-peta-tempat-duduk-perangkat-dan-kode-peserta)). Perubahan Profil adalah perubahan teks kecil di view yang ada.

---

## 11. Isian L2: kursi, buklet, dan bentuk tes

**Halaman.** `/admin/sesi-lapangan/{id}/kursi` (kemampuan `field_sessions.manage`). Satu baris per siswa rombel itu, kode saja. Isian: **baris** dan **kursi** (angka, dihitung dari depan dan dari kiri dilihat dari papan tulis, [02 → L2](02_LEMBAR_LAPANGAN.md#l2-peta-tempat-duduk-perangkat-dan-kode-peserta)).

**Yang dihitung panel saat disimpan (K10):**

| Hasil | Aturan | Sumber |
| --- | --- | --- |
| Buklet | baris ganjil dan kursi ganjil = B1; ganjil/genap = B2; genap/ganjil = B3; genap/genap = B4 | 02 → L2; 06 |
| Bentuk tes | baris + kursi genap: tes awal A, tes akhir B; ganjil: tes awal B, tes akhir A | [13](13_TES_AWAL_AKHIR.md) |

**Keseimbangan per rombel:**
- Panel menampilkan jumlah B1, B2, B3, B4.
- Bila selisih terbesar dan terkecil lebih dari 1, tampil peringatan kuning: "Buklet belum seimbang. Periksa lagi baris dan kursi di L2." Penyimpanan tetap berjalan.
- Panel juga menampilkan "Semua siswa sudah punya bentuk tes" bila setiap siswa rombel itu punya baris dan kursi. Kalimat ini titik sambung untuk mulainya tes awal (no. 22).

**Melekat pada siswa.** Buklet dan bentuk tes disimpan di `participant_seats` per siswa per studi.
- Sampai tes awal siswa itu dimulai (titik sambung no. 22, kolom `locked_at`), peneliti boleh mengubah baris dan kursi. Setiap perubahan dicatat di log audit (`seat_saved`, `seat_changed`) dengan nilai lama dan baru.
- Sesudah itu terkunci.
- Pindah tempat duduk di P2–P5 hanya ditulis di L2 kertas (02).

**L2 kertas.** Kolom Buklet dan Bentuk tes awal di L2 disalin dari panel, bukan diisi lebih dulu ([§23](#23-akibat-ke-dokumen-lain)).

---

## 12. Panel pemantauan

**Halaman** `/admin/pemantauan` (kemampuan `monitoring.view`).

**Isinya**, per sesi lapangan yang buka hari itu dan yang tutup kurang dari 24 jam lalu:

| Isi | Peneliti dan ketua | Admin (K2) |
| --- | --- | --- |
| Kode sesi, jam buka–tutup, keadaan | Ya | Ya |
| Token saat ini | Ya | — |
| Siswa masuk (punya sesi game di sesi ini) | Jumlah dan daftar kode | Jumlah |
| Aktif sekarang (`last_active_at` ≤ 10 menit, `AnalyticsService::ACTIVE_MINUTES`) | Jumlah dan daftar kode | Jumlah |
| Belum masuk (siswa rombel tanpa sesi game di sesi ini) | Daftar kode, bahan susulan | Jumlah |
| Kemajuan wilayah pertemuan (tantangan tuntas dari 5) | Per kode | Jumlah tuntas semua |
| Tantangan tertinggal untuk susulan P5 | Per kode | — |
| Event hilang (jumlah `dropped` dari `events_dropped` sesi game di sesi ini) | Jumlah per kode | Jumlah |
| Izin belum diisi (siswa rombel tanpa tanggal izin orang tua, [30 → Izin berkode per siswa](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#52-izin-berkode-per-siswa)) | Jumlah | — (isian izin dan angkanya tidak terlihat admin, [§21](#21-pertanyaan-terbuka) no. 7) |
| Siswa sesi itu yang belum punya kursi (tanpa kursi, tantangan 2–5 tidak terbuka: `BOOKLET_MISSING`, [31 §11.2](31_IMPLEMENTASI_BANK_SOAL_BARU.md#112-memilih-variasi-di-opennode), K10) | Daftar kode | Jumlah ([§22](#22-keputusan-pengembang) no. 13) |
| Perangkat layar kecil ditolak (jumlah audit `small_screen_refused` sesi ini, [33 §7.2](33_IMPLEMENTASI_MODE_PENELITIAN.md#72-layar-kecil)) | Jumlah | Jumlah ([§22](#22-keputusan-pengembang) no. 13) |
| Status tes awal/akhir per kode (`belum`, `sedang`, `selesai`, `susulan`, `hilang`), dari modul tes, tanpa jawaban, skor, atau tanda butir risiko ([34 §14](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#14-titik-sambung-ke-perubahan-lain)) | Per kode, dengan tombol *Tambah waktu* per siswa (`POST /admin/sesi-lapangan/{id}/tambah-waktu`, `field_sessions.manage`, 34 K3, [34 → Batas waktu tersembunyi](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#74-batas-waktu-tersembunyi), 34 §12) | — |
| Tombol *Mulai tes awal* / *Mulai tes akhir* di halaman sesi lapangan P1 dan P5 ([34 → Mulai tes bersama](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#53-mulai-tes-bersama)) | Ya | — |

**Penyegaran:**
- Data dari `GET /api/admin/monitoring?session={id}` setiap 30 detik.
- Bila gagal, jeda bertambah. Polanya disalin dari `public/assets/js/admin/export.js`, berkas baru `public/assets/js/admin/monitoring.js`.
- Permintaan ini membawa kepala `X-Gelita-Background: 1`, yang tidak dihitung sebagai aktivitas staf ([27 → Keamanan masuk staf](27_IMPLEMENTASI_PERAN_PENELITI.md#111-keamanan-masuk-staf)).

**Ringkasan.** Kartu baru "Sesi lapangan aktif" (kode sesi, jumlah masuk, jumlah aktif) untuk peneliti dan ketua di `/admin`, dengan tautan ke pemantauan.

**Kueri.** Metode baru di `AnalyticsService`: `fieldSessionSummary()`, `fieldSessionRoster()`, `droppedEvents()`. Event hilang dibaca dari `game_event_logs` (`event_type = 'events_dropped'`, indeks `(event_type, occurred_at)`), dijumlahkan dari `payload_json.dropped`.

**Untuk operasional, bukan hasil penelitian.** Panel tidak menampilkan jawaban, skor, atau bintang. Untuk modul tes, panel hanya menunjukkan status tes dan "modul selesai"; `risk_offered`, `risk_reconsent`, dan jawaban butir risiko tidak pernah tampil (34 §8.2, 30 §5.4).

---

## 13. Alat bantu `gelita:uji:akun` (perubahan no. 24)

Untuk instalasi uji validator ([20 → Prasyarat no. 8](20_PAKET_VALIDASI.md#prasyarat-no-8-instalasi-uji-dan-akun-uji)), dengan pola `gelita:simulasi` (`app/Commands/Simulation.php`).

**Kapan dibangun** (diputuskan pengembang 1 November 2026, [§22](#22-keputusan-pengembang) no. 7). Alat ini bisa dibangun sendiri lebih awal dengan tabel yang sekarang ada, tetapi pengembang memilih tetap di gelombang kode D bersama bagian ini, karena rancangannya memakai `research_code`, `rombel`, `participant_progress` (bagian ini), dan `participant_consents` (bagian 4). Akibatnya instalasi uji tahap 2 (*commit* yang dibekukan, prasyarat no. 4) tetap memakai cara di 20 → Prasyarat no. 8 langkah 3 dan 6: akun lewat `/daftar` dan akun cadangan, tanpa reset per akun. Alat ini pertama dipakai di instalasi uji G3.

**Perintah:**

```text
php spark gelita:uji:akun --buat                  # all accounts from the default list
php spark gelita:uji:akun --buat --nama=uji.cadangan3
php spark gelita:uji:akun --reset=uji.bahasa1     # delete this account's play data
php spark gelita:uji:akun --daftar                # list accounts, codes, last play
```

**Menolak berjalan bila:**
- `ENVIRONMENT === 'production'`;
- ada siswa yang nama penggunanya tidak berawalan `uji.`;
- tidak ada studi aktif.

Polanya `Simulation::refusal()`.

**Membuat akun (`--buat`), lewat layanan sendiri `TestAccountService`, bukan wizard pendaftaran:**
- **Daftar bawaan** dari [03](03_KODE_DAN_ARTI.md): `uji.bahasa1`, `uji.bahasa2`, `uji.digital1`, `uji.digital2`, `uji.ukur`, `uji.budaya1`–`uji.budaya3`, `uji.media1`–`uji.media3`, `uji.cadangan1`, `uji.cadangan2`. Isian lain (nama tampil huruf saja, umur 11) mengikuti 20 → Prasyarat no. 8.
- **Sekolah** `Sekolah Uji Validator`, dibuat bila belum ada, dengan `research_code = V01`. Rombel `A`.
- **Kode** `UJI-01`, `UJI-02`, … (bukan pola kode peserta, 20).
- **Kata sandi** acak yang memenuhi `PasswordPolicy`, ditampilkan sekali di layar dan tidak disimpan di berkas.
- **Akun yang sudah ada** dilewati dengan pesan.
- **Izin** satu baris `participant_consents` dengan tanggal izin orang tua hari itu dan izin butir risiko Ya, supaya validator bisa melihat T11 ([30 → Izin berkode per siswa](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#52-izin-berkode-per-siswa)).
- **Log audit** `test_account_created` dengan pelaku kosong (`via: cli`).

**Mengatur ulang (`--reset`):**
- Menghapus data permainan akun itu: sesi game, pengerjaan, percobaan, jawaban, event, `participant_progress`, kursi. Caranya lewat cakupan penghapusan `RetentionService` (`HARD_ORDER`, `scopedBuilder()`), yang dibuka untuk satu siswa.
- Akunnya tetap, begitu juga baris `participant_consents`-nya. Masuk berikutnya mulai dari awal.
- Log audit `test_account_reset`.

**Di G3 (ahli media langkah 2):**
- Pengembang menyalakan mode penelitian di instalasi uji dan membuat satu sesi lapangan `V01-A-P2` (atau pertemuan lain yang dinilai) yang buka selama masa telaah, paling lama 4 jam per sesi. Bila perlu, satu sesi baru per hari.
- Validator mengetik tokennya, sehingga layar token ikut dinilai.
- Di tahap 2, mode penelitian tetap mati dan `unlock_mode = free` (20).

---

## 14. Skema database

**Migrasi `2026-01-01-003500_CreateFieldSessions`.** Nomor sesudah migrasi bagian 1 (`003400`). Urutan akhir migrasi bagian 1–4 ada di [27 → Migrasi](27_IMPLEMENTASI_PERAN_PENELITI.md#61-migrasi-2026-01-01-003400_replaceteacherwithresearcherroles).

| Tabel atau kolom | Isi |
| --- | --- |
| `field_sessions` (baru) | `id`, `study_id` FK, `code` VARCHAR(20), `school_id` FK, `rombel` CHAR(1), `meeting_no` TINYINT, `makeup_no` TINYINT bawaan 0, `main_session_id` FK diri sendiri (null untuk sesi utama), `session_date` DATE, `opens_at`, `closes_at`, `closed_at` (null), `region_code` VARCHAR(20) (null; hanya untuk `satu_sesi`), `created_by` FK staf, `closed_by` FK staf, `created_at`, `updated_at`. Unik (`study_id`, `code`). Kolom `test_started_at` DATETIME (null) dan `test_started_by` FK staf ditambahkan migrasi bagian 8 ([34 → Tabel yang diubah](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#112-tabel-yang-diubah)) |
| `field_session_tokens` (baru) | `id`, `field_session_id` FK, `token` CHAR(6) unik, `created_by`, `created_at`, `revoked_at` (null) |
| `field_session_members` (baru) | `field_session_id` FK, `participant_id` FK, `added_by`, `created_at`. Unik pasangan. Hanya untuk susulan |
| `participant_seats` (baru) | `id`, `participant_id` FK, `study_id` FK, `row_no` TINYINT, `seat_no` TINYINT, `booklet` CHAR(2), `pre_form` CHAR(1), `post_form` CHAR(1), `entered_by`, `entered_at`, `updated_at`, `locked_at` (null). Unik (`participant_id`, `study_id`) |
| `participant_code_counters` (baru) | `id`, `school_id` FK, `rombel` CHAR(1), `next_no` TINYINT bawaan 1. Unik (`school_id`, `rombel`) |
| `participant_progress` (baru) | Kolom `session_progress` dengan kunci `participant_id`, `study_id` (unik). Diisi dari pengerjaan tuntas yang ada, lalu `session_progress` dibuang |
| `game_sessions` | Kolom baru `field_session_id` FK (null), `meeting_no` TINYINT (null). Indeks (`field_session_id`, `participant_id`) |
| `participants` | Kolom baru `rombel` CHAR(1) (null) |
| `schools` | Kolom baru `research_code` VARCHAR(3) unik (null) |
| `research_studies` | Kolom baru `field_mode` TINYINT(1) bawaan 0, `meeting_plan` VARCHAR(20) bawaan `lima_pertemuan` (K19) |

**Kolom dari bagian lain.** `field_sessions.test_started_at` DATETIME (null) dan `test_started_by` FK staf (`ON DELETE SET NULL`) dibuat migrasi bagian 8 ([34 §11.2](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#112-tabel-yang-diubah)), bukan migrasi ini; tombol *Mulai tes awal/akhir* mengisinya dengan UPDATE bersyarat `test_started_at IS NULL` (34 §5.3). `participant_seats.locked_at` diisi modul tes saat sesi tes awal siswa dibuat (34 K10).

Semua kunci asing ke `staff_users` memakai `ON DELETE SET NULL`. Kunci asing ke `participants` (`field_session_members`, `participant_seats`, `participant_progress`) memakai `ON DELETE CASCADE`, sesuai aturan hapus bagian 4 ([30 → Yang dihapus sistem](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#73-yang-dihapus-sistem)); `RetentionService::HARD_ORDER` tetap menghapusnya satu per satu, supaya jumlah barisnya masuk pratinjau.

**Ikut diperbarui:**
- `tests/_support/Database/ResetTables.php` (skema SQLite).
- `SystemResetService`: `GROUP_A`, `CLEAR_ORDER`, `REQUIRED_MIGRATION` menjadi `003500` (nilai akhir setelah bagian 4: `003800`), `nextParticipantCode()`.
- `RetentionService`: `HARD_ORDER` mendapat `participant_progress` (menggantikan `session_progress`), `participant_seats`, dan `field_session_members`; `scopedBuilder()`.
- Model baru: `FieldSessionModel`, `FieldSessionTokenModel`, `FieldSessionMemberModel`, `ParticipantSeatModel`, `ParticipantProgressModel`. `SessionProgressModel` dihapus.
- `GameSessionModel` dan `ParticipantModel`: `allowedFields`.

**Layanan baru:**
- `FieldSessionService`: buat, ubah jam, tutup, token, susulan, pemeriksaan token.
- `ParticipantCodeService`: penghitung dan pembentuk kode.

Keduanya didaftarkan di `app/Config/Services.php`.

---

## 15. Rute

**Game** (`app/Config/Routes.php`, slug berbahasa Indonesia):

| Rute | Controller | Filter |
| --- | --- | --- |
| `GET /token`, `POST /token` | `FieldTokenController::form`, `::submit` | `participantAuth` (tanpa sesi game) |
| `GET /sampai-jumpa` | `FieldTokenController::closed` | `participantAuth` |
| `GET /daftar` (langkah 0 token) | `RegisterController::token` dan `::saveToken` dalam mode penelitian | seperti sekarang |

**Panel** (semua di grup `staffAuth`, kemampuan dari [27 → Model hak akses di kode](27_IMPLEMENTASI_PERAN_PENELITI.md#5-model-hak-akses-di-kode)):

| Rute | Controller::method | Kemampuan |
| --- | --- | --- |
| `GET /admin/sesi-lapangan` | `FieldSessionController::index` | `field_sessions.manage` |
| `GET`, `POST /admin/sesi-lapangan/baru` | `::create`, `::store` | `field_sessions.manage` |
| `GET /admin/sesi-lapangan/{id}` | `::show` | `field_sessions.manage` |
| `POST /admin/sesi-lapangan/{id}/jam` | `::updateHours` | `field_sessions.manage` |
| `POST /admin/sesi-lapangan/{id}/token-baru` | `::regenerateToken` | `field_sessions.manage` |
| `POST /admin/sesi-lapangan/{id}/tutup` | `::close` | `field_sessions.manage` |
| `GET`, `POST /admin/sesi-lapangan/{id}/kursi` | `::seats`, `::saveSeats` | `field_sessions.manage` |
| `GET`, `POST /admin/sesi-lapangan/{id}/susulan` | `::makeupForm`, `::storeMakeup` | `field_sessions.manage` |
| `GET /admin/sesi-lapangan/{id}/lembar`, `GET`, `POST …/lembar/unggah`, `POST …/lembar/simpan` | `FieldSheetController` (bagian 3, [29 → Rute dan perintah spark](29_IMPLEMENTASI_DATA_PENELITIAN.md#13-rute-dan-perintah-spark)) | `field_sessions.manage` |
| `GET`, `POST /admin/sesi-lapangan/{id}/izin` | `ConsentController` (bagian 4, [30 → Rute, perintah, dan skrip](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#16-rute-perintah-dan-skrip)) | `field_sessions.manage` |
| `GET /admin/pemantauan` | `MonitoringController::index` | `monitoring.view` |
| `GET /api/admin/monitoring` | `AdminApiController::monitoring` | `monitoring.view` |

**Lain-lain:**
- Studi (`field_mode`) memakai rute studi bagian 1 (`studies.manage`).
- Kode sekolah penelitian memakai rute sekolah (`schools.manage`).
- Daftarkan `sesi-lapangan/baru` sebelum `sesi-lapangan/(:num)`, seperti `peserta/reset-sandi` di 27 §7.
- `ScreenViewFilter::ROUTES` mendapat kode layar untuk `/token` dan `/sampai-jumpa` (`ScreenViewFilterTest`).
- Menu panel mendapat **Sesi lapangan** dan **Pemantauan** di kelompok Data, sesuai slot yang sudah dipesan di [27 → Panel peneliti](27_IMPLEMENTASI_PERAN_PENELITI.md#8-panel-peneliti).

---

## 16. Layar dan teks

Teks awal dari [18](18_MOCKUP_LAYAR_BARU.md) (`mk-tk-01`–`mk-tk-05`), diterima sebagai teks awal. Kalimat akhirnya ditetapkan setelah telaah ahli media dan uji keterbacaan ([24](24_UJI_KETERBACAAN.md)). Mengikuti `.claude/rules/ui-copy.md`. Kunci game punya pasangan `en`.

**Game (`app/Language/id/Game.php`, `Js.php`, dan pasangan `en`)**

| Kunci | Usulan (id) | Usulan (en) |
| --- | --- | --- |
| `tokenTitle` | Token Sesi | Session Token |
| `tokenHelp` | Ketik enam huruf dan angka token yang ditulis kakak peneliti di papan tulis, ya. | Type the six letters and numbers the research team wrote on the board. |
| `tokenUnknown` | Token belum cocok. Periksa lagi huruf di papan tulis, ya. Kalau masih belum bisa, angkat tangan. | That token doesn't match. Check the board again. Still stuck? Raise your hand. |
| `tokenNotOpenYet` | Sesi ini belum dibuka. Tunggu aba-aba kakak peneliti, ya. | This session hasn't started yet. Wait for the research team's signal. |
| `tokenClosed` | Sesi ini sudah ditutup. Tanyakan token baru ke kakak peneliti, ya. | This session has ended. Ask the research team for a new token. |
| `tokenOtherClass` | Token ini untuk kelas lain. Periksa lagi token di papan tulis kelasmu, ya. | This token is for another class. Check the token on your class board. |
| `tokenNotMember` | Token ini untuk sesi susulan teman lain. Tanyakan ke kakak peneliti, ya. | This token is for another student's catch-up session. Ask the research team. |
| `tokenOtherStudy` | Akun ini sudah dipakai di kegiatan lain. Panggil kakak peneliti, ya. | This account was used in another activity. Please call the research team. |
| `tokenTooMany` | Sudah banyak percobaan. Tunggu lima menit, lalu coba lagi, ya. | Too many tries. Wait five minutes, then try again. |
| `sessionClosedTitle` | Sampai jumpa di sesi berikutnya! | See you in the next session! |
| `sessionClosedBody` | Permainan dibuka waktu sesi bersama kakak peneliti, ya. | The game opens during sessions with the research team. |
| `researchCodeLabel` | Kode penelitian | Research code |
| `codeFull` | Kode untuk kelasmu sudah penuh. Panggil kakak peneliti, ya. | Your class has no codes left. Please call the research team. |

**Panel (`app/Language/id/Admin.php`)**

| Kunci | Usulan |
| --- | --- |
| `menuFieldSessions` | Sesi lapangan |
| `menuMonitoring` | Pemantauan |
| `fieldSessionNew` | Buat sesi lapangan |
| `fieldSessionTokenNew` | Buat token baru |
| `fieldSessionCloseNow` | Tutup sesi sekarang |
| `fieldSessionMakeup` | Buat sesi susulan |
| `fieldSessionDuplicate` | Rombel ini sudah punya sesi untuk pertemuan yang sama. Buka sesi itu atau buat susulan. |
| `seatBalanceWarn` | Buklet belum seimbang. Periksa lagi baris dan kursi di L2. |
| `seatAllHaveForm` | Semua siswa sudah punya bentuk tes. |
| `fieldModeLockedOpen` | Masih ada sesi lapangan yang buka. Tutup dulu sesinya. |
| `codesFullWarn` | Kode peserta untuk rombel ini sudah 99. Pakai rombel lain atau hubungi ketua peneliti. |

**Catatan media langkah 2.** Periksa apakah keyboard layar menutupi kotak token ([18](18_MOCKUP_LAYAR_BARU.md), `mk-tk-01`).

---

## 17. Data untuk kamus dan unduhan

Bagian ini hanya menyediakan data. Lembar dan kolom unduhan ditetapkan di bagian 3 (`29`), dan variabel baru masuk `kamus-1.1`.

| Data | Sumber di sistem | Variabel ([11 → Variabel peserta dan sesi](11_KAMUS_DATA.md#variabel-peserta-dan-sesi)) |
| --- | --- | --- |
| Kode sesi lapangan | `field_sessions.code` | `field_session_code` |
| Nomor pertemuan | `field_sessions.meeting_no`, disalin ke `game_sessions.meeting_no` | `meeting_no` |
| Isi yang dibuka | Diturunkan dari rencana pertemuan ([§5.2](#52-rencana-pertemuan)) | `region_opened` |
| Tanggal dan jam buka–tutup | `session_date`, `opens_at`, `closes_at`, `closed_at` | variabel sesi lapangan di 11 |
| Kode sekolah, rombel | `schools.research_code`, `participants.rombel` | `school_code`, `rombel` |
| Buklet, bentuk tes | `participant_seats` | `booklet`; bentuk tes di `kamus-1.1` |
| Susulan | `makeup_no`, `main_session_id` | baru, untuk `kamus-1.1` (`is_makeup`, `makeup_no`) |
| Riwayat token | `field_session_tokens` | untuk L1, bukan analisis |
| Di luar sesi | Jam event dibanding jam sesi lapangan | `outside_session` (dihitung lapis turunan, no. 9, [35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md)) |
| Tantangan terbuka atau belum | `game_sessions.field_session_id`, rencana pertemuan ([§5.2](#52-rencana-pertemuan)), `participant_progress` | `locked` dan `not_started` (dihitung lapis turunan, [35 §6.4](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#64-derived_challenges-satu-baris-per-peserta--tantangan-15)) |

Kunci gabung dengan lembar lapangan: kode peserta, `field_session_code`, dan jam WIB ([11 → Aturan gabung lembar lapangan](11_KAMUS_DATA.md#aturan-gabung-lembar-lapangan)).

---

## 18. Titik sambung ke perubahan lain

| Perubahan | Yang disediakan bagian ini | Yang dirancang di sana |
| --- | --- | --- |
| No. 20 bank baru (bagian 5, [31 §11](31_IMPLEMENTASI_BANK_SOAL_BARU.md#11-variasi-dari-buklet-dan-butir-jangkar-perubahan-no-4-20)) | `participant_seats.booklet`, `field_mode`; angka "belum punya kursi" di pemantauan ([§12](#12-panel-pemantauan)) | Buklet menentukan variasi pengerjaan pertama (`VariantPicker`, `BookletRules`); tanpa kursi, tantangan 2–5 tidak dibuka (`BOOKLET_MISSING`, 31 K10) |
| No. 16 mode penelitian (bagian 7, [33](33_IMPLEMENTASI_MODE_PENELITIAN.md)) | Sakelar `field_mode`, rencana pertemuan, pengerjaan terbuka saat sesi tutup, layar token | Melanjutkan pengerjaan pertama yang terputus, pengerjaan ulang, Kenali, layar kecil (`stage_w` di `/token` dan `/daftar`, angka penolakan di pemantauan, 33 §7.2), jam disembunyikan |
| No. 21 skala sikap (bagian 8, [34](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md)) | Susulan P5 dengan jam buka panjang | Layar skala dan `sikap_makeup` (34 §9, §10.3); Balai Refleksi hanya di sesi P5 dan susulannya dalam mode penelitian (34 K12) |
| No. 22 modul tes (bagian 8, [34](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md)) | Token P1 dan P5 membuka modul; bentuk A/B dan `locked_at` dari `participant_seats`; "semua siswa punya bentuk" | Modul tes, mulai tes bersama (`field_sessions.test_started_at`, `test_started_by`), status tes dan tombol *Tambah waktu* di pemantauan, arah `/tes` setelah daftar, susulan tes, angket; batas 20 menit di P5 dan angket terlambat ([§21](#21-pertanyaan-terbuka) no. 2–3, diputuskan di 34 K1 dan §10.2); rencana `satu_sesi` tanpa modul tes dan skala sikap (34 §5.1, K12) |
| No. 9 lapis turunan (bagian 9, [35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md)) | `game_sessions.field_session_id`, rencana pertemuan, `participant_progress`, data di §17 | `locked`/`not_started`, `outside_session`, dihitung di lapis turunan |
| No. 23 (bagian 4, [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md)) | Kode peserta, sesi lapangan, halaman kursi, angka "izin belum diisi" di pemantauan | Izin berkode per siswa di `/admin/sesi-lapangan/{id}/izin`, baris izin saat mendaftar, token menolak siswa nonaktif, penanda tindak lanjut |
| Bagian 3 ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md)) | Data di §17, `events_dropped` per sesi | Lembar unduhan, kamus, log teknis admin, templat dan unggah lembar di `/admin/sesi-lapangan/{id}/lembar`, `session_paused {via: idle}` dari `markStale`, penanda `outside_session` |

---

## 19. Pengujian dan kriteria selesai

### 19.1 Tes baru

| Tes | Yang diperiksa |
| --- | --- |
| `FieldTokenTest` | Abjad dan panjang token; huruf kecil dan spasi diterima; keunikan dengan bentrok buatan; berlaku hanya saat buka; token lama ditolak setelah diganti; batas 10 coba per 10 menit |
| `FieldSessionLifecycleTest` | Kode sesi dan susulan (`-S`, `-S2`); penolakan sesi ganda; ubah jam; tutup lebih awal; satu event `session_paused {via: field_session_closed}` walau dua permintaan bersamaan (pola `Factories::injectMock`) |
| `FieldGateTest` | Mode penelitian menyala: halaman tanpa token ke `/token`, sesi tutup ke `/sampai-jumpa`, API 409 `FIELD_SESSION_CLOSED`. `/api/events`, `/api/audio-events`, heartbeat, `/keluar`, `/ganti-sandi` tetap terbuka. Studi `closed` membuka game |
| `TokenMembershipTest` | Kelas lain ditolak; susulan hanya anggota; anggota tercentang lebih dulu dari yang tidak hadir; akun dengan sesi game di studi lain ditolak `other_study` |
| `RegisterWithTokenTest` | Langkah 0 token; langkah sekolah dilewati; `school_id`, `rombel`, kode `S03-A01` berurutan; sesi game terikat; batas 99 |
| `ParticipantCodeRaceTest` | Dua pendaftaran bersamaan di rombel yang sama mendapat nomor berbeda (pola `RegisterWizardTest::testRegistrationCommittedAlongsideGetsItsOwnCode`) |
| `MeetingUnlockTest` | P2 membuka Temanggung saja; P3 membuka Magelang walau Temanggung belum tuntas; P5 membuka ketiganya; urutan tantangan dijaga di API (`ChallengeApiController::open`); rencana `satu_sesi` mendaftarkan lalu membuka wilayah yang dipilih |
| `ProgressBackfillMigrationTest` | Migrasi `003500` mengisi `participant_progress` dari pengerjaan tuntas di beberapa sesi game sebelum `session_progress` dibuang; tantangan dan bintang sama |
| `ProgressAcrossMeetingsTest` | Tantangan tuntas di P2 tetap tuntas di P5 dengan sesi game berbeda; masuk ulang setelah sesi selesai tidak mulai dari kosong (juga saat `field_mode` mati) |
| `SeatRulesTest` | Buklet dan bentuk dari baris dan kursi; peringatan keseimbangan; terkunci setelah `locked_at`; audit perubahan |
| `MonitoringTest` | Angka masuk, aktif, belum masuk, event hilang; admin tanpa kode dan tanpa token; peneliti dengan kode |
| `TestAccountCommandTest` | Menolak di produksi dan bila ada siswa bukan `uji.*`; membuat daftar bawaan dengan `V01` dan `UJI-01`; `--reset` menghapus data permainan dan menyisakan akun |
| `ReservedPrefixTest` | `uji.` dan `sim_` ditolak di pendaftaran siswa |

### 19.2 Tes yang ditulis ulang

- `RegisterWizardTest`, `GelitaHelperTest`, `SystemResetTest`: bentuk kode untuk mode penelitian; `GLT-…` tetap di luar mode.
- `SimulationTest` dan `Simulator`: bila `field_mode` menyala, simulator membuat sesi lapangan `sim` sendiri.
- `LibraryLockTest`, `RegionEntryTest`, `DialogueGateTest`: kemajuan dari `participant_progress`.
- Semua tes dan pendukung tes yang membaca `session_progress`, termasuk `TryLimitTest`, `tests/_support/Database/RawDataWorld.php`, dan `ResetTables.php`.
- `RouteWiringTest`, `ScreenViewFilterTest`, `LanguageFilesTest` untuk rute dan kunci baru.
- `GradeFiveWorld` menetapkan `field_mode = 0` secara tegas, dan satu tes memastikan studi baru dan `gelita:reset` mulai dengan `field_mode = 0`.

### 19.3 Cek manual

Di mesin pengembangan, dengan mode penelitian menyala:
1. Buat sesi `S01-A-P1`, daftarkan tiga siswa dengan token dari dua tab, isi kursi, dan lihat keseimbangan.
2. Tutup sesinya, lalu buat `S01-A-P2`. Pastikan satu siswa melihat Temanggung saja dan siswa lain di `/sampai-jumpa` setelah sesi ditutup.
3. Buat susulan untuk siswa ketiga.
4. Buka pemantauan sebagai peneliti dan sebagai admin.

### 19.4 Selesai bila

- `vendor/bin/phpunit --no-coverage` hijau, termasuk tes di 19.1.
- Cek manual 19.3 lolos.
- Tidak ada pembaca `session_progress` yang tersisa (`grep -rn session_progress app/ tests/` hanya menemukan migrasi lama).
- Dokumen di [§20](#20-dokumen-yang-ikut-diperbarui-saat-implementasi) diperbarui di cabang yang sama.

---

## 20. Dokumen yang ikut diperbarui saat implementasi

- `docs/07_FEATURE_INTEGRATION.md`: fitur masuk, daftar, peta, buka wilayah, panel staf, `gelita:reset`, `gelita:simulasi`; fitur baru sesi lapangan, pemantauan, `gelita:uji:akun`.
- `docs/01_DATABASE.md`, `docs/03_MODEL_ENTITY.md`, `docs/04_CONTROLLER_ROUTE.md`, `docs/05_VIEW_UI.md`, `docs/08_DEPLOYMENT.md` (instalasi uji), `README.md`.
- `.claude/rules/glossary.md`: baris baru untuk sesi lapangan (`field session`), token sesi (`session token`), kode sekolah penelitian (`research school code`), rombel (`class group`), buklet (`booklet`), pemantauan (`monitoring panel`), mode penelitian (`field mode`).
- Aturan kerja repo, daftar perintah spark: `gelita:uji:akun`.
- `docs/PROGRESS.md` dan catatan keputusan implementasi.

---

## 21. Pertanyaan terbuka

Tidak menahan draf ini. Dicek ulang 1 November 2026: no. 1 dan 6 selesai, no. 2 dan 3 pindah ke bagian 8 (`34`), no. 4 dipegang ketua peneliti.

1. ~~**Teks token.**~~ **Sudah diselaraskan (putaran kedua, 30 Oktober 2026):** "enam huruf dan angka" dipakai di mock-up `mk-tk-01`/`mk-tk-02` dan instrumen ahli media v1.1 ([04](04_CATATAN_KEPUTUSAN.md)). Kalimat akhirnya tetap mengikuti telaah ahli media dan uji keterbacaan, seperti semua teks di [§16](#16-layar-dan-teks); tidak perlu keputusan lagi di sini.
2. ~~**Batas 20 menit tantangan tertinggal di P5.**~~ **Diputuskan pengembang di bagian 8, 2 November 2026** ([34](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md), keputusan pengembang no. 1, K1): P1 menjaga 20 menit dengan jam, lalu menekan *Mulai tes akhir*; tantangan di sesi lapangan itu tertutup dan semua siswa diarahkan ke modul, pengerjaan yang terbuka dibiarkan (K14).
3. ~~**Angket yang terlambat.**~~ **Diatur di bagian 8** ([34 → Angket terlambat](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#102-angket-terlambat)): angket yang terlambat boleh diisi di sesi susulan P1 sampai P5 utama dibuka, dengan `questionnaire_late` bila siswa sudah bermain. Batas "sebelum P5 utama dibuka" dikonfirmasi peneliti pada 3 November 2026 (34 → *Pertanyaan terbuka* §17 no. 4).
4. **Daftar kode sekolah** (daftar kerja no. 12) belum ada. Disusun ketua peneliti dari sekolah yang direkrut, dan admin memasukkan kodenya ke `schools.research_code` ([04](04_CATATAN_KEPUTUSAN.md), 31 Oktober 2026). Sistem tidak bergantung pada isinya, tetapi sekolah tidak bisa dipakai untuk sesi lapangan sebelum kodenya diisi.
5. ~~**`GameSessionModel::markStale()`**~~ **Diputuskan di bagian 3:** `markStale()` menulis `session_paused` dengan `via: idle`, satu per sesi yang benar-benar diubahnya ([29 → Sesi yang diam](29_IMPLEMENTASI_DATA_PENELITIAN.md#57-sesi-yang-diam-markstale), K14). Dalam mode penelitian, penutupan sesi lapangan tetap memakai §6.6 (`via: field_session_closed`).
6. ~~**Satu siswa, dua perangkat bersamaan** dalam satu sesi lapangan.~~ **Diputuskan pengembang 1 November 2026** ([§22](#22-keputusan-pengembang) no. 10): tetap seperti sekarang. Sesi game yang sama dilanjutkan di perangkat kedua, dan perpindahannya dicatat di L2 sebagai pindah perangkat. Tidak ada kode baru.
7. ~~**Angka "izin belum diisi" untuk admin.**~~ **Diputuskan pengembang, dikonfirmasi 31 Oktober 2026:** angka itu hanya untuk peneliti dan ketua. Admin tidak memegang halaman izin dan tidak bisa menindaklanjutinya; isian izin per siswa juga tidak terlihat admin ([30 → Izin berkode per siswa](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#52-izin-berkode-per-siswa)). Ini menggantikan catatan PR #288 (admin juga melihat angka itu).
8. ~~**Kalimat "lupa kata sandi" setelah studi ditutup.**~~ **Diputuskan pengembang, dikonfirmasi 31 Oktober 2026:** dua kalimat menurut mode. Selama mode penelitian: "Lupa kata sandi? Minta kakak peneliti mengatur ulang, ya." Di luar itu, termasuk setelah studi ditutup: "Lupa kata sandi? Bilang ke gurumu, ya. Gurumu bisa meminta admin GELITA mengatur ulang." Teksnya di [27 → Teks antarmuka](27_IMPLEMENTASI_PERAN_PENELITI.md#12-teks-antarmuka) (`Game.forgotPasswordAdmin`). Ini menggantikan kalimat PR #288 ("Minta tolong admin GELITA mengatur ulang, ya."), karena anak lebih mudah menghubungi guru daripada admin. Kalimat akhir tetap mengikuti uji keterbacaan.

---

## 22. Keputusan pengembang

Satu putaran tanya, empat pertanyaan (30 Oktober 2026). Pengembang memilih usulan di keempatnya. `asal: pengembang`.

1. **Buklet dan bentuk tes dihitung panel dari baris dan kursi; ketidakseimbangan hanya diberi peringatan** (K10). Ini mengubah keputusan 25 Oktober 2026 "buklet dimasukkan P1 dari L2": P1 kini memasukkan baris dan kursi, bukan buklet. Tidak dipilih:
   - buklet diketik P1 dan dicocokkan dengan aturan;
   - dihitung dengan ketidakseimbangan yang menahan tes awal.
2. **`gelita:uji:akun` di luar alur token** (K15): akun dibuat langsung dengan kode `UJI-…` dan sekolah `V01`; `--reset` menghapus data permainan; di G3 satu sesi lapangan panjang untuk validator. Tidak dipilih: lewat alur token dengan kode seperti `V01-A01` dan reset yang hanya menutup sesi.
3. **Admin melihat panel pemantauan sebagai angka saja** (K2). Tidak dipilih: sama dengan peneliti; tidak ada akses.
4. **Token susulan hanya untuk siswa yang dicentang P1** (K12), dengan centang awal untuk yang tidak hadir. Tidak dipilih: semua siswa rombel itu boleh.

**Tambahan, 30 Oktober 2026** (pesan terpisah setelah sesi koordinator, `asal: pengembang`):

5. ~~Admin melihat angka "izin belum diisi" di pemantauan~~ **Diganti 31 Oktober 2026: angka hanya untuk peneliti dan ketua** ([§21](#21-pertanyaan-terbuka) no. 7). Pengembang memilih jawaban putaran kedua (pertanyaan dengan pilihan, 30 Oktober 2026) dan mengonfirmasinya 31 Oktober 2026 ([04](04_CATATAN_KEPUTUSAN.md)). Tidak dipilih: admin juga melihat angkanya.
6. **Kalimat lupa kata sandi di luar mode penelitian** ([§21](#21-pertanyaan-terbuka) no. 8). Mula-mula pengembang menulis "hubungi admin atau parafrase yang lebih sesuai". Kalimat yang dipilih di putaran kedua dan dikonfirmasi 31 Oktober 2026 mengarah ke guru, yang bisa meminta admin GELITA: "Lupa kata sandi? Bilang ke gurumu, ya. Gurumu bisa meminta admin GELITA mengatur ulang." Tidak dipilih: satu kalimat untuk kedua mode; "Minta tolong admin GELITA mengatur ulang, ya."

**Cek kemutakhiran dan tinjauan pengembang, 1 November 2026** (cabang `r10-cek-28`, `asal: pengembang`, pemeriksaan berbantuan AI). Putaran pertama, empat pertanyaan; pengembang memilih usulan di tiga, dan pilihan lain di no. 7.

7. **`gelita:uji:akun` tetap dibangun di gelombang kode D** bersama bagian ini ([§13](#13-alat-bantu-gelitaujiakun-perubahan-no-24)). Instalasi uji tahap 2 memakai cara manual di 20; alat ini pertama dipakai di G3. Tidak dipilih: dibangun lebih awal sebagai cabang tersendiri di samping gelombang C, sebelum versi dibekukan, lalu dilengkapi di gelombang D (usulan AI).
8. **Rencana pertemuan per studi** (K19): `lima_pertemuan` untuk KG3a, KG3b, dan KG5; `satu_sesi` untuk KG2 (daftar lalu satu wilayah yang dipilih saat sesi dibuat). Tidak dipilih: KG2 dengan `field_mode` mati (pendaftaran biasa, kode `GLT-…`); KG2 di instalasi uji terpisah.
9. **Token menolak akun yang sudah punya sesi game di studi lain** (K20, `other_study`). Tidak dipilih: hanya prosedur lapangan.
10. **Satu siswa di dua perangkat tetap melanjutkan sesi game yang sama** ([§21](#21-pertanyaan-terbuka) no. 6). Tidak dipilih: perangkat terbaru menutup sesi PHP perangkat lama.

Putaran kedua hari yang sama, dua pertanyaan; pengembang memilih usulan di keduanya:

11. **Migrasi `003500` mengisi `participant_progress` dari pengerjaan tuntas sebelum membuang `session_progress`** ([§8](#8-kemajuan-mengikuti-siswa-bukan-sesi-game)), jadi bukan migrasi yang menghapus data dan tidak menunggu keputusan produksi seperti `003400` dan `003600`. Tidak dipilih: tanpa pengisian, diperlakukan seperti `003400` dan `003600`.
12. **28 disetujui pengembang** (1 November 2026). Gelombang kode D boleh membangun dari dokumen ini.

Yang diselaraskan tanpa keputusan baru: aturan "dibangun sekarang" menggantikan "tahap 3, setelah G2" (status, §13); `field_mode` mati secara bawaan dan satu studi per uji (K1, §5.1); satu urutan `U` untuk semua uji dan penghitung kode yang tidak per studi (§10); rujukan kode (`GameSessionModel::resumable()`, `ParticipantAuthFilter`, `keepForLater()` juga membuang antrean pada 422, pemeriksaan urutan tantangan di `ChallengeController`, tes lain yang membaca `session_progress`); §21 no. 1–4. Urutan migrasi `003400` → `003500` → `003600`, `003700` → `003800` di [27 §6.1](27_IMPLEMENTASI_PERAN_PENELITI.md#61-migrasi-2026-01-01-003400_replaceteacherwithresearcherroles) tetap cocok; migrasi terakhir di kode masih `003300` dan `REQUIRED_MIGRATION` masih `2026-01-01-003300`.

**Sesi koordinator 31–35, 3 November 2026** (cabang `r10-cek-30`, `asal: pengembang`, `disetujui pengembang`):

13. **Angka "siswa belum punya kursi" (`BOOKLET_MISSING`) dan "perangkat layar kecil ditolak" tampil untuk admin, peneliti, dan ketua** ([§12](#12-panel-pemantauan)), karena keduanya urusan perangkat dan tempat duduk yang juga ditangani admin. Angka "izin belum diisi" tetap hanya untuk peneliti dan ketua (no. 5). Tidak dipilih: hanya peneliti dan ketua.
14. **Layar kecil juga ditolak saat mendaftar dengan token** ([§6.5](#65-pendaftaran-dengan-token-p1) langkah 0): `/daftar` dalam mode penelitian mengirim `stage_w` dan menolak panggung di bawah `min_stage_px` dengan pesan dan audit `small_screen_refused` yang sama dengan `/token`. Aturannya di [33 §7.2](33_IMPLEMENTASI_MODE_PENELITIAN.md#72-layar-kecil). Tidak dipilih: hanya di `/token`.

**Akibat dari kamus yang sudah disetujui** (bukan pertanyaan baru). Karena satu sesi game milik tepat satu sesi lapangan (11), kemajuan pindah dari sesi game ke siswa ([§8](#8-kemajuan-mengikuti-siswa-bukan-sesi-game)). Ini perubahan terbesar di bagian ini dan disebut di [04](04_CATATAN_KEPUTUSAN.md) untuk ditinjau pengembang.

---

## 23. Akibat ke dokumen lain

Dicatat di sini dan dibawa sesi koordinator setelah bagian 3 dan 4 selesai.

**Status per 30 Oktober 2026:** semua baris sudah dibawa sesi koordinator (PR #285, #286, #287), kecuali yang menunggu `kamus-1.1` atau implementasi.

| Dokumen | Bagian | Perubahan | Status |
| --- | --- | --- | --- |
| [01](01_REVISI_10_ANALITIK.md) | *Token sesi* langkah 2; no. 11, 12 | Token huruf dan angka (abjad K5); `region_opened` diturunkan dari rencana pertemuan; buklet dihitung panel dari baris dan kursi | Sudah (sesi koordinator, 30 Oktober 2026, PR #285) |
| [02](02_LEMBAR_LAPANGAN.md) | L2 | Kolom Buklet dan Bentuk tes awal disalin dari panel setelah baris dan kursi dimasukkan; P1 memasukkan kode, baris, kursi | Sudah (sesi koordinator, 30 Oktober 2026, PR #285) |
| [02](02_LEMBAR_LAPANGAN.md) | L1 | Kode sesi susulan kedua `-S2`; riwayat token dari panel | Sudah (sesi koordinator, 30 Oktober 2026, PR #285) |
| [03](03_KODE_DAN_ARTI.md) | Kode sesi, sekolah, akun uji | `-S2`, awalan `V` untuk instalasi uji, kode `UJI-01` | Sudah (sesi koordinator, 30 Oktober 2026, PR #285) |
| [04](04_CATATAN_KEPUTUSAN.md) | Entri 25 Oktober 2026 (buklet) | Tidak diubah (riwayat); keputusan baru di entri 30 Oktober | Tidak perlu diubah |
| [11](11_KAMUS_DATA.md) | Variabel peserta dan sesi | `is_makeup`, `makeup_no`, bentuk tes; masuk `kamus-1.1` | **Sudah**: `kamus-1.1` draf (3 November 2026) |
| [18](18_MOCKUP_LAYAR_BARU.md) | Teks layar token | "enam huruf dan angka"; pesan `tokenNotOpenYet`, `tokenOtherClass`, `tokenNotMember` | Sudah (sesi koordinator, 30 Oktober 2026, PR #285) |
| [20](20_PAKET_VALIDASI.md) | Prasyarat no. 8 | Di G3: mode penelitian menyala dan satu sesi lapangan `V01` untuk validator; akun dari `gelita:uji:akun` | Sudah (sesi koordinator, 30 Oktober 2026, PR #285) |
| [27](27_IMPLEMENTASI_PERAN_PENELITI.md) | §4, §16 no. 3 | Admin melihat pemantauan sebagai angka saja | Sudah (cabang ini) |
| [20](20_PAKET_VALIDASI.md) | Prasyarat no. 8: *Usulan alat bantu untuk tahap 3* dan *Di G3 (… setelah tahap 3)* | "Tahap 3" menjadi aturan 1 November 2026: `gelita:uji:akun` dibangun di gelombang kode D; instalasi uji tahap 2 tetap memakai langkah 3 dan 6; `--reset` menghapus data permainan (K15), bukan menutup sesi | Sudah (sesi tindak lanjut dokumen, 3 November 2026) |
| [03](03_KODE_DAN_ARTI.md) | Kode sesi; *Kegiatan dengan anak* | Kode sesi KG2 `U01-A-P1` (rencana `satu_sesi`); satu akun hanya di satu studi (K20) | Sudah (sesi tindak lanjut dokumen, 3 November 2026) |
| [18](18_MOCKUP_LAYAR_BARU.md) | Teks layar token | Pesan baru `tokenOtherStudy` | Sudah (sesi tindak lanjut dokumen, 3 November 2026) |
| `33` (bagian 7) | Mode penelitian | Memakai `field_mode` dan `meeting_plan` per studi dari sini; rencana `satu_sesi` tidak memakai Kenali dan pengerjaan ulang | Untuk sesi gelombang B (1 November 2026) |
| `34` (bagian 8) | Modul tes dan skala sikap | Memutuskan 28 §21 no. 2 (batas 20 menit P5) dan no. 3 (angket terlambat); rencana `satu_sesi` (KG2) tanpa modul tes, angket, dan skala sikap | Sudah (34 K1, §5.1, §10.2, K12; dibawa ke 28 oleh sesi koordinator bagian 8, 2 November 2026) |
| [11](11_KAMUS_DATA.md) | Variabel sesi | `meeting_plan` studi dan `region_code` sesi lapangan KG2 menurunkan `region_opened`; masuk `kamus-1.1` | **Sudah**: `kamus-1.1` draf (3 November 2026) |
