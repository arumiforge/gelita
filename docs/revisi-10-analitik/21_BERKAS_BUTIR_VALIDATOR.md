# Berkas Butir untuk Validator

**Judul penelitian:** *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*

Per 27 Oktober 2026 · **Status: draf** · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · Daftar kerja no. 3 dan 14 · Prototipe I

Dokumen ini menjelaskan berkas per butir yang diisi validator di tahap 2: daftar butir buta untuk pelabelan B.5a, daftar B.5b, dan lembar kerja (*spreadsheet*) untuk lembar per butir di ketiga instrumen. Semua berkas dibuat oleh satu skrip langsung dari dokumen butir (03, 08, 14–18) dan dari tabel di ketiga instrumen, jadi bisa dibuat ulang setiap kali bank, tes, atau instrumen berubah.

Siapa menerima paket apa, surat permohonan, dan jadwal ada di [20_PAKET_VALIDASI.md](20_PAKET_VALIDASI.md). Dokumen ini hanya mengurus isi berkas per butir.

## Ringkasan

1. **Berkas di [`dokumen-pdf/berkas-validator/`](dokumen-pdf/berkas-validator/):** enam lembar kerja `.xlsx` untuk validator, satu buklet B.5a (PDF dan sumber HTML-nya), satu berkas kunci khusus peneliti, dan laporan pemeriksaan. Isinya: 180 butir untuk B.5a dan B.5b (116 bank, 64 tes), 24 skenario (B.6), 622 sel kode kesalahan (B.7), 12 pernyataan sikap (B.8), 38 pasangan bentuk A–B dan 20 butir angket (B.9), 46 objek budaya, 24 petunjuk *Periksa Unggahan*, 12 butir PBK, 19 fakta, dan 27 gambar mock-up.
2. **B.5a dibuat buta.** Kode butir diganti ID netral (`BT-001` … `BT-180`), bahan bacaan diberi ID netral (`BH-01` … `BH-73`) dan urutannya diacak dengan benih tetap. Label pengembang, kode kesalahan, dan kode butir tidak ada di berkas. Pemetaan ID ke kode hanya ada di berkas kunci khusus peneliti.
3. **Skrip memeriksa dirinya sendiri.** 68 pemeriksaan (jumlah butir per lembar terhadap instrumen, kebutaan B.5a, dan keberadaan setiap kode di 03, 08, 14–18) lolos semua. Satu pemeriksaan gagal berarti tidak ada berkas yang ditulis.
4. **Empat pertanyaan** untuk pemilik proyek (ID netral, urutan kirim ke dua penilai B.5a, kunci di daftar buta, cakupan B.7) ada di [bagian akhir](#pertanyaan-untuk-pemilik-proyek). Draf ini memakai pilihan yang disarankan.

## Daftar berkas

Kolom "Kapan" mengikuti tabel D.3 di setiap instrumen. Putaran 1 = paket awal; putaran 2 = setelah lembar B.5a kedua penilai kembali.

| Berkas | Lembar instrumen | Penerima | Kapan | Sumber isi |
| --- | --- | --- | --- | --- |
| `materi-b5a-daftar-butir-buta.xlsx` | Ahli materi B.5a | Dua penilai B.5a yang ditunjuk pemilik proyek (dari panel ahli materi) | Putaran 1, hanya ke dua penilai itu (D.3 no. 2) | 08, 14, 15 |
| `materi-b5a-buklet-buta.pdf` (dari `.html`) | Ahli materi B.5a | Sama, kalau penilai lebih suka membaca atau mengisi di kertas | Putaran 1, bersama lembar kerjanya | 08, 14, 15 |
| `khusus-peneliti/materi-b5a-kunci-peneliti.xlsx` | Ahli materi C.4 | Ketua peneliti saja; **tidak dikirim** | Dipakai setelah kedua lembar B.5a kembali | 08, 13, 14, 15 |
| `materi-b5b-relevansi-kejelasan.xlsx` | Ahli materi B.5b dan pertanyaan khusus R1–R5 | Kelima ahli materi | Putaran 2 (D.3 no. 5) | 03, 08, 14, 15, instrumen |
| `materi-b6-b9-lembar-kerja.xlsx` | Ahli materi B.6, B.7, B.8a–b, B.9a (dan T1–T5), B.9b | Kelima ahli materi; B.7 wajib untuk Ahli Literasi Digital 1–2; B.8 menurut tabel penilai B.8 | Putaran 1, kecuali untuk dua penilai B.5a (lihat [pertanyaan 2](#pertanyaan-untuk-pemilik-proyek)) | 08, 14–17, instrumen |
| `materi-b7-kode-kesalahan-buta.xlsx` | Ahli materi B.7, versi pengodean buta | Ahli Literasi Digital 1–2, hanya bila peneliti meminta pengodean buta | Sebagai ganti lembar B.7 biasa | 08 |
| `budaya-b5-b8-lembar-kerja.xlsx` | Pengamat budaya B.5, B.6a, B.6b, B.7, B.8a–b | Ahli Budaya Kedu 1–3 | Putaran 1 (D.3 no. 11) | 08, 17, 18, instrumen |
| `media-b8-mockup.xlsx` | Ahli media B.8 | Ahli Media Pembelajaran 1–3 | Putaran 1 (D.3 no. 9) | 18, 03, instrumen |
| `LAPORAN.md` | — | Tim | Dibaca sebelum berkas dikirim | Dibuat skrip |

### Isi setiap lembar kerja

| Berkas | Lembar | Baris | Kolom yang diisi validator |
| --- | --- | --- | --- |
| B.5a buta | Petunjuk, Bahan, B.5a | 73 bahan, 180 butir | Dimensi (D1–D3), Sub-aspek D1 (D1a, D1b, —), Level (L1–L3), Bloom (C1–C6; butir tes sudah berisi "—"), Catatan |
| B.5b | Petunjuk, B.5b, Bahan, Pertanyaan khusus | 180 butir, 73 bahan, R1–R5 | Relevansi dan Kejelasan (1–4, TD), Kunci (B/S), Tanpa teks? (Y/T; D2 sudah "—"), Bocor? (Y/T), Temuan; Setuju? (Y/T) untuk R1–R5 |
| B.6–B.9 | B.6 Skenario, B.6 Naskah | 24 skenario | Realistis, Sesuai usia, Alasan (1–4, TD); Satu tepat, Tengah, Kode, Netral, Kerumitan, Penjelasan (Y/T) |
| | B.7 Kode | 622 sel kode | Setuju (Y/T), Kode usulan (daftar semua kode INF, DIG, BUD, dan R di 03), Alasan |
| | B.8a Sikap, B.8b Sikap | 12 pernyataan, 6 pertanyaan | Kolom B.8a menurut instrumen; Ya/Tidak di B.8b |
| | B.9a Kesetaraan, B.9a Pertanyaan T | 38 pasangan (4 teks, 16 butir membaca, 8 skenario, 2 adegan, 8 butir budaya digital), T1–T5 | Sel sama? (Y/T), Setara (1–4), Lebih mudah (A/B/–), Keakraban A dan B (1–4; butir sudah "—") |
| | B.9b Angket | 20 butir (ang-01–17, ang-21–23) | Relevan (1–4), lima kolom Y/T, kata sulit, usulan |
| B.7 buta | B.7 Kode | 622 sel kode | Kode usulan, Alasan (kode pengembang dikosongkan) |
| Pengamat budaya | B.5 Matriks | 46 objek dan 3 baris usulan, per wilayah | Akurasi, Otentisitas, Kepantasan (1–4, TD), Sensitif? (Y/T) |
| | B.6a Periksa Unggahan, B.6b PBK | 24 petunjuk, 12 butir | Skor 1–4 dan kolom Y/T menurut instrumen |
| | B.7 Cek fakta | 19 fakta | Konfirmasi (Ya/Tidak), catatan |
| | B.8a Sikap, B.8b Sikap | 12 pernyataan, 6 pertanyaan | Menurut tabel pengamat budaya ("Contoh dikenal di wilayah saya?") |
| Ahli media | B.8 Mock-up, B.8 Per gambar | 8 baris layar; 27 gambar | Lima skor 1–4 (TD bila tidak bisa dinilai dari gambar); catatan per gambar opsional |

### Tata letak yang sama di semua lembar penilaian

- **Kepala lembar** (baris 1–5): judul lembar, nama instrumen dan versinya, *Nama samaran validator* (daftar pilihan nama samaran dari [03 → Peran](03_KODE_DAN_ARTI.md#peran)), *Versi berkas*, dan *Tanggal pengisian*. Nama asli validator tidak pernah ditulis di berkas.
- **Judul kolom** di baris 7, diambil dari tabel instrumen. Kolom tambahan yang tidak ada di instrumen selalu diberi tanda "(rujukan)" dan berlatar abu-abu, misalnya teks petunjuk dari 08 di B.6a.
- **Daftar pilihan** di setiap sel penilaian: 1–4 dan TD untuk skor, Y/T, Ya/Tidak, A/B/–, atau daftar kode. Nilai di luar daftar ditolak lembar kerja.
- Sel yang tidak berlaku sudah berisi "—", seperti di tabel instrumen.
- Baris judul dibekukan, ada saringan (*filter*) di setiap kolom, dan lembar dicetak mendatar selebar satu halaman.

## Cara menjaga kebutaan B.5a

B.5a menguji apakah dua ahli memberi label yang sama dengan pengembang tanpa melihat label itu ([07](07_TELAAH_INSTRUMEN_VALIDASI.md), C.4 di instrumen ahli materi). Daftar butirnya dibuat supaya tidak ada yang membocorkan label.

| Yang bisa membocorkan label | Yang dilakukan |
| --- | --- |
| Kolom label pengembang (Sub, AKM, Bloom) di 08 dan kolom Sel di 14 | Tidak diambil. Kolom Dimensi, Sub-aspek D1, Level, dan Bloom di lembar kerja kosong (Bloom butir tes berisi "—") |
| Kode butir: `wnb-3-a02` menunjukkan wilayah, tantangan, dan variasi, padahal wilayah naik level; `tes-ae-…` menunjukkan bagian tes, jadi dimensinya | Diganti ID netral `BT-001` … `BT-180` |
| Urutan dokumen (Temanggung → Magelang → Wonosobo, Bagian 1 → 3) | Bahan diacak dengan benih tetap 20261027; butir di dalam satu bahan tetap urut. ID bahan netral `BH-01` … `BH-73` |
| Kode kesalahan: INF berarti D1, INF-SUM dan INF-FOP berarti D1b, DIG berarti D2, BUD berarti D3 | Tidak dicantumkan di daftar buta |
| Kode alasan R-RISK, R-ETIK, dan seterusnya | Tidak dicantumkan; kunci Skenario Digital menyebut teks tindakan dan alasan yang diharapkan saja |
| Judul bagian di 08 dan 15: "Level utama …", "Tingkat: tanda bahaya jelas", "Nomor 2 · … · samar", jenis petunjuk (Atribusi, Representasi, …) | Tidak diambil; yang diambil hanya teks yang dibaca siswa |
| Huruf bagian ketuk (A1–A8, B1–B8, C1–C10, …) menunjukkan variasi dan bentrok dengan label Bloom C1–C6 | Diganti "Bagian 1", "Bagian 2", … |
| Pilihan tindakan dan alasan Skenario Digital dalam urutan 08 | Diurutkan menurut abjad, sama seperti di game yang mengacaknya |

**Yang tetap ada.** Kunci jawaban dicantumkan, karena label Bloom dan level menilai proses yang dilakukan siswa untuk sampai ke jawaban benar ([pertanyaan 3](#pertanyaan-untuk-pemilik-proyek)). Instruksi tantangan dan "Gambaran layar" butir tes D2 ikut, karena keduanya bagian dari yang dilihat siswa.

**Pemeriksaan otomatis.** Setiap teks bahan, butir, pilihan, dan kunci dicek terhadap 12 pola: kode butir bank, kode tes, kode mock-up, kode kesalahan, kode alasan, label D1–D3, L1–L3, C1–C6, kata label (AKM, Bloom, PBK, sub-aspek, dimensi, kerumitan, padanan), jenis petunjuk D3, penanda tingkat wilayah, dan kata "Variasi a/b" atau "Bentuk A/B". Pemeriksaan diulang pada lembar kerja yang sudah ditulis dan pada buklet. Lembar kerja juga dicek tidak punya lembar kunci, dan kolom labelnya kosong. Uji kebalikannya sudah dicoba: menyisipkan "INF-LIT" dan "L2" ke satu bacaan 08 membuat skrip berhenti dan tidak menulis berkas.

**Yang tidak bisa ditutup dengan berkas.** Isi butir sendiri masih memberi isyarat, dan ini wajar:

- Nama tempat di bacaan (Temanggung, Magelang, Wonosobo) menunjukkan wilayah, dan wilayah naik level.
- Bentuk tugas berbeda per wilayah: bagian ketuk 8, 10, atau 12; pilihan tindakan 3 atau 4; pilihan alasan 3, 4, atau 5.
- Topik tes di luar Kedu menunjukkan butir tes, dan isi butir menunjukkan dimensinya (keamanan data tentu D2).

Isyarat ini memengaruhi label level dan Bloom, bukan dimensi. Karena κ mengukur kesepakatan dua penilai, isyarat yang sama untuk keduanya tidak membuat κ bias ke arah tertentu. Tetapi kesamaan dengan label pengembang bisa sedikit lebih tinggi daripada pelabelan yang benar-benar buta. Ini dicatat sebagai keterbatasan di laporan validasi.

**Berkas kunci.** `khusus-peneliti/materi-b5a-kunci-peneliti.xlsx` memetakan setiap ID netral ke kode butir, sumbernya (dokumen dan baris), dan label pengembang (dimensi, sub-aspek, level, asal level, Bloom). Berkas ini dipegang ketua peneliti, tidak dilampirkan ke surel validator, dan baru dibuka setelah kedua lembar B.5a kembali.

## Cara membuat ulang

Dari akar repo, setelah `composer install`:

```
php docs/revisi-10-analitik/dokumen-pdf/src/berkas-validator.php            # semua berkas, termasuk buklet PDF
php docs/revisi-10-analitik/dokumen-pdf/src/berkas-validator.php --no-pdf   # tanpa PDF
```

- Skrip memakai PhpSpreadsheet yang sudah ada di `composer.json`. Buklet PDF dicetak oleh `berkas-validator-pdf.mjs` dengan Playwright Chromium yang sudah terpasang, seperti `render.mjs` (jangan menjalankan `playwright install`). Tidak ada dependensi baru.
- Kode skrip ada di `dokumen-pdf/src/berkas-validator.php` dan folder `dokumen-pdf/src/berkas-validator/`: `Markdown.php` (membaca Markdown), `Sources.php` (membaca 03, 08, 14–18), `Instrument.php` (membaca tabel instrumen HTML, tanpa mengubahnya), dan `Book.php` (menulis lembar kerja).
- **Versi berkas** tertulis di setiap lembar, misalnya `berkas-1.0 · sumber 3a48dbfe`. Delapan karakter terakhir adalah hash dari dokumen sumber dan ketiga instrumen. Kalau satu kata di sumber berubah, versinya ikut berubah, jadi lembar yang kembali dari validator bisa dicocokkan dengan berkas yang dikirim.
- **Hasil yang sama.** Sumber yang sama selalu menghasilkan lembar kerja yang sama persis, termasuk ID netral B.5a. Buklet PDF memuat tanggal pembuatan dari Chromium, jadi berkasnya berbeda tiap kali dibuat walaupun isinya sama.
- **Kapan dibuat ulang:** setelah 08, 14–18, atau salah satu instrumen berubah, dan sekali lagi tepat sebelum paket dikirim. Commit berkas hasil bersama perubahan sumbernya.
- **Hati-hati dengan ID netral.** Menambah, menghapus, atau mengganti kode butir atau bahan mengubah ID netral. Jangan membuat ulang B.5a setelah dikirim ke penilai, kecuali kedua penilai menerima berkas baru dan berkas kunci ikut diganti.

## Hasil pemeriksaan

Rincian lengkap ada di [`LAPORAN.md`](dokumen-pdf/berkas-validator/LAPORAN.md), yang ditulis ulang setiap kali skrip dijalankan. Per 27 Oktober 2026 (`berkas-1.0 · sumber 3a48dbfe`), 68 dari 68 pemeriksaan lolos.

| Kelompok | Yang dicek | Hasil |
| --- | --- | --- |
| Jumlah butir | 116 bank (30 Isi Rumpang, 32 Benar atau Salah, 6 jangkar, 24 petunjuk *Periksa Unggahan*, 24 Skenario Digital) dan 64 tes (per bentuk: 16 membaca, 8 aman dan etis, 8 budaya digital) sama dengan teks B.5a dan B.9 instrumen dan tabel *Jumlah* di 08; PBK 12 | Lolos |
| Lembar lain | B.6: 24 kode skenario sama dengan 08. B.6a: 24 petunjuk dan jenisnya sama dengan 08. B.6b: 12 butir PBK. B.8a: 12 pernyataan sama dengan 17 (teks, indikator, arah) di kedua instrumen. B.9b: 20 butir sama dengan 16 dan 03. B.5: 46 objek sama dengan teks instrumen. Media B.8: 27 gambar di baris 1–6 sama dengan 18 dan 03 | Lolos |
| Tata letak | Judul kolom B.5a, B.5b, B.7, dan B.9a sama dengan tabel instrumen; definisi label di petunjuk B.5a sama kata demi kata dengan instrumen | Lolos |
| Kode | 622 sel kode B.7 memakai kode yang ada di 03; 192 kode butir mengikuti pola di 03; kode skala sikap, kode di R1–R5 dan T1–T5, kode "Muncul di" di matriks objek, dan sebelas nama samaran ada di 03, 08, 14, 15, atau 18 | Lolos |
| Kebutaan B.5a | Isi 180 butir dan 73 bahan, lembar kerja yang ditulis, dan buklet bebas dari 12 pola; kolom label kosong; tidak ada lembar kunci | Lolos |

## Temuan untuk pemilik proyek

Temuan ini ketidakcocokan atau celah antara instrumen dan dokumen butir. Dokumen lain tidak diubah di sesi ini; usulan perubahannya ada di [Akibat ke dokumen lain](#akibat-ke-dokumen-lain).

1. **Paket awal membocorkan label ke dua penilai B.5a.** D.3 no. 4 instrumen ahli materi mengirim bank lengkap dengan "pengecoh berkode" di putaran awal ke semua ahli materi, dan B.6 dan B.7 juga dikirim di awal. Kode kesalahan menunjukkan dimensi (INF = D1, DIG = D2, BUD = D3), dan INF-SUM dan INF-FOP menunjukkan D1b. Instrumen sendiri juga memuat label: R2 menyebut `tmg-3-a03` dan `tmg-3-b03` sebagai D1b, B.9 menyebut dimensi tiap bagian tes, dan B.5b ada di dokumen yang sama. Usul: dua penilai B.5a menerima paket B.5a saja dulu ([pertanyaan 2](#pertanyaan-untuk-pemilik-proyek)).
2. **Level pengembang butir tes D2 dan D3 berupa rentang.** 13 dan 15 memberi kerumitan dengan padanan "L1–L2" atau "L2–L3", bukan satu level. κ antara dua penilai tetap bisa dihitung, tetapi C.4 juga meminta label akhir dibandingkan dengan label pengembang. Untuk 32 butir itu perlu satu level pengembang per butir.
3. **Level pengembang *Periksa Unggahan* dan Skenario Digital tidak tertulis per butir.** Berkas kunci menurunkannya dari baris *Tingkat* 08 per wilayah (jelas = L1, samar = L2, nilai bertentangan atau dua langkah = L3), sesuai arti Level di B.5a. Kalau tim memaksudkan lain, label ini perlu ditulis di 08.
4. **B.7 lebih panjang dari perkiraan waktunya.** "Semua sel kode" menurut aturan 08 berjumlah 622: 300 kode tertulis di tabel 08 dan 322 kode dari aturan tetap (jawaban rumpang lain → INF-REL, pengecoh di rumpang lain → INF-LIT, bagian ketuk lain → kode jenis petunjuk). Instrumen memperkirakan B.7 2–3 jam ([pertanyaan 4](#pertanyaan-untuk-pemilik-proyek)).
5. **Nama topik Skenario Digital berbeda.** Kolom B.6 instrumen memakai empat nama topik ("Kata sandi dan OTP", "Kabar bohong"), sedangkan 08 memakai nama per skenario ("Kata sandi", "Pesan berantai" di Magelang, "Kabar bohong, dua langkah" di Wonosobo). Lembar kerja memakai nama di instrumen. Kodenya sama semua.
6. **Nama benda sasaran B.6b berbeda kata dengan kunci 08** di tiga butir: `tmg-4-x01` "Kuda kepang anyaman bambu" / "kuda kepang bambu", `wnb-4-x03` "Mi ongklok" / "ongklok", `wnb-4-x04` "Daun teh Tambi" / "daun teh". Isinya sama; lembar kerja memakai kata di instrumen dan menampilkan petunjuk 08 sebagai rujukan.
7. **Lembar kerja yang belum dibuat.** D.3 ahli media no. 9 juga meminta lembar kerja untuk B.3, B.5 (uji perangkat), B.6 (catatan temuan), dan B.7 (24 layar game sekarang). Ahli materi B.3 dan pengamat budaya B.3 (lembar penilaian umum) juga belum. Lembar-lembar itu tidak per butir, jadi di luar cakupan dokumen ini. Bisa ditambahkan ke skrip yang sama kalau dibutuhkan; untuk sekarang validator mengisinya di PDF instrumen.

## Pertanyaan untuk pemilik proyek

Draf dan berkas di atas memakai pilihan pertama di setiap pertanyaan.

1. **ID butir di daftar B.5a.**
   - (a) ID netral `BT-001` … dengan bahan diacak, dan berkas kunci khusus peneliti *(disarankan)*: kode asli menunjukkan wilayah, dan wilayah naik level, jadi kode asli bisa membocorkan label level.
   - (b) Kode asli dalam urutan dokumen: lebih mudah dicocokkan, tetapi pelabelan tidak sepenuhnya buta.
2. **Urutan kirim untuk dua penilai B.5a.**
   - (a) Paket B.5a saja dulu: lembar kerja atau buklet, dan halaman petunjuk B.5a. Instrumen lengkap, bank berkode, kisi-kisi, B.5b, dan lembar B.6–B.9 dikirim setelah B.5a kembali *(disarankan)*: kode kesalahan dan beberapa bagian instrumen menunjukkan dimensi dan sub-aspek (temuan no. 1).
   - (b) Sesuai D.3 sekarang: semua dikirim di awal, kecuali label pengembang dan kisi-kisi.
3. **Kunci jawaban di daftar B.5a.**
   - (a) Dicantumkan *(disarankan)*: label Bloom dan level menilai proses menuju jawaban benar, dan penilai butuh kunci untuk itu; kunci tidak memuat label.
   - (b) Tidak dicantumkan: penilai menebak kunci sendiri, lebih lama dan bisa salah paham.
4. **Cakupan B.7 (kode kesalahan).**
   - (a) Semua 622 sel, dengan kolom "Asal kode" supaya baris dari aturan tetap bisa disaring dan dinilai sekaligus, dan perkiraan waktu B.7 dinaikkan *(disarankan)*: sesuai instrumen ("semua sel kode") dan dibutuhkan kalau pengodean buta dipakai.
   - (b) Hanya 300 sel yang kodenya tertulis di tabel 08, ditambah satu baris per aturan tetap: lebih cepat, tetapi pengodean buta tidak bisa dihitung per sel.

## Akibat ke dokumen lain

Sesi ini hanya mengubah dokumen ini, berkas di `dokumen-pdf/berkas-validator/`, skrip di `dokumen-pdf/src/`, `dokumen-pdf/README.md` (tabel *Isi* dan bagian baru), baris 21 di [00](00_INDEKS.md) dan `docs/PROGRESS.md`, dan entrinya di [04](04_CATATAN_KEPUTUSAN.md). Perubahan berikut dibawa sesi koordinator setelah digabung.

| Dokumen | Bagian | Perubahan |
| --- | --- | --- |
| [20_PAKET_VALIDASI.md](20_PAKET_VALIDASI.md) | Siapa menerima apa dan kapan | Pakai nama berkas dan putaran di [Daftar berkas](#daftar-berkas). Bila pertanyaan 2 (a) disetujui: dua penilai B.5a menerima paket B.5a saja dulu, sisanya setelah B.5a kembali; berkas `khusus-peneliti/` tidak pernah dilampirkan |
| Instrumen ahli materi v2.1 (`dokumen-pdf/src/`) | B.5a, paragraf pembuka | "lembar kerja yang sudah berisi kode butir" → "berisi ID butir netral" (bila pertanyaan 1 (a) disetujui) |
| Instrumen ahli materi v2.1 | D.3 no. 2, 4, 5, dan tabel waktu di A.4 | Sebut nama berkas dari dokumen ini. No. 4: bank berkode untuk dua penilai B.5a dikirim setelah B.5a kembali (pertanyaan 2). Perkiraan waktu B.7 disesuaikan dengan 622 sel (pertanyaan 4) |
| Instrumen pengamat budaya v2.1 | D.3 no. 11 | Sebut `budaya-b5-b8-lembar-kerja.xlsx` |
| Instrumen ahli media v1.1 | D.3 no. 9 | Sebut `media-b8-mockup.xlsx`; lembar kerja B.3, B.5, B.6, dan B.7 belum dibuat (temuan no. 7) |
| Instrumen ahli materi v2.1 | B.6, kolom skenario; B.6b pengamat budaya | Samakan nama topik dan nama benda sasaran dengan 08, atau catat bahwa instrumen sengaja memakai nama ringkas (temuan no. 5 dan 6) |
| [13](13_TES_AWAL_AKHIR.md), [15](15_TES_BAGIAN_DIGITAL.md) | Kerumitan Bagian 2 dan 3 | Tetapkan satu level pengembang per butir tes D2 dan D3 untuk perbandingan di C.4 (temuan no. 2) |
| [08](08_DRAF_BANK_SOAL.md) | *Periksa Unggahan* dan Skenario Digital | Bila level per butir dimaksudkan lain dari baris *Tingkat* per wilayah, tulis levelnya per butir (temuan no. 3) |
| [03](03_KODE_DAN_ARTI.md) | Bagian baru atau *Tes awal–akhir dan angket* | Tambah `BT-001` … `BT-180` (ID butir netral B.5a) dan `BH-01` … `BH-73` (ID bahan netral B.5a), dipetakan di berkas kunci khusus peneliti; berlaku untuk `berkas-1.0` |
| [01](01_REVISI_10_ANALITIK.md) | Daftar kerja no. 3 dan 14 | Status: berkas per butir selesai di draf ([21](21_BERKAS_BUTIR_VALIDATOR.md)), menunggu persetujuan pemilik proyek |
