# Paket Validasi Ahli

Per 27 Oktober 2026 · **Status: draf**; pertanyaan pengembang dijawab 27 Oktober 2026; diselaraskan dengan 21–24 pada hari yang sama; cara memenuhi prasyarat no. 4, 8, 9, dan 10 ditulis 27 Oktober 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md)

Dokumen ini mengubah tiga instrumen validasi ahli menjadi paket yang siap dikirim ke sebelas validator. Isinya: siapa menerima apa dan kapan, surat permohonan, akses ke instalasi uji, cara hasil dikembalikan dan dicatat, apa yang terjadi setelah hasil kembali, dan daftar periksa validasi untuk gerbang G2. Daftar kerja no. 3 dan no. 14 di [01](01_REVISI_10_ANALITIK.md#peta-jalan-dan-daftar-kerja). Pekerjaan ini bagian dari **Prototipe I**.

Dokumen tahap 2 lain yang dirujuk:

| Berkas | Isi |
| --- | --- |
| `21_BERKAS_BUTIR_VALIDATOR.md` dan `dokumen-pdf/berkas-validator/` | Daftar butir buta B.5a, daftar B.5b, lembar kerja per lembar instrumen |
| `22_PERMOHONAN_IZIN_ETIK.md` | Protokol dan permohonan izin etik; separuh etik gerbang G2 |
| `23_SURAT_INFORMASI_DAN_PERSETUJUAN.md` | Surat informasi dan persetujuan orang tua, anak, sekolah, guru |
| `24_UJI_KETERBACAAN.md` | Protokol uji keterbacaan dengan empat anak kelas 5, ditambah satu anak di putaran kedua |

Ketiga instrumen ada di [dokumen-pdf/](dokumen-pdf/README.md), sumbernya di `dokumen-pdf/src/*.html`. Di dokumen ini disingkat:

| Singkatan | Instrumen |
| --- | --- |
| **IM** | Instrumen Validasi Ahli Materi v2.1 (`instrumen-validasi-ahli-materi-v2.1`) |
| **IB** | Instrumen Validasi Pengamat Budaya v2.1 (`instrumen-validasi-pengamat-budaya-v2.1`) |
| **IMd** | Instrumen Validasi Ahli Media v1.1, langkah 1 (`instrumen-validasi-ahli-media-v1.1`) |

Nomor berkas di dokumen ini selalu nomor di tabel D.3 instrumennya, misalnya "IM D.3 no. 5".

## Ringkasan

**Apa paketnya.** Satu paket per validator: surat permohonan, instrumen yang sesuai panelnya, berkas bahan dari tabel D.3 instrumen itu, lembar kerja dari 21, dan akses ke instalasi uji. Ahli materi menerima paketnya dalam dua kiriman, karena pelabelan buta (B.5a) harus selesai sebelum label pengembang dibuka. Dua penilai B.5a menerima **paket B.5a saja** di kiriman awal; instrumen lengkap dan semua bahan lain baru dikirim setelah lembar B.5a mereka kembali.

Di dokumen ini, **kiriman** berarti pengiriman bahan (kiriman awal, kiriman setelah B.5a, kiriman setelah B.6 media), sedangkan **putaran** hanya dipakai untuk putaran validasi: putaran 1 adalah penilaian pertama, putaran 2 penilaian ulang butir yang direvisi. 21 memakai istilah yang sama.

**Siapa.** Sebelas validator, semuanya dengan nama samaran dari [03 → Peran](03_KODE_DAN_ARTI.md#peran):

| Panel | Validator | Instrumen |
| --- | --- | --- |
| Ahli materi (5) | Ahli Bahasa Anak 1, 2; Ahli Literasi Digital 1, 2; Ahli Pengukuran | IM |
| Pengamat budaya (3) | Ahli Budaya Kedu 1, 2, 3 | IB |
| Ahli media (3) | Ahli Media Pembelajaran 1, 2, 3 | IMd (langkah 1) |

Dua dari lima ahli materi juga menjadi penilai B.5a. IM A.3 menyarankan satu ahli bahasa anak dan satu ahli literasi digital. Pengembang menetapkan **Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1** (ABA1 dan ALD1; 27 Oktober 2026, [Keputusan pengembang](#keputusan-pengembang)).

**Kapan boleh dikirim.** Validator tidak bekerja dengan anak, jadi validasi tidak menunggu izin etik. Uji keterbacaan dengan anak (24) tetap menunggu izin etik. Paket baru boleh dikirim setelah semua prasyarat ini terpenuhi:

| No | Prasyarat | Sumber | Status per 27 Oktober 2026 |
| --- | --- | --- | --- |
| 1 | Pengembang menyetujui ketiga instrumen | [dokumen-pdf/README.md → Sebelum dikirim ke validator](dokumen-pdf/README.md#sebelum-dikirim-ke-validator), [00](00_INDEKS.md) | Sudah (27 Oktober 2026) |
| 2 | Kotak *STATUS* di sampul HTML dihapus, PDF dibuat ulang; lampiran D.4 tidak dicetak untuk validator | README yang sama, langkah 2–3 | Sudah (27 Oktober 2026) |
| 3 | Dokumen bahan yang dinilai sudah disetujui pengembang (keputusan diambil): 08, 12–18, kamus-1.0 di 11 | IM A.4 "Prasyarat", [00 → Tahap dokumen 1](00_INDEKS.md#tahap-dokumen-yang-tersisa-sebelum-implementasi) | Keputusan sudah diambil; 13–17 berstatus draf menunggu validasi, dan memang itu yang divalidasi |
| 4 | Versi bahan dibekukan: tanggal dan *commit* `main` yang dinilai dicatat di 04, dan ditulis di surat pernyataan (D.1, "versi/tanggal") | IM A.4, IB A.4; [Prasyarat no. 4](#prasyarat-no-4-membekukan-versi-bahan) | Belum; prosedur ditulis 27 Oktober 2026, dijalankan setelah no. 6, 8, 9, dan 10 selesai |
| 5 | Telaah pendahuluan bank soal selesai | IM A.4, [09](09_TELAAH_PENDAHULUAN_BANK_SOAL.md) | Selesai (R1–R4 disetujui) |
| 6 | Cek fakta budaya oleh tim selesai dan tercatat di lembar B.7 IB | IB A.4, IB D.3 no. 9; [25](25_CEK_FAKTA_BUDAYA.md) | Sudah (27 Oktober 2026): 135 klaim dicek, 44 fakta tercatat di lembar B.7 IB dengan kode sumber dari 25; pengamat budaya mengonfirmasinya di B.7 |
| 7 | Daftar butir buta B.5a, daftar B.5b, dan lembar kerja per lembar siap | IM D.3 no. 2, 5; IB D.3 no. 11; IMd D.3 no. 9; dibuat di 21 | Dibuat (`berkas-1.0`, [21](21_BERKAS_BUTIR_VALIDATOR.md)); dibuat ulang sekali lagi tepat sebelum kirim, dan versi berkasnya dicatat |
| 8 | Instalasi uji siap, dengan akun uji validator | IM D.3 no. 12, IB D.3 no. 8, IMd D.3 no. 2–3; [Akses untuk validator](#akses-untuk-validator), [Prasyarat no. 8](#prasyarat-no-8-instalasi-uji-dan-akun-uji) | Belum; prosedur ditulis 27 Oktober 2026 (komputer lab kampus, subdomain HTTPS, akun dibuat pengembang lewat `/daftar`, semua wilayah terbuka); menunggu subdomain dari TI kampus |
| 9 | Nama asli validator ditetapkan, kesediaannya didapat lisan atau lewat pesan, dan tercatat di daftar padanan di luar repo | [Pengembalian dan pencatatan](#pengembalian-dan-pencatatan), [Prasyarat no. 9](#prasyarat-no-9-nama-asli-dan-kesediaan-validator) | Belum; templat daftar padanan dan pesan kesediaan siap 27 Oktober 2026, ditanyakan ketua peneliti (pertanyaan terbuka di 01) |
| 10 | Besaran honorarium dan sumber dananya diketahui ketua peneliti; di surat ditulis "sesuai ketentuan yang berlaku" | IM A.4, IB A.4; [Keputusan pengembang](#keputusan-pengembang) no. 2; [Prasyarat no. 10](#prasyarat-no-10-honorarium) | Belum; yang perlu diketahui ketua peneliti ditulis 27 Oktober 2026, besaran tidak pernah masuk repo |
| 11 | IM disesuaikan dengan urutan kirim di dokumen ini: pembuka B.5a menyebut ID butir netral, D.3 menyebut nama berkas dari 21 dan urutan kirim untuk dua penilai B.5a, tabel waktu A.4 memakai perkiraan B.7 yang baru | [21 → Akibat ke dokumen lain](21_BERKAS_BUTIR_VALIDATOR.md#akibat-ke-dokumen-lain), [Akibat ke dokumen lain](#akibat-ke-dokumen-lain) | Sudah (27 Oktober 2026, PR #268) |

Daftar kode sekolah (no. 12) **tidak** menahan paket ini ([04](04_CATATAN_KEPUTUSAN.md), 27 Oktober 2026). Yang tersentuh hanya IMd D.3 no. 7, dan berkas itu punya pengganti (daftar jenis perangkat).

Dua hal boleh menyusul setelah paket dikirim, karena instrumennya sendiri sudah menyebutnya menyusul: gambar baru Wayang Kedu Temanggungan dan Candi Pawon untuk IB B.5 (IB D.3, "Hal yang masih terbuka"), dan daftar temuan telaah pendahuluan media (IMd D.3 no. 10, dikirim setelah B.6 dikumpulkan).

## Siapa menerima apa

Lembar yang diisi diambil dari matriks penugasan setiap instrumen: IM Tabel A1 dan tabel penilai B.8; IB A.3, A.4, dan tabel penilai B.8; IMd A.3. Tanda: **W** = wajib, tanpa TD; **○** = diisi bila sesuai keahlian (TD boleh). Semua validator juga mengisi lembar identitas (B.1), kesimpulan (IM B.10, IB B.9, IMd B.9), dan surat pernyataan (D.1).

Kiriman: **awal**; **setelah B.5a** (setelah kedua lembar B.5a dikumpulkan); **setelah B.6 media** (setelah lembar B.6 ahli media dikumpulkan, masih langkah 1); **media langkah 2** (setelah tahap 3, untuk gerbang G3).

Perkiraan jam dijumlahkan dari tabel waktu instrumen (IM A.4 "Perkiraan waktu dan cara mengisi", IB A.4, IMd A.4). Belum termasuk pengarahan (IM 45 menit, IB 30–45 menit, IMd 30 menit). IM tidak memberi angka untuk bermain satu wilayah, jadi itu juga belum termasuk.

**Waktu B.7 dinaikkan.** Lembar B.7 mencakup semua 622 sel kode (keputusan pengembang di [21](21_BERKAS_BUTIR_VALIDATOR.md#pertanyaan-untuk-pengembang), pertanyaan 4): 300 kode yang tertulis di tabel 08 dan 322 kode dari aturan tetap. Perkiraan IM A.4 (2–3 jam) dipakai untuk 300 kode tertulis, ditambah 1–2 jam untuk 322 sel aturan tetap, yang bisa disaring lewat kolom "Asal kode" dan dinilai per aturan. Jadi B.7 diperkirakan **3–5 jam** (dikonfirmasi pengembang, 27 Oktober 2026) untuk setiap validator yang mengisinya (Ahli Literasi Digital 1 dan 2 wajib; Ahli Bahasa Anak 1, 2 dan Ahli Pengukuran bila diisi atau diminta). Versi pengodean buta (`materi-b7-kode-kesalahan-buta.xlsx`) lebih lama, karena setiap sel diberi kode sendiri tanpa kolom "Asal kode"; waktunya disepakati dengan validator bila versi itu diminta. Angka di tabel di bawah sudah memakai perkiraan baru ini.

### Panel ahli materi (IM)

| Validator | Lembar yang diisi | Berkas IM D.3: kiriman awal | Berkas IM D.3: kiriman setelah B.5a | Perkiraan jam |
| --- | --- | --- | --- | --- |
| Ahli Bahasa Anak 1 *(penilai B.5a)* | **B.5a**; B.3 aspek A, B, D, G, H, J (W), C, I (○), E, F (○); B.5b (W); B.6 (W); B.7 (○); B.8a relevansi, arah negatif, telaah bahasa, dan B.8b (W); B.9a Bagian 1 (W); B.9b bahasa (W) | **Paket B.5a saja:** 1 (surat saja), **2**, halaman petunjuk B.5a | 1 (instrumen lengkap), 3, 4 (dengan label pengembang), 5, 6, 7, 8, 9, 10, 11, 12 | B.5a 6–8 (minggu 1–2) + lainnya 9–14 (minggu 3–5) = **± 15–22 jam** (B.7 bila diisi: + 3–5) |
| Ahli Bahasa Anak 2 | B.3 seperti Ahli Bahasa Anak 1; B.5b (W); B.6 (W); B.7 (○); B.8 seperti Ahli Bahasa Anak 1; B.9a Bagian 1 (W); B.9b bahasa (W) | 1, 3, 4 (tanpa label), 7, 8 (tanpa label), 9, 10, 11, 12 | 4 (label pengembang), 5, 6 | **± 9–14 jam** (B.7 bila diisi: + 3–5) |
| Ahli Literasi Digital 1 *(penilai B.5a)* | **B.5a**; B.3 aspek A, B, D, E, F, H, J (W), C, I, G (○); B.5b (W); B.6 (W); B.7 (W); B.8a tumpang tindih D3 saja (W); B.9a Bagian 2–3 (W); B.9b isi (W) | **Paket B.5a saja:** 1 (surat saja), **2**, halaman petunjuk B.5a | 1 (instrumen lengkap), 3, 4 (dengan label pengembang), 5, 6, 7, 8, 9, 10, 11, 12 | B.5a 6–8 (minggu 1–2) + lainnya 12–18 (minggu 3–5) = **± 18–26 jam** |
| Ahli Literasi Digital 2 | B.3 seperti Ahli Literasi Digital 1; B.5b (W); B.6 (W); B.7 (W); B.8a tumpang tindih D3 saja (W); B.9a Bagian 2–3 (W); B.9b isi (W) | 1, 3, 4 (tanpa label), 7, 8 (tanpa label), 9, 10, 11, 12 | 4 (label pengembang), 5, 6 | **± 12–18 jam** |
| Ahli Pengukuran | B.3 aspek A, B, C, H, I, J (W), D, E, F, G (○); B.5b (W); B.6 (○); B.7 (○); B.8a relevansi, indikator tepat, arah negatif, dan B.8b (W); B.9a (W); B.9b (○) | 1, 3, 4 (tanpa label), 7, 8 (tanpa label), 9, 10, 11, 12 | 4 (label pengembang), 5, 6 | **± 7,5–12 jam** (B.6 dan B.7 bila diminta: + 4,5–7) |

Catatan untuk panel ini:

- **Dua penilai B.5a menerima paket B.5a saja dulu** (keputusan pengembang di [21](21_BERKAS_BUTIR_VALIDATOR.md#pertanyaan-untuk-pengembang), pertanyaan 2): surat permohonan, berkas no. 2 (lembar kerja `materi-b5a-daftar-butir-buta.xlsx` dan buklet `materi-b5a-buklet-buta.pdf`), dan halaman petunjuk B.5a dari IM, tanpa bagian instrumen lain. Butir diberi ID netral `BT-001` … `BT-180` dan bahan bacaan `BH-01` … `BH-73`, bukan kode butir. Instrumen lengkap, bank berkode, kisi-kisi, daftar B.5b, lembar kerja B.6–B.9, dan berkas lain baru dikirim setelah kedua lembar B.5a kembali, karena kode kesalahan dan beberapa bagian instrumen menunjukkan dimensi dan sub-aspek (21, temuan no. 1). Ini mengubah kolom "Kapan dikirim" IM D.3 untuk kedua penilai itu; IM disesuaikan lewat prasyarat no. 11.
- **Tiga ahli materi lain** (Ahli Bahasa Anak 2, Ahli Literasi Digital 2, Ahli Pengukuran) menerima kiriman awal seperti di IM D.3. Berkas no. 4 dan no. 8 dikirim **tanpa** kolom dimensi, sub-aspek, level, dan Bloom. Label pengembang dan kisi-kisi (no. 6) baru dikirim setelah B.5a dikumpulkan (IM A.4 "Pelabelan buta lebih dulu", IM D.3 no. 4–6), supaya tidak ada salinan berlabel yang beredar di panel sebelum B.5a selesai.
- Lembar yang dikerjakan tiga ahli materi lain setelah kiriman awal: B.6, B.7, B.8, B.9, dan B.3 kecuali bagian yang butuh kisi-kisi. B.5b dan sisa B.3 dikerjakan begitu kiriman setelah B.5a diterima. Dua penilai B.5a mengerjakan B.5a saja di minggu 1–2, lalu semua lembar lain di minggu 3–5 (lihat [Beban dua penilai B.5a](#beban-dua-penilai-b5a-di-minggu-35)).
- Bila sebuah butir B.3 atau B.5b punya kurang dari tiga penilaian non-TD, peneliti meminta ahli bertanda ○ mengisinya (IM catatan di bawah Tabel A1). Jadi jam Ahli Pengukuran bisa bertambah.

### Panel pengamat budaya (IB)

IB tidak punya tabel penugasan per orang. Ketiga pengamat budaya menilai semua butir B.3 dan semua butir PBK (IB A.3 "Alasan tiga orang"), mengisi B.5–B.7, dan di matriks objek B.5 menilai objek yang dikuasainya (IB A.3 "Etika validator"). Yang berbeda hanya B.8. Semua berkas IB D.3 dikirim di kiriman awal (tabel D.3 IB tidak punya kolom "Kapan dikirim"); berkas no. 3 tanpa label level dan Bloom, dan label itu memang tidak dikirim ke panel ini. Berkas no. 11 adalah `budaya-b5-b8-lembar-kerja.xlsx` dari 21.

| Validator | Lembar yang diisi | Berkas IB D.3: kiriman awal | Menyusul | Perkiraan jam |
| --- | --- | --- | --- | --- |
| Ahli Budaya Kedu 1 | B.3 (semua butir); B.5 matriks objek; B.6a *Periksa Unggahan* dan B.6b PBK; B.7 cek fakta; B.8 relevansi 1–4, indikator tepat, contoh budaya, B.8b (semua wajib) | 1–11 | Gambar Wayang Kedu Temanggungan dan Candi Pawon (bagian berkas no. 6) | bahan dan bermain 3–4, B.5 2–3, B.6 1,5–2, B.7 1–1,5 (44 fakta), B.8 0,5–0,75, B.3 1–2 = **± 9–13,5 jam** |
| Ahli Budaya Kedu 2 | B.3; B.5; B.6a, B.6b; B.7; B.8 contoh budaya untuk wilayahnya (wajib), B.8b (boleh) | 1–11 | Sama | **± 8,5–12,5 jam** (B.8 10 menit) |
| Ahli Budaya Kedu 3 | Sama dengan Ahli Budaya Kedu 2 | 1–11 | Sama | **± 8,5–12,5 jam** |

Idealnya satu orang paling mengenal Temanggung, satu Magelang, satu Wonosobo (IB A.3). Pembagian wilayah ini dicatat di daftar padanan di luar repo, bukan di sini.

### Panel ahli media (IMd)

Ketiga ahli media mengisi lembar yang sama. Satu pengecualian: butir teknis H3 hanya diisi Ahli Media Pembelajaran 3, sebagai daftar periksa lulus/tidak; yang lain mengisi TD (IMd A.3). Dari berkas no. 9, hanya lembar B.8 (`media-b8-mockup.xlsx`) yang tersedia sebagai lembar kerja; B.3 dan B.5–B.7 diisi di PDF instrumen atau di kertas, karena 21 tidak membuat lembar kerjanya (21, temuan no. 7).

| Validator | Lembar yang diisi | Berkas IMd D.3: kiriman awal | Kiriman setelah B.6 media | Media langkah 2 | Perkiraan jam |
| --- | --- | --- | --- | --- | --- |
| Ahli Media Pembelajaran 1 (teknologi pendidikan) | B.3 (H3 diisi TD); B.5 uji perangkat; B.6 temuan; B.7 per layar; B.8 mock-up | 1–9 | 10 | Paket langkah 2 (lihat bawah) | bermain 2–3, mock-up 1–1,5, B.3 1 = **± 4–5,5 jam** |
| Ahli Media Pembelajaran 2 (desain visual atau UX) | Sama dengan Ahli Media Pembelajaran 1 | 1–9 | 10 | Sama | **± 4–5,5 jam** |
| Ahli Media Pembelajaran 3 (rekayasa perangkat lunak atau game) | B.3 termasuk H3 (daftar periksa lulus/tidak); B.5; B.6; B.7; B.8 | 1–9 | 10 | Sama | **± 4–5,5 jam** |

Setiap ahli media menguji minimal tiga perangkat: laptop atau Chromebook, tablet mendatar 10 inci atau lebih, dan satu perangkat seperti milik sekolah sampel (IMd A.4). Ponsel tidak diuji. Bila validator tidak punya tablet atau perangkat sekolah, tim meminjamkannya; ini disebut di surat.

**Media langkah 2** belum punya instrumen sendiri. IMd A.1 hanya menyebut isinya: telaah singkat layar jadi di instalasi uji (gerak, suara, waktu muat, papan ketik sentuh, dan semua butir bertanda mock-up dinilai ulang). Instrumen dan paketnya disusun di tahap 3 dan masuk gerbang G3 (daftar kerja no. 21), bukan G2.

### Berkas dari 21 per penerima

Nama berkas di folder [`dokumen-pdf/berkas-validator/`](dokumen-pdf/berkas-validator/), sama dengan [21 → Daftar berkas](21_BERKAS_BUTIR_VALIDATOR.md#daftar-berkas).

| Berkas | Nomor D.3 | Penerima | Kiriman |
| --- | --- | --- | --- |
| `materi-b5a-daftar-butir-buta.xlsx` | IM no. 2 | Ahli Bahasa Anak 1, Ahli Literasi Digital 1 (penilai B.5a) | Awal, sebagai paket B.5a |
| `materi-b5a-buklet-buta.pdf` | IM no. 2 | Sama, bila lebih suka membaca atau mengisi di kertas | Awal, bersama lembar kerjanya |
| `materi-b5b-relevansi-kejelasan.xlsx` | IM no. 5 | Kelima ahli materi | Setelah B.5a |
| `materi-b6-b9-lembar-kerja.xlsx` | Lembar kerja IM B.6–B.9 (bahannya no. 4, 8, 9, 10) | Kelima ahli materi; B.7 wajib untuk Ahli Literasi Digital 1 dan 2; B.8 menurut tabel penilai B.8 | Awal ke Ahli Bahasa Anak 2, Ahli Literasi Digital 2, Ahli Pengukuran; setelah B.5a ke dua penilai B.5a |
| `materi-b7-kode-kesalahan-buta.xlsx` | Pengganti lembar B.7 di berkas di atas | Ahli Literasi Digital 1 dan 2, hanya bila peneliti meminta pengodean buta | Bersama lembar kerja B.6–B.9 penerimanya: Ahli Literasi Digital 2 awal, Ahli Literasi Digital 1 setelah B.5a. Bagi Ahli Literasi Digital 2 pengodean ini tidak sepenuhnya buta, karena bank berkode (no. 4) ada di kiriman awal; pengembang menerima keadaan ini (27 Oktober 2026) |
| `budaya-b5-b8-lembar-kerja.xlsx` | IB no. 11 | Ahli Budaya Kedu 1–3 | Awal |
| `media-b8-mockup.xlsx` | IMd no. 9 (lembar B.8 saja) | Ahli Media Pembelajaran 1–3 | Awal |
| `khusus-peneliti/materi-b5a-kunci-peneliti.xlsx` | — | Ketua peneliti saja; **tidak pernah dikirim atau dilampirkan** | Dibuka setelah kedua lembar B.5a kembali |
| `LAPORAN.md`, `materi-b5a-buklet-buta.html` | — | Tim; tidak dikirim | Laporan dibaca sebelum paket dikirim; HTML hanya sumber buklet |

Halaman petunjuk B.5a di paket B.5a bukan berkas 21: halaman itu dicetak dari IM (bagian B.5a saja) setelah pembukanya menyebut ID netral (prasyarat no. 11). Lembar kerja dan buklet sendiri sudah memuat petunjuk dan definisi label yang sama kata demi kata dengan IM.

### Cek silang ke tabel D.3

| Instrumen | Nomor di D.3 | Masuk tabel di atas |
| --- | --- | --- |
| IM | 1–12 | Ke dua penilai B.5a: 1 (surat) dan 2 awal, sebagai paket B.5a; 1 (instrumen lengkap), 3–12 (no. 4 berlabel) setelah B.5a. Ke tiga ahli materi lain: 1, 3, 7, 9, 10, 11, 12 awal; 4 dan 8 awal tanpa label; 4 (label), 5, 6 setelah B.5a |
| IB | 1–11 | Semua awal ke ketiga pengamat budaya; gambar baru di no. 6 menyusul |
| IMd | 1–10 | 1–9 awal ke ketiga ahli media; 10 setelah B.6 media |

## Urutan pengiriman dan jadwal

### Kiriman dan putaran

1. **Minggu 0, kesediaan.** Ketua peneliti menghubungi calon validator (telepon atau pesan) untuk menanyakan kesediaan, waktu, dan perangkat. Kepada dua penilai B.5a juga disebut urutan kerjanya: B.5a dulu, lalu semua lembar lain dalam tiga minggu (lihat [Beban dua penilai B.5a](#beban-dua-penilai-b5a-di-minggu-35)). Baru setelah bersedia, surat resmi dikirim. Nama dan kontak dicatat di daftar padanan.
2. **Kiriman awal (minggu 1).** Kesebelas validator menerima surat permohonan. Sembilan validator menerima berkas kiriman awal panelnya; dua penilai B.5a menerima paket B.5a saja. Pengarahan daring per panel, terpisah supaya validator tidak saling memengaruhi (IM A.4 "Independen"): ahli materi 45 menit untuk tiga ahli materi lain, pengamat budaya 30–45 menit, ahli media 30 menit. Dua penilai B.5a tidak ikut pengarahan panel di minggu ini, karena pengarahan itu membahas kode; mereka mendapat pengarahan B.5a tersendiri, ± 15 menit, tentang cara melabel tanpa membuka bahan lain.
3. **Kiriman setelah B.5a (minggu 3).** Setelah kedua lembar B.5a masuk dan diarsipkan, kelima ahli materi menerima surat lanjutan. Tiga ahli materi lain menerima berkas IM D.3 no. 4 berlabel, no. 5 (`materi-b5b-relevansi-kejelasan.xlsx`), dan no. 6. Dua penilai B.5a menerima instrumen lengkap dan semua berkas IM D.3 lain (no. 3–12, no. 4 langsung berlabel), lembar kerja `materi-b5b-relevansi-kejelasan.xlsx` dan `materi-b6-b9-lembar-kerja.xlsx`, dan akses ke instalasi uji, lalu ikut pengarahan ahli materi 45 menit. Kiriman ini dikirim begitu lembar B.5a kedua diarsipkan, tanpa menunggu awal minggu 3.
4. **Kiriman setelah B.6 media (minggu 3–4).** Setiap ahli media yang sudah mengumpulkan B.6 menerima IMd D.3 no. 10.
5. **Putaran 2 validasi (minggu 10–11).** Hanya butir atau bagian yang direvisi, dengan matriks tindak lanjut D.2 (lihat [Setelah hasil kembali](#setelah-hasil-kembali)).
6. **Penilaian ulang setelah uji keterbacaan.** Setelah izin etik terbit dan uji keterbacaan (24) selesai, butir yang direvisi dari temuan anak, dan sudah dibaca ulang anak K05 di putaran kedua uji itu, dikirim ke penilai yang ditunjuk [24 → Yang kembali ke ahli](24_UJI_KETERBACAAN.md#yang-kembali-ke-ahli), dengan surat dan matriks seperti putaran 2.
7. **Media langkah 2.** Setelah tahap 3, sebelum uji coba (G3).

### Jadwal relatif

Batas waktu mengikuti saran instrumen: pengisian boleh dicicil 2–3 minggu (IM A.4, IB A.4).

| Minggu | Kegiatan | Pengingat | Batas |
| --- | --- | --- | --- |
| 0 | Prasyarat 1–11 dipenuhi; kesediaan validator; akun uji dibuat | — | — |
| 1 | Kiriman awal ke 11 validator (ke dua penilai B.5a: paket B.5a saja); pengarahan per panel, dan pengarahan B.5a untuk dua penilainya | Hari ke-3: cek semua validator bisa membuka berkas, dan sembilan validator selain penilai B.5a bisa masuk instalasi uji | — |
| 2 | Validator mengisi; dua penilai B.5a mengisi B.5a saja | Pengingat 1 ke semua (singkat, sekaligus tawaran bantuan) | Akhir minggu 2: **B.5a** dari dua penilai |
| 3 | Kiriman setelah B.5a ke 5 ahli materi (ke dua penilai B.5a: semua bahan lain dan akses); pengarahan ahli materi untuk dua penilai B.5a; peneliti mulai menghitung κ | — | Akhir minggu 3: **B.6 media** |
| 4 | Kirim IMd D.3 no. 10; bila κ < 0,70, pedoman pelabelan dipertajam dan sampel dilabel ulang (IM C.6) | Pengingat 2 ke semua yang belum selesai | Akhir minggu 4: **semua lembar ahli media dan pengamat budaya** |
| 5 | Tiga ahli materi lain menyelesaikan B.5b dan sisa B.3; dua penilai B.5a menyelesaikan semua lembar selain B.5a | Pengingat 3, dua hari sebelum batas | Akhir minggu 5: **semua lembar ahli materi** |
| 6–7 | Entri, pemeriksaan ganda, analisis per instrumen, matriks revisi | — | — |
| 8–9 | Revisi Prototipe I; dicatat di 04 | — | — |
| 10–11 | Putaran 2: butir yang direvisi dikirim ke penilainya | Satu pengingat di minggu 11 | Akhir minggu 11 |
| 12 | Daftar periksa validasi G2 diisi (kecuali uji keterbacaan, yang menunggu izin etik) | — | — |
| Setelah izin etik | Uji keterbacaan (24) pada bahan setelah revisi; revisi dari temuan anak; anak K05 membaca bahan yang direvisi; penilaian ulang ahli untuk butir yang kembali ([24 → Yang kembali ke ahli](24_UJI_KETERBACAAN.md#yang-kembali-ke-ahli)) | Satu pengingat | Ditetapkan saat jadwal uji keterbacaan diketahui |

Bila validator terlambat lebih dari satu minggu, ketua peneliti menelepon. Bila tetap tidak bisa, peneliti meminta penilai pengganti dari bidang yang sama, karena butir dengan kurang dari tiga penilaian non-TD belum boleh dinyatakan lolos (IM C.6, IB C.5).

#### Beban dua penilai B.5a di minggu 3–5

Dengan urutan kirim yang baru, Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1 mengerjakan B.5a (6–8 jam) di minggu 1–2, lalu menerima semua bahan lain di minggu 3. Batas akhir lembar ahli materi tetap akhir minggu 5, jadi sisa lembar mereka harus selesai dalam tiga minggu:

| Validator | Sisa lembar di minggu 3–5 | Per minggu | Bandingkan |
| --- | --- | --- | --- |
| Ahli Bahasa Anak 1 | ± 9–14 jam (± 12–19 bila B.7 diisi) | ± 3–6 jam | Ahli Bahasa Anak 2: ± 9–14 jam dalam lima minggu, ± 2–3 jam per minggu |
| Ahli Literasi Digital 1 | ± 12–18 jam, termasuk B.7 3–5 jam | ± 4–6 jam | Ahli Literasi Digital 2: ± 12–18 jam dalam lima minggu, ± 2,5–3,5 jam per minggu |

Usulan untuk meringankannya (keputusannya di tangan ketua peneliti):

1. Beban per minggu ini disebut saat menanyakan kesediaan di minggu 0, supaya calon penilai B.5a bisa menolak atau memilih jadwal.
2. Kiriman setelah B.5a dikirim begitu lembar B.5a kedua diarsipkan. Bila B.5a kembali lebih cepat dari batasnya, waktu untuk sisa lembar ikut bertambah.
3. Bila perlu, batas kedua penilai B.5a diundur sampai akhir minggu 6. Entri lembar ahli materi lain sudah bisa dimulai, dan κ B.5a tidak bergantung pada lembar itu, jadi analisis di minggu 6–7 tidak tertunda lebih dari beberapa hari.
4. B.7 (○) sebaiknya tidak diminta dari Ahli Bahasa Anak 1, kecuali peneliti membutuhkan penilai B.7 tambahan.

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
- Satu permohonan izin etik mencakup semua kegiatan dengan anak, KG1–KG5 (22). Permohonan itu melampirkan instrumen untuk anak (tes, angket, skala sikap) dalam versi yang dibekukan untuk validasi. Bila revisi Prototipe I mengubah bahan yang dilihat anak, perubahannya diajukan ke *Lembaga Komite Etik* sebagai amandemen protokol (22 → *Perubahan protokol*; tata caranya tergantung komite, `needs_verification`).
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
| `[cara pembayaran]` | Honorarium ditulis "sesuai ketentuan yang berlaku", tanpa angka ([Keputusan pengembang](#keputusan-pengembang) no. 2) |
| `[tanggal pengarahan]`, `[tanggal batas]`, `[tanggal batas B.5a]`, `[tanggal kiriman kedua]`, `[tanggal pengarahan kedua]` | Dari jadwal; dua yang terakhir untuk kiriman setelah B.5a |
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
> [Hanya untuk dua penilai B.5a:] Bapak/Ibu juga kami mohon menjadi salah satu dari dua **penilai pelabelan buta** (lembar B.5a). Lembar ini dikerjakan lebih dulu, sebelum Bapak/Ibu membaca bahan lain. Karena itu, kiriman pertama untuk Bapak/Ibu hanya berisi paket B.5a: daftar butir dalam lembar kerja dan buklet, beserta petunjuk pelabelan. Setiap butir diberi ID netral (BT-001 sampai BT-180) dan setiap bahan bacaan diberi ID netral (BH-01 sampai BH-73), bukan kode butir, supaya pelabelan tetap buta. Instrumen lengkap, bank soal, kisi-kisi, dan lembar lainnya kami kirim setelah lembar B.5a Bapak/Ibu kami terima, paling lambat [tanggal batas B.5a].
>
> Beberapa hal yang perlu Bapak/Ibu ketahui:
>
> 1. **Waktu.** Perkiraan seluruh pengisian sekitar [perkiraan jam dari tabel] jam, boleh dicicil. Batas pengumpulan [tanggal batas]. Pengarahan daring sekitar 45 menit pada [tanggal pengarahan].
> 2. **Dua kiriman.** Sebagian bahan kami kirim sekarang. Label pengembang, kisi-kisi bank soal, dan daftar relevansi per butir (B.5b) menyusul setelah pelabelan buta selesai, sekitar [tanggal kiriman kedua].
> 3. **Cara mengisi.** Lembar per butir tersedia sebagai lembar kerja (*spreadsheet*) yang sudah berisi daftar butirnya: B.5b dan B.6–B.9 dengan kode butir. Lembar lain boleh diisi di berkas atau di kertas.
> 4. **Akses game.** Bapak/Ibu akan menerima alamat instalasi uji dan akun uji secara terpisah, untuk memainkan minimal satu wilayah. Mohon tidak memakai akun itu di luar keperluan validasi.
> 5. **Kerahasiaan.** Bank soal, kunci jawaban, butir tes, dan skema kode mohon tidak disebarkan, karena kunci yang bocor ke calon peserta merusak data. Bapak/Ibu tidak akan menerima data siswa apa pun.
> 6. **Mandiri.** Mohon tidak berdiskusi dengan validator lain sampai lembar Bapak/Ibu selesai. Pertanyaan tentang bahan silakan diajukan kepada saya; kami akan menjawab dengan informasi faktual.
> 7. **Nama.** Di dokumen penelitian, nama Bapak/Ibu diganti nama samaran. Nama asli hanya tercantum di surat pernyataan dan arsip peneliti, dan disebut di laporan hanya dengan izin Bapak/Ibu.
> 8. **Honorarium.** Sebagai penghargaan, kami menyediakan honorarium sesuai ketentuan yang berlaku, dibayarkan [cara pembayaran] setelah lembar dan surat pernyataan kami terima.
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

**Untuk dua penilai B.5a** (Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1), surat 1 diubah begini, karena kiriman awal mereka hanya paket B.5a:

- **Lampiran:** "1 berkas (paket pelabelan buta B.5a)".
- **Paragraf tugas:** kalimat "mengikuti Instrumen Validasi Ahli Materi v2.1 terlampir. Lembar yang menjadi tugas Bapak/Ibu ditandai di Tabel A1 instrumen." diganti dengan "mengikuti Instrumen Validasi Ahli Materi v2.1, yang kami kirim bersama bahan lain setelah pelabelan buta selesai. Lembar yang menjadi tugas Bapak/Ibu ditandai di Tabel A1 instrumen itu."
- **Butir 1 (waktu):** "Perkiraan pelabelan buta sekitar 6–8 jam, dengan batas [tanggal batas B.5a]; lembar lain sekitar [perkiraan jam dari tabel] jam, dengan batas [tanggal batas]. Semuanya boleh dicicil. Pengarahan daring pelabelan buta sekitar 15 menit pada [tanggal pengarahan], dan pengarahan ahli materi sekitar 45 menit pada [tanggal pengarahan kedua]."
- **Butir 2 (dua kiriman):** "Kiriman pertama hanya berisi paket B.5a. Instrumen lengkap, bank soal dengan label pengembang, kisi-kisi, daftar relevansi per butir (B.5b), lembar kerja B.6–B.9, dan bahan lainnya kami kirim begitu lembar B.5a Bapak/Ibu kembali, sekitar [tanggal kiriman kedua]."
- **Butir 3 (cara mengisi):** "Lembar B.5a tersedia sebagai lembar kerja (*spreadsheet*) dan sebagai buklet untuk diisi di kertas. Keduanya memakai ID netral, bukan kode butir. Lembar per butir lainnya menyusul sebagai lembar kerja berkode butir."
- **Butir 4 (akses game):** "Alamat instalasi uji dan akun uji kami kirim terpisah setelah lembar B.5a kembali. Mohon tidak memakai akun itu di luar keperluan validasi."
- **Butir 5 (kerahasiaan):** tambahkan "Mohon daftar butir B.5a juga tidak disebarkan."

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
- Ganti butir 3 dengan: "**Cara mengisi.** Lembar B.8 (gambar rancangan layar) tersedia sebagai lembar kerja (*spreadsheet*). Lembar lain diisi di berkas instrumen atau di kertas."
- Tambahkan butir: "**Perangkat.** Mohon menguji minimal tiga perangkat: laptop atau Chromebook, tablet mendatar 10 inci atau lebih, dan satu perangkat seperti milik sekolah. Bila Bapak/Ibu tidak memiliki salah satunya, kami dapat meminjamkannya."
- Ganti butir 4 dengan: "**Akses.** Alamat instalasi uji dan akun uji kami kirim terpisah. Mohon hanya memakai instalasi uji, tidak masuk ke server produksi, dan tidak menjalankan uji beban, uji penetrasi, atau percobaan masuk ke akun lain (IMd A.3)."
- **Kerahasiaan:** "Isi game, akses, rancangan, mock-up, dan tangkapan layar mohon tidak disebarkan."
- **Waktu:** sekitar 4–5,5 jam; pengarahan 30 menit.
- [Hanya untuk Ahli Media Pembelajaran 3:] "Butir teknis H3 hanya diisi Bapak/Ibu, sebagai daftar periksa lulus/tidak."

### Surat lanjutan setelah B.5a (ke lima ahli materi)

Ada dua versi: versi A untuk tiga ahli materi lain, yang sudah menerima kiriman awal lengkap, dan versi B untuk dua penilai B.5a, yang baru menerima paket B.5a.

**Versi A** (Ahli Bahasa Anak 2, Ahli Literasi Digital 2, Ahli Pengukuran):

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

**Versi B** (Ahli Bahasa Anak 1, Ahli Literasi Digital 1):

> Hal: **Kiriman kedua bahan validasi ahli materi**
>
> Yth. [nama validator],
>
> Terima kasih, lembar pelabelan buta (B.5a) Bapak/Ibu sudah kami terima dan arsipkan. Sekarang kami kirim bahan untuk lembar lainnya:
>
> 1. Instrumen Validasi Ahli Materi v2.1 lengkap; lembar yang menjadi tugas Bapak/Ibu ditandai di Tabel A1;
> 2. Bank soal dengan label pengembang (dimensi, sub-aspek, level AKM, dan Bloom) dan pengecoh berkode;
> 3. Daftar relevansi per butir (B.5b) dalam lembar kerja, berisi dimensi utama dan definisinya;
> 4. Kisi-kisi bank soal: butir per tantangan, buklet, jangkar, dan waktu bermain;
> 5. Lembar kerja B.6–B.9 dan bahan lainnya: ringkasan konstruk, cerita penghubung, tes awal–akhir dan angket, skala sikap, gambar rancangan layar, serta kutipan kurikulum dan kerangka AKM;
> 6. Alamat instalasi uji dan akun uji, yang kami kirim terpisah.
>
> Dengan bahan ini Bapak/Ibu dapat mengisi lembar lain yang menjadi tugas Bapak/Ibu. Batas pengumpulannya [tanggal batas]. Pengarahan daring sekitar 45 menit pada [tanggal pengarahan kedua]. Lembar B.5a yang sudah dikumpulkan tidak perlu diubah.
>
> Berkas ada di [tautan folder]. Bila ada pertanyaan, silakan hubungi saya di [nomor telepon/WhatsApp].
>
> Hormat saya,
> [nama ketua peneliti]

Kalimat "mohon lembar yang sudah selesai tidak diubah" di versi A menjaga lembar yang dikerjakan setelah kiriman awal tetap tidak dipengaruhi label. Untuk penilai B.5a, lembar B.5a sudah dikumpulkan dan diarsipkan sebelum bahan lain dikirim, jadi tidak bisa berubah.

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

Ahli materi dan pengamat budaya memainkan game yang ada dengan akun uji, minimal satu wilayah (IM A.4 "Kenali konteks", IB A.4). Dua penilai B.5a baru menerima alamat dan akun uji di kiriman setelah B.5a, karena game memperlihatkan wilayah setiap butir. Ahli media bermain satu wilayah penuh per perangkat, mendaftar akun baru, membuka Pustaka, dan keluar akun (IMd A.4). Kebutuhan dari D.3:

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

- **Dibuat pengembang** di instalasi uji lewat pendaftaran biasa (`/daftar`), sebelum minggu 1; pengembang juga memegang akun `admin` instalasi uji (27 Oktober 2026, [Prasyarat no. 8](#prasyarat-no-8-instalasi-uji-dan-akun-uji)). Nama pengguna mengikuti aturan nama pengguna siswa yang ada sekarang: huruf kecil, angka, titik, garis bawah, 3–30 karakter (`valid_username` di `app/Validation/GelitaRules.php`). Karena itu dipakai titik, bukan tanda hubung.
- **Nama lengkap di akun** juga nama samaran tanpa angka, misalnya "Uji Bahasa Satu", karena nama lengkap siswa hanya boleh berisi huruf (`valid_person_name`). Sekolah ditulis sendiri lewat pilihan "Sekolahku tidak ada di daftar" sebagai `Sekolah Uji Validator`, supaya akun tidak tertaut ke sekolah resmi mana pun, termasuk sekolah sampel.
- **Tidak memakai awalan `sim_`**, karena awalan itu dipakai siswa simulasi `gelita:simulasi`, dan **tidak memakai pola kode peserta** (`S03-A07`, `U01-A03`), supaya akun validator tidak tercampur dengan peserta penelitian atau uji coba.
- **Kata sandi** dibuat pengembang, dikirim lewat saluran terpisah dari alamat instalasi uji (misalnya alamat lewat surel, kata sandi lewat pesan), dan validator diminta menggantinya saat pertama masuk bila sistem memintanya.
- **Kemajuan bermain** tidak bisa direset per akun dengan sistem sekarang. Akun cadangan tidak dipakai ulang; bila perlu akun bersih, dipakai akun cadangan berikutnya atau dibuat akun `uji.cadangan3` dan seterusnya ([Prasyarat no. 8](#prasyarat-no-8-instalasi-uji-dan-akun-uji), langkah 6).
- Akun dinonaktifkan atau dihapus setelah validasi langkah 1 selesai. Untuk media langkah 2, akun dibuat ulang di instalasi uji versi tahap 3.

Cara teknis memasang instalasi uji dan membuat akunnya ada di [Prasyarat no. 8](#prasyarat-no-8-instalasi-uji-dan-akun-uji). Dokumen ini tidak mengubah sistem.

## Pengembalian dan pencatatan

### Apa yang kembali

| Dari | Yang kembali |
| --- | --- |
| Penilai B.5a (2) | Lembar kerja B.5a (`materi-b5a-daftar-butir-buta.xlsx`) atau buklet yang diisi di kertas, paling lambat akhir minggu 2 |
| Ahli materi (5) | Lembar kerja B.5b (`materi-b5b-relevansi-kejelasan.xlsx`) dan B.6–B.9 (`materi-b6-b9-lembar-kerja.xlsx`: B.6, B.7, B.8a–b, B.9a–b; B.7 dari `materi-b7-kode-kesalahan-buta.xlsx` bila pengodean buta diminta); lembar B.1, B.3, B.10 (berkas atau kertas); surat pernyataan D.1 bertanda tangan |
| Pengamat budaya (3) | Lembar kerja B.5–B.8 (`budaya-b5-b8-lembar-kerja.xlsx`); lembar B.1, B.3, B.9; surat pernyataan D.1 |
| Ahli media (3) | Lembar kerja B.8 (`media-b8-mockup.xlsx`); lembar B.3 dan B.5–B.7 di berkas instrumen atau kertas, karena 21 tidak membuat lembar kerjanya; tangkapan layar temuan; kesimpulan B.9; surat pernyataan D.1 (dengan daftar perangkat yang dipakai) |
| Putaran 2 | Lembar nilai ulang dan matriks D.2 yang diparaf |
| Penilaian ulang setelah uji keterbacaan | Sama dengan putaran 2 |

Setiap lembar kerja yang kembali dicek dulu *Versi berkas*-nya di kepala lembar (misalnya `berkas-1.0 · sumber 3a48dbfe`): harus sama dengan versi yang dikirim ke validator itu ([21 → Cara membuat ulang](21_BERKAS_BUTIR_VALIDATOR.md#cara-membuat-ulang)).

Surat pernyataan memuat nama asli dan tanda tangan. Karena itu surat pernyataan **tidak pernah** masuk repo.

### Di mana disimpan

| Bahan | Tempat | Siapa yang bisa membuka |
| --- | --- | --- |
| Lembar asli dan lembar kerja dari validator, surat pernyataan, tangkapan layar | Folder penelitian di luar repo: `[lokasi penyimpanan peneliti]`, satu subfolder per validator, dinamai dengan **nama samaran** | Ketua peneliti dan petugas entri |
| Daftar padanan nama samaran ↔ nama asli | Satu berkas terenkripsi di luar repo, terpisah dari folder lembar | Ketua peneliti saja |
| Rekap hasil (skor per butir per nama samaran), hasil analisis, matriks revisi | Repo, setelah dibersihkan dari nama asli dan identitas | Tim |

Nama berkas yang diusulkan: `<samaran>_<lembar>_<putaran>_<tanggal terima>`, misalnya `ABA1_B5b_p1_2026-12-10.xlsx` (`p1` putaran 1, `p2` putaran 2, `pk` penilaian ulang setelah uji keterbacaan). Singkatan samaran: `ABA1`, `ABA2`, `ALD1`, `ALD2`, `AP`, `ABK1`–`ABK3`, `AMP1`–`AMP3`.

### Siapa memasukkan dan siapa memeriksa

1. **Penerimaan.** Ketua peneliti mencatat setiap kiriman masuk di daftar penerimaan (samaran, lembar, tanggal, lengkap/tidak) dan menyimpan salinan aslinya **sebelum** dibuka atau diolah. Salinan asli tidak diubah.
2. **Pemeriksaan kelengkapan.** Dalam dua hari kerja: semua lembar W terisi, skor 1–2 punya catatan, TD punya alasan. Bila kurang, validator dihubungi sekali.
3. **Entri.** 21 tidak membuat lembar rekap. Peneliti menyusun rekap dari lembar kerja yang kembali: baris setiap lembar kerja disalin ke rekap tanpa ketik ulang, karena semua lembar kerja dari versi berkas yang sama punya baris dan kolom yang sama. ID netral B.5a dipetakan ke kode butir oleh ketua peneliti dengan `khusus-peneliti/materi-b5a-kunci-peneliti.xlsx`, setelah kedua lembar B.5a kembali. Lembar kertas diketik oleh `[petugas entri]`, anggota tim yang bukan validator.
4. **Pemeriksaan ganda.** Lembar kertas diketik dua kali oleh dua orang berbeda dan dibandingkan; setiap beda dicek ke lembar asli. Untuk lembar kerja digital, ketua peneliti mencocokkan 10% baris acak dengan berkas asli validator.
5. **Kunci data.** Setelah cocok, rekap dikunci (salinan baca saja bertanggal) dan baru dianalisis.

AI boleh membantu menghitung indeks dari rekap yang sudah bersih, tetapi skor AI tidak pernah ikut dihitung sebagai skor validator (T6 di [07](07_TELAAH_INSTRUMEN_VALIDASI.md#t6-penelaah-pendahuluan-berbantuan-ai)).

### Daftar padanan di luar repo

- Satu baris per validator: nama samaran, nama asli dan gelar, instansi, bidang, wilayah yang dikuasai (pengamat budaya), kontak, tanggal kesediaan, tanggal kirim dan terima per kiriman dan putaran, honorarium dibayar, izin disebut namanya di laporan (ya/tidak).
- Dipegang ketua peneliti, sama seperti pemegang akun staf ([03 → Akun staf](03_KODE_DAN_ARTI.md#akun-staf)).
- Tidak pernah disalin ke repo, ke tangkapan layar, ke percakapan dengan AI, atau ke pesan grup.
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

Satu matriks induk untuk ketiga instrumen, dan juga untuk temuan uji keterbacaan (24), supaya revisi dari ahli dan dari anak tercatat di satu tempat. Matriks D.2 di setiap instrumen adalah potongan matriks induk ini yang dikirim ke validator pada putaran 2 dan pada penilaian ulang setelah uji keterbacaan. Matriks induk ditulis sebagai dokumen baru di folder ini saat hasil pertama kembali; nomor dan namanya ditetapkan sesi koordinator dan dicatat di 00.

| No | Butir/bagian | Temuan | Sumber validator | Keputusan | Perubahan | Dokumen yang diubah | Perlu ditelaah ulang? |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 1 | `wnb-3-a02` (contoh) | [ringkas temuan; kutip catatan validator bila perlu] | ABA1, ALD2 (B.5b skor 2) | Direvisi | [apa yang diubah] | 08 | Ya: semua penilai B.5b butir ini |
| 2 | IB B.5 objek no. [n] (contoh) | [temuan] | ABK2 (veto) | Direvisi | [apa yang diubah] | 08, 12 | Ya: ABK1–3 |
| 3 | mk-sd-03 (contoh) | [temuan] | AMP2 (B.8 skor 2) | Ditunda ke tahap 3 | Dicatat untuk dokumen implementasi | — | Ya: di media langkah 2 |
| 4 | `mgl-2-a03` (contoh) | [kata yang tidak dikenal anak] | uji keterbacaan K01, K03; KB-KTA | Revisi kecil | [kata diganti sinonim; dibaca ulang K05] | 08 | Ya, setelah uji keterbacaan: cek singkat ABA1, ABA2 ([24 → Yang kembali ke ahli](24_UJI_KETERBACAAN.md#yang-kembali-ke-ahli)) |

Isi kolom:

| Kolom | Isi |
| --- | --- |
| Butir/bagian | Kode butir (`wnb-3-a02`, `tes-mb-A07`, `skp-03`), kode lembar dan butir instrumen (IM B.3 D4), nomor objek, atau kode mock-up |
| Temuan | Satu temuan per baris; gabungkan temuan yang sama dari beberapa validator |
| Sumber validator | Nama samaran singkat dan lembarnya; tandai **veto** bila termasuk catatan kritis (IM C.6, IB C.5) atau keparahan 4 / V/R ≥ 3 (IMd C.5). Untuk temuan dari anak: "uji keterbacaan", kode anak (`K01`–`K05`), dan kode kesulitan `KB-*` dari [24 → Kode kesulitan](24_UJI_KETERBACAAN.md#kode-kesulitan-kb--usulan) |
| Keputusan | Dipertahankan · revisi kecil · revisi besar · diganti · dihapus · dipindah (label atau kisi-kisi) · ditunda ke tahap 3 · tidak diikuti (alasan wajib) |
| Perubahan | Apa yang diubah, cukup untuk dibandingkan dengan versi sebelumnya |
| Dokumen yang diubah | Nomor dokumen repo (08, 12–18, 06, 11) atau instrumen |
| Perlu ditelaah ulang? | Ya (siapa, dan kapan: putaran 2, setelah uji keterbacaan, atau media langkah 2) / tidak (mengapa); lihat aturan di bawah |

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
| Butir direvisi dari temuan uji keterbacaan | Penilai menurut jenis revisinya, di penilaian ulang setelah uji keterbacaan; bahan dibaca ulang anak K05 lebih dulu | [24 → Yang kembali ke ahli](24_UJI_KETERBACAAN.md#yang-kembali-ke-ahli) |

Putaran 2 dikirim dengan matriks D.2 instrumen yang bersangkutan, hanya berisi baris untuk validator itu. Kalau butir masih gagal di putaran 2, butir diganti atau dihapus, dan butir pengganti dinilai lagi oleh semua penilainya.

### Pencatatan di 04

- **Satu entri per putaran per panel** setelah keputusan revisi diambil, misalnya "Prototipe I, revisi dari validasi ahli materi putaran 1". Revisi dari anak dicatat sebagai "Prototipe I, revisi dari uji keterbacaan" (24). Isinya: jumlah butir dipertahankan, direvisi, diganti, dan dihapus; κ yang dicapai; veto yang ditindaklanjuti; tautan ke matriks induk; dokumen yang diubah.
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
- [ ] Semua catatan kritis dan semua "Tidak" di cek fakta B.7 ditindaklanjuti, termasuk baris B.7 yang masih terbuka ([25](25_CEK_FAKTA_BUDAYA.md)) (IB C.5 syarat d).
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

- [ ] Uji keterbacaan dengan empat anak kelas 5, ditambah satu anak di putaran kedua, selesai pada bahan versi setelah revisi validasi ahli, dan revisinya dicatat di 04 (24; daftar kerja no. 23). Butir ini baru bisa dicentang setelah izin etik terbit.
- [ ] Butir yang direvisi dari uji keterbacaan sudah dinilai ulang atau dicek penilai yang ditunjuk [24 → Yang kembali ke ahli](24_UJI_KETERBACAAN.md#yang-kembali-ke-ahli), dan barisnya di matriks revisi induk tertutup.

### Dokumentasi

- [ ] Sebelas surat pernyataan diterima dan diarsipkan di luar repo.
- [ ] Rekap hasil diperiksa ganda dan dikunci.
- [ ] Matriks revisi induk lengkap; semua baris punya keputusan; baris "perlu ditelaah ulang" sudah dinilai ulang.
- [ ] Entri 04 untuk setiap putaran per panel, dengan versi sebelum dan sesudah revisi.
- [ ] Hasil validasi ditulis untuk laporan mengikuti IM C.9, IB C.8, dan IMd C.6.

## Cara memenuhi prasyarat yang tersisa

Ditulis 27 Oktober 2026 (cabang `r10-prasyarat-validasi`) untuk prasyarat no. 9, 8, 10, dan 4 di [Ringkasan](#ringkasan). Prasyarat no. 6 (cek fakta budaya) dipegang sesi lain. Semua yang di bawah ini `asal: pengembang`, berstatus `draf pengembang` sampai ketua peneliti menjalankannya; keputusan pengembang hari itu ada di [Keputusan pengembang](#keputusan-pengembang) no. 5–11.

Urutannya: no. 9 dan no. 10 boleh dikerjakan sekarang, bersamaan. No. 8 dikerjakan setelah alamat kampus didapat. No. 4 paling akhir, setelah no. 6, 8, 9, dan 10 selesai, karena *commit* yang dibekukan harus sama dengan yang terpasang di instalasi uji dan yang tertulis di surat.

### Prasyarat no. 9: nama asli dan kesediaan validator

**Yang tidak pernah masuk repo:** nama asli, gelar, instansi, kontak, dan jawaban validator. Di repo hanya tertulis "selesai" dan tanggalnya, di tabel *Ringkasan* dan di [04](04_CATATAN_KEPUTUSAN.md). Daftar padanan dipegang ketua peneliti ([Daftar padanan di luar repo](#daftar-padanan-di-luar-repo)).

**Siapa menanyakan.** Ketua peneliti (keputusan no. 7), karena permohonan atas nama peneliti dan daftar padanannya milik ketua. Pengembang menyiapkan teks pesan di bawah.

**Langkah:**

1. Ketua peneliti menyalin templat daftar padanan di bawah ke berkas terenkripsi di luar repo (misalnya lembar kerja di folder penelitian yang hanya bisa dibuka ketua), lalu mengisi calon per baris.
2. Ketua mengirim pesan kesediaan ke setiap calon, satu per satu (bukan di grup), lewat pesan atau surel. Besaran honorarium boleh disebut di percakapan pribadi ini bila ditanya ([Keputusan pengembang](#keputusan-pengembang) no. 2), setelah no. 10 diketahui.
3. Jawaban "bersedia" dicatat di daftar padanan: tanggal, saluran (lisan, WhatsApp, surel), dan siapa yang menerima jawaban. Tangkapan layar jawaban, kalau disimpan, masuk folder validator itu di luar repo, bukan ke percakapan dengan AI.
4. Bila calon menolak atau tidak menjawab dalam satu minggu, ketua mencari pengganti untuk nama samaran yang sama. Nama samaran tidak berubah, jadi dokumen di repo tidak perlu diubah.
5. Untuk pengamat budaya, catat wilayah yang paling dikuasai (Temanggung, Magelang, Wonosobo), supaya ketiganya tersebar (IB A.3). Untuk ahli media, catat perangkat yang dimiliki, supaya tim tahu siapa yang perlu dipinjami tablet atau perangkat seperti milik sekolah.
6. Setelah kesebelas baris berisi "bersedia", ketua (atau pengembang atas kabar dari ketua) menulis di tabel *Ringkasan* no. 9: "Selesai (*tanggal*), tercatat di daftar padanan di luar repo", dan mencatatnya di 04 tanpa nama.

**Templat daftar padanan** (disalin ke luar repo; jangan diisi di sini):

| Samaran | Singkatan | Nama asli dan gelar | Instansi | Bidang | Wilayah dikuasai / perangkat | Surel dan telepon | Ditanya (tanggal, saluran) | Bersedia (tanggal, saluran) | Akun uji | Honorarium (besaran, tanggal bayar, bukti) | Boleh disebut di laporan | Catatan |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Ahli Bahasa Anak 1 *(penilai B.5a)* | ABA1 | | | | — | | | | `uji.bahasa1` | | ya / tidak | |
| Ahli Bahasa Anak 2 | ABA2 | | | | — | | | | `uji.bahasa2` | | | |
| Ahli Literasi Digital 1 *(penilai B.5a)* | ALD1 | | | | — | | | | `uji.digital1` | | | |
| Ahli Literasi Digital 2 | ALD2 | | | | — | | | | `uji.digital2` | | | |
| Ahli Pengukuran | AP | | | | — | | | | `uji.ukur` | | | |
| Ahli Budaya Kedu 1 | ABK1 | | | | wilayah: | | | | `uji.budaya1` | | | |
| Ahli Budaya Kedu 2 | ABK2 | | | | wilayah: | | | | `uji.budaya2` | | | |
| Ahli Budaya Kedu 3 | ABK3 | | | | wilayah: | | | | `uji.budaya3` | | | |
| Ahli Media Pembelajaran 1 (teknologi pendidikan) | AMP1 | | | | perangkat: | | | | `uji.media1` | | | |
| Ahli Media Pembelajaran 2 (desain visual atau UX) | AMP2 | | | | perangkat: | | | | `uji.media2` | | | |
| Ahli Media Pembelajaran 3 (rekayasa perangkat lunak atau game) | AMP3 | | | | perangkat: | | | | `uji.media3` | | | |

Kolom tanggal kirim dan terima per kiriman dan putaran ditambahkan di berkas yang sama saat paket dikirim ([Daftar padanan di luar repo](#daftar-padanan-di-luar-repo)).

**Pesan kesediaan** (dikirim ketua peneliti; isi tanda kurung siku sebelum dikirim):

> Yth. Bapak/Ibu [nama],
>
> Saya [nama ketua peneliti], [jabatan/program studi] di [instansi]. Saya sedang meneliti *[judul penelitian]*, dengan media permainan GELITA untuk siswa kelas 5 SD tentang literasi digital dan budaya Kedu.
>
> Saya ingin memohon kesediaan Bapak/Ibu menjadi validator **[ahli materi / pengamat budaya / ahli media]** di bidang [bidang]. Validasi dikerjakan dari jarak jauh, dengan instrumen dan lembar kerja digital serta akses ke versi uji permainan. Perkiraan waktunya sekitar [jam dari tabel *Siapa menerima apa*] jam, tersebar dalam [minggu 1–5 / minggu 1–2 lalu 3–5], mulai [tanggal minggu 1]. Honorarium diberikan sesuai ketentuan yang berlaku.
>
> Nama Bapak/Ibu hanya dicatat oleh saya. Di dokumen kerja tim dipakai nama samaran, dan nama Bapak/Ibu baru disebut di laporan bila Bapak/Ibu mengizinkan.
>
> Bila Bapak/Ibu bersedia, mohon balas pesan ini. Surat permohonan resmi dan bahannya akan saya kirim sebelum [tanggal minggu 1]. Bila belum bisa, tidak apa-apa, dan terima kasih atas waktunya.
>
> Hormat saya,
> [nama ketua peneliti]

Untuk dua penilai B.5a, tambahkan satu kalimat setelah perkiraan waktu: "Bagian pertama (sekitar 6–8 jam di minggu 1–2) adalah pelabelan butir; bahan lainnya saya kirim setelah bagian itu selesai."

### Prasyarat no. 8: instalasi uji dan akun uji

**Keputusan pengembang** (no. 5, 6, 8, 11): instalasi uji dipasang di **komputer lab kampus** (Jalur W di `docs/08_DEPLOYMENT.md`), dibuka dari luar lewat **subdomain kampus dengan HTTPS**; **pengembang** memasangnya, memegang akun `admin`-nya, dan membuat ketiga belas akun `uji.*`; semua wilayah dibuka sejak awal (`unlock_mode` = `free`).

**Batas yang tidak boleh dilanggar.** Instalasi uji adalah mesin dan database sendiri, terpisah dari server produksi. Tidak ada perintah yang dijalankan di server produksi, dan `gelita:reset` hanya dijalankan di komputer lab ini. Tidak ada perubahan kode: yang dipasang adalah *commit* `main` yang dibekukan (no. 4), dan tahap 2 tidak mengubah sistem.

**Langkah 1. Menyiapkan komputer dan alamat**

1. Pilih satu PC lab yang bisa menyala terus dari minggu 1 sampai putaran 2 selesai (usulan tanggal: 9 November 2026 sampai akhir Januari 2027, termasuk jeda libur). Matikan tidur otomatis dan pembaruan Windows yang memulai ulang sendiri; bila ada, pasang UPS.
2. Minta bagian TI kampus: satu subdomain (misalnya `[gelita-uji.kampus.ac.id]`), penerusan port 443 (dan 80 untuk sertifikat) ke PC itu atau *reverse proxy* kampus ke PC itu, dan sertifikat HTTPS. HTTPS wajib, karena pemasangan sebagai aplikasi dan paket aset hanya jalan di HTTPS (IMd D.3 no. 2).
3. Server, database, dan data akun tetap di kampus, jadi tetap di Indonesia (sama dengan keputusan di 22).

**Langkah 2. Memasang GELITA**

Ikuti `docs/08_DEPLOYMENT.md` → *Instalasi — Jalur W*, lalu *Langkah terakhir*, dengan perbedaan ini:

| Bagian 08 | Di instalasi uji |
| --- | --- |
| Kode | `git checkout <commit beku>` dari no. 4, bukan ujung `main` terbaru. Commit yang sama dicatat di 04 |
| `.env` | `app.baseURL` = alamat subdomain uji; sandi database dan sandi `admin` **baru**, tidak pernah sama dengan produksi |
| `db:seed DatabaseSeeder` | Seperti 08. Hasilnya: 28.487 sekolah resmi Jawa Tengah, akun `admin`, studi aktif `STUDI-KEDU-KELAS5-2026`, tiga wilayah, 12 tantangan, Pustaka Kedu, bank soal kelas 5 |
| Gambar dan narasi | Sama dengan yang akan dinilai: gambar tim lewat `php spark gelita:katalog:impor`, narasi lewat `php spark gelita:narration:import` lalu disetujui di panel. Bila produksi masih memakai gambar contoh, instalasi uji juga, dan itu disebut ke validator |
| Akun guru | Tidak dibuat. Validator tidak memerlukan panel |
| Cron (Task Scheduler) | `php spark gelita:retention:run` sekali sehari, seperti 08 → *Tugas Terjadwal*. Sesi yang tidak disentuh 30 hari ditandai ditinggalkan (`sessionAbandonDays`), jadi validator yang lama tidak masuk mulai dari awal lagi; ini disebut di surat akses |
| Cadangan | Tidak wajib, karena tidak ada data penelitian. Lembar validator kembali lewat folder bersama, bukan lewat instalasi uji |

Lalu:

1. Jalankan `php spark gelita:content:verify` (harus 0 galat) dan 08 → *Verifikasi Setelah Deploy*. Baris `http2` Jalur W belum pernah diuji di Windows sungguhan, jadi uji ini wajib.
2. Masuk `/admin/login` sebagai `admin`, ganti sandi (`/admin/akun/sandi`).
3. **Buka semua wilayah.** Di **Studi** (`/admin/studi`), ubah *Cara wilayah terbuka* studi aktif menjadi **Bebas — semua langsung terbuka (untuk uji coba)**. Pustaka satu wilayah tetap baru terbuka setelah keempat tantangannya diselesaikan, juga di mode bebas (`GameProgress::libraryStatus()`); validator yang perlu membaca Pustaka (IM D.3 no. 12, IB D.3 no. 8) menyelesaikan satu wilayah dulu, dan ini ditulis di surat akses.

**Langkah 3. Membuat akun `uji.*`**

Sistem sekarang tidak punya panel atau perintah untuk membuat akun siswa: `/admin/peserta` hanya menampilkan siswa dan mereset sandi, dan `gelita:simulasi` membuat siswa `sim_*` dengan riwayat palsu (tidak cocok). Jadi akun dibuat lewat **pendaftaran biasa**, satu per satu, di jendela peramban privat (supaya tidak ada sesi siswa lain yang terbawa), lalu keluar akun sebelum akun berikutnya. Isian per langkah, sesuai aturan di `app/Controllers/Game/RegisterController.php` dan `app/Validation/GelitaRules.php`:

| Langkah `/daftar` | Isian | Aturan yang harus dipenuhi |
| --- | --- | --- |
| 1 Identitas | Nama lengkap: nama samaran tanpa angka, misalnya `Uji Bahasa Satu`, `Uji Digital Dua`, `Uji Ukur`, `Uji Budaya Tiga`, `Uji Media Satu`, `Uji Cadangan Satu`. Umur: `11`. Jenis kelamin: bebas | Nama 2–150 karakter, huruf, spasi, dan apostrof saja, tanpa titik (`valid_person_name`, `no_name_period`); umur 8–15 (`registrationAge`) |
| 2 Sekolah | Negara Indonesia, provinsi Jawa Tengah, kabupaten mana saja di Kedu, centang **Sekolahku tidak ada di daftar**, nama sekolah `Sekolah Uji Validator` | Tanpa centang itu, provinsi Jawa Tengah meminta NPSN sekolah resmi (`known_npsn`), dan akun akan tertaut ke sekolah sungguhan. Nama sekolah 3–200 karakter |
| 3 Akun | Nama pengguna dari [Usulan akun uji](#usulan-akun-uji), kata sandi, centang persetujuan | Nama pengguna `[a-z0-9._]{3,30}` (`valid_username`), bukan `admin`, `guru`, `gelita`, `root`, `system`, `test` (`reservedUsernames`), belum dipakai. Kata sandi 8–64 karakter dengan huruf besar, huruf kecil, angka, dan simbol, dan tidak memuat nama pengguna (`passwordPolicy`, harus "kuat") |

Kata sandi dibuat acak per akun di pengelola sandi pengembang. Daftar akun dan sandinya diserahkan ke ketua peneliti lewat saluran pribadi, lalu ketua mengirim alamat dan sandi ke validator lewat dua saluran berbeda ([Usulan akun uji](#usulan-akun-uji)). Sandi tidak pernah ditulis di repo, di surat, atau di percakapan dengan AI.

Ahli media yang mendaftar sendiri (IMd A.4) diberi tahu tiga hal di surat akses: pilih "Sekolahku tidak ada di daftar", tulis `Sekolah Uji Validator`, dan pakai nama pengguna yang diawali `uji.media`.

**Langkah 4. Cek setiap akun** (dicentang pengembang, hasilnya dicatat di 04 tanpa sandi)

Untuk setiap akun, di peramban privat:

1. Masuk di `/masuk` dengan nama pengguna dan sandinya; layar masuk ke permainan tanpa galat.
2. Di Peta Kedu (`/peta`), ketiga wilayah terbuka (tidak ada gembok). Di peta setiap wilayah, keempat tantangan berstatus terbuka.
3. Buka satu tantangan di setiap wilayah sampai layar tantangan muncul, lalu kembali tanpa menjawab. Untuk satu akun cadangan saja, selesaikan keempat tantangan satu wilayah, lalu pastikan Pustaka wilayah itu terbuka; akun ini sesudahnya tidak dipakai validator.
4. Keluar akun.
5. Di `/admin/peserta`, akun muncul dengan sekolah `Sekolah Uji Validator`, dan tidak ada akun lain selain `uji.*`.

Karena langkah 3 membuka tantangan, setiap akun sudah punya satu sesi bermain dengan pengerjaan yang belum selesai. Pengerjaan itu ditandai ditinggalkan oleh retensi setelah 24 jam (`attemptAbandonHours`) dan tidak mengunci apa pun; validator tetap bisa membuka tantangan itu lagi.

**Langkah 5. Kalau akun bermasalah**

- **Lupa sandi atau terkunci:** `admin` membuka `/admin/peserta/{id}`, mengetik `RESET`, dan mendapat sandi sementara. Validator wajib menggantinya saat masuk berikutnya (`/ganti-sandi`).
- **Validator diganti:** validator baru memakai `uji.cadangan1` atau `uji.cadangan2`, dan padanannya dicatat di daftar padanan di luar repo.

**Langkah 6. Mereset kemajuan bermain**

Sistem sekarang tidak punya reset kemajuan per akun. Yang ada:

| Keadaan | Cara |
| --- | --- |
| Satu validator butuh mulai dari awal | Beri akun cadangan yang belum dipakai, atau buat akun baru `uji.cadangan3` dan seterusnya lewat langkah 3. Akun lama dibiarkan |
| Validator menuntaskan ketiga wilayah | Sesinya selesai, dan masuk berikutnya otomatis membuka sesi baru dari awal (`SessionService::login()`, `GameSessionModel::resumable()`). Ini disebut di surat akses supaya tidak dikira galat |
| Semua akun perlu bersih, misalnya sebelum putaran 2 atau sebelum media langkah 2 | Di **komputer lab ini saja**: `php spark gelita:reset --dry-run`, periksa rencananya, lalu `php spark gelita:reset`. Semua akun siswa terhapus dan studi kembali ke bawaan (`sequential`), jadi langkah 2 butir 3 dan langkah 3–4 diulang. Akun `admin` tetap |

**Usulan alat bantu untuk tahap 3** (tidak dibuat sekarang; `asal: pengembang`, `draf pengembang`): satu perintah spark, misalnya `gelita:uji:akun`, yang (a) membuat akun `uji.*` dari daftar nama samaran dengan sandi acak yang ditampilkan sekali dan sekolah `Sekolah Uji Validator`, dan (b) dengan `--reset=<nama pengguna>` menutup sesi bermain akun itu sehingga masuk berikutnya mulai dari awal. Perintah ini menolak berjalan bila instalasi berisi siswa yang bukan `uji.*`, supaya tidak pernah terpakai di produksi. Gunanya terasa di media langkah 2 (G3), saat akun dibuat ulang di instalasi uji tahap 3. Dicatat di [Akibat ke dokumen lain](#akibat-ke-dokumen-lain) untuk *Catatan perubahan sistem* di 01. Sejak 30 Oktober 2026 menjadi perubahan sistem no. 24 di 01 dan didraf di 28 ([04](04_CATATAN_KEPUTUSAN.md)).

**Di G3 (ahli media langkah 2, setelah tahap 3)** (sesi koordinator, 30 Oktober 2026, dari keputusan pengembang di [27 → Akun staf dan perintah spark](27_IMPLEMENTASI_PERAN_PENELITI.md#11-akun-staf-dan-perintah-spark) dan [28 → Alat bantu `gelita:uji:akun`](28_IMPLEMENTASI_SESI_LAPANGAN.md#13-alat-bantu-gelitaujiakun-perubahan-no-24); `asal: pengembang`, `draf pengembang`). Langkah 3 dan 6 di atas berlaku untuk tahap 2. Di instalasi uji tahap 3:
- **Akun staf.** Pengembang memegang dua akun, `admin` dan `ketua`, bukan satu. Validator tetap tidak pernah mendapat akun staf.
- **Akun `uji.*`** dibuat dengan `php spark gelita:uji:akun --buat`, bukan lewat `/daftar`: sekolah `Sekolah Uji Validator` dengan kode sekolah `V01`, rombel `A`, dan kode `UJI-01`, `UJI-02`, …. Sandi acak ditampilkan sekali. Kemajuan satu akun direset dengan `--reset=<nama pengguna>`, jadi akun cadangan tidak lagi dipakai untuk mulai dari awal.
- **Mode penelitian menyala** di studi aktif (oleh akun `ketua`), dan pengembang membuat satu sesi lapangan untuk validator, misalnya `V01-A-P2`, yang buka selama masa telaah (paling lama 4 jam per sesi; bila perlu satu sesi baru per hari). Validator mengetik tokennya, sehingga layar token ikut dinilai. Di tahap 2 mode penelitian tetap mati dan `unlock_mode` = `free`.

**Selesai bila:** alamat uji terbuka lewat HTTPS dari luar kampus, ketiga belas akun lolos cek langkah 4, dan 04 mencatat tanggal, *commit* yang terpasang, dan hasil cek (tanpa sandi dan tanpa alamat IP).

### Prasyarat no. 10: honorarium

Surat tetap menulis "sesuai ketentuan yang berlaku" ([Keputusan pengembang](#keputusan-pengembang) no. 2). Besaran **tidak pernah** ditulis di repo (keputusan no. 9).

Yang harus diketahui ketua peneliti sebelum paket dikirim:

| Hal | Isi yang perlu jelas | Dicatat di |
| --- | --- | --- |
| Besaran | Per validator, atau per panel bila bebannya beda jauh (lihat perkiraan jam di [Siapa menerima apa](#siapa-menerima-apa): ± 4–5,5 jam ahli media sampai ± 18–26 jam penilai B.5a). Termasuk apakah putaran 2 dan penilaian ulang setelah uji keterbacaan dibayar terpisah | Daftar padanan di luar repo, kolom honorarium |
| Sumber dana | Hibah, dana pribadi, atau dana lembaga; harus sama dengan sumber dana yang ditulis di 22 dan 23, supaya pernyataan konflik kepentingan tetap benar | Catatan keuangan penelitian di luar repo; di 22 hanya jenis sumbernya |
| Ketentuan yang diacu | Standar biaya yang berlaku di lembaga pemberi dana, dan potongan pajak (PPh) bila ada | Catatan keuangan penelitian |
| Waktu dan cara bayar | Misalnya setelah surat pernyataan D.1 dan semua lembar putaran 1 kembali; transfer atau tunai | Daftar padanan, kolom honorarium |
| Bukti bayar | Kuitansi bertanda tangan atau bukti transfer, disimpan di folder validator di luar repo. Berisi nama asli, jadi tidak pernah masuk repo | Folder penelitian di luar repo |

**Selesai bila** ketua peneliti menyatakan besaran dan sumber dana sudah ditetapkan. Di tabel *Ringkasan* no. 10 dan di 04 hanya ditulis "Selesai (*tanggal*), dicatat ketua peneliti di luar repo".

### Prasyarat no. 4: membekukan versi bahan

"Dibekukan" berarti versinya dicatat, bukan tidak boleh diubah ([01 → Peran pengembang, peneliti, dan ahli](01_REVISI_10_ANALITIK.md#peran-pengembang-peneliti-dan-ahli), aturan 3). Pembekuan dilakukan **setelah** cek fakta budaya (no. 6), PR #275, dan PR cabang ini digabung ke `main`, dan setelah no. 8, 9, dan 10 selesai (keputusan no. 10). Pembekuan adalah langkah terakhir sebelum kirim.

**Langkah:**

1. **Pastikan tidak ada PR terbuka** yang mengubah bahan yang dinilai: 08, 11 (`kamus-1.0`), 12–18, tiga instrumen di `dokumen-pdf/src/`, atau 21. Bila ada, tunggu digabung atau tunda.
2. **Ambil `main` terbaru** di salinan bersih: `git fetch origin main && git checkout -B r10-beku-validasi origin/main`, lalu `composer install`.
3. **Buat ulang berkas validator** sesuai [21 → Cara membuat ulang](21_BERKAS_BUTIR_VALIDATOR.md#cara-membuat-ulang): `php docs/revisi-10-analitik/dokumen-pdf/src/berkas-validator.php` (dengan buklet PDF). Semua pemeriksaan di `dokumen-pdf/berkas-validator/LAPORAN.md` harus lolos. Catat *Versi berkas* di kepala lembar (misalnya `berkas-1.0 · sumber 3a48dbfe`).
4. **Bila `git status` menunjukkan berkas berubah** (selain tanggal di buklet PDF), commit di cabang `r10-beku-validasi`, buka PR ke `main`, dan gabungkan. Bila tidak ada perubahan isi, cabang dibuang.
5. **Tetapkan *commit* beku:** *commit* `main` sesudah langkah 4 (`git rev-parse origin/main`). Hash lengkap ini yang dipakai di semua tempat.
6. **Pasang *commit* yang sama di instalasi uji** (`git checkout <hash>` di komputer lab, lalu ulangi langkah 4 cek akun di no. 8 untuk satu akun).
7. **Ketua peneliti menyetujui** bahan versi ini untuk dikirim. Dengan itu, bahan yang dibekukan naik dari `disetujui pengembang` ke `disetujui peneliti` ([00 → Label asal dan status](00_INDEKS.md#label-asal-dan-status)), karena hanya bahan `disetujui peneliti` yang dikirim ke ahli.
8. **Catat di 04**, entri baru di atas, dengan isi:
   - judul "Versi bahan dibekukan untuk validasi putaran 1", tanggal, `asal: peneliti` (pembekuan dan persetujuan oleh ketua; disiapkan pengembang), status `disetujui peneliti`;
   - hash *commit* lengkap dan tujuh karakter pertamanya;
   - *Versi berkas* dari langkah 3;
   - daftar bahan yang dibekukan dengan statusnya: 08, 11 (`kamus-1.0`), 12–18, ketiga instrumen (IM v2.1, IB v2.1, IMd v1.1, dengan nama PDF-nya), dan berkas validator dari 21;
   - tanggal dan *commit* yang terpasang di instalasi uji (harus sama);
   - aturan sesudahnya (di bawah).
9. **Tulis versi di surat dan D.1:** "versi/tanggal" diisi `<tanggal beku> · commit <7 karakter> · <versi berkas>`, misalnya `9 November 2026 · commit 1a2b3c4 · berkas-1.0 · sumber 3a48dbfe`. Teks yang sama dipakai di IMd D.3 no. 2 (versi yang dinilai) dan di surat akses.
10. **Ubah tabel *Ringkasan* no. 4** menjadi "Selesai (*tanggal*, *commit* 7 karakter, lihat 04)".

**Kapan `berkas-validator.php` dijalankan lagi:**

| Keadaan | Yang dilakukan |
| --- | --- |
| Sumber berubah **sebelum** paket dikirim | Ulangi langkah 2–10. *Commit* beku yang lama diganti, dan entri 04 yang baru menyebut penggantinya |
| Sumber berubah **setelah** paket dikirim | Jangan buat ulang berkas yang sudah dikirim. Catat perubahan di 04 dengan asal dan akibatnya (dinilai ulang ahli di putaran 2, amandemen ke komite etik, atau tidak perlu apa-apa), sesuai 01 aturan 3 |
| B.5a sudah dikirim ke penilai | Jangan membuat ulang B.5a, kecuali kedua penilai menerima berkas baru dan berkas kunci ikut diganti ([21 → Cara membuat ulang](21_BERKAS_BUTIR_VALIDATOR.md#cara-membuat-ulang)) |
| Putaran 2 | Butir yang direvisi dibekukan lagi dengan langkah yang sama sebelum dikirim, dan entri 04-nya menyebut *commit* putaran 1 sebagai pembanding |
| Lembar kembali dari validator | Tidak dibuat ulang. *Versi berkas* di kepala lembar dicocokkan dengan versi yang dicatat di 04 ([Apa yang kembali](#apa-yang-kembali)) |

## Keputusan pengembang

Pertanyaan di draf ini dijawab pengembang pada 27 Oktober 2026 ([04](04_CATATAN_KEPUTUSAN.md), entri 27 Oktober 2026):

| No | Pertanyaan | Keputusan |
| --- | --- | --- |
| 1 | Siapa dua penilai pelabelan buta (B.5a)? | **Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1**, sesuai saran IM A.3: sisi membaca dan sisi digital konstruk sama-sama terwakili |
| 2 | Bagaimana honorarium ditulis di surat? | **"Sesuai ketentuan yang berlaku", tanpa angka.** Besarannya dicatat ketua peneliti di luar repo dan bisa disampaikan saat menanyakan kesediaan |
| 3 | Paket dikirim kertas atau digital? | **Digital.** PDF instrumen dan lembar kerja lewat folder bersama yang dibatasi per validator; surat pernyataan ditandatangani lalu dipindai; salinan kertas hanya bila validator memintanya |
| 4 | Apakah ahli media langkah 1 menunggu sesuatu? | **Tidak.** Dikirim bersama panel lain begitu instrumen disetujui, memakai daftar jenis perangkat sekolah sampel sebagai pengganti L8 (IMd D.3 no. 7) |

Putaran kedua, untuk prasyarat no. 4, 8, 9, dan 10 (27 Oktober 2026, cabang `r10-prasyarat-validasi`; semuanya `asal: pengembang`, `disetujui pengembang`; [Cara memenuhi prasyarat yang tersisa](#cara-memenuhi-prasyarat-yang-tersisa)):

| No | Pertanyaan | Keputusan |
| --- | --- | --- |
| 5 | Di mana instalasi uji dipasang? | **Komputer lab kampus** (Jalur W di `docs/08_DEPLOYMENT.md`). Tidak dipilih: VPS kecil terpisah (usulan), laptop pengembang dengan terowongan |
| 6 | Bagaimana validator membukanya dari rumah? | **Subdomain kampus dengan HTTPS**, lewat penerusan port atau *reverse proxy* dari bagian TI kampus (usulan). Tidak dipilih: layanan terowongan, hanya di jaringan kampus |
| 7 | Siapa menanyakan kesediaan validator? | **Ketua peneliti**, dengan teks pesan dari pengembang (usulan). Tidak dipilih: pengembang |
| 8 | Siapa membuat akun `uji.*` dan memegang sandinya? | **Pengembang**, sebagai pemegang `admin` instalasi uji, lewat pendaftaran biasa sebelum minggu 1; daftar sandi diserahkan ke ketua peneliti (usulan). Tidak dipilih: ketua peneliti, validator mendaftar sendiri |
| 9 | Apakah besaran honorarium pernah ditulis di repo? | **Tidak pernah;** dicatat ketua peneliti di luar repo (usulan). Tidak dipilih: besaran per panel di 20 |
| 10 | Kapan versi bahan dibekukan? | **Setelah cek fakta budaya, PR #275, dan PR prasyarat ini digabung, dan setelah no. 8–10 selesai**, sebagai langkah terakhir sebelum kirim (usulan). Tidak dipilih: bekukan sekarang lalu bekukan ulang bila berubah |
| 11 | Bagaimana wilayah terbuka di instalasi uji? | **Semua terbuka** (`unlock_mode` = `free` di `/admin/studi`) (usulan). Tidak dipilih: berurutan seperti siswa |

Keputusan pengembang di dokumen tahap 2 lain yang juga mengatur dokumen ini (27 Oktober 2026, dicatat di 04), dan sudah diterapkan di atas:

- **Urutan kirim B.5a** ([21](21_BERKAS_BUTIR_VALIDATOR.md#pertanyaan-untuk-pengembang), pertanyaan 1, 2, dan 4). Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1 menerima paket B.5a saja dulu, dengan ID netral `BT-001` … `BT-180` dan `BH-01` … `BH-73`. Instrumen lengkap, bank berkode, kisi-kisi, B.5b, dan lembar B.6–B.9 menyusul setelah B.5a kembali. Tiga ahli materi lain menerima kiriman awal seperti biasa, tanpa label pengembang dan kisi-kisi sampai B.5a kembali. B.7 mencakup semua 622 sel, jadi perkiraan waktunya naik (3–5 jam di dokumen ini). Diterapkan di *Siapa menerima apa*, *Kiriman dan putaran*, *Jadwal relatif*, surat 1, dan surat lanjutan.
- **Izin etik** (22). Satu permohonan untuk semua kegiatan dengan anak (KG1–KG5); revisi instrumen dilaporkan ke komite sebagai amandemen protokol, dan bahan untuk anak yang dilampirkan adalah versi yang dibekukan untuk validasi ahli.
- **Uji keterbacaan** (24). Empat anak, ditambah satu anak (K05) di putaran kedua, pada bahan Prototipe I setelah revisi dari validasi ahli. Temuan dari anak masuk matriks revisi induk, dan butir yang direvisi kembali ke ahli di penilaian ulang setelah uji keterbacaan.
- **Akun staf** (03). `admin` dan `ketua`, yang disebut di dokumen ini, termasuk akun staf yang sudah disetujui; tidak ada lagi yang berstatus usulan.

## Akibat ke dokumen lain

Status per 27 Oktober 2026, setelah penyelarasan dokumen tahap 2. Perubahan di instrumen HTML sudah dibawa sesi koordinator (prasyarat no. 11, PR #268).

| Dokumen | Bagian | Perubahan | Status |
| --- | --- | --- | --- |
| [03](03_KODE_DAN_ARTI.md) | *Akun staf* atau bagian baru sesudahnya | Tambah baris akun siswa uji validator `uji.bahasa1`–`2`, `uji.digital1`–`2`, `uji.ukur`, `uji.budaya1`–`3`, `uji.media1`–`3`, `uji.cadangan1`–`2`: akun siswa di instalasi uji, dibuat `admin`, bukan akun staf | Sudah |
| [03](03_KODE_DAN_ARTI.md) | *Peran* | Tambah singkatan samaran untuk nama berkas: `ABA1`, `ABA2`, `ALD1`, `ALD2`, `AP`, `ABK1`–`ABK3`, `AMP1`–`AMP3` | Sudah |
| [01](01_REVISI_10_ANALITIK.md) | *Peta jalan dan daftar kerja*, no. 3 dan 14 | Status: draf paket di 20; penilai B.5a ditetapkan: Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1 (27 Oktober 2026), dan keduanya menerima paket B.5a saja dulu | Sudah |
| [dokumen-pdf/README.md](dokumen-pdf/README.md) | *Sebelum dikirim ke validator* | Tambah langkah 5: "Ikuti prasyarat dan urutan kirim di 20" | Sudah |
| [07](07_TELAAH_INSTRUMEN_VALIDASI.md) | *Langkah berikutnya* | Tambah butir: paket validasi disusun di 20 | Sudah |
| Instrumen ahli materi v2.1 (`dokumen-pdf/src/instrumen-validasi-ahli-materi-v2.1.html`) | B.5a, paragraf pembuka | "lembar kerja yang sudah berisi kode butir" → berisi ID butir netral (`BT-…`) dan ID bahan netral (`BH-…`) | Sudah (PR #268) |
| Instrumen ahli materi v2.1 | D.3 | Sebut nama berkas dari 21: no. 2 `materi-b5a-daftar-butir-buta.xlsx` dan `materi-b5a-buklet-buta.pdf`, no. 5 `materi-b5b-relevansi-kejelasan.xlsx`, lembar kerja B.6–B.9 `materi-b6-b9-lembar-kerja.xlsx`. Kolom "Kapan dikirim" mengikuti [Siapa menerima apa](#siapa-menerima-apa): ke dua penilai B.5a hanya surat, no. 2, dan halaman petunjuk B.5a di awal; instrumen lengkap dan no. 3–12 setelah B.5a | Sudah (PR #268) |
| Instrumen ahli materi v2.1 | A.4, tabel waktu dan pengarahan | B.7 3–5 jam (622 sel kode), bukan 2–3 jam. Dua penilai B.5a: pengarahan B.5a ± 15 menit di awal, pengarahan 45 menit setelah B.5a | Sudah (PR #268) |
| Instrumen pengamat budaya v2.1 dan ahli media v1.1 | D.3 | Sebut `budaya-b5-b8-lembar-kerja.xlsx` (IB no. 11) dan `media-b8-mockup.xlsx` (IMd no. 9, lembar B.8 saja). Tidak wajib: kolom "Kapan dikirim" seperti IM D.3, supaya cocok dengan tabel *Siapa menerima apa* (IMd no. 10 setelah B.6; IB no. 6 gambar baru menyusul) | Sudah (PR #268) |
| [22](22_PERMOHONAN_IZIN_ETIK.md) | Lampiran dan *Perubahan protokol* | Sebut bahwa instrumen untuk anak yang dilampirkan adalah versi yang dibekukan untuk validasi, dan revisi Prototipe I yang mengubah bahan untuk anak diajukan ke *Lembaga Komite Etik* sebagai amandemen protokol | Sudah |
| [24](24_UJI_KETERBACAAN.md) | *Bahan yang menunggu uji keterbacaan* | Bahan yang diuji adalah versi setelah revisi validasi ahli | Sudah |
| [24](24_UJI_KETERBACAAN.md) | *Yang dibutuhkan dari dokumen lain → Dari 20* | Dipenuhi di 20: matriks revisi induk menampung temuan anak (sumber "uji keterbacaan", kode anak, `KB-*`), dan jadwal punya penilaian ulang setelah uji keterbacaan. Tidak ada yang perlu diubah di 24 | Sudah (di 20) |
| Matriks revisi induk (dokumen baru) | — | Dibuat saat hasil pertama kembali; nomor ditetapkan sesi koordinator dan dicatat di 00 | Belum (menunggu hasil validasi) |
| [03](03_KODE_DAN_ARTI.md) | *Akun uji validator* | Akun dibuat pengembang lewat `/daftar`, sekolah `Sekolah Uji Validator`, akun cadangan tidak dipakai ulang (27 Oktober 2026, cabang `r10-prasyarat-validasi`) | Sudah |
| [01](01_REVISI_10_ANALITIK.md) | *Catatan perubahan sistem untuk tahap nanti* | Usulan alat bantu `gelita:uji:akun` (membuat akun `uji.*` dan mereset kemajuan satu akun) dari [Prasyarat no. 8](#prasyarat-no-8-instalasi-uji-dan-akun-uji), langkah 6; dipertimbangkan bersama dokumen implementasi tahap 3 | Sudah (sesi koordinator, 30 Oktober 2026): dicatat di 01 sebagai usulan belum bernomor di bawah tabel *Catatan perubahan sistem untuk tahap nanti*; diberi nomor 24 oleh pengembang 30 Oktober 2026 dan didraf di 28 |
