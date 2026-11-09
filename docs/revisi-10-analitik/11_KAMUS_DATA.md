# Kamus Data Penelitian GELITA

**Judul penelitian:** *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*

Per 9 November 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · `kamus-1.1` (`disetujui pengembang` 9 November 2026; draf 3 November 2026) di atas `kamus-1.0` (disetujui pengembang 26 Oktober 2026, daftar kerja no. 2); ambang jeda dan tebakan cepat masih sementara

> **`kamus-1.1`: `disetujui pengembang` (9 November 2026, [04](04_CATATAN_KEPUTUSAN.md)), `asal: pengembang`** (disusun dengan bantuan AI, 3 November 2026), ditulis sekarang atas keputusan pengembang 2 November 2026 ([04](04_CATATAN_KEPUTUSAN.md); [35 → Keputusan pengembang](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#19-keputusan-pengembang) no. 6). Isinya usulan [35 §8.2](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#82-isi-yang-diusulkan) dan variabel yang diserahkan 13, 16, 17, dan dokumen implementasi 28–34. **Tidak ada definisi `kamus-1.0` yang diubah**: yang baru menambah sumber data, variabel, dan penegasan, dan ditandai *(1.1)*. Daftarnya di [Yang baru di `kamus-1.1`](#yang-baru-di-kamus-11). Teks `kamus-1.0` tetap bisa dibaca di riwayat git (*commit* `751522a`).
>
> Angka ambang, batas `game_time_group`, dan urutan alasan ditetapkan ulang setelah uji kelompok kecil dan menengah (KG3a, KG3b) sebagai versi baru `kamus-1.2`, dengan `asal: peneliti` bila peneliti yang menetapkannya. Versi dikunci di G4 bersama versi game.

Kamus ini membekukan **cara menghitung** setiap data penelitian: peta bukti, 10 analitik, butir yang dinilai, "dilewati", waktu aktif, data tersensor, tebakan cepat, kode, dan aturan menggabungkan log dengan lembar lapangan. Dokumen [01](01_REVISI_10_ANALITIK.md) menjelaskan *apa* yang diukur dan *kenapa*; kamus ini menjelaskan *bagaimana* angkanya didapat, sampai bisa dihitung ulang orang lain dari data mentah. Arti setiap kode tetap di [03](03_KODE_DAN_ARTI.md); di sini hanya aturan memakainya.

Kamus ini sendiri tidak mengubah sistem. Sejak 1 November 2026 sistem dibangun sekarang ([04](04_CATATAN_KEPUTUSAN.md)); lapis data turunan yang menghitung kamus ini dirancang di [35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md), ekspornya di [29](29_IMPLEMENTASI_DATA_PENELITIAN.md). Nama kolom ditulis dalam bahasa Inggris karena menjadi nama kolom di lapis data turunan dan ekspor peneliti (perubahan sistem no. 9 dan 14). Kolom yang **sudah ada** di sistem sekarang ditandai *(ada)*; sisanya baru. Keputusan pengembang atas draf kamus ini dikumpulkan di [Keputusan pengembang](#keputusan-pengembang).

## Ringkasan

1. **Data inti analisis adalah percobaan pertama di pengerjaan pertama**, yaitu pengerjaan yang memakai variasi buklet siswa. Pengerjaan ulang disimpan, tapi tidak dinilai.
2. **Butir tanpa percobaan adalah data hilang, bukan salah.** Butir yang belum berhasil setelah tiga kesempatan adalah data **tersensor**, bukan angka 3.
3. **"Dilewati" dihitung dalam tiga jenis terpisah:** (a) butir ditutup setelah kesempatan habis di tantangan yang kesempatannya per butir (Cari Objek dan Skenario Digital), (b) pengerjaan ditinggal dan tidak dilanjutkan di sesi lapangan yang sama, (c) tantangan yang sudah terbuka tapi tidak pernah dimulai.
4. **Waktu aktif** = waktu total dikurangi selang layar tersembunyi dan bagian jeda tanpa masukan yang melebihi ambang (sementara 3 menit). Narasi yang sedang diputar dihitung aktif.
5. **Setiap percobaan salah membawa tepat satu kode isi** dari bank soal. Tebakan cepat (NON-TEB) dan jawaban terakhir saat kesempatan habis (NON-HABIS) adalah penanda tambahan yang dihitung sistem, bukan pengganti kode di data mentah.
6. **Ambang masih sementara:** jeda 3 menit dan tebakan cepat di bawah 3 detik. Keduanya pengaturan yang mudah diubah tanpa mengubah sistem, ditetapkan ulang setelah uji coba, lalu dibekukan di gerbang G4 bersama pra-registrasi.
7. *(1.1)* **Sumber data dan variabel baru, definisi tetap.** `consent_ok` dihitung dari daftar izin berkode di sistem, dan data siswa yang mundur dengan data tetap dihitung sampai saat mundur. Waktu putus memakai `ready_ms`. Analisis memakai kode kesalahan dari bank terbaru. Variabel tes awal–akhir, angket, dan skala sikap masuk kamus, tetapi jawaban butir risiko daring hanya sebagai angka gabungan. Setiap putaran hitung lapis turunan mencatat versi kamus, nomor putaran, dan ambang yang dipakai.

## Versi dan cara berubah

| Versi | Kapan | Isi |
| --- | --- | --- |
| Draf | 25 Oktober 2026 | Disusun AI, dengan empat pertanyaan untuk pengembang |
| `kamus-1.0` | 26 Oktober 2026, disetujui pengembang | Semua definisi beku. Ambang jeda (3 menit) dan tebakan cepat (3 detik) masih sementara |
| `kamus-1.1` draf | 3 November 2026, `draf pengembang` | Sumber data, variabel, dan penegasan dari 13, 16, 17, dan 28–35 ([Yang baru di `kamus-1.1`](#yang-baru-di-kamus-11)). Selama belum disetujui, sistem menulisnya `kamus-1.1-usulan` di lembar *Kamus Data*, dengan catatan "usulan, belum disetujui" ([35 §8.1](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#81-cara-versinya-berjalan)) |
| `kamus-1.1` | 9 November 2026, `disetujui pengembang` | Isi draf 3 November 2026 tanpa perubahan; sistem menulis `kamus-1.1` tanpa catatan usulan. Ambang tetap sementara (jeda 3 menit, tebakan cepat 3 detik, tes 3 detik) |
| `kamus-1.2` | Setelah KG3a dan KG3b, sebelum G4 | Ambang jeda dan tebakan cepat (kolom studi, [Analitik 5](#analitik-5-waktu-pengerjaan)), batas `game_time_group`, ambang tebakan cepat tes, dan urutan alasan ditetapkan dari data uji coba; entri 04 dengan asalnya |
| Beku | Sejak G4 sampai analisis selesai | Dikunci bersama versi game, dan tidak berubah selama pengambilan data utama (KG5) ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md) K17). Kesalahan yang ketahuan kemudian dicatat sebagai penyimpangan, dan analisisnya dijalankan dengan aturan lama dan baru |

Setiap tabel data turunan menyimpan kolom `dictionary_version`, supaya angka di naskah bisa ditelusuri ke versi kamus yang dipakai. *(1.1)* Setiap tabel juga menyimpan `run_id` putaran hitungnya, dan ambang yang dipakai tercatat per putaran (`derived_runs`, [35 §4](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#4-bentuk-lapis-turunan)). Mengubah versi kamus atau ambang membuat putaran lama basi, jadi unduhan berikutnya dihitung ulang. Setiap perubahan dicatat di [04](04_CATATAN_KEPUTUSAN.md).

**Nilai yang ditulis sistem (9 November 2026):** selama `kamus-1.1` masih draf, unduhan dan lapis turunan menulis `kamus-1.1-usulan` (`ResearchDictionary::VERSION`; [35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#19-keputusan-pengembang) §19 no. 14). Sejak disetujui pengembang 9 November 2026 nilainya `kamus-1.1`, status entri baru `baru`, dan lembar *Kamus Data* tidak lagi memuat baris *Status kamus*; putaran hitung lama menjadi basi dan dihitung ulang saat unduhan berikutnya.

## Yang baru di `kamus-1.1`

*(1.1)* Semua butir menambah atau menegaskan; tidak ada definisi `kamus-1.0` yang diganti. Kolom terakhir menunjuk bagian kamus yang diubah.

| No | Isi | Asal | Di kamus |
| ---: | --- | --- | --- |
| 1 | `consent_ok` dihitung dari daftar izin berkode di sistem, dicocokkan `ketua` dengan L7; siswa `withdrawn_keep` dihitung sampai saat mundur; kolom `withdrawn_on`, `cutoff_basis`, `after_withdrawal` | 35 §7, 35 K4; 30 §5, §10.1; 13 → *Analisis* | [Penanda untuk analisis](#penanda-untuk-analisis), [Kunci penggabungan](#kunci-penggabungan-dan-waktu) |
| 2 | `registration_corrected`; `risk_reconsent` hanya sebagai angka gabungan | 30 §5.4, §9 | [Variabel peserta dan sesi](#variabel-peserta-dan-sesi) |
| 3 | `offset_known` | 29 K8 | Kunci penggabungan |
| 4 | "Pengambilan data selesai" = jam putaran hitung yang dipakai unduhan | 35 §6.4; 04, 2 November 2026 | [Data hilang](#data-hilang), Analitik 1 |
| 5 | Jenis (a) "dilewati" untuk skenario memakai event `scenario_failed`; pengerjaan ulang terbuka di P2–P4 setelah kelima tantangan wilayah pertemuan itu tuntas, `is_retake = 1` | 32; 33 §7.4; 04, 2 November 2026 | Analitik 1 |
| 6 | Sumber waktu aktif: jeda masukan `input_idle` (≥ 20 detik), potongan `challenge_attempt_segments`, offset jam audio; ambang di kolom studi `idle_threshold_ms`, `rapid_guess_ms` | 33; 35 §5, §6.7, K2 | Analitik 5 |
| 7 | Waktu putus = `last_input_ms − ready_ms`; alasan skenario memakai `reason_ready_ms`; data dari sebelum `ready_ms` ada memakai waktu putus kosong | 29 §6.1; 32 | Analitik 6 |
| 8 | `empty_tap` mencatat koordinat; jarak ke kunci diturunkan server dari geometri bank | 29 §6.2; 31 §9 | Analitik 6 |
| 9 | Analisis memakai kode kesalahan dari bank terbaru; kode saat percobaan di `error_code_at_try` | 31 §21 no. 3; 35 K5 | Analitik 6, 9 |
| 10 | `instruction_replays` = semua pemutaran narasi `challenge_instruction` | 32 §8; 01 no. 6 | Analitik 7 |
| 11 | `material_returns` bagian (b) dari event `passage_reopened` | 29 §6.3 | Analitik 8 |
| 12 | `first_action_key` memakai kunci tetap bank (`t1`, `t2`, …); urutan tampil di `answer_json.order`; `reason_level` kosong sampai urutan alasan ditetapkan | 32; 35 §8.2 no. 14 | Analitik 10 |
| 13 | Variabel butir dari bank baru: `round`, `variant_source`, `booklet_at_open`, `tap_min_dp`, `bloom_level_source`; variasi Susun Gambar `c`–`k` | 31 §13 | [Variabel butir](#variabel-butir) |
| 14 | `is_makeup`, `makeup_no`; `region_opened` diturunkan dari rencana pertemuan studi | 28 §5, §17 | Variabel peserta dan sesi |
| 15 | Variabel tes awal–akhir dan jawaban tes; `*_theta` dihitung di analisis, bukan sistem | 13 → *Penskoran*; 34 §11.4 | [Tes awal–akhir dan angket](#tes-awalakhir-angket-dan-skala-sikap-11) |
| 16 | Variabel angket, **tanpa** jawaban butir risiko daring per siswa; butir risiko hanya lembar `risiko_gabungan` | 16; 29 K9, §7.6 | Sama |
| 17 | Variabel skala sikap dan `feedback_rating`; `sikap_missing_reason = tanpa_izin` tidak pernah dari sistem | 17; 35 §20 | Sama |
| 18 | Pengamatan L4 diberi `attempt_id` dan `item_key` yang aktif (`derived_observations`); status tes dari modul, L6 tidak diimpor | 35 §6.6; 29 K12 | [Aturan gabung lembar lapangan](#aturan-gabung-lembar-lapangan) |
| 19 | L5 menunjuk percobaan dengan `try_no`; `manual_code` hanya kode kesalahan yang disepakati dan berbeda dari kode game, dari kode butir itu atau `*-LAIN` dimensinya; kode alasan R-\* yang dikode tangan (termasuk R-LAIN) tetap di lembar | 02 → L5; 29 §10.4, §19 no. 14; 04, 3 November 2026 | [Kode kesalahan](#analitik-6-pola-kesalahan), Aturan gabung lembar lapangan |
| 19 | `is_touch` tetap di log teknis admin, tidak di data penelitian | 29 §18 no. 3 | Variabel peserta dan sesi |
| 20 | Variabel sekolah dari L8; L2 baris dan kursi lewat panel; aturan nama dari 22 → *Di mana nama berada* menggantikan "nama hanya ada di L7" | 02 → L8; 28 §11; 22; `docs/PROGRESS.md` (keputusan 26 dan 27 Oktober 2026) | Variabel peserta dan sesi, Aturan gabung lembar lapangan |
| 21 | Setiap tabel turunan menyimpan `dictionary_version` dan `run_id`; versi antara `kamus-1.1-usulan` | 35 §4, §8.1 | [Versi dan cara berubah](#versi-dan-cara-berubah) |
| 22 | Syarat sistem di bawah ditandai dokumen implementasinya; kalimat "tahap 3, setelah G2" diganti | 04, 1 November 2026 | [Yang harus disediakan sistem](#yang-harus-disediakan-sistem-di-tahap-3) |

Yang masih menunggu uji coba: T_idle dan T_teb, batas `game_time_group` (16), ambang tebakan cepat tes (13; sementara memakai T_teb studi), urutan alasan (`reason_level`), dan ambang `rapid_share`.

## Peta bukti (beku)

Peta bukti menyambungkan klaim, bukti, dan tugas. Gambar di [01](01_REVISI_10_ANALITIK.md#kerangka-peta-bukti) sudah disesuaikan dengan kisi-kisi yang disetujui.

![Peta bukti: tugas menghasilkan bukti, bukti mendukung klaim tentang literasi digital siswa](gambar/peta-bukti.svg)

| Tugas | Butir yang dinilai | Skala | Analitik yang memakai |
| --- | --- | --- | --- |
| Isi Rumpang (`*-2`) | Setiap rumpang | D1 (D1a atau D1b) | 1–9 |
| Benar atau Salah (`*-3`), termasuk kartu jangkar `x` | Setiap pernyataan | D1 (D1a atau D1b) | 1–9 |
| Skenario Digital (`*-5`) | Setiap skenario: tindakan diskor, alasan dikode | D2 | 1–10 |
| Cari Objek babak 2 *Periksa Unggahan* (`*-4-a`, `*-4-b`) | Setiap petunjuk | D3 | 1–9 |
| Cari Objek babak 1 *Temukan Benda* (`*-4-x`) | Setiap petunjuk | PBK, skala pendamping | 1–9 |
| Skala sikap di Balai Refleksi | Setiap butir skala (daftar kerja no. 19) | `sikap`, skala pendamping | — |
| Susun Gambar (`*-1`) | Tidak ada | Pemanasan, tidak dinilai | 1, 5 |
| Pendaftaran akun | Metrik kata sandi | Bukti validitas luar D2, tidak masuk skor | 10 |
| Lembar L1–L7 | — | Penanda dan kovariat | Semua |
| Tes awal–akhir (L6) | Di luar game | Bukti validitas | — |

Setiap butir punya **tepat satu dimensi utama** (model *between-item*). Kode kesalahan tidak menentukan dimensi; dimensinya ikut butir. Karena itu DIG-HOX hanya D2 dan analitik 10 hanya D2 (03 disesuaikan).

## Istilah dasar

| Istilah | Arti di kamus ini | Kolom |
| --- | --- | --- |
| Peserta | Siswa yang punya kode peserta | `participant_code` (format `S03-A07`) |
| Sesi lapangan | Satu pertemuan satu rombel, dibuat P1 di panel peneliti; membawa token, wilayah yang dibuka, dan jam buka–tutup | `field_session_code` (format `S03-A-P1`) |
| Pertemuan | Pertemuan ke-1 sampai ke-5. Di kode sesi ditulis `P1`–`P5`; jangan tertukar dengan peran peneliti P1–P3 | `meeting_no` (1–5) |
| Sesi game | Satu sesi bermain di game, dimulai saat siswa masuk dan mengetik token. Satu sesi lapangan bisa berisi beberapa sesi game seorang siswa (misalnya setelah keluar dan masuk lagi) | `session_code` *(ada)* |
| Tantangan | Satu dari 15 tantangan, `tmg-1` sampai `wnb-5` | `node_ref` *(ada)* |
| Babak | Babak Cari Objek: 1 *Temukan Benda*, 2 *Periksa Unggahan* | `round` |
| Butir | Satuan yang dinilai: rumpang, pernyataan, petunjuk, atau skenario | `item_key` *(ada)* |
| Testlet | Kelompok butir yang berbagi bacaan, kartu, atau adegan | `testlet_key` |
| Pengerjaan | Satu kali tantangan dibuka sampai ditutup | `attempt_id`, `attempt_no` *(ada)* |
| Pengerjaan pertama | Pengerjaan dengan variasi buklet siswa, sampai tantangan itu tertutup untuk pertama kali. Satu-satunya pengerjaan yang dinilai | `is_scored_attempt` |
| Pengerjaan ulang | Setiap pengerjaan setelah tantangan tertutup. Disimpan, tidak dinilai | `is_retake` |
| Kesempatan | Batas percobaan, yaitu tiga. Dihitung per butir atau per tantangan, lihat tabel di bawah | `max_tries` *(ada)* |
| Percobaan | Satu jawaban yang dinilai server. Artinya per jenis tantangan ada di [Percobaan per jenis tantangan](#percobaan-per-jenis-tantangan) | `try_no` *(ada)* |
| Butir tertutup | Butir yang sudah benar, atau yang kesempatannya habis | `closed_at`, `closed_reason` |
| Tantangan tertutup | Semua butirnya tertutup | `completion_reason` *(ada)* |

### Percobaan per jenis tantangan

| Jenis | Satu percobaan adalah | Kesempatan dihitung per | Butir tertutup saat |
| --- | --- | --- | --- |
| Isi Rumpang, Benar atau Salah (termasuk kartu jangkar) | Satu kali **Cek**: setiap butir yang belum benar mendapat satu percobaan; butir yang sudah benar terkunci | Tantangan (tiga kali Cek) | Benar, atau Cek ketiga selesai |
| Cari Objek, kedua babak | Satu ketukan pada **bagian ketuk** (benda atau bagian unggahan) saat petunjuk itu aktif | Petunjuk | Ketukan benar, atau ketukan salah ketiga |
| Skenario Digital | Satu pilihan tindakan **dan** satu pilihan alasan | Skenario | Tindakan tepat dipilih, atau kesempatan ketiga selesai |
| Susun Gambar | Satu kali Cek | Tantangan | Tidak dinilai |

Ketukan di luar bagian ketuk mana pun **bukan percobaan** dan tidak memakai kesempatan (lihat NON-SEN di [Analitik 6](#analitik-6-pola-kesalahan)).

## Kunci penggabungan dan waktu

- **Kunci antartabel:** `participant_code`, `field_session_code`, `session_code`, `attempt_id`, `item_key`, `try_no`.
- **Kunci dengan lembar lapangan:** kode peserta, kode sesi lapangan, dan jam WIB.
- **Satu sesi game masuk tepat satu sesi lapangan**, lewat token yang diketik siswa. Setiap percobaan dan event ikut sesi lapangan dari sesi game-nya.
- **Waktu disimpan dalam WIB**, ISO 8601 dengan milidetik. Waktu percobaan adalah waktu server saat percobaan dinilai. Waktu event dari perangkat dikoreksi: `occurred_at_corrected = occurred_at + clock_offset_ms` *(ada)*. Semua selang waktu di kamus ini memakai waktu terkoreksi.
- **Jam lembar lapangan** diambil dari ponsel yang jamnya diatur otomatis dari jaringan, sampai detik (02). Selisih jam ponsel dan server dianggap kecil dibanding jendela pencocokan di bawah.
- *(1.1)* **`offset_known`** (1/0): 1 kalau selisih jam perangkat dan server diketahui untuk event itu. Event dengan `offset_known = 0` tetap dipakai dengan waktu mentahnya, dan banyaknya dilaporkan ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md) K8).
- *(1.1)* **Sesi game ke sesi lapangan** lewat `game_sessions.field_session_id` dan `meeting_no` ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md) §14).
- *(1.1)* **`after_withdrawal`** (1/0) di lembar mentah (`pengerjaan`, `jawaban`, `percobaan`, `navigasi`, `bantuan`, `selesai`, `event`): 1 untuk data sesudah saat mundur siswa `withdrawn_keep`. Data mentah tidak diubah; lembar turunan tidak memuat baris itu ([Penanda untuk analisis](#penanda-untuk-analisis)). Dihitung saat unduhan dibuat dari daftar izin berkode dan `deactivated_at`, dengan aturan saat mundur yang sama dengan lapis turunan ([35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#19-keputusan-pengembang) §19 no. 15).
- **Data di luar sesi lapangan tidak ada**, karena game tertutup di luar sesi. Kalau tetap muncul (misalnya karena salah atur), datanya diberi `outside_session = 1` dan dikeluarkan dari semua analisis.

## Data yang dinilai

### Butir dan skor per dimensi

Per siswa di pengerjaan pertama (sesuai [06](06_KISI_KISI_BANK_SOAL.md#per-siswa-pengerjaan-pertama)):

| Skala | Butir per siswa | Skor per butir | Model |
| --- | --- | --- | --- |
| D1 Literasi informasi | 37 (26 D1a, 11 D1b; termasuk 6 butir jangkar) | 0–1 | Rasch dikotomus |
| D2 Aman dan etis | 12 skenario | 0–1–2 dari tindakan pertama | *Partial credit* |
| D3 Partisipasi dan representasi budaya Kedu | 12 petunjuk | 0–1 | Rasch dikotomus |
| Literasi digital | 61 | | Multidimensi *between-item*, dibandingkan dengan satu dimensi |
| PBK (pendamping) | 12 petunjuk | 0–1 | Rasch satu dimensi, terpisah; kovariat |
| `sikap` (pendamping) | Mengikuti instrumen no. 19 | Mengikuti instrumen | Terpisah, di luar skor literasi digital |

Bintang dan Serpihan Cahaya tidak dianalisis sebagai prestasi (01). Super-item testlet tidak dibuat di sistem; sistem cukup menyediakan `testlet_key`, dan keputusannya diambil di lapis analisis setelah uji Q3.

### Pengerjaan pertama

- Pengerjaan pertama adalah pengerjaan yang memakai **variasi buklet** siswa ([06](06_KISI_KISI_BANK_SOAL.md#desain-buklet-usulan)), dihitung sampai tantangan itu **tertutup untuk pertama kali**.
- **Pengerjaan pertama yang terputus dilanjutkan, bukan diulang.** Kalau siswa keluar sebelum tantangan tertutup, lalu membukanya lagi di sesi lapangan yang sama atau di pertemuan 5 (susulan), sistem melanjutkan pengerjaan yang sama: butir yang sudah tertutup tetap tertutup, dan nomor percobaan butir yang terbuka berlanjut. Ini syarat untuk mode penelitian di tahap 3 (perubahan sistem no. 16). Alasannya: kalau dibuat pengerjaan baru, percobaan pertama yang kedua sudah tercampur umpan balik dari yang pertama.
- **Aturan cadangan di lapis turunan.** Kalau data tetap memuat dua pengerjaan sebelum tantangan tertutup (misalnya karena gangguan sistem), percobaan setiap butir diurutkan menurut waktu lintas pengerjaan. Percobaan pertama adalah yang paling awal, dan hanya tiga percobaan pertama yang dipakai. Sisanya diberi `anomaly = 'extra_try'` dan tidak dinilai.
- **Pengerjaan ulang** baru terbuka setelah kelima tantangan wilayah pertemuan itu tuntas, hanya di dalam sesi (keputusan 25 Oktober 2026). Datanya disimpan dengan `is_retake = 1` dan hanya dipakai secara deskriptif.

### Data hilang

| Keadaan | Nilai di data turunan | Diperlakukan di analisis |
| --- | --- | --- |
| Butir punya minimal satu percobaan di pengerjaan pertama | Skor sesuai analitik | Data |
| Tantangan tidak pernah dimulai, atau tidak pernah terbuka | `first_try_score` kosong; `missing_reason` = `not_started` atau `locked` | Hilang (tidak diberikan) |
| Tantangan dimulai, tapi butir ini belum punya percobaan saat pengambilan data selesai | `first_try_score` kosong; `missing_reason = 'no_try'` | Hilang |
| Percobaan berasal dari perangkat yang dipakai bersama (L2) | Skor tetap ditulis; `shared_device = 1` | Lihat [Penanda untuk analisis](#penanda-untuk-analisis) |

Kosong selalu berarti "tidak ada data", tidak pernah 0.

*(1.1)* **"Pengambilan data selesai"** = jam putaran hitung lapis turunan yang dipakai unduhan akhir. Unduhan sebelum studi ditutup menyebut jam putarannya, jadi `not_started` dan `no_try` dibaca "sampai jam itu" (35 §6.4; keputusan pengembang 2 November 2026). Tidak ada kolom tanggal akhir di studi.

### Penanda untuk analisis

Penanda tidak mengubah data mentah. Tiga penanda mengeluarkan data dari analisis (`consent_ok = 0`, `is_pilot = 1`, `outside_session = 1`); sisanya dipakai untuk menjalankan analisis utama dua kali, dengan dan tanpa data bertanda (01, *Rencana analisis singkat*), atau sebagai kovariat.

| Penanda | Satuan | Aturan | Sumber |
| --- | --- | --- | --- |
| `consent_ok` | Peserta | 1 kalau izin orang tua dan persetujuan siswa lengkap di L7, dan siswa tidak mengundurkan diri. Data dengan 0 tidak dianalisis dan diurus sesuai protokol. *(1.1)* Dihitung dari daftar izin berkode di sistem (`participant_consents`, [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md) §5.2 dan §10.1) dan centang persetujuan anak saat mendaftar (`assented_at`); kecocokan dengan L7 diperiksa `ketua`. Siswa `Mengundurkan diri (data tetap)` (`withdrawn_keep`) mendapat 1 dengan data **sampai saat mundur**; siswa `hapus data` tidak ada lagi di data. Aturan lengkap di [35 §7](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#7-consent_ok-dan-data-sampai-tanggal-mundur) | Daftar izin berkode di sistem, dicocokkan dengan L7 |
| `withdrawn_on`, `cutoff_basis` *(1.1)* | Peserta | Tanggal mundur siswa `withdrawn_keep`. Saat mundur = `deactivated_at` akun bila ada (`cutoff_basis = 'deactivated_at'`), selain itu akhir hari tanggal status (`'status_on'`). Data sesudahnya tidak masuk lapis turunan; pengerjaan yang terpotong dinilai dari yang tersisa | Sistem (30) |
| `registration_corrected` *(1.1)* | Peserta | 1 kalau umur, jenis kelamin, atau kelas pernah dibetulkan admin (dengan jejak audit) | Sistem (30 §9) |
| `is_pilot` | Peserta | 1 kalau kode sekolah berawalan `U`. Tidak masuk analisis utama. *(1.1)* Kode `V` hanya ada di instalasi uji validator, tidak pernah di data penelitian ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md) §10.1) | Kode peserta |
| `shared_device` | Percobaan | 1 kalau perangkat dipakai bersama di sesi itu (L2 "Pakai bareng: Ya") | L2 |
| `helped_content` | Percobaan | 1 kalau ada kejadian L3 berjenis ISI, STR, atau BHS untuk siswa itu, dari 1 menit sebelum sampai 2 menit sesudah jam kejadian (lihat [L3](#aturan-gabung-lembar-lapangan)) | L3 |
| `helped_technical` | Percobaan | Sama, untuk jenis TKN | L3 |
| `encouraged` | Percobaan | Sama, untuk jenis SMG | L3 |
| `disturbed` | Percobaan | Sama, untuk gangguan GG-* | L3 |
| `deviation_code` | Percobaan | Kode DEV-* L1 yang mengenai siswa itu pada jam percobaan | L1 |
| `rapid_guess` | Percobaan | Tebakan cepat, lihat [Analitik 6](#analitik-6-pola-kesalahan) | Log |
| `is_retake` | Pengerjaan | Pengerjaan ulang. *(1.1)* Dalam mode penelitian terbuka di P2–P4 setelah kelima tantangan wilayah pertemuan itu tuntas, hanya di dalam sesi ([33](33_IMPLEMENTASI_MODE_PENELITIAN.md) §7.4) | Log |
| `variant_source` *(1.1)* | Pengerjaan | `booklet`, `random`, atau `retake`: asal variasi pengerjaan itu. Pengerjaan pertama butir dinilai dalam mode penelitian selalu `booklet` ([31](31_IMPLEMENTASI_BANK_SOAL_BARU.md) §11) | Log |
| `outside_session` | Event | Data di luar sesi lapangan | Log |

Analisis utama yang dijalankan dua kali: tanpa dan dengan percobaan `helped_content = 1`, dan tanpa dan dengan `shared_device = 1` (01). Penanda `helped_technical`, `encouraged`, dan `disturbed` dipakai sebagai kovariat, tidak untuk mengeluarkan data. Percobaan `rapid_guess = 1` dipakai apa adanya di analisis utama; analisis kedua memperlakukannya sebagai data hilang (keputusan 26 Oktober 2026).

### Data siswa yang bisa dianalisis

Target sampel 300 siswa yang datanya bisa dianalisis ([06](06_KISI_KISI_BANK_SOAL.md#setengah-sampel-per-butir-variasi)) didefinisikan begini (keputusan 26 Oktober 2026):

- `analysable = 1` kalau `consent_ok = 1`, `is_pilot = 0`, dan siswa punya percobaan pertama yang **tidak** bertanda `shared_device` pada **sedikitnya 80% butir literasi digital** (49 dari 61).
- `complete = 1` kalau ke-61 butir punya percobaan pertama. Angka ini dilaporkan, tapi tidak menjadi syarat analisis, karena model Rasch bisa memakai data yang tidak lengkap.

## Sepuluh analitik

Setiap analitik di bawah memuat definisi, satuan, kolom keluaran di lapis turunan, dan sumber data mentahnya. Status di sistem dan alasan penelitiannya tetap di [01](01_REVISI_10_ANALITIK.md#10-analitik-versi-revisi).

### Analitik 1 Penyelesaian aktivitas

**Satuan:** peserta × tantangan (15), dirangkum per wilayah dan per pertemuan.

**Status penyelesaian** (`completion_status`), dinilai di akhir pengambilan data, dari pengerjaan pertama:

| Nilai | Arti |
| --- | --- |
| `solved` | Tantangan tertutup, semua butir benar |
| `exhausted` | Tantangan tertutup, minimal satu butir tidak benar setelah kesempatan habis |
| `unfinished` | Tantangan pernah dimulai, tapi tidak pernah tertutup |
| `not_started` | Tantangan sudah terbuka untuk siswa itu, tapi tidak pernah dimulai |
| `locked` | Tantangan tidak pernah terbuka, karena wilayahnya tidak dibuka untuk siswa itu atau tantangan sebelumnya belum tuntas (tantangan dalam satu wilayah terbuka berurutan) |

Tantangan **tuntas** = `solved` atau `exhausted`, sama dengan aturan Serpihan Cahaya sekarang. Wilayah tuntas = kelima tantangannya tuntas.

**Tiga jenis "dilewati"**, dihitung terpisah dan tidak saling meniadakan:

| Jenis | Kolom | Definisi | Satuan | Sumber |
| --- | --- | --- | --- | --- |
| (a) Ditutup setelah kesempatan habis | `skipped_failed_count` | Banyak butir yang ditutup tanpa jawaban benar setelah kesempatan ketiga, di tantangan yang kesempatannya per butir: petunjuk Cari Objek (kedua babak) dan skenario Skenario Digital | Peserta × tantangan | Event `clue_failed` *(ada)*; untuk skenario, *(1.1)* event `scenario_failed` ([32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md)) |
| (b) Ditinggal | `skipped_left_count` | Banyak kejadian pengerjaan pertama yang ditinggal (tombol Keluar, halaman ditutup, atau sesi berakhir) saat tantangan belum tertutup, **dan tidak dilanjutkan di sesi lapangan yang sama** | Peserta × tantangan × sesi lapangan | `challenge_abandoned` *(ada)* dan aktivitas pengerjaan sesudahnya |
| (c) Tidak dimulai | `completion_status = 'not_started'` | Tantangan sudah terbuka (wilayahnya dibuka token dan tantangan sebelumnya sudah tuntas) tapi tidak pernah dimulai sampai pengambilan data selesai. Tantangan yang tidak pernah terbuka diberi `locked`, bukan dilewati | Peserta × tantangan | Progres dan sesi lapangan |

Jenis (b) tetap terhitung walaupun tantangannya kemudian dilanjutkan dan tuntas di pertemuan 5; status akhirnya tetap `solved` atau `exhausted`. Di Isi Rumpang dan Benar atau Salah, butir yang salah setelah Cek ketiga **tidak** termasuk jenis (a); butir itu tercatat sebagai `exhausted` dan tersensor (Analitik 4).

**Kolom tambahan:** `closed_meeting_no` (pertemuan saat tantangan tuntas), `regions_completed` (0–3), `challenges_completed` (0–15), `scored_challenges_completed` (0–12).

### Analitik 2 Ketepatan jawaban

**Satuan:** peserta × butir, di pengerjaan pertama.

- `final_correct` = 1 kalau butir tertutup benar dalam tiga kesempatan, 0 kalau tertutup tidak benar, kosong kalau tidak ada percobaan. Untuk Skenario Digital, 1 berarti tindakan tepat dipilih di salah satu kesempatan.
- `final_score` untuk D2 = skor tindakan di percobaan terakhir (0–2); untuk butir lain sama dengan `final_correct`.
- **Ringkasan:** proporsi benar akhir per dimensi, sub-aspek (D1a, D1b), level AKM (L1–L3), wilayah, topik D2, dan jenis petunjuk D3. Penyebutnya butir yang punya percobaan.
- Ketepatan akhir sudah tercampur umpan balik, jadi hanya dipakai deskriptif. Ukuran kemampuan memakai Analitik 3.

### Analitik 3 Ketepatan percobaan pertama

**Satuan:** peserta × butir, di pengerjaan pertama. Ini data inti untuk Rasch.

| Jenis | Percobaan pertama | `first_try_score` |
| --- | --- | --- |
| Isi Rumpang | Kata di rumpang itu saat Cek pertama | 0–1 |
| Benar atau Salah, kartu jangkar | Pilihan Benar/Salah di kartu itu saat Cek pertama | 0–1 |
| Temukan Benda (PBK) | Ketukan pertama pada bagian ketuk saat petunjuk itu aktif | 0–1 |
| Periksa Unggahan (D3) | Sama | 0–1 |
| Skenario Digital (D2) | Tindakan di kesempatan pertama | 0, 1, atau 2 |

- `first_try_correct` = 1 kalau `first_try_score` maksimal (untuk D2 berarti skor 2). Dipakai untuk tabel deskriptif; Rasch D2 memakai skor 0–2.
- Percobaan pertama selalu **sebelum umpan balik** atas butir itu. Bantuan yang dibuka sebelumnya tidak membatalkan percobaan; perannya dicatat di Analitik 7.
- Di Cari Objek, siswa boleh berpindah petunjuk. Ketukan hanya dihitung untuk petunjuk yang aktif saat diketuk, seperti di game sekarang (`engines/cari.js`).
- Peluang tebakan Benar atau Salah tetap 50%; itu ditangani di analisis, bukan di kamus.

### Analitik 4 Jumlah percobaan

**Satuan:** peserta × butir, di pengerjaan pertama.

| Kolom | Arti |
| --- | --- |
| `tries_used` | Banyak percobaan yang dibuat untuk butir itu (1–3) |
| `solved_on_try` | Percobaan ke berapa butir benar (1, 2, 3); kosong kalau tidak pernah benar *(ada)* |
| `censored` | 1 kalau butir tidak pernah benar |
| `censor_reason` | `exhausted`: tiga percobaan dipakai tanpa benar. `left`: kurang dari tiga percobaan dan butir tidak pernah tertutup |

- Di Isi Rumpang dan Benar atau Salah, `solved_on_try` adalah nomor Cek saat butir pertama kali benar; butir yang sudah benar terkunci, jadi tidak punya percobaan lagi.
- Di Skenario Digital, `solved_on_try` adalah kesempatan saat tindakan tepat dipilih. Tindakan "kurang tepat" (skor 1) belum dihitung benar.
- **Data tersensor tidak pernah diisi angka 3** atau 4 (01). Analisisnya memakai model ordinal atau model kejadian waktu-diskret dengan sensor.

### Analitik 5 Waktu pengerjaan

**Satuan:** pengerjaan (utama), butir (Cari Objek dan Skenario Digital), dan sesi lapangan.

**Garis waktu satu pengerjaan.** Mulai = layar tantangan tampil (`challenge_opened` *(ada)*, sesudah kartu misi). Selesai = percobaan yang menutup tantangan, atau event terakhir pengerjaan itu kalau tantangan ditinggal. Kalau pengerjaan dilanjutkan di sesi lain, setiap potongan dihitung sendiri lalu dijumlahkan; selang di antara potongan tidak dihitung.

| Kolom | Rumus |
| --- | --- |
| `total_ms` | Jumlah (selesai − mulai) setiap potongan |
| `hidden_ms` | Jumlah selang `page_hidden` sampai `page_visible` berikutnya (atau sampai selesai) *(event ada)* |
| `idle_excess_ms` | Untuk setiap jeda tanpa masukan yang lebih panjang dari ambang **T_idle**, bagian yang melebihi T_idle (keputusan 26 Oktober 2026). Jeda 5 menit dengan T_idle 3 menit menyumbang 3 menit aktif |
| `active_ms` | `total_ms − hidden_ms − idle_excess_ms` |

**Aturan jeda tanpa masukan:**

- **Masukan** = sentuhan atau klik, tombol keyboard, gulir, dan seret. Gerakan tetikus tanpa klik tidak dihitung, supaya laptop dan tablet diperlakukan sama.
- Selang saat **narasi sedang diputar** tidak pernah dihitung jeda (siswa sedang mendengarkan).
- Selang yang sudah masuk `hidden_ms` tidak dihitung lagi sebagai jeda.
- **T_idle sementara 3 menit** (keputusan 26 Oktober 2026). Uji coba memeriksa nilainya, termasuk apakah layar dengan bacaan panjang butuh ambang lain.
- Supaya ambang bisa ditetapkan sesudah uji coba tanpa kehilangan data, sistem mencatat **setiap jeda masukan 20 detik atau lebih** (jam mulai dan jam akhir) di data mentah.
- **Ambang adalah pengaturan, bukan angka di kode.** T_idle dan T_teb disimpan di konfigurasi studi. Mengubahnya tidak butuh perubahan sistem: lapis turunan dihitung ulang dari data mentah, dan nilai ambang yang dipakai tercatat bersama `dictionary_version`.

**Waktu per butir:**

- Cari Objek: dari petunjuk aktif sampai butir tertutup. Kalau siswa berpindah petunjuk, yang dijumlahkan hanya selang saat petunjuk itu aktif. Ada versi total dan aktif.
- Skenario Digital: dari situasi tampil sampai skenario tertutup, total dan aktif.
- Isi Rumpang dan Benar atau Salah: butir berbagi satu layar, jadi waktu per butir hanya **perkiraan**: `first_input_ms` dan `last_input_ms` *(ada)*. Analisis waktu memakai tingkat pengerjaan.

**Sesi:** `session_active_ms` per peserta × sesi lapangan = jumlah `active_ms` semua pengerjaan ditambah waktu aktif di luar tantangan (cerita, peta, Pustaka) dengan aturan jeda yang sama. Kolom `game_sessions.duration_ms` *(ada)* memakai aturan denyut yang lain dan tidak dipakai untuk analisis.

**Kovariat:** `cumulative_active_ms` (waktu aktif kumulatif sampai pengerjaan dimulai) dan `meeting_no`, karena urutan wilayah tidak bisa diacak (01).

**Pelaporan:** waktu total dan waktu aktif selalu dilaporkan berdampingan, dengan median dan rentang antarkuartil.

*(1.1)* **Sumber data mentah** ([33](33_IMPLEMENTASI_MODE_PENELITIAN.md), [35 §6.7](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#67-waktu-aktif)):
- Jeda masukan 20 detik atau lebih: event `input_idle` dengan jam mulai dan akhir.
- Potongan pengerjaan: tabel `challenge_attempt_segments` (satu baris per potongan di satu sesi game). `challenge_attempts.duration_ms` *(ada)* tidak dipakai untuk pengerjaan yang berpotongan.
- Selang narasi diputar: `audio_usage_events`, dengan waktu terkoreksi lewat `clock_offset_ms`.
- **T_idle dan T_teb** ada di kolom studi `research_studies.idle_threshold_ms` (bawaan 180000) dan `rapid_guess_ms` (bawaan 3000), diubah `ketua` dengan log audit dan dikunci di G4 (`thresholds_locked_at`) ([35 §5](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#5-ambang-di-konfigurasi-studi)). Batas 24 dp (NON-SEN), 20 detik (jeda yang dicatat), dan 80% (`analysable`) tetap angka kamus.

### Analitik 6 Pola kesalahan

**Satuan:** percobaan salah.

**Kode isi** (`error_code`). Setiap percobaan salah membawa tepat satu kode dari bank soal. Server menurunkannya dari pilihan yang dipilih siswa saat percobaan dinilai, dan kode tidak pernah dikirim ke browser (perubahan sistem no. 1).

| Jenis | Kode diambil dari | Kode yang boleh |
| --- | --- | --- |
| Isi Rumpang | Sel rumpang × kata di matriks kode (`cloze_matrix`), dengan tiga aturan tetap di [08](08_DRAF_BANK_SOAL.md#cara-membaca) | INF-LIT, INF-KAT, INF-REL, INF-HUB, INF-GEN |
| Benar atau Salah, kartu jangkar | Kode jebakan pernyataan; dipakai setiap kali pernyataan itu dinilai keliru | D1a: INF-LIT, INF-KAT, INF-REL, INF-HUB. D1b: INF-SUM, INF-FOP, INF-GEN |
| Temukan Benda (PBK) | Kode benda pengecoh. Benda sasaran petunjuk lain = INF-LIT | INF-BUD, INF-LIT |
| Periksa Unggahan (D3) | Kode pengecoh utama di tabel petunjuk. Bagian ketuk lain, termasuk kunci petunjuk lain = kode jenis petunjuk itu (keputusan 26 Oktober 2026) | BUD-ATR, BUD-REP, BUD-IZN, BUD-ETK |
| Skenario Digital, tindakan | Kode pilihan tindakan dengan skor 0 atau 1 | DIG-PRI, DIG-SAN, DIG-IZN, DIG-ASG, DIG-HOX, DIG-ETK, DIG-PSF |
| Susun Gambar | Tidak dikode | — |

Kode `*-LAIN` tidak pernah ada di bank. Kode itu hanya diberikan saat pengodean manual (misalnya transkrip L5), dan disimpan di kolom terpisah `manual_code` supaya kode bank tidak tertimpa. *(1.1)* `manual_code` hanya berisi satu kode kesalahan untuk percobaan yang salah: salah satu kode butir itu di bank, atau kode `*-LAIN` dimensinya. Kode itu diisi dari L5 kalau kedua pengode sepakat dan kodenya berbeda dari kode game ([02 → L5](02_LEMBAR_LAPANGAN.md#l5-wawancara-retrospektif-dan-berpikir-lantang)). Kode alasan R-\* yang dikode tangan, termasuk R-LAIN, tetap di lembar L5 dan tidak masuk data percobaan.

**Kode alasan** (`reason_code`): kode R-* dari pilihan alasan, dicatat di **setiap** percobaan Skenario Digital, termasuk yang tindakannya tepat.

**Penanda prosedural** (kolom sendiri, dihitung sistem, tidak mengganti `error_code` di data mentah):

| Kode | Kolom | Aturan |
| --- | --- | --- |
| NON-TEB | `rapid_guess` | 1 kalau **waktu putus** (`decision_ms`) kurang dari ambang **T_teb** (sementara 3 detik, keputusan 26 Oktober 2026; pengaturan yang ditetapkan ulang setelah uji coba). Dihitung untuk Benar atau Salah, kartu jangkar, Cari Objek, dan tindakan Skenario Digital. Untuk alasan Skenario Digital dicatat di `reason_rapid`. Tidak dihitung untuk Isi Rumpang, karena menyeret beberapa kata berturut-turut setelah membaca satu bacaan bukan keputusan yang terpisah |
| NON-HABIS | `last_try_exhausted` | 1 pada percobaan salah yang menghabiskan kesempatan terakhir butir itu |
| NON-SEN | `near_miss_count` (per butir) | Banyak ketukan di luar semua bagian ketuk, yang jaraknya paling jauh 24 dp dari bagian ketuk kunci petunjuk yang aktif. Bukan percobaan dan tidak memakai kesempatan. Ketukan kosong yang lebih jauh dihitung di `empty_tap_count` |

**Waktu putus** (`decision_ms`) = selang dari saat butir bisa dijawab sampai masukan yang dinilai:

- Cari Objek: dari petunjuk aktif (percobaan pertama) atau dari umpan balik ketukan sebelumnya selesai, sampai ketukan.
- Skenario Digital: dari pilihan tindakan tampil sampai tindakan dipilih; untuk alasan, dari pilihan alasan tampil sampai alasan dipilih.
- Benar atau Salah: semua pernyataan terlihat bersama, jadi waktu putus adalah **perkiraan**: selang dari masukan sebelumnya di pengerjaan itu (atau dari layar tampil, atau dari umpan balik Cek sebelumnya) sampai pilihan terakhir di kartu itu sebelum Cek. Kalau kartu yang salah tidak disentuh lagi sebelum Cek berikutnya, waktu putusnya kosong dan `rapid_guess` tidak dihitung.

*(1.1)* **Sumber waktu putus.** `decision_ms = last_input_ms − ready_ms`, dengan `item_tries.ready_ms` = saat butir bisa dijawab untuk percobaan itu ([29 §6.1](29_IMPLEMENTASI_DATA_PENELITIAN.md#61-saat-butir-bisa-dijawab-ready_ms)). Alasan Skenario Digital memakai `reason_ready_ms` ([32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md)). Percobaan tanpa `ready_ms` (data dari sebelum kolom itu ada, termasuk `last_input_ms` Cari Objek yang memakai rujukan lama) mendapat `decision_ms` dan `rapid_guess` kosong, bukan 0.

*(1.1)* **Sumber NON-SEN.** Event `empty_tap` mencatat koordinat ketukan dalam persen adegan; jaraknya ke kotak kunci petunjuk aktif diturunkan server dari geometri adegan di bank ([29 §6.2](29_IMPLEMENTASI_DATA_PENELITIAN.md#62-ketukan-di-luar-bagian-ketuk-empty_tap), [31 §9](31_IMPLEMENTASI_BANK_SOAL_BARU.md#9-area-ketuk-tidak-bersentuhan-perubahan-no-17)). Kotak bagian ketuk tidak pernah dikirim ke peramban.

*(1.1)* **Kode bank terbaru.** `error_code` di lapis turunan diturunkan dari matriks kode bank yang berlaku saat putaran hitung, supaya kode yang direvisi dari validasi berlaku untuk semua data. Kode yang dicatat sistem saat percobaan tetap di data mentah (`item_tries.error_code`) dan di kolom turunan `error_code_at_try` (keputusan pengembang 2 November 2026; [35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md) K5).

**Kode untuk analisis pola** (`error_code_analysis`): sama dengan `error_code`, kecuali percobaan bertanda `rapid_guess` diberi NON-TEB, karena tebakan cepat tidak menunjukkan salah paham tertentu. `last_try_exhausted` hanya penanda; pola kesalahan dilaporkan dengan dan tanpa percobaan itu.

**Ringkasan utama** memakai kesalahan di **percobaan pertama** saja (satu kesalahan per siswa per butir), supaya siswa yang sering mencoba ulang tidak lebih berbobot. Kesalahan di semua percobaan dipakai di Analitik 9. Frekuensi dilaporkan per wilayah, dimensi, sub-aspek, dan level AKM.

**Validasi pengodean** tetap seperti di 01: dua pengode terpisah pada sampel 20% jawaban dan semua transkrip L5, Cohen's κ ≥ 0,70.

### Analitik 7 Penggunaan bantuan

**Satuan:** percobaan dan pengerjaan. Bantuan dihitung hanya kalau **dipilih siswa**; narasi yang berbunyi sendiri (*autoplay*) dicatat, tapi tidak dihitung bantuan.

| Jenis | Kolom | Definisi | Sumber |
| --- | --- | --- | --- |
| Tombol Bantuan 💡 | `hint_count` (pengerjaan) *(ada)* | Banyak Bantuan dibuka | `hint_opened` *(ada)* |
| | `hint_before` (percobaan) *(ada sebagai `hint_opened_before`)* | 1 kalau ada Bantuan dibuka di pengerjaan itu sebelum percobaan dinilai | Log |
| | `item_hint_before` (percobaan) | 1 kalau Bantuan khusus butir itu dibuka sebelum percobaan | Log |
| Narasi soal | `item_narration_before` (percobaan) | 1 kalau narasi butir itu diputar siswa sebelum percobaan: situasi Skenario Digital dan petunjuk *Periksa Unggahan* lewat tombol ▶, serta narasi petunjuk Temukan Benda | `audio_play`, `audio_replay` *(ada)* |
| Narasi instruksi | `instruction_replays` (pengerjaan) | Banyak instruksi tantangan diputar ulang siswa. Butuh kode konteks narasi instruksi yang baru (perubahan sistem no. 6). *(1.1)* = semua pemutaran narasi konteks `challenge_instruction` di pengerjaan itu; narasi instruksi tidak pernah berbunyi sendiri ([32 §8](32_IMPLEMENTASI_SKENARIO_DIGITAL.md)) | Log |
| Lama mendengar | `listened_ms` *(ada)* | Milidetik narasi didengar | Log |
| Bantuan orang | `helped_content`, `helped_technical`, `encouraged` | Lihat [Penanda untuk analisis](#penanda-untuk-analisis) | L3 |
| Headset | `headset_share` (sesi lapangan) | Siswa memakai headset dibagi siswa bermain. L1 tidak mencatat headset per siswa, jadi ini kovariat sesi | L1 |

Bacaan dan pernyataan D1 tidak pernah dibacakan (keputusan media), jadi butir D1 tidak punya narasi soal.

### Analitik 8 Navigasi permainan

**Satuan:** sesi game dan pengerjaan.

| Kolom | Definisi | Sumber |
| --- | --- | --- |
| Urutan layar | Urutan `screen_viewed` per sesi game, dengan waktu terkoreksi | `screen_viewed` *(ada, hanya server)* |
| `material_returns` (pengerjaan) | **Kembali ke materi**: (a) membuka Pustaka wilayah saat pengerjaan belum tertutup, ditambah (b) membuka lagi bacaan di dalam tantangan setelah masukan pertama, misalnya membuka panel bacaan atau mengetuk label "Berdasarkan Teks" | `library_opened` *(ada)*; *(1.1)* event `passage_reopened` untuk (b), dikirim hanya setelah masukan pertama ([29 §6.3](29_IMPLEMENTASI_DATA_PENELITIAN.md#63-membuka-lagi-bacaan-passage_reopened)) |
| `exit_count` (pengerjaan) | Banyak kali siswa keluar dari tantangan sebelum tertutup (tombol Keluar, Kembali, atau halaman ditutup) | `challenge_abandoned` *(ada)* |
| `library_pages`, `library_ms` (sesi) | Halaman Pustaka yang dibuka dan lama aktifnya | `library_page_viewed` *(ada)* |
| `reading_max_pct` (pengerjaan) | Bagian bacaan terjauh yang tergulir, dalam persen | `reading_progress` *(ada)* |

Event yang ditandai "Dihapus" di 01 (`answer_changed`, `answer_selected`, `dialogue_advanced`, `audio_pause`) tidak dipakai. Hitungan perubahan jawaban per butir tetap disimpan (`change_count` *(ada)*).

### Analitik 9 Perubahan setelah umpan balik

**Satuan:** peserta × butir yang **salah di percobaan pertama**.

| Nilai `change_after_feedback` | Arti |
| --- | --- |
| `fixed` | Percobaan kedua benar (D2: tindakan tepat) |
| `same_error` | Percobaan kedua salah dengan `error_code` yang sama |
| `other_error` | Percobaan kedua salah dengan `error_code` lain |
| `no_second_try` | Tidak ada percobaan kedua (butir ditinggal) |

- `answer_unchanged` = 1 kalau jawaban percobaan kedua persis sama dengan percobaan pertama (penting di Isi Rumpang dan Benar atau Salah, tempat siswa bisa menekan Cek tanpa mengubah jawaban). Di Benar atau Salah, `same_error` selalu berarti jawaban tidak diubah.
- Untuk D2, perpindahan dinilai dari **pilihan tindakan**, bukan dari kodenya, karena di 08 dua pilihan bisa berbagi kode (misalnya pilihan skor 0 dan skor 1 di `tmg-5-a03` sama-sama DIG-IZN): tindakan tepat = `fixed`, pilihan yang sama dengan percobaan pertama = `same_error`, pilihan lain yang belum tepat = `other_error`. Perpindahan skor (misalnya 0 ke 1) dicatat di `score_change`.
- Perpindahan percobaan kedua ke ketiga dihitung dengan aturan yang sama di kolom `change_after_feedback_2`, dan dilaporkan sebagai pelengkap.
- Untuk tantangan lain, dasarnya `error_code` mentah, bukan `error_code_analysis`.

### Analitik 10 Pilihan dan alasan di Skenario Digital

**Satuan:** peserta × skenario (12), dari kesempatan pertama.

| Kolom | Arti |
| --- | --- |
| `first_action_key` | Kunci pilihan tindakan pertama (pilihan tepat tidak punya kode kesalahan, jadi kuncinya yang disimpan). *(1.1)* Kunci tetap dari bank (`t1`, `t2`, …), bukan posisi tampil; urutan tampil yang diacak tersimpan di `answer_json.order` ([32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md)) |
| `first_action_score` | 0, 1, atau 2 |
| `first_action_code` | Kode DIG-* tindakan pertama; kosong kalau tepat |
| `first_reason_code` | Kode R-* alasan pertama |
| `action_reason_pattern` | Pola gabungan, tabel di bawah |

| `action_reason_pattern` | Aturan |
| --- | --- |
| `safe_expected` | Skor 2, alasan R-RISK atau R-ETIK |
| `safe_rule` | Skor 2, alasan R-ATUR ("tindakannya benar, alasannya dangkal") |
| `safe_wrong_reason` | Skor 2, alasan R-SOS atau R-ACAK |
| `partial` | Skor 1, alasan apa pun (alasannya tetap disimpan) |
| `risky` | Skor 0, alasan apa pun |

- **"Tindakannya benar, alasannya keliru"** di 01 = `safe_rule` + `safe_wrong_reason`, dan keduanya juga dilaporkan terpisah.
- Urutan alasan R-ACAK/R-SOS < R-ATUR < R-RISK/R-ETIK (`reason_level` 0–2) baru dipakai **setelah uji coba** (keputusan kisi-kisi 06, pertanyaan 3). Sampai saat itu alasan hanya dikode, tidak diskor, dan *(1.1)* kolom `reason_level` ada tetapi kosong.
- **Metrik kata sandi saat daftar**, bukti validitas luar D2 yang tidak masuk skor: `pw_first_submit_criteria` (0–5 syarat terpenuhi di kiriman pertama) dan `pw_weak_submit_count` (banyak penolakan karena sandi lemah) *(keduanya ada)*. Dengan token, pendaftaran terjadi di pertemuan 1.
- Transkrip L5 dikode dengan kode R-* yang sama dan dibandingkan dengan `first_reason_code` siswa itu.

## Variabel butir

Ekspor butir (perubahan sistem no. 3, 8, dan 20) memuat satu baris per butir.

| Kolom | Isi |
| --- | --- |
| `item_key`, `node_ref`, `round`, `variant` *(sebagian ada)* | Kode butir, tantangan, babak, variasi (`a`, `b`, `x`, dan `c`–`j` untuk Susun Gambar) |
| `booklet_shared` | 1 untuk butir `x` (dipakai semua buklet) |
| `dimension`, `subaspect` | D1, D2, D3, PBK; D1a, D1b |
| `akm_level`, `pirls_process`, `bloom_level` | Label pengembang dari 08, diganti hasil pelabelan buta setelah tahap 2. Kolom `akm_level_source` mencatat asalnya (`developer` atau `expert`) |
| `text_genre`, `testlet_key` | Genre teks dan kelompok testlet |
| `d2_topic`, `d3_clue_type` | Topik skenario; jenis petunjuk (atribusi, representasi, izin, menghargai tradisi) |
| `option_count`, `reason_option_count` | Banyak pilihan tindakan dan alasan (D2), atau bagian ketuk (Cari Objek) |
| Beban permukaan | `gap_count`, `distractor_word_count`, `passage_words`, `decoy_count`, `puzzle_grid` (perubahan sistem no. 8) |
| `review_status`, `reference_source` | `needs_verification` atau sudah divalidasi; sumber fakta |
| `bloom_level_source` *(1.1)* | Pasangan `akm_level_source`: `developer` atau `expert` ([31 §13.1](31_IMPLEMENTASI_BANK_SOAL_BARU.md#131-label-pengembang-dan-label-ahli)) |
| `tap_min_dp` *(1.1)* | Jarak terkecil antara kotak kunci dan kotak lain di adegan Cari Objek, dalam dp; bagian beban permukaan ([31 §13.2](31_IMPLEMENTASI_BANK_SOAL_BARU.md#132-variabel-beban-permukaan)) |

*(1.1)* `variant` Susun Gambar kini `a`–`k`: `a` dan `b` dua variasi bersih untuk pengerjaan pertama, `c` dan seterusnya untuk pengerjaan ulang ([31 §14](31_IMPLEMENTASI_BANK_SOAL_BARU.md#14-susun-gambar-selama-studi)). Asal variasi per pengerjaan ada di `variant_source` dan `booklet_at_open` ([Penanda untuk analisis](#penanda-untuk-analisis)). Kode kesalahan per pilihan dari bank ada di lembar `kode_pilihan`, hanya untuk `ketua` ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md) §7.2).

**Panjang bacaan** (`passage_words`) = banyak kata teks yang tampil di layar bacaan atau kartu (judul, isi, nama akun, keterangan, dan komentar), dihitung sebagai potongan yang dipisah spasi dan memuat minimal satu huruf atau angka. Emoji tidak dihitung.

## Variabel peserta dan sesi

| Satuan | Kolom | Sumber |
| --- | --- | --- |
| Peserta | `participant_code`, `school_code`, `rombel`, `booklet` (B1–B4) | Sistem, token, L2. *(1.1)* Buklet dihitung panel dari baris dan kursi L2 (`participant_seats`, [28](28_IMPLEMENTASI_SESI_LAPANGAN.md) §11) |
| | `age`, `gender`, `class_level` *(ada)* | Pendaftaran |
| | `consent_ok`, `is_pilot`, `analysable`, `complete` | L7, kode peserta, kamus |
| | `withdrawn_on`, `cutoff_basis`, `registration_corrected` *(1.1)* | Sistem ([Penanda untuk analisis](#penanda-untuk-analisis)) |
| | `pw_first_submit_criteria`, `pw_weak_submit_count` *(ada)* | Pendaftaran |
| | `rapid_share` | Proporsi percobaan pertama bertanda `rapid_guess`; penanda kesungguhan, ambangnya diputuskan setelah uji coba |
| Sesi lapangan | `field_session_code`, `meeting_no`, `region_opened`, tanggal, jam buka dan tutup | Panel peneliti. *(1.1)* `region_opened` diturunkan dari rencana pertemuan studi (`meeting_plan`) dan, untuk KG2, `region_code` sesi lapangan ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md) §5) |
| | `is_makeup`, `makeup_no`, `main_session_code` *(1.1)* | Sesi susulan (`-S`, `-S2`, …) dan sesi utamanya ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md) §6.7, §17) |
| | Internet, kebisingan, kehadiran guru, `headset_share`, menit tersedia dan dipakai | L1 |
| Sekolah *(1.1)* | `school_code`, `district`, `area_type` (`kota`, `desa`), `school_status` (`negeri`, `swasta`), `grade5_rombel_count`, `grade5_students`, `classroom_language`, `school_devices`, `devices_sufficient` (1/0), `internet_type`, `internet_mbps_min`, `internet_mbps_median`, `power_ok` (1/0), `screen_assessment_exp` (1/0), `digital_lessons` (1/0) | L8 (nama variabel usulan, ditetapkan bersama ringkasan sekolah di Bab III). Kolom *Catatan* L8 tidak masuk data; NPSN dan nama sekolah tidak pernah |
| Sesi game | `session_code`, `locale` *(ada)* | Log |
| | `device_type`, `screen_size` *(ada)* | Log (kovariat). Nama dan versi OS dan peramban, serta mode dan pergantian paket aset, hanya untuk admin; rincian perangkat lain dihapus (01). *(1.1)* `is_touch` juga tetap di log teknis admin, kecuali peneliti memintanya ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md) §18 no. 3) |

Nama, nama pengguna, nama sekolah, dan NPSN tidak pernah masuk data peneliti (01, *Admin dan peneliti*).

### Tes awal–akhir, angket, dan skala sikap (1.1)

Instrumen di luar game. Datanya dari modul tes dan layar skala sikap ([34](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md) §11.4), tidak diketik dari L6 ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md) K12). Rumus variabel turunan tetap di dokumen asalnya; lapis turunan menghitungnya ([35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md) K10), bukan modul. Semua bertanda Peneliti, dan tidak satu pun masuk skor literasi digital game. Sesi pratinjau (`is_preview = 1`) dan contoh soal (`tes-ct-*`, `skp-ct-01`) tidak dianalisis.

| Satuan | Kolom | Isi | Rumus atau arti di |
| --- | --- | --- | --- |
| Peserta | `pre_form`, `post_form` | `A` atau `B`, dihitung panel dari baris dan kursi L2 | [13](13_TES_AWAL_AKHIR.md), [28](28_IMPLEMENTASI_SESI_LAPANGAN.md) §11 |
| | `pre_date`, `post_date`, `pre_makeup`, `post_makeup` | Tanggal, dan apakah dikerjakan di susulan | 13 → *Penskoran* |
| | `pre_d1` … `post_d3` | Skor mentah per bagian (D1 0–16, D2 0–16, D3 0–8) | 13 |
| | `pre_minutes_p1`, `pre_minutes_p23`, `post_minutes_p1`, `post_minutes_p23`, `pre_capped`, `post_capped` | Lama kerja per bagian; bagian yang ditutup batas waktu | 13 |
| | `test_status` | `lengkap`, `hanya_awal`, `hanya_akhir`, `hilang`; dari modul | 13; 34 |
| | `pre_d1_theta` … `post_d3_theta` | Kemampuan Rasch per bagian. **Dihitung di analisis, bukan sistem** | 13 → *Analisis* |
| Jawaban tes (satu baris per butir per siswa per tes) | `participant_code`, `occasion` (`pre`/`post`), `form`, `item_code`, `response`, `score`, `error_codes`, `not_reached`, `time_on_item`, `answer_changes`, `instrument_version`, `rapid_guess` | Jawaban mentah dan skor dari kunci; `rapid_guess` memakai T_teb studi sampai ada ambang tes sendiri | 13 → *Penskoran*; 34 §11.4 |
| Bagian tes (per siswa per kesempatan) | `part`, `block`, `started_at`, `submitted_at`, `closed_by_limit`, `extra_minutes`, `raw_score`, `answered_count`, `is_makeup`, `field_session_code` | Satu baris per bagian | 34 §11.4 |
| Peserta (angket) | `questionnaire_status`, `questionnaire_date`, `questionnaire_makeup`, `questionnaire_late`, `questionnaire_minutes`; 41 kolom jawaban berkode `phone_access` … `books_home`; variabel turunan `own_phone` … `game_time_group`; `q_flags` | Jawaban mentah termasuk `-`, `8`, `9` | [16 → Variabel untuk kamus data](16_ANGKET_AKSES_DIGITAL.md#variabel-untuk-kamus-data) |
| Butir risiko daring | **Bukan variabel per siswa.** Jawaban ang-21–ang-23, `risk_reconsent`, dan `risk_offered` (siswa yang ditawari butir ini) hanya muncul sebagai angka gabungan di lembar `risiko_gabungan` | Kolom `risk_*` dan `support_*` di 16 tidak masuk data peserta | [29](29_IMPLEMENTASI_DATA_PENELITIAN.md) K9, §7.6; 30 §5.4 |
| Peserta (skala sikap) | `skp_01` … `skp_12`, `skp_01_ms` … `skp_12_ms`, `skp_changes`, `sikap_makeup`, `sikap_submitted_at`, `sikap_version`; turunan `sikap_score`, `sikap_sk1` … `sikap_sk4`, `sikap_n_answered`, `sikap_flag_straight`, `sikap_missing_reason` | Jawaban 1–4 atau `-`. `sikap_missing_reason = tanpa_izin` tidak pernah dihasilkan sistem (anak tanpa izin tidak punya akun); nilai itu hanya diisi di analisis dari L7 | [17 → Variabel untuk kamus data](17_SKALA_SIKAP.md#variabel-untuk-kamus-data) |
| | `feedback_rating` | Bintang 1–5 di kritik dan saran (`participant_feedback.rating`); data kepraktisan, bukan skala sikap | 17 |

Siswa `withdrawn_keep` ikut dengan data sampai saat mundur; tes akhir sesudah tanggal itu tidak ada. Ambang tebakan cepat tes ditinjau setelah uji coba, karena waktu membaca teks jatuh ke butir pertama (13); bila perlu, studi mendapat kolom `test_rapid_guess_ms` ([35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md) §18 no. 2).

## Aturan gabung lembar lapangan

| Lembar | Kunci | Aturan | Hasil di data turunan |
| --- | --- | --- | --- |
| L1 bagian atas | Kode sesi lapangan | Satu baris per sesi lapangan | Kovariat sesi |
| L1 penyimpangan | Kode sesi lapangan, jam | Mengenai siswa di kolom "Siswa terdampak" (nomor kursi dicocokkan ke kode peserta lewat L2; "semua" berarti semua siswa sesi itu) dari jam kejadian sampai 5 menit sesudahnya, atau sampai akhir lama kejadian kalau lamanya ditulis | `deviation_code` per percobaan |
| L2 | Kode peserta, kode sesi lapangan | Menghubungkan kursi, perangkat, dan buklet; jam pindah perangkat memotong jendela. *(1.1)* Baris dan kursi dimasukkan P1 ke panel, yang menghitung buklet dan bentuk tes ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md) §11) | `booklet`, `shared_device` |
| L3 | Kode peserta, jam | Percobaan siswa itu dari 1 menit sebelum jam kejadian sampai 2 menit sesudahnya. Kalau jam selesai ditulis (kejadian lebih dari 2 menit), jendelanya dari 1 menit sebelum jam mulai sampai 2 menit sesudah jam selesai. Baris untuk beberapa kursi berlaku untuk setiap siswa di kursi itu | `helped_content`, `helped_technical`, `encouraged`, `disturbed` |
| L4 | Kode peserta, jam | Satu pengamatan menutup 20 detik sejak jamnya, dipasangkan dengan pengerjaan dan butir yang aktif di selang itu | Kode perilaku dan emosi per pengamatan; *(1.1)* dengan `attempt_id` dan `item_key` yang aktif (`derived_observations`, [35 §6.6](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#66-derived_field_sessions-satu-baris-per-peserta--sesi-lapangan)) |
| L5 | Kode peserta, kode butir; *(1.1)* nomor percobaan (`try_no`, kosong = 1) | Transkrip dikode dengan kode R-*, INF, DIG, atau BUD; `*-LAIN` untuk yang tidak cocok. *(1.1)* Hanya kode kesalahan INF, DIG, atau BUD yang disepakati dan berbeda dari kode game yang masuk `manual_code`, dan hanya untuk percobaan yang salah; kodenya kode butir itu di bank atau `*-LAIN` dimensinya. Kode R-\* (termasuk R-LAIN) tetap di lembar, untuk kesepakatan pengode dan revisi skema | `manual_code` |
| L6 | Kode peserta | Skor tes awal dan akhir, angket akses digital. *(1.1)* Datanya dari modul tes; L6 hanya catatan pelaksanaan dan tidak diimpor ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md) K12) | Variabel peserta |
| L8 *(1.1)* | Kode sekolah | Satu baris per sekolah; tidak diimpor ke sistem, diketik tim di lapis analisis ([02 → L8](02_LEMBAR_LAPANGAN.md#l8-profil-sekolah)) | Variabel sekolah ([Variabel peserta dan sesi](#variabel-peserta-dan-sesi)) |
| L7 | Kode peserta | Menentukan `consent_ok`. *(1.1)* Nama anak tidak pernah ada di data penelitian; nama hanya ada di akun GELITA (hanya terlihat admin), di L7 dan formulir izin bertanda tangan, dan di berkas terpisah yang disimpan terkunci ketua peneliti ([22 → Di mana nama berada](22_PERMOHONAN_IZIN_ETIK.md#di-mana-nama-berada)). Kolom berkode L7 dimasukkan P1 ke panel (daftar izin berkode); sistem menghitung `consent_ok` dari sana, dan `ketua` mencocokkannya dengan L7 kertas sampai studi ditutup ([30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md) §10.1) | `consent_ok` |

## Yang harus disediakan sistem di tahap 3

Kamus ini menambah syarat data berikut ke [Catatan perubahan sistem](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti). *(1.1)* Sejak 1 November 2026 semuanya dibangun sekarang, tidak lagi menunggu G2 ([04](04_CATATAN_KEPUTUSAN.md)); kolom terakhir menunjuk dokumen implementasinya.

| Syarat | Untuk | Perubahan sistem di 01 | Dirancang di |
| --- | --- | --- | --- |
| `error_code`, `reason_code`, dan skor tindakan disimpan di setiap percobaan | Analitik 6, 9, 10 | no. 1, 2 | [31](31_IMPLEMENTASI_BANK_SOAL_BARU.md) §10, [32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md) |
| Pengerjaan pertama yang terputus dilanjutkan, bukan dibuat baru | Data inti | no. 16 | [33](33_IMPLEMENTASI_MODE_PENELITIAN.md) |
| Setiap jeda masukan 20 detik atau lebih dicatat; selang narasi diputar bisa dibaca dari log | Analitik 5 | no. 5 | 33 (`input_idle`) |
| Saat butir bisa dijawab dicatat (petunjuk aktif, pilihan tindakan dan alasan tampil, kartu Benar atau Salah disentuh pertama) | Waktu putus, NON-TEB | no. 14 | [29](29_IMPLEMENTASI_DATA_PENELITIAN.md) §6.1 (`ready_ms`), 32 (`reason_ready_ms`) |
| Ketukan di luar bagian ketuk dicatat dengan jaraknya ke kunci petunjuk aktif | NON-SEN | no. 14 | 29 §6.2 (koordinat), 31 §9 (geometri), 35 (jarak) |
| Event membuka lagi bacaan di dalam tantangan | Analitik 8 | no. 14 | 29 §6.3 (`passage_reopened`) |
| Event penutupan skenario setelah kesempatan ketiga (setara `clue_failed`) | Analitik 1 jenis (a) | no. 2 | 32 (`scenario_failed`) |
| Kode konteks narasi instruksi | Analitik 7 | no. 6 | 32 §8 (`challenge_instruction`) |
| Sesi game menyimpan `field_session_code` dan nomor pertemuan | Semua | no. 11 | [28](28_IMPLEMENTASI_SESI_LAPANGAN.md) §14 |
| Wilayah yang dibuka token terbuka untuk semua siswa sesi itu, walaupun wilayah sebelumnya belum tuntas; tantangan di dalam wilayah tetap terbuka berurutan | Analitik 1 (`locked`) | no. 11 | 28 §9 |
| Ambang T_idle dan T_teb disimpan di konfigurasi studi dan bisa diubah tanpa mengubah kode; lapis turunan dihitung ulang dengan ambang baru | Analitik 5, 6 | no. 9, 16 | [35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md) §5 |
| Lapis turunan menyimpan `dictionary_version` dan diuji otomatis dengan contoh data yang hasilnya dihitung tangan dari kamus ini | Semua | no. 9 | 35 |
| Lembar *Kamus Data* di ekspor peneliti diturunkan dari kamus ini | Semua | no. 14 | 29 §8, 35 §10.2 |
| *(1.1)* Daftar izin berkode dan saat mundur tersimpan di sistem | `consent_ok` | no. 23 | [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md) §5, §10.1 |
| *(1.1)* Jawaban tes, angket, dan skala sikap tersimpan per butir dengan waktunya | Variabel instrumen | no. 21, 22 | [34](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md) §11.4 |

## Penyesuaian di dokumen lain

Perubahan yang ikut dikerjakan bersama kamus ini:

- **03:** DIG-HOX hanya D2 (bukan "D2, D1"); analitik 8 berlaku untuk D1–D3, analitik 10 hanya D2. Alasannya model *between-item*: dimensi ikut butir, bukan kode.
- **Gambar peta bukti:** D3 memakai nama yang disetujui; Isi Rumpang dan Benar atau Salah hanya D1, Cari Objek babak 2 D3 dengan babak 1 sebagai PBK, Skenario Digital hanya D2; pola kesalahan menyebut kode BUD.
- **01:** analitik 3 tidak lagi menyebut Susun Gambar, yang tidak dinilai sejak kisi-kisi 06. Contoh data mentah mendapat kolom `rapid_guess` (kosong untuk Isi Rumpang) dan memakai kode bank. Catatan perubahan sistem no. 14 menyebut syarat data dari kamus ini.
- **06:** siswa yang belum selesai melanjutkan di pertemuan 5 (susulan), bukan "di pertemuan berikutnya", sesuai keputusan token (P5 untuk tantangan yang tertinggal).
- **08:** aturan kode *Periksa Unggahan* di *Cara membaca* tidak lagi memakai INF-REL: bagian ketuk lain diberi kode jenis petunjuknya, dan ketukan di tempat kosong dekat kunci = NON-SEN tanpa memakai kesempatan (keputusan 26 Oktober 2026).
- **01 dan 03:** ambang sementara jeda 3 menit dan tebakan cepat di bawah 3 detik (keputusan 26 Oktober 2026).

*(1.1)* **Untuk sesi koordinator** (ditulis 3 November 2026; **sudah dibawa** 3 November 2026: tiga butir pertama sudah ada di 13, 16, dan 17 sejak sesi koordinator 31–35, butir keempat dibawa sesi tindak lanjut dokumen):
- **16** → *Variabel untuk kamus data*: baris `risk_status` … `support_na` menunjuk lembar `risiko_gabungan`, bukan variabel peserta (29 K9).
- **17** → *Variabel untuk kamus data*: `sikap_missing_reason = tanpa_izin` hanya diisi di analisis dari L7.
- **13** → *Penskoran*: `*_theta` dihitung di analisis, bukan sistem; ambang tebakan cepat tes menunggu uji coba.
- **13, 16, 17**: kalimat "masuk `kamus-1.1` setelah uji coba" menjadi "masuk `kamus-1.1` (draf 3 November 2026); angkanya ditetapkan ulang setelah uji coba".

Penegasan yang menafsirkan dokumen sebelumnya. Tafsiran ini tidak dikoreksi pengembang saat menyetujui kamus, jadi ikut berlaku:

- "Dilewati" jenis (a) juga mencakup skenario yang ditutup setelah kesempatan ketiga, karena aturan tiga kesempatannya sama dengan petunjuk Cari Objek (keputusan 25 Oktober 2026).
- "Dibantu" untuk analisis utama hanya bantuan isi (ISI, STR, BHS). Bantuan teknis, semangat, dan gangguan dicatat terpisah sebagai kovariat.
- Ketukan di tempat kosong tidak memakai kesempatan, seperti game sekarang, yang hanya mengirim ketukan pada benda.
- Benda sasaran petunjuk lain di Temukan Benda diberi INF-LIT, karena kisi-kisi hanya mengizinkan INF-BUD dan INF-LIT di babak 1.
- NON-TEB tidak dihitung untuk Isi Rumpang (01 dan 03 tidak menyebut pengecualian), karena kata-kata satu bacaan diseret berturut-turut dan selang antarseretan bukan waktu memutuskan. `rapid_guess` di Isi Rumpang dibiarkan kosong.
- Token yang membuka wilayah sebuah pertemuan juga melewati aturan buka berurutan antarwilayah, supaya siswa yang belum menuntaskan wilayah sebelumnya tetap bisa bermain bersama kelasnya; sisanya dikerjakan di pertemuan 5.

## Keputusan pengembang

Empat pertanyaan di draf diputuskan pengembang pada 26 Oktober 2026:

1. **Kode bagian lain di *Periksa Unggahan*: setuju dengan usulan.** Mengetuk bagian ketuk yang bukan kunci dan bukan pengecoh utama (termasuk kunci petunjuk lain) diberi kode jenis petunjuk itu: atribusi BUD-ATR, representasi BUD-REP, izin BUD-IZN, menghargai tradisi BUD-ETK. Mengetuk di luar bagian ketuk mana pun = NON-SEN dan tidak memakai kesempatan. 08 sudah disesuaikan.
2. **Jeda tanpa masukan: ambang sementara 3 menit.** Hanya bagian jeda yang melebihi ambang yang dipotong dari waktu aktif. Ambangnya harus mudah diubah, jadi disimpan sebagai pengaturan dan ditetapkan ulang setelah uji coba.
3. **Data siswa yang bisa dianalisis: setuju dengan usulan.** Izin lengkap, bukan sekolah uji coba, dan percobaan pertama yang bukan dari perangkat bersama pada sedikitnya 80% butir literasi digital (49 dari 61).
4. **Tebakan cepat: dipakai apa adanya di analisis utama**, dengan ambang sementara di bawah 3 detik. Analisis kedua memperlakukan tebakan cepat sebagai data hilang.

### `kamus-1.1` (draf, 3 November 2026)

`kamus-1.1` tidak membawa keputusan baru. Isinya menurunkan keputusan pengembang yang sudah dicatat di [04](04_CATATAN_KEPUTUSAN.md) dan dokumen implementasi:

1. `kamus-1.1` ditulis sekarang sebagai `draf pengembang`, direvisi setelah uji coba, dan dikunci di G4 (2 November 2026; 35 §19 no. 6).
2. Analisis memakai kode kesalahan dari bank terbaru; kode saat percobaan tetap di data mentah (2 November 2026; 31 §22 no. 7, 35 §19 no. 5).
3. "Pengambilan data selesai" = jam putaran hitung (2 November 2026; 35 §19 no. 7).
4. Pengerjaan ulang tetap terbuka di P2–P4 setelah kelima tantangan wilayah pertemuan itu tuntas (2 November 2026; 33 §17 no. 5).
5. Ambang di kolom studi, diubah `ketua`, dikunci di G4 (35 §19 no. 2).
6. Data mentah tetap; lapis turunan dipotong pada saat mundur (35 §19 no. 4).
7. Jawaban butir risiko daring hanya sebagai angka gabungan (29 K9).
8. Status tes berasal dari modul; L6 tidak diimpor (29 K12).
9. `manual_code` dari L5: kode butir itu atau `*-LAIN` dimensinya, hanya bila disepakati dan berbeda dari kode game; kode alasan yang dikode tangan tetap di lembar (3 November 2026; 02 → L5, 29 §19 no. 15–17).
