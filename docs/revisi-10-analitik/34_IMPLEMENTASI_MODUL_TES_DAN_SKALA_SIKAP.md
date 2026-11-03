# Dokumen Implementasi Bagian 8 — Modul Tes, Angket, dan Skala Sikap

Per 2 November 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · perubahan sistem no. 21 dan 22 di [01 → Catatan perubahan sistem](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti)

> **Status: `disetujui pengembang`, `asal: pengembang`** (disusun dengan bantuan AI, 1–2 November 2026; disetujui pengembang 2 November 2026, [§18](#18-keputusan-pengembang) no. 5). Empat pilihan rancangan diputuskan pengembang dalam satu putaran wawancara ([§18](#18-keputusan-pengembang)). Isi instrumen tetap `needs_verification`; persetujuan ini untuk rancangan sistem, bukan untuk butir.
>
> Sejak 1 November 2026 kode dibangun sekarang ([04](04_CATATAN_KEPUTUSAN.md), entri 1 November 2026), di gelombang kode F, setelah bagian 2 (token, [28](28_IMPLEMENTASI_SESI_LAPANGAN.md)) dan bagian 4 (izin, [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md)). Modul ini tidak dipakai dengan anak sebelum persetujuan etik terbit. Semua perilakunya ada di balik sakelar `field_mode` yang mati secara bawaan, jadi `main` tetap bisa dirilis.
>
> **Isi instrumen berstatus `needs_verification`.** Butir tes ([14](14_TES_BAGIAN_MEMBACA.md), [15](15_TES_BAGIAN_DIGITAL.md)), angket ([16](16_ANGKET_AKSES_DIGITAL.md)), dan skala sikap ([17](17_SKALA_SIKAP.md)) masih menunggu validasi ahli dan uji keterbacaan. Karena itu isinya disimpan di berkas data, bukan di kode: revisi dari validasi cukup berupa suntingan berkas data lalu impor ulang ([§6](#6-isi-instrumen-di-berkas-data)).

---

## Daftar isi

0. [Cara memakai dokumen ini](#0-cara-memakai-dokumen-ini)
1. [Kebutuhan, sumber, dan tempatnya](#1-kebutuhan-sumber-dan-tempatnya)
2. [Keputusan yang dikunci](#2-keputusan-yang-dikunci)
3. [Yang tidak berubah](#3-yang-tidak-berubah)
4. [Satu siswa dari P1 sampai P5](#4-satu-siswa-dari-p1-sampai-p5)
5. [Kapan modul terbuka](#5-kapan-modul-terbuka)
6. [Isi instrumen di berkas data](#6-isi-instrumen-di-berkas-data)
7. [Modul tes dan angket](#7-modul-tes-dan-angket)
8. [Butir risiko daring, T11, dan T12](#8-butir-risiko-daring-t11-dan-t12)
9. [Skala sikap di Balai Refleksi](#9-skala-sikap-di-balai-refleksi)
10. [Susulan](#10-susulan)
11. [Skema database](#11-skema-database)
12. [Rute](#12-rute)
13. [Teks antarmuka](#13-teks-antarmuka)
14. [Titik sambung ke perubahan lain](#14-titik-sambung-ke-perubahan-lain)
15. [Pengujian dan kriteria selesai](#15-pengujian-dan-kriteria-selesai)
16. [Dokumen yang ikut diperbarui saat implementasi](#16-dokumen-yang-ikut-diperbarui-saat-implementasi)
17. [Pertanyaan terbuka](#17-pertanyaan-terbuka)
18. [Keputusan pengembang](#18-keputusan-pengembang)
19. [Akibat ke dokumen lain](#19-akibat-ke-dokumen-lain)

---

## 0. Cara memakai dokumen ini

**Urutan.** Kode bagian ini dikerjakan di gelombang F, bersama bagian 7 ([33](33_IMPLEMENTASI_MODE_PENELITIAN.md), nama usulan) dan sebelum bagian 9 ([00 → Tahap dokumen 3](00_INDEKS.md)). Bagian ini memakai:
- peta kemampuan dan filter `staffCan` dari bagian 1 ([27 → Peta kemampuan](27_IMPLEMENTASI_PERAN_PENELITI.md#51-peta-kemampuan));
- sakelar `field_mode`, rencana pertemuan, sesi lapangan, token, susulan, dan `participant_seats` dari bagian 2 ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md) §5, §6, §11);
- lembar unduhan dan riwayat unduhan dari bagian 3 ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md) §7);
- `ConsentService::riskItemsAllowed()`, aturan T11 dan T12, cakupan hapus `risk_items`, dan `RiskFollowUpService` dari bagian 4 ([30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md) §5.4, §6, §7.3).

**Batas.** Dokumen ini mengatur:
- modul tes awal–akhir dan angket akses digital (no. 22): tampilan, kapan terbuka, alur bagian, batas waktu, penyimpanan jawaban, penskoran mentah, dan susulan;
- layar T11 dan T12 serta penyimpanan jawaban butir risiko daring (aturannya dari 30 §5.4);
- skala sikap di Balai Refleksi dan perubahan kritik dan saran selama penelitian (no. 21);
- berkas data instrumen dan perintah impornya.

Yang **tidak** diatur di sini, dan hanya disebut sebagai titik sambung ([§14](#14-titik-sambung-ke-perubahan-lain)):
- variabel turunan: skor Rasch, `rapid_guess`, `test_status`, `questionnaire_status`, indeks angket, `q_flags`, `sikap_score`, `sikap_flag_straight`, `sikap_missing_reason` (bagian 9, [35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md), nama usulan);
- jam yang disembunyikan dan ubin *Total waktu* di Balai Refleksi (no. 16, bagian 7);
- kolom bintang per wilayah yang terlalu sempit untuk 15 tantangan ([18 → Celah](18_MOCKUP_LAYAR_BARU.md#celah-yang-ditemukan-di-dokumen-lain) no. 8; no. 2, bagian 6).

**Saat implementasi:**
- Satu cabang pendek dari `main`, commit berbahasa Inggris.
- `vendor/bin/phpunit --no-coverage` hijau sebelum push.
- Jangan menjalankan migrasi, `gelita:reset`, atau skrip rilis di server produksi.
- Setiap tulisan yang bisa bersaing (menyimpan jawaban, mengirim bagian, menutup bagian karena batas waktu, mengunci kursi, mulai tes) memakai UPDATE bersyarat dengan pola `ChallengeService::updateIf()` dan `updateOrStale()` (`app/Services/ChallengeService.php`, baris 1443–1462 per 2 November 2026), bukan `SELECT … FOR UPDATE`.

**Prinsip yang tetap berlaku:** server satu-satunya sumber kebenaran; kunci, skor, dan kode kesalahan tidak pernah dikirim ke browser; CSRF di setiap formulir; tanpa CDN; tindakan staf menulis log audit.

**Rujukan kode.** Ditulis per berkas dan metode, dengan nomor baris per 2 November 2026. Nomor baris bisa bergeser, jadi cari nama metodenya. Nomor migrasi `0042xx` sementara dan bisa diberi nomor ulang menurut urutan digabung.

---

## 1. Kebutuhan, sumber, dan tempatnya

| # | Kebutuhan | Sumber | Di sini |
| ---: | --- | --- | --- |
| 1 | Tampilan Bootstrap tanpa unsur game; Bootstrap lokal berversi di `public/assets/vendor/`, tanpa CDN | 01 no. 22; [13 → Modul tes di GELITA](13_TES_AWAL_AKHIR.md#modul-tes-di-gelita); [18](18_MOCKUP_LAYAR_BARU.md) `mk-mt-01`–`06` | §7.1 |
| 2 | Dibuka token sesi P1 dan P5, bukan wilayah game; di luar sesi tertutup | 01 no. 22; 13; [28 → Token](28_IMPLEMENTASI_SESI_LAPANGAN.md#63-token), §5.2 kunci `test` | §5 |
| 3 | Bentuk A/B dari panel (baris dan kursi L2); tes awal menunggu semua siswa punya bentuk; kursi terkunci saat tes awal siswa itu dimulai | 01 no. 22; [13 → Pembagian bentuk](13_TES_AWAL_AKHIR.md#pembagian-bentuk-denah-papan-catur); [28 → Isian L2](28_IMPLEMENTASI_SESI_LAPANGAN.md#11-isian-l2-kursi-buklet-dan-bentuk-tes) | §5.3, §7.2 |
| 4 | Satu bagian dalam satu waktu; boleh kembali sebelum dikirim; daftar nomor soal dengan tanda belum dijawab; urutan pilihan tetap | 13; 14 dan 15 → *Cara membaca* | §7.3 |
| 5 | Batas waktu per bagian tersembunyi, sebagai pengaturan studi; pengingat 5 menit sebelum batas; bagian ditutup otomatis | 01 no. 22; 13 → *Kisi-kisi tes*, *Modul tes di GELITA* | §7.4 |
| 6 | Jawaban per butir di server; kunci dan skor tidak ke browser; lanjut di perangkat lain dari soal terakhir | 01 no. 22; 13 | §7.5 |
| 7 | Dicatat: jawaban mentah, waktu tampil per butir, penggantian jawaban, jam mulai dan selesai bagian, bagian yang ditutup batas waktu, butir tidak sampai | 13 → *Penskoran dan pemasukan data* | §7.5, §7.6 |
| 8 | Skor dihitung server dari kunci: PG/PGK/menjodohkan/D3 0–1, D2 0–1–2; kode kesalahan dari jawaban mentah | 13; 14; 15 | §7.6 |
| 9 | Angket langsung setelah tes awal, satu bagian A–H per layar, tanpa batas waktu, kode `-`, `8`, `9` | 01 no. 22; [16 → Cara pelaksanaan](16_ANGKET_AKSES_DIGITAL.md#cara-pelaksanaan), *Pemasukan data dan variabel turunan* | §7.7 |
| 10 | Butir risiko daring setelah tes akhir, hanya bila izin mencakupnya; T11 lalu T12 untuk semua siswa di titik yang sama | 01 no. 22; [30 → Layar T11 dan T12](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#54-layar-t11-dan-t12); 16 → *Keputusan pengembang* | §8 |
| 11 | Jawaban butir risiko hanya muncul sebagai angka gabungan di unduhan | [29 → Butir risiko daring](29_IMPLEMENTASI_DATA_PENELITIAN.md#76-butir-risiko-daring) | §8.3 |
| 12 | Susulan tes dalam tiga hari dibuka peneliti; angket boleh terlambat sampai P5 (`questionnaire_late`) | 13 → *Aturan selama tes*; 16 → *Cara pelaksanaan* no. 9 | §10 |
| 13 | Batas 20 menit tantangan tertinggal di P5 | [28 → Pertanyaan terbuka](28_IMPLEMENTASI_SESI_LAPANGAN.md#21-pertanyaan-terbuka) no. 2 | §5.4; keputusan no. 1 |
| 14 | Skala sikap di Balai Refleksi setelah cerita penutup, sebelum kritik dan saran: petunjuk, contoh `skp-ct-01`, satu pernyataan per layar, empat pilihan tanpa titik tengah, *Lewati* dan *Kembali*, bilah kemajuan, urutan tetap, sekali kirim, tidak dibacakan | 01 no. 21; [17 → Format jawaban dan tampilan](17_SKALA_SIKAP.md#format-jawaban-dan-tampilan); 18 `mk-ss-01`–`05` | §9 |
| 15 | Pujian Mbah Kedu baru tampil setelah skala; kritik dan saran tetap ada, semua isian boleh kosong; bintang 1–5 menjadi `feedback_rating` | 01 no. 21; [17 → Pelaksanaan di P5](17_SKALA_SIKAP.md#pelaksanaan-di-p5) | §9.4 |
| 16 | Susulan skala paling lama 7 hari setelah P5 (`sikap_makeup`); siswa yang belum tuntas tidak mendapat jalur khusus | 01 no. 21; 17; [28 → Sesi susulan](28_IMPLEMENTASI_SESI_LAPANGAN.md#67-sesi-susulan-dan-anggotanya) no. 6 | §10.3 |
| 17 | Isi instrumen di berkas data, bukan di kode | Tugas bagian 8 (04, 1 November 2026) | §6 |
| 18 | Variabel untuk `kamus-1.1` | 13, 16, 17 → variabel; [11](11_KAMUS_DATA.md) | §11.4, §14 |

---

## 2. Keputusan yang dikunci

K1–K4 diputuskan pengembang ([§18](#18-keputusan-pengembang)). Sisanya menurunkan 01, 13–18, dan bagian 1–4, atau berasal dari pencarian kode; yang terakhir ditandai *kode* dan dicatat di [04](04_CATATAN_KEPUTUSAN.md) untuk ditinjau.

| Kode | Keputusan |
| --- | --- |
| K1 | **Tes akhir dimulai lewat tombol P1, yang sekaligus menutup tantangan.** P1 menjaga 20 menit tantangan tertinggal dengan jam, lalu menekan *Mulai tes akhir* di halaman sesi lapangan. Sejak itu tantangan di sesi lapangan itu tertutup dan setiap siswa diarahkan ke modul. Pengerjaan yang sedang terbuka dibiarkan apa adanya (28 K14). Tes awal di P1 memakai tombol yang sama (*Mulai tes awal*). Menjawab 28 §21 no. 2. |
| K2 | **Isi instrumen di berkas PHP** `data/instrumen/{tes,angket,sikap}.php`, seperti `data/story.php`, diimpor ke tabel berversi oleh `gelita:instrumen:impor` ([§6](#6-isi-instrumen-di-berkas-data)). |
| K3 | **Perangkat bermasalah: P1 menambah menit.** Batas waktu dihitung server sejak blok dimulai. Siswa melanjutkan di perangkat cadangan dari soal terakhir, dan P1 boleh menambah menit untuk siswa itu dengan alasan; tambahan menit tersimpan, masuk log audit, dan masuk unduhan. |
| K4 | **Modul tes dan skala sikap mengikuti `field_mode`.** Saat `field_mode` mati, `/tes` tertutup dan Balai Refleksi tetap seperti sekarang (bintang wajib, "minimal dua"). Instalasi uji validator melihat layarnya lewat sakelar pratinjau terpisah untuk akun `uji.*` ([§5.5](#55-pratinjau-untuk-instalasi-uji-validator)). |
| K5 | **Satu mesin untuk empat instrumen.** Tes, angket, butir risiko, dan skala sikap memakai tabel dan `InstrumentService` yang sama. Yang berbeda hanya tata letak (Bootstrap untuk tiga yang pertama, layar game untuk skala sikap) dan aturan per jenis (batas waktu, skor). |
| K6 | **Halaman dirender server, satu butir per permintaan.** Setiap pindah soal adalah formulir POST yang menyimpan jawaban lalu mengarahkan ke soal tujuan, jadi modul bekerja tanpa JavaScript dan waktu tampil diukur dari jam server. JavaScript hanya menampilkan pengingat 5 menit dan memuat ulang halaman saat batas tercapai; server tetap yang menegakkan batas. |
| K7 | **Satuan jawaban adalah satu kolom data.** Butir tes punya satu satuan (kode butirnya). Butir angket bertabel punya satu satuan per variabel (ang-05: `screen_time_school`, `screen_time_holiday`), sehingga 41 kolom angket di 16 terbaca langsung dari tabel jawaban. |
| K8 | **Skor dan kode kesalahan dihitung saat bagian ditutup** (dikirim atau batas waktu), dari kunci di database, dan disimpan per satuan. Skor mentah per bagian ikut disimpan. Variabel yang butuh aturan kamus (`rapid_guess`, status, skor Rasch, indeks) dihitung bagian 9. |
| K9 | **Bagian dan blok waktu.** Tes punya tiga bagian (`mb`, `ae`, `bd`) dan dua blok waktu (`p1` = Bagian 1, `p23` = Bagian 2 dan 3), mengikuti batas di 13 dan kolom `pre_capped` (`p1`, `p23`). Setiap bagian dikirim sendiri; batas waktu menutup blok. |
| K10 | **Mulai tes awal menunggu bentuk.** Tombol *Mulai tes awal* aktif bila panel menampilkan "Semua siswa sudah punya bentuk tes" (28 §11). Siswa tanpa bentuk tetap di layar pembuka dengan pesan menunggu. Kursi siswa dikunci (`participant_seats.locked_at`) saat sesi tes awal siswa itu dibuat, dengan UPDATE bersyarat `locked_at IS NULL`. |
| K11 | **Tes awal tidak boleh sesudah bermain.** Bila siswa sudah punya pengerjaan tantangan dalam studi itu, modul menolak tes awal dan statusnya kosong (`hilang` dihitung bagian 9). Angket tetap boleh, dengan `late = 1` (13, 16). |
| K12 | **Balai Refleksi dalam mode penelitian hanya terbuka di sesi lapangan P5 dan susulannya**, setelah semua tantangan tuntas (aturan sekarang) dan setelah cerita penutup. Di P2–P4 siswa yang sudah tuntas melihat "Balai Refleksi dibuka di pertemuan terakhir." Diturunkan dari 17 (skala setelah cerita penutup) dan rencana pertemuan 28 §5.2 (`ending` hanya di P5). Rencana `satu_sesi` (KG2) tidak punya P5, jadi skala sikap tidak pernah tampil di sana (28 K19). |
| K13 | **Isi instrumen hanya berbahasa Indonesia.** Teks bingkai modul (tombol, petunjuk umum, pesan) punya pasangan en di berkas bahasa. Butir, bacaan, dan pilihan tidak diterjemahkan, karena instrumen divalidasi dalam bahasa Indonesia; bila bahasa game siswa en, modul tetap menampilkan isinya dalam bahasa Indonesia. *Kode.* |
| K14 | **Tidak ada event game dari modul.** Jawaban dan waktu modul disimpan di tabelnya sendiri, bukan di `game_event_logs`, supaya tidak ada salinan kedua yang harus dibersihkan. Modul tidak menulis `screen_viewed`. Tindakan staf (mulai tes, tambah menit, pengaturan) masuk log audit. |
| K15 | **Tidak ada kemampuan baru.** Mulai tes dan tambah menit memakai `field_sessions.manage`; pengaturan batas waktu memakai `studies.manage`; jawaban terlihat lewat `research.browse` dan `research.export` (`ketua`), kecuali jawaban butir risiko, yang tidak pernah per siswa (29 §7.6). |

---

## 3. Yang tidak berubah

- Game, tantangan, bintang, dan kemajuan tidak berubah oleh bagian ini. Modul tidak memengaruhi skor game.
- Balai Refleksi di luar mode penelitian tetap seperti sekarang: `ReflectionController::store()` (`app/Controllers/Game/ReflectionController.php`, baris 113–174) dengan bintang wajib dan aturan "minimal dua" (baris 147–151, `Game.reflectionNeedTwo`).
- Tabel `participant_feedback` tetap; hanya `rating` boleh kosong ([§11.2](#112-tabel-yang-diubah)). Lembar unduhan `masukan` tetap (29 §7.2).
- Aturan izin, T11, T12, penanda tindak lanjut, dan permintaan hapus tetap milik bagian 4; di sini hanya layar dan penyimpanannya.
- Token, sesi lapangan, susulan, dan kursi tetap milik bagian 2; di sini hanya kolom `test_started_at` dan tombol mulai tes yang ditambahkan.
- Isi butir di 13–17 tidak diubah oleh dokumen ini.

---

## 4. Satu siswa dari P1 sampai P5

| Langkah | P1 | P5 |
| --- | --- | --- |
| 1 | Daftar dengan token P1 (28 §6.5), kartu "Kode penelitian" | Masuk dan mengetik token P5 (28 §6.4) |
| 2 | Layar pembuka modul: petunjuk 13 dan kode penelitian; menunggu | Tantangan tertinggal paling lama 20 menit, dijaga P1 dengan jam |
| 3 | P1 memasukkan baris dan kursi (28 §11), lalu menekan *Mulai tes awal* | P1 menekan *Mulai tes akhir*; tantangan tertutup (K1) |
| 4 | Lima contoh soal bersama koordinator (`tes-ct-01`–`05`), tidak diskor | Layar pembuka dan contoh soal yang sama |
| 5 | Blok `p1`: Bagian 1 Membaca, dikirim | Blok `p1` |
| 6 | Layar peregangan, lalu blok `p23`: Bagian 2, dikirim; Bagian 3, dikirim | Blok `p23` |
| 7 | Angket A–H, *Selesai* | T11 (hanya bila izin butir risiko Ya dan status aktif) → ang-21–ang-23 → T12; siswa lain langsung T12 |
| 8 | Layar selesai, tombol ke cerita pembuka (`/intro`) | Layar selesai, tombol ke cerita penutup (`/penutup`) |
| 9 | — | Balai Refleksi: skala sikap → kirim → pujian Mbah Kedu, ringkasan perjalanan, kritik dan saran |

Contoh soal tampil sebelum Bagian 1 (`tes-ct-01`–`03`), sebelum Bagian 2 (`tes-ct-04`), dan sebelum Bagian 3 (`tes-ct-05`), sesuai 14 dan 15. Jawabannya disimpan tapi tidak diskor dan tidak dihitung dalam waktu blok: blok dimulai saat siswa menekan *Mulai Bagian 1* (atau *Mulai Bagian 2*) setelah contoh.

---

## 5. Kapan modul terbuka

### 5.1 Syarat

`InstrumentGate::status(Participant $p, ?GameSession $s): string` menghitung keadaan modul. Modul terbuka bila semuanya benar:
1. `field_mode = 1` dan studi `active` (28 §5.1), atau pratinjau validator menyala ([§5.5](#55-pratinjau-untuk-instalasi-uji-validator));
2. sesi game siswa terikat ke sesi lapangan yang **buka**, dan rencana pertemuan studinya (`research_studies.meeting_plan`, 28 K19 dan §5.2) memberi pertemuan itu kunci `test` (`pre` di P1, `post` di P5), termasuk susulannya. Rencana `satu_sesi` (KG2) tidak punya kunci `test`, jadi modul tes, angket, dan butir risiko daring tidak pernah terbuka di sana;
3. siswa aktif (`participants.deactivated_at IS NULL`, 30 §5.3);
4. masih ada langkah modul yang belum selesai untuk siswa itu ([§4](#4-satu-siswa-dari-p1-sampai-p5)).

Di luar syarat ini, `/tes` menampilkan pesan tertutup yang sama dengan game di luar sesi (28 §7), tanpa menyebut alasan selain "Modul ini dibuka kakak peneliti saat pertemuan."

### 5.2 Arah setelah masuk

- P1: `SessionService::registerAndStart()` (`app/Services/SessionService.php`, baris 44) mengarahkan ke `/tes`, bukan `/intro`, bila syarat §5.1 terpenuhi (28 §6.5 langkah 8).
- P5: setelah token diterima, siswa ke peta seperti biasa; setelah P1 menekan *Mulai tes akhir*, filter baru `instrumentRedirect` mengarahkan rute tantangan ke `/tes` ([§5.4](#54-batas-20-menit-di-p5)).
- Selama modul belum selesai di pertemuan itu, `/intro`, `/penutup`, dan `/refleksi` juga diarahkan ke `/tes`.

### 5.3 Mulai tes bersama

**Tombol** *Mulai tes awal* (P1) atau *Mulai tes akhir* (P5) di halaman sesi lapangan (kemampuan `field_sessions.manage`). Menekannya mengisi `field_sessions.test_started_at` dengan UPDATE bersyarat `test_started_at IS NULL`; log audit `field_test_started {field_session_code}`.
- *Mulai tes awal* aktif hanya bila panel menampilkan "Semua siswa sudah punya bentuk tes" (K10). Sebelum itu tombolnya mati dengan keterangan "Masukkan baris dan kursi semua siswa dulu."
- Siswa di layar pembuka melihat tombol *Mulai* tidak aktif dan kalimat menunggu. Halaman bertanya ke `GET /api/instrument/status` setiap 10 detik; tanpa JavaScript, siswa menekan *Muat ulang*. Saat `test_started_at` terisi dan siswa punya bentuk, tombol aktif.
- Siswa yang masuk terlambat langsung mendapat tombol aktif.
- Sesi susulan punya tombolnya sendiri.

### 5.4 Batas 20 menit di P5

Keputusan pengembang no. 1 (K1):
- P1 menjaga 20 menit dengan jam, seperti usulan 28 §21 no. 2, dan memutuskan kapan tes akhir dimulai.
- Setelah *Mulai tes akhir*, filter `instrumentRedirect` menolak rute tantangan (`/peta`, `/wilayah/…`, `/misi/…`, `/tantangan/…`, dan API `check`/`submit` tantangan) untuk sesi game yang terikat ke sesi lapangan itu. Halaman diarahkan ke `/tes`; API menjawab `409` dengan kode `INSTRUMENT_STARTED`, dan `game/challenge.js` memuat `/tes` (dengan `state.leaving`, supaya tidak ada `challenge_abandoned` palsu).
- Pengerjaan yang sedang terbuka tidak ditutup (28 K14); datanya tersensor menurut 11.
- Siswa tanpa tantangan tertinggal boleh membuka pengerjaan ulang sampai tombol ditekan (13 → *Waktu di P1 dan P5*).

### 5.5 Pratinjau untuk instalasi uji validator

Keputusan pengembang no. 4 (K4). Validator di instalasi uji (20 prasyarat no. 8) perlu melihat modul dan skala tanpa mode penelitian.
- Sakelar baru `Config\Gelita::$instrumentPreview` (bawaan `false`), dinyalakan lewat `.env` hanya di instalasi uji.
- Bila menyala, akun yang nama penggunanya diawali `uji.` (28 §13) mendapat tautan *Coba modul tes* di menu HUD dan skala sikap di Balai Refleksi, tanpa token dan tanpa batas waktu.
- Bentuk tes pratinjau dipilih validator di layar pembuka (A atau B). Butir risiko tampil bila izin butir risiko Ya (30 §5.2: `gelita:uji:akun` mengisinya Ya).
- Jawaban pratinjau disimpan dengan `instrument_sittings.is_preview = 1` dan tidak pernah masuk unduhan atau penanda tindak lanjut.

---

## 6. Isi instrumen di berkas data

### 6.1 Berkas

Keputusan pengembang no. 2 (K2). Tiga berkas PHP yang mengembalikan array, di samping `data/story.php`:

| Berkas | Isi | Sumber |
| --- | --- | --- |
| `data/instrumen/tes.php` | Petunjuk pembuka, petunjuk bagian, contoh `tes-ct-01`–`05`, bacaan dan adegan (`tes-mb-A-M1` … `tes-bd-B-G2`), 64 butir (`tes-mb-A01` … `tes-bd-B08`) | [13](13_TES_AWAL_AKHIR.md), [14](14_TES_BAGIAN_MEMBACA.md), [15](15_TES_BAGIAN_DIGITAL.md) |
| `data/instrumen/angket.php` | Petunjuk, bagian A–H, ang-01–ang-17 dengan penjelasan baku, ang-21–ang-23 dengan pengantarnya | [16](16_ANGKET_AKSES_DIGITAL.md) |
| `data/instrumen/sikap.php` | Petunjuk layar pertama, `skp-ct-01`, `skp-01`–`skp-12`, empat pilihan | [17](17_SKALA_SIKAP.md) |

Setiap berkas dibuka dengan `'version' => 'tes-0.1'` (atau `angket-0.1`, `sikap-0.1`) dan `'status' => 'needs_verification'`. Versi naik setiap isi berubah; `*-1.0` diberikan saat dibekukan di G4 (17: `sikap-1.0`).

**Bentuk satu butir tes** (contoh dari 14; isi lengkap mengikuti tabel butir apa adanya):

```php
// One test item. 'show' is sent to the page; 'key' never leaves the server.
'tes-mb-A07' => [
    'part'     => 'mb', 'form' => 'A', 'no' => 7, 'stimulus' => 'tes-mb-A-M2',
    'type'     => 'match',
    'show'     => [
        'prompt' => 'Pasangkan setiap makanan dengan keterangan yang cocok menurut teks dan tabel.',
        'left'   => ['1' => 'Papeda', '2' => 'Sagu lempeng', '3' => 'Kapurung'],
        'right'  => ['A' => 'bercampur sayuran dan ikan atau ayam', 'B' => '…', 'C' => '…', 'D' => '…'],
    ],
    'key'      => ['answer' => '1B 2D 3A', 'errors' => ['C' => 'INF-HUB', 'other_pair' => 'INF-REL']],
    'cell'     => ['sub' => 'D1a', 'level' => 'L2'],
],
```

**Jenis butir** (`type`) dan cara mesin membacanya:

| `type` | Dipakai | Jawaban mentah yang disimpan | Skor |
| --- | --- | --- | --- |
| `pg` | Tes PG, D3 | Satu huruf `A`–`D` | 1 bila sama dengan kunci |
| `pgk` | Tes PGK B/S atau F/P | Pola tiga huruf, misalnya `BSB`, `PFF` | 1 bila seluruh pola sama |
| `match` | Tes menjodohkan | Pasangan `1C 2A 3D` | 1 bila ketiga pasangan tepat |
| `d2` | Skenario D2 | Satu huruf | Dari peta skor per huruf (2, 1, 0) |
| `single` | Angket pilih satu, satu baris tabel | Kode angka | — |
| `multi` | Angket centang semua (ang-02, ang-12, ang-23) | `1`/`0` per kotak, satu satuan per variabel | — |
| `likert4` | Skala sikap | Kode 1–4 | Disimpan mentah; pembalikan butir negatif di bagian 9 |

Butir yang tidak dijawab disimpan kosong (`NULL`) dan ditulis `-` di unduhan (13, 16, 17). Kode kesalahan PGK dan menjodohkan mengikuti *Aturan kode* di 14: satu kode per pernyataan atau pasangan yang salah, dipisah titik koma.

**Layar tiruan dan gambar.** Unggahan *Kedugram*, layar tiruan D2, dan adegan D3 digambar tim katalog dari keterangan di 14 dan 15. Berkas data menyimpan tulisannya sebagai teks dan menunjuk gambar lewat slot `instr.{kode bacaan}` (misalnya `instr.tes-mb-A-M3`), dipasang lewat `gelita:katalog:impor` seperti slot lain. Selama gambar belum ada, modul menampilkan tata letak teks yang sama tanpa ilustrasi, dan `AssetChecklist` mencatat slot yang kosong. Sejak 3 November 2026 ke-24 gambar M3, M4, D2, dan D3 (bentuk A dan B) tersedia di `gelita-katalog/mockup/gambar-mockup/instrumen/aset/instr.{kode bacaan}.webp`, dari berkas asli pengembang di [gambar/doc-34/](gambar/doc-34/). Bagian ini yang mendaftarkannya ke `CatalogImporter::MOCKUP_FILES` dan `AssetChecklist` saat slotnya dibuat.

### 6.2 Impor

Perintah baru `gelita:instrumen:impor [--berkas=tes|angket|sikap] [--dry-run]` (`app/Commands/InstrumentImport.php`), dan `gelita:reset` memanggilnya setelah impor bank.

**Pemeriksaan sebelum menulis** (impor gagal seluruhnya bila satu gagal):
- kode butir unik dan sesuai pola 13 (`tes-{bagian}-{bentuk}{nn}`, `ang-{nn}`, `skp-{nn}`);
- bentuk A dan B punya nomor butir, bagian, jenis butir, dan sel yang sama (13 → *Aturan kesetaraan*);
- setiap butir tes punya kunci yang cocok dengan jenisnya, dan setiap pilihan salah PG/D2/D3 punya kode kesalahan (15: pilihan tepat tanpa kode);
- 16 butir `mb`, 8 `ae`, 8 `bd` per bentuk; 17 butir angket utama dan 41 satuan; 12 butir sikap dengan empat negatif di posisi 3, 6, 8, 10;
- setiap `stimulus` yang dirujuk ada.

**Versi.** Bila isi berkas (hash SHA-256 isi yang dinormalisasi) sama dengan versi aktif, tidak ada yang ditulis. Bila berbeda tetapi `version` sama, impor ditolak dengan pesan "Isi berubah, naikkan versinya." Versi baru menjadi aktif; versi lama tetap, karena sesi tes yang sudah dibuat menunjuk versinya sendiri. Butir tidak pernah diubah di tempat.

**Cache.** Isi aktif dibaca lewat `InstrumentRepository` (mirip `ContentRepository`), dengan `flush()` setelah impor.

---

## 7. Modul tes dan angket

### 7.1 Tampilan

- **Bootstrap 5.3 lokal**, hanya CSS: `public/assets/vendor/bootstrap-5.3.3/bootstrap.min.css` dengan lisensinya, dicatat di `public/assets/vendor/README.md` dengan SHA-256 seperti Howler. Tidak ada JavaScript Bootstrap. Versi pastinya dipilih saat implementasi (rilis 5.3 terbaru yang sudah berumur lebih dari dua minggu).
- **Tata letak baru** `app/Views/layouts/instrument.php`, terpisah dari `layouts/game.php`: tanpa HUD, musik, narasi, efek layar, paralaks, Howler, atau `cine.js`. Huruf Plus Jakarta Sans lokal, ukuran dasar besar (± 20 px), latar putih polos.
- **Bilah atas:** "Tes awal" atau "Tes akhir" (atau "Angket", atau "Tes akhir · pertanyaan tambahan" di butir risiko, 18 celah no. 3), dan kode penelitian siswa. Tidak ada nama.
- **Halaman tes:** di layar lebar bacaan di kiri (bernomor paragraf, bergulir di dalam kartunya) dan soal di kanan; di layar sempit bertumpuk (`mk-mt-02`). Daftar nomor soal 1–16 (atau 1–8) di bawah, dengan tanda "belum dijawab" berupa teks dan bentuk, bukan warna saja.
- **Target sentuh** paling kecil 44 × 44 px; pilihan berupa tombol radio besar dengan huruf dan teks.
- Teks bingkai di berkas bahasa baru `app/Language/{id,en}/Instrument.php` ([§13](#13-teks-antarmuka)).

### 7.2 Sesi tes dan bentuk

Satu baris `instrument_sittings` per siswa per studi per kesempatan per jenis (`pre`/`post` × `test`/`questionnaire`/`risk`, dan `p5` × `attitude`).
- Dibuat saat siswa menekan *Mulai* di layar pembuka, lewat `INSERT` yang dijaga indeks unik; yang kalah membaca baris yang sudah ada.
- **Bentuk** disalin dari `participant_seats.pre_form` atau `post_form` saat dibuat, dan tidak berubah sesudahnya. Tes awal sekaligus mengunci kursi (`UPDATE participant_seats SET locked_at = ? WHERE id = ? AND locked_at IS NULL`, K10).
- **Versi isi** disalin dari versi aktif saat dibuat.
- `field_session_id` sesi lapangan tempat sesi tes dimulai, `is_makeup` dari sesi lapangan itu.

### 7.3 Alur bagian

1. **Layar pembuka** dengan petunjuk 13 → *Petunjuk di layar pembuka* (kata demi kata dari berkas data) dan kode penelitian (`mk-mt-01`).
2. **Contoh soal** sebelum bagian, ditandai *Contoh*, tanpa daftar nomor soal. Tombol *Mulai Bagian 1*.
3. **Butir.** Satu butir per halaman, `GET /tes/bagian/{part}/{no}`. Tombol *Sebelumnya*, *Berikutnya*, dan nomor di daftar soal semuanya mengirim formulir yang sama (`POST`), yang menyimpan jawaban di halaman itu lalu mengarahkan ke tujuan. Butir boleh dilewati.
4. **Periksa sebelum mengirim** (`mk-mt-04`): nomor yang belum dijawab disebut, *Kembali ke soal* atau *Kirim Bagian 1*, dengan peringatan bahwa bagian tertutup setelah dikirim.
5. **Kirim bagian:** `UPDATE instrument_parts SET submitted_at = ? WHERE id = ? AND submitted_at IS NULL AND closed_at IS NULL`. Permintaan yang kalah dijawab dari keadaan terbaru (langkah berikutnya).
6. **Antara blok `p1` dan `p23`:** layar peregangan ("Istirahat sebentar…") dengan tombol *Lanjut ke Bagian 2*; waktu blok `p23` baru berjalan setelah contoh Bagian 2.
7. **Bagian 3** dalam blok `p23` mulai setelah Bagian 2 dikirim, didahului contoh `tes-ct-05`.

**Urutan pilihan** selalu seperti di berkas data (14, 15). Tidak ada pengacakan di mana pun.

**Setelah bagian dikirim,** halaman butirnya menjawab dengan pengalihan ke langkah berikutnya; jawaban yang datang sesudahnya ditolak (`PART_CLOSED`) dan tidak mengubah apa pun.

### 7.4 Batas waktu tersembunyi

**Pengaturan studi** (K3, 01 no. 22). Kolom baru `research_studies.instrument_settings` (JSON), diubah `ketua` di halaman studi (`studies.manage`), dengan bawaan di `Config\Gelita::$instrumentDefaults`:

```php
// Hidden time limits for the pre/post test (revision 10, part 8). Minutes.
public array $instrumentDefaults = [
    'limit_p1'      => 40,   // Bagian 1 Membaca (13)
    'limit_p23'     => 35,   // Bagian 2 and 3 together (13)
    'reminder'      => 5,    // friendly reminder before the limit (13)
    'extra_max'     => 30,   // most extra minutes P1 may add per block
    'makeup_days'   => 3,    // test make-up window (13)
    'attitude_days' => 7,    // attitude-scale make-up window (17)
];
```

Batas akhir ditetapkan dari uji coba (13: waktu yang cukup untuk 95% siswa), jadi angkanya pengaturan, bukan konstanta. Perubahan pengaturan dicatat `study_instrument_settings_changed` dengan nilai lama dan baru, dan hanya berlaku untuk blok yang dimulai sesudahnya.

**Cara kerja:**
- Saat blok dimulai, baris `instrument_blocks` menyimpan `started_at`, `limit_minutes` (salinan pengaturan saat itu), dan `extra_minutes` 0. Batas = `started_at + limit + extra`.
- Setiap permintaan modul memeriksa batas. Bila lewat, blok ditutup: `UPDATE instrument_blocks SET closed_at = ?, closed_reason = 'limit' WHERE id = ? AND closed_at IS NULL`, lalu setiap bagian di blok itu yang belum dikirim ditutup dengan `closed_by_limit = 1`, di transaksi yang sama. Jawaban terakhir di halaman yang dikirim sebelum batas tetap disimpan; yang datang sesudahnya tidak.
- `gelita:retention:run` juga menutup blok yang sudah lewat batas tanpa permintaan lagi, supaya data tidak menggantung.
- **Pengingat:** halaman membawa `data-remind-in` dan `data-close-in` (detik, dari server). JavaScript kecil `public/assets/js/instrument/timer.js` menampilkan pengingat ramah (`mk-mt-03`) saat `remind-in` habis dan memuat ulang halaman saat `close-in` habis. Tidak ada jam atau hitung mundur di layar. Tanpa JavaScript, batas tetap ditegakkan server pada permintaan berikutnya.

**Tambah menit** (keputusan pengembang no. 3). Di halaman sesi lapangan dan panel pemantauan, P1 menekan *Tambah waktu* pada kode siswa, memilih blok yang sedang berjalan, mengisi menit (1–`extra_max`) dan alasan (misalnya "perangkat mati"). `UPDATE instrument_blocks SET extra_minutes = extra_minutes + ? WHERE id = ? AND closed_at IS NULL`. Log audit `instrument_time_extended {participant_code, occasion, block, minutes, reason}`. Blok yang sudah ditutup batas tidak bisa dibuka lagi; untuk itu dipakai susulan. Menit tambahan masuk unduhan ([§11.4](#114-data-untuk-kamus-dan-unduhan)).

### 7.5 Penyimpanan jawaban dan waktu

**Satuan jawaban** (`instrument_responses`), dibuat untuk semua satuan bagian itu saat bagian dibuka, dengan `response` kosong. Satu baris per satuan (K7).

**Saat siswa membuka butir:** server mengisi `shown_at` (jam server) di baris satuannya, menambah `view_count`, dan bila ini tampil pertama, `first_shown_at`.

**Saat formulir butir dikirim:**
- Waktu tampil kali ini = jam server sekarang − `shown_at`, dibatasi paling lama `limit` blok. Ditambahkan ke `time_on_item_ms`, dan disimpan juga sebagai `last_view_ms` (17 memakai waktu terakhir).
- Bila jawaban berbeda dari yang tersimpan dan yang tersimpan tidak kosong, `answer_changes` bertambah satu.
- Tulisan memakai `UPDATE instrument_responses SET … , revision = revision + 1 WHERE id = ? AND revision = ?` lalu memastikan bagiannya masih terbuka. Bila salah satu gagal (dua tab, atau bagian baru ditutup), transaksi dibatalkan dan permintaan dijawab dari keadaan terbaru: halaman butir yang sama dimuat ulang, atau langkah berikutnya bila bagian sudah tertutup.

Waktu diukur dari jam server, jadi termasuk jeda jaringan. Di jaringan lokal kelas selisihnya kecil; ambang tebakan cepat ditinjau setelah uji coba (13).

**Lanjut di perangkat lain.** Siswa masuk lagi dengan token yang sama; `/tes` membuka butir terakhir yang tampil di bagian yang terbuka (13: "melanjutkan di perangkat lain dari soal terakhir").

**Yang tidak pernah dikirim ke browser:** `key`, peta skor, kode kesalahan, skor, `not_reached`, dan isi bentuk lain. Tampilan hanya menerima `show` dari berkas data.

### 7.6 Penskoran mentah

Saat bagian dikirim atau ditutup batas, `InstrumentScorer::scorePart(int $partId)` mengisi per satuan:
- `score` sesuai tabel jenis butir di §6.1 (kosong untuk angket dan sikap);
- `error_codes` dari kunci (14 → *Aturan kode*, 15 → kolom *Kode*);
- `not_reached = 1` bila bagian ditutup batas dan butir itu belum pernah tampil (`first_shown_at IS NULL`). Butir yang sudah tampil tapi kosong dihitung dilewati (skor 0).

Lalu `instrument_parts.raw_score` dan `answered_count` diisi. Skor contoh soal tidak dihitung.

Skor tidak pernah tampil untuk siswa, guru, atau sekolah. Tes awal dinilai sebelum P2 (13), jadi `ketua` bisa membaca skor mentah `pre` per siswa di data mentah untuk memilih siswa wawancara L5.

### 7.7 Angket

- Dibuka langsung setelah Bagian 3 tes awal dikirim atau ditutup (16 → *Cara pelaksanaan* no. 1), di modul yang sama, tanpa batas waktu.
- Layar pembuka angket dengan petunjuk 16 → *Petunjuk untuk siswa*. Satu bagian A–H per layar, penanda "Bagian 3 dari 8", tombol pilihan dan kotak centang besar, penjelasan baku kecil di bawah pertanyaan yang memakainya (`mk-mt-05`).
- Pindah layar menyimpan seperti butir tes (§7.5). Tombol *Selesai* di layar terakhir mengirim angket dengan UPDATE bersyarat yang sama.
- Kotak "Tidak ada satu pun" tidak menghapus centang lain (16: data apa adanya; penandanya `q_flags` dihitung bagian 9).
- Bila P1 berakhir sebelum angket selesai, angket menunggu dan dilanjutkan di awal P2: dalam mode penelitian, token P2 mengarahkan siswa dengan angket terbuka ke `/tes` lebih dulu (16 no. 1). Ini satu-satunya keadaan modul terbuka di pertemuan tanpa kunci `test`.

---

## 8. Butir risiko daring, T11, dan T12

### 8.1 Alur

Aturannya dari 30 §5.4; di sini penerapannya.
1. Setelah Bagian 3 tes akhir dikirim atau ditutup, modul membuat `instrument_sittings` jenis `risk` untuk **setiap** siswa, dengan `risk_offered` dari `ConsentService::riskItemsAllowed()` (30) dibaca saat itu.
2. `risk_offered = 1`: layar T11 (teks 23 → T11, dua tombol sama besar dan sama warna). Pilihan disimpan sebagai `risk_reconsent` (1/0) dan `risk_reconsent_at` dengan UPDATE bersyarat `risk_reconsent IS NULL`.
3. *Ya*: ang-21, ang-22, ang-23, satu butir per layar ("Pertanyaan 1 dari 3", `mk-mt-06`), dengan pengantar 16 dan pilihan *Tidak mau menjawab* (kode `7`). Tidak ada batas waktu.
4. *Tidak*, `risk_offered = 0`, atau setelah ang-23: layar T12 (23 → T12), satu tombol *Lanjut*.
5. Layar selesai modul, lalu ke cerita penutup.

Jumlah butir (dua atau tiga) mengikuti berkas data; mesin tidak menganggap jumlahnya tetap (16 → *Keputusan pengembang* no. 2).

### 8.2 Privasi di layar dan di panel

- Layar T11, butir risiko, dan T12 memakai tampilan yang sama polosnya, tanpa warna mencolok atau ilustrasi.
- Lama tinggal di layar tidak dibuat sama untuk semua siswa; yang menyamakan adalah T12 di titik yang sama untuk semua (30 §5.4).
- Panel pemantauan bagian 2 hanya menunjukkan status tes akhir dan "modul selesai" (30 §5.4); `risk_offered`, `risk_reconsent`, dan jawaban tidak pernah tampil di pemantauan, data mentah, atau halaman siswa.

### 8.3 Data

- Jawaban ang-21–ang-23 disimpan di `instrument_responses` seperti angket, di bawah sesi jenis `risk`.
- `RiskFollowUpService::flags()` (30 §6) membaca satuan `risk_upset` dan `support_none` dari sesi jenis `risk` yang `is_preview = 0`.
- Unduhan: hanya lembar `risiko_gabungan` (29 §7.6). Siswa dengan `risk_offered = 0` dihitung sebagai *tidak diberi butir*; `risk_reconsent = 0` sebagai *tidak mau mengisi*; kode `7` sebagai *tidak mau menjawab*. `RawDataService` dan lembar per siswa mengecualikan sesi jenis `risk` di kuerinya sendiri, bukan dengan menyaring sesudahnya.
- Cakupan hapus `risk_items` (30 §7.2) menghapus sesi jenis `risk` anak itu beserta jawabannya (kunci asing `ON DELETE CASCADE`), dan `RetentionService` menghitung barisnya untuk pratinjau.

---

## 9. Skala sikap di Balai Refleksi

### 9.1 Kapan tampil

Dalam mode penelitian (K4, K12), `ReflectionController::index()` (baris 18–37) memeriksa urutan ini:
1. Semua tantangan tuntas (`GameProgress::allNodesCompleted()`, `app/Controllers/Concerns/GameProgress.php` baris 220). Bila belum: pesan sekarang (`Game.reflectionLocked`).
2. Sesi game terikat ke sesi lapangan P5 yang buka, atau susulannya dalam `attitude_days` hari setelah tanggal P5 utama. Bila tidak: "Balai Refleksi dibuka di pertemuan terakhir."
3. Modul P5 sudah selesai, dan cerita penutup sudah dibuka di sesi game ini (event `dialogue_completed` konteks `ending`, 29 §5.3). Bila belum, diarahkan ke langkah yang belum.
4. Sesi `attitude` siswa belum dikirim → diarahkan ke `/refleksi/sikap`.
5. Sudah dikirim → halaman Balai Refleksi dengan pujian, ringkasan, dan kritik dan saran (§9.4).

Siswa yang belum tuntas di akhir P5 tidak membuka Balai Refleksi dan tidak punya sesi `attitude`; alasan kosongnya (`belum_tuntas`) dihitung bagian 9 (17).

### 9.2 Layar

`AttitudeController` (baru) dengan tata letak game (`layouts/game.php`), latar Balai Refleksi, satu kartu biru tua di tengah panggung (`mk-ss-01`–`04`, 17 → *Layar*):
- **Petunjuk** (17 → *Petunjuk untuk siswa*), tombol *Coba contoh*.
- **Contoh** `skp-ct-01` bertanda *Contoh*, tanpa bilah kemajuan.
- **Pernyataan 1–12**, satu per layar, urutan tetap. Empat pilihan dari kiri ke kanan, titik emas yang makin besar dengan kata di bawahnya, tanpa wajah; pilihan terpilih bercahaya dan bertanda ●. Tombol *Kembali*, tautan kecil *Lewati*, dan *Lanjut* yang aktif setelah memilih atau melewati.
- **Bilah kemajuan** perunggu (`loading track`, `--loading-track`) 1/12–12/12.
- **Layar kirim:** "Kamu sudah menjawab {n} dari 12 kalimat…" (18), *Kembali* atau *Kirim*.
- Tidak ada narasi, rekaman, Mbah Kedu, bintang, serpihan, atau umpan balik. Musik latar Balai Refleksi tetap (bukan narasi); efek suara pilihan tidak ada.

Halaman dirender server dan disimpan per layar seperti modul tes (K6), dengan waktu tampil per pernyataan (`last_view_ms` dipakai sebagai `skp_nn_ms`, 17) dan `answer_changes`. *Kirim* menutup sesi dengan UPDATE bersyarat `submitted_at IS NULL`; setelah itu layar skala tidak terbuka lagi (17 → *Diisi ulang*).

### 9.3 Butir yang dilewati

*Lewati* menyimpan satuan itu kosong dan maju. Bila siswa sebelumnya sudah memilih lalu menekan *Lewati*, jawabannya dikosongkan dan `answer_changes` bertambah. Di unduhan, kosong ditulis `-` (17).

### 9.4 Balai Refleksi sesudah skala dan kritik dan saran

Dalam mode penelitian, setelah skala dikirim:
- `Game.reflectionLead` (pujian Mbah Kedu) baru tampil, bersama pesan "Pendapatmu tentang budaya Kedu sudah terkirim. Terima kasih!" (`mk-ss-05`).
- Kritik dan saran tetap ada. `ReflectionController::store()` memakai aturan penelitian: `rating` menjadi `permit_empty|integer|greater_than[0]|less_than[6]`, dan pemeriksaan "minimal dua" (baris 147–151) dilewati. Petunjuk "Semua pertanyaan boleh dikosongkan." menggantikan "Isi minimal dua pertanyaan, ya."
- Formulir yang dikirim kosong semua tetap disimpan satu baris, supaya "sudah melihat kritik dan saran" terbaca; `feedback_submitted` mencatat `rating` (atau `null`) dan `open_answers`.
- `ParticipantFeedbackModel` (`app/Models/ParticipantFeedbackModel.php`, aturan `rating` baris 19) mengikuti mode yang sama.

Di luar mode penelitian semua ini tetap seperti sekarang (§3).

---

## 10. Susulan

### 10.1 Tes susulan

- Peneliti membuat sesi susulan P1 atau P5 seperti biasa (28 §6.7; anggotanya siswa yang belum punya sesi tes, tercentang otomatis).
- Modul membuka tes di susulan hanya bila tanggal susulan paling lambat `makeup_days` (3) hari setelah sesi utama. Lewat dari itu, halaman sesi susulan menampilkan "Tes susulan sudah lewat 3 hari; tes tidak dibuka di sesi ini." dan modul hanya membuka angket.
- Bentuk sama dengan yang ditetapkan, batas waktu sama (13). Tombol *Mulai tes* sesi susulan dipakai seperti §5.3.
- Tes awal susulan ditolak bila siswa sudah bermain (K11).
- `is_makeup = 1` di sesi tesnya (`pre_makeup`, `post_makeup` di 13).

### 10.2 Angket terlambat

Angket yang belum diisi boleh diisi di sesi susulan P1 kapan saja sebelum P5 utama dibuka. Bila siswa sudah punya pengerjaan tantangan, sesi angketnya ditandai `late = 1` (`questionnaire_late`, 16 no. 9). Setelah P5 utama dibuka, angket P1 tidak lagi ditawarkan.

### 10.3 Susulan skala sikap

Susulan P5 biasa dengan jam buka yang panjang (28 §6.7 no. 6), paling lambat `attitude_days` (7) hari setelah P5 utama. Urutannya sama: tes akhir susulan bila masih dalam 3 hari, butir risiko, cerita penutup, skala. Sesi `attitude` di susulan mendapat `is_makeup = 1` (`sikap_makeup`, 17).

---

## 11. Skema database

### 11.1 Tabel baru

**Migrasi `2026-01-01-004200_CreateInstrumentModule`** (nomor sementara).

| Tabel | Kolom |
| --- | --- |
| `instrument_versions` | `id`, `instrument` VARCHAR(10) (`tes`, `angket`, `sikap`), `version` VARCHAR(20), `content_hash` CHAR(64), `status` VARCHAR(20) (`needs_verification`, `frozen`), `is_active` TINYINT(1), `imported_at`. Unik (`instrument`, `version`) |
| `instrument_stimuli` | `id`, `version_id` FK, `code` VARCHAR(30) (`tes-mb-A-M1`), `show_json` JSON, `image_slot` VARCHAR(60) (null). Unik (`version_id`, `code`) |
| `instrument_items` | `id`, `version_id` FK, `code` VARCHAR(20), `part` VARCHAR(10) (`ct`, `mb`, `ae`, `bd`, `A`–`H` angket, `risk`, `sikap`), `form` CHAR(1) (null), `position` SMALLINT, `type` VARCHAR(10), `stimulus_id` FK (null), `show_json` JSON, `key_json` JSON (null), `units_json` JSON (nama satuan/variabel), `cell_json` JSON (null). Unik (`version_id`, `code`) |
| `instrument_sittings` | `id`, `participant_id` FK `ON DELETE CASCADE`, `study_id` FK, `field_session_id` FK (null), `occasion` VARCHAR(4) (`pre`, `post`, `p5`), `kind` VARCHAR(14) (`test`, `questionnaire`, `risk`, `attitude`), `form` CHAR(1) (null), `version_id` FK, `is_makeup`, `late`, `is_preview` TINYINT(1) bawaan 0, `risk_offered` TINYINT(1) (null), `risk_reconsent` TINYINT(1) (null), `risk_reconsent_at` (null), `started_at`, `submitted_at` (null), `last_item_id` (null), `updated_at` DATETIME(3). Unik (`participant_id`, `study_id`, `occasion`, `kind`, `is_preview`) |
| `instrument_blocks` | `id`, `sitting_id` FK `ON DELETE CASCADE`, `block` VARCHAR(4) (`p1`, `p23`), `started_at`, `limit_minutes` SMALLINT, `extra_minutes` SMALLINT bawaan 0, `closed_at` (null), `closed_reason` VARCHAR(10) (null: `submitted`, `limit`). Unik (`sitting_id`, `block`) |
| `instrument_parts` | `id`, `sitting_id` FK `ON DELETE CASCADE`, `part` VARCHAR(10), `block_id` FK (null untuk angket, risiko, sikap), `opened_at`, `submitted_at` (null), `closed_at` (null), `closed_by_limit` TINYINT(1) bawaan 0, `raw_score` SMALLINT (null), `answered_count` SMALLINT (null). Unik (`sitting_id`, `part`) |
| `instrument_responses` | `id`, `part_id` FK `ON DELETE CASCADE`, `sitting_id` FK `ON DELETE CASCADE`, `item_id` FK, `unit` VARCHAR(40), `response` VARCHAR(40) (null), `score` TINYINT (null), `error_codes` VARCHAR(80) (null), `not_reached` TINYINT(1) bawaan 0, `first_shown_at`, `shown_at`, `answered_at` (null), `view_count` SMALLINT bawaan 0, `time_on_item_ms` INT bawaan 0, `last_view_ms` INT (null), `answer_changes` SMALLINT bawaan 0, `revision` INT bawaan 0, `updated_at` DATETIME(3). Unik (`sitting_id`, `unit`) |

`updated_at` di `instrument_sittings` dan `instrument_responses` diisi setiap kali baris ditulis (simpan jawaban, kirim, tutup, persetujuan ulang), supaya sidik jari lapis turunan memakai satu aturan `MAX(updated_at)` untuk semua tabel ([35 §4.3](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#43-sidik-jari-masukan), [§18](#18-keputusan-pengembang) no. 6). `revision` tetap untuk penyimpanan bersyarat, bukan untuk sidik jari.

Kunci asing ke `participants` memakai `ON DELETE CASCADE`, mengikuti aturan hapus bagian 4 (30 §7.3); `RetentionService::HARD_ORDER` tetap menghapusnya satu per satu.

### 11.2 Tabel yang diubah

| Tabel | Perubahan |
| --- | --- |
| `field_sessions` (28) | Kolom baru `test_started_at` DATETIME (null), `test_started_by` FK staf `ON DELETE SET NULL` |
| `research_studies` | Kolom baru `instrument_settings` JSON (null; null = bawaan `Config\Gelita::$instrumentDefaults`) |
| `participant_feedback` | `rating` boleh `NULL` (sekarang TINYINT tidak null, migrasi `2026-01-01-002400_CreateParticipantFeedback.php` baris 18) |

**`down()`** membuang tabel dan kolom baru. `rating` yang sudah kosong tidak bisa dikembalikan; `down()` mengisinya 0 dan menulis itu di komentar migrasi.

### 11.3 Yang ikut diperbarui

- `tests/_support/Database/ResetTables.php` (skema SQLite).
- `SystemResetService`: tabel jawaban instrumen di kelompok yang dikosongkan (`GROUP_A`, `CLEAR_ORDER`), tabel isi instrumen diisi ulang lewat impor; `REQUIRED_MIGRATION` mengikuti migrasi terakhir yang sudah digabung.
- `RetentionService::HARD_ORDER` (`app/Services/RetentionService.php`, baris 24–28): `instrument_responses`, `instrument_parts`, `instrument_blocks`, `instrument_sittings` sebelum `participants`; `scopedBuilder()` dan cakupan `risk_items` (30 §7.3).
- Model baru `InstrumentVersionModel`, `InstrumentItemModel`, `InstrumentStimulusModel`, `InstrumentSittingModel`, `InstrumentBlockModel`, `InstrumentPartModel`, `InstrumentResponseModel`; `FieldSessionModel`, `ResearchStudyModel`, `ParticipantFeedbackModel`: `allowedFields` dan aturan.
- Layanan baru di `app/Services/`, didaftarkan di `app/Config/Services.php`: `InstrumentRepository` (isi aktif, cache), `InstrumentService` (sesi, bagian, simpan, kirim, batas waktu, tambah menit), `InstrumentScorer`, `InstrumentGate`.

### 11.4 Data untuk kamus dan unduhan

Bagian ini menyediakan data mentah. Lembar dan kolom ditetapkan bersama bagian 3 (29 §7.2 menyebut lembar dari no. 21 dan 22 ditambahkan ke daftar yang sama); variabel turunan dan kolom berstatus dihitung bagian 9; semua masuk `kamus-1.1`.

| Lembar (usulan) | Satu baris per | Kolom mentah dari bagian ini |
| --- | --- | --- |
| `tes_jawaban` | Satuan per siswa per kesempatan | `participant_code`, `occasion`, `form`, `item_code`, `response`, `score`, `error_codes`, `not_reached`, `time_on_item` (ms), `answer_changes`, `instrument_version` (13 → *Jawaban tes*); `rapid_guess` dari bagian 9: tabel turunan `derived_test_responses` (lembar `turunan_tes`, [35 §6.8](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#68-derived_test_responses-satu-baris-per-jawaban-tes)), digabung per `participant_code`, `occasion`, dan `item_code` dari putaran yang berlaku ([§18](#18-keputusan-pengembang) no. 7) |
| `tes_bagian` | Bagian per siswa per kesempatan | `participant_code`, `occasion`, `part`, `block`, `started_at`, `submitted_at`, `closed_by_limit`, `extra_minutes`, `raw_score`, `answered_count`, `is_makeup`, `field_session_code` |
| `angket` | Siswa | `questionnaire_date`, `questionnaire_makeup`, `questionnaire_late`, `questionnaire_minutes`, dan 41 kolom `phone_access` … `books_home` (16) |
| `sikap` | Siswa | `skp_ct_01`, `skp_01`–`skp_12`, `skp_01_ms`–`skp_12_ms`, `skp_changes`, `sikap_makeup`, `sikap_submitted_at`, `sikap_version`, `feedback_rating` (17) |
| `peserta` (tambahan) | Siswa | `pre_form`, `post_form`, `pre_date`, `post_date`, `pre_makeup`, `post_makeup`, `pre_d1`–`post_d3` (dari `raw_score`), `pre_minutes_p1` … `post_minutes_p23`, `pre_capped`, `post_capped` (13) |
| `risiko_gabungan` | Butir × pilihan | Bentuknya di 29 §7.6; sumbernya sesi jenis `risk` |

Sesi `is_preview = 1` tidak pernah masuk unduhan. Contoh soal tes (`tes-ct-*`) tidak masuk `tes_jawaban`.

---

## 12. Rute

Path siswa berbahasa Indonesia, controller dan metode berbahasa Inggris, path `/api` berbahasa Inggris.

**Siswa** (grup filter `participantAuth` dan filter baru `instrumentGate`; tanpa `screenView`, K14):

| Metode dan path | Target | Isi |
| --- | --- | --- |
| `GET /tes` | `InstrumentController::index` | Arahkan ke langkah berikutnya, atau layar pembuka |
| `POST /tes/mulai` | `InstrumentController::start` | Membuat sesi tes (§7.2) |
| `GET /tes/contoh/{part}` | `InstrumentController::example` | Contoh soal sebelum bagian |
| `POST /tes/bagian/{part}/mulai` | `InstrumentController::startPart` | Mulai bagian (dan blok bila perlu) |
| `GET /tes/bagian/{part}/(:num)` | `InstrumentController::item` | Satu butir |
| `POST /tes/bagian/{part}/(:num)` | `InstrumentController::saveItem` | Simpan lalu pindah (`tujuan` = nomor, `sebelumnya`, `berikutnya`, `periksa`) |
| `GET /tes/bagian/{part}/periksa` | `InstrumentController::review` | Periksa sebelum mengirim |
| `POST /tes/bagian/{part}/kirim` | `InstrumentController::submitPart` | Kirim bagian |
| `GET /tes/jeda` | `InstrumentController::stretch` | Peregangan antara blok |
| `GET /tes/angket/(:num)`, `POST /tes/angket/(:num)` | `QuestionnaireController::section`, `::saveSection` | Satu bagian angket |
| `POST /tes/angket/selesai` | `QuestionnaireController::finish` | Kirim angket |
| `GET /tes/tambahan`, `POST /tes/tambahan` | `RiskItemsController::ask`, `::answerAsk` | T11 |
| `GET /tes/tambahan/(:num)`, `POST /tes/tambahan/(:num)` | `RiskItemsController::item`, `::saveItem` | ang-21–ang-23 |
| `GET /tes/pesan` | `RiskItemsController::support` | T12 |
| `GET /tes/selesai` | `InstrumentController::done` | Layar selesai, tombol ke `/intro` atau `/penutup` |
| `GET /refleksi/sikap` | `AttitudeController::index` | Petunjuk |
| `GET /refleksi/sikap/contoh`, `GET /refleksi/sikap/(:num)` | `AttitudeController::example`, `::item` | Contoh dan pernyataan |
| `POST /refleksi/sikap/(:num)` | `AttitudeController::saveItem` | Simpan, lewati, kembali |
| `GET /refleksi/sikap/kirim`, `POST /refleksi/sikap/kirim` | `AttitudeController::review`, `::submit` | Layar kirim |
| `GET /api/instrument/status` | `Api\InstrumentController::status` | `{started: bool, form_ready: bool}` untuk layar pembuka |

`{part}` adalah kode bagian (`mb`, `ae`, `bd`). Rute `/refleksi/sikap` berada di grup `gameSession` yang sama dengan `/refleksi` (`app/Config/Routes.php`, baris 61–62).

**Panel** (filter `staffCan`):

| Metode dan path | Target | Kemampuan |
| --- | --- | --- |
| `POST /admin/sesi-lapangan/{id}/mulai-tes` | `FieldTestController::start` | `field_sessions.manage` |
| `POST /admin/sesi-lapangan/{id}/tambah-waktu` | `FieldTestController::extendTime` | `field_sessions.manage` |
| `POST /admin/studi/{id}/instrumen` | `StudyController::updateInstrumentSettings` | `studies.manage` |

**Perintah spark:** `gelita:instrumen:impor`.

---

## 13. Teks antarmuka

Berkas baru `app/Language/id/Instrument.php` dan `app/Language/en/Instrument.php` (setiap kunci id punya pasangan en, `LanguageFilesTest`). Teks panel di `app/Language/id/Admin.php` (hanya Indonesia). Isi butir, petunjuk instrumen, T11, dan T12 tidak di sini: petunjuk dan butir di berkas data (K13), T11 dan T12 di berkas bahasa dengan kunci dan teks dari 30 §17.

Semua teks di bawah adalah teks awal dari 18 (*Draf salinan*, diterima pengembang 26 Oktober 2026) atau usulan baru, dan ikut uji keterbacaan ([24](24_UJI_KETERBACAAN.md#bahan-yang-menunggu-uji-keterbacaan)).

**Modul tes dan angket (`Instrument.php`)**

| Kunci | id | en |
| --- | --- | --- |
| `barPre` | Tes awal | Pre-test |
| `barPost` | Tes akhir | Post-test |
| `barQuestionnaire` | Angket | Questionnaire |
| `barRisk` | Tes akhir · pertanyaan tambahan | Post-test · extra questions |
| `researchCode` | Kode penelitian: {0} | Research code: {0} |
| `waitStart` | Tunggu sebentar, ya. Kakak peneliti akan memberi tanda kapan mulai. | Please wait a moment. A researcher will tell you when to start. |
| `waitForm` | Kakak peneliti sedang menyiapkan soalmu. Tunggu sebentar, ya. | A researcher is getting your questions ready. Please wait a moment. |
| `reload` | Muat ulang | Reload |
| `start` | Mulai | Start |
| `example` | Contoh | Example |
| `startPart` | Mulai Bagian {0} | Start Part {0} |
| `previous` | Sebelumnya | Previous |
| `next` | Berikutnya | Next |
| `answered` | sudah dijawab | answered |
| `unanswered` | belum dijawab | not answered |
| `reminder` | Waktunya hampir selesai. Periksa lagi jawabanmu, terutama soal yang masih bertanda, lalu kirim bagian ini. | Time is almost up. Check your answers again, especially the questions still marked, then send this part. |
| `reviewTitle` | Periksa dulu sebelum mengirim | Check before you send |
| `reviewMissing` | Soal yang belum dijawab: {0}. | Questions not answered yet: {0}. |
| `reviewAll` | Semua soal sudah dijawab. | All questions are answered. |
| `reviewWarn` | Setelah dikirim, bagian ini tidak bisa dibuka lagi. | Once you send it, you can't open this part again. |
| `backToItems` | Kembali ke soal | Back to the questions |
| `sendPart` | Kirim Bagian {0} | Send Part {0} |
| `partClosed` | Waktu bagian ini sudah habis. Jawabanmu sudah tersimpan. | Time for this part is up. Your answers are saved. |
| `stretch` | Istirahat sebentar. Regangkan tangan dan punggungmu, lalu lanjutkan. | Take a short break. Stretch your arms and back, then go on. |
| `continuePart` | Lanjut ke Bagian {0} | Go on to Part {0} |
| `sectionOf` | Bagian {0} dari {1} | Part {0} of {1} |
| `questionOf` | Pertanyaan {0} dari {1} | Question {0} of {1} |
| `finish` | Selesai | Finish |
| `doneTitle` | Terima kasih! | Thank you! |
| `doneBody` | Kamu sudah selesai. Tunggu petunjuk kakak peneliti, ya. | You're done. Please wait for the researcher's instructions. |
| `toIntro` | Lanjut ke cerita | Go on to the story |
| `toEnding` | Lanjut ke cerita penutup | Go on to the closing story |
| `closed` | Modul ini dibuka kakak peneliti saat pertemuan. | A researcher opens this during the meeting. |
| `noPreAfterPlay` | Tes awal sudah lewat. Bilang ke kakak peneliti, ya. | The pre-test has passed. Please tell a researcher. |
| `previewForm` | Pilih bentuk tes untuk pratinjau | Choose the test form to preview |

**Skala sikap (`Game.php`, karena tampil di layar game)**

| Kunci | id | en |
| --- | --- | --- |
| `attitudeTry` | Coba contoh | Try an example |
| `attitudeExample` | Contoh | Example |
| `attitudeSkip` | Lewati | Skip |
| `attitudeReview` | Kamu sudah menjawab {0} dari 12 kalimat. {1} kalimat kamu lewati. Kalau mau, kamu masih bisa kembali dan menjawabnya. Setelah dikirim, jawabanmu tidak bisa diganti lagi. | You answered {0} of 12 sentences. You skipped {1}. If you like, you can still go back and answer them. Once you send, your answers can't be changed. |
| `attitudeSend` | Kirim | Send |
| `attitudeThanks` | Pendapatmu tentang budaya Kedu sudah terkirim. Terima kasih! | Your thoughts about Kedu culture have been sent. Thank you! |
| `reflectionAllOptional` | Semua pertanyaan boleh dikosongkan. | You can leave every question empty. |
| `reflectionLastMeeting` | Balai Refleksi dibuka di pertemuan terakhir. | The Hall of Reflection opens at the last meeting. |

Judul, petunjuk, pernyataan, dan empat label pilihan skala ada di `data/instrumen/sikap.php` (K13); padanan en tidak dibuat untuk isi instrumen. `attitudeReview` memakai bentuk satu kalimat bila tidak ada yang dilewati (kunci tambahan `attitudeReviewAll`: "Kamu sudah menjawab semua 12 kalimat. Setelah dikirim, jawabanmu tidak bisa diganti lagi." / "You answered all 12 sentences. Once you send, your answers can't be changed.").

**Panel (`Admin.php`)**

| Kunci | Usulan |
| --- | --- |
| `fieldTestStartPre` | Mulai tes awal |
| `fieldTestStartPost` | Mulai tes akhir |
| `fieldTestNeedSeats` | Masukkan baris dan kursi semua siswa dulu. |
| `fieldTestStartPostHint` | Menekan tombol ini menutup tantangan untuk semua siswa sesi ini. Pengerjaan yang belum selesai dibiarkan. |
| `fieldTestStarted` | Tes dimulai pukul {0}. |
| `fieldTestMakeupLate` | Tes susulan sudah lewat 3 hari; tes tidak dibuka di sesi ini. |
| `instrumentExtend` | Tambah waktu |
| `instrumentExtendReason` | Alasan (misalnya perangkat mati) |
| `instrumentSettings` | Batas waktu tes (tidak tampil ke siswa) |
| `testStatus` | Status tes: belum, sedang, selesai, susulan, hilang |

---

## 14. Titik sambung ke perubahan lain

| Perubahan | Yang disediakan bagian ini | Yang dirancang di sana |
| --- | --- | --- |
| Bagian 1 ([27](27_IMPLEMENTASI_PERAN_PENELITI.md)) | Tidak ada kemampuan baru (K15) | Peta kemampuan, `staffCan` |
| Bagian 2 ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md)) | `field_sessions.test_started_at`, tombol mulai tes, filter `instrumentRedirect`, `participant_seats.locked_at` diisi; status tes untuk pemantauan (`belum`, `sedang`, `selesai`, `susulan`, `hilang`) dan tombol *Tambah waktu*; arah `/tes` setelah daftar | Sesi lapangan, token, kursi, bentuk, pemantauan, susulan |
| Bagian 3 ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md)) | Lembar `tes_jawaban`, `tes_bagian`, `angket`, `sikap`, kolom `peserta` tambahan ([§11.4](#114-data-untuk-kamus-dan-unduhan)); sumber `risiko_gabungan`; status L6 tes dari modul untuk templat | `ExportService::SHEETS`, `ResearchDictionary`, templat L6 |
| Bagian 4 ([30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md)) | Layar T11 dan T12, sesi jenis `risk` dengan `risk_offered` dan `risk_reconsent`, tabel untuk `HARD_ORDER` dan cakupan `risk_items`; sumber data `RiskFollowUpService`; `RiskItemsGateTest` bisa diaktifkan | `ConsentService::riskItemsAllowed()`, aturan dan teks T11/T12, penanda tindak lanjut, salinan satu anak |
| No. 16, bagian 7 ([33](33_IMPLEMENTASI_MODE_PENELITIAN.md)) | Balai Refleksi dalam mode penelitian (§9) | Ubin *Total waktu* disembunyikan; pengerjaan terbuka dilanjutkan di pertemuan lain |
| No. 2, bagian 6 ([32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md)) | — | Kolom bintang per wilayah untuk 15 tantangan di Balai Refleksi (18 celah no. 8) |
| No. 9, bagian 9 ([35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md)) | Data mentah §11.4, `raw_score`, `not_reached`, waktu per satuan | `rapid_guess`, `test_status`, skor Rasch, `questionnaire_status`, indeks angket, `q_flags`, `sikap_score`, `sikap_sk1`–`4`, `sikap_n_answered`, `sikap_flag_straight`, `sikap_missing_reason`, `consent_ok` |
| Uji keterbacaan ([24](24_UJI_KETERBACAAN.md)) | Pratinjau `uji.*` (§5.5) | Teks akhir layar modul dan skala |

---

## 15. Pengujian dan kriteria selesai

### 15.1 Tes baru

| Tes | Yang diperiksa |
| --- | --- |
| `InstrumentImportTest` | Impor tiga berkas contoh; pemeriksaan kesetaraan A/B, jumlah butir, kunci per jenis, kode kesalahan; isi sama tidak menulis; isi berbeda dengan versi sama ditolak; versi baru menjadi aktif tanpa mengubah sesi lama |
| `InstrumentGateTest` | Modul tertutup saat `field_mode` mati, di luar sesi, di pertemuan tanpa `test`, untuk siswa nonaktif; terbuka di P1/P5 dan susulannya; angket terbuka di P2 bila belum selesai; pratinjau hanya untuk `uji.*` |
| `InstrumentStartTest` | Mulai menunggu `test_started_at` dan bentuk; bentuk disalin dari `participant_seats`; kursi terkunci sekali; dua mulai bersamaan membuat satu sesi; tes awal ditolak setelah bermain (K11) |
| `InstrumentFlowTest` | Satu siswa P1 dari pembuka sampai `/intro`; P5 sampai `/penutup`; kembali ke butir sebelumnya; kirim menutup bagian; jawaban sesudah kirim ditolak `PART_CLOSED` |
| `InstrumentTimeLimitTest` | Batas `p1` dan `p23` dari pengaturan studi (jam palsu); bagian ditutup dengan `closed_by_limit`; `not_reached` hanya untuk butir yang belum tampil; tambah menit memundurkan batas dan menulis log audit; blok tertutup tidak bisa ditambah; `gelita:retention:run` menutup blok yang menggantung |
| `InstrumentResponseTest` | Waktu tampil dari jam server, `time_on_item_ms` bertambah, `last_view_ms`; `answer_changes`; balapan dua tab (data basi lewat `Factories::injectMock`) dijawab dari keadaan terbaru |
| `InstrumentScorerTest` | Untuk **setiap** butir dalam berkas data: jawaban kunci → skor penuh; setiap pilihan salah → skor dan kode kesalahan dari berkas; PGK dan menjodohkan menulis satu kode per pernyataan atau pasangan salah; D2 2/1/0; skor mentah per bagian (pengganti ketik ulang 10%, 13) |
| `InstrumentKeyLeakTest` | HTML setiap halaman butir, angket, risiko, dan sikap tidak memuat `key_json`, skor, kode kesalahan, atau butir bentuk lain; `/api/instrument/status` hanya dua boolean |
| `QuestionnaireTest` | Satuan per variabel (41); centang semua menyimpan 1/0 per kotak dan semua kosong bila tak satu pun dicentang; `late` di susulan setelah bermain |
| `RiskItemsFlowTest` | `risk_offered` dari izin; T11 hanya bila izin Ya dan aktif; *Tidak* dan tanpa izin langsung T12; `risk_reconsent` sekali; jawaban tidak muncul di data mentah atau lembar per siswa; cakupan hapus `risk_items` menghapus sesi `risk` |
| `AttitudeScaleTest` | Urutan Balai Refleksi dalam mode penelitian (§9.1); contoh tidak dihitung; *Lewati* mengosongkan; kirim sekali; pujian hanya setelah kirim; susulan dalam 7 hari menandai `is_makeup`; lewat 7 hari tertutup |
| `ReflectionFeedbackStudyTest` | Dalam mode penelitian semua isian boleh kosong dan `rating` null tersimpan; di luar mode aturan lama tetap |
| `FieldTestStartTest` | *Mulai tes awal* mati sebelum semua punya bentuk; *Mulai tes akhir* menutup rute tantangan dan API menjawab `INSTRUMENT_STARTED`; log audit |

### 15.2 Tes yang ditulis ulang

- Tes Balai Refleksi yang ada: dijalankan dengan `field_mode` mati dan tetap hijau tanpa diubah; varian mode penelitian ditambahkan.
- `LanguageFilesTest`: berkas `Instrument.php` baru.
- Tes `RetentionService` dan `SystemResetService`: tabel instrumen di `HARD_ORDER` dan kelompok reset.
- `RiskItemsGateTest` dan `RiskAggregateSheetTest` (30, 29): diaktifkan dengan data modul sungguhan.

### 15.3 Cek manual

1. Di instalasi lokal dengan `field_mode` menyala: satu kelas tiruan `gelita:simulasi` atau empat akun, P1 dari daftar dengan token sampai cerita pembuka, di laptop dan tablet berlayar sempit.
2. Matikan jaringan di tengah Bagian 1, pindah ke perangkat lain, lanjut dari soal terakhir; tambah menit dari panel.
3. Biarkan satu blok lewat batas (pengaturan 2 menit): pengingat tampil tanpa jam, bagian tertutup sendiri.
4. P5: tantangan tertinggal, *Mulai tes akhir* saat seorang siswa di tengah tantangan; layar tantangan berpindah ke `/tes` tanpa `challenge_abandoned` palsu.
5. T11 dengan izin Ya dan Tidak; periksa pemantauan tidak menunjukkan bedanya.
6. Balai Refleksi: skala, kirim, pujian, kritik dan saran kosong semua.
7. Lihat sumber halaman (Ctrl+U) di halaman butir: tidak ada kunci.
8. Pratinjau `uji.*` di instalasi dengan `instrumentPreview` menyala dan `field_mode` mati.

### 15.4 Selesai bila

- Semua tes di §15.1 dan §15.2 hijau dengan `vendor/bin/phpunit --no-coverage`.
- Berkas data berisi isi 13–17 apa adanya (status `needs_verification`) dan lolos impor.
- Bootstrap lokal tercatat di `public/assets/vendor/README.md` dengan SHA-256; tidak ada permintaan ke luar domain di halaman modul.
- Dengan `field_mode` mati, game dan Balai Refleksi berperilaku sama seperti sebelum bagian ini.
- Cek manual §15.3 dijalankan dan hasilnya dicatat di catatan keputusan perombakan kelas 5.

---

## 16. Dokumen yang ikut diperbarui saat implementasi

| Dokumen | Yang diubah |
| --- | --- |
| `docs/07` dan `docs/08` (teknis) | Modul tes, berkas data instrumen, `gelita:instrumen:impor`, pengaturan studi |
| `public/assets/vendor/README.md` | Bootstrap: versi, SHA-256, lisensi |
| `.claude/rules/glossary.md` | Istilah baru di [§19](#19-akibat-ke-dokumen-lain) |
| `CLAUDE.md` (*Spark commands*) | `gelita:instrumen:impor` |
| `docs/PROGRESS.md`, `docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md` | Baris dan keputusan implementasi |
| [11](11_KAMUS_DATA.md) | Variabel mentah §11.4 di `kamus-1.1` (bersama bagian 9, setelah uji coba) **Sudah**: `kamus-1.1` draf (3 November 2026). |

---

## 17. Pertanyaan terbuka

Tidak menahan draf ini.

1. **Ambang tebakan cepat untuk tes** (13: ditinjau setelah uji coba; waktu membaca bacaan jatuh ke butir pertama). Bagian 9 menghitungnya; bagian ini hanya menyediakan waktu per satuan. Perlu angka dari peneliti.
2. **Gambar layar tiruan** untuk M3, M4, D2, dan D3: ditutup 3 November 2026. Ke-24 gambar sudah ada (§6.1), jadi uji keterbacaan memakai gambar, bukan tata letak teks. Gambarnya masih `draf pengembang`; pemeriksaan ahli yang diminta 15 (misalnya motif karawo di `tes-bd-A-G1`) tetap berlaku.
3. **Isi instrumen dalam bahasa en** (K13): tidak dibuat. Bila penelitian nanti butuh versi en (misalnya untuk publikasi), terjemahannya dokumen tersendiri, bukan berkas bahasa.
4. **Angket setelah P5 utama dibuka** (§10.2): ditutup. Siswa yang belum mengisi sampai saat itu `hilang`. 16 no. 9 menulis "sampai P5"; dibaca di sini sebagai "sebelum P5 dimulai". Perlu konfirmasi peneliti.
5. **Pemeriksaan JavaScript di perangkat lab.** Modul bekerja tanpa JavaScript (K6), tetapi pengingat 5 menit butuh JavaScript. Bila perangkat sekolah mematikannya, pengingat tidak tampil; batas tetap ditegakkan. Dicek di uji coba.
6. **Teks akhir layar** (18 celah no. 3, §13) ditetapkan setelah telaah ahli media dan uji keterbacaan.

---

## 18. Keputusan pengembang

Satu putaran wawancara, 2 November 2026 (`asal: pengembang`). Pengembang memilih opsi yang disarankan di keempat pertanyaan.

1. **Batas 20 menit di P5: tombol P1 menutup game** (K1). P1 menjaga waktu dengan jam lalu menekan *Mulai tes akhir*, yang menutup tantangan dan mengarahkan semua siswa ke modul. Menjawab 28 §21 no. 2. Tidak dipilih: dijaga P1 dengan jam saja tanpa penegakan sistem; ditutup otomatis 20 menit setelah sesi buka.
2. **Isi di berkas data PHP** `data/instrumen/*.php` seperti `data/story.php` (K2). Tidak dipilih: lembar workbook baru (bacaan panjang dan layar tiruan sulit di sel); berkas JSON.
3. **Perangkat bermasalah: P1 menambah menit** dengan alasan dan log audit; tambahannya masuk unduhan (K3). Tidak dipilih: jam berhenti saat diam (siswa yang diam ikut mendapat waktu tanpa terlihat); tanpa tambahan.
4. **Mengikuti `field_mode`, dengan pratinjau untuk akun `uji.*`** (K4). Tidak dipilih: dua sakelar studi tersendiri (lebih banyak kombinasi); skala selalu menyala (mengumpulkan data tanpa dasar izin di luar penelitian).
5. **Dokumen disetujui pengembang** (2 November 2026), bersama keputusan rancangan K5–K15 dan penyelarasan dengan rencana `satu_sesi` (PR #319). Pertanyaan terbuka di [§17](#17-pertanyaan-terbuka) tetap untuk peneliti dan tidak menahan persetujuan ini.

**Sesi koordinator 31–35, 3 November 2026** (cabang `r10-cek-30`, `asal: pengembang`, `disetujui pengembang`):

6. **`instrument_sittings` dan `instrument_responses` mendapat `updated_at`** (§11.1), sehingga sidik jari lapis turunan ([35 §4.3](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#43-sidik-jari-masukan)) memakai satu aturan `MAX(updated_at)` untuk semua tabel. Tidak dipilih: sidik jari memakai `revision`.
7. **`tes_jawaban.rapid_guess` dibaca dari tabel turunan `derived_test_responses`** bagian 9 (§11.4), yang disimpan dan berversi per putaran seperti tabel `derived_*` lain. Tidak dipilih: dihitung hanya saat unduhan dibuat.

---

## 19. Akibat ke dokumen lain

Untuk sesi koordinator. **Status per 3 November 2026:** baris 28, 13, 29, 30, 27, 17, 18, dan 01 dibawa sesi koordinator bagian 8 (cabang `r10-dok-34-modul-tes-sikap`, PR #325, 2 November 2026) dan sesi koordinator 31–35 (cabang `r10-cek-30`, 3 November 2026). Yang belum, karena menunggu: 16 no. 9 (konfirmasi peneliti, §17 no. 4), 11 (`kamus-1.1`, bersama bagian 9 setelah uji coba), glosarium (saat implementasi, §16), dan berkas serah terima (bila dibuat versi baru).

| Dokumen | Bagian | Yang perlu diubah | Status |
| --- | --- | --- | --- |
| [28](28_IMPLEMENTASI_SESI_LAPANGAN.md) | §21 no. 2 dan 3 | Dicoret: no. 2 diputuskan di 34 K1 (tombol P1 menutup tantangan); no. 3 diatur di 34 §10.2 (angket terlambat di susulan P1 sampai P5 utama dibuka). Rencana `satu_sesi` tanpa modul tes dan skala sikap tertulis di 34 §5.1 dan K12 | Sudah (PR #325, cabang `r10-dok-34-modul-tes-sikap`, 2 November 2026; sesi koordinator 3 November 2026, cabang `r10-cek-30`) |
| 28 | §6.5 langkah 8, §12, §14 | Arah ke `/tes` setelah daftar; status tes di pemantauan diisi dari 34 §14, ditambah tombol *Tambah waktu*; kolom `field_sessions.test_started_at`, `test_started_by` (dibuat migrasi 34) | Sudah (PR #325, cabang `r10-dok-34-modul-tes-sikap`, 2 November 2026; sesi koordinator 3 November 2026, cabang `r10-cek-30`) |
| [13](13_TES_AWAL_AKHIR.md) | *Modul tes di GELITA* butir "Belum diputuskan" | Diputuskan di 34 K1; tombol *Mulai tes awal/akhir*; tambah menit oleh P1 (34 K3) di *Aturan selama tes* | Sudah (PR #325, cabang `r10-dok-34-modul-tes-sikap`, 2 November 2026; sesi koordinator 3 November 2026, cabang `r10-cek-30`) |
| [29](29_IMPLEMENTASI_DATA_PENELITIAN.md) | §7.2 | Lembar `tes_jawaban`, `tes_bagian`, `angket`, `sikap`, kolom `peserta` tambahan (34 §11.4); sumber `risiko_gabungan` = sesi jenis `risk` | Sudah (PR #325, cabang `r10-dok-34-modul-tes-sikap`, 2 November 2026; sesi koordinator 3 November 2026, cabang `r10-cek-30`) |
| [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md) | §7.3, §18 | Nama tabel jawaban modul (`instrument_sittings`, `instrument_blocks`, `instrument_parts`, `instrument_responses`); cakupan `risk_items` = sesi jenis `risk`; `risk_offered` disimpan per siswa | Sudah (PR #325, cabang `r10-dok-34-modul-tes-sikap`, 2 November 2026; sesi koordinator 3 November 2026, cabang `r10-cek-30`) |
| [27](27_IMPLEMENTASI_PERAN_PENELITI.md) | §15 | No. 21 dan 22 tidak menambah kemampuan | Sudah (PR #325, cabang `r10-dok-34-modul-tes-sikap`, 2 November 2026; sesi koordinator 3 November 2026, cabang `r10-cek-30`) |
| [16](16_ANGKET_AKSES_DIGITAL.md) | *Cara pelaksanaan* no. 9 | "sampai P5" dibaca "sebelum P5 utama dibuka" bila peneliti setuju (§17 no. 4) | Menunggu peneliti (§17 no. 4) |
| [17](17_SKALA_SIKAP.md) | Pembuka, *Pelaksanaan di P5* | Kalimat lama "sistem diubah di tahap 3" (04, 1 November 2026); Balai Refleksi hanya di sesi P5 dalam mode penelitian (34 K12) | Sudah (PR #325, cabang `r10-dok-34-modul-tes-sikap`, 2 November 2026; sesi koordinator 3 November 2026, cabang `r10-cek-30`) |
| [18](18_MOCKUP_LAYAR_BARU.md) | *Celah* no. 3 | Salinan modul tes dibawa ke 34 §13 | Sudah (PR #325, cabang `r10-dok-34-modul-tes-sikap`, 2 November 2026; sesi koordinator 3 November 2026, cabang `r10-cek-30`) |
| [11](11_KAMUS_DATA.md) | `kamus-1.1` | Kolom tambahan `extra_minutes`, `instrument_version`, `risk_offered` (hanya gabungan) di samping variabel 13, 16, 17 | **Sudah**: `kamus-1.1` draf (3 November 2026) |
| [01](01_REVISI_10_ANALITIK.md) | *Catatan perubahan sistem* no. 21, 22 | Rujukan ke 34 | Sudah (PR #325, cabang `r10-dok-34-modul-tes-sikap`, 2 November 2026; sesi koordinator 3 November 2026, cabang `r10-cek-30`) |
| `.claude/rules/glossary.md` | Tabel *Game* dan *People, data, and the panel* | Baris baru: modul tes (`instrument module`, `/tes`), sesi tes (`sitting`, `instrument_sittings`), blok waktu (`time block`, `p1`/`p23`), tambah waktu (`extra minutes`), skala sikap (`attitude scale`, `/refleksi/sikap`, `sikap`), pertanyaan tambahan (`risk items`, `/tes/tambahan`), *Mulai tes awal/akhir* (`test start`, `field_sessions.test_started_at`), pratinjau instrumen (`instrument preview`, `Config\Gelita::$instrumentPreview`) | Saat implementasi |
| `docs/revisi-10-analitik/dokumen-pdf/serah-terima-peneliti.pdf` | Pertanyaan untuk peneliti | §17 no. 1, 2, 4 bila dibuat versi baru | Bila dibuat versi baru |
