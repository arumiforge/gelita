# Catatan Keputusan Revisi 10 Analitik

Per 3 November 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md)

Catatan ini merekam keputusan selama revisi, dari yang terbaru. Keputusan untuk implementasi nanti dicatat di dokumen implementasi dan di catatan keputusan perombakan kelas 5 saat pekerjaannya dimulai.

## 3 November 2026

**31 disetujui pengembang; akibatnya dibawa ke dokumen lain** (cabang `r10-koordinator-31`, dari `main` setelah PR #324) · `asal: pengembang` · `disetujui pengembang` · tidak merevisi produk

Pengembang meninjau [31](31_IMPLEMENTASI_BANK_SOAL_BARU.md) dan menyetujui keenam pertanyaan terbukanya. No. 1–4 sudah dijawab sesi koordinator 2 November 2026 (entri di bawah). Usulan no. 5 dan 6 disetujui apa adanya: penanda baris bacaan digital ditetapkan saat implementasi bersama mock-up, dan `HUNT_SCENE_DP` diukur dari tata letak sekarang lalu dikunci tes. Untuk no. 4 pengembang menegaskan pilihan yang sama: AI mendraf terjemahan bahasa Inggris saat berkas data ditulis di gelombang D, lalu pengembang memeriksanya. Status 31 naik ke `disetujui pengembang` (31 §22 no. 9–11).

*Akibat ke dokumen lain* di 31 §23 dibawa tanpa keputusan baru:
- **29:** lembar `percobaan` mendapat `error_code` dan `manual_code`; lembar `pengerjaan` mendapat `variant_source` dan `booklet_at_open`; lembar `butir` mendapat label dan beban permukaan; ada lembar baru `kode_pilihan` (hanya `ketua`); baris titik sambung no. 1 diperluas ke bank baru.
- **28:** panel pemantauan mendapat baris "Belum punya kursi", karena tanpa kursi tantangan yang dinilai belum terbuka (31 K10); baris no. 20 di titik sambung menunjuk 31 §11.
- **20 dan 21:** workbook, berkas data bank, dan halaman konten panel memuat label pengembang dan kode kesalahan, jadi tidak ikut paket validator; berkas validator tetap dibuat dari 08.
- **03:** variasi Susun Gambar `a`/`b` dan `c … k`, `variant_source`, `BOOKLET_MISSING`, kode bagian ketuk, kode genre dan tampilan bacaan, asal label `developer`/`expert`.
- **08:** kalimat "pemetaan huruf … ditulis di dokumen implementasi" menunjuk 31 §14.
- **01:** baris perubahan sistem no. 1, 3, 4, 8, 17, 20 menunjuk 31.
- **31:** `BankRules::ENGINES[5]` ditulis `skenario`, sesuai 32.

Yang masih menunggu: glosarium (saat implementasi) dan kolom 31 di 11 → *Variabel butir* (saat `kamus-1.1` ditulis ke 11). Baris 31 di 00 juga dibetulkan; sebelumnya tertulis `disetujui pengembang` 2 November 2026 dengan frasa "isi instrumen" yang tidak cocok.

Diubah: 31, 01, 03, 08, 20, 21, 28, 29, 00 (dua baris 31), 04, `docs/PROGRESS.md`, PDF siap cetak.

## 2 November 2026

**Teks en T11 paragraf 1 di 30 disamakan dengan id** (cabang `r10-30-t11-en`) · `asal: pengembang` · `disetujui pengembang` · tidak merevisi produk

Pengembang meminta usulan di [23 → Akibat ke dokumen lain](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#akibat-ke-dokumen-lain) diterapkan: di [30 §17](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#17-teks-layar-dan-panel), paragraf 1 T11 en "that may have made you feel uncomfortable" menjadi "that may make you feel uncomfortable", sama dengan "yang mungkin membuatmu tidak nyaman" di T11 id. Teks id T11 dan isi 23 yang diserahkan ke peneliti (*commit* `f45d274`) tidak berubah, karena 23 tidak memuat teks en. Status 30 tetap `disetujui pengembang`. Baris 30 di *Akibat ke dokumen lain* 23 ditandai sudah.

Diubah: 30 (§17), 23 (*Akibat ke dokumen lain*, satu baris), 04, PDF siap cetak 04, 23, dan 30.
**Sesi koordinator bagian 8: akibat 34 dibawa ke 01, 13, 17, 18, 27, 28, 29, dan 30** (cabang `r10-dok-34-modul-tes-sikap`, setelah PR #323) · `asal: pengembang` (penyelarasan berbantuan AI, diminta pengembang) · status dokumen tidak berubah · tidak merevisi produk

Yang dibawa dari [34 → Akibat ke dokumen lain](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#19-akibat-ke-dokumen-lain), tanpa keputusan baru:
- **28:** §21 no. 2 dan 3 dicoret (34 K1, §10.2); arah ke `/tes` setelah daftar (§6.5 langkah 8); status tes per kode, tombol *Tambah waktu*, dan tombol *Mulai tes awal/akhir* di pemantauan (§12); kolom `test_started_at`, `test_started_by` di `field_sessions` (§14); titik sambung no. 21 dan 22 (§18); baris 34 di §23 bertanda sudah.
- **13:** butir "Belum diputuskan" diganti *Mulai tes bersama* (34 K1); tambah menit oleh P1 di *Aturan selama tes* (34 K3); kalimat "tahap 3" diganti aturan 1 November 2026.
- **29:** lima lembar dan kolom dari 34 §11.4 di §7.2; sumber `risiko_gabungan` di §7.6.
- **30:** nama tabel `instrument_*` untuk hapus (§7.3) dan untuk hapus manual (§13); `risk_offered` dan `risk_reconsent` di sesi jenis `risk` (§5.4, §6); titik sambung (§18).
- **27:** bagian 8 tidak menambah kemampuan (§5.1).
- **17:** kalimat lama "tahap 3, setelah G2" diganti; isi skala hanya berbahasa Indonesia di sistem (34 K13), terjemahan en ditulis terpisah bila perlu; baris *Mode penelitian* di *Pelaksanaan di P5* (34 K12).
- **18:** celah no. 3 bertanda dibawa; rujukan Bootstrap lokal ke 34 §7.1.
- **01:** rujukan ke 34 di daftar bagian dan baris no. 21, 22.

Yang belum dibawa, karena menunggu: 16 *Cara pelaksanaan* no. 9 (konfirmasi peneliti, 34 §17 no. 4), 11 `kamus-1.1` (bagian 9, setelah uji coba), `.claude/rules/glossary.md` (saat implementasi, 34 §16), dan berkas serah terima (bila dibuat versi baru).

Diubah: 01, 13, 17, 18, 27, 28, 29, 30, 34 (§19 status), 04, `docs/PROGRESS.md`, PDF siap cetak.

**Akibat 30 dibawa ke 22, 26, 27, dan 29; konfirmasi IDCloudHost dari peneliti** (sesi koordinator untuk 30, cabang `r10-cek-30`, setelah PR #324) · `asal: pengembang` (penyelarasan) dan `asal: peneliti` (konfirmasi penyedia server) · status dokumen tidak berubah · tidak merevisi produk

Atas permintaan pengembang, baris *Akibat ke dokumen lain* di [30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#23-akibat-ke-dokumen-lain) yang bertanggal 2 November 2026 dibawa ke dokumen tujuannya, tanpa keputusan baru:
- **29:** lembar `izin` di unduhan utama (§7.2), `consent_ok` dihitung dari lembar itu (§10.6), baris bagian 4 di titik sambung, dan catatan di kepala dokumen bahwa bagian 4 digabung sesudah bagian 3 dan tidak mengubah baris milik bagian 3 di berkas bersama (30 keputusan no. 7, 9, 10).
- **27 §6.1:** "setiap bagian diimplementasikan berurutan" diganti: bagian 3 dan 4 paralel di gelombang E, bagian 4 digabung kedua; `003800` nilai akhir bagian 1–4, dinaikkan lagi bagian 5–9.
- **22 dan 26:** provinsi dan kabupaten tetap disimpan setelah studi ditutup dan komite diminta menilainya (22 *Masa simpan dan pemusnahan*, 26 catatan R2; 30 keputusan no. 11). 26 L3, P18, dan syarat KG2 menyebut sakelar `gelita.researchPrivacy` dan `KEDUA_HARI` (30 keputusan no. 8).
- **23:** kalimat "tahap 3" di T4, T11, dan T12 sudah dibetulkan sesi cek 23 (PR #320); tidak ada yang diubah.
- **`docs/08_DEPLOYMENT.md`:** tetap diubah saat implementasi (30 §20).

**Konfirmasi IDCloudHost** (diperoleh peneliti, disampaikan pengembang 2 November 2026; `asal: peneliti`). Cadangan bisa dihapus pelanggan (kendali penuh); IDCloudHost tidak membuat cadangan otomatis kecuali pelanggan mengaturnya; data di database utama dihapus atau dianonimkan sesuai prosedur dan dasar penghapusannya; cadangan yang memuat data itu dihapus saat masa simpannya berakhir (bawaan 30 hari), sehingga cadangan lama tidak disimpan tanpa batas; IDCloudHost memberi informasi atau bukti bahwa cadangan sudah dihapus, termasuk di infrastruktur internal penyedia. Ini menjawab 30 §21 no. 6: langkah 5 penggantian cadangan (30 §7.5) bisa dijalankan, dan buktinya disimpan `ketua` bersama berita acara. Dimasukkan ke 30 §7.5 dan §21–§23, 22 (*Penyimpanan dan enkripsi*, peran prosesor), dan 26 (aliran data no. 8, P6). Isinya ditulis di perjanjian prosesor.

22 dan 26 berubah sesudah *commit* serah terima `f45d274`, jadi PDF siap cetak keduanya dibuat ulang dan dikirim kembali ke peneliti.

Diubah: 22, 26, 27, 29, 30, 04, `docs/PROGRESS.md`, PDF siap cetak.

**Gelombang A dan B digabung; pertanyaan terbuka yang bisa diputuskan sekarang dijawab** (sesi koordinator, cabang `sky/sweet-allen-0md2pw`) · `asal: pengembang` · `disetujui pengembang` · tidak merevisi produk

Kesebelas PR gelombang A (22, 23, 27–30) dan B (31–35) sudah digabung ke `main`. Sebelum digabung, `main` dimasukkan ke setiap cabang. Konflik di 04, 00, `docs/PROGRESS.md`, dan PDF siap cetak diselesaikan dengan menyimpan kedua sisi, lalu PDF dibuat ulang. Satu salah tempat dibetulkan saat itu: status 29 sempat tertulis di baris bagian 1 tabel *Tahap dokumen 3*.

*Commit* serah terima ke peneliti tetap **`f45d274`**, seperti dicatat atas permintaan pengembang di entri *Commit serah terima ke peneliti* di bawah. PDF 22, 23, 24, dan 26 tidak berubah sesudahnya. Perubahan dari peneliti masuk lewat pengembang dengan label `asal: peneliti` (entri 1 November 2026).

Pertanyaan terbuka di 27–35 diperiksa. Sebagian besar menunggu pihak lain (peneliti, ahli media, tim katalog, penyedia server) atau angka uji coba, jadi tetap terbuka. Tujuh bisa diputuskan sekarang; pengembang menjawabnya dalam dua putaran:

1. **Jumlah kisi-kisi tetap di kode** (`DifficultyRules`; [31](31_IMPLEMENTASI_BANK_SOAL_BARU.md) §21 no. 1, §22 no. 5). Tidak dipilih: sheet `rules` di workbook.
2. **Kartu jangkar bertumpuk dengan satu Cek** (31 §21 no. 2, §22 no. 6); ahli media memeriksanya di langkah 2. Tidak dipilih: kartu kedua muncul setelah pernyataan pertama dijawab.
3. **Analisis memakai kode kesalahan dari bank terbaru** (31 §21 no. 3, §22 no. 7; [35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md) §19 no. 5). Kode saat percobaan tetap di `item_tries.error_code` dan masuk lembar turunan sebagai `error_code_at_try`. Baris *Kode kesalahan mentah* dan kolom `error_code` di 35 disesuaikan. Tidak dipilih: kode saat percobaan.
4. **Pengerjaan ulang tetap terbuka di P2–P4** setelah kelima tantangan wilayah pertemuan itu tuntas ([33](33_IMPLEMENTASI_MODE_PENELITIAN.md) §15 no. 4, §17 no. 5). Tidak dipilih: hanya di P5.
5. **`kamus-1.1` ditulis ke 11 sekarang sebagai `draf pengembang`** (35 §18 no. 1, §19 no. 6), lalu direvisi setelah uji coba, dan dikunci di G4. Tidak dipilih: satu sesi setelah KG3b bersama peneliti (usulan AI).
6. **"Pengambilan data selesai" = jam putaran hitung**, tanpa kolom tanggal akhir di studi (35 §18 no. 3, §19 no. 7). Tidak dipilih: tanggal akhir yang diisi `ketua`.
7. **Terjemahan bahasa Inggris bank baru didraf AI sekarang** dan disetujui pengembang; butir yang direvisi dari validasi diterjemahkan ulang (31 §21 no. 4, §22 no. 8). Tidak dipilih: diterjemahkan sekali setelah matriks revisi, sebelum G3 (usulan AI), atau studi hanya berbahasa Indonesia.

**Ditutup tanpa keputusan baru**, karena sudah dijawab dokumen lain:
- 28 §21 no. 2–3 (batas 20 menit di P5 dan angket yang terlambat) dijawab di 34;
- 33 §15 no. 1: 33 dan 35 sama-sama memakai `research_studies.idle_threshold_ms` dan `rapid_guess_ms`;
- 32 §17 no. 4: blok migrasi bagian 5–9 tidak bertabrakan, jadi tidak ada penomoran ulang.

**Pekerjaan berikutnya** (baris di `docs/PROGRESS.md`):
- sesi koordinator membawa *Akibat ke dokumen lain* dari 22, 23, dan 27–35 ke dokumen lain;
- `kamus-1.1` ditulis ke 11;
- draf terjemahan bahasa Inggris bank baru;
- gelombang kode C (bagian 1, 27).

Diubah: 28, 31, 32, 33, 35, 00 (tabel gelombang), 04, `docs/PROGRESS.md`, dan PDF siap cetak.

**32 diselaraskan dengan format data skenario bagian 5** (cabang `r10-dok-32-skenario-digital`, setelah PR #317) · `asal: pengembang` (penyelarasan berbantuan AI) · status 32 tetap `draf pengembang` · tidak merevisi produk

PR #317 digabung sebelum penyelarasan ini masuk. [31](31_IMPLEMENTASI_BANK_SOAL_BARU.md) (PR #314) menetapkan bahwa data skenario milik bagian 5. 32 kini membacanya dari sana, tanpa keputusan baru:
- pilihan, skor, kode, dan umpan balik dari tabel `scenario_options`, bukan `config_json`/`answer_key_json`;
- `d2_topic` dan `explanation_*` dari `challenge_items`;
- kode tindakan lewat `ErrorCodeResolver`;
- babak *Periksa Unggahan* dari `round` dan `find_part`;
- panjang kolom kode di `item_tries` sama dengan 31.

32 menambah `BankRules::ENGINES[5] = 'skenario'`, yang di 31 §8.2 masih tertulis `{engine skenario}`. Nomor migrasi 32 (`004000`) tidak bertabrakan dengan 31 (`0039xx`), 33 (`0041xx`), atau 35 (`0043xx`).

Diubah: 32, 04, PDF siap cetak 04 dan 32.

**34 disetujui pengembang** (cabang `r10-dok-34-modul-tes-sikap`, setelah PR #319) · `asal: pengembang` · `disetujui pengembang` · tidak merevisi produk

Pengembang menyetujui [34](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md) apa adanya, termasuk keputusan rancangan K5–K15 dan catatan rencana `satu_sesi`. Persetujuan ini untuk rancangan sistem; butir tes, angket, dan skala sikap tetap `needs_verification` sampai validasi ahli dan uji keterbacaan. Pertanyaan terbuka di 34 §17 tetap untuk peneliti. Akibat ke dokumen lain di 34 §19 menunggu sesi koordinator.

Diubah: 34 (status, §18 no. 5), 00 (dua baris 34), 04, `docs/PROGRESS.md`, PDF siap cetak.

**Commit serah terima ke peneliti: `f45d274`** (cabang `r10-cek-22`, dari `main` setelah PR #312) · `asal: pengembang` · `disetujui pengembang` (diminta pengembang 2 November 2026) · tidak merevisi produk

Gelombang A untuk 22 dan 23 selesai: cek kemutakhiran 23 (PR #311) dan 22 (PR #312) sudah digabung. Paket untuk peneliti (keputusan no. 4 di entri *Pembagian kerja baru*, 1 November 2026) diambil dari *commit* **`f45d274`** (`f45d27447b30f35d692f0ced422471ef8625fce9`, gabungan PR #312 ke `main`, 2 November 2026). Di *commit* itu `cetak.mjs` tidak membangun ulang apa pun (36 dokumen tidak berubah), jadi semua PDF di bawah sesuai dengan Markdown-nya.

| Berkas | Isi dari *commit* (kaki halaman PDF) | Halaman |
| --- | --- | --- |
| `dokumen-pdf/cetak/22_PERMOHONAN_IZIN_ETIK.pdf` | `81647d8` (2 November 2026) | 51 |
| `dokumen-pdf/cetak/23_SURAT_INFORMASI_DAN_PERSETUJUAN.pdf` | `52541ed` (2 November 2026) | 33 |
| `dokumen-pdf/cetak/24_UJI_KETERBACAAN.pdf` | `8b46ca2` (1 November 2026) | 20 |
| `dokumen-pdf/cetak/26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.pdf` | `81647d8` (2 November 2026) | 19 |
| `dokumen-pdf/serah-terima-peneliti.pdf` (berkas serah terima v1.5) | sumber HTML di `f45d274` | 37 |

- Pengembang mengirim kelima berkas ini ke peneliti, PDF saja. Tanggal kirim dicatat di sini bila berbeda dari 2 November 2026.
- Perubahan dari peneliti atau komite etik masuk lewat pengembang dengan `asal: peneliti` atau `asal: komite etik` (keputusan no. 3). Setiap kiriman PDF berikutnya dicatat di 04 dengan *commit*-nya, supaya selalu jelas versi mana yang dipegang peneliti.
- Yang masih terbuka dan tidak menahan penyerahan: batas `retention_days` 3650 di formulir studi admin (kode bagian 4, 30 §11); isian di [22 → Daftar isian sebelum diajukan](22_PERMOHONAN_IZIN_ETIK.md#daftar-isian-sebelum-diajukan) diisi peneliti di luar repo.

Diubah: 04, 00 (baris 22 dan 23 di daftar dokumen dan tahap 2, catatan *Peneliti menerima PDF saja*), `docs/PROGRESS.md`, PDF siap cetak 00 dan 04. *Commit* serah terima sendiri tidak berubah: perubahan ini hanya mencatatnya.

**34 diselaraskan dengan rencana `satu_sesi` di 28** (cabang `r10-dok-34-modul-tes-sikap`, setelah PR #315) · `asal: pengembang` (pemeriksaan berbantuan AI) · status 34 tetap `draf pengembang` · tidak merevisi produk

28 K19 (rencana pertemuan per studi, digabung saat 34 ditulis) meminta 34 menulis bahwa rencana `satu_sesi` (KG2) tidak membuka modul tes, angket, butir risiko daring, dan skala sikap. Ditulis di 34 §5.1 dan K12; baris 28 di 34 §19 kini juga mencoret 28 §21 no. 3 (angket terlambat, diatur di 34 §10.2). Tidak ada keputusan baru.

**22 dicek kemutakhirannya dan siap diserahkan ke peneliti sebagai PDF** (gelombang A, cabang `r10-cek-22`, 1–2 November 2026) · `asal: pengembang` (pemeriksaan berbantuan AI) · status 22 tetap draf · tidak merevisi produk

[22](22_PERMOHONAN_IZIN_ETIK.md) dicek terhadap entri 04 sesudah *commit* terakhirnya (cek pasal PP 33/2026), terutama *Pembagian kerja baru* di bawah, terhadap 23, 24, 26, 30, 03, dan 01, dan terhadap kode. Tidak ada keputusan baru; yang diubah hanya yang sudah diputuskan. Nomor pasal PP No. 33 Tahun 2026 dan pemetaan formulir BRIN tidak disentuh.

- **Aturan lama "sistem baru diubah setelah G2" diganti** di daftar gerbang (*Desain penelitian*), baris *Develop* 3 tabel ADDIE, garis waktu (baris baru "Sejak November 2026, bersamaan dengan tahap 2"; baris "Setelah G2" kini berisi revisi validasi, ahli media langkah 2, dan uji perangkat menuju G3), dan alasan perpanjangan masa berlaku. Aturan baru: Prototipe II dibangun bersamaan dengan tahap 2 dan tidak dipakai dengan anak sebelum persetujuan etik terbit; G3 setelah G2; versi dikunci di G4; validator menilai versi yang dibekukan di 20 prasyarat no. 4. Dicatat sebagai keputusan no. 31 di 22.
- **"Dibangun di tahap 3"** untuk hapus sebagian, salinan dan pembetulan data satu anak, masa simpan 7305 hari, dan teks centang T4 diganti "dibangun sebelum sistem dipakai dengan anak"; keputusan no. 5 (27 Oktober 2026) diberi catatan.
- **Teks centang sekarang:** `assentLabel` ternyata sudah menyebut pencatatan untuk penelitian (bukan "hanya permainan"), dan tidak diganti: 30 §5.1 menambah kunci `assentLabelStudy` untuk studi dengan mode lapangan.
- ***Akibat ke dokumen lain*:** baris 01_DATABASE memakai 7305 hari (bukan ± 7300) dan mencatat temuan kode untuk 30 §11: formulir studi admin membatasi `retention_days` sampai 3650; baris 26 menjadi "sebagian sudah" (nama lembaga masa peralihan belum disebut di 26); baris model Plomp dan uji coba satu kelas ditandai sudah; baris baru untuk berkas serah terima v1.4 (empat kalimat aturan lama), 01 baris *Develop* 3 (opsional), dan kolom *Siap* 26.
- **Yang dicek dan tetap benar:** semua tautan jangkar di 22; nama T1–T13 dan varian T8/T9 di 23; KG1 di 24 (K01–K05, empat sesi ± 60 menit, LK1–LK3, masa simpan 20 tahun); P15 di 26; rujukan 30 §4–§11 dan §22; kode `KG`, `U01-A03`, `S03-A07` di 03; daftar kerja no. 12, 15, 17, 18, 22 dan perubahan no. 23 di 01; di kode: `retention_days` bawaan 1825, `Config\Session` masih `DatabaseHandler` dengan `HashedIpSessionHandler` belum dipasang, akun staf terkunci 15 menit setelah 5 kali gagal, ekspor kedaluwarsa 7 hari.

**Putaran kedua (2 November 2026, atas permintaan pengembang: "kerjakan update dokumen").** Hal yang semula hanya dicatat di *Akibat ke dokumen lain* 22 dikerjakan di cabang yang sama, tanpa keputusan baru:
- **Berkas serah terima v1.5:** enam kalimat aturan lama diganti aturan 1 November 2026 (pembuka peta jalan, tabel dokumen tahap 4, alasan dua langkah ahli media, tabel keputusan, tanya jawab "Kenapa sistem belum diubah" yang kini berbunyi "Apakah sistem dibangun sebelum validasi dan izin etik selesai?", dan butir pengingat agenda); PDF dibuat ulang dengan `render.mjs` (37 halaman). Rujukan versi di 00 dan `dokumen-pdf/README.md` ikut diubah.
- **26:** keterangan kolom *Siap* kini mengartikan *tahap 3* sebagai perubahan yang dibangun sekarang dan siap sebelum KG2, berlaku juga untuk tabel hak; nilai kolom tidak berubah. Nama lembaga masa peralihan (Kementerian Komunikasi dan Digital) disebut di hak mengadu dan P12, sama dengan 22. Status dan versi 26 tidak berubah.
- **01:** baris *Develop* 3 tabel ADDIE sama dengan 22.
- **Tetap terbuka:** batas `retention_days` 3650 di formulir studi admin adalah kode bagian 4 (30 §11), dikerjakan saat bagian 4 diimplementasikan.
- Dengan ini kalimat aturan lama di 26 (dari daftar *Kalimat lama yang belum diubah* di entri *Pembagian kerja baru*) sudah diubah; yang tersisa di daftar itu: 11, pembuka 17 dan 18, `gambar/peta-jalan.svg`, dan 27–30 (gelombang A).

Diubah: 22, 26, 01 (satu baris tabel ADDIE), berkas serah terima (sumber HTML dan PDF v1.5), `dokumen-pdf/README.md`, 00 (dua baris 22, dua baris 26, rujukan versi berkas serah terima; ukuran 22 dibetulkan menjadi 203 KB), `docs/PROGRESS.md`, PDF siap cetak 00, 01, 04, 22, dan 26. *Commit* serah terima dicatat koordinator setelah PR digabung.

**Dokumen implementasi bagian 9 didraf: lapis data turunan dan `kamus-1.1` usulan** (cabang `r10-dok-35-lapis-turunan`, gelombang B; wawancara 2 November 2026) · `asal: pengembang` (draf berbantuan AI, empat keputusan pengembang) · [35](35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md) `draf pengembang` · tidak merevisi produk

35 menulis perubahan sistem no. 9: satu kalkulator berversi (`App\Libraries\Derived\*`, fungsi murni yang diuji dengan contoh data hitungan tangan) yang menghitung semua data turunan 11 per putaran hitung satu studi, ditambah usulan `kamus-1.1`. `kamus-1.0` tidak diubah; sampai 11 diubah setelah uji coba, sistem menandai hasilnya `kamus-1.1-usulan`. Empat keputusan pengembang, semuanya usulan AI:

1. **Lapis turunan disimpan di tabel** `derived_*` (migrasi sementara `2026-01-01-004300_CreateDerivedLayer`), setiap baris membawa `run_id` dan `dictionary_version`; hasil yang berlaku diganti sekaligus lewat `research_studies.derived_run_id`. Tidak dipilih: dihitung saat unduhan dibuat.
2. **Ambang di kolom studi** (`idle_threshold_ms` 180000, `rapid_guess_ms` 3000, bawaan dari `Config\Gelita`), diubah `ketua` dengan log audit dan dikunci di G4. Tidak dipilih: hanya di `Config\Gelita`.
3. **Dihitung ulang saat unduhan bila basi** (sidik jari data mentah, lembar lapangan, izin, ambang, dan versi kamus), ditambah tombol *Hitung ulang* dan `gelita:turunan:hitung`. Tidak dipilih: hanya manual; setiap malam.
4. **Data mentah tetap, lapis turunan dipotong pada saat mundur** (`deactivated_at`, atau akhir `status_on`); peserta `withdrawn_keep` tetap `consent_ok = 1` dengan `withdrawn_on`. Tidak dipilih: hanya penanda di lembar turunan.

Keputusan turunan untuk ditinjau (K5–K14 di 35): variabel turunan angket dan skala sikap dihitung lapis turunan, bukan modul bagian 8; `*_theta` tes tetap di lapis 3; jarak ketukan kosong dihitung server; lembar turunan tanpa jawaban, kunci, butir risiko per siswa, atau id internal; lapis turunan hanya untuk studi `field_mode`; tanpa kemampuan baru. Temuan untuk sesi koordinator: 16 masih mendaftar `risk_*`/`support_*` sebagai variabel peserta, padahal 29 K9 hanya mengizinkan angka gabungan; `sikap_missing_reason = tanpa_izin` (17) tidak pernah terjadi di sistem. Nama data bagian 5–8 adalah anggapan, didaftar di 35 §15 dan §20.

Diubah: 35 (baru), 00 (*Daftar dokumen*, baris bagian 9 di *Tahap dokumen 3*), 04, `docs/PROGRESS.md`, PDF siap cetak.

**Dokumen implementasi bagian 6 (32, Skenario Digital) didraf** (cabang `r10-dok-32-skenario-digital`, gelombang B) · `asal: pengembang` (disusun dengan bantuan AI; empat keputusan pengembang) · `draf pengembang` · tidak merevisi produk

[32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md) mengatur perubahan sistem no. 2, 6, dan 18: engine dua langkah (tindakan, lalu alasan), tantangan kelima di tiga wilayah, cerita penghubung `scenario_open`, 15 Serpihan Cahaya, narasi instruksi, dan aturan narasi. Empat keputusan pengembang dalam wawancara 2 November 2026:

1. **Kode engine tersimpan `skenario`**, jenis butir `scenario_choice`. Tidak dipilih: `scenario`, `pilih`.
2. **Narasi instruksi hanya lewat tombol ▶**, di kelima belas tantangan, tidak pernah otomatis; kode konteks audio `challenge_instruction`. Tidak dipilih: sekali otomatis ditambah ▶ (usulan AI), atau di kartu misi.
3. **Tanpa sakelar studi:** engine selalu ada, tantangan kelima muncul bila bank memuatnya, total serpihan dihitung dari node aktif (12 menjadi 15), aturan narasi berlaku juga di luar studi. Tidak dipilih: di balik `field_mode`.
4. **Urutan pilihan diacak sekali per pengerjaan**, tetap di ketiga kesempatan, dan disimpan di `answer_json` setiap percobaan. Tidak dipilih: diacak ulang setiap kesempatan.

Rancangan yang menurunkan sumber (tidak ditanyakan): kesempatan per skenario seperti petunjuk Cari Objek, lewat `submitAnswer()` dan `/responses`; bintang `bintang-1` tidak berubah (benar = tindakan berskor 2); pilihan dikirim dengan rujukan HMAC seperti `objectRef()`; kolom baru `item_tries.action_key`, `action_score`, `reason_key`, `reason_code`, `reason_ready_ms` dan `challenge_nodes.audio_instruction_*` di migrasi sementara `2026-01-01-004000`; event server `scenario_failed` setara `clue_failed`; rute `/penghubung/{wilayah}`; butir dan bacaan D1 tidak pernah membawa audio. Hal yang perlu diubah di dokumen lain (01, 06, 11, 12, 18, 29, 31, 33, 35, glosarium) dicatat di 32 → *Akibat ke dokumen lain* untuk sesi koordinator.

Diubah: 32 (baru), 00 (baris 32 di *Daftar dokumen* dan *Tahap dokumen 3*), `docs/PROGRESS.md`, PDF siap cetak 00, 04, dan 32.

**Bagian 8 dokumen implementasi didraf: modul tes, angket, dan skala sikap** (cabang `r10-dok-34-modul-tes-sikap`) · `asal: pengembang` (disusun dengan bantuan AI; empat keputusan pengembang) · `draf pengembang` · Prototipe I, belum merevisi produk

[34](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md) merancang perubahan sistem no. 21 dan 22 dari [01](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti), memakai 13–18, 28 §6 dan §11, 29 §7.6, dan 30 §5.4. Isi instrumen tetap `needs_verification` dan disimpan di berkas data, jadi revisi dari validasi cukup berupa suntingan data lalu impor ulang. Empat pilihan diputuskan pengembang dalam satu putaran, semuanya opsi yang disarankan:

1. **Batas 20 menit tantangan tertinggal di P5: tombol P1 menutup tantangan.** P1 menjaga waktu dengan jam lalu menekan *Mulai tes akhir*; tantangan di sesi lapangan itu tertutup dan semua siswa diarahkan ke modul, pengerjaan terbuka dibiarkan. Tes awal memakai tombol *Mulai tes awal*. Menjawab [28 → Pertanyaan terbuka](28_IMPLEMENTASI_SESI_LAPANGAN.md#21-pertanyaan-terbuka) no. 2. Tidak dipilih: jam P1 saja tanpa penegakan sistem; ditutup otomatis 20 menit setelah sesi buka.
2. **Isi tes, angket, dan skala di berkas PHP** `data/instrumen/{tes,angket,sikap}.php`, seperti `data/story.php`, diimpor berversi oleh `gelita:instrumen:impor`. Tidak dipilih: lembar workbook baru; berkas JSON.
3. **Perangkat bermasalah: P1 menambah menit** untuk siswa itu, dengan alasan dan log audit; tambahannya masuk unduhan. Tidak dipilih: jam berhenti saat diam; tanpa tambahan.
4. **Modul tes dan skala sikap mengikuti `field_mode`.** Saat mati, `/tes` tertutup dan Balai Refleksi tetap seperti sekarang. Instalasi uji validator melihat layarnya lewat sakelar pratinjau `instrumentPreview` untuk akun `uji.*`, dengan jawaban yang tidak pernah diekspor. Tidak dipilih: dua sakelar studi tersendiri; skala selalu menyala.

**Keputusan rancangan lain di 34** (dari sumber dan pencarian kode, untuk ditinjau pengembang): satu mesin dan satu set tabel untuk keempat instrumen (K5); halaman dirender server satu butir per permintaan, waktu tampil dari jam server, modul berjalan tanpa JavaScript (K6); satu baris jawaban per kolom data (K7); skor dan kode kesalahan dihitung saat bagian ditutup, variabel turunan di bagian 9 (K8); blok waktu `p1` dan `p23` (K9); tes awal ditolak setelah siswa bermain (K11); Balai Refleksi dalam mode penelitian hanya di sesi P5 dan susulannya (K12); isi instrumen hanya berbahasa Indonesia (K13); modul tidak menulis event game (K14); tidak ada kemampuan baru (K15). Migrasi `2026-01-01-004200_CreateInstrumentModule` (nomor sementara).

**Pertanyaan untuk peneliti** (34 §17): ambang tebakan cepat untuk tes; uji keterbacaan dengan tata letak teks selama gambar layar tiruan belum ada; angket terlambat ditutup saat P5 utama dibuka.

**Akibat ke dokumen lain** dicatat di [34 → Akibat ke dokumen lain](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#19-akibat-ke-dokumen-lain) untuk sesi koordinator: 28 (§21 no. 2 dicoret, `test_started_at`, status tes di pemantauan), 13, 16, 17, 18, 27, 29 (lembar tes, angket, sikap), 30 (nama tabel untuk hapus), 11, dan glosarium.

Diubah: 34 (baru), 04, 00 (*Daftar dokumen*, *Tahap dokumen 3*), `docs/PROGRESS.md`, PDF siap cetak.

**Dokumen implementasi bagian 7: mode penelitian, waktu aktif, dan perbaikan media** (cabang `r10-dok-33-mode-penelitian`, gelombang B) · `asal: pengembang` (draf berbantuan AI, empat keputusan pengembang) · [33](33_IMPLEMENTASI_MODE_PENELITIAN.md) `draf pengembang` · tidak merevisi produk

[33](33_IMPLEMENTASI_MODE_PENELITIAN.md) ditulis untuk perubahan sistem no. 5, 16, dan 19 di [01](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti), dari 10, 11, 12, 28 §5 dan §9, 29 §5.4 dan §15, dan kode per *commit* `97f82d5`. Bagian ini hanya menyimpan data mentah; angka turunan (waktu aktif, dilewati, tersensor, tebakan cepat) dihitung bagian 9 (35), dengan batas yang ditulis di 33 §4. Semua perilaku game ada di balik `field_mode`; data mentah direkam di semua mode. Migrasi memakai blok `0041xx` (sementara).

Empat keputusan pengembang, semuanya usulan AI:
1. **Pengerjaan pertama yang terputus pindah ke sesi game baru** lewat UPDATE bersyarat, dan setiap potongannya dicatat di tabel baru `challenge_attempt_segments`. Tidak dipilih: pindah tanpa tabel potongan; pengerjaan tetap di sesi lama dengan pemeriksaan kepemilikan per siswa.
2. **Pengerjaan ulang tetap memberi bintang seperti sekarang**; analisis mengabaikannya lewat `is_retake`. Tidak dipilih: pengerjaan ulang tidak mengubah bintang selama studi.
3. **Layar kecil ditolak bila lebar panggung di bawah 960 px**, sebagai pengaturan studi `min_stage_px`. Tidak dipilih: di bawah 1024 px; ponsel menurut jenis perangkat.
4. **Keluar mengirim antrean event dulu** (paling lama 5 detik); sisa antrean tetap berpenanda sesi dan dibuang setelah 14 hari. Tidak dipilih: kirim lalu hapus semua sisa; tetap seperti sekarang.

Temuan kode yang dicatat untuk ditinjau:
- `audio_usage_events` menyimpan jam perangkat tanpa offset, dan sejak 29 K6 baris `pause` tidak punya event mentah yang membawa offset. 33 menambah `audio_usage_events.clock_offset_ms` (K10); 29 perlu menyebutnya.
- Token objek Cari Objek sudah acak per pengerjaan, tetapi gambar benda tetap dikirim dengan jalur berkas yang memuat kode butir (M-04). 33 mengirim gambar lewat rute API bertoken.
- Antrean event sudah berpenanda sesi, jadi M-05 tinggal soal *Keluar* yang tidak menunggu antrean terkirim.
- M-11 sudah tertutup oleh 28 K9 dan 29 §5.5.

Pertanyaan terbuka di 33 §15, termasuk tempat T_idle dan T_teb yang dipilih bersama 35. Perubahan yang dibutuhkan 01, 11, 12, 28, 29, 30, 32, dan 35 ada di 33 §16, untuk sesi koordinator.

Diubah: 33 (baru), 00 (dua baris 33), `docs/PROGRESS.md`, PDF siap cetak 00, 04, dan 33.

**Dokumen implementasi bagian 5: bank soal baru ([31](31_IMPLEMENTASI_BANK_SOAL_BARU.md))** (cabang `r10-dok-31-bank-soal`, gelombang B) · `asal: pengembang` (draf berbantuan AI dan empat keputusan pengembang) · `draf pengembang` · merancang sistem, tidak merevisi produk

Dokumen implementasi untuk perubahan sistem no. 1, 3, 4, 8, 17, dan 20 di [01](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti), dari 06, 08, 11, 19, 25, dan kode per 2 November 2026 (`main` `97f82d5`). Isinya: bank 08 di berkas data dan workbook dengan kolom dan sheet baru; impor; `DifficultyRules` dan `BankRules` baru; area ketuk kedua babak Cari Objek berjarak paling sedikit 24 dp (menggantikan batas tumpuk 20%); kode kesalahan diturunkan server ke `item_tries.error_code` dari matriks kode eksplisit di bank; variasi pengerjaan pertama dari buklet dan butir jangkar `x`; Cari Objek dua babak; label AKM dan dimensi dengan asalnya (`developer`/`expert`); variabel beban permukaan dihitung saat impor; pemetaan huruf Susun Gambar selama studi. Migrasi sementara `003900` dan `003910`. Kemajuan di `ChallengeService` tetap milik bagian 2 ([28 §8](28_IMPLEMENTASI_SESI_LAPANGAN.md#8-kemajuan-mengikuti-siswa-bukan-sesi-game)); engine Skenario Digital milik bagian 6.

Empat keputusan pengembang (wawancara satu putaran):

1. **Data skenario dirancang di bagian 5** (sheet `scenarios`/`scenario_options`, tabel, impor, aturan bank); bagian 6 merancang engine, layar, skor, dan narasi. Tidak dipilih: data skenario di bagian 6; sheet di bagian 5 dan tabel di bagian 6.
2. **Bagian ketuk *Periksa Unggahan* berupa kotak di workbook** (x, y, w, h dalam persen adegan), unggahan digambar dari kotak sebagai teks; satu geometri untuk aturan jarak dan jarak ketukan di lapis turunan. Tidak dipilih: templat CSS dengan pemeriksaan Playwright; gambar unggahan dengan titik ketuk.
3. **Bank baru menggantikan bank lama di `main`**; bank lama di riwayat git dan `docs/arsip/bank-soal-lama/`; server produksi mendapat bank baru hanya saat pengembang menjalankan impor. Tidak dipilih: dua bank di balik sakelar; bank baru di cabang sampai G2.
4. **Di mode penelitian, siswa tanpa buklet tidak bisa membuka tantangan yang dinilai** (`BOOKLET_MISSING`, pesan ramah); Susun Gambar tetap terbuka. Usulan AI adalah variasi acak `a`/`b` dengan penanda; pengembang memilih menahan supaya pengerjaan pertama selalu mengikuti desain buklet. Tidak dipilih: acak dengan penanda (usulan AI); buklet B1 sebagai bawaan.

Pertanyaan terbuka di [31 → Pertanyaan terbuka](31_IMPLEMENTASI_BANK_SOAL_BARU.md#21-pertanyaan-terbuka): jumlah kisi-kisi tetap di kode (usulan) atau di data, tata letak kartu jangkar, kode bank yang direvisi setelah ada percobaan, terjemahan bahasa Inggris bank baru, penanda baris bacaan digital, dan ukuran adegan `HUNT_SCENE_DP`. Akibat ke 01, 03, 08, 11, 20, 21, 28, 29, bagian 6, dan glosarium dicatat di [31 → Akibat ke dokumen lain](31_IMPLEMENTASI_BANK_SOAL_BARU.md#23-akibat-ke-dokumen-lain) untuk sesi koordinator.

Diubah: 31 (baru), 00 (daftar dokumen dan *Tahap dokumen 3*), `docs/PROGRESS.md`, PDF siap cetak 00, 04, dan 31.

**29 dicek kemutakhirannya dan disetujui pengembang** (cabang `r10-cek-29`, gelombang A sesi 5) · `asal: pengembang` (pemeriksaan berbantuan AI, delapan keputusan pengembang) · `disetujui pengembang` · tidak merevisi produk

[29](29_IMPLEMENTASI_DATA_PENELITIAN.md) (bagian 3: pembersihan log, halaman log teknis admin, data mentah baru untuk kamus, unduhan `ketua` dengan lembar *Kamus Data* berversi, templat dan impor L1–L6) dicek terhadap entri 04 sesudah 30 Oktober 2026, terhadap 11, 26, 27, 28, dan 30, dan terhadap kode (`app/` dan `public/assets/js/` tidak berubah sejak 26 Oktober 2026).

- **Aturan baru:** kotak status tidak lagi menulis "diubah di tahap 3, setelah gerbang G2"; kini dibangun di gelombang kode E, perilaku penelitian di balik `field_mode`, tidak dipakai dengan anak sebelum persetujuan etik, versi dikunci di G4, instalasi uji validator tetap di *commit* beku 20.
- **Migrasi `003600` menghapus data:** ditulis di kotak status, §0, dan §5.8 bahwa di server produksi migrasi itu hanya dijalankan bila pengembang memutuskannya. Urutan `003600`/`003700` cocok dengan 27 §6.1.
- **Temuan kode:** `dialogue_advanced` tetap benar (daftar berkas yang hanya berubah komentarnya dilengkapi). `audio_pause` tetap benar, tetapi satu celah dicoret: saat narasi lain mulai, `stopAllNarration()` memanggil `pause()` pemutar lain, jadi lama mendengarnya sudah terkirim; yang tersisa hanya pemutaran diulang dan halaman ditinggal. Nomor baris yang meleset dibetulkan; `ExportService::ADMIN_ONLY_SHEETS` dan `Simulator` baris 467 ditambah.
- **Diselaraskan tanpa keputusan baru:** "uji coba" menjadi KG3a dan KG3b; salinan untuk orang tua (putaran kedua, 30 no. 6) disebut sebagai jalur terpisah dari lembar `risiko_gabungan`.

Dua putaran tanya, delapan pertanyaan; pengembang memilih usulan di semuanya ([29 §19](29_IMPLEMENTASI_DATA_PENELITIAN.md#19-keputusan-pengembang) no. 5–12):
1. Penghapusan tetap di migrasi `003600` (K3), tidak dipisah menjadi perintah, karena `003400` sudah menahan server produksi. Tidak dipilih: migrasi hanya menambah kolom, penghapusan lewat perintah spark.
2. Situs statis memakai `kolom.json`; dicocokkan sekali sebelum KG3a bila dibuat di luar repo. Tidak dipilih: situs statis dihapus.
3. Pemeriksaan nama cukup untuk siswa rombel itu, ditambah aturan menulis dan pelatihan. Tidak dipilih: daftar kata tambahan.
4. `is_touch` tetap untuk admin; kembali lewat `kamus-1.1` bila peneliti memintanya. Tidak dipilih: lembar peneliti sejak awal.
5. Batas `empty_tap` tetap 30, angka konfigurasi yang dicek di KG3a dan KG3b. Tidak dipilih: tanpa batas.
6. Tanpa arsip data lama sebelum `003600` di server produksi (belum ada data studi). Tidak dipilih: salinan database dulu.
7. Unduhan selalu terikat satu studi (K16 baru), juga saat mode penelitian mati. Tidak dipilih: hanya dalam mode penelitian.
8. Versi kamus dan spesifikasi lembar dikunci di G4 (K17 baru). Tidak dipilih: kamus boleh naik versi selama KG5.

*Akibat ke dokumen lain* mendapat baris untuk 11 (`kamus-1.1` dikunci di G4), 02 (situs statis, spesifikasi dikunci), 27 (unduhan memerlukan studi), dan 35 (gelombang B sesi 9: data mentah §6, `ResearchDictionary::VERSION`, `field_sheet_rows`, waktu terkoreksi, satu studi), untuk sesi koordinator berikutnya.

Diubah: 29, 00 (dua baris 29), `docs/PROGRESS.md`, PDF siap cetak 00, 04, dan 29.

**27 dicek kemutakhirannya dan disetujui pengembang; empat keputusan baru** (gelombang A, cabang `r10-cek-27`) · `asal: pengembang` (pemeriksaan berbantuan AI dan wawancara singkat) · 27 naik ke `disetujui pengembang` · belum merevisi produk

Dokumen implementasi bagian 1 ([27](27_IMPLEMENTASI_PERAN_PENELITI.md)) dicek terhadap entri 04 sesudah *commit* terakhirnya (putaran kedua 30–31 Oktober 2026 dan semua entri 1 November 2026), dokumen yang dirujuknya, dan kode. Satu putaran tanya, empat pertanyaan; pengembang memilih usulan di keempatnya, lalu menyetujui dokumennya.

1. **CSV daftar siswa untuk admin dihapus** (27 §16 no. 4, K17). Nama hanya tampil di layar admin. Tidak dipilih: CSV admin tanpa nama; CSV admin dengan nama dan setiap unduhan dicatat.
2. **"Hanya ketua yang menyambungkan kode ke nama" tetap pembatasan prosedur** (27 §16 no. 6), seperti di 22 dan 26. Tidak dipilih: halaman siswa admin tanpa kode peserta.
3. **Akun `pengamat` dan `pewawancara` tetap berperan `peneliti`, dibuat hanya bila perlu** (27 §16 no. 7). Tidak dipilih: hanya `pewawancara`; peran keempat yang hanya melihat.
4. **Penjaga di migrasi `003400`** (temuan baru, K16). Skrip pembaruan di `docs/08_DEPLOYMENT.md` menjalankan `php spark migrate` otomatis, jadi tanpa penjaga, memperbarui produksi langsung menghapus akun `guru`. `003400` kini berhenti bila masih ada akun `guru` atau unduhannya, kecuali `.env` memuat `gelita.allowTeacherRemoval = true`; instalasi tanpa akun guru berjalan bebas. `StaffAuthFilter` mengakhiri sesi akun berperan di luar `StaffAccess::ROLES`. Tidak dipilih: prosedur saja.
5. **27 `disetujui pengembang`**, dasar kode gelombang C.

**Yang diselaraskan tanpa keputusan baru:**
- Status dan §0: "kode diubah di tahap 3, setelah G2" diganti aturan 1 November 2026 (bangun sekarang, tidak dipakai dengan anak sebelum persetujuan etik); §0 menambah sakelar studi yang mati secara bawaan, migrasi yang menghapus data, dan instalasi uji pada *commit* beku.
- K5, K12, §8: kode sekolah uji `U01`, …; ketua membuat satu baris `research_studies` per uji.
- §6.1: `003300` masih migrasi terakhir; urutan `003400`→`003800` tetap; bagian 5–9 mulai `003900`.
- §11: instalasi uji validator memakai *commit* beku (kode sebelum bagian 1, cukup akun `admin`); di G3 diperbarui ke Prototipe II, pengembang memegang `admin` dan `ketua`.
- Rujukan kode yang dibetulkan: daftar "20 event terakhir" di ringkasan tidak memakai nama sekolah (hanya "10 siswa terakhir"); jalur `app/Libraries/Simulation/Simulator.php`; `SystemResetService` (baris 288) dan `SchoolController` juga memegang `staff_users.school_id`; `grep guru app/` 120 baris, bukan sekitar 40 komentar; `TelemetryBatchTest` merender `isAdmin`, tanpa akun guru. Rujukan lain (berkas, metode, nomor baris, tes, kunci bahasa, anker) tetap benar; kode belum berubah sejak 30 Oktober 2026.

**Akibat ke dokumen lain** (27 §18, untuk sesi koordinator): penjaga yang sama untuk `003600` di [29](29_IMPLEMENTASI_DATA_PENELITIAN.md); `docs/08_DEPLOYMENT.md` menyebut penjaga `003400` saat implementasi.

Diubah: 27, 00 (dua baris 27), 04, `docs/PROGRESS.md`, PDF siap cetak.

## 1 November 2026

**30 dicek kemutakhirannya dan disetujui pengembang** (gelombang A, sesi 6 dari 6, cabang `r10-cek-30`; pertanyaan dijawab 2 November 2026) · `asal: pengembang` (pemeriksaan berbantuan AI, enam keputusan pengembang) · `disetujui pengembang` · tidak merevisi produk

[30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md) dicek terhadap entri 04 sesudah *commit* terakhirnya (putaran kedua 30–31 Oktober; entri 1 November: ADDIE dengan KG3a/KG3b, versi dikunci di G4, aturan bangun sekarang, cek pasal PP), terhadap 22, 23, 26, 03, 27, 29, dan kode.

- **Diselaraskan tanpa keputusan baru:** kalimat "diubah di tahap 3, setelah gerbang G2" diganti aturan bangun sekarang (gelombang kode E, di balik sakelar studi, tidak dipakai dengan anak sebelum persetujuan etik); tenggat §4 tetap "sebelum akun anak pertama kali dipakai" (KG2, tidak bersyarat) dan ditegaskan tidak bergantung G2; temuan nomor KG di 26 ditandai sudah dibetulkan; §8 memakai PP 33/2026 Pasal 77 ayat (4) huruf a sebagai pasal pembanding (`needs_verification` dicabut); isi pemberitahuan kepada orang tua (Pasal 87) dan konfirmasi lama penyiapan salinan (Pasal 78 ayat (2)) diambil dari 22; uji manual di instalasi uji pengembang, bukan instalasi validator yang dibekukan; `REQUIRED_MIGRATION` `003800` dinaikkan lagi oleh bagian 5–9.
- **Yang dicek dan tetap benar:** kode `app/` tidak berubah sejak 26 Oktober 2026; nomor baris rujukan cocok (satu dipertajam: hitungan retensi disaring di `RetentionService.php` baris 117–121); `HashedIpSessionHandler` ada, `Config\Session` masih `DatabaseHandler` (baris 29); lokasi kedua `backup.sh` tetap tanpa batas (`rsync -a`); migrasi `003800` ada di urutan 27 §6.1; peta kemampuan 27 §5.1 sama dengan 30 §14; teks id dan en T4, T11, T12 di 30 §17 sama dengan 23.
- **Keputusan pengembang** (30 §22 no. 7–12; usulan dipilih kecuali no. 8):
  7. bagian 3 dan 4 dibangun paralel, bagian 4 digabung sesudah bagian 3;
  8. semua isi bagian 4 di balik mode penelitian, termasuk IP berhash (sakelar `gelita.researchPrivacy`) dan batas 30 hari lokasi kedua cadangan (`KEDUA_HARI`); bawaan `retention_days` tetap 1825, 7305 hanya untuk studi `field_mode = 1`, migrasi `003800` tidak mengosongkan `ci_sessions` (usulan AI: perbaikan privasi langsung berlaku di `main`);
  9. lembar `izin` ditambahkan ke unduhan utama `ketua`, unduhan terpisah tetap (30 §21 no. 4);
  10. kritik dan saran diperiksa nama oleh `ketua` sebelum studi ditutup, sebagai syarat penutupan (no. 5);
  11. provinsi dan kabupaten tetap disimpan setelah studi ditutup; komite diminta menilainya (no. 3);
  12. 30 dinaikkan ke `disetujui pengembang`; §21 no. 6 (snapshot cadangan penyedia) tetap terbuka.
- **Akibat ke dokumen lain** (30 §23, baris bertanggal 2 November 2026, untuk sesi koordinator): 29 §7.2 lembar `izin`; 27 §6.1 "berurutan"; 22 dan 26 pertanyaan kabupaten untuk komite; 26 L3/P18 lewat sakelar; 23 kalimat "tahap 3" di T4, T11, T12; `docs/08_DEPLOYMENT.md` saat implementasi.

Diubah: 30, 00 (baris 30 di *Daftar dokumen* dan *Tahap dokumen 3*), `docs/PROGRESS.md` (baris sendiri di gelombang A), PDF siap cetak 30.

**28 dicek kemutakhirannya dan disetujui pengembang** (gelombang A, cabang `r10-cek-28`) · `asal: pengembang` (pemeriksaan berbantuan AI; enam keputusan pengembang) · `disetujui pengembang` · belum merevisi produk

[28](28_IMPLEMENTASI_SESI_LAPANGAN.md) (terakhir diubah 31 Oktober 2026) dicek terhadap entri 04 sesudahnya (putaran kedua 30–31 Oktober 2026; model ADDIE, KG3a/KG3b, satu awalan `U` dengan satu baris `research_studies` per uji, satu anak satu uji, versi dikunci di G4, aturan dibangun sekarang), terhadap 03, 20, dan 27 §6.1, dan terhadap kode (terakhir berubah 26 Oktober 2026). Dua putaran tanya, enam pertanyaan ([28 → Keputusan pengembang](28_IMPLEMENTASI_SESI_LAPANGAN.md#22-keputusan-pengembang) no. 7–12):

1. **`gelita:uji:akun` tetap di gelombang kode D.** Alat ini bisa dibangun sendiri lebih awal, tetapi pengembang memilih membangunnya bersama bagian 2; instalasi uji tahap 2 (*commit* yang dibekukan) tetap memakai cara manual di 20 → Prasyarat no. 8, dan alat ini pertama dipakai di G3. Tidak dipilih: cabang tersendiri sebelum pembekuan (usulan AI).
2. **Rencana pertemuan per studi** (`research_studies.meeting_plan`, 28 K19): `lima_pertemuan` untuk KG3a, KG3b, KG5; `satu_sesi` untuk KG2 (daftar lalu satu wilayah yang dipilih saat sesi lapangan dibuat). Tidak dipilih: KG2 dengan `field_mode` mati; KG2 di instalasi terpisah.
3. **Token menolak akun yang sudah punya sesi game di studi lain** (`other_study`, 28 K20), pelengkap aturan satu anak satu uji. Tidak dipilih: hanya prosedur lapangan.
4. **Satu siswa di dua perangkat** tetap melanjutkan sesi game yang sama (28 §21 no. 6). Tidak dipilih: perangkat terbaru menutup yang lama.
5. **Migrasi `003500` mengisi `participant_progress`** dari pengerjaan tuntas sebelum membuang `session_progress`, jadi tidak termasuk migrasi yang menghapus data. Tidak dipilih: diperlakukan seperti `003400` dan `003600`.
6. **28 naik menjadi `disetujui pengembang`.**

Diselaraskan tanpa keputusan baru: "tahap 3, setelah G2" diganti aturan dibangun sekarang (gelombang D); `field_mode` mati secara bawaan dan satu studi per uji; satu urutan `U` dan penghitung kode yang tidak per studi; rujukan kode dibetulkan (`GameSessionModel::resumable()`, `ParticipantAuthFilter` tidak menolak status `CLOSED`, `keepForLater()` juga membuang antrean pada 422, urutan tantangan sekarang hanya diperiksa di `ChallengeController`, `TryLimitTest` dan `RawDataWorld` ikut membaca `session_progress`); 28 §21 no. 1 selesai (teks token sudah disamakan), no. 2–3 pindah ke bagian 8 (`34`), no. 4 dipegang ketua peneliti. Urutan migrasi 27 §6.1 tetap cocok (kode masih di `003300`). Akibat ke 20, 03, 18, 33, 34, dan 11 dicatat di 28 §23 untuk sesi koordinator.

Diubah: 28, 00 (dua baris 28), `docs/PROGRESS.md`, PDF siap cetak 00, 04, dan 28.

**23 dicek kemutakhirannya dan siap diserahkan ke peneliti sebagai PDF** (cabang `r10-cek-23`, gelombang A sesi 2) · `asal: pengembang` (pemeriksaan berbantuan AI) · status 23 tetap draf · tidak merevisi produk

Atas permintaan pengembang, [23](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md) (terakhir diubah 1 November 2026 pukul 14.12, pembaruan ADDIE) dicek terhadap entri 04 sesudahnya, terhadap 22, 24, 26, 30, 02 → L7, dan 03, dan terhadap kode. Tidak ada keputusan baru dan tidak ada pertanyaan untuk pengembang. Teks untuk orang tua, sekolah, guru, dan anak (T1–T3, T5–T10, T13) tidak berubah; T9 dari 1 November 2026 tetap.

- **T4, T11, dan T12 dengan 30:** T4 di 30 §5.1 dan §17 cocok kata per kata (id dan en). T11 dan T12 dirujuk 30 §5.4 dan §17 "apa adanya" tanpa disalin ulang, dan aturannya sama. Satu usulan kecil untuk 30 dicatat di *Akibat ke dokumen lain* 23: en T11 paragraf 1 "may have made you feel" menjadi "may make you feel", supaya sama dengan id. Teks T4 "sekarang" masih sama dengan `app/Language/id/Game.php` dan `en/Game.php`.
- **Aturan 1 November 2026:** "file bahasa tidak diubah sekarang; masuk tahap 3" di T4, "teks en dibuat di tahap 3" di T11 dan T12, "dibangun di tahap 3" di T10 pilihan C, dan "modul tes, tahap 3" di tabel pembagian diganti: dibangun sekarang di balik sakelar studi, tidak dipakai dengan anak sebelum persetujuan etik, dengan bagian 4 (T4, hapus sebagian) dan bagian 8 (modul tes, T11 dan T12). Ditambahkan: bila teks T4, T11, atau T12 berubah, 23 diubah lebih dulu, baru berkas bahasa.
- **Peneliti menangani izin etik:** "dikirim ke luar hanya setelah permohonan disetujui pengembang" menjadi "hanya sebagai lampiran permohonan, yang ditangani ketua peneliti"; repo tetap salinan induk, perubahan peneliti atau komite masuk dengan `asal: peneliti` atau `asal: komite etik`.
- **Cek pasal PP 33/2026 (entri di bawah):** catatan T10 dan pengantar keputusan no. 16–19 kini menyebut PDF salinan resmi 1 November 2026, bukan `needs_verification`; catatan T10 menambah isi pemberitahuan hapus (jenis data, alasan, tanggal; Pasal 87 ayat (2), (4)–(5)) dan konfirmasi salinan sejak permintaan diterima dan terverifikasi (Pasal 78 ayat (1)–(2)), sama dengan 22. Kalimat untuk orang tua tidak berubah.
- ***Akibat ke dokumen lain*:** keadaan setiap baris dicatat. Sudah diterapkan: 01, 02 (L0, L5, L7), 03, 22, 24. Masih terbuka: 11 (`consent_ok` untuk data tetap di `kamus-1.1`, bagian 9) dan 16 (tautan T11, T12).
- **Yang dicek dan tetap benar:** semua tautan antardokumen di 23 menunjuk judul yang ada; tidak ada kalimat "sistem dibangun setelah G2"; angka peserta (± 360 di data utama) dan KG1–KG3b sama dengan 22 dan 03.

Diubah: 23, 00 (dua baris 23), `docs/PROGRESS.md`, PDF siap cetak 00, 04, dan 23.

**Pembagian kerja baru: peneliti menangani validasi dan izin etik, pengembang menyerahkan berkas awal lalu mengimplementasikan** (cabang `sky/sweet-allen-0md2pw`) · `asal: peneliti` (pembagian kerja, disampaikan lewat pengembang) dan `asal: pengembang` (empat keputusan lanjutan) · `disetujui pengembang` · merevisi rencana kerja, belum merevisi produk

Peneliti meminta dokumen permohonan izin etik ([22](22_PERMOHONAN_IZIN_ETIK.md)) serta surat informasi dan persetujuan ([23](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md)). Semua validasi (validasi ahli, izin etik, uji keterbacaan, uji dengan siswa) ditangani peneliti. Pengembang cukup menyerahkan berkas awal, lalu fokus mengimplementasikan perubahan terbaru ke sistem. Pengembang tetap menyediakan bahan teknis yang diminta peneliti, misalnya isian kelompok B di 22 atau instalasi uji dan akun `uji.*` di [20](20_PAKET_VALIDASI.md). Empat keputusan lanjutan diambil pengembang dalam wawancara singkat:

1. **Semua perubahan sistem dibangun sekarang** (no. 1–24 di [01](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti)), termasuk yang bergantung pada hasil validasi (no. 1–6, 8, 9, 16–22). Pengerjaan ulang setelah hasil validasi kembali diterima. Tidak dipilih: hanya perubahan yang tidak bergantung pada validasi (no. 7, 10–15, 23, 24; usulan AI), atau tetap menunggu G2.
2. **Cek kemutakhiran hanya untuk 22, 23, 27, 28, 29, dan 30.** 22 dan 23 diserahkan sekarang; 27–30 menjadi dasar implementasi. Lima belas dokumen lain yang belum dicek (01, 02, 03, 06, 07, 08, 11, 14–18, 20, 21, 24) adalah bahan validasi dan penelitian. Dokumen itu diperbarui dari hasil validasi, atau saat dokumen implementasi merujuknya. Tidak dipilih: ke-21 dokumen dalam gelombang enam sesi, atau hanya 27–30.
3. **Repo tetap salinan induk** 22, 23, 24, dan 26. Peneliti mengirim perubahan lewat pengembang. Sesi pengembang menerapkannya dengan label `asal: peneliti` (atau `asal: komite etik`), membuat ulang PDF siap cetak, lalu mengirimkannya kembali. Tidak dipilih: repo dibekukan saat diserahkan dan peneliti menyunting di luar repo (usulan AI).
4. **Peneliti menerima PDF saja:** PDF siap cetak 22, 23, 24, dan 26 di `dokumen-pdf/cetak/`, ditambah berkas serah terima v1.4. Tidak dipilih: PDF ditambah Word (usulan AI), atau Word saja.

**Aturan baru untuk pekerjaan sistem.** Aturan ini menggantikan "sistem baru diubah di tahap 3, setelah G2" (urutan tahap yang dikukuhkan 25 Oktober 2026, dan dipertahankan peneliti dalam entri model ADDIE di bawah). Urutan tahap lainnya tetap.
- Kode boleh dibangun sekarang, di cabang pendek dari `main`. Perilaku penelitian ada di balik sakelar studi yang mati secara bawaan (misalnya `field_mode`), jadi `main` tetap bisa dirilis.
- Tidak ada kegiatan dengan anak (KG1–KG5), dan sistem baru tidak dipakai dengan anak, sebelum persetujuan etik terbit. Urutan gerbang tidak berubah: G3 tetap dinilai setelah G2, pada Prototipe II yang sudah memuat revisi dari validasi ahli. Versi tetap dikunci di G4.
- Instalasi uji validator tetap memakai *commit* yang dibekukan di [20](20_PAKET_VALIDASI.md) prasyarat no. 4, bukan `main`. Dengan begitu validator menilai satu versi yang tetap dan tidak melihat label pengembang.
- Migrasi yang menghapus data (`003400` menghapus akun `guru`, `003600` menghapus baris log) baru dijalankan di server produksi bila pengembang memutuskannya.
- Isi (bank soal, cerita, teks) disimpan di berkas data (workbook, `story.php`, berkas bahasa), supaya revisi dari validasi cukup diubah di data lalu diimpor ulang, tanpa mengubah kode. Teks persetujuan T4, T11, dan T12 diambil dari 23 apa adanya. Bila peneliti atau komite etik mengubahnya, 23 diubah lebih dulu, baru berkas bahasa.

**Urutan kerja.** Rinciannya di [00 → Tahap dokumen 3](00_INDEKS.md) dan `docs/PROGRESS.md`.
- **Gelombang A:** enam sesi paralel, satu dokumen per sesi, masing-masing di cabangnya sendiri dari `main`. 22 dan 23 dicek kemutakhirannya lalu diserahkan sebagai PDF. 27–30 dicek kemutakhirannya dan ditinjau pengembang sampai `disetujui pengembang`.
- **Gelombang B,** bersamaan dengan A: lima dokumen implementasi baru untuk perubahan yang bergantung pada validasi. Nomor berkas 31–35; nomor migrasi mulai `003900`, mengikuti urutan digabung.
  - bagian 5: bank soal baru (no. 1, 3, 4, 8, 17, 20);
  - bagian 6: Skenario Digital (no. 2, 6, 18);
  - bagian 7: mode penelitian (no. 5, 16, 19);
  - bagian 8: modul tes dan skala sikap (no. 21, 22);
  - bagian 9: lapis data turunan (no. 9 dan `kamus-1.1`).
- **Kode:** bagian 1 dikerjakan sendirian → bagian 2 ∥ 5 → bagian 3 ∥ 4 ∥ 6 → bagian 7 ∥ 8 → bagian 9 terakhir. Bagian 1 lebih dulu karena semua bagian lain memakai peta kemampuannya ([27 → Model hak akses di kode](27_IMPLEMENTASI_PERAN_PENELITI.md#5-model-hak-akses-di-kode)).

**Kalimat lama yang belum diubah.** Sudah diketahui, dan diubah saat dokumennya disentuh: 11 (*Yang harus disediakan sistem di tahap 3*), pembuka 17 dan 18, kolom *Siap* di 26 (*tahap 3* = "setelah gerbang G2"), dan `gambar/peta-jalan.svg` (tahap 3). Kalimat di 22 dan 27–30 diubah di gelombang A. Baris lama `docs/PROGRESS.md` dibiarkan sebagai riwayat.

Diubah: 04, 01 (*Peta jalan dan daftar kerja*, *Catatan perubahan sistem untuk tahap nanti*), 00 (*Tahap dokumen 2* langkah berikutnya, *Tahap dokumen 3*), `docs/PROGRESS.md`, `.claude/rules/revisi-10.md`, PDF siap cetak 00, 01, dan 04.

**26 diselaraskan dengan keadaan terbaru** (cabang `sky/upbeat-heisenberg-gxyw3p`, setelah PR #304) · `asal: pengembang` (pemeriksaan berbantuan AI) · status 26 tetap `disetujui pengembang` v1.0 · tidak merevisi produk

Atas permintaan pengembang, [26](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md) dicek terhadap keputusan sesudah perubahan terakhirnya (cek pasal PP 33/2026, entri di bawah), terhadap 22, 23, 24, dan 30, dan terhadap kode. Tidak ada keputusan baru; risiko, langkah perlindungan, dan simpulan tidak berubah, jadi versi tetap 1.0.

- **Pasal PP pembanding untuk pengecualian jawaban butir risiko.** *Hak orang tua dan anak* → *Akses dan salinan* masih menulis "pasal sejenis di PP 33/2026 `needs_verification`", padahal cek pasal PP menemukan Pasal 77 ayat (4) huruf a dan 22 sudah memakainya. Kini sama dengan 22: hak dari UU Pasal 7 dan 32; pengecualian dari UU Pasal 33 huruf a dan PP Pasal 77 ayat (4) huruf a sebagai analogi (keduanya hanya menyebut akses perubahan), dinilai komite. Catatan sesi koordinator 30 Oktober 2026 di *Akibat ke dokumen lain* yang menyebut 30 §21 no. 1–2 "tetap terbuka" diberi catatan bahwa keduanya sudah diputuskan di 30 §22 no. 5–6.
- **Pemicu skala besar:** "± 365 anak" (angka sebelum uji coba dipecah) menjadi "paling banyak ± 465 anak di semua kegiatan (± 360 di pengambilan data utama)", sama dengan *Ringkasan untuk komite* dan 22.
- **Sekolah KG1:** "di luar sampel" menjadi "di luar sampel dan bukan sekolah uji coba", sama dengan 22 dan [24](24_UJI_KETERBACAAN.md).
- **Konsultasi kepala sekolah:** "KG2 atau KG3 (sekolah uji coba)" menjadi "KG2, KG3a, atau KG3b".
- **Syarat sebelum permohonan diajukan:** cek nomor pasal ke naskah resmi ditandai selesai 1 November 2026.
- **00:** baris 26 di daftar dokumen masih menyebut nomor pasal `needs_verification`; diganti hasil cek 1 November 2026, dan ukurannya dibetulkan (61 KB).
- **Yang dicek dan tetap benar:** `HashedIpSessionHandler` ada di kode tetapi `Config\Session` masih memakai `DatabaseHandler` (L3, P18 tetap tahap 3); akun staf terkunci 15 menit setelah 5 kali gagal dan ekspor kedaluwarsa setelah 7 hari (P4 "ada"); ringkasan untuk sekolah di 23 → T6 lampiran 5 tetap cocok dengan *Ringkasan untuk komite*; target persetujuan etik "akhir 2027" sama dengan 22 → *Dasar hukum*. Pencarian web 1 November 2026 belum menemukan Peraturan Presiden pembentukan Badan Pelindungan Data Pribadi atau Peraturan Lembaga, jadi kalimat "belum terbit per 27 Oktober 2026" tetap; dicek ulang sebelum KG1 seperti sebelumnya.

Diubah: 26, 00 (dua baris 26), `docs/PROGRESS.md`, PDF siap cetak 00, 04, dan 26.

**13 diselaraskan dengan dokumen implementasi bagian 2–4** (cabang `sky/amazing-cerf-j3jjzk`, setelah PR #302) · `asal: pengembang` (pemeriksaan berbantuan AI) · status 13 tetap draf · tidak merevisi produk

Atas permintaan pengembang, [13](13_TES_AWAL_AKHIR.md) dicek terhadap keputusan sesudah 26 Oktober 2026. Model ADDIE dan KG3a/KG3b sudah masuk 1 November 2026 (PR #299); yang tertinggal datang dari 28–30 (30 Oktober 2026). Tidak ada keputusan baru.

- **Buklet dan bentuk tes dihitung panel** dari baris dan kursi, disimpan di `participant_seats` (`pre_form`, `post_form`, `locked_at`), dengan peringatan keseimbangan yang tidak menahan tes ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md#11-isian-l2-kursi-buklet-dan-bentuk-tes), K10). 13 masih menulis "buklet dimasukkan ke panel" di tabel waktu P1 dan di *Pembagian bentuk*.
- **Butir risiko daring:** layar tanya ulang T11 untuk siswa yang izinnya ada, pesan dukungan T12 untuk semua siswa di titik yang sama, dan `risk_reconsent` ([30 §5.4](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#54-layar-t11-dan-t12)) ditulis di *Modul tes di GELITA* dan tabel waktu P5.
- **Status tes** berasal dari modul; L6 tidak diimpor ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md), K12).
- **Siapa yang masuk analisis:** siswa `Mengundurkan diri (data tetap)` ikut dengan data sampai tanggal mundur mulai `kamus-1.1` (`consent_ok`, 22).
- **Dicatat sebagai hal terbuka untuk dokumen implementasi no. 22:** batas 20 menit susulan di P5 dijaga P1 atau sistem (28 §21 no. 2).
- **Fakta budaya tes:** cek fakta budaya [25](25_CEK_FAKTA_BUDAYA.md) tidak mencakup topik budaya di 14 dan 15; baris validasi menyebutnya masih perlu dicek peneliti.
- ***Penyesuaian di dokumen lain*:** baris 07 tidak lagi menyebut instrumen "belum ditulis"; baris 28, 29, dan 30 ditambahkan. Status ditulis dengan label `draf pengembang`.

Diubah: 13, 00 (baris 13), `docs/PROGRESS.md`, PDF siap cetak 00, 04, dan 13.

**25 diselaraskan dengan keadaan terbaru** (cabang `sky/upbeat-heisenberg-gxyw3p`) · `asal: pengembang` (pemeriksaan berbantuan AI) · status 25 tetap `disetujui pengembang` · tidak merevisi produk

Atas permintaan pengembang, [25](25_CEK_FAKTA_BUDAYA.md) (terakhir diubah 31 Oktober 2026) dicek terhadap keputusan sesudahnya, terhadap dokumen yang dirujuknya, dan terhadap kode. Tidak ada keputusan baru; putusan, sumber, dan hitungan klaim (135; 103 dikonfirmasi, 20 varian, 2 keliru, 10 `needs_verification`) tidak berubah.

- **Akibat no. 5 dan keputusan no. 7:** kedua baris cerita (`kenal-temanggung-03`, `peta-tuntas-01`) sudah tertulis di [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md) sejak entri di bawah; 25 tidak lagi menyebut `peta-tuntas-01` "tidak di 12". Naskah aktif dan `story.php` tetap diubah di tahap 3. Usulan `kenal-temanggung-02` tetap pilihan yang belum dipilih.
- **Nomor baris:** rujukan "12 baris …" dan "IB B.7 (baris …)" tidak cocok lagi (12 diubah 1 November 2026; B.7 ditulis ulang saat cek dibuat), jadi diganti nama bagian. Dua nomor baris 08 yang bergeser dua baris oleh perubahan cek itu sendiri dibetulkan (1277 → 1279, 1285 → 1287). Nomor baris 08, berkas data bank soal, dan naskah aktif lainnya masih cocok, karena berkas itu belum berubah sejak 27 Oktober 2026.
- **Jadwal Pasar Papringan (akibat no. 7):** "sebelum pengambilan data, cek jadwal 2026" menjadi "sebelum versi dikunci di G4, cek jadwal yang berlaku". Ini mengikuti keputusan *versi dikunci di G4* (entri ADDIE di bawah): keterangan `tmg-1-i` menyebut Minggu Wage dan Minggu Pon dan tidak boleh diubah selama pengambilan data utama, yang kini paling cepat 2027.
- **Dicari lagi di web (1 November 2026), tanpa sumber baru yang dikutip:** surat penetapan Warisan Budaya Takbenda Wayang Kedu Temanggung (TMG-31) belum ditemukan, berita yang ada tetap menyebut usulannya "lolos"; jadwal Pasar Papringan 2026 juga belum ditemukan. Kedua baris tetap seperti semula.
- **Yang dicek dan tetap benar:** B.7 IB v2.1 tetap 44 fakta (pembaruan ADDIE 1 November 2026 hanya mengubah pengantar); prasyarat no. 6 di 20 dan "44 fakta" di 21 sudah ditandai; 08, berkas data bank soal, naskah aktif, dan `story.php` belum berubah sejak cek, jadi daftar *Akibat ke dokumen lain* no. 4 masih berlaku utuh untuk tahap 3; model ADDIE dan tiga uji dengan siswa tidak menyentuh isi 25.

Diubah: 25, 00 (baris 25), `docs/PROGRESS.md`, PDF siap cetak 00, 04, dan 25.

**12 diselaraskan dengan keadaan terbaru** (cabang `sky/amazing-cerf-j3jjzk`) · `asal: pengembang` (pemeriksaan berbantuan AI) · status 12 tetap `disetujui pengembang` · tidak merevisi produk

Atas permintaan pengembang, [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md) (terakhir diubah 27 Oktober 2026) dicek terhadap keputusan sesudahnya dan terhadap kode. Tidak ada keputusan baru; yang diubah hanya hal yang sudah diputuskan di dokumen lain.

- **Dua baris dari cek fakta budaya [25](25_CEK_FAKTA_BUDAYA.md#akibat-ke-dokumen-lain) no. 5 dimasukkan**, supaya 12 tetap satu-satunya sumber teks cerita untuk tahap 3: `kenal-temanggung-03` (Gondosuli "diperkirakan dibuat sekitar tahun 832") dan `peta-tuntas-01` ("Dataran Kedu" menjadi "tanah Kedu"). Hitungan naskah: 21 baru, 33 diubah, 58 tetap (dulu 31 dan 60); jumlah 112 baris dan ± 187 rekaman tidak berubah. Usulan opsional 25 untuk `kenal-temanggung-02` dicatat sebagai pilihan yang belum dipilih; barisnya tetap.
- **Urutan P5:** butir risiko daring (dengan izin terpisah) setelah tes akhir dan kritik dan saran setelah skala sikap ditulis di bagian *Penutup* dan keputusan no. 5, sama dengan 02, 06, dan 17.
- **Waktu cerita:** 12 masih memakai patokan lama 50 menit bermain; diganti ± 90 menit per pertemuan wilayah (06, keputusan 26 Oktober 2026). Pengecekan waktu aktif kini di uji keterbacaan, lalu uji kelompok kecil dan menengah (KG3a, KG3b), sesuai model ADDIE.
- **Catatan tahap 3:** no. 1 menyebut "sekali" dihitung per siswa per studi lewat `participant_progress` ([28 §8](28_IMPLEMENTASI_SESI_LAPANGAN.md#8-kemajuan-mengikuti-siswa-bukan-sesi-game)); no. 3 menyebut kedua baris dari 25; no. 7 menunjuk pengganti `dialogue_advanced` yang sudah dirancang, `dialogue_completed` ([29 §5.3](29_IMPLEMENTASI_DATA_PENELITIAN.md#53-dialogue_advanced-diganti-dialogue_completed)).
- **Yang dicek dan tetap benar:** `story.php` dan naskah aktif belum berubah sejak 26 Oktober 2026 (91 baris, hitungan per konteks cocok dengan tabel di 12); narasi belum direkam (`docs/PROGRESS.md`); kode butir, mock-up `mk-cr-01`/`mk-cr-02` di 18, dan rujukan 12 di 20, 22, dan 24 tidak perlu diubah.

Diubah: 12, 00 (baris 12 di daftar dokumen dan tahap 1), `docs/PROGRESS.md`, PDF siap cetak 00, 04, dan 12.

**Target persetujuan etik dipertahankan** (cabang `r10-cek-pasal-pp33`) · `asal: pengembang` · `disetujui pengembang` · tidak merevisi produk

Pengembang memilih tetap memakai target di 01 → *Peta jalan dan daftar kerja* no. 15: persetujuan etik **paling lambat akhir September sampai minggu pertama November 2027**. Patokan panduan BRIN (± 1–2 bulan sejak berkas lengkap, kira-kira Januari–Februari 2027) tetap ditulis di garis waktu 22 sebagai perkiraan, bukan target. Persetujuan yang lebih awal tidak mengubah urutan gerbang: kegiatan dengan anak dan tahap 3 tetap menunggu G2. Tidak dipilih: target Januari–Februari 2027 dengan atau tanpa cadangan sampai Maret 2027 (usulan AI). Berkas serah terima C.3 no. 2 sekarang menyebut keputusan ini dan meminta peneliti mengonfirmasi atau menetapkan target lain.

Diubah: 01 (no. 15 dan pertanyaan terbuka komisi etik), 22 (*Akibat ke dokumen lain*, baris 01), 00, berkas serah terima.

**Nomor pasal PP No. 33 Tahun 2026 dicocokkan dengan PDF salinan resmi** (cabang `r10-cek-pasal-pp33`) · `asal: pengembang` (pemeriksaan berbantuan AI) · status 22 dan 26 tidak berubah · tidak merevisi produk

Butir "cocokkan nomor pasal sekali lagi dengan PDF resmi" di 00 → *Langkah berikutnya di tahap 2* no. 2 dikerjakan, dipilih pengembang dari empat calon langkah. Sumbernya PDF hasil pindai salinan PP No. 33 Tahun 2026 dengan blok "Salinan sesuai dengan aslinya" Kementerian Sekretariat Negara, diunggah laman resmi Pemerintah Kabupaten Banyumas (120 halaman, batang tubuh Pasal 1–225, tanpa Penjelasan; ditetapkan dan diundangkan 16 Juli 2026, LN 2026 No. 88, berlaku enam bulan kemudian menurut Pasal 225). Teksnya dibaca lewat lapisan teks hasil pengenalan karakter dan setiap pasal yang dirujuk dibaca utuh. Semua rujukan pasal PP di 22 dan 26 diperiksa (Pasal 1 angka 4, 18, 19, 21; 6; 7; 13–15; 23–25; 27; 30; 32–33; 35–36; 38; 62; 70–71; 73–78; 82–89; 92; 114–122; 142–149; 154; Bab V, 160–162; 223; 225); rujukan di 23, 30, 02, dan berkas serah terima memakai nomor yang sama. Ini pemeriksaan dokumen, bukan nasihat hukum.

- **Satu nomor salah, dibetulkan di 26:** saran PPDP pada penilaian dampak ada di Pasal 121 **ayat (6)**, bukan ayat (3) (ayat (3) mengatur langkah perlindungan dijalankan selama pengolahan). Dibetulkan di *Mengapa penilaian dampak diperlukan*, *Konsultasi*, dan *Tinjauan ulang* (tinjauan ulang = ayat (5)). 22 sudah memakai ayat (5)–(6) dengan benar.
- **Dipertajam di 22 → *Mengundurkan diri dan hapus data*:** pemberitahuan penghapusan memuat juga alasannya dan dikirim setelah penghapusan untuk permintaan subjek data, paling lambat 3 × 24 jam (Pasal 87 ayat (2), (4)–(5)); konfirmasi lama penyiapan salinan dihitung sejak permohonan diterima **dan terverifikasi** (Pasal 78 ayat (2)).
- **`needs_verification` yang selesai:** pasal pembanding di PP untuk menahan jawaban butir risiko daring dari orang tua yang membahayakan anak ditemukan, yaitu **Pasal 77 ayat (4) huruf a** (wajib menolak akses perubahan yang membahayakan keamanan, kesehatan fisik, atau kesehatan mental subjek data atau orang lain). Seperti UU No. 27 Tahun 2022 Pasal 33 huruf a, pasal ini hanya menyebut akses *perubahan*, jadi pengecualian di 22 tetap analogi yang diminta dinilai komite.
- **Penjelasan Pasal 148 ayat (2)** tidak ada di PDF yang dibaca; batang tubuh Pasal 148 ayat (2) ("hanya dalam rangka pelaksanaan ketentuan Undang-Undang") mendukung kalimat di 22. Isi Penjelasan tetap bersumber dari transkripsi salinan naskah (sisa `needs_verification` yang kecil).
- `needs_verification` untuk nomor pasal PP dicabut di 22 (*Dasar hukum*, pembuka) dan 26 (pembuka, *Dasar hukum*, status). Peraturan Lembaga dan pembentukan lembaga pelindungan data tetap dicek ulang sebelum KG1, seperti sebelumnya.
- **Daftar isian sebelum diajukan (22):** tidak ada lagi isian yang bisa diselesaikan di repo tanpa data pribadi atau fakta dari pengembang dan ketua peneliti. Kelompok B yang dipegang pengembang masih menunggu fakta di luar repo: judul resmi dari usulan penelitian, nama institusi dan lembaga, dana, `[alamat GELITA untuk penelitian]`, `[alat enkripsi diisi tim]`, jenis dan nilai tanda terima kasih, lampiran pernyataan kerahasiaan tim, dan riwayat telaah etik.

Diubah: 22, 26, 01 (no. 15), 00 (baris 22 dan 26, langkah berikutnya no. 2–3), berkas serah terima v1.4 (C.3 no. 2, F.3) dan README `dokumen-pdf/`.

**Contoh pemakaian ADDIE di jurnal Indonesia ditangani peneliti** · `asal: pengembang` (menyampaikan pembagian kerja dengan peneliti) · `disetujui pengembang` · tidak merevisi produk

Pengembang menyampaikan bahwa contoh pemakaian ADDIE di jurnal Indonesia untuk Bab III dicari dan dirujuk langsung oleh peneliti, tanpa pengembang. Dokumen pendukung peneliti tidak perlu menambah daftar contoh itu; catatan "belum dicek di dokumen ini" di bagian *Pembaruan 1 November 2026 → Untuk Bab III* diganti keterangan itu, dan PDF-nya dibuat ulang. Sesi berikutnya tidak perlu mengusulkan pengecekan sumber ini.

**Sisa akibat model ADDIE: 06, 13, 16, dan dokumen pendukung peneliti** (cabang `sky/cool-fermi-rv44sj`, bersama PR #299) · `asal: pengembang` · `disetujui pengembang` · tidak merevisi produk

Atas permintaan pengembang, sisa dokumen yang masih memuat rencana lama diselaraskan. Tidak ada keputusan baru.

- **06:** target 300 siswa yang datanya bisa dianalisis (150 per butir variasi) dikukuhkan peneliti, dari sekitar enam sekolah utama; uji kelompok kecil dan menengah tidak dihitung.
- **13 → Validasi dan uji coba:** langkah 4 menjadi uji kelompok kecil (alur, petunjuk, waktu) lalu uji kelompok menengah dengan kedua bentuk; ± 40 siswa per bentuk, jadi angka butir masih petunjuk revisi, belum kalibrasi (sebelumnya ± 14 siswa per bentuk dari satu kelas).
- **16 → Validasi dan uji coba:** langkah 4 menjadi uji kelompok kecil lalu uji kelompok menengah; patokan butir kosong dan *Tidak tahu* dihitung dari ± 80 siswa uji kelompok menengah.
- **Dokumen pendukung peneliti:** bagian baru *Pembaruan 1 November 2026: model ADDIE* di awal dokumen: keputusan peneliti, apa kata Branch (2009) (hanya klaim yang sudah dicek di bagian 6 dan 11), cara menutup kekurangan ADDIE (ECD, Tessmer, Nieveen & Folmer, empat kriteria mutu, aturan Lynn, Rasch; uji coba dipisah dari data utama), tabel pemetaan peta jalan ke ADDIE yang menggantikan bagian 7, dan catatan untuk Bab III. Bagian 1, 4, 5, dan 7 dipertahankan sebagai catatan analisis 26 Oktober 2026, dan tidak ada bagian yang dinomori ulang, supaya rujukan "§6", "§9" di dokumen lain tetap benar. Contoh pemakaian ADDIE di jurnal Indonesia belum dicek. PDF dibuat ulang dengan Chromium: 14 halaman (sebelumnya 12).
- **Berkas serah terima v1.3:** baris dokumen pendukung peneliti dan baris terakhir A.5 disesuaikan; PDF dibuat ulang.

Diubah: 06, 13, 16, `dokumen-pendukung-peneliti/` (HTML, PDF, README), `dokumen-pdf/src/serah-terima-peneliti.html` dan PDF-nya, 00.

**Berkas serah terima ke peneliti v1.3** (cabang `sky/cool-fermi-rv44sj`, setelah PR #298) · `asal: pengembang` · `draf pengembang` (dokumen internal) · tidak merevisi produk

Pengembang memilih memperbarui berkas serah terima ke versi terbaru, bukan membiarkannya sebagai catatan keadaan sebelum pertemuan. Tidak ada keputusan baru; isinya diselaraskan dengan entri 1 November 2026 di bawah.

- **Sampul dan meta:** per 1 November 2026, versi 1.3, sumber 04 sampai 1 November 2026; kotak status menyebut bahwa model ADDIE dan tiga uji dengan siswa berasal dari peneliti.
- **A.5 baru, *Yang berubah sejak v1.2*:** model ADDIE, tiga uji dengan siswa, data utama dari sekitar enam sekolah, versi dikunci di G4, putaran penilaian ulang tanpa batas, satu anak satu uji, istilah, dan dokumen yang sudah dan belum diperbarui.
- **A.2:** kolom fase menjadi ADDIE (*Analyze* sampai *Evaluate*), tahap 4 menjadi uji coba bertahap, tahap 5–6 menyebut sekitar enam sekolah dan rekomendasi pengembangan.
- **B:** dokumen pendukung peneliti ditandai *perlu diperbarui*; ketiga instrumen ditandai diperbarui 1 November (ahli media 30 halaman); surat putaran 2 juga untuk putaran berikutnya; 22 dan 23 menyebut ADDIE, KG3a/KG3b, dan T9.
- **C:** honorarium mencakup putaran tanpa batas; jadwal KG di kelompok E; kode `U01`, `U02`, … di daftar kode sekolah; jadwal validasi menyebut putaran 3 dan seterusnya; **dua keputusan terbuka baru untuk peneliti**: no. 11 rincian uji coba dari pengembang (anak KG2 terpisah dengan surat sendiri, undian, ± 465 anak) dan no. 12 aturan honorarium per putaran (usulan: satu besaran tetap per putaran yang diikuti).
- **D.2:** baris Plomp diganti baris ADDIE (`asal: peneliti`) dan baris "Plomp tidak dipakai lagi"; prototipe III, aturan Lynn dengan putaran tanpa batas, dan empat kriteria mutu diselaraskan. Rujukan "instrumen D.6" untuk aturan Lynn dibetulkan menjadi C.6, karena instrumen ahli materi tidak punya bagian D.6. **D.6:** baris sampel menyebut sekitar enam sekolah utama.
- **E:** tanya jawab "Kenapa Plomp?" diganti "Kenapa ADDIE?", ditambah tanya jawab baru tentang tiga uji dan alasan uji kecil dan menengah tidak masuk data utama; jawaban aturan Lynn, ukuran sampel, dan "ahli menolak banyak butir" diselaraskan.
- **F.4 dan F.5:** keterbatasan angka butir dari uji yang versinya masih berubah, dan risiko tanggal G2 yang tidak pasti karena putaran tanpa batas.
- **G:** agenda dan lembar catatan keputusan menyebut model ADDIE yang sudah diputuskan, serta baris C.3 no. 11 dan 12.

PDF dibuat ulang dengan `render.mjs`: 36 halaman (v1.2: 32). Diubah: `dokumen-pdf/src/serah-terima-peneliti.html`, `dokumen-pdf/serah-terima-peneliti.pdf`, README `dokumen-pdf/`, 00.

**Instrumen validasi dan paket validasi diperbarui: model ADDIE dan putaran penilaian ulang tanpa batas** (cabang `sky/cool-fermi-rv44sj`, setelah PR #297) · `asal: peneliti` (model) dan `asal: pengembang` (putaran) · `disetujui pengembang` · belum merevisi produk; isi dan nomor butir instrumen tidak berubah

Atas permintaan pengembang, ketiga instrumen validasi diperbarui sebelum paket validasi dikirim. Tidak ada keputusan baru; yang diterapkan keputusan peneliti tentang ADDIE dan keputusan pengembang "putaran penilaian ulang tanpa batas" (keduanya di entri pertama hari ini, di bawah).

1. **Pengantar A.1 ketiga instrumen.** "Model penelitian desain pendidikan Plomp (2013), jenis studi pengembangan" diganti "model penelitian dan pengembangan ADDIE (Branch, 2009)": setiap fase diuji, dan temuan dikembalikan ke fase pengembangan sampai produknya final. Validasi ahli kini disebut telaah ahli di fase pengembangan (Nieveen & Folmer, 2013, tetap dirujuk). Instrumen ahli materi tetap menyebut empat kriteria mutu dengan rujukan Plomp (2013) sebagai sumber kriterianya, sesuai keputusan "empat kriteria tetap"; di instrumen pengamat budaya dan ahli media, Plomp (2013) dihapus dari daftar pustaka karena tidak dirujuk lagi. Branch (2009) ditambahkan di ketiga daftar pustaka. A.2 ahli materi: "uji coba satu kelas" menjadi "uji kelompok kecil dan menengah".
2. **Putaran penilaian ulang.** Di ringkasan cara pakai, A.4, aturan keputusan (C.6 ahli materi, C.5 pengamat budaya dan ahli media), pelaporan, dan D.2, "putaran 2" menjadi "putaran kedua dan berikutnya" atau "putaran berikutnya", dengan kalimat bahwa jumlah putaran tidak dibatasi dan putaran diulang sampai butir memenuhi aturan.
3. **Versi dan catatan perubahan.** Nomor versi tetap (v2.1, v2.1, v1.1), karena paket belum dikirim dan nomor itu dirujuk di 07, 20, dan 21; baris versi di sampul dan tabel D.4 mencatat perubahan 1 November 2026. PDF dibuat ulang: ahli materi 43 halaman, pengamat budaya 34, ahli media 30 (sebelumnya 29).
4. **Paket validasi (20).** Aturan lama "kalau butir masih gagal di putaran 2, butir diganti atau dihapus" diganti: butir direvisi lagi dan dinilai di putaran berikutnya sampai memenuhi aturan; mengganti atau menghapus tetap bisa diputuskan peneliti, tetapi tidak diwajibkan oleh jumlah putaran. Jadwal mendapat baris "putaran 3 dan seterusnya" (dua minggu per putaran, tanggal G2 bergeser), nama berkas `p3`, …, aturan honorarium per putaran perlu ditetapkan, dan surat permohonan butir 1 memberi tahu validator bahwa penilaian ulang bisa lebih dari satu putaran.
5. **Berkas validator (21) tidak dibuat ulang**, karena tabel butir di instrumen tidak berubah; yang diubah hanya teks pengantar, aturan, dan daftar pustaka.

Belum diubah: **berkas serah terima ke peneliti** (v1.2) masih memuat tabel fase Plomp, tanya jawab "Kenapa Plomp?", dan baris lembar keputusan "Model Plomp dan aturan Lynn". Berkas ini tidak dikirim ke validator; apakah diperbarui menjadi v1.3 atau dibiarkan sebagai catatan keadaan sebelum pertemuan diputuskan pengembang.

Diubah: `dokumen-pdf/src/` ketiga HTML instrumen dan PDF-nya, README `dokumen-pdf/`, 20, 00.

**Paket izin etik diperbarui untuk model ADDIE dan tiga uji dengan siswa** (cabang `sky/cool-fermi-rv44sj`, setelah PR #296) · `asal: peneliti` (model, uji, sampel) dan `asal: pengembang` (kode, rincian) · `draf pengembang` · merevisi rancangan penelitian, belum merevisi produk

Pengembang meminta paket izin etik diperbarui sebelum diajukan. Empat keputusan diambil pengembang dalam wawancara singkat; semuanya opsi yang direkomendasikan:

1. **Kode kegiatan: KG3a dan KG3b.** KG3 tetap berarti uji coba di sekolah berawalan `U`; KG3a uji kelompok kecil, KG3b uji kelompok menengah. Urutan waktu tetap dan setiap sebutan KG4 dan KG5 tetap berlaku. Pilihan lain: nomor baru KG1–KG6, atau KG6 untuk uji kelompok kecil.
2. **Uji kelompok kecil memakai lima pertemuan penuh** seperti data utama (P1 tes dan angket, P2–P4 game, P5 tes akhir, skala sikap, penutup), dengan semua lembar, supaya modul tes dan angket sudah dicoba sebelum dipakai 80 siswa. Pilihan lain: game saja dalam tiga pertemuan.
3. **Empat kriteria mutu tetap:** relevansi, konsistensi, kepraktisan, keefektifan, sebagai kriteria mutu produk yang tidak bergantung pada model pengembangan. Pengantar instrumen validasi cukup kehilangan kalimat "model Plomp". Pilihan lain: tiga kriteria Nieveen (valid, praktis, efektif).
4. **"Prinsip desain" menjadi "rekomendasi pengembangan asesmen berbasis game untuk anak"**, keluaran yang wajar dari fase *Evaluate*. Pilihan lain: tetap memakai istilah itu, atau menghapusnya.

Rincian yang mengikuti dari keputusan itu dan dari aturan "satu anak hanya ikut satu uji" (`asal: pengembang`, dikukuhkan bersama peneliti):
- **Anak berpikir lantang (KG2) bukan lagi bagian dari kelas uji coba.** KG2 mendapat surat varian sendiri (23 → T9): satu sesi ± 40 menit, termasuk membuat akun (± 10 menit), tanpa tes, angket, skala sikap, dan butir risiko daring, dengan satu pilihan tambahan (rekaman suara). KG2 selalu menjadi kegiatan pertama yang memakai akun GELITA anak, jadi tenggat "sebelum KG2" di 26 dan 30 tidak lagi bersyarat.
- **Anak KG2 dan KG3a diundi dari anak yang diizinkan**, tanpa melihat nilai; anak yang tidak terundi tetap belajar seperti biasa dan tidak ada datanya. KG3a berlangsung di ruang yang disediakan sekolah, dan anaknya mendapat tanda terima kasih setelah pertemuan terakhir, bukan sebagai imbalan (sama dengan KG1). KG3b mengundang semua siswa kelas 5 di kelas yang ikut, seperti KG5.
- **Wawancara retrospektif (KG4) juga berlaku di KG3a**, selain KG3b dan KG5.
- **Jumlah anak seluruhnya** paling banyak sekitar 465 (KG1 5, KG2 8, KG3a 12, KG3b 80, KG5 360), masing-masing hanya di satu kegiatan (26 → *Ringkasan untuk komite*).

Diubah: 22 (status, pemetaan formulir 2.4.a dan 2.6, *Ringkasan*, nilai sosial, *Tujuan*, *Desain penelitian* dengan tabel fase ADDIE, tabel kegiatan, garis waktu, populasi dan besar sampel, inklusi dan eksklusi, *Rekrutmen* no. 2 dan no. 7 baru, persetujuan, prosedur, *Manfaat*, ringkasan penilaian dampak, *Perubahan protokol*, lampiran, isian jadwal, keputusan no. 25–30, *Akibat ke dokumen lain*); 23 (T9 ditulis ulang menjadi *T9 Varian uji coba*, jumlah sekolah di T1, tabel pembagian, tempat kosong `[tanggal sesi]`, keputusan no. 23–24); 26 (ringkasan, tabel *Kegiatan*, tenggat); 03 (KG3a, KG3b, kode `U01`); 30 §4; 01 (*Model penelitian dan fasenya* ditulis ulang dengan tabel fase ADDIE, daftar kerja no. 17 dan 22); `gambar/peta-jalan.svg` (tahap 4 dan G4); 02 (L5 dan tautan T9); 24 (baris KG1–KG5, rujukan uji coba); 00.

Yang belum: pengantar ketiga instrumen validasi (ahli materi v2.1, pengamat budaya v2.1, ahli media v1.1) dan berkas serah terima masih menyebut Plomp, dan harus diperbarui sebelum paket validasi dikirim; 06, 13, dan 16 masih menyebut uji coba satu kelas; dokumen pendukung peneliti belum punya bagian ADDIE.

**Keputusan peneliti: model ADDIE menggantikan Plomp; tiga uji dengan siswa** (cabang `sky/cool-fermi-rv44sj`) · `asal: peneliti` · `disetujui peneliti` (disampaikan lisan lewat pengembang; dikukuhkan peneliti saat membaca dokumen yang diperbarui) · merevisi rancangan penelitian, belum merevisi produk

Peneliti memutuskan model penelitian dan pengembangan **ADDIE** (*Analyze, Design, Develop, Implement, Evaluate*), menggantikan model Plomp (2013) yang diputuskan pengembang 26 Oktober 2026 (entri *model penelitian dan pengembangan, dan aturan I-CVI*, no. 1). Alasannya, seperti disampaikan pengembang: setiap fase pengembangan diuji, dan perubahan dari saran ahli atau hasil uji masuk kembali ke fase pengembangan sampai produknya benar-benar final. Rujukannya Branch (2009), yang menyebut ADDIE iteratif dan nonlinier dengan uji rintis di fase *Develop*. Dokumen pendukung peneliti sudah mencatat bahwa kritik "ADDIE tidak menjelaskan cara mengevaluasi prototipe" terlalu keras; yang tetap benar hanya bahwa ADDIE tidak punya kriteria mutu khusus alat ukur.

1. **Tiga uji dengan siswa** (rinciannya dijawab pengembang dalam wawancara singkat hari yang sama):
   - **Uji kelompok kecil** (kegiatan baru): beberapa siswa memainkan game yang sudah berjalan di instalasi uji. Tidak masuk data utama.
   - **Uji kelompok menengah**: ± 80 siswa, menggantikan uji coba satu kelas (± 28 siswa, KG3). Tidak masuk data utama.
   - **Uji besar = pengambilan data utama (KG5)**: target **300 siswa yang datanya bisa dianalisis**; jumlah akhirnya boleh lebih atau kurang, dan perkiraan rekrut ± 360 tetap. Sekolahnya **sekitar enam sekolah utama** di wilayah Kedu, boleh lebih. Nama sekolah tidak ditulis di repo; kode dan pemetaan NPSN dipegang ketua peneliti (keputusan 31 Oktober 2026). Ini menggantikan "jumlah sekolah mengikuti ketersediaan kelas" (25 Oktober 2026) sebagai perkiraan, bukan batas.
   - Uji kelompok kecil dan menengah ada di fase *Develop* dan boleh memicu revisi. Sekolahnya di wilayah Kedu, di luar sekolah utama; jumlah sekolahnya bebas.
2. **Yang tetap:** ECD sebagai metode merancang asesmen; aturan I-CVI Lynn (1986), V Aiken, κ, dan model Rasch; tes awal–akhir sebagai bukti validitas kriteria; peta jalan enam tahap dan lima gerbang; aturan "sistem diubah setelah G2". Semuanya metode di dalam fase ADDIE dan tidak bergantung pada Plomp, dan menutup kekurangan ADDIE soal kriteria mutu alat ukur.
3. **Pemetaan ke fase ADDIE** (usulan pengembang, dibakukan saat 01 diperbarui):

   | Fase ADDIE | Tahap GELITA | Isi dan uji |
   | --- | --- | --- |
   | *Analyze* | Penelitian pendahuluan | Analisis kebutuhan dan konteks, L8, telaah butir lama, telaah media pendahuluan |
   | *Design* | Tahap 1 (G1) | Kisi-kisi, draf bank, tes, angket, skala sikap, mock-up |
   | *Develop* | Tahap 2–4 (G2–G4) | Validasi ahli dan uji keterbacaan (KG1) → sistem di instalasi uji dan ahli media langkah 2 (G3) → berpikir lantang (KG2) → uji kelompok kecil → uji kelompok menengah. Setiap uji boleh mengembalikan revisi ke *Develop* |
   | *Implement* | Tahap 5 | Uji besar: pengambilan data utama pada versi yang dikunci |
   | *Evaluate* | Formatif di setiap fase; sumatif di tahap 6 | Analisis dan laporan akhir |

**Empat keputusan lanjutan tentang uji dan validasi** (cabang `sky/cool-fermi-rv44sj`) · `asal: pengembang` · `disetujui pengembang` · belum merevisi produk; dikukuhkan peneliti saat dokumen diperbarui

Wawancara singkat setelah keputusan peneliti di atas. Pengembang memilih opsi yang direkomendasikan untuk no. 1, 2, dan 4, dan opsi lain untuk no. 3.

1. **Kode sekolah uji: satu awalan `U`.** Semua sekolah uji kelompok kecil dan menengah memakai `U01`, `U02`, … dalam satu urutan. Uji mana yang diikuti dicatat lewat studi: satu baris `research_studies` per uji (uji kelompok kecil, uji kelompok menengah, pengambilan data utama). Rancangan di [28](28_IMPLEMENTASI_SESI_LAPANGAN.md) sudah menyimpan sesi lapangan, kursi, dan kemajuan per siswa per studi, dan unduhan selalu terikat satu studi ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md)). `is_pilot` tetap 1 untuk kode berawalan `U` ([11](11_KAMUS_DATA.md#penanda-untuk-analisis)), format kode tetap seperti `S01`, dan satu sekolah boleh menjadi tempat kedua uji. Pilihan lain: nomor uji di dalam kode (`U11`, `U12`, … untuk uji kecil; `U21`, `U22`, … untuk uji menengah), yang membatasi sembilan sekolah per uji dan memberi dua kode untuk sekolah yang ikut keduanya.
2. **Versi dikunci di G4.** Pengambilan data utama berjalan pada satu versi yang dikunci. Selama uji besar hanya perbaikan kerusakan teknis yang boleh, dan setiap perbaikan dicatat; butir dan isi tidak diubah. Temuan selama uji besar masuk laporan sebagai revisi akhir atau rekomendasi, dan datanya tidak diambil ulang. Ini sejalan dengan pra-registrasi OSF di G4. Pilihan lain: uji besar juga boleh kembali ke *Develop*, sehingga data utama bercampur versi dan kalibrasi Rasch (150 siswa per butir variasi) melemah.
3. **Putaran penilaian ulang ahli: tanpa batas.** Butir direvisi dan dinilai ulang sampai memenuhi aturan Lynn (dengan lima ahli atau kurang, I-CVI = 1,00). Akibatnya tanggal G2 bergantung pada jumlah putaran, dan honorarium setiap putaran mengikuti ketentuan yang berlaku. Pilihan lain: paling banyak dua putaran (direkomendasikan) atau tiga putaran, lalu butir dibuang atau diganti butir cadangan.
4. **Uji kelompok kecil 8–12 anak, setelah berpikir lantang.** Berpikir lantang (KG2, 5–8 anak, satu per satu) tetap kegiatan tersendiri. Sesudahnya uji kelompok kecil dengan 8–12 anak dari satu sekolah, lalu uji kelompok menengah ± 80 siswa: urutan satu per satu → kelompok kecil → lapangan (Tessmer, 1993). Anak yang sudah ikut satu uji tidak ikut uji berikutnya atau pengambilan data utama. Pilihan lain: 5–8 anak dengan berpikir lantang di dalam uji kecil, atau jumlahnya ditetapkan peneliti nanti.

**Akibat ke dokumen lain** (dikerjakan di sesi berikutnya, satu dokumen atau satu paket per sesi):

1. **Paket izin etik, sebelum diajukan minggu kedua–keempat November 2026:** 22 (desain, tujuan, tabel kegiatan, garis waktu, ringkasan), 23 (varian surat uji coba T9, dan varian uji kelompok kecil bila perlu), 26 (tabel kegiatan dan jumlah anak), 03 dan 30 (tabel KG), 01 (*Model penelitian dan fasenya*, tabel peta jalan, daftar kerja no. 17 dan 22). Kode KG untuk uji kelompok kecil ditetapkan di sesi itu.
2. **Sebelum paket validasi dikirim:** pengantar ketiga instrumen (ahli materi v2.1, pengamat budaya v2.1, ahli media v1.1) menyebut model Plomp dan kriteria mutunya; berkas serah terima ke peneliti juga.
3. **Sesudahnya:** 02 (L8), 06 (sampel), 13 dan 16 (uji coba satu kelas), 24 (sekolah uji keterbacaan di luar sekolah uji), dokumen pendukung peneliti (bagian ADDIE menurut Branch, 2009), lalu PDF siap cetak. Dua hal diputuskan saat 01 diperbarui: kriteria mutu (tiga kriteria Nieveen atau empat kriteria Plomp) dan prinsip desain, yang bukan keluaran ADDIE.

Diubah: 04, 01 (penanda di *Model penelitian dan fasenya*, daftar kerja no. 17, pertanyaan sampel, pertanyaan baru tentang model), 00 (*Langkah berikutnya di tahap 2*, baris dokumen pendukung peneliti), `docs/PROGRESS.md`.

## 31 Oktober 2026

**Versi PDF siap cetak untuk setiap dokumen** (cabang `sky/elegant-galileo-kok1fx`) · `asal: pengembang` · `disetujui pengembang` · tidak merevisi produk

Pengembang meminta versi PDF siap cetak dari setiap dokumen dan memilih menyimpannya di repo (pilihan lain: hanya dibuat saat dibutuhkan). Skrip `dokumen-pdf/src/cetak.mjs` mengubah setiap dokumen bernomor 00–30 menjadi PDF A4 di `dokumen-pdf/cetak/` lewat pandoc dan Chromium, dengan gaya cetak instrumen (`_instrumen.css`) ditambah `_cetak.css`. Kaki halaman menyebut *commit* terakhir yang mengubah dokumen itu beserta tanggalnya, jadi dokumen dengan perubahan yang belum di-*commit* tidak dibuat. Tabel yang tidak muat di halaman tegak pindah ke halaman mendatar (per 31 Oktober 2026 hanya satu tabel di 14 dan satu di 20). `cetak/manifest.json` mencatat sumber, *commit*, dan jumlah halaman setiap PDF, sehingga yang tidak berubah tidak dibuat ulang. Hasil pertama: 31 PDF, sekitar 600 halaman, sekitar 14 MB. Aturan sesi (`.claude/rules/revisi-10.md`) kini meminta skrip dijalankan setelah perubahan dokumen di-*commit*, lalu PDF-nya ikut di-*commit*.

Diubah: `dokumen-pdf/` (skrip, gaya, README, folder `cetak/`), 00 (petunjuk *Mulai sesi baru di sini* dan baris `dokumen-pdf/`), aturan sesi.

**Tiga ketidaksesuaian lagi dibetulkan; tanda alasan yang disimpulkan diganti ◆** (cabang `sky/elegant-galileo-kok1fx`) · `asal: pengembang` · `disetujui pengembang` · tidak merevisi produk

Saat memeriksa sisa pekerjaan pengembang sebelum bertemu peneliti, ditemukan tiga ketidaksesuaian tentang siapa memegang pekerjaan. Dua diputuskan pengembang dalam wawancara singkat (keduanya opsi yang direkomendasikan); yang ketiga cukup disamakan dengan 22.

1. **Daftar kode sekolah (daftar kerja no. 12).** Keputusan 25 Oktober 2026 ("kode sekolah dari daftar peneliti"), [01 → Kode peserta baru](01_REVISI_10_ANALITIK.md#kode-peserta-baru), 03, 28, dan berkas serah terima menyebut daftar dibuat peneliti, sedangkan 01 no. 12 dan [22 → Daftar isian sebelum diajukan](22_PERMOHONAN_IZIN_ETIK.md#daftar-isian-sebelum-diajukan) kelompok D menyebut pengembang. **Diputuskan:** ketua peneliti menyusun daftar untuk sekolah yang direkrut, menyimpan pemetaan NPSN bersama L7 di luar repo, dan mengisi wilayah sekolah sasaran di formulir BRIN 2.5; admin memasukkan kodenya ke `schools.research_code`. Pilihan lain: pengembang menyusun daftar. 01 no. 12 dan 22 kelompok D disamakan, dan tempat kosong `[wilayah sekolah sasaran diisi pengembang]` di 22 menjadi `[wilayah sekolah sasaran]`. Penundaan 27 Oktober 2026 tetap berlaku.
2. **Pemegang akun staf (daftar kerja no. 13).** 01 no. 13 menyebut pengembang menetapkan semua akun, sedangkan berkas serah terima menyerahkan akun peneliti ke peneliti. **Diputuskan:** dibagi. Pengembang menetapkan pemegang `admin`; ketua peneliti menetapkan pemegang `koordinator`, `pengamat`, `pewawancara`, dan `ketua`. Semua nama dicatat ketua di luar repo, seperti keputusan 27 Oktober 2026. Pilihan lain: pengembang menetapkan semua, atau ketua menetapkan semua. 01 no. 13 dan [03 → Akun staf](03_KODE_DAN_ARTI.md#akun-staf) disamakan.
3. **Narahubung data.** 22 kelompok A menyebut pengembang menanyakan ke institusi apakah PPDP sudah ditunjuk, lalu ketua peneliti mengisi `[kontak PPDP atau narahubung]`; berkas serah terima menyerahkan seluruhnya ke peneliti. Berkas disamakan dengan 22; tidak ada keputusan baru.

Atas permintaan pengembang, tanda untuk alasan yang disimpulkan di berkas serah terima diganti dari † menjadi ◆. Entri berkas serah terima di bawah tetap menyebut tanda lama, karena catatan riwayat.

Berkas serah terima dibuat ulang (versi 1.2, 32 halaman): C.2 (narahubung data, kelompok D, pemegang akun staf, daftar kode sekolah) dan F.2 no. 8–10. Diubah: 00, 01, 03, 22, `dokumen-pdf/serah-terima-peneliti.pdf` dan sumbernya, README `dokumen-pdf/`.

**Tujuh ketidaksesuaian dibetulkan; 25 dan 26 disetujui pengembang** (cabang `sky/elegant-brown-3vnbv9`) · `asal: pengembang` · `disetujui pengembang` · tidak merevisi produk

Atas permintaan pengembang, ketujuh ketidaksesuaian di entri berkas serah terima (di bawah) dibetulkan di dokumen sumbernya. Satu keputusan diambil pengembang dalam wawancara singkat: 25 dan 26 dinaikkan menjadi `disetujui pengembang` (opsi yang direkomendasikan; pilihan lain menyetujui 25 saja atau membiarkan keduanya draf).

1. **Siapa mengajukan izin etik.** Kalimat pembuka [22](22_PERMOHONAN_IZIN_ETIK.md) kini sama dengan tabel langkah: ketua peneliti mengajukan dari akunnya di sistem klirens etik BRIN, didampingi pembimbing; pengembang menyiapkan isian dan lampiran. Baris pembimbing di *Identitas dan tim* dan keputusan no. 13 diberi catatan: pembimbing mengirim sendiri hanya bila komite memakai format PSEPPKN.
2. **Siapa mengisi data ketua.** [23 → Tempat kosong yang dipakai](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#tempat-kosong-yang-dipakai) disamakan dengan 22 → *Daftar isian*: data pribadi dan kontak penelitian diisi ketua peneliti (kelompok A); lembaga, alamat, dan sumber dana oleh pengembang bersama ketua peneliti (kelompok B).
3. **Judul.** 22 → *Identitas dan tim* memakai judul penelitian di 01 (dari peneliti), menggantikan draf judul pengembang; tetap `needs_verification` sampai dicocokkan dengan usulan penelitian resmi.
4. **Server di 26.** `[nama dan lokasi penyedia server]` di *Identitas penilaian* dan diagram aliran data diisi IDCloudHost, Jakarta Pusat (22 → no. 23).
5. **Status 25 dan 26.** Keduanya `disetujui pengembang` 31 Oktober 2026; 26 menjadi versi 1.0 dan baris pengembang di *Persetujuan dan tanda tangan* diisi. Saran PPDP dan penetapan ketua peneliti atas 26 tetap menyusul. Baris 25 dan 26 di 00 dan baris no. 15 di 01 diperbarui.
6. **Baris lama di 30 §23.** Baris untuk 22 dan 23 → T10 D memuat aturan putaran kedua (anak ditanya dulu; jawaban butir risiko diberikan bila orang tua tetap meminta, kecuali membahayakan anak). Isi 22 dan 23 sendiri sudah benar; yang diperbarui hanya baris catatan di 30.
7. **`gelita:uji:akun`.** Baris perubahan no. 24 di 01 mengikuti keputusan 28 → K15 (di luar alur token, sekolah `V01`, kode `UJI-…`); alasan lama "mengikuti alur pendaftaran baru" diganti. 03 dan 20 sudah sesuai. Entri bagian 1–4 tanggal 30 Oktober 2026 di bawah tidak diubah, karena catatan riwayat.

Berkas serah terima dibuat ulang (versi 1.1): F.2 kini mencatat ketujuh temuan sebagai sudah dibetulkan, dan status 25 dan 26 di B.3 dan B.4, judul di C.2, serta C.3 no. 1 disesuaikan. Diubah: 00, 01, 22, 23, 25, 26, 30, `dokumen-pdf/serah-terima-peneliti.pdf` dan sumbernya.

**Berkas serah terima ke peneliti (PDF)** (cabang `sky/elegant-brown-3vnbv9`) · `asal: pengembang` · `draf pengembang` · tidak merevisi produk

Atas permintaan pengembang, disusun [dokumen-pdf/serah-terima-peneliti.pdf](dokumen-pdf/serah-terima-peneliti.pdf) (32 halaman, sumber `dokumen-pdf/src/serah-terima-peneliti.html`, dibuat dengan `render.mjs`) sebagai pegangan pengembang saat bertemu peneliti. Isinya diringkas dari 00–30, 04 (23–31 Oktober 2026), dan dokumen pendukung peneliti; tidak ada keputusan baru. Bagian-bagiannya: posisi pekerjaan, daftar periksa enam paket dokumen, yang tidak diserahkan ke ahli, isian dan tanda tangan yang hanya bisa diberikan peneliti, sepuluh keputusan terbuka untuk peneliti, keputusan yang sudah diterapkan beserta alasannya (D.1–D.10), 19 tanya jawab, keterbatasan, agenda, dan lembar catatan keputusan. Berkas ini dokumen internal, tidak dikirim ke ahli atau komite.

- **Alasan bertanda †.** Banyak keputusan di 04 tercatat tanpa alasan (misalnya sampel 300/360, tiga peneliti per kelas, variasi a/b, model *between-item*, pra-registrasi OSF, akun `guru` dihapus). Alasan untuk keputusan seperti itu disimpulkan saat menyusun berkas dan ditandai †; pengembang dan peneliti mengukuhkannya atau memberi rujukan sebelum dipakai di depan validator, komite, atau penguji (berkas → F.1).
- **Ketidaksesuaian yang ditemukan** (berkas → F.2; semuanya dibetulkan hari yang sama, lihat entri di atas):
  1. Kalimat pembuka [22](22_PERMOHONAN_IZIN_ETIK.md) menyebut pengembang yang mengajukan permohonan, sedangkan tabel *Pengajuan lewat sistem klirens etik BRIN* memberikan langkah 1–8 kepada ketua peneliti didampingi pembimbing.
  2. [23 → Tempat kosong yang dipakai](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#tempat-kosong-yang-dipakai) menyebut data ketua diisi pengembang, sedangkan 22 kelompok A menyebut ketua peneliti.
  3. Draf judul di 22 → *Identitas dan tim* berbeda dengan judul penelitian di 01, 20, 21, dan 24.
  4. [26](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md) masih memuat `[nama dan lokasi penyedia server]` di *Identitas penilaian* dan diagram aliran data, padahal 22 sudah mengisinya (IDCloudHost, Jakarta Pusat).
  5. 00 menyebut 26 `disetujui pengembang`, sedangkan kepala 26 masih `draf pengembang` v0.1.
  6. Baris penyelarasan putaran pertama di 30 §23 untuk 22 dan 23 → T10 D masih memuat aturan lama soal jawaban butir risiko; perlu satu cek konsistensi.
  7. 28 → keputusan no. 2 (`gelita:uji:akun` di luar alur token) bertentangan dengan alasan memberi nomor perubahan no. 24 ("harus mengikuti alur pendaftaran baru").

Diubah: `dokumen-pdf/` (PDF dan sumber HTML baru, README), 00 (baris `dokumen-pdf/` dan petunjuk di *Mulai sesi baru di sini*). Langkah berikutnya: pengembang membaca berkas, lalu bertemu peneliti; keputusan peneliti dicatat di 04 dengan `asal: peneliti`.

**28 §21 no. 7 dan 8: jawaban putaran kedua berlaku** (cabang `pbb/pensive-ramanujan-d8nu9h`) · `asal: pengembang` · `disetujui pengembang` · Prototipe I, belum merevisi produk

Konflik yang dicatat di entri putaran kedua (di bawah) diputuskan pengembang: jawaban putaran kedua yang berlaku, dan catatan PR #288 diganti.
- **No. 7:** angka "izin belum diisi" di panel pemantauan hanya untuk peneliti dan ketua; admin tidak melihatnya ([28 → Panel pemantauan](28_IMPLEMENTASI_SESI_LAPANGAN.md#12-panel-pemantauan), [30 → Izin berkode per siswa](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#52-izin-berkode-per-siswa)).
- **No. 8:** di luar mode penelitian, `Game.forgotPasswordAdmin` berbunyi "Lupa kata sandi? Bilang ke gurumu, ya. Gurumu bisa meminta admin GELITA mengatur ulang." (en: "Forgot your password? Tell your teacher. Your teacher can ask the GELITA admin to reset it."). Selama mode penelitian tetap "Minta kakak peneliti mengatur ulang, ya." Nama kunci `…Admin` dari #288 dipertahankan, begitu juga `Auth.mustChangeAdmin` dan `Auth.currentHelpAdmin`, karena yang mengatur ulang di luar studi memang admin ([27 → Teks antarmuka](27_IMPLEMENTASI_PERAN_PENELITI.md#12-teks-antarmuka)).

Diubah: 27 §12, 28 §12, §21 no. 7–8, dan §22 no. 5–6, 30 §5.2. Tidak ada butir sesi koordinator yang tersisa; langkah berikutnya pengembang meninjau 27–30.

## 30 Oktober 2026

**Putaran kedua setelah sesi koordinator: empat pertanyaan terbuka diputuskan, teks token disamakan, janji kerahasiaan dibetulkan** (cabang `pbb/pensive-ramanujan-d8nu9h`) · `asal: pengembang` · `draf pengembang` (27–30) dan `disetujui pengembang` (perbaikan teks di 16, 18, 22, 23, 26, instrumen ahli media) · Prototipe I, revisi atas permintaan pengembang

Pengembang meminta keempat butir "Untuk pengembang" di entri sesi koordinator (di bawah) diperiksa ulang dan dibetulkan. Satu putaran tanya, empat pertanyaan; pengembang memilih usulan di keempatnya.

1–2. **28 §21 no. 7 dan 8: jawaban putaran ini tidak diterapkan, karena bertentangan dengan keputusan yang sudah digabung.** Di putaran ini pengembang memilih: angka "izin belum diisi" hanya untuk peneliti dan ketua; kalimat lupa kata sandi di luar mode penelitian "Bilang ke gurumu, ya. Gurumu bisa meminta admin GELITA mengatur ulang." Tetapi pengembang sudah memutuskan kedua hal itu lebih dulu, dalam pesan terpisah ke sesi A (PR #288, sudah di `main`): **admin juga melihat angka itu**, dan **kalimat di luar mode penelitian mengarah ke admin GELITA** (kunci `…Admin` di [27 → Teks antarmuka](27_IMPLEMENTASI_PERAN_PENELITI.md#12-teks-antarmuka)). Yang tertulis di 27, 28, dan 30 tetap keputusan #288. **Menunggu konfirmasi pengembang** mana yang berlaku; bila jawaban putaran ini yang dimaksud, 27, 28 §12 dan §21–22, dan 30 §5.2 diubah di cabang berikutnya.
3. **Transkrip ikut dihapus pada pilihan C rekaman** ([30 → Keputusan pengembang](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#22-keputusan-pengembang) no. 5); catatan tertulis P3 di L5 tetap. Formulir T10 kini menulis "rekaman yang sudah ada dan salinan tertulisnya dihapus". Tidak dipilih: transkrip berkode tetap disimpan; centang tambahan di T10.
4. **Jawaban butir risiko daring di salinan orang tua: anak ditanya dulu, diberikan bila orang tua tetap meminta** (30 no. 6), kecuali pemberiannya membahayakan anak. Ini mengubah keputusan 30 no. 2 ("ditahan kecuali anak setuju"). Alasannya hasil cek naskah resmi UU No. 27 Tahun 2022 (anotasi Mahkamah Konstitusi 2025): hak akses dan salinan ada di Pasal 7 dan 32, pengecualian penelitian di Pasal 15 tidak mencakupnya, dan Pasal 33 hanya mewajibkan menolak akses *perubahan* data yang membahayakan subjek data. Pasal 33 huruf a dipakai sebagai analogi untuk pengecualian keselamatan, dan komite diminta menilainya. Naskah PP 33/2026 belum bisa dibuka, jadi pasal sejenis di sana tetap `needs_verification`. Tidak dipilih: tetap ditahan dan diserahkan ke komite; selalu disertakan.

**Akibat keputusan no. 4: janji kerahasiaan dibuat jujur.** Kalimat "orang tua tidak melihat jawaban" dibetulkan di semua tempat, karena orang tua berhak meminta data anaknya:
- T11 paragraf 3: "Guru dan temanmu tidak melihat jawabanmu. Kalau orang tuamu meminta melihat jawabanmu, kakak peneliti akan bicara denganmu dulu." (en di 30 §17);
- T1 bagian 3 (surat orang tua) dan T3 (naskah anak di P1);
- 22 daftar tilik 6c, 16 *Etika* no. 2, 23 *Pilihan yang diambil* no. 13;
- 22 *Mengundurkan diri dan hapus data* dan *Dasar hukum*, 23 T10 pilihan D, 26 *Hak orang tua dan anak* dan P8.

**Teks token disamakan dengan 28** ("6 huruf dan angka", tanpa O, 0, I, 1, L):
- gambar mock-up `mk-tk-01` dan `mk-tk-02` digambar ulang dari sumbernya; 18 mencatatnya. Catatan lama di 18 bahwa gambar itu "sudah dikirim" dibetulkan: paket ahli media belum dikirim;
- instrumen ahli media v1.1: empat tempat di sumber HTML dibetulkan dan PDF dibuat ulang (tetap 29 halaman); berkas validator dibuat ulang, 69 pemeriksaan lolos, versi sumber `9f56ac8f`.

**Kalimat L7 di 02** dibaca ulang dan sudah benar: L7 bernama tidak pernah diunggah, dan yang di server hanya kolom berkodenya. Satu kalimat dipertegas: daftar izin berkode tidak punya kolom rombel; huruf rombel hanya ada di dalam kode peserta.

Langkah berikutnya: pengembang mengonfirmasi 28 §21 no. 7 dan 8 (keputusan #288 atau jawaban putaran ini), lalu meninjau 27–30.

**Sesi koordinator bagian 1–4: bagian diselaraskan, akibatnya dibawa ke 01, 02, 03, 18, 20, 22, 23, dan 26** (tiga sesi paralel, PR #285, #286, #287; penutupan di cabang `pbb/pensive-ramanujan-d8nu9h`) · `asal: pengembang` · `draf pengembang` · Prototipe I, belum merevisi produk

Atas permintaan pengembang, sesi koordinator yang direncanakan di entri keputusan empat bagian (di bawah, keputusan no. 2) dijalankan dalam tiga sesi paralel, dibagi per pemilik berkas. Berkas bersama (00, 04, `docs/PROGRESS.md`) dipegang sesi yang memulainya. Tidak ada keputusan baru; semuanya menjalankan keputusan di 27–30.

| Sesi | Berkas | PR | Yang dikerjakan |
| --- | --- | --- | --- |
| A | 27, 28, 29, 30 | #287 | Akibat antarbagian dibawa. Urutan migrasi ditulis sekali di 27 §6.1 (`003400` → `003500` → `003600`, `003700` → `003800`; `REQUIRED_MIGRATION` akhirnya `003800`). Peta kemampuan 27 §5.1 memuat semua kemampuan bagian 2–4, termasuk `deletion.confirm` dan `identities.replace`. Tabel rute tanpa jalur ganda. Kunci asing ke `participants` memakai `ON DELETE CASCADE` di 28 dan 29. Pertanyaan terbuka yang sudah dijawab bagian lain dicoret dengan rujukan (27 §16 no. 2 dan 5, 28 §21 no. 5) |
| B | 22, 23, 26 | #286 | Akibat 27, 29, dan 30 dibawa. Tabel *Kegiatan* di 26 memakai nomor KG dari 03 dan 22, dan setiap "KG2"–"KG5" di 26 diperiksa ulang. 26 → A7 dibetulkan: penanda tindak lanjut tidak disimpan (30 K7). 30 §21 no. 1 ditandai `needs_verification` dan no. 2 "belum diputuskan" di ketiga dokumen |
| C | 01, 02, 03, 18, 20 | #285 | Akibat 27–30 dibawa. 03 mendapat bagian *Event yang berubah di pembersihan log* dan *Persetujuan dan hak atas data*. Baris untuk 11 tetap menunggu `kamus-1.1` |

Penutupan di cabang ini: status setiap baris *Akibat ke dokumen lain* di 27–30 yang sudah dibawa B dan C diubah menjadi "Sudah"; yang tersisa hanya baris untuk 11 (`kamus-1.1`) dan `.claude/rules/glossary.md` (saat implementasi). 00 dan `docs/PROGRESS.md` diperbarui.

**Untuk pengembang** (dari laporan ketiga sesi; tidak menahan apa pun):
1. [28 → Pertanyaan terbuka](28_IMPLEMENTASI_SESI_LAPANGAN.md#21-pertanyaan-terbuka) no. 7: apakah admin melihat angka "izin belum diisi" di panel pemantauan; no. 8: teks "lupa kata sandi" setelah studi ditutup.
2. Gambar mock-up `gambar/mockup/mk-tk-01.webp` (sudah disetujui) masih memakai petunjuk token lama ("Ketik enam huruf token…"); 18 mencatatnya. Sumber HTML dan PDF instrumen ahli media v1.1 masih menulis token "6 huruf (tanpa O, 0, I, 1)", padahal token kini huruf dan angka tanpa O, 0, I, 1, L. Keduanya belum dibuat ulang; perlu diputuskan sebelum paket ahli media dikirim.
3. Kalimat L7 di 02: sesi C menulis "L7 bernama tidak pernah diunggah; hanya izin berkode yang ada di server", untuk menyatukan 29 (L7 tidak pernah di server) dan 30 (kolom berkode L7 di panel). Perlu dibaca pengembang.
4. Angka 7305 hari di 22 berasal dari 30 K10, yang masih temuan kode untuk ditinjau; 22 menyebutnya.

Langkah berikutnya: pengembang meninjau 27–30 dan butir di atas. Setelah itu, dokumen implementasi bagian 1–4 boleh dinaikkan ke `disetujui pengembang`.

**Bagian 4 dokumen implementasi didraf: persetujuan, hak atas data, masa simpan, dan IP berhash** (cabang `pbb/pensive-ramanujan-d8nu9h`) · `asal: pengembang` · `draf pengembang` · Prototipe I, belum merevisi produk

[30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md) ditulis untuk perubahan sistem no. 23. Isinya:
- teks centang T4 untuk mode penelitian dengan `assent_version`;
- izin berkode per siswa dari L7, status mundur yang langsung menonaktifkan akun, dan aturan tampil T11/T12 (layarnya tetap di no. 22);
- penanda tindak lanjut butir risiko untuk `ketua`, dihitung saat dibuka dan tidak disimpan;
- permintaan hapus (semua, jawaban butir risiko, rekaman) dengan konfirmasi ketua, unduhan di server, salinan peneliti, penggantian cadangan, daftar periksa, batas 3 × 24 jam di panel, dan berita acara;
- salinan satu anak, pembetulan umur, jenis kelamin, dan kelas;
- penutupan studi: daftar izin berkode, lalu nama, nama pengguna, dan sekolah diganti kode;
- masa simpan 7305 hari sejak studi ditutup, `HashedIpSessionHandler`, pembersihan `ci_sessions`, dan jalan manual sementara (26 → P15);
- skema (`003800`), rute, teks id dan en, tes, dan titik sambung ke no. 21, 22, dan bagian 2–3.

Tidak ada kode yang diubah.

**Tenggat "sebelum KG2" diselesaikan.** Temuan untuk bagian 4 di entri keputusan empat bagian (di bawah) benar: tabel *Kegiatan* di 26 memberi nomor KG2–KG5 yang berbeda dari 03 dan 22. Bagian 4 menulis tenggatnya sebagai "sebelum kegiatan pertama yang memakai akun GELITA anak", yaitu KG2 atau KG3 menurut 03 dan 22 ([30 → Tenggat](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#4-tenggat-sebelum-akun-gelita-anak-pertama-kali-dipakai)). Tabel 26 dibetulkan sesi koordinator; "sebelum KG2" di 01, 22, dan 26 tetap benar sesudahnya.

**Temuan kode, untuk ditinjau pengembang.**
1. **Cadangan di lokasi kedua tidak pernah dihapus.** Skrip cadangan hanya menambah berkas di lokasi kedua, jadi data yang dihapus atas permintaan orang tua, dan nama setelah studi ditutup, tetap ada di sana tanpa batas. Keputusan no. 3 di bawah menutupnya.
2. **Retensi menghitung dari mulai sesi, bukan dari studi ditutup** (`RetentionService::flagExpiredStudyData()`), dan 7300 hari kurang lima hari dari 20 tahun. Bagian 4 memakai 7305 hari sejak `closed_at` untuk studi `field_mode`.
3. **Sekolah tetap tersambung ke nama dan NPSN** lewat `school_id` setelah nama anak diganti kode. Bagian 4 memindahkan rujukan ke baris sekolah berkode saat studi ditutup.
4. **Akun langsung nonaktif saat P1 menyimpan status mundur**, bukan menunggu admin seperti tertulis di 22 dan 23 → T10, supaya batas 3 × 24 jam selalu terpenuhi.
5. **Pembersih sesi bawaan PHP tidak berjalan di PHP paket Debian/Ubuntu** (`session.gc_probability = 0`), jadi `gelita:retention:run` menghapus baris `ci_sessions` yang kedaluwarsa.

Satu putaran tanya, empat pertanyaan; pengembang memilih usulan di keempatnya ([30 → Keputusan pengembang](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#22-keputusan-pengembang)):
1. **Permintaan hapus dari `peneliti` dikonfirmasi `ketua` lebih dulu**; permintaan dari `ketua` langsung ke admin. Kemampuan baru `deletion.confirm`. Ini menjawab [27 → Pertanyaan terbuka](27_IMPLEMENTASI_PERAN_PENELITI.md#16-pertanyaan-terbuka) no. 2. Tidak dipilih: tanpa konfirmasi; hanya `ketua` yang mengajukan.
2. **Salinan untuk orang tua tidak memuat jawaban ang-21–ang-23, kecuali anak setuju**, dengan centang di log audit, supaya janji di T11 ("orang tua tidak melihat jawabanmu") tetap terpenuhi. Dasar hukumnya `needs_verification`. Tidak dipilih: selalu disertakan dengan T11 diubah; selalu ditahan.
3. **Cadangan diganti** setelah penghapusan dan saat studi ditutup: cadangan baru, lalu cadangan lama di kedua lokasi dihapus, sebelum orang tua diberi tahu; lokasi kedua juga 30 hari. Tidak dipilih: daftar hapus yang diterapkan ulang saat pulih; diserahkan ke penyedia server.
4. **Semua kolom L7 yang berkode masuk panel**: tanggal izin orang tua, izin rekaman, izin butir risiko, status dan tanggalnya. Daftar izin berkode diunduh dari sistem dan dicocokkan `ketua` dengan L7. Tidak dipilih: hanya izin butir risiko di panel, daftar izin disalin tangan.

Enam pertanyaan terbuka di [30 → Pertanyaan terbuka](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#21-pertanyaan-terbuka) tidak menahan draf, antara lain dasar hukum menahan jawaban butir risiko, transkrip pada pilihan C, dan kritik dan saran yang memuat nama.

Akibat ke 01, 02, 03, 11, 22, 23, 26, 27, 28, dan 29 dicatat di [30 → Akibat ke dokumen lain](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#23-akibat-ke-dokumen-lain) dan dibawa sesi koordinator. Yang paling penting: 22 dan 23 → T10 (konfirmasi ketua, akun nonaktif saat status disimpan, salinan tanpa jawaban butir risiko, penggantian cadangan) dan tabel *Kegiatan* di 26.

Langkah berikutnya: pengembang meninjau 30. Lalu satu sesi koordinator menyelaraskan bagian 1–4: nomor migrasi `003400`–`003800`, kemampuan baru, rute, dan *Akibat ke dokumen lain* keempat bagian.

**Bagian 3 dokumen implementasi didraf: pembersihan log, unduhan data penelitian, kamus data, dan lembar lapangan** (cabang `pbb/sharp-feynman-xsm6ky`) · `asal: pengembang` · `draf pengembang` · Prototipe I, belum merevisi produk

[29_IMPLEMENTASI_DATA_PENELITIAN.md](29_IMPLEMENTASI_DATA_PENELITIAN.md) ditulis untuk perubahan sistem no. 7, 14, dan 15. Isinya:
- satu katalog event dengan pemilik setiap jenis (peneliti, admin, dihapus);
- event dan rincian perangkat yang dihapus, dan halaman *Log teknis* untuk admin;
- data mentah baru yang diminta kamus: `item_tries.ready_ms`, event `empty_tap` dan `passage_reopened`;
- isi unduhan `ketua`, waktu terkoreksi, dan butir risiko daring hanya sebagai angka gabungan (dari 26 → P8);
- lembar *Kamus Data* dari `ResearchDictionary` dengan `dictionary_version`, dan riwayat unduhan;
- templat lembar lapangan per sesi dan impor L1–L6, dengan kolom dari tabel bertanda di 02;
- skema, rute, teks panel, tes, dan titik sambung ke no. 1, 2, 5, 6, 9, 21, 22, dan bagian 4.

Tidak ada kode yang diubah.

**Temuan kode, untuk ditinjau pengembang.** Tiga hal di kode membuat 01 tidak bisa diterapkan apa adanya:
1. `dialogue_advanced` (ditandai dihapus) dipakai game untuk lencana "Belum didengar" di peta wilayah. Penggantinya satu event `dialogue_completed` di slide terakhir, dibuat juga dari data lama di migrasi ([29 → §5.3](29_IMPLEMENTASI_DATA_PENELITIAN.md#53-dialogue_advanced-diganti-dialogue_completed)).
2. `audio_pause` (ditandai dihapus) adalah satu-satunya pembawa lama mendengar narasi yang belum selesai. Event mentahnya tidak ditulis lagi, tetapi baris `pause` di tabel audio tetap, dan klien juga mengirim lama mendengar saat narasi diulang atau ditinggal ([29 → §5.4](29_IMPLEMENTASI_DATA_PENELITIAN.md#54-audio_pause-dan-lama-mendengar)).
3. `last_input_ms` Cari Objek memakai rujukan waktu yang berbeda dari tantangan lain; `ready_ms` menyamakannya ([29 → §6.1](29_IMPLEMENTASI_DATA_PENELITIAN.md#61-saat-butir-bisa-dijawab-ready_ms)).

Satu putaran tanya, empat pertanyaan; pengembang memilih usulan di tiga pertanyaan pertama ([29 → Keputusan pengembang](29_IMPLEMENTASI_DATA_PENELITIAN.md#19-keputusan-pengembang)):
1. **`ketua` mendapat lembar `event` dan kolom `answer_key`.** Lembar `event` hanya berisi event bertanda Peneliti, dengan kode sebagai pengganti id internal. Ini menjawab [27 → Data yang terlihat per peran](27_IMPLEMENTASI_PERAN_PENELITI.md#9-data-yang-terlihat-per-peran). Tidak dipilih: lembar `event` saja; tidak keduanya.
2. **`answer_changed` digabung ke `answer_submitted`**, dengan `changed` di payload. Di kode, `answer_changed` ditulis server untuk kiriman Cari Objek yang dinilai, bukan perubahan sebelum Cek seperti tertulis di 01. Tidak dipilih: dihapus seperti tertulis di 01.
3. **Data lama dihapus di migrasi** setelah `gelita:log:hitung` mencetak jumlahnya, dengan satu baris audit. Tidak dipilih: hanya berhenti menulis.
4. **Templat terisi per sesi lapangan, ditambah impor L1–L6 ke sistem sekarang.** Usulan AI adalah templat terisi tanpa impor; pengembang memilih impor. Akibatnya lembar yang diketik kini juga tersimpan di server, berkode dan tanpa nama, dan masuk unduhan `ketua`. Pengaman yang diturunkan dari 02 dan 26: kutipan ucapan siswa L5 dan token L1 tidak diimpor, L7 tidak pernah di server, dan isian teks yang memuat kata nama siswa rombel itu memberi peringatan yang harus dikonfirmasi. Tidak dipilih: satu templat kosong; templat terisi tanpa impor.

Lima pertanyaan terbuka di [29 → Pertanyaan terbuka](29_IMPLEMENTASI_DATA_PENELITIAN.md#18-pertanyaan-terbuka) tidak menahan draf, antara lain situs statis lembar lapangan dan `is_touch`. Pertanyaan terbuka [28](28_IMPLEMENTASI_SESI_LAPANGAN.md#21-pertanyaan-terbuka) no. 5 dijawab: `markStale()` menulis `session_paused {via: idle}`.

Akibat ke 01, 02, 03, 11, 22, 26, 27, dan 28 dicatat di [29 → Akibat ke dokumen lain](29_IMPLEMENTASI_DATA_PENELITIAN.md#20-akibat-ke-dokumen-lain) dan dibawa sesi koordinator. Yang paling penting: 22 dan 26 (A8) masih menulis lembar lapangan hanya sebagai kertas lalu templat, padahal kini lembar juga diunggah ke server.

Langkah berikutnya: pengembang meninjau 29. Bagian 4 (`30`) boleh ditulis; nomor migrasi `003600` mungkin bertabrakan dengan bagian 4 dan diselaraskan sesi koordinator.

**Bagian 2 dokumen implementasi didraf: sesi lapangan, token, kode peserta, pemantauan, dan `gelita:uji:akun`** (cabang `sky/practical-knuth-x5vyu0`, PR #282) · `asal: pengembang` · `draf pengembang` · Prototipe I, belum merevisi produk

[28_IMPLEMENTASI_SESI_LAPANGAN.md](28_IMPLEMENTASI_SESI_LAPANGAN.md) ditulis untuk perubahan sistem no. 11, 12, 13, dan 24. Bagian 2 ditulis sebelum PR #282 digabung, atas permintaan pengembang, di cabang yang sama dengan bagian 1. Isinya:
- sakelar mode penelitian per studi dan rencana pertemuan P1–P5;
- sesi lapangan dengan kode `S03-A-P2` dan susulan `-S`, `-S2`;
- token 6 karakter (huruf dan angka tanpa O, 0, I, 1, L), pendaftaran P1 yang dimulai dengan token, dan game tertutup di luar sesi;
- kode sekolah penelitian `schools.research_code` dan kode peserta `S03-A07` dengan penghitung yang dinaikkan lewat UPDATE bersyarat;
- isian L2, panel pemantauan, dan alat bantu `gelita:uji:akun`;
- skema, rute, teks layar, tes, dan titik sambung ke no. 16, 20, 21, 22, dan bagian 3–4.

Tidak ada kode yang diubah.

**Akibat dari kamus yang sudah disetujui, untuk ditinjau pengembang.** `kamus-1.0` menetapkan satu sesi game milik tepat satu sesi lapangan. Kode sekarang menyimpan kemajuan per sesi game (`session_progress`, `completedForSession()`), sehingga siswa akan mulai dari kosong di setiap pertemuan. Bagian 2 memindahkan kemajuan ke siswa per studi (`participant_progress`) dan membuang `session_progress` ([28 → Kemajuan mengikuti siswa](28_IMPLEMENTASI_SESI_LAPANGAN.md#8-kemajuan-mengikuti-siswa-bukan-sesi-game)). Ini perubahan terbesar di bagian 2.

Satu putaran tanya, empat pertanyaan; pengembang memilih usulan di keempatnya ([28 → Keputusan pengembang](28_IMPLEMENTASI_SESI_LAPANGAN.md#22-keputusan-pengembang)):
1. **Buklet dan bentuk tes dihitung panel dari baris dan kursi**, dengan peringatan bila buklet tidak seimbang, tanpa menahan. Ini mengubah keputusan 25 Oktober 2026 "buklet dimasukkan P1 dari L2": P1 kini memasukkan baris dan kursi. Tidak dipilih: buklet diketik P1, atau ketidakseimbangan menahan tes awal.
2. **`gelita:uji:akun` di luar alur token**:
   - akun `uji.*` dibuat langsung dengan sekolah `Sekolah Uji Validator` (`V01`) dan kode `UJI-01`, …;
   - `--reset` menghapus data permainan akun itu;
   - di G3 satu sesi lapangan panjang untuk validator.

   Tidak dipilih: lewat alur token dengan kode seperti `V01-A01`.
3. **Admin melihat panel pemantauan sebagai angka saja**, tanpa kode peserta dan token. Ini menjawab [27 → Pertanyaan terbuka](27_IMPLEMENTASI_PERAN_PENELITI.md#16-pertanyaan-terbuka) no. 3. Tidak dipilih: sama dengan peneliti; tidak ada akses.
4. **Token susulan hanya untuk siswa yang dicentang P1**, dengan centang awal untuk yang tidak hadir. Tidak dipilih: semua siswa rombel itu.

Enam pertanyaan terbuka di [28 → Pertanyaan terbuka](28_IMPLEMENTASI_SESI_LAPANGAN.md#21-pertanyaan-terbuka) tidak menahan draf, antara lain teks "enam huruf dan angka" dan batas 20 menit di P5.

Akibat ke 01, 02, 03, 11, 18, dan 20 dicatat di [28 → Akibat ke dokumen lain](28_IMPLEMENTASI_SESI_LAPANGAN.md#23-akibat-ke-dokumen-lain) dan dibawa sesi koordinator. 27 sudah diselaraskan di cabang ini (admin di pemantauan, nama berkas aturan repo).

Langkah berikutnya: pengembang meninjau 28. Bagian 3 (`29`) dan bagian 4 (`30`) boleh ditulis.

**Keamanan masuk staf disetujui** (cabang `sky/practical-knuth-x5vyu0`, PR #282) · `asal: pengembang` · `disetujui pengembang` · Prototipe I, belum merevisi produk

Pengembang menyetujui usulan di [27 → Pertanyaan terbuka](27_IMPLEMENTASI_PERAN_PENELITI.md#16-pertanyaan-terbuka) no. 1:
- kata sandi staf memakai aturan kata sandi kuat yang sama dengan siswa (`PasswordPolicy`: huruf besar, huruf kecil, angka, simbol, tanpa nama pengguna), ditambah panjang minimal 12 yang sudah ada;
- sesi staf berakhir setelah 2 jam tanpa aktivitas (`Config\Gelita::$staffIdleSeconds = 7200`, dicatat `staff_idle_logout`);
- tanpa verifikasi dua langkah.

Rinciannya di [27 → Keamanan masuk staf](27_IMPLEMENTASI_PERAN_PENELITI.md#111-keamanan-masuk-staf) (K15, keputusan no. 5). Status 27 tetap `draf pengembang`.

**Bagian 1 dokumen implementasi didraf: peran `peneliti` dan `ketua`, peran `guru` dihapus, panel peneliti** (cabang `sky/practical-knuth-x5vyu0`, PR #282) · `asal: pengembang` · `draf pengembang` · Prototipe I, belum merevisi produk

[27_IMPLEMENTASI_PERAN_PENELITI.md](27_IMPLEMENTASI_PERAN_PENELITI.md) ditulis untuk perubahan sistem no. 10. Isinya:
- tabel hak akses per halaman untuk tiga peran;
- peta kemampuan di kode, yang menggantikan cabang "admin atau bukan";
- migrasi yang menghapus akun `guru`;
- mengatur ulang kata sandi lewat kode peserta;
- menu panel per peran, tes, dan kriteria selesai.

Halaman bagian 2–4 disebut sebagai titik sambung dengan haknya sudah ditetapkan. Pencarian kode menunjukkan hampir semua pemeriksaan sekarang berbentuk `role === 'admin'`, sehingga akun `peneliti` baru akan diperlakukan sebagai guru tanpa sekolah dan tidak melihat apa pun. Karena itu peta kemampuan menjadi inti bagian ini. Tidak ada kode yang diubah.

Satu putaran tanya, empat pertanyaan; pengembang memilih usulan di keempatnya ([27 → Keputusan pengembang](27_IMPLEMENTASI_PERAN_PENELITI.md#17-keputusan-pengembang)):
1. **Dua peran peneliti.**
   - `peneliti` (akun `koordinator`, `pengamat`, `pewawancara`): kerja lapangan, mengatur ulang kata sandi lewat kode, data satu siswa lewat kodenya, dan mengajukan permintaan hapus data; tidak boleh mengunduh data.
   - `ketua`: semua hak `peneliti`, ditambah data semua siswa, unduh data, pengaturan studi, dan penanda tindak lanjut butir risiko daring.

   Tidak dipilih: satu peran untuk semua dengan penanda ketua, atau centang hak per akun.
2. **Akun `guru` dihapus di migrasi**, bersama berkas unduhan dan permintaan hapus datanya, dan `staff_users.school_id` dibuang. Tidak dipilih: akun guru dibiarkan terkunci.
3. **Studi diubah `ketua` dan dilihat `admin`**; rilis dan paket aset tetap milik admin. Tidak dipilih: tetap di admin, atau keduanya boleh mengubah.
4. **Ditulis di cabang yang sama dengan keputusan empat bagian** (PR #282). Tidak dipilih: menggabung PR #282 dulu.

Tujuh pertanyaan terbuka dicatat di [27 → Pertanyaan terbuka](27_IMPLEMENTASI_PERAN_PENELITI.md#16-pertanyaan-terbuka). Satu di antaranya menunggu pengembang sebelum 27 disetujui: keamanan masuk staf (aturan kata sandi, batas waktu sesi, verifikasi dua langkah). Sisanya diserahkan ke bagian 2–4.

Akibat ke 01, 03, 20, 22, dan 26 dicatat di [27 → Akibat ke dokumen lain](27_IMPLEMENTASI_PERAN_PENELITI.md#18-akibat-ke-dokumen-lain) dan dibawa sesi koordinator setelah bagian 2–4.

Langkah berikutnya: pengembang meninjau 27. Setelah PR #282 digabung, bagian 2–4 (`28`–`30`) boleh ditulis paralel.

**Dokumen implementasi untuk perubahan yang tidak bergantung pada hasil validasi didraf sekarang, dalam empat bagian** (cabang `sky/practical-knuth-x5vyu0`) · `asal: pengembang` · `disetujui pengembang` · Prototipe I, belum merevisi produk

Banyak dari 23 perubahan sistem di [01 → Catatan perubahan sistem](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti) tidak bergantung pada hasil validasi ahli, jadi dokumen implementasinya bisa didraf sekarang sebagai `draf pengembang`. Ini mengubah sebagian keputusan 25 Oktober 2026 (*Urutan kerja tetap seperti peta jalan di 01*): yang ditunda sampai G2 kini hanya **perubahan sistemnya**, bukan lagi seluruh dokumen implementasi. Menyusun draf adalah kerja dokumen, jadi urutan penelitian tetap utuh: tidak ada kode, migrasi, atau instalasi yang diubah sebelum G2. Satu putaran tanya, empat pertanyaan; pengembang memilih usulan di keempatnya.

Yang didraf sekarang: no. 7, 10, 11, 12, 13, 14, 15, dan 23, ditambah no. 24 (keputusan no. 4 di bawah). Yang menunggu matriks revisi induk ([20](20_PAKET_VALIDASI.md)): no. 1–6, 8, 9, dan 16–22. Isinya bergantung pada bank, instrumen, layar, atau aturan yang bisa berubah karena hasil validasi, jadi baru didraf setelah matriks itu ada, supaya tidak ditulis dua kali.

Yang diputuskan:

1. **Empat bagian**, masing-masing satu dokumen, satu sesi, dan satu cabang dari `main`. Nomor berkasnya melanjutkan folder ini (27–30). Bagian diberi nomor 1–4, bukan kode huruf, supaya tidak tertukar dengan risiko R1–R14 di [26](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md).

   | Bagian | Berkas | Perubahan | Isi pokok | Sumber utama |
   | --- | --- | --- | --- | --- |
   | 1 | `27_IMPLEMENTASI_PERAN_PENELITI.md` | 10 | Peran `peneliti` di samping `admin`; peran `guru` dihapus seluruhnya (halaman, hak akses, baris yang tersimpan); tabel hak akses; akun bernama samaran per peran, termasuk apakah `ketua` peran sendiri atau hak tambahan (dibutuhkan bagian 4); kerangka panel peneliti dan slug rutenya, tempat bagian 2–4 menambah halaman; reset kata sandi lewat kode peserta dengan sandi sementara dan jejak audit | 01 → *Admin dan peneliti*; 03 → *Akun staf*; 26 (tim pengendali) |
   | 2 | `28_IMPLEMENTASI_SESI_LAPANGAN.md` | 11, 12, 13, 24 | Sesi lapangan dan token 6 huruf; wilayah per pertemuan; susulan `-S`; isian L2 (buklet, baris, kursi) dengan cek keseimbangan per rombel; game tertutup di luar sesi; pendaftaran dari token; kode `S03-A07` dengan nomor urut yang aman saat banyak siswa mendaftar bersamaan; daftar kode sekolah (pemetaan NPSN hanya untuk admin) dan awalan `U`; `field_session_code` di sesi game; panel pemantauan; alat bantu `gelita:uji:akun` | 01 → *Token sesi*, *Kode peserta baru*, *Kerja lapangan*; 02 → L1, L2; 11 → syarat untuk no. 11; 18 (layar token); 20 → prasyarat no. 8 langkah 6 |
   | 3 | `29_IMPLEMENTASI_DATA_PENELITIAN.md` | 7, 14, 15 | Event dan kolom bertanda "Dihapus" dihapus; data bertanda "Admin" keluar dari ekspor penelitian; ekspor peneliti hanya dengan kode peserta; lembar *Kamus Data* diturunkan dari `kamus-1.0` (`dictionary_version`); event dari kamus yang masuk no. 14; riwayat unduhan; templat spreadsheet L1–L7 dengan kolom yang sama dengan 02 (L7 tetap di luar server dan disimpan terpisah) | 01 → *Log: dihapus atau dipisah*, *Olah data*; 11 → *Kunci penggabungan dan waktu*, *Variabel butir*, *Variabel peserta dan sesi*, *Aturan gabung lembar lapangan*, *Yang harus disediakan sistem di tahap 3*; 02 |
   | 4 | `30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md` | 23 | Teks centang persetujuan (id dan en); layar izin ulang T11 dan pesan dukungan T12; penanda tindak lanjut butir risiko hanya untuk `ketua` dan tidak pernah diekspor; status mundur, hapus sebagian, ambil data satu anak, pembetulan dengan jejak audit, penghapusan termasuk cadangan dan ekspor, daftar izin berkode, nama diganti kode saat studi ditutup; `retention_days` ± 7300 (bawaan migrasi sekarang 1825); `HashedIpSessionHandler` (sudah ada di `app/Libraries/`, belum dipasang) di `Config\Session` dan pembersihan rutin `ci_sessions`; jalan manual P15 selama fungsinya belum ada | 01 → no. 23; 22 → *Dasar hukum*, *Masa simpan dan pemusnahan*; 23 → T4, T10–T12; 26 → L3, P15, P18 |

2. **Urutan: bagian 1 lebih dulu**, karena bagian itu menetapkan nama peran dan panel peneliti yang dipakai bagian lain. Setelah bagian 1 digabung ke `main`, bagian 2–4 ditulis paralel di tiga sesi. Lalu satu sesi koordinator menyelaraskan nomor migrasi, rute, nama peran, dan *Akibat ke dokumen lain*, seperti tahap 2 pada 27 Oktober 2026.
3. **Status paling tinggi sebelum G2: `disetujui pengembang`**, seperti dokumen lain. Setelah matriks revisi induk dan keputusan komite etik ada, setiap bagian dicek ulang sekali terhadap keduanya sebelum dokumen implementasi lengkap disetujui. Perubahan yang diminta komite ditandai `asal: komite etik`. Implementasi tetap baru dimulai setelah G2 dan setelah dokumen implementasi lengkap disetujui.
4. **`gelita:uji:akun` menjadi perubahan sistem no. 24** dan didraf di bagian 2. Alat ini membuat akun siswa, jadi harus mengikuti alur pendaftaran baru (token, kode peserta). Ini menjawab keputusan terbuka di [00 → Langkah berikutnya di tahap 2](00_INDEKS.md).

**Batas antarbagian.** Keempat bagian menyentuh perubahan yang menunggu matriks revisi hanya sebagai **titik sambung**, tanpa merancangnya. Contohnya:
- token yang membuka modul tes (no. 22);
- bentuk A/B yang ditetapkan dari baris dan kursi L2;
- layar T11 sebelum butir risiko daring di modul no. 22;
- kolom ekspor dari no. 1, 5, 9, 21, dan 22, yang ditambahkan nanti ke spesifikasi ekspor yang sama.

`kamus-1.0` tidak diubah; variabel tes, angket, dan sikap tetap menunggu `kamus-1.1`. Perubahan yang dibutuhkan dokumen lain dicatat di bagian *Akibat ke dokumen lain* setiap bagian.

Tidak dipilih:
- tiga bagian (no. 10–13 digabung) atau delapan bagian (satu per perubahan);
- ditulis satu per satu, atau keempatnya paralel sekarang;
- tetap `draf pengembang` sampai G2;
- `gelita:uji:akun` tetap belum bernomor, atau dibuang.

**Temuan untuk bagian 4** (tidak diubah di cabang ini). Tabel di [26 → Kegiatan](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md#kegiatan) memberi nomor KG2–KG5 berbeda dari [03 → Kegiatan dengan anak](03_KODE_DAN_ARTI.md#kegiatan-dengan-anak) dan 22: di 26, KG2 adalah tes awal P1; di 03 dan 22, KG2 adalah berpikir lantang (tahap 4). Jadi tenggat "sebelum KG2" di 26 (IP berhash, P15, P18), di 22, dan di 01 no. 23 bisa dibaca berbeda. Bagian 4 menulis tenggatnya sebagai "sebelum kegiatan pertama yang memakai akun GELITA anak", lalu membetulkan tabel di 26 atau menitipkannya ke sesi koordinator.

Langkah berikutnya: satu sesi untuk bagian 1 (`27`), di cabangnya sendiri dari `main`, mulai dari 00 → *Tahap dokumen 3*.

**Akibat 25 dan 26 dibawa ke 01, 20, 21, 22, 23, dan 26** (cabang `sky/dazzling-planck-6cl9vy`, sesi koordinator) · `asal: pengembang` · `disetujui pengembang` · Prototipe I, belum merevisi produk

Keempat sesi paralel (PR #276–#279) hanya mengubah dokumennya sendiri, dan menitipkan akibatnya ke dokumen lain di bagian *Akibat ke dokumen lain*. Atas permintaan pengembang, akibat itu dibawa sekarang, sebelum versi bahan dibekukan untuk validasi. Tidak ada keputusan baru; semuanya menjalankan keputusan di 25 dan 26.

Yang diubah:
- **20.** Prasyarat no. 6 (cek fakta budaya) ditandai sudah. Daftar periksa G2 pengamat budaya menyebut "baris B.7 yang masih terbuka (25)", karena tahun Tambi dan letak Candi Arjuna sudah dijawab tim. Perkiraan waktu B.7 menjadi 1–1,5 jam untuk 44 fakta, sehingga Ahli Budaya Kedu 1 ± 9–13,5 jam dan Ahli Budaya Kedu 2 dan 3 ± 8,5–12,5 jam. Baris akibat alat bantu `gelita:uji:akun` ditandai sudah.
- **Instrumen pengamat budaya v2.1.** A.4: konfirmasi cek fakta 1–1,5 jam; catatan di D.4. PDF dan berkas validator dibuat ulang.
- **21.** "19 fakta" menjadi "44 fakta".
- **22.** Empat tambahan:
  - pengembang dan admin adalah bagian dari tim pengendali, bukan prosesor (26 keputusan no. 1);
  - baris *Log teknis* menyebut data akun staf dan `ip_hash`;
  - ringkasan risiko menyebut R1–R3 tinggi sebelum perlindungan dan tidak ada yang tinggi sesudahnya;
  - jalan manual sementara dengan berita acara (26 → P15), dan KG2 tidak dimulai sebelum jalan itu diuji.
- **23 → T6.** Lampiran 5 berisi ringkasan penilaian dampak satu halaman untuk kepala sekolah (26 keputusan no. 3), dengan teksnya.
- **26.** Nomor pasal diselaraskan dengan hasil cek di 22: hak subjek data Pasal 70–71, PPDP Pasal 142–147, Peraturan Lembaga belum terbit, 3 × 24 jam kalender. `needs_verification` yang tersisa hanya pencocokan dengan PDF resmi sebelum berkas diunggah.
- **01.** Tiga tambahan:
  - perubahan sistem no. 23 mendapat `HashedIpSessionHandler`, pembersihan `ci_sessions`, dan jalan manual sementara;
  - `gelita:uji:akun` dicatat sebagai usulan belum bernomor, karena nomornya menunggu keputusan pengembang;
  - daftar kerja no. 15 menyebut 26 sebagai lampiran 18.
- **25 dan 26** → *Akibat ke dokumen lain*: butir yang dibawa ditandai sudah.

Yang tetap untuk pengembang: target persetujuan etik di 01 (patokan BRIN ± 1–2 bulan sejak berkas lengkap), nomor perubahan sistem untuk `gelita:uji:akun`, dan teks ringkasan sekolah di 23 → T6 sebelum dicetak.

## 27 Oktober 2026

**Izin etik siap diajukan: komite BRIN dan formulirnya, pengecekan PP No. 33 Tahun 2026, tempat kosong 22 dan 23** (cabang `r10-formulir-etik`, dari `sky/dazzling-planck-6cl9vy`) · `asal: pengembang` · status `draf pengembang` · Prototipe I, belum merevisi produk

Satu putaran wawancara, empat pertanyaan; pengembang memilih usulan pada tiga pertanyaan dan mengisi sendiri pertanyaan server.

Yang diputuskan (22 no. 22–24, 23 no. 22):
- **Komite tujuan: Komisi Etik Bidang Sosial Humaniora BRIN**, lewat Sistem Informasi Klirens Etik Riset, dengan Formulir Klirens Etik Riset BRIN yang diterbitkan (empat bagian, matriks etika 3.1–3.8, checklist lampiran). Nama komite dan alamatnya ditulis di 22 dan di surat 23, karena itu badan publik; *Lembaga Komite Etik* tetap nama rujukan di dokumen lain. PSEPPKN 2021 tetap cadangan. Tidak dipilih: komite etik institusi sendiri, atau belum memilih.
- **Server: IDCloudHost (PT Cloud Hosting Indonesia), pusat data di Jakarta Pusat**, mengisi `[nama dan lokasi penyedia server]` di 22 dan 23 (T1 bagian 3). Perjanjian prosesor memuat sebelas hal di Pasal 14 dan lokasi pusat datanya.
- **Data pribadi orang sungguhan tetap di luar repo.** Nama, kontak, dan data diri ketua peneliti, pembimbing, tim, dan narahubung data tetap tempat kosong, dengan daftar isian di [22 → Daftar isian sebelum diajukan](22_PERMOHONAN_IZIN_ETIK.md#daftar-isian-sebelum-diajukan).

Yang dikerjakan:
- **22 disesuaikan dengan formulir BRIN** tanpa mengubah isi atau keputusan: bagian baru [Pengajuan lewat sistem klirens etik BRIN](22_PERMOHONAN_IZIN_ETIK.md#pengajuan-lewat-sistem-klirens-etik-brin) (delapan langkah dari akun sampai jawaban atas catatan komisi, dan lampiran mana yang diunggah di langkah mana), dan [Daftar isian sebelum diajukan](22_PERMOHONAN_IZIN_ETIK.md#daftar-isian-sebelum-diajukan) (yang sudah diisi, lalu kelompok A–F menurut siapa yang mengisi). Masa berlaku dan perpanjangan kini ditulis menurut BRIN (12 bulan, formulir amandemen); bagian PSEPPKN ditandai cadangan. Garis waktu mendapat baris perkiraan keputusan Januari–Februari 2027 (patokan BRIN ± 1–2 bulan sejak berkas lengkap); target persetujuan di 01 tidak diubah dan menunggu tinjauan pengembang.
- **PP No. 33 Tahun 2026 dicek ke transkripsi salinan naskahnya** (Meridian Hukum, memuat blok "Salinan sesuai dengan aslinya" Kementerian Sekretariat Negara; PDF resmi di peraturan.go.id, JDIH Setneg, JDIH Komdigi, dan BPK tidak bisa dibuka 27 Oktober 2026). Ditetapkan dan diundangkan 16 Juli 2026, LN 2026 No. 88, TLN No. 7190, 225 pasal. Dibetulkan: Pasal 82 ayat (4) → ayat (3) (penghapusan melibatkan prosesor). Dipertajam: 3 × 24 jam dihitung sejak permintaan diterima (Pasal 92 ayat (2)), juga di surat T1 bagian 6 dan T10; pembetulan Pasal 70–71 (bukan 69–71); akses Pasal 77 ayat (1), salinan Pasal 78; prosesor lain Pasal 15 ayat (2); PPDP Pasal 142–147; definisi anak "dan belum menikah"; Pasal 115 juga untuk dampak serius terhadap kepentingan masyarakat; Pasal 38 ayat (7); batas 3 × 24 jam ditulis dalam jam, jadi definisi "Hari" tidak berlaku dan tim menghitung jam kalender. Nomor pasal UU 27/2022 cocok dengan naskah anotasi Mahkamah Konstitusi. `needs_verification` yang tersisa: satu pencocokan lagi dengan PDF resmi sebelum berkas diunggah.
- **Peraturan Lembaga belum terbit**, dan Badan Pelindungan Data Pribadi belum dibentuk (berita Bisnis 13 September dan CNBC Indonesia 16 September 2026). Selama masa peralihan, pemberitahuan kebocoran dan aduan ke "lembaga" dikirim ke Kementerian Komunikasi dan Digital ([22 → Dasar hukum](22_PERMOHONAN_IZIN_ETIK.md#dasar-hukum)).
- **Tempat kosong yang diisi:** nama dan kontak komite (laman klirensetik.brin.go.id, dibuka 27 Oktober 2026), pedoman di komitmen etik, penyedia server, nomor WA SAPA 129 (08111-129-129, pernyataan Kementerian PPPA 23 Februari 2026 dikutip Kompas TV), dan lampiran 18 ditautkan ke [26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md), yang ditulis sesi lain dengan tempat kosong yang sama.

Akibat ke dokumen lain (dicatat di 22 → *Akibat ke dokumen lain*, tidak diubah di cabang ini): target persetujuan di 01 untuk ditinjau pengembang; 26 memakai isian server dan Kementerian Komunikasi dan Digital yang sama; 24 dan 02 → L7 tidak perlu diubah.

**Prasyarat paket validasi: nama asli, instalasi uji dan akun `uji.*`, honorarium, dan prosedur pembekuan** (cabang `r10-prasyarat-validasi`, berangkat dari `sky/dazzling-planck-6cl9vy`, PR #275) · `asal: pengembang` · `disetujui pengembang` · Prototipe I, belum merevisi produk

Cara memenuhi prasyarat no. 9, 8, 10, dan 4 di [20 → Ringkasan](20_PAKET_VALIDASI.md#ringkasan) ditulis di bagian baru [20 → Cara memenuhi prasyarat yang tersisa](20_PAKET_VALIDASI.md#cara-memenuhi-prasyarat-yang-tersisa). Prasyarat no. 6 (cek fakta budaya) dipegang sesi lain. Tidak ada kode atau sistem yang diubah.

Keputusan pengembang (dua putaran tanya, 20 → *Keputusan pengembang* no. 5–11):

1. **Instalasi uji di komputer lab kampus** (Jalur W di `docs/08_DEPLOYMENT.md`), terpisah dari produksi. Tidak dipilih: VPS kecil terpisah (usulan), laptop pengembang dengan terowongan.
2. **Dibuka dari luar lewat subdomain kampus dengan HTTPS** (usulan). Tidak dipilih: layanan terowongan, hanya di jaringan kampus.
3. **Kesediaan validator ditanyakan ketua peneliti**, dengan pesan yang disiapkan pengembang (usulan). Tidak dipilih: pengembang.
4. **Akun `uji.*` dibuat pengembang**, pemegang `admin` instalasi uji, lewat pendaftaran biasa; sandi diserahkan ke ketua peneliti (usulan). Tidak dipilih: ketua peneliti, validator mendaftar sendiri.
5. **Besaran honorarium tidak pernah masuk repo** (usulan). Tidak dipilih: besaran per panel di 20.
6. **Pembekuan sebagai langkah terakhir:** setelah cek fakta budaya, PR #275, dan PR ini digabung, dan setelah no. 8–10 selesai (usulan). Tidak dipilih: bekukan sekarang lalu bekukan ulang.
7. **Semua wilayah terbuka** di instalasi uji (`unlock_mode` = `free`) (usulan). Tidak dipilih: berurutan.

Yang ditulis di 20:
- **No. 9:** templat daftar padanan di luar repo (samaran, nama asli, instansi, kontak, kesediaan dengan tanggal dan saluran, akun uji, honorarium, izin disebut nama) dengan kesebelas baris samaran, dan pesan kesediaan dari ketua peneliti. Di repo hanya "selesai" dan tanggal.
- **No. 8:** langkah memasang instalasi uji dari *commit* beku, membuka semua wilayah di `/admin/studi`, membuat akun lewat `/daftar` (isian per langkah sesuai `RegisterController` dan `GelitaRules`: nama samaran tanpa angka, umur 11, "Sekolahku tidak ada di daftar" dengan `Sekolah Uji Validator`, sandi kuat 8–64 karakter), cek per akun, reset sandi lewat `/admin/peserta`, dan cara mereset kemajuan.
- **No. 10:** yang harus diketahui ketua peneliti (besaran, sumber dana yang sama dengan 22 dan 23, ketentuan dan pajak, waktu bayar, bukti bayar) dan tempat mencatatnya di luar repo.
- **No. 4:** sepuluh langkah pembekuan (membuat ulang berkas validator per [21 → Cara membuat ulang](21_BERKAS_BUTIR_VALIDATOR.md#cara-membuat-ulang), *commit* beku, pasang di instalasi uji, persetujuan ketua menjadi `disetujui peneliti`, isi entri 04, teks "versi/tanggal" di D.1) dan tabel kapan `berkas-validator.php` dijalankan lagi.

Temuan dari kode (dicek 27 Oktober 2026):
- Tidak ada panel atau perintah untuk membuat akun siswa; `/admin/peserta` hanya menampilkan dan mereset sandi. Jadi akun dibuat lewat `/daftar`.
- Tidak ada reset kemajuan per akun. Akun cadangan tidak dipakai ulang (03 → *Akun uji validator* diperbarui); reset menyeluruh hanya `gelita:reset` di komputer lab, yang menghapus semua akun siswa dan mengembalikan studi ke `sequential`.
- Pustaka satu wilayah terbuka setelah keempat tantangannya selesai, juga di mode `free`. Validator yang perlu Pustaka menyelesaikan satu wilayah dulu.
- Usulan alat bantu `gelita:uji:akun` (membuat akun `uji.*`, mereset kemajuan satu akun, menolak berjalan bila ada siswa non-`uji.*`) dicatat di 20 untuk tahap 3, tidak dibuat sekarang.

Status prasyarat setelah sesi ini: no. 4, 8, 9, 10 masih "Belum", masing-masing dengan prosedur tertulis. Yang ditunggu: subdomain dari TI kampus (no. 8), jawaban kesediaan (no. 9), dan pernyataan ketua peneliti soal honorarium (no. 10).

**Penilaian dampak pelindungan data lengkap ditulis di 26 (lampiran 18 izin etik)** (cabang `r10-penilaian-dampak`) · `asal: pengembang` · status dokumen `draf pengembang` · Prototipe I, belum merevisi produk

[26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md) menguraikan ringkasan di [22 → Penilaian dampak pelindungan data](22_PERMOHONAN_IZIN_ETIK.md#penilaian-dampak-pelindungan-data) menjadi penilaian lengkap untuk komite dan PPDP: uraian dan tujuan pengolahan, dasar hukum (nomor pasal sama dengan 22, `needs_verification`), inventaris data anak, orang tua, sekolah, staf, dan log dengan tingkat kepekaan, aliran data, kebutuhan dan proporsionalitas, hak, 14 risiko dengan kemungkinan dan keparahan sebelum dan sesudah perlindungan, 18 langkah perlindungan, risiko sisa, konsultasi, tanda tangan, dan tinjauan ulang. Tidak ada aturan data baru; isinya mengikuti 22.

Temuan pokok:
- **Tiga risiko tinggi sebelum perlindungan:** kebocoran yang mengungkap nama bersama jawaban (R1), pengenalan kembali dari gabungan data (R2), dan jawaban butir risiko daring terbaca orang lain (R3). Sesudah perlindungan tidak ada risiko tinggi; R1 dan R2 tetap sedang, yang lain rendah. Karena itu konsultasi lebih dulu dengan lembaga pelindungan data pribadi tidak diperkirakan perlu.
- **Temuan dari skema basis data:** tabel sesi peramban `ci_sessions` menyimpan IP mentah sampai sesi kedaluwarsa dibersihkan, karena `HashedIpSessionHandler` belum dipasang (`docs/01_DATABASE.md`).
- **Tiga fungsi hak orang tua** (hapus sebagian, ambil data satu anak, pembetulan dengan jejak audit) baru ada di tahap 3. 26 menambah jalan manual sementara dengan berita acara, dan KG2 tidak dimulai sebelum fungsi atau jalan manual itu diuji (langkah P15).

Keputusan pengembang (27 Oktober 2026, `disetujui pengembang`; semuanya pilihan yang diusulkan):

1. **Pengembang dan admin adalah bagian dari tim pengendali**, terikat pernyataan kerahasiaan; prosesor hanya penyedia server. Tidak dipilih: pengembang/admin sebagai prosesor tersendiri dengan perjanjian kedua.
2. **IP mentah di `ci_sessions` diperbaiki sebelum KG2** (pasang `HashedIpSessionHandler`, bersihkan sesi kedaluwarsa rutin), sebagai perubahan sistem tahap 3. Tidak dipilih: diterima sebagai risiko sisa.
3. **Konsultasi dengan PPDP atau narahubung** (saran tertulis sebelum permohonan diajukan) **dan kepala sekolah** (ringkasan satu halaman bersama T6). Tidak dipilih: ditambah wakil orang tua per sekolah; atau PPDP saja.
4. **Tinjauan ulang tahunan dan saat ada perubahan**: bersama laporan kemajuan, sebelum amandemen yang mengubah data, setelah kebocoran, saat Peraturan Lembaga terbit, dan saat studi ditutup. Tidak dipilih: hanya saat ada perubahan.

Akibatnya (dibawa sesi yang memegang 22, 23, dan 01; daftar lengkap di [26 → Akibat ke dokumen lain](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md#akibat-ke-dokumen-lain)):
- **22:** lampiran 18 ditautkan ke 26 dengan status `draf pengembang`; tautan di *Penilaian dampak*; kalimat kedudukan pengembang dan admin di *Pengendali*; jalan manual sementara di *Mengundurkan diri*; hasil cek pasal dibawa ke 26, termasuk UU Pasal 4 ayat (2) huruf e dan Pasal 25 yang dipakai 26.
- **01:** perubahan sistem no. 23 mendapat `HashedIpSessionHandler`; daftar kerja no. 15 menyebut 26.
- **23:** T6 menyebut ringkasan penilaian dampak satu halaman.
- Tempat kosong baru khusus 26: `[tanggal tinjauan pertama]`, `[tanggal penandatanganan]`, `[nama pengembang]`, `[nama pembimbing]`, `[saran PPDP atau narahubung]`, `[tanggapan sekolah]`. Tempat kosong bersama 22 dan 23 dipakai dengan teks yang sama.

Bergantung pada PR #275 (cabang `sky/dazzling-planck-6cl9vy`, penamaan "pengembang" dan "AI"); `main` digabung ke cabang ini setelah #275 masuk.

**Cek fakta budaya tim: 135 klaim dicek, B.7 IB diperbarui, perbaikan 08 dan 12 diputuskan** (cabang `r10-cek-fakta-budaya`, bergantung pada PR #275) · `asal: pengembang` · `draf pengembang`, keputusan perbaikan `disetujui pengembang` · Prototipe I, belum merevisi produk

Prasyarat no. 6 di [20 → Ringkasan](20_PAKET_VALIDASI.md#ringkasan) dipenuhi di [25_CEK_FAKTA_BUDAYA.md](25_CEK_FAKTA_BUDAYA.md). Klaim budaya dikumpulkan dari:
- matriks 46 objek IB;
- isi budaya 08;
- keterangan Susun Gambar;
- cerita 12.

Setiap klaim dicek ke sumber dengan bantuan AI, dan hanya sumber yang benar-benar dibuka yang dikutip. Hasilnya: 103 dikonfirmasi, 20 varian, 2 keliru, 10 `needs_verification`. Ini kerja pengembang, bukan validasi ahli; pengamat budaya mengonfirmasinya di B.7.

Yang diputuskan pengembang (dua putaran, empat pertanyaan per putaran):
1. **Topeng Ireng `mgl-2-b04`.** Bacaan lama keliru: menurut dua kajian, gerak tari ini menggambarkan prajurit, bukan petani. Bacaan ditulis ulang ke makna keprajuritan dengan kunci *prajurit* dan pengecoh *penjajah* (INF-LIT); AKM L2 dan Bloom C2 tetap. Pengembang memilih ini, bukan usulan yang direkomendasikan (kunci *petani* tetap dengan kalimat baru).
2. **Kompleks Candi Arjuna** ada di Desa Dieng Kulon, Kecamatan Batur, Banjarnegara. Gambarnya tetap menjadi variasi `a` Susun Gambar Wonosobo, tetapi keterangannya menyebut letak itu, dan deskripsi tantangan menjadi "lukisan Wonosobo dan Dieng".
3. **Prasasti Gondosuli** ditulis "diperkirakan dibuat sekitar tahun 832 Masehi". Prasastinya tidak bertanggal, dan 832 adalah tafsiran de Casparis. Kunci `tmg-3-b02` tetap *benar*.
4. **Petunjuk teh Tambi** ditulis "sejak zaman Belanda": kebunnya sudah ada sejak 1838, dan baru disewa swasta pada 1865. Pertanyaan terbuka 1865 atau 1885 ditutup.
5. **Enam rumusan kecil di 08**, tanpa mengubah kunci:
   - tinggi Sindoro dan Sumbing ditulis "sekitar";
   - alasan larangan menyentuh relief menjadi "aus";
   - keterangan keliru `mgl-4-a` menjadi "tarian asli dari Temanggung";
   - getuk "dimasak";
   - embun upas "suhu di dekat tanah";
   - "panas dari permukaan bumi".

   "Puluhan penari" juga menjadi "banyak penari".
6. **"Dataran Kedu" menjadi "tanah Kedu"** di 12, pada baris yang mencakup Wonosobo (*Benang merah baru*, `penutup-01`).
7. **Perbaikan di berkas data** (keterangan Susun Gambar dan baris Kenali) dicatat di 25 → *Akibat ke dokumen lain* dan diterapkan di tahap 3.

Yang diubah:
- 25 baru;
- 08 dan 12 sesuai keputusan;
- IB: B.7 menjadi 44 fakta berkode, nama objek B.5 no. 6, 15, 26, 31, 42, dan 46, catatan di C.3, D.3, dan D.4;
- PDF IB dan lembar kerja validator, dibuat ulang;
- baris 25 di 00.

Yang dibawa sesi lain: di 20, prasyarat no. 6 dan daftar periksa pengamat budaya; di 21, "19 fakta" menjadi "44 fakta" (daftar di 25).

**Peran pengembang, peneliti, dan ahli; "pemilik proyek" menjadi "pengembang", nama asisten menjadi "AI"** (cabang `sky/dazzling-planck-6cl9vy`) · `asal: pengembang` · Prototipe I, belum merevisi produk

Pengembang menetapkan cara kerja revisi ini. Pengembang, dibantu AI, boleh mengerjakan hal yang nantinya diserahkan ke ahli, komite, atau pihak lain: memberi label, skor, telaah, dan validasi latihan, misalnya memberi skor pada butir instrumen validasi. Kerja itu ditandai jelas sebagai kerja pengembang. Peneliti menerima semuanya, menindaklanjutinya, menetapkannya, dan menyerahkannya ke ahli, komite, dan pihak terkait. Judul penelitian dan 10 analitik tetap berasal dari peneliti. Sebelum diputuskan final, tidak ada yang beku penuh: perubahan mendadak atau nanti tetap boleh, asalkan jelas berasal dari pengembang atau dari peneliti bersama ahli.

Yang diputuskan:
- **Prinsip baru di [01 → Peran pengembang, peneliti, dan ahli](01_REVISI_10_ANALITIK.md#peran-pengembang-peneliti-dan-ahli).** Ada dua jalur. Kerja pengembang bukan validasi ahli: skornya tidak masuk V Aiken, I-CVI, S-CVI, κ, atau ICC, mengikuti T6 di 07. Ahli menilai tanpa melihat skor atau label pengembang. "Dibekukan" berarti versinya dicatat, bukan tidak boleh diubah. Versi yang mengikat pihak lain ada empat: paket validasi, lampiran izin etik, skema kode kesalahan setelah uji coba, dan kamus data sebelum pengambilan data. Satu orang yang memegang dua peran menandai keputusannya per peran. Bantuan AI ditulis terbuka di naskah penelitian.
- **Peran baru di [03](03_KODE_DAN_ARTI.md#peran):** *Pengembang (berbantuan AI)*. Baris *Peneliti* ditambah perannya di dokumen revisi.
- **Label asal dan status di [00](00_INDEKS.md#label-asal-dan-status).** Asal: `pengembang`, `peneliti`, `peneliti + ahli`, `komite etik`. Status: `draf pengembang` → `disetujui pengembang` → `disetujui peneliti` → `tervalidasi ahli` → `final`. Status `disetujui pengembang` ditambahkan di antara draf dan persetujuan peneliti, karena semua yang dicatat "disetujui" sejauh ini ada di tingkat itu. `needs_verification` tetap penanda terpisah.
- **"Pemilik proyek" diganti "pengembang"** di semua berkas folder ini, kecuali draf asli di `dokumen-pdf/arsip/` yang tidak diubah. Penggantian mencakup Markdown, sumber HTML instrumen, dokumen pendukung peneliti, gambar SVG, skrip `berkas-validator.php`, dan `LAPORAN.md`. Jangkar ikut berubah, misalnya `#keputusan-pemilik-proyek` menjadi `#keputusan-pengembang`. Keputusan lama di catatan ini ikut ditulis ulang dengan kata "pengembang". Kedudukannya `disetujui pengembang`, dan peneliti mengukuhkannya saat menindaklanjuti.
- **Nama asisten diganti "AI".** Contohnya: kolom penyusun "AI (draf), pengembang"; T6 di 07 "AI bukan validator"; "Disusun: AI" di dokumen pendukung peneliti. Yang tetap hanya jalur berkas aturan `.claude/rules/`, karena itu nama folder di repo.
- PDF ketiga instrumen dan dokumen model penelitian dibuat ulang dari sumbernya; jumlah halamannya sama (43, 31, 29, 12). Berkas validator dibuat ulang dari instrumen yang diperbarui.

Pekerjaan sebelum validasi dan izin etik dilanjutkan di sesi paralel. Masing-masing memakai prinsip ini dan mencatat keputusannya sendiri di sini.

**Akibat sesi PP No. 33 Tahun 2026 dan sesi BRIN dibawa ke 01, 02, 22, 23, dan 24** (cabang `sky/vigilant-wozniak-12kyni`, sesi koordinator) · Prototipe I, belum merevisi produk

Kedua sesi berjalan bersamaan, dan masing-masing menyerahkan sebagian akibatnya kepada sesi yang lain. Setelah keduanya digabung (#272, #273), akibat itu tidak dipegang siapa pun, dan dokumen yang akan dikirim saling bertentangan: keputusan no. 19 di 22 menetapkan data berkode disimpan paling singkat 20 tahun, sedangkan tabel masa simpan 22, surat orang tua T1 dan T8a, dan 24 masih menulis 5 tahun. Atas permintaan pengembang, akibat itu dibawa sekarang, sebelum versi bahan dibekukan untuk validasi ([20 → Ringkasan](20_PAKET_VALIDASI.md#ringkasan)). Tidak ada keputusan baru; semuanya menjalankan keputusan no. 10–21 di 22.

Yang diubah:
- **Masa simpan data berkode paling singkat 20 tahun** (22 no. 19) di 22 (*Isi surat orang tua*, tabel *Masa simpan dan pemusnahan*, butir 3.5), 23 (T1 bagian 3, T8a *Kerahasiaan*, *Dasar yang sudah diputuskan*, *Tabel pembagian*, tabel masa simpan), dan 24 (*Dasar yang sudah diputuskan*, *Pengelolaan data*, *Yang dibutuhkan dari dokumen lain*). Kalimat untuk orang tua: data berkode disimpan paling sedikit 20 tahun karena undang-undang mewajibkannya, tanpa nama, dan setelah penelitian ditutup tidak bisa lagi dihubungkan dengan anak. Keputusan lama yang menyebut 5 tahun (22 no. 1, 23 no. 1, 24 no. 6) diberi catatan "diganti no. 19", tidak ditulis ulang.
- **UU No. 11 Tahun 2019 Pasal 40 ayat (5)** ditambahkan ke 22 → *Dasar hukum*.
- **22** juga mendapat:
  - status di kepala yang menyebut PP No. 33 Tahun 2026 dan panduan BRIN sebagai pedoman utama;
  - catatan patokan telaah BRIN di pembuka;
  - catatan formulir BRIN bagian 1 di *Identitas dan tim*;
  - butir pernyataan versi tidak ada potensi konflik di *Konflik kepentingan*;
  - lampiran 18 (penilaian dampak lengkap, belum ditulis) dan daftar dokumen pengendali yang diperlihatkan kalau diminta;
  - rujukan PP 33/2026 di standar 6 daftar tilik.
- **02 → L5** dan 22 → *Prosedur per pertemuan*: wawancara tidak terdengar guru atau teman (panduan BRIN butir 3.1). **02 → L7** mendapat butir surat dari panduan BRIN.
- **23 → *Cek isi L7*** mendapat baris tanpa foto dan video, lima baris PP No. 33 Tahun 2026, dan dua baris panduan BRIN; tabel masa simpan mendapat baris daftar izin berkode.
- **01:**
  - *Kerja lapangan* langkah 1 memakai waktu surat H-14 sampai H-10, pengingat H-5, batas kembali H-3 (22 no. 7), menggantikan "H-7 sampai H-1".
  - Daftar kerja no. 15 menyebut pedoman BRIN dan patokan telaahnya.
  - Perubahan sistem no. 23 mendapat empat perubahan dari PP No. 33 Tahun 2026 dan `retention_days` ± 7300 hari. Digabung ke no. 23, bukan nomor baru, supaya jumlah perubahan sistem tetap 23.

Yang sengaja tidak dibawa:
- 11 (`consent_ok` untuk `data tetap`, daftar izin berkode sebagai sumber `consent_ok`) menunggu `kamus-1.1`, karena `kamus-1.0` tidak diubah.
- Kontak PPDP tidak dipindah ke T1 bagian 8, karena tidak wajib.
- Target persetujuan etik di 01 tetap, dan ditinjau pengembang setelah komite dipilih.

Masih harus dikerjakan sebelum permohonan diajukan (minggu kedua–keempat November 2026):
- menulis penilaian dampak lengkap (lampiran 18);
- mengecek nomor pasal PP 33/2026 dan UU 27/2022 ke naskah resmi, dan Peraturan Lembaga yang sudah terbit;
- mengisi tempat kosong seperti `[nama dan lokasi penyedia server]` dan `[kontak PPDP atau narahubung]`;
- menyesuaikan 22 dan 23 dengan formulir komite yang dipilih.

**Keputusan pengembang: panduan klirens etik BRIN menjadi pedoman utama 22 dan 23** (cabang `r10-klirens-brin`) · Prototipe I, belum merevisi produk

*Panduan Klirens Etik Riset Bidang Sosial Humaniora* (Komisi Etik Bidang Sosial Humaniora BRIN, kata pengantar 12 November 2025, Penerbit BRIN Maret 2026, DOI 10.55981/brin.2462) dibaca utuh, termasuk bagan alir dan contoh lampiran yang berupa gambar, lalu dibandingkan dengan 22 dan 23.

Temuan pokok:

1. **Cakupan.** Riset sosial dan humaniora oleh periset di dalam dan di luar BRIN, lewat Sistem Informasi Klirens Etik Riset, tanpa biaya. Anak disebut kelompok rentan, dan penelitian di SD dipakai sebagai contoh, jadi cocok untuk GELITA. Komite di lembaga lain bisa memakai formulirnya sendiri.
2. **Prinsip:** tiga prinsip CIOMS 2016, ditambah keselamatan peneliti dan konflik kepentingan. Bahaya yang dicegah: fisik, psikis, sosial, dan hukum.
3. **Telaah:** tanpa tingkat dikecualikan, dipercepat, atau lengkap. Setiap usulan dibaca sedikitnya tiga anggota, lalu diputuskan di sidang pleno (Rabu pekan pertama dan ketiga). Patokan bagan alir ± 1–2 bulan sejak berkas lengkap. Keputusannya disetujui, disetujui dengan catatan, revisi (10 hari kerja), atau ditolak (kalau data sudah diambil).
4. **Masa berlaku 12 bulan;** perpanjangan dan perubahan lewat formulir amandemen. Periset melapor setelah pengumpulan data lapangan selesai.
5. **Formulir empat bagian:** data diri (termasuk jenis kelamin, pendidikan, disiplin, kepakaran, dan alamat), deskripsi riset 2.1–2.11 (termasuk tautan instrumen daring dan lama dibuka, serta anggaran dan independensi), matriks etika 3.1–3.8, dan checklist lampiran.
6. **Lampiran wajib:** proposal, instrumen, formulir, surat pengantar lembaga, pernyataan konflik kepentingan, dan *informed consent*; surat izin orang tua untuk peserta di bawah 18 tahun.
7. **Isi lembar persetujuan** mencakup judul dan alamat komite.
8. **Butir 3.5 merujuk UU No. 11 Tahun 2019 Pasal 40 ayat (5):** data primer dan keluaran penelitian wajib disimpan paling singkat 20 tahun. Teks pasalnya dicek 27 Oktober 2026.

Yang sudah sesuai: persetujuan berlapis dan persetujuan anak, sukarela, tanpa penipuan, data berkode, mitigasi kelompok rentan, butir sensitif dengan prosedur rujukan, risiko bagi peneliti, hasil kembali ke sekolah, tidak ada kegiatan sebelum persetujuan, amandemen, dan isi pokok surat T1.

Keputusan pengembang (27 Oktober 2026):

1. **Panduan BRIN menjadi pedoman utama** (pilihan B). PSEPPKN 2021 disimpan sebagai pedoman cadangan. Jawaban awal pengembang adalah pilihan A (berdampingan), lalu diganti B dalam sesi yang sama. Tidak dipilih: berdampingan sampai komite dipilih (usulan), atau catatan saja.
2. **Data penelitian berkode disimpan paling singkat 20 tahun** (sesuai usulan). Nama, L7, dan kunci kode tetap dimusnahkan saat studi ditutup, dan rekaman tetap dihapus setelah transkrip diperiksa. Ini menggantikan "5 tahun setelah studi ditutup" untuk data berkode. `needs_verification`: apakah data berkode tanpa kunci memenuhi kewajiban simpan "data mentah autentik". Tidak dipilih: tetap 5 tahun dengan alasan, atau 20 tahun hanya untuk komite BRIN.
3. **Lokasi ditulis lengkap sampai desa/kelurahan dan dusun** di formulir butir 2.5 dan di usulan penelitian, mengikuti tabel panduan. Nama sekolah tetap tidak ditulis di judul, laporan, atau publikasi. Tidak dipilih: sampai kabupaten/kota atau kecamatan (usulan).
4. **Pernyataan konflik kepentingan versi "tidak ada potensi konflik".** Pengembangan GELITA oleh tim sendiri dan pengamannya tetap diuraikan di 22 → *Konflik kepentingan* dan di isian 3.8. Tidak dipilih: versi "ada potensi konflik" (usulan).

Akibatnya:
- **22** mendapat:
  - *Pedoman yang dirujuk* dengan BRIN sebagai pedoman utama dan PSEPPKN 2021 sebagai cadangan;
  - *Pemetaan ke formulir klirens etik BRIN* (bagian 1–4, dengan tempat kosong untuk data tim, anggaran, tautan GELITA, dan lokasi) dan *Telaah dan masa berlaku menurut panduan BRIN*;
  - catatan pedoman cadangan di format protokol dan daftar tilik;
  - komitmen etik yang menyebut pedoman komite yang dipilih;
  - kolom *BRIN* di lampiran, lampiran 15–17 (proposal, pernyataan tanpa potensi konflik dengan teksnya, formulir terisi), dan daftar lampiran BRIN yang tidak berlaku;
  - keputusan no. 18–21 dan baris baru di *Akibat ke dokumen lain*.
- **23** mendapat: baris judul penelitian di T1, T6, dan T8a; tempat kosong `[judul penelitian]`; `[kontak komite etik]` berisi alamat, situs, dan surel komite; keputusan no. 20–21.
- **00** dan `docs/PROGRESS.md` diperbarui.

Belum diubah karena dipegang sesi PP No. 33 Tahun 2026 (dicatat di 22 → *Akibat ke dokumen lain*):
- masa simpan 20 tahun di 22 → *Masa simpan dan pemusnahan* dan *Isi surat orang tua*;
- UU No. 11 Tahun 2019 di 22 → *Dasar hukum*;
- masa simpan di 23 → T1 bagian 3 dan T8a *Kerahasiaan*; bagian 23 lain yang memuat "5 tahun" (*Dasar yang sudah diputuskan*, *Cek isi L7*) diubah bersamaan supaya 23 tidak saling bertentangan;
- 02 → L5: wawancara tidak terdengar guru atau teman (panduan butir 3.1).

Akibat lain yang belum dibawa:
- masa simpan di 24;
- target persetujuan di 01 ditinjau karena telaah BRIN jauh lebih cepat;
- *Konflik kepentingan* dan status di kepala 22;
- `retention_days` bawaan studi sebagai perubahan sistem tahap 3.

**Keputusan pengembang: 22 dan 23 dibandingkan dengan PP No. 33 Tahun 2026** (cabang `r10-pp-pdp`) · Prototipe I, belum merevisi produk

PP No. 33 Tahun 2026 adalah peraturan pelaksana UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi: diundangkan 16 Juli 2026 (LN 2026 No. 88), berlaku 16 Januari 2027 (Pasal 225), jadi berlaku penuh untuk KG1–KG5. Naskah resmi belum bisa diperoleh (peraturan.go.id dan JDIH belum memuat berkasnya). Yang dibaca utuh adalah naskah terkonsolidasi tidak resmi di datahukum.com (12 bab, berakhir di Pasal 225), dicocokkan dengan ringkasan beberapa kantor hukum. Ada sumber yang menyebut 15 Juli 2026 dan 224 pasal. **Semua nomor pasal `needs_verification`.** Ini pemeriksaan dokumen, bukan nasihat hukum.

Temuan pokok:

1. **Data anak** (di bawah 18 tahun) adalah data pribadi spesifik (Pasal 6). Pengolahannya wajib dengan persetujuan orang tua atau wali, yang diverifikasi "dengan mempertimbangkan teknologi yang tersedia" (Pasal 38). Bukti persetujuan wajib disimpan (Pasal 36).
2. **Pengecualian untuk penelitian ilmiah** (Pasal 148 ayat (1) huruf e, Pasal 154) hanya membatasi hak hapus, tarik persetujuan, keberatan atas keputusan otomatis, penundaan, dan portabilitas; tidak membatasi akses dan pembetulan, dan tidak membebaskan kewajiban pengendali (Pasal 149).
3. **Batas 3 × 24 jam** untuk akses, pembetulan, penghentian setelah tarik persetujuan, dan pemberitahuan setelah penghapusan (Pasal 71, 77, 87, 92). Persetujuan yang ditarik mewajibkan penghapusan (Pasal 82 ayat (1) huruf b), termasuk salinan cadangan.
4. **Kebocoran:** pemberitahuan tertulis paling lambat 3 × 24 jam kepada subjek data dan lembaga pelindungan data pribadi, dengan isi minimum dan kontak PPDP atau narahubung (Pasal 114–118). Sebelumnya 22 hanya menyebut sekolah, orang tua, dan komite.
5. **Penilaian dampak** wajib sebelum pengolahan (data spesifik; evaluasi dan penskoran sistematis; Pasal 120–121). Juga perjanjian prosesor dengan penyedia server (Pasal 14), PPDP atau narahubung (Pasal 142), catatan kegiatan pengolahan (Pasal 74), dan kebijakan masa simpan (Pasal 75). Server atau penyimpanan di luar negeri memicu aturan transfer Bab V.
6. **Informasi sebelum persetujuan** (Pasal 62): identitas pengendali dan kontak PPDP, dasar pengolahan, prosesor, dan hak akses, salinan, dan pembetulan belum ada di surat.

Yang sudah sesuai: centang terpisah per izin, surat sebelum formulir, mundur tanpa akibat, minimisasi data, kode dengan kunci terpisah, enkripsi, kalimat batas pengamanan daring, kegiatan pengganti bagi anak tanpa izin, dan masa simpan.

Keputusan pengembang (27 Oktober 2026), keempatnya pilihan yang diusulkan:

1. **Server, salinan cadangan, dan penyimpanan terenkripsi peneliti di Indonesia**; tidak ada transfer ke luar negeri. Tidak dipilih: boleh di luar negeri dengan penilaian transfer Bab V.
2. **Bukti persetujuan setelah studi ditutup: daftar izin berkode tanpa nama**, disimpan selama data berkode disimpan. Tidak dipilih: formulir bertanda tangan disimpan 5 tahun, atau tanpa perubahan.
3. **Ringkasan penilaian dampak ditulis di 22**, penilaian lengkap dilampirkan. Tidak dipilih: dokumen terpisah nanti.
4. **Pengecualian untuk penelitian tidak dipakai**: semua hak dijalankan sampai studi ditutup, pilihan A di T10 berarti persetujuan untuk data yang sudah ada tetap berlaku, dan batas 3 × 24 jam dipakai. Tidak dipilih: memakai Pasal 148 ayat (1) huruf e.

Diterapkan: 22 → *Isi surat orang tua*, *Mengundurkan diri dan hapus data*, *Kerahasiaan dan pengelolaan data* (subbagian baru *Pengendali, prosesor, dan narahubung data* dan *Penilaian dampak pelindungan data*, tempat data di Indonesia, daftar izin berkode, *Dasar hukum* ditulis ulang), *Pelaporan kejadian tak diinginkan* (langkah kebocoran), *Keputusan pengembang* no. 14–17; 23 → T1 bagian 3 dan 6, T2, T8a, T10 (pilihan D), tempat kosong (`[kontak PPDP atau narahubung]`, `[nama dan lokasi penyedia server]`; `[jumlah hari kerja]` dihapus), *Keputusan pengembang* no. 16–19; 02 → L7 (butir batas pengamanan daring yang sudah disetujui sebelumnya, isi surat dari PP 33/2026, verifikasi izin, daftar izin berkode).

Akibat yang belum dibawa (bagian sesi BRIN `r10-klirens-brin` atau dokumen lain; tercatat di [22 → Akibat ke dokumen lain](22_PERMOHONAN_IZIN_ETIK.md#akibat-ke-dokumen-lain)):
- 22 → *Lampiran*: penilaian dampak lengkap sebagai lampiran; 22 → *Daftar tilik tujuh standar*, standar 6: rujukan ke subbagian baru.
- 23 → *Cek isi L7 dan jalur persetujuan*: baris untuk butir L7 yang baru; 23 → T1 bagian 8: kontak PPDP boleh dipindah ke sana (tidak wajib).
- 01 → *Catatan perubahan sistem*: ambil data satu anak untuk permintaan akses, pembetulan data pendaftaran, penghapusan termasuk salinan cadangan, ekspor daftar izin berkode.
- 11 → *L7*: daftar izin berkode menjadi sumber `consent_ok` setelah L7 dimusnahkan.

Yang masih harus dicek sebelum permohonan diajukan: nomor pasal terhadap naskah resmi PP 33/2026 dan UU 27/2022, Peraturan Lembaga yang sudah terbit (PPDP, penilaian dampak, kebocoran, penghapusan), apakah lembaga pelindungan data pribadi sudah berjalan, dan apakah 3 × 24 jam dihitung dengan hari libur.

**Keputusan pengembang: 22 dan 23 diselaraskan dengan pedoman KEPPKN 2021** (cabang `sky/friendly-newton-jjuvj0`) · Prototipe I, belum merevisi produk

Pengembang memberi tautan *Pedoman dan Standar Etik Penelitian dan Pengembangan Kesehatan Nasional* (PSEPPKN) di situs KEPPKN. Isinya **edisi 2021** (KEPPKN periode 2021–2024, prakata 20 Juni 2021; berkasnya diunggah ulang Agustus 2025), sedangkan 22 masih merujuk edisi 2017. Naskah lengkapnya dibaca dan dibandingkan dengan 22, 23, dan 24.

Temuan pokok:

1. **Edisi dan nama.** Edisi 2021 ditulis *Komite* (bukan *Komisi*) Etik Penelitian dan Pengembangan Kesehatan Nasional. Tanda `needs_verification` tentang versi pedoman di 22 ditutup.
2. **Delapan kriteria di teks, tujuh di penilaian.** Bab III Standar 7 menambah *8. Pelindungan subjek rentan*. Daftar tilik (Lampiran 3) dan format protokol (Lampiran 1) masih memakai tujuh standar, dan Lampiran 1 masih menyebut "Pedoman KEPPKN 2017".
3. **Tingkat telaah.** Protokol dengan anak dibahas lengkap (*full board*). Data berkode yang bisa dihubungkan ke nama membuat penelitian ini tidak bisa dikecualikan.
4. **Masa berlaku satu tahun.** Rentang KG1–KG5 lebih dari setahun, jadi perpanjangan diajukan setiap tahun bersama laporan kemajuan.
5. **Kewajiban laporan:** laporan kemajuan paling sedikit sekali setahun, laporan akhir, KTD, dan penyimpangan; notifikasi cukup untuk perubahan yang tidak menambah risiko.
6. **Format protokol resmi dan daftar tilik.** Dibandingkan dengan Lampiran 1, 22 belum memuat aturan penghentian, rencana pelibatan masyarakat, hak atas data, komitmen etik, risiko bagi peneliti dan kelompok, dan simpulan risiko dan manfaat. Daftar tilik Lampiran 3 diisi peneliti sebelum protokol diajukan.
7. **Peneliti mahasiswa:** pembimbing yang mengirim permohonan dan mendampingi saat telaah lengkap.
8. **Surat orang tua** (Lampiran 2 dan indikator 7.3):
   - penelitian dengan alat daring yang melibatkan kelompok rentan harus menyebut upaya pengamanan dan batasnya (Lampiran 2 huruf i, CIOMS pedoman 22);
   - jenis pemberian harus disebut;
   - jumlah peserta, kontak cadangan, sumber dana, dan saksi juga diminta.

Yang sudah sesuai dan tidak diubah:
- persetujuan berlapis dan persetujuan ulang di P2–P5;
- tanpa penipuan, dan mundur tanpa sanksi;
- data berkode dengan kunci terpisah, serta penyimpanan terkunci dan terenkripsi;
- tanpa uang sebagai bujukan;
- dua orang dewasa dan tempat yang terlihat;
- prosedur rujukan dengan batas kerahasiaan;
- hasil kembali ke sekolah;
- protokol uji keterbacaan (24).

Pedoman tidak menetapkan masa simpan minimum dan menyebut komite tidak mengharuskan data dimusnahkan, jadi masa simpan di 22 tetap.

Keputusan pengembang (27 Oktober 2026):

1. **Semua usulan diterapkan:** sebelas perubahan di 22 dan empat di 23, dengan T8a dan T8b ikut di bagian yang berlaku.
2. **Tanda terima kasih disebut dalam satu kalimat netral** di 23 → T1 bagian 7 dan T8a: tanpa uang, tanpa biaya, dan sama untuk semua anak di kelas, ikut atau tidak, ditambah kudapan di P1 dan P5. Tidak dipilih: tetap tidak disebut di surat.
3. **Surat bisa dibacakan guru kelas atau peneliti.** Formulir T2 dan T8b mendapat baris saksi yang diisi hanya dalam keadaan itu, oleh orang di luar tim peneliti, dan tanda tangan boleh diganti cap jempol. Tidak dipilih: tanpa perubahan.
4. **Ketua peneliti mahasiswa (S2/S3).** 22 mendapat baris pembimbing/promotor; pembimbing mengirim permohonan dan mendampingi ketua peneliti saat telaah lengkap.
5. **Putaran tersendiri nanti:** panduan klirens etik bidang sosial humaniora BRIN (November 2025), kalau komite yang dipilih menelaah penelitian sosial atau pendidikan, dan PP No. 33 Tahun 2026 (peraturan pelaksana UU PDP, diundangkan 16 Juli 2026, berlaku 16 Januari 2027) untuk *Dasar hukum* dan batas pemberitahuan kebocoran di 22.

Aturan pelaksanaan yang ditulis sesi ini, bukan dari pedoman (bisa diubah pengembang):
- perpanjangan diajukan paling lambat satu bulan sebelum masa berlaku habis;
- sesi kelas ditunda kalau kurang dari dua peneliti hadir;
- setiap pembukaan kode oleh ketua peneliti dicatat tanggal dan alasannya;
- kalau sekolah menarik izin, data yang sudah terkumpul mengikuti pilihan tiap orang tua di 23 → T10.

Akibatnya:
- **22** mendapat:
  - rujukan edisi 2021 dan baris standar 8;
  - format protokol resmi;
  - *Tingkat telaah dan masa berlaku persetujuan*, *Laporan berkala dan laporan akhir*, dan *Penghentian kegiatan dan penelitian*;
  - notifikasi di *Perubahan protokol*;
  - risiko bagi kelompok dan peneliti, dengan simpulan;
  - pelibatan sekolah dan masyarakat, dan alasan melibatkan anak;
  - aturan membuka kode;
  - hasil diterbitkan apa pun hasilnya, dan hak atas data;
  - *Daftar tilik tujuh standar* dan *Komitmen etik* (lampiran 13 dan 14);
  - baris pembimbing;
  - nomor standar dan pedoman CIOMS di isu etik.
- **23** mendapat:
  - tambahan di T1, T2, T8, T8a, T8b, dan T9;
  - tempat kosong `[nomor cadangan]`, `[sumber dana]`, dan `[jenis tanda terima kasih]`;
  - baris baru di *Cek isi L7*;
  - hitung ulang keterbacaan: T1 13,0 kata per kalimat dan ± 11 menit; T2 13,0; T8a 10,9; T8b 12,2.
- **00** dan `docs/PROGRESS.md` diperbarui.
- **02 → L7** perlu butir isi surat *batas pengamanan daring*; ini dicatat di *Akibat ke dokumen lain* di 22 dan belum diubah.

**Keputusan pengembang: ketiga instrumen validasi ahli disetujui** (cabang `sky/inspiring-ritchie-nex4qi`, sesi koordinator) · Prototipe I, belum merevisi produk

1. **Instrumen ahli materi v2.1, pengamat budaya v2.1, dan ahli media v1.1 disetujui** apa adanya, dalam versi yang sudah diselaraskan dengan 20 dan 21 (entri di bawah).
2. Kotak status draf di sampul ketiganya dihapus (langkah 2 di `dokumen-pdf/README.md`), perubahannya dicatat di D.4 masing-masing, PDF dibuat ulang (43, 31, dan 29 halaman), dan berkas validator dibuat ulang karena HTML instrumen termasuk sumbernya (69 pemeriksaan lolos).

Akibatnya: prasyarat no. 1 dan 2 di 20 terpenuhi; 07, 00, README `dokumen-pdf/`, dan `docs/PROGRESS.md` diperbarui. 00 mendapat *Langkah berikutnya di tahap 2* sebagai pintu masuk sesi baru. Gerbang G1 tinggal menunggu daftar kode sekolah (no. 12), yang tidak menahan validasi maupun izin etik.

**Keputusan pengembang: satu level pengembang per butir tes D2 dan D3** (cabang `sky/inspiring-ritchie-nex4qi`, sesi koordinator; temuan no. 2 di [21](21_BERKAS_BUTIR_VALIDATOR.md)) · Prototipe I, belum merevisi butir

1. **Usulan sesi koordinator disetujui apa adanya.** Setiap nomor tes D2 dan D3 punya satu level pengembang, sama di bentuk A dan B, dengan arti level B.5a (L1 tanda jelas, L2 tanda samar, L3 nilai bertentangan atau dua langkah), sama seperti kata *Tingkat* di 08:
   - **D2:** nomor 1, 3, 5, 7 L1; nomor 2 dan 4 L2; nomor 6 (niat baik berhadapan dengan izin teman) dan 8 (pesan berantai dua langkah) L3.
   - **D3:** butir 1–4 (adegan G1) L1; butir 5 (atribusi) dan 7 (izin) L2; butir 6 (keliru tapi populer) dan 8 (benar tapi kasar) L3.
   - Sebarannya L1 empat, L2 dua, L3 dua di setiap bagian.
2. Tidak dipilih: nomor 4 D2 dinaikkan ke L3 (kesetiakawanan sebagai nilai yang bertentangan), atau level dibiarkan usulan sampai label ahli kembali.

Akibatnya: 15 mendapat bagian *Level pengembang* (alasan per nomor); 13 (tabel D2 dan D3) memakai level ini; skrip `berkas-validator.php` membaca level dari tabel itu dan memeriksanya (69 pemeriksaan lolos); berkas kunci B.5a tidak lagi berisi rentang; 21 (temuan no. 2) dan 00 diperbarui. Kelima sesi paralel tahap 2 diarsipkan atas permintaan pengembang.

**Keputusan pengembang atas lima hal yang tersisa dari penyelarasan, dan pembaruan instrumen** (cabang `sky/inspiring-ritchie-nex4qi`, sesi koordinator, PR #268) · Prototipe I, belum merevisi produk

Keputusan pengembang (27 Oktober 2026):

1. **Perkiraan waktu B.7 3–5 jam** (622 sel kode) disetujui.
2. **T1 tidak diuji baca oleh orang tua** di luar sampel sebelum permohonan etik dikirim.
3. **Pesan dukungan (23 → T12) untuk semua siswa di P5**, juga yang tanpa izin butir risiko daring, di titik yang sama.
4. **Selebaran tempat bercerita (23 → T13) dibagikan kepada semua anak di kelas di akhir P5**, bersama tanda terima kasih.
5. **Pengodean buta B.7 oleh Ahli Literasi Digital 2 diterima apa adanya**: bank berkode tetap ada di kiriman awalnya, jadi versi buta B.7 tidak sepenuhnya buta bagi validator itu. Kiriman tidak diubah.

Akibatnya: 16, 20, 22, 23, 24, dan 01 (perubahan sistem no. 23) diperbarui.

Pembaruan instrumen (atas permintaan pengembang, dari *Akibat ke dokumen lain* di 20 dan 21): instrumen ahli materi v2.1 (A.3 dan A.4: penilai B.5a, paket B.5a lebih dulu, pengarahan B.5a ± 15 menit, B.7 3–5 jam; B.5a: ID butir dan ID bahan netral yang diacak; D.3: nama berkas lembar kerja dan urutan kirim), pengamat budaya v2.1 (D.3 no. 11), dan ahli media v1.1 (D.3 no. 9: lembar kerja hanya B.8). Ketiganya memakai kalimat honorarium "sesuai ketentuan yang berlaku" dan menyebut uji keterbacaan empat anak ditambah satu. Nama topik B.6 dan benda sasaran B.6b di instrumen dipertahankan karena isinya sama dengan 08 (pilihan sesi koordinator; dicatat di D.4 pengamat budaya). Perubahan dicatat di D.4 setiap instrumen, PDF dibuat ulang (ahli materi kini 43 halaman), dan lembar kerja validator dibuat ulang (68 pemeriksaan lolos). Status ketiga instrumen tetap draf, menunggu persetujuan pengembang.

Masih terbuka: satu level pengembang untuk setiap butir tes D2 dan D3 di 13 dan 15 (sekarang rentang L1–L2 atau L2–L3), yang dibutuhkan analisis C.4 ahli materi.

**Penyelarasan dokumen tahap 2 (20–24) dan keputusan pengembang** (cabang `sky/inspiring-ritchie-nex4qi`, sesi koordinator, PR #268) · Prototipe I, belum merevisi produk

Kelima sesi paralel selesai pada hari yang sama, masing-masing dengan jawaban pengembang sendiri. Sesi koordinator memeriksa kelima dokumen bersama-sama dan menemukan satu keputusan yang bertentangan, beberapa keputusan yang belum terbawa ke dokumen lain, dan kode yang bertabrakan.

Keputusan pengembang (27 Oktober 2026):

1. **Batas permintaan hapus data: sampai studi ditutup**, mengikuti 22. Nama, L7, dan formulir dimusnahkan saat studi ditutup; hasil gabungan yang sudah terbit tidak bisa ditarik. Aturan di 23 ("sampai analisis utama dimulai") diganti. Tidak dipilih: gabungan dua aturan (permintaan sesudah analisis utama dimulai tetap menghapus data mentah), dan aturan 23.
2. **Formulir mundur T10 tetap tiga pilihan:** A berhenti dan data tetap dipakai, B berhenti dan data dihapus, C tetap ikut tetapi menarik rekaman atau jawaban butir risiko. L7 di 02 mendapat dua status, `Mengundurkan diri (data tetap)` dan `Mengundurkan diri (hapus data)`; mulai `kamus-1.1`, status `data tetap` dihitung `consent_ok = 1` untuk data sampai tanggal mundur. Hapus sebagian menjadi perubahan sistem no. 23. Tidak dipilih: hanya B dan C (seperti L7 lama), atau hanya A dan B.
3. **Anak uji keterbacaan mendapat tanda terima kasih kecil yang sama** dengan anak di kelas, setelah sesi terakhirnya, tidak disebut sebagai hadiah. Tidak dipilih: ditambah kudapan setiap sesi, atau tanpa hadiah.
4. **Akun `pengamat`, `pewawancara`, dan `ketua` disetujui** (sudah dicatat di entri koordinator di bawah).
5. **Keempat PR dokumen 21–24 digabung lewat satu PR koordinator** (#268), supaya konflik di 00, 04, dan PROGRESS diselesaikan sekali dan dokumen diselaraskan sebelum masuk `main`. Tidak dipilih: menggabung satu per satu.

Diselaraskan sesi koordinator tanpa keputusan baru:

- **Kode:** kegiatan dengan anak di 22 menjadi `KG1`–`KG5` (dulu `K1`–`K5`, bertabrakan dengan kode anak `K01`–`K05` di 24); kode sesi uji keterbacaan menjadi `K01-1` (dulu `K01-S1`, terbaca seperti kode sekolah). Label `T1`–`T13` di 23 dan `PB-*` di 24 tetap label di dalam dokumennya.
- **Keputusan 22 dibawa ke 23:** berbagi data berkode atas persetujuan komite, masa simpan, batas kerahasiaan, kontak komite, dan tiga teks baru untuk anak (T11 layar izin ulang sebelum butir risiko, T12 pesan dukungan, T13 selebaran tempat bercerita).
- **Keputusan 24 dibawa ke 22 dan 23:** empat anak ditambah satu, empat sesi ± 60 menit di sekolah anak, butir risiko daring diparafrasekan saja, dan sekolah uji keterbacaan tidak ikut sampel nanti. Nama dan tingkat membaca anak baru sampai ke peneliti setelah izin orang tua kembali.
- **Keputusan 21 dibawa ke 20:** dua penilai B.5a menerima paket B.5a saja lebih dulu; nama berkas dari 21; perkiraan waktu B.7 dinaikkan.
- **Keputusan 23 dibawa ke 22:** amplop H-14 sampai H-10, pengingat H-5, batas kembali H-3; ringkasan hasil lewat sekolah; pengingat persetujuan di P2–P5.
- **Janji "nama hanya di dua tempat"** di 22 dan 23 diganti aturan yang benar: nama tidak pernah ada di data penelitian; nama hanya di akun (admin) dan di berkas izin, mundur, dan rujukan yang disimpan terkunci terpisah.
- **Dokumen bersama:** 01 (daftar kerja no. 3, 14, 15, 23, pertanyaan izin etik, perubahan sistem no. 23 baru), 02 (L0, L5, L7; bagian *Perubahan setelah persetujuan*), 03 (ketua peneliti, *Kegiatan dengan anak*, ID netral B.5a, *Uji keterbacaan*), 06, 13–18 (tautan ke 24 dan 22; kalimat 16 "namamu tidak ditulis di mana pun" diganti "Jawabanmu disimpan dengan kode, bukan dengan namamu"), dan README `dokumen-pdf/`.

Yang menyusul di PR koordinator berikutnya: perubahan HTML ketiga instrumen dari 21 (ID netral di B.5a, nama berkas dan urutan kirim di D.3, waktu B.7), level tunggal untuk butir tes D2 dan D3 di 13 dan 15, dan nama topik B.6 dan benda B.6b yang berbeda dengan 08.

Menunggu konfirmasi pengembang: perkiraan waktu B.7 3–5 jam (dulu 2–3 jam) di 20; cara menjaga pengodean buta B.7 oleh Ahli Literasi Digital 2, yang menerima bank berkode di kiriman awal; uji baca T1 oleh dua–tiga orang tua di luar sampel (usulan 23); dan dua usulan di 23, yaitu T12 juga untuk anak tanpa izin butir risiko, dan T13 dibagikan ke semua anak di akhir P5 supaya tidak ada yang tampak berbeda. Kamus data `kamus-1.0` tidak diubah; `consent_ok` untuk `data tetap` dan aturan letak nama masuk `kamus-1.1`.

**Draf protokol uji keterbacaan, daftar kerja no. 23** (cabang `r10-uji-keterbacaan`, sesi paralel tahap 2; Prototipe I)

Status: **draf; keempat pertanyaan diputuskan pengembang di hari yang sama** (lihat di bawah). Dokumen baru [24_UJI_KETERBACAAN.md](24_UJI_KETERBACAAN.md) berisi protokol uji keterbacaan dengan anak kelas 5, yang dijalankan setelah izin etik terbit dan setelah bahan direvisi dari validasi ahli. Entri ini tidak merevisi prototipe; revisi dari hasil uji nanti dicatat sebagai "Prototipe I, revisi dari uji keterbacaan".

Yang ditulis: tujuan dan arti "terbaca"; syarat dan cara memilih anak (guru hanya mengelompokkan kelancaran membaca, tanpa nilai); daftar bahan dari 08 dan 12–18, dengan alasan bahan yang tidak diuji (Temukan Benda, keterangan Susun Gambar, cerita penghubung yang dibacakan, panel peneliti); pembagian bahan; perangkat (laptop, salinan layar statis dari sumber mock-up 18, tanpa ponsel, tanpa suara); empat sesi per anak dengan langkah baca sendiri, parafrase, pancingan baku `PB-1`–`PB-9`, menandai kata, dan kartu kata; peran (P3 pemandu, P2 pencatat, P1 persiapan, ketua peneliti penanggung jawab); lembar LK1–LK3 dan kode kesulitan `KB-*`; aturan keputusan dan yang kembali ke ahli; cara waktu yang diukur dibandingkan dengan 06, 13, dan 15; pengelolaan data; yang dibutuhkan dari 20, 22, dan 23.

Pilihan yang diambil saat menulis:
1. **Empat anak di putaran pertama, satu anak lagi untuk putaran kedua** bahan yang direvisi.
2. **Inti untuk semua anak** (satu bentuk tes lengkap, angket, skala sikap, kartu jangkar, kartu kata, salinan layar), **bank dibagi enam blok** wilayah × variasi, setiap blok dibaca dua anak.
3. **Parafrase sesudah membaca, bukan berpikir lantang selama membaca**, supaya waktu baca bisa dipakai; berpikir lantang tetap di uji coba tahap 4 (L5).
4. **Ambang keputusan dibedakan:** bacaan D1 hanya direvisi untuk kata di luar sasaran butir, pokok soal, pilihan, dan petunjuk; petunjuk direvisi kalau satu anak saja tidak paham; butir negatif skala direvisi kalau satu anak menjawab berlawanan.
5. **Batas waktu tes tidak diubah dari uji ini**; angka waktunya hanya memberi catatan ke 06, 13, dan 15 sampai uji coba.
6. **Butir risiko daring diuji dengan parafrase saja**, tanpa jawaban tentang diri sendiri, dan hanya dengan izin terpisah.
7. Ambang berhenti lama (± 10 detik) dan lama membaca sebelum jeda ditandai `needs_verification`.

**Keputusan pengembang (27 Oktober 2026, satu putaran, semuanya usulan):** (1) inti untuk semua anak dan bank dibagi enam blok, empat sesi per anak; (2) empat anak, ditambah satu anak untuk putaran kedua bahan yang direvisi; (3) anak dari sekolah di wilayah Kedu yang tidak masuk sampel dan bukan sekolah uji coba; (4) butir risiko daring diuji dengan parafrase saja, dengan izin terpisah. Dicatat di [24, Keputusan pengembang](24_UJI_KETERBACAAN.md#keputusan-pengembang). Perubahan yang dibutuhkan di 01, 03, 06, 13–18, 20, 22, dan 23 didaftar di [24, Akibat ke dokumen lain](24_UJI_KETERBACAAN.md#akibat-ke-dokumen-lain) dan dibawa sesi koordinator.

**Surat informasi dan persetujuan ditulis (draf)** (cabang `r10-surat-izin`, daftar kerja no. 15, [23](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md); Prototipe I, dokumen tahap 2)

Status: draf; empat pertanyaan pengembang dijawab (di bawah). Yang ditulis, semua sebagai blok siap cetak dengan tempat kosong bertanda:
- T1–T2 surat informasi dan formulir orang tua, dengan kotak Ya/Tidak terpisah untuk ikut, rekaman suara, dan butir risiko daring. Semua isi wajib surat di 02 → L7 ada di T1–T2 (tabel cek di 23).
- T3 naskah persetujuan anak di pembukaan P1, T4 usulan teks centang persetujuan di halaman daftar untuk mode penelitian (file bahasa tidak diubah), T5 pengingat singkat di P2–P5 dan sebelum wawancara L5.
- T6 surat kepala sekolah dengan permintaan data L8, T7 lembar guru kelas.
- T8 varian uji keterbacaan, T9 varian kelas uji coba (awalan `U`, berpikir lantang), T10 formulir pengunduran diri dan hapus data.
- Tabel pembagian (siapa, kapan, disimpan di mana, mengikuti aturan simpan L7), catatan keterbacaan per teks.

Pilihan yang diambil saat menulis (rinciannya di [23 → Pilihan yang diambil saat menulis](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#pilihan-yang-diambil-saat-menulis)): formulir yang tidak kembali dibaca tidak mengizinkan; kotak tambahan yang kosong dibaca Tidak; amplop tertutup H-14 sampai H-10 dengan batas kembali H-3; tanpa foto dan video; persetujuan anak diulang singkat di P2–P5; anak yang menolak di P1 dicatat `Tanpa izin`; T10 memberi pilihan berhenti tanpa menghapus data, yang butuh penyesuaian `consent_ok`.

Keputusan pengembang (wawancara singkat, 27 Oktober 2026; semuanya usulan, rinciannya di [23 → Keputusan pengembang](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#keputusan-pengembang)):
1. **Permintaan hapus data boleh diajukan sampai analisis utama dimulai.** *(Diganti pada hari yang sama: sampai studi ditutup, lihat entri penyelarasan di atas.)* Sesudahnya data anak tetap dihapus, tetapi angka gabungan yang sudah dihitung atau terbit tidak berubah. Tidak dipilih: kapan saja tanpa batas.
2. **Anak uji keterbacaan ikut membaca butir risiko daring, dengan centang terpisah**, dan hanya menilai kalimatnya. Tidak dipilih: hanya telaah ahli.
3. **Tanpa centang ringkasan hasil untuk orang tua;** ringkasan angka gabungan ke sekolah, dan orang tua memintanya lewat sekolah. Tidak dipilih: centang keempat.
4. **Kenang-kenangan kecil yang sama untuk seluruh kelas**, termasuk yang tidak ikut, di akhir P5, tidak disebut sebagai imbalan. Tidak dipilih: tanpa kenang-kenangan, atau hanya untuk peserta.

Akibat ke 01, 02, 11, 22, 24, dan 03 dicatat di [23 → Akibat ke dokumen lain](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#akibat-ke-dokumen-lain) dan dibawa sesi koordinator setelah PR digabung. Entri ini tidak merevisi bank, instrumen, atau sistem.

**Draf permohonan izin etik: [22_PERMOHONAN_IZIN_ETIK.md](22_PERMOHONAN_IZIN_ETIK.md)** (cabang `r10-izin-etik`, sesi paralel tahap 2; daftar kerja no. 15) · **Status: draf; keputusan pengembang atas empat pertanyaannya diambil hari yang sama**

Prototipe I: dokumen ini tidak merevisi bank, instrumen, atau mock-up; ia menjelaskan bagaimana Prototipe I sampai III dan data utama akan dijalankan dengan anak.

1. **Yang ditulis.** Protokol lengkap untuk *Lembaga Komite Etik*: identitas dan tim (nama samaran dan tempat kosong), ringkasan, nilai sosial, tujuan, desain Plomp dengan Prototipe I–III, lima kegiatan dengan anak (K1 uji keterbacaan, K2 berpikir lantang, K3 uji coba satu kelas `U`, K4 wawancara retrospektif, K5 data utama ± 360 siswa, ± 13 kelas, target 300), peserta, rekrutmen, lapis persetujuan, prosedur per pertemuan dan beban anak (paling lama ± 10 jam dalam lima pertemuan, dari 06 dan 13), risiko dan mitigasi, prosedur rujukan butir risiko daring, manfaat, kerahasiaan dan pengelolaan data, kompensasi, konflik kepentingan, KTD, perubahan protokol, lampiran, dan pemetaan ke tujuh standar KEPPKN dan ke bagian formulir umum.
2. **Pilihan yang dibuat saat menulis** (usulan, bisa diubah pengembang):
   - Penanda rujukan mengikuti keputusan 26 Oktober 2026: setiap anak yang menjawab *Pernah* di ang-21. Sapaan pribadi oleh ketua peneliti paling lambat hari sekolah berikutnya, didahulukan untuk *Beberapa kali atau lebih* atau yang tidak bercerita kepada siapa pun. Orang tua hanya dihubungi dengan persetujuan anak, kecuali ada bahaya serius. Penanda dan catatan rujukan di luar data penelitian, hanya terlihat ketua peneliti.
   - Batas kerahasiaan disebut jujur di surat orang tua dan kalimat anak sebelum butir risiko (akibat ke 02, 16, dan 23).
   - Penolakan anak didahulukan dari izin orang tua; persetujuan ulang singkat sebelum wawancara dan butir risiko; wawancara di tempat yang terlihat.
   - Usulan satu pertemuan per hari; data penelitian disimpan 5 tahun sesuai `retention_days` bawaan sistem.
   - Rujukan luar yang belum dicek ditandai `needs_verification`: rumusan dan versi pedoman KEPPKN, UU No. 27 Tahun 2022 (data anak, pemberitahuan kebocoran), layanan SAPA 129 dan UPTD PPA, batas waktu laporan KTD dan aturan amandemen komite.
3. **Keputusan pengembang atas empat pertanyaan draf (27 Oktober 2026, wawancara satu putaran):**
   - **Nama dan L7 dimusnahkan saat studi ditutup** (usulan draf: 12 bulan setelah P5 terakhir). Permintaan hapus diterima sampai studi ditutup; data berkode disimpan 5 tahun.
   - **Data per anak berkode dibagikan atas permintaan, dengan persetujuan komite** (usulan draf: tidak dibagikan). Tidak ada unggahan publik; sebelum dibagikan kode diganti kode acak baru, kode sekolah dan rombel dihapus, dan jawaban butir risiko serta transkrip tidak ikut. Surat orang tua (23) harus menyebutnya.
   - **Tanda terima kasih kecil yang sama untuk semua anak di kelas**, termasuk yang tidak ikut, plus kudapan di P1 dan P5; tanpa uang (sesuai usulan).
   - **Satu permohonan untuk K1–K5**, revisi instrumen dilaporkan sebagai perubahan protokol (sesuai usulan).

   Status 22 tetap draf: diajukan setelah dokumen tahap 1 yang dilampirkan disetujui dan formulir komite sebenarnya diketahui.
4. **Disesuaikan dengan 20 (PR #263, catatan sesi koordinator):** bahan untuk anak yang dilampirkan adalah versi yang dibekukan untuk validasi ahli, dan setiap revisi Prototipe I (atau sesudahnya) yang mengubah bahan anak diajukan ke *Lembaga Komite Etik* sebagai amandemen protokol. Nama samaran akun staf `pengamat`, `pewawancara`, dan `ketua` tidak lagi ditandai usulan.

Akibat ke dokumen lain (01, 02, 03, 16, 17, 23, 24) dicatat di 22, bagian *Akibat ke dokumen lain*, dan dibawa sesi koordinator setelah digabung.

**Berkas butir untuk validator: draf 21 dan skrip pembuatnya** (cabang `r10-berkas-validator`, sesi paralel tahap 2; Prototipe I, belum merevisi produk) · **Status: draf; keputusan pengembang diambil 27 Oktober 2026**

1. **Yang ditulis.** [21_BERKAS_BUTIR_VALIDATOR.md](21_BERKAS_BUTIR_VALIDATOR.md) dan skrip `dokumen-pdf/src/berkas-validator.php`. Skrip membaca 03, 08, 14–18 dan tabel ketiga instrumen (tanpa mengubahnya), lalu menulis berkas di `dokumen-pdf/berkas-validator/`:
   - daftar buta B.5a (lembar kerja dan buklet PDF) dan kuncinya di `khusus-peneliti/`;
   - daftar B.5b;
   - lembar kerja B.6–B.9 ahli materi, dan B.7 versi pengodean buta;
   - lembar kerja B.5–B.8 pengamat budaya;
   - lembar kerja B.8 ahli media;
   - laporan pemeriksaan `LAPORAN.md`.

   Jumlahnya: 180 butir B.5a dan B.5b (116 bank, 64 tes), 24 skenario, 622 sel kode, 12 pernyataan sikap, 38 pasangan A–B, 20 butir angket, 46 objek budaya, 24 petunjuk, 12 butir PBK, 19 fakta, dan 27 gambar mock-up. Semua 68 pemeriksaan lolos.
2. **Pilihan saat menulis**, semuanya pilihan yang disarankan di pertanyaan 21:
   - ID netral `BT-…` dan `BH-…`, dengan bahan diacak dengan benih tetap;
   - kunci jawaban dicantumkan di daftar buta, kode kesalahan dan label tidak;
   - bagian ketuk dinamai "Bagian 1, 2, …";
   - B.7 berisi semua 622 sel dengan kolom "Asal kode";
   - level pengembang *Periksa Unggahan* dan Skenario Digital diturunkan dari baris *Tingkat* 08 per wilayah.
3. **Temuan untuk pengembang** (rinciannya di 21):
   - paket awal di D.3 ahli materi membocorkan dimensi dan sub-aspek ke dua penilai B.5a lewat kode kesalahan dan beberapa bagian instrumen;
   - level pengembang butir tes D2 dan D3 berupa rentang;
   - B.7 lebih panjang dari perkiraan 2–3 jam;
   - nama topik B.6 dan nama benda B.6b berbeda kata dengan 08;
   - lembar kerja B.3–B.7 ahli media belum dibuat.
4. **Keputusan pengembang (27 Oktober 2026), keempatnya pilihan yang disarankan:**
   - daftar B.5a memakai ID netral yang diacak, dengan berkas kunci khusus peneliti;
   - dua penilai B.5a menerima paket B.5a saja dulu; instrumen lengkap, bank berkode, kisi-kisi, B.5b, dan lembar B.6–B.9 dikirim setelah B.5a kembali;
   - kunci jawaban dicantumkan di daftar buta;
   - B.7 mencakup semua 622 sel, dan perkiraan waktunya dinaikkan.

   Berkas sudah memakai pilihan ini, jadi tidak dibuat ulang. Urutan kirim dan waktu B.7 masuk ke 20 dan instrumen ahli materi lewat sesi koordinator.

Akibatnya: 00 (baris 21), `dokumen-pdf/README.md` (tabel *Isi* dan bagian *Membuat ulang berkas validator*), dan `docs/PROGRESS.md` diperbarui. Perubahan untuk 01, 03, 08, 13, 15, 20, dan ketiga instrumen dicatat di 21 → *Akibat ke dokumen lain* untuk sesi koordinator.

**Draf paket validasi ahli: [20_PAKET_VALIDASI.md](20_PAKET_VALIDASI.md)** (cabang `r10-paket-validasi`, sesi paralel tahap 2; daftar kerja no. 3 dan 14) · Prototipe I · Status: draf; empat pertanyaan dijawab pengembang pada hari yang sama (lihat akhir entri)

Yang ditulis: prasyarat kirim, tabel siapa menerima apa per nama samaran (lembar dari matriks penugasan, nomor berkas dari D.3 ketiga instrumen, perkiraan jam, putaran kirim), jadwal relatif dua belas minggu dengan usulan tanggal mulai 9 November 2026, tiga surat permohonan, surat lanjutan setelah B.5a, pengingat dan surat putaran 2, usulan akun siswa uji validator, cara pengembalian dan pencatatan, templat matriks revisi induk, aturan kapan butir kembali ke validator, cara mencatat revisi di 04, dan daftar periksa validasi untuk G2.

Pilihan yang diambil saat menulis (semua bisa diubah pengembang):

1. **Penilai B.5a dalam draf: Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1**, mengikuti saran IM A.3. Ditanyakan ke pengembang.
2. **Label pengembang dan kisi-kisi ditahan untuk kelima ahli materi** sampai B.5a dikumpulkan, bukan hanya untuk dua penilainya, sesuai IM D.3 no. 4–6 dan supaya tidak ada salinan berlabel di panel. Lembar yang sudah selesai di putaran awal diminta tidak diubah setelah label dibuka.
3. **Akun validator adalah akun siswa uji**, bukan akun staf: `uji.bahasa1`–`2`, `uji.digital1`–`2`, `uji.ukur`, `uji.budaya1`–`3`, `uji.media1`–`3`, dan dua cadangan, dibuat `admin`. Titik dipakai karena aturan nama pengguna siswa tidak mengizinkan tanda hubung.
4. **Daftar padanan nama samaran ↔ nama asli dan surat pernyataan disimpan di luar repo**, dipegang ketua peneliti; lembar kertas diketik dua kali oleh dua orang, lembar kerja digital dicek 10% baris acak.
5. **Satu matriks revisi induk** untuk ketiga instrumen; D.2 tiap instrumen adalah potongannya untuk putaran 2. Matriks induk menjadi dokumen baru saat hasil pertama kembali.
6. **Media langkah 2 belum punya instrumen**; disusun di tahap 3 untuk G3.

**Keputusan pengembang atas pertanyaan di 20** (27 Oktober 2026):

1. **Penilai B.5a: Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1** (sesuai usulan).
2. **Honorarium di surat ditulis "sesuai ketentuan yang berlaku", tanpa angka** (bukan usulan draf, yang menyebut besaran per panel). Besarannya dicatat ketua peneliti di luar repo.
3. **Paket dikirim digital**: folder bersama yang dibatasi per validator, surat pernyataan dipindai, kertas hanya atas permintaan (sesuai usulan).
4. **Ahli media langkah 1 tidak menunggu L8**: dikirim bersama panel lain begitu instrumen disetujui, dengan daftar jenis perangkat sebagai pengganti L8 (sesuai usulan).

20 diperbarui: bagian *Pertanyaan untuk pengembang* menjadi *Keputusan pengembang*, dan teks surat memakai kalimat honorarium yang baru. Perubahan yang dibutuhkan di 01, 03, 07, README `dokumen-pdf/`, 22, dan 24 ada di bagian *Akibat ke dokumen lain* di 20, dibawa sesi koordinator setelah digabung. Entri ini tidak merevisi isi prototipe; ia menyiapkan validasi Prototipe I.

**Keputusan pengembang: daftar kerja no. 12 dan 13, dan dimulainya dokumen tahap 2** (cabang `sky/inspiring-ritchie-nex4qi`, sesi koordinator)

1. **Daftar kode sekolah penelitian (no. 12) belum diputuskan; tahap 2 tetap dimulai.** Kode `S01`, `S02`, … di semua dokumen tetap contoh, dan tempat kosongnya di 02 tetap ditandai. Daftar ini baru dibutuhkan sebelum sekolah direkrut dan L8 diisi, jadi tidak menahan dokumen validasi dan izin etik.
2. **Pemegang akun admin dan akun peneliti (no. 13) sementara memakai nama samaran sesuai perannya:** akun admin `admin`, akun peneliti untuk koordinator sesi (P1) `koordinator`, dan seterusnya. `pengamat` (P2), `pewawancara` (P3), dan `ketua` (ketua peneliti) diusulkan sesi koordinator untuk "dan seterusnya" dan **disetujui pengembang pada hari yang sama**; akunnya dibuat kalau perannya butuh akun ([03 → Akun staf](03_KODE_DAN_ARTI.md#akun-staf)). Siapa orangnya ditetapkan nanti dan dicatat ketua peneliti di luar repo, sama seperti nama asli validator.
3. **Dokumen tahap 2 ditulis paralel di beberapa sesi.** Sesi koordinator membaginya menjadi lima dokumen, satu sesi dan satu cabang masing-masing:

   | Dokumen | Cabang | Isi |
   | --- | --- | --- |
   | `20_PAKET_VALIDASI.md` | `r10-paket-validasi` | Siapa menerima apa dan kapan, surat permohonan, jadwal, pengembalian, matriks revisi, daftar periksa validasi di G2 |
   | `21_BERKAS_BUTIR_VALIDATOR.md` | `r10-berkas-validator` | Daftar butir buta B.5a, daftar B.5b, lembar kerja per lembar instrumen |
   | `22_PERMOHONAN_IZIN_ETIK.md` | `r10-izin-etik` | Protokol dan permohonan izin etik |
   | `23_SURAT_INFORMASI_DAN_PERSETUJUAN.md` | `r10-surat-izin` | Surat informasi dan persetujuan untuk orang tua, anak, sekolah, dan guru |
   | `24_UJI_KETERBACAAN.md` | `r10-uji-keterbacaan` | Protokol uji keterbacaan dengan 3–5 anak kelas 5 |

   Uji keterbacaan menjadi daftar kerja baru no. 23 di 01, karena sudah ada di peta jalan (tahap 2, Prototipe I) tetapi belum punya baris sendiri.

Gerbang G1 belum resmi terpenuhi: no. 12 masih terbuka, dan instrumen ahli materi v2.1, pengamat budaya v2.1, dan ahli media v1.1 masih menunggu persetujuan. Pengembang memilih tetap memulai tahap 2. Jadi dokumen tahap 2 boleh ditulis sekarang, tetapi yang dikirim ke luar (paket ke validator, permohonan ke *Lembaga Komite Etik*) tetap menunggu persetujuan dokumen yang dibutuhkannya.

Akibatnya: 00 (tahap dokumen 2, baris 02, baris dokumen 20–24, "sebelas validator" menggantikan "tujuh validator"), 01 (daftar kerja no. 3, 12, 13, 14, 15, dan no. 23 baru), 02 (tempat kosong akun P1), dan 03 (bagian *Akun staf*) diperbarui. Entri ini tidak merevisi prototipe.

## 26 Oktober 2026

**Instrumen validasi ahli ditulis ulang: ahli materi v2.1, pengamat budaya v2.1, ahli media v1.1** (cabang `pbb/dazzling-feynman-r7tljf`, [07 → Langkah berikutnya](07_TELAAH_INSTRUMEN_VALIDASI.md#langkah-berikutnya) no. 2 dan 4)

Pengembang menyerahkan draf r10 v2.0 dan media v1.0 (23 Oktober 2026) dan meminta:

1. **r10 dipecah menjadi dua dokumen**, satu per bidang validator: ahli materi dan pengamat budaya. Masing-masing punya panduan, analisis, dan lampiran sendiri.
2. **PDF penting disimpan di subfolder khusus** [dokumen-pdf/](dokumen-pdf/README.md), dengan sumber HTML di `src/` dan draf asli di `arsip/`.

Keputusan pengembang saat penulisan (wawancara singkat, 26 Oktober 2026):

3. **Panel pengamat budaya tiga orang** (Ahli Budaya Kedu 1–3), idealnya satu orang per wilayah. Sebelumnya hanya ada satu nama samaran, sehingga I-CVI dan Aiken's V tidak bisa dihitung untuk instrumen budaya.
4. **Panel ahli media tiga orang** (Ahli Media Pembelajaran 1–3): teknologi pendidikan atau media pembelajaran, desain visual atau UX, dan rekayasa perangkat lunak atau pengembangan game.

Jadi validatornya sebelas orang. Akibatnya, dicatat di instrumen dan di 03, 01, 13, 16, dan 17:

- Aturan Lynn berlaku untuk panel tiga orang: butir lolos hanya bila ketiganya memberi 3 atau 4.
- Butir teknis H3 di instrumen media menjadi daftar periksa lulus/tidak, karena hanya satu validator berlatar rekayasa perangkat lunak.
- Relevansi skala sikap (B.8) tetap dinilai empat orang seperti keputusan sebelumnya, dengan Ahli Budaya Kedu 1 sebagai pengamat budaya; Ahli Budaya Kedu 2 dan 3 hanya mengisi kolom "contoh budaya tepat" untuk wilayahnya.

Isi instrumen mengikuti semua perubahan di 07. Yang berbeda dari tulisan di 07, dan pilihan yang diambil saat menulis, dicatat di [07 → Instrumen versi 2.1 dan 1.1](07_TELAAH_INSTRUMEN_VALIDASI.md#instrumen-versi-21-dan-11). Yang terpenting:

- Telaah *Periksa Unggahan* mencakup 24 petunjuk, bukan 12.
- INF-BUD hanya untuk PBK.
- Butir PBK dinilai relevansinya oleh pengamat budaya.
- Ponsel dihapus dari matriks perangkat ahli media.
- Letak kompleks Candi Arjuna dan tahun kebun teh Tambi ditanyakan ke pengamat budaya.

Instrumen ini dipakai untuk menilai prototipe I (tahap 1–2, berbasis kertas dan mock-up), sesuai penomoran prototipe di entri berikutnya.

Sekalian dikerjakan dari entri *empat usulan dokumen model penelitian* di bawah, karena berkasnya dipegang cabang ini:
- "L0–L7" di 00 diganti "L0–L8", dan baris 02 di 00 ditambah "L8 draf".
- L8 Profil sekolah (bagian perangkat dan internet) masuk paket berkas ahli media (D.3 media v1.1).

`gambar/peta-jalan.svg`, `gambar/lapis-data.svg`, dan variabel L8 di `kamus-1.1` masih menyusul.

Status: draf, menunggu persetujuan pengembang. Kotak status di sampul dihapus sebelum dicetak untuk validator.

**Keputusan pengembang: empat usulan dokumen model penelitian** (cabang `r10-usulan-model-penelitian`, usulan yang belum diputuskan di entri *model penelitian dan pengembangan, dan aturan I-CVI* di bawah; alasannya di [dokumen pendukung peneliti](dokumen-pendukung-peneliti/README.md), bagian 7–9)

1. **Tes awal–akhir terutama bukti validitas kriteria; uji bedanya eksploratif.**
   - Kegunaan utama tes adalah korelasi skor tes per bagian dengan kemampuan Rasch game per dimensi.
   - Uji berpasangan, ukuran efek, dan model campuran tetap dijalankan dan dilaporkan lengkap, sebagai "perubahan" yang **eksploratif**, bukan pengaruh game dan bukan jawaban pertanyaan penelitian utama.
   - Alasannya: desain satu kelompok dengan tes awal dan akhir adalah desain pra-eksperimen (Campbell & Stanley, 1963).
   - Tidak dipilih: uji beda sebagai temuan utama dengan paragraf keterbatasan, dan menghapus uji beda.
2. **Kepraktisan: log, L1, L3, L4, dan bintang 1–5 di kritik dan saran sudah cukup untuk sekarang.**
   - Tidak ada butir baru untuk siswa sekarang, jadi 17 dan 11 tidak berubah.
   - Perlu tidaknya pertanyaan kepraktisan tambahan diputuskan setelah uji coba tahap 4. Bahan kualitatifnya dari berpikir lantang (L5).
   - Tidak dipilih: 2–3 butir kepraktisan sekarang, karena perlu validasi ahli di tahap 2 dan menambah beban P5.
3. **Penelitian pendahuluan ditulis sebagai fase tersendiri, ditambah lembar konteks sekolah.**
   - Bahannya dari 01, 05, 10, dan game kelas 5 yang ada sekarang (daftar kerja no. 22 baru di 01).
   - Data konteks sekolah sasaran dari lembar baru **L8 Profil sekolah** di [02](02_LEMBAR_LAPANGAN.md#l8-profil-sekolah), satu per sekolah, diisi P1 saat sekolah setuju ikut. Isinya fakta sekolah (letak, rombel kelas 5, perangkat, internet, listrik, pengalaman ANBK, pelajaran digital), tanpa nama guru atau siswa.
   - L0 tetap daftar periksa persiapan, dan angket akses digital (16) tetap sumber data akses tiap siswa. Keduanya tidak diubah.
   - Pengembang memilih pilihan ini, bukan usulan awal (fase tersendiri tanpa lembar baru, konteks dari data sekolah terbuka dan ringkasan angket). Melebur ke tahap 1 tidak dipilih.
4. **Prototipe diberi nomor I–III, mengikuti tabel bagian 7 dokumen pendukung.**
   - Prototipe I: tahap 1–2, berbasis kertas dan mock-up.
   - Prototipe II: tahap 3, instalasi uji.
   - Prototipe III: tahap 4, uji coba.
   - Produk final: tahap 5.
   - Mulai sekarang, setiap entri di catatan ini yang merevisi produk menyebut prototipe yang direvisinya.
   - Tidak dipilih: hanya nama tahap.

Akibatnya:
- **13** (*Apa yang diukur*, *Analisis*, keputusan pengembang, rujukan Campbell & Stanley) diperbarui.
- **01** diperbarui:
  - tabel fase dan prototipe di *Peta jalan*;
  - daftar kerja no. 22;
  - empat pertanyaan terbuka yang dicentang;
  - baris uji beda di *Rencana analisis*;
  - "L0–L8" di pembuka.
- **02** mendapat L8 (draf).
- **Dokumen pendukung peneliti** diperbarui: HTML, PDF, dan README.

Yang menyusul, karena berkasnya sedang dipakai sesi lain atau belum waktunya:
- "L0–L7" di 00, `gambar/peta-jalan.svg`, dan `gambar/lapis-data.svg` diganti "L0–L8". Baris 02 di 00 ditambah "L8 draf".
- Paket validasi di 07 menyertakan L8 untuk dibaca Ahli Media Pembelajaran (bagian perangkat dan internet).
- Variabel L8 masuk `kamus-1.1`.

**Keputusan pengembang: baris penutup sebelum skala sikap** (sesi `r10-skala-sikap`, pemeriksaan cerita penutup di [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md#penutup-ending) terhadap [17](17_SKALA_SIKAP.md))

1. **`penutup-04` diubah.** "…saat membaca, saat memakai layar, dan saat bercerita tentang budaya Kedu" menjadi "…saat membaca, saat memakai layar, dan saat budaya Kedu tampil di layar" (EN: "…and when Kedu's culture appears on a screen"). Kalimat lama memperagakan butir `skp-07` ("Aku senang menceritakan budaya Kedu kepada teman") beberapa menit sebelum siswa menjawabnya; kalimat baru tetap mencakup tiga dimensi, dengan bagian ketiga merujuk D3. Tidak ada baris penutup lain yang memuji siswa; pujian Mbah Kedu di Balai Refleksi sudah dipindah ke sesudah skala.

Akibatnya: 12 diperbarui (baris dan keputusan no. 6). Pada hari yang sama, akibat skala sikap ke dokumen lain diselesaikan: 02 (susulan skala di L1 dan tabel pertemuan, aturan bantuan kata di L3, kalimat skala di surat izin L7), 18 (`mk-ss-05`, Balai Refleksi dengan kritik dan saran), dan 17 (teks siap tempel untuk permohonan izin etik).

**Keputusan pengembang: mock-up layar baru untuk ahli media langkah 1** (cabang `r10-mockup-media`, daftar kerja no. 20, [18](18_MOCKUP_LAYAR_BARU.md))

Putaran 1, sebelum menggambar:

1. **Mock-up memakai tampilan game sungguhan**: halaman HTML statis dengan CSS, huruf, dan gambar UI game, digambar ke WebP 1280 × 720. Sumbernya di `gambar/mockup/src/` supaya bisa dipakai lagi di tahap 3.
2. **Skenario Digital digambar dalam lima keadaan**: situasi dengan ▶, tindakan dipilih, langkah alasan, umpan balik "kurang tepat" dengan kesempatan tersisa, dan kesempatan habis.
3. **Layar token digambar semua**: masukkan token, token keliru, di luar sesi, layar kecil ditolak, dan panel peneliti membuat sesi.
4. **Cerita penghubung dan kartu misi tantangan kelima ikut digambar**, satu gambar masing-masing.

Putaran 2, setelah melihat 23 gambar:

1. **Gambar disetujui apa adanya** untuk paket ahli media langkah 1. Gambar skala sikap (`mk-ss-01`) masih placeholder dan diperbarui setelah `17_SKALA_SIKAP.md` final.
2. **Penjelasan setelah kesempatan Skenario Digital habis ditulis sebagai satu kalimat tersendiri per skenario di 08** (24 kalimat), tidak memakai umpan balik pilihan tepat yang dipotong. Dikerjakan di sisi bank sebelum validasi ahli.
3. **Lapisan pemeriksaan area ketuk (`mk-pu-02`) ikut dikirim ke ahli media**, bertanda tidak tampil ke siswa.
4. **Draf salinan di 18** (token keliru, layar kecil, ketukan keliru di *Periksa Unggahan*, pengingat lima menit, kalimat kesempatan habis) **diterima sebagai teks awal**; kalimat akhirnya ditetapkan di dokumen implementasi setelah telaah ahli dan uji keterbacaan.

Akibatnya: 18 ditulis; 00, 01 (daftar kerja no. 20), dan 03 (kode `mk-*`) diperbarui. Atas permintaan pengembang di sesi yang sama, 24 kalimat *Penjelasan setelah kesempatan habis* ditulis di 08 (satu per skenario, dengan aturannya di *Cara membaca*: menyebut risikonya lalu mengapa tindakan tepat aman, tanpa kata "tepat", "salah", atau "gagal", tidak dibacakan, tidak diskor), dan `mk-sd-05` digambar ulang. Kalimatnya ikut validasi ahli bersama bank. Setelah 17 masuk `main` (PR #253), placeholder `mk-ss-01` diganti empat gambar `mk-ss-01`–`04` (petunjuk, contoh soal, `skp-03`, layar kirim) dengan format jawaban dari 17, jadi 18 kini memuat 26 gambar. Celah lain yang ditemukan dicatat di 18 → *Celah yang ditemukan di dokumen lain*.

**Keputusan pengembang: pertanyaan terbuka skala sikap** (sesi `r10-skala-sikap`, menjawab pertanyaan terbuka di [17](17_SKALA_SIKAP.md#pertanyaan-yang-masih-terbuka))

1. **Butir bahasa daerah (skp-10) adil** untuk siswa yang bahasa ibunya bukan bahasa Jawa, karena menyebut "bahasa daerah" umum. Butir tetap dipakai.
2. **skp-12 menyebut contoh acara setempat:** "Aku mau ikut acara budaya seperti merti desa dan kirab." Ahli Budaya Kedu memastikan keduanya dikenal di tiga wilayah.
3. **Bintang 1–5 di kritik dan saran menjadi data kepraktisan** (`feedback_rating`, masuk `kamus-1.1`).
4. **Siswa yang belum tuntas di akhir P5: data apa adanya.** Tidak ada jalur khusus ke Balai Refleksi; skalanya kosong dengan alasan `belum_tuntas`, dan data tantangannya dipakai seperti adanya. Susulan 7 hari hanya untuk siswa yang tidak hadir di P5.
5. **Korelasi sikap dengan PBK dan D3 diharapkan sedang: cukup kuat, tidak berlebihan** (usulan r ≈ 0,30–0,50; r ≥ 0,70 atau r < 0,10 dicatat sebagai temuan). Batas pastinya ditulis di pra-registrasi OSF di G4.

Akibatnya: [17](17_SKALA_SIKAP.md) diperbarui (butir, pelaksanaan, variabel `sikap_missing_reason` dan `feedback_rating`, akibat ke 01 dan dokumen pendukung peneliti).

**Keputusan pengembang: skala sikap menjaga warisan** (sesi `r10-skala-sikap`, dua putaran wawancara; semua usulan disetujui)

1. **Format jawaban: empat pilihan tanpa titik tengah** (*Tidak setuju · Kurang setuju · Setuju · Sangat setuju*), kata dengan titik emas yang makin besar, tanpa wajah.
2. **12 butir, empat indikator × tiga:** menghargai (SK1), ingin tahu (SK2), mau ikut merawat (SK3), bangga (SK4). Satu butir per indikator berarah negatif dan dibalik skornya, ditulis tanpa "tidak" atau "bukan". Semua butir berlatar dunia nyata, tanpa ruang digital, supaya terpisah dari D3 dan PBK.
3. **Tidak dibacakan:** hanya di layar, tanpa rekaman. P3 boleh menjelaskan satu kata, dicatat di L3.
4. **Kritik dan saran yang ada sekarang tetap**, sesudah skala; selama penelitian semua isiannya boleh kosong.
5. **Penilai ahli:** Ahli Budaya Kedu, Ahli Pengukuran, Ahli Bahasa Anak 1 dan 2 menilai relevansi (I-CVI, aturan Lynn: keempatnya harus memberi 3 atau 4); Ahli Bahasa Anak juga menelaah bahasa; Ahli Literasi Digital 1 dan 2 hanya memberi catatan tumpang tindih D3.
6. **Tidak hadir di P5:** susulan paling lama 7 hari, ditandai; sesudahnya kosong, tanpa perkiraan.
7. **Tampilan:** satu pernyataan per layar, boleh kembali sebelum kirim, urutan tetap, bilah kemajuan.

Akibatnya: [17](17_SKALA_SIKAP.md) ditulis; kode skala masuk 03. Penyesuaian 01 (perubahan sistem no. 21), 02 (L1, L3, L7), 06 (baris P5), 07 (lembar penilaian v2.1), 11 (`kamus-1.1`), 12, 13 (waktu P5), dan mock-up 18 tercatat di [17](17_SKALA_SIKAP.md#akibat-ke-dokumen-lain).

**Keputusan pengembang: lembar lapangan final, putaran 1** (sesi `r10-lembar-lapangan`, daftar kerja no. 11)

1. **Siswa tanpa izin orang tua tidak punya akun GELITA.** Mereka ikut kegiatan pengganti yang setara di ruang yang sama, disiapkan guru (misalnya membaca buku), jadi tidak ada data tentang mereka. Pilihan bermain lalu datanya dihapus, atau mode tamu tanpa penyimpanan, tidak dipakai.
2. **Di P1, baris dan kursi dimasukkan langsung setelah pendaftaran.** Siswa mendaftar lalu menunggu di layar pembuka modul tes. P1 mengetik kode peserta → baris dan kursi untuk seluruh kelas di panel peneliti (± 5 menit), bersama buklet. P3 mencocokkan kode dengan L2. Setelah itu tes awal dimulai bersama. Buklet tidak lagi dimasukkan "sesudah pertemuan P1".
3. **Wawancara retrospektif L5 di P2–P4, tiga siswa per pertemuan**, satu dari setiap kelompok nilai tes awal (rendah, sedang, tinggi). Setiap siswa diwawancarai paling banyak sekali, jadi ± 9 siswa per kelas. Wawancara di P5 tidak dijadwalkan.
4. **Siswa yang absen di pertemuan wilayah ikut sesi susulan dalam tiga hari**, seperti susulan tes. P1 membuka sesi lapangan susulan untuk wilayah itu dengan token sendiri (kode sesi berakhiran `-S`, misalnya `S03-A-P3-S`), diamati sedikitnya satu peneliti yang mengisi L1–L3. Sisa tantangan tetap dilanjutkan di susulan P5.

**Persetujuan:** pengembang menyetujui lembar L0–L7 final di [02](02_LEMBAR_LAPANGAN.md) apa adanya, termasuk pilihan yang menyertainya:
- L3 juga dipakai saat tes dan angket, menggantikan kolom catatan L6 di 13.
- L4 hanya selama waktu bermain.
- Kode penyimpangan baru DEV-TIM (peneliti kurang dari tiga).
- L2 satu baris per siswa, dengan kolom baris dan kursi di baris.
- Kelompok nilai tes awal untuk L5 adalah sepertiga terbawah, tengah, dan teratas di rombel.
- Contoh tanggal fiktif, sesudah perkiraan persetujuan etik.

Akibatnya: 02 final; 03 mendapat kode sesi susulan, DEV-TIM, dan arti L5 yang baru. Sesudah persetujuan, 01 (*Kerja lapangan*, perubahan sistem no. 11 dan no. 22), 06 (pembagian pertemuan, buklet), dan 13 (waktu P1 menjadi ± 124 menit, urutan P5, bantuan saat tes di L3) disesuaikan. Urutan P5 di catatan keputusan 12 dibiarkan sebagai riwayat. Yang tersisa: 11 di `kamus-1.1`. Yang perlu disesuaikan di dokumen lain dicatat di [02](02_LEMBAR_LAPANGAN.md#perubahan-dari-draf).

**Keputusan pengembang: label Bloom pengembang** (sesi `r10-label-bloom`, daftar kerja no. 4, telaah di [19](19_TELAAH_LABEL_BLOOM.md))

1. **Petunjuk Cari Objek diberi label menurut proses yang dilakukan siswa.** PBK: C1 kalau petunjuk menyebut nama bendanya (Temanggung), C2 kalau hanya mendeskripsikannya (Magelang, Wonosobo). Periksa Unggahan per jenis petunjuk: atribusi C2, izin C3, representasi C2 di Temanggung dan C4 di Magelang dan Wonosobo, menghargai tradisi C2 di Temanggung dan Magelang dan C4 di Wonosobo. Tidak ada C5 di Cari Objek.
2. **Benar atau Salah paling tinggi C4, kecuali dua butir C5.** Membandingkan isi dua teks dan menggolongkan fakta atau pendapat menjadi C2 (mengikuti Anderson & Krathwohl, bukan daftar kata kerja operasional yang menaruh "membedakan" di C4). Menilai kepercayaan sumber dari penanda yang diberikan, atau menolak alasan popularitas dan posisi, menjadi C4. C5 hanya untuk `wnb-3-a06` dan `wnb-3-b06`, yang memeriksa apakah simpulan didukung bukti.
3. **Skenario Digital diberi label per butir di 08.** Temanggung dan Magelang C3, Wonosobo C4, kecuali `wnb-5-a03` dan `wnb-5-b03` C5 (menimbang privasi teman melawan tekanan grup).
4. **Rentang Bloom wilayah direvisi, butir tidak ditulis ulang:** Temanggung C1–C3, Magelang C2–C4, Wonosobo C2–C5. Aturan no. 14 di 06 kini menyebut bahwa Bloom menggambarkan proses berpikir, bukan tingkat kesulitan; kesulitan ditunjukkan level AKM dan data Rasch.
5. **Lima perubahan label Isi Rumpang dipertahankan** (ditanyakan sesudah wawancara): `mgl-2-a04`, `mgl-2-b05`, dan `wnb-2-b05` menjadi C2 (perbandingan angka atau sebab–akibat langsung, sama dengan butir serupa di wilayah lain); `wnb-2-a04` dan `wnb-2-a05` menjadi C3 (menerapkan keterangan rentang ketinggian).
6. **`wnb-3-b02` ditulis ulang, level AKM tetap L2** (ditanyakan setelah PR #247 digabung). Jawabannya tersurat, jadi lebih dekat ke L1; daripada memasang L1 di Wonosobo, pernyataannya ditulis ulang di sesi bank berikutnya supaya perlu inferensi. Catatannya ada di 08 (*Yang harus dicek sebelum validasi ahli*). Atas permintaan pengembang, butir itu langsung ditulis ulang di hari yang sama: "Menurut bacaan, petugas memasang pagar pembatas karena kawah di Dieng terlalu panas untuk didekati." Kunci tetap *salah*, kode salah menjadi INF-HUB (sebab yang keliru), Bloom tetap C2.

Akibatnya: 39 dari 92 label lama di 08 berubah, dan 36 label baru ditulis (12 PBK, 24 skenario). Rentang per tantangan dan per wilayah di 06 serta tabel pelevelan wilayah di 01 disesuaikan. Ini label pengembang; label ahli dari pelabelan buta B.5a di tahap 2 menggantikannya.

**Keputusan pengembang: penyelarasan dokumen bersama setelah keputusan tes** (sesi `pbb/magical-hamilton-4f6l98`, wawancara lanjutan)

1. **Variabel tes dan angket masuk kamus data di `kamus-1.1`, setelah uji coba.** `kamus-1.0` di [11](11_KAMUS_DATA.md) tidak diubah sekarang. Sampai saat itu, daftar variabelnya ada di [13](13_TES_AWAL_AKHIR.md#penskoran-dan-pemasukan-data) dan [16](16_ANGKET_AKSES_DIGITAL.md#variabel-untuk-kamus-data).
2. **Pertemuan wilayah (P2–P4) juga tidak terikat 2 JP: waktu bermain ± 90 menit.** Tantangan yang belum selesai tetap dilanjutkan di susulan P5 (paling lama 20 menit). Dengan perkiraan 29–52 menit per wilayah di [06](06_KISI_KISI_BANK_SOAL.md#waktu-bermain), hampir semua siswa selesai di pertemuannya.
3. **Butir risiko daring di P5 memakai izin orang tua terpisah.** Surat izin orang tua dan L7 punya centang sendiri untuk butir ini. Tanpa centang itu, siswa tidak melihat butir tersebut, tetapi tetap ikut kegiatan lain.

Akibatnya: 02 (L6 dan L7), 06 (waktu bermain dan pembagian pertemuan), 03 (kode tes dan angket), 07 (paket validasi), 13, dan 16 disesuaikan.

**Keputusan pengembang: tes awal–akhir dan angket akses digital** (sesi `pbb/magical-hamilton-4f6l98`, menjawab empat pertanyaan di [13](13_TES_AWAL_AKHIR.md#keputusan-pengembang) dan satu putaran wawancara lanjutan)

1. **Lama tes mengikuti kebutuhan, tidak terikat jam pelajaran sekolah.** Penelitian sudah mendapat izin memakai seluruh JP, bahkan sampai setelah jam sekolah. Jadi lama P1 dan P5 mengikuti kebutuhan tes dan kegiatan lain; pilihan 3 JP dan 4 JP di 13 tidak dipakai lagi.
   - Setiap bagian tes diberi batas waktu longgar di atas perkiraan: Bagian 1 ± 40 menit, Bagian 2 dan 3 ± 35 menit. Jam tidak tampil di layar, sama seperti di game.
   - Waktu selesai setiap siswa dicatat, dan batas akhirnya ditetapkan dari uji coba.
2. **Media tes: modul tes di GELITA**, dengan tampilan Bootstrap yang sederhana dan tidak dibuat seperti game. Tes tidak memakai kertas, dan jawaban tidak diketik ulang. Modul ini menjadi perubahan sistem no. 22 di [01](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti) dan dibangun di tahap 3, setelah G2.
3. **Topik teks dan adegan tes: budaya Nusantara di luar Kedu**, seperti draf [14](14_TES_BAGIAN_MEMBACA.md) dan [15](15_TES_BAGIAN_DIGITAL.md).
4. **Angket akses digital masuk modul yang sama**, langsung setelah tes awal di P1. Pertanyaannya hanya tampil di layar dan tidak dibacakan koordinator sesi.
5. **Pengalaman risiko daring: 2–3 butir di angket, diisi di P5 setelah tes akhir**, sendiri di layar (ang-21 sampai ang-23 di [16](16_ANGKET_AKSES_DIGITAL.md)). Syaratnya: butir ini disebut di permohonan izin etik dan surat izin orang tua, dan ada prosedur rujukan tertulis untuk anak yang menjawab pernah mengalami hal yang mengganggu.

Akibatnya: 13, 14, 15, dan 16 disesuaikan dengan modul layar (petunjuk menjawab, waktu, pemasukan data otomatis, kode butir tetap). 02 (L6), 06 (isi P1 dan P5), 11 (variabel tes dan angket), 03 (pola kode), dan 07 (paket validasi) diselaraskan sesudahnya.

**Keputusan pengembang: model penelitian dan pengembangan, dan aturan I-CVI** (sesi `sky/peaceful-curie-68ufr5`)

1. **Model penelitian dan pengembangan: penelitian desain pendidikan model Plomp (2013), jenis studi pengembangan.**
   - ECD menjadi metode merancang asesmen di dalamnya.
   - Lapis evaluasi formatif Tessmer (1993) dan tabel metode Nieveen & Folmer (2013) menentukan cara menguji setiap prototipe.
   - Empat kriteria mutu (relevansi, konsistensi, kepraktisan, keefektifan) menjadi ukuran mutu produk.
   - Peta jalan enam tahap dan lima gerbang di 01 tidak berubah. Tahap 1–4 adalah iterasi fase prototipe, dan tahap 5–6 adalah fase penilaian.
   - Alasannya:
     - GELITA adalah alat ukur, jadi keefektifan diartikan sebagai mutu pengukuran, bukan peningkatan hasil belajar.
     - Prototipe awal boleh berupa kertas, sesuai aturan "sistem diubah setelah G2".
     - Model ini menghasilkan prinsip desain sebagai sumbangan teoretis.
   - ADDIE, 4D, Borg & Gall, dan Sugiyono dibandingkan dan tidak dipilih.
   - Rinciannya, dengan setiap rujukan dicek ke sumber asli, ada di [dokumen pendukung peneliti](dokumen-pendukung-peneliti/README.md). Folder itu adalah bacaan untuk peneliti dan tim, bukan dokumen implementasi.
2. **Aturan I-CVI: aturan Lynn (1986) tetap dipakai.**
   - Dengan lima ahli atau kurang, butir hanya lolos kalau semua ahli memberi skor 3 atau 4 (I-CVI = 1,00).
   - Aturan Polit, Beck, & Owen (2007) yang lebih longgar tidak dipakai.
   - Dicatat di 07 (*Koreksi kecil*) dan ditulis di D.6 instrumen v2.1.
3. **Rujukan dibetulkan setelah dicek ke sumber asli:**
   - PIRLS 2026: editornya "Kennedy, A." (bukan "A. M."), ditambah DOI, di 01 dan 06.
   - Judul DigComp 3.0 tanpa "The", ditambah DOI, di 01, 05, dan 06.
   - Laporan Kominfo & Katadata terbit 2023, dengan istilah pilarnya sendiri (Kecakapan, Etika, Keamanan, Budaya Digital), di 05, 06, dan 07.
   - Webb (1999) ditambahkan di 01 sebagai sumber level DOK.
   - Judul Cohen (1968) dibetulkan di 07.
   - Judul "Rujukan metode (klasik, tidak dicek ulang)" di 01 diganti, karena semuanya sudah dicek.

Usulan dokumen itu yang belum diputuskan (semuanya **diputuskan 26 Oktober 2026**, lihat entri *empat usulan dokumen model penelitian* di atas):
- uji beda tes awal–akhir dilaporkan sebagai temuan eksploratif, karena desain satu kelompok tanpa kontrol tidak bisa menunjukkan sebab-akibat; **diputuskan: ya, dan tes terutama menjadi bukti validitas kriteria**;
- kepraktisan diukur dari log dan lembar L1, L3, dan L4, karena guru tidak ikut (ditambah bintang 1–5 di kritik dan saran sebagai pendapat siswa, sesuai keputusan skala sikap 26 Oktober 2026); **diputuskan: cukup untuk sekarang, ditinjau lagi setelah uji coba**;
- penelitian pendahuluan ditulis sebagai fase tersendiri; **diputuskan: ya, ditambah lembar L8 Profil sekolah**;
- prototipe diberi nomor I–III; **diputuskan: ya**.

**Konfirmasi pengembang: fakta dan gambar Susun Gambar** (sesi `sky/festive-hopper-cl6p65`)

- **Wayang Kedu Temanggungan** adalah varian tradisi wayang kulit asli dari wilayah Kedu, Jawa Tengah, yang berkembang khusus di Kabupaten Temanggung. Keterangan gambar baru Temanggung ditulis dari fakta ini. Kalimat tentang Warisan Budaya Takbenda dihapus dari draf, karena surat penetapannya belum ditemukan.
- **Topeng Lengger berasal dari Wonosobo**, jadi syarat Lengger sebagai variasi `b` Wonosobo terpenuhi. Bukit Sikunir tidak dipakai di pengerjaan pertama. Tahun penetapan 2020 di keterangannya tetap dicek peneliti.
- **Gambar baru Wayang Kedu dibuat tim katalog dan dicek ulang nanti.** Yang diperlukan sekarang hanya konsepnya, dan arahannya sudah ada di 08.
- **Gambar Candi Pawon diganti**, karena gambar sekarang menonjolkan stupa kecil di atap yang mirip sasaran PBK `mgl-4-x01`. Arahan gambar baru ada di 08, dan gambarnya dicek ulang setelah dibuat.

**Keputusan pengembang: variasi Susun Gambar selama studi** (sesi `sky/festive-hopper-cl6p65`, dua pertanyaan di [08](08_DRAF_BANK_SOAL.md#keputusan-pengembang-26-oktober-2026) dan empat pertanyaan lanjutan)

1. **Cara memperbaiki: dua variasi bersih diacak**, bukan satu variasi tetap per wilayah (usulan awal). Kedua variasi menjadi `a` dan `b`, sesuai `FIRST_ATTEMPT_GROUPS` di 06, dan Susun Gambar tidak mengikuti buklet. Variasi lain hanya untuk pengerjaan ulang.
2. **Pasangan per wilayah:**
   - Temanggung: Pasar Papringan, ditambah gambar dan keterangan baru tentang **Wayang Kedu** (topik yang tidak dipakai butir mana pun). Menulis ulang `tmg-1-a` atau `tmg-1-e` tidak dipilih, dan Posong ditolak karena topik gunungnya sama dengan `tmg-2-a`.
   - Magelang: Candi Mendut dan Candi Pawon.
   - Wonosobo: Kompleks Candi Arjuna dan Tari Topeng Lengger. Lengger dipakai hanya kalau ahli budaya membenarkan asalnya dari Wonosobo; kalau tidak, pengembang memilih ulang, dan Bukit Sikunir yang dipangkas menjadi satu-satunya sisa.
3. **Yang menyusul:** draf keterangan Wayang Kedu dan arahan gambar (usulan prompt TMG-P11) ada di 08, menunggu cek fakta dan gambar dari tim katalog. Fakta Papringan, Pawon, Arjuna, dan Lengger dicek peneliti sebelum validasi ahli.

Disesuaikan di dokumen lain: 06 (perubahan sistem no. 4, tabel kode, tabel "Yang harus ditulis") dan 01 (perubahan sistem no. 20). Rujukan "perubahan sistem no. 4 di 01" di entri sebelumnya keliru dan sudah dibetulkan: pemilihan variasi ada di no. 20 di 01, yang merujuk ke no. 4 di 06.
**Keputusan pengembang: cerita penghubung dan narasi tantangan kelima** (sesi `sky/optimistic-bardeen-4zx8ob`)

Pengembang menyetujui seluruh draf [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md) beserta semua usulannya:

1. **Cerita penghubung diputar sekali**, saat tantangan kelima pertama kali dibuka, sebelum kartu misinya.
2. **Selama studi, Kenali wilayah baru terbuka setelah wilayahnya tuntas**, seperti Pustaka. Di luar studi tetap seperti sekarang. Masuk perubahan sistem no. 16 di 01.
3. **Kabar berantai Wonosobo menjadi ramalan gempa**, bukan kawah akan meletus.
4. **Di Skenario Digital hanya situasi yang dibacakan**, lewat tombol ▶.
5. **Urutan P5:** tantangan susulan, tes akhir, cerita penutup, skala sikap di Balai Refleksi, wawancara L5. Tes akhir kini sebelum cerita penutup.

Penyesuaian di dokumen lain: 06 (urutan P5; baris waktu "Cerita wilayah: pembuka, penghubung, tuntas" 4–5 menit, sehingga waktu bermain menjadi ± 29–36, 36–44, dan 43–52 menit), 02 (aturan tes akhir), 01 (daftar kerja no. 7 selesai; perubahan sistem no. 16), dan 00. Pemetaan narasi `petunjuk-*` ke butir `x01`–`x04` dibetulkan di tahap 3, karena pemetaan sekarang masih benar untuk bank yang terpasang.

Keterangan Susun Gambar diperiksa dan diputuskan di sesi lain (dua entri di atas). Karena asal Topeng Lengger dari Wonosobo sudah dikonfirmasi pengembang, `kenal-wonosobo-04` di 12 kembali ke teks lama dan tetap menyebut Lengger; yang dibetulkan di Kenali tinggal jumlah relief yang bercerita (`kenal-magelang-02`). Naskah menjadi 21 baris baru dan 31 baris diubah. Pemetaan narasi `petunjuk-*` ke butir `x01`–`x04` dikerjakan di tahap 3 (12, daftar tahap 3 butir 3).

**Pemeriksaan keterangan Susun Gambar terhadap draf bank soal** (sesi `sky/festive-hopper-cl6p65`, menindaklanjuti *Di luar dokumen ini* di [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md#di-luar-dokumen-ini))

- Ketiga puluh keterangan Susun Gambar (`*-1`, variasi a–j) dicocokkan dengan semua butir D1, kunci D3, dan petunjuk PBK di [08](08_DRAF_BANK_SOAL.md#susun-gambar-selama-studi). Hasilnya: 22 keterangan bocor atau memberi isyarat. Kedua belas petunjuk PBK dan keenam butir representasi D3 masing-masing punya keterangan yang sepadan. Skenario D2 tidak terkena.
- **Koreksi atas 12:** variasi d–j hanya muncul di pengerjaan ulang, yang selama studi baru terbuka setelah wilayahnya tuntas. Jadi Situs Liyangan, Prasasti Gondosuli, dan Umbul Jumprit tidak bocor selama studi, dan tidak ada keterangan yang bocor ke wilayah sesudahnya. Yang berlaku adalah variasi a–c di pengerjaan pertama: 7 dari 9 bocor (`tmg-1-a`, `b`, `c`; `mgl-1-a`, `b`; `wnb-1-a`, `c`). Tiga gambarnya juga foto unggahan *Periksa Unggahan* di wilayah yang sama.
- **Usulan** (menunggu dua jawaban pengembang di 08):
  - Selama studi, pengerjaan pertama Susun Gambar memakai satu variasi tetap per wilayah dari keterangan yang bersih: Pasar Papringan, Candi Mendut, dan Kompleks Candi Arjuna.
  - Ke-27 variasi lain tetap untuk pengerjaan ulang. Tidak ada keterangan atau gambar yang diubah.
  - Setelah diputuskan, pemilihan variasi pengerjaan pertama (perubahan sistem no. 20 di 01, no. 4 di daftar 06) dan kode Susun Gambar di 06 disesuaikan.
- Petunjuk PBK dihitung sebagai kebocoran, sejalan dengan usulan Kenali di 12 (pertanyaan 2).
- Dua fakta keterangan dicatat untuk peneliti: asal Topeng Lengger di `wnb-1-i` (sengaja tidak dipakai di 08), dan tahun Tambi di `wnb-1-h`.
- Pemetaan narasi `petunjuk-*` yang usang (temuan kedua di 12) belum dikerjakan di sesi ini.

**Draf cerita penghubung dan narasi tantangan kelima** (sesi `sky/optimistic-bardeen-4zx8ob`, daftar kerja no. 7)

- Cerita penghubung, kartu misi, baris lama yang berubah, dan daftar rekaman disusun di [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md). Statusnya draf sampai pengembang menjawab empat pertanyaan di bagian akhir dokumen itu:
  1. kapan cerita penghubung diputar
  2. Kenali wilayah selama studi (usulan: baru terbuka setelah wilayahnya tuntas, karena fakta budayanya adalah kunci butir *Periksa Unggahan* dan Temukan Benda)
  3. kabar berantai Wonosobo: tetap kawah atau ganti ramalan gempa
  4. yang dibacakan di Skenario Digital
- **Usulan yang ikut draf** (bisa dikoreksi pengembang):
  - Serpihan kelima setiap wilayah disembunyikan Kabut Lupa di dalam layar (*Kabut di Layar*). Konteks dialog baru `scenario_open`, berkas `layar-{wilayah}-NN`, 6 baris per wilayah.
  - Sebelas aturan menulis cerita untuk penelitian. Intinya: cerita sebelum tantangan tidak menyebut jawaban, tindakan kunci, atau ciri jebakan, dan tidak mendorong ke arah sebaliknya; pelajaran datang di cerita tuntas dan hanya mengulang umpan balik; cerita tuntas tidak menyebut jumlah serpihan seluruhnya, karena ada siswa susulan.
  - Kode berkas baru `skenario-{kode butir}` (Narator) dan `unggahan-{kode butir}` (Mbah Kedu), dengan arahan suara datar supaya nada bicara tidak membocorkan jawaban.
  - Kartu misi tantangan kelima, dan tujuh kartu lama disesuaikan dengan bentuk teks bank baru, sama untuk variasi a dan b.
  - Naskah menjadi 112 baris per bahasa (21 baru, 32 diubah); dengan situasi, petunjuk *Periksa Unggahan*, dan instruksi, rekaman menjadi ± 187 per bahasa. Waktu cerita per pertemuan wilayah naik sekitar satu menit.
- **Temuan pemeriksaan naskah lama terhadap 08** (subagen, dicek ulang): 9 baris menyebut jawaban butir D1 atau tindakan kunci D2, terutama di Kenali dan dialog masuk Wonosobo; dua fakta Kenali bertentangan dengan 08; 12 baris masih menyebut 12 serpihan atau 4 tantangan. Draf 12 lalu ditelaah ulang secara terpisah; temuannya (antara lain kata "katanya" sebagai penanda jebakan, desakan waktu, dan "putuskan sendiri" sebelum skenario yang kuncinya melibatkan orang dewasa) sudah dimasukkan. Naskah aktif (`docs/naskah-cerita.md`) belum diubah; isinya dipasang di tahap 3.
- **Di luar dokumen itu:** keterangan Susun Gambar (tantangan 1) memuat jawaban butir D1 Temanggung, dan pemetaan narasi `petunjuk-*` ke butir `a01`–`a04` sudah usang; keduanya perlu dibereskan di bank sebelum validasi ahli. Diusulkan juga tes akhir dikerjakan sebelum cerita penutup di P5, yang mengubah urutan di 06 dan menunggu persetujuan pengembang.

**Keputusan pengembang: kamus data** (sesi `sky/optimistic-carson-kkpbcv`)

Empat pertanyaan di draf [11](11_KAMUS_DATA.md#keputusan-pengembang) diputuskan, dan kamus menjadi `kamus-1.0`:

1. **Kode bagian lain di *Periksa Unggahan*: sesuai usulan.** Bagian ketuk yang bukan kunci dan bukan pengecoh utama diberi kode jenis petunjuknya (BUD-ATR, BUD-REP, BUD-IZN, BUD-ETK), bukan INF-REL. Ketukan di tempat kosong dekat kunci = NON-SEN dan tidak memakai kesempatan. Aturan di 08 disesuaikan.
2. **Ambang jeda tanpa masukan: 3 menit, sementara.** Hanya bagian jeda yang melebihi ambang yang dipotong dari waktu aktif. Pengembang meminta durasinya mudah diubah, jadi ambang disimpan sebagai pengaturan di konfigurasi studi, dan lapis turunan dihitung ulang dari data mentah tanpa mengubah sistem.
3. **Data siswa yang bisa dianalisis: sesuai usulan.** Izin lengkap, bukan sekolah uji coba, dan percobaan pertama yang bukan dari perangkat bersama pada sedikitnya 80% butir literasi digital (49 dari 61).
4. **Tebakan cepat dipakai apa adanya di analisis utama**; analisis kedua memperlakukannya sebagai data hilang. Ambang sementaranya di bawah 3 detik (usulan awal 1,5 detik), juga berupa pengaturan.

Ambang 3 menit dan 3 detik menggantikan angka usulan lama (60 detik dan 1,5 detik) di 01 dan 03, dan tetap ditetapkan ulang setelah uji coba (`kamus-1.1`). Penegasan yang menyertai draf tidak dikoreksi, jadi ikut berlaku.

## 25 Oktober 2026

**Draf kamus data** (sesi `sky/optimistic-carson-kkpbcv`, daftar kerja no. 2)

- Kamus data disusun di [11_KAMUS_DATA.md](11_KAMUS_DATA.md): peta bukti, butir yang dinilai, pengerjaan pertama, data hilang, penanda analisis, definisi beku 10 analitik, variabel butir dan peserta, aturan gabung lembar, dan syarat data untuk tahap 3. Statusnya draf sampai pengembang memutuskan empat pertanyaan di bagian akhir dokumen itu:
  1. kode bagian ketuk lain di *Periksa Unggahan*
  2. cara memotong jeda di waktu aktif
  3. definisi data siswa yang bisa dianalisis
  4. perlakuan tebakan cepat di analisis utama
- **Penegasan yang diusulkan bersama draf** (tafsiran dokumen sebelumnya, bisa dikoreksi pengembang):
  - Pengerjaan pertama yang terputus dilanjutkan, bukan dibuat baru. Syarat ini ditambahkan ke mode penelitian (perubahan sistem no. 16).
  - "Dilewati" jenis (a) juga mencakup skenario yang ditutup setelah kesempatan ketiga.
  - "Dibantu" di analisis utama hanya bantuan isi L3 (ISI, STR, BHS).
  - Ketukan di tempat kosong tidak memakai kesempatan; ketukan dekat kunci dihitung sebagai NON-SEN di luar percobaan.
  - Benda sasaran petunjuk lain di Temukan Benda diberi INF-LIT.
  - NON-TEB tidak dihitung untuk Isi Rumpang.
  - Token yang membuka wilayah sebuah pertemuan melewati aturan buka berurutan antarwilayah; tantangan yang tertinggal dikerjakan di pertemuan 5.
  - Perpindahan setelah umpan balik di Skenario Digital dinilai dari pilihan tindakan, bukan kodenya, karena dua pilihan bisa berbagi kode.
  - Kode kesalahan bank tidak pernah ditimpa: NON-TEB dan NON-HABIS menjadi penanda tersendiri, dan `*-LAIN` dari pengodean manual disimpan di kolom lain.
- **Perbaikan tanpa keputusan baru:**
  - Di 03, DIG-HOX hanya D2, analitik 8 berlaku untuk D1–D3, dan analitik 10 hanya D2, karena dimensi ikut butir (model *between-item*).
  - Gambar peta bukti disesuaikan dengan kisi-kisi yang disetujui: nama D3, Isi Rumpang dan Benar atau Salah hanya D1, Cari Objek babak 2 D3 dengan babak 1 PBK, Skenario Digital hanya D2, dan kode BUD di pola kesalahan.
  - Contoh data mentah di 01 mendapat kolom `rapid_guess` (kosong untuk Isi Rumpang). Baris tebakan cepat kini memakai kode bank butir itu (INF-HUB), dan `wnb-3-a01` memakai INF-LIT sesuai 08.
  - Analitik 3 di 01 tidak lagi menyebut Susun Gambar (tidak dinilai sejak kisi-kisi 06), dan catatan perubahan sistem no. 14 menunjuk ke syarat data di 11.
  - Di 06, siswa yang belum selesai melanjutkan di pertemuan 5 (susulan), sesuai keputusan token.

**Pemeriksaan sebelum tahap dokumen 1** (sesi `pbb/eloquent-sagan-j40sg8`)

- **Keputusan pengembang: pelabelan buta hanya sekali, oleh dua ahli di tahap 2** (B.5a di 07). Kesepakatan κ ≥ 0,70 pindah dari gerbang G1 ke G2. G1 kini berarti konstruk disetujui dan semua dokumen tahap 1 selesai dan disetujui pengembang. Daftar kerja no. 3 pindah ke tahap 2. Alasannya: pelabelan ganda memakan waktu, dan anggota tim yang sudah melihat label pengembang di 08 tidak bisa melabel secara buta.
- **Perbaikan 01 tanpa keputusan baru**, supaya sejalan dengan kisi-kisi 06 yang sudah disetujui sebelum kamus data disusun:
  - Tabel topik Skenario Digital: izin atas karya budaya pindah ke D3, topik izin D2 cukup foto dan obrolan diri sendiri atau teman.
  - Skema kesalahan mendapat bagian kode BUD (bagian C; NON jadi D, alasan R jadi E). Kalimat "INF-BUD juga D3" dan "DIG-IZN dan DIG-ETK juga D3" diganti pembagian yang disetujui.
  - Cara kode menempel kini memisahkan Cari Objek babak 1 (PBK) dan babak 2 (*Periksa Unggahan*, D3).
  - Contoh data mentah memakai kode `x` untuk butir Temukan Benda.
  - Dua kalimat "kalau token sesi dipakai" dihapus.
- Gambar peta jalan dan 00 disesuaikan.

**Keputusan pengembang: wawancara pertanyaan terbuka** (sesi `pbb/eloquent-sagan-j40sg8`, bertahap)

Tahap 1, urutan kerja dan akses:

- **Urutan kerja tetap seperti peta jalan di 01.** Dokumen implementasi dan perubahan sistem baru dimulai di tahap 3, setelah validasi ahli dan izin etik (gerbang G2). Akibatnya, layar baru (Skenario Digital, *Periksa Unggahan*, token) belum ada saat ahli media menilai di tahap 2; cara menilainya masuk pertanyaan terbuka di 01.
- **Token sesi dipakai, versi ringan, dan sekaligus mengatur wilayah yang dibuka.** P1 pendaftaran dan cerita pembuka, P2 Temanggung, P3 Magelang, P4 Wonosobo, P5 tantangan yang tertinggal dan cerita penutup.
- **Di luar sesi lapangan, seluruh game tertutup sampai penelitian selesai**, termasuk Pustaka dan Profil. Siswa melihat pesan ramah. Setelah status studi ditutup, game terbuka seperti biasa.
- **Kode peserta `S03-A07` disetujui** dengan aturannya di 01: kode sekolah dari daftar peneliti, awalan `U` untuk sekolah uji coba, tanpa `GLT-`, dibuat sistem saat daftar dari token, tidak pernah diubah setelah ada data.
- **Perbaikan dokumen tanpa keputusan baru:** kalimat "kode butir tidak bergeser" di 01 diselaraskan dengan keputusan bank baru (kode yang sudah punya data siswa tidak dipakai ulang); perubahan sistem no. 4 di 01 kini menyebut butir jangkar antarvariasi; L1 di 02 tidak lagi menyebut HP sebagai perangkat.

Tahap 2, aturan game selama penelitian:

- **Skenario Digital memakai tiga kesempatan**, sama seperti tantangan lain. Setiap kesempatan berisi pilihan tindakan dan alasan; yang dinilai tindakan dan alasan pertama. Tafsiran yang dipakai: skenario selesai saat tindakan yang tepat dipilih, pilihan "kurang tepat" (skor 1) memakai satu kesempatan, dan setelah kesempatan ketiga tindakan yang aman ditampilkan dan dijelaskan.
- **Buklet dimasukkan P1 dari L2.** Sesudah pertemuan P1, koordinator memasukkan buklet per kode peserta di panel peneliti; panel memeriksa keseimbangan B1–B4 per rombel. Siswa duduk di kursi yang sama di P2–P4; buklet melekat pada siswa. L2 di 02 mendapat kolom Buklet.
- **Layar terlalu kecil saat studi berjalan: sesi ditolak dengan pesan ramah.** Di luar studi cukup peringatan. Ini memperjelas keputusan media sebelumnya ("ditolak atau diberi peringatan").
- **Pengerjaan ulang selama studi** baru terbuka setelah kelima tantangan wilayah pertemuan itu tuntas, hanya di dalam sesi. Datanya disimpan tapi tidak dinilai.

Tahap 3, akun dan data:

- **Peran `guru` dihapus seluruhnya**, termasuk halaman dan hak aksesnya, bukan hanya dinonaktifkan. Kalau laporan guru dibuat nanti, perannya dibangun ulang dengan hak baru. Kolom Guru di tabel peran 01 dihapus.
- **Lupa kata sandi di lapangan: P1 me-reset lewat kode peserta** dari panel peneliti (kode dicari di L2). Sistem memberi kata sandi sementara dan mencatatnya di log audit; peneliti tetap tidak melihat nama, dan admin tidak perlu hadir.
- **Nama lengkap siswa tetap disimpan di akun, hanya terlihat oleh admin**, sesuai rencana di 01 (usulan nama panggilan saja tidak dipilih).
- **Indikator `sikap` dipakai sebagai skala pendamping sikap menjaga warisan lewat Balai Refleksi**, di luar skor literasi digital, seperti PBK. Saat ini Balai Refleksi hanya berisi ringkasan perjalanan dan kritik dan saran, jadi skala ini instrumen baru: disusun di tahap 1 (daftar kerja no. 19), divalidasi di tahap 2 (G3 pengamat budaya), dan menjadi perubahan sistem no. 21. Waktu pengisiannya masuk pertanyaan terbuka.
- **Ambang waktu tidak aktif dan tebakan cepat ditetapkan setelah uji coba**, mengikuti tahap 4 peta jalan yang urutannya sudah dikukuhkan di tahap 1 wawancara.

Tahap 4, operasional penelitian:

- **Tiga peneliti per kelas** (P1, P2, P3) di pengambilan data utama. Kalau pada hari itu satu berhalangan, P1 juga mengisi L3, L5 dipindah, dan penyimpangannya dicatat di L1.
- **Tes awal dan akhir:** bagian literasi membaca diadaptasi dari contoh soal AKM/ANBK yang terbuka untuk umum; bagian D2 dan D3 disusun sendiri, sejajar dengan kisi-kisi tapi tidak sama dengan butir game; divalidasi bersama bank di tahap 2.
- **Pra-registrasi di OSF** untuk skema kesalahan dan rencana analisis, di gerbang G4 (setelah uji coba, sebelum pengambilan data utama).
- **Ahli media menilai dalam dua langkah:** tahap 2 menilai game sekarang ditambah mock-up layar baru (daftar kerja no. 20); setelah tahap 3, telaah singkat layar jadi di instalasi uji sebelum uji coba (no. 21, bagian gerbang G3). Gambar peta jalan disesuaikan, sekaligus angka lamanya ("129 butir", "15 perubahan") diganti rumusan tanpa angka.

Tahap 5, rencana dokumen:

- **Cerita penghubung dan narasi tantangan kelima didraf AI** dari skenario di 08, disetujui pengembang, dan direkam pengisi suara yang sama dengan narasi lain di sesi rekaman yang sama.
- **Skala sikap diisi sekali, di P5**, di Balai Refleksi setelah cerita penutup.
- **Dokumen berikutnya: kamus data** (`11_KAMUS_DATA.md`, daftar kerja no. 2), yang membekukan definisi 10 analitik, "dilewati", waktu aktif, tersensor, dan kode. Dokumen tahap 1 lain merujuk ke sana.
- **Aturan menjaga konteks** dicatat di `.claude/rules/revisi-10.md` (dimuat saat berkas di folder ini disentuh), dan [00_INDEKS.md](00_INDEKS.md) memuat status setiap dokumen, urutan baca, dan tahap dokumen yang tersisa.
- **Status lama dibetulkan:** daftar kerja no. 6 (Skenario Digital) sudah selesai di draf 08.

Validator dan komisi etik belum bisa ditetapkan pengembang. Sementara:

- **Tujuh validator dengan nama samaran**, sesuai komposisi panel di 07 yang sudah diterima: Ahli Bahasa Anak 1 dan 2, Ahli Literasi Digital 1 dan 2, Ahli Pengukuran (panel ahli materi lima orang), Ahli Budaya Kedu (pengamat budaya), dan Ahli Media Pembelajaran. Nama samaran dicatat di 03 dan dipakai di instrumen dan paket validasi sampai orangnya ditetapkan.
- **Komite etik memakai nama samaran *Lembaga Komite Etik*.** Izin etik diajukan pada minggu kedua sampai keempat November 2026. Persetujuan diperoleh sebelum kegiatan yang mensyaratkannya dimulai, paling lambat antara akhir September dan minggu pertama November 2027.
- **Gerbang G2 tetap menunggu persetujuan etik.** Dengan jadwal di atas, dokumen implementasi dan perubahan sistem (tahap 3) bisa baru dimulai sekitar akhir 2027. Usulan memulai tahap 3 setelah izin diajukan tidak dipilih; pengembang tetap pada urutan ketat dari tahap 1 wawancara. Uji keterbacaan dengan anak (tahap 2), uji coba, dan pengambilan data juga menunggu persetujuan.

**Keputusan pengembang: kisi-kisi, draf bank soal, dan telaah pendahuluan T6** (sesi `kisi-kisi-bank-soal`)

- **Kisi-kisi bank soal ([06](06_KISI_KISI_BANK_SOAL.md)) disetujui seluruhnya**, termasuk semua bagian yang semula bertanda "usulan". Dengan begitu rumusan keputusan di [05](05_TELAAH_BUTIR_DAN_DIMENSI.md) ikut berlaku:
  - D3 = *Partisipasi dan Representasi Budaya Kedu di Ruang Digital*, diukur lewat Cari Objek babak 2 *Periksa Unggahan* (12 butir per siswa). Cari Objek babak 1 jadi skala pendamping *Pengetahuan Budaya Kedu* (PBK), di luar skor literasi digital.
  - D2: tindakan Skenario Digital diskor 0–1–2; alasan hanya data diagnosis dan tidak masuk skor utama di versi pertama.
  - Kode BUD-ATR, BUD-REP, BUD-IZN, BUD-ETK, BUD-LAIN untuk D3. INF-BUD hanya untuk PBK; DIG-IZN dan DIG-ETK hanya untuk D2.
  - Isi Rumpang Temanggung memakai dua kata pengecoh.
  - Per siswa di pengerjaan pertama: 61 butir literasi digital (37 D1 = 26 D1a + 11 D1b; 12 D2; 12 D3) ditambah 12 butir PBK. Isi Rumpang 4/5/6 rumpang, Benar atau Salah 4/6/6 pernyataan.
  - Desain **empat buklet (B1–B4)** per kursi untuk pengerjaan pertama, menggantikan pengacakan variasi; pengerjaan ulang tetap bebas.
  - **Butir jangkar antarvariasi**: 6 pernyataan D1 (2 per wilayah) di kartu jangkar yang sama untuk variasi a dan b, bukan butir yang diulang di tiga wilayah.
  - **Sampel**: target 300 siswa yang datanya bisa dianalisis, merekrut sekitar 360 (± 13 kelas).
  - **Lima pertemuan per kelas**: tes awal; Temanggung; Magelang; Wonosobo; susulan, cerita penutup, dan tes akhir.
  - Opsi tiga pilihan ("tidak bisa dipastikan dari teks") untuk D1b Wonosobo ditunda sampai setelah uji coba.
- **Penyimpangan dari kisi-kisi yang disetujui** (06 sudah disesuaikan): sebaran kode kesalahan dihitung per wilayah, bukan per variasi; panjang teks `wnb-3` 90–150 kata dan kartu jangkar Wonosobo 25–50 kata.
- **Draf bank soal baru** disusun di [08](08_DRAF_BANK_SOAL.md): 116 butir literasi digital dalam variasi a/b ditambah 6 butir jangkar dan 12 butir PBK, semua berstatus `needs_verification`. Terjemahan bahasa Inggris dikerjakan pengembang nanti.
- **Nama fiktif dicek di web.** "Sanggar Turangga Muda" diganti "Sanggar Kuda Pelangi" karena mirip nama kelompok kuda lumping nyata. Semua unggahan tampil di aplikasi media sosial fiktif *Kedugram*, dan komentator ditulis dengan nama depan saja. Pencarian manual nama akun di Instagram dan TikTok masih perlu dilakukan satu orang tim.
- **Telaah pendahuluan T6** dijalankan ([09](09_TELAAH_PENDAHULUAN_BANK_SOAL.md)); 2 temuan sistematis (pilihan tepat dan alasan yang diharapkan selalu paling panjang) dan 13 temuan per butir dibetulkan di 08. Sisa temuan R1–R4 disetujui sesuai usulan: rumpang yang hanya punya satu calon dibiarkan dan dipantau di uji coba (R1); label D1b Temanggung diputuskan ahli (R2); pola kalimat pilihan "kurang tepat" dinilai ahli bahasa anak (R3); kunci Periksa Unggahan Wonosobo dipertahankan dan dipastikan ahli (R4).
- **Cek fakta C.5**: semua fakta bank lama didukung sumber; tahun perkebunan teh Tambi (1865 atau 1885) diserahkan ke ahli budaya.

**Keputusan pengembang: media**

Keempat usulan dari telaah pendahuluan media ([10](10_TELAAH_PENDAHULUAN_MEDIA.md)) disetujui:

- **Tanpa ponsel.** Pengambilan data hanya memakai laptop, Chromebook, atau tablet mendatar berlayar 10 inci atau lebih. Di ponsel, sebagian besar teks tampil di bawah 12 px, sehingga skor membaca ikut bergantung pada perangkat. Aturan ini masuk daftar periksa L0, dan game menolak atau memberi peringatan pada sesi penelitian di layar kecil.
- **Area ketuk Cari Objek tidak boleh bertumpuk.** Adegan Magelang dan Wonosobo ditata ulang supaya area ketuk tidak bersentuhan (jarak minimal setara 24 dp). Aturan bank soal mendapat pemeriksaan otomatis yang sama, berlaku untuk babak 1 dan babak 2 (*Periksa Unggahan*).
- **Jam disembunyikan selama penelitian.** Waktu tetap direkam server. Hitungan benar/salah boleh tampil setelah Cek pertama sebagai umpan balik. Diatur lewat mode penelitian di konfigurasi studi.
- **Aturan narasi.** Bacaan dan pernyataan D1 tidak pernah dibacakan. Situasi Skenario Digital (D2) dan petunjuk *Periksa Unggahan* (D3) boleh dibacakan lewat tombol ▶, tidak diputar otomatis, dan pemutarannya tercatat. Kalimat kisi-kisi "teks di layar game tetap dibacakan narasi seperti sekarang" dibaca dengan aturan ini.

**Telaah pendahuluan media (AI, bukan validasi ahli)**

- Media game ditelaah di instalasi uji lokal: [10_TELAAH_PENDAHULUAN_MEDIA.md](10_TELAAH_PENDAHULUAN_MEDIA.md). Tiga temuan berkode V dan keparahan 3 perlu keputusan pengembang sebelum uji coba:
  - pengambilan data tidak memakai ponsel
  - area ketuk Cari Objek yang bertumpuk ditata ulang dan diperiksa otomatis
  - jam disembunyikan selama penelitian
- Usulan aturan narasi: bacaan dan pernyataan D1 tidak pernah dibacakan; situasi Skenario Digital dan petunjuk *Periksa Unggahan* boleh dibacakan lewat tombol ▶.

**Keputusan pengembang**

- Media game yang ada sekarang (tampilan, layar, interaksi, audio, gambar) juga terbuka untuk diubah, ditambah, dan dihapus sesuai kebutuhan Revisi 10.
- Telaah instrumen validasi ([07](07_TELAAH_INSTRUMEN_VALIDASI.md)) diterima seluruhnya:
  - B.5 dipecah menjadi B.5a (pelabelan buta, dua ahli) dan B.5b (relevansi dan kejelasan, semua ahli menilai semua butir, minimal tiga penilaian non-TD per butir).
  - Panel ahli materi lima orang, dengan matriks penugasan aspek.
  - κ berbobot untuk level AKM.
  - Perkiraan beban kerja dicantumkan di surat permohonan.
- Ahli media masuk daftar bidang validasi (daftar kerja no. 14).
- AI berperan sebagai penelaah pendahuluan, bukan validator: skornya tidak dihitung dan AI tidak melakukan pelabelan buta. Telaah pendahuluan media dikerjakan di instalasi uji lokal.
- Belum ada data siswa asli di server. Bank soal baru boleh memakai susunan kode butir yang baru.
- Setiap tantangan yang dinilai cukup punya **dua variasi (a/b)**, bukan tiga.
- Analisis memakai **model multidimensi *between-item***: setiap butir punya tepat satu dimensi utama.
- **D1 bertumpu pada literasi membaca**, dengan dua label sub-aspek: D1a memahami informasi dan D1b menilai informasi dan sumber. Jumlah butir D1 **tidak dibatasi 29**; lebih banyak tidak masalah.
- Beberapa bacaan diubah menjadi **teks bergenre digital**.
- Kisi-kisi bank soal baru disusun di sesi terpisah, di [06_KISI_KISI_BANK_SOAL.md](06_KISI_KISI_BANK_SOAL.md) (branch `kisi-kisi-bank-soal`). Disetujui pada hari yang sama (lihat di atas).
- Draf instrumen validasi ahli (r10 untuk ahli materi dan pengamat budaya, serta instrumen ahli media) ditelaah di [07_TELAAH_INSTRUMEN_VALIDASI.md](07_TELAAH_INSTRUMEN_VALIDASI.md). Versi 1.0 instrumen ahli materi sudah digantikan r10.
- Seluruh isi bank soal yang ada terbuka untuk diubah, diperbaiki, dihapus, dan ditambah sesuai kebutuhan penelitian. Bank soal baru disusun bersama AI. Batas yang tetap berlaku hanya batas teknis penelitian: kunci jawaban tetap di server, butir baru atau yang diubah berstatus `needs_verification` sampai divalidasi, dan kode butir yang sudah punya data siswa tidak dipakai ulang untuk isi yang berbeda.

**Telaah butir dan dimensi** (rumusan keputusannya berlaku sejak kisi-kisi 06 disetujui, lihat di atas)

- Pengembang mengusulkan rumusan keputusan dimensi dan penskoran: satu dimensi utama per butir, D1 sekitar 29 butir, D2 sekitar 12 butir, D3 diubah menjadi *Partisipasi dan Representasi Budaya Kedu di Ruang Digital*, skor utama dari percobaan pertama.
- Telaah butir dan dimensi disusun di [05_TELAAH_BUTIR_DAN_DIMENSI.md](05_TELAAH_BUTIR_DAN_DIMENSI.md): 99 butir dinilai ditelaah satu per satu, ada rumusan keputusan yang sudah disesuaikan, dan daftar perbaikan butir. Rumusan itu berlaku sejak kisi-kisi 06 disetujui.

## 23 Oktober 2026

**Keputusan pengembang**

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

## Titik awal: dokumen 10 analitik dari pengembang

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
