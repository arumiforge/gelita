# Dokumen Implementasi Bagian 4 — Persetujuan, Hak atas Data, Masa Simpan, dan IP Berhash

Per 30 Oktober 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · perubahan sistem no. 23 di [01 → Catatan perubahan sistem](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti)

> **Status: `draf pengembang`, `asal: pengembang`** (disusun dengan bantuan AI, 30 Oktober 2026). Ini dokumen, bukan perubahan sistem. Kode, migrasi, skrip cadangan, dan instalasi baru diubah di tahap 3, setelah gerbang G2 dan setelah dokumen implementasi lengkap disetujui ([04](04_CATATAN_KEPUTUSAN.md), entri 30 Oktober 2026).
>
> Draf ini boleh naik sampai `disetujui pengembang`. Sebelum dokumen implementasi lengkap disetujui, isinya dicek ulang sekali terhadap matriks revisi induk ([20](20_PAKET_VALIDASI.md)) dan keputusan komite etik. Bagian ini paling mungkin berubah karena komite, jadi setiap perubahan dari komite ditandai `asal: komite etik`.
>
> **Bagian 4 dari 4.** Bagian ini memakai peran dan peta kemampuan dari [27](27_IMPLEMENTASI_PERAN_PENELITI.md) (`deletion.request`, `deletion.execute`, `risk_flags.view`, `research.export`, `participants.accounts`), sesi lapangan, rombel, dan kode peserta dari [28](28_IMPLEMENTASI_SESI_LAPANGAN.md), serta riwayat unduhan, `data_exports.participant_count`, dan `field_sheet_rows` dari [29](29_IMPLEMENTASI_DATA_PENELITIAN.md).

---

## Daftar isi

0. [Cara memakai dokumen ini](#0-cara-memakai-dokumen-ini)
1. [Kebutuhan, sumber, dan tempatnya](#1-kebutuhan-sumber-dan-tempatnya)
2. [Keputusan yang dikunci](#2-keputusan-yang-dikunci)
3. [Yang tidak berubah](#3-yang-tidak-berubah)
4. [Tenggat: sebelum akun GELITA anak pertama kali dipakai](#4-tenggat-sebelum-akun-gelita-anak-pertama-kali-dipakai)
5. [Persetujuan di sistem](#5-persetujuan-di-sistem)
6. [Penanda tindak lanjut butir risiko daring](#6-penanda-tindak-lanjut-butir-risiko-daring)
7. [Permintaan hapus data](#7-permintaan-hapus-data)
8. [Salinan data satu anak](#8-salinan-data-satu-anak)
9. [Pembetulan data pendaftaran](#9-pembetulan-data-pendaftaran)
10. [Penutupan studi](#10-penutupan-studi)
11. [Masa simpan](#11-masa-simpan)
12. [IP berhash di sesi peramban](#12-ip-berhash-di-sesi-peramban)
13. [Jalan manual sementara](#13-jalan-manual-sementara)
14. [Hak akses dan menu](#14-hak-akses-dan-menu)
15. [Skema database](#15-skema-database)
16. [Rute, perintah, dan skrip](#16-rute-perintah-dan-skrip)
17. [Teks layar dan panel](#17-teks-layar-dan-panel)
18. [Titik sambung ke perubahan lain](#18-titik-sambung-ke-perubahan-lain)
19. [Pengujian dan kriteria selesai](#19-pengujian-dan-kriteria-selesai)
20. [Dokumen yang ikut diperbarui saat implementasi](#20-dokumen-yang-ikut-diperbarui-saat-implementasi)
21. [Pertanyaan terbuka](#21-pertanyaan-terbuka)
22. [Keputusan pengembang](#22-keputusan-pengembang)
23. [Akibat ke dokumen lain](#23-akibat-ke-dokumen-lain)

---

## 0. Cara memakai dokumen ini

**Urutan.** Implementasinya dikerjakan setelah bagian 1–3:
- peran `peneliti` dan `ketua`, peta kemampuan, dan cakupan `research()` berasal dari bagian 1;
- sesi lapangan, rombel, kode peserta `S03-A07`, `field_mode`, dan `participant_progress` berasal dari bagian 2;
- katalog event, isi unduhan `ketua`, riwayat unduhan, dan `field_sheet_rows` berasal dari bagian 3.

**Batas.** Dokumen ini mengatur:
- persetujuan di sistem: teks centang anak (23 → T4), izin berkode per siswa dari L7, status mundur, dan akun nonaktif;
- penanda tindak lanjut butir risiko daring untuk `ketua`;
- permintaan hapus data (semua dan sebagian), termasuk ekspor, salinan peneliti, cadangan, daftar periksa, dan berita acara;
- salinan data satu anak dan pembetulan data pendaftaran;
- penutupan studi: daftar izin berkode, nama dan sekolah diganti kode;
- masa simpan studi penelitian dan IP berhash di sesi peramban;
- jalan manual sementara selama fungsi di atas belum ada.

Layar T11 dan T12 berada di modul tes (no. 22), jadi di sini hanya teks, aturan tampil, dan data yang disimpan ([§5.4](#54-layar-t11-dan-t12)). Skala sikap (no. 21) dan modul tes (no. 22) disebut sebagai **titik sambung** ([§18](#18-titik-sambung-ke-perubahan-lain)).

**Saat implementasi:**
- Satu cabang pendek dari `main`, commit berbahasa Inggris.
- `vendor/bin/phpunit --no-coverage` hijau sebelum push.
- Jangan menjalankan migrasi, `gelita:reset`, atau skrip rilis di server produksi. Penghapusan, penggantian nama, dan penggantian cadangan tidak bisa dibatalkan; urutannya diuji dulu di instalasi uji.
- Setiap tulisan yang bisa bersaing (perubahan izin, perpindahan status permintaan, pembetulan) memakai UPDATE bersyarat, bukan `SELECT … FOR UPDATE` (aturan kerja repo, bagian *Concurrency*).

**Rujukan kode.** Ditulis per berkas dan metode, dengan nomor baris per 30 Oktober 2026. Nomor baris bisa bergeser.

---

## 1. Kebutuhan, sumber, dan tempatnya

| # | Kebutuhan | Sumber | Di sini |
| ---: | --- | --- | --- |
| 1 | Teks centang persetujuan mode penelitian, id dan en | 01 no. 23; [23 → T4](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#t4-usulan-teks-centang-persetujuan-di-halaman-daftar); [22 → Lapis persetujuan](22_PERMOHONAN_IZIN_ETIK.md#lapis-persetujuan) | §5.1 |
| 2 | Izin rekaman suara dan izin butir risiko daring per siswa, dengan tanggal; butir risiko hanya untuk siswa yang izinnya Ya | 22 → Lapis persetujuan; 01 no. 22; [02 → L7](02_LEMBAR_LAPANGAN.md#l7-izin-orang-tua-dan-status-peserta); [28 → Titik sambung](28_IMPLEMENTASI_SESI_LAPANGAN.md#18-titik-sambung-ke-perubahan-lain) | §5.2 |
| 3 | Pengumpulan data dihentikan paling lambat 3 × 24 jam sejak permintaan mundur diterima | [22 → Mengundurkan diri dan hapus data](22_PERMOHONAN_IZIN_ETIK.md#mengundurkan-diri-dan-hapus-data) (PP 33/2026 Pasal 92) | §5.3 |
| 4 | Layar izin ulang T11 dan pesan dukungan T12 untuk semua siswa di P5 | 01 no. 23; [23 → T11](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#t11-layar-sebelum-butir-risiko-daring), [T12](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#t12-pesan-dukungan-sesudah-butir-risiko-daring) | §5.4 |
| 5 | Penanda tindak lanjut dihitung sistem, hanya terlihat `ketua`, tidak pernah diekspor; catatan tindak lanjut di luar sistem | 01 no. 23; [22 → Prosedur rujukan](22_PERMOHONAN_IZIN_ETIK.md#prosedur-rujukan-butir-risiko-daring) langkah 2, 3, 6; [26 → P8](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md#langkah-perlindungan); 27 §4 | §6 |
| 6 | Dua status mundur (`data tetap`, `hapus data`) dan hapus sebagian (rekaman atau jawaban butir risiko) | 01 no. 23; 22 → Mengundurkan diri; [23 → T10](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#t10-formulir-pengunduran-diri-dan-permintaan-hapus-data) | §5.3, §7 |
| 7 | Penghapusan mencakup ekspor yang masih ada, salinan peneliti, dan cadangan server, dengan tanggal untuk berita acara; orang tua diberi tahu paling lambat 3 × 24 jam setelah penghapusan | 01 no. 23; 22 (PP 33/2026 Pasal 82 ayat (3), 86, 87, 88) | §7.4–§7.7 |
| 8 | Siapa mengajukan, siapa menyetujui, siapa menjalankan permintaan hapus | [27 → Pertanyaan terbuka](27_IMPLEMENTASI_PERAN_PENELITI.md#16-pertanyaan-terbuka) no. 2 | §7.1; keputusan no. 1 |
| 9 | Ambil data satu anak lewat kode, tanpa data anak lain dan tanpa kunci jawaban | 01 no. 23; 22 (Pasal 76–78); 23 → T10 pilihan D; 27 §4 | §8 |
| 10 | Pembetulan umur, jenis kelamin, kelas oleh admin, dengan jejak audit; kode, sekolah, dan rombel tidak pernah diubah | 01 no. 23; 22 (Pasal 70–71); 27 §4; [28 → Kode peserta](28_IMPLEMENTASI_SESI_LAPANGAN.md#102-kode-peserta) | §9 |
| 11 | Daftar izin berkode tanpa nama, sebelum nama diganti kode | 01 no. 23; [22 → Masa simpan dan pemusnahan](22_PERMOHONAN_IZIN_ETIK.md#masa-simpan-dan-pemusnahan) (Pasal 36); 02 → L7 | §10.1 |
| 12 | Nama di akun diganti kode dan pemetaan NPSN–kode dimusnahkan saat studi ditutup | 22 → Masa simpan; 26 → P13 | §10.2 |
| 13 | Data berkode disimpan paling singkat 20 tahun (`retention_days` ± 7300; sekarang 1825) | 01 no. 23; 22 → Masa simpan (UU No. 11 Tahun 2019 Pasal 40 ayat (5)) | §11 |
| 14 | `HashedIpSessionHandler` dipasang di `Config\Session`; `ci_sessions` kedaluwarsa dibersihkan rutin | 01 no. 23; [26 → L3](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md#log-teknis-dan-sesi), P18, keputusan no. 2 | §12 |
| 15 | Jalan manual dengan berita acara selama fungsi belum ada; diuji sekali sebelum akun anak dipakai | 26 → P15; 22 → Mengundurkan diri | §13 |
| 16 | Tenggat "sebelum KG2" dibaca sama di semua dokumen | [04](04_CATATAN_KEPUTUSAN.md), temuan untuk bagian 4 (30 Oktober 2026) | §4 |

---

## 2. Keputusan yang dikunci

K1–K4 diputuskan pengembang ([§22](#22-keputusan-pengembang)). K5, K10, dan K11 berasal dari pencarian kode dan dicatat di [04](04_CATATAN_KEPUTUSAN.md) untuk ditinjau. Sisanya menurunkan 01, 22, 23, 26, dan bagian 1–3.

| Kode | Keputusan |
| --- | --- |
| K1 | **Permintaan hapus dari `peneliti` dikonfirmasi `ketua` lebih dulu.** `ketua` mencocokkan kode dengan L7, lalu sistem menghitung pratinjau untuk admin. Permintaan yang diajukan `ketua` langsung dihitung. Kemampuan baru `deletion.confirm` untuk `ketua`. Admin tetap satu-satunya yang menjalankan. |
| K2 | **Salinan untuk orang tua tidak memuat jawaban ang-21–ang-23**, kecuali anak setuju. `ketua` mencentang "anak sudah setuju" dan centang itu tercatat di log audit. Salinan menyebut dengan kalimat biasa bahwa bagian itu tidak disertakan. |
| K3 | **Cadangan diganti setelah penghapusan dan saat studi ditutup.** Admin membuat cadangan baru, lalu menghapus semua cadangan yang lebih lama di kedua lokasi, sebelum orang tua diberi tahu. Lokasi kedua juga hanya menyimpan 30 hari. |
| K4 | **P1 memasukkan semua kolom L7 yang berkode ke panel:** tanggal izin orang tua, izin rekaman suara, izin butir risiko daring, dan status dengan tanggal. Nama tidak pernah. Data ini menentukan siapa melihat T11, menonaktifkan akun saat mundur, dan menjadi daftar izin berkode. |
| K5 | **Akun langsung nonaktif saat status mundur disimpan** (`participants.deactivated_at`), di transaksi yang sama, tanpa menunggu admin. Game menolak masuk dan mengakhiri sesi pada permintaan berikutnya. Hanya admin yang bisa mengaktifkan lagi, dengan alasan. Ini lebih cepat dari 22 dan 23 → T10 ("akun dinonaktifkan admin"), supaya batas 3 × 24 jam selalu terpenuhi. |
| K6 | **Izin baru bawaannya Tidak** (rekaman dan butir risiko) sampai P1 mengisinya. Siswa yang izinnya belum diisi atau Tidak tidak melihat T11 dan langsung melihat T12. |
| K7 | **Penanda tindak lanjut tidak disimpan.** Penanda dihitung saat halaman `ketua` dibuka, dari jawaban ang-21 dan ang-23. Tidak ada kolom "sudah dihubungi", tidak ada CSV, tidak di unduhan, tidak di pemantauan, dan log audit hanya mencatat jumlahnya. |
| K8 | **Permintaan hapus dari orang tua selalu `hard`.** Mode `soft` hanya tersisa di formulir admin untuk cakupan sesi dan studi. |
| K9 | **`RetentionService` tetap satu-satunya jalur yang menghapus data penelitian.** Cakupan baru: jawaban butir risiko satu siswa. Cakupan "rekaman" tidak menghapus apa pun di sistem; isinya hanya daftar periksa, karena rekaman tidak pernah ada di server. |
| K10 | **Masa simpan studi penelitian 7305 hari (20 × 365,25), dihitung sejak studi ditutup** (`research_studies.closed_at`). "± 7300" di 01 kurang lima hari dari 20 tahun. Sekarang retensi menghitung dari mulai sesi (`RetentionService::flagExpiredStudyData()`, baris 429–482), jadi pratinjau hapus bisa muncul sebelum 20 tahun sejak studi ditutup. |
| K11 | **Saat studi ditutup, sekolah ikut diganti kode.** Mengganti nama di akun tidak cukup: `participants.school_id` dan `field_sessions.school_id` masih menunjuk baris sekolah bernama dan ber-NPSN. Sistem membuat satu baris sekolah berkode per kode sekolah penelitian dan memindahkan semua rujukan ke sana. |
| K12 | **IP sesi peramban di-hash.** `Config\Session::$driver = HashedIpSessionHandler::class`. Migrasi mengosongkan `ci_sessions`. `gelita:retention:run` menghapus baris sesi yang kedaluwarsa, karena pembersih sesi bawaan PHP bisa tidak pernah berjalan. |
| K13 | **Pembetulan hanya umur, jenis kelamin, dan kelas**, oleh admin, dengan alasan, nilai lama dan baru di log audit, dan `participants.corrected_at`. |
| K14 | **Salinan satu anak dibuat saat diminta dan langsung diunduh**, tidak disimpan di server: satu ZIP berisi PDF ringkas dan XLSX. Berkode, tanpa kunci jawaban, tanpa teks soal, tanpa id internal, tanpa data anak lain. |
| K15 | **Yang di luar sistem hanya dicatat sebagai centang bertanggal**: rekaman dan transkrip, lembar kertas, salinan peneliti, dan cadangan. Sistem tidak menyimpan nama berkas atau nama orang. |

---

## 3. Yang tidak berubah

| Bagian | Aturan |
| --- | --- |
| Penghapusan dua langkah | `RetentionService::preview()`, `execute()` dengan pemeriksaan selisih (`driftExceeded()`), `cancel()`, audit `delete_preview`, `delete_execute`, `delete_cancel`; kata konfirmasi `HAPUS` di `GovernanceController` |
| Centang persetujuan anak | Langkah 3 pendaftaran, wajib (`assent` `required\|in_list[1]`, `RegisterController.php:265`); waktunya di `participants.assented_at` |
| Unduhan | Berkas di `writable/exports/` dihapus setelah 7 hari (`exportRetentionDays`, `ExportService::purgeExpired()`); hanya `ketua`, selalu berkode (27, 29) |
| Nama | Hanya di akun yang dilihat admin; L7 tidak pernah di server; peneliti dan `ketua` melihat kode (27) |
| Log audit | `AuditLogModel::record()` dengan `ip_hash`; metadata memakai kode peserta, bukan nama (`SessionService.php:281-286`) |
| Hash IP | `hash_ip()` = SHA-256 IP + `gelita.ipSalt` (`app/Helpers/gelita_helper.php:39-46`) |
| Isi cadangan | Database tanpa isi `ci_sessions`, unggahan, paket aset, salinan `.env`; jadwal 01.00 dan jam sekolah ([../08_DEPLOYMENT.md → Backup](../08_DEPLOYMENT.md#backup)) |
| Kode peserta, sekolah, rombel | Tidak pernah diubah setelah ada data (28 §10.2) |

---

## 4. Tenggat: sebelum akun GELITA anak pertama kali dipakai

**Temuan.** Tabel kegiatan di [26 → Kegiatan](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md#kegiatan) memberi nomor KG2–KG5 yang berbeda dari [03 → Kegiatan dengan anak](03_KODE_DAN_ARTI.md#kegiatan-dengan-anak) dan [22 → Kegiatan yang dimintakan izin](22_PERMOHONAN_IZIN_ETIK.md#kegiatan-yang-dimintakan-izin):

| Kode | 03 dan 22 (berlaku) | 26 (keliru) |
| --- | --- | --- |
| KG2 | Berpikir lantang, tahap 4 | Tes awal dan angket (P1) |
| KG3 | Uji coba satu kelas, sekolah `U`, tahap 4 | Bermain P2–P4 |
| KG4 | Wawancara L5 di P2–P4 | P5 |
| KG5 | Pengambilan data utama P1–P5 | Kelas uji coba dan berpikir lantang |

Dengan nomor 26, "sebelum KG2" berarti sebelum P1 data utama, sehingga uji coba (KG5 di 26) bisa berjalan tanpa fungsi ini. Itu bukan maksudnya.

**Tenggat yang dipakai di sini:** *sebelum kegiatan pertama yang memakai akun GELITA anak*. Dengan nomor 03 dan 22 itu KG2 (berpikir lantang) atau KG3 (uji coba), mana yang lebih dulu; keduanya setelah G3. KG1 (uji keterbacaan) tidak memakai akun. Jadi "sebelum KG2" di 01, 22, dan 26 tetap benar setelah tabel 26 dibetulkan ([§23](#23-akibat-ke-dokumen-lain)).

| Bagian | Harus siap |
| --- | --- |
| §5 persetujuan, §11 masa simpan, §12 IP berhash | Sebelum akun anak pertama kali dipakai |
| §6 penanda tindak lanjut, §5.4 T11/T12 | Sebelum P5 pertama yang memakai akun (P5 uji coba), bersama modul no. 22 |
| §7 hapus, §8 salinan, §9 pembetulan | Sebelum akun anak pertama kali dipakai; kalau belum siap, jalan manual §13 diuji sekali di instalasi uji |
| §10 penutupan studi | Sebelum studi ditutup; boleh dibangun setelah data utama |

---

## 5. Persetujuan di sistem

### 5.1 Centang persetujuan anak (T4)

Tiga kunci baru di `app/Language/id/Game.php` dan `en/Game.php`, berisi usulan [23 → T4](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#t4-usulan-teks-centang-persetujuan-di-halaman-daftar) apa adanya ([§17](#17-teks-layar-dan-panel)):

| Kunci | Dipakai saat |
| --- | --- |
| `assentLabelStudy`, `assentNameStudy`, `assentRequiredStudy` | Studi aktif `field_mode = 1` (28 §5.1) |
| `assentLabel`, `assentName`, `assentRequired` (tetap) | Di luar mode penelitian (instalasi pengembang, pemakaian sekolah setelah studi) |

- `RegisterController` memilih kunci dari `field_mode` studi aktif, di tampilan (`game/register/step-3.php:81-86`) dan di pesan validasi (`RegisterController.php:294`, `357-359`).
- Kolom baru `participants.assent_version` VARCHAR(20), diisi bersama `assented_at`: `t4-1` untuk teks penelitian, `umum-1` untuk teks lama. Bila teks T4 berubah (misalnya atas permintaan komite), versinya naik, sehingga daftar izin berkode menunjukkan teks mana yang dicentang anak.
- Anak yang tidak mencentang tidak bisa mendaftar, sama dengan sekarang. Itu sesuai 22: anak yang tidak mau ikut tidak punya akun dan ikut kegiatan pengganti.

### 5.2 Izin berkode per siswa

**Halaman** `/admin/sesi-lapangan/{id}/izin` (kemampuan `field_sessions.manage`), di sebelah halaman kursi bagian 2. Satu baris per siswa rombel sesi itu, kode saja. P1 mengisinya di P1 dari L7, langsung setelah pendaftaran, bersama baris dan kursi.

| Isian | Nilai | Catatan |
| --- | --- | --- |
| Tanggal izin orang tua | Tanggal | Wajib saat pertama disimpan; dari kolom *Izin orang tua (tanggal)* L7 |
| Izin rekaman suara | Ya / Tidak | Bawaan Tidak (K6) |
| Izin butir risiko daring | Ya / Tidak | Bawaan Tidak (K6) |
| Status | Aktif · Keluar · Mengundurkan diri (data tetap) · Mengundurkan diri (hapus data) | `Tanpa izin` tidak pernah ada di sistem, karena anak tanpa izin tidak punya akun |
| Tanggal status | Tanggal | Wajib untuk status selain Aktif |

**Baris dibuat saat mendaftar.** Dalam mode penelitian, `SessionService::registerAndStart()` (28 §6.5) juga menyisipkan satu baris `participant_consents` berisi bawaan, di transaksi yang sama. Siswa yang dibuat `gelita:uji:akun` (28 §13) mendapat izin butir risiko Ya, supaya validator bisa melihat T11 di instalasi uji.

**Aturan:**
- Izin yang diubah dari Ya ke Tidak wajib bertanggal; tanggalnya disimpan sebagai tanggal ditarik (`*_withdrawn_on`).
- Tanggal tidak boleh di masa depan atau sebelum tanggal izin orang tua.
- Setiap perubahan setelah simpan pertama dicatat `consent_updated` dengan metadata `{participant_code, field, from, to, on}`.
- Simpan memakai UPDATE bersyarat pada `updated_at`. Yang kalah mendapat pesan "Data izin ini baru saja diubah orang lain. Muat ulang halaman, lalu periksa lagi."
- Halaman menampilkan peringatan "Izin belum diisi untuk {n} siswa" sampai semua baris punya tanggal izin orang tua. Peringatan yang sama tampil di panel pemantauan bagian 2, sebagai angka, juga untuk admin ([28 → Pertanyaan terbuka](28_IMPLEMENTASI_SESI_LAPANGAN.md#21-pertanyaan-terbuka) no. 7).
- Isian izin tidak terlihat admin (admin tidak memegang `field_sessions.manage`).

**Kode yang disimpan** (baru, bahasa Inggris; label panel berbahasa Indonesia):

| Kode `status` | Label | Dari L7 |
| --- | --- | --- |
| `active` | Aktif | `Aktif` |
| `left` | Keluar | `Keluar` |
| `withdrawn_keep` | Mengundurkan diri (data tetap) | Pilihan A |
| `withdrawn_delete` | Mengundurkan diri (hapus data) | Pilihan B |

### 5.3 Status mundur dan akun nonaktif

**Saat status `withdrawn_keep` atau `withdrawn_delete` disimpan** (K5):
1. `participants.deactivated_at` diisi (bila masih kosong), di transaksi yang sama dengan perubahan status.
2. Log audit `participant_deactivated` dengan `{participant_code, via: "consent_status"}`.
3. `ParticipantCheck::checkParticipant()` (`app/Filters/ParticipantCheck.php`) menambah syarat `deactivated_at IS NULL`, sehingga sesi siswa berakhir pada permintaan berikutnya, termasuk di tengah tantangan. Pengerjaan yang terputus ditandai `abandoned` oleh retensi seperti biasa.
4. `SessionService::login()` mengembalikan status `inactive` dengan pesan `Game.loginInactive`. Pemeriksaan token (28 §6.4) juga menolak siswa nonaktif.
5. Event yang masih antre di perangkat ditolak `/api/events` karena sesinya sudah berakhir; data sesudah tanggal mundur memang tidak dianalisis (`consent_ok` di `kamus-1.1`).

**Siapa memakai jalur ini:**
- pilihan A dari orang tua, atau anak yang menyatakan sendiri ingin berhenti: P1 mengubah status hari itu;
- pilihan B: P1 mengubah status, lalu mengajukan permintaan hapus ([§7](#7-permintaan-hapus-data)); formulir permintaan B mengubah status sendiri bila belum berubah.

Kotak "akun dinonaktifkan tanggal" di T10 diisi dari tanggal simpan ini.

**Status `left`** (pindah sekolah, tidak lagi hadir) tidak menonaktifkan akun, karena datanya tetap dipakai dan tidak ada permintaan apa pun.

**Mengaktifkan lagi** hanya untuk salah catat. Admin membuka halaman siswa, menekan *Aktifkan lagi*, dan mengisi alasan; log audit `participant_reactivated`. P1 mengembalikan status lebih dulu; tanpa itu tombolnya tidak tersedia. Mengembalikan status tidak mengaktifkan akun sendiri, supaya anak yang mundur tidak terbuka lagi karena salah klik.

### 5.4 Layar T11 dan T12

Layarnya bagian modul tes (no. 22, tampilan Bootstrap). Bagian ini menetapkan aturannya:

| Aturan | Isi |
| --- | --- |
| Siapa melihat T11 | Siswa dengan `risk_items_consent = 1` dan status `active`, dibaca server saat modul sampai di titik itu (setelah tes akhir P5) |
| Pilihan di T11 | *Ya, aku mau mengisi* → ang-21–ang-23, lalu T12; *Tidak, lewati saja* → langsung T12 |
| Siswa lain | Langsung T12 di titik yang sama, termasuk siswa tanpa izin dan yang izinnya belum diisi |
| Yang disimpan | Pilihan T11 sebagai `risk_reconsent` (1 atau 0) dengan waktu, di penyimpanan jawaban modul (no. 22). Bukan bagian kamus `kamus-1.0`; masuk `kamus-1.1` |
| Yang tidak boleh terlihat | P1–P3 tidak melihat siapa yang melihat T11, apa pilihannya, atau apakah siswa mengisi butir risiko. Pemantauan bagian 2 hanya menunjukkan "modul selesai" |
| Tampilan | Kedua tombol T11 sama besar dan sama warna (23 → T11); T12 satu tombol *Lanjut* |
| Teks | id dari 23 → T11 dan T12 apa adanya; en diusulkan di [§17](#17-teks-layar-dan-panel) |

---

## 6. Penanda tindak lanjut butir risiko daring

**Aturan penanda** (`RiskFollowUpService::RULE_VERSION = 'tl-1'`, dari [22 → Prosedur rujukan](22_PERMOHONAN_IZIN_ETIK.md#prosedur-rujukan-butir-risiko-daring) langkah 2 dan [16 → Etika](16_ANGKET_AKSES_DIGITAL.md#etika) no. 8):

| Penanda | Syarat |
| --- | --- |
| Ditandai | ang-21 = *Pernah, sekali atau dua kali* atau *Beberapa kali atau lebih* |
| Didahulukan | ang-21 = *Beberapa kali atau lebih*, atau ang-23 memuat *tidak bercerita kepada siapa pun* |

Kode pilihannya mengikuti kode jawaban di 16 dan modul no. 22. Bila Ahli Literasi Digital atau komite menilai penanda terlalu lebar, aturan dan versinya diubah setelah keputusan pengembang (22).

**Halaman** `/admin/tindak-lanjut` (kemampuan `risk_flags.view`, hanya `ketua`):
- dikelompokkan per sesi lapangan P5 (dan susulannya), terbaru di atas;
- setiap baris: kode peserta, *Didahulukan* (Ya atau kosong), jam butir terakhir dijawab (WIB); jawaban itu sendiri tidak ditampilkan;
- di atas daftar: "Daftar ini hanya untuk prosedur rujukan. Catatan tindak lanjut ditulis di berkas terpisah, tidak di sini.";
- `Cache-Control: no-store`, tanpa tombol unduh atau cetak;
- setelah studi ditutup, halaman hanya berisi "Studi sudah ditutup. Penanda tidak ditampilkan lagi." (26 → A7: penanda sampai studi ditutup).

**Ringkasan untuk `ketua`.** Satu kartu di Ringkasan: "Penanda tindak lanjut dari P5 hari ini: {n}", dengan tautan ke halaman. Peran lain tidak mendapat kartu ini.

**Yang tidak dilakukan** (K7):
- penanda tidak disimpan, jadi tidak ada yang bisa bocor atau terekspor selain jawabannya sendiri;
- tidak ada isian "sudah dihubungi" atau catatan, karena catatan rujukan disimpan `ketua` di luar sistem (22 langkah 6);
- log audit `risk_flags_viewed` hanya memuat `{field_session_code, count}`, tanpa kode peserta, karena admin membaca log audit dan memegang nama.

**Sumber data.** `RiskFollowUpService::flags(int $studyId, ?int $fieldSessionId)` membaca jawaban ang-21 dan ang-23 dari penyimpanan modul no. 22 (titik sambung). Siswa nonaktif tetap ikut dihitung bila jawabannya ada, karena keselamatan anak tidak bergantung pada status mundur. Jawaban yang sudah dihapus lewat permintaan C tidak menghasilkan penanda lagi.

---

## 7. Permintaan hapus data

### 7.1 Alur dan status

```text
peneliti mengajukan ──► requested ──(ketua konfirmasi)──► preview ──(admin HAPUS)──► executed ──(semua centang)──► completed
ketua mengajukan ─────────────────────────────────────────► preview
                       requested / preview ──(batal atau tolak)──► cancelled
```

| Langkah | Siapa | Rute | Yang terjadi |
| --- | --- | --- | --- |
| 1. Ajukan | `peneliti` atau `ketua` (`deletion.request`) | `/admin/permintaan-data/baru` | Isi kode peserta, jenis ([§7.2](#72-jenis-permintaan)), waktu permintaan diterima, lewat apa (kertas, WA, surel, lisan), dan centang "identitas pemohon cocok dengan formulir izin T2" dengan waktunya. Setelah kode diketik, formulir menampilkan kode sekolah, rombel, dan status untuk dicocokkan, tanpa nama. Status izin ikut berubah ([§5.3](#53-status-mundur-dan-akun-nonaktif)) |
| 2. Konfirmasi | `ketua` (`deletion.confirm`) | `/admin/permintaan-data` | Hanya untuk permintaan dari `peneliti`. `ketua` mencocokkan kode dengan L7, lalu *Konfirmasi* atau *Tolak* dengan alasan (misalnya kode salah) |
| 3. Pratinjau | Sistem | — | Saat dikonfirmasi (atau diajukan `ketua`), `RetentionService::preview()` menghitung baris per tabel dengan mode `hard` |
| 4. Jalankan | Admin (`deletion.execute`) | `/admin/tata-kelola` | Permintaan tampil dengan label "Permintaan orang tua", kode peserta, jumlah baris, dan batas waktunya. Admin mengetik `HAPUS`. Berkas unduhan di server ikut dihapus ([§7.4](#74-unduhan-di-server-dan-salinan-peneliti)) |
| 5. Centang | `ketua`, admin, `peneliti` | `/admin/permintaan-data/{id}` | Daftar periksa ([§7.6](#76-daftar-periksa-dan-berita-acara)) |
| 6. Selesai | Sistem | — | Semua centang jenis itu terisi, termasuk "orang tua diberi tahu" → `completed` |

**Perpindahan status** memakai UPDATE bersyarat (`… WHERE id = ? AND status = ?`), termasuk `execute()`, yang sekarang memeriksa status setelah `find()` (`RetentionService.php:176-187`). Dua admin yang menekan *Jalankan* bersamaan: hanya satu yang berhasil, yang lain mendapat pesan "Permintaan ini sudah dijalankan."

**Siapa melihat apa** di `/admin/permintaan-data`:
- `peneliti`: permintaan yang diajukannya sendiri, dengan status dan centang yang menjadi tugasnya;
- `ketua`: semua permintaan;
- admin: semua permintaan, dengan kode peserta. Admin juga melihat nama di halaman akun, jadi admin bisa memastikan anak yang benar sebelum menjalankan, tanpa menulis nama ke permintaan.

**Pilihan A dan D bukan permintaan hapus.** Pilihan A cukup perubahan status ([§5.3](#53-status-mundur-dan-akun-nonaktif)). Pilihan D memakai [§8](#8-salinan-data-satu-anak) dan [§9](#9-pembetulan-data-pendaftaran). Tanggalnya dicatat di kotak peneliti T10 dan di log audit.

### 7.2 Jenis permintaan

| Jenis (`kind`) | Dari T10 | Status izin yang ikut berubah | Yang dihapus sistem | Di luar sistem (centang) |
| --- | --- | --- | --- | --- |
| `all` | B | Status `withdrawn_delete`, akun nonaktif | Semua data anak ([§7.3](#73-yang-dihapus-sistem)) | Rekaman dan transkrip L5, baris berkode di lembar kertas L2–L6, salinan peneliti, cadangan |
| `risk_items` | C, butir risiko | Izin butir risiko Tidak, dengan tanggal ditarik | Jawaban ang-21–ang-23 dan `risk_reconsent` anak itu | Salinan peneliti, cadangan |
| `recording` | C, rekaman | Izin rekaman Tidak, dengan tanggal ditarik | Tidak ada; rekaman tidak pernah di server | Rekaman yang sudah ada |

- Bila anak belum punya jawaban butir risiko (misalnya izin ditarik sebelum P5), formulir hanya mengubah izinnya dan berkata "Belum ada jawaban yang perlu dihapus. Izinnya sudah diubah." Tidak ada permintaan yang dibuat.
- Permintaan `recording` dari `peneliti` tetap dikonfirmasi `ketua`, lalu langsung `executed` dengan nol baris, karena tidak ada yang dijalankan admin.
- Transkrip pada pilihan C rekaman: lihat [§21](#21-pertanyaan-terbuka) no. 2.

### 7.3 Yang dihapus sistem

`RetentionService::HARD_ORDER` (`RetentionService.php:24-28`) dan `scopedBuilder()` (baris 564–604) mendapat tabel dari bagian 2–4 dan perubahan lain. Cakupan baru `['participant_id' => …, 'kind' => 'risk_items']` di `normalizeScope()`.

| Tabel | Cakupan `all` | Cakupan `risk_items` | Asal tabel |
| --- | --- | --- | --- |
| `game_event_logs`, `audio_usage_events`, `item_tries`, `item_responses`, `challenge_attempts`, `device_logs`, `participant_feedback`, `game_sessions` | Ya | — | Sudah ada |
| `participant_progress`, `participant_seats`, `field_session_members` | Ya | — | Bagian 2 (menggantikan `session_progress`) |
| `field_sheet_rows` (per `participant_id`) | Ya | — | Bagian 3 |
| `participant_consents` | Ya (ikut `ON DELETE CASCADE`) | — | Bagian 4 |
| Jawaban modul tes, angket, dan skala sikap | Ya | Hanya ang-21–ang-23 dan `risk_reconsent` | No. 21, 22 (titik sambung) |
| `participants` | Ya, terakhir | — | Sudah ada |

**Yang tetap:**
- `data_deletion_requests` menyimpan `participant_code` di kolomnya sendiri sebagai bukti untuk berita acara;
- `audit_logs` tetap, karena itu catatan tindakan staf dan metadatanya hanya memuat kode.

**Kunci asing ke `participants`** dari bagian 2–4 (`participant_seats`, `participant_progress`, `field_session_members`, `field_sheet_rows`, `participant_consents`) memakai `ON DELETE CASCADE`, tetapi `HARD_ORDER` tetap menghapusnya satu per satu, supaya jumlah barisnya masuk pratinjau dan berita acara. Ini menjawab "kunci asing ke `participants` ikut aturan hapus bagian 4" di 28 §14 dan 29 §12.

### 7.4 Unduhan di server dan salinan peneliti

**Berkas unduhan di server.** Saat permintaan `all` atau `risk_items` dijalankan, `ExportService::purgeForRequest(int $requestId, string $since)` menghapus berkas semua unduhan yang masih ada (`file_path` tidak kosong) dan dibuat sejak `participants.created_at` anak itu. `file_path` menjadi kosong dan `data_exports.purged_by_request_id` diisi; log audit `export_purge` dengan id permintaan. Unduhan berumur paling lama 7 hari, jadi jumlahnya kecil. `ketua` membuat unduhan baru bila masih perlu.

**Salinan di perangkat tim.** Halaman permintaan menampilkan unduhan `ketua` sejak anak itu mendaftar yang pernah diunduh dan berisi siswa (`participant_count > 0`, riwayat unduhan 29 §9), dengan tanggal dan formatnya. `ketua` menghapus salinan itu dari penyimpanan terenkripsi atau menggantinya dengan unduhan baru, lalu mencentang `researcher_copies`.

### 7.5 Salinan cadangan

Cadangan dibuat skrip sendiri ([../08_DEPLOYMENT.md → Backup](../08_DEPLOYMENT.md#backup)): 30 hari di lokasi pertama, lalu disalin ke lokasi kedua yang **hanya menambah berkas**, sehingga lokasi kedua menyimpan setiap cadangan tanpa batas. Data anak yang sudah dihapus tetap ada di sana, dan nama tetap ada setelah studi ditutup.

**Penggantian cadangan** (K3), setelah permintaan dijalankan dan sebelum orang tua diberi tahu:
1. Admin menjalankan `backup.sh --ganti` (Jalur L) atau `backup.ps1 -Ganti` (Jalur W).
2. Skrip membuat cadangan baru seperti biasa dan mengujinya (`gzip -t` atau uji zip).
3. Bila uji lolos, skrip menghapus semua berkas cadangan yang lebih lama di **kedua lokasi**, lalu mencetak jumlah yang dihapus dan stempel waktu yang tersisa.
4. Bila lokasi kedua tidak bisa dijangkau, skrip berhenti dengan kode 2 tanpa menghapus apa pun di lokasi pertama. Admin mengulang setelah lokasi kedua tersambung.
5. Bila penyedia server juga membuat snapshot atau cadangan sendiri, admin menghapusnya lewat panel penyedia atau memastikan fitur itu tidak dipakai. Perjanjian prosesor (26 → P6) memuat kewajiban ini.
6. Admin mencentang `backups` dengan tanggal.

**Lokasi kedua dibatasi 30 hari**, sama dengan lokasi pertama (`find "$KEDUA" -mtime +30 -delete`; padanannya di `backup.ps1`). Lokasi kedua tetap tidak ikut terhapus bila lokasi pertama kosong, karena batas itu dihitung dari umur berkasnya sendiri.

**Akibatnya:** setiap penggantian membuang titik pulih yang lebih lama. Permintaan hapus jarang (beberapa per studi), jadi ini diterima. Penggantian dijalankan di luar jam sesi, tetapi tetap di dalam 3 × 24 jam sejak penghapusan.

### 7.6 Daftar periksa dan berita acara

**Daftar periksa** di `data_deletion_requests.checklist_json`; setiap butir menyimpan tanggal dan id staf yang mencentang.

| Butir | `all` | `risk_items` | `recording` | Dicentang oleh |
| --- | --- | --- | --- | --- |
| `system`: data di sistem dihapus | ✓ | ✓ | — | Otomatis saat dijalankan |
| `server_exports`: unduhan di server dihapus | ✓ | ✓ | — | Otomatis |
| `researcher_copies`: salinan di perangkat tim | ✓ | ✓ | — | `ketua` |
| `backups`: cadangan diganti | ✓ | ✓ | — | Admin |
| `recordings`: rekaman (dan transkrip untuk `all`) | ✓ | — | ✓ | `peneliti` atau `ketua` |
| `paper_sheets`: baris berkode di lembar kertas L2–L6 dihitamkan | ✓ | — | — | `peneliti` |
| `parent_notified`: orang tua diberi tahu | ✓ | ✓ | ✓ | `ketua`, setelah semua butir lain |

`parent_notified` baru bisa dicentang setelah butir lain jenis itu lengkap, karena 22 meminta penyedia server dan salinan dibereskan sebelum orang tua diberi tahu.

**Berita acara** (`/admin/permintaan-data/{id}/berita-acara`, PDF lewat mPDF seperti `ReportService`):
- isi: nomor permintaan, kode peserta, jenis, waktu diterima, waktu identitas dicocokkan, siapa mengajukan dan mengonfirmasi (nama akun peran, misalnya `koordinator`), waktu dijalankan, jumlah baris per tabel, setiap butir daftar periksa dengan tanggalnya, waktu orang tua diberi tahu;
- tanpa nama anak atau orang tua; dua baris tanda tangan (`ketua` dan admin);
- dicetak atau disimpan `ketua` di catatannya (PP 33/2026 Pasal 86), dan dimusnahkan bersama L7 saat studi ditutup.

### 7.7 Batas waktu di panel

Semua batas dihitung 3 × 24 jam kalender (22 → *Dasar hukum*, satuan waktu) dan ditampilkan dalam WIB.

| Batas | Dihitung dari | Tampil di |
| --- | --- | --- |
| Pengumpulan data dihentikan | `received_at` | Terpenuhi saat status disimpan (§5.3); halaman permintaan menampilkan jamnya |
| Orang tua diberi tahu | `executed_at` | "Sisa waktu memberi tahu orang tua: {jam} jam"; kuning di bawah 24 jam, merah bila lewat |
| Menunggu konfirmasi atau dijalankan | `received_at` | "Diterima {jam} jam yang lalu" |

Kartu Ringkasan: admin melihat "Permintaan hapus menunggu dijalankan: {n}"; `ketua` melihat "menunggu konfirmasi: {n}" dan "menunggu centang: {n}". Tidak ada surel otomatis.

---

## 8. Salinan data satu anak

Untuk 23 → T10 pilihan D (melihat atau meminta salinan). `ketua` membuka kode lewat L7, memeriksa identitas pemohon, lalu membuat salinan.

**Halaman** `/admin/peserta/salinan` (kemampuan `research.export`, hanya `ketua`):
- isian: kode peserta, keperluan (*Melihat* atau *Salinan*);
- centang "Sertakan jawaban pertanyaan tentang pengalaman tidak nyaman di internet. Anak sudah setuju." Centang ini hanya muncul bila anak punya jawaban ang-21–ang-23, dan bawaannya kosong (K2);
- hasil: satu ZIP `salinan-{kode}-{tanggal}.zip`, langsung diunduh, tidak disimpan di server, `Cache-Control: no-store`.

**Isi ZIP:**

| Berkas | Isi |
| --- | --- |
| PDF ringkas | Ringkasan dengan kalimat biasa untuk orang tua: data pendaftaran (umur, jenis kelamin, kelas, kode sekolah, rombel), tantangan yang selesai per wilayah, bintang, lama bermain, bagian tes dan angket yang dikerjakan, dan kalimat tentang bagian yang tidak disertakan. Dibuat dari `ReportService::participantData()` (`ReportService.php:55-77`) |
| XLSX data | Lembar yang sama dengan unduhan `ketua` (29 §7.2), disaring ke anak ini: sesi, pengerjaan, jawaban, percobaan, bantuan, event bertanda Peneliti, tes, angket ang-1–ang-20, skala sikap, kritik dan saran |

**Tidak disertakan:**
- data anak lain, id internal, data staf, rincian perangkat, `ip_hash`;
- kunci jawaban (`answer_key`) dan teks soal, supaya bank dan tes tetap bisa dipakai (22: "bukan kunci jawaban soal"); kolom benar/salah tetap ada;
- jawaban ang-21–ang-23 dan `risk_reconsent`, kecuali centang anak setuju diisi;
- penanda tindak lanjut, karena tidak disimpan (K7).

Kalimat di PDF bila jawaban butir risiko tidak disertakan: "Jawaban anak untuk beberapa pertanyaan tentang pengalaman tidak nyaman di internet tidak disertakan. Sebelum menjawab, anak diberi tahu bahwa orang tua tidak melihat jawaban itu. Bapak/Ibu bisa membicarakannya dengan ketua peneliti."

**Log audit** `participant_copy` dengan `{participant_code, purpose, include_risk}`. Batas jawabnya (3 × 24 jam) dicatat `ketua` di kotak peneliti T10.

---

## 9. Pembetulan data pendaftaran

**Tempat.** Halaman siswa milik admin (`/admin/peserta/{id}`, kartu akun) mendapat formulir *Betulkan data pendaftaran* (kemampuan `participants.accounts`). Admin menjalankannya atas permintaan `ketua`, yang memberi kode peserta.

| Bisa dibetulkan | Tidak bisa |
| --- | --- |
| `age`, `gender`, `class_level` | `participant_code`, sekolah, `rombel` (28 §10.2), nama pengguna, metrik kata sandi |

**Aturan:**
- Umur dan jenis kelamin memakai aturan validasi pendaftaran yang sama (`RegisterController.php:239-240`). Kelas tidak diisi anak saat mendaftar (diambil dari `Config\Gelita::$registrationClass`, sekarang `5`), jadi isiannya angka 1–6.
- Alasan wajib, singkat, tanpa nama, misalnya "permintaan orang tua, T10 18 Nov 2027".
- Simpan memakai UPDATE bersyarat pada `updated_at`.
- Log audit `participant_corrected` dengan `{participant_code, changes: {field: [lama, baru]}, reason}`.
- `participants.corrected_at` diisi. Unduhan `ketua` mendapat kolom `registration_corrected` (0/1) lewat `kamus-1.1` (titik sambung bagian 3), supaya analisis tahu nilai mana yang pernah dibetulkan.

---

## 10. Penutupan studi

### 10.1 Daftar izin berkode

**Unduhan** `/admin/ekspor/izin` (kemampuan `research.export`, hanya `ketua`): XLSX `daftar-izin-{kode studi}-{tanggal}.xlsx`, satu baris per siswa yang punya baris izin di studi itu.

| Kolom | Isi |
| --- | --- |
| `participant_code` | Kode peserta (rombel hanya sebagai bagian kode) |
| `parent_consent_on` | Tanggal izin orang tua |
| `recording_consent`, `recording_withdrawn_on` | Ya/Tidak; tanggal ditarik bila ada |
| `risk_items_consent`, `risk_items_withdrawn_on` | Ya/Tidak; tanggal ditarik bila ada |
| `assented_at`, `assent_version` | Waktu centang persetujuan anak dan versi teksnya |
| `status`, `status_on` | Status dan tanggalnya |

- Tanpa nama, nama sekolah, NPSN, atau nama pengguna.
- Dicatat di `data_exports` (`scope_json.kind = "consent_register"`) dan log audit; `research_studies.consent_register_exported_at` diisi.
- Sebelum L7 dimusnahkan, `ketua` mencocokkan setiap baris dengan L7. Selisih dibetulkan dulu di halaman izin, lalu daftar diunduh lagi.
- Disimpan bersama data berkode selama data itu disimpan (22 → Masa simpan, PP 33/2026 Pasal 36).
- Lembar yang sama dipakai untuk menghitung `consent_ok` di lapis 3 (`kamus-1.1`), dengan L7 sebagai pembanding sampai studi ditutup.

Anak yang datanya dihapus (pilihan B) tidak ada di daftar, karena baris izinnya ikut terhapus; buktinya permintaan hapus dan berita acaranya.

### 10.2 Nama dan sekolah diganti kode

**Halaman** `/admin/studi/{id}/penutupan` (dilihat admin dan `ketua`; dijalankan admin, kemampuan baru `identities.replace`).

**Syarat**, ditampilkan sebagai daftar centang otomatis. Tombol *Hitung* baru aktif bila semuanya terpenuhi:
1. Studi berstatus `closed` (diubah `ketua`); `research_studies.closed_at` terisi.
2. Tidak ada permintaan hapus studi itu yang belum `completed` atau `cancelled`.
3. Daftar izin berkode sudah diunduh setelah perubahan izin terakhir (`consent_register_exported_at` ≥ `participant_consents.updated_at` terbaru).

**Dua langkah**, seperti penghapusan: *Hitung* menampilkan jumlah akun dan sekolah yang akan diubah; admin mengetik `GANTI`; `StudyClosureService::replaceIdentities(int $studyId)` berjalan dalam satu transaksi.

| Yang diubah | Menjadi |
| --- | --- |
| `participants.display_name` | Kode peserta |
| `participants.username` | Kode peserta, huruf kecil (tetap unik karena kodenya unik) |
| `participants.password_hash` | Hash acak yang tidak bisa dipakai masuk; `deactivated_at` diisi |
| `participants.school_name_snapshot` | Kode sekolah penelitian |
| Sekolah bernama (`schools`) | Untuk setiap kode sekolah penelitian di studi itu: satu baris sekolah baru bernama kode itu (`S03`), tanpa NPSN dan alamat. `research_code` pindah ke baris baru. `participants.school_id`, `field_sessions.school_id`, dan `participant_code_counters.school_id` dipindah ke baris baru (K11). Baris sekolah asli tetap di direktori tanpa `research_code` |

- Cakupannya siswa yang punya baris `participant_consents` di studi itu.
- Provinsi dan kabupaten di `participants` tetap, karena tidak cukup untuk mengenali sekolah ([§21](#21-pertanyaan-terbuka) no. 3).
- Log audit `study_identities_replaced` dengan `{study_code, participants, schools}`; `research_studies.identities_replaced_at` diisi.
- Setelah itu studi tidak bisa dibuka lagi. Permintaan hapus, salinan satu anak, dan pembetulan untuk studi itu ditolak dengan pesan "Studi sudah ditutup dan nama sudah diganti kode. Data tidak bisa lagi dihubungkan dengan anak."

**Sesudahnya, di luar sistem:** admin mengganti cadangan ([§7.5](#75-salinan-cadangan)), supaya nama juga hilang dari cadangan. `ketua` memusnahkan L7, formulir T2 dan T10, catatan rujukan, dan berita acara permintaan, dengan berita acara pemusnahan (22 → Masa simpan).

Anak yang ingin tetap bermain GELITA setelah studi memakai akun baru di luar mode penelitian (22 → Manfaat).

---

## 11. Masa simpan

| Bagian | Sekarang | Sesudah |
| --- | --- | --- |
| Bawaan `research_studies.retention_days` | 1825 (`2026-01-01-000300_CreateResearchStudies.php`, `ResearchStudySeeder`) | 7305 (K10); baris yang masih 1825 diubah di migrasi |
| Studi `field_mode = 1` | — | `StudyController` menolak `retention_days` < 7305 dengan pesan "Masa simpan studi penelitian paling singkat 7305 hari (20 tahun)." |
| Awal hitungan, studi `field_mode = 1` | Mulai sesi (`game_sessions.started_at`, `RetentionService.php:437-438`) | `research_studies.closed_at`. Studi yang belum ditutup tidak pernah mendapat pratinjau retensi |
| Awal hitungan, studi lain | Mulai sesi | Tetap |
| Cakupan pratinjau setelah masa simpan habis | Sesi studi itu, tanpa `participants` | Seluruh studi, termasuk `participants` dan `participant_consents` siswa studi itu (yang namanya sudah diganti kode) |

- `closed_at` diisi saat status berubah ke `closed` dan dikosongkan bila studi dibuka lagi. Membuka lagi hanya boleh sebelum §10.2.
- Retensi tetap hanya membuat pratinjau; menghapus tetap keputusan admin di Tata kelola (`RetentionService.php:422-428`).
- `docs/08_DEPLOYMENT.md` → *Tata kelola data anak* no. 2 diganti: masa simpan studi penelitian ditetapkan oleh izin etik, bukan titik awal.

---

## 12. IP berhash di sesi peramban

**Sekarang.** `Config\Session::$driver` = `DatabaseHandler` (`app/Config/Session.php:29`), yang menulis IP mentah ke `ci_sessions.ip_address`. `App\Libraries\HashedIpSessionHandler` sudah ada (turunan `MySQLiHandler`, IP di-hash `hash_ip()` di konstruktor) tetapi belum dipasang. Kolom `ip_address` VARCHAR(64) sudah cukup untuk SHA-256 (`2026-01-01-002900_CreateCiSessions.php`).

**Perubahan:**
1. `Config\Session::$driver = HashedIpSessionHandler::class`. CodeIgniter 4.7 hanya mengganti `DatabaseHandler` dengan turunan per platform; kelas lain dibuat langsung dengan `($config, $request->getIPAddress())` (`system/Config/Services.php`, `session()`). Penangan ini hanya untuk MySQL/MariaDB, sama dengan server produksi.
2. `matchIP` tetap `false`. Bila kelak dinyalakan, perbandingannya tetap benar karena nilai yang disimpan dan dibandingkan sama-sama hash.
3. Migrasi bagian ini mengosongkan `ci_sessions`, supaya IP mentah yang sudah tersimpan hilang. Semua pengguna masuk ulang, jadi migrasi dijalankan di luar jam sesi.
4. `RetentionService::run()` mendapat langkah `purgeExpiredBrowserSessions()`: hapus baris `ci_sessions` yang `timestamp`-nya lebih tua dari `Config\Session::$expiration` (14400 detik). Batasnya dihitung di PHP, supaya kueri yang sama berjalan di SQLite (tes). Jumlahnya masuk ringkasan `retention_run` sebagai `expired_browser_sessions`.

**Mengapa langkah 4 perlu.** CodeIgniter hanya menyetel `session.gc_maxlifetime` (`system/Session/Session.php:172`); kapan pembersih sesi berjalan diatur `session.gc_probability` di php.ini. PHP paket Debian/Ubuntu menyetelnya 0 (terlihat juga di mesin pengembangan cloud, 30 Oktober 2026), jadi di Jalur L baris sesi lama bisa tidak pernah terhapus. Periksa sekali di server: `php -i | grep gc_probability`.

**Yang tidak berubah:** cadangan tetap tanpa isi `ci_sessions`; `ip_hash` di tabel lain tetap.

---

## 13. Jalan manual sementara

Untuk [26 → P15](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md#langkah-perlindungan): dipakai hanya bila §7–§9 belum selesai saat akun anak pertama kali akan dipakai. Jalan ini diuji sekali di instalasi uji dengan akun `uji.*`, dan hasil ujinya dicatat di 04.

| Kebutuhan | Jalan manual | Siapa |
| --- | --- | --- |
| Hapus semua data (B) | Sudah ada: Tata kelola, cakupan nomor siswa, mode `hard` | Admin, atas perintah `ketua` |
| Hapus jawaban butir risiko (C) | `DELETE` baris ang-21–ang-23 anak itu di tabel jawaban modul (no. 22) dengan id peserta, setelah `SELECT COUNT(*)` yang sama dicatat | Admin, atas perintah `ketua` |
| Salinan satu anak (D) | `SELECT` per tabel dengan id peserta, disimpan sebagai CSV di penyimpanan terenkripsi admin; `ketua` menyusun salinan tanpa kunci jawaban dan, kecuali anak setuju, tanpa jawaban butir risiko | Admin dan `ketua` |
| Pembetulan (D) | `UPDATE participants SET {kolom} = … WHERE participant_code = …` | Admin, atas perintah `ketua` |
| Cadangan | Cadangan baru, lalu berkas lama di kedua lokasi dihapus dengan tangan | Admin |

Setiap jalan manual dicatat dalam berita acara di catatan `ketua`: tanggal, kode peserta, perintah yang dijalankan, jumlah baris, dan siapa yang menjalankan. Admin tidak menulis nama ke berita acara.

---

## 14. Hak akses dan menu

Dua kemampuan baru di peta [27 §5.1](27_IMPLEMENTASI_PERAN_PENELITI.md#51-peta-kemampuan):

```php
'deletion.confirm'   => ['ketua'],
'identities.replace' => ['admin'],
```

| Halaman atau tindakan | Admin | Peneliti | Ketua | Kemampuan |
| --- | --- | --- | --- | --- |
| Izin berkode per siswa | — | Ya | Ya | `field_sessions.manage` |
| Mengajukan permintaan hapus | — | Ya | Ya | `deletion.request` |
| Daftar permintaan | Semua | Miliknya | Semua | `deletion.request`, `deletion.confirm`, `deletion.execute` |
| Konfirmasi atau tolak permintaan | — | — | Ya | `deletion.confirm` |
| Menjalankan permintaan | Ya | — | — | `deletion.execute` |
| Centang `backups` | Ya | — | — | `deletion.execute` |
| Centang `researcher_copies`, `parent_notified` | — | — | Ya | `deletion.confirm` |
| Centang `recordings`, `paper_sheets` | — | Ya | Ya | `deletion.request` |
| Berita acara | Ya | — | Ya | `deletion.execute`, `deletion.confirm` |
| Penanda tindak lanjut | — | — | Ya | `risk_flags.view` |
| Salinan satu anak | — | — | Ya | `research.export` |
| Daftar izin berkode | — | — | Ya | `research.export` |
| Pembetulan, aktifkan lagi | Ya | — | — | `participants.accounts` |
| Halaman penutupan studi | Ya (menjalankan) | — | Lihat | `identities.replace`, `studies.view` |

**Menu.** "Permintaan data" di kelompok Data untuk ketiga peran; "Tindak lanjut" untuk `ketua`. Izin berkode tidak punya menu sendiri; tautannya di halaman sesi lapangan. Salinan satu anak ada di halaman siswa `ketua` dan di menu "Permintaan data".

---

## 15. Skema database

**Migrasi `2026-01-01-003800_AddConsentAndDataRights`.** Nomor sesudah bagian 3 (`003700`) dan migrasi terakhir bagian 1–4; urutan lengkapnya ada di [27 → Migrasi](27_IMPLEMENTASI_PERAN_PENELITI.md#61-migrasi-2026-01-01-003400_replaceteacherwithresearcherroles).

| Tabel atau kolom | Isi |
| --- | --- |
| `participant_consents` (baru) | `participant_id` PK, FK `participants` `ON DELETE CASCADE`; `study_id` FK; `parent_consent_on` DATE (null); `recording_consent` TINYINT(1) bawaan 0; `recording_withdrawn_on` DATE (null); `risk_items_consent` TINYINT(1) bawaan 0; `risk_items_withdrawn_on` DATE (null); `status` VARCHAR(20) bawaan `active`; `status_on` DATE (null); `entered_by`, `updated_by` FK staf; `created_at`, `updated_at`. Indeks (`study_id`, `status`) |
| `participants` | Kolom baru `deactivated_at` DATETIME (null), `assent_version` VARCHAR(20) (null), `corrected_at` DATETIME (null) |
| `research_studies` | `retention_days` bawaan 7305 (baris 1825 diubah); kolom baru `closed_at`, `consent_register_exported_at`, `identities_replaced_at` DATETIME (null) |
| `data_deletion_requests` | Kolom baru `kind` VARCHAR(20) (null untuk permintaan admin dan retensi), `participant_code` VARCHAR(40) (null), `received_at`, `verified_at` DATETIME (null), `received_via` VARCHAR(10) (null: `paper`, `whatsapp`, `email`, `oral`), `confirmed_by` FK staf (null), `confirmed_at`, `completed_at` DATETIME (null), `checklist_json` JSON (null). Status baru `requested` dan `completed` di samping `preview`, `executed`, `cancelled` |
| `data_exports` | Kolom baru `purged_by_request_id` FK `data_deletion_requests` (null) |
| `ci_sessions` | Dikosongkan; struktur tetap |

Semua kunci asing ke `staff_users` memakai `ON DELETE SET NULL`. Kunci asing ke `participants` dari bagian 2–4 memakai `ON DELETE CASCADE` ([§7.3](#73-yang-dihapus-sistem)).

**`down()`** membuang tabel dan kolom baru dan mengembalikan bawaan `retention_days` ke 1825. Isi `ci_sessions` dan data yang terhapus tidak kembali; ini ditulis di komentar migrasi.

**Ikut diperbarui:**
- `tests/_support/Database/ResetTables.php` (skema SQLite).
- `SystemResetService`: `REQUIRED_MIGRATION` menjadi `2026-01-01-003800`, nilai akhir setelah bagian 1–4; `participant_consents` di kelompok A (dikosongkan).
- `RetentionService`: `HARD_ORDER`, `scopedBuilder()`, `normalizeScope()` (`kind`), `flagExpiredStudyData()`, `purgeExpiredBrowserSessions()`, perpindahan status bersyarat.
- `ResearchStudySeeder`: `retention_days` 7305.
- Model baru `ParticipantConsentModel`; `ParticipantModel`, `ResearchStudyModel`, `DataDeletionRequestModel`, `DataExportModel`: `allowedFields` dan aturan status.

**Layanan baru** di `app/Services/`, didaftarkan di `app/Config/Services.php`:
- `ConsentService`: izin per siswa, status, nonaktif, daftar izin berkode;
- `DataRequestService`: ajukan, konfirmasi, tolak, centang, batas waktu, berita acara (memanggil `RetentionService` untuk pratinjau dan eksekusi);
- `RiskFollowUpService`: penanda (§6);
- `ParticipantCopyService`: salinan satu anak (§8);
- `StudyClosureService`: syarat dan penggantian identitas (§10.2).

---

## 16. Rute, perintah, dan skrip

**Panel** (grup `staffAuth`, filter `staffCan` dari 27):

| Rute | Controller::method | Kemampuan |
| --- | --- | --- |
| `GET`, `POST /admin/sesi-lapangan/{id}/izin` | `ConsentController::edit`, `::save` | `field_sessions.manage` |
| `GET /admin/permintaan-data` | `DataRequestController::index` | `deletion.request`, `deletion.confirm`, `deletion.execute` |
| `GET`, `POST /admin/permintaan-data/baru` | `::create`, `::store` | `deletion.request` |
| `GET /admin/permintaan-data/{id}` | `::show` | sama dengan daftar, dibatasi milik sendiri untuk `peneliti` |
| `POST /admin/permintaan-data/{id}/konfirmasi`, `…/tolak` | `::confirm`, `::reject` | `deletion.confirm` |
| `POST /admin/permintaan-data/{id}/batal` | `::cancel` | `deletion.request` (pengaju, sebelum dikonfirmasi) |
| `POST /admin/permintaan-data/{id}/centang` | `::tick` | Per butir, [§14](#14-hak-akses-dan-menu) |
| `GET /admin/permintaan-data/{id}/berita-acara` | `::minutes` | `deletion.confirm`, `deletion.execute` |
| `POST /admin/tata-kelola/hapus/{id}/jalankan` | Tetap (`GovernanceController::deleteExecute`) | `deletion.execute` |
| `GET /admin/tindak-lanjut` | `RiskFollowUpController::index` | `risk_flags.view` |
| `GET`, `POST /admin/peserta/salinan` | `ParticipantCopyController::form`, `::download` | `research.export` |
| `POST /admin/peserta/{id}/betulkan`, `…/aktifkan` | `ParticipantController::correct`, `::reactivate` | `participants.accounts` |
| `GET /admin/ekspor/izin` | `ExportController::consentRegister` | `research.export` |
| `GET /admin/studi/{id}/penutupan` | `StudyClosureController::show` | `studies.view` |
| `POST /admin/studi/{id}/penutupan/hitung`, `…/jalankan` | `::preview`, `::run` | `identities.replace` |

**Game dan API.** Tidak ada rute baru. `/masuk` mendapat status `inactive`. Modul tes (no. 22) membaca izin dan menyimpan `risk_reconsent` lewat `ConsentService`.

**Perintah spark.** Tidak ada perintah baru. `gelita:retention:run` mendapat langkah §12.

**Skrip cadangan** ([§7.5](#75-salinan-cadangan)):

```text
bash deploy/linux/backup.sh --ganti        # fresh backup, verify, then delete older backups at both locations
powershell -File deploy\windows\backup.ps1 -Config … -Ganti
```

Kedua skrip juga menghapus berkas lebih dari 30 hari di lokasi kedua pada setiap jalan biasa.

---

## 17. Teks layar dan panel

Mengikuti `.claude/rules/ui-copy.md`. Teks game di `Game.php` (id dan en, `LanguageFilesTest`); teks modul di berkas bahasa modul no. 22 (id dan en); teks panel di `app/Language/id/Admin.php` (panel hanya berbahasa Indonesia).

**Game**

| Kunci | id | en |
| --- | --- | --- |
| `assentLabelStudy` | Aku mau ikut penelitian GELITA. Aku tahu jawabanku di permainan dan di soal dicatat dengan kodeku, bukan namaku. Aku boleh berhenti kapan saja. | I want to take part in the GELITA study. I know my answers in the game and the questions are recorded with my code, not my name. I can stop at any time. |
| `assentNameStudy` | Persetujuan ikut penelitian | Agreement to take part |
| `assentRequiredStudy` | Centang dulu kalau kamu mau ikut, ya. Kalau tidak mau, tidak apa-apa. Bilang pelan-pelan ke kakak peneliti. | Tick the box first if you want to take part. If you don't want to, that's fine. Quietly tell a researcher. |
| `loginInactive` | Akun ini sudah tidak dipakai lagi. Kalau ada pertanyaan, bicara dengan kakak peneliti, ya. | This account is no longer in use. If you have a question, talk to a researcher. |

**Modul tes: T11** (id dari 23 → T11 apa adanya; en usulan)

| Bagian | en (usulan) |
| --- | --- |
| Judul | A few questions about the internet |
| Paragraf 1 | There are a few more questions. They are about things on the internet or a phone that may have made you feel uncomfortable. |
| Paragraf 2 | You don't have to answer them. That's okay. Every question also has the choice *I don't want to answer*. |
| Paragraf 3 | Your answers are saved with your code, not your name. Your teachers, parents, and friends do not see your answers. |
| Paragraf 4 | But there is one thing you should know. The researchers want every child to be safe. If your answers show you might need help, a researcher may ask you for a short chat. If you are in danger, the researcher will ask a grown-up to help keep you safe. |
| Pertanyaan | Do you want to answer these questions? |
| Tombol | Yes, I'll answer · No, skip them |

**Modul tes: T12** (id dari 23 → T12 apa adanya; en usulan)

| Bagian | en (usulan) |
| --- | --- |
| Judul | Thank you. |
| Paragraf 1 | Sometimes things on the internet make us feel uncomfortable, scared, or sad. That is not your fault. |
| Paragraf 2 | If that happens, tell a grown-up you trust. For example, your parents, a teacher, or a researcher. |
| Paragraf 3 | The researchers are here until today's session ends. You can come to them at any time. |
| Tombol | Next |

Label pilihan *Tidak mau menjawab* di en mengikuti teks en butir ang-21–ang-23 yang dibuat di no. 22.

**Panel**

| Kunci | Usulan |
| --- | --- |
| `menuDataRequests` | Permintaan data |
| `menuRiskFollowUp` | Tindak lanjut |
| `consentTitle` | Izin per siswa |
| `consentHelp` | Isi dari L7. Hanya kode; jangan menulis nama. Izin rekaman dan pertanyaan internet tetap Tidak sampai diisi. |
| `consentParentDate`, `consentRecording`, `consentRiskItems`, `consentStatus`, `consentStatusDate` | Tanggal izin orang tua · Izin rekaman suara · Izin pertanyaan internet · Status · Tanggal status |
| `consentStatusActive`, `consentStatusLeft`, `consentStatusWithdrawnKeep`, `consentStatusWithdrawnDelete` | Aktif · Keluar · Mengundurkan diri (data tetap) · Mengundurkan diri (hapus data) |
| `consentMissing` | Izin belum diisi untuk {0} siswa. |
| `consentStale` | Data izin ini baru saja diubah orang lain. Muat ulang halaman, lalu periksa lagi. |
| `consentDeactivated` | Akun {0} dinonaktifkan. Data sampai tanggal itu tetap tersimpan. |
| `requestNew` | Ajukan permintaan hapus |
| `requestKindAll`, `requestKindRisk`, `requestKindRecording` | Hapus semua data (pilihan B) · Hapus jawaban pertanyaan internet (pilihan C) · Hapus rekaman (pilihan C) |
| `requestReceived` | Diterima tanggal dan jam |
| `requestVerified` | Identitas pemohon cocok dengan formulir izin T2 |
| `requestNothingToDelete` | Belum ada jawaban yang perlu dihapus. Izinnya sudah diubah. |
| `requestWaitConfirm` | Menunggu konfirmasi ketua peneliti. |
| `requestConfirmHelp` | Cocokkan kode ini dengan L7 sebelum mengonfirmasi. |
| `requestFromParents` | Permintaan orang tua |
| `requestNotifyLeft` | Sisa waktu memberi tahu orang tua: {0} jam |
| `requestNotifyLate` | Batas memberi tahu orang tua sudah lewat {0} jam. |
| `requestChecklistBlocked` | Centang ini baru bisa diisi setelah butir lain selesai. |
| `requestCopiesHelp` | Unduhan ini mungkin memuat data anak ini. Hapus salinannya di perangkat tim, atau ganti dengan unduhan baru. |
| `requestBackupsHelp` | Jalankan skrip cadangan dengan pilihan ganti, lalu centang setelah cadangan lama di kedua lokasi terhapus. |
| `riskIntro` | Daftar ini hanya untuk prosedur rujukan. Catatan tindak lanjut ditulis di berkas terpisah, tidak di sini. |
| `riskPriority` | Didahulukan |
| `riskStudyClosed` | Studi sudah ditutup. Penanda tidak ditampilkan lagi. |
| `copyIncludeRisk` | Sertakan jawaban pertanyaan tentang pengalaman tidak nyaman di internet. Anak sudah setuju. |
| `copyRiskLeftOut` | Jawaban anak untuk beberapa pertanyaan tentang pengalaman tidak nyaman di internet tidak disertakan. Sebelum menjawab, anak diberi tahu bahwa orang tua tidak melihat jawaban itu. Bapak/Ibu bisa membicarakannya dengan ketua peneliti. |
| `correctTitle` | Betulkan data pendaftaran |
| `correctReason` | Alasan (tanpa nama) |
| `reactivate` | Aktifkan lagi |
| `closureTitle` | Penutupan studi |
| `closureReplaced` | Studi sudah ditutup dan nama sudah diganti kode. Data tidak bisa lagi dihubungkan dengan anak. |
| `retentionMinStudy` | Masa simpan studi penelitian paling singkat 7305 hari (20 tahun). |

---

## 18. Titik sambung ke perubahan lain

| Perubahan | Yang disediakan bagian ini | Yang dirancang di sana |
| --- | --- | --- |
| No. 21 skala sikap | Cakupan hapus `all` mencakup tabel skala | Tabel jawaban skala |
| No. 22 modul tes | `ConsentService::riskItemsAllowed()`, aturan T11/T12 dan teksnya, `risk_reconsent`, cakupan hapus `risk_items`, `RiskFollowUpService` | Layar, tabel jawaban, kode pilihan ang-21–ang-23 |
| No. 9 lapis turunan | Daftar izin berkode, `status_on`, `registration_corrected` | `consent_ok`, data sampai tanggal mundur (`kamus-1.1`) |
| Bagian 2 (`28`) | Baris izin dibuat saat mendaftar; nonaktif ditolak token; `gelita:uji:akun` mengisi izin butir risiko Ya | Halaman kursi di sebelah halaman izin; angka "izin belum diisi" di pemantauan |
| Bagian 3 (`29`) | `purged_by_request_id`, salinan satu anak dari lembar unduhan | Lembar `izin` bila `ketua` membutuhkannya di unduhan utama ([§21](#21-pertanyaan-terbuka) no. 4) |

---

## 19. Pengujian dan kriteria selesai

### 19.1 Tes baru

| Tes | Yang diperiksa |
| --- | --- |
| `StudyAssentTextTest` | `field_mode = 1` memakai kunci `*Study` di halaman dan pesan validasi; `assent_version` `t4-1`; di luar mode penelitian teks lama dan `umum-1` |
| `ConsentPageTest` | P1 mengisi izin per kode; bawaan Tidak; tanggal wajib saat ditarik; audit `consent_updated`; dua simpan bersamaan, satu menang (pola `Factories::injectMock`); admin 404; tidak ada nama di halaman |
| `WithdrawalDeactivationTest` | Status mundur mengisi `deactivated_at` di transaksi yang sama; permintaan game berikutnya mengakhiri sesi; masuk ditolak `inactive`; token ditolak; mengembalikan status tidak mengaktifkan akun; admin mengaktifkan lagi dengan audit |
| `RiskItemsGateTest` | T11 hanya untuk izin Ya dan status aktif; *Tidak* dan siswa tanpa izin langsung ke T12; `risk_reconsent` tersimpan (diaktifkan setelah no. 22) |
| `RiskFollowUpTest` | Aturan `tl-1` dan *Didahulukan*; hanya `ketua`; tanpa jawaban di halaman; tanpa kode di audit; studi ditutup → kosong; jawaban yang dihapus tidak menandai lagi (diaktifkan setelah no. 22) |
| `RiskFlagsNeverExportedTest` | Tidak ada kolom atau lembar penanda di unduhan, data mentah, pemantauan, dan salinan satu anak |
| `DataRequestFlowTest` | `peneliti` mengajukan → `requested`; `ketua` mengonfirmasi → `preview` `hard`; `ketua` mengajukan → langsung `preview`; tolak dan batal; admin menjalankan; peneliti hanya melihat miliknya |
| `DataRequestConcurrencyTest` | Dua eksekusi atau dua konfirmasi bersamaan: satu berhasil, satu mendapat pesan; tidak ada penghapusan ganda |
| `DataRequestScopeTest` | `all` menghapus semua tabel §7.3 termasuk tabel bagian 2–3; `risk_items` hanya jawaban ang-21–ang-23 dan `risk_reconsent`; `recording` nol baris; `participant_code` tetap di permintaan |
| `DataRequestExportPurgeTest` | Berkas unduhan sejak anak mendaftar terhapus, `purged_by_request_id` terisi; unduhan yang lebih lama tetap |
| `DataRequestChecklistTest` | Butir per jenis; `parent_notified` terkunci sampai butir lain lengkap; `completed` setelah semua; batas waktu dan warnanya; hak centang per peran |
| `DeletionMinutesTest` | Berita acara berisi kode, jenis, tanggal, jumlah baris, dan centang; tanpa nama anak atau orang tua |
| `ParticipantCopyTest` | ZIP tanpa disimpan di server; hanya anak itu; tanpa `answer_key`, teks soal, id internal, perangkat; jawaban risiko hanya bila dicentang, dengan audit `include_risk`; kalimat "tidak disertakan" ada di PDF |
| `RegistrationCorrectionTest` | Hanya `age`, `gender`, `class_level`; validasi pendaftaran; alasan wajib; audit lama dan baru; `corrected_at`; kode, sekolah, dan rombel tidak bisa diubah |
| `ConsentRegisterExportTest` | Kolom §10.1; tanpa nama, sekolah, NPSN, nama pengguna; `consent_register_exported_at` terisi; hanya `ketua` |
| `StudyClosureTest` | Syarat menahan tombol; `GANTI` mengganti nama, nama pengguna, sandi, dan sekolah ke baris berkode; baris sekolah asli tanpa `research_code`; audit; permintaan, salinan, dan pembetulan ditolak setelahnya |
| `StudyRetentionTest` | Bawaan 7305; studi `field_mode` < 7305 ditolak; hitungan dari `closed_at`; studi belum ditutup tanpa pratinjau; studi lain tetap dari mulai sesi |
| `HashedIpSessionTest` | `Config\Session::$driver` = `HashedIpSessionHandler`; konstruktor menyimpan `hash_ip()` dari IP, bukan IP mentah |
| `BrowserSessionPurgeTest` | Retensi menghapus baris `ci_sessions` yang kedaluwarsa dan membiarkan yang masih hidup; jumlahnya di ringkasan |
| `ConsentDataRightsMigrationTest` | Tabel dan kolom baru; `retention_days` 1825 → 7305; `ci_sessions` kosong; `down()` berjalan |

### 19.2 Tes yang ditulis ulang

- Tes retensi dan penghapusan (`grep -rln "RetentionService\|tata-kelola" tests/`): `HARD_ORDER` baru, status bersyarat, mode `hard` untuk permintaan orang tua.
- Tes pendaftaran dan masuk: kunci `*Study`, `assent_version`, status `inactive`.
- `ParticipantCheck` di tes filter game.
- `RouteWiringTest` untuk rute baru; `LanguageFilesTest` untuk kunci `Game.php` baru.

### 19.3 Cek manual

Di instalasi uji dengan akun `uji.*` dan data simulasi:
1. Daftar satu siswa dalam mode penelitian; periksa teks centang dan `assent_version`. Isi izin di panel sebagai `koordinator`.
2. Ubah status ke *Mengundurkan diri (data tetap)* saat siswa sedang bermain; pastikan sesinya berakhir dan masuk ditolak.
3. Ajukan permintaan B sebagai `koordinator`, konfirmasi sebagai `ketua`, jalankan sebagai admin. Jalankan `backup.sh --ganti` di Jalur L dan `backup.ps1 -Ganti` di Jalur W, termasuk sekali dengan lokasi kedua terputus. Isi semua centang dan cetak berita acaranya.
4. Buat salinan satu anak, sekali tanpa dan sekali dengan centang anak setuju.
5. Betulkan umur satu siswa; periksa log audit dan unduhan.
6. Tutup studi uji, unduh daftar izin berkode, jalankan *Ganti nama dengan kode*, lalu periksa halaman siswa admin, direktori sekolah, dan unduhan.
7. Di MariaDB: masuk, lalu periksa bahwa `ci_sessions.ip_address` berisi hash 64 karakter; jalankan `gelita:retention:run` dan periksa baris kedaluwarsa terhapus.
8. Bila §7–§9 belum selesai: uji jalan manual §13 sekali dan catat hasilnya di 04.

### 19.4 Selesai bila

- `vendor/bin/phpunit --no-coverage` hijau, termasuk tes di 19.1 (tes yang bergantung pada no. 22 dilewati dengan alasan sampai modul itu ada).
- Cek manual 19.3 lolos di kedua jalur.
- `grep -rn "DatabaseHandler::class" app/Config/Session.php` tidak menemukan apa pun.
- Dokumen di [§20](#20-dokumen-yang-ikut-diperbarui-saat-implementasi) diperbarui di cabang yang sama.

---

## 20. Dokumen yang ikut diperbarui saat implementasi

- `docs/01_DATABASE.md`: tabel dan kolom baru; *Retensi dan penghapusan data* (jenis permintaan, status `requested` dan `completed`, hitungan dari `closed_at`); `ci_sessions` dengan IP berhash.
- `docs/08_DEPLOYMENT.md`: *Backup* (lokasi kedua 30 hari, pilihan `--ganti` dan `-Ganti`); *Tata kelola data anak* no. 1, 2, 5, dan 7 (persetujuan di panel, masa simpan dari izin etik, permintaan orang tua, IP berhash); *Tugas Terjadwal* (pembersihan sesi di retensi); langkah migrasi di luar jam sesi.
- `docs/02_PROJECT_FOUNDATION.md`: status `HashedIpSessionHandler`.
- `docs/04_CONTROLLER_ROUTE.md`, `docs/05_VIEW_UI.md`, `docs/07_FEATURE_INTEGRATION.md` (FITUR 16 dan 17), `README.md`.
- `deploy/linux/backup.sh`, `deploy/windows/backup.ps1`.
- `.claude/rules/glossary.md`: izin berkode (`consent register`, `participant_consents`), permintaan data (`data request`), penanda tindak lanjut (`risk follow-up flag`), berita acara (`deletion minutes`), ganti nama dengan kode (`identity replacement`), cadangan diganti (`backup replacement`), akun nonaktif (`deactivated`).
- Aturan kerja repo: bagian *Testing and known pitfalls* bila ada jebakan baru (misalnya tes penangan sesi di SQLite).
- `docs/PROGRESS.md` dan catatan keputusan implementasi.

---

## 21. Pertanyaan terbuka

Tidak menahan draf ini.

1. **Dasar menahan jawaban butir risiko dari salinan orang tua** (K2). PP 33/2026 memberi orang tua hak akses atas data anak; keputusan ini memakai janji di T11 dan kepentingan terbaik anak. Pasal yang membolehkan pembatasan akses demi keselamatan subjek data belum dicek (`needs_verification`). Usulan: dicek bersama nomor pasal lain sebelum berkas etik diunggah, dan disebut di 22 supaya komite bisa menilainya.
2. **Transkrip pada pilihan C rekaman.** T10 hanya menyebut "rekaman yang sudah ada dihapus". Transkrip berkode yang dibuat dari rekaman itu ikut dihapus atau tidak? Usulan: ikut dihapus, karena izinnya untuk rekaman wawancara itu; perlu kalimat di 22 dan T10.
3. **Provinsi dan kabupaten setelah studi ditutup.** §10.2 membiarkannya. Bila komite atau peneliti menilai kabupaten bersama kode sekolah masih terlalu sempit, keduanya ikut dikosongkan; analisis memakai L8 untuk konteks sekolah.
4. **Lembar `izin` di unduhan utama `ketua`.** §10.1 membuat unduhan terpisah. Bila lapis 3 lebih mudah dengan lembar `izin` di unduhan utama, lembar itu ditambahkan ke daftar lembar 29 §7.2.
5. **Kritik dan saran yang memuat nama.** Isian bebas anak bisa memuat nama sendiri atau teman. Penggantian nama §10.2 tidak menyentuhnya. Usulan: `ketua` memeriksanya sekali sebelum studi ditutup, dengan pemeriksaan kata nama seperti impor lembar (29 K13), lalu mengganti nama dengan `[nama]`.
6. **Snapshot cadangan penyedia server.** Bila paket server IDCloudHost menyertakan cadangan otomatis yang tidak bisa dihapus pengguna, langkah 5 di §7.5 tidak bisa dijalankan. Ditanyakan ke penyedia saat perjanjian prosesor disusun (26 → P6).

---

## 22. Keputusan pengembang

Satu putaran tanya, empat pertanyaan (30 Oktober 2026). Pengembang memilih usulan di keempatnya. `asal: pengembang`.

1. **Permintaan hapus dari `peneliti` dikonfirmasi `ketua` lebih dulu** (K1). Permintaan dari `ketua` langsung ke admin. Ini menjawab [27 → Pertanyaan terbuka](27_IMPLEMENTASI_PERAN_PENELITI.md#16-pertanyaan-terbuka) no. 2. Tidak dipilih: tanpa konfirmasi, seperti tertulis di 22 dan 23 → T10; hanya `ketua` yang boleh mengajukan.
2. **Jawaban ang-21–ang-23 tidak masuk salinan orang tua, kecuali anak setuju** (K2), dengan centang di log audit dan kalimat di salinan. Tidak dipilih: selalu disertakan (dengan kalimat T11 diubah); selalu ditahan tanpa pengecualian.
3. **Cadangan diganti** (K3): setelah penghapusan dan saat studi ditutup, cadangan baru dibuat lalu cadangan lama di kedua lokasi dihapus, sebelum orang tua diberi tahu; lokasi kedua juga 30 hari. Tidak dipilih: daftar hapus yang diterapkan ulang saat pulih, dengan cadangan habis sendiri dalam 30 hari; diserahkan ke penyedia server.
4. **Semua kolom L7 yang berkode masuk panel** (K4): tanggal izin orang tua, izin rekaman, izin butir risiko, status dan tanggalnya. Tidak dipilih: hanya izin butir risiko di panel, dengan daftar izin berkode disalin tangan dari L7.

**Temuan kode untuk ditinjau pengembang** (bukan pertanyaan baru; dicatat di 04):
- Lokasi kedua cadangan tidak pernah menghapus berkas, jadi data yang dihapus dan nama tetap ada di sana tanpa batas ([§7.5](#75-salinan-cadangan)).
- Retensi menghitung dari mulai sesi, bukan dari studi ditutup, dan 7300 hari kurang dari 20 tahun (K10).
- Sekolah tetap tersambung ke nama dan NPSN lewat `school_id` setelah nama anak diganti kode (K11).
- Akun langsung nonaktif saat P1 menyimpan status mundur, bukan menunggu admin (K5).
- Pembersih sesi bawaan PHP bisa tidak pernah berjalan di Debian/Ubuntu ([§12](#12-ip-berhash-di-sesi-peramban)).
- Nomor KG di 26 berbeda dari 03 dan 22 ([§4](#4-tenggat-sebelum-akun-gelita-anak-pertama-kali-dipakai)).

---

## 23. Akibat ke dokumen lain

Dicatat di sini dan dibawa sesi koordinator setelah bagian 1–4 diselaraskan.

| Dokumen | Bagian | Perubahan | Status |
| --- | --- | --- | --- |
| [01](01_REVISI_10_ANALITIK.md) | *Catatan perubahan sistem*, no. 23 | `retention_days` 7305 dihitung sejak studi ditutup (bukan "± 7300"); permintaan dari peneliti dikonfirmasi ketua; cadangan diganti; izin berkode di panel; tenggat "sebelum kegiatan pertama yang memakai akun anak" | Belum |
| [02](02_LEMBAR_LAPANGAN.md) | *L7*, cara mengisi dan penyimpanan | P1 memasukkan kolom berkode L7 ke panel (tanggal izin orang tua, dua izin, status dan tanggal); daftar izin berkode diunduh dari sistem dan dicocokkan `ketua` dengan L7, bukan disalin tangan; status mundur menonaktifkan akun saat disimpan | Belum |
| [11](11_KAMUS_DATA.md) | *Penanda untuk analisis* (`consent_ok`) | Sumber `consent_ok` adalah daftar izin berkode dari sistem, dicocokkan dengan L7; `registration_corrected`; `risk_reconsent` | Saat `kamus-1.1` |
| [22](22_PERMOHONAN_IZIN_ETIK.md) | *Mengundurkan diri dan hapus data* | Jalur: peneliti mengajukan, ketua mengonfirmasi, admin menjalankan; akun nonaktif saat status disimpan; salinan tanpa jawaban butir risiko kecuali anak setuju (dengan dasar yang dicek, [§21](#21-pertanyaan-terbuka) no. 1); cadangan diganti sebelum orang tua diberi tahu; transkrip pada pilihan C ([§21](#21-pertanyaan-terbuka) no. 2) | Belum |
| [22](22_PERMOHONAN_IZIN_ETIK.md) | *Penyimpanan dan enkripsi*, *Masa simpan dan pemusnahan* | Cadangan disimpan 30 hari di kedua lokasi dan diganti setelah penghapusan; saat studi ditutup nama pengguna dan sekolah juga diganti kode; sistem menyimpan izin berkode (tanpa nama) | Belum |
| [23](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md) | T10, *Cara menjalankan* | A: akun nonaktif saat P1 mengubah status; B dan C: ketua mengonfirmasi sebelum admin menjalankan; D: salinan tanpa jawaban butir risiko kecuali anak setuju; kotak peneliti mendapat "dikonfirmasi ketua tanggal ___" | Belum |
| [23](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md) | T4, T11, T12 | Teks en diusulkan di [§17](#17-teks-layar-dan-panel); tidak ada perubahan teks id | Belum |
| [26](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md) | *Kegiatan* | Nomor KG disamakan dengan 03 dan 22 (KG2 berpikir lantang, KG3 uji coba, KG4 wawancara, KG5 data utama); periksa setiap "KG2"–"KG5" di 26 setelahnya, termasuk *Konsultasi* dan *Syarat sebelum kegiatan dengan anak* ([§4](#4-tenggat-sebelum-akun-gelita-anak-pertama-kali-dipakai)) | Belum |
| [26](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md) | *Hak orang tua dan anak*, *Langkah perlindungan* (P8, P13, P14, P15, P18), inventaris A14 | Kolom *Siap di sistem* menunjuk bagian 4; A14 tempatnya juga server (izin berkode); P14 menyebut konfirmasi ketua dan penggantian cadangan; risiko R8 dan R14 mendapat langkah K3 | Belum |
| [27](27_IMPLEMENTASI_PERAN_PENELITI.md) | §5.1, §16 no. 2 | Kemampuan `deletion.confirm` (ketua) dan `identities.replace` (admin); pertanyaan no. 2 dijawab | Sudah (sesi koordinator, 30 Oktober 2026) |
| [28](28_IMPLEMENTASI_SESI_LAPANGAN.md) | §6.5, §13, §14 | Baris `participant_consents` dibuat saat mendaftar; token menolak siswa nonaktif; `gelita:uji:akun` mengisi izin butir risiko Ya; kunci asing ke `participants` `ON DELETE CASCADE` | Sudah (sesi koordinator, 30 Oktober 2026) |
| [29](29_IMPLEMENTASI_DATA_PENELITIAN.md) | §12, §15 | Kunci asing `field_sheet_rows.participant_id` `ON DELETE CASCADE`; `data_exports.purged_by_request_id`; lembar `izin` ([§21](#21-pertanyaan-terbuka) no. 4) | Sudah (sesi koordinator, 30 Oktober 2026) |
| [03](03_KODE_DAN_ARTI.md) | Kode baru | Status izin (`active`, `left`, `withdrawn_keep`, `withdrawn_delete`), jenis permintaan (`all`, `risk_items`, `recording`), aturan penanda `tl-1`, `assent_version` | Belum |
| [00](00_INDEKS.md) | *Daftar dokumen*, *Tahap dokumen 3* | Baris bagian 4 | Sudah (cabang ini) |
