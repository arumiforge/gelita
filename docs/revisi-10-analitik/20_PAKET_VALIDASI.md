# Paket Validasi Ahli

Per 27 Oktober 2026 · **Status: draf** · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md)

Dokumen ini mengubah tiga instrumen validasi ahli menjadi paket yang siap dikirim ke sebelas validator. Isinya: siapa menerima apa dan kapan, surat permohonan, akses ke instalasi uji, cara hasil dikembalikan dan dicatat, apa yang terjadi setelah hasil kembali, dan daftar periksa validasi untuk gerbang G2. Daftar kerja no. 3 dan no. 14 di [01](01_REVISI_10_ANALITIK.md#peta-jalan-dan-daftar-kerja). Pekerjaan ini bagian dari **Prototipe I**.

Dokumen tahap 2 lain yang dirujuk:

| Berkas | Isi |
| --- | --- |
| `21_BERKAS_BUTIR_VALIDATOR.md` dan `dokumen-pdf/berkas-validator/` | Daftar butir buta B.5a, daftar B.5b, lembar kerja per lembar instrumen |
| `22_PERMOHONAN_IZIN_ETIK.md` | Protokol dan permohonan izin etik; separuh etik gerbang G2 |
| `23_SURAT_INFORMASI_DAN_PERSETUJUAN.md` | Surat informasi dan persetujuan orang tua, anak, sekolah, guru |
| `24_UJI_KETERBACAAN.md` | Protokol uji keterbacaan dengan 3–5 anak kelas 5 |

Ketiga instrumen ada di [dokumen-pdf/](dokumen-pdf/README.md), sumbernya di `dokumen-pdf/src/*.html`. Di dokumen ini disingkat:

| Singkatan | Instrumen |
| --- | --- |
| **IM** | Instrumen Validasi Ahli Materi v2.1 (`instrumen-validasi-ahli-materi-v2.1`) |
| **IB** | Instrumen Validasi Pengamat Budaya v2.1 (`instrumen-validasi-pengamat-budaya-v2.1`) |
| **IMd** | Instrumen Validasi Ahli Media v1.1, langkah 1 (`instrumen-validasi-ahli-media-v1.1`) |

Nomor berkas di dokumen ini selalu nomor di tabel D.3 instrumennya, misalnya "IM D.3 no. 5".

## Ringkasan

**Apa paketnya.** Satu kiriman per validator: surat permohonan, instrumen yang sesuai panelnya, berkas bahan dari tabel D.3 instrumen itu, lembar kerja dari 21, dan akses ke instalasi uji. Ahli materi menerima paketnya dalam dua putaran kirim, karena pelabelan buta (B.5a) harus selesai sebelum label pengembang dibuka.

**Siapa.** Sebelas validator, semuanya dengan nama samaran dari [03 → Peran](03_KODE_DAN_ARTI.md#peran):

| Panel | Validator | Instrumen |
| --- | --- | --- |
| Ahli materi (5) | Ahli Bahasa Anak 1, 2; Ahli Literasi Digital 1, 2; Ahli Pengukuran | IM |
| Pengamat budaya (3) | Ahli Budaya Kedu 1, 2, 3 | IB |
| Ahli media (3) | Ahli Media Pembelajaran 1, 2, 3 | IMd (langkah 1) |

Dua dari lima ahli materi juga menjadi penilai B.5a. IM A.3 menyarankan satu ahli bahasa anak dan satu ahli literasi digital. Draf ini memakai **Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1**; penetapannya ada di [Pertanyaan untuk pemilik proyek](#pertanyaan-untuk-pemilik-proyek).

**Kapan boleh dikirim.** Validator tidak bekerja dengan anak, jadi validasi tidak menunggu izin etik. Uji keterbacaan dengan anak (24) tetap menunggu izin etik. Paket baru boleh dikirim setelah semua prasyarat ini terpenuhi:

| No | Prasyarat | Sumber | Status per 27 Oktober 2026 |
| --- | --- | --- | --- |
| 1 | Pemilik proyek menyetujui ketiga instrumen | [dokumen-pdf/README.md → Sebelum dikirim ke validator](dokumen-pdf/README.md#sebelum-dikirim-ke-validator), [00](00_INDEKS.md) | Belum |
| 2 | Kotak *STATUS* di sampul HTML dihapus, PDF dibuat ulang; lampiran D.4 tidak dicetak untuk validator | README yang sama, langkah 2–3 | Belum |
| 3 | Dokumen bahan yang dinilai sudah disetujui pemilik proyek (keputusan diambil): 08, 12–18, kamus-1.0 di 11 | IM A.4 "Prasyarat", [00 → Tahap dokumen 1](00_INDEKS.md#tahap-dokumen-yang-tersisa-sebelum-implementasi) | Keputusan sudah diambil; 13–17 berstatus draf menunggu validasi, dan memang itu yang divalidasi |
| 4 | Versi bahan dibekukan: tanggal dan *commit* `main` yang dinilai dicatat di 04, dan ditulis di surat pernyataan (D.1, "versi/tanggal") | IM A.4, IB A.4 | Belum |
| 5 | Telaah pendahuluan bank soal selesai | IM A.4, [09](09_TELAAH_PENDAHULUAN_BANK_SOAL.md) | Selesai (R1–R4 disetujui) |
| 6 | Cek fakta budaya oleh tim selesai dan tercatat di lembar B.7 IB | IB A.4, IB D.3 no. 9 | Belum diperiksa sesi ini |
| 7 | Daftar butir buta B.5a, daftar B.5b, dan lembar kerja per lembar siap | IM D.3 no. 2, 5; IB D.3 no. 11; IMd D.3 no. 9; dibuat di 21 | Sedang ditulis (cabang `r10-berkas-validator`) |
| 8 | Instalasi uji siap, dengan akun uji validator | IM D.3 no. 12, IB D.3 no. 8, IMd D.3 no. 2–3; [Akses untuk validator](#akses-untuk-validator) | Belum |
| 9 | Nama asli validator ditetapkan, kesediaannya didapat lisan atau lewat pesan, dan tercatat di daftar padanan di luar repo | [Pengembalian dan pencatatan](#pengembalian-dan-pencatatan) | Belum (pertanyaan terbuka di 01) |
| 10 | Besaran honorarium dan sumber dananya diketahui | IM A.4, IB A.4 ("dicantumkan di surat permohonan") | Belum |

Daftar kode sekolah (no. 12) **tidak** menahan paket ini ([04](04_CATATAN_KEPUTUSAN.md), 27 Oktober 2026). Yang tersentuh hanya IMd D.3 no. 7, dan berkas itu punya pengganti (daftar jenis perangkat).

Dua hal boleh menyusul setelah paket dikirim, karena instrumennya sendiri sudah menyebutnya menyusul: gambar baru Wayang Kedu Temanggungan dan Candi Pawon untuk IB B.5 (IB D.3, "Hal yang masih terbuka"), dan daftar temuan telaah pendahuluan media (IMd D.3 no. 10, dikirim setelah B.6 dikumpulkan).

## Siapa menerima apa

Lembar yang diisi diambil dari matriks penugasan setiap instrumen: IM Tabel A1 dan tabel penilai B.8; IB A.3, A.4, dan tabel penilai B.8; IMd A.3. Tanda: **W** = wajib, tanpa TD; **○** = diisi bila sesuai keahlian (TD boleh). Semua validator juga mengisi lembar identitas (B.1), kesimpulan (IM B.10, IB B.9, IMd B.9), dan surat pernyataan (D.1).

Putaran kirim: **awal**; **setelah B.5a** (setelah kedua lembar B.5a dikumpulkan); **setelah B.6 media** (setelah lembar B.6 ahli media dikumpulkan, masih langkah 1); **media langkah 2** (setelah tahap 3, untuk gerbang G3).

Perkiraan jam dijumlahkan dari tabel waktu instrumen (IM A.4 "Perkiraan waktu dan cara mengisi", IB A.4, IMd A.4). Belum termasuk pengarahan (IM 45 menit, IB 30–45 menit, IMd 30 menit). IM tidak memberi angka untuk bermain satu wilayah, jadi itu juga belum termasuk.

### Panel ahli materi (IM)

| Validator | Lembar yang diisi | Berkas IM D.3: awal | Berkas IM D.3: setelah B.5a | Perkiraan jam |
| --- | --- | --- | --- | --- |
| Ahli Bahasa Anak 1 *(penilai B.5a, usulan)* | **B.5a**; B.3 aspek A, B, D, G, H, J (W), C, I (○), E, F (○); B.5b (W); B.6 (W); B.7 (○); B.8a relevansi, arah negatif, telaah bahasa, dan B.8b (W); B.9a Bagian 1 (W); B.9b bahasa (W) | 1, **2**, 3, 4 (tanpa label), 7, 8 (tes tanpa label), 9, 10, 11, 12 | 4 (label pengembang), 5, 6 | B.5a 6–8 + lainnya 9–14 = **± 15–22 jam** (B.7 bila diisi: + 2–3) |
| Ahli Bahasa Anak 2 | B.3 seperti Ahli Bahasa Anak 1; B.5b (W); B.6 (W); B.7 (○); B.8 seperti Ahli Bahasa Anak 1; B.9a Bagian 1 (W); B.9b bahasa (W) | 1, 3, 4 (tanpa label), 7, 8, 9, 10, 11, 12 | 4 (label pengembang), 5, 6 | **± 9–14 jam** (B.7 bila diisi: + 2–3) |
| Ahli Literasi Digital 1 *(penilai B.5a, usulan)* | **B.5a**; B.3 aspek A, B, D, E, F, H, J (W), C, I, G (○); B.5b (W); B.6 (W); B.7 (W); B.8a tumpang tindih D3 saja (W); B.9a Bagian 2–3 (W); B.9b isi (W) | 1, **2**, 3, 4 (tanpa label), 7, 8 (tes tanpa label), 9, 10, 11, 12 | 4 (label pengembang), 5, 6 | B.5a 6–8 + lainnya 11–16 = **± 17–24 jam** |
| Ahli Literasi Digital 2 | B.3 seperti Ahli Literasi Digital 1; B.5b (W); B.6 (W); B.7 (W); B.8a tumpang tindih D3 saja (W); B.9a Bagian 2–3 (W); B.9b isi (W) | 1, 3, 4 (tanpa label), 7, 8, 9, 10, 11, 12 | 4 (label pengembang), 5, 6 | **± 11–16 jam** |
| Ahli Pengukuran | B.3 aspek A, B, C, H, I, J (W), D, E, F, G (○); B.5b (W); B.6 (○); B.7 (○); B.8a relevansi, indikator tepat, arah negatif, dan B.8b (W); B.9a (W); B.9b (○) | 1, 3, 4 (tanpa label), 7, 8, 9, 10, 11, 12 | 4 (label pengembang), 5, 6 | **± 7,5–12 jam** (B.6 dan B.7 bila diminta: + 3,5–5) |

Catatan untuk panel ini:

- **Berkas no. 2 hanya ke dua penilai B.5a** (IM D.3). Berkas no. 4 dan no. 8 untuk semua dikirim awal **tanpa** kolom dimensi, sub-aspek, level, dan Bloom. Label pengembang dan kisi-kisi (no. 6) baru dikirim setelah B.5a dikumpulkan (IM A.4 "Pelabelan buta lebih dulu", IM D.3 no. 4–6). Aturan ini berlaku untuk kelima ahli, supaya tidak ada salinan berlabel yang beredar di panel sebelum B.5a selesai.
- Lembar yang bisa dikerjakan di putaran awal: B.6, B.7, B.8, B.9, dan B.3 kecuali bagian yang butuh kisi-kisi. B.5b dan sisa B.3 dikerjakan setelah putaran kedua.
- Bila sebuah butir B.3 atau B.5b punya kurang dari tiga penilaian non-TD, peneliti meminta ahli bertanda ○ mengisinya (IM catatan di bawah Tabel A1). Jadi jam Ahli Pengukuran bisa bertambah.

### Panel pengamat budaya (IB)

IB tidak punya tabel penugasan per orang. Ketiga pengamat budaya menilai semua butir B.3 dan semua butir PBK (IB A.3 "Alasan tiga orang"), mengisi B.5–B.7, dan di matriks objek B.5 menilai objek yang dikuasainya (IB A.3 "Etika validator"). Yang berbeda hanya B.8. Semua berkas IB D.3 dikirim di putaran awal (tabel D.3 IB tidak punya kolom "Kapan dikirim"); berkas no. 3 tanpa label level dan Bloom, dan label itu memang tidak dikirim ke panel ini.

| Validator | Lembar yang diisi | Berkas IB D.3: awal | Menyusul | Perkiraan jam |
| --- | --- | --- | --- | --- |
| Ahli Budaya Kedu 1 | B.3 (semua butir); B.5 matriks objek; B.6a *Periksa Unggahan* dan B.6b PBK; B.7 cek fakta; B.8 relevansi 1–4, indikator tepat, contoh budaya, B.8b (semua wajib) | 1–11 | Gambar Wayang Kedu Temanggungan dan Candi Pawon (bagian berkas no. 6) | bahan dan bermain 3–4, B.5 2–3, B.6 1,5–2, B.7 1, B.8 0,5–0,75, B.3 1–2 = **± 9–13 jam** |
| Ahli Budaya Kedu 2 | B.3; B.5; B.6a, B.6b; B.7; B.8 contoh budaya untuk wilayahnya (wajib), B.8b (boleh) | 1–11 | Sama | **± 8,5–12 jam** (B.8 10 menit) |
| Ahli Budaya Kedu 3 | Sama dengan Ahli Budaya Kedu 2 | 1–11 | Sama | **± 8,5–12 jam** |

Idealnya satu orang paling mengenal Temanggung, satu Magelang, satu Wonosobo (IB A.3). Pembagian wilayah ini dicatat di daftar padanan di luar repo, bukan di sini.

### Panel ahli media (IMd)

Ketiga ahli media mengisi lembar yang sama. Satu pengecualian: butir teknis H3 hanya diisi Ahli Media Pembelajaran 3, sebagai daftar periksa lulus/tidak; yang lain mengisi TD (IMd A.3).

| Validator | Lembar yang diisi | Berkas IMd D.3: awal | Setelah B.6 media | Media langkah 2 | Perkiraan jam |
| --- | --- | --- | --- | --- | --- |
| Ahli Media Pembelajaran 1 (teknologi pendidikan) | B.3 (H3 diisi TD); B.5 uji perangkat; B.6 temuan; B.7 per layar; B.8 mock-up | 1–9 | 10 | Paket langkah 2 (lihat bawah) | bermain 2–3, mock-up 1–1,5, B.3 1 = **± 4–5,5 jam** |
| Ahli Media Pembelajaran 2 (desain visual atau UX) | Sama dengan Ahli Media Pembelajaran 1 | 1–9 | 10 | Sama | **± 4–5,5 jam** |
| Ahli Media Pembelajaran 3 (rekayasa perangkat lunak atau game) | B.3 termasuk H3 (daftar periksa lulus/tidak); B.5; B.6; B.7; B.8 | 1–9 | 10 | Sama | **± 4–5,5 jam** |

Setiap ahli media menguji minimal tiga perangkat: laptop atau Chromebook, tablet mendatar 10 inci atau lebih, dan satu perangkat seperti milik sekolah sampel (IMd A.4). Ponsel tidak diuji. Bila validator tidak punya tablet atau perangkat sekolah, tim meminjamkannya; ini disebut di surat.

**Media langkah 2** belum punya instrumen sendiri. IMd A.1 hanya menyebut isinya: telaah singkat layar jadi di instalasi uji (gerak, suara, waktu muat, papan ketik sentuh, dan semua butir bertanda mock-up dinilai ulang). Instrumen dan paketnya disusun di tahap 3 dan masuk gerbang G3 (daftar kerja no. 21), bukan G2.

### Cek silang ke tabel D.3

| Instrumen | Nomor di D.3 | Masuk tabel di atas |
| --- | --- | --- |
| IM | 1–12 | 1, 3, 7, 9, 10, 11, 12 awal ke semua; 2 awal ke dua penilai B.5a; 4 dan 8 awal tanpa label; 4 (label), 5, 6 setelah B.5a |
| IB | 1–11 | Semua awal ke ketiga pengamat budaya; gambar baru di no. 6 menyusul |
| IMd | 1–10 | 1–9 awal ke ketiga ahli media; 10 setelah B.6 media |

## Urutan pengiriman dan jadwal

### Putaran kirim

1. **Minggu 0, kesediaan.** Ketua peneliti menghubungi calon validator (telepon atau pesan) untuk menanyakan kesediaan, waktu, dan perangkat. Baru setelah bersedia, surat resmi dikirim. Nama dan kontak dicatat di daftar padanan.
2. **Putaran awal (minggu 1).** Kesebelas validator menerima surat permohonan dan berkas putaran awal. Pengarahan daring per panel, terpisah supaya validator tidak saling memengaruhi (IM A.4 "Independen"): ahli materi 45 menit (penilai B.5a ditambah 15 menit tentang cara melabel tanpa membuka bahan lain), pengamat budaya 30–45 menit, ahli media 30 menit.
3. **Setelah B.5a (minggu 3).** Setelah kedua lembar B.5a masuk dan diarsipkan, kelima ahli materi menerima surat lanjutan dan berkas IM D.3 no. 4 berlabel, no. 5, dan no. 6.
4. **Setelah B.6 media (minggu 3–4).** Setiap ahli media yang sudah mengumpulkan B.6 menerima IMd D.3 no. 10.
5. **Putaran 2 validasi (minggu 10–11).** Hanya butir atau bagian yang direvisi, dengan matriks tindak lanjut D.2 (lihat [Setelah hasil kembali](#setelah-hasil-kembali)).
6. **Media langkah 2.** Setelah tahap 3, sebelum uji coba (G3).

### Jadwal relatif

Batas waktu mengikuti saran instrumen: pengisian boleh dicicil 2–3 minggu (IM A.4, IB A.4).

| Minggu | Kegiatan | Pengingat | Batas |
| --- | --- | --- | --- |
| 0 | Prasyarat 1–10 dipenuhi; kesediaan validator; akun uji dibuat | — | — |
| 1 | Kirim putaran awal ke 11 validator; pengarahan per panel | Hari ke-3: cek semua validator bisa masuk instalasi uji dan membuka berkas | — |
| 2 | Validator mengisi | Pengingat 1 ke semua (singkat, sekaligus tawaran bantuan) | Akhir minggu 2: **B.5a** dari dua penilai |
| 3 | Kirim putaran setelah B.5a ke 5 ahli materi; peneliti mulai menghitung κ | — | Akhir minggu 3: **B.6 media** |
| 4 | Kirim IMd D.3 no. 10; bila κ < 0,70, pedoman pelabelan dipertajam dan sampel dilabel ulang (IM C.6) | Pengingat 2 ke semua yang belum selesai | Akhir minggu 4: **semua lembar ahli media dan pengamat budaya** |
| 5 | Ahli materi menyelesaikan B.5b dan sisa B.3 | Pengingat 3, dua hari sebelum batas | Akhir minggu 5: **semua lembar ahli materi** |
| 6–7 | Entri, pemeriksaan ganda, analisis per instrumen, matriks revisi | — | — |
| 8–9 | Revisi Prototipe I; dicatat di 04 | — | — |
| 10–11 | Putaran 2: butir yang direvisi dikirim ke penilainya | Satu pengingat di minggu 11 | Akhir minggu 11 |
| 12 | Daftar periksa validasi G2 diisi (kecuali uji keterbacaan, yang menunggu izin etik) | — | — |

Bila validator terlambat lebih dari satu minggu, ketua peneliti menelepon. Bila tetap tidak bisa, peneliti meminta penilai pengganti dari bidang yang sama, karena butir dengan kurang dari tiga penilaian non-TD belum boleh dinyatakan lolos (IM C.6, IB C.5).

### Usulan tanggal (perlu persetujuan)

Tanggal ini **usulan**, dengan anggapan ketiga instrumen disetujui paling lambat awal November 2026.

| Minggu | Usulan tanggal |
| --- | --- |
| 1 | Senin 9 November 2026 |
| 2 (batas B.5a) | Jumat 20 November 2026 |
| 3 | Senin 23 November 2026 |
| 4 | Senin 30 November 2026 |
| 5 (batas semua lembar ahli materi) | Jumat 11 Desember 2026 |
| 6–7 | 14–24 Desember 2026, lalu jeda libur akhir tahun sampai 1 Januari 2027 |
| 8–9 | 4–15 Januari 2027 |
| 10–11 (putaran 2) | 18–29 Januari 2027 |
| 12 | Minggu 1 Februari 2027 |

### Hubungannya dengan izin etik

- Permohonan izin etik diajukan minggu kedua–keempat November 2026 ([01 → Peta jalan](01_REVISI_10_ANALITIK.md#peta-jalan-dan-daftar-kerja)). Dengan usulan di atas, itu sama dengan minggu 1–3 validasi. Ketua peneliti mengurus keduanya, jadi pengiriman paket sebaiknya selesai sebelum berkas etik difinalkan.
- Validasi tidak menunggu izin etik, karena validator tidak bekerja dengan anak dan tidak menerima data siswa (IM A.3, IB A.3 "Kerahasiaan").
- Permohonan etik melampirkan instrumen untuk anak (tes, angket, skala sikap) dalam versi yang dibekukan untuk validasi. Bila revisi Prototipe I mengubah bahan yang dilihat anak, perubahannya dilaporkan ke *Lembaga Komite Etik* sesuai aturan komite itu (`needs_verification`: cara melapor perubahan tergantung komite; diatur di 22).
- Uji keterbacaan (24) baru berjalan setelah izin etik terbit (paling lambat akhir September–minggu pertama November 2027). Bahan yang diuji adalah versi **setelah** revisi validasi ahli, jadi revisi Prototipe I sudah selesai jauh sebelum uji itu.

## Surat permohonan validasi

Surat ditulis dari ketua peneliti. Bagian dalam kurung siku diisi saat dikirim dan **tidak** pernah diisi di repo. Judul penelitian diambil dari surat pernyataan D.1 ketiga instrumen.

Tempat isian yang dipakai di ketiga surat:

| Isian | Arti |
| --- | --- |
| `[nomor surat]`, `[tanggal surat]` | Dari tata usaha lembaga peneliti |
| `[nama validator]`, `[gelar]`, `[instansi validator]` | Dari daftar padanan |
| `[nama ketua peneliti]`, `[NIM/NIP]`, `[program studi]`, `[nama perguruan tinggi]` | Identitas peneliti |
| `[nama pembimbing/promotor]` | Bila surat perlu diketahui pembimbing |
| `[besaran honorarium]`, `[cara pembayaran]` | Lihat [Pertanyaan untuk pemilik proyek](#pertanyaan-untuk-pemilik-proyek) |
| `[tanggal pengarahan]`, `[tanggal batas]`, `[tanggal batas B.5a]` | Dari jadwal |
| `[alamat instalasi uji]`, `[nama akun uji]` | Dikirim terpisah dari surat (lihat [Akses untuk validator](#akses-untuk-validator)) |
| `[nomor telepon/WhatsApp]`, `[surel peneliti]` | Kontak ketua peneliti |
| `[tautan folder]` | Folder bersama per validator |

### Surat 1: ahli materi

> Nomor: [nomor surat]
> Lampiran: 1 berkas (instrumen dan bahan)
> Hal: **Permohonan menjadi validator ahli materi**
>
> Yth. [nama validator], [gelar]
> [instansi validator]
>
> Dengan hormat,
>
> Saya, [nama ketua peneliti] ([NIM/NIP], [program studi], [nama perguruan tinggi]), sedang melaksanakan penelitian berjudul *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*. Penelitian ini mengembangkan game GELITA untuk siswa kelas 5 SD beserta bank soal, tes awal–akhir, angket akses digital, dan skala sikap.
>
> Mengingat keahlian Bapak/Ibu di bidang [bidang keahlian], saya memohon kesediaan Bapak/Ibu menjadi **validator ahli materi**. Bapak/Ibu diminta menilai kesesuaian isi dengan konstruk literasi digital, ketepatan kunci, kebahasaan, dan relevansi setiap butir, mengikuti Instrumen Validasi Ahli Materi v2.1 terlampir. Lembar yang menjadi tugas Bapak/Ibu ditandai di Tabel A1 instrumen.
>
> [Hanya untuk dua penilai B.5a:] Bapak/Ibu juga kami mohon menjadi salah satu dari dua **penilai pelabelan buta** (lembar B.5a). Lembar ini dikerjakan lebih dulu, sebelum Bapak/Ibu membaca label pengembang atau kisi-kisi. Karena itu, label dan kisi-kisi baru kami kirim setelah lembar B.5a Bapak/Ibu terima, paling lambat [tanggal batas B.5a].
>
> Beberapa hal yang perlu Bapak/Ibu ketahui:
>
> 1. **Waktu.** Perkiraan seluruh pengisian sekitar [perkiraan jam dari tabel] jam, boleh dicicil. Batas pengumpulan [tanggal batas]. Pengarahan daring sekitar 45 menit pada [tanggal pengarahan].
> 2. **Dua kiriman.** Sebagian bahan kami kirim sekarang. Label pengembang, kisi-kisi bank soal, dan daftar relevansi per butir (B.5b) menyusul setelah pelabelan buta selesai, sekitar [tanggal kiriman kedua].
> 3. **Cara mengisi.** Lembar per butir tersedia sebagai lembar kerja (*spreadsheet*) yang sudah berisi kode butir. Lembar lain boleh diisi di berkas atau di kertas.
> 4. **Akses game.** Bapak/Ibu akan menerima alamat instalasi uji dan akun uji secara terpisah, untuk memainkan minimal satu wilayah. Mohon tidak memakai akun itu di luar keperluan validasi.
> 5. **Kerahasiaan.** Bank soal, kunci jawaban, butir tes, dan skema kode mohon tidak disebarkan, karena kunci yang bocor ke calon peserta merusak data. Bapak/Ibu tidak akan menerima data siswa apa pun.
> 6. **Mandiri.** Mohon tidak berdiskusi dengan validator lain sampai lembar Bapak/Ibu selesai. Pertanyaan tentang bahan silakan diajukan kepada saya; kami akan menjawab dengan informasi faktual.
> 7. **Nama.** Di dokumen penelitian, nama Bapak/Ibu diganti nama samaran. Nama asli hanya tercantum di surat pernyataan dan arsip peneliti, dan disebut di laporan hanya dengan izin Bapak/Ibu.
> 8. **Honorarium.** Sebagai penghargaan, kami menyediakan honorarium sebesar [besaran honorarium], dibayarkan [cara pembayaran] setelah lembar dan surat pernyataan kami terima.
>
> Bila bersedia, mohon konfirmasi melalui [nomor telepon/WhatsApp] atau [surel peneliti]. Berkas dapat dibuka di [tautan folder].
>
> Atas kesediaan dan bantuan Bapak/Ibu, saya mengucapkan terima kasih.
>
> Hormat saya,
> [nama ketua peneliti]
>
> Mengetahui,
> [nama pembimbing/promotor]

### Surat 2: pengamat budaya

Sama dengan surat 1, dengan perubahan ini:

- **Hal:** Permohonan menjadi validator pengamat budaya.
- **Paragraf tugas:** "Mengingat pengalaman Bapak/Ibu dalam [meneliti/mendokumentasikan/mempraktikkan] budaya Kedu, khususnya [wilayah yang paling dikuasai], saya memohon kesediaan Bapak/Ibu menjadi **validator pengamat budaya**. Bapak/Ibu diminta menilai ketepatan dan kepantasan isi budaya Kedu dalam game, cerita, gambar, dan soal, termasuk saat budaya itu tampil di ruang digital, mengikuti Instrumen Validasi Pengamat Budaya v2.1 terlampir. Kami juga mohon Bapak/Ibu mengonfirmasi atau membantah hasil cek fakta yang sudah kami lakukan (lembar B.7)."
- **Tanpa** paragraf B.5a dan **tanpa** butir 2 (dua kiriman). Ganti butir 2 dengan: "**Gambar menyusul.** Dua gambar baru (Wayang Kedu Temanggungan dan Candi Pawon) masih dibuat dan akan kami kirim menyusul untuk dinilai di matriks objek."
- **Waktu:** pengarahan 30–45 menit.
- **Kerahasiaan:** "Bank soal, kunci jawaban, dan skema kode mohon tidak disebarkan."
- Tambahan di butir 6: "Perbedaan versi antardesa atau antarkomunitas akan kami catat sebagai versi, bukan langsung dianggap salah."
- [Hanya untuk Ahli Budaya Kedu 1:] "Bapak/Ibu juga kami mohon menilai relevansi skala sikap menjaga warisan (lembar B.8), sekitar 30–45 menit."

### Surat 3: ahli media

Sama dengan surat 1, dengan perubahan ini:

- **Hal:** Permohonan menjadi validator ahli media.
- **Paragraf tugas:** "Mengingat keahlian Bapak/Ibu di bidang [teknologi pendidikan/desain visual atau UX/rekayasa perangkat lunak atau pengembangan game], saya memohon kesediaan Bapak/Ibu menjadi **validator ahli media**. Bapak/Ibu diminta menilai tampilan, audio, interaksi, alur permainan, dan keandalan GELITA di beberapa perangkat, serta 27 gambar rancangan layar baru, mengikuti Instrumen Validasi Ahli Media v1.1 terlampir."
- Ganti butir 2 dengan: "**Dua langkah.** Penilaian sekarang adalah langkah 1: game yang ada di instalasi uji dan gambar rancangan layar baru. Setelah layar baru selesai dibuat, kami akan memohon telaah singkat langkah 2 pada layar jadi, sebelum uji coba. Permohonan itu kami kirim terpisah."
- Tambahkan butir: "**Perangkat.** Mohon menguji minimal tiga perangkat: laptop atau Chromebook, tablet mendatar 10 inci atau lebih, dan satu perangkat seperti milik sekolah. Bila Bapak/Ibu tidak memiliki salah satunya, kami dapat meminjamkannya."
- Ganti butir 4 dengan: "**Akses.** Alamat instalasi uji dan akun uji kami kirim terpisah. Mohon hanya memakai instalasi uji, tidak masuk ke server produksi, dan tidak menjalankan uji beban, uji penetrasi, atau percobaan masuk ke akun lain (IMd A.3)."
- **Kerahasiaan:** "Isi game, akses, rancangan, mock-up, dan tangkapan layar mohon tidak disebarkan."
- **Waktu:** sekitar 4–5,5 jam; pengarahan 30 menit.
- [Hanya untuk Ahli Media Pembelajaran 3:] "Butir teknis H3 hanya diisi Bapak/Ibu, sebagai daftar periksa lulus/tidak."

### Surat lanjutan setelah B.5a (ke lima ahli materi)

> Hal: **Kiriman kedua bahan validasi ahli materi**
>
> Yth. [nama validator],
>
> Terima kasih atas waktu yang sudah Bapak/Ibu berikan. Pelabelan buta (B.5a) sudah selesai, jadi bahan berikut sekarang bisa kami kirim:
>
> 1. Bank soal dengan label pengembang (dimensi, sub-aspek, level AKM, dan Bloom);
> 2. Daftar relevansi per butir (B.5b) dalam lembar kerja, berisi dimensi utama dan definisinya;
> 3. Kisi-kisi bank soal: butir per tantangan, buklet, jangkar, dan waktu bermain.
>
> Dengan bahan ini Bapak/Ibu dapat mengisi lembar B.5b dan menyelesaikan lembar penilaian B.3. Batas pengumpulan seluruh lembar tetap [tanggal batas]. Mohon lembar yang sudah selesai sebelumnya tidak diubah setelah membaca label ini, kecuali ada kekeliruan pengisian; bila ada, mohon beri catatan.
>
> Berkas ada di [tautan folder]. Bila ada pertanyaan, silakan hubungi saya di [nomor telepon/WhatsApp].
>
> Hormat saya,
> [nama ketua peneliti]

Kalimat "mohon lembar yang sudah selesai tidak diubah" menjaga lembar yang dikerjakan di putaran awal tetap tidak dipengaruhi label. Untuk penilai B.5a, lembar B.5a sudah dikumpulkan dan diarsipkan, jadi tidak bisa berubah.

### Pengingat singkat

> Yth. [nama validator], kami mengingatkan dengan hormat bahwa batas pengumpulan lembar validasi GELITA adalah [tanggal batas]. Bila Bapak/Ibu butuh waktu tambahan, bantuan akses, atau perangkat, silakan kabari kami. Terima kasih. — [nama ketua peneliti]

### Surat putaran 2 validasi

> Hal: **Penilaian ulang butir yang telah direvisi**
>
> Yth. [nama validator],
>
> Terima kasih atas penilaian Bapak/Ibu. Kami telah merevisi bahan berdasarkan masukan para validator. Terlampir matriks tindak lanjut (lampiran D.2 instrumen) yang menjelaskan setiap temuan dan perubahannya, serta [jumlah] butir/bagian yang mohon dinilai ulang dengan rubrik yang sama. Perkiraan waktu [perkiraan] jam; batas [tanggal batas]. Butir yang tidak tercantum tidak perlu dinilai ulang.
>
> Hormat saya,
> [nama ketua peneliti]

## Akses untuk validator

Ahli materi dan pengamat budaya memainkan game yang ada dengan akun uji, minimal satu wilayah (IM A.4 "Kenali konteks", IB A.4). Ahli media bermain satu wilayah penuh per perangkat, mendaftar akun baru, membuka Pustaka, dan keluar akun (IMd A.4). Kebutuhan dari D.3:

| Kebutuhan | IM | IB | IMd |
| --- | --- | --- | --- |
| Alamat instalasi uji (bukan server produksi) | D.3 no. 12 | D.3 no. 8 | D.3 no. 2, dengan versi/tanggal yang dinilai dan cara memasang sebagai aplikasi |
| Akun uji | D.3 no. 12 | D.3 no. 8 | D.3 no. 3: akun uji, atau izin mendaftar akun baru |
| Wilayah yang terbuka | Empat tantangan yang ada sekarang | Satu wilayah atau lebih | Wilayah terbuka bebas |
| Isi Pustaka Kedu | D.3 no. 12 | D.3 no. 8, beserta sumbernya | Dibuka saat berperan sebagai siswa |

### Usulan akun uji

Akun validator adalah **akun siswa uji** di instalasi uji, bukan akun staf. Validator tidak membutuhkan panel admin atau panel peneliti, dan tidak boleh melihat data siswa. Penamaan mengikuti pola [03 → Akun staf](03_KODE_DAN_ARTI.md#akun-staf): nama samaran per peran, bukan nama orang.

| Akun (nama pengguna) | Untuk | Catatan |
| --- | --- | --- |
| `uji.bahasa1`, `uji.bahasa2` | Ahli Bahasa Anak 1, 2 | Satu akun per validator |
| `uji.digital1`, `uji.digital2` | Ahli Literasi Digital 1, 2 | |
| `uji.ukur` | Ahli Pengukuran | |
| `uji.budaya1`, `uji.budaya2`, `uji.budaya3` | Ahli Budaya Kedu 1–3 | |
| `uji.media1`, `uji.media2`, `uji.media3` | Ahli Media Pembelajaran 1–3 | Ahli media juga boleh mendaftar akun baru sendiri, karena pendaftaran termasuk yang dinilai (IMd A.4); nama penggunanya diawali `uji.media` |
| `uji.cadangan1`, `uji.cadangan2` | Cadangan bila akun terkunci atau validator diganti | |

Aturan akun:

- **Dibuat oleh `admin`** di instalasi uji, sebelum minggu 1. Nama pengguna mengikuti aturan nama pengguna siswa yang ada sekarang: huruf kecil, angka, titik, garis bawah, 3–30 karakter (`valid_username` di `app/Validation/GelitaRules.php`). Karena itu dipakai titik, bukan tanda hubung.
- **Nama lengkap di akun** juga nama samaran tanpa angka, misalnya "Uji Bahasa Satu", karena nama lengkap siswa hanya boleh berisi huruf (`valid_person_name`). Sekolah yang dipilih adalah sekolah contoh di instalasi uji, bukan sekolah sampel.
- **Tidak memakai awalan `sim_`**, karena awalan itu dipakai siswa simulasi `gelita:simulasi`, dan **tidak memakai pola kode peserta** (`S03-A07`, `U01-A03`), supaya akun validator tidak tercampur dengan peserta penelitian atau uji coba.
- **Kata sandi** dibuat `admin`, dikirim lewat saluran terpisah dari alamat instalasi uji (misalnya alamat lewat surel, kata sandi lewat pesan), dan validator diminta menggantinya saat pertama masuk bila sistem memintanya.
- **Kemajuan bermain** boleh direset `admin` di antara validator bila akun cadangan dipakai ulang.
- Akun dinonaktifkan atau dihapus setelah validasi langkah 1 selesai. Untuk media langkah 2, akun dibuat ulang di instalasi uji versi tahap 3.

Cara teknis membuat akun siswa oleh `admin` (lewat pendaftaran biasa atau panel admin) diputuskan saat instalasi uji disiapkan. Dokumen ini tidak mengubah sistem.

## Pengembalian dan pencatatan

### Apa yang kembali

| Dari | Yang kembali |
| --- | --- |
| Penilai B.5a (2) | Lembar kerja B.5a, di putaran awal |
| Ahli materi (5) | Lembar kerja B.5b, B.7, B.9 (dan B.5a dari penilainya); lembar B.1, B.3, B.6, B.8, B.10 (berkas atau kertas); surat pernyataan D.1 bertanda tangan |
| Pengamat budaya (3) | Lembar kerja B.5–B.8; lembar B.1, B.3, B.9; surat pernyataan D.1 |
| Ahli media (3) | Lembar kerja B.3, B.5–B.8 atau kertasnya; tangkapan layar temuan; kesimpulan B.9; surat pernyataan D.1 (dengan daftar perangkat yang dipakai) |
| Putaran 2 | Lembar nilai ulang dan matriks D.2 yang diparaf |

Surat pernyataan memuat nama asli dan tanda tangan. Karena itu surat pernyataan **tidak pernah** masuk repo.

### Di mana disimpan

| Bahan | Tempat | Siapa yang bisa membuka |
| --- | --- | --- |
| Lembar asli dan lembar kerja dari validator, surat pernyataan, tangkapan layar | Folder penelitian di luar repo: `[lokasi penyimpanan peneliti]`, satu subfolder per validator, dinamai dengan **nama samaran** | Ketua peneliti dan petugas entri |
| Daftar padanan nama samaran ↔ nama asli | Satu berkas terenkripsi di luar repo, terpisah dari folder lembar | Ketua peneliti saja |
| Rekap hasil (skor per butir per nama samaran), hasil analisis, matriks revisi | Repo, setelah dibersihkan dari nama asli dan identitas | Tim |

Nama berkas yang diusulkan: `<samaran>_<lembar>_<putaran>_<tanggal terima>`, misalnya `ABA1_B5b_p1_2026-12-10.xlsx`. Singkatan samaran: `ABA1`, `ABA2`, `ALD1`, `ALD2`, `AP`, `ABK1`–`ABK3`, `AMP1`–`AMP3`.

### Siapa memasukkan dan siapa memeriksa

1. **Penerimaan.** Ketua peneliti mencatat setiap kiriman masuk di daftar penerimaan (samaran, lembar, tanggal, lengkap/tidak) dan menyimpan salinan aslinya **sebelum** dibuka atau diolah. Salinan asli tidak diubah.
2. **Pemeriksaan kelengkapan.** Dalam dua hari kerja: semua lembar W terisi, skor 1–2 punya catatan, TD punya alasan. Bila kurang, validator dihubungi sekali.
3. **Entri.** Lembar kerja digital digabung lewat lembar rekap dari 21 (tanpa ketik ulang). Lembar kertas diketik oleh `[petugas entri]`, anggota tim yang bukan validator.
4. **Pemeriksaan ganda.** Lembar kertas diketik dua kali oleh dua orang berbeda dan dibandingkan; setiap beda dicek ke lembar asli. Untuk lembar kerja digital, ketua peneliti mencocokkan 10% baris acak dengan berkas asli validator.
5. **Kunci data.** Setelah cocok, rekap dikunci (salinan baca saja bertanggal) dan baru dianalisis.

Claude boleh membantu menghitung indeks dari rekap yang sudah bersih, tetapi skor Claude tidak pernah ikut dihitung sebagai skor validator (T6 di [07](07_TELAAH_INSTRUMEN_VALIDASI.md#t6-penelaah-pendahuluan-berbantuan-ai)).

### Daftar padanan di luar repo

- Satu baris per validator: nama samaran, nama asli dan gelar, instansi, bidang, wilayah yang dikuasai (pengamat budaya), kontak, tanggal kesediaan, tanggal kirim dan terima per putaran, honorarium dibayar, izin disebut namanya di laporan (ya/tidak).
- Dipegang ketua peneliti, sama seperti pemegang akun staf ([03 → Akun staf](03_KODE_DAN_ARTI.md#akun-staf)).
- Tidak pernah disalin ke repo, ke tangkapan layar, ke percakapan dengan Claude, atau ke pesan grup.
- Di repo, di 04, di PR, dan di matriks revisi hanya nama samaran yang dipakai.

## Setelah hasil kembali

### Analisis per instrumen

Rumus dan ambangnya tidak diulang di sini. Ikuti bagian C instrumen:

| Instrumen | Bagian analisis | Aturan keputusan |
| --- | --- | --- |
| IM | C.1 rerata dan kategori; C.2 Aiken's V; C.3 I-CVI aturan Lynn dan S-CVI; C.4 κ dan κ berbobot, label akhir; C.5 PA dan ICC; C.7 kualitatif; C.9 pelaporan | C.6; skala sikap: aturan di lembar B.8 |
| IB | C.1 rerata; C.2 Aiken's V dan I-CVI aturan Lynn; C.3 matriks objek dan lembar telaah; C.4 reliabilitas; C.6 kualitatif; C.8 pelaporan | C.5; skala sikap: aturan di lembar B.8 IM |
| IMd | C.1 rerata; C.2 Aiken's V dan I-CVI; C.3 catatan temuan; C.4 uji perangkat; C.6 pelaporan | C.5 |

Urutan kerja: κ B.5a lebih dulu (minggu 3–4), karena label akhir menentukan apakah butir perlu dipindah di kisi-kisi. Lalu I-CVI dan V per butir, lalu lembar B.3, lalu catatan kualitatif dan veto.

### Matriks revisi Prototipe I

Satu matriks induk untuk ketiga instrumen. Matriks D.2 di setiap instrumen adalah potongan matriks induk ini yang dikirim ke validator pada putaran 2. Matriks induk ditulis sebagai dokumen baru di folder ini saat hasil pertama kembali; nomor dan namanya ditetapkan sesi koordinator dan dicatat di 00.

| No | Butir/bagian | Temuan | Sumber validator | Keputusan | Perubahan | Dokumen yang diubah | Perlu ditelaah ulang? |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | `wnb-3-a02` (contoh) | [ringkas temuan; kutip catatan validator bila perlu] | ABA1, ALD2 (B.5b skor 2) | Direvisi | [apa yang diubah] | 08 | Ya: semua penilai B.5b butir ini |
| 2 | IB B.5 objek no. [n] (contoh) | [temuan] | ABK2 (veto) | Direvisi | [apa yang diubah] | 08, 12 | Ya: ABK1–3 |
| 3 | mk-sd-03 (contoh) | [temuan] | AMP2 (B.8 skor 2) | Ditunda ke tahap 3 | Dicatat untuk dokumen implementasi | — | Ya: di media langkah 2 |

Isi kolom:

| Kolom | Isi |
| --- | --- |
| Butir/bagian | Kode butir (`wnb-3-a02`, `tes-mb-A07`, `skp-03`), kode lembar dan butir instrumen (IM B.3 D4), nomor objek, atau kode mock-up |
| Temuan | Satu temuan per baris; gabungkan temuan yang sama dari beberapa validator |
| Sumber validator | Nama samaran singkat dan lembarnya; tandai **veto** bila termasuk catatan kritis (IM C.6, IB C.5) atau keparahan 4 / V/R ≥ 3 (IMd C.5) |
| Keputusan | Dipertahankan · revisi kecil · revisi besar · diganti · dihapus · dipindah (label atau kisi-kisi) · ditunda ke tahap 3 · tidak diikuti (alasan wajib) |
| Perubahan | Apa yang diubah, cukup untuk dibandingkan dengan versi sebelumnya |
| Dokumen yang diubah | Nomor dokumen repo (08, 12–18, 06, 11) atau instrumen |
| Perlu ditelaah ulang? | Ya (siapa) / tidak (mengapa); lihat aturan di bawah |

Kode butir lama tidak dipakai ulang untuk isi baru (IM C.6, IB C.5). Butir pengganti mendapat kode baru.

### Kapan butir yang direvisi kembali ke validator

| Keadaan | Kembali ke | Dasar |
| --- | --- | --- |
| Butir soal I-CVI < 1,00 atau V < 0,80, lalu direvisi | Semua penilai butir itu di putaran 2 | IM C.6, IB C.5 |
| Butir B.3 (mutu rancangan) V < 0,80 atau ada skor 1–2 | Semua penilai B.3 panel itu, untuk bagian yang diperbaiki | IM C.6 |
| Catatan kritis (veto) | Validator yang memberi catatan **dan** semua penilai butir itu | IM C.6, IB C.5 |
| Butir dengan kurang dari tiga penilaian non-TD | Penilai tambahan dari panel; butir belum lolos | IM C.6, IB C.5, IMd A.3 |
| κ < 0,70 | Dua penilai B.5a: pedoman dipertajam, butir yang tidak sepakat dibahas, sampel dilabel ulang | IM C.4, C.6 |
| Pasangan tes A–B dinilai ≤ 2 oleh dua ahli | Ahli yang menilai B.9a; selisih proporsi benar dicek lagi di uji coba | IM C.6 |
| Pernyataan skala sikap direvisi atau diganti, termasuk usulan kalimat Ahli Bahasa Anak | Keempat penilai relevansi (Ahli Budaya Kedu 1, Ahli Pengukuran, Ahli Bahasa Anak 1, 2) | lembar B.8 IM |
| Contoh budaya di skala sikap diganti | Ketiga pengamat budaya | lembar B.8 IM |
| Temuan media keparahan 3 | Putaran 2 atau media langkah 2 | IMd C.5 |
| Skor 1–2 pada mock-up (B.8 IMd) | Media langkah 2, pada layar jadi | IMd C.5 |
| Revisi kecil (skor 3, catatan kecil) | Tidak dikirim ulang; cukup tercatat di matriks | IM A.5 Tabel A2 |
| Perbedaan versi budaya yang sah | Tidak dikirim ulang; dicatat sebagai versi | IB C.5 |

Putaran 2 dikirim dengan matriks D.2 instrumen yang bersangkutan, hanya berisi baris untuk validator itu. Kalau butir masih gagal di putaran 2, butir diganti atau dihapus, dan butir pengganti dinilai lagi oleh semua penilainya.

### Pencatatan di 04

- **Satu entri per putaran per panel** setelah keputusan revisi diambil, misalnya "Prototipe I, revisi dari validasi ahli materi putaran 1". Isinya: jumlah butir dipertahankan, direvisi, diganti, dan dihapus; κ yang dicapai; veto yang ditindaklanjuti; tautan ke matriks induk; dokumen yang diubah.
- **Entri tersendiri** untuk keputusan yang mengubah rancangan, bukan hanya butir: label akhir yang memindah butir di kisi-kisi 06, jumlah akhir butir risiko daring (dua atau tiga, setelah B.9b), tahun kebun teh Tambi dan letak kompleks Candi Arjuna (dari B.7 IB), perubahan skala sikap di bawah batas akhir 10 butir.
- Setiap entri menyebut versi bahan yang dinilai (tanggal dan *commit*) dan versi sesudah revisi.
- Nama asli validator tidak pernah ditulis di 04.

## Daftar periksa validasi untuk gerbang G2

Gerbang G2 butuh dua separuh: **validasi** (di sini) dan **izin etik** (di 22). Kolom "Dasar" menunjuk syarat lolos di instrumen.

### Ahli materi

- [ ] Rerata keseluruhan lembar B.3 minimal "valid" (IM C.6 syarat a).
- [ ] Semua butir bank dan tes memenuhi aturan Lynn setelah revisi (I-CVI = 1,00 dari B.5b), dan S-CVI/Ave setiap dimensi ≥ 0,90 (IM C.3, C.6 syarat b).
- [ ] Setiap butir punya minimal tiga penilaian non-TD (IM C.6).
- [ ] κ label dimensi dan level ≥ 0,70; untuk label berurutan (level AKM, Bloom) κ berbobot kuadratik ≥ 0,70, dan κ biasa ikut dilaporkan (IM C.4, C.6 syarat c; [01 → Peta jalan](01_REVISI_10_ANALITIK.md#peta-jalan-dan-daftar-kerja), keputusan 25 Oktober 2026).
- [ ] Label akhir ditetapkan untuk semua butir; butir yang labelnya tidak cocok dengan kisi-kisi sudah direvisi atau dipindah, dan 06 disesuaikan (IM C.4, C.6).
- [ ] Setiap "Tanpa teks?" atau "Bocor?" bernilai Y sudah diperiksa dan, bila terbukti, butirnya direvisi (IM C.6).
- [ ] Pasangan tes A–B yang dinilai tidak setara sudah direvisi (IM C.6).
- [ ] Jumlah akhir butir risiko daring (dua atau tiga) ditetapkan dari B.9b (IM D.3, "Hal yang masih terbuka").
- [ ] Semua catatan kritis (veto) ditindaklanjuti (IM C.6 syarat d).

### Pengamat budaya

- [ ] Rerata keseluruhan lembar B.3 minimal "valid" (IB C.5 syarat a).
- [ ] Semua butir B.3 dan butir PBK memenuhi aturan Lynn setelah revisi (IB C.5 syarat b).
- [ ] Semua petunjuk *Periksa Unggahan* lolos dari sisi budaya, dan butir D3 lolos juga di B.5b ahli materi ([07 → Instrumen versi 2.1 dan 1.1](07_TELAAH_INSTRUMEN_VALIDASI.md#instrumen-versi-21-dan-11), no. 4; IB C.5 syarat c).
- [ ] Semua catatan kritis dan semua "Tidak" di cek fakta B.7 ditindaklanjuti, termasuk tahun kebun teh Tambi dan letak kompleks Candi Arjuna (IB C.5 syarat d).
- [ ] Gambar baru Wayang Kedu Temanggungan dan Candi Pawon sudah dinilai di B.5 (IB D.3).

### Skala sikap (B.8)

- [ ] Setiap pernyataan I-CVI = 1,00 dari empat penilai relevansi; S-CVI/Ave ≥ 0,90 (lembar B.8 IM).
- [ ] Indikator tepat, arah negatif, contoh budaya, dan tumpang tindih D3 sudah ditindaklanjuti sesuai aturannya (lembar B.8 IM).
- [ ] Batas akhir terpenuhi: paling sedikit 10 butir, dua per indikator, butir negatif di setidaknya tiga indikator (lembar B.8 IM).

### Ahli media langkah 1

- [ ] Rerata keseluruhan minimal "layak" (IMd C.5).
- [ ] Tidak ada temuan berkeparahan 3–4 yang terbuka; temuan veto (keparahan 4, atau V/R ≥ 3) sudah diperbaiki atau dijadwalkan sebelum uji coba (IMd C.5).
- [ ] Jenis perangkat sekolah lulus uji perangkat (IMd C.5, C.4).
- [ ] Skor 1–2 pada mock-up tercatat untuk dokumen implementasi tahap 3 dan media langkah 2 (IMd C.5). Media langkah 2 sendiri masuk G3, bukan G2.

### Uji keterbacaan

- [ ] Uji keterbacaan dengan 3–5 anak kelas 5 selesai pada bahan versi setelah revisi validasi ahli, dan revisinya dicatat di 04 (24; daftar kerja no. 23). Butir ini baru bisa dicentang setelah izin etik terbit.

### Dokumentasi

- [ ] Sebelas surat pernyataan diterima dan diarsipkan di luar repo.
- [ ] Rekap hasil diperiksa ganda dan dikunci.
- [ ] Matriks revisi induk lengkap; semua baris punya keputusan; baris "perlu ditelaah ulang" sudah dinilai ulang.
- [ ] Entri 04 untuk setiap putaran per panel, dengan versi sebelum dan sesudah revisi.
- [ ] Hasil validasi ditulis untuk laporan mengikuti IM C.9, IB C.8, dan IMd C.6.

## Pertanyaan untuk pemilik proyek

1. **Siapa dua penilai pelabelan buta (B.5a)?** Usulan: Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1. Alasan: saran IM A.3, supaya sisi membaca dan sisi digital konstruk sama-sama terwakili. Pilihan lain: Ahli Pengukuran dan satu ahli literasi digital (lebih kuat di level dan Bloom, lebih lemah di D1).
2. **Bagaimana honorarium ditulis di surat?** Usulan: besaran disebut terang per panel di surat (`[besaran honorarium]`), karena instrumen meminta honorarium "dicantumkan di surat permohonan" dan beban ahli materi (± 7,5–24 jam) jauh lebih besar dari ahli media (± 4–5,5 jam). Pilihan lain: "sesuai ketentuan yang berlaku" tanpa angka.
3. **Paket dikirim kertas atau digital?** Usulan: digital. PDF instrumen dan lembar kerja lewat folder bersama yang dibatasi per validator; surat pernyataan ditandatangani lalu dipindai (aslinya menyusul lewat pos bila lembaga meminta). Alasan: lembar per butir memang dirancang sebagai lembar kerja, dan pengembalian digital mengurangi entri ulang. Kertas tetap disediakan bagi validator yang memintanya.
4. **Apakah ahli media langkah 1 menunggu sesuatu?** Usulan: tidak menunggu L8; dikirim bersama panel lain begitu instrumen disetujui, memakai daftar jenis perangkat sekolah sampel sebagai pengganti L8 (IMd D.3 no. 7 membolehkannya). Alasan: L8 menunggu daftar kode sekolah (no. 12) yang masih ditunda. Pilihan lain: menunggu sampai satu sekolah setuju dan L8 terisi.

## Akibat ke dokumen lain

| Dokumen | Bagian | Perubahan |
| --- | --- | --- |
| [03](03_KODE_DAN_ARTI.md) | *Akun staf* atau bagian baru sesudahnya | Tambah baris akun siswa uji validator `uji.bahasa1`–`2`, `uji.digital1`–`2`, `uji.ukur`, `uji.budaya1`–`3`, `uji.media1`–`3`, `uji.cadangan1`–`2`: akun siswa di instalasi uji, dibuat `admin`, bukan akun staf |
| [03](03_KODE_DAN_ARTI.md) | *Peran* | Tambah singkatan samaran untuk nama berkas: `ABA1`, `ABA2`, `ALD1`, `ALD2`, `AP`, `ABK1`–`ABK3`, `AMP1`–`AMP3` |
| [01](01_REVISI_10_ANALITIK.md) | *Peta jalan dan daftar kerja*, no. 3 dan 14 | Status: draf paket di 20; penilai B.5a mengikuti jawaban pertanyaan 1 |
| [dokumen-pdf/README.md](dokumen-pdf/README.md) | *Sebelum dikirim ke validator* | Tambah langkah 5: "Ikuti prasyarat dan urutan kirim di [20](../20_PAKET_VALIDASI.md)" |
| [07](07_TELAAH_INSTRUMEN_VALIDASI.md) | *Langkah berikutnya* | Tambah butir: paket validasi disusun di 20 |
| Instrumen IM, IB, IMd (`dokumen-pdf/src/*.html`) | D.3 | Tidak wajib. Bila pemilik proyek setuju, IB D.3 dan IMd D.3 diberi kolom "Kapan dikirim" seperti IM D.3, supaya cocok dengan tabel *Siapa menerima apa* (IMd no. 10 setelah B.6; IB no. 6 gambar baru menyusul) |
| 22 | Lampiran dan perubahan bahan | Sebut bahwa instrumen untuk anak yang dilampirkan adalah versi yang dibekukan untuk validasi, dan revisi Prototipe I yang mengubah bahan untuk anak dilaporkan ke *Lembaga Komite Etik* |
| 24 | Bahan uji | Bahan yang diuji adalah versi setelah revisi validasi ahli |
| Matriks revisi induk (dokumen baru) | — | Dibuat saat hasil pertama kembali; nomor ditetapkan sesi koordinator dan dicatat di 00 |
