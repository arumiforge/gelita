# Indeks Revisi 10 Analitik

Per 27 Oktober 2026 · Pintu masuk untuk setiap sesi baru di [revisi 10 analitik](01_REVISI_10_ANALITIK.md)

Dokumen Markdown di folder ini sekitar 1,2 MB, kira-kira 500 ribu token kalau dibaca utuh (per 27 Oktober 2026). Satu sesi tidak perlu membaca semuanya. Aturan menjaga konteks ada di `.claude/rules/revisi-10.md` dan dimuat otomatis saat berkas di folder ini dibuka.

## Mulai sesi baru di sini

1. Baca berkas ini.
2. Baca entri teratas [04_CATATAN_KEPUTUSAN.md](04_CATATAN_KEPUTUSAN.md) (keputusan terbaru).
3. Di [01](01_REVISI_10_ANALITIK.md), baca bagian *Pertanyaan yang masih terbuka* dan *Peta jalan dan daftar kerja* saja. Cari judulnya, jangan baca seluruh berkas.
4. Baca baris revisi di `docs/PROGRESS.md`.
5. Ingat *Label asal dan status* di bawah: semua yang tercatat sejauh ini masih kerja pengembang.

Dokumen lain dibaca per bagian sesuai tugas.

## Label asal dan status

Dipakai di semua dokumen revisi sejak 27 Oktober 2026, mengikuti [01 → Peran pengembang, peneliti, dan ahli](01_REVISI_10_ANALITIK.md#peran-pengembang-peneliti-dan-ahli). Setiap keputusan baru di [04](04_CATATAN_KEPUTUSAN.md) dan setiap perubahan setelah versi dibekukan menyebut asalnya. Status dokumen di tabel bawah dibaca dengan urutan status ini.

**Asal perubahan**

| Label | Artinya |
| --- | --- |
| `asal: pengembang` | Dari pengembang, termasuk yang dikerjakan dengan bantuan AI |
| `asal: peneliti` | Dari peneliti |
| `asal: peneliti + ahli` | Dari peneliti bersama validator, misalnya hasil validasi atau matriks revisi |
| `asal: komite etik` | Dari *Lembaga Komite Etik*: syarat persetujuan atau tanggapan amandemen |

**Status**, dari awal sampai akhir

| Label | Artinya |
| --- | --- |
| `draf pengembang` | Disusun pengembang (berbantuan AI), belum disetujui |
| `disetujui pengembang` | Disetujui pengembang; siap diserahkan ke peneliti. Dokumen yang ditulis "disetujui" atau "selesai" sebelum 27 Oktober 2026 ada di tingkat ini |
| `disetujui peneliti` | Ditetapkan peneliti; siap dikirim ke ahli atau komite |
| `tervalidasi ahli` | Sudah dinilai ahli dan direvisi dari hasilnya (atau disetujui komite etik, untuk bahan izin etik) |
| `final` | Ditetapkan peneliti setelah validasi dan izin etik; dipakai untuk implementasi atau pengambilan data |

Penanda `needs_verification` tetap dipakai terpisah untuk fakta atau rujukan yang belum dicek ke sumbernya, di status mana pun. Per 27 Oktober 2026 belum ada dokumen yang `disetujui peneliti`, `tervalidasi ahli`, atau `final`.

## Daftar dokumen

| Berkas | Isi | Status | Kapan dibaca | Ukuran |
| --- | --- | --- | --- | --- |
| [01](01_REVISI_10_ANALITIK.md) | Dokumen utama: konstruk, 10 analitik, Skenario Digital, skema kesalahan, peran, log, token, kode peserta, peta jalan, pertanyaan terbuka, 23 perubahan sistem | Aktif | Selalu, per bagian | 72 KB |
| [02](02_LEMBAR_LAPANGAN.md) | Lembar lapangan L0–L8 | L0–L7 final, disetujui 26 Oktober 2026 (daftar kerja no. 11); L8 Profil sekolah draf (26 Oktober 2026); daftar kode sekolah (no. 12) masih ditandai kosong; akun P1 memakai nama samaran `koordinator` (no. 13, 27 Oktober 2026); L7 ditambah batas pengamanan daring, isi surat dari PP No. 33 Tahun 2026, verifikasi izin, dan daftar izin berkode (27 Oktober 2026); L5 tidak terdengar guru atau teman dan butir surat L7 dari panduan BRIN (sesi koordinator, 27 Oktober 2026) | Saat menyusun lembar atau alur lapangan | 41 KB |
| [03](03_KODE_DAN_ARTI.md) | Arti semua kode | Rujukan | Cari kode yang dibutuhkan saja | 13 KB |
| [04](04_CATATAN_KEPUTUSAN.md) | Riwayat keputusan, terbaru di atas | Aktif | Selalu, entri teratas | 100 KB |
| [05](05_TELAAH_BUTIR_DAN_DIMENSI.md) | Telaah butir bank lama terhadap dimensi | Selesai; keputusannya masuk 06 | Hanya untuk menelusuri alasan | 39 KB |
| [06](06_KISI_KISI_BANK_SOAL.md) | Kisi-kisi bank soal baru, buklet, jangkar, waktu bermain | Disetujui 25 Oktober 2026 | Per bagian, saat menulis butir, tes, atau dokumen implementasi | 42 KB |
| [07](07_TELAAH_INSTRUMEN_VALIDASI.md) | Telaah instrumen validasi ahli; lembar penilaian skala sikap B.8; ringkasan instrumen v2.1 dan v1.1 dan bedanya dari usulan telaah | Diterima; instrumen ahli materi v2.1, pengamat budaya v2.1, dan ahli media v1.1 ditulis 26 Oktober 2026 di `dokumen-pdf/`, disetujui pengembang 27 Oktober 2026 | Saat menyusun paket validasi atau mengubah instrumen | 33 KB |
| [08](08_DRAF_BANK_SOAL.md) | Draf bank soal baru: 116 butir variasi a/b, 6 jangkar, 12 PBK; pemeriksaan kebocoran keterangan Susun Gambar | Draf, `needs_verification`; variasi Susun Gambar diputuskan 26 Oktober 2026 (gambar baru Wayang Kedu dan Candi Pawon menyusul) | Cari per kode butir (misalnya `wnb-3-a02`) atau judul bagian | 95 KB |
| [09](09_TELAAH_PENDAHULUAN_BANK_SOAL.md) | Telaah pendahuluan T6 atas draf 08 | Selesai; R1–R4 disetujui | Saat menyiapkan paket validasi | 20 KB |
| [10](10_TELAAH_PENDAHULUAN_MEDIA.md) | Telaah pendahuluan media | Selesai; keputusan masuk 01 | Saat menyiapkan mock-up dan instrumen media | 18 KB |
| [11](11_KAMUS_DATA.md) | Kamus data: peta bukti, definisi beku 10 analitik, data yang dinilai, "dilewati", waktu aktif, tersensor, tebakan cepat, kode, aturan gabung lembar | Disetujui 26 Oktober 2026 (`kamus-1.0`); ambang jeda dan tebakan cepat masih sementara | Saat menulis instrumen, lembar, pra-registrasi, atau dokumen implementasi; cari per analitik | 41 KB |
| [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md) | Cerita penghubung dan narasi tantangan kelima, penyesuaian cerita lama, kartu misi, daftar rekaman, hasil pemeriksaan kebocoran cerita | Disetujui 26 Oktober 2026 | Saat menyiapkan rekaman, menulis dokumen implementasi perubahan no. 2 dan no. 16, atau mengubah cerita | 50 KB |
| [13](13_TES_AWAL_AKHIR.md) | Rancangan tes awal–akhir: kisi-kisi tiga bagian (D1, D2, D3), kesetaraan bentuk A dan B, kode butir, modul tes GELITA, pelaksanaan di P1 dan P5, penskoran, analisis, validasi | Draf; keputusan pengembang 26 Oktober 2026 (modul GELITA bertampilan Bootstrap, batas waktu longgar, topik di luar Kedu); menunggu validasi ahli | Saat menyusun paket validasi, lembar L6, jadwal pertemuan, atau dokumen implementasi perubahan no. 22 | 37 KB |
| [14](14_TES_BAGIAN_MEMBACA.md) | Tes Bagian 1 Membaca (D1), bentuk A dan B: teks, butir, kunci, kode kesalahan, sumber adaptasi AKM/ANBK | Draf, `needs_verification` | Cari per kode butir (misalnya `tes-mb-A07`) | 63 KB |
| [15](15_TES_BAGIAN_DIGITAL.md) | Tes Bagian 2 Aman dan etis (D2) dan Bagian 3 Budaya digital (D3), bentuk A dan B; level pengembang per nomor | Draf, `needs_verification`; level pengembang per nomor disetujui 27 Oktober 2026 | Cari per kode butir (misalnya `tes-ae-B03`) | 66 KB |
| [16](16_ANGKET_AKSES_DIGITAL.md) | Angket akses digital dan butir risiko daring P5: butir, kode jawaban, variabel, pelaksanaan, etika | Draf; keputusan pengembang 26 Oktober 2026 (di modul, tidak dibacakan; butir risiko daring di P5); menunggu validasi ahli | Saat menyusun lembar L6, izin etik, atau variabel peserta | 45 KB |
| [17](17_SKALA_SIKAP.md) | Skala sikap menjaga warisan (pendamping, Balai Refleksi P5): konstruk, kisi-kisi SK1–SK4, 12 butir, format empat pilihan, penskoran, pelaksanaan, validasi, etika, akibat ke dokumen lain | Draf, `needs_verification`; keputusan pengembang 26 Oktober 2026; menunggu validasi ahli | Saat menyusun paket validasi, lembar L1/L3/L7, atau dokumen implementasi perubahan no. 21 | 24 KB |
| [18](18_MOCKUP_LAYAR_BARU.md) | Mock-up layar baru untuk ahli media langkah 1: Skenario Digital, *Periksa Unggahan*, token, modul tes dan angket, skala sikap, cerita penghubung; keadaan per layar dan yang dinilai ahli | Disetujui 26 Oktober 2026; gambar skala sikap dari 17 | Saat menyusun instrumen media v1.1, paket validasi media, atau dokumen implementasi layar baru | 24 KB |
| [19](19_TELAAH_LABEL_BLOOM.md) | Telaah label Bloom pengembang per butir (116 butir literasi digital, 12 PBK), pola sistematis, rentang wilayah baru | Selesai; keputusan pengembang 26 Oktober 2026 masuk 08 dan 06 | Saat menelusuri alasan label Bloom atau menyiapkan B.5a | 25 KB |
| [20](20_PAKET_VALIDASI.md) | Paket validasi untuk sebelas validator: siapa menerima apa dan kapan, surat permohonan, jadwal, akses dan akun uji, pengembalian, matriks revisi, daftar periksa validasi di G2 | Draf (27 Oktober 2026); keputusan pengembang diambil hari itu (penilai B.5a Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1, honorarium "sesuai ketentuan", kirim digital, ahli media langkah 1 tidak menunggu L8); dikirim setelah instrumen disetujui; cara memenuhi prasyarat no. 4, 8, 9, 10 ditulis 27 Oktober 2026 (instalasi uji di komputer lab kampus, akun `uji.*` lewat `/daftar`, templat daftar padanan, pembekuan setelah cek fakta budaya) | Saat menyiapkan atau mengirim paket validasi | 61 KB |
| [21](21_BERKAS_BUTIR_VALIDATOR.md) | Daftar butir dan lembar kerja untuk validator: daftar buta B.5a, daftar B.5b, lembar kerja per lembar instrumen, cara membuat ulang; berkasnya di `dokumen-pdf/berkas-validator/` | Draf (27 Oktober 2026); empat pertanyaan dijawab pengembang 27 Oktober 2026 | Saat menyiapkan berkas per butir | 23 KB |
| [22](22_PERMOHONAN_IZIN_ETIK.md) | Draf permohonan izin etik ke *Lembaga Komite Etik*: protokol, kegiatan KG1–KG5 dengan anak, peserta, persetujuan, risiko, prosedur rujukan butir risiko daring, pengelolaan data, lampiran | Draf (27 Oktober 2026); keputusan pengembang hari yang sama (hapus sampai studi ditutup, berbagi data, kompensasi, satu permohonan, tiga pilihan mundur), diselaraskan dengan 23 dan 24, lalu dengan pedoman KEPPKN 2021 (27 Oktober 2026: delapan kriteria, telaah lengkap, masa berlaku satu tahun, format protokol resmi, penghentian, daftar tilik tujuh standar, komitmen etik, pembimbing); dibandingkan dengan PP No. 33 Tahun 2026 (27 Oktober 2026: dasar hukum, pengendali dan prosesor, penilaian dampak, server di Indonesia, batas 3 × 24 jam, daftar izin berkode; nomor pasal `needs_verification`); panduan klirens etik BRIN menjadi pedoman utama (27 Oktober 2026: pemetaan formulir, telaah dan masa berlaku 12 bulan dengan amandemen, lampiran 15–17, data berkode paling singkat 20 tahun, lokasi lengkap, pernyataan tanpa potensi konflik kepentingan), PSEPPKN 2021 sebagai cadangan; akibat kedua putaran itu dibawa sesi koordinator 27 Oktober 2026 (masa simpan 20 tahun di tabel dan surat, UU No. 11 Tahun 2019, lampiran 18 penilaian dampak yang belum ditulis); komite dipilih pengembang 27 Oktober 2026 (Komisi Etik Bidang Sosial Humaniora BRIN, formulir BRIN), urutan unggah dan daftar isian ditambahkan, nomor pasal PP No. 33 Tahun 2026 dicek ke salinan naskahnya (Pasal 82 ayat (3) dibetulkan; satu pencocokan lagi dengan PDF resmi), lampiran 18 ditautkan ke 26; diajukan setelah dokumen tahap 1 yang dilampirkan disetujui | Saat menyusun atau mengajukan izin etik, surat izin (23), atau lembar L7 | 159 KB |
| [23](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md) | Surat informasi dan persetujuan T1–T13: surat dan formulir orang tua (centang terpisah rekaman suara dan butir risiko daring), naskah persetujuan anak dan usulan teks centang daftar, surat kepala sekolah dengan data L8, lembar guru kelas, varian uji keterbacaan dan kelas uji coba, formulir pengunduran diri dan hapus data, layar sebelum butir risiko, pesan dukungan, selebaran tempat bercerita, tabel pembagian | Draf (27 Oktober 2026); empat keputusan pengembang diambil 27 Oktober 2026 ([23](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#keputusan-pengembang)); isi tambahan dari pedoman KEPPKN 2021 dimasukkan hari yang sama (batas pengamanan daring, jumlah peserta, sumber dana, kontak cadangan, tanda terima kasih disebut netral, penghentian, surat dibacakan dengan saksi); isi dari PP No. 33 Tahun 2026 dimasukkan hari yang sama (pengendali dan kontak data, server di Indonesia, kebocoran 3 × 24 jam, hak melihat dan membetulkan data, pilihan D di T10); dibandingkan dengan panduan klirens etik BRIN (27 Oktober 2026): judul penelitian di T1, T6, T8a dan alamat komite di `[kontak komite etik]`; masa simpan data berkode paling singkat 20 tahun di T1, T8a, dan tabel, dan baris baru di *Cek isi L7* (sesi koordinator, 27 Oktober 2026); nama dan alamat komite BRIN, server IDCloudHost di Jakarta Pusat, nomor WA SAPA 129, dan batas 3 × 24 jam sejak permintaan diterima (27 Oktober 2026) | Saat menyiapkan izin etik, L7, atau rekrutmen sekolah | 102 KB |
| [24](24_UJI_KETERBACAAN.md) | Protokol uji keterbacaan dengan empat anak kelas 5 ditambah satu di putaran kedua: peserta, pembagian bahan, langkah per anak, lembar LK1–LK3 dan kode `KB-*`, aturan revisi, waktu, pengelolaan data | Draf; keputusan pengembang 27 Oktober 2026 (empat anak ditambah satu, inti dan blok bank, sekolah Kedu di luar sampel, butir risiko daring diparafrasekan saja), diselaraskan dengan 22 dan 23; masa simpan data berkode paling singkat 20 tahun (sesi koordinator, 27 Oktober 2026) | Saat menyiapkan uji keterbacaan, izin etik, atau matriks revisi | 68 KB |
| [25](25_CEK_FAKTA_BUDAYA.md) | Cek fakta budaya tim (prasyarat no. 6 di 20): 135 klaim dari matriks IB, 08, keterangan Susun Gambar, dan 12, masing-masing dengan putusan, sumber, dan usulan perbaikan; hasilnya di lembar B.7 IB (44 fakta); perbaikan yang diterapkan ke 08 dan 12; daftar perbaikan berkas data untuk tahap 3 | `draf pengembang`, `asal: pengembang` (27 Oktober 2026); keputusan perbaikan disetujui pengembang hari yang sama (Topeng Ireng ditulis ulang ke makna keprajuritan, letak Candi Arjuna disebut, Gondosuli "sekitar 832", Tambi "sejak zaman Belanda"); dikonfirmasi pengamat budaya di B.7 | Saat menyiapkan paket validasi pengamat budaya, menindaklanjuti B.7, atau mengimpor bank baru di tahap 3; cari per kode klaim (misalnya `WNB-17`) | 89 KB |
| [26](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md) | Penilaian dampak pelindungan data pribadi lengkap (lampiran 18 izin etik): uraian dan tujuan pengolahan, dasar hukum, inventaris data anak, orang tua, sekolah, staf, dan log, aliran data, kebutuhan dan proporsionalitas, hak, 14 risiko dengan kemungkinan dan keparahan, 18 langkah perlindungan, risiko sisa, konsultasi, tanda tangan, tinjauan ulang | `draf pengembang` (27 Oktober 2026); empat keputusan pengembang hari itu (pengembang dan admin bagian tim pengendali, IP mentah di sesi diperbaiki sebelum KG2, konsultasi PPDP dan kepala sekolah, tinjauan tahunan dan saat berubah); nomor pasal `needs_verification` seperti 22 | Saat menyusun atau mengajukan izin etik, perjanjian prosesor, atau catatan kegiatan pengolahan | 50 KB |
| `gambar/` | Peta bukti, lapis data, peta jalan, buklet, tangkapan layar telaah media, mock-up layar baru (`gambar/mockup/`, sumber HTML di `src/`) | Rujukan | Saat dokumen yang memakainya diubah | — |
| [dokumen-pdf/](dokumen-pdf/README.md) | Dokumen PDF penting: tiga instrumen validasi ahli (ahli materi v2.1, pengamat budaya v2.1, ahli media v1.1), sumber HTML dan skrip pembuatnya di `src/`, dan draf asli pengembang di `arsip/`; lembar kerja validator di `berkas-validator/` ([21](21_BERKAS_BUTIR_VALIDATOR.md)) | Disetujui pengembang 27 Oktober 2026 (setelah diselaraskan dengan paket validasi) | Saat menyusun paket validasi; PDF dibuat ulang dari HTML | 3,3 MB |
| [dokumen-pendukung-peneliti/](dokumen-pendukung-peneliti/README.md) | Bacaan untuk peneliti dan tim, **bukan dokumen implementasi**: model penelitian dan pengembangan (PDF), dengan rujukan yang dicek ke sumber asli | Rujukan; model Plomp dan aturan I-CVI Lynn diputuskan 26 Oktober 2026 | Saat menulis Bab III atau bagian metodologi; tidak dibaca untuk implementasi | 390 KB (PDF dan sumbernya) |

## Tahap dokumen yang tersisa sebelum implementasi

Urutan kerja mengikuti peta jalan di 01, yang dikukuhkan pengembang pada 25 Oktober 2026: sistem baru diubah di tahap 3, setelah gerbang G2. Jadi masih ada **tiga tahap dokumen**.

**Tahap dokumen 1: desain instrumen (sekarang, sampai gerbang G1)**

| Dokumen | Daftar kerja di 01 | Penyusun | Status |
| --- | --- | --- | --- |
| [11_KAMUS_DATA.md](11_KAMUS_DATA.md): definisi beku 10 analitik, "dilewati", waktu aktif, tersensor, dan kode | no. 2 | AI (draf), pengembang | Selesai (`kamus-1.0`, 26 Oktober 2026) |
| [12_CERITA_PENGHUBUNG_DAN_NARASI.md](12_CERITA_PENGHUBUNG_DAN_NARASI.md): cerita penghubung dan narasi tantangan kelima | no. 7 | AI (draf), pengembang | Selesai (disetujui 26 Oktober 2026) |
| [13](13_TES_AWAL_AKHIR.md)–[16](16_ANGKET_AKSES_DIGITAL.md): tes awal–akhir bentuk A dan B, dan angket akses digital | no. 9, 10 | AI (draf), pengembang | Draf; keputusan pengembang diambil 26 Oktober 2026 ([13](13_TES_AWAL_AKHIR.md#keputusan-pengembang)); menunggu validasi ahli |
| [17_SKALA_SIKAP.md](17_SKALA_SIKAP.md): skala sikap menjaga warisan untuk Balai Refleksi | no. 19 | AI (draf), pengembang | Draf; keputusan pengembang diambil 26 Oktober 2026 ([17](17_SKALA_SIKAP.md#keputusan-pengembang)); menunggu validasi ahli |
| [19_TELAAH_LABEL_BLOOM.md](19_TELAAH_LABEL_BLOOM.md): telaah label Bloom | no. 4 | AI (telaah), pengembang | Selesai (26 Oktober 2026); 08 dan 06 diperbarui |
| [18_MOCKUP_LAYAR_BARU.md](18_MOCKUP_LAYAR_BARU.md): mock-up layar baru untuk ahli media langkah 1 (27 gambar di `gambar/mockup/`) | no. 20 | AI (draf), pengembang | Selesai (disetujui 26 Oktober 2026; gambar skala sikap dari 17) |
| [02_LEMBAR_LAPANGAN.md](02_LEMBAR_LAPANGAN.md): lembar L0–L7 final (L8 Profil sekolah menyusul sebagai draf) | no. 11 | AI (draf), pengembang | Selesai (disetujui 26 Oktober 2026) |
| Instrumen validasi ahli materi v2.1, pengamat budaya v2.1, dan ahli media v1.1 ([dokumen-pdf/](dokumen-pdf/README.md)) | [07](07_TELAAH_INSTRUMEN_VALIDASI.md#instrumen-versi-21-dan-11) | AI (draf), pengembang | Selesai (disetujui 27 Oktober 2026) |

Gerbang G1: konstruk disetujui, dan semua dokumen tahap 1 selesai dan disetujui pengembang.

**Tahap dokumen 2: validasi ahli dan izin etik (sampai gerbang G2)**

Pelabelan buta oleh dua ahli (B.5a di [07](07_TELAAH_INSTRUMEN_VALIDASI.md)), dengan kesepakatan κ ≥ 0,70 sebagai bagian gerbang G2. Paket validasi untuk sebelas validator (nama samaran di [03](03_KODE_DAN_ARTI.md#peran)), permohonan izin etik ke *Lembaga Komite Etik* (nama samaran; diajukan minggu kedua–keempat November 2026), surat izin orang tua (L7), dan uji keterbacaan dengan anak setelah izin etik terbit. Setelah itu bank dan instrumen direvisi dari hasil validasi (Prototipe I).

Dimulai 27 Oktober 2026 atas permintaan pengembang, ditulis paralel di lima sesi, satu dokumen per sesi dan satu cabang per dokumen ([04](04_CATATAN_KEPUTUSAN.md), entri 27 Oktober 2026). Kelima draf selesai hari itu juga, lalu diselaraskan satu sama lain dan digabung lewat satu PR koordinator (#268); keputusan yang bertentangan diputuskan ulang pengembang. Gerbang G1 belum resmi terpenuhi (daftar kode sekolah no. 12 ditunda, tiga instrumen validasi menunggu persetujuan), jadi dokumen tahap 2 boleh ditulis sekarang, tetapi yang dikirim ke luar (paket ke validator, permohonan ke komite etik) menunggu persetujuan dokumen yang dibutuhkannya.

| Dokumen | Daftar kerja di 01 | Cabang | Penyusun | Status |
| --- | --- | --- | --- | --- |
| [20_PAKET_VALIDASI.md](20_PAKET_VALIDASI.md): paket validasi sebelas validator, surat permohonan, jadwal, pengembalian, matriks revisi, daftar periksa validasi di G2 | no. 3, 14 | `r10-paket-validasi` | AI (draf), pengembang | Draf (27 Oktober 2026); keputusan pengembang diambil ([20](20_PAKET_VALIDASI.md#keputusan-pengembang)); diselaraskan dengan 21; prosedur prasyarat no. 4, 8, 9, 10 ([20 → Cara memenuhi prasyarat yang tersisa](20_PAKET_VALIDASI.md#cara-memenuhi-prasyarat-yang-tersisa), cabang `r10-prasyarat-validasi`, 27 Oktober 2026) |
| [21_BERKAS_BUTIR_VALIDATOR.md](21_BERKAS_BUTIR_VALIDATOR.md): daftar butir buta B.5a, daftar B.5b, dan lembar kerja per lembar instrumen, dibuat dari 08 dan 13–18 | no. 3, 14 | `r10-berkas-validator` | AI (draf), pengembang | Draf; keputusan pengembang 27 Oktober 2026 |
| [22_PERMOHONAN_IZIN_ETIK.md](22_PERMOHONAN_IZIN_ETIK.md): protokol dan permohonan izin etik | no. 15 | `r10-izin-etik` | AI (draf), pengembang | Draf; keputusan pengembang 27 Oktober 2026 dimasukkan; diselaraskan dengan pedoman KEPPKN 2021 (cabang `sky/friendly-newton-jjuvj0`, 27 Oktober 2026); panduan klirens etik BRIN menjadi pedoman utama (cabang `r10-klirens-brin`, 27 Oktober 2026); akibat kedua putaran dibawa (cabang `sky/vigilant-wozniak-12kyni`, 27 Oktober 2026); komite BRIN dan formulirnya dipilih, nomor pasal PP No. 33 Tahun 2026 dicek, tempat kosong diisi atau didaftar (cabang `r10-formulir-etik`, 27 Oktober 2026) |
| [23_SURAT_INFORMASI_DAN_PERSETUJUAN.md](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md): surat informasi dan persetujuan untuk orang tua, anak, sekolah, dan guru | no. 15 | `r10-surat-izin` | AI (draf), pengembang | Draf (27 Oktober 2026); keputusan pengembang diambil 27 Oktober 2026; diselaraskan dengan pedoman KEPPKN 2021 (cabang `sky/friendly-newton-jjuvj0`, 27 Oktober 2026); judul penelitian dan alamat komite dari panduan klirens etik BRIN (cabang `r10-klirens-brin`, 27 Oktober 2026); masa simpan 20 tahun dan *Cek isi L7* (cabang `sky/vigilant-wozniak-12kyni`, 27 Oktober 2026); nama komite, server, dan nomor SAPA 129 ditulis di surat (cabang `r10-formulir-etik`, 27 Oktober 2026) |
| [24_UJI_KETERBACAAN.md](24_UJI_KETERBACAAN.md): protokol uji keterbacaan dengan anak | no. 23 | `r10-uji-keterbacaan` | AI (draf), pengembang | Draf; keputusan pengembang diambil 27 Oktober 2026 ([24](24_UJI_KETERBACAAN.md#keputusan-pengembang)) |
| [26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md): penilaian dampak pelindungan data lengkap, lampiran 18 [22](22_PERMOHONAN_IZIN_ETIK.md#lampiran) | no. 15 | `r10-penilaian-dampak` | AI (draf), pengembang | `draf pengembang` (27 Oktober 2026); keputusan pengembang hari itu ([26](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md#keputusan-pengembang)); perubahan yang dibutuhkan 22 di [26 → Akibat ke dokumen lain](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md#akibat-ke-dokumen-lain) |

Setiap sesi hanya mengubah dokumennya sendiri, barisnya sendiri di berkas ini dan di `docs/PROGRESS.md`, dan entrinya sendiri di 04. Perubahan yang dibutuhkan di dokumen lain dicatat di bagian *Akibat ke dokumen lain* dokumen itu dan dibawa sesi koordinator setelah digabung ke `main`.

**Langkah berikutnya di tahap 2** (per 30 Oktober 2026; kelima dokumen tahap 2, ketiga instrumen, cek fakta budaya [25](25_CEK_FAKTA_BUDAYA.md), dan penilaian dampak [26](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md) sudah ada di `main`, semuanya `disetujui pengembang`; belum ada yang `disetujui peneliti`):

1. **Paket validasi.** Prasyarat no. 6 (cek fakta budaya) sudah selesai. Yang tersisa ada di [20 → Ringkasan](20_PAKET_VALIDASI.md#ringkasan), dan cara memenuhinya sudah ditulis di 20:
   - no. 9: nama asli dan kesediaan validator, dicatat di daftar padanan di luar repo;
   - no. 8: instalasi uji di subdomain kampus dengan akun `uji.*`;
   - no. 10: honorarium dikonfirmasi ketua peneliti;
   - no. 4, pada hari kirim: versi bahan dibekukan dan *commit*-nya dicatat di 04, lalu berkas validator ([21](21_BERKAS_BUTIR_VALIDATOR.md)) dibuat ulang.

   Setelah itu paket dikirim sesuai urutan di 20. Gambar baru Wayang Kedu Temanggungan dan Candi Pawon boleh menyusul.
2. **Izin etik** ke Komisi Etik Bidang Sosial Humaniora BRIN dengan formulir BRIN (dipilih 27 Oktober 2026), diajukan minggu kedua–keempat November 2026 lewat [22 → Pengajuan lewat sistem klirens etik BRIN](22_PERMOHONAN_IZIN_ETIK.md#pengajuan-lewat-sistem-klirens-etik-brin). Sebelum diajukan:
   - isi [22 → Daftar isian sebelum diajukan](22_PERMOHONAN_IZIN_ETIK.md#daftar-isian-sebelum-diajukan) di luar repo, termasuk judul resmi dan `[kontak PPDP atau narahubung]`;
   - cocokkan nomor pasal PP No. 33 Tahun 2026 sekali lagi dengan PDF resmi;
   - minta saran PPDP atau narahubung, lalu persetujuan peneliti atas penilaian dampak [26](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md) (lampiran 18);
   - baca ringkasan penilaian dampak untuk sekolah di [23 → T6](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#t6-surat-permohonan-izin-kepada-kepala-sekolah) sebelum dicetak.

   Pedoman yang sudah diterapkan: panduan klirens etik BRIN (pedoman utama), PSEPPKN/KEPPKN 2021 (cadangan), PP No. 33 Tahun 2026, dan masa simpan data berkode paling singkat 20 tahun.
3. **Keputusan pengembang yang masih terbuka:**
   - target persetujuan etik di [01 → Peta jalan dan daftar kerja](01_REVISI_10_ANALITIK.md#peta-jalan-dan-daftar-kerja) no. 15 (patokan BRIN ± 1–2 bulan sejak berkas lengkap, kira-kira Januari–Februari 2027);
   - nomor perubahan sistem untuk usulan `gelita:uji:akun` (01 → *Catatan perubahan sistem untuk tahap nanti*).
4. **Gerbang G1:** tinggal daftar kode sekolah (daftar kerja no. 12). Ditunda pengembang dan tidak menahan validasi maupun izin etik, tetapi dibutuhkan sebelum sekolah direkrut dan L8 diisi.
5. **Setelah hasil validasi kembali:** matriks revisi induk (20), revisi Prototipe I, lalu uji keterbacaan ([24](24_UJI_KETERBACAAN.md)) setelah izin etik terbit.

**Tahap dokumen 3: dokumen implementasi (tahap 3, setelah gerbang G2)**

Dokumen implementasi lengkap dari 23 perubahan sistem di 01, dibagi per bagian seperti Dok 1–3 perombakan kelas 5. Tahap ini menunggu persetujuan etik, yang ditargetkan paling lambat akhir September sampai minggu pertama November 2027. Implementasi baru dimulai setelah dokumen ini disetujui.
