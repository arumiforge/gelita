# Kode dan Artinya

**Judul penelitian:** *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*

Per 27 Oktober 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md)

Semua kode yang dipakai di dokumen revisi, lembar lapangan, dan data penelitian dikumpulkan di sini. Aturan memakai kode di data dan nilai kolom data turunan (misalnya status penyelesaian dan jenis "dilewati") ada di [kamus data](11_KAMUS_DATA.md). Kode yang sudah tersimpan di data game (misalnya `tmg-2-a01` atau `bintang-1`) tidak diganti; kode baru di revisi ini ditandai "usulan" sampai disetujui tim.

## Peran

| Kode | Arti | Dipakai di |
| --- | --- | --- |
| Admin | Pengelola sistem: akun, identitas, konten, rilis, log teknis. Tidak mengunduh data penelitian | Sistem, panel admin |
| Pengembang (berbantuan AI) | Pengembang GELITA, dibantu AI. Menyusun draf, label pengembang, skor dan telaah pendahuluan, cek fakta, dan berkas siap kirim; boleh berperan sebagai ahli atau komite untuk latihan dan penyaringan awal. Bukan validator: skornya tidak dihitung dalam validasi dan tidak dilihat ahli sebelum menilai. Keputusannya berkedudukan *disetujui pengembang* sampai disetujui peneliti ([01 → Peran pengembang, peneliti, dan ahli](01_REVISI_10_ANALITIK.md#peran-pengembang-peneliti-dan-ahli)) | Semua dokumen revisi, telaah pendahuluan (T6 di [07](07_TELAAH_INSTRUMEN_VALIDASI.md)) |
| Peneliti | Pemegang data penelitian pseudonim: sesi lapangan, token, ekspor, data turunan. Di dokumen revisi: sumber judul penelitian dan 10 analitik; menerima kerja pengembang, menindaklanjutinya bersama ahli dan komite, dan menetapkan final | Sistem, panel peneliti (usulan), dokumen revisi |
| Guru | Guru kelas. Tidak punya akun; peran `guru` di sistem dihapus (keputusan 25 Oktober 2026). Cukup membantu perangkat dan internet | Lapangan |
| P1 | Koordinator sesi: membuka sesi, token, L0, L1, L2, L7 | Lapangan |
| P2 | Pengamat keterlibatan: L4 | Lapangan |
| P3 | Pengamat bantuan dan pewawancara: L3, L5 | Lapangan |
| Ketua peneliti | Memegang L7 bersama P1. Satu-satunya yang menghubungkan kode peserta dengan nama, untuk rujukan butir risiko daring dan permintaan hapus; menerima laporan kejadian tak diinginkan (KTD). Akun `ketua` | Lapangan, izin etik ([22](22_PERMOHONAN_IZIN_ETIK.md)) |
| Ahli Bahasa Anak 1, 2 | Nama samaran validator: literasi membaca dan bahasa Indonesia SD (panel ahli materi). Singkatan di nama berkas: `ABA1`, `ABA2` | Validasi, tahap 2 |
| Ahli Literasi Digital 1, 2 | Nama samaran validator: literasi digital anak (panel ahli materi). Singkatan di nama berkas: `ALD1`, `ALD2` | Validasi, tahap 2 |
| Ahli Pengukuran | Nama samaran validator: evaluasi dan pengukuran (panel ahli materi). Singkatan di nama berkas: `AP` | Validasi, tahap 2 |
| Ahli Budaya Kedu 1, 2, 3 | Nama samaran validator: pengamat budaya, idealnya satu orang per wilayah (panel tiga orang, keputusan 26 Oktober 2026). Ahli Budaya Kedu 1 juga menilai relevansi skala sikap (B.8). Singkatan di nama berkas: `ABK1`–`ABK3` | Validasi, tahap 2 |
| Ahli Media Pembelajaran 1, 2, 3 | Nama samaran validator: ahli media, dua langkah (mock-up, lalu layar jadi). Panel tiga orang (keputusan 26 Oktober 2026): 1 teknologi pendidikan atau media pembelajaran, 2 desain visual atau UX, 3 rekayasa perangkat lunak atau pengembangan game. Singkatan di nama berkas: `AMP1`–`AMP3` | Validasi, tahap 2 dan 3 |
| Lembaga Komite Etik | Nama samaran komite etik penelitian; pengajuan minggu kedua–keempat November 2026, persetujuan paling lambat akhir September–minggu pertama November 2027 | Izin etik, tahap 2 |

### Akun staf

Pemegang akun admin dan akun peneliti sementara memakai nama samaran sesuai perannya (keputusan pengembang, 27 Oktober 2026; daftar kerja no. 13 di [01](01_REVISI_10_ANALITIK.md#peta-jalan-dan-daftar-kerja)). Siapa orangnya ditetapkan nanti dan dicatat ketua peneliti di luar repo, sama seperti nama asli validator.

| Akun | Peran di sistem | Dipegang | Catatan |
| --- | --- | --- | --- |
| `admin` | Admin | Pengelola sistem | Akun, identitas, konten, rilis, log teknis; tidak mengunduh data penelitian |
| `koordinator` | Peneliti | P1, koordinator sesi | Sesi lapangan, token, baris-kursi-buklet dari L2, reset kata sandi lewat kode peserta |
| `pengamat`, `pewawancara`, `ketua` | Peneliti | P2, P3, ketua peneliti | Pola yang sama ("dan seterusnya"), disetujui pengembang 27 Oktober 2026; dibuat kalau perannya butuh akun |

Satu orang yang memegang dua peran tetap memakai dua akun terpisah ([01](01_REVISI_10_ANALITIK.md#admin-dan-peneliti-dua-peran-berbeda)).

### Akun uji validator

Akun **siswa** di instalasi uji untuk validator, bukan akun staf, dibuat `admin` sebelum paket dikirim ([20 → Akses untuk validator](20_PAKET_VALIDASI.md#akses-untuk-validator), 27 Oktober 2026). Titik dipakai karena aturan nama pengguna siswa tidak mengizinkan tanda hubung.

| Akun | Untuk |
| --- | --- |
| `uji.bahasa1`, `uji.bahasa2` | Ahli Bahasa Anak 1, 2 |
| `uji.digital1`, `uji.digital2` | Ahli Literasi Digital 1, 2 |
| `uji.ukur` | Ahli Pengukuran |
| `uji.budaya1`–`uji.budaya3` | Ahli Budaya Kedu 1–3 |
| `uji.media1`–`uji.media3` | Ahli Media Pembelajaran 1–3 |
| `uji.cadangan1`, `uji.cadangan2` | Cadangan, kalau akun terkunci atau validator diganti |

## Dimensi literasi digital

| Kode | Arti | Pilar Komdigi |
| --- | --- | --- |
| D1 | Literasi informasi, bertumpu pada literasi membaca: menemukan, menafsirkan, dan menilai informasi | Cakap Bermedia Digital |
| D1a | Sub-aspek D1: memahami informasi (menemukan; interpretasi dan integrasi) | Cakap Bermedia Digital |
| D1b | Sub-aspek D1: menilai informasi dan sumber (fakta dan pendapat, kredibilitas, kabar bertentangan) | Cakap Bermedia Digital |
| D2 | Aman dan etis: data pribadi, kata sandi dan OTP, orang asing, kabar bohong, izin membagikan foto atau obrolan diri sendiri dan teman | Aman dan Etis Bermedia Digital |
| D3 | Partisipasi dan representasi budaya Kedu di ruang digital: atribusi, representasi akurat, izin pelaku dan pemilik karya, menghargai tradisi | Budaya Bermedia Digital |
| PBK | Pengetahuan Budaya Kedu: skala pendamping dari Cari Objek babak 1, di luar skor literasi digital | — |
| sikap | Sikap menjaga warisan: skala pendamping lewat Balai Refleksi, di luar skor literasi digital (keputusan 25 Oktober 2026) | — |

## Sepuluh analitik

| Kode | Arti | Dimensi |
| --- | --- | --- |
| 1 | Penyelesaian aktivitas | D1–D3 |
| 2 | Ketepatan jawaban | D1–D3 |
| 3 | Ketepatan percobaan pertama | D1–D3 |
| 4 | Jumlah percobaan | D1–D3 |
| 5 | Waktu pengerjaan (waktu aktif) | D1–D3 |
| 6 | Pola kesalahan | D1–D3 |
| 7 | Penggunaan bantuan | D1–D3 |
| 8 | Navigasi permainan | D1–D3 |
| 9 | Perubahan setelah umpan balik | D1–D3 |
| 10 | Pilihan dan alasan di skenario digital | D2 |

## Wilayah dan tantangan

| Kode | Arti | Catatan |
| --- | --- | --- |
| tmg | Wilayah Temanggung (mudah) | Kode tersimpan |
| mgl | Wilayah Magelang (sedang) | Kode tersimpan |
| wnb | Wilayah Wonosobo (sulit) | Kode tersimpan |
| tmg-1, mgl-1, wnb-1 | Susun Gambar (`puzzle`), pemanasan | Kode tersimpan |
| tmg-2, mgl-2, wnb-2 | Isi Rumpang (`rumpang`) | Kode tersimpan |
| tmg-3, mgl-3, wnb-3 | Benar atau Salah (`boleh`) | Kode tersimpan |
| tmg-4, mgl-4, wnb-4 | Cari Objek (`cari`): babak 1 *Temukan Benda* (PBK), babak 2 *Periksa Unggahan* (D3) | Kode tersimpan; babak 2 baru |
| tmg-5, mgl-5, wnb-5 | Skenario Digital, tantangan kelima (D2) | Disetujui |
| a, b | Variasi soal dalam tantangan yang dinilai; pengerjaan pertama memakai variasi dari buklet siswa | Disetujui 25 Oktober 2026 (dulu a–c) |
| x | Butir yang dipakai semua buklet: kartu jangkar (mis. `tmg-3-x01`) dan Temukan Benda (mis. `wnb-4-x03`) | Disetujui |
| c … j | Variasi Susun Gambar untuk pengerjaan ulang | Kode tersimpan |
| B1–B4 | Buklet: kombinasi variasi per wilayah (B1 a-a-a, B2 b-b-b, B3 a-b-a, B4 b-a-b), dibagikan per kursi lewat denah L2 | Disetujui |
| Kedugram | Aplikasi media sosial fiktif tempat semua unggahan di bacaan digital dan *Periksa Unggahan* tampil | Disetujui |
| tmg-2-a01 | Kode butir: tantangan `tmg-2`, variasi `a`, butir ke-01 | Kode tersimpan |

## Peserta, sekolah, dan sesi

| Kode | Arti | Catatan |
| --- | --- | --- |
| S03-A07 | Kode peserta: sekolah penelitian `S03`, rombel `A`, siswa ke-`07` | Disetujui 25 Oktober 2026, mengganti `GLT-000123` |
| S03 | Kode sekolah penelitian, dari daftar peneliti (bukan NPSN) | Disetujui 25 Oktober 2026 |
| U01-A03 | Kode peserta di sekolah uji coba (awalan `U`) | Disetujui 25 Oktober 2026 |
| A, B | Rombel kelas 5 di satu sekolah | Disetujui 25 Oktober 2026 |
| S03-A-P1 | Kode sesi lapangan: sekolah, rombel, pertemuan ke-1 | Disetujui 25 Oktober 2026 |
| S03-A-P3-S | Kode sesi lapangan susulan: siswa yang absen di P3, dalam tiga hari | 02, 26 Oktober 2026 |
| KD7RMA | Contoh token sesi: 6 huruf dan angka tanpa O, 0, I, 1 | Disetujui 25 Oktober 2026 |
| GLT-000123 | Kode peserta lama | Diganti |
| P01, P02, … | Nomor stiker perangkat | Lapangan |

## Kegiatan dengan anak

Kegiatan yang dimintakan izin etik dalam satu permohonan ([22 → Kegiatan yang dimintakan izin](22_PERMOHONAN_IZIN_ETIK.md#kegiatan-yang-dimintakan-izin), 27 Oktober 2026). Awalan `KG` dipakai supaya tidak tertukar dengan kode anak uji keterbacaan `K01`–`K05`.

| Kode | Arti | Tahap |
| --- | --- | --- |
| KG1 | Uji keterbacaan dengan anak ([24](24_UJI_KETERBACAAN.md)) | 2 |
| KG2 | Berpikir lantang (L5) | 4 |
| KG3 | Uji coba satu kelas, sekolah berawalan `U` | 4 |
| KG4 | Wawancara retrospektif di P2–P4 (L5) | 4 dan 5 |
| KG5 | Pengambilan data utama, P1–P5 dan sesi susulan | 5 |

## Kode kesalahan: salah memilih informasi (INF)

| Kode | Arti | Dimensi |
| --- | --- | --- |
| INF-LIT | Salah membaca informasi tersurat | D1 |
| INF-KAT | Terkecoh kesamaan kata | D1 |
| INF-REL | Benar, tapi tidak menjawab pertanyaan | D1 |
| INF-HUB | Salah menghubungkan informasi (sebab-akibat, urutan) | D1 |
| INF-SUM | Salah menilai sumber | D1 |
| INF-FOP | Mencampur fakta dan pendapat | D1 |
| INF-BUD | Tertukar benda budaya yang mirip | PBK (Cari Objek babak 1) |
| INF-GEN | Menggeneralisasi berlebihan | D1 |
| INF-LAIN | Kesalahan informasi yang tidak cocok dengan kode lain; dikode manual | D1 |

## Kode kesalahan: salah memilih tindakan digital (DIG)

| Kode | Arti | Dimensi |
| --- | --- | --- |
| DIG-PRI | Membagikan data pribadi berlebihan (berisiko) | D2 |
| DIG-SAN | Membagikan kata sandi atau kode OTP (berisiko) | D2 |
| DIG-IZN | Membagikan foto atau obrolan diri sendiri atau teman tanpa izin (berisiko) | D2 |
| DIG-ASG | Menanggapi orang asing (berisiko) | D2 |
| DIG-HOX | Meneruskan kabar yang belum dicek (berisiko) | D2 |
| DIG-ETK | Berkomunikasi tidak etis (berisiko) | D2 |
| DIG-PSF | Terlalu pasif, tidak melapor (kurang tepat) | D2 |
| DIG-LAIN | Kesalahan tindakan yang tidak cocok dengan kode lain; dikode manual | D2 |

## Kode kesalahan: budaya digital (BUD)

| Kode | Arti | Dimensi |
| --- | --- | --- |
| BUD-ATR | Tidak mencantumkan atau salah menunjuk pembuat dan sumber karya budaya | D3 |
| BUD-REP | Menerima atau menyebarkan keterangan budaya yang keliru | D3 |
| BUD-IZN | Mengunggah ulang penampilan atau karya budaya tanpa izin | D3 |
| BUD-ETK | Menanggapi tradisi dengan cara yang tidak menghormati | D3 |
| BUD-LAIN | Kesalahan budaya digital yang tidak cocok dengan kode lain; dikode manual | D3 |

## Kode kesalahan: prosedural (NON)

| Kode | Arti | Catatan |
| --- | --- | --- |
| NON-TEB | Menebak cepat: menjawab kurang dari 3 detik tanpa membaca | Ambang sementara (26 Oktober 2026), ditetapkan ulang setelah uji coba |
| NON-SEN | Salah ketuk di dekat sasaran (Cari Objek) | — |
| NON-HABIS | Jawaban terakhir saat kesempatan habis | — |

## Kode alasan di Skenario Digital (R)

| Kode | Arti | Catatan |
| --- | --- | --- |
| R-RISK | Paham risikonya | Alasan yang diharapkan |
| R-ETIK | Empati atau izin | Alasan yang diharapkan |
| R-ATUR | "Karena disuruh atau dilarang" | Tindakan bisa benar, alasan dangkal |
| R-SOS | Tekanan teman atau ingin disukai | — |
| R-ACAK | Alasannya tidak nyambung | — |

## Tes awal–akhir dan angket

Dari [13](13_TES_AWAL_AKHIR.md#kode-butir) dan [16](16_ANGKET_AKSES_DIGITAL.md). Kode dibekukan setelah uji coba memakainya.

| Kode | Arti | Catatan |
| --- | --- | --- |
| tes-mb-A07 | Butir tes: bagian, bentuk, nomor. Bagian `mb` Membaca (D1), `ae` Aman dan etis (D2), `bd` Budaya digital (D3); bentuk `A` atau `B` ditulis kapital supaya tidak tertukar dengan variasi game `a` dan `b` | 13 |
| tes-mb-A-M1 | Teks Bagian 1 (M1–M4) | 14 |
| tes-bd-A-G1 | Adegan Bagian 3 (G1–G2) | 15 |
| tes-ct-01 | Contoh soal, tidak diskor, sama di kedua bentuk (01–05) | 14, 15 |
| ang-01 … ang-17 | Butir angket akses digital di P1 | 16 |
| ang-21 … ang-23 | Butir risiko daring di P5, hanya dengan izin terpisah; nomor 18–20 sengaja kosong | 16 |
| `-` | Butir tes atau angket yang tidak dijawab | |
| 9 / 8 / 7 | Jawaban angket: tidak tahu atau tidak ingat / tidak berlaku / tidak mau menjawab (7 hanya di ang-21 sampai ang-23) | 16 |
| `not_reached` | Butir tes yang belum pernah tampil saat bagiannya ditutup batas waktu; data hilang di analisis Rasch | 13 |

## Gambar mock-up

Dari [18](18_MOCKUP_LAYAR_BARU.md). Kode gambar mock-up untuk ahli media langkah 1; berkasnya `gambar/mockup/{kode}.webp`, sumbernya `gambar/mockup/src/{kode}.html`.

| Kode | Arti | Catatan |
| --- | --- | --- |
| mk-sd-01 … 05 | Skenario Digital (tantangan kelima) | 18 |
| mk-pu-01 … 04 | Cari Objek babak 2 *Periksa Unggahan* | 18 |
| mk-tk-01 … 05 | Token sesi, di luar sesi, layar kecil, panel peneliti | 18 |
| mk-mt-01 … 06 | Modul tes dan angket | 18 |
| mk-ss-01 … 05 | Skala sikap di Balai Refleksi: petunjuk, contoh soal, satu pernyataan, layar kirim; lalu Balai Refleksi dengan kritik dan saran | 18 |
| mk-cr-01 … 02 | Cerita penghubung *Kabut di Layar* dan kartu misi tantangan kelima | 18 |

## ID netral pelabelan buta (B.5a)

Dipakai di daftar buta B.5a supaya kode butir (yang menyebut wilayah dan tantangan) tidak memberi isyarat level ([21](21_BERKAS_BUTIR_VALIDATOR.md), keputusan pengembang 27 Oktober 2026). Padanannya dengan kode butir hanya ada di berkas kunci khusus peneliti.

| Kode | Arti | Catatan |
| --- | --- | --- |
| BT-001 … BT-180 | ID butir netral: 116 butir bank dan 64 butir tes | Diacak dengan benih tetap; berlaku untuk `berkas-1.0` |
| BH-01 … BH-73 | ID bahan (bacaan, unggahan, situasi) netral | Butir tetap berurutan di dalam satu bahan |

## Skala sikap menjaga warisan

Dari [17](17_SKALA_SIKAP.md). Usulan; dibekukan sebagai `sikap-1.0` di G4.

| Kode | Arti | Catatan |
| --- | --- | --- |
| skp-01 … skp-12 | Butir skala sikap, nomor urut tampil | Butir negatif (dibalik): skp-03, skp-06, skp-08, skp-10 |
| skp-ct-01 | Contoh soal skala sikap, tidak diskor | |
| SK1–SK4 | Indikator: SK1 menghargai, SK2 ingin tahu, SK3 mau ikut merawat, SK4 bangga | Bukan kode sekolah (`S01`, dua angka) |
| 1 / 2 / 3 / 4 | Jawaban: Tidak setuju / Kurang setuju / Setuju / Sangat setuju; butir negatif diskor 5 − kode | `-` = dilewati |

## Lembar lapangan

| Kode | Arti | Pengisi |
| --- | --- | --- |
| L0 | Daftar periksa persiapan sesi | P1 |
| L1 | Kondisi sesi dan penyimpangan protokol | P1 |
| L2 | Peta tempat duduk, perangkat, dan kode peserta | P1 |
| L3 | Log bantuan dan gangguan | P3 |
| L4 | Observasi keterlibatan dan emosi (adaptasi BROMP) | P2 |
| L5 | Wawancara retrospektif (P2–P4, tiga siswa per pertemuan) dan berpikir lantang (uji coba) | P3 |
| L6 | Log tes awal–akhir dan angket akses digital: catatan pelaksanaan, karena jawaban tersimpan di modul tes | P1 |
| L7 | Izin orang tua dan status peserta | P1 |

## Kode di L1: penyimpangan

| Kode | Arti |
| --- | --- |
| DEV-NET | Internet putus |
| DEV-LIS | Listrik atau baterai bermasalah |
| DEV-WKT | Waktu sesi dipotong |
| DEV-PRT | Protokol dilanggar, misalnya guru menjelaskan soal |
| DEV-TIM | Peneliti kurang dari tiga; P1 juga mengisi L3 dan L5 dipindah |
| DEV-LAIN | Penyimpangan lain |

## Kode di L3: bantuan dan gangguan

| Kode | Arti | Kelompok |
| --- | --- | --- |
| TMN | Teman | Pemberi bantuan |
| GRU | Guru | Pemberi bantuan |
| PNL | Peneliti | Pemberi bantuan |
| LN | Lainnya | Pemberi bantuan |
| ISI | Memberi tahu jawaban | Jenis bantuan |
| STR | Memberi strategi | Jenis bantuan |
| BHS | Menjelaskan arti kata | Jenis bantuan |
| TKN | Bantuan teknis | Jenis bantuan |
| SMG | Memberi semangat | Jenis bantuan |
| GG-KLR | Meninggalkan kursi | Gangguan |
| GG-LAIN | Membuka aplikasi lain | Gangguan |
| GG-OBR | Mengobrol di luar tugas | Gangguan |
| GG-TEK | Perangkat bermasalah | Gangguan |

## Kode di L4: perilaku dan emosi

| Kode | Arti | Kelompok |
| --- | --- | --- |
| ON | Mengerjakan sendiri | Perilaku |
| ONK | Berdiskusi soal tugas | Perilaku |
| OFF | Di luar tugas | Perilaku |
| TBK | Mengetuk asal atau menebak cepat | Perilaku |
| FOK | Fokus | Emosi |
| BNG | Bingung | Emosi |
| FRS | Frustrasi | Emosi |
| BSN | Bosan | Emosi |
| SNG | Senang | Emosi |
| NTR | Netral | Emosi |
| ? | Tidak jelas, sedang dibantu, atau tidak di kursi | Perilaku dan emosi |

## Uji keterbacaan (KG1)

Kode di protokol [24](24_UJI_KETERBACAAN.md), semuanya usulan sampai uji keterbacaan dijalankan.

| Kode | Arti | Catatan |
| --- | --- | --- |
| K01–K04 | Anak uji keterbacaan putaran pertama | Dari sekolah di wilayah Kedu di luar sampel dan uji coba; tanpa nama di lembar |
| K05 | Anak uji keterbacaan putaran kedua, membaca bahan yang sudah direvisi | |
| K01-1 … K05-4 | Kode sesi: anak dan sesi ke-1 sampai ke-4 | Bukan `S…`, supaya tidak tertukar dengan kode sekolah |
| LK1, LK2, LK3 | Lembar ringkasan sesi, catatan per butir, kartu kata | |
| KB-KTA | Kata tidak dikenal atau artinya keliru | Kode kesulitan |
| KB-KLM | Kalimat sulit dipahami | Kode kesulitan |
| KB-MKS | Maksud soal disalahpahami | Kode kesulitan |
| KB-PTJ | Petunjuk atau cara menjawab tidak dipahami | Kode kesulitan |
| KB-PIL | Pilihan jawaban sulit dibedakan | Kode kesulitan |
| KB-ARH | Butir negatif dijawab berlawanan dengan maksud anak | Kode kesulitan |
| KB-TPL | Masalah tampilan | Kode kesulitan |
| KB-LMA | Berhenti lama atau membaca ulang | Kode kesulitan |
| KB-LTS | Menjawab dari pengetahuan sendiri, bukan dari teks | Kode kesulitan |
| KB-RSA | Kalimat membuat anak tidak nyaman | Kode kesulitan |
| KB-LAIN | Kesulitan lain | Kode kesulitan |
| PB-1 … PB-9 | Pancingan baku (parafrase soal, kalimat, cara menjawab, pilihan, kata, petunjuk, arah, rasa, penutup) | Label di dalam 24 saja |

## Level dan taksonomi

| Kode | Arti | Kerangka |
| --- | --- | --- |
| L1, L2, L3 | Singkatan level AKM di kisi-kisi dan bank soal: menemukan informasi, interpretasi dan integrasi, evaluasi dan refleksi | AKM |
| Menemukan informasi | Mencari dan mengambil informasi tersurat | AKM, Temanggung |
| Interpretasi dan integrasi | Memahami informasi tersirat dan memadukan antarbagian teks | AKM, Magelang |
| Evaluasi dan refleksi | Menilai isi dan kredibilitas teks, mengaitkan dengan hal di luar teks | AKM, Wonosobo |
| C1 | Mengingat | Bloom revisi |
| C2 | Memahami | Bloom revisi |
| C3 | Menerapkan | Bloom revisi |
| C4 | Menganalisis | Bloom revisi |
| C5 | Mengevaluasi | Bloom revisi |
| C6 | Mencipta | Bloom revisi |
| κ | Cohen's kappa, ukuran kesepakatan dua pengode atau pengamat | Statistik |

## Status, tahap, dan keputusan log

| Kode | Arti | Dipakai di |
| --- | --- | --- |
| Sudah | Sudah terekam penuh di sistem | Status 10 analitik |
| Sebagian | Terekam sebagian, perlu dilengkapi | Status 10 analitik |
| Belum | Belum ada di sistem | Status 10 analitik |
| Belum mulai, Berjalan, Selesai | Status pekerjaan tim | Daftar kerja |
| Tahap 1–6 | Desain instrumen; validasi ahli dan izin etik; perubahan sistem; uji coba; pengambilan data utama; analisis dan pelaporan | Peta jalan |
| G1–G5 | Gerbang antartahap; tahap berikutnya mulai setelah gerbangnya terpenuhi | Peta jalan |
| Dihapus | Log berhenti direkam | Log: dihapus atau dipisah |
| Admin | Log hanya untuk admin, tidak masuk ekspor penelitian | Log: dihapus atau dipisah |
| Peneliti | Log untuk data penelitian, dengan kode peserta | Log: dihapus atau dipisah |
