# Dokumen Implementasi Bagian 1 — Peran Peneliti dan Panel Peneliti

Per 30 Oktober 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · perubahan sistem no. 10 di [01 → Catatan perubahan sistem](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti)

> **Status: `draf pengembang`, `asal: pengembang`** (disusun dengan bantuan AI, 30 Oktober 2026). Ini dokumen, bukan perubahan sistem. Kode, migrasi, dan instalasi baru diubah di tahap 3, setelah gerbang G2 dan setelah dokumen implementasi lengkap disetujui ([04](04_CATATAN_KEPUTUSAN.md), entri 30 Oktober 2026).
>
> Draf ini boleh naik sampai `disetujui pengembang`. Sebelum dokumen implementasi lengkap disetujui, isinya dicek ulang sekali terhadap matriks revisi induk ([20](20_PAKET_VALIDASI.md)) dan keputusan komite etik.
>
> **Bagian 1 dari 4.** Bagian 2 (`28`, sesi lapangan, token, kode peserta, pemantauan), bagian 3 (`29`, log, ekspor, kamus data, templat lembar), dan bagian 4 (`30`, persetujuan dan hak atas data) memakai peran, hak, dan panel yang ditetapkan di sini.

---

## Daftar isi

0. [Cara memakai dokumen ini](#0-cara-memakai-dokumen-ini)
1. [Kebutuhan, sumber, dan tempatnya](#1-kebutuhan-sumber-dan-tempatnya)
2. [Keputusan yang dikunci](#2-keputusan-yang-dikunci)
3. [Yang tidak berubah](#3-yang-tidak-berubah)
4. [Hak akses per peran](#4-hak-akses-per-peran)
5. [Model hak akses di kode](#5-model-hak-akses-di-kode)
6. [Skema database](#6-skema-database)
7. [Rute](#7-rute)
8. [Panel peneliti](#8-panel-peneliti)
9. [Data yang terlihat per peran](#9-data-yang-terlihat-per-peran)
10. [Mengatur ulang kata sandi lewat kode peserta](#10-mengatur-ulang-kata-sandi-lewat-kode-peserta)
11. [Akun staf dan perintah spark](#11-akun-staf-dan-perintah-spark)
12. [Teks antarmuka](#12-teks-antarmuka)
13. [Kode yang dihapus](#13-kode-yang-dihapus)
14. [Pengujian dan kriteria selesai](#14-pengujian-dan-kriteria-selesai)
15. [Dokumen yang ikut diperbarui saat implementasi](#15-dokumen-yang-ikut-diperbarui-saat-implementasi)
16. [Pertanyaan terbuka](#16-pertanyaan-terbuka)
17. [Keputusan pengembang](#17-keputusan-pengembang)
18. [Akibat ke dokumen lain](#18-akibat-ke-dokumen-lain)

---

## 0. Cara memakai dokumen ini

**Urutan.** Bagian 1 dikerjakan lebih dulu, baik sebagai dokumen maupun nanti sebagai implementasi di tahap 3, karena bagian 2–4 menambah halaman ke panel ini dan memakai peta hak aksesnya ([§5](#5-model-hak-akses-di-kode)).

**Batas.** Dokumen ini hanya mengatur peran staf, hak aksesnya, dan kerangka panel. Halaman baru milik bagian lain hanya disebut sebagai **titik sambung**: hak aksesnya ditetapkan di sini, isinya di bagian masing-masing.

**Saat implementasi:**
- Kerjakan di satu cabang pendek dari `main`. Commit berbahasa Inggris (`.claude/rules/language.md`).
- `vendor/bin/phpunit --no-coverage` harus hijau sebelum push.
- Jangan menjalankan migrasi, `gelita:reset`, atau skrip rilis di server produksi (aturan kerja repo).

**Prinsip yang tetap berlaku:**
- Server satu-satunya sumber kebenaran.
- CSRF di setiap formulir.
- Setiap tindakan penting menulis log audit.
- Tanpa CDN.
- `Admin.php` hanya berbahasa Indonesia; teks yang dilihat siswa selalu punya pasangan bahasa Inggris.

**Rujukan kode.** Ditulis per berkas dan metode, dengan nomor baris per 30 Oktober 2026. Nomor baris bisa bergeser, jadi cari nama metodenya.

---

## 1. Kebutuhan, sumber, dan tempatnya

| # | Kebutuhan | Sumber | Di sini |
| ---: | --- | --- | --- |
| 1 | Admin dan peneliti dua peran berbeda; admin memegang sistem dan identitas, peneliti memegang data berkode | [01 → Admin dan peneliti](01_REVISI_10_ANALITIK.md#admin-dan-peneliti-dua-peran-berbeda); 04, 25 Oktober 2026 | §2, §4 |
| 2 | Peran `guru` dihapus seluruhnya, termasuk halaman dan hak aksesnya | 01, aturan pemisahan; 04, 25 Oktober 2026 (tahap 3 wawancara) | §6, §13 |
| 3 | Peneliti tidak pernah melihat nama atau nama pengguna siswa | 01, aturan pemisahan; [22 → Kerahasiaan dan pengelolaan data](22_PERMOHONAN_IZIN_ETIK.md#kerahasiaan-dan-pengelolaan-data); 26 → P2 | §9 |
| 4 | Admin tidak mengunduh data penelitian; cukup log teknis | 01, aturan pemisahan | §4, §9 |
| 5 | Satu orang boleh memegang dua peran, lewat dua akun terpisah | 01, aturan pemisahan | §2 (K9) |
| 6 | P1 mengatur ulang kata sandi siswa lewat kode peserta; sistem memberi kata sandi sementara dan mencatatnya di log audit | 01, aturan pemisahan; [02 → L2](02_LEMBAR_LAPANGAN.md#l2-peta-tempat-duduk-perangkat-dan-kode-peserta); 26 → P17 | §10 |
| 7 | Akun bernama samaran per peran: `admin`, `koordinator`, `pengamat`, `pewawancara`, `ketua` | [03 → Akun staf](03_KODE_DAN_ARTI.md#akun-staf); [22 → Identitas dan tim](22_PERMOHONAN_IZIN_ETIK.md#identitas-dan-tim) | §11 |
| 8 | Penanda tindak lanjut butir risiko daring hanya terlihat ketua peneliti dan tidak pernah diekspor | 01 no. 23; 26 → P8; 22 → prosedur rujukan | §4 (titik sambung bagian 4) |
| 9 | Studi dan statusnya dipegang peneliti | 01, tabel hak akses | §4, §7; keputusan no. 3 |
| 10 | Konten, bank soal, media, dan narasi dilihat saja oleh peneliti | 01, tabel hak akses | §4, §7 |
| 11 | Panel peneliti bertampilan panel admin, bukan game | [18](18_MOCKUP_LAYAR_BARU.md), layar `mk-tk-05` | §8 |
| 12 | Akun staf dinonaktifkan, tidak dihapus; pengembang dan admin bagian dari tim pengendali | [26 → Tim dan staf](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md#tim-dan-staf); 26, keputusan no. 1 | §2 (K10), §3 |
| 13 | Ekspor diunduh lewat akun peneliti dan dicatat di log audit; berkas di server dihapus setelah 7 hari | 26 → Aliran data; 22 → Data yang dikumpulkan | §9 (titik sambung bagian 3) |

---

## 2. Keputusan yang dikunci

Keputusan ini tidak perlu ditanyakan ulang saat implementasi. K1, K2, K11, K12, dan K15 diputuskan pengembang ([§17](#17-keputusan-pengembang)); sisanya menurunkan 01, 03, 22, dan 26.

| Kode | Keputusan |
| --- | --- |
| K1 | **Tiga peran staf:** `admin`, `peneliti`, `ketua`, disimpan apa adanya di `staff_users.role`. Peran `guru` tidak ada lagi. |
| K2 | **`peneliti`** (akun `koordinator`, `pengamat`, `pewawancara`) memegang kerja lapangan: sesi lapangan (bagian 2), isian L2, mengatur ulang kata sandi lewat kode peserta, pemantauan, data satu siswa lewat kodenya, dan mengajukan permintaan hapus data. Tidak boleh mengunduh data. **`ketua`** memegang semua hak `peneliti`, ditambah data penelitian semua siswa (sesi, data mentah, kritik dan saran), unduh data dan riwayat unduhannya, pengaturan studi, dan penanda tindak lanjut butir risiko daring (bagian 4). |
| K3 | **`admin`** memegang sistem dan identitas: akun siswa (nama, nama pengguna, status), konten, rilis dan paket aset, sekolah, akun staf, log audit, retensi, dan menjalankan permintaan hapus data. Admin tidak melihat dan tidak mengunduh data penelitian, dan melihat pengaturan studi tanpa bisa mengubahnya. |
| K4 | **Satu panel staf di `/admin`** dengan menu per peran. Tidak ada awalan rute baru. `/admin/login`, `/admin/logout`, dan `/admin/dashboard` tetap (pengecualian lama di `.claude/rules/language.md`). |
| K5 | **`peneliti` dan `ketua` tidak pernah melihat** nama, nama pengguna, nama sekolah, atau NPSN, di layar maupun di unduhan. Sekolah tampil sebagai kode: `SCH-000123` dari `RawDataService::schoolRef()` sampai bagian 2 menyediakan kode sekolah penelitian (`S01`, `S02`, …). |
| K6 | **Hak diatur oleh satu peta kemampuan** di kode ([§5](#5-model-hak-akses-di-kode)), bukan oleh cabang "admin atau bukan". Rute, menu, tampilan, dan layanan data membaca peta yang sama. |
| K7 | **Konten, bank soal, media, dan narasi** dilihat saja oleh `peneliti` dan `ketua` (permintaan `GET`). Semua permintaan yang mengubah (`POST`), impor bank, unggah, dan setujui hanya untuk `admin`. |
| K8 | **Kata sandi siswa diatur ulang oleh `peneliti` atau `ketua` lewat kode peserta**, tanpa nama. Siswa wajib membuat kata sandi baru saat masuk berikutnya, sama dengan alur sekarang. |
| K9 | **Satu orang yang memegang dua peran memakai dua akun** dengan nama pengguna berbeda, supaya jejak audit jelas. |
| K10 | **Akun staf dinonaktifkan, tidak dihapus.** Satu-satunya penghapusan adalah akun `guru` di migrasi [§6](#6-skema-database). |
| K11 | **Akun `guru` dihapus di migrasi**, bersama berkas unduhan dan permintaan hapus data yang diajukannya. Kolom `staff_users.school_id` dibuang. |
| K12 | **Studi diubah oleh `ketua`; `admin` melihat saja.** Rilis dan paket aset tetap milik `admin`, walaupun halamannya di bawah `/admin/studi/rilis`. |
| K13 | **Pratinjau paket aset (`?paket=`) hanya untuk `admin`.** Sekarang semua staf boleh (`AssetPackageService.php:198-208`). |
| K14 | **Unduh data hanya untuk `ketua` dan selalu berkode.** Mode unduh dengan identitas dihapus. Lembar dan kolom apa saja yang masuk ditetapkan bagian 3. |
| K15 | **Keamanan masuk staf** ([§11.1](#111-keamanan-masuk-staf)): kata sandi staf memakai aturan kata sandi kuat yang sama dengan siswa, ditambah panjang minimal 12 yang sudah ada. Sesi staf berakhir setelah 2 jam tanpa aktivitas. Tanpa verifikasi dua langkah. |

---

## 3. Yang tidak berubah

| Bagian | Aturan |
| --- | --- |
| Masuk staf | `AuthController` (`/admin/login`), kunci 15 menit setelah 5 kali gagal, kata sandi berhash, HTTPS. Sesi menyimpan `staff_id`, `staff_role`, `staff_name`; `staff_school_id` dihapus bersama kolomnya. |
| `StaffAuthFilter` | Tetap memuat ulang akun dari database di setiap permintaan, mengakhiri sesi bila akun hilang atau nonaktif, menyelaraskan peran, dan mewajibkan ganti kata sandi. Karena itu akun `guru` yang terhapus langsung keluar di permintaan berikutnya. |
| Log audit | Tabel `audit_logs` dengan `ip_hash`, `AuditLogModel::record()`. Aksi baru ditambahkan, tidak ada yang dihapus. |
| Kata sandi staf | `AccountController` (`/admin/akun/sandi`) untuk akun sendiri; `gelita:staff:password` untuk semua peran. |
| Seeder | `StaffUserSeeder` tetap hanya membuat `admin`. |
| Akun siswa | Nama pengguna dan kata sandi kuat, metrik kata sandi, ganti kata sandi, `must_change_password`. |
| Game | Tidak ada perubahan di layar siswa selain tiga kalimat di [§12](#12-teks-antarmuka). |
| Tampilan panel | Sistem desain panel admin (perombakan kelas 5, Dok 3 §8) tetap; peneliti memakai tampilan yang sama. |

---

## 4. Hak akses per peran

"Ya" berarti boleh membuka dan memakai, "Lihat" berarti membuka tanpa mengubah, "—" berarti 404 untuk halaman dan 403 untuk API. Kolom *Kemampuan* merujuk ke [§5.1](#51-peta-kemampuan).

**Halaman yang sudah ada**

| Halaman (rute) | Admin | Peneliti | Ketua | Kemampuan | Catatan |
| --- | --- | --- | --- | --- | --- |
| Ringkasan (`/admin`, `/admin/dashboard`) | Ya | Ya | Ya | `dashboard.view` | Isi kartu per peran, [§8](#8-panel-peneliti) |
| Ganti kata sandi sendiri (`/admin/akun/sandi`) | Ya | Ya | Ya | (semua staf) | Tidak berubah |
| Daftar siswa (`/admin/peserta`) | Ya: akun | Ya: kode | Ya: kode | `participants.accounts` / `participants.research` | Satu rute, dua tampilan, [§9](#9-data-yang-terlihat-per-peran) |
| Halaman siswa (`/admin/peserta/{id}`) | Ya: akun | Ya: data penelitian | Ya: data penelitian | sama | |
| Atur ulang kata sandi lewat id (`/admin/peserta/{id}/reset-sandi`) | Ya | — | — | `participants.reset_by_id` | Alur admin sekarang |
| Atur ulang kata sandi lewat kode (`/admin/peserta/reset-sandi`, **baru**) | — | Ya | Ya | `participants.reset_by_code` | [§10](#10-mengatur-ulang-kata-sandi-lewat-kode-peserta) |
| Sesi bermain (`/admin/sesi`, `/admin/sesi/{id}`, `/admin/sesi/{id}/event`) | — | — | Ya | `research.browse` | Peneliti memakai panel pemantauan bagian 2 |
| Data mentah (`/admin/data`, `/admin/data/{dimensi}`) | — | — | Ya | `research.browse` | |
| Kritik dan saran (`/admin/masukan`) | — | — | Ya | `research.browse` | |
| Unduh data (`/admin/ekspor`, `…/xlsx`, `…/csv`, `…/pdf`, `…/unduh/{id}`) | — | — | Ya | `research.export` | Selalu berkode (K14) |
| Konten, narasi, media, kelengkapan aset (permintaan `GET`) | Ya | Lihat | Lihat | `content.view` | Tombol ubah disembunyikan |
| Konten, impor bank, narasi, media (`POST` dan halaman impor) | Ya | — | — | `content.edit` | |
| Studi (`/admin/studi`, `GET`) | Lihat | — | Ya | `studies.view` | |
| Studi (`/admin/studi`, `POST`) | — | — | Ya | `studies.manage` | Keputusan no. 3 |
| Rilis dan paket aset (`/admin/studi/rilis…`) | Ya | — | — | `releases.manage` | Jalurnya tidak diganti |
| Pratinjau paket (`?paket=`) | Ya | — | — | `releases.manage` | K13 |
| Tata kelola: hapus data (`/admin/tata-kelola`, `…/hapus/…`) | Ya | — | — | `deletion.execute` | Pengajuan dan konfirmasi di halaman *Permintaan data* bagian 4 ([30 → Permintaan hapus data](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#7-permintaan-hapus-data)) |
| Tata kelola: log audit (`/admin/tata-kelola/audit`) | Ya | — | — | `audit.view` | Ketua melihat riwayat unduhannya sendiri di halaman unduh data |
| Tata kelola: retensi (`/admin/tata-kelola/retensi`) | Ya | — | — | `retention.run` | |
| Sekolah (`/admin/sekolah…`) | Ya | — | — | `schools.manage` | Pemetaan NPSN ke kode hanya admin |
| Akun staf (`/admin/staf…`) | Ya | — | — | `staff.manage` | |
| API ringkasan (`/api/admin/summary`) | Ya | Ya | Ya | `dashboard.view` | Isi per peran |
| API linimasa sesi (`/api/admin/sessions/{id}/timeline`) | — | — | Ya | `research.browse` | |
| API status unduhan (`/api/admin/exports/{id}/status`) | — | — | Ya | `research.export` | Hanya unduhan milik sendiri |

**Titik sambung untuk bagian 2–4** (hak ditetapkan di sini; halaman dan isinya di bagian masing-masing)

| Halaman | Admin | Peneliti | Ketua | Kemampuan | Bagian |
| --- | --- | --- | --- | --- | --- |
| Sesi lapangan dan token | — | Ya | Ya | `field_sessions.manage` | 2 (`28`) |
| Isian L2 (kode, baris, kursi, buklet) | — | Ya | Ya | `field_sessions.manage` | 2 (`28`) |
| Templat lembar lapangan per sesi, unggah lembar, ketikan kedua | — | Ya | Ya | `field_sessions.manage` | 3 (`29`), di halaman sesi lapangan |
| Izin berkode per siswa (kolom berkode L7) | — | Ya | Ya | `field_sessions.manage` | 4 (`30`), di halaman sesi lapangan |
| Panel pemantauan lapangan | Lihat angka saja, tanpa kode peserta ([28 → Panel pemantauan](28_IMPLEMENTASI_SESI_LAPANGAN.md#12-panel-pemantauan)) | Ya | Ya | `monitoring.view` | 2 (`28`) |
| Log teknis (perangkat, paket, event hilang) | Ya | — | — | `tech_logs.view` | 3 (`29`) |
| Lembar *Kamus Data* dan isi unduhan berkode, termasuk lembar `event` dan kolom `answer_key` | — | — | Ya | `research.export` | 3 (`29`) |
| Mengajukan permintaan hapus data (semua atau sebagian) | — | Ya | Ya | `deletion.request` | 4 (`30`) |
| Mengonfirmasi atau menolak permintaan dari `peneliti` | — | — | Ya | `deletion.confirm` | 4 (`30`, K1) |
| Menjalankan permintaan hapus data | Ya | — | — | `deletion.execute` | 4 (`30`) |
| Penanda tindak lanjut butir risiko daring | — | — | Ya | `risk_flags.view` | 4 (`30`) |
| Ambil data satu anak untuk permintaan orang tua | — | — | Ya | `research.export` | 4 (`30`), 23 → T10 pilihan D |
| Daftar izin berkode untuk penutupan studi | — | — | Ya | `research.export` | 4 (`30`) |
| Pembetulan data pendaftaran dengan jejak audit, aktifkan lagi akun | Ya | — | — | `participants.accounts` | 4 (`30`) |
| Penutupan studi: nama dan sekolah diganti kode | Ya (menjalankan) | — | Lihat | `identities.replace`, `studies.view` | 4 (`30`) |

Halaman milik bagian 2–4, rute, dan menu yang lengkap ada di [28 → Rute](28_IMPLEMENTASI_SESI_LAPANGAN.md#15-rute), [29 → Rute dan perintah spark](29_IMPLEMENTASI_DATA_PENELITIAN.md#13-rute-dan-perintah-spark), dan [30 → Hak akses dan menu](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#14-hak-akses-dan-menu).

---

## 5. Model hak akses di kode

### 5.1 Peta kemampuan

Kelas baru `App\Libraries\StaffAccess` memegang daftar peran dan peta kemampuan. Nama kemampuan berbahasa Inggris dan hanya ada di kode, tidak disimpan di database.

```php
// Roles stored in staff_users.role. 'guru' was removed (revision 10, part 1).
public const ROLES = ['admin', 'peneliti', 'ketua'];

// Capability => roles that hold it. The only place where rights are decided.
private const CAPABILITIES = [
    'dashboard.view'              => ['admin', 'peneliti', 'ketua'],
    'participants.accounts'       => ['admin'],
    'participants.reset_by_id'    => ['admin'],
    'participants.research'       => ['peneliti', 'ketua'],
    'participants.reset_by_code'  => ['peneliti', 'ketua'],
    'research.browse'             => ['ketua'],
    'research.export'             => ['ketua'],
    'content.view'                => ['admin', 'peneliti', 'ketua'],
    'content.edit'                => ['admin'],
    'studies.view'                => ['admin', 'ketua'],
    'studies.manage'              => ['ketua'],
    'releases.manage'             => ['admin'],
    'schools.manage'              => ['admin'],
    'staff.manage'                => ['admin'],
    'audit.view'                  => ['admin'],
    'retention.run'               => ['admin'],
    'deletion.request'            => ['peneliti', 'ketua'],
    'deletion.confirm'            => ['ketua'],   // part 4 (30, K1)
    'deletion.execute'            => ['admin'],
    // Hooks for parts 2-4.
    'field_sessions.manage'       => ['peneliti', 'ketua'],
    'monitoring.view'             => ['admin', 'peneliti', 'ketua'],
    'tech_logs.view'              => ['admin'],
    'risk_flags.view'             => ['ketua'],
    'identities.replace'          => ['admin'],   // part 4 (30 §10.2)
];
```

Peta ini lengkap untuk bagian 1–4: bagian 2 (`28`) dan bagian 3 (`29`) tidak menambah kemampuan; bagian 4 (`30`) menambah `deletion.confirm` dan `identities.replace` ([30 → Hak akses dan menu](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#14-hak-akses-dan-menu)).

`StaffAccess::allows(string $role, string $capability): bool` mengembalikan `false` untuk peran atau kemampuan yang tidak dikenal, jadi salah ketik menutup akses, bukan membukanya.

### 5.2 Pemeriksaan di entity, controller, dan tampilan

- **Entity.** `StaffUser::can(string $capability): bool` memanggil `StaffAccess::allows($this->role, …)`. `StaffUser::schoolScope()` dihapus. `isAdmin()` hanya dipakai untuk penjaga "admin tidak menurunkan perannya sendiri" (`StaffController`).
- **Controller.** Trait `App\Controllers\Concerns\StaffScope` mendapat `can(string $capability)` dari `session('staff_role')`, yang diselaraskan `StaffAuthFilter` di setiap permintaan. `schoolScope()` dan `isAdmin()` di trait itu dihapus.
- **Tampilan.** Helper `staff_can('content.edit')` untuk menyembunyikan tombol dan formulir. Tampilan tidak pernah memeriksa nama peran secara langsung.

### 5.3 Filter rute `staffCan`

- Filter baru `App\Filters\StaffCanFilter` dengan alias `staffCan` di `app/Config/Filters.php`.
- Argumennya satu atau beberapa kemampuan, misalnya `staffCan:research.export`. Akses diberikan bila peran memegang salah satunya.
- Jawabannya sama dengan `StaffRoleFilter` sekarang: 404 untuk halaman, 403 untuk `/api`.
- `StaffRoleFilter` dan alias `staffRole` dihapus setelah semua rute pindah.

### 5.4 Menu

- `AdminMenu::ITEMS` mengganti kolom keempat (benar/salah "khusus admin") dengan nama kemampuan.
- `AdminMenu::items()` menerima fungsi pemeriksa kemampuan, bukan `bool $isAdmin`.
- Menu yang tidak berhak tetap tidak dirender; rutenya tetap dijaga filter.

### 5.5 Cakupan data

`App\Services\StaffScope` dipakai layanan data (`RawDataService`, `ExportService`, `AnalyticsService`, `ReportService`, `ParticipantModel::scopedForStaff()`, `AdminTable`).

| Sekarang | Sesudah |
| --- | --- |
| `admin()`: semua sekolah, dengan identitas | `accounts()`: semua sekolah, identitas akun, **tanpa** data penelitian. Dipakai daftar dan halaman siswa milik admin |
| `teacher(int $schoolId)`: satu sekolah, selalu anonim; tanpa sekolah menjadi sekolah 0 | **Dihapus** |
| — | `research()`: semua sekolah, selalu berkode, sekolah sebagai `school_ref`, tanpa kolom identitas (`display_name`, `username`, `school_name`, `school_npsn`). Dipakai `peneliti` dan `ketua` |
| `fromStaff()`: `admin` atau `teacher` | `fromStaff()`: `admin` → `accounts()`, `peneliti` dan `ketua` → `research()`; peran lain melempar pengecualian |
| `withAnonymous()` | Dihapus; `research()` selalu berkode |

Bedanya `peneliti` dan `ketua` (satu siswa lewat kode, atau semua siswa dan unduhan) diatur di tingkat rute lewat kemampuan, bukan di cakupan data.

### 5.6 Cabang "admin atau bukan" yang diganti

Setiap cabang di bawah diganti dengan `can(...)` atau dengan cakupan [§5.5](#55-cakupan-data). Hasil pencarian kode 30 Oktober 2026:

| Berkas | Yang berubah |
| --- | --- |
| `app/Controllers/Admin/ParticipantController.php` (`index`, `show`, CSV, pencarian, `resetPassword`) | Dua tampilan per kemampuan; reset lewat kode baru ([§10](#10-mengatur-ulang-kata-sandi-lewat-kode-peserta)) |
| `app/Controllers/Admin/ExportController.php`, `app/Services/ExportService.php` (`context()`) | Hanya `ketua`; `anonymized` selalu 1; daftar unduhan hanya milik sendiri |
| `app/Controllers/Admin/DashboardController.php` | Kartu per peran: kesiapan sistem dan peringatan retensi untuk admin, angka partisipasi berkode untuk peneliti dan ketua |
| `app/Controllers/Admin/RawDataController.php`, `app/Services/RawDataService.php` | Kolom identitas tidak pernah dibuat untuk `research()`; cabang "sekolah dipaksa" dihapus |
| `app/Services/AnalyticsService.php`, `app/Services/ReportService.php` | Cakupan dari `fromStaff()` |
| `app/Models/ParticipantModel.php` (`scopedForStaff()`), `app/Libraries/AdminTable.php` (`visibleColumns()`) | Kolom per cakupan |
| `app/Controllers/Api/AdminApiController.php` | Pemeriksaan per kemampuan, lihat [§4](#4-hak-akses-per-peran) |
| `app/Controllers/Admin/BaseAdminController.php` | Cabang filter "sekolah sendiri" dihapus |
| `app/Controllers/Admin/StudyController.php` | `GET` untuk `studies.view`, `POST` untuk `studies.manage`; formulir terkunci untuk admin |
| `app/Controllers/Admin/ContentController.php`, `MediaController.php`, `NarrationController.php` | Tombol dan formulir ubah hanya bila `content.edit` |
| `app/Services/AssetPackageService.php` (pratinjau `?paket=`) | Hanya `releases.manage` |
| `app/Views/layouts/admin.php`, `components/admin-sidebar.php`, `components/admin-filter-bar.php`, `admin/staff/index.php`, `admin/participants/*`, `admin/login.php` | Label peran, teks pencarian, cabang sekolah terkunci, pilihan peran |

---

## 6. Skema database

### 6.1 Migrasi `2026-01-01-003400_ReplaceTeacherWithResearcherRoles`

Nomor sesudah migrasi terakhir (`003300_AddPackageCodeToResearchStudies`).

**Urutan migrasi bagian 1–4** (diselaraskan sesi koordinator, 30 Oktober 2026). Setiap bagian diimplementasikan berurutan dan menaikkan `SystemResetService::REQUIRED_MIGRATION` ke migrasi terakhirnya; setelah keempat bagian, nilainya `2026-01-01-003800`.

| Migrasi | Bagian |
| --- | --- |
| `2026-01-01-003400_ReplaceTeacherWithResearcherRoles` | 1 (dokumen ini) |
| `2026-01-01-003500_CreateFieldSessions` | 2 ([28 → Skema database](28_IMPLEMENTASI_SESI_LAPANGAN.md#14-skema-database)) |
| `2026-01-01-003600_CleanResearchLogs` | 3 ([29 → Skema database](29_IMPLEMENTASI_DATA_PENELITIAN.md#12-skema-database)) |
| `2026-01-01-003700_CreateFieldSheets` | 3 |
| `2026-01-01-003800_AddConsentAndDataRights` | 4 ([30 → Skema database](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#15-skema-database)) |

Bila ada migrasi lain yang masuk `main` sebelum implementasi, seluruh urutan digeser bersama dan tabel ini diperbarui.

**`up()`** dalam satu transaksi:

1. Ambil id semua baris `staff_users` dengan `role = 'guru'`.
2. Untuk setiap baris `data_exports` dengan `requested_by` salah satu id itu: hapus berkasnya dari penyimpanan, lalu barisnya. Kunci asing `requested_by` tidak punya `ON DELETE` dan akan menahan penghapusan akun.
3. Hapus baris `data_deletion_requests` dengan `requested_by` salah satu id itu, karena kuncinya juga tanpa `ON DELETE`. Saat ini guru tidak bisa mengajukan, jadi biasanya tidak ada baris.
4. Hapus baris `staff_users` peran `guru`. Rujukan lain menjadi `NULL` sesuai kuncinya (`ON DELETE SET NULL`): `audit_logs.staff_user_id`, `audio_assets.approved_by`, `data_deletion_requests.approved_by`, `game_event_logs.deleted_by`, `asset_packages.built_by`. Riwayat audit tetap ada dengan pelaku kosong.
5. Buang kunci asing dan indeks `staff_users.school_id`, lalu kolomnya.
6. Ubah `staff_users.role` menjadi `VARCHAR(20) NOT NULL` tanpa nilai bawaan (sekarang bawaannya `'guru'`).

Sebelum menulis migrasi, periksa ulang setiap kunci asing ke `staff_users` (`grep -rn "staff_users', 'id'" app/Database/Migrations`). Bila ada tabel baru sejak 30 Oktober 2026, tambahkan langkahnya.

**`down()`** mengembalikan kolom `school_id` (boleh kosong, `ON DELETE SET NULL`) dan bawaan `role`. Akun, unduhan, dan permintaan yang terhapus tidak kembali; ini ditulis di komentar migrasi.

**SQLite (tes).** `dropColumn` di SQLite membangun ulang tabel, jadi kunci asing dibuang dulu. Tes migrasi di [§14](#14-pengujian-dan-kriteria-selesai) menjalankannya di SQLite. Perilaku di MariaDB/MySQL dicek sekali di mesin pengembangan dengan salinan database.

### 6.2 Model dan aturan

| Berkas | Perubahan |
| --- | --- |
| `app/Models/StaffUserModel.php` | `role` → `required\|in_list[admin,peneliti,ketua]`, dari `StaffAccess::ROLES`; `school_id` keluar dari `allowedFields` |
| `app/Entities/StaffUser.php` | `can()` baru; `schoolScope()` dihapus; `toSafeArray()` tanpa `school_id` |
| `app/Controllers/Admin/StaffController.php` | Aturan peran dari `StaffAccess::ROLES`; aturan "guru wajib punya sekolah" dan `postedSchool()` dihapus |
| `app/Services/SystemResetService.php` | `REQUIRED_MIGRATION` menjadi `2026-01-01-003400`; bagian 2–4 menaikkannya sampai `2026-01-01-003800` ([§6.1](#61-migrasi-2026-01-01-003400_replaceteacherwithresearcherroles)) |

---

## 7. Rute

Jalur yang sudah ada tidak diganti; hanya filternya. Kelompok di `app/Config/Routes.php` menjadi seperti ini (ringkas):

```php
$routes->group('', ['filter' => 'staffAuth'], static function ($routes): void {
    $routes->get('/', 'DashboardController::index');
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('akun/sandi', 'AccountController::passwordForm');
    $routes->post('akun/sandi', 'AccountController::changePassword');

    // Students: admin sees accounts, researchers see codes (one route, two views).
    $routes->group('', ['filter' => 'staffCan:participants.accounts,participants.research'], static function ($routes): void {
        $routes->get('peserta', 'ParticipantController::index');
        $routes->get('peserta/(:num)', 'ParticipantController::show/$1');
    });
    $routes->post('peserta/(:num)/reset-sandi', 'ParticipantController::resetPassword/$1', ['filter' => 'staffCan:participants.reset_by_id']);
    $routes->group('', ['filter' => 'staffCan:participants.reset_by_code'], static function ($routes): void {
        $routes->get('peserta/reset-sandi', 'ParticipantController::resetByCodeForm');
        $routes->post('peserta/reset-sandi', 'ParticipantController::resetByCode');
    });

    // Research data of all students: head researcher only.
    $routes->group('', ['filter' => 'staffCan:research.browse'], static function ($routes): void { /* sesi, data, masukan */ });
    $routes->group('', ['filter' => 'staffCan:research.export'], static function ($routes): void { /* ekspor */ });

    // Content: GET for everyone with content.view, writes for content.edit.
    $routes->group('', ['filter' => 'staffCan:content.view'], static function ($routes): void { /* GET konten, narasi, media */ });
    $routes->group('', ['filter' => 'staffCan:content.edit'], static function ($routes): void { /* POST, impor bank */ });

    $routes->get('studi', 'StudyController::index', ['filter' => 'staffCan:studies.view']);
    $routes->group('', ['filter' => 'staffCan:studies.manage'], static function ($routes): void { /* POST studi */ });
    $routes->group('', ['filter' => 'staffCan:releases.manage'], static function ($routes): void { /* studi/rilis… */ });
    // tata-kelola: deletion.execute, audit.view, retention.run; sekolah: schools.manage; staf: staff.manage
});
```

Catatan:
- `peserta/reset-sandi` dan `peserta/salinan` (bagian 4) tidak bertabrakan dengan `peserta/(:num)`, karena `(:num)` hanya angka. Begitu juga `sesi-lapangan/baru` (bagian 2) dengan `sesi-lapangan/(:num)`. Tetap daftarkan rute tetap sebelum rute berpola, supaya urutannya jelas.
- Halaman impor bank (`konten/impor-bank`, termasuk `GET` pratinjau dan templat) masuk `content.edit`, karena isinya alur mengubah.
- Rute `api/admin/*` mendapat filter per rute sesuai [§4](#4-hak-akses-per-peran).
- Harapan di `tests/unit/RouteWiringTest.php` (sekarang `['staffAuth','staffRole:admin']`) ditulis ulang per kelompok.

---

## 8. Panel peneliti

Panel peneliti adalah panel staf yang sama dengan menu yang berbeda, bertampilan panel admin dan bukan game ([18](18_MOCKUP_LAYAR_BARU.md), `mk-tk-05`).

**Masuk dan kepala halaman**
- Masuk lewat `/admin/login` yang sama, berjudul "Masuk Panel Staf".
- Label peran di kepala halaman: "Admin", "Peneliti", "Ketua peneliti".
- Studi yang sedang berjalan tetap tampil di kepala halaman untuk semua peran.

**Menu per peran** (urutan kelompok tetap: Data, Konten, Pengaturan)

| Kelompok | Menu | Admin | Peneliti | Ketua |
| --- | --- | --- | --- | --- |
| — | Ringkasan | Ya | Ya | Ya |
| Data | Siswa | Ya (akun) | Ya (kode) | Ya (kode) |
| Data | Atur ulang kata sandi siswa | — | Ya | Ya |
| Data | Sesi lapangan, Pemantauan (bagian 2) | — | Ya | Ya |
| Data | Sesi bermain, Data mentah, Kritik dan saran | — | — | Ya |
| Data | Unduh data | — | — | Ya |
| Data | Log teknis (bagian 3) | Ya | — | — |
| Data | Permintaan data (bagian 4) | Ya | Ya (miliknya) | Ya |
| Data | Tindak lanjut (bagian 4) | — | — | Ya |
| Konten | Konten, Impor bank soal, Narasi, Media, Kelengkapan aset | Ya | Lihat (tanpa Impor bank soal) | Lihat (tanpa Impor bank soal) |
| Pengaturan | Studi | Lihat | — | Ya |
| Pengaturan | Rilis, Sekolah, Akun staf, Tata kelola | Ya | — | — |

**Ringkasan per peran**
- **Admin:** kesiapan sistem, peringatan retensi, ringkasan teknis. Tanpa angka partisipasi per siswa.
- **Peneliti dan ketua:** angka partisipasi berkode per kode sekolah, tanpa nama sekolah. Kartu dari bagian 2 (sesi lapangan aktif) menyusul.
- Daftar "20 event terakhir" dan "10 siswa terakhir" di ringkasan sekarang memakai nama sekolah (`DashboardController.php:130`). Untuk peneliti dan ketua diganti kode sekolah; untuk admin daftar event dihapus, karena event adalah data penelitian.

**Pencarian cepat**
- Peneliti dan ketua mencari dengan kode peserta saja.
- Admin mencari dengan kode, nama pengguna, atau nama.

**Kode sekolah.** Sampai bagian 2 menyediakan kode sekolah penelitian, sekolah tampil sebagai `SCH-000123`. Bagian 2 mengganti tampilannya ke `S03` di satu tempat (`RawDataService::schoolRef()` atau penggantinya).

---

## 9. Data yang terlihat per peran

| Data | Admin | Peneliti | Ketua |
| --- | --- | --- | --- |
| Nama, nama pengguna siswa | Ya | — | — |
| Nama sekolah, NPSN, provinsi, kabupaten | Ya | — | — |
| Kode peserta, kode sekolah (`school_ref`) | Ya | Ya | Ya |
| Status akun (aktif, wajib ganti kata sandi) | Ya | — | — |
| Usia, jenis kelamin, kelas, rombel | Ya (akun) | Ya | Ya |
| Kemajuan, pengerjaan, jawaban, percobaan, bantuan, navigasi, waktu | — | Satu siswa lewat halaman siswa | Semua siswa |
| Kritik dan saran | — | — | Ya |
| Sesi bermain dan event | — | — | Ya |
| Nama OS dan browser | Ya (log teknis, bagian 3) | — | — |
| Jenis perangkat dan ukuran layar | — | Ya | Ya |
| Log audit staf | Ya | — | Riwayat unduhannya sendiri |
| Unduhan data | — | — | Ya, berkode |
| Penanda tindak lanjut butir risiko daring (bagian 4) | — | — | Ya, di layar saja |

**Halaman siswa dibelah.**
- **Admin** melihat kartu akun: kode, nama pengguna, nama, sekolah, status, tombol atur ulang kata sandi. Tab data penelitian tidak ada.
- **Peneliti dan ketua** melihat kode, kode sekolah, kovariat, dan tab data penelitian, tanpa nama dan nama pengguna. Judul halaman memakai kode peserta.

**Daftar siswa.**
- Admin: kolom akun.
- Peneliti dan ketua: kolom kode dan kemajuan.
- CSV daftar siswa ikut kolom tampilannya dan dicatat sebagai `list_csv`. Lihat [§16](#16-pertanyaan-terbuka) no. 4 untuk CSV admin.

**Lembar `event` dan kolom `answer_key`.** Sekarang hanya untuk admin. Sesudah bagian 1, admin tidak lagi melihat data penelitian. Bagian 3 memberi keduanya ke `ketua` ([29 → Keputusan yang dikunci](29_IMPLEMENTASI_DATA_PENELITIAN.md#2-keputusan-yang-dikunci), K1): lembar `event` hanya event bertanda Peneliti dengan kode sebagai pengganti id internal, `answer_key` di lembar `butir` dan `jawaban`. Admin tidak mendapat keduanya.

**Jawaban butir risiko daring (ang-21–ang-23).** Tidak pernah per baris di unduhan; `ketua` hanya mendapat lembar angka gabungan, dengan sel di bawah 5 siswa ditulis `<5` ([29 → Butir risiko daring](29_IMPLEMENTASI_DATA_PENELITIAN.md#76-butir-risiko-daring), K9).

---

## 10. Mengatur ulang kata sandi lewat kode peserta

**Alur**
1. P1 mencari kode peserta siswa di L2, lalu membuka **Atur ulang kata sandi siswa** (`/admin/peserta/reset-sandi`).
2. P1 mengetik kode peserta. Huruf besar-kecil tidak dibedakan dan spasi di tepi dibuang. P1 juga mengetik `RESET` sebagai konfirmasi, sama dengan alur admin sekarang.
3. Bila kode tidak ditemukan, halaman menjawab: "Kode peserta tidak ditemukan. Periksa lagi kodenya di L2."
4. Bila ditemukan, `SessionService::resetPasswordByStaff()` membuat kata sandi sementara (16 karakter) dan memasang `must_change_password`.
5. Halaman hasil menampilkan **kode peserta dan kata sandi sementara saja**, dengan `Cache-Control: no-store`, tanpa nama dan nama pengguna. Tombol "Selesai" kembali ke formulir kosong.
6. Log audit mencatat `participant_password_reset` dengan metadata `{participant_code, via: "kode"}`.
7. Saat siswa masuk dengan kata sandi sementara, game meminta kata sandi baru (alur sekarang, `/ganti-sandi`).

**Catatan**
- Siswa yang belum punya izin tidak punya akun ([01 → Kerja lapangan](01_REVISI_10_ANALITIK.md#kerja-lapangan-cara-kerjanya)), jadi tidak ada kasus khusus untuk mereka.
- Akun `uji.*` di instalasi uji diatur ulang lewat jalan yang sama oleh akun `ketua` pengembang, atau lewat `gelita:uji:akun` (bagian 2, perubahan no. 24).
- Bentuk kode sekarang masih bentuk lama. Formulir ini tidak memeriksa pola `S03-A07`, jadi tetap bekerja setelah bagian 2.

---

## 11. Akun staf dan perintah spark

**Halaman akun staf (`/admin/staf`)**
- Pilihan peran: Admin, Peneliti, Ketua peneliti, masing-masing dengan satu kalimat penjelasan.
- Kolom sekolah dihapus.
- Admin tetap tidak bisa menurunkan perannya sendiri atau menonaktifkan dirinya.
- Tidak ada tombol hapus akun (K10).

**Akun per peran** ([03 → Akun staf](03_KODE_DAN_ARTI.md#akun-staf); dibuat kalau perannya butuh akun)

| Akun (nama samaran) | Peran | Dipegang |
| --- | --- | --- |
| `admin` | `admin` | Pengelola sistem |
| `koordinator` | `peneliti` | P1 Koordinator sesi |
| `pengamat` | `peneliti` | P2 Pengamat keterlibatan |
| `pewawancara` | `peneliti` | P3 Pengamat bantuan dan pewawancara |
| `ketua` | `ketua` | Ketua peneliti |

Siapa orangnya dicatat ketua peneliti di luar repo, sama seperti sekarang.

**Perintah spark**
- `gelita:reset`: opsi `--hapus-guru` dan hitungan guru dihapus (`app/Commands/SystemReset.php`, `SystemResetService`). Rencana reset mencetak jumlah akun staf per peran (`admin`, `peneliti`, `ketua`). Semua akun staf tetap.
- `gelita:staff:password`: tidak berubah, berlaku untuk semua peran.
- Pencarian "admin aktif pertama" (`RetentionService`, `SystemResetService`, `BankImport`, `KontenKelas5Seeder`) tidak berubah. `Simulator.php` yang mengambil staf pertama dari peran apa pun diubah ke admin aktif pertama.

**Nama pengguna siswa yang dicadangkan.** `Config\Gelita::$reservedUsernames` tetap memuat `guru`, ditambah `peneliti` dan `ketua`, supaya siswa tidak memakai nama yang mirip peran staf.

**Instalasi uji** ([20 → Prasyarat no. 8](20_PAKET_VALIDASI.md#prasyarat-no-8-instalasi-uji-dan-akun-uji)). Di tahap 2 cukup akun `admin` pengembang. Di G3 (ahli media langkah 2), pengembang memegang dua akun, `admin` dan `ketua`, sesuai K9. Validator tidak pernah mendapat akun staf.

### 11.1 Keamanan masuk staf

Diputuskan pengembang 30 Oktober 2026 (K15, [§17](#17-keputusan-pengembang) no. 5).

**Aturan kata sandi**
- Kata sandi staf memakai aturan `strong_password[username]` yang sudah dipakai siswa (`GelitaRules::strong_password()`, di `RegisterController` dan `LoginController`).
- Panjang minimal 12 karakter yang sekarang berlaku untuk staf tetap dipakai.
- Aturan ini berlaku di semua tempat yang menetapkan kata sandi staf:
  - `AccountController::changePassword()` (ganti sendiri);
  - `StaffController` (kata sandi awal dan atur ulang oleh admin);
  - `gelita:staff:password`.
- Kata sandi lama yang tidak memenuhi aturan tidak ditolak saat masuk. Aturannya dipakai saat kata sandi dibuat atau diganti berikutnya.

**Batas waktu sesi staf**
- Sesi staf berakhir setelah 2 jam tanpa aktivitas.
- `StaffAuthFilter` menyimpan waktu permintaan terakhir di sesi (`staff_last_seen`). Bila selisihnya lebih dari batas, sesi staf diakhiri, log audit mencatat `staff_idle_logout`, dan halaman masuk menampilkan: "Sesi berakhir karena tidak ada aktivitas selama 2 jam. Silakan masuk lagi."
- Batasnya pengaturan `Config\Gelita::$staffIdleSeconds = 7200`.
- Sesi siswa tidak terpengaruh. `Config\Session::$expiration` (4 jam) tetap.
- Permintaan API latar (`/api/admin/summary` yang diperbarui otomatis, pemantauan bagian 2) tidak dihitung sebagai aktivitas, supaya tab yang dibiarkan terbuka tetap berakhir.

**Tanpa verifikasi dua langkah.** Yang sudah ada tetap: kunci 15 menit setelah 5 kali gagal, kata sandi berhash, HTTPS, dan log audit masuk.

---

## 12. Teks antarmuka

Mengikuti `.claude/rules/ui-copy.md`: kalimat panel sopan dan polos, kalimat siswa hangat dengan "kamu". Kata "reset" tidak tampil; dipakai "atur ulang". Teks akhir ditetapkan saat implementasi.

**Panel (`app/Language/id/Admin.php`, tanpa pasangan bahasa Inggris)**

| Kunci | Sekarang | Usulan |
| --- | --- | --- |
| `loginTitle` | Masuk Panel Guru & Admin | Masuk Panel Staf |
| `roleGuru` | Guru | dihapus |
| `rolePeneliti` (baru) | — | Peneliti |
| `roleKetua` (baru) | — | Ketua peneliti |
| `quickSearchTeacher` | Cari kode siswa | diganti nama menjadi `quickSearchCode`, teks sama |
| `menuResetByCode` (baru) | — | Atur ulang kata sandi siswa |
| `resetByCodeHelp` (baru) | — | Ketik kode peserta dari L2. Kata sandi sementara hanya tampil sekali. |
| `resetCodeNotFound` (baru) | — | Kode peserta tidak ditemukan. Periksa lagi kodenya di L2. |
| `roleHelpPeneliti` (baru) | — | Kerja lapangan: sesi, isian L2, atur ulang kata sandi lewat kode. Tidak bisa mengunduh data. |
| `roleHelpKetua` (baru) | — | Semua hak peneliti, ditambah data semua siswa, unduh data, pengaturan studi, konfirmasi permintaan hapus data, dan penanda tindak lanjut. |
| `roleHelpAdmin` (baru) | — | Sistem, akun, konten, rilis, sekolah, dan log audit. Tidak melihat data penelitian. |
| Label audit yang menyebut "akun guru/admin" | — | "akun staf" |
| `staffIdleLogout` (baru) | — | Sesi berakhir karena tidak ada aktivitas selama 2 jam. Silakan masuk lagi. |
| `staffPasswordWeak` (baru) | — | Kata sandi belum cukup kuat. Pakai minimal 12 karakter dengan huruf besar, huruf kecil, angka, dan simbol, dan jangan memuat nama pengguna. |

**Game (siswa), `id` dan `en`**

| Kunci | Sekarang (id) | Usulan (id) | Usulan (en) |
| --- | --- | --- | --- |
| `Auth.mustChange` | Gurumu sudah mengatur ulang kata sandimu. Buat kata sandi baru yang kuat, ya. | Kakak peneliti sudah mengatur ulang kata sandimu. Buat kata sandi baru yang kuat, ya. | The research team has reset your password. Make a new strong password. |
| `Auth.currentHelp` | Pakai kata sandi sementara dari gurumu, atau kata sandimu yang sekarang. | Pakai kata sandi sementara dari kakak peneliti, atau kata sandimu yang sekarang. | Use the temporary password from the research team, or your current password. |
| `Game.forgotPassword` | Lupa kata sandi? Minta gurumu mengatur ulang. | Lupa kata sandi? Minta kakak peneliti mengatur ulang, ya. | Forgot your password? Ask the research team to reset it. |
| `Auth.mustChangeAdmin` (baru) | — | Admin GELITA sudah mengatur ulang kata sandimu. Buat kata sandi baru yang kuat, ya. | The GELITA admin has reset your password. Make a new strong password. |
| `Auth.currentHelpAdmin` (baru) | — | Pakai kata sandi sementara dari admin GELITA, atau kata sandimu yang sekarang. | Use the temporary password from the GELITA admin, or your current password. |
| `Game.forgotPasswordAdmin` (baru) | — | Lupa kata sandi? Minta tolong admin GELITA mengatur ulang, ya. | Forgot your password? Ask the GELITA admin to reset it. |

"Kakak peneliti" mengikuti pesan game tertutup di [01 → Token sesi](01_REVISI_10_ANALITIK.md#token-sesi-perlu-atau-tidak). Tiga kunci pertama dipakai selama mode penelitian berjalan (`field_mode` menyala dan studi `active`, [28 → Mode penelitian](28_IMPLEMENTASI_SESI_LAPANGAN.md#51-sakelar-field_mode)). Di luar itu, termasuk setelah studi ditutup, yang mengatur ulang adalah admin, dan server memilih kunci `…Admin` (diputuskan pengembang 30 Oktober 2026, [28 → Pertanyaan terbuka](28_IMPLEMENTASI_SESI_LAPANGAN.md#21-pertanyaan-terbuka) no. 8). "Admin GELITA" dipakai, bukan "admin" saja, supaya anak tahu admin yang mana; kalimat akhir mengikuti uji keterbacaan.

---

## 13. Kode yang dihapus

| Yang dihapus | Tempat |
| --- | --- |
| Cakupan guru | `Services\StaffScope::teacher()`, `withAnonymous()`; `Concerns\StaffScope::schoolScope()`, cabang `dataScope()` untuk guru |
| Sekolah milik staf | `StaffUser::schoolScope()`; aturan sekolah di `StaffController` (`postedSchool()`); penanganan `staff_users.school_id` di `SchoolModel`, `SchoolDirectory` (gabung sekolah), `SchoolController` |
| Filter sekolah terkunci | Cabang "sekolah sendiri" di `BaseAdminController` dan `components/admin-filter-bar.php` |
| Peran di rute | Alias `staffRole` dan `StaffRoleFilter` (diganti `staffCan`) |
| Reset guru | Opsi `--hapus-guru` dan hitungan guru di `SystemReset`/`SystemResetService` |
| Teks | `Admin.roleGuru`; komentar yang menyebut guru (sekitar 40, `grep -rn guru app/`) ditulis ulang dalam bahasa Inggris saat bloknya disentuh |
| Unduh dengan identitas | Mode non-anonim di `ExportController`/`ExportService` |

---

## 14. Pengujian dan kriteria selesai

### 14.1 Tes baru

| Tes | Yang diperiksa |
| --- | --- |
| `StaffAccessTest` | Setiap kemampuan hanya memuat peran dari `ROLES`; peran atau kemampuan tak dikenal ditolak; admin tidak memegang kemampuan `research.*`; peneliti tidak memegang `research.export` dan `studies.manage` |
| `StaffRouteMatrixTest` | Untuk setiap kelompok rute di [§4](#4-hak-akses-per-peran) dan setiap peran: 200 bila berhak, 404 untuk halaman dan 403 untuk API bila tidak |
| `ResearcherNeverSeesIdentityTest` | Daftar siswa, halaman siswa, sesi, kritik dan saran, data mentah, CSV, dan unduhan untuk peneliti dan ketua tidak memuat nama, nama pengguna, nama sekolah, atau NPSN siswa contoh |
| `AdminHasNoResearchDataTest` | Admin mendapat 404 di sesi, data mentah, kritik dan saran, dan unduh data; halaman siswa admin tanpa tab data penelitian |
| `ResetByCodeTest` | Kode benar memberi kata sandi sementara, `must_change_password`, `no-store`, dan baris audit `via: kode`; kode salah memberi pesan ramah tanpa perubahan; halaman hasil tanpa nama pengguna; admin mendapat 404 |
| `StudyAccessTest` | Ketua mengubah studi; admin melihat formulir terkunci dan `POST`-nya 404; peneliti 404 |
| `ReplaceTeacherMigrationTest` | Akun guru, unduhannya beserta berkas, dan permintaannya terhapus; baris audit tetap dengan pelaku kosong; `school_id` hilang; peran wajib diisi |
| `StaffRolesTest` | Membuat dan mengubah akun dengan tiga peran; peran `guru` atau kosong ditolak; admin tidak bisa menurunkan perannya sendiri. Belum ada tes untuk ini sekarang. |
| `StaffLoginSecurityTest` | Kata sandi staf yang lemah atau memuat nama pengguna ditolak di ganti sendiri, akun baru, dan `gelita:staff:password`. Sesi staf yang diam lebih dari `staffIdleSeconds` diakhiri dengan audit `staff_idle_logout`. Permintaan API latar tidak memperpanjang sesi. Sesi siswa tidak terpengaruh. |

### 14.2 Tes yang ditulis ulang

Semua tes yang sekarang memakai akun guru beralih ke `peneliti` atau `ketua`, sesuai apa yang diuji:
- `AdminTableViewTest`, `ParticipantPanelTest`, `RawExportTest`, `ExportRulesTest`, `AdminTableTest`;
- `NarrationPagesTest`, `PackageSwitchTest`, `SystemResetTest`, `StaffPasswordGateTest`, `SchoolDirectoryTest`, `DebugToolbarRedactionTest`, `TelemetryBatchTest`, `RouteWiringTest`;
- perlengkapan tes `tests/_support/Database/ResetTables.php` (bawaan `guru`), `RawDataWorld.php` (`guru_alpha`), `GradeFiveWorld.php`, `SchoolTables.php`;
- skrip QA `tools/qa/admin-check.mjs` (opsi `--guru` menjadi `--peneliti` dan `--ketua`).

### 14.3 Cek manual

Di mesin pengembangan, masuk sebagai tiap peran:
- **Admin:** buat satu akun `peneliti` dan satu `ketua`, atur ulang kata sandi siswa lewat id, pastikan menu Data hanya berisi Siswa.
- **Peneliti:** atur ulang kata sandi lewat kode, buka satu halaman siswa, pastikan tidak ada nama dan menu Unduh data.
- **Ketua:** unduh data, ubah studi, pastikan tidak ada nama di unduhan.

### 14.4 Selesai bila

- `vendor/bin/phpunit --no-coverage` hijau, termasuk tes baru di 14.1.
- `grep -rn "'guru'" app/ tests/` hanya menemukan daftar nama pengguna yang dicadangkan, migrasi lama, dan migrasi `003400`.
- Cek manual 14.3 lolos untuk ketiga peran.
- Dokumen di [§15](#15-dokumen-yang-ikut-diperbarui-saat-implementasi) diperbarui di cabang yang sama.

---

## 15. Dokumen yang ikut diperbarui saat implementasi

- `docs/07_FEATURE_INTEGRATION.md`: FITUR 2a (*Reset oleh Guru*), FITUR 10 dan 10a–10c (panel staf, akun, studi dan rilis, sekolah), FITUR 14–18 (ekspor, PDF, tata kelola, retensi, `gelita:reset`), aturan sistem.
- `docs/01_DATABASE.md`, `docs/02_PROJECT_FOUNDATION.md`, `docs/03_MODEL_ENTITY.md`, `docs/04_CONTROLLER_ROUTE.md`, `docs/05_VIEW_UI.md`, `docs/08_DEPLOYMENT.md`, `README.md`: setiap penyebutan peran `guru`.
- `docs/perombakan-kelas5/03_TAMPILAN_16x9_DAN_ADMIN.md` §8.3–8.4 dan §12.1: diberi catatan bahwa menu dan cakupan guru diganti dokumen ini.
- `.claude/rules/glossary.md`: baris "guru, admin, staf" menjadi "admin, peneliti, ketua peneliti, staf" dengan nilai tersimpan `admin`, `peneliti`, `ketua`.
- `docs/PROGRESS.md` dan catatan keputusan implementasi.

---

## 16. Pertanyaan terbuka

Tidak menahan draf ini; dijawab pengembang atau di bagian yang disebut.

1. ~~**Keamanan masuk staf.**~~ **Diputuskan pengembang 30 Oktober 2026:** aturan kata sandi kuat yang sama dengan siswa, sesi staf berakhir setelah 2 jam tanpa aktivitas, tanpa verifikasi dua langkah ([§11.1](#111-keamanan-masuk-staf), K15).
2. ~~**Siapa mengajukan permintaan hapus data.**~~ **Diputuskan pengembang 30 Oktober 2026 di bagian 4:** `peneliti` dan `ketua` mengajukan (`deletion.request`); permintaan dari `peneliti` dikonfirmasi `ketua` lebih dulu (`deletion.confirm`); admin tetap satu-satunya yang menjalankan ([30 → Keputusan yang dikunci](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#2-keputusan-yang-dikunci), K1; [30 → Keputusan pengembang](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#22-keputusan-pengembang) no. 1).
3. ~~**Admin dan panel pemantauan.**~~ **Diputuskan pengembang 30 Oktober 2026 di bagian 2:** admin melihat angka saja per sesi lapangan, tanpa kode peserta dan tanpa token ([28 → Panel pemantauan](28_IMPLEMENTASI_SESI_LAPANGAN.md#12-panel-pemantauan), K2).
4. **CSV daftar siswa untuk admin.** Sekarang admin bisa mengunduh daftar dengan nama. Usulan: CSV admin dihapus, nama hanya di layar, untuk meminimalkan salinan identitas. Daftar izin berkode untuk penutupan studi ditetapkan di bagian 4 ([30 → Daftar izin berkode](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#101-daftar-izin-berkode)); pertanyaan CSV admin tetap terbuka.
5. ~~**Jawaban butir risiko daring per baris.**~~ **Diputuskan di bagian 3:** jawaban ang-21–ang-23 tidak pernah per baris di unduhan; hanya lembar angka gabungan, sel di bawah 5 siswa ditulis `<5` ([29 → Butir risiko daring](29_IMPLEMENTASI_DATA_PENELITIAN.md#76-butir-risiko-daring), K9). Penanda tindak lanjut dihitung di layar `ketua` dan tidak diekspor ([30 → Penanda tindak lanjut](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#6-penanda-tindak-lanjut-butir-risiko-daring), K7).
6. **"Hanya ketua yang menyambungkan kode ke nama"** (03, 22, 26) hanya berlaku sebagai prosedur. Secara teknis P1 memegang L7 dan admin memegang akun. Dokumen ini tidak bisa menutupnya di sistem; cukup dicatat di 22 dan 26 sebagai pembatasan prosedur.
7. **Akun `pengamat` dan `pewawancara`.** P2 tidak punya tindakan di sistem menurut 02; P3 membuka jawaban satu siswa untuk wawancara L5. Keduanya tetap `peneliti` dan dibuat hanya bila perlu.

---

## 17. Keputusan pengembang

Satu putaran tanya, empat pertanyaan (30 Oktober 2026); pengembang memilih usulan di keempatnya. `asal: pengembang`.

1. **Dua peran peneliti: `peneliti` dan `ketua`** (K1, K2). Tidak dipilih:
   - satu peran `peneliti` untuk semua akun, dengan penanda ketua hanya untuk daftar tindak lanjut butir risiko (semua boleh mengunduh);
   - satu peran dengan centang hak per akun yang diatur admin.
2. **Akun `guru` dihapus di migrasi**, bersama berkas unduhan dan permintaan hapus datanya; baris audit tetap dengan pelaku kosong; `staff_users.school_id` dibuang (K11). Tidak dipilih: akun guru dibiarkan terkunci dengan nilai peran lama dan kolom sekolah tetap.
3. **Studi diubah `ketua`, dilihat `admin`** (K12). Rilis dan paket aset tetap milik admin. Di instalasi uji, pengembang memegang dua akun. Tidak dipilih: studi tetap di tangan admin dengan peneliti hanya melihat; admin dan ketua sama-sama boleh mengubah.
4. **Draf ini ditulis di cabang yang sama dengan keputusan empat bagian** (`sky/practical-knuth-x5vyu0`, PR #282). Tidak dipilih: menggabung PR #282 dulu lalu membuka cabang baru.
5. **Keamanan masuk staf** (K15, [§11.1](#111-keamanan-masuk-staf)). Usulan di pertanyaan terbuka no. 1 disetujui pengembang 30 Oktober 2026 dalam pesan terpisah:
   - aturan kata sandi kuat yang sama dengan siswa;
   - sesi staf berakhir setelah 2 jam tanpa aktivitas;
   - tanpa verifikasi dua langkah.

---

## 18. Akibat ke dokumen lain

Dicatat di sini dan dibawa sesi koordinator setelah bagian 2–4 selesai, supaya dokumen lain tidak diubah dua kali.

**Status per 30 Oktober 2026:** semua baris sudah dibawa sesi koordinator (PR #285, #286, #287), kecuali yang menunggu `kamus-1.1` atau implementasi.

| Dokumen | Bagian | Perubahan | Status |
| --- | --- | --- | --- |
| [01](01_REVISI_10_ANALITIK.md) | *Admin dan peneliti: dua peran berbeda* | Tabel hak akses mendapat kolom `ketua` (unduh data, studi, data semua siswa, penanda tindak lanjut). Baris "Data penelitian dan ekspornya: Peneliti Ya" menjadi "Ketua". Kalimat "peran guru makin sedikit" di *Token sesi* diganti. | Sudah (sesi koordinator, 30 Oktober 2026, PR #285) |
| [03](03_KODE_DAN_ARTI.md) | *Akun staf* | `ketua` berperan sistem `ketua` (bukan Peneliti); "panel peneliti (usulan)" menjadi panel yang ditetapkan di 27 | Sudah (sesi koordinator, 30 Oktober 2026, PR #285) |
| [22](22_PERMOHONAN_IZIN_ETIK.md) | *Kerahasiaan dan pengelolaan data*, *Identitas dan tim* | Unduh data hanya lewat akun `ketua`; pembatasan "hanya ketua menyambungkan kode ke nama" ditulis sebagai prosedur ([§16](#16-pertanyaan-terbuka) no. 6) | Sudah (sesi koordinator, 30 Oktober 2026, PR #286) |
| [26](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md) | *Tim dan staf*, *Aliran data*, langkah P2 | Sama dengan 22: unduhan lewat akun `ketua`; tiga peran staf | Sudah (sesi koordinator, 30 Oktober 2026, PR #286) |
| [20](20_PAKET_VALIDASI.md) | *Prasyarat no. 8* | Di G3, pengembang memegang akun `admin` dan `ketua` di instalasi uji | Sudah (sesi koordinator, 30 Oktober 2026, PR #285) |
| [23](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md) | T10, pilihan D | "`ketua` membuka kode" sesuai hak `ketua` di 27; tidak ada perubahan teks surat | Tidak perlu diubah |
| `.claude/rules/glossary.md` | *People, data, and the panel* | Baris peran staf, saat implementasi ([§15](#15-dokumen-yang-ikut-diperbarui-saat-implementasi)) | Saat implementasi |
